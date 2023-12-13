module.exports = async (page, scenario) => {
    const variables = require("../variables/variables");
    const functions = {
        click: require("../functions/click"),
    };

    const data = {
        name: variables.name.medium,
        text: variables.lorem(10000),
        image: variables.img(798, 798),
        picture: variables.img(190, 133),
        userGroup: {
            class: variables.userGroups.certified.class,
            text: variables.userGroups.certified.text.seller,
        },
    };

    await require("../modules/additionalInfo")(page);
    await page.waitForFunction(replaceBranchData, {}, data);
    await page.waitForFunction(replaceHeadquarterData, {}, data);
    await require("../modules/sellerSidebar")(page);
    await functions.click(page, `[atas="page__branch__company-more-picture-btn"]`);
};

const replaceBranchData = data => {
    return (async function () {
        document.querySelector('[atas="page__branch__company-text"]').textContent = data.text;
        document.querySelector(`[atas="page__branch__company-video-bg"]`).src = data.image;
        document.querySelectorAll(`[atas="page__branch__company-picture"]`).forEach(picture => {
            picture.src = data.picture;
        });
        return true;
    })();
};

const replaceHeadquarterData = data => {
    return (async function () {
        document.querySelector(`[atas="page__branch__headquarter_user-name"]`).textContent = data.name;
        document.querySelector(`[atas="page__branch__headquarter_user-experience"]`).textContent = `99999 year of experience`;
        const userGroup = document.querySelector(`[atas="page__branch__headquarter_user-group"]`);
        userGroup.textContent = data.userGroup.text;
        userGroup.classList.add(data.userGroup.class);
        return true;
    })();
};
