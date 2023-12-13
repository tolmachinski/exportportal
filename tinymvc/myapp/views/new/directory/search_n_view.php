<div class="directory-page">
	<a class="btn btn-primary btn-panel-left fancyboxSidebar fancybox mb-25" data-title="Category" href="#main-flex-card__fixed-left">
		<i class="ep-icon ep-icon_items"></i>
		Sidebar
	</a>

	<div class="clearfix pb-15 pt-3">
		<?php tmvc::instance()->controller->view->display('new/search_counter_view'); ?>
    	<?php tmvc::instance()->controller->view->display('new/directory/nav_list_grid_category_view'); ?>
	</div>

	<?php tmvc::instance()->controller->view->display('new/directory/list_view'); ?>

	<div class="pt-10 flex-display flex-jc--sb flex-ai--c">
		<span></span>
   		<?php tmvc::instance()->controller->view->display('new/paginator_view'); ?>
    </div>
</div>
