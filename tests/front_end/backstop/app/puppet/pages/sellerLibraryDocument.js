module.exports = async (page, scenario) => {
    const {
        submitForm,
        fillForm,
        openEditDocumentPopup,
        openShareDocumentPopup,
        openEmailDocumentPopup,
        showItemDropdown,
        openItemShareDocumentPopup,
        openItemEmailDocumentPopup
    } = scenario;
    const variables = require("../variables/variables");
    const functions = {
        click: require("../functions/click"),
    };

    const data = {
        title: variables.lorem(50),
        category: variables.lorem(50),
        date: variables.dateFormat.withTime,
        description: variables.lorem(250),
        img: variables.imgLocal['800x780'],
        editForm: {
            title: variables.lorem(50),
            description: variables.lorem(250),
        }
    };

    const dataForm = {
        description: variables.lorem(1000),
        email: variables.mail,
    }

    await require("../modules/sellerSidebar")(page);
    await page.waitForFunction(replaceDataForLibraryDocument, {}, data);
    await page.waitForFunction(replaceDataForLibrary, {}, data);

    if (openEmailDocumentPopup) {
        await functions.click(page, `[atas="page__company-library__document-detail_email-btn"]`);
        await page.waitForNetworkIdle();

        if (fillForm) {
            await page.waitForFunction(fillEmailForm, {}, dataForm);
        }

        if (submitForm) {
            await functions.click(page, `[atas="popup__seller-library-email__form_submit-btn"]`);
        }
    }

    if (openShareDocumentPopup) {
        await functions.click(page, `[atas="page__company-library__document-detail_share-btn"]`);
        await page.waitForNetworkIdle();

        if (fillForm) {
            await page.waitForFunction(fillShareForm, {}, dataForm);
        }

        if (submitForm) {
            await functions.click(page, `[atas="popup__seller-library-share__form_submit-btn"]`);
        }
    }

    if (showItemDropdown) {
        await functions.click(page, `[atas="page__company-library__item_dropdown-menu_btn"]`);

        if (openEditDocumentPopup) {
            await functions.click(page, `[atas="page__company-library__item_dropdown-menu_edit-btn"]`);
            await page.waitForNetworkIdle();

            if (submitForm) {
                await functions.click(page, `[atas="popup__seller-library-add-document__form_save-btn"]`);
            } else {
                await page.waitForFunction(fillEditForm, {}, data.editForm);
            }
        }

        if (openItemShareDocumentPopup) {
            await functions.click(page, `[atas="page__company-library__item_dropdown-menu_share-btn"]`);
            await page.waitForNetworkIdle();

            if (fillForm) {
                await page.waitForFunction(fillShareForm, {}, dataForm);
            }

            if (submitForm) {
                await functions.click(page, `[atas="popup__seller-library-share__form_submit-btn"]`);
            }
        }

        if (openItemEmailDocumentPopup) {
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

const replaceDataForLibraryDocument = data => {
    return (() => {
        const iframe = document.querySelector(`[atas="page__company-library__document-detail_iframe"]`);

        if(iframe) {
            iframe.src = data.img;
        }

        document.querySelector(`[atas="page__company-library__document-detail_title"]`).textContent = data.title;
        document.querySelector(`[atas="page__company-library__document-detail_category"]`).textContent = data.category;
        document.querySelector(`[atas="page__company-library__document-detail_date"]`).textContent = data.date;
        document.querySelector(`[atas="page__company-library__document-detail_description"]`).textContent = data.description;
        return true;
    })();
};

const replaceDataForLibrary = data => {
    return (() => {
        document.querySelector('[atas="page__company-library__document-detail_more-documents_title-counter"]').textContent = '(9999)';
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

const fillEditForm = data => {
    return (() => {
        const title = document.querySelector('[atas="popup__seller-library-add-document__form_title-input"]');
        const description = document.querySelector('[atas="popup__seller-library-add-document__form_description-textarea"]');
        title.value = data.title;
        description.value = data.description;
        title.dispatchEvent(new Event("change", { bubbles: true }));
        description.dispatchEvent(new Event("change", { bubbles: true }));

        return true;
    })();
};
