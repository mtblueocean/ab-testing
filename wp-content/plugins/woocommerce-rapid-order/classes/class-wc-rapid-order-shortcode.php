<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Rapid_Order_Shortcode {

	protected static $_instance = null;

	protected $is_showing = false;

	protected $attributes = array();

	public function __construct() {
		add_action( 'widgets_init', array( $this, 'remove_widget_conflicts' ), 1 );
		add_shortcode( 'rapidorder', array( $this, 'shortcode' ) );
		add_action( 'pre_get_posts', array( $this, 'override_post_paging' ) );
		add_filter( 'wcro_next_page', array( $this, 'next_page' ) );

		if ( ! empty( $_REQUEST['wcro-atts'] ) ) {
			$this->attributes = $_REQUEST['wcro-atts'];
		}
	}

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Output our rapidorder shortcode. Please note only one rapidorder shortcode is allowed per page.
	 *
	 * @param array $atts
	 *
	 * @return string|void
	 */
	public function shortcode( $atts ) {
		// We can only have one instance of Rapid Order per page.
		if ( $this->is_showing ) {
			return;
		}

		$this->is_showing = true;

		$options = shortcode_atts( array(
			'categories'         => null,
			'tags'               => null,
			'show_sort'          => true,
			'ids'                => null,
			'previously_ordered' => false,
		), $atts );

		$meta_query = WC()->query->get_meta_query();

		if ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) ) {
			$meta_query[] = array(
				'key'     => '_stock_status',
				'value'   => 'instock',
				'compare' => '=',
			);
		}

		$args = array(
			'post_status'         => 'publish',
			'post_type'           => 'product',
			'ignore_sticky_posts' => 1,
			'meta_query'          => $meta_query,
		);

		if ( ! empty( $options['ids'] ) ) {
			$args['post__in'] = array_map( 'trim', explode( ',', $options['ids'] ) );
		}

		$args['product_cat'] = trim( $options['categories'] );
		$args['product_tag'] = trim( $options['tags'] );

		// This makes sure any ordering parameters in the url are set properly in the query.
		$sorting = WC()->query->get_catalog_ordering_args();

		$args = array_merge( $args, $sorting );
		$args = apply_filters( 'wcro_modify_shortcode_args', $args );

		$orderedIds = array();
		if ( $options['previously_ordered'] ) {
			$orderedIds       = $this->customer_products();
			$args['post__in'] = $orderedIds;
		}

		$loop = new WP_Query( $args );

		// Store these for later use by our next_page method
		$this->attributes = $args;

		ob_start();

		if ( ! $loop->have_posts() || ( $options['previously_ordered'] && ! $orderedIds ) ) {
			echo __( 'No products found' );
		} else if ( $loop->have_posts() ) {

			if ( $options['show_sort'] ) {
				echo '<div class="storefront-sorting">';
				$this->woocommerce_catalog_ordering();
				echo "</div>";
			}

			require_once __DIR__ . "/../templates/woocommerce/loop/loop-start.php";

			while ( $loop->have_posts() ) : $loop->the_post();
				WC_Rapid_Order::instance()->loop->the_product();
			endwhile;

			require_once __DIR__ . "/../templates/woocommerce/loop/loop-end.php";
		} else {
			echo __( 'No products found' );
		}

		$content = ob_get_contents();
		ob_end_clean();

		wp_reset_postdata();

		return $content;
	}

	public function customer_products() {
		if ( ! is_user_logged_in() ) {
			return array();
		}

		$customer_orders = get_posts( array(
			'numberposts' => - 1,
			'meta_key'    => '_customer_user',
			'meta_value'  => get_current_user_id(),
			'post_type'   => wc_get_order_types(),
			'post_status' => 'wc-completed',
		) );

		$ids = array();
		foreach ( $customer_orders as $order ) {
			$order = new WC_Order( $order->ID );
			$items = $order->get_items();
			foreach ( $items as $item ) {
				$ids[] = $item['product_id'];
			}
		}

		return $ids;
	}

	/**
	 * If this is an paging ajax request from a shortcode, we need to hijack the normal loop
	 * and output products instead. do_action will output a json response of products.
	 *
	 * @param WP_Query $query
	 */
	public function override_post_paging( $query ) {
		if ( wcro_is_ajax() && ! empty( $_REQUEST['wcro-shortcode'] ) ) {
			// Let's hijack this query and check for more products

			// prevent infinite loop
			remove_filter( 'pre_get_posts', array( $this, 'override_post_paging' ) );


			$page = ! empty( $_REQUEST['wcro-page'] ) ? $_REQUEST['wcro-page'] : 1;

			$args          = $this->attributes;
			$args['paged'] = $page;

			// We need to set this here again so that WC can attach to appropriate hooks/filters
			WC()->query->get_catalog_ordering_args();

			// yes, we want to alter the main query here since 'wcro_loop_process_ajax_request' will use it
			query_posts( $args );

			// this will output the json then exit
			do_action( 'wcro_loop_process_ajax_request' );
			exit;
		}
	}

	/**
	 * Modifies the next page value for usage in shortcode infinite scroll
	 *
	 * @param string $next_page
	 *
	 * @return string The next page URL to be used by shortcode infinite scroll
	 */
	public function next_page( $next_page ) {
		// Modify only if this is an ajax page request from a shortcode
		if ( $this->is_showing || ! empty( $_REQUEST['wcro-shortcode'] ) ) {
			$page = ! empty( $_REQUEST['wcro-page'] ) ? $_REQUEST['wcro-page'] : 1;

			$next_page = add_query_arg( 'wcro-shortcode', '1', $next_page );
			$next_page = add_query_arg( 'wcro-page', $page + 1, $next_page );
			$next_page = add_query_arg( 'wcro-atts', $this->attributes, $next_page );
		}

		return $next_page;
	}

	/**
	 * We has to replicates WC's function here since they do an inital check that doesn't return true when not using the main loop.
	 */
	public function woocommerce_catalog_ordering() {
		$orderby                 = isset( $_GET['orderby'] ) ? wc_clean( $_GET['orderby'] ) : apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby' ) );
		$show_default_orderby    = 'menu_order' === apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby' ) );
		$catalog_orderby_options = apply_filters( 'woocommerce_catalog_orderby', array(
			'menu_order' => __( 'Default sorting', 'woocommerce' ),
			'popularity' => __( 'Sort by popularity', 'woocommerce' ),
			'rating'     => __( 'Sort by average rating', 'woocommerce' ),
			'date'       => __( 'Sort by newness', 'woocommerce' ),
			'price'      => __( 'Sort by price: low to high', 'woocommerce' ),
			'price-desc' => __( 'Sort by price: high to low', 'woocommerce' )
		) );

		if ( ! $show_default_orderby ) {
			unset( $catalog_orderby_options['menu_order'] );
		}

		if ( 'no' === get_option( 'woocommerce_enable_review_rating' ) ) {
			unset( $catalog_orderby_options['rating'] );
		}

		wc_get_template( 'loop/orderby.php', array(
			'catalog_orderby_options' => $catalog_orderby_options,
			'orderby'                 => $orderby,
			'show_default_orderby'    => $show_default_orderby
		) );
	}

	public function remove_widget_conflicts() {
		global $woosidebars;

		// Woosidebars was causing a bunch of errors on infinite scroll on shortcode pages.
		// Not totally sure why it was happening, but this currently seems to fix the issue.
		if ( $woosidebars && wcro_is_ajax() && ! empty( $_REQUEST['wcro-shortcode'] ) ) {
			remove_action( 'widgets_init', array( $woosidebars, 'register_custom_sidebars' ) );
		}
	}
}
