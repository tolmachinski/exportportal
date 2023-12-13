module.exports = async (page, scenario) => {
    const { showDropdown, openAddReportPopup, fillForm, submitForm, openEditReplyPopup, openAddReplyPopup } = scenario;
    const variables = require("../variables/variables");
    const functions = {
        click: require("../functions/click"),
    };

    const data = {
        pictureImg: variables.img(75, 75),
        title: variables.lorem(200),
        country: variables.country,
        userName: variables.name.medium,
        itemTitle: variables.lorem(255),
        date: variables.dateFormat.withoutTime,
        description: variables.lorem(1000),
    };

    const dataForm = {
        description: variables.lorem(1000),
        text: variables.lorem(700),
        reason: 2,
    }

    await require("../modules/sellerSidebar")(page);
    await page.waitForFunction(replaceDataForFeedbacks, {}, data);

    if (showDropdown) {
        await functions.click(page, `[atas="global__company-feedbacks__item_dropdown-btn"]`);

        if (openAddReportPopup) {
            await functions.click(page, `[atas="global__company-feedbacks__item_dropdown-menu_report-btn"]`);
            await page.waitForNetworkIdle();

            if (fillForm) {
                await page.waitForFunction(fillReportForm, {}, dataForm);
            }

            if (submitForm) {
                await functions.click(page, `[atas="popup__complains__report-form_save-btn"]`);
            }
        }

        if (openEditReplyPopup) {
            await functions.click(page, `[atas="global__company-feedbacks-reply__item_dropdown-menu_edit-btn"]`);
            await page.waitForNetworkIdle();

            if (submitForm) {
                await functions.click(page, `[atas="popup__edit-feedback-reply__form_save-btn"]`);
            }
        }

        if (openAddReplyPopup) {
            await functions.click(page, `[atas="global__company-feedbacks__item_dropdown-menu_reply-btn"]`);
            await page.waitForNetworkIdle();

            if (fillForm) {
                await page.waitForFunction(fillReplyForm, {}, dataForm);
            }

            if (submitForm) {
                await functions.click(page, `[atas="popup__add-feedback-reply__form_save-btn"]`);
            }
        }
    }
};

const replaceDataForFeedbacks = data => {
    return (() => {
        document.querySelectorAll(`[atas="global__company-feedbacks__item"]`).forEach(item => {
            item.querySelector(`[atas="global__company-feedbacks__item-photo"]`).src = data.pictureImg;
            item.querySelector(`[atas="global__company-feedbacks__item-title"]`).textContent = data.title;
            item.querySelector(`[atas="global__company-feedbacks__item-country-flag"]`).src = data.country.flag;
            item.querySelector(`[atas="global__company-feedbacks__item-country-name"]`).textContent = data.country.name;
            item.querySelector(`[atas="global__company-feedbacks__item-user-name"]`).textContent = data.userName;
            item.querySelector(`[atas="global__company-feedbacks__item-product-title"]`).textContent = data.itemTitle;
            item.querySelector(`[atas="global__company-feedbacks__item-date"]`).textContent = data.date;
            item.querySelector(`[atas="global__company-feedbacks__item-text"]`).textContent = data.description;
        });

        return true;
    })();
};

const fillReportForm = data => {
    return (() => {
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

const fillReplyForm = data => {
    return (() => {
        const textarea = document.querySelector('[atas="popup__add-feedback-reply__form_description-textarea"]');
        textarea.value = data.description;
        textarea.dispatchEvent(new Event("change", { bubbles: true }));

        return true;
    })();
};
