module.exports = async (page, scenario = {}) => {
    const variables = require("../variables/variables");
    const data = {
        news: {
            title: variables.lorem(100),
            description: variables.lorem(100),
            date: variables.dateFormat.withTime,
            img: variables.img(335, 200),
            link: variables.lorem(20),
            logo: variables.img(36, 20),
        },
    };
    await page.waitForFunction(replaceNews, {}, { data: data.news, withImages: scenario.withImages ?? null });
}

function replaceNews({ data, withImages = null }) {
    return (async () => {
        document.querySelectorAll(`[atas="global__mass-media__news"]`).forEach((news, i) => {
            const textElements = {
                title: news.querySelector(`[atas="global__mass-media__news-title"]`),
                description: news.querySelector(`[atas="global__mass-media__news-description"]`),
                date: news.querySelector(`[atas="global__mass-media__news-date"]`),
                link: news.querySelector(`[atas="global__mass-media__news-link"]`),
            };
            const img = news.querySelector(`[atas="global__mass-media__news-image"]`);
            const logo = news.querySelector(`[atas="global__mass-media__news-logo"]`);

            Object.keys(textElements).forEach(key => {
                if (textElements[key]) {
                    textElements[key].textContent = data[key];
                }
            });

            if (withImages === false || (withImages === null && i % 2 === 0)) {
                img.closest(`[atas="global__mass-media__news-image-parent"]`).remove();
            } else {
                img.dataset.src = data.img;
                img.src = data.img;
            }

            if(logo) {
                logo.src = data.logo;
            }
        });

        return true;
    })();
}
