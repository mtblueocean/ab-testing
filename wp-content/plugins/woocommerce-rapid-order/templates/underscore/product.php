<?php
list($width, $height) = wcro_get_thumb_size();
?>
<script type="text/template" id="productTemplate">
	<?php if(get_option( 'wcro_hide_thumbnail' ) != 'yes') : ?>
	<td data-label="" class="wcro_thumb">
		<?php $image_close_tag = ''; ?>
		<?php switch(get_option( 'wcro_link_image' )) {
			case 'product_page':
				$image_close_tag = '</a>';
				echo '<a href="<%= permalink %>" title="<%= title %>">';
				break;
			case 'image':
				$image_close_tag = '</a>';
				echo '<% if(image.full) { %><a href="<%= image.full %>" class="wcro-swipebox" title="<%= title %>"><% } %>';
				break;
			default:
		}
		?>
			
		<?php if ( get_option( 'wcro_sale_badge' ) == 'yes' ) : ?>
			<% if(on_sale) { %>
			<div class="wcro_badge wcro_badge_sale"><?php echo __( 'Sale', WC_Rapid_Order::TEXT_DOMAIN ) ?></div><% } %>
		<?php endif; ?>
		<%= image.src %>

		<% if(image.full) { %>
		<?php echo $image_close_tag; ?>
		<% } %>
	</td>
	<?php endif; ?>

	<td data-label="" class="wcro_desc" style="min-height: <?php echo $width ?>px">

		<h3 style="line-height: 100%; margin:0">

			<?php if ( get_option( 'wcro_link_title' ) == 'yes' ) : ?>
				<a href="<%= permalink %>" title="<%= title %>"><%= title %></a>
			<?php else : ?>
				<%= title %>
			<?php endif; ?>

			<?php if ( get_option( 'wcro_featured_badge' ) == 'yes' ) : ?>
				<% if(featured) { %>
				<span class="wcro_featured_text"><?php echo __( 'Featured', WC_Rapid_Order::TEXT_DOMAIN ) ?></span>
				<% } %>
			<?php endif; ?>
		</h3>


		<div class="wcro_price"><div class="wcro_price_contents"><%= price_html %></div></div>
		<div class="wcro_excerpt"><%= excerpt %></div>
	</td>

	<td data-label="Quantity" class="wcro_form wcro_centered">
		<form class="cart" method="post" enctype='multipart/form-data'>
			<%= quantity_html %>
		</form>
	</td>

	<?php if ( get_option( 'wcro_show_dont_sub' ) == 'yes' ) : ?>
		<td data-label="<?php echo get_option( 'wcro_dont_sub_text', __( "Don't Sub", WC_Rapid_Order::TEXT_DOMAIN ) ) ?>"
		    class="wcro_centered wcro_no_subs">
			<% if(in_stock){ %>
			<input name="wcro-sub-box" type="checkbox" value="1">
			<% } %>
		</td>
	<?php endif; ?>

	<td data-label="Total" class="wcro_total wcro_centered">
		<span class="wcro_total_price"><%= totalPrice %></span>
		<div class="wcro-cart-discount"></div>
		<div class="wcro-cart-action"><?php echo __( 'Cart Updated', WC_Rapid_Order::TEXT_DOMAIN ) ?></div>
		<div class="wcro-item-loader" style="display:none"><img style="display:inline" width="16px" align="center"
		                                                        alt="loading spinner"
		                                                        src="<?php echo get_admin_url( null, '/images/wpspin_light-2x.gif' ) ?>">
		</div>
	</td>
</script>