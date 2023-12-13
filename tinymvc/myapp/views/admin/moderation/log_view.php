<div class="wr-modal-b">
    <div class="modal-b__content pb-0 w-900">
        <div class="row">
            <div class="col-xs-12 mb-15">
                <table cellspacing="0" cellpadding="0" class="data table-striped table-bordered mt-5 w-100pr" id="logs--table">
                    <thead>
                        <tr>
                            <th class="dt_date">Date</th>
                            <th class="dt_step">Step</th>
                            <th class="dt_message">Message</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($activity)) { ?>
                            <?php foreach($activity as $log) { ?>
                                <tr>
                                    <td class="tac">
                                        <?php echo null !== $log['date'] ? $log['date']->format("m/d/Y h:i A") : '-'; ?>
                                    </td>
                                    <td class="tac">
                                        <?php echo !empty($log['step']) ? ucfirst($log['step']) : '-'; ?>
                                    </td>
                                    <td>
                                        <?php if (empty($log['ref'] )) { ?>
                                            <?php tmvc::instance()->controller->view->display('admin/moderation/partials/log_default_view', array(
                                                'resource' => $resource,
                                                'message'  => $log['message'],
                                                'context'  => $log['context'],
                                                'step'     => $log['step'],
                                                'date'     => $log['date'],
                                            )); ?>
                                        <?php } else { ?>
                                            <?php if (!empty($log['notice'] )) { ?>
                                                <?php tmvc::instance()->controller->view->display('admin/moderation/partials/log_notice_view', array(
                                                    'resource' => $resource,
                                                    'message'  => $log['message'],
                                                    'context'  => $log['context'],
                                                    'notice'   => $log['notice'],
                                                    'step'     => $log['step'],
                                                    'date'     => $log['date'],
                                                )); ?>
                                            <?php } ?>
                                            <?php if (!empty($log['block'] )) { ?>
                                                <?php tmvc::instance()->controller->view->display('admin/moderation/partials/log_block_view', array(
                                                    'resource' => $resource,
                                                    'message'  => $log['message'],
                                                    'context'  => $log['context'],
                                                    'notice'   => $log['notice'],
                                                    'block'    => $log['block'],
                                                    'step'     => $log['step'],
                                                    'date'     => $log['date'],
                                                )); ?>
                                            <?php } ?>
                                        <?php } ?>
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
        var collpases = $('.collapse-handler');
        var logTable = $("#logs--table");
        var logDatagridOptions = {
            sDom: '<"top">rt<"bottom"ip><"clear">',
			bProcessing: false,
			bServerSide: false,
			aoColumnDefs: [
				{ sClass: "vam tac w-135", aTargets: ['dt_date'],    bSortable: false },
				{ sClass: "vam tac w-120", aTargets: ['dt_step'],    bSortable: false },
				{ sClass: "",              aTargets: ['dt_message'], bSortable: false },
			],
			sPaginationType: "full_numbers",
            sorting : [[0, 'desc']],
        };

        var collapseHandler = function(event) {
            event.preventDefault();

            var self = $(this);
            var icon = self.find('i');
            var target = $(self.attr('href') || null);
            if(target.length === 0) {
                return;
            }

            if(self.hasClass('active')) {
                icon.removeClass('ep-icon_minus').addClass('ep-icon_plus');
                target.hide();
            } else {
                icon.removeClass('ep-icon_plus').addClass('ep-icon_minus');
                target.show();
            }

            self.toggleClass('active');
        };

        if(logTable.length) {
            logTable.DataTable(logDatagridOptions);
        }
        if(collpases.length) {
            collpases.on('click', collapseHandler);
        }
    })
</script>
