<script>
var dtSystemMess, systmessFilters;
var delete_systmess = function (opener){
	var $this = $(opener);
	$.ajax({
		type: 'POST',
		async: false,
		url: "systmess/ajax_systmessages_operation/delete",
		data: {idmess : $this.data('id')},
		dataType: 'json',
		success: function(data){
			systemMessages( data.message, 'message-' + data.mess_type );
			if(data.mess_type != 'error'){
				dtSystemMess.fnDraw(false);
			}
		}
	});
}

var export_excel = function(){
    var exportUrl = "<?php echo  __SITE_URL?>systmess/export_syst_mess";
    $('#js-download-report').attr('src', exportUrl);
}

$(document).ready(function(){

	$('.menu-level3 a').on('click', function(e){
		$parentLi = $(this).parent('li');
		if(!$parentLi.hasClass('active')){
			$parentLi.addClass('active').siblings('li').removeClass('active');
			dtSystemMess.fnDraw();
		}
		e.preventDefault();
	});

	dtSystemMess = $('#dtSystemMess').dataTable( {
		"sDom": '<"top"lpf>rt<"bottom"ip><"clear">',
		"bProcessing": true,
		"bServerSide": true,
		"sAjaxSource": "<?php echo __SITE_URL?>systmess/ajax_systmessages_administration",
		"sServerMethod": "POST",
		"aoColumnDefs": [
			{"sClass": "w-40", "aTargets": ['dt_id'], "mData": "dt_id"},
			{"sClass": "w-130", "aTargets": ['dt_type'], "mData": "dt_type"},
			{"sClass": "w-250", "aTargets": ['dt_code'], "mData": "dt_code"},
			{"sClass": "w-150", "aTargets": ['dt_module'], "mData": "dt_module"},
			{"sClass": "250", "aTargets": ['dt_title'], "mData": "dt_title"},
			{"sClass": "", "aTargets": ['dt_message'], "mData": "dt_message", "bSortable": false},
			{"sClass": "w-100 tac", "aTargets": ['dt_proofread'], "mData": "dt_proofread"},
			{"sClass": "w-100 tac", "aTargets": ['dt_changed'], "mData": "dt_changed"},
			{"sClass": "tac w-80", "aTargets": ['dt_actions'], "mData": "dt_actions", "bSortable": false}
		],
		"fnServerData": function (sSource, aoData, fnCallback) {

			if (!systmessFilters) {
				systmessFilters = $('.dt_filter').dtFilters('.dt_filter',{
					'container': '.wr-filter-list',
					'debug': false,
					callBack: function () {
						dtSystemMess.fnDraw();
					},
					onSet: function(callerObj, filterObj){
						if(filterObj.name == 'type_mess'){
                            $('.menu-level3 a[data-value="' + filterObj.value + '"]').parent('li').addClass('active').siblings().removeClass('active');
                        }
                    },
                    onDelete: function(filter){
                        if(filter.name == 'type_mess'){
                            var $li = $('a[data-name=' + filter.name + '][data-value="' + filter.default + '"]').parent('li');
                            $li.siblings('li').removeClass('active');
                            $li.addClass('active');
                        }
                    }
				});
			}

			aoData = aoData.concat(systmessFilters.getDTFilter());

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
		"fnServerParams": function ( aoData ) {
			var typeMess = 'info';
			$('.menu-level3 li').each(function(){
				if($(this).attr('class') == 'active')
					typeMess = $(this).children('a').data('value');
			});
			aoData.push( { "name": "type_mess", "value": typeMess } );
		},
		"sPaginationType": "full_numbers",
		"iDisplayLength": 25,
		"fnDrawCallback": function( oSettings ) {

		}
	});

})
</script>

<div class="row">
	<div class="pt-20 col-xs-12">
		<div class="titlehdr h-30">Notifications

        <iframe src="" class="d-none-full" id="js-download-report"></iframe>
        <a class="pull-right ep-icon ep-icon_download call-function" data-callback="export_excel" title="Export excel" data-title="Export excel"></a>
        <a class="pull-right ep-icon ep-icon_plus-circle txt-green fancyboxValidateModalDT fancybox.ajax" href="<?php echo __SITE_URL;?>systmess/ajax_systmessages_operation/add_form" data-title="Add system message" data-table="dtSystemMess"></a></div>
		<?php tmvc::instance()->controller->view->display('admin/systmess/filter_panel_view'); ?>
		<div class="wr-filter-list clearfix mt-10"></div>


		<ul class="menu-level3 mb-10 clearfix">
			<li class="active"><a class="dt_filter" data-name="type_mess" data-value="" data-value-text="All">All</a></li>
			<li><a class="dt_filter" data-name="type_mess" data-value="notice" data-value-text="Notice">Notice</a></li>
			<li><a class="dt_filter" data-name="type_mess" data-value="warning" data-value-text="Warning">Warning</a></li>
	   </ul>

		<table id="dtSystemMess" class="data table-bordered table-striped" >
			<thead>
				<tr>
					<th class="dt_id">#</th>
					<th class="dt_type">Type</th>
					<th class="dt_code">Code</th>
					<th class="dt_module">Module</th>
					<th class="dt_title">Title</th>
					<th class="dt_message">Message</th>
					<th class="dt_proofread">Proofread</th>
					<th class="dt_changed">Changed</th>
					<th class="dt_actions">Actions</th>
				</tr>
			</thead>
			<tbody></tbody>
		</table>
	</div>
</div>
