module.exports = async (page, scenario = {}) => {
    const variables = require("../variables/variables");
    const data = {
        news: {
            title: variables.lorem(100),
            description: variables.lorem(100),
            date: variables.dateFormat.withTime,
            img: variables.img(570, 180),
        },
    };

    await page.waitForFunction(replaceNews, {}, { data: data.news});
};

function replaceNews({ data }) {
    return (async () => {
        document.querySelectorAll(`[atas="page__ep-newsletter__item"]`).forEach((news, i) => {
            const title = news.querySelector(`[atas="page__ep-newsletter__item_title"]`);
            const description = news.querySelector(`[atas="page__ep-newsletter__item_description"]`);
            const date = news.querySelector(`[atas="page__ep-newsletter__item_date"]`);
            const img = news.querySelector(`[atas="page__ep-newsletter__item_image"]`);

            title.textContent = data.title;
            description.textContent = data.description;
            date.textContent = data.date;
            img.dataset.src = data.img;
            img.src = data.img;
        });

        return true;
    })();
}
