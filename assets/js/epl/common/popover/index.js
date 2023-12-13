// @ts-ignore
import { createPopper } from "@popperjs/core";
import { openFancyboxPopup } from "@src/plugins/fancybox/v3";
import delay from "@src/util/async/delay";

/**
 * Make the tooltip visible
 *
 * @param {object} popperInstance
 * @param {object} tooltip
 */
const showPopover = (popperInstance, tooltip) => {
    tooltip.classList.add("active", `tooltip--${tooltip.dataset.popperPlacement}`);

    // Enable the event listeners
    popperInstance.setOptions(options => ({
        ...options,
        modifiers: [...options.modifiers, { name: "eventListeners", enabled: true }],
    }));

    // Update its position
    popperInstance.update();
};

/**
 * Hide the tooltip
 *
 * @param {object} popperInstance
 * @param {object} tooltip
 */
const hidePopover = (popperInstance, tooltip) => {
    tooltip.classList.remove(`tooltip--${tooltip.dataset.popperPlacement}`);
    tooltip.classList.remove("active");

    // Disable the event listeners
    popperInstance.setOptions(options => ({
        ...options,
        modifiers: [...options.modifiers, { name: "eventListeners", enabled: false }],
    }));
};

/**
 * Create tooltip
 *
 * @param {string} btnSelector
 * @param {string} tooltipSelector
 * @param {Object} options
 * @returns {Object} popover Instance
 */
const createPopover = (btnSelector, tooltipSelector, options = {}) => {
    const button = document.querySelector(btnSelector);
    const tooltip = document.querySelector(tooltipSelector);
    const popoverMob = document.getElementById("js-popover-notifications-mep");
    const tooltipNotification = document.getElementById("js-tooltip-notifications");
    const defaultOptions = {
        placement: "bottom",
        trigger: "hover",
        modifiers: [
            {
                name: "offset",
                options: {
                    offset: [0, 8],
                },
            },
        ],
    };
    const popoverOptions = { ...defaultOptions, ...options };
    const popperInstance = createPopper(button, tooltip, popoverOptions);

    if (popoverOptions.trigger === "click") {
        popoverMob.addEventListener("click", async () => {
            if (popoverMob.classList.contains("js-popover-toggled")) {
                hidePopover(popperInstance, tooltip);
                const { title, type, src, mw } = popoverMob.dataset;
                await openFancyboxPopup(
                    {
                        title,
                        type,
                        src,
                    },
                    {
                        mw: `${mw}px`,
                    }
                );
                await delay(100);
                popoverMob.classList.remove("js-popover-toggled");
            } else {
                showPopover(popperInstance, tooltip);
                await delay(100);
                popoverMob.classList.add("js-popover-toggled");
            }
        });
    }

    if (popoverOptions.trigger === "hover") {
        button.addEventListener("mouseenter", showPopover.bind(showPopover, popperInstance, tooltip));
        button.addEventListener("mouseleave", hidePopover.bind(hidePopover, popperInstance, tooltip));
        tooltipNotification.addEventListener("mouseenter", () => {
            if (globalThis.screen.width > 991) {
                showPopover(popperInstance, tooltipNotification);
            }
        });
        tooltipNotification.addEventListener("mouseleave", () => {
            if (globalThis.screen.width > 991) {
                hidePopover(popperInstance, tooltipNotification);
            }
        });
    }

    document.querySelector("body").addEventListener("click", event => {
        if (!popoverMob.contains(event.target) && !tooltip.contains(event.target)) {
            popoverMob.classList.remove("js-popover-toggled");
            hidePopover(popperInstance, tooltip);
        }
    });

    return popperInstance;
};

export default createPopover;
export { showPopover, hidePopover };
