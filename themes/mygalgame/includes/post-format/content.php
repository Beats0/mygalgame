<div class="article well clearfix mybody3" aos="flip-up">
<i class="fa fa-bookmark article-stick visible-md visible-lg"></i>
	<?php if( is_sticky() ) echo '<i class="fa fa-bookmark article-stick visible-md visible-lg"></i>';?>
	
	<div class="data-article hidden-xs">
		<span class="month"><?php the_time('n月') ?></span>
		<span class="day"><?php the_time('d') ?></span>
	</div>
	<!-- 大型设备文章属性 -->
	<section class="hidden-xs">
		<div class="title-article">
			<h1><a href="<?php the_permalink() ?>" target="_blank">
			<span class="animated_h1"><?php the_title(); ?></span>
			</a></h1>
		</div>
		<div class="tag-article">
			<span class="label label-zan"><i class="fa fa-tags"></i> <?php the_category(','); ?></span>
			<span class="label label-zan"><i class="fa fa-user"></i> <?php the_author_posts_link(); ?></span>
			<span class="label label-zan"><i class="fa fa-eye"></i> <?php if(function_exists('the_views')) { the_views(); } ?></span>
			<span class="label label-zan"><i class="fa fa-comment"></i> <?php comments_number( '0', '1', '%' ); ?></span>
		</div>

		<?php if ( has_post_thumbnail() ) { ?>

		<div class="alert alert-zan mybody3">
			<p style="text-align: center;">
				<a href="<?php the_permalink() ?>" target="_blank"></a>
            </p>
			<div class="ih-item square effect bottom_to_top">
				<a href="<?php the_permalink() ?>" target="_blank">
					<div class="img">
						<?php the_post_thumbnail( 'full' ); ?>
					</div>
					<div class="info">
						<p><?php echo mb_strimwidth(strip_tags(apply_filters('the_content', $post->post_content)), 0, 250,"..."); ?></p>
					</div>
				</a>
			</div>							
		</div>
	</section>

	<?php } else {?>

		<div class="alert alert-zan mybody3">
    		<div class="info">
				<p style="font-size: 13px;"><?php echo mb_strimwidth(strip_tags(apply_filters('the_content', $post->post_content)), 0, 250,"..."); ?></p>
        		<a class="btn btn-danger pull-right" href="<?php the_permalink() ?>" title="more" style="color:white;" target="_blank">more</a>        	
    		</div>
</div>
</section>

<?php } ?>
	<!-- 大型设备文章属性结束 -->
	<!-- 小型设备文章属性 -->
	<section class="visible-xs">
		<div class="title-article">
			<center>
				<h4><a href="<?php the_permalink() ?>" target="_blank">
						<span class="animated_h1"><?php the_title(); ?></span>
					</a>
				</h4>
			</center>
		</div>
		<center><i class="fa fa-calendar"></i> <?php the_time('n'); ?>-<?php the_time('d'); ?></center>
		<p></p>
		<center>
			<i class="fa fa-tags"></i> <?php the_category(','); ?>
			<i class="fa fa-eye"></i> <?php if(function_exists('the_views')) { the_views(); } ?>
			<i class="fa fa-comment"></i> <?php comments_number( '0', '1', '%' ); ?>
		</center>
		<p></p>

		<?php if ( has_post_thumbnail() ) { ?>

		<div class="alert alert-zan mybody3">
			<p style="text-align: center;">
				<a href="<?php the_permalink() ?>"><?php the_post_thumbnail( 'full' ); ?></a>
			</p>
		</div>
		<div class="alert alert-zan">					
				<?php echo mb_strimwidth(strip_tags(apply_filters('the_content', $post->post_content)), 0, 150,"..."); ?>
		</div>
		<a class="btn btn-danger pull-center read-more btn-block" href="<?php the_permalink() ?>"  title="详细阅读 <?php the_title(); ?>">阅读全文 <span class="badge"><?php comments_number( '0', '1', '%' ); ?></span></a>

		<?php } else {?>

		<div class="alert alert-zan">					
			<?php echo mb_strimwidth(strip_tags(apply_filters('the_content', $post->post_content)), 0, 150,"..."); ?>
		</div>

		<?php } ?>
	</section>
	<!-- 小型设备文章属性结束 -->
</div>

