module.exports = async (page, scenario) => {
    await page.waitForTimeout(500);
    await page.waitForFunction(changeShowingEntries);
};

function changeShowingEntries() {
    return (async () => {
        document.querySelector(".dataTables_info").textContent = "Showing 1 to 10 of 999 entries";

        return true;
    })();
}
