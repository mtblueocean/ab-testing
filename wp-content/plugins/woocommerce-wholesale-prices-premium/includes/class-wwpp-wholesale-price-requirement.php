<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WWPP_Wholesale_Price_Requirement' ) ) {

    /**
     * Model that handles the checking if a wholesale user meets the requirements of having wholesale price.
     * 
     * @since 1.12.8
     */
    class WWPP_Wholesale_Price_Requirement {

        /*
        |--------------------------------------------------------------------------
        | Class Properties
        |--------------------------------------------------------------------------
        */

        /**
         * Property that holds the single main instance of WWPP_Wholesale_Price_Requirement.
         *
         * @since 1.12.8
         * @access private
         * @var WWPP_Wholesale_Price_Requirement
         */
        private static $_instance;
        
        /**
         * Model that houses the logic of retrieving information relating to wholesale role/s of a user.
         *
         * @since 1.12.8
         * @access private
         * @var WWPP_Wholesale_Roles
         */
        private $_wwpp_wholesale_roles;

        /**
         * Class attribute that houses bundle product items from a given cart.
         *
         * @since 1.15.0
         * @access public
         * @var array
         */
        private $_bundle_product_items;

        /**
         * Class attribute that houses composite product items from a given cart.
         *
         * @since 1.15.0
         * @access public
         * @var array
         */
        private $_composite_product_items;
        



        /*
        |--------------------------------------------------------------------------
        | Class Methods
        |--------------------------------------------------------------------------
        */

        /**
         * WWPP_Wholesale_Price_Requirement constructor.
         *
         * @since 1.12.8
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_Wholesale_Price_Requirement model.
         */
        public function __construct( $dependencies ) {

            $this->_wwpp_wholesale_roles = $dependencies[ 'WWPP_Wholesale_Roles' ];

        }

        /**
         * Ensure that only one instance of WWPP_Wholesale_Price_Requirement is loaded or can be loaded (Singleton Pattern).
         *
         * @since 1.12.8
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_Wholesale_Price_Requirement model.
         * @return WWPP_Wholesale_Price_Requirement
         */
        public static function instance( $dependencies ) {

            if ( !self::$_instance instanceof self )
                self::$_instance = new self( $dependencies );

            return self::$_instance;

        }


        
        
        /*
        |--------------------------------------------------------------------------
        | Minimum Order Quantity Requirement
        |--------------------------------------------------------------------------
        */

        /**
         * Filter the price to show the minimum order quantity for wholesale users for this specific product.
         *
         * @since 1.4.0
         * @since 1.14.0 Refactor codebase and move to its proper model.
         * @since 1.14.5 Bug Fix. Per parent variable product minimum wholesale order quantity requirement not shown. (WWPP-417).
         * @since 1.15.3 Improvement. Move the variable product min order requirement label onto the variable product price instead of the variation price.
         * @access public
         * 
         * @param string     $wholesale_price_html Wholesale price markup.
         * @param float      $price                Product price.                      
         * @param WC_Product $product              Product object.
         * @param array      $user_wholesale_role  Array of user wholesale roles.
         * @return string Filtered wholesale price markup.
         */
        public function display_minimum_wholesale_order_quantity( $wholesale_price_html , $price , $product , $user_wholesale_role , $wholesale_price_title_text , $wholesale_price ) {

            if ( !empty( $user_wholesale_role ) ) {
                
                $product_id   = WWP_Helper_Functions::wwp_get_product_id( $product );
                $product_type = WWP_Helper_Functions::wwp_get_product_type( $product );

                if ( $product_id ) {

                    if ( $product_type === "variable" )
                        $minimum_order = get_post_meta( $product_id , $user_wholesale_role[ 0 ] . "_variable_level_wholesale_minimum_order_quantity" , true );                          
                    else // variation and simple
                        $minimum_order = get_post_meta( $product_id , $user_wholesale_role[ 0 ] . "_wholesale_minimum_order_quantity" , true );

                    if ( isset( $minimum_order ) && $minimum_order > 0 ) {

                        if ( $product_type === "variable" )
                            $wholesale_price_html .= ' <span class="wholesale_price_minimum_order_quantity">' . sprintf( __( 'Min: %1$s of any variation combination' , 'woocommerce-wholesale-prices-premium' ) , $minimum_order ) . '</span>';
                        else // variation and simple
                            $wholesale_price_html .= ' <span class="wholesale_price_minimum_order_quantity">' . sprintf( __( 'Min: %1$s' , 'woocommerce-wholesale-prices-premium' ) , $minimum_order ) . '</span>';

                    }

                }

            }

            return $wholesale_price_html;

        }

        /**
         * Set minimum order quantity as minimum value ( default value ) for a given product if one is set.
         *
         * @since 1.4.2
         * @since 1.14.0 Refactor codebase and move to its proper model.
         * @since 1.14.5 Bug Fix. Per parent variable product minimum wholesale order quantity requirement not shown. (WWPP-417).
         * @since 1.15.3 Silence notices thrown by function is_shop
         * @access public
         *
         * @param array      $args    Quantity field input args.
         * @param WC_Product $product Product object.
         * @return array Filtered quantity field input args.
         */
        public function set_minimum_order_quantity_as_initial_value( $args , $product ) {

            if ( is_null( $product ) )
                $product = $GLOBALS[ 'product' ];
            
            $user_wholesale_role = $this->_wwpp_wholesale_roles->getUserWholesaleRole();
            $filtered_args       = $args;

            if ( !empty( $user_wholesale_role ) && ( is_product() || @is_shop() || is_product_category() || is_product_tag() ) ) {

                // No need for variable product, we don't need it be applied on a price range
                // We need it to be applied per variation for variable products

                $product_id   = WWP_Helper_Functions::wwp_get_product_id( $product );
                $product_type = WWP_Helper_Functions::wwp_get_product_type( $product );                

                if ( $product_id && $product_type !== "variable" ) {

                    $wholesale_price = WWP_Wholesale_Prices::get_product_wholesale_price_on_shop( $product_id , $user_wholesale_role );
                    $minimum_order   = get_post_meta( $product_id , $user_wholesale_role[ 0 ] . "_wholesale_minimum_order_quantity" , true );

                    if ( $minimum_order && $wholesale_price ) {

                        $filtered_args[ 'input_value' ] = $minimum_order;
                        $filtered_args[ 'min_value' ]   = 1;

                    }

                }

            }

            return apply_filters( 'wwpp_filter_set_product_quantity_value_to_minimum_order_quantity' , $filtered_args , $args , $product , $user_wholesale_role );

        }

        /**
         * Set qty field value to the min set for this variation product. We only use the per variation min, we dont enforce min if it is set per variable product.
         * This function is Min/Max Quantities plugin compatible. We only change the qty field value, not touch the others.
         * Our min set will take precedence over what is set on Min/Max Quantities plugin.
         * The min we set will be set on the qty field value, not the min attribute coz we still allow purchase lower than min set.
         * 
         * @since 1.15.3
         * @access public
         *
         * @param array                $args Array of variation arguments.
         * @param WC_Product_Variable  $variable Variable product instance.
         * @param WC_Product_Variation $variation Variation product instance.
         * @return array Filtered array of variation arguments.
         */
        public function enforce_min_order_qty_requirement_on_qty_field( $args , $variable , $variation ) {

            $variation_id        = WWP_Helper_Functions::wwp_get_product_id( $variation );
            $user_wholesale_role = $this->_wwpp_wholesale_roles->getUserWholesaleRole();
            $wholesale_price     = WWP_Wholesale_Prices::get_product_wholesale_price_on_shop( $variation_id , $user_wholesale_role );

            if ( !empty( $user_wholesale_role ) && !empty( $wholesale_price ) ) {

                $minimum_order_qty = get_post_meta( $variation_id , $user_wholesale_role[ 0 ] . "_wholesale_minimum_order_quantity" , true ); // Per variation level
                
                if ( $minimum_order_qty )
                    $args[ 'input_value' ] = $minimum_order_qty;

            }

            return $args;

        }

        /**
         * Extract bundle and composite products from cart items. We will use these later.
         *
         * @since 1.15.0
         * @access public
         *
         * @param $cart_object         WC_Cart object.
         * @param $user_wholesale_role Array of user wholesale role.
         */
        public function extract_bundle_and_composite_products( $cart_object , $user_wholesale_role ) {

            $this->_bundle_product_items    = [];
            $this->_composite_product_items = [];

            foreach ( $cart_object->get_cart() as $cart_item_key => $cart_item ) {

                if ( WWP_Helper_Functions::wwp_get_product_type( $cart_item[ 'data' ] ) === 'bundle' ) {
                    
                    $bundle_product = wc_get_product( $product_id = WWP_Helper_Functions::wwp_get_product_id( $cart_item[ 'data' ] ) );
                    $bundle_items   = $bundle_product->get_bundled_items();

                    foreach ( $bundle_items as $bundle_item )
                        $this->_bundle_product_items[ $bundle_item->item_id ] = array( 'is_priced_individually' => $bundle_item->is_priced_individually() );

                } elseif ( WWP_Helper_Functions::wwp_get_product_type( $cart_item[ 'data' ] ) === 'composite' ) {

                    $composite_product = wc_get_product( $product_id = WWP_Helper_Functions::wwp_get_product_id( $cart_item[ 'data' ] ) );
                    $composite_items   = $composite_product->get_composite_data();

                    foreach ( $composite_items as $composite_item ) {

                        $composite_item_obj = new WC_CP_Component( $composite_item[ 'component_id' ] , $cart_item[ 'data' ] );

                        $this->_composite_product_items[ $composite_item[ 'component_id' ] ] = array( 'is_priced_individually' => $composite_item_obj->is_priced_individually() );

                    }

                }

            }

        }

        /**
         * Check if cart item is to be included on cart totals computation. This is our own cart price total and items total computation.
         * We cannot use the get_total function of WC_Cart here coz we are hooking into 'woocommerce_before_calculate_totals' which is too early to use get_total.
         * We need to hook into 'woocommerce_before_calculate_totals' tho, this is important.
         * Basically what we are doing is skipping products on cart that are part or a component of a complex product and they are not priced individually.
         * 
         * @since 1.15.0
         * @access public
         *
         * @param boolean $return              Boolean flag that determines either include or exclude current cart item on our custom cart totals computation.
         * @param array   $cart_item           Cart item.
         * @param array   $user_wholesale_role Current user wholesale roles.
         */
        public function filter_if_cart_item_is_included_on_cart_totals_computation( $return , $cart_item , $user_wholesale_role ) {

            /*
             * Only perform the check if wholesale role is not empty and product price is not empty
             * Products with empty price might belong to a composite or a bundle or any complex product set up as non per-product pricing.
             * In these cases, automatically return false. ( Don't apply wholesale pricing ).
             */
            if ( empty( $user_wholesale_role ) || $cart_item[ 'data' ]->get_price() === '' )
                return false; // Not a wholesale user and/or no product price

            if ( isset( $cart_item[ 'bundled_by' ] ) && $cart_item[ 'bundled_by' ] && 
                 array_key_exists( $cart_item[ 'bundled_item_id' ] , $this->_bundle_product_items ) && !$this->_bundle_product_items[ $cart_item[ 'bundled_item_id' ] ][ 'is_priced_individually' ] )
                return false;
            
            if ( isset( $cart_item[ 'composite_parent' ] ) && $cart_item[ 'composite_parent' ] &&
                 array_key_exists( $cart_item[ 'composite_item' ] , $this->_composite_product_items ) && !$this->_composite_product_items[ $cart_item[ 'composite_item' ] ][ 'is_priced_individually' ] )
                return false;

            return $return;

        }

        /**
         * Filter if apply wholesale price per product level. Validate if per product level requirements are meet or not.
         *
         * Important Note: We are retrieving the raw wholesale price, not wholesale price with applied tax. Just the raw
         * wholesale price of the product.
         * 
         * @since 1.15.0
         * @access public
         *
         * @param boolean $apply_wholesale_price Boolean flag that determines either to apply or not wholesale pricing to the current cart item.
         * @param array   $cart_item             Cart item.
         * @param WC_Cart $cart_object           WC_Cart instance.
         * @param array   $user_wholesale_role   Current user wholesale roles.
         * @return array|boolean Array of error notices on if current cart item fails product requirement, boolean true if passed and should apply wholesale pricing.
         */
        public function filter_if_apply_wholesale_price_per_product_level( $apply_wholesale_price , $cart_item , $cart_object , $user_wholesale_role , $wholesale_price ) {
            
            if ( !$this->filter_if_cart_item_is_included_on_cart_totals_computation( true , $cart_item , $user_wholesale_role ) )
                return false;

            $notice                    = array();          
            $formatted_wholesale_price = WWP_Helper_Functions::wwp_formatted_price( $wholesale_price );

            if ( in_array( WWP_Helper_Functions::wwp_get_product_type( $cart_item[ 'data' ] ) , array( 'simple' , 'bundle' , 'composite' ) ) ) {

                $moq = get_post_meta( WWP_Helper_Functions::wwp_get_product_id( $cart_item[ 'data' ] ) , $user_wholesale_role[ 0 ] . '_wholesale_minimum_order_quantity' , true );
                $moq = ( is_numeric( $moq ) ) ? ( int ) $moq : 0;

                if ( $cart_item[ 'quantity' ] < $moq )
                    $notice = array( 'type' => 'notice' , 'message' => sprintf( __( '<span class="wwpp-notice"></span>You did not meet the minimum order quantity <b>(%1$s items)</b> of the product <b>%2$s</b> to activate wholesale pricing <b>(%3$s)</b>. Please increase quantities to the cart to activate adjusted pricing.' , 'woocommerce-wholesale-prices-premium' ) , $moq , $cart_item[ 'data' ]->get_title() , $formatted_wholesale_price ) );

            } elseif ( WWP_Helper_Functions::wwp_get_product_type( $cart_item[ 'data' ] ) === 'variation' ) {

                // Process variable level wholesale minimum order quantity
                $variable_id        = WWP_Helper_Functions::wwp_get_parent_variable_id( $cart_item[ 'data' ] );
                $variable_level_moq = get_post_meta( $variable_id , $user_wholesale_role[ 0 ] . '_variable_level_wholesale_minimum_order_quantity' , true );
                $variable_level_moq = ( is_numeric( $variable_level_moq ) ) ? ( int ) $variable_level_moq : 0;
                $variable_total     = 0;

                // Get total items of a variable product in cart ( Total items of its variations )
                foreach ( $cart_object->cart_contents as $cart_item_key => $ci )
                    if ( WWP_Helper_Functions::wwp_get_product_type( $ci[ 'data' ] ) === 'variation' && WWP_Helper_Functions::wwp_get_parent_variable_id( $ci[ 'data' ] ) == $variable_id )
                        $variable_total += $ci[ 'quantity' ];
                
                $variable_level_wholesale_moq_passed = ( $variable_total >= $variable_level_moq );
                
                // Process variation level wholesale minimum order quantity
                $variation_level_moq                  = get_post_meta( WWP_Helper_Functions::wwp_get_product_id( $cart_item[ 'data' ] ) , $user_wholesale_role[ 0 ] . '_wholesale_minimum_order_quantity' , true );
                $variation_level_moq                  = ( is_numeric( $variation_level_moq ) ) ? ( int ) $variation_level_moq : 0;
                $variation_level_wholesale_moq_passed = ( $cart_item[ 'quantity' ] >= $variation_level_moq );
                
                // Check if this cart item passed the required minimum order quantity both variable and variation level
                if ( !$variation_level_wholesale_moq_passed || !$variable_level_wholesale_moq_passed ) {

                    // Construct variation attributes
                    $variable_attributes = "";

                    if ( is_array( $cart_item[ 'variation' ] ) && !empty( $cart_item[ 'variation' ] ) ) {

                        foreach ( $cart_item[ 'variation' ] as $attribute => $attributeVal ) {

                            $attribute = str_replace( 'attribute_' , '' , $attribute );

                            if ( strpos( $attribute , 'pa_' ) !== false) {

                                // Attribute based variable product attribute
                                $attribute = str_replace( 'pa_' , '' , $attribute );

                                $attributeVal = str_replace( '-' , ' ' , $attributeVal );
                                $attributeVal = ucwords( $attributeVal );

                            }

                            $attribute = str_replace( '-' , ' ' , $attribute );
                            $attribute = ucwords( $attribute );

                            if ( !empty( $variable_attributes ) )
                                $variable_attributes .= ", ";

                            $variable_attributes .= $attribute . ": " . $attributeVal;

                        }

                    }

                    if ( !empty( $variable_attributes ) )
                        $variable_attributes = "(" . $variable_attributes . ")";

                    if ( !$variation_level_wholesale_moq_passed && $variation_level_moq )
                        $notice = array( 'type' => 'notice' , 'message' => sprintf( __( '<span class="wwpp-notice"></span>You did not meet the minimum order quantity <b>(%1$s items)</b> of the product <b>%2$s</b> to activate wholesale pricing <b>(%3$s)</b>. Please increase quantities to the cart to activate adjusted pricing.' , 'woocommerce-wholesale-prices-premium' ) , $variation_level_moq , $cart_item[ 'data' ]->get_title() . ' ' . $variable_attributes , $formatted_wholesale_price ) );
                    elseif ( !$variable_level_wholesale_moq_passed && $variable_level_moq )
                        $notice = array( 'type' => 'notice' , 'message' => sprintf( __( '<span class="wwpp-notice"></span>You did not meet the minimum order quantity <b>(%1$s items of any variation)</b> of the product <b>%2$s</b> to activate wholesale pricing <b>(%3$s)</b> for the variation <b>%4$s</b>. Please increase quantities to the cart to activate adjusted pricing.' , 'woocommerce-wholesale-prices-premium' ) , $variable_level_moq , $cart_item[ 'data' ]->get_title() , $formatted_wholesale_price , $variable_attributes ) );

                }

            }

            $notice = apply_filters( 'wwpp_filter_wholesale_price_per_product_basis_requirement_failure_notice' , $notice , $cart_item , $cart_object , $user_wholesale_role );

            return !empty( $notice ) ? $notice : $apply_wholesale_price;
            
        }

        /**
         * Filter if apply wholesale price per cart level. Validate if cart level requirements are meet or not.
         *
         * Important Note: This does not use the raw cart total, this calculate the cart total by using the wholesale price
         * of each product on the cart. The idea is that so even after the cart is applied with wholesale price, it will
         * still meet the minimum order price.
         *
         * Important Note: We are retrieving the raw wholesale price, not wholesale price with applied tax. Just the raw
         * wholesale price of the product.
         *
         * Important Note: Minimum order price is purely based on product price. It does not include tax and shipping costs.
         * Just the total product price on the cart using wholesale price.
         * 
         * @since 1.15.0
         * @access public
         *
         * @param boolean $apply_wholesale_price Boolean flag that determines either to apply or not wholesale pricing per cart level.
         * @param WC_Cart $cart_object           WC_Cart instance.
         * @param array   $user_wholesale_role   Current user wholesale roles.
         * @return array|boolean Array of error notices on if current cart item fails cart requirements, boolean true if passed and should apply wholesale pricing.
         */
        public function filter_if_apply_wholesale_price_cart_level( $apply_wholesale_price , $cart_total , $cart_items , $cart_object , $user_wholesale_role ) {

            $minimum_cart_items                     = trim( get_option( 'wwpp_settings_minimum_order_quantity' ) );
            $minimum_cart_price                     = trim( get_option( 'wwpp_settings_minimum_order_price' ) );
            $minimum_requirements_conditional_logic = get_option( 'wwpp_settings_minimum_requirements_logic' );
            $notices                                = array();

            // Check if there is an option that overrides wholesale price order requirement per role
            $override_per_wholesale_role = get_option( 'wwpp_settings_override_order_requirement_per_role' , false );

            if ( $override_per_wholesale_role === 'yes' ) {

                $per_wholesale_role_order_requirement = get_option( WWPP_OPTION_WHOLESALE_ROLE_ORDER_REQUIREMENT_MAPPING , array() );
                if ( !is_array( $per_wholesale_role_order_requirement ) )
                    $per_wholesale_role_order_requirement = array();

                if ( array_key_exists( $user_wholesale_role[ 0 ] , $per_wholesale_role_order_requirement ) ) {

                    // Use minimum order quantity set for this current wholesale role
                    $minimum_cart_items                     = $per_wholesale_role_order_requirement[ $user_wholesale_role[ 0 ] ][ 'minimum_order_quantity' ];
                    $minimum_cart_price                     = $per_wholesale_role_order_requirement[ $user_wholesale_role[ 0 ] ][ 'minimum_order_subtotal' ];
                    $minimum_requirements_conditional_logic = $per_wholesale_role_order_requirement[ $user_wholesale_role[ 0 ] ][ 'minimum_order_logic' ];

                }

            }

            if ( is_numeric( $minimum_cart_items ) && ( !is_numeric( $minimum_cart_price ) || strcasecmp( $minimum_cart_price , '' ) == 0 || ( ( float ) $minimum_cart_price <= 0) ) ) {

                $minimum_cart_items = (int) $minimum_cart_items;
                if ( $cart_items < $minimum_cart_items )
                    $notices[] = array( 'type' => 'notice' , 'message' => sprintf( __( '<span class="wwpp-notice"></span>You have not met the minimum order quantity of <b>(%1$s)</b> to activate adjusted pricing. Retail  prices will be shown below until the minimum order threshold is met.' , 'woocommerce-wholesale-prices-premium' ) , $minimum_cart_items ) );

            } elseif ( is_numeric( $minimum_cart_price ) && ( !is_numeric( $minimum_cart_items ) || strcasecmp( $minimum_cart_items , '' ) == 0 || ( (int) $minimum_cart_items <= 0) ) ){

                $minimum_cart_price = (float) $minimum_cart_price;
                if ( $cart_total < $minimum_cart_price )
                    $notices[] = array( 'type' => 'notice' , 'message' => sprintf( __( '<span class="wwpp-notice"></span>You have not met the minimum order subtotal of <b>(%1$s)</b> to activate adjusted pricing. Retail  prices will be shown below until the minimum order threshold is met. The cart subtotal calculated with wholesale prices is <b>%2$s</b>' , 'woocommerce-wholesale-prices-premium' ) , WWP_Helper_Functions::wwp_formatted_price( $minimum_cart_price ) , WWP_Helper_Functions::wwp_formatted_price( $cart_total ) ) );

            } elseif ( is_numeric($minimum_cart_price) && is_numeric($minimum_cart_items) ) {

                if ( strcasecmp( $minimum_requirements_conditional_logic , 'and' ) == 0) {

                    if ( $cart_items < $minimum_cart_items || $cart_total < $minimum_cart_price )
                        $notices[] = array( 'type' => 'notice' , 'message' => sprintf( __( '<span class="wwpp-notice"></span>You have not met the minimum order quantity of <b>(%1$s)</b> and minimum order subtotal of <b>(%2$s)</b> to activate adjusted pricing. Retail prices will be shown below until the minimum order threshold is met. The cart subtotal calculated with wholesale prices is <b>%3$s</b>' , 'woocommerce-wholesale-prices-premium' ) , $minimum_cart_items , WWP_Helper_Functions::wwp_formatted_price( $minimum_cart_price ) , WWP_Helper_Functions::wwp_formatted_price( $cart_total ) ) );

                } else {

                    if ( $cart_items < $minimum_cart_items && $cart_total < $minimum_cart_price )
                        $notices[] = array( 'type' => 'notice' , 'message' => sprintf( __( '<span class="wwpp-notice"></span>You have not met the minimum order quantity of <b>(%1$s)</b> or minimum order subtotal of <b>(%2$s)</b> to activate adjusted pricing. Retail prices will be shown below until the minimum order threshold is met. The cart subtotal calculated with wholesale prices is <b>%3$s</b>' , 'woocommerce-wholesale-prices-premium' ) , $minimum_cart_items , WWP_Helper_Functions::wwp_formatted_price( $minimum_cart_price ) , WWP_Helper_Functions::wwp_formatted_price( $cart_total ) ) );

                }

            }

            $notices = apply_filters( 'wwpp_filter_wholesale_price_requirement_failure_notice' , $notices , $minimum_cart_items , $minimum_cart_price , $cart_items , $cart_total , $cart_object , $user_wholesale_role );

            return !empty( $notices ) ? $notices : $apply_wholesale_price;

        }

        /**
         * Disable the purchasing capabilities of the current wholesale user if not all wholesale requirements are met.
         *
         * @since 1.14.0
         * @since 1.15.3 Add support for WC 3.2 checkout page changes.
         * @access public
         *
         * @param WC_Cart $cart_object         Cart object.
         * @param array   $user_wholesale_role Array of wholesale role keys for the current customer.
         */
        public function disable_purchasing_capabilities( $cart_object , $user_wholesale_role ) {

            if ( !empty( $user_wholesale_role ) ) {
                
                $all_registered_wholesale_roles = $this->_wwpp_wholesale_roles->getAllRegisteredWholesaleRoles();
                $only_allow_wholesale_orders    = isset( $all_registered_wholesale_roles[ $user_wholesale_role[ 0 ] ][ 'onlyAllowWholesalePurchases' ] ) ? $all_registered_wholesale_roles[ $user_wholesale_role[ 0 ] ][ 'onlyAllowWholesalePurchases' ] : 'no';

                if ( $only_allow_wholesale_orders === 'yes' && ( is_cart() || is_checkout() ) ) {

                    $woocommerce_data = WWP_Helper_Functions::get_woocommerce_data();

                    if ( ( version_compare( $woocommerce_data[ 'Version' ] , '3.2.0' , '>=' ) || $woocommerce_data[ 'Version' ] === '3.2.0-rc.2' ) && is_checkout() ) {
                        // Only do this for WC 3.2 ?>
                        
                        <script>
                            jQuery( document ).ready( function( $ ) {

                                var button_replace_text = "<?php _e( '<h4>Please adjust your cart to meet all of the wholesale requirements in order to proceed.</h4>' , 'woocommerce-wholesale-prices-premium' ); ?>";

                                $( 'body' ).on( 'DOMNodeInserted' , function( e ) {

                                    var $condition_container = $( e.target );

                                    if ( $condition_container.attr( 'id' ) === 'payment' )
                                        $condition_container.find( ".form-row.place-order" ).replaceWith( button_replace_text );

                                    return $( this );

                                } );

                            } );
                        </script>

                        <?php wc_print_notice( __( '<span class="wwpp-notice"></span>Please adjust your cart to meet all of the wholesale requirements in order to proceed.' , 'woocommerce-wholesale-prices-premium' ) , 'notice' );
                            
                    }
                    
                    add_filter( 'woocommerce_order_button_html' , function( $fields ) {

                        return __( '<h4>Please adjust your cart to meet all of the wholesale requirements in order to proceed.</h4>' , 'woocommerce-wholesale-prices-premium' );

                    } );

                    remove_action( 'woocommerce_proceed_to_checkout' , 'woocommerce_button_proceed_to_checkout' , 20 );
                    remove_action( 'woocommerce_proceed_to_checkout' , array( $this , 'output_disable_purchase_notice' ) );
                    add_action( 'woocommerce_proceed_to_checkout' , array( $this , 'output_disable_purchase_notice' ) );

                }

            }

        }

        /**
         * Output disable purchase notice.
         *
         * @since 1.14.9
         * @access public
         */
        public function output_disable_purchase_notice() {

            _e( '<h4>Please adjust your cart to meet all of the wholesale requirements in order to proceed.</h4>' , 'woocommerce-wholesale-prices-premium' );

        }




        /*
        |--------------------------------------------------------------------------
        | Execute Model
        |--------------------------------------------------------------------------
        */

        /**
         * Execute model.
         *
         * @since 1.12.8
         * @access public
         */
        public function run() {

            // Add order minimum order quantity data on product wholesale price fields
            add_filter( 'wwp_filter_wholesale_price_html' , array( $this , 'display_minimum_wholesale_order_quantity' )    , 100 , 6 );
            add_filter( 'woocommerce_quantity_input_args' , array( $this , 'set_minimum_order_quantity_as_initial_value' ) , 10  , 2 );

            // Set min qty as qty field initial value ( variation )
            add_filter( 'woocommerce_available_variation' , array( $this , 'enforce_min_order_qty_requirement_on_qty_field' ) , 20 , 3 );

            // Check if current user meets the wholesale price requirement
            add_action( 'wwp_before_apply_product_wholesale_price_cart_loop' , array( $this , 'extract_bundle_and_composite_products' )                      , 10 , 2 );
            add_filter( 'wwp_include_cart_item_on_cart_totals_computation' ,   array( $this , 'filter_if_cart_item_is_included_on_cart_totals_computation' ) , 10 , 3 );
            add_filter( 'wwp_apply_wholesale_price_per_product_level' ,        array( $this , 'filter_if_apply_wholesale_price_per_product_level' )          , 10 , 5 );
            add_filter( 'wwp_apply_wholesale_price_cart_level' ,               array( $this , 'filter_if_apply_wholesale_price_cart_level' )                 , 10 , 5 );
            add_action( 'wwp_wholesale_requirements_not_passed' ,              array( $this , 'disable_purchasing_capabilities' )                            , 10 , 2 );
            
        }

    }

}
