module.exports = async (page, scenario) => {
    const variables = require("../variables/variables");
    const functions = {
        click: require('../functions/click'),
        counter: require('../functions/counter'),
    };

    const data = {
        name: variables.name.xLong,
        title: variables.lorem(75),
        image: variables.img(100, 100),
        text: variables.lorem(500),
        order: "#99999999999",
    };
    await page.waitForNetworkIdle();
    await require("../modules/dashboardShowingEntries")(page);
    await require("../modules/ratingBootstrap")(page);
    await require("../modules/changeData")(page, [
        // Item Image
        {
            selectorAll: `[atas="page__my-reviews__table_item-image"]`,
            value: data.image,
            attr: "src",
            type: "image",
        },
        // Item Title
        {
            selectorAll: `[atas="page__my-reviews__table_item-title"]`,
            value: data.title,
        },
        // Item Seller Name
        {
            selectorAll: `[atas="page__my-reviews__table_item-seller-name"]`,
            value: data.name,
        },
        // Item Order Number
        {
            selectorAll: `[atas="page__my-reviews__table_item-order-number"]`,
            value: data.order,
        },
        // Item Review text
        {
            selectorAll: `[atas="page__my-reviews__table_item-description"]`,
            value: data.text,
        },
    ]);

    await page.waitForFunction(functions.counter, {}, { selectorAll: `[atas="page__my-reviews__table_item-counter"]`, value: 99999 });

    if(scenario.addReview) {
        await functions.click(page, `[atas="page__my-reviews__dashboard_add-review-btn"]`);
        await page.waitForNetworkIdle();
    }

    if(scenario.editReview) {
        await functions.click(page, `[atas="page__my-reviews__table_dropdown-menu_edit-btn"]`);
        await page.waitForNetworkIdle();
    }

    if(scenario.deleteReview) {
        await functions.click(page, `[atas="page__my-reviews__table_dropdown-menu_delete-btn"]`);
        await page.waitForNetworkIdle();
    }

    if(scenario.detailsReview) {
        await functions.click(page, `[atas="page__my-reviews__table_dropdown-menu_details-btn"]`);
        await page.waitForNetworkIdle();
        await require("../modules/ratingBootstrap")(page);
        await require('../modules/reviews')(page);
    }
};

