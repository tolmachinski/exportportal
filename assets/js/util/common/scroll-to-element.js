import $ from "jquery";
import callFunction from "@src/util/common/call-function";

const scrollToElement = function (element, minus = 0, speed = 2000, callbackName = "", mainClasses = "html, body", typePosition = "offset") {
    const minusHeight = minus === -1 ? $("#js-ep-header-fixed-top").height() : minus;
    const $element = $(element);
    let calc = 0;

    if (typePosition === "offset") {
        calc = $element.offset().top - minusHeight;
    } else {
        calc = $element.position().top - minusHeight;
    }

    $(mainClasses)
        .animate(
            {
                scrollTop: calc,
            },
            speed
        )
        .promise()
        .then(() => {
            if (callbackName) {
                callFunction(callbackName, $element);
            }
        });
};

export default scrollToElement;
