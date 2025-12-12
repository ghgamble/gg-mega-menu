<?php
/**
 * Plugin Name: GG Mega Menu
 * Description: GG Dev reusable mega menu: Menu Summary field for pages, custom walker, and hover mega menu behavior.
 * Author: GG Dev
 * Version: 1.0.0
 * Text Domain: gg-mega-menu
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GGMM_VERSION', '1.0.0' );
define( 'GGMM_URL', plugin_dir_url( __FILE__ ) );
define( 'GGMM_PATH', plugin_dir_path( __FILE__ ) );

/**
 * Include the custom Walker class.
 */
require_once GGMM_PATH . 'inc/class-gg-mega-menu-walker.php';

/**
 * Add "Menu Summary" meta box on Pages
 * Meta key: _gg_menu_summary (same key you already use)
 */
add_action( 'add_meta_boxes', 'ggmm_add_menu_summary_metabox' );
function ggmm_add_menu_summary_metabox() {
	add_meta_box(
		'gg_menu_summary',
		__( 'Menu Summary', 'gg-mega-menu' ),
		'ggmm_menu_summary_cb',
		'page',
		'normal',
		'high'
	);
}

function ggmm_menu_summary_cb( $post ) {
	wp_nonce_field( 'ggmm_menu_summary_save', 'ggmm_menu_summary_nonce' );
	$value = get_post_meta( $post->ID, '_gg_menu_summary', true );

	echo '<textarea style="width:100%; height:80px;" name="gg_menu_summary">';
	echo esc_textarea( $value );
	echo '</textarea>';
}

/**
 * Save Menu Summary meta
 */
add_action( 'save_post', 'ggmm_save_menu_summary_meta' );
function ggmm_save_menu_summary_meta( $post_id ) {

	// Nonce & permissions
	if (
		! isset( $_POST['ggmm_menu_summary_nonce'] ) ||
		! wp_verify_nonce( $_POST['ggmm_menu_summary_nonce'], 'ggmm_menu_summary_save' )
	) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// Save value
	if ( isset( $_POST['gg_menu_summary'] ) ) {
		update_post_meta(
			$post_id,
			'_gg_menu_summary',
			sanitize_text_field( $_POST['gg_menu_summary'] )
		);
	}
}

/**
 * Enqueue Mega Menu assets (CSS + JS)
 */
add_action( 'wp_enqueue_scripts', 'ggmm_enqueue_mega_menu_assets' );
function ggmm_enqueue_mega_menu_assets() {

	wp_enqueue_style(
		'gg-mega-menu',
		GGMM_URL . 'assets/css/mega-menu.css',
		array(),
		GGMM_VERSION
	);

	wp_enqueue_script(
		'gg-mega-menu',
		GGMM_URL . 'assets/js/mega-menu.js',
		array(),
		GGMM_VERSION,
		true
	);
}

/**
 * Attach the custom Walker to a menu location.
 *
 * ❗ Adjust the theme_location list to match your theme.
 *   For your WRV site, 'primary' or 'main-menu' might be right.
 */
add_filter( 'wp_nav_menu_args', function( $args ) {

	$mega_locations = array( 'primary', 'main-menu', 'header' );

	if ( isset( $args['theme_location'] ) && in_array( $args['theme_location'], $mega_locations, true ) ) {

		// Walker
		$args['walker'] = new GG_Mega_Menu_Walker();

		// Add a predictable wrapper class (works across themes)
		$args['container']       = $args['container'] ?? 'nav';
		$args['container_class'] = trim( ( $args['container_class'] ?? '' ) . ' gg-mega-nav' );

		// Add a predictable <ul> class (JS + CSS can target this instead of #menu-main-menu)
		$args['menu_class'] = trim( ( $args['menu_class'] ?? '' ) . ' gg-mega-menu' );
	}

	return $args;
}, 20 );



/**
 * Add Screen Options toggles for our custom menu item fields.
 */
add_filter( 'manage_nav-menus_columns', function( $columns ) {
	$columns['ggmm_menu_item_summary'] = __( 'GG: Menu Item Summary', 'gg-mega-menu' );
	$columns['ggmm_menu_item_image']   = __( 'GG: Menu Item Image', 'gg-mega-menu' );
	return $columns;
} );

/**
 * Output custom fields on each menu item.
 * Respects Screen Options via "manage_nav-menus_columns".
 */
add_action( 'wp_nav_menu_item_custom_fields', function( $item_id, $item, $depth, $args ) {

	// Screen Options enabled columns (what user checked)
	$enabled = get_user_option( 'managenav-menuscolumnshidden' );
	$hidden  = is_array( $enabled ) ? $enabled : array(); // hidden column keys

	$show_summary = ! in_array( 'ggmm_menu_item_summary', $hidden, true );
	$show_image   = ! in_array( 'ggmm_menu_item_image',   $hidden, true );

	$summary = get_post_meta( $item_id, '_ggmm_menu_item_summary', true );
	$image_id = (int) get_post_meta( $item_id, '_ggmm_menu_item_image_id', true );

	$image_thumb = $image_id ? wp_get_attachment_image( $image_id, array( 80, 80 ), false, array(
		'style' => 'display:block; margin-top:6px; border:1px solid #ccd0d4; padding:2px; background:#fff;',
	) ) : '';

	// Summary field
	if ( $show_summary ) : ?>
		<p class="description description-wide ggmm-field ggmm-field-summary">
			<label for="edit-menu-item-ggmm-summary-<?php echo esc_attr( $item_id ); ?>">
				<?php esc_html_e( 'GG Menu Item Summary', 'gg-mega-menu' ); ?><br>
				<textarea
					id="edit-menu-item-ggmm-summary-<?php echo esc_attr( $item_id ); ?>"
					class="widefat code edit-menu-item-ggmm-summary"
					rows="3"
					name="menu-item-ggmm-summary[<?php echo esc_attr( $item_id ); ?>]"
				><?php echo esc_textarea( $summary ); ?></textarea>
			</label>
		</p>
	<?php endif;

	// Image field
	if ( $show_image ) : ?>
		<p class="description description-wide ggmm-field ggmm-field-image">
			<label>
				<?php esc_html_e( 'GG Menu Item Image', 'gg-mega-menu' ); ?>
			</label>
			<input
				type="hidden"
				class="ggmm-menu-item-image-id"
				name="menu-item-ggmm-image-id[<?php echo esc_attr( $item_id ); ?>]"
				value="<?php echo esc_attr( $image_id ); ?>"
			/>
			<span class="ggmm-menu-item-image-preview">
				<?php echo $image_thumb ? $image_thumb : '<em style="display:block; margin-top:6px;">No image selected.</em>'; ?>
			</span>
			<br>
			<button type="button" class="button ggmm-select-image">
				<?php esc_html_e( 'Select Image', 'gg-mega-menu' ); ?>
			</button>
			<button type="button" class="button ggmm-remove-image">
				<?php esc_html_e( 'Remove Image', 'gg-mega-menu' ); ?>
			</button>
		</p>
	<?php endif;

}, 10, 4 );

/**
 * Save custom menu item fields.
 */
add_action( 'wp_update_nav_menu_item', function( $menu_id, $menu_item_db_id ) {

	// Summary
	if ( isset( $_POST['menu-item-ggmm-summary'][ $menu_item_db_id ] ) ) {
		$val = sanitize_text_field( wp_unslash( $_POST['menu-item-ggmm-summary'][ $menu_item_db_id ] ) );
		update_post_meta( $menu_item_db_id, '_ggmm_menu_item_summary', $val );
	} else {
		delete_post_meta( $menu_item_db_id, '_ggmm_menu_item_summary' );
	}

	// Image ID
	if ( isset( $_POST['menu-item-ggmm-image-id'][ $menu_item_db_id ] ) ) {
		$image_id = (int) $_POST['menu-item-ggmm-image-id'][ $menu_item_db_id ];
		if ( $image_id > 0 ) {
			update_post_meta( $menu_item_db_id, '_ggmm_menu_item_image_id', $image_id );
		} else {
			delete_post_meta( $menu_item_db_id, '_ggmm_menu_item_image_id' );
		}
	} else {
		delete_post_meta( $menu_item_db_id, '_ggmm_menu_item_image_id' );
	}

}, 10, 2 );

add_action( 'admin_enqueue_scripts', function( $hook ) {

	if ( $hook !== 'nav-menus.php' ) {
		return;
	}

	wp_enqueue_media();

	wp_add_inline_script( 'jquery-core', "
	jQuery(function($){
		let frame;

		$(document).on('click', '.ggmm-select-image', function(e){
			e.preventDefault();

			const wrap = $(this).closest('.ggmm-field-image');
			const input = wrap.find('.ggmm-menu-item-image-id');
			const preview = wrap.find('.ggmm-menu-item-image-preview');

			if (frame) { frame.open(); return; }

			frame = wp.media({
				title: 'Select Menu Item Image',
				button: { text: 'Use this image' },
				multiple: false
			});

			frame.on('select', function(){
				const att = frame.state().get('selection').first().toJSON();
				input.val(att.id);

				const url = (att.sizes && att.sizes.thumbnail) ? att.sizes.thumbnail.url : att.url;
				preview.html('<img src=\"' + url + '\" style=\"display:block; margin-top:6px; border:1px solid #ccd0d4; padding:2px; background:#fff; max-width:80px; height:auto;\" />');
			});

			frame.open();
		});

		$(document).on('click', '.ggmm-remove-image', function(e){
			e.preventDefault();
			const wrap = $(this).closest('.ggmm-field-image');
			wrap.find('.ggmm-menu-item-image-id').val('');
			wrap.find('.ggmm-menu-item-image-preview').html('<em style=\"display:block; margin-top:6px;\">No image selected.</em>');
		});
	});
	" );
} );
