<?php get_header(); ?>
<div id="zan-bodyer">
	<div class="container">
		<div class="row">
			<div id="mainstay" class="col-md-8">

				<!-- 面包屑 -->
				<div class="breadcrumb zan-breadcrumb">
				    <?php 
				    	if(function_exists('bcn_display')) {
				        	echo '<i class="fa fa-home"></i> ';
				        	bcn_display();
				    	}
				    ?>
				</div>
				<!-- 面包屑结束 -->	

				<!-- 内容主体 -->
				<div id="article-list">
					<?php while ( $wp_query->have_posts() ) : $wp_query->the_post(); ?>
						<?php get_template_part( 'includes/post-format/content', get_post_format() ); ?>
					<?php endwhile; ?>
				</div>
				<!-- 内容主体结束 -->

				<!-- 分页 -->
				<?php if(get_option( 'zan_page' )==1) { ?>
					<?php zan_page('auto'); ?>
				<?php } else { ?>
					<?php zan_page('manual'); ?>
				<?php } ?>
				<!-- 分页结束 -->

			</div>
			<?php get_sidebar(); ?>
		</div>
	</div>
</div>
<?php get_footer(); ?>
