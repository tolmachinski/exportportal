import { BACKSTOP_TEST_MODE } from "@src/common/constants";
import getSwipeCount from "@src/util/slick/get-swipe-count";

/**
 *
 * @param {JQuery} slider
 * @param {} slick
 * @param {number} time
 */
const customSlickAutoplay = (slider, slick, time, { breakpoints }) => {
    if (BACKSTOP_TEST_MODE) {
        return;
    }

    let timeout;
    const onFocusOutAutoplay = () => {
        clearInterval(timeout);
        timeout = setInterval(() => {
            slick.goTo(slick.currentSlide + getSwipeCount(breakpoints));
        }, time);
    };

    slider.on("mouseenter touchstart", () => clearTimeout(timeout));
    slider.on("mouseleave touchend", () => onFocusOutAutoplay());
    onFocusOutAutoplay();
};

export default customSlickAutoplay;
