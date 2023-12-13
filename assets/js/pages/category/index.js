import $ from "jquery";

import hideMaxList from "@src/plugins/hide-max-list/index";
import EventHub from "@src/event-hub";

import "@scss/user_pages/category/index.scss";

$(() => {
    hideMaxList(".js-hide-max-list");
    EventHub.on("banner-add-item:hide", async () => {
        const { hideBannerNewAddItemProcess } = await import("@src/components/banners/add_item_process_sticky_banner/index");
        hideBannerNewAddItemProcess();
    });

    EventHub.on("category:open-modal", async (_e, button) => {
        const { openModalPopup } = await import("@src/plugins/bootstrap-dialog/index");

        openModalPopup({
            classes: "bootstrap-dialog--categories",
            title: button.data("title"),
            content: button.attr("href"),
            isAjax: true,
            closable: true,
        });
    });
});
