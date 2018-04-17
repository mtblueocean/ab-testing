<?php

require_once( zva_get_wp_load_path() );

if (!class_exists('DrewM\MailChimp\MailChimp')) {
    include( plugin_dir_path( __FILE__ ) . 'lib/mailchimp/MailChimp.php');
}

global $wpdb;

$table_name = $wpdb->prefix . "zva_custom_template";

$ip = zva_ct_client_ip();
$email = $_POST['email'];
$page_id = $_POST['page_id'];

$file_link = get_post_meta($page_id, '_zva_ct_mmerge4_field', true);

if ($file_link) {
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $one_day_before_date = date('Y-m-d h:i:s', strtotime(date('Y-m-d h:i:s') . ' -1 day'));
        $rows_by_ip = $wpdb->get_results("SELECT * FROM $table_name WHERE `email` = '$email' AND `ip_address` = '$ip' AND `created_at` > '$one_day_before_date'");

        if (count($rows_by_ip) < 20) {
            $exist_email = $wpdb->get_results("SELECT * FROM $table_name WHERE `email` = '$email'");
            if (empty($exist_email)) {
                zva_ct_subscribe_mailchimp($email, $file_link);

                $wpdb->insert(
                    $table_name,
                    [
                        'email' => $email,
                        'file_url' => $file_link,
                        'ip_address' => $ip,
                        'created_at' => date('Y-m-d h:i:s')
                    ]
                );

                $error_code = 0; // Success.
            } else {
                $error_code = 1; // Duplicated email address.
            }
        } else {
            $error_code = 2; // Exceed ip address limit.
        }
    } else {
        $error_code = 3; // Invalid Email address.
    }

} else {
    $error_code = 4; // MMERGE4 is not present.
}

// Validate email address.

wp_send_json( [
    'error_code' => $error_code,
    'email' => $email,
    'success_content' => htmlspecialchars_decode( get_post_meta($page_id, 'SMTH_METANAME' , true ))
] );

exit();

function zva_get_wp_load_path()
{
    $base = dirname(__FILE__);
    $path = false;

    if (@file_exists(dirname(dirname($base))."/wp-load.php"))
    {
        $path = dirname(dirname($base))."/wp-load.php";
    }
    else
    if (@file_exists(dirname(dirname(dirname($base)))."/wp-load.php"))
    {
        $path = dirname(dirname(dirname($base)))."/wp-load.php";
    }
    else
    $path = false;

    if ($path != false)
    {
        $path = str_replace("\\", "/", $path);
    }
    return $path;
}

function zva_ct_client_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    return $ip;
}

function zva_ct_subscribe_mailchimp($email_submitted, $media_file_url) {
    $mailchimp = new DrewM\MailChimp\MailChimp(get_option('zva_ct_key'));

    try{
        $res = $mailchimp->post("lists/" . get_option('zva_ct_list') . "/members", array(
            'email_address' => $email_submitted,
            'status'       => 'subscribed',
            'ip_signup' => zva_ct_client_ip(),
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

?>