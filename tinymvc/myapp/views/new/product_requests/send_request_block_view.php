<section class="product-requests">
    <div>
        <h2 class="product-requests__title">
            <?php echo translate('product_requests_couldnt_find_text', null, true); ?>
        </h2>
        <p class="product-requests__subtitle">
            <?php echo translate('product_requests_fill_out_form', null, true); ?>
        </p>
    </div>

    <button
        class="product-requests__btn btn btn-new16 btn-primary fancybox.ajax fancyboxValidateModal"
        data-title="<?php echo translate('product_requests_fancybox_title', null, true); ?>"
        data-mw="470"
        data-fancybox-href="<?php echo getUrlForGroup("/product_requests/popup_forms/send"); ?>"
        <?php echo addQaUniqueIdentifier("category__request-products")?>
    >
        <?php echo translate('product_requests_btn_text', null, true); ?>
    </button>
</section>
