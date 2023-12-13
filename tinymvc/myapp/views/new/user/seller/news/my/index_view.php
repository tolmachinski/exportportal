<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/jquery-datatables-1-10-12/jquery.dataTables.min.js');?>"></script>
<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/jquery-dtfilters/jquery.dtFilters.js');?>"></script>
<?php tmvc::instance()->controller->view->display('new/user/seller/news/news_scripts_view'); ?>

<script>
    var dtNews;
    var myFilters;
    filters_has_datepicker = true;

    $(document).ready(function(){
        dataT = dtNews = $('#dt-news-list').dataTable( {
            "sDom": '<"top"i>rt<"bottom"lp><"clear">',
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo __SITE_URL; ?>seller_news/ajax_news_list_dt",
            "sServerMethod": "POST",
            "aoColumnDefs": [
				{"sClass": "", "aTargets": ['dt_news'], "mData": "dt_news", "bSortable": false},
				{"sClass": "w-120 tac vam", "aTargets": ['dt_date'], "mData": "dt_date", "bSortable": false},
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

<?php tmvc::instance()->controller->view->display('new/filter_panel_main_view', array('filter_panel' => 'new/user/seller/news/my/filter_view')); ?>

<div class="container-center dashboard-container">

    <div class="dashboard-line">
        <h1 class="dashboard-line__ttl">Company news</h1>

        <div class="dashboard-line__actions">
            <a
                class="btn btn-primary pl-20 pr-20 fancybox.ajax fancyboxValidateModal"
                title="Add company news"
                data-title="Add company news"
                href="<?php echo __SITE_URL;?>seller_news/popup_forms/add_news_form"
                <?php echo addQaUniqueIdentifier('seller-news-my__add-news-btn'); ?>
            >
                <i class="ep-icon ep-icon_plus-circle fs-20"></i>
                <span class="dn-m-min">Add news</span>
            </a>
            <!-- <a class="btn btn-light fancybox fancybox.ajax" href="<?php echo __SITE_URL;?>user_guide/popup_forms/show_doc/company_post_news_doc?user_type=seller" title="Company news" data-title="Company news" target="_blank">User guide</a> -->
            <a
                class="btn btn-dark btn-filter fancybox"
                href="#dtfilter-hidden"
                data-mw="740"
                data-title="Filter panel"
                <?php echo addQaUniqueIdentifier('seller-news-my__filter-btn'); ?>
            >
                <i class="ep-icon ep-icon_filter"></i> Filter
            </a>
        </div>
    </div>

    <div class="info-alert-b">
		<i class="ep-icon ep-icon_info-stroke"></i>
		<span><?php echo translate('copmany_news_description'); ?></span>
	</div>

    <table class="main-data-table" id="dt-news-list" <?php echo addQaUniqueIdentifier('seller-news-my__news-table'); ?>>
        <thead>
            <tr>
                <th class="dt_news">News</th>
                <th class="dt_date">Date</th>
                <th class="dt_actions"></th>
            </tr>
        </thead>
        <tbody class="tabMessage"></tbody>
    </table>
</div>
