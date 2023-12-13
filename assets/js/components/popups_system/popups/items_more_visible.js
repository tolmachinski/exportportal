import loadBootstrapDialog, { openHeaderImageModal } from "@src/plugins/bootstrap-dialog/index";
import postRequest from "@src/util/http/post-request";

// @ts-ignore
import imgTitle from "@images/upgrade_page/modals/upgrade_now.jpg";
import { SITE_URL, SUBDOMAIN_URL } from "@src/common/constants";

const openUpgradeAccountNowPopup = async () => {
    await postRequest(`${SUBDOMAIN_URL}popups/ajax_operations/items_more_visible`)
        .then(async response => {
            if (response.mess_type === "success") {
                await loadBootstrapDialog();
                await openHeaderImageModal({
                    title: response.title,
                    subTitle: response.subTitle,
                    content: response.content,
                    titleImage: imgTitle,
                    titleUppercase: true,
                    isAjax: false,
                    buttons: [
                        {
                            label: "Get started",
                            cssClass: "btn btn-primary mnw-185",
                            action: () => {
                                globalThis.location.href = `${SITE_URL}upgrade`;
                            },
                        },
                    ],
                });
            }
        })
        .catch(() => {});
};

export default openUpgradeAccountNowPopup;
