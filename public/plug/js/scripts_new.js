// BEGIN LivePerson Monitor
//window.lpTag=window.lpTag||{};if(typeof window.lpTag._tagCount==='undefined'){window.lpTag={site:'74080141'||'',section:lpTag.section||'',autoStart:lpTag.autoStart===false?false:true,ovr:lpTag.ovr||{},_v:'1.6.0',_tagCount:1,protocol:'https:',events:{bind:function(app,ev,fn){lpTag.defer(function(){lpTag.events.bind(app,ev,fn);},0);},trigger:function(app,ev,json){lpTag.defer(function(){lpTag.events.trigger(app,ev,json);},1);}},defer:function(fn,fnType){if(fnType==0){this._defB=this._defB||[];this._defB.push(fn);}else if(fnType==1){this._defT=this._defT||[];this._defT.push(fn);}else{this._defL=this._defL||[];this._defL.push(fn);}},load:function(src,chr,id){var t=this;setTimeout(function(){t._load(src,chr,id);},0);},_load:function(src,chr,id){var url=src;if(!src){url=this.protocol+'//'+((this.ovr&&this.ovr.domain)?this.ovr.domain:'lptag.liveperson.net')+'/tag/tag.js?site='+this.site;}var s=document.createElement('script');s.setAttribute('charset',chr?chr:'UTF-8');if(id){s.setAttribute('id',id);}s.setAttribute('src',url);document.getElementsByTagName('head').item(0).appendChild(s);},init:function(){this._timing=this._timing||{};this._timing.start=(new Date()).getTime();var that=this;if(window.attachEvent){window.attachEvent('onload',function(){that._domReady('domReady');});}else{window.addEventListener('DOMContentLoaded',function(){that._domReady('contReady');},false);window.addEventListener('load',function(){that._domReady('domReady');},false);}if(typeof(window._lptStop)=='undefined'){this.load();}},start:function(){this.autoStart=true;},_domReady:function(n){if(!this.isDom){this.isDom=true;this.events.trigger('LPT','DOM_READY',{t:n});}this._timing[n]=(new Date()).getTime();},vars:lpTag.vars||[],dbs:lpTag.dbs||[],ctn:lpTag.ctn||[],sdes:lpTag.sdes||[],ev:lpTag.ev||[]};lpTag.init();}else{window.lpTag._tagCount+=1;}
// END LivePerson Monitor

var scrollupState = true;
var widthBrowser = $(window).width();
var heightBrowser = $(window).height();
var fancyW = "70%";
var fancyH = "auto";
var fancyWAlter = 0.7;
var fancyWPr = 0.7;
var fancyP = 30;
var fancyMW = 700;
var scrollTopContent;

$.lockBody = function() {
    if(window.pageYOffset) {
        scrollTopContent = window.pageYOffset;

        $('.ep-content').css({top: -(scrollTopContent)});
    }

    $('html, body').css({
        height: "100%",
        overflow: "hidden"
    });
}

$.unlockBody = function() {
    $('html, body').css({
        height: "",
        overflow: ""
    });

    $('.ep-content').css({
        top: ''
    });

    window.scrollTo(0, scrollTopContent);
    window.setTimeout(function () {
        scrollTopContent = null;
    }, 0);
}

$(function () {
    inputAutocompliteOff();

    myInitFancybox();

    // SET USER LOCAL TIMEZONE
    check_user_tz();

    // Set global request headers for AJAX
    $.ajaxSetup({
        headers: {
            "X-User-Language": __site_lang,
            "X-Requested-With": "XMLHttpRequest",
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            "X-Script-Mode": "legacy",
        },
    });

    //    validationEngine
    $.fn.validationEngineLanguage = function () {};
    $.validationEngineLanguage = {
        newLang: function () {
            $.validationEngineLanguage.allRules = translate_js_lang({ plug: "validationEngine" });
        },
    };
    $.validationEngineLanguage.newLang();
    //    END validationEngine

    // GLOBAL SCROLL WITH PREVENTINT ON SCROLL USER
    var windowIsScrolled = false;
    var htmlBody = $("html, body");
    var scrollToAncor = function (target) {
        var menuHeight = window.matchMedia("(max-width:991px)").matches ? $(".js-mep-user-line").outerHeight() : $(".js-main-user-line").outerHeight();
        htmlBody.animate(
            {
                scrollTop: target.offset()["top"] - menuHeight - parseInt(target.find(">:first-child").css("marginTop")),
            },
            500,
            function () {
                windowIsScrolled = false;
            }
        );
    };
    $(document).on("click", ".js-achor-link", function (e) {
        e.preventDefault();
        windowIsScrolled = true;
        var target = $($(this).attr("href"));

        if ($.fancybox.isOpen) {
            $.fancybox.close();
            setTimeout(function () {
                htmlBody.stop();
                scrollToAncor(target);
            }, 500);
        } else {
            scrollToAncor(target);
        }
    });

    htmlBody.on("mousedown wheel DOMMouseScroll mousewheel touchmove", function () {
        if (!windowIsScrolled) return;
        htmlBody.stop();
    });
    // END GLOBAL SCROLL WITH PREVENTINT ON SCROLL USER

    $("body").on("click", ".call-click", function () {
        var href = $(this).closest(".call-click-wr").find("a.call-click-link").attr("href");

        window.location.href = href;
    });

    $.ajaxSetup({
        xhrFields: {
            withCredentials: true,
        },
    });

    objectFitImages();

    $("body").on("click", ".call-function:not(.disabled)", function (e) {
        e.preventDefault();
        var $thisBtn = $(this);
        var callBack = $thisBtn.data("callback");
        callFunction(callBack, $thisBtn);
        return false;
    });

    $("body").on("click", ".call-function.disabled", function (e) {
        e.preventDefault();
        return false;
    });

    $("body").on("click", ".call-systmess", function (e) {
        e.preventDefault();
        var $thisBtn = $(this);
        var mess = $thisBtn.data("message");
        var type = $thisBtn.data("type");
        systemMessages(mess, type);

        return false;
    });

    $("body").on("click", ".js-require-logout-systmess", function (e) {
        e.preventDefault();
        systemMessages(translate_js({ plug: "general_i18n", text: "systmess_error_should_be_logout" }), "info");
    });

    $("body").on("click", ".js-require-logged-systmess", function (e) {
        e.preventDefault();
        systemMessages(translate_js({ plug: "general_i18n", text: "systmess_error_should_be_logged_in" }), "info");

        return false;
    });

    $("body").on("change", ".js-pseudo-radio-btn input[type=\"radio\"]", function(){
        $(".js-pseudo-radio-btn input[name=" + $(this).attr("name") + "]").not(this).parent().removeClass("selected");
        $(this).parent().addClass("selected");
    });

    if ($.datepicker) {
        $.datepicker._gotoToday = function (id) {
            var target = $(id);
            var inst = this._getInst(target[0]);
            if (this._get(inst, "gotoCurrent") && inst.currentDay) {
                inst.selectedDay = inst.currentDay;
                inst.drawMonth = inst.selectedMonth = inst.currentMonth;
                inst.drawYear = inst.selectedYear = inst.currentYear;
            } else {
                var date = new Date();
                inst.selectedDay = date.getDate();
                inst.drawMonth = inst.selectedMonth = date.getMonth();
                inst.drawYear = inst.selectedYear = date.getFullYear();
                // the below two lines are new
                this._setDateDatepicker(target, date);
                this._selectDate(id, this._getDateDatepicker(target));
            }
            this._notifyChange(inst);
            this._adjustDate(target);
        };
    }

    var googleAnalyticsData = {};
    $(".fancyboxAddItem").fancybox(
        {
            width: "100%",
            height: "100%",
            maxWidth: 762,
            autoSize: false,
            loop: false,
            helpers: {
                title: {
                    type: "inside",
                    position: "top",
                },
                overlay: {
                    locked: true,
                },
            },
            lang: __site_lang,
            i18n: translate_js_one({ plug: "fancybox" }),
            modal: true,
            closeBtn: true,
            padding: fancyP,
            closeBtnWrapper: ".fancybox-skin .fancybox-title",
            afterShow: function () {
                // if ( applePie() ) {
                $("body").css({ position: "fixed" });
                // }
            },
            beforeClose: function () {
                var stepsNode = this.wrap.find("#js-add-item-nav-tabs li");
                var notCompetedSteps = [];
                var closedStep = "";

                $.each(stepsNode, function (i) {
                    var step = $(this).find("a");
                    closedStep = step.hasClass("active") ? step.data().name : closedStep;
                    if (!$(this).hasClass("complete")) notCompetedSteps.push(i + 1);
                });

                googleAnalyticsData = {}; // clear object to prevent resend previous
                if ($(".bootstrap-dialog").length && notCompetedSteps.length === stepsNode.length) {
                    closedStep = "terms_conditions";
                } else {
                    var notCompletedStepList = notCompetedSteps.length === stepsNode.length ? "_all" : "(" + notCompetedSteps.join(",") + ")";
                    googleAnalyticsData.notCompletedStepList = notCompletedStepList;
                }
                googleAnalyticsData.closedStep = closedStep;
            },
            afterClose: function () {
                $("body").css({ position: "" });
                if (window.location.search.indexOf("popup_add") >= 0) {
                    history.replaceState(null, "", window.location.origin + window.location.pathname);
                }

                if (this.element.hasClass("js-fancyboxEditItem") || globalThis.callGAEventState) return;
                if (googleAnalyticsData.notCompletedStepList) {
                    callGAEvent("add_item_close_not_completed" + googleAnalyticsData.notCompletedStepList, "add-item");
                }
                callGAEvent("add_item_close_on_" + googleAnalyticsData.closedStep, "add-item");
            },
            beforeLoad: function () {
                fancyWAlter = 1;
                fancyMW = 1170;

                var $elem = this.element;

                // if($elem.data("before-callback") != undefined){
                //     window[$elem.data("before-callback")](this);
                // }

                if ($elem.data("title")) {
                    this.title = '<span class="fancybox-ttl">' + htmlEscape($elem.data("title")) + "</span>";
                }
                // if($elem.data('h')){
                //     this.autoHeight = false;
                //     this.height = $elem.data('h');
                // }

                // if($elem.data('w')){
                //     this.width = $elem.data('w')
                //     this.autoWidth = false;
                // }else{
                //     this.width = fancyW;
                // }

                // if($elem.data('mw')){
                //     this.maxWidth = $elem.data('mw')
                // }

                // if($elem.data('mnh')){
                //     this.minHeight = $elem.data('mnh')
                // }

                if ($elem.data("p") != undefined) {
                    this.padding = [$elem.data("p"), $elem.data("p"), $elem.data("p"), $elem.data("p")];

                    if ($elem.data("p") == 0) {
                        this.wrapCSS = "fancybox-title--close";
                    }
                } else {
                    this.padding = [fancyP, fancyP, fancyP, fancyP];
                }
            },
            ajax: {
                complete: function (jqXHR, textStatus) {
                    var $caller_btn = this.caller_btn;
                    inputAutocompliteOff();
                    $(".validateModal").validationEngine("attach", {
                        promptPosition: "topLeft:0",
                        autoPositionUpdate: true,
                        focusFirstField: false,
                        scroll: false,
                        showArrow: false,
                        addFailureCssClassToField: "validengine-border",
                        onValidationComplete: function (form, status) {
                            if (status) {
                                if ($(form).data("callback") != undefined) window[$(form).data("callback")](form, $caller_btn);
                                else modalFormCallBack(form, $caller_btn);
                            } else {
                            }
                        },
                    });
                },
            },
        },
        ".fancyboxAddItem"
    );

    $(".fancyboxValidateStepsForm").fancybox(
        {
            width: "100%",
            height: "100%",
            maxWidth: 1170,
            autoSize: false,
            loop: false,
            helpers: {
                title: {
                    type: "inside",
                    position: "top",
                },
                overlay: {
                    locked: true,
                },
            },
            lang: __site_lang,
            i18n: translate_js_one({ plug: "fancybox" }),
            modal: true,
            closeBtn: true,
            padding: fancyP,
            closeBtnWrapper: ".fancybox-skin .fancybox-title",
            afterShow: function () {
                $("body").css({ position: "fixed" });
            },
            afterClose: function () {
                $("body").css({ position: "" });
            },
            beforeLoad: function () {
                var $elem = this.element;

                if ($elem.data("title")) {
                    this.title = '<span class="fancybox-ttl">' + htmlEscape($elem.data("title")) + "</span>";
                }

                if ($elem.data("p") != undefined) {
                    this.padding = [$elem.data("p"), $elem.data("p"), $elem.data("p"), $elem.data("p")];

                    if ($elem.data("p") == 0) {
                        this.wrapCSS = "fancybox-title--close";
                    }
                } else {
                    this.padding = [fancyP, fancyP, fancyP, fancyP];
                }
            },
            ajax: {
                complete: function (jqXHR, textStatus) {
                    var $caller_btn = this.caller_btn;
                    inputAutocompliteOff();
                    $(".validateModal").validationEngine("attach", {
                        promptPosition: "topLeft:0",
                        autoPositionUpdate: true,
                        focusFirstField: false,
                        scroll: false,
                        showArrow: false,
                        addFailureCssClassToField: "validengine-border",
                        onValidationComplete: function (form, status) {
                            if (status) {
                                if ($(form).data("callback") != undefined) window[$(form).data("callback")](form, $caller_btn);
                                else modalFormCallBack(form, $caller_btn);
                            } else {
                                systemMessages(translate_js({ plug: "general_i18n", text: "validate_error_message" }), "error");
                            }
                        },
                    });
                },
            },
        },
        ".fancyboxValidateStepsForm"
    );

    $(".fancybox").fancybox(
        {
            width: fancyW,
            height: fancyH,
            maxWidth: 700,
            autoSize: false,
            loop: false,
            helpers: {
                title: {
                    type: "inside",
                    position: "top",
                },
                overlay: {
                    locked: true,
                },
            },
            lang: __site_lang,
            i18n: translate_js_one({ plug: "fancybox" }),
            modal: true,
            padding: fancyP,
            closeBtn: true,
            closeBtnWrapper: ".fancybox-skin .fancybox-title",
            beforeShow: function () {
                var $elem = this.element;

                if ($elem.data("dashboard-class") != undefined) {
                    $(".fancybox-inner").addClass($elem.data("dashboard-class"));
                }
            },
            afterShow: function () {
                var $fancyboxContent = $(".fancybox-inner .modal-flex__content");
                // var $fancyboxContent2 = $('.fancybox-inner .modal-b__content');

                setTimeout(function () {
                    if ($fancyboxContent.length && $fancyboxContent.hasScrollBar()) {
                        $fancyboxContent.addClass("pr-15");
                    }

                    // if($fancyboxContent2.length && $fancyboxContent2.hasScrollBar()){
                    //     $fancyboxContent2.addClass('pr-15');
                    // }
                }, 100);
            },
            beforeLoad: function () {
                var $elem = this.element;

                if ($elem.data("before-callback") != undefined) {
                    window[$elem.data("before-callback")](this);
                }

                if ($elem.data("title")) this.title = htmlEscape($elem.data("title"));

                if ($elem.data("h")) {
                    this.autoHeight = false;
                    this.height = $elem.data("h");
                }

                if ($elem.data("w")) {
                    this.width = $elem.data("w");
                    this.autoWidth = false;
                } else {
                    this.width = fancyW;
                }

                if ($elem.data("mw")) {
                    this.maxWidth = $elem.data("mw");
                }

                if ($elem.data("mnh")) {
                    this.minHeight = $elem.data("mnh");
                }

                if ($elem.data("p") != undefined) {
                    this.padding = [$elem.data("p"), $elem.data("p"), $elem.data("p"), $elem.data("p")];

                    if ($elem.data("p") == 0) {
                        this.wrapCSS = "fancybox-title--close";
                    }
                } else {
                    this.padding = [fancyP, fancyP, fancyP, fancyP];
                }

                const classMod = $elem.data("classModificator");
                if (classMod) {
                    this.wrapCSS = `${this.wrapCSS} ${classMod}`;
                }
            },
        },
        ".fancybox"
    );

    $(".fancyboxGallery").fancybox(
        {
            width: fancyW,
            height: fancyH,
            maxWidth: 900,
            // maxHeight    : 150,
            // fitToView: false,
            autoSize: false,
            loop: false,
            lang: __site_lang,
            preload: 0,
            i18n: translate_js_one({ plug: "fancybox" }),
            helpers: {
                title: {
                    type: "inside",
                    position: "top",
                },
                overlay: {
                    locked: true,
                },
            },
            padding: fancyP,
            closeBtn: true,
            closeBtnWrapper: ".fancybox-skin .fancybox-title",
            tpl: {
                next: '<i class="fancybox-nav fancybox-next"><span title="{{NEXT}}"></span></i>',
                prev: '<i class="fancybox-nav fancybox-prev"><span title="{{PREVIOUS}}"></span></i>'
            },
            beforeLoad: function () {
                var $elem = this.element;
                this.title = "&nbsp;";

                if ($elem.data("title")) {
                    this.title = htmlEscape($elem.data("title"));
                    var image_alt = this.title;
                    if ($elem.data("image-index")) {
                        image_alt += " " + $elem.data("image-index");
                    }

                    this.tpl.image = '<img class="fancybox-image" src="{href}" alt="' + image_alt + '" />';
                }

                if ($elem.data("w")) {
                    this.width = $elem.data("w");
                } else {
                    this.width = fancyW;
                }

                if ($elem.data("mw")) {
                    this.maxWidth = $elem.data("mw");
                }

                if ($elem.data("p") != undefined) {
                    this.padding = [$elem.data("p"), $elem.data("p"), $elem.data("p"), $elem.data("p")];

                    if ($elem.data("p") == 0) {
                        this.wrapCSS = "fancybox-title--close";
                    }
                } else {
                    this.padding = [fancyP, fancyP, fancyP, fancyP];
                }
            },
        },
        ".fancyboxGallery"
    );

    $(".fancyboxVideo").fancybox(
        {
            width: fancyW,
            maxWidth: 700,
            autoSize: false,
            loop: false,
            helpers: {
                title: {
                    type: "inside",
                    position: "top",
                },
                media: {},
                overlay: {
                    locked: true,
                },
            },
            lang: __site_lang,
            i18n: translate_js_one({ plug: "fancybox" }),
            modal: true,
            padding: fancyP,
            closeBtn: true,
            closeBtnWrapper: ".fancybox-skin .fancybox-title",
            beforeShow: function () {
                $(".fancybox-inner").addClass("fancybox-video");
            },
            beforeLoad: function () {
                var $elem = this.element;
                if ($elem.data("title")) this.title = htmlEscape($elem.data("title"));

                if ($elem.data("h")) {
                    this.autoHeight = false;
                    this.height = $elem.data("h");
                }

                if ($elem.data("w")) {
                    this.width = $elem.data("w");
                    this.autoWidth = false;
                } else {
                    this.width = fancyW;
                }

                if ($elem.data("mw")) {
                    this.maxWidth = $elem.data("mw");
                }

                if ($elem.data("p") != undefined) {
                    this.padding = [$elem.data("p"), $elem.data("p"), $elem.data("p"), $elem.data("p")];

                    if ($elem.data("p") == 0) {
                        this.wrapCSS = "fancybox-title--close";
                    }
                } else {
                    this.padding = [fancyP, fancyP, fancyP, fancyP];
                }
            },
        },
        ".fancyboxVideo"
    );

    $(".fancyboxMep").fancybox(
        {
            width: "100%",
            height: fancyH,
            maxWidth: 1090,
            maxHeight: 700,
            autoSize: false,
            loop: false,
            helpers: {
                title: {
                    type: "inside",
                    position: "top",
                },
                overlay: {
                    locked: true,
                },
            },
            lang: __site_lang,
            i18n: translate_js_one({ plug: "fancybox" }),
            modal: true,
            closeBtn: true,
            padding: fancyP,
            closeBtnWrapper: ".fancybox-skin .fancybox-title",
            afterShow: function () {
                // console.log(fancyH);
            },
            beforeLoad: function () {
                var $elem = this.element;

                if ($elem.data("before-callback") != undefined) {
                    if (window[$elem.data("before-callback")](this) == false) return false;
                }

                if ($elem.data("title") != undefined) {
                    this.title = htmlEscape($elem.data("title")) + "&emsp;";
                }

                if ($elem.data("title-type") != undefined) {
                    this.title = $elem.data("title") + "&emsp;";
                }

                if ($elem.data("h")) {
                    this.autoHeight = false;
                    this.height = $elem.data("h");
                } else {
                    this.height = fancyH;

                    if (this.height == "100%") {
                        this.autoHeight = false;
                    } else {
                        this.autoHeight = true;
                    }
                }

                if ($elem.data("w")) {
                    this.width = $elem.data("w");
                    this.autoWidth = false;
                } else {
                    this.width = fancyW;
                }

                if ($elem.data("mw")) {
                    this.maxWidth = $elem.data("mw");
                }

                if ($elem.data("p") != undefined) {
                    this.padding = [$elem.data("p"), $elem.data("p"), $elem.data("p"), $elem.data("p")];

                    if ($elem.data("p") == 0) {
                        this.wrapCSS = "fancybox-title--close";
                    }
                } else {
                    this.padding = [fancyP, fancyP, fancyP, fancyP];
                }

                this.ajax.caller_btn = $elem;
            },
            ajax: {
                complete: function (jqXHR, textStatus) {
                    var $caller_btn = this.caller_btn;
                    inputAutocompliteOff();
                    $(".validateModal").validationEngine("attach", {
                        promptPosition: "topLeft",
                        autoPositionUpdate: true,
                        focusFirstField: false,
                        scroll: false,
                        showArrow: false,
                        addFailureCssClassToField: "validengine-border",
                        onValidationComplete: function (form, status) {
                            if (status) {
                                if ($(form).data("callback") != undefined) window[$(form).data("callback")](form, $caller_btn);
                                else modalFormCallBack(form, $caller_btn);
                            } else {
                            }
                        },
                    });
                },
            },
        },
        ".fancyboxMep"
    );

    $(".fancyboxValidateModalMessages").fancybox(
        {
            width: fancyW,
            height: fancyH,
            maxWidth: 700,
            autoSize: false,
            loop: false,
            helpers: {
                title: {
                    type: "inside",
                    position: "top",
                },
                overlay: {
                    locked: true,
                },
            },
            lang: __site_lang,
            i18n: translate_js_one({ plug: "fancybox" }),
            modal: true,
            closeBtn: true,
            padding: fancyP,
            closeBtnWrapper: ".fancybox-skin .fancybox-title",
            onUpdate: function () {
                if (this.inner) {
                    this.inner.trigger("fancybox:on-update");
                }
            },
            beforeClose: function () {
                if ("messagesPopupTree" in window && typeof messagesPopupTree === "function") {
                    clearInterval(messagesPopupTree.getNewMessagesInterval);
                }

                if (this.inner) {
                    this.inner.trigger("fancybox:before-close");
                }
            },
            beforeShow: function () {
                var $elem = this.element;

                if ($elem.data("dashboard-class") != undefined) {
                    $(".fancybox-inner").addClass($elem.data("dashboard-class"));
                }
            },
            beforeLoad: function () {
                var $elem = this.element;
                if ($elem.data("before-callback") != undefined) {
                    if (window[$elem.data("before-callback")](this) == false) return false;
                }

                var title = $elem.data("title") || null;
                if (null !== title) {
                    this.title = $elem.data("title-type") ? title : htmlEscape(title) + "&emsp;";
                }

                if ($elem.data("title-type") != undefined) {
                    this.title = $elem.data("title") + "&emsp;";
                }

                if ($elem.data("h")) {
                    this.autoHeight = false;
                    this.height = $elem.data("h");
                }

                if ($elem.data("w")) {
                    this.width = $elem.data("w");
                    this.autoWidth = false;
                } else {
                    this.width = fancyW;
                }

                if ($elem.data("mw")) {
                    this.maxWidth = $elem.data("mw");
                }

                if ($elem.data("p") != undefined) {
                    this.padding = [$elem.data("p"), $elem.data("p"), $elem.data("p"), $elem.data("p")];

                    if ($elem.data("p") == 0) {
                        this.wrapCSS = "fancybox-title--close";
                    }
                } else {
                    this.padding = [fancyP, fancyP, fancyP, fancyP];
                }

                this.ajax.caller_btn = $elem;
            },
        },
        ".fancyboxValidateModalMessages"
    );

    $(".fancyboxValidateModal").fancybox(
        {
            width: fancyW,
            height: fancyH,
            maxWidth: 700,
            autoSize: false,
            loop: false,
            helpers: {
                title: {
                    type: "inside",
                    position: "top",
                },
                overlay: {
                    locked: true,
                },
            },
            lang: __site_lang,
            i18n: translate_js_one({ plug: "fancybox" }),
            modal: true,
            closeBtn: true,
            padding: fancyP,
            closeBtnWrapper: ".fancybox-skin .fancybox-title",
            bodyClass: "",
            beforeShow: function () {
                var $elem = this.element;

                if ($elem.data("dashboard-class") != undefined) {
                    $(".fancybox-inner").addClass($elem.data("dashboard-class"));
                }
            },
            beforeLoad: function () {
                var $elem = this.element;
                if ($elem.data("before-callback") != undefined) {
                    if (window[$elem.data("before-callback")](this) == false) return false;
                }

                if ($elem.attr("data-post-params") !== undefined) {
                    this.ajax.type = 'POST';
                    this.ajax.data = $elem.attr("data-post-params");
                }

                if ($elem.data("get-params") !== undefined) {
                    this.ajax.data =  $elem.data("get-params");
                }

                if ($elem.data("title") != undefined) {
                    this.title = htmlEscape($elem.data("title")) + "&emsp;";
                }

                if ($elem.data("body-class") != undefined) {
                    this.bodyClass = htmlEscape($elem.data("body-class"));
                    $("body").addClass(this.bodyClass);
                }

                if ($elem.data("title-type") != undefined) {
                    this.title = $elem.data("title") + "&emsp;";
                }

                if ($elem.data("h")) {
                    this.autoHeight = false;
                    this.height = $elem.data("h");
                }

                if ($elem.data("w")) {
                    this.width = $elem.data("w");
                    this.autoWidth = false;
                } else {
                    this.width = fancyW;
                }

                if ($elem.data("mw")) {
                    this.maxWidth = $elem.data("mw");
                }

                if ($elem.data("p") != undefined) {
                    this.padding = [$elem.data("p"), $elem.data("p"), $elem.data("p"), $elem.data("p")];

                    if ($elem.data("p") == 0) {
                        this.wrapCSS = "fancybox-title--close";
                    }
                } else {
                    this.padding = [fancyP, fancyP, fancyP, fancyP];
                }

                this.ajax.caller_btn = $elem;
            },
            afterLoad: function () {
                var wrapClass = this.element.data("wrapClass");
                if (wrapClass) {
                    this.wrap.addClass(wrapClass);
                }
            },
            ajax: {
                complete: function (jqXHR, textStatus) {
                    var $caller_btn = this.caller_btn;
                    inputAutocompliteOff();
                    lazyLoadingInstance(".js-lazy", { threshhold: "10px" });
                    $(".validateModal").validationEngine("attach", {
                        promptPosition: "topLeft:0",
                        autoPositionUpdate: true,
                        focusFirstField: false,
                        scroll: false,
                        showArrow: false,
                        addFailureCssClassToField: "validengine-border",
                        onValidationComplete: function (form, status) {
                            if (status) {
                                if ($(form).data("callback") != undefined) window[$(form).data("callback")](form, $caller_btn);
                                else modalFormCallBack(form, $caller_btn);
                            } else {
                                systemMessages(translate_js({ plug: "general_i18n", text: "validate_error_message" }), "error");
                            }
                        },
                    });
                },
            },
            afterClose: function () {
                $("body").removeClass(this.bodyClass);
            },
        },
        ".fancyboxValidateModal"
    );

    $(".fancybox-ttl-inside").fancybox(
        {
            width: fancyW,
            height: fancyH,
            maxWidth: 700,
            autoSize: false,
            loop: false,
            helpers: {
                title: {
                    type: "inside",
                    position: "top",
                },
                overlay: {
                    locked: true,
                },
            },
            lang: __site_lang,
            i18n: translate_js_one({ plug: "fancybox" }),
            modal: true,
            padding: fancyP,
            closeBtn: true,
            closeBtnWrapper: ".fancybox-skin .fancybox-title",
            beforeLoad: function () {
                var $elem = this.element;
                if ($elem.data("title")) this.title = $elem.data("title");

                if ($elem.data("h")) {
                    this.autoHeight = false;
                    this.height = $elem.data("h");
                }

                if ($elem.data("w")) {
                    this.width = $elem.data("w");
                    this.autoWidth = false;
                } else {
                    this.width = fancyW;
                }

                if ($elem.data("mw")) {
                    this.maxWidth = $elem.data("mw");
                }

                if ($elem.data("p") != undefined) {
                    this.padding = [$elem.data("p"), $elem.data("p"), $elem.data("p"), $elem.data("p")];

                    if ($elem.data("p") == 0) {
                        this.wrapCSS = "fancybox-title--close";
                    }
                } else {
                    this.padding = [fancyP, fancyP, fancyP, fancyP];
                }
            },
        },
        ".fancybox-ttl-inside"
    );

    $(".fancyboxIframe").fancybox(
        {
            width: fancyW,
            height: fancyH,
            maxWidth: 700,
            autoSize: false,
            loop: false,
            lang: __site_lang,
            i18n: translate_js_one({ plug: "fancybox" }),
            helpers: {
                title: {
                    type: "inside",
                    position: "top",
                },
                overlay: {
                    locked: true,
                },
            },
            modal: true,
            type: "iframe",
            iframe: {
                preload: false, // this will prevent to place map off center
            },
            padding: fancyP,
            closeBtnWrapper: ".fancybox-skin .fancybox-title",
            beforeLoad: function () {
                var $elem = this.element;

                if ($elem.data("title")) this.title = htmlEscape($elem.data("title"));

                if ($elem.data("h")) {
                    this.autoHeight = false;
                    this.height = $elem.data("h");
                }

                if ($elem.data("w")) {
                    this.width = $elem.data("w");
                    this.autoWidth = false;
                } else {
                    this.width = fancyW;
                }

                if ($elem.data("mw")) {
                    this.maxWidth = $elem.data("mw");
                }

                if ($elem.data("p") != undefined) {
                    this.padding = [$elem.data("p"), $elem.data("p"), $elem.data("p"), $elem.data("p")];

                    if ($elem.data("p") == 0) {
                        this.wrapCSS = "fancybox-title--close";
                    }
                } else {
                    this.padding = [fancyP, fancyP, fancyP, fancyP];
                }
            },
        },
        ".fancyboxIframe"
    );

    $(".fancyboxValidateModalDT").fancybox(
        {
            width: fancyW,
            height: fancyH,
            maxWidth: 700,
            autoSize: false,
            loop: false,
            helpers: {
                title: {
                    type: "inside",
                    position: "top",
                },
                overlay: {
                    locked: true,
                },
            },
            lang: __site_lang,
            i18n: translate_js_one({ plug: "fancybox" }),
            modal: true,
            padding: fancyP,
            closeBtn: true,
            closeBtnWrapper: ".fancybox-skin .fancybox-title",
            beforeLoad: function () {
                var $elem = this.element;

                if ($elem.data("before-callback") != undefined) {
                    window[$elem.data("before-callback")](this);
                }

                if ($elem.data("title")) this.title = htmlEscape($elem.data("title"));

                if ($elem.data("h")) {
                    this.autoHeight = false;
                    this.height = $elem.data("h");
                }

                if ($elem.data("w")) {
                    this.width = $elem.data("w");
                    this.autoWidth = false;
                } else {
                    this.width = fancyW;
                }

                this.ajax.data_table_var = window[$(this.element).data("table")];

                if (this.ajax.data_table_var === undefined) this.ajax.data_table_var = window[$(this.element).parents("table").first().attr("id")];

                if ($elem.data("mw")) {
                    this.maxWidth = $elem.data("mw");
                }

                if ($elem.data("p") != undefined) {
                    this.padding = [$elem.data("p"), $elem.data("p"), $elem.data("p"), $elem.data("p")];

                    if ($elem.data("p") == 0) {
                        this.wrapCSS = "fancybox-title--close";
                    }
                } else {
                    this.padding = [fancyP, fancyP, fancyP, fancyP];
                }
            },
            ajax: {
                complete: function (jqXHR, textStatus) {
                    var $data_table_var = this.data_table_var;
                    inputAutocompliteOff();
                    $(".validateModal").validationEngine("attach", {
                        promptPosition: "topLeft",
                        autoPositionUpdate: true,
                        scroll: false,
                        showArrow: false,
                        addFailureCssClassToField: "validengine-border",
                        onValidationComplete: function (form, status) {
                            if (status) {
                                if ($(form).data("callback") != undefined) window[$(form).data("callback")](form, $data_table_var);
                                else modalFormCallBack(form, $data_table_var);
                            } else {
                                systemMessages(translate_js({ plug: "general_i18n", text: "validate_error_message" }), "error");
                            }
                        },
                    });
                },
            },
        },
        ".fancyboxValidateModalDT"
    );

    $(".fancyboxSidebar").fancybox(
        {
            width: "auto",
            height: "100%",
            autoSize: false,
            loop: false,
            openMethod: "slideIn",
            openSpeed: 250,
            helpers: {
                title: {
                    type: "inside",
                    position: "top",
                },
                overlay: {
                    locked: true,
                },
            },
            lang: __site_lang,
            i18n: translate_js_one({ plug: "fancybox" }),
            padding: 5,
            closeBtn: true,
            closeBtnWrapper: ".fancybox-skin .fancybox-title",
            beforeShow: function () {
                // $('html').addClass('fancybox-margin fancybox-lock');
            },
            afterShow: function () {
                // $('html').addClass('fancybox-margin fancybox-lock');
                modalResizeReturnPosition();
            },
            beforeLoad: function () {
                var $elem = this.element;

                if ($elem.data("before-callback") != undefined) {
                    if (window[$elem.data("before-callback")](this) == false) return false;
                }

                if ($elem.data("title")) this.title = htmlEscape($elem.data("title"));

                if ($elem.data("h")) {
                    this.autoHeight = false;
                    this.height = $elem.data("h");
                }

                if ($elem.data("w")) {
                    this.width = $elem.data("w");
                    this.autoWidth = false;
                } else {
                    this.width = fancyW;
                }

                if ($elem.data("mw")) {
                    this.maxWidth = $elem.data("mw");
                }

                if ($elem.data("p") != undefined) {
                    this.padding = [$elem.data("p"), $elem.data("p"), $elem.data("p"), $elem.data("p")];

                    if ($elem.data("p") == 0) {
                        this.wrapCSS = "fancybox-title--close";
                    }
                } else {
                    this.padding = [fancyP, fancyP, fancyP, fancyP];
                }

                this.ajax.caller_btn = $elem;
            },
            afterClose: function () {
                // $('html').removeClass('fancybox-margin fancybox-lock');
                var bodyWidth = $("body").width();
                var $elem = this.element;
                var href = $elem.attr("href");
                $(href).css({ display: "" });
                modalResizeReturnPositionOff();
            },
        },
        ".fancyboxSidebar"
    );

    $(".fancyboxLang").fancybox(
        {
            width: "auto",
            height: "auto",
            maxWidth: 700,
            autoSize: false,
            loop: false,
            helpers: {
                title: {
                    type: "inside",
                    position: "top",
                },
                overlay: {
                    locked: true,
                },
            },
            modal: true,
            padding: fancyP,
            lang: __site_lang,
            i18n: translate_js_one({ plug: "fancybox" }),
            closeBtn: true,
            closeBtnWrapper: ".fancybox-skin .fancybox-title",
            beforeLoad: function () {
                var $elem = this.element;

                if ($elem.data("title")) this.title = htmlEscape($elem.data("title"));

                if ($elem.data("w")) {
                    this.width = $elem.data("w");
                } else {
                    this.width = fancyW;
                }

                if ($elem.data("mw")) {
                    this.maxWidth = $elem.data("mw");
                }

                if ($elem.data("p") != undefined) {
                    this.padding = [$elem.data("p"), $elem.data("p"), $elem.data("p"), $elem.data("p")];

                    if ($elem.data("p") == 0) {
                        this.wrapCSS = "fancybox-title--close";
                    }
                } else {
                    this.padding = [fancyP, fancyP, fancyP, fancyP];
                }
            },
            onUpdate: function () {},
        },
        ".fancyboxLang"
    );

    $(".validengine_bl").validationEngine("attach", {
        promptPosition: "bottomLeft",
        autoPositionUpdate: true,
        showArrow: false,
        scroll: false,
        addFailureCssClassToField: "validengine-border",
        onValidationComplete: function (form, status) {
            if (status) {
                if ($(form).data("callback") !== undefined) {
                    eval("window." + $(form).data("callback") + "(form)");
                    //window[$(form).data("callback")](form);
                } else {
                    $(form).validationEngine("detach");
                    $(form).submit();
                }
            } else {
                systemMessages(translate_js({ plug: "general_i18n", text: "validate_error_message" }), "error");
            }
        },
    });

    $(".validengine:not(.js-search-autocomplete-form)").validationEngine("attach", {
        promptPosition: "topLeft",
        autoPositionUpdate: true,
        showArrow: false,
        scroll: false,
        addFailureCssClassToField: "validengine-border",
        onValidationComplete: function (form, status) {
            if (status) {
                if ($(form).data("callback") !== undefined) {
                    window[$(form).data("callback")](form);
                } else {
                    $(form).validationEngine("detach");
                    $(form).submit();
                }
            } else {
                systemMessages(translate_js({ plug: "general_i18n", text: "validate_error_message" }), "error");
            }
        },
    });

    $(".validengine_search").validationEngine("attach", {
        promptPosition: "topLeft",
        autoPositionUpdate: true,
        showArrow: false,
        scroll: false,
        addFailureCssClassToField: "validengine-border",
        focusFirstField: false,
    });

    $(".validengine_noscroll").validationEngine({
        scroll: false,
        promptPosition: "topLeft",
        autoPositionUpdate: true,
        showArrow: false,
        addFailureCssClassToField: "validengine-border",
        onValidationComplete: function (form, status) {
            if (status) {
                if ($(form).data("callback") !== undefined) {
                    window[$(form).data("callback")](form);
                } else {
                    $(form).validationEngine("detach");
                    $(form).submit();
                }
            } else {
                systemMessages(translate_js({ plug: "general_i18n", text: "validate_error_message" }), "error");
            }
        },
    });

    $(".validengine_noscroll_nofocus").validationEngine({
        scroll: false,
        promptPosition: "topLeft",
        autoPositionUpdate: true,
        focusFirstField: false,
        showArrow: false,
        addFailureCssClassToField: "validengine-border",
        onValidationComplete: function (form, status) {
            if (status) {
                if ($(form).data("callback") !== undefined) {
                    window[$(form).data("callback")](form);
                } else {
                    $(form).validationEngine("detach");
                    $(form).submit();
                }
            } else {
                systemMessages(translate_js({ plug: "general_i18n", text: "validate_error_message" }), "error");
            }
        },
    });

    (function ($, F) {
        // Opening animation - fly from the top
        F.transitions.dropIn = function () {
            var endPos = F._getPosition(true);

            endPos.top = parseInt(endPos.top, 10) - 200 + "px";
            endPos.opacity = 0;

            F.wrap.css(endPos).show().animate(
                {
                    top: "+=200px",
                    opacity: 1,
                },
                {
                    duration: F.current.openSpeed,
                    complete: F._afterZoomIn,
                }
            );
        };

        // Closing animation - fly to the top
        F.transitions.dropOut = function () {
            F.wrap.removeClass("fancybox-opened").animate(
                {
                    top: "-=200px",
                    opacity: 0,
                },
                {
                    duration: F.current.closeSpeed,
                    complete: F._afterZoomOut,
                }
            );
        };

        // Next gallery item - fly from left side to the center
        F.transitions.slideIn = function () {
            var endPos = F._getPosition(true);

            endPos.left = parseInt(endPos.left, 10) - 200 + "px";
            endPos.opacity = 0;

            F.wrap.css(endPos).show().animate(
                {
                    left: "+=200px",
                    opacity: 1,
                },
                {
                    duration: F.current.nextSpeed,
                    complete: F._afterZoomIn,
                }
            );
        };

        // Current gallery item - fly from center to the right
        F.transitions.slideOut = function () {
            F.wrap.removeClass("fancybox-opened").animate(
                {
                    left: "+=200px",
                    opacity: 0,
                },
                {
                    duration: F.current.prevSpeed,
                    complete: function () {
                        $(this).trigger("onReset").remove();
                    },
                }
            );
        };
    })(jQuery, jQuery.fancybox);

    $("body").on("click", ".image-dialog-one", function (e) {
        var $thisBtn = $(this);
        e.preventDefault();

        var galElements = '<div class="carousel-item active">\
                <img class="image" src="' + $thisBtn.data("img") + '" />\
            </div>';

        BootstrapDialog.closeAll();
        BootstrapDialog.show({
            cssClass: "info-bootstrap-dialog",
            message:
                '<div id="bootstrap-dialog-gallery" class="bootstrap-dialog-gallery carousel" data-ride="carousel">\
                 <div class="carousel-inner">' +
                galElements +
                "</div>",
            onshow: function (dialog) {
                dialog.getModalDialog().addClass("modal-dialog-centered");
            },
            onshown: function (dialog) {
                $(".carousel").carousel({ interval: false });
            },
            type: "type-light",
            size: "size-wide",
            closable: true,
            draggable: false,
            animate: true,
        });
    });

    $("body").on("click", ".image-dialog", function (e) {
        var $thisBtn = $(this);
        e.preventDefault();

        var parentItem = $thisBtn.closest(".image-dialog-wr");
        var parent = parentItem.parent();
        var galElements = "";
        parent.find(".image-dialog-wr").each(function () {
            var $thisWr = $(this);
            var $this = $thisWr.find(".image-dialog");
            var active = "";

            if ($thisWr.index() == parentItem.index()) {
                active = "active";
            }
            galElements +=
                '<div class="carousel-item ' + active + '">\
                    <img class="image" src="' + $this.data("img") + '" />\
                </div>';
        });

        BootstrapDialog.closeAll();
        BootstrapDialog.show({
            cssClass: "info-bootstrap-dialog",
            message:
                '<div id="bootstrap-dialog-gallery" class="bootstrap-dialog-gallery carousel" data-ride="carousel">\
                 <div class="carousel-inner">' +
                galElements +
                '</div>\
                <a class="carousel-control-prev" href="#bootstrap-dialog-gallery" role="button" data-slide="prev">\
                <span class="ep-icon ep-icon_arrow-left"></span>\
              </a>\
              <a class="carousel-control-next" href="#bootstrap-dialog-gallery" role="button" data-slide="next">\
                <span class="ep-icon ep-icon_arrow-right"></span>\
              </a>',
            onshow: function (dialog) {
                dialog.getModalDialog().addClass("modal-dialog-centered");
            },
            onshown: function (dialog) {
                $(".carousel").carousel({ interval: false });
            },
            type: "type-light",
            size: "size-wide",
            closable: true,
            draggable: false,
            animate: true,
        });
    });

    $("body").on("click", ".info-dialog-100pr", function (e) {
        var $thisBtn = $(this);
        e.preventDefault();

        var storedMessage = $thisBtn.data("message") || null;
        var storedContent = $thisBtn.data("content") || null;
        var message = "";

        if (null !== storedMessage) {
            message = storedMessage;
        } else if (null !== storedContent) {
            message = $(storedContent).html();
        }

        open_info_dialog_100pr($thisBtn.data("title"), message, false);
    });

    $("body").on("click", ".info-dialog", function (e) {
        e.preventDefault();

        var $thisBtn = $(this);
        var storedMessage = $thisBtn.data("message") || null;
        var storedContent = $thisBtn.data("content") || null;
        var validate = $thisBtn.data("validate") || false;
        var message = "";

        if (null !== storedMessage) {
            message = storedMessage;
        } else if (null !== storedContent) {
            message = $(storedContent).html();
        }

        var closeByBg = true;
        if ($thisBtn.data("close-bg") !== undefined && $thisBtn.data("close-bg") != "true") {
            closeByBg = false;
        }

        open_result_modal({
            title: $thisBtn.data("title"),
            content: message,
            isAjax: false,
            validate: validate,
            closable: true,
            closeByBg: closeByBg,
            keepModal: $thisBtn.data("keepModal"),
            type: "info",
            buttons: [
                {
                    label: translate_js({ plug: "BootstrapDialog", text: "close" }),
                    cssClass: "btn btn-light",
                    action: function (dialog) {
                        dialog.close();
                    },
                },
            ],
        });
    });

    $("body").on("click", ".info-dialog-ajax", function (e) {
        e.preventDefault();

        var $this = $(this);
        var classes = $this.data("classes") || "";
        var validate = Boolean(~~($this.data("validate") || false));
        var closeClickAction = $this.data("close-click") || "overlay";

        open_result_modal({
            title: $this.data("title"),
            subTitle: null,
            content: $this.data("href") || null,
            isAjax: true,
            validate: validate,
            classes: classes,
            closable: true,
            closeByBg: "overlay" === closeClickAction,
            type: "info",
            buttons: [
                {
                    label: translate_js({ plug: "BootstrapDialog", text: "close" }),
                    cssClass: "btn btn-light",
                    action: function (dialog) {
                        dialog.close();
                    },
                },
            ],
        });
    });

    $("body").on("click", ".js-open-dialog", function () {
        var data = $(this).data();
        open_result_modal({
            title: data.title,
            subTitle: data.message || $(data.content).html(),
            type: data.type || "info",
            keepModal: data.keepModal || false,
            closable: true,
            closeByBg: true,
            buttons: [
                {
                    label: translate_js({ plug: "BootstrapDialog", text: "close" }),
                    cssClass: "btn btn-light",
                    action: function (dialog) {
                        dialog.close();
                    },
                },
            ],
        });
    });

    $("body").on("click", ".js-validate-modal", function (e) {
        e.preventDefault();
        var $this = $(this);
        var link = $this.attr("href") || $this.data("href");
        var title = $this.data("title");
        var classes = $this.data("classes") || "";

        // var buttons = '<div class="modal-flex__btns w-100pr">\
        //     <div class="modal-flex__btns-left">\
        //         <button class="js-btn-prev btn btn-dark btn-block display-n call-function" data-callback="prevRegisterSteps" type="button">Back</button>\
        //     </div>\
        //     <div class="modal-flex__btns-right">\
        //         <button class="js-btn-next btn btn-primary btn-block call-function" data-callback="nextRegisterSteps" type="button">Next</button>\
        //         <button class="js-btn-submit btn btn-success display-n call-function" data-callback="validateTabSubmit" type="button">Finish</button>\
        //         <button class="js-btn-done btn btn-dark display-n call-function" data-callback="closeFormRegAdditional" type="button">Done</button>\
        //     </div>\
        // </div>';

        // var classes = 'info-bootstrap-dialog--mw-530 info-bootstrap-dialog--footer-custom wr-input-label inputs-40 js-modal-add-description-items';

        open_modal_dialog({
            title: title,
            isAjax: true,
            content: link,
            validate: true,
            classes: classes,
            buttons: [],
        });
    });

    $("body").on("click", ".confirm-dialog", function (e) {
        e.preventDefault();

        var $thisBtn = $(this),
            title = $thisBtn.data("title") || undefined,
            subTitle = $thisBtn.data("message") || undefined,
            callBackBtn = $thisBtn.data("callback") || undefined,
            typeModal = $thisBtn.data("type") || "info",
            keepModal = $thisBtn.data("keepModal"),
            icon = $thisBtn.data("icon") || undefined;

        open_result_modal({
            title: title,
            subTitle: subTitle,
            icon: icon,
            type: typeModal,
            keepModal: keepModal,
            onShownCallback: function(){
                var $button = $('.js-open-result-modal-button-ok');
                var atas = $thisBtn.attr("atas") || undefined;

                if (atas) {
                    $button.attr("atas", "global_confirm-dialog_ok_btn");
                }
            },
            buttons: [
                {
                    label: translate_js({ plug: "BootstrapDialog", text: "ok" }),
                    cssClass: "btn-success js-open-result-modal-button-ok",
                    action: function (dialogRef) {
                        var callBack = callBackBtn;
                        var $button = this;
                        $button.disable();

                        window[callBack]($thisBtn);
                        dialogRef.close();
                    },
                },
                {
                    label: translate_js({ plug: "BootstrapDialog", text: "cancel" }),
                    action: function (dialogRef) {
                        dialogRef.close();
                    },
                },
            ],
        });
    });

    $(".rating-bootstrap").rating();

    $("body").on("click", ".minfo-sidebar-mlist__ttl", function (e) {
        e.preventDefault();
        var $thisBtn = $(this);
        var $thisParent = $thisBtn.closest(".minfo-sidebar-mlist__item");

        if (!$thisParent.hasClass("active")) {
            $thisParent.addClass("active"); //.siblings().removeClass('active');
        } else {
            $thisParent.removeClass("active");
        }

        $thisBtn.toggleClass("minfo-sidebar-mlist__ttl--rotate");
    });

    $("body").on("change", ".live-category-featured", function (e) {
        e.preventDefault();

        var $thisBtn = $(this);
        var value = $thisBtn.val();

        if (value !== "") window.location.href = value;
    });

    $(".minfo-select2").select2({
        theme: "default paginator-select2",
        width: "auto",
        dropdownAutoWidth: true,
        minimumResultsForSearch: -1,
    });

    $("body").on("click", ".js-didhelp-btn:not(.disabled)", function (e) {
        e.preventDefault();

        var btn = $(this);
        var item = btn.data("item");
        var type = btn.data("type");
        var action = btn.data("action");
        var page = btn.data("page");
        var voted_class = "txt-blue2";

        $.ajax({
            type: "POST",
            url: __current_sub_domain_url + page + "/ajax_" + type + "_operation/help",
            data: { id: item, type: action },
            dataType: "JSON",
            success: function (response) {
                if (response.mess_type !== "success") {
                    systemMessages(response.message, response.mess_type);

                    return false;
                }

                var did_help_wrapper = btn.parent();

                if (typeof response.counter_plus !== "undefined") {
                    did_help_wrapper.find(".js-counter-plus").text(response.counter_plus);
                }

                if (typeof response.counter_minus !== "undefined") {
                    did_help_wrapper.find(".js-counter-minus").text(response.counter_minus);
                }

                var arrow_up_element = did_help_wrapper.find(".js-arrow-up");
                var arrow_down_element = did_help_wrapper.find(".js-arrow-down");

                if (typeof response.select_plus !== "undefined" && !arrow_up_element.hasClass(voted_class)) {
                    arrow_up_element.addClass(voted_class);
                }

                if (typeof response.remove_plus !== "undefined") {
                    arrow_up_element.removeClass(voted_class);
                }

                if (typeof response.select_minus !== "undefined" && !arrow_down_element.hasClass(voted_class)) {
                    arrow_down_element.addClass(voted_class);
                }

                if (typeof response.remove_minus !== "undefined") {
                    arrow_down_element.removeClass(voted_class);
                }
            },
        });
    });

    $("body").on("click", "#epuser-saved2 .epuser-pagination a", function (e) {
        e.preventDefault();
        var $this = $(this);
        var type = $this.data("type");
        var page = $this.data("page");
        laodSavedList(type, page);
    });

    //START notifications
    //notifications pagination
    $("body").on("click", "#js-epuser-notifications2 .epuser-pagination a", function (e) {
        e.preventDefault();
        loadNotificationList2($(this).data("status"), $(this).data("page"), $(this).data("type"));
    });
    //end notifications pagination

    var notificationsAllChange = false;
    var notificationsInnerChange = false;

    //select all checkbox
    $("body").on("change", ".js-check-all2 input[type=checkbox]", function (event) {
        // console.log('check-all2');

        if (!notificationsInnerChange) {
            notificationsAllChange = true;

            var inputStatus = $(this).prop("checked");

            $(".js-epuser-subline-list2 input[type=checkbox]").prop("checked", inputStatus);

            checkNotifyAll();
            notificationsAllChange = false;
        }
    });
    //end select all checkbox

    //START check if has checked notify for btn remove
    $("body").on("change", "#js-epuser-notifications2 .js-epuser-subline-list2__item input[type=checkbox]", function () {
        if (!notificationsAllChange) {
            notificationsInnerChange = true;

            var notify = $("#js-epuser-notifications2");
            var totalNotifyChecked = 0;
            var totalNotify = notify.find(".js-epuser-subline-list2__item").length;
            notify.find(".js-epuser-subline-list2__item").each(function () {
                if ($(this).find("input[type=checkbox]").prop("checked")) {
                    totalNotifyChecked++;
                }
            });

            $(".js-check-all2 input[type=checkbox]").prop("checked", totalNotify == totalNotifyChecked);

            checkNotifyAll();
            notificationsInnerChange = false;
        }
    });
    //END check if has checked notify for btn remove

    function checkNotifyAll() {
        var notify = $("#js-epuser-notifications2");

        if (!notify.length) {
            return false;
        }

        var totalNotifyChecked = 0;
        var $btnRemoveNotification = $("#js-epuser-notifications2 .remove-notification");
        var $btnReadNotification = $("#js-epuser-notifications2 .read-notification");

        notify.find(".js-epuser-subline-list2__item").each(function () {
            if ($(this).find("input[type=checkbox]").prop("checked")) {
                totalNotifyChecked++;
            }
        });

        if (totalNotifyChecked > 0) {
            if ($btnRemoveNotification.length) {
                $btnRemoveNotification.data("callback", "remove_notification2").removeClass("call-function").addClass("confirm-dialog");
            }

            if ($btnReadNotification.length) {
                $btnReadNotification.data("callback", "read_notification2").removeClass("call-function").addClass("confirm-dialog");
            }
        } else {
            if ($btnRemoveNotification.length) {
                $btnRemoveNotification.data("callback", "no_remove_notification2").removeClass("confirm-dialog").addClass("call-function");
            }

            if ($btnReadNotification.length) {
                $btnReadNotification.data("callback", "no_read_notification2").removeClass("confirm-dialog").addClass("call-function");
            }
        }
    }
    //END notifications

    if ($(".main-data-table").length > 0 && $(window).width() < 768) {
        $(".main-data-table").addClass("main-data-table--mobile");
    }

    $('[data-toggle="popover"]').popover();

    $("body").on("change", ".wr-modal-b form :input, .wr-modal-flex form :input", function () {
        $(".fancybox-title").find('a[data-callback="closeFancyBox"]').removeClass("call-function").addClass("confirm-dialog");
    });

    setInterval(function () {
        updateActivity();
    }, 600000);

    $("body")
        .on("focus", ".tagsinput input", function () {
            $(this).closest(".tagsinput").prev(".formError").addClass("show").removeClass("hide");
        })
        .on("blur", ".tagsinput input", function () {
            $(this).closest(".tagsinput").prev(".formError").addClass("hide").removeClass("show");
        });

    lazyLoadingInstance(".js-lazy", { threshhold: "10px" });
    showSuccessSubscribtionPopupIfNeeded();
});

$(window).resize(function () {
    $.fancybox.update();
});

Object.size = function (obj) {
    var size = 0,
        key;
    for (key in obj) {
        if (obj.hasOwnProperty(key)) size++;
    }
    return size;
};

$.fn.setValHookType = function (type) {
    this.each(function () {
        this.type = type;
    });

    return this;
};

var bootstrapDialogCloseAll = function () {
    BootstrapDialog.closeAll();

    dispatchCustomEvent("modal:call-close-modal", globalThis);
};

// Global function of google analytics events
/**
 * @function callGAEvent Send data to Google Analytics
 * @param {string} event_name Event name, ex: Add-item-success-submit
 * @param {string} event_category Event category, ex: Click, Submit, Ajax success, Validation error, e.t.c
 * @param {string} event_label Event Label, ex: Some data, may be some string like object {send:true, date:date}
 */
var callGAEvent = function (event_name, event_category, event_label) {
    var tracker = globalThis.gtag || null;
    if (tracker === null) {
        if (typeof globalThis.dataLayer === "undefined" || globalThis.dataLayer.push === "function") {
            return;
        }

        tracker = globalThis.dataLayer.push;
    }

    try {
        tracker("event", event_name, {
            event_category: event_category,
            event_label: event_label,
            transport_type: "beacon",
        });
    } catch(e) {
        console.log(e);
    }
};

var callGAEventCoolDownTimer;
var callGAEventCoolDown = function () {
    globalThis.callGAEventState = true;
    callGAEventCoolDownTimer = setTimeout(function () {
        clearTimeout(callGAEventCoolDownTimer);
        globalThis.callGAEventState = false;
    }, 1000);
};

var inputAutocompliteOff = function () {
    $('input:not([type="hidden"])').each(function (e) {
        var $this = $(this);
        if (Boolean(~~$this.data("noAutocompletePropRefresh") || false)) {
            return;
        }

        $this.attr("autocomplete", ($this.attr("name") || "input") + "_" + Date.now() + $this.index());
    });
};

jQuery(window).on("resizestop", function () {
    if ($(".main-data-table").length > 0) {
        if ($(this).width() < 768) {
            $(".main-data-table").addClass("main-data-table--mobile");
        } else {
            $(".main-data-table").removeClass("main-data-table--mobile");
        }
    }

    myInitFancybox();
});

var myInitFancybox = function () {
    var bodyWidth = $("body").width();
    var bodyHeight = $(window).height();

    if (bodyWidth < 768 || bodyHeight < 636) {
        fancyW = "99%";
        fancyH = "100%";
        fancyP = 15;
    } else {
        fancyW = "70%";
        fancyH = "auto";
        fancyP = 30;
    }
};

var dataTableScrollPage = function (target) {
    // console.log('dt page 0');
    target.on("page.dt", function () {
        // console.log('dt page 1');
        $("html, body").animate(
            {
                scrollTop: $(".dataTables_wrapper").offset().top,
            },
            "slow"
        );
    });
};

var dataTableAllInfo = function ($obj) {
    var row = $obj.closest('tr[role="row"]');
    var table = row.closest("table");
    var api = new $.fn.dataTable.Api(table);
    var rowIndex = api.row(row).index();
    var collectCells = function (api, rowIndex) {
        return api
            .cells()
            .eq(0)
            .filter(function (index) {
                return index.row === rowIndex;
            })
            .map(function (index) {
                var cell = api.cell(index);
                var header = cell.column(index.column).header();
                var title = $(header).text() || null;
                if (null === title) {
                    return;
                }

                return {
                    row: typeof index.row !== "undefined" ? index.row : null,
                    data: cell.data().toString().trim(),
                    title: title,
                    column: typeof index.column !== "undefined" ? index.column : null,
                };
            })
            .toArray()
            .filter(function (i) {
                return i;
            });
    };
    var makeCellDetails = function (cell) {
        return (
            '<div class="dtable-all__item">' +
            '<div class="dtable-all__ttl">' +
            cell.title +
            "</div>" +
            '<div class="dtable-all__detail">' +
            cell.data +
            "</div>" +
            "</div>"
        );
    };

    return dataTableAllInfoFancybox('<div class="dtable-all">' + collectCells(api, rowIndex).map(makeCellDetails).join("") + "</div>");
};

var dataTableAllInfoFancybox = function (textHtml) {
    $.fancybox.open(
        {
            title: "All info",
            content: textHtml,
        },
        {
            width: fancyW,
            height: fancyH,
            maxWidth: 350,
            autoSize: false,
            loop: false,
            helpers: {
                title: {
                    type: "inside",
                    position: "top",
                },
                overlay: {
                    locked: true,
                },
            },
            modal: true,
            closeBtn: true,
            padding: fancyP,
            closeBtnWrapper: ".fancybox-skin .fancybox-title",
        }
    );
};

var openFancyboxValidateModal = function (href, title, mw) {
    var href = href || "";
    var mw = mw || "";
    var title = title || "";

    if (href == "") {
        return false;
    }

    $.fancybox.open(
        {
            title: title,
            type: "ajax",
            href: href,
        },
        {
            width: fancyW,
            height: fancyH,
            maxWidth: 700,
            autoSize: false,
            loop: false,
            helpers: {
                title: {
                    type: "inside",
                    position: "top",
                },
                overlay: {
                    locked: true,
                },
            },
            modal: true,
            closeBtn: true,
            padding: fancyP,
            closeBtnWrapper: ".fancybox-skin .fancybox-title",
            lang: __site_lang,
            i18n: translate_js_one({ plug: "fancybox" }),
            beforeLoad: function () {
                this.width = fancyW;
                this.padding = [fancyP, fancyP, fancyP, fancyP];
            },
            ajax: {
                complete: function (jqXHR, textStatus) {
                    inputAutocompliteOff();
                    $(".validateModal").validationEngine("attach", {
                        promptPosition: "topLeft:0",
                        autoPositionUpdate: true,
                        focusFirstField: false,
                        scroll: false,
                        showArrow: false,
                        addFailureCssClassToField: "validengine-border",
                        onValidationComplete: function (form, status) {
                            if (status) {
                                if ($(form).data("callback") != undefined) window[$(form).data("callback")](form);
                                else modalFormCallBack(form);
                            } else {
                                systemMessages(translate_js({ plug: "general_i18n", text: "validate_error_message" }), "error");
                            }
                        },
                    });
                },
            },
        }
    );
};

var addReviewFancybox = function (obj) {
    var $this = $(obj);
    var href = $this.data("href");

    $.fancybox.open(
        {
            title: "Add review",
            type: "ajax",
            href: href,
        },
        {
            width: fancyW,
            height: fancyH,
            maxWidth: 700,
            autoSize: false,
            loop: false,
            helpers: {
                title: {
                    type: "inside",
                    position: "top",
                },
                overlay: {
                    locked: true,
                },
            },
            modal: true,
            closeBtn: true,
            padding: fancyP,
            closeBtnWrapper: ".fancybox-skin .fancybox-title",
            lang: __site_lang,
            i18n: translate_js_one({ plug: "fancybox" }),
            beforeLoad: function () {
                var $elem = $this;

                if ($elem.data("before-callback") != undefined) {
                    if (window[$elem.data("before-callback")](this) == false) return false;
                }

                if ($elem.data("title") != undefined) this.title = htmlEscape($elem.data("title")) + "&emsp;";

                if ($elem.data("h")) {
                    this.autoHeight = false;
                    this.height = $elem.data("h");
                }

                if ($elem.data("w")) {
                    this.width = $elem.data("w");
                    this.autoWidth = false;
                } else {
                    this.width = fancyW;
                }

                if ($elem.data("mw")) {
                    this.maxWidth = $elem.data("mw");
                }

                if ($elem.data("p") != undefined) {
                    this.padding = [$elem.data("p"), $elem.data("p"), $elem.data("p"), $elem.data("p")];

                    if ($elem.data("p") == 0) {
                        this.wrapCSS = "fancybox-title--close";
                    }
                } else {
                    this.padding = [fancyP, fancyP, fancyP, fancyP];
                }

                this.ajax.caller_btn = $elem;
            },
            ajax: {
                complete: function (jqXHR, textStatus) {
                    var $caller_btn = this.caller_btn;
                    inputAutocompliteOff();
                    $(".validateModal").validationEngine("attach", {
                        promptPosition: "topLeft:0",
                        autoPositionUpdate: true,
                        focusFirstField: false,
                        scroll: false,
                        showArrow: false,
                        addFailureCssClassToField: "validengine-border",
                        onValidationComplete: function (form, status) {
                            if (status) {
                                if ($(form).data("callback") != undefined) window[$(form).data("callback")](form, $caller_btn);
                                else modalFormCallBack(form, $caller_btn);
                            } else {
                                systemMessages(translate_js({ plug: "general_i18n", text: "validate_error_message" }), "error");
                            }
                        },
                    });
                },
            },
        }
    );
};

function setDateFilters(callerObj, filterObj) {
    if (filterObj.name == "start_from") {
        $(".start_to").datepicker("option", "minDate", $(".start_from").datepicker("getDate"));
    }
    if (filterObj.name == "start_to") {
        $(".start_from").datepicker("option", "maxDate", $(".start_to").datepicker("getDate"));
    }

    if (filterObj.name == "update_from") {
        $(".update_to").datepicker("option", "minDate", $(".update_from").datepicker("getDate"));
    }
    if (filterObj.name == "update_to") {
        $(".update_from").datepicker("option", "maxDate", $(".update_to").datepicker("getDate"));
    }
}

var filters_has_datepicker = false;
globalThis.dataT = null;
var initDtFilter = function (selector) {
    var filtersSelector = selector || ".dt_filter";

    return $(filtersSelector).dtFilters(filtersSelector, {
        container: ".dtfilter-list",
        // 'debug':true,
        txtResetBtn: "Reset filters",
        callBack: function () {
            dataT.fnDraw();
        },
        onActive: function (array) {
            if (array.length) {
                $(".btn-filter").addClass("btn-filter--active");
            } else {
                $(".btn-filter").removeClass("btn-filter--active");
            }
        },
        beforeSet: function (callerObj) {
            if (typeof beforeSetFilters == "function") {
                beforeSetFilters(callerObj);
            }
        },
        onSet: function (callerObj, filterObj) {
            if (filters_has_datepicker === true) {
                setDateFilters(callerObj, filterObj);
            }

            if (typeof onSetFilters == "function") {
                onSetFilters(callerObj, filterObj);
            }
        },
        onDelete: function (filter) {
            if (typeof onDeleteFilters == "function") {
                onDeleteFilters(filter);
            }
        },
        onReset: function () {
            if (filters_has_datepicker) {
                $(".dtfilter-popup .hasDatepicker").datepicker("option", {
                    minDate: null,
                    maxDate: null,
                });
            }

            if (typeof onResetFilters == "function") {
                onResetFilters();
            }
        },
    });
};

function didhelpSend(btn) {
    var item = btn.data("item");
    var type = btn.data("type");
    var action = btn.data("action");
    var page = btn.data("page");

    if (!btn.hasClass("disabled")) {
        $.ajax({
            type: "POST",
            url: __site_url + page + "/ajax_" + type + "_operation/help",
            data: { id: item, type: action },
            dataType: "JSON",
            success: function (resp) {
                if (resp.mess_type == "success") {
                    if (action == "y") {
                        var btnPush = ".i-up";
                        var btnPull = ".i-down";
                        btn.addClass("disabled");
                        if (btn.siblings(".didhelp-btn").hasClass("disabled")) btn.siblings(".didhelp-btn").removeClass("disabled");
                    } else {
                        var btnPush = ".i-down";
                        var btnPull = ".i-up";
                        btn.addClass("disabled");
                        if (btn.siblings(".didhelp-btn").hasClass("disabled")) btn.siblings(".didhelp-btn").removeClass("disabled");
                    }

                    var $parentHelp = btn.parent(".did-help");

                    if ($parentHelp.hasClass("rate-didhelp")) {
                        var $pullCount = $parentHelp.find(btnPull + " .counter-b");
                        var pullCount = parseInt($pullCount.text());
                        $pullCount.text(pullCount - 1);
                    } else {
                        $parentHelp.addClass("rate-didhelp");
                    }

                    var $pushCount = $parentHelp.find(btnPush + " .counter-b");
                    var pushCount = parseInt($pushCount.text());
                    $pushCount.text(pushCount + 1);
                }
                systemMessages(resp.message, resp.mess_type);
            },
        });
    }
}

function get_items($fancybox) {
    if (typeof window.set_data_item == "function") if (window.set_data_item($fancybox.element) === false) return false;

    var prepared_obj = {};
    $fancybox.ajax.type = "POST";

    var items = $($fancybox.element).data("items").split(",");

    $.each(items, function (index, value) {
        var parts = value.split("x");

        if (typeof prepared_obj["item[" + parts[0] + "]"] === "undefined") prepared_obj["item[" + parts[0] + "]"] = 0;

        prepared_obj["item[" + parts[0] + "]"] += typeof parts[1] === "undefined" ? 1 : parseInt(parts[1], 10);
    });

    $fancybox.ajax.data = prepared_obj;
}

var go_to_featured_companies = function (element) {
    var page = $(element).attr("href");

    if (window.location.href == __site_url || window.location.href == page) {
        var companies_block_selector = "#" + $(element).data("anchor");

        $("html,body").animate(
            {
                scrollTop: $(companies_block_selector).offset().top,
            },
            0
        );

        $(".fancybox-lock").removeClass("fancybox-lock");
        $.fancybox.close();
    } else {
        $.fancybox.close();
        window.location.href = page;
    }
};

function closeFancyBox() {
    //console.log('close');
    if ($(".fancybox-skin .dtfilter-popup .nav-tabs").length) {
        var navTabs = $(".fancybox-skin .dtfilter-popup .nav-tabs");
        var firstLi = navTabs.find("li:first-child");

        firstLi.find(".nav-link").addClass("active").end().siblings().find(".nav-link").removeClass("active");

        var tabContent = $(".fancybox-skin .dtfilter-popup .tab-content");

        tabContent.find(".tab-pane:first-child").addClass("active").siblings().removeClass("active");
    }

    $(".validateModal").validationEngine("detach");
    $.fancybox.close();
}

function ratingBootstrap($this) {
    var rating = $this.val();
    var text = "";

    if (rating.length) {
        text = ratingBootstrapStatus(rating);
        $this.next(".rating-bootstrap-status").text(text);
    }
}

function ratingBootstrapStatus(rating) {
    var text = "";

    switch (true) {
        case rating < 2:
            text = "Terrible";
            break;
        case rating < 3:
            text = "Poor";
            break;
        case rating < 4:
            text = "Ok";
            break;
        case rating < 5:
            text = "Good";
            break;
        case rating < 6:
            text = "Excellent";
            break;
    }

    return text;
}

var showLoader = function (selector, text, position, index) {
    if (text == "default" || text == undefined) {
        text = "Sending...";
    }

    var index = index || 0;
    var position = position || "absolute";
    var wrapper = $(selector);
    var positionWrapper = wrapper.css("position");
    var loader = wrapper.children(".ajax-loader");
    var positionClass = position == "fixed" ? " ajax-loader__fixed" : " ajax-loader__absolute";

    if (index > 0) {
        index = 'style="z-index: ' + index + '"';
    } else {
        index = "";
    }

    var template =
        '<div class="ajax-loader' +
        positionClass +
        '" ' +
        index +
        '><i class="ajax-loader__icon"></i><span class="ajax-loader__text">' +
        text +
        "</span></div>";

    if (positionWrapper == "static") {
        wrapper.addClass("relative-b");
    }

    if (position == "fixed") {
        $("html").addClass("ajax-loader-lock");
    }

    if (0 === loader.length) {
        loader = $(template);
        wrapper.prepend(loader);
    }

    loader.css({ display: "flex" });
};

var hideLoader = function (selector) {
    var wrapper = $(selector);
    var loader = wrapper.children(".ajax-loader");
    $("html").removeClass("ajax-loader-lock");

    if (loader.length > 0) {
        loader.hide();
    }
};

//remove seller from Favorites
var remove_company = function (opener) {
    var $this = $(opener);
    $.ajax({
        url: "directory/ajax_company_operations/remove_company_saved",
        type: "POST",
        dataType: "JSON",
        data: { company: $this.data("company") },
        success: function (resp) {
            systemMessages(resp.message, resp.mess_type);
            if (resp.mess_type == "success") {
                $this.data("callback", "add_company");

                if ($this.find(".ep-icon").length) {
                    $this
                        .find(".ep-icon")
                        .toggleClass("ep-icon_favorite ep-icon_favorite-empty")
                        .end()
                        .find("span")
                        .html(translate_js({ plug: "general_i18n", text: "seller_home_page_sidebar_menu_dropdown_favorite" }));
                } else {
                    $this.toggleClass("ep-icon_favorite ep-icon_favorite-empty");
                }
            }
        },
    });
};

//Add seller to Favorites
var add_company = function (opener) {
    var $this = $(opener);
    $.ajax({
        url: "directory/ajax_company_operations/add_company_saved",
        type: "POST",
        dataType: "JSON",
        data: { company: $this.data("company") },
        success: function (resp) {
            systemMessages(resp.message, resp.mess_type);
            if (resp.mess_type == "success") {
                $this.data("callback", "remove_company");

                if ($this.find(".ep-icon").length) {
                    $this
                        .find(".ep-icon")
                        .toggleClass("ep-icon_favorite ep-icon_favorite-empty")
                        .end()
                        .find("span")
                        .html(translate_js({ plug: "general_i18n", text: "seller_home_page_sidebar_menu_dropdown_favorited" }));
                } else {
                    $this.toggleClass("ep-icon_favorite ep-icon_favorite-empty");
                }
            }
        },
    });
};

var unfollow_user = function (obj) {
    var $this = $(obj);
    var user = $this.data("user");

    $.ajax({
        type: "POST",
        url: "followers/ajax_followers_operation/delete_follow_user",
        data: { user: user },
        dataType: "json",
        success: function (resp) {
            systemMessages(resp.message, resp.mess_type);

            if (resp.mess_type == "success") {
                $this
                    .removeClass("call-function")
                    .data("title", translate_js({ plug: "general_i18n", text: "seller_home_page_sidebar_menu_dropdown_follow_user" }))
                    .attr("href", "followers/popup_followers/follow_user/" + resp.user)
                    .attr("title", translate_js({ plug: "general_i18n", text: "seller_home_page_sidebar_menu_dropdown_follow_user" }))
                    .addClass("fancybox.ajax fancyboxValidateModal");

                if ($this.find("i").length) {
                    $this.find("i").toggleClass("ep-icon_reply-left-empty ep-icon_reply-right-empty");
                    $this.find("span").html(translate_js({ plug: "general_i18n", text: "seller_home_page_sidebar_menu_dropdown_follow_user" }));
                } else {
                    $this.toggleClass("ep-icon_reply-left-empty ep-icon_reply-right-empty");
                }

                //                showMyStatistic('block',['follow_users','followers_user']);
                //                countersDashboardFollowers(resp.followers_count, resp.followed_count);
            }
        },
    });
};

function callbackFollowedPopup(user) {}

// var showLiveChat = function(){
//     $('.LPMcontainer').trigger('click');
// }

var initSelectCity = function ($selectCity, placeholder, regionElement) {
    var placeholderText = placeholder || translate_js({ plug: "general_i18n", text: "form_placeholder_select2_state_first" });
    var getState = function () {
        if (typeof selectState !== "undefined" && selectState) {
            return selectState;
        }

        if (regionElement instanceof jQuery || regionElement instanceof HTMLElement) {
            return $(regionElement).val() || null;
        }

        return null;
    };

    $selectCity
        .select2({
            ajax: {
                type: "POST",
                url: __current_sub_domain_url + "location/ajax_get_cities",
                dataType: "json",
                delay: 250,
                data: function (params) {
                    return {
                        page: params.page,
                        search: params.term,
                        state: getState(),
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
            language: __site_lang,
            theme: "default ep-select2-h30",
            width: "100%",
            placeholder: placeholderText,
            minimumInputLength: 2,
            escapeMarkup: function (markup) {
                return markup;
            },
            templateResult: formatCity,
            templateSelection: formatCitySelection,
        })
        .data("select2")
        .on("results:message", function (e) {
            this.dropdown._positionDropdown();
        });

    if ($selectCity.find("option").length < 2) {
        $selectCity.prop("disabled", true);
    }
};

function formatCity(repo) {
    if (repo.loading) return repo.text;

    var markup = repo.name;

    return markup;
}

function formatCitySelection(repo) {
    return repo.name || repo.text;
}

function selectCountry(selectCountry, statesSelectElement, placeholder) {
    var country = $(selectCountry).val();
    var placeholder = placeholder || "";

    return $.ajax({
        type: "POST",
        dataType: "JSON",
        url: __current_sub_domain_url + "location/ajax_get_states",
        data: { country: country, placeholder: placeholder },
        success: function (resp) {
            $(statesSelectElement).html(resp.states);
        },
    });
}

function get_price(fprice, show_symbol) {
    var currency_value = getCookie("currency_value");

    if (show_symbol === false) {
        return number_format(fprice, 2, ".", ",");
    }

    if (show_symbol !== true) {
        return number_format(fprice * currency_value, 2, ".", ",");
    }

    var currency_code = getCookie("currency_code");
    var price = parseFloat(fprice * currency_value);

    if (!price) {
        return currency_code + number_format(0, 2, ".", ",");
    }

    return currency_code + number_format(price, 2, ".", ",");
}

function number_format(number, decimals, dec_point, thousands_point) {
    if (number == null || !isFinite(number)) {
        throw new TypeError("number is not valid");
    }

    if (!decimals) {
        var len = number.toString().split(".").length;
        decimals = len > 1 ? len : 0;
    }

    if (!dec_point) {
        dec_point = ".";
    }

    if (!thousands_point) {
        thousands_point = ",";
    }

    number = parseFloat(number).toFixed(decimals);

    number = number.replace(".", dec_point);

    var splitNum = number.split(dec_point);
    splitNum[0] = splitNum[0].replace(/\B(?=(\d{3})+(?!\d))/g, thousands_point);
    number = splitNum.join(dec_point);

    return number;
}

function dropdown(obj, link, after_link, not_use_false_value) {
    //alert(after_link)
    if (after_link == undefined || after_link == null) {
        after_link = "";
    }

    if (!(obj.value == false && not_use_false_value)) {
        if (after_link != "") {
            after_link = after_link + "&" + obj.name + "=" + obj.value;
        } else {
            after_link = "?" + obj.name + "=" + obj.value;
        }
    }

    if (after_link.length && after_link[0] != "?") {
        after_link = "?" + after_link;
    }

    window.location.replace(link + after_link);
}

var socialRegister = function (obj) {
    //     var $this = $(obj);
    //     winPopup("/login/" + $this.data('type'), "chat", "width=900,height=700,0,status=0");
    systemMessages("Sorry, you can't register or log in via social pages while the pre-registration process is open.", "info");
};

function scrollToElement(element, minus, speed, callbackName, mainClasses, typePosition) {
    var calc = 0;
    speed = speed || 2000;
    minus = minus || 0;
    callbackName = callbackName || false;
    mainClasses = mainClasses || "html,body";
    typePosition = typePosition || "offset";

    if (minus === -1) {
        minus = $("#js-ep-header-fixed-top").height();
    }

    if (typePosition == "offset") {
        calc = $(element).offset().top - minus;
    } else {
        calc = $(element).position().top - minus;
    }

    $(mainClasses)
        .animate(
            {
                scrollTop: calc,
            },
            speed
        )
        .promise()
        .then(function () {
            if (callbackName) {
                window[callbackName]($(element));
            }
        });
}

function check_user_tz() {
    var ud = new Date();
    var utz = -ud.getTimezoneOffset() / 60;
    setCookie("_ep_utz", utz, 7);
    return true;
}

function unlockPrivateInfo(btn) {
    var $this = $(btn);

    var id = $this.data("id");
    var type = $this.data("type");
    var unlock = $this.data("unlock");

    var params = { id: id };

    switch (type) {
        case "company":
            var link = "company/ajax_company_operation/";
            break;
        case "user":
            var link = "user/ajax_user_operation/";
            break;
        case "service":
            var link = "seller_about/ajax_about_operation/";
            break;
        case "shipper":
            var link = "shipper/ajax_shipper_operation/";
            break;
        default:
            return false;
            break;
    }

    switch (unlock) {
        case "phone":
            link += "unlock_phone";
            break;
        case "email":
            link += "unlock_email";
            break;
        case "fax":
            link += "unlock_fax";
            break;
        case "right":
            link += "unlock_right";
            var right = $this.data("right");
            params.right = right;
            break;
        default:
            return false;
            break;
    }

    $.ajax({
        type: "POST",
        url: link,
        dataType: "JSON",
        data: params,
        success: function (resp) {
            if (resp.mess_type == "success") {
                $this.replaceWith(resp.block_info);
            } else {
                systemMessages(resp.message, resp.mess_type);
            }
        },
    });
}

var productDetailToggle = function (obj) {
    var $this = $(obj);

    $this.toggleClass("active").closest(".detail-info").find(".detail-info__toggle").slideToggle();
};

var formatCcodeSelectionNew = function (cCode) {
    if (!cCode.id) {
        return cCode.text;
    }

    var data = cCode.element.dataset || {};

    return $(
        '<img class="select-country-flag" width="32" height="32" src="' +
            (data.countryFlag || null) +
            '" alt="' +
            (data.countryName || "") +
            '"/>' +
            "<span>" +
            (data.code || "") +
            "</span>"
    );
};

var formatCcodeNew = function (cCode) {
    if (cCode.loading) {
        return cCode.text;
    }
    var element = $(cCode.element);

    return $(
        renderTemplate(
            '<span class="flex-display flex-ai--c notranslate">\
                <img class="w-16 h-16 mr-10" src="{{url}}" alt="{{alt}}"/>\
                <span>{{name}}</span>\
            </span>',
            {
                url: element.data("countryFlag") || null,
                alt: element.data("countryName") || "",
                name: htmlEscape(element.text()),
            }
        )
    );
};

function formatCcode(cCode) {
    if (cCode.loading) {
        return cCode.text;
    }

    /**
     * @type {HTMLElement} element
     */
    var element = cCode.element;
    var data = element.dataset || {};

    return $(
        '<span class="flex-display flex-ai--c notranslate">' +
            '<img class="w-16 h-16 mr-10" src="' +
            (data.countryFlag || null) +
            '" alt="' +
            (data.countryName || "") +
            '"/>' +
            "<span>" +
            (element.innerText || element.textContent || data.countryName || "") +
            "</span>" +
            "</span>"
    );
}

function uniqid(prefix, more_entropy) {
    // %         note 1: Uses an internal counter (in php_js global) to avoid collision
    // *     example 1: uniqid();
    // *     returns 1: 'a30285b160c14'
    // *     example 2: uniqid('foo');
    // *     returns 2: 'fooa30285b1cd361'
    // *     example 3: uniqid('bar', true);
    // *     returns 3: 'bara20285b23dfd1.31879087'
    if (typeof prefix === "undefined") {
        prefix = "";
    }

    var retId;
    var formatSeed = function (seed, reqWidth) {
        seed = parseInt(seed, 10).toString(16); // to hex str
        if (reqWidth < seed.length) {
            // so long we split
            return seed.slice(seed.length - reqWidth);
        }
        if (reqWidth > seed.length) {
            // so short we pad
            return Array(1 + (reqWidth - seed.length)).join("0") + seed;
        }
        return seed;
    };

    // BEGIN REDUNDANT
    if (!this.php_js) {
        this.php_js = {};
    }
    // END REDUNDANT
    if (!this.php_js.uniqidSeed) {
        // init seed with big random int
        this.php_js.uniqidSeed = Math.floor(Math.random() * 0x75bcd15);
    }
    this.php_js.uniqidSeed++;

    retId = prefix; // start with prefix, add current milliseconds hex string
    retId += formatSeed(parseInt(new Date().getTime() / 1000, 10), 8);
    retId += formatSeed(this.php_js.uniqidSeed, 5); // add seed hex string
    if (more_entropy) {
        // for more entropy we add a float lower to 10
        retId += (Math.random() * 10).toFixed(8).toString();
    }

    return retId;
}

function search($form) {
    var category = $form.find("select[data-type=category]").val();
    var country = $form.find("select[data-type=country]").val();
    var keyword = $form.find("input[data-type=keyword]").val();
    var action = "";
    if (category.trim() == "" && country.trim() == "" && keyword.trim() == "") {
        systemMessages("Error: Search parameters can't be empty.", "error");
        return false;
    }

    if (category != "") action = "category/" + category;
    else action = "search";

    if (country != "") {
        action += "/country/" + country;
    }

    $form.attr("action", "http://" + window.location.hostname + "/" + action);
    return true;
}

var SETTINGS = {
    navBarTravelling: false,
    navBarTravelDirection: "",
    navBarTravelDistance: 100,
};
var last_known_scroll_position = 0;
var ticking = false;

function navJssliderInit(pnProductNav, pnProductNavContents, pnAdvancerLeft, pnAdvancerRight) {
    // Handle the scroll of the horizontal container
    pnProductNav.addEventListener("scroll", function () {
        last_known_scroll_position = window.scrollY;
        if (!ticking) {
            window.requestAnimationFrame(function () {
                doSomething(last_known_scroll_position);
                ticking = false;
            });
        }
        ticking = true;
    });

    pnAdvancerLeft.addEventListener("click", function (e) {
        e.preventDefault();
        // If in the middle of a move return
        if (SETTINGS.navBarTravelling === true) {
            return;
        }
        // If we have content overflowing both sides or on the left
        if (determineOverflow(pnProductNavContents, pnProductNav) === "left" || determineOverflow(pnProductNavContents, pnProductNav) === "both") {
            // Find how far this panel has been scrolled
            var availableScrollLeft = pnProductNav.scrollLeft;
            // If the space available is less than two lots of our desired distance, just move the whole amount
            // otherwise, move by the amount in the settings
            if (availableScrollLeft < SETTINGS.navBarTravelDistance * 2) {
                pnProductNavContents.style.transform = "translateX(" + availableScrollLeft + "px)";
            } else {
                pnProductNavContents.style.transform = "translateX(" + SETTINGS.navBarTravelDistance + "px)";
            }
            // We do want a transition (this is set in CSS) when moving so remove the class that would prevent that
            pnProductNavContents.classList.remove("nav-jsslider__content--no-transition");
            // Update our settings
            SETTINGS.navBarTravelDirection = "left";
            SETTINGS.navBarTravelling = true;
        }
        // Now update the attribute in the DOM
        pnProductNav.setAttribute("data-overflowing", determineOverflow(pnProductNavContents, pnProductNav));
    });

    pnAdvancerRight.addEventListener("click", function (e) {
        e.preventDefault();
        // If in the middle of a move return
        if (SETTINGS.navBarTravelling === true) {
            return;
        }
        // If we have content overflowing both sides or on the right
        if (determineOverflow(pnProductNavContents, pnProductNav) === "right" || determineOverflow(pnProductNavContents, pnProductNav) === "both") {
            // Get the right edge of the container and content
            var navBarRightEdge = pnProductNavContents.getBoundingClientRect().right;
            var navBarScrollerRightEdge = pnProductNav.getBoundingClientRect().right;
            // Now we know how much space we have available to scroll
            var availableScrollRight = Math.floor(navBarRightEdge - navBarScrollerRightEdge);
            // If the space available is less than two lots of our desired distance, just move the whole amount
            // otherwise, move by the amount in the settings
            if (availableScrollRight < SETTINGS.navBarTravelDistance * 2) {
                pnProductNavContents.style.transform = "translateX(-" + availableScrollRight + "px)";
            } else {
                pnProductNavContents.style.transform = "translateX(-" + SETTINGS.navBarTravelDistance + "px)";
            }
            // We do want a transition (this is set in CSS) when moving so remove the class that would prevent that
            pnProductNavContents.classList.remove("nav-jsslider__content--no-transition");
            // Update our settings
            SETTINGS.navBarTravelDirection = "right";
            SETTINGS.navBarTravelling = true;
        }
        // Now update the attribute in the DOM
        pnProductNav.setAttribute("data-overflowing", determineOverflow(pnProductNavContents, pnProductNav));
    });

    pnProductNavContents.addEventListener(
        "transitionend",
        function () {
            // get the value of the transform, apply that to the current scroll position (so get the scroll pos first) and then remove the transform
            var styleOfTransform = window.getComputedStyle(pnProductNavContents, null);
            var tr = styleOfTransform.getPropertyValue("-webkit-transform") || styleOfTransform.getPropertyValue("transform");
            // If there is no transition we want to default to 0 and not null
            var amount = Math.abs(parseInt(tr.split(",")[4]) || 0);
            pnProductNavContents.style.transform = "none";
            pnProductNavContents.classList.add("nav-jsslider__content--no-transition");
            // Now lets set the scroll position
            if (SETTINGS.navBarTravelDirection === "left") {
                pnProductNav.scrollLeft = pnProductNav.scrollLeft - amount;
            } else {
                pnProductNav.scrollLeft = pnProductNav.scrollLeft + amount;
            }
            SETTINGS.navBarTravelling = false;
        },
        false
    );
}

function doSomething(scroll_pos) {
    pnProductNav.setAttribute("data-overflowing", determineOverflow(pnProductNavContents, pnProductNav));
}

function determineOverflow(content, container) {
    var containerMetrics = container.getBoundingClientRect();
    var containerMetricsRight = Math.floor(containerMetrics.right);
    var containerMetricsLeft = Math.floor(containerMetrics.left);
    var contentMetrics = content.getBoundingClientRect();
    var contentMetricsRight = Math.floor(contentMetrics.right);
    var contentMetricsLeft = Math.floor(contentMetrics.left);
    if (containerMetricsLeft > contentMetricsLeft && containerMetricsRight < contentMetricsRight) {
        return "both";
    } else if (contentMetricsLeft < containerMetricsLeft) {
        return "left";
    } else if (contentMetricsRight > containerMetricsRight) {
        return "right";
    } else {
        return "none";
    }
}

function toOrderNumber(str) {
    str = str.toString().replace(/^#/, "");
    if (str == parseInt(str, 10)) {
        str = str.substr(-11);
        var pad = "#00000000000";
        return pad.substring(0, pad.length - str.length) + str;
    } else return false;
}

var mobileDataTable = function ($table, replaceTitles) {
    replaceTitles = typeof replaceTitles !== "undefined" ? Boolean(~~replaceTitles) : true;

    $table.each(function () {
        var titles = [];
        var $thisTable = $(this);

        $thisTable.find("> thead > tr > th").each(function () {
            titles.push($(this).text());
        });

        if (replaceTitles) {
            $thisTable.find("> tbody > tr > td").each(function () {
                $(this).attr("data-title", titles[$(this).index()]);
            });
        }
    });
};

var calcHeightDashboard = function ($blocksArray, widthChanged, heightChanged, minusMain) {
    if ($blocksArray == undefined) return false;

    if (widthChanged == undefined) widthChanged = true;

    if (heightChanged == undefined) heightChanged = true;

    if (minusMain == undefined) var minusMain = 64 + 131 + 44;

    // Main content block
    var browserHeight = parseInt($("body").height());
    var finalHeightMain = 532;

    if (browserHeight > finalHeightMain + minusMain) {
        finalHeightMain = browserHeight - minusMain;
        $mainContent.css({ height: finalHeightMain + "px" });
    } else {
        $mainContent.css({ height: "532px" });
    }

    //    console.log('---------');
    for (var key in $blocksArray) {
        if ($mainContent.height() > 532 && heightChanged) {
            calcHeightBlockSimple($blocksArray[key], widthChanged, heightChanged, finalHeightMain);
        } else if ($blocksArray[key].width === true && widthChanged) {
            calcHeightBlockSimple($blocksArray[key], widthChanged, heightChanged, finalHeightMain);
        }
    }
};

function calcHeightBlockSimple(objectBlock, widthChanged, heightChanged, finalHeightMain) {
    var nameBlock = objectBlock.name;
    var apiBlock = window[nameBlock + "Api"];

    //if apiBlock is undefined
    if (apiBlock == undefined) return false;

    //if width and height not was changed
    if (!widthChanged && !heightChanged) return false;

    // console.log(nameBlock);
    var $block = window["$" + nameBlock];
    var blockTimeout = window[nameBlock + "Timeout"];
    var minusBlock = objectBlock.minus;

    showLoader($block, "");

    //if need change width
    if (widthChanged) {
        $block.css({ width: "auto" }).find(".jspContainer").css({ width: "100%" }).end().find(".jspPane").css({ width: "100%" });
    }

    //if need change height
    if (heightChanged) {
        //remove height
        $block.css({ height: "auto" }).find(".jspContainer").css({ height: "100%" });

        //if timeout of changed height was finished
        if (!blockTimeout) {
            //calc final height for block
            var finalHeightBlock = finalHeightMain - minusBlock;
            //set final height for block
            $block
                .css({ height: finalHeightBlock + "px" })
                .find(".jspContainer")
                .css({ height: finalHeightBlock + "px" });
        }
    }

    //if timeout of changed height was finished
    if (!blockTimeout) {
        //init timeout for reinitialise height
        blockTimeout = setTimeout(function () {
            apiBlock.reinitialise();
            hideLoader($block, "");
            blockTimeout = null;
        }, 50);
    }
}

function calcWidthBlockSimple(objectBlock, widthChanged) {
    var nameBlock = objectBlock[0].name;
    var apiBlock = window[nameBlock + "Api"];

    //if apiBlock is undefined
    if (apiBlock == undefined) return false;

    //if width and height not was changed
    if (!widthChanged) return false;

    //    console.log(nameBlock);
    var $block = window["$" + nameBlock];
    var blockTimeout = window[nameBlock + "Timeout"];

    showLoader($block, "");

    //if need change width
    if (widthChanged) {
        $block.css({ width: "auto" }).find(".jspContainer").css({ width: "100%" }).end().find(".jspPane").css({ width: "100%" });
    }

    //if timeout of changed height was finished
    if (!blockTimeout) {
        //init timeout for reinitialise height
        blockTimeout = setTimeout(function () {
            apiBlock.reinitialise();
            hideLoader($block, "");
            blockTimeout = null;
        }, 50);
    }
}

var calcWidthBlocks = function ($blocksArray, widthChanged) {
    if ($blocksArray == undefined) return false;

    if (!widthChanged) return false;

    $blocksArray.forEach(function (element) {
        var nameBlock = element.name;
        var apiBlock = window[nameBlock + "Api"];

        if (apiBlock == undefined) {
            return;
        }

        var $block = window["$" + nameBlock];
        var blockTimeout = window[nameBlock + "Timeout"];

        showLoader($block, "");

        //if need change width
        if (widthChanged) {
            $block.css({ width: "auto" }).find(".jspContainer").css({ width: "100%" }).end().find(".jspPane").css({ width: "100%" });
        }

        //if timeout of changed height was finished
        if (blockTimeout == undefined) {
            //console.log(blockTimeout);
            //init timeout for reinitialise height
            blockTimeout = setTimeout(function () {
                hideLoader($block, "");
                apiBlock.reinitialise();
                blockTimeout = null;
            }, 50);
        }
    });
};

function _notifyContentChangeCallback() {
    systemMessages(translate_js({ plug: "general_i18n", text: "system_message_changes_will_come_soon" }), "info");
}

function intval(num) {
    if (null == num) {
        return 0;
    } else if (typeof num == "number" || typeof num == "string") {
        num = num.toString();
        var dotLocation = num.indexOf(".");
        if (dotLocation > 0) {
            num = num.substr(0, dotLocation);
        }

        if (isNaN(Number(num))) {
            num = parseInt(num);
        }

        if (isNaN(num)) {
            return 0;
        }

        return Number(num);
    } else if (typeof num == "object" && num.length != null && num.length > 0) {
        return 1;
    } else if (typeof num == "boolean" && num === true) {
        return 1;
    }

    return 0;
}

function floatval(mixed_var) {
    return parseFloat(mixed_var) || 0;
}

function applePie() {
    return navigator.userAgent.match(/(iPhone|iPod|iPad)/i);
}

function normalize_discount(number) {
    var temp_number = floatval(number).toFixed(2) * 100;
    temp_number = intval(temp_number);
    return temp_number / 100;
}

function decimalAdjust(type, value, exp) {
    // If the exp is undefined or zero...
    if (typeof exp === "undefined" || +exp === 0) {
        return Math[type](value);
    }
    value = +value;
    exp = +exp;
    // If the value is not a number or the exp is not an integer...
    if (isNaN(value) || !(typeof exp === "number" && exp % 1 === 0)) {
        return NaN;
    }
    // Shift
    value = value.toString().split("e");
    value = Math[type](+(value[0] + "e" + (value[1] ? +value[1] - exp : -exp)));
    // Shift back
    value = value.toString().split("e");
    return +(value[0] + "e" + (value[1] ? +value[1] + exp : exp));
}

// Decimal round
if (!Math.round100) {
    Math.round100 = function (value, exp) {
        return decimalAdjust("round", value, exp);
    };
}
// Decimal floor
if (!Math.floor100) {
    Math.floor100 = function (value, exp) {
        return decimalAdjust("floor", value, exp);
    };
}
// Decimal ceil
if (!Math.ceil100) {
    Math.ceil100 = function (value, exp) {
        return decimalAdjust("ceil", value, exp);
    };
}

function ieDetection() {
    if (
        navigator.appName == "Microsoft Internet Explorer" ||
        !!(navigator.userAgent.match(/Trident/) || navigator.userAgent.match(/rv:11/)) ||
        (typeof $.browser !== "undefined" && $.browser.msie == 1)
    ) {
        return true;
    } else {
        return false;
    }
}

function searchByItem($form) {
    var type = $form.data("type");
    var keyword = encodeURIComponent($form.find("input[name=keywords]").val());
    var search_path = __site_url;
    var action = "";

    switch (type) {
        case "items":
            var category = $form.find("select[name=category]").val();
            var country = $form.find("select[name=country]").val();

            if (!category && !country && keyword.trim() == "") {
                systemMessages("Error: Search parameters can't be empty.", "error");
                return false;
            }

            action = category ? "category/" + category : "search";
            if (country) {
                action += "/country/" + country;
            }

            if (keyword) {
                action += "?keywords=" + encodeURIComponent(keyword);
            }

            break;
        case "category":
            if (keyword.trim() == "") {
                systemMessages("Error: Search parameters can't be empty.", "error");
                return false;
            }

            action = "categories?keywords=" + encodeURIComponent(keyword);
            break;
        case "directory":
            var industry = $form.find("select[name=industry]").val();
            var country = $form.find("select[name=country]").val();

            if (!industry && !country && keyword.trim() == "") {
                systemMessages("Error: Search parameters can't be empty.", "error");
                return false;
            }

            action = "directory/all";

            if (country) {
                action += "/country/" + country;
            }

            if (industry) {
                action += "/industry/" + industry;
            }

            if (keyword) {
                action += "?keywords=" + encodeURIComponent(keyword);
            }
            break;
        case "b2b":
            var industry = $form.find("select[name=industry]").val();
            var country = $form.find("select[name=country]").val();

            if (!industry && !country && keyword.trim() == "") {
                systemMessages("Error: Search parameters can't be empty.", "error");
                return false;
            }

            action = "b2b/all";

            if (keyword) {
                if (country) {
                    action += "/country/" + country;
                }

                if (industry) {
                    action += "/industry/" + industry;
                }

                action += "?keywords=" + encodeURIComponent(keyword);
            } else if (industry) {
                if (country) {
                    action += "/country/" + country;
                }

                action += "/industry/" + industry;
            } else if (country) {
                action += "/country/" + country;
            }
            break;
        case "help":
            if (!keyword) {
                systemMessages("Error: Search parameters can't be empty.", "message-error");
                return false;
            }

            action = "help/search/?keywords=" + encodeURIComponent(keyword);
            break;
        case "questions":
            var page = $form.find("select[name=page]").val();

            var available_pages = {
                user_guide: "user_guide/search/?keywords=",
                faq: "faq/search/?keywords=",
                topics: "topics/help?keywords=",
                community: "questions?keywords=",
            };

            if (keyword || page) {
                systemMessages("Error: Search parameters can't be empty.", "error");
                return false;
            }

            if (!page in available_pages) {
                systemMessages("Error: The data you sent does not appear to be valid.", "error");
                return false;
            }

            action = available_pages[page] + keyword;
            break;
        case "blogs":
            if (!keyword) {
                systemMessages("Error: Search parameters can't be empty.", "error");
                return false;
            }

            action = "?keywords=" + encodeURIComponent(keyword);
            search_path = __blog_url;
            break;
        case "events":
            var filterByTime = $form.find("select[name=time]").val();
            var filterByCountry = $form.find("select[name=country]").val();
            var filterByCategory = $form.find("input[name=category]").val();
            var filterByType = $form.find("select[name=type]").val();
            var filterByKeywords = $form.find("input[name=keywords]").val();
            var sorting = $form.find("input[name=sort]").val();
            var page = $form.data("page");

            if (!filterByCountry && !filterByKeywords && !filterByTime && !filterByType) {
                systemMessages("Error: Search parameters can't be empty.", "error");

                return false;
            }

            action = "ep_events";
            if ("past" === filterByTime || ("pastEvents" === page && !filterByTime)) {
                action += "/past";
            }

            if (filterByCountry) {
                action += "/country/" + filterByCountry;
            }

            if (filterByCategory) {
                action += "/category/" + filterByCategory;
            }

            if (filterByType) {
                action += "/type/" + filterByType;
            }

            if (filterByTime && !("pastEvents" !== page && "past" === filterByTime)) {
                action += "/time/" + filterByTime;
            }

            if (filterByKeywords) {
                action += "?keywords=" + encodeURIComponent(filterByKeywords);
            }

            if (sorting) {
                action += (filterByKeywords ? "&" : "?") + "sort=" + sorting;
            }

            break;
    }

    // For autocompletion
    $form.trigger("form:submit:autcomplete");

    var url = new URL(search_path + action);
    // url.searchParams.append('t', type);
    document.location.href = url;

    return false;
}

function updateActivity() {
    $.ajax({
        url: __current_sub_domain_url + "authenticate/checkSession",
    });
}

function hideDTbottom(thisDt) {
    var dtbottom = $(thisDt.selector + "_wrapper").find(".bottom");
    var settings = thisDt.fnSettings();
    var displayLength = settings._iDisplayLength || 0;
    var displayLengthMin = settings.aLengthMenu[0] || 1;
    var recordsTotal = settings.fnRecordsDisplay();
    var pages = displayLength > 0 ? Math.ceil(recordsTotal / displayLengthMin) : 1;

    if (pages > 1) {
        dtbottom.show();
    } else {
        dtbottom.hide();
    }
}

$.fn.hasScrollBar = function () {
    return this.get(0).scrollHeight > this.get(0).clientHeight;
};

var onDownloadFile = function ($this) {
    var body = $("body");
    var frameId = "file-download-frame";
    var iframe = $("#" + frameId);
    var document = $this.prop("href") || null;

    if (iframe.length === 0) {
        iframe = $("<iframe>");
        iframe.css({ display: "none" });
        iframe.attr({ id: frameId });
        body.append(iframe);
    }

    iframe.attr({ src: document });
};

var viewPassword = function ($this) {
    var $input = $this.siblings("input");

    if ($input.prop("type") == "text") {
        $input.prop("type", "password");
    } else {
        $input.prop("type", "text");
    }

    $this.toggleClass("ep-icon_visible ep-icon_invisible");
};

function open_info_dialog_100pr(title, content, is_ajax, buttons) {
    is_ajax = is_ajax || 0;

    buttons = buttons || [];

    BootstrapDialog.show({
        cssClass: "info-bootstrap-dialog bootstrap-dialog--h-100pr",
        title: title,
        message: $('<div class="h-100pr"></div>'),
        onshow: function (dialog) {
            var $modal_dialog = dialog.getModalDialog();
            $modal_dialog.addClass("modal-dialog-centered");

            if (is_ajax) {
                showLoader($modal_dialog.find(".modal-content"), "Loading...");

                $.get(content).done(function (html_resp) {
                    setTimeout(function () {
                        dialog.getMessage().append(html_resp);
                        hideLoader($modal_dialog.find(".modal-content"));
                    }, 200);
                });
            } else {
                dialog.getMessage().append(content);
            }
        },
        buttons: buttons,
        type: "type-light",
        size: "size-wide",
        closable: true,
        closeByBackdrop: true,
        closeByKeyboard: false,
        draggable: false,
        animate: true,
        nl2br: false,
    });
}

function open_info_dialog_100(title, bodyContent, footerContent, onAfterShow) {
    return BootstrapDialog.show({
        cssClass: "info-bootstrap-dialog bootstrap-dialog--h-100pr",
        title: title,
        onshow: function (dialog) {
            var $modal_dialog = dialog.getModalDialog();
            $modal_dialog.addClass("modal-dialog-centered");
            dialog.getModalBody().html(bodyContent);

            if (footerContent != "") {
                dialog.getModalFooter().html(footerContent).show();
            } else {
                dialog.getModalFooter().hide();
            }

            callFunction(onAfterShow || "showStatusModal", dialog);
        },
        type: "type-light",
        size: "size-wide",
        closable: true,
        closeByBackdrop: true,
        closeByKeyboard: false,
        draggable: false,
        animate: true,
        nl2br: false,
    });
}

function open_modal(title, content, is_ajax, buttons, validate, classes, closeCallBack) {
    is_ajax = is_ajax || 0;
    validate = Boolean(~~validate) || false;

    BootstrapDialog.show({
        cssClass: "info-bootstrap-dialog " + classes,
        title: title,
        message: $("<div></div>"),
        onhide: function () {
            if (typeof closeCallBack === "function" && closeCallBack) {
                closeCallBack();
            }
        },
        onshow: function (dialog) {
            var $modal_dialog = dialog.getModalDialog();
            $modal_dialog.addClass("modal-dialog-centered");
            if (is_ajax) {
                showLoader($modal_dialog.find(".modal-content"), "Loading...");
                getRequest(content, "html").then(function (html_resp) {
                    setTimeout(function () {
                        dialog.getModalFooter().append(buttons).css({ display: "flex" }).find(".bootstrap-dialog-footer").remove();
                        dialog.getMessage().append(html_resp);

                        if (dialog.getMessage().find(".modal-system-messages.errors").length) {
                            dialog.getModalFooter().html("");
                        }

                        hideLoader($modal_dialog.find(".modal-content"));
                    }, 200);
                });
            } else {
                setTimeout(function () {
                    dialog.getMessage().append(content);

                    if (validate == true) {
                        dialog
                            .getMessage()
                            .find(".validateModal")
                            .validationEngine("attach", {
                                promptPosition: "topLeft:0",
                                autoPositionUpdate: true,
                                focusFirstField: false,
                                scroll: false,
                                showArrow: false,
                                addFailureCssClassToField: "validengine-border",
                                onValidationComplete: function (form, status) {
                                    if (status) {
                                        if ($(form).data("callback") != undefined) window[$(form).data("callback")](form);
                                        else modalFormCallBack(form);
                                    } else {
                                        systemMessages(translate_js({ plug: "general_i18n", text: "validate_error_message" }), "error");
                                    }
                                },
                            });
                    }
                }, 200);
            }
        },
        // buttons:buttons,
        type: "type-light",
        size: "size-wide",
        closable: true,
        closeByBackdrop: false,
        closeByKeyboard: false,
        draggable: false,
        animate: true,
        nl2br: false,
    });
}

function open_email_success_dialog(title, content, buttons) {
    buttons = buttons || [];

    BootstrapDialog.show({
        cssClass: "info-bootstrap-dialog friend-invite__modal",
        title: title,
        message: $('<div class="ep-tinymce-text"></div>'),
        onshow: function (dialog) {
            var $modal_dialog = dialog.getModalDialog();
            $modal_dialog.addClass("modal-dialog-centered");
            dialog.getMessage().append(content);
        },
        buttons: buttons,
        type: "type-light",
        size: "size-wide",
        closable: true,
        closeByKeyboard: false,
        draggable: false,
        animate: true,
        nl2br: false,
    });
}

function openVideoModal($this) {
    var link = "https://www.youtube.com/embed/" + $this.data("href");

    if ($this.data("autoplay")) {
        link = "https://www.youtube.com/embed/" + $this.data("href") + "?autoplay=1";
    }

    var title = $this.data("title");
    var classes = "bootstrap-dialog--video";
    var content =
        '<iframe class="js-popup-video-iframe"\
                        width="100%"\
                        height="100%"\
                        src="' +
        link +
        '"\
                        frameborder="0"\
                        allow="autoplay; encrypted-media"\
                        allowfullscreen>\
                    </iframe>';
    var currentScrollPosition = $(globalThis).scrollTop();
    var scrollToEl = function () {
        var scrollPosition = $(globalThis).scrollTop();
        if (currentScrollPosition !== scrollPosition) {
            $(globalThis).scrollTop(currentScrollPosition);
        }
    };

    open_modal(title, content, false, null, false, classes, scrollToEl);
}

var getKeyCodeAndr = function (str) {
    return str.charCodeAt(str.length);
};

var dataTableDrawHidden = function () {
    $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
};

var openNewOrderNotificationModal = function (id, message) {
    message = message || "The order %s was created.".replace("%s", toOrderNumber(id));
    console.log(message);
    open_result_modal({
        content: message,
        type: "success",
        closable: true,
        buttons: [
            {
                label: translate_js({ plug: "BootstrapDialog", text: "view_order" }),
                cssClass: "btn-success",
                action: function () {
                    this.disable();
                    location.href = __site_url + "order/my/order_number/" + id;
                },
            },
            {
                label: translate_js({ plug: "BootstrapDialog", text: "close" }),
                cssClass: "btn btn-light",
                action: function (dialog) {
                    dialog.close();
                },
            },
        ],
    });
};

var companyNotificationModal = function (params) {
    var buttons = [];

    if (params.additional_button) {
        buttons.push({
            label: translate_js({ plug: "general_i18n", text: params.additional_button.text }),
            cssClass: params.additional_button.class,
            action: function () {
                location.href = params.additional_button.location;
            },
        });
    }

    buttons.push({
        label: translate_js({ plug: "BootstrapDialog", text: "close" }),
        cssClass: "btn-light",
        action: function (dialogRef) {
            dialogRef.close();
        },
    });

    open_result_modal({
        title: params.title,
        subTitle: params.subTitle || "",
        type: "success",
        closable: true,
        buttons: buttons,
    });
};

var fancyboxOpenByHref = function ($this, mw) {
    if ($.fancybox.isActive) {
        return false;
    }

    var href = "";
    var mw = mw || "";

    if (typeof $this == "object") {
        href = $this.data("href");
    } else if (typeof $this == "string") {
        href = $this;
    }
    if (href == "") {
        return false;
    }

    $.fancybox.open({
        href: href,
        width: fancyW,
        height: "auto",
        maxWidth: 700,
        autoSize: false,
        loop: false,
        helpers: {
            title: {
                type: "inside",
                position: "top",
            },
            overlay: {
                locked: true,
            },
        },
        lang: __site_lang,
        i18n: translate_js_one({ plug: "fancybox" }),
        modal: true,
        padding: fancyP,
        closeBtn: true,
        closeBtnWrapper: ".fancybox-skin .fancybox-title",
        beforeLoad: function () {
            if (typeof $this == "object") {
                var $elem = $this;

                if ($elem.data("before-callback") != undefined) {
                    window[$elem.data("before-callback")](this);
                }

                if ($elem.data("title")) this.title = htmlEscape($elem.data("title"));

                if ($elem.data("h")) {
                    this.autoHeight = false;
                    this.height = $elem.data("h");
                }

                if ($elem.data("w")) {
                    this.width = $elem.data("w");
                    this.autoWidth = false;
                } else {
                    this.width = fancyW;
                }

                if ($elem.data("mw")) {
                    this.maxWidth = $elem.data("mw");
                }

                if ($elem.data("mnh")) {
                    this.minHeight = $elem.data("mnh");
                }

                if ($elem.data("p") != undefined) {
                    this.padding = [$elem.data("p"), $elem.data("p"), $elem.data("p"), $elem.data("p")];

                    if ($elem.data("p") == 0) {
                        this.wrapCSS = "fancybox-title--close";
                    }
                } else {
                    this.padding = [fancyP, fancyP, fancyP, fancyP];
                }
            } else {
                if (mw != "") {
                    this.maxWidth = mw;
                }
            }
        },
    });
};

function open_result_modal(params) {
    var title = params.title || undefined,
        titleUppercase = params.titleUppercase || false,
        titleImage = params.titleImage || false,
        subTitle = params.subTitle || undefined,
        subTitleCustom = params.subTitleCustom || undefined,
        titleType = "",
        content = params.content || "",
        delimeterClass = params.delimeterClass || "bootstrap-dialog--content-delimeter",
        contentFooter = params.contentFooter || "",
        isAjax = params.isAjax || 0,
        buttons = params.buttons || [],
        validate = Boolean(~~params.validate) || false,
        classes = params.classes || "",
        closeByBg = params.closeByBg || undefined,
        typeModal = params.type || "info",
        iconModal = params.icon || undefined,
        closableModal = Boolean(~~params.closable) || false,
        closeCallBack = params.closeCallBack || undefined,
        iconModalType = "ok-stroke2",
        styles = params.styles,
        onShownCallback = params.onShownCallback || undefined,
        keepOtherModals = params.keepOtherModals,
        iconImage = params.iconImage,
        classContent = "modal-tinymce-text",
        keepModal = params.keepModal;

    if (closeByBg !== undefined && closeByBg == true) {
        closeByBg = true;
    } else {
        closeByBg = false;
    }

    if (params.classContent !== undefined) {
        classContent = params.classContent;
    }

    switch (typeModal) {
        case "info":
            typeModal = "info";
            iconModalType = "info-character fs-25";
            titleType = "Info";
            break;
        case "success":
            typeModal = "success";
            iconModalType = "ok-stroke2 fs-26";
            titleType = "Success!";
            break;
        case "warning":
            typeModal = "warning";
            iconModalType = "warning-character";
            titleType = "Warning!";
            break;
        case "error":
            typeModal = "danger";
            iconModalType = "remove-stroke2 fs-20";
            titleType = "Error!";
            break;
        case "question":
            typeModal = "info";
            iconModalType = "question-character";
            titleType = "Question";
            break;
        case "image":
            typeModal = "image";
            iconModalType = "image";
            break;
        case "certified":
            titleType = "Warning!";
            typeModal = "warning";
            iconModalType = "iconImage";
            break;
    }

    var typeModalClass = " bootstrap-dialog--results-" + typeModal;

    BootstrapDialog.show({
        cssClass: "bootstrap-dialog--results " + typeModalClass + " " + classes,
        title: title != undefined ? title : titleType,
        size: BootstrapDialog.SIZE_NORMAL,
        message: $("<div>"),
        onhide: function () {
            if (typeof closeCallBack === "function" && closeCallBack != undefined) {
                closeCallBack();
            }
        },
        onshown: function () {
            if (typeof onShownCallback === "function" && onShownCallback != undefined) {
                onShownCallback();
            }
        },
        onshow: function (dialog) {
            var $dialogHeader = dialog.getModalHeader().find(".bootstrap-dialog-header");

            if (titleUppercase == true) {
                $dialogHeader.find(".bootstrap-dialog-title").addClass("tt-uppercase");
            }

            if (iconModalType === "iconImage") {
                $dialogHeader.prepend(
                    '<div class="bootstrap-dialog-icon-title"><div class="bootstrap-dialog-icon-image"><img class="image" src="' + iconImage + '"></div></div>'
                );
            } else if (iconModalType !== "image") {
                $dialogHeader.prepend(
                    '<div class="bootstrap-dialog-icon-title"><i class="ep-icon ep-icon_' +
                        (iconModal != undefined ? iconModal : iconModalType) +
                        '"></i></div>'
                );
            } else {
                if (titleImage !== undefined) {
                    $dialogHeader
                        .closest(".modal-header")
                        .prepend('<div class="bootstrap-dialog-image-title"><img class="image" src="' + titleImage + '"></div>');
                }
            }

            if (subTitle != undefined) {
                $dialogHeader.append('<h6 class="bootstrap-dialog-sub-title">' + subTitle + "</h6>");
            }

            if (subTitleCustom != undefined) {
                $dialogHeader.append(subTitleCustom);
            }

            var $modal_dialog = dialog.getModalDialog();
            var addValidationIfPossible = function () {
                if (!validate) {
                    return;
                }

                enableFormValidation(dialog.getMessage().find(".validateModal"));
            };
            $modal_dialog.addClass("modal-dialog-scrollable modal-dialog-centered");

            if (!keepModal) {
                $(".modal").modal("hide");
            }

            if (styles) {
                $modal_dialog.css(styles);
            }

            if (isAjax) {
                showLoader($modal_dialog.find(".modal-content"), "Loading...");
                $.get(content, null, null, "text").done(function (resp) {
                    var isJsonResponse = false;
                    try {
                        resp = JSON.parse(resp);
                        isJsonResponse = true;
                    } catch (error) {
                        isJsonResponse = false;
                    }

                    setTimeout(function () {
                        hideLoader($modal_dialog.find(".modal-content"));

                        $modal_dialog.addClass(delimeterClass);

                        if (isJsonResponse) {
                            if (resp.mess_type == "success") {
                                dialog.getMessage().append(resp.content);

                                if (resp.footer != undefined) {
                                    dialog.getModalFooter().html(resp.footer);
                                }
                            }
                        } else {
                            dialog.getMessage().append(resp);
                        }

                        addValidationIfPossible();
                    }, 200);
                });
            } else {
                setTimeout(function () {
                    if (content.length > 0) {
                        $modal_dialog.addClass(delimeterClass);

                        dialog.getMessage().append('<div class="' + classContent + '">' + content + "</div>");
                        addValidationIfPossible();

                        if (contentFooter.length > 0) {
                            dialog.getModalFooter().append(contentFooter);
                        }
                    }
                }, 200);
            }

            if (Object.size(buttons) > 0) {
                $modal_dialog.addClass("bootstrap-dialog--footer-padding");
            }

            dialog.getModalFooter().show();
        },
        buttons: buttons,
        closable: closableModal,
        closeByBackdrop: closeByBg,
        closeByKeyboard: false,
        draggable: false,
        animate: true,
        nl2br: false,
    });
}

function open_modal_dialog(params) {
    var btn = params.btn || null,
        title = params.title || undefined,
        subTitle = params.subTitle || undefined,
        footerContent = params.footerContent || "",
        content = params.content || "",
        isAjax = params.isAjax || 0,
        btnSubmitText = params.btnSubmitText || "Submit",
        btnSubmitCallBack = params.btnSubmitCallBack || undefined,
        btnCancelText = params.btnCancelText || translate_js({ plug: "BootstrapDialog", text: "cancel" }),
        buttons = params.buttons || [
            {
                label: btnCancelText,
                cssClass: "btn-dark",
                action: function (dialogRef) {
                    dialogRef.close();
                },
            },
            {
                label: btnSubmitText,
                cssClass: "btn-primary",
                action: function (dialogRef) {
                    if (btnSubmitCallBack == undefined) {
                        var $form = dialogRef.getModalBody().find("form");

                        if ($form.length) {
                            $form.submit();
                        }
                    } else {
                        var callBack = btnSubmitCallBack;
                        var $button = this;
                        $button.disable();

                        window[callBack]($thisBtn);
                        dialogRef.close();
                    }
                },
            },
        ],
        validate = Boolean(~~params.validate) || false,
        classes = params.classes || "",
        closeByBg = params.closeByBg || undefined,
        closableModal = Boolean(~~params.closable) || true,
        closeCallBack = params.closeCallBack || undefined,
        onShownCallback = params.onShownCallback || undefined,
        keepModal = params.keepModal;

    if (closeByBg !== undefined && closeByBg == true) {
        closeByBg = true;
    } else {
        closeByBg = false;
    }

    BootstrapDialog.show({
        cssClass: "info-bootstrap-dialog bootstrap-dialog--form inputs-40 " + classes,
        title: title,
        type: "type-light",
        size: "size-wide",
        message: $("<div>"),
        onhide: function () {
            if (typeof closeCallBack === "function" && closeCallBack != undefined) {
                closeCallBack();
            }
        },
        onshow: function (dialog) {
            var $dialogHeader = dialog.getModalHeader().find(".bootstrap-dialog-header");
            var $modal_dialog = dialog.getModalDialog();
            var addValidationIfPossible = function () {
                if (!validate) {
                    return;
                }

                enableFormValidation(dialog.getMessage().find(".validateModal"));
            };

            $modal_dialog.addClass("modal-dialog-scrollable modal-dialog-centered");

            if (btn) {
                $modal_dialog.addClass($(btn).data("classes"));
            }

            if (subTitle != undefined) {
                $dialogHeader.append('<h6 class="bootstrap-dialog-sub-title">' + subTitle + "</h6>");
            }

            if (!keepModal) {
                $(".modal").modal("hide");
            }

            if (isAjax) {
                showLoader($modal_dialog.find(".modal-content"), "Loading...");

                $.get(content).done(function (html_resp) {
                    setTimeout(function () {
                        hideLoader($modal_dialog.find(".modal-content"));

                        if (html_resp.length > 0) {
                            dialog.getMessage().append(html_resp);
                            addValidationIfPossible();
                        }
                    }, 200);
                });
            } else {
                setTimeout(function () {
                    if (content.length > 0) {
                        dialog.getMessage().append('<div class="modal-tinymce-text">' + content + "</div>");
                        addValidationIfPossible();
                    }
                }, 200);
            }

            if (footerContent != "") {
                dialog.getModalFooter().html(footerContent).show();
            } else if (Object.size(buttons) > 0) {
                $modal_dialog.addClass("bootstrap-dialog--footer-submit");
                dialog.getModalFooter().show();
            }
        },
        onshown: function () {
            if (typeof onShownCallback === "function" && onShownCallback != undefined) {
                onShownCallback();
            }
        },
        buttons: buttons,
        closable: closableModal,
        closeByBackdrop: closeByBg,
        closeByKeyboard: false,
        draggable: false,
        animate: true,
        nl2br: false,
    });
}

function initNewTinymce(editor, params) {
    var valHook = params.valHook || undefined,
        validate = params.validate || "validate[required, maxSize[20000]]";

    var container = $(editor.editorContainer);
    var containerId = container.attr("id");
    var showPrompt = function (e) {
        var selector = "." + containerId + "formError";
        var errorBox = container.siblings(selector);
        if (errorBox.length) {
            errorBox.show();
            errorBox.css("opacity", 1);
        }
    };
    var hidePrompt = function (e) {
        var selector = "." + containerId + "formError";
        var errorBox = container.siblings(selector);
        if (errorBox.length) {
            errorBox.hide();
            errorBox.css("opacity", 0);
        }
    };
    var reValidate = function () {
        container.validationEngine("validate");
    };

    container.addClass(validate).setValHookType(valHook).on("blur", showPrompt);

    editor.on("blur", hidePrompt);
    editor.on("dirty", reValidate);
    editor.on("blur", function () {
        reValidate();
        showPrompt();
    });
    editor.on("click change", function () {
        if (this.getContent() === "" && container.siblings("." + containerId + "formError").length) {
            reValidate();
            showPrompt();
        }
    });
}

function updateTemplateScaleSize(param) {
    var wrapper = param.wrapper,
        element = param.element,
        elementInner = param.elementInner,
        elData = {
            width: elementInner.outerWidth(),
            height: elementInner.outerHeight(),
        },
        wrData = {
            width: wrapper.outerWidth(),
            height: wrapper.outerHeight(),
        },
        scale = Math.min(wrData.width / elData.width, wrData.height / elData.height);

    elementInner.css({
        transform: "translate(0, 0) scale(" + scale + ")",
        "transform-origin": "0 0",
    });

    var iframeHeight = Math.ceil((elementInner.height() + 1) * scale);

    element.attr("height", iframeHeight);
    hideLoader(wrapper);
}

var callHeaderImageModal = function (params) {
    var title = params.title || undefined,
        titleUppercase = params.titleUppercase || false,
        subTitle = params.subTitle || undefined,
        content = params.content || "",
        isAjax = params.isAjax || true,
        validate = Boolean(~~params.validate) || false,
        classes = params.classes || "",
        closable = Boolean(~~params.closable) || true,
        type = params.type || "image",
        buttons = params.buttons || [],
        titleImage = params.titleImage || "";
    (closeCallBack = params.closeCallBack || undefined),
        open_result_modal({
            titleImage: titleImage,
            titleUppercase: titleUppercase,
            title: title,
            subTitle: subTitle,
            content: content,
            isAjax: isAjax,
            validate: validate,
            classes: classes,
            closable: closable,
            type: type,
            buttons: buttons,
            closeCallBack: closeCallBack,
        });
};

var modalResizeReturnPositionOff = function () {
    $(window).off("resize.fancybox");
};

var modalResizeReturnPosition = function () {
    var modal = document.getElementsByClassName("fancybox-inner")[0],
        height = modal.scrollHeight,
        scroll = modal.scrollTop,
        setTimeoutResizeWindow,
        setTimeoutScrollTo;

    $(window).on("resize.fancybox", function () {
        if (modal.scrollTop !== 0 && scroll !== modal.scrollTop) {
            clearTimeout(setTimeoutResizeWindow);
            clearTimeout(setTimeoutScrollTo);
            scroll = modal.scrollTop;
        }

        setTimeoutResizeWindow = setTimeout(function () {
            clearTimeout(setTimeoutResizeWindow);
            clearTimeout(setTimeoutScrollTo);

            setTimeoutScrollTo = setTimeout(function () {
                modal.scrollTo(0, scroll);
            }, 500);
        }, 100);
    });
};

var callMoveByLink = function ($thisBtn) {
    var link = $thisBtn.data("link");
    var target = $thisBtn.data("target");

    if (link !== undefined) {
        if (target !== undefined) {
            window.open(link, target);
        } else {
            window.location.href = link;
        }
    }
};

var showSuccessSubscribtionPopup = function (message) {
    open_result_modal({
        title: translate_js({ plug: "general_i18n", text: "subscribe_popup_success_txt" }),
        subTitle: message,
        type: "success",
        closable: true,
        closeByBg: true,
        buttons: [
            {
                label: translate_js({
                    plug: "BootstrapDialog",
                    text: "ok",
                }),
                cssClass: "btn btn-light",
                action: function (dialog) {
                    dialog.close();
                },
            },
        ],
    });
};

var showSuccessSubscribtionPopupIfNeeded = function () {
    var isSubscribed = getCookie("_ep_subscriber_confirmed");

    if (isSubscribed) {
        var message = translate_js({ plug: "general_i18n", text: "js_subscribe_successfully_subscribed_message" });

        if (existCookie("_ep_success_subscribe_dm_message_key")) {
            message = translate_js({ plug: "general_i18n", text: getCookie("_ep_success_subscribe_dm_message_key") });
            removeCookie("_ep_success_subscribe_dm_message_key");
        }

        showSuccessSubscribtionPopup(message);
        removeCookie("_ep_subscriber_confirmed");
    }
};

var userSharePopupIsActive = false;
var userSharePopup = function ($this) {

    if (userSharePopupIsActive) {
        return true;
    }

    userSharePopupIsActive = true;
    var type = $this.data("type");
    var itemId = $this.data("item");

    postRequest(`${__current_sub_domain_url}user/popup_forms/share`, {type, itemId}).then(function (response) {

        if (response.mess_type === 'success') {
            open_result_modal({
                title: response.title,
                subTitle: response.subTitle,
                content: response.content,
                isAjax: false,
                closable: true,
                type: "info",
                icon: "share-stroke2",
                delimeterClass: "bootstrap-dialog--content-delimeter2 bootstrap-dialog--no-border",
                buttons: [],
            });
        } else {
            systemMessages(response.message, response.mess_type);
        }

        userSharePopupIsActive = false;

    }).catch(onRequestError);
};
