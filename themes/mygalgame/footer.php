<footer id="zan-footer">
	<div class="container">
		<div class="row">
             © 2014-2018 <a href="https://www.mygalgame.com">Mygalgame.com</a> | Powered By WordPress | Theme By<a href="https://github.com/yeahzan/zanblog"> Yeahzan </a> | Revised By <a href="https://github.com/Beats0">Beats0</a> 
			<script src="<?php echo get_template_directory_uri(); ?>/ui/js/myblog_bd.js"></script>
			<script src="<?php echo get_template_directory_uri(); ?>/ui/js/myblog_min.js"></script>
		</div>
		<!--统计代码-->
    <?php $analytics = get_option('zan_analytics');if ($analytics != "") : ?>
    	<?php echo stripslashes($analytics); ?>
    <?php endif ?>
    <!--统计代码结束-->
	</div>
</footer>
<!-- 回到顶端 -->
<div id="zan-gotop">
	<i class="fa fa-angle-up"></i>
</div>
<!-- 回到顶端结束 -->
<?php wp_footer(); ?>
</body>
</html>