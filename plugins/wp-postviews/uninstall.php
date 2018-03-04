<?php
/*
 * Uninstall plugin
 */

if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit ();

if ( is_multisite() ) {
	$ms_sites = function_exists( 'get_sites' ) ? get_sites() : wp_get_sites();

	if( 0 < count( $ms_sites ) ) {
		foreach ( $ms_sites as $ms_site ) {
			$blog_id = class_exists( 'WP_Site' ) ? $ms_site->blog_id : $ms_site['blog_id'];
			switch_to_blog( $blog_id );
			uninstall();
			restore_current_blog();
		}
	}
} else {
	uninstall();
}

function uninstall() {
	global $wpdb;

	$option_names = array( 'views_options', 'widget_views_most_viewed', 'widget_views' );

	if( count( $option_names ) > 0 ) {
		foreach( $option_names as $option_name ) {
			delete_option( $option_name );
		}
	}

	$wpdb->query( "DELETE FROM $wpdb->postmeta WHERE meta_key = 'views'" );
}
