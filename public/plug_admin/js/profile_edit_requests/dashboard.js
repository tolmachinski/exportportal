var ProfileEditRequestDashboardModule = (function () {
    "use strict";

    //#region Declarations
    /**
     * @typedef {Object} ModuleParameters
     * @property {string} [listUrl]
     * @property {string} [datagrid]
     * @property {string} [tableFilter]
     * @property {string} [activeFilters]
     * @property {string} [datepickerFields]
     */

    /**
     * @typedef {Object} TableHandler
     * @property {JQuery} [table]
     * @property {any} [instance]
     * @property {any} [api]
     */
    //#endregion Declarations

    //#region Variables
    var defaultDatagridOptions = {
        sDom: '<"top"lp>rt<"bottom"ip><"clear">',
        bProcessing: true,
        bServerSide: true,
        sAjaxSource: null,
        aoColumnDefs: [
            { sClass: "w-75 tac vam", aTargets: ["dt-request"], mData: "request", bSortable: false },
            { sClass: "w-275 vam", aTargets: ["dt-user"], mData: "user", bSortable: false },
            { sClass: "w-120 tac vam", aTargets: ["dt-status"], mData: "status", bSortable: false },
            { sClass: "tal vam", aTargets: ["dt-reason"], mData: "reason", bSortable: false },
            { sClass: "w-120 tac vam", aTargets: ["dt-created-at"], mData: "createdAt", bSortable: true },
            { sClass: "w-120 tac vam", aTargets: ["dt-updated-at"], mData: "updatedAt", bSortable: true },
            { sClass: "w-120 tac vam", aTargets: ["dt-accepted-at"], mData: "acceptedAt", bSortable: true },
            { sClass: "w-120 tac vam", aTargets: ["dt-declined-at"], mData: "declinedAt", bSortable: true },
            { sClass: "w-80 tac vam", aTargets: ['dt-actions'], mData: "actions", bSortable: false }
        ],
        sorting: [[4, "desc"]],
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
            min: function (element, value) {
                element.prop("min", value);
            },
            max: function (element, value) {
                element.prop("max", value);
            },
        },
        date: {
            min: function (element, value) {
                element.prop("min", value);
            },
            max: function (element, value) {
                element.prop("max", value);
            },
        },
        datepicker: {
            min: function (element, value) {
                element.datepicker("option", "minDate", value);
            },
            max: function (element, value) {
                element.datepicker("option", "maxDate", value);
            },
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
     * Handler that is run on accept or decline of the request.
     */
    function onAcceptOrDeline(datagrid) {
        datagrid.draw();
    }

    /**
     * Module entrypoint.
     *
     * @param {ModuleParameters} params
     */
    function entrypoint(params) {
        var listUrl = params.listUrl || null;
        var datagrid = $(params.datagrid || null);
        var tableFilter = params.tableFilter || null;
        var activeFilters = params.activeFilters || null;
        var datepickerFields = $(params.datepickerFields || null);
        /** @type {TableHandler} */
        var tableHandler = { table: null, api: null, instance: null };
        var filters = null;

        //#region Deploy plugins
        if (null !== tableFilter) {
            filters = createFilters(tableFilter, tableHandler, activeFilters ? { container: activeFilters } : null);
        }
        if (0 !== datagrid.length && $.fn.DataTable) {
            tableHandler.instance = datagrid.dataTable(
                Object.assign({}, defaultDatagridOptions, {
                    sAjaxSource: listUrl,
                    fnServerData: fetchList.bind(null, filters),
                })
            );
            tableHandler.table = datagrid;
            tableHandler.api = tableHandler.instance.api();
        }

        if (0 !== datepickerFields.length && $.fn.datepicker) {
            datepickerFields.datepicker({
                beforeShow: function (input, instance) {
                    if (instance.dpDiv && instance.dpDiv.length) {
                        instance.dpDiv.addClass("dtfilter-ui-datepicker");
                    }
                },
            });
        }
        //#endregion Deploy plugins

        $(globalThis).on('preview-profile-edit-request::accepted', onAcceptOrDeline.bind(undefined, tableHandler.api));
        $(globalThis).on('preview-profile-edit-request::declined', onAcceptOrDeline.bind(undefined, tableHandler.api));
    }

    return { default: entrypoint };
})();
