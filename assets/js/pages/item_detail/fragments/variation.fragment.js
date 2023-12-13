import $ from "jquery";

import { SITE_URL } from "@src/common/constants";
import { getPrice } from "@src/util/number";
import checkIfImageExists from "@src/util/common/check-image-exist";

let paramsInner;
let currentVariantGroup = {};
let itemVariants = {};
let totalProperties = 0;
let defaultVariant = {};

const setMainImage = (imgName = "main") => {
    const imageWr = $(".js-product-gallery-main");
    const image = imageWr.find(".image");
    const link = imageWr.find(".link");

    if (imgName !== "main") {
        let url = "";
        if (paramsInner.photos[imgName].id !== undefined) {
            url = `${SITE_URL}public/storage/items/${paramsInner.itemId}/${imgName}`;
        } else {
            url = `${SITE_URL}${paramsInner.photos[imgName].photo_name}`;
        }

        checkIfImageExists(url, exists => {
            if (!exists) {
                url = `${SITE_URL}public/img/no_image/group/noimage-other.svg`;
            }

            image.attr("src", url);
            link.attr("href", url);
        });
    } else {
        const url = $(".js-product-main-image-link").attr("data-href");
        image.attr("src", url);
        link.attr("href", url);
    }
};

const changeDetailHtmlParams = params => {
    const discountLabel = $(".js-product-gallery-label-discount");
    const discountPriceBlock = $(".js-product-param-discount");
    const priceBlock = $(".js-product-price-new-block");
    const priceOldBlock = $(".js-product-price-old");

    if (params.discount > 0) {
        discountPriceBlock.removeClass("display-n_i");
        priceBlock.addClass("display-n_i");
        priceOldBlock.removeClass("display-n_i");
        if (!discountLabel.length) {
            $(".js-product-gallery-main").append(`
                <div class="js-product-gallery-label-discount product-gallery__label" atas="item__discount">
                    <span>- ${params.discount}%</span>
                </div>
            `);
        } else {
            discountLabel.show().html(`<span>- ${params.discount}%</span>`);
        }
    } else {
        discountLabel.hide();
        discountPriceBlock.addClass("display-n_i");
        priceBlock.removeClass("display-n_i");
        priceOldBlock.addClass("display-n_i");
    }
};

const variantsMakePrice = statusParam => {
    const status = statusParam || "call";
    const priceParams = { start: 0, final: 0 };
    const variantSelected = $(".js-product-variant .variant-selected").length;
    const inputOrderVariation = $("#js-order-variation");

    if (variantSelected === totalProperties) {
        const idVariation = Object.values(currentVariantGroup).reduce((a, b) => a.filter(x => b.includes(x)));
        const variationData = itemVariants.variants[idVariation[0]];

        priceParams.start = parseFloat(variationData.price);
        priceParams.final = parseFloat(variationData.final_price);

        if (status !== "init") {
            setMainImage(variationData.image || "main");
        }

        let allInputsOptionsSelected = `<input type="hidden" name="variant[id]" value="${variationData.id}">`;
        $(".js-product-variant-param-item.variant-selected").each(function optionsSelected() {
            const el = $(this);
            allInputsOptionsSelected += `<input type="hidden" name="variant[options][]" value="${el.data("option")}">`;
        });
        inputOrderVariation.html(allInputsOptionsSelected);
        changeDetailHtmlParams(variationData);
    } else {
        inputOrderVariation.html("");
        changeDetailHtmlParams({ discount: 0 });
        setMainImage();
    }

    return priceParams;
};

const makePrice = status => {
    const detailOrderPrice = $("#js-item-detail-order-price");
    let finalPrice = parseFloat(String(detailOrderPrice.val()));

    if ($(".js-product-variant-selected").length) {
        const priceParams = variantsMakePrice(status);
        finalPrice = priceParams.final;
        $(".js-product-price-old").html(getPrice(priceParams.start, true));
    }

    // item variants
    detailOrderPrice.val(finalPrice);
    $(".js-product-price-new").html(getPrice(finalPrice, true));
    $(".js-item-real-price").html(getPrice(finalPrice, false));
};

const calculateTotal = () => {
    const price = parseFloat(String($("#js-item-detail-order-price").val()));
    const weight = parseFloat(String($("#js-item-detail-weight").val()));
    const quantity = parseInt(String($("#js-quantity-order").val()), 10);
    const totalWeight = weight * quantity;
    const summ = (price * quantity).toFixed(2);

    $("#js-total-price-b").html(getPrice(summ, true));
    $("#js-total-weight-b").text(totalWeight.toFixed(3));
};

const selectVarianCombMultiple = () => {
    $(".js-product-variant-wr .js-product-variant").each(function productVariant() {
        const $this = $(this);
        const group = $this.data("group");
        const tmp = { ...currentVariantGroup };
        delete tmp[group];
        const size = Object.keys(tmp).length;

        $this.find(".js-product-variant-param-item:not(.variant-selected)").each(function variantParams() {
            const $thisV = $(this);
            const variantsData = String($thisV.data("variants"));
            const variants = variantsData.split(" ");
            let enable = false;
            const breakException = {};

            try {
                variants.forEach(element => {
                    let inVariants = 0;

                    $.each(tmp, (_indexG, elementG) => {
                        if (Object.values(elementG).indexOf(element) > -1) {
                            inVariants += 1;
                            return true;
                        }

                        return true;
                    });

                    if (size === inVariants) {
                        enable = true;
                        $thisV.removeClass("variant-disabled").addClass("variant-enable");
                        throw breakException;
                    }
                });
            } catch (e) {
                if (e !== breakException) throw e;
            }

            if (!enable) {
                $thisV.removeClass("variant-enable").addClass("variant-disabled");
            }
        });
    });
};

const initDefaulVariants = () => {
    const anyOptions = {};
    defaultVariant.property_options.forEach(element => {
        if (anyOptions[element.id_property] !== undefined) {
            return;
        }

        const $defaultItem = $(`.js-product-variant-param-item[data-option="${element.id}"]`);
        $defaultItem.addClass("variant-default variant-selected");
        const variantsData = String($defaultItem.data("variants"));
        const allVariants = variantsData.split(" ");
        currentVariantGroup[element.id_property] = allVariants;
        anyOptions[element.id_property] = 1;
    });

    setMainImage(defaultVariant.image);
    selectVarianCombMultiple();

    return true;
};

const changeItemVariant = (optionElement, add) => {
    const propertyId = optionElement.closest(".js-product-variant").data("group");
    const selectedItem = $(`.js-product-variant-selected .js-product-variant-selected-item[data-property=${propertyId}]`);
    if (add) {
        selectedItem
            .attr("data-option", optionElement.data("option"))
            .attr("data-variant", optionElement.data("variants"))
            .find(".js-product-variant-selected-param")
            .text(optionElement.text());
    } else {
        selectedItem.attr("data-option", "").attr("data-variant", "").find(".js-product-variant-selected-param").text("Not select");
    }
};

const selectOption = optionElement => {
    let selected = true;
    const group = optionElement.closest(".js-product-variant").data("group");

    if (optionElement.hasClass("variant-selected")) {
        selected = false;
        optionElement.removeClass("variant-selected");
        delete currentVariantGroup[group];
    } else {
        optionElement.addClass("variant-selected").siblings().removeClass("variant-selected");
        const variantsData = String(optionElement.data("variants"));
        const allVariants = variantsData.split(" ");
        currentVariantGroup[group] = allVariants;
    }

    const $variantSelected = $(".js-product-variant-param-item.variant-selected");

    if (!$variantSelected.length) {
        currentVariantGroup = {};
        $(".js-product-variant-param-item").removeClass("variant-disabled").addClass("variant-enable");
    } else {
        selectVarianCombMultiple();
    }

    changeItemVariant(optionElement, selected);

    makePrice();
    calculateTotal();
};

export { makePrice, calculateTotal };
export default params => {
    paramsInner = { ...params };
    itemVariants = JSON.parse(paramsInner.itemVariants);
    totalProperties = Object.keys(itemVariants.properties).length;
    defaultVariant = JSON.parse(paramsInner.defaultVariant);

    initDefaulVariants();

    $(".js-product-variant-wr").on("click", ".js-product-variant.variant-disabled", function variantDisabled(e) {
        e.stopImmediatePropagation();
    });

    $(".js-product-variant-wr").on("click", ".variant-enable", function ariantEnable(e) {
        e.stopImmediatePropagation();
        selectOption($(this));
    });

    return true;
};
