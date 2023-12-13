import $ from "jquery";
import { isMobile } from "@src/util/platform";
import { openModalPopup } from "@src/plugins/bootstrap-dialog/index";

import "@scss/landings/payments/critical.scss"; // header
import "@scss/landings/payments/index.scss"; // content

const onClickScheme = async function () {
    const href = $(this).data("href");
    if (isMobile()) {
        globalThis.open(href);
        return;
    }
    await openModalPopup({
        content: `<img src="${href}" alt="Trade transaction">`,
        title: "Trade transaction",
        classes: "bootstrap-dialog--transaction",
    });
};

$(() => {
    $(".js-scheme").on("click", onClickScheme);
});
