module.exports = async (page, scenario) => {
    const variables = require("../variables/variables");
    const functions = {
        click: require("../functions/click"),
    };

    await require("../modules/sellerSidebar")(page);
    await require("../modules/comment")(page, 500);

    const data = {
        img: variables.img(730, 411),
        date: variables.dateFormat.withoutTime,
        text: variables.lorem(700),
        email: variables.mail,
        reason: 2,
    };

    await require("../modules/changeData")(page, [
        {
            selectorAll: `[atas="page__company-news-detail__image"]`,
            value: data.img,
            attr: "src",
            type: "image",
        },
        {
            selectorAll: `[atas="page__company-news-detail__description"]`,
            value: data.text,
        },
        {
            selectorAll: `[atas="page__company-news-detail__date"]`,
            value: data.date,
        },
        {
            selectorAll: `[atas="page__company-news-detail__actions-dropdown-menu_comments-count"]`,
            value: 999,
        },
    ]);

    if (scenario.dropdown) {
        await functions.click(page, `[atas="page__company-news-detail__actions-dropdown-btn"]`);

        if (scenario.share) {
            await functions.click(page, `[atas="page__company-news-detail__actions-dropdown-menu_share-btn"]`);
            await page.waitForNetworkIdle();
            await page.waitForFunction(fillShareFollow, {}, data);

            if (scenario.sendShare) {
                await functions.click(page, `[atas="popup__company-news__share-form_send-btn"]`);
            }
        }

        if (scenario.email) {
            await functions.click(page, `[atas="page__company-news-detail__actions-dropdown-menu_email-btn"]`);
            await page.waitForNetworkIdle();
            await page.waitForFunction(fillEmailsForm, {}, data);

            if (scenario.sendEmail) {
                await functions.click(page, `[atas="popup__company-news__email-form__send-btn"]`);
            }
        }

        if (scenario.report) {
            await functions.click(page, `[atas="page__company-news-detail__actions-dropdown-menu_report-btn"]`);
            await page.waitForNetworkIdle();
            await page.waitForFunction(fillReportForm, {}, data);

            if (scenario.sendReport) {
                await functions.click(page, `[atas="popup__complains__report-form_save-btn"]`);
            }
        }

        if (scenario.edit) {
            await functions.click(page, `[atas="page__company-news__list_actions-dropdown-menu_edit-btn"]`);
            await page.waitForNetworkIdle();
        }
    }

    if(scenario.editComment) {
        await functions.click(page, `[atas="page__company-news-detail__comments-actions_dropdown-menu_edit-btn"]`);
        await page.waitForNetworkIdle();
        await page.waitForFunction(fillEditCommentForm, {}, data);

        if (scenario.sendEditComment) {
            await functions.click(page, `[atas="popup__company-news-detail__edit-comment-form_submit-btn"]`);
        }
    }

    if(scenario.delete) {
        await functions.click(page, `[atas="page__company-news-detail__comments-actions_dropdown-menu_delete-btn"]`);
        await page.waitForNetworkIdle();
    }

    if(scenario.reportComment) {
        await functions.click(page, `[atas="page__company-news-detail__comments-actions_dropdown-menu_report-btn"]`);
        await page.waitForNetworkIdle();
        await page.waitForFunction(fillReportCommentForm, {}, data);

        if (scenario.sendReportComment) {
            await functions.click(page, `[atas="popup__complains__report-form_save-btn"]`);
        }
    }

};

const fillShareFollow = data => {
    return (async function () {
        const textarea = document.querySelector('[atas="popup__company-news__share-form_message-textarea"]');
        textarea.textContent = data.text;
        textarea.dispatchEvent(new Event("change", { bubbles: true }));
        return true;
    })();
};

const fillEmailsForm = data => {
    return (async function () {
        const textNodes = {
            text: document.querySelector('[atas="popup__company-news__email-form_message-textarea"]'),
            email: document.querySelector('[atas="popup__company-news__email-form_emails-input"]'),
        };

        Object.keys(textNodes).forEach(keyNodes => {
            textNodes[keyNodes].value = data[keyNodes];
            textNodes[keyNodes].dispatchEvent(new Event("change", { bubbles: true }));
        });

        return true;
    })();
};

const fillReportForm = data => {
    return (async function () {
        const textNodes = {
            text: document.querySelector('[atas="popup__complains__report-form_message-textarea"]'),
            reason: document.querySelector('[atas="popup__complains__report-form_reason-select"]'),
        };

        Object.keys(textNodes).forEach(keyNodes => {
            textNodes[keyNodes].value = data[keyNodes];
            textNodes[keyNodes].dispatchEvent(new Event("change", { bubbles: true }));
        });
        return true;
    })();
};

const fillEditCommentForm = data => {
    return (async function () {
        const textarea = document.querySelector('[atas="popup__company-news-detail__edit-comment-form_content-textarea"]');
        textarea.textContent = data.text;
        textarea.dispatchEvent(new Event("change", { bubbles: true }));
        return true;
    })();
};

const fillReportCommentForm = data => {
    return (async function () {
        const textNodes = {
            text: document.querySelector('[atas="popup__complains__report-form_message-textarea"]'),
            reason: document.querySelector('[atas="popup__complains__report-form_reason-select"]'),
        };

        Object.keys(textNodes).forEach(keyNodes => {
            textNodes[keyNodes].value = data[keyNodes];
            textNodes[keyNodes].dispatchEvent(new Event("change", { bubbles: true }));
        });
        return true;
    })();
};




