<?php
	define('ROOTDIR', plugin_dir_path(__FILE__));
	require_once(ROOTDIR . 'inc/zva-ab-tester.php');

	add_action( 'init', 'zva_ab_test_variation_init' );

	function zva_ab_test_variation_init() {
		$zva_ab_query = new ZvaAbQuery();

		global $zvaAbTester;
		$zvaAbTester = new zvaAbTester();

		if (!isset($_SESSION['zva-ab-test'])) {
			$active_ab_test_id = $zva_ab_query->get_zva_ab_test_by_field('is_active', 1, 'id');

			$_SESSION['zva-ab-test'] = false;
			if ($active_ab_test_id) {
				$_SESSION['zva-ab-test'] = $active_ab_test_id;

				$temp_variation = rand(0, 1) < 0.5;
				if ($temp_variation) {
					$_SESSION['zva-ab-variation'] = "A";
				} else {
					$_SESSION['zva-ab-variation'] = "B";
				}
			}
		}

		// Update Revenue A and B.
		if (isset($_SESSION['zva-ab-test']) && $_SESSION['zva-ab-test']) {
			add_action( 'woocommerce_payment_complete' , 'zva_ab_update_revenue');
		}
	}

	function zva_ab_update_revenue($order_id) {
		global $woocommerce;
		$zva_ab_query = new ZvaAbQuery();

		$curr_zva_ab_revenue_a = $zva_ab_query->get_zva_ab_test_by_field('id', $_SESSION['zva-ab-test'], 'revenue_a');
		$curr_zva_ab_revenue_b = $zva_ab_query->get_zva_ab_test_by_field('id', $_SESSION['zva-ab-test'], 'revenue_b');

		// get order
		$order = wc_get_order($order_id);

		// get order total
		$order_total = $order->get_total();

		if ($_SESSION['zva-ab-variation'] == "A") {
			$updated_revenue = ['revenue_a' => $curr_zva_ab_revenue_a + $order_total];
		} elseif ($_SESSION['zva-ab-variation'] == "B") {
			$updated_revenue = ['revenue_b' => $curr_zva_ab_revenue_b + $order_total];
		}

		$zva_ab_query->update_zva_ab_test($updated_revenue, ['id' => $_SESSION['zva-ab-test']]);
	}
?>