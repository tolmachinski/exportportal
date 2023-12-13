module.exports = async (page, scenario) => {
    const variables = require("../variables/variables");
    const functions = {
        click: require('../functions/click')
    }

    const data = {
        name: variables.name.xLong,
        img: variables.img(100, 80),
        companyName: variables.companyName,
        flagImg: variables.country.flag,
        country: variables.country.location,
        price: variables.price.min,
        date: variables.dateFormat.withTime
    };

    await page.waitForNetworkIdle();
    await page.waitForTimeout(1000);

    await page.waitForFunction(changeData, {}, data);

    // Open filters
    if (scenario.openFilter) {
        await functions.click(page, `[atas="page__droplist__open_filter_btn"]`);
    }
    // Open actions buttons
    if (scenario.openActions) {
        await functions.click(page, `[atas="page__droplist__dropdown-btn"]`);
        await page.waitForTimeout(500);
    }
    // Edit droplist item
    if (scenario.editDroplist) {
        await functions.click(page, `[atas="page__droplist__edit-btn"]`);
    }
    // Remove droplist item
    if (scenario.removeFromDroplist) {
        await functions.click(page, `[atas="page__droplist__remove-btn"]`);
    }

    await page.waitForTimeout(500);
}

function changeData(data){
    return (async function(){
        for(let selector of document.querySelectorAll(`[atas="page__droplist__item-img"]`)){
            selector.src = data.img;
        }

        for(let selector of document.querySelectorAll(`[atas="page__droplist__item-title"]`)){
            selector.textContent = data.name;
        }

        for(let selector of document.querySelectorAll(`[atas="page__droplist__item-img"]`)){
            selector.src = data.img;
        }

        for(let selector of document.querySelectorAll(`[atas="page__droplist__item-title"]`)){
            selector.textContent = data.companyName;
        }

        for(let selector of document.querySelectorAll(`[atas="page__droplist__flag-img"]`)){
            selector.src = data.flagImg;
        }

        for(let selector of document.querySelectorAll(`[atas="page__droplist__country-name"]`)){
            selector.textContent = data.country;
        }

        for(let selector of document.querySelectorAll(`[atas="page__droplist__price"]`)){
            selector.textContent = data.price;
        }

        for(let selector of document.querySelectorAll(`[atas="page__droplist__date"]`)){
            selector.textContent = data.date;
        }

        return true
    })()
}
