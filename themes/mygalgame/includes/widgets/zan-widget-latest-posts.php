<?php
/**
 * ZanBlog 最新文章组件
 *
 * @package    ZanBlog
 * @subpackage Widget
 */
 
class Zan_Latest_Posts extends WP_Widget {

  // 设定小工具信息
  function Zan_Latest_Posts() {
    $widget_options = array(
          'name'        => '最新文章组件（ZanBlog）', 
          'description' => 'ZanBlog 最新文章组件' 
    );
    parent::WP_Widget( false, false, $widget_options );  
  }

  // 设定小工具结构
  function widget( $args, $instance ) {  
  	extract($args);
    @$title = $instance['title'] ? $instance['title'] : '最新文章';
    @$num = $instance['num'] ? $instance['num'] :8;
    echo $before_widget;
    ?>
      <div class="panel panel-zan recent">
        <div class="panel-heading">
          <i class="fa fa-refresh"></i> <?php echo $title; ?>
          <i class="fa fa-times-circle panel-remove"></i>
          <i class="fa fa-chevron-circle-up panel-toggle"></i>
        </div>
        <ul class="list-group list-group-flush">
          <?php 
            // 设置全局变量，实现post整体赋值
            global $post;
            $posts = zan_get_latest_posts( $num );
            foreach ( $posts as $post ) :
            setup_postdata($post);
          ?>
            <li class="list-group-item">
              <span class="post-title">
                <a href="<?php the_permalink();?>">
                  <?php the_title();?>
                </a>
              </span>
              <?php if(function_exists('the_views')) { ?>
              <span class="badge"><?php the_views(); ?></span>
              <?php } ?>
            </li>
          <?php
            endforeach;
            wp_reset_postdata();
          ?>
        </ul>
      </div>
    <?php
    echo $after_widget;
  }

  function update( $new_instance, $old_instance ) {                
     return $new_instance;
  }

  function form( $instance ) {        
    @$title = esc_attr( $instance['title'] );
    @$num = esc_attr( $instance['num'] );
    ?>
      <p>
        <label for="<?php echo $this->get_field_id( 'title' ); ?>">
          标题（默认最新文章）：
          <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
        </label>
      </p>
      <p>
        <label for="<?php echo $this->get_field_id( 'num' ); ?>">
          显示文章条数（默认显示8条）：
          <input class="widefat" id="<?php echo $this->get_field_id( 'num' ); ?>" name="<?php echo $this->get_field_name( 'num' ); ?>" type="text" value="<?php echo $num; ?>" />
        </label>
      </p>
    <?php 
  }
} 

register_widget( 'Zan_Latest_Posts' );
?>