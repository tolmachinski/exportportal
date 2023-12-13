<div id="epuser-saved2" class="epuser-subline-wr">
	<?php tmvc::instance()->controller->view->display('new/nav_header/saved/saved2_block_view'); ?>
</div>
<?php echo dispatchDynamicFragmentInCompatMode("popup:saved-popup", asset('public/plug/js/saved/popup.js', 'legacy'), null, null); ?>
