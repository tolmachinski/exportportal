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
            price: variables.price.max,
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
    // Sidebar company info
    await require('./companyCard')(page);
    // Sidebar products
    await require('./productCard')(page);
    // Sidebar banner
    await page.waitForFunction(require('../functions/picture'), {}, {
        selectorAll: `[atas="global__banner-picture"]`,
        src: data.other.banner
    });
};

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
