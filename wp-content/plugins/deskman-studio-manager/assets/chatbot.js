/* ==========================================================================
   Deskman Chatbot — front-end behaviour + answer engine.

   No dependencies. Everything runs in the visitor's browser:
     - the knowledge base (keywords + answers) is passed in via DESKMAN_CB.kb;
     - a lightweight scorer picks the best-matching answer for what was typed;
     - nothing the visitor types is stored or sent anywhere.

   Extending with real AI later:
     define window.deskmanCbAnswer = async function(text){ return "…"; }
     and it will be used instead of the local matcher (return a string, or an
     object { text, contact:true }). If it throws or returns nothing, the local
     matcher is used as a fallback. This file needs no changes for that.
   ========================================================================== */
(function () {
  "use strict";

  var CFG = window.DESKMAN_CB || {};
  var root = document.getElementById("deskman-cb");
  if (!root || !CFG) { return; }

  var launcher = root.querySelector(".dcb-launcher");
  var panel    = root.querySelector("#dcb-panel");
  var log      = root.querySelector("#dcb-log");
  var quickWrap= root.querySelector("#dcb-quick");
  var form     = root.querySelector("#dcb-form");
  var input    = root.querySelector("#dcb-input");
  var xBtn      = root.querySelector(".dcb-x");
  var resetBtn  = root.querySelector(".dcb-reset");
  if (!launcher || !panel || !log || !form || !input) { return; }

  var SKEY_OPEN = "deskman_cb_open";
  var SKEY_TX   = "deskman_cb_tx";
  var reduce = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  var greeted = false;
  var leadName = "", leadProject = "", leadPhone = "";   // best-effort lead context
  var otpEmail = "", otpVerified = false;                 // email-verification handshake state
  var leadCaptured = false;                               // true once a real lead has been posted

  /* ---------------------------------------------------------------- storage */
  function loadTx() {
    try { return JSON.parse(sessionStorage.getItem(SKEY_TX) || "[]"); }
    catch (e) { return []; }
  }
  function saveTx(tx) {
    try { sessionStorage.setItem(SKEY_TX, JSON.stringify(tx.slice(-40))); } catch (e) {}
  }
  var transcript = loadTx();

  /* ------------------------------------------------------------- rendering */
  function el(tag, cls, text) {
    var n = document.createElement(tag);
    if (cls) { n.className = cls; }
    if (text != null) { n.textContent = text; }
    return n;
  }

  /* Render an answer bubble. Any {contact} token is simply removed — the bot
     collects details in chat rather than pushing a form. Text is inserted as
     plain text (never innerHTML), so an answer can never inject markup. */
  function renderBot(text) {
    var wrap = el("div", "dcb-msg bot");
    var clean = String(text)
      .replace(/\[\[LEAD\]\][\s\S]*?\[\[\/LEAD\]\]/gi, "") // full hidden block
      .replace(/\[\[LEAD\]\][\s\S]*$/i, "")                 // truncated block (no close)
      .replace(/\[\[\/?LEAD\]\]/gi, "")                     // any stray marker
      .replace(/\{contact\}/gi, "")
      .replace(/[ \t]{2,}/g, " ")        // squeeze runs of spaces/tabs…
      .replace(/[ \t]*\n[ \t]*/g, "\n")  // …but keep line breaks tidy
      .replace(/\n{3,}/g, "\n\n")         // at most one blank line
      .trim();
    wrap.appendChild(document.createTextNode(clean));
    log.appendChild(wrap);
    scroll();
  }
  function renderUser(text) {
    log.appendChild(el("div", "dcb-msg user", text));
    scroll();
  }
  function scroll() { log.scrollTop = log.scrollHeight; }

  function typing(on) {
    var t = log.querySelector(".dcb-typing");
    if (on) {
      if (t) { return; }
      var d = el("div", "dcb-typing");
      d.appendChild(el("i")); d.appendChild(el("i")); d.appendChild(el("i"));
      log.appendChild(d); scroll();
    } else if (t) { t.remove(); }
  }

  function replayTranscript() {
    log.innerHTML = "";
    transcript.forEach(function (m) {
      if (m.who === "user") { renderUser(m.text); }
      else { renderBot(m.text, m.contact); }
    });
    if (transcript.length) { greeted = true; }
  }

  /* --------------------------------------------------------- quick replies */
  /* Suggestions are a starting nudge only: they show under the greeting and
     vanish the moment the visitor sends anything, so the rest of the chat is
     just them and Deskman. They never come back in the same session. */
  function hasUserTurn() {
    for (var i = 0; i < transcript.length; i++) { if (transcript[i].who === "user") { return true; } }
    return false;
  }
  function buildQuick() {
    quickWrap.innerHTML = "";
    if (hasUserTurn()) { quickWrap.style.display = "none"; return; }
    (CFG.quick || []).forEach(function (q) {
      var b = el("button", "dcb-chip", q);
      b.type = "button";
      b.addEventListener("click", function () { submit(q); });
      quickWrap.appendChild(b);
    });
    quickWrap.style.display = (CFG.quick && CFG.quick.length) ? "flex" : "none";
  }
  function hideQuick() {
    quickWrap.innerHTML = "";
    quickWrap.style.display = "none";
  }

  /* ------------------------------------------------------------- the matcher */
  function normalize(s) {
    return " " + String(s).toLowerCase()
      .replace(/[^\p{L}\p{N}\s]/gu, " ")
      .replace(/\s+/g, " ").trim() + " ";
  }

  function localAnswer(query) {
    var q = normalize(query);
    var kb = CFG.kb || [];
    var best = null, bestScore = 0;
    for (var i = 0; i < kb.length; i++) {
      var entry = kb[i], score = 0;
      var keys = entry.k || [];
      for (var j = 0; j < keys.length; j++) {
        var kw = normalize(keys[j]).trim();
        if (!kw) { continue; }
        var weight = 1 + Math.min(kw.length / 6, 3);
        // Strongest: the keyword/phrase appears as a whole word (q is padded
        // with spaces at both ends, so this covers start, middle and end).
        if (q.indexOf(" " + kw + " ") !== -1) {
          score += weight;
        // Forgiving: for longer keywords, a substring hit still counts, so
        // "pricing" matches the keyword "price". Scored a little lower.
        } else if (kw.length >= 5 && q.indexOf(kw) !== -1) {
          score += weight * 0.7;
        }
      }
      if (score > bestScore) { bestScore = score; best = entry; }
    }
    if (best && bestScore >= 1) { return { text: best.a }; }
    return { text: CFG.fallback || "I'm not sure about that one.", contact: true };
  }

  function answer(query) {
    // 1) A custom hook wins if defined.
    if (typeof window.deskmanCbAnswer === "function") {
      try {
        var r = window.deskmanCbAnswer(query);
        if (r && typeof r.then === "function") {
          return r.then(function (v) { return normalizeAns(v) || fallbackChain(query); })
                  .catch(function () { return fallbackChain(query); });
        }
        var n = normalizeAns(r);
        if (n) { return Promise.resolve(n); }
      } catch (e) {}
    }
    // 2) Server AI (if enabled) → 3) local knowledge base.
    return fallbackChain(query);
  }
  function fallbackChain(query) {
    return serverAnswer().then(function (a) {
      if (a) { return a; }
      // AI on but the call still failed after retries: stay graceful. If we've
      // already captured the lead, reassure rather than ask them to repeat.
      if (CFG.ai) {
        if (leadCaptured) { return { text: CFG.leadThanks || "Thanks — I've got your details and passed them to the team; they'll be in touch shortly. Anything else you'd like to add?" }; }
        return { text: CFG.aiHiccup || "Sorry, I had a brief hiccup just then — could you say that once more?" };
      }
      // AI off: answer from the local knowledge base.
      return localAnswer(query);
    });
  }
  function normalizeAns(v) {
    if (!v) { return null; }
    if (typeof v === "string") { return { text: v }; }
    if (typeof v === "object" && v.text) { return v; }
    return null;
  }

  /* The recent conversation, mapped to chat roles, for the server to reason
     over. Mechanical verification lines (code prompts, "resend", the typed
     code) are tagged sys and left OUT so they don't crowd the model's memory
     or make it forget what was already discussed. */
  function toMessages() {
    return transcript
      .filter(function (m) { return m.text && !m.sys; })
      .slice(-20)
      .map(function (m) { return { role: m.who === "user" ? "user" : "assistant", content: m.text }; });
  }

  /* Calls the WordPress proxy, which holds the API key server-side. Resolves to
     an answer object, or null to fall back. Retries up to twice with a short
     backoff so a brief network/API/rate-limit blip doesn't derail the chat. */
  function serverAnswer(attempt) {
    attempt = attempt || 0;
    if (!CFG.ai || !CFG.rest) { return Promise.resolve(null); }
    return fetch(CFG.rest, {
      method: "POST",
      headers: { "Content-Type": "application/json", "X-WP-Nonce": CFG.nonce || "" },
      body: JSON.stringify({ messages: toMessages() })
    })
      .then(function (r) { return r.ok ? r.json() : null; })
      .then(function (d) {
        if (!d || d.fallback) { throw new Error("fallback"); }
        if (d.reply && String(d.reply).trim()) { return { text: String(d.reply), contact: !!d.contact }; }
        throw new Error("empty");
      })
      .catch(function () {
        if (attempt < 2) {
          return new Promise(function (res) {
            setTimeout(function () { res(serverAnswer(attempt + 1)); }, 700 * (attempt + 1));
          });
        }
        return null;
      });
  }

  /* --- contact-detail detection + validation (make it smart) --- */
  var BAD_TLDS = { cum:1, con:1, vom:1, xom:1, cmo:1, ocm:1, comm:1, cim:1, coom:1, clm:1, cpm:1, om:1, c0m:1, ney:1, nte:1, ogr:1, orgg:1, cin:1 };
  var PROVIDERS = {
    gmail:["com"], googlemail:["com"], yahoo:["com","co.in","co.uk","in","ca"], ymail:["com"],
    hotmail:["com","co.uk","co.in"], outlook:["com","in"], live:["com","in"], icloud:["com"],
    proton:["me"], protonmail:["com"], aol:["com"], zoho:["com","in"], rediffmail:["com"], rediff:["com"]
  };
  function extractEmail(s) {
    var m = String(s).match(/[^\s@]+@[^\s@]+\.[^\s@]+/);
    return m ? m[0].replace(/[.,;:>)\]]+$/, "") : "";
  }
  function emailParts(e) {
    var at = e.lastIndexOf("@"); if (at < 1) { return null; }
    var domain = e.slice(at + 1).toLowerCase();
    var dot = domain.indexOf("."); if (dot < 1) { return null; }
    return { local: e.slice(0, at), sld: domain.slice(0, dot), tld: domain.slice(dot + 1) };
  }
  function isValidEmail(e) {
    if (!/^[^\s@]+@[^\s@]+\.[a-z]{2,}$/i.test(e)) { return false; }
    var p = emailParts(e); if (!p) { return false; }
    if (BAD_TLDS[p.tld]) { return false; }
    if (PROVIDERS[p.sld] && PROVIDERS[p.sld].indexOf(p.tld) === -1) { return false; }
    return true;
  }
  function suggestEmail(e) {
    var p = emailParts(e); if (!p) { return ""; }
    if (PROVIDERS[p.sld]) { return p.local + "@" + p.sld + "." + PROVIDERS[p.sld][0]; }
    if (BAD_TLDS[p.tld]) { return p.local + "@" + p.sld + ".com"; }
    return "";
  }
  function extractPhone(s) {
    var m = String(s).match(/(\+?\d[\d\s().-]{6,}\d)/);
    return m ? m[1].trim() : "";
  }
  function phoneDigits(p) { return String(p).replace(/\D/g, ""); }
  /* Valid Indian mobile → returns "+91XXXXXXXXXX", else "". Accepts +91 / 91 /
     leading-0 prefixes; the core must be 10 digits starting 6, 7, 8 or 9. */
  function normalizeIndianPhone(p) {
    var d = phoneDigits(p);
    if (d.length === 12 && d.slice(0, 2) === "91") { d = d.slice(2); }
    else if (d.length === 13 && d.slice(0, 3) === "091") { d = d.slice(3); }
    else if (d.length === 11 && d.charAt(0) === "0") { d = d.slice(1); }
    return /^[6-9]\d{9}$/.test(d) ? ("+91" + d) : "";
  }

  /* Post a lead to the server. Works with AI on or off; the server dedupes and
     only stores a lead once the email is OTP-verified or the mobile is a valid
     Indian number, so fake / trash contacts never get saved. */
  function captureLead(payload) {
    if (!CFG.restLead || !payload) { return; }
    payload.messages = toMessages();
    try {
      fetch(CFG.restLead, {
        method: "POST",
        headers: { "Content-Type": "application/json", "X-WP-Nonce": CFG.nonce || "" },
        body: JSON.stringify(payload)
      });
    } catch (e) {}
  }
  function sendLead(extra) {
    var payload = { phone: leadPhone || "", name: leadName || "", project: leadProject || "" };
    if (extra) { for (var k in extra) { if (Object.prototype.hasOwnProperty.call(extra, k)) { payload[k] = extra[k]; } } }
    if (otpVerified && otpEmail && !payload.email) { payload.email = otpEmail; payload.email_verified = 1; }
    if (!payload.phone && !payload.email) { return; }
    leadCaptured = true;
    captureLead(payload);
  }

  /* --- tiny message helpers --- */
  function emit(msg, sys) { renderBot(msg); transcript.push({ who: "bot", text: String(msg), sys: !!sys }); saveTx(transcript); }
  function botSay(msg, sys) {
    typing(true);
    var t = Date.now(), fl = reduce ? 100 : 280;
    setTimeout(function () { typing(false); emit(msg, sys); }, Math.max(0, fl - (Date.now() - t)));
  }
  function markLastUserSys() {
    for (var i = transcript.length - 1; i >= 0; i--) {
      if (transcript[i].who === "user") { transcript[i].sys = true; saveTx(transcript); break; }
    }
  }
  function fmt(s, v) { v = v || {}; return String(s == null ? "" : s).replace(/\{(\w+)\}/g, function (_, k) { return v[k] != null ? v[k] : ""; }); }
  var OTP = CFG.otp || {};

  /* --- email OTP handshake --- */
  function doOtpSend(email, isResend) {
    otpEmail = email; otpVerified = false;
    if (!CFG.restOtp) { otpEmail = ""; sendLead({ email: email, email_unverified: 1 }); botSay(OTP.failed || "I couldn't verify that email right now, but I've noted it. Could you share your mobile number?"); return; }
    typing(true);
    fetch(CFG.restOtp, { method: "POST", headers: { "Content-Type": "application/json", "X-WP-Nonce": CFG.nonce || "" }, body: JSON.stringify({ email: email }) })
      .then(function (r) { return r.ok ? r.json() : null; })
      .then(function (d) {
        typing(false);
        if (d && d.verified) { otpVerified = true; onVerified(); return; }
        if (d && d.ok) { emit(fmt(isResend ? OTP.resent : OTP.sent, { email: email }), true); return; }
        // couldn't send (e.g. SMTP off): keep the real email, flagged unverified.
        otpEmail = ""; sendLead({ email: email, email_unverified: 1 });
        emit(fmt(OTP.failed || "I couldn't send the code just now, but I've noted your email. Could you also share your mobile number?"), true);
      })
      .catch(function () { typing(false); otpEmail = ""; sendLead({ email: email, email_unverified: 1 }); emit(fmt(OTP.failed || "I couldn't send the code just now, but I've noted your email."), true); });
  }
  function doOtpVerify(email, code) {
    typing(true);
    fetch(CFG.restOtpVerify, { method: "POST", headers: { "Content-Type": "application/json", "X-WP-Nonce": CFG.nonce || "" }, body: JSON.stringify({ email: email, code: code }) })
      .then(function (r) { return r.ok ? r.json() : null; })
      .then(function (d) {
        typing(false);
        if (d && d.ok) { otpVerified = true; onVerified(); return; }
        var reason = d && d.reason;
        if (reason === "expired") { emit(fmt(OTP.expired || "That code has expired — say \"resend\" for a new one."), true); }
        else if (reason === "toomany") { emit(fmt(OTP.toomany || "Too many tries — say \"resend\" for a new code."), true); }
        else { emit(fmt(OTP.invalid || "That code doesn't match — please try again, or say \"resend\"."), true); }
      })
      .catch(function () { typing(false); emit(fmt(OTP.invalid || "That code doesn't match — please try again."), true); });
  }
  function onVerified() {
    var e = otpEmail;
    sendLead({ email: e, email_verified: 1 });
    emit(leadName ? fmt(OTP.ok, { name: leadName }) : (OTP.okNoName || "Your email is verified ✅"));
    if (CFG.ai) {
      typing(true);
      serverAnswer().then(function (a) {
        typing(false);
        if (a && a.text) { emit(a.text); }
        else { emit(CFG.leadThanks || "Thanks — the Deskman Studio team will review your details and get back to you shortly."); }
      });
    }
  }

  /* ------------------------------------------------------------- send flow */
  function submit(text) {
    text = String(text || "").trim();
    if (!text) { return; }
    hideQuick();
    renderUser(text);
    transcript.push({ who: "user", text: text }); saveTx(transcript);
    input.value = "";

    var emailTok = extractEmail(text);
    var restText = emailTok ? text.replace(emailTok, " ") : text;
    var digitsAll = text.replace(/\D/g, "");
    var goodEmail = ( emailTok && isValidEmail(emailTok) ) ? emailTok : "";
    var badEmail = emailTok && !goodEmail;

    // previous bot line, for the name heuristic
    var prevBot = "";
    for (var bi = transcript.length - 2; bi >= 0; bi--) {
      if (transcript[bi].who !== "user") { prevBot = String(transcript[bi].text || ""); break; }
    }
    if (!leadName && /\bname\b/i.test(prevBot) && /\?/.test(prevBot) && !emailTok && digitsAll.length < 6) {
      var cand = text.trim();
      if (/^[\p{L}][\p{L}\s.'’-]{1,40}$/u.test(cand) && cand.split(/\s+/).length <= 3) {
        leadName = cand.replace(/\s+/g, " ").trim();
      }
    }
    if (!leadProject && !emailTok && digitsAll.length < 6 && text.replace(/\s/g, "").length >= 6) {
      leadProject = text.slice(0, 180);
    }

    // Capture a valid Indian mobile whenever it appears (even mid-OTP).
    var phoneTok = extractPhone(restText);
    var goodPhone = normalizeIndianPhone(phoneTok || restText);
    var gotPhoneNow = false;
    if (goodPhone) { leadPhone = goodPhone; sendLead({}); gotPhoneNow = true; }

    // 0) Awaiting an email code? Handle resend / the 6-digit code / re-ask.
    if (otpEmail && !otpVerified && !goodEmail) {
      if (/\b(re-?send|resend|again|didn'?t\s*get|not\s*receiv|new\s*code|send.*code)\b/i.test(text)) { markLastUserSys(); doOtpSend(otpEmail, true); return; }
      var codeM = text.match(/\b(\d{6})\b/);
      if (codeM && digitsAll.length <= 7) { markLastUserSys(); doOtpVerify(otpEmail, codeM[1]); return; }
      markLastUserSys();
      botSay((gotPhoneNow ? "Got your number ✅ " : "") + fmt(OTP.ask || "Please enter the 6-digit code I emailed to {email}.", { email: otpEmail }), true);
      return;
    }

    // 1) A mistyped email → offer a correction, don't proceed.
    if (badEmail) {
      var sug = suggestEmail(emailTok);
      botSay(sug
        ? "Hmm, that email looks a little off — did you mean " + sug + "? Mind sending it again?"
        : "Hmm, that email doesn't look quite right — could you double-check and send it again?");
      return;
    }
    // 2) A valid email → start (or restart) verification. Don't store yet.
    if (goodEmail && !(otpVerified && otpEmail === goodEmail)) { doOtpSend(goodEmail, false); return; }

    // 3) A number that was meant to be a phone but isn't a valid Indian mobile.
    if (!goodPhone) {
      var run = (restText.match(/\d[\d\s().-]{6,}\d/) || [""])[0].replace(/\D/g, "");
      var phoneIntent = /(mobile|phone|number|whats?app|call|cell|contact)/i.test(restText);
      if ((run.length >= 10 && run.length <= 13) || (phoneIntent && run.length >= 7)) {
        botSay(CFG.badIndianPhone || "That doesn't look like a valid 10-digit Indian mobile number — could you send it again?");
        return;
      }
    }

    // 4) Normal answer: AI → local knowledge base.
    typing(true);
    var floor = reduce ? 120 : (CFG.ai ? 240 : 450 + Math.min(text.length * 12, 500));
    var t0 = Date.now();
    answer(text).then(function (a) {
      var extra = Math.max(0, floor - (Date.now() - t0));
      setTimeout(function () {
        typing(false);
        renderBot(a.text);
        transcript.push({ who: "bot", text: a.text }); saveTx(transcript);
      }, extra);
    });
  }

  /* --------------------------------------------------------------- open/close */
  function open() {
    root.setAttribute("data-open", "true");
    launcher.setAttribute("aria-expanded", "true");
    panel.setAttribute("aria-hidden", "false");
    try { sessionStorage.setItem(SKEY_OPEN, "1"); } catch (e) {}
    if (!greeted && !transcript.length) {
      greeted = true;
      var g = CFG.greeting || "Hi!";
      renderBot(g, false);
      transcript.push({ who: "bot", text: g, contact: false }); saveTx(transcript);
    }
    syncViewport();
    setTimeout(function () { input.focus(); syncViewport(); }, reduce ? 0 : 220);
  }
  function close() {
    root.setAttribute("data-open", "false");
    launcher.setAttribute("aria-expanded", "false");
    panel.setAttribute("aria-hidden", "true");
    clearViewport();
    try { sessionStorage.setItem(SKEY_OPEN, "0"); } catch (e) {}
    launcher.focus();
  }
  function toggle() { root.getAttribute("data-open") === "true" ? close() : open(); }

  /* Reset / new chat: clears the transcript and starts fresh with the greeting
     and the starting suggestions. */
  function resetChat() {
    transcript = [];
    try { sessionStorage.removeItem(SKEY_TX); } catch (e) {}
    greeted = false;
    leadName = ""; leadProject = ""; leadPhone = "";
    otpEmail = ""; otpVerified = false; leadCaptured = false;
    log.innerHTML = "";
    buildQuick();
    var g = CFG.greeting || "Hi!";
    renderBot(g, false);
    transcript.push({ who: "bot", text: g, contact: false }); saveTx(transcript);
    greeted = true;
    input.focus();
  }

  /* ---------------------------------------------------- mobile keyboard fit */
  /* On phones the panel is a fixed bottom-sheet. When the on-screen keyboard
     opens, a fixed element normally stays pinned behind it — so the input (and
     whatever you're typing) disappears under the keyboard. We watch the visual
     viewport and lift the sheet to sit exactly above the keyboard instead. */
  var vv = window.visualViewport || null;
  function isSheet() {
    return window.matchMedia && window.matchMedia("(max-width:480px)").matches;
  }
  function clearViewport() {
    panel.style.height = "";
    panel.style.maxHeight = "";
    panel.style.bottom = "";
  }
  function syncViewport() {
    if (root.getAttribute("data-open") !== "true") { return; }
    if (!vv || !isSheet()) { clearViewport(); return; }
    var kb = Math.max(0, window.innerHeight - vv.height - vv.offsetTop);
    if (kb > 90) {                 // keyboard is up → fill the visible area above it
      panel.style.bottom = kb + "px";
      panel.style.height = vv.height + "px";
      panel.style.maxHeight = vv.height + "px";
    } else {                        // keyboard down → back to the CSS bottom-sheet
      clearViewport();
    }
    scroll();
  }
  if (vv) {
    vv.addEventListener("resize", syncViewport);
    vv.addEventListener("scroll", syncViewport);
  }
  window.addEventListener("orientationchange", function () { setTimeout(syncViewport, 250); });
  input.addEventListener("focus", function () { setTimeout(syncViewport, 60); });
  input.addEventListener("blur", function () { setTimeout(syncViewport, 60); });

  /* ------------------------------------------------------------------ wire up */
  launcher.addEventListener("click", toggle);
  if (xBtn) { xBtn.addEventListener("click", close); }
  if (resetBtn) { resetBtn.addEventListener("click", resetChat); }
  form.addEventListener("submit", function (e) { e.preventDefault(); submit(input.value); });
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape" && root.getAttribute("data-open") === "true") { close(); }
  });

  buildQuick();
  if (transcript.length) { replayTranscript(); }

  // Reopen if it was open before a page navigation this session.
  try {
    if (sessionStorage.getItem(SKEY_OPEN) === "1") { open(); }
  } catch (e) {}
})();
