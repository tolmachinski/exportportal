/* eslint-disable */
var DetachedDocumentsGridModule = (function () {
    "use strict";

    /**
     * Default datatable options
     */
    var datagridOptions = {
        sDom: 'rt<"bottom"ip><"clear">',
        bProcessing: true,
        bServerSide: true,
        aoColumnDefs: [
            { sClass: "w-100 tac vat", aTargets: "dt-details", mData: "showDetails", bSortable: false },
            { sClass: "w-250 vat", aTargets: "dt-envelope", mData: "envelope", bSortable: false },
            { sClass: "w-100 vat dt-body-center", aTargets: "dt-status", mData: "status", bSortable: false },
            { sClass: "w-150 tac vam dn-lg", aTargets: "dt-created-at", mData: "createdAt", bSortable: true },
            { sClass: "w-150 tac vam dn-lg", aTargets: "dt-updated-at", mData: "updatedAt", bSortable: true },
            { sClass: "w-40 tac vam", aTargets: "dt-actions", mData: "actions", bSortable: false },
        ],
        sorting: [[3, "desc"]],
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
    function fetchServerData(orderId, source, data, callback) {
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
        var requestData = data.concat([
            { name: "mode", value: "legacy" },
            { name: "filters[order]", value: orderId }
        ]);

        return postRequest(source, requestData).then(onRequestSuccess).catch(onRequestFail);
    }

    /**
     * Renders the details for the datatable row.
     *
     * @param {any} data
     */
    function renderRowDetails(data) {
        var details = [
            { column: "Sender", value: data.sender || "—" },
            { column: "Status", value: data.status || "—" },
            { column: "Type", value: data.type || "—" },
            { column: "Description", value: data.description || "—" },
        ].concat(data.details || []);

        return (
            '<div class="dt-details">' +
            '<table class="dt-details__table">' +
            details
                .map(function (entry) {
                    return '<tr><td class="w-200">' + entry.column + "</td><td>" + entry.value + "</td></tr>";
                })
                .join("") +
            "</table>" +
            "</div>"
        );
    }

    /**
     * Sends envelope to the recipients.
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

    function onDatagridDraw(datagridReader) {
        datagridReader().closest(".dataTables_wrapper").find('[data-toggle="popover"]').popover({
            trigger: "hover",
            placement: "top",
        });

        $.fancybox.update();
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

    /**
     * Module entrypoint.
     *
     * @param {{[x: string]: string }} urls
     * @param {{[x: string]: string }} selectors
     * @param {{[x: string]: string }} params
     */
    function entrypoint(urls, selectors, params) {
        var body = $("body");
        var urlList = urls || {};
        var selectorList = selectors || {};

        // URLs
        var listEnvelopesUrl = urlList.listEnvelopesUrl;
        var addEnvelopeTabsUrl = urlList.addEnvelopeTabsUrl;
        var downloadDocumentUrl = urlList.downloadDocumentUrl;

        // Selectors
        var datagridSelector = selectorList.datagrid;
        var datepickerSelector = selectorList.datepicker;
        var rowDetailsSelector = selectorList.rowDetails;

        // Params
        var orderId = params.orderId;

        loadDependencies().then(function () {
            var datagridReader = function () {
                return datagrid;
            };

            var datagrid = $(datagridSelector).dataTable(
                Object.assign({}, datagridOptions, {
                    sAjaxSource: listEnvelopesUrl,
                    fnServerData: fetchServerData.bind(null, orderId),
                    fnDrawCallback: onDatagridDraw.bind(null, datagridReader),
                })
            );

            createDatepicker(datepickerSelector);
            body.on("click", rowDetailsSelector, function (e) {
                showDetailsRow($(this), datagrid);
            });

            mix(
                window,
                {
                    downloadEnvelopeDocument: function (button) {
                        downloadDocuments(button.data("envelope" || null), button.data("document" || null), downloadDocumentUrl);
                    },
                    showEnvelopeGridRowContent: function (button) {
                        dataTableAllInfo(button);
                    },
                    addEnvelopeTabs: function (button) {
                        openTabsEditor(button.data("envelope" || null), addEnvelopeTabsUrl, datagrid);
                    },
                },
                false
            );
        });
    }

    return { default: entrypoint };
})();
