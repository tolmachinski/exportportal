<?php
    use App\Documents\Versioning\ExpiringVersionInterface;
    use App\Documents\Versioning\RejectedVersionInterface;
    use App\Documents\Versioning\VersionInterface;
    use Doctrine\Common\Collections\Collection;
?>

<?php views()->display('new/download_script'); ?>
<table class="main-data-table main-data-table--head-cursor mt-15 mb-15" id="verification--table">
    <thead>
        <tr>
            <th><?php echo translate('accreditation_document_title'); ?></th>
            <th class="w-200 tac vam"><?php echo translate('accreditation_verification_status', null, true);?></th>
            <th class="w-170 tac vam"></th>
        </tr>
    </thead>
    <tbody>
        <?php /** @var ColLection|array[] $documents */ ?>
        <?php foreach($documents as $document) {?>
            <?php /** @var VersionInterface|RejectedVersionInterface|ExpiringVersionInterface $version */ ?>
            <?php $version = arrayGet($document, 'latest_version');?>
            <tr data-document="<?php echo $document['id_document']; ?>" <?php echo addQaUniqueIdentifier("verification__document-type-id-" . $document['id_type'])?>>
                <td class="vam">
                    <?php echo cleanOutput($document['title']) . (!empty($document['subtitle']) ? ' (' . cleanOutput($document['subtitle']) . ')' : ''); ?>
                    <?php if (!empty($document['country_title'])) { ?>
                        (<?php echo cleanOutput($document['country_title']); ?>)
                    <?php } ?>
                    <div class="txt-gray">
                        <?php echo cleanOutput($document['description']); ?>
                    </div>
                </td>

                <td class="tac vam js-status">
                    <?php if (null === $version) { ?>
                        <span class="txt-red doc_status_description" data-toggle="popoverview" data-content="<?php echo translate('accreditation_user_have_to_upload_document', null, true); ?>">
                            <span class="display-ib">
                                <?php echo translate("accreditation_documents_status_init", null, true); ?>
                            </span>
                        </span>
                    <?php } else if (
                        !$document['metadata']['is_version_rejected']
                        && (
                            ($document['metadata']['is_expiring_soon'] && !empty($status = arrayGet($statuses, 'expires')))
                            || ($document['metadata']['is_expired'] && !empty($status = arrayGet($statuses, 'expired')))
                        )
                    ) { ?>
                        <span class="<?php echo arrayGet($status, 'color'); ?> doc_status_description" data-toggle="popoverview" data-content="<?php echo cleanOutput(arrayGet($status, 'description', '—')); ?>">
                            <span class="display-ib">
                                <?php if (is_callable($status_title = arrayGet($status, 'title', '—'))) { ?>
                                    <?php echo cleanOutput($status_title($version->getExpirationDate())); ?>
                                <?php } else { ?>
                                    <?php echo cleanOutput($status_title); ?>
                                <?php } ?>
                            </span>
                        </span>
                    <?php } else if (!empty($status = arrayGet($statuses, get_class($version)))) { ?>
                        <span class="<?php echo arrayGet($status, 'color'); ?> doc_status_description" data-toggle="popoverview" data-content="<?php echo cleanOutput(arrayGet($status, 'description', '—')); ?>">
                            <span class="display-ib">
                                <?php echo cleanOutput(arrayGet($status, 'title', '—')); ?>
                            </span>
                        </span>
                        <?php if (
                            $document['metadata']['is_version_rejected']
                            && !empty($reason_text = arrayGet($notifications, "{$version->getReasonCode()}.message_text", $version->getReason()))
                            && !empty($reason_title = arrayGet($notifications, "{$version->getReasonCode()}.message_title", $version->getReasonTitle()))
                        ) { ?>
                            <a class="info-dialog"
                                data-message="<?php echo cleanOutput($reason_text); ?>"
                                data-title="<?php echo cleanOutput($reason_title); ?>"
                                title="<?php echo translate("verification_page_reason_info_label_title", null, true); ?>">
                                <i class="ep-icon ep-icon_info fs-16"></i>
                            </a>
                        <?php } ?>
                    <?php } else { ?>
                        &mdash;
                    <?php } ?>
                </td>

                <td class="tac vam">
                    <div role="group" class="btn-group d-flex js-button-group">
                        <?php if ($show_upload_buttons = ($document['metadata']['is_uploadable'] || $document['metadata']['is_reuploadable'])) { ?>
                            <?php if (!$document['metadata']['is_reuploadable']) { ?>
                                <a
                                    <?php echo addQaUniqueIdentifier("verification__upload-document-button")?>
                                    class="btn btn-primary mnw-150 fancybox.ajax fancyboxValidateModal js-button-upload"
                                    data-fancybox-href="<?php echo getUrlForGroup("personal_documents/popup_forms/upload/{$document['id_document']}"); ?>"
                                    data-title="<?php echo translate('accreditation_upload_the_document', null, true); ?>"
                                    title="<?php echo translate('accreditation_upload_the_document', null, true); ?>">
                                    <?php echo translate('accreditation_upload_file'); ?>
                                </a>
                            <?php } ?>

                            <a
                                <?php echo addQaUniqueIdentifier("verification__reupload-document-button")?>
                                class="btn btn-primary mnw-150 fancybox.ajax fancyboxValidateModal js-button-re-upload"
                                <?php if (!$document['metadata']['is_reuploadable']) { ?>style="display: none;"<?php } ?>
                                data-fancybox-href="<?php echo getUrlForGroup("personal_documents/popup_forms/replace/{$document['id_document']}"); ?>"
                                data-title="<?php echo translate('accreditation_re_upload_the_document', null, true); ?>"
                                title="<?php echo translate('accreditation_re_upload_the_document', null, true); ?>">
                                <?php echo translate('accreditation_re_upload_file'); ?>
                            </a>
                        <?php } ?>

                        <span class="lh-42 mnw-150 tac bg-gray-lighter js-label-upload"
                            <?php if ($show_upload_buttons) { ?>style="display: none;"<?php } ?>>
                            <?php echo translate('accreditation_uploaded'); ?>
                        </span>

                        <a
                            <?php echo addQaUniqueIdentifier("verification__download-document-button")?>
                            class="btn btn-dark js-button-download call-function bdl-1-white"
                            data-callback="downloadDocument"
                            data-document="<?php echo $document['id_document']; ?>"
                            data-version="<?php echo $document['latest_version_index']; ?>"
                            title="<?php echo translate('accreditation_download_the_document', null, true); ?>">
                            <i class="ep-icon ep-icon_download"></i>
                        </a>

                        <?php if($document['metadata']['is_uploaded'] && 1 == (int) $document['type']['document_is_multiple']){?>
                            <a class="btn btn-secondary bdl-1-white fancybox.ajax fancyboxValidateModal js-button-upload-multiple"
                                data-fancybox-href="<?php echo getUrlForGroup("personal_documents/popup_forms/upload/{$document['id_document']}"); ?>"
                                data-title="<?php echo translate('general_button_upload_another_full_text', null, true); ?>"
                                title="<?php echo translate('general_button_upload_another_full_text', null, true); ?>">
                                <i class="ep-icon ep-icon_plus-stroke"></i>
                            </a>
                        <?php }?>
                    </div>

                    <?php /** @var Collection|array[] $other */ ?>
                    <?php if (
                        $document['metadata']['is_uploadable']
                        && $other_documents
                        && !empty($other = $other_documents->get($document['id_type'])) && $other->count() > 0
                    ) { ?>
                        <div role="group" class="btn-group d-flex mt-1 js-button-group-others">
                            <a
                                <?php echo addQaUniqueIdentifier("verification__use-existing-button")?>
                                class="btn btn-dark mnw-150 dropdown-toggle text-nowrap"
                                data-toggle="dropdown"
                                aria-haspopup="true"
                                aria-expanded="false">
                                <?php echo translate('personal_documents_list_other_accounts_documents_dropdown_button_text', null, true); ?>
                            </a>
                            <span
                                class="btn btn-dark bdl-1-white info-dialog ml-0"
                                title="<?php echo translate('personal_documents_list_other_accounts_documents_info_title', null, true); ?>"
                                data-title="<?php echo translate('personal_documents_list_other_accounts_documents_info_dialog_title', null, true); ?>"
                                data-message="<?php echo translate('personal_documents_list_other_accounts_documents_info_dialog_text'); ?>">
                                <i class="ep-icon ep-icon_info fs-16"></i>
                            </span>

                            <div class="dropdown-menu">
                                <?php foreach ($other as $submitted_document) { ?>
                                    <span class="dropdown-item call-function js-use-document cur-pointer"
                                        data-callback="useDocument"
                                        data-document="<?php echo cleanOutput($submitted_document['id_document']); ?>">
                                        <span class="txt">
                                            <?php echo translate(
                                                'personal_documents_list_other_accounts_documents_dropdown_entry_text',
                                                array('[[ACCOUNT]]' => $groups[$submitted_document['owner']['user_group']] ?? translate('personal_documents_list_other_accounts_documents_unknown_account_text')),
                                                true
                                            ); ?>
                                        </span>
                                    </span>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>

<script type="text/template" id="verification--template--uploaded-info">
    <span class="lh-30"><?php echo translate('accreditation_uploaded'); ?></span>
</script>
<script type="text/template" id="verification--template--uploaded-status">
    <span class="{{color}} doc_status_description" data-toggle="popoverview" data-content="{{description}}">
        <span class="display-ib">
            {{title}}
        </span>
    </span>
</script>
<script type="text/template" id="verification--template--not-uploaded-status">
    <span class="txt-red doc_status_description" data-toggle="popoverview" data-content="<?php echo translate('accreditation_user_have_to_upload_document', null, true); ?>">
        <span class="display-ib">
            <?php echo translate('accreditation_not_uploaded'); ?>
        </span>
    </span>
</script>
<script>
    $(function() {
        var statuses = <?php echo json_encode($statuses ? arrayByKey($statuses, 'type') : new \stdClass()); ?>;
        var copyUrl = __group_site_url + 'personal_documents/ajax_operation/copy_version';
        var helpButton = $('#js-action-show-help');
        var verificationTable = $('#verification--table');
        var buttonUploadedTemplate = $('#verification--template--uploaded-info').text() || null;
        var statusUploadedTemplate = $('#verification--template--uploaded-status').text() || null;
        var statusNotUploadedTemplate = $('#verification--template--not-uploaded-status').text() || null;
        var checkForPopups = Boolean(~~parseInt('<?php echo (int) isset($upgrade_request) && $upgrade_request['status'] == 'new' && $upgrade_request['type'] == 'upgrade'; ?>', 0));

        var renewPopovers = function () {
            $('[data-toggle="popoverview"]').popover({trigger: 'hover', placement: 'top'});
        };

        var showHelpIfPossible = function () {
            if (__disable_popup_system) {
                return;
            }

            if (parseInt(getCookie('ep_open_what_next_verification')) == 2) {
                removeCookie('ep_open_what_next_verification');
                helpButton.trigger('click');
            }else{
                var uploadButtons = verificationTable.find('.js-button-upload').filter(':visible');

                if (
                    !uploadButtons.length
                    && (parseInt(getCookie('ep_open_what_next_verification')) != 1)
                ) {
                    setTimeout(function() {
                        helpButton.trigger('click');
                    }, 300);
                }
            }
        };

        var onLatestVersionDownload = function (button) {
            var url = __group_site_url + 'personal_documents/ajax_operation/download_document';
            var version = button.data('version');
            var documentId = button.data('document') || null;
            var onRequestStart = function() {
                button.prop('disabled', true).addClass('disabled');
            };
            var onRequestEnd = function () {
                button.prop('disabled', false).removeClass('disabled');
            };
			var onRequestSuccess = function (data) {
				if ('success' === data.mess_type) {
					if (
						data.token
						&& typeof data.token === 'object'
						&& data.token.constructor === Object
						&& data.token.url
					) {
						downloadFile(data.token.url + (data.token.filename ? "?" + $.param({ name: data.token.filename }) : ''), data.token.filename || data.token.name);
					}
				} else {
					systemMessages(data.message, data.mess_type);
				}
			};

			if (null === documentId) {
				return;
			}
            onRequestStart();

			return $.post(url, { document: documentId }, null, 'json')
				.done(onRequestSuccess)
				.fail(onRequestError)
				.always(onRequestEnd);
        };

        var updateRow = function (row, data, status) {
            var statusCell = row.find('.js-status');
            var uploadLabel = row.find('.js-label-upload');
            var uploadButton = row.find('.js-button-upload');
            var reUploadButton = row.find('.js-button-re-upload');
            var downloadButton = row.find('.js-button-download');
            var othersDocuments = row.find('.js-button-group-others');

            if(data.isMultiple){
                location.reload();
            }

            //region Change upload button
            othersDocuments.remove();
            downloadButton.data('version', parseInt(downloadButton.data('version') || 0, 10) + 1);
            uploadButton.remove();
            if (!data.isReUplodable) {
                reUploadButton.remove();
                uploadLabel.show();
            } else if (reUploadButton.length) {
                reUploadButton.show();
            } else {
                uploadLabel.show();
            }
            //endregion Change upload button

            //region Renew uploaded document status
            if (statusCell.length > 0)  {
                statusCell.html(renderTemplate(statusUploadedTemplate, {
                    color: htmlEscape(status.color),
                    title: htmlEscape(status.title),
                    description: htmlEscape(status.description),
                }));
            }
            //endregion Renew uploaded document status

            renewPopovers();
        };

        var onUseOtherDocument = function (table, button) {
            var documentRow = button.closest('tr');
            var sourceDocumentId = button.data('document') || null;
            var targetDocumentId = documentRow.data('document') || null;
            var onRequestStart = function() {
                button.closest('.dropdown-menu').find('.dropdown-item').prop('disabled', true).addClass('disabled');
            };
            var onRequestEnd = function () {
                button.closest('.dropdown-menu').find('.dropdown-item').prop('disabled', false).removeClass('disabled');
            };
            if (!sourceDocumentId || !targetDocumentId) {
                return Promise.resolve();
            }
            onRequestStart();

            return postRequest(copyUrl, { source: sourceDocumentId, target: targetDocumentId })
                .then(function (response) { updateRow(documentRow, response || {}, statuses[response.status || 'pending'] || {}); })
                .catch(onRequestError)
                .then(function () { onRequestEnd(); });
        };

        var onReUploadComplete = function (table, document, data) {
            var documentRow = table.find('tr[data-document="' + document + '"]');
            var uploadLabel = documentRow.find('.js-label-upload');
            var reUploadButton = documentRow.find('.js-button-re-upload');
            var downloadButton = documentRow.find('.js-button-download');
            if (!data.isReUplodable) {
                reUploadButton.remove();
                uploadLabel.show();
            }
        };

        var onUploadComplete = function (table, document, data) {
            //region Update document upload information
            var documentRow = table.find('tr[data-document="' + document + '"]');
            if (documentRow.length > 0)  {
                updateRow(documentRow, data || {}, statuses.pending || {});
            } else {
                renewPopovers();
            }

            showHelpIfPossible();
            //endregion Update document upload information
        };

        showHelpIfPossible();
        mobileDataTable(verificationTable);
        renewPopovers();
        mix(globalThis, {
            useDocument: onUseOtherDocument.bind(null, verificationTable),
            downloadDocument: onLatestVersionDownload,
            callbackUploadDocument: onUploadComplete.bind(null, verificationTable),
            callbackReUploadDocument: onReUploadComplete.bind(null, verificationTable),
        });

        // NEW POPUP CALL feedback_certification
        if (checkForPopups && !__disable_popup_system) {
            setTimeout(
                function() {
                    dispatchCustomEvent("popup:call-popup", globalThis, {detail: { name: "feedback_certification" }});
                },
                2000
            );
        }
    });
</script>
