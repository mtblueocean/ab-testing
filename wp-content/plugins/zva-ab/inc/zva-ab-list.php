<?php

define('ROOTDIR', plugin_dir_path(__FILE__));
require_once(ROOTDIR . 'inc/zva-ab-query.php');

function zva_ab_list() {
    global $wpdb;
    $table_name = $wpdb->prefix . "zva_ab_tests";

    $zva_ab_query = new ZvaAbQuery();

    // In case of Creat Action
    if (isset($_POST['insert'])) {
        $new_zva_ab_test = [
            'name'       => $_POST['name'],
            'desc_a'     => $_POST['desc_a'],
            'desc_b'     => $_POST['desc_b'],
            'views_a'    => 0,
            'views_b'    => 0,
            'revenue_a'  => 0,
            'revenue_b'  => 0,
            'is_active'  => 0,
            'created_at' => date('Y-m-d h:i:s')
        ];

        $zva_ab_query->create_zva_ab_test($new_zva_ab_test);
    }

    // In case of Update Action
    if (isset($_POST['update'])) {
        $zva_ab_query->update_zva_ab_test_active_status($_POST['update_id'], $_POST['is_active']);
    }

    // In case of Delete Action
    if (isset($_POST['delete'])) {
        $zva_ab_query->delete_zva_ab_test_by_id($_POST['delete_id']);
    }

    $rows = $zva_ab_query->get_zva_ab_test_list();

    ?>
    <link type="text/css" href="<?php echo WP_PLUGIN_URL; ?>/zva-ab/css/style-admin.css" rel="stylesheet" />
    
    <div class="wrap">
        <div class="add-new-entry-section">
            <h2>Add New Entry</h2>
            <form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
                <input type="hidden" name="create_action" value="1">
                <table class='wp-list-table widefat fixed'>
                    <tr>
                        <th class="ss-list-width">Name</th>
                        <th class="ss-list-width">Description A</th>
                        <th class="ss-list-width">Description B</th>
                    </tr>
                    <tr>
                        <td><input type="text" name="name" class="ss-field-width" /></td>
                        <td><input type="text" name="desc_a" class="ss-field-width" /></td>
                        <td><input type="text" name="desc_b" class="ss-field-width" /></td>
                    </tr>
                </table>
                <br>
                <input type='submit' name="insert" value='Save' class='button'>
            </form>
        </div>

        <?php if (!empty($rows)) { ?>
        <h2>AB Testing Entries</h2>
        <table class='wp-list-table widefat striped posts border-collapse' width="100%">
            <tr>
                <th class="ss-list-width">No</th>
                <th class="ss-list-width">Name</th>
                <th class="ss-list-width">Description A</th>
                <th class="ss-list-width">Description B</th>
                <th class="ss-list-width">Revenue/Views A</th>
                <th class="ss-list-width">Revenue/Views B</th>
                <th class="ss-list-width">Active</th>
                <th class="ss-list-width text-center" colspan="2">Actions</th>
            </tr>
            <?php foreach ($rows as $key => $row) { ?>
                <tr>
                    <td class="ss-list-width"><?php echo ($key + 1); ?></td>
                    <td class="ss-list-width"><?php echo $row->name; ?></td>
                    <td class="ss-list-width"><?php echo $row->desc_a; ?></td>
                    <td class="ss-list-width"><?php echo $row->desc_b; ?></td>
                    <td class="ss-list-width"><?php echo $row->revenue_a; ?> / <?php echo $row->views_a; ?></td>
                    <td class="ss-list-width"><?php echo $row->revenue_b; ?> / <?php echo $row->views_b; ?></td>
                    <td class="ss-list-width"><?php echo $row->is_active; ?></td>
                    <td class="ss-list-width text-center">
                        <form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
                            <input type="hidden" name="update_id" value="<?php echo $row->id; ?>" />
                            <input type="hidden" name="is_active" value="<?php echo $row->is_active; ?>" />
                            <input type="submit" name="update" class="blue-btn" value="<?php echo ($row->is_active ? "Deactivate" : "Activate"); ?>" onclick="return confirm('Are you sure, you want to <?php echo ($row->is_active ? "deactivate" : "activate"); ?> it?');" />
                        </form>
                    </td>
                    <td class="manage-column ss-list-width text-center">
                        <form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
                            <input type="hidden" name="delete_id" value="<?php echo $row->id; ?>" />
                            <input type="submit" name="delete" class="blue-btn" value="Delete" onclick="return confirm('Are you sure, you want to delete it?');" />
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </table>
        <?php } ?>
    </div>
    <?php
}