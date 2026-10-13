=== Deskman Studio Manager ===
Requires at least: 6.0
Requires PHP: 7.4
Stable tag: 5.1.0
License: GPL-2.0-or-later

The complete back end for the Deskman Studio theme: site content, projects,
enquiries, the on-site chatbot, and AI-generated location landing pages.
Everything is edited from one menu: Deskman in the admin sidebar.


== What changed in 5.0.0 ==

Two things, and one of them needs a small action from you.

**The chatbot is now part of this plugin.** The separate "Deskman Chatbot"
plugin has been folded in. Its settings, knowledge base, saved leads and REST
endpoints are all unchanged — the same option name, the same routes, the same
post type — so nothing is lost and nothing needs re-entering.

*Action required:* deactivate and delete the old "Deskman Chatbot" plugin.
Until you do, this plugin detects it, disables its own copy of the chatbot to
avoid a fatal conflict, and shows a notice in the admin. Your chat keeps
running from the old plugin in the meantime, so there is no downtime — but it
is worth doing today rather than forgetting about.

The screens moved from their own top-level menu to **Deskman → Chatbot** and
**Deskman → Chat leads**.

**Area pages are new.** Location landing pages, written for you. See below.


== Area pages ==

A page per city and service — "Best Digital Marketing Agency in Visakhapatnam",
"SEO Company in Warangal" — that ranks for local searches without ever
appearing in your header or footer.

**Deskman → Generate area page.** Type the city, the service and the phrase you
want to rank for, press the button, and a complete draft appears: hero,
introduction, services, why-us, process, FAQ, localities, closing call to
action, meta title and description, and the structured data.

There are two engines behind that button:

* **Grok.** Add your xAI key under the Settings tab and each page is written
  fresh for its city.
* **The built-in template.** Used when no key is saved, and automatically
  whenever an API call fails. It is a real, publishable page — not a
  placeholder — so the feature works from the first minute with no key at all.

**Bulk generate** takes a list of cities, one per line, and works through them
one at a time so a long list cannot time out. Add a service after a pipe to
override the default, and a keyword after a second pipe:

    Visakhapatnam
    Hyderabad | Digital Marketing
    Warangal | SEO | Best SEO Company in Warangal

= Why these are not just Pages =

They are a custom post type, which is what keeps them out of the navigation
without any exclusion rules to maintain — a post type never joins a WordPress
menu unless somebody adds it by hand. They stay fully public and appear in your
sitemap, because hidden from the menu is not the same as hidden from Google.
And they carry their own fields, so one template renders every area
consistently and the structured data is built from real values rather than
scraped back out of a blob of page content.

= What ships on each page =

* `ProfessionalService` structured data with `areaServed` covering the city and
  every locality you list
* `Service` data with your service list as an offer catalogue
* `FAQPage` data from the page's own questions — the thing that can earn the
  expandable answers under a search result
* `BreadcrumbList`, so the result shows a readable path rather than a bare URL
* Meta title and description written into the same fields the theme's SEO
  engine already reads, so nothing in the theme had to change

= Two deliberate constraints =

**Everything is created as a draft.** Programmatic local pages are worth doing.
Publishing them unread is how a site ends up with fifty pages of near-identical
text and a manual action for spam. Read each one before it goes live.

**Nothing is invented.** The model is instructed, repeatedly, never to produce
statistics, client names, testimonials, awards, review scores or prices. Those
are the details that make a local page persuasive, which is exactly why a
fabricated one is a real problem — it is a claim you would have to stand
behind. Put true numbers in yourself, in the fields provided.

= Cross-linking =

On by default, and worth leaving on. A stack of location pages with no links
between them is the version search engines tend to treat as thin. Linking each
page to its siblings gives the set a real structure and a crawl path to every
page, while none of them enters the menu.

There is also a `[deskman_areas]` shortcode for listing every area page
anywhere you like:

    [deskman_areas]
    [deskman_areas title="Where we work" service="SEO" limit="20"]

= URLs =

Area pages live at `/locations/your-page/` by default, and the index of all of
them at `/locations/`. The base is editable under Settings — but if pages
already rank, leave it alone or set up redirects first.


== The rest of the plugin ==

Unchanged from 4.2.0:

* **Settings** — every piece of front-end text, link, colour and image on the
  home page and the interior pages
* **Projects** — the portfolio type behind the Work archive, with an asset
  checker for missing screenshots
* **Forms** — contact enquiries, with scored spam filtering that quarantines
  rather than discards
* **Setup** — the wizard that creates pages, menus and the static homepage
* **Search / SEO** — the per-page title, description and noindex panel, now on
  area pages too


== Installation ==

1. Upload the folder to `/wp-content/plugins/` and activate it.
2. If the old "Deskman Chatbot" plugin is installed, deactivate and delete it.
3. Visit **Deskman → Setup** if you have not already run it.
4. Add your xAI key under **Deskman → Generate area page → Settings**, press
   "Test the connection", then generate your first page.

If an area page 404s straight after upgrading, open Settings → Permalinks and
press Save once. That rebuilds the rewrite rules for the new post type.


== Frequently asked questions ==

= Will these pages show up in my menu? =

No. They are a separate post type and are excluded from the nav menu screens,
so they cannot be added by accident either.

= Will Google index them? =

Yes — that is the point. They are public and included in the WordPress sitemap.
Use the per-page noindex checkbox if you ever want to hold one back.

= The generation failed. What do I do? =

Press "Test the connection" on the Settings tab. It sends one small request and
shows exactly what the API said. A wrong model id is the most common cause by
some distance — model names change often, so copy the current one from your xAI
console. A failed generation still leaves a usable draft written from the
template.

= Is my API key exposed to visitors? =

No. It is stored on your server and only ever used in the admin, when you press
Generate. It is never sent to the browser. The chatbot's key works the same way.

= Can I use a provider other than xAI? =

Yes. The endpoint field takes any OpenAI-compatible chat-completions URL, so
you can point it elsewhere without changing anything else.

= Can I restyle the area pages? =

Copy `templates/single-deskman_area.php` into your theme with the same
filename and the theme's version takes over. The stylesheet reads the theme's
own design tokens, so the bundled template already matches the site.


== Changelog ==

= 5.0.0 =
* Merged the Deskman Chatbot plugin into this one. Settings, knowledge base,
  leads and REST routes carry over untouched; a conflict guard prevents a fatal
  error if the old plugin is still active.
* New: Area pages — location landing pages with a Grok-powered generator, a
  built-in template fallback, bulk generation, cross-linking, an index archive,
  a shortcode, and ProfessionalService / Service / FAQPage / BreadcrumbList
  structured data.
* Area pages join the per-page SEO panel and the theme's SEO engine with no
  theme changes required.
* Dashboard now shows area page and chat lead counts, and flags drafts waiting
  to be reviewed.
* Fixed: text truncation no longer calls `mb_strrpos` unguarded. WordPress
  polyfills `mb_substr` and `mb_strlen` but not `mb_strrpos`, so this would
  have fatalled on a host without the mbstring extension.


== Earlier releases ==

== What changed in 4.1.0 (SEO & AEO) ==

This release adds the search-engine and answer-engine layer the site was
missing. Nothing about the existing content or design changes.

* New "SEO & Business" section in Deskman Studio settings: default meta
  description, homepage title tagline, social share image, logo, X/Twitter
  handle, and the business address / city / region / postal / country / phone
  / map coordinates / price range / founding year used for local-business
  structured data. City and region default to Visakhapatnam, Andhra Pradesh.
* New "Search / SEO" panel on every Page and Project (and Posts): a custom
  search title, meta description and a noindex switch, with a live Google-style
  preview and character counters so a page can be optimised for a chosen
  keyword without guessing at truncation.
* These values are read by the theme (v3.4.0+), which now emits meta
  descriptions, canonical URLs, Open Graph + Twitter cards, a robots policy
  and a full schema.org JSON-LD graph (Organization / local ProfessionalService,
  WebSite with search action, WebPage, BreadcrumbList, a Services ItemList and
  a FAQPage built from the pricing FAQ). This is what Google rich results and
  AI answer engines read to understand and cite the site.

If the theme is older than v3.4.0 the new fields simply store and wait; they
do no harm.

== What changed in 3.0.0 ==

The home page was rebuilt against a new design. This release adds the fields
that page needs and corrects a set of defaults that would otherwise have
changed the page the first time anyone pressed Save.

* 165 new fields, grouped into "Home ·" sections: Hero cards, Reel, Founder
  panel, Latest work, Studio panel, Toolkit, Why Deskman, Trusted & voices,
  Pricing, Showcase, Final CTA, Footer.
* New "Menu feature card" fields for the mega menu's "What we build" column.
  These are separate from the Services fields, which the interior Services
  page uses in title case — sharing them would have forced one wording onto
  both.
* Two glyph defaults corrected. The brand glyph is now ✵ (U+2735) and the
  ticker separator ✶ (U+2736), matching the design. They were previously ✳
  (U+2733) and ✦ (U+2726) — visibly different marks.
* Home-only defaults retuned to the new copy: hero sub-line, intro band, the
  eight hero arc-card captions, and testimonial 1.

== 3.0.1 ==

* New "Home · Hero card clips" section: a clip and a poster for each of the
  eight hero cards. Blank means "use the placeholder bundled with the theme",
  so clearing a field never leaves an empty card.
* The media picker now opens the video library for clip fields. Image fields
  are unchanged.

== Upgrade note ==

If this plugin was saved on this site before upgrading, the old values are
still stored and will keep showing. Two fields are worth checking after the
update, because the old defaults were the wrong characters:

    Brand & colours -> Brand glyph        should be  ✵
    Brand & colours -> Nav ticker strip   separator  ✶

Clear a field and save to fall back to the new default.

== How content reaches the page ==

The theme calls deskman_opt( key, fallback ). A stored value wins; otherwise
the theme's own fallback is used. Every home-page fallback in the theme is
identical to the default defined here, so an untouched install, a saved
install and the original design comp all render the same page. There is a test
for this — see harness/coverage.php in the build notes.

Deactivating the plugin does not break the site. The theme falls back to the
same copy.

== Text conventions ==

Nothing stores HTML in the database. Three plain-text conventions become
markup at render time:

    [square brackets]   highlighted chip
    {curly braces}      small highlighted mark
    | (pipe)            hard line break

A newline inside a textarea also becomes a line break.

== 5.1.0 — per-city page designs, and Groq defaults for the generator ==

WHY

Two things were in the way. Location pages all rendered through one bundled
template, so the only way to change how Warangal looked was to change how every
city looked. And the Locations generator still defaulted to xAI (api.x.ai /
grok-3) while the chatbot defaulted to Groq — so a Groq key saved into
Locations -> Settings, with the endpoint and model left alone, failed with a
bare "model not found".

1. PER-CITY DESIGNS  (new includes/area-designs.php, new area-designs/ folder)

   Content and presentation are now separate. The fields, the CPT, the
   generator and every existing page are untouched; only the template that
   draws them is swappable, one city at a time.

   A design is a folder holding template.php (plus optional style.css,
   script.js, functions.php). Folders are looked for in the child theme, then
   the theme, then this plugin, so custom work placed in the theme survives a
   plugin update.

   A page uses, in order: the design chosen in the new "Page design" box on the
   edit screen; a folder named after its city slug (warangal/ for Warangal); a
   folder whose "Cities:" header lists it; otherwise "default".

   Dropping in a folder called warangal/ is therefore the whole installation
   step, and deleting it returns the page to the default design with its
   content intact. See AREA-DESIGN-GUIDE.md.

   MOVED  templates/single-deskman_area.php
     ->   area-designs/default/template.php

   Byte-identical apart from its header comment, so the default look is
   unchanged. A theme's own single-deskman_area.php still overrides everything,
   exactly as before: the design router runs at priority 30 and steps aside
   when the theme has already claimed the template at 20.

   A design's style.css is enqueued with assets/areas.css as a dependency, so
   it loads after it and can restyle the shared .dsa- rules without
   !important. A deskman-area-design-{name} class is added to <body>.

   The Locations list gains a Design column, so which pages are on a custom
   look is visible at a glance.

2. THE GENERATOR NOW DEFAULTS TO GROQ  (includes/areas.php)

   ai_endpoint  https://api.x.ai/v1/chat/completions -> https://api.groq.com/openai/v1/chat/completions
   ai_model     grok-3 -> openai/gpt-oss-120b

   These are defaults only. Any endpoint and model already saved are left
   exactly as they are; this changes what a site gets when nothing has been
   chosen, and makes one Groq key work for both the chatbot and the generator.

3. THE THREE FAILURES THAT ACTUALLY HAPPEN NOW EXPLAIN THEMSELVES
   (includes/areas-generator.php)

   Provider/model mismatch is caught before the request, because it is
   reachable from the old defaults and the API's own answer ("model not found")
   reads as though the model were retired rather than sent to the wrong
   company. A Grok id against Groq, or a non-Grok id against xAI, now names the
   problem and gives both ways to fix it.

   A decommissioned model is reported as such with the replacement to use.
   Groq shut down llama-3.3-70b-versatile and llama-3.1-8b-instant on
   16 August 2026 for free and developer accounts, which is enough to break a
   setup that worked for months with nothing else changed.

   401/403 and 429 now say "the key was rejected, check it belongs to this
   provider" and "rate limit, wait a minute" instead of a raw status code.

4. THE SETTINGS SCREEN NOW SAYS GROQ, NOT GROK  (includes/areas-generator.php)

   The Locations settings tab was headed "Grok API", showed an xai-… key
   placeholder and pointed at console.x.ai, while the Chatbot screen next door
   was set up for Groq. Two different companies with near-identical names, one
   plugin, two contradictory instructions.

   The section is now "AI provider": a gsk_… placeholder, a link to
   console.groq.com/keys, and a note that the free tier covers page generation.
   The model help names openai/gpt-oss-120b and openai/gpt-oss-20b, and the
   endpoint help gives the Groq URL as the default while still documenting the
   xAI one for anyone who wants it, with a line saying plainly that keys and
   model ids are not interchangeable between the two.

   "Use the Grok API" on the Generate and Bulk tabs is now "Write this page
   with AI", and generated pages are noted as "Written by AI".

5. ONE KEY FOR THE WHOLE PLUGIN

   With the Locations key blank, the generator now falls back to the key saved
   on the Chatbot screen, and the settings screen says so when that is what is
   happening. Both fields remain, so the two can still run on different
   providers or different models — but nobody has to paste the same Groq key
   twice to get started.

6. AREA PAGES CAN NOW SIT AT THE SITE ROOT  (new includes/area-urls.php)

   Location pages lived under a base — /locations/warangal/. Leave the URL base
   empty on the Settings tab and they sit at the root on their own slug
   instead, so a page titled "Best Digital Marketing in Visakhapatnam" is
   reachable at /best-digital-marketing-in-visakhapatnam/. The slug comes from
   the title and stays editable per page.

   WHY THIS IS NOT JUST AN EMPTY REWRITE SLUG

   Registering the post type with an empty slug makes WordPress generate the
   catch-all rule ^([^/]+)/?$ for it. That matches every one-segment URL on the
   site, so /contact/ and /pricing/ would be looked up as area pages first and
   404 when none existed. The base exists to stop exactly that.

   In root mode the post type is therefore registered with rewrite disabled and
   no archive, and one literal rule is added per published area page. Nothing
   else can match a literal slug, so the rest of the site is untouched — this
   was tested with /, /services/, /contact/ and an unknown URL, which return
   200, 200, 200 and a correct 404.

   The slug list is cached and rebuilt whenever an area page is saved, trashed,
   restored or deleted, so permalinks follow the content without a manual
   flush.

   OLD URLS ARE REDIRECTED, NOT DROPPED

   Removing the base changes every location URL at once, which would throw away
   the ranking the pages exist for. The base in use before the change is
   remembered, and /locations/{slug}/ now 301s to the new address. The redirect
   runs only on a 404, so it can never shadow a URL that already resolves.

   COLLISIONS ARE REPORTED, NOT GUESSED

   If an area page's slug is already owned by a real page or post, no rule is
   added — the existing page keeps its URL — and the clash is listed in a
   notice on the Area pages screen so it can be renamed.

   Note: with no base there is no /locations/ archive. The theme's footer strip
   drops its "All locations" pill automatically when the archive is absent.

NOTHING ELSE CHANGED

   The chatbot, leads, SEO meta, the setup wizard, projects and every theme
   option behave exactly as in 5.0.0. No stored content or setting is migrated,
   rewritten or removed.


