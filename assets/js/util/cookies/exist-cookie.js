import Cookies from "js-cookie";

/**
 * Checks if cookie with provided name exists.
 *
 * @param {string} cookieName
 *
 * @returns {boolean}
 */
const existCookie = cookieName => !!Cookies.get(cookieName);

export default existCookie;
