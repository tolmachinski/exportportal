<div class="wr-modal-flex inputs-40" id="sample-orders-timeline--wrapper">
    <div class="modal-flex__form">
        <div class="modal-flex__content pt-25">
            <div class="container-fluid-modal">
                <table class="main-data-table dataTable" id="sample-orders-timeline--items">
                    <thead>
                        <tr>
                            <th class="w-130">Date</th>
                            <th class="w-100">Member</th>
                            <th class="mnw-100">Activity</th>
                        </tr>
                    </thead>
                    <tbody class="tabMessage">
                        <?php foreach (array_reverse($sample_order_timeline) as $order_log) { ?>
                            <tr>
                                <td data-title="Date"><?php echo getDateFormat($order_log['date'], DATE_ATOM); ?></td>
                                <td data-title="Member"><?php echo $order_log['user']; ?></td>
                                <td data-title="Activity">
                                    <div class="grid-text">
                                        <div class="grid-text__item">
                                            <?php echo $order_log['message']; ?>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script><?php echo getPublicScriptContent('plug/js/sample_orders/popups/timeline.js', true); ?></script>
<script>
    $(function () {
		if (!('SampleOrderTimeline' in window)) {
			if (__debug_mode) {
				console.error(new SyntaxError("'SampleOrderTimeline' must be defined"));
			}

			return;
		}

		SampleOrderTimeline.default({
            selectors: {
                form: '#sample-orders-timeline--wrapper',
                timeline: '#sample-orders-timeline--items',
            }
        });
	});
</script>
