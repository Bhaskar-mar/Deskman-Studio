<?php
/**
 * Deskman Studio Manager — Area page generator.
 *
 * Turns "Visakhapatnam + Digital Marketing" into a complete, filled-in area
 * page. Two engines sit behind one function:
 *
 *   1. An OpenAI-compatible chat API — Groq (api.groq.com) by default, and
 *      anything else OpenAI-shaped, including xAI, by changing the endpoint.
 *      The model is asked for a strict JSON object whose keys
 *      map one-to-one onto the fields in areas.php. It writes the copy; it
 *      never touches the database directly.
 *   2. A built-in template. Used when no API key is set, and as the automatic
 *      fallback whenever a call fails or comes back unparseable — so the
 *      button always produces a usable draft rather than an error.
 *
 * Two rules shape the prompt, and they matter more than the wording:
 *
 *   - Nothing is published. Every generated page is created as a draft, so a
 *     person reads it before it is public. Programmatic local pages are worth
 *     doing; publishing them unread is how a site ends up with fifty pages of
 *     near-identical text and a manual action for spam.
 *   - Nothing is invented. The model is told, repeatedly, not to produce
 *     statistics, client names, awards, review counts or prices. Those are the
 *     details that make a local page persuasive, which is exactly why a
 *     fabricated one is a real problem — it is a claim the studio would have
 *     to stand behind.
 *
 * @package Deskman_Studio_Manager
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/* ==========================================================================
   The AI client (Groq by default; any OpenAI-compatible endpoint)
   ========================================================================== */

/**
 * Ask the configured model for one JSON object.
 *
 * Returns the decoded array on success, or a WP_Error carrying the provider's
 * own message. Surfacing that message rather than a generic failure is
 * deliberate: nearly every real problem here is a wrong model id or an expired
 * key, and the API says so precisely.
 */
function deskman_area_ai_request( $system, $user, $timeout = 90 ) {
	$key      = trim( (string) deskman_area_opt( 'ai_key' ) );
	$endpoint = trim( (string) deskman_area_opt( 'ai_endpoint' ) );
	$model    = trim( (string) deskman_area_opt( 'ai_model' ) );

	/* One key for the whole plugin. The chatbot and the generator each keep
	   their own field, which is right — they can sit on different providers or
	   different models — but nobody should have to paste the same Groq key
	   twice to get started. With this field empty, borrow the chatbot's. */
	if ( $key === '' && function_exists( 'deskman_cb_opt' ) ) {
		$key = trim( (string) deskman_cb_opt( 'ai_key' ) );
	}

	if ( $key === '' )      { return new WP_Error( 'no_key', __( 'No API key saved.', 'deskman-studio-manager' ) ); }
	if ( $endpoint === '' )  { return new WP_Error( 'no_endpoint', __( 'No API endpoint saved.', 'deskman-studio-manager' ) ); }
	if ( $model === '' )     { return new WP_Error( 'no_model', __( 'No model name saved.', 'deskman-studio-manager' ) ); }

	/* Provider and model have to agree. Sending Groq a Grok id (or the
	   reverse) returns a bare "model not found", which reads like the model is
	   gone rather than like it was sent to the wrong company. Both of these
	   were reachable from the old defaults, so catch them before the call. */
	$is_groq = ( stripos( $endpoint, 'api.groq.com' ) !== false );
	$is_xai  = ( stripos( $endpoint, 'api.x.ai' ) !== false );
	if ( $is_groq && stripos( $model, 'grok' ) === 0 ) {
		return new WP_Error( 'mismatch', sprintf(
			/* translators: %s: the model id that was saved. */
			__( 'The endpoint is Groq but the model is "%s", which is an xAI (Grok) model. Under Locations → Settings, use a Groq model such as openai/gpt-oss-120b, or change the endpoint to https://api.x.ai/v1/chat/completions.', 'deskman-studio-manager' ),
			$model
		) );
	}
	if ( $is_xai && stripos( $model, 'grok' ) !== 0 ) {
		return new WP_Error( 'mismatch', sprintf(
			/* translators: %s: the model id that was saved. */
			__( 'The endpoint is xAI but the model is "%s", which is not a Grok model. Under Locations → Settings, use a Grok model, or change the endpoint to https://api.groq.com/openai/v1/chat/completions.', 'deskman-studio-manager' ),
			$model
		) );
	}

	$payload = array(
		'model'       => $model,
		'messages'    => array(
			array( 'role' => 'system', 'content' => $system ),
			array( 'role' => 'user',   'content' => $user ),
		),
		'temperature' => 0.7,
		/* Ask for JSON mode where the provider supports it. Providers that do
		   not simply ignore the parameter, and the parser below copes with a
		   fenced or chatty reply anyway. */
		'response_format' => array( 'type' => 'json_object' ),
	);

	$resp = wp_remote_post( $endpoint, array(
		'timeout' => (int) $timeout,
		'headers' => array(
			'Authorization' => 'Bearer ' . $key,
			'Content-Type'  => 'application/json',
		),
		'body'    => wp_json_encode( $payload ),
	) );

	if ( is_wp_error( $resp ) ) {
		return new WP_Error( 'http', $resp->get_error_message() );
	}

	$code = (int) wp_remote_retrieve_response_code( $resp );
	$body = json_decode( wp_remote_retrieve_body( $resp ), true );

	if ( $code < 200 || $code >= 300 ) {
		$msg = '';
		if ( is_array( $body ) ) {
			if ( isset( $body['error']['message'] ) )   { $msg = (string) $body['error']['message']; }
			elseif ( isset( $body['error'] ) && is_string( $body['error'] ) ) { $msg = $body['error']; }
			elseif ( isset( $body['message'] ) )        { $msg = (string) $body['message']; }
		}
		if ( $msg === '' ) { $msg = wp_remote_retrieve_response_message( $resp ); }

		/* Turn the two failures that actually happen into instructions.
		   Groq retired llama-3.3-70b-versatile and llama-3.1-8b-instant on
		   16 August 2026 for free and developer accounts, so a setup that
		   worked for months starts failing here with nothing else changed. */
		$code_str = ( is_array( $body ) && isset( $body['error']['code'] ) ) ? (string) $body['error']['code'] : '';
		if ( $code_str === 'model_decommissioned' || $code_str === 'model_not_found' || stripos( $msg, 'decommissioned' ) !== false ) {
			return new WP_Error( 'model_gone', sprintf(
				/* translators: 1: the model id that was saved, 2: the provider's own message. */
				__( 'The model "%1$s" is no longer available from this provider. Pick a current one under Locations → Settings — on Groq, openai/gpt-oss-120b writes the best copy and openai/gpt-oss-20b is faster. (Provider said: %2$s)', 'deskman-studio-manager' ),
				$model,
				$msg
			) );
		}
		if ( $code === 401 || $code === 403 ) {
			return new WP_Error( 'auth', sprintf(
				/* translators: %s: the message returned by the API. */
				__( 'The API key was rejected. Check it under Locations → Settings, and make sure it belongs to the provider the endpoint points at. (Provider said: %s)', 'deskman-studio-manager' ),
				$msg
			) );
		}
		if ( $code === 429 ) {
			return new WP_Error( 'rate', sprintf(
				/* translators: %s: the message returned by the API. */
				__( 'Rate limit reached. Wait a minute and generate again, or generate fewer pages at a time. (Provider said: %s)', 'deskman-studio-manager' ),
				$msg
			) );
		}

		/* translators: 1: HTTP status code, 2: message returned by the API. */
		return new WP_Error( 'api', sprintf( __( 'API returned %1$d — %2$s', 'deskman-studio-manager' ), $code, $msg ) );
	}

	$text = isset( $body['choices'][0]['message']['content'] ) ? (string) $body['choices'][0]['message']['content'] : '';
	if ( trim( $text ) === '' ) {
		return new WP_Error( 'empty', __( 'The model returned an empty reply.', 'deskman-studio-manager' ) );
	}

	$data = deskman_area_parse_json( $text );
	if ( ! is_array( $data ) ) {
		return new WP_Error( 'parse', __( 'The model did not return readable JSON. Try again, or use a different model.', 'deskman-studio-manager' ) );
	}
	return $data;
}

/**
 * Pull a JSON object out of a model reply.
 *
 * Handles the three shapes that turn up in practice: clean JSON, JSON inside a
 * ```json fence, and JSON with a sentence of commentary either side.
 */
function deskman_area_parse_json( $text ) {
	$text = trim( (string) $text );

	$direct = json_decode( $text, true );
	if ( is_array( $direct ) ) { return $direct; }

	if ( preg_match( '/```(?:json)?\s*(.+?)```/is', $text, $m ) ) {
		$fenced = json_decode( trim( $m[1] ), true );
		if ( is_array( $fenced ) ) { return $fenced; }
	}

	$start = strpos( $text, '{' );
	$end   = strrpos( $text, '}' );
	if ( $start !== false && $end !== false && $end > $start ) {
		$slice = substr( $text, $start, $end - $start + 1 );
		$loose = json_decode( $slice, true );
		if ( is_array( $loose ) ) { return $loose; }
	}
	return null;
}

/* ==========================================================================
   Prompting
   ========================================================================== */

/** The instructions that govern every generated page. */
function deskman_area_system_prompt() {
	$brand = trim( (string) deskman_opt( 'brand_name', 'Deskman' ) . ' ' . deskman_opt( 'brand_sub', 'Studio' ) );
	$tone  = trim( (string) deskman_area_opt( 'ai_tone' ) );
	$extra = trim( (string) deskman_area_opt( 'ai_extra' ) );

	$out  = "You write local landing pages for {$brand}, an independent web design, WordPress, branding and digital marketing studio in Visakhapatnam, India.\n\n";
	$out .= "You reply with ONE JSON object and nothing else. No commentary, no markdown fence.\n\n";

	$out .= "== What you must never do ==\n";
	$out .= "1. Never invent statistics, percentages, growth figures, rankings, review scores or years in business.\n";
	$out .= "2. Never invent client names, case studies, testimonials, awards or certifications.\n";
	$out .= "3. Never state a price, a discount or a guarantee of results or rankings.\n";
	$out .= "4. Never claim offices, staff numbers or a physical address in the target city. The studio works with clients there; it is not headquartered in every city it serves.\n";
	$out .= "5. Never write filler that would read identically for any other city. If a sentence would survive a find-and-replace of the city name, rewrite it.\n\n";

	$out .= "== How to write ==\n";
	$out .= "Use the target keyword naturally in the H1, once in the opening paragraph, and in one or two headings. Nowhere else — repeated phrasing reads as spam to both people and search engines.\n";
	$out .= "Write for a business owner in that city who is deciding who to call. Concrete, calm, specific. Short paragraphs. Indian English.\n";
	$out .= "Ground the page in the actual place: the industries that city is known for, how businesses there tend to find customers, what a local audience searches for. This is the part that makes a local page worth publishing.\n";
	if ( $tone !== '' ) { $out .= "Tone: {$tone}\n"; }
	if ( $extra !== '' ) { $out .= "\n== Extra instructions from the studio ==\n{$extra}\n"; }

	$out .= "\n== The JSON object ==\n";
	$out .= "Return exactly these keys:\n";
	$out .= '{
  "title": "the page title, containing the target keyword, under 65 characters",
  "slug": "url-friendly-slug",
  "seo_title": "search result title, under 60 characters, keyword near the front",
  "seo_desc": "meta description, 140-155 characters, written to earn a click",
  "hero_eyebrow": "3-6 word label above the headline",
  "hero_h": "the H1, contains the target keyword, under 70 characters",
  "hero_sub": "40-60 words under the headline",
  "cta_label": "hero button text, 2-4 words",
  "stats": [{"value":"short value","label":"short label"}],
  "intro_h": "heading for the intro section",
  "intro_body": "3 paragraphs, 90-130 words each, separated by a blank line, specific to this city",
  "services_h": "heading for the services section",
  "services": [{"title":"service name","description":"25-40 words"}],
  "why_h": "heading for the why-us section",
  "why": [{"title":"reason","description":"25-40 words"}],
  "process_h": "heading for the process section",
  "process": [{"title":"step name","description":"20-30 words"}],
  "faq_h": "heading for the FAQ section",
  "faq": [{"question":"a question someone in this city would actually type","answer":"40-70 words"}],
  "cta_h": "closing call to action heading",
  "cta_sub": "one sentence under it",
  "cta_btn": "button label, 2-4 words",
  "localities": ["real neighbourhoods or business areas in the target city"]
}';
	$out .= "\n\nCounts: 3 stats, 6 services, 3 why entries, 5 process steps, 6 FAQs, 6-10 localities.\n";
	$out .= "For \"stats\", use qualitative values that are true of any careful studio (for example \"In-house\" / \"Design & build\", \"1-2 days\" / \"Reply time\"). Never a number you cannot verify.\n";
	$out .= "For \"localities\", use real, well-known areas of the target city only. If you are unsure of the city's geography, return fewer entries rather than invented ones.";

	return $out;
}

/** The per-page brief. */
function deskman_area_user_prompt( $args ) {
	$city    = $args['city'];
	$service = $args['service'];
	$keyword = $args['keyword'];
	$region  = $args['region'];
	$second  = trim( (string) $args['keywords'] );
	$notes   = trim( (string) $args['notes'] );

	$out  = "Write the area page for:\n\n";
	$out .= "City: {$city}\n";
	if ( $region !== '' ) { $out .= "State / region: {$region}\n"; }
	$out .= "Service focus: {$service}\n";
	$out .= "Primary target keyword: {$keyword}\n";
	if ( $second !== '' ) { $out .= "Secondary keywords to weave in naturally: {$second}\n"; }
	if ( $notes !== '' )  { $out .= "\nSpecific notes for this page:\n{$notes}\n"; }

	/* Feed the studio's own service copy in as reference so the generated page
	   describes the real offering rather than a generic agency menu. */
	$svc = array();
	if ( function_exists( 'dm_services' ) ) {
		foreach ( dm_services() as $s ) { $svc[] = '- ' . $s['title'] . ': ' . $s['short']; }
	}
	if ( $svc ) {
		$out .= "\nWhat the studio actually offers (base the services section on these, worded for this city):\n" . implode( "\n", $svc ) . "\n";
	}

	$out .= "\nReturn the JSON object now.";
	return $out;
}

/* ==========================================================================
   The built-in fallback
   ========================================================================== */

/**
 * A complete page written without the API.
 *
 * This is not a placeholder to be replaced later — it is a real, publishable
 * page with the city and service woven through it, so the feature works on day
 * one with no key, and so a failed API call still leaves a usable draft rather
 * than an empty post. It is intentionally free of any claim the studio has not
 * made: no numbers, no clients, no prices.
 */
function deskman_area_fallback_content( $args ) {
	$city    = $args['city'];
	$service = $args['service'];
	$keyword = $args['keyword'];
	$region  = trim( (string) $args['region'] );
	$where   = $city . ( $region !== '' ? ', ' . $region : '' );
	$brand   = trim( (string) deskman_opt( 'brand_name', 'Deskman' ) . ' ' . deskman_opt( 'brand_sub', 'Studio' ) );
	$lc      = strtolower( $service );

	return array(
		'title'     => $keyword,
		'seo_title' => deskman_area_cap( $keyword . ' | ' . $brand, 60 ),
		'seo_desc'  => deskman_area_cap( "Looking for {$lc} in {$city}? {$brand} designs, builds and markets websites that bring local businesses real enquiries. Talk to us about your project.", 155 ),

		'hero_eyebrow' => strtoupper( $service . ' — ' . $city ),
		'hero_h'       => $keyword,
		'hero_sub'     => "{$brand} works with businesses across {$where} on {$lc} that is built to be measured. Design, development and campaigns are handled in-house, so the person who scopes your project is the person who delivers it.",
		'cta_label'    => 'Start a project',

		'stats' => array(
			array( 'value' => 'In-house', 'label' => 'Design & build' ),
			array( 'value' => 'End to end', 'label' => 'Idea to launch' ),
			array( 'value' => '1–2 days', 'label' => 'Typical reply time' ),
		),

		'intro_h'    => "{$service} for businesses in {$city}",
		'intro_body' =>
			"Most businesses in {$city} are not short of competition — they are short of visibility at the moment someone is ready to buy. Somebody searches, compares two or three names, and picks one. If you are not in that shortlist, the quality of your work never gets a hearing. That is the problem {$lc} exists to solve, and it is the one we start from.\n\n" .
			"We work as one small team across strategy, design, development and campaigns. There are no account layers and no handoffs to people you never meet, which means decisions happen quickly and the thing that goes live is the thing that was agreed. For a business in {$where}, that usually matters more than the size of the agency.\n\n" .
			"Every engagement starts with understanding what you sell, who buys it and how they currently find you. From there we build the smallest thing that can prove itself — a page, a campaign, a search presence — measure what it does, and grow it. No lock-in, no jargon, and no report that takes an hour to decode.",

		'services_h' => "What we do for {$city} businesses",
		'services'   => array(
			array( 'title' => 'Local SEO', 'description' => "Getting you found for the searches people in {$city} actually make, including map results and the queries that come with buying intent." ),
			array( 'title' => 'Web design & development', 'description' => 'Custom sites built to load fast and convert, designed around your customers rather than a template someone else is also using.' ),
			array( 'title' => 'Search & performance campaigns', 'description' => 'Paid search and social run against a clear cost per enquiry, so spend is judged on results and not on impressions.' ),
			array( 'title' => 'Content & social', 'description' => 'Words and posts that sound like your business, answer real questions, and give search engines something worth ranking.' ),
			array( 'title' => 'Branding & identity', 'description' => 'Logo, type, colour and the rules that keep it consistent across every screen, sign and document.' ),
			array( 'title' => 'Analytics & reporting', 'description' => 'Tracking set up properly at launch, and plain-language reporting that tells you what worked and what to do next.' ),
		),

		'why_h' => "Why work with us in {$city}",
		'why'   => array(
			array( 'title' => 'One team, start to finish', 'description' => 'The person who scopes the work designs and builds it. Fewer handoffs means fewer surprises and a shorter path from idea to live.' ),
			array( 'title' => 'Built to be measured', 'description' => 'Every site and campaign ships with tracking in place, so the conversation is about enquiries and cost per lead rather than opinions.' ),
			array( 'title' => 'Yours to run afterwards', 'description' => 'You get an editable site, the logins, and documentation. Nothing is held hostage, and you are free to take it elsewhere.' ),
		),

		'process_h' => 'How we work',
		'process'   => array(
			array( 'title' => 'Discover', 'description' => 'A short call to understand the business, the customers and what a good month actually looks like for you.' ),
			array( 'title' => 'Plan', 'description' => 'A clear scope, timeline and the specific outcome we are aiming for, agreed before any work starts.' ),
			array( 'title' => 'Design & build', 'description' => 'Designed in the open and reviewed together, then built hand-crafted, fast and accessible.' ),
			array( 'title' => 'Launch', 'description' => 'Tested across devices, then shipped with analytics, search and tracking configured properly.' ),
			array( 'title' => 'Grow', 'description' => 'We stay on to measure, refine and keep improving what the site and the campaigns are doing.' ),
		),

		'faq_h' => 'Common questions',
		'faq'   => array(
			array( 'question' => "Do you work with businesses based in {$city}?", 'answer' => "Yes. We work with clients across {$where} and beyond. Most of the work happens over calls and shared documents, and we are happy to meet where it makes sense." ),
			array( 'question' => 'How much does it cost?', 'answer' => 'There is no fixed price, because a one-page site and an ongoing campaign are not the same job. Tell us what you are trying to achieve and we will scope it properly and quote before anything starts.' ),
			array( 'question' => 'How long does it take?', 'answer' => 'Smaller websites usually run a few weeks; larger builds and ongoing marketing run longer. You get a realistic timeline after the first scoping conversation, not an optimistic one.' ),
			array( 'question' => 'Can I edit the site myself afterwards?', 'answer' => 'Yes. Sites are built on WordPress with editable blocks and a simple admin, so your team can update content without breaking the design or needing us for every change.' ),
			array( 'question' => 'Can you guarantee first place on Google?', 'answer' => 'No, and you should be wary of anyone who does. We can commit to the work that reliably improves visibility — technical health, relevant content, local signals — and to showing you exactly what it moves.' ),
			array( 'question' => 'What do you need from us to start?', 'answer' => 'A conversation about the business and your goals. If you have existing analytics, branding or a current website, those help, but they are not required to get started.' ),
		),

		'cta_h'   => "Let's talk about your {$lc} in {$city}",
		'cta_sub' => 'Tell us what you are trying to achieve and we will come back with a clear scope, a realistic timeline, and an honest answer about whether we are the right fit.',
		'cta_btn' => 'Start a project',

		'localities' => array(),
	);
}

/**
 * Trim a string to a length without cutting a word in half.
 *
 * The mb_* calls are guarded rather than used directly. WordPress polyfills
 * mb_substr and mb_strlen when the mbstring extension is missing, but it does
 * not polyfill mb_strrpos — so an unguarded call here would fatal on a shared
 * host without mbstring, which is exactly the kind of host this plugin runs
 * on. The single-byte path is slightly less careful with multibyte titles and
 * entirely correct for the Latin text these fields hold.
 */
function deskman_area_cap( $text, $max ) {
	$text = trim( preg_replace( '/\s+/u', ' ', (string) $text ) );
	$mb   = function_exists( 'mb_strlen' ) && function_exists( 'mb_substr' ) && function_exists( 'mb_strrpos' );

	$len = $mb ? mb_strlen( $text ) : strlen( $text );
	if ( $len <= $max ) { return $text; }

	$cut = $mb ? mb_substr( $text, 0, $max ) : substr( $text, 0, $max );
	$sp  = $mb ? mb_strrpos( $cut, ' ' ) : strrpos( $cut, ' ' );
	if ( $sp && $sp > $max * 0.6 ) {
		$cut = $mb ? mb_substr( $cut, 0, $sp ) : substr( $cut, 0, $sp );
	}
	return rtrim( $cut, " ,.–—-" );
}

/* ==========================================================================
   Creating the page
   ========================================================================== */

/**
 * Generate one area page and save it as a draft.
 *
 * $args: city, service, keyword, keywords, region, localities, notes, use_ai.
 * Returns array( 'id' => int, 'engine' => 'grok'|'template', 'note' => string )
 * or a WP_Error. A failed API call is not an error here — it downgrades to the
 * template and says so in 'note', because a draft you can edit beats a red box.
 */
function deskman_area_generate( $args ) {
	$defaults = array(
		'city'       => '',
		'service'    => deskman_area_opt( 'service' ),
		'keyword'    => '',
		'keywords'   => '',
		'region'     => deskman_area_opt( 'region' ),
		'localities' => '',
		'notes'      => '',
		'use_ai'     => true,
	);
	$args = wp_parse_args( $args, $defaults );

	$args['city'] = trim( (string) $args['city'] );
	if ( $args['city'] === '' ) {
		return new WP_Error( 'no_city', __( 'An area or city name is required.', 'deskman-studio-manager' ) );
	}
	$args['service'] = trim( (string) $args['service'] );
	if ( $args['service'] === '' ) { $args['service'] = 'Digital Marketing'; }
	$args['keyword'] = trim( (string) $args['keyword'] );
	if ( $args['keyword'] === '' ) {
		$args['keyword'] = 'Best ' . $args['service'] . ' Agency in ' . $args['city'];
	}

	$engine = 'template';
	$note   = '';
	$data   = null;

	if ( $args['use_ai'] && trim( (string) deskman_area_opt( 'ai_key' ) ) !== '' ) {
		$result = deskman_area_ai_request(
			deskman_area_system_prompt(),
			deskman_area_user_prompt( $args )
		);
		if ( is_wp_error( $result ) ) {
			$note = sprintf(
				/* translators: %s: the error message returned by the API. */
				__( 'Written from the built-in template — the AI call failed (%s).', 'deskman-studio-manager' ),
				$result->get_error_message()
			);
		} else {
			$data   = $result;
			$engine = 'grok';
		}
	} elseif ( $args['use_ai'] ) {
		$note = __( 'Written from the built-in template — no API key is saved yet.', 'deskman-studio-manager' );
	}

	if ( ! is_array( $data ) ) {
		$data = deskman_area_fallback_content( $args );
	}

	/* Merge the model's output over the template's, key by key. Anything the
	   model skipped keeps the template's version, so a partial reply still
	   produces a complete page instead of a half-empty one. */
	$base = deskman_area_fallback_content( $args );
	foreach ( $base as $k => $v ) {
		if ( ! isset( $data[ $k ] ) || $data[ $k ] === '' || $data[ $k ] === array() ) {
			$data[ $k ] = $v;
		}
	}

	$title = trim( (string) deskman_area_get( $data, 'title', $args['keyword'] ) );
	if ( $title === '' ) { $title = $args['keyword']; }

	$slug = sanitize_title( (string) deskman_area_get( $data, 'slug', '' ) );
	if ( $slug === '' ) { $slug = sanitize_title( $title ); }

	$post_id = wp_insert_post( array(
		'post_type'   => DESKMAN_AREA_CPT,
		'post_status' => 'draft',
		'post_title'  => $title,
		'post_name'   => $slug,
	), true );

	if ( is_wp_error( $post_id ) ) { return $post_id; }

	/* Localities: whatever the editor typed wins, since they know the city;
	   otherwise take the model's list. */
	$localities = trim( (string) $args['localities'] );
	if ( $localities === '' ) {
		$localities = deskman_area_flatten_list( deskman_area_get( $data, 'localities', array() ) );
	}

	$meta = array(
		'city'         => $args['city'],
		'region'       => $args['region'],
		'service'      => $args['service'],
		'keyword'      => $args['keyword'],
		'keywords'     => $args['keywords'],
		'localities'   => $localities,
		'hero_eyebrow' => deskman_area_get( $data, 'hero_eyebrow', '' ),
		'hero_h'       => deskman_area_get( $data, 'hero_h', $args['keyword'] ),
		'hero_sub'     => deskman_area_get( $data, 'hero_sub', '' ),
		'cta_label'    => deskman_area_get( $data, 'cta_label', 'Start a project' ),
		'stats'        => deskman_area_pairs_from( deskman_area_get( $data, 'stats', array() ), 'value', 'label' ),
		'intro_h'      => deskman_area_get( $data, 'intro_h', '' ),
		'intro_body'   => deskman_area_get( $data, 'intro_body', '' ),
		'services_h'   => deskman_area_get( $data, 'services_h', '' ),
		'services'     => deskman_area_pairs_from( deskman_area_get( $data, 'services', array() ), 'title', 'description' ),
		'why_h'        => deskman_area_get( $data, 'why_h', '' ),
		'why'          => deskman_area_pairs_from( deskman_area_get( $data, 'why', array() ), 'title', 'description' ),
		'process_h'    => deskman_area_get( $data, 'process_h', '' ),
		'process'      => deskman_area_pairs_from( deskman_area_get( $data, 'process', array() ), 'title', 'description' ),
		'faq_h'        => deskman_area_get( $data, 'faq_h', '' ),
		'faq'          => deskman_area_pairs_from( deskman_area_get( $data, 'faq', array() ), 'question', 'answer' ),
		'cta_h'        => deskman_area_get( $data, 'cta_h', '' ),
		'cta_sub'      => deskman_area_get( $data, 'cta_sub', '' ),
		'cta_btn'      => deskman_area_get( $data, 'cta_btn', 'Start a project' ),
		'phone'        => '',
		'map'          => '',
	);

	foreach ( $meta as $k => $v ) {
		$v = is_array( $v ) ? implode( "\n", $v ) : (string) $v;
		$v = trim( $v );
		if ( $v === '' ) { continue; }
		update_post_meta( $post_id, '_dsk_area_' . $k, sanitize_textarea_field( $v ) );
	}

	// Feed the theme's existing SEO engine through the meta keys it already reads.
	$seo_title = deskman_area_cap( deskman_area_get( $data, 'seo_title', $title ), 65 );
	$seo_desc  = deskman_area_cap( deskman_area_get( $data, 'seo_desc', '' ), 158 );
	if ( $seo_title !== '' ) { update_post_meta( $post_id, '_deskman_seo_title', sanitize_text_field( $seo_title ) ); }
	if ( $seo_desc !== '' )  { update_post_meta( $post_id, '_deskman_seo_desc', sanitize_text_field( $seo_desc ) ); }

	update_post_meta( $post_id, '_dsk_area_engine', $engine );
	update_post_meta( $post_id, '_dsk_area_generated', current_time( 'mysql' ) );

	/* Set the excerpt here rather than relying on the save_post hook in
	   areas.php: that hook fires during wp_insert_post above, before any of
	   this meta exists, so at that moment there is no hero paragraph to copy. */
	$lead = trim( (string) deskman_area_field( $post_id, 'hero_sub' ) );
	if ( $lead !== '' ) {
		wp_update_post( array( 'ID' => $post_id, 'post_excerpt' => wp_trim_words( $lead, 40, '' ) ) );
	}

	return array( 'id' => (int) $post_id, 'engine' => $engine, 'note' => $note );
}

/** Read a key from the decoded reply, tolerating a missing or wrong-typed value. */
function deskman_area_get( $data, $key, $default = '' ) {
	if ( ! is_array( $data ) || ! isset( $data[ $key ] ) ) { return $default; }
	$v = $data[ $key ];
	if ( is_string( $v ) ) { return trim( $v ); }
	if ( is_numeric( $v ) ) { return (string) $v; }
	if ( is_array( $v ) ) { return $v; }
	return $default;
}

/** Turn a list of objects into the "a :: b" lines the fields store. */
function deskman_area_pairs_from( $rows, $key_a, $key_b ) {
	$out = array();
	foreach ( (array) $rows as $row ) {
		if ( is_string( $row ) ) { $out[] = trim( $row ); continue; }
		if ( ! is_array( $row ) ) { continue; }
		$a = isset( $row[ $key_a ] ) ? trim( (string) $row[ $key_a ] ) : '';
		$b = isset( $row[ $key_b ] ) ? trim( (string) $row[ $key_b ] ) : '';
		if ( $a === '' ) { continue; }
		// Strip any :: the model produced, so it cannot break the row format.
		$a = str_replace( '::', '—', $a );
		$b = str_replace( '::', '—', $b );
		$out[] = $b === '' ? $a : $a . ' :: ' . $b;
	}
	return $out;
}

/** Flatten a list of strings into a comma-separated field value. */
function deskman_area_flatten_list( $rows ) {
	$out = array();
	foreach ( (array) $rows as $r ) {
		if ( is_string( $r ) && trim( $r ) !== '' ) { $out[] = trim( $r ); }
		elseif ( is_array( $r ) && isset( $r['name'] ) ) { $out[] = trim( (string) $r['name'] ); }
	}
	return implode( ', ', $out );
}

/* ==========================================================================
   Admin screen
   ========================================================================== */

add_action( 'admin_menu', function () {
	add_submenu_page(
		'deskman-studio',
		__( 'Generate area page', 'deskman-studio-manager' ),
		__( 'Generate area page', 'deskman-studio-manager' ),
		'manage_options',
		'deskman-areas',
		'deskman_areas_screen'
	);
}, 11 );

function deskman_areas_screen() {
	if ( ! current_user_can( 'manage_options' ) ) { return; }

	$tab   = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'generate';
	$tabs  = array(
		'generate' => __( 'Generate', 'deskman-studio-manager' ),
		'bulk'     => __( 'Bulk generate', 'deskman-studio-manager' ),
		'settings' => __( 'Settings', 'deskman-studio-manager' ),
	);
	if ( ! isset( $tabs[ $tab ] ) ) { $tab = 'generate'; }

	$made = null;
	if ( $tab === 'generate' && isset( $_POST['deskman_area_go'] ) && check_admin_referer( 'deskman_area_generate', 'deskman_area_gen_nonce' ) ) {
		$made = deskman_area_generate( array(
			'city'       => isset( $_POST['city'] ) ? sanitize_text_field( wp_unslash( $_POST['city'] ) ) : '',
			'service'    => isset( $_POST['service'] ) ? sanitize_text_field( wp_unslash( $_POST['service'] ) ) : '',
			'keyword'    => isset( $_POST['keyword'] ) ? sanitize_text_field( wp_unslash( $_POST['keyword'] ) ) : '',
			'keywords'   => isset( $_POST['keywords'] ) ? sanitize_textarea_field( wp_unslash( $_POST['keywords'] ) ) : '',
			'region'     => isset( $_POST['region'] ) ? sanitize_text_field( wp_unslash( $_POST['region'] ) ) : '',
			'localities' => isset( $_POST['localities'] ) ? sanitize_textarea_field( wp_unslash( $_POST['localities'] ) ) : '',
			'notes'      => isset( $_POST['notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['notes'] ) ) : '',
			'use_ai'     => ! empty( $_POST['use_ai'] ),
		) );
	}

	$tested = null;
	if ( $tab === 'settings' && isset( $_POST['deskman_area_test'] ) && check_admin_referer( 'deskman_area_test', 'deskman_area_test_nonce' ) ) {
		$tested = deskman_area_ai_request(
			'You are a connection test. Reply with JSON only.',
			'Return exactly {"ok":true}.',
			25
		);
	}

	deskman_admin_head(
		__( 'Area pages', 'deskman-studio-manager' ),
		__( 'Location landing pages — one per city and service. They are fully indexable and appear in your sitemap, but never in the header or footer menu.', 'deskman-studio-manager' )
	);

	echo '<h2 class="nav-tab-wrapper" style="margin-top:14px">';
	foreach ( $tabs as $slug => $label ) {
		printf(
			'<a href="%s" class="nav-tab%s">%s</a>',
			esc_url( admin_url( 'admin.php?page=deskman-areas&tab=' . $slug ) ),
			$tab === $slug ? ' nav-tab-active' : '',
			esc_html( $label )
		);
	}
	echo '</h2>';

	if ( $made !== null ) {
		if ( is_wp_error( $made ) ) {
			echo '<div class="notice notice-error"><p>' . esc_html( $made->get_error_message() ) . '</p></div>';
		} else {
			echo '<div class="notice notice-success" style="padding:14px 16px">';
			echo '<p style="margin:0 0 8px"><strong>' . esc_html__( 'Draft created.', 'deskman-studio-manager' ) . '</strong> ';
			echo esc_html(
				$made['engine'] === 'grok'
					? __( 'Written by AI. Read it through before publishing — you are responsible for what it says.', 'deskman-studio-manager' )
					: __( 'Written from the built-in template.', 'deskman-studio-manager' )
			);
			echo '</p>';
			if ( ! empty( $made['note'] ) ) {
				echo '<p style="margin:0 0 8px;color:#8a6d3b">' . esc_html( $made['note'] ) . '</p>';
			}
			echo '<p style="margin:0">';
			echo '<a class="button button-primary" href="' . esc_url( get_edit_post_link( $made['id'], '' ) ) . '">' . esc_html__( 'Edit the page', 'deskman-studio-manager' ) . ' &rarr;</a> ';
			echo '<a class="button" href="' . esc_url( get_preview_post_link( $made['id'] ) ) . '" target="_blank">' . esc_html__( 'Preview', 'deskman-studio-manager' ) . '</a>';
			echo '</p></div>';
		}
	}

	if ( $tab === 'generate' )      { deskman_areas_tab_generate(); }
	elseif ( $tab === 'bulk' )      { deskman_areas_tab_bulk(); }
	else                            { deskman_areas_tab_settings( $tested ); }

	echo '</div>';
}

/** Tab: one page at a time. */
function deskman_areas_tab_generate() {
	$has_key = trim( (string) deskman_area_opt( 'ai_key' ) ) !== '';
	?>
	<form method="post" style="background:#fff;border:1px solid #dcdcde;border-radius:10px;padding:22px;max-width:820px;margin-top:18px">
		<?php wp_nonce_field( 'deskman_area_generate', 'deskman_area_gen_nonce' ); ?>
		<h2 style="margin-top:0"><?php esc_html_e( 'Generate a page', 'deskman-studio-manager' ); ?></h2>
		<p style="color:#555;margin-top:0">
			<?php esc_html_e( 'Fill in the area and what you want to rank for. The page is created as a draft with every section written — hero, intro, services, why us, process, FAQ and the structured data — so you can read it, edit it, and publish when it is right.', 'deskman-studio-manager' ); ?>
		</p>

		<table class="form-table" role="presentation"><tbody>
			<tr>
				<th scope="row"><label for="dsa-city"><?php esc_html_e( 'Area / city', 'deskman-studio-manager' ); ?> <span style="color:#b32d2e">*</span></label></th>
				<td><input type="text" id="dsa-city" name="city" class="regular-text" required placeholder="Visakhapatnam" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="dsa-region"><?php esc_html_e( 'State / region', 'deskman-studio-manager' ); ?></label></th>
				<td><input type="text" id="dsa-region" name="region" class="regular-text" value="<?php echo esc_attr( deskman_area_opt( 'region' ) ); ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="dsa-service"><?php esc_html_e( 'Service focus', 'deskman-studio-manager' ); ?></label></th>
				<td>
					<input type="text" id="dsa-service" name="service" class="regular-text" value="<?php echo esc_attr( deskman_area_opt( 'service' ) ); ?>" />
					<p class="description"><?php esc_html_e( 'One service per page. A page trying to rank for marketing, web design and SEO at once usually ranks for none of them.', 'deskman-studio-manager' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="dsa-keyword"><?php esc_html_e( 'Primary keyword', 'deskman-studio-manager' ); ?></label></th>
				<td>
					<input type="text" id="dsa-keyword" name="keyword" class="large-text" placeholder="Best Digital Marketing Agency in Visakhapatnam" />
					<p class="description"><?php esc_html_e( 'The exact phrase you want this page to rank for. Leave blank and it becomes "Best {service} Agency in {city}".', 'deskman-studio-manager' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="dsa-keywords"><?php esc_html_e( 'Secondary keywords', 'deskman-studio-manager' ); ?></label></th>
				<td>
					<textarea id="dsa-keywords" name="keywords" rows="2" class="large-text" placeholder="digital marketing company vizag, seo services visakhapatnam, social media agency vizag"></textarea>
					<p class="description"><?php esc_html_e( 'Comma separated. Worked into the copy naturally — there is no meta keywords tag, because search engines stopped reading it long ago.', 'deskman-studio-manager' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="dsa-localities"><?php esc_html_e( 'Localities', 'deskman-studio-manager' ); ?></label></th>
				<td>
					<textarea id="dsa-localities" name="localities" rows="2" class="large-text" placeholder="MVP Colony, Gajuwaka, Madhurawada, Dwaraka Nagar"></textarea>
					<p class="description"><?php esc_html_e( 'Optional but worth filling in — you know the city better than the model does. Leave blank and it will suggest some.', 'deskman-studio-manager' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="dsa-notes"><?php esc_html_e( 'Notes for this page', 'deskman-studio-manager' ); ?></label></th>
				<td>
					<textarea id="dsa-notes" name="notes" rows="3" class="large-text" placeholder="Focus on export businesses and the port economy. Mention that we handle Telugu and English content."></textarea>
					<p class="description"><?php esc_html_e( 'Anything specific about this area or audience. This is what stops fifty pages reading like the same page.', 'deskman-studio-manager' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Write with', 'deskman-studio-manager' ); ?></th>
				<td>
					<label><input type="checkbox" name="use_ai" value="1" <?php checked( $has_key ); ?> <?php disabled( ! $has_key ); ?> /> <?php esc_html_e( 'Write this page with AI', 'deskman-studio-manager' ); ?></label>
					<p class="description">
						<?php if ( $has_key ) : ?>
							<?php esc_html_e( 'Unticked, the page is written from the built-in template instead — instant and free.', 'deskman-studio-manager' ); ?>
						<?php else : ?>
							<?php printf(
								wp_kses( __( 'No API key saved yet, so the built-in template is used. <a href="%s">Add your Groq key</a> to have pages written for each area.', 'deskman-studio-manager' ), array( 'a' => array( 'href' => array() ) ) ),
								esc_url( admin_url( 'admin.php?page=deskman-areas&tab=settings' ) )
							); ?>
						<?php endif; ?>
					</p>
				</td>
			</tr>
		</tbody></table>

		<p><button class="button button-primary button-hero" name="deskman_area_go" value="1"><?php esc_html_e( 'Generate draft page', 'deskman-studio-manager' ); ?></button></p>
		<p style="color:#777;margin:0"><?php esc_html_e( 'Generation takes 15–40 seconds with the API on. The page is saved as a draft — nothing goes live until you publish it.', 'deskman-studio-manager' ); ?></p>
	</form>
	<?php
}

/** Tab: many pages, one API call each, driven from the browser so a long run
 *  never hits the PHP time limit and progress is visible while it works. */
function deskman_areas_tab_bulk() {
	$has_key = trim( (string) deskman_area_opt( 'ai_key' ) ) !== '';
	?>
	<div style="background:#fff;border:1px solid #dcdcde;border-radius:10px;padding:22px;max-width:820px;margin-top:18px">
		<h2 style="margin-top:0"><?php esc_html_e( 'Generate several at once', 'deskman-studio-manager' ); ?></h2>
		<p style="color:#555;margin-top:0">
			<?php esc_html_e( 'One area per line. Add a service after a pipe to override the default, and a keyword after a second pipe:', 'deskman-studio-manager' ); ?>
		</p>
		<pre style="background:#f6f7f7;border:1px solid #dcdcde;border-radius:6px;padding:10px 12px;white-space:pre-wrap;color:#3c434a">Visakhapatnam
Hyderabad | Digital Marketing
Warangal | SEO | Best SEO Company in Warangal</pre>

		<table class="form-table" role="presentation"><tbody>
			<tr>
				<th scope="row"><label for="dsa-bulk"><?php esc_html_e( 'Areas', 'deskman-studio-manager' ); ?></label></th>
				<td><textarea id="dsa-bulk" rows="9" class="large-text code" placeholder="Visakhapatnam&#10;Hyderabad&#10;Warangal&#10;Vijayawada&#10;Guntur"></textarea></td>
			</tr>
			<tr>
				<th scope="row"><label for="dsa-bulk-service"><?php esc_html_e( 'Default service', 'deskman-studio-manager' ); ?></label></th>
				<td><input type="text" id="dsa-bulk-service" class="regular-text" value="<?php echo esc_attr( deskman_area_opt( 'service' ) ); ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="dsa-bulk-region"><?php esc_html_e( 'Default region', 'deskman-studio-manager' ); ?></label></th>
				<td><input type="text" id="dsa-bulk-region" class="regular-text" value="<?php echo esc_attr( deskman_area_opt( 'region' ) ); ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Write with', 'deskman-studio-manager' ); ?></th>
				<td><label><input type="checkbox" id="dsa-bulk-ai" <?php checked( $has_key ); ?> <?php disabled( ! $has_key ); ?> /> <?php esc_html_e( 'Write this page with AI', 'deskman-studio-manager' ); ?></label></td>
			</tr>
		</tbody></table>

		<p>
			<button class="button button-primary button-hero" id="dsa-bulk-go"><?php esc_html_e( 'Generate all', 'deskman-studio-manager' ); ?></button>
			<button class="button" id="dsa-bulk-stop" style="display:none"><?php esc_html_e( 'Stop', 'deskman-studio-manager' ); ?></button>
		</p>
		<p style="color:#777;margin:0 0 4px"><?php esc_html_e( 'Pages are generated one at a time so a long list cannot time out. Keep this tab open until it finishes. Everything lands as a draft.', 'deskman-studio-manager' ); ?></p>

		<div id="dsa-bulk-log" style="margin-top:16px"></div>
	</div>

	<script>
	(function(){
		var go   = document.getElementById('dsa-bulk-go');
		var stop = document.getElementById('dsa-bulk-stop');
		var log  = document.getElementById('dsa-bulk-log');
		var cancelled = false;

		function row(text, state){
			var d = document.createElement('div');
			d.style.cssText = 'padding:8px 12px;border:1px solid #dcdcde;border-radius:6px;margin:0 0 6px;background:#fff;font-size:13px';
			if (state === 'ok')   { d.style.borderColor = '#68de7c'; d.style.background = '#f2fcf3'; }
			if (state === 'bad')  { d.style.borderColor = '#f0b3b2'; d.style.background = '#fcf2f2'; }
			if (state === 'work') { d.style.borderColor = '#c3c4c7'; d.style.background = '#f6f7f7'; }
			d.innerHTML = text;
			log.appendChild(d);
			return d;
		}

		go.addEventListener('click', async function(e){
			e.preventDefault();
			var raw = document.getElementById('dsa-bulk').value.split('\n')
				.map(function(s){ return s.trim(); }).filter(Boolean);
			if (!raw.length) { return; }

			var service = document.getElementById('dsa-bulk-service').value;
			var region  = document.getElementById('dsa-bulk-region').value;
			var useAi   = document.getElementById('dsa-bulk-ai').checked ? '1' : '';

			cancelled = false;
			go.disabled = true;
			stop.style.display = '';
			log.innerHTML = '';

			var done = 0, failed = 0;
			for (var i = 0; i < raw.length; i++) {
				if (cancelled) { row('<strong><?php echo esc_js( __( 'Stopped.', 'deskman-studio-manager' ) ); ?></strong>', 'bad'); break; }

				var bits    = raw[i].split('|').map(function(s){ return s.trim(); });
				var city    = bits[0];
				var svc     = bits[1] || service;
				var keyword = bits[2] || '';

				var line = row('<span class="spinner is-active" style="float:none;margin:0 8px 0 0"></span> ' + city + ' — ' + svc + '…', 'work');

				var body = new URLSearchParams();
				body.append('action', 'deskman_area_generate');
				body.append('_ajax_nonce', '<?php echo esc_js( wp_create_nonce( 'deskman_area_ajax' ) ); ?>');
				body.append('city', city);
				body.append('service', svc);
				body.append('keyword', keyword);
				body.append('region', region);
				body.append('use_ai', useAi);

				try {
					var res  = await fetch(ajaxurl, { method:'POST', credentials:'same-origin', body: body });
					var data = await res.json();
					if (data && data.success) {
						done++;
						line.style.borderColor = '#68de7c';
						line.style.background  = '#f2fcf3';
						line.innerHTML = '&#10003; <strong>' + city + '</strong> — ' + data.data.title +
							' <a href="' + data.data.edit + '">edit</a>' +
							(data.data.note ? ' <span style="color:#8a6d3b">· ' + data.data.note + '</span>' : '');
					} else {
						failed++;
						line.style.borderColor = '#f0b3b2';
						line.style.background  = '#fcf2f2';
						line.innerHTML = '&#10007; <strong>' + city + '</strong> — ' + ((data && data.data) ? data.data : 'failed');
					}
				} catch (err) {
					failed++;
					line.style.borderColor = '#f0b3b2';
					line.style.background  = '#fcf2f2';
					line.innerHTML = '&#10007; <strong>' + city + '</strong> — ' + err.message;
				}
			}

			go.disabled = false;
			stop.style.display = 'none';
			row('<strong><?php echo esc_js( __( 'Finished.', 'deskman-studio-manager' ) ); ?></strong> ' + done + ' <?php echo esc_js( __( 'created', 'deskman-studio-manager' ) ); ?>' +
				(failed ? ', ' + failed + ' <?php echo esc_js( __( 'failed', 'deskman-studio-manager' ) ); ?>' : '') +
				' — <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=' . DESKMAN_AREA_CPT ) ); ?>"><?php echo esc_js( __( 'review the drafts', 'deskman-studio-manager' ) ); ?></a>', done ? 'ok' : 'bad');
		});

		stop.addEventListener('click', function(e){ e.preventDefault(); cancelled = true; });
	})();
	</script>
	<?php
}

/** One page per AJAX call, so the browser can pace a long bulk run. */
add_action( 'wp_ajax_deskman_area_generate', function () {
	check_ajax_referer( 'deskman_area_ajax' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( __( 'Not allowed.', 'deskman-studio-manager' ), 403 );
	}

	$made = deskman_area_generate( array(
		'city'    => isset( $_POST['city'] ) ? sanitize_text_field( wp_unslash( $_POST['city'] ) ) : '',
		'service' => isset( $_POST['service'] ) ? sanitize_text_field( wp_unslash( $_POST['service'] ) ) : '',
		'keyword' => isset( $_POST['keyword'] ) ? sanitize_text_field( wp_unslash( $_POST['keyword'] ) ) : '',
		'region'  => isset( $_POST['region'] ) ? sanitize_text_field( wp_unslash( $_POST['region'] ) ) : '',
		'use_ai'  => ! empty( $_POST['use_ai'] ),
	) );

	if ( is_wp_error( $made ) ) {
		wp_send_json_error( $made->get_error_message() );
	}

	wp_send_json_success( array(
		'id'    => $made['id'],
		'title' => get_the_title( $made['id'] ),
		'edit'  => get_edit_post_link( $made['id'], '' ),
		'note'  => $made['note'],
	) );
} );

/** Tab: settings, including the API key and a connection test. */
function deskman_areas_tab_settings( $tested ) {
	$o       = wp_parse_args( (array) get_option( DESKMAN_AREA_OPTION ), deskman_area_defaults() );
	$name    = DESKMAN_AREA_OPTION;
	$has_key = trim( (string) $o['ai_key'] ) !== '';

	if ( $tested !== null ) {
		if ( is_wp_error( $tested ) ) {
			echo '<div class="notice notice-error"><p><strong>' . esc_html__( 'Connection failed.', 'deskman-studio-manager' ) . '</strong> ' . esc_html( $tested->get_error_message() ) . '</p></div>';
		} else {
			echo '<div class="notice notice-success"><p><strong>' . esc_html__( 'Connected.', 'deskman-studio-manager' ) . '</strong> ' . esc_html__( 'The key, endpoint and model all work.', 'deskman-studio-manager' ) . '</p></div>';
		}
	}
	settings_errors( DESKMAN_AREA_OPTION );
	?>
	<form method="post" action="options.php" style="max-width:820px;margin-top:18px">
		<?php settings_fields( 'deskman_areas_group' ); ?>

		<h2 style="border-bottom:1px solid #dcdcde;padding-bottom:.4em"><?php esc_html_e( 'Pages', 'deskman-studio-manager' ); ?></h2>
		<table class="form-table" role="presentation"><tbody>
			<tr>
				<th scope="row"><label for="dsa-slug"><?php esc_html_e( 'URL base', 'deskman-studio-manager' ); ?></label></th>
				<td>
					<code><?php echo esc_html( trailingslashit( home_url() ) ); ?></code>
					<input type="text" id="dsa-slug" name="<?php echo esc_attr( $name ); ?>[slug]" value="<?php echo esc_attr( $o['slug'] ); ?>" style="width:160px" />
					<code>/your-page/</code>
					<p class="description">
						<?php echo wp_kses_post( __( '<strong>Leave this empty</strong> to drop the base entirely, so each page sits at the root on its own slug — <code>/best-digital-marketing-in-visakhapatnam/</code>. The slug comes from the page title and can be edited per page.', 'deskman-studio-manager' ) ); ?>
						<br>
						<?php esc_html_e( 'With no base there is no /locations/ archive page, and if an area page ever shares a slug with a real page or post, the existing one keeps the URL and a warning appears on the Area pages screen.', 'deskman-studio-manager' ); ?>
						<br>
						<?php esc_html_e( 'Changing this rewrites every area page URL. If pages already rank, leave it alone — or set up redirects first.', 'deskman-studio-manager' ); ?>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="dsa-def-service"><?php esc_html_e( 'Default service', 'deskman-studio-manager' ); ?></label></th>
				<td><input type="text" id="dsa-def-service" class="regular-text" name="<?php echo esc_attr( $name ); ?>[service]" value="<?php echo esc_attr( $o['service'] ); ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="dsa-def-region"><?php esc_html_e( 'Default region', 'deskman-studio-manager' ); ?></label></th>
				<td><input type="text" id="dsa-def-region" class="regular-text" name="<?php echo esc_attr( $name ); ?>[region]" value="<?php echo esc_attr( $o['region'] ); ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="dsa-country"><?php esc_html_e( 'Country', 'deskman-studio-manager' ); ?></label></th>
				<td><input type="text" id="dsa-country" class="regular-text" name="<?php echo esc_attr( $name ); ?>[country]" value="<?php echo esc_attr( $o['country'] ); ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="dsa-phone"><?php esc_html_e( 'Phone for area pages', 'deskman-studio-manager' ); ?></label></th>
				<td>
					<input type="text" id="dsa-phone" class="regular-text" name="<?php echo esc_attr( $name ); ?>[phone]" value="<?php echo esc_attr( $o['phone'] ); ?>" placeholder="+91 89191 23456" />
					<p class="description"><?php esc_html_e( 'Shown on every area page and included in the local business structured data. An individual page can override it.', 'deskman-studio-manager' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Cross-linking', 'deskman-studio-manager' ); ?></th>
				<td>
					<label><input type="checkbox" name="<?php echo esc_attr( $name ); ?>[related]" value="1" <?php checked( $o['related'], '1' ); ?> /> <?php esc_html_e( 'Link each area page to other areas', 'deskman-studio-manager' ); ?></label>
					<input type="number" min="0" max="12" style="width:70px;margin-left:10px" name="<?php echo esc_attr( $name ); ?>[related_max]" value="<?php echo esc_attr( $o['related_max'] ); ?>" />
					<p class="description"><?php esc_html_e( 'Worth keeping on. A set of location pages with no links between them is the version search engines tend to treat as thin — the links give the set a structure and a crawl path, without any of them entering the menu.', 'deskman-studio-manager' ); ?></p>
				</td>
			</tr>
		</tbody></table>

		<h2 style="border-bottom:1px solid #dcdcde;padding-bottom:.4em;margin-top:2em"><?php esc_html_e( 'AI provider', 'deskman-studio-manager' ); ?></h2>
		<p style="color:#555">
			<?php esc_html_e( 'Used only in the admin, only when you press Generate. The key is stored on your server and never reaches a visitor\'s browser.', 'deskman-studio-manager' ); ?>
		</p>
		<table class="form-table" role="presentation"><tbody>
			<tr>
				<th scope="row"><label for="dsa-key"><?php esc_html_e( 'API key', 'deskman-studio-manager' ); ?></label></th>
				<td>
					<input type="password" id="dsa-key" class="regular-text" name="<?php echo esc_attr( $name ); ?>[ai_key]" value="" autocomplete="off" placeholder="<?php echo $has_key ? esc_attr__( '•••••••• saved — leave blank to keep it', 'deskman-studio-manager' ) : 'gsk_…'; ?>" />
					<?php
					$dsa_cb_key = function_exists( 'deskman_cb_opt' ) ? trim( (string) deskman_cb_opt( 'ai_key' ) ) : '';
					if ( ! $has_key && $dsa_cb_key !== '' ) :
					?>
						<p class="description" style="color:#135e96"><strong><?php esc_html_e( 'Using the key saved on the Chatbot screen.', 'deskman-studio-manager' ); ?></strong> <?php esc_html_e( 'Leave this blank to keep doing that, or paste a different key to use one just for generating pages.', 'deskman-studio-manager' ); ?></p>
					<?php endif; ?>
					<p class="description"><?php echo wp_kses_post( __( 'Groq keys start <code>gsk_</code> and come from <a href="https://console.groq.com/keys" target="_blank" rel="noopener">console.groq.com/keys</a> — the free tier is enough for generating pages. This is the same kind of key the Chatbot screen uses, and one key can serve both. Leaving this blank keeps the key you already saved.', 'deskman-studio-manager' ) ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="dsa-model"><?php esc_html_e( 'Model', 'deskman-studio-manager' ); ?></label></th>
				<td>
					<input type="text" id="dsa-model" class="regular-text" name="<?php echo esc_attr( $name ); ?>[ai_model]" value="<?php echo esc_attr( $o['ai_model'] ); ?>" />
					<p class="description"><?php echo wp_kses_post( __( 'On Groq\'s free tier: <code>openai/gpt-oss-120b</code> writes the better copy, <code>openai/gpt-oss-20b</code> is faster. The id must match the provider the endpoint points at, and a wrong one is the most common cause of a failed generation — use the test button below to check it. Model names change, so copy the exact id from <a href="https://console.groq.com/docs/models" target="_blank" rel="noopener">the provider\'s model list</a> if generation starts failing.', 'deskman-studio-manager' ) ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="dsa-endpoint"><?php esc_html_e( 'API endpoint', 'deskman-studio-manager' ); ?></label></th>
				<td>
					<input type="text" id="dsa-endpoint" class="regular-text" name="<?php echo esc_attr( $name ); ?>[ai_endpoint]" value="<?php echo esc_attr( $o['ai_endpoint'] ); ?>" />
					<p class="description"><?php echo wp_kses_post( __( 'Groq: <code>https://api.groq.com/openai/v1/chat/completions</code> (the default). Any OpenAI-compatible chat-completions URL works, so you can point this at another provider without changing anything else — for xAI Grok use <code>https://api.x.ai/v1/chat/completions</code> with a <code>grok-</code> model. Groq and Grok are different companies: keys and model names are not interchangeable.', 'deskman-studio-manager' ) ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="dsa-tone"><?php esc_html_e( 'Tone of voice', 'deskman-studio-manager' ); ?></label></th>
				<td><textarea id="dsa-tone" class="large-text" rows="2" name="<?php echo esc_attr( $name ); ?>[ai_tone]"><?php echo esc_textarea( $o['ai_tone'] ); ?></textarea></td>
			</tr>
			<tr>
				<th scope="row"><label for="dsa-extra"><?php esc_html_e( 'Standing instructions', 'deskman-studio-manager' ); ?></label></th>
				<td>
					<textarea id="dsa-extra" class="large-text" rows="4" name="<?php echo esc_attr( $name ); ?>[ai_extra]" placeholder="<?php esc_attr_e( 'e.g. Always mention that we handle both English and Telugu content. Never mention e-commerce — we do not take those projects.', 'deskman-studio-manager' ); ?>"><?php echo esc_textarea( $o['ai_extra'] ); ?></textarea>
					<p class="description"><?php esc_html_e( 'Added to every generation. Good for the things that are true of your studio everywhere, so you are not retyping them per page.', 'deskman-studio-manager' ); ?></p>
				</td>
			</tr>
		</tbody></table>

		<?php submit_button( __( 'Save area page settings', 'deskman-studio-manager' ) ); ?>
	</form>

	<form method="post" style="max-width:820px;margin-top:-6px">
		<?php wp_nonce_field( 'deskman_area_test', 'deskman_area_test_nonce' ); ?>
		<p><button class="button" name="deskman_area_test" value="1"><?php esc_html_e( 'Test the connection', 'deskman-studio-manager' ); ?></button>
		<span style="color:#777;margin-left:8px"><?php esc_html_e( 'Sends one tiny request and reports exactly what came back. Save your settings first.', 'deskman-studio-manager' ); ?></span></p>
	</form>
	<?php
}
