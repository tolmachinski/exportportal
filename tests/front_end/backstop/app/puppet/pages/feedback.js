module.exports = async (page, scenario) => {
    const functions = {
        click: require("../functions/click"),
    };

    if (scenario.yesLikeIt) {
        await functions.click(page, `[atas="page__feedback__hear-your-feedback_yes-btn"]`);
        if(scenario.submit) {
            await functions.click(page, `[atas="page__feedback__hear-your-feedback_form_submit-btn"]`);
        }
    }

    if (scenario.noDontLikeIt) {
        await functions.click(page, `[atas="page__feedback__hear-your-feedback_no-btn"]`);
    }

    if (scenario.submitEmptyForm) {
        await functions.click(page, `[atas="page__feedback__hear-your-feedback_form_submit-btn"]`);
    }
};
