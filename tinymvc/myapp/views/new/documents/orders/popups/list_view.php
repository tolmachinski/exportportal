<div class="wr-modal-flex inputs-40">
    <div class="modal-flex__form">
        <div class="modal-flex__content mb-0">
            <table id="envelopes-datagrid--detached-list" class="main-data-table w-100pr modal-table">
                <thead>
                    <tr>
                        <th class="preview"><?php echo translate("order_documents_dashboard_datagrid_column_document_text", null, true); ?></th>
                        <th class="created_at"><?php echo translate("order_documents_dashboard_datagrid_column_create_date_text", null, true); ?></th>
                        <th class="updated_at"><?php echo translate("order_documents_dashboard_datagrid_column_update_date_text", null, true); ?></th>
                        <th class="actions"></th>
                    </tr>
                </thead>
                <tbody class="tabMessage" id="pageall"></tbody>
            </table>
        </div>
    </div>
</div>

<?php
    echo dispatchDynamicFragmentInCompatMode(
        'documents:envelopes-detached-grid',
        asset('public/plug/js/documents/orders/detached-grid.js', 'legacy'),
        sprintf(
            "function () { DocumentsDetachedGridModule.default(%s, %s, %s); } ",
            $order['id'],
            json_encode(
                $urls = [
                    'baseUrl'                 => getUrlForGroup("/"),
                    'sendEnvelopeUrl'         => getUrlForGroup("/order_documents/ajax_operation/send-envelope"),
                    'viewEnvelopeUrl'         => getUrlForGroup("/order_documents/ajax_operation/view-envelope"),
                    'listEnvelopesUrl'        => getUrlForGroup("/order_documents/ajax_operation/list-detached-envelopes"),
                    'requireApprovalUrl'      => getUrlForGroup("/order_documents/ajax_operation/require-approval"),
                    'confirmEnvelopeUrl'      => getUrlForGroup("/order_documents/ajax_operation/confirm-envelope"),
                    'downloadDocumentUrl'     => getUrlForGroup("/order_documents/ajax_operation/download-document"),
                    'accessRemoteEnvelopeUrl' => getUrlForGroup("/order_documents/ajax_operation/access-remote-envelope"),
                ]
            ),
            json_encode(
                $selectors = [
                    "datagrid" => "#envelopes-datagrid--detached-list",
                ]
            ),
        ),
        [$order['id'], $urls, $selectors],
    );
?>
