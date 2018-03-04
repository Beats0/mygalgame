<?php get_header(); ?>
<div id="zan-bodyer">
	<div class="container">
		<div class="row">
			<div class="col-md-8">	
				<?php get_template_part( 'includes/post-format/single', get_post_format() ); ?>
				<?php comments_template(); ?> 									
			</div>
			<?php get_sidebar(); ?>
		</div>
	</div>
</div>
<?php get_footer(); ?>
</body>
</html>