<?php
/*
Plugin Name: Zenva Email File Links
Plugin URI: http://www.zenva.com
Description: Create file links for tutorial assets
Version: 1.2
Author: Zenva (Updated By Ryan)
Author URI: http://www.zenva.com
License: GPL2
*/

include( plugin_dir_path( __FILE__ ) . 'zva_settings.php');
include( plugin_dir_path( __FILE__ ) . 'zva_post_edit.php');
include( plugin_dir_path( __FILE__ ) . 'zva_ef_shortcode.php');

function zva_ef_install() {
    global $wpdb;

    $table_name = $wpdb->prefix . "zva_ef_link";
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

register_activation_hook(__FILE__, 'zva_ef_install');

?>
