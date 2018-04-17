<script>
    jQuery( document ).ready( function( $ ) {

        $( "#wholesale-visibility-select" ).chosen();

    } );
</script>
<div id="wholesale-visiblity" class="misc-pub-section">

    <strong><?php _e( 'Restrict To Wholesale Roles:' , 'woocommerce-wholesale-prices-premium' ); ?></strong>
    <p><em><?php _e( 'Set this product to be visible only to specified wholesale user role/s only' , 'woocommerce-wholesale-prices-premium' ); ?></em></p>

    <div id="wholesale-visibility-select-container">

        <select style="width: 100%;" data-placeholder="<?php _e( 'Choose wholesale users...' , 'woocommerce-wholesale-prices-premium' ); ?>" name="wholesale-visibility-select[]" id="wholesale-visibility-select" multiple>

        <?php foreach ( $all_registered_wholesale_roles as $role_key => $role ) { ?>
            <option value="<?php echo $role_key ?>" <?php if( in_array( $role_key , $product_wholesale_role_filter ) ) { _e( "selected" , "woocommerce-wholesale-prices-premium" ); } ?>><?php echo $role[ 'roleName' ]; ?></option>
        <?php } ?>

        </select><!--#wholesale-visibility-select-->

        <?php wp_nonce_field( 'wwpp_action_save_product_wholesale_role_visibility_filter' , 'wwpp_nonce_save_product_wholesale_role_visibility_filter' ); ?>

    </div><!--#wholesale-visibility-select-->

</div><!--#wholesale-visiblity-filter-->