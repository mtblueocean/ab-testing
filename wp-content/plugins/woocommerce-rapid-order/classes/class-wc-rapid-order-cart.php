<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Rapid_Order_Cart {

	/**
	 * The contents of the cart
	 *
	 * @var array $cart
	 */
	protected $cart;

	/**
	 * Our product helper class
	 *
	 * @var WC_Rapid_Order_Product
	 */
	protected $product;

	public function __construct( WC_Rapid_Order_Product $product ) {
		$this->product = $product;

		add_filter( 'woocommerce_add_cart_item_data', array( $this, 'add_cart_item_data' ), 10, 3 );

		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.3', '>=' ) ) {
			add_filter( 'woocommerce_add_to_cart_fragments', array( $this, 'cart_total_fragment' ) );
		} else {
			add_filter( 'add_to_cart_fragments', array( $this, 'cart_total_fragment' ) );
		}
	}

	public function get_id( $product, $variation_id = 0, $variation = array(), $cart_item_data = array() ) {
		$cart_item_data = (array) apply_filters( 'woocommerce_add_cart_item_data', $cart_item_data, $product->get_id(), $variation_id );

		$cart_id = WC()->cart->generate_cart_id( $product->get_id(), $variation_id, $variation, $cart_item_data );

		return WC()->cart->find_product_in_cart( $cart_id );
	}

	public function get_item_quantity( $cart_item_hash ) {
		if ( ! $cart_item_hash ) {
			return 0;
		}
		
		$contents = $this->get_cart_contents();

		return (float) ( isset( $contents[ $cart_item_hash ] ) ? $contents[ $cart_item_hash ]['quantity'] : 0 );
	}

	public function get_cart_contents() {
		if ( ! $this->cart && WC()->cart ) {
			$this->cart = WC()->cart->get_cart();
		}

		return $this->cart;
	}

	public function cart_item_has_property( $property, $product_id ) {
		$items = array_map( function ( $product ) use ( $property ) {
			return isset( $product[ $property ] ) ? $product : false;
		}, $this->get_cart_contents() );
		
		$extra = array();
		foreach ( $items as $item ) {
			$id = $item['variation_id'] ? $item['variation_id'] : $item['product_id'];
			if ( $product_id == $id && ! empty( $item[ $property ] ) ) {
				$extra[ $property ] = $item[ $property ];
				break;
			}
		}

		return $extra;
	}

	/**
	 * @param array $cart_item_data
	 *
	 * @return array
	 */
	public function add_cart_item_data( $cart_item_data ) {
		if ( ! empty( $_POST['wcro-sub'] ) && 'yes' == $_POST['wcro-sub'] ) {
			$cart_item_data['wcro-sub'] = array(
				'display' => get_option( 'wcro_dont_sub_text', __( "Don't Sub", WC_Rapid_Order::TEXT_DOMAIN ) ),
			);
		}

		return $cart_item_data;
	}

	public function cart_total_fragment( $fragments ) {
		// We need to use ob_clean since IE seems to fail when just trying to return wcro_cart_total() into the array.
		ob_start();
		echo wcro_cart_total();
		$fragments['.wcro_cart_total'] = ob_get_clean();

		// This allows us to update the displayed price each time an item is added/removed from the cart.
		// This is necessary for dynamic pricing style plugins support. 
		
		// Here we are updating removed items from the cart. This resets the price html. 
		// TODO leaving this commented out for now. causing some issues with inital price.
//		$fragments = array_merge($fragments, $this->get_items_price_fragment(WC()->cart->removed_cart_contents));
		
		// Here we are updating all the cart items' prices 
		$fragments = array_merge($fragments, $this->get_items_price_fragment(WC()->cart->get_cart()));
		
		return $fragments;
	}
	
	private function get_items_price_fragment($items){
		$fragments = array();
		foreach($items as $cart_item_key => $cart_item){
			if(!isset($cart_item['product_id'])) continue;
			
			$cart_item['data'] = !isset($cart_item['data']) ? wc_get_product($cart_item['product_id']) : $cart_item['data'];
			
			$product_price = $cart_item['data']->get_price_html();

			$targetEl = '#wcro_item_'.$cart_item['product_id'].' .wcro_price_contents';
			$fragments[$targetEl] = '<div class="wcro_price_contents">'.$product_price.'</div>';
		}
		
		return $fragments;
	}
}
