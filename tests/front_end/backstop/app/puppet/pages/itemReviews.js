module.exports = async (page, scenario) => {
    const variables = require("../variables/variables");
    const functions = {
        counter: require('../functions/counter'),
        text: require('../functions/text'),
        click: require('../functions/click')
    }

    const data = {
        title: variables.lorem(50),
        text: variables.lorem(500),
        reason: 2,
    };

    await require('../modules/itemsChangeMainData')(page);
    // Reviews
    await require("../modules/ratingBootstrap")(page);
    await require('../modules/reviews')(page);
    // Open edit modal
    if(scenario.edit){
        await functions.click(page, `[atas="global__reviews__dropdown-btn"]`);
        await functions.click(page, `[atas="global__reviews__dropdown-menu_edit-btn"]`);
        await page.waitForNetworkIdle();
        await page.waitForFunction(fillReviewForm, {}, data);
    }
    // Additional wait 1000ms for loading all images
    await page.waitForTimeout(500);
}

const fillReviewForm = data => {
    return (async function () {
        document.querySelector('[atas="popup__reviews__form_title-input"]').value = data.title;
        document.querySelector('[atas="popup__reviews__form_description-textarea"]').value = data.text;

        return true;
    })();
};
