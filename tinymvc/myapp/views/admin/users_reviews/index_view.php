<script>
    var dtReviewsList;
    var myFilters;
    var block;

    $(function() {
        dtReviewsList = $('#dtReviewsList').dataTable({
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo __SITE_URL; ?>reviews/ajax_list_dt/reviews",
            "aoColumnDefs": [
                {"sClass": "w-50 tac vam", "aTargets": ['checkboxes'], "mData": "checkboxes", "bSortable": false},
                {"sClass": "w-150 tac", "aTargets": ['item'], "mData": "title"},
                {"sClass": "w-100 tac vam", "aTargets": ['id_order'], "mData": "id_order"},
                {"sClass": "w-130 tac", "aTargets": ['fullname'], "mData": "fullname"},
                {"sClass": "w-40 tac vam", "aTargets": ['rev_rating'], "mData": "rev_rating"},
                {"sClass": "", "aTargets": ['review'], "mData": "review"},
                {"sClass": "mnw-350", "aTargets": ['reply'], "mData": "reply"},
                {"sClass": "w-130 tac vam", "aTargets": ['rev_date'], "mData": "rev_date"},
                {"sClass": "w-50 tt-c", "aTargets": ['rev_status'], "mData": "rev_status"},
                {"sClass": "w-40 tac vam", "aTargets": ['plus'], "mData": "plus"},
                {"sClass": "w-40 tac vam", "aTargets": ['minus'], "mData": "minus"},
                {"sClass": "w-60 tac vam", "aTargets": ['actions'], "mData": "actions", "bSortable": false}
            ],
            "sPaginationType": "full_numbers",
            "sDom": '<"top"lp>rt<"bottom"ip><"clear">',
            "sorting": [[6, "desc"]],
            "fnServerData": function(sSource, aoData, fnCallback) {
                if(!myFilters){
                    myFilters = $('.dt_filter').dtFilters('.dt_filter',{
                        'container': '.wr-filter-list',
                        callBack: function(){
                            dtReviewsList.fnDraw();
                        },
                        onSet: function(callerObj, filterObj){
                            if(filterObj.name == 'start_date'){
                                $("#finish_date").datepicker("option","minDate", $("#start_date").datepicker("getDate"));
                            }
                            if(filterObj.name == 'finish_date'){
                                $("#start_date").datepicker("option","maxDate", $("#finish_date").datepicker("getDate"));
                            }
                        },
                        onReset: function(){
                            $('.filter-admin-panel .hasDatepicker').datepicker( "option" , {
                                minDate: null,
                                maxDate: null
                            });
                        }
                    });
                }

                aoData = aoData.concat(myFilters.getDTFilter());
                $.ajax({
                    "dataType": 'json',
                    "type": "POST",
                    "url": sSource,
                    "data": aoData,
                    "success": function(data, textStatus, jqXHR) {
                    if (data.mess_type == 'error')
                        systemMessages(data.message, 'message-' + data.mess_type);

                    fnCallback(data, textStatus, jqXHR);

                    }
                });
            },
            "fnDrawCallback": function(oSettings) {

            }
        });

        $('.check-all-reviews').on('click', function() {
            if ($(this).prop("checked")){
                $('.check-review').prop("checked", true);
                $('.btns-actions-all').show();
            }else{
                $('.check-review').prop("checked", false);
                $('.btns-actions-all').hide();
            }
        });

        $('body').on('click', '.check-review', function(){
            if($(this).prop("checked")){
                $('.btns-actions-all').show();
            }else {
                var hideBlock = true;
                $('.check-review').each(function(){
                    if($(this).prop("checked")){
                        hideBlock = false;
                        return false;
                    }
                });
                if(hideBlock)
                    $('.btns-actions-all').hide();
            }
        });

        $('body').on('click', '.view-details', function(){
            block = $(this).data('scroll');
        });

        idStartItemNew = <?php echo $last_reviews_id;?>;
        startCheckAdminNewItems('reviews/ajax_review_operation/check_new', idStartItemNew);
    });

	var delete_review = function(opener){
		var $this = $(opener);
		var checked_reviews = $this.data('id');

		$.ajax({
			type: 'POST',
			url: "<?php echo __SITE_URL ?>reviews/ajax_review_operation/delete",
			dataType: "JSON",
			data: {checked_reviews: checked_reviews},
			success: function(data) {
				systemMessages(data.message, 'message-' + data.mess_type);
				if (data.mess_type == 'success'){
					dtReviewsList.fnDraw();
				}
			}
		});
	}

    var checked_reviews = [];
    var moderateReviews = function(){
        if(checked_reviews.length === 0){
            systemMessages( 'No reviews were selected.', 'warning' );
            return;
        }

		$.ajax({
			type: 'POST',
			url: __site_url + 'reviews/ajax_reviews_administration_operation/moderate',
			data: { checked_reviews: checked_reviews.join(',')},
			dataType: 'json',
			success: function(resp){
				systemMessages( resp.message, resp.mess_type );

				if(resp.mess_type == 'success'){
					dtReviewsList.fnDraw();
				}
			}
		});
    }

    var moderate_reviews = function(obj){
        var $this = $(obj);
        checked_reviews = [];
        $('#dtReviewsList').find(".check-review:checked").each(function() {
            checked_reviews.push(intval($(this).attr('data-id-review')));
        });

        moderateReviews();
    }

    var moderate_review = function(opener){
        var $this = $(opener);
        checked_reviews = [$this.data('review')];

        moderateReviews();
    }

    var reviewDetails = function (btn) {
        var nTr = btn.parents('tr')[0];

        if (dtReviewsList.fnIsOpen(nTr)) {
            dtReviewsList.fnClose(nTr);
        } else {
            dtReviewsList.fnOpen(nTr, fnFormatDetails(nTr), 'details');
        }

        btn.toggleClass('ep-icon_plus ep-icon_minus');
    }

    function fnFormatDetails(nTr){
        var aData = dtReviewsList.fnGetData(nTr);

        var sOut = '<div class="dt-details"><table class="dt-details__table">';
            sOut += aData['dt_details'];
            sOut += '</table> </div>';
        return sOut;
    }

    var removeReviewImage = function (element) {
        const imageId = $(element).data('image');
        postRequest(__site_url + 'reviews/ajax_reviews_administration_operation/remove_image', {image: $(element).data('image')}, "json")
        .then(function (response) {
            if ('success' === response.mess_type) {
                $('#js-image-' + imageId).remove();
            }

            systemMessages(response.message, response.mess_type);
        });
    }
</script>

<div class="row">
	<div class="col-xs-12">
		<div class="titlehdr h-30">
		    <span>Reviews list</span>
			<div class="pull-right btns-actions-all display-n">
				<a class="ep-icon ep-icon_sheild-ok txt-green pull-right mr-5 confirm-dialog" data-callback="moderate_reviews" data-message="Are you sure want to moderate selected reviews?" title="Moderate reviews"></a>
			</div>
		</div>
		<?php tmvc::instance()->controller->view->display('admin/users_reviews/filter_panel_view'); ?>

		<div class="wr-filter-list mt-10 clearfix"></div>

		<table id="dtReviewsList" class="data table-bordered table-striped w-100pr" >
			<thead>
				<tr>
				    <th class="checkboxes">
					<input type="checkbox" class="check-all-reviews pull-left mr-5">#</th>
				    <th class="item">Item</th>
				    <th class="id_order">Order</th>
				    <th class="fullname author">User</th>
				    <th class="review tac">Review</th>
					<th class="reply">Reply</th>
				    <th class="rev_date">Date</th>
				    <th class="rev_status">Status</th>
				    <th class="rev_rating tal"><a class="ep-icon ep-icon_star" title="Rating"></a></th>
				    <th class="plus"><a class="ep-icon ep-icon_star-plus" title="Likes"></a></th>
				    <th class="minus"><a class="ep-icon ep-icon_star-minus" title="Dislikes"></a></th>
				    <th class="actions">Actions</th>
				</tr>
			</thead>
			<tbody></tbody>
		</table>
	</div>
</div>


