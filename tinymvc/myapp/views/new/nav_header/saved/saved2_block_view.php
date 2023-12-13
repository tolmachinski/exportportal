<div id="js-epuser-saved-menu" class="epuser-subline-nav2">
	<div class="epuser-subline-nav2__item epuser-subline-nav2__item--multiple epuser-subline-nav2__item--hiden-660">
        <a
            class="link call-function call-action <?php if($counter_contact == 0){?>disabled<?php }?>"
            data-js-action="saved:laod-saved-list"
            data-callback="btnlaodSavedList"
            data-type="contact"
            href="#"
        >
			<span class="txt"><?php echo translate('header_navigation_saved_contacts');?></span>
			<span class="count"><?php echo $counter_contact;?></span>
		</a>
        <a
            class="link call-function call-action <?php if($counter_company == 0){?>disabled<?php }?>"
            data-js-action="saved:laod-saved-list"
            data-callback="btnlaodSavedList"
            data-type="directory"
            href="#"
        >
			<span class="txt"><?php echo translate('header_navigation_saved_sellers');?></span>
			<span class="count"><?php echo $counter_company;?></span>
		</a>
        <a
            class="link call-function call-action <?php if($counter_shippers == 0){?>disabled<?php }?>"
            data-js-action="saved:laod-saved-list"
            data-callback="btnlaodSavedList"
            data-type="shippers"
            href="#"
        >
			<span class="txt"><?php echo translate('header_navigation_saved_shippers');?></span>
			<span class="count"><?php echo $counter_shippers;?></span>
		</a>
		<?php if($counter_b2b !== false  && i_have_company()){?>
        <a
            class="link call-function call-action <?php if($counter_b2b == 0){?>disabled<?php }?>"
            data-js-action="saved:laod-saved-list"
            data-callback="btnlaodSavedList"
            data-type="b2b"
            href="#"
        >
			<span class="txt"><?php echo translate('header_navigation_saved_b2b_partners');?></span>
			<span class="count"><?php echo $counter_b2b;?></span>
		</a>
		<?php }?>
        <a
            class="link call-function call-action <?php if($counter_product == 0){?>disabled<?php }?>"
            data-js-action="saved:laod-saved-list"
            data-callback="btnlaodSavedList"
            data-type="items"
            href="#"
        >
			<span class="txt"><?php echo translate('header_navigation_saved_products');?></span>
			<span class="count"><?php echo $counter_product;?></span>
		</a>
        <a
            class="link call-function call-action <?php if($counter_search == 0){?>disabled<?php }?>"
            data-js-action="saved:laod-saved-list"
            data-callback="btnlaodSavedList"
            data-type="save_search"
            href="#"
        >
			<span class="txt"><?php echo translate('header_navigation_saved_search');?></span>
			<span class="count"><?php echo $counter_search;?></span>
		</a>
	</div>

	<div class="epuser-subline-filter--mobile-660">
		<div id="js-epuser-saved-menu-dropdown" class="dropdown">
			<a class="btn btn-light btn-block dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
				<span class="txt"><?php echo translate('header_navigation_saved_contacts');?></span>
				<span class="count"><?php echo $counter_contact;?></span>
				<i class="ep-icon ep-icon_arrow-down fs-10"></i>
			</a>

			<div class="dropdown-menu dropdown-menu-right">
                <a
                    class="dropdown-item pl-45 call-function call-action <?php if($counter_contact == 0){?>disabled<?php }?>"
                    data-callback="btnlaodSavedList"
                    data-js-action="saved:laod-saved-list"
                    data-type="contact"
                    href="#"
                >
					<span class="txt"><?php echo translate('header_navigation_saved_contacts');?></span>
					<span class="count"><?php echo $counter_contact;?></span>
				</a>
                <a
                    class="dropdown-item pl-45 call-function call-action <?php if($counter_company == 0){?>disabled<?php }?>"
                    data-callback="btnlaodSavedList"
                    data-js-action="saved:laod-saved-list"
                    data-type="directory"
                    href="#"
                >
					<span class="txt"><?php echo translate('header_navigation_saved_sellers');?></span>
					<span class="count"><?php echo $counter_company;?></span>
				</a>
                <a
                    class="dropdown-item pl-45 call-function call-action <?php if($counter_shippers == 0){?>disabled<?php }?>"
                    data-callback="btnlaodSavedList"
                    data-js-action="saved:laod-saved-list"
                    data-type="shippers"
                    href="#"
                >
					<span class="txt"><?php echo translate('header_navigation_saved_shippers');?></span>
					<span class="count"><?php echo $counter_shippers;?></span>
				</a>
				<?php if($counter_b2b !== false  && i_have_company()){?>
                <a
                    class="dropdown-item pl-45 call-function call-action <?php if($counter_b2b == 0){?>disabled<?php }?>"
                    data-callback="btnlaodSavedList"
                    data-js-action="saved:laod-saved-list"
                    data-type="b2b"
                    href="#"
                >
					<span class="txt"><?php echo translate('header_navigation_saved_b2b_partners');?></span>
					<span class="count"><?php echo $counter_b2b;?></span>
				</a>
				<?php }?>
                <a
                    class="dropdown-item pl-45 call-function call-action <?php if($counter_product == 0){?>disabled<?php }?>"
                    data-callback="btnlaodSavedList"
                    data-js-action="saved:laod-saved-list"
                    data-type="items"
                    href="#"
                >
					<span class="txt"><?php echo translate('header_navigation_saved_products');?></span>
					<span class="count"><?php echo $counter_product;?></span>
				</a>
                <a
                    class="dropdown-item pl-45 call-function call-action <?php if($counter_search == 0){?>disabled<?php }?>"
                    data-callback="btnlaodSavedList"
                    data-js-action="saved:laod-saved-list"
                    data-type="save_search"
                    href="#"
                >
					<span class="txt"><?php echo translate('header_navigation_saved_search');?></span>
					<span class="count"><?php echo $counter_search;?></span>
				</a>
			</div>
		</div>
	</div>
</div>

<?php tmvc::instance()->controller->view->display('new/nav_header/saved/contact_header_list_view');?>

