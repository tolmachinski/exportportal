<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/jquery-datatables-1-10-12/jquery.dataTables.min.js');?>"></script>
<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/jquery-dtfilters/jquery.dtFilters.js');?>"></script>

<script>
    var dtFeedbacksList;
    var myFilters;
    filters_has_datepicker = true;

    $(document).ready(function(){
        dataT = dtFeedbacksList = $('#dt-feedbacks-list').dataTable( {
            "sDom": '<"top"i>rt<"bottom"lp><"clear">',
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo __SITE_URL; ?>feedbacks/ajax_my_list_dt/written",
            "sServerMethod": "POST",
            "aoColumnDefs": [
                {"sClass": "w-150", "aTargets": ['dt_user'], "mData": "dt_user", "bSortable": false},
                {"sClass": "w-90", "aTargets": ['dt_order'], "mData": "dt_order", "bSortable": false},
                {"sClass": "", "aTargets": ['dt_title'], "mData": "dt_title", "bSortable": false},
                {"sClass": "w-100 dn-xl", "aTargets": ['dt_added'], "mData": "dt_added", "bSortable": false},
                {"sClass": "w-100 dn-xl", "aTargets": ['dt_statistics'], "mData": "dt_statistics", "bSortable": false},
                {"sClass": "w-50 tac vam", "aTargets": ['dt_actions'], "mData": "dt_actions", "bSortable": false}
            ],
            "sorting" : [],
            "sPaginationType": "full_numbers",
            "language": {
                "paginate": {
                    "first": "<i class='ep-icon ep-icon_arrow-left'></i>",
                    "previous": "<i class='ep-icon ep-icon_arrows-left'></i>",
                    "next": "<i class='ep-icon ep-icon_arrows-right'></i>",
                    "last": "<i class='ep-icon ep-icon_arrow-right'></i>"
                }
            },
            "fnServerData": function ( sSource, aoData, fnCallback ) {
                if(!myFilters){
                    myFilters = initDtFilter();
                }

                aoData = aoData.concat(myFilters.getDTFilter());
                $.ajax( {
                    "dataType": 'JSON',
                    "type": "POST",
                    "url": sSource,
                    "data": aoData,
                    "success": function (data, textStatus, jqXHR) {
                        if(data.mess_type == 'error' || data.mess_type == 'info')
                            systemMessages(data.message, data.mess_type);

                        fnCallback(data, textStatus, jqXHR);

                    }
                });
            },
            "fnDrawCallback": function(oSettings) {
                hideDTbottom(this);
                mobileDataTable($('.main-data-table'));
            }
        });
        dataTableScrollPage(dataT);
    });

    function addFeedbackCallback(resp){
        dtFeedbacksList.fnDraw();
    }

    function editFeedbackCallback(resp){
        dtFeedbacksList.fnDraw();
    }
</script>

<?php tmvc::instance()->controller->view->display('new/filter_panel_main_view', array('filter_panel' => 'new/users_feedbacks/my/filter_view')); ?>

<div class="container-center dashboard-container">

    <div class="dashboard-line">
        <h1 class="dashboard-line__ttl">Written feedbacks</h1>

        <div class="dashboard-line__actions">
            <a class="btn btn-primary pl-20 pr-20 fancybox.ajax fancyboxValidateModal" title="Add feedback" data-title="Add feedback" href="<?php echo __SITE_URL;?>feedbacks/popup_forms/add_feedback">
                <i class="ep-icon ep-icon_plus-circle fs-20"></i>
                <span class="dn-m-min">Add feedback</span>
            </a>
            <!-- <a class="btn btn-light fancybox fancybox.ajax" href="<?php echo __SITE_URL;?>user_guide/popup_forms/show_doc/feedbacks_written_doc?user_type=<?php echo strtolower(user_group_type());?>" title="Written feedbacks" data-title="Written feedbacks" target="_blank">User guide</a> -->
            <a class="btn btn-dark btn-filter fancybox" href="#dtfilter-hidden" data-mw="740" data-title="Filter panel">
                <i class="ep-icon ep-icon_filter"></i> Filter
            </a>
        </div>
    </div>

    <div class="info-alert-b">
		<i class="ep-icon ep-icon_info-stroke"></i>
		<span><?php echo translate('feedbacks_written_description'); ?></span>
	</div>

    <table class="main-data-table" id="dt-feedbacks-list">
        <thead>
            <tr>
                <th class="dt_user">User</th>
                <th class="dt_order">Order</th>
                <th class="dt_title">Title</th>
                <th class="dt_added">Date</th>
                <th class="dt_statistics">Statistics</th>
                <th class="dt_actions"></th>
            </tr>
        </thead>
        <tbody class="tabMessage"></tbody>
    </table>
</div>
