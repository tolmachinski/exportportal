const click = require("../functions/click");
const variables = require("../variables/variables");
module.exports = async (page, scenario) => {
    const variables = require("../variables/variables");

    await require("../modules/changeData")(page, [
        // Description test
        {
            selectorAll: `[atas="ep-events__event__img"]`,
            value: variables.img(232, 165),
            attr: "src",
            type: "img",
        },
        {
            selectorAll: `[atas="page__events-detail__img-user"]`,
            value: variables.img(42, 42),
            attr: "src",
            type: "img",
        },
        {
            selectorAll: `[atas="page__events-detail__img"]`,
            value: variables.img(858, 373),
            attr: "src",
            type: "img",
        },
        {
            selectorAll: `[atas="page__events-detail__img-gallery"]`,
            value: variables.img(206, 155),
            attr: "src",
            type: "img",
        },
        {
            selectorAll: `[atas="ep-events__event__date"] span`,
            value: variables.dateFormat.withInterval,
        },
        {
            selectorAll: `[atas="page__events-detail__views"]`,
            value: "9999",
        },
        {
            selectorAll: `[atas="ep-events__event__ttl"]`,
            value: variables.name.short,
        },
        {
            selectorAll: `[atas="ep-events__event__country_img"]`,
            value: variables.country.flag,
            attr: "src",
            type: "img",
        },
        {
            selectorAll: `[atas="ep-events__event__country"]`,
            value: variables.country.name,
        },
        {
            selectorAll: `[atas="ep-events__event__place"]`,
            value: "Online",
        },
        {
            selectorAll: `[atas="ep-events__event__speaker"]`,
            value: "21 Savage",
        },
        {
            selectorAll: `[atas="ep-events__event__desc"]`,
            value: variables.lorem(500),
        },
        {
            selectorAll: `[atas="ep-events__past_event__date"]`,
            value: "26-31 Aug, 2022",
        },
    ]);

    await page.waitForFunction(changeEventType);
    await page.waitForFunction(removeTopBanner);
    await page.waitForFunction(changeEventInfo);

    await page.waitForFunction(require('../functions/text'), {}, {
        selectorAll: `[atas="global__sidebar-category"]`,
        text: variables.lorem(45),
    });

    await page.waitForFunction(require('../functions/counter'), {}, {
        selectorAll: `[atas="global__sidebar-counter"]`,
        value: 99999,
    });

    if(scenario.detailPage){
        await page.waitForNetworkIdle();
        await require('../modules/commentsCommon')(page, 1000);
    }
    // Open modal add to calendar
    if(scenario.eventAdd){
        await click(page, `[atas="ep-events__event__calendar_add"]`);
        await page.waitForNetworkIdle();
    }

    // Open modal remove calendar
    if(scenario.eventRemove){
        await click(page, `[atas="ep-events__event__calendar_remove"]`);
        await page.waitForNetworkIdle();
    }

    // Open modal share
    if(scenario.eventShare){
        await click(page, `[atas="ep-events__event__share_btn"]`);
        await page.waitForNetworkIdle();
        await page.waitForTimeout(500);
    }

    // Open menu mobile
    if(scenario.openMenu){
        await page.waitForFunction(openMenu);
        await page.waitForTimeout(500);
    }
}

function openMenu(){
    return (async function(){
        if (document.body.clientWidth < 991) {
            document.querySelector(`[atas="ep-events__filter__btn"]`).click();
        }
        return true;
    })()
}

function removeTopBanner(){
    return (async function(){
        if (document.querySelector(`[atas="page__events__event-banner"]`)) {
            document.querySelector(`[atas="page__events__event-banner"]`).remove();
        }
        return true;
    })()
}

function changeEventInfo(){
    return (async function(){
        document.querySelectorAll('[atas="ep-events__event__info"]').forEach((el, i) => {
            let speaker = el.querySelector('[atas="ep-events__event__speaker"]');
            let country = el.querySelector('[atas="ep-events__event__country"]');
            let labelWrap = el.querySelector('[atas="ep-events__event__label-wrap"]');
            let divElPlace = document.createElement("div");
            let divElLabel = document.createElement("div");
            let aEl = document.createElement("a");

            if (speaker) {
                let speakerParent = speaker.parentNode;
                divElPlace.textContent = "Online";
                divElPlace.classList.add("ep-events__place");
                speakerParent.replaceWith(divElPlace);
            }

            if (country) {
                let countryParent = country.parentNode;
                console.log(countryParent)
                divElPlace.textContent = "Online";
                divElPlace.classList.add("ep-events__place");
                countryParent.replaceWith(divElPlace);
            }

            if (labelWrap && !el.classList.contains("ep-events-past__item")) {
                let labelEl = labelWrap.querySelector("a");

                console.log(labelEl)

                labelEl.textContent = "Recommended";
            }

            if(!labelWrap && !el.classList.contains("ep-events-past__item")) {
                aEl.classList.add("ep-events__label");
                aEl.textContent = "Recommended";
                divElLabel.classList.add("ep-events__labels")
                el.appendChild(divElLabel);
                divElLabel.appendChild(aEl);
            }

        })

        return true;
    })()
}

function changeEventType(){
    return (async function(){
        document.querySelectorAll('[atas="ep-events__event__type"]').forEach((el, i) => {
            let pastEvent = el.querySelector(".ep-events-past__type");
            let activeEvent = el.querySelector(`[atas="ep-events__event__type-el"]`);
            let wasActiveEvent = el.querySelector(`[atas="ep-events__event__type-el-past"]`);
            let spanEl = document.createElement("span");

            if(el.classList.contains("ep-events-past__top")) {
                if(pastEvent) {
                    pastEvent.textContent = "past";
                } else {
                    spanEl.textContent = "past";
                    spanEl.classList.add("ep-events-past__type");
                    el.prepend(spanEl);
                }
            }

            if(el.classList.contains("ep-events__type-wrap")) {
                if(el.childElementCount > 1) {
                    wasActiveEvent.remove();
                }

                if (activeEvent) {
                    activeEvent.textContent = "Webinar";
                }

                if (wasActiveEvent) {
                    wasActiveEvent.textContent = "Webinar";
                    wasActiveEvent.classList.remove("ep-events__type--past")
                }
            }
        })

        return true;
    })()
}
