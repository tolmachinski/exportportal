module.exports = async (page, scenario) => {
    const functions = {
        click: await require("../functions/click"),
    };
    let data = {
        email: `backstop-forgot-123456789qwerty@backstop.test`,
    };

    if (scenario.submitEmptyForm) {
        await functions.click(page, `[atas="page__forgot-password__submit-btn"]`);
    }

    if (scenario.notFoundEmail) {
        await page.waitForFunction(fillForm, {}, data);
        await functions.click(page, `[atas="page__forgot-password__submit-btn"]`);
    }
};

function fillForm(data) {
    return (async function () {
        document.querySelector(`[atas="page__forgot-password__email-input"]`).value = data.email;

        return true;
    })();
}
