import $ from "jquery";

const DtFilters = function (selector, options = {}) {
    let dtFilters = {}; // main filter collector

    const filterSelector = selector;

    const baseOptions = {
        // default config
        filterContainer: ".filter-admin-panel",
        container: ".dt-filter-list", // active filter container
        txtResetBtn: "Reset",
        txtApplyBtn: "Apply",
        ulClass: "filter-plugin-list",
        liClass: "filter-plugin-element",
        debug: false,
        autoApply: true, // apply filter after each setting, if false will be applied by button
        // eslint-disable-next-line no-unused-vars
        onInit(handler) {
            // call on init
        },
        callBack() {
            // main callback
        },
        // eslint-disable-next-line no-unused-vars
        beforeSet(callerObj) {
            // callback before setting a filter
            // callerObj - object which called the setting operation
        },
        // eslint-disable-next-line no-unused-vars
        onSet(callerObj, filterObj, meta) {
            // callback on setting a filter
            // callerObj - object which called the setting operation
            // filterObj - object which describes the activated filter
            // meta - filter meta information
        },
        // eslint-disable-next-line no-unused-vars
        onDelete(filterObj, field, meta) {
            // callback on deleting a filter
            // filterObj - object which describes the deactivated filter
            // field - latest HTML element for this filter
            // meta - filter meta information
        },
        // eslint-disable-next-line no-unused-vars
        onApply(callerObj) {
            // callerObj - object which called the setting operation
        },
        // eslint-disable-next-line no-unused-vars
        onReset(callerObj) {
            // callerObj - object which called the setting operation
        },
        // eslint-disable-next-line no-unused-vars
        onActive(callerObj) {
            // callerObj - object which called the setting operation
        },
    };

    let dtFilterSettings = { ...baseOptions };

    const methods = {
        // (public) Initialization the filter list
        init() {
            methods.createUl();

            this.each(() => {
                if (typeof dtFilters[this.name] !== "object") {
                    methods.addFilter(this);
                }
            });

            methods.controlButtons();
            methods.addFilterListeners();
            dtFilterSettings.onInit({
                getDTFilter: methods.getFilterDTFormat,
                removeFilter: methods.removeFilter,
                reInit: methods.reInit,
            });
        },

        reInit() {
            dtFilters = {};

            $(filterSelector).each(() => {
                if (typeof dtFilters[this.name] !== "object" && $(this).parents(dtFilterSettings.filterContainer).length) {
                    methods.addFilter(this);
                }
            });
        },

        // (private) Create UL as list for filters
        createUl() {
            const $container = $(dtFilterSettings.container);
            if (!$container.length) {
                throw new Error("Container should be set in settings!");
            }

            $container.html(`<ul class='${dtFilterSettings.ulClass}'></ul>`);
        },

        // (private) Add a item(li) on filter list(ul)
        activateFilter(filter) {
            if (filter.currentValue.value === filter.defaultValue.value) {
                return;
            }

            const $ulFilters = $(`.${dtFilterSettings.ulClass}`);
            let $li = $ulFilters.find(`li.af-${filter.name}`);
            if (!$li.length) {
                $li = $(
                    `<li class="dt-filter__param af-${filter.name}">\
                    <span class="dt-filter__param-name">${filter.filterLabel}: </span>\
                    <span class="dt-filter__param-val">` +
                        `</span> \
                    <a class="dt-filter__param-remove dt-filter-delete ep-icon ep-icon_remove-stroke" data-parent="${filter.name}"></a>\
                </li>`
                );

                $ulFilters.append($li);
            }

            $li.find(".dt-filter__param-val").text(filter.currentValue.text);
        },

        // (private) Remove an item(li) from filter list(ul)
        deactivateFilter(filter) {
            if ($(`li.af-${filter.name}`).length) $(`li.af-${filter.name}`).remove();
        },

        // (private) Buttons 'Reset' and 'Apply'
        controlButtons() {
            const $ul = $(`.${dtFilterSettings.ulClass}`);
            if (!$ul.children("li").length) {
                $ul.siblings().remove();
                return;
            }

            if (!$ul.siblings(".dt-filter-reset").length) {
                $ul.parent().append(`<a class="dt-filter-reset dt-filter-reset-buttons btn btn-light">${dtFilterSettings.txtResetBtn}</a>`);
            }

            if (!dtFilterSettings.autoApply && !$ul.siblings(".dt-filter-apply").length) {
                $ul.parent().prepend(`<a class="dt-filter-apply dt-filter-apply-buttons">${dtFilterSettings.txtApplyBtn}</a>`);
            }
        },

        // (private) Append class to buttons
        addClassToButtons(buttonClass, className) {
            $(`.${buttonClass}`).addClass(className);
        },

        // (private) Append class to buttons
        removeClassFromButtons(buttonClass, className) {
            $(`.${buttonClass}`).removeClass(className);
        },
        // (private) Add a filter to the filter collector
        addFilter(obj) {
            let idFilter;
            if (obj.name !== "" && obj.name !== undefined) {
                idFilter = obj.name;
            } else if ($(obj).attr("data-name") !== "") {
                idFilter = $(obj).attr("data-name");
            } else {
                throw new Error('Object should have "name" or "data-name" attribute');
            }

            if (typeof dtFilters[idFilter] === "object") return;

            dtFilters[idFilter] = {};
            dtFilters[idFilter].jqObj = $(obj);
            if (obj.name) {
                dtFilters[idFilter].name = obj.name;
            } else {
                dtFilters[idFilter].name = dtFilters[idFilter].jqObj.attr("data-name");
            }

            dtFilters[idFilter].filterLabel = dtFilters[idFilter].jqObj.attr("data-title");
            dtFilters[idFilter].tagName = obj.tagName;

            switch (obj.tagName) {
                case "INPUT":
                    dtFilters[idFilter].type = dtFilters[idFilter].jqObj.prop("type");

                    break;
                case "SELECT":
                    dtFilters[idFilter].type = "select";

                    break;
                case "A":
                    dtFilters[idFilter].type = "button";

                    break;
                default:
                // do nothing
            }

            dtFilters[idFilter].defaultValue = methods.getFilterDefaultVal(dtFilters[idFilter]);
            dtFilters[idFilter].currentValue = methods.getFilterVal(dtFilters[idFilter]);
            methods.activateFilter(dtFilters[idFilter]);
        },

        // (public) Remove an active filter
        removeFilter(name, emitCallack) {
            const doEmitCallack = typeof emitCallack !== "undefined" ? emitCallack : true;
            methods.toDefault(dtFilters[name]);
            methods.deactivateFilter(dtFilters[name]);
            if (doEmitCallack) {
                methods.callCallback();
            }
        },

        // (private) Remove one or all filters from
        clearActiveFilters(filterName) {
            let activeFilters = {};

            if (filterName) {
                activeFilters[filterName] = dtFilters[filterName];
            } else {
                activeFilters = methods.getActiveFilters();
            }

            // eslint-disable-next-line no-restricted-syntax
            for (const key in activeFilters) {
                if (Object.prototype.hasOwnProperty.call(activeFilters, key)) {
                    methods.deactivateFilter(activeFilters[key]);
                    methods.toDefault(activeFilters[key]);

                    dtFilterSettings.onDelete(
                        methods.getFilter(activeFilters[key]),
                        activeFilters[key].jqObj ? activeFilters[key].jqObj.get(0) : null,
                        activeFilters[key]
                    );
                }
            }

            if (dtFilterSettings.autoApply || !$(`.${dtFilterSettings.ulClass}`).children("li").length) {
                methods.callCallback(true);
            }

            methods.removeClassFromButtons("dt-filter-apply-buttons", "active");
            methods.controlButtons();
        },

        // (private) Handler with value of the filter on setting operation
        proccessingFilter(obj, newValue, valueText) {
            let idFilter;
            if (obj.name !== "" && obj.name !== undefined) {
                idFilter = obj.name;
            } else if ($(obj).attr("data-name") !== "") {
                idFilter = $(obj).attr("data-name");
            } else {
                throw new Error('Object should have "name" or "data-name" attribute');
            }

            if (dtFilters[idFilter]) {
                const filter = dtFilters[idFilter];
                if (filter.currentValue.value === newValue) {
                    return;
                }

                methods.updateFilter(filter, newValue, valueText);

                filter.currentValue.text = valueText;
                filter.currentValue.value = newValue;

                if (filter.currentValue.value === filter.defaultValue.value /* || filter.currentValue.value == "" */) {
                    methods.deactivateFilter(filter);
                    if (!$(`.${dtFilterSettings.ulClass}`).children("li").length) {
                        methods.callCallback(true);
                    }
                } else methods.activateFilter(filter);
            } else {
                methods.addFilter(obj);
                dtFilters[idFilter].independent = true;
            }

            methods.callCallback();

            methods.removeClassFromButtons("dt-filter-apply-buttons", "active");

            dtFilterSettings.onSet(obj, methods.getFilter(dtFilters[idFilter]), dtFilters[idFilter]);

            methods.controlButtons();
        },

        // (private) Handler with value of the filter on updating operation
        updateFilter(filter, newValue, valueText) {
            const currentFilter = filter;
            if (currentFilter.currentValue.value === newValue) {
                return;
            }

            switch (currentFilter.type) {
                case "select":
                    currentFilter.currentValue.value = newValue;

                    if (currentFilter.jqObj.attr("multiple") !== undefined) {
                        /** @type {string[]} */
                        const values = newValue.split(",");
                        const texts = [];

                        values.forEach(v => {
                            currentFilter.jqObj.children(`option[value="${v}"]`).prop("selected", true);
                            texts.push(currentFilter.jqObj.children(`option[value="${v}"]`).text());
                        });

                        if (texts.length) {
                            currentFilter.currentValue.text = texts.join(",");
                        } else {
                            currentFilter.currentValue.value = newValue;
                        }
                    } else {
                        const $selectedOption = filter.jqObj.children(`option[value="${filter.currentValue.value}"]`).first();
                        $selectedOption.prop("selected", true);
                        currentFilter.currentValue.text = $selectedOption.text();
                    }

                    break;
                case "radio":
                    // eslint-disable-next-line no-case-declarations
                    const $checkedRadio = $(`input[name="${currentFilter.name}"][type="radio"][value="${currentFilter.currentValue.value}"]${filterSelector}`);

                    currentFilter.currentValue.value = newValue;
                    $checkedRadio.prop("checked", true);
                    if ($checkedRadio.attr("data-value-text") === undefined) {
                        throw new Error('Radio should have attribute "data-value-text" ');
                    }

                    currentFilter.currentValue.text = $checkedRadio.attr("data-value-text");

                    break;
                case "tel":
                case "url":
                case "week":
                case "date":
                case "text":
                case "time":
                case "email":
                case "range":
                case "color":
                case "month":
                case "search":
                case "number":
                case "password":
                case "datetime":
                case "datetime-local":
                    currentFilter.currentValue.value = newValue;
                    currentFilter.jqObj.val(currentFilter.currentValue.value);
                    currentFilter.currentValue.text = newValue;
                    break;
                case "hidden":
                    currentFilter.currentValue.value = newValue;
                    currentFilter.jqObj.val(currentFilter.currentValue.value);
                    if (valueText === undefined) {
                        currentFilter.currentValue.text = newValue;
                    } else {
                        currentFilter.currentValue.text = valueText;
                    }

                    break;
                case "checkbox":
                    /** @type {string[]} */
                    // eslint-disable-next-line no-case-declarations
                    const values = newValue.split(",");
                    // eslint-disable-next-line no-case-declarations
                    const texts = [];

                    values.forEach(v => {
                        const element = $(`input[name="${currentFilter.name}"][type="checkbox"][value="${values[v]}"]`);

                        element.prop("checked", true);
                        texts.push(element.data("value-text"));
                    });

                    currentFilter.currentValue.value = newValue;

                    if (texts.length) {
                        currentFilter.currentValue.text = texts.join(",");
                    } else {
                        currentFilter.currentValue.value = newValue;
                    }

                    break;
                case "button":
                    currentFilter.currentValue.value = newValue;
                    if (valueText === undefined) throw new Error('Button should have attribute "data-value-text" ');

                    currentFilter.currentValue.text = valueText;
                    currentFilter.currentValue.value = newValue;

                    break;
                default:
                    break;
            }
            methods.activateFilter(currentFilter);
        },

        // (private) Set a filter to default value
        toDefault(filter) {
            const currentFilter = filter;

            switch (currentFilter.type) {
                case "select":
                    // eslint-disable-next-line no-case-declarations
                    let defaultElement = currentFilter.jqObj.children('option[data-default="true"]');
                    if (typeof defaultElement === "undefined") {
                        defaultElement = currentFilter.jqObj.children(`option[value="${currentFilter.defaultValue.value}"]`);
                    }

                    defaultElement.prop("selected", true);
                    currentFilter.currentValue = $.extend({}, currentFilter.defaultValue);

                    break;
                case "radio":
                    $(`input[name="${currentFilter.name}"][type="radio"][value="${currentFilter.defaultValue.value}"]${filterSelector}`).prop("checked", true);
                    currentFilter.currentValue = $.extend({}, currentFilter.defaultValue);

                    break;
                case "tel":
                case "url":
                case "week":
                case "date":
                case "text":
                case "time":
                case "email":
                case "range":
                case "color":
                case "month":
                case "search":
                case "number":
                case "password":
                case "datetime":
                case "datetime-local":
                    currentFilter.jqObj.val(currentFilter.defaultValue.value);
                    currentFilter.currentValue = $.extend({}, currentFilter.defaultValue);

                    break;
                case "hidden":
                    currentFilter.jqObj.val(currentFilter.defaultValue.value);
                    currentFilter.currentValue = $.extend({}, currentFilter.defaultValue);

                    break;
                case "checkbox":
                    $(`input[name="${currentFilter.name}"][type="checkbox"]${filterSelector}`).prop("checked", false);
                    currentFilter.currentValue = $.extend({}, currentFilter.defaultValue);

                    break;
                case "button":
                    if (dtFilters[currentFilter.name].independent) {
                        delete dtFilters[currentFilter.name];
                    } else {
                        currentFilter.currentValue = $.extend({}, currentFilter.defaultValue);
                    }

                    break;
                default:
                    break;
            }
        },

        // (private) Add listeners for all elements
        addFilterListeners() {
            methods.addListnersSelect();

            methods.addListnersRadio();

            methods.addListnersText();

            methods.addListnersHidden();

            methods.addListnersCheckbox();

            methods.addListnersA();

            methods.addListnersX();

            methods.addListnersReset();

            methods.addListnersApply();
        },

        // (private) Add listener for SELECT
        addListnersSelect() {
            $("body").on("change", `select${filterSelector}`, () => {
                const jqObj = $(this);

                dtFilterSettings.beforeSet(jqObj);
                let newValue;
                let newText;

                if ($(this).attr("multiple") !== undefined) {
                    const texts = [];
                    const values = [];

                    $("option:selected", this).each(() => {
                        let valText = jqObj.data("value-text");

                        if (valText === undefined) valText = this.text;

                        texts.push(valText);
                        values.push(this.value);
                    });
                    newText = texts.join(", ");
                    newValue = values.join(",");
                } else {
                    const $optionSelected = $("option:selected", this);
                    let valText = $optionSelected.data("value-text");

                    if (valText === undefined) valText = $optionSelected.text();

                    newText = valText;
                    newValue = jqObj.val();
                }
                methods.proccessingFilter(this, newValue, newText);
            });
        },

        // (private) Add listener for ETXT
        addListnersText() {
            const handler = function () {
                const jqObj = $(this);
                const newValue = jqObj.val();
                const newText = jqObj.val();

                dtFilterSettings.beforeSet(jqObj);
                methods.proccessingFilter(this, newValue, newText);
            };

            // Add same handler for any type inherited from 'text' such as 'email', 'number' etc.
            [
                "tel",
                "url",
                "week",
                "date",
                "text",
                "time",
                "email",
                "range",
                "color",
                "month",
                "search",
                "number",
                "password",
                "datetime",
                "datetime-local",
            ].forEach(type => {
                $("body").on("change", `input[type="${type}"]${filterSelector}`, handler);
            });
        },

        // (private) Add listener for ETXT
        addListnersHidden() {
            $("body").on("change", `input[type="hidden"]${filterSelector}`, () => {
                const jqObj = $(this);
                dtFilterSettings.beforeSet(jqObj);
                const newValue = jqObj.val();
                const newText = jqObj.val();
                methods.proccessingFilter(this, newValue, newText);
            });
        },

        // (private) Add listener for CHECKBOX
        addListnersCheckbox() {
            $("body").on("click", `input[type="checkbox"]${filterSelector}`, () => {
                const jqObj = $(this);
                const texts = [];
                const values = [];

                dtFilterSettings.beforeSet(jqObj);

                $(`input[type="checkbox"][name="${this.name}"]:checked`).each(() => {
                    // eslint-disable-next-line consistent-this
                    const that = $(this);

                    if (that.attr("data-value-text") === undefined) throw new Error('Checkbox should have attribute "data-value-text"');

                    texts.push(that.attr("data-value-text"));
                    values.push(that.attr("value"));
                });

                const newText = texts.join(", ");
                const newValue = values.join(",");

                methods.proccessingFilter(this, newValue, newText);
            });
        },

        // (private) Add listener for RADIO
        addListnersRadio() {
            $("body").on("click", `input[type="radio"]${filterSelector}`, () => {
                const jqObj = $(this);
                dtFilterSettings.beforeSet(jqObj);

                if (jqObj.attr("data-value-text") === undefined) throw new Error('Radio should have attribute "data-value-text"');

                const newText = jqObj.attr("data-value-text");
                const newValue = jqObj.val();

                methods.proccessingFilter(this, newValue, newText);
            });
        },

        // (private) Add listener for A(ADD BUTTONS)
        addListnersA() {
            $("body").on("click", `a${filterSelector}`, e => {
                e.preventDefault();

                const jqObj = $(this);
                dtFilterSettings.beforeSet(jqObj);

                if (jqObj.attr("data-name") === undefined) throw new Error('A should have "data-name" attribute');

                if (jqObj.attr("data-value") === undefined) throw new Error('A should have "data-value" attribute');

                const newValue = jqObj.attr("data-value");
                const newText = jqObj.attr("data-value-text");

                methods.proccessingFilter(this, newValue, newText);
            });
        },

        // (private) Add listener for X(REMOVE BUTTONS)
        addListnersX() {
            $("body").on("click", ".dt-filter-delete", () => {
                const filterId = $(this).attr("data-parent");
                methods.clearActiveFilters(filterId);
                // methods.callCallback();
            });
        },

        // (private) Add listener for RESET BUTTON
        addListnersReset() {
            $("body").on("click", ".dt-filter-reset-buttons", () => {
                methods.clearActiveFilters();
                // methods.callCallback(true);
                dtFilterSettings.onReset();
            });
        },

        // (private) Add listener for APPLY BUTTON
        addListnersApply() {
            // eslint-disable-next-line consistent-return
            $("body").on("click", ".dt-filter-apply-buttons", () => {
                if ($(this).hasClass("active")) {
                    return false;
                }

                methods.callCallback(true);
                dtFilterSettings.onApply();
                methods.addClassToButtons("dt-filter-apply-buttons", "active");
            });
        },

        // (private) Get current value of the filter
        getFilterVal(filter) {
            const filterValue = {};
            const currentFilter = filter;

            switch (currentFilter.type) {
                case "select":
                    // eslint-disable-next-line no-case-declarations
                    let newText;
                    // eslint-disable-next-line no-case-declarations
                    let newValue;
                    if (currentFilter.jqObj.attr("multiple") !== undefined) {
                        const texts = [];
                        const values = [];
                        currentFilter.jqObj.children(`${filterSelector} option:selected`).each(() => {
                            let valText = $(this).data("value-text");

                            if (valText === undefined) valText = this.text;

                            texts.push(valText);
                            values.push(this.value);
                        });
                        newText = texts.join(", ");
                        newValue = values.join(",");
                    } else {
                        const $optionSelected = filter.jqObj.children(`${filterSelector} option:selected`).first();
                        let valText = $optionSelected.data("value-text");
                        if (valText === undefined) {
                            valText = $optionSelected.text();
                        }

                        newText = valText;
                        newValue = $optionSelected.val();
                    }
                    filterValue.text = newText;
                    filterValue.value = newValue;

                    break;
                case "radio":
                    // eslint-disable-next-line no-case-declarations
                    let $radioChecked = $(`input[type="radio"][name="${currentFilter.name}"][data-current="true"]${filterSelector}`);

                    if (!$radioChecked.length) {
                        $radioChecked = $(`input[type="radio"][name="${currentFilter.name}"]:checked${filterSelector}`);
                    }
                    if (!$radioChecked.length) {
                        $radioChecked = $(`input[type="radio"][name="${currentFilter.name}"][data-default="true"]${filterSelector}`);
                    }
                    if (!$radioChecked.length) {
                        $radioChecked = $(`input[type="radio"][name="${currentFilter.name}"][value=""]${filterSelector}`);
                    }
                    if ($radioChecked.length) {
                        $radioChecked.prop("checked", true);
                    }

                    filterValue.text = $radioChecked.attr("data-value-text");
                    filterValue.value = $radioChecked.val();

                    break;
                case "tel":
                case "url":
                case "week":
                case "date":
                case "text":
                case "time":
                case "email":
                case "range":
                case "color":
                case "month":
                case "search":
                case "number":
                case "password":
                case "datetime":
                case "datetime-local":
                    filterValue.text = currentFilter.jqObj.val();
                    filterValue.value = currentFilter.jqObj.val();

                    break;
                case "hidden":
                    filterValue.value = currentFilter.jqObj.val();
                    if (currentFilter.jqObj.data("value-text") !== undefined) {
                        filterValue.text = currentFilter.jqObj.data("value-text");
                    } else {
                        filterValue.text = filterValue.value;
                    }
                    break;
                case "checkbox":
                    // eslint-disable-next-line no-case-declarations
                    const texts = [];
                    // eslint-disable-next-line no-case-declarations
                    const values = [];
                    $(`input[name="${currentFilter.name}"]:checked${filterSelector}`).each(() => {
                        texts.push($(this).attr("data-value-text"));
                        values.push($(this).attr("data-value"));
                    });

                    filterValue.text = texts.length ? texts.join(", ") : "";
                    filterValue.value = values.length ? values.join(",") : "";

                    break;
                case "button":
                    // eslint-disable-next-line no-case-declarations
                    let $activeButton = $(`a[data-name="${currentFilter.name}"][data-current="true"]${filterSelector}`);
                    if (!$activeButton.length) {
                        $activeButton = currentFilter.jqObj;
                    }

                    filterValue.text = $activeButton.attr("data-value-text");
                    filterValue.value = $activeButton.attr("data-value");

                    break;
                default:
                    break;
            }

            return filterValue;
        },

        // (private) Get default value of the filter
        getFilterDefaultVal(filter) {
            const filterValue = {};
            const currentFilter = filter;

            switch (filter.type) {
                case "select":
                    // eslint-disable-next-line no-case-declarations
                    const $optionsSelected = currentFilter.jqObj.children('option[data-default="true"]');

                    if (currentFilter.jqObj.attr("multiple") !== undefined) {
                        if (!$optionsSelected.length) {
                            filterValue.text = "";
                            filterValue.value = "";
                        } else {
                            const texts = [];
                            const values = [];
                            $optionsSelected.each(() => {
                                let valText = $(this).data("value-text");
                                if (valText === undefined) {
                                    valText = this.text;
                                }

                                texts.push(valText);
                                values.push(this.value);
                            });
                            filterValue.text = texts.join(", ");
                            filterValue.value = values.join(",");
                        }
                    } else {
                        let $optionSelected = $optionsSelected.first();

                        if (!$optionSelected.length) $optionSelected = currentFilter.jqObj.children('option[value=""]').first();

                        if (!$optionSelected.length) {
                            filterValue.text = "";
                            filterValue.value = "";
                        } else {
                            let valText = $optionSelected.data("value-text");
                            if (valText === undefined) {
                                valText = $optionSelected.text();
                            }

                            filterValue.text = valText;
                            filterValue.value = $optionSelected.val();
                        }
                    }
                    break;
                case "radio":
                    // eslint-disable-next-line no-case-declarations
                    let $radioChecked = $(`input[type="radio"][name="${currentFilter.name}"][data-default="true"]${filterSelector}`);

                    if (!$radioChecked.length) {
                        $radioChecked = $(`input[type="radio"][name="${currentFilter.name}"][value=""]${filterSelector}`).first();
                    }
                    if (!$radioChecked.length) {
                        $radioChecked = $(`input[type="radio"][name="${currentFilter.name}"]${filterSelector}`).first();
                    }

                    filterValue.text = $radioChecked.attr("data-value-text");
                    filterValue.value = $radioChecked.val();

                    break;
                case "tel":
                case "url":
                case "week":
                case "date":
                case "text":
                case "time":
                case "email":
                case "range":
                case "color":
                case "month":
                case "search":
                case "number":
                case "password":
                case "datetime":
                case "datetime-local":
                    filterValue.text = "";
                    filterValue.value = "";
                    break;
                case "hidden":
                    filterValue.text = "";
                    filterValue.value = "";
                    break;
                case "checkbox":
                    filterValue.text = "";
                    filterValue.value = "";
                    break;
                case "button":
                    // eslint-disable-next-line no-case-declarations
                    let $button = $(`a[data-name="${currentFilter.name}"][data-default="true"]${filterSelector}`);
                    if (!$button.length) {
                        $button = $(`a[data-name="${currentFilter.name}"][data-value=""]${filterSelector}`);
                    }

                    if ($button.length) {
                        filterValue.text = $button.attr("data-value-text");
                        filterValue.value = $button.attr("data-value");
                    } else {
                        filterValue.text = "";
                        filterValue.value = "";
                    }
                    break;

                default:
                    break;
            }
            return filterValue;
        },

        // (private) Get a public info for the filter
        getFilter(filter) {
            if (filter === undefined) {
                return {};
            }

            return {
                tag: filter.tagName,
                name: filter.name,
                label: filter.filterLabel,
                value: filter.currentValue.value,
                default: filter.defaultValue.value,
            };
        },

        // (private) Get list of the active filters
        getActiveFilters() {
            const activeFilters = {};
            // eslint-disable-next-line no-restricted-syntax
            for (const key in dtFilters) {
                if (Object.prototype.hasOwnProperty.call(dtFilters, key)) {
                    if (dtFilters[key].currentValue.value !== dtFilters[key].defaultValue.value) {
                        activeFilters[dtFilters[key].name] = dtFilters[key];
                    }
                }
            }

            return activeFilters;
        },

        // (public) Get list of the active filters in format for DataTables
        getFilterDTFormat() {
            const returnArray = [];
            const activeFilters = methods.getActiveFilters();

            // eslint-disable-next-line no-restricted-syntax
            for (const key in activeFilters) {
                if (Object.prototype.hasOwnProperty.call(activeFilters, key)) {
                    returnArray.push({ name: activeFilters[key].name, value: activeFilters[key].currentValue.value });
                }
            }
            dtFilterSettings.onActive(returnArray);
            if (dtFilterSettings.debug) {
                // eslint-disable-next-line no-console
                console.log(dtFilters);
                // eslint-disable-next-line no-console
                console.log(returnArray);
            }

            return returnArray;
        },

        // (private) Caller of main callback
        callCallback(forced) {
            if (forced || dtFilterSettings.autoApply) {
                dtFilterSettings.callBack();
            }
        },
    };

    // Begin
    dtFilterSettings = $.extend(dtFilterSettings, options);

    // eslint-disable-next-line prefer-rest-params
    return methods.init.apply(this, arguments);
};

export default () => {
    $.fn.extend({
        dtFilters(options) {
            return DtFilters.call(this, options.selector, options);
        },
    });
};
