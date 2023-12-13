module.exports = async (page, scenario) => {
    const variables = require('../variables/variables');
    const data = {
        products: {
            img: variables.img(375, 300),
        },
    };

    //Seller Sidebar
    await require("../modules/sellerSidebar")(page);

    // Products cards
    await require('../modules/productCard')(page);

    // Sidebar filters
    await page.waitForFunction(require('../functions/text'), {}, {
        selectorAll: `[atas="global__sidebar-category"]`,
        text: variables.lorem(45),
    });

    await page.waitForFunction(require('../functions/counter'), {}, {
        selectorAll: `[atas="global__sidebar-counter"]`,
        value: 99999,
    });

    // Open all dropdowns with links
    await page.waitForFunction(openAllNavButtons);
    await require('../modules/replaceUserStatus')(page);

    if (scenario?.hoverItem) {
        await page.waitForNetworkIdle();
        await page.waitForSelector('[atas="global__item"]');
        await page.waitForFunction(() => {
            return (() => {
                if (window.matchMedia("(min-width: 991px)").matches) {
                    document.querySelector('[atas="global__item"]').classList.add("hover");
                }
                return true;
            })();
        });
    }
}

function openAllNavButtons() {
    return (async () => {
        document.querySelectorAll(`[atas="seller__sidebar_nav_btn_menu"]`).forEach(btn => btn.click());
        document.querySelectorAll(`[atas="seller__sidebar_nav_items-counter"]`).forEach(e => e.textContent = 999);

        return true;
    })();
}
