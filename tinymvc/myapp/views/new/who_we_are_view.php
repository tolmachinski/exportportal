<h3 class="minfo-sidebar-ttl">
	<span class="minfo-sidebar-ttl__txt"><?php echo translate('blog_sidebar_about_us_header');?></span>
</h3>

<div class="minfo-sidebar-box">
	<div class="minfo-sidebar-box__img">
		<img
            class="image js-lazy"
            src="<?php echo getLazyImage(298, 200);?>"
            data-srcset="<?php echo asset("public/build/images/who_we_are/who-we-are.jpg");?> 1x, <?php echo asset("public/build/images/who_we_are/who-we-are@2x.jpg");?> 2x"
            data-src="<?php echo asset("public/build/images/who_we_are/who-we-are.jpg"); ?>"
            alt="<?php echo translate('blog_sidebar_about_us_header', null, true);?>">
	</div>
	<div class="minfo-sidebar-box__desc">
		<div class="minfo-sidebar-box__txt">
			<?php echo translate('blog_sidebar_about_us_text');?>
		</div>
		<div class="minfo-sidebar-box__sign">
			<?php echo translate('blog_sidebar_founder_name');?>
		</div>
	</div>
</div>
