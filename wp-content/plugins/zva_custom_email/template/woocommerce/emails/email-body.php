<?php
/**
 * Body Content shown in emails.
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

 /**
  * @hooked WC_Emails::email_header() Output the email header
  */
do_action( 'woocommerce_email_header', $email_heading, $email );

?>

<div style="margin-bottom: 40px;"><?php echo $content; ?></div>

<?php

do_action( 'woocommerce_email_footer_legacy', $question_box, $email );

?>
