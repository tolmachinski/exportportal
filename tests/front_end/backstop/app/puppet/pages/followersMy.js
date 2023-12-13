module.exports = async (page, scenario) => {
    const functions = {
        click: require("../functions/click"),
        counter: require("../functions/counter"),
    };

    if (scenario.following) {
        await page.waitForFunction(clickOnFollowingTab);
        await page.waitForNetworkIdle();
    }

    await page.waitForFunction(functions.counter, {}, { selectorAll: `[atas="global__sidebar-counter"]`, value: 99999 });
    await page.waitForFunction(functions.counter, {}, { selectorAll: `[atas="followers-my__item_total-counter"]`, value: 99999 });

    await require("../modules/followers")(page, scenario);
};

const clickOnFollowingTab = () => {
    return (async function () {
        document.querySelectorAll('[atas="followers-my__sidebar-followers-link"]')[1].click();
        return true;
    })();
};
