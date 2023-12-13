<div class="mobile-links">
    <a class="btn btn-primary btn-panel-left fancyboxSidebar fancybox" data-title="Menu" href="#main-flex-card__fixed-left">
        <i class="ep-icon ep-icon_items"></i>
        <?php echo translate('label_menu');?>
    </a>
</div>

<div class="user-guides">

    <div class="user-guides__item">
        <div class="user-guides__img">
            <picture class="display-b h-100pr">
                <source media="(max-width: 425px)" srcset="<?php echo asset("public/build/images/user-guides/registration-mobile.jpg"); ?>">
                <source media="(min-width: 426px) and (max-width: 1200px)" srcset="<?php echo asset("public/build/images/user-guides/registration-tablet.jpg"); ?>">
                <img class="image" src="<?php echo asset("public/build/images/user-guides/registration.jpg"); ?>" alt="<?php echo translate('user_guide_registration_ttl', null, true); ?>">
            </picture>
        </div>

        <div class="user-guides__detail">
            <div class="user-guides__ttl"><?php echo translate('user_guide_registration_ttl'); ?></div>

            <p class="user-guides__desc"><?php echo translate('user_guide_registration_desc'); ?></p>

            <div class="user-guides__info">
                <div class="user-guides__info-ttl"><?php echo translate('user_guide_info_list_ttl'); ?></div>

                <ul class="user-guides-info-list">
                    <li class="user-guides-info-list__item"><?php echo translate('user_guide_registration_info_list_item1'); ?></li>
                    <li class="user-guides-info-list__item"><?php echo translate('user_guide_registration_info_list_item2'); ?></li>
                    <li class="user-guides-info-list__item"><?php echo translate('user_guide_registration_info_list_item3'); ?></li>
                    <li class="user-guides-info-list__item"><?php echo translate('user_guide_registration_info_list_item4'); ?></li>
                </ul>
            </div>

            <div class="user-guides__btns">
                <button
                    class="btn btn-primary call-function"
                    data-callback="downloadGuide"
                    data-guide-name="registration"
                    data-lang="en"
                    data-group="all"
                >
                    <?php echo translate('user_guide_download_btn'); ?>
                </button>
                <a
                    class="btn btn-outline-dark fancyboxVideo fancybox.iframe"
                    data-title="<?php echo translate('user_guide_registration_ttl', null, true); ?>"
                    href="https://www.youtube.com/embed/vnqwhhBFEhI"
                    title="<?php echo translate('user_guide_registration_ttl', null, true); ?>"
                    data-mw="1920"
                    data-w="80%"
                    data-h="88%"
                    data-dashboard-class="youtube-video"
                >
                    <?php echo translate('user_guide_watch_video_btn'); ?>
                </a>
            </div>
        </div>
    </div>

    <div class="user-guides__item user-guides__item--reverse">
        <div class="user-guides__img">
            <picture class="display-b h-100pr">
                <source media="(max-width: 425px)" srcset="<?php echo asset("public/build/images/user-guides/profile_completion-mobile.jpg"); ?>">
                <source media="(min-width: 426px) and (max-width: 1200px)" srcset="<?php echo asset("public/build/images/user-guides/profile_completion-tablet.jpg"); ?>">
                <img src="<?php echo asset("public/build/images/user-guides/profile_completion.jpg"); ?>" alt="<?php echo translate('user_guide_profile_completion_ttl', null, true); ?>">
            </picture>
        </div>

        <div class="user-guides__detail">
            <div class="user-guides__ttl"><?php echo translate('user_guide_profile_completion_ttl'); ?></div>

            <p class="user-guides__desc"><?php echo translate('user_guide_profile_completion_desc'); ?></p>

            <div class="user-guides__info">
                <div class="user-guides__info-ttl"><?php echo translate('user_guide_info_list_ttl'); ?></div>

                <ul class="user-guides-info-list">
                    <li class="user-guides-info-list__item"><?php echo translate('user_guide_profile_completion_info_list_item1'); ?></li>
                    <li class="user-guides-info-list__item"><?php echo translate('user_guide_profile_completion_info_list_item2'); ?></li>
                    <li class="user-guides-info-list__item"><?php echo translate('user_guide_profile_completion_info_list_item3'); ?></li>
                    <li class="user-guides-info-list__item"><?php echo translate('user_guide_profile_completion_info_list_item4'); ?></li>
                </ul>
            </div>

            <div class="user-guides__btns user-guides__btns--100pr">
                <button
                    class="btn btn-primary call-function"
                    data-callback="downloadGuide"
                    data-guide-name="profile_completion"
                    data-lang="en"
                    data-group="all"
                >
                    <?php echo translate('user_guide_download_btn'); ?>
                </button>
            </div>

            <div class="user-guides-videos">
                <div class="user-guides-videos__header">
                    <div class="user-guides-videos__ttl"><?php echo translate('user_guide_watch_needed_video'); ?></div>
                </div>

                <ul class="user-guides-videos-list">
                    <li class="user-guides-videos-list__item">
                        <a
                            class="user-guides-videos-list__link fancyboxVideo fancybox.iframe"
                            data-title="<?php echo translate('user_guide_profile_completion_ttl', null, true); ?>"
                            href="https://www.youtube.com/embed/mGjdoIHmPZ8"
                            title="<?php echo translate('user_guide_profile_completion_ttl', null, true); ?>"
                            data-mw="1920"
                            data-w="80%"
                            data-h="88%"
                        >
                            <span class="user-guides-videos-list__play-btn"><?php echo widgetGetSvgIcon('play', 25, 25); ?></span>
                            <span><?php echo translate('user_guide_profile_completion_buyer'); ?></span>
                        </a>
                    </li>
                    <li class="user-guides-videos-list__item">
                        <a
                            class="user-guides-videos-list__link fancyboxVideo fancybox.iframe"
                            data-title="<?php echo translate('user_guide_profile_completion_ttl', null, true); ?>"
                            href="https://www.youtube.com/embed/kJkCxgIeNFQ"
                            title="<?php echo translate('user_guide_profile_completion_ttl', null, true); ?>"
                            data-mw="1920"
                            data-w="80%"
                            data-h="88%"
                        >
                            <span class="user-guides-videos-list__play-btn"><?php echo widgetGetSvgIcon('play', 25, 25); ?></span>
                            <span><?php echo translate('user_guide_profile_completion_seller'); ?></span>
                        </a>
                    </li>
                    <li class="user-guides-videos-list__item">
                        <a
                            class="user-guides-videos-list__link fancyboxVideo fancybox.iframe"
                            data-title="<?php echo translate('user_guide_profile_completion_ttl', null, true); ?>"
                            href="https://www.youtube.com/embed/ovGKdkdpJ9g"
                            title="<?php echo translate('user_guide_profile_completion_ttl', null, true); ?>"
                            data-mw="1920"
                            data-w="80%"
                            data-h="88%"
                        >
                            <span class="user-guides-videos-list__play-btn"><?php echo widgetGetSvgIcon('play', 25, 25); ?></span>
                            <span><?php echo translate('user_guide_profile_completion_ff'); ?></span>
                        </a>
                    </li>
                    <li class="user-guides-videos-list__item">
                        <a
                            class="user-guides-videos-list__link fancyboxVideo fancybox.iframe"
                            data-title="<?php echo translate('user_guide_profile_completion_ttl', null, true); ?>"
                            href="https://www.youtube.com/embed/VBmsF2kB69Q"
                            title="<?php echo translate('user_guide_profile_completion_ttl', null, true); ?>"
                            data-mw="1920"
                            data-w="80%"
                            data-h="88%"
                        >
                            <span class="user-guides-videos-list__play-btn"><?php echo widgetGetSvgIcon('play', 25, 25); ?></span>
                            <span><?php echo translate('user_guide_profile_completion_manufacturer'); ?></span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="user-guides__item">
        <div class="user-guides__img">
            <picture class="display-b h-100pr">
                <source media="(max-width: 425px)" srcset="<?php echo asset("public/build/images/user-guides/document_upload-mobile.jpg"); ?>">
                <source media="(min-width: 426px) and (max-width: 1200px)" srcset="<?php echo asset("public/build/images/user-guides/document_upload-tablet.jpg"); ?>">
                <img class="image" src="<?php echo asset("public/build/images/user-guides/document_upload.jpg"); ?>" alt="<?php echo translate('user_guide_document_upload_ttl', null, true); ?>">
            </picture>
        </div>

        <div class="user-guides__detail">
            <div class="user-guides__ttl"><?php echo translate('user_guide_document_upload_ttl'); ?></div>

            <p class="user-guides__desc"><?php echo translate('user_guide_document_upload_desc'); ?></p>

            <div class="user-guides__info">
                <div class="user-guides__info-ttl"><?php echo translate('user_guide_info_list_ttl'); ?></div>

                <ul class="user-guides-info-list">
                    <li class="user-guides-info-list__item"><?php echo translate('user_guide_document_upload_info_list_item1'); ?></li>
                    <li class="user-guides-info-list__item"><?php echo translate('user_guide_document_upload_info_list_item2'); ?></li>
                    <li class="user-guides-info-list__item"><?php echo translate('user_guide_document_upload_info_list_item3'); ?></li>
                    <li class="user-guides-info-list__item"><?php echo translate('user_guide_document_upload_info_list_item4'); ?></li>
                </ul>
            </div>

            <form class="user-guides-form validengine" data-callback="submitGuideForm">
                <div class="user-guides-form__inputs">
                    <div class="user-guides-form__inputs-item">
                        <label class="input-label"><?php echo translate('user_guide_document_upload_user_type_label'); ?></label>
                        <select class="validate[required] js-ug-select-user_type" name="user_type">
                            <option value="" selected disabled><?php echo translate('user_guide_document_upload_choose_user'); ?></option>
                            <option value="buyer"><?php echo translate('user_guide_document_upload_select_buyer'); ?></option>
                            <option value="seller"><?php echo translate('user_guide_document_upload_select_seller'); ?></option>
                            <option value="manufacturer"><?php echo translate('user_guide_document_upload_select_manufacturer'); ?></option>
                            <option value="freight_forwarder"><?php echo translate('user_guide_document_upload_select_ff'); ?></option>
                        </select>
                    </div>

                    <div class="user-guides-form__inputs-item">
                        <label class="input-label"><?php echo translate('user_guide_document_upload_language_label'); ?></label>
                        <select class="validate[required] js-ug-select-lang" name="language">
                            <?php foreach ($documentUploadLangs as $k => $lang) { ?>
                                <option value="<?php echo $k; ?>"><?php echo $lang; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <button
                    class="user-guides-form__btn btn btn-primary js-download-btn"
                    type="submit"
                    data-guide-name="document_upload"
                    data-lang=""
                    data-group=""
                >
                    <?php echo translate('user_guide_download_btn'); ?>
                </button>
            </form>
        </div>
    </div>

    <div class="user-guides__item user-guides__item--reverse">
        <div class="user-guides__img">
            <picture class="display-b h-100pr">
                <source media="(max-width: 425px)" srcset="<?php echo asset("public/build/images/user-guides/add_items-mobile.jpg"); ?>">
                <source media="(min-width: 426px) and (max-width: 1200px)" srcset="<?php echo asset("public/build/images/user-guides/add_items-tablet.jpg"); ?>">
                <img class="image" src="<?php echo asset("public/build/images/user-guides/add_items.jpg"); ?>" alt="<?php echo translate('user_guide_add_item_ttl', null, true); ?>">
            </picture>
        </div>

        <div class="user-guides__detail">
            <div class="user-guides__ttl"><?php echo translate('user_guide_add_item_ttl'); ?></div>

            <p class="user-guides__desc"><?php echo translate('user_guide_add_item_desc'); ?></p>

            <div class="user-guides__info">
                <div class="user-guides__info-ttl"><?php echo translate('user_guide_info_list_ttl'); ?></div>

                <ul class="user-guides-info-list">
                    <li class="user-guides-info-list__item"><?php echo translate('user_guide_add_item_info_list_item1'); ?></li>
                    <li class="user-guides-info-list__item"><?php echo translate('user_guide_add_item_info_list_item2'); ?></li>
                    <li class="user-guides-info-list__item"><?php echo translate('user_guide_add_item_info_list_item3'); ?></li>
                    <li class="user-guides-info-list__item"><?php echo translate('user_guide_add_item_info_list_item4'); ?></li>
                </ul>
            </div>

            <div class="user-guides__btns">
                <button
                    class="btn btn-primary call-function"
                    data-callback="downloadGuide"
                    data-guide-name="add_item"
                    data-lang="en"
                    data-group="all"
                >
                    <?php echo translate('user_guide_download_btn'); ?>
                </button>
                <a
                    class="btn btn-outline-dark fancyboxVideo fancybox.iframe"
                    data-title="<?php echo translate('user_guide_add_item_ttl', null, true); ?>"
                    href="https://www.youtube.com/embed/Z-BJhfVcp_4"
                    title="<?php echo translate('user_guide_add_item_ttl', null, true); ?>"
                    data-mw="1920"
                    data-w="80%"
                    data-h="88%"
                >
                    <?php echo translate('user_guide_watch_video_btn'); ?>
                </a>
            </div>
        </div>
    </div>

    <div class="user-guides__item" id="bulk_item_upload_guide">
        <div class="user-guides__img">
            <picture class="display-b h-100pr">
                <source media="(max-width: 425px)" srcset="<?php echo asset("public/build/images/user-guides/bulk_item_upload-mobile.jpg"); ?>">
                <source media="(min-width: 426px) and (max-width: 1200px)" srcset="<?php echo asset("public/build/images/user-guides/bulk_item_upload-tablet.jpg"); ?>">
                <img class="image" src="<?php echo asset("public/build/images/user-guides/bulk_item_upload.jpg"); ?>" alt="<?php echo translate('user_guide_bulk_upload_item_ttl', null, true); ?>">
            </picture>
        </div>

        <div class="user-guides__detail">
            <div class="user-guides__ttl"><?php echo translate('user_guide_bulk_upload_item_ttl'); ?></div>

            <p class="user-guides__desc"><?php echo translate('user_guide_bulk_upload_item_desc'); ?></p>

            <div class="user-guides__info">
                <div class="user-guides__info-ttl"><?php echo translate('user_guide_info_list_ttl'); ?></div>

                <ul class="user-guides-info-list">
                    <li class="user-guides-info-list__item"><?php echo translate('user_guide_bulk_upload_item_info_list_item1'); ?></li>
                    <li class="user-guides-info-list__item"><?php echo translate('user_guide_bulk_upload_item_info_list_item2'); ?></li>
                    <li class="user-guides-info-list__item"><?php echo translate('user_guide_bulk_upload_item_info_list_item3'); ?></li>
                    <li class="user-guides-info-list__item"><?php echo translate('user_guide_bulk_upload_item_info_list_item4'); ?></li>
                </ul>
            </div>

            <div class="user-guides__btns">
                <button
                    class="btn btn-primary call-function"
                    data-callback="downloadGuide"
                    data-guide-name="item_bulk_upload"
                    data-lang="en"
                    data-group="all"
                >
                    <?php echo translate('user_guide_download_btn'); ?>
                </button>
                <a
                    class="btn btn-outline-dark fancyboxVideo fancybox.iframe"
                    data-title="<?php echo translate('user_guide_bulk_upload_item_ttl', null, true); ?>"
                    href="https://www.youtube.com/embed/NTzntcavZi4"
                    title="<?php echo translate('user_guide_bulk_upload_item_ttl', null, true); ?>"
                    data-mw="1920"
                    data-w="80%"
                    data-h="88%"
                >
                    <?php echo translate('user_guide_watch_video_btn'); ?>
                </a>
            </div>
        </div>
    </div>
</div>
<?php views()->display('new/download_script'); ?>
<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/js/user_guide/index.js'); ?>"></script>
