jQuery( document ).ready( function( $ ) {

    var variations_data = JSON.parse( $( "form.variations_form" ).attr( "data-product_variations" ) );

    function update_variation_price_quantity_field_value() {
        
        var $variations_form = $( ".variations_form" ),
            variation_id     = $variations_form.find( ".single_variation_wrap .variation_id" ).attr( 'value' ),
            $qty_field       = $variations_form.find( ".variations_button .qty" );

        for ( var i = 0 ; i < variations_data.length ; i++ ) {

            if ( variations_data[ i ].variation_id == variation_id && variations_data[ i ].input_value ) {

                $qty_field.val( variations_data[ i ].input_value );
                break;

            } else
                $qty_field.val( variations_data[ i ].min_qty );

        }

    }

    $( "body" ).on( "woocommerce_variation_has_changed" , ".variations_form" , update_variation_price_quantity_field_value );
    $( "body" ).on( "found_variation" , ".variations_form" , update_variation_price_quantity_field_value ); // Only triggered on ajax complete

} );