module.exports = async (page, config) => {
    await page.waitForFunction(dataChanger, {}, config)
}

function dataChanger(config){
    return (async function(){
        let changer = (selectorAll, value, attr, type, isTinymce) => {
            for (let selector of document.querySelectorAll(selectorAll)) {
                if (isTinymce && selector.childElementCount > 0) {
                    for (let element of selector.childNodes) {
                        if (type === "text" && element.nodeName === "#text") {
                            element[attr] = value;
                        }
                    }
                    continue;
                }
                if (type === "image") {
                    selector.srcset = value;
                    selector.dataset.src = value;
                }
                selector[attr] = value;
            }
        }
        for (const target of config) {
            changer(
                target.selectorAll, //selectorAll
                target.value, // value
                (target.attr) ? target.attr : "textContent", // attribute
                (target.type) ? target.type : "text", // is image?
                (target.isTinymce) ? target.isTinymce : false // if this is Tinymce element p and is parent of few elements and need to find text or image
            )
        }
        return true
    })()
}
