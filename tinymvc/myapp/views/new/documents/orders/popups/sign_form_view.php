<div class="js-modal-flex wr-modal-flex inputs-40" id="order-documents-sign-document-envelope--form-container">
    <form
        id="order-documents-sign-document-envelope--form"
        class="modal-flex__form validateModal"
        data-callback="documentsOrdersSignFormCallBack"
        data-js-action="documents:sign-envelope"
    >
		<div class="modal-flex__content">
            <div class="container-fluid-modal">
                <div class="row">
                    <div class="col-12">
                        <?php widgetEpdocsFileUploader(
                            'order-documents-sign-document-envelope--form--files',
                            'files[]',
                            'document',
                            $download ?? [],
                            $files ?? [],
                            $locales ?? [],
                            null,
                            false,
                            true,
                            true,
                            true,
                            false,
                            true
                        ); ?>
                    </div>
                </div>
            </div>
		</div>

		<div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" type="submit">
                    <?php echo translate('order_documents_dashboard_decline_popup_sign_button_text', null, true)?>
                </button>
            </div>
		</div>
	</form>
</div>

<?php
    echo dispatchDynamicFragmentInCompatMode(
        'documents:sign-envelope',
        asset('public/plug/js/documents/orders/sign.js', 'legacy'),
        sprintf(
            "function () { SignEnvelopeModule.default(%s, %s, \"%s\"); } ",
            (int) $envelope['id'],
            json_encode(
                $selectors = [
                    'container' => '#order-documents-sign-document-envelope--form-container',
                ]
            ),
            $url
        ),
        [(int) $envelope['id'], $selectors, $url],
    );
?>
