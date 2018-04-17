<?php

get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">
                    
                <?php 
                    if(get_option('zva_essentials_cat_id')) {
                        //section title
                        $title = get_option('zva_essentials_text');
                        
                        $posts = new WP_Query( array(
                            'posts_per_page' => 4,
                            'cat' => get_option('zva_essentials_cat_id')
                        ) );
                        
                        include('box-group.php');
                    }                    
                ?>
                    
                <?php 
                    if(get_option('zva_latest_text')) {
                        //section title
                        $title = get_option('zva_latest_text');
                        
                        $posts = new WP_Query( array( 'posts_per_page' => 4 ) );
                        
                        include('box-group.php');
                    }                    
                ?>
                
                <?php 
                    if(get_option('zva_extra_cat_id_1')) {
                        //section title
                        $post_data = get_category(get_option('zva_extra_cat_id_1'));
                        $title = $post_data->name;
                        
                        $posts = new WP_Query( array(
                            'posts_per_page' => 4,
                            'cat' => get_option('zva_extra_cat_id_1')
                        ) );
                        
                        include('box-group.php');
                    }                    
                ?>
                
                <?php 
                    if(get_option('zva_extra_cat_id_2')) {
                        //section title
                        $post_data = get_category(get_option('zva_extra_cat_id_2'));
                        $title = $post_data->name;
                        
                        $posts = new WP_Query( array(
                            'posts_per_page' => 4,
                            'cat' => get_option('zva_extra_cat_id_2')
                        ) );
                        
                        include('box-group.php');
                    }                    
                ?>
                    
                <?php 
                    if(get_option('zva_extra_cat_id_3')) {
                        //section title
                        $post_data = get_category(get_option('zva_extra_cat_id_3'));
                        $title = $post_data->name;
                        
                        $posts = new WP_Query( array(
                            'posts_per_page' => 4,
                            'cat' => get_option('zva_extra_cat_id_3')
                        ) );
                        
                        include('box-group.php');
                    }                    
                ?>
                
		</main><!-- .site-main -->
	</div><!-- .content-area -->

<?php get_footer(); ?>
