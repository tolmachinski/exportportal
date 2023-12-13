import $ from "jquery";

const mobileDataTable = function ($table, replaceTitles) {
    const tableFn = function () {
        const titles = [];
        const $thisTable = $(this);
        const thisTablePushFn = function () {
            titles.push($(this).text());
        };
        $thisTable.find("> thead > tr > th").each(thisTablePushFn);

        if (typeof replaceTitles !== "undefined" ? Boolean(~~replaceTitles) : true) {
            const thisTableTitleFn = function () {
                $(this).attr("data-title", titles[$(this).index()]);
            };
            $thisTable.find("> tbody > tr > td").each(thisTableTitleFn);
        }
    };
    $table.each(tableFn);
};

export default mobileDataTable;
