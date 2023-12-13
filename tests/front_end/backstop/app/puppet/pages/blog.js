module.exports = async (page, scenario) => {
    const variables = require('../variables/variables');

    const dataNewsAndMedia = {
        date: variables.dateFormat.withoutTime,
        title: variables.lorem(90),
        source: variables.link,
    };
    // Blog cards
    await require('../modules/blogCard')(page);

    // Slider Products
    await require('../modules/productCard')(page);

    // News And Media
    await page.waitForFunction(replaceNewsAndMedia, {}, dataNewsAndMedia);

    // Sidebar filters
    await page.waitForFunction(require('../functions/text'), {}, {
        selectorAll: `[atas="global__sidebar-category"], [atas="global__sidebar-archive"]`,
        text: variables.lorem(45),
    });

    await page.waitForFunction(require('../functions/counter'), {}, {
        selectorAll: `[atas="global__sidebar-counter"]`,
        value: 99999,
    });
}

const replaceNewsAndMedia = data => {
    return (() => {
        document.querySelectorAll(`[atas="page__blog__news-and-media_item"]`).forEach(element => {
            element.querySelector(`[atas="page__blog__news-and-media_date"]`).textContent = data.date;
            element.querySelector(`[atas="page__blog__news-and-media_title"]`).textContent = data.title;
            element.querySelector(`[atas="page__blog__news-and-media_source"]`).textContent = data.source;
        });

        return true;
    })();
}
