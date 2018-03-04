<?php
/*
Plugin Name: Auto Highslide
Plugin URI: http://showfom.com/auto-hishslide-wordpress-plugin/
Description: This plugin automatically add HighSlide Image Effect in your blog and You don't Need To Change Anything! If you want to use other effect of HighSlide , please use <a href="http://wordpress.org/extend/plugins/highslide4wp/">HighSlide4WP</a> with <a href="http://wordpress.org/extend/plugins/add-highslide/">Add Highslide</a>.
Author: Showfom 
Author URI: http://showfom.com
Version: 1.0
Put in /wp-content/plugins/ of your Wordpress installation
*/
/* Add HighSlide Image Code */
add_filter('the_content', 'addhighslideclass_replace');
function addhighslideclass_replace ($content)
{   global $post;
	$pattern = "/<a(.*?)href=('|\")([^>]*).(bmp|gif|jpeg|jpg|png)('|\")(.*?)>(.*?)<\/a>/i";
    $replacement = '<a$1href=$2$3.$4$5 class="highslide-image" onclick="return hs.expand(this);"$6>$7</a>';
    $content = preg_replace($pattern, $replacement, $content);
    return $content;
}
/* Add HighSlide */
function highslide_head() {
	print('
<link rel="stylesheet" href="'.get_bloginfo('wpurl').'/wp-content/plugins/auto-highslide/highslide/highslide.css" type="text/css" />
<script type="text/javascript" src="'.get_bloginfo('wpurl').'/wp-content/plugins/auto-highslide/highslide/highslide-with-html.packed.js"></script>
<script type="text/javascript">
jQuery(document).ready(function($) {
    hs.graphicsDir = "'.get_bloginfo('wpurl').'/wp-content/plugins/auto-highslide/highslide/graphics/";
    hs.outlineType = "rounded-white";
    hs.dimmingOpacity = 0.8;
    hs.outlineWhileAnimating = true;
    hs.showCredits = false;
    hs.captionEval = "this.thumb.alt";
    hs.numberPosition = "caption";
    hs.align = "center";
    hs.transitions = ["expand", "crossfade"];
    hs.addSlideshow({
        interval: 5000,
        repeat: true,
        useControls: true,
        fixedControls: "fit",
        overlayOptions: {
            opacity: 0.75,
            position: "bottom center",
            hideOnMouseOut: true

        }

    });
});
</script>
	');
}
add_action('wp_head', 'highslide_head');
?>