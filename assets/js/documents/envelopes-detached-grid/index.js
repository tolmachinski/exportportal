import $ from "jquery";

import { initialize as createGrid, toggleGridBottom, adjustGridForMobile, scrollGridToTop, showDatatableContentPopup } from "@src/plugins/datatables/index";
import { sendPlainRequest, downloadDocuments, viewDocuments } from "@src/documents/common";
import { updateFancyboxPopup } from "@src/plugins/fancybox/v2/util";
import { systemMessages } from "@src/util/system-messages/index";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";
import EventHub from "@src/event-hub";

const defaultDatagridOptions = {
    sorting: [[2, "desc"]],
    columnDefs: [
        { class: "w-350", targets: "preview", data: "preview", sortable: false },
        { class: "w-100 dn-lg", targets: "created_at", data: "createdAt", sortable: true },
        { class: "w-100 dn-lg", targets: "updated_at", data: "updatedAt", sortable: true },
        { class: "w-40 tac vam", targets: "actions", data: "actions", sortable: false },
    ],
};

const fetchServerData = async function (sourceUrl, data, callback) {
    try {
        const response = await postRequest(sourceUrl, data);
        if (response.mess_type === "error") {
            systemMessages(response.message, response.mess_type);
        }

        callback(response);
    } catch (error) {
        handleRequestError(error);
    }
};

const onDatagridDraw = function (settings) {
    toggleGridBottom(this, settings);
    adjustGridForMobile(this);
    if (globalThis.ENCORE_MODE) {
        updateFancyboxPopup();
        // @ts-ignore
    } else if (globalThis.$.fancybox) {
        // @ts-ignore
        globalThis.$.fancybox.update();
    }

    // $('[data-toggle="popover"]').popover({
    //     trigger: "hover",
    //     placement: "top",
    // });
};

/**
 * Fragment entrypoint.
 *
 * @param {{[x: string]: string }} selectors
 */
export default async (orderId, urls, selectors) => {
    const { listEnvelopesUrl, sendEnvelopeUrl, viewEnvelopeUrl, confirmEnvelopeUrl, downloadDocumentUrl } = urls;
    const { datagrid: datagridSelector } = selectors;

    const container = $(globalThis);
    const datagrid = await createGrid(datagridSelector, {
        ...defaultDatagridOptions,
        drawCallback: onDatagridDraw,
        ajax(data, callback) {
            return fetchServerData(
                listEnvelopesUrl,
                {
                    ...data,
                    filters: { order: orderId },
                },
                callback
            );
        },
    });

    // Normalize table
    if (datagrid.length > 0 && container.width() < 768) {
        datagrid.addClass("main-data-table--mobile");
    }

    const handlers = {
        declined: () => datagrid.api().draw(true),
        voided: () => datagrid.api().draw(true),
        signed: () => datagrid.api().draw(true),
        processed: () => datagrid.api().draw(true),
        "info-updated": () => datagrid.api().draw(true),
        "grid:show-row-content": (e, button) => showDatatableContentPopup(button),
        "grid:send-envelope": (e, button) => sendPlainRequest(button.data("envelope" || null), sendEnvelopeUrl, datagrid),
        "grid:confirm-envelope": (e, button) => sendPlainRequest(button.data("envelope" || null), confirmEnvelopeUrl, datagrid),
        "grid:view-envelope": (e, button) => viewDocuments(button.data("envelope" || null), button.data("document" || null), viewEnvelopeUrl, datagrid),
        "grid:download-document": (e, button) => downloadDocuments(button.data("envelope" || null), button.data("document" || null), downloadDocumentUrl),
    };

    Object.keys(handlers).forEach(event => {
        EventHub.off(`documents:envelope-${event}`);
        EventHub.on(`documents:envelope-${event}`, handlers[event]);
    });

    scrollGridToTop(datagrid);
};
