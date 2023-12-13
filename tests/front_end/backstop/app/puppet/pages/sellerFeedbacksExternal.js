module.exports = async (page, scenario) => {
    const variables = require("../variables/variables");

    const data = {
        userName: variables.name.medium,
        date: variables.dateFormat.withoutTime,
        description: variables.lorem(200),
    }

    await require("../modules/sellerSidebar")(page);
    await page.waitForFunction(replaceDataForExternalFeedbacks, {}, data);
};

const replaceDataForExternalFeedbacks = data => {
    return (() => {
        document.querySelectorAll(`[atas="global__company-external-feedbacks__item"]`).forEach(item => {
            item.querySelector(`[atas="global__company-external-feedbacks__item-user-name"]`).textContent = data.userName;
            item.querySelector(`[atas="global__company-external-feedbacks__item-date"]`).textContent = data.date;
            item.querySelector(`[atas="global__company-external-feedbacks__text"]`).textContent = data.description;
        });

        return true;
    })();
};
