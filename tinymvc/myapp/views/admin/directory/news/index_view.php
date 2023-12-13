<script>
	var myFilters;
	var dtNewsList;
	var delete_image = function(opener){
		var $this = $(opener);
		var id_news = $this.data('news');
		$.ajax({
			type: "POST",
			context: $(this),
			url: "<?php echo __SITE_URL ?>directory/ajax_company_news_operation/delete_image",
			data: {news: id_news},
			dataType: "JSON",
			success: function(resp) {
				if (resp.mess_type == 'success') {
					$this.parents('td').first().fadeOut('slow', function(){$(this).remove();});
					dtNewsList.fnDraw();
				}
				systemMessages(resp.message, 'message-' + resp.mess_type);
			}
		});
	}

	var delete_news_one = function(opener){
		var $this = $(opener);
		var checked_news = $this.data('id');

		$.ajax({
			type: 'POST',
			url: "<?php echo __SITE_URL ?>directory/ajax_company_news_operation/delete",
			dataType: "JSON",
			data: {id: checked_news},
			success: function(data) {
				closeFancyBox();
				systemMessages(data.message, 'message-' + data.mess_type);
				if (data.mess_type != 'error')
					dtNewsList.fnDraw();
			}
		});
	}

	var delete_news_multiple = function(opener){
		var $this = $(opener);
		var checked_news = '';
		$.each($('.check-news:checked'), function(){
			checked_news += $(this).data('id-news') + ',';
		});
		checked_news = checked_news.substring(0, checked_news.length - 1);
		$.ajax({
			type: 'POST',
			url: "<?php echo __SITE_URL ?>directory/ajax_company_news_operation/delete",
			dataType: "JSON",
			data: {id: checked_news},
			success: function(data) {
				closeFancyBox();
				systemMessages(data.message, 'message-' + data.mess_type);
				if (data.mess_type != 'error')
					dtNewsList.fnDraw();
			}
		});
	}

	var moderate_one = function(opener){
		var $this = $(opener);
		var checked_news = $this.data('id');
		$.ajax({
			type: "POST",
			url: "<?php echo __SITE_URL ?>directory/ajax_company_news_operation/moderate",
			dataType: "JSON",
			data: {id: checked_news},
			success: function(resp) {
				if (resp.mess_type == 'success') {
					dtNewsList.fnDraw();
				}
				systemMessages(resp.message, 'message-' + resp.mess_type);
			}
		});
	}
	var moderate_multiple = function(opener){
		var $this = $(this);
		var checked_news = "";
		$(document).find(".check-news").each(function() {
		if ($(this).is(":checked"))
			checked_news += $(this).attr('data-id-news') + ",";
		});

		checked_news = checked_news.slice(0, -1);
		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL ?>directory/ajax_company_news_operation/moderate',
			dataType: "JSON",
			data: {id: checked_news, multiple:1},
			success: function(data) {
				systemMessages(data.message, 'message-' + data.mess_type);
				if (data.mess_type != 'error') {
					dtNewsList.fnDraw();
					$('.check-all-news').prop("checked", false);
				}
			}
		});
	}

$(document).ready(function(){
	function fnFormatDetails(nTr) {
	    var aData = dtNewsList.fnGetData(nTr);

	    var sOut = '<div class="dt-details"><table class="dt-details__table">';
	    sOut += '<tr><td class="w-100">Title' +
		    '<td><p>' + aData['full_title'] +
		    '</p></td>' +
		    '</tr>';
	    sOut += '<tr><td class="w-100">Description' +
		    '<td><p>' + aData['full_description'] +
		    '</p></td>' +
		    '</tr>';
	    sOut += '</table></div>';
	    return sOut;
	}

	dtNewsList = $('#dtNewsList').dataTable({
	    "bProcessing": true,
	    "bServerSide": true,
	    "sAjaxSource": "<?php echo __SITE_URL; ?>directory/ajax_news_list_dt",
	    "aoColumnDefs": [
			{"sClass": "w-50 vam tac", "aTargets": ['id_dt'], "mData": "id", "bSortable": false},
			{"sClass": "w-140 vam tac", "aTargets": ['photo_dt'], "mData": "photo", "bSortable": false},
			{"sClass": "w-300 tac vam", "aTargets": ['company_dt'], "mData": "company"},
			{"sClass": "w-100 tac vat", "aTargets": ['user_dt'], "mData": "user"},
			{"sClass": "w-200 vat", "aTargets": ['title_dt'], "mData": "title"},
			{"sClass": "vat", "aTargets": ['description_dt'], "mData": "description"},
			{"sClass": "w-80 tac vam", "aTargets": ['added_dt'], "mData": "added"},
			{"sClass": "tac vam w-60", "aTargets": ['comments_dt'], "mData": "comments"},
			{"sClass": "w-60 tac vam", "aTargets": ['actions_dt'], "mData": "actions", "bSortable": false}
	    ],
	    "sPaginationType": "full_numbers",
	    "sDom": '<"top"lp>rt<"bottom"ip><"clear">',
	    "sorting": [[6, "desc"]],
	    "fnServerData": function(sSource, aoData, fnCallback) {
		if (!myFilters) {
		    myFilters = $('.dt_filter').dtFilters('.dt_filter',{
				'container': '.wr-filter-list',
				callBack: function() {
					dtNewsList.fnDraw();
				},
				onSet: function(callerObj, filterObj) {
					if (filterObj.name == 'start_date') {
						$("#finish_date").datepicker("option", "minDate", $("#start_date").datepicker("getDate"));
					}
					if (filterObj.name == 'finish_date') {
						$("#start_date").datepicker("option", "maxDate", $("#finish_date").datepicker("getDate"));
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
			$('.rating-bootstrap').rating();
		}
	});

	$('body').on('click', 'a[rel=photo_details]', function() {
		var $this = $(this);
	    var nTr = $this.parents('tr')[0];

	    if (dtNewsList.fnIsOpen(nTr))
			dtNewsList.fnClose(nTr);
	    else
			dtNewsList.fnOpen(nTr, fnFormatDetails(nTr), 'details');

		$this.toggleClass('ep-icon_plus ep-icon_minus');
	});

	$('.check-all-news').on('click', function() {
	    if ($(this).prop("checked")) {
			$('.check-news').prop("checked", true);
			$('.btns-actions-all').show();
	    } else{
			$('.check-news').prop("checked", false);
			$('.btns-actions-all').hide();
	    }
	});

	$('body').on('click', '.check-news', function() {
	    if ($(this).prop("checked")) {
			$('.btns-actions-all').show();
	    } else {
			var hideBlock = true;
			$('.check-news').each(function() {
				if ($(this).prop("checked")) {
					hideBlock = false;
					return false;
				}
			})
			if (hideBlock)
				$('.btns-actions-all').hide();
	    }
	});

	idStartItemNew = <?php echo $last_news_id;?>;
	startCheckAdminNewItems('directory/ajax_company_news_operation/check_new', idStartItemNew);

 });

</script>
<div class="row">
    <div class="col-xs-12">
	<div class="titlehdr h-30">
	    <span>News list</span>
	    <div class="pull-right btns-actions-all display-n">
			<a class="ep-icon ep-icon_remove txt-red pull-right confirm-dialog" data-callback="delete_news_multiple" data-message="Are you sure want to delete these news?" title="Delete news"></a>
			<a class="ep-icon ep-icon_sheild-ok txt-green pull-right mr-5 confirm-dialog" data-callback="moderate_multiple" data-message="Are you sure want to moderate these news?" title="Moderate news"></a>
	    </div>
	</div>

	<?php tmvc::instance()->controller->view->display('admin/directory/news/filter_panel'); ?>

		<div class="wr-filter-list mt-10 clearfix"></div>

        <table id="dtNewsList" class="data table-striped table-bordered w-100pr">
			<thead>
			<tr>
				<th class="id_dt"><input type="checkbox" class="check-all-news pull-left">#</th>
				<th class="photo_dt">News</th>
				<th class="company_dt">Company</th>
				<th class="user_dt">Seller info</th>
				<th class="title_dt">Title</th>
				<th class="description_dt">Description</th>
				<th class="added_dt">Added</th>
				<th class="comments_dt"><span title="Comments">C</span></th>
				<th class="actions_dt">Actions</th>
			</tr>
			</thead>
			<tbody class="tabMessage" id="pageall"></tbody>
		</table>
    </div>
</div>
