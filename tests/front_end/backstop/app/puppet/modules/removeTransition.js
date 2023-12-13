module.exports = async (page) => {
    await page.waitForFunction(() => {
        return (async () => {
            Array.from(document.querySelectorAll("*")).forEach(e => {
                e.style.transition = "none";
            });

            return true;
        })()
    })
}
