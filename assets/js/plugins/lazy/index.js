import { BACKSTOP_TEST_MODE } from "@src/common/constants";

// @ts-nocheck
let lazyLoadingObserver;

const lazyLoadingInstance = (selector, { threshhold = "0px" } = {}) => {
    const lazyLoadingObserverOptions = {
        roomMargin: threshhold,
    };

    const loading = (img, observer) => {
        /* eslint-disable no-param-reassign */
        // Img tag
        const dataSrc = img.dataset.src;
        const dataSrcset = img.dataset.srcset;
        if (!dataSrc) {
            return;
        }
        img.src = dataSrc;
        img.removeAttribute("data-src");
        if (dataSrcset) {
            img.srcset = dataSrcset;
            img.removeAttribute("data-srcset");
        }
        // Source tag
        img.parentNode.querySelectorAll("source").forEach(source => {
            if (source.dataset.srcset) {
                source.srcset = source.dataset.srcset;
                source.removeAttribute("data-srcset");
            }
        });
        img.classList.add("loaded");
        observer?.unobserve(img);
    };

    // Initialization observers on all images
    const images = document.querySelectorAll(`${selector}:not(.loaded)`);

    if ("IntersectionObserver" in window && !BACKSTOP_TEST_MODE) {
        if (lazyLoadingObserver) {
            images.forEach(img => lazyLoadingObserver.unobserve(img));
        }
        // DOM observer
        lazyLoadingObserver = new IntersectionObserver(changes => {
            changes.forEach(change => {
                if (change.isIntersecting) {
                    const img = change.target;
                    loading(img, lazyLoadingObserver);
                }
            });
        }, lazyLoadingObserverOptions);
        images.forEach(img => lazyLoadingObserver.observe(img));
    } else {
        images.forEach(img => loading(img));
    }
};

export default lazyLoadingInstance;
