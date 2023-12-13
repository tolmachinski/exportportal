<div class="modal-flex__btns">
    <div class="modal-flex__btns-left">
        <button class="js-modal-select-featured-items-cancel btn btn-dark" type="button"><?php echo translate('featured_items_modal_btn_cancel');?></button>
    </div>
    <div class="modal-flex__btns-right">
        <button class="js-modal-select-featured-items-submit btn btn-primary" type="button"><?php echo translate('featured_items_modal_btn_submit');?></button>
    </div>
</div>

<?php
    echo dispatchDynamicFragment(
        "popup:free_featured_items",
        [
            [
                'maxItems'      => config('max_free_featured_items_select'),
                'saveUrl'       => getUrlForGroup('featured/ajax_featured_operation/save_products'),
                'searchUrl'     => getUrlForGroup('featured/ajax_featured_operation/find_products'),
                'selectors'     => [
                    'form'                      => '#js-modal-select-featured-items',
                    'searchField'               => '#js-modal-select-featured-items input.js-search-product',
                    'productsList'              => '#js-modal-select-featured-items .js-products-list',
                    'selectedProducts'          => '#js-modal-select-featured-items .js-selected-products',
                    'selectedProductsWrapper'   => '#js-modal-select-featured-items .js-selected-products-b',
                    'deleteProduct'             => '.js-delete-product',
                    'noProductsRow'             => '.js-no-product',
                    'productRow'                => '.js-product',
                    'btnCancel'                 => '.js-modal-select-featured-items-cancel',
                    'btnSubmit'                 => '.js-modal-select-featured-items-submit',
                ]
            ]
        ],
        true
    );
?>
