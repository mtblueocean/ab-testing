<?php if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WWPP_Wholesale_Role_Tax_Option_Mapping' ) ) {

    /**
     * Model that houses the logic of wholesale role tax options mapping.
     *
     * @since 1.14.0
     */
    class WWPP_Wholesale_Role_Tax_Option_Mapping {

        /*
        |--------------------------------------------------------------------------
        | Class Properties
        |--------------------------------------------------------------------------
        */

        /**
         * Property that holds the single main instance of WWPP_Wholesale_Role_Tax_Option_Mapping.
         *
         * @since 1.14.0
         * @access private
         * @var WWPP_Wholesale_Role_Tax_Option_Mapping
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
         * WWPP_Wholesale_Role_Tax_Option_Mapping constructor.
         *
         * @since 1.14.0
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_Wholesale_Role_Tax_Option_Mapping model.
         */
        public function __construct( $dependencies ) {

            $this->_wwpp_wholesale_roles = $dependencies[ 'WWPP_Wholesale_Roles' ];

        }

        /**
         * Ensure that only one instance of WWPP_Wholesale_Role_Tax_Option_Mapping is loaded or can be loaded (Singleton Pattern).
         *
         * @since 1.14.0
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_Wholesale_Role_Tax_Option_Mapping model.
         * @return WWPP_Wholesale_Role_Tax_Option_Mapping
         */
        public static function instance( $dependencies ) {

            if ( !self::$_instance instanceof self )
                self::$_instance = new self( $dependencies );

            return self::$_instance;

        }

        /**
         * Add an entry to wholesale role / tax option mapping.
         * Design based on trust that the caller will supply an array with the following elements below.
         *
         * wholesale_role
         * tax_exempted
         *
         * @since 1.4.7
         * @since 1.14.0 Refactor codebase and move to its proper model.
         * @access public
         *
         * @param null|array $mapping Mapping entry.
         * @return array Operation status.
         */
        public function wwpp_add_wholesale_role_tax_option( $mapping = null ) {

            if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
                $mapping = $_POST[ 'mapping' ];

            $tax_option_mapping = get_option( WWPP_OPTION_WHOLESALE_ROLE_TAX_OPTION_MAPPING , array() );
            if ( !is_array( $tax_option_mapping ) )
                $tax_option_mapping = array();

            if ( array_key_exists( $mapping[ 'wholesale_role' ] , $tax_option_mapping ) ) {

                $response = array(
                    'status'        => 'fail',
                    'error_message' => __( 'Duplicate Wholesale Role Tax Option Entry, Already Exist' , 'woocommerce-wholesale-prices-premium' )
                );

            } else {

                $wholesale_role = $mapping[ 'wholesale_role' ];
                unset( $mapping[ 'wholesale_role' ] );

                $tax_option_mapping[ $wholesale_role ] = $mapping;

                update_option( WWPP_OPTION_WHOLESALE_ROLE_TAX_OPTION_MAPPING , $tax_option_mapping );

                $response = array( 'status' => 'success' );

            }

            if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

                @header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
                echo wp_json_encode( $response );
                wp_die();

            } else
                return $response;

        }

        /**
         * Edit an entry of wholesale role / tax option mapping.
         *
         * Design based on trust that the caller will supply an array with the following elements below.
         *
         * wholesale_role
         * tax_exempted
         *
         * @since 1.4.7
         * @since 1.14.0 Refactor codebase and move to its proper model.
         * @access public
         *
         * @param null|null $mapping Mapping entry.
         * @return array Operation status.
         */
        public function wwpp_edit_wholesale_role_tax_option( $mapping = null ) {

            if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
                $mapping = $_POST[ 'mapping' ];

            $tax_option_mapping = get_option( WWPP_OPTION_WHOLESALE_ROLE_TAX_OPTION_MAPPING , array() );
            if ( !is_array( $tax_option_mapping ) )
                $tax_option_mapping = array();

            if ( !array_key_exists( $mapping[ 'wholesale_role' ] , $tax_option_mapping ) ) {

                $response = array(
                    'status'        => 'fail',
                    'error_message' => __( 'Wholesale Role Tax Option Entry You Wish To Edit Does Not Exist' , 'woocommerce-wholesale-prices-premium' )
                );

            } else {

                $wholesale_role = $mapping[ 'wholesale_role' ];
                unset( $mapping[ 'wholesale_role' ] );

                $tax_option_mapping[ $wholesale_role ] = $mapping;

                update_option( WWPP_OPTION_WHOLESALE_ROLE_TAX_OPTION_MAPPING , $tax_option_mapping );

                $response = array( 'status' => 'success' );

            }

            if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

                @header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
                echo wp_json_encode( $response );
                wp_die();

            } else
                return $response;

        }

        /**
         * Delete an entry of wholesale role / tax option mapping.
         *
         * @since 1.4.7
         * @since 1.14.0 Refactor codebase and move to its proper model.
         * @access public
         *
         * @param null|string $wholesale_role Wholeslae role key.
         * @return array Operation status.
         */
        public function wwpp_delete_wholesale_role_tax_option( $wholesale_role = null ) {

            if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
                $wholesale_role = $_POST[ 'wholesale_role' ];

            $tax_option_mapping = get_option( WWPP_OPTION_WHOLESALE_ROLE_TAX_OPTION_MAPPING , array() );
            if ( !is_array( $tax_option_mapping ) )
                $tax_option_mapping = array();

            if ( !array_key_exists( $wholesale_role , $tax_option_mapping ) ) {

                $response = array(
                    'status'        => 'fail',
                    'error_message' => __( 'Wholesale Role Tax Option Entry You Wish To Delete Does Not Exist' , 'woocommerce-wholesale-prices-premium' )
                );

            } else {

                unset( $tax_option_mapping[ $wholesale_role ] );

                update_option( WWPP_OPTION_WHOLESALE_ROLE_TAX_OPTION_MAPPING , $tax_option_mapping );

                $response = array( 'status' => 'success' );

            }

            if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

                @header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
                echo wp_json_encode( $response );
                wp_die();

            } else
                return $response;

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

            add_action( "wp_ajax_wwpp_add_wholesale_role_tax_option"    , array( $this , 'wwpp_add_wholesale_role_tax_option' ) );
            add_action( "wp_ajax_wwpp_edit_wholesale_role_tax_option"   , array( $this , 'wwpp_edit_wholesale_role_tax_option' ) );
            add_action( "wp_ajax_wwpp_delete_wholesale_role_tax_option" , array( $this , 'wwpp_delete_wholesale_role_tax_option' ) );

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