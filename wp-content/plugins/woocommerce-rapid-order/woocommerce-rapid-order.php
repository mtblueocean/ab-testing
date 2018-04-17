<?php
/**
 * Plugin Name: Rapid Order for WooCommerce
 * Plugin URI: http://rapidorderplugin.com
 * Description: A fast wholesale order form for WooCommerce.
 * Version: 2.2.interpetiv
 * Author: Nicholas Verwymeren
 * Author URI: https://www.nickv.codes
 * Developer: Nick Verwymeren
 * Developer URI: https://www.nickv.codes
 * Text Domain: woocommerce-rapid-order
 * Domain Path: /languages
 *
 * Copyright: © 2017 Nicholas Verwymeren.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

define('WCRO_VERSION', '2.2');

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Boostrap our woocommerce plugin helper
require_once( 'lib/greatwitenorth/wchelper/class-gwn-wch-plugin-base.php' );

class WC_Rapid_Order extends GWN_WCH_Plugin_Base {

	const TEXT_DOMAIN = 'woocommerce-rapid-order';

	protected static $_instance = null;

	/**
	 * Set this to false and no template overriding will occur
	 *
	 * @var bool
	 */
	public $override_templates = false;
	/**
	 * @var WC_Rapid_Order_Product
	 */
	public $product;
	/**
	 * @var WC_Rapid_Order_Loop $loop
	 */
	public $loop;
	/**
	 * @var WC_Rapid_Order_Cart
	 */
	protected $cart;
	/**
	 * @var WC_Rapid_Order_AJAX
	 */
	protected $ajax;

	/**
	 * @var WC_Rapid_Order_Settings
	 */
	protected $settings;

	/**
	 * @var WC_Rapid_Order_Shortcode
	 */
	protected $shortcode;

	public function __construct() {
		parent::__construct();

		$this->includes();
		
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'woocommerce_init', array( $this, 'woocommerce_init' ) );
		add_action( 'wp_footer', array( $this, 'load_underscore_templates' ) );
		add_action( 'wp_print_styles', array( $this, 'wp_print_styles' ) );
		add_action( 'template_redirect', array( $this, 'check_for_404' ) );
		add_action( 'wp', array( $this, 'wp' ) );
		
		add_action( 'wc_dynamic_pricing_load_modules', array($this, 'wc_dynamic_pricing_load_modules'), 99, 1);
		add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array( $this, 'add_action_links' ) );

		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_uninstall_hook( __FILE__, array( 'WC_Rapid_Order', 'uninstall' ) );
	}

	public function includes() {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		require_once( 'classes/class-wc-rapid-order-product.php' );
		require_once( 'classes/class-wc-rapid-order-ajax.php' );
		require_once( 'classes/class-wc-rapid-order-cart.php' );
		require_once( 'classes/class-wc-rapid-order-loop.php' );
		require_once( 'classes/class-wc-rapid-order-settings.php' );
		require_once( 'classes/class-wc-rapid-order-shortcode.php' );
	}

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function activate() {
		// Create the rapid order wholesaler role by default and give it view access to the wholesale list. 
		$role = add_role(
				'rapid_order_wholesaler',
				__( 'Wholesaler (Rapid Order)' ),
				array(
						'read'         => true,  
						'edit_posts'   => false,
						'delete_posts' => false, 
				)
		);
		if($role) $role->add_cap( 'wcro_wholesaler' );

		add_option( 'wcro_dont_sub_text', __( "Don't Sub", WC_Rapid_Order::TEXT_DOMAIN ) );
		add_option( 'wcro_show_search', 'yes' );
		add_option( 'wcro_sale_badge', 'yes' );
		add_option( 'wcro_featured_badge', 'yes' );
		add_option( 'wcro_use_plugin_increment_css', 'yes' );
		add_option( 'wcro_api_manager_product_id', 'Rapid Order');
		add_option( 'wcro_api_manager_instance', wp_generate_password());
		add_option( 'wcro_override_all', 'yes' );
		add_option( 'wcro_override_template', 'yes' );
	}

	public static function uninstall() {
		delete_option( 'wcro_override_guests' );
		delete_option( 'wcro_override_all' );
		delete_option( 'wcro_show_dont_sub' );
		delete_option( 'wcro_dont_sub_help_text' );
		delete_option( 'wcro_table_header_sticky' );
		delete_option( 'wcro_link_title' );
		delete_option( 'wcro_use_plugin_increment_css' );
		delete_option( 'wcro_dont_sub_text' );
		delete_option( 'wcro_show_search' );
		delete_option( 'wcro_sale_badge' );
		delete_option( 'wcro_featured_badge' );
		delete_option( 'wcro_use_plugin_increment_css' );
		delete_option( 'wcro_role' );
		delete_option( 'wcro_fixed_header_class' );
		delete_option( 'wcro_link_image' );
		delete_option( 'wcro_override_template' );
		
		remove_role('rapid_order_wholesaler');
	}

	public function wp() {
		if(is_product_category() || is_shop() || is_product_tag() ) return; 
		
		$this->disable_override_templates();
	}
	
	public function add_action_links ( $links ) {
		$mylinks = array(
				'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=products&section=wcro' ) . '">Settings</a>',
		);
		return array_merge( $links, $mylinks );
	}

	public function load_underscore_templates() {
		include( 'templates/underscore/product.php' );
	}

	public function wp_print_styles() {
		// If WC quantity increments is enabled, dequeue it's styles since they're not nice.
		if ( is_plugin_active( 'quantity-increment-buttons-for-woocommerce/woocommerce-quantity-increment-buttons.php' ) && 
		     get_option('wcro_use_plugin_increment_css') == 'yes'
		) {
			wp_dequeue_style( 'wcqib-css' );
		}
	}

	public function enqueue_scripts() {
		if ( is_plugin_active( 'quantity-increment-buttons-for-woocommerce/woocommerce-quantity-increment-buttons.php' ) && 
		     get_option('wcro_use_plugin_increment_css') == 'yes' 
		) {
			wp_enqueue_style( 'wcro-quantity-increment', $this->get_plugin_url( 'assets/css/increment-buttons.css' ) );
		}

		wp_enqueue_style( 'wc-rapid-order', $this->get_plugin_url( 'assets/css/style.css' ) );

		$suffix = '.min';
		if( defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ) {
			$suffix = '';
			
			wp_enqueue_script(
					'wc-rapid-order',
					$this->get_plugin_url( 'assets/js/wc-rapid-order-backbone.js' ),
					array( 'jquery', 'backbone', 'tooltipster' ),
					WCRO_VERSION,
					true
			);

			wp_enqueue_script(
					'sticky-header-footer',
					$this->get_plugin_url( 'assets/js/jquery-sticky-header-footer.js' ),
					array( 'jquery' ),
					WCRO_VERSION,
					true
			);

			wp_enqueue_script(
					'resize-sensor',
					$this->get_plugin_url( 'assets/js/ResizeSensor.js' ),
					array( 'jquery' ),
					WCRO_VERSION,
					true
			);

			wp_enqueue_script(
					'element-queries',
					$this->get_plugin_url( 'assets/js/ElementQueries.js' ),
					array( 'jquery', 'resize-sensor' ),
					WCRO_VERSION,
					true
			);

			wp_enqueue_script(
					'tooltipster',
					$this->get_plugin_url( 'assets/js/jquery.tooltipster.js' ),
					array( 'jquery' ),
					WCRO_VERSION,
					true
			);
			wp_enqueue_script(
				'swipebox',
				$this->get_plugin_url( 'assets/js/jquery.swipebox.js' ),
				array( 'jquery' ),
				WCRO_VERSION,
				true
			);
		} else {
			wp_enqueue_script(
					'wc-rapid-order',
					$this->get_plugin_url( 'assets/js/wc-rapid-order.min.js' ),
					array( 'jquery', 'backbone' ),
					WCRO_VERSION,
					true
			);
		}

		wp_register_script( 'accounting', WC()->plugin_url() . '/assets/js/accounting/accounting' . $suffix . '.js', array( 'jquery' ), '0.4.2' );
		wp_localize_script( 'accounting', 'accounting_params', array(
				'mon_decimal_point' => wc_get_price_decimal_separator()
		) );
		wp_enqueue_script('accounting');
		
		wp_enqueue_style( 'tooltipster', $this->get_plugin_url( 'assets/css/tooltipster-light.css' ) );

		global $wp_query, $post;
		$args = array(
				'category'                     => isset( $wp_query->query_vars['product_cat'] ) ? $wp_query->query_vars['product_cat'] : '',
				'base_path'                    => get_option( 'siteurl' ),
				'cart_remove_url'              => WC()->cart->get_remove_url( 'wcro_item_key' ),
				'stick_table'                  => get_option( 'wcro_table_header_sticky' ),
				'wc_ajax_url'                  => WC_AJAX::get_endpoint( '%%endpoint%%' ),
				'currency_format_num_decimals' => wc_get_price_decimals(),
				'currency_format_symbol'       => get_woocommerce_currency_symbol(),
				'currency_format_decimal_sep'  => esc_attr( wc_get_price_decimal_separator() ),
				'currency_format_thousand_sep' => esc_attr( wc_get_price_thousand_separator() ),
				'currency_format'              => esc_attr( str_replace( array( '%1$s', '%2$s' ), array(
						'%s',
						'%v'
				), get_woocommerce_price_format() ) ), // For accounting JS
				'currency_pos'                 => get_option( 'woocommerce_currency_pos' ),
				'fixed_header_class'           => get_option( 'wcro_fixed_header_class', '' ),
		);
		
		$args = apply_filters('wcro_javascript_args', $args);
		
		wp_localize_script( 'wc-rapid-order', 'wcro', $args );

	}

	public function woocommerce_init() {
		// Only enable template overriding if enabled for everyone or the user has the wcro_wholesaler role.
		// We have to do this after wordpress init since is_user_logged_in isn't available to us until this point.
		if(get_option( 'wcro_override_template', 'yes' ) !== 'yes'){
			$this->disable_override_templates();
		} else if(get_option( 'wcro_override_guests' ) === 'yes' && ! is_user_logged_in()){
			// show for for all non-logged in users
			$this->enable_override_templates();
		} else if(get_option( 'wcro_override_all' ) === 'yes'){
			// show for everyone
			$this->enable_override_templates();
		} else if(is_user_logged_in() && current_user_can( 'wcro_wholesaler' )){
			// show for users who have access via settings
			$this->enable_override_templates();
		}
		
//		if ( ( get_option( 'wcro_override_guests' ) === 'yes' && ! is_user_logged_in() ) ||
//		     get_option( 'wcro_override_all' ) === 'yes' ||
//		     ( is_user_logged_in() && current_user_can( 'wcro_wholesaler' ) )
//		) {
//			$this->enable_override_templates();
//		}

		$this->ajax    = new WC_Rapid_Order_AJAX();
		$this->product = new WC_Rapid_Order_Product();
		$this->cart    = new WC_Rapid_Order_Cart( $this->product );
		
		$this->product->set_cart( $this->cart );
		
		$this->loop      = new WC_Rapid_Order_Loop( $this->product );
		$this->settings  = new WC_Rapid_Order_Settings();
		$this->shortcode = WC_Rapid_Order_Shortcode::instance();
	}

	public function plugins_loaded() {
		load_plugin_textdomain( WC_Rapid_Order::TEXT_DOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		add_filter( 'woocommerce_quantity_input_args', array( $this, 'quantity_input_args' ), 100, 2 );
	}

	/**
	 * Checks if 404 and if it is, disable template override since it does not look pretty
	 */
	public function check_for_404() {
		if(is_404()) $this->disable_override_templates();
	}

	/**
	 * Updates the min value to zero (from the default '1').
	 *
	 * @param array $args
	 *
	 * @return mixed
	 */
	public function quantity_input_args( $args ) {
		$args['min_value'] = 0;

		return $args;
	}

	/**
	 * This is kind of a hackish way to get support for the Dynamic Pricing plugin by Lucas Stark. 
	 * Basically on a cart update we don't want their cart session loader to fire. So in order to do 
	 * this we just remove all the modules if this is a cart update.  
	 * 
	 * @param array $modules
	 *
	 * @return array
	 */
	public function wc_dynamic_pricing_load_modules( $modules ) {
		if( defined('DOING_AJAX') && 
		    isset($_REQUEST['update_cart']) && 
		    !did_action('woocommerce_before_calculate_totals')
		) return array();
		
		return $modules;
	}
}

$GLOBALS['wc_list_items'] = WC_Rapid_Order::instance();

/**
 * Check if this is a Rapid Order ajax request
 *
 * @return bool
 */
function wcro_is_ajax() {
	if ( !empty($_REQUEST['wcro-ajax']) ){
		//this prevents woocommerce from sending back a 302 on search results with one item
		add_filter( 'woocommerce_redirect_single_search_result', function(){ return false; } );
		
		return true;
	}
	return false;
//	return ! empty( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) == 'xmlhttprequest';
}

/**
 * @return array An array with 2 values, width [0] and height [1].
 */
function wcro_get_thumb_size() {
	return WC_Rapid_Order::instance()->product->get_product_thumbail_size();
}

function wcro_cart_total() {
	// make sure to keep this all on one line. IE gets angry if you return fragments with line breaks
	return '<div class="wcro_cart_total">' . __( 'Subtotal', WC_Rapid_Order::TEXT_DOMAIN ) . ': <span class="wcro_amount">' . wp_kses_data( WC()->cart->get_cart_subtotal() ) . '</span></div>';
}

function wcro_wc_version_compare($target_version, $operator = '<'){
	return version_compare(WC()->version, $target_version, $operator );
}

include('lib/class-wcro-api-manager.php');