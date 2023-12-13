<?php foreach($documents as $item){ ?>
    <li
        id="document-<?php echo $item['id_file'];?>-block"
        class="spersonal-library__item"
        <?php echo addQaUniqueIdentifier('page__company-library__item'); ?>
    >
        <div class="spersonal-library__top">
            <h4 class="spersonal-library__ttl">
                <a
                    class="link"
                    href="<?php echo $base_company_url;?>/document/<?php echo $item['id_file'];?>"
                    <?php echo addQaUniqueIdentifier('page__company-library__item_title'); ?>
                >
                    <?php echo $item['title_file'];?>
                </a>
            </h4>

            <?php if(logged_in()){ ?>
                <div class="dropdown">
                    <a
                        class="dropdown-toggle"
                        data-toggle="dropdown"
                        aria-haspopup="true"
                        aria-expanded="false"
                        href="#"
                        <?php echo addQaUniqueIdentifier('page__company-library__item_dropdown-menu_btn'); ?>
                    >
                        <i class="ep-icon ep-icon_menu-circles"></i>
                    </a>

                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">
                        <?php if(is_privileged('user', $item['id_seller'], 'have_library')){ ?>
                            <a
                                class="dropdown-item fancybox.ajax fancyboxValidateModal"
                                data-title="<?php echo translate('seller_library_edit_document_title', null, true);?>"
                                data-fancybox-href="seller_library/popup_forms/edit_document/<?php echo $item['id_file'];?>"
                                title="<?php echo translate('seller_library_edit_document_title', null, true);?>"
                                <?php echo addQaUniqueIdentifier('page__company-library__item_dropdown-menu_edit-btn'); ?>
                            >
                                <i class="ep-icon ep-icon_pencil"></i> <?php echo translate('seller_library_edit_word');?>
                            </a>
                        <?php } ?>
                        <a
                            class="dropdown-item fancybox.ajax fancyboxValidateModal"
                            data-title="<?php echo translate('seller_library_share_document_title', null, true);?>"
                            data-fancybox-href="seller_library/popup_forms/share/<?php echo $item['id_file'];?>"
                            <?php echo addQaUniqueIdentifier('page__company-library__item_dropdown-menu_share-btn'); ?>
                        >
                            <i class="ep-icon ep-icon_share-stroke"></i> <?php echo translate('seller_library_share_word');?>
                        </a>
                        <a
                            class="dropdown-item fancybox.ajax fancyboxValidateModal"
                            data-title="<?php echo translate('seller_library_send_email_title', null, true);?>"
                            data-fancybox-href="seller_library/popup_forms/email/<?php echo $item['id_file'];?>"
                            <?php echo addQaUniqueIdentifier('page__company-library__item_dropdown-menu_email-btn'); ?>
                        >
                            <i class="ep-icon ep-icon_envelope"></i> <?php echo translate('seller_library_email_word');?>
                        </a>
                    </div>
                </div>
            <?php } ?>
        </div>

        <div class="spersonal-library__params">
            <span
                class="spersonal-library__category"
                <?php echo addQaUniqueIdentifier('page__company-library__item_category'); ?>
            >
                <?php echo $item['category_title'];?>
            </span>
            <div
                class="spersonal-library__date"
                <?php echo addQaUniqueIdentifier('page__company-library__item_date'); ?>
            >
                <?php echo formatDate($item['add_date_file'], 'd.m.Y');?>
            </div>
        </div>
        <div
            class="spersonal-library__text"
            <?php echo addQaUniqueIdentifier('page__company-library__item_description'); ?>
        >
            <?php echo truncWords($item['description_file'],30);?>
        </div>
    </li>
<?php }?>
