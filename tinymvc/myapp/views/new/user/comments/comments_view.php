<?php if(isset($comments) && !empty($comments)){?>
	<?php tmvc::instance()->controller->view->display('new/items_comments/comments_scripts_view'); ?>
<?php }?>
<a class="btn btn-primary btn-panel-left mb-25 fancyboxSidebar fancybox" data-title="Category" href="#main-flex-card__fixed-left">
	<i class="ep-icon ep-icon_items"></i>
	Sidebar
</a>

<div class="title-public pt-0">
	<h1 class="title-public__txt">Comments</h1>
</div>

<?php tmvc::instance()->controller->view->display('new/items_comments/list_view'); ?>

<div class="pt-25 flex-display flex-jc--sb flex-ai--c">
	<?php tmvc::instance()->controller->view->display("new/per_page_view"); ?>
	<?php tmvc::instance()->controller->view->display("new/paginator_view"); ?>
</div>	
