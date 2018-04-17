<?php

//add admin settings
add_action('admin_init', 'zva_ct_admin_init');
add_action('admin_menu', 'zva_ct_plugin_menu');


/**
 * Add plugin admin settings
 */
function zva_ct_admin_init() {
    register_setting('zva_ct_group', 'zva_ct_key');
    register_setting('zva_ct_group', 'zva_ct_list');
}

/**
 * add menu to admin
 */
function zva_ct_plugin_menu() {
    add_options_page( 'Zenva Custom Template', 'Zenva Custom Template', 'manage_options', 'zva-ct', 'zva_ct_plugin_options' );
}

/**
 * show admin settings page
 */
function zva_ct_plugin_options() {
    ?>
    <div class="wrap">
        <?php screen_icon(); ?>
        <h2>Zenva Custom Template Mailchimp Keys</h2>
        <form action="options.php" method="post">
            <?php settings_fields('zva_ct_group'); ?>
            <?php do_settings_sections('zva_ct_group'); ?>
            <table class="form-table"> 
                <tr valign="top"> 
                    <th scope="row"><label>Mailchimp secret key</label></th> 
                    <td>
                        <input type="text" name="zva_ct_key" id="zva_ct_key" value="<?php echo get_option('zva_ct_key'); ?>" />
                        <br/>
                    </td>                
                </tr>                 
                <tr valign="top"> 
                    <th scope="row"><label>Mailchimp list ID</label></th> 
                    <td>
                        <input type="text" name="zva_ct_list" id="zva_ct_list" value="<?php echo get_option('zva_ct_list'); ?>" />
                        <br/>
                    </td>                
                </tr>                 
                
            </table> <?php @submit_button(); ?> 
        </form>
        
    </div>
    <?php
}

?>
