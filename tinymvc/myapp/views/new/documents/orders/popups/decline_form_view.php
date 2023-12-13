<div class="js-modal-flex wr-modal-flex inputs-40" id="order-documents-decline-document-envelope--form-container">
    <form
        class="modal-flex__form validateModal"
        id="order-documents-decline-document-envelope--form"
        data-callback="documentsOrdersDeclineFormCallBack"
        data-js-action="documents:decline-envelope"
    >
        <div class="form-group">
            <label class="input-label input-label--required">
                <?php echo translate('order_documents_dashboard_decline_popup_reason_label', null, true); ?>
            </label>
            <textarea name="reason"
                data-max="500"
                id="order-documents-decline-document-envelope--formfield--reason"
                class="validate[required,maxSize[500]] textcounter-document_comment"
                placeholder="<?php echo translate('order_documents_dashboard_decline_popup_reason_placeholder', null, true); ?>"></textarea>
        </div>

		<div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" type="submit"><?php echo translate('order_documents_dashboard_decline_popup_submit_button_text', null, true); ?></button>
            </div>
		</div>
	</form>
</div>

<?php
    echo dispatchDynamicFragmentInCompatMode(
        'documents:decline-envelope',
        asset('public/plug/js/documents/orders/decline.js', 'legacy'),
        sprintf(
            "function () { DeclineEnvelopeModule.default(%s, %s, \"%s\"); } ",
            (int) $envelope['id'],
            json_encode(
                $selectors = [
                    'container' => '#order-documents-decline-document-envelope--form-container',
                    'reason'    => '#order-documents-decline-document-envelope--formfield--reason',
                ]
            ),
            $url
        ),
        [(int) $envelope['id'], $selectors, $url],
    );
?>
