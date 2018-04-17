<?php
/*
Plugin Name: Zenva Azure Media Player
Plugin URI: http://www.zenva.com
Description: Include Azure Media Library.
Version: 1.0
Author: Ryan
Author URI: http://www.zenva.com
License: GPL2
*/

// Hooks
add_action( 'init', 'zva_amp_render_scripts' );

// Functions.
function zva_amp_render_scripts() {
    wp_enqueue_script( 'zva_azure_media_script', plugins_url() . '/zva_azure_player/lib/azuremediaplayer.min.js', array('jquery') );
    wp_enqueue_style( 'zva_azure_media_style', plugins_url() . '/zva_azure_player/lib/azuremediaplayer.min.css', false );
}

?>