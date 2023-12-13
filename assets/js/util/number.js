import getCookie from "@src/util/cookies/get-cookie";

const toPadedNumber = str => {
    let transformed = str.toString().replace(/^#/, "");
    if (transformed === parseInt(transformed, 10).toString()) {
        const pad = "#00000000000";
        transformed = transformed.substr(-11);

        return pad.substring(0, pad.length - transformed.length) + transformed;
    }

    return `#${transformed}`;
};

const intval = num => {
    if (num == null) {
        return 0;
    }

    let transformedNum = num;

    if (typeof num === "number" || typeof num === "string") {
        transformedNum = transformedNum.toString();
        const dotLocation = transformedNum.indexOf(".");
        if (dotLocation > 0) {
            transformedNum = transformedNum.substr(0, dotLocation);
        }

        if (Number.isNaN(Number(transformedNum))) {
            transformedNum = parseInt(transformedNum, 10);
        }

        if (Number.isNaN(transformedNum)) {
            return 0;
        }

        return Number(num);
    }

    if (typeof num === "object" && num.length != null && num.length > 0) {
        return 1;
    }

    if (typeof num === "boolean" && num === true) {
        return 1;
    }

    return 0;
};

const numberFormat = (numberParam, decimalsParam, decPointParam, thousandsPointParam) => {
    let number = numberParam;
    let decimals = decimalsParam;
    let decPoint = decPointParam;
    let thousandsPoint = thousandsPointParam;

    if (number == null || !Number.isFinite(number)) {
        throw new TypeError("number is not valid");
    }

    if (!decimals) {
        const len = number.toString().split(".").length;
        decimals = len > 1 ? len : 0;
    }

    if (!decPoint) {
        decPoint = ".";
    }

    if (!thousandsPoint) {
        thousandsPoint = ",";
    }

    number = parseFloat(number).toFixed(decimals);

    number = number.replace(".", decPoint);

    const splitNum = number.split(decPoint);
    splitNum[0] = splitNum[0].replace(/\B(?=(\d{3})+(?!\d))/g, thousandsPoint);
    number = splitNum.join(decPoint);

    return number;
};

const getPrice = (fpriceParam, showSymbol) => {
    const currencyValue = parseFloat(getCookie("currency_value"));
    const fprice = parseFloat(fpriceParam);

    if (showSymbol === false) {
        return numberFormat(fprice, 2, ".", ",");
    }

    if (showSymbol !== true) {
        return numberFormat(fprice * currencyValue, 2, ".", ",");
    }

    const currencyCode = getCookie("currency_code");
    const price = fprice * currencyValue;

    if (!price) {
        return `${currencyCode}${numberFormat(0, 2, ".", ",")}`;
    }

    return `${currencyCode}${numberFormat(price, 2, ".", ",")}`;
};

// eslint-disable-next-line import/prefer-default-export
export { toPadedNumber, intval, getPrice, numberFormat };
