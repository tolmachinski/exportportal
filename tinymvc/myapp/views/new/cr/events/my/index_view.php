<?php tmvc::instance()->controller->view->display('new/filter_panel_main_view', array('filter_panel' => 'new/cr/events/my/filter_panel_view')); ?>

<div class="container-center dashboard-container">
    <div class="dashboard-line">
        <h1 class="dashboard-line__ttl"><?php echo translate("cr_events_dashboard_title_text"); ?></h1>

        <div class="dashboard-line__actions">
            <?php if (have_right('manage_cr_personal_events')) { ?>
                <a class="btn btn-primary pl-20 pr-20 fancybox.ajax fancyboxValidateModal"
                    data-fancybox-href="<?php echo __SITE_URL . "cr_events/popup_forms/add_event";?>"
                    data-title="<?php echo translate("cr_events_dashboard_add_event_modal_title", null, true); ?>"
                    title="<?php echo translate("cr_events_dashboard_add_event_button_title", null, true); ?>">
                    <i class="ep-icon ep-icon_plus-circle fs-20"></i>
                    <span class="dn-m-min">Add event</span>
                </a>
            <?php } ?>

            <a class="btn btn-dark btn-filter fancybox btn-counter"
                data-fancybox-href="#dtfilter-hidden"
                data-title="<?php echo translate("general_dt_filters_modal_title"); ?>"
                data-mw="740"
                title="<?php echo translate("general_dt_filters_button_title"); ?>">
                <i class="ep-icon ep-icon_filter"></i> <?php echo translate("general_dt_filters_button_text"); ?>
            </a>
        </div>
    </div>

    <div class="info-alert-b">
		<i class="ep-icon ep-icon_info-stroke"></i>
		<span><?php echo translate('cr_events_my_description'); ?></span>
	</div>

    <table class="main-data-table" id="dtEventTicketsList">
        <thead>
            <tr>
                <th class="event_dt"><?php echo translate("cr_events_dashboard_dt_event_text"); ?></th>
                <th class="description_dt"><?php echo translate("cr_events_dashboard_dt_description_text"); ?></th>
                <th class="location_dt"><?php echo translate("cr_events_dashboard_dt_location_text"); ?></th>
                <th class="start_dt"><?php echo translate("cr_events_dashboard_dt_start_date_text"); ?></th>
                <th class="end_dt"><?php echo translate("cr_events_dashboard_dt_end_date_text"); ?></th>
                <?php if (have_right('manage_cr_personal_events')) { ?>
                    <th class="actions_dt"></th>
                <?php } ?>
            </tr>
        </thead>
        <tbody class="tabMessage"></tbody>
    </table>
</div>

<?php tmvc::instance()->controller->view->display('new/file_upload_scripts'); ?>

<script type="application/javascript" src="<?php echo fileModificationTime('public/plug/jquery-datatables-1-10-12/jquery.dataTables.min.js');?>"></script>
<script type="application/javascript" src="<?php echo fileModificationTime('public/plug/jquery-dtfilters/jquery.dtFilters.js');?>"></script>
<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/tinymce-4-3-10/tinymce.min.js'); ?>"></script>
<script type="application/javascript">
    $(function() {
        var myFilters;
        var dtEventTicketsList;

        var onSetFilters = function(caller, filter) {
            if(filter.name === 'start_from'){
                $("#filter-starts-to").datepicker("option", "minDate", $("#filter-starts-from").datepicker("getDate"));
            }
            if(filter.name === 'start_to'){
                $("#filter-starts-from").datepicker("option","maxDate", $("#filter-starts-to").datepicker("getDate"));
            }
            if(filter.name === 'end_from'){
                $("#filter-ends-to").datepicker("option", "minDate", $("#filter-ends-from").datepicker("getDate"));
            }
            if(filter.name === 'end_to'){
                $("#filter-ends-from").datepicker("option","maxDate", $("#filter-ends-to").datepicker("getDate"));
            }
        };
        var onDeleteFilters = function(filter) {
            if(filter.name === 'start_from'){
                $("#filter-starts-to").datepicker("option", "minDate", null);
            }
            if(filter.name === 'start_to'){
                $("#filter-starts-from").datepicker("option","maxDate", null);
            }
            if(filter.name === 'end_from'){
                $("#filter-ends-to").datepicker("option", "minDate", null);
            }
            if(filter.name === 'end_to'){
                $("#filter-ends-from").datepicker("option","maxDate", null);
            }
        };
        var fetchServerData = function(source, data, callback) {
            var onRequestSuccess = function(response, textStatus, jqXHR) {
                if (response.mess_type == 'error') {
                    systemMessages(response.message, response.mess_type);
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
        var onChangeCountry = function(event) {
            var self = $(this);
            var country = self.val() || null;
            var statePlaceholder = statesList.children().first();
            var citiesPlaceholder = citiesList.children().first();
            var onRequestSuccess = function (response) {
                statesList.html(response.states);
                statesList.children().first().replaceWith(statePlaceholder)
                statesList.prop('disabled', false);
            };

            if(statesList.length) {
                statesList.val(null).prop('disabled', true);
                citiesList.val(null).prop('disabled', true);
                citiesList.children().not(citiesPlaceholder).remove();
                $.post(__site_url + 'location/ajax_get_states', { country: country }, null, 'json')
                    .done(onRequestSuccess)
                    .fail(onRequestError);
            }
        };
        var onChangeState = function(event) {
            var self = $(this);
            var region = self.val() || null;
            if(null !== region) {
                citiesList.prop('disabled', false);
            }
        };
        var onCitiesSearchRequest = function (params) {
            return {
                page: params.page,
                search: params.term,
                state: statesList.val() || null,
            };
        };
        var onCitiesSearchResponse = function (data, params) {
            params.page = params.page || 1;
            data.items.forEach(function(item) {
                item.text = item.name;
            });

            return {
                results: data.items,
                pagination: {
                    more: (params.page * data.per_p) < data.total_count
                }
            };
        };
        var onCitiesResultShow = function (e) {
            this.dropdown._positionDropdown();
        };
        var onCreateEvent = function() {
            dtEventTicketsList.fnDraw();
        };
        var onEditEvent = function() {
            dtEventTicketsList.fnDraw(false);
        };

        var datepickers = $(".datepicker-init");
        var countriesList = $('#filter-country');
        var statesList = $('#filter-state');
        var citiesList = $('#filter-city');
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
            sAjaxSource: location.origin + '/cr_events/ajax_list_my_events_dt',
            aoColumnDefs: [
                { sClass: "w-350",        aTargets: ['event_dt'],       mData: "event",          bSortable: true  },
                { sClass: "dn-xl",        aTargets: ['description_dt'], mData: "description",    bSortable: false },
                { sClass: "w-175",        aTargets: ['location_dt'],    mData: "location",       bSortable: false },
                { sClass: "w-100 dn-lg",  aTargets: ['start_dt'],       mData: "starts_at",      bSortable: true  },
                { sClass: "w-100 dn-lg",  aTargets: ['end_dt'],         mData: "ends_at",        bSortable: true  },
                { sClass: "w-40 tac vam", aTargets: ['actions_dt'],     mData: "actions",        bSortable: false },
            ],
            sorting: [[3, 'desc']],
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
        var citiesOptions = {
            ajax: {
                delay: 250,
                type: 'POST',
                dataType: 'json',
                url: __site_url + "location/ajax_get_cities",
                data: onCitiesSearchRequest,
                processResults: onCitiesSearchResponse,
            },
            width: '100%',
            minimumInputLength: 2,
            language: __site_lang,
            theme: "default ep-select2-h30",
        };

        dataT = dtEventTicketsList = $('#dtEventTicketsList').dataTable(datagridOptions);
        dataTableScrollPage(dataT);
        datepickers.datepicker(datepickerOptions);
        countriesList.on('change', onChangeCountry);
        statesList.on('change', onChangeState);
        citiesList.select2(citiesOptions).data('select2').on("results:message", onCitiesResultShow);
        mix(window, {
            onSetFilters: onSetFilters,
            onDeleteFilters: onDeleteFilters,
            callbackAddEvent: onCreateEvent,
            callbackEditEvent: onEditEvent,
        });
    });
</script>
