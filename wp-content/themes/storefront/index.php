<?php
/**
 * The main template file.
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 * Learn more: https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package storefront
 */

get_header(); ?>

    <!-- Debug to send zenva custom email -->
    <?php
    global $zvaWpMail;

    $to = 'ryanocean123@gmail.com';
    $subject = 'Signup Email Template for Zenva.';
    $heading_title = 'Signup Email';
    $body = 'Plain Text Message Body';

    // $mail = $zvaWpMail->zva_wp_send_mail($to, $subject, $heading_title, $body, false);
    ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

		<?php if ( have_posts() ) :

			get_template_part( 'loop' );

		else :

			get_template_part( 'content', 'none' );

		endif; ?>

		</main><!-- #main -->
	</div><!-- #primary -->

<?php
do_action( 'storefront_sidebar' );
get_footer();
