module.exports = async (page, scenario) => {
    const variables = require("../variables/variables");
    const functions = {
        click: require("../functions/click"),
    };
    const data = {
        pictureImg: variables.img(391, 295),
        category: "Backstop test category",
        title: scenario.videoTitle || `backstop-video-${new Date().getTime()}`,
        staticTitle: variables.lorem(150),
        text: variables.lorem(500),
        videoUrl: "https://youtu.be/6cP5ntW9EzY",
        email: variables.mail,
    };

    await require("../modules/sellerSidebar")(page);
    await page.waitForFunction(replaceDataForVideos, {}, data);

    if (scenario.showHeadingDropdown) {
        await functions.click(page, `[atas="page__seller-videos__heading_dropdown-btn"]`);

        if (scenario.openAddVideoPopup) {
            await functions.click(page, `[atas="page__seller-videos__heading_dropdown-menu_add-video-btn"]`);
            await page.waitForNetworkIdle();
        }

        if (scenario.submitEmptyForm) {
            await functions.click(page, `[atas="popup__seller-videos__add-video-form_save-btn"]`);
            await page.waitForSelector(variables.systMessCardClass, { visible: true });
        }

        if (scenario.submitSuccessForm) {
            await page.waitForFunction(fillAddVideoForm, {}, data);
            await page.waitForTimeout(500);
            await functions.click(page, `[atas="popup__seller-videos__add-video-form_save-btn"]`);
            await page.waitForSelector(variables.systMessCardClass, { visible: true });
        }
    }

    if (scenario.showVideoDropdown) {
        await functions.click(page, `[atas="page__seller-videos__item_dropdown-btn"]`);

        if (scenario.openSharePopup) {
            await functions.click(page, `[atas="page__seller-videos__item_dropdown-menu_share-btn"]`);
            await page.waitForNetworkIdle();

            if (scenario.submitEmptyForm) {
                await functions.click(page, `[atas="popup__seller-share-video__form_send-btn"]`);
                await page.waitForSelector(variables.systMessCardClass, { visible: true });
            }

            if (scenario.submitFormSuccess) {
                await page.waitForFunction(fillShareForm, {}, data);
                await page.waitForTimeout(500);
                await functions.click(page, `[atas="popup__seller-share-video__form_send-btn"]`);
                await page.waitForNetworkIdle();
                await page.waitForSelector(variables.systMessCardClass, { visible: true });
            }
        }

        if (scenario.openEmailPopup) {
            await functions.click(page, `[atas="page__seller-videos__item_dropdown-menu_email-btn"]`);
            await page.waitForNetworkIdle();

            if (scenario.submitEmptyForm) {
                await functions.click(page, `[atas="popup__seller-email-video__form_send-btn"]`);
                await page.waitForSelector(variables.systMessCardClass, { visible: true });
            }

            if (scenario.submitFormSuccess) {
                await page.waitForFunction(fillEmailForm, {}, data);
                await page.waitForTimeout(500);
                await functions.click(page, `[atas="popup__seller-email-video__form_send-btn"]`);
                await page.waitForNetworkIdle();
                await page.waitForSelector(variables.systMessCardClass, { visible: true });
            }
        }

        if (scenario.openEditPopup) {
            await functions.click(page, `[atas="page__seller-videos__item_dropdown-menu_edit-btn"]`);
            await page.waitForNetworkIdle();
            await page.waitForFunction(fillAddVideoForm, {}, data);
            await page.waitForTimeout(500);

            if (scenario.submitFormSuccess) {
                await functions.click(page, `[atas="popup__seller-videos__add-video-form_save-btn"]`);
                await page.waitForNetworkIdle();
                await page.waitForSelector(variables.systMessCardClass, { visible: true });
            }
        }

        if (scenario.openReportPopup) {
            await functions.click(page, `[atas="page__seller-videos__item_dropdown-menu_report-btn"]`);
            await page.waitForNetworkIdle();

            if (scenario.submitEmptyForm) {
                await functions.click(page, `[atas="popup__complains__report-form_save-btn"]`);
                await page.waitForSelector(variables.systMessCardClass, { visible: true });
            }

            if (scenario.submitFormSuccess) {
                await page.waitForFunction(fillReportForm, {}, data);
                await page.waitForTimeout(500);
                await functions.click(page, `[atas="popup__complains__report-form_save-btn"]`);
                await page.waitForNetworkIdle();
                await page.waitForSelector(variables.systMessCardClass, { visible: true });
            }
        }
    }
};

const replaceDataForVideos = data => {
    return (async function () {
        document.querySelectorAll(`[atas="page__seller-videos__item"]`).forEach(item => {
            item.querySelector(`[atas="page__seller-videos__item-image"]`).src = data.pictureImg;
            item.querySelector(`[atas="page__seller-videos__item-title"]`).textContent = data.staticTitle;
            item.querySelector(`[atas="page__seller-videos__item-category"]`).textContent = data.category;
            item.querySelector(`[atas="page__seller-videos__item-comments-count"]`).textContent = 99999;
        });

        return true;
    })();
};

const fillAddVideoForm = data => {
    return (async function () {
        const selectCategory = document.querySelector(`[atas="global__categories-field__category-select"]`);
        selectCategory.options[1].selected = true;
        selectCategory.dispatchEvent(new Event("change"));

        document.querySelector(`[atas="popup__seller-videos__add-video-form_title-input"]`).value = data.title;
        document.querySelector(`[atas="popup__seller-videos__add-video-form_url-input"]`).value = data.videoUrl;
        document.querySelector(`[atas="popup__seller-videos__add-video-form_description-textarea"]`).value = data.text;
        document.querySelector(`[atas="popup__seller-videos__add-video-form_post-on-wall-checkbox"]`).click();

        return true;
    })();
};

const fillShareForm = data => {
    return (async function () {
        document.querySelector(`[atas="popup__seller-share-video__form_message-textarea"]`).value = data.text;

        return true;
    })();
};

const fillEmailForm = data => {
    return (async function () {
        document.querySelector('[atas="popup__seller-email-video__form_emails-input"]').value = data.email;
        document.querySelector('[atas="popup__seller-email-video__form_message-textarea"]').value = data.text;

        return true;
    })();
};

const fillReportForm = data => {
    return (async function () {
        const selectReason = document.querySelector(`[atas="popup__complains__report-form_reason-select"]`);
        selectReason.options[1].selected = true;
        selectReason.dispatchEvent(new Event("change"));

        document.querySelector(`[atas="popup__complains__report-form_message-textarea"]`).value = data.text;
        return true;
    })();
};
