module.exports = async function(page, selector, checkVisibility){
    let jsClick = function(selector, checkVisibility){
        return (async function(){
            document.querySelectorAll(selector).forEach(el => {
                if(checkVisibility && window.getComputedStyle(el)['display'] === 'none'){
                    return;
                }
                el.click();
            });

            return true;
        })()
    }
    await page.waitForSelector(selector);
    await page.waitForFunction(jsClick, {}, selector, checkVisibility);

    return true;
}
