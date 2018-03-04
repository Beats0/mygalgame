<aside class="col-md-4" id="sidebar">
  <?php if( is_single() ) ( function_exists( 'dynamic_sidebar' ) && dynamic_sidebar( '文章侧边栏' ) ) ? true : false; ?>
  <?php if( is_home() ) ( function_exists( 'dynamic_sidebar' ) && dynamic_sidebar( '首页侧边栏' ) ) ? true : false; ?>
  <?php if( is_archive() | is_search() ) ( function_exists( 'dynamic_sidebar' ) && dynamic_sidebar( '归档侧边栏' ) ) ? true : false; ?>
  <?php if( is_page() ) ( function_exists( 'dynamic_sidebar' ) && dynamic_sidebar( '页面侧边栏' ) ) ? true : false; ?>
</aside>