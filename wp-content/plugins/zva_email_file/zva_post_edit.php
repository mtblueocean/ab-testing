<?php
/**
 * Adding tutorial assets link to edit post
 */

//show metabox in post editing page
add_action('add_meta_boxes', 'zva_ef_add_metabox');

//save metabox data
//add_action('save_post', 'zva_ef_save_metabox' ); 

function zva_ef_add_metabox() {
    add_meta_box('zva_ef', 'Tutorial Assets','zva_ef_post_widget');
    
}

function zva_ef_post_widget() {
    ?>
    <div><strong>Adding the tutorial assets file</strong></div>
    <div>
        <ol>
            <li>Upload the tutorial ZIP file by going to <em>Media</em> - <em>Add New</em> on the left menu</li>
            <li>Once the upload is complete, click on the uploaded file and enter short descriptive title. <em>Example: Infinite runner tutorial assets</em></li>
            <li>On that same page, grab the file ID from the URL. <em>Example: http://example.com/wp-admin/upload.php?item=<strong>12345</strong></em></li>
            <li>To generate a link to the file in the post content, just type:
                <div style="color:blue;">[zva-file-link id=12345 text="Link text"][/zva-file-link]</div>
                <div><small>Replace 12345 by the file ID and "Link text" by the text you want to show)</small></div>
            </li>
        </ol>
    </div>
    <?php
}
    
   