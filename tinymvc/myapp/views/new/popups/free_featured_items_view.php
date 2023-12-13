<form id="js-modal-select-featured-items">
    <div class="info-alert-b mb-20"><i class="ep-icon ep-icon_info-stroke"></i> <span>
        <strong class="display-b"><?php echo translate('featured_items_modal_info_title');?></strong>
        <?php echo translate('featured_items_modal_info_desc', ['[DAYS]' => config('feature_items_free_period')]);?>
    </span></div>

    <div class="input-search-products input-search-products--featured">
        <input class="js-search-product" type="text" placeholder="<?php echo translate('featured_items_modal_placeholder');?>">

        <div class="js-products-list input-search-products__results"></div>
    </div>

    <div class="js-selected-products-b feature-selected-items-b visible-h">
        <h3 class="feature-selected-items-b__ttl"><?php echo translate('featured_items_modal_list_title');?></h3>
        <div class="js-selected-products input-search-products-selected input-search-products-selected--featured"></div>
    </div>
</form>

<script type="text/template" id="js-modal-select-featured-items-product">
    <div class="js-product input-search-products-selected__item" style="display: none;" data-item="{{index}}" data-idproduct="{{id}}">
        <a class="input-search-products-selected__link flex-card" href="{{url}}" target="_blank">
            <input type="hidden" name="items[]" value="{{id}}">
            <div class="input-search-products-selected__img image-card3 flex-card__fixed">
                <span class="link">
                    <img class="image" src="{{image}}" alt="{{title}}">
                </span>
            </div>
            <div class="input-search-products-selected__name flex-card__float">
                <div class="grid-text">
                    <div class="grid-text__item">
                        {{title}}
                    </div>
                </div>
            </div>
        </a>
        <div>
            <a
                class="btn btn-light js-delete-product"
                title="<?php echo translate('featured_items_modal_remove_item_title');?>"
                data-message="<?php echo translate('featured_items_modal_remove_item_message');?>"
            >
                <i class="ep-icon ep-icon_trash-stroke"></i>
            </a>
        </div>
    </div>
</script>
