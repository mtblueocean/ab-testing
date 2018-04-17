<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WWPP_Admin_Custom_Fields_Product_Category' ) ) {

    /**
     * Model that houses the logic of product category admin custom fields.
     *
     * @since 1.13.0
     */
    class WWPP_Admin_Custom_Fields_Product_Category {

        /*
        |--------------------------------------------------------------------------
        | Class Properties
        |--------------------------------------------------------------------------
        */

        /**
         * Property that holds the single main instance of WWPP_Admin_Custom_Fields_Product_Category.
         *
         * @since 1.13.0
         * @access private
         * @var WWPP_Admin_Custom_Fields_Product_Category
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
         * WWPP_Admin_Custom_Fields_Product_Category constructor.
         *
         * @since 1.13.0
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_Admin_Custom_Fields_Product_Category model.
         */
        public function __construct( $dependencies ) {

            $this->_wwpp_wholesale_roles = $dependencies[ 'WWPP_Wholesale_Roles' ];

        }

        /**
         * Ensure that only one instance of WWPP_Admin_Custom_Fields_Product_Category is loaded or can be loaded (Singleton Pattern).
         *
         * @since 1.13.0
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_Admin_Custom_Fields_Product_Category model.
         * @return WWPP_Admin_Custom_Fields_Product_Category
         */
        public static function instance( $dependencies ) {

            if ( !self::$_instance instanceof self )
                self::$_instance = new self( $dependencies );

            return self::$_instance;

        }
        



        /*
        |--------------------------------------------------------------------------
        | Set 'have_wholesale_price' meta
        |--------------------------------------------------------------------------
        */

        /**
         * Set 'have_wholesale_price' meta for products if they have no wholesale price directly set to them.
         * Meaning their wholesale price is determined via the product category with wholesale discount set.
         *
         * @since 1.13.0
         * @access public
         *
         * @param int     $post_id  Simple product id.
         * @param string  $role_key Wholesale role key.
         */
        public function set_have_wholesale_price_meta_prod_cat_wholesale_discount( $post_id , $role_key ) {

            update_post_meta( $post_id , $role_key . '_have_wholesale_price' , 'no' );

            $terms = get_the_terms( $post_id , 'product_cat' );
            if ( !is_array( $terms ) )
                $terms = array();

            foreach ( $terms as $term ) {

                $category_wholesale_prices = get_option( 'taxonomy_' . $term->term_id );

                if ( is_array( $category_wholesale_prices ) && array_key_exists( $role_key . '_wholesale_discount' , $category_wholesale_prices ) ) {

                    $curr_discount = $category_wholesale_prices[ $role_key . '_wholesale_discount' ];

                    if ( !empty( $curr_discount ) ) {

                        update_post_meta( $post_id , $role_key . '_have_wholesale_price' , 'yes' );

                        // Add additional meta to indicate that have wholesale price meta was set by the category
                        update_post_meta( $post_id , $role_key . '_have_wholesale_price_set_by_product_cat' , 'yes' );

                        break;

                    }

                }

            }

        }
        



        /*
        |--------------------------------------------------------------------------------------------------------------------
        | Wholesale price per category level
        |--------------------------------------------------------------------------------------------------------------------
        */

        /**
         * Add wholesale price fields to product category taxonomy add page.
         *
         * @since 1.0.5
         * @since 1.14.0 Refactor codebase and move to its proper model.
         * @access public
         *
         * @param WP_Taxonomy $taxonomy Taxonomy object.
         */
        public function product_category_add_custom_fields( $taxonomy ) {

            $all_registered_wholesale_roles = $this->_wwpp_wholesale_roles->getAllRegisteredWholesaleRoles();

            foreach ( $all_registered_wholesale_roles as $role_key => $role ) { ?>

                <div class="form-field wholesale-discount-product-cat-level <?php echo $role_key; ?>-wholesale-discount-product-cat-level">
                    <label for="term_meta[<?php echo $role_key; ?>_wholesale_discount]"><?php echo $role[ 'roleName' ]; ?></label>
                    <input type="text" name="term_meta[<?php echo $role_key; ?>_wholesale_discount]" id="term_meta[<?php echo $role_key; ?>_wholesale_discount]" class="wc_input_price <?php echo $role_key ?>_wholesale_discount" value="">
                    <p class="description"><?php echo sprintf( __( '%1$s Discount For Products In This Category. In Percent (%). Ex. 3 percent then input 3, 30 percent then input 30, 0.3 percent then input 0.3.' , 'woocommerce-wholesale-prices-premium' ) , $role[ 'roleName' ] ); ?></p>
                </div>

            <?php }

        }

        /**
         * Add wholesale price fields to product category taxonomy edit page.
         *
         * @since 1.0.5
         * @since 1.14.0 Refactor codebase and move to its proper model.
         * @access public
         *
         * @param WP_Term $term Term object.
         */
        public function product_category_edit_custom_fields( $term ) {

            $all_registered_wholesale_roles = $this->_wwpp_wholesale_roles->getAllRegisteredWholesaleRoles();
            $t_id                           = $term->term_id; // put the term ID into a variable
            $term_meta                      = get_option( "taxonomy_$t_id" ); // retrieve the existing value(s) for this meta field. This returns an array

            foreach ( $all_registered_wholesale_roles as $role_key => $role ) { ?>

                <tr class="form-field wholesale-discount-product-cat-level <?php echo $role_key; ?>-wholesale-discount-product-cat-level">
                    <th scope="row" valign="top"><label for="term_meta[<?php echo $role_key; ?>_wholesale_discount]"><?php echo $role[ 'roleName' ]; ?></label></th>
                    <td>
                        <input type="text" name="term_meta[<?php echo $role_key; ?>_wholesale_discount]" id="term_meta[<?php echo $role_key; ?>_wholesale_discount]" class="wc_input_price <?php echo $role_key ?>_wholesale_discount" value="<?php echo esc_attr( $term_meta[ $role_key . '_wholesale_discount'] ) ? esc_attr( $term_meta[ $role_key . '_wholesale_discount'] ) : ''; ?>">
                        <p class="description"><?php echo sprintf( __( '%1$s Discount For Products In This Category. In Percent (%). Ex. 3 percent then input 3, 30 percent then input 30, 0.3 percent then input 0.3.' , 'woocommerce-wholesale-prices-premium' ) , $role[ 'roleName' ] ); ?></p>
                    </td>
                </tr>

            <?php }

        }

        /**
         * Save wholesale price fields data on product category taxonomy add and edit page.
         *
         * @since 1.0.5
         * @since 1.7.0 Bug fix. Properly set have_post_meta value to all products under the edited product category.
         * @since 1.14.0 Refactor codebase and move to its own model.
         * @access public
         * 
         * @param int $term_id Term id.
         */
        public function product_category_save_custom_fields( $term_id ) {

            if ( isset( $_POST[ 'term_meta' ] ) ) {

                $t_id      = $term_id;
                $term_meta = get_option( "taxonomy_$t_id" );
                $cat_keys  = array_keys( $_POST[ 'term_meta' ] );

                $products = WWPP_WPDB_Helper::getProductsByCategory( $term_id );

                foreach ( $cat_keys as $key ) {

                    if ( isset ( $_POST[ 'term_meta' ][ $key ] ) ) {

                        $term_meta[ $key ] = $_POST[ 'term_meta' ][ $key ];
                        $wholesale_role    = str_replace( '_wholesale_discount' , '' , $key );

                        if ( $_POST[ 'term_meta' ][ $key ] ) {

                            // Has discount

                            foreach ( $products as $p ) {

                                if ( get_post_meta( $p->ID , $wholesale_role . '_have_wholesale_price' , true ) != 'yes' ) {

                                    // Either not having $wholesale_role . '_have_wholesale_price' or having value of 'no'

                                    // Add have wholesale price meta
                                    update_post_meta( $p->ID , $wholesale_role . '_have_wholesale_price' , 'yes' );

                                    // Add additional meta to indicate that have wholesale price meta was set by the category
                                    update_post_meta( $p->ID , $wholesale_role . '_have_wholesale_price_set_by_product_cat' , 'yes' );

                                }

                            }

                        } else {

                            // No discount
                            foreach ( $products as $p ) {

                                if ( get_post_meta( $p->ID , $wholesale_role . '_have_wholesale_price' , true ) == 'yes' &&
                                     get_post_meta( $p->ID , $wholesale_role . '_have_wholesale_price_set_by_product_cat' , true ) == 'yes' ) {

                                    // Meaning, product have wholesale price meta that was set by the category
                                    // Don't bother changing the meta for products that have no _have_wholesale_price_set_by_product_cat meta
                                    // it means that those products have wholesale price set on per product level.

                                    // Set have post meta to no
                                    update_post_meta( $p->ID , $wholesale_role . '_have_wholesale_price' , 'no' );

                                    // Delete post additional post meta
                                    delete_post_meta( $p->ID , $wholesale_role . '_have_wholesale_price_set_by_product_cat' );

                                }

                            }

                        }

                    }

                }

                // Save the option array.
                update_option( "taxonomy_$t_id", $term_meta );

            }

        }
        



        /*
        |--------------------------------------------------------------------------------------------------------------------
        | Order quantity based wholesale percent discount per product category level
        |--------------------------------------------------------------------------------------------------------------------
        */

        /**
         * Render order quantity based wholesale discount per category level controls.
         *
         * @since 1.11.0
         * @since 1.14.0 Refactor codebase and move to its own model.
         * @access public
         *
         * @param WP_Term $term Term object.
         */
        public function edit_product_cat_level_quantity_based_wholesale_percent_discount_fields( $term ) {

            $all_registered_wholesale_roles = $this->_wwpp_wholesale_roles->getAllRegisteredWholesaleRoles();
            $enable_feature                 = get_term_meta( $term->term_id , 'wwpp_enable_quantity_based_wholesale_discount' , true );
            
            $qbwd_mapping = get_term_meta( $term->term_id , 'wwpp_quantity_based_wholesale_discount_mapping' , true );
            if ( !is_array( $qbwd_mapping ) )
                $qbwd_mapping = array(); ?>
            
            <tr class="form-field">
                <th scope="row" valign="top"><label><?php _e( 'Quantity Based Wholesale %' , 'woocommerce-wholesale-prices-premium' ); ?></label></th>
                <td>
                    <div id="per-order-quantity-wholesale-percent-discount-cat-level-controls-header">
                        <div class="meta" style="display: none !important;">
                            <span class="term-id"><?php echo $term->term_id; ?></span>
                        </div>

                        <h4><?php _e( 'Specify tiered % discount based on the quantity of the product being purchased.' , 'woocommerce-wholesale-prices-premium' ); ?></h4>
                        <p class="desc"><?php echo sprintf( __( '<b>Ending Qty</b> can be left blank to apply that price for all quantities above the <b>Starting Qty.</b><br>Only applies to the wholesale roles that you specify. <a href="%1$s" target="_blank">Please see here</a> for an explanation of this feature.' , 'woocommerce-wholesale-prices-premium' ) , 'https://wholesalesuiteplugin.com/kb/quantity-based-tiered-pricing-product-categories-guide/?utm_source=Prices%20Premium%20Plugin&utm_medium=Feature&utm_campaign=Knowledge%20Base%20' ); ?></p>
                        <label for="enable-per-order-quantity-wholesale-percent-discount-cat-level">
                            <span class="spinner"></span>
                            <input type="checkbox" id="enable-per-order-quantity-wholesale-percent-discount-cat-level" <?php echo ( $enable_feature == 'yes' ) ? 'checked="checked"' : ''; ?> autocomplete="off">
                            <?php _e( 'Enable further wholesale pricing discounts based on quantity purchased?' , 'woocommerce-wholesale-prices-premium' ); ?>
                        </label>
                    </div><!--#per-order-quantity-wholesale-percent-discount-cat-level-controls-header-->

                    <div id="per-order-quantity-wholesale-percent-discount-cat-level-controls-body">

                        <div id="per-order-quantity-wholesale-percent-discount-cat-level-controls">

                            <div class="meta" style="display: none !important;">
                                <span class="index"></span>
                            </div>

                            <div class="field-controls">

                                <div class="field-set">
                                    <label for="wholesale-role"><?php _e( 'Wholesale Role' , 'woocommerce-wholesale-prices-premium' ); ?></label>
                                    <select id="wholesale-role" autocomplete="off">
                                        <?php foreach ( $all_registered_wholesale_roles as $roleKey => $role ) { ?>
                                            <option value="<?php echo $roleKey; ?>"><?php echo $role[ 'roleName' ]; ?></option>
                                        <?php } ?>
                                    </select>
                                    <span class="dashicons dashicons-editor-help tooltip right" data-tip="<?php _e( 'Select wholesale role to which rule applies.' , 'woocommerce-wholesale-prices-premium' ); ?>"></span>
                                </div>

                                <div class="field-set">
                                    <label for="starting-qty"><?php _e( 'Starting Qty' , 'woocommerce-wholesale-prices-premium' ); ?></label>
                                    <input type="number" id="starting-qty" min="0" autocomplete="off">
                                    <span class="dashicons dashicons-editor-help tooltip right" data-tip="<?php _e( 'Minimum order quantity required for this rule. Must be a number.' , 'woocommerce-wholesale-prices-premium' ); ?>"></span>                                
                                </div>

                                <div class="field-set">
                                    <label for="ending-qty"><?php _e( 'Ending Qty' , 'woocommerce-wholesale-prices-premium' ); ?></label>
                                    <input type="number" id="ending-qty" min="0" autocomplete="off">
                                    <span class="dashicons dashicons-editor-help tooltip right" data-tip="<?php _e( 'Maximum order quantity required for this rule. Must be a number. Leave this blank for no maximum quantity.' , 'woocommerce-wholesale-prices-premium' ); ?>"></span>                                                                
                                </div>

                                <div class="field-set">
                                    <label for="wholesale-discount"><?php _e( 'Wholesale Discount (%)' , 'woocommerce-wholesale-prices-premium' ); ?></label>
                                    <input type="number" id="wholesale-discount" min="0" autocomplete="off">
                                    <span class="dashicons dashicons-editor-help tooltip right" data-tip="<?php _e( 'The new % value off the regular price. This will be the discount value used for quantities within the given range.' , 'woocommerce-wholesale-prices-premium' ); ?>"></span>                                                                                                
                                </div>

                                <div class="field-set button-field-set">
                                    <input type="button" id="cancel-edit-quantity-discount-rule" class="button button-secondary" value="<?php _e( 'Cancel' , 'woocommerce-wholesale-prices-premium' ); ?>" autocomplete="off">
                                    <input type="button" id="edit-quantity-discount-rule" class="button button-primary" value="<?php _e( 'Edit Quantity Discount Rule' , 'woocommerce-wholesale-prices-premium' ); ?>" autocomplete="off">
                                    <input type="button" id="add-quantity-discount-rule" class="button button-primary" value="<?php _e( 'Add Quantity Discount Rule' , 'woocommerce-wholesale-prices-premium' ); ?>" autocomplete="off">
                                    <span class="spinner"></span>
                                </div>

                            </div>

                        </div><!--#per-order-quantity-wholesale-percent-discount-cat-level-controls-->

                        <div id="per-order-quantity-wholesale-percent-discount-cat-level-mapping-table-container">

                            <table id="per-order-quantity-wholesale-percent-discount-cat-level-mapping-table" class="widefat striped">

                                <thead>
                                    <tr>
                                        <th class="wholesale-role-heading"><?php _e( 'Wholesale Role' , 'woocommerce-wholesale-prices-premium' ); ?></th>
                                        <th class="start-qty-heading"><?php _e( 'Starting Qty' , 'woocommerce-wholesale-prices-premium' ); ?></th>
                                        <th class="end-qty-heading"><?php _e( 'Ending Qty' , 'woocommerce-wholesale-prices-premium' ); ?></th>
                                        <th class="wholesale-discount"><?php _e( 'Wholesale Discount (%)' , 'woocommerce-wholesale-prices-premium' ); ?></th>
                                        <th class="controls-heading"></th>
                                    </tr>
                                </thead>

                                <tfoot>
                                    <tr>
                                        <th class="wholesale-role-heading"><?php _e( 'Wholesale Role' , 'woocommerce-wholesale-prices-premium' ); ?></th>
                                        <th class="start-qty-heading"><?php _e( 'Starting Qty' , 'woocommerce-wholesale-prices-premium' ); ?></th>
                                        <th class="end-qty-heading"><?php _e( 'Ending Qty' , 'woocommerce-wholesale-prices-premium' ); ?></th>
                                        <th class="wholesale-discount"><?php _e( 'Wholesale Discount (%)' , 'woocommerce-wholesale-prices-premium' ); ?></th>
                                        <th class="controls-heading"></th>
                                    </tr>
                                </tfoot>

                                <tbody>
                                    <?php if ( !empty( $qbwd_mapping ) ) {

                                        foreach ( $qbwd_mapping as $index => $mapping_data ) { ?>

                                            <tr>
                                                <td class="meta hidden">
                                                    <span class="index"><?php echo $index; ?></span>
                                                    <span class="wholesale-role"><?php echo $mapping_data[ 'wholesale-role' ]; ?></span>
                                                    <span class="wholesale-discount"><?php echo $mapping_data[ 'wholesale-discount' ]; ?></span>
                                                </td>
                                                <td class="wholesale-role-text"><?php echo $all_registered_wholesale_roles[ $mapping_data[ 'wholesale-role' ] ][ 'roleName' ]; ?></td>
                                                <td class="start-qty"><?php echo $mapping_data[ 'start-qty' ]; ?></td>
                                                <td class="end-qty"><?php echo $mapping_data[ 'end-qty' ]; ?></td>
                                                <td class="wholesale-discount-text"><?php echo $mapping_data[ 'wholesale-discount' ]; ?>%</td>
                                                <td class="controls">
                                                    <a class="edit dashicons dashicons-edit"></a>
                                                    <a class="delete dashicons dashicons-no"></a>
                                                </td>
                                            </tr>

                                        <?php }

                                    } else { ?>

                                        <tr class="no-items">
                                            <td class="colspanchange" colspan="5"><?php _e( 'No Quantity Discount Rules Found' , 'woocommerce-wholesale-prices-premium' ); ?></td>
                                        </tr>

                                    <?php } ?>
                                </tbody>

                            </table>

                        </div><!--#per-order-quantity-wholesale-percent-discount-cat-level-mapping-table-container-->

                    </div><!--#per-order-quantity-wholesale-percent-discount-cat-level-controls-body-->

                </td>
            </tr>
            
            <?php

        }

        /**
         * Toggle order quantity based wholesale discount per category level feature.
         *
         * @since 1.11.0
         * @since 1.14.0 Refactor codebase and move to its own model.
         * @access public
         *
         * @param null|int    $term_id Term id.
         * @param null|string $enable  Value of 'yes' or 'no'.
         * @return array Operation status.
         */
        public function toggle_cat_level_quantity_based_wholesale_discount_fields( $term_id = null , $enable = null ) {

            if ( is_admin() && defined( 'DOING_AJAX' ) && DOING_AJAX ) {

                $term_id = $_POST[ 'term_id' ];
                $enable  = $_POST[ 'enable' ];

            }

            update_term_meta( $term_id , 'wwpp_enable_quantity_based_wholesale_discount' , $enable );
            
            $response = array( 'status' => 'success' );

            if ( is_admin() && defined( 'DOING_AJAX' ) && DOING_AJAX ) {

                @header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
                echo wp_json_encode( $response );                
                wp_die();

            } else
                return $response;
            
        }

        /**
         * Save order quantity based wholesale discount per category level entry data.
         * 
         * @since 1.11.0
         * @since 1.14.0 Refactor codebase and move to its own model.
         * @access public
         *
         * @param null|int   $term_id Term id.
         * @param null|array $data    Mapping entry data.
         * @return array Operation status.
         */
        public function save_cat_level_quantity_based_wholesale_discount_entry( $term_id = null , $data = null ) {

            if ( is_admin() && defined( 'DOING_AJAX' ) && DOING_AJAX ) {

                $term_id = $_POST[ 'term_id' ];
                $data    = $_POST[ 'data' ];

            }

            $all_registered_wholesale_roles = $this->_wwpp_wholesale_roles->getAllRegisteredWholesaleRoles();

            $qbwd_mapping = get_term_meta( $term_id , 'wwpp_quantity_based_wholesale_discount_mapping' , true );
            if ( !is_array( $qbwd_mapping ) )
                $qbwd_mapping = array();          

            $validation_result = $this->validate_cat_level_quantity_based_wholesale_discount_entry_data( $data , $term_id , $qbwd_mapping , $all_registered_wholesale_roles );
            if ( $validation_result !== true )
                return new WP_Error( 'wwpp-invalid-cat-level-quantity-based-wholesale-discount-entry-data' , $validation_result , array( 'term_id' => $term_id , 'data' => $data , 'all_registered_wholesale_roles' => $all_registered_wholesale_roles ) );

            $data[ 'wholesale-discount' ] = wc_format_decimal( $data[ 'wholesale-discount' ] );

            if ( $data[ 'wholesale-discount' ] < 0 )
                $data[ 'wholesale-discount' ] = 0; 
            
            // ==========================================================
            // Check for dups and overlaps
            // ==========================================================
            $dup               = false;
            $start_qty_overlap = false;
            $end_qty_overlap   = false;
            $err_indexes       = array();

            foreach ( $qbwd_mapping as $idx => $mapping ) {

                if ( $mapping[ 'wholesale-role' ] == $data[ 'wholesale-role' ] ) {

                    if ( array_key_exists( 'index' , $data ) ) {

                        // Edit Mode

                        if ( $mapping[ 'start-qty' ] == $data[ 'start-qty' ] && $data[ 'index' ] != $idx ) {

                            $dup = true;
                            if ( !in_array( $idx , $err_indexes ) )
                                $err_indexes[] = $idx;

                        }

                    } else {

                        // Add Mode

                        if ( $mapping[ 'start-qty' ] == $data[ 'start-qty' ] ) {

                            $dup = true;
                            if ( !in_array( $idx , $err_indexes ) )
                                $err_indexes[] = $idx;

                        }

                    }

                    // Check for overlapping mappings. Only do this if no dup yet

                    if ( !$dup && ( ( array_key_exists( 'index' , $data ) && $data[ 'index' ] != $idx ) || !array_key_exists( 'index' , $data ) ) ) {

                        if ( $data[ 'start-qty' ] >= $mapping[ 'start-qty' ] && $data[ 'start-qty' ] <= $mapping[ 'end-qty' ] ) {

                            $start_qty_overlap = true;
                            if ( !in_array( $idx , $err_indexes ) )
                                $err_indexes[] = $idx;

                        }

                        if ( $data[ 'end-qty' ] <= $mapping[ 'end-qty' ] && $data[ 'end-qty' ] >= $mapping[ 'start-qty' ] ) {

                            $end_qty_overlap = true;
                            if ( !in_array( $idx , $err_indexes ) )
                                $err_indexes[] = $idx;

                        }

                    }

                }

                // break loop if there is dup or overlap
                if ( $dup || $start_qty_overlap || $end_qty_overlap )
                    break;

            }

            if ( $dup )
                return new WP_Error( 'wwpp-duplicate-cat-level-quantity-based-wholesale-discount-entry' , __( 'Duplicate wholesale discount rule' , 'woocommerce-wholesale-prices-premium' ) , array( 'term_id' => $term_id , 'data' => $data , 'all_registered_wholesale_roles' => $all_registered_wholesale_roles , 'err_indexes' => $err_indexes ) );
            elseif ( $start_qty_overlap || $end_qty_overlap )
                return new WP_Error( 'wwpp-overlap-cat-level-quantity-based-wholesale-discount-entry' , __( 'Overlap wholesale discount rule' , 'woocommerce-wholesale-prices-premium' ) , array( 'term_id' => $term_id , 'data' => $data , 'all_registered_wholesale_roles' => $all_registered_wholesale_roles , 'err_indexes' => $err_indexes ) );
            
            
            // ==========================================================
            // Update mapping data
            // ==========================================================
            if ( array_key_exists( 'index' , $data ) ) {

                // Edit Mode
                $edited_index = $data[ 'index' ];
                unset( $data[ 'index' ] );

                $qbwd_mapping[ $edited_index ] = $data;

                $index = $edited_index;

            } else {

                // Add Mode

                $qbwd_mapping[] = $data;

                end( $qbwd_mapping );
                $new_index = key( $qbwd_mapping );

                $index = $new_index;

            }

            update_term_meta( $term_id , 'wwpp_quantity_based_wholesale_discount_mapping' , $qbwd_mapping );

            if ( is_wp_error( $index ) ) {

                $response   = array( 'status' => 'fail' , 'error_message' => $index->get_error_message() );
                $error_data = $index->get_error_data( $index->get_error_code() );

                if ( array_key_exists( 'err_indexes' , $error_data ) )
                    $response[ 'err_indexes' ] = $error_data[ 'err_indexes' ];

            } else
                $response = array( 'status' => 'success' , 'index'  => $index );
            
            if ( is_admin() && defined( 'DOING_AJAX' ) && DOING_AJAX ) {

                @header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
                echo wp_json_encode( $response );
                wp_die();

            } else
                return $response;

        }

        /**
         * Delete order quantity based wholesale discount per category level entry data.
         *
         * @since 1.11.0
         * @since 1.14.0 Refactor codebase and move to its own model.
         * @access public
         *
         * @param null|int $term_id Term id.
         * @param null|int $index   Mapping entry index.
         * @return array Operation status.
         */
        public function delete_cat_level_quantity_based_wholesale_discount_entry( $term_id = null , $index = null ) {

            if ( is_admin() && defined( 'DOING_AJAX' ) && DOING_AJAX ) {

                $term_id = $_POST[ 'term_id' ];
                $index    = $_POST[ 'index' ];

            }

            $qbwd_mapping = get_term_meta( $term_id , 'wwpp_quantity_based_wholesale_discount_mapping' , true );
            if ( !is_array( $qbwd_mapping ) )
                $qbwd_mapping = array();

            if ( !array_key_exists( $index , $qbwd_mapping ) )
                return new WP_Error( 'wwpp-invalid-cat-level-quantity-based-wholesale-discount-entry-data' , __( 'The wholesale discount entry you wish to delete does not exist on record' , 'woocommerce-wholesale-prices-premium' ) , array( 'term_id' => $term_id , 'index' => $index ) );
            
            $deleted_entry = $qbwd_mapping[ $index ];
            
            unset( $qbwd_mapping[ $index ] );

            update_term_meta( $term_id , 'wwpp_quantity_based_wholesale_discount_mapping' , $qbwd_mapping );

            if ( is_wp_error( $deleted_entry ) ) {

                $response = array(
                    'status'        => 'fail',
                    'error_message' => $deleted_entry->get_error_message()
                );

            } else {

                $response = array(
                    'status'        => 'success',
                    'deleted_entry' => $deleted_entry
                );

            }
            
            if ( is_admin() && defined( 'DOING_AJAX' ) && DOING_AJAX ) {

                @header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
                echo wp_json_encode( $response );
                wp_die();

            } else
                return $response;

        }




        /*
        |--------------------------------------------------------------------------------------------------------------------
        | Helper Functions
        |--------------------------------------------------------------------------------------------------------------------
        */

        /**
         * Validate order quantity based wholesale discount per category level entry data.
         *
         * @since 1.11.0
         * @since 1.14.0 Refactor codebase and move to its own model.
         * @access public
         *
         * @param array $data                           Mapping entry data.
         * @param int   $term_id                        Term id.
         * @param array $qbwd_mapping                   Current mapping data.
         * @param array $all_registered_wholesale_roles Array of current wholesale role mappings.
         * @return boolean True if success, string explaining the error on failure.
         */
        public function validate_cat_level_quantity_based_wholesale_discount_entry_data( $data , $term_id , $qbwd_mapping , $all_registered_wholesale_roles ) {

            if ( is_array( $data ) &&
                 array_key_exists( 'wholesale-role' , $data ) && array_key_exists( $data[ 'wholesale-role' ] , $all_registered_wholesale_roles ) &&
                 array_key_exists( 'start-qty' , $data ) && filter_var( $data[ 'start-qty' ] , FILTER_VALIDATE_INT ) &&
                 array_key_exists( 'end-qty' , $data ) &&
                 array_key_exists( 'wholesale-discount' , $data ) && filter_var( $data[ 'wholesale-discount' ] , FILTER_VALIDATE_FLOAT ) ) {
                
                if ( $data[ 'end-qty' ] != '' )
                    if ( !filter_var( $data[ 'end-qty' ] , FILTER_VALIDATE_INT ) || $data[ 'end-qty' ] < $data[ 'start-qty' ] )
                        return __( 'Ending Qty must not be less than Starting Qty' , 'woocommerce-wholesale-prices-premium' );
                
                if ( array_key_exists( 'index' , $data ) )
                    if ( !array_key_exists( $data[ 'index' ] , $qbwd_mapping ) )
                        return __( 'The wholesale discount entry you wish to edit does not exist on record' , 'woocommerce-wholesale-prices-premium' );
                
                return true;

            } else
                return __( 'Invalid category level quantity based wholesale discount entry data' , 'woocommerce-wholesale-prices-premium' );
            
        }




        /*
        |--------------------------------------------------------------------------------------------------------------------
        | Execute model
        |--------------------------------------------------------------------------------------------------------------------
        */

        /**
         * Register model ajax handlers.
         *
         * @since 1.14.0
         * @access public
         */
        public function register_ajax_handlers() {

            add_action( "wp_ajax_wwpp_toggle_cat_level_quantity_based_wholesale_discount_fields" , array( $this , 'toggle_cat_level_quantity_based_wholesale_discount_fields' ) );
            add_action( "wp_ajax_wwpp_save_cat_level_quantity_based_wholesale_discount_entry"    , array( $this , 'save_cat_level_quantity_based_wholesale_discount_entry' ) );
            add_action( "wp_ajax_wwpp_delete_cat_level_quantity_based_wholesale_discount_entry"  , array( $this , 'delete_cat_level_quantity_based_wholesale_discount_entry' ) );

        }

        /**
         * Execute model.
         *
         * @since 1.13.0
         * @since 1.14.0 Add product category custom fields and product category level order quantity based wholesale pricing.
         * @access public
         */
        public function run() {

            // Set 'have_wholesale_price' meta
            add_action( 'wwp_set_have_wholesale_price_meta_prod_cat_wholesale_discount' , array( $this , 'set_have_wholesale_price_meta_prod_cat_wholesale_discount' ) , 10 , 2 );

            // Wholesale price per category level
            add_action( 'product_cat_add_form_fields'  , array( $this , 'product_category_add_custom_fields' )  , 10 , 1 );
            add_action( 'product_cat_edit_form_fields' , array( $this , 'product_category_edit_custom_fields' ) , 10 , 1 );
            add_action( 'edited_product_cat'           , array( $this , 'product_category_save_custom_fields' ) , 10 , 2 );
            add_action( 'create_product_cat'           , array( $this , 'product_category_save_custom_fields' ) , 10 , 2 );

            // Per order quantity wholesale percentage discount per category level
            add_action( 'product_cat_edit_form_fields' , array( $this , 'edit_product_cat_level_quantity_based_wholesale_percent_discount_fields' ) , 10 , 1 );
            
            // Register model ajax handlers
            add_action( 'init' , array( $this , 'register_ajax_handlers' ) );

        }

    }

}
