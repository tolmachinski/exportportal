module.exports = async (page, scenario) => {
    const variables = require("../variables/variables");
    const functions = {
        click: require("../functions/click"),
        text: require("../functions/text"),
    };

    const data = {
        newsImg: variables.img(110, 62),
        date: variables.dateFormat.withoutTime,
        title: variables.lorem(150),
        text: variables.lorem(700),
        email: variables.mail,
        reason: 2,
        newsEditImg: variables.img(220,124),
    };

    await require("../modules/sellerSidebar")(page);
    await page.waitForFunction(replaceDataForItems, {}, data);

    await page.waitForFunction(functions.text, {}, {
        selectorAll: `[atas="page__company-news__list_actions-dropdown-menu_comments-btn"]`,
        text: 999,
    });

    if (scenario.addNews) {
        await functions.click(page, `[atas="page__company-news__header_dropdown-menu_add-btn"]`);
        await page.waitForNetworkIdle();
        await page.waitForFunction(fillNewsForm, {}, data);
    }

    if (scenario.dropdown) {
        await functions.click(page, `[atas="page__company-news__list_actions-dropdown-btn"]`);

        if (scenario.share) {
            await functions.click(page, `[atas="page__company-news__list_actions-dropdown-menu_share-btn"]`);
            await page.waitForNetworkIdle();
            await page.waitForFunction(fillShareFollow, {}, data);

            if (scenario.sendShare) {
                await functions.click(page, `[atas="popup__company-news__share-form_send-btn"]`);
            }
        }

        if (scenario.email) {
            await functions.click(page, `[atas="page__company-news__list_actions-dropdown-menu_email-btn"]`);
            await page.waitForNetworkIdle();
            await page.waitForFunction(fillEmailsForm, {}, data);

            if (scenario.sendEmail) {
                await functions.click(page, `[atas="popup__company-news__email-form__send-btn"]`);
            }
        }

        if (scenario.report) {
            await functions.click(page, `[atas="page__company-news__list_actions-dropdown-menu_report-btn"]`);
            await page.waitForNetworkIdle();
            await page.waitForFunction(fillReportForm, {}, data);

            if (scenario.sendReport) {
                await functions.click(page, `[atas="popup__complains__report-form_save-btn"]`);
            }
        }

        if (scenario.edit) {
            await functions.click(page, `[atas="page__company-news__list_actions-dropdown-menu_edit-btn"]`);
            await page.waitForNetworkIdle();
            await page.waitForFunction(fillNewsForm, {}, data);
        }
    }
};

const replaceDataForItems = data => {
    return (async function () {
        document.querySelectorAll(`[atas="page__company-news__list-item"]`).forEach(item => {
            const image = item.querySelector(`[atas="page__company-news__list_image"]`);

            if (image) {
                image.src = data.newsImg;
            }

            item.querySelector(`[atas="page__company-news__list_date"]`).textContent = data.date;
            item.querySelector(`[atas="page__company-news__list_title"]`).textContent = data.title;
            item.querySelector(`[atas="page__company-news__list_description"]`).textContent = data.text;
        });

        return true;
    })();
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


const fillNewsForm = data => {
    return (async function () {
        const image = document.querySelector(`[atas="popup__seller-news__edit-comment-form_image"]`);

        if (image) {
            image.src = data.newsEditImg;
        }
        document.querySelector(`[atas="popup__seller-news__edit-comment-form_title-input"]`).value = data.title;

        return true;
    })();
};

