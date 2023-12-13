module.exports = async (page, scenario) => {
    const ENV = require('../variables/env');
    const variables = await require('../variables/variables');

    await require("../functions/cookie")(
        page,
        "ep_r",
        `${variables.getAuthData(scenario.authentication)["cookie"]};`
    );
    await page.waitForTimeout(50);
    await page.goto(scenario.url, { waitUntil: "domcontentloaded" });
};
