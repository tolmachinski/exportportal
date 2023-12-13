module.exports = async (page, scenario) => {
    const functions = {
        click: require('../functions/click'),
    };

    const clientWidth = await page.evaluate(() => {
        return document.documentElement.clientWidth;
    });
    // Products
    await require('../modules/productCard')(page);
    // Filters
    await require("../modules/itemsSidebarFilters")(page, scenario);
    // Product request modal
    await require("../modules/productRequests")(page, scenario);
    // Open header search form dropdown
    if(scenario.openDropdown){
        await page.waitForFunction(openHeaderSearchFormDropdown);
        await page.waitForSelector(`[atas="global__header__navbar-search-form_dropdown-menu-items-btn"]`);
    }

    // Check header search form results
    if(scenario.fillSearchForm) {
        const input = `[atas="global__header__navbar-search-form_keywords-input"]`;

        if (scenario.searchType === "items") {
            const submitBtn = `[atas="global__header__navbar-search-form_submit-btn"]`;

            // Fill form
            await page.waitForFunction(initAutocompletePlugin);
            await page.type(input, scenario.itemBackstop ? "[backstop]" : "car");
            await page.waitForNetworkIdle();
            await page.waitForSelector(`[atas="global__header__navbar-search-form_dropdown-menu-items-btn"]`);
                    // Submit form
            await functions.click(page, submitBtn);
            await page.waitForNavigation();

            // Fill out the form again
            await page.waitForFunction(initAutocompletePlugin);
            await page.waitForTimeout(500);
            await page.type(input, "card");
            await page.waitForNetworkIdle();

            // Submit form
            await functions.click(page, submitBtn);
            await page.waitForNavigation();

            // Fill out the form again and show results
            await page.goto(scenario.url);
            await page.waitForFunction(initAutocompletePlugin);
        } else {
            await page.waitForFunction(initAutocompletePlugin);
            await page.waitForNetworkIdle();
            await page.waitForSelector(`[atas="global__header__navbar-search-form_dropdown-menu-items-btn"]`);

            // REMOVE ANIMATION TRANSITION FROM ALL ELEMENTS
            await require('../modules/removeTransition')(page);
            await require('../modules/productCard')(page);
        }

        await page.waitForFunction(initAutocompletePlugin);

        if (scenario.searchType !== "items") {
            if (scenario.clickCategoryItem){
                await functions.click(page, `[atas="global__header__navbar-search-form_dropdown-menu-category-btn"]`);
                await page.waitForSelector('.js-search-autocomplete-container');
            }

            if(scenario.clickB2bItem){
                await functions.click(page, `[atas="global__header__navbar-search-form_dropdown-menu-b2b-btn"]`);
                await page.waitForSelector('.js-search-autocomplete-container');
            }

            if(scenario.clickEventsItem){
                await functions.click(page, `[atas="global__header__navbar-search-form_dropdown-menu-events-btn"]`);
                await page.waitForSelector('.js-search-autocomplete-container');
            }

            if (scenario.clickHeplItem) {
                await functions.click(page, `[atas="global__header__navbar-search-form_dropdown-menu-help-btn"]`);
                await page.waitForSelector('.js-search-autocomplete-container');
            }

            if (scenario.clickBlogslItem) {
                await functions.click(page, `[atas="global__header__navbar-search-form_dropdown-menu-blogs-btn"]`);
                await page.waitForSelector('.js-search-autocomplete-container');
            }
        }

        // REMOVE ANIMATION TRANSITION FROM ALL ELEMENTS
        await require('../modules/removeTransition')(page);

        await require('../modules/productCard')(page);

        await page.type(input, scenario.itemBackstop ? "[backstop]" : "car");
        await page.waitForTimeout(500);
        await page.waitForNetworkIdle();
    }
}

function openHeaderSearchFormDropdown() {
    return (async function () {
        const delay = ms => new Promise(res => setTimeout(res, ms));

        if (window.innerWidth < 992) {
            document.querySelector(`[atas="global__header__navbar-search-form_toggle-btn"]`).click();
            await delay(500);
        }

        document.querySelector(`[atas="global__header__navbar-search-form_dropdown-toggle-btn"]`).click();
        await delay(500);

        return true;
    })();
}


function initAutocompletePlugin() {
    return (async function () {
        const delay = ms => new Promise(res => setTimeout(res, ms));

        if (window.innerWidth < 992) {
            document.querySelector(`[atas="global__header__navbar-search-form_toggle-btn"]`).click();
        }

        document.querySelector(`[atas="global__header__navbar-search-form_keywords-input"]`).click();
        await delay(500);

        return true;
    })();
}
