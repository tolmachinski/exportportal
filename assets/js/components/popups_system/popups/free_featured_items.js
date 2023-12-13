import { SUBDOMAIN_URL } from "@src/common/constants";
import { openResultModal } from "@src/plugins/bootstrap-dialog/index";
import postRequest from "@src/util/http/post-request";

const openFreeFeaturedItemsPopup = async () => {
    await postRequest(`${SUBDOMAIN_URL}popups/ajax_operations/free_featured_items`)
        .then(async response => {
            if (response.mess_type === "success") {
                await import("@scss/components/popups/free_featured_items/index.scss");
                await openResultModal({
                    title: response.title,
                    subTitle: response.subTitle,
                    content: response.content,
                    contentFooter: response.footer,
                    classes: " inputs-40",
                    classContent: "",
                    isAjax: false,
                    closable: true,
                    buttons: [],
                });
            }
        })
        .catch(() => {});
};

export default openFreeFeaturedItemsPopup;
