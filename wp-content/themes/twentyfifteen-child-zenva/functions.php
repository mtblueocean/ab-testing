<?php

//include admin settings
include('settings.php');

//theme shortcodes
include('shortcodes.php');

//remove read more link from exerp
function custom_excerpt_more($output) {
   return preg_replace('/<a[^>]+>Continue reading.*?<\/a>/i','',$output);
}
add_filter('get_the_excerpt', 'custom_excerpt_more');

// Add footer widget 
function footer_widgets_init() { 
    register_sidebar( array( 'name' => 'Footer widget area', 
        'id' => 'footer_sidebar', 
        'before_widget' => '<div>', 
        'after_widget' => '</div>', 
        'before_title' => '<h2 class="widget-title">', 
        'after_title' => '</h2>', ) ); 
    } 
add_action( 'widgets_init', 'footer_widgets_init' ); 

//remove the word "Category"
add_filter( 'get_the_archive_title', function( $title ) {

  if ( is_category() ) {
    $title = single_cat_title( '<h1 class="page-title">', '</h1>' );
  } 
  return $title;
} );


//exerpt length
function custom_excerpt_length( $length ) {
	return 15;
}
add_filter( 'excerpt_length', 'custom_excerpt_length', 999 );

//google analytics
add_action('wp_footer', 'zva_add_google_analytics');
function zva_add_google_analytics() { 

    if(get_option('zva_google_analytics')) {
        
?>
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', '<?php echo get_option('zva_google_analytics'); ?>', 'auto');
  ga('send', 'pageview');

</script>
<?php 
    }
}

//remove google font call
add_action( 'wp_enqueue_scripts', 'remove_google_font', 99 );

function remove_google_font() {
    wp_dequeue_style('twentyfifteen-fonts');
}

//image sizes
add_theme_support( 'post-thumbnails' );
add_image_size( 'zva-homepage-thumb', 379, 284, true ); // Hard Crop Mode
add_image_size( 'zva-post-thumb', 825, 619 ); // Soft Crop Mode


function my_excerpt($text, $excerpt)
{
    if ($excerpt) return $excerpt;

    $text = strip_shortcodes( $text );

    $text = apply_filters('the_content', $text);
    $text = str_replace(']]>', ']]&gt;', $text);
    $text = strip_tags($text);
    $excerpt_length = apply_filters('excerpt_length', 55);
    $excerpt_more = apply_filters('excerpt_more', ' ' . '[...]');
    $words = preg_split("/[\n\r\t ]+/", $text, $excerpt_length + 1, PREG_SPLIT_NO_EMPTY);
    if ( count($words) > $excerpt_length ) {
            array_pop($words);
            $text = implode(' ', $words);
            $text = $text . $excerpt_more;
    } else {
            $text = implode(' ', $words);
    }
    
    $text = preg_replace('/<a[^>]+>Continue reading.*?<\/a>/i','',$text);
    
    return apply_filters('wp_trim_excerpt', $text, $raw_excerpt);
}

//top menu 
function top_header_menu() {

register_nav_menu('top-header-menu',__( 'Top Header Menu' ));

}

add_action( 'init', 'top_header_menu' );

//Dequeue JavaScript
function zva_dequeue_scripts() {
    global $wp_scripts;
    
    //remove parent theme's
    wp_enqueue_script( 'twentyfifteen-script');
    wp_deregister_script( 'twentyfifteen-script');
    
    //remove dependency so it doesn't break others
    foreach($wp_scripts->queue as $script) {
        if(isset($wp_scripts->registered[$script])) {
            $wp_scripts->registered[$script]->deps = array_diff($wp_scripts->registered[$script]->deps, array('twentyfifteen-script'));
        }
    }
    
    //replace by child theme's
    wp_enqueue_script( 'twentyfifteen-script', get_stylesheet_directory_uri() . '/js/functions-child.js', array( 'jquery' ), '20150330', true );
    wp_localize_script( 'twentyfifteen-script', 'screenReaderText', array(
		'expand'   => '<span class="screen-reader-text">' . __( 'expand child menu', 'twentyfifteen' ) . '</span>',
		'collapse' => '<span class="screen-reader-text">' . __( 'collapse child menu', 'twentyfifteen' ) . '</span>',
	) );
    
}

add_action( 'wp_enqueue_scripts', 'zva_dequeue_scripts', PHP_INT_MAX);
