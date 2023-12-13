module.exports = async (page, scenario) => {
    if(scenario.isLogin){
        await page.waitForFunction(successLogin, {}, scenario);
        await page.waitForTimeout(2500);
    } else {
        await require('../functions/click')(page, `[atas="login__form-submit"]`);
    }

    // Replace user info
    await require("../modules/userInfo")(page, scenario);
}

// Success login
function successLogin(data){
    return (async function(){
        document.querySelector(`[atas="login__form-email"]`).value = data.login;
        document.querySelector(`[atas="login__form-password"]`).value = data.password;
        document.querySelector(`[atas="login__form-submit"]`).click();
        return true
    })()
}
