<?php

//add admin settings
add_action('admin_init', 'zva_ef_admin_init');
add_action('wp_footer', 'zva_ef_init');
add_action('admin_menu', 'zva_ef_plugin_menu');

/**
 * Add plugin admin settings
 */
function zva_ef_admin_init() {
    register_setting('zva_ef_group', 'zva_ef_key');
    register_setting('zva_ef_group', 'zva_ef_list');
}

/**
 * add menu to admin
 */
function zva_ef_plugin_menu() {
    add_options_page( 'Zenva Email File Links', 'Zenva Email File Links', 'manage_options', 'zva-ef', 'zva_ef_plugin_options' );
}


function zva_ef_init() {
    ob_start();
    include(plugin_dir_path( __FILE__ ) . 'templates/zva_ef_modal_template.php');
    echo ob_get_clean();
    ?>
 
    <?php
}

/**
 * show admin settings page
 */
function zva_ef_plugin_options() {
    ?>
    <div class="wrap">
        <?php screen_icon(); ?>
        <h2>Zenva Email File Link</h2>
        <form action="options.php" method="post">
            <?php settings_fields('zva_ef_group'); ?>
            <?php do_settings_sections('zva_ef_group'); ?>
            <table class="form-table"> 
                <tr valign="top"> 
                    <th scope="row"><label>Mailchimp secret key</label></th> 
                    <td>
                        <input type="text" name="zva_ef_key" id="zva_ef_key" value="<?php echo get_option('zva_ef_key'); ?>" />
                        <br/>
                    </td>                
                </tr>                 
                <tr valign="top"> 
                    <th scope="row"><label>Mailchimp list ID</label></th> 
                    <td>
                        <input type="text" name="zva_ef_list" id="zva_ef_list" value="<?php echo get_option('zva_ef_list'); ?>" />
                        <br/>
                    </td>                
                </tr>                 
                
            </table> <?php @submit_button(); ?> 
        </form>
        
    </div>
    <?php
}

?>
