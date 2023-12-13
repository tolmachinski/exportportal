import loadBootstrapDialog, { openResultModal } from "@src/plugins/bootstrap-dialog/index";
import { SUBDOMAIN_URL } from "@src/common/constants";
import postRequest from "@src/util/http/post-request";

const openShowPreactivationPopup = async () => {
    await postRequest(`${SUBDOMAIN_URL}what_next/popup_forms/activation`)
        .then(async response => {
            if (response.mess_type === "success") {
                await loadBootstrapDialog();
                await openResultModal({
                    title: response.title,
                    subTitle: response.subTitle,
                    content: response.content,
                    contentFooter: response.footer,
                    isAjax: false,
                    closable: true,
                    classContent: "",
                    buttons: [],
                });
            }
        })
        .catch(() => {});
};

export default openShowPreactivationPopup;
