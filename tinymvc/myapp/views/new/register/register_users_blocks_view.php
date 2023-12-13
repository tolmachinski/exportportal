<div class="register-users-blocks">
    <div class="register-users-blocks__row">
        <div class="register-users-blocks__item register-users-blocks__image">
            <img
                class="image js-lazy"
                width="720"
                height="400"
                data-src="<?php echo __IMG_URL . 'public/img/register/happy-couple.jpg';?>"
                src="<?php echo getLazyImage(720, 400); ?>"
                alt="<?php echo translate('register_block_buyer_header_img', null, true);?>"
            >
        </div>

        <div class="register-users-blocks__item register-users-blocks__item--last">
            <div class="register-users-blocks__info">
                <h3 class="register-users-blocks__title"><?php echo translate('register_block_buyer_header');?></h3>
                <ul class="register-users-blocks__list-info">
                    <li class="register-users-blocks__list-info-item">
                        <i class="ep-icon ep-icon_ok"></i><?php echo translate('register_block_buyer_header_sub_text_1');?>
                    </li>
                    <li class="register-users-blocks__list-info-item">
                        <i class="ep-icon ep-icon_ok"></i><?php echo translate('register_block_buyer_header_sub_text_2');?>
                    </li>
                    <li class="register-users-blocks__list-info-item">
                        <i class="ep-icon ep-icon_ok"></i><?php echo translate('register_block_buyer_header_sub_text_3');?>
                    </li>
                </ul>

                <a
                    class="register-users-blocks__btn btn btn-primary"
                    <?php echo addQaUniqueIdentifier("page__learn_more__register-buyer__btn"); ?>
                    href="<?php echo get_static_url('register/buyer');?>"><?php echo translate('register_button_text');?></a>
            </div>
        </div>
    </div>

    <div class="register-users-blocks__row">
        <div class="register-users-blocks__item">
            <div class="register-users-blocks__info">
                <h3 class="register-users-blocks__title"><?php echo translate('register_block_seller_header');?></h3>
                <ul class="register-users-blocks__list-info">
                    <li class="register-users-blocks__list-info-item">
                        <i class="ep-icon ep-icon_ok"></i><?php echo translate('register_block_seller_header_sub_text_1');?>
                    </li>
                    <li class="register-users-blocks__list-info-item">
                        <i class="ep-icon ep-icon_ok"></i><?php echo translate('register_block_seller_header_sub_text_2');?>
                    </li>
                    <li class="register-users-blocks__list-info-item">
                        <i class="ep-icon ep-icon_ok"></i><?php echo translate('register_block_seller_header_sub_text_3');?>
                    </li>
                </ul>

                <a
                    class="register-users-blocks__btn btn btn-primary"
                    <?php echo addQaUniqueIdentifier("page__learn_more__register-seller__btn"); ?>
                    href="<?php echo get_static_url('register/seller');?>"><?php echo translate('register_button_text');?></a>
            </div>
        </div>

        <div class="register-users-blocks__item register-users-blocks__image">
            <img
                class="image js-lazy"
                width="720"
                height="400"
                data-src="<?php echo __IMG_URL . 'public/img/register/man-performing-check.jpg';?>"
                src="<?php echo getLazyImage(720, 400); ?>"
                alt="<?php echo translate('register_block_seller_header_img', null, true);?>"
            >
        </div>
    </div>

    <div class="register-users-blocks__row">
        <div class="register-users-blocks__item register-users-blocks__image">
            <img
                class="image js-lazy"
                width="720"
                height="400"
                data-src="<?php echo __IMG_URL . 'public/img/register/building-site-worker.jpg';?>"
                src="<?php echo getLazyImage(720, 400); ?>"
                alt="<?php echo translate('register_block_manufacturer_header_img', null, true);?>"
            >
        </div>

        <div class="register-users-blocks__item register-users-blocks__item--last">
            <div class="register-users-blocks__info">
                <h3 class="register-users-blocks__title"><?php echo translate('register_block_manufacturer_header');?></h3>
                <ul class="register-users-blocks__list-info">
                    <li class="register-users-blocks__list-info-item">
                        <i class="ep-icon ep-icon_ok"></i><?php echo translate('register_block_manufacturer_header_sub_text_1');?>
                    </li>
                    <li class="register-users-blocks__list-info-item">
                        <i class="ep-icon ep-icon_ok"></i><?php echo translate('register_block_manufacturer_header_sub_text_2');?>
                    </li>
                    <li class="register-users-blocks__list-info-item">
                        <i class="ep-icon ep-icon_ok"></i><?php echo translate('register_block_manufacturer_header_sub_text_3');?>
                    </li>
                </ul>

                <a
                    class="register-users-blocks__btn btn btn-primary"
                    <?php echo addQaUniqueIdentifier("page__learn_more__register-manufacturer__btn"); ?>
                    href="<?php echo get_static_url('register/manufacturer');?>"><?php echo translate('register_button_text');?></a>
            </div>
        </div>
    </div>

    <div class="register-users-blocks__row">
        <div class="register-users-blocks__item">
            <div class="register-users-blocks__info">
                <h3 class="register-users-blocks__title"><?php echo translate('register_block_shipper_header');?></h3>
                <ul class="register-users-blocks__list-info">
                    <li class="register-users-blocks__list-info-item">
                        <i class="ep-icon ep-icon_ok"></i><?php echo translate('register_block_shipper_header_sub_text_1');?>
                    </li>
                    <li class="register-users-blocks__list-info-item">
                        <i class="ep-icon ep-icon_ok"></i><?php echo translate('register_block_shipper_header_sub_text_2');?>
                    </li>
                    <li class="register-users-blocks__list-info-item">
                        <i class="ep-icon ep-icon_ok"></i><?php echo translate('register_block_shipper_header_sub_text_3');?>
                    </li>
                </ul>
                <a
                    class="register-users-blocks__btn btn btn-primary"
                    <?php echo addQaUniqueIdentifier("page__learn_more__register-freight-forwarder__btn"); ?>
                    href="<?php echo __SHIPPER_URL . 'register/ff'; ?>">
                    <?php echo translate('register_button_text');?>
                </a>
            </div>
        </div>

        <div class="register-users-blocks__item register-users-blocks__image">
            <img
                class="image js-lazy"
                width="720"
                height="400"
                data-src="<?php echo __IMG_URL;?>public/img/register/containers-and-trucks.jpg"
                src="<?php echo getLazyImage(720, 400); ?>"
                alt="<?php echo translate('register_block_shipper_header_img');?>"
            >
        </div>
    </div>
</div>

<?php if($has_refferal ?? false){?>
    <script>
        history.pushState(null, '', __site_url + 'register');
    </script>
<?php }?>
