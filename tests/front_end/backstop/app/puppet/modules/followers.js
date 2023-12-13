module.exports = async (page, scenario) => {
    const variables = require("../variables/variables");
    const functions = {
        click: require("../functions/click"),
        counter: require("../functions/counter"),
    };

    const data = {
        name: variables.name.xLong,
        img: variables.img(76, 76),
        userGroup: variables.userGroups.certified.text.seller,
        follower: `follower ${variables.dateFormat.withoutTime}`,
        text: variables.lorem(500),
        shareWithEmail: {
            email: variables.mail,
        },
    };

    if (scenario.dropdown) {
        await page.waitForFunction(changeFollowersPlace);
        await functions.click(page, '[atas="global__item__followers-block_dropdown-btn"]');

        if (scenario.fillFollowForm) {
            await page.waitForSelector('[atas="global__item__followers-block_dropdown_follow-btn"]');
            await functions.click(page, '[atas="global__item__followers-block_dropdown_follow-btn"]');
            await page.waitForNetworkIdle();
            await page.waitForFunction(fillFollowForm, {}, data);

            if (scenario.followUser) {
                await functions.click(page, '[atas="popup__follow__form_send-btn"]');
                await page.waitForNetworkIdle();
                await functions.click(page, '[atas="global__item__followers-block_dropdown_follow-btn"]');
                await page.waitForNetworkIdle();
            }
        }

        if (scenario.email) {
            await functions.click(page, '[atas="global__item__followers-block_dropdown_email-btn"]');
            await page.waitForNetworkIdle();
            await page.waitForFunction(fillEmailsForm, {}, data);

            if (scenario.sendEmail) {
                await functions.click(page, '[atas="popup__share__form_send-btn"]');
                await page.waitForNetworkIdle();
            }
        }

        if (scenario.share) {
            await functions.click(page, '[atas="global__item__followers-block_dropdown_share-btn"]');
            await page.waitForNetworkIdle();
            await page.waitForFunction(fillShareForm, {}, data);

            if (scenario.sendShare) {
                await functions.click(page, '[atas="popup__share__form_send-btn"]');
                await page.waitForNetworkIdle();
            }
        }
    }

    await page.waitForNetworkIdle();
    await require("./followersItems")(page, scenario);
};

const changeFollowersPlace = () => {
    return (async function () {
        const followers = document.querySelectorAll('[atas="global__item__followers-block"]');

        if (window.innerWidth < 992) {
            followers[followers.length - 1].after(followers[0]);
        }

        if (window.innerWidth < 768) {
            followers[followers.length - 1].after(followers[1]);
        }

        return true;
    })();
};

const fillFollowForm = data => {
    return (async function () {
        document.querySelector('[atas="popup__follow__form_message-input"]').value = data.text;
        return true;
    })();
};

const fillEmailsForm = data => {
    return (async function () {
        document.querySelector('[atas="popup__share__form_emails-input"]').value = data.shareWithEmail.email;
        document.querySelector('[atas="popup__share__form_message-textarea"]').textContent = data.text;
        return true;
    })();
};

const fillShareForm = data => {
    return (async function () {
        document.querySelector('[atas="popup__share__form_message-input"]').value = data.text;
        return true;
    })();
};
