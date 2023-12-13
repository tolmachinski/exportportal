module.exports = async (page, scenario) => {
    const funcions = {
        click: require("../functions/click"),
    };
    const variables = require("../variables/variables");
    const data = {
        detailedInfo: {
            title: variables.lorem(200),
            description: variables.lorem(3000),
            date: variables.dateFormat.withTime,
        },
        sidebarInfo: {
            title: variables.lorem(200),
            date: variables.dateFormat.withTime,
        },
    };

    await page.waitForFunction(replaceDetailedInfo, {}, data.detailedInfo);
    await page.waitForFunction(replaceSidebarInfo, {}, data.sidebarInfo);
    await require("../modules/commentsCommon")(page, 500);
    await require("../modules/epUpdates")(page);

    if (scenario.openAddCommentPopup) {
        await funcions.click(page, `[atas="global__common-comments_title_button-add"]`);
        await page.waitForNetworkIdle();
        await page.waitForSelector(`.fancybox-inner .btn`);
    }
}

function replaceDetailedInfo(data) {
    return (async () => {
        const textElements = {
            title: document.querySelector(`[atas="ep-updates-detail__title"]`),
            description: document.querySelector(`[atas="ep-updates-detail__description"]`),
            date: document.querySelector(`[atas="ep-updates-detail__date"]`),
        };

        Object.keys(textElements).forEach(key => {
            textElements[key].textContent = data[key];
        });

        return true;
    })();
}

function replaceSidebarInfo(data) {
    return (async () => {
        const textElements = {
            title: document.querySelectorAll(`[atas="ep-updates-detail__sidebar-title"]`),
            date: document.querySelectorAll(`[atas="ep-updates-detail__sidebar-date"]`),
        };

        Object.keys(textElements).forEach(key => {
            textElements[key].forEach(item => {
                item.textContent = data[key];
            })
        });

        return true;
    })();
}
