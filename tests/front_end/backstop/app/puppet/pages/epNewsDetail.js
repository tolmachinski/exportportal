module.exports = async (page, scenario) => {
    const funcions = {
        click: require("../functions/click"),
    };
    const variables = require("../variables/variables");
    const data = {
        detailedInfo: {
            title: variables.lorem(200),
            description: variables.lorem(2000),
            date: variables.dateFormat.withTime,
            img: variables.img(512, 512),
        },
    };

    await page.waitForFunction(replaceDetailedInfo, {}, data.detailedInfo);
    await require("../modules/newsItem")(page, scenario);
    await require("../modules/commentsCommon")(page, 500);

    if (scenario.withoutMainImage) {
        await page.waitForFunction(removeMainImage);
    }

    if (scenario.openAddCommentPopup) {
        await funcions.click(page, `[atas="global__common-comments_title_button-add"]`);
        await page.waitForNetworkIdle();
        await page.waitForSelector(`.fancybox-inner .btn`);
    }
}

function replaceDetailedInfo(data) {
    return (async () => {
        const news = document.querySelector(`[atas="page__ep-news-detail__info"]`)
        const title = news.querySelector(`[atas="page__ep-news-detail__title"]`);
        const description = news.querySelector(`[atas="page__ep-news-detail__description"]`);
        const date = news.querySelector(`[atas="page__ep-news-detail__date"]`);
        const img = news.querySelector(`[atas="page__ep-news-detail__image"]`);

        title.textContent = data.title;
        description.textContent = data.description;
        date.textContent = data.date;
        img.dataset.src = data.img;
        img.src = data.img;


        return true;
    })();
}

function removeMainImage() {
    return (async () => {
        document.querySelector(`[atas="page__ep-news-detail__image-parent"]`).remove();

        return true;
    })();
}
