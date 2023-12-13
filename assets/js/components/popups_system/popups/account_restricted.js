import $ from "jquery";
import loadBootstrapDialog, { openResultModal } from "@src/plugins/bootstrap-dialog/index";
import { translate } from "@src/i18n";
import { SUBDOMAIN_URL } from "@src/common/constants";
import postRequest from "@src/util/http/post-request";
import { hideLoader, showLoader } from "@src/util/common/loader";

const openPopupAccountRestricted = async () => {
    await loadBootstrapDialog();
    await postRequest(`${SUBDOMAIN_URL}popups/ajax_operations/account_restricted`)
        .then(async response => {
            if (response.mess_type === "success") {
                await openResultModal({
                    type: "error",
                    title: response.title,
                    content: response.content,
                    classes: " inputs-40",
                    classContent: "",
                    delimeterClass: "bootstrap-dialog--content-delimeter3",
                    isAjax: false,
                    closable: true,
                    buttons: [
                        {
                            label: translate({ plug: "BootstrapDialog", text: "contact_support" }),
                            cssClass: "btn btn-primary",
                            action(dialog) {
                                showLoader($(".bootstrap-dialog"), "Opening chat with support...");
                                $("#zsiq_float").trigger("click");

                                if (!$(".zls-sptwndw").hasClass("siqanim")) {
                                    const ifOppenedChat = setInterval(() => {
                                        if ($(".zls-sptwndw").hasClass("siqanim")) {
                                            dialog.close();
                                            clearInterval(ifOppenedChat);
                                        } else {
                                            $("#zsiq_float").trigger("click");
                                        }
                                    }, 1000);
                                } else {
                                    dialog.close();
                                }
                            },
                        },
                    ],
                });
            }
        })
        .catch(() => {});
};

export default openPopupAccountRestricted;
