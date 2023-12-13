<?php tmvc::instance()->controller->view->display('new/filter_panel_main_view', array('filter_panel' => 'new/user/seller/updates/my/filter_panel_view')); ?>

<div class="container-center dashboard-container">
    <div class="dashboard-line">
        <h1 class="dashboard-line__ttl"><?php echo translate("seller_updates_dashboard_title_text"); ?></h1>

        <div class="dashboard-line__actions">
            <a class="btn btn-primary pl-20 pr-20 fancybox.ajax fancyboxValidateModal"
                data-fancybox-href="<?php echo __SITE_URL;?>seller_updates/popup_forms/add_update"
                data-title="<?php echo translate("seller_updates_dashboard_add_update_modal_title", null, true); ?>"
                title="<?php echo translate("seller_updates_dashboard_add_update_button_title", null, true); ?>"
                <?php echo addQaUniqueIdentifier('seller-updates-my__add-update-btn'); ?>
            >
                <i class="ep-icon ep-icon_plus-circle fs-20"></i>
                <span class="dn-m-min">Add update</span>
            </a>

            <!-- <a class="btn btn-light fancybox fancybox.ajax"
                data-fancybox-href="<?php echo __SITE_URL;?>user_guide/popup_forms/show_doc/75"
                data-title="<?php echo translate("seller_updates_dashboard_documentation_modal_title", null, true); ?>"
                title="<?php echo translate("seller_updates_dashboard_documentation_button_title", null, true); ?>"
                target="_blank">
                User guide
            </a> -->

            <a class="btn btn-dark btn-filter fancybox btn-counter"
                data-fancybox-href="#dtfilter-hidden"
                data-title="<?php echo translate("general_dt_filters_modal_title"); ?>"
                data-mw="740"
                title="<?php echo translate("general_dt_filters_button_title"); ?>"
                <?php echo addQaUniqueIdentifier('seller-updates-my__filter-btn'); ?>
            >
                <i class="ep-icon ep-icon_filter"></i> <?php echo translate("general_dt_filters_button_text"); ?>
            </a>
        </div>
    </div>

    <div class="info-alert-b">
        <i class="ep-icon ep-icon_info-stroke"></i>
        <span><?php echo translate('company_updates_description'); ?></span>
    </div>

    <table class="main-data-table" id="dtUpdatesList" <?php echo addQaUniqueIdentifier('seller-updates-my__updates-table'); ?>>
        <thead>
            <tr>
                <th class="picture_dt"><?php echo translate("seller_updates_dashboard_dt_column_picture_text"); ?></th>
                <th class="description_dt"><?php echo translate("seller_updates_dashboard_dt_column_description_text"); ?></th>
                <th class="created_dt"><?php echo translate("seller_updates_dashboard_dt_column_create_date_text"); ?></th>
                <th class="updated_dt"><?php echo translate("seller_updates_dashboard_dt_column_update_date_text"); ?></th>
                <th class="actions_dt"></th>
            </tr>
        </thead>
        <tbody class="tabMessage"></tbody>
    </table>
</div>

<?php tmvc::instance()->controller->view->display('new/file_upload_scripts'); ?>

<script type="application/javascript" src="<?php echo fileModificationTime('public/plug/jquery-datatables-1-10-12/jquery.dataTables.min.js');?>"></script>
<script type="application/javascript" src="<?php echo fileModificationTime('public/plug/jquery-dtfilters/jquery.dtFilters.js');?>"></script>
<script type="application/javascript">
    $(function() {
        var myFilters;
        var dtUpdatesList;

        var onSetFilters = function(caller, filter) {
            if(filter.name === 'created_from'){
                $("#filter-created-to").datepicker("option", "minDate", $("#filter-created-from").datepicker("getDate"));
            }
            if(filter.name === 'created_to'){
                $("#filter-created-from").datepicker("option","maxDate", $("#filter-created-to").datepicker("getDate"));
            }
            if(filter.name === 'updated_from'){
                $("#filter-updated-to").datepicker("option", "minDate", $("#filter-updated-from").datepicker("getDate"));
            }
            if(filter.name === 'updated_to'){
                $("#filter-updated-from").datepicker("option","maxDate", $("#filter-updated-to").datepicker("getDate"));
            }
        };
        var onDeleteFilters = function(filter) {
            if(filter.name === 'created_from'){
                $("#filter-created-to").datepicker("option", "minDate", null);
            }
            if(filter.name === 'created_to'){
                $("#filter-created-from").datepicker("option","maxDate", null);
            }
            if(filter.name === 'updated_from'){
                $("#filter-updated-to").datepicker("option", "minDate", null);
            }
            if(filter.name === 'updated_to'){
                $("#filter-updated-from").datepicker("option","maxDate", null);
            }
        };
        var onAddUpdate = function (response){
            dtUpdatesList.fnDraw();
        };
        var onEditUpdate = function (response){
            dtUpdatesList.fnDraw(false);
        };
        var deleteUpdate = function(caller) {
            var button = $(caller);
            var update = button.data('update') || null;
            var url = __site_url + 'seller_updates/ajax_updates_operation/delete_update';
            var onRequestSuccess = function(response) {
                systemMessages(response.message, response.mess_type);
                dtUpdatesList.fnDraw();
            }

            if(null !== update) {
                $.post(url, { update: update }, null, 'json').done(onRequestSuccess).fail(onRequestError);
            }
        };
        var fetchServerData = function(source, data, callback) {
            var onRequestSuccess = function(response, textStatus, jqXHR) {
                if (response.mess_type == 'error') {
                    systemMessages(response.message, 'message-' + response.mess_type);
                }

                callback(response, textStatus, jqXHR);
            };

            if(!myFilters){
                myFilters = initDtFilter();
            }

            $.post(source, data.concat(myFilters.getDTFilter()), null, 'json').done(onRequestSuccess).fail(onRequestError);
        };
        var onDatagridDraw = function(settings) {
            hideDTbottom(this);
            mobileDataTable($('.main-data-table'));
        };
        var onDatepickerShow = function(input, instance) {
            $('#ui-datepicker-div').addClass('dtfilter-ui-datepicker');
        };
        var datepickerOptions = {
            beforeShow: onDatepickerShow
        };
        var datagridOptions = {
            sDom: '<"top"i>rt<"bottom"lp><"clear">',
            language: {
                url: location.origin + '/public/plug/jquery-datatables-1-10-12/i18n/' + __site_lang + '.json'
            },
            bProcessing: false,
            bServerSide: true,
            sAjaxSource: location.origin + '/seller_updates/ajax_updates_list_dt',
            aoColumnDefs: [
                { sClass: "",         aTargets: ['picture_dt'],     mData: "picture",     bSortable: false },
                { sClass: "dn-xl",    aTargets: ['description_dt'], mData: "description", bSortable: false },
                { sClass: "w-100",    aTargets: ['created_dt'],     mData: "created_at",  bSortable: false },
                { sClass: "w-100",    aTargets: ['updated_dt'],     mData: "updated_at",  bSortable: false },
                { sClass: "w-40 tac vam", aTargets: ['actions_dt'],     mData: "actions",     bSortable: false }
            ],
            sorting: [],
            sPaginationType: "full_numbers",
            language: {
                paginate: {
                    previous: '<i class="ep-icon ep-icon_arrows-left"></i>',
                    first: '<i class="ep-icon ep-icon_arrow-left"></i>',
                    next: '<i class="ep-icon ep-icon_arrows-right"></i>',
                    last: '<i class="ep-icon ep-icon_arrow-right"></i>'
                }
            },
            fnServerData: fetchServerData,
            fnDrawCallback: onDatagridDraw,
        };

        dataT = dtUpdatesList = $('#dtUpdatesList').dataTable(datagridOptions);
        dataTableScrollPage(dataT);
        $(".datepicker-init").datepicker(datepickerOptions);
        mix(window, {
            deleteUpdate: deleteUpdate,
            onSetFilters: onSetFilters,
            onDeleteFilters: onDeleteFilters,
            callbackAddUpdate: onAddUpdate,
            callbackEditUpdate: onEditUpdate
        });
    });
</script>
