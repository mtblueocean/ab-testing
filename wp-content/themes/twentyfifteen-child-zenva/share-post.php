<?php
/**
 * The template for displaying post share area
 *
 * @package WordPress
 * @subpackage Twenty_Fifteen
 * @since Twenty Fifteen 1.0
 */
?>

<div class="author-info">
    <h2 class="author-heading"><?php _e( 'Share this article', 'twentyfifteen' ); ?></h2>

    <div class="share-area">

            <a class="share-link" target="_blank" 
href="https://twitter.com/intent/tweet?url=<?php echo urlencode(get_the_permalink()); ?>&text=<?php echo urlencode(html_entity_decode(get_the_title(), ENT_COMPAT, 'UTF-8')); ?>"></a>
            <a class="share-link" target="_blank" 
href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(get_the_permalink()); ?>"></a>

    </div><!-- .author-description -->
</div><!-- .author-info -->
