<?php
	/*
	Template Name: 文章存档
	*/
?>

<?php get_header(); ?>
<div id="zan-bodyer">
	<div class="container">
		<div class="row">
			<div class="col-md-8">
				<?php while ($wp_query->have_posts()) : $wp_query->the_post(); ?>
				<article class="article container well">
					
					<!-- 面包屑 -->
					<div class="breadcrumb">
					    <?php 
					    	if(function_exists('bcn_display')) {
					        	echo '<i class="fa fa-home"></i> ';
					        	bcn_display();
					    	}
					    ?>
					</div>	
					<!-- 面包屑 -->

					<?php zan_archives_list(); ?>
				</article>
				<?php endwhile; ?>
			</div>
			<?php get_sidebar(); ?>
		</div>
	</div>
</div>
<?php get_footer(); ?>



