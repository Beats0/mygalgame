<?php

add_action( 'admin_init', 'nelioefi_maybe_add_notices' );
function nelioefi_maybe_add_notices() {
	if ( apply_filters( 'nelioefi_recommend_nelio_content', true ) ) {
		add_action( 'admin_notices', 'nelio_efi_recommend_campaign_notice' );
	}//end if
}//end nelioefi_maybe_add_notices()

function nelio_efi_recommend_campaign_notice() {

	if ( is_plugin_active( 'nelio-content/nelio-content.php' ) ) {
		$message = sprintf(
			__( '<strong>Nelio Content</strong> and <strong>Nelio External Featured Images</strong> are both active. Please, <a href="%s">deactivate Nelio External Featured Images</a> now.', 'nelioefi' ),
			esc_url( admin_url( 'plugins.php' ) )
		);
	} else if ( file_exists( WP_PLUGIN_DIR . '/nelio-content/nelio-content.php' ) ) {
		$message = sprintf(
			__( '<strong>Nelio Content</strong> is already installed in your site. Please, <a href="%s">deactivate <strong>Nelio External Featured Images</strong> and activate <strong>Nelio Content</strong> instead</a>.', 'nelioefi' ),
			esc_url( admin_url( 'plugins.php' ) )
		);
	} else {
		$message = sprintf(
			__( '<strong>Nelio External Featured Images is now discontinued.</strong> Please <a href="%s">install <strong>Nelio Content</strong></a> to continue having support and updates.', 'nelioefi' ),
			esc_url( 'https://neliosoftware.com/content' ),
			esc_url( admin_url( 'plugin-install.php?s=nelio+content&tab=search&type=term' ) )
		);
	}//end if

	?>
	<div class="updated">
		<p style="font-size:15px;"><?php echo $message; ?></p>
	</div>
	<?php
}

