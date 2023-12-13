module.exports = async (page, scenario) => {
    const variables = require("../variables/variables");
    const functions = {
        click: require("../functions/click"),
    };

    const data = {
        img: variables.img(136, 130),
        date: variables.dateFormat.withoutTime,
        text: variables.lorem(700),
        email: variables.mail,
        reason: 2,
    };

    await require("../modules/sellerSidebar")(page);
    await page.waitForFunction(changeUpdatesItemsData, {}, data);

    if (scenario.dropdown) {
        await functions.click(page, `[atas="page__company-updates__list_actions-dropdown-btn"]`);

        if (scenario.share) {
            await functions.click(page, `[atas="page__company-updates__list_actions-dropdown-menu_share-btn"]`);
            await page.waitForNetworkIdle();
            await page.waitForFunction(fillShareFollow, {}, data);

            if (scenario.sendShare) {
                await functions.click(page, `[atas="popup__company-updates__share-form_send-btn"]`);
            }
        }

        if (scenario.email) {
            await functions.click(page, `[atas="page__company-updates__list_actions-dropdown-menu_email-btn"]`);
            await page.waitForNetworkIdle();
            await page.waitForFunction(fillEmailsForm, {}, data);

            if (scenario.sendEmail) {
                await functions.click(page, `[atas="popup__company-updates__email-form_send-btn"]`);
            }
        }

        if (scenario.report) {
            await functions.click(page, `[atas="page__company-updates__list_actions-dropdown-menu_report-btn"]`);
            await page.waitForNetworkIdle();
            await page.waitForFunction(fillReportForm, {}, data);

            if (scenario.sendReport) {
                await functions.click(page, `[atas="popup__complains__report-form_save-btn"]`);
            }
        }

        if (scenario.edit) {
            await functions.click(page, `[atas="page__company-updates__list_actions-dropdown-menu_edit-btn"]`);
            await page.waitForNetworkIdle();
        }
    }
};

const changeUpdatesItemsData = data => {
    return (async () => {
        document.querySelectorAll(`[atas="page__company-updates__list_item"]`).forEach(item => {
            const image = item.querySelector(`[atas="page__company-updates__list_image"]`);
            if (image) {
                image.src = data.img;
            }

            item.querySelector(`[atas="page__company-updates__list_date"]`).textContent = data.date;
            item.querySelector(`[atas="page__company-updates__list_text"]`).textContent = data.text;
        });

        return true;
    })();
};


const fillShareFollow = data => {
    return (async function () {
        const textarea = document.querySelector('[atas="popup__company-updates__share-form_message-textarea"]');
        textarea.textContent = data.text;
        textarea.dispatchEvent(new Event("change", { bubbles: true }));
        return true;
    })();
};

const fillEmailsForm = data => {
    return (async function () {
        const textNodes = {
            email: document.querySelector('[atas="popup__company-updates__email-form_emails-input"]'),
            text: document.querySelector('[atas="popup__company-updates__email-form_message-textarea"]'),
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
