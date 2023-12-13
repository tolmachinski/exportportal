<div class="p-15">
    <h1 class="titlehdr">Statistics</h1>
    <table class="table table-striped">
        <tr>
            <td>Count of all not registered users requests</td>
            <td><?php echo (int) $statistics['total_not_registered']; ?></td>
        </tr>
        <tr>
            <td>Count of all registered users requests</td>
            <td><?php echo (int) $statistics['total_registered']; ?></td>
        </tr>
        <tr>
            <td>Times downloaded by not registered users</td>
            <td><?php echo (int) $statistics['download_not_registered']; ?></td>
        </tr>
        <tr>
            <td>Times downloaded by registered users</td>
            <td><?php echo (int) $statistics['download_registered']; ?></td>
        </tr>
    </table>

    <h1 class="titlehdr">User data</h1>
    <iframe src="" class="d-none-full" id="js-download-report-stat"></iframe>
    <a class="btn btn-info call-function" data-callback="export_excel" title="Export excel">Export excel</a>
    <table id="dtUsersDownloadableMaterials"
        class="data table-striped table-bordered"
        cellspacing="0"
        cellpadding="0">
        <thead>
            <tr>
                <th class="dt_user">User</th>
                <th class="dt_email">Email</th>
                <th class="dt_phone">Phone</th>
                <th class="dt_country">Country</th>
                <th class="dt_downloads">Downloads</th>
                <th class="dt_updated">Last download</th>
                <th class="dt_referral">Referral</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<script type="text/javascript">

    var dtUsersDownloadableMaterials, requirementFilters;

    $(document).ready(function(){
        dtUsersDownloadableMaterials = $('#dtUsersDownloadableMaterials').dataTable({
            "sDom": 'rt<"bottom"ip><"clear">',
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo __SITE_URL . 'downloadable_materials/ajaxUsersDtAdministration/' . $idMaterial;?>",
            "sServerMethod": "POST",
            "aoColumnDefs": [
                { "sClass": "tal w-100", "aTargets": ['dt_user'], "mData": "dt_user", "bSortable": false},
                { "sClass": "tal w-80", "aTargets": ['dt_email'], "mData": "dt_email", "bSortable": false },
                { "sClass": "tal w-100", "aTargets": ['dt_phone'], "mData": "dt_phone", "bSortable": false },
                { "sClass": "tac w-100", "aTargets": ['dt_country'], "mData": "dt_country", "bSortable": false },
                { "sClass": "tac w-50", "aTargets": ['dt_downloads'], "mData": "dt_downloads", "bSortable": false },
                { "sClass": "w-80 tac vam", "aTargets": ['dt_updated'], "mData": "dt_updated", "bSortable": true},
                { "sClass": "w-150 tac vam text-break", "aTargets": ['dt_referral'], "mData": "dt_referral", "bSortable": false}
            ],
            "fnServerParams": function ( aoData ) {},
            "fnServerData": function ( sSource, aoData, fnCallback ) {

                if(!requirementFilters){
                    requirementFilters = $('.dt_filter').dtFilters('.dt_filter',{
                        'container': '.wr-filter-list',
                        'debug': false,
                        callBack: function(){
                            dtUsersDownloadableMaterials.fnDraw()
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
            "iDisplayLength": 4,
            "sorting" : [[5,'desc']],
            "sPaginationType": "full_numbers",
            "fnDrawCallback": function( oSettings ) { $.fancybox.update(); }
        });
    });

    var export_excel = function(button){
        var exportUrl = "<?php echo  __SITE_URL . "downloadable_materials/export_statistics/" . $idMaterial;?>";
        $('#js-download-report-stat').attr('src', exportUrl);
       // $(button).addClass("disabled");
    }
</script>
