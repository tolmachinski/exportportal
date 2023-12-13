import { initialize as createGrid, toggleGridBottom, adjustGridForMobile, scrollGridToTop, showDatatableContentPopup } from "@src/plugins/datatables/index";
import DTFiltersTools, { dropPathParams, rewriteHystory, maskFilterNumber } from "@src/plugins/dt-filters/tools/index";
import { sendPlainRequest, downloadDocuments, viewDocuments } from "@src/documents/common";
import { initialize as intitializeFilters } from "@src/plugins/dt-filters/index";
import { initialize as createDatepicker } from "@src/plugins/datepicker/index";
import { systemMessages } from "@src/util/system-messages/index";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";
import EventHub from "@src/event-hub";

const datagridOptions = {
    sorting: [[2, "desc"]],
    columnDefs: [
        { class: "w-350", targets: "preview", data: "preview", sortable: false },
        { class: "dn-xl", targets: "description", data: "description", sortable: false },
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
};

const createFilterOptions = (baseUrl, filters) => {
    const preMutators = new DTFiltersTools.Bindings(
        [
            { name: filters.document.name, op: [{ name: "maskFilterNumber" }] },
            { name: filters.order.name, op: [{ name: "maskFilterNumber" }] },
        ],
        {
            maskFilterNumber,
        }
    );
    const mutators = new DTFiltersTools.Bindings(
        [
            { name: filters.createdFrom.name, source: filters.createdFrom.selector, op: [{ name: "minDate", target: filters.createdTo.selector }] },
            { name: filters.updatedFrom.name, source: filters.updatedFrom.selector, op: [{ name: "minDate", target: filters.updatedTo.selector }] },
            { name: filters.createdTo.name, source: filters.createdTo.selector, op: [{ name: "maxDate", target: filters.createdFrom.selector }] },
            { name: filters.updatedTo.name, source: filters.updatedTo.selector, op: [{ name: "maxDate", target: filters.updatedFrom.selector }] },
        ],
        {
            minDate(source, target) {
                target.datepicker("option", "minDate", source.datepicker("getDate"));
            },
            maxDate(source, target) {
                target.datepicker("option", "maxDate", source.datepicker("getDate"));
            },
        }
    );
    const cleaners = new DTFiltersTools.Bindings(
        [
            { name: filters.createdFrom.name, source: filters.createdFrom.selector, op: [{ name: "cleanMinDate", target: filters.createdTo.selector }] },
            { name: filters.updatedFrom.name, source: filters.updatedFrom.selector, op: [{ name: "cleanMinDate", target: filters.updatedTo.selector }] },
            { name: filters.createdTo.name, source: filters.createdTo.selector, op: [{ name: "cleanMaxDate", target: filters.createdFrom.selector }] },
            { name: filters.updatedTo.name, source: filters.updatedTo.selector, op: [{ name: "cleanMaxDate", target: filters.updatedFrom.selector }] },
            { name: filters.document.name, op: [{ name: "clearUrl" }] },
            { name: filters.order.name, op: [{ name: "clearUrl" }] },
        ],
        {
            cleanMinDate(source, target) {
                target.datepicker("option", "minDate", null);
            },
            cleanMaxDate(source, target) {
                target.datepicker("option", "maxDate", null);
            },
            clearUrl() {
                rewriteHystory(baseUrl + dropPathParams(globalThis.location.pathname, this.name), this);
            },
        }
    );

    return {
        onSet: filter => mutators.handle(filter),
        onDelete: filter => cleaners.handle(filter),
        beforeSet: node => preMutators.handle({ name: node.prop("name"), node }),
    };
};

/**
 * Fragment entrypoint.
 *
 * @param {{[x: string]: string }} urls
 * @param {{[x: string]: string }} selectors
 * @param {{[x: string]: { name: string, selector: string } }} filterTypes
 */
export default async (urls, selectors, filterTypes) => {
    const { listEnvelopesUrl, sendEnvelopeUrl, viewEnvelopeUrl, confirmEnvelopeUrl, downloadDocumentUrl, baseUrl } = urls;
    const { datagrid: datagridSelector, filters: filtersSelector, datepicker: datepickerSelector } = selectors;

    const filters = await intitializeFilters(filtersSelector, {
        ...createFilterOptions(baseUrl, filterTypes),
        callBack() {
            // eslint-disable-next-line no-use-before-define
            datagrid.api().draw();
        },
    });
    const datagrid = await createGrid(datagridSelector, {
        ...datagridOptions,
        drawCallback: onDatagridDraw,
        ajax(data, callback) {
            return fetchServerData(
                listEnvelopesUrl,
                {
                    ...data,
                    filters: filters.getDTFilter().reduce((accumulator, { name, value }) => {
                        return { ...accumulator, [name]: value };
                    }, {}),
                },
                callback
            );
        },
    });
    await createDatepicker(datepickerSelector);

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
