<div class="row">
    <div class="col-xs-12">
        <div class="titlehdr h-30">
            <span>List of support categories</span>
            <a class="pull-right ep-icon ep-icon_items fs-24 ml-10 mr-4" href="<?php echo __SITE_URL?>email_message/administration" title="List of message"></a>
            <a class="fancyboxValidateModalDT fancybox.ajax pull-right ep-icon ep-icon_plus-circle txt-green" href="<?php echo __SITE_URL?>category_support/popup_forms/add_category_support" data-table="dtCategorySupport" data-title="Add Category Support" title="Add category of support"></a>
        </div>
        <?php tmvc::instance()->controller->view->display('admin/category_support/filter_panel_view'); ?>
        <div class="wr-filter-list clearfix mt-10"></div>

        <table class="data table-striped table-bordered w-100pr" id="dtCategorySupport" cellspacing="0" cellpadding="0" >
            <thead>
                <tr>
                    <th class="dt_id_record">#</th>
                    <th class="dt_category">Category</th>
                    <th class="dt_assign">Assign Users</th>
                    <th class="dt_actions">Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
<script>
    var requirementFilters;
    var dtCategorySupport;
    var remove_category_support = function(obj){
        var $this = $(obj);
        $.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL?>category_support/ajax_category_support_operation/remove_category_support',
            data: {record: $this.data('record')},
            beforeSend: function(){ },
            dataType: 'json',
            success: function(data){
                systemMessages( data.message, 'message-' + data.mess_type );
                if(data.mess_type == 'success'){ dtCategorySupport.fnDraw(false); }
            }
        });
    }

    $(document).ready(function(){
        dtCategorySupport = $('#dtCategorySupport').dataTable( {
            "sDom": '<"top"lp>rt<"bottom"ip><"clear">',
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo __SITE_URL?>category_support/ajax_dt_cat_support",
            "sServerMethod": "POST",
            "aoColumnDefs": [
                { "sClass": "w-40  tac vam", "aTargets": ['dt_id_record'],"mData": "dt_id_record" },
                { "sClass": "w-150 tac vam", "aTargets": ['dt_category'], "mData": "dt_category"  },
                { "sClass": "w-300 tac vam", "aTargets": ['dt_assign'],   "mData": "dt_assign", "bSortable": false},
                { "sClass": "w-50  tac vam", "aTargets": ['dt_actions'],  "mData": "dt_actions","bSortable": false}
            ],
            "sorting": [[0, "asc"]],
            "fnServerData": function ( sSource, aoData, fnCallback ) {

                if(!requirementFilters){
                    requirementFilters = $('.dt_filter').dtFilters('.dt_filter',{
                        'container': '.wr-filter-list',
                        'debug':false,
                        callBack: function(){ dtCategorySupport.fnDraw(); },
                        onSet: function(callerObj, filterObj){

                        },
                        onDelete: function(filter){

                        }
                    });
                }

                aoData = aoData.concat(requirementFilters.getDTFilter());
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
            "fnDrawCallback": function( oSettings ) {

                var keywordsSearch = $('.filter-admin-panel').find('input[name=keywords]').val();
                if( keywordsSearch !== '' )
                    $("#dtCategorySupport tbody *").highlight(keywordsSearch, "highlight");
            }
        });
    });

</script>
