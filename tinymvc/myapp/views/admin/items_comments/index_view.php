<script>
    var dtItemsCommentsList;
    var myFilters;
    var block = "";

    function fnFormatDetails(nTr) {
		var aData = dtItemsCommentsList.fnGetData(nTr);
		var sOut = '<div class="dt-details"><table class="dt-details__table">';
		sOut += '<tr><td class="w-80">Comment</td>' +
			'<td><p class="mb-10">' + aData['full_text'] +
			'</p><p>' + aData['added'] +
			'</p></td>' +
			'</tr>';
		sOut += '</table></div>';
		return sOut;
    }

$(document).ready(function() {
	dtItemsCommentsList = $('#dtItemsCommentsList').dataTable({
	    "bProcessing": true,
	    "bServerSide": true,
	    "sAjaxSource": "<?php echo __SITE_URL; ?>items_comments/ajax_list_dt",
	    "aoColumnDefs": [
		{"sClass": "w-60 tac vam", "aTargets": ['checkboxes'], "mData": "checkboxes", "bSortable": false},
		{"sClass": "w-150 tac", "aTargets": ['author'], "mData": "author"},
		{"sClass": "w-300 tac", "aTargets": ['item'], "mData": "item"},
		{"sClass": "vam", "aTargets": ['text_dt'], "mData": "text"},
		{"sClass": "w-90 tac vam", "aTargets": ['added'], "mData": "added"},
		{"sClass": "w-60 tac vam", "aTargets": ['actions'], "mData": "actions", "bSortable": false}
	    ],
	    "sPaginationType": "full_numbers",
	    "sDom": '<"top"lp>rt<"bottom"ip><"clear">',
	    "sorting": [[2, "desc"]],
	    "fnServerData": function(sSource, aoData, fnCallback) {
		if(!myFilters){
		myFilters = $('.dt_filter').dtFilters('.dt_filter',{
				'container': '.wr-filter-list',
				callBack: function(){
					dtItemsCommentsList.fnDraw();
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
	$('body').on('click', '.com-tree', function(){
		block = $(this).data('scroll-block');
	});

	$('body').on('click', 'a[rel=item_comment_details]', function() {
		var $thisBtn = $(this);
	    var nTr = $thisBtn.parents('tr')[0];
	    if (dtItemsCommentsList.fnIsOpen(nTr)) {
			dtItemsCommentsList.fnClose(nTr);
	    }else{
			dtItemsCommentsList.fnOpen(nTr, fnFormatDetails(nTr), 'details');
	    }

		$thisBtn.toggleClass('ep-icon_plus ep-icon_minus');
	});

	$('.check-all-item-comments').on('click', function() {
	    if ($(this).prop("checked")){
			$('.check-item-comment').prop("checked", true);
			$('.btns-actions-all').show();
	    }else {
			$('.check-item-comment').prop("checked", false);
			$('.btns-actions-all').hide();
	    }
	});

	$('body').on('click', '.check-item-comment', function(){
		if($(this).prop("checked")){
		    $('.btns-actions-all').show();
		}else {
		    var hideBlock = true;
		    $('.check-item-comment').each(function(){
				if($(this).prop("checked")){
					hideBlock = false;
					return false;
				}
		    });
		    if(hideBlock)
				$('.btns-actions-all').hide();
		}
	});

	idStartItemNew = <?php echo $last_items_comments_id;?>;
	startCheckAdminNewItems('items_comments/ajax_comments_administration_operation/check_new', idStartItemNew);
});

	var moderate_comment = function(opener){
		var $this = $(opener);
		var comment = [];
		comment[0] = $this.data('comment');

		$.ajax({
			type: 'POST',
			url: "<?php echo __SITE_URL ?>items_comments/ajax_comments_administration_operation/moderate",
			dataType: "JSON",
			data: {checked_comments: comment},
			success: function(data) {
				systemMessages(data.message, 'message-' + data.mess_type);
				if (data.mess_type == 'success')
					dtItemsCommentsList.fnDraw();

			}
		});
	}

	var moderate_comments = function(){
		var checked_comments = new Array();

		$(".check-item-comment:checked").each(function() {
			checked_comments.push($(this).data('id-item-comment'));
		});

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL ?>items_comments/ajax_comments_administration_operation/moderate',
			dataType: "JSON",
			data: {checked_comments: checked_comments},
			success: function(data) {
				systemMessages(data.message, 'message-' + data.mess_type);
				if (data.mess_type == 'success') {
					dtItemsCommentsList.fnDraw();
					$('.check-all-item-comments').prop("checked", false);
				}
			}
		});
	}

	var delete_comment = function(opener){
		var $this = $(opener);
		var comment = [];
		comment[0] = $this.data('comment');

		$.ajax({
			type: 'POST',
			url: "<?php echo __SITE_URL ?>items_comments/ajax_comments_administration_operation/delete",
			dataType: "JSON",
			data: {checked_comments: comment},
			success: function(data) {
				systemMessages(data.message, 'message-' + data.mess_type);
				if (data.mess_type == 'success'){
					dtItemsCommentsList.fnDraw();
					$this.closest('li').fadeOut('normal', function(){
						$(this).remove();
					});
				}
			}
		});
	}

	var delete_comment_dt = function(opener){
		var $this = $(opener);
		var comment = [];
		comment[0] = $this.data('comment');

		$.ajax({
			type: 'POST',
			url: "<?php echo __SITE_URL ?>items_comments/ajax_comments_administration_operation/delete",
			dataType: "JSON",
			data: {checked_comments: comment},
			success: function(data) {
				systemMessages(data.message, 'message-' + data.mess_type);
				if (data.mess_type == 'success')
					dtItemsCommentsList.fnDraw();
			}
		});
	}

	var delete_comments = function(){
		var checked_comments = new Array();

		$(".check-item-comment:checked").each(function() {
			checked_comments.push($(this).data('id-item-comment'));
		});

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL ?>items_comments/ajax_comments_administration_operation/delete',
			dataType: "JSON",
			data: {checked_comments: checked_comments},
			success: function(data) {
				systemMessages(data.message, 'message-' + data.mess_type);
				if (data.mess_type == 'success') {
					dtItemsCommentsList.fnDraw();
					$('.check-all-item-comments').prop("checked", false);
				}
			}
		});
	}
</script>

<div class="row">
	<div class="col-xs-12">
		<div class="titlehdr h-30">
		    <span>Item's comments list</span>
			<div class="pull-right btns-actions-all display-n">
				<a class="ep-icon ep-icon_remove txt-red pull-right confirm-dialog" data-message="Are you sure want delete selected comments?" data-callback="delete_comments" title="Delete comments"></a>
				<a class="ep-icon ep-icon_sheild-ok txt-gren mr-5 pull-right confirm-dialog" data-message="Are you sure want moderate selected comments?" data-callback="moderate_comments" title="Moderate comments"></a>
			</div>
		</div>

		<?php tmvc::instance()->controller->view->display('admin/items_comments/filter_panel'); ?>

		<div class="wr-filter-list mt-10 clearfix"></div>

        <table id="dtItemsCommentsList" class="data table-bordered table-striped w-100pr" >
            <thead>
                <tr>
                	<th class="checkboxes"><input type="checkbox" class="check-all-item-comments pull-left">#</th>
					<th class="author">Author</th>
					<th class="item">Item</th>
					<th class="tac text_dt">Text</th>
					<th class="added">Added</th>
					<th class="actions">Actions</th>
				</tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
