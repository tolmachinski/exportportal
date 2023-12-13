module.exports = async (page, scenario) => {
    const variables = require("../variables/variables");
    const functions = {
        click: require("../functions/click"),
    };
    const data = {
        mainPictureImg: variables.img(800, 800),
        pictureImg: variables.img(391, 295),
        title: variables.lorem(150),
        category: variables.lorem(100),
        text: variables.lorem(500),
    };

    await require("../modules/sellerSidebar")(page);
    await require("../modules/comment")(page, 500);

    await page.waitForFunction(replaceMainData, {}, data);
    await page.waitForFunction(replaceDataForMorePictures, {}, data);

    if (scenario.showLeaveCommentDropdown) {
        await functions.click(page, `[atas="page__company-pictures__comments_dropdown-btn"]`);

        if (scenario.showAddCommentPopup) {
            await functions.click(page, `[atas="page__company-pictures__comments_dropdown-menu_leave-a-comment-btn"]`);
            await page.waitForNetworkIdle();
            await page.waitForSelector(`[atas="popup__seller-pictures__add-comment-form_send-btn"]`, { visible: true });
        }

        if (scenario.submitEmptyForm) {
            await functions.click(page, `[atas="popup__seller-pictures__add-comment-form_send-btn"]`);
            await page.waitForSelector(variables.systMessCardClass, { visible: true });
        }
    }
};

const replaceMainData = data => {
    return (async function () {
        document.querySelector(`[atas="page__company-pictures__details-image"]`).src = data.mainPictureImg;
        document.querySelector(`[atas="page__company-pictures__details-title"]`).textContent = data.title;
        document.querySelector(`[atas="page__company-pictures__details-category"]`).textContent = data.category;

        return true;
    })();
};

const replaceDataForMorePictures = data => {
    return (async function () {
        document.querySelectorAll(`[atas="page__company-pictures__item"]`).forEach(item => {
            item.querySelector(`[atas="page__company-pictures__item-image"]`).src = data.pictureImg;
            item.querySelector(`[atas="page__company-pictures__item-title"]`).textContent = data.title;
            item.querySelector(`[atas="page__company-pictures__item-category"]`).textContent = data.category;
            item.querySelector(`[atas="page__company-pictures__item-comments-count"]`).textContent = 99999;
        });

        return true;
    })();
};
