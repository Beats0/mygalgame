<?php
/*
Plugin Name: Custom Field Template
Plugin URI: http://wpgogo.com/development/custom-field-template.html
Description: This plugin adds the default custom fields on the Write Post/Page.
Author: Hiroaki Miyashita
Author URI: http://wpgogo.com/
Version: 2.3.8
Text Domain: custom-field-template
Domain Path: /
*/

/*
This program is based on the rc:custom_field_gui plugin written by Joshua Sigar.
I appreciate your efforts, Joshua.
*/

/*  Copyright 2008 -2018 Hiroaki Miyashita

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

class custom_field_template {
	var $is_excerpt, $format_post_id;

	function __construct() {
		add_action( 'plugins_loaded', array(&$this, 'custom_field_template_plugins_loaded') );
		add_action( 'init', array(&$this, 'custom_field_template_init'), 100 );
		add_action( 'admin_menu', array(&$this, 'custom_field_template_admin_menu') );
		add_action( 'admin_print_scripts', array(&$this, 'custom_field_template_admin_scripts') );
		add_action( 'admin_head', array(&$this, 'custom_field_template_admin_head'), 100 );
		add_action( 'dbx_post_sidebar', array(&$this, 'custom_field_template_dbx_post_sidebar') );
		add_action( 'add_meta_boxes', array(&$this, 'custom_field_template_add_meta_boxes') );
		
		//add_action( 'edit_post', array(&$this, 'edit_meta_value'), 100 );
		add_action( 'save_post', array(&$this, 'edit_meta_value'), 100, 2 );
		//add_action( 'publish_post', array(&$this, 'edit_meta_value'), 100 );

		add_action( 'delete_post', array(&$this, 'custom_field_template_delete_post'), 100 );
		
		add_filter( 'media_send_to_editor', array(&$this, 'media_send_to_custom_field'), 15 );
		add_filter( 'plugin_action_links', array(&$this, 'wpaq_filter_plugin_actions'), 100, 2 );
		
		add_filter( 'get_the_excerpt', array(&$this, 'custom_field_template_get_the_excerpt'), 1 );
		add_filter( 'the_content', array(&$this, 'custom_field_template_the_content') );
		add_filter( 'the_content_rss', array(&$this, 'custom_field_template_the_content') );

		add_filter( 'attachment_fields_to_edit', array(&$this, 'custom_field_template_attachment_fields_to_edit'), 10, 2 );
		add_filter( '_wp_post_revision_fields', array(&$this, 'custom_field_template_wp_post_revision_fields'), 1 );
		add_filter( 'edit_form_after_title', array(&$this, 'custom_field_template_edit_form_after_title') );

		if ( isset($_REQUEST['cftsearch_submit']) ) :
			if ( !empty($_REQUEST['limit']) )
				add_action( 'post_limits', array(&$this, 'custom_field_template_post_limits'), 100);
			add_filter( 'posts_join', array(&$this, 'custom_field_template_posts_join'), 100 );
			add_filter( 'posts_where', array(&$this, 'custom_field_template_posts_where'), 100 );
			add_filter( 'posts_orderby',  array(&$this, 'custom_field_template_posts_orderby'), 100 );
		endif;
		
		if ( function_exists('add_shortcode') ) :
			add_shortcode( 'cft', array(&$this, 'output_custom_field_values') );
			add_shortcode( 'cftsearch', array(&$this, 'search_custom_field_values') );
		endif;
		
		add_filter( 'get_post_metadata', array(&$this, 'get_preview_postmeta'), 10, 4 );
	}
	
	function custom_field_template_plugins_loaded() {
		load_plugin_textdomain('custom-field-template', false, plugin_basename( dirname( __FILE__ ) ) );
	}
		
	function custom_field_template_init() {
		global $wp_version;
		$options = $this->get_custom_field_template_data();
		
		if ( is_user_logged_in() && isset($_REQUEST['post']) && isset($_REQUEST['page']) && $_REQUEST['page'] == 'custom-field-template/custom-field-template.php' && $_REQUEST['cft_mode'] == 'selectbox' ) {
			echo $this->custom_field_template_selectbox();
			exit();
		}
		
		if ( is_user_logged_in() && isset($_REQUEST['post']) && isset($_REQUEST['page']) && $_REQUEST['page'] == 'custom-field-template/custom-field-template.php' && $_REQUEST['cft_mode'] == 'ajaxsave' ) {
			if ( $_REQUEST['post'] > 0 )
				$this->edit_meta_value( $_REQUEST['post'], '' );
			exit();
		}
		
		if ( is_user_logged_in() && isset($_REQUEST['page']) && $_REQUEST['page'] == 'custom-field-template/custom-field-template.php' && $_REQUEST['cft_mode'] == 'ajaxload') {
			if ( isset($_REQUEST['id']) ) :
				$id = $_REQUEST['id'];			
			elseif ( isset($options['posts'][$_REQUEST['post']]) ) :
				$id = $options['posts'][$_REQUEST['post']];
			else :
				$filtered_cfts = $this->custom_field_template_filter();
				if ( count($filtered_cfts)>0 ) :
					$id = $filtered_cfts[0]['id'];
				else :
					$id = 0;
				endif;
			endif;
			list($body, $init_id) = $this->load_custom_field( $id );
			echo $body;
			exit();
		}
		
		if( strstr($_SERVER['REQUEST_URI'], 'wp-admin/plugins.php') && ((isset($_GET['activate']) && $_GET['activate'] == 'true') || (isset($_GET['activate-multi']) && $_GET['activate-multi'] == 'true') ) ) {
			$options = $this->get_custom_field_template_data();
			if( !$options ) {
				$this->install_custom_field_template_data();
				$this->install_custom_field_template_css();
			}
		}
		
		if ( function_exists('current_user_can') && current_user_can('edit_plugins') ) :
			if ( isset($_POST['custom_field_template_export_options_submit']) ) :
				$filename = "cft".date('Ymd');
				header("Accept-Ranges: none");
				header("Content-Disposition: attachment; filename=$filename");
				header('Content-Type: application/octet-stream');
				echo maybe_serialize($options);
				exit();
			endif;
		endif;
				
		if ( !empty($options['custom_field_template_widget_shortcode']) )
			add_filter('widget_text', 'do_shortcode');

		if ( substr($wp_version, 0, 3) >= '2.7' ) {
			if ( empty($options['custom_field_template_disable_custom_field_column']) ) :
				add_action( 'manage_posts_custom_column', array(&$this, 'add_manage_posts_custom_column'), 10, 2 );
				add_filter( 'manage_posts_columns', array(&$this, 'add_manage_posts_columns') );
				add_action( 'manage_pages_custom_column', array(&$this, 'add_manage_posts_custom_column'), 10, 2 );
				add_filter( 'manage_pages_columns', array(&$this, 'add_manage_pages_columns') );
			endif;
			if ( empty($options['custom_field_template_disable_quick_edit']) )
				add_action( 'quick_edit_custom_box', array(&$this, 'add_quick_edit_custom_box'), 10, 2 );
		}
		
		if ( substr($wp_version, 0, 3) < '2.5' ) {
			add_action( 'simple_edit_form', array(&$this, 'insert_custom_field'), 1 );
			add_action( 'edit_form_advanced', array(&$this, 'insert_custom_field'), 1 );
			add_action( 'edit_page_form', array(&$this, 'insert_custom_field'), 1 );
		}

		if( strstr($_SERVER['REQUEST_URI'], 'wp-admin/post-new.php') || strstr($_SERVER['REQUEST_URI'], 'wp-admin/post.php') || strstr($_SERVER['REQUEST_URI'], 'wp-admin/page-new.php') || strstr($_SERVER['REQUEST_URI'], 'wp-admin/page.php') ) :
			add_action('admin_head', array(&$this, 'custom_field_template_admin_head_buffer') );   
			add_action('admin_footer', array(&$this, 'custom_field_template_admin_footer_buffer') );  
		endif;
	}
	
	function custom_field_template_add_meta_boxes() {
		$options = $this->get_custom_field_template_data();

		if ( function_exists('remove_meta_box') && !empty($options['custom_field_template_disable_default_custom_fields']) ) :
			remove_meta_box('postcustom', 'post', 'normal');
			remove_meta_box('postcustom', 'page', 'normal');
			remove_meta_box('pagecustomdiv', 'page', 'normal');
		endif;

		if ( !empty($options['custom_field_template_deploy_box']) ) :
			if ( !empty($options['custom_fields']) ) :
				$i = 0;
				foreach ( $options['custom_fields'] as $key => $val ) :
					if ( empty($options['custom_field_template_replace_the_title']) ) $title = __('Custom Field Template', 'custom-field-template');
					else $title = $options['custom_fields'][$key]['title'];
					if ( empty($options['custom_fields'][$key]['custom_post_type']) ) :
						if ( empty($options['custom_fields'][$key]['post_type']) ) :
							add_meta_box('cftdiv'.$i, $title, array(&$this, 'insert_custom_field'), 'post', 'normal', 'core', $key);
							add_meta_box('cftdiv'.$i, $title, array(&$this, 'insert_custom_field'), 'page', 'normal', 'core', $key);
						elseif ( $options['custom_fields'][$key]['post_type']=='post' ) :
							add_meta_box('cftdiv'.$i, $title, array(&$this, 'insert_custom_field'), 'post', 'normal', 'core', $key);
						elseif ( $options['custom_fields'][$key]['post_type']=='page' ) :
							add_meta_box('cftdiv'.$i, $title, array(&$this, 'insert_custom_field'), 'page', 'normal', 'core', $key);
						endif;
					else :
						$tmp_custom_post_type = explode(',', $options['custom_fields'][$key]['custom_post_type']);
						$tmp_custom_post_type = array_filter( $tmp_custom_post_type );
						$tmp_custom_post_type = array_unique(array_filter(array_map('trim', $tmp_custom_post_type)));
						foreach ( $tmp_custom_post_type as $type ) :
							add_meta_box('cftdiv'.$i, $title, array(&$this, 'insert_custom_field'), $type, 'normal', 'core', $key);
						endforeach;
					endif;
					$i++;
				endforeach;
			endif;
		else :
			add_meta_box('cftdiv', __('Custom Field Template', 'custom-field-template'), array(&$this, 'insert_custom_field'), 'post', 'normal', 'core');
			add_meta_box('cftdiv', __('Custom Field Template', 'custom-field-template'), array(&$this, 'insert_custom_field'), 'page', 'normal', 'core');
		endif;
						
		if ( empty($options['custom_field_template_deploy_box']) && is_array($options['custom_fields']) ) :
			$custom_post_type = array();
			foreach($options['custom_fields'] as $key => $val ) :
				if ( isset($options['custom_fields'][$key]['custom_post_type']) ) :
					$tmp_custom_post_type = explode(',', $options['custom_fields'][$key]['custom_post_type']);
					$tmp_custom_post_type = array_filter( $tmp_custom_post_type );
					$tmp_custom_post_type = array_unique(array_filter(array_map('trim', $tmp_custom_post_type)));
					$custom_post_type = array_merge($custom_post_type, $tmp_custom_post_type);
				endif;
			endforeach;
			if ( isset($custom_post_type) && is_array($custom_post_type) ) :
				foreach( $custom_post_type as $val ) :
					if ( function_exists('remove_meta_box') && !empty($options['custom_field_template_disable_default_custom_fields']) ) :
						remove_meta_box('postcustom', $val, 'normal');
					endif;
					add_meta_box('cftdiv', __('Custom Field Template', 'custom-field-template'), array(&$this, 'insert_custom_field'), $val, 'normal', 'core');
					if ( empty($options['custom_field_template_disable_custom_field_column']) ) :
						add_filter( 'manage_'.$val.'_posts_columns', array(&$this, 'add_manage_pages_columns') );
					endif;
				endforeach;
			endif;
		endif;
	}
	
	function custom_field_template_attachment_fields_to_edit($form_fields, $post) {
		$form_fields["custom_field_template"]["label"] = __('Media Picker', 'custom-field-template');
		$form_fields["custom_field_template"]["input"] = "html";
		$form_fields["custom_field_template"]["html"] = '<a href="javascript:void(0);" onclick="var win = window.dialogArguments || opener || parent || top;win.cft_use_this('.$post->ID.');return false;">'.__('Use this', 'custom-field-template').'</a>';
		
		return $form_fields;
	}
	
	function custom_field_template_add_enctype($buffer) {
		$buffer = preg_replace('/<form name="post"/', '<form enctype="multipart/form-data" name="post"', $buffer);
		return $buffer;
	}
	
	function custom_field_template_admin_head_buffer() {   
		ob_start(array(&$this, 'custom_field_template_add_enctype'));   
	} 
	
	function custom_field_template_admin_footer_buffer() {   
		ob_end_flush();   
	}

	function has_meta( $postid ) {
		global $wpdb;

		return $wpdb->get_results( $wpdb->prepare("SELECT meta_key, meta_value, meta_id, post_id FROM $wpdb->postmeta WHERE post_id = %d ORDER BY meta_key,meta_id", $postid), ARRAY_A );
	}
	
	function get_post_meta($post_id, $key = '', $single = false) {
		if ( !$post_id ) return '';
			
		if ( $preview_id = $this->get_preview_id( $post_id ) ) $post_id = $preview_id;

		$post_id = (int) $post_id;

		$meta_cache = wp_cache_get($post_id, 'cft_post_meta');

		if ( !$meta_cache ) {
			if ( $meta_list = $this->has_meta( $post_id ) ) {
				foreach ( (array) $meta_list as $metarow) {
					$mpid = (int) $metarow['post_id'];
					$mkey = $metarow['meta_key'];
					$mval = $metarow['meta_value'];

					if ( !isset($cache[$mpid]) || !is_array($cache[$mpid]) )
						$cache[$mpid] = array();
					if ( !isset($cache[$mpid][$mkey]) || !is_array($cache[$mpid][$mkey]) )
						$cache[$mpid][$mkey] = array();

					$cache[$mpid][$mkey][] = $mval;
				}
			}

			/*foreach ( (array) $ids as $id ) {
				if ( ! isset($cache[$id]) )
					$cache[$id] = array();
			}*/

			if ( !empty($cache) && is_array($cache) ) :
				foreach ( (array) array_keys($cache) as $post)
					wp_cache_set($post, $cache[$post], 'cft_post_meta');
				
				$meta_cache = wp_cache_get($post_id, 'cft_post_meta');
			endif;
		}
	
		if ( $key ) :
			if ( $single && isset($meta_cache[$key][0]) ) :
				return maybe_unserialize( $meta_cache[$key][0] );
			else :
				if ( isset($meta_cache[$key]) ) :
					if ( is_array($meta_cache[$key]) ) :
						return array_map('maybe_unserialize', $meta_cache[$key]);
					else :
						return $meta_cache[$key];
					endif;
				endif;
			endif;
		else :
			if ( is_array($meta_cache) ) :
				return array_map('maybe_unserialize', $meta_cache);
			endif;
		endif;

		return '';
	}

	function add_quick_edit_custom_box($column_name, $type) {
		if( $column_name == 'custom-fields' ) :
			global $wp_version;
			$options = $this->get_custom_field_template_data();
		
			if( $options == null)
				return;

			if ( !$options['css'] ) {
				$this->install_custom_field_template_css();
				$options = $this->get_custom_field_template_data();
			}
			
			$out = '';	
			$out .= '<fieldset style="clear:both;">' . "\n";
			$out .= '<div class="inline-edit-group">';
			$out .=	'<style type="text/css">' . "\n" .
					'<!--' . "\n";
			$out .=	$options['css'] . "\n";
			$out .=	'-->' . "\n" .
					'</style>';
		
			if ( count($options['custom_fields'])>1 ) {
				$out .= '<select id="custom_field_template_select">';
				for ( $i=0; $i < count($options['custom_fields']); $i++ ) {
					if ( isset($_REQUEST['post']) && isset($options['posts'][$_REQUEST['post']]) && $i == $options['posts'][$_REQUEST['post']] ) {
						$out .= '<option value="' . $i . '" selected="selected">' . stripcslashes($options['custom_fields'][$i]['title']) . '</option>';
					} else
						$out .= '<option value="' . $i . '">' . stripcslashes($options['custom_fields'][$i]['title']) . '</option>';
				}
				$out .= '</select>';
				$out .= '<input type="button" class="button" value="' . __('Load', 'custom-field-template') . '" onclick="var post = jQuery(this).parent().parent().parent().parent().attr(\'id\').replace(\'edit-\',\'\'); var cftloading_select = function() {jQuery.ajax({type: \'GET\', url: \'?page=custom-field-template/custom-field-template.php&cft_mode=ajaxload&id=\'+jQuery(\'#custom_field_template_select\').val()+\'&post=\'+post, success: function(html) {jQuery(\'#cft\').html(html);}});};cftloading_select(post);" />';
			}
		
			$out .= '<input type="hidden" name="custom-field-template-verify-key" id="custom-field-template-verify-key" value="' . wp_create_nonce('custom-field-template') . '" />';
			$out .= '<div id="cft" class="cft">';
			$out .= '</div>';

			$out .= '</div>' . "\n";
			$out .= '</fieldset>' . "\n";
		
			echo $out;
		endif;
	}
	
	function custom_field_template_admin_head() {
		global $wp_version, $post;
		$options = $this->get_custom_field_template_data();

		if ( !defined('WP_PLUGIN_DIR') )
			$plugin_dir = str_replace( ABSPATH, '', dirname(__FILE__) );
		else
			$plugin_dir = dirname( plugin_basename(__FILE__) );

		echo '<link rel="stylesheet" type="text/css" href="' . wp_guess_url() . '/' . PLUGINDIR . '/' . $plugin_dir . '/js/datePicker.css" />'."\n";

		if ( !empty($options['custom_field_template_use_validation']) ) :
			if( strstr($_SERVER['REQUEST_URI'], 'wp-admin/post-new.php') || strstr($_SERVER['REQUEST_URI'], 'wp-admin/post.php') || strstr($_SERVER['REQUEST_URI'], 'wp-admin/page-new.php') || strstr($_SERVER['REQUEST_URI'], 'wp-admin/page.php') || strstr($_SERVER['REQUEST_URI'], 'wp-admin/edit.php') || (is_object($post) && $post->post_type=='page') ) :
?>
<script type="text/javascript">
// <![CDATA[
	jQuery(document).ready(function() {
		jQuery("#post").validate();
	});
//-->
</script>
<style type="text/css">
<!--
	label.error				{ color:#FF0000; }
-->
</style>

<?php
			endif;
		endif;

		if ( substr($wp_version, 0, 3) >= '2.7' && is_user_logged_in() && ( strstr($_SERVER['REQUEST_URI'], 'wp-admin/edit.php') || strstr($_SERVER['REQUEST_URI'], 'wp-admin/edit-pages.php') ) && !strstr($_SERVER['REQUEST_URI'], 'page=') ) {
?>
<script type="text/javascript">
// <![CDATA[
	jQuery(document).ready(function() {
		jQuery('.hide-if-no-js-cft').show();
		jQuery('.hide-if-js-cft').hide();
		
		inlineEditPost.addEvents = function(r) {
			r.each(function() {
				var row = jQuery(this);
				jQuery('a.editinline', row).click(function() {
					inlineEditPost.edit(this);
					post_id = jQuery(this).parent().parent().parent().parent().attr('id').replace('post-','');
					inlineEditPost.cft_load(post_id);
					return false;
				});
			});
		}
		
		inlineEditPost.save = function(id) {
			if( typeof(id) == 'object' )
				id = this.getId(id);

			jQuery('table.widefat .inline-edit-save .waiting').show();

			var params = {
				action: 'inline-save',
				post_type: <?php if ( substr($wp_version, 0, 3) >= '3.0' ) echo 'typenow'; else echo 'this.type'; ?>, 
				post_ID: id,
				edit_date: 'true'
			};

			var fields = jQuery('#edit-'+id+' :input').fieldSerialize();
			params = fields + '&' + jQuery.param(params);

			// make ajax request
			jQuery.post('admin-ajax.php', params,
				function(r) {
					jQuery('table.widefat .inline-edit-save .waiting').hide();

					if (r) {
						if ( -1 != r.indexOf('<tr') ) {
							jQuery(inlineEditPost.what+id).remove();
							jQuery('#edit-'+id).before(r).remove();

							var row = jQuery(inlineEditPost.what+id);
							row.hide();

							if ( 'draft' == jQuery('input[name="post_status"]').val() )
								row.find('td.column-comments').hide();

							row.find('.hide-if-no-js').removeClass('hide-if-no-js');
							jQuery('.hide-if-no-js-cft').show();
							jQuery('.hide-if-js-cft').hide();

							inlineEditPost.addEvents(row);
							row.fadeIn();
						} else {
							r = r.replace( /<.[^<>]*?>/g, '' );
							jQuery('#edit-'+id+' .inline-edit-save').append('<span class="error">'+r+'</span>');
						}
					} else {
						jQuery('#edit-'+id+' .inline-edit-save').append('<span class="error">'+inlineEditL10n.error+'</span>');
					}
				}
			, 'html');
			return false;
		}
		
		jQuery('.editinline').click(function () {post_id = jQuery(this).parent().parent().parent().parent().attr('id').replace('post-',''); inlineEditPost.cft_load(post_id);});
		inlineEditPost.cft_load = function (post_id) {
			jQuery.ajax({type: 'GET', url: '?page=custom-field-template/custom-field-template.php&cft_mode=ajaxload&post='+post_id, success: function(html) {jQuery('#cft').html(html);}});
		};
	});
//-->
</script>
<style type="text/css">
<!--
	div.cft_list p.key		{ font-weight:bold; margin: 0; }
	div.cft_list p.value	{ margin: 0 0 0 10px; }
	.cft-actions			{ visibility: hidden; padding: 2px 0 0; }
	tr:hover .cft-actions	{ visibility: visible; }
	.inline-edit-row fieldset label { display:inline; }
	label.error				{ color:#FF0000; }
-->
</style>
<?php
		}
	}
	
	function custom_field_template_dbx_post_sidebar() {
		global $wp_version;
		$options = $this->get_custom_field_template_data();
		
		if ( !empty($options['custom_field_template_deploy_box']) ) :
			$suffix = '"+win.jQuery("#cft_current_template").val()+"';
		else :
			$suffix = '';
		endif;

		$out = '';
		$out .= 	'<script type="text/javascript">' . "\n" .
					'// <![CDATA[' . "\n";
		$out .=		'function cft_use_this(file_id) {
		var win = window.dialogArguments || opener || parent || top;
		win.jQuery("#"+win.jQuery("#cft_clicked_id").val()+"_hide").val(file_id);
		var fields = win.jQuery("#cft'.$suffix.' :input").fieldSerialize();
		win.jQuery.ajax({type: "POST", url: "?page=custom-field-template/custom-field-template.php&cft_mode=ajaxsave&post="+win.jQuery(\'#post_ID\').val()+"&custom-field-template-verify-key="+win.jQuery("#custom-field-template-verify-key").val(), data: fields, success: function() {win.jQuery.ajax({type: "GET", url: "?page=custom-field-template/custom-field-template.php&cft_mode=ajaxload&id="+win.jQuery("#cft_current_template").val()+"&post="+win.jQuery(\'#post_ID\').val(), success: function(html) {win.jQuery("#cft'.$suffix.'").html(html);win.tb_remove();}});}});
	}';

		$out .=		'function qt_set(new_id) { eval("qt_"+new_id+" = new QTags(\'qt_"+new_id+"\', \'"+new_id+"\', \'editorcontainer_"+new_id+"\', \'more\');");}';
		
		$out .=     'function _edInsertContent(myField, myValue) {
	var sel, startPos, endPos, scrollTop;

	//IE support
	if (document.selection) {
		myField.focus();
		sel = document.selection.createRange();
		sel.text = myValue;
		myField.focus();
	}
	//MOZILLA/NETSCAPE support
	else if (myField.selectionStart || myField.selectionStart == "0") {
		startPos = myField.selectionStart;
		endPos = myField.selectionEnd;
		scrollTop = myField.scrollTop;
		myField.value = myField.value.substring(0, startPos)
		              + myValue
                      + myField.value.substring(endPos, myField.value.length);
		myField.focus();
		myField.selectionStart = startPos + myValue.length;
		myField.selectionEnd = startPos + myValue.length;
		myField.scrollTop = scrollTop;
	} else {
		myField.value += myValue;
		myField.focus();
	}
}';

		$out .= 	'function send_to_custom_field(h) {' . "\n" .
					'	if ( tmpFocus ) ed = tmpFocus;' . "\n" .
					'	else if ( typeof tinyMCE == "undefined" ) ed = document.getElementById("content");' . "\n" .
					'	else { ed = tinyMCE.get("content"); if(ed) {if(!ed.isHidden()) isTinyMCE = true;}}' . "\n" .
					'	if ( typeof tinyMCE != "undefined" && isTinyMCE && !ed.isHidden() ) {' . "\n" .
					'		ed.focus();' . "\n" .
					'		if ( tinymce.isIE && ed.windowManager.insertimagebookmark )' . "\n" .
					'			ed.selection.moveToBookmark(ed.windowManager.insertimagebookmark);' . "\n" .
					'		if ( h.indexOf("[caption") === 0 ) {' . "\n" .
					'			if ( ed.plugins.wpeditimage )' . "\n" .
					'				h = ed.plugins.wpeditimage._do_shcode(h);' . "\n" .
					'		} else if ( h.indexOf("[gallery") === 0 ) {' . "\n" .
					'			if ( ed.plugins.wpgallery )' . "\n" .
					'				h = ed.plugins.wpgallery._do_gallery(h);' . "\n" .
					'		} else if ( h.indexOf("[embed") === 0 ) {' . "\n" .
					'			if ( ed.plugins.wordpress )' . "\n" .
					'				h = ed.plugins.wordpress._setEmbed(h);' . "\n" .
					'		}' . "\n" .
					'		ed.execCommand("mceInsertContent", false, h);' . "\n" .
					'	} else {' . "\n" .
					'		if ( tmpFocus ) _edInsertContent(tmpFocus, h);' . "\n" .
					'		else edInsertContent(edCanvas, h);' . "\n" .
					'	}' . "\n";
					
					if ( empty($options['custom_field_template_use_multiple_insert']) ) {
						$out .= '	tb_remove();' . "\n" .
								'	tmpFocus = undefined;' . "\n" .
								'	isTinyMCE = false;' . "\n";
					}

		if ( substr($wp_version, 0, 3) < '3.3' ) :
			$qt_position = 'jQuery(\'#editorcontainer_\'+id).prev()';
			$load_tinyMCE = 'tinyMCE.execCommand(' . "'mceAddControl'" . ',false, id);';
		elseif ( substr($wp_version, 0, 3) < '3.9' ) :
			$qt_position = 'jQuery(\'#qt_\'+id+\'_toolbar\')';
			$load_tinyMCE = 'var ed = new tinyMCE.Editor(id, tinyMCEPreInit.mceInit[\'content\']); ed.render();';
		else :
			$qt_position = 'jQuery(\'#qt_\'+id+\'_toolbar\')';
			$load_tinyMCE = 'tinyMCE.execCommand(' . "'mceAddEditor'" . ', true, id);';
		endif;

		$out .=		'}' . "\n" .
					'jQuery(".thickbox").bind("click", function (e) {' . "\n" .
					'	tmpFocus = undefined;' . "\n" .
					'	isTinyMCE = false;' . "\n" . 
					'});' . "\n" .
					'var isTinyMCE;' . "\n" .
					'var tmpFocus;' . "\n" .
					'function focusTextArea(id) {' . "\n" . 
					'	jQuery(document).ready(function() {' . "\n" .
					'		if ( typeof tinyMCE != "undefined" ) {' . "\n" .
					'			var elm = tinyMCE.get(id);' . "\n" .
					'		}' . "\n" .
					'		if ( ! elm || elm.isHidden() ) {' . "\n" .
					'			elm = document.getElementById(id);' . "\n" .
					'			isTinyMCE = false;' . "\n" .
					'		}else isTinyMCE = true;' . "\n" .
					'		tmpFocus = elm' . "\n" .
					'		elm.focus();' . "\n" .
					'		if (elm.createTextRange) {' . "\n" .
					'			var range = elm.createTextRange();' . "\n" .
					'			range.move("character", elm.value.length);' . "\n" .
					'			range.select();' . "\n" .
					'		} else if (elm.setSelectionRange) {' . "\n" .
					'			elm.setSelectionRange(elm.value.length, elm.value.length);' . "\n" .
					'		}' . "\n" .
					'	});' . "\n" .
					'}' . "\n" .
					'function switchMode(id) {' . "\n" .
					'	var ed = tinyMCE.get(id);' . "\n" .
					'	if ( ! ed || ed.isHidden() ) {' . "\n" .
					'		document.getElementById(id).value = switchEditors.wpautop(document.getElementById(id).value);' . "\n" .
					'		if ( ed ) { '.$qt_position.'.hide(); ed.show(); }' . "\n" .
					'		else {'.$load_tinyMCE.'}' . "\n" .
					'	} else {' . "\n" .
					'		ed.hide(); '.$qt_position.'.show(); document.getElementById(id).style.color="#000000";' . "\n" .
					'	}' . "\n" .
					'}' . "\n";
				
		$out .=		'function thickbox(link) {' . "\n" .
					'	var t = link.title || link.name || null;' . "\n" .
					'	var a = link.href || link.alt;' . "\n" .
					'	var g = link.rel || false;' . "\n" .
					'	tb_show(t,a,g);' . "\n" .
					'	link.blur();' . "\n" .
					'	return false;' . "\n" .
					'}' . "\n";
		$out .=     '//--></script>';
		$out .= '<input type="hidden" id="cft_current_template" value="" />';
		$out .= '<input type="hidden" id="cft_clicked_id" value="" />';
		$out .= '<input type="hidden" name="custom-field-template-verify-key" id="custom-field-template-verify-key" value="' . wp_create_nonce('custom-field-template') . '" />';

		$out .=		'<style type="text/css">' . "\n" .
					'<!--' . "\n";
		$out .=		$options['css'] . "\n";
		$out .=		'.editorcontainer { overflow:hidden; background:#FFFFFF; }
.content { width:98%; }
.editorcontainer .content { padding: 6px; line-height: 150%; border: 0 none; outline: none;	-moz-box-sizing: border-box;	-webkit-box-sizing: border-box;	-khtml-box-sizing: border-box; box-sizing: border-box; }
.quicktags { border:1px solid #DFDFDF; border-collapse: separate; -moz-border-radius: 6px 6px 0 0; -webkit-border-top-right-radius: 6px; -webkit-border-top-left-radius: 6px; -khtml-border-top-right-radius: 6px; -khtml-border-top-left-radius: 6px; border-top-right-radius: 6px; border-top-left-radius: 6px; }
.quicktags { padding: 0; margin-bottom: -1px; border-bottom-width:1px;	background-image: url("images/ed-bg.gif"); background-position: left top; background-repeat: repeat; }
.quicktags div div { padding: 2px 4px 0; }
.quicktags div div input { margin: 3px 1px 4px; line-height: 18px; display: inline-block; border-width: 1px; border-style: solid; min-width: 26px; padding: 2px 4px; font-size: 12px; -moz-border-radius: 3px; -khtml-border-radius: 3px; -webkit-border-radius: 3px; border-radius: 3px; background:#FFFFFF url(images/fade-butt.png) repeat-x scroll 0 -2px; overflow: visible; }' . "\n";
		$out .=		'-->' . "\n" .
					'</style>';
		echo $out;
	}
	
	function add_manage_posts_custom_column($column_name, $post_id) {
		$data = $this->get_post_meta($post_id);
		
		if( is_array($data) && $column_name == 'custom-fields' ) :
			$flag = 0;
			$content = $output = '';
			foreach($data as $key => $val) :
				if ( substr($key, 0, 1) == '_' || !$val[0] ) continue;
				$content .= '<p class="key">' . $key . '</p>' . "\n";
				foreach($val as $val2) :
					$val2 = htmlspecialchars($val2, ENT_QUOTES);
					if ( $flag ) :
						$content .= '<p class="value">' . $val2 . '</p>' . "\n";
					else :
						if ( function_exists('mb_strlen') ) :
							if ( mb_strlen($val2) > 50 ) :
								$before_content = mb_substr($val2, 0, 50);
								$after_content  = mb_substr($val2, 50);
								$content .= '<p class="value">' . $before_content . '[[[break]]]' . '<p class="value">' . $after_content . '</p>' . "\n";
								$flag = 1;
							else :
								$content .= '<p class="value">' . $val2 . '</p>' . "\n";
							endif;
						else :
							if ( strlen($val2) > 50 ) :
								$before_content = substr($val2, 0, 50);
								$after_content  = substr($val2, 50);
								$content .= '<p class="value">' . $before_content . '[[[break]]]' . '<p class="value">' . $after_content . '</p>' . "\n";
								$flag = 1;
							else :
								$content .= '<p class="value">' . $val2 . '</p>' . "\n";
							endif;
						endif;
					endif;
				endforeach;
			endforeach;
			if ( $content ) :
				$content = preg_replace('/([^\n]+)\n([^\n]+)\n([^\n]+)\n([^\n]+)\n([^$]+)/', '\1\2\3\4[[[break]]]\5', $content);
				@list($before, $after) = explode('[[[break]]]', $content, 2);
				$after = preg_replace('/\[\[\[break\]\]\]/', '', $after);
				$output .= '<div class="cft_list">';
				$output .= balanceTags($before, true);
				if ( $after ) :
					$output .= '<span class="hide-if-no-js-cft"><a href="javascript:void(0);" onclick="jQuery(this).parent().next().show(); jQuery(this).parent().next().next().show(); jQuery(this).parent().hide();">... ' . __('read more', 'custom-field-template') . '</a></span>';
					$output .= '<span class="hide-if-js-cft">' . balanceTags($after, true) . '</span>';
					$output .= '<span style="display:none;"><a href="javascript:void(0);" onclick="jQuery(this).parent().prev().hide(); jQuery(this).parent().prev().prev().show(); jQuery(this).parent().hide();">[^]</a></span>';
				endif;
				$output .= '</div>';
			else :
				$output .= '&nbsp;';
			endif;
		endif;
		
		if ( isset($output) ) echo $output;
	}
	
	function add_manage_posts_columns($columns) {
		$new_columns = array();
		foreach($columns as $key => $val) :
			$new_columns[$key] = $val;
			if ( $key == 'tags' )
				$new_columns['custom-fields'] = __('Custom Fields', 'custom-field-template');
		endforeach;
		return $new_columns;
	}
	
	function add_manage_pages_columns($columns) {
		$new_columns = array();
		foreach($columns as $key => $val) :
			$new_columns[$key] = $val;
			if ( $key == 'author' )
				$new_columns['custom-fields'] = __('Custom Fields', 'custom-field-template');
		endforeach;
		return $new_columns;
	}
	
	function media_send_to_custom_field($html) {
		if ( strstr($_SERVER['REQUEST_URI'], 'wp-admin/admin-ajax.php') ) return $html;
		$out =  '<script type="text/javascript">' . "\n" .
					'	/* <![CDATA[ */' . "\n" .
					'	var win = window.dialogArguments || opener || parent || top;' . "\n" .
					'   if ( typeof win.send_to_custom_field == "function" ) ' . "\n" .
					'	    win.send_to_custom_field("' . addslashes($html) . '");' . "\n" .
					'   else ' . "\n" .
					'       win.send_to_editor("' . addslashes($html) . '");' . "\n" .
					'/* ]]> */' . "\n" .
					'</script>' . "\n";

		echo $out;
		exit();

		/*if ($options['custom_field_template_use_multiple_insert']) {
			return;
		} else {
			exit();
		}*/
	}
	
	function wpaq_filter_plugin_actions($links, $file){
		static $this_plugin;

		if( ! $this_plugin ) $this_plugin = plugin_basename(__FILE__);

		if( $file == $this_plugin ){
			$settings_link = '<a href="options-general.php?page=custom-field-template.php">' . __('Settings') . '</a>';
			$links = array_merge( array($settings_link), $links);
		}
		return $links;
	}
	
	function custom_field_template_admin_scripts() {
		global $post;
		$options = $this->get_custom_field_template_data();
		$locale = get_locale();

		if ( !defined('WP_PLUGIN_DIR') )
			$plugin_dir = str_replace( ABSPATH, '', dirname(__FILE__) );
		else
			$plugin_dir = dirname( plugin_basename(__FILE__) );
		
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-form' );
		wp_enqueue_script( 'bgiframe', '/' . PLUGINDIR . '/' . $plugin_dir . '/js/jquery.bgiframe.js', array('jquery') ) ;
		if (strpos($_SERVER['REQUEST_URI'], 'custom-field-template') !== false ) 
			wp_enqueue_script( 'textarearesizer', '/' . PLUGINDIR . '/' . $plugin_dir . '/js/jquery.textarearesizer.js', array('jquery') );
		if( strstr($_SERVER['REQUEST_URI'], 'wp-admin/post-new.php') || strstr($_SERVER['REQUEST_URI'], 'wp-admin/post.php') || strstr($_SERVER['REQUEST_URI'], 'wp-admin/page-new.php') || strstr($_SERVER['REQUEST_URI'], 'wp-admin/page.php') || strstr($_SERVER['REQUEST_URI'], 'wp-admin/edit.php') || (is_object($post) && $post->post_type=='page') ) :
			wp_enqueue_script('date', '/' . PLUGINDIR . '/' . $plugin_dir . '/js/date.js', array('jquery') );
			wp_enqueue_script('datePicker', '/' . PLUGINDIR . '/' . $plugin_dir . '/js/jquery.datePicker.js', array('jquery') );
			wp_enqueue_script('editor');
			wp_enqueue_script('quicktags');

			if ( !empty($options['custom_field_template_use_validation']) ) :
				wp_enqueue_script( 'jquery-validate', '/' . PLUGINDIR . '/' . $plugin_dir . '/js/jquery.validate.js', array('jquery') );
				wp_enqueue_script( 'additional-methods', '/' . PLUGINDIR . '/' . $plugin_dir . '/js/additional-methods.js', array('jquery') );
				if ( file_exists(ABSPATH . PLUGINDIR . '/' . $plugin_dir . '/js/messages_' . $locale . '.js') )
					wp_enqueue_script( 'messages_' . $locale, '/' . PLUGINDIR . '/' . $plugin_dir . '/js/messages_' . $locale .'.js', array('jquery') );
			endif;
		endif;

	}

	function install_custom_field_template_data() {
		$options['custom_field_template_before_list'] = '<ul>';
		$options['custom_field_template_after_list'] = '</ul>';
		$options['custom_field_template_before_value'] = '<li>';
		$options['custom_field_template_after_value'] = '</li>';
		$options['custom_fields'][0]['title']   = __('Default Template', 'custom-field-template');
		$options['custom_fields'][0]['content'] = '[Plan]
type = text
size = 35
label = Where are you going to go?

[Plan]
type = textfield
size = 35
hideKey = true

[Favorite Fruits]
type = checkbox
value = apple # orange # banana # grape
default = orange # grape

[Miles Walked]
type = radio
value = 0-9 # 10-19 # 20+
default = 10-19
clearButton = true

[Temper Level]
type = select
value = High # Medium # Low
default = Low

[Hidden Thought]
type = textarea
rows = 4
cols = 40
tinyMCE = true
htmlEditor = true
mediaButton = true

[File Upload]
type = file';
		$options['shortcode_format'][0] = '<table class="cft">
<tbody>
<tr>
<th>Plan</th><td colspan="3">[Plan]</td>
</tr>
<tr>
<th>Favorite Fruits</th><td>[Favorite Fruits]</td>
<th>Miles Walked</th><td>[Miles Walked]</td>
</tr>
<tr>
<th>Temper Level</th><td colspan="3">[Temper Level]</td>
</tr>
<tr>
<th>Hidden Thought</th><td colspan="3">[Hidden Thought]</td>
</tr>
</tbody>
</table>';
		update_option('custom_field_template_data', $options);
	}
	
	function install_custom_field_template_css() {
		$options = get_option('custom_field_template_data');
		$options['css'] = '.cft { overflow:hidden; }
.cft:after { content:" "; clear:both; height:0; display:block; visibility:hidden; }
.cft dl { margin:10px 0; }
.cft dl:after { content:" "; clear:both; height:0; display:block; visibility:hidden; }
.cft dt { width:20%; clear:both; float:left; display:inline; font-weight:bold; text-align:center; }
.cft dt .hideKey { visibility:hidden; }
.cft dd { margin:0 0 0 21%; }
.cft dd p.label { font-weight:bold; margin:0; }
.cft_instruction { margin:10px; }
.cft fieldset { border:1px solid #CCC; margin:5px; padding:5px; }
.cft .dl_checkbox { margin:0; }
';
		update_option('custom_field_template_data', $options);
	}

	
	function get_custom_field_template_data() {
		$options = get_option('custom_field_template_data');
		if ( !empty($options) && !is_array($options) ) $options = array();
		return $options;
	}

	function custom_field_template_admin_menu() {
		add_options_page(__('Custom Field Template', 'custom-field-template'), __('Custom Field Template', 'custom-field-template'), 'manage_options', basename(__FILE__), array(&$this, 'custom_field_template_admin'));
	}
	
	
	function custom_field_template_get_the_excerpt($excerpt) {
		$options = $this->get_custom_field_template_data();
		if ( empty($excerpt) ) $this->is_excerpt = true;
		if ( !empty($options['custom_field_template_excerpt_shortcode']) ) return do_shortcode($excerpt);
		else return $excerpt;
	}
	
	function custom_field_template_the_content($content) {
		global $wp_query, $post, $shortcode_tags, $wp_version;
		$options = $this->get_custom_field_template_data();
				
		if ( isset($options['hook']) && count($options['hook']) > 0 ) :
			$categories = get_the_category();
			$cats = array();
			foreach( $categories as $val ) :
				$cats[] = $val->cat_ID;
			endforeach;
			
			for ( $i=0; $i<count($options['hook']); $i++ ) :
						
				if ( $this->is_excerpt && empty($options['hook'][$i]['excerpt']) ) :
					$this->is_excerpt = false;
					$content = $post->post_excerpt ? $post->post_excerpt : strip_shortcodes($content);
					$strip_shortcode = 1;
					continue;
				endif;

				$options['hook'][$i]['content'] = stripslashes($options['hook'][$i]['content']);
				if ( is_feed() && empty($options['hook'][$i]['feed']) ) break;
				if ( !empty($options['hook'][$i]['category']) ) :
					if ( is_category() || is_single() || is_feed() ) :
						if ( !empty($options['hook'][$i]['use_php']) ) :
							$options['hook'][$i]['content'] = $this->EvalBuffer(stripcslashes($options['hook'][$i]['content']));
						endif;
						$needle = explode(',', $options['hook'][$i]['category']);
						$needle = array_filter($needle);
						$needle = array_unique(array_filter(array_map('trim', $needle)));
						foreach ( $needle as $val ) :
							if ( in_array($val, $cats ) ) :
								if ( $options['hook'][$i]['position'] == 0 )
									$content .= $options['hook'][$i]['content'];
								elseif ( $options['hook'][$i]['position'] == 2 )
									$content = preg_replace('/\[cfthook hook='.$i.'\]/', $options['hook'][$i]['content'], $content);
								else
									$content = $options['hook'][$i]['content'] . $content;
								break;
							endif;
						endforeach;
					endif;
				elseif ( $options['hook'][$i]['post_type']=='post' ) :
					if ( is_single() ) :
						if ( !empty($options['hook'][$i]['use_php']) ) :
							$options['hook'][$i]['content'] = $this->EvalBuffer(stripcslashes($options['hook'][$i]['content']));
						endif;
						if ( $options['hook'][$i]['position'] == 0 )
							$content .= $options['hook'][$i]['content'];
						elseif ( $options['hook'][$i]['position'] == 2 )
							$content = preg_replace('/\[cfthook hook='.$i.'\]/', $options['hook'][$i]['content'], $content);
						else
							$content = $options['hook'][$i]['content'] . $content;
					endif;		
				elseif ( $options['hook'][$i]['post_type']=='page' ) :
					if ( is_page() ) :
						if ( !empty($options['hook'][$i]['use_php']) ) :
							$options['hook'][$i]['content'] = $this->EvalBuffer(stripcslashes($options['hook'][$i]['content']));
						endif;
						if ( $options['hook'][$i]['position'] == 0 )
							$content .= $options['hook'][$i]['content'];
						elseif ( $options['hook'][$i]['position'] == 2 )
							$content = preg_replace('/\[cfthook hook='.$i.'\]/', $options['hook'][$i]['content'], $content);
						else
							$content = $options['hook'][$i]['content'] . $content;
					endif;
				elseif ( $options['hook'][$i]['custom_post_type'] ) :
					$custom_post_type = explode(',', $options['hook'][$i]['custom_post_type']);
					$custom_post_type = array_filter( $custom_post_type );
					array_walk( $custom_post_type, create_function('&$v', '$v = trim($v);') );
					if ( in_array($post->post_type, $custom_post_type) ) :
						if ( !empty($options['hook'][$i]['use_php']) ) :
							$options['hook'][$i]['content'] = $this->EvalBuffer(stripcslashes($options['hook'][$i]['content']));
						endif;
						if ( $options['hook'][$i]['position'] == 0 )
							$content .= $options['hook'][$i]['content'];
						elseif ( $options['hook'][$i]['position'] == 2 )
							$content = preg_replace('/\[cfthook hook='.$i.'\]/', $options['hook'][$i]['content'], $content);
						else
							$content = $options['hook'][$i]['content'] . $content;
					endif;
				else :
					if ( !empty($options['hook'][$i]['use_php']) ) :
							$options['hook'][$i]['content'] = $this->EvalBuffer(stripcslashes($options['hook'][$i]['content']));
					endif;
					if ( $options['hook'][$i]['position'] == 0 )
						$content .= $options['hook'][$i]['content'];
					elseif ( $options['hook'][$i]['position'] == 2 )
						$content = preg_replace('/\[cfthook hook='.$i.'\]/', $options['hook'][$i]['content'], $content);
					else
						$content = $options['hook'][$i]['content'] . $content;
				endif;
			endfor;
		endif;
				
		return !empty($strip_shortcode)? $content : do_shortcode($content);
	}
	
	function custom_field_template_admin() {
		global $wp_version;
		$locale = get_locale();
		
		$options = $this->get_custom_field_template_data();

		if( !empty($_POST["custom_field_template_set_options_submit"]) ) :
			unset($options['custom_fields']);
			$j = 0;
			for($i=0;$i<count($_POST["custom_field_template_content"]);$i++) {
				if( $_POST["custom_field_template_content"][$i] ) {
					if ( preg_match('/\[content\]|\[post_title\]|\[excerpt\]|\[action\]/i', $_POST["custom_field_template_content"][$i]) ) :
						$errormessage = __('You can not use the following words as the field key: `content`, `post_title`, and `excerpt`, and `action`.', 'custom-field-template');
					endif;
					if ( isset($_POST["custom_field_template_title"][$i]) ) $options['custom_fields'][$j]['title']   = $_POST["custom_field_template_title"][$i];
					if ( isset($_POST["custom_field_template_content"][$i]) ) $options['custom_fields'][$j]['content'] = $_POST["custom_field_template_content"][$i];
					if ( isset($_POST["custom_field_template_instruction"][$i]) ) $options['custom_fields'][$j]['instruction'] = $_POST["custom_field_template_instruction"][$i];
					if ( isset($_POST["custom_field_template_category"][$i]) ) $options['custom_fields'][$j]['category'] = $_POST["custom_field_template_category"][$i];
					if ( isset($_POST["custom_field_template_post"][$i]) ) $options['custom_fields'][$j]['post'] = $_POST["custom_field_template_post"][$i];
					if ( isset($_POST["custom_field_template_post_type"][$i]) ) $options['custom_fields'][$j]['post_type'] = $_POST["custom_field_template_post_type"][$i];
					if ( isset($_POST["custom_field_template_custom_post_type"][$i]) ) $options['custom_fields'][$j]['custom_post_type'] = $_POST["custom_field_template_custom_post_type"][$i];
					if ( isset($_POST["custom_field_template_template_files"][$i]) ) $options['custom_fields'][$j]['template_files'] = $_POST["custom_field_template_template_files"][$i];					
					if ( isset($_POST["custom_field_template_disable"][$i]) ) $options['custom_fields'][$j]['disable'] = $_POST["custom_field_template_disable"][$i];					
					$options['custom_fields'][$j]['format'] = isset($_POST["custom_field_template_format"][$i]) ? $_POST["custom_field_template_format"][$i] : '';
					$j++;
				}
			}			
			update_option('custom_field_template_data', $options);
			$message = __('Options updated.', 'custom-field-template');
		elseif( !empty($_POST["custom_field_template_global_settings_submit"]) ) :
			$options['custom_field_template_replace_keys_by_labels'] = isset($_POST['custom_field_template_replace_keys_by_labels']) ? 1 : '';
			$options['custom_field_template_use_multiple_insert'] = isset($_POST['custom_field_template_use_multiple_insert']) ? 1 : '';
			$options['custom_field_template_use_wpautop'] = isset($_POST['custom_field_template_use_wpautop']) ? 1 : '';
			$options['custom_field_template_use_autosave'] = isset($_POST['custom_field_template_use_autosave']) ? 1 : '';
			$options['custom_field_template_use_disable_button'] = isset($_POST['custom_field_template_use_disable_button']) ? 1 : '';
			$options['custom_field_template_disable_initialize_button'] = isset($_POST['custom_field_template_disable_initialize_button']) ? 1 : '';
			$options['custom_field_template_disable_save_button'] = isset($_POST['custom_field_template_disable_save_button']) ? 1 : '';
			$options['custom_field_template_disable_default_custom_fields'] = isset($_POST['custom_field_template_disable_default_custom_fields']) ? 1 : '';
			$options['custom_field_template_disable_quick_edit'] = isset($_POST['custom_field_template_disable_quick_edit']) ? 1 : '';
			$options['custom_field_template_disable_custom_field_column'] = isset($_POST['custom_field_template_disable_custom_field_column']) ? 1 : '';
			$options['custom_field_template_replace_the_title'] = isset($_POST['custom_field_template_replace_the_title']) ? 1 : '';
			$options['custom_field_template_deploy_box'] = isset($_POST['custom_field_template_deploy_box']) ? 1 : '';
			if ( !empty($options['custom_field_template_deploy_box']) ) :
				$options['css'] = preg_replace('/#cft /', '.cft ', $options['css']);
				$options['css'] = preg_replace('/#cft_/', '.cft_', $options['css']);
			endif;
			$options['custom_field_template_widget_shortcode'] = isset($_POST['custom_field_template_widget_shortcode']) ? 1 : '';
			$options['custom_field_template_excerpt_shortcode'] = isset($_POST['custom_field_template_excerpt_shortcode']) ? 1 : '';
			$options['custom_field_template_use_validation'] = isset($_POST['custom_field_template_use_validation']) ? 1 : '';
			$options['custom_field_template_before_list'] = isset($_POST['custom_field_template_before_list']) ? $_POST['custom_field_template_before_list'] : '';
			$options['custom_field_template_after_list'] = isset($_POST['custom_field_template_after_list']) ? $_POST['custom_field_template_after_list'] : '';
			$options['custom_field_template_before_value'] = isset($_POST['custom_field_template_before_value']) ? $_POST['custom_field_template_before_value'] : '';
			$options['custom_field_template_after_value'] = isset($_POST['custom_field_template_after_value']) ? $_POST['custom_field_template_after_value'] : '';
			$options['custom_field_template_replace_keys_by_labels'] = isset($_POST['custom_field_template_replace_keys_by_labels']) ? 1 : '';
			$options['custom_field_template_replace_keys_by_labels'] = isset($_POST['custom_field_template_replace_keys_by_labels']) ? 1 : '';
			$options['custom_field_template_replace_keys_by_labels'] = isset($_POST['custom_field_template_replace_keys_by_labels']) ? 1 : '';
			$options['custom_field_template_disable_ad'] = isset($_POST['custom_field_template_disable_ad']) ? 1 : '';
			update_option('custom_field_template_data', $options);
			$message = __('Options updated.', 'custom-field-template');
		elseif ( !empty($_POST['custom_field_template_css_submit']) ) :
			$options['css'] = $_POST['custom_field_template_css'];
			update_option('custom_field_template_data', $options);
			$message = __('Options updated.', 'custom-field-template');
		elseif ( !empty($_POST['custom_field_template_shortcode_format_submit']) ) :
			unset($options['shortcode_format'], $options['shortcode_format_use_php']);
			$j = 0;
			for($i=0;$i<count($_POST["custom_field_template_shortcode_format"]);$i++) {
				if( !empty($_POST["custom_field_template_shortcode_format"][$i]) ) :
					$options['shortcode_format'][$j] = $_POST["custom_field_template_shortcode_format"][$i];
					$options['shortcode_format_use_php'][$j] = isset($_POST["custom_field_template_shortcode_format_use_php"][$i]) ? $_POST["custom_field_template_shortcode_format_use_php"][$i] : '';
					$j++;
				endif;
			}			
			update_option('custom_field_template_data', $options);
			$message = __('Options updated.', 'custom-field-template');
		elseif ( !empty($_POST['custom_field_template_php_submit']) ) :
			unset($options['php']);
			for($i=0;$i<count($_POST["custom_field_template_php"]);$i++) {
				if( !empty($_POST["custom_field_template_php"][$i]) )
					$options['php'][] = $_POST["custom_field_template_php"][$i];
			}			
			update_option('custom_field_template_data', $options);
			$message = __('Options updated.', 'custom-field-template');
		elseif( !empty($_POST["custom_field_template_hook_submit"]) ) :
			unset($options['hook']);
			$j = 0;
			for($i=0;$i<count($_POST["custom_field_template_hook_content"]);$i++) {
				if( $_POST["custom_field_template_hook_content"][$i] ) {
					$options['hook'][$j]['position'] = !empty($_POST["custom_field_template_hook_position"][$i]) ? $_POST["custom_field_template_hook_position"][$i] : '';
					$options['hook'][$j]['content']  = $_POST["custom_field_template_hook_content"][$i];
					$options['hook'][$j]['custom_post_type'] = preg_replace('/\s/', '', $_POST["custom_field_template_hook_custom_post_type"][$i]);
					$options['hook'][$j]['category'] = preg_replace('/\s/', '', $_POST["custom_field_template_hook_category"][$i]);
					$options['hook'][$j]['use_php']  = !empty($_POST["custom_field_template_hook_use_php"][$i]) ? $_POST["custom_field_template_hook_use_php"][$i] : '';
					$options['hook'][$j]['feed']  = !empty($_POST["custom_field_template_hook_feed"][$i]) ? $_POST["custom_field_template_hook_feed"][$i] : '';
					$options['hook'][$j]['post_type']  = !empty($_POST["custom_field_template_hook_post_type"][$i]) ? $_POST["custom_field_template_hook_post_type"][$i] : '';
					$options['hook'][$j]['excerpt']  = !empty($_POST["custom_field_template_hook_excerpt"][$i]) ? $_POST["custom_field_template_hook_excerpt"][$i] : '';
					$j++;
				}
			}			
			update_option('custom_field_template_data', $options);
			$message = __('Options updated.', 'custom-field-template');
		elseif ( !empty($_POST['custom_field_template_rebuild_value_counts_submit']) ) :
			$this->custom_field_template_rebuild_value_counts();
			$options = $this->get_custom_field_template_data();
			$message = __('Value Counts rebuilt.', 'custom-field-template');
		elseif ( !empty($_POST['custom_field_template_rebuild_tags_submit']) ) :
			$options = $this->get_custom_field_template_data();
			$message = __('Tags rebuilt.', 'custom-field-template');
		elseif ( !empty($_POST['custom_field_template_import_options_submit']) ) :
			if ( is_uploaded_file($_FILES['cftfile']['tmp_name']) ) :
				ob_start();
				readfile ($_FILES['cftfile']['tmp_name']);
				$import = ob_get_contents();
				ob_end_clean();
				$import = maybe_unserialize($import);
				update_option('custom_field_template_data', $import);
				$message = __('Options imported.', 'custom-field-template');
				$options = $this->get_custom_field_template_data();
			endif;
		elseif ( !empty($_POST['custom_field_template_reset_options_submit']) ) :
			$this->install_custom_field_template_data();
			$this->install_custom_field_template_css();
			$options = $this->get_custom_field_template_data();
			$message = __('Options resetted.', 'custom-field-template');
		elseif ( !empty($_POST['custom_field_template_delete_options_submit']) ) :
			delete_option('custom_field_template_data');
			$options = $this->get_custom_field_template_data();
			$message = __('Options deleted.', 'custom-field-template');
		endif;
		
		if ( !defined('WP_PLUGIN_DIR') )
			$plugin_dir = str_replace( ABSPATH, '', dirname(__FILE__) );
		else
			$plugin_dir = dirname( plugin_basename(__FILE__) );
?>
<style type="text/css">
#poststuff h3								{ font-size: 14px; line-height: 1.4; margin: 0; padding: 8px 12px; }
div.grippie {
background:#EEEEEE url(<?php echo '../' . PLUGINDIR . '/' . $plugin_dir . '/js/'; ?>grippie.png) no-repeat scroll center 2px;
border-color:#DDDDDD;
border-style:solid;
border-width:0pt 1px 1px;
cursor:s-resize;
height:9px;
overflow:hidden;
}
.resizable-textarea textarea {
display:block;
margin-bottom:0pt;
}
</style>
<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery('textarea.resizable:not(.processed)').TextAreaResizer();
	});
</script>
<?php if ( !empty($message) ) : ?>
<div id="message" class="updated"><p><?php echo $message; ?></p></div>
<?php endif; ?>
<?php if ( !empty($errormessage) ) : ?>
<div id="errormessage" class="error"><p><?php echo $errormessage; ?></p></div>
<?php endif; ?>
<div class="wrap">
<div id="icon-plugins" class="icon32"><br/></div>
<h2><?php _e('Custom Field Template', 'custom-field-template'); ?></h2>

<br class="clear"/>

<div id="poststuff" style="position: relative; margin-top:10px;">
<?php if ( empty($options['custom_field_template_disable_ad']) ) : ?><div style="width:75%; float:left;"><?php endif; ?>
<div class="postbox">
<div class="handlediv" title="<?php _e('Click to toggle', 'custom-field-template'); ?>"><br /></div>
<h3><?php _e('Custom Field Template Options', 'custom-field-template'); ?></h3>
<div class="inside">
<form method="post">
<table class="form-table" style="margin-bottom:5px;">
<tbody>
<?php
	for ( $i = 0; $i < count($options['custom_fields'])+1; $i++ ) {
?>
<tr><td>
<p><strong>TEMPLATE #<?php echo $i; ?></strong>
<label for="custom_field_template_disable[<?php echo $i; ?>]"><input type="checkbox" name="custom_field_template_disable[<?php echo $i; ?>]" id="custom_field_template_disable[<?php echo $i; ?>]" value="1" <?php if ( isset($options['custom_fields'][$i]['disable']) ) checked(1, $options['custom_fields'][$i]['disable']); ?> /> <?php _e('Disable', 'custom-field-template'); ?></label>
</p>
<p><label for="custom_field_template_title[<?php echo $i; ?>]"><?php echo sprintf(__('Template Title', 'custom-field-template'), $i); ?></label>:<br />
<input type="text" name="custom_field_template_title[<?php echo $i; ?>]" id="custom_field_template_title[<?php echo $i; ?>]" value="<?php if ( isset($options['custom_fields'][$i]['title']) )  echo esc_attr(stripcslashes($options['custom_fields'][$i]['title'])); ?>" size="80" /></p>
<p><label for="custom_field_template_instruction[<?php echo $i; ?>]"><a href="javascript:void(0);" onclick="jQuery(this).parent().next().next().toggle();"><?php echo sprintf(__('Template Instruction', 'custom-field-template'), $i); ?></a></label>:<br />
<textarea class="large-text" name="custom_field_template_instruction[<?php echo $i; ?>]" id="custom_field_template_instruction[<?php echo $i; ?>]" rows="5" cols="80"<?php if ( empty($options['custom_fields'][$i]['instruction']) ) : echo ' style="display:none;"'; endif; ?>><?php if ( isset($options['custom_fields'][$i]['instruction']) ) echo htmlspecialchars(stripcslashes($options['custom_fields'][$i]['instruction'])); ?></textarea></p>
<p><label for="custom_field_template_post_type[<?php echo $i; ?>]"><a href="javascript:void(0);" onclick="jQuery(this).parent().next().next().toggle();"><?php echo sprintf(__('Post Type', 'custom-field-template'), $i); ?></a></label>:<br />
<span<?php if ( empty($options['custom_fields'][$i]['post_type']) ) : echo ' style="display:none;"'; endif; ?>>
<input type="radio" name="custom_field_template_post_type[<?php echo $i; ?>]" id="custom_field_template_post_type[<?php echo $i; ?>]" value=""<?php if ( !isset($options['custom_fields'][$i]['post_type']) ) :  echo ' checked="checked"'; endif; ?> /> <?php _e('Both', 'custom-field-template'); ?>
<input type="radio" name="custom_field_template_post_type[<?php echo $i; ?>]" id="custom_field_template_post_type[<?php echo $i; ?>]" value="post"<?php if ( !empty($options['custom_fields'][$i]['post_type']) && $options['custom_fields'][$i]['post_type']=='post') : echo ' checked="checked"'; endif; ?> /> <?php _e('Post', 'custom-field-template'); ?>
<input type="radio" name="custom_field_template_post_type[<?php echo $i; ?>]" id="custom_field_template_post_type[<?php echo $i; ?>]" value="page"<?php if ( !empty($options['custom_fields'][$i]['post_type']) && $options['custom_fields'][$i]['post_type']=='page') : echo ' checked="checked"'; endif; ?> /> <?php _e('Page', 'custom-field-template'); ?></span></p>
<p><label for="custom_field_template_custom_post_type[<?php echo $i; ?>]"><a href="javascript:void(0);" onclick="jQuery(this).parent().next().next().toggle();"><?php echo sprintf(__('Custom Post Type (comma-deliminated)', 'custom-field-template'), $i); ?></a></label>:<br />
<input type="text" name="custom_field_template_custom_post_type[<?php echo $i; ?>]" id="custom_field_template_custom_post_type[<?php echo $i; ?>]" value="<?php if ( isset($options['custom_fields'][$i]['custom_post_type']) ) echo esc_attr(stripcslashes($options['custom_fields'][$i]['custom_post_type'])); ?>" size="80"<?php if ( empty($options['custom_fields'][$i]['custom_post_type']) ) : echo ' style="display:none;"'; endif; ?> /></p>
<p><label for="custom_field_template_post[<?php echo $i; ?>]"><a href="javascript:void(0);" onclick="jQuery(this).parent().next().next().toggle();"><?php echo sprintf(__('Post ID (comma-deliminated)', 'custom-field-template'), $i); ?></a></label>:<br />
<input type="text" name="custom_field_template_post[<?php echo $i; ?>]" id="custom_field_template_post[<?php echo $i; ?>]" value="<?php if ( isset($options['custom_fields'][$i]['post']) ) echo esc_attr(stripcslashes($options['custom_fields'][$i]['post'])); ?>" size="80"<?php if ( empty($options['custom_fields'][$i]['post']) ) : echo ' style="display:none;"'; endif; ?> /></p>
<p><label for="custom_field_template_category[<?php echo $i; ?>]"><a href="javascript:void(0);" onclick="jQuery(this).parent().next().next().toggle();"><?php echo sprintf(__('Category ID (comma-deliminated)', 'custom-field-template'), $i); ?></a></label>:<br />
<input type="text" name="custom_field_template_category[<?php echo $i; ?>]" id="custom_field_template_category[<?php echo $i; ?>]" value="<?php if ( isset($options['custom_fields'][$i]['category']) ) echo esc_attr(stripcslashes($options['custom_fields'][$i]['category'])); ?>" size="80"<?php if ( empty($options['custom_fields'][$i]['category']) ) : echo ' style="display:none;"'; endif; ?> /></p>
<p><label for="custom_field_template_template_files[<?php echo $i; ?>]"><a href="javascript:void(0);" onclick="jQuery(this).parent().next().next().toggle();"><?php echo sprintf(__('Page Template file name(s) (comma-deliminated)', 'custom-field-template'), $i); ?></a></label>:<br />
<input type="text" name="custom_field_template_template_files[<?php echo $i; ?>]" id="custom_field_template_template_files[<?php echo $i; ?>]" value="<?php if ( isset($options['custom_fields'][$i]['template_files']) ) echo esc_attr(stripcslashes($options['custom_fields'][$i]['template_files'])); ?>" size="80"<?php if ( empty($options['custom_fields'][$i]['template_files']) ) : echo ' style="display:none;"'; endif; ?> /></p>
<p><label for="custom_field_template_format[<?php echo $i; ?>]"><a href="javascript:void(0);" onclick="jQuery(this).parent().next().next().toggle();"><?php echo sprintf(__('Template Format', 'custom-field-template'), $i); ?></a></label>:<br />
<select name="custom_field_template_format[<?php echo $i; ?>]" <?php if ( !isset($options['custom_fields'][$i]['format']) || !is_numeric($options['custom_fields'][$i]['format']) ) : echo ' style="display:none;"'; endif; ?>>
<option value=""></option>
<?php
	if ( isset($options['shortcode_format']) ) $count = count($options['shortcode_format']);
	else $count = 0;
	for ($j=0;$j<$count;$j++) :
?>
<option value="<?php echo $j; ?>"<?php if ( isset($options['custom_fields'][$i]['format']) && is_numeric($options['custom_fields'][$i]['format']) ) selected($j, $options['custom_fields'][$i]['format']); ?>>FORMAT #<?php echo $j; ?></option>
<?php
	endfor;
?>
</select></p>
<p><label for="custom_field_template_content[<?php echo $i; ?>]"><?php echo sprintf(__('Template Content', 'custom-field-template'), $i); ?></label>:<br />
<textarea name="custom_field_template_content[<?php echo $i; ?>]" class="resizable large-text" id="custom_field_template_content[<?php echo $i; ?>]" rows="10" cols="80"><?php if ( isset($options['custom_fields'][$i]['content']) ) echo htmlspecialchars(stripcslashes($options['custom_fields'][$i]['content'])); ?></textarea></p>
</td></tr>
<?php
	}
?>
<tr><td>
<p><input type="submit" name="custom_field_template_set_options_submit" value="<?php _e('Update Options &raquo;', 'custom-field-template'); ?>" class="button-primary" /></p>
</td></tr>
</tbody>
</table>
</form>
</div>
</div>

<div class="postbox closed">
<div class="handlediv" title="<?php _e('Click to toggle', 'custom-field-template'); ?>"><br /></div>
<h3><?php _e('Global Settings', 'custom-field-template'); ?></h3>
<div class="inside">
<form method="post">
<table class="form-table" style="margin-bottom:5px;">
<tbody>
<?php
/*
<tr><td>
<p><label for="custom_field_template_use_multiple_insert"><?php _e('In case that you would like to insert multiple images at once in use of the custom field media buttons', 'custom-field-template'); ?></label>:<br />
<input type="checkbox" name="custom_field_template_use_multiple_insert" id="custom_field_template_use_multiple_insert" value="1" <?php if ($options['custom_field_template_use_multiple_insert']) { echo 'checked="checked"'; } ?> /> <?php _e('Use multiple image inset', 'custom-field-template'); ?><br /><span style="color:#FF0000; font-weight:bold;"><?php _e('Caution:', 'custom-field-teplate'); ?> <?php _e('You need to edit `wp-admin/includes/media.php`. Delete or comment out the code in the function media_send_to_editor.', 'custom-field-template'); ?></span></p>
</td>
</tr>
*/
?>
<tr><td>
<p><label for="custom_field_template_replace_keys_by_labels"><?php _e('In case that you would like to replace custom keys by labels if `label` is set', 'custom-field-template'); ?>:<br />
<input type="checkbox" name="custom_field_template_replace_keys_by_labels" id="custom_field_template_replace_keys_by_labels" value="1" <?php if ( !empty($options['custom_field_template_replace_keys_by_labels']) ) { echo 'checked="checked"'; } ?> /> <?php _e('Use labels in place of custom keys', 'custom-field-template'); ?></label></p>
</td></tr>
<tr><td>
<p><label for="custom_field_template_use_wpautop"><?php _e('In case that you would like to add p and br tags in textareas automatically', 'custom-field-template'); ?>:<br />
<input type="checkbox" name="custom_field_template_use_wpautop" id="custom_field_template_use_wpautop" value="1" <?php if ( !empty($options['custom_field_template_use_wpautop']) ) { echo 'checked="checked"'; } ?> /> <?php _e('Use wpautop function', 'custom-field-template'); ?></label></p>
</td>
</tr>
<tr><td>
<p><label for="custom_field_template_use_autosave"><?php _e('In case that you would like to save values automatically in switching templates', 'custom-field-template'); ?>:<br />
<input type="checkbox" name="custom_field_template_use_autosave" id="custom_field_template_use_autosave" value="1" <?php if ( !empty($options['custom_field_template_use_autosave']) ) { echo 'checked="checked"'; } ?> /> <?php _e('Use the auto save in switching templates', 'custom-field-template'); ?></label></p>
</td>
</tr>
<tr><td>
<p><label for="custom_field_template_use_disable_button"><?php _e('In case that you would like to disable input fields of the custom field template temporarily', 'custom-field-template'); ?>:<br />
<input type="checkbox" name="custom_field_template_use_disable_button" id="custom_field_template_use_disable_button" value="1" <?php if ( !empty($options['custom_field_template_use_disable_button']) ) { echo 'checked="checked"'; } ?> /> <?php _e('Use the `Disable` button. The default custom fields will be superseded.', 'custom-field-template'); ?></label></p>
</td>
</tr>
<tr><td>
<p><label for="custom_field_template_disable_initialize_button"><?php _e('In case that you would like to forbid to use the initialize button.', 'custom-field-template'); ?>:<br />
<input type="checkbox" name="custom_field_template_disable_initialize_button" id="custom_field_template_disable_initialize_button" value="1" <?php if ( !empty($options['custom_field_template_disable_initialize_button']) ) { echo 'checked="checked"'; } ?> /> <?php _e('Disable the initialize button', 'custom-field-template'); ?></label></p>
</td>
</tr>
<tr><td>
<p><label for="custom_field_template_disable_save_button"><?php _e('In case that you would like to forbid to use the save button.', 'custom-field-template'); ?>:<br />
<input type="checkbox" name="custom_field_template_disable_save_button" id="custom_field_template_disable_save_button" value="1" <?php if ( !empty($options['custom_field_template_disable_save_button']) ) { echo 'checked="checked"'; } ?> /> <?php _e('Disable the save button', 'custom-field-template'); ?></label></p>
</td>
</tr>
<tr><td>
<p><label for="custom_field_template_disable_default_custom_fields"><?php _e('In case that you would like to forbid to use the default custom fields.', 'custom-field-template'); ?>:<br />
<input type="checkbox" name="custom_field_template_disable_default_custom_fields" id="custom_field_template_disable_default_custom_fields" value="1" <?php if ( !empty($options['custom_field_template_disable_default_custom_fields']) ) { echo 'checked="checked"'; } ?> /> <?php _e('Disable the default custom fields', 'custom-field-template'); ?></label></p>
</td>
</tr>
<tr><td>
<p><label for="custom_field_template_disable_quick_edit"><?php _e('In case that you would like to forbid to use the quick edit.', 'custom-field-template'); ?>:<br />
<input type="checkbox" name="custom_field_template_disable_quick_edit" id="custom_field_template_disable_quick_edit" value="1" <?php if ( !empty($options['custom_field_template_disable_quick_edit']) ) { echo 'checked="checked"'; } ?> /> <?php _e('Disable the quick edit', 'custom-field-template'); ?></label></p>
</td>
</tr>
<tr><td>
<p><label for="custom_field_template_disable_custom_field_column"><?php _e('In case that you would like to forbid to display the custom field column on the edit post list page.', 'custom-field-template'); ?>:<br />
<input type="checkbox" name="custom_field_template_disable_custom_field_column" id="custom_field_template_disable_custom_field_column" value="1" <?php if ( !empty($options['custom_field_template_disable_custom_field_column']) ) { echo 'checked="checked"'; } ?> /> <?php _e('Disable the custom field column (The quick edit also does not work.)', 'custom-field-template'); ?></label></p>
</td>
</tr>
<tr><td>
<p><label for="custom_field_template_replace_the_title"><?php _e('In case that you would like to replace the box title with the template title.', 'custom-field-template'); ?>:<br />
<input type="checkbox" name="custom_field_template_replace_the_title" id="custom_field_template_replace_the_title" value="1" <?php if ( !empty($options['custom_field_template_replace_the_title']) ) { echo 'checked="checked"'; } ?> /> <?php _e('Replace the box title', 'custom-field-template'); ?></label></p>
</td>
</tr>
<tr><td>
<p><label for="custom_field_template_deploy_box"><?php _e('In case that you would like to deploy the box in each template.', 'custom-field-template'); ?>:<br />
<input type="checkbox" name="custom_field_template_deploy_box" id="custom_field_template_deploy_box" value="1" <?php if ( !empty($options['custom_field_template_deploy_box']) ) { echo 'checked="checked"'; } ?> /> <?php _e('Deploy the box in each template', 'custom-field-template'); ?></label></p>
</td>
</tr>
<tr><td>
<p><label for="custom_field_template_widget_shortcode"><?php _e('In case that you would like to use the shortcode in the widget.', 'custom-field-template'); ?>:<br />
<input type="checkbox" name="custom_field_template_widget_shortcode" id="custom_field_template_widget_shortcode" value="1" <?php if ( !empty($options['custom_field_template_widget_shortcode']) ) { echo 'checked="checked"'; } ?> /> <?php _e('Use the shortcode in the widget', 'custom-field-template'); ?></label></p>
</td>
</tr>
<tr><td>
<p><label for="custom_field_template_excerpt_shortcode"><?php _e('In case that you would like to use the shortcode in the excerpt.', 'custom-field-template'); ?>:<br />
<input type="checkbox" name="custom_field_template_excerpt_shortcode" id="custom_field_template_excerpt_shortcode" value="1" <?php if ( !empty($options['custom_field_template_excerpt_shortcode']) ) { echo 'checked="checked"'; } ?> /> <?php _e('Use the shortcode in the excerpt', 'custom-field-template'); ?></label></p>
</td>
</tr>
<tr><td>
<p><label for="custom_field_template_use_validation"><?php _e('In case that you would like to use the jQuery validation.', 'custom-field-template'); ?>:<br />
<input type="checkbox" name="custom_field_template_use_validation" id="custom_field_template_use_validation" value="1" <?php if ( !empty($options['custom_field_template_use_validation']) ) { echo 'checked="checked"'; } ?> /> <?php _e('Use the jQuery validation', 'custom-field-template'); ?></label></p>
</td>
</tr>
<tr><td>
<?php
	if ( !isset($options['custom_field_template_before_list']) ) $options['custom_field_template_before_list'] = '<ul>';
	if ( !isset($options['custom_field_template_after_list']) ) $options['custom_field_template_after_list'] = '</ul>';
	if ( !isset($options['custom_field_template_before_value']) ) $options['custom_field_template_before_value'] = '<li>';
	if ( !isset($options['custom_field_template_after_value']) ) $options['custom_field_template_after_value'] = '</li>';
?>
<p><label for="custom_field_template_before_list"><?php _e('Text to place before every list which is called by the cft shortcode', 'custom-field-template'); ?></label>:<br />
<input type="text" name="custom_field_template_before_list" id="custom_field_template_before_list" value="<?php echo esc_attr(stripcslashes($options['custom_field_template_before_list'])); ?>" /></p>
<p><label for="custom_field_template_after_list"><?php _e('Text to place after every list which is called by the cft shortcode', 'custom-field-template'); ?></label>:<br />
<input type="text" name="custom_field_template_after_list" id="custom_field_template_after_list" value="<?php echo esc_attr(stripcslashes($options['custom_field_template_after_list'])); ?>" /></p>
<p><label for="custom_field_template_before_value"><?php _e('Text to place before every value which is called by the cft shortcode', 'custom-field-template'); ?></label>:<br />
<input type="text" name="custom_field_template_before_value" id="custom_field_template_before_value" value="<?php echo esc_attr(stripcslashes($options['custom_field_template_before_value'])); ?>" /></p>
<p><label for="custom_field_template_after_value"><?php _e('Text to place after every value which is called by the cft shortcode', 'custom-field-template'); ?></label>:<br />
<input type="text" name="custom_field_template_after_value" id="custom_field_template_after_value" value="<?php echo esc_attr(stripcslashes($options['custom_field_template_after_value'])); ?>" /></p>
</td>
</tr>
<tr><td>
<p><label for="custom_field_template_disable_ad"><?php _e('In case that you would like to hide the advertisement right column.', 'custom-field-template'); ?>:<br />
<input type="checkbox" name="custom_field_template_disable_ad" id="custom_field_template_disable_ad" value="1" <?php if ( !empty($options['custom_field_template_disable_ad']) ) { echo 'checked="checked"'; } ?> /> <?php _e('I want to use a wider screen.', 'custom-field-template'); ?></label></p>
</td>
</tr>
<tr><td>
<p><input type="submit" name="custom_field_template_global_settings_submit" value="<?php _e('Update Options &raquo;', 'custom-field-template'); ?>" class="button-primary" /></p>
</td></tr>
</tbody>
</table>
</form>
</div>
</div>

<div class="postbox closed">
<div class="handlediv" title="<?php _e('Click to toggle', 'custom-field-template'); ?>"><br /></div>
<h3><?php _e('ADMIN CSS', 'custom-field-template'); ?></h3>
<div class="inside">
<form method="post">
<table class="form-table" style="margin-bottom:5px;">
<tbody>
<tr><td>
<p><textarea name="custom_field_template_css" class="large-text resizable" id="custom_field_template_css" rows="10" cols="80"><?php if ( isset($options['css']) ) echo htmlspecialchars(stripcslashes($options['css'])); ?></textarea></p>
</td></tr>
<tr><td>
<p><input type="submit" name="custom_field_template_css_submit" value="<?php _e('Update Options &raquo;', 'custom-field-template'); ?>" class="button-primary" /></p>
</td></tr>
</tbody>
</table>
</form>
</div>
</div>

<div class="postbox closed">
<div class="handlediv" title="<?php _e('Click to toggle', 'custom-field-template'); ?>"><br /></div>
<h3><?php _e('[cft] and [cftsearch] Shortcode Format', 'custom-field-template'); ?></h3>
<div class="inside">
<form method="post">
<p><?php _e('For [cft], [key] will be converted into the value of [key].', 'custom-field-template'); ?><br />
<?php _e('For [cftsearch], [key] will be converted into the input field.', 'custom-field-template'); ?></p>
<table class="form-table" style="margin-bottom:5px;">
<tbody>
<?php
	if ( isset($options['shortcode_format']) ) $count = count($options['shortcode_format']);
	else $count = 0;
	for ($i=0;$i<$count+1;$i++) :
?>
<tr><th><strong>FORMAT #<?php echo $i; ?></strong></th></tr>
<tr><td>
<p><textarea name="custom_field_template_shortcode_format[<?php echo $i; ?>]" class="large-text resizable" rows="10" cols="80"><?php if ( isset($options['shortcode_format'][$i]) ) echo htmlspecialchars(stripcslashes($options['shortcode_format'][$i])); ?></textarea></p>
<p><label><input type="checkbox" name="custom_field_template_shortcode_format_use_php[<?php echo $i; ?>]" value="1" <?php if ( !empty($options['shortcode_format_use_php'][$i]) ) { echo ' checked="checked"'; } ?> /> <?php _e('Use PHP', 'custom-field-template'); ?></label></p>
</td></tr>
<?php
	endfor;
?>
<tr><td>
<p><input type="submit" name="custom_field_template_shortcode_format_submit" value="<?php _e('Update Options &raquo;', 'custom-field-template'); ?>" class="button-primary" /></p>
</td></tr>
</tbody>
</table>
</form>
</div>
</div>

<div class="postbox closed">
<div class="handlediv" title="<?php _e('Click to toggle', 'custom-field-template'); ?>"><br /></div>
<h3><?php _e('PHP CODE (Experimental Option)', 'custom-field-template'); ?></h3>
<div class="inside">
<form method="post" onsubmit="return confirm('<?php _e('Are you sure to save PHP codes? Please do it at your own risk.', 'custom-field-template'); ?>');">
<dl><dt><?php _e('For `text` and `textarea`, you must set $value as an string.', 'custom-field-template'); ?><br />
ex. `text` and `textarea`:</dt><dd>$value = 'Yes we can.';</dd></dl>
<dl><dt><?php _e('For `checkbox`, `radio`, and `select`, you must set $values as an array.', 'custom-field-template'); ?><br />
ex. `radio` and `select`:</dt><dd>$values = array('dog', 'cat', 'monkey'); $default = 'cat';</dd>
<dt>ex. `checkbox`:</dt><dd>$values = array('dog', 'cat', 'monkey'); $defaults = array('dog', 'cat');</dd></dl>
<table class="form-table" style="margin-bottom:5px;">
<tbody>
<?php
	if ( isset($options['php']) ) $count = count($options['php']);
	else $count = 0;
	for ($i=0;$i<$count+1;$i++) :
?>
<tr><th><strong>CODE #<?php echo $i; ?></strong></th></tr>
<tr><td>
<p><textarea name="custom_field_template_php[]" class="large-text resizable" rows="10" cols="80"><?php if ( isset($options['php'][$i]) ) echo htmlspecialchars(stripcslashes($options['php'][$i])); ?></textarea></p>
</td></tr>
<?php
	endfor;
?>
<tr><td>
<p><input type="submit" name="custom_field_template_php_submit" value="<?php _e('Update Options &raquo;', 'custom-field-template'); ?>" class="button-primary" /></p>
</td></tr>
</tbody>
</table>
</form>
</div>
</div>

<div class="postbox closed">
<div class="handlediv" title="<?php _e('Click to toggle', 'custom-field-template'); ?>"><br /></div>
<h3><?php _e('Auto Hook of `the_content()` (Experimental Option)', 'custom-field-template'); ?></h3>
<div class="inside">
<form method="post">
<table class="form-table" style="margin-bottom:5px;">
<tbody>
<?php
	if ( isset($options['hook']) ) $count = count($options['hook']);
	else $count = 0;
	for ($i=0;$i<$count+1;$i++) :
?>
<tr><th><strong>HOOK #<?php echo $i; ?></strong></th></tr>
<tr><td>
<p><label for="custom_field_template_hook_position[<?php echo $i; ?>]"><?php echo sprintf(__('Position', 'custom-field-template'), $i); ?></label>:<br />
<label><input type="radio" name="custom_field_template_hook_position[<?php echo $i; ?>]" value="1" <?php if( isset($options['hook'][$i]['position']) && $options['hook'][$i]['position']==1 ) echo ' checked="checked"'; ?> /> <?php _e('Before the content', 'custom-field-template'); ?></label> 
<label><input type="radio" name="custom_field_template_hook_position[<?php echo $i; ?>]" value="0" <?php if( isset($options['hook'][$i]['position']) && $options['hook'][$i]['position']==0) echo ' checked="checked"'; ?> /> <?php _e('After the content', 'custom-field-template'); ?></label> 
<label><input type="radio" name="custom_field_template_hook_position[<?php echo $i; ?>]" value="2" <?php if( isset($options['hook'][$i]['position']) && $options['hook'][$i]['position']==2) echo ' checked="checked"'; ?> /> <?php echo sprintf(__('Inside the content ([cfthook hook=%d])', 'custom-field-template'), $i); ?></label>
</p>
<p><label for="custom_field_template_hook_post_type[<?php echo $i; ?>]"><?php echo sprintf(__('Post Type', 'custom-field-template'), $i); ?></label>:<br />
<label><input type="radio" name="custom_field_template_hook_post_type[<?php echo $i; ?>]" id="custom_field_template_hook_post_type[<?php echo $i; ?>]" value=""<?php if ( !isset($options['hook'][$i]['post_type']) ) :  echo ' checked="checked"'; endif; ?> /> <?php _e('Both', 'custom-field-template'); ?></label> 
<label><input type="radio" name="custom_field_template_hook_post_type[<?php echo $i; ?>]" id="custom_field_template_hook_post_type[<?php echo $i; ?>]" value="post"<?php if ( isset($options['hook'][$i]['post_type']) && $options['hook'][$i]['post_type']=='post') : echo ' checked="checked"'; endif; ?> /> <?php _e('Post', 'custom-field-template'); ?></label> 
<label><input type="radio" name="custom_field_template_hook_post_type[<?php echo $i; ?>]" id="custom_field_template_hook_post_type[<?php echo $i; ?>]" value="page"<?php if ( isset($options['hook'][$i]['post_type']) && $options['hook'][$i]['post_type']=='page') : echo ' checked="checked"'; endif; ?> /> <?php _e('Page', 'custom-field-template'); ?></label></p>
<p><label for="custom_field_template_hook_custom_post_type[<?php echo $i; ?>]"><?php echo sprintf(__('Custom Post Type (comma-deliminated)', 'custom-field-template'), $i); ?></label>:<br />
<input type="text" name="custom_field_template_hook_custom_post_type[<?php echo $i; ?>]" id="custom_field_template_hook_custom_post_type[<?php echo $i; ?>]" value="<?php if ( isset($options['hook'][$i]['custom_post_type']) ) echo esc_attr(stripcslashes($options['hook'][$i]['custom_post_type'])); ?>" size="80" /></p>
<p><label for="custom_field_template_hook_category[<?php echo $i; ?>]"><?php echo sprintf(__('Category ID (comma-deliminated)', 'custom-field-template'), $i); ?></label>:<br />
<input type="text" name="custom_field_template_hook_category[<?php echo $i; ?>]" id="custom_field_template_hook_category[<?php echo $i; ?>]" value="<?php if ( isset($options['hook'][$i]['category']) ) echo esc_attr(stripcslashes($options['hook'][$i]['category'])); ?>" size="80" /></p>
<p><label for="custom_field_template_hook_content[<?php echo $i; ?>]"><?php echo sprintf(__('Content', 'custom-field-template'), $i); ?></label>:<br /><textarea name="custom_field_template_hook_content[<?php echo $i; ?>]" class="large-text resizable" rows="5" cols="80"><?php if ( isset($options['hook'][$i]['content']) ) echo htmlspecialchars(stripcslashes($options['hook'][$i]['content'])); ?></textarea></p>
<p><label><input type="checkbox" name="custom_field_template_hook_use_php[<?php echo $i; ?>]" id="custom_field_template_hook_use_php[<?php echo $i; ?>]" value="1" <?php if ( !empty($options['hook'][$i]['use_php']) ) { echo ' checked="checked"'; } ?> /> <?php _e('Use PHP', 'custom-field-template'); ?></label></p>
<p><label><input type="checkbox" name="custom_field_template_hook_feed[<?php echo $i; ?>]" id="custom_field_template_hook_feed[<?php echo $i; ?>]" value="1" <?php if ( !empty($options['hook'][$i]['feed']) ) { echo ' checked="checked"'; } ?> /> <?php _e('Apply to feeds', 'custom-field-template'); ?></label></p>
<p><label><input type="checkbox" name="custom_field_template_hook_excerpt[<?php echo $i; ?>]" id="custom_field_template_hook_excerpt[<?php echo $i; ?>]" value="1" <?php if ( !empty($options['hook'][$i]['excerpt']) ) { echo ' checked="checked"'; } ?> /> <?php _e('Apply also to excerpts', 'custom-field-template'); ?></label></p>
</td></tr>
<?php
	endfor;
?>
<tr><td>
<p><input type="submit" name="custom_field_template_hook_submit" value="<?php _e('Update Options &raquo;', 'custom-field-template'); ?>" class="button-primary" /></p>
</td></tr>
</tbody>
</table>
</form>
</div>
</div>

<div class="postbox closed">
<div class="handlediv" title="<?php _e('Click to toggle', 'custom-field-template'); ?>"><br /></div>
<h3><?php _e('Rebuild Value Counts', 'custom-field-template'); ?></h3>
<div class="inside">
<form method="post" onsubmit="return confirm('<?php _e('Are you sure to rebuild all value counts?', 'custom-field-template'); ?>');">
<table class="form-table" style="margin-bottom:5px;">
<tbody>
<tr><td>
<p><?php _e('Value Counts are used for temporarily saving how many values in each key. Set `valueCount = true` into fields.', 'custom-field-template'); ?></p>
<p>global $custom_field_template;<br />
$value_count = $custom_field_template->get_value_count();<br />
echo $value_count[$meta_key][$meta_value];</p>
<p><input type="submit" name="custom_field_template_rebuild_value_counts_submit" value="<?php _e('Rebuild Value Counts &raquo;', 'custom-field-template'); ?>" class="button-primary" /></p>
</td></tr>
</tbody>
</table>
</form>
</div>
</div>

<!--
<div class="postbox closed">
<div class="handlediv" title="<?php _e('Click to toggle', 'custom-field-template'); ?>"><br /></div>
<h3><?php _e('Rebuild Tags', 'custom-field-template'); ?></h3>
<div class="inside">
<form method="post" onsubmit="return confirm('<?php _e('Are you sure to rebuild tags?', 'custom-field-template'); ?>');">
<table class="form-table" style="margin-bottom:5px;">
<tbody>
<tr><td>
<p><input type="submit" name="custom_field_template_rebuild_tags_submit" value="<?php _e('Rebuild Tags &raquo;', 'custom-field-template'); ?>" class="button-primary" /></p>
</td></tr>
</tbody>
</table>
</form>
</div>
</div>
//-->

<div class="postbox closed">
<div class="handlediv" title="<?php _e('Click to toggle', 'custom-field-template'); ?>"><br /></div>
<h3><?php _e('Option List', 'custom-field-template'); ?></h3>
<div class="inside">
ex.<br />
[Plan]<br />
type = textfield<br />
size = 35<br />
hideKey = true<br />

<table class="widefat" style="margin:10px 0 5px 0;">
<thead>
<tr>
<th>type</th><th>text or textfield</th><th>checkbox</th><th>radio</th><th>select</th><th>textarea</th><th>file</th>
</tr>
</thead>
<tbody>
<tr>
<th>hideKey</th><td>hideKey = true</td><td>hideKey = true</td><td>hideKey = true</td><td>hideKey = true</td><td>hideKey = true</td><td>hideKey = true</td>
</tr>
<tr>
<th>label</th><td>label = ABC</td><td>label = DEF</td><td>label = GHI</td><td>label = JKL</td><td>label = MNO</td><td>label = PQR</td>
</tr>
<tr>
<th>size</th><td>size = 30</td><td></td><td></td><td></td><td></td><td>size = 30</td>
</tr>
<tr>
<th>value</th><td></td><td>value = apple # orange # banana</td><td>value = apple # orange # banana</td><td>value = apple # orange # banana</td><td></td>
<td></td>
</tr>
<tr>
<th>valueLabel</th><td></td><td>valueLabel = apples # oranges # bananas</td><td>valueLabel = apples # oranges # bananas</td><td>valueLabel = apples # oranges # bananas</td><td></td>
<td></td>
</tr>
<tr>
<th>default</th><td>default = orange</td><td>default = orange # banana</td><td>default = orange</td><td>default = orange</td><td>default = orange</td><td></td>
</tr>
<tr>
<th>clearButton</th><td></td><td></td><td>clearButton = true</td><td></td><td></td><td></td>
</tr>
<tr>
<th>selectLabel</th><td></td><td></td><td></td><td>selectLabel = Select a fruit</td><td></td><td></td>
</tr>
<tr>
<th>rows</th><td></td><td></td><td></td><td></td><td>rows = 4</td><td></td>
</tr>
<tr>
<th>cols</th><td></td><td></td><td></td><td></td><td>cols = 40</td><td></td>
</tr>
<tr>
<th>wrap</th><td></td><td></td><td></td><td></td><td>wrap = off</td><td></td>
</tr>
<tr>
<th>tinyMCE</th><td></td><td></td><td></td><td></td><td>tinyMCE = true</td><td></td>
</tr>
<tr>
<th>htmlEditor</th><td></td><td></td><td></td><td></td><td>htmlEditor = true</td><td></td>
</tr>
<tr>
<th>date</th><td>date = true</td><td></td><td></td><td></td><td></td><td></td>
</tr>
<tr>
<th>dateFirstDayOfWeek</th><td>dateFirstDayOfWeek = 0</td><td></td><td></td><td></td><td></td><td></td>
</tr>
<tr>
<th>dateFormat</th><td>dateFormat = yyyy/mm/dd</td><td></td><td></td><td></td><td></td><td></td>
</tr>
<tr>
<th>startDate</th><td>startDate = '1970/01/01'</td><td></td><td></td><td></td><td></td><td></td>
</tr>
<tr>
<th>endDate</th><td>endDate = (new Date()).asString()</td><td></td><td></td><td></td><td></td><td></td>
</tr>
<tr>
<th>readOnly</th><td>readOnly = true</td><td></td><td></td><td></td><td></td><td></td>
</tr>
<tr>
<th>mediaButton</th><td></td><td></td><td></td><td></td><td>mediaButton = true</td><td></td>
</tr>
<tr>
<th>mediaOffImage</th><td></td><td></td><td></td><td></td><td>mediaOffImage = true</td><td></td>
</tr>
<tr>
<th>mediaOffVideo</th><td></td><td></td><td></td><td></td><td>mediaOffVideo = true</td><td></td>
</tr>
<tr>
<th>mediaOffAudio</th><td></td><td></td><td></td><td></td><td>mediaOffAudio = true</td><td></td>
</tr>
<tr>
<th>mediaOffMedia</th><td></td><td></td><td></td><td></td><td>mediaOffMedia = true</td><td></td>
</tr>
<tr>
<th>relation</th><td></td><td></td><td></td><td></td><td></td><td>relation = true</td>
</tr>
<tr>
<th>mediaLibrary</th><td></td><td></td><td></td><td></td><td></td><td>mediaLibrary = true</td>
</tr>
<tr>
<th>mediaPicker</th><td></td><td></td><td></td><td></td><td></td><td>mediaPicker = true</td>
</tr>
<tr>
<th>mediaRemove</th><td></td><td></td><td></td><td></td><td></td><td>mediaRemove = true</td>
</tr>
<tr>
<th>code</th><td>code = 0</td><td>code = 0</td><td>code = 0</td><td>code = 0</td><td>code = 0</td><td></td>
</tr>
<tr>
<th>editCode</th><td>editCode = 0</td><td>editCode = 0</td><td>editCode = 0</td><td>editCode = 0</td><td>editCode = 0</td><td>editCode = 0</td>
</tr>
<tr>
<th>level</th><td>level = 1</td><td>level = 3</td><td>level = 5</td><td>level = 7</td><td>level = 9</td><td>level = 10</td>
</tr>
<tr>
<th>insertTag</th><td>insertTag = true</td><td>insertTag = true</td><td>insertTag = true</td><td>insertTag = true</td><td>insertTag = true</td><td></td>
</tr>
<tr>
<th>tagName</th><td>tagName = movie_tag</td><td>tagName = book_tag</td><td>tagName = img_tag</td><td>tagName = dvd_tag</td><td>tagName = bd_tag</td><td></td>
</tr>
<tr>
<th>output</th><td>output = true</td><td>output = true</td><td>output = true</td><td>output = true</td><td>output = true</td><td></td>
</tr>
<tr>
<th>outputCode</th><td>outputCode = 0</td><td>outputCode = 0</td><td>outputCode = 0</td><td>outputCode = 0</td><td>outputCode = 0</td><td></td>
</tr>
<tr>
<th>outputNone</th><td>outputNone = No Data</td><td>outputNone = No Data</td><td>outputNone = No Data</td><td>outputNone = No Data</td><td>outputNone = No Data</td><td></td>
</tr>
<tr>
<th>singleList</th><td>singleList = true</td><td>singleList = true</td><td>singleList = true</td><td>singleList = true</td><td>singleList = true</td><td></td>
</tr>
<tr>
<th>shortCode</th><td>shortCode = true</td><td>shortCode = true</td><td>shortCode = true</td><td>shortCode = true</td><td>shortCode = true</td><td></td>
</tr>
<tr>
<th>multiple</th><td>multiple = true</td><td></td><td>multiple = true</td><td>multiple = true</td><td>multiple = true</td><td>multiple = true</td>
</tr>
<tr>
<th>startNum</th><td>startNum = 5</td><td></td><td>startNum = 5</td><td>startNum = 5</td><td>startNum = 5</td><td>startNum = 5</td>
</tr>
<tr>
<th>endNum</th><td>endNum = 10</td><td></td><td>endNum = 10</td><td>endNum = 10</td><td>endNum = 10</td><td>endNum = 10</td>
</tr>
<tr>
<th>multipleButton</th><td>multipleButton = true</td><td></td><td>multipleButton = true</td><td>multipleButton = true</td><td>multipleButton = true</td><td>multipleButton = true</td>
</tr>
<tr>
<th>blank</th><td>blank = true</td><td>blank = true</td><td>blank = true</td><td>blank = true</td><td>blank = true</td><td>blank = true</td>
</tr>
<tr>
<th>sort</th><td>sort = asc</td><td>sort = desc</td><td>sort = asc</td><td>sort = desc</td><td>sort = asc</td><td></td>
</tr>
<tr>
<th>search</th><td>search = true</td><td>search = true</td><td>search = true</td><td>search = true</td><td>search = true</td>
</tr>
<tr>
<th>class</th><td>class = text</td><td>class = checkbox</td><td>class = radio</td><td>class = select</td><td>class = textarea</td><td>class = file</td>
</tr>
<tr>
<th>style</th><td>style = color:#FF0000;</td><td>style = color:#FF0000;</td><td>style = color:#FF0000;</td><td>style = color:#FF0000;</td><td>style = color:#FF0000;</td><td>style = color:#FF0000;</td>
</tr>
<tr>
<th>before</th><td>before = abcde</td><td></td><td>before = abcde</td><td>before = abcde</td><td>before = abcde</td><td>before = abcde</td>
</tr>
<tr>
<th>after</th><td>after = abcde</td><td></td><td>after = abcde</td><td>after = abcde</td><td>after = abcde</td><td>after = abcde</td>
</tr>
<tr>
<th>valueCount</th><td>valueCount = true</td><td>valueCount = true</td><td>valueCount = true</td><td>valueCount = true</td><td>valueCount = true</td><td></td>
</tr>
<tr>
<th>JavaScript Event Handlers</th><td>onclick = alert('ok');</td><td>onchange = alert('ok');</td><td>onchange = alert('ok');</td><td>onchange = alert('ok');</td><td>onfocus = alert('ok');</td><td></td>
</tr>
</tbody>
</table>
</div>
</div>

<div class="postbox closed">
<div class="handlediv" title="<?php _e('Click to toggle', 'custom-field-template'); ?>"><br /></div>
<h3><?php _e('Export Options', 'custom-field-template'); ?></h3>
<div class="inside">
<form method="post">
<table class="form-table" style="margin-bottom:5px;">
<tbody>
<tr><td>
<p><input type="submit" name="custom_field_template_export_options_submit" value="<?php _e('Export Options &raquo;', 'custom-field-template'); ?>" class="button-primary" /></p>
</td></tr>
</tbody>
</table>
</form>
</div>
</div>

<div class="postbox closed">
<div class="handlediv" title="<?php _e('Click to toggle', 'custom-field-template'); ?>"><br /></div>
<h3><?php _e('Import Options', 'custom-field-template'); ?></h3>
<div class="inside">
<form method="post" enctype="multipart/form-data" onsubmit="return confirm('<?php _e('Are you sure to import options? Options you set will be overwritten.', 'custom-field-template'); ?>');">
<table class="form-table" style="margin-bottom:5px;">
<tbody>
<tr><td>
<p><input type="file" name="cftfile" /> <input type="submit" name="custom_field_template_import_options_submit" value="<?php _e('Import Options &raquo;', 'custom-field-template'); ?>" class="button-primary" /></p>
</td></tr>
</tbody>
</table>
</form>
</div>
</div>

<div class="postbox closed">
<div class="handlediv" title="<?php _e('Click to toggle', 'custom-field-template'); ?>"><br /></div>
<h3><?php _e('Reset Options', 'custom-field-template'); ?></h3>
<div class="inside">
<form method="post" onsubmit="return confirm('<?php _e('Are you sure to reset options? Options you set will be reset to the default settings.', 'custom-field-template'); ?>');">
<table class="form-table" style="margin-bottom:5px;">
<tbody>
<tr><td>
<p><input type="submit" name="custom_field_template_reset_options_submit" value="<?php _e('Reset Options &raquo;', 'custom-field-template'); ?>" class="button-primary" /></p>
</td></tr>
</tbody>
</table>
</form>
</div>
</div>

<div class="postbox closed">
<div class="handlediv" title="<?php _e('Click to toggle', 'custom-field-template'); ?>"><br /></div>
<h3><?php _e('Delete Options', 'custom-field-template'); ?></h3>
<div class="inside">
<form method="post" onsubmit="return confirm('<?php _e('Are you sure to delete options? Options you set will be deleted.', 'custom-field-template'); ?>');">
<table class="form-table" style="margin-bottom:5px;">
<tbody>
<tr><td>
<p><input type="submit" name="custom_field_template_delete_options_submit" value="<?php _e('Delete Options &raquo;', 'custom-field-template'); ?>" class="button-primary" /></p>
</td></tr>
</tbody>
</table>
</form>
</div>
</div>
</div>

<?php if ( empty($options['custom_field_template_disable_ad']) ) : ?>
<div style="width:24%; float:right;">
<div class="postbox" style="min-width:200px;">
<div class="handlediv" title="<?php _e('Click to toggle', 'custom-field-template'); ?>"><br /></div>
<h3><?php _e('Donation', 'custom-field-template'); ?></h3>
<div class="inside">
<p><?php _e('If you liked this plugin, please make a donation via paypal! Any amount is welcome. Your support is much appreciated.', 'custom-field-template'); ?></p>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" style="text-align:center;" target="_blank">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="WN7Y2442JPRU6">
<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG_global.gif" border="0" name="submit" alt="PayPal">
</form>
</div>
</div>

<?php
	if ( $locale == 'ja' ) :
?>
<div class="postbox" style="min-width:200px;">
<div class="handlediv" title="<?php _e('Click to toggle', 'custom-field-template'); ?>"><br /></div>
<h3><?php _e('Custom Field Template Manual', 'custom-field-template'); ?></h3>
<div class="inside">
<p><?php _e('Do you have any trouble with the plugin setup? Please visit the following manual site.', 'custom-field-template'); ?></p>
<p style="text-align:center"><a href="http://ja.wpcft.com/" target="_blank"><?php _e('Custom Field Template Manual', 'custom-field-template'); ?></a></p>
</div>
</div>

<div class="postbox" style="min-width:200px;">
<div class="handlediv" title="<?php _e('Click to toggle', 'custom-field-template'); ?>"><br /></div>
<h3><?php _e('CMS x WP', 'custom-field-template'); ?></h3>
<div class="inside">
<p><?php _e('There are much more plugins which are useful for developing business websites such as membership sites or ec sites. You could totally treat WordPress as CMS by use of CMS x WP plugins.', 'custom-field-template'); ?></p>
<p style="text-align:center"><a href="http://www.cmswp.jp/" target="_blank"><img src="<?php echo get_option('siteurl') . '/' . PLUGINDIR . '/' . $plugin_dir . '/js/'; ?>cmswp.jpg" width="125" height="125" alt="CMSxWP" /></a><br /><a href="http://www.cmswp.jp/" target="_blank"><?php _e('WordPress plugin sales site: CMS x WP', 'custom-field-template'); ?></a></p>
</div>
</div>
<?php
	endif;
?>
</div>
<?php endif; ?>

<script type="text/javascript">
// <![CDATA[
<?php if ( version_compare( substr($wp_version, 0, 3), '2.7', '<' ) ) { ?>
jQuery('.postbox h3').prepend('<a class="togbox">+</a> ');
<?php } ?>
jQuery('.postbox div.handlediv').click( function() { jQuery(jQuery(this).parent().get(0)).toggleClass('closed'); } );
jQuery('.postbox h3').click( function() { jQuery(jQuery(this).parent().get(0)).toggleClass('closed'); } );
jQuery('.postbox.close-me').each(function(){
jQuery(this).addClass("closed");
});
//-->
</script>

</div>
<?php
	}
		
	function sanitize_name( $name ) {
		$name = sanitize_title( $name );
		$name = str_replace( '-', '_', $name );
		
		return $name;
	}
	
	function get_custom_fields( $id ) {
		$options = $this->get_custom_field_template_data();

		if ( empty($options['custom_fields'][$id]) )
			return null;
			
		$custom_fields = $this->parse_ini_str( $options['custom_fields'][$id]['content'], true );
		return $custom_fields;
	}
	
	function make_textfield( $name, $sid, $data, $post_id ) {
		$cftnum = $size = $default = $hideKey = $label = $code = $class = $style = $before = $after = $maxlength = $multipleButton = $date = $dateFirstDayOfWeek = $dateFormat = $startDate = $endDate = $readOnly = $onclick = $ondblclick = $onkeydown = $onkeypress = $onkeyup = $onmousedown = $onmouseup = $onmouseover = $onmouseout = $onmousemove = $onfocus = $onblur = $onchange = $onselect = '';
		$hide = $addfield = $out = $out_key = $out_value = '';
		extract($data);
		$options = $this->get_custom_field_template_data();

		$name = stripslashes($name);

		$title = $name;
		$name = $this->sanitize_name( $name );
		$name_id = preg_replace( '/%/', '', $name );

		if ( isset($code) && is_numeric($code) ) :
			eval(stripcslashes($options['php'][$code]));
		endif;
		
		if ( !isset($_REQUEST['default']) || (isset($_REQUEST['default']) && $_REQUEST['default'] != true) ) $_REQUEST['default'] = false;
		
		if( isset( $post_id ) && $post_id > 0 && $_REQUEST['default'] != true ) {
			$value = $this->get_post_meta( $post_id, $title, false );
			if ( !empty($value) && is_array($value) ) {
				$ct_value = count($value);
				$value = isset($value[ $cftnum ]) ? $value[ $cftnum ] : '';
			}
		} else {
			$value = stripslashes($default);
		}
		if ( empty($ct_value) ) :
			$ct_value = !empty($startNum) ? $startNum-1 : 1;
		endif;
		
		if ( isset($enforced_value) ) :
			$value = $enforced_value;
		endif;
				
		if ( isset($hideKey) && $hideKey == true ) $hide = ' class="hideKey"';
		if ( !empty($class) && $date == true ) $class = ' class="' . $class . ' datePicker"';
		elseif ( empty($class) && isset($date) && $date == true ) $class = ' class="datePicker"';
		elseif ( !empty($class) ) $class = ' class="' . $class . '"';
		if ( !empty($style) ) $style = ' style="' . $style . '"';
		if ( !empty($maxlength) ) $maxlength = ' maxlength="' . $maxlength . '"';
		if ( !empty($readOnly) ) $readOnly = ' readonly="readonly"';
		
		if ( !empty($label) && !empty($options['custom_field_template_replace_keys_by_labels']) )
			$title = stripcslashes($label);
		
		$event = array('onclick' => $onclick, 'ondblclick' => $ondblclick, 'onkeydown' => $onkeydown, 'onkeypress' => $onkeypress, 'onkeyup' => $onkeyup, 'onmousedown' => $onmousedown, 'onmouseup' => $onmouseup, 'onmouseover' => $onmouseover, 'onmouseout' => $onmouseout, 'onmousemove' => $onmousemove, 'onfocus' => $onfocus, 'onblur' => $onblur, 'onchange' => $onchange, 'onselect' => $onselect);
		$event_output = "";
		foreach($event as $key => $val) :
			if ( $val )
				$event_output .= " " . $key . '="' . stripcslashes(trim($val)) . '"';
		endforeach;
		
		if ( isset($multipleButton) && $multipleButton == true && $date != true && $ct_value == $cftnum ) :
			$addfield .= '<div style="margin-top:-1em;">';
			$addfield .= '<a href="#clear" onclick="jQuery(this).parent().parent().parent().clone().insertAfter(jQuery(this).parent().parent().parent()).find('."'input'".').val('."''".');jQuery(this).parent().css('."'visibility','hidden'".');jQuery(this).parent().prev().css('."'visibility','hidden'".'); return false;">' . __('Add New', 'custom-field-template') . '</a>';
			$addfield .= '</div>';
		endif;
				
		$out_key = '<span' . $hide . '><label for="' . $name_id . $sid . '_' . $cftnum . '">' . $title . '</label></span>'.$addfield;
		
		$out = 
			'<dl id="dl_' . $name_id . $sid . '_' . $cftnum . '" class="dl_text">' .
			'<dt>'.$out_key.'</dt>' .
			'<dd>';

		if ( !empty($label) && empty($options['custom_field_template_replace_keys_by_labels']) )
			$out_value .= '<p class="label">' . stripcslashes($label) . '</p>';
		$out_value .= trim($before).'<input id="' . $name_id . $sid . '_' . $cftnum . '" name="' . $name . '['. $sid . '][]" value="' . esc_attr(trim($value)) . '" type="text" size="' . $size . '"' . $class . $style . $maxlength . $event_output . $readOnly . ' />'.trim($after);
		
		if ( $date == true ) :
			$out_value .= '<script type="text/javascript">' . "\n" .
					'// <![CDATA[' . "\n";
			if ( is_numeric($dateFirstDayOfWeek) ) $out_value .= 'Date.firstDayOfWeek = ' . stripcslashes(trim($dateFirstDayOfWeek)) . ";\n";
			if ( $dateFormat ) $out_value .= 'Date.format = "' . stripcslashes(trim($dateFormat)) . '"' . ";\n";
			$out_value .=	'jQuery(document).ready(function() { jQuery(".datePicker").css("float", "left"); jQuery(".datePicker").datePicker({';
			if ( $startDate ) $out_value .= "startDate: " . stripcslashes(trim($startDate));
			if ( $startDate && $endDate ) $out_value .= ",";
			if ( $endDate ) $out_value .= "endDate: " . stripcslashes(trim($endDate)) . "";
			$out_value .= '}); });' . "\n" .
					'// ]]>' . "\n" .
					'</script>';
		endif;

		$out .= $out_value.'</dd></dl>'."\n";
			
		return array($out, $out_key, $out_value);
	}
	
	function make_checkbox( $name, $sid, $data, $post_id ) {
		$cftnum = $value = $valueLabel = $checked = $hideKey = $label = $code = $class = $style = $before = $after = $onclick = $ondblclick = $onkeydown = $onkeypress = $onkeyup = $onmousedown = $onmouseup = $onmouseover = $onmouseout = $onmousemove = $onfocus = $onblur = $onchange = $onselect = '';
		$hide = $addfield = $out = $out_key = $out_value = '';
		extract($data);
		$options = $this->get_custom_field_template_data();

		$name = stripslashes($name);

		$title = $name;
		$name = $this->sanitize_name( $name );
		$name_id = preg_replace( '/%/', '', $name );

		if ( !$value ) $value = "true";

		if ( !isset($_REQUEST['default']) || (isset($_REQUEST['default']) && $_REQUEST['default'] != true) ) $_REQUEST['default'] = false;

		if( isset( $post_id ) && $post_id > 0 && $_REQUEST['default'] != true ) {
			$selected = $this->get_post_meta( $post_id, $title );
			if ( $selected ) {
 				if ( in_array(stripcslashes($value), $selected) ) $checked = 'checked="checked"';
			}
		} else {
			if( $checked == true )  $checked = ' checked="checked"';
		}

		if ( $hideKey == true ) $hide = ' class="hideKey"';
		if ( !empty($class) ) $class = ' class="' . $class . '"';
		if ( !empty($style) ) $style = ' style="' . $style . '"';

		if ( !empty($label) && !empty($options['custom_field_template_replace_keys_by_labels']) )
			$title = stripcslashes($label);

		$event = array('onclick' => $onclick, 'ondblclick' => $ondblclick, 'onkeydown' => $onkeydown, 'onkeypress' => $onkeypress, 'onkeyup' => $onkeyup, 'onmousedown' => $onmousedown, 'onmouseup' => $onmouseup, 'onmouseover' => $onmouseover, 'onmouseout' => $onmouseout, 'onmousemove' => $onmousemove, 'onfocus' => $onfocus, 'onblur' => $onblur, 'onchange' => $onchange, 'onselect' => $onselect);
		$event_output = "";
		foreach($event as $key => $val) :
			if ( $val )
				$event_output .= " " . $key . '="' . stripcslashes(trim($val)) . '"';
		endforeach;

		$id = $name_id . '_' . $this->sanitize_name( $value ) . '_' . $sid . '_' . $cftnum;

		$out_key = '<span' . $hide . '>' . $title . '</span>';

		$out .= 
			'<dl id="dl_' . $id . '" class="dl_checkbox">' .
			'<dt>'.$out_key.'</dt>' .
			'<dd>';
		
		if ( !empty($label) && !$options['custom_field_template_replace_keys_by_labels'] && $cftnum == 0 )
			$out_value .= '<p class="label">' . stripcslashes($label) . '</p>';
		$out_value .=	'<label for="' . $id . '" class="selectit"><input id="' . $id . '" name="' . $name . '[' . $sid . '][' . $cftnum . ']" value="' . esc_attr(stripcslashes(trim($value))) . '"' . $checked . ' type="checkbox"' . $class . $style . $event_output . ' /> ';
		if ( $valueLabel )
			$out_value .= stripcslashes(trim($valueLabel));
		else
			$out_value .= stripcslashes(trim($value));
		$out_value .= '</label> ';

		$out .= $out_value.'</dd></dl>'."\n";
		
		return array($out, $out_key, $out_value);
	}
	
	function make_radio( $name, $sid, $data, $post_id ) {
		$cftnum = $values = $valueLabels = $clearButton = $default = $hideKey = $label = $code = $class = $style = $before = $after = $multipleButton = $onclick = $ondblclick = $onkeydown = $onkeypress = $onkeyup = $onmousedown = $onmouseup = $onmouseover = $onmouseout = $onmousemove = $onfocus = $onblur = $onchange = $onselect = '';
		$hide = $addfield = $out = $out_key = $out_value = '';
		extract($data);
		$options = $this->get_custom_field_template_data();

		$name = stripslashes($name);

		$title = $name;
		$name = $this->sanitize_name( $name );
		$name_id = preg_replace( '/%/', '', $name );

		if ( isset($code) && is_numeric($code) ) :
			eval(stripcslashes($options['php'][$code]));
			if ( !empty($valueLabel) && is_array($valueLabel) ) $valueLabels = $valueLabel;
		endif;
		
		if ( !isset($_REQUEST['default']) || (isset($_REQUEST['default']) && $_REQUEST['default'] != true) ) $_REQUEST['default'] = false;

		if( isset( $post_id ) && $post_id > 0 && $_REQUEST['default'] != true ) {
			$selected = $this->get_post_meta( $post_id, $title );
			$ct_value = count($selected);
			$selected = isset($selected[ $cftnum ]) ? $selected[ $cftnum ] : '';
		} else {
			$selected = stripslashes($default);
		}
		if ( empty($ct_value) ) :
			$ct_value = !empty($startNum) ? $startNum-1 : 1;
		endif;
		
		if ( $hideKey == true ) $hide = ' class="hideKey"';
		$class .= ' '.$name_id . $sid;
		if ( !empty($class) ) $class = ' class="' . trim($class) . '"';
		if ( !empty($style) ) $style = ' style="' . $style . '"';

		if ( !empty($label) && !empty($options['custom_field_template_replace_keys_by_labels']) )
			$title = stripcslashes($label);

		$event = array('onclick' => $onclick, 'ondblclick' => $ondblclick, 'onkeydown' => $onkeydown, 'onkeypress' => $onkeypress, 'onkeyup' => $onkeyup, 'onmousedown' => $onmousedown, 'onmouseup' => $onmouseup, 'onmouseover' => $onmouseover, 'onmouseout' => $onmouseout, 'onmousemove' => $onmousemove, 'onfocus' => $onfocus, 'onblur' => $onblur, 'onchange' => $onchange, 'onselect' => $onselect);
		$event_output = "";
		foreach($event as $key => $val) :
			if ( $val )
				$event_output .= " " . $key . '="' . stripcslashes(trim($val)) . '"';
		endforeach;

		if ( $multipleButton == true && $ct_value == $cftnum ) :
			$addfield .= '<div style="margin-top:-1em;">';
			$addfield .= '<a href="#clear" onclick="var tmp = jQuery(this).parent().parent().parent().clone().insertAfter(jQuery(this).parent().parent().parent());tmp.find('."'input'".').attr('."'checked',false".');if(tmp.find('."'input'".').attr('."'name'".').match(/\[([0-9]+)\]$/)) { matchval = RegExp.$1; matchval++;tmp.find('."'input'".').attr('."'name',".'tmp.find('."'input'".').attr('."'name'".').replace(/\[([0-9]+)\]$/, \'[\'+matchval+\']\'));}jQuery(this).parent().css('."'visibility','hidden'".');jQuery(this).parent().prev().css('."'visibility','hidden'".'); return false;">' . __('Add New', 'custom-field-template') . '</a>';
			$addfield .= '</div>';
		endif;
		
		$out_key = '<span' . $hide . '>' . $title . '</span>'.$addfield;

		if( $clearButton == true ) {
			$out_key .= '<div>';
			$out_key .= '<a href="#clear" onclick="jQuery(\'.'.$name_id . $sid.'\').attr(\'checked\', false); return false;">' . __('Clear', 'custom-field-template') . '</a>';
			$out_key .= '</div>';
		}	

		$out .= 
			'<dl id="dl_' . $name_id . $sid . '_' . $cftnum . '" class="dl_radio">' .
			'<dt>'.$out_key.'</dt>' .
			'<dd>';

		if ( !empty($label) && empty($options['custom_field_template_replace_keys_by_labels']) )
			$out_value .= '<p class="label">' . stripcslashes($label) . '</p>';
		$i = 0;
		
		$out_value .= trim($before).'<input name="' . $name . '[' . $sid . '][' . $cftnum . ']" value="" type="hidden" />';

		if ( is_array($values) ) :
			foreach( $values as $val ) {
				$value_id = preg_replace( '/%/', '', $this->sanitize_name( $val ) );
				$id = $name_id . '_' . $value_id . '_' . $sid . '_' . $cftnum;
			
				$checked = ( stripcslashes(trim( $val )) == trim( $selected ) ) ? 'checked="checked"' : '';
			
				$out_value .=	
					'<label for="' . $id . '" class="selectit"><input id="' . $id . '" name="' . $name . '[' . $sid . '][' . $cftnum . ']" value="' . esc_attr(trim(stripcslashes($val))) . '" ' . $checked . ' type="radio"' . $class . $style . $event_output . ' /> ';
				if ( isset($valueLabels[$i]) )
					$out_value .= stripcslashes(trim($valueLabels[$i]));
				else
					$out_value .= stripcslashes(trim($val));
				$out_value .= '</label> ';
				$i++;
			}
		endif;
		$out_value .= trim($after);
		$out .= $out_value.'</dd></dl>'."\n";
		
		return array($out, $out_key, $out_value);
	}
	
	function make_select( $name, $sid, $data, $post_id ) {
		$cftnum = $values = $valueLabels = $default = $hideKey = $label = $code = $class = $style = $before = $after = $selectLabel = $multipleButton = $onclick = $ondblclick = $onkeydown = $onkeypress = $onkeyup = $onmousedown = $onmouseup = $onmouseover = $onmouseout = $onmousemove = $onfocus = $onblur = $onchange = $onselect = '';
		$hide = $addfield = $out = $out_key = $out_value = '';
		extract($data);
		$options = $this->get_custom_field_template_data();

		$name = stripslashes($name);

		$title = $name;
		$name = $this->sanitize_name( $name );
		$name_id = preg_replace( '/%/', '', $name );

		if ( isset($code) && is_numeric($code) ) :
			eval(stripcslashes($options['php'][$code]));
			if ( !empty($valueLabel) && is_array($valueLabel) ) $valueLabels = $valueLabel;
		endif;
	
		if ( !isset($_REQUEST['default']) || (isset($_REQUEST['default']) && $_REQUEST['default'] != true) ) $_REQUEST['default'] = false;

		if( isset( $post_id ) && $post_id > 0 && $_REQUEST['default'] != true ) {
			$selected = $this->get_post_meta( $post_id, $title );
			$ct_value = count($selected);
			$selected = isset($selected[ $cftnum ]) ? $selected[ $cftnum ] : '';
		} else {
			$selected = stripslashes($default);
		}
		if ( empty($ct_value) ) :
			$ct_value = !empty($startNum) ? $startNum-1 : 1;
		endif;
		
		if ( $hideKey == true ) $hide = ' class="hideKey"';
		if ( !empty($class) ) $class = ' class="' . $class . '"';
		if ( !empty($style) ) $style = ' style="' . $style . '"';

		if ( !empty($label) && !empty($options['custom_field_template_replace_keys_by_labels']) )
			$title = stripcslashes($label);

		$event = array('onclick' => $onclick, 'ondblclick' => $ondblclick, 'onkeydown' => $onkeydown, 'onkeypress' => $onkeypress, 'onkeyup' => $onkeyup, 'onmousedown' => $onmousedown, 'onmouseup' => $onmouseup, 'onmouseover' => $onmouseover, 'onmouseout' => $onmouseout, 'onmousemove' => $onmousemove, 'onfocus' => $onfocus, 'onblur' => $onblur, 'onchange' => $onchange, 'onselect' => $onselect);
		$event_output = "";
		foreach($event as $key => $val) :
			if ( $val )
				$event_output .= " " . $key . '="' . stripcslashes(trim($val)) . '"';
		endforeach;
		
		if ( $multipleButton == true && $ct_value == $cftnum ) :
			$addfield .= '<div style="margin-top:-1em;">';
			$addfield .= '<a href="#clear" onclick="jQuery(this).parent().parent().parent().clone().insertAfter(jQuery(this).parent().parent().parent()).find('."'select'".').val('."''".');jQuery(this).parent().css('."'visibility','hidden'".');jQuery(this).parent().prev().css('."'visibility','hidden'".'); return false;">' . __('Add New', 'custom-field-template') . '</a>';
			$addfield .= '</div>';
		endif;
		
		$out_key = '<span' . $hide . '><label for="' . $name_id . $sid . '_' . $cftnum . '">' . $title . '</label></span>'.$addfield;

		$out .= 
			'<dl id="dl_' . $name_id . $sid . '_' . $cftnum . '" class="dl_select">' .
			'<dt>'.$out_key.'</dt>' .
			'<dd>';
			
		if ( !empty($label) && !$options['custom_field_template_replace_keys_by_labels'] )
			$out_value .= '<p class="label">' . stripcslashes($label) . '</p>';
		$out_value .=	trim($before).'<select id="' . $name_id . $sid . '_' . $cftnum . '" name="' . $name . '[' . $sid . '][]"' . $class . $style . $event_output . '>';
		
		if ( $selectLabel )
			$out_value .= '<option value="">' . stripcslashes(trim($selectLabel)) . '</option>';
		else
			$out_value .= '<option value="">' . __('Select', 'custom-field-template') . '</option>';
		
		$i = 0;
		if ( is_array($values) ) :
			foreach( $values as $val ) {
				$checked = ( stripcslashes(trim( $val )) == trim( $selected ) ) ? 'selected="selected"' : '';
		
				$out_value .=	'<option value="' . esc_attr(stripcslashes(trim($val))) . '" ' . $checked . '>';
				if ( isset($valueLabels[$i]) )
					$out_value .= stripcslashes(trim($valueLabels[$i]));
				else
					$out_value .= stripcslashes(trim($val));
				$out_value .= '</option>';
				$i++;
			}
		endif;
		$out_value .= '</select>'.trim($after);
		$out .= $out_value.'</dd></dl>'."\n";
		
		return array($out, $out_key, $out_value);
	}
	
	function make_textarea( $name, $sid, $data, $post_id ) {
		$cftnum = $rows = $cols = $tinyMCE = $htmlEditor = $mediaButton = $default = $hideKey = $label = $code = $class = $style = $wrap = $before = $after = $multipleButton = $mediaOffMedia = $mediaOffImage = $mediaOffVideo = $mediaOffAudio = $onclick = $ondblclick = $onkeydown = $onkeypress = $onkeyup = $onmousedown = $onmouseup = $onmouseover = $onmouseout = $onmousemove = $onfocus = $onblur = $onchange = $onselect = '';
		$hide = $addfield = $out = $out_key = $out_value = $media = $editorcontainer_class = $quicktags_hide = '';
		extract($data);
		$options = $this->get_custom_field_template_data();

		global $wp_version;

		$name = stripslashes($name);

		$title = $name;
		$name = $this->sanitize_name( $name );
		$name_id = preg_replace( '/%/', '', $name );
		
		if ( is_numeric($code) ) :
			eval(stripcslashes($options['php'][$code]));
		endif;
		
		if ( !isset($_REQUEST['default']) || (isset($_REQUEST['default']) && $_REQUEST['default'] != true) ) $_REQUEST['default'] = false;

		if( isset( $post_id ) && $post_id > 0 && $_REQUEST['default'] != true ) {
			$value = $this->get_post_meta( $post_id, $title );
			if ( !empty($value) && is_array($value) ) {
				$ct_value = count($value);
				$value = isset($value[ $cftnum ]) ? $value[ $cftnum ] : '';
			}
		} else {
			$value = stripslashes($default);
		}

		if ( empty($ct_value) ) :
			$ct_value = !empty($startNum) ? $startNum-1 : 1;
		endif;
		
		$rand = rand();
		$switch = '';
		$textarea_id = sha1($name . $rand).rand(0,9);

		if( $tinyMCE == true ) {
			$out_value = '<script type="text/javascript">' . "\n" .
					'// <![CDATA[' . "\n" .
					'jQuery(document).ready(function() {if ( typeof tinyMCE != "undefined" ) {' . "\n";
					
			if ( substr($wp_version, 0, 3) < '3.3' ) :
				$load_tinyMCE = 'tinyMCE.execCommand('."'mceAddControl'".', false, "'. $textarea_id . '");';
				$editorcontainer_class = ' class="editorcontainer"';
			elseif ( substr($wp_version, 0, 3) < '3.9' ) :
				$load_tinyMCE = 'var ed = new tinyMCE.Editor("'. $textarea_id . '", tinyMCEPreInit.mceInit["content"]); ed.render();';
				$editorcontainer_class = ' class="wp-editor-container"';
			else :
				$load_tinyMCE = '';
				if ( wp_default_editor() == 'html' ) $load_tinyMCE .= 'tinyMCE.init({"convert_urls": false, "relative_urls": false, "remove_script_host": false});';
				$load_tinyMCE .= 'tinyMCE.execCommand('."'mceAddEditor'".', false, "'. $textarea_id . '");';
				$editorcontainer_class = ' class="wp-editor-container"';
			endif;
			if ( !empty($options['custom_field_template_use_wpautop']) ) :
				$out_value .=	'document.getElementById("'. $textarea_id . '").value = document.getElementById("'. $textarea_id . '").value; '.$load_tinyMCE.' tinyMCEID.push("'. $textarea_id . '");' . "\n";
			else:
				$out_value .=	'document.getElementById("'. $textarea_id . '").value = switchEditors.wpautop(document.getElementById("'. $textarea_id . '").value); '.$load_tinyMCE.' tinyMCEID.push("'. $textarea_id . '");' . "\n";
			endif;
			$out_value .= '}});' . "\n";
			$out_value .= '// ]]>' . "\n" . '</script>';
		}
		
		if ( substr($wp_version, 0, 3) >= '2.5' ) {

		if ( !strstr($_SERVER['REQUEST_URI'], 'wp-admin/edit.php') && !strstr($_SERVER['REQUEST_URI'], 'wp-admin/edit-pages.php')  ) {

			if ( $mediaButton == true ) :
				$media_upload_iframe_src = "media-upload.php";
				
				if ( substr($wp_version, 0, 3) < '3.3' ) :
					if ( !$mediaOffImage ) :
						$image_upload_iframe_src = apply_filters('image_upload_iframe_src', "$media_upload_iframe_src?type=image");
						$image_title = __('Add an Image');
						$media .= "<a href=\"{$image_upload_iframe_src}&TB_iframe=true\" id=\"add_image{$rand}\" title='$image_title' onclick=\"focusTextArea('".$textarea_id."'); jQuery(this).attr('href',jQuery(this).attr('href').replace('\?','?post_id='+jQuery('#post_ID').val())); return thickbox(this);\"><img src='images/media-button-image.gif' alt='$image_title' /></a> ";
					endif;
					if ( !$mediaOffVideo ) :
						$video_upload_iframe_src = apply_filters('video_upload_iframe_src', "$media_upload_iframe_src?type=video");
						$video_title = __('Add Video');
						$media .= "<a href=\"{$video_upload_iframe_src}&amp;TB_iframe=true\" id=\"add_video{$rand}\" title='$video_title' onclick=\"focusTextArea('".$textarea_id."'); jQuery(this).attr('href',jQuery(this).attr('href').replace('\?','?post_id='+jQuery('#post_ID').val())); return thickbox(this);\"><img src='images/media-button-video.gif' alt='$video_title' /></a> ";
					endif;
					if ( !$mediaOffAudio ) :
						$audio_upload_iframe_src = apply_filters('audio_upload_iframe_src', "$media_upload_iframe_src?type=audio");
						$audio_title = __('Add Audio');
						$media .= "<a href=\"{$audio_upload_iframe_src}&amp;TB_iframe=true\" id=\"add_audio{$rand}\" title='$audio_title' onclick=\"focusTextArea('".$textarea_id."'); jQuery(this).attr('href',jQuery(this).attr('href').replace('\?','?post_id='+jQuery('#post_ID').val())); return thickbox(this);\"><img src='images/media-button-music.gif' alt='$audio_title' /></a> ";
					endif;
					if ( !$mediaOffMedia ) :
						$media_title = __('Add Media');
						$media .= "<a href=\"{$media_upload_iframe_src}?TB_iframe=true\" id=\"add_media{$rand}\" title='$media_title' onclick=\"focusTextArea('".$textarea_id."'); jQuery(this).attr('href',jQuery(this).attr('href').replace('\?','?post_id='+jQuery('#post_ID').val())); return thickbox(this);\"><img src='images/media-button-other.gif' alt='$media_title' /></a>";
					endif;
				else :
					$media_title = __('Add Media');
					$media .= "<a href=\"{$media_upload_iframe_src}?TB_iframe=true\" id=\"add_media{$rand}\" title='$media_title' onclick=\"focusTextArea('".$textarea_id."'); jQuery(this).attr('href',jQuery(this).attr('href').replace('\?','?post_id='+jQuery('#post_ID').val())); return thickbox(this);\"><img src='images/media-button.png' alt='$media_title' /></a>";
				endif;
			endif;

			$switch = '<div>';
			if( $tinyMCE == true && user_can_richedit() ) {
				$switch .= '<a href="#toggle" onclick="switchMode(jQuery(this).parent().parent().parent().find(\'textarea\').attr(\'id\')); return false;">' . __('Toggle', 'custom-field-template') . '</a>';
			}
			$switch .= '</div>';
		}

		}
				
		if ( $hideKey == true ) $hide = ' class="hideKey"';
		$content_class = ' class="';
		if ( $htmlEditor == true || $tinyMCE == true ) :
			if ( substr($wp_version, 0, 3) < '3.3' ) :
				$content_class .= 'content';
			else :
				$content_class .= 'wp-editor-area';
			endif;
		endif;
		if ( !empty($class) ) $content_class .= ' ' . $class;
		$content_class .= '"';
		if ( !empty($style) ) $style = ' style="' . $style . '"';
		if ( !empty($wrap) && ($wrap == 'soft' || $wrap == 'hard' || $wrap == 'off') ) $wrap = ' wrap="' . $wrap . '"';

		if ( !empty($label) && !empty($options['custom_field_template_replace_keys_by_labels']) )
			$title = stripcslashes($label);

		$event = array('onclick' => $onclick, 'ondblclick' => $ondblclick, 'onkeydown' => $onkeydown, 'onkeypress' => $onkeypress, 'onkeyup' => $onkeyup, 'onmousedown' => $onmousedown, 'onmouseup' => $onmouseup, 'onmouseover' => $onmouseover, 'onmouseout' => $onmouseout, 'onmousemove' => $onmousemove, 'onfocus' => $onfocus, 'onblur' => $onblur, 'onchange' => $onchange, 'onselect' => $onselect);
		$event_output = "";
		foreach($event as $key => $val) :
			if ( $val )
				$event_output .= " " . $key . '="' . stripcslashes(trim($val)) . '"';
		endforeach;

		if ( $multipleButton == true && $ct_value == $cftnum ) :
			$addfield .= '<div style="margin-top:-1em;">';
			if ( !empty($htmlEditor) ) :
				if ( substr($wp_version, 0, 3) < '3.3' ) :
					$load_htmlEditor1 = 'jQuery(\'#qt_\'+original_id+\'_qtags\').remove();';
					$load_htmlEditor2 = 'qt_set(original_id);qt_set(new_id);';
					if( $tinyMCE == true ) : $load_htmlEditor2 .= ' jQuery(\'#qt_\'+original_id+\'_qtags\').hide(); jQuery(\'#qt_\'+new_id+\'_qtags\').hide();'; endif;
				else  :
					$load_htmlEditor1 = 'jQuery(\'#qt_\'+original_id+\'_toolbar\').remove();';
					$load_htmlEditor2 = 'new QTags(new_id);QTags._buttonsInit();';
					if( $tinyMCE == true ) : $load_htmlEditor2 .= ' jQuery(\'#qt_\'+new_id+\'_toolbar\').hide();'; endif;
				endif;
			endif;
			if ( !empty($tinyMCE) ) :
				if ( substr($wp_version, 0, 3) < '3.3' ) :
					$load_tinyMCE = 'tinyMCE.execCommand(' . "'mceAddControl'" . ',false, original_id);tinyMCE.execCommand(' . "'mceAddControl'" . ',false, new_id);';
				elseif ( substr($wp_version, 0, 3) < '3.9' ) :
					$load_tinyMCE = 'var ed = new tinyMCE.Editor(original_id, tinyMCEPreInit.mceInit[\'content\']); ed.render(); var ed = new tinyMCE.Editor(new_id, tinyMCEPreInit.mceInit[\'content\']); ed.render();';
				else :
					$load_tinyMCE = 'tinyMCE.execCommand('."'mceAddEditor'".', false, original_id);tinyMCE.execCommand('."'mceAddEditor'".', false, new_id);';
				endif;

				$addfield .= '<a href="#clear" onclick="var original_id; var new_id; jQuery(this).parent().parent().parent().find('."'textarea'".').each(function(){original_id = jQuery(this).attr('."'id'".');'.$load_htmlEditor1.'tinyMCE.execCommand(' . "'mceRemoveControl'" . ',true,jQuery(this).attr('."'id'".'));});var clone = jQuery(this).parent().parent().parent().clone().insertAfter(jQuery(this).parent().parent().parent()); clone.find('."'textarea'".').val('."''".');if(original_id.match(/([0-9])$/)) {var matchval = RegExp.$1;re = new RegExp(matchval, '."'ig'".');clone.html(clone.html().replace(re, parseInt(matchval)+1)); new_id = original_id.replace(/([0-9])$/, parseInt(matchval)+1);}if ( tinyMCE.get(jQuery(this).attr('."original_id".')) ) {'.$load_tinyMCE.'}jQuery(this).parent().css('."'visibility','hidden'".');'.$load_htmlEditor2.'jQuery(this).parent().prev().css('."'visibility','hidden'".'); return false;">' . __('Add New', 'custom-field-template') . '</a>';
			else :
				$addfield .= '<a href="#clear" onclick="var original_id; var new_id; jQuery(this).parent().parent().parent().find('."'textarea'".').each(function(){original_id = jQuery(this).attr('."'id'".');});'.$load_htmlEditor1.'var clone = jQuery(this).parent().parent().parent().clone().insertAfter(jQuery(this).parent().parent().parent()); clone.find('."'textarea'".').val('."''".');if(original_id.match(/([0-9]+)$/)) {var matchval = RegExp.$1;re = new RegExp(matchval, '."'ig'".');clone.html(clone.html().replace(re, parseInt(matchval)+1)); new_id = original_id.replace(/([0-9]+)$/, parseInt(matchval)+1);}'.$load_htmlEditor2.'jQuery(this).parent().css('."'visibility','hidden'".');jQuery(this).parent().prev().css('."'visibility','hidden'".'); return false;">' . __('Add New', 'custom-field-template') . '</a>';
			endif;
			$addfield .= '</div>';
		endif;		
		
		$out_key = '<span' . $hide . '><label for="' . $name_id . $sid . '_' . $cftnum . '">' . $title . '</label></span><br />' . $addfield . $media . $switch;
		
		$out .= 
			'<dl id="dl_' . $name_id . $sid . '_' . $cftnum . '" class="dl_textarea">' .
			'<dt>'.$out_key.'</dt>' .
			'<dd>';

		if ( !empty($label) && empty($options['custom_field_template_replace_keys_by_labels']) )
			$out_value .= '<p class="label">' . stripcslashes($label) . '</p>';
		
		$out_value .= trim($before);

		if ( ($htmlEditor == true || $tinyMCE == true) && substr($wp_version, 0, 3) < '3.3' ) $out_value .= '<div class="quicktags">';
		
		if ( $htmlEditor == true ) :
			if ( substr($wp_version, 0, 3) < '3.3' ) :
				if( $tinyMCE == true ) $quicktags_hide = ' jQuery(\'#qt_' . $textarea_id . '_qtags\').hide();';
				$out_value .= '<script type="text/javascript">' . "\n" . '// <![CDATA[' . '
		jQuery(document).ready(function() { qt_' . $textarea_id . ' = new QTags(\'qt_' . $textarea_id . '\', \'' . $textarea_id . '\', \'editorcontainer_' . $textarea_id . '\', \'more\'); ' . $quicktags_hide . ' });' . "\n" . '// ]]>' . "\n" . '</script>';
				$editorcontainer_class = ' class="editorcontainer"';
			else :
				if( $tinyMCE == true ) $quicktags_hide = ' jQuery(\'#qt_' . $textarea_id . '_toolbar\').hide();';
				$out_value .= '<script type="text/javascript">' . "\n" . '// <![CDATA[' . '
		jQuery(document).ready(function() { new QTags(\'' . $textarea_id . '\'); QTags._buttonsInit(); ' . $quicktags_hide . ' }); ' . "\n";
				$out_value .=  '// ]]>' . "\n" . '</script>';
				$editorcontainer_class = ' class="wp-editor-container"';
			endif;
		endif;
		
		$out_value .= '<div' . $editorcontainer_class . ' id="editorcontainer_' . $textarea_id . '" style="clear:none;"><textarea id="' . $textarea_id . '" name="' . $name . '[' . $sid . '][]" rows="' .$rows. '" cols="' . $cols . '"' . $content_class . $style . $event_output . $wrap . '>' . htmlspecialchars(trim($value)) . '</textarea><input type="hidden" name="'.$name.'_rand['.$sid.']" value="'.$rand.'" /></div>';
		if ( ($htmlEditor == true || $tinyMCE == true) && substr($wp_version, 0, 3) < '3.3' ) $out_value .= '</div>';
		$out_value .= trim($after);
		$out .= $out_value.'</dd></dl>'."\n";
		
		return array($out, $out_key, $out_value);
	}
	
	function make_file( $name, $sid, $data, $post_id ) {
		$cftnum = $size = $hideKey = $label = $class = $style = $before = $after = $multipleButton = $relation = $mediaLibrary = $mediaPicker = '';
		$hide = $addfield = $out = $out_key = $out_value = $picker = $inside_fieldset = '';
		extract($data);
		$options = $this->get_custom_field_template_data();

		$name = stripslashes($name);

		$title = $name;
		$name = $this->sanitize_name( $name );
		$name_id = preg_replace( '/%/', '', $name );

		if ( !isset($_REQUEST['default']) || (isset($_REQUEST['default']) && $_REQUEST['default'] != true) ) $_REQUEST['default'] = false;

		if( isset( $post_id ) && $post_id > 0 && $_REQUEST['default'] != true ) {
			$value = $this->get_post_meta( $post_id, $title );
			$ct_value = count($value);
			$value = isset($value[ $cftnum ]) ? $value[ $cftnum ] : '';
		}

		if ( empty($ct_value) ) :
			$ct_value = !empty($startNum) ? $startNum-1 : 1;
		endif;
				
		if ( $hideKey == true ) $hide = ' class="hideKey"';
		if ( !empty($class) ) $class = ' class="' . $class . '"';
		if ( !empty($style) ) $style = ' style="' . $style . '"';
		
		if ( !empty($label) && !empty($options['custom_field_template_replace_keys_by_labels']) )
			$title = stripcslashes($label);
			
		if ( $multipleButton == true && $ct_value == $cftnum ) :
			$addfield .= '<div style="margin-top:-1em;">';
			$addfield .= '<a href="#clear" onclick="var tmp = jQuery(this).parent().parent().parent().clone().insertAfter(jQuery(this).parent().parent().parent());if(tmp.find('."'input[type=file]'".').attr('."'id'".').match(/([0-9]+)$/)) { matchval = RegExp.$1; matchval++;tmp.find('."'input[type=file]'".').attr('."'id',".'tmp.find('."'input[type=file]'".').attr('."'id'".').replace(/([0-9]+)$/, matchval));}if(tmp.find('."'input[type=hidden]'".').attr('."'id'".').match(/([0-9]+)_hide$/)) { matchval = RegExp.$1; matchval++;tmp.find('."'input[type=hidden]'".').attr('."'id',".'tmp.find('."'input[type=hidden]'".').attr('."'id'".').replace(/([0-9]+)_hide$/, matchval+'."'_hide'".'));}if(tmp.find('."'input[type=hidden]'".').attr('."'name'".').match(/\[([0-9]+)\]$/)) { matchval = RegExp.$1; matchval++;tmp.find('."'input[type=hidden]'".').attr('."'name',".'tmp.find('."'input[type=hidden]'".').attr('."'name'".').replace(/\[([0-9]+)\]$/, \'[\'+matchval+\']\'));}jQuery(this).parent().css('."'visibility','hidden'".');jQuery(this).parent().prev().css('."'visibility','hidden'".'); return false;">' . __('Add New', 'custom-field-template') . '</a>';
			$addfield .= '</div>';
		endif;
	
		if ( $relation == true ) $tab = 'gallery';
		else $tab = 'library';
		$media_upload_iframe_src = "media-upload.php";
		$image_upload_iframe_src = apply_filters('image_upload_iframe_src', "$media_upload_iframe_src?type=image&tab=library");

		if ( $mediaPicker == true ) :
			$picker = __(' OR ', 'custom-field-template');
			$picker .= '<a href="'.$image_upload_iframe_src.'&post_id='.$post_id.'&TB_iframe=1&tab='.$tab.'" class="thickbox" onclick="jQuery('."'#cft_current_template'".').val(jQuery(this).parent().parent().parent().';
			if ( $inside_fieldset ) $picker .= 'parent().';
			$picker .= 'parent().attr(\'id\').replace(\'cft_\',\'\'));jQuery('."'#cft_clicked_id'".').val(jQuery(this).parent().find(\'input\').attr(\'id\'));">'.__('Select by Media Picker', 'custom-field-template').'</a>';
		endif;
		
		$out_key = '<span' . $hide . '><label for="' . $name_id . $sid . '_' . $cftnum . '">' . $title . '</label></span>'.$addfield;
			
		$out .= 
			'<dl id="dl_' . $name_id . $sid . '_' . $cftnum . '" class="dl_file">' .
			'<dt>'.$out_key.'</dt>' .
			'<dd>';

		if ( !empty($label) && !$options['custom_field_template_replace_keys_by_labels'] )
			$out_value .= '<p class="label">' . stripcslashes($label) . '</p>';
		$out_value .= trim($before).'<input id="' . $name_id . $sid . '_' . $cftnum . '" name="' . $name . '['.$sid.'][]" type="file" size="' . $size . '"' . $class . $style . ' onchange="if (jQuery(this).val()) { jQuery(\'#cft_save_button\'+jQuery(this).parent().parent().parent().parent().attr(\'id\').replace(\'cft_\',\'\')).attr(\'disabled\', true); jQuery(\'#post-preview\').hide(); } else { jQuery(\'#cft_save_button\').attr(\'disabled\', false); jQuery(\'#post-preview\').show(); }" />'.trim($after).$picker;

		if ( isset($value) && ( $value = intval($value) ) && $thumb_url = wp_get_attachment_image_src( $value, 'thumbnail', true ) ) :
			$thumb_url = $thumb_url[0];
			
			$post = get_post($value);
			$filename = basename($post->guid);
			$title = esc_attr(trim($post->post_title));
			
			if ( !empty($mediaLibrary) ) :
				$title = '<a href="'.$image_upload_iframe_src.'&post_id='.$post_id.'&TB_iframe=1&tab='.$tab.'" class="thickbox">'.$title.'</a>';
			endif;
			
			$out_value .= '<p><label for="'.$name . $sid . '_' . $cftnum . '_delete"><input type="checkbox" name="'.$name . '_delete[' . $sid . '][' . $cftnum . ']" id="'.$name_id . $sid . '_' . $cftnum . '_delete" value="1" class="delete_file_checkbox" /> ' . __('Delete', 'custom-field-template') . '</label> <img src="'.$thumb_url.'" width="32" height="32" style="vertical-align:middle;" /> ' . $title . ' </p>';
			$out_value .= '<input type="hidden" id="' . $name_id . $sid . '_' . $cftnum . '_hide" name="'.$name . '[' . $sid . '][' . $cftnum . ']" value="' . $value . '" />';
		else :
			$out_value .= '<input type="hidden" id="' . $name_id . $sid . '_' . $cftnum . '_hide" name="'.$name . '[' . $sid . '][' . $cftnum . ']" value="" />';
		endif;

		$out .= $out_value.'</dd></dl>'."\n";

		return array($out, $out_key, $out_value);
	}


	function load_custom_field( $id = 0 ) {
		global $current_user, $post, $wp_version;
		$level = $current_user->user_level;

		$options = $this->get_custom_field_template_data();
		
		$post_id = isset($_REQUEST['post']) ? $_REQUEST['post'] : '';
		
		if ( isset($post_id) ) $post = get_post($post_id);

		if ( isset($_REQUEST['revision']) ) $post_id = $_REQUEST['revision'];

		if ( !empty($options['custom_fields'][$id]['disable']) )
			return;

		$fields = $this->get_custom_fields( $id );

		if ( $fields == null )
			return;

		if ( (isset($_REQUEST['post_type']) && $_REQUEST['post_type'] == 'page') || $post->post_type=='page' ) :
			$post->page_template = get_post_meta( $post->ID, '_wp_page_template', true );
			if ( !$post->page_template ) $post->page_template = 'default';
		endif;

		if ( !empty($options['custom_fields'][$id]['post_type']) ) :
			if ( substr($wp_version, 0, 3) < '3.0' ) :
				if ( $options['custom_fields'][$id]['post_type'] == 'post' && (strstr($_SERVER['REQUEST_URI'], 'wp-admin/page-new.php') || strstr($_SERVER['REQUEST_URI'], 'wp-admin/page.php') || strstr($_SERVER['REQUEST_URI'], 'wp-admin/edit-pages.php')) ) :
					return;
				endif;
				if ( $options['custom_fields'][$id]['post_type'] == 'page' && (strstr($_SERVER['REQUEST_URI'], 'wp-admin/post-new.php') || strstr($_SERVER['REQUEST_URI'], 'wp-admin/post.php') || strstr($_SERVER['REQUEST_URI'], 'wp-admin/edit.php')) ) :
					return;
				endif;
			else :
				if ( (isset($_REQUEST['post_type']) && $_REQUEST['post_type']!=$options['custom_fields'][$id]['post_type']) && $post->post_type!=$options['custom_fields'][$id]['post_type'] ) :
					return;
				endif;
			endif;
		endif;

		if ( !empty($options['custom_fields'][$id]['custom_post_type']) ) :
			$custom_post_type = explode(',', $options['custom_fields'][$id]['custom_post_type']);
			$custom_post_type = array_filter( $custom_post_type );
			$custom_post_type = array_unique(array_filter(array_map('trim', $custom_post_type)));
			if ( !in_array($post->post_type, $custom_post_type) )
				return;
		endif;		

		if ( substr($wp_version, 0, 3) < '3.0' ) :
			if ( !empty($options['custom_fields'][$id]['category']) && (strstr($_SERVER['REQUEST_URI'], 'wp-admin/page-new.php') || strstr($_SERVER['REQUEST_URI'], 'wp-admin/page.php')) && empty($options['custom_fields'][$id]['template_files']) ) :
				return;
			endif;
			if ( !empty($options['custom_fields'][$id]['template_files']) && (strstr($_SERVER['REQUEST_URI'], 'wp-admin/post-new.php') || strstr($_SERVER['REQUEST_URI'], 'wp-admin/post.php')) && empty($options['custom_fields'][$id]['category']) ) :
				return;
			endif;
		else :
			if ( !empty($options['custom_fields'][$id]['category']) && ((isset($_REQUEST['post_type']) && $_REQUEST['post_type']=='page') || $post->post_type=='page') && empty($options['custom_fields'][$id]['template_files']) ) :
				return;
			endif;
			if ( !empty($options['custom_fields'][$id]['template_files']) && ($_REQUEST['post_type']!='page' && $post->post_type!='page') && empty($options['custom_fields'][$id]['category']) ) :
				return;
			endif;
		endif;

		if ( (!isset($post_id) || $post_id<0) && !empty($options['custom_fields'][$id]['category']) && $_REQUEST['cft_mode'] != 'ajaxload' )
			return;
	
		if ( isset($post_id) && !empty($options['custom_fields'][$id]['category']) && (!isset($options['posts'][$post_id]) || (isset($options['posts'][$post_id]) && $options['posts'][$post_id] !== $id)) && $_REQUEST['cft_mode'] != 'ajaxload' )
			return;
	
		if ( !isset($_REQUEST['id']) && !empty($options['custom_fields'][$id]['category']) && isset($_REQUEST['cft_mode']) && $_REQUEST['cft_mode'] == 'ajaxload' ) :
			$category = explode(',', $options['custom_fields'][$id]['category']);
			$category = array_filter( $category );
			$category = array_unique(array_filter(array_map('trim', $category)));
	
			if ( !empty($_REQUEST['tax_input']) && is_array($_REQUEST['tax_input']) ) :
				foreach($_REQUEST['tax_input'] as $key => $val) :
					foreach($val as $key2 => $val2 ) :
						if ( in_array($val2, $category) ) : $notreturn = 1; break; endif;;
					endforeach;
				endforeach;
			else :
				if ( !empty($_REQUEST['post_category']) && is_array($_REQUEST['post_category']) ) :
					foreach($_REQUEST['post_category'] as $val) :
						if ( in_array($val, $category) ) : $notreturn = 1; break; endif;;
					endforeach;
				endif;
			endif;
			if ( empty($notreturn) ) return;
		endif;

		if ( !empty($options['custom_fields'][$id]['post']) ) :
			$post_ids = explode(',', $options['custom_fields'][$id]['post']);
			$post_ids = array_filter( $post_ids );
			$post_ids = array_unique(array_filter(array_map('trim', $post_ids)));
			if ( !in_array($post_id, $post_ids) )
				return;
		endif;

		if ( !empty($options['custom_fields'][$id]['template_files']) && (isset($post->page_template) || (isset($_REQUEST['page_template']) && $_REQUEST['page_template'])) ) :
			$template_files = explode(',', $options['custom_fields'][$id]['template_files']);
			$template_files = array_filter( $template_files );
			$template_files = array_unique(array_filter(array_map('trim', $template_files)));
			if ( isset($_REQUEST['page_template']) ) :
				if ( !in_array($_REQUEST['page_template'], $template_files) ) :
					return;
				endif;
			else :
				if ( !in_array($post->page_template, $template_files) ) :
					return;
				endif;
			endif;
		endif;
		
		if ( substr($wp_version, 0, 3) >= '3.3' && !post_type_supports($post->post_type, 'editor') && $post->post_type!='post' && $post->post_type!='page' ) :
			wp_editor('', 'content', array('dfw' => true, 'tabindex' => 1) );
			$out = '<style type="text/css">#wp-content-wrap { display:none; }</style>';
		else :
			$out = '';
		endif;
		
		if ( !empty($options['custom_fields'][$id]['instruction']) ) :
			$instruction = $this->EvalBuffer(stripcslashes($options['custom_fields'][$id]['instruction']));
			$out .= '<div id="cft_instruction'.$id.'" class="cft_instruction">' . $instruction . '</div>';
		endif;

		$out .= '<div id="cft_'.$id.'">';
		$out .= '<div>';
		$out .= '<input type="hidden" name="custom-field-template-id[]" id="custom-field-template-id" value="' . $id . '" />';

		if ( isset($options['custom_fields'][$id]['format']) && is_numeric($options['custom_fields'][$id]['format']) )
			$format = stripslashes($options['shortcode_format'][$options['custom_fields'][$id]['format']]);

		$last_title = '';
		$fieldset_open = 0;
		foreach( $fields as $field_key => $field_val ) :
			foreach( $field_val as $title => $data ) {
				$class = $style = $addfield = $tmpout = $out_all = $out_key = $out_value = $duplicator = '';
				if ( isset($data['parentSN']) && is_numeric($data['parentSN']) ) $parentSN = $data['parentSN'];
				else $parentSN = $field_key;
				if ( $fieldset_open ) $data['inside_fieldset'] = 1;
					if ( isset($data['level']) && is_numeric($data['level']) ) :
						if ( $data['level'] > $level ) continue;
					endif;
					if( $data['type'] == 'break' ) {
						if ( !empty($data['class']) ) $class = ' class="' . $data['class'] . '"';
						if ( !empty($data['style']) ) $style = ' style="' . $data['style'] . '"';
						$tmpout .= '</div><div' . $class . $style . '>';
					}
					else if( $data['type'] == 'fieldset_open' ) {
						$fieldset_open = 1;
						if ( !empty($data['class']) ) $class = ' class="' . $data['class'] . '"';
						if ( !empty($data['style']) ) $style = ' style="' . $data['style'] . '"';
						$tmpout .= '<fieldset' . $class . $style . '>'."\n";
						$tmpout .= '<input type="hidden" name="' . $this->sanitize_name( $title ) . '[]" value="1" />'."\n";
					
						if ( isset($data['multipleButton']) && $data['multipleButton'] == true ) :
							$addfield .= ' <span>';
							if ( isset($post_id) ) $addbutton = $this->get_post_meta( $post_id, $title, true )-1;
							if ( !isset($addbutton) || $addbutton<=0 ) $addbutton = 0;
							if ( $data['cftnum']/2 == $addbutton ) :
								if ( substr($wp_version, 0, 3) < '3.3' ) :
									$load_htmlEditor1 = 'if ( jQuery(\'#qt_\'+jQuery(this).attr('."'id'".')+\'_qtags\').html() ) {jQuery(\'#qt_\'+jQuery(this).attr('."'id'".')+\'_qtags\').remove();';
									$load_htmlEditor2 = 'qt_set(textarea_html_ids[i]);';
									$load_tinyMCE = 'tinyMCE.execCommand(' . "'mceAddControl'" . ',false, textarea_tmce_ids[i]); switchMode(textarea_tmce_ids[i]); switchMode(textarea_tmce_ids[i]);';
								elseif ( substr($wp_version, 0, 3) < '3.9' ) :
									$load_htmlEditor1 = 'if ( jQuery(\'#qt_\'+jQuery(this).attr('."'id'".')+\'_toolbar\').html() ) {jQuery(\'#qt_\'+jQuery(this).attr('."'id'".')+\'_toolbar\').remove();';
									$load_htmlEditor2 = 'new QTags(textarea_html_ids[i]);QTags._buttonsInit();';
									$load_tinyMCE = 'var ed = new tinyMCE.Editor(textarea_tmce_ids[i], tinyMCEPreInit.mceInit[\'content\']); ed.render(); switchMode(textarea_tmce_ids[i]); switchMode(textarea_tmce_ids[i]);';
								else :
									$load_htmlEditor1 = 'if ( jQuery(\'#qt_\'+jQuery(this).attr('."'id'".')+\'_toolbar\').html() ) {jQuery(\'#qt_\'+jQuery(this).attr('."'id'".')+\'_toolbar\').remove();';
									$load_htmlEditor2 = 'new QTags(textarea_html_ids[i]);QTags._buttonsInit();';
									$load_tinyMCE = 'tinyMCE.execCommand('."'mceAddEditor'".', true, textarea_tmce_ids[i]); switchMode(textarea_tmce_ids[i]); switchMode(textarea_tmce_ids[i]);';
								endif;
								$addfield .= '<input type="hidden" id="' . $this->sanitize_name( $title ) . '_count" value="0" /><script type="text/javascript">jQuery(document).ready(function() {jQuery(\'#' . $this->sanitize_name( $title ) . '_count\').val(0); });</script>';
								$addfield .= ' <a href="#clear" onclick="var textarea_tmce_ids = new Array();var textarea_html_ids = new Array();var html_start = 0;jQuery(this).parent().parent().parent().find('."'textarea'".').each(function(){if ( jQuery(this).attr('."'id'".') ) {'.$load_htmlEditor1.'if ( jQuery(\'#'.$this->sanitize_name( $title ).'_count\').val() == 0 ) html_start++;textarea_html_ids.push(jQuery(this).attr('."'id'".'));}}ed = tinyMCE.get(jQuery(this).attr('."'id'".')); if(ed) {textarea_tmce_ids.push(jQuery(this).attr('."'id'".'));tinyMCE.execCommand(' . "'mceRemoveControl'" . ',false,jQuery(this).attr('."'id'".'));}});var checked_ids = new Array();jQuery(this).parent().parent().parent().find('."'input[type=radio]:checked'".').each(function(){checked_ids.push(jQuery(this).attr('."'id'".'));});var tmp = jQuery(this).parent().parent().parent().clone().insertAfter(jQuery(this).parent().parent().parent());tmp.find('."'input'".').attr('."'checked',false".');for( var i=0;i<checked_ids.length;i++) { jQuery('."'#'+checked_ids[i]".').attr('."'checked'".', true); }tmp.find('."'input[type=text],input[type=hidden],input[type=file]'".').val('."''".');tmp.find('."'select'".').val('."''".');tmp.find('."'textarea'".').text('."''".');tmp.find('."'p'".').remove();tmp.find('."'dl'".').each(function(){if(jQuery(this).attr('."'id'".')){if(jQuery(this).attr('."'id'".').match(/_([0-9]+)$/)) {matchval = RegExp.$1;matchval++;jQuery(this).attr('."'id',".'jQuery(this).attr('."'id'".').replace(/_([0-9]+)$/, \'_\'+matchval));jQuery(this).find('."'textarea'".').each(function(){if(jQuery(this).attr('."'id'".').match(/([0-9]+)$/)) {var tmce_check = false;var html_check = false;for( var i=0;i<textarea_tmce_ids.length;i++) { if ( jQuery(this).attr('."'id'".')==textarea_tmce_ids[i] ) { tmce_check = true; } }for( var i=0;i<textarea_html_ids.length;i++) { if ( jQuery(this).attr('."'id'".')==textarea_html_ids[i] ) { html_check = true; } }  if ( tmce_check || html_check ) {matchval2 = RegExp.$1;jQuery(this).attr('."'id',".'jQuery(this).attr('."'id'".').replace(/([0-9]+)$/, parseInt(matchval2)+1));re = new RegExp(matchval2, '."'ig'".');jQuery(this).parent().parent().parent().html(jQuery(this).parent().parent().parent().html().replace(re, parseInt(matchval2)+1));if ( tmce_check ) textarea_tmce_ids.push(jQuery(this).attr('."'id'".'));if ( html_check ) textarea_html_ids.push(jQuery(this).attr('."'id'".'));}}jQuery(this).attr('."'name',".'jQuery(this).attr('."'name'".').replace(/\[([0-9]+)\]$/, \'[\'+matchval+\']\'));});jQuery(this).find('."'input'".').each(function(){if(jQuery(this).attr('."'id'".')){jQuery(this).attr('."'id',".'jQuery(this).attr('."'id'".').replace(/_([0-9]+)_/, \'_\'+matchval+\'_\'));jQuery(this).attr('."'id',".'jQuery(this).attr('."'id'".').replace(/_([0-9]+)$/, \'_\'+matchval));}if(jQuery(this).attr('."'name'".')){jQuery(this).attr('."'name',".'jQuery(this).attr('."'name'".').replace(/\[([0-9]+)\]$/, \'[\'+matchval+\']\'));}});jQuery(this).find('."'label'".').each(function(){jQuery(this).attr('."'for',".'jQuery(this).attr('."'for'".').replace(/_([0-9]+)_/, \'_\'+matchval+\'_\'));jQuery(this).attr('."'for',".'jQuery(this).attr('."'for'".').replace(/_([0-9]+)$/, \'_\'+matchval));jQuery(this).attr('."'for',".'jQuery(this).attr('."'for'".').replace(/\[([0-9]+)\]$/, \'[\'+matchval+\']\'));});}}});for( var i=html_start;i<textarea_html_ids.length;i++) { '.$load_htmlEditor2.' }for( var i=html_start;i<textarea_tmce_ids.length;i++) { '.$load_tinyMCE.' }jQuery(this).parent().css('."'visibility','hidden'".');jQuery(\'#'.$this->sanitize_name( $title ).'_count\').val(parseInt(jQuery(\'#'.$this->sanitize_name( $title ).'_count\').val())+1);return false;">' . __('Add New', 'custom-field-template') . '</a>';
							else :
								$addfield .= ' <a href="#clear" onclick="jQuery(this).parent().parent().parent().remove();return false;">' . __('Delete', 'custom-field-template') . '</a>';
							endif;
							$addfield .= '</span>';
						endif;
		
						if ( isset($data['legend']) || isset($addfield) ) :
							if ( !isset($data['legend']) ) $data['legend'] = '';
							if ( !isset($addfield) ) $addfield = '';
							$tmpout .= '<legend>' . stripcslashes(trim($data['legend'])) . $addfield . '</legend>';
						endif;
					}
					else if( $data['type'] == 'fieldset_close' ) {
						$fieldset_open = 0;
						$tmpout .= '</fieldset>';
					}
					else if( $data['type'] == 'textfield' || $data['type'] == 'text' ) {
						list($out_all,$out_key,$out_value) = $this->make_textfield( $title, $parentSN, $data, $post_id );
					}
					else if( $data['type'] == 'checkbox' ) {
						list($out_all,$out_key,$out_value) = $this->make_checkbox( $title, $parentSN, $data, $post_id );
					}
					else if( $data['type'] == 'radio' ) {
						$data['values'] = explode( '#', $data['value'] );
						if ( isset($data['valueLabel']) ) $data['valueLabels'] = explode( '#', $data['valueLabel'] );
						list($out_all,$out_key,$out_value) = $this->make_radio( $title, $parentSN, $data, $post_id );
					}
					else if( $data['type'] == 'select' ) {
						if ( isset($data['value']) ) $data['values'] = explode( '#', $data['value'] );
						if ( isset($data['valueLabel']) ) $data['valueLabels'] = explode( '#', $data['valueLabel'] );
						list($out_all,$out_key,$out_value) = $this->make_select( $title, $parentSN, $data, $post_id );
					}
					else if( $data['type'] == 'textarea' ) {
						list($out_all,$out_key,$out_value) = $this->make_textarea( $title, $parentSN, $data, $post_id );
					}
					else if( $data['type'] == 'file' ) {
						list($out_all,$out_key,$out_value) = $this->make_file( $title, $parentSN, $data, $post_id );
					}
				if ( isset($options['custom_fields'][$id]['format']) && is_numeric($options['custom_fields'][$id]['format']) ) :
					$duplicator = '['.$title.']';
					$preg_key = preg_quote($title, '/');
					$out_key = str_replace('\\', '\\\\', $out_key); 
					$out_key = str_replace('$', '\$', $out_key); 
					$out_value = str_replace('\\', '\\\\', $out_value); 
					$out_value = str_replace('$', '\$', $out_value); 
					$format = preg_replace('/\[\['.$preg_key.'\]\]/', $out_key, $format);
					$format = preg_replace('/\['.$preg_key.'\]/', $out_value.$duplicator, $format);
					if ( !empty($last_title) && $last_title != $title ) $format = preg_replace('/\['.preg_quote($last_title,'/').'\]/', '', $format);
					$last_title = $title;
				else :
					$out .= $tmpout.$out_all;
				endif;
			}
		endforeach;
		if ( !empty($last_title) ) $format = preg_replace('/\['.preg_quote($last_title,'/').'\]/', '', $format);
		if ( isset($options['custom_fields'][$id]['format']) && is_numeric($options['custom_fields'][$id]['format']) ) $out .= $format;

		$out .= '<script type="text/javascript">' . "\n" .
				'// <![CDATA[' . "\n";
		$out .= '	jQuery(document).ready(function() {' . "\n" .
				'		jQuery("#custom_field_template_select").val("' . $id . '");' . "\n" .
				'	});' . "\n";	
		$out .= '// ]]>' . "\n" .
				'</script>';				
		$out .= '</div>';
		$out .= '</div>';
	
		return array($out, $id);
	}

	function insert_custom_field($post, $args) {
		global $wp_version, $post, $wpdb;
		$options = $this->get_custom_field_template_data();
		$out = '';
		
		if( $options == null)
			return;

		if ( !$options['css'] ) {
			$this->install_custom_field_template_css();
			$options = $this->get_custom_field_template_data();
		}

		if ( substr($wp_version, 0, 3) < '2.5' ) {
			$out .= '
<div class="dbx-b-ox-wrapper">
<fieldset id="seodiv" class="dbx-box">
<div class="dbx-h-andle-wrapper">
<h3 class="dbx-handle">' . __('Custom Field Template', 'custom-field-template') . '</h3>
</div>
<div class="dbx-c-ontent-wrapper">
<div class="dbx-content">';
        }
		
		if ( isset($args['args']) ) :
			$init_id = $args['args'];
			$suffix = $args['args'];
			$suffix2 = '_'.$args['args'];
			$suffix3 = $args['args'];
		else :
			if ( isset($_REQUEST['post']) ) $request_post = $_REQUEST['post'];
			else $request_post = '';
			if( isset($options['posts'][$request_post]) && count($options['custom_fields'])>$options['posts'][$request_post] ) :
				$init_id = $options['posts'][$request_post];
			else :
				$filtered_cfts = $this->custom_field_template_filter();
				if ( count($filtered_cfts)>0 ) :
					$init_id = $filtered_cfts[0]['id'];
				else :
					$init_id = 0;
				endif;
			endif;
			$suffix = '';
			$suffix2 = '';
			$suffix3 = '\'+jQuery(\'#custom-field-template-id\').val()+\'';
		endif;

		$out .= '<script type="text/javascript">' . "\n" .
				'// <![CDATA[' . "\n";	
		$out .=		'jQuery(document).ready(function() {' . "\n";

		$fields = $this->get_custom_fields( $init_id );
		if ( user_can_richedit() ) :
			if ( is_array($fields) ) :
				foreach( $fields as $field_key => $field_val ) :
					foreach( $field_val as $title => $data ) :
							if( $data[ 'type' ] == 'textarea' && !empty($data['tinyMCE']) ) :
								if ( substr($wp_version, 0, 3) >= '2.7' ) :
		/*$out .=		'	if ( getUserSetting( "editor" ) == "html" ) {
jQuery("#edButtonPreview").trigger("click"); }' . "\n";*/
								else :
		$out .=		'	if(wpTinyMCEConfig) if(wpTinyMCEConfig.defaultEditor == "html") { jQuery("#edButtonPreview").trigger("click"); }' . "\n";
								endif;
								break;
							endif;
					endforeach;
				endforeach;
			endif;
		endif;

		if ( empty($options['custom_field_template_deploy_box']) && !empty($options['custom_fields']) ) :
			if ( substr($wp_version, 0, 3) < '3.0' ) $taxonomy = 'categories';
			else $taxonomy = 'category';
		
			foreach ( $options['custom_fields'] as $key => $val ) :
				if ( !empty($val['category']) ) :
					$val['category'] = preg_replace('/\s/', '', $val['category']);
					$categories = explode(',', $val['category']);
					$categories = array_filter($categories);
					array_walk( $categories, create_function('&$v', '$v = trim($v);') );
					$query = "SELECT * FROM `".$wpdb->prefix."term_taxonomy` WHERE term_id IN (".addslashes($val['category']).")";
					$result = $wpdb->get_results($query, ARRAY_A);
					$category_taxonomy = array();
					if ( !empty($result) && is_array($result) ) :
						for($i=0;$i<count($result);$i++) :
							$category_taxonomy[$result[$i]['term_id']] = $result[$i]['taxonomy'];
						endfor;
					endif;
					foreach($categories as $cat_id) :
						if ( is_numeric($cat_id) ) :
							if ( $taxonomy == 'category' ) $taxonomy = $category_taxonomy[$cat_id];
							$out .=		'jQuery(\'#in-'.$category_taxonomy[$cat_id].'-' . $cat_id . '\').click(function(){if(jQuery(\'#in-'.$category_taxonomy[$cat_id].'-' . $cat_id . '\').attr(\'checked\') == true) { if(tinyMCEID.length) { for(i=0;i<tinyMCEID.length;i++) {tinyMCE.execCommand(\'mceRemoveControl\', false, tinyMCEID[i]);} tinyMCEID.length=0;}; jQuery.get(\'?page=custom-field-template/custom-field-template.php&cft_mode=selectbox&post=\'+jQuery(\'#post_ID\').val()+\'&\'+jQuery(\'#'.$taxonomy.'-all :input\').fieldSerialize(), function(html) { jQuery(\'#cft_selectbox\').html(html);';
							if ( !empty($options['custom_field_template_use_autosave']) ) :
								$out .= ' var fields = jQuery(\'#cft'.$suffix.' :input\').fieldSerialize();';
								$out .= 'jQuery.ajax({type: \'POST\', url: \'?page=custom-field-template/custom-field-template.php&cft_mode=ajaxsave&post=\'+jQuery(\'#post_ID\').val()+\'&custom-field-template-verify-key=\'+jQuery(\'#custom-field-template-verify-key\').val()+\'&\'+fields, success: function(){jQuery(\'#custom_field_template_select\').val(\'' . $key . '\');jQuery.ajax({type: \'GET\', url: \'?page=custom-field-template/custom-field-template.php&cft_mode=ajaxload&id=' . $key . '&post=\'+jQuery(\'#post_ID\').val(), success: function(html) {';
								if ( !empty($options['custom_field_template_replace_the_title']) ) :
									$out .= 'jQuery(\'#cftdiv'.$suffix.' h3 span\').text(\'' . $options['custom_fields'][$key]['title'] . '\');';
								endif;
								$out .= 'jQuery(\'#cft\').html(html);}});}});';
							else :
								$out .=		'	jQuery(\'#custom_field_template_select\').val(\'' . $key . '\');jQuery.ajax({type: \'GET\', url: \'?page=custom-field-template/custom-field-template.php&cft_mode=ajaxload&id=' . $key . '&post=\'+jQuery(\'#post_ID\').val()+\'&\'+jQuery(\'#'.$taxonomy.'-all :input\').fieldSerialize(), success: function(html) {';
								if ( !empty($options['custom_field_template_replace_the_title']) ) :
									$out .= 'jQuery(\'#cftdiv'.$suffix.' h3 span\').text(\'' . $options['custom_fields'][$key]['title'] . '\');';
								endif;
								$out .= 'jQuery(\'#cft\').html(html);}});';
							endif;
							$out .= ' });';
							
							$out .=		'	}else{ jQuery(\'#cft\').html(\'\');jQuery.get(\'?page=custom-field-template/custom-field-template.php&cft_mode=selectbox&post=\'+jQuery(\'#post_ID\').val()+\'&\'+jQuery(\'#'.$taxonomy.'-all :input\').fieldSerialize(), function(html) { jQuery(\'#cft_selectbox\').html(html); jQuery.ajax({type: \'GET\', url: \'?page=custom-field-template/custom-field-template.php&cft_mode=ajaxload&post=\'+jQuery(\'#post_ID\').val()+\'&\'+jQuery(\'#'.$taxonomy.'-all :input\').fieldSerialize(), success: function(html) { jQuery(\'#cft\').html(html);}}); });';
							if ( !empty($options['custom_field_template_replace_the_title']) ) :
								$out .= 'jQuery(\'#cftdiv'.$suffix.' h3 span\').text(\'' . __('Custom Field Template', 'custom-field-template') . '\');';
							endif;
							$out .= '}});' . "\n";
						endif;
					endforeach;
				endif;
			endforeach;
		endif;

		if ( empty($options['custom_field_template_deploy_box']) && 0 != count( get_page_templates() ) ):
			if ( empty($_REQUEST['post_type']) ) $_REQUEST['post_type'] = 'post';
			$out .=	'jQuery(\'#page_template\').change(function(){ if(tinyMCEID.length) { for(i=0;i<tinyMCEID.length;i++) {tinyMCE.execCommand(\'mceRemoveControl\', false, tinyMCEID[i]);} tinyMCEID.length=0;}; jQuery.get(\'?post_type='.$_REQUEST['post_type'].'&page=custom-field-template/custom-field-template.php&cft_mode=selectbox&post=\'+jQuery(\'#post_ID\').val()+\'&page_template=\'+jQuery(\'#page_template\').val(), function(html) { jQuery(\'#cft_selectbox\').html(html); jQuery.ajax({type: \'GET\', url: \'?post_type='.$_REQUEST['post_type'].'&page=custom-field-template/custom-field-template.php&cft_mode=ajaxload&page_template=\'+jQuery(\'#page_template\').val()+\'&post=\'+jQuery(\'#post_ID\').val(), success: function(html) { jQuery(\'#cft\').html(html);';
			if ( !empty($options['custom_field_template_replace_the_title']) ) :
				$out .= 'if(html) { jQuery(\'#cftdiv'.$suffix.' h3 span\').text(jQuery(\'#custom_field_template_select :selected\').text());}';
			endif;
			$out .= '}});});';
			$out .= '});' . "\n";
		endif;

		$out .= 	'	jQuery(\'#cftloading_img'.$suffix.'\').ajaxStart(function() { jQuery(this).show();});';
		$out .= 	'	jQuery(\'#cftloading_img'.$suffix.'\').ajaxStop(function() { jQuery(this).hide();});';
		$out .=		'});' . "\n";

		$out .=		'var tinyMCEID = new Array();' . "\n" .
					'// ]]>' . "\n" .
					'</script>';
		list($body, $init_id) = $this->load_custom_field($init_id);

		if ( empty($options['custom_field_template_deploy_box']) ) :
			$out .= '<div id="cft_selectbox">';
			$out .= $this->custom_field_template_selectbox();
			$out .= '</div>';
		else :
			$out .= '<div>&nbsp;</div>';
		endif;
	
		$out .= '<div id="cft'.$suffix.'" class="cft">';
		$out .= $body;
		$out .= '</div>';
		
		if ( substr($wp_version, 0, 3) < '3.3' ) :
			$top_margin = 30;
		else :
			$top_margin = 0;
		endif;
				
		$out .= '<div style="position:absolute; top:'.$top_margin.'px; right:5px;">';
		$out .= '<img class="waiting" style="display:none; vertical-align:middle;" src="images/loading.gif" alt="" id="cftloading_img'.$suffix.'" /> ';
		if ( !empty($options['custom_field_template_use_disable_button']) ) :
		$out .= '<input type="hidden" id="disable_value" value="0" />';
		$out .= '<input type="button" value="' . __('Disable', 'custom-field-template') . '" onclick="';
		$out .= 'if(jQuery(\'#disable_value\').val()==0) { jQuery(\'#disable_value\').val(1);jQuery(this).val(\''.__('Enable', 'custom-field-template').'\');jQuery(\'#cft'.$suffix2.' input, #cft'.$suffix2.' select, #cft'.$suffix2.' textarea\').attr(\'disabled\',true);}else{  jQuery(\'#disable_value\').val(0);jQuery(this).val(\''.__('Disable', 'custom-field-template').'\');jQuery(\'#cft'.$suffix2.' input, #cft_'.$init_id.' select, #cft'.$suffix2.' textarea\').attr(\'disabled\',false);}'; 
		$out .= '" class="button" style="vertical-align:middle;" />';
		endif;
		if ( empty($options['custom_field_template_disable_initialize_button']) ) :
		$out .= '<input type="button" value="' . __('Initialize', 'custom-field-template') . '" onclick="';
		$out .= 'if(confirm(\''.__('Are you sure to reset current values? Default values will be loaded.', 'custom-field-template').'\')){if(tinyMCEID.length) { for(i=0;i<tinyMCEID.length;i++) {tinyMCE.execCommand(\'mceRemoveControl\', false, tinyMCEID[i]);} tinyMCEID.length=0;};jQuery.ajax({type: \'GET\', url: \'?page=custom-field-template/custom-field-template.php&cft_mode=ajaxload&default=true&id='.$suffix3.'&post=\'+jQuery(\'#post_ID\').val(), success: function(html) {';
		$out .= 'jQuery(\'#cft'.$suffix2.'\').html(html);}});}';
		$out .= '" class="button" style="vertical-align:middle;" />';
		endif;
		if ( empty($options['custom_field_template_disable_save_button']) ) :
		$out .= '<input type="button" id="cft_save_button'.$suffix.'" value="' . __('Save', 'custom-field-template') . '" onclick="';
		if ( !empty($options['custom_field_template_use_validation']) ) :
		$out .= 'if(!jQuery(\'#post\').valid()) return false;';
		endif;
		$out .= 'tinyMCE.triggerSave(); var fields = jQuery(\'#cft'.$suffix2.' :input\').fieldSerialize();';
		$out .= 'jQuery.ajax({type: \'POST\', url: \'?page=custom-field-template/custom-field-template.php&cft_mode=ajaxsave&post=\'+jQuery(\'#post_ID\').val()+\'&custom-field-template-verify-key=\'+jQuery(\'#custom-field-template-verify-key\').val(), data: fields, success: function() {jQuery(\'.delete_file_checkbox:checked\').each(function() {jQuery(this).parent().parent().remove();});}});';
		$out .= '" class="button" style="vertical-align:middle;" />';
		endif;
		$out .= '</div>';
			
		if ( substr($wp_version, 0, 3) < '2.5' ) {
			$out .= '</div></fieldset></div>';
		} else {
			if ( $body && !empty($options['custom_field_template_replace_the_title']) && empty($options['custom_field_template_deploy_box']) ) :
				$out .= '<script type="text/javascript">' . "\n" . '// <![CDATA[' . "\n";
				$out .=	'jQuery(document).ready(function() {jQuery(\'#cftdiv h3 span\').text(\'' . $options['custom_fields'][$init_id]['title'] . '\');});' . "\n";
				$out .= '// ]]>' . "\n" . '</script>';
			endif;
		}

		$out .= '<div style="clear:both;"></div>';
		echo $out;
	}

	function custom_field_template_filter(){
		global $post, $wp_version;
		
		$options = $this->get_custom_field_template_data();
		$filtered_cfts = array();
		
		$post_id = isset($_REQUEST['post']) ? $_REQUEST['post'] : '';
		if ( empty($post) ) $post = get_post($post_id);
		
		$categories = get_the_category($post_id);
		$cats = array();
		if ( is_array($categories) ) foreach($categories as $category) $cats[] = $category->cat_ID;
		
		if ( !empty($_REQUEST['tax_input']) && is_array($_REQUEST['tax_input']) ) :
			foreach($_REQUEST['tax_input'] as $key => $val) :
				$cats = array_merge($cats, $val);
			endforeach;
		elseif ( !empty($_REQUEST['post_category']) ) :
			$cats = array_merge($cats, $_REQUEST['post_category']);
		endif;

		for ( $i=0; $i < count($options['custom_fields']); $i++ ) :
			unset($cat_ids, $template_files, $post_ids);
			if ( !empty($options['custom_fields'][$i]['post_type']) ) :
				if ( substr($wp_version, 0, 3) < '3.0' ) :
					if ( $options['custom_fields'][$i]['post_type'] == 'post' && (strstr($_SERVER['REQUEST_URI'], 'wp-admin/page-new.php') || strstr($_SERVER['REQUEST_URI'], 'wp-admin/page.php') || strstr($_SERVER['REQUEST_URI'], 'wp-admin/edit-pages.php')) ) :
						continue;
					endif;
					if ( $options['custom_fields'][$i]['post_type'] == 'page' && (strstr($_SERVER['REQUEST_URI'], 'wp-admin/post-new.php') || strstr($_SERVER['REQUEST_URI'], 'wp-admin/post.php') || strstr($_SERVER['REQUEST_URI'], 'wp-admin/edit.php')) ) :
						continue;
					endif;
				else :
					if ( $post->post_type!=$options['custom_fields'][$i]['post_type'] ) :
						continue;
					endif;
				endif;
			endif;
			
			if ( !empty($options['custom_fields'][$i]['custom_post_type']) ) :
				$custom_post_type = explode(',', $options['custom_fields'][$i]['custom_post_type']);
				$custom_post_type = array_filter( $custom_post_type );
				$custom_post_type = array_unique(array_filter(array_map('trim', $custom_post_type)));
				if ( !in_array($post->post_type, $custom_post_type) )
					continue;
			endif;
		
			$cat_ids = isset($options['custom_fields'][$i]['category']) ? explode(',', $options['custom_fields'][$i]['category']) : array();
			$template_files = isset($options['custom_fields'][$i]['template_files']) ? explode(',', $options['custom_fields'][$i]['template_files']) : array();
			$post_ids = isset($options['custom_fields'][$i]['post']) ? explode(',', $options['custom_fields'][$i]['post']) : array();
			$cat_ids = array_filter( $cat_ids );
			$template_files = array_filter( $template_files );
			$post_ids = array_filter( $post_ids );
			$cat_ids = array_unique(array_filter(array_map('trim', $cat_ids)));
			$template_files = array_unique(array_filter(array_map('trim', $template_files)));
			$post_ids = array_unique(array_filter(array_map('trim', $post_ids)));

			if ( !empty($template_files) ) :
				if ( (strstr($_SERVER['REQUEST_URI'], 'wp-admin/page-new.php') || strstr($_SERVER['REQUEST_URI'], 'wp-admin/page.php') || strstr($_SERVER['REQUEST_URI'], 'wp-admin/edit-pages.php') || strstr($_SERVER['REQUEST_URI'], 'post_type=page') || $post->post_type=='page') ) :
					if ( count($template_files) && (isset($post->page_template) || isset($_REQUEST['page_template'])) ) :
						if( !in_array($post->page_template, $template_files) && (!isset($_REQUEST['page_template']) || (isset($_REQUEST['page_template']) && !in_array($_REQUEST['page_template'], $template_files))) ) :
							continue;
						endif;
					else :
						continue;
					endif;
				else :
					continue;
				endif;
			endif;
					
			if ( count($post_ids) && (!isset($_REQUEST['post']) || (isset($_REQUEST['post']) &&!in_array($_REQUEST['post'], $post_ids))) ) :
				continue;
			endif;

			if ( !empty($cat_ids) ) :
				if ( (strstr($_SERVER['REQUEST_URI'], 'wp-admin/post-new.php') || strstr($_SERVER['REQUEST_URI'], 'wp-admin/post.php') || strstr($_SERVER['REQUEST_URI'], 'wp-admin/edit.php')) ) :
					if ( is_array($cat_ids) && count($cat_ids) && count($cats)>0 ) :
						$cat_match = 0;
						foreach ( $cat_ids as $cat_id ) :
							if (in_array($cat_id, $cats) ) :
								$cat_match = 1;
							endif;
						endforeach;
						if($cat_match == 0) :
							continue;
						endif;
					else :
						continue;
					endif;
				else :
					continue;
				endif;
			endif;

			$options['custom_fields'][$i]['id'] = $i;
			$filtered_cfts[] = $options['custom_fields'][$i];
		endfor;
		return $filtered_cfts;
	}
	
	function custom_field_template_selectbox() {
		$options = $this->get_custom_field_template_data();

		if( count($options['custom_fields']) < 2 ) :
			return '&nbsp;';
		endif;

		$filtered_cfts = $this->custom_field_template_filter();

		if( count($filtered_cfts) < 1 ) :
			return '&nbsp;';
		endif;
		
		$out = '<select id="custom_field_template_select">';
		foreach ( $filtered_cfts as $filtered_cft ) :
			if ( isset($options['custom_fields'][$filtered_cft['id']]['disable']) ) :
			
			  elseif ( isset($_REQUEST['post']) && isset($options['posts'][$_REQUEST['post']]) && $filtered_cft['id'] == $options['posts'][$_REQUEST['post']] ) :
				$out .= '<option value="' . $filtered_cft['id'] . '" selected="selected">' . stripcslashes($filtered_cft['title']) . '</option>';
			else :
				$out .= '<option value="' . $filtered_cft['id'] . '">' . stripcslashes($filtered_cft['title']) . '</option>';
			endif;
		endforeach;
		$out .= '</select> ';
		
		$post_type = '';
		if ( !empty($_REQUEST['post_type']) ) $post_type = '+\'&post_type='.esc_attr($_REQUEST['post_type']).'\'';
		
		$out .= '<input type="button" class="button" value="' . __('Load', 'custom-field-template') . '" onclick="if(tinyMCEID.length) { for(i=0;i<tinyMCEID.length;i++) {tinyMCE.execCommand(\'mceRemoveControl\', false, tinyMCEID[i]);} tinyMCEID.length=0;};';
		$out .= ' var cftloading_select = function() {jQuery.ajax({type: \'GET\', url: \'?page=custom-field-template/custom-field-template.php&cft_mode=ajaxload&id=\'+jQuery(\'#custom_field_template_select\').val()+\'&post=\'+jQuery(\'#post_ID\').val()'.$post_type.'+\'&page_template=\'+jQuery(\'#page_template\').val(), success: function(html) {';
		if ( !empty($options['custom_field_template_replace_the_title']) ) :
			$out .= 'jQuery(\'#cftdiv h3 span\').text(jQuery(\'#custom_field_template_select :selected\').text());';
		endif;
		$out .= 'jQuery(\'#cft\').html(html);}});};';
		if ( !empty($options['custom_field_template_use_autosave']) ) :
			$out .= 'var fields = jQuery(\'#cft :input\').fieldSerialize();';
			$out .= 'jQuery.ajax({type: \'POST\', url: \'?page=custom-field-template/custom-field-template.php&cft_mode=ajaxsave&post=\'+jQuery(\'#post_ID\').val()+\'&custom-field-template-verify-key=\'+jQuery(\'#custom-field-template-verify-key\').val()+\'&\'+fields, success: cftloading_select});';
		else :
			$out .= 'cftloading_select();';
		endif;
		$out .= '" />';
	
		return $out;
	}

	function edit_meta_value( $id, $post ) {
		global $wpdb, $wp_version, $current_user;
		$options = $this->get_custom_field_template_data();

		if( !isset( $id ) || isset($_REQUEST['post_ID']) )
			$id = $_REQUEST['post_ID'];

		if( !current_user_can('edit_post', $id) )
			return $id;
								
		if( isset($_REQUEST['custom-field-template-verify-key']) && !wp_verify_nonce($_REQUEST['custom-field-template-verify-key'], 'custom-field-template') )
			return $id;

		if ( !empty($_POST['wp-preview']) && $id != $post->ID ) :
			/*$revision_ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_parent = %d AND post_type = 'revision'", $id ) );
			$wpdb->query( "DELETE FROM $wpdb->postmeta WHERE post_id IN (" . implode( ',', $revision_ids ) . ")" );
				
			wp_cache_flush();
			$original_data = $this->get_post_meta($id);

			if ( !empty($original_data) && is_array($original_data) ) :
				foreach ( $original_data as $key => $val ) :
					if ( is_array($val) ) :
						foreach ( $val as $val2 ) :
							add_metadata( 'post', $post->ID, $key, $val2 );
						endforeach;
					else :					
						add_metadata( 'post', $post->ID, $key, $val );
					endif;
				endforeach;
			endif;*/

			$id = $post->ID;
		endif;

		/*if ( $post->post_type == 'revision' )
    		return $id;*/

		if ( !isset($_REQUEST['custom-field-template-id']) ) :
			if ( isset($options['posts'][$id]) ) unset($options['posts'][$id]);
			update_option('custom_field_template_data', $options);
			return $id;
		endif;

		if ( !empty($_REQUEST['custom-field-template-id']) && is_array($_REQUEST['custom-field-template-id']) ) :
			foreach ( $_REQUEST['custom-field-template-id'] as $cft_id ) :
		$fields = $this->get_custom_fields($cft_id);
		
		if ( $fields == null )
			continue;
			
		if ( substr($wp_version, 0, 3) >= '2.8' ) {
			if ( !class_exists('SimpleTags') && !empty($_POST['tax_input']['post_tag']) && is_string($_POST['tax_input']['post_tag']) ) {
				$tags_input = explode(",", $_POST['tax_input']['post_tag']);
			}
		} else {
			if ( !class_exists('SimpleTags') && !empty($_POST['tags_input']) ) {
				$tags_input = explode(",", $_POST['tags_input']);
			}
		}
		
		$save_value = array();

		if ( !empty($_FILES) && is_array($_FILES) ) :
			foreach($_FILES as $key => $val ) :
				foreach( $val as $key2 => $val2 ) :
					if ( is_array($val2) ) :
						foreach( $val2 as $key3 => $val3 ) :
							foreach( $val3 as $key4 => $val4 ) :
								if ( !empty($val['name'][$key3][$key4]) ) :
									$tmpfiles[$key][$key3][$key4]['name']     = $val['name'][$key3][$key4];
									$tmpfiles[$key][$key3][$key4]['type']     = $val['type'][$key3][$key4];
									$tmpfiles[$key][$key3][$key4]['tmp_name'] = $val['tmp_name'][$key3][$key4];
									$tmpfiles[$key][$key3][$key4]['error']    = $val['error'][$key3][$key4];
									$tmpfiles[$key][$key3][$key4]['size']     = $val['size'][$key3][$key4];
								endif;
							endforeach;
						endforeach;
						break;
					endif;
				endforeach;
			endforeach;
		endif;
		unset($_FILES);

		foreach( $fields as $field_key => $field_val) :
			foreach( $field_val as $title => $data) :
				//if ( is_numeric($data['parentSN']) ) $field_key = $data['parentSN'];
				$name = $this->sanitize_name( $title );
				$title = esc_sql(stripcslashes(trim($title)));
				
				if ( isset($data['level']) && is_numeric($data['level']) && $current_user->user_level < $data['level'] ) :
					$save_value[$title] = $this->get_post_meta($id, $title, false);
					continue;
				endif;

				$field_key = 0;
				if ( isset($_REQUEST[$name]) && is_array($_REQUEST[$name]) ) :
					foreach( $_REQUEST[$name] as $tmp_key => $tmp_val ) :
						$field_key = $tmp_key;
						if ( is_array($tmp_val) ) $_REQUEST[$name][$tmp_key] = array_values($tmp_val);
					endforeach;
				endif;
				
				switch ( $data['type'] ) :
					case 'fieldset_open' :
						$save_value[$title][0] = count($_REQUEST[$name]);
						break;
					default :
						
						$value = isset($_REQUEST[$name][$field_key][$data['cftnum']]) ? trim($_REQUEST[$name][$field_key][$data['cftnum']]) : '';

						if ( !empty($options['custom_field_template_use_wpautop']) && $data['type'] == 'textarea' && !empty($value) )
							$value = wpautop($value);
						if ( isset($data['editCode']) && is_numeric($data['editCode']) ) :
							eval(stripcslashes($options['php'][$data['editCode']]));
						endif;
						if ( $data['type'] != 'file' ) :
							if( isset( $value ) && strlen( $value ) ) :
								if ( isset($data['insertTag']) && $data['insertTag'] == true ) :
									if ( !empty($data['tagName']) ) :
										$tags_input[trim($data['tagName'])][] = $value;
									else :
										$tags_input['post_tag'][] = $value;
									endif;
								endif;
								if ( isset($data['valueCount']) && $data['valueCount'] == true ) :
									$options['value_count'][$title][$value] = $this->set_value_count($title, $value, $id)+1;
								endif;
								if ( $data['type'] == 'textarea' && isset($_REQUEST['TinyMCE_' . $name . trim($_REQUEST[ $name."_rand" ][$field_key]) . '_size']) ) {
									preg_match('/cw=[0-9]+&ch=([0-9]+)/', $_REQUEST['TinyMCE_' . $name . trim($_REQUEST[ $name."_rand" ][$field_key]) . '_size'], $matched);
									$options['tinyMCE'][$id][$name][$field_key] = (int)($matched[1]/20);			
								}
								$save_value[$title][] = $value;
							elseif ( isset($data['blank']) && $data['blank'] == true ) :
								$save_value[$title][] = '';
							else :
								$tmp_value = $this->get_post_meta( $id, $title, false );
								if ( $data['type'] == 'checkbox' ) :
									delete_post_meta($id, $title, $data['value']);
								else :
									if ( isset($tmp_value[$data['cftnum']]) ) delete_post_meta($id, $title, $tmp_value[$data['cftnum']]);
								endif;
							endif;
						endif;

						if ( $data['type'] == 'file' ) :
							if ( isset($_REQUEST[$name.'_delete'][$field_key][$data['cftnum']]) ) :
								if ( empty($data['mediaRemove']) ) wp_delete_attachment($value);
								delete_post_meta($id, $title, $value);
								unset($value);
							endif;
							if( isset($tmpfiles[$name][$field_key][$data['cftnum']]) ) :
								$_FILES[$title] = $tmpfiles[$name][$field_key][$data['cftnum']];
								if ( isset($value) ) :
									if ( empty($data['mediaRemove']) ) wp_delete_attachment($value);
								endif;

								if ( isset($data['relation']) && $data['relation'] == true ) :
									$upload_id = media_handle_upload($title, $id);
								else :
									$upload_id = media_handle_upload($title, '');
								endif;
								$save_value[$title][] = $upload_id;
								unset($_FILES);
							else :
								if ( !get_post($value) && $value ) :
									if ( isset($data['blank']) && $data['blank'] == true ) :
										$save_value[$title][] = '';
									endif;
								elseif ( $value ) :
									$save_value[$title][] = $value;
								else :
									if ( isset($data['blank']) && $data['blank'] == true ) :
										$save_value[$title][] = '';
									endif;
								endif;
							endif;
						endif;
				endswitch;
			endforeach;
		endforeach;

		/*echo 'tmpfiles';
		print_r($tmpfiles);
		echo 'fields';
		print_r($fields);
		echo '_REQUEST';
		print_r($_REQUEST);
		echo 'save_value';
		print_r($save_value);
		echo 'get_post_custom';
		print_r(get_post_custom($id));
		exit();*/

		foreach( $save_value as $title => $values ) :
			unset($delete);
			if ( count($values) == 1 ) :
				if ( !add_metadata( 'post', $id, $title, apply_filters('cft_'.rawurlencode($title), $values[0]), true ) ) :
					if ( count($this->get_post_meta($id, $title, false))>1 ) :
						delete_metadata( 'post', $id, $title );
						add_metadata( 'post', $id, $title, apply_filters('cft_'.rawurlencode($title), $values[0]) );
					else :
						update_metadata( 'post', $id, $title, apply_filters('cft_'.rawurlencode($title), $values[0]) );
					endif;
				endif;
			elseif ( count($values) > 1 ) :
				$tmp = $this->get_post_meta( $id, $title, false );
				if ( $tmp ) delete_metadata( 'post', $id, $title );
				foreach($values as $val)
					add_metadata( 'post', $id, $title, apply_filters('cft_'.rawurlencode($title), $val) );
			endif;
		endforeach;
		
		if ( !empty($tags_input) && is_array($tags_input) ) :
			  foreach ( $tags_input as $tags_key => $tags_value ) :
				if ( class_exists('SimpleTags') && $tags_key == 'post_tag' ) :
					wp_cache_flush();
					$taxonomy = wp_get_object_terms($id, 'post_tag', array());
					if ( $taxonomy ) foreach($taxonomy as $val) $tags[] = $val->name;
					if ( is_array($tags) ) $tags_value = array_merge($tags, $tags_value);
				endif;
				
				if ( is_array($tags_value) )
					$tags_input = array_unique($tags_value);
				else
					$tags_input = $tags_value;
				if ( substr($wp_version, 0, 3) >= '2.8' )
					wp_set_post_terms( $id, $tags_value, $tags_key, true ); 
				else if ( substr($wp_version, 0, 3) >= '2.3' )
					wp_set_post_tags( $id, $tags_value );
			endforeach;
		endif;

		if ( empty($options['custom_field_template_deploy_box']) ) $options['posts'][$id] = $cft_id;
		
		endforeach;
		endif;

		update_option('custom_field_template_data', $options);
		wp_cache_flush();
		
		do_action('cft_save_post', $id, $post);
	}
	
	function parse_ini_str($Str,$ProcessSections = TRUE) {
		$options = $this->get_custom_field_template_data();

		$Section = NULL;
		$Data = array();
		$Sections = array();
		if ($Temp = strtok($Str,"\r\n")) {
			$sn = -1;
			do {
				switch ($Temp{0}) {
					case ';':
					case '#':
						break;
					case '[':
						if (!$ProcessSections) {
							break;
						}
						$Pos = strpos($Temp,'[');
						$Section = substr($Temp,$Pos+1,strpos($Temp,']',$Pos)-1);
						$sn++;
						$Data[$sn][$Section] = array();
						if ( isset($cftnum[$Section]) ) $cftnum[$Section]++;
						else $cftnum[$Section] = 0;
						$Data[$sn][$Section]['cftnum'] = $cftnum[$Section];
						if($Data[$sn][$Section])
						break;
					default:
						$Pos = strpos($Temp,'=');
						if ($Pos === FALSE) {
							break;
						}
						$Value = array();
						$Value["NAME"] = trim(substr($Temp,0,$Pos));
						$Value["VALUE"] = trim(substr($Temp,$Pos+1));
						
						if ($ProcessSections) {							
							$Data[$sn][$Section][$Value["NAME"]] = $Value["VALUE"];
						}
						else {
							$Data[$Value["NAME"]] = $Value["VALUE"];
						}
						break;
				}
			} while ($Temp = strtok("\r\n"));
			
			$gap = $key = 0;
			$returndata = array();
			foreach( $Data as $Data_key => $Data_val ) :
				foreach( $Data_val as $title => $data) :
					if ( isset($cftisexist[$title]) ) $tmp_parentSN = $cftisexist[$title];
					else $tmp_parentSN = count($returndata);
					switch ( $data["type"]) :
						case 'checkbox' :
							if ( isset($data["code"]) && is_numeric($data["code"]) ) :
								eval(stripcslashes($options['php'][$data["code"]]));
							else :
								if ( isset($data["value"]) ) $values = explode( '#', $data["value"] );
								if ( isset($data["valueLabel"]) ) $valueLabel = explode( '#', $data["valueLabel"] );
								if ( isset($data["default"]) ) $defaults = explode( '#', $data["default"] );
							endif;
							
							if ( !empty($valueLabel) ) $valueLabels = $valueLabel;

							if ( isset($defaults) && is_array($defaults) )
								foreach($defaults as $dkey => $dval)
									$defaults[$dkey] = trim($dval);

							$tmp = $key;
							$i = 0;
							if ( isset($values) && is_array($values) ) :
								foreach($values as $value) {
									$count_key = count($returndata);
									$Data[$Data_key][$title]["value"] = trim($value);
									$Data[$Data_key][$title]["originalValue"] = $data["value"];
									$Data[$Data_key][$title]['cftnum'] = $i;
									if ( isset($valueLabels[$i]) )
										$Data[$Data_key][$title]["valueLabel"] = trim($valueLabels[$i]);
									if ( $tmp!=$key )
										$Data[$Data_key][$title]["hideKey"] = true;
									if ( isset($defaults) && is_array($defaults) ) :
										if ( in_array(trim($value), $defaults) )
											$Data[$Data_key][$title]["checked"] = true;
										else
											unset($Data[$Data_key][$title]["checked"]);
									endif;
									$Data[$Data_key][$title]['parentSN'] = $tmp_parentSN+$gap;
									$returndata[$count_key] = $Data[$Data_key];
									$key++;
									$i++;
								}
							endif;
							break;
						default :
							if ( $data['type'] == 'fieldset_open' ) :
								$fieldset = array();
								if ( isset($_REQUEST[$this->sanitize_name($title)]) ) $fieldsetcounter = count($_REQUEST[$this->sanitize_name($title)])-1;
								else if ( isset($_REQUEST['post']) ) $fieldsetcounter = $this->get_post_meta( $_REQUEST['post'], $title, true )-1;
								else $fieldsetcounter = 0;
								if ( !empty($data['multiple']) ) : $fieldset_multiple = 1; endif;
							endif;
							if ( isset($fieldset) && is_array($fieldset) ) :
								if ( empty($tmp_parentSN2[$title]) ) $tmp_parentSN2[$title] = $tmp_parentSN;
							endif;
							if ( isset($data['multiple']) && $data['multiple'] == true && $data['type'] != 'checkbox' && $data['type'] != 'fieldset_open' && !isset($fieldset) ) :
								$counter = isset($_REQUEST[$this->sanitize_name($title)][$tmp_parentSN+$gap]) ? count($_REQUEST[$this->sanitize_name($title)][$tmp_parentSN+$gap]) : 0;
								if ( $data['type'] == 'file' && !empty($_FILES[$this->sanitize_name($title)]) ) $counter = (int)count($_FILES[$this->sanitize_name($title)]['name'][$tmp_parentSN+$gap])+1;
								if ( isset($_REQUEST['post_ID']) )	$org_counter = count($this->get_post_meta( $_REQUEST['post_ID'], $title ));
								else if ( isset($_REQUEST['post']) ) $org_counter = count($this->get_post_meta( $_REQUEST['post'], $title ));
								else $org_counter = 1;
								if ( !$counter ) :
									$counter = $org_counter;
									$counter++;
								else :
									if ( empty($_REQUEST[$this->sanitize_name($title)][$tmp_parentSN+$gap][$counter-1]) ) $counter--;
								endif;
								if ( !$org_counter ) $org_counter = 2;
								if ( isset($data['startNum']) && is_numeric($data['startNum']) && $data['startNum']>$counter ) $counter = $data['startNum'];
								if ( isset($data['endNum']) && is_numeric($data['endNum']) && $data['endNum']<$counter ) $counter = $data['endNum'];
								if ( $counter ) :
									for($i=0;$i<$counter; $i++) :
										$count_key = count($returndata);
										if ( $i!=0 ) $Data[$Data_key][$title]["hideKey"] = true;
										if ( $i!=0 ) unset($Data[$Data_key][$title]["label"]);
										$Data[$Data_key][$title]['cftnum'] = $i;
										$Data[$Data_key][$title]['parentSN'] = $tmp_parentSN+$gap;
										$returndata[$count_key] = $Data[$Data_key];
										if ( isset($fieldset) && is_array($fieldset) ) :
											$fieldset[] = $Data[$Data_key];
										endif;
									endfor;
								endif;
								if ( $counter != $org_counter ) :
									$gap += ($org_counter - $counter);
								endif;
							else :
								if ( !isset($cftisexist[$title]) && !isset($fieldset) ) $Data[$Data_key][$title]['parentSN'] = $tmp_parentSN+$gap;
								else $Data[$Data_key][$title]['parentSN'] = $tmp_parentSN;
								$returndata[] = $Data[$Data_key];
								if ( isset($fieldset) && is_array($fieldset) ) :
									$Data[$Data_key][$title]['parentSN'] = $tmp_parentSN2[$title];
									$fieldset[] = $Data[$Data_key];
								endif;
							endif;
							if ( $data['type'] == 'fieldset_close' && is_array($fieldset) ) :
								for($i=0;$i<$fieldsetcounter;$i++) :
									$returndata = array_merge($returndata, $fieldset);
								endfor;
								if ( isset($_REQUEST['post_ID']) ) $groupcounter = (int)$this->get_post_meta( $_REQUEST['post_ID'], $title, true );
								if ( !isset($groupcounter) || $groupcounter == 0 ) $groupcounter = $fieldsetcounter;
								if ( isset($_REQUEST[$this->sanitize_name($title)]) && $fieldset_multiple ) :
									$gap += ($groupcounter - count($_REQUEST[$this->sanitize_name($title)]))*count($fieldset);
									unset($fieldset_multiple);
								endif;
								unset($fieldset, $tmp_parentSN2);
							endif;
							unset($counter);
					endswitch;
					if ( !isset($cftisexist[$title]) ) $cftisexist[$title] = $Data[$Data_key][$title]['parentSN'];
				endforeach;
			endforeach;
			
			$cftnum = array();
			if ( is_array($returndata) ) :
				foreach( $returndata as $Data_key => $Data_val ) :
					foreach( $Data_val as $title => $data ) :
						if ( isset($cftnum[$title]) && is_numeric($cftnum[$title]) ) $cftnum[$title]++;
						else $cftnum[$title] = 0;
						$returndata[$Data_key][$title]['cftnum'] = $cftnum[$title];
					endforeach;
				endforeach;
			endif;
		}

		return $returndata;
	}

	function output_custom_field_values($attr) {
		global $post;
		$options = $this->get_custom_field_template_data();
		
		if ( empty($post->ID) ) $post_id = get_the_ID();
		else $post_id = $post->ID;

		if ( !isset($options['custom_field_template_before_list']) ) $options['custom_field_template_before_list'] = '<ul>';
		if ( !isset($options['custom_field_template_after_list']) ) $options['custom_field_template_after_list'] = '</ul>';
		if ( !isset($options['custom_field_template_before_value']) ) $options['custom_field_template_before_value'] = '<li>';
		if ( !isset($options['custom_field_template_after_value']) ) $options['custom_field_template_after_value'] = '</li>';

		if ( !empty($attr['post_id']) ) $this->format_post_id = $attr['post_id'];
		if ( empty($attr['post_id']) && $this->format_post_id ) $post_id = $this->format_post_id;

		extract(shortcode_atts(array(
			'post_id'   => $post_id,
			'template'  => 0,
			'format'    => '',
			'key'   => '',
			'single'    => false,
			'before_list' => $options['custom_field_template_before_list'],
			'after_list' => $options['custom_field_template_after_list'],
			'before_value' => $options['custom_field_template_before_value'],
			'after_value' => $options['custom_field_template_after_value'],
			'image_size' => '',
			'image_src' => false,
			'image_width' => false,
			'image_height' => false,
			'value_count' => false,
			'value' => ''
		), $attr));
		
		$metakey = $key;
		$output = '';
		if ( $metakey ) :
			if ( $value_count && $value ) :
				return number_format($options['value_count'][$metakey][$value]);
			endif;		
			$metavalue = $this->get_post_meta($post_id, $key, $single);
			if ( !is_array($metavalue) ) $metavalue = array($metavalue);
			if ( $before_list ) : $output = $before_list . "\n"; endif;
			foreach ( $metavalue as $val ) :
				if ( !empty($image_size) ) :
					if ( $image_src || $image_width || $image_height ) :
						list($src, $width, $height) = wp_get_attachment_image_src($val, $image_size);
						if ( $image_src ) : $val = $src; endif;
						if ( $image_width ) : $val = $width; endif;
						if ( $image_height ) : $val = $height; endif;
					else :
						$val = wp_get_attachment_image($val, $image_size);
					endif;
				endif;
				$output .= (isset($before_value) ? $before_value : '') . $val . (isset($after_value) ? $after_value : '') . "\n";
			endforeach;
			if ( $after_list ) : $output .= $after_list . "\n"; endif;
			return do_shortcode($output);
		endif;

		if ( is_numeric($format) && !empty($options['shortcode_format'][$format]) && $output = $options['shortcode_format'][$format] ) :
			$data = $this->get_post_meta($post_id);
			$output = stripcslashes($output);
			
			if( $data == null)
				return;

			$count = count($options['custom_fields']);
			if ( $count ) :
				for ($i=0;$i<$count;$i++) :
					$fields = $this->get_custom_fields( $i );
					foreach ( $fields as $field_key => $field_val ) :					
						foreach ( $field_val as $key => $val ) :
							$replace_val = '';
							if ( isset($data[$key]) && count($data[$key]) > 1 ) :
								if ( isset($val['sort']) && $val['sort'] == 'asc' ) :
									sort($data[$key]);
								elseif ( isset($val['sort']) && $val['sort'] == 'desc' ) :
									rsort($data[$key]);
								endif;
								if ( $before_list ) : $replace_val = $before_list . "\n"; endif;
								foreach ( $data[$key] as $val2 ) :
									$value = $val2;
									if ( isset($val['outputCode']) && is_numeric($val['outputCode']) ) :
										eval(stripcslashes($options['php'][$val['outputCode']]));
									endif;
									if ( isset($val['shortCode']) && $val['shortCode'] == true ) $value = do_shortcode($value);
									$replace_val .= $before_value . $value . $after_value . "\n";
								endforeach;
								if ( $after_list ) : $replace_val .= $after_list . "\n"; endif;
							elseif ( isset($data[$key]) && count($data[$key]) == 1 ) :
								$value = $data[$key][0];
								if ( isset($val['outputCode']) && is_numeric($val['outputCode']) ) :
									eval(stripcslashes($options['php'][$val['outputCode']]));
								endif;
								if ( isset($val['shortCode']) && $val['shortCode'] == true ) $value = do_shortcode($value);
								$replace_val = $value;
								if ( isset($val['singleList']) && $val['singleList'] == true ) :
									if ( $before_list ) : $replace_val = $before_list . "\n"; endif;
									$replace_val .= $before_value . $value . $after_value . "\n";
									if ( $after_list ) : $replace_val .= $after_list . "\n"; endif;
								endif;
							else :
								if ( isset($val['outputNone']) ) $replace_val = $val['outputNone'];
								else $replace_val = '';
							endif;
							if ( isset($options['shortcode_format_use_php'][$format]) )
								$output = $this->EvalBuffer($output);
								
							$key = preg_quote($key, '/');
							$replace_val = str_replace('\\', '\\\\', $replace_val); 
							$replace_val = str_replace('$', '\$', $replace_val); 
							$output = preg_replace('/\['.$key.'\]/', $replace_val, $output);
						endforeach;
					endforeach;
				endfor;
			endif;
		else :
			$fields = $this->get_custom_fields( $template );
					
			if( $fields == null)
				return;

			$output = '<dl class="cft cft'.$template.'">' . "\n";
			foreach ( $fields as $field_key => $field_val ) :					
				foreach ( $field_val as $key => $val ) :
					if ( isset($keylist[$key]) && $keylist[$key] == true ) break;
					$values = $this->get_post_meta( $post_id, $key );
					if ( $values ):
						if ( isset($val['sort']) && $val['sort'] == 'asc' ) :
							sort($values);
						elseif ( isset($val['sort']) && $val['sort'] == 'desc' ) :
							rsort($values);
						endif;
						if ( isset($val['output']) && $val['output'] == true ) :
							foreach ( $values as $num => $value ) :
								$value = str_replace('\\', '\\\\', $value); 
								if ( isset($val['outputCode']) && is_numeric($val['outputCode']) ) :
									eval(stripcslashes($options['php'][$val['outputCode']]));
								endif;
								if ( empty($value) && $val['outputNone'] ) $value = $val['outputNone'];
								if ( isset($val['shortCode']) && $val['shortCode'] == true ) $value = do_shortcode($value);			
								if ( !empty($val['label']) && !empty($options['custom_field_template_replace_keys_by_labels']) )
									$key_val = stripcslashes($val['label']);
								else $key_val = $key;
								if ( isset($val['hideKey']) && $val['hideKey'] != true && $num == 0 )
									$output .= '<dt>' . $key_val . '</dt>' . "\n";
								$output .= '<dd>' . $value . '</dd>' . "\n";
							endforeach;
						endif;
					endif;
					$keylist[$key] = true;
				endforeach;
			endforeach;
			$output .= '</dl>' . "\n";
		endif;

		return do_shortcode(stripcslashes($output));
	}
	
	function search_custom_field_values($attr) {
		global $post;
		$options = $this->get_custom_field_template_data();

		extract(shortcode_atts(array(
			'template'    => 0,
			'format'      => '',
			'search_label' => __('Search &raquo;', 'custom-field-template'),
			'button'      => true
		), $attr));
		
		if ( is_numeric($format) && $output = $options['shortcode_format'][$format] ) :
			$output = stripcslashes($output);
			$output = '<form method="get" action="'.get_option('home').'/" id="cftsearch'.(int)$format.'">' . "\n" . $output;

			$count = count($options['custom_fields']);
			if ( $count ) :
				for ($t=0;$t<$count;$t++) :
					$fields = $this->get_custom_fields( $t );
					foreach ( $fields as $field_key => $field_val ) :
						foreach ( $field_val as $key => $val ) :
							unset($replace);
							$replace[0] = $val;

							$search = array();
							if( isset($val['searchType']) ) eval('$search["type"] =' . stripslashes($val['searchType']));
							if( isset($val['searchValue']) ) eval('$search["value"] =' . stripslashes($val['searchValue']));
							if( isset($val['searchOperator']) ) eval('$search["operator"] =' . stripslashes($val['searchOperator']));
							if( isset($val['searchValueLabel']) ) eval('$search["valueLabel"] =' . stripslashes($val['searchValueLabel']));
							if( isset($val['searchDefault']) ) eval('$search["default"] =' . stripslashes($val['searchDefault']));
							if( isset($val['searchClass']) ) eval('$search["class"] =' . stripslashes($val['searchClass']));
							if( isset($val['searchSelectLabel']) ) eval('$search["selectLabel"] =' . stripslashes($val['searchSelectLabel']));
							
							foreach ( $search as $skey => $sval ) :
								$j = 1;
								foreach ( $sval as $sval2 ) :
									$replace[$j][$skey] = $sval2;
									$j++;
								endforeach;
							endforeach;
												
							foreach( $replace as $rkey => $rval ) :				
								$replace_val[$rkey] = "";
								$class = "";
								$default = array();
								switch ( $rval['type'] ) :
									case 'text':
									case 'textfield':
									case 'textarea':
										if ( !empty($rval['class']) ) $class = ' class="' . $rval['class'] . '"'; 
										$replace_val[$rkey] .= '<input type="text" name="cftsearch[' . rawurlencode($key) . '][' . $rkey . '][]" value="' . (isset($_REQUEST['cftsearch'][rawurlencode($key)][$rkey][0]) ? esc_attr($_REQUEST['cftsearch'][rawurlencode($key)][$rkey][0]) : '') . '"' . $class . ' />';
										break;		
									case 'checkbox':
										if ( !empty($rval['class']) ) $class = ' class="' . $rval['class'] . '"'; 
										$values = $valueLabel = array();
										if ( $rkey != 0 )
											$values = explode( '#', $rval['value'] );
										else
											$values = explode( '#', $rval['originalValue'] );
										$valueLabel = explode( '#', $rval['valueLabel'] );
										$default = explode( '#', $rval['default'] );
										if ( is_numeric($rval['searchCode']) ) :
											eval(stripcslashes($options['php'][$rval['searchCode']]));
										endif;
										if ( count($values) > 1 ) :
											$replace_val[$rkey] .= '<ul' . $class . '>';
											$j=0;
											foreach( $values as $metavalue ) :
												$checked = '';
												$metavalue = trim($metavalue);
												if ( is_array($_REQUEST['cftsearch'][rawurlencode($key)][$rkey]) ) :
													if ( in_array($metavalue, $_REQUEST['cftsearch'][rawurlencode($key)][$rkey]) )
														$checked = ' checked="checked"';
													else
														$checked = '';
												endif;
												if ( in_array($metavalue, $default) && !$_REQUEST['cftsearch'][rawurlencode($key)][$rkey] )
													$checked = ' checked="checked"';

												$replace_val[$rkey] .= '<li><label><input type="checkbox" name="cftsearch[' . rawurlencode($key) . '][' . $rkey . '][]" value="' . esc_attr($metavalue) . '"' . $class . $checked . '  /> ';			
												if ( $valueLabel[$j] ) $replace_val[$rkey] .= stripcslashes($valueLabel[$j]);
												else $replace_val[$rkey] .= stripcslashes($metavalue);
												$replace_val[$rkey] .= '</label></li>';
												$j++;
											endforeach;
											$replace_val[$rkey] .= '</ul>';
										else :
											if ( $_REQUEST['cftsearch'][rawurlencode($key)][$rkey][0] == esc_attr(trim($values[0])) )
												$checked = ' checked="checked"';
											$replace_val[$rkey] .= '<label><input type="checkbox" name="cftsearch[' . rawurlencode($key) . '][' . $rkey . '][]" value="' . esc_attr(trim($values[0])) . '"' . $class . $checked . ' /> ';			
											if ( $valueLabel[0] ) $replace_val[$rkey] .= stripcslashes(trim($valueLabel[0]));
											else $replace_val[$rkey] .= stripcslashes(trim($values[0]));
											$replace_val[$rkey] .= '</label>';
										endif;
										break;
									case 'radio':
										if ( !empty($rval['class']) ) $class = ' class="' . $rval['class'] . '"'; 
										$values = explode( '#', $rval['value'] );
										$valueLabel = explode( '#', $rval['valueLabel'] );
										$default = explode( '#', $rval['default'] );
										if ( is_numeric($rval['searchCode']) ) :
											eval(stripcslashes($options['php'][$rval['searchCode']]));
										endif;
										if ( count($values) > 1 ) :
											$replace_val[$rkey] .= '<ul' . $class . '>';
											$j=0;
											foreach ( $values as $metavalue ) :
												$checked = '';
												$metavalue = trim($metavalue);
												if ( is_array($_REQUEST['cftsearch'][rawurlencode($key)][$rkey]) ) :
													if ( in_array($metavalue, $_REQUEST['cftsearch'][rawurlencode($key)][$rkey]) )
														$checked = ' checked="checked"';
													else
														$checked = '';
												endif;
												if ( in_array($metavalue, $default) && !$_REQUEST['cftsearch'][rawurlencode($key)][$rkey] )
													$checked = ' checked="checked"';
												$replace_val[$rkey] .= '<li><label><input type="radio" name="cftsearch[' . rawurlencode($key) . '][' . $rkey . '][]" value="' . esc_attr($metavalue) . '"' . $class . $checked . ' /> ';			
												if ( $valueLabel[$j] ) $replace_val[$rkey] .= stripcslashes(trim($valueLabel[$j]));
												else $replace_val[$rkey] .= stripcslashes($metavalue);
												$replace_val[$rkey] .= '</label></li>';
												$j++;
											endforeach;
											$replace_val[$rkey] .= '</ul>';
										else :
											if ( $_REQUEST['cftsearch'][rawurlencode($key)][$rkey][0] == esc_attr(trim($values[0])) )
												$checked = ' checked="checked"';
											$replace_val[$rkey] .= '<label><input type="radio" name="cftsearch[' . rawurlencode($key) . '][]" value="' . esc_attr(trim($values[0])) . '"' . $class . $checked . ' /> ';			
											if ( $valueLabel[0] ) $replace_val[$rkey] .= stripcslashes(trim($valueLabel[0]));
											else $replace_val[$rkey] .= stripcslashes(trim($values[0]));
											$replace_val[$rkey] .= '</label>';
										endif;
										break;
									case 'select':
										if ( !empty($rval['class']) ) $class = ' class="' . $rval['class'] . '"'; 
										$values = explode( '#', $rval['value'] );
										$valueLabel = isset($rval['valueLabel']) ? explode( '#', $rval['valueLabel'] ) : array();
										$default = isset($rval['default']) ? explode( '#', $rval['default'] ) : array();
										$selectLabel= isset($rval['selectLabel']) ? $rval['selectLabel'] : '';

										if ( isset($rval['searchCode']) && is_numeric($rval['searchCode']) ) :
											eval(stripcslashes($options['php'][$rval['searchCode']]));
										endif;
										$replace_val[$rkey] .= '<select name="cftsearch[' . rawurlencode($key) . '][' . $rkey . '][]"' . $class . '>';
										$replace_val[$rkey] .= '<option value="">'.$selectLabel.'</option>';
										$j=0;
										foreach ( $values as $metaval ) :
											$metaval = trim($metaval);
											if ( in_array($metavalue, $default) && !$_REQUEST['cftsearch'][rawurlencode($key)][$rkey] )
													$checked = ' checked="checked"';

											if ( $_REQUEST['cftsearch'][rawurlencode($key)][$rkey][0] == $metaval ) $selected = ' selected="selected"';
											else $selected = "";
											$replace_val[$rkey] .= '<option value="' . esc_attr($metaval) . '"' . $selected . '>';			
											if ( $valueLabel[$j] )
												$replace_val[$rkey] .= stripcslashes(trim($valueLabel[$j]));
											else
												$replace_val[$rkey] .= stripcslashes($metaval);
											$replace_val[$rkey] .= '</option>' . "\n";
											$j++;
										endforeach;
										$replace_val[$rkey] .= '</select>' . "\n";
										break;
								endswitch;			
							endforeach;
						
							if ( isset($options['shortcode_format_use_php'][$format]) )
								$output = $this->EvalBuffer($output);
							$key = preg_quote($key, '/');
							$output = preg_replace('/\['.$key.'\](?!\[[0-9]+\])/', $replace_val[0], $output); 
							$output = preg_replace('/\['.$key.'\]\[([0-9]+)\](?!\[\])/e', '$replace_val[${1}]', $output);
						endforeach;
					endforeach;
				endfor;
			endif;

			if ( $button === true )
				$output .= '<p><input type="submit" value="' . $search_label . '" class="cftsearch_submit" /></p>' . "\n";
			$output .= '<input type="hidden" name="cftsearch_submit" value="1" />' . "\n";
			$output .= '</form>' . "\n";
		else :
			$fields = $this->get_custom_fields( $template );
	
			if ( $fields == null )
				return;

			$output = '<form method="get" action="'.get_option('home').'/" id="cftsearch'.(int)$format.'">' . "\n";
			foreach( $fields as $field_key => $field_val) :
				foreach( $field_val as $key => $val) :
					if ( isset($val['search']) && $val['search'] == true ) :
						if ( !empty($val['label']) && !empty($options['custom_field_template_replace_keys_by_labels']) )
							$label = stripcslashes($val['label']);
						else $label = $key;
						$output .= '<dl>' ."\n";
						if ( !isset($val['hideKey']) || $val['hideKey'] != true) :
							$output .= '<dt><label>' . $label . '</label></dt>' ."\n";
						endif;

						$class = "";
						switch ( $val['type'] ) :
							case 'text':
							case 'textfield':
							case 'textarea':
								if ( !empty($val['class']) ) $class = ' class="' . $val['class'] . '"'; 
								$output .= '<dd><input type="text" name="cftsearch[' . rawurlencode($key) . '][' . $key . '][]" value="' . (isset($_REQUEST['cftsearch'][rawurlencode($key)][0][0]) ? esc_attr($_REQUEST['cftsearch'][rawurlencode($key)][0][0]) : '') . '"' . $class . ' /></dd>';
								break;		
							case 'checkbox':
								$checked = '';
								if ( !empty($val['class']) ) $class = ' class="' . $val['class'] . '"';
								if ( isset($_REQUEST['cftsearch'][rawurlencode($key)]) && is_array($_REQUEST['cftsearch'][rawurlencode($key)]) ) 
									foreach ( $_REQUEST['cftsearch'][rawurlencode($key)] as $values )
										if ( $val['value'] == $values[0] ) $checked = ' checked="checked"';
								$output .= '<dd><label><input type="checkbox" name="cftsearch[' . rawurlencode($key) . '][' . $key . '][]" value="' . esc_attr($val['value']) . '"' . $class . $checked . ' /> ';
								if ( !empty($val['valueLabel']) )
									$output .= stripcslashes($val['valueLabel']);
								else
									$output .= stripcslashes($val['value']);
								$output .= '</label></dd>' . "\n";
								break;
							case 'radio':
								if ( !empty($val['class']) ) $class = ' class="' . $val['class'] . '"'; 
								$values = explode( '#', $val['value'] );
								$valueLabel = isset($val['valueLabel']) ? explode( '#', $val['valueLabel'] ) : '';
								$i=0;
								foreach ( $values as $metaval ) :
									$checked = '';
									$metaval = trim($metaval);
									if ( isset($_REQUEST['cftsearch'][rawurlencode($key)][0][0]) && $_REQUEST['cftsearch'][rawurlencode($key)][0][0] == $metaval ) $checked = 'checked="checked"';
									$output .= '<dd><label>' . '<input type="radio" name="cftsearch[' . rawurlencode($key) . '][' . $key . '][]" value="' . esc_attr($metaval) . '"' . $class . $checked . ' /> ';			
									if ( !empty($val['valueLabel']) )
										$output .= stripcslashes(trim($valueLabel[$i]));
									else
										$output .= stripcslashes($metaval);
									$i++;
									$output .= '</label></dd>' . "\n";
								endforeach;
								break;
							case 'select':
								if ( !empty($val['class']) ) $class = ' class="' . $val['class'] . '"'; 
								$values = explode( '#', $val['value'] );
								$valueLabel = isset($val['valueLabel']) ? explode( '#', $val['valueLabel'] ) : '';
								$output .= '<dd><select name="cftsearch[' . rawurlencode($key) . '][' . $key . '][]"' . $class . '>';
								$output .= '<option value=""></option>';
								$i=0;
								foreach ( $values as $metaval ) :
									$selected = '';
									$metaval = trim($metaval);
									if ( isset($_REQUEST['cftsearch'][rawurlencode($key)][0][0]) && $_REQUEST['cftsearch'][rawurlencode($key)][0][0] == $metaval ) $selected = 'selected="selected"';
									else $selected = "";
									$output .= '<option value="' . esc_attr($metaval) . '"' . $selected . '>';			
									if ( !empty($val['valueLabel']) )
										$output .= stripcslashes(trim($valueLabel[$i]));
									else
										$output .= stripcslashes($metaval);
									$output .= '</option>' . "\n";
									$i++;
								endforeach;
								$output .= '</select></dd>' . "\n";
								break;
						endswitch;
						$output .= '</dl>' ."\n";
					endif;
				endforeach;
			endforeach;
			if ( $button == true )
				$output .= '<p><input type="submit" value="' . $search_label . '" class="cftsearch_submit" /></p>' . "\n";
			$output .= '<input type="hidden" name="cftsearch_submit" value="1" /></p>' . "\n";
			$output .= '</form>' . "\n";
		endif;
		
		return do_shortcode(stripcslashes($output));
	}
	
	function custom_field_template_posts_where($where) {
		global $wp_query, $wp_version, $wpdb;
		$options = $this->get_custom_field_template_data();
		
		if ( isset($_REQUEST['no_is_search']) ) :
			$wp_query->is_search = '';
		else:
			$wp_query->is_search = 1;
		endif;
		$wp_query->is_page = '';
		$wp_query->is_singular = '';
		
		$original_where = $where;

		$where = '';

		$count = count($options['custom_fields']);
		if ( $count ) :
			for ($i=0;$i<$count;$i++) :
				$fields = $this->get_custom_fields( $i );
				foreach ( $fields as $field_key => $field_val ) :
					foreach ( $field_val as $key => $val ) :
						$replace[$key] = $val;
						$search = array();
						if( isset($val['searchType']) ) eval('$search["type"] =' . stripslashes($val['searchType']));
						if( isset($val['searchValue']) ) eval('$search["value"] =' . stripslashes($val['searchValue']));
						if( isset($val['searchOperator']) ) eval('$search["operator"] =' . stripslashes($val['searchOperator']));
						
						foreach ( $search as $skey => $sval ) :
							$j = 1;
							foreach ( $sval as $sval2 ) :
								$replace[$key][$j][$skey] = $sval2;
								$j++;
							endforeach;
						endforeach;
					endforeach;
				endforeach;
			endfor;
		endif;
			
		if ( is_array($_REQUEST['cftsearch']) ) :
			foreach ( $_REQUEST['cftsearch'] as $key => $val ) :
				$key = rawurldecode($key);
				if ( is_array($val) ) :
					$ch = 0;
					foreach( $val as $key2 => $val2 ) :
						if ( is_array($val2) ) :
							foreach( $val2 as $val3 ) :
								if ( $val3 ) :
									if ( $ch == 0 ) : $where .= ' AND (';
									else :
										if ( $replace[$key][$key2]['type'] == 'checkbox' || !$replace[$key][$key2]['type'] ) $where .= ' OR ';
										else $where .= ' AND ';
									endif;
									if ( !isset($replace[$key][$key2]['operator']) ) $replace[$key][$key2]['operator'] = '';
									switch( $replace[$key][$key2]['operator'] ) :
										case '<=' :
										case '>=' :
										case '<' :
										case '>' :
										case '=' :
										case '<>' :
										case '<=>':
											if ( is_numeric($val3) ) :
												$where .=  $wpdb->prepare(" ID IN (SELECT `" . $wpdb->postmeta . "`.post_id FROM `" . $wpdb->postmeta . "` WHERE (`" . $wpdb->postmeta . "`.meta_key = %s AND `" . $wpdb->postmeta . "`.meta_value " . $replace[$key][$key2]['operator'] . " %d) ) ", $key, trim($val3));
											else :
												$where .= $wpdb->prepare(" ID IN (SELECT `" . $wpdb->postmeta . "`.post_id FROM `" . $wpdb->postmeta . "` WHERE (`" . $wpdb->postmeta . "`.meta_key = %s AND `" . $wpdb->postmeta . "`.meta_value " . $replace[$key][$key2]['operator'] . " %s) ) ", $key, trim($val3));
											endif;
											break;
										default :
											$where .= $wpdb->prepare(" ID IN (SELECT `" . $wpdb->postmeta . "`.post_id FROM `" . $wpdb->postmeta . "` WHERE (`" . $wpdb->postmeta . "`.meta_key = %s AND `" . $wpdb->postmeta . "`.meta_value LIKE %s) ) ", $key, '%'.trim($val3).'%');
											break;
									endswitch;
									$ch++;
								endif;
							endforeach;
						endif;
					endforeach;
					if ( $ch>0 ) $where .= ') ';
				endif;
			endforeach;
		endif;
		
		if ( isset($_REQUEST['s']) ) :
			$where .= ' AND (';
			if ( function_exists('mb_split') ) :
				$s = mb_split('\s', $_REQUEST['s']);
			else:
				$s = split('\s', $_REQUEST['s']);
			endif;
			$i=0;
			foreach ( $s as $v ) :
				if ( !empty($v) ) :
					if ( $i>0 ) $where .= ' AND ';
					$where .= $wpdb->prepare(" ID IN (SELECT `" . $wpdb->postmeta . "`.post_id FROM `" . $wpdb->postmeta . "` WHERE (`" . $wpdb->postmeta . "`.meta_value LIKE %s) ) ", '%'.trim($v).'%');
					$i++;
				endif;
			endforeach;
			$where .= $wpdb->prepare(" OR ((`" . $wpdb->posts . "`.post_title LIKE %s) OR (`" . $wpdb->posts . "`.post_content LIKE %s))", '%'.trim($_REQUEST['s']).'%', '%'.trim($_REQUEST['s']).'%');
			$where .= ') ';
		endif;

		if ( isset($_REQUEST['cftcategory_in']) && is_array($_REQUEST['cftcategory_in']) ) :
			$ids = get_objects_in_term($_REQUEST['cftcategory_in'], 'category');
			if ( is_array($ids) && count($ids) > 0 ) :
				$in_posts = "'" . implode("', '", $ids) . "'";
				$where .= " AND ID IN (" . $in_posts . ")";
			endif;
			$where .= " AND `" . $wpdb->posts . "`.post_type = 'post'"; 
		endif;
		if ( isset($_REQUEST['cftcategory_not_in']) && is_array($_REQUEST['cftcategory_not_in']) ) :
			$ids = get_objects_in_term($_REQUEST['cftcategory_not_in'], 'category');
			if ( is_array($ids) && count($ids) > 0 ) :
				$in_posts = "'" . implode("', '", $ids) . "'";
				$where .= " AND ID NOT IN (" . $in_posts . ")";
			endif;
		endif;
		
		if ( !empty($_REQUEST['post_type']) ) :
			$where .= $wpdb->prepare(" AND `" . $wpdb->posts . "`.post_type = %s", trim($_REQUEST['post_type'])); 
		endif;
				
		if ( !empty($_REQUEST['no_is_search']) ) :
			$where .= " AND `".$wpdb->posts."`.post_status = 'publish'";
		else :
			$where .= " AND `".$wpdb->posts."`.post_status = 'publish' GROUP BY `".$wpdb->posts."`.ID";
		endif;
				
		return $where;
	}

	function custom_field_template_posts_join($sql) {
		if ( !empty($_REQUEST['orderby']) && !in_array($_REQUEST['orderby'], array('post_author', 'post_date', 'post_title', 'post_modified', 'menu_order', 'post_parent', 'ID')) ):
			if ( (strtoupper($_REQUEST['order']) == 'ASC' || strtoupper($_REQUEST['order']) == 'DESC') ) :
				global $wpdb;

				$sql = $wpdb->prepare(" LEFT JOIN `" . $wpdb->postmeta . "` AS meta ON (`" . $wpdb->posts . "`.ID = meta.post_id AND meta.meta_key = %s)", $_REQUEST['orderby']); 
				return $sql;
			endif;
		endif;
	}

	function custom_field_template_posts_orderby($sql) {
		global $wpdb;

		if ( empty($_REQUEST['order']) || ((strtoupper($_REQUEST['order']) != 'ASC') && (strtoupper($_REQUEST['order']) != 'DESC')) )
			$_REQUEST['order'] = 'DESC';

		if ( !empty($_REQUEST['orderby']) ) :
			if ( in_array($_REQUEST['orderby'], array('post_author', 'post_date', 'post_title', 'post_modified', 'menu_order', 'post_parent', 'ID')) ):
				$sql = "`" . $wpdb->posts . "`." . $_REQUEST['orderby'] . " " . $_REQUEST['order'];
			elseif ( $_REQUEST['orderby'] == 'rand' ):
				$sql = "RAND()";
			else:
				if ( !empty($_REQUEST['cast']) && in_array($_REQUEST['cast'], array('binary', 'char', 'date', 'datetime', 'signed', 'time', 'unsigned')) ) :
					$sql = " CAST(meta.meta_value AS " . $_REQUEST['cast'] . ") " . $_REQUEST['order'];
				else :
					$sql = " meta.meta_value " . $_REQUEST['order'];
				endif;
			endif;

			return $sql;
		endif;

		$sql = "`" . $wpdb->posts . "`.post_date " . $_REQUEST['order'];
		return $sql;
	}
	
	function custom_field_template_post_limits($sql_limit) {
		global $wp_query;

		if ( !$sql_limit ) return;
		list($offset, $old_limit) = explode(',', $sql_limit);
		$limit = (int)$_REQUEST['limit'];
		if ( !$limit )
			$limit = trim($old_limit);
		$wp_query->query_vars['posts_per_page'] = $limit;
		$offset = ($wp_query->query_vars['paged'] - 1) * $limit;
		if ( $offset < 0 ) $offset = 0;

		return ( $limit ? "LIMIT $offset, $limit" : '' );
	}
	
	function get_preview_id( $post_id ) {
		global $post;
		$preview_id = 0;
		if ( isset($post) && $post->ID == $post_id && is_preview() && $preview = wp_get_post_autosave( $post->ID ) ) :
			$preview_id = $preview->ID;
		endif;
		return $preview_id;
	}
	
	function get_preview_postmeta( $return, $post_id, $meta_key, $single ) {
	    if ( $preview_id = $this->get_preview_id( $post_id ) ) :
	   	    if ( $post_id != $preview_id ) :
        	    $return = $this->get_post_meta( $preview_id, $meta_key, $single );
				/*if ( empty($return) && !empty($post_id) ) :
        	  		$return = $this->get_post_meta( $post_id, $meta_key, $single );
				endif;*/
        	endif;
    	endif;
    	return $return;
	}
	
	function EvalBuffer($string) {
		ob_start();
		eval('?>'.$string);
		$ret = ob_get_contents();
		ob_end_clean();
		return $ret;
	}
	
	function set_value_count($key, $value, $id) {
		global $wpdb;
		
		if ( $id ) $where = " AND `". $wpdb->postmeta."`.post_id<>".$id;
		$query = $wpdb->prepare("SELECT COUNT(meta_id) FROM `". $wpdb->postmeta."` WHERE `". $wpdb->postmeta."`.meta_key = %s AND `". $wpdb->postmeta."`.meta_value = %s $where;", $key, $value);
		$count = $wpdb->get_var($query);
		return (int)$count;
	}
	
	function get_value_count($key = '', $value = '') {
		$options = $this->get_custom_field_template_data();
		
		if ( $key && $value ) :
			return $options['value_count'][$key][$value];
		else:
			return $options['value_count'];
		endif; 
	}
	
	function custom_field_template_delete_post($post_id) {
		global $wpdb;
		$options = $this->get_custom_field_template_data();
		
	    if ( is_numeric($post_id) )
			$id = !empty($options['posts'][$post_id]) ? $options['posts'][$post_id] : '';
		
		if ( is_numeric($id) ) :
			$fields = $this->get_custom_fields($id);
		
			if ( $fields == null )
				return;
					
			foreach( $fields as $field_key => $field_val) :
				foreach( $field_val as $title	=> $data) :
					$name = $this->sanitize_name( $title );
					$title = esc_sql(stripcslashes(trim($title)));
					$value = $this->get_post_meta($post_id, $title);
					if ( is_array($value) ) :
						foreach ( $value as $val ) :
							if ( $data['valueCount'] == true ) :
								$count = $this->set_value_count($title, $val, '')-1;
								if ( $count<=0 )
									unset($options['value_count'][$title][$val]);
								else
									$options['value_count'][$title][$val] = $count;
							endif;
						endforeach;
					else :
						if ( $data['valueCount'] == true ) :
							$count = $this->set_value_count($title, $value, '')-1;
							if ( $count<=0 )
								unset($options['value_count'][$title][$value]);
							else
								$options['value_count'][$title][$value] = $count;
						endif;
					endif;
				endforeach;
			endforeach;
		endif;
		update_option('custom_field_template_data', $options);
	}
	
	function custom_field_template_rebuild_value_counts() {
		global $wpdb;
		$options = $this->get_custom_field_template_data();
		unset($options['value_count']);
		set_time_limit(0);

		if ( is_array($options['custom_fields']) ) :
			for($j=0;$j<count($options['custom_fields']);$j++) :
		
				$fields = $this->get_custom_fields($j);
		
				if ( $fields == null )
					return;
					
				foreach( $fields as $field_key => $field_val) :
					foreach( $field_val as $title	=> $data) :
						$name = $this->sanitize_name( $title );
						$title = esc_sql(stripcslashes(trim($title)));
						if ( $data['valueCount'] == true ) :
							$query = $wpdb->prepare("SELECT COUNT(meta_id) as meta_count, `". $wpdb->postmeta."`.meta_value FROM `". $wpdb->postmeta."` WHERE `". $wpdb->postmeta."`.meta_key = %s GROUP BY `". $wpdb->postmeta."`.meta_value;", $title);
							$result = $wpdb->get_results($query, ARRAY_A);
							if ( $result ) :
								foreach($result as $val) :
									$options['value_count'][$title][$val['meta_value']] = $val['meta_count'];
								endforeach;
							endif;
						endif;
					endforeach;
				endforeach;
			endfor;
		endif;
		update_option('custom_field_template_data', $options);
	}
	
	function custom_field_template_wp_post_revision_fields($fields) {
		$fields['cft_debug_preview'] = 'cft_debug_preview';
		return $fields;	
	}
	
	function custom_field_template_edit_form_after_title() {
		echo '<input type="hidden" name="cft_debug_preview" value="cft_debug_preview" />';
	}
}

if ( !function_exists('esc_html') ) :
function esc_html( $text ) {
	$safe_text = wp_specialchars( $safe_text, ENT_QUOTES );
	return apply_filters( 'esc_html', $safe_text, $text );
}
function esc_attr( $text ) {
	return attribute_escape($text);
}
function esc_url( $url, $protocols = null ) {
	return clean_url( $url, $protocols, 'display' );
}
endif;

$custom_field_template = new custom_field_template();
?>