import $ from "jquery";

/**
 * Sends a HTTP request and returns the response in form of Promise. Is a promisified version of $.ajax() function.
 *
 * @param {string} method
 * @param {string|URL} url
 * @param {any} [data]
 * @param {string} [type=json]
 *
 * @returns {Promise<any>}
 */
const httpRequest = function (method, url, data, type, opts = {}) {
    const options = {};
    let xhrHandler = null;
    const getTopDomain = function (host) {
        return host.split(".").slice(-2).join(".");
    };

    try {
        const fullUrl = url instanceof URL ? url : new URL(url);
        if (fullUrl.host !== globalThis.location.host) {
            options.crossDomain = true;
            options.headers = { "X-Requested-With": "XMLHttpRequest" };
            if (getTopDomain(fullUrl.host) === getTopDomain(globalThis.location.host)) {
                options.xhrFields = { withCredentials: true };
            }
        }
    } catch (error) {
        // sadly, no valid URL instance
    }
    const request = new Promise((resolve, reject) => {
        xhrHandler = $.ajax({ url: url.toString(), type: method.toUpperCase(), data, dataType: type || "json", ...opts })
            .fail((xhr, status, error) => {
                const failure = { message: error, status, data: xhr.responseJSON ? xhr.responseJSON.data || null : null, isCustom: true, xhr };
                reject(failure);
            })
            .done((response, status, xhr) => {
                if (response && (response.status === "error" || response.mess_type === "error" || response.mess_type === "errors")) {
                    const failure = { message: response.message, status, data: response.data || null, isCustom: true, xhr };
                    reject(failure);

                    return;
                }

                resolve(response);
            });
    });
    Object.defineProperty(request, "xhrHandler", { writable: false, value: xhrHandler });

    return request;
};

export default httpRequest;
