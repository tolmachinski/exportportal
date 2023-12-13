<script>
    $(function() {
        var onActionStart = function(table, preservePostion) {
            var wrapper = table.closest('.dataTables_wrapper');
            var global = $(window);
            preservePostion = typeof preservePostion !== 'undefined' ? preservePostion : true;

            table.hide();
            wrapper.addClass('h-450');
            showLoader(wrapper);
            if (preservePostion) {
                table.data('scrollPosition', window.scrollY || window.pageYOffset);
                $(window).scrollTop(0);
            }
        };
        var onActionEnd = function(table, preservePostion) {
            var wrapper = table.closest('.dataTables_wrapper');
            preservePostion = typeof preservePostion !== 'undefined' ? preservePostion : true;

            table.show();
            wrapper.removeClass('h-450');
            hideLoader(wrapper);
            if (preservePostion) {
                $(window).scrollTop(table.data('scrollPosition') || 0);
            }
        };
        var updateTables = function(refilter) {
            refilter = typeof refilter !== "undefined" ? refilter : true;

            if ($.fn.dataTable) {
                $.fn.dataTable.tables().forEach(function(table) {
                    $(table)
                        .dataTable()
                        .fnDraw(refilter);
                });
            }
        };
        var sendRequest = function(url, data) {
            return $.post(url, data || null, null, 'json');
        };
        var onPlaceBid = function() {
            updateTables(true);
        };
        var onDeleteBid = function(caller) {
            var button = $(caller);
            var table = button.closest("table.dataTable");
            var bid = button.data("bid") || null;
            var url = __group_site_url + "orders_bids/ajax_operations/delete_bid";
            var onRequestSuccess = function(response) {
                systemMessages(response.message, response.mess_type);
                if ("success" === response.mess_type) {
                    updateTables();
                }
            };

            if (null !== document) {
                onActionStart(table);
                sendRequest(url, {
                        bid: bid
                    })
                    .done(onRequestSuccess)
                    .fail(onRequestError)
                    .always(onActionEnd.bind(null, table));
            }
        };
        var onChangeCountry = function(filters, statesList, citiesList) {
            return function(event) {
                var self = $(this);
                var name = self.prop('name') || '';
                var country = self.val() || null;
                var statePlaceholder = statesList.children().first();
                var citiesPlaceholder = citiesList.children().first();
                var onRequestSuccess = function(response) {
                    statesList.html(response.states);
                    statesList.children().first().replaceWith(statePlaceholder)
                    statesList.prop('disabled', false);
                };

                if (statesList.length) {
                    removeStateFilter(filters, self.closest('.fancybox-skin'), name.replace('_country', '_state'), name.replace('_country', ''), false, true);
                    if (null !== country) {
                        $.post(__group_site_url + 'location/ajax_get_states', {
                                country: country
                            }, null, 'json')
                            .done(onRequestSuccess)
                            .fail(onRequestError);
                    }
                }
            }
        };
        var onChangeState = function(filters, citiesList) {
            return function(event) {
                var self = $(this);
                var name = self.prop('name') || '';
                var region = self.val() || null;

                removeCityFilter(filters, self.closest('.fancybox-skin'), name.replace('_state', '_city'), name.replace('_state', ''), false, true);
                citiesList.prop('disabled', null === region);
            }
        };
        var onCitiesSearchRequest = function(statesList) {
            return function(params) {
                return {
                    page: params.page,
                    search: params.term,
                    state: statesList.val() || null,
                };
            }
        };
        var onCitiesSearchResponse = function(data, params) {
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
        var onCitiesResultShow = function(e) {
            this.dropdown._positionDropdown();
        };
        var onDatagridDraw = function(settings) {
            hideDTbottom(this);
            mobileDataTable($('.main-data-table'));
        };
        var onDatepickerShow = function(input, instance) {
            $('#ui-datepicker-div').addClass('dtfilter-ui-datepicker');
        };
        var removeCoutryFilter = function(filters, container, name, prefix, redraw, chained) {
            redraw = typeof redraw !== 'undefined' ? redraw : false;
            chained = typeof chained !== 'undefined' ? chained : false;
            prefix = prefix || null;
            if (null === prefix) {
                return;
            }

            var statesList = container.find('select[name="' + prefix + '_state"]');
            var countriesList = container.find('select[name="' + name + '"]');

            removeStateFilter(filters, container, prefix + '_state', prefix, redraw, true);
            if (chained) {
                filters.removeFilter(name, redraw);
            }
            statesList.prop('disabled', true);
            countriesList.val(null);
        };
        var removeStateFilter = function(filters, container, name, prefix, redraw, chained) {
            redraw = typeof redraw !== 'undefined' ? redraw : false;
            chained = typeof chained !== 'undefined' ? chained : false;
            prefix = prefix || null;
            if (null === prefix) {
                return;
            }

            var statesList = container.find('select[name="' + name + '"]');
            var citiesList = container.find('select[name="' + prefix + '_city"]');

            removeCityFilter(filters, container, prefix + '_city', prefix, redraw, true);
            if (chained) {
                filters.removeFilter(name, redraw);
            }
            citiesList.prop('disabled', true);
        };
        var removeCityFilter = function(filters, container, name, prefix, redraw, chained) {
            redraw = typeof redraw !== 'undefined' ? redraw : false;
            chained = typeof chained !== 'undefined' ? chained : false;
            prefix = prefix || null;
            if (null === prefix) {
                return;
            }

            var citiesList = container.find('select[name="' + name + '"]');
            var citiesPlaceholder = citiesList.children().first();

            if (chained) {
                filters.removeFilter(name, redraw);
            }
            citiesList.children().not(citiesPlaceholder).remove();
        };

        try {
            mix(window, {
                deleteBid: onDeleteBid,
                callbackPlaceBid: onPlaceBid,
                onChangeCountry: onChangeCountry,
                onChangeState: onChangeState,
                onCitiesSearchRequest: onCitiesSearchRequest,
                onCitiesSearchResponse: onCitiesSearchResponse,
                onCitiesResultShow: onCitiesResultShow,
                onDatagridDraw: onDatagridDraw,
                onDatepickerShow: onDatepickerShow,
                removeCoutryFilter: removeCoutryFilter,
                removeStateFilter: removeStateFilter,
                removeCityFilter: removeCityFilter
            });
        } catch (error) {
            if (__debug_mode) {
                if (!error instanceof TypeError) {
                    console.log(error);
                }
            }
        }
    });
</script>