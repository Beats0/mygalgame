<?php

/* =========================================================================*/
/* =========================================================================*/
/*             SOME USEFUL FUNCTIONS                                        */
/* =========================================================================*/
/* =========================================================================*/

/**
 * This function returns Nelio EFI's post meta key. The key can be changed
 * using the filter `nelioefi_post_meta_key'
 */
function _nelioefi_url() {
	return apply_filters( 'nelioefi_post_meta_key', '_nelioefi_url' );
}


/**
 * This function returns whether the post whose id is $id uses an external
 * featured image or not
 */
function uses_nelioefi( $id ) {
	$image_url = nelioefi_get_thumbnail_src( $id );
	if ( $image_url === false )
		return false;
	else
		return true;
}


/**
 * This function returns the URL of the external featured image (if any), or
 * false otherwise.
 */
function nelioefi_get_thumbnail_src( $id, $called_on_save = false ) {

	// Remove filter temporarily, because uses_nelioefi checks if a regular
	// feat. image is used.
	nelioefi_unhook_thumbnail_id();
	$regular_feat_image = get_post_meta( $id, '_thumbnail_id', true );
	nelioefi_hook_thumbnail_id();

	if ( isset( $regular_feat_image ) && $regular_feat_image > 0 ) {
		return false;
	}//end if

	$image_url = get_post_meta( $id, _nelioefi_url(), true );

	if ( ! $image_url || strlen( $image_url ) === 0 ) {

		$is_frontend = ! is_admin() || $called_on_save;

		if ( apply_filters( 'nelioefi_use_first_image', true ) && $is_frontend ) {

			$first_feat_image = get_post_meta( $id, '_nelioefi_first_image', true );

			if ( empty( $first_feat_image ) ) {

				$image_url = '""';

				$matches = array();
				$post = get_post( $id );
				if ( ! is_wp_error( $post ) && $post ) {

					preg_match(
						'/<img [^>]*src=("[^"]*"|\'[^\']*\')/i',
						$post->post_content,
						$matches
					);

					if ( count( $matches ) > 1 ) {
						$image_url = $matches[1];
					}//end if

				}//end if

				$image_url = substr( $image_url, 1, strlen( $image_url ) - 2 );
				$first_feat_image = array( $image_url );
				delete_post_meta( $id, '_nelioefi_first_image' );
				update_post_meta( $id, '_nelioefi_first_image', $first_feat_image );
			}//end if

			if ( count( $first_feat_image ) > 0 && strlen( $first_feat_image[0] ) > 0 ) {
				return $first_feat_image[0];
			}//end if

		}//end if

		return false;
	}//end if

	return $image_url;
}

add_filter( 'save_post', 'nelioefi_fix_first_image' );
function nelioefi_fix_first_image( $post_id ) {
	if ( wp_is_post_revision( $post_id) || wp_is_post_autosave( $post_id ) ) {
		return;
	}//end if
	delete_post_meta( $post_id, '_nelioefi_first_image' );
	nelioefi_get_thumbnail_src( $post_id, true );
}//end nelioefi_fix_first_image()


/**
 * This function prints an image tag with the external featured image (if any).
 * This tag, in fact, has a 1x1 px transparent gif image as its src, and
 * includes the external featured image via inline CSS styling.
 */
function nelioefi_the_html_thumbnail( $id, $size = false, $attr = array() ) {
	if ( uses_nelioefi( $id ) )
		echo nelioefi_get_html_thumbnail( $id );
}


/**
 * This function returns the image tag with the external featured image (if
 * any). This tag, in fact, has a 1x1 px transparent gif image as its src,
 * and includes the external featured image via inline CSS styling.
 */
function nelioefi_get_html_thumbnail( $id, $size = false, $attr = array() ) {
	if ( uses_nelioefi( $id ) === false )
		return false;

	$image_url = nelioefi_get_thumbnail_src( $id );

	$width = false;
	$height = false;
	$additional_classes = '';

	global $_wp_additional_image_sizes;
	if ( is_array( $size ) ) {
		$width = $size[0];
		$height = $size[1];
	}
	else if ( isset( $_wp_additional_image_sizes[ $size ] ) ) {
		$width = $_wp_additional_image_sizes[ $size ]['width'];
		$height = $_wp_additional_image_sizes[ $size ]['height'];
		$additional_classes = 'attachment-' . $size . ' ';
	}

	if ( $width && $width > 0 ) $width = "width:${width}px;";
	else $width = '';

	if ( $height && $height > 0 ) $height = "height:${height}px;";
	else $height = '';

	if ( isset( $attr['class'] ) )
		$additional_classes .= $attr['class'];

	$alt = get_post_meta( $id, '_nelioefi_alt', true );
	if ( isset( $attr['alt'] ) )
		$alt = $attr['alt'];
	if ( !$alt )
		$alt = '';

	if ( is_feed() ) {
		$style = '';
		if ( isset( $attr['style'] ) )
			$style = 'style="' . $attr['style'] . '" ';
		$html = sprintf(
			'<img src="%s" %s' .
			'class="%s wp-post-image nelioefi" '.
			'alt="%s" />',
			$image_url, $style, $additional_classes, $alt );
	}
	else {
		$html = sprintf(
			'<img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" ' .
			'style="background:url(\'%s\') no-repeat center center;' .
			'-webkit-background-size:cover;' .
			'-moz-background-size:cover;' .
			'-o-background-size:cover;' .
			'background-size:cover;' .
			'%s%s" class="%s wp-post-image nelioefi" '.
			'alt="%s" />',
			$image_url, $width, $height, $additional_classes, $alt );
	}

	return $html;
}


/* =========================================================================*/
/* =========================================================================*/
/*             ALL HOOKS START HERE                                         */
/* =========================================================================*/
/* =========================================================================*/

// Overriding post thumbnail when necessary
add_filter( 'genesis_pre_get_image', 'nelioefi_genesis_thumbnail', 10, 3 );
function nelioefi_genesis_thumbnail( $unknown_param, $args, $post ) {
	$image_url = get_post_meta( $post->ID, _nelioefi_url(), true );

	if ( !$image_url || strlen( $image_url ) == 0 ) {
		return false;
	}

	if ( $args['format'] == 'html' ) {
		$html = nelioefi_replace_thumbnail( '', $post->ID, 0, $args['size'], $args['attr'] );
		$html = str_replace( 'style="', 'style="min-width:150px;min-height:150px;', $html );
		return $html;
	}
	else {
		return $image_url;
	}
}


// Overriding post thumbnail when necessary
add_filter( 'post_thumbnail_html', 'nelioefi_replace_thumbnail', 10, 5 );
function nelioefi_replace_thumbnail( $html, $post_id, $post_image_id, $size, $attr ) {
	if ( uses_nelioefi( $post_id ) )
		$html = nelioefi_get_html_thumbnail( $post_id, $size, $attr );
	return $html;
}


add_action( 'init', 'nelioefi_add_hooks_for_faking_featured_image_if_necessary' );
function nelioefi_add_hooks_for_faking_featured_image_if_necessary(){

	nelioefi_hook_thumbnail_id();

}//end nelioefi_add_hooks_for_faking_featured_image_if_necessary();

function nelioefi_fake_featured_image_if_necessary( $null, $object_id, $meta_key ) {

	$result = null;
	if ( '_thumbnail_id' === $meta_key ) {

		if ( uses_nelioefi( $object_id ) ) {
			$result = -1;
		}//end if

	}//end if

	return $result;

}//end nelioefi_fake_featured_image_if_necessary()

function nelioefi_hook_thumbnail_id() {
	foreach ( get_post_types() as $post_type ) {
		add_filter( "get_${post_type}_metadata", 'nelioefi_fake_featured_image_if_necessary', 10, 3 );
	}//end foreach
}//end nelioefi_hook_thumbnail_id()

function nelioefi_unhook_thumbnail_id() {
	foreach ( get_post_types() as $post_type ) {
		remove_filter( "get_${post_type}_metadata", 'nelioefi_fake_featured_image_if_necessary', 10, 3 );
	}//end foreach
}//end nelioefi_unhook_thumbnail_id()
