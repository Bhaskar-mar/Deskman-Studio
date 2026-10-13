<?php
/**
 * Plugin Name:       Deskman Studio Manager
 * Description:       The complete back end for the Deskman Studio theme (v5 design): edit every piece of front-end text, links, colours and project images from one screen, run the on-site chatbot, and generate location landing pages ("best digital marketing agency in Visakhapatnam") that rank without ever appearing in your navigation. The theme reads this content; if this plugin is off, the theme falls back to identical built-in defaults.
 * Version:           7.1.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Deskman Studio
 * License:           GPL-2.0-or-later
 * Text Domain:       deskman-studio-manager
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'DESKMAN_OPTION', 'deskman_settings' );
define( 'DESKMAN_GROUP',  'deskman_settings_group' );
define( 'DESKMAN_VER',    '7.1.0' );
define( 'DESKMAN_DIR',    plugin_dir_path( __FILE__ ) );
define( 'DESKMAN_URL',    plugin_dir_url( __FILE__ ) );

/* Modules: portfolio types, setup wizard, and the studio admin experience.
   Loaded after the constants above so every module can rely on them. */
require_once DESKMAN_DIR . 'includes/cpt.php';
require_once DESKMAN_DIR . 'includes/setup.php';
require_once DESKMAN_DIR . 'includes/admin.php';
require_once DESKMAN_DIR . 'includes/seo-meta.php';
require_once DESKMAN_DIR . 'includes/leads.php';

/* Area pages — the location landing pages and the generator that writes them. */
require_once DESKMAN_DIR . 'includes/areas.php';
require_once DESKMAN_DIR . 'includes/area-urls.php';
require_once DESKMAN_DIR . 'includes/areas-generator.php';

/* Per-city page designs. Loaded after areas.php because it uses its CPT
   constant and field helpers, and it re-routes the single template. */
require_once DESKMAN_DIR . 'includes/area-designs.php';

/* Site templates — the /templates/ gallery, the live customiser and the
   industry website templates. Enquiries go through leads.php into Forms. */
require_once DESKMAN_DIR . 'includes/site-templates.php';

/* The chatbot, formerly the standalone "Deskman Chatbot" plugin.
   -------------------------------------------------------------------------
   If that plugin is still installed and active, its file has already defined
   the same functions this module does, and loading ours would fatal on a
   redeclare. So the module is skipped and an admin notice explains what to do.
   Settings, leads and REST routes all carry over untouched, so deactivating
   the old plugin loses nothing — it is genuinely just a deactivate. */
if ( defined( 'DESKMAN_CB_OPTION' ) || function_exists( 'deskman_cb_defaults' ) ) {
	add_action( 'admin_notices', function () {
		if ( ! current_user_can( 'activate_plugins' ) ) { return; }
		echo '<div class="notice notice-warning"><p><strong>' . esc_html__( 'Deskman Studio Manager:', 'deskman-studio-manager' ) . '</strong> ';
		echo esc_html__( 'the old "Deskman Chatbot" plugin is still active, so the built-in chatbot is switched off to avoid a conflict. Deactivate that plugin — your chatbot settings, knowledge base and saved leads all carry over automatically, and the chat will keep working from here.', 'deskman-studio-manager' );
		echo ' <a href="' . esc_url( admin_url( 'plugins.php' ) ) . '">' . esc_html__( 'Go to Plugins', 'deskman-studio-manager' ) . '</a></p></div>';
	} );
} else {
	require_once DESKMAN_DIR . 'includes/chatbot.php';
}


/** Choices for the hero arc card selects. Keys are what the theme reads. */
function deskman_arc_kinds() {
	return array(
		'browser' => 'Browser chrome',
		'bars'    => 'Title + bars',
		'type'    => 'Big type',
		'metric'  => 'Counting number',
		'slide'   => 'Sliding progress bar',
		'grid'    => 'Column grid',
		'chart'   => 'Bar chart',
	);
}
function deskman_arc_tints() {
	return array(
		'paper'    => 'Paper (white)',
		'electric' => 'Electric green',
		'violet'   => 'Violet',
		'ink'      => 'Ink (dark)',
	);
}

/** The field schema — the single source of truth (shared with the theme's defaults). */
function deskman_schema() {
	return array(
		/* Site content for the v5 theme ("Site · …"), then SEO, leads and testimonials. */
		'v5_bar_on' => array( 'label'=>'Show the announcement bar', 'type'=>'checkbox', 'section'=>'Site · Header', 'default'=>'1' ),
		'v5_bar_b' => array( 'label'=>'Announcement · bold part', 'type'=>'text', 'section'=>'Site · Header', 'default'=>'Now booking projects' ),
		'v5_bar_t' => array( 'label'=>'Announcement · text', 'type'=>'text', 'section'=>'Site · Header', 'default'=>'Custom WordPress themes, plugins & SEO' ),
		'v5_bar_cta' => array( 'label'=>'Announcement · link label', 'type'=>'text', 'section'=>'Site · Header', 'default'=>'Start a project' ),
		'v5_nav_cta' => array( 'label'=>'Nav button label', 'type'=>'text', 'section'=>'Site · Header', 'default'=>'Book a call' ),
		'v5_call_url' => array( 'label'=>'Booking link (Calendly etc.)', 'type'=>'url', 'section'=>'Site · Header', 'default'=>'', 'help'=>'Where every "Book a call" button goes. Leave blank to send people to the contact page.' ),
		'v5_annc_b' => array( 'label'=>'Hero pill · badge', 'type'=>'text', 'section'=>'Site · Home', 'default'=>'New' ),
		'v5_annc_t' => array( 'label'=>'Hero pill · text', 'type'=>'text', 'section'=>'Site · Home', 'default'=>'Care plans for existing WordPress sites' ),
		'v5_annc_url' => array( 'label'=>'Hero pill · link', 'type'=>'url', 'section'=>'Site · Home', 'default'=>'', 'help'=>'Leave blank to link to the Care plans service page.' ),
		'v5_hero_h' => array( 'label'=>'Hero headline', 'type'=>'text', 'section'=>'Site · Home', 'default'=>'Custom WordPress websites|{built to rank and convert.}', 'help'=>'In headlines, a | starts a new line and {curly braces} mark the coloured accent.' ),
		'v5_hero_lead' => array( 'label'=>'Hero description', 'type'=>'textarea', 'section'=>'Site · Home', 'default'=>'Deskman Studio designs and engineers custom themes, purpose-built plugins and technical SEO — one system your team can run, without page builders or plugin piles.', 'help'=>'Also used as the default meta description.' ),
		'v5_hero_cta1' => array( 'label'=>'Hero primary button', 'type'=>'text', 'section'=>'Site · Home', 'default'=>'Start a project' ),
		'v5_hero_cta2' => array( 'label'=>'Hero secondary button', 'type'=>'text', 'section'=>'Site · Home', 'default'=>'See our work' ),
		'v5_hero_meta' => array( 'label'=>'Hero checklist (one per line)', 'type'=>'textarea', 'section'=>'Site · Home', 'default'=>'Custom theme
Custom plugins
Technical SEO
Care after launch' ),
		'v5_tabs_h' => array( 'label'=>'Services tabs · headline', 'type'=>'text', 'section'=>'Site · Home', 'default'=>'Everything your website runs on, {designed to fit together.}', 'help'=>'In headlines, a | starts a new line and {curly braces} mark the coloured accent.' ),
		'v5_tabs_lead' => array( 'label'=>'Services tabs · text', 'type'=>'textarea', 'section'=>'Site · Home', 'default'=>'Theme, plugins, SEO and care are built by the same team, so your site works like one product instead of twenty parts.' ),
		'v5_cmp_h' => array( 'label'=>'Comparison · headline', 'type'=>'text', 'section'=>'Site · Home', 'default'=>'One system — {not a pile of plugins.}', 'help'=>'In headlines, a | starts a new line and {curly braces} mark the coloured accent.' ),
		'v5_cmp_lead' => array( 'label'=>'Comparison · text', 'type'=>'textarea', 'section'=>'Site · Home', 'default'=>'Most sites are a bought theme, a page builder and twenty plugins that never quite agree. Here\'s the difference.' ),
		'v5_work_h' => array( 'label'=>'Work · headline', 'type'=>'text', 'section'=>'Site · Home', 'default'=>'Built for real businesses, {live today.}', 'help'=>'Projects come from Deskman → Projects (featured first).' ),
		'v5_proc_h' => array( 'label'=>'Process · headline', 'type'=>'text', 'section'=>'Site · Home', 'default'=>'A clear path from first call {to launch — and beyond.}' ),
		'v5_proc_lead' => array( 'label'=>'Process · text', 'type'=>'textarea', 'section'=>'Site · Home', 'default'=>'One point of contact, a staging site you can click through, and weekly updates at every step.' ),
		'v5_pk_h' => array( 'label'=>'Packages · headline', 'type'=>'text', 'section'=>'Site · Home', 'default'=>'Pick a starting point. {We\'ll shape the rest.}' ),
		'v5_pk_lead' => array( 'label'=>'Packages · text', 'type'=>'textarea', 'section'=>'Site · Home', 'default'=>'Every project is scoped to what you need, with a clear fixed quote after a short call.' ),
		'v5_svc_h' => array( 'label'=>'Services page · headline', 'type'=>'text', 'section'=>'Site · Pages', 'default'=>'Everything your website needs, {from one team.}', 'help'=>'In headlines, a | starts a new line and {curly braces} mark the coloured accent.' ),
		'v5_svc_lead' => array( 'label'=>'Services page · text', 'type'=>'textarea', 'section'=>'Site · Pages', 'default'=>'Design, custom development, SEO and care — scoped together, so your theme, plugins and search visibility are built to work as one system.' ),
		'v5_also' => array( 'label'=>'Services page · "Also from the studio" (one per line)', 'type'=>'textarea', 'section'=>'Site · Pages', 'default'=>'UI/UX design
Brand identity
Landing pages
Website redesigns
Migrations to WordPress
Content & copywriting' ),
		'v5_work_page_h' => array( 'label'=>'Work page · headline', 'type'=>'text', 'section'=>'Site · Pages', 'default'=>'Websites and platforms, {live and working.}' ),
		'v5_work_page_lead' => array( 'label'=>'Work page · text', 'type'=>'textarea', 'section'=>'Site · Pages', 'default'=>'A selection of projects we\'ve designed and built — from WooCommerce stores to school websites and city-wide platforms.' ),
		'v5_pkp_h' => array( 'label'=>'Packages page · headline', 'type'=>'text', 'section'=>'Site · Pages', 'default'=>'Clear packages. {Honest, fixed quotes.}' ),
		'v5_pkp_lead' => array( 'label'=>'Packages page · text', 'type'=>'textarea', 'section'=>'Site · Pages', 'default'=>'Every project is scoped to what you actually need. Choose a starting point, book a short call, and get a fixed quote — no surprises later.' ),
		'v5_about_h' => array( 'label'=>'About page · headline', 'type'=>'text', 'section'=>'Site · Pages', 'default'=>'A studio small enough to care, {senior enough to ship.}' ),
		'v5_about_lead' => array( 'label'=>'About page · text', 'type'=>'textarea', 'section'=>'Site · Pages', 'default'=>'Deskman is an independent web studio in Visakhapatnam. We design, build and look after custom WordPress websites — so what goes live is exactly what was drawn.' ),
		'v5_toolkit' => array( 'label'=>'About page · toolkit (one per line)', 'type'=>'textarea', 'section'=>'Site · Pages', 'default'=>'WordPress
Block editor
PHP
JavaScript
WooCommerce
REST APIs
Figma
Schema.org
Search Console
Analytics' ),
		'v5_contact_h' => array( 'label'=>'Contact page · headline', 'type'=>'text', 'section'=>'Site · Pages', 'default'=>'Let\'s talk about {your website.}' ),
		'v5_contact_lead' => array( 'label'=>'Contact page · text', 'type'=>'textarea', 'section'=>'Site · Pages', 'default'=>'Tell us what you\'re planning. You\'ll get a reply within one working day — from the person who will actually work on it.' ),
		'v5_tpl_h' => array( 'label'=>'Templates page · headline', 'type'=>'text', 'section'=>'Site · Pages', 'default'=>'Website templates, {made for your industry.}', 'help'=>'In headlines, a | starts a new line and {curly braces} mark the coloured accent.' ),
		'v5_tpl_lead' => array( 'label'=>'Templates page · text', 'type'=>'textarea', 'section'=>'Site · Pages', 'default'=>'Pick a design built for your kind of business, add your name, logo and colours, and preview it live on mobile, tablet, laptop and desktop. When it feels right, we turn it into your finished website.' ),
		'v5_blog_h' => array( 'label'=>'Insights · headline', 'type'=>'text', 'section'=>'Site · Pages', 'default'=>'Notes on building {better WordPress sites.}' ),
		'v5_blog_lead' => array( 'label'=>'Insights · text', 'type'=>'textarea', 'section'=>'Site · Pages', 'default'=>'Practical guides on WordPress, SEO, performance and running a website that keeps working for your business.' ),
		'v5_pk1_name' => array( 'label'=>'Package 1 · name', 'type'=>'text', 'section'=>'Site · Packages', 'default'=>'Landing page', 'help'=>'Package 2 is the highlighted one. No prices are shown anywhere; every button opens the contact form with the package pre-selected.' ),
		'v5_pk1_scope' => array( 'label'=>'Package 1 · scope line', 'type'=>'text', 'section'=>'Site · Packages', 'default'=>'1 page' ),
		'v5_pk1_for' => array( 'label'=>'Package 1 · who it is for', 'type'=>'text', 'section'=>'Site · Packages', 'default'=>'For a launch, campaign or single offer.' ),
		'v5_pk1_items' => array( 'label'=>'Package 1 · included (one per line)', 'type'=>'textarea', 'section'=>'Site · Packages', 'default'=>'Custom design, no template
Copy structured to convert
Analytics & on-page SEO
Fast on every device' ),
		'v5_pk2_name' => array( 'label'=>'Package 2 · name', 'type'=>'text', 'section'=>'Site · Packages', 'default'=>'Business website' ),
		'v5_pk2_scope' => array( 'label'=>'Package 2 · scope line', 'type'=>'text', 'section'=>'Site · Packages', 'default'=>'Multi-page site' ),
		'v5_pk2_for' => array( 'label'=>'Package 2 · who it is for', 'type'=>'text', 'section'=>'Site · Packages', 'default'=>'A complete custom site that brings in enquiries.' ),
		'v5_pk2_items' => array( 'label'=>'Package 2 · included (one per line)', 'type'=>'textarea', 'section'=>'Site · Packages', 'default'=>'Custom theme & blocks
Enquiry forms & lead inbox
Technical & local SEO
Training at launch
Launch support' ),
		'v5_pk3_name' => array( 'label'=>'Package 3 · name', 'type'=>'text', 'section'=>'Site · Packages', 'default'=>'Platform & store' ),
		'v5_pk3_scope' => array( 'label'=>'Package 3 · scope line', 'type'=>'text', 'section'=>'Site · Packages', 'default'=>'Custom scope' ),
		'v5_pk3_for' => array( 'label'=>'Package 3 · who it is for', 'type'=>'text', 'section'=>'Site · Packages', 'default'=>'Bookings, memberships, stores and integrations.' ),
		'v5_pk3_items' => array( 'label'=>'Package 3 · included (one per line)', 'type'=>'textarea', 'section'=>'Site · Packages', 'default'=>'Everything in Business
Custom plugins
Payments, CRM & APIs
Priority support' ),
		'v5_faq' => array( 'label'=>'Home & packages FAQ (one per line: Question :: Answer)', 'type'=>'textarea', 'section'=>'Site · FAQ & forms', 'default'=>'', 'help'=>'Leave blank to use the built-in six questions. Also published as FAQ structured data for search engines.' ),
		'v5_ptypes' => array( 'label'=>'Enquiry form · project types (one per line)', 'type'=>'textarea', 'section'=>'Site · FAQ & forms', 'default'=>'New website
Custom plugin
SEO
WooCommerce
Redesign
Care plan' ),
		'v5_form_h' => array( 'label'=>'Contact form · title', 'type'=>'text', 'section'=>'Site · FAQ & forms', 'default'=>'Project enquiry' ),
		'v5_form_fine' => array( 'label'=>'Form · small print', 'type'=>'text', 'section'=>'Site · FAQ & forms', 'default'=>'We only use your details to reply. No spam, ever.' ),
		'v5_cta_label' => array( 'label'=>'CTA block · label', 'type'=>'text', 'section'=>'Site · FAQ & forms', 'default'=>'Start a project' ),
		'v5_cta_h' => array( 'label'=>'CTA block · headline', 'type'=>'text', 'section'=>'Site · FAQ & forms', 'default'=>'Let\'s build a website that {works as hard as you do.}', 'help'=>'In headlines, a | starts a new line and {curly braces} mark the coloured accent.' ),
		'v5_cta_lead' => array( 'label'=>'CTA block · text', 'type'=>'textarea', 'section'=>'Site · FAQ & forms', 'default'=>'Tell us what you need. You\'ll hear back within one working day with honest advice on the best approach — and a clear, fixed quote.' ),
		'v5_cta_btn' => array( 'label'=>'CTA block · call button', 'type'=>'text', 'section'=>'Site · FAQ & forms', 'default'=>'Book a 20-min call' ),
		'brand_name' => array( 'label'=>'Brand name', 'type'=>'text', 'section'=>'Site · Brand & contact', 'default'=>'Deskman' ),
		'brand_sub' => array( 'label'=>'Brand suffix', 'type'=>'text', 'section'=>'Site · Brand & contact', 'default'=>'Studio' ),
		'contact_email' => array( 'label'=>'Email address', 'type'=>'text', 'section'=>'Site · Brand & contact', 'default'=>'hi@deskmanstudio.com' ),
		'contact_location' => array( 'label'=>'Location', 'type'=>'text', 'section'=>'Site · Brand & contact', 'default'=>'Visakhapatnam, India' ),
		'form_success' => array( 'label'=>'Form · success message', 'type'=>'text', 'section'=>'Site · Brand & contact', 'default'=>'Thanks — your message is on its way. We’ll be in touch shortly.' ),
		'v5_foot_blurb' => array( 'label'=>'Footer · description', 'type'=>'textarea', 'section'=>'Site · Footer & social', 'default'=>'Custom WordPress themes, plugins and SEO for growing businesses. Based in Visakhapatnam, working worldwide.' ),
		'v5_status' => array( 'label'=>'Footer · status pill', 'type'=>'text', 'section'=>'Site · Footer & social', 'default'=>'Accepting new projects' ),
		'v5_copy' => array( 'label'=>'Footer · copyright line', 'type'=>'text', 'section'=>'Site · Footer & social', 'default'=>'', 'help'=>'Leave blank for "© year Deskman Studio. All rights reserved."' ),
		'social_yt' => array( 'label'=>'YouTube URL', 'type'=>'url', 'section'=>'Site · Footer & social', 'default'=>'' ),
		'social_ig' => array( 'label'=>'Instagram URL', 'type'=>'url', 'section'=>'Site · Footer & social', 'default'=>'#' ),
		'social_in' => array( 'label'=>'LinkedIn URL', 'type'=>'url', 'section'=>'Site · Footer & social', 'default'=>'#' ),
		'social_x' => array( 'label'=>'X / Twitter URL', 'type'=>'url', 'section'=>'Site · Footer & social', 'default'=>'#' ),
		'seo_desc' => array( 'label'=>'Default meta description', 'type'=>'textarea', 'section'=>'SEO & Business', 'default'=>'Deskman Studio is an independent web design, WordPress and branding studio in Visakhapatnam. We design, build and launch fast, accessible websites end to end.', 'help'=>'Used as the site-wide search-result snippet and social description when a page has no description of its own. Aim for 140–160 characters.' ),
		'seo_home_tagline' => array( 'label'=>'Home title tagline', 'type'=>'text', 'section'=>'SEO & Business', 'default'=>'Web Design, WordPress & Branding Studio in Visakhapatnam', 'help'=>'Appended after the brand name in the homepage <title> and browser tab.' ),
		'seo_image' => array( 'label'=>'Social share image', 'type'=>'image', 'section'=>'SEO & Business', 'default'=>'', 'help'=>'Shown when the site is shared on WhatsApp, LinkedIn, X, Facebook, etc. Use a 1200×630 image. Falls back to the logo, then the theme screenshot.' ),
		'seo_logo' => array( 'label'=>'Logo (for search results)', 'type'=>'image', 'section'=>'SEO & Business', 'default'=>'', 'help'=>'A square or wide logo on a solid background. Used in the Organization structured data Google reads for the knowledge panel.' ),
		'seo_twitter' => array( 'label'=>'X / Twitter handle', 'type'=>'text', 'section'=>'SEO & Business', 'default'=>'', 'help'=>'Without the @ — e.g. deskmanstudio. Used for Twitter card attribution.' ),
		'seo_phone' => array( 'label'=>'Business phone', 'type'=>'text', 'section'=>'SEO & Business', 'default'=>'', 'help'=>'Full international format, e.g. +91 89191 23456. Used in local-business structured data.' ),
		'seo_street' => array( 'label'=>'Street address', 'type'=>'text', 'section'=>'SEO & Business', 'default'=>'' ),
		'seo_locality' => array( 'label'=>'City / locality', 'type'=>'text', 'section'=>'SEO & Business', 'default'=>'Visakhapatnam' ),
		'seo_region' => array( 'label'=>'State / region', 'type'=>'text', 'section'=>'SEO & Business', 'default'=>'Andhra Pradesh' ),
		'seo_postal' => array( 'label'=>'Postal code', 'type'=>'text', 'section'=>'SEO & Business', 'default'=>'' ),
		'seo_country' => array( 'label'=>'Country code', 'type'=>'text', 'section'=>'SEO & Business', 'default'=>'IN', 'help'=>'Two-letter ISO code, e.g. IN.' ),
		'seo_lat' => array( 'label'=>'Map latitude', 'type'=>'text', 'section'=>'SEO & Business', 'default'=>'', 'help'=>'Optional. Decimal, e.g. 17.6868. Add with longitude to appear on the map for local searches.' ),
		'seo_lng' => array( 'label'=>'Map longitude', 'type'=>'text', 'section'=>'SEO & Business', 'default'=>'', 'help'=>'Optional. Decimal, e.g. 83.2185.' ),
		'seo_price_range' => array( 'label'=>'Price range', 'type'=>'text', 'section'=>'SEO & Business', 'default'=>'₹₹', 'help'=>'A rough indicator shown in local results, e.g. ₹₹ or $$.' ),
		'seo_founded' => array( 'label'=>'Founded (year)', 'type'=>'text', 'section'=>'SEO & Business', 'default'=>'', 'help'=>'Optional, e.g. 2021.' ),
		'lead_phone_required' => array( 'label'=>'Require a phone number', 'type'=>'checkbox', 'section'=>'Leads & spam filtering', 'default'=>'1', 'help'=>'On: the form will not submit without one. Off: the field still appears, marked optional, and is still validated when filled in.' ),
		'lead_ptype_required' => array( 'label'=>'Require a project type', 'type'=>'checkbox', 'section'=>'Leads & spam filtering', 'default'=>'1' ),
		'lead_block_disposable' => array( 'label'=>'Block throwaway email addresses', 'type'=>'checkbox', 'section'=>'Leads & spam filtering', 'default'=>'1', 'help'=>'Rejects Mailinator, YOPmail, 10MinuteMail and similar, so every lead has an address you can actually reply to.' ),
		'lead_check_mx' => array( 'label'=>'Check the email domain exists', 'type'=>'checkbox', 'section'=>'Leads & spam filtering', 'default'=>'1', 'help'=>'Looks up the domain’s mail records before accepting. Catches invented and dead domains. If the lookup itself fails the address is accepted — a DNS blip never costs you a lead.' ),
		'lead_block_cyrillic' => array( 'label'=>'Flag messages in Cyrillic script', 'type'=>'checkbox', 'section'=>'Leads & spam filtering', 'default'=>'0', 'help'=>'Off by default. Only turn this on if you never expect enquiries in Russian or Ukrainian.' ),
		'lead_spam_threshold' => array( 'label'=>'Spam score threshold', 'type'=>'text', 'section'=>'Leads & spam filtering', 'default'=>'5', 'help'=>'A message scoring this or higher is quarantined instead of emailed. 5 is a balanced default; 4 is stricter, 7 is lenient. Every quarantined message lists the score it got and why.' ),
		'lead_min_seconds' => array( 'label'=>'Minimum seconds to fill the form', 'type'=>'text', 'section'=>'Leads & spam filtering', 'default'=>'4', 'help'=>'Anything submitted faster than this is scored as automated. Nobody types a name, email, phone and project brief in under four seconds.' ),
		'lead_rate_limit' => array( 'label'=>'Maximum submissions per hour, per IP', 'type'=>'text', 'section'=>'Leads & spam filtering', 'default'=>'5', 'help'=>'Stops a script hammering the form. Keep this generous — a whole office can share one address.' ),
		'lead_block_domains' => array( 'label'=>'Additional blocked email domains', 'type'=>'textarea', 'section'=>'Leads & spam filtering', 'default'=>'', 'help'=>'One per line, or comma separated, e.g. example.com. Added to the built-in throwaway list. Subdomains are covered automatically.' ),
		'lead_block_words' => array( 'label'=>'Additional blocked phrases', 'type'=>'textarea', 'section'=>'Leads & spam filtering', 'default'=>'', 'help'=>'One per line. Added to the built-in list of sales-pitch phrases. Each match adds to the spam score rather than rejecting outright, so a phrase a real client might use will not lose you the lead on its own.' ),
		'testi_eyebrow' => array( 'label'=>'Eyebrow', 'type'=>'text', 'section'=>'Testimonials', 'default'=>'In their words' ),
		'testi1_quote' => array( 'label'=>'Testimonial 1 · quote', 'type'=>'textarea', 'section'=>'Testimonials', 'default'=>'They took a vague brief and gave us a site we actually understood' ),
		'testi1_name' => array( 'label'=>'Testimonial 1 · name', 'type'=>'text', 'section'=>'Testimonials', 'default'=>'Placeholder Client' ),
		'testi1_role' => array( 'label'=>'Testimonial 1 · role', 'type'=>'text', 'section'=>'Testimonials', 'default'=>'FOUNDER, SAMPLE CO.' ),
		'testi2_quote' => array( 'label'=>'Testimonial 2 · quote', 'type'=>'textarea', 'section'=>'Testimonials', 'default'=>'One person, start to finish. No handoffs, no telephone game — the work just kept moving. [Replace with a real client quote before launch.]' ),
		'testi2_name' => array( 'label'=>'Testimonial 2 · name', 'type'=>'text', 'section'=>'Testimonials', 'default'=>'Placeholder Client' ),
		'testi2_role' => array( 'label'=>'Testimonial 2 · role', 'type'=>'text', 'section'=>'Testimonials', 'default'=>'Director, Sample Ltd.' ),
		'testi3_quote' => array( 'label'=>'Testimonial 3 · quote', 'type'=>'textarea', 'section'=>'Testimonials', 'default'=>'Fast, calm and genuinely design-led. The site does the selling now. [Replace with a real client quote before launch.]' ),
		'testi3_name' => array( 'label'=>'Testimonial 3 · name', 'type'=>'text', 'section'=>'Testimonials', 'default'=>'Placeholder Client' ),
		'testi3_role' => array( 'label'=>'Testimonial 3 · role', 'type'=>'text', 'section'=>'Testimonials', 'default'=>'Owner, Sample Studio' ),
		'testi4_quote' => array( 'label'=>'Testimonial 4 · quote', 'type'=>'textarea', 'section'=>'Testimonials', 'default'=>'' ),
		'testi4_name' => array( 'label'=>'Testimonial 4 · name', 'type'=>'text', 'section'=>'Testimonials', 'default'=>'' ),
		'testi4_role' => array( 'label'=>'Testimonial 4 · role', 'type'=>'text', 'section'=>'Testimonials', 'default'=>'' ),
		'testi5_quote' => array( 'label'=>'Testimonial 5 · quote', 'type'=>'textarea', 'section'=>'Testimonials', 'default'=>'' ),
		'testi5_name' => array( 'label'=>'Testimonial 5 · name', 'type'=>'text', 'section'=>'Testimonials', 'default'=>'' ),
		'testi5_role' => array( 'label'=>'Testimonial 5 · role', 'type'=>'text', 'section'=>'Testimonials', 'default'=>'' ),
		'testi6_quote' => array( 'label'=>'Testimonial 6 · quote', 'type'=>'textarea', 'section'=>'Testimonials', 'default'=>'' ),
		'testi6_name' => array( 'label'=>'Testimonial 6 · name', 'type'=>'text', 'section'=>'Testimonials', 'default'=>'' ),
		'testi6_role' => array( 'label'=>'Testimonial 6 · role', 'type'=>'text', 'section'=>'Testimonials', 'default'=>'' ),
	);
}

/** Defaults derived from the schema. */
function deskman_defaults() {
	$out = array();
	foreach ( deskman_schema() as $k => $f ) { $out[ $k ] = $f['default']; }
	return $out;
}

/**
 * Master accessor used by the theme.
 * Returns the stored value, or the supplied default, or the schema default.
 * Empty strings fall back to a default (so blanks show sensible content); the
 * literal "0" (unchecked toggles) is preserved.
 */
if ( ! function_exists( 'deskman_opt' ) ) {
	function deskman_opt( $key, $default = null ) {
		$o = get_option( DESKMAN_OPTION );
		if ( ! is_array( $o ) ) { $o = array(); }
		if ( array_key_exists( $key, $o ) && $o[ $key ] !== '' ) { return $o[ $key ]; }
		if ( $default !== null ) { return $default; }
		$d = deskman_defaults();
		return isset( $d[ $key ] ) ? $d[ $key ] : '';
	}
}

/** Escaping helpers for templates (defined here; the theme also has fallbacks). */
if ( ! function_exists( 'dsk_e' ) ) {
	function dsk_e( $k, $d = '' ) { echo esc_html( deskman_opt( $k, $d ) ); }
	function dsk_a( $k, $d = '' ) { echo esc_attr( deskman_opt( $k, $d ) ); }
	function dsk_u( $k, $d = '' ) { echo esc_url( deskman_opt( $k, $d ) ); }
}

/** Smart page URL resolver (falls back gracefully). */
if ( ! function_exists( 'deskman_url' ) ) {
	function deskman_url( $slug ) {
		$slug = trim( (string) $slug, '/' );
		if ( $slug === '' ) { return home_url( '/' ); }
		$p = get_page_by_path( $slug );
		if ( $p ) { return get_permalink( $p->ID ); }
		return home_url( '/' . $slug . '/' );
	}
}

/** On activation: create the required pages, set the homepage, remove the Sample Page. */
function deskman_activate() {
	$pages = array(
		'home'     => 'Home',
		'work'     => 'Work',
		'services' => 'Services',
		'studio'   => 'Studio',
		'pricing'  => 'Pricing',
		'contact'  => 'Contact',
	);
	$ids = array();
	foreach ( $pages as $slug => $title ) {
		$existing = get_page_by_path( $slug );
		if ( $existing ) { $ids[ $slug ] = (int) $existing->ID; continue; }
		$new = wp_insert_post( array(
			'post_title'   => $title,
			'post_name'    => $slug,
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => '',
		) );
		if ( ! is_wp_error( $new ) ) { $ids[ $slug ] = (int) $new; }
	}
	if ( ! empty( $ids['home'] ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $ids['home'] );
	}
	// Remove WordPress's default "Sample Page" if it is still the untouched default.
	$sample = get_page_by_path( 'sample-page' );
	if ( $sample ) { wp_trash_post( $sample->ID ); }
	update_option( 'deskman_pages', $ids );

	/* Create a Page for each service (child of "Services"), assigned to the
	   theme's "Service — single" template, so each service has its own page
	   with its own content instead of every link landing on the overview. */
	deskman_ensure_service_pages();
	update_option( 'deskman_service_pages_v1', 1 );

	set_transient( 'deskman_activation_redirect', 1, 60 );

	/* Register the area post type before flushing, so its rewrite rules exist
	   in the rules being rebuilt. Without this the first area page you publish
	   404s until permalinks are saved by hand — the classic new-CPT trap. */
	if ( function_exists( 'deskman_area_register_cpt' ) ) { deskman_area_register_cpt(); }
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'deskman_activate' );

/**
 * Ensure one Page exists per service, as a child of "Services", assigned to the
 * theme's "Service — single" template (page-service.php). Idempotent: existing
 * pages are left alone apart from making sure the template is assigned.
 *
 * The slugs match deskman_service_catalog() in the theme template, which is what
 * turns each page into its own service-specific content.
 */
function deskman_ensure_service_pages() {
	$parent    = get_page_by_path( 'services' );
	$parent_id = $parent ? (int) $parent->ID : 0;

	$services = deskman_service_page_list();

	foreach ( $services as $slug => $title ) {
		$path     = $parent_id ? 'services/' . $slug : $slug;
		$existing = get_page_by_path( $path );

		if ( $existing ) {
			if ( 'page-service.php' !== get_post_meta( $existing->ID, '_wp_page_template', true ) ) {
				update_post_meta( $existing->ID, '_wp_page_template', 'page-service.php' );
			}
			continue;
		}

		wp_insert_post( array(
			'post_title'   => $title,
			'post_name'    => $slug,
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_parent'  => $parent_id,
			'post_content' => '',
			'meta_input'   => array( '_wp_page_template' => 'page-service.php' ),
		) );
	}
}

/** The current service pages (slug => title). Matches dm_services() in the theme. */
function deskman_service_page_list() {
	return array(
		'custom-wordpress-themes' => 'Custom WordPress Themes',
		'custom-plugins'          => 'Custom Plugins & Integrations',
		'seo'                     => 'SEO & Technical SEO',
		'woocommerce'             => 'WooCommerce Stores',
		'speed-security'          => 'Speed & Security',
		'care-plans'              => 'Care & Maintenance Plans',
	);
}

/** Service page slugs from the previous site → the page that replaces them (301). */
function deskman_service_redirect_map() {
	return array(
		'web-design'            => 'custom-wordpress-themes',
		'wordpress-development' => 'custom-wordpress-themes',
		'branding'              => 'custom-wordpress-themes',
		'digital-marketing'     => 'seo',
		'ecommerce'             => 'woocommerce',
		'conversion-growth'     => 'seo',
	);
}

/**
 * v7 migration (runs once): new service pages, old ones unpublished (their
 * URLs 301 to the replacement below), an Insights page as the blog, and the
 * Pricing / Studio pages retitled Packages / About (URLs unchanged).
 */
add_action( 'admin_init', function () {
	if ( get_option( 'deskman_site_v7' ) || ! current_user_can( 'manage_options' ) ) { return; }
	deskman_ensure_service_pages();
	foreach ( array_keys( deskman_service_redirect_map() ) as $old ) {
		$pg = get_page_by_path( 'services/' . $old );
		if ( $pg && 'publish' === $pg->post_status && ! array_key_exists( $old, deskman_service_page_list() ) ) {
			wp_update_post( array( 'ID' => $pg->ID, 'post_status' => 'draft' ) );
		}
	}
	if ( ! (int) get_option( 'page_for_posts' ) ) {
		$ins = get_page_by_path( 'insights' );
		$id  = $ins ? (int) $ins->ID : (int) wp_insert_post( array( 'post_title' => 'Insights', 'post_name' => 'insights', 'post_status' => 'publish', 'post_type' => 'page' ) );
		if ( $id ) { update_option( 'page_for_posts', $id ); }
	}
	foreach ( array( 'pricing' => 'Packages', 'studio' => 'About' ) as $slug => $title ) {
		$pg = get_page_by_path( $slug );
		if ( $pg && $pg->post_title !== $title ) { wp_update_post( array( 'ID' => $pg->ID, 'post_title' => $title ) ); }
	}
	update_option( 'deskman_site_v7', 1 );
	flush_rewrite_rules();
} );

/** 301 the previous service URLs to their replacements. */
add_action( 'template_redirect', function () {
	if ( ! is_404() ) { return; }
	$path = trim( (string) wp_parse_url( isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '', PHP_URL_PATH ), '/' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	if ( ! preg_match( '#(?:^|/)services/([a-z0-9-]+)$#', $path, $m ) ) { return; }
	$map = deskman_service_redirect_map();
	if ( ! isset( $map[ $m[1] ] ) ) { return; }
	$to = get_page_by_path( 'services/' . $map[ $m[1] ] );
	if ( $to && 'publish' === $to->post_status ) { wp_safe_redirect( get_permalink( $to ), 301 ); exit; }
} );

/**
 * One-time migration for sites that were already active before service pages
 * existed. Runs once, guarded by an option flag, then flushes rewrite rules so
 * the nested /services/<slug>/ URLs resolve immediately.
 */
add_action( 'admin_init', function () {
	if ( get_option( 'deskman_service_pages_v1' ) ) { return; }
	if ( ! current_user_can( 'manage_options' ) ) { return; }
	deskman_ensure_service_pages();
	update_option( 'deskman_service_pages_v1', 1 );
	flush_rewrite_rules();
} );

/** After first activation, send the admin to the Setup screen once. */
add_action( 'admin_init', function () {
	if ( ! get_transient( 'deskman_activation_redirect' ) ) { return; }
	delete_transient( 'deskman_activation_redirect' );
	if ( wp_doing_ajax() || isset( $_GET['activate-multi'] ) || ! current_user_can( 'manage_options' ) ) { return; }
	wp_safe_redirect( admin_url( 'admin.php?page=deskman-setup' ) );
	exit;
} );

function deskman_deactivate() { flush_rewrite_rules(); }
register_deactivation_hook( __FILE__, 'deskman_deactivate' );

/* Contact form handler, lead validation and spam filtering now live in
   includes/leads.php. It defines deskman_handle_contact() and hooks it to the
   same admin_post actions this file used to, so the form's wire format is
   unchanged — same action name, same nonce, same cf_* fields. It is required
   at the top of this file with the other includes. */

/** Register the single stored option + sanitizer. */
add_action( 'admin_init', function () {
	register_setting( DESKMAN_GROUP, DESKMAN_OPTION, array(
		'type'              => 'array',
		'sanitize_callback' => 'deskman_sanitize',
		'default'           => array(),
	) );
} );

function deskman_sanitize( $input ) {
	$schema = deskman_schema();
	$clean  = array();
	if ( ! is_array( $input ) ) { $input = array(); }
	foreach ( $schema as $key => $f ) {
		$type = $f['type'];
		if ( $type === 'checkbox' ) {
			$clean[ $key ] = isset( $input[ $key ] ) ? '1' : '0';
			continue;
		}
		$val = isset( $input[ $key ] ) ? $input[ $key ] : '';
		switch ( $type ) {
			case 'select':
				$opts = ( isset( $f['options'] ) && is_callable( $f['options'] ) ) ? call_user_func( $f['options'] ) : array();
				$clean[ $key ] = array_key_exists( $val, $opts ) ? $val : $f['default'];
				break;
			case 'color':
				$c = sanitize_hex_color( $val );
				$clean[ $key ] = $c ? $c : $f['default'];
				break;
			case 'url':
			case 'image':
			case 'video':
				$clean[ $key ] = esc_url_raw( trim( $val ) );
				break;
			case 'textarea':
				$clean[ $key ] = sanitize_textarea_field( $val );
				break;
			default:
				$clean[ $key ] = sanitize_text_field( $val );
		}
	}
	add_settings_error( DESKMAN_OPTION, 'deskman_saved', __( 'Deskman Studio content saved.', 'deskman-studio-manager' ), 'updated' );
	return $clean;
}

/** Admin menu is registered in includes/admin.php (Dashboard, Projects,
 *  Services, Testimonials, Forms, Settings, Setup, Asset checker). */


/** Quick "Edit content" link on the Plugins screen. */
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), function ( $links ) {
	$url = admin_url( 'admin.php?page=deskman-settings' );
	array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Edit content', 'deskman-studio-manager' ) . '</a>' );
	return $links;
} );

/** Media uploader + tiny admin script, only on our page. */
add_action( 'admin_enqueue_scripts', function ( $hook ) {
	$is_settings = ( isset( $_GET['page'] ) && $_GET['page'] === 'deskman-settings' );
	if ( ! $is_settings ) { return; }
	unset( $hook );
	wp_enqueue_media();
	$js = <<<JS
jQuery(function($){
	$(document).on('click','.deskman-upload',function(e){
		e.preventDefault();
		var \$btn=$(this), \$td=\$btn.closest('td');
		var lib=\$btn.data('lib')||'image', word=\$btn.data('label')||'image';
		var frame=wp.media({title:'Select or upload a '+word,button:{text:'Use this '+word},library:{type:lib},multiple:false});
		frame.on('select',function(){
			var a=frame.state().get('selection').first().toJSON();
			\$td.find('.deskman-img-field').val(a.url);
			\$td.find('.deskman-img-preview').attr('src',a.url).show();
		});
		frame.open();
	});
	$(document).on('click','.deskman-clear',function(e){
		e.preventDefault();
		var \$td=$(this).closest('td');
		\$td.find('.deskman-img-field').val('');
		\$td.find('.deskman-img-preview').attr('src','').hide();
	});
});
JS;
	wp_add_inline_script( 'jquery-core', $js );
} );

/** Settings screen. */
function deskman_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) { return; }
	$schema = deskman_schema();
	$o = get_option( DESKMAN_OPTION );
	if ( ! is_array( $o ) ) { $o = array(); }
	$val = function ( $k ) use ( $o, $schema ) {
		if ( array_key_exists( $k, $o ) ) { return $o[ $k ]; }
		return isset( $schema[ $k ]['default'] ) ? $schema[ $k ]['default'] : '';
	};

	// group by section, preserve order
	$sections = array();
	foreach ( $schema as $k => $f ) { $sections[ $f['section'] ][ $k ] = $f; }
	?>
	<div class="wrap">
		<h1><span class="dashicons dashicons-location-alt" style="font-size:26px;width:26px;height:26px;vertical-align:-3px;color:#6840FF"></span> Deskman Studio</h1>
		<p style="max-width:760px;color:#555">Edit the front-end content of your Deskman Studio site. Changes appear on the homepage immediately after saving. Leave a field blank to fall back to its built-in default.</p>
		<?php settings_errors( DESKMAN_OPTION ); ?>
		<form method="post" action="options.php">
			<?php settings_fields( DESKMAN_GROUP ); ?>
			<?php foreach ( $sections as $sec => $fields ) :
				$sec_id = 'dsk-sec-' . sanitize_title( $sec ); ?>
				<h2 id="<?php echo esc_attr( $sec_id ); ?>" style="margin-top:2em;border-bottom:1px solid #dcdcde;padding-bottom:.4em"><?php echo esc_html( $sec ); ?></h2>
				<table class="form-table" role="presentation"><tbody>
				<?php foreach ( $fields as $k => $f ) :
					$name = DESKMAN_OPTION . '[' . $k . ']';
					$v = $val( $k );
					?>
					<tr>
						<th scope="row"><label for="dsk_<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $f['label'] ); ?></label></th>
						<td>
						<?php if ( $f['type'] === 'textarea' ) : ?>
							<textarea id="dsk_<?php echo esc_attr( $k ); ?>" name="<?php echo esc_attr( $name ); ?>" rows="3" class="large-text"><?php echo esc_textarea( $v ); ?></textarea>
						<?php elseif ( $f['type'] === 'checkbox' ) : ?>
							<label><input type="checkbox" id="dsk_<?php echo esc_attr( $k ); ?>" name="<?php echo esc_attr( $name ); ?>" value="1" <?php checked( $v, '1' ); ?> /> <?php esc_html_e( 'Enabled', 'deskman-studio-manager' ); ?></label>
						<?php elseif ( $f['type'] === 'select' ) :
							$opts = ( isset( $f['options'] ) && is_callable( $f['options'] ) ) ? call_user_func( $f['options'] ) : array(); ?>
							<select id="dsk_<?php echo esc_attr( $k ); ?>" name="<?php echo esc_attr( $name ); ?>">
								<?php foreach ( $opts as $ov => $ol ) : ?>
									<option value="<?php echo esc_attr( $ov ); ?>" <?php selected( $v, $ov ); ?>><?php echo esc_html( $ol ); ?></option>
								<?php endforeach; ?>
							</select>
						<?php elseif ( $f['type'] === 'color' ) : ?>
							<input type="color" id="dsk_<?php echo esc_attr( $k ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $v ); ?>" style="width:64px;height:38px;vertical-align:middle;padding:2px" />
							<code style="margin-left:8px"><?php echo esc_html( $v ); ?></code>
						<?php elseif ( $f['type'] === 'video' ) : ?>
							<input type="text" id="dsk_<?php echo esc_attr( $k ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $v ); ?>" class="regular-text deskman-img-field" placeholder="<?php esc_attr_e( 'Empty — the clip bundled with the theme is used', 'deskman-studio-manager' ); ?>" />
							<button class="button deskman-upload" data-lib="video" data-label="<?php esc_attr_e( 'video', 'deskman-studio-manager' ); ?>"><?php esc_html_e( 'Select video', 'deskman-studio-manager' ); ?></button>
							<button class="button deskman-clear"><?php esc_html_e( 'Clear', 'deskman-studio-manager' ); ?></button>
						<?php elseif ( $f['type'] === 'image' ) : ?>
							<input type="text" id="dsk_<?php echo esc_attr( $k ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $v ); ?>" class="regular-text deskman-img-field" placeholder="No image — CSS preview is used" />
							<button class="button deskman-upload"><?php esc_html_e( 'Select image', 'deskman-studio-manager' ); ?></button>
							<button class="button deskman-clear"><?php esc_html_e( 'Clear', 'deskman-studio-manager' ); ?></button>
							<br /><img class="deskman-img-preview" src="<?php echo esc_url( $v ); ?>" style="max-width:220px;margin-top:10px;border:1px solid #dcdcde;border-radius:6px;<?php echo $v ? '' : 'display:none'; ?>" alt="" />
						<?php elseif ( $f['type'] === 'url' ) : ?>
							<input type="text" id="dsk_<?php echo esc_attr( $k ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $v ); ?>" class="regular-text" placeholder="#section, /page, https://…, mailto:…" />
						<?php else : ?>
							<input type="text" id="dsk_<?php echo esc_attr( $k ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $v ); ?>" class="regular-text" />
						<?php endif; ?>
						<?php if ( ! empty( $f['help'] ) ) : ?>
							<p class="description"><?php echo esc_html( $f['help'] ); ?></p>
						<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody></table>
			<?php endforeach; ?>
			<?php submit_button( 'Save all content' ); ?>
		</form>
	</div>
	<?php
}
