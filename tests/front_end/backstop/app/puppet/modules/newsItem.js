module.exports = async (page, scenario = {}) => {
    const variables = require("../variables/variables");
    const data = {
        news: {
            title: variables.lorem(100),
            description: variables.lorem(100),
            date: variables.dateFormat.withTime,
            img: scenario.newsletterArchive ? variables.img(570, 180) : variables.img(335, 200),
        },
    };

    await page.waitForFunction(replaceNews, {}, { data: data.news, withImages: scenario.withImages ?? null });
};

function replaceNews({ data, withImages }) {
    return (async () => {
        document.querySelectorAll(`[atas="global__news__item"]`).forEach((news, i) => {
            const title = news.querySelector(`[atas="global__news__item_title"]`);
            const description = news.querySelector(`[atas="global__news__item_description"]`);
            const date = news.querySelector(`[atas="global__news__item_date"]`);
            const img = news.querySelector(`[atas="global__news__item_image"]`);

            title.textContent = data.title;
            description.textContent = data.description;
            date.textContent = data.date;
            if (withImages === false || (withImages === null && i % 2 === 0)) {
                img.closest(`[atas="global__news__item_image-parent"]`).remove();
            } else {
                img.dataset.src = data.img;
                img.src = data.img;
            }
        });

        return true;
    })();
}
