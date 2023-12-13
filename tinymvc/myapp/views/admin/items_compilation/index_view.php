<div class="row">
    <div class="col-xs-12">
        <div class="titlehdr">
			<span>Items compilation</span>
            <div class="pull-right">
                <a class="ep-icon ep-icon_plus-circle txt-green fancyboxValidateModalDT fancybox.ajax" href="<?php echo __SITE_URL . 'items_compilation/popup_forms/add_compilation';?>" title="Add compilation" data-title="Add compilation" data-table="dtItemsCompilation"></a>
            </div>
		</div>

        <?php views('admin/items_compilation/filter_panel_view');?>
        <div class="wr-filter-list clearfix mt-10"></div>

        <table class="data table-striped table-bordered w-100pr" id="itemsCompilation" cellspacing="0" cellpadding="0" >
            <thead>
                <tr>
                    <th class="dt_id tac vam w-25">#</th>
                    <th class="dt_title tac vam w-250">Title</th>
                    <th class="dt_url tac vam w-250">URL</th>
                    <th class="dt_is_published tac vam w-40">Is published</th>
                    <th class="dt_actions tac vam w-50">Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<?php views('admin/file_upload_scripts');?>

<script>
    var requirementFilters;
    var dtItemsCompilation;

    $(document).ready(function(){
        dtItemsCompilation = $('#itemsCompilation').dataTable( {
            "sDom": '<"top"lp>rt<"bottom"ip><"clear">',
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo __SITE_URL . "items_compilation/ajax_dt_administration";?>",
            "sServerMethod": "POST",
            "aoColumnDefs": [
                { "sClass": "tac vam w-25",  "aTargets": ['dt_id'], "mData": "id"},
                { "sClass": "tac vam w-250",  "aTargets": ['dt_title'], "mData": "title", "bSortable": false },
                { "sClass": "tac vam w-250", "aTargets": ['dt_url'], "mData": "url", "bSortable": false },
                { "sClass": "tac vam w-40", "aTargets": ['dt_is_published'], "mData": "isPublished", "bSortable": false },
                { "sClass": "tac vam w-50", "aTargets": ['dt_actions'], "mData": "actions", "bSortable": false },
            ],
            "sorting": [[0, "desc"]],
            "fnServerData": function ( sSource, aoData, fnCallback ) {
                if(!requirementFilters){
                    requirementFilters = $('.dt_filter').dtFilters('.dt_filter',{
                        'container': '.wr-filter-list',
                        'debug':false,
                        callBack: function(){
                            dtItemsCompilation.fnDraw();
                        },
                        onSet: function(callerObj, filterObj){},
                        onDelete: function(callerObj, filterObj){},
						onReset: function(){}
                    });
                }

                aoData = aoData.concat(requirementFilters.getDTFilter());
                $.ajax( {
                    "dataType": 'JSON',
                    "type": "POST",
                    "url": sSource,
                    "data": aoData,
                    "success": function (data, textStatus, jqXHR) {
                        if (data.mess_type == 'error' || data.mess_type == 'info'){
                            systemMessages(data.message, 'message-' + data.mess_type);
                        }

                        fnCallback(data, textStatus, jqXHR);

                    }
                });
            },
            "sPaginationType": "full_numbers",
            "lengthMenu": [[10, 50, 100, 250], [10, 50, 100, 250]],
            "fnDrawCallback": function( oSettings ) {

            }
        });
    });

    var toglePublishStatus = function (element) {
        var btn = $(element);
        var id = btn.data('id');

        $.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL . 'items_compilation/ajax_operations/togle_published_status';?>',
            data: { id : id},
            dataType: 'json',
            success: function(response){
                systemMessages( response.message, 'message-' + response.mess_type );

                if (response.mess_type == 'success') {
                    dtItemsCompilation.fnDraw(false);
                }
            }
        });
    }

    var deleteCompilation = function (element) {
        var btn = $(element);
        var id = btn.data('id');

        $.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL . 'items_compilation/ajax_operations/delete_compilation';?>',
            data: { id : id},
            dataType: 'json',
            success: function(response){
                systemMessages( response.message, 'message-' + response.mess_type );

                if (response.mess_type == 'success') {
                    dtItemsCompilation.fnDraw(false);
                }
            }
        });
    }
</script>
