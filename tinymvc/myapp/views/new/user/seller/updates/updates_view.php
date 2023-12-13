<?php views()->display('new/user/seller/company/company_menu_block'); ?>

<div class="title-public pt-0">
	<h1 class="title-public__txt"><?php echo translate('seller_updates_h1_title');?></h1>

	<?php if(logged_in() && have_right('have_updates') && of_my($company['id_company'], 'id_company')){?>
	<div class="dropdown">
		<a
            class="dropdown-toggle"
            data-toggle="dropdown"
            aria-haspopup="true"
            aria-expanded="false"
            href="#"
            <?php echo addQaUniqueIdentifier('company-updates__header-dropdown-btn'); ?>
        >
			<i class="ep-icon ep-icon_menu-circles"></i>
		</a>
		<div class="dropdown-menu">
			<button
                class="dropdown-item fancybox.ajax fancyboxValidateModal"
				data-fancybox-href="<?php echo __SITE_URL . 'seller_updates/popup_forms/add_update';?>"
				data-title="<?php echo translate('seller_updates_add_update_button_title', null, true);?>"
				title="<?php echo translate('seller_updates_add_update_button_title', null, true);?>"
                type="button"
                <?php echo addQaUniqueIdentifier('company-updates__header-dropdown-menu_add-update-btn'); ?>
            >
				<i class="ep-icon ep-icon_arrow-line-up "></i> <?php echo translate('seller_updates_add_update_button_text');?>
			</button>
		</div>
	</div>
	<?php }?>
</div>

<ul class="spersonal-updates" id="ul_updates_list">
	<?php if(isset($updates) && !empty($updates)){?>
		<?php views()->display('new/user/seller/updates/updates_list_view'); ?>
	<?php } else{?>
		<li class="empty-updates"><div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> <?php echo translate('seller_updates_no_updates_text');?></div></li>
	<?php }?>
</ul>

<div class="pt-10 flex-display flex-jc--sb flex-ai--c">
	<?php views()->display('new/paginator_view'); ?>
</div>

<?php views()->display('new/user/seller/updates/updates_scripts_view'); ?>
