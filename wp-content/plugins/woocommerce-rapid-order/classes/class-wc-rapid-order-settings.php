<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Rapid_Order_Settings {

	public function __construct() {
		add_filter( 'woocommerce_get_sections_products', array( $this, 'add_products_section' ) );
		add_filter( 'woocommerce_get_settings_products', array( $this, 'products_section_settings' ), 10, 2 );
		add_action( 'updated_option', array( $this, 'save_role' ) );
	}

	/**
	 * Adds the Rapid Order config screen to the products admin area.
	 *
	 * @param array $sections
	 *
	 * @return array
	 */
	public function add_products_section( $sections ) {
		$sections['wcro'] = __( 'Rapid Order', WC_Rapid_Order::TEXT_DOMAIN );

		return $sections;
	}

	public function save_role($option) {
		if ( $option == 'wcro_role' ) {
			// for some reason $value is NULL so we need to re-fetch the roles. 
			$roles = get_option('wcro_role');
			
			foreach($roles as $role => $value){
				/** var WP_Role $role **/
				$role = get_role($role);
				if($value == 'yes'){
					$role->add_cap('wcro_wholesaler');
				} else {
					$role->remove_cap('wcro_wholesaler');
				}
			}
		}
	}

	/**
	 * Add some settings to the WC admin area
	 *
	 * @param array $settings
	 * @param string $current_section
	 *
	 * @return array
	 */
	public function products_section_settings( $settings, $current_section ) {
		if ( 'wcro' == $current_section ) {
			$settings = array();

			$settings[] = array(
					'name' => __( 'User Visibility', WC_Rapid_Order::TEXT_DOMAIN ),
					'type' => 'title',
					'desc' => __( 'This section allows you to control which users the wholesale order form is shown to.', WC_Rapid_Order::TEXT_DOMAIN ),
					'id'   => 'wcro_visibility'
			);

			$settings[] = array(
				'name'     => __( 'Override archive, shop, and search result pages?', WC_Rapid_Order::TEXT_DOMAIN ),
				'id'       => 'wcro_override_template',
				'type'     => 'checkbox',
				'css'      => 'min-width:300px;',
				'desc'     => __( 'If checked, any user who is granted access to view the Rapid Order form will see it on the archive, shop, and search result pages.', WC_Rapid_Order::TEXT_DOMAIN ),
				'default'  => 'yes',
			);

			// Fetch and output all roles
			$roles = $this->get_editable_roles();
			$count = count($roles) + 1;

			$settings[] = array(
					'name' => __( 'Who should see the wholesale form?', WC_Rapid_Order::TEXT_DOMAIN ),
					'id'   => 'wcro_override_all',
					'type' => 'checkbox',
					'css'  => 'min-width:300px;',
					'desc' => __( 'Everyone (role based visibility does not apply to shortcode usage)'  , WC_Rapid_Order::TEXT_DOMAIN ),
					'checkboxgroup' => 'start',
					'hide_if_checked' => 'option'
			);

			$settings[] = array(
					'name' => __( 'Guests', WC_Rapid_Order::TEXT_DOMAIN ),
					'id'   => 'wcro_override_guests',
					'type' => 'checkbox',
					'css'  => 'min-width:300px;',
					'desc' => __( 'Guests (non-logged in users)'  , WC_Rapid_Order::TEXT_DOMAIN ),
					'checkboxgroup' => '',
					'hide_if_checked' => 'yes'
			);

			$i = 2; // start at 2 since the above option is '1'
			foreach($roles as $id => $role){
				
				$setting = array(
						'desc' => __( $role['name'], WC_Rapid_Order::TEXT_DOMAIN ),
						'id'   => "wcro_role[".$id."]",
						'type' => 'checkbox',
						'css'  => 'min-width:300px;',
						'hide_if_checked' => 'yes',
				);
				
				if($i >= $count){
					$setting['checkboxgroup'] = 'end';
				} elseif($i > 1){
					$setting['checkboxgroup'] = '';
				}
				
				if(isset($role['capabilities']['wcro_wholesaler']) && $role['capabilities']['wcro_wholesaler'])
					$setting['default'] = 'yes';
				
				// Disable unchecking this as it's the entire purpose of this role.
//				if($id == 'rapid_order_wholesaler')
//					$setting['custom_attributes']['disabled'] = 'disabled';
				
				$settings[] = $setting;
				$i++;
			}

			$settings[] = array( 'type' => 'sectionend', 'id' => 'wcro_visibility' );

			$settings[] = array(
					'name' => __( 'Additional Column', WC_Rapid_Order::TEXT_DOMAIN ),
					'type' => 'title',
					'id'   => 'wcro_additional_column'
			);

			$settings[] = array(
				'name'     => __( 'Additional column', WC_Rapid_Order::TEXT_DOMAIN ),
				'desc_tip' => __( 'This will present an additional column in the list with a checkbox for all items in your store. Use this if you need some additional basic product configuration.', WC_Rapid_Order::TEXT_DOMAIN ),
				'id'       => 'wcro_show_dont_sub',
				'type'     => 'checkbox',
				'css'      => 'min-width:300px;',
				'desc'     => __( 'Show the additional column?', WC_Rapid_Order::TEXT_DOMAIN ),
			);

			$settings[] = array(
				'name'     => __( 'Additional column text', WC_Rapid_Order::TEXT_DOMAIN ),
				'desc_tip' => __( 'This is the header text that displays above the additional column.', WC_Rapid_Order::TEXT_DOMAIN ),
				'id'       => 'wcro_dont_sub_text',
				'type'     => 'text',
			);

			$settings[] = array(
				'name'     => __( 'Help text', WC_Rapid_Order::TEXT_DOMAIN ),
				'desc_tip' => __( 'This is the help text for the additional column. It will display when the user hovers over the questions mark.', WC_Rapid_Order::TEXT_DOMAIN ),
				'id'       => 'wcro_dont_sub_help_text',
				'type'     => 'textarea',
			);

			$settings[] = array( 'type' => 'sectionend', 'id' => 'wcro_additional_column' );

			$settings[] = array(
					'name' => __( 'Display Options', WC_Rapid_Order::TEXT_DOMAIN ),
					'type' => 'title',
					'desc' => __( 'Configure how the wholesale list is displayed.', WC_Rapid_Order::TEXT_DOMAIN ),
					'id'   => 'wcro_display_options'
			);

			$settings[] = array(
				'name'     => __( 'Search bar', WC_Rapid_Order::TEXT_DOMAIN ),
				'desc_tip' => __( 'Check this to show a search bar within the list', WC_Rapid_Order::TEXT_DOMAIN ),
				'id'       => 'wcro_show_search',
				'type'     => 'checkbox',
				'css'      => 'min-width:300px;',
				'desc'     => __( 'Show the search bar?', WC_Rapid_Order::TEXT_DOMAIN ),
			);

			$settings[] = array(
				'name' => __( 'Table header', WC_Rapid_Order::TEXT_DOMAIN ),
				'id'   => 'wcro_table_header_sticky',
				'type' => 'checkbox',
				'css'  => 'min-width:300px;',
				'desc' => __( 'Make the table header sticky?', WC_Rapid_Order::TEXT_DOMAIN ),
			);

			$settings[] = array(
					'name'     => __( 'Class/ID of existing sticky header', WC_Rapid_Order::TEXT_DOMAIN ),
					'desc_tip' => __( 'If you have a div that is set to a fixed position (sticky) enter the id or class name here (ie .fixedHeader or #fixedHeader)', WC_Rapid_Order::TEXT_DOMAIN ),
					'id'       => 'wcro_fixed_header_class',
					'type'     => 'text',
			);

			$settings[] = array(
				'name' => __( 'Link title to product page', WC_Rapid_Order::TEXT_DOMAIN ),
				'id'   => 'wcro_link_title',
				'type' => 'checkbox',
				'css'  => 'min-width:300px;',
				'desc' => __( "Should we link the product title to it's product page?", WC_Rapid_Order::TEXT_DOMAIN ),
			);

			$settings[] = array(
				'title' => __( 'Product Thumbnail', WC_Rapid_Order::TEXT_DOMAIN ),
				'desc' => __( "Hide the product thumbnail?", WC_Rapid_Order::TEXT_DOMAIN ),
				'id'   => 'wcro_hide_thumbnail',
				'type' => 'checkbox',
				'css'  => 'min-width:300px;',
			);

			$settings[] = array(
//				'name' => __( 'Image Link', WC_Rapid_Order::TEXT_DOMAIN ),
				'id'   => 'wcro_link_image',
				'type' => 'radio',
				'options'  => array(
					'nothing'  => __( 'Nothing', WC_Rapid_Order::TEXT_DOMAIN ),
					'image'  => __( 'Larger image in lightbox', WC_Rapid_Order::TEXT_DOMAIN ),
					'product_page'  => __( 'Product Page', WC_Rapid_Order::TEXT_DOMAIN ),
				),
				'default'  => 'nothing',
				'desc' => __( "What should the product thumbnail link to when clicked?", WC_Rapid_Order::TEXT_DOMAIN ),
			);

			$settings[] = array(
				'name' => __( 'Sale Badge', WC_Rapid_Order::TEXT_DOMAIN ),
				'id'   => 'wcro_sale_badge',
				'type' => 'checkbox',
				'css'  => 'min-width:300px;',
				'desc' => __( 'Show the sale badge for on sale items?', WC_Rapid_Order::TEXT_DOMAIN ),
			);

			$settings[] = array(
				'name' => __( 'Featured Badge', WC_Rapid_Order::TEXT_DOMAIN ),
				'id'   => 'wcro_featured_badge',
				'type' => 'checkbox',
				'css'  => 'min-width:300px;',
				'desc' => __( 'Show the featured badge for featured items?', WC_Rapid_Order::TEXT_DOMAIN ),
			);

			$settings[] = array(
					'name' => __( 'Use Rapid Order increment button css', WC_Rapid_Order::TEXT_DOMAIN ),
					'id'   => 'wcro_use_plugin_increment_css',
					'type' => 'checkbox',
					'css'  => 'min-width:300px;',
					'desc' => __( 'When using the Quantity Increment Buttons for WooCommerce plugin we\'ll automatically load some css to make the buttons look nicer. Uncheck this if your using your own custom css for the increment buttons.'  , WC_Rapid_Order::TEXT_DOMAIN ),
			);

			$settings[] = array( 'type' => 'sectionend', 'id' => 'wcro_display_options' );
		}
		
		return $settings;
	}

	public function get_editable_roles() {
		global $wp_roles;

		$all_roles = $wp_roles->roles;
		$editable_roles = apply_filters('editable_roles', $all_roles);

		return $editable_roles;
	}
}
