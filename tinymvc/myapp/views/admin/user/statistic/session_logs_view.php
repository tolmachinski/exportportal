<div class="relative-b">
    <div class="wr-form-content w-900 mh-700 mt-10">
        <table class="data table-bordered table-striped w-100pr" id="dtUserSessionLogs">
            <thead>
                <tr>
                    <th class="dt_date">Date</th>
                    <th class="dt_message">Note</th>
                </tr>
            </thead>
            <tbody class="tabMessage" id="pageall">
            </tbody>
        </table>
	</div>
	<div class="wr-form-btns clearfix">
		<button class="pull-right btn btn-primary call-function" data-callback="closeFancyBox" type="button">Close</button>
	</div>
</div>
<style>
    .wr-form-content .dataTables_info{
        padding-top:0px;
    }
</style>

<script>
    var dtUserSessionLogs = null;
    var usersSessionLogsFilters = null;
    $(function(){
        dtUserSessionLogs = $('#dtUserSessionLogs').dataTable( {
            sDom: '<"top"ip>rt<"clear">',
            bProcessing: true,
            bServerSide: true,
            bSortCellsTop: true,
            sAjaxSource: "<?php echo __SITE_URL?>session_logs/ajax_operations/by_user_dt/<?php echo $user_info['idu'];?>",
            sServerMethod: "POST",
            iDisplayLength: 20,
            aoColumnDefs: [
                { 
                    sClass:     "vam w-150 tac", 
                    aTargets:   ['dt_date'], 
                    mData:      "dt_date",
                    bSortable:  true
                },
                { 
                    sClass:     "vam", 
                    aTargets:   ["dt_message"], 
                    mData:      "dt_message", 
                    bSortable:  false 
                }
            ],
            sorting : [[0,'desc']],
            fnServerData: function ( sSource, aoData, fnCallback ) {
                $.ajax( {
                    "dataType": 'json',
                    "type": "POST",
                    "url": sSource,
                    "data": aoData,
                    "success": function (data, textStatus, jqXHR) {
                        if(data.mess_type == 'success'){
                            fnCallback(data, textStatus, jqXHR);
                        } else{
                            systemMessages(data.message, 'message-' + data.mess_type);
                        }
                    }
                } );
            },
            sPaginationType: "full_numbers",
            fnDrawCallback: function( oSettings ) {
                $.fancybox.update();
            }
        });
    });
</script>