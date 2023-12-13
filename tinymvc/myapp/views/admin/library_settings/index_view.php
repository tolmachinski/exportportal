<div class="row">
    <div class="col-xs-12">
        <div class="titlehdr h-30">
            <span>Library settings</span>
            <a class="fancyboxValidateModalDT fancybox.ajax pull-right ep-icon ep-icon_plus-circle txt-green" href="<?php echo __SITE_URL?>library_setting/popup_forms/add_library_setting" data-table="dtLibraryConfig" data-title="Add library setting"></a>
        </div>
        <?php tmvc::instance()->controller->view->display('admin/library_settings/filter_panel_view'); ?>
        <div class="wr-filter-list clearfix mt-10"></div>

        <table class="data table-striped table-bordered w-100pr" id="dtLibraryConfig" cellspacing="0" cellpadding="0" >
            <thead>
                <tr>
                    <th class="dt_id_lib">#</th>
                    <th class="dt_lib_title">Title Config</th>
                    <th class="dt_lib_text">Description</th>
                    <th class="dt_type_control">Type insert</th>
                    <th class="dt_lib_file">File name</th>
                    <th class="dt_actions">Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
<script>
    var requirementFilters;
    var dtLibraryConfig;
    var remove_library_setting = function(obj){
        var $this = $(obj);
        $.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL?>library_setting/ajax_library_setting_operation/remove_library_setting',
            data: {record: $this.data('record')},
            beforeSend: function(){ },
            dataType: 'json',
            success: function(data){
                systemMessages( data.message, 'message-' + data.mess_type );
                if(data.mess_type == 'success'){ dtLibraryConfig.fnDraw(false); }
            }
        });
    }

    $(document).ready(function(){
        dtLibraryConfig = $('#dtLibraryConfig').dataTable( {
            "sDom": '<"top"lp>rt<"bottom"ip><"clear">',
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo __SITE_URL?>library_setting/ajax_config_library_administration",
            "sServerMethod": "POST",
            "aoColumnDefs": [
                { "sClass": "w-40 tac",  "aTargets": ['dt_id_lib'],      "mData": "dt_id_lib" },
                { "sClass": "w-250 tac", "aTargets": ['dt_lib_title'],   "mData": "dt_lib_title" },
                { "sClass": "",          "aTargets": ['dt_lib_text'],    "mData": "dt_lib_text",    "bSortable": false },
                { "sClass": "w-80 tac",  "aTargets": ['dt_type_control'],"mData": "dt_type_control","bSortable": false },
                { "sClass": "w-120 tac", "aTargets": ['dt_lib_file'],    "mData": "dt_lib_file",    "bSortable": false },
                { "sClass": "w-150 tac", "aTargets": ['dt_actions'],     "mData": "dt_actions",     "bSortable": false }
            ],
            "sorting": [[0, "asc"]],
            "fnServerData": function ( sSource, aoData, fnCallback ) {

                if(!requirementFilters){
                    requirementFilters = $('.dt_filter').dtFilters('.dt_filter',{
                        'container': '.wr-filter-list',
                        'debug':false,
                        callBack: function(){ dtLibraryConfig.fnDraw(); },
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
                    $("#dtLibraryConfig tbody *").highlight(keywordsSearch, "highlight");
            }
        });

        $('body').on('click', '.btn-customs-req-more', function(e){
            e.preventDefault();
            var $thisBtn = $(this);
            var $textB = $thisBtn.closest('td').find('.hidden-b');
            $textB.toggleClass('h-50');

            ($textB.hasClass('h-50'))?$thisBtn.attr('title','view more'):$thisBtn.attr('title','hide more');
            $thisBtn.toggleClass('ep-icon_arrows-down ep-icon_arrows-up');
        });

    });

</script>
