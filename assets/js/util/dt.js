import $ from "jquery";
/**
 * Normalizes width of the provided tables.
 *
 * @param {JQuery} tables
 */
const normalizeTables = tables => {
    if (tables.length !== 0) {
        if ($(globalThis).width() < 768) {
            tables.addClass("main-data-table--mobile");
        } else {
            tables.removeClass("main-data-table--mobile");
        }
    }
};
/**
 * Aligns height of the table cells.
 *
 * @param {JQuery} allRows
 * @param {JQuery} fixedRows
 * @param {JQuery} dynamicColumns
 * @param {string} dynamicCellSelector
 */
const alignCellHeight = (allRows, fixedRows, dynamicColumns, dynamicCellSelector) => {
    const rows = allRows instanceof $ ? allRows.toArray() : [];
    const fixed = fixedRows instanceof $ ? fixedRows.toArray() : [];
    const columns = dynamicColumns instanceof $ ? dynamicColumns.toArray() : [];
    const cellSelector = dynamicCellSelector || null;

    rows.forEach(element => {
        $(element).css({ height: "auto" });
    });
    fixed.forEach((element, index) => {
        const row = $(element);
        let maxHeight = row.height();

        // Let's find max height
        if (cellSelector !== null) {
            columns.forEach(columnElement => {
                const cell = $(columnElement).find(cellSelector).eq(index);
                if (cell.length) {
                    const cellHeight = cell.height();
                    if (cellHeight > maxHeight) {
                        maxHeight = cellHeight;
                    }
                }
            });
        }

        // ...and set the max height to everyone
        row.height(maxHeight);
        columns.forEach(columnElement => {
            const cell = $(columnElement).find(cellSelector).eq(index);
            if (cell.length) {
                cell.height(maxHeight);
            }
        });
    });
};

/**
 * Updates DataTables instances in the window.
 *
 * @param {boolean} refilter
 */
const updateDataTables = refilter => {
    if ($.fn.dataTable) {
        // @ts-ignore
        $.fn.dataTable.tables().forEach(table => {
            $(table)
                .dataTable()
                // @ts-ignore
                .fnDraw(typeof refilter !== "undefined" ? refilter : true);
        });
    }
};

export { normalizeTables, alignCellHeight, updateDataTables };
