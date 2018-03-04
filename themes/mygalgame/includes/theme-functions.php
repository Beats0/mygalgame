<?php
/**
 * Theme-Functions 主要函数
 *
 * @package     ZanBlog
 * @subpackage  Include
 * @since       2.1.0
 * @author      YEAHZAN
 */


// 注册加载JS & CSS文件
add_action('wp_enqueue_scripts', 'zan_scripts_styles');

// 设定后台特色图像
add_theme_support( 'post-thumbnails' );

// 隐藏admin bar
add_filter( 'show_admin_bar', '__return_false' );

// 开启链接管理（包括友情链接）
add_filter( 'pre_option_link_manager_enabled', '__return_true' );


/**
 * ZanBlog 自定义子菜单类
 *
 * @since Zanblog 2.0.0
 *
 * @return void
 */
class Zan_Nav_Menu extends Walker_Nav_Menu {

  function start_lvl( &$output, $depth = 0, $args = array() ) {
    $indent  = str_repeat( "\t", $depth );
    $output .= "\n$indent<ul class=\"dropdown-menu\">\n";

    return $output;
  }
}


/**
 * 注册、加载CSS & JS文件
 *
 * @since Zanblog 2.0.0
 *
 * @return void
 */
function zan_scripts_styles() {

// js
  // jquery.min.js
  // wp_enqueue_script( 'jquery-min', get_template_directory_uri() . '/ui/js/jquery.min.js', array('jquery'), '3.3.1');  

  // bootstrap.js
  wp_enqueue_script( 'bootstrap-script', get_template_directory_uri() . '/ui/js/bootstrap.js', array('jquery'), '3.0.0');

  // jquery.icheck.min.js
  wp_enqueue_script( 'icheck-script', get_template_directory_uri() . '/ui/js/jquery.icheck.min.js', array('jquery'));

  // jquery.validate.js
  wp_enqueue_script( 'validate-script', get_template_directory_uri() . '/ui/js/jquery.validate.js', array('jquery'), '1.9.0' );
 
  wp_enqueue_script( 'lazyload-script', get_template_directory_uri() . '/ui/js/jquery.lazyload.min.js', array('jquery'), '1.9.3' );

  // zanblog.js
  // wp_enqueue_script( 'zanblog-script', get_template_directory_uri() . '/ui/js/zanblog.js', array('jquery'), '2.1.0' );
  
  // myblog.js
  wp_enqueue_script( 'myblog', get_template_directory_uri() . '/ui/js/myblog.js', array('jquery'), '1.0.1' );
   
  // custom.js 自定义js
  wp_enqueue_script( 'custom-script', get_template_directory_uri() . '/ui/js/custom.js', array('jquery'), '2.1.0' );


// css

  wp_enqueue_style( 'bootstrapUI', get_template_directory_uri() . '/ui/css/bootstrapUI.css', array(), '3.0.0');

  wp_enqueue_style( 'fontawesome-style', get_template_directory_uri() . '/ui/font-awesome/css/font-awesome.min.css', array(), '4.0.1');

  wp_enqueue_style( 'icheck-style', get_template_directory_uri() . '/ui/css/flat/red.css', array());

  // core.css
  // wp_enqueue_style( 'custom-style', get_template_directory_uri() . '/ui/css/core.css', array(), '2.1.0' );

  // myblog.css
  wp_enqueue_style( 'custom-style', get_template_directory_uri() . '/ui/css/myblog.css', array(), '2.1.0' );
  
  wp_enqueue_style( 'zanblog-style', get_stylesheet_uri(), array(), '2.1.0' );

  wp_enqueue_style( 'custom-style', get_template_directory_uri() . '/ui/css/custom.css', array(), '2.1.0' );
}


/**
 * 获取最热文章
 *
 * @since 2.1.0
 * @return array [最热文章数组]
 */
function zan_get_hotest_posts($num) {
  $args = array(
    'posts_per_page'   => $num,
    'offset'           => 0,
    'category'         => '',
    'orderby'          => 'comment_count',
    'order'            => 'DESC',
    'include'          => '',
    'exclude'          => '',
    'meta_key'         => '',
    'meta_value'       => '',
    'post_type'        => 'post',
    'post_mime_type'   => '',
    'post_parent'      => '',
    'post_status'      => 'publish',
    'suppress_filters' => true
  );

  return get_posts($args);
}

/**
 * 获取最新评论（排除作者评论）
 *
 * @since 2.1.0
 * @return array [最新评论数组]
 */
function zan_get_latest_comments($num) {
  $args = array(
    'author_email' => '',
    'ID' => '',
    'karma' => '',
    'number' => $num,
    'offset' => '',
    'orderby' => 'comment_date',
    'order' => 'DESC',
    'parent' => '',
    'post_id' => 0,
    'post_author' => '',
    'post_name' => '',
    'post_parent' => '',
    'post_status' => 'publish',
    'post_type' => '',
    'status' => 'approve',
    'type' => 'comment',
    'user_id' => '',
    'search' => '',
    'count' => false,
    'meta_key' => '',
    'meta_value' => '',
    'meta_query' => '',
  ); 

  return get_comments($args);
}

/**
 * 获取最新文章
 *
 * @since 2.1.0
 * @return array [最新文章数组]
 */
function zan_get_latest_posts($num) {
  $args = array(
    'posts_per_page'   => $num,
    'offset'           => 0,
    'category'         => '',
    'orderby'          => 'post_date',
    'order'            => 'DESC',
    'include'          => '',
    'exclude'          => '',
    'meta_key'         => '',
    'meta_value'       => '',
    'post_type'        => 'post',
    'post_mime_type'   => '',
    'post_parent'      => '',
    'post_status'      => 'publish',
    'suppress_filters' => true
  );

  return get_posts($args);
}

/**
 * 获取随机文章
 *
 * @since 2.1.0
 * @return array [最新文章数组]
 */
function zan_get_rand_posts($num) {
  $args = array(
    'posts_per_page'   => $num,
    'offset'           => 0,
    'category'         => '',
    'orderby'          => 'rand',
    'order'            => 'DESC',
    'include'          => '',
    'exclude'          => '',
    'meta_key'         => '',
    'meta_value'       => '',
    'post_type'        => 'post',
    'post_mime_type'   => '',
    'post_parent'      => '',
    'post_status'      => 'publish',
    'suppress_filters' => true
  );

  return get_posts($args);
}


/**
 * 字符串截取
 *
 * @since 2.1.0
 * @return string
 */
function zan_cut_string($string, $sublen, $start = 0, $code = 'UTF-8') {
  if($code == 'UTF-8') {
    $pa = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/";
    preg_match_all($pa, $string, $t_string);
    if(count($t_string[0]) - $start > $sublen) return join('', array_slice($t_string[0], $start, $sublen)) . "...";
    return join('', array_slice($t_string[0], $start, $sublen));
  } else {
    $start = $start * 2;
    $sublen = $sublen * 2;
    $strlen = strlen($string);
    $tmpstr = '';  

    for($i = 0; $i < $strlen; $i++) {
      if($i >= $start && $i < ($start + $sublen)) {
        if(ord(substr($string, $i, 1)) > 129) $tmpstr .= substr($string, $i, 2);
        else $tmpstr .= substr($string, $i, 1);
      } 
      if(ord(substr($string, $i, 1)) > 129) $i++;
    }
    if(strlen($tmpstr) < $strlen ) $tmpstr .= "...";
    return $tmpstr;
  }
}

/**
 * 分页功能（异步加载或自然分页）
 *
 * @since Zanblog 2.1.0
 *
 * @return void.
 */
function zan_page($trigger) {   

  global  $paged;

  if(empty($paged)) $paged = 1;  

  $next = $paged + 1;   
    
  if($trigger == 'auto') {
    echo "<a id='load-more' class='btn btn-inverse-primary btn-block' load-data='努力加载中...' href='" . get_pagenum_link($next) . "'><i></i> <attr>加载更多</attr></a>";

  } elseif($trigger == "manual") {
    show_paginate();

  } else {
    echo "<div class='alert alert-danger'><i class='icon-warning-sign'></i> 请输入正确的触发值（auto或者manual）</div>";
  }
} 

// 自然分页
if ( !function_exists( 'paginate' ) ):
  function paginate( $args = null ) {
    $range_gap = 3;         
    if (get_option( 'zan_paginate_num' ) != '' && intval( get_option( 'zan_paginate_num' ) ) > 0) {
      $range_gap = intval( get_option( 'zan_paginate_num' ) );
    }        
    $defaults = array( 'page'=>null, 'pages'=>null, 'range'=>$range_gap, 'gap'=>$range_gap, 'anchor'=>1, 'echo'=>1 );        
    $r = wp_parse_args( $args, $defaults );
    extract($r, EXTR_SKIP);       
    if ( !$page && !$pages ) {
      global $wp_query;           
      $page = get_query_var( 'paged' );
      $page = ! empty( $page ) ? intval( $page ) : 1;            
      $posts_per_page = intval( get_query_var( 'posts_per_page' ) );
      $pages = intval( ceil( $wp_query->found_posts / $posts_per_page ) );
    }
    
    $output = "";
    if ( $pages > 1 ) {
      $ellipsis = "<li><span>...</span></li>";            
      $min_links = $range * 2 + 1;
      $block_min = min( $page - $range, $pages - $min_links );
      $block_high = max( $page + $range, $min_links );
      $left_gap = ( ( $block_min - $anchor - $gap ) > 0 ) ? true : false;
      $right_gap = ( ( $block_high + $anchor + $gap ) < $pages ) ? true : false;            
      if ( $left_gap && !$right_gap ) {
        $output .= sprintf( '%s%s%s', paginate_loop( 1, $anchor ), $ellipsis, paginate_loop( $block_min, $pages, $page ) );
      } else if ( $left_gap && $right_gap ) {
        $output .= sprintf( '%s%s%s%s%s', paginate_loop( 1, $anchor ), $ellipsis, paginate_loop( $block_min, $block_high, $page ), $ellipsis, paginate_loop( ( $pages - $anchor + 1 ), $pages ) );
      } else if ( $right_gap && !$left_gap ) {
        $output .= sprintf( '%s%s%s', paginate_loop( 1, $block_high, $page ), $ellipsis, paginate_loop( ( $pages - $anchor + 1 ), $pages ) );
      } else {
        $output .= paginate_loop( 1, $pages, $page );
      }
    }        
    if ( $echo ) {
      echo $output;
    }       
    return $output;
  }
endif;
if ( !function_exists( 'paginate_loop' ) ):
  function paginate_loop( $start, $max, $page = 0 ) {
    $output = "";
    for ( $i = $start; $i <= $max; $i++ ) {
      $output .= ( $page === intval( $i ) ) ? "<li class='active'><span>$i</span></li>" : "<li><a href='".get_pagenum_link($i)."'>$i</a></li>";
    }
    return $output;
  }
endif;
if ( !function_exists( 'show_paginate' ) ):
  function show_paginate() {
?>
<div id="zan-page" class="clearfix">
  <ul class="pagination pagination-zan pull-right">
    <?php
      echo "<li>";
      previous_posts_link( __( '&laquo;', '' ), 0 );
      echo "</li>";
      if ( function_exists( "paginate" ) )  paginate();

      echo "<li>";
      next_posts_link( __( '&raquo;', '' ), 0);
      echo "</li>";
      wp_link_pages();
    ?>
  </ul>
</div>
<?php
}
endif;


/**
 * 获取评论列表
 *
 * @since 2.1.0
 * @return array [评论列表]
 */
function zan_get_commments_list($size) {
  $args = array(
    'walker'            => null,
    'max_depth'         => '',
    'style'             => 'ol',
    'callback'          => null,
    'end-callback'      => null,
    'type'              => 'all',
    'reply_text'        => '回复',
    'page'              => '',
    'avatar_size'       => $size,
    'reverse_top_level' => null,
    'reverse_children'  => '',
    'format'            => 'html5',
    'short_ping'        => false,
    'echo'              => true 
  );

  return wp_list_comments($args);
}

/**
 * 获取评论分页
 *
 * @since 2.1.0
 * @return array [评论分页]
 */
function zan_comments_pagination() {
  $args = array(
    'prev_text'    => __( '«' ),
    'next_text'    => __( '»' )
  );

  return paginate_comments_links($args);
}

/**
 * 评论表单
 *
 * @since 2.1.0
 * @return array [自定义表单]
 */
function zan_comments_form() {
  $args = array(
    'title_reply'          => '<i class="fa fa-pencil"></i> 欢迎留言',
    'title_reply_to'       => __( '回复 %s' ),
    'cancel_reply_link'    => __( '取消回复' ),
    'fields'               => array(
                        'author' => '<div class="row"><div class="col-sm-4"><div class="input-group"><span class="input-group-addon"><i class="fa fa-user"></i></span><input type="text" name="author" id="author" placeholder="* 昵称"></div></div>',
                        'email'  => '<div class="col-sm-4"><div class="input-group"><span class="input-group-addon"><i class="fa fa-envelope-o"></i></span><input type="text" name="email" id="email" placeholder="* 邮箱"></div></div>',
                        'url'    => '<div class="col-sm-4"><div class="input-group"><span class="input-group-addon"><i class="fa fa-link"></i></span><input type="text" name="url" id="url" placeholder="网站"></div></div></div>'
    ),
    'comment_field'        => '<textarea id="comment" placeholder="赶快发表你的见解吧！" name="comment" cols="45" rows="8" aria-required="true"></textarea>',
    'comment_notes_before' => '<div id="commentform-error" class="alert hidden"></div>',
    'comment_notes_after' => ''

  );
  return comment_form($args);
}

/**
 * 彩色云标签
 *
 * @since Zanblog 2.0.0
 *
 * @return tags.
 */
function zan_color_cloud($text) { 
  $text = preg_replace_callback('|<a (.+?)>|i', 'zan_color_cloud_callback', $text); 
  return $text; 
} 

function zan_color_cloud_callback($matches) { 
  $text = $matches[1]; 
  $color = dechex(rand(0,16777215)); 
  $pattern = '/style=(\'|\")(.*)(\'|\")/i'; 
  $text = preg_replace($pattern, "style=\"color:#{$color};$2;\"", $text); 

  return "<a $text>"; 
}
add_filter('wp_tag_cloud', 'zan_color_cloud', 1);

/**
 *   文章存档函数
 *
 * @since Zanblog 2.0.5
 *
 * @return archives.
 */
function zan_archives_list() {
  if( !$output = get_option('zan_archives_list') ) {
    $output = '<div id="archives">';      
    $the_query = new WP_Query( 'posts_per_page=-1&ignore_sticky_posts=1' );
    $year=0; $mon=0; $i=0; $j=0;
    while ( $the_query->have_posts() ) : $the_query->the_post();
    $year_tmp = get_the_time('Y');
    $mon_tmp = get_the_time('m');
    $y=$year; $m=$mon;
    if ($mon != $mon_tmp && $mon > 0) $output .= '</div></div></div>';
    if ($year != $year_tmp && $year > 0) $output .= '</div>';
    if ($year != $year_tmp) {
      $year = $year_tmp;
      $output .= '<h3 class="year">'. $year .' 年</h3><div class="panel-group" id="accordion">';
    }
    if ($mon != $mon_tmp) {
      $mon = $mon_tmp;
      $output .= '<div class="panel panel-default">
                      <div class="panel-heading">
                          <h4 class="panel-title">
                              <a data-toggle="collapse" data-toggle="collapse" data-parent="#accordion" href="#collapse'.$year. $mon .'">
                                  '. $mon .' 月</a></h4></div>
                      <div id="collapse'.$year. $mon .'" class="panel-collapse collapse">
                          <div class="panel-body">';
    }
    $output .= '<p>'. get_the_time('d日: ') .'<a href="'. get_permalink() .'">'. get_the_title() .'</a> <span class="badge">'. get_comments_number('0', '1', '%') .'</span></p>';

    endwhile;
    wp_reset_postdata();
    $output .= '</div></div></div></div></div>';
    update_option('zan_archives_list', $output);
  }
  echo $output;
}
function clear_zal_cache() {
  update_option('zan_archives_list', '');
}
add_action('save_post', 'clear_zal_cache');


/*禁用后台google字体*/
add_filter('gettext_with_context', 'disable_open_sans', 888, 4);

function disable_open_sans($translations, $text, $context, $domain) {
    if('Open Sans font: on or off' == $context && 'on' == $text) {
        $translations = 'off';
    }
    return $translations;
}

?>