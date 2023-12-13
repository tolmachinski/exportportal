import EventHub from "@src/event-hub";
import getSwipeCount from "@src/util/slick/get-swipe-count";

const customSlickButtons = (sliderName, slider, slick, { breakpoints }) => {
    if (slider.find("button.slick-arrow-custom").length) {
        return;
    }

    slider.append(`
        <button class="slick-arrow-custom slick-prev slick-arrow call-action" data-js-action="slick-${sliderName}-btn:prev" aria-label="Previous"><i class="ep-icon ep-icon_arrow-left"></i></button>
        <button class="slick-arrow-custom slick-next slick-arrow call-action" data-js-action="slick-${sliderName}-btn:next" aria-label="Next"><i class="ep-icon ep-icon_arrow-right"></i></button>
    `);

    EventHub.on(`slick-${sliderName}-btn:prev`, () => {
        const { currentSlide, slideCount } = slick;
        const swipeCount = getSwipeCount(breakpoints);
        if (currentSlide - swipeCount < 0) {
            const c = currentSlide - swipeCount;
            slick.goTo(-1, c < -1);
            if (c < -1) {
                slick.goTo(slideCount + c);
            }
        } else {
            slick.goTo(currentSlide - swipeCount);
        }
    });
    EventHub.on(`slick-${sliderName}-btn:next`, () => {
        slick.goTo(slick.currentSlide + getSwipeCount(breakpoints));
    });
};

export default customSlickButtons;
