import $ from "jquery";

import { toPadedNumber } from "@src/util/number";
import Operation from "@src/plugins/dt-filters/tools/Operation";
import Binding from "@src/plugins/dt-filters/tools/Binding";
import Bindings from "@src/plugins/dt-filters/tools/Bindings";

/**
 * @param {{name: string, [x: string]: any}} filter
 * @param {Array.<{name: string, source?: any, op: Array.<{name: any, target?: any, value?: any}>}>} bindingsList
 * @param {{[x: string]: Function}} handlers
 */
const adjustFilter = function (filter, bindingsList, handlers) {
    new Bindings(bindingsList || [], handlers || {}).handle(filter);
};

const rewriteHystory = function (url, filter) {
    if (typeof globalThis.history !== "undefined" && typeof globalThis.history.pushState !== "undefined") {
        let value = null;
        const segments = {};
        const parts = globalThis.location.pathname.split("/").filter(p => p);
        for (let i = 0; i < parts.length; i += 2) {
            const part = parts[i];
            segments[part] = parts[i + 1] || null;
        }
        if (Object.prototype.hasOwnProperty.call(segments, "group")) {
            value = segments.group || null;
        }

        globalThis.history.replaceState({ filter: { name: filter.name, value } }, $("title").text(), globalThis.location.href);
        globalThis.history.pushState({ filter: { name: filter.name, value: null } }, $("title").text(), url);
    } else {
        window.location.href = url;
    }
};

const maskFilterNumber = function () {
    const value = this.node.val() || null;
    const number = value !== null ? toPadedNumber(value) : null;
    if (number) {
        this.node.val(number);
    } else {
        this.node.val("");

        return false;
    }

    return true;
};

const dropPathParams = function (pathname, keys, skip) {
    const eachSlice = function (array, size) {
        const { length } = array;
        if (!length || size < 1) {
            return [];
        }
        let index = 0;
        let resIndex = 0;
        const result = new Array(Math.ceil(length / size));
        while (index < length) {
            // eslint-disable-next-line no-plusplus
            result[resIndex++] = array.slice(index, (index += size));
        }

        return result;
    };
    const fromPairs = function (pairs) {
        const length = pairs !== null ? pairs.length : 0;
        if (pairs == null || !length) {
            return {};
        }

        return pairs.reduce((accumulator, value) => {
            // eslint-disable-next-line prefer-destructuring
            accumulator[value[0]] = value[1];

            return accumulator;
        }, {});
    };
    const toPairs = function (props) {
        return Object.keys(props).map(key => [key, props[key]]);
    };
    const flatten = function (array) {
        return array.reduce((accumulator, value) => accumulator.concat(value), []);
    };

    const parts = fromPairs(
        eachSlice(
            pathname
                .split("/")
                .filter(f => f)
                .slice(skip || 0),
            2
        )
    );
    (Array.isArray(keys) ? keys : [keys]).forEach(key => {
        if (parts[key]) {
            delete parts[key];
        }
    });

    return flatten(toPairs(parts)).join("/");
};

export { adjustFilter, rewriteHystory, dropPathParams, maskFilterNumber };
export default {
    Operation,
    Bindings,
    Binding,
};
