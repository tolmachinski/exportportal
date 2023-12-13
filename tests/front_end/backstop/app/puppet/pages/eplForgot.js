//For authorization, it is better to execute 1 script in order to be able to see the Clean Session block and log in with the deletion of the session.
//If you try to run more than two asynchronous login tests, you might get errors.

module.exports = async (page, scenario) => {
    const variables = require("../variables/variables");
    const data = {
        email: scenario.email,
    }

    // Submit forgot form
    if (scenario.submitForm) {
        await page.waitForFunction(forgot, {}, data);
        await page.waitForNetworkIdle();
        await page.waitForSelector(variables.systMessCardClass, { visible: true });
    }
}

function forgot(data) {
    return (async function () {
        document.querySelector(`[atas="epl-forgot__form_email-input"]`).value = data.email;
        document.querySelector(`[atas="epl-forgot__form_submit-btn"]`).click();

        return true;
    })()
}
