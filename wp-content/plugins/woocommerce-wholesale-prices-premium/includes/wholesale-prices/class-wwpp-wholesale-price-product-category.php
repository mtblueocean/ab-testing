<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WWPP_Wholesale_Price_Product_Category' ) ) {

    /**
     * Model that houses the logic of applying product category level wholesale pricing.
     * 
     * @since 1.14.0
     */
    class WWPP_Wholesale_Price_Product_Category {

        /*
        |--------------------------------------------------------------------------
        | Class Properties
        |--------------------------------------------------------------------------
        */

        /**
         * Property that holds the single main instance of WWPP_Wholesale_Price_Product_Category.
         *
         * @since 1.14.0
         * @access private
         * @var WWPP_Wholesale_Price_Product_Category
         */
        private static $_instance;
        
        /**
         * Model that houses the logic of retrieving information relating to wholesale role/s of a user.
         *
         * @since 1.14.0
         * @access private
         * @var WWPP_Wholesale_Roles
         */
        private $_wwpp_wholesale_roles;

        /**
         * Model that houses logic of wholesale prices.
         * 
         * @since 1.14.0
         * @access private
         * @var WWPP_Wholesale_Prices
         */
        private $_wwpp_wholesale_prices;



        
        /*
        |--------------------------------------------------------------------------
        | Class Methods
        |--------------------------------------------------------------------------
        */

        /**
         * WWPP_Wholesale_Price_Product_Category constructor.
         *
         * @since 1.14.0
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_Wholesale_Price_Product_Category model.
         */
        public function __construct( $dependencies ) {

            $this->_wwpp_wholesale_roles  = $dependencies[ 'WWPP_Wholesale_Roles' ];
            $this->_wwpp_wholesale_prices = $dependencies[ 'WWPP_Wholesale_Prices' ];
            
        }

        /**
         * Ensure that only one instance of WWPP_Wholesale_Price_Product_Category is loaded or can be loaded (Singleton Pattern).
         *
         * @since 1.14.0
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_Wholesale_Price_Product_Category model.
         * @return WWPP_Wholesale_Price_Product_Category
         */
        public static function instance( $dependencies ) {

            if ( !self::$_instance instanceof self )
                self::$_instance = new self( $dependencies );

            return self::$_instance;

        }
        
        /**
         * Render order quantity based wholesale discount per category level table markup on product single page.
         * 
         * @since 1.11.0
         * @since 1.14.0 Refactor codebase and move to its own model.
         * @access public
         * 
         * @param string     $wholesale_price_html Wholesale price html.
         * @param float      $price                Raw price. 
         * @param WC_Product $product              Product object.
         * @param array      $user_wholesale_role  Array of wholesale roles of a user.
         * @return string Filtered wholesale price html.
         */
        public function render_order_quantity_based_wholesale_discount_per_category_level_table_markup( $wholesale_price_html , $price , $product , $user_wholesale_role ) {

            // Only apply this to single product pages
            if ( !empty( $user_wholesale_role ) && 
                 ( ( get_option( 'wwpp_settings_hide_quantity_discount_table' , false ) !== 'yes' && is_product() && ( in_array( WWP_Helper_Functions::wwp_get_product_type( $product ) , array( 'simple' , 'variation' , 'composite' , 'bundle' ) ) ) ) ||
                  apply_filters( 'render_order_quantity_based_wholesale_discount_per_category_level_table_markup' , false ) ) ) {
                
                $product_id = WWP_Helper_Functions::wwp_get_product_id( $product );
                $post_id    = ( WWP_Helper_Functions::wwp_get_product_type( $product ) === 'variation' ) ? WWP_Helper_Functions::wwp_get_parent_variable_id( $product ) : WWP_Helper_Functions::wwp_get_product_id( $product );
                
                // We need to check if there are "any sort" of wholesale pricing on the per product level
                // If there is, we skip this then
                
                // Get the original wholesale price ( per product level ), we don't need to filter the wholesale price.
                // We only need to get if the per product level wholesale price is set
                // No need to check the per quantity wholesale price per product level coz it depends on the per product level wholesale price
                // if per product level wholesale price, is not set, meaning per quantity wholesale price per product level is void too even if set
                $wholesale_price = WWP_Wholesale_Prices::get_product_raw_wholesale_price( $product_id , $user_wholesale_role );
                if ( !empty( $wholesale_price ) )
                    return $wholesale_price_html;
                
                // Get the base category term id
                // We need the admin to specify a base discount for a category in order for this feature to take effect
                $term_id        = $this->get_base_term_id( $post_id , $user_wholesale_role );
                $enable_feature = get_term_meta( $term_id , 'wwpp_enable_quantity_based_wholesale_discount' , true );

                if ( $enable_feature === 'yes' ) {

                    $qbwd_mapping = get_term_meta( $term_id , 'wwpp_quantity_based_wholesale_discount_mapping' , true );
                    if ( !is_array( $qbwd_mapping ) )
                        $qbwd_mapping = array();
                    
                    // Get category level per order quantity wholesale discount
                    $mapping_table_html    = $this->get_cat_level_per_order_quantity_wholesale_discount_table_markup( $qbwd_mapping , $product , $user_wholesale_role );
                    $wholesale_price_html .= $mapping_table_html;
                    
                }

            }

            return $wholesale_price_html;

        }

        /**
         * Apply order quantity based wholesale price per category level.
         *
         * @since 1.11.0
         * @since 1.14.0 Refactor codebase and move to its own model.
         * @access public
         *
         * @param float $wholesale_price     Raw wholesale price.
         * @param int   $product_id          Product id.
         * @param array $user_wholesale_role User wholesale role.
         * @param array $cart_item           Cart item data.
         * @return float Filtered raw wholesale price.
         */
        public function apply_order_quantity_based_wholesale_discount_per_category_level( $wholesale_price , $product_id , $user_wholesale_role , $cart_item ) {

            if ( empty( $wholesale_price ) && !empty( $user_wholesale_role ) ) {

                $product        = wc_get_product( $product_id );
                $post_id        = ( WWP_Helper_Functions::wwp_get_product_type( $product ) === 'variation' ) ? WWP_Helper_Functions::wwp_get_parent_variable_id( $product ) : $product_id;
                $term_id        = $this->get_base_term_id( $post_id , $user_wholesale_role );
                $enable_feature = get_term_meta( $term_id , 'wwpp_enable_quantity_based_wholesale_discount' , true );
                
                if ( $enable_feature == 'yes' ) {

                    $qbwd_mapping = get_term_meta( $term_id , 'wwpp_quantity_based_wholesale_discount_mapping' , true );
                    if ( !is_array( $qbwd_mapping ) )
                        $qbwd_mapping = array();
                    
                    $calculated_wholesale_price = $this->get_cat_level_per_order_quantity_wholesale_price( $term_id , $qbwd_mapping , $product_id , $user_wholesale_role , $cart_item );

                    if ( !is_null( $calculated_wholesale_price ) )
                        $wholesale_price = $calculated_wholesale_price;

                }

            }

            return $wholesale_price;

        }




        /*
        |--------------------------------------------------------------------------------------------------------------------
        | Helper Functions
        |--------------------------------------------------------------------------------------------------------------------
        */

        /**
         * Get the base term id of the given product depending on the 'wwpp_settings_multiple_category_wholesale_discount_logic' option.
         * 
         * @since 1.11.0
         * @since 1.14.0 Refactor codebase and move to its own model.
         * @access public
         *
         * @param int   $product_id          Product id.
         * @param array $user_wholesale_role User wholesale role.
         * @return int Base term id.
         */
        public function get_base_term_id( $product_id , $user_wholesale_role ) {

            $terms = get_the_terms( $product_id , 'product_cat' );
            if ( !is_array( $terms ) )
                $terms = array();

            $lowest_discount          = null;
            $highest_discount         = null;
            $lowest_discount_term_id  = null;
            $highest_discount_term_id = null;

            foreach ( $terms as $term ) {

                $category_wholesale_prices = get_option( 'taxonomy_' . $term->term_id );

                if ( is_array( $category_wholesale_prices ) && array_key_exists( $user_wholesale_role[ 0 ] . '_wholesale_discount' , $category_wholesale_prices ) ) {

                    $curr_discount = $category_wholesale_prices[ $user_wholesale_role[ 0 ] . '_wholesale_discount' ];

                    if ( !empty( $curr_discount ) ) {

                        if ( is_null( $lowest_discount ) || $curr_discount < $lowest_discount ) {

                            $lowest_discount         = $curr_discount;
                            $lowest_discount_term_id = $term->term_id;

                        }

                        if ( is_null( $highest_discount ) || $curr_discount > $highest_discount ) {

                            $highest_discount         = $curr_discount;
                            $highest_discount_term_id = $term->term_id;

                        }

                    }

                }

            }

            $category_wholsale_price_logic = get_option( 'wwpp_settings_multiple_category_wholesale_discount_logic' );

            if ( $category_wholsale_price_logic == 'highest' )
                return $highest_discount_term_id;
            else
                return $lowest_discount_term_id;

        }

        /**
         * Get per order quantity wholesale discount per category level table markup to be displayed on the single product page on the front page.
         *
         * @since 1.11.0
         * @since 1.14.0 Refactor codebase and move to its own model.
         * @access public
         *
         * @param array      $qbwd_mapping        Mapping data.
         * @param WC_Prodcut $product             Product object.
         * @param array      $user_wholesale_role User wholesale role.
         * @return string Discount table html markup.
         */
        public function get_cat_level_per_order_quantity_wholesale_discount_table_markup( $qbwd_mapping , $product , $user_wholesale_role ) {

            $product_active_price = get_option( 'wwpp_settings_explicitly_use_product_regular_price_on_discount_calc' ) == 'yes' ? $product->get_regular_price() : $product->get_price();
            $has_range_discount   = false;
            $mapping_table_html   = '';

            // Table view
            ob_start(); ?>

            <table class="order-quantity-based-wholesale-pricing-view table-view">
                <thead>
                    <tr>
                        <th><?php _e( 'Qty' , 'woocommerce-wholesale-prices-premium' );  ?></th>
                        <th><?php _e( 'Price' , 'woocommerce-wholesale-prices-premium' );  ?></th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ( $qbwd_mapping as $index => $mapping_data ) {
                        
                        if ( $user_wholesale_role[ 0 ] == $mapping_data[ 'wholesale-role' ] ) {

                            if ( !$has_range_discount )
                                $has_range_discount = true;

                            $product_computed_price = $product_active_price - ( ( $mapping_data[ 'wholesale-discount' ] / 100 ) * $product_active_price  );
                            $product_computed_price = WWP_Helper_Functions::wwp_formatted_price( $product_computed_price );
                            
                            if ( $mapping_data[ 'end-qty' ] != '' )
                                $qty_range = $mapping_data[ 'start-qty' ] . ' - ' . $mapping_data[ 'end-qty' ];
                            else
                                $qty_range = $mapping_data[ 'start-qty' ] . '+'; ?>
                            
                            <tr>
                                <td><?php echo $qty_range; ?></td>
                                <td><?php echo $product_computed_price; ?></td>
                            </tr>

                        <?php }

                    } ?>
                </tbody>
            </table>
                
            <?php $mapping_table_html = ob_get_clean();

            if ( $has_range_discount )
                return $mapping_table_html;
            else
                return '';
        
        }

        /**
         * Get product category level wholesale discount.
         *
         * @since 1.14.0
         * @access public
         *
         * @param int   $product_id Product id.
         * @param array $user_wholesale_role User wholesale roles.
         * @return float|boolean Wholesale price if there is one, false otherwise.
         */
        public function get_cat_level_wholesale_discount( $term_id , $product_id , $user_wholesale_role ) {

            $product                   = wc_get_product( $product_id );
            $product_price             = get_option( 'wwpp_settings_explicitly_use_product_regular_price_on_discount_calc' ) == 'yes' ? $product->get_regular_price() : $product->get_price();                
            $category_wholesale_prices = get_option( 'taxonomy_' . $term_id );

            if ( is_array( $category_wholesale_prices ) && array_key_exists( $user_wholesale_role[ 0 ] . '_wholesale_discount' , $category_wholesale_prices ) ) {

                $curr_discount   = $category_wholesale_prices[ $user_wholesale_role[ 0 ] . '_wholesale_discount' ];
                $wholesale_price = round( $product_price - ( $product_price * ( $curr_discount / 100 ) ) , 2 );

                return ( $wholesale_price < 0 ) ? 0 : $wholesale_price;

            } else
                return false;
            
        }

        /**
         * Get per order quantity wholesale discount per category level.
         *
         * @since 1.11.0
         * @since 1.14.0 Refactor codebase and move to its own model.
         * @since 1.14.5 Now it has improve support for per order quantity discount per category level.
         *               Composite or Bundled items are now being counted and applied per order quantity discount but in the context of the parent bundle or composite product.
         *               It's quantity won't be mixed up to the total count of the "other products under the same category" that is not a composite or bundle of the parent bundle or composite product ( that is independent products ). 
         * @access public
         *
         * @param array $qbwd_mapping        Mapping data.
         * @param int   $product_id          Product id.
         * @param array $user_wholesale_role User wholesale role.
         * @param array $cart_item           Cart item data.
         * @return float wholesale price if there is mapping entry, null if no mapping entry.
         */
        public function get_cat_level_per_order_quantity_wholesale_price( $term_id , $qbwd_mapping , $product_id , $user_wholesale_role , $cart_item ) {

            $cat_product_cart_items = 0;
            $cart_object            = WC()->cart;
            $product                = wc_get_product( $product_id );
            $product_active_price   = get_option( 'wwpp_settings_explicitly_use_product_regular_price_on_discount_calc' ) == 'yes' ? $product->get_regular_price() : $product->get_price();

            if ( isset( $cart_item[ 'bundled_by' ] ) || isset( $cart_item[ 'composite_parent' ] ) ) {

                foreach ( $cart_object->get_cart() as $cart_item_key => $cart_item_data ) {

                    if ( ( !isset( $cart_item_data[ 'bundled_by' ] ) || $cart_item_data[ 'bundled_by' ] !== $cart_item[ 'bundled_by' ] ) &&
                         ( !isset( $cart_item_data[ 'composite_parent' ] ) || $cart_item_data[ 'composite_parent' ] === $cart_item[ 'composite_parent' ] ) )
                         continue;

                    $product_id = ( WWP_Helper_Functions::wwp_get_product_type( $cart_item_data[ 'data' ] ) === 'variation' ) ? WWP_Helper_Functions::wwp_get_parent_variable_id( $cart_item_data[ 'data' ] ) : WWP_Helper_Functions::wwp_get_product_id( $cart_item_data[ 'data' ] );

                    if ( has_term( $term_id , 'product_cat' , $product_id ) )
                        $cat_product_cart_items += $cart_item_data[ 'quantity' ];
                    
                }

            } else {

                foreach ( $cart_object->get_cart() as $cart_item_key => $cart_item_data ) {

                    if ( isset( $cart_item_data[ 'bundled_by' ] ) || isset( $cart_item_data[ 'composite_parent' ] ) )
                        continue;

                    $product_id = ( WWP_Helper_Functions::wwp_get_product_type( $cart_item_data[ 'data' ] ) === 'variation' ) ? WWP_Helper_Functions::wwp_get_parent_variable_id( $cart_item_data[ 'data' ] ) : WWP_Helper_Functions::wwp_get_product_id( $cart_item_data[ 'data' ] );
                    
                    if ( has_term( $term_id , 'product_cat' , $product_id ) )
                        $cat_product_cart_items += $cart_item_data[ 'quantity' ];

                }

            }

            foreach ( $qbwd_mapping as $index => $mapping_data ) {
                
                if ( $user_wholesale_role[ 0 ] == $mapping_data[ 'wholesale-role' ] ) {

                    if ( $cat_product_cart_items >= $mapping_data[ 'start-qty' ] &&
                        ( empty( $mapping_data[ 'end-qty' ] ) || $cat_product_cart_items <= $mapping_data[ 'end-qty' ] ) &&
                        $mapping_data[ 'wholesale-discount' ] != '' ) {
                        
                        return round( $product_active_price - ( ( $mapping_data[ 'wholesale-discount' ] / 100 ) * $product_active_price  ) , 2 );

                    }

                }

            }

            return null; // Meaning no equivalent wholesale discount on mapping
            
        }

    }

}