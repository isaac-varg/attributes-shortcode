<?php
/**
 * Plugin Name:       IsaacisVargas Attributes Shortcode
 * Plugin URI:        https://github.com/isaac-varg/attributes-shortcode
 * Description:       Displays a horizontal list of product attributes.
 * Version:           1.0
 * Requires at least: 5.2
 * Requires PHP:      7.0
 * Author:            Isaac Vargas
 * Author URI:        isaacvargas.me
 * Text Domain:       iiv_attributes_shortcode
 * Domain Path:       /languages
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function iiv_enqueue() {
	$plugin_url = plugin_dir_url( __FILE__ );

wp_enqueue_style( 'iiv_attributes_shortcode_style',  $plugin_url . "/src/style.css");
}

add_action( 'wp_enqueue_scripts', 'iiv_enqueue' );

function iiv_attributes_shortcode_translation() {

	load_plugin_textdomain( 'iiv_attributes_shortcode', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

}

add_action( 'init', 'iiv_attributes_shortcode_translation' );

require_once( ABSPATH . 'wp-admin/includes/plugin.php' );


if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {

function iiv_attributes_shortcode( $atts ) {

	// Shortcode attributes

	$atts = shortcode_atts(
		array(
			'attribute'			=> '',
			'orderby'			=> 'name',
			'order'				=> 'asc',
			'hide_empty'		=> 1, // Must be 1 not true
			'show_counts'		=> 0, // Must be 0 not false
			'archive_links'		=> 0, // Must be 0 not false
			'min_price'			=> '',
			'max_price'			=> '',
		),
		$atts,
		'iiv_attributes'
	);

	// get the id of the post/product
	$post_id = get_the_ID();

	// Start output

	$output = '';

	// Get attribute taxonomies

	$attribute_taxonomies = wc_get_attribute_taxonomies();

	if ( !empty( $attribute_taxonomies ) ) {

		// Loop taxonomies

		foreach ( $attribute_taxonomies as $taxonomy ) {

			// If attribute matches shortcode parameter

			if ( $atts['attribute'] == $taxonomy->attribute_name ) {

			

				// Set taxonomy id correctly so it can be used for get_terms() lookup

				$taxonomy_id = 'pa_' . $taxonomy->attribute_name;

				// Get terms

				$terms = get_the_terms($post_id, $taxonomy_id);


				// If terms exist

				if ( !empty( $terms ) ) {

					// Output the list

					$output .= '<ul class="iiv-product-attributes" id="iiv-product-attributes-' . esc_attr( $taxonomy_id ) . '">';

					// index of run
					$x = 1;

					// look through terms
					foreach ( $terms as $term ) {

						$output .= '<li>';

						if ( $atts['archive_links'] == 0 ) {

							$href = get_permalink( wc_get_page_id( 'shop' ) ) . '?filter_' . $taxonomy->attribute_name . '=' . $term->slug;

							if ( '' !== $atts['min_price'] ) {

								$href .= '&min_price=' . $atts['min_price'];

							}

							if ( '' !== $atts['max_price'] ) {

								$href .= '&max_price=' . $atts['max_price'];

							}

							$output .= '<a href="' . esc_url( $href ) . '">' . wp_kses_post( $term->name ) . '</a>';

						} else {

							if ( $taxonomy->attribute_public == 1 ) {

								$href = get_term_link( $term );
								$output .= '<a href="' . esc_url( $href ) . '">' . wp_kses_post( $term->name ) . '</a>';

							} else {

								$output .= wp_kses_post( $term->name );

							}

						}

						if ( $atts['show_counts'] == 1 ) {
							
							$output .= ' ' . esc_html__( '(', 'iiv-product-attributes-shortcode' ) . wp_kses_post( $term->count ) . esc_html__( ')', 'iiv-product-attributes-shortcode' );

						}
						
						if ($x != count($terms) ) {
							$output .= ', ';
						}
						$output .= '</li>';
						$x++;

					}

					$output .= '</ul>';

				}

			}

		}

	}

	return wp_kses_post( $output );
	}
add_shortcode( 'iiv_attributes', 'iiv_attributes_shortcode' );

} else {

add_action( 'admin_notices', function() {

	if ( current_user_can( 'edit_plugins' ) ) {

		?>

		<div class="notice notice-error">
			<p><strong><?php esc_html_e( 'Product Attributes Shortcode requires WooCommerce to be installed and activated.', 'iiv-product-attributes-shortcode' ); ?></strong></p>
		</div>

		<?php

	}

});

}