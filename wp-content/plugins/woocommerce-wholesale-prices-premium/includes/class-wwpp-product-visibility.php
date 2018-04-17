<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WWPP_Product_Visibility' ) ) {

    /**
     * Model that houses the logic of filtering the products and only showing them to the proper recipient.
     *
     * @since 1.12.8
     * @see WWPP_Query They are related in a way that WWPP_Query also filter products but via query.
     */
    class WWPP_Product_Visibility {

        /*
        |--------------------------------------------------------------------------
        | Class Properties
        |--------------------------------------------------------------------------
        */

        /**
         * Property that holds the single main instance of WWPP_Product_Visibility.
         *
         * @since 1.12.8
         * @access private
         * @var WWPP_Product_Visibility
         */
        private static $_instance;

        /**
         * Model that house various generic plugin helper functions.
         *
         * @since 1.12.8
         * @access private
         * @var WWPP_Helper_Functions
         */
        private $_wwpp_helper_functions;

        /**
         * Model that houses the logic of retrieving information relating to wholesale role/s of a user.
         *
         * @since 1.12.8
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
         * WWPP_Product_Visibility constructor.
         *
         * @since 1.12.8
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_Product_Visibility model.
         */
        public function __construct( $dependencies ) {

            $this->_wwpp_helper_functions = $dependencies[ 'WWPP_Helper_Functions' ];
            $this->_wwpp_wholesale_roles  = $dependencies[ 'WWPP_Wholesale_Roles' ];

        }

        /**
         * Ensure that only one instance of WWPP_Product_Visibility is loaded or can be loaded (Singleton Pattern).
         *
         * @since 1.12.8
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_Product_Visibility model.
         * @return WWPP_Product_Visibility
         */
        public static function instance( $dependencies ) {

            if ( !self::$_instance instanceof self )
                self::$_instance = new self( $dependencies );

            return self::$_instance;

        }

        /**
         * Get curent user wholesale role.
         *
         * @since 1.12.8
         * @access private
         *
         * @return mixed String of user wholesale role, False otherwise.
         */
        private function _get_current_user_wholesale_role() {

            $user_wholesale_role = $this->_wwpp_wholesale_roles->getUserWholesaleRole();

            return ( is_array( $user_wholesale_role ) && !empty( $user_wholesale_role ) ) ? $user_wholesale_role[ 0 ] : false;

        }




        /*
        |--------------------------------------------------------------------------
        | Wholesale Role Visibility Filter On Single Product Admin Page
        |--------------------------------------------------------------------------
        */

        /**
         * Embed custom metabox with fields relating to wholesale role filter into the single product admin page.
         *
         * @since 1.0.0
         * @since 1.12.8 Refactor code base.
         * @access public
         */
        public function add_product_wholesale_role_visibility_filter_fields() {

            global $post;
            $all_registered_wholesale_roles = $this->_wwpp_wholesale_roles->getAllRegisteredWholesaleRoles();

            if ( $post->post_type == 'product' ) {
                // $currProductWholesaleFilter
                $product_wholesale_role_filter = get_post_meta( $post->ID , WWPP_PRODUCT_WHOLESALE_VISIBILITY_FILTER );
                if ( !is_array( $product_wholesale_role_filter ) )
                    $product_wholesale_role_filter = array();

                require_once ( WWPP_VIEWS_PATH . 'backend/product/single/view-wwpp-product-wholesale-role-visibility-filter.php' );

            }

        }

        /**
         * Save custom embeded fields relating to wholesale role visibility filter.
         *
         * @since 1.0.0
         * @since 1.12.8 Refactor code base to be more efficient and secure.
         * @access public
         *
         * @param int $post_id Post ( Product ) Id.
         */
        public function save_product_wholesale_role_visibility_filter( $post_id ) {

            // Check if this is an inline edit. If true then return.
            if ( isset( $_POST[ '_inline_edit' ] ) && wp_verify_nonce ( $_POST[ '_inline_edit' ] , 'inlineeditnonce' ) )
                return;

            // Check if valid save post action
            if ( $this->_wwpp_helper_functions->check_if_valid_save_post_action( $post_id , 'product' ) ) {

                // Security check
                if ( isset( $_POST[ 'wwpp_nonce_save_product_wholesale_role_visibility_filter' ] ) && wp_verify_nonce( $_POST[ 'wwpp_nonce_save_product_wholesale_role_visibility_filter' ] , 'wwpp_action_save_product_wholesale_role_visibility_filter' ) ) {

                    // Because we are adding post meta via add_post_meta
                    // We make sure to delete old post meta so the meta won't contains duplicate values
                    delete_post_meta( $post_id , WWPP_PRODUCT_WHOLESALE_VISIBILITY_FILTER );

                    if ( isset( $_POST[ 'wholesale-visibility-select' ] ) && is_array( $_POST[ 'wholesale-visibility-select' ] ) && !empty( $_POST[ 'wholesale-visibility-select' ] ) ) {

                        foreach( $_POST[ 'wholesale-visibility-select' ] as $wholesaleRole )
                            add_post_meta( $post_id , WWPP_PRODUCT_WHOLESALE_VISIBILITY_FILTER , $wholesaleRole );

                    } else
                        add_post_meta( $post_id , WWPP_PRODUCT_WHOLESALE_VISIBILITY_FILTER , 'all' );

                }

            }

        }

        /**
         * Apply wholesale role visibility filter to each single product page.
         * If single product page is loaded, check the filter and the current user if he/she is authorized to view the product.
         * If yes then continue loading the single product page. Else redirect to the shop page.
         *
         * @since 1.0.0
         * @since 1.12.8 Refactor code base to be more efficient and maintainable.
         * @access public
         */
        public function check_product_wholesale_role_visibility_filter() {

            // Check if user is not an admin, else we don't want to restrict admins in any way.
            if ( !current_user_can( 'manage_options' ) && is_product() ) {

                global $post;

                $user_wholesale_role   = $this->_get_current_user_wholesale_role();
                $post_wholesale_filter = get_post_meta( $post->ID , WWPP_PRODUCT_WHOLESALE_VISIBILITY_FILTER );

                if ( !is_array( $post_wholesale_filter ) || empty( $post_wholesale_filter ) )
                    $post_wholesale_filter = array( 'all' ); // If no filter then meaning this product is accessible to all users

                if ( !in_array( $user_wholesale_role , $post_wholesale_filter ) && !in_array( 'all' , $post_wholesale_filter ) ) {

                    wp_redirect( get_permalink( wc_get_page_id( 'shop' ) ) );
                    exit();

                }

            }

        }




        /*
        |--------------------------------------------------------------------------
        | Only Show Wholesale Products To Wholesale Users
        |--------------------------------------------------------------------------
        */

        /**
         * Only show wholesale products to wholesale users if specified by admin. (Single product page).
         *
         * @since 1.0.3
         * @since 1.13.0 Refactor codebase and move to its correct model.
         */
        public function only_show_wholesale_products_to_wholesale_users() {

            // Check if user is not an admin, else we don't want to restrict admins in any way.
            // And also check if settings for "Only Showing Wholesale Products To Wholesale Users" option is checked.
            if ( !current_user_can( 'manage_options' ) && get_option( 'wwpp_settings_only_show_wholesale_products_to_wholesale_users' , false ) === 'yes' && is_product() ) {

                global $post;

                $user_wholesale_role = $this->_get_current_user_wholesale_role();

                $role_discount = get_option( WWPP_OPTION_WHOLESALE_ROLE_GENERAL_DISCOUNT_MAPPING );
                if ( !is_array( $role_discount ) )
                    $role_discount = array();

                // If there is a default general wholesale discount set for this wholesale role
                // then all products are considered wholesale for this dude
                if ( $user_wholesale_role && !array_key_exists( $user_wholesale_role , $role_discount ) ) {

                    $have_wholesale_price = get_post_meta( $post->ID , $user_wholesale_role . '_have_wholesale_price' , true );

                    if ( $have_wholesale_price !== 'yes' ) {

                        wp_redirect( get_permalink( wc_get_page_id( 'shop' ) ) );
                        exit();

                    }

                }

            }

        }




        /*
        |--------------------------------------------------------------------------
        | Filter Cross/Inter Sells Products
        |--------------------------------------------------------------------------
        */

        /**
         * Filter inter sells products ( cross-sells, up-sells ).
         *
         * @since 1.7.3
         * @since 1.12.8 Refactor codebase for effeciency and maintainability.
         *
         * @param array      $product_ids Arrays of product ids.
         * @param WC_Product $product     Product object.
         * @return array Filtered array of product ids.
         */
        public function filter_cross_and_up_sell_products( $product_ids , $product ) {

            // Check if user is not an admin, else we don't want to restrict admins in any way.
            if ( !current_user_can( 'manage_options' ) ) {

                $user_wholesale_role  = $this->_get_current_user_wholesale_role();
                $filtered_product_ids = array();

                // This only affects wholesale users
                if ( $user_wholesale_role && get_option( 'wwpp_settings_only_show_wholesale_products_to_wholesale_users' , false ) === 'yes' ) {

                    $role_discount = get_option( WWPP_OPTION_WHOLESALE_ROLE_GENERAL_DISCOUNT_MAPPING , array() );
                    if ( !is_array( $role_discount ) )
                        $role_discount = array();

                    // If there is a default general wholesale discount set for this wholesale role
                    // then all products are considered wholesale for this dude

                    if ( !array_key_exists( $user_wholesale_role , $role_discount ) ) {

                        foreach ( $product_ids as $product_id )
                            if ( get_post_meta( $product_id , $user_wholesale_role . '_have_wholesale_price' , true ) === 'yes' || floatval( get_post_meta( $product_id , $user_wholesale_role . '_wholesale_price' , true ) ) > 0 ) // WWPP-158
                                $filtered_product_ids[] = $product_id;

                    } else
                        $filtered_product_ids = $product_ids;

                } else {

                    foreach ( $product_ids as $product_id ) {

                        $visibility_filter = get_post_meta( $product_id , WWPP_PRODUCT_WHOLESALE_VISIBILITY_FILTER );
                        if ( !is_array( $visibility_filter ) || empty( $visibility_filter ) )
                            $visibility_filter = array( 'all' ); // If no filter then meaning this product is accessible to all users

                        if ( in_array( $user_wholesale_role , $visibility_filter ) || in_array( 'all' , $visibility_filter ) )
                            $filtered_product_ids[] = $product_id;

                    }

                }

                return $filtered_product_ids;

            } else
                return $product_ids;

        }




        /*
        |--------------------------------------------------------------------------
        | Product Category Items Count
        |--------------------------------------------------------------------------
        */

        /**
         * Filter product category product items count.
         *
         * @since 1.7.3
         * @since 1.14.0 Refactor codebase and move to its proper model.
         * @access public
         *
         * @param string $count_markup Category product count html markup.
         * @param object $category     Category object.
         * @return string Filtered category product count html markup.
         */
        public function filter_product_category_post_count( $count_markup , $category ) {

            $user_wholesale_role = $this->_wwpp_wholesale_roles->getUserWholesaleRole();

            if ( !empty( $user_wholesale_role ) && get_option( 'wwpp_settings_hide_product_categories_product_count' , false ) === 'yes' )
                return '';

            $product_ids = array();
            $products    = WWPP_WPDB_Helper::getProductsByCategory( $category->term_id ); // WP_Post

            foreach ( $products as $product )
                $product_ids[] = $product->ID;

            // Reuse the logic for the filter product inter sells, filter only products visible to this wholesale role
            $product_ids = $this->filter_cross_and_up_sell_products( $product_ids , null );

            return ' <mark class="count">(' . count( $product_ids ) . ')</mark>';

        }

        /**
         * Display wholesale product visibility field in quick edit. Hooked into 'wwp_after_quick_edit_wholesale_price_fields'.
         *
         * @since 1.14.4
         * @access public
         *
         * @param Array $all_wholesale_roles    list of wholesale roles
         */
        public function quick_edit_display_product_visibility_field( $all_wholesale_roles ) {

            $all_registered_wholesale_roles = $this->_wwpp_wholesale_roles->getAllRegisteredWholesaleRoles();

            ?>
                <div class="quick_edit_product_visibility_field" style="float: none; clear: both; display: block;">
                    <div style="height: 1px;"></div><!--To Prevent Heading From Bumping Up-->
                    <h4><?php _e( 'Restrict To Wholesale Roles', 'woocommerce-wholesale-prices-premium' ); ?></h4>
                    <select style="width: 100%;" data-placeholder="<?php _e( 'Choose wholesale users...' , 'woocommerce-wholesale-prices-premium' ); ?>" name="wholesale-visibility-select[]" id="wholesale-visibility-select" multiple>

                        <?php foreach ( $all_registered_wholesale_roles as $role_key => $role ) : ?>
                            <option value="<?php echo $role_key ?>"><?php echo $role[ 'roleName' ]; ?></option>
                        <?php endforeach; ?>

                    </select><!--#wholesale-visibility-select-->
                </div>
            <?php
        }

        /**
         * Add the product visibility data on the product listing column so it can be used to populate the
         * current values of the quick edit fields via javascript.
         *
         * @since 1.14.4
         * @access public
         *
         * @param Array  $all_wholesale_roles   list of wholesale roles
         * @param int    $product_id            Product ID
         */
        public function add_product_visibility_data_to_product_listing_column( $all_wholesale_roles , $product_id ) {

            $product_wholesale_role_filter = get_post_meta( $product_id , WWPP_PRODUCT_WHOLESALE_VISIBILITY_FILTER );

            if ( ! is_array( $product_wholesale_role_filter ) )
                $product_wholesale_role_filter = array();
            ?>

            <div class="wholesale_product_visibility_data" data-selected_roles='<?php echo json_encode( $product_wholesale_role_filter ); ?>'></div>
            <?php
        }

        /**
         * Save wholesale custom fields on the quick edit option.
         *
         * @since 1.14.4
         * @access public
         *
         * @param WC_Product $product               Product object.
         * @param int        $product_id            Product ID.
         */
        public function save_product_visibility_on_quick_edit_screen( $product , $product_id ) {

            // Because we are adding post meta via add_post_meta
            // We make sure to delete old post meta so the meta won't contains duplicate values
            delete_post_meta( $product_id , WWPP_PRODUCT_WHOLESALE_VISIBILITY_FILTER );

            if ( isset( $_POST[ 'wholesale-visibility-select' ] ) && is_array( $_POST[ 'wholesale-visibility-select' ] ) && !empty( $_POST[ 'wholesale-visibility-select' ] ) ) {

                foreach( $_POST[ 'wholesale-visibility-select' ] as $wholesaleRole )
                    add_post_meta( $product_id , WWPP_PRODUCT_WHOLESALE_VISIBILITY_FILTER , $wholesaleRole );

            } else
                add_post_meta( $product_id , WWPP_PRODUCT_WHOLESALE_VISIBILITY_FILTER , 'all' );
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
         * @since 1.13.0 Move the logic of only showing wholesale products to wholesale users here
         * @access public
         */
        public function run() {

            // Wholesale role visibility filter on single product admin page
            add_action( "post_submitbox_misc_actions" , array( $this , 'add_product_wholesale_role_visibility_filter_fields' ) , 100 );
            add_action( 'save_post'                   , array( $this , 'save_product_wholesale_role_visibility_filter' )       , 10 , 1 );
            add_action( 'template_redirect'           , array( $this , 'check_product_wholesale_role_visibility_filter' )      , 10 );

            // Only show wholesale products to wholesale users
            add_filter( 'template_redirect' , array( $this , 'only_show_wholesale_products_to_wholesale_users' ) , 100 );

            // Filter cross and up sell products
            add_filter( 'woocommerce_product_crosssell_ids' , array( $this , 'filter_cross_and_up_sell_products' ) , 10 , 2 );
            add_filter( 'woocommerce_product_upsell_ids'    , array( $this , 'filter_cross_and_up_sell_products' ) , 10 , 2 );


            // Filter product category product items count.
            add_filter( 'woocommerce_subcategory_count_html' , array( $this , 'filter_product_category_post_count' ) , 10 , 2 );

            // Quick edit support
            add_action( 'wwp_after_quick_edit_wholesale_price_fields' , array( $this , 'quick_edit_display_product_visibility_field' ) , 10 , 1 );
            add_action( 'wwp_add_wholesale_price_fields_data_to_product_listing_column' , array( $this , 'add_product_visibility_data_to_product_listing_column' ) , 10 , 2 );
            add_action( 'wwp_save_wholesale_price_fields_on_quick_edit_screen' , array( $this , 'save_product_visibility_on_quick_edit_screen' ) , 10 , 2 );

        }

    }

}
