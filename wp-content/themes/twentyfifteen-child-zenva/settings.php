<?php

//theme admin settings
function zva_theme_admin_menus() {
    add_submenu_page('themes.php', 'Zenva Theme Settings', 'Zenva Theme Settings', 'manage_options', 'front-page-elements', 'zva_theme_settings'); 
}
 

add_action('admin_init', 'zva_theme_admin_init');
add_action("admin_menu", "zva_theme_admin_menus");

function zva_theme_admin_init() {
    //register settings
    register_setting('zva-theme', 'zva_logo_id');
    register_setting('zva-theme', 'zva_essentials_text');
    register_setting('zva-theme', 'zva_essentials_cat_id');
    register_setting('zva-theme', 'zva_latest_text');
    register_setting('zva-theme', 'zva_extra_cat_id_1');
    register_setting('zva-theme', 'zva_extra_cat_id_2');
    register_setting('zva-theme', 'zva_extra_cat_id_3');
    register_setting('zva-theme', 'zva_read_text');
    register_setting('zva-theme', 'zva_google_analytics');
    register_setting('zva-theme', 'zva_google_webmaster');
    register_setting('zva-theme', 'zva_modal_url');
    register_setting('zva-theme', 'zva_signup_modal_id');
    register_setting('zva-theme', 'zva_adsense_on');
    register_setting('zva-theme', 'zva_adsense_client');
    register_setting('zva-theme', 'zva_adense_slot');
    register_setting('zva-theme', 'zva_fb_pixel');
    register_setting('zva-theme', 'zvapfa_feed_url');
    register_setting('zva-theme', 'zva_za_blog_code');
}

function zva_theme_settings() {
    // Check that the user is allowed to update options
    if (!current_user_can('manage_options')) {
        wp_die('You do not have sufficient permissions to access this page.');
    }
    
    ?>
    <div class="wrap">
        <?php screen_icon('themes'); ?> <h2>Zenva Theme Settings</h2>
 
        <form action="options.php" method="post">
            <?php settings_fields('zva-theme'); ?>
            <?php @do_settings_fields('zva-theme'); ?> 
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        <label>
                            Logo media ID:
                        </label> 
                    </th>
                    <td>
                        <input type="text" name="zva_logo_id" size="25" value="<?php echo get_option('zva_logo_id'); ?>" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label>
                            Essentials text:
                        </label> 
                    </th>
                    <td>
                        <input type="text" name="zva_essentials_text" size="25" value="<?php echo get_option('zva_essentials_text') ? get_option('zva_essentials_text') : 'Essentials'; ?>" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label>
                            Essentials Category ID:
                        </label> 
                    </th>
                    <td>
                        <input type="text" name="zva_essentials_cat_id" size="25" value="<?php echo get_option('zva_essentials_cat_id'); ?>" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label>
                            Latest text:
                        </label> 
                    </th>
                    <td>
                        <input type="text" name="zva_latest_text" size="25" value="<?php echo get_option('zva_latest_text') ? get_option('zva_latest_text') : 'Latest Articles'; ?>" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label>
                            Optional Extra Category ID #1:
                        </label> 
                    </th>
                    <td>
                        <input type="text" name="zva_extra_cat_id_1" size="25" value="<?php echo get_option('zva_extra_cat_id_1'); ?>" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label>
                            Optional Extra Category ID #2:
                        </label> 
                    </th>
                    <td>
                        <input type="text" name="zva_extra_cat_id_2" size="25" value="<?php echo get_option('zva_extra_cat_id_2'); ?>" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label>
                            Optional Extra Category ID #3:
                        </label> 
                    </th>
                    <td>
                        <input type="text" name="zva_extra_cat_id_3" size="25" value="<?php echo get_option('zva_extra_cat_id_3'); ?>" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label>
                            "Read" text:
                        </label> 
                    </th>
                    <td>
                        <input type="text" name="zva_read_text" size="25" value="<?php echo get_option('zva_read_text') ? get_option('zva_read_text') : 'Read'; ?>" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label>
                            Google Analytics ID:
                        </label> 
                    </th>
                    <td>
                        <input type="text" name="zva_google_analytics" size="25" value="<?php echo get_option('zva_google_analytics'); ?>" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label>
                            Google Webmaster Verification:
                        </label> 
                    </th>
                    <td>
                        <input type="text" name="zva_google_webmaster" size="25" value="<?php echo get_option('zva_google_webmaster'); ?>" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label>
                            Modal dialog URL:
                        </label> 
                    </th>
                    <td>
                        <input type="text" name="zva_modal_url" size="25" value="<?php echo get_option('zva_modal_url'); ?>" />
                    </td>
                </tr>     
                <tr valign="top">
                    <th scope="row">
                        <label>
                            Signup modal image id:
                        </label> 
                    </th>
                    <td>
                        <input type="text" name="zva_signup_modal_id" size="25" value="<?php echo get_option('zva_signup_modal_id'); ?>" />
                    </td>
                </tr>  
                <tr valign="top">
                    <th scope="row">
                        <label>
                            Adsense on:
                        </label> 
                    </th>
                    <td>
                        <input type="text" name="zva_adsense_on" size="25" value="<?php echo get_option('zva_adsense_on'); ?>" />
                    </td>
                </tr>  
                <tr valign="top">
                    <th scope="row">
                        <label>
                            Adsense client:
                        </label> 
                    </th>
                    <td>
                        <input type="text" name="zva_adsense_client" size="25" value="<?php echo get_option('zva_adsense_client'); ?>" />
                    </td>
                </tr>  
                <tr valign="top">
                    <th scope="row">
                        <label>
                            Adsense slot:
                        </label> 
                    </th>
                    <td>
                        <input type="text" name="zva_adense_slot" size="25" value="<?php echo get_option('zva_adense_slot'); ?>" />
                    </td>
                </tr>  
                <tr valign="top">
                    <th scope="row">
                        <label>
                            Facebook pixel code:
                        </label> 
                    </th>
                    <td>
                        <input type="text" name="zva_fb_pixel" size="25" value="<?php echo get_option('zva_fb_pixel'); ?>" />
                    </td>
                </tr>  
                <tr valign="top">
                    <th scope="row">
                        <label>
                            Product feed URL:
                        </label> 
                    </th>
                    <td>
                        <input type="text" name="zvapfa_feed_url" size="25" value="<?php echo get_option('zvapfa_feed_url'); ?>" />
                    </td>
                </tr>  
                <tr valign="top">
                    <th scope="row">
                        <label>
                            ZA blog code (eg. html5hive):
                        </label> 
                    </th>
                    <td>
                        <input type="text" name="zva_za_blog_code" size="25" value="<?php echo get_option('zva_za_blog_code'); ?>" />
                    </td>
                </tr>  
                
            </table><?php @submit_button(); ?> 
        </form>
    </div>
<?php
}
