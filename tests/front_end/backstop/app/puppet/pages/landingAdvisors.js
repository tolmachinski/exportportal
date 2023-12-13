module.exports = async (page, scenario) => {
    const variables = require('../variables/variables');
    let data = {
        sliders: {
            src: variables.img(1912, 670),
            title: variables.lorem(14),
            text: variables.lorem(70),
            list: variables.lorem(10)
        }
    }
    // Slider
    await page.waitForFunction(advisorsSlider, {}, data.sliders);
}

function advisorsSlider(slider){
    return (async function(){
        document.querySelectorAll('[atas="landing__advisors-item"]').forEach(item => {
            // Title
            item.querySelector('.epl-slider__headline').textContent = slider.title;
            // Text
            item.querySelector('.epl-slider__text').textContent = slider.text;
            // List
            let list = item.querySelectorAll('.epl-slider__list-item');
            if (list.length) {
                item.querySelectorAll('.epl-slider__list-item').forEach(li => {
                    li.textContent = slider.list;
                })
            }
        })
        return true;
    })()
}
