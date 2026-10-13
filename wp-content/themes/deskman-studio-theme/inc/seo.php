<?php
/**
 * Deskman Studio — SEO & AEO engine.
 *
 * This file adds everything the theme was missing for search engines and for
 * "answer engines" (Google AI Overviews, ChatGPT, Perplexity, Copilot): a real
 * meta description, canonical URL, Open Graph + Twitter cards, a robots policy,
 * and a schema.org JSON-LD graph.
 *
 * Design rules, matching the rest of the theme:
 *   - Every function is guarded, so nothing fatals if the file is loaded twice
 *     or a helper is redefined elsewhere.
 *   - All values are read from the Deskman Studio Manager plugin via
 *     deskman_opt() with sensible fallbacks, so the site is fully described
 *     even on a fresh install where nothing has been filled in yet.
 *   - Per-page overrides (meta title / description / noindex) come from post
 *     meta the plugin's SEO meta box writes; when absent, everything is derived
 *     from the page's own content so no page is ever left blank.
 *   - Output is escaped at the point of print. JSON-LD is encoded with
 *     wp_json_encode(), which escapes for a <script> context.
 *
 * @package Deskman_Studio
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/* ---------------------------------------------------------------------------
   Small value helpers
   --------------------------------------------------------------------------- */

/** Site / brand name, assembled from the two brand fields the plugin stores. */
if ( ! function_exists( 'deskman_seo_site_name' ) ) {
	function deskman_seo_site_name() {
		$name = trim( (string) deskman_opt( 'brand_name', 'Deskman' ) );
		$sub  = trim( (string) deskman_opt( 'brand_sub', 'Studio' ) );
		$full = trim( $name . ' ' . $sub );
		return $full !== '' ? $full : get_bloginfo( 'name' );
	}
}

/** Collapse any string to a clean, single-line, length-capped meta value. */
if ( ! function_exists( 'deskman_seo_trim' ) ) {
	function deskman_seo_trim( $text, $max = 160 ) {
		$text = wp_strip_all_tags( (string) $text );
		$text = preg_replace( '/\s+/u', ' ', $text );
		$text = trim( $text );
		if ( function_exists( 'mb_strlen' ) ? mb_strlen( $text ) <= $max : strlen( $text ) <= $max ) {
			return $text;
		}
		$cut = function_exists( 'mb_substr' ) ? mb_substr( $text, 0, $max - 1 ) : substr( $text, 0, $max - 1 );
		$sp  = function_exists( 'mb_strrpos' ) ? mb_strrpos( $cut, ' ' ) : strrpos( $cut, ' ' );
		if ( $sp && $sp > 40 ) { $cut = function_exists( 'mb_substr' ) ? mb_substr( $cut, 0, $sp ) : substr( $cut, 0, $sp ); }
		return rtrim( $cut ) . '…';
	}
}

/** The best share image URL available: per-page featured image → configured
 *  share image → site logo → theme screenshot. Always returns a URL or ''. */
if ( ! function_exists( 'deskman_seo_image' ) ) {
	function deskman_seo_image() {
		if ( is_singular() && has_post_thumbnail() ) {
			$src = get_the_post_thumbnail_url( get_queried_object_id(), 'deskman-thumb' );
			if ( $src ) { return $src; }
		}
		$share = trim( (string) deskman_opt( 'seo_image', '' ) );
		if ( $share !== '' ) { return $share; }
		$logo = trim( (string) deskman_opt( 'seo_logo', '' ) );
		if ( $logo !== '' ) { return $logo; }
		$shot = get_theme_file_uri( 'screenshot.png' );
		return $shot ? $shot : '';
	}
}

/* ---------------------------------------------------------------------------
   Per-view context: one function that resolves the title, description,
   canonical, type and index policy for whatever is being rendered. Everything
   downstream (meta tags AND JSON-LD) reads from this single source of truth.
   --------------------------------------------------------------------------- */

if ( ! function_exists( 'deskman_seo_context' ) ) {
	function deskman_seo_context() {
		static $ctx = null;
		if ( $ctx !== null ) { return $ctx; }

		$site   = deskman_seo_site_name();
		$sep     = ' — ';
		$default = array(
			'title'     => $site,
			'desc'      => deskman_seo_trim( deskman_opt( 'seo_desc', deskman_opt( 'v5_hero_lead', get_bloginfo( 'description' ) ) ) ),
			'canonical' => home_url( '/' ),
			'type'      => 'website',
			'noindex'   => false,
		);

		if ( is_front_page() ) {
			$default['title']     = $site . $sep . deskman_seo_trim( deskman_opt( 'seo_home_tagline', 'Web Design, WordPress & Branding Studio' ), 70 );
			$default['canonical'] = home_url( '/' );
			$ctx = $default;
			return $ctx;
		}

		if ( is_singular() ) {
			$id  = get_queried_object_id();
			$ctx = $default;

			/* Per-page overrides written by the plugin's SEO meta box. */
			$mt  = trim( (string) get_post_meta( $id, '_deskman_seo_title', true ) );
			$md  = trim( (string) get_post_meta( $id, '_deskman_seo_desc', true ) );
			$mn  = get_post_meta( $id, '_deskman_seo_noindex', true ) === '1';

			$ctx['title']     = $mt !== '' ? $mt : ( wp_strip_all_tags( get_the_title( $id ) ) . $sep . $site );
			$ctx['canonical'] = get_permalink( $id );
			$ctx['type']      = 'article';
			$ctx['noindex']   = $mn;

			if ( $md !== '' ) {
				$ctx['desc'] = deskman_seo_trim( $md );
			} else {
				/* Prefer a page-specific lead field, then the page body, then
				   the excerpt — so a page always describes itself. */
				$lead = deskman_seo_lead_for( $id );
				$body = $lead !== '' ? $lead : wp_strip_all_tags( get_post_field( 'post_content', $id ) );
				if ( trim( $body ) === '' ) { $body = get_the_excerpt( $id ); }
				if ( trim( $body ) !== '' ) { $ctx['desc'] = deskman_seo_trim( $body ); }
			}
			return $ctx;
		}

		if ( is_archive() ) {
			$default['title']     = wp_strip_all_tags( get_the_archive_title() ) . $sep . $site;
			$default['canonical'] = deskman_seo_current_url();
			$at = get_the_archive_description();
			if ( trim( wp_strip_all_tags( $at ) ) !== '' ) { $default['desc'] = deskman_seo_trim( $at ); }
		} elseif ( is_search() ) {
			$default['title']   = sprintf( 'Search: %s', wp_strip_all_tags( get_search_query() ) ) . $sep . $site;
			$default['noindex'] = true;
			$default['canonical'] = deskman_seo_current_url();
		} elseif ( is_404() ) {
			$default['title']   = 'Page not found' . $sep . $site;
			$default['noindex'] = true;
			$default['canonical'] = deskman_seo_current_url();
		} else {
			$default['canonical'] = deskman_seo_current_url();
		}

		$ctx = $default;
		return $ctx;
	}
}

/** Best-guess canonical for non-singular views (search/archive/paged). */
if ( ! function_exists( 'deskman_seo_current_url' ) ) {
	function deskman_seo_current_url() {
		global $wp;
		$url = home_url( add_query_arg( array(), isset( $wp->request ) ? $wp->request : '' ) );
		return trailingslashit( $url );
	}
}

/** Interior templates keep their lead copy in the plugin under predictable
 *  keys. Map a page slug to its lead field so the meta description matches the
 *  visible intro rather than the page's raw block content. */
if ( ! function_exists( 'deskman_seo_lead_for' ) ) {
	function deskman_seo_lead_for( $id ) {
		$slug = get_post_field( 'post_name', $id );
		$map  = array(
			'services' => array( 'v5_svc_lead', 'Design, custom development, SEO and care — scoped together, so your theme, plugins and search visibility are built to work as one system.' ),
			'pricing'  => array( 'v5_pkp_lead', 'Every project is scoped to what you actually need. Choose a starting point, book a short call, and get a fixed quote — no surprises later.' ),
			'packages' => array( 'v5_pkp_lead', 'Every project is scoped to what you actually need. Choose a starting point, book a short call, and get a fixed quote — no surprises later.' ),
			'studio'   => array( 'v5_about_lead', 'Deskman is an independent web studio in Visakhapatnam. We design, build and look after custom WordPress websites.' ),
			'about'    => array( 'v5_about_lead', 'Deskman is an independent web studio in Visakhapatnam. We design, build and look after custom WordPress websites.' ),
			'work'     => array( 'v5_work_page_lead', 'Websites and platforms we have designed and built — from WooCommerce stores to school websites and city-wide platforms.' ),
			'contact'  => array( 'v5_contact_lead', 'Tell us what you are planning. You will get a reply within one working day.' ),
		);
		if ( isset( $map[ $slug ] ) ) {
			return trim( (string) deskman_opt( $map[ $slug ][0], $map[ $slug ][1] ) );
		}
		if ( 'page-service.php' === get_page_template_slug( $id ) && function_exists( 'dm_services' ) ) {
			$all = dm_services();
			$k   = dm_service_alias( $slug );
			if ( isset( $all[ $k ] ) ) { return $all[ $k ]['lead']; }
		}
		return '';
	}
}

/* ---------------------------------------------------------------------------
   <head> output: title fallback, description, canonical, robots, OG, Twitter.
   Runs at priority 1 so it sits high in <head>. Core's rel_canonical is
   removed so there is never a duplicated canonical tag.
   --------------------------------------------------------------------------- */

remove_action( 'wp_head', 'rel_canonical' );

/** Let WordPress' title-tag support use our resolved title verbatim. */
add_filter( 'pre_get_document_title', function ( $title ) {
	$ctx = deskman_seo_context();
	return $ctx['title'] !== '' ? $ctx['title'] : $title;
}, 20 );

/** Keep the OG/Twitter title in sync with the document title on themes that
 *  build titles via document_title_parts as well. */
add_filter( 'document_title_separator', function () { return '—'; } );

add_action( 'wp_head', function () {
	$ctx  = deskman_seo_context();
	$site = deskman_seo_site_name();
	$img  = deskman_seo_image();
	$desc = $ctx['desc'];
	$url  = $ctx['canonical'];

	echo "\n<!-- Deskman Studio SEO -->\n";

	if ( $desc !== '' ) {
		echo '<meta name="description" content="' . esc_attr( $desc ) . '" />' . "\n";
	}

	/* Robots. Respect the site's global discourage-search-engines switch, then
	   any per-page noindex. max-image-preview:large opts big thumbnails into
	   rich/answer results. */
	$robots = array();
	if ( $ctx['noindex'] || ! (int) get_option( 'blog_public', 1 ) ) {
		$robots[] = 'noindex';
		$robots[] = 'nofollow';
	} else {
		$robots[] = 'index';
		$robots[] = 'follow';
		$robots[] = 'max-image-preview:large';
		$robots[] = 'max-snippet:-1';
		$robots[] = 'max-video-preview:-1';
	}
	echo '<meta name="robots" content="' . esc_attr( implode( ', ', $robots ) ) . '" />' . "\n";

	if ( $url !== '' ) {
		echo '<link rel="canonical" href="' . esc_url( $url ) . '" />' . "\n";
	}

	/* Open Graph */
	echo '<meta property="og:type" content="' . esc_attr( $ctx['type'] ) . '" />' . "\n";
	echo '<meta property="og:site_name" content="' . esc_attr( $site ) . '" />' . "\n";
	echo '<meta property="og:title" content="' . esc_attr( $ctx['title'] ) . '" />' . "\n";
	if ( $desc !== '' ) {
		echo '<meta property="og:description" content="' . esc_attr( $desc ) . '" />' . "\n";
	}
	if ( $url !== '' ) {
		echo '<meta property="og:url" content="' . esc_url( $url ) . '" />' . "\n";
	}
	echo '<meta property="og:locale" content="' . esc_attr( str_replace( '-', '_', get_bloginfo( 'language' ) ?: 'en_US' ) ) . '" />' . "\n";
	if ( $img !== '' ) {
		echo '<meta property="og:image" content="' . esc_url( $img ) . '" />' . "\n";
		echo '<meta property="og:image:alt" content="' . esc_attr( $site ) . '" />' . "\n";
	}

	/* Twitter */
	echo '<meta name="twitter:card" content="' . ( $img !== '' ? 'summary_large_image' : 'summary' ) . '" />' . "\n";
	echo '<meta name="twitter:title" content="' . esc_attr( $ctx['title'] ) . '" />' . "\n";
	if ( $desc !== '' ) {
		echo '<meta name="twitter:description" content="' . esc_attr( $desc ) . '" />' . "\n";
	}
	if ( $img !== '' ) {
		echo '<meta name="twitter:image" content="' . esc_url( $img ) . '" />' . "\n";
	}
	$tw = trim( (string) deskman_opt( 'seo_twitter', '' ) );
	if ( $tw !== '' ) {
		$tw = '@' . ltrim( $tw, '@' );
		echo '<meta name="twitter:site" content="' . esc_attr( $tw ) . '" />' . "\n";
		echo '<meta name="twitter:creator" content="' . esc_attr( $tw ) . '" />' . "\n";
	}
}, 1 );

/* ---------------------------------------------------------------------------
   JSON-LD structured data — the part that matters most for answer engines.

   One connected @graph with stable @id references, so Google and LLM crawlers
   resolve Organization ← WebSite ← WebPage ← Breadcrumb as one entity rather
   than four loose blobs. Page-type-specific nodes (Service list, FAQPage,
   OfferCatalog) are appended where they apply.
   --------------------------------------------------------------------------- */

if ( ! function_exists( 'deskman_seo_org_node' ) ) {
	function deskman_seo_org_node() {
		$home = home_url( '/' );
		$site = deskman_seo_site_name();

		$same = array();
		foreach ( array( 'social_ig', 'social_in', 'social_x' ) as $k ) {
			$u = trim( (string) deskman_opt( $k, '' ) );
			if ( $u !== '' && $u !== '#' && filter_var( $u, FILTER_VALIDATE_URL ) ) { $same[] = $u; }
		}

		$logo = trim( (string) deskman_opt( 'seo_logo', '' ) );
		if ( $logo === '' ) { $logo = deskman_seo_image(); }

		/* ProfessionalService is a LocalBusiness subtype — the right type for a
		   studio with a city base and an area it serves. That drives local +
		   "near me" style answers. */
		$node = array(
			'@type'       => array( 'Organization', 'ProfessionalService' ),
			'@id'         => $home . '#org',
			'name'        => $site,
			'url'         => $home,
			'description' => deskman_seo_trim( deskman_opt( 'seo_desc', deskman_opt( 'v5_hero_lead', '' ) ) ),
		);

		if ( $logo !== '' ) {
			$node['logo']  = array( '@type' => 'ImageObject', 'url' => $logo );
			$node['image'] = $logo;
		}

		$email = trim( (string) deskman_opt( 'contact_email', '' ) );
		if ( $email !== '' ) { $node['email'] = $email; }
		$phone = trim( (string) deskman_opt( 'seo_phone', '' ) );
		if ( $phone !== '' ) { $node['telephone'] = $phone; }

		/* Postal address — city/region default to Visakhapatnam so local schema
		   is present out of the box; every part is individually editable. */
		$street  = trim( (string) deskman_opt( 'seo_street', '' ) );
		$city    = trim( (string) deskman_opt( 'seo_locality', 'Visakhapatnam' ) );
		$region  = trim( (string) deskman_opt( 'seo_region', 'Andhra Pradesh' ) );
		$postal  = trim( (string) deskman_opt( 'seo_postal', '' ) );
		$country = trim( (string) deskman_opt( 'seo_country', 'IN' ) );
		$addr = array( '@type' => 'PostalAddress' );
		if ( $street !== '' )  { $addr['streetAddress'] = $street; }
		if ( $city !== '' )    { $addr['addressLocality'] = $city; }
		if ( $region !== '' )  { $addr['addressRegion'] = $region; }
		if ( $postal !== '' )  { $addr['postalCode'] = $postal; }
		if ( $country !== '' ) { $addr['addressCountry'] = $country; }
		if ( count( $addr ) > 1 ) { $node['address'] = $addr; }

		$lat = trim( (string) deskman_opt( 'seo_lat', '' ) );
		$lng = trim( (string) deskman_opt( 'seo_lng', '' ) );
		if ( is_numeric( $lat ) && is_numeric( $lng ) ) {
			$node['geo'] = array( '@type' => 'GeoCoordinates', 'latitude' => (float) $lat, 'longitude' => (float) $lng );
		}

		if ( $city !== '' ) {
			$node['areaServed'] = array(
				array( '@type' => 'City', 'name' => $city ),
				array( '@type' => 'Country', 'name' => 'India' ),
				'Worldwide (remote)',
			);
		}

		$price = trim( (string) deskman_opt( 'seo_price_range', '₹₹' ) );
		if ( $price !== '' ) { $node['priceRange'] = $price; }

		$founded = trim( (string) deskman_opt( 'seo_founded', '' ) );
		if ( $founded !== '' ) { $node['foundingDate'] = $founded; }

		if ( $same ) { $node['sameAs'] = array_values( $same ); }

		/* The six services, as an offer catalog on the organisation. */
		$svc = array();
		for ( $i = 1; $i <= 6; $i++ ) {
			$t = trim( (string) deskman_opt( "svc{$i}_t", '' ) );
			$d = trim( (string) deskman_opt( "svc{$i}_desc", deskman_opt( "svc{$i}_s", '' ) ) );
			if ( $t === '' ) { continue; }
			$item = array(
				'@type' => 'Offer',
				'itemOffered' => array(
					'@type' => 'Service',
					'name'  => $t,
					'provider' => array( '@id' => $home . '#org' ),
				),
			);
			if ( $d !== '' ) { $item['itemOffered']['description'] = deskman_seo_trim( $d, 300 ); }
			$svc[] = $item;
		}
		if ( $svc ) {
			$node['hasOfferCatalog'] = array(
				'@type' => 'OfferCatalog',
				'name'  => 'Services',
				'itemListElement' => $svc,
			);
		}

		return $node;
	}
}

if ( ! function_exists( 'deskman_seo_breadcrumb_node' ) ) {
	function deskman_seo_breadcrumb_node() {
		if ( is_front_page() ) { return null; }
		$home  = home_url( '/' );
		$items = array( array(
			'@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $home,
		) );
		$pos = 2;

		if ( is_singular( 'deskman_project' ) ) {
			$work = deskman_url( 'work' );
			$items[] = array( '@type' => 'ListItem', 'position' => $pos++, 'name' => 'Work', 'item' => $work );
			$items[] = array( '@type' => 'ListItem', 'position' => $pos++, 'name' => wp_strip_all_tags( get_the_title() ), 'item' => get_permalink() );
		} elseif ( is_singular() ) {
			$id = get_queried_object_id();
			$parent = wp_get_post_parent_id( $id );
			if ( $parent ) {
				$items[] = array( '@type' => 'ListItem', 'position' => $pos++, 'name' => wp_strip_all_tags( get_the_title( $parent ) ), 'item' => get_permalink( $parent ) );
			}
			$items[] = array( '@type' => 'ListItem', 'position' => $pos++, 'name' => wp_strip_all_tags( get_the_title( $id ) ), 'item' => get_permalink( $id ) );
		} elseif ( is_archive() ) {
			$items[] = array( '@type' => 'ListItem', 'position' => $pos++, 'name' => wp_strip_all_tags( get_the_archive_title() ), 'item' => deskman_seo_current_url() );
		} else {
			return null;
		}

		$ctx = deskman_seo_context();
		return array(
			'@type' => 'BreadcrumbList',
			'@id'   => $ctx['canonical'] . '#breadcrumb',
			'itemListElement' => $items,
		);
	}
}

/** FAQPage node built from the pricing FAQ fields — the block most likely to
 *  win an AI answer or a Google FAQ rich result. Only emitted on the pricing
 *  page, and only for Q/A pairs that are actually filled in. */
if ( ! function_exists( 'deskman_seo_faq_node' ) ) {
	function deskman_seo_faq_node() {
		$items = array();
		if ( is_front_page() || is_page( array( 'pricing', 'packages' ) ) ) {
			$items = function_exists( 'dm_faq' ) ? dm_faq() : array();
		} elseif ( is_page_template( 'page-service.php' ) && function_exists( 'dm_services' ) ) {
			$all   = dm_services();
			$slug  = dm_service_alias( get_post_field( 'post_name', get_queried_object_id() ) );
			$items = isset( $all[ $slug ] ) ? $all[ $slug ]['faq'] : array();
		}
		$qa = array();
		foreach ( $items as $it ) {
			$qa[] = array( '@type' => 'Question', 'name' => wp_strip_all_tags( $it[0] ), 'acceptedAnswer' => array( '@type' => 'Answer', 'text' => wp_strip_all_tags( $it[1] ) ) );
		}
		if ( ! $qa ) { return null; }
		$url = is_front_page() ? home_url( '/' ) : get_permalink();
		return array( '@type' => 'FAQPage', '@id' => $url . '#faq', 'mainEntity' => $qa );
	}
}

/** Service ItemList on the services page, from the theme's service list. */
if ( ! function_exists( 'deskman_seo_services_node' ) ) {
	function deskman_seo_services_node() {
		if ( ! ( is_page_template( 'page-services.php' ) || is_page( 'services' ) ) || ! function_exists( 'dm_services' ) ) { return null; }
		$home  = home_url( '/' );
		$items = array();
		$pos   = 1;
		foreach ( dm_services() as $slug => $s ) {
			$items[] = array( '@type' => 'ListItem', 'position' => $pos++, 'item' => array(
				'@type' => 'Service', 'name' => $s['title'], 'description' => deskman_seo_trim( $s['short'], 300 ),
				'url' => dm_service_url( $slug ), 'provider' => array( '@id' => $home . '#org' ),
				'areaServed' => trim( (string) deskman_opt( 'seo_locality', 'Visakhapatnam' ) ),
			) );
		}
		return array( '@type' => 'ItemList', '@id' => get_permalink() . '#services', 'name' => 'Services', 'itemListElement' => $items );
	}
}

add_action( 'wp_head', function () {
	$ctx  = deskman_seo_context();
	$home = home_url( '/' );
	$site = deskman_seo_site_name();

	$graph = array();

	/* Organization / LocalBusiness */
	$graph[] = deskman_seo_org_node();

	/* WebSite + on-site search action */
	$graph[] = array(
		'@type'     => 'WebSite',
		'@id'       => $home . '#website',
		'url'       => $home,
		'name'      => $site,
		'publisher' => array( '@id' => $home . '#org' ),
		'inLanguage'=> get_bloginfo( 'language' ),
		'potentialAction' => array(
			'@type'       => 'SearchAction',
			'target'      => array(
				'@type'       => 'EntryPoint',
				'urlTemplate' => $home . '?s={search_term_string}',
			),
			'query-input' => 'required name=search_term_string',
		),
	);

	/* WebPage for the current view (skip pure 404/search noise). */
	if ( ! is_404() && ! is_search() ) {
		$webpage = array(
			'@type'      => ( is_front_page() ? array( 'WebPage', 'CollectionPage' ) : 'WebPage' ),
			'@id'        => $ctx['canonical'] . '#webpage',
			'url'        => $ctx['canonical'],
			'name'       => $ctx['title'],
			'isPartOf'   => array( '@id' => $home . '#website' ),
			'about'      => array( '@id' => $home . '#org' ),
			'inLanguage' => get_bloginfo( 'language' ),
		);
		if ( $ctx['desc'] !== '' ) { $webpage['description'] = $ctx['desc']; }
		$img = deskman_seo_image();
		if ( $img !== '' ) { $webpage['primaryImageOfPage'] = array( '@type' => 'ImageObject', 'url' => $img ); }
		if ( is_singular() ) {
			$webpage['datePublished'] = get_the_date( 'c' );
			$webpage['dateModified']  = get_the_modified_date( 'c' );
		}
		$graph[] = $webpage;
	}

	/* Breadcrumb, FAQ, Services — appended when they apply. */
	foreach ( array( deskman_seo_breadcrumb_node(), deskman_seo_faq_node(), deskman_seo_services_node() ) as $node ) {
		if ( is_array( $node ) ) { $graph[] = $node; }
	}

	$payload = array( '@context' => 'https://schema.org', '@graph' => array_values( array_filter( $graph ) ) );

	$json = wp_json_encode( $payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
	if ( $json === false ) { return; }

	/* Harden for the <script> context: even though every field is passed
	   through wp_strip_all_tags() upstream, a stray "<", ">" or "&" must never
	   be able to close the tag early. Escaping to \u00XX keeps the JSON valid
	   and the markup safe — this is the same hardening WordPress core and the
	   major SEO plugins apply. */
	$json = str_replace(
		array( '<', '>', '&', "\xE2\x80\xA8", "\xE2\x80\xA9" ),
		array( '\u003C', '\u003E', '\u0026', '\u2028', '\u2029' ),
		$json
	);

	echo "\n" . '<script type="application/ld+json">' . $json . '</script>' . "\n";
}, 2 );

/* ---------------------------------------------------------------------------
   Sensible defaults elsewhere: give the XML sitemap a hint about our project
   archive and make sure attachment pages don't dilute the index.
   --------------------------------------------------------------------------- */

/** Redirect ugly attachment pages to the file — they are thin, duplicate URLs
 *  that answer engines and Google both penalise as index bloat. */
add_action( 'template_redirect', function () {
	if ( is_attachment() ) {
		$url = wp_get_attachment_url( get_queried_object_id() );
		if ( $url ) { wp_safe_redirect( $url, 301 ); exit; }
	}
} );
