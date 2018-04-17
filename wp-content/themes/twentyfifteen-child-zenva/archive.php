<?php
/**
 * The template for displaying archive pages
 *
 * Used to display archive-type pages if nothing more specific matches a query.
 * For example, puts together date-based pages if no date.php file exists.
 *
 * If you'd like to further customize these archive views, you may create a
 * new template file for each one. For example, tag.php (Tag archives),
 * category.php (Category archives), author.php (Author archives), etc.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Twenty_Fifteen
 * @since Twenty Fifteen 1.0
 */

get_header(); ?>

	<section id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

		<?php if ( have_posts() ) : ?>

			<header class="page-header">
				<?php
					the_archive_title( '<h1 class="page-title">', '</h1>' );
					the_archive_description( '<div class="taxonomy-description">', '</div>' );
				?>
			</header><!-- .page-header -->
                        <div class="zva-posts-section">
			<?php
                            // Start the Loop.
                            while ( have_posts() ) : the_post();
                        ?>
				<div class="zva-home-block">
                        <a href="<?php the_permalink(); ?>" title="<?php echo esc_attr(the_title_attribute()); ?>">
                        <div class="zva-thumb-small">
                        <?php

                        if(has_post_thumbnail()) {
                            the_post_thumbnail('zva-homepage-thumb');
                        }
                        ?>
                        </div>
                        </a>
                        <a class="zva-home-post-title" href="<?php the_permalink(); ?>" title="<?php echo esc_attr(the_title_attribute()); ?>">
                            <?php echo the_title_attribute(); ?>
                        </a>
                        <?php the_excerpt() ?>
                            <a href="<?php the_permalink() ?>" title="<?php echo esc_attr(the_title_attribute()); ?>" class="zva-home-read-more"><?php echo esc_attr(get_option('zva_read_text') ? get_option('zva_read_text') : 'Read'); ?></a> 
                        </div>
                        <?php

			// End the loop.
			endwhile;
                        
                        ?> 
                        </div>
                        <?php

			// Previous/next page navigation.
			the_posts_pagination( array(
				'prev_text'          => __( 'Previous page', 'twentyfifteen' ),
				'next_text'          => __( 'Next page', 'twentyfifteen' ),
				'before_page_number' => '<span class="meta-nav screen-reader-text">' . __( 'Page', 'twentyfifteen' ) . ' </span>',
			) );

		// If no content, include the "No posts found" template.
		else :
			get_template_part( 'content', 'none' );

		endif;
		?>

		</main><!-- .site-main -->
	</section><!-- .content-area -->

<?php get_footer(); ?>
