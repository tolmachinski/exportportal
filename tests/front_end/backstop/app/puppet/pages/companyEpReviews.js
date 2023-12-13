module.exports = async (page, scenario) => {
    const variables = require("../variables/variables");
    const functions = {
        click: require("../functions/click"),
    };
    const data = {
        title: variables.lorem(50),
        text: variables.lorem(500),
        reason: 2,
    };

    await require("../modules/sellerSidebar")(page);

    // Reviews 0 images, 10 images, 5 images...
    await require("../modules/ratingBootstrap")(page);
    await require("../modules/reviews")(page);

    if (scenario.openReviewDropdown) {
        await functions.click(page, `[atas="global__reviews__dropdown-btn"]`);

        if (scenario.openReasonPopup) {
            await functions.click(page, `[atas="global__reviews__dropdown-menu_report-btn"]`);
            await page.waitForNetworkIdle();

            if (scenario.sendReport) {
                await page.waitForFunction(fillReportForm, {}, data);
                await page.waitForTimeout(500);
                await functions.click(page, `[atas="popup__complains__report-form_save-btn"]`);
                await page.waitForNetworkIdle();
                await page.waitForSelector(variables.systMessCardClass);
            }
        }

        if (scenario.openEditPopup) {
            await functions.click(page, `[atas="global__reviews__dropdown-menu_edit-btn"]`);
            await page.waitForNetworkIdle();
            await page.waitForFunction(fillReviewForm, {}, data);

            if (scenario.submitEditPopup) {
                await page.waitForTimeout(500);
                await functions.click(page, `[atas="popup__reviews__form_submit-btn"]`);
                await page.waitForNetworkIdle();
                await page.waitForSelector(variables.systMessCardClass);
            }
        }

        if (scenario.openEditReplyPopup) {
            await functions.click(page, `[atas="global__reviews__reply_dropdown-btn"]`);
            await page.waitForNetworkIdle();
            await page.waitForFunction(fillReplyForm, {}, data);

            if (scenario.submitEditReplyPopup) {
                await page.waitForTimeout(500);
                await functions.click(page, `[atas="popup__reviews__form_submit-btn"]`);
                await page.waitForNetworkIdle();
                await page.waitForSelector(variables.systMessCardClass);
            }
        }
    }

    if (scenario.openReplyDropdown) {
        await functions.click(page, `[atas="global__reviews__reply_dropdown-btn"]`);

        if (scenario.openAddReplyPopup) {
            await functions.click(page, `[atas="global__reviews__dropdown-menu_add-reply-btn"]`);
            await page.waitForNetworkIdle();
        }

        if (scenario.openEditReplyPopup) {
            await functions.click(page, `[atas="global__reviews__reply_dropdown-menu_edit-btn"]`);
            await page.waitForNetworkIdle();
            await page.waitForFunction(fillReplyForm, {}, data);

            if (scenario.editReply) {
                await page.waitForTimeout(500);
                await functions.click(page, `[atas="popup__reviews__reply-form_submit-btn"]`);
                await page.waitForNetworkIdle();
                await page.waitForSelector(variables.systMessCardClass);
            }
        }
    }
};

const fillReportForm = data => {
    return (async function () {
        document.querySelector('[atas="popup__complains__report-form_message-textarea"]').value = data.text;
        document.querySelector('[atas="popup__complains__report-form_reason-select"]').value = data.reason;

        return true;
    })();
};

const fillReviewForm = data => {
    return (async function () {
        document.querySelector('[atas="popup__reviews__form_title-input"]').value = data.title;
        document.querySelector('[atas="popup__reviews__form_description-textarea"]').value = data.text;

        return true;
    })();
};

const fillReplyForm = data => {
    return (async function () {
        document.querySelector('[atas="popup__reviews__reply-form_description-textarea"]').value = data.text;

        return true;
    })();
};
