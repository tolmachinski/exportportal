<div class="userinfo-scheme container-1420">
    <h2 class="userinfo-scheme__ttl"><?php echo translate('selling_scheme_ttl');?></h2>
    <div class="userinfo-scheme__row">
        <div class="userinfo-scheme__col">
            <div class="userinfo-scheme__wrap">
                <div class="userinfo-scheme__icon-wrap">
                    <img class="image js-lazy" src="<?php echo getLazyImage(60, 60);?>" data-src="<?php echo asset('public/build/images/selling/users-shopping-stroke.svg'); ?>" alt="<?php echo translate('selling_users_shopping_img');?>">
                </div>
                <span class="userinfo-scheme__name">
                    <?php echo translate('selling_scheme_text_market_customer');?>
                </span>
            </div>
        </div>
        <div class="userinfo-scheme__col">
            <div class="userinfo-scheme__wrap">
                <div class="userinfo-scheme__icon-wrap">
                    <img class="image js-lazy userinfo-scheme__icon-sheild" src="<?php echo getLazyImage(60, 60);?>" data-src="<?php echo asset('public/build/images/selling/sheild-ok2.svg'); ?>" alt="<?php echo translate('selling_sheild_ok_img');?>">
                </div>
                <span class="userinfo-scheme__name">
                    <?php echo translate('selling_scheme_text_make_process');?>
                </span>
            </div>
        </div>
        <div class="userinfo-scheme__col">
            <div class="userinfo-scheme__wrap">
                <div class="userinfo-scheme__icon-wrap">
                    <img class="image js-lazy userinfo-scheme__icon-paper" src="<?php echo getLazyImage(60, 60);?>" data-src="<?php echo asset('public/build/images/selling/paper-stroke.svg'); ?>" alt="<?php echo translate('selling_paper_img');?>">
                </div>
                <span class="userinfo-scheme__name">
                    <?php echo translate('selling_scheme_text_products_following');?>
                </span>
            </div>
        </div>
        <div class="userinfo-scheme__col">
            <div class="userinfo-scheme__wrap">
                <div class="userinfo-scheme__icon-wrap">
                    <img class="image js-lazy" src="<?php echo getLazyImage(60, 60);?>" data-src="<?php echo asset('public/build/images/selling/support-stroke.svg'); ?>" alt="<?php echo translate('selling_support_img');?>">
                </div>
                <span class="userinfo-scheme__name">
                    <?php echo translate('selling_scheme_text_get_help');?>
                </span>
            </div>
        </div>
        <div class="userinfo-scheme__col">
            <div class="userinfo-scheme__wrap">
                <div class="userinfo-scheme__icon-wrap">
                    <img class="image js-lazy userinfo-scheme__select-shipper-icon" src="<?php echo getLazyImage(60, 60);?>" data-src="<?php echo asset('public/build/images/selling/select-shipper-stroke.svg'); ?>" alt="<?php echo translate('selling_select_shipper_img');?>">
                </div>
                <span class="userinfo-scheme__name">
                    <?php echo translate('selling_scheme_text_select_shipper');?>
                </span>
            </div>
        </div>
        <div class="userinfo-scheme__col">
            <div class="userinfo-scheme__wrap">
                <div class="userinfo-scheme__icon-wrap">
                    <img class="image js-lazy" src="<?php echo getLazyImage(60, 60);?>" data-src="<?php echo asset('public/build/images/selling/megaphone2.svg'); ?>" alt="<?php echo translate('selling_megaphone_img');?>">
                </div>
                <span class="userinfo-scheme__name">
                    <?php echo translate('selling_scheme_text_get_order');?>
                </span>
            </div>
        </div>
        <div class="userinfo-scheme__col bdb-none">
            <div class="userinfo-scheme__wrap">
                <div class="userinfo-scheme__icon-wrap">
                    <img class="image js-lazy userinfo-scheme__card-lock-icon" src="<?php echo getLazyImage(60, 60);?>" data-src="<?php echo asset('public/build/images/selling/card-lock2.svg'); ?>" alt="<?php echo translate('selling_card_lock_img');?>">
                </div>
                <span class="userinfo-scheme__name">
                    <?php echo translate('selling_scheme_text_protect_money');?>
                </span>
            </div>
        </div>
        <div class="userinfo-scheme__col bdb-none">
            <div class="userinfo-scheme__wrap">
                <div class="userinfo-scheme__icon-wrap">
                    <img class="image js-lazy" src="<?php echo getLazyImage(60, 60);?>" data-src="<?php echo asset('public/build/images/selling/get-paid-stroke.svg'); ?>" alt="<?php echo translate('selling_get_paid_img');?>">
                </div>
                <span class="userinfo-scheme__name">
                    <?php echo translate('selling_scheme_text_get_paid');?>
                </span>
            </div>
        </div>
        <div class="userinfo-scheme__col bdb-none">
            <div class="userinfo-scheme__wrap">
                <div class="userinfo-scheme__icon-wrap">
                    <img class="image js-lazy userinfo-scheme__icon-box" src="<?php echo getLazyImage(60, 60);?>" data-src="<?php echo asset('public/build/images/selling/box-icon.svg'); ?>" alt="<?php echo translate('selling_box_icon_img');?>">
                </div>
                <?php $autoplay = !isBackstopEnabled() ? 'true' : 'false'; ?>
                <span class="userinfo-scheme__name">
                    <?php echo translate('selling_scheme_text_add_items', [
                        '{{START_TAG}}'         => '<a class="userinfo-scheme__btn call-function call-action"' . addQaUniqueIdentifier("page__selling_bulk_upload_btn") . ' data-js-action="modal:open-video-modal" data-title="'. translate('popup_bulk_item_upload_ttl', null, true). '" data-href="'.  config("my_items_bulk_upload_video_url"). '" data-autoplay="'. $autoplay .'" title="'.  translate('popup_bulk_item_upload_ttl', null, true). '" data-mw="1920" data-w="80%" data-h="88%">',
                        '{{END_TAG}}'           => '</a>',
                    ]);?>
                </span>
            </div>
        </div>
    </div>
</div>
