module.exports = async function(page, cookieName, cookieValue){
    await page.waitForFunction(setCookie, {}, cookieName, cookieValue);

    return true;
};

function setCookie (cookieName, cookieValue) {
    return (async function(){
        document.cookie = `${cookieName}=${cookieValue}`;

        return true
    })();
}
