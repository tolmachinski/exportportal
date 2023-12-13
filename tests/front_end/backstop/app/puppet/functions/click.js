module.exports = async function(page, selector, checkVisibility = false, optionsWaitForSelector = {}){
    let jsClick = function(selector, checkVisibility){
        return (async function(){
            const elem = document.querySelector(selector);
            if(checkVisibility && window.getComputedStyle(elem)['display'] == 'none'){
                return true
            }
            const click = async elem => {
                if (elem) {
                    elem.click();
                } else {
                    await new Promise(resolve => setTimeout(() => resolve(), 200));
                    click(elem);
                }
            };
            click(elem);

            return true
        })()
    }
    await page.waitForSelector(selector, optionsWaitForSelector);
    await page.waitForFunction(jsClick, {}, selector, checkVisibility);

    return true;
}
