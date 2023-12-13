import $ from "jquery";
import { closeFancyBoxConfirm } from "@src/plugins/fancybox/v3/util";
import { TEMPLATES } from "@src/epl/common/popups/templates";
import { hideLoader, showLoader } from "@src/util/common/loader";
import getRequest from "@src/util/http/get-request";
import { LANG } from "@src/common/constants";

const createModalHeader = (instance, opts) => {
    const { title } = opts;
    const fancyboxInner = instance.$refs.inner;
    const headerTpl = TEMPLATES.modalHeaderTpl;

    if (title) {
        const titleWrap = fancyboxInner.find(".fancybox-title");

        if (!titleWrap.length) {
            const titleTemplate = `<h2 class="fancybox-title">${title}</h2>`;
            fancyboxInner.find(".fancybox-content").prepend(headerTpl.replace("{{CONTENT}}", titleTemplate));
        }
    }
};

const createCloseBtn = (instance, opts) => {
    const { closeBtn, closeBtnWrapper, i18n } = opts;
    const fancyboxInner = instance.$refs.inner;

    if (closeBtn) {
        const closeBtnPattern = $(TEMPLATES.btnTpl.closeBtn.replace("{{CLOSE}}", i18n[LANG].close).replace("{{CLOSE_MESSAGE}}", i18n[LANG].close_message));

        // eslint-disable-next-line no-param-reassign
        instance.current.$closeBtn = closeBtnPattern.appendTo(fancyboxInner.find(closeBtnWrapper));
        instance.current.$closeBtn.on("click", closeFancyBoxConfirm.bind(instance.current.$closeBtn));
    }
};

const createButton = function (dialog, { label = null, title = "", cssClass = "", action = () => {} }) {
    const button = $('<button class="btn fancybox-dialog__button"></button>');

    // Label
    if (label) {
        button.append(label);
    }

    // title
    if (title) {
        button.attr("title", title);
    }

    // Css class
    button.addClass(cssClass && cssClass.trim() ? cssClass : "btn-secondary");

    // Button on click
    button.on("click", event => {
        action.call($(this), dialog, event);
    });

    return button;
};

const createFooterButtons = (instance, buttonsOptions = []) => {
    const container = instance.$refs.inner.find(".fancybox-dialog__footer-buttons");

    buttonsOptions.forEach(options => {
        container.append(createButton(instance, options));
    });
};

const setModalDimensions = (instance, mw = null, mh = null) => {
    const content = instance.$refs.inner.find(".fancybox-content");
    if (mw) {
        content.css("max-width", mw);
    }

    if (mh) {
        content.css("max-height", mh);
    }
};

const createModalContent = async (instance, opts) => {
    const fancyboxInner = instance.$refs.inner;
    let contentWrap = fancyboxInner.find(".fancybox-content");
    const { isDialog, mw = null, mh = null, type, src, wrClass, isVideoModal } = opts;

    contentWrap.removeClass("fancybox-content").wrap('<div class="fancybox-content"></div>');
    contentWrap = fancyboxInner.find(".fancybox-content");

    if (wrClass) {
        contentWrap.addClass(wrClass);
    }

    if (!isDialog) {
        const contentBodyTpl = TEMPLATES.modalContentBodyTpl;

        if (type !== "ajax") {
            contentWrap.html(contentBodyTpl.replace("{{CONTENT}}", isVideoModal ? "" : $(src).html()));
        } else {
            contentWrap.html(contentBodyTpl.replace("{{CONTENT}}", contentWrap.html()));
        }

        if (isVideoModal) {
            const videoContent = instance.current.$content;

            videoContent.addClass("fancybox-video");
            fancyboxInner.find(".fancybox-body").append(videoContent);
        }

        // Create title
        createModalHeader(instance, opts);
    } else {
        const { iconModal, iconModalType, title, subTitle, isAjax, ajaxUrl, buttons, contentFooter, delimiterClass, bodyWrapper } = opts;
        const icon = `<i class="ep-icon ep-icon_${iconModal !== undefined ? iconModal : iconModalType}"></i>`;
        const subTitleTemplate = subTitle ? `<p class="fancybox-dialog__subtitle">${subTitle}</p>` : "";
        const bodyWrap = '<div class="fancybox-dialog__body modal-tinymce-text"></div>';
        let footerWrap = "";

        if (buttons.length) {
            footerWrap = `
                <div class="fancybox-dialog__footer">
                    <div class="fancybox-dialog__footer-buttons"></div>
                </div>
            `;
        }

        if (contentFooter) {
            footerWrap = `<div class="fancybox-dialog__footer">${contentFooter}</div>`;
        }

        const dialogContent = TEMPLATES.dialogContentTpl
            .replace("{{ICON}}", icon)
            .replace("{{TITLE}}", title)
            .replace("{{SUBTITLE}}", subTitleTemplate)
            .replace("{{BODY_WRAPPER}}", bodyWrap)
            .replace("{{FOOTER_WRAPPER}}", footerWrap);

        contentWrap.html(dialogContent);

        if (isAjax && ajaxUrl) {
            showLoader(contentWrap, "Loading...");

            try {
                const { mess_type: messType, content } = await getRequest(ajaxUrl, "json");

                if (messType === "success") {
                    $(instance.current.$slide).addClass(delimiterClass);
                    $(fancyboxInner).find(bodyWrapper).html(content);
                }
            } catch (error) {
                $(fancyboxInner).find(bodyWrapper).html(TEMPLATES.errorTpl);
            } finally {
                hideLoader(contentWrap);
            }
        } else if (src) {
            if (type === "html") {
                $(fancyboxInner).find(bodyWrapper).append(src);
            } else if (type === "inline") {
                $(fancyboxInner).find(bodyWrapper).append($(src));
            }
            $(instance.current.$slide).addClass(delimiterClass);
        }

        if (buttons) {
            createFooterButtons(instance, buttons);
        }
    }

    setModalDimensions(instance, mw, mh);
};

const afterLoad = instance => {
    const { opts } = instance.current;

    // Create modal content
    createModalContent(instance, opts);
    // Create close button
    createCloseBtn(instance, opts);
};

const contentMaxWidth = (instance, max) => {
    afterLoad(instance);
    instance.$refs.inner.find(".fancybox-content").css("max-width", max);
};

export default afterLoad;
export { contentMaxWidth };
