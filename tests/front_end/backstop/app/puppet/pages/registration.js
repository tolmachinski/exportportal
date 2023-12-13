module.exports = async (page, scenario) => {
    const variables = require("../variables/variables");
    const functions = {
        click: require('../functions/click'),
        text: require('../functions/text'),
        recursiveCallOnCatch: require("../functions/recursive-try-catcher"),
    }
    let data = {
        firstStep: {
            ["first-name"]: "Pablo Diego Hose Al",
            ["last-name"]: "Pachino del Machino",
            ["email"]: `backstop-${new Date().getTime()}${Math.floor(Math.random() * 100000)}@backstop.test`,
            ["password"]: "f3C7n4T2h9b0R3c1",
            ["password-confirm"]: "f3C7n4T2h9b0R3c1",
            ["phone-input"]: 68794532
        },
        secondStep:{
            ["company-name"]: "Backstop company name",
            ["company-name-displayed"]: "Backstop company name displayed",
            ["number-of-office"]: 999999,
            ["annual-teu"]: 9999999999
        },
        lastStep:{
            ["country"]: ""
        }
    }
    data.step = scenario.step;
    data.accountClass = scenario.accountClass;

    // Replace banner sidebar tablet/mobile
    await page.waitForFunction(require('../functions/picture'), {}, {
        selectorAll: `[atas="global__banner-picture"]`,
        src: variables.img(965, 250),
        media: {
            mobile: {
                attr: "(max-width:767px)",
                src: variables.img(300, 600),
            }
        }
    });

    await page.waitForSelector(`.slick-initialized[atas="register__reviews-slider"]`, { timeout: 240000 });

    // Filling first form
    if (scenario.formFilling || data.step > 1) {
        await functions.click(page, `[atas="${data.accountClass}-registration__form-phone-select"] .select2`, { visible: true });
        await page.waitForNetworkIdle();
        await page.waitForFunction(firstStep, {}, data);
    }
    // Go to next form
    if (data.step > 1) {
        await functions.click(page, `[atas="${data.accountClass}-registration__step-1"] [atas="${data.accountClass}-registration__form-next-btn"]`);
        await page.waitForNetworkIdle();
        await page.waitForSelector(`[atas="${data.accountClass}-registration__step-2"]`, { timeout: 240000, visible: true })
        // Select bussiness account to see inputs
        if(data.accountClass == "buyer"){
            await functions.click(page, `[atas="${data.accountClass}-registration__form-radio-business"]`);
        }
    }

    // Filling second form
    if (data.step > 2) {
        data.accountType = scenario.accountType;
        if(data.accountClass == "seller" || data.accountClass == "manufacturer"){
            await page.waitForSelector(`[atas="${data.accountClass}-registration__form-multiselect-industries"]`, { timeout: 240000, visible: true });
        }
        await page.waitForFunction(secondStep, {}, data);
    }
    // Goto 2.5 - 3 steps
    let btnStep2_3;
    if (data.step > 2) {
        btnStep2_3 = (data.step === 2.5) ? `[atas="${data.accountClass}-registration__form-add-account"]` : `[atas="${data.accountClass}-registration__form-next-btn"]`;
        await functions.click(page, `[atas="${data.accountClass}-registration__step-2"] ${btnStep2_3}`);
        await page.waitForNetworkIdle();
    }
    // If 2.5 need to show all inputs
    if (data.step === 2.5) {
        await functions.recursiveCallOnCatch(
            async () => {
                await page.waitForSelector(`[atas="${data.accountClass}-registration__step-2-5"]`, { timeout: 5000, visible: true });
            },
            async () => {
                await functions.click(page, `[atas="${data.accountClass}-registration__step-2"] ${btnStep2_3}`);
            },
            120000,
        );
        await  functions.click(page, `[atas="${data.accountClass}-registration__step-2-5"] [atas="${data.accountClass}-registration__form-add-all-accounts"]`);
        await page.waitForNetworkIdle();

        if (data.accountClass !== 'buyer') {
            await functions.click(page, `[atas="${data.accountClass}-registration__form-additional-buyer-radio-business"]`);
            await page.waitForNetworkIdle();
        }
    }
    if (data.step >= 3) {
        await functions.recursiveCallOnCatch(
            async () => {
                await page.waitForSelector(`[atas="${data.accountClass}-registration__step-3"]`, { timeout: 5000, visible: true });
            },
            async () => {
                await functions.click(page, `[atas="${data.accountClass}-registration__step-2"] ${btnStep2_3}`);
            },
            120000,
        );
    }
    // Success submit form
    if (data.step === 4) {
        await page.waitForSelector(`[atas="${data.accountClass}-registration__form-select-country"]`, { timeout: 240000, visible: true });
        await page.waitForFunction(lastStep1, {}, data);
        await page.waitForFunction(lastStep2, {}, data);
        await page.waitForFunction(lastStep3, {}, data);
        // Registration submit
        await functions.click(page, `[atas="${data.accountClass}-registration__form-register-btn"]`, false, { timeout: 240000, visible: true });
        await page.waitForNetworkIdle();
        // Wait loader
        await page.waitForSelector('[atas="registration__done-email"]');
        await page.waitForFunction(functions.text, {}, {
            selectorAll: `[atas="registration__done-email"]`,
            text: `backstop-test@backstop.test`,
        });
    }

    await page.waitForFunction(hideSelect2);
}

// 1st step with filling form
function firstStep(data){
    return (async function(){
        // Filling data
        document.querySelector(`[atas="${data.accountClass}-registration__form-first-name"]`).dispatchEvent(new Event("focus"));
        let grecaptcha = document.querySelector(`.grecaptcha-badge iframe`);

        if(grecaptcha !== null) {
            await new Promise(resolve => {
                grecaptcha.onload = () => {
                    document.querySelector(".grecaptcha-badge").style.visibility = "hidden";
                    resolve();
                };
            });
        }
        for(let key in data.firstStep){
            // If is phone select value in country
            if (key == 'phone-input') {
                const select = document.querySelector(`[atas="${data.accountClass}-registration__form-phone-select"]`);
                select.querySelector("select").value = "137";
                select.querySelector("select").dispatchEvent(new Event("change"));
                await new Promise(resolve => setTimeout(() => resolve(), 500));
            }
            document.querySelector(`[atas="${data.accountClass}-registration__form-${key}"]`).value = data.firstStep[key];
        }
        // Ignore validation on mask
        if (data.step > 1) {
            document.querySelector(`[atas="${data.accountClass}-registration__form-phone-input"]`).removeAttribute('class');
        }

        return true
    })()
}

// 2nd step with filling form
function secondStep(data){
    return (async function(){
        // Buyer sellection type of account
        if(data.accountClass === "buyer"){
            // Check radio button personal/bussiness
            document.querySelector(`[atas="buyer-registration__form-radio-${data.step == 2 ? data.accountType : 'business'}"]`).click();
        }
        // Select 3 options
        let select = document.querySelector(`[atas="${data.accountClass}-registration__form-select-industries"]`),
            multiselect = document.querySelector(`[atas="${data.accountClass}-registration__form-multiselect-industries"]`);
        if(select){
            for(let i = 1; i<4; i++){
                select.options[i].selected = true;
                select.dispatchEvent(new Event("change"));
            }
        }
        if(multiselect){
            multiselect.querySelector("li.multiple-epselect__parent label").click();
            await new Promise(resolve => setTimeout(() => resolve(), 2000));
            let opc = multiselect.querySelectorAll("li:not(.multiple-epselect__parent) label");
            for(let i = 0; i < 3; i++){
                opc[i]?.click();
            }
        }
        // Filling data
        for(let key in data.secondStep){
            let target = document.querySelector(`[atas="${data.accountClass}-registration__form-${key}"]`);
            if(target){
                target.value = data.secondStep[key];
            }
        }

        return true
    })()
}

function lastStep1(data){
    return (async function(){
        let opt1 = document.createElement('option'),
            country = document.querySelector(`[atas="${data.accountClass}-registration__form-select-country"]`);
        // Country
        opt1.value = 139;
        opt1.textContent = "Moldova";
        country.innerHTML = '';
        country.append(opt1);
        country.dispatchEvent(new Event("change"));

        return true
    })()
}

function lastStep2(data){
    return (async function(){
        if (data.accountClass === "buyer") {
            await new Promise(resolve => {
                let interval;
                interval = setInterval(() => {
                    if (!!document.querySelector(`[atas="${data.accountClass}-registration__form-select-state"] .select2-selection__rendered`)) {
                        resolve();
                        clearInterval(interval);
                    }
                }, 200);
            });
        }

        let opt2 = document.createElement('option'),
            state = document.querySelector(`[atas="${data.accountClass}-registration__form-select-state"] select`);
        // State/Region
        opt2.value = 1864;
        opt2.textContent = "Anenii noi";
        state.innerHTML = '';
        state.append(opt2);
        state.dispatchEvent(new Event("change"));

        return true
    })()
}

function lastStep3(data){
    return (async function(){
        if (data.accountClass === "buyer") {
            await new Promise(resolve => {
                let interval;
                interval = setInterval(() => {
                    if (!!document.querySelector(`[atas="${data.accountClass}-registration__form-select-city"] .select2-selection__rendered`)) {
                        resolve();
                        clearInterval(interval);
                    }
                }, 200)
            });
        }
        let opt3 = document.createElement('option'),
            city = document.querySelector(`[atas="${data.accountClass}-registration__form-select-city"] select`);
        // City
        opt3.value = 1864;
        opt3.textContent = "Anenii noi";
        city.innerHTML = '';
        city.append(opt3);
        // Address
        document.querySelector(`[atas="${data.accountClass}-registration__form-address"]`).value = "Backstop 36 ap. 23";
        // Zip code
        document.querySelector(`[atas="${data.accountClass}-registration__form-zip-code"]`).value = 90001;
        // Terms & Conditions
        document.querySelector(`[atas="${data.accountClass}-registration__form-checkbox-terms"]`).click();

        return true
    })()
}

function hideSelect2() {
    return (async () => {
        document.querySelectorAll(".select2-container--open").forEach(e => e.classList.remove("select2-container--open"));
        await new Promise(resolve => setTimeout(() => resolve(), 500));

        return true
    })();
}
