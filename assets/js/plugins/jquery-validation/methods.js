import $ from "jquery";
import { translate } from "@src/i18n";

export default async () => {
    // @ts-ignore
    $.extend($.validator.messages, {
        required: translate({ plug: "jquery_validation", text: "required" }),
        equalTo: translate({ plug: "jquery_validation", text: "equalTo" }),
        max: translate({ plug: "jquery_validation", text: "max" }),
    });

    // @ts-ignore
    $.validator.addMethod(
        "validUserName",
        value => {
            return /^[a-zA-Z][a-zA-Z\ \'\-]{1,}$/.test(value);
        },
        translate({ plug: "jquery_validation", text: "validUserName" })
    );
    // @ts-ignore
    $.validator.addMethod(
        "selectPhoneMask",
        // eslint-disable-next-line func-names
        (value, element, param) => param,
        translate({ plug: "jquery_validation", text: "selectPhoneMask" })
    );
    // @ts-ignore
    $.validator.addMethod(
        "completePhoneMask",
        // eslint-disable-next-line func-names
        (value, element, param) => param,
        translate({ plug: "jquery_validation", text: "completePhoneMask" })
    );
    // @ts-ignore
    $.validator.addMethod(
        "phoneNumber",
        value => {
            return /^$|^[1-9]\d{0,24}$/.test(value);
        },
        translate({ plug: "jquery_validation", text: "phoneNumber" })
    );
    // @ts-ignore
    $.validator.addMethod(
        "minSize",
        // eslint-disable-next-line func-names
        function (value, element, param) {
            const length = Array.isArray(value) ? value.length : this.getLength(value, element);
            return this.optional(element) || length >= param;
        },
        translate({ plug: "jquery_validation", text: "minSize" })
    );
    // @ts-ignore
    $.validator.addMethod(
        "maxSize",
        // eslint-disable-next-line func-names
        function (value, element, param) {
            const length = Array.isArray(value) ? value.length : this.getLength(value, element);
            return this.optional(element) || length <= param;
        },
        translate({ plug: "jquery_validation", text: "maxSize" })
    );
    // @ts-ignore
    $.validator.addMethod(
        "noWhitespaces",
        value => {
            if (value === "") {
                return true;
            }

            return value.trim() === value;
        },
        translate({ plug: "jquery_validation", text: "noWhitespaces" })
    );
    // @ts-ignore
    $.validator.addMethod(
        "emailWithWhitespaces",
        value => {
            if (value === "") {
                return true;
            }

            return /^([\s]*)?(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))([\s]*)?$/.test(
                value
            );
        },
        translate({ plug: "jquery_validation", text: "email" })
    );
    // @ts-ignore
    $.validator.addMethod(
        "companyTitle",
        value => {
            return /^[A-Za-z0-9][0-9A-Za-z-_., '&()]+$/.test(value);
        },
        translate({ plug: "jquery_validation", text: "companyTitle" })
    );
    // @ts-ignore
    $.validator.addMethod(
        "naturalNumber",
        value => {
            return /^[1-9][0-9]*$/.test(value);
        },
        translate({ plug: "jquery_validation", text: "naturalNumber" })
    );
    // @ts-ignore
    $.validator.addMethod(
        "zipCode",
        value => {
            return /^[0-9A-Za-z\-\. ]{3,20}$/.test(value);
        },
        translate({ plug: "jquery_validation", text: "zipCode" })
    );
};
