module.exports = async (page, scenario, type) => {
    const functions = {
        click: require("../functions/click"),
    };

    if (scenario.openScheduleDemoPopup) {
        // Schedule a demo popup
        const openScheduleDemo = async () => {
            await page.waitForSelector(`[atas="${type}__banner-demo"] [atas="global__banner-picture"] img`, { visible: true });
            await functions.click(page, `[atas="${type}__banner-demo"] [atas="global__banner-btn"]`, false, { visible: true });

            return true;
        };

        if (type === "faq") {
            try {
                await page.waitForSelector(`[atas="${type}__banner-demo-bottom"] [atas="global__banner-picture"] img`, { visible: true, timeout: 5000 });
                await functions.click(page, `[atas="${type}__banner-demo-bottom"] [atas="global__banner-btn"]`, false, { visible: true, timeout: 5000 });
            } catch (e) {
                await openScheduleDemo();
            }
        } else {
            await openScheduleDemo();
        }

        await page.waitForSelector(`[atas="global__schedule-demo_form_submit-btn_popup"]`);
        await page.waitForNetworkIdle();
        await page.waitForTimeout(500);

        // Schedule a demo popup with validation
        if (scenario.validate) {
            await require("../functions/click")(
                page,
                `[atas="global__schedule-demo_form_submit-btn_popup"]`
            );
        }

        // Schedule a demo popup submit
        if (scenario.isSubmited) {
            const data = {
                fname: "Backstop",
                lname: "Test",
                email: `backstop-${new Date().getTime()}@backstop.test`,
                phone: "68321525",
            };
            await page.waitForFunction(loadSelect2);
            await page.waitForNetworkIdle();
            await page.waitForFunction(submitForm, {}, data);
            await functions.click(page, `[atas="global__schedule-demo_form_submit-btn_popup"]`);
            await page.waitForNetworkIdle();
            await page.waitForSelector(".bootstrap-dialog--results-success");
        }
    }

    return true;
};

function loadSelect2() {
    return (async function () {
        const select = document.querySelector(
            '[atas="global__schedule-demo_form_phone-select_popup'
        );
        select.querySelector(".select2").click();
        // Additional time to load plugin
        await new Promise((resolve) =>
            setTimeout(() => resolve(), 500)
        );

        return true;
    })();
}

function submitForm(data) {
    return (async function () {
        // Filling data
        document
            .querySelector('[atas="global__schedule-demo_form_fname_popup"]')
            .dispatchEvent(new Event("focus"));

        for (let key in data) {
            // If is phone select value in country
            if (key === "phone") {
                const select = document.querySelector(
                    '[atas="global__schedule-demo_form_phone-select_popup'
                );
                const realSelect = select.querySelector("select")
                realSelect.value = "137";
                realSelect.dispatchEvent(new Event("change"));
                document
                    .querySelectorAll(".select2-container--open")
                    .forEach((e) =>
                        e.classList.remove("select2-container--open")
                    );
                await new Promise((resolve) =>
                    setTimeout(() => resolve(), 500)
                );
            }
            document.querySelector(
                `[atas="global__schedule-demo_form_${key}_popup"]`
            ).value = data[key];
        }

        const opt1 = document.createElement("option");
        const opt2 = document.createElement("option");

        // Select Country
        country = document.querySelector('[atas="global__schedule-demo_form_country_popup"]');
        opt1.value = 139;
        opt1.textContent = "Moldova";
        country.innerHTML = "";
        country.append(opt1);
        country.dispatchEvent(new Event("change"));
        await new Promise(resolve => setTimeout(() => resolve(), 500));

        // Select User Type
        userType = document.querySelector('[atas="global__schedule-demo_form_user-type_popup"]');
        opt2.value = "buyer";
        opt2.textContent = "Buyer";
        userType.innerHTML = "";
        userType.append(opt2);
        userType.dispatchEvent(new Event("change"));
        await new Promise(resolve => setTimeout(() => resolve(), 500));

        // Ignore validation on mask
        document
            .querySelector('[atas="global__schedule-demo_form_phone_popup"]')
            .removeAttribute("class");

        return true;
    })();
}
