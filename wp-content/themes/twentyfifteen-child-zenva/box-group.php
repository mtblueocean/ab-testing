<header class="page-header">
    <h1 class="page-title"><?php echo esc_attr($title); ?></h1>
</header>
<div class="zva-posts-section">
<?php

if ( $posts->have_posts() ) {
    $i = 0;

    ?> 
    <?php

    while ( $posts->have_posts() ){
        $posts->the_post();

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
    }
}

?>
</div>