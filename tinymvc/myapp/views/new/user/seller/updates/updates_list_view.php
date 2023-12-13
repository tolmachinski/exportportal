<?php foreach($updates as $item){?>
	<li
        class="spersonal-updates__item"
        id="update-<?php echo $item['id_update'];?>-block"
        <?php echo addQaUniqueIdentifier('page__company-updates__list_item'); ?>
    >
		<div class="spersonal-updates__txt flex-card ep-tinymce-text">
			<?php if($item['photo_path'] != ''){?>
				<div class="spersonal-updates__img flex-card__fixed image-card">
					<a
                        class="link fancyboxGallery"
                        href="<?php echo $item['imageLink'];?>"
                        data-title="<?php echo $company['name_company'];?>"
                        title="<?php echo $company['name_company'];?>"
                        <?php echo addQaUniqueIdentifier('page__company-updates__list_image-link'); ?>
                    >
						<img
                            class="image"
                            src="<?php echo $item['imageThumbLink'];?>"
                            alt="<?php echo $company['name_company'];?>"
                            <?php echo addQaUniqueIdentifier('page__company-updates__list_image'); ?>
                        />
					</a>
				</div>
			<?php }?>
			<div class="spersonal-updates__desc flex-card__float">
				<div class="spersonal-updates__top">
					<div class="spersonal-updates__date" <?php echo addQaUniqueIdentifier('page__company-updates__list_date'); ?>>
                        <?php echo formatDate($item['date_update'], 'd.m.Y H:i A');?>
                    </div>
					<div class="dropdown">
						<a
                            class="dropdown-toggle"
                            data-toggle="dropdown"
                            aria-haspopup="true"
                            aria-expanded="false"
                            href="#"
                            <?php echo addQaUniqueIdentifier('page__company-updates__list_actions-dropdown-btn'); ?>
                        >
							<i class="ep-icon ep-icon_menu-circles"></i>
						</a>
						<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
							<?php if(logged_in()){ ?>
								<button
                                    class="dropdown-item fancyboxValidateModal fancybox.ajax"
                                    data-title="<?php echo translate('seller_updates_send_update_title', null, true);?>"
                                    data-fancybox-href="seller_updates/popup_forms/email/<?php echo $item['id_update'];?>"
                                    type="button"
                                    <?php echo addQaUniqueIdentifier('page__company-updates__list_actions-dropdown-menu_email-btn'); ?>
                                >
									<i class="ep-icon ep-icon_envelope-send"></i><span class="txt"><?php echo translate('seller_updates_email_text');?></span>
								</button>
								<button
                                    class="dropdown-item fancyboxValidateModal fancybox.ajax"
                                    data-title="<?php echo translate('seller_updates_share_update_title', null, true);?>"
                                    data-fancybox-href="seller_updates/popup_forms/share/<?php echo $item['id_update'];?>"
                                    type="button"
                                    <?php echo addQaUniqueIdentifier('page__company-updates__list_actions-dropdown-menu_share-btn'); ?>
                                >
									<i class="ep-icon ep-icon_share-stroke"></i><span class="txt"><?php echo translate('seller_updates_share_update_text');?></span>
								</button>
								<button
                                    class="dropdown-item fancyboxValidateModal fancybox.ajax"
                                    data-title="<?php echo translate('seller_updates_report_update_title', null, true);?>"
                                    data-fancybox-href="complains/popup_forms/add_complain/company_updates/<?php echo $item['id_update'];?>/<?php echo $company['id_user']; ?>/<?php echo $company['id_company']; ?>"
                                    type="button"
                                    <?php echo addQaUniqueIdentifier('page__company-updates__list_actions-dropdown-menu_report-btn'); ?>
                                >
									<i class="ep-icon ep-icon_warning-circle-stroke"></i><span class="txt"><?php echo translate('seller_updates_report_update_text');?></span>
								</button>
							<?php } else { ?>
								<button
                                    class="dropdown-item call-systmess"
                                    data-message="<?php echo translate('seller_updates_not_logged_in_error_text', null, true);?>"
                                    data-type="error"
                                    title="<?php echo translate('seller_updates_send_update_title', null, true);?>"
                                    type="button"
                                    <?php echo addQaUniqueIdentifier('page__company-updates__list_actions-dropdown-menu_email-btn'); ?>
                                >
									<i class="ep-icon ep-icon_envelope-send"></i><span class="txt"><?php echo translate('seller_updates_email_text');?></span>
								</button>
								<button
                                    class="dropdown-item call-systmess"
                                    data-message="<?php echo translate('seller_updates_not_logged_in_error_text', null, true);?>"
                                    data-type="error"
                                    title="<?php echo translate('seller_updates_share_update_title', null, true);?>"
                                    type="button"
                                    <?php echo addQaUniqueIdentifier('page__company-updates__list_actions-dropdown-menu_share-btn'); ?>
                                >
									<i class="ep-icon ep-icon_share-stroke"></i><span class="txt"><?php echo translate('seller_updates_share_update_text');?></span>
								</button>
                            <?php } ?>

							<?php if(logged_in() && $seller_view && have_right('have_updates')){?>
                                <button
                                    class="dropdown-item fancybox.ajax fancyboxValidateModal"
                                    data-title="<?php echo translate('seller_updates_edit_update_title', null, true);?>"
                                    title="<?php echo translate('seller_updates_edit_update_title', null, true);?>"
                                    data-fancybox-href="seller_updates/popup_forms/edit_update/<?php echo $item['id_update'];?>"
                                    type="button"
                                    <?php echo addQaUniqueIdentifier('page__company-updates__list_actions-dropdown-menu_edit-btn'); ?>
                                >
                                    <i class="ep-icon ep-icon_pencil"></i><span class="txt"><?php echo translate('seller_updates_edit_update_text');?></span>
                                </button>
							<?php }?>
						</div>
					</div>
				</div>

				<div class="spersonal-updates__text" <?php echo addQaUniqueIdentifier('page__company-updates__list_text'); ?>>
					<?php echo $item['text_update'];?>
				</div>
			</div>
		</div>
	</li>
<?php }?>
