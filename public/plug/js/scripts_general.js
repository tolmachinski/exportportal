/* eslint-disable */
(function () {
    "use strict";

    //#region Functions
    /**
     * Mexes properties into the object.
     *
     * @param {any} object
     * @param {any} properties
     * @param {boolean} [immutable]
     */
    function mix(object, properties, immutable) {
        immutable = typeof immutable !== "undefined" ? immutable : true;
        for (var key in properties) {
            if (properties.hasOwnProperty(key)) {
                if (immutable) {
                    Object.defineProperty(object, key, {
                        writable: false,
                        value: properties[key],
                    });
                } else {
                    object[key] = properties[key];
                }
            }
        }

        return object;
    }
    /**
     * Promisfies the function
     *
     * @param {Function} fn
     *
     * @returns {Promise<any>}
     */
    function promisify(fn) {
        return function () {
            var context = this;
            for (var length = arguments.length, args = new Array(length), key = 0; key < length; key++) {
                args[key] = arguments[key];
            }

            return new Promise(function (resolve, reject) {
                try {
                    resolve(fn.apply(context, args));
                } catch (error) {
                    reject(error);
                }
            });
        };
    }
    /**
     * Checks if device is running on iOS
     *
     * @returns {boolean}
     */
    function isIoS() {
        return (
            (/iPad|iPhone|iPod/.test(navigator.userAgent || navigator.vendor || globalThis.opera) && !globalThis.MSStream) ||
            (!!navigator.platform && /iPad|iPhone|iPod/.test(navigator.platform))
        );
    }
    /**
     * Checks if browser is Safari.
     *
     * @returns {boolean}
     */
    function isSafari() {
        return (
            /constructor/i.test(globalThis.HTMLElement) ||
            Object.prototype.toString.call(globalThis.HTMLElement).indexOf("Constructor") > 0 ||
            typeof globalThis.safari !== "undefined" ||
            /^((?!chrome|android).)*safari/i.test(navigator.userAgent)
        );
    }
    /**
     * Checks if browser is IE.
     *
     * @returns {boolean}
     */
    function isIe() {
        return document.documentMode || /Edge/.test(navigator.userAgent);
    }
    /**
     * Checks if device is running on Android
     *
     * @returns {boolean}
     */
    function isAndroid() {
        return /(android)/i.test(navigator.userAgent || navigator.vendor || globalThis.opera);
    }
    /**
     * Checks if browser is iOS version of Chrome.
     *
     * @returns {boolean}
     */
    function isChromeIoS() {
        return /CriOS\/[\d]+/.test(navigator.userAgent || navigator.vendor || globalThis.opera);
    }
    /**
     * Checks if it is mobile device
     *
     * @returns {boolean}
     */
    function isMobile() {
        var check = false;
        (function (a) {
            if (
                /(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino|android|ipad|playbook|silk/i.test(
                    a
                ) ||
                /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(
                    a.substr(0, 4)
                )
            ) {
                check = true;
            }
        })(navigator.userAgent || navigator.vendor || globalThis.opera);

        return check;
    }
    /**
     * Checks if value is object and not is NULL
     *
     * @param {any} value
     *
     * @returns {boolean}
     */
    function isObject(value) {
        return value != null && (typeof value === "object" || typeof value === "function");
    }
    /**
     * Checks if value is object and not is NULL
     *
     * @param {any} value
     *
     * @returns {boolean}
     */
    function isEmptyObject(value) {
        return typeof value !== "object" || value.constructor !== Object || Object.keys(value).length === 0;
    }
    /**
     * Calls provided function if it is defined
     *
     * @param {string|Function} fn
     * @param {...any} args
     */
    function callFunction(fn) {
        var args = Array.prototype.slice.call(arguments, 1) || [];
        if (typeof fn === "string" && fn in window && window[fn]) {
            window[fn].apply(window[fn], args);
        } else if (typeof fn === "function") {
            fn.apply(fn, args);
        }
    }
    /**
     * Handles the request errors. Default handler.
     *
     * @param {any} error
     */
    function onRequestError(error) {
        var requestError = error.isCustom ? error.xhr || null : typeof error.statusCode !== "undefined" ? error : null;
        var genericError = error.isGeneric ? error : null;
        if (null !== requestError) {
            if (requestError.responseJSON && requestError.responseJSON.message) {
                systemMessages(requestError.responseJSON.message, requestError.responseJSON.mess_type || "error");
            } else {
                systemMessages(translate_js({ plug: "general_i18n", text: "system_message_server_error_text" }), "error");
            }
        } else if (null !== genericError) {
            systemMessages(
                genericError.message || translate_js({ plug: "general_i18n", text: "system_message_client_error_text" }),
                genericError.messageType || genericError.mess_type || "error"
            );
        } else {
            systemMessages(translate_js({ plug: "general_i18n", text: "system_message_client_error_text" }), "error");
        }

        if (__debug_mode) {
            console.error(error);
        }
    }
    /**
     * Runs submit tracking signal for provided form.
     *
     * @param {string|HTMLElement|JQuery} form
     * @param {boolean} isSuccessfull
     */
    function runFormTracking(form, isSuccessfull) {
        if (!form) {
            return;
        }

        if (typeof __analytics !== "undefined") {
            __analytics.trackSubmit(
                $(form).filter(__tracking_selector).toArray(),
                {
                    isManual: true,
                    isSuccessfull: Boolean(~~isSuccessfull),
                },
                {
                    immediate: true,
                    propagate: false,
                }
            );
        }
    }
    /**
     * Return the URL with provided path for current domain.
     *
     * @param {string} path
     *
     * @deprecated
     *
     * @returns {string}
     */
    function domainUrl(path) {
        var urlPath = path ? ("/" === path.charAt(0) ? path.substr(1) : path) : "";
        if (!__is_main_domain) {
            return __group_site_url + urlPath;
        }

        return __site_url + urlPath;
    }

    /**
     * Handles the validation completion for the jquery.validationEngine.
     *
     * @param {JQuery|HTMLElement} form
     * @param {any} status
     *
     * @returns {any}
     */
     function onValidationCompleted(form, status, button) {
        if (status) {
            var callback = $(form).data("callback") || null;
            if (null !== callback) {
                return callFunction(callback, form || null, button || null);
            } else if ("modalFormCallBack" in globalThis && modalFormCallBack) {
                return callFunction("modalFormCallBack", form || null, button || null);
            }
        } else {
            systemMessages(translate_js({ plug: "general_i18n", text: "validate_error_message" }), "error");
        }

        return status;
    }

    /**
     * Enables provided form validation.
     *
     * @param {string|HTMLElement|JQuery} selector
     * @param {any} [options]
     * @param {JQuery} [button=null]
     */
    function enableFormValidation(selector, options, button) {
        if (typeof $.fn.validationEngine === "undefined") {
            return;
        }

        $(selector).validationEngine(
            "attach",
            $.extend(
                {},
                {
                    scroll: false,
                    showArrow: false,
                    promptPosition: "topLeft:0",
                    focusFirstField: false,
                    autoPositionUpdate: true,
                    addFailureCssClassToField: "validengine-border",
                    onValidationComplete: function (form, status) {
                        return onValidationCompleted(form, status, button);
                    },
                },
                options || {}
            )
        );
    }

    /**
     * Disables the form validation.
     *
     * @param {HTMLElement|JQuery} selector
     */
    function disableFormValidation(selector) {
        var elements = $(selector);
        if (typeof $.fn.validationEngine === "undefined") {
            return elements;
        }

        return elements.validationEngine("detach");
    }

    /**
     * Validates the element.
     *
     * @param {HTMLElement|JQuery|JQuery[]} selector
     * @param {any} [options]
     */
    function validateElement(selector, options) {
        if (typeof $.fn.validationEngine === "undefined") {
            return false;
        }

        var result = $(selector).validationEngine(
            "validate",
            $.extend(
                {
                    scroll: false,
                    showArrow: false,
                    promptPosition: "topLeft:0",
                    focusFirstField: false,
                    autoPositionUpdate: true,
                    addFailureCssClassToField: "validengine-border",
                    onValidationComplete: onValidationCompleted,
                },
                options || {}
            )
        );

        if (typeof result !== "boolean") {
            return false;
        }

        return result;
    }

    /**
     * Escapes HTML entities in text.
     *
     * @param {string} text
     *
     * @returns {string}
     */
    function htmlEscape(text) {
        text = text || "";
        text = text.toString();
        if (text.length === 0) {
            return text;
        }

        var replaceFrom = ["&", "<", ">", '"', "'", "`"];
        var replaceTo = ["&amp;", "&lt;", "&gt;", "&quot;", "&#x27;", "&#x60;"];
        if (new RegExp("(?:" + replaceFrom.join("|") + ")").test(text)) {
            for (var index = 0; index < replaceFrom.length; index++) {
                text = text.replace(new RegExp(replaceFrom[index], "g"), replaceTo[index]);
            }
        }

        return text;
    }
    /**
     * Reloads current page.
     *
     * @deprecated
     */
    function siteNewPage() {
        if (__debug_mode) {
            if (existCookie("_ep_legacy_mode")) {
                removeCookie("_ep_legacy_mode");
            } else {
                setCookie("_ep_legacy_mode", 1, 365);
            }

            document.location.reload(true);
        }

        return false;
    }
    /**
     * Checks if cookie with provided name exists.
     *
     * @param {string} cookieName
     *
     * @returns {boolean}
     */
    function existCookie(cookieName) {
        if (Cookies.get(cookieName) == undefined) {
            return false;
        } else {
            return true;
        }
    }
    /**
     * Sets the specified cookie.
     *
     * @param {string} cookieName
     * @param {any} cookieValue
     * @param {number|Date} expires
     */
    function setCookie(cookieName, cookieValue, expires) {
        Cookies.set(cookieName, cookieValue, {
            path: "/",
            expires: expires,
            domain: __js_cookie_domain,
            secure: !__debug_mode,
        });
    }
    /**
     * Returns the specified cookie.
     *
     * @param {string} cookieName
     *
     * @returns {any}
     */
    function getCookie(cookieName) {
        return Cookies.get(cookieName);
    }
    /**
     * Removes the specified cookie.
     *
     * @param {string} cookieName
     */
    function removeCookie(cookieName) {
        Cookies.remove(cookieName, {
            path: "/",
            domain: __js_cookie_domain,
        });
    }
    /**
     * Sends a HTTP request and returns the response in form of Promise. Is a promisified version of $.ajax() function.
     *
     * @param {string} method
     * @param {string|URL} url
     * @param {any} [data]
     * @param {string} [type=json]
     *
     * @see {@link jQuery.ajax}
     *
     * @returns {Promise<any>}
     */
    function httpRequest(method, url, data, type) {
        var options = {};
        var xhrHandler = null;
        var getTopDomain = function (host) {
            return host.split(".").slice(-2).join(".");
        };

        try {
            var fullUrl = new URL(url);
            if (fullUrl.host !== location.host) {
                options.crossDomain = true;
                options.headers = { "X-Requested-With": "XMLHttpRequest" };
                if (getTopDomain(fullUrl.host) === getTopDomain(location.host)) {
                    options.xhrFields = { withCredentials: true };
                }
            }
        } catch (error) {
            // sadly no url
        }

        var request = new Promise(function (resolve, reject) {
            xhrHandler = $.ajax(Object.assign({}, options, { url: url, type: method.toUpperCase(), data: data, dataType: type || "json" }))
                .fail(function (xhr, status, error) {
                    reject({ message: error, status: status, data: xhr.responseJSON ? xhr.responseJSON.data || null : null, isCustom: true, xhr: xhr });
                })
                .done(function (response, status, xhr) {
                    if (response && ("error" === response.status || "error" === response.mess_type)) {
                        reject({ message: response.message, status: status, data: response.data || null, isCustom: true, xhr: xhr });

                        return;
                    }

                    resolve(response);
                });
        });
        request.xhrHandler = xhrHandler;

        return request;
    }
    /**
     * Sends a HTTP POST request and returns the response in form of Promise. Is a promisified version of $.post() function.
     *
     * @param {string|URL} url
     * @param {any} [data]
     * @param {string} [type=json]
     *
     * @see {@link jQuery.post}
     *
     * @returns {Promise<any>}
     */
    function postRequest(url, data, type) {
        return httpRequest("POST", url, data, type);
    }

    /**
     * Sends a HTTP GET request and returns the response in form of Promise. Is a promisified version of $.get() function.
     *
     * @param {string|URL} url
     * @param {string} [type=json]
     *
     * @see {@link jQuery.get}
     *
     * @returns {Promise<any>}
     */
    function getRequest(url, type) {
        return httpRequest("GET", url, null, type);
    }

    /**
     * Loads the scrips by URL and puts it on the page. Promisified version of $.getScript().
     *
     * @param {string|URL} src
     * @param {boolean} cache
     *
     * @see {@link jQuery.getScript}
     *
     * @returns {Promise<any>}
     */
    function getScript(src, cache) {
        var isCached = typeof cache !== "undefined" ? Boolean(~~cache) : false;

        return new Promise(function (resolve, reject) {
            $.get({ url: src, cache: isCached, dataType: "script" })
                .fail(function (xhr, status, error) {
                    reject({ message: error, status: status, data: xhr.responseJSON ? xhr.responseJSON.data || null : null, isCustom: true, xhr: xhr });
                })
                .done(function (response, status, xhr) {
                    if ("success" !== status) {
                        reject({ message: "Failed to load the script", status: status, data: null, isCustom: true, xhr: xhr });

                        return;
                    }

                    resolve(response);
                });
        });
    }

    /**
     * Loads the styles by URL and puts it in the container. Promisified version of $.getScript().
     *
     * @param {string|URL} src
     * @param {boolean} cache
     *
     * @see {@link jQuery.getScript}
     *
     * @returns {Promise<any>}
     */
    function getStyle(src, cache, container) {
        container = container || "body";
        var isCached = typeof cache !== "undefined" ? Boolean(~~cache) : false;

        return new Promise(function (resolve, reject) {
            $.get({ url: src, cache: isCached, dataType: "text" })
                .fail(function (xhr, status, error) {
                    reject({ message: error, status: status, data: xhr.responseJSON ? xhr.responseJSON.data || null : null, isCustom: true, xhr: xhr });
                })
                .done(function (response, status, xhr) {
                    if ("success" !== status) {
                        reject({ message: "Failed to load the script", status: status, data: null, isCustom: true, xhr: xhr });

                        return;
                    }

                    if (container) {
                        $(container).append($("<style>").text(response));
                    }

                    resolve(response);
                });
        });
    }

    /**
     * Load styles and puts them into the boostrap modal.
     *
     * @param {string|URL} url
     * @param {boolean} [cache]
     */
    function loadDialogStyle(url, cache) {
        if (!BootstrapDialog.dialogs || Object.keys(BootstrapDialog.dialogs).length === 0) {
            return Promise.resolve(null);
        }

        return getStyle(url, cache, ".modal-body");
    }

    /**
     * Load styles and puts them into the fancybox popup.
     *
     * @param {string|URL} url
     * @param {boolean} [cache]
     */
    function loadPopupStyle(url, cache) {
        if (!$.fancybox.current) {
            return Promise.resolve(null);
        }

        return getStyle(url, cache, ".fancybox-inner");
    }

    /**
     * Normalizes width of the provided tables.
     *
     * @param {JQuery} tables
     */
    function normalizeTables(tables) {
        if (tables.length !== 0) {
            if ($(globalThis).width() < 768) {
                tables.addClass("main-data-table--mobile");
            } else {
                tables.removeClass("main-data-table--mobile");
            }
        }
    }

    /**
     * Opens a popup using Boostrap dialog.
     *
     * @param {string} url
     * @param {string} [title=null]
     * @param {any} [data={}]
     *
     * @returns {Promise<{dialog: BootstrapDialog, response: any }>}
     */
    function openPopup(url, title, data) {
        title = title || null;
        data = data || {};
        var formatResponse = function (response) {
            try {
                return JSON.parse(response);
            } catch (e) {
                return response;
            }
        };
        var filterResponse = function (response) {
            if (response && isObject(response)) {
                if (response.mess_type && "success" !== response.mess_type) {
                    throw { isGEneric: true, message: response.message || null, messageType: response.mess_type };
                }
            }

            return response;
        };
        var showPopup = function (dialog, resolve, response) {
            dialog.getMessage().append(response.html ? response.html : response);
            enableFormValidation(dialog.getMessage().find("form.validateModal"));
            hideLoader(dialog.getModalDialog().find(".modal-content"));
            resolve({ dialog: dialog, response: response });
        };

        return new Promise(function (resolve) {
            BootstrapDialog.show({
                title: title,
                tabindex: 0,
                cssClass: "info-bootstrap-dialog dialog-type-popup",
                message: $("<div></div>"),
                onshow: function (dialog) {
                    dialog.getModalDialog().addClass("modal-dialog-centered");
                    showLoader(dialog.getModalDialog().find(".modal-content"), "Loading...");

                    return postRequest(url, data, "text")
                        .then(formatResponse)
                        .then(filterResponse)
                        .then(showPopup.bind(null, dialog, resolve))
                        .catch(onRequestError);
                },
                type: "type-light",
                size: "size-wide",
                closable: true,
                closeByBackdrop: false,
                closeByKeyboard: false,
                draggable: false,
                animate: true,
                nl2br: false,
            });
        });
    }

    /**
     * Shows confirmation dialog.
     *
     * @param {string} message
     * @param {string} [title]
     *
     * @returns {Promise<{ confirm: boolean, dialog: BootstrapDialog }>}
     */
    function askConfirmation(message, title) {
        return new Promise(function (resolve, reject) {
            if (typeof BootstrapDialog === "undefined" || !("BootstrapDialog" in window)) {
                reject(new Error("The 'BootstrapDialog' is not found."));

                return;
            }

            BootstrapDialog.show({
                cssClass: 'bootstrap-dialog--results bootstrap-dialog--results-info',
                title: title || null,
                size: BootstrapDialog.SIZE_NORMAL,
                message: $('<div>'),
                onshow: function(dialog) {
                    var $dialogHeader = dialog.getModalHeader().find('.bootstrap-dialog-header');
                    $dialogHeader.prepend('<div class="bootstrap-dialog-icon-title"><i class="ep-icon ep-icon_info-character fs-25"></i></div>')
                    $dialogHeader.append('<h6 class="bootstrap-dialog-sub-title">' + message + '</h6>');

                    var $modal_dialog = dialog.getModalDialog();
                    $modal_dialog.addClass('modal-dialog-scrollable modal-dialog-centered');
                    $modal_dialog.addClass('bootstrap-dialog--footer-padding');
                    dialog.getModalFooter().show();
                },
                buttons: [
                    {
                        label: translate_js({ plug: "BootstrapDialog", text: "ok" }),
                        cssClass: "btn-success",
                        action: function (dialog) {
                            resolve({ confirm: true, dialog: dialog });
                        },
                    },
                    {
                        label: translate_js({ plug: "BootstrapDialog", text: "cancel" }),
                        action: function (dialog) {
                            resolve({ confirm: false, dialog: dialog });
                        },
                    },
                ],
                closable: true,
                closeByBackdrop: true,
                closeByKeyboard: false,
                draggable: false,
                animate: true,
                nl2br: false,
            });

            // open_result_modal({
            //     title: title || null,
            //     subTitle: message,
            //     classes: 'confirm-remove-featured',
            //     buttons: [
            //         {
            //             label: translate_js({ plug: "BootstrapDialog", text: "ok" }),
            //             cssClass: "btn-success",
            //             action: function (dialog) {
            //                 resolve({ confirm: true, dialog: dialog });
            //             },
            //         },
            //         {
            //             label: translate_js({ plug: "BootstrapDialog", text: "cancel" }),
            //             action: function (dialog) {
            //                 resolve({ confirm: false, dialog: dialog });
            //             },
            //         },
            //     ],
            // });
        });
    }
    /**
     * Aligns height of the table cells.
     *
     * @param {JQuery} allRows
     * @param {JQuery} fixedRows
     * @param {JQuery} dynamicColumns
     * @param {string} dynamicCellSelector
     */
    function alignCellHeight(allRows, fixedRows, dynamicColumns, dynamicCellSelector) {
        var rows = allRows instanceof jQuery ? allRows.toArray() : [];
        var fixed = fixedRows instanceof jQuery ? fixedRows.toArray() : [];
        var columns = dynamicColumns instanceof jQuery ? dynamicColumns.toArray() : [];
        var cellSelector = dynamicCellSelector || null;

        rows.forEach(function (element) {
            $(element).css({ height: "auto" });
        });
        fixed.forEach(function (element, index) {
            var row = $(element);
            var maxHeight = row.height();

            // Let's find max height
            if (null !== cellSelector) {
                columns.forEach(function (element) {
                    var cell = $(element).find(cellSelector).eq(index);
                    if (cell.length) {
                        var cellHeight = cell.height();
                        if (cellHeight > maxHeight) {
                            maxHeight = cellHeight;
                        }
                    }
                });
            }

            // ...and set the max height to everyone
            row.height(maxHeight);
            columns.forEach(function (element) {
                var cell = $(element).find(cellSelector).eq(index);
                if (cell.length) {
                    cell.height(maxHeight);
                }
            });
        });
    }
    /**
     * Updates DataTables instances in the window.
     *
     * @param {boolean} refilter
     */
    function updateDataTables(refilter) {
        refilter = typeof refilter !== "undefined" ? refilter : true;

        if ($.fn.dataTable) {
            $.fn.dataTable.tables().forEach(function (table) {
                $(table).dataTable().fnDraw(refilter);
            });
        }
    }
    /**
     * Handles lazy loading of the images.
     */
    // function lazyLoadImages() {
    //     if (typeof IntersectionObserver === "undefined") {
    //         $("img.js-lzy-img").each(function () {
    //             var $this = $(this);
    //             var src = $this.data("src");

    //             $this.attr("src", src);
    //         });
    //         console.warn("IntersectionObserver API is not supported by this browser.");
    //         return;
    //     }

    //     var imageObserver = new IntersectionObserver(function (entries, imgObserver) {
    //         entries.forEach(function (entry) {
    //             if (entry.isIntersecting) {
    //                 var lazyImage = entry.target;

    //                 lazyImage.src = lazyImage.dataset.src;
    //                 lazyImage.classList.remove("js-lzy-img");
    //                 imgObserver.unobserve(lazyImage);
    //             }
    //         });
    //     });

    //     if (document.querySelectorAll("img.js-lzy-img").length) {
    //         document.querySelectorAll("img.js-lzy-img").forEach(function (v) {
    //             imageObserver.observe(v);
    //         });
    //     }
    // }
    /**
     * Handles click callback invocation.
     *
     * @param {Event|JQuery.Event} e
     *
     * @returns {boolean}
     */
    function invokeCallback(e) {
        e.preventDefault();
        var self = $(this);
        var callBack = self.data("callback");
        callFunction(callBack, self);

        return false;
    }

    /**
     * Finds value in the object using dot notation.
     *
     * @param {any} object
     * @param {string} path
     * @param {any} [value]
     *
     * @returns {any}
     */
    function dotIndex(object, path, value) {
        if (typeof path == "string") {
            return dotIndex(object, path.split("."), value);
        }

        if (path.length == 1 && typeof value !== "undefined") {
            return (object[path[0]] = value);
        }

        if (path.length == 0) {
            return object;
        }

        return dotIndex(object[path[0]], path.slice(1), value);
    }
    /**
     * Renders the template.
     *
     * @param {string} template
     * @param {any} [context]
     * @param {any} [partials]
     *
     * @returns {string}
     */
    function renderTemplate(template, context, partials) {
        context = context || {};
        partials = partials || {};
        var match;
        var pattern = /\{\{([a-zA-Z0-9\.]+)\}\}/gi;
        var partialsPattern = /\{\{> ([a-zA-Z0-9]+)\}\}/gi;
        var interpolatedTemplate = template;

        while ((match = pattern.exec(template)) !== null) {
            if (!match[1]) {
                continue;
            }

            var key = match[1];
            var value = dotIndex(context, key);
            if (typeof value !== "undefined") {
                interpolatedTemplate = interpolatedTemplate.replace("{{" + key + "}}", value);
            }
        }

        while ((match = partialsPattern.exec(interpolatedTemplate)) !== null) {
            if (!match[1]) {
                continue;
            }

            var key = match[1];
            var value = dotIndex(partials, key);
            if (value && typeof value.template === "string") {
                interpolatedTemplate = interpolatedTemplate.replace(
                    "{{> " + key + "}}",
                    renderTemplate(value.template, value.context || {}, value.partials || {})
                );
            } else {
                throw new Error('The partial "' + key + '" is not found.');
            }
        }

        return interpolatedTemplate;
    }
    /**
     * Shows the system message.
     *
     * @param {string} messages
     * @param {string} [type=error]
     */
    function systemMessages(messages, type) {
        var preparedMessages = [];
        var messageType = (type || "error").replace(/^message\-/, "");
        var variableType = typeof messages;
        var messagesList = $(".system-messages__cards");
        var shownMessages = $(".system-messages");
        var typeMetadata = {
            info: { text: "Info", class: "info" },
            error: { text: "Error", class: "error" },
            warning: { text: "Warning", class: "warning" },
            success: { text: "Success", class: "success" },
        };
        var builders = {
            string: function (messages) {
                return [messages];
            },
            object: function (messages) {
                return Object.values(messages);
            },
        };
        var hasType = typeMetadata.hasOwnProperty(messageType);
        var typeText = hasType ? typeMetadata[messageType].text : typeMetadata.error.text;
        var typeClass = hasType ? typeMetadata[messageType].class : typeMetadata.error.class;
        var messageTemplate =
            '<li class="system-messages__card system-messages__card--{{class}}">' +
            '<div class="system-messages__card-ttl">' +
            "<strong>{{type}}</strong>" +
            '<i class="ep-icon ep-icon_remove-stroke call-function" data-callback="systemMessagesCardClose"></i>' +
            "</div>" +
            '<div class="system-messages__card-txt">' +
            "{{message}}" +
            "</div>" +
            "</li>";

        if (builders.hasOwnProperty(variableType)) {
            preparedMessages = builders[variableType].call(null, messages);
        } else {
            preparedMessages = Array.from(messages);
        }
        if (shownMessages.length && !shownMessages.is(":visible")) {
            shownMessages.fadeIn("fast");
        }

        var $ttl = $('.system-messages__ttl');
        if (!$ttl.hasClass('call-function')) {
            $ttl.addClass("call-function").data("callback", "systemMessagesClose");
        }

        preparedMessages.forEach(function (message) {
            messagesList.prepend(renderTemplate(messageTemplate, { type: typeText, class: typeClass, message: message }));
            messagesList
                .children(".system-messages__card")
                .first()
                .addClass("flipInX")
                .show()
                .delay(20000)
                .slideUp("slow", function () {
                    if (1 === messagesList.children(".system-messages__card").length) {
                        messagesList.closest(".system-messages").slideUp();
                    }

                    $(this).remove();
                });
        });
    }
    /**
     * Closes system messages queue.
     */
    function systemMessagesClose() {
        $(".system-messages")
            .clearQueue()
            .fadeOut("slow", function () {
                $(this).find(".system-messages__cards").empty();
            });
    }
    /**
     * Closes the system message
     *
     * @param {JQuery} element
     */
    function systemMessagesCardClose(element) {
        var message = element.closest(".system-messages__card");
        message.clearQueue();
        message.slideUp("slow", function () {
            if (1 === $(".system-messages__card").length) {
                $(".system-messages").slideUp();
            }

            message.remove();
        });
    }
    /**
     * Opens share popup.
     *
     * @param {string|HTMLElement|JQuery} obj
     */
    function popup_share(obj) {
        var $this = $(obj);
        var popupUrl = "";
        var popupTitle = "";
        var popupOptions = "width=550,height=400,0,status=0";
        var socialUrl = $this.data("url");
        var socialTitle = encodeURIComponent(htmlEscape($this.data("title")));
        switch ($this.data("social")) {
            case "facebook":
                popupUrl += "https://www.facebook.com/share.php?u=" + socialUrl + "&title=" + socialTitle;
                popupTitle = "Share with facebook";
                break;
            case "twitter":
                popupUrl += "https://twitter.com/intent/tweet?text=" + socialTitle + "&url=" + socialUrl;
                popupTitle = "Share with twitter";
                break;
            case "pinterest":
                popupUrl +=
                    "https://pinterest.com/pin/create/bookmarklet/?media=" +
                    $this.data("img") +
                    "&url=" +
                    socialUrl +
                    "&is_video=false&description=" +
                    socialTitle;
                popupTitle = "Share with pinterest";
                popupOptions = "width=750,height=400,0,status=0";
                break;
            case "linkedin":
                popupUrl += "https://www.linkedin.com/shareArticle?mini=true&url=" + socialUrl + "&title=" + socialTitle;
                popupTitle = "Share with linkedin";
                break;
        }

        winPopup(popupUrl, popupTitle, popupOptions);
    }
    /**
     * Opens share popup for friends invite.
     *
     * @param {string|HTMLElement|JQuery} obj
     */
    function popup_friend_invite(obj) {
        var $this = $(obj);
        var popupUrl = "";
        var popupTitle = "";
        var viewportwidth = document.documentElement.clientWidth;
        var left = viewportwidth;
        var popupOptions = "width=550,height=400,status=0,left=" + left;
        var socialUrl = $this.data("url");
        var socialText = $this.data("text");
        switch ($this.data("social")) {
            case "facebook":
                popupUrl += "https://www.facebook.com/share.php?u=" + socialUrl + "&quote=" + socialText;
                popupTitle = "Share with facebook";
                break;
            case "twitter":
                popupUrl += "https://twitter.com/intent/tweet?text=" + socialText;
                popupTitle = "Share with twitter";
                break;
            case "linkedin":
                popupUrl += "https://www.linkedin.com/shareArticle?mini=true&url=" + socialUrl;
                popupTitle = "Share with linkedin";
                break;
        }

        winPopup(popupUrl, popupTitle, popupOptions);
    }
    /**
     * Opens new window.
     *
     * @param {string|URL} [mylink]
     * @param {string} [title]
     * @param {any} [options]
     */
    function winPopup(mylink, title, options) {
        // open the window with blank url
        var mywin = globalThis.open(mylink, title, options);
        mywin.focus();

        // return the window
        return mywin;
    }
    /**
     * Returns the handler for event with prevented deafult action.
     *
     * @param {Function} fn
     *
     * @returns {Function}
     */
    function preventDefault(fn) {
        return function (e) {
            e.preventDefault();

            var args = [];
            for (var argsLength = arguments.length, args = new Array(argsLength > 1 ? argsLength - 1 : 0), key = 1; key < argsLength; key++) {
                args[key - 1] = arguments[key];
            }

            return fn.apply(this, [e].concat(args));
        };
    }
    /**
     * Checks if window width is less than provided value
     *
     * @param {number} width
     *
     * @returns {boolean}
     */
    function widthLessThan(width) {
        width = width || 0;

        return globalThis.innerWidth < width;
    }
    /**
     * Closes closest to the element Bootstrap Dialog
     *
     * @param {JQuery} [element]
     */
    function closeBootstrapDialog(element) {
        if (!("BootstrapDialog" in globalThis)) {
            return;
        }

        var id = null !== element ? element.closest(".modal.bootstrap-dialog").prop("id") || null : null;
        var dialog = null !== id ? BootstrapDialog.getDialog(id) || null : null;
        if (null !== dialog) {
            dialog.close();
        }
    }
    /**
     * Closes current fancybox instance.
     */
    function closeFancyboxPopup() {
        if (typeof $.fancybox !== "undefined") {
            $.fancybox.close();
        }
    }
    /**
     * Returns the elements for provided selectors and subset.
     *
     * @param {{[x: string]: string|HTMLElement}} selectors
     * @param {Array<string>} keySubset
     *
     * @returns {{[x: string]: JQuery}}
     */
    function findElementsFromSelectors(selectors, keySubset) {
        var elements = {};
        var allowedKeys = keySubset || [];
        var selectorKeys = Object.keys(selectors);
        var filterElements = typeof keySubset !== "undefined";

        for (var index = 0; index < selectorKeys.length; index++) {
            var key = selectorKeys[index];
            if (filterElements && -1 === allowedKeys.indexOf(key)) {
                continue;
            }

            if (selectors.hasOwnProperty(key) && selectors[key]) {
                var selector = selectors[key];
                var element = $(selector);
                if (element.length) {
                    elements[key] = element;
                }
            }
        }

        return elements;
    }
    /**
     * Opens popup where user can indicate his location.
     *
     * @param {any} initParams
     * @param {any} address
     * @param {Function} [callbackAfterShowModal]
     */
    function openLocationPopup(initParams, address, callbackAfterShowModal) {
        return new Promise(function (resolve, reject) {
            var titleModal = initParams.title || "Add location";
            var addressShow = initParams.address || false;
            var postalCodeShow = initParams.postalCode || false;

            var onSubmit = function (dialog, form, data) {
                var location = new Object();

                (data || []).forEach(function (entry) {
                    location[entry.name] = { value: entry.value, name: "" };
                    var $input = form.find("[name='" + entry.name + "']");

                    if ($input.length && $input.prop("type") == "select-one") {
                        if (entry.name != "city") {
                            location[entry.name].name = $input.find("option:selected").text().trim();
                        } else {
                            location[entry.name].name = $input.next("span").find(".select2-selection__rendered").text().trim();
                        }
                    } else {
                        location[entry.name].name = $input.val().trim();
                    }
                });

                resolve(location);
                dialog.close();
            };

            var url = __site_url + "location/popup_forms/get_location";
            var params = { postal_code_show: postalCodeShow, address_show: addressShow };
            postRequest(url, Object.assign({}, address, params))
                .then(function (response) {
                    if (response.mess_type && "success" !== response.mess_type) {
                        systemMessage(response.message, response.mess_type);
                        reject(null);

                        return;
                    }

                    BootstrapDialog.show({
                        tabindex: 0,
                        title: titleModal,
                        cssClass: "info-bootstrap-dialog",
                        message: $("<div></div>"),
                        onshow: function (dialog) {
                            dialog.getModalDialog().css("max-width", "425px");
                            dialog.getMessage().append(response.html);
                            dialog
                                .getMessage()
                                .find("form.validateModal")
                                .data("callback", function () {
                                    onSubmit(
                                        dialog,
                                        dialog.getMessage().find("form.validateModal"),
                                        dialog.getMessage().find("form.validateModal").serializeArray()
                                    );
                                });

                            enableFormValidation(dialog.getMessage().find("form.validateModal"));

                            var $formLocation = dialog.getMessage().find(".js-global-location-form");
                            var $selectCity = $formLocation.find("#js-location-port-city");
                            var $selectCountry = $formLocation.find("#js-location-country-states");
                            var selectState = $selectCountry.val() || "";

                            $selectCity.select2({
                                ajax: {
                                    type: "POST",
                                    url: __current_sub_domain_url + "location/ajax_get_cities",
                                    dataType: "json",
                                    delay: 250,
                                    data: function (params) {
                                        return {
                                            search: params.term, // search term
                                            page: params.page,
                                            state: selectState,
                                        };
                                    },
                                    beforeSend: function (xhr, opts) {},
                                    processResults: function (data, params) {
                                        params.page = params.page || 1;

                                        return {
                                            results: data.items,
                                            pagination: {
                                                more: params.page * data.per_p < data.total_count,
                                            },
                                        };
                                    },
                                },
                                dropdownParent: $formLocation.closest(".modal"),
                                language: __site_lang,
                                theme: "default ep-select2-h30",
                                width: "100%",
                                placeholder: translate_js({ plug: "general_i18n", text: "form_placeholder_select2_state_first" }),
                                minimumInputLength: 2,
                                escapeMarkup: function (markup) {
                                    return markup;
                                },
                                templateResult: formatCity,
                                templateSelection: formatCitySelection,
                            });

                            function formatCity(repo) {
                                if (repo.loading) return repo.text;

                                var markup = repo.name;

                                return markup;
                            }

                            function formatCitySelection(repo) {
                                return repo.name || repo.text;
                            }

                            if ($selectCity.find("option").length < 2) {
                                $selectCity.prop("disabled", true);
                            }

                            $selectCity
                                .data("select2")
                                .$container.attr("id", "select-сity--formfield--location-container")
                                .addClass("validate[required]")
                                .setValHookType("selectCityLocation");

                            $.valHooks.selectCityLocation = {
                                get: function (el) {
                                    return $selectCity.val() || [];
                                },
                            };

                            $formLocation.on("change", "#js-location-country", function () {
                                showLoader($formLocation);

                                $.ajax({
                                    type: "POST",
                                    dataType: "JSON",
                                    url: __current_sub_domain_url + "location/ajax_get_states",
                                    data: { country: $(this).val() },
                                    success: function (resp) {
                                        hideLoader($formLocation);
                                        $selectCountry.html(resp.states);
                                    },
                                });

                                selectState = 0;
                                $selectCity.empty().trigger("change").prop("disabled", true);
                            });

                            $formLocation.on("change", "#js-location-country-states", function () {
                                selectState = this.value;
                                $selectCity.empty().trigger("change").prop("disabled", false);

                                if (selectState != "" || selectState != 0) {
                                    var select_text = translate_js({ plug: "general_i18n", text: "form_placeholder_select2_city" });
                                } else {
                                    var select_text = translate_js({ plug: "general_i18n", text: "form_placeholder_select2_state_first" });
                                    $selectCity.prop("disabled", true);
                                }
                                $selectCity.siblings(".select2").find(".select2-selection__placeholder").text(select_text);
                            });

                            if (typeof callbackAfterShowModal == "function") {
                                callbackAfterShowModal();
                            }
                        },
                        onhide: function () {
                            reject(null);
                        },
                        onshown: function () {
                            $('.js-add-location-btn').attr('atas', 'items-my-add__another-location__add-btn');
                        },
                        buttons: [
                            {
                                label: "Add location",
                                cssClass: "btn-primary mnw-130 js-add-location-btn",
                                action: function (dialog) {
                                    dialog.getMessage().find(".validateModal").trigger("submit");
                                },
                            },
                        ],
                        type: "type-light",
                        size: "size-wide",
                        closable: true,
                        closeByBackdrop: false,
                        closeByKeyboard: false,
                        draggable: false,
                        animate: true,
                        nl2br: false,
                    });
                })
                .catch(function (error) {
                    onRequestError(error);

                    reject(null);
                });
        });
    }
    /**
     * Handles the open of the popup dialog.
     */
    function onPopupOpen() {
        var self = $(this);
        var data = self.data() || {};
        var url = data.url || data.href || null;
        if (null === url) {
            return;
        }

        openPopup(url, data.title || null, data.params || {});
    }

    /**
     * Adds a textcounter to the element.
     *
     * @param {HTMLElement|JQuery} selector
     * @param {any} options
     */
    function addCounter (selector, options) {
        var elements = $(selector);
        var textPrefix = translate_js({ plug: "textcounter", text: "count_down_text_before" });
        var textPostfix = translate_js({ plug: "textcounter", text: "count_down_text_after" });
        var countDownText = textPrefix + "%d " + textPostfix;

        elements.toArray().forEach(function (e) {
            var node = $(e);

            node.textcounter(
                Object.assign(
                    {},
                    {
                        max: Number(node.data("max") || 200),
                        min: Number(node.data("min") || 0),
                        countContainerClass: "textcounter-wrapper",
                        textCountClass: "textcounter",
                        countDown: true,
                        countSpaces: true,
                        countDownText: countDownText,
                    },
                    options || {}
                )
            );
        });

        return elements;
    };

    /**
     * Creates an iCheck for element
     *
     * @param {HTMLElement|JQuery} selector
     * @param {any} options
     */
    function createIcheck (selector, options) {
        /** @type {JQuery} */
        var element = $(selector);

        element.icheck(Object.assign(
            {},
            {
                checkboxClass: "icheckbox icheckbox--20 icheckbox--blue",
                radioClass: "icheckbox icheckbox--20 icheckbox--radio icheckbox--blue",
                callbacks: {
                    ifCreated: true,
                }
            },
            options
        ));

        return element;
    };

    /**
     * Initializes the datepicker for given selector.
     *
     * @param {string} selector
     * @param {any} options
     */
    function createDatepicker(selector, options) {
        var datepickerOptions = options || {};
        var elements = $(document.querySelectorAll(selector));

        setTimeout(function () {
            elements.datepicker(
                Object.assign(
                    {},
                    {
                        beforeShow: function (input, instance) {
                            instance.dpDiv.addClass("dtfilter-ui-datepicker");
                        },
                    },
                    datepickerOptions
                )
            );
        }, 0);

        return elements;
    }

    /**
     * Dispatches pure custom event into the provided element.
     *
     * @param {string} type
     * @param {HTMLElement} element
     * @param {CustomEventInit} params
     *
     * @returns {boolean}
     */
    function dispatchCustomEvent(type, element, params) {
        var event;
        var eventParams = Object.assign({ bubbles: true, cancelable: true, detail: undefined }, params);

        try {
            event = new CustomEvent(type, eventParams);
        } catch (error) {
            event = document.createEvent("CustomEvent");
            event.initCustomEvent(type, eventParams.bubbles, eventParams.cancelable, eventParams.detail);

            return true;
        }

        return element.dispatchEvent(event);
    };
    //#endregion Functions

    function isLoggedIn() {
        return new Promise(function (resolve, reject) {
            var url = __site_url + "user/ajax_user_operation/logged_in";

            postRequest(url, {})
                .then(function (response) {
                    if (response.mess_type && "success" !== response.mess_type) {
                        reject(null);

                        return;
                    }else{
                        resolve(response);
                    }
                })
                .catch(function (error) {
                    onRequestError(error);

                    reject(null);
                });
        });
    }

    function openAttachFilesDialog(roomId, userId) {
        if (null === roomId || null === userId) {
            throw new ReferenceError("Both 'roomId' and 'userId' are required to open the dialog");
        }

        BootstrapDialog.show({
            type: "type-light",
            size: "size-wide",
            title: "Attach files",
            closable: true,
            cssClass: "info-bootstrap-dialog inputs-40",
            closeByBackdrop: false,
            closeByKeyboard: false,
            draggable: false,
            animate: false,
            nl2br: false,
            onshow: function (dialog) {
                var modalDialog = dialog.getModalDialog();
                modalDialog.addClass("modal-dialog-centered");
                dialog.getModalBody().addClass("mnh-100");
                showLoader(dialog.getModalBody(), "Loading...");

                $.get(__group_site_url + "/chats/popupForms/attachFiles").done(function (response) {
                    dialog.getModalBody().html(response);
                    dialog
                        .getModalFooter()
                        .append(
                            '<button ' +
                                'id="js-chat-app-attach-files-modal-dialog" ' +
                                'class="btn btn-primary mnw-130 call-function" ' +
                                'data-callback="attachFilesToMessage" ' +
                                'data-user="' + userId + '" ' +
                                'data-room="' + roomId + '" ' +
                                'disabled>' +
                                'Send file(s)' +
                            '</button>'
                        )
                        .css({ display: "flex" });
                });
            }
        });
    }

    //#region Event handling
    // $("body").on("click", ".call-function:not(.disabled)", invokeCallback);
    // document.addEventListener("DOMContentLoaded", lazyLoadImages);
    $(function () {
        $("body").on("click", ".popup-dialog", preventDefault(onPopupOpen));
        globalThis.addEventListener("chat-client:open-attach-files-dialog", function (e) {
            openAttachFilesDialog(e.detail.roomId || null, e.detail.userId || null);
        });
        globalThis.addEventListener("chat-client:close-all-popups", function () {
            $.fancybox.close();
            BootstrapDialog.closeAll();
        });
    });
    //#endregion Event handling

    //#region Export
    mix(globalThis, {
        promisify: promisify,
        mix: mix,
        isIe: isIe,
        isIoS: isIoS,
        isSafari: isSafari,
        isMobile: isMobile,
        isObject: isObject,
        isAndroid: isAndroid,
        isChromeIoS: isChromeIoS,
        isEmptyObject: isEmptyObject,
        siteNewPage: siteNewPage,
        removeCookie: removeCookie,
        existCookie: existCookie,
        domainUrl: domainUrl,
        setCookie: setCookie,
        getCookie: getCookie,
        htmlEscape: htmlEscape,
        callFunction: callFunction,
        onRequestError: onRequestError,
        httpRequest: httpRequest,
        postRequest: postRequest,
        getRequest: getRequest,
        getScript: getScript,
        getStyle: getStyle,
        openPopup: openPopup,
        loadPopupStyle: loadPopupStyle,
        loadDialogStyle: loadDialogStyle,
        askConfirmation: askConfirmation,
        runFormTracking: runFormTracking,
        onValidationCompleted: onValidationCompleted,
        enableFormValidation: enableFormValidation,
        disableFormValidation: disableFormValidation,
        validateElement: validateElement,
        updateDataTables: updateDataTables,
        normalizeTables: normalizeTables,
        alignCellHeight: alignCellHeight,
        renderTemplate: renderTemplate,
        dotIndex: dotIndex,
        popup_share: popup_share,
        popup_friend_invite: popup_friend_invite,
        winPopup: winPopup,
        preventDefault: preventDefault,
        closeBootstrapDialog: closeBootstrapDialog,
        closeFancyboxPopup: closeFancyboxPopup,
        widthLessThan: widthLessThan,
        openLocationPopup: openLocationPopup,
        findElementsFromSelectors: findElementsFromSelectors,
        addCounter: addCounter,
        createIcheck: createIcheck,
        createDatepicker: createDatepicker,
        isLoggedIn: isLoggedIn,
        dispatchCustomEvent: dispatchCustomEvent,
    });

    mix(
        globalThis,
        {
            systemMessages: systemMessages,
            systemMessagesClose: systemMessagesClose,
            systemMessagesCardClose: systemMessagesCardClose,
        },
        false
    );
    //#endregion Export
})();
