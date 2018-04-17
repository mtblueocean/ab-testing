/* global jQuery */
jQuery( document ).ready( function( $ ) {

    var wholesale_roles = wwpp_custom_bulk_actions_params.wholesale_roles;

    function wholesale_role_wholesale_minimum_order_quantity( event , data ) {

        var value = window.prompt( wwpp_custom_bulk_actions_params.i18n_prompt_message );

        if ( value != null )
            data.value = value;

        return data;

    }

    for ( var role in wholesale_roles ) {
        if ( wholesale_roles.hasOwnProperty( role ) ) {
            $( 'select.variation_actions' ).on( role + "_wholesale_min_order_qty_ajax_data" , wholesale_role_wholesale_minimum_order_quantity );
        }
    }

} );
