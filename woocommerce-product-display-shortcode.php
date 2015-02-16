<?php
   /*
   Plugin Name: Woocommerce Product Display Shortcode
   Plugin URI: http://www.bobbiejwilson.com/woocommerce-product-display-shortcode
   Description: A plugin to add a shortcode for product display
   Version: 1.5
   Author: Bobbie Wilson
   Author URI: http://www.bobbiejwilson.com
   License: GPL2
   */
   /* Use it like the recent products shortcode from  Woocommerce
   [display_products per_page="4" columns="4" ids="1,2"]
   */

add_filter('add_to_cart_redirect', 'custom_add_to_cart_redirect');

function custom_add_to_cart_redirect() {
 global $woocommerce;
 $checkout_url = $woocommerce->cart->get_cart_url();
 return $checkout_url;
}
/**
 * Display Products shortcode
 *
 * @access public
 * @param array $atts
 * @return string
 */
function display_subscription_products( $atts ) {
	global $woocommerce_loop;

		if ( empty( $atts ) ) return '';

		extract( shortcode_atts( array(
			'columns' 	=> '4',
			'orderby'   => 'title',
			'order'     => 'asc'
		), $atts ) );

		$args = array(
			'post_type'				=> 'product',
			'post_status' 			=> 'publish',
			'ignore_sticky_posts'	=> 1,
			'orderby' 				=> $orderby,
			'order' 				=> $order,
			'posts_per_page' 		=> -1,
			'meta_query' 			=> array(
				array(
					'key' 		=> '_visibility',
					'value' 	=> array('catalog', 'visible'),
					'compare' 	=> 'IN'
				)
			)
		);

		if ( isset( $atts['skus'] ) ) {
			$skus = explode( ',', $atts['skus'] );
			$skus = array_map( 'trim', $skus );
			$args['meta_query'][] = array(
				'key' 		=> '_sku',
				'value' 	=> $skus,
				'compare' 	=> 'IN'
			);
		}

		if ( isset( $atts['ids'] ) ) {
			$ids = explode( ',', $atts['ids'] );
			$ids = array_map( 'trim', $ids );
			$args['post__in'] = $ids;
		}
	ob_start();

	$products = new WP_Query( apply_filters( 'woocommerce_shortcode_products_query', $args, $atts ) );

	$woocommerce_loop['columns'] = $columns;


	if ( $products->have_posts() ) : ?>

		<?php woocommerce_product_loop_start(); ?>

			<?php while ( $products->have_posts() ) : $products->the_post(); ?>
			

			<div itemscope itemtype="<?php echo woocommerce_get_product_schema(); ?>" id="product-<?php the_ID(); ?>" <?php post_class(); ?>>
				<div class="summary entry-summary">

				<?php remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 ); ?>
				<?php do_action( 'woocommerce_single_product_summary'); ?>
				<style>
				span.from {display: none;}
				.variations .label {display: none;}
				.single_variation .price {display: none;}
				.single_variation_wrap { display: block !important;}
				.single_variation_wrap .quantity { display: none;}
				</style>
				<script>
				jQuery('#style option[value=""]').text('Style');
				jQuery('#size option[value=""]').text('Size');
				</script>
				</div>
			</div>

			<?php endwhile; // end of the loop. ?>

		<?php woocommerce_product_loop_end(); ?>

	<?php endif;

	wp_reset_postdata();

	return '<div class="woocommerce columns-' . $columns . '">' . ob_get_clean() . '</div>';
	
}

add_shortcode('display_products','display_subscription_products');
?>