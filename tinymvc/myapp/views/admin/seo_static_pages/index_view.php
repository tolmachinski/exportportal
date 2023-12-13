<script type="text/javascript">
var seoFilters; //obj for filters
var dtSeo; //obj of datatable
$(document).ready(function(){

	dtSeo = $('#dtSeo').dataTable( {
		"sDom": '<"top"lp>rt<"bottom"ip><"clear">',
		"bProcessing": true,
		"bServerSide": true,
		"bSortCellsTop": true,
		"sAjaxSource": "<?php echo __SITE_URL?>seo_static_pages/ajax_dt_administration",
		"sServerMethod": "POST",
		"iDisplayLength": 10,
		"aLengthMenu": [
		    [10, 25, 50, 100, 0],
		    [10, 25, 50, 100, 'All']
		],
		"aoColumnDefs": [
			{ "sClass": "w-50 tac", "aTargets": ['dt_id'], "mData": "dt_id" },
			{ "sClass": "w-150 tac", "aTargets": ['dt_short_key'], "mData": "dt_short_key" },
			{ "sClass": "", "aTargets": ['dt_meta_title'], "mData": "dt_meta_title" },
			{ "sClass": "", "aTargets": ['dt_meta_description'], "mData": "dt_meta_description" },
			{ "sClass": "", "aTargets": ['dt_meta_keys'], "mData": "dt_meta_keys" },
			{ "sClass": "w-100 tac", "aTargets": ['dt_actions'], "mData": "dt_actions", "bSortable": false },
		],
		"sorting" : [[0,'desc']],
		"fnServerData": function ( sSource, aoData, fnCallback ) {

			if(!seoFilters){
				seoFilters = $('.dt_filter').dtFilters('.dt_filter',{
					'container': '.wr-filter-list',
					'debug':false,
					callBack: function(){ dtSeo.fnDraw(); },
					onSet: function(callerObj, filterObj){
					},
					onDelete: function(filter){
					}
				});
			}

			aoData = aoData.concat(seoFilters.getDTFilter());
			$.ajax( {
				"dataType": 'JSON',
				"type": "POST",
				"url": sSource,
				"data": aoData,
				"success": function (data, textStatus, jqXHR) {
					if(data.mess_type == 'error' || data.mess_type == 'info')
						systemMessages(data.message, 'message-' + data.mess_type);

					fnCallback(data, textStatus, jqXHR);

				}
			});
		},
		"sPaginationType": "full_numbers",
		"fnDrawCallback": function( oSettings ) {}
	});

	remove_seo = function(obj){
		var $this = $(obj);
		var seo = $this.data('seo');

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL?>seo_static_pages/ajax_seo_operation/remove_seo',
			data: {seo: seo},
			beforeSend: function(){ },
			dataType: 'json',
			success: function(data){
				systemMessages( data.message, 'message-' + data.mess_type );

				if(data.mess_type == 'success'){
					dtSeo.fnDraw(false);
				}
			}
		});
	}
});
</script>

<div class="row">
	<div class="col-xs-12">
		<div class="titlehdr">
			<span>Seo for static pages</span>
			<a class="fancyboxValidateModalDT fancybox.ajax pull-right ep-icon ep-icon_plus-circle txt-green" data-table="dtSeo" href="<?php echo __SITE_URL;?>seo_static_pages/popup_seo/add_seo" title="Add SEO" data-title="Add SEO"></a>
		</div>

		<div class="wr-filter-list clearfix mt-10"></div>
		<table class="data table-striped w-100pr" id="dtSeo" cellspacing="0" cellpadding="0" >
			 <thead>
				 <tr>
					 <th class="dt_id">#</th>
					 <th class="dt_short_key">Short key</th>
					 <th class="dt_meta_title">Title</th>
					 <th class="dt_meta_description">Descriptions</th>
					 <th class="dt_meta_keys">Keys</th>
					 <th class="dt_actions">Actions</th>
				 </tr>
			 </thead>
			 <tbody></tbody>
		 </table>
	 </div>
</div>
