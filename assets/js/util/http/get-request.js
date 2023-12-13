import httpRequest from "@src/util/http/http-request";

/**
 * Sends a HTTP GET request and returns the response in form of Promise. Is a promisified version of $.get() function.
 *
 * @param {string|URL} url
 * @param {string} [type=json]
 *
 * @see {@link jQuery.get}
 *
 * @returns {Promise<any>}
 */
const getRequest = function getRequest(url, type) {
    return httpRequest("GET", url, null, type);
};

export default getRequest;
