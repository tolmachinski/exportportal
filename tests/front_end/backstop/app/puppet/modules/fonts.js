module.exports = async (page) => {
    await page.waitForNetworkIdle({ timeout: 240000 });
    await page.waitForFunction(() => {
        return document.fonts.ready.then(() => {
            return true
        })
    })
}
