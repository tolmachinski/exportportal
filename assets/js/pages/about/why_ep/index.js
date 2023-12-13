import $ from "jquery";

import { translate } from "@src/i18n";
import reviewsSlider from "@src/pages/about/why_ep/fragments/reviews-slider";
import EventHub from "@src/event-hub";

import "@scss/user_pages/why_ep_page/index_page.scss";

reviewsSlider();

const onClickWriteReviewPopup = async (e, btn) => {
    const { title, subTitle } = btn.data();
    const { default: boot, openResultModal } = await import("@src/plugins/bootstrap-dialog/index");
    await boot();
    openResultModal({
        title,
        subTitle,
        closable: true,
        buttons: [
            {
                label: translate({ plug: "BootstrapDialog", text: "cancel" }),
                cssClass: "btn btn-light",
                action(dialogRef) {
                    dialogRef.close();
                },
            },
            {
                label: translate({ plug: "BootstrapDialog", text: "login" }),
                cssClass: "btn btn-primary",
                action(dialog) {
                    dialog.close();
                    $(".js-sign-in").trigger("click");
                },
            },
        ],
    });
};

$(() => {
    EventHub.on("about-why-ep:log-in-popup", onClickWriteReviewPopup);

    const url = new URL(globalThis.location.href);
    if (url.searchParams.get("openNewReviewPopup") === "1") {
        setTimeout(() => {
            $("#js-open-write-review-popup").trigger("click");
            url.searchParams.delete("openNewReviewPopup");
            globalThis.history.pushState({}, document.title, url.href);
        }, 100);
    }
});
