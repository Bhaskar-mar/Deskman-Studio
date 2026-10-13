<?php
/**
 * Site content: the structured parts of the design (services, packages,
 * process, standards, FAQ). Defaults live here; the plugin can override the
 * list-style ones through simple textareas ("Question :: Answer" per line).
 *
 * Content rules: no invented clients, numbers or testimonials.
 *
 * @package Deskman_Studio
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Services, keyed by page slug (children of /services/). */
function dm_services() {
	$s = array(
		'custom-wordpress-themes' => array(
			'icon' => 'theme', 'title' => 'Custom WordPress themes', 'menu' => 'Designed for your brand, no page builder.',
			'short' => 'Designed for your brand and coded from scratch, with editable blocks.',
			'h1' => 'Custom WordPress themes,|{designed for your brand.}',
			'lead' => 'A theme designed and coded from scratch for your business — with editable blocks your team can use, no page builder and nothing you don\'t need.',
			'bullets' => array( 'Brand design system', 'Custom blocks & templates', 'Fast, accessible front end' ),
			'kv' => array( 'Built with' => 'WordPress block editor', 'Design' => 'Your brand system', 'Handover' => 'Training + written guide', 'After launch' => 'Optional care plan' ),
			'features' => array(
				array( 'palette', 'Brand design system', 'Colours, type, spacing and components defined once and used everywhere.' ),
				array( 'grid', 'Custom blocks', 'Every section of your site is a block your team can add, edit and reorder.' ),
				array( 'file', 'Page templates', 'Services, case studies, blog, landing pages and legal pages, ready to use.' ),
				array( 'gauge', 'Performance first', 'Lean code, optimised images and no page-builder overhead.' ),
				array( 'access', 'Accessible', 'Semantic markup, keyboard support and readable contrast throughout.' ),
				array( 'search', 'SEO foundations', 'Clean headings, schema, meta and sitemaps built into the theme.' ),
			),
			'faq' => array(
				array( 'Can I still use plugins?', 'Yes. We keep the plugins that genuinely help and build the rest into the theme or a custom plugin.' ),
				array( 'Will my team need to code?', 'No. Content lives in editable blocks; code is only touched when you want something new.' ),
				array( 'Can you redesign my existing site?', 'Yes. We can rebuild your current site on a custom theme and keep your content, URLs and rankings intact with redirects.' ),
				array( 'Who owns the theme?', 'You do. The theme is built for your site and handed over with the code and a written guide.' ),
			),
		),
		'custom-plugins' => array(
			'icon' => 'plug', 'title' => 'Custom plugins & integrations', 'menu' => 'Features built for how you work.',
			'short' => 'Features built for your workflow instead of five plugins stitched together.',
			'h1' => 'Custom plugins,|{built for how you work.}',
			'lead' => 'Bookings, quotes, memberships, CRM and payment links — built as one plugin you own, with one dashboard for your team, instead of a stack of subscriptions.',
			'bullets' => array( 'Bookings, quotes, memberships', 'CRM, payment & API links', 'One dashboard for your team' ),
			'kv' => array( 'Built with' => 'WordPress plugin APIs', 'Data' => 'Stored in your site', 'Admin' => 'One dashboard', 'Support' => 'Updates on request' ),
			'features' => array(
				array( 'cal', 'Bookings & appointments', 'Availability, confirmations and reminders that fit your service.' ),
				array( 'inbox', 'Leads & enquiries', 'Forms, spam filtering and a lead inbox inside WordPress.' ),
				array( 'card', 'Payments & invoices', 'Connect the payment provider you already use.' ),
				array( 'api', 'CRM & API sync', 'Send leads and orders to the tools your team works in.' ),
				array( 'users', 'Members & portals', 'Logged-in areas for customers, students or partners.' ),
				array( 'grid', 'Admin you can run', 'Clean screens in the WordPress admin, made for your team.' ),
			),
			'faq' => array(
				array( 'Why not just install an existing plugin?', 'Sometimes that is the right answer, and we will say so. Custom makes sense when several plugins overlap, conflict or charge per feature.' ),
				array( 'Will it survive WordPress updates?', 'Yes. We build on WordPress\'s standard APIs and test against updates; a care plan keeps it maintained.' ),
				array( 'Can it connect to our CRM?', 'Usually, yes — any tool with an API or webhooks can be connected.' ),
				array( 'Who owns the code?', 'You do. The plugin is delivered with its source.' ),
			),
		),
		'seo' => array(
			'icon' => 'search', 'title' => 'SEO & technical SEO', 'menu' => 'Search-ready structure, then growth.',
			'short' => 'Search-ready structure from day one, then content and local SEO.',
			'h1' => 'SEO built into the code,|{then grown with content.}',
			'lead' => 'Technical foundations first — speed, structure, schema and sitemaps — then content and local SEO that help the right people find you.',
			'bullets' => array( 'Technical SEO audit & fixes', 'Schema, sitemaps, meta', 'Local SEO & location pages' ),
			'kv' => array( 'Starts with' => 'Technical audit', 'Covers' => 'On-page & local SEO', 'Tracking' => 'Search Console & analytics', 'Ongoing' => 'Optional monthly SEO' ),
			'features' => array(
				array( 'search', 'Technical audit', 'Crawl, index and speed issues found and fixed.' ),
				array( 'code', 'Structured data', 'Organization, LocalBusiness, FAQ and breadcrumb schema.' ),
				array( 'pin', 'Local SEO', 'Location pages and profiles for the cities you serve.' ),
				array( 'doc', 'On-page SEO', 'Titles, headings and internal links that make sense to people and search engines.' ),
				array( 'gauge', 'Core Web Vitals', 'Speed treated as a ranking and conversion feature.' ),
				array( 'chart', 'Reporting', 'Clear monthly notes on what changed and why.' ),
			),
			'faq' => array(
				array( 'Is SEO included in every website?', 'Every site we build ships with technical SEO foundations. Ongoing content and local SEO is a separate service.' ),
				array( 'Can you guarantee rankings?', 'No honest agency can. We fix what is in our control and report clearly on progress.' ),
				array( 'Do you work on sites you did not build?', 'Yes. Most SEO projects start with an audit of an existing site.' ),
				array( 'How long does SEO take?', 'Technical fixes show quickly; content and local SEO build up over months.' ),
			),
		),
		'woocommerce' => array(
			'icon' => 'cart', 'title' => 'WooCommerce stores', 'menu' => 'Stores that are easy to run.',
			'short' => 'Online stores that are easy to run and quick to check out.',
			'h1' => 'WooCommerce stores|{that are easy to run.}',
			'lead' => 'Product pages that sell, a checkout that doesn\'t get in the way, and an admin your team can manage every day.',
			'bullets' => array( 'Product & category design', 'Payments & shipping setup', 'Order emails & analytics' ),
			'kv' => array( 'Platform' => 'WooCommerce', 'Design' => 'Custom, on-brand', 'Payments' => 'Your provider', 'After launch' => 'Optional care plan' ),
			'features' => array(
				array( 'img', 'Product pages', 'Clear images, variants and details that build trust.' ),
				array( 'grid', 'Categories & filters', 'Browsing that helps customers find the right product fast.' ),
				array( 'card', 'Checkout', 'A short, mobile-friendly checkout with your payment provider.' ),
				array( 'mail', 'Order emails', 'Branded order and shipping emails.' ),
				array( 'chart', 'Analytics', 'Ecommerce tracking set up from day one.' ),
				array( 'gauge', 'Speed', 'A fast store, even with a large catalogue.' ),
			),
			'faq' => array(
				array( 'Can you move my store to WooCommerce?', 'Yes — products, customers and orders can usually be migrated.' ),
				array( 'Which payment gateways do you support?', 'Any gateway with a WooCommerce integration, including the common Indian and international providers.' ),
				array( 'Can my team add products?', 'Yes. We train your team and keep the admin simple.' ),
				array( 'Do you handle shipping rules?', 'Yes, including zones, rates and free-shipping thresholds.' ),
			),
		),
		'speed-security' => array(
			'icon' => 'bolt', 'title' => 'Speed & security', 'menu' => 'Audits and fixes for existing sites.',
			'short' => 'Audits and fixes for slow or vulnerable WordPress sites.',
			'h1' => 'A faster, safer|{WordPress site.}',
			'lead' => 'We find what is slowing your site down or putting it at risk, fix it, and leave you with a clear report.',
			'bullets' => array( 'Performance audit', 'Caching & image optimisation', 'Hardening & malware cleanup' ),
			'kv' => array( 'Starts with' => 'Audit & report', 'Covers' => 'Speed + security', 'Delivered' => 'Fixes on staging first', 'After' => 'Optional care plan' ),
			'features' => array(
				array( 'gauge', 'Performance audit', 'What is slow, why, and what it costs you.' ),
				array( 'bolt', 'Caching & assets', 'Caching, minification and image optimisation.' ),
				array( 'layers', 'Plugin clean-up', 'Remove overlapping or heavy plugins safely.' ),
				array( 'shield', 'Hardening', 'Login protection, updates and safer settings.' ),
				array( 'lock', 'Malware cleanup', 'Clean an infected site and close the hole.' ),
				array( 'doc', 'Clear report', 'Before-and-after notes you can share with your team.' ),
			),
			'faq' => array(
				array( 'Will you change how my site looks?', 'No. Speed and security work happens behind the scenes, tested on staging first.' ),
				array( 'My site was hacked — can you help?', 'Yes. We clean it, find how it happened and harden it so it does not happen again.' ),
				array( 'Do I need a care plan afterwards?', 'It is recommended, so the site stays fast and secure, but it is optional.' ),
				array( 'Can you work with my host?', 'Yes, we work with your existing hosting.' ),
			),
		),
		'care-plans' => array(
			'icon' => 'cycle', 'title' => 'Care & maintenance', 'menu' => 'Updates, backups and improvements.',
			'short' => 'A monthly plan that keeps your site updated, backed up and improving.',
			'h1' => 'Care plans that keep|{your site in shape.}',
			'lead' => 'Updates, backups, monitoring and small changes every month — so your website keeps working while you run your business.',
			'bullets' => array( 'Updates & backups', 'Monitoring & fixes', 'Small changes each month' ),
			'kv' => array( 'Billing' => 'Monthly', 'Updates' => 'Tested first', 'Backups' => 'Off-site', 'Changes' => 'Included each month' ),
			'features' => array(
				array( 'cycle', 'Updates', 'WordPress core, theme and plugin updates, tested before they go live.' ),
				array( 'shield', 'Backups & security', 'Off-site backups, hardening and clean-up if anything goes wrong.' ),
				array( 'clock', 'Monitoring', 'Uptime and error monitoring, with fixes when needed.' ),
				array( 'gauge', 'Speed & SEO checks', 'Regular performance and search health checks.' ),
				array( 'pen', 'Small changes', 'Content edits and small improvements every month.' ),
				array( 'msg', 'Direct support', 'Talk to the people who know your site.' ),
			),
			'faq' => array(
				array( 'Do you look after sites you did not build?', 'Yes, after a short audit to understand how the site is put together.' ),
				array( 'What counts as a small change?', 'Content edits, new pages from existing blocks and minor tweaks. Bigger work is quoted separately.' ),
				array( 'Can I cancel?', 'Yes. Care plans are monthly.' ),
				array( 'What if something breaks?', 'We fix it — that is what the plan is for.' ),
			),
		),
	);
	return apply_filters( 'dm_services', $s );
}

/** Old service page slugs → the current service they now describe. */
function dm_service_alias( $slug ) {
	$map = array( 'web-design' => 'custom-wordpress-themes', 'wordpress-development' => 'custom-wordpress-themes', 'branding' => 'custom-wordpress-themes', 'digital-marketing' => 'seo', 'ecommerce' => 'woocommerce', 'conversion-growth' => 'seo', 'plugins' => 'custom-plugins', 'care' => 'care-plans' );
	return isset( $map[ $slug ] ) ? $map[ $slug ] : $slug;
}

function dm_service_url( $slug ) {
	$p = get_page_by_path( 'services/' . $slug );
	if ( $p && 'publish' === $p->post_status ) { return get_permalink( $p ); }
	return trailingslashit( dm_link( 'services' ) ) . '#' . $slug;
}

/** Package lists: two audiences (the segmented control on the packages page). */
function dm_packages() {
	return array(
		'new' => array(
			array( 'name' => dm_opt( 'v5_pk1_name', 'Landing page' ), 'icon' => 'file', 'scope' => dm_opt( 'v5_pk1_scope', '1 page' ), 'note' => 'Fixed quote after a short call', 'for' => dm_opt( 'v5_pk1_for', 'For a launch, campaign or single offer.' ), 'pop' => false,
				'items' => dm_lines( 'v5_pk1_items', array( 'Custom design, no template', 'Copy structured to convert', 'Analytics & on-page SEO', 'Fast on every device' ) ) ),
			array( 'name' => dm_opt( 'v5_pk2_name', 'Business website' ), 'icon' => 'theme', 'scope' => dm_opt( 'v5_pk2_scope', 'Multi-page site' ), 'note' => 'Fixed quote after a short call', 'for' => dm_opt( 'v5_pk2_for', 'A complete custom site that brings in enquiries.' ), 'pop' => true,
				'items' => dm_lines( 'v5_pk2_items', array( 'Custom theme & blocks', 'Enquiry forms & lead inbox', 'Technical & local SEO', 'Training at launch', 'Launch support' ) ) ),
			array( 'name' => dm_opt( 'v5_pk3_name', 'Platform & store' ), 'icon' => 'layers', 'scope' => dm_opt( 'v5_pk3_scope', 'Custom scope' ), 'note' => 'Scoped in a discovery call', 'for' => dm_opt( 'v5_pk3_for', 'Bookings, memberships, stores and integrations.' ), 'pop' => false,
				'items' => dm_lines( 'v5_pk3_items', array( 'Everything in Business', 'Custom plugins', 'Payments, CRM & APIs', 'Priority support' ) ) ),
		),
		'existing' => array(
			array( 'name' => 'Speed & security audit', 'icon' => 'bolt', 'scope' => 'One-off', 'note' => 'Fixed price after a quick look', 'for' => 'Find and fix what is slowing your site or putting it at risk.', 'pop' => false,
				'items' => array( 'Performance audit', 'Security hardening', 'Plugin clean-up', 'Written report' ) ),
			array( 'name' => 'Care plan', 'icon' => 'cycle', 'scope' => 'Monthly', 'note' => 'Cancel any time', 'for' => 'Updates, backups, monitoring and small changes every month.', 'pop' => true,
				'items' => array( 'Tested updates', 'Off-site backups', 'Monitoring & fixes', 'Small changes each month', 'Direct support' ) ),
			array( 'name' => 'Redesign', 'icon' => 'theme', 'scope' => 'Project', 'note' => 'Keeps your content & rankings', 'for' => 'Move your current site onto a fast custom theme.', 'pop' => false,
				'items' => array( 'New custom theme', 'Content & redirects kept', 'SEO preserved', 'Team training' ) ),
		),
	);
}

function dm_process() {
	return array(
		array( 'search', 'Discover', 'Goals, audience and what the site must do.', array( 'Kick-off call', 'Scope & sitemap', 'Fixed proposal' ) ),
		array( 'pen', 'Design', 'Wireframes, then the full visual design.', array( 'Wireframes', 'UI design', 'Design system' ) ),
		array( 'code', 'Develop', 'Custom theme and plugins on staging.', array( 'Theme & blocks', 'Plugins & integrations', 'Content entry' ) ),
		array( 'gauge', 'Optimise', 'Speed, SEO and accessibility checks.', array( 'Technical SEO', 'Performance pass', 'Device QA' ) ),
		array( 'rocket', 'Launch & care', 'Go live, train your team, keep improving.', array( 'Launch & redirects', 'Team training', 'Care plan' ) ),
	);
}

function dm_standards() {
	return array(
		array( 'gauge', 'Built for Core Web Vitals', 'Lean templates, optimised images and caching from day one.' ),
		array( 'access', 'Accessible by default', 'Semantic HTML, keyboard support and readable contrast.' ),
		array( 'shield', 'Secure & maintained', 'Hardened setup, backups and a clear update routine.' ),
		array( 'search', 'Search-ready structure', 'Schema, meta, sitemaps and clean URLs built in.' ),
		array( 'theme', 'Editable without breaking', 'Your team edits blocks, not code — the design holds.' ),
		array( 'inbox', 'Leads you never miss', 'Forms, spam filtering and a lead inbox inside WordPress.' ),
		array( 'globe', 'Local SEO pages', 'Location pages that help you rank in the cities you serve.' ),
		array( 'doc', 'Documented handover', 'Training and a guide so your team owns the site.' ),
	);
}

function dm_compare() {
	return array(
		array( 'Design', 'Bought theme, adjusted to fit', 'Designed for your brand' ),
		array( 'Features', 'One plugin per feature', 'Built for your workflow' ),
		array( 'Editing', 'Page builder & many dashboards', 'Native blocks, one dashboard' ),
		array( 'SEO', 'Added with another plugin', 'Built into the code' ),
		array( 'Speed', 'Heavy pages, slow on mobile', 'Lean code, fast everywhere' ),
		array( 'Support', "You're on your own after launch", 'Care plan & ongoing support' ),
	);
}

function dm_principles() {
	return array(
		array( 'Design and build in one place', 'The person who scopes your project designs and builds it. No handoffs, no drift between the drawing and the live site.' ),
		array( 'Fast without cutting the craft', 'Hand-written front end, real performance budgets and accessibility from the first commit.' ),
		array( 'Yours to run afterwards', 'An editing layer your team can actually use, plus documentation and aftercare.' ),
		array( 'Honest scoping', 'We recommend what you need — and tell you when an existing plugin is the right answer.' ),
	);
}

/** Home FAQ. Plugin override: one "Question :: Answer" per line. */
function dm_faq() {
	$default = array(
		array( 'Why custom WordPress instead of a page builder?', 'Custom themes load faster, are easier to edit and don\'t break when a builder updates. You own clean code instead of renting a layout.' ),
		array( 'Can my team update the site themselves?', 'Yes. Every section is an editable block, and we train your team at launch with a short written guide.' ),
		array( 'Do you work on existing WordPress sites?', 'Yes — speed and security audits, redesigns, SEO and monthly care plans for sites we did not build.' ),
		array( 'Is SEO included in every website?', 'Every site ships with technical SEO foundations: schema, meta, sitemaps and clean structure. Ongoing content and local SEO is a separate service.' ),
		array( 'What happens after launch?', 'We train your team and support the launch. A care plan keeps the site updated, backed up and improving every month.' ),
		array( 'How do you price a project?', 'After a short call we send a written scope with a fixed price, so there are no hourly surprises.' ),
	);
	$raw = trim( (string) deskman_opt( 'v5_faq', '' ) );
	if ( $raw === '' ) { return $default; }
	$out = array();
	foreach ( preg_split( '/\r\n|\r|\n/', $raw ) as $line ) {
		$bits = array_map( 'trim', explode( '::', $line, 2 ) );
		if ( count( $bits ) === 2 && $bits[0] !== '' && $bits[1] !== '' ) { $out[] = $bits; }
	}
	return $out ? $out : $default;
}

/** Project-type choices used by every enquiry form. */
function dm_ptypes() {
	return dm_lines( 'v5_ptypes', array( 'New website', 'Custom plugin', 'SEO', 'WooCommerce', 'Redesign', 'Care plan' ) );
}
