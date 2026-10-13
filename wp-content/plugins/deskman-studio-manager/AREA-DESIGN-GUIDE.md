# Building a custom design for one location page

Location pages keep their content and their look in separate places. The
generator writes the content once, into the page's own fields; a **design**
decides how those fields are drawn.

That means a Warangal-specific design can be added at any time without
regenerating anything, and removing it puts the page straight back on the
default look with every word intact.

---

## 1. Where a design lives

A design is a folder. Three locations are searched, and the first folder with a
given name wins:

| Order | Folder | Use it for |
|-------|--------|------------|
| 1 | `wp-content/themes/{child-theme}/area-designs/{name}/` | work that must survive plugin updates |
| 2 | `wp-content/themes/deskman-studio-theme/area-designs/{name}/` | the same, without a child theme |
| 3 | `wp-content/plugins/deskman-studio-manager/area-designs/{name}/` | where the bundled `default` design lives |

Inside:

```
warangal/
  template.php   required — the page
  style.css      optional — loaded only on pages using this design
  script.js      optional — loaded in the footer, same pages only
  functions.php  optional — runs before <head>, e.g. to add a font
```

## 2. How a page picks its design

1. Whatever is chosen in the **Page design** box on the edit screen.
2. Otherwise a folder named after the city slug — `warangal/` for Warangal.
3. Otherwise a folder whose `Cities:` header lists that city.
4. Otherwise `default`.

So uploading a folder called `warangal` is the entire installation step. The
edit screen tells you which design a page is using, and the Locations list has
a **Design** column so you can see the whole set at a glance.

One design for several cities:

```php
/**
 * Design Name: North Telangana
 * Cities: Warangal, Karimnagar, Khammam
 */
```

## 3. What template.php has to do

It is a **complete template** — the same contract the old single
`single-deskman_area.php` had. Call `get_header()` and `get_footer()` so the
page keeps the real nav, mega menu, ticker and footer:

```php
get_header();
while ( have_posts() ) : the_post(); $id = get_the_ID();
  // …your markup…
endwhile;
get_footer();
```

The easiest start is to copy `area-designs/default/` and edit it.

## 4. Reading the content

Everything comes through one helper:

```php
deskman_area_field( $id, 'hero_h', get_the_title() )
```

Single-value fields:

```
city  region  service  keyword  keywords
hero_eyebrow  hero_h  hero_sub  cta_label
intro_h  intro
services_h  why_h  process_h  faq_h
localities
cta_h  cta_sub  cta_btn  phone
```

Two fields need unpacking. Both return arrays and both are safe to call on an
empty field:

```php
// Comma-separated → a flat list
foreach ( deskman_area_list( deskman_area_field( $id, 'localities' ) ) as $place ) { … }

// One "Title :: description" per line → [ 't' => …, 'd' => … ]
foreach ( deskman_area_pairs( deskman_area_field( $id, 'services' ) ) as $row ) {
    echo esc_html( $row['t'] ); // title
    echo esc_html( $row['d'] ); // description
}
```

`services`, `why`, `process` and `faq` are all pair fields.

Also available:

```php
deskman_area_related( $id, 6 )   // other location pages, for cross-links
deskman_area_opt( 'service' )    // the site-wide defaults
deskman_url( 'contact' )         // a site URL by slug
```

Every value is plain text. **Always print through `esc_html()`.**

Any field can be empty if an editor cleared it, so wrap each block:

```php
<?php $faqs = deskman_area_pairs( deskman_area_field( $id, 'faq' ) ); ?>
<?php if ( $faqs ) : ?>
  …
<?php endif; ?>
```

## 5. Styling

`assets/areas.css` still loads on every location page, and your `style.css`
loads **after** it, so you can restyle the shared `.dsa-` rules without
`!important` — or ignore them entirely and use your own class names.

Scope everything to your own prefix so a design can never leak elsewhere:

```css
.wgl-main .hero { … }   /* good */
.hero { … }             /* bad — hits every page */
```

A `deskman-area-design-{name}` class is added to `<body>`, so you can also
hang rules off that.

The theme's tokens are available on these pages:

```css
--electric #A1FF62   --violet #6840FF   --coral #F84131
--ink --paper --muted --line
--font-display  --font-body  --font-mono  --font-script
--sp-8 … --sp-184        spacing, multiples of 8
--nav-h --ticker-h --nav-top
```

Clear the fixed header on your first section:

```css
.wgl-hero{ padding-top: calc(var(--nav-top) + var(--nav-h) + var(--ticker-h) + var(--sp-40)); }
```

End with a reduced-motion guard:

```css
@media (prefers-reduced-motion:reduce){ .wgl-main *{ transition:none !important; } }
```

## 6. Skeleton

```php
<?php
/**
 * Design Name: Warangal
 * Description: Local design for the Warangal location page.
 * Cities: Warangal
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();
while ( have_posts() ) : the_post();
	$id   = get_the_ID();
	$city = deskman_area_field( $id, 'city', get_the_title() );
	?>
	<main id="top" class="wgl-main">
		<section class="wgl-hero">
			<div class="wrap">
				<h1 class="wgl-h1"><?php echo esc_html( deskman_area_field( $id, 'hero_h', get_the_title() ) ); ?></h1>
				<p class="wgl-sub"><?php echo esc_html( deskman_area_field( $id, 'hero_sub' ) ); ?></p>
				<a class="btn solid" href="<?php echo esc_url( deskman_url( 'contact' ) ); ?>">
					<?php echo esc_html( deskman_area_field( $id, 'cta_label', 'Start a project' ) ); ?>
				</a>
			</div>
		</section>

		<?php $services = deskman_area_pairs( deskman_area_field( $id, 'services' ) ); ?>
		<?php if ( $services ) : ?>
			<section class="wgl-services"><div class="wrap">
				<h2><?php echo esc_html( deskman_area_field( $id, 'services_h', sprintf( 'What we do in %s', $city ) ) ); ?></h2>
				<?php foreach ( $services as $s ) : ?>
					<article class="wgl-card">
						<h3><?php echo esc_html( $s['t'] ); ?></h3>
						<p><?php echo esc_html( $s['d'] ); ?></p>
					</article>
				<?php endforeach; ?>
			</div></section>
		<?php endif; ?>
	</main>
	<?php
endwhile;
get_footer();
```

## 7. Asking for a design

Say which city and what it should feel like — local colours, motifs, imagery,
the mood you want. Reference photos or a rough sketch help most. Because the
field list above never changes, a new design is a new folder and nothing else:
the generator, the chatbot and every existing page stay exactly as they are.
