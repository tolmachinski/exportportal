<?php $url = $url ?? __CURRENT_URL;?>
<div class="pages-socials-list <?php echo !empty($additionalClass) ? $additionalClass : '';?>">
    <button
        class="pages-socials-list__item pages-socials-list__item--facebook call-function call-action"
        data-js-action="footer:share-popup"
        data-callback="popup_share"
        data-title="<?php echo $title;?>"
        data-url="<?php echo $url;?>"
        data-social="facebook"
        type="button"
    >
        <?php echo widgetGetSvgIcon('facebook', 16, 16);?>
    </button>

    <button
        class="pages-socials-list__item pages-socials-list__item--twitter call-function call-action"
        data-js-action="footer:share-popup"
        data-callback="popup_share"
        data-title="<?php echo $title;?>"
        data-url="<?php echo $url;?>"
        data-social="twitter"
        type="button"
    >
        <?php echo widgetGetSvgIcon('twitter', 16, 16);?>
    </button>

    <button
        class="pages-socials-list__item pages-socials-list__item--linkedin call-function call-action"
        data-js-action="footer:share-popup"
        data-callback="popup_share"
        data-title="<?php echo $title;?>"
        data-url="<?php echo $url;?>"
        data-social="linkedin"
        type="button"
    >
        <?php echo widgetGetSvgIcon('linkedin', 16, 16);?>
    </button>

    <?php if (!empty($img)) {?>
        <button
            class="pages-socials-list__item pages-socials-list__item--pinterest call-function call-action"
            data-js-action="footer:share-popup"
            data-callback="popup_share"
            data-title="<?php echo $title;?>"
            data-url="<?php echo $url;?>"
            data-social="pinterest"
            data-img="<?php echo $img?>"
            type="button"
        >
            <?php echo widgetGetSvgIcon('pinterest', 16, 16);?>
        </button>
    <?php }?>
</div>
