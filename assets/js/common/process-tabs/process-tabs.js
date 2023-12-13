import $ from "jquery";

const initProcessTabs = function (selector) {
    let indexPrev;
    const tabsElements = {};

    $(selector)
        .find(".tabs-circle__item:visible")
        .each(function () {
            const $this = $(this);
            const $link = $this.find(".link");
            const $point = $this.find(".tabs-circle__point");
            const $delimeter = $this.find(".delimeter");
            const element = {};
            element.index = $this.index();
            element.width = $this.outerWidth();
            element.left = $this.position().left;
            element.leftTotal = element.left + element.width;
            element.link = {};
            element.link.width = $point.outerWidth();
            element.link.left = $point.position().left;
            let progress = "";

            if ($this.hasClass("complete") || $link.hasClass("active") || $this.hasClass("additional")) {
                progress = " progress";
            } else if ($delimeter.length && $delimeter.hasClass("progress")) {
                progress = " progress";
            }

            if (tabsElements[indexPrev] !== undefined) {
                const prevElement = tabsElements[indexPrev];
                const delimeter = {};
                delimeter.plusElementWidth = (element.width - element.link.width) / 2;
                delimeter.plusAllWidth = delimeter.plusElementWidth + (prevElement.width - prevElement.link.width) / 2;
                delimeter.width = element.left + delimeter.plusAllWidth - prevElement.leftTotal;
                delimeter.minusPosition = delimeter.width - delimeter.plusElementWidth;
                $this.find(".delimeter").remove();
                $this.append(`<div class="delimeter${progress}" style="width: ${delimeter.width}px; left: -${delimeter.minusPosition}px;"></div>`);
            }

            indexPrev = element.index;
            tabsElements[element.index] = element;
        });
};

export default initProcessTabs;
