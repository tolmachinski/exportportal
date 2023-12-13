const variables = require("../variables/variables");
module.exports = async (page, scenario) => {
    const variables = require("../variables/variables");
    const data = {
        detailedInfo: {
            title: variables.lorem(200),
            description: variables.lorem(2000),
            date: variables.dateFormat.withTime,
            img: variables.img(512, 512),
            link: variables.lorem(100),
        },
        sidebarInfo: {
            title: variables.lorem(200),
            date: variables.dateFormat.withTime,
            link: variables.lorem(100),
            imgSmall: variables.img(36, 36),
        },
    };

    await page.waitForFunction(replaceDetailedInfo, {}, data.detailedInfo);
    await page.waitForFunction(replaceSidebarInfo, {}, data.sidebarInfo);
    await require("../modules/massMedia")(page, scenario);

    if (scenario.withoutMainImage) {
        await page.waitForFunction(removeMainImage);
    }
}

function replaceDetailedInfo(data) {
    return (async () => {
        const textElements = {
            title: document.querySelector(`[atas="mass-media-detail__title"]`),
            description: document.querySelector(`[atas="mass-media-detail__description"]`),
            link: document.querySelector(`[atas="mass-media-detail__link"]`),
            date: document.querySelector(`[atas="mass-media-detail__date"]`),
        };
        const img = document.querySelector(`[atas="mass-media-detail__img"]`);

        Object.keys(textElements).forEach(key => {
            textElements[key].textContent = data[key];
        });
        img.dataset.src = data.img;
        img.src = data.img;

        return true;
    })();
}

function removeMainImage() {
    return (async () => {
        document.querySelector(`[atas="mass-media-detail__img-parent"]`).remove();

        return true;
    })();
}

function replaceSidebarInfo(data) {
    return (async () => {
        const textElements = {
            title: document.querySelectorAll(`[atas="mass-media-detail__sidebar-title"]`),
            date: document.querySelectorAll(`[atas="mass-media-detail__sidebar-date"]`),
            link: document.querySelectorAll(`[atas="mass-media-detail__sidebar-link"]`),
        };

        const imgSmall = document.querySelectorAll(`[atas="mass-media__link-img"]`);

        imgSmall.forEach(item => {
            item.src = data.imgSmall;
        })

        Object.keys(textElements).forEach(key => {
            textElements[key].forEach(item => {
                item.textContent = data[key];
            })
        });

        return true;
    })();
}
