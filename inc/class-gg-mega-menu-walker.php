<?php

if ( ! class_exists( 'GG_Mega_Menu_Walker' ) ) :

class GG_Mega_Menu_Walker extends Walker_Nav_Menu {

	public function start_lvl( &$output, $depth = 0, $args = null ) {
		if ( $depth === 0 ) {
			// Mega menu wrapper for the first submenu level
			// NOTE: keeping your existing markup structure to avoid CSS/JS changes.
			$output .= '<ul class="sub-menu mega-menu"><div class="mega-menu-inner">';
		} else {
			parent::start_lvl( $output, $depth, $args );
		}
	}

	public function end_lvl( &$output, $depth = 0, $args = null ) {
		if ( $depth === 0 ) {
			$output .= '</div></ul>';
		} else {
			parent::end_lvl( $output, $depth, $args );
		}
	}

	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {

		// ===== TOP LEVEL ITEM (PARENT) =====
		if ( $depth === 0 ) {

			$classes   = isset( $item->classes ) ? (array) $item->classes : array();
			$classes[] = 'menu-item';
			$classes[] = 'menu-item-depth-0';
			$classes[] = 'menu-item-has-mega';

			$class_names = implode(
				' ',
				array_map( 'sanitize_html_class', array_unique( $classes ) )
			);

			$output .= '<li class="' . esc_attr( $class_names ) . '">';
			$output .= '<a href="' . esc_url( $item->url ) . '">'
			           . esc_html( $item->title ) .
			           '</a>';

			return;
		}

		// ===== MEGA MENU ITEMS (FIRST SUBLEVEL) =====
		if ( $depth === 1 ) {

			/**
			 * 1) Menu-item fields (works for Pages AND Custom Links)
			 * Saved on the menu item itself (nav_menu_item post).
			 */
			$summary  = get_post_meta( $item->ID, '_ggmm_menu_item_summary', true );
			$image_id = (int) get_post_meta( $item->ID, '_ggmm_menu_item_image_id', true );

			$image_html = '';
			if ( $image_id ) {
				$image_html = wp_get_attachment_image(
					$image_id,
					'thumbnail',
					false,
					array( 'class' => 'mega-menu-icon' )
				);
			}

			/**
			 * 2) Fallbacks: if it's a Page item and menu-item fields are empty,
			 * use the page-level summary + featured image.
			 */
			if ( empty( $summary ) && $item->object === 'page' && ! empty( $item->object_id ) ) {
				$summary = get_post_meta( $item->object_id, '_gg_menu_summary', true );
			}

			if ( empty( $image_html ) && $item->object === 'page' && ! empty( $item->object_id ) && has_post_thumbnail( $item->object_id ) ) {
				$image_html = get_the_post_thumbnail(
					$item->object_id,
					'thumbnail',
					array( 'class' => 'mega-menu-icon' )
				);
			}

			$output .= '<li class="mega-menu-item">';

			// Icon
			if ( ! empty( $image_html ) ) {
				$output .= '<div class="mega-icon-wrapper">' . $image_html . '</div>';
			}

			// Text wrapper
			$output .= '<div class="mega-text-wrapper">';

			// Title link
			$output .= '<a href="' . esc_url( $item->url ) . '" class="mega-title">'
			           . esc_html( $item->title ) .
			           '</a>';

			// Summary text
			if ( ! empty( $summary ) ) {
				$output .= '<div class="mega-summary">' . esc_html( $summary ) . '</div>';
			}

			$output .= '</div>'; // .mega-text-wrapper
			$output .= '</li>';

			return;
		}

		// ===== FALLBACK FOR ANY DEEPER LEVELS =====
		parent::start_el( $output, $item, $depth, $args, $id );
	}

	public function end_el( &$output, $item, $depth = 0, $args = null ) {
		$output .= '</li>';
	}
}

endif;
