<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WWPP_Wholesale_Price_Variable_Product' ) ) {

    /**
     * Model that houses the logic of wholesale prices for variable products.
     *
     * @since 1.13.4
     */
    class WWPP_Wholesale_Price_Variable_Product {

        /*
        |--------------------------------------------------------------------------
        | Class Properties
        |--------------------------------------------------------------------------
        */

        /**
         * Property that holds the single main instance of WWPP_Admin_Custom_Fields_Variable_Product.
         *
         * @since 1.13.0
         * @access private
         * @var WWPP_Admin_Custom_Fields_Variable_Product
         */
        private static $_instance;
        
        /**
         * Model that houses the logic of retrieving information relating to wholesale role/s of a user.
         *
         * @since 1.13.0
         * @access private
         * @var WWPP_Wholesale_Roles
         */
        private $_wwpp_wholesale_roles;



        
        /*
        |--------------------------------------------------------------------------
        | Class Methods
        |--------------------------------------------------------------------------
        */

        /**
         * WWPP_Admin_Custom_Fields_Variable_Product constructor.
         *
         * @since 1.13.0
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_Admin_Custom_Fields_Variable_Product model.
         */
        public function __construct( $dependencies ) {

            $this->_wwpp_wholesale_roles  = $dependencies[ 'WWPP_Wholesale_Roles' ];

        }

        /**
         * Ensure that only one instance of WWPP_Admin_Custom_Fields_Variable_Product is loaded or can be loaded (Singleton Pattern).
         *
         * @since 1.13.0
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_Admin_Custom_Fields_Variable_Product model.
         * @return WWPP_Admin_Custom_Fields_Variable_Product
         */
        public static function instance( $dependencies ) {

            if ( !self::$_instance instanceof self )
                self::$_instance = new self( $dependencies );

            return self::$_instance;

        }

        /**
         * Get curent user wholesale role.
         *
         * @since 1.15.0
         * @access private
         *
         * @return string User role string or empty string.
         */
        private function _get_current_user_wholesale_role() {

            $user_wholesale_role = $this->_wwpp_wholesale_roles->getUserWholesaleRole();
            
            return ( is_array( $user_wholesale_role ) && !empty( $user_wholesale_role ) ) ? $user_wholesale_role[ 0 ] : '';

        }

        /**
         * Filter the display format of variable product price for wholesale customers.
         *
         * @since 1.14.0
         * @access public
         *
         * @param string     $wholesale_price     Wholesale price text. Formatted price text.
         * @param string     $price               Original ( non-wholesale ) formatted price text.
         * @param WC_Product $product             Product object.
         * @param array      $user_wholesale_role User wholesale role.
         * @param float      $min_price           Variable product minimum wholesale price.
         * @param float      $max_price           Variable product maximum wholeslae price.
         * @return string Filtered variable product formatted price.
         */
        public function filter_wholesale_customer_variable_product_price_range( $wholesale_price , $price , $product , $user_wholesale_role , $min_price , $max_price ) {

            if ( !empty( $wholesale_price ) && $min_price != $max_price && $min_price < $max_price ) {

                switch ( get_option( 'wwpp_settings_variable_product_price_display' , true ) ) {
                    
                    case 'minimum':
                        return apply_filters( 'wwpp_minimum_product_price_display_mode_prefix' , __( 'From: ' , 'woocommerce-wholesale-prices-premium' ) ) . WWP_Helper_Functions::wwp_formatted_price( $min_price ) . apply_filters( 'wwp_filter_wholesale_price_display_suffix' , $product->get_price_suffix() );

                    case 'maximum':
                        return apply_filters( 'wwpp_maximum_product_price_display_mode_prefix' , __( 'Up to: ' , 'woocommerce-wholesale-prices-premium' ) ) . WWP_Helper_Functions::wwp_formatted_price( $max_price ) . apply_filters( 'wwp_filter_wholesale_price_display_suffix' , $product->get_price_suffix() );
                    
                    default:
                        return WWP_Helper_Functions::wwp_formatted_price( $min_price ) . ' - ' . WWP_Helper_Functions::wwp_formatted_price( $max_price ) . apply_filters( 'wwp_filter_wholesale_price_display_suffix' , $product->get_price_suffix() );
                    
                }

            } else
                return $wholesale_price;
            
        }

        // Remove this function
        /**
         * The purpose for this helper function is to generate price range for none wholesale users for variable product.
         * You see, default WooCommerce calculations include all variations of a product to generate min and max price range.
         *
         * Now some variations have filters to be only visible to certain wholesale users ( Set by WWPP ). But WooCommerce
         * Don't have an idea about this, so it will still include those variations to the min and max price range calculations
         * thus giving incorrect price range.
         *
         * This is the purpose of this function, to generate a correct price range that recognizes the custom visibility filter
         * of each variations.
         *
         * @since 1.13.4
         * @since 1.14.0 Add feature to set how to show the price range ( min|max|range ).
         * @access public
         *
         * @param WC_Product_Variable $variable   Variable product.
         * @param array               $variations Variations data.
         * @param string              $range_type 'regular' or 'sale'.
         * @return array Array of price range data.
         */
        public function generate_variable_product_price_range( $variable , $variations , $range_type = 'regular' ) {

            $hasSalePrice = false;
            $minPrice = '';
            $maxPrice = '';

            $active_currency = get_woocommerce_currency();

            foreach( $variations as $variation_item ) {

                $variation = wc_get_product( $variation_item[ 'variation_id' ] );

                if ( $range_type == 'regular' ) {

                    $currVarPrice = WWP_Helper_Functions::wwp_get_product_display_price( $variation , array( 'price' => $variation->get_regular_price() ) );

                    /*
                    * If it has a meta of is_purchasable of false, and it has a valid price.
                    * Meaning, this must be set on different reason, ex. variation currently out of stock, etc.
                    * Lets continue to the next item.
                    */
                    if ( !$variation_item[ 'is_purchasable' ] && $currVarPrice )
                        continue;

                    /*
                    * is_purchasable is false and it has no valid price and aelia currency switcher isn't present.
                    * Lets continue to the next item.
                    */
                    if ( !$variation_item[ 'is_purchasable' ] && !$currVarPrice && !WWP_ACS_Integration_Helper::aelia_currency_switcher_active() )
                        continue;

                    /*
                    * Default woocommerce regular price field is empty and Aelia currency switcher is active
                    * Meaning the user must have changed the base currency for this specific product.
                    * We manually get the prices the user sets on various currency and find out which is the base.
                    */
                    if ( $currVarPrice == "" && WWP_ACS_Integration_Helper::aelia_currency_switcher_active() ) {

                        $variation_regular_prices = get_post_meta( $variation_item[ 'variation_id' ] , 'variable_regular_currency_prices' , true );
                        $variation_regular_prices = json_decode( $variation_regular_prices );

                        if ( !empty( $variation_regular_prices ) ) {

                            $variation_regular_prices = get_object_vars( $variation_regular_prices );

                            $product_base_currency = WWP_ACS_Integration_Helper::get_product_base_currency( $variation_item[ 'variation_id' ] );

                            if ( array_key_exists( $product_base_currency , $variation_regular_prices ) && $variation_regular_prices[ $product_base_currency ] )
                                $currVarPrice = WWP_ACS_Integration_Helper::convert( $variation_regular_prices[ $product_base_currency ] , $active_currency , $product_base_currency );
                            else
                                $currVarPrice = 0; // No choice set it to zero. In this case there is an issue of how the user set up the pricing

                        } else
                            $currVarPrice = 0; // No choice set it to zero. In this case there is an issue of how the user set up the pricing

                    } elseif ( $currVarPrice == "" && !WWP_ACS_Integration_Helper::aelia_currency_switcher_active() )
                        $currVarPrice = 0; // No choice set it to zero. In this case there is an issue of how the user set up the pricing

                } elseif ( $range_type == 'sale' ) {

                    $currVarPrice = WWP_Helper_Functions::wwp_get_product_display_price( $variation , array( 'price' => $variation->get_price() ) );

                    /*
                    * If it has a meta of is_purchasable of false, and it has a valid price.
                    * Meaning, this must be set on different reason, ex. variation currently out of stock, etc.
                    * Lets continue to the next item.
                    */
                    if ( !$variation_item[ 'is_purchasable' ] && $currVarPrice )
                        continue;

                    /*
                    * is_purchasable is false and it has no valid price and aelia currency switcher isn't present.
                    * Lets continue to the next item.
                    */
                    if ( !$variation_item[ 'is_purchasable' ] && !$currVarPrice && !WWP_ACS_Integration_Helper::aelia_currency_switcher_active() )
                        continue;

                    // Set up $hasSalePrice variable flag
                    if ( !$hasSalePrice && $variation->get_regular_price() != "" && $variation->get_price() != "" && $variation->get_regular_price() != $variation->get_price() )
                        $hasSalePrice = true;

                    if ( $currVarPrice == "" && WWP_ACS_Integration_Helper::aelia_currency_switcher_active() ) {

                        /*
                        * Default woocommerce sale price field is empty and Aelia currency switcher is active
                        * Meaning the user must have changed the base currency for this specific product.
                        * We manually get the prices the user sets on various currency and find out which is the base.
                        */

                        $variation_sale_prices = get_post_meta( $variation_item[ 'variation_id' ] , 'variable_sale_currency_prices' , true );
                        $variation_sale_prices = json_decode( $variation_sale_prices );

                        if ( !empty( $variation_sale_prices ) ) {

                            $variation_sale_prices = get_object_vars( $variation_sale_prices );

                            $product_base_currency = WWP_ACS_Integration_Helper::get_product_base_currency( $variation_item[ 'variation_id' ] );

                            if ( array_key_exists( $product_base_currency , $variation_sale_prices ) && $variation_sale_prices[ $product_base_currency ] ) {

                                $currVarPrice = WWP_ACS_Integration_Helper::convert( $variation_sale_prices[ $product_base_currency ] , $active_currency , $product_base_currency );

                                if ( !$hasSalePrice )
                                    $hasSalePrice = true;

                            } else
                                $currVarPrice = 0; // No choice set it to zero. In this case there is an issue of how the user set up the pricing

                        } else
                            $currVarPrice = 0; // No choice set it to zero. In this case there is an issue of how the user set up the pricing

                    } elseif ( $currVarPrice == "" && !WWP_ACS_Integration_Helper::aelia_currency_switcher_active() )
                        $currVarPrice = 0; // No choice set it to zero. In this case there is an issue of how the user set up the pricing

                }

                if ( $minPrice == "" || $currVarPrice < $minPrice )
                    $minPrice = $currVarPrice;

                if ( $maxPrice == "" || $currVarPrice > $maxPrice )
                    $maxPrice = $currVarPrice;

            }

            // Only alter price html if, some/all variations of this variable product have sale price and
            // min and max price have valid values
            if ( strcasecmp( $minPrice , '' ) != 0 && strcasecmp( $maxPrice , '' ) != 0 ) {

                if ( $minPrice != $maxPrice && $minPrice < $maxPrice ) {

                    switch ( get_option( 'wwpp_settings_variable_product_price_display' , true ) ) {
                        
                        case 'minimum':
                            $priceRange = apply_filters( 'wwpp_minimum_product_price_display_mode_prefix' , __( 'From: ' , 'woocommerce-wholesale-prices-premium' ) ) . WWP_Helper_Functions::wwp_formatted_price( $minPrice ) . $variable->get_price_suffix();
                            break;

                        case 'maximum':
                            $priceRange = apply_filters( 'wwpp_maximum_product_price_display_mode_prefix' , __( 'Up to: ' , 'woocommerce-wholesale-prices-premium' ) ) . WWP_Helper_Functions::wwp_formatted_price( $maxPrice ) . $variable->get_price_suffix();
                            break;    
                        
                        default:
                            $priceRange = WWP_Helper_Functions::wwp_formatted_price( $minPrice ) . ' - ' . WWP_Helper_Functions::wwp_formatted_price( $maxPrice ) . $variable->get_price_suffix();
                            break;
                        
                    }

                } else
                    $priceRange = WWP_Helper_Functions::wwp_formatted_price( $maxPrice ) . $variable->get_price_suffix();

            } else {

                // Must be due to regular prices for variations of a variable product not set or regular user not meant
                // to see all variations of a variable product.
                return false;

            }

            $priceRange = apply_filters( 'wwp_filter_variable_product_price_range' , $priceRange , $variable , $variations , $range_type , $minPrice , $maxPrice );

            return array(
                        'price_range'    => $priceRange,
                        'has_sale_price' => $hasSalePrice
                    );

        }

        // Remove this function
        /**
         * Filter the variable product price range for none wholesale customers.
         *
         * @since 1.3.4
         * @since 1.14.0 Since WC 3.0.0, no more crossed out regular price if there are sale price on price range.
         * @access public
         *
         * @param string              $price            Product price string.
         * @param WC_Product_Variable $variable_product Variable product.
         * @return float Filtered product price string.
         */
        public function filter_regular_customer_variable_product_price_range( $price , $variable_product ) {

            // Variable product price range calculation for none wholesale users -------------------------------------------

            // Fix for the product price range if some variations are only to be displayed to certain wholesale roles
            // If code below is not present, woocommerce will include in the min and max price calculation the variations
            // that are not supposed to be displayed outside the set exclusive wholesale roles.
            // Therefore giving misleading min and max price range.

            if ( WWP_Helper_Functions::wwp_get_product_type( $variable_product ) === 'variable' && !empty( $price ) ) {

                $variations        = WWP_Helper_Functions::wwp_get_variable_product_variations( $variable_product );
                $regularPriceRange = $this->generate_variable_product_price_range( $variable_product , $variations , 'regular' );
                $salePriceRange    = $this->generate_variable_product_price_range( $variable_product , $variations , 'sale' );
                $woocommerce_data  = WWP_Helper_Functions::get_woocommerce_data(); // TODO: Remove not used

                if ( $regularPriceRange !== false ) {

                    if ( $salePriceRange[ 'has_sale_price' ] )
                        $price = '<del>' . $regularPriceRange[ 'price_range' ] . '</del> <ins>' . $salePriceRange[ 'price_range' ] . '</ins>';   
                    else
                        $price = $regularPriceRange[ 'price_range' ];

                } else {

                    // If no variations is available to none-wholesale customer then make the price empty.
                    // This is the same thing that WooCommerce does, leaving price as empty string.
                    $price = '';

                }

            }

            return $price;

        }
        
        /**
         * Filter available variable product variations.
         * The main purpose for this is to address the product price range of a variable product for non wholesale customers.
         * You see in wwpp, you can set some variations of a variable product to be exclusive only to a certain wholesale roles.
         * Now if we dont do the code below, the price range computation for regular customers will include those variations that are exclusive only to certain wholesale roles.
         * Therefore making the calculation wrong. That is why we need to filter the variation ids of a variable product depending on the current user's role.
         * This function is a replacement to our in-house built function 'filter_regular_customer_variable_product_price_range' which is not really efficient.
         * Basically 'filter_regular_customer_variable_product_price_range' function re invents the wheel and we are recreating the price range for non wholesale users ourselves. Not good.
         *
         * Important Note: WooCommerce tend to save a cache data of a product on transient, that is why sometimes this hook 'woocommerce_get_children' will not be executed
         * if there is already a cached data on transient. No worries tho, on version 1.15.0 of WWPP we are now clearing WC transients on WWPP activation so we are sure that 'woocommerce_get_children' will be executed.
         * We only need to do that once on WWPP activation coz, individual product transient is cleared on every product update on the backend.
         * So if they update the variation visibility on the backend, of course they will hit save to save the changes, that will clear the transient for this product and in turn executing this callback. So all good.
         *
         * @since 1.15.0
         * @access public
         *
         * @param array               $children Array of variation ids.
         * @param WC_Product_Variable $product  Variable product instance.
         * @return array Filtered array of variation ids.
         */
        public function filter_available_variable_product_variations( $children , $product ) {

            if ( !current_user_can( 'manage_options' ) && WWP_Helper_Functions::wwp_get_product_type( $product ) === "variable" ) {

                $filtered_children   = array();
                $user_wholesale_role = $this->_get_current_user_wholesale_role();

                foreach ( $children as $variation_id ) {

                    $roles_variation_is_visible = get_post_meta( $variation_id , WWPP_PRODUCT_WHOLESALE_VISIBILITY_FILTER );
                    if ( !is_array( $roles_variation_is_visible ) )
                        $roles_variation_is_visible = array();

                    if ( empty( $roles_variation_is_visible ) || in_array( 'all' , $roles_variation_is_visible ) || in_array( $user_wholesale_role , $roles_variation_is_visible ) )
                        $filtered_children[] = $variation_id;

                }

                return $filtered_children;

            }

            return $children;

        }


        /**
         * Execute model.
         *
         * @since 1.13.4
         * @access public
         */
        public function run() {

            add_filter( 'wwp_filter_variable_product_wholesale_price_range' , array( $this , 'filter_wholesale_customer_variable_product_price_range' ) , 10 , 6 );
            // add_filter( 'wwp_filter_variable_product_price_range_for_none_wholesale_users' , array( $this , 'filter_regular_customer_variable_product_price_range' ) , 10 , 2 );

            add_filter( 'woocommerce_get_children' , array( $this , 'filter_available_variable_product_variations' ) , 10 , 2 );

        }

    }

}