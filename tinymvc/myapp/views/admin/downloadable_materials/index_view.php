<?php views()->display('admin/file_upload_scripts'); ?>

<script type="text/javascript">

    var dtNewsArchive, groupsFilters, dtDownloadableMaterials, requirementFilters;

    $(document).ready(function(){
        dtDownloadableMaterials = $('#dtDownloadableMaterials').dataTable({
            "sDom": '<"top"lp>rt<"bottom"ip><"clear">',
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo __SITE_URL . 'downloadable_materials/ajaxDtAdministration';?>",
            "sServerMethod": "POST",
            "aoColumnDefs": [
                { "sClass": "tac w-30", "aTargets": ['dt_id'], "mData": "dt_id", "bSortable": true},
                { "sClass": "tal w-400", "aTargets": ['dt_title'], "mData": "dt_title", "bSortable": false},
                { "sClass": "tal", "aTargets": ['dt_short_description'], "mData": "dt_short_description", "bSortable": false },
                { "sClass": "tac w-100", "aTargets": ['dt_cover'], "mData": "dt_cover", "bSortable": false },
                { "sClass": "tac w-50", "aTargets": ['dt_downloads'], "mData": "dt_downloads", "bSortable": true },
                { "sClass": "w-80 tac vam", "aTargets": ['dt_actions'], "mData": "dt_actions", "bSortable": false}
            ],
            "fnServerParams": function ( aoData ) {},
            "fnServerData": function ( sSource, aoData, fnCallback ) {

                if(!requirementFilters){
                    requirementFilters = $('.dt_filter').dtFilters('.dt_filter',{
                        'container': '.wr-filter-list',
                        'debug': false,
                        callBack: function(){
                            dtDownloadableMaterials.fnDraw()
                        },
                        onSet: function(callerObj, filterObj){
							if (filterObj.name == 'created_from') {
								$('input[name="created_to"]').datepicker("option", "minDate", $('input[name="created_from"]').datepicker("getDate"));
							}

							if (filterObj.name == 'created_to') {
								$('input[name="created_from"]').datepicker("option", "maxDate", $('input[name="created_to"]').datepicker("getDate"));
							}
						},
                        onDelete: function(callerObj, filterObj){
                            if (filterObj.name == 'created_to') {
								$('input[name="created_from"]').datepicker( "option" , {maxDate: null});
                            }

                            if (filterObj.name == 'created_from') {
								$('input[name="created_to"]').datepicker( "option" , {minDate: null});
							}
                        },
						onReset: function(){
							$('.dt_filter .hasDatepicker').datepicker( "option" , {
								minDate: null,
								maxDate: null
							});
						}
                    });
                }

                aoData = aoData.concat(requirementFilters.getDTFilter());

                $.ajax({
                    "dataType": 'JSON',
                    "type": "POST",
                    "url": sSource,
                    "data": aoData,
                    "success": function (data, textStatus, jqXHR) {
                        if(data.mess_type == 'error' || data.mess_type == 'info') {
                            systemMessages(data.message, 'message-' + data.mess_type);
                        }

                        fnCallback(data, textStatus, jqXHR);
                    }
                });
            },
            "sorting" : [[0,'desc']],
            "sPaginationType": "full_numbers",
            "fnDrawCallback": function( oSettings ) {}
        });
    });

    function delete_record(e) {
        $.ajax({
            type: 'POST',
            url: e.data('delete-link'),
            dataType: 'JSON',
            success: function(resp) {
                systemMessages(resp.message, 'message-' + resp.mess_type);
                dtDownloadableMaterials.fnDraw(true);
            }
        });
    }

</script>

<div class="row">
    <div class="col-xs-12">
        <div class="titlehdr h-30">
            <span>Downloadable Materials</span>
            <a class="btn btn-primary btn-sm pull-right fancybox.ajax fancyboxValidateModalDT"
               href="<?php echo __SITE_URL . 'downloadable_materials/ajaxPopupAdministration/add';?>"
               data-table="dtDownloadableMaterials"
               data-title="Add downloadable materials">
               Add downloadable materials
            </a>
        </div>

        <?php views()->display('admin/downloadable_materials/filter_panel_view'); ?>
        <div class="wr-filter-list clearfix mt-10"></div>

        <table id="dtDownloadableMaterials"
               class="data table-striped table-bordered w-100pr"
               cellspacing="0"
               cellpadding="0">
            <thead>
                <tr>
                    <th class="dt_id w-30">#</th>
                    <th class="dt_cover">Cover</th>
                    <th class="dt_title w-400">Title</th>
                    <th class="dt_short_description">Short Description</th>
                    <th class="dt_downloads">Downloads</th>
                    <th class="dt_actions">Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
