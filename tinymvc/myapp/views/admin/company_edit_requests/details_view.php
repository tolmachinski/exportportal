<div class="wr-modal-b">
    <div class="modal-b__content pb-0 w-900" id="preview-company-edit-request--wrapper">
        <div class="row">
            <div class="col-xs-12 mb-15">
                <table cellspacing="0" cellpadding="0" class="data table-striped table-bordered mt-5 w-100pr vam-table">
                    <tbody>
                        <tr>
                            <td class="w-25pr tac">
                                <strong>Status</strong>
                                <div>
                                    <span class="fs-12 label <?php echo cleanOutput($status['color']); ?>">
                                        <?php echo cleanOutput($status['label']); ?>
                                    </span>
                                </div>
                            </td>
                            <td class="w-25pr tac">
                                <strong>User</strong>
                                <div>
                                    <a href="<?php echo cleanOutput($profileUrl); ?>" target="_blank">
                                        <?php echo cleanOutput($userName); ?>
                                    </a>
                                </div>
                            </td>
                            <td class="w-25pr tac">
                                <strong>Type</strong>
                                <div>
                                    <span class="fs-12 label label-primary">
                                        Company
                                    </span>
                                </div>
                            </td>
                            <td class="w-25pr tac">
                                <strong>Date</strong>
                                <div>
                                    <?php echo $requestDate ?? '&mdash;'; ?>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="col-xs-12">
                <?php if ($isDeclined) { ?>
                    <div class="warning-alert-b">
                        <i class="ep-icon ep-icon_warning-circle-stroke"></i>
                        <span>This request was declined at <strong><?php echo cleanOutput($declineDate); ?></strong> due to the following reason:</span>
                        <div class="txt-pre-wrap"><?php echo cleanOutput($declineReason); ?></div>
                    </div>
                <?php } else { ?>
                    <?php if (!$isCompleted && $hasOtherRequests) { ?>
                        <div class="warning-alert-b mb-10">
                            <i class="ep-icon ep-icon_warning-circle-stroke"></i>
                            <span>This request cannot be accepted right now: user has pending upgrade or cancellation request.</span>
                        </div>
                    <?php } ?>

                    <div class="form-control">
                        User <a href="<?php echo cleanOutput($profileUrl); ?>" target="_blank"><?php echo cleanOutput($userName); ?></a> has requested <a href="<?php echo cleanOutput($companyUrl); ?>" target="_blank">company</a> update.
                    </div>
                <?php } ?>
            </div>

            <?php if (!empty($companyDiff)) { ?>
                <div class="col-xs-12">
                    <table
                        cellspacing="0"
                        cellpadding="0"
                        class="data table-striped table-bordered table-fixed w-100pr mt-15 mb-15 vam-table"
                        style="table-layout: fixed;"
                    >
                        <thead>
                            <tr role="row">
                                <th class="w-20pr" rowspan="2">Field</th>
                                <th colspan="2">Changes</th>
                            </tr>
                            <tr role="row">
                                <th class="w-50pr">Current</th>
                                <th class="w-50pr">New</th>
                            </tr>
                        </thead>
                        <tbody class="tabMessage">
                            <?php foreach ($companyDiff as $key => list($current, $new)) { ?>
                                <tr>
                                    <td class="w-20pr"><?php echo cleanOutput($key); ?></td>
                                    <td class="w-40pr"><?php echo cleanOutput($current); ?></td>
                                    <td class="w-40pr"><?php echo cleanOutput($new); ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } ?>

            <?php if (!empty($documents ?? null)) { ?>
                <div class="col-xs-12">
                    <table cellspacing="0" cellpadding="0" class="data table-striped table-bordered table-fixed w-100pr mt-15 mb-15 vam-table" style="table-layout:fixed;">
                        <thead>
                            <tr role="row">
                                <th class="w-50 tac">#</th>
                                <th>Document type</th>
                                <th class="w-70 tac"></th>
                            </tr>
                        </thead>
                        <tbody class="tabMessage">
                            <?php foreach ($documents as $index => $document) { ?>
                                <tr>
                                    <td class="w-50 tac"><?php echo cleanOutput($index + 1); ?></td>
                                    <td><?php echo cleanOutput($document['title']); ?></td>
                                    <td class="w-70 tac">
                                        <?php if ($isPending) { ?>
                                            <a
                                                class="ep-icon ep-icon_download txt-blue js-download-original-document txt-green"
                                                title="Download original document"
                                                data-document="<?php echo cleanOutput($document['originalDocument']); ?>"
                                                data-user="<?php echo cleanOutput($userId); ?>"
                                                data-url="<?php echo cleanOutput($document['downloadOriginalUrl']); ?>"
                                            ></a>
                                            <?php if ($document['enabled']) { ?>
                                                <a
                                                    class="ep-icon ep-icon_download txt-blue js-download-edit-request-document"
                                                    title="Download document"
                                                    data-url="<?php echo cleanOutput($document['downloadUrl']); ?>"
                                                ></a>
                                            <?php } else { ?>
                                                <a
                                                    href="#"
                                                    class="ep-icon ep-icon_download txt-blue call-systmess"
                                                    title="Download document"
                                                    data-type="warning"
                                                    data-message="The document is not yet processed. Please try again later."
                                                ></a>
                                            <?php } ?>
                                        <?php } else { ?>
                                            &mdash;
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } ?>
        </div>
    </div>
    <div class="modal-b__btns clearfix mnh-60">
        <?php if ($isPending) { ?>
            <button
                id="preview-company-edit-request--action--accept"
                type="button"
                title="Accept request"
                class="btn btn-success pull-right"
                <?php if (!$isCompleted && $hasOtherRequests) { ?>disabled<?php } ?>
            >
                <span class="ep-icon ep-icon_ok"></span> Accept
            </button>

            <button
                id="preview-company-edit-request--action--decline"
                type="button"
                title="Decline request"
                class="btn btn-danger pull-right mr-10 fancybox.ajax fancyboxValidateModal"
                data-title="Decline request #<?php echo cleanOutput($request); ?>"
                data-fancybox-href="<?php echo cleanOutput($declineUrl); ?>"
            >
                <span class="ep-icon ep-icon_remove"></span> Decline
            </button>
        <?php } ?>
    </div>
</div>

<script><?php echo getPublicScriptContent('plug_admin/js/company_edit_requests/details.js', true); ?></script>
<script>
    $(function () {
		if (!('CompanyEditRequestDetailsModule' in window)) {
			if (__debug_mode) {
				console.error(new SyntaxError("'CompanyEditRequestDetailsModule' must be defined"))
			}

			return;
        }

        CompanyEditRequestDetailsModule.default(
            <?php echo json_encode([
                'acceptUrl'              => $acceptUrl,
                'acceptButton'           => '#preview-company-edit-request--action--accept',
                'detailsWrapper'         => '#preview-company-edit-request--wrapper',
                'downloadButton'         => '.js-download-edit-request-document',
                'downloadOriginalButton' => '.js-download-original-document',
            ]); ?>
        );
	});
</script>
