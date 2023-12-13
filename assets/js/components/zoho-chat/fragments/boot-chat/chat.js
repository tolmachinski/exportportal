import $ from "jquery";

import showMarkerUserMepAction from "@src/components/navigation/fragments/show-marker-user-mep-action";

export class Chat {
    constructor(button, url, code, domain, userName = null, userEmail = null) {
        this.$btnCallMainChat = button;
        this.$counterMessages = null;
        this.$floatBtnChat = null;
        this.$scriptnChat = null;
        this.addMarker = null;
        this.zohoStart = true;
        this.user_name = userName;
        this.user_email = userEmail;
        this.domain = domain;
        this.ccode = code;
        this.url = url;
        this.zoho = {};
    }

    onInitMainChat() {
        if (!this.zohoStart || document.getElementById("zsiqscript")) {
            return false;
        }

        this.zohoStart = false;
        this.zoho.salesiq = this.zoho.salesiq || {
            widgetcode: this.ccode,
            values: {},
            ready: function () {},
        };

        const firstTag = document.getElementsByTagName("script")[0];
        const script = document.createElement("script");
        script.type = "text/javascript";
        script.id = "zsiqscript";
        script.defer = true;
        script.src = this.url;
        firstTag.parentNode.insertBefore(script, firstTag);

        this.hideFloatBtnMessages();
        this.zoho.salesiq.ready = () => {
            this.zoho.salesiq.domain(this.domain);

            var ifIssetChat = setInterval(() => {
                this.$floatBtnChat = $("#zsiq_float");
                this.$counterMessages = $("#zsiq_unreadcnt");

                if (this.$floatBtnChat.length) {
                    this.setUserData();
                    this.ifNewMessages();

                    clearInterval(ifIssetChat);
                }
            }, 100);
        };
    }

    onShowMainChat(button) {
        this.$floatBtnChat = $("#zsiq_float");

        if (!this.$floatBtnChat.length) {
            this.onInitMainChat();

            var $icon = button.find(".ep-icon");
            var $iconSvgUpdates = button.find(".js-svg-icon-updates");
            var $iconSvgChat = button.find(".js-svg-icon-chat");

            if ($icon.length) {
                $icon.addClass("ep-icon_updates rotate-circle");
            } else {
                $iconSvgChat.hide();
                $iconSvgUpdates.show().addClass('rotate-circle');
            }

            var ifIssetChat = setInterval(function () {
                this.$floatBtnChat = $("#zsiq_float");

                if (this.$floatBtnChat.length) {
                    this.$floatBtnChat.trigger("click");

                    var ifOppenedChat = setInterval(function () {
                        if ($(".zls-sptwndw").hasClass("siqanim")) {
                            if($icon.length){
                                button.find(".ep-icon").removeClass("ep-icon_updates rotate-circle");
                            }else{
                                $iconSvgChat.show();
                                $iconSvgUpdates.hide().removeClass('rotate-circle');
                            }
                            this.zohoStart = true;
                            clearInterval(ifOppenedChat);
                        } else {
                            this.$floatBtnChat.trigger("click");
                        }
                    }, 1000);

                    clearInterval(ifIssetChat);
                }
            }, 100);
        } else {
            this.zohoStart = true;
            this.setUserData();
            this.$floatBtnChat.trigger("click");
        }
    }

    hideFloatBtnMessages() {
        var style = document.createElement("style");
        var ref = document.querySelector("script");

        style.innerHTML = "body .zsiq_floatmain { display: none!important; } .siq_showload:after{border-radius: 0!important;background: #ffffff!important; }";
        ref.parentNode.insertBefore(style, ref);
    }

    setUserData() {
        this.zoho.salesiq.visitor.name(this.user_name);
        this.zoho.salesiq.visitor.email(this.user_email);
    }

    ifNewMessages() {
        this.addMarker = new MutationObserver((mutations, observer) => {
            if ($(mutations[0].target).css("display") === "none") {
                // mainChat.$btnMainChat.removeClass('btn-main-chat--active');
                this.$btnCallMainChat.removeClass("active-min");
                // .find('.epuser-line__circle-sign').remove();
                showMarkerUserMepAction("chat", false);
            } else {
                // mainChat.$btnMainChat.addClass('btn-main-chat--active');
                this.$btnCallMainChat.addClass("active-min");
                // .append('<span class="epuser-line__circle-sign bg-green"></span>');
                showMarkerUserMepAction("chat", true);
            }
        });

        this.addMarker.observe(this.$counterMessages.get(0), {
            childList: true,
            subtree: true,
        });
    }
}
