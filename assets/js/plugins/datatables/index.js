import $ from "jquery";

import openContentPopup from "@src/common/popups/types/modal-content-popup";

let bootHandle = null;
const defaultOptions = {
    dom: '<"top"i>rt<"bottom"lp><"clear">',
    serverSide: true,
    processing: false,
    paginationType: "full_numbers",
    language: {
        paginate: {
            previous: '<i class="ep-icon ep-icon_arrows-left"></i>',
            first: '<i class="ep-icon ep-icon_arrow-left"></i>',
            next: '<i class="ep-icon ep-icon_arrows-right"></i>',
            last: '<i class="ep-icon ep-icon_arrow-right"></i>',
        },
    },
};

const doBoot = async () => {
    // @ts-ignore
    await import(/* webpackChunkName: "datatables-core" */ "datatables.net");

    // @ts-ignore
    return $.fn.DataTable;
};

/**
 * Boots the plugin only one time.
 */
const boot = async () => {
    if (bootHandle === null) {
        bootHandle = doBoot();
    }

    return bootHandle;
};

/**
 * Get DataTable object.
 */
const DataTables = async () => {
    await boot();

    return $.fn.DataTable;
};

/**
 * Get static DataTable object.
 */
const DataTablesStatic = async () => {
    await boot();

    return $.fn.dataTable;
};

/**
 * Creates the grid.
 *
 * @param {string|HTMLElement} selector
 * @param {DataTables.Settings} [options={}]
 *
 * @returns {Promise<DataTables.JQueryDataTables>}
 */
const initialize = async (selector, options = {}) => {
    await boot();

    /** @type {JQuery<HTMLElement>} */
    // @ts-ignore
    const element = $(selector instanceof HTMLElement ? selector : document.querySelectorAll(selector));
    element.DataTable($.extend({}, defaultOptions, options, true));

    return element.dataTable();
};

/**
 *
 * @param {JQuery.Selector|HTMLElement} tables
 * @param {boolean} replaceTitles
 */
const adjustGridForMobile = function (tables, replaceTitles = true) {
    /** @type {JQuery<HTMLElement>} elements */
    // @ts-ignore
    const elements = $(tables);
    const replace = typeof replaceTitles !== "undefined" ? Boolean(~~replaceTitles) : true;

    elements.toArray().forEach(e => {
        const titles = [];
        const table = $(e);

        table
            .find("> thead > tr > th")
            .toArray()
            .forEach(th => {
                titles.push($(th).text());
            });

        if (replace) {
            table
                .find("> tbody > tr > td")
                .toArray()
                .forEach(td => {
                    const node = $(td);

                    node.attr("data-title", titles[node.index()]);
                });
        }
    });
};

/**
 * Toggle grid bottom.
 *
 * @param {DataTables.JQueryDataTables} tableApi
 * @param {DataTables.SettingsLegacy} settings
 */
const toggleGridBottom = (tableApi, settings) => {
    const tableBottom = tableApi.siblings(".bottom");
    // eslint-disable-next-line no-underscore-dangle
    const displayLength = settings._iDisplayLength || 0;
    const displayLengthMin = settings.aLengthMenu[0] || 1;
    const recordsTotal = settings.fnRecordsDisplay();
    const pages = displayLength > 0 ? Math.ceil(recordsTotal / displayLengthMin) : 1;

    if (pages > 1) {
        tableBottom.show();
    } else {
        tableBottom.hide();
    }
};

/**
 *
 * @param {DataTables.JQueryDataTables} tableApi
 */
const scrollGridToTop = tableApi => {
    tableApi.on("page.dt", () => {
        $("html, body").animate({ scrollTop: tableApi.closest(".dataTables_wrapper").offset().top }, "slow");
    });
};

/**
 * Shows datatable conetent popup.
 *
 * @param {string|HTMLElement|JQuery} selector
 */
const showDatatableContentPopup = async function (selector) {
    /** @type {JQuery<HTMLElement>} node */
    // @ts-ignore
    const node = $(selector);
    const row = node.closest('tr[role="row"]');
    const table = row.closest("table");
    const dataTables = await DataTablesStatic();
    if (!dataTables.isDataTable(table)) {
        return;
    }

    const api = new dataTables.Api(table);
    const rowIndex = api.row(row).index();
    const collectCells = function () {
        return api
            .cells()
            .eq(0)
            .filter(index => index.row === rowIndex)
            .map(index => {
                const cell = api.cell(index);
                // @ts-ignore
                const header = cell.column(index.column).header();
                const title = $(header).text() || null;
                if (title === null) {
                    return;
                }

                // eslint-disable-next-line consistent-return
                return {
                    row: index.row || null,
                    data: cell.data().toString().trim(),
                    title,
                    column: index.column || null,
                };
            })
            .toArray()
            .filter(i => i);
    };
    const makeCellDetails = cell => `<div class="dtable-all__item">
        <div class="dtable-all__ttl">
            ${cell.title}
        </div>
        <div class="dtable-all__detail">
        ${cell.data}
        </div>
    </div>`;

    openContentPopup("All info", `<div class="dtable-all">${collectCells().map(makeCellDetails).join("")}</div>`, {
        maxWidth: 350,
        autoSize: false,
    });
};

export { boot };
export { initialize };
export { DataTables };
export { scrollGridToTop };
export { DataTablesStatic };
export { toggleGridBottom };
export { adjustGridForMobile };
export { showDatatableContentPopup };
export default async () => boot();
