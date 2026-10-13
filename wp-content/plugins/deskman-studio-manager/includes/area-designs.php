<?php
/**
 * Area page designs.
 *
 * Until now every location page rendered through one bundled template, and the
 * only way to change the look was to replace that template for every city at
 * once. This module keeps the content exactly where it is — the same CPT, the
 * same fields, the same generator — and makes only the *presentation*
 * swappable, one city at a time.
 *
 * A design is a folder:
 *
 *   area-designs/warangal/
 *     template.php    required — a complete template (calls get_header())
 *     style.css       optional — loaded only on pages using this design
 *     script.js       optional — loaded in the footer, same pages only
 *     functions.php   optional — runs before <head>, e.g. to add a font
 *
 * Folders are found in three places, first match wins, so custom work in the
 * theme is never overwritten by a plugin update:
 *
 *   1. {child theme}/area-designs/{name}/
 *   2. {theme}/area-designs/{name}/
 *   3. {this plugin}/area-designs/{name}/
 *
 * Which design a page uses:
 *
 *   1. the design chosen in the "Page design" box on the edit screen;
 *   2. otherwise a folder named after the city slug — warangal/ for Warangal;
 *   3. otherwise a folder whose "Cities:" header lists that city;
 *   4. otherwise "default".
 *
 * So dropping in a folder called warangal/ is the whole installation step, and
 * deleting it puts the page straight back on the default design without
 * touching a word of its content.
 *
 * @package Deskman_Studio_Manager
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'DESKMAN_AREA_DESIGN_META', '_deskman_area_design' );

/**
 * Every installed design.
 *
 * @return array name => [ name, label, description, cities, dir, url, source ]
 */
function deskman_area_designs() {
	static $cache = null;
	if ( $cache !== null ) { return $cache; }

	$roots = array();
	$child = get_stylesheet_directory();
	$theme = get_template_directory();
	$roots[] = array( $child . '/area-designs', get_stylesheet_directory_uri() . '/area-designs', 'theme' );
	if ( $theme !== $child ) {
		$roots[] = array( $theme . '/area-designs', get_template_directory_uri() . '/area-designs', 'theme' );
	}
	$roots[] = array( untrailingslashit( DESKMAN_DIR ) . '/area-designs', untrailingslashit( DESKMAN_URL ) . '/area-designs', 'plugin' );

	$found = array();
	foreach ( $roots as $root ) {
		list( $dir, $url, $source ) = $root;
		if ( ! is_dir( $dir ) ) { continue; }
		foreach ( (array) glob( trailingslashit( $dir ) . '*', GLOB_ONLYDIR ) as $path ) {
			$name = sanitize_key( basename( $path ) );
			if ( $name === '' || isset( $found[ $name ] ) || ! is_file( $path . '/template.php' ) ) { continue; }

			$head = get_file_data( $path . '/template.php', array(
				'label'       => 'Design Name',
				'description' => 'Description',
				'cities'      => 'Cities',
			) );
			$cities = array_filter( array_map( 'sanitize_title', array_map( 'trim', explode( ',', (string) $head['cities'] ) ) ) );

			$found[ $name ] = array(
				'name'        => $name,
				'label'       => $head['label'] !== '' ? $head['label'] : ucwords( str_replace( array( '-', '_' ), ' ', $name ) ),
				'description' => (string) $head['description'],
				'cities'      => array_values( $cities ),
				'dir'         => $path,
				'url'         => trailingslashit( $url ) . basename( $path ),
				'source'      => $source,
			);
		}
	}

	$cache = $found;
	return $cache;
}

/** One design by name, falling back to default. */
function deskman_area_design( $name ) {
	$all = deskman_area_designs();
	if ( isset( $all[ $name ] ) ) { return $all[ $name ]; }
	return isset( $all['default'] ) ? $all['default'] : null;
}

/** The design name a given area page should render with. */
function deskman_area_design_for( $post_id ) {
	$post_id = (int) $post_id;
	$all     = deskman_area_designs();
	$chosen  = sanitize_key( (string) get_post_meta( $post_id, DESKMAN_AREA_DESIGN_META, true ) );

	if ( $chosen !== '' && isset( $all[ $chosen ] ) ) {
		$name = $chosen;
	} else {
		$name = 'default';
		$city = sanitize_title( (string) deskman_area_field( $post_id, 'city' ) );
		if ( $city === '' ) { $city = (string) get_post_field( 'post_name', $post_id ); }
		if ( $city !== '' ) {
			if ( isset( $all[ $city ] ) ) {
				$name = $city;
			} else {
				foreach ( $all as $d ) {
					if ( in_array( $city, $d['cities'], true ) ) { $name = $d['name']; break; }
				}
			}
		}
	}

	/**
	 * Filter the design an area page renders with.
	 *
	 * @param string $name    Design folder name.
	 * @param int    $post_id Area page ID.
	 */
	$name = (string) apply_filters( 'deskman_area_design', $name, $post_id );
	return isset( $all[ $name ] ) ? $name : 'default';
}

/* ==========================================================================
   Front end
   ========================================================================== */

/** The area page being viewed, or 0. */
function deskman_area_current_id() {
	return is_singular( DESKMAN_AREA_CPT ) ? (int) get_queried_object_id() : 0;
}

/**
 * Route a single area page to its design.
 *
 * Runs at priority 30, after the routing already in areas.php (20), so a theme
 * that supplies its own single-deskman_area.php still wins — that override has
 * always been supported and stays supported.
 */
add_filter( 'template_include', function ( $template ) {
	$id = deskman_area_current_id();
	if ( ! $id ) { return $template; }

	/* The theme's own override was picked at priority 20; leave it alone. */
	$theme_override = locate_template( array( 'single-' . DESKMAN_AREA_CPT . '.php' ) );
	if ( $theme_override && $template === $theme_override ) { return $template; }

	$design = deskman_area_design( deskman_area_design_for( $id ) );
	if ( $design && is_file( $design['dir'] . '/template.php' ) ) {
		return $design['dir'] . '/template.php';
	}
	return $template;
}, 30 );

/** A design's optional functions.php, early enough to enqueue from. */
add_action( 'template_redirect', function () {
	$id = deskman_area_current_id();
	if ( ! $id ) { return; }
	$design = deskman_area_design( deskman_area_design_for( $id ) );
	if ( $design && is_file( $design['dir'] . '/functions.php' ) ) {
		include_once $design['dir'] . '/functions.php';
	}
} );

/**
 * A design's own assets, after areas.css (enqueued in areas.php at the default
 * priority) so a design can restyle the shared .dsa- rules without !important.
 */
add_action( 'wp_enqueue_scripts', function () {
	$id = deskman_area_current_id();
	if ( ! $id ) { return; }
	$design = deskman_area_design( deskman_area_design_for( $id ) );
	if ( ! $design ) { return; }

	$css = $design['dir'] . '/style.css';
	if ( is_file( $css ) ) {
		wp_enqueue_style(
			'deskman-area-design-' . $design['name'],
			$design['url'] . '/style.css',
			array( 'deskman-areas' ),
			(string) filemtime( $css )
		);
	}
	$js = $design['dir'] . '/script.js';
	if ( is_file( $js ) ) {
		wp_enqueue_script(
			'deskman-area-design-' . $design['name'],
			$design['url'] . '/script.js',
			array(),
			(string) filemtime( $js ),
			true
		);
	}
}, 20 );

/** So a design can hang CSS off the body as well as its own markup. */
add_filter( 'body_class', function ( $classes ) {
	$id = deskman_area_current_id();
	if ( $id ) { $classes[] = 'deskman-area-design-' . deskman_area_design_for( $id ); }
	return $classes;
} );

/* ==========================================================================
   Edit screen: the design picker
   ========================================================================== */

add_action( 'add_meta_boxes', function () {
	add_meta_box(
		'deskman-area-design',
		__( 'Page design', 'deskman-studio-manager' ),
		'deskman_area_design_box',
		DESKMAN_AREA_CPT,
		'side',
		'default'
	);
} );

function deskman_area_design_box( $post ) {
	$designs = deskman_area_designs();
	$saved   = sanitize_key( (string) get_post_meta( $post->ID, DESKMAN_AREA_DESIGN_META, true ) );
	$active  = deskman_area_design_for( $post->ID );
	$city    = deskman_area_field( $post->ID, 'city' );
	wp_nonce_field( 'deskman_area_design_save', 'deskman_area_design_nonce' );
	?>
	<p>
		<label for="deskman-area-design-select" class="screen-reader-text"><?php esc_html_e( 'Design', 'deskman-studio-manager' ); ?></label>
		<select name="deskman_area_design" id="deskman-area-design-select" style="width:100%">
			<option value=""><?php esc_html_e( 'Automatic', 'deskman-studio-manager' ); ?></option>
			<?php foreach ( $designs as $d ) : ?>
				<option value="<?php echo esc_attr( $d['name'] ); ?>" <?php selected( $saved, $d['name'] ); ?>>
					<?php echo esc_html( $d['label'] ); ?>
				</option>
			<?php endforeach; ?>
		</select>
	</p>
	<p class="description">
		<?php
		printf(
			/* translators: %s: design name currently in use. */
			esc_html__( 'Now using: %s', 'deskman-studio-manager' ),
			'<code>' . esc_html( $active ) . '</code>'
		);
		?>
		<br>
		<?php
		if ( $city !== '' ) {
			printf(
				/* translators: 1: folder name, 2: city name. */
				esc_html__( 'On Automatic, a design folder named %1$s would be used for %2$s as soon as it exists.', 'deskman-studio-manager' ),
				'<code>' . esc_html( sanitize_title( $city ) ) . '</code>',
				esc_html( $city )
			);
		} else {
			esc_html_e( 'On Automatic, a design folder named after this page\'s city is used when one exists, otherwise the default.', 'deskman-studio-manager' );
		}
		?>
	</p>
	<?php
}

add_action( 'save_post_' . DESKMAN_AREA_CPT, function ( $post_id ) {
	if ( ! isset( $_POST['deskman_area_design_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['deskman_area_design_nonce'] ) ), 'deskman_area_design_save' ) ) { return; }
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) ) { return; }
	if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }

	$value = isset( $_POST['deskman_area_design'] ) ? sanitize_key( wp_unslash( $_POST['deskman_area_design'] ) ) : '';
	if ( $value === '' || ! array_key_exists( $value, deskman_area_designs() ) ) {
		delete_post_meta( $post_id, DESKMAN_AREA_DESIGN_META );
	} else {
		update_post_meta( $post_id, DESKMAN_AREA_DESIGN_META, $value );
	}
} );

/* A "Design" column in the Locations list, so it is obvious at a glance which
   pages are on a custom look and which are on the default. */
add_filter( 'manage_' . DESKMAN_AREA_CPT . '_posts_columns', function ( $cols ) {
	$out = array();
	foreach ( $cols as $key => $label ) {
		$out[ $key ] = $label;
		if ( $key === 'title' ) {
			$out['deskman_design'] = __( 'Design', 'deskman-studio-manager' );
		}
	}
	return $out;
} );

add_action( 'manage_' . DESKMAN_AREA_CPT . '_posts_custom_column', function ( $col, $post_id ) {
	if ( $col !== 'deskman_design' ) { return; }
	$name  = deskman_area_design_for( $post_id );
	$saved = get_post_meta( $post_id, DESKMAN_AREA_DESIGN_META, true );
	echo '<code>' . esc_html( $name ) . '</code>';
	if ( $saved === '' && $name !== 'default' ) {
		echo ' <span class="description">' . esc_html__( '(matched by city)', 'deskman-studio-manager' ) . '</span>';
	}
}, 10, 2 );
