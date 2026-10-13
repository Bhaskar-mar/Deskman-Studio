<?php
/**
 * Product shot: the Deskman dashboard inside WordPress (hero visual).
 * Illustrative UI, clearly labelled as sample data.
 *
 * @package Deskman_Studio
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<div class="app">
 <div class="app__bar"><span class="dots"><i></i><i></i><i></i></span><span class="url"><svg class="i"><use href="#i-lock"/></svg>yourbusiness.com/wp-admin/admin.php?page=deskman</span><span class="sample">Preview · sample data</span></div>
 <div class="app__body">
  <aside class="side">
   <span class="brand"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/img' ) ); ?>/deskman-symbol.png" alt=""><b>Deskman</b></span>
   <a class="on" href="#"><svg class="i"><use href="#i-home"/></svg>Overview</a>
   <a href="#"><svg class="i"><use href="#i-inbox"/></svg>Leads<span class="n">4</span></a>
   <a href="#"><svg class="i"><use href="#i-file"/></svg>Pages</a>
   <a href="#"><svg class="i"><use href="#i-grid"/></svg>Projects</a>
   <h6>GROWTH</h6>
   <a href="#"><svg class="i"><use href="#i-search"/></svg>SEO</a>
   <a href="#"><svg class="i"><use href="#i-gauge"/></svg>Performance</a>
   <a href="#"><svg class="i"><use href="#i-globe"/></svg>Locations</a>
   <h6>CARE</h6>
   <a href="#"><svg class="i"><use href="#i-shield"/></svg>Security</a>
   <a href="#"><svg class="i"><use href="#i-cycle"/></svg>Updates</a>
   <span class="me"><i></i><span>Your team<small>Editor</small></span></span>
  </aside>
  <div class="main">
   <div class="main__h"><b>Overview</b><span class="chip"><svg class="i"><use href="#i-cal"/></svg>Last 30 days</span><span class="sp chip"><svg class="i"><use href="#i-plus"/></svg>New page</span></div>
   <div class="kpis">
    <div class="kpi"><small><svg class="i"><use href="#i-inbox"/></svg>Enquiries</small><strong>38<em>+12%</em></strong><svg class="sp" viewBox="0 0 100 26" preserveAspectRatio="none"><defs><linearGradient id="sg412" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#8B6CFF" stop-opacity=".35"/><stop offset="1" stop-color="#8B6CFF" stop-opacity="0"/></linearGradient></defs><path d="M0,20 12,18 24,19 36,14 48,15 60,10 72,11 84,6 100,4 V26 H0Z" fill="url(#sg412)"/><path d="M0,20 12,18 24,19 36,14 48,15 60,10 72,11 84,6 100,4" fill="none" stroke="#8B6CFF" stroke-width="1.6" vector-effect="non-scaling-stroke"/></svg></div>
    <div class="kpi"><small><svg class="i"><use href="#i-file"/></svg>Pages published</small><strong>24</strong><svg class="sp" viewBox="0 0 100 26" preserveAspectRatio="none"><defs><linearGradient id="sg951" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#A1FF62" stop-opacity=".35"/><stop offset="1" stop-color="#A1FF62" stop-opacity="0"/></linearGradient></defs><path d="M0,18 14,18 28,15 42,15 56,12 70,12 84,9 100,9 V26 H0Z" fill="url(#sg951)"/><path d="M0,18 14,18 28,15 42,15 56,12 70,12 84,9 100,9" fill="none" stroke="#A1FF62" stroke-width="1.6" vector-effect="non-scaling-stroke"/></svg></div>
    <div class="kpi"><small><svg class="i"><use href="#i-search"/></svg>SEO checks</small><strong>46/48</strong><svg class="sp" viewBox="0 0 100 26" preserveAspectRatio="none"><defs><linearGradient id="sg881" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#8B6CFF" stop-opacity=".35"/><stop offset="1" stop-color="#8B6CFF" stop-opacity="0"/></linearGradient></defs><path d="M0,14 16,12 32,13 48,9 64,8 80,6 100,5 V26 H0Z" fill="url(#sg881)"/><path d="M0,14 16,12 32,13 48,9 64,8 80,6 100,5" fill="none" stroke="#8B6CFF" stroke-width="1.6" vector-effect="non-scaling-stroke"/></svg></div>
    <div class="kpi"><small><svg class="i"><use href="#i-gauge"/></svg>Core Web Vitals</small><strong>Good</strong><svg class="sp" viewBox="0 0 100 26" preserveAspectRatio="none"><defs><linearGradient id="sg471" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#22C55E" stop-opacity=".35"/><stop offset="1" stop-color="#22C55E" stop-opacity="0"/></linearGradient></defs><path d="M0,16 16,15 32,12 48,12 64,9 80,8 100,7 V26 H0Z" fill="url(#sg471)"/><path d="M0,16 16,15 32,12 48,12 64,9 80,8 100,7" fill="none" stroke="#22C55E" stroke-width="1.6" vector-effect="non-scaling-stroke"/></svg></div>
   </div>
   <div class="panes">
    <div class="pane"><div class="pane__h">Recent enquiries <span>View all</span></div>
     <table class="tbl">
      <tr><td><span class="av" style="background:#6840FF">RK</span>Website redesign</td><td>Contact form</td><td><span class="pill pill--v"><i></i>New</span></td><td>2m ago</td></tr>
      <tr><td><span class="av" style="background:#0EA5E9">SP</span>WooCommerce store</td><td>Get a quote</td><td><span class="pill pill--v"><i></i>New</span></td><td>1h ago</td></tr>
      <tr><td><span class="av" style="background:#F59E0B">AM</span>Booking plugin</td><td>Service page</td><td><span class="pill pill--ok"><i></i>Replied</span></td><td>Yesterday</td></tr>
      <tr><td><span class="av" style="background:#10B981">NV</span>Local SEO</td><td>Location page</td><td><span class="pill pill--ok"><i></i>Replied</span></td><td>2d ago</td></tr>
     </table></div>
    <div class="pane"><div class="pane__h">Site health <span>All good</span></div>
     <ul class="health">
      <li><svg class="i"><use href="#i-search"/></svg><b>Schema &amp; sitemap</b><span class="pill pill--ok"><i></i>Valid</span></li>
      <li><svg class="i"><use href="#i-gauge"/></svg><b>Page speed</b><span class="pill pill--ok"><i></i>Good</span></li>
      <li><svg class="i"><use href="#i-cycle"/></svg><b>Core &amp; plugins</b><span class="pill pill--ok"><i></i>Up to date</span></li>
      <li><svg class="i"><use href="#i-shield"/></svg><b>Backups</b><span class="pill pill--ok"><i></i>Daily</span></li>
     </ul></div>
   </div>
   <div class="traffic"><div class="pane__h">Organic visits <span>Search Console · sample</span></div>
    <svg viewBox="0 0 600 92" preserveAspectRatio="none"><defs><linearGradient id="tg" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#8B6CFF" stop-opacity=".35"/><stop offset="1" stop-color="#8B6CFF" stop-opacity="0"/></linearGradient></defs>
    <path d="M0 30H600M0 60H600" stroke="rgba(255,255,255,.06)"/>
    <path d="M0 78 40 74 80 76 120 66 160 69 200 58 240 61 280 50 320 53 360 42 400 45 440 34 480 37 520 25 560 22 600 14V92H0Z" fill="url(#tg)"/>
    <path d="M0 78 40 74 80 76 120 66 160 69 200 58 240 61 280 50 320 53 360 42 400 45 440 34 480 37 520 25 560 22 600 14" fill="none" stroke="#8B6CFF" stroke-width="2" vector-effect="non-scaling-stroke"/></svg></div>
  </div>
 </div>
</div>
