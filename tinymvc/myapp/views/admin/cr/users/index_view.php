<script type="text/javascript">
var dtFilter;
var dtTable;
var $selectCity;
var selectState;
var $selectCcodePhone, $selectCcodeFax;

$(document).ready(function(){

	$('body').on('change', "select#states", function(){
		selectState = this.value;
		$selectCity.empty().trigger("change").prop("disabled", false);

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

	$('.check-all-users').on('click', function() {
		var value = $(this).is(":checked") ? 1 : 0;
		if (value) {
			$('.check-user').prop("checked", true);
			$('.btns-actions-all').show();
		}
		else {
			$('.check-user').prop("checked", false);
			$('.btns-actions-all').hide();
		}
	});

	$('body').on('click', '.check-user', function() {
		if ($(this).prop("checked")) {
			$('.btns-actions-all').show();
		} else {
			var hideBlock = true;
			$('.check-user').each(function() {
				if ($(this).prop("checked")) {
					hideBlock = false;
					return false;
				}
			})
			if (hideBlock)
				$('.btns-actions-all').hide();
		}
	});

	dtTable = $('#dtTable').dataTable( {
		"sDom": '<"top"lp>rt<"bottom"ip><"clear">',
		"bProcessing": true,
		"bServerSide": true,
		"bSortCellsTop": true,
		"sAjaxSource": "<?php echo __SITE_URL?>cr_users/ajax_operations/list_dt",
		"sServerMethod": "POST",
		"iDisplayLength": 10,
		"aLengthMenu": [
			[10, 25, 50, 100, 0],
			[10, 25, 50, 100, 'All']
		],
		"aoColumnDefs": [
			{ "sClass": "vam w-30 tac", "aTargets": ['dt_idu'], "mData": "dt_idu", "bSortable": false},
			{ "sClass": "w-50 tac vam", "aTargets": ["dt_photo"], "mData": "dt_photo", "bSortable": false },
			{ "sClass": "w-100 vam", "aTargets": ["dt_fullname"], "mData": "dt_fullname" },
			{ "sClass": "vam w-150", "aTargets": ["dt_email"], "mData": "dt_email" },
			{ "sClass": "w-70 tac vam", "aTargets": ["dt_country"], "mData": "dt_country" , "bSortable": false},
			{ "sClass": "w-70 vam", "aTargets": ["dt_gr_name"], "mData": "dt_gr_name" , "bSortable": false},
			{ "sClass": "w-70 tac vam", "aTargets": ["dt_ip"], "mData": "dt_ip" , "bSortable": false},
			{ "sClass": "w-80 tac vam", "aTargets": ["dt_registered"], "mData": "dt_registered" },
			{ "sClass": "w-90 tac vam", "aTargets": ["dt_activity"], "mData": "dt_activity" },
			{ "sClass": "w-90 tac vam", "aTargets": ["dt_status"], "mData": "dt_status", "bSortable": false },
			{ "sClass": "w-70 tac vam", "aTargets": ["dt_records"], "mData": "dt_records", "bSortable": false },
			{ "sClass": "w-50 tar vam", "aTargets": ["dt_actions"], "mData": "dt_actions", "bSortable": false },

		],
		"sorting" : [[9,'desc']],
		"fnServerData": function ( sSource, aoData, fnCallback ) {
			if(!dtFilter){
				dtFilter = $('.dt_filter').dtFilters('.dt_filter',{
					'container': '.wr-filter-list',
					'debug'	: false,
					'autoApply': false,
					callBack: function(filter){
						dtTable.fnDraw();
					},
					onSet: function(callerObj, filterObj){
						if(filterObj.name == 'group'){
							$('.menu-level3 a[data-value="' + filterObj.value + '"]').parent('li')
								.addClass('active').siblings().removeClass('active');
						}

						if (filterObj.name == 'reg_date_from') {
							$('input[name="reg_date_to"]').datepicker("option", "minDate", $('input[name="reg_date_from"]').datepicker("getDate"));
						}

						if (filterObj.name == 'reg_date_to') {
							$('input[name="reg_date_from"]').datepicker("option", "maxDate", $('input[name="reg_date_to"]').datepicker("getDate"));
						}

						if (filterObj.name == 'activity_date_from') {
							$('input[name="activity_date_to"]').datepicker("option", "minDate", $('input[name="activity_date_from"]').datepicker("getDate"));
						}

						if (filterObj.name == 'activity_date_to') {
							$('input[name="activity_date_from"]').datepicker("option", "maxDate", $('input[name="activity_date_to"]').datepicker("getDate"));
						}
					},
					onDelete: function(filterObj){
						if(filterObj.name == 'group'){
							$('a[data-value="' + filterObj.default + '"]').parent('li')
								.addClass('active').siblings().removeClass('active');
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

			aoData = aoData.concat(dtFilter.getDTFilter());

			$.ajax( {
				"dataType": 'json',
				"type": "POST",
				"url": sSource,
				"data": aoData,
				"success": function (data, textStatus, jqXHR) {
					if(data.mess_type == 'error')
						systemMessages(data.message, 'message-' + data.mess_type);

					fnCallback(data, textStatus, jqXHR);
					$('.menu-level3 li > a[data-group="all"] span.users_counter').text(data.iTotalRecords);
					$.each(data.groups_users_count, function(id_group, group_obj){
						$('.menu-level3 li > a[data-group="'+id_group+'"] span.users_counter').text(group_obj.counter);
					});
				}
			} );
		},
		"sPaginationType": "full_numbers",
		"fnDrawCallback": function( oSettings ) {}
	});
});

	function fnFormatDetails(nTr){
		var aData = dtTable.fnGetData(nTr);
		var sOut = '<div class="dt-details"><table class="dt-details__table">';
			sOut += aData['dt_detail'];
			sOut += '</table> </div>';
		return sOut;
	}

	var toggle_detail = function(btn){
		var $this = $(btn);
		var $tr = $this.parents('tr')[0];

		if (dtTable.fnIsOpen($tr)){
			dtTable.fnClose($tr);
		} else{
			dtTable.fnOpen($tr, fnFormatDetails($tr), 'details');
		}

		$this.toggleClass('ep-icon_plus ep-icon_minus');
	}

	var delete_user = function(obj){
		var $this = $(obj);
		var user = $this.data('user');
		$.ajax({
			url: '<?php echo __SITE_URL;?>cr_users/ajax_operations/delete_user',
			type: 'POST',
			data:  {user:user},
			dataType: 'json',
			success: function(resp){
				systemMessages(resp.message, 'message-' + resp.mess_type );
                if(resp.mess_type == 'success'){
					dtTable.fnDraw(false);
				}
			}
		});
	}

	var explore_user = function(obj){
		var $this = $(obj);
		var user = $this.data('user');
		$.ajax({
			url: '<?php echo __SITE_URL;?>login/explore_user',
			type: 'POST',
			data:  {user:user},
			dataType: 'json',
			success: function(resp){
                if(resp.mess_type == 'success'){
					window.location.href = resp.redirect;
				} else{
					systemMessages(resp.message, 'message-' + resp.mess_type );
				}
			}
		});
	}
</script>
<div class="row">
	<div class="col-xs-12">
		<?php tmvc::instance()->controller->view->display('admin/cr/users/filters_view')?>
		<div class="titlehdr h-30">
			<span>Countries representatives</span>
			<a class="fancyboxValidateModalDT fancybox.ajax pull-right ep-icon ep-icon_plus-circle txt-green" data-table="dtTable" title="Add new member" href="<?php echo __SITE_URL;?>cr_users/popup_forms/add_user" data-title="Add new member"></a>
		</div>
		<div class="wr-filter-list clearfix mt-10"></div>

		<ul class="menu-level3 mb-10 clearfix">
			<li class="active">
				<a class="dt_filter" data-group="all" data-title="Group" data-name="group" data-value="" data-value-text="All">All (<span class="users_counter">0</span>)</a>
			</li>
			<?php foreach($groups as $gr){ ?>
				<li class="<?php echo equals($gr['idgroup'], $group, 'active')?>">
					<a class="dt_filter" data-group="<?php echo $gr['idgroup']?>" data-name="group" data-value="<?php echo $gr['idgroup']?>" data-value-text="<?php echo $gr['gr_name']?>"><?php echo $gr['gr_name']?> (<span class="users_counter"><?php echo $gr['u_counter']?></span>)</a>
				</li>
			<?php } ?>
		</ul>

		<table class="data table-bordered table-striped w-100pr" id="dtTable">
			<thead>
				<tr>
					<th class="dt_idu"><input type="checkbox" class="check-all-users mt-1"></th>
					<th class="dt_photo">Avatar</th>
					<th class="dt_fullname">Full name</th>
					<th class="dt_email">Email</th>
					<th class="dt_gr_name">Group</th>
					<th class="dt_country"><span class="ep-icon ep-icon_globe fs-22 m-0"></span></th>
					<th class="dt_ip">IP</th>
					<th class="dt_registered">Registered</th>
					<th class="dt_activity">Last active</th>
					<th class="dt_status">Status</th>
					<th class="dt_records">Records</th>
					<th class="dt_actions">Actions</th>
				</tr>
			</thead>
			<tbody class="tabMessage" id="pageall">
			</tbody>
		</table>
	</div>
</div>
