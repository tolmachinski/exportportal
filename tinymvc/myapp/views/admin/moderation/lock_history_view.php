<div class="wr-modal-b">
    <div class="modal-b__content pb-0 w-900">
        <div class="row">
            <div class="col-xs-12 mb-15">
                <table cellspacing="0" cellpadding="0" class="data table-striped table-bordered mt-5 w-100pr" id="logs--table">
                    <thead>
                        <tr>
                            <th class="dt_date">Date</th>
                            <th class="dt_moderator">Moderator</th>
                            <th class="dt_reason">Reason</th>
                            <th class="dt_message">Message</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($lock_history)) { ?>
                            <?php foreach($lock_history as $log) { ?>
                                <tr>
                                    <td class="tac">
                                        <?php echo null !== $log['date'] ? $log['date']->format("m/d/Y h:i A") : '-'; ?>
                                    </td>
                                    <td>
                                        <?php echo !empty($log['moderator']['fullname']) ? $log['moderator']['fullname'] : '-'; ?>
                                    </td>
                                    <td>
                                        <?php echo !empty($log['reason']) ? cleanOutput($log['reason']) : '-'; ?>
                                    </td>
                                    <td>
                                        <?php echo !empty($log['message']) ? cleanOutput($log['message']) : '-'; ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 mb-15">
            </div>
        </div>
    </div>
    <div class="modal-b__btns clearfix">
    </div>
</div>
<script type="application/javascript">
    $(function() {
        var logTable = $("#logs--table");
        var logDatagridOptions = {
            sDom: '<"top">rt<"bottom"ip><"clear">',
			bProcessing: false,
			bServerSide: false,
			aoColumnDefs: [
				{ sClass: "vat tac w-75",  aTargets: ['dt_date'],      bSortable: false },
				{ sClass: "vat tac w-120", aTargets: ['dt_moderator'], bSortable: false },
				{ sClass: "vat w-200",     aTargets: ['dt_reason'],    bSortable: false },
				{ sClass: "",              aTargets: ['dt_message'],   bSortable: false },
			],
			sPaginationType: "full_numbers",
            sorting : [[0, 'desc']],
        };

        if(logTable.length) {
            logTable.DataTable(logDatagridOptions);
        }
    })
</script>