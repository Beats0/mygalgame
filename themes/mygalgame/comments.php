<?php

if ( !empty( $_SERVER['SCRIPT_FILENAME'] ) && 'comments.php' == basename( $_SERVER['SCRIPT_FILENAME'] ) )
  die ('请不要直接加载该页面！');

if ( post_password_required() ) { ?>
  <p class="nocomments"><?php _e( '该评论需要权限查看，请输入密码。', 'contempo' ); ?></p>
<?php
  return;
}
?>

<div id="comments-template">
  <div class="comments-wrap">
    <div id="comments" data-url="<?php echo get_bloginfo("template_url") ?>/includes/comment-ajax.php">
      <?php if ( have_comments() ) : ?>
        <h3 id="comments-title" class="comments-header alert alert-info"><i class="fa fa-comments"></i> <?php comments_number( __('暂无评论', 'contempo'), __('1 条评论', 'contempo'), __( '% 条评论', 'contempo') );?></h3>
        <div id="loading-comments"><i class="fa fa-spinner fa-spin"></i></div>
        <ol class="commentlist">
          <?php zan_get_commments_list( 70 ); ?>
        </ol>
        <?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : ?>
        <nav id="comment-nav" class="clearfix">
          <div class='pagination pagination-zan pull-right'>
            <?php zan_comments_pagination(); ?>
          </div>
        </nav>
        <?php endif; ?>
      <?php else : ?>
      <?php if ( pings_open() && !comments_open() ) : ?>
        <p class="comments-closed pings-open"><?php _e( 'Comments are closed, but', 'contempo' ); ?> <a href="%1$s" title="<?php _e('Trackback URL for this post', 'contempo'); ?>"><?php _e( 'trackbacks', 'contempo' ); ?></a> <?php _e( 'and pingbacks are open.', 'contempo' ); ?></p>
      <?php elseif ( !comments_open() ) : ?>
        <p class="nocomments"><?php _e( '评论已经关闭。', 'contempo' ); ?></p>
      <?php endif; ?>
    <?php endif; ?>
    </div>
    <?php zan_comments_form(); ?>
    <div id="smilelink">
      <a onclick="javascript:grin(':i_f01:')"><img src="<?php echo get_template_directory_uri(); ?>/ui/images/smilies/i_f01.png" title="呵呵" alt="呵呵" /></a>
      <a onclick="javascript:grin(':i_f02:')"><img src="<?php echo get_template_directory_uri(); ?>/ui/images/smilies/i_f02.png" title="哈哈" alt="哈哈" /></a>
      <a onclick="javascript:grin(':i_f03:')"><img src="<?php echo get_template_directory_uri(); ?>/ui/images/smilies/i_f03.png" title="吐舌" alt="吐舌" /></a>
      <a onclick="javascript:grin(':i_f04:')"><img src="<?php echo get_template_directory_uri(); ?>/ui/images/smilies/i_f04.png" title="啊" alt="啊" /></a>
      <a onclick="javascript:grin(':i_f05:')"><img src="<?php echo get_template_directory_uri(); ?>/ui/images/smilies/i_f05.png" title="酷" alt="酷" /></a>
      <a onclick="javascript:grin(':i_f06:')"><img src="<?php echo get_template_directory_uri(); ?>/ui/images/smilies/i_f06.png" title="怒" alt="怒" /></a>
      <a onclick="javascript:grin(':i_f07:')"><img src="<?php echo get_template_directory_uri(); ?>/ui/images/smilies/i_f07.png" title="开心" alt="开心" /></a>
      <a onclick="javascript:grin(':i_f08:')"><img src="<?php echo get_template_directory_uri(); ?>/ui/images/smilies/i_f08.png" title="汗" alt="汗" /></a>
      <a onclick="javascript:grin(':i_f09:')"><img src="<?php echo get_template_directory_uri(); ?>/ui/images/smilies/i_f09.png" title="泪" alt="泪" /></a>
      <a onclick="javascript:grin(':i_f10:')"><img src="<?php echo get_template_directory_uri(); ?>/ui/images/smilies/i_f10.png" title="黑线" alt="黑线" /></a>
      <a onclick="javascript:grin(':i_f11:')"><img src="<?php echo get_template_directory_uri(); ?>/ui/images/smilies/i_f11.png" title="鄙视" alt="鄙视" /></a>
      <a onclick="javascript:grin(':i_f12:')"><img src="<?php echo get_template_directory_uri(); ?>/ui/images/smilies/i_f12.png" title="不高兴" alt="不高兴" /></a>
      <a onclick="javascript:grin(':i_f13:')"><img src="<?php echo get_template_directory_uri(); ?>/ui/images/smilies/i_f13.png" title="真棒" alt="真棒" /></a>
      <a onclick="javascript:grin(':i_f14:')"><img src="<?php echo get_template_directory_uri(); ?>/ui/images/smilies/i_f14.png" title="狂" alt="狂" /></a>
      <a onclick="javascript:grin(':i_f15:')"><img src="<?php echo get_template_directory_uri(); ?>/ui/images/smilies/i_f15.png" title="狂" alt="狂" /></a>
      <a onclick="javascript:grin(':i_f16:')"><img src="<?php echo get_template_directory_uri(); ?>/ui/images/smilies/i_f16.png" title="阴险" alt="阴险" /></a>
      <a onclick="javascript:grin(':i_f17:')"><img src="<?php echo get_template_directory_uri(); ?>/ui/images/smilies/i_f17.png" title="吐" alt="吐" /></a>
      <a onclick="javascript:grin(':i_f18:')"><img src="<?php echo get_template_directory_uri(); ?>/ui/images/smilies/i_f18.png" title="咦" alt="咦" /></a>
      <a onclick="javascript:grin(':i_f19:')"><img src="<?php echo get_template_directory_uri(); ?>/ui/images/smilies/i_f19.png" title="委屈" alt="委屈" /></a>
      <a onclick="javascript:grin(':i_f20:')"><img src="<?php echo get_template_directory_uri(); ?>/ui/images/smilies/i_f20.png" title="花心" alt="花心" /></a>
      <a onclick="javascript:grin(':i_f21:')"><img src="<?php echo get_template_directory_uri(); ?>/ui/images/smilies/i_f21.png" title="呼~" alt="呼~" /></a>
      <a onclick="javascript:grin(':i_f22:')"><img src="<?php echo get_template_directory_uri(); ?>/ui/images/smilies/i_f22.png" title="笑眼" alt="笑眼" /></a>
      <a onclick="javascript:grin(':i_f23:')"><img src="<?php echo get_template_directory_uri(); ?>/ui/images/smilies/i_f23.png" title="冷" alt="冷" /></a>
      <a onclick="javascript:grin(':i_f24:')"><img src="<?php echo get_template_directory_uri(); ?>/ui/images/smilies/i_f24.png" title="太开心" alt="太开心" /></a>
      <a onclick="javascript:grin(':i_f25:')"><img src="<?php echo get_template_directory_uri(); ?>/ui/images/smilies/i_f25.png" title="滑稽" alt="滑稽" /></a>
      <a onclick="javascript:grin(':i_f26:')"><img src="<?php echo get_template_directory_uri(); ?>/ui/images/smilies/i_f26.png" title="勉强" alt="勉强" /></a>
      <a onclick="javascript:grin(':i_f27:')"><img src="<?php echo get_template_directory_uri(); ?>/ui/images/smilies/i_f27.png" title="狂汗" alt="狂汗" /></a>
      <a onclick="javascript:grin(':i_f28:')"><img src="<?php echo get_template_directory_uri(); ?>/ui/images/smilies/i_f28.png" title="乖" alt="乖" /></a>
      <a onclick="javascript:grin(':i_f29:')"><img src="<?php echo get_template_directory_uri(); ?>/ui/images/smilies/i_f29.png" title="睡觉" alt="睡觉" /></a>
      <a onclick="javascript:grin(':i_f30:')"><img src="<?php echo get_template_directory_uri(); ?>/ui/images/smilies/i_f30.png" title="惊" alt="惊" /></a>
      <a onclick="javascript:grin(':i_f31:')"><img src="<?php echo get_template_directory_uri(); ?>/ui/images/smilies/i_f31.png" title="生气" alt="生气" /></a>
      <a onclick="javascript:grin(':i_f32:')"><img src="<?php echo get_template_directory_uri(); ?>/ui/images/smilies/i_f32.png" title="惊讶" alt="惊讶" /></a>
      <a onclick="javascript:grin(':i_f33:')"><img src="<?php echo get_template_directory_uri(); ?>/ui/images/smilies/i_f33.png" title="喷" alt="喷" /></a>
      <a onclick="javascript:grin(':i_f34:')"><img src="<?php echo get_template_directory_uri(); ?>/ui/images/smilies/i_f34.png" title="爱心" alt="爱心" /></a>
      <a onclick="javascript:grin(':i_f35:')"><img src="<?php echo get_template_directory_uri(); ?>/ui/images/smilies/i_f35.png" title="心碎" alt="心碎" /></a>
      <a onclick="javascript:grin(':i_f36:')"><img src="<?php echo get_template_directory_uri(); ?>/ui/images/smilies/i_f36.png" title="玫瑰" alt="玫瑰" /></a>
      <a onclick="javascript:grin(':i_f37:')"><img src="<?php echo get_template_directory_uri(); ?>/ui/images/smilies/i_f37.png" title="礼物" alt="礼物" /></a>
      <a onclick="javascript:grin(':i_f38:')"><img src="<?php echo get_template_directory_uri(); ?>/ui/images/smilies/i_f38.png" title="彩虹" alt="彩虹" /></a>
      <a onclick="javascript:grin(':i_f39:')"><img src="<?php echo get_template_directory_uri(); ?>/ui/images/smilies/i_f39.png" title="星星月亮" alt="星星月亮" /></a>
      <a onclick="javascript:grin(':i_f40:')"><img src="<?php echo get_template_directory_uri(); ?>/ui/images/smilies/i_f40.png" title="太阳" alt="太阳" /></a>
      <a onclick="javascript:grin(':i_f41:')"><img src="<?php echo get_template_directory_uri(); ?>/ui/images/smilies/i_f41.png" title="钱币" alt="钱币" /></a>
      <a onclick="javascript:grin(':i_f42:')"><img src="<?php echo get_template_directory_uri(); ?>/ui/images/smilies/i_f42.png" title="灯泡" alt="灯泡" /></a>
      <a onclick="javascript:grin(':i_f43:')"><img src="<?php echo get_template_directory_uri(); ?>/ui/images/smilies/i_f43.png" title="茶杯" alt="茶杯" /></a>
      <a onclick="javascript:grin(':i_f44:')"><img src="<?php echo get_template_directory_uri(); ?>/ui/images/smilies/i_f44.png" title="蛋糕" alt="蛋糕" /></a>
      <a onclick="javascript:grin(':i_f45:')"><img src="<?php echo get_template_directory_uri(); ?>/ui/images/smilies/i_f45.png" title="音乐" alt="音乐" /></a>
      <a onclick="javascript:grin(':i_f46:')"><img src="<?php echo get_template_directory_uri(); ?>/ui/images/smilies/i_f46.png" title="haha" alt="haha" /></a>
      <a onclick="javascript:grin(':i_f47:')"><img src="<?php echo get_template_directory_uri(); ?>/ui/images/smilies/i_f47.png" title="胜利" alt="胜利" /></a>
      <a onclick="javascript:grin(':i_f48:')"><img src="<?php echo get_template_directory_uri(); ?>/ui/images/smilies/i_f48.png" title="大拇指" alt="大拇指" /></a>
      <a onclick="javascript:grin(':i_f49:')"><img src="<?php echo get_template_directory_uri(); ?>/ui/images/smilies/i_f49.png" title="弱" alt="弱" /></a>
      <a onclick="javascript:grin(':i_f50:')"><img src="<?php echo get_template_directory_uri(); ?>/ui/images/smilies/i_f50.png" title="OK" alt="OK" /></a>
    </div>
  </div>
</div>
<script type="text/javascript" language="javascript">
/* <![CDATA[ */
  var smiley  = jQuery( "#smilelink" );
      clone   = smiley.clone();
      comment = jQuery( "#comment" );

  smiley.remove();
  comment.before( smiley );

  function grin(tag) {
    var myField;
    tag = ' ' + tag + ' ';
      if ( document.getElementById( 'comment' ) && document.getElementById( 'comment' ).type == 'textarea' ) {
      myField = document.getElementById( 'comment' );
    } else {
      return false;
    }
    if (document.selection) {
      myField.focus();
      sel = document.selection.createRange();
      sel.text = tag;
      myField.focus();
    }
    else if ( myField.selectionStart || myField.selectionStart == '0' ) {
      var startPos = myField.selectionStart;
      var endPos = myField.selectionEnd;
      var cursorPos = endPos;
      myField.value = myField.value.substring(0, startPos)
              + tag
              + myField.value.substring( endPos, myField.value.length );
      cursorPos += tag.length;
      myField.focus();
      myField.selectionStart = cursorPos;
      myField.selectionEnd = cursorPos;
    }
    else {
      myField.value += tag;
      myField.focus();
    }
  }
/* ]]> */
</script>