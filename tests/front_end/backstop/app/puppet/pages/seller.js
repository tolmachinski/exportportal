module.exports = async (page, scenario) => {
    const variables = require("../variables/variables");
    const functions = {
        click: require('../functions/click'),
        clickAll: require('../functions/clickAll'),
        text: require("../functions/text"),
        picture: require("../functions/picture"),
    };

    const data = {
        country: variables.country.name,
        flag: variables.country.flag,
        wallData: {
            banner: variables.img(524, 350),
            updates: variables.img(150, 100),
            videos: variables.img(524, 350),
            photos: variables.img(524, 350),
            news: variables.img(524, 350),
            item: variables.img(375, 300),
            itemThumb: variables.img(156, 125),
        }
    };

    await page.waitForFunction(changeAdress, {}, data);
    await require("../modules/sellerSidebar")(page);
    
    await page.waitForFunction(functions.text, {}, {
        selectorAll: `[atas="seller__statistics_number"]`,
        text: 99999999,
    });
    await page.waitForFunction(functions.picture, {}, {
        selectorAll: `[atas="seller__wall-item_video-img"]`,
        src: variables.img(520, 290),
    });
    await require("../modules/additionalInfo")(page);

    if (scenario.backstopVariant === 2) {
        await page.waitForFunction(changeWallData, {}, data.wallData);
    }

    if (scenario.openMobileSidebarMenu) {
        await page.waitForFunction(openMobileSidebarMenu);
        await page.waitForNetworkIdle();
        await page.waitForSelector(`[atas="seller__sidebar_more_actions_menu"]`, { visible: true });
        await page.waitForTimeout(1000);
    }

    // Open all dropdowns with links
    await page.waitForFunction(openAllNavButtons);

    // Если есть необходимость открыть какую-то модалку из списка экшенов
    if (scenario.moreActionItem) {
        await functions.click(page, `[atas="seller__sidebar_more-actions-dropdown_${scenario.moreActionItem}"]`);
        await page.waitForNetworkIdle();
        if (scenario.moreActionItem !== "share") {
            await page.waitForTimeout(500);
            await page.waitForSelector(".textcounter-wrapper .textcounter", { visible: true });
            await page.waitForTimeout(500);
        }
    }

    await require('../modules/replaceUserStatus')(page);
    await page.waitForFunction(replaceAdditionalElements);
};

function openMobileSidebarMenu() {
    return (async function () {
        if (window.innerWidth < 768) {
            document.querySelector(`[atas="seller__wall_mobile_menu_btn"]`).click();
        }

        return true;
    })();
}

function openAllNavButtons() {
    return (async () => {
        document.querySelectorAll(`[atas="seller__sidebar_nav_btn_menu"]`).forEach(btn => btn.click());
        document.querySelectorAll(`[atas="seller__sidebar_nav_items-counter"]`).forEach(e => e.textContent = 999);
        document.querySelector(`[atas="seller__sidebar_more_actions_menu"]`).click();

        return true;
    })();
}

function replaceAdditionalElements() {
    return (async () => {
        // Months of experience of selling
        document.querySelectorAll(`[atas="seller__sidebar-experience"]`).forEach(e => {
            e.textContent = `10 months of experience`;
        });

        return true;
    })();
}

function changeWallData (data) {
    return (async () => {
        const images = {
            banner: document.querySelector(`[atas="seller__wall-banner-img"]`),
            updates: document.querySelector(`[atas="seller__wall-update-img"]`),
            videos: document.querySelector(`[atas="seller__wall-video-img"]`),
            photos: document.querySelector(`[atas="seller__wall-photo-img"]`),
            news: document.querySelector(`[atas="seller__wall-news-img"]`),
            item: document.querySelector(`[atas="seller__wall-item-img"]`),
            itemThumb: document.querySelector(`[atas="seller__wall-item-thumb-img"]`),
        };

        Object.keys(images).forEach(key => {
            images[key].dataset.src = data[key];
            images[key].src = data[key];
        });

        return true;
    })();
}

function changeAdress(data) {
    return (async function () {
        [...document.querySelectorAll(`[atas="page__seller-company__search-in"]`)].map((node, i) => {
            if (i % 2 === 0) {
                node.innerHTML = `
                    <span class="spersonal-history-b2b__search-name">Search in:</span>
                        <span class="spersonal-history-b2b__country">
                            <img class="image" width="24" height="24" src="${data.flag}"/>
                            <span class="spersonal-history-b2b__country-name">${data.country}</span>
                            <span class="spersonal-history-b2b__country-more">+<span>999</span> <span class="spersonal-history-b2b__country-more-text">more</span></span>
                        </span>
                    </span>
                `;
            } else {
                node.innerHTML = `
                    <span class="spersonal-history-b2b__search-name">Search in:</span>
                        <span>Globally</span>
                    </span>
                `;
            }
        });

        return true;
    })();
}
