<div class="container-fluid content-dashboard">
	<div class="row">
		<div class="col-xs-12">
			<div class="titlehdr h-30">
				<span>Activity</span>
			</div>

			<?php tmvc::instance()->controller->view->display('admin/activity/filter_panel_view'); ?>

			<div class="wr-filter-list mt-10 clearfix"></div>
            <ul class="menu-level3 mb-10 clearfix log-counters-wrapper">
			    <li class="active">
				    <a class="dt_filter" data-title="Is viewed" data-name="viewed" data-value="" data-value-text="All">
                        All records (<span class="log-counter counter-all"><?php echo $visibility['all']; ?></span>)
                    </a>
			    </li>
                <li>
					<a class="dt_filter" data-title="Is viewed" data-name="viewed" data-value="1" data-value-text="Yes">
                        Viewed records  (<span class="log-counter counter-viewed"><?php echo $visibility['viewed']; ?></span>)
                    </a>
				</li>
                <li>
					<a class="dt_filter" data-title="Is viewed" data-name="viewed" data-value="0" data-value-text="No">
                        Not viewed records (<span class="log-counter counter-not-viewed"><?php echo $visibility['not_viewed']; ?></span>)
                    </a>
				</li>
            </ul>

			<table id="dtActivity" class="data table-bordered table-striped w-100pr dataTable">
                <thead>
                <tr>
                    <th class="dt_id">#</th>
                    <th class="dt_datetime">Date</th>
                    <th class="dt_initiator">Initiator</th>
                    <th class="dt_resource">Resource</th>
                    <th class="dt_message">Message</th>
                    <th class="dt_level">Level</th>
                    <th class="dt_viewed">Is viewed</th>
                    <th class="dt_actions">Actions</th>
                </tr>
                </thead>
                <tbody class="tabMessage" id="pageall"></tbody>
            </table>
		</div>
	</div>
</div>
<script>
	var dtActivity;
    var myFilters;
	$(document).ready(function(){
		dtActivity = $('#dtActivity').dataTable({
			sDom: '<"top"lp>rt<"bottom"ip><"clear">',
			bProcessing: true,
			bServerSide: true,
            iDisplayLength: 50,
			sAjaxSource: "<?php echo __SITE_URL;?>activity/ajax_operations/administration_dt",
			aoColumnDefs: [
				{sClass: "w-75 tac vam", aTargets: ['dt_id'], mData: "dt_id"},
				{sClass: "w-75 tac vam", aTargets: ['dt_datetime'], mData: "dt_datetime"},
				{sClass: "w-100 tac vam", aTargets: ['dt_level'], mData: "dt_level", bSortable: false},
				{sClass: "w-220 vam", aTargets: ['dt_resource'], mData: "dt_resource", bSortable: false},
				{sClass: "w-200 vam", aTargets: ['dt_initiator'], mData: "dt_initiator", bSortable: false},
				{sClass: "vam", aTargets: ['dt_message'], mData: "dt_message", bSortable: false},
				{sClass: "w-60 tac vam", aTargets: ['dt_viewed'], mData: "dt_viewed", bSortable: false},
				{sClass: "w-80 tac vam", aTargets: ['dt_actions'], mData: "dt_actions", bSortable: false}
			],
			fnServerData: function(sSource, aoData, fnCallback) {
				if(!myFilters){
                    var resourceTypeFilter;

                    myFilters = $('.dt_filter').dtFilters('.dt_filter',{
                        container: '.wr-filter-list',
                        callBack: function(){
                            dtActivity.fnDraw();
                        },
                        onSet: function(node, filter){
                            if('resource_type' === filter.name) {
                                resourceTypeFilter = $(node);
                                var resourceNameInput;
                                var resourceNameId = resourceTypeFilter.data('toggle') || null;
                                if(null !== resourceNameId && (resourceNameInput = $(resourceNameId)).length) {
                                    var resourceTypeFilterValue = resourceTypeFilter.val() || null;
                                    if(null !== resourceTypeFilterValue) {
                                        resourceNameInput.removeAttr('disabled');
                                    } else {
                                        resourceNameInput.attr('disabled', 1);
                                    }
                                }
                            }
                        },
                        onDelete: function(filter){
                            if('resource_type' === filter.name && resourceTypeFilter.length) {
                                var resourceNameInput;
                                var resourceNameId = resourceTypeFilter.data('toggle') || null;
                                if(null !== resourceNameId && (resourceNameInput = $(resourceNameId)).length) {
                                    resourceNameInput.attr('disabled', 1);
                                }
                            }
                        },
                        onReset: function(node, filter){
                            if(resourceTypeFilter.length) {
                                var resourceNameInput;
                                var resourceNameId = resourceTypeFilter.data('toggle') || null;
                                if(null !== resourceNameId && (resourceNameInput = $(resourceNameId)).length) {
                                    resourceNameInput.attr('disabled', 1);
                                }
                            }
                        },
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

                        if(data.aoVisibility) {
                            for (var key in data.aoVisibility) {
                                if (data.aoVisibility.hasOwnProperty(key)) {
                                    var value = data.aoVisibility[key];
                                    var label = $('.log-counters-wrapper .counter-' + key.replace(/\_/, '-'));
                                    if(label.length) {
                                        label.text(value)
                                    }
                                }
                            }
                        }

                        fnCallback(data, textStatus, jqXHR);
                    }
                });
			},
			sorting : [[1,'desc']],
			sPaginationType: "full_numbers",
			fnDrawCallback: function(oSettings) {}
		});
	});
</script>
