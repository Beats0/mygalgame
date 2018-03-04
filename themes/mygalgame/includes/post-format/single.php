<!-- 广告 -->
<?php if ( get_option( 'zan_single_top_ad' ) ) : ?>
  <div class="ad hidden-xs">
    <?php echo stripslashes( get_option( 'zan_single_top_ad' ) ); ?>
  </div>
<?php endif; ?>
<!-- 广告结束 -->	
<!-- 内容主体 -->
<?php while ( $wp_query->have_posts() ) : $wp_query->the_post(); ?>
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
	<!-- 面包屑结束 -->	
	<!-- 大型设备文章属性 -->
	<div class="hidden-xs">
		<div class="title-article">
			<h1><a href="<?php the_permalink() ?>" target="_blank"><?php the_title(); ?></a></h1>
		</div>
		<div class="tag-article container">
			<span class="label label-zan"><i class="fa fa-calendar"></i> <?php the_time('n'. '-' .'d'); ?></span>
			<span class="label label-zan"><i class="fa fa-tags"></i> <?php the_category(','); ?></span>
			<span class="label label-zan"><i class="fa fa-user"></i> <?php the_author_posts_link(); ?></span>
			<span class="label label-zan"><i class="fa fa-eye"></i> <?php if(function_exists('the_views')) { the_views(); } ?></span>
			<?php edit_post_link( '<span class="label label-zan"><i class="fa fa-edit"></i> 编辑', ' ', '</span>'); ?>
		</div>
	</div>
	<!-- 大型设备文章属性结束 -->
	<!-- 小型设备文章属性 -->
	<div class="visible-xs">
		<center>
			<div class="title-article">
				<h4><a href="<?php the_permalink() ?>" target="_blank"><?php the_title(); ?>
					<br>
					<span class="label label-info" style="font-size:13px;"></span>
				</a></h4>
			</div>
			<p>
				<i class="fa fa-calendar"></i> <?php the_time('n'); ?>-<?php the_time('d'); ?>
				<i class="fa fa-eye"></i> <?php if(function_exists('the_views')) { the_views(); } ?>
			</p>
		</center>
	</div>
	<!-- 小型设备文章属性结束 -->
	<div class="centent-article">
		<?php the_content(); ?>
		<!-- tag -->
		<footer class="article-footer">
		 	<div class="article-tags">
			 <i class="fa fa-tags"></i>
			 <?php the_category(','); ?>
			 </div> 
		</footer>
		<!-- tag -->
		<!-- 分页 -->
		<div class="zan-page bs-example">
      	<ul class="pager">
					<li class="previous"><?php previous_post_link('%link', '上一篇', TRUE); ?></li>
					<li class="next"><?php next_post_link('%link', '下一篇', TRUE); ?></li>
				</ul>
    </div>

    <!-- 分页 -->
		<script>
		window._bd_share_config={"common":{"bdSnsKey":{},"bdText":"","bdMini":"2","bdMiniList":false,"bdPic":"","bdStyle":"1","bdSize":"24"},"share":{}};with(document)0[(getElementsByTagName('head')[0]||body).appendChild(createElement('script')).src='http://bdimg.share.baidu.com/static/api/js/share.js?v=89860593.js?cdnversion='+~(-new Date()/36e5)];
		</script>
		<!-- Baidu Button END -->
	</div>				
</article>
<?php endwhile; ?>
<!-- 内容主体结束 -->
<!-- 广告 -->
<?php if ( get_option( 'zan_single_down_ad' ) ) : ?>
  <div class="ad hidden-xs">
    <?php echo stripslashes( get_option( 'zan_single_down_ad' ) ); ?>
  </div>
<?php endif; ?>
<!-- 广告结束 -->
