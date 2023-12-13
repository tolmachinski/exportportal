module.exports = async (page, scenario) => {
    const variables = require("../variables/variables");
    const functions = {
        click: require("../functions/click"),
    };

    const data = {
        name: variables.name.xLong,
        category: "Category name",
        title: variables.lorem(150),
        text: variables.lorem(300),
        img: variables.img(100, 80),
        filterPanel: {
            search: variables.lorem(20),
            createdFrom: "01/01/2020",
            createdTo: "01/31/2030",
            repliedFrom: "01/01/2020",
            repliedTo: "01/31/2030",
            status: "new",
            replied: "yes",
        },
    };

    if (scenario.filterPopup) {
        await functions.click(page, `[atas="global__dashboard_filter-btn"]`);
        await page.waitForNetworkIdle();

        if (scenario.fillFilterPopup) {
            await page.waitForFunction(fillFilterForm, {}, data);
            await page.waitForNetworkIdle();

            if (scenario.activeFilter) {
                await functions.click(page, `[atas="global__dashboard_filter-panel_active-filters-btn"]`);
            }
        }
    }

    if (scenario.details) {
        await functions.click(page, `[atas="item-questions-my__table_dropdown-btn"]`);
        await functions.click(page, `[atas="item-questions-my__table_dropdown-details-btn"]`);
        await page.waitForNetworkIdle();
        await require("../modules/question")(page);

        if (scenario.report) {
            await functions.click(page, `[atas="items_questions-my__details_replied_dropdown-btn"]`);
            await functions.click(page, `[atas="items_questions-my__details_replied_dropdown_report-btn"]`);
            await page.waitForNetworkIdle();
            await page.waitForFunction(fillReportForm, {}, data);
            await page.waitForNetworkIdle();
        }
    }

    if (scenario.reply) {
        await functions.click(page, `[atas="item-questions-my__table_dropdown-btn"]`);
        await functions.click(page, `[atas="item-questions-my__table_dropdown-edit-reply-btn"]`);
        await page.waitForNetworkIdle();
        await require("../modules/changeData")(page, [
            {
                selectorAll: `[atas="items-questions-my__edit-reply_answer-textarea_popup"]`,
                value: data.text,
            },
        ]);

        if (scenario.sendReply) {
            await functions.click(page, `[atas="items-questions-my__edit-reply_submit-btn_popup"]`);
            await page.waitForNetworkIdle();
        }
    }

    if (scenario.edit) {
        await functions.click(page, `[atas="item-questions-my__table_dropdown-btn"]`);
        await functions.click(page, `[atas="item-questions-my__table_dropdown-edit-btn"]`);
        await page.waitForNetworkIdle();
        await page.waitForFunction(fillEditForm, {}, data);

        if (scenario.sendEdit) {
            await functions.click(page, `[atas="items-questions-my__edit_save-btn_popup"]`);
            await page.waitForNetworkIdle();
        }
    }

    if (scenario.delete) {
        await functions.click(page, `[atas="item-questions-my__table_dropdown-btn"]`);
        await functions.click(page, `[atas="item-questions-my__table_dropdown-delete-btn"]`);
    }

    await page.waitForNetworkIdle();
    await page.waitForTimeout(1000);
    await page.waitForFunction(changeData, {}, data);
    await require("../modules/dashboardShowingEntries")(page);
    await require("../modules/ratingBootstrap")(page);
};

function changeData(data){
    return (async function(){
        // Item Question title
        for(let selector of document.querySelectorAll(`[atas="item-questions-my__table_item-title"]`)){
            selector.textContent = data.name;
        }
        // Item Question Avatar
        for(let selector of document.querySelectorAll(`[atas="item-questions-my__table_item-image"]`)){
            selector.src = data.img;
            selector.dataset.src = data.img;
        }
        // Item Question Category
        for(let selector of document.querySelectorAll(`[atas="item-questions-my__table_item-category"]`)){
            selector.textContent = data.category;
        }
        // Question Title
        for(let selector of document.querySelectorAll(`[atas="item-questions-my__table-question-title"]`)){
            selector.textContent = data.title;
        }
        // Question Text
        for(let selector of document.querySelectorAll(`[atas="item-questions-my__table-question-text"]`)){
            selector.textContent = data.text;
        }

        return true
    })()
}

const fillFilterForm = data => {
    return (async function () {
        const textNodes = {
            search: document.querySelector(`[atas="items-questions-my__filter-panel_search-input"]`),
            createdFrom: document.querySelector(`[atas="items-questions-my__filter-panel_create-from-input"]`),
            createdTo: document.querySelector(`[atas="items-questions-my__filter-panel_create-to-input"]`),
            repliedFrom: document.querySelector(`[atas="items-questions-my__filter-panel_replied-from-input"]`),
            repliedTo: document.querySelector(`[atas="items-questions-my__filter-panel_replied-to-input"]`),
            status: document.querySelector(`[atas="items-questions-my__filter-panel_status-select"]`),
            replied: document.querySelector(`[atas="items-questions-my__filter-panel_replied-select"]`),
        };

        Object.keys(textNodes).forEach(keyNodes => {
            textNodes[keyNodes].value = data.filterPanel[keyNodes];
            textNodes[keyNodes].dispatchEvent(new Event("change", { bubbles: true }));
        });

        return true;
    })();
};

const fillEditForm = data => {
    return (async function () {
        document.querySelector(`[atas="items-questions-my__edit_category-select_popup"]`).selectedIndex = 1;
        document.querySelector(`[atas="items-questions-my__edit_title-input_popup"]`).value = data.title;
        document.querySelector(`[atas="items-questions-my__edit_text-textarea_popup"]`).value = data.text;
        return true;
    })();
};

const fillReportForm = data => {
    return (async function () {
        document.querySelector(`[atas="popup__complains__report-form_reason-select"]`).selectedIndex = 6;
        document.querySelector(`[atas="popup__complains__report-form_message-textarea"]`).value = data.text;
        return true;
    })();
};
