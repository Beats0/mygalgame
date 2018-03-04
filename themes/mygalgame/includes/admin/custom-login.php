<?php 

/*自定义登录后台*/
function custom_login() {
    echo '<link rel="stylesheet" type="text/css" href="' . get_bloginfo( 'template_directory' ) . '/ui/css/admin.css" />';
}
add_action('login_head', 'custom_login');

/*更改logo的url*/
function custom_headerurl( $url ) {
    return get_bloginfo( 'url' );
}
add_filter( 'login_headerurl', 'custom_headerurl' );

/*更改logo的title*/
function custom_headertitle( $title ) {
    return  get_bloginfo('name');
}
add_filter('login_headertitle','custom_headertitle');


/*删除控制面板顶部左上角LOGO图像*/
function zan_custom_logo() {
    echo '
        <style type="text/css">
        #wp-admin-bar-wp-logo,
        #dashboard_right_now .versions p,
        #wp-version-message
        {display:none !important;}
        </style>
    ';
}
add_action( 'admin_head', 'zan_custom_logo' );

/*隐藏控制面板页脚版权信息和版本号*/
function change_footer_admin () {
    return ' Theme By:<a href="http://www.yeahzan.com/" target="_blank">YeahZan</a>';
}
add_filter( 'admin_footer_text', 'change_footer_admin', 9999 );

function change_footer_version() {
    return ' ';
}
add_filter( 'update_footer', 'change_footer_version', 9999 );

?>