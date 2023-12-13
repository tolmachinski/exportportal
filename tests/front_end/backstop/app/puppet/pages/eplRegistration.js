module.exports = async (page, scenario) => {
    const variables = require('../variables/variables');
    const functions = {
        click: require("../functions/click"),
        text: require('../functions/text'),
    };
    const data = {
        firstStep: {
            ["first-name"]: "Pablo Diego Hose Al",
            ["last-name"]: "Pachino del Machino",
            ["email"]: `backstop-${new Date().getTime()}@backstop.test`,
            ["password"]: "f3C7n4T2h9b0R3c1",
            ["password-confirm"]: "f3C7n4T2h9b0R3c1",
            ["phone-input"]: 68794532,
        },
        secondStep: {
            ["company-name"]: "Backstop company name",
            ["company-name-displayed"]: "Backstop company name displayed",
            ["number-of-office"]: 999999,
            ["annual-teu"]: 9999999999,
        },
        lastStep: {
            ["country"]: "",
        },
        accountClass: "shipper",
        step: scenario.step,
    };

    // Filling first form and go to second form
    if (scenario.formFilling || data.step > 1) {
        await page.waitForSelector(`[atas="${data.accountClass}-registration__step-1"] [atas="${data.accountClass}-registration__form-next-btn"]`);
        await page.waitForFunction(fillingFirstStepForm, {}, data);
        await page.waitForTimeout(500);
        await functions.click(page, `[atas="${data.accountClass}-registration__step-1"] [atas="${data.accountClass}-registration__form-next-btn"]`);
        await page.waitForNetworkIdle();
        await page.waitForSelector(`[atas="${data.accountClass}-registration__step-2"] [atas="${data.accountClass}-registration__form-next-btn"]`);
    }

    // Filling second form
    if (data.step > 2) {
        await page.waitForSelector(`[atas="${data.accountClass}-registration__step-2"] [atas="${data.accountClass}-registration__form-next-btn"]`);
        await page.waitForFunction(fillingSecondStepForm, {}, data);
        await page.waitForTimeout(500);
        await functions.click(page, `[atas="${data.accountClass}-registration__step-2"] [atas="${data.accountClass}-registration__form-next-btn"]`);
        await page.waitForNetworkIdle();
        await page.waitForSelector(`[atas="${data.accountClass}-registration__step-3"] [atas="${data.accountClass}-registration__form-register-btn"]`);
    }

    // Filling last form and submit success
    if (data.step === 4) {
        await page.waitForFunction(fillingLastStepForm, {}, data);
        await page.waitForTimeout(250);
        await functions.click(page, `[atas="${data.accountClass}-registration__form-register-btn"]`);
        await page.waitForNetworkIdle();
        await page.waitForSelector(`[atas="${data.accountClass}-registration__form-success-contact-btn"]`);

        // Change text in didnt't receive the confirmation email list
        await page.waitForFunction(functions.text, {}, {
            selectorAll: `[atas="${data.accountClass}-registration__form-success_email"]`,
            text: "backstop-test@backstop.test",
        });

        // Open contact us popup
        if (scenario.openContactUsPopup) {
            await page.waitForTimeout(500);
            await functions.click(page, `[atas="${data.accountClass}-registration__form-success-contact-btn"]`);
            await page.waitForNetworkIdle();
            await page.waitForSelector(`[atas="global__contact-us_submit-btn_popup"]`);

            if (scenario.submitContactUsPopup) {
                await page.waitForTimeout(500);
                await require('../modules/contactUs')(page);
                await page.waitForTimeout(500);
                await functions.click(page, `[atas="global__contact-us_submit-btn_popup"]`);
                await page.waitForNetworkIdle();
                await page.waitForSelector(variables.systMessCardClass, { visible: true });
            }
        }
    }
};

function fillingFirstStepForm(data) {
    return (async function () {
        // Filling data
        document.querySelector(`[atas="${data.accountClass}-registration__form-first-name"]`).dispatchEvent(new Event("focus"));
        // Hide Grecaptcha
        grecaptcha = document.querySelector(`.grecaptcha-badge iframe`);

        if( grecaptcha !== null ) {
            await new Promise(resolve => {
                let interval;
                let grecaptcha = null;
                interval = setInterval(() => {
                    if (grecaptcha) {
                        clearInterval(interval);
                        grecaptcha.onload = () => {
                            document.querySelector(".grecaptcha-badge").style.visibility = "hidden";
                            resolve();
                        };
                    }
                    grecaptcha = document.querySelector(`.grecaptcha-badge iframe`);
                }, 50);
            });
        }

        for (let key in data.firstStep) {
            // If is phone select value in country
            if (key == "phone-input") {
                // Download plugin and init
                const select = document.querySelector(`[atas="${data.accountClass}-registration__form-phone-select"]`);
                select.querySelector(".select2").click();
                // Additional time to load plugin
                await new Promise(resolve => setTimeout(() => resolve(), 500));
                select.querySelector("select").value = "137";
                select.querySelector("select").dispatchEvent(new Event("change"));
                document.querySelectorAll(".select2-container--open").forEach(e => e.classList.remove("select2-container--open"));
                await new Promise(resolve => setTimeout(() => resolve(), 500));
            }
            document.querySelector(`[atas="${data.accountClass}-registration__form-${key}"]`).value = data.firstStep[key];
        }

        // Ignore validation on mask
        if (data.step > 1) {
            document.querySelector(`[atas="${data.accountClass}-registration__form-phone-input"]`).classList.add("ignore");
        }

        return true;
    })();
}

function fillingSecondStepForm(data) {
    return (async function () {
        // Filling data
        for (let key in data.secondStep) {
            let target = document.querySelector(`[atas="${data.accountClass}-registration__form-${key}"]`);
            if (target) {
                target.value = data.secondStep[key];
            }
        }

        return true;
    })();
}

function fillingLastStepForm(data) {
    return (async function () {
        let opt1 = document.createElement("option"),
            opt2 = document.createElement("option"),
            opt3 = document.createElement("option"),
            country = document.querySelector(`[atas="${data.accountClass}-registration__form-select-country"]`),
            state = document.querySelector(`[atas="${data.accountClass}-registration__form-select-state"] select`),
            city = document.querySelector(`[atas="${data.accountClass}-registration__form-select-city"] select`);
        // Country
        opt1.value = 139;
        opt1.textContent = "Moldova";
        country.innerHTML = "";
        country.append(opt1);
        country.dispatchEvent(new Event("change"));
        await new Promise(resolve => setTimeout(() => resolve(), 500));
        // State/Region
        opt2.value = 1864;
        opt2.textContent = "Anenii noi";
        state.innerHTML = "";
        state.append(opt2);
        state.dispatchEvent(new Event("change"));
        await new Promise(resolve => setTimeout(() => resolve(), 500));
        // City
        opt3.value = 1864;
        opt3.textContent = "Anenii noi";
        city.innerHTML = "";
        city.append(opt3);
        await new Promise(resolve => setTimeout(() => resolve(), 500));
        // Address
        document.querySelector(`[atas="${data.accountClass}-registration__form-address"]`).value = "Backstop 36 ap. 23";
        // Zip code
        document.querySelector(`[atas="${data.accountClass}-registration__form-zip-code"]`).value = 90001;
        // Terms & Conditions
        document.querySelector(`[atas="${data.accountClass}-registration__form-checkbox-terms"]`).click();

        return true;
    })();
}
