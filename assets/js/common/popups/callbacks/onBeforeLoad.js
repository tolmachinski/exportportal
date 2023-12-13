import $ from "jquery";

import { calculateModalBoxSizes } from "@src/plugins/fancybox/v2/util";
import callFunction from "@src/util/common/call-function";
import htmlEscape from "@src/util/common/html-escape";

const beforeLoad = function () {
    const classMod = this.element?.data("classModificator");
    if (classMod) {
        this.wrapCSS = `${this.wrapCSS} ${classMod}`;
    }
    /** @type {JQuery} element */
    const element = this?.element;
    const adjustments = calculateModalBoxSizes();
    const that = this;
    const p = adjustments.gutter;

    // Default configs
    this.width = adjustments.width;
    this["padding"] = [p, p, p, p];

    // Rewrite configs
    let dataset = element?.data();
    let dataConfigs = {
        set mw(mw) {
            that["maxWidth"] = mw;
        },
        set w(w) {
            that.width = w;
            that["autoWidth"] = false;
        },
        set h(h) {
            that["height"] = h;
            that["autoHeight"] = false;
        },
        set mh(mh) {
            that["maxHeight"] = mh;
        },
        set mnw(mnw) {
            that["minWidth"] = mnw;
        },
        set mnh(mnh) {
            that["minHeight"] = mnh;
        },
        set title(t) {
            that["title"] = htmlEscape(t) + "&emsp;";
        },
        set titleType(t) {
            that["title"] = t.length ? t : "&emsp;";
        },
        set p(p) {
            this["padding"] = [p, p, p, p];
            if (element.data("p") === 0) {
                that["wrapCSS"] = "fancybox-title--close";
            }
        },
        set beforeCallback(fn) {
            if (fn && callFunction(fn) === false) {
                throw new ReferenceError("The callback must be defined.");
            }
        },
        set dashboardClass(cl) {
            $(".fancybox-inner").addClass(cl);
        },
        set bodyClass(cl) {
            that["bodyClass"] = htmlEscape(cl);
            $("body").addClass(that["bodyClass"]);
        },
        set imageIndex(imageIndex) {
            that["tpl"]["image"] = `<img class="fancybox-image" src="{href}" alt="${dataset["title"]} ${imageIndex}"/>`;
        },
        // TODO: Протестить когда добавиться модалка .fancyboxValidateModalDT
        set table(id) {
            that["ajax"]["data_table_var"] = window[id];
        },
    };

    try {
        if (dataset) {
            for (const key in dataset) {
                if (Object.prototype.hasOwnProperty.call(dataset, key)) {
                    if (key === "titleType") continue;
                    if (key === "title" && !!dataset["titleType"]) {
                        dataConfigs["titleType"] = dataset["title"];
                        continue;
                    }
                    dataConfigs[key] = dataset[key];
                }
            }
        }
    } catch (error) {
        return false;
    }

    if (element) {
        this["ajax"]["caller_btn"] = element;

        // TODO: Протестить когда добавиться модалка .fancyboxValidateModalDT
        if (this["ajax"]["data_table_var"] === undefined) {
            this["ajax"]["data_table_var"] = window[$(element).parents("table").first().attr("id")];
        }
    }
};

export { beforeLoad };
