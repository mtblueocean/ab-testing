<?php
	define('ROOTDIR', plugin_dir_path(__FILE__));
	require_once(ROOTDIR . 'inc/zva-ab-query.php');

	Class zvaAbTester {
		public function __construct() {
		    global $wpdb;
  			$this->table_name = $wpdb->prefix . "zva_ab_tests";
    	}
		
		public function zva_ab_check_test($entry_id) {
			return $_SESSION['zva-ab-test'] == $entry_id;
		}

		public function zva_ab_check_variation($user_validation) {
			$zva_ab_query = new ZvaAbQuery();

			$curr_zva_ab_view_a = $zva_ab_query->get_zva_ab_test_by_field('id', $_SESSION['zva-ab-test'], 'views_a');
			$curr_zva_ab_view_b = $zva_ab_query->get_zva_ab_test_by_field('id', $_SESSION['zva-ab-test'], 'views_b');

			if ($user_validation == "A") {
				$updated_view = ['views_a' => $curr_zva_ab_view_a + 1];
			} elseif ($user_validation == "B") {
				$updated_view = ['views_b' => $curr_zva_ab_view_b + 1];
			}

			$zva_ab_query->update_zva_ab_test($updated_view, ['id' => $_SESSION['zva-ab-test']]);

			return $_SESSION['zva-ab-variation'] == $user_validation;
		}
	}
?>