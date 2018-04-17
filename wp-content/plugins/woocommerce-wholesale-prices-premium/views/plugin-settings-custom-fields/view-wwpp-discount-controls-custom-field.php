<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<tr valign="top">
    <th colspan="2" scope="row" class="titledesc">
        <div class="discount-controls">

            <div class="field-container">

                <label for="wwpp-wholesale-roles"><?php _e( 'Wholesale Role' , 'woocommerce-wholesale-prices-premium' ); ?></label>
                <select id="wwpp-wholesale-roles" data-placeholder="<?php _e( 'Choose wholesale role...' , 'woocommerce-wholesale-prices-premium' ); ?>">
                    <option value=""></option>
                    <?php foreach ( $all_wholesale_roles as $wholesaleRoleKey => $wholesaleRole ) { ?>
                        <option value="<?php echo $wholesaleRoleKey ?>"><?php echo $wholesaleRole[ 'roleName' ]; ?></option>
                    <?php } ?>
                </select>

            </div>

            <div class="field-container">

                <label for="wwpp-wholesale-discount"><?php _e( 'Percent Discount' , 'woocommerce-wholesale-prices-premium' ); ?></label>
                <input type="number" min="0" step="1" id="wwpp-wholesale-discount"/>
                <p class="desc"> <?php _e( 'General discount for products purchase by this wholesale role.<br/>In percent (%), Ex. 3 percent then input 3, 30 percent then input 30, 0.3 percent then input 0.3.' , 'woocommerce-wholesale-prices-premium' ); ?></p>

            </div>

            <div style="clear: both; float: none; display: block;"></div>

        </div>

        <div class="button-controls add-mode">

            <input type="button" id="cancel-edit-mapping" class="button button-secondary" value="<?php _e( 'Cancel' , 'woocommerce-wholesale-prices-premium' ); ?>"/>
            <input type="button" id="save-mapping" class="button button-primary" value="<?php _e( 'Save Mapping' , 'woocommerce-wholesale-prices-premium' ); ?>"/>
            <input type="button" id="add-mapping" class="button button-primary" value="<?php _e( 'Add Mapping' , 'woocommerce-wholesale-prices-premium' ); ?>"/>
            <span class="spinner"></span>

            <div style="clear: both; float: none; display: block;"></div>

        </div>

        <table id="wholesale-role-general-discount-mapping" class="wp-list-table widefat">
            <thead>
                <tr>
                    <th><?php _e( 'Wholesale Role' , 'woocommerce-wholesale-prices-premium' ); ?></th>
                    <th><?php _e( 'General Discount' , 'woocommerce-wholesale-prices-premium' ); ?></th>
                    <th></th>
                </tr>
            </thead>

            <tfoot>
                <tr>
                    <th><?php _e( 'Wholesale Role' , 'woocommerce-wholesale-prices-premium' ); ?></th>
                    <th><?php _e( 'General Discount' , 'woocommerce-wholesale-prices-premium' ); ?></th>
                    <th></th>
                </tr>
            </tfoot>

            <?php
            if ( $saved_general_discount ) {

                $itemNumber =   0;

                foreach( $saved_general_discount as $wholesale_role => $discount ) {
                    $itemNumber++;

                    if ( $itemNumber % 2 == 0 ) { // even  ?>
                        <tr class="even">
                    <?php } else { // odd ?>
                        <tr class="odd alternate">
                    <?php } ?>

                        <td class="meta hidden"></td>
                        <td class="wholesale_role"><?php echo $wholesale_role; ?></td>
                        <td class="general_discount"><?php echo $discount; ?></td>
                        <td class="controls">
                            <a class="edit dashicons dashicons-edit"></a>
                            <a class="delete dashicons dashicons-no"></a>
                        </td>

                    </tr>
                <?php
                }

            } else { ?>
                <tr class="no-items">
                    <td class="colspanchange" colspan="3"><?php _e( 'No Mappings Found' , 'woocommerce-wholesale-prices-premium' ); ?></td>
                </tr>
            <?php } ?>

        </table>
    </th>
</tr>

<style>
    p.submit {
        display: none !important;
    }
</style>
