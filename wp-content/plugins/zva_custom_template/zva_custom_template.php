<?php
/*
Plugin Name: Zenva Custom Template : 'Zva Custom Template'
Plugin URI: http://www.zenva.com
Description: Create custom template for landing page.
Version: 1.0
Author: Zenva
Author URI: http://www.zenva.com
License: GPL2
*/

include( plugin_dir_path( __FILE__ ) . 'zva_ct_settings.php');
include( plugin_dir_path( __FILE__ ) . 'zva_ct_meta_box.php');
include( plugin_dir_path( __FILE__ ) . 'zva_ct_init.php');

function zva_ct_install() {
    global $wpdb;

    $table_name = $wpdb->prefix . "zva_custom_template";
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table_name (
            `id` int(255) NOT NULL AUTO_INCREMENT,
            `email` varchar(255) CHARACTER SET utf8 NOT NULL,
            `file_url` varchar(255) CHARACTER SET utf8 NOT NULL,
            `ip_address` varchar(255) CHARACTER SET utf8 NOT NULL,
            `created_at` datetime NOT NULL,
            PRIMARY KEY (`id`)
          ) $charset_collate; ";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta($sql);
}

register_activation_hook(__FILE__, 'zva_ct_install');

?>
