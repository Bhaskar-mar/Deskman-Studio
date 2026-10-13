<?php
/**
 * Home.
 *
 * @package Deskman_Studio
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();

$svc      = dm_services();
$projects = array_slice( dm_projects( 6 ), 0, 3 );
$launches = array_slice( dm_projects( 8 ), 0, 4 );
$pk       = dm_packages();
?>
<div class="dark">
<!-- ================================================= HERO -->
<section class="hero">
	<i class="gridbg"></i><i class="glow g1"></i><i class="glow glow--l g2"></i>
	<div class="wrap">
		<div class="hero__c">
			<a class="annc" href="<?php echo esc_url( dm_opt( 'v5_annc_url', dm_service_url( 'care-plans' ) ) ); ?>"><b><?php dm_e( 'v5_annc_b', 'New' ); ?></b><?php dm_e( 'v5_annc_t', 'Care plans for existing WordPress sites' ); ?> <?php echo dm_i( 'arrow' ); // phpcs:ignore ?></a>
			<h1 class="display"><?php echo dm_hd( dm_opt( 'v5_hero_h', 'Custom WordPress websites|{built to rank and convert.}' ) ); // phpcs:ignore ?></h1>
			<p class="lead"><?php dm_e( 'v5_hero_lead', 'Deskman Studio designs and engineers custom themes, purpose-built plugins and technical SEO — one system your team can run, without page builders or plugin piles.' ); ?></p>
			<div class="hero__cta">
				<a class="btn btn--v btn--lg" href="<?php echo esc_url( dm_link( 'contact' ) ); ?>"><?php dm_e( 'v5_hero_cta1', 'Start a project' ); ?> <?php echo dm_i( 'arrow' ); // phpcs:ignore ?></a>
				<a class="btn btn--g btn--lg" href="<?php echo esc_url( dm_link( 'work' ) ); ?>"><?php dm_e( 'v5_hero_cta2', 'See our work' ); ?></a>
			</div>
			<div class="hero__meta"><?php foreach ( dm_lines( 'v5_hero_meta', array( 'Custom theme', 'Custom plugins', 'Technical SEO', 'Care after launch' ) ) as $m ) : ?><span><?php echo dm_i( 'check' ); // phpcs:ignore ?><?php echo esc_html( $m ); ?></span><?php endforeach; ?></div>
		</div>
		<div class="shot" role="img" aria-label="<?php esc_attr_e( 'Preview of the Deskman dashboard inside WordPress, with sample data', 'deskman-studio' ); ?>"><?php get_template_part( 'template-parts/app-shot' ); ?><i class="shot__fade"></i></div>
	</div>
</section>
<section class="logos" aria-label="<?php esc_attr_e( 'Recent launches', 'deskman-studio' ); ?>"><div class="wrap"><p><?php esc_html_e( 'Recent launches', 'deskman-studio' ); ?></p><ul>
	<?php foreach ( $launches as $n => $p ) : $shape = array( '', 'border-radius:50%', 'transform:rotate(45deg) scale(.8)', '' ); ?>
		<li><i style="<?php echo esc_attr( $shape[ $n % 4 ] ); ?>"></i><?php echo esc_html( $p['name'] ); ?></li>
	<?php endforeach; ?>
</ul></div></section>
</div>

<!-- ================================================= PLATFORM TABS -->
<section class="sec" id="services">
	<div class="wrap">
		<div class="shead rv">
			<div><span class="label"><i></i><?php esc_html_e( 'One studio, one system', 'deskman-studio' ); ?></span><h2 class="h2"><?php echo dm_h( dm_opt( 'v5_tabs_h', 'Everything your website runs on, {designed to fit together.}' ) ); // phpcs:ignore ?></h2></div>
			<p class="lead"><?php dm_e( 'v5_tabs_lead', 'Theme, plugins, SEO and care are built by the same team, so your site works like one product instead of twenty parts.' ); ?></p>
		</div>
		<div class="tabs rv" data-tabs>
			<div class="tabs__list" role="tablist" aria-label="<?php esc_attr_e( 'Services', 'deskman-studio' ); ?>">
				<?php $i = 0; foreach ( array( 'custom-wordpress-themes', 'custom-plugins', 'seo', 'woocommerce', 'care-plans' ) as $slug ) : $s = $svc[ $slug ]; ?>
					<button class="tab<?php echo 0 === $i ? ' on' : ''; ?>" type="button" role="tab" id="tab-<?php echo esc_attr( $slug ); ?>" aria-controls="tp-<?php echo esc_attr( $slug ); ?>" aria-selected="<?php echo 0 === $i ? 'true' : 'false'; ?>">
						<span class="t"><span class="ico"><?php echo dm_i( $s['icon'] ); // phpcs:ignore ?></span><?php echo esc_html( $s['title'] ); ?></span>
						<span class="p"><?php echo esc_html( $s['short'] ); ?></span><span class="bar2"><i></i></span>
					</button>
				<?php $i++; endforeach; ?>
				<a class="link" href="<?php echo esc_url( dm_link( 'services' ) ); ?>" style="margin:14px 0 0 20px"><?php esc_html_e( 'Explore all services', 'deskman-studio' ); ?> <?php echo dm_i( 'arrow' ); // phpcs:ignore ?></a>
			</div>
			<div class="tabs__panel">
				<div class="tp" role="tabpanel" id="tp-custom-wordpress-themes" aria-labelledby="tab-custom-wordpress-themes"><?php get_template_part( 'template-parts/editor-shot' ); ?></div>
				<?php foreach ( array( 'custom-plugins', 'seo', 'woocommerce', 'care-plans' ) as $slug ) : $s = $svc[ $slug ]; ?>
					<div class="tp" role="tabpanel" id="tp-<?php echo esc_attr( $slug ); ?>" aria-labelledby="tab-<?php echo esc_attr( $slug ); ?>" hidden>
						<div class="app app--l" style="height:100%">
							<div class="app__bar"><span class="dots"><i></i><i></i><i></i></span><span class="url"><?php echo dm_i( 'lock' ); // phpcs:ignore ?>Deskman · <?php echo esc_html( $s['title'] ); ?></span><span class="sample"><?php esc_html_e( 'Preview', 'deskman-studio' ); ?></span></div>
							<div style="padding:24px;display:grid;grid-template-columns:repeat(2,1fr);gap:12px">
								<?php foreach ( $s['features'] as $f ) : ?>
									<div class="kpi" style="display:flex;gap:12px;align-items:flex-start;padding:16px"><span class="ico" style="width:34px;height:34px;flex:none"><?php echo dm_i( $f[0] ); // phpcs:ignore ?></span><span><b style="font-size:14px;display:block"><?php echo esc_html( $f[1] ); ?></b><small style="display:block;font-size:12.5px;color:var(--muted);margin-top:3px;line-height:1.45"><?php echo esc_html( $f[2] ); ?></small></span></div>
								<?php endforeach; ?>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>

<!-- ================================================= SERVICES BENTO -->
<section class="sec" style="padding-top:0">
	<div class="wrap">
		<div class="bento">
			<article class="dcard b1 rv" style="color:var(--dt)"><span class="ico"><?php echo dm_i( 'plug' ); // phpcs:ignore ?></span><h3 class="h3"><?php esc_html_e( 'Plugins built for how you work', 'deskman-studio' ); ?></h3><p><?php esc_html_e( 'Bookings, quotes, memberships, CRM and payment links — one plugin you own instead of five you rent.', 'deskman-studio' ); ?></p>
				<div class="vis"><div class="app" style="box-shadow:none"><table class="tbl">
					<tr><td><?php echo dm_i( 'cal' ); // phpcs:ignore ?> <?php esc_html_e( 'Bookings & appointments', 'deskman-studio' ); ?></td><td><span class="pill pill--v"><i></i><?php esc_html_e( 'Custom', 'deskman-studio' ); ?></span></td></tr>
					<tr><td><?php echo dm_i( 'api' ); // phpcs:ignore ?> <?php esc_html_e( 'CRM sync', 'deskman-studio' ); ?></td><td><span class="pill pill--ok"><i></i><?php esc_html_e( 'Connected', 'deskman-studio' ); ?></span></td></tr>
					<tr><td><?php echo dm_i( 'card' ); // phpcs:ignore ?> <?php esc_html_e( 'Payments & invoices', 'deskman-studio' ); ?></td><td><span class="pill pill--ok"><i></i><?php esc_html_e( 'Connected', 'deskman-studio' ); ?></span></td></tr></table></div></div></article>
			<article class="card b2 rv"><span class="ico"><?php echo dm_i( 'search' ); // phpcs:ignore ?></span><h3 class="h3"><?php esc_html_e( 'SEO built into the code', 'deskman-studio' ); ?></h3><p><?php esc_html_e( 'Schema, meta, sitemaps and location pages ship with the theme — then content and local SEO that compounds.', 'deskman-studio' ); ?></p>
				<div class="vis"><div class="card" style="padding:16px;box-shadow:none;background:var(--bg)">
					<small class="mono" style="color:#188038">yourbusiness.com › services</small>
					<div style="font-size:17px;color:#1A0DAB;margin-top:4px"><?php esc_html_e( 'Custom WordPress Development | Your Business', 'deskman-studio' ); ?></div>
					<div class="small" style="font-size:12.5px;margin-top:2px"><?php esc_html_e( 'Fast, accessible pages with clean structure and rich results…', 'deskman-studio' ); ?></div>
					<div style="display:flex;gap:6px;margin-top:12px;flex-wrap:wrap"><?php foreach ( array( 'Organization', 'LocalBusiness', 'FAQPage', 'Breadcrumbs' ) as $t ) : ?><span class="chiptag" style="background:var(--lime2);border-color:#C6F5A6"><?php echo esc_html( $t ); ?></span><?php endforeach; ?></div></div></div></article>
			<article class="card b3 rv"><span class="ico"><?php echo dm_i( 'cart' ); // phpcs:ignore ?></span><h3 class="h3"><?php esc_html_e( 'WooCommerce stores', 'deskman-studio' ); ?></h3><p><?php esc_html_e( 'Shops that are easy to manage and quick to check out.', 'deskman-studio' ); ?></p>
				<div class="vis"><div class="card" style="display:grid;grid-template-columns:72px 1fr;gap:12px;align-items:center;padding:8px;box-shadow:none;background:var(--bg)"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/img/jhumkana.webp' ) ); ?>" alt="" loading="lazy" width="72" height="72" style="height:72px;width:72px;object-fit:cover;border-radius:9px;object-position:62% 50%"><div><b style="font-size:13.5px"><?php esc_html_e( 'Product page', 'deskman-studio' ); ?></b><div style="display:flex;gap:6px;margin-top:8px"><span class="chiptag"><?php esc_html_e( 'Size', 'deskman-studio' ); ?></span><span class="btn btn--k" style="height:26px;font-size:11px;padding:0 10px;border-radius:7px"><?php esc_html_e( 'Add to cart', 'deskman-studio' ); ?></span></div></div></div></div></article>
			<article class="card b4 rv"><span class="ico"><?php echo dm_i( 'bolt' ); // phpcs:ignore ?></span><h3 class="h3"><?php esc_html_e( 'Speed & security', 'deskman-studio' ); ?></h3><p><?php esc_html_e( 'Lean code, caching, hardening and audits for existing sites.', 'deskman-studio' ); ?></p>
				<div class="vis" style="display:flex;gap:10px">
					<?php foreach ( array( array( 'Speed', 7 ), array( 'A11y', 3 ), array( 'SEO', 2 ) ) as $g ) : ?>
						<div class="card" style="flex:1;padding:12px;text-align:center;box-shadow:none;background:var(--bg)"><svg viewBox="0 0 36 36" style="width:44px;height:44px;margin:0 auto;display:block" aria-hidden="true"><circle cx="18" cy="18" r="15" fill="none" stroke="#E7E4DE" stroke-width="3.5"/><circle cx="18" cy="18" r="15" fill="none" stroke="#22C55E" stroke-width="3.5" stroke-linecap="round" stroke-dasharray="94.2" stroke-dashoffset="<?php echo (int) $g[1]; ?>" transform="rotate(-90 18 18)"/></svg><small class="mono" style="color:var(--muted)"><?php echo esc_html( $g[0] ); ?></small></div>
					<?php endforeach; ?>
				</div></article>
			<article class="card b5 rv"><span class="ico"><?php echo dm_i( 'cycle' ); // phpcs:ignore ?></span><h3 class="h3"><?php esc_html_e( 'Care plans', 'deskman-studio' ); ?></h3><p><?php esc_html_e( 'Updates, backups, monitoring and small changes every month.', 'deskman-studio' ); ?></p>
				<div class="vis"><ul class="card" style="box-shadow:none;background:var(--bg);overflow:hidden">
					<?php foreach ( array( array( 'Core & plugin updates', 'monthly' ), array( 'Off-site backups', 'daily' ), array( 'Uptime monitoring', '24/7' ) ) as $n => $r ) : ?>
						<li style="display:flex;gap:10px;align-items:center;padding:10px 14px;font-size:12.5px;<?php echo $n < 2 ? 'border-bottom:1px solid var(--line)' : ''; ?>"><i style="width:7px;height:7px;border-radius:50%;background:#22C55E"></i><?php echo esc_html( $r[0] ); ?><span class="mono" style="margin-left:auto;color:var(--faint)"><?php echo esc_html( $r[1] ); ?></span></li>
					<?php endforeach; ?></ul></div></article>
		</div>
	</div>
</section>

<!-- ================================================= COMPARE -->
<section class="sec dark" style="overflow:hidden">
	<i class="gridbg"></i><i class="glow" style="width:900px;height:600px;right:-300px;top:-200px;opacity:.6"></i>
	<div class="wrap" style="position:relative">
		<div class="shead rv">
			<div><span class="label"><i></i><?php esc_html_e( 'The Deskman way', 'deskman-studio' ); ?></span><h2 class="h2"><?php echo dm_hd( dm_opt( 'v5_cmp_h', 'One system — {not a pile of plugins.}' ) ); // phpcs:ignore ?></h2></div>
			<p class="lead"><?php dm_e( 'v5_cmp_lead', 'Most sites are a bought theme, a page builder and twenty plugins that never quite agree. Here\'s the difference.' ); ?></p>
		</div>
		<div class="cmp rv">
			<div class="cmp__col cmp__col--a"><h4><?php esc_html_e( 'Typical template build', 'deskman-studio' ); ?> <span class="chiptag"><?php esc_html_e( 'The usual', 'deskman-studio' ); ?></span></h4>
				<ul><?php foreach ( dm_compare() as $r ) : ?><li><span class="no" style="display:contents"><?php echo dm_i( 'x' ); // phpcs:ignore ?></span><?php echo esc_html( $r[1] ); ?><small><?php echo esc_html( $r[0] ); ?></small></li><?php endforeach; ?></ul></div>
			<div class="cmp__col cmp__col--b"><h4><?php esc_html_e( 'Deskman build', 'deskman-studio' ); ?> <span class="mono" style="font-size:10.5px;padding:4px 9px;border-radius:7px;background:var(--lime);color:var(--ink)"><?php esc_html_e( 'RECOMMENDED', 'deskman-studio' ); ?></span></h4>
				<ul><?php foreach ( dm_compare() as $r ) : ?><li><span class="yes" style="display:contents"><?php echo dm_i( 'check' ); // phpcs:ignore ?></span><?php echo esc_html( $r[2] ); ?><small><?php echo esc_html( $r[0] ); ?></small></li><?php endforeach; ?></ul></div>
		</div>
	</div>
</section>

<!-- ================================================= WORK -->
<section class="sec" id="work">
	<div class="wrap">
		<div class="shead rv">
			<div><span class="label"><i></i><?php esc_html_e( 'Selected work', 'deskman-studio' ); ?></span><h2 class="h2"><?php echo dm_h( dm_opt( 'v5_work_h', 'Built for real businesses, {live today.}' ) ); // phpcs:ignore ?></h2></div>
			<a class="btn btn--w" href="<?php echo esc_url( dm_link( 'work' ) ); ?>"><?php esc_html_e( 'View all projects', 'deskman-studio' ); ?> <?php echo dm_i( 'arrow' ); // phpcs:ignore ?></a>
		</div>
		<div class="works">
			<?php foreach ( $projects as $p ) { get_template_part( 'template-parts/work-card', null, array( 'p' => $p ) ); } ?>
		</div>
	</div>
</section>

<!-- ================================================= PROCESS -->
<section class="sec" id="process" style="padding-top:0">
	<div class="wrap">
		<div class="shead rv">
			<div><span class="label"><i></i><?php esc_html_e( 'How we work', 'deskman-studio' ); ?></span><h2 class="h2"><?php echo dm_h( dm_opt( 'v5_proc_h', 'A clear path from first call {to launch — and beyond.}' ) ); // phpcs:ignore ?></h2></div>
			<p class="lead"><?php dm_e( 'v5_proc_lead', 'One point of contact, a staging site you can click through, and weekly updates at every step.' ); ?></p>
		</div>
		<ol class="proc rv">
			<?php foreach ( dm_process() as $n => $st ) : ?>
				<li class="ps<?php echo 0 === $n ? ' on' : ''; ?>"><i class="prog"></i><div class="top"><span class="ico"><?php echo dm_i( $st[0] ); // phpcs:ignore ?></span><span class="mono"><?php echo esc_html( sprintf( '%02d', $n + 1 ) ); ?></span></div><h3 style="font-size:18px;letter-spacing:-.02em;margin-top:18px"><?php echo esc_html( $st[1] ); ?></h3><p><?php echo esc_html( $st[2] ); ?></p>
					<ul><?php foreach ( $st[3] as $li ) : ?><li><?php echo dm_i( 'check' ); // phpcs:ignore ?><?php echo esc_html( $li ); ?></li><?php endforeach; ?></ul></li>
			<?php endforeach; ?>
		</ol>
	</div>
</section>

<!-- ================================================= STANDARDS -->
<section class="sec dark" style="overflow:hidden">
	<i class="gridbg"></i>
	<div class="wrap" style="position:relative">
		<div class="shead shead--c rv"><div><span class="label"><i></i><?php esc_html_e( 'Engineering standards', 'deskman-studio' ); ?></span><h2 class="h2" style="margin-top:18px"><?php echo dm_hd( 'Every Deskman site ships with {the same foundations.}' ); // phpcs:ignore ?></h2></div></div>
		<div class="std">
			<?php foreach ( dm_standards() as $st ) : ?>
				<div class="dcard rv"><span class="ico"><?php echo dm_i( $st[0] ); // phpcs:ignore ?></span><h3 style="font-size:16px;margin-top:16px;letter-spacing:-.02em"><?php echo esc_html( $st[1] ); ?></h3><p style="font-size:13.5px;margin-top:6px"><?php echo esc_html( $st[2] ); ?></p></div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- ================================================= PACKAGES -->
<section class="sec" id="packages">
	<div class="wrap">
		<div class="shead rv"><div><span class="label"><i></i><?php esc_html_e( 'Packages', 'deskman-studio' ); ?></span><h2 class="h2"><?php echo dm_h( dm_opt( 'v5_pk_h', 'Pick a starting point. {We\'ll shape the rest.}' ) ); // phpcs:ignore ?></h2></div>
			<div style="display:flex;flex-direction:column;align-items:flex-end;gap:18px"><p class="lead" style="text-align:right"><?php dm_e( 'v5_pk_lead', 'Every project is scoped to what you need, with a clear fixed quote after a short call.' ); ?></p>
			<span class="seg" role="tablist"><button type="button" class="on" data-seg="new" aria-selected="true"><?php esc_html_e( 'New website', 'deskman-studio' ); ?></button><button type="button" data-seg="existing" aria-selected="false"><?php esc_html_e( 'Existing website', 'deskman-studio' ); ?></button></span></div></div>
		<?php get_template_part( 'template-parts/plans', null, array( 'pk' => $pk ) ); ?>
	</div>
</section>

<!-- ================================================= FAQ -->
<section class="sec" style="padding-top:0">
	<div class="wrap">
		<div class="shead rv"><div><span class="label"><i></i>FAQ</span><h2 class="h2"><?php echo dm_h( 'Good to know {before we start.}' ); // phpcs:ignore ?></h2></div><a class="btn btn--w" href="<?php echo esc_url( dm_link( 'contact' ) ); ?>"><?php echo dm_i( 'mail' ); // phpcs:ignore ?><?php esc_html_e( 'Ask a question', 'deskman-studio' ); ?></a></div>
		<?php get_template_part( 'template-parts/faq', null, array( 'items' => dm_faq() ) ); ?>
	</div>
</section>

<?php get_template_part( 'template-parts/cta', null, array( 'style' => 'padding-top:0' ) ); ?>
<?php get_footer(); ?>
