<?php

include( plugin_dir_path( __FILE__ ) . 'lib/mailchimp/MailChimp.php');

add_action('init', 'zva_ef_register_shortcodes');
add_action('wp_enqueue_scripts', 'zva_ef_modal_script_load');

add_action('wp_ajax_zva_ef_submit', 'zva_ef_submit');
add_action('wp_ajax_nopriv_zva_ef_submit', 'zva_ef_submit');

function zva_ef_register_shortcodes() {
    add_shortcode('zva-file-link', 'zva_ef_shortcode');
}

function zva_ef_modal_script_load() {
    wp_enqueue_script('jquery_modal', plugins_url() . '/zva_email_file/lib/modal/jquery.modal.min.js', array('jquery'));
    wp_enqueue_script('zva_ef_shortcode', plugins_url() . '/zva_email_file/assets/js/zva_ef_shortcode.js', array('jquery'));
    wp_localize_script('zva_ef_shortcode', 'zva_ef_ajax', ['ajax_url' => admin_url( 'admin-ajax.php')]);

    wp_enqueue_style('jquery_modal', plugins_url() . '/zva_email_file/lib/modal/jquery.modal.min.css', false);
    wp_enqueue_style('zva_ef_shortcode', plugins_url() . '/zva_email_file/assets/css/zva_ef_shortcode.css', false);
}

function zva_ef_submit() {
    global $wpdb;
    global $post;

    $table_name = $wpdb->prefix . "zva_ef_link";

    $headers = 'From: Zenva Automated Email <noreply@zenva.com>' . "\r\n";
    add_filter('wp_mail_content_type', 'zva_ef_content_type');

    $emailTo = $_POST['email'];
    $file_attachment_id = $_POST['file_id'];

    // Get file link by media attachment id.
    $url = wp_get_attachment_url($file_attachment_id);
    $url_explode_by_wp_content = explode('/wp-content/', $url);
    $file_link = get_home_url() . '/wp-content/' . $url_explode_by_wp_content[1];

    // Get file name by media attachment id.
    $file = get_post($file_attachment_id);
    $file_title = $file->post_title ? $file->post_title : 'Asset files';

    $subject = $file_title;
    $body = file_get_contents(plugins_url() . '/zva_email_file/templates/zva_ef_template.php');
    // Replace email template with real data.
    $body = str_replace(['put_file_title_here', 'put_file_url_here', 'put_current_year_here'], [$file_title, $file_link, date('Y')], $body);
    $ip = zva_ef_client_ip();

    // Validate email address.
    if (filter_var($emailTo, FILTER_VALIDATE_EMAIL)) {
        $one_day_before_date = date('Y-m-d h:i:s', strtotime(date('Y-m-d h:i:s') . ' -1 day'));
        $rows_by_ip = $wpdb->get_results("SELECT * FROM $table_name WHERE `email` = '$emailTo' AND `ip_address` = '$ip' AND `created_at` > '$one_day_before_date'");

        if (count($rows_by_ip) < 20) {
            if (wp_mail($emailTo, $subject, $body, $headers)) {
                // Subscribe user to mailchimp if the email address is not present in the database.
                $exist_email = $wpdb->get_results("SELECT * FROM $table_name WHERE `email` = '$emailTo'");
                if (empty($exist_email)) {
                    zva_ef_subscribe_mailchimp($emailTo, $file_link);
                }

                $wpdb->insert(
                    $table_name,
                    [
                        'email' => $emailTo,
                        'file_url' => $file_link,
                        'ip_address' => $ip,
                        'created_at' => date('Y-m-d h:i:s')
                    ]
                );

                $error_code = 0; // Success
            } else {
                $error_code = 1; // WP Mail Failure
            }

            remove_filter('wp_mail_content_type', 'zva_ef_content_type');
        } else {
            $error_code = 2; // Exceed limit of requests per ip.
        }
    } else {
        $error_code = 3; // Invalid Email address
    }

    wp_send_json(['error_code' => $error_code]);

    exit();
}

function zva_ef_content_type() {
    return 'text/html';
}

function zva_ef_client_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    return $ip;
}

function zva_ef_subscribe_mailchimp($email_submitted, $media_file_url) {
    $mailchimp = new DrewM\MailChimp\MailChimp(get_option('zva_ef_key'));

    try{                 
        $res = $mailchimp->post("lists/".get_option('zva_ef_list')."/members", array(
            'email_address' => $email_submitted,
            'status'       => 'subscribed',
            'ip_signup' => zva_ef_client_ip(),
            'merge_fields' => array(
                'MMERGE3' => 'en',
                'MMERGE4' => $media_file_url
            )
        ));
    }
    catch(Exception $e) {
        error_log($e->getMessage());
    }
}

function zva_ef_shortcode($args) {
    global $post;
    $file = get_post($args['id']);

    if($file) {
        $url = wp_get_attachment_url($args['id']);

        $featured_img_url = get_the_post_thumbnail_url($post->ID, 'medium');

        $zva_hidden_ele = '<input type="hidden" class="zva_post_title" value="' . $post->post_title . '" />' .
            '<input type="hidden" class="zva_post_image_url" value="' . $featured_img_url . '" />';

        if($url) {
            $result = '<a href="javascript:void(0)" class="zva_ef_shortcode_link" data-file-id="' . $args['id'] . '">' . $args['text'] . $zva_hidden_ele . '</a>';
            return $result;
        }
    }
}
