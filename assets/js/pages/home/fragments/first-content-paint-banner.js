import $ from "jquery";
import { BACKSTOP_TEST_MODE, LOGGED_IN } from "@src/common/constants";
import getCookie from "@src/util/cookies/get-cookie";
import setCookie from "@src/util/cookies/set-cookie";
import delay from "@src/util/async/delay";

const delayCallTimeout = BACKSTOP_TEST_MODE ? 0 : 5000;
const firstContentPaintBanner = async () => {
    const fcpSliderNode = ".js-header-slider";
    if ($(fcpSliderNode).children().length > 1) {
        if (!LOGGED_IN && !getCookie("FCP-HomePage")) {
            await delay(window.matchMedia("(max-width: 575px)").matches ? delayCallTimeout : 0);
            setCookie("FCP-HomePage", 1, { expires: 14 });
        }
        const { default: headerHomeSlider } = await import("@src/pages/home/fragments/header-slider");
        headerHomeSlider(fcpSliderNode);
    }
};

export default firstContentPaintBanner;
