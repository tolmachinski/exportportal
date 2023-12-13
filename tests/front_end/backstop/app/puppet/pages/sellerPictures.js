module.exports = async (page, scenario) => {
    const variables = require("../variables/variables");
    const functions = {
        click: require("../functions/click"),
    };
    const data = {
        pictureImg: variables.img(391, 295),
        title: variables.lorem(150),
        category: variables.lorem(100),
        text: variables.lorem(500),
        email: variables.mail,
    };

    await require("../modules/sellerSidebar")(page);
    await page.waitForFunction(replaceDataForPictures, {}, data);

    if (scenario.showHeadingDropdown) {
        await functions.click(page, `[atas="page__company-pictures__heading_dropdown-btn"]`);

        if (scenario.openAddPicturePopup) {
            await functions.click(page, `[atas="page__company-pictures__heading_dropdown-menu_add-picture-btn"]`);
            await page.waitForNetworkIdle();
        }

        if (scenario.submitEmptyForm) {
            await functions.click(page, `[atas="popup__seller-pictures-my__form_save-btn"]`);
            await page.waitForSelector(variables.systMessCardClass, { visible: true });
        }
    }

    if (scenario.showPictureDropdown) {
        await functions.click(page, `[atas="page__company-pictures__item_dropdown-btn"]`);

        if (scenario.openSharePopup) {
            await functions.click(page, `[atas="page__company-pictures__item_dropdown-menu_share-btn"]`);
            await page.waitForNetworkIdle();

            if (scenario.submitEmptyForm) {
                await functions.click(page, `[atas="popup__seller-pictures__share-form_send-btn"]`);
                await page.waitForSelector(variables.systMessCardClass, { visible: true });
            }

            if (scenario.submitFormSuccess) {
                await page.waitForFunction(fillShareForm, {}, data);
                await page.waitForTimeout(500);
                await functions.click(page, `[atas="popup__seller-pictures__share-form_send-btn"]`);
                await page.waitForNetworkIdle();
                await page.waitForSelector(variables.systMessCardClass, { visible: true });
            }
        }

        if (scenario.openEmailPopup) {
            await functions.click(page, `[atas="page__company-pictures__item_dropdown-menu_email-btn"]`);
            await page.waitForNetworkIdle();

            if (scenario.submitEmptyForm) {
                await functions.click(page, `[atas="popup__email-picture__form_send-btn"]`);
                await page.waitForSelector(variables.systMessCardClass, { visible: true });
            }

            if (scenario.submitFormSuccess) {
                await page.waitForFunction(fillEmailForm, {}, data);
                await page.waitForTimeout(500);
                await functions.click(page, `[atas="popup__email-picture__form_send-btn"]`);
                await page.waitForNetworkIdle();
                await page.waitForSelector(variables.systMessCardClass, { visible: true });
            }
        }

        if (scenario.openEditPopup) {
            await functions.click(page, `[atas="page__company-pictures__item_dropdown-menu_edit-btn"]`);
            await page.waitForNetworkIdle();

            if (scenario.submitFormSuccess) {
                await functions.click(page, `[atas="popup__seller-pictures-my__form_save-btn"]`);
                await page.waitForNetworkIdle();
                await page.waitForSelector(variables.systMessCardClass, { visible: true });
            }
        }
    }
};

const replaceDataForPictures = data => {
    return (async function () {
        document.querySelectorAll(`[atas="page__company-pictures__item"]`).forEach(item => {
            item.querySelector(`[atas="page__company-pictures__item-image"]`).src = data.pictureImg;
            item.querySelector(`[atas="page__company-pictures__item-title"]`).textContent = data.title;
            item.querySelector(`[atas="page__company-pictures__item-category"]`).textContent = data.category;
            item.querySelector(`[atas="page__company-pictures__item-comments-count"]`).textContent = 99999;
        });

        return true;
    })();
};

const fillShareForm = data => {
    return (async function () {
        document.querySelector(`[atas="popup__share-picture__form_message-textarea"]`).value = data.text;

        return true;
    })();
};

const fillEmailForm = data => {
    return (async function () {
        document.querySelector('[atas="popup__email-picture__form_adresses-input"]').value = data.email;
        document.querySelector('[atas="popup__email-picture__form_message-textarea"]').value = data.text;

        return true;
    })();
};
