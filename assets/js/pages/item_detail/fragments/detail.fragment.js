import $ from "jquery";
import { systemMessages } from "@src/util/system-messages/index";
import initVariations, { makePrice, calculateTotal } from "@src/pages/item_detail/fragments/variation.fragment";

import EventHub from "@src/event-hub";
import mix from "@src/util/common/mix";
import lazyLoadingScriptOnScroll from "@src/common/lazy/lazy-script";
import initGallerySlider from "@src/pages/item_detail/fragments/gallery-slider";

let paramsInner;

const setDataItem = opener => {
    const $this = $(opener);
    const quantity = parseInt(String($("#js-quantity-order").val()), 10);

    if (quantity === 0) {
        return false;
    }

    $this.attr("data-items", `${paramsInner.itemId}x${quantity}`);
    return true;
};

const checkIfOptionsFilled = fancybox => {
    const orderVariation = $("#js-order-variation");

    if (orderVariation.length && orderVariation.html() === "") {
        systemMessages(paramsInner.systmessFillOptions, "warning");

        return false;
    }

    if (orderVariation.length) {
        // eslint-disable-next-line no-param-reassign
        fancybox.ajax.type = "POST";
        const sendData = {};

        orderVariation.find("input").each((index, input) => {
            const inputElement = $(input);
            const inputName = inputElement.attr("name") === "variant[id]" ? inputElement.attr("name") : `variant[options][${index}]`;
            sendData[inputName] = inputElement.val();
        });
        // eslint-disable-next-line no-param-reassign
        fancybox.ajax.data = sendData;
    }

    return true;
};

export default params => {
    paramsInner = params;

    $(() => {
        if ($(".js-product-variant-selected").length) {
            initVariations(params);
        }

        makePrice("init");
        calculateTotal();
        initGallerySlider();

        $("#js-order-now-form").on("submit", () => {
            return false;
        });
    });

    EventHub.off("item-detail:add-to-basket");
    EventHub.on("item-detail:add-to-basket", async (_e, btn) => {
        const { default: addToBasket } = await import("@src/pages/item_detail/fragments/add-to-basket");
        addToBasket(btn, paramsInner);
    });

    EventHub.off("remove:droplist-item");
    EventHub.on("remove:droplist-item", async (_e, btn) => {
        const { default: removeFromDroplist } = await import("@src/pages/item_detail/fragments/remove-from-droplist");
        removeFromDroplist(btn, paramsInner);
    });

    lazyLoadingScriptOnScroll(
        $(".js-product-params-spinner"),
        async () => {
            // @ts-ignore
            await import(/* webpackChunkName: "jquery-ui-spinner-core" */ "jquery-ui/ui/widgets/spinner").then(async () => {
                const { default: initMinMaxSpinner } = await import("@src/pages/item_detail/fragments/min-max-spinner");
                initMinMaxSpinner(paramsInner);
            });
        },
        "50px"
    );

    mix(
        globalThis,
        {
            checkIfOptionsFilled,
            set_data_item: setDataItem,
        },
        false
    );
};
