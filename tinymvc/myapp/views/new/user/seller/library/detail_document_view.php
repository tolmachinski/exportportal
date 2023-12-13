<div class="detail-info">
	<div class="title-public pt-0">
		<h1
            class="title-public__txt"
            <?php echo addQaUniqueIdentifier('page__company-library__document-detail_title'); ?>
        >
            <?php echo $document['title_file'];?>
        </h1>
	</div>

	<div class="spersonal-library-detail__params">
		<div
            class="spersonal-library-detail__category"
            <?php echo addQaUniqueIdentifier('page__company-library__document-detail_category'); ?>
        >
			<?php echo $document['category_title'];?>
		</div>
		<div
            class="spersonal-library-detail__date"
            <?php echo addQaUniqueIdentifier('page__company-library__document-detail_date'); ?>
        >
			<?php echo formatDate($document['add_date_file'], 'd.m.Y H:i A');?>
		</div>
	</div>

	<?php if(($document_type == 'public' && $document['type_file'] == $document_type) || ($document_type == 'private' && is_privileged('user', $company['id_user'])) || $document_type == 'all'){ ?>

		<?php if(isset($document['filePath'])){ ?>
            <?php if($document_type == 'private' && is_privileged('user', $company['id_user'])){?>
                <div class="warning-alert-b"><i class="ep-icon ep-icon_warning-circle-stroke"></i> <?php echo translate('seller_library_document_is_private_only_owner'); ?></div>
            <?php } ?>
			<div class="spersonal-library-detail__load">
				<iframe
                    src="<?php echo 'https://docs.google.com/viewer?url=' . $document['filePath'] . '&embedded=true';?>"
                    width="100%"
                    height="780"
                    style="border: none;"
                    <?php echo addQaUniqueIdentifier('page__company-library__document-detail_iframe'); ?>
                >
                </iframe>
			</div>
		<?php } else{?>
			<div class="warning-alert-b"><i class="ep-icon ep-icon_warning-circle-stroke"></i> <?php echo translate('seller_library_document_not_found'); ?></div>
		<?php }?>

		<div
            class="spersonal-library-detail__text"
            <?php echo addQaUniqueIdentifier('page__company-library__document-detail_description'); ?>
        >
			<?php echo $document['description_file'];?>
		</div>
	<?php } else { ?>
		<div class="warning-alert-b"><i class="ep-icon ep-icon_warning-circle-stroke"></i> <?php echo translate('seller_library_document_is_private'); ?></div>
	<?php } ?>

	<div class="clearfix">
		<a
            class="btn btn-light pl-45 pr-45 fancybox.ajax fancyboxValidateModal"
            data-title="<?php echo translate('seller_library_share_document_title', null, true); ?>"
            href="<?php echo __SITE_URL; ?>seller_library/popup_forms/email/<?php echo $document['id_file'];?>"
            <?php echo addQaUniqueIdentifier('page__company-library__document-detail_email-btn'); ?>
        >
			<i class="ep-icon ep-icon_envelope"></i> <?php echo translate('general_email_this_text'); ?>
		</a>

		<a
            class="btn btn-light pl-45 pr-45 fancybox.ajax fancyboxValidateModal"
            data-title="<?php echo translate('seller_library_share_document_title', null, true); ?>"
            href="<?php echo __SITE_URL; ?>seller_library/popup_forms/share/<?php echo $document['id_file'];?>"
            <?php echo addQaUniqueIdentifier('page__company-library__document-detail_share-btn'); ?>
        >
			<i class="ep-icon ep-icon_share-stroke"></i> <?php echo translate('seller_library_share_this_text'); ?>
		</a>
	</div>
</div>

<div class="title-public">
	<h2 class="title-public__txt">
        <?php echo translate('seller_library_more_documents'); ?>
        <span
            <?php echo addQaUniqueIdentifier('page__company-library__document-detail_more-documents_title-counter'); ?>
        >(<?php echo $count; ?>)</span>
    </h2>
</div>

<ul
    class="spersonal-library"
    <?php echo addQaUniqueIdentifier('page__company-library__document-detail_more-documents-list'); ?>
>
	<?php views()->display('new/user/seller/library/item_view'); ?>
</ul>

<?php if ($count > count($documents) && !isset($pagination)) {?>
	<div class="flex-display flex-jc--c">
		<a
            class="btn btn-outline-dark btn-block mw-280"
            href="<?php echo $more_documents_btn_link;?>"
            <?php echo addQaUniqueIdentifier('page__company-library__document-detail_view-more-btn'); ?>
        >
            <?php echo translate('general_view_more_btn');?>
        </a>
	</div>
<?php }?>

<?php views()->display('new/user/seller/library/library_scripts_view'); ?>
