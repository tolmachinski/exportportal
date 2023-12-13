module.exports = async (page, scenario) => {
    const variables = require('../variables/variables.js');
    const functions = {
        counter: require('../functions/counter'),
        text: require('../functions/text'),
        click: require('../functions/click')
    }
    let data = {
        main:{
            title: variables.lorem(104),
            mainImage: variables.img(640, 512),
            sliderImages: [
                variables.img(313, 125),
                variables.img(50, 125),
                variables.img(125, 125)
            ],
            views: 999999,
            discount: 99,
            price: variables.price.high,
            valPerPrice: "Twenty-Foot Container",
            weight: "9999999999999.99",
            option: "Backstop Option Name:",
            optionVal: "Backstop Option Value",
            quantity: 9999,
            quantityCounter: 3464534
        },
        other:{
            b2b: variables.lorem(500),
            year: 9999,
            photos: variables.img(850, 340),
            banner: variables.img(426, 600)
        }
    };
    // Main data
    await page.waitForFunction(mainData, {}, data.main);
    // Counters views, quantity
    await page.waitForFunction(functions.counter, {}, {
        selectorAll:`[atas="item__counter_views"]`,
        value: data.main.views
    });
    await page.waitForFunction(functions.counter, {}, {
        selectorAll: `[atas="item__min-max-counter"]`,
        value: data.main.quantityCounter
    });
    await page.waitForFunction(functions.counter, {}, {
        selectorAll: `[atas="item__discount"]`,
        value: data.main.discount
    });
    // Text: Weight, prices, val per price
    await page.waitForFunction(functions.text, {}, {
        selectorAll: `[atas="item__weight"]`,
        text: data.main.weight
    });
    await page.waitForFunction(functions.text, {}, {
        selectorAll: `[atas="item__price"]`,
        text: data.main.price
    });
    await page.waitForFunction(functions.text, {}, {
        selectorAll: `[atas="item__val-for-price"]`,
        text: data.main.valPerPrice
    });
    // Other data
    await page.waitForFunction(otherData, {}, data.other);
    // Sidebar company info
    await require('../modules/companyCard')(page);
    // Sidebar products
    await require('../modules/productCard')(page);
    // Sidebar banner
    await page.waitForFunction(require('../functions/picture'), {}, {
        selectorAll: `[atas="global__banner-picture"]`,
        src: data.other.banner
    });
    // Comments
    if(scenario.showAllComments) {
        await functions.click(page, `[atas="item__show-all-comments"]`);
        await require('../modules/comment')(page, 500);
    }
    if(scenario.openModal){
        await functions.click(page, `[atas="page__item__edit-btn"]`);
    }
    // Questions
    await require('../modules/question')(page);
    // Reviews
    await require("../modules/ratingBootstrap")(page);
    await require('../modules/reviews')(page);
    // Add to droplist
    if (scenario.addToDroplist) {
        await functions.click(page, `[atas="page__item-detail__add_to_droplist"]`);

        await page.waitForNetworkIdle();
        await page.waitForTimeout(500);
        await require("../modules/changeData")(page, [
            // Description test
            {
                selectorAll: `[atas="popup__add-to-droplist__img"]`,
                value: variables.img(87, 65),
                attr: "src",
                type: "img",
            },
            {
                selectorAll: `[atas="popup__add-to-droplist__ttl-item"]`,
                value: variables.name.medium,
            },
            {
                selectorAll: `[atas="popup__add-to-droplist__price-item"]`,
                value: variables.price.low,
            },
        ]);

        await page.waitForTimeout(500);
    }
    // Add to droplist
    if (scenario.removeFromDroplist) {
        await functions.click(page, `[atas="page__item-detail__remove_from_droplist"]`);
    }

    await require("../modules/disableSliders")(page);
    // Additional wait 1000ms for loading all images
    await page.waitForTimeout(500);
    await page.waitForNetworkIdle();
}

function mainData(data){
    return (async function(){
        // Title
        document.querySelector(`[atas="item__title"]`).textContent = data.title;
        // Main image
        document.querySelector(`[atas="item__main-image"]`).src = data.mainImage;
        // Slider images
        document.querySelectorAll(`[atas="item__gallery-image"]`).forEach((e,i)=>{
            let imgs = data.sliderImages;
            e.src = imgs[i % imgs.length];
        })
        // Toggle info panels with class .active to disable active
        for(let selector of document.querySelectorAll(`[atas="item__toggle-info"].active`)){
            selector.click();
        }
        // Options
        for(let selector of document.querySelectorAll(`[atas="item__option-key"]`)){
            selector.textContent = data.option;
        }
        for(let selector of document.querySelectorAll(`[atas="item__option-val"]`)){
            selector.textContent = data.optionVal;
        }
        // Quantity
        document.querySelector(`[atas="item__quantity"]`).value = data.quantity;
        return true
    })()
}

function otherData(data){
    return (async function(){
        // B2b description
        let b2bAboutDescription = document.querySelector(`[atas="item__b2b-about-description"]`);
        if(b2bAboutDescription && b2bAboutDescription != null) {
            b2bAboutDescription.textContent = data.b2b;
        }
        // Product information year
        document.querySelector(`[atas="item__option-year-val"]`).textContent = data.year;
        // Photos
        for(let selector of document.querySelectorAll(`[atas="item__photo"]`)){
            selector.dataset.src = data.photos;
            selector.src = data.photos;
        }
        return true
    })()
}
