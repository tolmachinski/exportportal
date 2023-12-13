module.exports = async (page, scenario) => {
    const variables = require("../variables/variables");
    const functions = {
        click: require("../functions/click"),
    };
    const data = {
        pictureImg: variables.img(391, 295),
        title: variables.lorem(150),
        category: variables.lorem(100),
        text: variables.lorem(500),
    };

    await require("../modules/sellerSidebar")(page);
    await require("../modules/comment")(page, 500);

    await page.waitForFunction(replaceMainData, {}, data);
    await page.waitForFunction(replaceDataForMoreVideo, {}, data);
    await page.waitForFunction(require("../modules/iframeVideo"), {}, {selector: `[atas="global__video-iframe"]`});

    if (scenario.showLeaveCommentDropdown) {
        await functions.click(page, `[atas="page__seller-videos__comments_dropdown-btn"]`);

        if (scenario.showAddCommentPopup) {
            await functions.click(page, `[atas="page__seller-videos__comments_dropdown-menu_leave-a-comment-btn"]`);
            await page.waitForNetworkIdle();
        }

        if (scenario.submitEmptyForm) {
            await functions.click(page, `[atas="popup__seller-videos__add-comment-form_send-btn"]`);
            await page.waitForSelector(variables.systMessCardClass, { visible: true });
        }
    }
};

const replaceMainData = data => {
    return (async function () {
        document.querySelector(`[atas="page__seller-videos__details-title"]`).textContent = data.title;
        document.querySelector(`[atas="page__seller-videos__details-category"]`).textContent = data.category;
        return true;
    })();
};

const replaceDataForMoreVideo = data => {
    return (async function () {
        document.querySelectorAll(`[atas="page__seller-videos__item"]`).forEach(item => {
            item.querySelector(`[atas="page__seller-videos__item-image"]`).src = data.pictureImg;
            item.querySelector(`[atas="page__seller-videos__item-title"]`).textContent = data.title;
            item.querySelector(`[atas="page__seller-videos__item-category"]`).textContent = data.category;
            item.querySelector(`[atas="page__seller-videos__item-comments-count"]`).textContent = 99999;
        });

        return true;
    })();
};
