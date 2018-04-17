<?php
/*
Plugin Name: Zenva Custom Email
Plugin URI: http://www.zenva.com
Description: Change Woocommerce Email Template
Version: 1.0
Author: Ryan
Author URI: http://www.zenva.com
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

define( 'ZENVA_CE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

include( ZENVA_CE_PLUGIN_DIR . 'zva_ce_settings.php' );
include( ZENVA_CE_PLUGIN_DIR . 'zva_ce_init.php' );

?>
