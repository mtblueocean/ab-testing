<?php
    
    require_once( plugin_dir_path(__FILE__) . 'zva_ce_emogrifier.php' );

    Class ZvaWpMail {
        protected $template_html;

        public function __construct() {
            $this->template_html  = 'emails/email-body.php';
            add_action( 'woocommerce_email_footer_legacy', array( $this, 'email_footer' ) );
        }

        public function email_footer( $question_box ) {
            wc_get_template( 'emails/email-footer.php', array( 'question_box' => $question_box ) );
        }

        public function get_content_html($heading, $email, $content, $is_question_box) {
            return $this->wc_get_template_html( $this->template_html, array(
                'email_heading' => $heading,
                'email'         => $email,
                'content'       => $content,
                'question_box'  => $is_question_box,
            ) );
        }

        function wc_get_template_html( $template_name, $args = array() ) {
            ob_start();
            wc_get_template( $template_name, $args );
            return ob_get_clean();
        }

        public function style_inline( $content ) {
            // make sure we only inline CSS for html emails
            ob_start();
            wc_get_template( 'emails/email-styles.php' );
            $css = apply_filters( 'woocommerce_email_styles', ob_get_clean() );

            // apply CSS styles inline for picky email clients
            try {
                $emogrifier = new Emogrifier( $content, $css );
                $content    = $emogrifier->emogrify();
            } catch ( Exception $e ) {
                $logger = wc_get_logger();
                $logger->error( $e->getMessage(), array( 'source' => 'emogrifier' ) );
            }

            return $content;
        }

        public function zva_wp_send_mail( $sendTo, $subject, $heading_title, $message, $is_question_box = true, $headers = '', $attachments = array() ) {

            global $woocommerce;
            $mailer = $woocommerce->mailer();

            // Send Email Template defined by zenva plugin.
            $body = $this->get_content_html($heading_title, $sendTo, $message, $is_question_box);

            $message = apply_filters( 'woocommerce_mail_content', $this->style_inline( $body ) );

            $return = $mailer->send( $sendTo, $subject, $message);

            return $return;
        }
    }

?>
