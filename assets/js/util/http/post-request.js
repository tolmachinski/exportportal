import httpRequest from "@src/util/http/http-request";
/**
 * Sends a HTTP POST request and returns the response in form of Promise. Is a promisified version of $.post() function.
 *
 * @param {string|URL} url
 * @param {any} [data]
 * @param {string} [type=json]
 *
 * @see {@link jQuery.post}
 *
 * @returns {Promise<any>}
 */
const postRequest = function postRequest(url, data, type, options = {}) {
    return httpRequest("POST", url, data, type, options);
};

export default postRequest;
