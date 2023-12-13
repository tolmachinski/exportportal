import $ from "jquery";
import FileSaver from "file-saver";

import Platform from "@src/chat_app/platform";
import { open } from "@src/plugins/fancybox/v2/index";
import { calculateModalBoxSizes } from "@src/plugins/fancybox/v2/util";
import loadingValidationEngine from "@src/plugins/validation-engine/lazy";
import loadBootstrapDialog, { openResultModal, openAttachFilesDialog } from "@src/plugins/bootstrap-dialog/index";
import { GROUP_SITE_URL, SHIPPER_PAGE } from "@src/common/constants";
import initSystemMessages, { systemMessages } from "@src/util/system-messages/index";
import { emitterLogout } from "@src/chat_app/listeners/emiters";
import { dispatchEvent } from "@src/util/events";
import { translate } from "@src/i18n";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";
import getRequest from "@src/util/http/get-request";
import openAttachFilesDialogEpl from "@src/epl/common/popups/types/modals";

// ON LOAD MAIN CHAT IFRAME
const listenerMainLoad = (listener, emitter, passPhrase = null, openRoomById = null) => {
    listener.off("load");
    listener.on("load", () => {
        $(".js-open-chat, .js-popover-messages, #js-popover-messages").removeClass("disabled");
        emitter.emit("init", Platform.autorizationData);
        if (passPhrase) {
            emitter.emit("setSecurePassphrase:keygen", { passPhrase });
        }
        if (openRoomById) {
            emitter.emit("openRoomById", openRoomById);
        }
        // Logout
        emitterLogout(listener, emitter);
    });
};

// Слушаем событие открытие модалки загрузки файлов
export const listenerOpenAttachFilesPopup = listener => {
    listener.off("openAttachFilesPopup");
    listener.on("openAttachFilesPopup", ({ context: { data } }) => {
        if (globalThis.ENCORE_MODE) {
            if (SHIPPER_PAGE) {
                openAttachFilesDialogEpl(data.roomId, data.userId);
            } else {
                openAttachFilesDialog(data.roomId, data.userId);
            }
        } else {
            dispatchEvent("chat-client:open-attach-files-dialog", globalThis, { detail: data });
        }
    });
};

// // Слушаем событие открытие модалки добавление Sample Order
export const listenerOpenModalAddSampleOrder = listener => {
    listener.off("openModalAddSampleOrder");
    listener.on("openModalAddSampleOrder", ({ context: { data } }) => {
        const adjustments = calculateModalBoxSizes();

        open(
            {
                title: "Create Sample Order",
                type: "ajax",
                href: `${GROUP_SITE_URL}sample_orders/popup_forms/create_order?room=${data.roomId}&user=${data.userId}`,
            },
            {
                padding: adjustments.gutter,
                beforeLoad: () => {
                    $(document).on("click focusout", ".validateModal input", loadingValidationEngine);
                },
            }
        );
    });
};

// // Слушаем событие открытие модалки подключение Sample Order
export const listenerOpenModalAssignSampleOrder = listener => {
    listener.off("openModalAssignSampleOrder");
    listener.on("openModalAssignSampleOrder", ({ context: { data } }) => {
        const adjustments = calculateModalBoxSizes();

        open(
            {
                title: "Assign Sample Order",
                type: "ajax",
                href: `${GROUP_SITE_URL}sample_orders/popup_forms/assign_order?room=${data.roomId}&user=${data.userId}`,
            },
            {
                maxWidth: 400,
                padding: adjustments.gutter,
                beforeLoad: () => {
                    $(document).on("click focusout", ".validateModal input", loadingValidationEngine);
                },
            }
        );
    });
};

// Слушаем событие update комнаты в БД
export const listenerUpdateNewRoom = listener => {
    listener.off("updateNewRoom");
    listener.on("updateNewRoom", ({ context: { data: { id, module, item, user } } }) =>
        // eslint-disable-next-line no-underscore-dangle
        postRequest(`${globalThis.__current_sub_domain_url}chats/ajax_chats_operations/update_room`, { id, user, item, module }, "json").catch(
            handleRequestError
        )
    );
};

// SYSTEM MESSAGES
export const listenerSystemMessages = listener => {
    listener.off("systemMessages");
    initSystemMessages();
    listener.on("systemMessages", ({ context: { data } }) => {
        const {
            message: { text, replaces },
        } = data;
        systemMessages(translate({ plug: "general_i18n", text, replaces }), data.type);
    });
};

// Update site chat counter
export const listenerUpdateChatCounter = listener => {
    listener.off("updateChatCounter");
    listener.on(
        "updateChatCounter",
        ({
            context: {
                data: { counter },
            },
        }) => {
            const elCircle = $(".js-icon-circle-messages");
            const elCounter = $("#js-popover-messages-count-new");
            if (counter === 0) {
                elCircle.addClass("display-n_i");
            } else {
                elCircle.removeClass("display-n_i");
            }

            elCounter.text(counter);
        }
    );
};

// Слушаем событие скачивания файла
export const listenerDownloadFile = (listener, emitter) => {
    listener.off("downloadFile");
    listener.on("downloadFile", ({ context: { data } }) => {
        getRequest(`${GROUP_SITE_URL}matrix_chat/ajax_operation/access-attachment/file/${data.id}`)
            .then(({ token: { url: link, name } }) => FileSaver.saveAs(link, name))
            .catch(handleRequestError)
            .finally(() => emitter.emit(`fileDownloaded-${data.id}`));
    });
};

// Слушаем открытие конфирма
export const listenerOpenConfirm = (listener, emitter) => {
    listener.off("openConfirm");
    listener.on(
        "openConfirm",
        async ({
            context: {
                data: { message, emitName },
            },
        }) => {
            await loadBootstrapDialog();
            openResultModal({
                subTitle: message,
                type: "info",
                buttons: [
                    {
                        label: translate({ plug: "BootstrapDialog", text: "ok" }),
                        cssClass: "btn-success",
                        action(dialogRef) {
                            emitter.emit(emitName);
                            dialogRef.close();
                        },
                    },
                    {
                        label: translate({ plug: "BootstrapDialog", text: "close" }),
                        cssClass: "btn btn-light",
                        action(dialog) {
                            dialog.close();
                        },
                    },
                ],
            });
        }
    );
};

// Слушаем получение секретной passphrase
export const listenerGetSecurePassphrase = (listener, emitter) => {
    listener.off("getSecurePassphrase");
    listener.on("getSecurePassphrase", () => {
        getRequest(`${GROUP_SITE_URL}matrix_chat/ajax_operation/get-security-passphrase`)
            .then(({ mess_type: messType, passPhrase }) => {
                if (messType === "success") {
                    emitter.emit("setSecurePassphrase", { passPhrase });
                }
            })
            .catch(handleRequestError);
    });
};

/**
 * Attaches the listeners for key update.
 */
export const listenerSecretKeyCreate = (listener, emitter) => {
    listener.off("SecretStorage.created");
    listener.on("SecretStorage.created", async () => {
        try {
            await postRequest(`${GROUP_SITE_URL}matrix_chat/ajax_operation/update-keys-statuses`);
        } catch (error) {
            emitter.emit("SecretKey.updateFailure", error);
        }
    });
};

export const listenerLogoutCloseIframes = listener => {
    listener.off("logout:close-chat-room");
    listener.on("logout:close-chat-room", () => {
        document.querySelectorAll("#chat-app-room, #chat-app-new-chat, #chat-app-contact, #chat-app-info").forEach(e => e.remove());
    });
};

export const listenerWindowLocationChange = listener => {
    listener.off("window:location-change");
    listener.on("window:location-change", ({ context: { data } }) => {
        globalThis.location.href = data.href;
    });
};

export const listenerIdleWorkerResetTimeout = listener => {
    listener.off("idle-worker:reset-timeout");
    listener.on("idle-worker:reset-timeout", () => globalThis.dispatchEvent(new CustomEvent("chat-app:idle-worker-reset-timeout")));
};

export default listenerMainLoad;
