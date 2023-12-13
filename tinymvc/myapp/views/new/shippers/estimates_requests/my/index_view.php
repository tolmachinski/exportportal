<?php tmvc::instance()->controller->view->display('new/filter_panel_main_view', array('filter_panel' => 'new/shippers/estimates_requests/my/filter_panel_view')); ?>

<div class="container-center dashboard-container">
    <div class="dashboard-line">
        <h1 class="dashboard-line__ttl"><?php echo translate("shipping_estimates_dashboard_title_text"); ?></h1>

        <div class="dashboard-line__actions">
            <a class="btn btn-dark btn-filter fancybox btn-counter"
                data-fancybox-href="#dtfilter-hidden"
                data-title="<?php echo translate("general_dt_filters_modal_title"); ?>"
                data-mw="740"
                title="<?php echo translate("general_dt_filters_button_title"); ?>">
                <i class="ep-icon ep-icon_filter"></i> <?php echo translate("general_dt_filters_button_text"); ?>
            </a>
        </div>
    </div>

    <table class="main-data-table" id="dtShippingRequests">
        <thead>
            <tr>
                <th class="estimate_dt"><?php echo translate("shipping_estimates_dashboard_dt_column_estimate_request_text"); ?></th>
                <th class="destination_dt"><?php echo translate("shipping_estimates_dashboard_dt_column_destination_text"); ?></th>
                <th class="created_dt"><?php echo translate("shipping_estimates_dashboard_dt_column_create_date_text"); ?></th>
                <th class="updated_dt"><?php echo translate("shipping_estimates_dashboard_dt_column_update_date_text"); ?></th>
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
        var dtShippingRequests;

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
            if(filter.name === 'countdown_from'){
                $("#filter-countdown-to").datepicker("option", "minDate", $("#filter-countdown-from").datepicker("getDate"));
            }
            if(filter.name === 'countdown_to'){
                $("#filter-countdown-from").datepicker("option","maxDate", $("#filter-countdown-to").datepicker("getDate"));
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
            if(filter.name === 'countdown_from'){
                $("#filter-countdown-to").datepicker("option", "minDate", null);
            }
            if(filter.name === 'countdown_to'){
                $("#filter-countdown-from").datepicker("option","maxDate", null);
            }
            if(filter.name === 'from_city' || filter.name === 'to_city'){
                var select = $('.dtfilter-popup select[name="' + filter.name + '"]');
                if(select.length) {
                    select.val('').trigger('change');
                }
            }
            if(filter.name === 'group_key'){
                var url = __site_url + 'shippers/estimates_requests';
                if(
                    typeof window.history !== 'undefined' &&
                    typeof window.history.pushState !== 'undefined'
                ){
                    var value = null;
                    var segments = {};
                    var parts = location.pathname.split('/').filter(function(p) { return p; });
                    for (var i = 0; i < parts.length; i = i + 2) {
                        var part = parts[i];
                        segments[part] = parts[i + 1] || null;
                    }
                    if(segments.hasOwnProperty('group')) {
                        value = segments.group || null;
                    }

                    history.replaceState({ filter: { name: filter.name, value: value } }, $('title').text(), location.href);
                    history.pushState({ filter: { name: filter.name, value: null } }, $('title').text(), url);
                } else {
                    window.location.href = url;
                }
            }
        };
        var onDeleteEstimate = function(caller) {
            var button = $(caller);
            var estimate = button.data('estimate') || null;
            var url = __site_url + 'shippers/ajax_shippers_operation/delete_estimate';
            var onRequestSuccess = function(response) {
                systemMessages(response.message, response.mess_type);
                if('success' === response.mess_type) {
                    dtShippingRequests.fnDraw();
                }
            }

            if(null !== estimate) {
                $.post(url, { id: estimate }, null, 'json').done(onRequestSuccess).fail(onRequestError);
            }
        };
        var onSaveEstimate = function(caller) {
            var button = $(caller);
            var estimate = button.data('estimate') || null;
            var url = __site_url + 'shippers/ajax_shippers_operation/save_for_order';
            var onRequestSuccess = function(response) {
                systemMessages(response.message, response.mess_type);
                if('success' === response.mess_type) {
                    dtShippingRequests.fnDraw(false);
                }
            }

            if(null !== estimate) {
                $.post(url, { id: estimate }, null, 'json').done(onRequestSuccess).fail(onRequestError);
            }
        };
        var onRenewEstimate = function(caller) {
            var button = $(caller);
            var estimate = button.data('estimate') || null;
            var url = __site_url + 'shippers/ajax_shippers_operation/renew_estimate';
            var onRequestSuccess = function(response) {
                systemMessages(response.message, response.mess_type);
                if('success' === response.mess_type) {
                    dtShippingRequests.fnDraw(false);
                }
            }

            if(null !== estimate) {
                $.post(url, { id: estimate }, null, 'json').done(onRequestSuccess).fail(onRequestError);
            }
        };
        var onChangeCountry = function(statesList, citiesList) {
            return function(event) {
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
            }
        };
        var onChangeState = function(citiesList) {
            return function(event) {
                var self = $(this);
                var region = self.val() || null;
                if(null !== region) {
                    citiesList.prop('disabled', false);
                }
            }
        };
        var onCitiesSearchRequest = function(statesList) {
            return function (params) {
                return {
                    page: params.page,
                    search: params.term,
                    state: statesList.val() || null,
                };
            }
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
        var fetchServerData = function(source, data, callback) {
            var onRequestSuccess = function(response, textStatus, jqXHR) {
                if (response.mess_type == 'error') {
                    systemMessages(response.message, response.mess_type);
                }

                callback(response, textStatus, jqXHR);
            };

            $.post(source, data.concat(myFilters.getDTFilter()), null, 'json').done(onRequestSuccess).fail(onRequestError);
        };
        var onDatagridDraw = function(settings) {
            hideDTbottom(this);
            mobileDataTable($('.main-data-table'));
        };
        var onDatepickerShow = function(input, instance) {
            $('#ui-datepicker-div').addClass('dtfilter-ui-datepicker');
        };
        var onGoBack = function(e) {
            var state = e.state || {};
            if(typeof state === 'object' && state.hasOwnProperty('filter')) {
                var filter = $('[name="' + state.filter.name + '"], [data-name="' + state.filter.name + '"]');
                if (filter.length) {
                    filter.val(state.filter.value);
                    filter.trigger('change');
                }
            }
        };

        var datepickers = $(".datepicker-init");
        var initialCountriesList = $('#filter-from-country');
        var initialStatesList = $('#filter-from-state');
        var initialCitiesList = $('#filter-from-city');
        var finalCountriesList = $('#filter-to-country');
        var finalStatesList = $('#filter-to-state');
        var finalCitiesList = $('#filter-to-city');
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
            sAjaxSource: location.origin + '/shippers/ajax_estimates_requests_list_dt',
            aoColumnDefs: [
                { sClass: "w-350",        aTargets: ['estimate_dt'],    mData: "estimate",    bSortable: true  },
                { sClass: "dn-xl",        aTargets: ['destination_dt'], mData: "destination", bSortable: false },
                { sClass: "w-100 dn-lg",  aTargets: ['created_dt'],     mData: "created_at",  bSortable: true  },
                { sClass: "w-100 dn-lg",  aTargets: ['updated_dt'],     mData: "updated_at",  bSortable: true  },
                { sClass: "w-40 tac vam", aTargets: ['actions_dt'],     mData: "actions",     bSortable: false },
            ],
            sorting: [[2, 'desc']],
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
        var initialCitiesOptions = {
            ajax: {
                delay: 250,
                type: 'POST',
                dataType: 'json',
                url: __site_url + "location/ajax_get_cities",
                data: onCitiesSearchRequest(initialStatesList),
                processResults: onCitiesSearchResponse,
            },
            width: '100%',
            minimumInputLength: 2,
            language: __site_lang,
            theme: "default ep-select2-h30",
        };
        var finalCitiesOptions = {
            ajax: {
                delay: 250,
                type: 'POST',
                dataType: 'json',
                url: __site_url + "location/ajax_get_cities",
                data: onCitiesSearchRequest(finalStatesList),
                processResults: onCitiesSearchResponse,
            },
            width: '100%',
            minimumInputLength: 2,
            language: __site_lang,
            theme: "default ep-select2-h30",
        };

        myFilters = initDtFilter();
        dataT = dtShippingRequests = $('#dtShippingRequests').dataTable(datagridOptions);
        dataTableScrollPage(dataT);
        datepickers.datepicker(datepickerOptions);
        initialCountriesList.on('change', onChangeCountry(initialStatesList, initialCitiesList));
        finalCountriesList.on('change', onChangeCountry(finalStatesList, finalCitiesList));
        initialStatesList.on('change', onChangeState(initialCitiesList));
        finalStatesList.on('change', onChangeState(finalCitiesList));
        initialCitiesList.select2(initialCitiesOptions).data('select2').on("results:message", onCitiesResultShow);
        finalCitiesList.select2(finalCitiesOptions).data('select2').on("results:message", onCitiesResultShow);
        mix(window, {
            onSetFilters: onSetFilters,
            onDeleteFilters: onDeleteFilters,
            saveEstimate: onSaveEstimate,
            renewEstimate: onRenewEstimate,
            deleteEstimate: onDeleteEstimate,
        });
        window.addEventListener('popstate', onGoBack);
    });
</script>
