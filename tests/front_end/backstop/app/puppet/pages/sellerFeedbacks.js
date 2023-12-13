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

    const dataExternal = {
        userName: variables.name.medium,
        date: variables.dateFormat.withoutTime,
        description: variables.lorem(200),
    }

    const dataForm = {
        description: variables.lorem(1000),
        text: variables.lorem(700),
        reason: 2,
    }

    await require("../modules/sellerSidebar")(page);
    await page.waitForFunction(replaceDataForFeedbacks, {}, data);
    await page.waitForFunction(replaceDataForReplyFeedbacks, {}, data);
    await page.waitForFunction(replaceDataForExternalFeedbacks, {}, dataExternal);

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
            item.querySelector(`[atas="global__company-feedbacks__item_photo"]`).src = data.pictureImg;
            item.querySelector(`[atas="global__company-feedbacks__item_title"]`).textContent = data.title;
            item.querySelector(`[atas="global__company-feedbacks__item_country-flag"]`).src = data.country.flag;
            item.querySelector(`[atas="global__company-feedbacks__item_country-name"]`).textContent = data.country.name;
            item.querySelector(`[atas="global__company-feedbacks__item_user-name"]`).textContent = data.userName;
            item.querySelector(`[atas="global__company-feedbacks__item_product-title"]`).textContent = data.itemTitle;
            item.querySelector(`[atas="global__company-feedbacks__item_date"]`).textContent = data.date;
            item.querySelector(`[atas="global__company-feedbacks__item_text"]`).textContent = data.description;
        });

        return true;
    })();
};

const replaceDataForReplyFeedbacks = data => {
    return (() => {
        const replyItems = document.querySelectorAll(`[atas="global__company-feedbacks-reply__item"]`);
        if(replyItems.length) {
            replyItems.forEach(item => {
                item.querySelector(`[atas="global__company-feedbacks-reply__item-user-name"]`).textContent = data.userName;
                item.querySelector(`[atas="global__company-feedbacks-reply__item-date"]`).textContent = data.date;
                item.querySelector(`[atas="global__company-feedbacks-reply__item-text"]`).textContent = data.description;
            });
        }


        return true;
    })();
};

const replaceDataForExternalFeedbacks = data => {
    return (() => {
        document.querySelectorAll(`[atas="global__company-external-feedbacks__item"]`).forEach(item => {
            item.querySelector(`[atas="global__company-external-feedbacks__item_user-name"]`).textContent = data.userName;
            item.querySelector(`[atas="global__company-external-feedbacks__item_date"]`).textContent = data.date;
            item.querySelector(`[atas="global__company-external-feedbacks__item_text"]`).textContent = data.description;
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
