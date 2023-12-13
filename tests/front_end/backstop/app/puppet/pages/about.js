module.exports = async (page, scenario) => {
    const variables = require('../variables/variables');
    const data = {
        gif: variables.img(336, 369),
    };

    // Replace banner
    await page.waitForFunction(require('../functions/picture'), {}, {
        selectorAll: `[atas="global__banner-picture"]`,
        src: variables.img(1170, 200),
        media: {
            tablet: {
                attr: "(max-width:991px)",
                src: variables.img(738, 190),
            },
            mobile: {
                attr: "(max-width:575px)",
                src: variables.img(290, 500),
            }
        }
    });

    await page.waitForFunction(data => {
        return (async () => {
            // Replace gif in footer
            await new Promise(resolve => {
                const img = document.querySelector(`[atas="about__footer_content_img"]`);
                img.dataset.src = data.gif;
                img.onload = () => resolve();
                img.src = data.gif;
            });

            return true;
        })();
    }, {}, data);

    // Open Shedule Demo popup
    if (scenario.openScheduleDemoPopup) {
        await require("../modules/scheduleDemoPopup")(
            page,
            scenario,
            scenario.type
        );
    }

    await require("../modules/disableSliders")(page);
}
