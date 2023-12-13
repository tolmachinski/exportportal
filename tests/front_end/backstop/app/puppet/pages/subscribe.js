module.exports = async (page, scenario) => {
    const functions = {
        click: await require("../functions/click"),
    };
    let data = {
        email: scenario.email || `backstop-${new Date().getTime()}${Math.floor(Math.random() * 100000)}@backstop.test`,
    }

    if (scenario.submitEmptyForm) {
        await functions.click(page, `[atas="page__subscribe__submit-btn"]`);
    }

    if (scenario.submitSuccess) {
        await page.waitForFunction(fillForm, {}, data);
        await page.waitForTimeout(500);
        await functions.click(page, `[atas="page__subscribe__t&c-label"]`);
        await functions.click(page, `[atas="page__subscribe__submit-btn"]`);
        await page.waitForNetworkIdle();
        await page.waitForSelector(".bootstrap-dialog");
    }
};

function fillForm(data){
    return (async function(){
        document.querySelector(`[atas="page__subscribe__email-input"]`).value = data.email;

        return true
    })()
}
