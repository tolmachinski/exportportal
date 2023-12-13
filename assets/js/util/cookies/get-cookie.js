import Cookies from "js-cookie";

/**
 * Returns the specified cookie.
 *
 * @param {string} cookieName
 *
 * @returns {any}
 */
const getCookie = cookieName => Cookies.get(cookieName);

export default getCookie;
