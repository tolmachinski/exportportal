import { DEBUG, COOKIE_DOMAIN } from "@src/common/constants";
import Cookies from "js-cookie";
/**
 * Sets the specified cookie.
 *
 * @param {string} cookieName
 * @param {any} cookieValue
 * @param {any} [options={}]
 */
const setCookie = function (cookieName, cookieValue, { expires }) {
    Cookies.set(cookieName, cookieValue, {
        path: "/",
        expires: expires instanceof Date ? expires.toUTCString() : expires,
        domain: COOKIE_DOMAIN,
        secure: !DEBUG,
    });
};

export default setCookie;
