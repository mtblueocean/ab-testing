jQuery( document ).ready( function ( $ ) {

    /*
     |------------------------------------------------------------------------------------------------------------------
     | Variable Declarations
     |------------------------------------------------------------------------------------------------------------------
     */

    var $discount_controls = $( ".discount-controls" ),
        $button_controls = $( ".button-controls" ),
        $wholesale_role_general_discount_mapping = $( "#wholesale-role-general-discount-mapping" ),
        errorMessageDuration = '10000',
        successMessageDuration = '5000';


    /*
     |------------------------------------------------------------------------------------------------------------------
     | Helper Functions
     |------------------------------------------------------------------------------------------------------------------
     */

    function removeTableNoItemsPlaceholder ( $table ) {

        $table.find("tbody").find(".no-items").remove();

    }

    function resetTableRowStyling () {

        $wholesale_role_general_discount_mapping
            .find( "tbody" )
            .find( "tr" )
            .each( function( index ) {

                index++; // we do this coz index is zero base

                if ( index % 2 == 0 ) {
                    // even
                    $( this )
                        .removeClass("odd")
                        .removeClass("alternate")
                        .addClass("even");

                } else {
                    // odd
                    $( this )
                        .removeClass("even")
                        .addClass("odd")
                        .addClass("alternate");

                }

            } );

    }

    function resetFields () {

        $discount_controls.find( "#wwpp-wholesale-roles" ).val( "" ).removeAttr( "disabled" ).trigger( "change" ).trigger( "chosen:updated" );
        $discount_controls.find( "#wwpp-wholesale-discount" ).val( "" );
    }

    function isNumber( n ) {
        return !isNaN(parseFloat(n)) && isFinite(n);
    }


    /*
     |------------------------------------------------------------------------------------------------------------------
     | Events
     |------------------------------------------------------------------------------------------------------------------
     */

    $button_controls.find( "#add-mapping" ).click( function () {

        var $this = $( this),
            checkPoint = true;

        $this.attr( 'disabled' , 'disabled' );
        $button_controls.addClass( 'processing' );

        var wholesale_role = $.trim( $discount_controls.find( "#wwpp-wholesale-roles" ).val() ),
            discount = $.trim( $discount_controls.find( "#wwpp-wholesale-discount" ).val() );

        if ( wholesale_role == "" ) {
            toastr.error( wwpp_discount_controls_custom_field_params.i18n_specify_wholesale_role , wwpp_discount_controls_custom_field_params.i18n_form_error , { "closeButton" : true , "showDuration" : errorMessageDuration } );
            checkPoint = false;
        }

        if ( discount == "" || !isNumber( discount ) ) {
            toastr.error( wwpp_discount_controls_custom_field_params.i18n_input_discount_properly , wwpp_discount_controls_custom_field_params.i18n_form_error , { "closeButton" : true , "showDuration" : errorMessageDuration } );
            checkPoint = false;
        }

        if ( checkPoint ) {

            var discountMapping = {
                wholesale_role : wholesale_role,
                general_discount : discount
            };

            wwppBackendAjaxServices.addWholesaleRoleGeneralDiscount( discountMapping )
                .done( function ( data , textStatus , jqXHR ) {

                    if ( data.status == 'success' ) {

                        toastr.success( '' , wwpp_discount_controls_custom_field_params.i18n_role_successfully_added , { "closeButton" : true , "showDuration" : successMessageDuration } );

                        removeTableNoItemsPlaceholder( $wholesale_role_general_discount_mapping );

                        var tr_class    =   "";

                        if( $wholesale_role_general_discount_mapping.find( "tr" ).length % 2 == 0 ) // currently even, next add (our add) would make it odd
                            tr_class    =   "odd alternate";
                        else // currently odd, next add (our add) would make it even
                            tr_class    =   "even";

                        $wholesale_role_general_discount_mapping.find( "tbody" )
                            .append('<tr class="'+tr_class+' edited">' +
                                        '<td class="meta hidden"></td>' +
                                        '<td class="wholesale_role">' + discountMapping.wholesale_role + '</td>' +
                                        '<td class="general_discount">' + discountMapping.general_discount + '</td>' +
                                        '<td class="controls">' +
                                            '<a class="edit dashicons dashicons-edit"></a>' +
                                            '<a class="delete dashicons dashicons-no"></a>' +
                                        '</td>' +
                                    '</tr>');

                        resetFields();

                        // Remove edited class to the recently added user field
                        setTimeout(function(){
                            $wholesale_role_general_discount_mapping
                                .find("tr.edited")
                                .removeClass("edited");
                        },500);

                    } else {

                        toastr.error( data.error_message , wwpp_discount_controls_custom_field_params.i18n_fail_role_add , { "closeButton" : true , "showDuration" : errorMessageDuration } );

                        console.log( wwpp_discount_controls_custom_field_params.i18n_fail_role_add );
                        console.log( data );
                        console.log( '----------' );

                    }

                } )
                .fail( function ( jqXHR , textStatus , data ) {

                    toastr.error( jqXHR.responseText , wwpp_discount_controls_custom_field_params.i18n_fail_role_add , { "closeButton" : true , "showDuration" : errorMessageDuration } );

                    console.log( wwpp_discount_controls_custom_field_params.i18n_fail_role_add );
                    console.log( jqXHR );
                    console.log( '----------' );

                } )
                .always( function ( ) {

                    $this.removeAttr( 'disabled' );
                    $button_controls.removeClass( 'processing' );

                } );

        } else {

            $this.removeAttr( 'disabled' );
            $button_controls.removeClass( 'processing' );

        }

    } );

    $button_controls.find( "#save-mapping" ).click( function () {

        var $this = $( this),
            checkPoint = true;

        $this.attr( 'disabled' , 'disabled' );
        $button_controls.addClass( 'processing' );

        var wholesale_role = $.trim( $discount_controls.find( "#wwpp-wholesale-roles" ).val() ),
            discount = $.trim( $discount_controls.find( "#wwpp-wholesale-discount" ).val() );

        if ( wholesale_role == "" ) {
            toastr.error( wwpp_discount_controls_custom_field_params.i18n_specify_wholesale_role , wwpp_discount_controls_custom_field_params.i18n_form_error , { "closeButton" : true , "showDuration" : errorMessageDuration } );
            checkPoint = false;
        }

        if ( discount == "" || !isNumber( discount ) ) {
            toastr.error( wwpp_discount_controls_custom_field_params.i18n_input_discount_properly , wwpp_discount_controls_custom_field_params.i18n_form_error , { "closeButton" : true , "showDuration" : errorMessageDuration } );
            checkPoint = false;
        }

        if ( checkPoint ) {

            var discountMapping = {
                wholesale_role   : wholesale_role,
                general_discount : discount
            };

            wwppBackendAjaxServices.editWholesaleRoleGeneralDiscount( discountMapping )
                .done( function ( data , textStatus , jqXHR ) {

                    if ( data.status == 'success' ) {

                        $wholesale_role_general_discount_mapping.find( "tr.edited" )
                            .find( ".wholesale_role" ).text( discountMapping.wholesale_role ).end()
                            .find( ".general_discount" ).text( discountMapping.general_discount );

                        $wholesale_role_general_discount_mapping.find( "tr .controls .dashicons" )
                            .css( "display" , "inline-block" );

                        resetFields();

                        // Remove edited class to the recently added user field
                        setTimeout(function(){
                            $wholesale_role_general_discount_mapping
                                .find( "tr.edited" )
                                .removeClass( "edited" );
                        },500);

                        $button_controls
                            .removeClass( "edit-mode" )
                            .addClass( "add-mode" );

                        toastr.success( '' , wwpp_discount_controls_custom_field_params.i18n_role_successfully_updated , { "closeButton" : true , "showDuration" : successMessageDuration } );

                    } else {

                        toastr.error( data.error_message , wwpp_discount_controls_custom_field_params.i18n_fail_role_update , { "closeButton" : true , "showDuration" : errorMessageDuration } );

                        console.log( wwpp_discount_controls_custom_field_params.i18n_fail_role_update );
                        console.log( data );
                        console.log( '----------' );

                    }

                } )
                .fail( function ( jqXHR , textStatus , data ) {

                    toastr.error( jqXHR.responseText , wwpp_discount_controls_custom_field_params.i18n_fail_role_update , { "closeButton" : true , "showDuration" : errorMessageDuration } );

                    console.log( wwpp_discount_controls_custom_field_params.i18n_fail_role_update );
                    console.log( jqXHR );
                    console.log( '----------' );

                } )
                .always( function ( ) {

                    $this.removeAttr( 'disabled' );
                    $button_controls.removeClass( 'processing' );

                } );

        } else {

            $this.removeAttr( 'disabled' );
            $button_controls.removeClass( 'processing' );

        }

    } );

    $button_controls.find( "#cancel-edit-mapping" ).click( function () {

        resetFields();

        $button_controls
            .removeClass( "edit-mode" )
            .addClass( "add-mode" );

        $wholesale_role_general_discount_mapping
            .find( "tbody tr" )
                .removeClass( "edited" )
            .find( ".controls .dashicons" )
                .css( "display" , "inline-block" );

    } );

    $wholesale_role_general_discount_mapping.delegate( '.edit' , 'click' , function () {

        var $this = $( this ),
            $currentTr = $this.closest( 'tr' );

        $currentTr.addClass( "edited" );
        $wholesale_role_general_discount_mapping.find( ".controls .dashicons" )
            .css( "display" , "none" );

        var currMapping = {
            'wholesale_role' : $.trim( $currentTr.find( ".wholesale_role" ).text() ),
            'general_discount' : $.trim( $currentTr.find( ".general_discount" ).text() )
        };

        $discount_controls.find( "#wwpp-wholesale-roles" ).val( currMapping.wholesale_role ).attr( "disabled" , "disabled" ).trigger( "change" ).trigger( "chosen:updated" );
        $discount_controls.find( "#wwpp-wholesale-discount" ).val( currMapping.general_discount );
        $button_controls
            .removeClass( "add-mode" )
            .addClass( "edit-mode" );

    } );

    $wholesale_role_general_discount_mapping.delegate( '.delete' , 'click' , function () {

        var $this = $( this ),
            $currentTr = $this.closest( 'tr' );

        $currentTr.addClass( "edited" );

        if ( confirm( wwpp_discount_controls_custom_field_params.i18n_click_ok_remove_mapping ) ) {

            var wholesaleRole = $.trim( $currentTr.find( ".wholesale_role" ).text() );

            $wholesale_role_general_discount_mapping.find( ".controls .dashicons" )
                .css( "display" , "none" );

            wwppBackendAjaxServices.deleteWholesaleRoleGeneralDiscount( wholesaleRole )
                .done( function ( data , textStatus , jqXHR ) {

                    if ( data.status == 'success' ) {

                        $currentTr.fadeOut( "fast" , function () {

                            $currentTr.remove();

                            resetTableRowStyling();

                            // If no more item then append the empty table placeholder
                            if ( $wholesale_role_general_discount_mapping.find( "tbody" ).find( "tr" ).length <= 0 ) {

                                $wholesale_role_general_discount_mapping
                                    .find("tbody")
                                    .html(  '<tr class="no-items">' +
                                                '<td class="colspanchange" colspan="3">' + wwpp_discount_controls_custom_field_params.i18n_no_mappings_found + '</td>' +
                                            '</tr>');

                            }

                        } );

                        toastr.success( '' , wwpp_discount_controls_custom_field_params.i18n_role_successfully_deleted , { "closeButton" : true , "showDuration" : successMessageDuration } );

                    } else {

                        toastr.error( data.error_message , wwpp_discount_controls_custom_field_params.i18n_fail_delete_role , { "closeButton" : true , "showDuration" : errorMessageDuration } );

                        console.log( wwpp_discount_controls_custom_field_params.i18n_fail_delete_role );
                        console.log( data );
                        console.log( '----------' );

                    }

                } )
                .fail( function ( jqXHR , textStatus , errorThrown ) {

                    toastr.error( jqXHR.responseText , wwpp_discount_controls_custom_field_params.i18n_fail_delete_role , { "closeButton" : true , "showDuration" : errorMessageDuration } );

                    console.log( wwpp_discount_controls_custom_field_params.i18n_fail_delete_role );
                    console.log( jqXHR );
                    console.log( '----------' );

                } )
                .always( function () {

                    $wholesale_role_general_discount_mapping.find( ".controls .dashicons" )
                        .css( "display" , "inline-block" );

                } );

        } else
            $currentTr.removeClass( "edited" );

    } );




    /*
     |------------------------------------------------------------------------------------------------------------------
     | On Page Load
     |------------------------------------------------------------------------------------------------------------------
     */

    $discount_controls.find( "#wwpp-wholesale-roles" ).chosen( { allow_single_deselect : true } );

} );