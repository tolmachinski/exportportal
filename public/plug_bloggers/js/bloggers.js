var existCookie = function (cookie_name){
	if(Cookies.get(cookie_name) == undefined){
		return false;
	} else{
		return true;
	}
};
var setCookie = function (cookie_name, cookie_value, expires){
	Cookies.set(cookie_name, cookie_value, { expires: expires , path: '/', domain: __js_cookie_domain});
};
var getCookie = function (cookie_name){
	return Cookies.get(cookie_name);
};
var removeCookie = function (cookie_name){
	Cookies.remove(cookie_name, {path: '/', domain: __js_cookie_domain});
};
var fileuploadRemove = function (button) {
    var url = button.data('action') || null;
    var file = button.data('file') || null;
    var callback = button.data('additional-callback') || null;
    if (typeof callback === 'string') {
        callback = callback in window ? window[callback] : null;
    }
    if (null === url || null === file) {
        return;
    }

    $.post(url, {file: file}, null, 'json').done(function (response) {
        if (response.mess_type && response.mess_type !== 'success') {
            Messenger.notification(response.mess_type, response.message);
        } else {
            button.closest('.uploadify-queue-item').remove();
            if (null !== callback) {
                callback.call(this, button);
            }
        }
    }).fail(function () {
        Messenger.error('Service is temporary unavailable');
    });
};
var templateFileUpload = function (type, className, index, iconClassName) {
    if (type === 'files') {
        templateHtml = '<div id="fileupload-item-' + index + '" class="uploadify-queue-item ' + className + ' icon">' +
            '<div class="img-b icon-files-' + iconClassName + '-middle"></div>' +
            '<div class="cancel"></div>' +
            '</div>';
    } else {
        templateHtml = '<div id="fileupload-item-' + index + '" class="uploadify-queue-item ' + className + '">' +
            '<div class="img-b"></div>' +
            '<div class="cancel"></div>' +
            '</div>';
    }

    return templateHtml;
};
var addLoader = function (containerSelector) {
    var container = $(containerSelector);
    var loader = container.find('.blogger-preloader');
    if (
        container.length &&
        !container.hasClass('blogger-preloader-wrapper') &&
        !loader.length
    ) {
        var image = $('<img>').attr({
            src: __img_url + "public/img/bloggers/preloader.gif",
            alt: "Loading"
        });
        var loader = $('<div>').attr({
            class: "bloggers-preloader"
        }).append(image);

        container.prepend(loader);
        container.addClass('blogger-preloader-wrapper');
        if ('BODY' === container.prop('tagName')) {
            container.addClass('blogger-preloader-wrapper-fixed');
        }
    }
};
var removeLoader = function (containerSelector) {
    var container = $(containerSelector);
    var loader = container.find('.bloggers-preloader');
    if (
        container.length &&
        container.hasClass('blogger-preloader-wrapper') &&
        loader.length
    ) {
        loader.remove();
        container.removeClass('blogger-preloader-wrapper');
        if ('BODY' === container.prop('tagName')) {
            container.removeClass('blogger-preloader-wrapper-fixed');
        }
    }
};
var cleanInput = function (str) {
    if (str != undefined && str != '' && str != null) {
        str = str.replace(/&/g, "&amp;");
        str = str.replace(/>/g, "&gt;");
        str = str.replace(/</g, "&lt;");
        str = str.replace(/"/g, "&quot;");
        str = str.replace(/'/g, "&#039;");
        return str;
    } else {
        return false;
    }
};
var Messenger = (function ($) {
    var Messenger = function (theme) {
        this.theme = theme || 'relax';
        this.defaultOptions = {
            theme: this.theme,
            closeWith: ['click', 'button'],
        };
    }
    Messenger.prototype.templates = {
        notifications: {
            icon: "<div class=\"system-messages__card-icon-wrapper\">\
                    <i class=\"system-messages__card-icon [[:icon:]]\"></i>\
                </div>",
            title: "<div class=\"system-messages__card-title\">\
                    <strong>[[:title:]]</strong>\
                </div>",
            text: "<div class=\"system-messages__card-text\">\
                    [[:text:]]\
                </div>",
            body: "<div class=\"system-messages__card system-messages__card-[[:type:]]\">\
                    [[:icon:]]\
                    [[:title:]]\
                    [[:text:]]\
                </div>"
        },
        dialog: {
            title: "<div class=\"system-messages__card-title\">\
                    <strong>[[:title:]]</strong>\
                </div>",
            text: "<div class=\"system-messages__card-text\">\
                    [[:text:]]\
                </div>",
            body: "<div class=\"system-messages__card system-messages__card-modal system-messages__card-[[:type:]]\">\
                    <div class=\"system-messages__card system-messages__card-modal-header\">\
                        [[:title:]]\
                    </div>\
                    <div class=\"system-messages__card system-messages__card-modal-body\">\
                        [[:text:]]\
                    </div>\
                </div>"
        },

    };
    Messenger.prototype.notification = function (type, message, title, options) {
        type = type || 'alert';
        title = title || null;
        message = message || "System " + type;
        options = $.extend(true, {}, options || {}, this.defaultOptions, {
            type: type,
            layout: 'topRight',
            timeout: 10000,
            progressBar: true,
            queue: 'notifications'
        });

        return this.enqueueNotifications(type, message, title).map(function (notification) {
            var notificationOptions = $.extend({}, options, {
                text: this.buildNotification(notification.type, notification.title, notification.message)
            });
            var noty = new Noty(notificationOptions);
            noty.show();

            return noty;
        }, this);
    };
    Messenger.prototype.dialog = function (message, title, buttons, options) {
        var noty;
        var type = 'confirm';
        title = title || "Information";
        message = message || null;
        options = options || {};
        buttons = buttons || [];
        if (null === message) {
            throw new TypeError('Invalid argument at positon 1 - string expected');
        }

        options = $.extend(true, {}, options, this.defaultOptions, {
            width: 640,
            minWidth: 320,
            type: 'alert',
            modal: true,
            killer: true,
            layout: 'top',
            queue: 'dialog',
            closeWith: ['button'],
            text: this.buildDialog(type, title, message),
            buttons: buttons,
            animation: {
                open: false,
                close: false,
            }
        });

        noty = new Noty(options);
        noty.show();

        return noty;
    };
    Messenger.prototype.alert = function (message, title, options) {
        return this.notification('alert', message, title, options)
    }
    Messenger.prototype.info = function (message, title, options) {
        return this.notification('info', message, title, options)
    }
    Messenger.prototype.success = function (message, title, options) {
        return this.notification('success', message, title, options)
    }
    Messenger.prototype.warning = function (message, title, options) {
        return this.notification('warning', message, title, options)
    }
    Messenger.prototype.error = function (message, title, options) {
        return this.notification('error', message, title, options)
    }
    Messenger.prototype.confirm = function (message, title, callback, options) {
        callback = callback || $.noop;

        return this.dialog(message, title, [
            Noty.button(translate_js({
                plug: 'BootstrapDialog',
                text: 'ok'
            }), 'system-messages__card-button system-messages__card-button-ok', function (noty) {
                if (callback !== $.noop) {
                    callback.call(this, noty);
                } else {
                    noty.close();
                }
            }, {
                'data-status': 'ok'
            }),

            Noty.button(translate_js({
                plug: 'BootstrapDialog',
                text: 'cancel'
            }), 'system-messages__card-button system-messages__card-button-cancel', function (noty) {
                noty.close();
            }, {
                'data-status': 'cancel'
            })
        ], options);
    };
    Messenger.prototype.enqueueNotifications = function (type, message, title) {
        var queue = [];
        var messageType = typeof message;
        var addItem = function (message) {
            if (typeof message === 'string') {
                message = {
                    detail: message
                }
            }
            queue.push({
                type: message.type || type,
                title: message.title || title,
                message: message.detail || 'Undefined error'
            });
        };

        switch (messageType) {
            case 'string':
                addItem(message);

                break;
            case 'array':
                message.forEach(function (rawMessage) {
                    addItem(rawMessage);
                });

                break;
            case 'object':
                for (var key in message) {
                    if (message.hasOwnProperty(key)) {
                        addItem(message[key]);
                    }
                }

                break;
            default:
                return [];
        };

        return queue;
    };
    Messenger.prototype.buildNotification = function (type, title, text, icon) {
        title = title || null;
        type = type || null;
        text = text || null;
        icon = icon || null;

        if (null !== icon) {
            icon = this.templates.notifications.icon.replace('[[:icon:]]', icon);
        }
        if (null !== text) {
            text = this.templates.notifications.text.replace('[[:text:]]', text);
        }
        if (null !== title) {
            title = this.templates.notifications.title.replace('[[:title:]]', title);
        }

        return this.templates.notifications.body
            .replace('[[:type:]]', type || 'info')
            .replace('[[:icon:]]', icon || '')
            .replace('[[:text:]]', text || '')
            .replace('[[:title:]]', title || '');
    };
    Messenger.prototype.buildDialog = function (type, title, text) {
        title = title || null;
        type = type || null;
        text = text || null;

        if (null !== text) {
            text = this.templates.dialog.text.replace('[[:text:]]', text);
        }
        if (null !== title) {
            title = this.templates.dialog.title.replace('[[:title:]]', title);
        }

        return this.templates.dialog.body
            .replace('[[:type:]]', type || 'alert')
            .replace('[[:text:]]', text || '')
            .replace('[[:title:]]', title || '');
    };

    return new Messenger();
}(jQuery));

(function (window, $) {

    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });
    
    var runFormTracking = function (form, isSuccessfull) {
        if(!form) {
            return;
        }

        if(typeof __analytics !== 'undefined') {
            __analytics.trackSubmit(
                $(form).filter(__tracking_selector).toArray(),
                { isManual: true, isSuccessfull: Boolean(~~isSuccessfull) },
                { immediate: true, propagate: false }
            );
        }
    };

    var checkUserTZ = function () {
        var ud = new Date();
        var utz = -(ud.getTimezoneOffset()) / 60;
        setCookie('_ep_utz', utz, 7);

        return true;
    };
    var bindValidationRules = function () {
        $.fn.validationEngineLanguage = function () {};
        $.validationEngineLanguage = {
            newLang: function () {
                $.validationEngineLanguage.allRules = translate_js_lang({
                    plug: 'validationEngine'
                });
            }
        };
        $.validationEngineLanguage.newLang();
    };
    var beforeModalLoad = function () {
        var modalInitializer = this.element;
        var modalTitle = modalInitializer.data("title") || null;
        var modalWidth = modalInitializer.data("w") || null;
        var modalHeight = modalInitializer.data("h") || null;
        var beforeCallback = modalInitializer.data("before-callback") || null;
        if (typeof beforeCallback === 'string') {
            beforeCallback = beforeCallback in window ? window[beforeCallback] : null;
        }

        if (null !== beforeCallback && false === beforeCallback.call(this)) {
            return false;
        }

        if (null !== modalTitle) {
            this.title = cleanInput(modalTitle);
        }

        if (null !== modalHeight) {
            this.autoHeight = false;
            this.height = modalHeight;
        }
        if (null !== modalWidth) {
            this.autoWidth = false;
            this.width = modalWidth;
        }

        this.ajax.caller_btn = modalInitializer;
    };
    var addCloseButton = function () {
        $('.fancybox-title').addClass('modal-title');
        $('.fancybox-title-outside-wrap').append(
            $('<a>').attr('title', "Close")
            .addClass("modal-close pull-right call-function")
            .data({
                message: "Are you sure you want to close this window? All your progress will be lost",
                callback: closeFancyBox
            })
            .append(
                $('<span>').attr('aria-hidden', true).html('&times;')
            )
        );
    };
    var closeFancyBox = function () {
        $('.validate-modal').validationEngine('detach');
        $.fancybox.close();
    };
    var mutateCloseButtonClass = function () {
        $('.fancybox-title').find('a.modal-close').removeClass('call-function').addClass('confirm-dialog');
    };
    var callInsetFunction = function (e) {
        e.preventDefault();

        var button = $(this);
        var callback = button.data('callback') || null;
        if (typeof callback === 'string') {
            callback = callback in window ? window[callback] : null;
        }

        if (null !== callback) {
            callback.call(this, button);
        }

        return false;
    };
    var callConfirmDialog = function (e) {
        e.preventDefault();
        var button = $(this);
        var callback = button.data('callback') || null;
        if (typeof callback === 'string') {
            callback = callback in window ? window[callback] : null;
        }

        Messenger.confirm(button.data('message'), null, function (noty) {
            $(this.dom).prop("disabled", true).toggleClass('disabled', true);
            if (null !== callback) {
                callback.call(this, button);
            }
            noty.close();
        });
    };
    var showSystemMessage = function (e) {
        e.preventDefault();

        var button = $(this);
        var type = button.data('type') || 'error';
        var message = button.data('message') || null;
        if (null !== message) {
            Messenger.notification(type, message);
        }

        return false;
    };
    var onModalRequestComplete = function () {
        var modalInitializer = this.caller_btn || null;
        if (typeof $.fn.validationEngine === 'undefined') {
            return;
        }

        $(".validate-modal").validationEngine('attach', {
            scroll: false,
            autoPositionUpdate: true,
            focusFirstField: false,
            maxErrorsPerField: 1,
            promptPosition: "topLeft:0,0",
            showArrowOnRadioAndCheckbox: true,
            onValidationComplete: function (formElement, status) {
                var form = $(formElement);
                if (status) {
                    var callback = form.data("callback") || null;
                    if (typeof callback === 'string') {
                        callback = callback in window ? window[callback] : null;
                    }
                    if(null === callback) {
                        callback = typeof modalFormCallBack !== 'undefined' ? modalFormCallBack : $.noop;
                    }

                    callback.call(formElement, form, modalInitializer)
                } else {
                    form.each(function(index, form) {
                        var elements = form.elements || [];
                        if(elements.length) {
                            $(elements[0]).validationEngine('updatePromptsPosition')
                        }
                    });
                    Messenger.error("Form fields contain invalid values.\nPlease check the entered information to proceed further");
                    runFormTracking(form, false);
                }
            }
        });
    };
    var submitCode = function (event) {
        event.preventDefault();

        var code;
        var form = $(this);
        var url = form.attr('action') || null;
        var codeInput = form.find('input[type=text][name=code]');
        if (null === url || !codeInput.length) {
            Messenger.error('Error - integrity violation');

            return;
        }

        code = codeInput.val() || null;
        if (null === code) {
            Messenger.error('You must enter the access code');

            return;
        }

        addLoader('body');
        $.post(url, { code: code }, null, 'json').done(function (response) {
            if (response.mess_type && response.mess_type !== 'success') {
                Messenger.notification(response.mess_type, response.message || 'Service is temporary unavailable');
                removeLoader('body');

                return;
            }

            form.trigger('reset');
            $.fancybox && $.fancybox.open({
                type: 'ajax',
                href: response.location,
                element: $('<a>').data({
                    title: "Fill the form",
                    w: 768
                }),
            }, fancyboxModalFormOptions);
        }).fail(function () {
            Messenger.error('Service is temporary unavailable');
            removeLoader('body');
        });
    };
    var fancyboxModalOptions = {
        loop: false,
        helpers: {
            title: {
                type: 'outside',
                position: 'top'
            },
            overlay: {
                closeClick: false,
                showEarly: false,
                locked: true
            }
        },
        lang: __site_lang,
        i18n: translate_js_one({
            plug: 'fancybox'
        }),
        modal: true,
        padding: 0,
        closeBtn: true,
        closeClick: false,
        closeBtnWrapper: '.fancybox-wrap .fancybox-title',
        beforeLoad: beforeModalLoad,
        beforeShow: function () {
            removeLoader('body');
        },
        afterShow: addCloseButton,
    };
    var fancyboxModalFormOptions = $.extend({}, true, fancyboxModalOptions, {
        ajax: {
            complete: onModalRequestComplete
        }
    });

    $(function () {
        checkUserTZ();
        bindValidationRules();

        $(".fancybox:not(.fancybox-modal-form)").fancybox(fancyboxModalOptions || {}, ".fancybox:not(.fancybox-modal-form)");
        $(".fancybox:has(.fancybox-modal-form)").fancybox(fancyboxModalFormOptions || {}, ".fancybox:has(.fancybox-modal-form)");
        $('body')
            .on('submit', '.code-submit-form', submitCode)
            .on('click', ".call-function", callInsetFunction)
            .on('click', ".confirm-dialog", callConfirmDialog)
            .on('change', "form.content-form.content-form--modal :input", mutateCloseButtonClass)
            .on('click', ".call-system-message", showSystemMessage);

        Object.defineProperty(window, 'runFormTracking', { writable: false, value: runFormTracking });
    });
}(window, jQuery));
