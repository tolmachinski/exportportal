/* eslint-disable */
var DocumentsDetachedGridModule = (function () {
    "use strict";

    var datagridOptions = {
        sDom: '<"top"i>rt<"bottom"lp><"clear">',
        bProcessing: false,
        bServerSide: true,
        aoColumnDefs: [
            { sClass: "w-350", aTargets: "preview", mData: "preview", bSortable: false },
            { sClass: "dn-xl", aTargets: "description", mData: "description", bSortable: false },
            { sClass: "w-100 dn-lg", aTargets: "created_at", mData: "createdAt", bSortable: true },
            { sClass: "w-100 dn-lg", aTargets: "updated_at", mData: "updatedAt", bSortable: true },
            { sClass: "w-40 tac vam", aTargets: "actions", mData: "actions", bSortable: false },
        ],
        sorting: [[2, "desc"]],
        sPaginationType: "full_numbers",
        language: {
            paginate: {
                previous: '<i class="ep-icon ep-icon_arrows-left"></i>',
                first: '<i class="ep-icon ep-icon_arrow-left"></i>',
                next: '<i class="ep-icon ep-icon_arrows-right"></i>',
                last: '<i class="ep-icon ep-icon_arrow-right"></i>',
            },
        },
    };

    function loadDependencies(baseUrl) {
        return Promise.all([
            getScript(baseUrl + "public/plug/jquery-datatables-1-10-12/jquery.dataTables.min.js", true),
            getScript(baseUrl + "public/plug/file-saver-2-0-2/CustomFileSaver.min.js", true),
        ]);
    }

    /**
     * Opens a native window with given parameters.
     *
     * @param {string} url
     * @param {string} title
     * @param {string|{[x: string]: string}} features
     */
    function openWindow(url, title, features) {
        var popupFeatures =
            typeof features === "string"
                ? features
                : Object.keys(features || {}).map(function (key) {
                      if (!isNaN(parseFloat(key)) && isFinite(key)) {
                          return features[key];
                      }

                      return key + "=" + features[key];
                  });

        var newWindow = window.open(url, title, popupFeatures.join(","));
        if (newWindow && newWindow.focus) {
            newWindow.focus();
        }

        return newWindow;
    }

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
            { name: "filters[order]", value: orderId },
        ]);

        return postRequest(source, requestData).then(onRequestSuccess).catch(onRequestFail);
    }

    function onDatagridDraw() {
        hideDTbottom(this);
        mobileDataTable(this);

        $.fancybox.update();
        $('[data-toggle="popover"]').popover({
            trigger: "hover",
            placement: "top",
        });
    }

    /**
     * Sends envelope to the recipients.
     *
     * @param {number} envelopeId
     * @param {string|URL} url
     * @param {DataTables.JQueryDataTables} datagrid
     */
    function sendPlainRequest(envelopeId, url, datagrid) {
        if (envelopeId === null) {
            throw new TypeError("The argument 0 must be a number.");
        }

        return postRequest(url, { envelope: envelopeId }, "json")
            .then(function (response) {
                var message = response.message || null;
                var messageType = response.mess_type || null;
                if (message) {
                    systemMessages(message, messageType);
                }
                if (messageType === "success") {
                    datagrid.fnDraw(true);
                }

                return response;
            })
            .catch(function (e) {
                onRequestError(e);
            });
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
     * View envelope.
     *
     * @param {JQuery} button
     * @param {number} envelopeId
     * @param {number} documentId
     * @param {string|URL} url
     * @param {DataTables.JQueryDataTables} datagrid
     */
    function viewDocuments(button, envelopeId, documentId, url, datagrid) {
        button.prop("disabled", true).addClass("disabled");

        var newTab = null;
        if (isSafari() || isChromeIoS()) {
            // One more reason to hate Apple
            newTab = globalThis.open("", "Preview");
            if (newTab) {
                newTab.document.title = "Preview";
                newTab.document.body.innerText = "opening...";
            }
        }

        return postRequest(url, { envelope: envelopeId, document: documentId }, "json")
            .then(function (response) {
                var preview = response.preview || { url: null, targets: [] };
                var targets = preview.targets || [];
                var message = response.message || null;
                var messageType = response.mess_type || null;
                if (message) {
                    systemMessages(message, messageType);
                }
                if (messageType === "success") {
                    datagrid.fnDraw(true);
                }
                if (!targets.length) {
                    return;
                }

                targets.forEach(function (target) {
                    if (isSafari() || isChromeIoS()) {
                        if (newTab) {
                            newTab.location.href = target;
                        } else {
                            globalThis.location.href = target;
                        }
                    } else {
                        newTab = newTab || globalThis.open(target, "_blank");
                    }
                    newTab = null;
                });
            })
            .catch(function (e) {
                onRequestError(e);
            });
    }

    /**
     * Opens the digital document in the preview mode.
     *
     * @param {JQuery} button
     * @param {number} envelopeId
     * @param {string|URL} url
     * @param {DataTables.JQueryDataTables} datagrid
     */
    function openEnvelopePreview(button, envelopeId, url) {
        button.prop("disabled", true).addClass("disabled");

        return postRequest(url, { envelope: envelopeId }, "json")
            .then(function (response) {
                button.closest(".dropdown-menu").removeClass("show");

                var message = response.message || null;
                var messageType = response.mess_type || null;
                var redirectUrl = response.redirectUrl || null;
                if (message) {
                    systemMessages(message, messageType);
                }
                if (null === redirectUrl) {
                    return;
                }

                if (isSafari() || isChromeIoS()) {
                    var newTab = globalThis.open("", "Preview");
                    if (newTab) {
                        newTab.document.title = "Preview";
                        newTab.document.body.innerText = "opening...";
                        newTab.location.href = redirectUrl;
                    } else {
                        globalThis.location.href = redirectUrl;
                    }
                } else {
                    openWindow(redirectUrl, "_blank", { 0: "noreferrer", 1: "noopener" });
                }
            })
            .catch(function (e) {
                onRequestError(e);
            })
            .finally(function () {
                button.prop("disabled", false).removeClass("disabled");
            });
    }

    /**
     * Module entrypoint.
     *
     * @param {number} orderId
     * @param {{[x: string]: string }} urls
     * @param {{[x: string]: string }} selectors
     */
    function entrypoint(orderId, urls, selectors) {
        var urlList = urls || {};
        var selectorList = selectors || {};

        // URLs
        var baseUrl = urlList.baseUrl;
        var listEnvelopesUrl = urlList.listEnvelopesUrl;
        var sendEnvelopeUrl = urlList.sendEnvelopeUrl;
        var viewEnvelopeUrl = urlList.viewEnvelopeUrl;
        var confirmEnvelopeUrl = urlList.confirmEnvelopeUrl;
        var requireApprovalUrl = urlList.requireApprovalUrl;
        var downloadDocumentUrl = urlList.downloadDocumentUrl;
        var accessRemoteEnvelopeUrl = urlList.accessRemoteEnvelopeUrl;

        // Selectors
        var datagridSelector = selectorList.datagrid;

        loadDependencies(baseUrl).then(function () {
            var container = $(globalThis);
            var datagrid = $(datagridSelector).dataTable(
                Object.assign({}, datagridOptions, {
                    sAjaxSource: listEnvelopesUrl,
                    fnServerData: fetchServerData.bind(null, orderId),
                    fnDrawCallback: onDatagridDraw,
                })
            );

            // Normalize table
            if (datagrid.length > 0 && container.width() < 768) {
                datagrid.addClass("main-data-table--mobile");
            }

            var handlers = {
                declined: function () {
                    datagrid.fnDraw(true);
                },
                voided: function () {
                    datagrid.fnDraw(true);
                },
                signed: function () {
                    datagrid.fnDraw(true);
                },
                processed: function () {
                    datagrid.fnDraw(true);
                },
                "info-updated": function () {
                    datagrid.fnDraw(true);
                },
            };

            Object.keys(handlers).forEach(function (event) {
                $(globalThis).off("documents:envelope-" + event);
                $(globalThis).on("documents:envelope-" + event, handlers[event]);
            });

            mix(
                window,
                {
                    dataT: datagrid,
                    downloadEnvelopeDocument: function (button) {
                        downloadDocuments(button.data("envelope" || null), button.data("document" || null), downloadDocumentUrl);
                    },
                    confirmSignedEnvelope: function (button) {
                        sendPlainRequest(button.data("envelope" || null), confirmEnvelopeUrl, datagrid);
                    },
                    accessRemoteEnvelope: function (button) {
                        openEnvelopePreview(button, button.data("envelope" || null), accessRemoteEnvelopeUrl);
                    },
                    viewEnvelopeAsRecipient: function (button) {
                        viewDocuments(button, button.data("envelope" || null), button.data("document" || null), viewEnvelopeUrl, datagrid);
                    },
                    sendEnvelopeToRecipients: function (button) {
                        sendPlainRequest(button.data("envelope" || null), sendEnvelopeUrl, datagrid);
                    },
                    requireEnvelopeApproval: function (button) {
                        sendPlainRequest(button.data("envelope" || null), requireApprovalUrl, datagrid);
                    },
                    showEnvelopeGridRowContent: function (button) {
                        dataTableAllInfo(button);
                    },
                },
                false
            );
            dataTableScrollPage(datagrid);
        });
    }

    return { default: entrypoint };
})();
