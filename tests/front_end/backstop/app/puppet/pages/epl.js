module.exports = async (page, scenario) => {

    const functions = {
        click: require("../functions/click"),
        clickAll: require('../functions/clickAll'),
    }

    // Open faq
    if(scenario.openFaq){
        await functions.clickAll(page, `[atas^="epl-faq-list__btn-"]`);
    }

    // Open menu mobile
    if(scenario.openMenu){
        await page.waitForFunction(openMenu);
    }

    // Open navigation menu if online
    if (scenario.authentication && scenario.openDropdownUserMenu) {
        await functions.click(page, `[atas="global__header_epl-user-menu-btn"]`);
        await page.waitForSelector(`[atas="global__navigation-logout-btn"]`);
        await page.waitForTimeout(500);
    }
}

function openMenu(){
    return (async function(){
        if (document.body.clientWidth < 901) {
            document.querySelector(`[atas="epl-header-mobile-line__btn-menu"]`).click();
        }
        return true;
    })()
}

