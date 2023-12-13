import getCookie from "@src/util/cookies/get-cookie";

export default class CookieListener {
    constructor() {
        this.cookieListeners = [];
        this.cookieRegistry = [];
    }

    addListener(cookieName, callback = null) {
        // eslint-disable-next-line consistent-return
        this.cookieListeners[`${cookieName}_listener`] = setInterval(() => {
            const currentCookie = getCookie(cookieName);

            if (this.cookieRegistry[cookieName]) {
                if (currentCookie !== this.cookieRegistry[cookieName]) {
                    this.cookieRegistry[cookieName] = currentCookie;
                    return typeof callback === "function" ? callback() : null;
                }
            } else {
                this.cookieRegistry[cookieName] = currentCookie;
            }
        }, 500);
    }

    removeListener(cookieName) {
        this.cookieRegistry[cookieName] = null;

        clearInterval(this.cookieListeners[`${cookieName}_listener`]);
    }
}
