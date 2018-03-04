<?php

// Creating box
add_action( 'add_meta_boxes', 'nelioefi_add_url_metabox' );
function nelioefi_add_url_metabox() {

	$excluded_post_types = array(
		'attachment', 'revision', 'nav_menu_item', 'wpcf7_contact_form',
	);

	foreach ( get_post_types( '', 'names' ) as $post_type ) {
		if ( in_array( $post_type, $excluded_post_types ) )
			continue;
		add_meta_box(
			'nelioefi_url_metabox',
			'External Featured Image',
			'nelioefi_url_metabox',
			$post_type,
			'side',
			'default'
		);
	}

}

function nelioefi_url_metabox( $post ) {
	$nelioefi_url = get_post_meta( $post->ID, _nelioefi_url(), true );
	$nelioefi_alt = get_post_meta( $post->ID, '_nelioefi_alt', true );
	$has_img = strlen( $nelioefi_url ) > 0;
	if ( $has_img ) {
		$hide_if_img = 'display:none;';
		$show_if_img = '';
	}
	else {
		$hide_if_img = '';
		$show_if_img = 'display:none;';
	}
	?>
	<input type="text" placeholder="ALT attribute" style="width:100%;margin-top:10px;<?php echo $show_if_img; ?>"
		id="nelioefi_alt" name="nelioefi_alt"
		value="<?php echo esc_attr( $nelioefi_alt ); ?>" /><?php
	if ( $has_img ) { ?>
	<div id="nelioefi_preview_block"><?php
	} else { ?>
	<div id="nelioefi_preview_block" style="display:none;"><?php
	} ?>
		<div id="nelioefi_image_wrapper" style="<?php
			echo (
				'width:100%;' .
				'max-width:300px;' .
				'height:200px;' .
				'margin-top:10px;' .
				'background:url(' . $nelioefi_url . ') no-repeat center center; ' .
				'-webkit-background-size:cover;' .
				'-moz-background-size:cover;' .
				'-o-background-size:cover;' .
				'background-size:cover;' );
			?>">
		</div>

	<a id="nelioefi_remove_button" href="#" onClick="javascript:nelioefiRemoveFeaturedImage();" style="<?php echo $show_if_img; ?>">Remove featured image</a>
	<script>
	function nelioefiRemoveFeaturedImage() {
		jQuery("#nelioefi_preview_block").hide();
		jQuery("#nelioefi_image_wrapper").hide();
		jQuery("#nelioefi_remove_button").hide();
		jQuery("#nelioefi_alt").hide();
		jQuery("#nelioefi_alt").val('');
		jQuery("#nelioefi_url").val('');
		jQuery("#nelioefi_url").show();
		jQuery("#nelioefi_preview_button").parent().show();
	}
	function nelioefiPreview() {
		jQuery("#nelioefi_preview_block").show();
		jQuery("#nelioefi_image_wrapper").css('background-image', "url('" + jQuery("#nelioefi_url").val() + "')" );
		jQuery("#nelioefi_image_wrapper").show();
		jQuery("#nelioefi_remove_button").show();
		jQuery("#nelioefi_alt").show();
		jQuery("#nelioefi_url").hide();
		jQuery("#nelioefi_preview_button").parent().hide();
	}
	</script>
	</div>
	<input type="text" placeholder="Image URL" style="width:100%;margin-top:10px;<?php echo $hide_if_img; ?>"
		id="nelioefi_url" name="nelioefi_url"
		value="<?php echo esc_attr( $nelioefi_url ); ?>" />
	<div style="text-align:right;margin-top:10px;<?php echo $hide_if_img; ?>">
		<a class="button" id="nelioefi_preview_button" onClick="javascript:nelioefiPreview();">Preview</a>
	</div>
	<?php
}

add_action( 'save_post', 'nelioefi_save_url' );
function nelioefi_save_url( $post_ID ) {
	if ( isset( $_POST['nelioefi_url'] ) ) {
		$url = strip_tags( $_POST['nelioefi_url'] );
		update_post_meta( $post_ID, _nelioefi_url(), $url );
	}

	if ( isset( $_POST['nelioefi_alt'] ) )
		update_post_meta( $post_ID, '_nelioefi_alt', strip_tags( $_POST['nelioefi_alt'] ) );
}



