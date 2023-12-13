<script type="text/javascript">
var importFilters; //obj for filters
var dtImport; //obj of datatable
$(document).ready(function(){
	dtImport = $('#dtImport').dataTable( {
		"sDom": '<"top"lp>rt<"bottom"ip><"clear">',
		"bProcessing": true,
		"bServerSide": true,
		"bSortCellsTop": true,
		"sAjaxSource": "<?php echo __SITE_URL?>admin_import/ajax_admin_import_dt",
		"sServerMethod": "POST",
		"iDisplayLength": 10,
		"aLengthMenu": [
		    [10, 25, 50, 100, 0],
		    [10, 25, 50, 100, 'All']
		],
		"aoColumnDefs": [
			{ "sClass": "w-30 vam tac", "aTargets": ["dt_check"], "mData": "dt_check", "bSortable": false },
			{ "sClass": "w-100 vam tac", "aTargets": ["dt_id"], "mData": "dt_id"},
			{ "sClass": "mnw-200 vam tac", "aTargets": ["dt_type"], "mData": "dt_type"},
			{ "sClass": "w-200 vam tac", "aTargets": ["dt_date"], "mData": "dt_date"}, 
			{ "sClass": "w-200 vam tac", "aTargets": ["dt_status"], "mData": "dt_status"},
			{ "sClass": "w-60 vam tac", "aTargets": ["dt_actions"], "mData": "dt_actions", "bSortable": false }
			
		],
		"sorting" : [[0,'desc']],
		"fnServerData": function ( sSource, aoData, fnCallback ) {
			
			if(!importFilters){
				importFilters = $('.dt_filter').dtFilters('.dt_filter',{
					'container': '.wr-filter-list',
					'debug':false,
					callBack: function(){ dtImport.fnDraw(); },
					onSet: function(callerObj, filterObj){
						if(filterObj.name == 'status'){
							$('.menu-level3').find('a[data-value="'+filterObj.value+'"]').parent('li').addClass('active').siblings('li').removeClass('active');
						}
                        
                        if(filterObj.name == 'start'){
                            $("#finish_date").datepicker("option","minDate", $("#start_date").datepicker("getDate"));
                        }
                        if(filterObj.name == 'finish'){
                            $("#start_date").datepicker("option","maxDate", $("#finish_date").datepicker("getDate"));
                        }
					},
					onDelete: function(filter){
						if(filter.name == 'status'){
							$('.menu-level3 a[data-value="' + filter.value + '"]').parent('li')
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

			aoData = aoData.concat(importFilters.getDTFilter());
			$.ajax( {
				"dataType": 'JSON',
				"type": "POST",
				"url": sSource,
				"data": aoData,
				"success": function (data, textStatus, jqXHR) {
					if(data.mess_type == 'error')
						systemMessages(data.message, 'message-' + data.mess_type);
					if(data.mess_type == 'info')
						systemMessages(data.message, 'message-' + data.mess_type);

					fnCallback(data, textStatus, jqXHR);
				}
			});
		},
		"sPaginationType": "full_numbers",
		"fnDrawCallback": function( oSettings ) {

		}
	})
    .on('page', function(){
        $('.check-all-rows').prop("checked", false);
    });
	
	$('body').on('click', 'a[rel=import_details]',function() {
	    var $aTd = $(this);
		var nTr = $aTd.parents('tr')[0];
	    if (dtImport.fnIsOpen(nTr)){
			dtImport.fnClose(nTr);
	    }else{
			dtImport.fnOpen(nTr, fnFormatDetails(nTr), 'details');
	    }

		$aTd.toggleClass('ep-icon_plus ep-icon_minus');
	});
    
    $('.check-all-rows').on('click', function() {
	    var checked = $(this).is(":checked") ? 1 : 0;
        $('.check-import-data').prop("checked", checked);
	});
    
    var replace_keys = [];
    replace_keys['user_fname'] = "First name";
    replace_keys['user_lname'] = "Last name";
    replace_keys['group'] = "Group";
    replace_keys['group_name'] = "Group name";
    replace_keys['email'] = "Email";
    replace_keys['company_name'] = "Company name";
    replace_keys['country'] = "Country";
    replace_keys['state'] = "State";
    replace_keys['city'] = "City";
    replace_keys['id_country'] = "Country ID";
    replace_keys['id_state'] = "State ID";
    replace_keys['id_city'] = "City ID";
    replace_keys['address'] = "Address";
    replace_keys['zip'] = "ZIP";
    replace_keys['phone'] = "Phone";
    replace_keys['fax'] = "Fax";
    replace_keys['logo'] = "Logo";
    replace_keys['image_logo'] = "Logo image";
    replace_keys['images'] = "Images";
    replace_keys['shipper_name'] = "Freight Forwarder name";
    function fnFormatDetails(nTr){
		var aData = dtImport.fnGetData(nTr);
		var import_data = jQuery.parseJSON(aData['dt_detail']);
		var sOut = '<div class="dt-details"><table class="dt-details__table">';
        $.each(import_data, function( index, value ){
            if(index === 'images'){
                sOut += '<tr><td class="w-100"><strong>'+replace_keys[index]+'</strong></td><td>';
                $.each(value, function(image_index, image){
                    sOut += '<div class="w-200 h-200 vam display-tc mr-5 mb-5">';
                    sOut += '<img class="w-100pr" src="'+image.path+image.name+'" alt="img">';
                    sOut += '</div>';
                });
                sOut += '</td></tr>';
            } else if(index === 'image_logo'){
                sOut += '<tr><td class="w-100"><strong>'+replace_keys[index]+'</strong></td><td>';
                sOut += '<div class="w-200 h-200 vam display-tc mr-5 mb-5">';
                sOut += '<img class="w-100pr" src="'+value.path+value.name+'" alt="img">';
                sOut += '</div>';
                sOut += '</td></tr>';
            } else{
                sOut += '<tr><td class="w-100"><strong>'+replace_keys[index]+'</strong></td><td>' + value + '</td></tr>';                
            }
        });
        sOut += '</table></div>';
		return sOut;
    }
});
    var import_rows = function(btn){
        var $this = $(btn);
        var type = $this.data('import_type');
        var imports_list = [];
        if(type == 'single'){
            imports_list.push($this.data('import'));
        } else{
            $.each($(".check-import-data:checked"), function() {
                imports_list.push($(this).data('import'));
            });
        }
        
        $.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL ?>admin_import/ajax_operations/import_rows',
            dataType: "JSON",
            data: {imports_list:imports_list},
            success: function(data) {
                systemMessages(data.message, 'message-' + data.mess_type);
                if (data.mess_type == 'success') {
                    $('.check-all-rows, .check-import-data').prop("checked", false);
                    dtImport.fnDraw(false);
                }
            }
        });
    }
</script>
<div class="row">
    <div class="col-xs-12">
		<?php tmvc::instance()->controller->view->display('admin/import/filter_panel_view')?>
        <div class="titlehdr h-30">
		    <span>Import</span>
		    <a class="fancyboxValidateModal fancybox.iframe pull-right ep-icon ep-icon_plus-circle txt-green" href="#" data-title="Import companies"></a>
            <a class="ep-icon ep-icon_ok-circle txt-green pull-right confirm-dialog mr-10" data-callback="import_rows" data-import_type="multiple" data-message="Are you sure want to register all selected data?" title="Register all selected data"></a>
        </div>
		<div class="wr-filter-list clearfix mt-10 "></div>
        <ul class="menu-level3 mb-10 clearfix">
            <li class="active"><a class="dt_filter" data-name="status" data-title="Status" data-value="" data-value-text="All">All</a></li>
            <li><a class="dt_filter" data-name="status" data-value="new" data-value-text="New">New</a></li>
            <li><a class="dt_filter" data-name="status" data-value="updated" data-value-text="Updated">Updated</a></li>
            <li><a class="dt_filter" data-name="status" data-value="ready" data-value-text="Ready to notify">Ready to notify</a></li>
        </ul>
        <table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr" id="dtImport">
            <thead>
                <tr>
                    <th class="dt_check"><input type="checkbox" class="check-all-rows mt-0"></th>
                    <th class="dt_id">#</th>
                    <th class="tac dt_type">Import data type</th>
                    <th class="tac dt_date">Import date</th>
                    <th class="tac dt_status">Status</th>
                    <th class="dt_actions">Actions</th>
                </tr>
            </thead>
            <tbody class="tabMessage" id="pageall">
            </tbody>
        </table>
    </div>
</div>
