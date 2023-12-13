<div class="row">
    <div class="col-xs-12">
        <h3 class="titlehdr mt-10 mb-10">
        	<span>Payments methods</span>
            <?php if(have_right('manage_bills')) { ?>
        	    <a class="btn btn-primary btn-sm pull-right fancybox.ajax fancyboxValidateModal" href="<?php echo __SITE_URL; ?>payments/popups_payment/add_payment" data-title="Add payment">Add payment</a>
            <?php } ?>
        </h3>

        <?php tmvc::instance()->controller->view->display('admin/payments/filter_panel_view'); ?>

        <div class="wr-filter-list mt-10 clearfix"></div>

        <table id="dt-payment-methods" cellspacing="0" cellpadding="0" class="data table-striped table-bordered vam w-100pr">
            <thead>
                <tr>
                    <th class="dt_id">#</th>
                    <th class="dt_method">Method</th>
                    <th class="dt_translations">Translations</th>
                    <th class="dt_actions">Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
<script type="application/javascript" src="<?php echo __SITE_URL; ?>public/plug_admin/tinymce-4-3-10/tinymce.min.js"></script>
<script type="application/javascript">
    var myFilters;
    var dtPaymentMethods;
    var activateMethod = function(button){
        var self = $(button);
        var action = self.data('action');
        var method = self.data('method') || null;
        var url = self.data('href') || null;
        var onRequestSuccess = function (response) {
            systemMessages(response.message, 'message-' + response.mess_type);
            if(response.mess_type == 'success'){
                dtPaymentMethods.fnDraw(false);
            }
        };

        if(
            null === method ||
            null === url
        ) {
            return;
        }

        $.post(url, { method: method, action: action }, null, 'json').done(onRequestSuccess).fail(onRequestError);
    };
    var removePaymentMethodTranslation = function (button) {
        var self = $(button);
        var url = self.data('href') || null;
        var onRequestSuccess = function (response) {
            systemMessages(response.message, 'message-' + response.mess_type);
            if(response.mess_type == 'success'){
                dtPaymentMethods.fnDraw(false);
            }
        };
        if(null === url) {
            return;
        }

        $.post(url, null, null, 'json').done(onRequestSuccess).fail(onRequestError);
    };

    $(document).ready(function(){
        dtPaymentMethods = $('#dt-payment-methods').dataTable({
			sDom: '<"top"lp>rt<"bottom"ip><"clear">',
			bProcessing: true,
			bServerSide: true,
			sAjaxSource: location.origin + "/payments/administration_dt",
			aoColumnDefs: [
				{sClass: "vam w-50 tac",      aTargets: ['dt_id'],           mData: "dt_id"},
				{sClass: "vam",               aTargets: ['dt_method'],       mData: "dt_method"},
				{sClass: "vam w-400 tac vam", aTargets: ['dt_translations'], mData: "dt_translations", bSortable: false},
				{sClass: "w-80 tac",          aTargets: ['dt_actions'],      mData: "dt_actions",      bSortable: false},
			],
			fnServerData: function(sSource, aoData, fnCallback) {
				if(!myFilters){
                    myFilters = $('.dt_filter').dtFilters('.dt_filter',{
                        container: '.wr-filter-list',
                        callBack: function(){
                            dtPaymentMethods.fnDraw();
                        }
                    });
                }

                aoData = aoData.concat(myFilters.getDTFilter());
                $.ajax({
                    dataType: 'json',
                    type: "POST",
                    url: sSource,
                    data: aoData,
                    success: function(data, textStatus, jqXHR) {
                        if (data.mess_type == 'error') {
                            systemMessages(data.message, 'message-' + data.mess_type);
                        }

                        fnCallback(data, textStatus, jqXHR);
                    }
                });
			},
			sorting : [[0,'desc']],
			sPaginationType: "full_numbers",
		});
    });
</script>
