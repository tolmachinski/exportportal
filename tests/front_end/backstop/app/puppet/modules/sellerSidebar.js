module.exports = async page => {
    const variables = require("../variables/variables");
    await require("./companyCard")(page);

    const data = {
        logo: variables.img(280, 280),
        name: variables.name.medium,
    };

    await page.waitForFunction(replaceSellerSidebarData, {}, data);
    await require("../modules/replaceUserStatus")(page);
};

const replaceSellerSidebarData = data => {
    return (async () => {
        document.querySelector(`[atas="seller__sidebar_company-logo"]`).src = data.logo;
        document.querySelector(`[atas="seller__sidebar_company-name"]`).textContent = data.name;
        const branch = document.querySelector(`[atas="page__branch__headquarter_user-name"]`);
        if (branch) {
            branch.textContent.textContent = data.name;
        }
        return true;
    })();
};
