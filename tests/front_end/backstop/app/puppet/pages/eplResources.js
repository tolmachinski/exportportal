module.exports = async (page, scenario) => {
    const variables = require('../variables/variables');
    const functions = {
        click: require('../functions/click'),
        clickAll: require('../functions/clickAll'),
        text: require('../functions/text'),
    }
    const data = {
        questionTitle: variables.lorem(80),
        questionText: variables.lorem(500),
        questionCategory: "Export Portal Basics Questions",
        countQuestions: "10 Questions",
        videoImg: variables.img(1500, 850),
    }

    // Change data for Questions
    await require('../modules/question')(page);
    await page.waitForTimeout(500);

    // Change title for faq category
    await page.waitForFunction(functions.text, {}, {
        selectorAll: `[atas="epl-resources__faq_category-title"]`,
        text: data.questionCategory
    });

    // Change title for faq category
    await page.waitForFunction(functions.text, {}, {
        selectorAll: `[atas="epl-resources__faq_category-count-questions"]`,
        text: data.countQuestions
    });

    // Change title for popular questions
    await page.waitForFunction(functions.text, {}, {
        selectorAll: `[atas="epl-resources__faq_popular-questions-title"]`,
        text: data.questionTitle
    });

    // Change title for popular text
    await page.waitForFunction(functions.text, {}, {
        selectorAll: `[atas="epl-resources__faq_popular-questions-text"]`,
        text: data.questionText
    });

    // Open faq
    if (scenario.openFaq) {
        await functions.clickAll(page, `[atas^="epl-resources__faq-list-btn-"]`);
    }

    // Open popup registration guide video
    if (scenario.openRegistrationGuideVideo) {
        await functions.click(page, `[atas="epl-resources__registration-guide_video-btn"]`);
        await page.waitForSelector(".fancybox-video .fancybox-iframe");
        await page.waitForFunction(onLoadIframe);
    }

    // Open popup profile completion guide video
    if (scenario.openProfileCompletionGuideVideo) {
        await functions.click(page, `[atas="epl-resources__profile-completion_video-btn"]`);
        await page.waitForSelector(".fancybox-video .fancybox-iframe");
        await page.waitForFunction(onLoadIframe);
    }
};

function onLoadIframe() {
    return (async () => {
        await new Promise(resolve => {
            document.querySelector(`.fancybox-video .fancybox-iframe`).addEventListener("load", () => {
                resolve();
            });
        });

        return true;
    })();
}
