<?php
	/*
	Template Name: 全屏页面(不带侧边栏)
	*/
?>
<?php get_header(); ?>
<div id="zan-bodyer">
	<div class="container">

		<!-- 内容主体 -->
		<?php while ($wp_query->have_posts()) : $wp_query->the_post(); ?>
		<article class="article well clearfix">
			<?php the_content(); ?>
		</article>
		<?php endwhile; ?> 
		<!-- 内容主体结束 -->

	</div>
</div>
<?php get_footer(); ?>
