<?php
	Class ZvaAbQuery {
	    protected $table_name;

		public function __construct(){
		    global $wpdb;
  			$this->table_name = $wpdb->prefix . "zva_ab_tests";
 		}

 		public function get_zva_ab_test_list() {
 			global $wpdb;
 			$table_name = $this->table_name;

			return $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC");
		}

		public function create_zva_ab_test($new_ab_test) {
			global $wpdb;

	        $wpdb->insert(
	            $this->table_name, //table
	            $new_ab_test // Data
	        );
		}

		public function update_zva_ab_test($updated_ab_test, $where) {
			global $wpdb;

	        $wpdb->update(
	            $this->table_name, //table
	            $updated_ab_test, //data
	            $where //where
	        );
		}

		public function update_zva_ab_test_active_status($entry_id, $is_active) {
			global $wpdb;

			if (!$is_active) {
				$this->update_zva_ab_test(['is_active' => 0], ['is_active' => 1]);
			}

			$this->update_zva_ab_test(['is_active' => !$is_active], ['id' => $entry_id]);
		}

		public function delete_zva_ab_test_by_id($entry_id) {
			global $wpdb;
			$table_name = $this->table_name;

	        $wpdb->delete(
	            $table_name, //table
	            array('id' => $entry_id) //where
	        );
		}

		public function get_zva_ab_test_by_field($field, $val, $return_field) {
			global $wpdb;
			$table_name = $this->table_name;

			$row = $wpdb->get_row("SELECT * FROM $table_name WHERE $field = $val");
			if ($return_field) {
				return $row->$return_field;
			} else {
				return $row;
			}
		}
	}
?>