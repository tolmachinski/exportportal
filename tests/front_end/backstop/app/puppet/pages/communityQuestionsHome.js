module.exports = async (page, scenario) => {
    const variables = require("../variables/variables");
    const functions = {
        click: require("../functions/click"),
    };
    const data = {
        text: variables.lorem(50),
        textarea: variables.lorem(500),
    };

    // Replace banner
    await page.waitForFunction(
        require("../functions/picture"),
        {},
        {
            selectorAll: `[atas="global__banner-picture"]`,
            src: variables.img(300, 500),
            media: {
                tablet: {
                    attr: "(max-width:991px)",
                    src: variables.img(738, 190),
                },
                mobile: {
                    attr: "(max-width:575px)",
                    src: variables.img(290, 500),
                },
            },
        }
    );

    // Change data for Questions
    await require("../modules/question")(page);

    if (scenario.openAskQuestionPopup) {
        // Open ask a question popup
        await functions.click(page, `[atas="page__community__header_ask-question-btn"]`);
        await page.waitForNetworkIdle();
        await page.waitForSelector(`[atas="popup__ask-question__form_submit-btn"]`);
        await page.waitForTimeout(2000);
    }

    // Ask a question popup with validation error
    if (scenario.askQuestionPopupValidationError) {
        await functions.click(page, `[atas="popup__ask-question__form_submit-btn"]`);
        await page.waitForTimeout(500);
    }

    // Add question
    if (scenario.addQuestion) {
        await page.waitForFunction(fillFormAskAQuestion, {}, data);
        await page.waitForTimeout(500);
        await functions.click(page, `[atas="popup__ask-question__form_submit-btn"]`);
        await page.waitForNetworkIdle();
    }
};

async function fillFormAskAQuestion(data) {
    return (async function () {
        document.querySelector(`[atas="popup__ask-question__form_category-select"]`).value = 4;
        document.querySelector(`[atas="popup__ask-question__form_country-select"]`).value = 5;
        document.querySelector(`[atas="popup__ask-question__form_question-title-input"]`).value = data.text;
        document.querySelector(`[atas="popup__ask-question__form_description-textarea"]`).value = data.textarea;

        return true;
    })();
}
