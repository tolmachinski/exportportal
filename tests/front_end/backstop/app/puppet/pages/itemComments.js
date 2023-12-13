module.exports = async (page, scenario) => {
    const functions = {
        counter: require('../functions/counter'),
        text: require('../functions/text'),
        click: require('../functions/click')
    }

    await require('../modules/itemsChangeMainData')(page);

    await functions.click(page, `[atas="item__show-all-comments"]`);
    await require('../modules/comment')(page, 500);
    // Open add comment modal
    if(scenario.addComment){
        await functions.click(page, `[atas="items-comments__leave-comment__open-dropdown"]`);
        await functions.click(page, `[atas="items-comments__leave-comment"]`);
    }
    // Open add reply modal
    if(scenario.addReply){
        await functions.click(page, `[atas="items-comments__reply__open-dropdown"]`);
        await functions.click(page, `[atas="items-comments__reply"]`);
    }
    // Additional wait 1000ms for loading all images
    await page.waitForTimeout(500);
}
