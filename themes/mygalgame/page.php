<?php get_header(); ?>
<div id="zan-bodyer">
	<div class="container">
		<div class="row">
			<div class="col-md-8">

				<!-- 内容主体 -->
				<?php while ($wp_query->have_posts()) : $wp_query->the_post(); ?>
				<article class="article well clearfix">
					<?php the_content(); ?>
				</article>
				<?php endwhile; ?> 
				<!-- 内容主体结束 -->

			</div>
			<?php get_sidebar(); ?>
		</div>
	</div>
</div>
<?php get_footer(); ?>
