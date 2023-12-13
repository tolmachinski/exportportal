<?php views()->display('new/user/seller/company/company_menu_block'); ?>
<?php views()->display('new/user/seller/news/news_scripts_view'); ?>

<div class="title-public pt-0">
	<h1 class="title-public__txt"><?php echo translate('seller_news_h1_title');?></h1>

	<?php if(is_privileged('user', $company['id_user'], 'have_news')){?>
	<div class="dropdown">
		<a
            class="dropdown-toggle"
            data-toggle="dropdown"
            aria-haspopup="true"
            aria-expanded="false"
            href="#"
            <?php echo addQaUniqueIdentifier('page__company-news__header_dropdown-btn'); ?>
        >
			<i class="ep-icon ep-icon_menu-circles"></i>
		</a>
		<div class="dropdown-menu">
			<button
                class="dropdown-item fancybox.ajax fancyboxValidateModal"
                data-title="<?php echo translate('seller_news_add_news_text', null, true);?>"
                data-fancybox-href="<?php echo __SITE_URL;?>seller_news/popup_forms/add_news_form"
                type="button"
                <?php echo addQaUniqueIdentifier('page__company-news__header_dropdown-menu_add-btn'); ?>
            >
				<i class="ep-icon ep-icon_arrow-line-up "></i> <?php echo translate('seller_news_add_news_text');?>
			</button>
		</div>
	</div>
	<?php }?>
</div>

<ul class="spersonal-news" id="seller_news_block">
	<?php if (empty($news)) {?>
		<div class="empty_news info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> <span><?php echo translate('seller_news_no_news_yet_message');?></span></div>
	<?php } else {?>
		<?php views()->display('new/user/seller/news/item_news_view');?>
	<?php }?>
</ul>

<div class="pt-10 flex-display flex-jc--sb flex-ai--c" <?php echo addQaUniqueIdentifier('company-news__paginator'); ?>>
	<?php views()->display('new/paginator_view'); ?>
</div>
