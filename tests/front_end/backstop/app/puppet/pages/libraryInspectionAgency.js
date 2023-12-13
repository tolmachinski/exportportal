module.exports = async (page, scenario) => {
    const variables = require("../variables/variables");
    await page.waitForFunction(replaceDataForAgencies, {}, {
        title: variables.lorem(125),
        addressFlag: variables.img(24, 24),
        addressText: variables.lorem(200),
        description: variables.lorem(350),
        phoneNumber: variables.phone,
    });
}

function replaceDataForAgencies(data) {
    return (async () => {
        document.querySelectorAll(`[atas="library-inspection-agency__agency-item"]`).forEach(agency => {
            const addressFlag = agency.querySelector(`[atas="library-inspection-agency__agency-address-flag"]`);
            const textNodes = {
                title: agency.querySelector(`[atas="library-inspection-agency__agency-title"]`),
                addressText: agency.querySelector(`[atas="library-inspection-agency__agency-address-text"]`),
                description: agency.querySelector(`[atas="library-inspection-agency__agency-description"]`),
                phoneNumber: agency.querySelector(`[atas="library-inspection-agency__agency-phone"]`)
            };
            Object.keys(textNodes).forEach(nodeKey => {
                textNodes[nodeKey].textContent = data[nodeKey];
            });
            addressFlag.dataset.src = data.addressFlag;
            addressFlag.src = data.addressFlag;
        });

        return true;
    })();
}
