<?php

// Add meta box into admin custom template.
add_action( 'add_meta_boxes',  function() { 
    add_meta_box('html_myid_61_section', 'Subscribe Success Content', 'success_content_wysiwyg');

    add_meta_box(
        'zva_ct_custom_options',
        'Custom Template Options',
        'zva_custom_box_html',
        'page'
    );
});

function success_content_wysiwyg( $post ) {
    wp_editor( htmlspecialchars_decode( get_post_meta($post->ID, 'SMTH_METANAME' , true ) ), 'success_content_wysiwyg_box', $settings = array('textarea_name'=>'MyInputNAME') );
}

function zva_custom_box_html($post) {
    $email_label = get_post_meta($post->ID, '_zva_ct_email_label', true);
    $mailchimp_mmerge4_field = get_post_meta($post->ID, '_zva_ct_mmerge4_field', true);
    $zva_selected_product = get_post_meta($post->ID, '_zva_ct_product_id', true);

    $args = array(
        'posts_per_page' => -1,
        'post_type'      => 'product',
        'meta_query' => array(
            array(
                'key' => '_stock_status',
                'value' => 'outofstock',
                'compare' => '!=',
            )
        )
    );

    $zva_products = get_posts($args);

    ?>
    <style type="text/css">
        .zva-ct-option-item {
            padding: 10px 0;
            display: flex;
            align-items: center;
        }

        .zva-ct-option-item label {
            width: 135px;
        }

        .zva-ct-option-item input,
        .zva-ct-option-item select {
            width: 75%;
        }
    </style>

    <div class="zva-ct-option-item">
        <label for="zva_ct_email_field">Email Button Label: &nbsp;</label>
        <input type="text" name="zva_ct_email_field" id="zva_ct_email_field" value="<?php echo $email_label; ?>">
    </div>

    <div class="zva-ct-option-item">
        <label for="zva_ct_mmerge4_field">Mailchimp MMERGE4: &nbsp;</label>
        <input type="text" name="zva_ct_mmerge4_field" id="zva_ct_mmerge4_field" value="<?php echo $mailchimp_mmerge4_field; ?>">
    </div>

    <div class="zva-ct-option-item">
        <label for="zva_ct_mmerge4_field">All Products in stock: &nbsp;</label>
        <select name="zva_all_products" id="zva_all_products">
            <?php foreach ($zva_products as $product) { ?>
            <option value="<?php echo $product->ID; ?>" <?php selected($zva_selected_product, $product->ID); ?>><?php echo $product->post_title; ?></option>
            <?php } ?>
        </select>
    </div>
    <?php
}

add_action( 'save_post', function($post_id) {
    if (!empty($_POST['MyInputNAME'])) {
        $wysiwyg_datta=htmlspecialchars($_POST['MyInputNAME']);
        update_post_meta($post_id, 'SMTH_METANAME', $wysiwyg_datta );
    } else {
        update_post_meta($post_id, 'SMTH_METANAME', '' );
    }

    if (!empty($_POST['zva_ct_email_field'])) {
        $email_datta = $_POST['zva_ct_email_field'];
        update_post_meta($post_id, '_zva_ct_email_label', $email_datta );
    } else {
        update_post_meta($post_id, '_zva_ct_email_label', '' );
    }

    if (!empty($_POST['zva_ct_mmerge4_field'])) {
        $mmerge4_datta = $_POST['zva_ct_mmerge4_field'];
        update_post_meta($post_id, '_zva_ct_mmerge4_field', $mmerge4_datta );
    } else {
        update_post_meta($post_id, '_zva_ct_mmerge4_field', '' );
    }

    if (!empty($_POST['zva_all_products'])) {
        $selected_product_id = $_POST['zva_all_products'];
        update_post_meta($post_id, '_zva_ct_product_id', $selected_product_id );
    } else {
        update_post_meta($post_id, '_zva_ct_product_id', 0 );
    }
});

?>