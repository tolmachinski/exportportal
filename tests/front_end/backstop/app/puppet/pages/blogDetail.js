module.exports = async (page, scenario) => {
    const variables = require('../variables/variables');
    const data = {
        recommended: {
            titles: [
                variables.lorem(11),
                variables.lorem(35),
                variables.lorem(60),
                variables.lorem(100),
            ],
            img: variables.img(360, 156)
        },
    }

    // Blog content
    await require('../modules/changeData')(page, [
        {
            selectorAll: `[atas="page__blog-detail__category"]`,
            value: variables.lorem(50)
        },
        {
            selectorAll: `[atas="page__blog-detail__title"]`,
            value: variables.lorem(250)
        },
        {
            selectorAll: `[atas="page__blog-detail__short-description"]`,
            value: variables.lorem(250)
        },
        {
            selectorAll: `[atas="page__blog-detail__date"]`,
            value: variables.dateFormat.withoutTime
        },
        {
            selectorAll: `[atas="page__blog-detail__main-image"]`,
            value: variables.img(750, 326),
            attr: "src",
            type: "image"
        },
        {
            selectorAll: `[atas="page__blog-detail__main-image-caption"]`,
            value: variables.lorem(250),
        },
        {
            selectorAll: `[atas="page__blog-detail__content"] h2`,
            value: variables.lorem(30),
        },
        {
            selectorAll: `[atas="page__blog-detail__content"] h3`,
            value: variables.lorem(20),
        },
        {
            selectorAll: `[atas="page__blog-detail__content"] h4`,
            value: variables.lorem(20),
        },
        {
            selectorAll: `[atas="page__blog-detail__content"] p`,
            value: variables.lorem(500),
            isTinymce: true
        },
        {
            selectorAll: `[atas="page__blog-detail__content"] li:not([role="presentation"])`,
            value: variables.lorem(50),
        },
        {
            selectorAll: `[atas="page__blog-detail__content"] img`,
            value: variables.img(750, 325),
            attr: "src",
            type: "image",
        },
        {
            selectorAll: `[atas="page__blog-detail__content"] figcaption`,
            value: variables.lorem(250),
        },
        {
            selectorAll: `[atas="page__blog-detail__tags_item"]`,
            value: variables.tag[20]
        }
    ])

    // Slider Products
    await require('../modules/productCard')(page);

    // Blog comments
    await page.waitForNetworkIdle();
    await require('../modules/commentsCommon')(page, 1000);

    // Recommended blogs
    await page.waitForFunction(replaceRecommendedBlogs, {}, data);
}

const replaceRecommendedBlogs = data => {
    return (() => {
        let j = 0;
        document.querySelectorAll(`[atas="page__blog-detail__recommended_item"]`).forEach(element => {
            if (j == 4) {
                j = 0;
            }

            element.querySelector(`[atas="page__blog-detail__recommended_image"]`).src = data.recommended.img;
            element.querySelector(`[atas="page__blog-detail__recommended_title"]`).textContent = data.recommended.titles[j];

            j += 1;
        });

        return true;
    })()
};
