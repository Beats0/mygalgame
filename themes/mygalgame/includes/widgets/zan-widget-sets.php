<?php
/**
 * ZanBlog 集合组件
 *
 * @package    ZanBlog
 * @subpackage Widget
 */
 
class Zan_Sets extends WP_Widget {

  // 设定小工具信息
  function Zan_Sets() {
    $widget_options = array(
          'name'        => '集合组件（ZanBlog）', 
          'description' => 'ZanBlog 集合组件，包含分类目录、热门标签、友情链接模块' 
    );
    parent::WP_Widget( false, false, $widget_options );  
  }

  // 设定小工具结构
  function widget( $args, $instance ) {  
  	extract($args);

    @$title1 = $instance['title1'] ? $instance['title1'] : '分类目录';
    @$title2 = $instance['title2'] ? $instance['title2'] : '热门标签';
    @$title3 = $instance['title3'] ? $instance['title3'] : '友情链接';
    echo $before_widget;
    ?>

    <div class="panel panel-zan hidden-xs aos-init aos-animate" aos="fade-up" aos-duration="2000">    
      <ul class="nav nav-pills pills-zan">
        <li class="active"><a href="#sidebar-tags" data-toggle="tab"><?php echo $title2; ?></a></li>
        <li><a href="#sidebar-categories" data-toggle="tab"><?php echo $title1; ?></a></li>
        <?php if ( is_home() ) { ?>
        <li><a href="#sidebar-links" data-toggle="tab"><?php echo $title3; ?></a></li>
        <?php } ?>
      </ul>
      <div class="tab-content">
      <div class="cloud-tags tab-pane nav bs-sidenav fade active in" id="sidebar-tags">
      <?php wp_tag_cloud( 'smallest=14&largest=14&orderby=count&unit=px&number=50&order=RAND' );?>
      </div>        
        <div class="tab-pane fade" id="sidebar-categories"><?php wp_list_categories('title_li=&depth=1'); ?></div>
        <?php if ( is_home() ) { ?>
        <div class="tab-pane nav bs-sidenav fade" id="sidebar-links"><?php wp_list_bookmarks('title_li=&categorize=0'); ?></div>
        <?php } ?>
      </div>
    </div>
    <?php
    echo $after_widget;
  }

  function update($new_instance, $old_instance) {                
    return $new_instance;
  }

  function form($instance) {  
    @$title1 = esc_attr( $instance['title1'] );
    @$title2 = esc_attr( $instance['title2'] );
    @$title3 = esc_attr( $instance['title3'] );
    ?>
     <p>
        <label for="<?php echo $this->get_field_id( 'title2' ); ?>">
          标题（默认热门标签）：
          <input class="widefat" id="<?php echo $this->get_field_id( 'title2' ); ?>" name="<?php echo $this->get_field_name( 'title2' ); ?>" type="text" value="<?php echo $title2; ?>" />
        </label>
      </p>
      <p>
        <label for="<?php echo $this->get_field_id( 'title1' ); ?>">
          标题（默认分类目录）：
          <input class="widefat" id="<?php echo $this->get_field_id( 'title1' ); ?>" name="<?php echo $this->get_field_name( 'title1' ); ?>" type="text" value="<?php echo $title1; ?>" />
        </label>
      </p>
     
      <p>
        <label for="<?php echo $this->get_field_id( 'title3' ); ?>">
          标题（默认友情链接）：
          <input class="widefat" id="<?php echo $this->get_field_id( 'title3' ); ?>" name="<?php echo $this->get_field_name( 'title3' ); ?>" type="text" value="<?php echo $title3; ?>" />
        </label>
      </p>
    <?php 
  }
} 

register_widget( 'Zan_Sets' );
?>