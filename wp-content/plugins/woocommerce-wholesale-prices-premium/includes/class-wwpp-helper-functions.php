<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WWPP_Helper_Functions' ) ) {

    /**
     * Model that house various generic plugin helper functions.
     *
     * @since 1.12.8
     */
    class WWPP_Helper_Functions {

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
         * @var WWPP_Helper_Functions
         */
        private static $_instance;




        /*
        |--------------------------------------------------------------------------
        | Class Methods
        |--------------------------------------------------------------------------
        */

        /**
         * WWPP_Helper_Functions constructor.
         *
         * @since 1.12.8
         * @access public
         */
        public function __construct() {}

        /**
         * Ensure that only one instance of WWPP_Helper_Functions is loaded or can be loaded (Singleton Pattern).
         *
         * @since 1.12.8
         * @access public
         * 
         * @return WWPP_Helper_Functions
         */
        public static function instance() {

            if ( !self::$_instance instanceof self )
                self::$_instance = new self();

            return self::$_instance;

        }

        /**
        * Check validity of a save post action.
        *
        * @since 1.0.0
        * @access public
        *
        * @param int    $post_id   Id of the coupon post.
        * @param string $post_type Post type to check.
        * @return bool True if valid save post action, False otherwise.
        */
        public function check_if_valid_save_post_action( $post_id , $post_type ) {

            if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) || !current_user_can( 'edit_post' , $post_id ) || get_post_type() != $post_type || empty( $_POST ) )
                return false;
            else
                return true;
            
        }
        
    }

}