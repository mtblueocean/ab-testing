<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the "site-content" div and all content after.
 *
 * @package WordPress
 * @subpackage Twenty_Fifteen
 * @since Twenty Fifteen 1.0
 */
?>

	</div><!-- .site-content -->

	<footer id="colophon" class="site-footer" role="contentinfo">
            <?php if ( is_active_sidebar( 'footer_sidebar' ) ) : ?> 
            <div id="footer-sidebar" class="footer-sidebar widget-area" role="complementary"> 
            <?php dynamic_sidebar( 'footer_sidebar' ); ?>
            </div>
            <?php endif; ?>
                
            <div class="site-info">
                    <?php
                            /**
                             * Fires before the Twenty Fifteen footer text for footer customization.
                             *
                             * @since Twenty Fifteen 1.0
                             */
                            do_action( 'twentyfifteen_credits' );
                    ?>
            </div><!-- .site-info -->
	</footer><!-- .site-footer -->

</div><!-- .site -->

<?php wp_footer(); ?>

<?php if(get_option('zva_modal_url')): ?>
    <?php require('modal/modal-main-event.php'); ?>
<?php endif; ?>
<?php if(get_option('zva_signup_modal_id')): ?>
    <?php require('modal/modal-signup.php'); ?>
<?php endif; ?>
</body>
</html>
