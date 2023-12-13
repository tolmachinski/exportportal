<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/jquery-datatables-1-10-12/jquery.dataTables.min.js');?>"></script>
<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/jquery-dtfilters/jquery.dtFilters.js');?>"></script>

<script>
    var dtPartnersList;
    var partnersFilters;
    var fnDrawFirst = 0;

    delete_partner = function(opener){
        var btn = $(opener);
        var partner = btn.data('partner');
        var company = btn.data('company');
        $.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL?>b2b/ajax_b2b_operation/delete_partner',
            data: { partner: partner, company: company},
            dataType: 'JSON',
            success: function(resp){
                systemMessages( resp.message, resp.mess_type );
                if(resp.mess_type == 'success')
                    dtPartnersList.fnDraw();
            },
        })
    }
    filters_has_datepicker = true;

    var onDeleteFilters = function(filter) {
        if(filter.name === 'start_from'){
            $("#start_to").datepicker("option", "minDate", null);
        }
        if(filter.name === 'start_to'){
            $("#start_from").datepicker("option","maxDate", null);
        }
    };

    $(document).ready(function(){
        dataT = dtPartnersList = $('#dtPartnersList').dataTable( {
            "sDom": '<"top"i>rt<"bottom"lp><"clear">',
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo __SITE_URL; ?>b2b/ajax_my_partners",
            "sServerMethod": "POST",
            "aoColumnDefs": [
                {"sClass": "vam", "aTargets": ['dt_partner'], "mData": "partner_dt", "bSortable": false},
                {"sClass": "w-200 vam dn-xl", "aTargets": ['dt_address'], "mData": "address_dt", "bSortable": false},
                {"sClass": "w-200 vam", "aTargets": ['dt_contact'], "mData": "contact_dt", "bSortable": false},
                {"sClass": "w-150 vam", "aTargets": ['dt_date_partnership'], "mData": "date_partnership_dt"},
                {"sClass": "w-20 vam tac dt-actions", "aTargets": ['dt_actions'], "mData": "actions_dt", "bSortable": false}
            ],
            "sorting" : [[3,'desc']],
            "sPaginationType": "full_numbers",
            "language": {
                "paginate": {
                    "first": "<i class='ep-icon ep-icon_arrow-left'></i>",
                    "previous": "<i class='ep-icon ep-icon_arrows-left'></i>",
                    "next": "<i class='ep-icon ep-icon_arrows-right'></i>",
                    "last": "<i class='ep-icon ep-icon_arrow-right'></i>"
                }
            },
            "fnServerData": function ( sSource, aoData, fnCallback ) {
                if ( !partnersFilters ) {
                    //view template initDtFilter in scripts_new
                    partnersFilters = initDtFilter();
                }

                aoData = aoData.concat(partnersFilters.getDTFilter());
                $.ajax({
                    "dataType": 'json',
                    "type": "POST",
                    "url": sSource,
                    "data": aoData,
                    "success": function(data, textStatus, jqXHR) {
                        if ( data.mess_type == 'error' || data.mess_type == 'info' ) {
                            systemMessages(data.message, data.mess_type);
                        }

                        fnCallback(data, textStatus, jqXHR);
                    }
                });
            },
            "fnDrawCallback": function(oSettings) {
                hideDTbottom(this);
            }
        });
        dataTableScrollPage(dataT);
    });
</script>

<?php tmvc::instance()->controller->view->display('new/filter_panel_main_view', array('filter_panel' => 'new/b2b/my/filter_panel_partners_view')); ?>

<div class="container-center dashboard-container">

    <div class="dashboard-line">
        <h1 class="dashboard-line__ttl">My Partners</h1>

        <div class="dashboard-line__actions">
            <!-- <a class="btn btn-light fancybox fancybox.ajax" href="<?php echo __SITE_URL;?>user_guide/popup_forms/show_doc/96" title="View B2B Partners user guide" data-title="View B2B Partners user guide" target="_blank">User guide</a> -->

            <a class="btn btn-dark fancybox btn-filter" <?php echo addQaUniqueIdentifier('global__dashboard_filter-btn') ?> href="#dtfilter-hidden" data-mw="740" data-title="Filter panel">
                <i class="ep-icon ep-icon_filter"></i> Filter
            </a>
        </div>
    </div>

	<div class="info-alert-b">
		<i class="ep-icon ep-icon_info-stroke"></i>
		<span><?php echo translate('b2b_my_partners'); ?></span>
	</div>

    <table class="main-data-table" id="dtPartnersList" <?php echo addQaUniqueIdentifier('page__b2b-my-partners_table'); ?>>
        <thead>
        <tr>
            <th class="dt_partner">Partner</th>
            <th class="dt_address">Address</th>
            <th class="dt_contact">Contact</th>
            <th class="dt_date_partnership">Partnership created</th>
            <th class="dt_actions"></th>
        </tr>
        </thead>
        <tbody class="tabMessage"></tbody>
    </table>
</div>


