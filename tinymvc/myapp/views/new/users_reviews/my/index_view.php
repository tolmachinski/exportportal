<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/jquery-datatables-1-10-12/jquery.dataTables.min.js');?>"></script>
<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/jquery-dtfilters/jquery.dtFilters.js');?>"></script>
<?php tmvc::instance()->controller->view->display('new/users_reviews/reviews_scripts_view');?>

<script>
    var dtReviews;
    var myFilters;
    var delete_review = function(obj){
        var $this = $(obj);
        var review = $this.data('review');

        $.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL?>reviews/ajax_review_operation/delete',
            data: { checked_reviews : review },
            dataType: 'json',
            success: function(data){
                systemMessages( data.message, data.mess_type );
                if(data.mess_type == 'success'){
                    dtReviews.fnDraw();
                }
            }
        });
    }
    filters_has_datepicker = true;

    $(document).ready(function(){
        dataT = dtReviews = $('#dt-reviews-list').dataTable( {
            "sDom": '<"top"i>rt<"bottom"lp><"clear">',
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo __SITE_URL; ?>reviews/ajax_list_my_reviews_dt",
            "sServerMethod": "POST",
            "aoColumnDefs": [
				{"sClass": "w-350", "aTargets": ['dt_item'], "mData": "dt_item", "bSortable": true},
				{"sClass": "", "aTargets": ['dt_title'], "mData": "dt_title", "bSortable": false},
				{"sClass": "w-100 dn-xl", "aTargets": ['dt_date'], "mData": "dt_date", "bSortable": true},
                {"sClass": "w-100 dn-xl", "aTargets": ['dt_replied'], "mData": "dt_replied", "bSortable": true},
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
                $('.rating-bootstrap').rating();
            }
        });
        dataTableScrollPage(dataT);
    });
</script>

<?php tmvc::instance()->controller->view->display('new/filter_panel_main_view', array('filter_panel' => 'new/users_reviews/my/filter_view')); ?>

<div class="container-center dashboard-container">

    <div class="dashboard-line">
        <h1 class="dashboard-line__ttl">My reviews</h1>

        <div class="dashboard-line__actions">
            <?php if(have_right('write_reviews')){?>
                <a class="btn btn-primary pl-20 pr-20 fancybox.ajax fancyboxValidateModal" title="Add review" data-title="Add review" href="<?php echo __SITE_URL;?>reviews/popup_forms/add_review/my<?php echo isBackstopEnabled() ? '?backstop=true' : ''; ?>"  <?php echo addQaUniqueIdentifier("page__my-reviews__dashboard_add-review-btn")?>>
                    <i class="ep-icon ep-icon_plus-circle fs-20"></i>
                    <span class="dn-m-min">Add review</span>
                </a>
            <?php }?>
            <!-- <a class="btn btn-light fancybox fancybox.ajax" href="<?php echo __SITE_URL;?>user_guide/popup_forms/show_doc/items_reviews_doc?user_type=<?php echo strtolower(user_group_type());?>" title="Items reviews" data-title="Items reviews" target="_blank">User guide</a> -->
            <a class="btn btn-dark btn-filter fancybox" <?php echo addQaUniqueIdentifier("page__my-reviews__dashboard_add-filter-btn")?> href="#dtfilter-hidden" data-mw="740" data-title="Filter panel">
                <i class="ep-icon ep-icon_filter"></i> Filter
            </a>
        </div>
    </div>

    <div class="info-alert-b">
		<i class="ep-icon ep-icon_info-stroke"></i>
		<span><?php echo translate('reviews_my_description'); ?></span>
	</div>

    <table class="main-data-table" id="dt-reviews-list">
        <thead>
            <tr>
                <th class="dt_item">Item</th>
                <th class="dt_title">Review</th>
                <th class="dt_date">Created</th>
                <th class="dt_replied">Replied</th>
                <th class="dt_actions"></th>
            </tr>
        </thead>
        <tbody class="tabMessage"></tbody>
    </table>
</div>
