module.exports = async (page, scenario) => {
    const functions = {
        click: require("../functions/click"),
    };
    const variables = require("../variables/variables");

    // Toggle category button to open list with subcategories
    await page.waitForFunction(toggleMaxListMore);
    // Toggle category button to open list with subcategories
    if (!scenario.nothingFound) {
        await functions.click(page, `[atas="global__sidebar-toggle-category"]`);
    }
    // Change all names in sidebar elements
    await page.waitForFunction(
        require("../functions/text"),
        {},
        {
            selectorAll: `[atas="global__sidebar-category"], [atas="global__sidebar-country"], [atas="global__sidebar-toggled-category"]`,
            text: variables.name.xLong,
        }
    );
    // Fix counters
    await page.waitForFunction(
        require("../functions/counter"),
        {},
        {
            selectorAll: `[atas="global__search-counter"], [atas="global__sidebar-counter"], [atas="global__sidebar-toggled-counter"]`,
            value: 99999,
        }
    );
};

function toggleMaxListMore() {
    return (async () => {
        document.querySelectorAll(`.js-maxlist-more button`).forEach(button => {
            button.click();
        });


        document.querySelectorAll(`.js-hide-max-list`).forEach(list => {
            const listTotal = list.querySelectorAll(`.js-maxlist-hidden`);

            if (listTotal.length > 5) {
                listTotal.forEach((listItem, listItemIndex) => {
                    if (listItemIndex > 4) {
                        listItem.style.display = 'none';
                    }
                });
            }
        });

        return true;
    })();
}
