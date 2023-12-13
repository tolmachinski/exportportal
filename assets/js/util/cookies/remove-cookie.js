import { COOKIE_DOMAIN } from "@src/common/constants";
import Cookies from "js-cookie";

/**
 * Removes the specified cookie.
 *
 * @param {string} cookieName
 */
const removeCookie = function (cookieName) {
    Cookies.remove(cookieName, {
        path: "/",
        domain: COOKIE_DOMAIN,
    });
};

export default removeCookie;
