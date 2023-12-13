module.exports = async (page, scenario) => {
    const variables = require("../variables/variables");
    const functions = {
        click: require("../functions/click"),
        text: require("../functions/text"),
    };

    const data = {
        img: variables.img(200, 200),
        title: variables.name.xLong,
        btnText: variables.name.short,
        text: variables.lorem(1500),
    };

    await page.waitForFunction(checkTheNumberOfBlocks, {}, data);

    await page.waitForFunction(
        functions.text,
        {},
        {
            selectorAll: `[atas="page__shipping-methods__item-title"]`,
            text: data.title,
        }
    );

    await page.waitForFunction(
        functions.text,
        {},
        {
            selectorAll: `[atas="page__shipping-methods__read-more-item"]`,
            text: data.text,
        }
    );
    await page.waitForFunction(
        functions.text,
        {},
        {
            selectorAll: `[atas="page__shipping-methods__find-ideal-method-btn"]`,
            text: data.btnText,
        }
    );

    await page.waitForFunction(changeImage, {}, data);
    await page.waitForFunction(addReadMoreButton, {});
    await page.waitForFunction(hideTestMethods, {});

    // Open more text
    if (scenario.readMore) {
        await functions.click(page, `[atas="global__text__read-more-btn"]`);
    }
};

function changeImage(data) {
    return (async () => {
        document.querySelectorAll(`[atas="page__shipping-methods__find-ideal-method_image"]`).forEach(image => {
            image.src = data.img;
        });

        return true;
    })();
}

function addReadMoreButton() {
    return (async () => {
        document.querySelectorAll(`[atas="page__shipping-methods__item-body"]`).forEach(item => {
            const readMoreBtn = item.querySelector(`[atas="global__text__read-more-btn"]`);
            if (!readMoreBtn) {
                item.insertAdjacentHTML(
                    "beforeend",
                    `<button class="read-more-btn txt-blue2 bg-n" atas="global__text__read-more-btn">Read more <i class="ep-icon ep-icon_arrow-down fs-12"></i></button>`
                );
            }
        });

        return true;
    })();
}

function hideTestMethods(data) {
    return (async () => {
        const btns = document.querySelectorAll(`[atas="page__shipping-methods__find-ideal-method-btn"]`);
        const items = document.querySelectorAll(`[atas="page__shipping-methods__item"]`);
        const NUMBER_OF_METHODS = 10;
        items.forEach((item, index) => {
            if (NUMBER_OF_METHODS <= index) {
                item.style.display = "none";
                btns[index].style.display = "none";
            }
        });

        return true;
    })();
}

function checkTheNumberOfBlocks(data) {
    return (async () => {
        const parent = document.querySelector(`[atas="page__shipping-methods__item"]`).parentElement;
        const items = document.querySelectorAll(`[atas="page__shipping-methods__item"]`);
        let result = "";
        for (let i = items.length; i < 10; i++) {
            result += `
            <li class="shipping-methods__item">
                <img
                    class="shipping-methods__item-image js-lazy"
                    src="${data.img}"
                    alt="backstop-test" >
                <div class="shipping-methods__item-body" atas="page__shipping-methods__item-body">
                    <h3 class="shipping-methods__item-title">
                        ${data.title}
                    </h3>
                    <div class="js-read-more shipping-methods__item-text">
                    ${data.text}
                    </div>
                </div>
            </li>
        `;
        }

        parent.insertAdjacentHTML("beforeend", result);
        return true;
    })();
}
