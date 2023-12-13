module.exports = async (page, scenario) => {
    const {
        showHeaderDropdown,
        showDropdown,
        openAddDocumentPopup,
        submitForm,
        fillForm,
        openEditDocumentPopup,
        openShareDocumentPopup,
        openEmailDocumentPopup
    } = scenario;
    const variables = require("../variables/variables");
    const functions = {
        click: require("../functions/click"),
    };

    const data = {
        title: variables.lorem(50),
        category: variables.lorem(50),
        date: variables.dateFormat.withoutTime,
        description: variables.lorem(250),
    };

    const dataForm = {
        description: variables.lorem(1000),
        email: variables.mail,
    }

    await require("../modules/sellerSidebar")(page);
    await page.waitForFunction(replaceDataForLibrary, {}, data);

    if (showHeaderDropdown) {
        await functions.click(page, `[atas="page__company-library__heading_dropdown-menu_btn"]`);

        if (openAddDocumentPopup) {
            await functions.click(page, `[atas="page__company-library__heading_dropdown-menu_add-document-btn"]`);
            await page.waitForNetworkIdle();

            if (submitForm) {
                await functions.click(page, `[atas="popup__seller-library-add-document__form_save-btn"]`);
            }
        }
    }

    if (showDropdown) {
        await functions.click(page, `[atas="page__company-library__item_dropdown-menu_btn"]`);


        if (openEditDocumentPopup) {
            await functions.click(page, `[atas="page__company-library__item_dropdown-menu_edit-btn"]`);
            await page.waitForNetworkIdle();

            if (submitForm) {
                await functions.click(page, `[atas="popup__seller-library-add-document__form_save-btn"]`);
            }
        }

        if (openShareDocumentPopup) {
            await functions.click(page, `[atas="page__company-library__item_dropdown-menu_share-btn"]`);
            await page.waitForNetworkIdle();

            if (fillForm) {
                await page.waitForFunction(fillShareForm, {}, dataForm);
            }

            if (submitForm) {
                await functions.click(page, `[atas="popup__seller-library-share__form_submit-btn"]`);
            }
        }

        if (openEmailDocumentPopup) {
            await functions.click(page, `[atas="page__company-library__item_dropdown-menu_email-btn"]`);
            await page.waitForNetworkIdle();

            if (fillForm) {
                await page.waitForFunction(fillEmailForm, {}, dataForm);
            }

            if (submitForm) {
                await functions.click(page, `[atas="popup__seller-library-email__form_submit-btn"]`);
            }
        }
    }
};

const replaceDataForLibrary = data => {
    return (() => {
        document.querySelectorAll(`[atas="page__company-library__item"]`).forEach(item => {
            item.querySelector(`[atas="page__company-library__item_title"]`).textContent = data.title;
            item.querySelector(`[atas="page__company-library__item_category"]`).textContent = data.category;
            item.querySelector(`[atas="page__company-library__item_date"]`).textContent = data.date;
            item.querySelector(`[atas="page__company-library__item_description"]`).textContent = data.description;
        });

        return true;
    })();
};

const fillShareForm = data => {
    return (() => {
        const textarea = document.querySelector('[atas="popup__seller-library-share__form_message-textarea"]');
        textarea.value = data.description;
        textarea.dispatchEvent(new Event("change", { bubbles: true }));

        return true;
    })();
};

const fillEmailForm = data => {
    return (() => {
        const textNodes = {
            email: document.querySelector('[atas="popup__seller-library-email__form_email-input"]'),
            description: document.querySelector('[atas="popup__seller-library-email__form_message-textarea"]'),
        };

        Object.keys(textNodes).forEach(keyNodes => {
            textNodes[keyNodes].value = data[keyNodes];
            textNodes[keyNodes].dispatchEvent(new Event("change", { bubbles: true }));
        });
        return true;
    })();
};
