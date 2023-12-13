<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/jquery-datatables-1-10-12/jquery.dataTables.min.js');?>"></script>
<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/jquery-dtfilters/jquery.dtFilters.js');?>"></script>
<?php tmvc::instance()->controller->view->display('new/questions/list_view_scripts'); ?>
<script>
    var dtQuestions;
    var myFilters;
    filters_has_datepicker = true;

    $(document).ready(function(){
        dataT = dtQuestions = $('#dt-questions-list').dataTable( {
            "sDom": '<"top"i>rt<"bottom"lp><"clear">',
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo __CURRENT_SUB_DOMAIN_URL; ?>community_questions/ajax_my_dt",
            "sServerMethod": "POST",
            "aoColumnDefs": [
                {"sClass": "", "aTargets": ['dt_text'], "mData": "dt_text", "bSortable": false},
                {"sClass": "w-100 dn-xl", "aTargets": ['dt_date'], "mData": "dt_date"},
                {"sClass": "w-80 tac dn-xl", "aTargets": ['dt_count_answers'], "mData": "dt_count_answers"},
                {"sClass": "w-200 tac", "aTargets": ['dt_last_answer'], "mData": "dt_last_answer"},
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
                        if(data.mess_type == 'error' || data.mess_type == 'info'){
                            systemMessages(data.message, data.mess_type);
                        }

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
</script>

<?php tmvc::instance()->controller->view->display('new/filter_panel_main_view', array('filter_panel' => 'new/questions/my/filter_view')); ?>

<div class="container-center dashboard-container">

    <div class="dashboard-line">
        <h1 class="dashboard-line__ttl">Community questions</h1>

        <div class="dashboard-line__actions">
            <a class="btn btn-primary pl-20 pr-20" <?php echo addQaUniqueIdentifier("community-question-my__add"); ?> title="Ask a question" data-mw="535" data-title="Ask a question" href="<?php echo __COMMUNITY_URL;?>">
                <i class="ep-icon ep-icon_plus-circle fs-20"></i>
                <span class="dn-m-min">Add question</span>
            </a>
            <a class="btn btn-dark btn-filter fancybox" <?php echo addQaUniqueIdentifier("community_questions_filter_btn")?> href="#dtfilter-hidden" data-mw="740" data-title="Filter panel">
                <i class="ep-icon ep-icon_filter"></i> Filter
            </a>
        </div>
    </div>

    <div class="info-alert-b">
		<i class="ep-icon ep-icon_info-stroke"></i>
		<span><?php echo translate('community_questions_my_description'); ?></span>
	</div>

    <table class="main-data-table" id="dt-questions-list">
        <thead>
            <tr>
				<th class="dt_text">Question</th>
				<th class="dt_date">Date</th>
				<th class="dt_count_answers">Answers</th>
				<th class="dt_last_answer">Last answer</th>
				<th class="dt_actions"></th>
            </tr>
        </thead>
        <tbody class="tabMessage"></tbody>
    </table>
</div>
