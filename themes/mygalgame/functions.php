<?php
/**
 * Functions 整体函数调用
 *
 * @package    YEAHZAN
 * @subpackage ZanBlog
 * @since      ZanBlog 2.1.0
 *
 */

// 自定义theme路径
define( 'THEMEPATH', TEMPLATEPATH . '/' );

// 自定义includes路径
define( 'INCLUDESEPATH', THEMEPATH . 'includes/' );

// 自定义widgets路径
define( 'WIDGETSPATH', INCLUDESEPATH . 'widgets/' );

// 自定义classes路径
define( 'CLASSESPATH', INCLUDESEPATH . 'classes/' );

// 自定义admin路径
define( 'ADMINPATH', INCLUDESEPATH . 'admin/' );

// 加载主题函数文件
require_once( INCLUDESEPATH . 'theme-functions.php' );

// 加载小工具文件
require_once( WIDGETSPATH . 'widgets.php' );

// 加载主题选项文件
require_once(INCLUDESEPATH . 'theme-options.php');

// 加载短代码文件
require_once(INCLUDESEPATH . 'shortcodes.php');

// 加载自定义登录文件
require_once(ADMINPATH . 'custom-login.php');

// 加载自定义用户资料文件
require_once(ADMINPATH . 'custom-user.php');

//自定义表情路径和名称
function custom_smilies_src($src, $img){return get_bloginfo('template_directory').'/ui/images/smilies/' . $img;}
add_filter('smilies_src', 'custom_smilies_src', 10, 2);
    if ( !isset( $wpsmiliestrans ) ) {
        $wpsmiliestrans = array(
            ':i_f01:' => 'i_f01.png',
            ':i_f02:' => 'i_f02.png',
            ':i_f03:' => 'i_f03.png',
            ':i_f04:' => 'i_f04.png',
            ':i_f05:' => 'i_f05.png',
            ':i_f06:' => 'i_f06.png',
            ':i_f07:' => 'i_f07.png',
            ':i_f08:' => 'i_f08.png',
            ':i_f09:' => 'i_f09.png',
            ':i_f10:' => 'i_f10.png',
            ':i_f11:' => 'i_f11.png',
            ':i_f12:' => 'i_f12.png',
            ':i_f13:' => 'i_f13.png',
            ':i_f14:' => 'i_f14.png',
            ':i_f15:' => 'i_f15.png',
            ':i_f16:' => 'i_f16.png',
            ':i_f17:' => 'i_f17.png',
            ':i_f18:' => 'i_f18.png',
            ':i_f19:' => 'i_f19.png',
            ':i_f20:' => 'i_f20.png',
            ':i_f21:' => 'i_f21.png',
            ':i_f22:' => 'i_f22.png',
            ':i_f23:' => 'i_f23.png',
            ':i_f24:' => 'i_f24.png',
            ':i_f25:' => 'i_f25.png',
            ':i_f26:' => 'i_f26.png',
            ':i_f27:' => 'i_f27.png',
            ':i_f28:' => 'i_f28.png',
            ':i_f29:' => 'i_f29.png',
            ':i_f30:' => 'i_f30.png',
            ':i_f31:' => 'i_f31.png',
            ':i_f32:' => 'i_f32.png',
            ':i_f33:' => 'i_f33.png',
            ':i_f34:' => 'i_f34.png',
            ':i_f35:' => 'i_f35.png',
            ':i_f36:' => 'i_f36.png',
            ':i_f37:' => 'i_f37.png',
            ':i_f38:' => 'i_f38.png',
            ':i_f39:' => 'i_f39.png',
            ':i_f40:' => 'i_f40.png',
            ':i_f41:' => 'i_f41.png',
            ':i_f42:' => 'i_f42.png',
            ':i_f43:' => 'i_f43.png',
            ':i_f44:' => 'i_f44.png',
            ':i_f45:' => 'i_f45.png',
            ':i_f46:' => 'i_f46.png',
            ':i_f47:' => 'i_f47.png',
            ':i_f48:' => 'i_f48.png',
            ':i_f49:' => 'i_f49.png',
            ':i_f50:' => 'i_f50.png',            
        );
    }

    // 分页标准
    function pagination($query_string){
        global $posts_per_page, $paged;
        $my_query = new WP_Query($query_string ."&posts_per_page=-1");
        $total_posts = $my_query->post_count;
        if(empty($paged))$paged = 1;
        $prev = $paged - 1;
        $next = $paged + 1;
        $range = 0;    // 如果你想展示更多分页链接，修改它！
        $showitems = ($range * 2)+1;
        $pages = ceil($total_posts/$posts_per_page);
        if(1 != $pages){
            echo "<div class='ring'>";
            echo ($paged > 2 && $paged+$range+1 > $pages && $showitems < $pages)? "<a href='".get_pagenum_link(1)."' class='menuItem'>最前</a>":"<a href='".get_pagenum_link(1)."' class='menuItem'>最前</a>";
            echo ($paged < $pages && $showitems < $pages) ? "<a href='".get_pagenum_link($next)."' class='menuItem'>下一页</a>" :"<a href='".get_pagenum_link($paged)."' class='menuItem'>下一页</a>";
            for ($i=1; $i <= $pages; $i++){
                if (1 != $pages &&( !($i >= $paged+$range+1 || $i <= $paged-$range-1) || $pages <= $showitems )) {
                    echo ($paged == $i)? "<a href='".get_pagenum_link($paged)."' class=' menuItem' >当前第".$i."页</a>":"<a href='".get_pagenum_link($paged)."' class='menuItem' >当前第".$i."页</a>";
                }
            }
            echo ($paged < $pages-1 &&  $paged+$range-1 < $pages && $showitems < $pages) ? "<a href='".get_pagenum_link($pages)."' class='menuItem'>最后</a>":"<a href='".get_pagenum_link($paged)."' class='menuItem'>最后</a>";
            echo  "<a href='".get_pagenum_link(1)."/message' class='menuItem'>留言板</a>";
            echo ($paged > 1 && $showitems < $pages)? "<a href='".get_pagenum_link($prev)."' class='menuItem'>上一页</a>":"<a href='".get_pagenum_link(1)."' class='menuItem'>上一页</a>";
            echo "</div>\n";
        }
    }
?>