<?php
/**
 * Deskman Studio — lead validation & spam filtering.
 *
 * Everything that decides whether a contact submission is a real lead lives
 * here, so the rules can be read in one place and changed without touching the
 * rest of the plugin.
 *
 * THE GUIDING RULE: never silently destroy a lead.
 *
 * Submissions fall into three outcomes, not two:
 *
 *   1. REJECTED  — the visitor made a fixable mistake (bad email, bad phone,
 *                  empty required field). They are sent back to the form with
 *                  a specific message and everything they typed still in the
 *                  fields. Nothing is stored.
 *   2. QUARANTINED — the submission scored over the spam threshold. It IS
 *                  still saved, as a draft enquiry flagged as spam, and no
 *                  notification email is sent. The visitor sees the normal
 *                  success message. If the filter ever gets it wrong the lead
 *                  is sitting in Forms → Spam, not gone.
 *   3. ACCEPTED  — stored, published and emailed as before.
 *
 * Only two things are ever hard-blocked outright: a filled honeypot and an
 * IP over the rate limit. Both are near-certain bots, and both still return
 * the success screen so a bot learns nothing about why it failed.
 *
 * Scoring, not rules. Any single signal here has some false-positive rate, so
 * no single signal rejects on its own — they accumulate, and only the total
 * crossing an editable threshold quarantines the message. That is what keeps
 * a real client on a corporate VPN with JavaScript disabled from being thrown
 * away for tripping two weak checks at once.
 *
 * @package Deskman_Studio_Manager
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/* ==========================================================================
   Small helpers
   ========================================================================== */

/** A settings value as a plain boolean. Checkboxes store '1' / '0'. */
if ( ! function_exists( 'deskman_lead_on' ) ) {
	function deskman_lead_on( $key, $default = true ) {
		$v = deskman_opt( $key, $default ? '1' : '0' );
		return $v === '1' || $v === 1 || $v === true;
	}
}

/** A settings value as an integer, floored at a minimum. */
if ( ! function_exists( 'deskman_lead_int' ) ) {
	function deskman_lead_int( $key, $default, $min = 0 ) {
		$v = (int) deskman_opt( $key, (string) $default );
		return $v < $min ? $default : $v;
	}
}

/**
 * The visitor's IP.
 *
 * Behind Cloudflare / a load balancer, REMOTE_ADDR is the proxy, so the real
 * client sits in a forwarded header. Those headers are trivially spoofable
 * when there is no proxy in front, which is exactly why this is used only for
 * rate limiting and for display in the admin — never for access control. The
 * first entry in X-Forwarded-For is the client; the rest are hops.
 */
if ( ! function_exists( 'deskman_lead_ip' ) ) {
	function deskman_lead_ip() {
		$keys = array( 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' );
		foreach ( $keys as $k ) {
			if ( empty( $_SERVER[ $k ] ) ) { continue; }
			$raw = sanitize_text_field( wp_unslash( $_SERVER[ $k ] ) );
			$first = trim( strtok( $raw, ',' ) );
			$ip = filter_var( $first, FILTER_VALIDATE_IP );
			if ( $ip ) { return $ip; }
		}
		return '';
	}
}

/** The visitor's user agent, trimmed to something safe to store. */
if ( ! function_exists( 'deskman_lead_ua' ) ) {
	function deskman_lead_ua() {
		if ( empty( $_SERVER['HTTP_USER_AGENT'] ) ) { return ''; }
		return substr( sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ), 0, 255 );
	}
}

/**
 * A newline/comma separated admin list, merged with a built-in list.
 * Lowercased, trimmed and de-duplicated so comparisons are cheap later.
 */
if ( ! function_exists( 'deskman_lead_list' ) ) {
	function deskman_lead_list( $option_key, $builtin = array() ) {
		$raw = (string) deskman_opt( $option_key, '' );
		$extra = preg_split( '/[\r\n,]+/', $raw );
		$all = array_merge( (array) $builtin, (array) $extra );
		$all = array_map( 'trim', array_map( 'strtolower', $all ) );
		return array_values( array_unique( array_filter( $all, 'strlen' ) ) );
	}
}

/* ==========================================================================
   Reference data
   ========================================================================== */

/**
 * Throwaway-inbox providers.
 *
 * Deliberately short and conservative. A long scraped blocklist goes stale,
 * bloats every request and eventually blocks a real person — this covers the
 * services that actually show up in agency contact forms, and the admin can
 * extend it from Settings without editing code.
 */
if ( ! function_exists( 'deskman_lead_disposable_domains' ) ) {
	function deskman_lead_disposable_domains() {
		return deskman_lead_list( 'lead_block_domains', array(
			'mailinator.com', 'guerrillamail.com', 'guerrillamail.net', 'sharklasers.com',
			'10minutemail.com', '10minutemail.net', 'tempmail.com', 'temp-mail.org',
			'throwawaymail.com', 'yopmail.com', 'yopmail.fr', 'trashmail.com',
			'getnada.com', 'nada.email', 'dispostable.com', 'maildrop.cc',
			'fakeinbox.com', 'mailnesia.com', 'mytemp.email', 'moakt.com',
			'emailondeck.com', 'tempinbox.com', 'spamgourmet.com', 'mohmal.com',
			'burnermail.io', 'mail-temp.com', 'linshiyouxiang.net', 'grr.la',
			'spam4.me', 'byom.de', 'discard.email', 'tmpmail.org',
		) );
	}
}

/**
 * Near-misses of the big providers.
 *
 * These are the single most valuable check in the whole file. A typo'd email
 * is a real person with real intent whose reply will bounce — catching it at
 * the form is the difference between a lead and nothing. The value is the
 * correct domain, which is offered back as "did you mean…?".
 */
if ( ! function_exists( 'deskman_lead_typo_domains' ) ) {
	function deskman_lead_typo_domains() {
		return array(
			'gmial.com' => 'gmail.com',   'gmai.com'    => 'gmail.com',
			'gmail.co'  => 'gmail.com',   'gmail.con'   => 'gmail.com',
			'gmail.cm'  => 'gmail.com',   'gamil.com'   => 'gmail.com',
			'gmaill.com' => 'gmail.com',  'gnail.com'   => 'gmail.com',
			'gmail.comm' => 'gmail.com',
			'hotmial.com' => 'hotmail.com', 'hotmai.com' => 'hotmail.com',
			'hotmail.co' => 'hotmail.com',  'hotmail.con' => 'hotmail.com',
			'homail.com' => 'hotmail.com',
			'yaho.com'  => 'yahoo.com',   'yahoo.co'    => 'yahoo.com',
			'yahoo.con' => 'yahoo.com',   'yhaoo.com'   => 'yahoo.com',
			'outlok.com' => 'outlook.com', 'outlook.co' => 'outlook.com',
			'oulook.com' => 'outlook.com',
			'rediffmail.co' => 'rediffmail.com',
			'iclould.com' => 'icloud.com', 'icloud.co'  => 'icloud.com',
			'protonmai.com' => 'protonmail.com',
		);
	}
}

/** Shared-mailbox prefixes. A weak signal only — plenty of real enquiries
 *  come from info@ at a small company, so this never rejects on its own. */
if ( ! function_exists( 'deskman_lead_role_prefixes' ) ) {
	function deskman_lead_role_prefixes() {
		return array( 'info', 'admin', 'sales', 'support', 'contact', 'office',
			'noreply', 'no-reply', 'donotreply', 'postmaster', 'webmaster',
			'marketing', 'billing', 'help', 'team' );
	}
}

/** Phrases that characterise unsolicited agency spam rather than enquiries. */
if ( ! function_exists( 'deskman_lead_spam_words' ) ) {
	function deskman_lead_spam_words() {
		return deskman_lead_list( 'lead_block_words', array(
			'seo services', 'first page of google', 'rank your website',
			'guest post', 'link building', 'backlinks', 'do follow link',
			'increase your traffic', 'guaranteed ranking', 'buy followers',
			'cheap price', 'bulk email', 'crypto', 'bitcoin', 'forex',
			'casino', 'betting', 'loan offer', 'viagra', 'cialis',
			'work from home', 'make money online', 'click here now',
			'limited time offer', 'act now', 'wire transfer', 'binary options',
			'telegram me', 'whatsapp me at', 'website audit report',
		) );
	}
}

/* ==========================================================================
   Email validation
   ========================================================================== */

/**
 * Validate an email address for deliverability, not just syntax.
 *
 * Returns array(ok, code, msg, suggest). `suggest` carries a corrected
 * address when the domain looks like a typo, so the form can offer it back.
 *
 * Order matters: syntax, then typo, then disposable, then DNS. The DNS lookup
 * is last because it is the only step that touches the network.
 */
if ( ! function_exists( 'deskman_lead_check_email' ) ) {
	function deskman_lead_check_email( $email ) {
		$email = trim( (string) $email );
		$fail = function ( $code, $msg, $suggest = '' ) {
			return array( 'ok' => false, 'code' => $code, 'msg' => $msg, 'suggest' => $suggest );
		};

		if ( $email === '' ) {
			return $fail( 'email_empty', __( 'Please enter your email address.', 'deskman-studio-manager' ) );
		}
		if ( ! is_email( $email ) ) {
			return $fail( 'email_syntax', __( 'That email address doesn’t look right. Please check it and try again.', 'deskman-studio-manager' ) );
		}

		$parts  = explode( '@', $email );
		$local  = strtolower( $parts[0] );
		$domain = strtolower( end( $parts ) );

		/* A typo is a real person we would otherwise lose to a bounce, so it
		   is worth interrupting them for — and we can tell them exactly what
		   to fix. */
		$typos = deskman_lead_typo_domains();
		if ( isset( $typos[ $domain ] ) && $typos[ $domain ] !== $domain ) {
			$suggest = $local . '@' . $typos[ $domain ];
			return $fail(
				'email_typo',
				sprintf(
					/* translators: %s: the corrected email address. */
					__( 'Did you mean %s? Please check your email address.', 'deskman-studio-manager' ),
					$suggest
				),
				$suggest
			);
		}

		if ( deskman_lead_on( 'lead_block_disposable', true ) ) {
			$disposable = deskman_lead_disposable_domains();
			foreach ( $disposable as $d ) {
				/* Match the domain or any subdomain of it, so mail.yopmail.com
				   is caught alongside yopmail.com — but "notyopmail.com" is not. */
				if ( $domain === $d || substr( $domain, - ( strlen( $d ) + 1 ) ) === '.' . $d ) {
					return $fail( 'email_disposable', __( 'Please use a permanent email address so we can actually reply to you.', 'deskman-studio-manager' ) );
				}
			}
		}

		/* Does the domain accept mail at all? Catches invented domains and
		   dead ones. Guarded three ways: the function does not exist on some
		   Windows hosts, the option can be turned off, and a lookup failure
		   is treated as "cannot tell" rather than "invalid" — a DNS blip must
		   never cost a lead. */
		if ( deskman_lead_on( 'lead_check_mx', true ) && function_exists( 'checkdnsrr' ) ) {
			$has_mx = @checkdnsrr( $domain, 'MX' ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
			$has_a  = $has_mx ? true : @checkdnsrr( $domain, 'A' ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
			if ( ! $has_mx && ! $has_a ) {
				return $fail( 'email_domain', __( 'We couldn’t find that email domain. Please check the address and try again.', 'deskman-studio-manager' ) );
			}
		}

		return array( 'ok' => true, 'code' => '', 'msg' => '', 'suggest' => '',
			'domain' => $domain, 'local' => $local );
	}
}

/* ==========================================================================
   Phone validation
   ========================================================================== */

/**
 * Validate and normalise a phone number.
 *
 * Kept deliberately permissive on format and strict only on substance. The
 * studio takes international enquiries, so anything that insists on an Indian
 * 10-digit pattern would reject real clients. What it does catch is the thing
 * that actually wastes time: placeholder numbers.
 *
 * Returns array(ok, value, code, msg) where `value` is the cleaned number.
 */
if ( ! function_exists( 'deskman_lead_check_phone' ) ) {
	function deskman_lead_check_phone( $raw, $required = true ) {
		$raw = trim( (string) $raw );
		$fail = function ( $code, $msg ) use ( $raw ) {
			return array( 'ok' => false, 'value' => $raw, 'code' => $code, 'msg' => $msg );
		};

		if ( $raw === '' ) {
			if ( $required ) {
				return $fail( 'phone_empty', __( 'Please enter a phone number we can reach you on.', 'deskman-studio-manager' ) );
			}
			return array( 'ok' => true, 'value' => '', 'code' => '', 'msg' => '' );
		}

		/* Keep only what a phone number can contain, then count the digits. */
		$clean  = preg_replace( '/[^0-9+]/', '', $raw );
		$digits = preg_replace( '/[^0-9]/', '', $clean );
		$len    = strlen( $digits );

		/* E.164 allows up to 15 digits; 7 is the shortest realistic national
		   number. Anything outside that is a mistake or a placeholder. */
		if ( $len < 7 || $len > 15 ) {
			return $fail( 'phone_length', __( 'That phone number doesn’t look right. Please include your country or area code.', 'deskman-studio-manager' ) );
		}

		/* 0000000000, 1111111111 — the classic "just let me submit" entry.
		   A single repeated digit is never a real number anywhere, so this is
		   the one pattern safe to reject outright. */
		if ( preg_match( '/^(\d)\1+$/', $digits ) ) {
			return $fail( 'phone_fake', __( 'Please enter a real phone number.', 'deskman-studio-manager' ) );
		}

		/* NOT rejected here: straight runs like 9876543210. That looks like a
		   placeholder, and often is — but it is also a real Indian mobile
		   prefix (98, 99, 97 are all live series), so rejecting it would turn
		   away genuine local leads, which is the one failure this whole file
		   exists to avoid. It is passed to the spam scorer as a weak signal
		   instead, where it can only matter alongside other evidence. */

		/* Normalise for storage: keep a leading + if the visitor gave one,
		   because it is the only part of the format that carries meaning. */
		$value = ( strpos( $clean, '+' ) === 0 ? '+' : '' ) . $digits;

		return array( 'ok' => true, 'value' => $value, 'code' => '', 'msg' => '' );
	}
}

/* ==========================================================================
   Spam scoring
   ========================================================================== */

/**
 * Score a submission. Higher is more suspicious.
 *
 * Returns array(score, reasons). The reasons are stored with quarantined
 * enquiries so that when something is wrongly filtered, the admin can see
 * exactly which rule did it and adjust rather than guess.
 */
if ( ! function_exists( 'deskman_lead_score' ) ) {
	function deskman_lead_score( $d ) {
		$score   = 0;
		$reasons = array();
		$add = function ( $n, $why ) use ( &$score, &$reasons ) {
			$score += $n;
			$reasons[] = $why . ' (+' . $n . ')';
		};

		$msg   = (string) $d['msg'];
		$name  = (string) $d['name'];
		$lower = strtolower( $msg . ' ' . $name );

		/* --- did a browser actually render this form? ------------------- */
		if ( empty( $d['js_ok'] ) ) {
			$add( 2, 'No JavaScript token — form fields were posted without rendering the page' );
		}

		/* --- how long did it take to fill in? ---------------------------- */
		$elapsed = isset( $d['elapsed'] ) ? (int) $d['elapsed'] : -1;
		$min     = deskman_lead_int( 'lead_min_seconds', 4, 0 );
		if ( $elapsed < 0 ) {
			$add( 2, 'Missing or invalid form timestamp' );
		} elseif ( $elapsed < $min ) {
			$add( 3, sprintf( 'Submitted in %ds — faster than a person can type', $elapsed ) );
		}

		/* --- links: the whole point of most form spam -------------------- */
		$links = preg_match_all( '#(https?://|www\.)#i', $msg );
		if ( $links === 1 ) {
			$add( 1, 'One link in the message' );
		} elseif ( $links === 2 ) {
			$add( 2, 'Two links in the message' );
		} elseif ( $links > 2 ) {
			/* Decisive on its own. Nobody opens an enquiry with three links;
			   every observed case is a link farm. */
			$add( 5, $links . ' links in the message' );
		}
		/* BBCode / raw anchor tags never appear in a genuine enquiry. */
		if ( preg_match( '#\[url[=\]]|</?a\s|href\s*=#i', $msg ) ) {
			$add( 4, 'Message contains link markup' );
		}

		/* --- sales-pitch vocabulary -------------------------------------- */
		$hits = array();
		foreach ( deskman_lead_spam_words() as $w ) {
			if ( $w !== '' && strpos( $lower, $w ) !== false ) { $hits[] = $w; }
		}
		if ( $hits ) {
			$add( min( 5, 3 * count( $hits ) ), 'Blocked phrase: ' . implode( ', ', array_slice( $hits, 0, 4 ) ) );
		}

		/* --- shape of the message ---------------------------------------- */
		$len = function_exists( 'mb_strlen' ) ? mb_strlen( trim( $msg ) ) : strlen( trim( $msg ) );
		if ( $len < 15 ) {
			$add( 2, 'Message is only ' . $len . ' characters' );
		}
		if ( $len > 30 && strpos( trim( $msg ), ' ' ) === false ) {
			$add( 3, 'Message is one long unbroken string' );
		}
		if ( $len > 20 && $msg === strtoupper( $msg ) && preg_match( '/[A-Z]/', $msg ) ) {
			$add( 1, 'Message is entirely upper case' );
		}

		/* --- shape of the name -------------------------------------------- */
		if ( preg_match( '#https?://|www\.#i', $name ) ) {
			/* Also decisive alone: a person's name is never a URL. */
			$add( 5, 'Name field contains a URL' );
		}
		if ( $name !== '' && trim( $name ) === trim( $msg ) ) {
			$add( 2, 'Name and message are identical' );
		}

		/* --- placeholder-looking phone: weak, demoted from a hard reject ---
		   9876543210 is both the number people type to skip the field and a
		   real Indian mobile prefix, so it can contribute to a verdict but
		   must never produce one. */
		$digits = preg_replace( '/[^0-9]/', '', (string) $d['phone'] );
		if ( strlen( $digits ) >= 8 ) {
			$asc = '01234567890123456789';
			if ( strpos( $asc, $digits ) !== false || strpos( strrev( $asc ), $digits ) !== false ) {
				$add( 1, 'Phone number is a straight digit run' );
			}
		}

		/* --- shared mailbox: weak, never decisive on its own --------------- */
		if ( ! empty( $d['email_local'] ) && in_array( $d['email_local'], deskman_lead_role_prefixes(), true ) ) {
			$add( 1, 'Shared mailbox address' );
		}

		/* --- non-Latin script, off by default ------------------------------
		   The studio writes in English, so Cyrillic bodies are almost always
		   spam — but "almost always" is not good enough to enable by default
		   for a business that might one day want those enquiries. */
		if ( deskman_lead_on( 'lead_block_cyrillic', false ) && preg_match( '/[\x{0400}-\x{04FF}]/u', $msg ) ) {
			$add( 4, 'Message is in Cyrillic script' );
		}

		/* --- has this exact message arrived before? ------------------------ */
		if ( ! empty( $d['dupe'] ) ) {
			$add( 4, 'Identical message already received recently' );
		}

		/* --- client fingerprint -------------------------------------------- */
		$ua = (string) $d['ua'];
		if ( $ua === '' ) {
			$add( 2, 'No user agent' );
		} elseif ( preg_match( '/(curl|wget|python|scrapy|libwww|httpclient|go-http|okhttp|java\/)/i', $ua ) ) {
			$add( 4, 'Automated client: ' . substr( $ua, 0, 60 ) );
		}

		return array( 'score' => $score, 'reasons' => $reasons );
	}
}

/* ==========================================================================
   Rate limiting & replay protection
   ========================================================================== */

/** How many submissions this IP has made in the current window. */
if ( ! function_exists( 'deskman_lead_rate_key' ) ) {
	function deskman_lead_rate_key( $ip ) {
		return 'dsk_lead_rate_' . md5( $ip );
	}
}

/**
 * Is this IP over its hourly limit?
 *
 * Shared offices and mobile carriers put many people behind one address, so
 * the default ceiling is high enough that a real team never hits it while a
 * script hammering the endpoint does.
 */
if ( ! function_exists( 'deskman_lead_throttled' ) ) {
	function deskman_lead_throttled( $ip ) {
		if ( $ip === '' ) { return false; }
		$max = deskman_lead_int( 'lead_rate_limit', 5, 1 );
		$n = (int) get_transient( deskman_lead_rate_key( $ip ) );
		return $n >= $max;
	}
}

/** Record one submission against this IP for the next hour. */
if ( ! function_exists( 'deskman_lead_note_submission' ) ) {
	function deskman_lead_note_submission( $ip ) {
		if ( $ip === '' ) { return; }
		$key = deskman_lead_rate_key( $ip );
		$n = (int) get_transient( $key );
		set_transient( $key, $n + 1, HOUR_IN_SECONDS );
	}
}

/**
 * Has this exact message already been received?
 * Keyed on email + message so a genuine follow-up with new wording still
 * gets through, while a script replaying the same payload does not.
 */
if ( ! function_exists( 'deskman_lead_is_duplicate' ) ) {
	function deskman_lead_is_duplicate( $email, $msg ) {
		$key = 'dsk_lead_dupe_' . md5( strtolower( $email ) . '|' . trim( $msg ) );
		if ( get_transient( $key ) ) { return true; }
		set_transient( $key, 1, DAY_IN_SECONDS );
		return false;
	}
}

/* ==========================================================================
   The form timestamp token
   ========================================================================== */

/**
 * A signed "this form was rendered at" stamp.
 *
 * Signed rather than plain so a bot cannot post an older timestamp to fake a
 * slow, human-looking fill. wp_hash() keys it to this site's salts.
 */
if ( ! function_exists( 'deskman_lead_form_token' ) ) {
	function deskman_lead_form_token() {
		$ts = time();
		return $ts . '.' . substr( wp_hash( $ts . '|deskman_lead_form' ), 0, 20 );
	}
}

/**
 * Seconds between rendering the form and submitting it.
 * Returns -1 when the token is missing, malformed, forged or older than a day.
 */
if ( ! function_exists( 'deskman_lead_token_age' ) ) {
	function deskman_lead_token_age( $token ) {
		$token = (string) $token;
		if ( strpos( $token, '.' ) === false ) { return -1; }
		list( $ts, $sig ) = explode( '.', $token, 2 );
		if ( ! ctype_digit( (string) $ts ) ) { return -1; }
		$expect = substr( wp_hash( $ts . '|deskman_lead_form' ), 0, 20 );
		if ( ! hash_equals( $expect, (string) $sig ) ) { return -1; }
		$age = time() - (int) $ts;
		if ( $age < 0 || $age > DAY_IN_SECONDS ) { return -1; }
		return $age;
	}
}

/* ==========================================================================
   Re-populating the form after a rejection
   ========================================================================== */

/**
 * Park what the visitor typed so the form can be handed back filled in.
 *
 * A short-lived transient keyed by a random token, with the token travelling
 * in the redirect URL. Storing the values in the URL itself would leak them
 * into server logs, browser history and the Referer header; a session would
 * mean starting one for every anonymous visitor.
 */
if ( ! function_exists( 'deskman_lead_stash' ) ) {
	function deskman_lead_stash( $values, $error_code, $error_msg, $suggest = '' ) {
		$token = wp_generate_password( 20, false, false );
		set_transient( 'dsk_lead_back_' . $token, array(
			'values'  => $values,
			'code'    => $error_code,
			'msg'     => $error_msg,
			'suggest' => $suggest,
		), 10 * MINUTE_IN_SECONDS );
		return $token;
	}
}

/** Read a stash back. Returns array or null. Does not delete it: a refresh
 *  of the returned page must not blank the form. */
if ( ! function_exists( 'deskman_lead_recall' ) ) {
	function deskman_lead_recall( $token ) {
		$token = preg_replace( '/[^A-Za-z0-9]/', '', (string) $token );
		if ( $token === '' ) { return null; }
		$data = get_transient( 'dsk_lead_back_' . $token );
		return is_array( $data ) ? $data : null;
	}
}

/* ==========================================================================
   The handler
   ========================================================================== */

add_action( 'admin_post_nopriv_deskman_contact', 'deskman_handle_contact' );
add_action( 'admin_post_deskman_contact', 'deskman_handle_contact' );

/**
 * Contact form handler.
 *
 * Replaces the original in deskman-studio-manager.php. The wire format is
 * unchanged — same action, same nonce, same cf_* field names — so any existing
 * form markup still posts here successfully. cf_phone and the three anti-bot
 * fields are additive and optional: a form that does not send them still works,
 * it just scores slightly higher.
 */
if ( ! function_exists( 'deskman_handle_contact' ) ) {
	function deskman_handle_contact() {

		$back = wp_get_referer();
		if ( ! $back ) { $back = deskman_url( 'contact' ); }
		/* Strip our own state off the referer so repeated failures do not
		   stack ?deskman_contact=error&dsk=… fragments onto the URL. */
		$back = remove_query_arg( array( 'deskman_contact', 'dsk', 'reason' ), $back );

		$ok_url = add_query_arg( 'deskman_contact', 'sent', $back );

		$reject = function ( $values, $code, $msg, $suggest = '' ) use ( $back ) {
			$token = deskman_lead_stash( $values, $code, $msg, $suggest );
			wp_safe_redirect( add_query_arg( array(
				'deskman_contact' => 'error',
				'dsk'             => $token,
			), $back ) );
			exit;
		};

		/* --- 1. honeypots -------------------------------------------------
		   Two of them: the original off-screen cf_hp, and cf_url, whose name
		   is the kind a scraper fills on sight. Either being non-empty is a
		   bot. Show the success screen so it learns nothing. */
		if ( ! empty( $_POST['cf_hp'] ) || ! empty( $_POST['cf_url'] ) ) {
			wp_safe_redirect( $ok_url ); exit;
		}

		/* --- 2. nonce ------------------------------------------------------ */
		if ( ! isset( $_POST['deskman_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['deskman_nonce'] ), 'deskman_contact' ) ) {
			$reject( array(), 'nonce', __( 'Your session expired before the message could be sent. Please try again.', 'deskman-studio-manager' ) );
		}

		/* --- 3. rate limit ------------------------------------------------- */
		$ip = deskman_lead_ip();
		if ( deskman_lead_throttled( $ip ) ) {
			wp_safe_redirect( $ok_url ); exit;
		}

		/* --- 4. collect ----------------------------------------------------- */
		$g = function ( $k ) { return isset( $_POST[ $k ] ) ? sanitize_text_field( wp_unslash( $_POST[ $k ] ) ) : ''; };

		$name     = $g( 'cf_name' );
		$email    = isset( $_POST['cf_email'] ) ? sanitize_email( wp_unslash( $_POST['cf_email'] ) ) : '';
		$phone_in = $g( 'cf_phone' );
		$company  = $g( 'cf_company' );
		$website  = isset( $_POST['cf_website'] ) ? esc_url_raw( trim( (string) wp_unslash( $_POST['cf_website'] ) ) ) : '';
		$ptype    = $g( 'cf_ptype' );
		$budget   = $g( 'cf_budget' );
		$timeline = $g( 'cf_timeline' );
		$msg      = isset( $_POST['cf_msg'] ) ? sanitize_textarea_field( wp_unslash( $_POST['cf_msg'] ) ) : '';
		/* Optional: the plan a visitor picked on the Pricing page. Purely
		   informational — never required, never validated — so it can never
		   block a submission. */
		$plan     = $g( 'cf_plan' );

		/* Everything the visitor typed, so a rejection can hand it all back.
		   The phone goes back as entered, not normalised, or correcting a
		   different field would silently rewrite their number. */
		$values = compact( 'name', 'email', 'phone_in', 'company', 'website', 'ptype', 'budget', 'timeline', 'msg' );

		/* --- 5. required fields ---------------------------------------------- */
		if ( $name === '' ) {
			$reject( $values, 'name', __( 'Please tell us your name.', 'deskman-studio-manager' ) );
		}
		if ( $msg === '' ) {
			$reject( $values, 'msg', __( 'Please tell us a little about the project.', 'deskman-studio-manager' ) );
		}
		if ( deskman_lead_on( 'lead_ptype_required', true ) && $ptype === '' ) {
			$reject( $values, 'ptype', __( 'Please choose a project type.', 'deskman-studio-manager' ) );
		}

		/* --- 6. email --------------------------------------------------------- */
		$echk = deskman_lead_check_email( $email );
		if ( ! $echk['ok'] ) {
			$reject( $values, $echk['code'], $echk['msg'], $echk['suggest'] );
		}

		/* --- 7. phone ---------------------------------------------------------- */
		/* Footer newsletter signups carry cf_kind=newsletter and no phone. */
		$is_news        = ( $g( 'cf_kind' ) === 'newsletter' );
		$phone_required = deskman_lead_on( 'lead_phone_required', true ) && ! $is_news;
		$pchk = deskman_lead_check_phone( $phone_in, $phone_required );
		if ( ! $pchk['ok'] ) {
			$reject( $values, $pchk['code'], $pchk['msg'] );
		}
		$phone = $pchk['value'];

		/* --- 8. score ------------------------------------------------------------ */
		$scored = deskman_lead_score( array(
			'name'        => $name,
			'msg'         => $msg,
			'phone'       => $phone,
			'email_local' => isset( $echk['local'] ) ? $echk['local'] : '',
			'js_ok'       => ! empty( $_POST['cf_js'] ),
			'elapsed'     => deskman_lead_token_age( isset( $_POST['cf_t'] ) ? wp_unslash( $_POST['cf_t'] ) : '' ),
			'ua'          => deskman_lead_ua(),
			'dupe'        => deskman_lead_is_duplicate( $email, $msg ),
		) );

		$threshold = deskman_lead_int( 'lead_spam_threshold', 5, 1 );
		$is_spam   = $scored['score'] >= $threshold;

		deskman_lead_note_submission( $ip );

		/* --- 9. store ------------------------------------------------------------
		   Quarantined messages are saved as drafts, never discarded, so a
		   false positive is recoverable from Forms → Spam. */
		if ( post_type_exists( 'deskman_enquiry' ) ) {
			$eid = wp_insert_post( array(
				'post_type'    => 'deskman_enquiry',
				'post_status'  => $is_spam ? 'draft' : 'publish',
				'post_title'   => ( $is_spam ? '[Spam] ' : '' ) . $name . ' — ' . ( $ptype ? $ptype : 'Enquiry' ),
				'post_content' => $msg,
			) );
			if ( $eid && ! is_wp_error( $eid ) ) {
				update_post_meta( $eid, '_deskman_email', $email );
				update_post_meta( $eid, '_deskman_phone', $phone );
				update_post_meta( $eid, '_deskman_company', $company );
				update_post_meta( $eid, '_deskman_website', $website );
				update_post_meta( $eid, '_deskman_ptype', $ptype );
				if ( $plan !== '' ) { update_post_meta( $eid, '_deskman_plan', $plan ); }
				update_post_meta( $eid, '_deskman_budget', $budget );
				update_post_meta( $eid, '_deskman_timeline', $timeline );
				update_post_meta( $eid, '_deskman_ip', $ip );
				update_post_meta( $eid, '_deskman_ua', deskman_lead_ua() );
				update_post_meta( $eid, '_deskman_score', (int) $scored['score'] );
				if ( $scored['reasons'] ) {
					update_post_meta( $eid, '_deskman_flags', implode( "\n", $scored['reasons'] ) );
				}
				if ( $is_spam ) { update_post_meta( $eid, '_deskman_spam', '1' ); }
			}
		}

		/* --- 10. notify ------------------------------------------------------------
		   Quarantined messages are deliberately not emailed: the whole point
		   is to keep the inbox clean. They remain visible in the admin. */
		if ( ! $is_spam ) {
			$to      = deskman_opt( 'contact_email', get_option( 'admin_email' ) );
			$subject = sprintf( 'New enquiry from %s — Deskman Studio', $name );
			$lines   = array( "Name: {$name}", "Email: {$email}" );
			if ( $plan )     { $lines[] = "Selected plan: {$plan}"; }
			if ( $phone )    { $lines[] = "Phone: {$phone}"; }
			if ( $company )  { $lines[] = "Company: {$company}"; }
			if ( $website )  { $lines[] = "Current website: {$website}"; }
			if ( $ptype )    { $lines[] = "Project type: {$ptype}"; }
			if ( $budget )   { $lines[] = "Budget: {$budget}"; }
			if ( $timeline ) { $lines[] = "Timeline: {$timeline}"; }
			$body    = implode( "\n", $lines ) . "\n\n" . $msg . "\n";
			$headers = array( 'Reply-To: ' . $name . ' <' . $email . '>' );
			wp_mail( $to, $subject, $body, $headers );
		}

		wp_safe_redirect( $ok_url );
		exit;
	}
}
