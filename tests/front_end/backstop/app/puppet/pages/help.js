module.exports = async (page, scenario) => {
    const functions = {
        click: await require("../functions/click"),
    };
    const variables = require("../variables/variables")

    if (scenario.topicsTab){
        await functions.click(page, `[atas="page__help-search__topics-tab"]`);
        await page.waitForTimeout(500);

        // Replace topics list
        await require("../modules/topicsList")(page);
    }

    if (scenario.questionsTab){
        await functions.click(page, `[atas="page__help-search__questions-tab"]`);
        await page.waitForTimeout(500);

        // Replace questions list
        await require("../modules/question")(page);
    }

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
    // Change text for found counter
    await page.waitForFunction(require('../functions/counter'), {}, {
        selectorAll: `[atas="page__help-search__counter"]`,
        value: `9999`,
    });

    if (scenario.faqTab) {
        // Replace faq list
        await require("../modules/faqList")(page);
    }

    if(scenario.hoverCard){
        await page.waitForFunction(hoverCard);
    }

    // Open Shedule Demo popup
    if (scenario.openScheduleDemoPopup) {
        await require("../modules/scheduleDemoPopup")(
            page,
            scenario,
            scenario.type
        );
    }
};

function hoverCard(){
    return (async function(){
        document.querySelector(`[atas="help__hover-card"]`).classList.add('active');

        return true
    })()
}
