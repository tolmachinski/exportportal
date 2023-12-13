<ul class="nav nav-tabs flex-jc--sb nav--p0 nav--toggle nav--gray nav--borders inputs-40" role="tablist">
	<li class="nav-item">
		<a class="nav-link active" href="#header-toggle-basket" aria-controls="title" role="tab" data-toggle="tab">
			<i class="ep-icon ep-icon_items"></i> By sellers
		</a>
	</li>
	<li class="nav-item">
		<a class="btn btn-light" href="<?php echo __SITE_URL;?>basket/my"><?php echo translate('header_navigation_popup_basket_link_to_basket_page');?></a>
	</li>
</ul>

<div class="tab-content tab-content--new tab-content--toggle inputs-40">
	<div role="tabpanel" class="tab-pane fade show active" id="header-toggle-basket">
		<?php
			tmvc::instance()->controller->view->display('new/nav_header/basket/basket_list_view');
		?>
	</div>
</div>
