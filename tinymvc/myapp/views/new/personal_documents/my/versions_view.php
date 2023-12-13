<div class="wr-modal-flex inputs-40" id="document-versions--popup">
    <div class="modal-flex__form">
        <div class="modal-flex__content">
            <div class="container-fluid-modal">
                <div class="row">
                    <div class="col-12">
                        <table id="dtDocumentVersions" class="main-data-table modal-table">
                            <thead>
                            <tr>
                                <th class="version_dt">Version</th>
                                <th class="created_dt">Created</th>
                                <th class="actions_dt"></th>
                            </tr>
                            </thead>
                            <tbody class="tabMessage" id="pageall"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php if(null !== $reference['from']) { ?>
            <div class="modal-flex__btns">
                <?php views()->display('new/return_back_button_view', array('reference' => $reference['from'], 'id' => "document-versions--popup--reference")); ?>
            </div>
        <?php } ?>
    </div>
</div>

<script>
    $(function() {
        var reference = '<?php echo !empty($reference['to']) ? "?{$reference['to']}" : null; ?>';
        var onServerRequest = function(url, data, callback) {
            var onRequestSuccess = function(response, textStatus, jqXHR) {
                if (response.mess_type == 'error') {
                    systemMessages(response.message, response.mess_type);
                }

                callback(response, textStatus, jqXHR);
            };

            $.post(url, data, null, 'json').done(onRequestSuccess).fail(onRequestError);
        };
        var onDatagridDraw = function() {
            mobileDataTable(dtDocumentVersions);
            $.fancybox.update();
        };
        var dtDocumentVersions = $('#dtDocumentVersions')
        if((dtDocumentVersions.length > 0) && ($(window).width() < 768)){
            dtDocumentVersions.addClass('main-data-table--mobile');
        }

        dtDocumentVersions.dataTable({
            bProcessing: true,
            bServerSide: true,
            sAjaxSource: __group_site_url + "personal_documents/ajax_operation/versions/<?php echo $document['id_document']; ?>" + reference,
            aoColumnDefs: [
                { sClass: "",             aTargets: ['version_dt'], mData: "version",    bSortable: false },
                { sClass: "w-100",        aTargets: ['created_dt'], mData: "created_at", bSortable: false },
                { sClass: "w-40 tac vam", aTargets: ['actions_dt'], mData: "actions",    bSortable: false },
            ],
            sDom: '<"top"l>rt<"bottom"p><"clear">',
            sorting : [],
            sPaginationType: "full_numbers",
            fnServerData: onServerRequest,
            fnDrawCallback: onDatagridDraw,
            language: {
                paginate: {
                    previous: '<i class="ep-icon ep-icon_arrows-left"></i>',
                    first: '<i class="ep-icon ep-icon_arrow-left"></i>',
                    next: '<i class="ep-icon ep-icon_arrows-right"></i>',
                    last: '<i class="ep-icon ep-icon_arrow-right"></i>'
                }
            },
            aLengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
        });
    });
</script>