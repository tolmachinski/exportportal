module.exports = async (page, scenario) => {
    const functions = {
        click: require("../functions/click"),
    };

    if (!scenario.openLoginPopup && scenario.validationError) {
        //Login page validation error
        await functions.click(page, `[atas="epl-login__form_submit-btn"]`);
    }

    if (scenario.authorize) {
        await page.waitForFunction(login, {}, scenario);
    }

    if (scenario.openDropdownUserMenu) {
        await page.waitForTimeout(500);
        await functions.click(page, `[atas="global__header_epl-user-menu-btn"]`);
    }

    // Open popup login
    if (scenario.openLoginPopup) {
        await functions.click(page, `[atas="global__header_epl-login-btn"]`);
        await page.waitForSelector(`[atas="epl-login__form_submit-btn_popup"]`);

        // Validation error
        if (scenario.validationError) {
            await page.waitForTimeout(500);
            await functions.click(page, `[atas="epl-login__form_submit-btn_popup"]`);
        }
    }
};

function login(data) {
    return (async function () {
        let delay = ms => new Promise(res => setTimeout(res, ms));
        let condition = {
            while: true,
            authorization: false,
        };
        while (condition) {
            let delogBlock = document.querySelector(`[atas="epl-login__form-clean-session-block"]`);
            // Сlear session
            if (delogBlock && delogBlock.style.display === "block" && condition.authorization) {
                document.querySelector(`[atas="epl-login__form-clear-session-btn"]`).click();
                await delay(1500);
            } else if (document.querySelector('[atas="epl-login__form"]')) {
                document.querySelector(`[atas="epl-login__form_email-input"]`).value = data.login;
                document.querySelector(`[atas="epl-login__form_password-input"]`).value = data.password;
                document.querySelector(`[atas="epl-login__form_submit-btn"]`).click();
                condition.authorization = true;

                //If this is not a shipper, then an info popup appears that you need to log in to ep.
                if (data.notShipper) {
                    return true;
                }
            } else {
                // Page refreshed
                condition.while = false;
                return true;
            }
            // Delay to reduce cycle speed and number of requests
            await delay(1500);
        }

        await delay(5000);
    })();
}
