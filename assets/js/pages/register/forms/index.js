import $ from "jquery";
import { BACKSTOP_TEST_MODE, DISABLE_POPUP_SYSTEM } from "@src/common/constants";

import { viewPassword } from "@src/pages/login/fragments/login/index";
import lazyLoadingScriptOnScroll from "@src/common/lazy/lazy-script";
import lazyLoadingInstance from "@src/plugins/lazy/index";
import TriggerPopup from "@src/components/popups_system/popup_util";
import EventHub from "@src/event-hub";

import "@scss/user_pages/register_forms/index_page.scss";

$(() => {
    EventHub.on("register-forms:view-password", (e, button) => viewPassword(button));
    const sliderNode = "#js-testimonials-slick";
    const slider = $(sliderNode);
    lazyLoadingScriptOnScroll(
        slider,
        () => {
            // @ts-ignore
            import(/* webpackChunkName: "slick-carousel-chunk" */ "slick-carousel").then(() => {
                slider.on("init", () => lazyLoadingInstance(`${sliderNode} .js-lazy`));
                slider.slick({
                    dots: true,
                    infinite: true,
                    arrows: false,
                    slidesToShow: 1,
                    autoplay: !BACKSTOP_TEST_MODE,
                    autoplaySpeed: 10000,
                });
            });
        },
        "100px"
    );

    // Init feedback popup
    setTimeout(() => {
        // NEW POPUP CALL feedback_registration
        globalThis.triggerFeedbackRegistration = new TriggerPopup("feedback_registration");

        EventHub.on("register-forms:call-trigger-feedback-registration", () => {
            if (!globalThis.triggerFeedbackRegistration.getStatus() && !DISABLE_POPUP_SYSTEM) {
                EventHub.trigger("popup:call-popup", { detail: { name: "feedback_registration" } });
            }
        });
    }, 5000);
});
