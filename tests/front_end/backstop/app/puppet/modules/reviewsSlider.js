module.exports = async (page, scenario) => {
    const variables = require("../variables/variables");
    const data = {
        review: {
            img: variables.img(300, 300),
            name: variables.name.medium,
            type: variables.userGroups.certified.text.manufacturer,
            classType: variables.userGroups.certified.class,
            text: variables.lorem(100),
        },
    };

    // Replace reviews
    await page.waitForFunction(reviewsSlider, {}, data.review);
};

function reviewsSlider(data) {
    return (async () => {
        document.querySelectorAll(`[atas="global__reviews-slide"]`).forEach((e, i) => {
            const image = e.querySelector(`[atas="global__reviews-slider_image"]`);
            const group = e.querySelector(`[atas="global__reviews-slider_group"]`);

            image.dataset.src = data.img;
            image.src = data.img;
            group.textContent = data.type;
            group.classList.add(data.classType);
            e.querySelector(`[atas="global__reviews-slider_name"]`).textContent = data.name;
            e.querySelector(`[atas="global__reviews-slider_text"]`).textContent = data.text;
        });

        return true;
    })();
}
