<div class="row">
	<div class="col-xs-12">
		<div class="titlehdr h-30">
			<span>Order documents</span>

            <?php foreach ($integrations as $name => $integration) { ?>
                <?php if (!$integration['enabled']) { ?>
                    <?php continue; ?>
                <?php } ?>

                <?php if (!$integration['connected']) { ?>
                    <a class="btn btn-default pull-right ml-10 js-get-consent"
                        title="<?php echo cleanOutput(sprintf("Connect %s", $integration['title'])); ?>"
                        data-title="<?php echo cleanOutput($integration['title']); ?>"
                        data-href="<?php echo cleanOutput(getUrlForGroup("/oauth2/authorize?type={$name}")); ?>">
                        <?php echo cleanOutput(sprintf("Connect %s", $integration['title'])); ?>
                    </a>
                <?php } else { ?>
                    <a class="btn btn-default pull-right ml-10 js-refresh-token"
                        title="<?php echo cleanOutput(sprintf("Refresh %s Token", $integration['title'])); ?>"
                        data-title="<?php echo cleanOutput($integration['title']); ?>"
                        data-href="<?php echo cleanOutput(getUrlForGroup("/oauth2/authorize?type={$name}")); ?>">
                        <?php echo cleanOutput(sprintf("Refresh %s Token", $integration['title'])); ?>
                    </a>
                <?php } ?>
            <?php } ?>
        </div>

        <?php views()->display('admin/documents/orders/filter_view'); ?>

		<div class="mt-10 wr-filter-list clearfix"></div>

		<table id="order-documents--list" class="data table-bordered table-striped w-100pr dataTable">
			<thead>
                <tr>
                    <th class="dt-details">#</th>
                    <th class="dt-order">Order</th>
                    <th class="dt-sender">Sender</th>
                    <th class="dt-status">Status</th>
                    <th class="dt-type">Type</th>
                    <th class="dt-envelope">Document</th>
                    <th class="dt-description">Description</th>
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
        getScript('<?php echo asset('public/plug_admin/js/documents/orders/grid.js', 'legacy'); ?>', true).then(function () {
            DocumentsGridModule.default(
                <?php echo json_encode([
                    'listEnvelopesUrl'    => getUrlForGroup('/order_documents/ajax_admin_operation/list-envelopes'),
                    'addEnvelopeTabsUrl'  => getUrlForGroup('/order_documents/start_envelope_edit'),
                    'downloadDocumentUrl' => getUrlForGroup('/order_documents/ajax_admin_operation/download-document'),
                ]); ?>,
                {
                    'filters': '.dt-filter',
                    'datagrid': '#order-documents--list',
                    'datepicker': ".date-picker",
                    'rowDetails': ".js-open-row-details",
                    'consentButtons': ".js-get-consent",
                    'refreshTokenButtons': ".js-refresh-token",
                },
                {
                    'order': { 'name': 'order', 'selector': '#documents--order--filter-order' },
                    'sender': { 'name': 'sender', 'selector': '#documents--order--filter-sender' },
                    'document': { 'name': 'document', 'selector': '#documents--order--filter-document' },
                    'recipient': { 'name': 'recipient', 'selector': '#documents--order--filter-recipient' },
                    'createdFrom': { 'name': 'created_from', 'selector': '#documents--order--filter-created-from' },
                    'updatedFrom': { 'name': 'updated_from', 'selector': '#documents--order--filter-updated-from' },
                    'createdTo': { 'name': 'created_to', 'selector': '#documents--order--filter-created-to' },
                    'updatedTo': { 'name': 'updated_to', 'selector': '#documents--order--filter-updated-to' },
                },
                <?php echo json_encode($filters ?? []); ?>
            );
        });
    });
</script>
