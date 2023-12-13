module.exports = async (page) => {
    const variables = require("../variables/variables");

    await page.waitForFunction(replaceUserStatus, {}, {
        userStatus: variables.userOnlineStatus,
        classTextRed: variables.textClassList.textRed,
        classTextGreen: variables.textClassList.textGreen,
    });
}

function replaceUserStatus(data) {
    return (async () => {
        // Online status
        const status = document.querySelectorAll(`[atas="global__user-online-status"]`);
        if(status.length) {
            status.forEach(e => {
                e.textContent = data.userStatus;
                e.classList.remove(data.classTextRed);
                e.classList.add(data.classTextGreen);
            });
        }

        return true;
    })();
}
