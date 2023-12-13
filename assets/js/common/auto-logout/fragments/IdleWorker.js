import debounce from "lodash/debounce";

import removeCookie from "@src/util/cookies/remove-cookie";
import setCookie from "@src/util/cookies/set-cookie";
import { closeAllDialogs, openResultModal } from "@src/plugins/bootstrap-dialog/index";
import { SITE_URL, SUBDOMAIN_URL } from "@src/common/constants";
import { openDialogModal } from "@src/epl/common/popups/types/modal-dialog";
import { closeFancyboxPopup } from "@src/plugins/fancybox/v3/util";
import handleRequestError from "@src/util/http/handle-request-error";
import CookieListener from "@src/common/auto-logout/fragments/CookieListener";
import postRequest from "@src/util/http/post-request";
import Platform from "@src/epl/platform";

class IdleWorker {
    constructor(warnTime) {
        // eslint-disable-next-line no-underscore-dangle
        this.checkedDontShowMore = 0;
        this.logoutUrl = `${SITE_URL}authenticate/logout?reason=inactivity`;
        this.cookieResetName = "ep_reset_logout_timeout";
        this.cookieDestroyReset = "ep_logout_destroy_reset";
        this.cookieCloseWarnName = "ep_close_warning_autologout";
        this.events = ["load", "mousemove", "mousedown", "click", "scroll", "keypress", "wheel", "DOMMouseScroll", "mousewheel", "touchmove"];
        this.warnTime = warnTime;
        this.cookieListener = new CookieListener();
        this.warn = this.warn.bind(this);
        this.logout = this.logout.bind(this);
        this.resetTimeout = this.resetTimeout.bind(this);

        this.callListenerResetTimeout();
        this.checkOtherTabs();
    }

    callListenerResetTimeout() {
        this.resetTimeoutForListener = debounce(() => {
            this.resetTimeout();
        }, 300);

        this.events.forEach(event => {
            globalThis.addEventListener(event, this.resetTimeoutForListener);
        });

        globalThis.addEventListener("chat-app:idle-worker-reset-timeout", this.resetTimeoutForListener);
        this.resetTimeoutForListener();
    }

    clearTimeout() {
        if (this.warnTimeout) {
            clearTimeout(this.warnTimeout);
        }
    }

    setTimeout() {
        this.warnTimeout = setTimeout(this.warn, this.warnTime * 60000);
    }

    resetTimeout(cookie = true) {
        if (cookie) {
            setCookie(this.cookieResetName, new Date().getTime() + Math.random(), {});
        }
        this.clearTimeout();
        this.setTimeout();
    }

    closeWarning(caller = "", checked = 0) {
        if (caller === "closeByBtn") {
            setCookie(this.cookieCloseWarnName, true, {});
        }

        if (checked) {
            this.checkedDontShowMore = checked;
            setCookie(this.cookieDestroyReset, true, {});

            this.destroy();
            postRequest(`${SUBDOMAIN_URL}authenticate/ajax_no_auto_logout`);
        }

        this.cookieListener.removeListener(this.cookieResetName);
        this.cookieListener.removeListener(this.cookieCloseWarnName);
        if (Platform.eplPage) {
            closeFancyboxPopup();
        } else {
            closeAllDialogs();
        }

        setTimeout(() => {
            removeCookie(this.cookieDestroyReset);
            removeCookie(this.cookieCloseWarnName);

            if (!this.checkedDontShowMore) {
                this.callListenerResetTimeout();
            }
        }, 1500);
    }

    checkOtherTabs() {
        this.cookieListener.addListener(this.cookieResetName, () => {
            this.resetTimeout(false);
        });
    }

    warn() {
        this.clearListenersResetTimeout();

        this.cookieListener.addListener(this.cookieCloseWarnName, () => {
            this.closeWarning();
        });
        this.cookieListener.addListener(this.cookieDestroyReset, () => {
            this.checkedDontShowMore = 1;
            this.clearListenersResetTimeout();
        });

        // eslint-disable-next-line no-underscore-dangle
        return postRequest(`${SUBDOMAIN_URL}authenticate/ajax_operations/logout_warning`)
            .then(response => {
                this.postResponce = response;
                this.openSessionTimeoutModal();
            })
            .catch(handleRequestError);
    }

    openSessionTimeoutModal() {
        if (this.postResponce.isLogged !== true) {
            globalThis.location.reload();
            return;
        }

        const dialogParams = {
            title: "Session Timeout",
            content: this.postResponce.content,
            contentFooter: this.postResponce.footer,
            buttons: [],
            closable: false,
            category: "warning",
            modal: true,
        };

        if (Platform.eplPage) {
            openDialogModal({ ...dialogParams, type: "html" });
        } else {
            openResultModal({ ...dialogParams, type: "warning" });
        }
    }

    logout() {
        if (globalThis.matrixLogoutEmitter) {
            globalThis.dispatchEvent(
                new CustomEvent("matrixLogout", {
                    detail: {
                        callback: () => {
                            globalThis.location.href = this.logoutUrl;
                        },
                    },
                })
            );
        } else {
            globalThis.location.href = this.logoutUrl;
        }
    }

    clearListenersResetTimeout() {
        const that = this;
        that.events.forEach(event => {
            globalThis.removeEventListener(event, that.resetTimeoutForListener);
        });
    }

    destroy() {
        this.clearTimeout();
        this.cookieListener.removeListener(this.cookieResetName);
        this.clearListenersResetTimeout();
        globalThis.removeEventListener("chat-app:idle-worker-reset-timeout", this.resetTimeoutForListener);
    }
}

export default IdleWorker;
