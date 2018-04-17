<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Rapid_Order_AJAX {

	public function __construct() {
		$this->register_ajax_methods();
	}

	/**
	 * This uses methods names in this class that starts with
	 * wcro and registers those as usable ajax names
	 */
	public function register_ajax_methods() {
		$reflection = new ReflectionClass( get_class( $this ) );
		foreach ( $reflection->getMethods() as $method ) {
			if ( strpos( $method->name, 'wcro' ) === 0 ) {
				$r = new ReflectionMethod( get_class( $this ), $method->name );
				add_action( 'wc_ajax_' . $method->name, array(
					$this,
					$method->name,
				), 10, count( $r->getParameters() ) );
			}
		}
	}

	/**
	 * AJAX add to cart. This closely mimicks the WooCommerce ajax add to cart function with the difference that it
	 * returns the token of the item we just added to the cart (as opposed to returning the entire cart hash).
	 */
	public function wcro_add_to_cart() {
		global $woocommerce;
		ob_start();
		
		$product_id        = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $_POST['product_id'] ) );
		$quantity          = empty( $_POST['quantity'] ) ? 1 : wc_stock_amount( $_POST['quantity'] );
		$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity );
		$product_status    = get_post_status( $product_id );
		$variation_id      = empty( $_POST['variation_id'] ) ? 0 : $_POST['variation_id'];
		$variation         = empty( $_POST['variation_atts'] ) ? array() : $_POST['variation_atts'];

		if ( $passed_validation &&
		     ( $hash = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation ) ) &&
		     'publish' === $product_status
		) {

			do_action( 'woocommerce_ajax_added_to_cart', $product_id );
			
			// Even though this is an add to cart, our system really treats this more like a cart update
			do_action('woocommerce_update_cart_action_cart_updated'); 

			// Return fragments
			$item = $woocommerce->cart->get_cart_item( $hash );

			$total = 0;
			if( $item && wc_tax_enabled() && 'incl' === get_option( 'woocommerce_tax_display_shop' ) ){
				$total = $item['data']->get_price_including_tax($_POST['quantity']);
			} elseif ($item) {
				$total = $item['line_subtotal'];
			}

			wp_send_json( array(
					'success' => true,
					'total' => $total,
					'price' => isset($item['data']) ? $this->get_display_price($item['data']) : 0,
					'discount' => 0,//TODO fill this in with dynamic price discount
					'cart_item_hash' => $hash 
			) );

		} else {

			// If there was an error adding to the cart, redirect to the product page to show any errors
			$data = array(
				'error'       => true,
				'product_url' => apply_filters(
					'woocommerce_cart_redirect_after_error',
					get_permalink( $product_id ),
					$product_id
				),
			);

			wp_send_json( $data );
		}

		die();
	}

	/**
	 * Used for updating a specific item in the cart. Generally used for quantity changes.
	 */
	public static function wcro_update_cart() {
		WC()->cart->set_quantity( $_POST['cart_item_key'], $_POST['quantity'] );
		
		do_action('woocommerce_update_cart_action_cart_updated');

		WC()->cart->calculate_totals();
		
		$item = WC()->cart->get_cart_item( $_POST['cart_item_key'] );

		$total = 0;
		
		if( $item && wc_tax_enabled() && 'incl' === get_option( 'woocommerce_tax_display_shop' ) ){
			$total = self::get_price_including_tax($item['data'], $_POST['quantity']);
		} elseif ($item) {
			$total = $item['line_subtotal'];
		}
		
		$data = array(
				'success'  => true,
				'total'    => $total,
				'price'    => isset( $item['data'] ) ? self::get_display_price($item['data']) : 0,
				'discount' => 0,
			//TODO fill this in with dynamic price discount
//				'discount' => isset($item['discounts']['display_price']) ? $item['discounts']['display_price']*$item['quantity'] - $item['line_total'] : 0,
		);

		if(defined('WP_DEBUG') && WP_DEBUG) $data['item'] = $item;
		
		wp_send_json( $data );
	}

	public function wcro_fetch_products() {
		global $wp_query;
		$ids = explode( ',', wc_clean( $_GET['product_ids'] ) );

		$args = array(
			'post_type'      => array( 'product', 'product_variation' ),
			'post__in'       => $ids,
			'paged'          => get_query_var( 'paged' ),
			'posts_per_page' => apply_filters( 'loop_shop_per_page', get_option( 'posts_per_page' ) ),
		);

		$wp_query = new WP_Query( $args );

		// our loop class will pick things up from here and render everything the way we want it.
		do_action( 'wcro_loop_process_ajax_request' );
	}

	/**
	 * @param WC_Product $product
	 * @param int $qty
	 *
	 * @return float
	 */
	public static function get_price_including_tax( $product, $qty = 1 ) {
		if(function_exists('wc_get_price_including_tax')){
			return wc_get_price_including_tax($product, array('qty' => $qty));
		} else {
			return $product->get_price_including_tax($qty);
		}
	}

	/**
	 * @param WC_Product $product
	 *
	 * @return float
	 */
	public static function get_display_price( $product ) {
		if(function_exists('wc_get_price_to_display')){
			return wc_get_price_to_display($product);
		} else {
			return $product->get_display_price();
		}
	}
}
