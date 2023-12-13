<script>
    var requirementFilters;
    var dtInspection;

    $(document).ready(function(){
        dtInspection = $('#dtInspection').dataTable( {
            "sDom": '<"top"lp>rt<"bottom"ip><"clear">',
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo __SITE_URL?>library_inspection_agency/ajax_dt_inspection_agency",
            "sServerMethod": "POST",
            "iDisplayLength": 50,
            "aoColumnDefs": [
                { "sClass": "w-50 tac",  "aTargets": ['dt_id_record'],"mData": "dt_id_record" },
                { "sClass": "w-100 tac", "aTargets": ['dt_country'],  "mData": "dt_country" },
                { "sClass": "w-100 tac", "aTargets": ['dt_company'],  "mData": "dt_company" },
                { "sClass": "w-100 tac", "aTargets": ['dt_address'],  "mData": "dt_address" },
                { "sClass": "w-100 tac", "aTargets": ['dt_phone'],    "mData": "dt_phone" },
                { "sClass": "w-100 tac", "aTargets": ['dt_email'],    "mData": "dt_email" },
                { "sClass": "w-70 tac", "aTargets": ['dt_website'],   "mData": "dt_website" },
                { "sClass": "", "aTargets": ['dt_serv_provided'],     "mData": "dt_serv_provided","bSortable": false },
                { "sClass": "w-70 tac", "aTargets": ['dt_type_add'],  "mData": "dt_type_add", "bSortable": false },
                { "sClass": "w-50 tac", "aTargets": ['dt_is_visible'],"mData": "dt_is_visible" },
                { "sClass": "w-80 tac", "aTargets": ['dt_actions'],   "mData": "dt_actions", "bSortable": false }
            ],
            "sorting": [[0, "asc"]],
            "fnServerData": function ( sSource, aoData, fnCallback ) {

                if(!requirementFilters){
                    requirementFilters = $('.dt_filter').dtFilters('.dt_filter',{
                        'container': '.wr-filter-list',
                        'debug':false,
                        callBack: function(){ dtInspection.fnDraw(); },
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
                    $("#dtInspection tbody *").highlight(keywordsSearch, "highlight");
            }
        });

        $('.select_records').on('click', function(){
            var $this = $(this);
            if ($this.prop('checked')){
                $('.checked-element').prop("checked", true);
            }else{
                $('.checked-element').prop("checked", false);
            }
        });
    });

var remove_acc_record = function(obj){
    var $this = $(obj);
    $.ajax({
        type: 'POST',
        url: '<?php echo __SITE_URL?>library_inspection_agency/ajax_library_operation/remove_record',
        data: {record: $this.data('record')},
        beforeSend: function(){ },
        dataType: 'json',
        success: function(data){
            systemMessages( data.message, 'message-' + data.mess_type );
            if(data.mess_type == 'success'){ dtInspection.fnDraw(false); }
        }
    });
}

var download_last_excel = function(obj){
    var $this = $(obj);
    var status = $this.data('status');

    $.ajax({
        type: 'POST',
        url: '<?php echo __SITE_URL?>library_inspection_agency/ajax_library_operation/download_last_file',
        data: {},
        beforeSend: function(){ },
        dataType: 'json',
        success: function(data){
            if(data.mess_type == 'success'){
                $('#downloadLastFile').prop('src', data.src);
            }else{
                systemMessages( data.message, 'message-' + data.mess_type );
            }
        }
    });
}

var visible_status = function(obj){
    var $this = $(obj);
    var id_record = $this.data('record');
    var status= $this.data('status');

    $.ajax({
        type: 'POST',
        url: '<?php echo __SITE_URL?>library_inspection_agency/ajax_library_operation/change_status',
        data: {id_record : id_record, status : status},
        beforeSend: function(){ },
        dataType: 'json',
        success: function(data){
            systemMessages( data.message, 'message-' + data.mess_type );
            if(data.mess_type == 'success'){ dtInspection.fnDraw(false); }
        }
    });
}

var remove_list = function(obj){
    var elements= $('.checked-element'),
        list = [];

    for(var i = 0; i < elements.length; i++){
        var currentInput = $(elements[i]);
        if (currentInput.prop('checked')){
            var data = {
                name : "elements[]",
                value: currentInput.val()
            }
            list.push(data);
        }
    }

    if (list.length > 0){
        $.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL?>library_inspection_agency/ajax_library_operation/remove_records',
            data: list,
            beforeSend: function(){ },
            dataType: 'json',
            success: function(data){
                systemMessages( data.message, 'message-' + data.mess_type );
                $('.select_records').prop("checked", false);
                if(data.mess_type == 'success'){ dtInspection.fnDraw(false); }
            }
        });
    } else {
        systemMessages( 'Please select records', 'message-info' );
    }

}
</script>

<div class="row">
    <div class="col-xs-12">
        <div class="titlehdr h-30">
            <span>Inspection agency</span>
            <a class="pull-right ep-icon icon-files-xlsx-small w-35 h-35 call-function" data-callback="download_last_excel" title="Download accreditation body file"></a>
            <a class="pull-right ep-icon ep-icon_remove txt-red confirm-dialog mr-5" data-callback="remove_list" title="Remove records" data-message="Are you sure you want to remove this records?"></a>
            <a class="pull-right ep-icon ep-icon_edit-list txt-lblue-darker fancyboxValidateModalDT fancybox.ajax mr-5" href="<?php echo __SITE_URL?>library_inspection_agency/popup_forms/update_by_country"  data-table="dtInspection" data-title="Update country records" title="Update country records"></a>
            <a class="fancyboxValidateModalDT fancybox.ajax pull-right ep-icon ep-icon_upload mr-5" href="<?php echo __SITE_URL?>library_inspection_agency/popup_forms/add_record_excel" data-table="dtInspection" data-title="Add inspection agency from file" title="Add inspection agency from file"></a>
            <a class="fancyboxValidateModalDT fancybox.ajax pull-right ep-icon ep-icon_plus-circle txt-green mr-5" href="<?php echo __SITE_URL?>library_inspection_agency/popup_forms/add_record" data-table="dtInspection" data-title="Add new inspection agency" title="Add inspection agency manual"></a>
        </div>
        <?php tmvc::instance()->controller->view->display('admin/library_settings/filter_panel_view'); ?>
        <div class="wr-filter-list clearfix mt-10 "></div>
        <div class="w-230">Select all records from this page:
            <div class="ml-5 w-15 mt-2 pull-right">
                <input class="select_records" type="checkbox" name="select_all" value="select_all">
            </div>
        </div>
        <table class="data table-striped table-bordered w-100pr" id="dtInspection" cellspacing="0" cellpadding="0" >
            <thead>
                <tr>
                    <th class="dt_id_record">#</th>
                    <th class="dt_country">Country</th>
                    <th class="dt_company">Company Name</th>
                    <th class="dt_address">Address</th>
                    <th class="dt_phone">Tel.</th>
                    <th class="dt_website">Website</th>
                    <th class="dt_email">Email</th>
                    <th class="dt_serv_provided">Services provided</th>
                    <th class="dt_type_add">Type adding</th>
                    <th class="dt_is_visible">Visible</th>
                    <th class="dt_actions">Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
        <iframe src="" id="downloadLastFile" style="display:none"></iframe>
    </div>
</div>
