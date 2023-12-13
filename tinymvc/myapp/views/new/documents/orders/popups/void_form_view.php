<div class="js-modal-flex wr-modal-flex inputs-40" id="documents-void-document-envelope--form-container">
    <form
        id="documents-void-document-envelope--form"
        class="modal-flex__form validateModal"
        data-callback="documentsOrdersVoidFormCallBack"
        data-js-action="documents:decline-envelope"
    >

        <div class="form-group">
            <label class="input-label input-label--required"><?php echo translate('order_documents_dashboard_void_popup_reason_label', null, true); ?></label>
            <textarea name="reason"
                data-max="500"
                id="documents-void-document-envelope--formfield--reason"
                class="validate[required,maxSize[500]] textcounter-document_comment"
                placeholder="<?php echo translate('order_documents_dashboard_void_popup_reason_placeholder', null, true); ?>"
                ></textarea>
        </div>

		<div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" type="submit">
                    <?php echo translate('order_documents_dashboard_void_popup_submit_button_text', null, true); ?>
                </button>
            </div>
		</div>
	</form>
</div>

<?php
    echo dispatchDynamicFragmentInCompatMode(
        'documents:void-envelope',
        asset('public/plug/js/documents/orders/void.js', 'legacy'),
        sprintf(
            "function () { VoidEnvelopeModule.default(%s, %s, \"%s\"); } ",
            (int) $envelope['id'],
            json_encode(
                $selectors = [
                    'container' => '#documents-void-document-envelope--form-container',
                    'reason'    => '#documents-void-document-envelope--formfield--reason',
                ]
            ),
            $url
        ),
        [(int) $envelope['id'], $selectors, $url],
    );
?>
