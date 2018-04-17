<?php if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WWPP_Wholesale_Role_General_Discount_Mapping' ) ) {

    /**
     * Model that houses the logic of wholesale roles general discount mapping.
     *
     * @since 1.14.0
     */
    class WWPP_Wholesale_Role_General_Discount_Mapping {

        /*
        |--------------------------------------------------------------------------
        | Class Properties
        |--------------------------------------------------------------------------
        */

        /**
         * Property that holds the single main instance of WWPP_Wholesale_Role_General_Discount_Mapping.
         *
         * @since 1.14.0
         * @access private
         * @var WWPP_Wholesale_Role_General_Discount_Mapping
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



        
        /*
        |--------------------------------------------------------------------------
        | Class Methods
        |--------------------------------------------------------------------------
        */

        /**
         * WWPP_Wholesale_Role_General_Discount_Mapping constructor.
         *
         * @since 1.14.0
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_Wholesale_Role_General_Discount_Mapping model.
         */
        public function __construct( $dependencies ) {

            $this->_wwpp_wholesale_roles = $dependencies[ 'WWPP_Wholesale_Roles' ];

        }

        /**
         * Ensure that only one instance of WWPP_Wholesale_Role_General_Discount_Mapping is loaded or can be loaded (Singleton Pattern).
         *
         * @since 1.14.0
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_Wholesale_Role_General_Discount_Mapping model.
         * @return WWPP_Wholesale_Role_General_Discount_Mapping
         */
        public static function instance( $dependencies ) {

            if ( !self::$_instance instanceof self )
                self::$_instance = new self( $dependencies );

            return self::$_instance;

        }

        /**
         * Add wholesale role / general discount mapping.
         * $discountMapping variable is expected to be an array with the following keys.
         * wholesale_role
         * general_discount
         *
         * @since 1.2.0
         * @since 1.14.0 Refactor codebase and move to its proper model.
         * @access public
         *
         * @param null|array $discount_mapping Discount mapping data.
         * @return array Operation status.
         */
        public function add_wholesale_role_general_discount_mapping( $discount_mapping = null ) {

            if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
                $discount_mapping = $_POST[ 'discountMapping' ];

            $saved_discount_mapping = get_option( WWPP_OPTION_WHOLESALE_ROLE_GENERAL_DISCOUNT_MAPPING );
            if ( !is_array( $saved_discount_mapping ) )
                $saved_discount_mapping = array();

            if ( !array_key_exists( $discount_mapping[ 'wholesale_role' ] , $saved_discount_mapping ) ) {

                $saved_discount_mapping[ $discount_mapping[ 'wholesale_role' ] ] = $discount_mapping[ 'general_discount' ];
                update_option( WWPP_OPTION_WHOLESALE_ROLE_GENERAL_DISCOUNT_MAPPING , $saved_discount_mapping );
                $response = array( 'status' => 'success' );

            } else
                $response = array( 'status' => 'fail' , 'error_message' => __( 'Duplicate Entry, Entry Already Exists' , 'woocommerce-wholesale-prices-premium' ) );
            
            if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
                
                @header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
                echo wp_json_encode( $response );
                wp_die();

            } else
                return $response;

        }

        /**
         * Edit saved wholesale role / general discount mapping.
         *
         * @since 1.2.0
         * @since 1.14.0 Refactor codebase and move to its proper model.
         * @access public
         *
         * @param null|array $discount_mapping Discount mapping data.
         * @return array Operation status.
         */
        public function edit_wholesale_role_general_discount_mapping( $discount_mapping = null ) {

            if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
                $discount_mapping = $_POST[ 'discountMapping' ];

            $saved_discount_mapping = get_option( WWPP_OPTION_WHOLESALE_ROLE_GENERAL_DISCOUNT_MAPPING );
            if ( !is_array( $saved_discount_mapping ) )
                $saved_discount_mapping = array();

            if ( array_key_exists( $discount_mapping[ 'wholesale_role' ] , $saved_discount_mapping ) ) {

                $saved_discount_mapping[ $discount_mapping[ 'wholesale_role' ] ] = $discount_mapping[ 'general_discount' ];
                update_option( WWPP_OPTION_WHOLESALE_ROLE_GENERAL_DISCOUNT_MAPPING , $saved_discount_mapping );
                $response = array( 'status' => 'success' );

            } else
                $response = array( 'status' => 'fail' , 'error_message' => __( 'Entry to be edited does not exist' , 'woocommerce-wholesale-prices-premium' ) );

            if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

                @header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
                echo wp_json_encode( $response );
                wp_die();

            } else
                return $response;

        }

        /**
         * Delete a wholesale role / general discount mapping entry.
         *
         * @since 1.2.0
         * @since 1.14.0 Refactor codebase and move to its proper model.
         * @access public
         *
         * @param null|string $wholesale_role
         * @return array Operation status.
         */
        public function delete_wholesale_role_general_discount_mapping( $wholesale_role = null ) {

            if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
                $wholesale_role = $_POST[ 'wholesaleRole' ];

            $saved_discount_mapping = get_option( WWPP_OPTION_WHOLESALE_ROLE_GENERAL_DISCOUNT_MAPPING );
            if ( !is_array( $saved_discount_mapping ) )
                $saved_discount_mapping = array();

            if ( array_key_exists( $wholesale_role , $saved_discount_mapping ) ) {

                unset( $saved_discount_mapping[ $wholesale_role ] );
                update_option( WWPP_OPTION_WHOLESALE_ROLE_GENERAL_DISCOUNT_MAPPING , $saved_discount_mapping );            
                $response = array( 'status' => 'success' );

            } else
                $response = array( 'status' => 'fail' , 'error_message' => __( 'Entry to be deleted does not exist' , 'woocommerce-wholesale-prices-premium' ) );
            
            if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

                @header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
                echo wp_json_encode( $response );
                wp_die();

            } else
                return true;
            
        }
        



        /*
        |---------------------------------------------------------------------------------------------------------------
        | Execute model
        |---------------------------------------------------------------------------------------------------------------
        */
        
        /**
         * Register model ajax handlers.
         *
         * @since 1.14.0
         * @access public
         */
        public function register_ajax_handler() {

            add_action( "wp_ajax_wwppAddWholesaleRoleGeneralDiscountMapping"    , array( $this , 'add_wholesale_role_general_discount_mapping' ) );
            add_action( "wp_ajax_wwppEditWholesaleRoleGeneralDiscountMapping"   , array( $this , 'edit_wholesale_role_general_discount_mapping' ) );
            add_action( "wp_ajax_wwppDeleteWholesaleRoleGeneralDiscountMapping" , array( $this , 'delete_wholesale_role_general_discount_mapping' ) );

        }

        /**
         * Execute model.
         *
         * @since 1.14.0
         * @access public
         */
        public function run() {

            add_action( 'init' , array( $this , 'register_ajax_handler' ) );

        }

    }

}