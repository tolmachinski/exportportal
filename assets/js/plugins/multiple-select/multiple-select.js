import $ from "jquery";

import { translate } from "@src/i18n";
import { showLoader, hideLoader } from "@src/util/common/loader";
import { systemMessages } from "@src/util/system-messages/index";

import "@scss/plug/multiple-select/_multiple_epselect.scss";

// Create the defaults, only once!
const defaults = {
    childCheck: false,
    parentCheck: false,
    allChecked: {},
    industriesRequired: false,
    industriesOnly: false,
    maxIndustriesCount: 0,
    industriesSelectAll: false,
    categoriesSelectedById: false,
    selectCount: 0,
    main: "",
    input_suffix: "",
};

class MultipleEpSelect {
    constructor(element, options) {
        this.element = element;
        this.options = $.extend({}, defaults, options);
        this.init();
    }

    init() {
        const _ = this;
        const el = $(_.element);

        _.options.$selectInputs = el.find(".js-multiple-epselect-inputs");
        _.options.$selectListWr = el.find(".multiple-epselect__list-wr");
        _.options.$selectList = el.find(".multiple-epselect__list");
        _.options.$selectInput = el.find(".multiple-epselect__input");
        _.options.$selectTop = el.find(".multiple-epselect__top");
        _.options.$selectInner = el.find(".multiple-epselect__inner");
        _.options.$selectAllParents = el.find(".multiple-epselect__parent:not(.multiple-epselect__parent--all)");
        _.options.$selectAllParentsData = el.find(".multiple-epselect__parent[data-industry]");
        _.reInitOptions();

        if (_.options.industriesSelectAll) {
            _.options.$selectAll = el.find(".js-multiple-epselect-all");
            _.selectAllInit();
        }

        _.initPlug();
        _.initListiners();
    }

    reInitOptions() {
        const that = this;
        that.options.allChecked = { ...that.options.selected_cat_json };
        that.options.industriesRequired = Boolean(~~parseInt(that.options.industries_required, 10));
        that.options.industriesOnly = Boolean(~~parseInt(that.options.industries_only, 10));
        that.options.maxIndustriesCount = parseInt(that.options.max_industries, 10);
        that.options.industriesSelectAll = Boolean(~~parseInt(that.options.industries_select_all, 10));
        that.options.categoriesSelectedById = Boolean(~~parseInt(that.options.categories_selected_by_id, 10));
        that.options.selectCount = parseInt(that.options.industries_selected, 10);
        that.options.main = that.options.widget_id;
        that.options.inputSuffix = that.options.input_suffix;
    }

    initPlug() {
        const _ = this;
        _.generateInputs();

        if (_.options.industriesRequired) {
            _.setValidateMultipleEpselect();
        }
    }

    initListiners() {
        const _ = this;
        const el = $(_.element);

        el.on("click", ".js-call-check-industry:not(.disabled)", function (e) {
            e.preventDefault();
            const $thisBtn = $(this);
            _.callCheckIndustry($thisBtn);
            _.triggerChangeValidate();
        });

        el.on("click", ".js-call-check-category:not(.disabled)", function (e) {
            e.preventDefault();
            const $thisBtn = $(this);
            _.callCheckCategory($thisBtn);
            _.triggerChangeValidate();
        });

        el.on("click", ".js-call-multiple-toggle-categories:not(.disabled)", function (e) {
            e.preventDefault();
            const $thisBtn = $(this);
            _.multipleToggleCategories($thisBtn);
            _.triggerChangeValidate();
        });

        el.on("click", ".js-call-multiple-select-all-industries:not(.disabled)", function (e) {
            e.preventDefault();
            const $thisBtn = $(this);
            _.multipleSelectAllIndustries($thisBtn);
            _.triggerChangeValidate();
        });

        $("body").on("click", function (e) {
            const $choice = el;

            if ($(e.target)[0] === $choice[0] || $(e.target).parents(".multiple-epselect")[0] === $choice[0]) {
                return;
            }

            if (
                ($(e.target)[0] === _.options.$selectListWr[0] || $(e.target).parents(".multiple-epselect__list-wr")[0] !== _.options.$selectListWr[0]) &&
                _.options.$selectListWr.is(":visible")
            ) {
                _.options.$selectListWr.hide();
            }

            _.triggerChangeValidate();
        });

        _.options.$selectInput.on("click", function () {
            $(this).next(".multiple-epselect__list-wr").toggle();
            _.triggerChangeValidate();
        });
    }

    triggerChangeValidate() {
        const _ = this;
        const el = $(_.element);

        el.val(_.options.$selectInputs.find("input[type=hidden]").length || []);
    }

    generateInputs() {
        const _ = this;
        let inputs = "";

        Object.keys(_.options.allChecked).map(industryKey => {
            const value = _.options.allChecked[industryKey];
            inputs += `<input type="hidden" name="industriesSelected${_.options.inputSuffix}[]" value="${industryKey}">`;

            if (Object.keys(value).length > 0) {
                Object.keys(value).map(catKey => {
                    inputs += `<input type="hidden" name="categoriesSelected${_.options.inputSuffix}[]" value="${catKey}">`;
                    return true;
                });
            }

            return true;
        });

        _.options.$selectInputs.html(inputs);

        _.validateMultipleEpselect();
    }

    setValidateMultipleEpselect() {
        const _ = this;
        const el = $(_.element);
        const valHookName = `multipleIndustryEpselect${_.options.widget_id}`;
        // @ts-ignore
        el.addClass("validate[required]").setValHookType(valHookName);

        $.valHooks[valHookName] = {
            get() {
                return _.options.$selectInputs.find("input[type=hidden]").length || [];
            },
        };
    }

    validateMultipleEpselect() {
        const _ = this;

        if (_.options.$selectInput.hasClass("validengine-border")) {
            _.options.$selectInput.removeClass("validengine-border").prev(".formError").remove();
        }
    }

    multipleToggleCategories($this) {
        const _ = this;
        const $parent = $this.closest(".multiple-epselect__parent");

        const idIndustries = $parent.data("industry");
        const $inner = $parent.find(".multiple-epselect__inner");

        if (!$inner.find("li").length) {
            _.multipleLoadCategories($parent, idIndustries);
        } else {
            $parent.find(".multiple-epselect__toggle").toggleClass("ep-icon_plus-stroke ep-icon_remove-stroke");
            $inner.slideToggle();
        }
    }

    multipleLoadCategories($parent, idIndustries) {
        const _ = this;

        if (_.options.industriesOnly || $parent.hasClass("nochildrens")) {
            return true;
        }

        $.ajax({
            type: "POST",
            url: "directory/ajax_company_category_new",
            dataType: "json",
            data: { industry: idIndustries },
            beforeSend() {
                showLoader($parent.closest(".multiple-epselect__list-wr"), "Loading...");
            },
            success(data) {
                hideLoader($parent.closest(".multiple-epselect__list-wr"));

                if (data.mess_type === "success") {
                    if (data.categories.length) {
                        let categoriesHtml = "";
                        $.each(data.categories, function (key, value) {
                            categoriesHtml += `<li>\
                                <label\
                                    class="js-call-check-category"\
                                    data-callback="callCheckCategory"\
                                    data-category="${value.category_id}"\
                                >\
                                    <span class="pseudo-checkbox"></span>\
                                    <span class="name">${value.name}</span>\
                                </label>\
                            </li>`;
                        });

                        $parent
                            .find(".multiple-epselect__inner")
                            .html(categoriesHtml)
                            .slideToggle()
                            .end()
                            .find(".multiple-epselect__toggle")
                            .toggleClass("ep-icon_plus-stroke ep-icon_remove-stroke");

                        if (!$parent.find(".multiple-epselect__counted").length) {
                            $parent
                                .find(".multiple-epselect__top label")
                                .append(
                                    `<div class="multiple-epselect__counted"> ${_.options.translate_multiple_select_selected_categories} <span class="js-count-cat-selected">0</span>/${data.categories_count}</div>`
                                );
                        }
                    } else {
                        $parent.addClass("nochildrens");
                        systemMessages(translate({ plug: "general_i18n", text: "multiple_select_industry_without_categories_msg" }), "info");
                    }
                } else {
                    systemMessages(data.message, data.mess_type);
                }
            },
        });

        return true;
    }

    multipleSelectAllIndustries($this) {
        const _ = this;
        const $checkbox = $this.find(".pseudo-checkbox");
        const checked = $checkbox.hasClass("checked");

        if (!checked) {
            $checkbox.addClass("checked");

            _.options.$selectAllParentsData.each(function selectAllParentsData() {
                const element = $(this);
                const $checkboxParent = element.find(".pseudo-checkbox");
                const industry = parseInt(element.data("industry"), 10);

                if ($checkboxParent.hasClass("disabled")) {
                    return true;
                }
                if ($checkboxParent.hasClass("checked")) {
                    return true;
                }
                $checkboxParent.addClass("checked");
                _.options.allChecked[industry] = {};

                return true;
            });
        } else {
            $checkbox.removeClass("checked");
            _.options.allChecked = {};

            _.options.$selectAllParentsData.each(function selectAllParentsData() {
                const $checkboxParent = $(this).find(".pseudo-checkbox.checked");

                if ($checkboxParent.length) {
                    $checkboxParent.removeClass("checked");
                }
            });
        }

        _.countAllSelected();
        _.generateInputs();
    }

    callCheckIndustry($this) {
        const _ = this;
        const industry = $this.closest(".multiple-epselect__parent").data("industry");

        if (_.verifyMaxIndustries(industry)) {
            return false;
        }

        if (_.options.allChecked[industry] === undefined) {
            $this.find(".pseudo-checkbox").addClass("checked");
            _.options.allChecked[industry] = {};
        } else {
            $this.find(".pseudo-checkbox").removeClass("checked");
            delete _.options.allChecked[industry];
        }

        if (_.options.industriesSelectAll) {
            _.selectAllInit();
        }

        _.countAllSelected();
        _.generateInputs();

        return true;
    }

    callCheckCategory($this) {
        const _ = this;
        const $parent = $this.closest(".multiple-epselect__parent");
        const industry = $parent.data("industry");
        const category = $this.data("category");

        if (_.verifyMaxIndustries(industry)) {
            return false;
        }

        if (_.options.allChecked[industry] === undefined) {
            _.options.allChecked[industry] = {};
        }

        if (_.options.allChecked[industry][category] === undefined) {
            $this.find(".pseudo-checkbox").addClass("checked");
            _.options.allChecked[industry][category] = category;
        } else {
            $this.find(".pseudo-checkbox").removeClass("checked");
            delete _.options.allChecked[industry][category];

            if (Object.keys(_.options.allChecked[industry]).length === 0) {
                delete _.options.allChecked[industry];
            }
        }

        _.countSelected($parent, industry);
        _.countAllSelected();
        _.generateInputs();

        return true;
    }

    countAllSelected() {
        const _ = this;
        const indutries = Object.keys(_.options.allChecked).length;
        let categories = 0;

        Object.keys(_.options.allChecked).map(objectKey => {
            const value = _.options.allChecked[objectKey];
            categories += Object.keys(value).length;

            return true;
        });

        if (_.options.industriesOnly) {
            _.options.$selectInput.text(
                translate({ plug: "general_i18n", text: "multiple_select_count_selected_industries_placeholder", replaces: { "{{COUNT}}": indutries } })
            );
        } else {
            _.options.$selectInput.text(
                translate({
                    plug: "general_i18n",
                    text: "multiple_select_select_industries_and_categories_count_placeholder",
                    replaces: { "{{COUNT_C}}": categories, "{{COUNT_I}}": indutries },
                })
            );
        }
    }

    verifyMaxIndustries(industry) {
        const _ = this;
        const indutries = Object.keys(_.options.allChecked).length;

        if (_.options.allChecked[industry] === undefined && _.options.maxIndustriesCount > 0 && indutries === _.options.maxIndustriesCount) {
            systemMessages(
                translate({ plug: "general_i18n", text: "multiple_select_max_industries", replaces: { "[COUNT]": _.options.maxIndustriesCount } }),
                "warning"
            );

            return true;
        }

        return false;
    }

    selectAllInit() {
        const _ = this;
        let notCheked = 0;

        _.options.$selectAllParents.each(function selectAllParents() {
            const $checkbox = $(this).find(".pseudo-checkbox");

            if ($checkbox.hasClass("disabled")) {
                return true;
            }
            if ($checkbox.hasClass("checked")) {
                return true;
            }
            notCheked += 1;

            return true;
        });

        if (notCheked === 0) {
            _.options.$selectAll.find(".pseudo-checkbox").addClass("checked");
        } else {
            _.options.$selectAll.find(".pseudo-checkbox").removeClass("checked");
        }
    }

    countSelected($parent, industry) {
        const _ = this;
        let categoriesSelected = 0;
        if (_.options.allChecked[industry] !== undefined) {
            categoriesSelected = Object.keys(_.options.allChecked[industry]).length;
        }
        $parent.find(".multiple-epselect__top .js-count-cat-selected").text(categoriesSelected);
        if (categoriesSelected > 0 && !$parent.hasClass("checked")) {
            $parent.addClass("checked");
        } else if (categoriesSelected === 0 && $parent.hasClass("checked")) {
            $parent.removeClass("checked");
        }
    }
}

export default () => {
    $.fn.extend({
        multipleEpSelect(options) {
            // @ts-ignore
            return this.each(function () {
                if (!$.data(this, "multipleEpSelect")) {
                    $.data(this, "multipleEpSelect", new MultipleEpSelect(this, options));
                }
            });
        },
    });
};
