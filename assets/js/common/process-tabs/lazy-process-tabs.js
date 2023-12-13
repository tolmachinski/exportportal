import initProcesTabs from "@src/common/process-tabs/process-tabs";

/**
 * @param {string|HTMLElement|JQuery<any>} selector
 */
export default function resizePage(selector) {
    initProcesTabs(selector);

    const resizeObserver = new ResizeObserver(entries => {
        entries.forEach(entry => {
            initProcesTabs(entry.target);
        });
    });

    resizeObserver.observe(selector[0]);
}
