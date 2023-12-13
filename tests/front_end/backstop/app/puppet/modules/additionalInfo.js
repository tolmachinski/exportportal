module.exports = async (page, scenario) => {
    const variables = await require("../variables/variables");

    const data = {
        name: variables.name.medium,
        userGroup: {
            text: variables.userGroups.certified.text,
            class: variables.userGroups.certified.class,
        },
        country: variables.country.name,
        location: variables.country.location,
        flag: variables.country.flag,
    };
    await page.waitForFunction(replaceAdditionalInfo, {}, data);
};

function replaceAdditionalInfo(data) {
    return (async function () {
        document.querySelectorAll(`[atas="global__additional-info__user"]`).forEach(el => {
            if (el) {
                el.textContent = data.name;
            }
        });
        document.querySelectorAll(`[atas="global__additional-info__user-group"]`).forEach(el => {
            if (el) {
                el.textContent = data.userGroup.text;
                el.classList.add(data.userGroup.class);
            }
        });
        document.querySelectorAll(`[atas="global__additional-info__country-name"]`).forEach(el => {
            el.textContent = data.country;
        });
        document.querySelectorAll(`[atas="global__additional-info__location-city"]`).forEach(el => {
            el.textContent = data.location;
        });
        document.querySelectorAll(`[atas="global__additional-info__country-flag"]`).forEach(el => {
            el.src = data.flag;
        });
        return true;
    })();
}
