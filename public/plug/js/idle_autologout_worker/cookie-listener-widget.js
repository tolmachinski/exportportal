
var CookieListener = (function() {

    function CookieListener() {
        this.cookieListeners = [];
        this.cookieRegistry = [];
    }

    var _p = CookieListener.prototype;

    _p.addListener = function(cookieName, callback)
    {
        callback = callback !== undefined ? callback : null;
        var self = this;
        this.cookieListeners[cookieName + '_listener'] = setInterval(function()
        {
            var current_cookie = getCookie(cookieName);
            // console.log(current_cookie);
            if (self.cookieRegistry[cookieName])
            {
                if (current_cookie != self.cookieRegistry[cookieName])
                {
                    self.cookieRegistry[cookieName] = current_cookie;
                    return (callback !== null ? callback() : null);
                }
            } else {
                self.cookieRegistry[cookieName] = current_cookie;
            }
        }, 500);

    }

    _p.removeListener = function(cookieName){
        var self = this;
        delete self.cookieRegistry[cookieName];
        clearInterval(self.cookieListeners[cookieName + '_listener']);
    }

    return CookieListener;

})();
