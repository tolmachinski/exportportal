<?php tmvc::instance()->controller->view->display('new/filter_panel_main_view', array('filter_panel' => 'new/epl/orders_bids/upcoming/filter_panel_view')); ?>

<div class="container-center dashboard-container">
    <div class="dashboard-line">
        <h1 class="dashboard-line__ttl">Upcoming orders</h1>

        <div class="dashboard-line__actions">
            <a class="btn btn-dark btn-filter fancybox"
                data-fancybox-href="#dtfilter-hidden"
                data-title="<?php echo translate("general_dt_filters_modal_title"); ?>"
                data-mw="740"
                title="<?php echo translate("general_dt_filters_button_title"); ?>">
                <i class="ep-icon ep-icon_filter"></i> <?php echo translate("general_dt_filters_button_text"); ?>
            </a>
        </div>
    </div>

    <div class="info-alert-b mt-15">
        <i class="ep-icon ep-icon_info-stroke"></i>
        <span>
            Upcoming orders' page includes the list of all the <strong>recently created orders</strong>, within your shipping area, which might interest you.
            <br>
            <strong>Place a bid</strong> if you would like to ship one of these orders.
        </span>
    </div>

    <table class="main-data-table" id="dtUpcomingOrdersList">
        <thead>
            <tr>
                <th class="order_dt">Order</th>
                <th class="incoterms_dt">Incoterm</th>
                <th class="location_dt">Shipping location</th>
                <th class="created_dt">Created</th>
                <th class="updated_dt">Updated</th>
                <th class="expires_dt">Expires</th>
                <th class="actions_dt"></th>
            </tr>
        </thead>
        <tbody class="tabMessage"></tbody>
    </table>
</div>

<script src="<?php echo fileModificationTime('public/plug/jquery-datatables-1-10-12/jquery.dataTables.min.js');?>"></script>
<script src="<?php echo fileModificationTime('public/plug/jquery-dtfilters/jquery.dtFilters.js');?>"></script>
<script src="<?php echo fileModificationTime('public/plug/js/dt.scripts.js');?>"></script>
<?php views()->display('new/epl/orders_bids/common/scripts_view'); ?>
<script>
    $(function() {
        var beforeSetFilters = function (caller) {
            if (typeof filterPreMutators !== 'undefined') {
                filterPreMutators.handle({ name: caller.prop("name"), node: caller });
            }
        };
        var onSetFilters = function (caller, filter) {
            if (typeof filterMutators !== 'undefined') {
                filterMutators.handle(filter);
            }
        };
        var onDeleteFilters = function(filter) {
            if (typeof filterCleaners !== 'undefined') {
                filterCleaners.handle(filter);
            }
        };
        var fetchServerData = function(source, data, callback) {
            var onRequestSuccess = function(response, textStatus, jqXHR) {
                if (response.mess_type !== 'success') {
                    systemMessages(response.message, response.mess_type);
                }

                callback(
                    $.extend({ aaData: [], iTotalRecords: 0, iTotalDisplayRecords: 0 }, response || {}),
                    textStatus,
                    jqXHR
                );
            };
            var onRequestFail = function (jqXHR, textStatus, errorThrown) {
                onRequestError(jqXHR, textStatus, errorThrown);
                callback(
                    { aaData: [], iTotalRecords: 0, iTotalDisplayRecords: 0 },
                    textStatus,
                    jqXHR
                );
            }

            $.post(source, data.concat(myFilters.getDTFilter()), null, 'json')
                .done(onRequestSuccess)
                .fail(onRequestFail);
        };

        var myFilters;
        var dtUpcomingOrdersList;
        var datepickers = $(".datepicker-init");
        var departureCountriesList = $('#filter-from-country');
        var departureStatesList = $('#filter-from-state');
        var departureCitiesList = $('#filter-from-city');
        var destinationCountriesList = $('#filter-to-country');
        var destinationStatesList = $('#filter-to-state');
        var destinationCitiesList = $('#filter-to-city');
        var filterPreMutators = new DTFilters.Bindings(
            [
                { name: 'order',   op: [{ name: 'maskNumber' }]},
            ],
            {
                'maskNumber': function() {
                    var value = this.node.val() || null;
                    var number = null !== value ? toOrderNumber(value) : null;
                    if(number){
                        this.node.val(number);
                    } else{
                        this.node.val('');

                        return false;
                    }
                }
            }
        );
        var filterMutators = new DTFilters.Bindings(
            [
                { name: 'created_from',  source: '#filter-created-from',  op: [{ name: 'min', target: '#filter-created-to'   }]},
                { name: 'created_to',    source: '#filter-created-to',    op: [{ name: 'max', target: '#filter-created-from' }]},
                { name: 'updated_from',  source: '#filter-updated-from',  op: [{ name: 'min', target: '#filter-updated-to'   }]},
                { name: 'updated_to',    source: '#filter-updated-to',    op: [{ name: 'max', target: '#filter-updated-from' }]},
                { name: 'expires_from',  source: '#filter-expires-from',  op: [{ name: 'min', target: '#filter-expires-to'   }]},
                { name: 'expires_to',    source: '#filter-expires-to',    op: [{ name: 'max', target: '#filter-expires-from' }]},
            ],
            {
                min: function (source, target) { target.datepicker("option", "minDate", source.datepicker("getDate")); },
                max: function (source, target) { target.datepicker("option", "maxDate", source.datepicker("getDate")); }
            }
        );
        var filterCleaners = new DTFilters.Bindings(
            [
                { name: 'created_from',  op: [{ name: 'min', target: '#filter-created-to'    }]},
                { name: 'created_to',    op: [{ name: 'max', target: '#filter-created-from'  }]},
                { name: 'updated_from',  op: [{ name: 'min', target: '#filter-updated-to'    }]},
                { name: 'updated_to',    op: [{ name: 'max', target: '#filter-updated-from'  }]},
                { name: 'expires_from',  op: [{ name: 'min', target: '#filter-expires-to'    }]},
                { name: 'expires_to',    op: [{ name: 'max', target: '#filter-expires-from'  }]},
                { name: 'from_country',  op: [{ name: 'clearCountry' }]},
                { name: 'to_country',    op: [{ name: 'clearCountry' }]},
                { name: 'from_state',    op: [{ name: 'clearState'   }]},
                { name: 'to_state',      op: [{ name: 'clearState'   }]},
                { name: 'from_city',     op: [{ name: 'clearCity'    }]},
                { name: 'to_city',       op: [{ name: 'clearCity'    }]},
                { name: 'bid',           op: [{ name: 'clearUrl'     }]},
                { name: 'order',         op: [{ name: 'clearUrl'     }]},
                { name: 'status',        op: [{ name: 'clearUrl'     }]},
            ],
            {
                min: function (source, target) { target.datepicker("option", "minDate", null); },
                max: function (source, target) { target.datepicker("option", "maxDate", null); },
                clearUrl: function () { DTFilters.Util.rewriteHystory(__group_site_url +  DTFilters.Util.dropPathParams(location.pathname, this.name), this); },
                clearCity: function () { removeCityFilter(myFilters, $('.dtfilter-popup'), this.name, this.name.replace('_city', '')); },
                clearState: function () { removeStateFilter(myFilters, $('.dtfilter-popup'), this.name, this.name.replace('_state', '')); },
                clearCountry: function () { removeCoutryFilter(myFilters, $('.dtfilter-popup'), this.name, this.name.replace('_country', '')); }
            }
        );
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
            sAjaxSource: location.origin + '/orders_bids/ajax_operations/upcoming_orders',
            aoColumnDefs: [
                { sClass: "w-350",        aTargets: ['order_dt'],     mData: "order_dt",      bSortable: false },
                { sClass: "w-130 dn-lg",  aTargets: ['incoterms_dt'], mData: "incoterms_dt",  bSortable: false },
                { sClass: "dn-xl",        aTargets: ['location_dt'],  mData: "location_dt",   bSortable: false },
                { sClass: "w-100 dn-lg",  aTargets: ['created_dt'],   mData: "created_dt", bSortable: true  },
                { sClass: "w-100 dn-lg",  aTargets: ['updated_dt'],   mData: "updated_dt", bSortable: true  },
                { sClass: "w-100 dn-lg",  aTargets: ['expires_dt'],   mData: "expires_dt", bSortable: true  },
                { sClass: "w-40 tac vam", aTargets: ['actions_dt'],   mData: "actions_dt",    bSortable: false },
            ],
            sorting: [[5, 'desc']],
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
                url: __group_site_url + "location/ajax_get_cities",
                data: onCitiesSearchRequest(departureStatesList),
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
                url: __group_site_url + "location/ajax_get_cities",
                data: onCitiesSearchRequest(destinationStatesList),
                processResults: onCitiesSearchResponse,
            },
            width: '100%',
            minimumInputLength: 2,
            language: __site_lang,
            theme: "default ep-select2-h30",
        };

        myFilters = initDtFilter();
        dataT = dtUpcomingOrdersList = $('#dtUpcomingOrdersList').dataTable(datagridOptions);
        datepickers.datepicker(datepickerOptions);
        departureCountriesList.on('change', onChangeCountry(myFilters, departureStatesList, departureCitiesList));
        destinationCountriesList.on('change', onChangeCountry(myFilters, destinationStatesList, destinationCitiesList));
        departureStatesList.on('change', onChangeState(myFilters, departureCitiesList));
        destinationStatesList.on('change', onChangeState(myFilters, destinationCitiesList));
        departureCitiesList.select2(initialCitiesOptions).data('select2').on("results:message", onCitiesResultShow);
        destinationCitiesList.select2(finalCitiesOptions).data('select2').on("results:message", onCitiesResultShow);

        dataTableScrollPage(dataT);
        mix(window, {
            onSetFilters: onSetFilters,
            onDeleteFilters: onDeleteFilters,
            beforeSetFilters: beforeSetFilters
        });
    });
</script>
