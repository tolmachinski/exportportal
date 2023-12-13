module.exports = async (page, scenario) => {
    const variables = require('../variables/variables');

    let data = {
        sliders: {
            src: variables.img(477, 266),
            title: variables.lorem(),
            text: variables.lorem(4),
            list: variables.lorem(10),
            srcBig: variables.img(720, 453),
        }
    }

    // Slider
    await page.waitForFunction(learnMoreSlider, {}, data.sliders);

    // Subscribe
    if(scenario.subscribe){
        // Error validation
        await page.waitForFunction(errorValidation);
        await page.waitForNetworkIdle();
    }

    // Additional wait 500ms
    await page.waitForTimeout(500);
}

function errorValidation(){
    return (async function(){
        document.querySelector(`[atas="page__learn-more__subscribe-btn"]`).click();
        return true
    })()
}

function learnMoreSlider(slider){
    return (async function(){
        document.querySelectorAll('[atas="page__learnmore-check__slider-item"]').forEach(item => {
            item.querySelector('[atas="page__learnmore-check__slider-text"]').textContent = slider.title;
            item.querySelector('[atas="page__learnmore-check__slider-image"]').src = slider.src;
        })

        document.querySelectorAll('[atas="page__learnmore-tour__slider-item"]').forEach(item => {
            item.querySelector('[atas="page__learnmore-tour__slider-ttl"]').textContent = slider.title;
            item.querySelector('[atas="page__learnmore-tour__slider-date"]').textContent = '2018';
            item.querySelector('[atas="page__learnmore-tour__slider-image"]').src = slider.srcBig;
        })
        return true;
    })()
}
