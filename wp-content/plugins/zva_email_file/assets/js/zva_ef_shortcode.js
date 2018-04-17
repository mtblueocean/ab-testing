( function( $ ) {
    $( document ).ready( function() {
        var jSubmitBtn = $('.zva-ef-modal .zva-ef-submit'),
            jCloseModalBtn = $('.zva-ef-modal .zva-ef-close'),
            jShortCodeLink = $('.zva_ef_shortcode_link');

        jShortCodeLink.on('click', function() {
            var jZvaModal = $('.zva-shortcode-modal');
            var jConfirmDialog = $('#zva_ef_confirm_dialog'),
                jConfirmDialogContent = jConfirmDialog.find('.content');

            // Insert post info to modal.
            jZvaModal.find('#zva_ef_modal_img').attr('src', $(this).find('.zva_post_image_url').val());
            jZvaModal.find('#zva_ef_modal_img').attr('alt', $(this).find('.zva_post_title').val());
            jZvaModal.find('input[name="zva_ef_file_id"]').val($(this).data('file-id'));

            var attachmentId = $(this).data('file-id');
            if (attachmentId) {
                jZvaModal.modal();
            } else {
                jConfirmDialogContent.html('<p>Please confirm to put the file ID within shortcode!</p>');
                jConfirmDialog.modal();
            }
        });

        jSubmitBtn.on('click', function() {
            var jForm = $(this).parents('.zva-ef-modal'),
                email = jForm.find('input[name=zva_ef_email]').val(),
                fileId = jForm.find('input[name=zva_ef_file_id]').val();

            var data = {
                    action: 'zva_ef_submit',
                    email: email,
                    file_id: fileId
                };

            var isValidEmail = jForm.find('form')[0].checkValidity();
            if(!isValidEmail) {
                return;
            }

            jForm.hide();
            $('body').addClass('zva_ef_loading').append('<div class="zva_ef_loader"></div>');

            $.ajax({
                url: zva_ef_ajax.ajax_url,
                type: 'POST',
                data: data,
                success: function (response) {
                    $('body').removeClass('zva_ef_loading').remove('.zva_ef_loader');

                    var errorCode = response.error_code;
                    var jConfirmDialog = $('#zva_ef_confirm_dialog'),
                        jConfirmDialogContent = jConfirmDialog.find('.content');

                    switch(errorCode) {
                        case 0: // Success
                            jConfirmDialogContent.html('<p>The file download link has been sent to your email!</p>');
                            jConfirmDialog.modal();
                            break;
                        case 1: // WP Mail Failure
                            jConfirmDialogContent.html('<p>Something was wrong! Please try later or contact to administrator.</p>');
                            jConfirmDialog.modal();
                            break;
                        case 2: // Exceed limit of requests per ip.
                            jConfirmDialogContent.html('<p>This IP address has made more than 20 requests in total within the last 24 hours.</p>');
                            jConfirmDialog.modal();
                            break;
                        case 3: // Invalid Email address
                            jConfirmDialogContent.html('<p>Your entered email address is not valid. Please use valid email address!</p>');
                            jConfirmDialog.modal();
                            break;
                    }
                }
            });
        });

        jCloseModalBtn.on('click', function() {
            $.modal.close();
        });
    } );
} )( jQuery );
