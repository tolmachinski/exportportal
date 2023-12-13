import $ from "jquery";

const lazyLoadingScriptOnScroll = (selector, callback, threshold) => {
    if (selector.length === 0) {
        return;
    }

    let isLoad = false;
    const lazyFunction = function () {
        if (isLoad) return;

        const targetOffsetTop = selector.offset().top;
        const targetOuterHeight = selector.outerHeight();
        const windowScrollTop = $(globalThis).scrollTop();
        const windowHeight = $(globalThis).height();
        const displayThreshold = threshold.match(/%/g) ? (windowHeight / 100) * parseInt(threshold, 10) : parseInt(threshold, 10) || 200;
        if (windowScrollTop > targetOffsetTop) {
            if (windowScrollTop - targetOffsetTop - targetOuterHeight < displayThreshold) {
                isLoad = true;
                callback();
            }

            return;
        }
        if (targetOffsetTop - windowScrollTop - windowHeight < displayThreshold) {
            isLoad = true;
            callback();
        }
    };
    $(globalThis).on("scroll", lazyFunction);
    if (document.readyState === "complete") {
        lazyFunction();
    } else {
        $(globalThis).on("load", lazyFunction);
    }
};

export default lazyLoadingScriptOnScroll;
