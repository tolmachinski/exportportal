var ProductRequestDashboardModule = (function () {
    "use strict";

    //#region Declarations
    /**
     * @typedef {Object} ModuleParameters
     * @property {string} [listUrl]
     * @property {any} [selectors]
     */

    /**
     * @typedef {Object} TableHandler
     * @property {JQuery} [table]
     * @property {any} [api]
     */

    /**
     * @typedef {Object} CustomElements
     * @property {JQuery} [table]
     * @property {JQuery} [wrapper]
     * @property {JQuery} [datepickerFields]
     */

    /**
     * @typedef {Object} Selectors
     * @property {string} [table]
     * @property {string} [wrapper]
     * @property {string} [tableFilter]
     * @property {string} [activeFilters]
     * @property {string} [datepickerFields]
     */
    //#endregion Declarations

    //#region Variables
    /**
     * @type {CustomElements}
     */
    var defaultElements = { table: null, wrapper: null, datepickerFields: null };

    /**
     * @type {Selectors}
     */
    var defaultSelectors = { table: null, wrapper: null, tableFilter: null, activeFilters: null, datepickerFields: null };

    var defaultDatagridOptions = {
        sDom: '<"top"lp>rt<"bottom"ip><"clear">',
        bProcessing: true,
        bServerSide: true,
        sAjaxSource: null,
        aoColumnDefs: [
            { sClass: "w-75 tac vam", aTargets: ["dt-request"], mData: "request", bSortable: false },
            { sClass: "w-275 tac vam", aTargets: ["dt-user"], mData: "user", bSortable: false },
            { sClass: "tac vat", aTargets: ["dt-product"], mData: "product", bSortable: false },
            { sClass: "w-120 tac vam", aTargets: ["dt-quantity"], mData: "quantity", bSortable: true },
            { sClass: "w-120 tac vam", aTargets: ["dt-start-price"], mData: "start_price", bSortable: true },
            { sClass: "w-120 tac vam", aTargets: ["dt-final-price"], mData: "final_price", bSortable: true },
            { sClass: "w-150 tac vam", aTargets: ["dt-departure-country"], mData: "departure_country", bSortable: false },
            { sClass: "w-150 tac vam", aTargets: ["dt-destination-country"], mData: "destination_country", bSortable: false },
            { sClass: "w-120 tac vam", aTargets: ["dt-created-at"], mData: "created_at", bSortable: true },
            { sClass: "w-120 tac vam", aTargets: ["dt-updated-at"], mData: "updated_at", bSortable: true },
        ],
        sorting: [[8, "desc"]],
        fnServerData: null,
        fnDrawCallback: null,
    };

    var defaultFiltersOptions = {
        debug: true,
        container: ".dtfilter-list",
        txtResetBtn: "Reset",
        callBack: $.noop,
        onActive: $.noop,
        onSet: $.noop,
        onReset: $.noop,
        onDelete: $.noop,
        beforeSet: $.noop,
    };

    var mutators = {
        number: {
            min: function (element, value) { element.prop("min", value); },
            max: function (element, value) { element.prop("max", value); },
        },
        date: {
            min: function (element, value) { element.prop("min", value); },
            max: function (element, value) { element.prop("max", value); },
        },
        datepicker: {
            min: function (element, value) { element.datepicker("option", "minDate", value); },
            max: function (element, value) { element.datepicker("option", "maxDate", value); },
        },
    };
    //#endregion Variables

    /**
     * Handler called on setting of filter.
     *
     * @param {HTMLElement} element
     * @param {any} filter
     */
    function onSetFilter(element, filter) {
        var field = $(element);
        var hasPair = Boolean(~~parseInt(field.data("entryPair"), 10));
        var entryType = field.data("entryType") || "raw";
        if (hasPair) {
            var action = field.data("entryPairAction") || null;
            var selector = field.data("entryPairSelector") || null;
            var pairField = null !== selector ? $(selector) : null;
            if (pairField.length && mutators[entryType] && mutators[entryType][action]) {
                mutators[entryType][action].call(null, pairField, filter.value);
            }
        }
    }

    /**
     * Handler called on setting of filter.
     *
     * @param {any} filter
     * @param {HTMLElement} element
     * @param {any} meta
     */
    function onDeleteFilter(filter, element, meta) {
        var field = $(element);
        var hasPair = Boolean(~~parseInt(field.data("entryPair"), 10));
        var entryType = field.data("entryType") || "raw";
        if (hasPair) {
            var action = field.data("entryPairAction") || null;
            var selector = field.data("entryPairSelector") || null;
            var pairField = null !== selector ? $(selector) : null;
            if (pairField.length && mutators[entryType] && mutators[entryType][action]) {
                mutators[entryType][action].call(null, pairField, null);
            }
        }
    }

    /**
     * Creates the page filters.
     *
     * @param {string} filterSelector
     * @param {TableHandler} tableHandler
     * @param {any} options
     */
    function createFilters(filterSelector, tableHandler, options) {
        if (null === filterSelector || !$.fn.dtFilters) {
            return null;
        }

        return $(filterSelector).dtFilters(
            filterSelector,
            Object.assign(
                {},
                defaultFiltersOptions,
                {
                    callBack: function () {
                        if (tableHandler.api) {
                            tableHandler.api.draw();
                        }
                    },
                    onSet: onSetFilter,
                    onDelete: onDeleteFilter,
                },
                options || {}
            )
        );
    }

    function fetchList(filters, source, data, callback) {
        var baseState = { aaData: [], iTotalRecords: 0, iTotalDisplayRecords: 0 };
        var onRequestSuccess = function (response, textStatus, jqXHR) {
            if (response.mess_type !== "success") {
                systemMessages(response.message, response.mess_type);
            }

            callback(Object.assign({}, baseState, response || {}), textStatus, jqXHR);
        };
        var onRequestFail = function (jqXHR, textStatus, errorThrown) {
            onRequestError(jqXHR, textStatus, errorThrown);
            callback(baseState, textStatus, jqXHR);
        };

        return postRequest(source, !filters ? data : data.concat(filters.getDTFilter()), null, "json")
            .then(onRequestSuccess)
            .catch(onRequestFail);
    }

    /**
     * Module entrypoint.
     *
     * @param {ModuleParameters} params
     */
    function entrypoint(params) {
        var listUrl = params.listUrl || null;
        /** @type {Selectors} */
        var selectors = Object.assign({}, defaultSelectors, params.selectors || {});
        /** @type {CustomElements} */
        var elements = Object.assign({}, defaultElements, findElementsFromSelectors(selectors, Object.keys(defaultElements)));
        /** @type {TableHandler} */
        var tableHandler = { table: null, api: null };
        var filters = null;

        //#region Deploy plugins
        if (null !== selectors.tableFilter) {
            filters = createFilters(selectors.tableFilter, tableHandler, selectors.activeFilters ? { container: selectors.activeFilters } : null);
        }

        if (null !== elements.table && $.fn.DataTable) {
            tableHandler.table = elements.table;
            tableHandler.api = elements.table.DataTable(
                Object.assign({}, defaultDatagridOptions, {
                    sAjaxSource: listUrl,
                    fnServerData: fetchList.bind(null, filters),
                })
            );
        }

        if (null !== elements.datepickerFields && $.fn.datepicker) {
            elements.datepickerFields.datepicker({
                beforeShow: function (input, instance) {
                    if (instance.dpDiv && instance.dpDiv.length) {
                        instance.dpDiv.addClass("dtfilter-ui-datepicker");
                    }
                },
            });
        }
        //#endregion Deploy plugins

        return {};
    }

    return { default: entrypoint };
})();
