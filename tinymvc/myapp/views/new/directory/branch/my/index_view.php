<script src="https://maps.googleapis.com/maps/api/js?v=3&key=AIzaSyAko8g1LP9autKH12-8d1VkUZn3UaIZB8E"></script>
<?php tmvc::instance()->controller->view->display('new/file_upload_scripts'); ?>

<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/jquery-datatables-1-10-12/jquery.dataTables.min.js');?>"></script>
<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/jquery-dtfilters/jquery.dtFilters.js');?>"></script>

<script>
var itemsFilters;
var dtDirectoriesList;

filters_has_datepicker = true;

$(document).ready(function() {
	var fnDrawFirst = 0;
	dataT = dtDirectoriesList = $('#dt-itemsquestions-list').dataTable({
		"bProcessing": true,
		"bServerSide": true,
		"sAjaxSource": "<?php echo __SITE_URL; ?>company_branches/ajax_list_branches_dt",
		"aoColumnDefs": [
			{"sClass": "mnw-200 vam", "aTargets": ['dt_company'], "mData": "dt_company"},
			{"sClass": "w-150 vam", "aTargets": ['dt_country'], "mData": "dt_country"},
			{"sClass": "w-100 vam", "aTargets": ['dt_registered'], "mData": "dt_registered"},
			{"sClass": "w-40 tac vam dt-actions", "aTargets": ['dt_actions'], "mData": "dt_actions", "bSortable": false}
		],
		"sPaginationType": "full_numbers",
		"sDom": '<"top"i>rt<"bottom"lp><"clear">',
		"sorting": [[0, "desc"]],
		"fnServerData": function(sSource, aoData, fnCallback) {
			if(!itemsFilters){
				//view template initDtFilter in scripts_new
				itemsFilters = initDtFilter();
			}

			aoData = aoData.concat(itemsFilters.getDTFilter());
			$.ajax({
				"dataType": 'json',
				"type": "POST",
				"url": sSource,
				"data": aoData,
				"success": function(data, textStatus, jqXHR) {
					if (data.mess_type == 'error')
						systemMessages(data.message, data.mess_type);

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

	$(".datepicker-init").datepicker({
        beforeShow: function (input, instance) {
            $('#ui-datepicker-div').addClass('dtfilter-ui-datepicker');
        },
    });
});

<?php if(have_right('manage_branches')){?>
var change_visibility = function(obj){
	var $this = $(obj);
	var company = $this.data('company');
	var state = $this.data('state');

	$.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL?>company_branches/ajax_branch_operation/change_visibility',
		data: { company : company, state: state},
		beforeSend: function(){  },
		dataType: 'json',
		success: function(data){
			systemMessages( data.message, data.mess_type );

			if(data.mess_type == 'success'){
				dtDirectoriesList.fnDraw();
			}
		}
	});
}
var delete_branch = function(obj){
	var $this = $(obj);
	var company = $this.data('company');

	$.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL?>company_branches/ajax_branch_operation/delete_branch',
		data: { company : company },
		beforeSend: function(){  },
		dataType: 'json',
		success: function(data){
			systemMessages( data.message, data.mess_type );

			if(data.mess_type == 'success'){
				dtDirectoriesList.fnDraw();
			}
		}
	});
}
<?php }?>

var callbackReplaceCropImages = function(){

	if($('#js-form-branch input[name="branch"]').length){
		dtDirectoriesList.fnDraw();
	}

}

var companyInformationFancybox = function($this){
	var nTr = $this.parents('tr')[0];

	var aData = dtDirectoriesList.fnGetData(nTr);
	var sOut = '<div class="flex-card">\
					<div class="flex-card__fixed w-150 image-card">\
						<span class="link">'
							+ aData['logo']
						+ '</span>\
					</div>\
					<div class="flex-card__float">'
						+ aData['description']
					+ '</div>\
				</div>\
				<ul class="pt-10 pb-10">\
					<li class=""><strong>Email: </strong>' + aData['email'] + '</li>\
					<li class=""><strong>Phone: </strong>' + aData['phone'] + '</li>\
					<li class=""><strong>Fax: </strong>' + aData['fax'] + '</li>\
					<li class=""><strong>Address: </strong>' + aData['address'] + '</li>\
					<li class=""><strong>Zip: </strong>' + aData['zip'] + '</li>\
					<li class=""><strong>Longitude: </strong>' + aData['longitude'] + '</li>\
					<li class=""><strong>Latitude: </strong>' + aData['latitude'] + '</li>\
					<li class=""><strong>Number of employees: </strong>' + aData['employees'] + '</li>\
					<li class=""><strong>Revenue of the company: </strong>' + aData['revenue'] + '</li>\
				</ul>\
				<h3 class="ttl-h2">Social networks</h3>\
				<div class="pt-10">' + aData['social'] + '</div>';

	$.fancybox.open({
		title: 'Company information',
		content: sOut
	},{
		width		: fancyW,
		height		: 'auto',
		maxWidth	: 700,
		autoSize	: false,
		loop : false,
		helpers : {
			title: {
				type: 'inside',
				position: 'top'
			},
			overlay: {
				locked: true
			}
		},
		modal: true,
		closeBtn : true,
		padding : fancyP,
		closeBtnWrapper: '.fancybox-skin .fancybox-title',
		lang : __site_lang,
		i18n : translate_js_one({plug:'fancybox'}),
		beforeShow : function() {

		},
		beforeLoad : function() {
			this.width = fancyW;
			this.padding = [fancyP,fancyP,fancyP,fancyP];
		},
		onUpdate : function() {}
	});
}
</script>

<?php tmvc::instance()->controller->view->display('new/filter_panel_main_view', array('filter_panel' => 'new/directory/branch/my/filter_view')); ?>

<div class="container-center dashboard-container">

<div class="dashboard-line">
	<h1 class="dashboard-line__ttl">Branches list</h1>

	<div class="dashboard-line__actions">
		<?php if(have_right('manage_branches')){?>
		<a class="btn btn-primary pl-20 pr-20 fancyboxValidateModal fancybox.ajax" href="<?php echo __SITE_URL;?>company_branches/popup_forms/add_branch" data-title="Add branch" title="Add branch">
			<i class="ep-icon ep-icon_plus-circle fs-20"></i>
			<span class="dn-m-min">Add branch</span>
		</a>
		<?php }?>

		<!-- <a class="btn btn-light fancybox fancybox.ajax" href="<?php echo __SITE_URL;?>user_guide/popup_forms/show_doc/79" title="View Branches list documentation" data-title="View Branches list documentation" target="_blank">User guide</a> -->

		<a class="btn btn-dark fancybox btn-filter" href="#dtfilter-hidden" data-mw="740" data-title="Filter panel">
			<i class="ep-icon ep-icon_filter"></i> Filter
		</a>
	</div>
</div>

<div class="info-alert-b">
	<i class="ep-icon ep-icon_info-stroke"></i>
	<span><?php echo translate('company_branches_my_description'); ?></span>
</div>

<table class="main-data-table" id="dt-itemsquestions-list">
	<thead>
		<tr>
			<th class="dt_company">Company</th>
			<th class="dt_country">Location</th>
			<th class="dt_registered">Registered</th>
			<th class="dt_actions"></th>
		</tr>
	</thead>
	<tbody class="tabMessage"></tbody>
</table>
</div>
