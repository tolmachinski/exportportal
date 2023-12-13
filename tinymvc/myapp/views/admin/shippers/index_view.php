<script type="text/javascript">
    var blogsFilters, cur_lvl_cats;
	var dtShippers;

	var $selectCity;
	var selectState;

$(document).ready(function () {

	$('body').on('change', "select#states", function(){
		selectState = this.value;
		$selectCity.empty().trigger("change").prop("disabled", true);

		if(selectState != '' || selectState != 0){
			var select_text = translate_js({plug:'general_i18n', text:'form_placeholder_select2_city'});
		} else{
			var select_text = translate_js({plug:'general_i18n', text:'form_placeholder_select2_state_first'});
			$selectCity.prop("disabled", true);
		}
		$selectCity.siblings('.select2').find('.select2-selection__placeholder').text(select_text);
	});

	$('body').on('change', "#country", function(){
		selectCountry($(this), 'select#states');
		selectState = 0;
		$selectCity.empty().trigger("change").prop("disabled", true);
	});

	change_visibility = function (obj) {
		var $this = $(obj);
		var id = $this.data('id');

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL ?>shippers/ajax_shippers_operation/change_visibility',
			data: {id_shipper: id},
			beforeSend: function () {
				showLoader(dtShippers);
			},
			dataType: 'json',
			success: function (data) {
				hideLoader(dtShippers);
				systemMessages(data.message, 'message-' + data.mess_type);
				if (data.mess_type == 'success') {
					dtShippers.fnDraw();
				}
			}
		});
	}

	dtShippers = $('#dtShippers').dataTable({
		"sDom": '<"top"lp>rt<"bottom"ip><"clear">',
		"bProcessing": true,
		"bServerSide": true,
		"sAjaxSource": "<?php echo __SITE_URL ?>shippers/ajax_shippers_dt/<?php echo $upload_folder;?>",
		"sServerMethod": "POST",
		"aoColumnDefs": [
			{"sClass": "w-40 tac vam", "aTargets": ['dt_id'], "mData": "dt_id"},
			{"sClass": "w-95 tac vam", "aTargets": ['dt_logo'], "mData": "dt_logo", "bSortable": false},
			{"sClass": "tal vam", "aTargets": ['dt_co_name'], "mData": "dt_co_name"},
			{"sClass": "tal vam", "aTargets": ['dt_user'], "mData": "dt_user"},
			{"sClass": "w-150 tac vam", "aTargets": ['dt_phone'], "mData": "dt_phone", "bSortable": false},
			{"sClass": "w-150 tac vam", "aTargets": ['dt_fax'], "mData": "dt_fax", "bSortable": false},
			{"sClass": "w-150 tac vam", "aTargets": ['dt_registered'], "mData": "dt_registered", "bSortable": true},
			{"sClass": "w-200 tac vam", "aTargets": ['dt_email'], "mData": "dt_email", "bSortable": false},
			{"sClass": "tac w-80 vam", "aTargets": ['dt_actions'], "mData": "dt_actions", "bSortable": false}
		],
		"sorting": [[0, "desc"]],
		"fnServerData": function (sSource, aoData, fnCallback) {

			if (!blogsFilters) {
				blogsFilters = $('.dt_filter').dtFilters('.dt_filter',{
					'container': '.wr-filter-list',
					'debug': false,
					callBack: function () {
						dtShippers.fnDraw();
					},
					onSet: function (callerObj, filterObj) {

					},
					onDelete: function (filter) {

					}
				});
			}

			aoData = aoData.concat(blogsFilters.getDTFilter());
			$.ajax({
				"dataType": 'JSON',
				"type": "POST",
				"url": sSource,
				"data": aoData,
				"success": function (data, textStatus, jqXHR) {
					if (data.mess_type == 'error' || data.mess_type == 'info')
						systemMessages(data.message, 'message-' + data.mess_type);

					fnCallback(data, textStatus, jqXHR);

				}
			});
		},
		"sPaginationType": "full_numbers",
		"fnDrawCallback": function (oSettings) {

			var keywordsSearch = $('.filter-admin-panel').find('input[name=keywords]').val();
			if (keywordsSearch !== '')
				$("#dtShippers tbody *").highlight(keywordsSearch, "highlight");
		}
	});

	$('body').on('click', '#industry-list .ep-icon_arrows-right', function(){
		//this btn send ALL
		var $thisBtn = $(this);
		//industry li ALL
		var $thisLi = $thisBtn.closest('li');
		//remove this btn li ALL
		$thisBtn.addClass('display-n_i');
		//hide industry li ALL
		$thisLi.fadeOut();
		//append industry li SELECTED
		$('#industry-list-selected').append('<li>'+$thisLi.children('span').text()+' <input type="hidden" value="'+$thisLi.data('value')+'" name="industries[]"> <i class="ep-icon ep-icon_remove"></i></li>');
		return false;
	});

	$('body').on('click', '#industry-list-selected .ep-icon_remove', function(){
		//this btn remove SELECTED
		var $thisBtn = $(this);
		//industry li SELECTED
		var $thisLi = $thisBtn.closest('li');
		//industry ID SELECTED
		var thisId = $thisLi.find('input').val();
		//show industry li ALL
		$('#industry-list').find('li[data-value="'+thisId+'"]').fadeIn().end()
			.find('.ep-icon_arrows-right').removeClass('display-n_i');
		//remove industry li SELECTED
		$thisLi.remove();

		return false;
    });

    $(globalThis).on('company:edit-name', function (e) {
        dtShippers.fnDraw(false);
    });
});
</script>

<div class="row">
    <div class="col-xs-12">
        <div class="titlehdr h-30">
        	<span>Freight Forwarders</span>
<!--        	<a class="pull-right ep-icon ep-icon_plus-circle txt-green fancybox.ajax fancyboxValidateModalDT" data-title="Add freight forwarder" href="shippers/popup_forms/add_shipper/<?php echo $upload_folder;?>" data-table="dtShippers"></a>-->
        </div>

        <?php tmvc::instance()->controller->view->display('admin/shippers/filter_panel_view'); ?>
        <div class="wr-filter-list clearfix mt-10"></div>

        <table class="data table-striped table-bordered w-100pr" id="dtShippers" cellspacing="0" cellpadding="0" >
            <thead>
                <tr>
                    <th class="dt_id">#</th>
                    <th class="dt_logo">Logo</th>
                    <th class="dt_co_name">Co Name</th>
                    <th class="dt_user">User Name</th>
                    <th class="dt_email">Email</th>
                    <th class="dt_phone">Phone</th>
                    <th class="dt_fax">Fax</th>
                    <th class="dt_registered">Registered</th>
                    <th class="dt_actions">Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
