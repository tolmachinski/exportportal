<div class="wr-modal-flex inputs-40" id="order-documents-edit-document-envelope-info--form-container">
    <form
        id="order-documents-edit-document-envelope-info--form"
        class="modal-flex__form validateModal"
        data-callback="documentsOrdersUpdateInfoFormCallBack"
        data-js-action="documents:update-envelope-info"
    >

        <div class="modal-flex__content">
            <?php views()->display('/new/documents/orders/partials/envelope_display_info_view', array(
                'prefix'   => 'order-documents-edit-document-envelope-info',
                'envelope' => $envelope ?? null,
            )); ?>
        </div>

        <div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" type="submit">
                    <?php echo translate('order_documents_process_form_edit_button_text', null, true); ?>
                </button>
            </div>
        </div>
    </form>
</div>

<?php
    echo dispatchDynamicFragmentInCompatMode(
        'documents:update-envelope-info',
        asset('public/plug/js/documents/orders/update-info.js', 'legacy'),
        sprintf(
            "function () { UpdateEnvelopeInfoModule.default(%s, %s, \"%s\"); } ",
            (int) $envelope['id'],
            json_encode(
                $selectors = [
                    'container' => '#order-documents-edit-document-envelope-info--form-container',
                ]
            ),
            $url
        ),
        [(int) $envelope['id'], $selectors, $url],
    );
?>
