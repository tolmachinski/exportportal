module.exports = async (page, scenario) => {
    const variables = require('../variables/variables');
    await require('../modules/b2b')(page);
    await require('../modules/changeData')(page, [
        // Top countries Name
        {
            selectorAll: `[atas="page__b2b__search-by-country_country-name"]`,
            value: variables.country.name,
        },
    ]);

    await page.waitForFunction(require('../functions/counter'), {}, {
        selectorAll: `[atas="page__b2b__search-by-country_counter"]`,
        value: 99999,
    });
}
