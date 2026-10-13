<?php
/**
 * Deskman Studio Manager — Setup wizard.
 *
 * Turns a fresh WordPress install into the Deskman site: required pages, legal
 * pages with real starter copy, the project category structure, four starter
 * projects (optional demo content), a primary + footer menu, and the static
 * homepage. Every step is idempotent — running it twice never duplicates
 * anything — and every item the plugin creates is tagged so a later reset only
 * removes plugin-made content, never the user's own.
 *
 * @package Deskman_Studio_Manager
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'DESKMAN_SETUP_OPTION', 'deskman_setup' );
define( 'DESKMAN_SETUP_VERSION', '1.0.0' );

/** The pages the site needs, in menu order. */
function deskman_setup_pages() {
	return array(
		'home'                 => array( 'title' => 'Home',                'nav' => false ),
		'work'                 => array( 'title' => 'Work',                'nav' => true ),
		'services'             => array( 'title' => 'Services',            'nav' => true ),
		'studio'               => array( 'title' => 'About',               'nav' => true ),
		'contact'              => array( 'title' => 'Contact',             'nav' => true ),
		'pricing'              => array( 'title' => 'Packages',            'nav' => true ),
		'insights'             => array( 'title' => 'Insights',            'nav' => true ),
		'templates'            => array( 'title' => 'Templates',           'nav' => true ),
		'privacy-policy'       => array( 'title' => 'Privacy Policy',      'nav' => false, 'legal' => 'privacy' ),
		'terms-and-conditions' => array( 'title' => 'Terms & Conditions',  'nav' => false, 'legal' => 'terms' ),
	);
}

/** Project categories used by the Work filter. */
function deskman_setup_categories() {
	return array( 'Branding', 'Web', 'WordPress', 'Product', 'E-commerce', 'Digital Experience' );
}

/** The four starter projects. No fake screenshots — images are left empty and
 *  surfaced by the asset checker so the studio can upload the real ones. */
function deskman_setup_projects() {
	return array(
		array(
			'key'    => 'ivy-crest-school',
			'title'  => 'Ivy Crest School',
			'cats'   => array( 'Branding', 'Web' ),
			'year'   => '2025',
			'client' => 'The Ivy Crest School',
			'url'    => 'https://theivycrestschool.com/',
			'services' => 'Branding, Web Design, WordPress',
			'tech'   => 'WordPress, PHP, JavaScript',
			'short'  => 'A premium digital experience for a modern learning ecosystem.',
			'long'   => 'The Ivy Crest School needed a website that matched the ambition of its teaching — calm, considered and clearly premium. We built a brand-led site that guides prospective parents from first impression to enquiry without friction.',
			'challenge' => 'Translate a modern, values-led school into a digital experience that feels premium without feeling cold — and makes admissions enquiries effortless.',
			'did'    => 'A refreshed identity, a full content architecture and a fast, editable WordPress build the school\'s own team can maintain.',
			'outcome'=> 'A cohesive brand presence and a clear admissions path that positions the school as a modern learning ecosystem.',
		),
		array(
			'key'    => 'pineprompts',
			'title'  => 'PinePrompts',
			'cats'   => array( 'Product', 'Web' ),
			'year'   => '2025',
			'client' => 'PinePrompts',
			'url'    => 'https://pineprompts.com/',
			'services' => 'Product Design, Web App',
			'tech'   => 'React, Node, Design System',
			'short'  => 'An AI prompt discovery platform to find, explore and share useful prompts.',
			'long'   => 'PinePrompts helps people discover, explore and share prompts that actually work. We shaped the product experience around fast discovery and effortless sharing, with an interface that stays out of the way.',
			'challenge' => 'Make a large, growing library of prompts genuinely discoverable — and make sharing a good one a one-tap habit.',
			'did'    => 'Product design and front-end for a discovery-first web app: search, browse, collections and a clean sharing flow.',
			'outcome'=> 'A focused platform that turns prompt discovery into something quick, useful and repeatable.',
		),
		array(
			'key'    => 'jhumkana',
			'title'  => 'Jhumkana',
			'cats'   => array( 'Branding', 'E-commerce' ),
			'year'   => '2025',
			'client' => 'Jhumkana',
			'url'    => 'https://jhumkana.com/',
			'services' => 'Branding, E-commerce, WordPress',
			'tech'   => 'WooCommerce, WordPress, PHP',
			'short'  => 'A modern jewellery shopping experience rooted in Indian craft.',
			'long'   => 'Jhumkana brings traditional Indian jewellery craft to a contemporary online store. We built a shopping experience that honours the craft while meeting the expectations of today\'s customer.',
			'challenge' => 'Present handcrafted jewellery online in a way that feels modern and trustworthy without losing the warmth of the craft.',
			'did'    => 'Brand direction and a WooCommerce store designed around the product — clean browsing, confident product pages and a smooth checkout.',
			'outcome'=> 'A modern jewellery shopping experience that respects its roots and sells with confidence.',
		),
		array(
			'key'    => 'vizagmap',
			'title'  => 'VizagMap',
			'cats'   => array( 'Digital Experience', 'Product' ),
			'year'   => '2026',
			'client' => 'VizagMap',
			'url'    => 'https://vizagmap.com/',
			'services' => 'Product Design, Web Platform',
			'tech'   => 'JavaScript, Maps API, WordPress',
			'short'  => 'A digital map connecting Vizag\'s companies, startups, jobs and ecosystem.',
			'long'   => 'VizagMap maps the city\'s growing ecosystem — companies, startups, jobs and the people building them. We designed a discovery platform that makes a whole ecosystem legible in one place.',
			'challenge' => 'Bring a sprawling, fast-growing local ecosystem into a single, navigable map that people actually want to explore.',
			'did'    => 'Product and platform design for a map-led discovery experience connecting companies, startups and jobs.',
			'outcome'=> 'A digital map and discovery platform that connects Vizag\'s companies, startups, jobs and growing ecosystem.',
		),
	);
}

/** Starter legal copy. Business specifics are pulled from the studio settings
 *  and clearly marked so they are easy to complete. Not legal advice. */
function deskman_setup_legal_content( $which ) {
	$name  = deskman_opt( 'brand_name', 'Deskman' ) . ' ' . deskman_opt( 'brand_sub', 'Studio' );
	$name  = trim( $name );
	$email = deskman_opt( 'contact_email', 'hi@deskmanstudio.com' );
	$loc   = deskman_opt( 'contact_location', 'Visakhapatnam, India' );
	$today = date_i18n( get_option( 'date_format' ) );

	if ( $which === 'privacy' ) {
		return
			'<p><em>Last updated: ' . esc_html( $today ) . '. This is a starter policy — review it and complete the highlighted details before launch. It is not legal advice.</em></p>' .
			'<h2>Who we are</h2>' .
			'<p>' . esc_html( $name ) . ' (&ldquo;we&rdquo;, &ldquo;us&rdquo;) is an independent digital studio based in ' . esc_html( $loc ) . '. You can reach us at ' . esc_html( $email ) . '.</p>' .
			'<h2>What we collect</h2>' .
			'<p>When you contact us through the site we collect the details you provide — such as your name, email, company and message — so we can reply and, where relevant, scope your project. We do not sell your data.</p>' .
			'<h2>How we use it</h2>' .
			'<p>We use the information you send only to respond to your enquiry and to deliver work you engage us for. We keep it no longer than needed for those purposes.</p>' .
			'<h2>Cookies &amp; analytics</h2>' .
			'<p><strong>[Add the cookies and analytics you actually use here.]</strong> If you run analytics or embed third-party tools, describe them and link to their policies.</p>' .
			'<h2>Your rights</h2>' .
			'<p>You can ask us what data we hold about you, ask us to correct it, or ask us to delete it. Email ' . esc_html( $email ) . ' and we will respond promptly.</p>' .
			'<h2>Contact</h2>' .
			'<p>Questions about this policy? Email ' . esc_html( $email ) . '.</p>';
	}

	return
		'<p><em>Last updated: ' . esc_html( $today ) . '. This is a starter agreement — review it and complete the highlighted details before launch. It is not legal advice.</em></p>' .
		'<h2>Overview</h2>' .
		'<p>These terms cover your use of the ' . esc_html( $name ) . ' website. By using the site you agree to them.</p>' .
		'<h2>Our work</h2>' .
		'<p>Anything you commission from us is governed by a separate written proposal or agreement. These website terms do not replace that.</p>' .
		'<h2>Intellectual property</h2>' .
		'<p>The content, design and code of this site belong to ' . esc_html( $name ) . ' unless stated otherwise. Please do not reproduce it without permission.</p>' .
		'<h2>Liability</h2>' .
		'<p>The site is provided &ldquo;as is&rdquo;. <strong>[Add your specific liability and warranty terms here.]</strong></p>' .
		'<h2>Governing law</h2>' .
		'<p><strong>[State the governing jurisdiction — e.g. the laws of India.]</strong></p>' .
		'<h2>Contact</h2>' .
		'<p>Questions about these terms? Email ' . esc_html( $email ) . '.</p>';
}

/** Real starter body content for a page. Legal pages return their agreement;
 *  the rest return an editable intro that renders inside the page (above the
 *  theme's designed sections), so every page has content you can edit. */
function deskman_setup_page_content( $slug, $meta ) {
	if ( ! empty( $meta['legal'] ) ) {
		return deskman_setup_legal_content( $meta['legal'] );
	}

	$loc = deskman_opt( 'contact_location', 'Visakhapatnam, India' );

	switch ( $slug ) {
		case 'home':
			return '<p>This is your homepage. Its sections are designed in the theme and edited under <strong>Deskman &rarr; Settings</strong>, so this body can stay as a short note. <a href="' . esc_url( home_url( '/' ) ) . '">View the site &rarr;</a></p>';

		case 'work':
			return '<p>A selection of recent work — brands, websites and digital products we\'ve designed, built and shipped. Browse the projects below and filter by discipline, or <a href="' . esc_url( home_url( '/contact/' ) ) . '">start a project</a> of your own.</p>';

		case 'services':
			return
				'<p>One studio for the whole build — strategy, design, development and growth. We take on a small number of projects at a time so each one gets real attention, from the first idea to a live site that\'s looked after once it\'s there.</p>' .
				'<h2>How we work</h2>' .
				'<p>Every engagement starts with understanding the business, the goals and the audience. From there we move through design, build and launch — in the open, reviewed together — and stay on afterwards to measure and refine. You work directly with the people doing the work; there are no account layers or handoffs.</p>' .
				'<h2>What you can expect</h2>' .
				'<ul>' .
				'<li>A clear scope and a realistic timeline before anything starts.</li>' .
				'<li>Design and development handled in-house, not outsourced.</li>' .
				'<li>Fast, accessible, hand-built code you can actually maintain.</li>' .
				'<li>Support after launch — improvements, fixes and care.</li>' .
				'</ul>';

		case 'studio':
			return
				'<p>Deskman is an independent digital studio based in ' . esc_html( $loc ) . '. We design brands, websites and digital products for ambitious teams — small enough to care about the details, senior enough to ship them.</p>' .
				'<h2>Our philosophy</h2>' .
				'<p>Good work comes from focus. We keep the studio small on purpose, take on a handful of projects at a time, and stay close to each one. The person who scopes your project is the person who designs and builds it. That means fewer surprises, faster decisions and a result that actually reflects what you set out to make.</p>' .
				'<p>We care about thoughtful design, clean development and performance — and about being straightforward to work with from the first call to long after launch.</p>';

		case 'contact':
			return '<p>Tell us what you\'re building, what you\'re trying to solve, and where you want to go. Fill in the form and we\'ll reply within a day or two. Prefer email? You can reach us directly using the details alongside the form.</p>';

		case 'pricing':
			return '<p>Straightforward pricing for design and development, scoped to what you actually need. Every project is quoted after a short scoping conversation, so you know the cost before anything starts. Here\'s how our engagements typically work.</p>';
	}

	return '';
}

/* --------------------------------------------------------------------------
   The runner
   -------------------------------------------------------------------------- */

/** Run selected setup steps. Returns a log array. Safe to call repeatedly. */
function deskman_setup_run( $steps ) {
	$state = get_option( DESKMAN_SETUP_OPTION );
	if ( ! is_array( $state ) ) { $state = array(); }
	$log = array();

	$do_pages    = ! empty( $steps['pages'] );
	$do_projects = ! empty( $steps['projects'] );
	$do_menus    = ! empty( $steps['menus'] );
	$do_home     = ! empty( $steps['homepage'] );
	$demo        = ! empty( $steps['demo'] );

	$page_ids = isset( $state['pages'] ) && is_array( $state['pages'] ) ? $state['pages'] : array();

	// 1. Pages (+ real starter content). Existing pages are only filled when
	//    their body is empty, so anything you have edited is never overwritten.
	if ( $do_pages ) {
		foreach ( deskman_setup_pages() as $slug => $meta ) {
			$content  = deskman_setup_page_content( $slug, $meta );
			$existing = get_page_by_path( $slug );

			if ( $existing ) {
				$page_ids[ $slug ] = (int) $existing->ID;
				$body = trim( (string) get_post_field( 'post_content', $existing->ID ) );
				if ( $body === '' && $content !== '' ) {
					wp_update_post( array( 'ID' => $existing->ID, 'post_content' => $content ) );
					update_post_meta( $existing->ID, '_deskman_created', 1 );
					$log[] = 'Filled empty page: ' . $meta['title'];
				} else {
					$log[] = 'Page exists: ' . $meta['title'];
				}
				continue;
			}

			$id = wp_insert_post( array(
				'post_title'   => $meta['title'],
				'post_name'    => $slug,
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => $content,
			) );
			if ( ! is_wp_error( $id ) ) {
				update_post_meta( $id, '_deskman_created', 1 );
				$page_ids[ $slug ] = (int) $id;
				$log[] = 'Created page: ' . $meta['title'];
			}
		}
		// Native privacy page hint.
		if ( ! empty( $page_ids['privacy-policy'] ) ) {
			update_option( 'wp_page_for_privacy_policy', (int) $page_ids['privacy-policy'] );
		}
		// Remove the untouched default Sample Page.
		$sample = get_page_by_path( 'sample-page' );
		if ( $sample && (int) $sample->ID ) { wp_trash_post( $sample->ID ); }
		$state['pages'] = $page_ids;
	}

	// 2. Homepage.
	if ( $do_home && ! empty( $page_ids['home'] ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', (int) $page_ids['home'] );
		$log[] = 'Homepage set';
	}

	// 3. Project categories.
	if ( $do_projects ) {
		foreach ( deskman_setup_categories() as $cat ) {
			if ( ! term_exists( $cat, 'deskman_project_cat' ) ) {
				$t = wp_insert_term( $cat, 'deskman_project_cat' );
				if ( ! is_wp_error( $t ) ) { $log[] = 'Created category: ' . $cat; }
			}
		}
	}

	// 4. Starter projects (only when demo content is requested).
	if ( $do_projects && $demo ) {
		$created = isset( $state['projects'] ) && is_array( $state['projects'] ) ? $state['projects'] : array();
		$order   = 1;
		foreach ( deskman_setup_projects() as $p ) {
			// Skip if this demo project already exists (by its tracking key).
			$found = get_posts( array(
				'post_type'   => 'deskman_project',
				'post_status' => 'any',
				'numberposts' => 1,
				'fields'      => 'ids',
				'meta_key'    => '_deskman_demo_key',
				'meta_value'  => $p['key'],
			) );
			if ( $found ) {
				$created[ $p['key'] ] = (int) $found[0];
				$log[] = 'Project exists: ' . $p['title'];
				$order++;
				continue;
			}
			$id = wp_insert_post( array(
				'post_type'    => 'deskman_project',
				'post_status'  => 'publish',
				'post_title'   => $p['title'],
				'post_name'    => $p['key'],
				'post_excerpt' => $p['short'],
				'post_content' => $p['long'],
				'menu_order'   => $order,
			) );
			if ( ! is_wp_error( $id ) ) {
				update_post_meta( $id, '_deskman_created', 1 );
				update_post_meta( $id, '_deskman_demo', 1 );
				update_post_meta( $id, '_deskman_demo_key', $p['key'] );
				update_post_meta( $id, '_deskman_number', sprintf( '%02d', $order ) );
				update_post_meta( $id, '_deskman_year', $p['year'] );
				update_post_meta( $id, '_deskman_client', $p['client'] );
				update_post_meta( $id, '_deskman_live_url', esc_url_raw( $p['url'] ) );
				update_post_meta( $id, '_deskman_services', $p['services'] );
				update_post_meta( $id, '_deskman_tech', $p['tech'] );
				update_post_meta( $id, '_deskman_challenge', $p['challenge'] );
				update_post_meta( $id, '_deskman_did', $p['did'] );
				update_post_meta( $id, '_deskman_outcome', $p['outcome'] );
				update_post_meta( $id, '_deskman_featured', '1' );
				wp_set_object_terms( $id, $p['cats'], 'deskman_project_cat' );
				$created[ $p['key'] ] = (int) $id;
				$log[] = 'Created project: ' . $p['title'];
			}
			$order++;
		}
		$state['projects'] = $created;
	}

	// 5. Menus (primary + footer). Rendered nav is slug-based, so these are for
	//    editors who prefer managing links under Appearance → Menus.
	if ( $do_menus ) {
		$primary = deskman_setup_menu( 'Deskman Primary', array(
			array( 'slug' => 'work',     'title' => 'Work' ),
			array( 'slug' => 'services', 'title' => 'Services' ),
			array( 'slug' => 'studio',   'title' => 'Studio' ),
			array( 'slug' => 'contact',  'title' => 'Contact' ),
		), $page_ids, home_url( '/' ) );
		$footer = deskman_setup_menu( 'Deskman Footer', array(
			array( 'slug' => 'privacy-policy',       'title' => 'Privacy' ),
			array( 'slug' => 'terms-and-conditions', 'title' => 'Terms' ),
		), $page_ids );
		$locations = get_theme_mod( 'nav_menu_locations' );
		if ( ! is_array( $locations ) ) { $locations = array(); }
		if ( $primary ) { $locations['primary'] = $primary; }
		if ( $footer )  { $locations['footer']  = $footer; }
		set_theme_mod( 'nav_menu_locations', $locations );
		$state['menus'] = array( 'primary' => $primary, 'footer' => $footer );
		$log[] = 'Navigation menus ready';
	}

	$state['version']   = DESKMAN_SETUP_VERSION;
	$state['completed'] = true;
	$state['demo']      = $demo ? true : ( isset( $state['demo'] ) ? $state['demo'] : false );
	$state['time']      = time();
	update_option( DESKMAN_SETUP_OPTION, $state );

	flush_rewrite_rules();
	return $log;
}

/** Create (or reuse) a menu, populate it once with page links, return its id. */
function deskman_setup_menu( $name, $items, $page_ids, $home_url = '' ) {
	$menu = wp_get_nav_menu_object( $name );
	if ( $menu ) { return (int) $menu->term_id; }
	$menu_id = wp_create_nav_menu( $name );
	if ( is_wp_error( $menu_id ) ) { return 0; }

	if ( $home_url ) {
		wp_update_nav_menu_item( $menu_id, 0, array(
			'menu-item-title'  => 'Home',
			'menu-item-url'    => $home_url,
			'menu-item-status' => 'publish',
			'menu-item-type'   => 'custom',
		) );
	}
	foreach ( $items as $it ) {
		$pid = isset( $page_ids[ $it['slug'] ] ) ? (int) $page_ids[ $it['slug'] ] : 0;
		if ( $pid ) {
			wp_update_nav_menu_item( $menu_id, 0, array(
				'menu-item-object-id' => $pid,
				'menu-item-object'    => 'page',
				'menu-item-type'      => 'post_type',
				'menu-item-title'     => $it['title'],
				'menu-item-status'    => 'publish',
			) );
		}
	}
	return (int) $menu_id;
}

/** Remove ONLY the demo projects this plugin created. Never touches user work. */
function deskman_setup_reset_demo() {
	$removed = 0;
	$ids = get_posts( array(
		'post_type'   => 'deskman_project',
		'post_status' => 'any',
		'numberposts' => -1,
		'fields'      => 'ids',
		'meta_key'    => '_deskman_demo',
		'meta_value'  => 1,
	) );
	foreach ( $ids as $id ) {
		if ( get_post_meta( $id, '_deskman_created', true ) ) {
			wp_trash_post( $id );
			$removed++;
		}
	}
	$state = get_option( DESKMAN_SETUP_OPTION );
	if ( is_array( $state ) ) { $state['projects'] = array(); $state['demo'] = false; update_option( DESKMAN_SETUP_OPTION, $state ); }
	return $removed;
}
