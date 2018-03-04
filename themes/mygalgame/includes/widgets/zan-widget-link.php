<?php
/**
 * ZanBlog 链接组件
 *
 * @package    ZanBlog
 * @subpackage Widget
 */
 
class Zan_Link extends WP_Widget {

  // 设定小工具信息
  function Zan_Link() {
    $widget_options = array(
          'name'        => '链接组件（ZanBlog）', 
          'description' => 'Zanblog 链接组件' 
    );
    parent::WP_Widget( false, false, $widget_options );  
  }

  // 设定小工具结构
  function widget( $args, $instance ) {  
  	extract( $args );
    @$aurl = $instance['aurl'] ? $instance['aurl'] : '';
    @$title = $instance['title'] ? $instance['title'] : '';
    echo $before_widget;
    ?>
    <div class="panel archive hidden-xs">
      <a href="<?php echo $aurl; ?>" target="_blank">
        <div class="panel-heading">     
          <?php echo $title; ?> 
        </div>
      </a>
    </div>

    <?php
    echo $after_widget;
  }

  function update( $new_instance, $old_instance ) {                
     return $new_instance;
  }

  function form( $instance ) {  
    @$aurl = esc_attr( $instance['aurl'] );
    @$title = esc_attr( $instance['title'] );
    ?>
      <p>
        <label for="<?php echo $this->get_field_id( 'aurl' ); ?>">
          指向链接：
          <input class="widefat" id="<?php echo $this->get_field_id( 'aurl' ); ?>" name="<?php echo $this->get_field_name( 'aurl' ); ?>" type="text" value="<?php echo $aurl; ?>" />
        </label>
      </p>
      <p>
        <label for="<?php echo $this->get_field_id( 'title' ); ?>">
          标题：
          <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
        </label>
      </p>
    <?php 
  }
} 

register_widget( 'Zan_Link' );
?>