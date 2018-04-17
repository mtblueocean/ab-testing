<?php

// Hooks.
add_action( 'admin_init', 'zva_ce_admin_init' );
add_action( 'admin_menu', 'zva_ce_plugin_menu' );

define( 'Zva_Q_List_Cnt', 3 );
define( 'Zva_R_List_Cnt', 3 );

// Functions.
function zva_ce_admin_init() {
    // Admin Settings for got questions section.
    for ( $i = 1; $i <= Zva_Q_List_Cnt; $i++ ) { 
        $zva_ce_q_title = 'zva_ce_q_title_' . $i;
        $zva_ce_q_url = 'zva_ce_q_url_' . $i;
        register_setting( 'zva_ce_settings_group', $zva_ce_q_title );
        register_setting( 'zva_ce_settings_group', $zva_ce_q_url );
    }

    // Admin Settings for extra resources.
    for ( $i = 1; $i <= Zva_R_List_Cnt; $i++ ) { 
        $zva_ce_r_title = 'zva_ce_r_title_' . $i;
        $zva_ce_r_url = 'zva_ce_r_url_' . $i;
        register_setting( 'zva_ce_settings_group', $zva_ce_r_title );
        register_setting( 'zva_ce_settings_group', $zva_ce_r_url );
    }

    register_setting( 'zva_ce_settings_group', 'zva_ce_company_name' );
    register_setting( 'zva_ce_settings_group', 'zva_ce_address' );
    register_setting( 'zva_ce_settings_group', 'zva_ce_postal_code' );
    register_setting( 'zva_ce_settings_group', 'zva_ce_country' );
    register_setting( 'zva_ce_settings_group', 'zva_ce_business_number' );
}

function zva_ce_plugin_menu() {
    add_options_page( 'Zenva Custom Email', 'Zenva Custom Email', 'manage_options', 'zva-ce', 'zva_ce_plugin_options' );
}

function zva_ce_plugin_options() {
    ?>
    <link type="text/css" href="<?php echo WP_PLUGIN_URL; ?>/zva_custom_email/assets/zva_ce_style.css" rel="stylesheet" />
    <div class="wrap">
        <?php screen_icon(); ?>
        <h2>Zenva Custom Email Settings</h2>
        <form class="zva-ce-form" action="options.php" method="post">
            <?php settings_fields( 'zva_ce_settings_group' ); ?>
            <?php do_settings_sections( 'zva_ce_settings_group' ); ?>

            <h2>Got Questions?</h2>
            <table class="form-table">
                <tr>
                    <th align="center">No</th><th>Title</th><th>URL</th>
                </tr>              
                <?php for ( $i = 1; $i <= Zva_Q_List_Cnt; $i++ ) { ?>
                <tr>
                    <td align="center" width="4%"><?php echo $i; ?></td>
                    <td width="48%"><input type="text" name="zva_ce_q_title_<?php echo $i; ?>" id="zva_ce_q_title_<?php echo $i; ?>" value="<?php echo get_option( 'zva_ce_q_title_' . $i ); ?>" /></td>
                    <td width="48%"><input type="text" name="zva_ce_q_url_<?php echo $i; ?>" id="zva_ce_q_url_<?php echo $i; ?>" value="<?php echo get_option( 'zva_ce_q_url_' . $i ); ?>" /></td>
                </tr>
                <?php } ?>
            </table>

            <h2>Want to check out some extra resources?</h2>
            <table class="form-table">
                <tr>
                    <th>No</th><th>Title</th><th>URL</th> 
                </tr>              
                <?php for ( $i = 1; $i <= Zva_R_List_Cnt; $i++ ) { ?>
                <tr>
                    <td align="center" width="4%"><?php echo $i; ?></td>
                    <td width="48%"><input type="text" name="zva_ce_r_title_<?php echo $i; ?>" id="zva_ce_r_title_<?php echo $i; ?>" value="<?php echo get_option( 'zva_ce_r_title_' . $i ); ?>" /></td>
                    <td width="48%"><input type="text" name="zva_ce_r_url_<?php echo $i; ?>" id="zva_ce_r_url_<?php echo $i; ?>" value="<?php echo get_option( 'zva_ce_r_url_' . $i ); ?>" /></td>
                </tr>
                <?php } ?>
            </table>

            <h2>Email Settings</h2>
            <table class="form-table business-info">
                <tr>
                    <th>Company Name: </th>
                    <td><input type="text" name="zva_ce_company_name" id="zva_ce_company_name" value="<?php echo get_option( 'zva_ce_company_name' ); ?>" /></td>
                </tr>
                <tr>
                    <th>Address: </th>
                    <td><input type="text" name="zva_ce_address" id="zva_ce_address" value="<?php echo get_option( 'zva_ce_address' ); ?>" /></td>
                </tr>
                <tr>
                    <th>Postal Code: </th>
                    <td><input type="text" name="zva_ce_postal_code" id="zva_ce_postal_code" value="<?php echo get_option( 'zva_ce_postal_code' ); ?>" /></td>
                </tr>
                <tr>
                    <th>Country: </th>
                    <td><input type="text" name="zva_ce_country" id="zva_ce_country" value="<?php echo get_option( 'zva_ce_country' ); ?>" /></td>
                </tr>
                <tr>
                    <th>Business Number: </th>
                    <td><input type="text" name="zva_ce_business_number" id="zva_ce_business_number" value="<?php echo get_option( 'zva_ce_business_number' ); ?>" /></td>
                </tr>
            </table>
            <?php submit_button(); ?> 
        </form>
    </div>
    <?php
}

?>
