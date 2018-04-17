<?php
/**
 * The default template for displaying content
 *
 * Used for both single and index/archive/search.
 *
 * @package WordPress
 * @subpackage Twenty_Fifteen
 * @since Twenty Fifteen 1.0
 */
?>

<?php if(get_option('zva_adsense_on')): ?>
<div class="zva-top-ad-banner">
    <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
    <!-- GDA top -->
    <ins class="adsbygoogle zva_adsense_top"
         style="display:inline-block;"
         data-ad-client="<?php echo get_option('zva_adsense_client'); ?>"
         data-ad-slot="<?php echo get_option('zva_adense_slot'); ?>"></ins>
    <script>
    (adsbygoogle = window.adsbygoogle || []).push({});
    </script>
</div>
<?php endif; ?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
        <!-- featured image ZVA-MOD -->
	<div class="post-thumbnail" <?php if ( is_single() ) : ?>id="post-thumbnail" <?php endif; ?>>
		<?php the_post_thumbnail('zva-post-thumb'); ?>
	</div><!-- .post-thumbnail -->

	<header class="entry-header">
		<?php
			if ( is_single() ) :
				the_title( '<h1 class="entry-title">', '</h1>' );
			else :
				the_title( sprintf( '<h2 class="entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' );
			endif;
		?>
	</header><!-- .entry-header -->

	<div class="entry-content">
  <?php
    if ( is_archive () ):
        the_excerpt(__( 'Continue reading %s', 'twentyfifteen' ));
      else:
      /* translators: %s: Name of current post */
      the_content( sprintf(
        __( 'Continue reading %s', 'twentyfifteen' ),
        the_title( '<span class="screen-reader-text">', '</span>', false )
        ) );
      endif;
      ?>
    </div>
  <!-- .entry-content -->

	<?php
		// Author bio.
		if ( is_single() && get_the_author_meta( 'description' ) ) :
			get_template_part( 'author-bio' );
		endif;
	?>
  
        <?php
		// Author bio.
		if ( is_single() ) :
			get_template_part( 'share-post' );
		endif;
	?>

	<footer class="entry-footer">
		<?php twentyfifteen_entry_meta(); ?>
		<?php edit_post_link( __( 'Edit', 'twentyfifteen' ), '<span class="edit-link">', '</span>' ); ?>
	</footer><!-- .entry-footer -->

</article><!-- #post-## -->
<?php if ( is_single() ) : ?>
<script>
window.addEventListener("load",function() {
    //document.getElementById("post-thumbnail").style.visibility = "visible";
    
    setTimeout(function(){
        if(document.getElementById("disqus_thread")) {
            document.getElementById("disqus_thread").style.display = "block";
        }
    }, 8000);    
});
</script>
<?php endif; ?>
