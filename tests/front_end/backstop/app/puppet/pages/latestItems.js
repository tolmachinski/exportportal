module.exports = async (page, scenario) => {
    // Filters
    await require("../modules/itemsSidebarFilters")(page, scenario);
    // Products
    await require("../modules/productCard")(page, scenario.hoverItem);
    // Product request modal
    await require("../modules/productRequests")(page, scenario);

    if (scenario.asideFilters) {
        await page.waitForFunction(openAsideFiltersPanel);
    }
};

const openAsideFiltersPanel = () => {
    return (async function () {
        if (window.innerWidth <= 1200) {
            document.querySelector('[atas="global__sidebar-filter-btn"]').click();
        }

        return true;
    })();
};
