import $ from "jquery";

import { translate } from "@src/i18n";
import { systemMessages } from "@src/util/system-messages/index";
import { getPrice } from "@src/util/number";

import tagsInput from "@src/plugins/tags-input/index";
import htmlEscape from "@src/util/common/html-escape";
import selectVariantImages from "@src/pages/items_my/fragments/select_variant_images.fragment";
import EventHub from "@src/event-hub";
import intersection from "lodash/intersection";

class PriceVariationAddItemModule {
    constructor(params) {
        this.global = globalThis;
        this.propertyTemplateHtml = `<div class="js-property-row add-info-row add-info-row--options">
                            <div class="add-info-row__col add-info-row__col--w100pr">
                                <div class="add-info-row__item add-info-row__item--inline">
                                    <span class="add-info-row__item-name">{{titleName}}</span>
                                    <div class="add-info-row__inline-options">
                                        {{variantsShow}}
                                    </div>
                                </div>
                            </div>
                            <div class="add-info-row__col add-info-row__col--130">
                                <div class="add-info-row__actions add-info-row__actions--3">
                                    <button
                                        class="btn btn-light call-action"
                                        data-js-action="price-variation-add-item-module:change-position"
                                        data-action="down"
                                        title="Change down position group"
                                        type="button"
                                    ><i class="ep-icon ep-icon_arrow-line-down"></i>
                                    </button>
                                    <button
                                        class="btn btn-light call-action"
                                        data-js-action="price-variation-add-item-module:change-position"
                                        data-action="up"
                                        title="Change up position group"
                                        type="button"
                                    ><i class="ep-icon ep-icon_arrow-line-up"></i>
                                    </button>
                                    <button
                                        class="btn btn-light call-action"
                                        data-js-action="price-variation-add-item-module:remove-property"
                                        data-group="{{newVariantKey}}"
                                        title="Remove options group"
                                        type="button"
                                        ${params.atasRemoveBtn}
                                    ><i class="ep-icon ep-icon_trash-stroke"></i>
                                    </button>
                                </div>
                            </div>
                            <input type="hidden" name="properties[{{newVariantKey}}][id]" value="{{newVariantKey}}">
                            <input type="hidden" name="properties[{{newVariantKey}}][name]" value="{{titleName}}">
                            <input class="js-item-type" type="hidden" name="properties[{{newVariantKey}}][type]" value="{{typeName}}">
                            {{propertieInputTemp}}
                        </div>`;

        this.variantTemplateSelect = `<div class="js-select-variant-wr add-info-row__col add-info-row__col--simple add-info-row__col--320 mb-10">
                <select
                    data-name="{{titleName}}"
                    data-group="{{newVariantKey}}"
                    placeholder="Variants"
                    ${params.atasSelectVariant}
                ><option value="" selected>Any {{titleName}}</option>{{variantsOptions}}</select>
            </div>`;

        this.variantUnitTypeClass = "js-item-add-variant-unit-type";
        this.variantTemplateHtml = `<div
                            class="js-item-add-variant item-add-variant"
                            data-variant-key="{{variantUniqueKey}}"
                            data-variant="{{newVariantKey}}"
                            data-img="{{img}}"
                        >
                            <div class="item-add-variant__img">
                                <div class="item-add-variant__img-inner image-card3">
                                    <span class="link">
                                        <img
                                            class="image {{imgClass}}"
                                            src="{{imgSrc}}"
                                            alt="Image variant"
                                        >
                                    </span>
                                </div>
                            </div>
                            <div class="item-add-variant__item">
                                <div class="js-variant-detail item-add-variant__detail">
                                    {{title}}
                                </div>
                                <div class="item-add-variant__detail-numbers">
                                    {{priceDiscountHtml}}
                                    <span class="item-add-variant__number">$ {{discountPrice}}</span>
                                    {{discountHtml}}
                                    <span class="item-add-variant__number">{{quantity}}</span>
                                    <span class="${this.variantUnitTypeClass} item-add-variant__unit-type">{{unitType}}</span>
                                </div>
                            </div>
                            <div class="item-add-variant__actions">
                                <button
                                    class="btn btn-light call-action"
                                    data-js-action="price-variation-add-item-module:remove-variant"
                                    data-variant="{{newVariantKey}}"
                                    title="Remove option"
                                    type="button"
                                    ${params.atasRemoveVariant}
                                >
                                    <i class="ep-icon ep-icon_trash-stroke"></i>
                                </button>
                            </div>
                            <div class="item-add-variant__detail-numbers-mobile">
                                {{priceDiscountHtml}}
                                <span class="item-add-variant__number">$ {{discountPrice}}</span>
                                {{discountHtml}}
                                <span class="item-add-variant__number">{{quantity}}</span>
                                <span class="${this.variantUnitTypeClass} item-add-variant__unit-type">{{unitType}}</span>
                            </div>
                            <input type="hidden" name="combinations[{{newVariantKey}}][id]" value="{{newVariantKey}}">
                            <input class="js-item-type" type="hidden" name="combinations[{{newVariantKey}}][type]" value="{{typeName}}">
                            <input type="hidden" name="combinations[{{newVariantKey}}][img]" value="{{img}}">
                            <input type="hidden" name="combinations[{{newVariantKey}}][price]" value="{{priceInput}}">
                            <input type="hidden" name="combinations[{{newVariantKey}}][final_price]" value="{{discountPriceInput}}">
                            <input type="hidden" name="combinations[{{newVariantKey}}][quantity]" value="{{quantity}}">
                            {{inputVariants}}
                        </div>`;

        this.variantTemplatePriceHtml = `<span class="item-add-variant__price-discount item-add-variant__number">$ {{price}}</span>`;
        this.variantTemplateDiscountHtml = `<span class="item-add-variant__number">-{{discount}}%</span>`;

        this.addItemForm = $("#js-add-item-form");
        this.selectunitType = this.addItemForm.find('select[name="unit_type"]');
        this.inputPriceName = "#js-add-item-price-in-dol";
        this.inputPrice = $(this.inputPriceName);
        this.inputDiscountPriceName = "#js-add-item-final-price";
        this.inputDiscountPrice = $(this.inputDiscountPriceName);
        this.inputDiscount = $("#js-add-item-discount");

        this.specificsPriceWrapper = $("#js-specifics-price-wrapper");
        this.priceWrapper = $("#js-add-price-wrapper");
        this.variationsWrapper = $("#js-add-variations-wrapper");

        this.maxProperties = params.maxProperties;
        this.newPropertyCount = 0;
        this.propertyOptionTitle = $("#js-properties-title");
        this.propertiesOption = $("#js-properties-options");
        this.propertiesOptionShowWrapper = $("#js-add-item-properties-wr");
        this.propertiesOptionSelectWrapper = $("#js-add-item-properties-select-wr");
        this.keyOption = 0;
        this.addItemVariants = $("#js-add-item-variants");
        this.addItemVariantsWrapper = $("#js-add-item-variants-wr");
        this.itemVariants = JSON.parse(params.itemVariants) || {};
        this.variantGroups = this.itemVariants.properties || {};
        this.newVariantCount = 0;
        this.newVariantOptionCount = 0;
        this.variants = this.itemVariants.variants || {};

        this.title = params.title;
        this.maxOptionCharacters = params.maxOptionCharacters;
        this.maxOptions = params.maxOptions;
        this.warning = params.warning;

        this.optionTags = {};

        this.init();
    }

    init() {
        const that = this;

        selectVariantImages([that.title]);
        that.initGeneratedProperties();

        that.optionTags = tagsInput("#js-properties-options", {
            width: "100%",
            height: "auto",
            minChars: 1,
            maxChars: that.maxOptionCharacters,
            delimiter: [";"],
            defaultText: "e.g. Red; Green; Blue;",
            onAddTag(tagText) {
                const thatTag = $(this);
                const container = thatTag.siblings("div.tagsinput");
                const tags = container.find(".tag");

                if (tags.length > that.maxOptions) {
                    that.optionTags.removeTag(thatTag, tagText);
                    systemMessages(
                        translate({ plug: "general_i18n", text: "add_items_variants_max_options" }).toString().replace("[COUNT]", that.maxOptions),
                        "warning"
                    );
                }
            },
        });

        that.selectunitType.on("change", function unitTypeNameChange() {
            const el = $(this);
            const valueNames = htmlEscape(el.find("option:selected").text());
            $(`.${that.variantUnitTypeClass}`).text(valueNames);
        });

        $(`${that.inputPriceName}, ${that.inputDiscountPriceName}`).on("change", () => {
            that.renewPrice();
        });

        EventHub.off("price-variation-add-item-module:remove-variant");
        EventHub.on("price-variation-add-item-module:remove-variant", (e, button) => that.removeVariant(button));
        EventHub.off("price-variation-add-item-module:change-position");
        EventHub.on("price-variation-add-item-module:change-position", (e, button) => that.changePositionPropertyGroup(button));
        EventHub.off("price-variation-add-item-module:remove-property");
        EventHub.on("price-variation-add-item-module:remove-property", (e, button) => that.removeProperty(button));
        EventHub.off("price-variation-add-item-module:add-property");
        EventHub.on("price-variation-add-item-module:add-property", () => that.addPropertyGroup());
        EventHub.off("price-variation-add-item-module:add-variant");
        EventHub.on("price-variation-add-item-module:add-variant", () => that.addVariant());

        EventHub.off("price-variation-add-item-module:toggle-price-specifics");
        EventHub.on("price-variation-add-item-module:toggle-price-specifics", (e, button) => that.togglePriceSpecifics(button));
    }

    initGeneratedProperties() {
        const that = this;

        if (!Object.keys(that.variantGroups).length) {
            return true;
        }

        this.addItemVariants.removeClass("display-n");

        let propertyTemplateHtmlPrepared = "";
        let variantTemplateSelectPrepared = "";
        that.variantGroups = Object.values(that.variantGroups).sort((a, b) => (a.priority < b.priority ? -1 : 0));
        that.variantGroups.forEach(element => {
            const nameCleaned = htmlEscape(element.name);
            let variantsOptions = "";
            const variantsArray = [];
            let propertieInputTemp = "";

            element.property_options.forEach(elementOption => {
                variantsArray.push(elementOption.name);
                variantsOptions += `<option value="${elementOption.id}">${elementOption.name}</option>`;
                propertieInputTemp += `<input type="hidden" name="properties[${element.id}][options][${elementOption.id}][id]" value="${elementOption.id}">
                                        <input type="hidden" name="properties[${element.id}][options][${elementOption.id}][name]" value="${elementOption.name}">`;
            });

            const variantsShow = variantsArray.map(variant => `<span class="add-info-row__option">${htmlEscape(variant)}</span>`);

            propertyTemplateHtmlPrepared += that.propertyTemplateHtml
                .replaceAll("{{titleName}}", nameCleaned)
                .replace("{{typeName}}", "exist")
                .replace("{{variantsShow}}", variantsShow.join(" "))
                .replaceAll("{{newVariantKey}}", String(element.id))
                .replace("{{propertieInputTemp}}", propertieInputTemp);

            variantTemplateSelectPrepared += that.variantTemplateSelect
                .replaceAll("{{titleName}}", nameCleaned)
                .replace("{{newVariantKey}}", String(element.id))
                .replace("{{variantsOptions}}", variantsOptions);
        });

        that.propertiesOptionShowWrapper.html(propertyTemplateHtmlPrepared);
        that.propertiesOptionSelectWrapper.html(variantTemplateSelectPrepared);

        that.initGeneratedVariants();
        return true;
    }

    initGeneratedVariants() {
        const that = this;
        const unitType = that.selectunitType.find("option:selected").text();
        let variantTemplatePrepared = "";

        Object.values(that.variants).forEach(element => {
            let variantUniqueKey = "";
            let inputVariants = "";
            const anyOptions = {};

            element.property_options.forEach(elementOption => {
                variantUniqueKey += ` ${elementOption.id}`;

                if (anyOptions[elementOption.id_property] === undefined) {
                    anyOptions[elementOption.id_property] = `<span><strong>${htmlEscape(elementOption.propertyName)}</strong>: ${elementOption.name};</span> `;
                } else {
                    anyOptions[elementOption.id_property] = `<span><strong>${htmlEscape(elementOption.propertyName)}</strong>: Any;</span> `;
                }

                inputVariants += `<input type="hidden" name="combinations[${element.id}][variants][${that.newVariantOptionCount}][property_id]" value="${elementOption.id_property}">
                                <input type="hidden" name="combinations[${element.id}][variants][${that.newVariantOptionCount}][option_id]" value="${elementOption.id}">`;
                that.newVariantOptionCount += 1;
            });

            variantTemplatePrepared += that.replaceVariantTemplateVariants({
                discount: element.discount,
                price: element.price,
                discountPrice: element.final_price ?? 0,
                variantUniqueKey,
                newVariantKey: element.id,
                typeName: "exist",
                img: element.img.name,
                imgClass: element.img.class,
                imgSrc: element.img.src,
                title: Object.values(anyOptions).join(""),
                quantity: element.quantity,
                unitType,
                inputVariants,
            });
        });

        that.addItemVariantsWrapper.html(variantTemplatePrepared);

        return true;
    }

    replaceVariantTemplateVariants(params) {
        const that = this;
        const priceDiscountHtml =
            params.discountPrice > 0 && params.discountPrice !== params.price
                ? that.variantTemplatePriceHtml.replace("{{price}}", getPrice(params.price, false))
                : "";
        const discountPriceInput = String(params.discountPrice > 0 && params.discountPrice !== params.price ? params.discountPrice : params.price);
        const discountPrice = getPrice(discountPriceInput, false);
        const discountHtml = params.discount > 0 ? that.variantTemplateDiscountHtml.replace("{{discount}}", String(params.discount)) : "";
        const priceInput = params.price;
        const price = getPrice(priceInput, false);

        return this.variantTemplateHtml
            .replace("{{variantUniqueKey}}", params.variantUniqueKey.trim())
            .replaceAll("{{newVariantKey}}", params.newVariantKey)
            .replace("{{typeName}}", params.typeName)
            .replaceAll("{{img}}", params.img)
            .replace("{{imgClass}}", params.imgClass)
            .replace("{{imgSrc}}", params.imgSrc)
            .replace("{{title}}", params.title)
            .replaceAll("{{priceDiscountHtml}}", priceDiscountHtml)
            .replaceAll("{{discountPrice}}", discountPrice)
            .replaceAll("{{discountPriceInput}}", discountPriceInput)
            .replaceAll("{{discountHtml}}", discountHtml)
            .replaceAll("{{quantity}}", params.quantity)
            .replaceAll("{{unitType}}", params.unitType)
            .replaceAll("{{price}}", price)
            .replaceAll("{{priceInput}}", priceInput)
            .replace("{{inputVariants}}", params.inputVariants);
    }

    togglePriceSpecifics(button) {
        const that = this;
        const { type } = button.data();

        const changeValidateInputPrice = (removeClass = true) => {
            that.priceWrapper.find("input[data-validate-class]").each(function inputElements() {
                const el = $(this);

                if (removeClass) {
                    el.removeClass(`${el.data("validate-class")} validengine-border`)
                        .prev(".formError")
                        .remove();
                } else {
                    el.addClass(el.data("validate-class"));
                }
            });
        };

        switch (type) {
            case "price":
                that.specificsPriceWrapper.addClass("display-n");
                that.priceWrapper.removeClass("display-n").addClass("active");
                changeValidateInputPrice(false);
                break;
            case "variation":
                that.specificsPriceWrapper.addClass("display-n");
                that.variationsWrapper.removeClass("display-n").addClass("active");
                that.priceWrapper.find("input[data-validate-class]").each(function inputElements() {
                    const el = $(this);
                    el.removeClass(el.data("validate-class"));
                });
                break;
            default:
                that.specificsPriceWrapper.removeClass("display-n");
                that.priceWrapper.addClass("display-n").removeClass("active");
                that.variationsWrapper.addClass("display-n").removeClass("active");
                changeValidateInputPrice();
                break;
        }
    }

    calcDiscount(price, discountPrice) {
        const that = this;
        const params = {
            discount: 0,
            price,
            discountPrice,
        };

        const isZeroPrice = function (value) {
            return Math.abs(0 - parseFloat(value)) <= 1e-3;
        };
        const isGreaterThan = function (a, b) {
            const epsilon = 1e-18;
            if (Math.abs(a - b) < epsilon) {
                return false;
            }

            if (a > b) {
                return true;
            }

            return false;
        };

        if (Number.isNaN(params.price) || isZeroPrice(params.price) || Number.isNaN(params.discountPrice) || isZeroPrice(params.discountPrice)) {
            return params;
        }

        if (isGreaterThan(discountPrice, price)) {
            params.discountPrice = price;
            systemMessages(that.warning, "warning");
        }

        params.discount = 100 - (params.discountPrice * 100) / params.price;
        params.discount = params.discount < 1 ? 0 : parseInt(String(params.discount), 10);

        return params;
    }

    renewPrice() {
        const that = this;

        const price = parseFloat(String(that.inputPrice.val())) || 0;
        const discountPrice = parseFloat(String(that.inputDiscountPrice.val())) || 0;
        const params = this.calcDiscount(price, discountPrice);

        if (discountPrice !== params.discountPrice) {
            that.inputDiscountPrice.val(params.discountPrice);
        }

        that.inputDiscount.text(`${params.discount}%`);
    }

    addPropertyGroup() {
        const that = this;

        let variantsArray = {};
        let titleName = String(that.propertyOptionTitle.val()).trim();
        const titleNameLength = titleName.length;
        titleName = htmlEscape(titleName);

        variantsArray = String(that.propertiesOption.val())
            .split(";")
            .filter(element => {
                return element && element.trim().length > 0;
            });

        if (that.propertiesOptionShowWrapper.find(".js-property-row").length === that.maxProperties) {
            systemMessages(
                translate({ plug: "general_i18n", text: "add_items_variants_max_properties" }).toString().replace("[COUNT]", that.maxProperties),
                "warning"
            );
            return false;
        }

        if (that.propertyOptionTitle.hasClass("validengine-border") || that.propertiesOption.hasClass("validengine-border")) {
            return false;
        }

        if (titleNameLength === 0) {
            systemMessages(translate({ plug: "general_i18n", text: "add_items_variants_variation_type_required" }), "warning");
            return false;
        }

        if (variantsArray.length === 0) {
            systemMessages(translate({ plug: "general_i18n", text: "add_items_variants_options_required" }), "warning");
            return false;
        }

        let variantsShow = "";
        let variantsOptions = "";
        let propertieInputTemp = "";
        let variantInputTemp = "";
        const newPropertyCountKeyTemp = `property_${that.newPropertyCount}`;

        variantsArray.forEach(element => {
            that.keyOption += 1;
            const elementCleaned = htmlEscape(element);
            const indexKey = `option_${that.keyOption}`;
            variantsOptions += `<option value="${indexKey}">${elementCleaned}</option>`;
            variantInputTemp += `<input type="hidden" name="combinations[{{VARIANTKEY}}][variants][${that.newVariantOptionCount}][property_id]" value="${newPropertyCountKeyTemp}">
                                        <input type="hidden" name="combinations[{{VARIANTKEY}}][variants][${that.newVariantOptionCount}][option_id]" value="${indexKey}">`;
            propertieInputTemp += `<input type="hidden" name="properties[${newPropertyCountKeyTemp}][options][${indexKey}][id]" value="${indexKey}">
                                    <input type="hidden" name="properties[${newPropertyCountKeyTemp}][options][${indexKey}][name]" value="${elementCleaned}">`;
            that.newVariantOptionCount += 1;
            variantsShow += `<span class="add-info-row__option">${htmlEscape(element)}</span>`;
        });

        that.propertyOptionTitle.val("");
        that.optionTags.clearTags(that.propertiesOption, "");

        const propertyTemplateHtmlPrepared = that.propertyTemplateHtml
            .replaceAll("{{titleName}}", titleName)
            .replace("{{typeName}}", "new")
            .replace("{{variantsShow}}", variantsShow)
            .replaceAll("{{newVariantKey}}", newPropertyCountKeyTemp)
            .replace("{{propertieInputTemp}}", propertieInputTemp);
        that.propertiesOptionShowWrapper.append(propertyTemplateHtmlPrepared);
        that.global.validateTabInit();

        const variantTemplateSelectPrepared = that.variantTemplateSelect
            .replaceAll("{{titleName}}", titleName)
            .replace("{{newVariantKey}}", newPropertyCountKeyTemp)
            .replace("{{variantsOptions}}", variantsOptions);
        that.propertiesOptionSelectWrapper.append(variantTemplateSelectPrepared);

        if (that.addItemVariants.hasClass("display-n")) {
            that.addItemVariants.removeClass("display-n");
        }

        if (that.addItemVariantsWrapper.find(".js-item-add-variant").length) {
            that.addItemVariantsWrapper.find(".js-item-add-variant").each(function priceItems() {
                const $thisVariant = $(this);
                const variant = $thisVariant.data("variant");
                $thisVariant.append(variantInputTemp.replaceAll("{{VARIANTKEY}}", variant));
                $thisVariant.find(".js-item-type").val("new");
                $thisVariant.find(".js-variant-detail").append(`<span data-name="${newPropertyCountKeyTemp}"><strong>${titleName}</strong>: Any; </span>`);
            });

            systemMessages(translate({ plug: "general_i18n", text: "add_items_variants_updated" }), "info");
        }

        that.newPropertyCount += 1;

        return true;
    }

    changePositionPropertyGroup(button) {
        // eslint-disable-next-line no-unused-vars
        const that = this;

        const $thisBtn = $(button);
        const $group = $thisBtn.closest(".js-property-row");
        const action = $thisBtn.data("action");

        if (action === "up") {
            const $prevGroup = $group.prev(".js-property-row");

            if ($prevGroup.length) {
                $prevGroup.slideUp(function insertAfterItem() {
                    $(this).insertAfter($group).slideDown();
                });
            }
        } else if (action === "down") {
            const $nextGroup = $group.next(".js-property-row");

            if ($nextGroup.length) {
                $nextGroup.slideUp(function insertBeforeItem() {
                    $(this).insertBefore($group).slideDown();
                });
            }
        }
    }

    removeProperty(button) {
        const that = this;

        const buttonElement = $(button);

        if (that.addItemVariantsWrapper.find(".js-item-add-variant").length) {
            systemMessages(translate({ plug: "general_i18n", text: "add_items_variants_clear" }), "warning");

            return false;
        }

        that.propertiesOptionSelectWrapper
            .find(`select[data-group="${buttonElement.data("group")}"]`)
            .closest(".js-select-variant-wr")
            .remove();
        buttonElement.closest(".js-property-row").remove();

        if (!that.propertiesOptionShowWrapper.find(".js-property-row").length) {
            that.addItemVariants.addClass("display-n");
            that.cleanVariantInputData();
        }

        return true;
    }

    addVariant() {
        const that = this;

        const variantParams = {
            title: "",
            price: 0,
            discountPrice: 0,
            quantity: 0,
        };

        if (!that.propertiesOptionSelectWrapper.find("select").length) {
            systemMessages(translate({ plug: "general_i18n", text: "add_items_variants_add_option" }), "warning");
            return false;
        }

        const priceField = that.addItemVariants.find(".js-item-input-variant-prices");
        const discountPriceField = that.addItemVariants.find(".js-item-input-variant-discount-price");
        const quantityField = that.addItemVariants.find(".js-item-input-variant-quantity");

        variantParams.price = Number(parseFloat(String(priceField.val())).toFixed(2));
        variantParams.discountPrice = Number(parseFloat(String(discountPriceField.val() || 0)).toFixed(2));
        variantParams.quantity = Number(parseInt(String(quantityField.val()), 10));

        if (priceField.hasClass("validengine-border") || priceField.val() === "" || variantParams.price <= 0) {
            systemMessages(translate({ plug: "general_i18n", text: "add_items_variants_add_valid_price" }), "warning");
            priceField.prev(".formError").removeClass("hide").addClass("show");
            return false;
        }

        if (discountPriceField.hasClass("validengine-border") || variantParams.discountPrice < 0) {
            systemMessages(translate({ plug: "general_i18n", text: "add_items_variants_add_valid_discount" }), "warning");
            discountPriceField.prev(".formError").removeClass("hide").addClass("show");
            return false;
        }

        if (quantityField.hasClass("validengine-border") || quantityField.val() === "" || variantParams.quantity <= 0) {
            systemMessages(translate({ plug: "general_i18n", text: "add_items_variants_add_valid_quantity" }), "warning");
            quantityField.prev(".formError").removeClass("hide").addClass("show");
            return false;
        }

        const totalProperty = that.propertiesOptionShowWrapper.find(".js-property-row").length;
        const totalPropertySelect = that.propertiesOptionSelectWrapper.find("select").length;
        if (totalProperty !== totalPropertySelect) {
            systemMessages(translate({ plug: "general_i18n", text: "add_items_variants_all_options_included" }), "warning");
            return false;
        }

        const calcParams = that.calcDiscount(variantParams.price, variantParams.discountPrice);

        if (variantParams.discountPrice > variantParams.price) {
            return false;
        }

        if (variantParams.discountPrice !== calcParams.discountPrice) {
            variantParams.discountPrice = calcParams.discountPrice;
        }

        if (that.addItemVariantsWrapper.find(".js-item-add-variant").length) {
            const variantKey = [];
            that.propertiesOptionSelectWrapper.find(".js-select-variant-wr select").val((_index, value) => {
                if (value !== "") {
                    variantKey.push(value);
                }

                return value;
            });

            if (!variantKey.length) {
                systemMessages(translate({ plug: "general_i18n", text: "add_items_variants_already_included" }), "warning");
                return false;
            }

            let alreadyInclude = false;
            that.addItemVariantsWrapper.find(".js-item-add-variant").each((_index, element) => {
                const dataVariantKey = $(element).data("variant-key");
                const data = String(dataVariantKey).split(" ");
                const intersectionRsult = intersection(data, variantKey);

                if (intersectionRsult.length === variantKey.length) {
                    alreadyInclude = true;
                    return false;
                }

                return dataVariantKey;
            });

            if (alreadyInclude) {
                systemMessages(translate({ plug: "general_i18n", text: "add_items_variants_already_included" }), "warning");
                return false;
            }
        }

        let inputVariants = "";
        let variantUniqueKey = "";
        const newVariantKey = `key_${that.newVariantCount}`;
        that.propertiesOptionSelectWrapper.find("select").each(function propertiesOptionSelectWrapper() {
            const thisItem = $(this);

            const title = thisItem.data("name");
            const group = thisItem.data("group");
            const valueNames = htmlEscape(thisItem.find("option:selected").text());

            if (thisItem.val() === "") {
                variantParams.title += `<span><strong>${htmlEscape(title)}</strong>: Any; </span> `;

                thisItem.find("option").each(function eachOptionsItems() {
                    const valueOption = $(this).val();
                    variantUniqueKey += ` ${valueOption}`;
                    if (valueOption !== "") {
                        inputVariants += `<input type="hidden" name="combinations[${newVariantKey}][variants][${that.newVariantOptionCount}][property_id]" value="${group}">
                                            <input type="hidden" name="combinations[${newVariantKey}][variants][${that.newVariantOptionCount}][option_id]" value="${valueOption}">`;
                        that.newVariantOptionCount += 1;
                    }
                });
            } else {
                variantUniqueKey += ` ${thisItem.val()}`;
                variantParams.title += `<span><strong>${htmlEscape(title)}</strong>: ${valueNames}; </span> `;
                inputVariants += `<input type="hidden" name="combinations[${newVariantKey}][variants][${
                    that.newVariantOptionCount
                }][property_id]" value="${group}">
                <input type="hidden" name="combinations[${newVariantKey}][variants][${that.newVariantOptionCount}][option_id]" value="${thisItem.val()}">`;
                that.newVariantOptionCount += 1;
            }
        });

        const imgElement = that.addItemVariants.find(".js-select-variant-images .select-variant-images__selected-img .image");
        const img = imgElement.data("image");
        const imgSrc = imgElement.attr("src");
        const unitType = that.selectunitType.find("option:selected").text();

        const variantTemplatePrepared = that.replaceVariantTemplateVariants({
            discount: calcParams.discount,
            price: variantParams.price,
            discountPrice: variantParams.discountPrice,
            variantUniqueKey,
            newVariantKey,
            typeName: "new",
            img,
            imgClass: img === "main" ? " js-add-item-change-main-photo" : "",
            imgSrc,
            title: variantParams.title,
            quantity: variantParams.quantity,
            unitType,
            inputVariants,
        });

        that.addItemVariantsWrapper.append(variantTemplatePrepared);
        that.cleanVariantInputData();

        that.newVariantCount += 1;
        return true;
    }

    cleanVariantInputData() {
        const that = this;
        const priceField = that.addItemVariants.find(".js-item-input-variant-prices");
        const discountPriceField = that.addItemVariants.find(".js-item-input-variant-discount-price");
        const quantityField = that.addItemVariants.find(".js-item-input-variant-quantity");

        that.propertiesOptionSelectWrapper.find("select").prop("selectedIndex", 0);
        priceField.val("");
        discountPriceField.val("");
        quantityField.val("");
    }

    removeVariant(button) {
        // eslint-disable-next-line no-unused-vars
        const that = this;

        const $this = $(button);

        $this.closest(".js-item-add-variant").fadeOut("normal", function removeVariantItem() {
            $(this).remove();
        });
    }

    numberFormat(number) {
        // eslint-disable-next-line no-unused-vars
        const that = this;
        return new Intl.NumberFormat("en-US").format(number);
    }
}

export default params => {
    // eslint-disable-next-line no-new
    new PriceVariationAddItemModule(params);
};
