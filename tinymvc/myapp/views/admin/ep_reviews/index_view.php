<div class="row">
    <div class="col-xs-12">
        <div class="titlehdr">
			<span>User reviews</span>
		</div>

        <?php views('admin/ep_reviews/filter_panel_view'); ?>
        <div class="wr-filter-list clearfix mt-10"></div>

        <table class="data table-striped table-bordered w-100pr" id="dtEpReviews" cellspacing="0" cellpadding="0" >
            <thead>
                <tr>
                    <th class="dt_id tac vam w-40">#</th>
                    <th class="dt_user tac vam w-250">User</th>
                    <th class="dt_message tac vam">Message</th>
                    <th class="dt_is_moderated tac vam w-90">Is moderated</th>
                    <th class="dt_is_published tac vam w-80">Is published</th>
                    <th class="dt_writing_date tac vam w-120">Date of writing</th>
                    <th class="dt_publishing_date tac vam w-130">Date of publication</th>
                    <th class="dt_actions tac vam w-90">Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<script>
    var requirementFilters;
    var dtEpReviews;

    $(document).ready(function(){
        dtEpReviews = $('#dtEpReviews').dataTable( {
            "sDom": '<"top"lp>rt<"bottom"ip><"clear">',
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo __SITE_URL . "ep_reviews/ajax_dt_administration";?>",
            "sServerMethod": "POST",
            "aoColumnDefs": [
                { "sClass": "tac vam w-40",  "aTargets": ['dt_id'], "mData": "id", "bSortable": false },
                { "sClass": "tac vam w-250", "aTargets": ['dt_user'], "mData": "user", "bSortable": false },
                { "sClass": "tac vam", "aTargets": ['dt_message'], "mData": "message", "bSortable": false },
                { "sClass": "tac vam w-90", "aTargets": ['dt_is_moderated'], "mData": "isModerated", "bSortable": false },
                { "sClass": "tac vam w-80", "aTargets": ['dt_is_published'], "mData": "isPublished", "bSortable": false },
                { "sClass": "tac vam w-120", "aTargets": ['dt_writing_date'], "mData": "writingDate" },
                { "sClass": "tac vam w-130", "aTargets": ['dt_publishing_date'], "mData": "publishingDate" },
                { "sClass": "tac vam w-90", "aTargets": ['dt_actions'], "mData": "actions", "bSortable": false },
            ],
            "sorting": [[5, "desc"]],
            "fnServerData": function ( sSource, aoData, fnCallback ) {
                if(!requirementFilters){
                    requirementFilters = $('.dt_filter').dtFilters('.dt_filter',{
                        'container': '.wr-filter-list',
                        'debug':false,
                        callBack: function(){
                            dtEpReviews.fnDraw();
                        },
                        onSet: function(callerObj, filterObj){
                            switch (filterObj.name) {
                                case 'added_from':
                                    $('input[name="added_to"]').datepicker("option", "minDate", $('input[name="added_from"]').datepicker("getDate"));
                                break;
                                case 'added_to':
                                    $('input[name="added_from"]').datepicker("option", "maxDate", $('input[name="added_to"]').datepicker("getDate"));
                                break;
                                case 'published_from':
                                    $('input[name="published_to"]').datepicker("option", "minDate", $('input[name="published_from"]').datepicker("getDate"));
                                break;
                                case 'published_to':
                                    $('input[name="published_from"]').datepicker("option", "maxDate", $('input[name="published_to"]').datepicker("getDate"));
                                break;
                            }
						},
                        onDelete: function(callerObj, filterObj){
                            switch (filterObj.name) {
                                case 'added_from':
                                    $('input[name="added_to"]').datepicker("option", {minDate: null});
                                break;
                                case 'added_to':
                                    $('input[name="added_from"]').datepicker("option", {maxDate: null});
                                break;
                                case 'published_from':
                                    $('input[name="published_to"]').datepicker("option", {minDate: null});
                                break;
                                case 'published_to':
                                    $('input[name="published_from"]').datepicker("option", {maxDate: null});
                                break;
                            }
                        },
						onReset: function(){
                            $('.dt_filter .hasDatepicker').datepicker( "option" , {
								minDate: null,
								maxDate: null
							});
						}
                    });
                }

                aoData = aoData.concat(requirementFilters.getDTFilter());
                $.ajax( {
                    "dataType": 'JSON',
                    "type": "POST",
                    "url": sSource,
                    "data": aoData,
                    "success": function (data, textStatus, jqXHR) {
                        if (data.mess_type == 'error' || data.mess_type == 'info'){
                            systemMessages(data.message, 'message-' + data.mess_type);
                        }

                        fnCallback(data, textStatus, jqXHR);

                    }
                });
            },
            "sPaginationType": "full_numbers",
            "lengthMenu": [[10, 50, 100, 250], [10, 50, 100, 250]],
            "fnDrawCallback": function( oSettings ) {

            }
        });
    });

    var toglePublishStatus = function (element) {
        var btn = $(element);
        var reviewId = btn.data('id');

        $.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL . 'ep_reviews/ajax_operations/togle_published_status';?>',
            data: { review : reviewId},
            dataType: 'json',
            success: function(response){
                systemMessages( response.message, 'message-' + response.mess_type );

                if (response.mess_type == 'success') {
                    dtEpReviews.fnDraw(false);
                }
            }
        });
    }

    var moderateReview = function (element) {
        var btn = $(element);
        var reviewId = btn.data('id');

        $.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL . 'ep_reviews/ajax_operations/moderate_review';?>',
            data: { review : reviewId},
            dataType: 'json',
            success: function(response){
                systemMessages( response.message, 'message-' + response.mess_type );

                if (response.mess_type == 'success') {
                    dtEpReviews.fnDraw(false);
                }
            }
        });
    }

    var deleteReview = function (element) {
        var btn = $(element);
        var reviewId = btn.data('id');

        $.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL . 'ep_reviews/ajax_operations/delete_review';?>',
            data: { review : reviewId},
            dataType: 'json',
            success: function(response){
                systemMessages( response.message, 'message-' + response.mess_type );

                if (response.mess_type == 'success') {
                    dtEpReviews.fnDraw(false);
                }
            }
        });
    }
</script>
