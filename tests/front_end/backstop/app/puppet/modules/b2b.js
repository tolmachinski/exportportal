module.exports = async page => {
    const variables = require("../variables/variables");
    const adressData = {
        name: variables.country.name,
        img: variables.country.flag,
    };
    // Clone tags
    await page.waitForFunction(cloneTags);
    // Change address
    await page.waitForFunction(changeAdress, {}, adressData);
    // Change all data
    require("../modules/changeData")(page, [
        // Found counter
        {
            selectorAll: `[atas="global__search-counter"]`,
            value: 99999,
        },
        // Counters
        {
            selectorAll: `[atas="page__b2b__counter"]`,
            value: 99999,
        },
        // Image
        {
            selectorAll: `[atas="page__b2b__request-image"]`,
            value: variables.img(300, 226),
            attr: "src",
            type: "img",
        },
        // Title
        {
            selectorAll: `[atas="page__b2b__request-title"]`,
            value: variables.lorem(250),
        },
        // Company name
        {
            selectorAll: `[atas="page__b2b__company-name"]`,
            value: variables.name.long,
        },
        // // Location flag image
        {
            selectorAll: `[atas="page__b2b__country-image"]`,
            value: variables.country.flag,
            attr: "src",
            type: "img",
        },
        // // Country
        {
            selectorAll: `[atas="page__b2b__country-name"]`,
            value: variables.country.name,
        },
        // Company from location
        {
            selectorAll: `[atas="page__b2b__company-city"]`,
            value: "Backstopaster, backstoperbackstoper, str. Backstop John Cena 16, 9900010299000102",
        },
        // Search distance
        {
            selectorAll: `[atas="page__b2b__request-radius"]`,
            value: "99999 km",
        },
        // Tags
        {
            selectorAll: `[atas="page__b2b__request-tag"]`,
            value: variables.tag[30],
        },
    ]);
};

function cloneTags() {
    return (async function () {
        // Tags
        let tagSelector = `[atas="page__b2b__request-tag"]`;
        for (const selector of document.querySelectorAll(`[atas="page__b2b__request-tags"]`)) {
            let l = selector.querySelectorAll(tagSelector).length;
            while (l != 10) {
                if (l > 10) {
                    selector.querySelectorAll(tagSelector)[l - 1].remove();
                    l--;
                } else {
                    selector.append(selector.querySelector(tagSelector).cloneNode(true));
                    l++;
                }
            }
        }
        return true;
    })();
}

function changeAdress(data) {
    return (async function () {
        [...document.querySelectorAll(`[atas="page__b2b__search-in"]`)].map((node, i) => {
            if (i % 2 === 0) {
                node.innerHTML = `
                    <img class="b2b-card__country-img" src="${data.img}"/>
                    <span class="b2b-card__country-name">${data.name}</span>
                    <span class="b2b-card__country-more">+<span>999</span> <span class="b2b-card__country-more-text">more</span></span>
                `;
            } else {
                node.innerHTML = `<span>Globally</span>`;
            }
        });

        return true;
    })();
}
