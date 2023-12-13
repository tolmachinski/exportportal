(function () {
    if (typeof globalThis.notifyOutOfStock !== 'undefined') {
        return;
    }

    mix(globalThis, {
        notifyOutOfStock: function(element) {
            var that = $(element);
            var url = that.data('href');
            var item = that.data('resource');

            return postRequest(url, { item: item }).then(function (response) {
                systemMessages(response.message, response.mess_type);
            });
        }
    });
})();
