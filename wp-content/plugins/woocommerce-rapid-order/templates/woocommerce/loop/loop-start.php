<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$colspan = get_option( 'wcro_show_dont_sub' ) == 'yes' ? 5 : 4;
$colspan = get_option( 'wcro_hide_thumbnail' ) == 'yes' ? $colspan - 1 : $colspan;

list($width, $height) = wcro_get_thumb_size();

?>
<style>
	.woocommerce-pagination{ display: none }
</style>

<label>
<input type="checkbox" id="toggleDescriptionZ" checked="checked">
Show descriptions
</label>
<script>
var checkbox = document.getElementById('toggleDescriptionZ');

checkbox.addEventListener('click', function() {
	if (this.checked) {
		jQuery('#hide-excerpt').remove();
	} else {
		jQuery('body').append('<style id="hide-excerpt">.wcro_excerpt{display:none;}</style>');
	}
}, false);
</script>

<div class="wcro_search">

</div>
<div class="wcro">
<table class="wcro-products">
	<thead>
	<tr>
		<?php if(get_option( 'wcro_hide_thumbnail' ) != 'yes') : ?>
		<th class="wcro_thumb_head" style="width: <?php echo $width ?>px"></th>
		<?php endif; ?>
		<th class="wcro_desc_head"></th>
		<th><?php echo __( 'Qty', WC_Rapid_Order::TEXT_DOMAIN ) ?></th>
		<?php if ( get_option( 'wcro_show_dont_sub' ) == 'yes' ) : ?>
			<th style="white-space: nowrap">
				<?php echo get_option( 'wcro_dont_sub_text', '' ) ?>
				<?php if ( $tip = get_option( 'wcro_dont_sub_help_text' ) ) : ?>
					<img class="tooltip wcro_tooltip" title="<?php echo $tip ?>"
					     src="<?php echo plugins_url() ?>/woocommerce/assets/images/help.png" height="16" width="16">
				<?php endif; ?>
			</th>
		<?php endif; ?>
		<th><?php echo __( 'Total', WC_Rapid_Order::TEXT_DOMAIN ) ?></th>
	</tr>
	</thead>
	<tbody>
	<?php if ( get_option( 'wcro_show_search' ) == 'yes' ) : ?>
		<tr class="wcro_search">
			<td colspan="<?php echo $colspan ?>"><?php wc_get_template( 'product-searchform.php' ) ?></td>
		</tr>
	<?php endif; ?>
	<tr id="wcro-loader">
		<td colspan="<?php echo $colspan ?>" style="text-align: center;">
			<h4><?php echo __( 'Loading Products', WC_Rapid_Order::TEXT_DOMAIN ) ?></h4>
			<img style="display:inline" width="16px" align="center" alt="loading spinner"
			     src="<?php echo get_admin_url( null, '/images/wpspin_light-2x.gif' ) ?>">
		</td>
	</tr>