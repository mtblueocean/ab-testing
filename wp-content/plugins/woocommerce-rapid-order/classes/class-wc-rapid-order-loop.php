<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Rapid_Order_Loop {

	/**
	 * @var WC_Rapid_Order_Product
	 */
	protected $product;

	/**
	 * @var array
	 */
	protected $products = array();

	/**
	 * The next page in the loop
	 * @var string
	 */
	protected $next_page;

	public function __construct( $product ) {
		$this->product = $product;
		add_action( 'get_header', array( $this, 'get_header' ), 9 );

		// This allows us to call this from other classes.
		add_action( 'wcro_loop_process_ajax_request', array( $this, 'process_ajax_request' ) );
	}

	public function get_header() {
		if ( wcro_is_ajax() ) {
			$this->process_ajax_request();
		}
	}

	/**
	 * This is will send the contents out as JSON if it's an ajax request.
	 */
	public function process_ajax_request() {
		$return_first = isset( $_GET['single'] ) && $_GET['single'] ? true : false;

		// the_products() will detect this is ajax and send the outputs as json.
		// We do this here before any headers have been sent.
		if ( have_posts() ) {
			while ( have_posts() ) : the_post();
				$this->the_product();
			endwhile;
		}

		if ( $return_first ) {
			wp_send_json( $this->get_first_item() );
		} else {
			wp_send_json( $this->get_loop_items() );
		}
	}

	/**
	 * Fetches the product formatted for our backbone application and stores it in the array.
	 */
	public function the_product() {
		extract( $this->product->the_product() );
		$this->products  = array_merge( $this->products, $products );
		$this->next_page = $next_page;
	}

	public function get_first_item() {
		if ( isset( $this->products[0] ) ) {
			return $this->products[0];
		} else {
			return array();
		}
	}

	/**
	 * @return array Returns a formatted response for our backbone application
	 */
	public function get_loop_items() {
		return array(
			'products'  => $this->products,
			'next_page' => $this->next_page,
		);
	}
}
