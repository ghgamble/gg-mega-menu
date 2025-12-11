<?php

if ( ! class_exists( 'GG_Mega_Menu_Walker' ) ) :

class GG_Mega_Menu_Walker extends Walker_Nav_Menu {

	public function start_lvl( &$output, $depth = 0, $args = null ) {
		if ( $depth === 0 ) {
			// Our mega menu wrapper for the first submenu level
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

			// Summary from custom field on target page
			$summary = '';
			if ( $item->object === 'page' && $item->object_id ) {
				$summary = get_post_meta( $item->object_id, '_gg_menu_summary', true );
			}

			// Featured image (icon) for the page
			$image_html = '';
			if ( $item->object === 'page' && has_post_thumbnail( $item->object_id ) ) {
				$image_html = get_the_post_thumbnail(
					$item->object_id,
					'thumbnail',
					array( 'class' => 'mega-menu-icon' )
				);
			}

			$output .= '<li class="mega-menu-item">';

			// Icon
			if ( $image_html ) {
				$output .= '<div class="mega-icon-wrapper">' . $image_html . '</div>';
			}

			// Text wrapper
			$output .= '<div class="mega-text-wrapper">';

			// Title link
			$output .= '<a href="' . esc_url( $item->url ) . '" class="mega-title">'
			           . esc_html( $item->title ) .
			           '</a>';

			// Summary text
			if ( $summary ) {
				$output .= '<div class="mega-summary">' . esc_html( $summary ) . '</div>';
			}

			$output .= '</div>'; // .mega-text-wrapper
			$output .= '</li>';

			return;
		}

		// ===== FALLBACK FOR DEEPER LEVELS =====
		parent::start_el( $output, $item, $depth, $args, $id );
	}

	public function end_el( &$output, $item, $depth = 0, $args = null ) {
		$output .= '</li>';
	}
}

endif;
