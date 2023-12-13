<div class="wr-modal-b mnw-700 inputs-40">
    <div class="modal-b__content pb-15">
        <table id="envelopes-datagrid--detached-list" class="data table-striped table-bordered vam-table mt-15 mb-0 w-100pr">
            <thead>
                <tr>
                    <th class="dt-details">#</th>
                    <th class="dt-envelope">Document</th>
                    <th class="dt-status">Status</th>
                    <th class="dt-created-at">Created at</th>
                    <th class="dt-updated-at">Updated at</th>
                    <th class="dt-actions"></th>
                </tr>
            </thead>
            <tbody class="tabMessage" id="pageall"></tbody>
        </table>
    </div>
</div>

<script>
    $(function () {
        getScript('<?php echo asset('public/plug_admin/js/documents/orders/detached-grid.js', 'legacy'); ?>', true).then(function () {
            DetachedDocumentsGridModule.default(
                <?php echo json_encode([
                    'listEnvelopesUrl'    => getUrlForGroup('/order_documents/ajax_admin_operation/list-detached-envelopes'),
                    'addEnvelopeTabsUrl'  => getUrlForGroup('/order_documents/start_envelope_edit'),
                    'downloadDocumentUrl' => getUrlForGroup('/order_documents/ajax_admin_operation/download-document'),
                ]); ?>,
                {
                    'datagrid': '#envelopes-datagrid--detached-list',
                    'datepicker': "#envelopes-datagrid--detached-list .date-picker",
                    'rowDetails': "#envelopes-datagrid--detached-list .js-open-row-details",
                },
                <?php echo json_encode(['orderId' => $order['id']]);?>
            );
        });
    });
</script>
