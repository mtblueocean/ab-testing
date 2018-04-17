<?php
/*
 * Template Name: Zva Custom Template
 * Description: A Page Template for Landing Page.
 */

$plugin_url = plugin_dir_url( __FILE__ );
$email_label = get_post_meta($post->ID, '_zva_ct_email_label', true);

?>

<title><?php echo get_the_title(); ?></title>
<link rel="stylesheet" type="text/css" href="<?php echo $plugin_url; ?>assets/style.css" />
<script type="text/javascript" src="<?php echo $plugin_url; ?>lib/jquery/jquery.js"></script>
<script type="text/javascript" src="<?php echo $plugin_url; ?>assets/custom.js"></script>

<?php if (has_post_thumbnail( $post->ID ) ): ?>
<?php $image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'single-post-thumbnail' ); ?>
<div class="zct_wrapper" style="background-image: url('<?php echo $image[0]; ?>')">
<?php else: ?>
<div class="zct_wrapper">
<?php endif; ?>

    <div class="zct_container">
        <div class="zct_logo">
            <a href="https://zenva.com"><img src="<?php echo $plugin_url; ?>assets/image/logo.png" width="200"></a>
        </div>
        <h2 class="zct_title"><?php echo get_the_title(); ?></h2>
        <?php
        // TO SHOW THE PAGE CONTENTS
        while ( have_posts() ) : the_post(); ?>
            <div class="zct_content">
                <?php the_content(); ?>
            </div>
        <?php
        endwhile;
        wp_reset_query();
        ?>
        <div class="zct_email_subscribe zva_wysiwyg_editor">
            <p class="alert_box"></p>
            <form class="zct_subscribe_form" onsubmit="return false;">
                <label class="email_label">Email Address</label>
                <input type="email" name="zva_ct_email" class="email" required />
                <input type="hidden" name="zva_ct_page_id" value="<?php echo $post->ID; ?>" />
                <input type="hidden" name="plugin_path" value="<?php echo $plugin_url; ?>" />

                <div class="actions">
                    <input type="submit" class="zva-ct-submit" value="<?php echo $email_label; ?>" />
                </div>
            </form>
        </div>
    </div>
</div>
