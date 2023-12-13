<div class="wr-modal-flex wr-modal-flex--relative inputs-40" id="order-documents-add-or-edit-document-envelope--form-container">
    <form
        id="order-documents-add-or-edit-document-envelope--form"
        class="modal-flex__form validateModal"
        data-callback="documentsOrdersProcessFormCallBack"
        data-js-action="documents:process-envelope"
    >
        <div class="modal-flex__content">
            <div class="container-fluid-modal">
                <div class="row">
                    <div class="col-12">
                        <?php widgetEpdocsFileUploader(
                            'order-documents-add-or-edit-document-envelope--form--files',
                            'files[]',
                            'document',
                            $download ?? [],
                            $files ?? [],
                            $locales['files'] ?? [],
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

            <?php views()->display('/new/documents/orders/partials/envelope_display_info_view', array(
                'prefix'   => 'order-documents-add-or-edit-document-envelope',
                'envelope' => $envelope ?? null,
            )); ?>

            <div class="form-group">
                <label class="input-label input-label--required">
                    <span class="input-label__text"><?php echo translate('order_documents_dashboard_edit_popup_recipients_label', null, true); ?></span>
                    <a
                        class="ep-icon ep-icon_info lh-16 info-dialog"
                        title="<?php echo translate('order_documents_dashboard_edit_popup_recipients_details_title', null, true); ?>"
                        data-title="<?php echo translate('order_documents_dashboard_edit_popup_recipients_details_dialog_title', [
                            "[[HIGHLIGHT]]"  => "<span class=\"txt-gray\">",
                            "[[/HIGHLIGHT]]" => "</span>",
                        ], true); ?>"
                        data-message="<?php echo translate('order_documents_dashboard_edit_popup_recipients_details_dialog_message', null, true); ?>">
                    </a>
                </label>

                <?php views()->display('/new/documents/orders/partials/recipients_list_view', [
                    'assignees'  => $assignees ?? [],
                    'recipients' => $recipients ?? []
                ]); ?>
            </div>

            <div class="form-group">
                <label class="input-label input-label--required">
                    <span class="input-label__text"><?php echo translate('order_documents_dashboard_edit_popup_signing_mechanism_label', null, true); ?></span>
                    <a
                        class="ep-icon ep-icon_info lh-16 info-dialog"
                        title="<?php echo translate('order_documents_dashboard_edit_popup_signing_mechanism_details_title', null, true); ?>"
                        data-title="<?php echo translate('order_documents_dashboard_edit_popup_signing_mechanism_details_dialog_title', [
                            "[[HIGHLIGHT]]"  => "<span class=\"txt-gray\">",
                            "[[/HIGHLIGHT]]" => "</span>",
                        ], true); ?>"
                        data-message="<?php echo translate('order_documents_dashboard_edit_popup_signing_mechanism_details_dialog_message', null, true); ?>">
                    </a>
                </label>

                <div class="checkbox-list">
                    <div class="checkbox-list__item">
                        <label class="checkbox-list__label custom-radio">
                            <input
                                type="radio"
                                name="signing_type"
                                class="js-signing-mechanism-variants"
                                value="<?php echo cleanOutput(\App\Envelope\SigningMecahisms::NATIVE); ?>"
                                <?php echo !empty($signingMechanism) && \App\Envelope\SigningMecahisms::NATIVE === $signingMechanism ? 'checked' : null; ?>
                            >
                            <span class="checkbox-list__txt custom-radio__text">
                                <?php echo translate('order_documents_dashboard_edit_popup_signing_mechanism_option_native', null, true); ?>
                            </span>
                        </label>
                    </div>

                    <div class="checkbox-list__item">
                        <label class="checkbox-list__label custom-radio">
                            <input
                                type="radio"
                                name="signing_type"
                                class="js-signing-mechanism-variants"
                                value="<?php echo cleanOutput(\App\Envelope\SigningMecahisms::DOCUSIGN); ?>"
                                <?php echo !empty($signingMechanism) && \App\Envelope\SigningMecahisms::DOCUSIGN === $signingMechanism ? 'checked' : null; ?>
                            >
                            <span class="checkbox-list__txt custom-radio__text">
                            <?php echo translate('order_documents_dashboard_edit_popup_signing_mechanism_option_docusign', null, true); ?>
                            </span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" type="submit">
                    <?php echo translate($locales['form']['button'] ?? '', null, true) ?: $locales['form']['button'] ?? null; ?>
                </button>
            </div>
        </div>
    </form>
</div>

<?php
    echo dispatchDynamicFragmentInCompatMode(
        'documents:process-envelope',
        asset('public/plug/js/documents/orders/process.js', 'legacy'),
        sprintf(
            "function () { ProcessEnvelopeModule.default(%s, %s, %s, %s, %s, %s, %s, \"%s\"); } ",
            ((int) $envelope['id'] ?? null) ?: 'null',
            (int) $order['id'],
            json_encode(
                $selectors = [
                    'datepicker'          => ".js-recipient-due-date",
                    'container'           => '#order-documents-add-or-edit-document-envelope--form-container',
                    'typesList'           => '#order-documents-add-or-edit-document-envelope--form .js-recipients-types',
                    'description'         => '#order-documents-add-or-edit-document-envelope--form .js-description',
                    'assigneesList'       => '#order-documents-add-or-edit-document-envelope--form .js-recipients-assignees',
                    'expiresAt'           => '#order-documents-add-or-edit-document-envelope--form .js-recipients-due-date',
                    'assigneesButton'     => '#order-documents-add-or-edit-document-envelope--form .js-recipients-add-assignee',
                    'recipientsContainer' => '#order-documents-add-or-edit-document-envelope--form .js-recipients-container',
                    'signingTypeVariants' => '#order-documents-add-or-edit-document-envelope--form .js-signing-mechanism-variants',
                ]
            ),
            json_encode(
                $recipientList = array_map(fn(array $recipient) => arrayCamelizeAssocKeys($recipient), $recipients ?? [])
            ),
            $maxRecipients ?? 'null',
            $maxDueDays,
            $defaultDueDateInterval,
            $url
        ),
        [
            ((int) $envelope['id'] ?? null) ?: null,
            (int) $order['id'],
            $selectors,
            $recipientList,
            $maxRecipients,
            $defaultDueDateInterval,
            $maxDueDays,
            $url
        ],
    );
?>
