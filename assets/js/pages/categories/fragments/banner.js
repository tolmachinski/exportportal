import { BACKSTOP_TEST_MODE } from "@src/common/constants";
import $ from "jquery";

const autoToggleBannerPictures = function () {
    const pictures = $(".js-categories-banner__img");
    let i = 0;

    // Backstop disable slide intervals
    if (BACKSTOP_TEST_MODE) {
        return;
    }

    setInterval(() => {
        pictures.eq(i).removeClass("show");
        i = i === pictures.length - 1 ? 0 : i + 1;
        pictures.eq(i).addClass("show");
    }, 3000);
};

export default autoToggleBannerPictures;
