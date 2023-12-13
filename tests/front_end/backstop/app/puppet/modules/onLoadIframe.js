module.exports = async (page, selector) => {
    await page.waitForSelector(selector);
    await page.waitForFunction(onLoadIframe, {}, selector);
};

function onLoadIframe(selector) {
    return (async () => {
        await new Promise(resolve => {
            document.querySelector(selector).addEventListener("load", () => {
                resolve();
            });
        });

        return true;
    })();
}
