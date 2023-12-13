import { SITE_URL } from "@src/common/constants";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";

const setPopupPosition = (popupNode, event) => {
    let left = event.pageX;
    let top = event.pageY + 10;
    let unitsOfMeasurement = "px";
    let transform = "none";
    if (document.documentElement.clientWidth - event.clientX < popupNode.offsetWidth) {
        left = document.documentElement.clientWidth - popupNode.offsetWidth;
    }
    if (document.documentElement.clientHeight - event.clientY < popupNode.offsetHeight) {
        top = document.documentElement.clientHeight - popupNode.offsetHeight;
    }
    if (globalThis.matchMedia("(max-width: 767px)").matches) {
        left = 50;
        top = 50;
        transform = "translate(-50%, -50%)";
        unitsOfMeasurement = "%";
    }
    popupNode.setAttribute(
        "style",
        `
            top: ${top}${unitsOfMeasurement};
            left: ${left}${unitsOfMeasurement};
            transform: ${transform}
        `
    );
};

const setOverlayOnMobile = () => {
    const overlay = document.querySelector(".js-calendar-overlay");
    const { body } = document;
    if (!overlay && globalThis.matchMedia("(max-width: 767px)").matches) {
        body.classList.add("overflow");
        document.body.insertAdjacentHTML(
            "beforeend",
            `<div class="calendar-info-popup__overlay js-calendar-overlay call-action" data-element="overlay" data-js-action="calendar-info:close"></div>`
        );
    }
};

const eventMap = new Map();

const renderEventPopup = async (eventId, event) => {
    let detailInfoWrapper = "";
    const eventCard = document.querySelectorAll(`[data-js-action="calendar-event:click"]`);

    eventCard.forEach(element => {
        const { style } = element;
        style["pointer-events"] = "none";
    });

    if (eventMap.has(eventId)) {
        console.log(1)
        const elements = document.getElementsByClassName("js-calendar-info-popup-wrapper");
        while (elements.length > 0) {
            elements[0].parentNode.removeChild(elements[0]);
        }

        detailInfoWrapper = `
             <div class="js-calendar-info-popup-wrapper js-calendar-popup calendar-info-popup-wrapper" style="visibility: hidden">
                ${eventMap.get(eventId)}
             </div>
        `;
    } else {
        console.log(2)
        try {
            const { content } = await postRequest(`${SITE_URL}calendar/get_calendar_event_details/${eventId}`);
            if (!content) {
                return false;
            }
            eventMap.set(eventId, content);

            detailInfoWrapper = `
                 <div class="js-calendar-info-popup-wrapper js-calendar-popup calendar-info-popup-wrapper" style="visibility: hidden">
                    ${content}
                 </div>
            `;
        } catch (error) {
            handleRequestError(error);
        }
    }

    setOverlayOnMobile();
    document.body.insertAdjacentHTML("beforeend", detailInfoWrapper);
    setPopupPosition(document.querySelector(".js-calendar-info-popup-wrapper"), event);

    eventCard.forEach(element => {
        const { style } = element;
        style["pointer-events"] = "initial";
    });

    return true;
};

export { renderEventPopup, setPopupPosition, setOverlayOnMobile };
