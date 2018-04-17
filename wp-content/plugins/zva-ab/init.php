<?php
/*
Plugin Name: Zenva AB Testing
Description: This is AB Testing plugin created by Ryan
Version: 1.0.0
Author: Ryan
*/
// function to create the DB / Options / Defaults					
function zva_ab_install() {

    global $wpdb;

    $table_name = $wpdb->prefix . "zva_ab_tests";
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table_name (
            `id` int(255) NOT NULL AUTO_INCREMENT,
            `name` varchar(50) CHARACTER SET utf8 NOT NULL,
            `desc_a` text CHARACTER SET utf8 NOT NULL,
            `desc_b` text CHARACTER SET utf8 NOT NULL,
            `views_a` int(50) NOT NULL,
            `views_b` int(50) NOT NULL,
            `revenue_a` int(50) NOT NULL,
            `revenue_b` int(50) NOT NULL,
            `is_active` boolean NOT NULL,
            `created_at` datetime NOT NULL,
            PRIMARY KEY (`id`)
          ) $charset_collate; ";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta($sql);
}

// run the install scripts upon plugin activation
register_activation_hook(__FILE__, 'zva_ab_install');

//menu items
add_action('admin_menu','zva_ab_menu');
function zva_ab_menu() {
	
	//this is the main item for the menu
	add_options_page('AB Testing', //page title
	'Zenva AB Testing', //menu title
	'manage_options', //capabilities
	'zva_ab_list', //menu slug
	'zva_ab_list' //function
	);
}

define('ROOTDIR', plugin_dir_path(__FILE__));
require_once(ROOTDIR . 'inc/zva-ab-list.php');
require_once(ROOTDIR . 'inc/zva-ab-test-variation.php');
require_once(ROOTDIR . 'inc/zva-ab-tester.php');
