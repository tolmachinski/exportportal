import $ from "jquery";
import { calculateTotal } from "@src/pages/item_detail/fragments/variation.fragment";

/**
 * It initializes the quantity spinner
 * @param {any} params
 */
const initMinMaxSpinner = async params => {
    // @ts-ignore
    $("#js-quantity-order").spinner({
        step: 1,
        numberFormat: "n",
        icons: { down: "ep-icon ep-icon_minus-stroke", up: "ep-icon ep-icon_plus-stroke" },
        max: params.maxSaleQuantity,
        min: params.minSaleQuantity,
        change() {
            const btn = $(this);
            // @ts-ignore
            const maxVal = btn.spinner("option", "max");

            // @ts-ignore
            const minVal = btn.spinner("option", "min");
            const currentVal = parseInt(String(btn.val()), 10) || minVal;
            btn.val(currentVal);

            if (currentVal >= maxVal) {
                // @ts-ignore
                btn.spinner("value", maxVal);
            } else if (currentVal < minVal) {
                // @ts-ignore
                btn.spinner("value", minVal);
            }

            calculateTotal();
        },
        spin() {
            setTimeout(() => {
                calculateTotal();
            }, 0);
        },
    });
};

export default initMinMaxSpinner;
