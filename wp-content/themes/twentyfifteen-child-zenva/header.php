<?php
/**
 * The template for displaying the header
 *
 * Displays all of the head element and everything up until the "site-content" div.
 *
 * @package WordPress
 * @subpackage Twenty_Fifteen
 * @since Twenty Fifteen 1.0
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
        <meta name="google-site-verification" content="<?php echo get_option('zva_google_webmaster'); ?>" />
	<!--[if lt IE 9]>
	<script src="<?php echo esc_url( get_template_directory_uri() ); ?>/js/html5.js"></script>
	<![endif]-->
        <?php if(is_single()): ?>
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:site" content="@zenvatweets">
        <meta name="twitter:creator" content="@zenvatweets">
        <meta name="twitter:title" content="<?php echo wp_strip_all_tags(the_title()) ?>">
        <meta name="twitter:description" content="<?php echo wp_strip_all_tags(substr($post->post_content, 0, 200)) ?>">
        <meta name="twitter:image" content="<?php echo the_post_thumbnail_url('zva-post-thumb') ?>">
        
        <meta property="og:image" content="<?php echo the_post_thumbnail_url('zva-post-thumb') ?>"/>
        <meta property="og:url" content="<?php echo the_permalink() ?>" />
        <meta property="og:type" content="article" />
        <meta property="og:title" content="<?php echo wp_strip_all_tags(the_title()) ?>" />
        <meta property="og:description" content="<?php echo wp_strip_all_tags(substr($post->post_content, 0, 200)) ?>" />
        <meta property="og:image" content="<?php echo the_post_thumbnail_url('zva-post-thumb') ?>" />
	<?php endif; ?>
        <?php wp_head(); ?>
        <!-- Facebook Pixel Code -->
        <?php $zvaPixel = get_option('zva_fb_pixel') ?>
        <?php if($zvaPixel): ?>        
        <script>
            !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
            n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
            document,'script','//connect.facebook.net/en_US/fbevents.js');
            fbq('init', '<?php echo $zvaPixel ?>');
            fbq('track', "PageView");
            setTimeout(function(){
                fbq('track', "ViewContent");                
                fbq('track', "ViewBlogPost");
            }, 60000);            
        </script>
        <noscript><img height="1" width="1" style="display:none"
            src="https://www.facebook.com/tr?id=<?php echo $zvaPixel ?>&ev=PageView&noscript=1"
        /></noscript>        
        <?php endif; ?>
        <!-- End Facebook Pixel Code -->
        <?php if(get_option('zva_adsense_on')): ?>
        <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
        
        <script>
          (adsbygoogle = window.adsbygoogle || []).push({
            google_ad_client: "<?php echo get_option('zva_adsense_client'); ?>",
            enable_page_level_ads: true
          });
        </script>
        <?php endif; ?>
</head>

<body <?php body_class(); ?>>
<div id="page" class="hfeed site">
	<a class="skip-link screen-reader-text" href="#content"><?php _e( 'Skip to content', 'twentyfifteen' ); ?></a>
        
        <div id="navbar" class="top-navbar">
            <nav id="site-navigation" class="navigation top-navigation" role="navigation">
                <a class="zva-logo-link" href="https://zenva.com"><img class="zva-logo" src="<?php echo get_stylesheet_directory_uri() ?>/images/zenvalogo.png" /></a>
                <a class="zva-logo-link" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><img class="blog-logo" src="<?php echo esc_url( wp_get_attachment_url(get_option('zva_logo_id')) ); ?>" alt="<?php bloginfo( 'name' ); ?>" title="<?php bloginfo( 'name' ); ?>" /></a>
                <a class="login-btn" href="https://academy.zenva.com/?zva_show_login=1&zva_src=<?php echo get_site_url() ?>-login-btn">Login / Signup</a>
                <?php wp_nav_menu( array( 'theme_location' => 'top-header-menu', 'menu_class' => 'top-nav-menu' ) ); ?>	
                
            </nav><!-- #site-navigation -->
        </div><!-- #navbar -->
        <div id="top-area"></div>
	<div id="sidebar" class="sidebar">
		<header id="masthead" class="site-header" role="banner">
			<div class="site-branding">
                            
                                <a class="zva-logo-link" href="https://zenva.com"><img class="zva-logo" src="<?php echo get_stylesheet_directory_uri() ?>/images/zenvalogo.png" /></a>        
				<button class="secondary-toggle"><?php _e( 'Menu and widgets', 'twentyfifteen' ); ?></button>
			</div><!-- .site-branding -->
		</header><!-- .site-header -->

		<?php get_sidebar(); ?>
	</div><!-- .sidebar -->

	<div id="content" class="site-content">
