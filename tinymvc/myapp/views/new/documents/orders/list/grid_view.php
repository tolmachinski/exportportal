<table class="main-data-table" id="envelopes-datagrid--list">
    <thead>
        <tr>
            <th class="preview"><?php echo translate("order_documents_dashboard_datagrid_column_document_text", null, true); ?></th>
            <th class="description"><?php echo translate("order_documents_dashboard_datagrid_column_description_text", null, true); ?></th>
            <th class="created_at"><?php echo translate("order_documents_dashboard_datagrid_column_create_date_text", null, true); ?></th>
            <th class="updated_at"><?php echo translate("order_documents_dashboard_datagrid_column_update_date_text", null, true); ?></th>
            <th class="actions"></th>
        </tr>
    </thead>
    <tbody class="tabMessage"></tbody>
</table>

<?php
    echo dispatchDynamicFragmentInCompatMode(
        'documents:envelopes-grid',
        asset('public/plug/js/documents/orders/grid.js', 'legacy'),
        sprintf(
            "function () { DocumentsGridModule.default(%s, %s, %s); } ",
            json_encode(
                $urls = [
                    'baseUrl'                 => getUrlForGroup('/'),
                    'sendEnvelopeUrl'         => getUrlForGroup('/order_documents/ajax_operation/send-envelope'),
                    'listEnvelopesUrl'        => getUrlForGroup('/order_documents/ajax_operation/list-envelopes'),
                    'viewEnvelopeUrl'         => getUrlForGroup('/order_documents/ajax_operation/view-envelope'),
                    'requireApprovalUrl'      => getUrlForGroup('/order_documents/ajax_operation/require-approval'),
                    'confirmEnvelopeUrl'      => getUrlForGroup('/order_documents/ajax_operation/confirm-envelope'),
                    'downloadDocumentUrl'     => getUrlForGroup('/order_documents/ajax_operation/download-document'),
                    'accessRemoteEnvelopeUrl' => getUrlForGroup('/order_documents/ajax_operation/access-remote-envelope'),
                ]
            ),
            json_encode(
                $selectors = [
                    'filters'    => '.dt-filter',
                    'datagrid'   => '#envelopes-datagrid--list',
                    'datepicker' => '.datepicker-init',
                ]
            ),
            json_encode(
                $filterTypes = [
                    'order'       => ['name' => 'order', 'selector' => '#documents--order--filter-document'],
                    'document'    => ['name' => 'document', 'selector' => '#documents--order--filter-document'],
                    'createdFrom' => ['name' => 'created_from', 'selector' => '#documents--order--filter-created-from'],
                    'updatedFrom' => ['name' => 'updated_from', 'selector' => '#documents--order--filter-updated-from'],
                    'createdTo'   => ['name' => 'created_to', 'selector' => '#documents--order--filter-created-to'],
                    'updatedTo'   => ['name' => 'updated_to', 'selector' => '#documents--order--filter-updated-to'],
                ]
            ),
        ),
        [$urls, $selectors, $filterTypes],
        true
    );
?>
