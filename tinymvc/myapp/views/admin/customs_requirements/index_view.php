<div class="row">
    <div class="col-xs-12">
        <div class="titlehdr h-30">
            <span>Customs requirements</span>
            <a class="fancyboxValidateModalDT fancybox.ajax pull-right ep-icon ep-icon_plus-circle txt-green" href="<?php echo __SITE_URL?>customs_requirements/popup_forms/add_requirement" data-table="dtRequirements" data-title="Add requirement"></a>
        </div>
        <?php tmvc::instance()->controller->view->display('admin/customs_requirements/filter_panel_view'); ?>
        <div class="wr-filter-list clearfix mt-10"></div>

        <table class="data table-striped table-bordered w-100pr" id="dtRequirements" cellspacing="0" cellpadding="0" >
            <thead>
                <tr>
                    <th class="dt_id_requirement">#</th>
                    <th class="dt_country">Country</th>
                    <th class="dt_text">Text</th>
                    <th class="dt_meta_data">Meta data</th>
                    <th class="dt_actions">Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
<script>
    var requirementFilters;
    var dtRequirements;
    var remove_customs_req = function(obj){
        var $this = $(obj);
        $.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL?>customs_requirements/ajax_requirement_operation/remove_custom_requirement',
            data: {record: $this.data('record')},
            beforeSend: function(){ },
            dataType: 'json',
            success: function(data){

                systemMessages( data.message, 'message-' + data.mess_type );

                if(data.mess_type == 'success'){ dtRequirements.fnDraw(false); }
            }
        });
    }

    var change_visible_customs_req = function(obj){
        var $this = $(obj);
        $.ajax({
            type: "POST",
            context: $(this),
            url: '<?php echo __SITE_URL?>customs_requirements/ajax_requirement_operation/change_visible_custom_requirement',
            data: { record: $this.data("record") },
            dataType: 'JSON',
            success: function(resp){

                systemMessages( resp.message, 'message-' + resp.mess_type );

                if(resp.mess_type == 'success'){
                    $this.toggleClass('ep-icon_invisible ep-icon_visible');
                    dtRequirements.fnDraw(false);
                }

            }
        });
    }
    $(document).ready(function(){
        dtRequirements = $('#dtRequirements').dataTable( {
            "sDom": '<"top"lp>rt<"bottom"ip><"clear">',
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo __SITE_URL?>customs_requirements/ajax_customs_requirements_administration",
            "sServerMethod": "POST",
            "aoColumnDefs": [
                { "sClass": "w-40 tac",  "aTargets": ['dt_id_requirement'], "mData": "dt_id_requirement" },
                { "sClass": "w-100 tac", "aTargets": ['dt_country'],        "mData": "dt_country" },
                { "sClass": "w-150 tac", "aTargets": ['dt_meta_data'],      "mData": "dt_meta_data",    "bSortable": false },
                { "sClass": "w-150 tac", "aTargets": ['dt_photo'],          "mData": "dt_photo",        "bSortable": false },
                { "sClass": "",          "aTargets": ['dt_text'],           "mData": "dt_text",         "bSortable": false },
                { "sClass": "tac w-80",  "aTargets": ['dt_actions'],        "mData": "dt_actions",      "bSortable": false }
            ],
            "sorting": [[1, "asc"]],
            "fnServerData": function ( sSource, aoData, fnCallback ) {

                if(!requirementFilters){
                    requirementFilters = $('.dt_filter').dtFilters('.dt_filter',{
                        'container': '.wr-filter-list',
                        'debug':false,
                        callBack: function(){ dtRequirements.fnDraw(); },
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
                    $("#dtRequirements tbody *").highlight(keywordsSearch, "highlight");
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
