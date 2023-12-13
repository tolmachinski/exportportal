// Simple function for support without connect lodash

var debounceTimeout;
function debounce(func, wait) {
    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(function() {
        func();
    }, wait);
};

var IdleWorker = (function() {

    function IdleWorker(warn_time) {
        this.checkedDontShowMore = 0;
        this.logout_url = __site_url + "authenticate/logout?reason=inactivity";
        this.cookie_reset_name = 'ep_reset_logout_timeout';
        this.cookieDestroyReset = "ep_logout_destroy_reset";
        this.cookie_close_warn_name = 'ep_close_warning_autologout';
        this.events = ["load", "mousemove", "mousedown", "click", "touch", "scroll", "keypress", "wheel", "DOMMouseScroll", "mousewheel", "touchmove"];
        this.warn_time = warn_time;
        this.cookieListener = new CookieListener();
        this.warn = this.warn.bind(this);
        this.logout = this.logout.bind(this);
        this.resetTimeout = this.resetTimeout.bind(this);

        this.callListenerResetTimeout();
        this.checkOtherTabs();
    }

    var _p = IdleWorker.prototype;

    _p.callListenerResetTimeout = function() {
        var self = this;
        this.resetTimeoutForListener = function(){
            debounce(function(){
                self.resetTimeout();
            }, 300)
        };

        this.events.forEach(function (event) {
            window.addEventListener(event, self.resetTimeoutForListener);
        });
        globalThis.addEventListener("chat-app:idle-worker-reset-timeout", self.resetTimeoutForListener);
        self.resetTimeoutForListener();
    }

    _p.clearTimeout = function() {
        if (this.warnTimeout) {
            clearTimeout(this.warnTimeout);
        }
    }

    _p.setTimeout = function() {
        this.warnTimeout = setTimeout(this.warn, this.warn_time * 60 * 1000);
    }

    _p.resetTimeout = function(cookie_set) {
        cookie_set = cookie_set !== undefined ? cookie_set : true;
        if(cookie_set){
            setCookie(this.cookie_reset_name, new Date().getTime() + Math.random());
        }
        this.clearTimeout();
        this.setTimeout();
    }

    _p.closeWarning = function(caller, checked){
        var self = this;

        if (caller === "closeByBtn") {
            setCookie(this.cookie_close_warn_name, true, 1);
        }

        if (checked) {
            this.checkedDontShowMore = checked;
            setCookie(this.cookieDestroyReset, true, 1);

            this.destroy();
            postRequest(`${__current_sub_domain_url}authenticate/ajax_no_auto_logout`);
        }

        this.cookieListener.removeListener(this.cookieResetName);
        this.cookieListener.removeListener(this.cookie_close_warn_name);
        bootstrapDialogCloseAll();

        setTimeout(() => {
            removeCookie(self.cookieResetName);
            removeCookie(self.cookie_close_warn_name);

            if (!this.checkedDontShowMore) {
                self.callListenerResetTimeout();
            }
        }, 1500);
    }

    _p.checkOtherTabs = function(){
        var self = this;
        this.cookieListener.addListener(this.cookie_reset_name, function(){
            self.resetTimeout(false);
        });
    }

    _p.warn = function() {
        var self = this;
        self.clearListenersResetTimeout();

        self.cookieListener.addListener(this.cookie_close_warn_name, function(){
            self.closeWarning();
        });

        self.cookieListener.addListener(this.cookieDestroyReset, function(){
            this.checkedDontShowMore = 1;
            self.clearListenersResetTimeout();
        });

        var url = __current_sub_domain_url + "authenticate/ajax_operations/logout_warning";

        var onRequestSuccess = function (response) {
            if (response.isLogged === true) {
                open_result_modal({
                    title: "Session Timeout",
                    type: "warning",
                    content: response.content,
                    contentFooter: response.footer,
                    buttons: []
                });
            } else {
                location.reload();
            }
        };

        return postRequest(url)
            .then(onRequestSuccess)
            .catch(function (error) {
                if (__debug_mode) {
                    console.error(error);
                }
            });
    }

    _p.logout = function() {
        globalThis.dispatchEvent(
            new CustomEvent("matrixLogout", {
                detail: {
                    callback: () => {
                        window.location.href = this.logout_url;
                    },
                },
            })
        );
    }

    _p.clearListenersResetTimeout = function() {
        var self = this;
        this.events.forEach(function (event) {
            window.removeEventListener(event, self.resetTimeoutForListener);
        });
    }

    _p.destroy = function() {
        this.clearTimeout();
        this.cookieListener.removeListener(this.cookie_reset_name);
        this.clearListenersResetTimeout();
        globalThis.removeEventListener("chat-app:idle-worker-reset-timeout", self.resetTimeoutForListener);
    }

    return IdleWorker;

})();
