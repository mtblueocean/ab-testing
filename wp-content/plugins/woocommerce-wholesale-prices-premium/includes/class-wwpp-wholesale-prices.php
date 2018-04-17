<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WWPP_Wholesale_Prices {

    private static $_instance;

    public static function getInstance() {

        if ( !self::$_instance instanceof self )
            self::$_instance = new self;

        return self::$_instance;

    }

    /**
     * Get the price of a product on shop pages with taxing applied (Meaning either including or excluding tax
     * depending on the settings of the shop).
     *
     * @since 1.7.1
     * @access public
     * @since _printWholesalePricePerOrderQuantityTable
     *
     * @param $product
     * @param $price
     * @param $wc_price_arg
     * @return mixed
     */
    public function getProductShopPriceWithTaxingApplied( $product , $price , $wc_price_arg = array() , $user_wholesale_role ) {

        if ( get_option( 'woocommerce_calc_taxes' , false ) === 'yes' ) {

            $woocommerce_tax_display_shop = get_option( 'woocommerce_tax_display_shop' , false ); // (WooCommerce) Display Prices in the Shop
            $wholesale_tax_display_shop   = get_option( 'wwpp_settings_incl_excl_tax_on_wholesale_price' , false ); // (Wholesale) Display Prices in the Shop
            $tax_exempted                 = get_option( 'wwpp_settings_tax_exempt_wholesale_users' , false );

            $wholesale_role_tax_option_mapping = get_option( WWPP_OPTION_WHOLESALE_ROLE_TAX_OPTION_MAPPING , array() );
            if ( !is_array( $wholesale_role_tax_option_mapping ) )
                $wholesale_role_tax_option_mapping = array();

            if ( array_key_exists( $user_wholesale_role[ 0 ] , $wholesale_role_tax_option_mapping ) )
                $tax_exempted = $wholesale_role_tax_option_mapping[ $user_wholesale_role[ 0 ] ][ 'tax_exempted' ];

            if ( $tax_exempted === 'yes' ) {

                // Wholesale user is tax exempted so no matter what, the user will always see tax exempted prices
                $filtered_price = WWP_Helper_Functions::wwp_formatted_price( WWP_Helper_Functions::wwp_get_price_excluding_tax( $product , array( 'qty' => 1 , 'price' => $price ) ) , $wc_price_arg );

            } else {

                if ( $wholesale_tax_display_shop === 'incl' )
                    $filtered_price = WWP_Helper_Functions::wwp_formatted_price( WWP_Helper_Functions::wwp_get_price_including_tax( $product , array( 'qty' => 1 , 'price' => $price ) ) , $wc_price_arg );
                elseif ( $wholesale_tax_display_shop === 'excl' )
                    $filtered_price = WWP_Helper_Functions::wwp_formatted_price( WWP_Helper_Functions::wwp_get_price_excluding_tax( $product , array( 'qty' => 1 , 'price' => $price ) ) , $wc_price_arg );
                elseif ( empty( $wholesale_tax_display_shop ) ) {

                    if ( $woocommerce_tax_display_shop === 'incl' )
                        $filtered_price = WWP_Helper_Functions::wwp_formatted_price( WWP_Helper_Functions::wwp_get_price_including_tax( $product , array( 'qty' => 1 , 'price' => $price ) ) , $wc_price_arg );
                    else
                        $filtered_price = WWP_Helper_Functions::wwp_formatted_price( WWP_Helper_Functions::wwp_get_price_excluding_tax( $product , array( 'qty' => 1 , 'price' => $price ) ) , $wc_price_arg );
                    
                }

            }

            return apply_filters( 'wwpp_filter_product_shop_price_with_taxing_applied' , $filtered_price , $price , $product );

        } else
            return WWP_Helper_Functions::wwp_formatted_price( $price , $wc_price_arg ); // Else return the price
        
    }

    /**
     * Filter the text for the wholesale price title.
     *
     * @param $titleText
     *
     * @return mixed
     * @since 1.0.0
     */
    public function filterWholesalePriceTitleText ( $titleText ) {

        $settingTitleText = esc_attr( trim( get_option( 'wwpp_settings_wholesale_price_title_text' ) ) );
        return $settingTitleText;

    }

    /**
     * Used to show/hide original product price.
     * 
     * @since 1.14.0
     * @access public
     *
     * @param string     $original_price      Crossed out original price html.
     * @param float      $wholesale_price     wholesale price.
     * @param float      $price               Original price.
     * @param WC_Product $product             Product object.
     * @param array      $user_wholesale_role User wholesale role.
     * @return string Filtered crossed out original price html.
     */
    public function filter_product_original_price_visibility( $original_price , $wholesale_price , $price , $product , $user_wholesale_role ) {

        if ( get_option( 'wwpp_settings_hide_original_price' ) === "yes" )
            $original_price = '';
        
        return $original_price;

    }

    /**
     * Override the price suffix for wholesale users only.
     *
     * @param $priceDisplaySuffix
     * @param $userWholesaleRole
     *
     * @return mixed
     * @since 1.4.0
     */
    public function overrideWholesalePriceSuffix( $priceDisplaySuffix , $userWholesaleRole ) {

        if ( !empty( $userWholesaleRole ) ) {

            $newPriceSuffix = get_option( 'wwpp_settings_override_price_suffix' );

            return !empty( $newPriceSuffix ) ? ' <small class="woocommerce-price-suffix wholesale-price-suffix">' . $newPriceSuffix . '</small>' : $priceDisplaySuffix;

        }

        return $priceDisplaySuffix;

    }
    
    /**
     * Override the price suffix for regular prices viewed by wholesale customers.
     *
     * @since 1.14.7
     * @access public
     *
     * @param string     $priceSuffixHtml   Price suffix markup.
     * @param WC_Product $product           WC Product instance.
	 * @param string     $price             Product price.
	 * @param integer    $qty               Product quantity.
     * @param array      $userWholesaleRole Variable that contains wholesale role/s of the current user.
     * @return string Filtered price suffix markup.
     */
    public function overrideRegularPriceSuffixForWholesaleRoles( $priceSuffixHtml , $product , $userWholesaleRole ) {
        
        if ( !empty( $userWholesaleRole ) ) {

            $wholesaleSuffixForRegularPrice = get_option( 'wwpp_settings_override_price_suffix_regular_price' );

            return !empty( $wholesaleSuffixForRegularPrice ) ? ' <small class="woocommerce-price-suffix wholesale-user-regular-price-suffix">' . $wholesaleSuffixForRegularPrice . '</small>' : $priceSuffixHtml;

        }

        return $priceSuffixHtml;

    }

    /**
     * Set coupons availability to wholesale users.
     *
     * @since 1.5.0
     * @access public
     * 
     * @param $enabled
     * @param $userWholesaleRole
     * @return bool
     */
    public function toggleAvailabilityOfCouponsToWholesaleUsers ( $enabled , $userWholesaleRole ) {

        if ( get_option( 'wwpp_settings_disable_coupons_for_wholesale_users' ) == 'yes' && !empty( $userWholesaleRole ) )
            $enabled = false;

        return $enabled;

    }

    /**
     * There's a bug on wwpp where wholesale users can still avail coupons even if 'Disable Coupons For Wholesale Users' option is enabled.
     * They can do this by applying coupon to cart first before logging in as wholesale user.
     * Therefore when wholesale user visits cart/checkout pages, we check if 'Disable Coupons For Wholesale Users' is enabled.
     * If so then we remove coupons to the cart.
     *
     * @since 1.10.0
     * @access public
     * 
     * @param $userWholesaleRole
     */
    public function removeCouponsForWholesaleUsersWhenNecessary( $userWholesaleRole ) {

        if ( get_option( 'wwpp_settings_disable_coupons_for_wholesale_users' ) == 'yes' && !empty( $userWholesaleRole ) )
            WC()->cart->remove_coupons();

    }
    
    /**
     * Display quantity based discount markup on single product pages.
     *
     * @since 1.6.0
     * @since 1.7.0 Add Aelia currency switcher plugin integration
     * @access public
     * @see _printWholesalePricePerOrderQuantityTable
     *
     * @param $wholesalePriceHTML
     * @param $price
     * @param $product
     * @param $userWholesaleRole
     * @return string
     */
    public function displayOrderQuantityBasedWholesalePricing( $wholesalePriceHTML , $price , $product , $userWholesaleRole ) {

        // Only apply this to single product pages
        if ( !empty( $userWholesaleRole ) &&
             ( ( get_option( 'wwpp_settings_hide_quantity_discount_table' , false ) !== 'yes' && is_product() && in_array( WWP_Helper_Functions::wwp_get_product_type( $product ) , array( 'simple' , 'composite' , 'bundle' , 'variation' ) ) ) ||
               apply_filters( 'render_order_quantity_based_wholesale_pricing' , false ) ) ) {
            
            $productId = WWP_Helper_Functions::wwp_get_product_id( $product );

            // Since quantity based wholesale pricing relies on the presence of the wholesale price at a product level
            // We need to get the original wholesale price ( per product level ), we don't need to filter the wholesale price.
            $wholesalePrice = WWP_Wholesale_Prices::get_product_raw_wholesale_price( $productId , $userWholesaleRole );
            
            if ( !empty( $wholesalePrice ) ) {

                $enabled = get_post_meta( $productId , WWPP_POST_META_ENABLE_QUANTITY_DISCOUNT_RULE , true );

                $mapping = get_post_meta( $productId , WWPP_POST_META_QUANTITY_DISCOUNT_RULE_MAPPING , true );
                if ( !is_array( $mapping ) )
                    $mapping = array();

                // Table view
                $mappingTableHtml = '';

                if ( $enabled == 'yes' && !empty( $mapping ) ) {
                    ob_start();

                    /*
                     * Get the base currency mapping. The base currency mapping well determine what wholesale
                     * role and range pairing a product has wholesale price with.
                     */
                    $baseCurrencyMapping = $this->_getBaseCurrencyMapping( $mapping , $userWholesaleRole );

                    if ( WWPP_ACS_Integration_Helper::aelia_currency_switcher_active() ) {

                        $baseCurrency   = WWPP_ACS_Integration_Helper::get_product_base_currency( $productId );
                        $activeCurrency = get_woocommerce_currency();

                        // No point on doing anything if have no base currency mapping
                        if ( !empty( $baseCurrencyMapping ) ) {

                            if ( $baseCurrency == $activeCurrency ) {

                                /*
                                 * If active currency is equal to base currency, then we just need to pass
                                 * the base currency mapping.
                                 */
                                $this->_printWholesalePricePerOrderQuantityTable( $wholesalePrice , $baseCurrencyMapping , array() , $mapping , $product , $userWholesaleRole , true , $baseCurrency , $activeCurrency );

                            } else {

                                $specific_currency_mapping = $this->_getSpecificCurrencyMapping( $mapping , $userWholesaleRole , $activeCurrency , $baseCurrencyMapping );

                                $this->_printWholesalePricePerOrderQuantityTable( $wholesalePrice , $baseCurrencyMapping , $specific_currency_mapping , $mapping , $product , $userWholesaleRole , false , $baseCurrency , $activeCurrency );

                            }

                        }

                    } else {

                        // Default without Aelia currency switcher plugin

                        if ( !empty( $baseCurrencyMapping ) )
                            $this->_printWholesalePricePerOrderQuantityTable( $wholesalePrice , $baseCurrencyMapping , array() , $mapping , $product , $userWholesaleRole , true , get_woocommerce_currency() , get_woocommerce_currency() );

                    }

                    $mappingTableHtml = ob_get_clean();

                }

                $wholesalePriceHTML .= $mappingTableHtml;

            }

        }

        return $wholesalePriceHTML;

    }

    /**
     * Print wholesale pricing per order quantity table.
     *
     * @since 1.7.0
     * @since 1.7.1 Apply taxing on the wholesale price on the per order quantity wholesale pricing table.
     * @access private
     * @see getProductShopPriceWithTaxingApplied
     * 
     * @param $wholesalePrice
     * @param $baseCurrencyMapping
     * @param $specificCurrencyMapping
     * @param $mapping
     * @param $product
     * @param $userWholesaleRole
     * @param $isBaseCurrency
     * @param $baseCurrency
     * @param $activeCurrency
     */
    private function _printWholesalePricePerOrderQuantityTable( $wholesalePrice , $baseCurrencyMapping , $specificCurrencyMapping , $mapping , $product , $userWholesaleRole , $isBaseCurrency , $baseCurrency , $activeCurrency ) { ?>

        <table class="order-quantity-based-wholesale-pricing-view table-view">

            <thead>
                <tr>
                    <?php do_action( 'wwpp_action_before_wholesale_price_table_per_order_quantity_heading_view' , $mapping , $product , $userWholesaleRole ); ?>
                    <th><?php echo apply_filters( 'wwpp_filter_wholesale_price_table_per_order_quantity_qty_heading_txt' , __( 'Qty' , 'woocommerce-wholesale-prices-premium' ) );  ?></th>
                    <th><?php echo apply_filters( 'wwpp_filter_wholesale_price_table_per_order_quantity_price_heading_txt' , __( 'Price' , 'woocommerce-wholesale-prices-premium' ) );  ?></th>
                    <?php do_action( 'wwpp_action_after_wholesale_price_table_per_order_quantity_heading_view' , $mapping , $product , $userWholesaleRole ); ?>
                </tr>
            </thead>

            <tbody>

                <?php
                if ( !$isBaseCurrency ) {

                    // Specific currency

                    foreach ( $baseCurrencyMapping as $baseMap ) {

                        /*
                         * Even if this is a not a base currency, we will still rely on the base currency "RANGE".
                         * Because some range that are present on the base currency, may not be present in this current currency.
                         * But this current currency still has a wholesale price for that range, its wholesale price will be derived
                         * from base currency wholesale price by converting it to this current currency.
                         *
                         * Also if a wholesale price is set for this current currency range ( ex. 10 - 20 ) but that range
                         * is not present on the base currency mapping. We don't recognize this specific product on this range
                         * ( 10 - 20 ) as having wholesale price. User must set wholesale price on the base currency for the
                         * 10 - 20 range for this to be recognized as having a wholesale price.
                         */

                        $qty = $baseMap[ 'start_qty' ];

                        if ( !empty( $baseMap[ 'end_qty' ] ) )
                            $qty .= ' - ' . $baseMap[ 'end_qty' ];
                        else
                            $qty .= '+';

                        $price = '';

                        /*
                         * First check if a price is set for this wholesale role : range pair in the specific currency mapping.
                         * If wholesale price is present, then use it.
                         */
                        foreach ( $specificCurrencyMapping as $specificMap ) {

                            if ( $specificMap[ $activeCurrency . '_start_qty' ] == $baseMap[ 'start_qty' ] && $specificMap[ $activeCurrency . '_end_qty' ] == $baseMap[ 'end_qty' ] ) {
                                
                                if ( isset( $specificMap[ 'price_type' ] ) ) {

                                    if ( $specificMap[ 'price_type' ] == 'fixed-price' )
                                        $price = WWP_Helper_Functions::wwp_formatted_price( $specificMap[ $activeCurrency . '_wholesale_price' ] , array( 'currency' => $activeCurrency ) );
                                    elseif ( $specificMap[ 'price_type' ] == 'percent-price' ) {
                                        
                                        $price = $wholesalePrice - ( ( $specificMap[ $activeCurrency . '_wholesale_price' ] / 100 ) * $wholesalePrice );
                                        $price = WWP_Helper_Functions::wwp_formatted_price( $price  , array( 'currency' => $activeCurrency ) );
                                        
                                    }

                                } else
                                    $price = WWP_Helper_Functions::wwp_formatted_price( $specificMap[ $activeCurrency . '_wholesale_price' ] , array( 'currency' => $activeCurrency ) );

                            }
                            
                        }

                        /*
                         * Now if there is no mapping for this specific wholesale role : range pair inn the specific currency mapping,
                         * since this range is present on the base map mapping. We derive the price by converting the price set on the
                         * base currency mapping to this active currency.
                         */
                        if ( !$price ) {
                            
                            if ( isset( $baseMap[ 'price_type' ] ) ) {
                                
                                if ( $baseMap[ 'price_type' ] == 'fixed-price' )
                                    $price = WWPP_ACS_Integration_Helper::convert( $baseMap[ 'wholesale_price' ] , $activeCurrency , $baseCurrency );
                                elseif ( $baseMap[ 'price_type' ] == 'percent-price' ) {

                                    $price = $wholesalePrice - ( ( $baseMap[ 'wholesale_price' ] / 100 ) * $wholesalePrice );
                                    $price = WWPP_ACS_Integration_Helper::convert( $price , $activeCurrency , $baseCurrency );

                                }
                                
                            } else
                                $price = WWPP_ACS_Integration_Helper::convert( $baseMap[ 'wholesale_price' ] , $activeCurrency , $baseCurrency );

                            $price = $this->getProductShopPriceWithTaxingApplied( $product , $price , array( 'currency' => $activeCurrency ) , $userWholesaleRole );
                            
                        } ?>

                        <tr>
                            <?php do_action( 'wwpp_action_before_wholesale_price_table_per_order_quantity_entry_view' , $baseMap , $product , $userWholesaleRole ); ?>
                            <td><?php echo $qty; ?></td>
                            <td><?php echo $price; ?></td>
                            <?php do_action( 'wwpp_action_after_wholesale_price_table_per_order_quantity_entry_view' , $baseMap , $product , $userWholesaleRole ); ?>
                        </tr>

                    <?php }

                } else {

                    /*
                     * Base currency.
                     * Also the default if Aelia currency switcher plugin isn't active.
                     */
                    foreach ( $baseCurrencyMapping as $map ) {

                        $qty = $map[ 'start_qty' ];

                        if ( !empty( $map[ 'end_qty' ] ) )
                            $qty .= ' - ' . $map[ 'end_qty' ];
                        else
                            $qty .= '+';
                        
                        if ( isset( $map[ 'price_type' ] ) ) {
                            
                            if ( $map[ 'price_type' ] == 'fixed-price' )
                                $price = $this->getProductShopPriceWithTaxingApplied( $product , $map[ 'wholesale_price' ] , array( 'currency' => $baseCurrency ) , $userWholesaleRole );
                            elseif ( $map[ 'price_type' ] == 'percent-price' ) {

                                $price = $wholesalePrice - ( ( $map[ 'wholesale_price' ] / 100 ) * $wholesalePrice );
                                $price = $this->getProductShopPriceWithTaxingApplied( $product , $price , array( 'currency' => $baseCurrency ) , $userWholesaleRole );

                            }

                        } else
                            $price = $this->getProductShopPriceWithTaxingApplied( $product , $map[ 'wholesale_price' ] , array( 'currency' => $baseCurrency ) , $userWholesaleRole ); ?>

                        <tr>
                            <?php do_action( 'wwpp_action_before_wholesale_price_table_per_order_quantity_entry_view' , $map , $product , $userWholesaleRole ); ?>
                            <td><?php echo $qty; ?></td>
                            <td><?php echo $price; ?></td>
                            <?php do_action( 'wwpp_action_after_wholesale_price_table_per_order_quantity_entry_view' , $map , $product , $userWholesaleRole ); ?>
                        </tr>

                    <?php }

                } ?>

            </tbody>

        </table><!--.order-quantity-based-wholesale-pricing-view table-view-->

    <?php

    }

    /**
     * Apply quantity based discount on products on cart.
     *
     * @since 1.6.0
     * @since 1.7.0 Add Aelia currency switcher plugin integration
     *
     * @param $wholesalePrice
     * @param $productID
     * @param $userWholesaleRole
     * @param $cartItem
     * @return mixed
     */
    public function applyOrderQuantityBasedWholesalePricing( $wholesalePrice , $productID , $userWholesaleRole , $cartItem ) {

        // Quantity based discount depends on a wholesale price being set on the per product level
        // If none is set, then, quantity based discount will not be applied even if it is defined
        if ( !empty( $wholesalePrice ) && !empty( $userWholesaleRole ) ) {

            $enabled = get_post_meta( $productID , WWPP_POST_META_ENABLE_QUANTITY_DISCOUNT_RULE , true );

            $mapping = get_post_meta( $productID , WWPP_POST_META_QUANTITY_DISCOUNT_RULE_MAPPING , true );
            if ( !is_array( $mapping ) )
                $mapping = array();

            if ( $enabled == 'yes' && !empty( $mapping ) ) {

                /*
                 * Get the base currency mapping. The base currency mapping well determine what wholesale
                 * role and range pairing a product has wholesale price with.
                 */
                $baseCurrencyMapping = $this->_getBaseCurrencyMapping( $mapping , $userWholesaleRole );

                if ( WWPP_ACS_Integration_Helper::aelia_currency_switcher_active() ) {

                    $baseCurrency   = WWPP_ACS_Integration_Helper::get_product_base_currency( $productID );
                    $activeCurrency = get_woocommerce_currency();

                    if ( $baseCurrency == $activeCurrency ) {

                        $wholesalePrice = $this->_getWholesalePriceFromMapping( $wholesalePrice , $baseCurrencyMapping , array() , $cartItem , $baseCurrency , $activeCurrency , true );

                    } else {

                        // Get specific currency mapping
                        $specific_currency_mapping = $this->_getSpecificCurrencyMapping( $mapping , $userWholesaleRole , $activeCurrency , $baseCurrencyMapping );

                        $wholesalePrice = $this->_getWholesalePriceFromMapping( $wholesalePrice , $baseCurrencyMapping , $specific_currency_mapping , $cartItem , $baseCurrency , $activeCurrency , false );

                    }

                } else {

                    $wholesalePrice = $this->_getWholesalePriceFromMapping( $wholesalePrice , $baseCurrencyMapping , array() , $cartItem , get_woocommerce_currency() , get_woocommerce_currency() , true );

                }

            } // if ( $enabled == 'yes' && !empty( $mapping ) )

        }

        return $wholesalePrice;

    }

    /**
     * Get the wholesale price of a wholesale role for the appropriate range from the wholesale price per order
     * quantity mapping that is appropriate for the current items on the current wholesale user's cart.
     *
     * @since 1.7.0
     *
     * @param $wholesalePrice
     * @param $baseCurrencyMapping
     * @param $specificCurrencyMapping
     * @param $cartItem
     * @param $baseCurrency
     * @param $activeCurrency
     * @param $isBaseCurrency
     * @return float|string
     */
    private function _getWholesalePriceFromMapping( $wholesalePrice , $baseCurrencyMapping , $specificCurrencyMapping , $cartItem , $baseCurrency , $activeCurrency , $isBaseCurrency ) {

        if ( !$isBaseCurrency ) {

            foreach ( $baseCurrencyMapping as $baseMap ) {

                $price = "";

                /*
                 * First check if a price is set for this wholesale role : range pair in the specific currency mapping.
                 * If wholesale price is present, then use it.
                 */
                foreach ( $specificCurrencyMapping as $specificMap ) {

                    if ( $cartItem[ 'quantity' ] >= $specificMap[ $activeCurrency . '_start_qty' ] &&
                        ( empty( $specificMap[ $activeCurrency . '_end_qty' ] ) || $cartItem[ 'quantity' ] <= $specificMap[ $activeCurrency . '_end_qty' ] ) &&
                        $specificMap[ $activeCurrency . '_wholesale_price' ] != '' ) {

                            if ( isset( $specificMap[ 'price_type' ] ) ) {

                                if ( $specificMap[ 'price_type' ] == 'fixed-price' )
                                    $price = $specificMap[ $activeCurrency . '_wholesale_price' ];
                                elseif ( $specificMap[ 'price_type' ] == 'percent-price' )                                    
                                    $price = round( $wholesalePrice - ( ( $specificMap[ $activeCurrency . '_wholesale_price' ] / 100 ) * $wholesalePrice ) , 2 );
                                
                            } else
                                $price = $specificMap[ $activeCurrency . '_wholesale_price' ];

                    }

                }

                /*
                 * Now if there is no mapping for this specific wholesale role : range pair inn the specific currency mapping,
                 * since this range is present on the base map mapping. We derive the price by converting the price set on the
                 * base currency mapping to this active currency.
                 */
                if ( !$price ) {

                    if ( $cartItem[ 'quantity' ] >= $baseMap[ 'start_qty' ] &&
                        ( empty( $baseMap[ 'end_qty' ] ) || $cartItem[ 'quantity' ] <= $baseMap[ 'end_qty' ] ) &&
                        $baseMap[ 'wholesale_price' ] != '' ) {

                        if ( isset( $baseMap[ 'price_type' ] ) ) {

                            if ( $baseMap[ 'price_type' ] == 'fixed-price' )
                                $price = WWPP_ACS_Integration_Helper::convert( $baseMap[ 'wholesale_price' ] , $activeCurrency , $baseCurrency );
                            elseif ( $baseMap[ 'price_type' ] == 'percent-price' ) {

                                $price = round( $wholesalePrice - ( ( $baseMap[ 'wholesale_price' ] / 100 ) * $wholesalePrice ) , 2 );
                                $price = WWPP_ACS_Integration_Helper::convert( $price , $activeCurrency , $baseCurrency );
                                
                            }

                        } else
                            $price = WWPP_ACS_Integration_Helper::convert( $baseMap[ 'wholesale_price' ] , $activeCurrency , $baseCurrency );

                    }

                }

                if ( $price ) {

                    $wholesalePrice = $price;
                    break;

                }

            }

        } else {

            foreach ( $baseCurrencyMapping as $map ) {

                if ( $cartItem[ 'quantity' ] >= $map[ 'start_qty' ] &&
                    ( empty( $map[ 'end_qty' ] ) || $cartItem[ 'quantity' ] <= $map[ 'end_qty' ] ) &&
                    $map[ 'wholesale_price' ] != '' ) {
                    
                    if ( isset( $map[ 'price_type' ] ) ) {

                        if ( $map[ 'price_type' ] == 'fixed-price' )
                            $wholesalePrice = $map[ 'wholesale_price' ];
                        elseif ( $map[ 'price_type' ] == 'percent-price' )
                            $wholesalePrice = round( $wholesalePrice - ( ( $map[ 'wholesale_price' ] / 100 ) * $wholesalePrice ) , 2 );

                    } else
                        $wholesalePrice = $map[ 'wholesale_price' ];

                    break;

                }

            }

        }

        return $wholesalePrice;

    }

    /**
     * Get the base currency mapping from the wholesale price per order quantity mapping.
     *
     * @since 1.7.0
     *
     * @param $mapping
     * @param $userWholesaleRole
     * @return array
     */
    private function _getBaseCurrencyMapping( $mapping , $userWholesaleRole ) {

        $baseCurrencyMapping = array();

        foreach ( $mapping as $map ) {

            // Skip non base currency mapping
            if ( array_key_exists( 'currency' , $map ) )
                continue;

            // Skip mapping not meant for the current user wholesale role
            if ( $userWholesaleRole[ 0 ] != $map[ 'wholesale_role' ] )
                continue;

            $baseCurrencyMapping[] = $map;

        }

        return $baseCurrencyMapping;

    }

    /**
     * Get the specific currency mapping from the wholesale price per order quantity mapping.
     *
     * @since 1.7.0
     *
     * @param $mapping
     * @param $userWholesaleRole
     * @param $activeCurrency
     * @param $baseCurrencyMapping
     * @return array
     */
    private function _getSpecificCurrencyMapping( $mapping , $userWholesaleRole , $activeCurrency , $baseCurrencyMapping ) {

        // Get specific currency mapping
        $specificCurrencyMapping = array();

        foreach ( $mapping as $map ) {

            // Skip base currency
            if ( !array_key_exists( 'currency' , $map ) )
                continue;

            // Skip mappings that are not for the active currency
            if ( !array_key_exists( $activeCurrency . '_wholesale_role' , $map ) )
                continue;

            // Skip mapping not meant for the currency user wholesale role
            if ( $userWholesaleRole[ 0 ] != $map[ $activeCurrency . '_wholesale_role' ] )
                continue;

            // Only extract out mappings for this current currency that has equivalent mapping
            // on the base currency.
            foreach ( $baseCurrencyMapping as $base_map ) {

                if ( $base_map[ 'start_qty' ] == $map[ $activeCurrency . '_start_qty' ] && $base_map[ 'end_qty' ] == $map[ $activeCurrency . '_end_qty' ] ) {

                    $specificCurrencyMapping[] = $map;
                    break;

                }

            }

        }

        return $specificCurrencyMapping;

    }

    /**
     * Apply product category level wholesale discount. Only applies when a product has no wholesale price set.
     *
     * @since 1.0.5
     * @param $wholesalePrice
     * @param $productID
     * @param $userWholesaleRole
     * @return mixed
     */
    public function applyProductCategoryWholesaleDiscount( $wholesalePrice , $productID , $userWholesaleRole ) {

        if ( empty( $wholesalePrice ) && !empty( $userWholesaleRole ) ) {

            $product       = wc_get_product( $productID );
            $product_price = get_option( 'wwpp_settings_explicitly_use_product_regular_price_on_discount_calc' ) == 'yes' ? $product->get_regular_price() : $product->get_price();
            $post_id       = ( WWP_Helper_Functions::wwp_get_product_type( $product ) === 'variation' ) ? WWP_Helper_Functions::wwp_get_parent_variable_id( $product ) : $productID;

            if ( !is_null( $post_id ) ) {

                $terms = get_the_terms( $post_id , 'product_cat' );
                if ( !is_array( $terms ) )
                    $terms = array();

                $lowest_discount = null;
                $highest_discount = null;

                foreach ( $terms as $term ) {

                    $category_wholesale_prices = get_option( 'taxonomy_' . $term->term_id );

                    if ( is_array( $category_wholesale_prices ) && array_key_exists( $userWholesaleRole[ 0 ] . '_wholesale_discount' , $category_wholesale_prices ) ) {

                        $curr_discount = $category_wholesale_prices[ $userWholesaleRole[ 0 ] . '_wholesale_discount' ];

                        if ( !empty( $curr_discount ) ) {

                            if ( is_null( $lowest_discount ) || $curr_discount < $lowest_discount )
                                $lowest_discount = $curr_discount;

                            if ( is_null( $highest_discount ) || $curr_discount > $highest_discount )
                                $highest_discount = $curr_discount;

                        }

                    }

                }

                $category_wholsale_price_logic = get_option( 'wwpp_settings_multiple_category_wholesale_discount_logic' );

                if ( $category_wholsale_price_logic == 'highest' ) {

                    if ( !is_null( $highest_discount ) )
                        $wholesalePrice = round( $product_price - ( $product_price * ( $highest_discount / 100 ) ) , 2 );

                } else {

                    if ( !is_null( $lowest_discount ) )
                        $wholesalePrice = round( $product_price - ( $product_price * ( $lowest_discount / 100 ) ) , 2 );

                }

                if ( $wholesalePrice < 0 )
                    $wholesalePrice = 0;

            }

        }

        return $wholesalePrice;

    }

    /**
     * Apply wholesale role general discount to the product being purchased by this user.
     * Only applies if
     * General discount is set for this wholesale role
     * No category level discount is set
     * No wholesale price is set
     *
     * @since 1.2.0
     * @param $wholesalePrice
     * @param $productID
     * @param $userWholesaleRole
     * @return string
     */
    public function applyWholesaleRoleGeneralDiscount ( $wholesalePrice , $productID , $userWholesaleRole ) {

        if ( empty( $wholesalePrice ) && !empty( $userWholesaleRole ) ) {

            $roleDiscount = get_option( WWPP_OPTION_WHOLESALE_ROLE_GENERAL_DISCOUNT_MAPPING );
            if ( !is_array( $roleDiscount ) )
                $roleDiscount = array();

            if ( array_key_exists( $userWholesaleRole[ 0 ] , $roleDiscount ) && is_numeric( $roleDiscount[ $userWholesaleRole[ 0 ] ] ) ) {
                
                $product        = wc_get_product( $productID );
                $product_price  = get_option( 'wwpp_settings_explicitly_use_product_regular_price_on_discount_calc' ) == 'yes' ? $product->get_regular_price() : $product->get_price();
                $wholesalePrice = round( $product_price - ( $product_price * ( $roleDiscount[ $userWholesaleRole[ 0 ] ] / 100 ) ) , 2 );

            }

        }

        return $wholesalePrice;

    }

    /**
     * Filter callback that alters the product price, it embeds the wholesale price of a product for a wholesale user ( Custom product types ).
     *
     * @since 1.8.0 Partial support for composite product.
     * @since 1.9.0 Partial support for bundle product.
     * @access public
     *
     * @param $price
     * @param $product
     * @param $userWholesaleRole
     * @return mixed
     */
    public function wholesalePriceHTMLFilter( $price , $product , $userWholesaleRole ) {

        if ( !empty( $userWholesaleRole ) && !empty( $price ) ) {

            $wholesalePrice = '';

            if ( in_array( WWP_Helper_Functions::wwp_get_product_type( $product ) , array( 'composite' , 'bundle' ) ) ) {

                $wholesalePrice = WWP_Wholesale_Prices::get_product_wholesale_price_on_shop( WWP_Helper_Functions::wwp_get_product_id( $product ) , $userWholesaleRole );

                if ( strcasecmp( $wholesalePrice , '' ) != 0 )
                    $wholesalePrice = WWP_Helper_Functions::wwp_formatted_price( $wholesalePrice ) . apply_filters( 'wwp_filter_wholesale_price_display_suffix' , $product->get_price_suffix() );
                
            }

            if ( strcasecmp( $wholesalePrice , '' ) != 0 ) {

                $wholesalePriceHTML = apply_filters( 'wwp_product_original_price' , '<del class="original-computed-price">' . $price . '</del>' , $wholesalePrice , $price , $product , $userWholesaleRole );

                $wholesalePriceTitleText = __( 'Wholesale Price:' , 'woocommerce-wholesale-prices-premium' );
                $wholesalePriceTitleText = apply_filters( 'wwp_filter_wholesale_price_title_text' , $wholesalePriceTitleText );

                $wholesalePriceHTML .= '<span style="display: block;" class="wholesale_price_container">
                                            <span class="wholesale_price_title">' . $wholesalePriceTitleText . '</span>
                                            <ins>' . $wholesalePrice . '</ins>
                                        </span>';

                return apply_filters( 'wwp_filter_wholesale_price_html' , $wholesalePriceHTML , $price , $product , $userWholesaleRole , $wholesalePriceTitleText , $wholesalePrice );

            }

        }

        return $price;

    }

    /**
     * Apply wholesale price upon adding product to cart ( Custom Product Types ).
     *
     * @since 1.8.0
     * @since 1.15.0 Use 'get_product_wholesale_price_on_cart' function of class WWP_Wholesale_Prices.
     * @access public
     *
     * @param $wholesale_price
     * @param $value
     * @param $user_wholesale_role
     * @return string
     */
    public function applyCustomProductTypeWholesalePrice( $wholesale_price , $value , $user_wholesale_role ) {

        if ( in_array( WWP_Helper_Functions::wwp_get_product_type( $value[ 'data' ] ) , array( 'composite' , 'bundle' ) ) )
            $wholesale_price = WWP_Wholesale_Prices::get_product_wholesale_price_on_cart( WWP_Helper_Functions::wwp_get_product_id( $value[ 'data' ] ) , $user_wholesale_role , $value );
        
        return $wholesale_price;

    }


    
    
    /*
    |-------------------------------------------------------------------------------------------------------------------
    | Helper Functions
    |-------------------------------------------------------------------------------------------------------------------
    */

    /**
     * Get inner html of a DOMNode object.
     *
     * @param $node
     * @return string
     *
     * @since 1.4.1
     */
    public function getNodeInnerHTML( $node ) {

        $innerHTML= '';
        $children = $node->childNodes;
        foreach ( $children as $child )
            $innerHTML .= $child->ownerDocument->saveXML( $child );

        return $innerHTML;

    }

}
