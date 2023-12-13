var lazyLoadingInstance = function (selector, options) {
    var lazyLoadingObserver;

    var lazyLoading = function (selector, options) {
        options = options || {};
        var threshhold = options.threshhold || "0px";
        var lazyLoadingObserverOptions = {
            roomMargin: threshhold,
        };

        var loading = function (img, observer) {
            // Img tag
            var dataSrc = img.dataset.src;
            var dataSrcset = img.dataset.srcset;
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
            img.parentNode.querySelectorAll("source").forEach(function (source) {
                if (source.dataset.srcset) {
                    source.srcset = source.dataset.srcset;
                    source.removeAttribute("data-srcset");
                }
            });
            img.classList.add("loaded");
            if (observer) {
                observer.unobserve(img);
            }
        };

        // Initialization observers on all images
        var images = document.querySelectorAll(selector + ":not(.loaded)");
        if ("IntersectionObserver" in window && !globalThis.__backstop_test_mode) {
            if (lazyLoadingObserver) {
                images.forEach(function(img) {
                    lazyLoadingObserver.unobserve(img)
                });
            }
            // DOM observer
            lazyLoadingObserver = new IntersectionObserver(function (changes) {
                changes.forEach(function(change) {
                    if (change.isIntersecting) {
                        var img = change.target;
                        loading(img, lazyLoadingObserver);
                    }
                });
            }, lazyLoadingObserverOptions);
            images.forEach(function(img) {
                lazyLoadingObserver.observe(img)
            });
        } else {
            images.forEach(function(img) {
                loading(img);
            });
        }
    };

    return lazyLoading(selector, options);
};
