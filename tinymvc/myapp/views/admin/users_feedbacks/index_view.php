<script>
    var dtFeedbacksList;
    var myFilters;

    function fnFormatDetails(nTr) {
		var aData = dtFeedbacksList.fnGetData(nTr);
		var services = '';
		$.each(aData['services'], function(i, val){
			services += '<div class="clearfix mb-5">\
						<span class="pull-left w-130 lh-15 pr-5">' + i + '</span>\
						<span class="pull-left">\
							<input class="rating-bootstrap" data-filled="ep-icon ep-icon_star txt-orange fs-15 m-0" data-empty="ep-icon ep-icon_star-empty txt-orange fs-15 m-0" type="hidden" name="val" value="' + val +'" data-readonly>\
							<span class="rating-bootstrap-status txt-green fs-15 lh-15 display-ib"></span>\
						</span>\
					</div>';
		});

		var sOut = '<div class="dt-details"><table class="dt-details__table">';
		sOut += '<tr><td class="w-100">Feedback</td>' +
				'<td><p class="mb-10"><strong>' + aData['full_title'] +
				'</strong></p><p class="mb-10">' + aData['full_text'] +
				'</p><p class="mb-10">' + aData['added'] +
				'</p><p>' +
				'</p></td>' +
			'</tr>';
		sOut += '<tr><td>Service\'s rating</td>' +
				'<td>' + services +'</td>' +
			'</tr>';
		sOut += '<tr><td>Reply</td>' +
				'<td><p class="mb-10">' + aData['full_reply'] +
				'</p><p>' + aData['reply_date'] +
				'</p></td></tr>';
		sOut += '</table></div>';
		return sOut;
    }

    $(document).ready(function(){
        dtFeedbacksList = $('#dtFeedbacksList').dataTable({
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo __SITE_URL; ?>feedbacks/ajax_list_dt",
            "aoColumnDefs": [
                {"sClass": "w-50 tac vam", "aTargets": ['checkboxes'], "mData": "checkboxes", "bSortable": false},
                {"sClass": "w-150 tac", "aTargets": ['user'], "mData": "user"},
                {"sClass": "w-150 tac", "aTargets": ['poster'], "mData": "poster"},
                {"sClass": "w-150 tac", "aTargets": ['order'], "mData": "order", "bSortable": false},
                {"sClass": "w-150 vam", "aTargets": ['title_dt'], "mData": "title"},
                {"sClass": "vam", "aTargets": ['text_dt'], "mData": "text"},
                {"sClass": "w-130 vam", "aTargets": ['reply'], "mData": "reply"},
                {"sClass": "w-80 tac vam", "aTargets": ['added'], "mData": "added"},
                {"sClass": "w-40 tac vam", "aTargets": ['rating'], "mData": "rating"},
                {"sClass": "w-40 tac vam", "aTargets": ['plus'], "mData": "plus"},
                {"sClass": "w-40 tac vam", "aTargets": ['minus'], "mData": "minus"},
                {"sClass": "w-60 tac vam", "aTargets": ['actions'], "mData": "actions", "bSortable": false}
            ],
            "sPaginationType": "full_numbers",
            "sDom": '<"top"lp>rt<"bottom"ip><"clear">',
            "sorting": [[7, "desc"]],
            "fnServerData": function(sSource, aoData, fnCallback) {
            if(!myFilters){
            myFilters = $('.dt_filter').dtFilters('.dt_filter',{
                    'container': '.wr-filter-list',
                    callBack: function(){
                        dtFeedbacksList.fnDraw();
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

        $('body').on('click', 'a[rel=feedback_details]', function() {
            var $thisBtn = $(this);
            var nTr = $thisBtn.parents('tr')[0];

            if (dtFeedbacksList.fnIsOpen(nTr)) {
                dtFeedbacksList.fnClose(nTr);
            }else{
                dtFeedbacksList.fnOpen(nTr, fnFormatDetails(nTr), 'details');
                $('.rating-bootstrap').rating();

				$('.rating-bootstrap').each(function () {
					var $this = $(this);
					ratingBootstrap($this);
				});
            }

			$thisBtn.toggleClass('ep-icon_plus ep-icon_minus');
        });

        $('.check-all-feedbacks').on('click', function() {
			if ($(this).prop("checked")){
				$('.check-feedback').prop("checked", true);
				$('.btns-actions-all').show();
            }else {
				$('.check-feedback').prop("checked", false);
				$('.btns-actions-all').hide();
            }
        });

        $('body').on('click', '.check-feedback', function(){
            if($(this).prop("checked")){
                $('.btns-actions-all').show();

            } else {
                var hideBlock = true;
                $('.check-feedback').each(function(){
                    if($(this).prop("checked")){
                        hideBlock = false;
                        return false;
                    }
                });
                if(hideBlock)
                    $('.btns-actions-all').hide();
            }
        });

        idStartItemNew = <?php echo $last_feedbacks_id;?>;
        startCheckAdminNewItems('feedbacks/ajax_feedbacks_administration_operation/check_new', idStartItemNew);
    });

    var moderate_feedback = function(obj){
		var $this = $(obj);
		var feedback = [];
		feedback[0] = $this.data('feedback');

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL?>feedbacks/ajax_feedbacks_administration_operation/moderate',
			data: { checked_feedbacks: feedback },
			dataType: 'json',
			success: function(data){
				systemMessages( data.message, 'message-' + data.mess_type );
				if(data.mess_type == 'success'){
					dtFeedbacksList.fnDraw();
				}
			}
		});
	}

	var moderate_feedbacks = function(){
		var checked_feedbacks = new Array();
		$(document).find(".check-feedback").each(function() {
			if($(this).is(":checked"))
				checked_feedbacks.push($(this).data('id-feedback'));
		});

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL ?>feedbacks/ajax_feedbacks_administration_operation/moderate',
			dataType: "JSON",
			data: {checked_feedbacks: checked_feedbacks},
			success: function(data) {
				systemMessages(data.message, 'message-' + data.mess_type);
				if (data.mess_type != 'error') {
					dtFeedbacksList.fnDraw();
					$('.check-all-feedbacks').prop("checked", false);
				}
			}
		});
	}
</script>

<div class="row">
    <div class="col-xs-12">
		<div class="titlehdr h-30">
		    <span>Feedback list</span>
			<div class="pull-right btns-actions-all display-n">
				<a class="ep-icon ep-icon_sheild-ok txt-green mr-5 pull-right confirm-dialog" data-callback="moderate_feedbacks" data-message="Are you sure want moderate selected feedback?" title="Moderate feedback"></a>
			</div>
		</div>

		<?php tmvc::instance()->controller->view->display('admin/users_feedbacks/filter_panel'); ?>
		<div class="wr-filter-list mt-10 clearfix"></div>

        <table id="dtFeedbacksList" class="data table-bordered table-striped w-100pr" >
            <thead>
                <tr>
                    <th class="checkboxes"><input type="checkbox" class="check-all-feedbacks pull-left">#</th>
					<th class="poster">Poster</th>
					<th class="user">User</th>
					<th class="order">Order</th>
                    <th class="tac title_dt">Title</th>
                    <th class="tac text_dt">Text</th>
                    <th class="tac reply">Reply</th>
                    <th class="added">Added</th>
					<th class="rating tal"><a class="ep-icon ep-icon_star" title="Rating"></a></th>
                    <th class="plus"><a class="ep-icon ep-icon_star-plus" title="Likes"></a></th>
                    <th class="minus"><a class="ep-icon ep-icon_star-minus" title="Dislikes"></a></th>
					<th class="actions">Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
