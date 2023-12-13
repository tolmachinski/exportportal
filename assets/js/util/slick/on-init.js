import $ from "jquery";
import lazyLoadingInstance from "@src/plugins/lazy/index";
import customSlickButtons from "@src/util/slick/custom-buttons";
import fixGetNavigableIndexesSlick from "@src/util/slick/get-navigable-indexes";
import onResizeCallback from "@src/util/dom/on-resize-callback";

/**
 * It initializes a slick slider
 * @param {Object} params
 * @param {string} params.sliderNode - The node of the slider.
 * @param {string} [params.sliderName=""] - The name of the slider. This is used to create the custom buttons.
 * @param {Object} [params.buttonsBreakpoints={}] - An object with the breakpoints for the slider buttons.
 */
const onInitSlickSlider = ({ sliderNode, sliderName = "", buttonsBreakpoints = {} }) => {
    const slider = $(sliderNode);
    slider.on("init", (_e, slick) => {
        lazyLoadingInstance(`${sliderNode} .js-lazy`);
        fixGetNavigableIndexesSlick(slick);

        if (Object.keys(buttonsBreakpoints).length) {
            customSlickButtons(sliderName, slider, slick, {
                breakpoints: buttonsBreakpoints,
            });

            onResizeCallback(() => {
                if ($(globalThis).width() > 991) {
                    customSlickButtons(sliderName, slider, slick, { breakpoints: buttonsBreakpoints });
                }

                if (!slider.hasClass("slick-initialized")) {
                    slider.find(".slick-arrow").remove();
                }
            }, globalThis);
        }
    });
};

export default onInitSlickSlider;
