module.exports = async (page, scenario) => {
    // Questions
    const variables = require("../variables/variables");
    const functions = {
        click: require("../functions/click"),
    };

    // Show more comments
    if (scenario.showMoreComments) {
        await page.waitForFunction(showMoreComments);
        await page.waitForNetworkIdle();
    }

    // Change data for Questions
    await require("../modules/question")(page);

    // Open reply popup
    if (scenario.showReplyPopup) {
        await functions.click(page, `[atas="page__community-detail__replies_add-reply-btn"]`);
        await page.waitForSelector(`[atas="popup__add-answer__form_submit-btn"]`);
        await page.waitForNetworkIdle();

        // Submit empty form
        if (scenario.validationError) {
            await page.waitForTimeout(500);
            await functions.click(page, `[atas="popup__add-answer__form_submit-btn"]`);
        }

        // Add reply success
        if (scenario.addReply) {
            await page.waitForFunction(
                require("../functions/text"),
                {},
                {
                    selectorAll: `[atas="popup__add-asnwer__form_textarea"]`,
                    text: variables.lorem(200),
                }
            );
            await page.waitForTimeout(500);
            await functions.click(page, `[atas="popup__add-answer__form_submit-btn"]`);
            await page.waitForNetworkIdle();
            await page.waitForSelector(".bootstrap-dialog");
        }
    }

    // Open comment popup
    if (scenario.showCommentPopup) {
        await functions.click(page, `[atas="page__community-detail__question-answers_comment-btn"]`);
        await page.waitForNetworkIdle();
        await page.waitForSelector(`[atas="popup__comment-answer__form_submit-btn"]`);

        // Submit empty form
        if (scenario.validationError) {
            await page.waitForTimeout(500);
            await functions.click(page, `[atas="popup__comment-answer__form_submit-btn"]`);
        }

        // Add comment success
        if (scenario.addComment) {
            await page.waitForFunction(
                require("../functions/text"),
                {},
                {
                    selectorAll: `[atas="popup__comment-answer__form_textarea"]`,
                    text: variables.lorem(200),
                }
            );
            await page.waitForTimeout(500);
            await functions.click(page, `[atas="popup__comment-answer__form_submit-btn"]`);
            await page.waitForSelector(".bootstrap-dialog");
        }
    }
};

function showMoreComments() {
    return (async () => {
        const showMoreBtns = document.querySelectorAll(`[atas="page__community-detail__question-answers_comments-count-btn"]`);
        showMoreBtns.forEach(btn => btn.click());

        return true;
    })();
}
