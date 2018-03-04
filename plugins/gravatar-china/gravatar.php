<?php
/*
Plugin Name: Gravatar China
Plugin URI: http://loo2k.com/blog/gravatar-cache-reset/
Description: 中国大陆 WordPress 用户 Gravatar 必备插件，保证 Gravatar 头像能在墙内正常显示；
Version: 1.0
Author: LOO2K
Author URI: http://loo2k.com/
License: GPL2
*/

if ( ! defined('ABSPATH') ) {
	die('Please do not load this file directly.');
}

/**
 * Language support
 */
add_action('init', 'init_lang_support');
function init_lang_support(){
  load_plugin_textdomain('wp_avatar_cn', PLUGINDIR . '/' . dirname(plugin_basename (__FILE__)) . '/lang');
}

/**
 * Add menu
 */
function avatar_cn () {
	add_options_page('Gravatar China Options', 'Gravatar China', 'manage_options', 'wp-avatar-cn', 'avatar_cn_options');
	add_action( 'admin_init', 'register_avatar_cn_options' );
}

add_action('admin_menu', 'avatar_cn');

/**
 * Register options
 */
function register_avatar_cn_options () {
	register_setting( 'avatar_cn_opt', 'avatar_cn_settings' );
}

/**
 * Option page
 */
function avatar_cn_options() {
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	?>
	<div class="wrap">
	<h2>Gravatar China for WordPress</h2>
	<form method="post" action="options.php">
	<?php 
		settings_fields( 'avatar_cn_opt' );
		$options = get_option( 'avatar_cn_settings' );
	?>
	<p><?php echo __("Because of some unknow reason, you can't access gravatar.com easily from China mainland, so this plugin's purpose is  let China WordPress users to access gravatar.com easily.", 'wp_avatar_cn');?></p>
	<table class="form-table">
        <tr valign="top">
        <th scope="row"><label for="www_path"><?php echo __("Enable the Gravatar patch:", 'wp_avatar_cn');?></label></th>
		<td>
			<label><input type="checkbox" id="www_path" name="avatar_cn_settings[www_path]" value="1" <?php if ( $options['www_path'] ) echo 'checked="checked"';?>/>
			<?php echo __("Eable", 'wp_avatar_cn');?></label>
			<p style="color:#666;"><?php echo __("We generally recommend you to only enable \"Gravatar patch\", so make your WordPress normal access to the Gravatar avatar.", 'wp_avatar_cn');?></p>
		</td>
        </tr>
         
        <tr valign="top">
        <th scope="row"><label for="avatar_cache"><?php echo __("Enable Gravatar local cache:", 'wp_avatar_cn');?></label>
			<p style="color:#666;"><?php echo __("Before you enable it, please make sure the directory \"<small class=\"description\">wp-content/plugins/gravatar-cn/cache</small>\" is writable.", 'wp_avatar_cn');?></p>
		</th>
        <td>
			<label><input type="checkbox" id="avatar_cache" name="avatar_cn_settings[avatar_cache]" value="1" <?php if ( $options['avatar_cache'] ) echo 'checked="checked"';?>/>
			<?php echo __("Eable", 'wp_avatar_cn');?></label>
			<p style="color:#666;"><?php echo __("Enable \"Gravatar local cache\" would have a little bit of performance loss, but only effect in cache generate.", 'wp_avatar_cn');?></p>
			<p style="color:#666;"><?php echo __("If you can't access Gravatar avatar on someday, we recommend you to enable \"Gravatar local cache\". For can't access it, a little bit of performance loss is negligible.", 'wp_avatar_cn');?></p>
		</td>
        </tr>
		
        <tr id="expired-setting" valign="top">
        <th scope="row"><label for="expired_time"><?php echo __("Set cache expiration time:", 'wp_avatar_cn');?></label>
			<p style="color:#666;"><?php echo __("Units of days, only numbers support;", 'wp_avatar_cn');?></p>
		</th>
        <td>
			<input id="expired_time" type="text" name="avatar_cn_settings[expired_time]" value="<?php if ($options['expired_time']) echo $options['expired_time']; else echo '15'; ?>" />
			<p style="color:#666;"><?php echo __("File will update in x days, 15 is default.", 'wp_avatar_cn');?></p>
		</td>
        </tr>
    </table>
		<p class="submit"><input type="submit" class="button-primary" value="<?php echo __("Save Changes", 'wp_avatar_cn') ?>" /></p>
	</form>
	<p><?php echo __("If you like this plugin, you can <a href='http://loo2k.com/donate/'>donate to author</a> for support.", 'wp_avatar_cn');?></p>
	<?php
}

/**
 * Function of Gravatar China
 */

function access_avatar ($avatar) {
	$string = $avatar;
	$pattern = '/[0-9s]{1}.gravatar.com/i';
	$avatar = preg_replace($pattern, 'www.gravatar.com', $string);
	
	return $avatar;
}

function cache_avatar ($avatar) {
	
	$options = get_option( 'avatar_cn_settings' );
	if ( preg_match('/[0-9]{1,}/i', $options['expired_time']) ) {
		$expired_time = $options['expired_time']*24*60*60;
	} else {
		$expired_time = 1296000;
	}

	# get src
	$src_pattern = '/src=["\']([^"\']*)["\']/i';
	preg_match($src_pattern, $avatar, $src_match);
	$src = $src_match[1];
	
	# get hash
	$hash_pattern = '/[0-9a-fA-F]{32}/i';
	preg_match_all($hash_pattern, urldecode($src), $hash);
	$e_hash = $hash[0][0];
	$d_hash = $hash[0][1];
	
	# get size
	$size_pattern = '/s=([0-9]{1,})&/i';
	preg_match($size_pattern, urldecode($src), $size_match);
	$size = $size_match[1];
	
	# get default
	$default_pattern = '/d=(.*)&/i';
	preg_match($default_pattern, urldecode($src), $default_match);
	$default = $default_match[1];
	
	# get rank
	$rank_pattern = '/r=(.*)/i';
	preg_match($rank_pattern, urldecode($src), $rank_match);
	$rank = $rank_match[1];
	
	# pre cache
	$host = get_bloginfo('url').'/';
	$stor_dir = 'wp-content/plugins/gravatar-cn/cache/';
	$src_dir = $host . $stor_dir;
	$cache_dir = ABSPATH . $stor_dir;
	
	$src_file = $src_dir . $e_hash . $size . '.png';
	$cache_file = $cache_dir . $e_hash . $size . '.png';
	
	if ( !file_exists( $cache_file ) || ( time() - fileatime( $cache_file ) ) > $expired_time ) {
		$header_url = str_replace('&amp;', '&', $src);
		$header = get_headers($header_url, true);
		// print_r($header);
		if ( $header[0] == "HTTP/1.1 302 Found" ) {
			copy($header['Location'], $cache_file);
		} else {
			copy($src, $cache_file);
		}
	}

	$avatar = str_replace($src, $src_file, $avatar);
	
	return $avatar;
	
}

$avatar_cn_settings = get_option( 'avatar_cn_settings' );

if ( $avatar_cn_settings['avatar_cache'] ) {
	add_filter('get_avatar', 'access_avatar');
	add_filter('get_avatar', 'cache_avatar');
} elseif ( $avatar_cn_settings['www_path'] ) {
	add_filter('get_avatar', 'access_avatar');
}