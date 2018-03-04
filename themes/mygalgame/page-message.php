<?php
	/*
	Template Name: 留言板
	*/
?>

<?php get_header(); ?>
<div id="zan-bodyer">
	<div class="container">
		<div class="row">
			<div class="col-md-8">
				<div class="alert alert-info fade in">

					<!-- 面包屑 -->
					<?php 
			    	if(function_exists('bcn_display')) {
			        	echo '<i class="fa fa-home"></i> ';
			        	bcn_display();
			    	}
			    ?>
			    <!-- 面包屑结束 -->

					<!-- 内容主体 -->
			    <?php while ($wp_query->have_posts()) : $wp_query->the_post(); ?>
			    	<?php the_content(); ?>
					<?php endwhile; ?>
					<!-- 内容主体结束 -->

				</div>
				<?php comments_template(); ?>
			</div>
			<?php get_sidebar(); ?>
		</div>
	</div>
</div>
<?php get_footer(); ?>
