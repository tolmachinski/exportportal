module.exports = async (page, scenario) => {
    await page.waitForFunction(openSidebarPopup, {}, scenario.rightSidebarPopup);
    await page.waitForTimeout(500);
};

async function openSidebarPopup (type) {
    return (async () => {
        document.querySelector(`[atas="right-sidebar__popup-${type}"]`).click();

        return true;
    })()
}
