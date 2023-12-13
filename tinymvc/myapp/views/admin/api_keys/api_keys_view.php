<script>
    
var block;
var dtApiKeysList;

$(document).ready(function() {
        
        var myFilters;
        dtApiKeysList = $('#dtApiKeysList').dataTable({
            "bProcessing": true,
            "bServerSide": true,
            "bFilter": false,
            "sAjaxSource": "<?php echo __SITE_URL; ?>api_keys/ajax_list_dt/",
		    "sServerMethod": "POST",
            "aoColumnDefs": [
                {"sClass": "w-50 tac vam", "aTargets": ['dt_id_key'],  "mData": "dt_id_key"},
                {"sClass": "w-400 tac", "aTargets": ['dt_api_key'], "mData": "dt_api_key",  "bSortable": false},
                {"sClass": "tac", "aTargets": ['dt_domain'], "mData": "dt_domain"},
                {"sClass": "w-400 tac", "aTargets": ['dt_title_client'], "mData": "dt_title_client",  "bSortable": false},
                {"sClass": "w-300 tac vam", "aTargets": ['dt_registered'], "mData": "dt_registered"},
                {"sClass": "w-60 tac", "aTargets": ['dt_actions'], "mData": "dt_actions", "bSortable": false}
            ],
            "sPaginationType": "full_numbers",
            "fnServerData": function(sSource, aoData, fnCallback) {
                if(!myFilters){
                    myFilters = $('.dt_filter').dtFilters('.dt_filter',{
                        'container': '.wr-filter-list',
                        callBack: function(){
                            dtApiKeysList.fnDraw();
                        },
                        onSet: function(callerObj, filterObj){
                        }
                    });
                }

                aoData = aoData.concat(myFilters.getDTFilter());

                $.ajax( {
                    "dataType": 'json',
                    "type": "POST",
                    "url": sSource,
                    "data": aoData,
                    "success": function (data, textStatus, jqXHR) {
                    if(data.mess_type == 'error')
                        systemMessages(data.message, 'message-' + data.mess_type);

                    fnCallback(data, textStatus, jqXHR);
                    },
                } );
            },
            "fnDrawCallback": function(oSettings) {

            }
        });
        
        delete_api = function(obj){
             var $this = $(obj);
             var id = $this.data('id');

             $.ajax({
                type: 'POST',
                url: "<?php echo __SITE_URL ?>api_keys/ajax_api_keys_operation/delete_api_key",
                data: {id_key: id},
                dataType: "JSON",
                success: function(resp) {
                   if(resp.mess_type == 'success'){
                        dtApiKeysList.fnDraw();
                   }
                   systemMessages(resp.message, 'message-' + resp.mess_type);
                }
             });
        }
                
        change_visib = function(obj){
             var $this = $(obj);
             var id = $this.data("id");
             var state = $this.hasClass('ep-icon_visible') ? 0 : 1;

             $.ajax({
                type: 'POST',
                url: "<?php echo __SITE_URL ?>api_keys/ajax_api_keys_operation/change_state_api_key",
                data: {
                    id_key: id,
                    state: state
                },
                dataType: "JSON",
                success: function(resp) {
                   if(resp.mess_type == 'success'){
                        dtApiKeysList.fnDraw();
                   }
                   systemMessages(resp.message, 'message-' + resp.mess_type);
                }
             });
        }
        
        
        moderate_api = function(obj){
             var $this = $(obj);
             var id = $this.data("id");

             $.ajax({
                type: 'POST',
                url: "<?php echo __SITE_URL ?>api_keys/ajax_api_keys_operation/moderate_api_key",
                data: { id_key: id },
                dataType: "JSON",
                success: function(resp) {
                   if(resp.mess_type == 'success'){
                        dtApiKeysList.fnDraw();
                   }
                   systemMessages(resp.message, 'message-' + resp.mess_type);
                }
             });
        }

        
    $('body').on('click', 'a[rel=api_keys_details]',function() {
	    var $aTd = $(this);
		var nTr = $aTd.parents('tr')[0];
	    if (dtApiKeysList.fnIsOpen(nTr))
			dtApiKeysList.fnClose(nTr);
	    else
			dtApiKeysList.fnOpen(nTr, fnFormatDetails(nTr), 'details');

		$aTd.toggleClass('ep-icon_plus ep-icon_minus');
	});

    function fnFormatDetails(nTr){
		var aData = dtApiKeysList.fnGetData(nTr);
		var sOut = '<div class="dt-details"><table class="dt-details__table">';
            sOut += '<tr><td class="w-100">Description:</td><td>' + aData['dt_desrcription'] +'</td></tr>';
			sOut += '</table> </div>';
		return sOut;
    }
});
</script>

<div class="row">
	<div class="col-xs-12">
		<div class="titlehdr h-30">
		    <span>API keys list</span>
		    <div class="pull-right">
		        <a class="ep-icon ep-icon_plus-circle txt-green fancyboxValidateModalDT fancybox.ajax" data-title="Add api key" data-table="dtApiKeysList" title="Add api key" href="<?php echo __SITE_URL;?>api_keys/popups_forms_api_key/add"></a>
            </div>
		</div>
		<?php tmvc::instance()->controller->view->display('admin/api_keys/api_keys_filter_panel_view'); ?>

		<div class="mt-10 wr-filter-list clearfix"></div>

		<table id="dtApiKeysList" class="data table-striped table-bordered w-100pr" >
			<thead>
				<tr>
				    <th class="dt_id_key">#</th>
				    <th class="dt_api_key first item">Key</th>
				    <th class="dt_domain">Domain</th>
				    <th class="dt_title_client fullname author">Title</th>
				    <th class="dt_registered rev_title tac">Registered</th>
				    <th class="dt_actions rev_text tac">Actions</th> 
				</tr>
			</thead>
			<tbody></tbody>	
		</table>
	</div>
</div>


