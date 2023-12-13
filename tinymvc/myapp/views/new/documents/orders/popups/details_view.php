<div class="wr-modal-flex inputs-40">
	<div class="modal-flex__form">
        <div class="modal-flex__content">
            <div class="container-fluid-modal">
                <div class="row">
                    <div class="col-12">
                        <div class="minfo-sidebar-ttl mt-15 mb-15">
                            <span class="minfo-sidebar-ttl__txt">
                                <?php echo translate('order_documents_dashboard_details_popup_general_title', null, true); ?>
                            </span>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="input-label txt-gray"><?php echo translate('order_documents_dashboard_details_popup_creation_date_label', null, true); ?></label>
                        <?php echo getDateFormatIfNotEmpty($envelope['created_at_date'] ?? null, DATE_ATOM); ?>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="input-label txt-gray"><?php echo translate('order_documents_dashboard_details_popup_completion_date_label', null, true); ?></label>
                        <?php echo getDateFormatIfNotEmpty($envelope['completed_at_date'] ?? null, DATE_ATOM); ?>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="input-label txt-gray"><?php echo translate('order_documents_dashboard_details_popup_title_label', null, true); ?></label>
                        <?php echo cleanOutput($envelope['display_title'] ?? null); ?>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="input-label txt-gray"><?php echo translate('order_documents_dashboard_details_popup_type_label', null, true); ?></label>
                        <?php echo cleanOutput($envelope['display_type'] ?? null); ?>
                    </div>

                    <div class="col-12">
                        <label class="input-label txt-gray"><?php echo translate('order_documents_dashboard_details_popup_description_label', null, true); ?></label>
                        <?php echo cleanOutput($envelope['display_description'] ?? null); ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="minfo-sidebar-ttl mb-15">
                            <span class="minfo-sidebar-ttl__txt"><?php echo translate('order_documents_dashboard_details_popup_recipients_title', null, true); ?></span>
                        </div>

                        <ul class="list-group">
                            <?php foreach ($recipients ?? [] as $index => $recipient) { ?>
                                <li class="list-group-item list-info-col-group__item list-info-col-group__item--mobile-block">
                                    <div class="list-info-col-group__col list-info-col-group__col--float">
                                        <div class="list-info-col-group__name"><?php echo $index + 1; ?>. <?php echo cleanOutput($recipient['assignee_name']); ?></div>
                                        <div class="list-info-col-group__type <?php echo $recipient['assignee_group_color'];?>"><?php echo $recipient['assignee_group'];?></div>
                                    </div>
                                    <div class="list-info-col-group__col list-info-col-group__col--right">
                                        <?php if(!empty($recipient['status_badge']['text'])){?>
                                            <div><span class="badge badge-pill <?php echo $recipient['status_badge']['color'];?>"><?php echo $recipient['status_badge']['text'];?></span></div>
                                        <?php }?>
                                        <div class="list-info-col-group__date"><?php echo $recipient['recipient_type'];?> <?php echo !empty($recipient['expires_at']) ? 'due on ' . getDateFormatIfNotEmpty($recipient['expires_at'], 'm/d/Y', \App\Common\PUBLIC_DATE_FORMAT) : ''; ?></div>
                                    </div>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="minfo-sidebar-ttl mb-15">
                            <span class="minfo-sidebar-ttl__txt"><?php echo translate('order_documents_dashboard_details_popup_documents_title', null, true); ?></span>
                        </div>

                        <ul class="list-group list-info-col-group">
                            <?php foreach ($documents ?? [] as $index => list($type, $document)) { ?>
                                <li class="list-group-item list-info-col-group__item">
                                    <div class="list-info-col-group__col list-info-col-group__col--float">
                                        <div class="list-info-col-group__row">
                                            <div class="list-info-col-group__col list-info-col-group__col--limited-width">
                                                <div class="list-info-col-group__name"><?php echo $index + 1; ?>. <?php echo cleanOutput($document['file_original_name'] ?? null); ?></div>
                                                <div>
                                                    <?php if (!empty($document['status_badge']['text'])) { ?>
                                                        <span class="badge badge-pill <?php echo $document['status_badge']['color']; ?>">
                                                            <?php echo $document['status_badge']['text']; ?>
                                                        </span>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                            <div class="list-info-col-group__col">
                                                <div class="list-info-col-group__date"><?php echo getDateFormatIfNotEmpty($document['uploaded_at_date'] ?? null, DATE_ATOM); ?></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="list-info-col-group__col list-info-col-group__col--right">
                                        <a
                                            class="btn btn-light call-function call-action"
                                            title="<?php echo translate('order_documents_dashboard_details_popup_description_download_button_text', null, true); ?>"
                                            data-document="<?php echo cleanOutput($document['id']); ?>"
                                            data-callback="downloadDocumentsFromDetails"
                                            data-js-action="documents:details:download-documents">
                                            <i class="ep-icon ep-icon_download-stroke"></i>
                                        </a>
                                    </div>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
    echo dispatchDynamicFragmentInCompatMode(
        'documents:envelope-details',
        asset('public/plug/js/documents/orders/details.js', 'legacy'),
        sprintf(
            "function () { EnvelopeDetailsModule.default(%s, \"%s\"); } ",
            (int) $envelope['id'],
            $url = getUrlForGroup("/order_documents/ajax_operation/download-document")
        ),
        [(int) $envelope['id'], $url],
    );
?>
