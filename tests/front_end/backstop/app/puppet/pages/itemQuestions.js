module.exports = async (page, scenario) => {
    const functions = {
        counter: require('../functions/counter'),
        text: require('../functions/text'),
        click: require('../functions/click')
    }

    await require('../modules/itemsChangeMainData')(page);
    // Questions
    await require('../modules/question')(page);
    // Open ask question modal
    if(scenario.askQuestion){
        await functions.click(page, `[atas="items-questions__ask-question__open-dropdown"]`);
        await functions.click(page, `[atas="items-questions__ask-question"]`);
    }
    // Open add report modal
    if(scenario.addReport){
        await functions.click(page, `[atas="items_questions-my__details_replied_dropdown-btn"]`);
        await functions.click(page, `[atas="items_questions-my__details_replied_dropdown_report-btn"]`);
    }
    // Additional wait 1000ms for loading all images
    await page.waitForTimeout(500);
}
