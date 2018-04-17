<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Rapid_Order_Product {

	/**
	 * @var WC_Rapid_Order_Cart
	 */
	protected $cart;

	public function __construct() {
		add_action( 'woocommerce_add_order_item_meta', array( $this, 'add_order_item_meta' ), 10, 2 );
		add_filter( 'woocommerce_get_item_data', array( $this, 'get_item_data' ), 10, 2 );
	}

	/**
	 * @param WC_Rapid_Order_Cart $cart
	 */
	public function set_cart( $cart ) {
		$this->cart = $cart;
	}

	/**
	 * This method is responsible for taking a product in the loop, and formatting it for
	 * consumption for the javascript code that displays our products on the frontend.
	 *
	 * @return array An array containing the keys 'next_page' and 'products'
	 */
	public function the_product() {
		$this->thumb_size = apply_filters( 'wcro_thumbnail_size', array( 90, 90 ) );
		$product          = wc_get_product();
		$hash             = $this->get_hash( $product );

		$item = WC()->cart->get_cart_item($hash);
		$total = $this->get_subtotal($item);

		if(function_exists('wc_get_price_to_display')){
			$price = $item ? wc_get_price_to_display($product) : $this->get_price($product);
		} else {
			$price = $item ? $item['data']->get_display_price() : $this->get_price($product);
		}

		$adjusters = array();
		$full = wc_get_product_attachment_props( $product->get_image_id() );
		$item = array(
			'excerpt'          => $this->get_short_description($product),
			'image'            => array( 'src' => $product->get_image( $this->get_product_thumbail_size() ), 'full' => isset($full['url']) ? $full['url'] : false ),
			'title'            => $product->get_title(),
			'price_html'       => $product->get_price_html(),
			'quantity_html'    => $this->get_quantity_html( $product ),
			'in_stock'         => $product->is_in_stock(),
			'id'               => $product->get_id(),
			'type'             => $product->get_type(),
			'price'            => (float) $price,
			'variations'       => array(),
			'cart_item_hash'   => $hash,
			'cart_item_total'  => round($total, wc_get_price_decimals(), PHP_ROUND_HALF_UP),
			'in_cart_quantity' => $this->cart->get_item_quantity( $hash ),
			'permalink'        => get_the_permalink(),
			'on_sale'          => $product->is_on_sale(),
			'featured'         => $product->is_featured(),
			'stars'            => (int) $product->get_review_count(),
			'price_adjusters'  => apply_filters( 'wcro_price_adjusters', $adjusters ),
		);

		$products[] = apply_filters( 'wcro_loop_single_item', $item, $product );

		foreach ( $product->get_children() as $variation_id ) {
			$variation = new WC_Product_Variation( $variation_id );

			// Don't show this variation if we don't want out of stock items displayed
			if(!$variation->is_in_stock() && 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ))
				continue;

			$hash = $this->get_hash( $product, $variation_id );

			$item  = WC()->cart->get_cart_item( $hash );
			$total = $this->get_subtotal($item);

			if(function_exists('wc_get_price_to_display')){
				$price = $item ? wc_get_price_to_display($variation) : $this->get_price($variation);
			} else {
				$price = $item ? $item['data']->get_display_price() : $this->get_price( $variation );
			}

			// Format the variation title.
			$title = apply_filters(
				'wcro_variation_title',
				$this->format_variation_attributes( $variation ),
				$variation
			);

			$full = wc_get_product_attachment_props( $variation->get_image_id() );
			$item      = array(
				'excerpt'          => $this->get_variation_short_description($variation),
				'image'            => array( 'src' => $variation->get_image( $this->get_product_thumbail_size() ), 'full' => isset($full['url']) ? $full['url'] : false ),
				'title'            => $title,
				'price_html'       => $variation->get_price_html(),
				'quantity_html'    => $this->get_quantity_html( $variation, $variation_id ),
				'in_stock'         => $variation->is_in_stock(),
				'id'               => $variation_id,
				'type'             => $variation->get_type(),
				'price'            => (float) $price,
				'cart_item_hash'   => $hash,
				'cart_item_total'  => round($total, wc_get_price_decimals(), PHP_ROUND_HALF_UP),
				'in_cart_quantity' => $this->cart->get_item_quantity( $hash ),
				'permalink'        => get_the_permalink(),
				'on_sale'          => $variation->is_on_sale(),
				'featured'         => $variation->is_featured(),
				'stars'            => (int) $variation->get_review_count(),
			);

			$products[] = apply_filters( 'wcro_loop_single_item', $item, $variation );
		}

		global $wp_query, $post;
		$next_page = html_entity_decode( get_next_posts_page_link( $wp_query->max_num_pages ) );
		$next_page = $next_page ? add_query_arg( 'wcro-ajax', '1', $next_page ) : '';

		$next_page = apply_filters('wcro_next_page', $next_page);

		$data = compact( 'next_page', 'products' );

		return apply_filters( 'wcro_loop_products', $data );
	}

	/**
	 * @param WC_Product $product
	 */
	public function get_short_description( $product ) {
		if(wcro_wc_version_compare('3.0.0', '<')){
			return apply_filters( 'woocommerce_short_description', strip_tags($product->get_post_data()->post_excerpt) );
		}

		return strip_tags($product->get_short_description());
	}

	/**
	 * @param WC_Product_Variation $variation
	 */
	public function get_variation_short_description( $variation ) {
		if(wcro_wc_version_compare('3.0.0', '<')){
			return strip_tags($variation->get_variation_description()) ?: apply_filters( 'woocommerce_short_description', strip_tags($variation->get_post_data()->post_excerpt) );
		}

		return strip_tags($variation->get_description());
	}

	public function get_subtotal($item){
		if(!$item) return 0;
		if( wc_tax_enabled() && 'incl' === get_option( 'woocommerce_tax_display_shop' ) && wc_prices_include_tax()){
			return $item['data']->get_price_including_tax($item['quantity']);
		} else {
			return $item['line_subtotal'];
		}
	}

	/**
	 * Get the cart hash for a product
	 *
	 * @param WC_Product $product
	 * @param null|int $variation_id
	 * @param array $cart_item_data
	 *
	 * @return string
	 */
	public function get_hash( $product, $variation_id = null, $cart_item_data = array() ) {
		// Use the variation ID over the parent
		$id = $variation_id ? $variation_id : $product->get_id();

		$extra          = $this->cart->cart_item_has_property( 'wcro-sub', $id );
		$cart_item_data = array_merge( $extra, $cart_item_data );

		$atts = null;
		if ( $variation_id ) {
			$variation = new WC_Product_Variation( $variation_id );

			global $woocommerce;
			if( version_compare( $woocommerce->version, 3.0, ">=" ) ) {
				$parent = $variation->get_parent_id();
			} else {
				$parent = $variation->get_parent();
			}

			$product   = wc_get_product( $parent );
			$atts      = $variation->get_variation_attributes();
		}

		return apply_filters( 'wcro_hash', $this->cart->get_id( $product, $variation_id, $atts, $cart_item_data ), $product, $variation_id );
	}

	/**
	 * This function adds support for the Dynamic pricing plugin.
	 *
	 * @param null|WC_Product $product
	 *
	 * @return string
	 */
	public function get_price( $product = null ) {
		$product = $product ? $product : wc_get_product();

		if(class_exists('WC_Dynamic_Pricing')){
			return WC_Dynamic_Pricing::instance()->on_get_price($product->get_price(), $product, true);
		}

		if(function_exists('wc_get_price_to_display')){
			return wc_get_price_to_display($product);
		}

		return $product->get_display_price();
	}

	/**
	 * Get the product thumbnail size.
	 * @todo make this configurable in the admin
	 *
	 * @return mixed|void
	 */
	public function get_product_thumbail_size() {
		return apply_filters( 'wcro_thumbnail_size', array( 100, 100 ) );
	}

	/**
	 * Gets the quantity html for a given product. It also populates the
	 * quantity value and hash if it's currently in the users cart.
	 *
	 * @param WC_Product|WC_Product_Variable $product
	 *
	 * @return string
	 */
	public function get_quantity_html( $product, $variation_id = null ) {
		if(!$product->is_in_stock()){
			$availability      = $product->get_availability();
			$availability_html = empty( $availability['availability'] ) ? '' : '<span class="stock ' . esc_attr( $availability['class'] ) . '">' . esc_html( $availability['availability'] ) . '</span>';
			$html = apply_filters( 'woocommerce_stock_html', $availability_html, $availability['availability'], $product );
			return $html;
		}

		$id = $variation_id ? $variation_id : $product->get_id();
		$extra = $this->cart->cart_item_has_property( 'wcro-sub', $id );

		$hash    = $this->get_hash( $product, $variation_id, $extra );
		$in_cart = $this->cart->get_item_quantity( $hash );

		$args = array(
			'min_value'   => apply_filters( 'woocommerce_quantity_input_min', 1, $product ),
			'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product->backorders_allowed() ? '' : $product->get_stock_quantity(), $product ),
			'input_value' => $in_cart,
		);

		$html = woocommerce_quantity_input( $args, $product, false );
		$html .= '<input type="hidden" name="product_id" value="' . esc_attr( $product->get_id() ) . '" />';

		if ( $product->is_type( 'variation' ) ) {
			$html .= '<input type="hidden" name="variation_id" value="' . $variation_id . '" />';
			foreach ( $product->get_variation_attributes() as $attr_name => $attr_value ) {
				$html .= '<input type="hidden" name="variation_atts[' . sanitize_title( $attr_name ) . ']" value="' . $attr_value . '">';
				if ( 'attribute_type' == $attr_name ) {
					$html .= '<input type = "hidden" name = "attribute_type" value = "' . $attr_value . '" >';
				}
			}
		}

		$sub_val = ! empty( $extra['wcro-sub'] ) ? 'yes' : 'no';
		$html .= '<input type="hidden" name="wcro-sub" value="' . $sub_val . '">';
		$html .= '<input type="hidden" name="update_cart" value="1">';

		return apply_filters( 'wcro_quantity_html', $html );
	}

	/**
	 * @param $item_data
	 * @param $cart_item
	 *
	 * @return array
	 */
	public function get_item_data( $item_data, $cart_item ) {
		if ( ! empty( $cart_item['wcro-sub'] ) ) {
			$item_data[] = array(
				'key'     => get_option( 'wcro_dont_sub_text', __( "Don't Sub", WC_Rapid_Order::TEXT_DOMAIN ) ),
				'display' => __( 'checked', WC_Rapid_Order::TEXT_DOMAIN ),
			);
		}

		return $item_data;
	}


	/**
	 * Gets the title for a given product. If it's a variation,
	 * we append the variations attributes to the title.
	 *
	 * @param string $title
	 *
	 * @return string
	 */
	public function the_title( $title ) {
		global $product;

		if ( method_exists( $product, 'is_type' ) && $product->is_type( 'variation' ) ) {
			$title .= ' - ' . implode( '/', $product->get_variation_attributes() );
		}

		return $title;
	}

	public function add_order_item_meta( $item_id, $values ) {
		if ( ! empty( $values['wcro-sub'] ) ) {
			wc_add_order_item_meta( $item_id, $values['wcro-sub']['display'], __( 'checked', WC_Rapid_Order::TEXT_DOMAIN ) );
		}
	}

	/**
	 * Get the variation attribute name
	 *
	 * @param WC_Product_Variation $variation
	 *
	 * @return string The formatted variation name
	 */
	public function format_variation_attributes( $variation ) {
		if(wcro_wc_version_compare('3.0.0', '>=')){
			return $variation->get_name();
		}

		return $variation->get_title() . $this->format_variation_attributes_pre_v3($variation);
	}

	/**
	 * This recreates the WC method get_formatted_variation_attributes() but without the attribute name.
	 *
	 * @param WC_Product_Variation $variation
	 *
	 * @deprecated
	 * @return string The formatted variation name
	 */
	public function format_variation_attributes_pre_v3( $variation ) {
		$variation_data = $variation->get_variation_attributes();
		$attributes     = $variation->parent->get_attributes();
		$return         = '';

		if ( is_array( $variation_data ) ) {
			foreach ( $attributes as $attribute ) {

				// Only deal with attributes that are variations
				if ( ! $attribute['is_variation'] ) {
					continue;
				}

				$variation_selected_value = isset( $variation_data[ 'attribute_' . sanitize_title( $attribute['name'] ) ] ) ? $variation_data[ 'attribute_' . sanitize_title( $attribute['name'] ) ] : '';

				// Get terms for attribute taxonomy or value if its a custom attribute
				if ( $attribute['is_taxonomy'] ) {

					$post_terms = wp_get_post_terms( $variation->get_id(), $attribute['name'] );

					foreach ( $post_terms as $term ) {
						if ( $variation_selected_value === $term->slug ) {
							$return .= esc_html( apply_filters( 'woocommerce_variation_option_name', $term->name ) ) . ' ';
						}
					}

				} else {

					$options = wc_get_text_attributes( $attribute['value'] );

					foreach ( $options as $option ) {

						if ( sanitize_title( $variation_selected_value ) === $variation_selected_value ) {
							if ( $variation_selected_value !== sanitize_title( $option ) ) {
								continue;
							}
						} else {
							if ( $variation_selected_value !== $option ) {
								continue;
							}
						}

						$return .= esc_html( apply_filters( 'woocommerce_variation_option_name', $option ) ). ' ';
					}
				}
			}
		}

		return $return ? ' - ' . trim($return) : '';
	}
}
