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
add_filter( 'wp_nav_menu_args', 'ggmm_inject_mega_menu_walker' );
function ggmm_inject_mega_menu_walker( $args ) {

	// You can tweak this array as needed per site.
	$mega_locations = array( 'primary', 'main-menu', 'header' );

	if ( isset( $args['theme_location'] ) && in_array( $args['theme_location'], $mega_locations, true ) ) {
		$args['walker'] = new GG_Mega_Menu_Walker();
	}

	return $args;
}
