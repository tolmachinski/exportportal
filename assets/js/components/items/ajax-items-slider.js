import $ from "jquery";
import removeAjaxLoader from "@src/util/dom/remove-ajax-loader";
import getItemsForSlider from "@src/util/items/get-items-for-slider";
import onInitSlickSlider from "@src/util/slick/on-init";
import onResizeCallback from "@src/util/dom/on-resize-callback";
import lazyLoadingInstance from "@src/plugins/lazy/index";
import initSlickSlider from "@src/components/items/items-slider";

/**
 * It initializes a slick slider with ajax loading
 * @param {Object} params - Slider params
 * @param {string} params.sliderNode - The node of the slider
 * @param {string} params.sliderName - the name of the slider, used for the arrows event listeners
 * @param {string} [params.sliderParentSelector=section] - The selector of the parent element of the slider.
 * @param {Object} [params.buttonsBreakpoints={}] - An object with the breakpoints for the slider buttons.
 * @param {Object} [params.options={}] - Object with additional options for slick slider
 * @param {string} params.ajaxUrl - The URL to which the AJAX request will be sent.
 * @param {Object} [params.postData={}] - The data that will be sent to the server via ajax
 * @param {number} [params.disableBreakpoint=null] - If you want to disable the slider on a certain breakpoint, pass
 * the breakpoint value.
 */
const initAjaxProductsSlider = async ({
    sliderNode,
    sliderName,
    sliderParentSelector = "section",
    buttonsBreakpoints = {},
    options = {},
    ajaxUrl,
    postData = {},
    disableBreakpoint = null,
}) => {
    const slider = $(sliderNode);

    if (!slider.hasClass("loading")) {
        return;
    }

    const sliderParent = slider.closest(sliderParentSelector);
    const itemsCount = await getItemsForSlider(slider, sliderParent, ajaxUrl, postData);

    onInitSlickSlider({
        sliderNode,
        sliderName,
        buttonsBreakpoints,
    });

    if (disableBreakpoint && !window.matchMedia(`(min-width: ${disableBreakpoint + 1}px)`).matches) {
        lazyLoadingInstance(`${sliderNode} .js-lazy`);
    } else {
        initSlickSlider({ slider, itemsCount, options });
    }

    if (disableBreakpoint) {
        onResizeCallback(() => {
            if (window.matchMedia(`(max-width: ${disableBreakpoint}px)`).matches) {
                if (slider.hasClass("slick-initialized")) {
                    slider.slick("unslick");
                }

                return;
            }

            if (!slider.hasClass("slick-initialized")) {
                initSlickSlider({ slider, itemsCount, options });
            }
        });
    }

    removeAjaxLoader(sliderParent, () => slider.removeClass("loading"));
};

export default initAjaxProductsSlider;
