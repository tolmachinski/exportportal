module.exports = async (page) => {
    await page.waitForSelector("html", { visible: true });
    await page.waitForSelector("footer", { visible: true });
    await page.evaluate(() => {
        return new Promise(resolve => {
            const getFilteredImagesSlider = nodeList => {
                return Array.from(nodeList).filter(img =>
                    img.offsetHeight !== 0 &&
                    img.offsetWidth !== 0 &&
                    window.getComputedStyle(img).display !== "none" &&
                    window.getComputedStyle(img).visibility !== "hidden" &&
                    window.getComputedStyle(img).opacity !== 0 &&
                    !img.closest(".slick-slide")
                );
            };
            const getFilteredImages = nodeList => {
                return Array.from(nodeList).filter(img =>
                    img.offsetHeight !== 0 &&
                    img.offsetWidth !== 0 &&
                    window.getComputedStyle(img).display !== "none" &&
                    window.getComputedStyle(img).visibility !== "hidden" &&
                    window.getComputedStyle(img).opacity !== 0
                );
            };
            (function () {
                var r = false;
                var h = async function () {
                    if (r) { return } r = true;
                    // DOM READY
                    let scrollHeight = document.body.scrollHeight;
                    await new Promise(resolve => {
                        const scrollDuration = scrollHeight / 2.5;
                        const scrollInterval = setInterval(function () {
                            const scrollStep = scrollHeight / (scrollDuration / 15);
                            if (window.scrollY + window.innerHeight < scrollHeight) {
                                window.scrollBy(0, scrollStep);
                            } else {
                                window.scrollBy(0, document.body.scrollHeight);
                                clearInterval(scrollInterval);
                                resolve(true);
                            };
                            scrollHeight = document.body.scrollHeight
                        }, 15);
                        setTimeout(() => {
                            clearInterval(scrollInterval);
                            resolve(true);
                        }, scrollDuration * 2);
                    });

                    await new Promise(resolve => {
                        const imgs = getFilteredImages(document.images);
                        let c = 0;

                        imgs.forEach(img => {
                            if (img.complete) {
                                incrementCounter();
                            } else {
                                img.addEventListener('load', incrementCounter);
                            }
                        });

                        function incrementCounter() {
                            c += 1;
                            if (c === imgs.length) {
                                resolve();
                            }
                        }
                    });
                    
                    resolve();
                    // DOM READY
                };
                var c = function () { document.removeEventListener("DOMContentLoaded", c), h(); };
                "loading" !== document.readyState ? window.setTimeout(h) : (document.addEventListener("DOMContentLoaded", c));
            })()
        });
    });
}
