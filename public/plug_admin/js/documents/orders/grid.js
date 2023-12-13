var DocumentsGridModule = (function () {
    "use strict";

    /**
     * Default datatable options
     */
    var datagridOptions = {
        sDom: '<"top"lp>rt<"bottom"ip><"clear">',
        bProcessing: false,
        bServerSide: true,
        aoColumnDefs: [
            { sClass: "w-100 tac vat", aTargets: "dt-details", mData: "showDetails", bSortable: false },
            { sClass: "w-100 tac vat", aTargets: "dt-order", mData: "order", bSortable: false },
            { sClass: "w-200 tac vat", aTargets: "dt-sender", mData: "sender", bSortable: false },
            { sClass: "w-150 tac vat", aTargets: "dt-status", mData: "status", bSortable: true },
            { sClass: "w-150 tac vat", aTargets: "dt-type", mData: "type", bSortable: true },
            { sClass: "w-250 vat", aTargets: "dt-envelope", mData: "envelope", bSortable: false },
            { sClass: "dn-xl vat", aTargets: "dt-description", mData: "description", bSortable: false },
            { sClass: "w-150 tac vam dn-lg", aTargets: "dt-created-at", mData: "createdAt", bSortable: true },
            { sClass: "w-150 tac vam dn-lg", aTargets: "dt-updated-at", mData: "updatedAt", bSortable: true },
            { sClass: "w-40 tac vam", aTargets: "dt-actions", mData: "actions", bSortable: false },
        ],
        sorting: [[7, "desc"]],
        sPaginationType: "full_numbers",
        language: {
            url: location.origin + "/public/plug/jquery-datatables-1-10-12/i18n/" + __site_lang + ".json",
            paginate: {
                previous: '<i class="ep-icon ep-icon_arrows-left"></i>',
                first: '<i class="ep-icon ep-icon_arrow-left"></i>',
                next: '<i class="ep-icon ep-icon_arrows-right"></i>',
                last: '<i class="ep-icon ep-icon_arrow-right"></i>',
            },
        },
    };

    /**
     * Default filters options
     */
    var filterOptions = {
        container: ".wr-filter-list",
    };

    /**
     * Loads the dependencies for the datatable.
     */
    function loadDependencies() {
        return Promise.all([
            getScript("public/plug_admin/jquery-datatables-1-10-12/jquery.dataTables.min.js", true),
            getScript("public/plug_admin/file-saver-2-0-2/CustomFileSaver.min.js", true),
            getScript("public/plug_admin/jquery-dtfilters/jquery.dtFilters.js", true),
        ]);
    }

    /**
     * Fetches data for datatable from server.
     *
     * @param {any} filters
     * @param {string} source
     * @param {any} data
     * @param {Function} callback
     */
    function fetchServerData(filters, source, data, callback) {
        var onRequestSuccess = function (response, textStatus, jqXHR) {
            if (response.mess_type == "error") {
                systemMessages(response.message, response.mess_type);
            }

            callback($.extend({ aaData: [], iTotalRecords: 0, iTotalDisplayRecords: 0 }, response || {}), textStatus, jqXHR);
        };
        var onRequestFail = function (jqXHR, textStatus, errorThrown) {
            onRequestError(jqXHR, textStatus, errorThrown);
            callback({ aaData: [], iTotalRecords: 0, iTotalDisplayRecords: 0 }, textStatus, jqXHR);
        };
        var requestData = data.concat(
            [{ name: "mode", value: "legacy" }],
            filters.getDTFilter().map(function (filter) {
                return { name: "filters[" + filter.name + "]", value: filter.value };
            })
        );

        return postRequest(source, requestData).then(onRequestSuccess).catch(onRequestFail);
    }

    /**
     * Renders the details for the datatable row.
     *
     * @param {any} data
     */
    function renderRowDetails(data) {
        return (
            '<div class="dt-details">' +
            '<table class="dt-details__table">' +
            (data.details || [])
                .map(function (entry) {
                    return '<tr><td class="w-200">' + entry.column + "</td><td>" + entry.value + "</td></tr>";
                })
                .join("") +
            "</table>" +
            "</div>"
        );
    }

    /**
     * Download the envelope document.
     *
     * @param {number} envelopeId
     * @param {number} documentId
     * @param {string|URL} url
     */
    function downloadDocuments(envelopeId, documentId, url) {
        return postRequest(url, { envelope: envelopeId, document: documentId }, "json")
            .then(function (response) {
                var file = response.file || null;
                var message = response.message || null;
                var messageType = response.mess_type || null;
                if (message) {
                    systemMessages(message, messageType);
                }
                if (!file) {
                    return;
                }

                saveAs(file.url, file.name);
            })
            .catch(function (e) {
                onRequestError(e);
            });
    }

    /**
     * Opens a tab that allows to edit
     *
     * @param {JQuery} button
     * @param {number} envelopeId
     * @param {string|URL} url
     * @param {DataTables.JQueryDataTables} datagrid
     */
    function openTabsEditor(envelopeId, url, datagrid)
    {
        var currentWindow = $(window);
        var redirectUrl = url + '/' + envelopeId;

        openCenteredWindow(redirectUrl, "_blank", currentWindow.width(), currentWindow.height());
        currentWindow.one("edit-tabs:finished", function () { datagrid.fnDraw(true); });
        currentWindow.one("edit-tabs:error", function (e) {
            if (typeof e.detail.error !== 'undefined') {
                onRequestError(e.detail.error);
            }
        });
    }

    /**
     * Handlers that shows the details for datatable row
     */
    function showDetailsRow(button, datagrid) {
        var row = button.closest("tr").get(0);

        if (datagrid.fnIsOpen(row)) {
            datagrid.fnClose(row);
        } else {
            datagrid.fnOpen(row, renderRowDetails(datagrid.fnGetData(row)), "details");
        }

        button.toggleClass("ep-icon_plus ep-icon_minus");
    }

    var createFilters = function (selector, options, activeFilters) {
        return $(selector).dtFilters(selector, Object.assign({}, filterOptions, options || {}), activeFilters || []);
    };

    /**
     * Synchronizes the date filters.
     *
     * @param {any} filterTypes
     * @param {EasyFilter.Filter} filter
     */
    function syncDateFilters(filterTypes, filter) {
        var minDate = function (source, target) {
            target.datepicker("option", "minDate", source.datepicker("getDate") || null);
        };
        var maxDate = function (source, target) {
            target.datepicker("option", "maxDate", source.datepicker("getDate") || null);
        };
        var transformers = {
            [filterTypes.createdFrom.name]: function () {
                minDate($(filterTypes.createdFrom.selector), $(filterTypes.createdTo.selector));
            },
            [filterTypes.updatedFrom.name]: function () {
                minDate($(filterTypes.updatedFrom.selector), $(filterTypes.updatedTo.selector));
            },
            [filterTypes.createdTo.name]: function () {
                maxDate($(filterTypes.createdTo.selector), $(filterTypes.createdFrom.selector));
            },
            [filterTypes.updatedTo.name]: function () {
                maxDate($(filterTypes.updatedTo.selector), $(filterTypes.updatedFrom.selector));
            },
        };

        var transformer = transformers[filter.name] || null;
        if (null !== transformer) {
            transformer();
        }
    }

    /**
     * Consent button click handler.
     *
     * @param {JQuery.Event} e
     */
    function onRequestConsent(e) {
        e.preventDefault();

        var opener = $(window);
        var self = $(this);
        var url = self.data("href") || null;
        if (url === null) {
            return;
        }

        openCenteredWindow(url, "_blank", 600, 800);
        opener.one("auth:finished", function () {
            var text = "Refresh {type} Token".replace("{type}", self.data("title") || "");

            self.text(text);
            self.prop("title", text);
            systemMessages("The OAuth2 consent is granted.", "success");
        });
    }

    /**
     * Refresh token button click handler.
     *
     * @param {JQuery.Event} e
     */
    function onRefreshToken(e) {
        e.preventDefault();

        var opener = $(window);
        var self = $(this);
        var url = self.data("href") || null;
        if (url === null) {
            return;
        }

        openCenteredWindow(url, "_blank", 600, 800);
        opener.one("auth:finished", function () {
            systemMessages("The OAuth2 token is refreshed.", "success");
        });
    }

    /**
     * Module entrypoint.
     *
     * @param {{[x: string]: string }} urls
     * @param {{[x: string]: string }} selectors
     * @param {{[x: string]: { name: string, selector: string } }} filterTypes
     */
    function entrypoint(urls, selectors, filterTypes, filters) {
        var body = $("body");
        var urlList = urls || {};
        var selectorList = selectors || {};
        var activeFilters = filters || [];

        // URLs
        var listEnvelopesUrl = urlList.listEnvelopesUrl;
        var addEnvelopeTabsUrl = urlList.addEnvelopeTabsUrl;
        var downloadDocumentUrl = urlList.downloadDocumentUrl;

        // Selectors
        var datagridSelector = selectorList.datagrid;
        var filtersSelector = selectorList.filters;
        var datepickerSelector = selectorList.datepicker;
        var rowDetailsSelector = selectorList.rowDetails;
        var consentButtonsSelector = selectorList.consentButtons;
        var refreshTokenButtonsSelector = selectorList.refreshTokenButtons;

        loadDependencies().then(function () {
            var datagridReader = function () {
                return datagrid;
            };

            var filters = createFilters(
                filtersSelector,
                {
                    callBack: function () {
                        datagridReader().fnDraw();
                    },
                    onSet: function (source, filter) {
                        syncDateFilters(filterTypes, filter);
                    },
                    onDelete: function (filter) {
                        syncDateFilters(filterTypes, filter);
                    },
                },
                activeFilters
            );
            var datagrid = $(datagridSelector).dataTable(
                Object.assign({}, datagridOptions, {
                    sAjaxSource: listEnvelopesUrl,
                    fnServerData: fetchServerData.bind(null, filters),
                })
            );

            createDatepicker(datepickerSelector);
            $(consentButtonsSelector).on("click", onRequestConsent);
            $(refreshTokenButtonsSelector).on("click", onRefreshToken);
            body.on("click", rowDetailsSelector, function (e) {
                showDetailsRow($(this), datagrid);
            });

            mix(window, {
                downloadEnvelopeDocument: function (button) {
                    downloadDocuments(button.data("envelope" || null), button.data("document" || null), downloadDocumentUrl);
                },
                showEnvelopeGridRowContent: function (button) {
                    dataTableAllInfo(button);
                },
                addEnvelopeTabs: function (button) {
                    openTabsEditor(button.data("envelope" || null), addEnvelopeTabsUrl, datagrid);
                },
            });
        });
    }

    return { default: entrypoint };
})();
