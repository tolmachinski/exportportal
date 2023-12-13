import $ from "jquery";

import { hideLoader, showLoader } from "@src/util/common/loader";
import { translate } from "@src/i18n";
import { LOGGED_IN, SUBDOMAIN_URL } from "@src/common/constants";

import postRequest from "@src/util/http/post-request";
import handleRequestError from "@src/util/http/handle-request-error";

const loadNewComments = async resourceId => {
    const commentsWrapper = $("#js-comments-wrapper");
    const button = $("#js-comments-more-block");

    showLoader(commentsWrapper, "Loading...");
    button.prop("disabled", false);

    try {
        const { commentsList } = await postRequest(`${SUBDOMAIN_URL}comments/ajax_operations/recent`, {
            maxCommentId: parseInt(commentsWrapper.find(".js-common-comments-row:first-child").data("comment-row"), 10),
            resourceId,
        });

        if (commentsList) {
            $("#js-comments-list").prepend(commentsList);
            commentsWrapper.find(".js-common-comments-empty").remove();
        }
    } catch (error) {
        handleRequestError(error);
    } finally {
        hideLoader(commentsWrapper);
        button.prop("disabled", false);
    }

    return true;
};

const saveComment = async form => {
    const type = form.data("type") ?? "add";
    const submitButton = form.find("button[type=submit]");

    showLoader(form);
    submitButton.prop("disabled", true);

    const submitForm = async () => {
        try {
            const { message, mess_type: messType, commentId, text, commentCounter, resourceId } = await postRequest(
                `${SUBDOMAIN_URL}comments/ajax_operations/${type}`,
                form.serializeArray()
            );

            if (type === "edit") {
                $(`.js-common-comments-row[data-comment-row="${commentId}"] .js-common-comments-message`).html(text);
            } else {
                $("#js-count-comments").html(commentCounter);

                if (LOGGED_IN) {
                    loadNewComments(resourceId);
                }
            }

            const { closeFancyboxPopup } = await import("@src/plugins/fancybox/v2/util");
            closeFancyboxPopup();

            const { openResultModal, default: loadBootstrapDialog } = await import("@src/plugins/bootstrap-dialog/index");
            await loadBootstrapDialog();

            openResultModal({
                content: message,
                type: messType,
                closable: true,
                buttons: [
                    {
                        label: translate({ plug: "BootstrapDialog", text: "close" }),
                        cssClass: "btn btn-light",
                        action(dialogRef) {
                            dialogRef.close();
                        },
                    },
                ],
            });
        } catch (error) {
            handleRequestError(error);
        } finally {
            hideLoader(form);
            submitButton.prop("disabled", false);
        }
    };

    if (!LOGGED_IN) {
        const { googleRecaptchaLoading, googleRecaptchaValidation } = await import("@src/common/recaptcha/index");

        await googleRecaptchaLoading();
        googleRecaptchaValidation(form)
            .then(() => {
                submitForm();
            })
            .catch(() => {
                submitButton.prop("disabled", false);
            });
    } else {
        submitForm();
    }
};

export default saveComment;
