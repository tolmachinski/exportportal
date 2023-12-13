module.exports = async (page, scenario) => {
    const variables = require("../variables/variables");
    const functions = {
        clickAll: require('../functions/clickAll'),
        picture: require("../functions/picture"),
    }
    await require("../modules/sellerSidebar")(page);


    await page.waitForFunction(functions.picture, {}, {
        selectorAll: `[atas="seller__wall-item_video-img"]`,
        src: variables.img(800, 450),
    });

    if(scenario.detail) {
        await functions.clickAll(page, `[atas="seller__about_detail_btn"]`);
    }

    if (scenario.openMobileSidebarMenu) {
        await page.waitForFunction(openMobileSidebarMenu);
        await page.waitForNetworkIdle();
        await page.waitForTimeout(500);
    }

    // Open all dropdowns with links
    await page.waitForFunction(openAllNavButtons);
    await require('../modules/replaceUserStatus')(page);
}

function openAllNavButtons() {
    return (async () => {
        document.querySelectorAll(`[atas="seller__sidebar_nav_btn_menu"]`).forEach(btn => btn.click());
        document.querySelector(`[atas="seller__sidebar_more_actions_menu"]`).click();

        return true;
    })();
}

function openMobileSidebarMenu() {
    return (async function () {
        if (window.innerWidth < 768) {
            document.querySelector(`[atas="seller__wall_mobile_menu_btn"]`).click();
        }

        return true;
    })();
}
