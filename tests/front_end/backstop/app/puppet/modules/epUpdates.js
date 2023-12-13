module.exports = async page => {
    const variables = require("../variables/variables");
    const data = {
        updates: {
            title: variables.lorem(100),
            description: variables.lorem(100),
            date: variables.dateFormat.withTime,
        },
    };
    await page.waitForFunction(replaceUpdates, {}, data.updates);
}

function replaceUpdates(data) {
    return (async () => {
        document.querySelectorAll(`[atas="ep-updates__item"]`).forEach(update => {
            const textElements = {
                title: update.querySelector(`[atas="ep-updates__title"]`),
                description: update.querySelector(`[atas="ep-updates__date"]`),
                date: update.querySelector(`[atas="ep-updates__description"]`),
            };

            Object.keys(textElements).forEach(key => {
                textElements[key].textContent = data[key];
            });
        });

        return true;
    })();
}
