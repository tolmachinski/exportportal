$(() => {
    let dropFilters;

    dataT = $("#dtDropList").dataTable({
        sDom: '<"top"i>rt<"bottom"lp><"clear">',
        bProcessing: true,
        bServerSide: true,
        sAjaxSource: globalThis.__site_url + "items/ajax_droplist_datatable",
        sServerMethod: "POST",
        aoColumnDefs: [
            {
                sClass: "",
                aTargets: ["dt_item"],
                mData: "dt_item",
                bSortable: false,
            },
            {
                sClass: "w-5 w-190 dn-xl",
                aTargets: ["dt_seller"],
                mData: "dt_seller",
                bSortable: false,
            },
            {
                sClass: "w-120",
                aTargets: ["dt_droplist_price"],
                mData: "dt_droplist_price",
            },
            {
                sClass: "w-120",
                aTargets: ["dt_current_price"],
                mData: "dt_current_price",
            },
            {
                sClass: "w-110 hide-1024",
                aTargets: ["dt_added_date"],
                mData: "dt_added_date",
            },
            {
                sClass: "w-150 dn-hide-1300",
                aTargets: ["dt_price_change_date"],
                mData: "dt_price_change_date",
            },
            {
                sClass: "w-40 tac vam dt-actions",
                aTargets: ["dt_actions"],
                mData: "dt_actions",
                bSortable: false,
            },
        ],
        sorting: [[4, "desc"]],
        sPaginationType: "full_numbers",
        language: {
            paginate: {
                first: "<i class='ep-icon ep-icon_arrow-left'></i>",
                previous: "<i class='ep-icon ep-icon_arrows-left'></i>",
                next: "<i class='ep-icon ep-icon_arrows-right'></i>",
                last: "<i class='ep-icon ep-icon_arrow-right'></i>",
            },
        },
        fnServerData(sSource, aoData, fnCallback) {
            if (!dropFilters) {
                dropFilters = initDtFilter();
            }

            aoData = aoData.concat(dropFilters.getDTFilter());
            $.ajax({
                dataType: "JSON",
                type: "POST",
                url: sSource,
                data: aoData,
                success(data, textStatus, jqXHR) {
                    if (data.mess_type === "error" || data.mess_type === "info") {
                        systemMessages(data.message, data.mess_type);
                    }

                    fnCallback(data, textStatus, jqXHR);
                },
            });
        },
        fnDrawCallback(oSettings) {
            hideDTbottom(this)
            mobileDataTable($(".main-data-table"));
            $(".rating-bootstrap").rating();

            $("[data-active]").each((index, element) => {
                if(!$(element).data("active")) {
                    $(element).closest("tr").addClass("js-unavailable-tr unavailable").data({
                        title: translate_js({ plug: "BootstrapDialog", text: "items_droplist_unavailable_ttl" }),
                        message: translate_js({ plug: "BootstrapDialog", text: "items_droplist_unavailable_subttl" })
                    });
                }
            })
        },
    });

    $(".datepicker-init").datepicker({
        beforeShow: function(input, instance) {
            $('#ui-datepicker-div').addClass('dtfilter-ui-datepicker');
        },
    });

    $(document).on("click", ".js-unavailable-tr", e => {
        const el = $(e.target);

        if(!el.closest("[data-toggle]").length) {
            open_result_modal({
                title: el.data("title"),
                subTitle: el.data("message"),
                isAjax: false,
                closable: true,
                type: "info",
                buttons: [
                    {
                        label: translate_js({ plug: "BootstrapDialog", text: "ok" }),
                        cssClass: "btn btn-light",
                        action: function (dialog) {
                            dialog.close();
                        },
                    },
                ],
            });
        }
    })

    mix(globalThis, {
        removeDroplistItem: function (btn) {
            askConfirmation(btn.data("message"), btn.data("title")).then(function (result) {
                if (result.confirm) {
                    $.ajax({
                        dataType: "JSON",
                        type: "POST",
                        url: globalThis.__site_url + "items/ajax_remove_from_droplist/" + btn.data('item-id'),
                        success(data) {
                            systemMessages(data.message, data.mess_type);
                            dataT.fnDraw(false);
                        },
                        complete() {
                            result.dialog.close();
                        },
                        error() {
                            systemMessages('Undefined server error', 'error');
                        }
                    });
                } else {
                    result.dialog.close();
                }
            });
        },
    });
})
