<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $woocommerce;
$colspan = get_option( 'wcro_show_dont_sub' ) == 'yes' ? 5 : 4;
$colspan = get_option( 'wcro_hide_thumbnail' ) == 'yes' ? $colspan - 1 : $colspan;
?>
</tbody>
<tfoot>
<tr>
	<td colspan="<?php echo $colspan ?>" class="wcro_footer_total">
		<?php echo wcro_cart_total() ?>
		<div class="wcro_review_button">
			<a href="<?php echo $woocommerce->cart->get_cart_url() ?>"
			   class="button wcro_button"><?php echo __( 'Review Cart', WC_Rapid_Order::TEXT_DOMAIN ) ?></a>
		</div>
		<div style="clear: both;"></div>
	</td>
</tr>
</tfoot>
</table>

</div>

<script>
	var WCRO_Items = <?php echo json_encode(WC_Rapid_Order::instance()->loop->get_loop_items()) ?>;
</script>