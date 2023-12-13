module.exports = async (page, scenario) => {
    const variables = require("../variables/variables");
    const data = {
        gifLogo: {
            img: variables.img(335, 250),
        },
    };
    await page.waitForFunction(changeGifLogoInFooter, {}, data.gifLogo);
};

function changeGifLogoInFooter(data) {
    return (async () => {
        const gif = document.querySelector(`[atas="logistics-ambassador__gif-logo"]`)
        gif.dataset.src = data.img;
        gif.src = data.img;

        return true;
    })();
}
