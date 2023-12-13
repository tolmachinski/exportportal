import $ from "jquery";

import { hideLoader, showLoader } from "@src/util/common/loader";
import { LANG, SITE_URL, SUBDOMAIN_URL } from "@src/common/constants";
import { enableFormValidation } from "@src/plugins/validation-engine/index";
import { systemMessages } from "@src/util/system-messages/index";
import { translate } from "@src/i18n";
import handleRequestError from "@src/util/http/handle-request-error";
import RequestError from "@src/util/http/RequestError";
import callFunction from "@src/util/common/call-function";
import postRequest from "@src/util/http/post-request";
import getRequest from "@src/util/http/get-request";
import isObject from "@src/util/common/is-object";
import EventHub from "@src/event-hub";

const connectStyleForBootstrapDialog = async () => {
    // eslint-disable-next-line no-underscore-dangle
    if (!globalThis.__shipper_page) {
        return true;
    }

    const elementModalId = "js-verify-bootstrapdialog-styles";
    let elementModal = document.getElementById(elementModalId);

    if (!elementModal) {
        const divCreated = document.createElement("div");
        divCreated.className = "modal";
        divCreated.id = elementModalId;
        divCreated.style.display = "none";
        document.body.appendChild(divCreated);
        elementModal = document.getElementById(elementModalId);
    }

    if (getComputedStyle(elementModal).position !== "fixed") {
        await import("@scss/epl/_old-bootstrap-modal.scss");
    }

    return true;
};

/**
 * Boots the plugin only one time.
 */
const boot = async () => {
    // Local variable was created because dinamyc import don't work with contants
    const siteLang = LANG;
    const [{ default: BootstrapDialog }] = await Promise.all([
        // @ts-ignore
        import(/* webpackChunkName: "bootstrap-dialog-chunk" */ "@src/plugins/bootstrap-dialog/dialog"),
        // eslint-disable-next-line no-underscore-dangle
        import(/* webpackChunkName: "bootstrap-dialog-i18n" */ `@plug/bootstrap-dialog-1-35-4/lang/${siteLang}.js`),
    ]);

    await connectStyleForBootstrapDialog();
    return BootstrapDialog;
};
/**
 * Opens a popup using bootstrap dialog.
 *
 * @param {string} url
 * @param {string} [title=null]
 * @param {any} [data={}]
 *
 * @returns {Promise<{dialog: BootstrapDialog, response: any }>}
 */
const openPopup = async function (url, title = null, data = {}) {
    const BootstrapDialog = await boot();

    const formatResponse = function (response) {
        try {
            return JSON.parse(response);
        } catch (e) {
            return response;
        }
    };
    const filterResponse = function (response) {
        if (response && isObject(response)) {
            if (response.mess_type && response.mess_type !== "success") {
                throw new RequestError(response.message || null, response.mess_type, true);
            }
        }

        return response;
    };
    const showPopup = function (dialog, resolve, response) {
        dialog.getMessage().append(response.html ? response.html : response);
        enableFormValidation(dialog.getMessage().find("form.validateModal"));
        hideLoader(dialog.getModalContent());
        resolve({ dialog, response, shown: true });
    };
    const handleError = function (dialog, resolve, error) {
        dialog.close();
        handleRequestError(error);
        resolve({ dialog, error, shown: false });
    };

    return new Promise(resolve => {
        BootstrapDialog.show({
            title,
            tabindex: 0,
            message: $("<div></div>"),
            closeIcon: '<i class="ep-icon ep-icon_remove-stroke"></i>',
            cssClass: "dialog-type-popup",
            onshow(dialog) {
                dialog.getModalDialog().addClass("modal-dialog-centered");
                dialog.getModalHeader().find(".close, .bootstrap-dialog-title").addClass("txt-white");
                dialog.getModalBody().addClass("mnh-100");
                showLoader(dialog.getModalContent(), "Loading...");

                return postRequest(url, data, "text")
                    .then(formatResponse)
                    .then(filterResponse)
                    .then(showPopup.bind(null, dialog, resolve))
                    .catch(handleError.bind(null, dialog, resolve));
            },
            closable: true,
            closeByBackdrop: false,
            closeByKeyboard: false,
            draggable: false,
            animate: false,
            nl2br: false,
        });
    });
};

/**
 * Shows confirmation dialog.
 *
 * @param {string} message
 * @param {string} [title]
 *
 * @returns {Promise<{ confirm: boolean, dialog: BootstrapDialog }>}
 */
const askConfirmation = async function (message, title) {
    const BootstrapDialog = await boot();

    return new Promise(resolve => {
        BootstrapDialog.show({
            cssClass: "bootstrap-dialog--results bootstrap-dialog--results-info",
            title: title || "Information",
            size: BootstrapDialog.SIZE_NORMAL,
            message: $("<div>"),
            onshow(dialog) {
                const $dialogHeader = dialog.getModalHeader().find(".bootstrap-dialog-header");
                $dialogHeader.prepend('<div class="bootstrap-dialog-icon-title"><i class="ep-icon ep-icon_info-character fs-25"></i></div>');
                $dialogHeader.append(`<h6 class="bootstrap-dialog-sub-title">${message}</h6>`);

                const $modalDialog = dialog.getModalDialog();
                $modalDialog.addClass("modal-dialog-scrollable modal-dialog-centered");
                $modalDialog.addClass("bootstrap-dialog--footer-padding");
                dialog.getModalFooter().show();
            },
            buttons: [
                {
                    label: translate({ plug: "BootstrapDialog", text: "ok" }),
                    cssClass: "btn-success",
                    action(dialog) {
                        resolve({ confirm: true, dialog });
                    },
                },
                {
                    label: translate({ plug: "BootstrapDialog", text: "cancel" }),
                    cssClass: "btn-light",
                    action(dialog) {
                        resolve({ confirm: false, dialog });
                    },
                },
            ],
            closable: true,
            closeByBackdrop: true,
            closeByKeyboard: false,
            draggable: false,
            animate: false,
            nl2br: false,
        });
    });
};

/**
 * Closes closest to the element Bootstrap Dialog
 *
 * @param {JQuery} [element]
 */
const closeBootstrapDialog = async function (element) {
    const BootstrapDialog = await boot();
    const id = element !== null ? element.closest(".modal.bootstrap-dialog").prop("id") || null : null;
    const dialog = id !== null ? BootstrapDialog.getDialog(id) || null : null;

    if (dialog !== null) {
        dialog.close();
    }
};

/**
 * Opens popup where user can indicate his location.
 *
 * @param {any} initParams
 * @param {any} address
 * @param {Function} [callbackAfterShowModal]
 */
const openLocationPopup = async function (initParams, address, callbackAfterShowModal) {
    const BootstrapDialog = await boot();

    return new Promise((resolve, reject) => {
        const titleModal = initParams.title || "Add location";
        const addressShow = initParams.address || false;
        const postalCodeShow = initParams.postalCode || false;

        const onSubmit = function (dialog, form, data) {
            const location = {};

            (data || []).forEach(entry => {
                location[entry.name] = { value: entry.value, name: "" };
                const $input = form.find(`[name='${entry.name}']`);

                if ($input.length && $input.prop("type") === "select-one") {
                    if (entry.name !== "city") {
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

        // eslint-disable-next-line camelcase
        const params = { postal_code_show: postalCodeShow, address_show: addressShow };
        const url = `${SITE_URL}location/popup_forms/get_location`;

        postRequest(url, { ...address, ...params })
            .then(response => {
                if (response.mess_type && response.mess_type !== "success") {
                    systemMessages(response.message, response.mess_type);
                    reject();

                    return;
                }

                BootstrapDialog.show({
                    tabindex: 0,
                    title: titleModal,
                    cssClass: "info-bootstrap-dialog",
                    closeIcon: '<i class="ep-icon ep-icon_remove-stroke"></i>',
                    message: $("<div></div>"),
                    onshow(dialog) {
                        dialog.getModalDialog().css("max-width", "425px");
                        dialog.getMessage().append(response.html);
                        dialog
                            .getMessage()
                            .find("form.validateModal")
                            .data("callback", () => {
                                onSubmit(
                                    dialog,
                                    dialog.getMessage().find("form.validateModal"),
                                    dialog.getMessage().find("form.validateModal").serializeArray()
                                );
                            });

                        enableFormValidation(dialog.getMessage().find("form.validateModal"));

                        const $formLocation = dialog.getMessage().find(".js-global-location-form");
                        const $selectCity = $formLocation.find("#js-location-port-city");
                        const $selectCountry = $formLocation.find("#js-location-country-states");
                        let selectState = $selectCountry.val() || "";

                        function formatCity(repo) {
                            if (repo.loading) return repo.text;

                            const markup = repo.name;

                            return markup;
                        }

                        function formatCitySelection(repo) {
                            return repo.name || repo.text;
                        }

                        $selectCity.select2({
                            ajax: {
                                type: "POST",
                                url: `${SUBDOMAIN_URL}location/ajax_get_cities`,
                                dataType: "json",
                                delay: 250,
                                data(selectParams) {
                                    return {
                                        search: selectParams.term, // search term
                                        page: selectParams.page,
                                        state: selectState,
                                    };
                                },
                                processResults(data, selectParams) {
                                    const page = selectParams.page || 1;

                                    return {
                                        results: data.items,
                                        pagination: {
                                            more: page * data.per_p < data.total_count,
                                        },
                                    };
                                },
                            },
                            dropdownParent: $formLocation.closest(".modal"),
                            language: LANG,
                            theme: "default ep-select2-h30",
                            width: "100%",
                            placeholder: translate({ plug: "general_i18n", text: "form_placeholder_select2_state_first" }),
                            minimumInputLength: 2,
                            escapeMarkup(markup) {
                                return markup;
                            },
                            templateResult: formatCity,
                            templateSelection: formatCitySelection,
                        });

                        if ($selectCity.find("option").length < 2) {
                            $selectCity.prop("disabled", true);
                        }

                        $selectCity
                            .data("select2")
                            .$container.attr("id", "select-сity--formfield--location-container")
                            .addClass("validate[required]")
                            .setValHookType("selectCityLocation");

                        $.valHooks.selectCityLocation = {
                            get() {
                                return $selectCity.val() || [];
                            },
                        };

                        $formLocation.on("change", "#js-location-country", function onChangeCountry() {
                            showLoader($formLocation);

                            $.ajax({
                                type: "POST",
                                dataType: "JSON",
                                url: `${SUBDOMAIN_URL}location/ajax_get_states`,
                                data: { country: $(this).val() },
                                success(resp) {
                                    hideLoader($formLocation);
                                    $selectCountry.html(resp.states);
                                },
                            });

                            selectState = 0;
                            $selectCity.empty().trigger("change").prop("disabled", true);
                        });

                        $formLocation.on("change", "#js-location-country-states", function onChangeStates() {
                            selectState = this.value;
                            $selectCity.empty().trigger("change").prop("disabled", false);
                            let text = "form_placeholder_select2_city";
                            if (selectState === "" || selectState === 0) {
                                text = "form_placeholder_select2_state_first";
                                $selectCity.prop("disabled", true);
                            }
                            $selectCity
                                .siblings(".select2")
                                .find(".select2-selection__placeholder")
                                .text(translate({ plug: "general_i18n", text }));
                        });

                        if (typeof callbackAfterShowModal === "function") {
                            callbackAfterShowModal();
                        }
                    },
                    onhide() {
                        reject();
                    },
                    buttons: [
                        {
                            label: "Add location",
                            cssClass: "btn-primary mnw-130",
                            action(dialog) {
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
                    animate: false,
                    nl2br: false,
                });
            })
            .catch(error => {
                handleRequestError(error);
                reject();
            });
    });
};

/**
 *
 * @param {any} [params]
 */
const openModalPopup = async function (params) {
    const BootstrapDialog = await boot();
    const { btn, title, content, isAjax = 0, buttons, validate = false, classes, closeCallBack } = params;

    BootstrapDialog.show({
        title,
        message: $("<div></div>"),
        cssClass: `info-bootstrap-dialog ${classes}`,
        closeIcon: '<i class="ep-icon ep-icon_remove-stroke"></i>',
        onhide() {
            if (typeof closeCallBack === "function" && closeCallBack) {
                closeCallBack();
            }
        },
        onshow(dialog) {
            const $modalDialog = dialog.getModalDialog();
            $modalDialog.addClass("modal-dialog-centered");

            if (btn) {
                $modalDialog.addClass($(btn).data("classes"));
            }

            if (isAjax) {
                showLoader($modalDialog.find(".modal-content"), "Loading...");

                $.get(content).done(htmlResp => {
                    setTimeout(() => {
                        dialog.getModalFooter().append(buttons).css({ display: "flex" }).find(".bootstrap-dialog-footer").remove();
                        dialog.getMessage().append(htmlResp);

                        if (dialog.getMessage().find(".modal-system-messages.errors").length) {
                            dialog.getModalFooter().html("");
                        }

                        hideLoader($modalDialog.find(".modal-content"));
                    }, 200);
                });
            } else {
                dialog.getMessage().append(content);
                if (~~validate) {
                    enableFormValidation(dialog.getMessage().find(".validateModal"));
                }
            }
        },
        // buttons:buttons,
        type: "type-light",
        size: "size-wide",
        closable: true,
        closeByBackdrop: false,
        closeByKeyboard: false,
        draggable: false,
        animate: false,
        nl2br: false,
    });
};

const openInfoDialog100pr = async function (params) {
    const BootstrapDialog = await boot();

    const title = params.title || undefined;
    const content = params.content || "";
    const isAjax = params.is_ajax || 0;
    const buttons = params.buttons || [];

    BootstrapDialog.show({
        cssClass: "info-bootstrap-dialog bootstrap-dialog--h-100pr",
        title,
        message: $('<div class="h-100pr"></div>'),
        onshow(dialog) {
            const $modalDialog = dialog.getModalDialog();
            $modalDialog.addClass("modal-dialog-centered");

            if (isAjax) {
                showLoader($modalDialog.find(".modal-content"), "Loading...");

                $.get(content).done(htmlResp => {
                    setTimeout(() => {
                        dialog.getMessage().append(htmlResp);
                        hideLoader($modalDialog.find(".modal-content"));
                    }, 200);
                });
            } else {
                dialog.getMessage().append(content);
            }
        },
        buttons,
        type: "type-light",
        size: "size-wide",
        closable: true,
        closeByBackdrop: true,
        closeByKeyboard: false,
        draggable: false,
        animate: false,
        nl2br: false,
    });
};

const openInfoDialog100 = async function (params) {
    const BootstrapDialog = await boot();

    const title = params.title || undefined;
    const bodyContent = params.bodyContent || "";
    const footerContent = params.footerContent || 0;
    const onAfterShow = params.onAfterShow || {};

    return BootstrapDialog.show({
        cssClass: "info-bootstrap-dialog bootstrap-dialog--h-100pr",
        title,
        onshow(dialog) {
            const $modalDialog = dialog.getModalDialog();
            $modalDialog.addClass("modal-dialog-centered");
            dialog.getModalBody().html(bodyContent);

            if (footerContent !== "") {
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
        animate: false,
        nl2br: false,
    });
};

const openResultModal = async function (params) {
    const BootstrapDialog = await boot();
    const {
        buttons = [],
        content = "",
        contentFooter = "",
        classContent = "",
        closable = false,
        closeCallBack,
        closeByBg = false,
        classes = "",
        delimeterClass = "bootstrap-dialog--content-delimeter",
        icon: iconModal,
        isAjax = false,
        openCallBack,
        keepModal,
        subTitle,
        title,
        titleImage = false,
        titleUppercase = false,
        validate = false,
        type,
    } = params;

    let typeModal = type || "info";
    let iconModalType = "ok-stroke2";
    let titleType = "";

    const modalData = {
        info: {
            icon: "info-character fs-25",
            title: "Info",
        },
        success: {
            icon: "ok-stroke2",
            title: "Success!",
        },
        warning: {
            icon: "warning-character",
            title: "Warning!",
        },
        error: {
            type: "danger",
            icon: "remove-stroke2 fs-20",
            title: "Error!",
        },
        question: {
            type: "info",
            icon: "question-character",
            title: "Question",
        },
        image: {
            type: "image",
            icon: "image",
        },
        certified: {
            title: "Warning!",
            type: "warning",
            icon: "iconImage",
        },
    };

    iconModalType = modalData[typeModal].icon;
    titleType = modalData[typeModal].title;
    typeModal = modalData[typeModal].type ? modalData[typeModal].type : typeModal;

    const eventName = `custom-bs:dialog-is-shown-${Date.now()}`;
    const typeModalClass = ` bootstrap-dialog--results-${typeModal}`;

    return BootstrapDialog.show({
        cssClass: `bootstrap-dialog--results${typeModalClass} ${classes}`,
        title: title || titleType,
        size: BootstrapDialog.SIZE_NORMAL,
        message: $("<div>"),
        closeIcon: '<i class="ep-icon ep-icon_remove-stroke"></i>',
        onhide() {
            if (typeof closeCallBack === "function" && closeCallBack) {
                closeCallBack();
            }
        },
        onshown(dialog) {
            dialog.getMessage().trigger(eventName);
        },
        async onshow(dialog) {
            const dialogHeader = dialog.getModalHeader().find(".bootstrap-dialog-header");
            const modalDialog = dialog.getModalDialog();

            if (titleUppercase === true) {
                dialogHeader.find(".bootstrap-dialog-title").addClass("tt-uppercase");
            }

            if (iconModalType === "iconImage") {
                dialogHeader.prepend(
                    `<div class="bootstrap-dialog-icon-title"><div class="bootstrap-dialog-icon-image"><img class="image" src="${titleImage}"></div></div>`
                );
            } else if (iconModalType !== "image") {
                dialogHeader.prepend(
                    `<div class="bootstrap-dialog-icon-title"><i class="ep-icon ep-icon_${iconModal !== undefined ? iconModal : iconModalType}"></i></div>`
                );
            } else {
                // eslint-disable-next-line no-lonely-if
                if (titleImage !== undefined) {
                    dialogHeader.closest(".modal-header").prepend(`<div class="bootstrap-dialog-image-title"><img class="image" src="${titleImage}"></div>`);
                }
            }

            const addValidationIfPossible = function () {
                if (!validate) {
                    return;
                }

                enableFormValidation(dialog.getMessage().find(".validateModal"));
            };

            modalDialog.addClass("modal-dialog-scrollable modal-dialog-centered");

            if (!keepModal) {
                $(".modal").modal("hide");
            }

            if (subTitle) {
                dialogHeader.append(`<h6 class="bootstrap-dialog-sub-title">${subTitle}</h6>`);
            }
            if (isAjax || content.length) {
                showLoader(modalDialog.find(".modal-content"), "Loading...");
            }
            if (isAjax) {
                const url = new URL(content);
                url.searchParams.append("webpackData", "1");
                try {
                    const { mess_type: messType, footer, content: ajaxContent } = await getRequest(url.href);
                    dialog.getMessage().one(
                        eventName,
                        (() => {
                            hideLoader(modalDialog.find(".modal-content"));

                            if (messType === "success") {
                                modalDialog.addClass(delimeterClass);
                                dialog.getMessage().append(ajaxContent);
                                if (footer) {
                                    dialog.getModalFooter().html(footer);
                                }

                                addValidationIfPossible();
                            }
                        })()
                    );
                } catch (error) {
                    handleRequestError(error);
                }
            } else {
                dialog.getMessage().one(eventName, () => {
                    if (content.length) {
                        hideLoader(modalDialog.find(".modal-content"));
                        modalDialog.addClass(delimeterClass);
                        dialog.getMessage().append(`<div class="${classContent}">${content}</div>`);
                    }
                    if (contentFooter.length) dialog.getModalFooter().append(contentFooter);
                    addValidationIfPossible();
                });
            }

            if (Object.keys(buttons).length > 0) {
                modalDialog.addClass("bootstrap-dialog--footer-padding");
            }

            dialog.getModalFooter().show();

            if (typeof openCallBack === "function" && openCallBack) {
                openCallBack();
            }
        },
        buttons,
        closable,
        closeByBackdrop: closeByBg,
        closeByKeyboard: false,
        draggable: false,
        animate: false,
        nl2br: false,
    });
};

const openEmailSuccessDialog = async function (title, content, buttons) {
    const BootstrapDialog = await boot();
    const dialogButtons = buttons || [];

    BootstrapDialog.show({
        closeIcon: '<i class="ep-icon ep-icon_remove-stroke"></i>',
        cssClass: "info-bootstrap-dialog friend-invite__modal",
        title,
        message: $('<div class="ep-tinymce-text"></div>'),
        onshow(dialog) {
            const modalDialog = dialog.getModalDialog();
            modalDialog.addClass("modal-dialog-centered");
            dialog.getMessage().append(content);
        },
        buttons: dialogButtons,
        type: "type-light",
        size: "size-wide",
        closable: true,
        closeByKeyboard: false,
        draggable: false,
        animate: false,
        nl2br: false,
    });
};

const openHeaderImageModal = async function (params) {
    await boot();

    const title = params.title || undefined;
    const titleUppercase = params.titleUppercase || false;
    const subTitle = params.subTitle || undefined;
    const content = params.content || "";
    const isAjax = Boolean(~~params.isAjax) ?? true;
    const validate = Boolean(~~params.validate) || false;
    const classes = params.classes || "";
    const closable = Boolean(~~params.closable) || true;
    const type = params.type || "image";
    const buttons = params.buttons || [];
    const titleImage = params.titleImage || "";
    const closeCallBack = params.closeCallBack || undefined;
    const openCallBack = params.openCallBack || undefined;
    const classContent = params.classContent ?? undefined;
    const contentFooter = params.contentFooter || "";

    return openResultModal({
        titleImage,
        titleUppercase,
        title,
        subTitle,
        content,
        isAjax,
        validate,
        classes,
        closable,
        type,
        buttons,
        closeCallBack,
        classContent,
        openCallBack,
        contentFooter,
    });
};

const openVideoModal = $this => {
    let link = `https://www.youtube.com/embed/${$this.data("href")}`;

    if ($this.data("autoplay")) {
        link = `https://www.youtube.com/embed/${$this.data("href")}?autoplay=1`;
    }

    const content = `
        <iframe class="js-popup-video-iframe"
                width="100%"
                height="100%"
                src="${link}"
                frameborder="0"
                allow="autoplay; encrypted-media"
                allowfullscreen>
        </iframe>
    `;

    const currentScrollPosition = $(globalThis).scrollTop();
    const scrollToEl = () => {
        const scrollPosition = $(globalThis).scrollTop();
        if (currentScrollPosition !== scrollPosition) {
            $(globalThis).scrollTop(currentScrollPosition);
        }
    };

    openModalPopup({
        content,
        title: $this.data("title"),
        classes: "bootstrap-dialog--video",
        closeCallBack: scrollToEl,
    });
};

/**
 * Handles the open of the popup dialog.
 */
const onPopupOpen = function () {
    const data = $(this).data() || {};
    const url = data.url || data.href || null;
    if (url === null) {
        return;
    }

    openPopup(url, data.title || null, data.params || {});
};

const closeAllDialogs = async () => {
    const BootstrapDialog = await boot();

    BootstrapDialog.closeAll();
};

/**
 *
 * @param {JQuery.Event} e
 * @param {JQuery} openButton
 */
const openConfirmationDialog = async function (e, openButton) {
    await boot();

    const title = openButton.data("title") || undefined;
    const subTitle = openButton.data("message") || undefined;
    const typeModal = openButton.data("type") || "info";
    const icon = openButton.data("icon") || undefined;
    const keepModal = openButton.data("keepModal");

    return openResultModal({
        title,
        subTitle,
        icon,
        type: typeModal,
        keepModal,
        buttons: [
            {
                label: translate({ plug: "BootstrapDialog", text: "ok" }),
                cssClass: "btn-success",
                action(dialogRef) {
                    this.disable();

                    const action = openButton.data("jsAction") || null;

                    if (action) {
                        EventHub.trigger(action, [openButton, this, dialogRef, e]);
                        dialogRef.close();

                        return;
                    }

                    // Backward compatibilty
                    const callBack = openButton.data("callback") || null;
                    if (callBack) {
                        callFunction(callBack, openButton);
                        dialogRef.close();

                        return;
                    }

                    dialogRef.close();
                },
            },
            {
                label: translate({ plug: "BootstrapDialog", text: "cancel" }),
                cssClass: "btn-light",
                action(dialogRef) {
                    dialogRef.close();
                },
            },
        ],
    });
};

const openAttachFilesDialog = async (roomId, userId) => {
    const BootstrapDialog = await boot();

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
        onshow(dialog) {
            const modalDialog = dialog.getModalDialog();
            modalDialog.addClass("modal-dialog-centered");
            dialog.getModalBody().addClass("mnh-100");
            showLoader(dialog.getModalBody(), "Loading...");

            // eslint-disable-next-line no-underscore-dangle
            $.get(`${globalThis.__group_site_url}chats/popupForms/attachFiles`).done(resp => {
                dialog.getModalBody().html(resp);
                dialog
                    .getModalFooter()
                    .append(
                        `<button
                                id="js-chat-app-attach-files-modal-dialog"
                                class="btn btn-primary mnw-130 call-action"
                                data-js-action="chat:room-attach-files"
                                form="js-modal-message-attach-inner"
                                type="button"
                                data-user="${userId}"
                                data-room="${roomId}"
                                disabled
                        >Send file(s)</button>`
                    )
                    .css({ display: "flex" });
            });
        },
    });
};

const openDialog = async btn => {
    const { type = "info", message, content, title, keepModal = false } = btn.data();
    await boot();

    return openResultModal({
        title,
        subTitle: message || $(content).html(),
        type,
        keepModal,
        closable: true,
        closeByBg: true,
        buttons: [
            {
                label: translate({ plug: "BootstrapDialog", text: "close" }),
                cssClass: "btn btn-light",
                action(dialog) {
                    dialog.close();
                },
            },
        ],
    });
};

const openBootstrapDialog = async options => {
    const BootstrapDialog = await boot();
    BootstrapDialog.show({
        type: "type-light",
        size: "size-wide",
        closeIcon: '<i class="ep-icon ep-icon_remove-stroke"></i>',
        closable: true,
        cssClass: "info-bootstrap-dialog inputs-40",
        closeByBackdrop: false,
        closeByKeyboard: false,
        draggable: false,
        animate: false,
        nl2br: false,
        ...options,
    });
};

export default boot;
export {
    boot,
    openPopup,
    askConfirmation,
    openConfirmationDialog,
    closeBootstrapDialog,
    openLocationPopup,
    openModalPopup,
    openResultModal,
    openEmailSuccessDialog,
    openHeaderImageModal,
    closeAllDialogs,
    onPopupOpen,
    openAttachFilesDialog,
    openVideoModal,
    openInfoDialog100pr,
    openInfoDialog100,
    openDialog,
    openBootstrapDialog,
};
