<?php views()->display('new/user/seller/company/company_menu_block'); ?>

<div class="title-public pt-0">
	<h1 class="title-public__txt"><?php echo translate('seller_library_library_title');?></h1>

	<?php if(logged_in() && have_right('have_updates') && of_my($company['id_company'], 'id_company')){?>
	<div class="dropdown">
		<a
            class="dropdown-toggle"
            data-toggle="dropdown"
            aria-haspopup="true"
            aria-expanded="false"
            href="#"
            <?php echo addQaUniqueIdentifier('page__company-library__heading_dropdown-menu_btn'); ?>
        >
			<i class="ep-icon ep-icon_menu-circles"></i>
		</a>
		<div class="dropdown-menu">
			<a
                class="dropdown-item fancybox.ajax fancyboxValidateModal"
                data-title="<?php echo translate('seller_library_add_document_title', null, true);?>"
                href="<?php echo __SITE_URL;?>seller_library/popup_forms/add_document"
                <?php echo addQaUniqueIdentifier('page__company-library__heading_dropdown-menu_add-document-btn'); ?>
            >
                <i class="ep-icon ep-icon_arrow-line-up "></i> <?php echo translate('seller_library_add_document_title');?>
            </a>
		</div>
	</div>
	<?php }?>
</div>

<ul class="spersonal-library" <?php echo addQaUniqueIdentifier('page__company-library__document-list'); ?>>
	<?php views()->display('new/user/seller/library/item_view'); ?>

	<?php if(empty($documents)){?>
		<li class="empty-library w-100pr"><div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> <?php echo translate('seller_library_no_documents_message');?></div></li>
	<?php }?>
</ul>

<div class="pt-10 flex-display flex-jc--sb flex-ai--c" <?php echo addQaUniqueIdentifier('page__company-library__paginator'); ?>>
	<?php views()->display('new/paginator_view'); ?>
</div>

<?php views()->display('new/file_upload_scripts'); ?>
<?php views()->display('new/user/seller/library/library_scripts_view'); ?>
