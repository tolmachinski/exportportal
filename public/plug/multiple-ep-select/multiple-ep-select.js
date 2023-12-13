(function ($, undefined) {
    "use strict";

    // Create the defaults, only once!
    var defaults = {
        childCheck: false,
        parentCheck: false,
        allChecked: new Object(),
        industriesRequired: false,
        industriesOnly: false,
        maxIndustriesCount: 0,
        industriesSelectAll: false,
        categoriesSelectedById: false,
        selectCount: 0,
        main: "",
        input_suffix: "",
    };

    function multipleEpSelect(element, options) {
        this.element = element;
        this.options = $.extend({}, defaults, options);
        this.init();
    }

    multipleEpSelect.prototype.init = function () {
        var _ = this;
        var el = $(_.element);

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
    };

    multipleEpSelect.prototype.reInitOptions = function () {
        var _ = this;
        _.options.allChecked = new Object(_.options.selected_cat_json);
        _.options.industriesRequired = Boolean(~~parseInt(_.options.industries_required, 10));
        _.options.industriesOnly = Boolean(~~parseInt(_.options.industries_only, 10));
        _.options.maxIndustriesCount = parseInt(_.options.max_industries, 10);
        _.options.industriesSelectAll = Boolean(~~parseInt(_.options.industries_select_all, 10));
        _.options.categoriesSelectedById = Boolean(~~parseInt(_.options.categories_selected_by_id, 10));
        _.options.selectCount = parseInt(_.options.industries_selected, 10);
        _.options.main = _.options.widget_id;
        _.options.inputSuffix = _.options.input_suffix;

        // console.log(_.options);
    };

    multipleEpSelect.prototype.initPlug = function () {
        var _ = this;
        _.generateInputs();

        if (_.options.industriesRequired) {
            _.setValidateMultipleEpselect();
        }
    };

    multipleEpSelect.prototype.initListiners = function () {
        var _ = this;
        var el = $(_.element);

        el.on("click", ".js-call-check-industry:not(.disabled)", function (e) {
            e.preventDefault();
            var $thisBtn = $(this);
            _.callCheckIndustry($thisBtn);
        });

        el.on("click", ".js-call-check-category:not(.disabled)", function (e) {
            e.preventDefault();
            var $thisBtn = $(this);
            _.callCheckCategory($thisBtn);
        });

        el.on("click", ".js-call-multiple-toggle-categories:not(.disabled)", function (e) {
            e.preventDefault();
            var $thisBtn = $(this);
            _.multipleToggleCategories($thisBtn);
        });

        el.on("click", ".js-call-multiple-select-all-industries:not(.disabled)", function (e) {
            e.preventDefault();
            var $thisBtn = $(this);
            _.multipleSelectAllIndustries($thisBtn);
        });

        $("body").on("click", function (e) {
            var $choice = el;

            if ($(e.target)[0] === $choice[0] || $(e.target).parents(".multiple-epselect")[0] === $choice[0]) {
                // console.log('body');
                return;
            }

            if (
                ($(e.target)[0] === _.options.$selectListWr[0] || $(e.target).parents(".multiple-epselect__list-wr")[0] !== _.options.$selectListWr[0]) &&
                _.options.$selectListWr.is(":visible")
            ) {
                // console.log('body2');
                _.options.$selectListWr.hide();
            }
        });

        _.options.$selectInput.on("click", function (e) {
            // console.log('click-multiple2');
            $(this).next(".multiple-epselect__list-wr").toggle();
        });
    };

    multipleEpSelect.prototype.generateInputs = function () {
        var _ = this;
        var inputs = "";

        Object.keys(_.options.allChecked).map(function (industryKey, industryIndex) {
            var value = _.options.allChecked[industryKey];
            inputs += '<input type="hidden" name="industriesSelected' + _.options.inputSuffix + '[]" value="' + industryKey + '">';

            if (Object.keys(value).length > 0) {
                Object.keys(value).map(function (catKey, catIndex) {
                    inputs += '<input type="hidden" name="categoriesSelected' + _.options.inputSuffix + '[]" value="' + catKey + '">';
                });
            }
        });

        _.options.$selectInputs.html(inputs);

        _.validateMultipleEpselect();
    };

    multipleEpSelect.prototype.setValidateMultipleEpselect = function ($this) {
        var _ = this;
        var el = $(_.element);
        var valHookName = "multipleIndustryEpselect" + _.options.widget_id;

        el.addClass("validate[required]").setValHookType(valHookName);

        $.valHooks[valHookName] = {
            get: function (el) {
                return _.options.$selectInputs.find("input[type=hidden]").length || [];
            },
        };
    };

    multipleEpSelect.prototype.validateMultipleEpselect = function ($this) {
        var _ = this;

        if (_.options.$selectInput.hasClass("validengine-border")) {
            _.options.$selectInput.removeClass("validengine-border").prev(".formError").remove();
        }
    };

    multipleEpSelect.prototype.multipleToggleCategories = function ($this) {
        var _ = this;
        var $parent = $this.closest(".multiple-epselect__parent");

        var idIndustries = $parent.data("industry");
        var $inner = $parent.find(".multiple-epselect__inner");

        if (!$inner.find("li").length) {
            _.multipleLoadCategories($parent, idIndustries);
        } else {
            $parent.find(".multiple-epselect__toggle").toggleClass("ep-icon_plus-stroke ep-icon_remove-stroke");
            $inner.slideToggle();
        }
    };

    multipleEpSelect.prototype.multipleLoadCategories = function ($parent, idIndustries) {
        var _ = this;

        if (_.options.industriesOnly || $parent.hasClass("nochildrens")) {
            return true;
        }

        $.ajax({
            type: "POST",
            url: "directory/ajax_company_category_new",
            dataType: "json",
            data: { industry: idIndustries },
            beforeSend: function () {
                showLoader($parent.closest(".multiple-epselect__list-wr"), "Loading...");
            },
            success: function (data) {
                hideLoader($parent.closest(".multiple-epselect__list-wr"), "Loading...");

                if (data.mess_type == "success") {
                    if (data.categories.length) {
                        var categoriesHtml = "";
                        $.each(data.categories, function (key, value) {
                            categoriesHtml +=
                                '<li>\
                                    <label\
                                        class="js-call-check-category"\
                                        data-callback="callCheckCategory"\
                                        data-category="' +
                                value.category_id +
                                '"\
                                    >\
                                        <span class="pseudo-checkbox"></span>\
                                        <span class="name">' +
                                value.name +
                                "</span>\
                                    </label>\
                                </li>";
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
                                    '<div class="multiple-epselect__counted"> ' +
                                        _.options.translate_multiple_select_selected_categories +
                                        ' <span class="js-count-cat-selected">0</span>/' +
                                        data.categories_count +
                                        "</div>"
                                );
                        }
                    } else {
                        $parent.addClass("nochildrens");
                        systemMessages(translate_js({ plug: "general_i18n", text: "multiple_select_industry_without_categories_msg" }), "info");
                    }
                } else {
                    systemMessages(data.message, data.mess_type);
                }
            },
        });
    };

    multipleEpSelect.prototype.multipleSelectAllIndustries = function ($this) {
        var _ = this;
        var $checkbox = $this.find(".pseudo-checkbox");
        var checked = $checkbox.hasClass("checked");

        if (!checked) {
            $checkbox.addClass("checked");

            _.options.$selectAllParentsData.each(function () {
                var $this = $(this);
                var $checkboxParent = $this.find(".pseudo-checkbox");
                var industry = parseInt($this.data("industry"));

                if ($checkboxParent.hasClass("disabled")) {
                    return true;
                } else if ($checkboxParent.hasClass("checked")) {
                    return true;
                } else {
                    $checkboxParent.addClass("checked");
                    _.options.allChecked[industry] = new Object();
                }
            });
        } else {
            $checkbox.removeClass("checked");
            _.options.allChecked = new Object();

            _.options.$selectAllParentsData.each(function () {
                var $this = $(this);
                var $checkboxParent = $this.find(".pseudo-checkbox.checked");
                var industry = parseInt($this.data("industry"));

                if ($checkboxParent.length) {
                    $checkboxParent.removeClass("checked");
                }
            });
        }

        _.countAllSelected();
        _.generateInputs();
    };

    multipleEpSelect.prototype.callCheckIndustry = function ($this) {
        var _ = this;
        // console.log(_.options);
        var industry = $this.closest(".multiple-epselect__parent").data("industry");

        if (_.verifyMaxIndustries(industry)) {
            return false;
        }

        if (_.options.allChecked[industry] == undefined) {
            $this.find(".pseudo-checkbox").addClass("checked");
            _.options.allChecked[industry] = new Object();
        } else {
            $this.find(".pseudo-checkbox").removeClass("checked");
            delete _.options.allChecked[industry];
        }

        if (_.options.industriesSelectAll) {
            _.selectAllInit();
        }

        _.countAllSelected();
        _.generateInputs();
    };

    multipleEpSelect.prototype.callCheckCategory = function ($this) {
        var _ = this;
        var $parent = $this.closest(".multiple-epselect__parent");
        var industry = $parent.data("industry");
        var category = $this.data("category");

        if (_.verifyMaxIndustriesByCategories(industry)) {
            return false;
        }

        if (_.options.allChecked[industry] == undefined) {
            _.options.allChecked[industry] = new Object();
        }

        if (_.options.allChecked[industry][category] == undefined) {
            $this.find(".pseudo-checkbox").addClass("checked");
            _.options.allChecked[industry][category] = category;
        } else {
            $this.find(".pseudo-checkbox").removeClass("checked");
            delete _.options.allChecked[industry][category];

            if (Object.keys(_.options.allChecked[industry]).length == 0) {
                delete _.options.allChecked[industry];
            }
        }

        _.countSelected($parent, industry);
        _.countAllSelected();
        _.generateInputs();
    };

    multipleEpSelect.prototype.countAllSelected = function ($this) {
        var _ = this;
        var indutries = Object.keys(_.options.allChecked).length,
            categories = 0;

        Object.keys(_.options.allChecked).map(function (objectKey, index) {
            var value = _.options.allChecked[objectKey];
            categories += Object.keys(value).length;
        });

        if (_.options.industriesOnly) {
            _.options.$selectInput.text(
                translate_js({ plug: "general_i18n", text: "multiple_select_count_selected_industries_placeholder", replaces: { "{{COUNT}}": indutries } })
            );
        } else {
            var key = "multiple_select_select_industries_and_categories_count_placeholder";
            if(indutries === 1 && categories !== 1){
                key = "multiple_select_select_industry_and_categories_count_placeholder";
            }
            if(indutries === 1 && categories === 1){
                key = "multiple_select_select_industry_and_category_count_placeholder";
            }
            _.options.$selectInput.text(
                translate_js({
                    plug: "general_i18n",
                    text: key,
                    replaces: { "{{COUNT_C}}": categories, "{{COUNT_I}}": indutries },
                })
            );
        }
    };

    multipleEpSelect.prototype.verifyMaxIndustries = function (industry) {
        var _ = this;
        var indutries = Object.keys(_.options.allChecked).length;

        if (_.options.allChecked[industry] == undefined && _.options.maxIndustriesCount > 0 && indutries == _.options.maxIndustriesCount) {
            systemMessages(
                translate_js({ plug: "general_i18n", text: "multiple_select_max_industries", replaces: { "[COUNT]": _.options.maxIndustriesCount } }),
                "warning"
            );

            return true;
        }

        return false;
    };

    multipleEpSelect.prototype.verifyMaxIndustriesByCategories = function (industry) {
        var _ = this;
        var indutries = Object.keys(_.options.allChecked).length;

        if (_.options.allChecked[industry] == undefined && _.options.maxIndustriesCount > 0 && indutries == _.options.maxIndustriesCount) {
            systemMessages(
                translate_js({ plug: "general_i18n", text: "multiple_select_max_industries_by_categories", replaces: { "[COUNT]": _.options.maxIndustriesCount } }),
                "warning"
            );

            return true;
        }

        return false;
    };

    multipleEpSelect.prototype.selectAllInit = function ($this) {
        var _ = this;
        var notCheked = 0;

        _.options.$selectAllParents.each(function () {
            var $this = $(this);
            var $checkbox = $this.find(".pseudo-checkbox");

            if ($checkbox.hasClass("disabled")) {
                return true;
            } else if ($checkbox.hasClass("checked")) {
                return true;
            } else {
                notCheked++;
            }
        });

        // console.log(notCheked);
        if (notCheked == 0) {
            _.options.$selectAll.find(".pseudo-checkbox").addClass("checked");
        } else {
            _.options.$selectAll.find(".pseudo-checkbox").removeClass("checked");
        }
    };

    multipleEpSelect.prototype.countSelected = function ($parent, industry) {
        var _ = this;
        var categoriesSelected = 0;
        if (_.options.allChecked[industry] != undefined) {
            categoriesSelected = Object.keys(_.options.allChecked[industry]).length;
        }
        $parent.find(".multiple-epselect__top .js-count-cat-selected").text(categoriesSelected);
        if (categoriesSelected > 0 && !$parent.hasClass("checked")) {
            $parent.addClass("checked");
        } else if (categoriesSelected == 0 && $parent.hasClass("checked")) {
            $parent.removeClass("checked");
        }
    };

    $.fn.extend({
        multipleEpSelect: function (options) {
            return this.each(function () {
                if (!$.data(this, "multipleEpSelect")) {
                    $.data(this, "multipleEpSelect", new multipleEpSelect(this, options));
                }
            });
        },
    });
})(jQuery);
