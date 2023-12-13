<?php
    use App\Documents\Versioning\{AbstractVersion, VersionInterface, ExpiringVersionInterface, RejectedVersionInterface, AcceptedVersionInterface};
    use App\Documents\File\FileAwareInterface;
    use App\Documents\File\FileCopy;
?>
<form method="post" class="validateModal relative-b" action="<?php echo $action; ?>" id="verification-documents--form">
	<input type="hidden" name="user" value="<?php echo $user['idu']; ?>">
	<div class="wr-form-content w-850 mh-700">
        <div class="js-list-container" <?php if (empty($additional_documents)) { ?>style="display: none;"<?php } ?>>
			<p class="mt-15 mb-5">
				<strong>
                    Recovered info
				</strong>
			</p>
			<ul id="verification-documents--template--document-list" class="list-group clearfix mb-5">
                <?php if (empty($recovered_info)) { ?>
                    <div class="info-alert-b">
                        <i class="ep-icon ep-icon_remove-circle"></i> <span>No recovery data</span>
                    </div>
                <?php } else { ?>
                    <?php
                        foreach($recovered_info as $document => $status) {
                            switch ($status) {
                                case 'absent':
                                    echo '<div class="warning-alert-b mb-5"><i class="ep-icon ep-icon_remove-circle"></i> <span>' . $document . ' : Not loaded.</span></div>';
                                break;
                                case 'pending':
                                    echo '<div class="info-alert-b mb-5"><i class="ep-icon ep-icon_info-stroke"></i> <span>' . $document . ' : Not verified.</span></div>';
                                break;
                                case 'accepted':
                                    echo '<div class="success-alert-b mb-5"><i class="ep-icon ep-icon_ok-circle"></i> <span> ' . $document . ' : Verified.</span></div>';
                                break;
                                case 'rejected':
                                    echo '<div class="error-alert-b mb-5"><i class="ep-icon ep-icon_remove-circle"></i> <span>' . $document . ' : Rejected.</span></div>';
                                break;
                            }
                        }
                    ?>
                <?php } ?>
			</ul>
        </div>

        <div id="verification-documents--files-list">
			<p class="mt-5 mb-5">
				<strong>
                    Stored files
                </strong>
            </p>
            <div class="js-loader d-block"></div>
            <div class="error-alert-b js-error-container" style="display: none;">
                <i class="ep-icon ep-icon_remove-circle"></i> <span></span>
            </div>
            <div class="list-group clearfix mb-5 js-files-container" style="display: none;">
            </div>
        </div>

        <hr>

		<table class="data table-striped table-bordered w-100pr mt-15 mb-15 vam-table">
            <thead>
                <tr>
                    <th class="tal">Document title</th>
                    <th class="w-175">Expire on</th>
                    <th class="w-130">Status</th>
                    <th class="w-130">Decline reason</th>
                    <th class="w-100">Actions</th>
                </tr>
            </thead>
            <tbody class="tabMessage">
				<?php if (!empty($documents)) { ?>
					<?php foreach($documents as $document) { ?>
						<?php /** @var null|AbstractVersion|VersionInterface|ExpiringVersionInterface|RejectedVersionInterface|AcceptedVersionInterface $version */ ?>
						<?php $version = arrayGet($document, 'latest_version'); ?>

						<tr class="tr-doc" id="document_<?php echo $document['id_document']; ?>" <?php echo addQaUniqueIdentifier("verification__document-type-id-" . $document['id_type']);?>>
							<td class="vam">
								<?php echo cleanOutput($document['title']); ?>
                                <?php if (
                                    null !== $version
                                    && $version instanceof FileAwareInterface
                                    && $version->hasFile()
                                    && $version->getFile() instanceof FileCopy
                                ) { ?>
                                    <br>
                                    <span class="label label-primary">Copy</span>
                                <?php } ?>
							</td>
							<td class="tac vam td-expire-on">
								<div class="input-group js-expires"
									<?php if (!$document['is_version_expirable']) { ?>style="display: none;"<?php } ?>>
									<input type="text"
                                        <?php echo addQaUniqueIdentifier("admin-users__verification-form__expire-on-input")?>
										name="doc_expire_on"
										class="form-control date-picker tac"
										value="<?php echo getDateFormatIfNotEmpty($document['expires'], DATE_ISO8601, 'm/d/Y', null); ?>"
										placeholder="&mdash;"
										readonly>
									<div class="input-group-btn">
										<span class="btn btn-success btn-sm call-function"
                                            <?php echo addQaUniqueIdentifier("admin-users__verification-form__expire-on-ok-button")?>
											data-user="<?php echo $user['idu']; ?>"
											data-callback="changeExpirationDate"
											data-document="<?php echo $document['id_document']; ?>">
											<i class="ep-icon ep-icon_ok mb-0 mr-0 fs-10"></i>
										</span>

										<span class="btn btn-danger btn-sm call-function"
                                            <?php echo addQaUniqueIdentifier("admin-users__verification-form__expire-on-remove-button")?>
											data-user="<?php echo $user['idu']; ?>"
											data-callback="removeExpirationDate"
											data-document="<?php echo $document['id_document']; ?>">
											<i class="ep-icon ep-icon_remove mb-0 mr-0 fs-10"></i>
										</span>
									</div>
								</div>
								<div class="js-no-expiration" <?php if ($document['is_version_expirable']) { ?>style="display: none;"<?php } ?>>
									&mdash;
								</div>
							</td>
							<td class="tac vam td-status" <?php echo addQaUniqueIdentifier("admin-users__verification-form__status-label")?>>
								<?php if (null === $version) { ?>
									<span class="txt-red js-status">
										<i class="ep-icon ep-icon_minus-circle mb-0"></i>
										<span class="display-ib">
											Not uploaded
										</span>
									</span>
								<?php } else if (
									!$document['is_version_rejected']
									&& (
										($document['is_expiring_soon'] && !empty($status = arrayGet($statuses, 'expires')))
										|| ($document['is_expired'] && !empty($status = arrayGet($statuses, 'expired')))
									)
								) { ?>
									<span class="<?php echo str_replace('2', '', arrayGet($status, 'color')); ?> js-status">
										<i class="ep-icon <?php echo arrayGet($status, 'icon'); ?> mb-0"></i>
										<span class="display-ib">
											<?php if (is_callable($status_title = arrayGet($status, 'title', '—'))) { ?>
												<?php echo null !== $version ? cleanOutput($status_title($version->getExpirationDate())) : ''; ?>
											<?php } else { ?>
												<?php echo cleanOutput($status_title); ?>
											<?php } ?>
										</span>
									</span>
								<?php } else if (!empty($status = arrayGet($statuses, get_class($version)))) { ?>
									<span class="<?php echo arrayGet($status, 'color'); ?> js-status">
										<i class="ep-icon <?php echo arrayGet($status, 'icon'); ?> mb-0"></i>
										<span class="display-ib">
											<?php echo cleanOutput(arrayGet($status, 'title', '—')); ?>
										</span>
									</span>
								<?php } else { ?>
									&mdash;
								<?php } ?>
							</td>
							<td class="tac vam td-reason">
								<?php if ($document['is_version_rejected'] && $version->hasReasonCode()) { ?>
									<?php echo arrayGet($notifications, "{$version->getReasonCode()}.title", '&mdash'); ?>
								<?php } else { ?>
									&mdash;
								<?php } ?>
							</td>
							<td class="vam tar td-actions">
								<div class="document-declined-select js-rejection-list mb-5" style="display: none;">
									<label class="display-b tal">Select decline reason:</label>
									<div class="input-group">
										<select class="w-100 h-30 form-control" name="reason_document" <?php echo addQaUniqueIdentifier("admin-users__verification-form__reason-decline-select")?>>
											<?php foreach($notifications as $notification_id => $notification) { ?>
												<option value="<?php echo $notification_id; ?>">
													<?php echo cleanOutput($notification['title']); ?>
												</option>
											<?php } ?>
										</select>
										<span class="input-group-addon p-0">
											<a class="confirm-dialog ep-icon ep-icon_ok txt-green ml-5 mb-0"
												data-user="<?php echo $user['idu']; ?>"
                                                <?php echo addQaUniqueIdentifier("admin-users__verification-form__confirm-decline-link")?>
												data-message="Are you sure you want to decline this document?"
												data-document="<?php echo $document['id_document']; ?>"
												data-callback="rejectDocumentVersion"
                                                data-atas="admin-users__verification-form__confirm-decline-dialog__button-ok"
												title="Send reason">
											</a>
											<a class="call-function ep-icon ep-icon_remove txt-red mb-0"
												data-callback="hideRejectionList"
                                                <?php echo addQaUniqueIdentifier("admin-users__verification-form__cancel-decline-link")?>
												title="Cancel reason">
											</a>
										</span>
									</div>
								</div>

								<div class="wr-doc-actions js-document-actions">
									<a <?php echo addQaUniqueIdentifier("admin-users__verification-form__decline-icon-button")?> class="ep-icon ep-icon_remove txt-red call-function actions-buttons update-buttons mr-0 fs-16 js-button js-button-reject"
										<?php if ($document['is_version_pending']) { ?>style="display: inline-block;"<?php } else { ?>style="display: none;"<?php } ?>
										data-callback="showRejectionList"
										title="Decline the document">
									</a>

									<a <?php echo addQaUniqueIdentifier("admin-users__verification-form__accept-icon-button")?> class="ep-icon ep-icon_ok txt-green confirm-dialog actions-buttons update-buttons mr-0 fs-16 js-button js-button-accept"
										<?php if (!$document['is_expired'] && $document['is_version_pending']) { ?>style="display: inline-block;"<?php } else { ?>style="display: none;"<?php } ?>
										data-user="<?php echo $user['idu']; ?>"
                                        data-atas="admin-users__verification-form__confirmaccept-dialog__button-ok"
										data-message="Are you sure you want to accept this document?"
										data-document="<?php echo $document['id_document']; ?>"
										data-callback="acceptDocumentVersion"
										title="Accept the document">
									</a>

									<a <?php echo addQaUniqueIdentifier("admin-users__verification-form__show-comment-icon-button")?> class="ep-icon ep-icon ep-icon_comments mr-0 fs-16 js-button js-button-comment call-function"
										<?php if (null === $version || empty($version->getComment())) { ?>style="display: none;"<?php }else { ?>style="display: inline-block;"<?php } ?>
										<?php if (null !== $version) { ?>data-comment="<?php echo cleanOutput($version->getComment()); ?>"<?php } ?>
										data-callback="showDocumentComment"
										data-title="<?php echo cleanOutput(sprintf("Comment on the document \"%s\"", $document['title'])); ?>"
										title="Show comment">
									</a>

									<a <?php echo addQaUniqueIdentifier("admin-users__verification-form__download-document-icon-button")?> class="ep-icon ep-icon_download call-function actions-buttons mr-0 fs-16 js-button js-button-download"
										<?php if ($document['is_downloadable']) { ?>style="display: inline-block;"<?php } else { ?>style="display: none;"<?php } ?>
										data-user="<?php echo $user['idu']; ?>"
										data-document="<?php echo $document['id_document']; ?>"
										data-callback="downloadDocument"
										title="Download the document">
									</a>

									<a <?php echo addQaUniqueIdentifier("admin-users__verification-form__upload-document-icon-button")?> class="ep-icon ep-icon_upload txt-blue fancyboxValidateModal fancybox.ajax mr-0 fs-16 js-button js-button-upload"
										<?php if ($document['is_uploadable']) { ?>style="display: inline-block;"<?php } else { ?>style="display: none;"<?php } ?>
										data-fancybox-href="<?php echo getUrlForGroup("personal_documents/popup_forms/upload/{$document['id_document']}/{$user['idu']}?{$modal_refernce}"); ?>"
										data-title="Upload the document"
										title="Upload the document">
                                    </a>

									<a <?php echo addQaUniqueIdentifier("admin-users__verification-form__upload-another-icon-button")?> class="ep-icon ep-icon_file-plus txt-blue fancyboxValidateModal fancybox.ajax mr-0 fs-16 js-button js-button-upload"
										<?php if ($document['type']['document_is_multiple']) { ?>style="display: inline-block;"<?php } else { ?>style="display: none;"<?php } ?>
										data-fancybox-href="<?php echo getUrlForGroup("personal_documents/popup_forms/upload/{$document['id_document']}/{$user['idu']}?{$modal_refernce}"); ?>"
										data-title="<?php echo translate('general_button_upload_another_full_text', null, true); ?>"
										title="<?php echo translate('general_button_upload_another_full_text', null, true); ?>">
                                    </a>

                                    <a <?php echo addQaUniqueIdentifier("admin-users__verification-form__edit-fields-icon-button")?> class="ep-icon ep-icon_pencil txt-green call-function mr-0 fs-16 js-button js-button-custom-edit"
                                        <?php if ($document['has_dynamic_fields']) { ?>style="display: inline-block;"<?php } else { ?>style="display: none;"<?php } ?>
                                        data-callback="showEditCustomFieldsPopup"
                                        data-title="Edit fields"
                                        data-href="<?php echo getUrlForGroup("verification/popup_forms/edit_custom_fields/user/{$user['idu']}/document/{$document['id_document']}?dialog=1"); ?>"
                                        style="display: inline-block;"
                                        title="Edit fields">
                                    </a>

									<!-- <a class="ep-icon ep-icon_envelope fancyboxValidateModal fancybox.ajax mr-0 fs-16 js-button js-button-contact"
										data-fancybox-href="<?php echo getUrlForGroup("contact/popup_forms/email_user/{$user['idu']}"); ?>"
										data-title="<?php echo cleanOutput("Email user {$user['full_name']}"); ?>"
										title="Email user">
									</a> -->

									<a <?php echo addQaUniqueIdentifier("admin-users__verification-form__remove-document-icon-button")?> class="ep-icon ep-icon_trash txt-red confirm-dialog actions-buttons update-buttons mr-0 fs-16 js-button js-button-remove"
										<?php if ($document['is_version_accepted']) { ?>style="display: none;"<?php } else { ?>style="display: inline-block;"<?php } ?>
										data-user="<?php echo $user['idu']; ?>"
                                        data-atas="admin-users__verification-form__confirm-remove-dialog__button-ok"
										data-message="Are you sure you want to remove this document?"
										data-document="<?php echo $document['id_document']; ?>"
										data-callback="removeDocument"
										title="Remove document">
									</a>
								</div>
							</td>
						</tr>
					<?php } ?>
				<?php } else { ?>
					<tr>
						<td class="vam tac" colspan="5">
							No data available in the table
						</td>
					</tr>
				<?php } ?>
            </tbody>
		</table>
		<div class="js-list-container" <?php if (empty($additional_documents)) { ?>style="display: none;"<?php } ?>>
			<p class="mb-5">
				<strong>
					Additional documents
				</strong>
			</p>
			<ul id="verification-documents--template--document-list" class="list-group clearfix mb-5">
				<?php foreach($additional_documents as $additional_document) { ?>
					<li class="list-group-item">
						<label>
							<input
                                <?php echo addQaUniqueIdentifier("admin-users__verification-form__additional-document-checkbox-" . $additional_document['id_document'])?>
                                class="pull-left mt-1 mr-5"
                                name="documents[]"
                                type="checkbox"
                                value="<?php echo $additional_document['id_document']; ?>">
							<?php echo cleanOutput($additional_document['document_title']); ?>
						</label>
					</li>
				<?php } ?>
			</ul>
		</div>
	</div>

	<div class="wr-form-btns clearfix">
		<?php if (!filter_var($user['is_verified'], FILTER_VALIDATE_BOOLEAN)) { ?>
			<a class="pull-right btn btn-success ml-10 confirm-dialog"
                <?php echo addQaUniqueIdentifier("admin-users__verification-form__complete-verification-button")?>
				data-user="<?php echo $user['idu']; ?>"
				data-callback="completeVerification"
                data-atas="admin-users__verification-form__confirm-complete-verification-dialog__button-ok"
				data-message="Are you sure you want to notify user about completed verification?">
				<span class="ep-icon ep-icon_ok"></span> Complete verification
			</a>
		<?php } ?>

		<button type="submit"
			class="pull-left btn btn-primary mr-10 js-add-additional-documents-button"
            <?php echo addQaUniqueIdentifier("admin-users__verification-form__assign-additional-docs-button")?>
			<?php if (empty($additional_documents)) { ?>style="display:none;"<?php } ?>>
			<span class="ep-icon ep-icon_plus"></span> Assign additional documents
        </button>

        <?php if ('active' === $user['status']) { ?>
            <!-- <?php echo contactUserButton( //TODO: admin chat hidden
                (int) $user['idu'] ?: null,
                $resourceOptions ?? null,
                'Messages',
                '<i class="ep-icon ep-icon_envelope mr-5"></i>',
                array_merge(['class' => 'btn btn-success'], getQaUniqueIdentifierAttributes("admin-users__verification-form__view-message-button")),
                'a'
            ) ?> -->
        <?php } ?>
	</div>
</form>

<script type="text/template" id="verification-documents--template--document-list-item">
	<li class="list-group-item">
		<label>
			<input
            class="pull-left mt-1 mr-5"
            name="documents[]"
            <?php echo addQaUniqueIdentifier("admin-users__verification-form__additional-document-checkbox-recovery-{{id}}")?>
            type="checkbox"
            value="{{id}}"> {{title}}
		</label>
	</li>
</script>
<script>
	$(function() {
		var reasons = JSON.parse('<?php echo json_encode($notifications); ?>') || {};
		var statuses = JSON.parse('<?php echo json_encode(arrayByKey(array_values($statuses), 'type')); ?>') || {};
		var datetimeFields = $(".date-picker");
		var datetimeOptions = {
			yearRange: new Date().getFullYear() + ':' + (new Date().getFullYear() + 50),
			changeYear: true
        }
        var documentsForm = $('#verification-documents--form');
        var fetchedFilesList = $('#verification-documents--files-list .js-files-container');
        var fetchedFilesErrors = $('#verification-documents--files-list .js-error-container');
        var fetchedFilesContainer = $('#verification-documents--files-list');
        var customFieldsEditButton = $('#verification-documents--form .js-button.js-button-custom-edit');
		var additionalDocumentsList = $("#verification-documents--template--document-list");
		var additionalDocumentItemTemplate = $("#verification-documents--template--document-list-item").text() || null;
        var showUserFiles = function () {
            var userId = documentsForm.find('input[type="hidden"][name="user"]').val() || null;
            var showFileslLoader = function () {
                showLoader(fetchedFilesContainer.find('.js-loader'), '');
                fetchedFilesContainer.find('.js-loader').addClass('relative-b').addClass('mnh-80');
            };
            var hideFilesLoader = function () {
                hideLoader(fetchedFilesContainer.find('.js-loader'));
                fetchedFilesContainer.find('.js-loader').removeClass('mnh-80');
            };
            var handleError = function (e) {
                var requestError = e.isCustom ? e.xhr || null : typeof e.statusCode !== "undefined" ? e : null;
                    if (null !== requestError) {
                        fetchedFilesErrors.find('span').text(requestError.responseJSON.message || 'Unknonw server error');
                        fetchedFilesErrors.show();
                    } else {
                        onRequestError(e);
                    }
            };
            var showFiles = function (response) {
                fetchedFilesList.html(response.preview || '');
                fetchedFilesList.show();
            };

            showFileslLoader();
            postRequest(__group_site_url + 'verification/ajax_operations/fetch_files', { user: userId })
                .then(showFiles)
                .catch(handleError)
                .finally(hideFilesLoader);
        }
		var doCompleteVerification = function (userId) {
			return sendRequest(__group_site_url + 'verification/ajax_operations/complete', { user: userId || null })
				.fail(onRequestError);
		};
		var refreshModalOrClose = function () {
			if ($.fancybox.current.element) {
				$.fancybox.current.element.trigger('click');
			} else {
				closeFancyBox();
			}
		};
		var updateDocumentRow = function (row, metadata) {
			metadata = metadata || {};
			var statusCell = row.find('.td-status');
			var reasonCell = row.find('.td-reason');
			var actionsCell = row.find('.td-actions');
			var expirationCell = row.find('.td-expire-on');
			var actionButtons = actionsCell.find('.js-button');
			var rejectButton = actionsCell.find('.js-button-reject');
			var acceptButton = actionsCell.find('.js-button-accept');
			var uploadButton = actionsCell.find('.js-button-upload');
			var removeButton = actionsCell.find('.js-button-remove');
			var contactButton = actionsCell.find('.js-button-contact');
			var commentButton = actionsCell.find('.js-button-comment');
			var downloadButton = actionsCell.find('.js-button-download');
			var editFieldsButton = actionsCell.find('.js-button-custom-edit');
			var statusText = statusCell.find('.display-ib');
			var statusIcon = statusCell.find('.ep-icon');
			var statusLabel = statusCell.find('.js-status');
			var expirationGroup = expirationCell.find('.js-expires');
			var expirationDateInput = expirationCell.find('.js-expires input');

			//region Buttons
			actionButtons.not(contactButton).not(removeButton).hide();
			if (metadata.is_uploadable) {
				uploadButton.show();
			}
			if (metadata.is_downloadable) {
				downloadButton.show();
			}
			if (metadata.is_uploaded) {
				removeButton.show();
			}
			if (metadata.is_version_pending) {
				rejectButton.show();
			}
			if (!metadata.is_expired && metadata.is_version_pending) {
				acceptButton.show();
			}
			if (metadata.is_version_accepted) {
				removeButton.hide();
			}
			if (metadata.has_comment) {
				commentButton.show();
			}
			if (metadata.has_dynamic_fields) {
				editFieldsButton.show();
			}
			//endregion Buttons

			//region Reason
			if (metadata.is_version_rejected) {
				if (metadata.rejection_code && reasons.hasOwnProperty(metadata.rejection_code)) {
					reasonCell.html(reasons[metadata.rejection_code].title);
				} else if (metadata.rejection_title) {
					reasonCell.html(metadata.rejection_title);
				} else  {
					reasonCell.html('&mdash;');
				}
			} else {
				reasonCell.html('&mdash;');
			}
			//endregion Reason

			//region Expiration
			if (metadata.is_version_expirable) {
				expirationCell.find('.js-no-expiration').hide();
				expirationGroup.show();
				if (metadata.expires) {
					try {
						expirationDateInput.datepicker('setDate', new Date(metadata.expires));
					} catch (error) {
						expirationGroup.hide();
					}
				} else {
					expirationDateInput.datepicker('setDate', null);
				}
			} else {
				expirationCell.find('.js-no-expiration').show();
				expirationGroup.hide();
			}
			//endregion Expiration

			//region Status
			var icon = null;
			var colorClass = null;
			var statusMessage = null;
			statusLabel.removeClass(function (index, className) {
				return (className.match (/(^|\s)txt-\S+/g) || []).join(' ');
			});
			statusIcon.removeClass(function (index, className) {
				return (className.match (/(^|\s)ep-icon_\S+/g) || []).join(' ');
			});
			switch (true) {
				case metadata.is_version_rejected:
					if (statuses.rejected) {
						icon = statuses.rejected.icon || null;
						colorClass = statuses.rejected.color || null;
						statusMessage = statuses.rejected.title || null;
					}

					break;
				case metadata.is_expired:
					if (statuses.expired) {
						icon = statuses.expired.icon || null;
						colorClass = statuses.expired.color || null;
						statusMessage = statuses.expired.title || null;
					}

					break;
				case metadata.is_expiring_soon:
					if (statuses.expires) {
						icon = statuses.expires.icon || null;
						colorClass = statuses.expires.color || null;
						statusMessage = statuses.expires.raw_texts.title['*'] || null;
						if (null !== colorClass) {
							colorClass = colorClass.replace('2', '', colorClass);
						}
					}

					break;
				case metadata.is_version_pending:
					if (statuses.pending) {
						icon = statuses.pending.icon || null;
						colorClass = statuses.pending.color || null;
						statusMessage = statuses.pending.title || null;
					}

					break;
				case metadata.is_version_accepted:
					if (statuses.accepted) {
						icon = statuses.accepted.icon || null;
						colorClass = statuses.accepted.color || null;
						statusMessage = statuses.accepted.title || null;
					}

					break;
			}

			if (icon) {
				statusIcon.addClass(icon);
			} else {
				statusIcon.addClass('ep-icon_minus-circle');
			}
			if (colorClass) {
				statusLabel.addClass(colorClass);
			} else {
				statusLabel.addClass('txt-red');
			}
			if (statusMessage) {
				statusText.text(statusMessage);
			} else {
				statusText.text("Not uploaded");
			}
			//endregion Status
		};
		var removeDocumentRow = function (row) {
			var table = row.closest('table');

			row.remove();
			if(!table.find('tr').length) {
				var tableBody = table.find('tbody');
				var row = $('<tr>');
				var cell = $('<td>').addClass('vam tac').attr('colspan', 5).text("No data available in the table");

				tableBody.append(
					row.append(cell)
				);
			}
		};
		var addAdditionalDocumentRow = function (list, type, template) {
			type = type || {};
			var id = type.id || null;
			var title = type.title || null;
			if (null === id || null === title || null === template) {
				return;
			}

			list.append(template.replace(/\{\{id\}\}/g, id).replace(/\{\{title\}\}/g, title));
			list.closest('.js-list-container').show();
		};
		var onRequestStart = function (form) {
			showLoader(form);
		};
		var onRequestEnd = function (form) {
			hideLoader(form);
		};
		var sendRequest = function (url, data) {
			return $.post(url, data || null, null, 'json');
		};
		var downloadDocument = function (button) {
			var url = __group_site_url + 'personal_documents/ajax_operation/download_document';
			var form = button.closest('form');
			var userId = button.data('user') || null;
			var documentId = button.data('document') || null;
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

			if (null === documentId || null === userId) {
				return;
			}

			onRequestStart(form);
			sendRequest(url, { document: documentId, user: userId })
				.done(onRequestSuccess)
				.fail(onRequestError)
				.always(onRequestEnd.bind(null, form));
		};
		var addDocuments = function (form) {
			var url = form.attr('action') || null;
			var data = form.serializeArray();
			var userId = form.find('input[name=user]').val() || null;
			var onRequestSuccess = function (data) {
				systemMessages(data.message, data.mess_type);
				if ('success' === data.mess_type) {
					sendAssignmentNotification(userId, data.documents || null);
					refreshModalOrClose();
				}
			};
			if (null === url) {
				return;
			}

			onRequestStart(form);
			sendRequest(url, data)
				.done(onRequestSuccess)
				.fail(onRequestError)
				.always(onRequestEnd.bind(null, form));
		};
		var removeDocument = function (button) {
			var url = __group_site_url + 'personal_documents/ajax_operation/remove_document';
			var user = button.data('user') || null;
			var form = button.closest('form');
			var document = button.data('document') || null;
			var onRequestSuccess = function (data) {
				systemMessages(data.message, data.mess_type);
				if ('success' === data.mess_type) {
					removeDocumentRow(button.closest('tr'));
					addAdditionalDocumentRow(additionalDocumentsList, data.type || {}, additionalDocumentItemTemplate);
					$('.js-add-additional-documents-button').show();
				}
			};

			if (null === document || null === user) {
				return;
			}

			onRequestStart(form);
			sendRequest(url, { document: document, user: user })
				.done(onRequestSuccess)
				.fail(onRequestError)
				.always(onRequestEnd.bind(null, form));
		};
		var acceptDocumentVersion = function (button) {
			var url = __group_site_url + 'personal_documents/ajax_operation/accept_version';
			var user = button.data('user') || null;
			var form = button.closest('form');
			var document = button.data('document') || null;
			var onRequestSuccess = function (data) {
				systemMessages(data.message, data.mess_type);
				if ('success' === data.mess_type) {
					updateDocumentRow(button.closest('tr'), data.version && data.version.metadata ? data.version.metadata : {});
					sendConfirmationNotification(user, document);
				}
			};

			if (null === document || null === user) {
				return;
			}

			onRequestStart(form);
			sendRequest(url, { document: document, user: user })
				.done(onRequestSuccess)
				.fail(onRequestError)
				.always(onRequestEnd.bind(null, form));
		};
		var rejectDocumentVersion = function (button) {
			var url = __group_site_url + 'personal_documents/ajax_operation/reject_version';
			var user = button.data('user') || null;
			var form = button.closest('form');
			var document = button.data('document') || null;
			var reasonList = button.closest('.document-declined-select').find('select[name="reason_document"]');
			var reasonCode = reasonList.val() || null;
			var onRequestSuccess = function (data) {
				systemMessages(data.message, data.mess_type);
				if ('success' === data.mess_type) {
					hideRejectionList(button);
					updateDocumentRow(button.closest('tr'), data.version && data.version.metadata ? data.version.metadata : {});
					sendRejectionNotification(user, document);
				}
			};

			if (null === document || null === user || null === reasonCode) {
				return;
			}

			onRequestStart(form);
			sendRequest(url, { document: document, user: user, reason: reasonCode })
				.done(onRequestSuccess)
				.fail(onRequestError)
				.always(onRequestEnd.bind(null, form));
		};
		var changeExpirationDate = function (button) {
			var url = __group_site_url + 'personal_documents/ajax_operation/change_expiration_date';
			var user = button.data('user') || null;
			var form = button.closest('form');
			var document = button.data('document') || null;
			var expiresAt = button.closest('.input-group').find('input[name="doc_expire_on"]').val() || null;
			var onRequestSuccess = function (data) {
				systemMessages(data.message, data.mess_type);
				if ('success' === data.mess_type) {
					updateDocumentRow(button.closest('tr'), data.version && data.version.metadata ? data.version.metadata : {});
				}
			};

			if (null === document || null === user) {
				return;
			}

			onRequestStart(form);
			sendRequest(url, { document: document, user: user, expires: expiresAt })
				.done(onRequestSuccess)
				.fail(onRequestError)
				.always(onRequestEnd.bind(null, form));
		};
		var removeExpirationDate = function (button) {
			var url = __group_site_url + 'personal_documents/ajax_operation/remove_expiration_date';
			var user = button.data('user') || null;
			var form = button.closest('form');
			var document = button.data('document') || null;
			var onRequestSuccess = function (data) {
				systemMessages(data.message, data.mess_type);
				if ('success' === data.mess_type) {
					updateDocumentRow(button.closest('tr'), data.version && data.version.metadata ? data.version.metadata : {});
				}
			};

			if (null === document || null === user) {
				return;
			}

			onRequestStart(form);
			sendRequest(url, { document: document, user: user })
				.done(onRequestSuccess)
				.fail(onRequestError)
				.always(onRequestEnd.bind(null, form));
		};
		var completeVerification = function (button) {
			var userId = button.data('user') || null;
			var form = button.closest('form');
			var onRequestSuccess = function (data) {
				systemMessages(data.message, data.mess_type);
				if ('success' === data.mess_type) {
					sendCompletionNotification(userId);
					callFunction('updateTable');
					closeFancyBox();
				}
			};

			if (null === userId) {
				return;
			}

			onRequestStart(form);
			doCompleteVerification(userId)
				.done(onRequestSuccess)
				.always(onRequestEnd.bind(null, form));
		};
		var completeDocumentVersionUpload = function (user, documentId) {
			sendUploadNotification(user, documentId);
		};
		var sendUploadNotification = function (userId, documentId) {
			var url = __group_site_url + 'verification/ajax_operations/upload_notification';
			if (null === userId || null === documentId) {
				return;
			}

			return sendRequest(url, { user: userId, document: documentId }).fail(onRequestError);
		};
		var sendAssignmentNotification = function (userId, documents) {
			var url = __group_site_url + 'verification/ajax_operations/assignment_notification';
			if (null === userId || !(Array.isArray(documents) && documents.length)) {
				return;
			}

			return sendRequest(url, { user: userId, documents: documents || [] }).fail(onRequestError);
		};
		var sendConfirmationNotification = function (userId, documentId) {
			var url = __group_site_url + 'verification/ajax_operations/confirmation_notification';
			if (null === userId || null === documentId) {
				return;
			}

			return sendRequest(url, { user: userId, document: documentId }).fail(onRequestError);
		};
		var sendCompletionNotification = function (userId, documentId) {
			var url = __group_site_url + 'verification/ajax_operations/completion_notification';
			if (null === userId || null === documentId) {
				return;
			}

			return sendRequest(url, { user: userId, document: documentId }).fail(onRequestError);
		};
		var sendRejectionNotification = function (userId, documentId) {
			var url = __group_site_url + 'verification/ajax_operations/rejection_notification';
			if (null === userId || null === documentId) {
				return;
			}

			return sendRequest(url, { user: userId, document: documentId }).fail(onRequestError);
		};
		var showRejectionList = function (button) {
			var actionButtons = button.closest('.js-document-actions');

			actionButtons.fadeOut(function(){
				actionButtons.prev('.js-rejection-list').fadeIn();
			});
		};
		var hideRejectionList = function (button) {
			var rejectionList = button.closest('.js-rejection-list');

			rejectionList.fadeOut(function(){
				rejectionList.next('.js-document-actions').fadeIn();
			});
		};
		var showDocumentComment = function (button) {
			if (!button.is(':visible')) {
				return;
			}

			open_info_dialog(button.data('title') || null, button.data('comment')  || null, false, [
				{
					label: "Close",
					cssClass: 'btn-primary',
					action: function (dialog) {
						dialog.close();
					}
				}
			]);
        };
        var showEditCustomFieldsPopup = function (button) {
            var data = button.data() || {};
            var url = data.url || data.href || null;
            if (null === url) {
                return;
            }

            openPopup(url, data.title || null, data.params || {}).then(function (result) {
                if (!result.shown) {
                    return;
                }

                result.dialog.addButton({
                    label: '<span class="ep-icon ep-icon_ok"></span> Save',
                    cssClass: 'btn-success js-save-edit-field',
                    action: function(dialog){
                        dialog.getModalBody().find('button[type="submit"]').trigger('click').prop('disabled', true);
                    }
                });
                result.dialog.updateButtons();
                result.dialog.getModalBody().removeClass('mnh-100');
                result.dialog.getModalBody().find('.wr-modal-b').removeClass('wr-modal-b');
                result.dialog.getModalBody().find('.modal-b__btns').hide();
                result.dialog.getModalFooter().show();
                result.dialog.getModalFooter().find('.js-save-edit-field').attr('atas', 'admin-users__verification-edit-field-form__save-button');
            });
        };

		datetimeFields.datepicker(datetimeOptions);

        showUserFiles();
		mix(window, {
			removeDocument: removeDocument,
			downloadDocument: downloadDocument,
			modalFormCallBack: addDocuments,
			showRejectionList: showRejectionList,
			hideRejectionList: hideRejectionList,
			showDocumentComment: showDocumentComment,
			changeExpirationDate: changeExpirationDate,
			removeExpirationDate: removeExpirationDate,
			completeVerification: completeVerification,
			rejectDocumentVersion: rejectDocumentVersion,
			acceptDocumentVersion: acceptDocumentVersion,
			callbackUploadDocument: completeDocumentVersionUpload,
			showEditCustomFieldsPopup: showEditCustomFieldsPopup,
		}, false);
	});
</script>
