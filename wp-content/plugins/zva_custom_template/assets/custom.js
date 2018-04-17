( function( $ ) {
    $( document ).ready( function() {
        var jForm = $('form.zct_subscribe_form'),
            jSubmitBtn = jForm.find('.zva-ct-submit');
        jSubmitBtn.on('click', function() {
            var email = jForm.find('input[name=zva_ct_email]').val(),
                page_id = jForm.find('input[name=zva_ct_page_id]').val(),
                zva_ct_ajax_url = jForm.find('input[name=plugin_path]').val();

            var data = {
                    email: email,
                    page_id: page_id
                };

            $.ajax({
                url: zva_ct_ajax_url + 'zva_ct_ajax.php',
                type: 'POST',
                data: data,
                success: function (response) {
                    var error_code = response.error_code,
                        email = response.email,
                        success_content = response.success_content,
                        jEmailSubscribeForm = $('.zct_email_subscribe'),
                        jAlertBox = $('.alert_box');

                    switch(error_code) {
                        case 0:
                            jAlertBox.hide();
                            jEmailSubscribeForm.html( success_content );
                            break;
                        case 1:
                            jAlertBox.html(email + ' is already subscribed to list Zenva.').show();
                            break;
                        case 2:
                            jAlertBox.html('Your ip address was exceeded to 20 limitation count.').show();
                            break;
                        case 3:
                            jAlertBox.html(email + ' is not valid address.').show();
                            break;
                        case 4:
                            jAlertBox.html('MMERGE4 is not present').show();
                            break;
                    }
                }
            });
        });
    } );
} )( jQuery );
