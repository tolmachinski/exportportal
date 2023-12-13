<div class="row">
    <div class="col-xs-12">
        <?php views()->display('admin/countries/filter_panel_view'); ?>
        <div class="wr-filter-list clearfix mt-10"></div>

        <table class="data table-striped table-bordered w-100pr" id="dtCountries" cellspacing="0" cellpadding="0" >
            <thead>
                <tr>
                    <th class="dt_id tac vam">#</th>
                    <th class="dt_country w-450 tac vam">Country</th>
                    <th class="dt_continent tac vam">Continent</th>
                    <th class="dt_code tac vam">Code</th>
                    <th class="dt_position_in_select tac vam">Position in Select</th>
                    <th class="dt_focus_country tac vam">Is Focus Country</th>
                    <th class="dt_translations tac vam">Translations</th>
                    <th class="dt_actions tac vam">Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
<script>
    var requirementFilters;
    var dtCountries;

    $(document).ready(function(){
        dtCountries = $('#dtCountries').dataTable( {
            "sDom": '<"top"lp>rt<"bottom"ip><"clear">',
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo __SITE_URL . "country/ajax_dt_countries";?>",
            "sServerMethod": "POST",
            "aoColumnDefs": [
                { "sClass": "w-50 tac vam",  "aTargets": ['dt_id'], "mData": "dt_country_id", "bSortable": false },
                { "sClass": "w-50 tac vam", "aTargets": ['dt_code'],   "mData": "dt_country_code", "bSortable": false },
                { "sClass": "w-300 tac vam", "aTargets": ['dt_country'],  "mData": "dt_country_name"},
                { "sClass": "tac vam", "aTargets": ['dt_continent'],"mData": "dt_country_continent", "bSortable": false },
                { "sClass": "w-150 tac vam", "aTargets": ['dt_position_in_select'],"mData": "dt_country_position"},
                { "sClass": "w-100 tac vam", "aTargets": ['dt_focus_country'],"mData": "dt_country_is_focus", "bSortable": false },
                { "sClass": "w-200 tac vam", "aTargets": ['dt_translations'],"mData": "dt_translations", "bSortable": false },
                { "sClass": "w-100 tac vam", "aTargets": ['dt_actions'],"mData": "dt_country_actions", "bSortable": false },
            ],
            "sorting": [[1, "asc"]],
            "fnServerData": function ( sSource, aoData, fnCallback ) {

                if(!requirementFilters){
                    requirementFilters = $('.dt_filter').dtFilters('.dt_filter',{
                        'container': '.wr-filter-list',
                        'debug':false,
                        callBack: function(){ dtCountries.fnDraw(); },
                        onSet: function(callerObj, filterObj){
							if (filterObj.name == 'sent_date_from') {
								$('input[name="sent_date_to"]').datepicker("option", "minDate", $('input[name="sent_date_from"]').datepicker("getDate"));
							}

							if (filterObj.name == 'sent_date_to') {
								$('input[name="sent_date_from"]').datepicker("option", "maxDate", $('input[name="sent_date_to"]').datepicker("getDate"));
							}
						},
                        onDelete: function(callerObj, filterObj){
                            if (filterObj.name == 'sent_date_to') {
								$('input[name="sent_date_from"]').datepicker( "option" , {maxDate: null});
                            }

                            if (filterObj.name == 'sent_date_from') {
								$('input[name="sent_date_to"]').datepicker( "option" , {minDate: null});
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
            "lengthMenu": [[50, 100, 250], [50, 100, 250]],
            "fnDrawCallback": function( oSettings ) {

            }
        });
    });

</script>
