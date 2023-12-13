import postRequest from "@src/util/http/post-request";
import { SITE_URL } from "@src/common/constants";
import handleRequestError from "@src/util/http/handle-request-error";
import loadBootstrapDialog, { openResultModal } from "@src/plugins/bootstrap-dialog";
import { translate } from "@src/i18n";

const removeFromDroplist = async btn => {
    try {
        const { mess_type: messType, message, messTitle, canAddToDroplist } = await postRequest(`${SITE_URL}items/ajax_remove_from_droplist/${btn.data("itemId")}`);

        if (messType === "success") {
            await loadBootstrapDialog();
            await openResultModal({
                title: messTitle,
                subTitle: message,
                classes: " inputs-40",
                closable: true,
                isAjax: false,
                type: "success",
                buttons: [
                    {
                        label: translate({ plug: "BootstrapDialog", text: "go_to_droplist" }),
                        cssClass: "btn btn-primary",
                        action: () => {
                            globalThis.location.href = `${SITE_URL}items/droplist`;
                        },
                    },
                    {
                        label: translate({ plug: "BootstrapDialog", text: "close" }),
                        cssClass: "btn btn-light",
                        action: dialog => {
                            dialog.close();
                        },
                    },
                ],
            });
            if (!canAddToDroplist) {
                btn.addClass("product__droplist--disable");
            }
            btn.removeClass("js-confirm-dialog").addClass("js-fancybox-validate-modal fancybox.ajax js-add-to-droplist");
            btn.data({
                "fancybox-href": `${SITE_URL}items/ajax_add_to_droplist/${btn.data("itemId")}`,
                mw: "470",
                "class-modificator": "droplist",
                title: translate({ plug: "fancybox3", text: "items_droplist_popup_header" }),
            });
            btn.find("span").html(translate({ plug: "fancybox3", text: "items_add_to_droplist_btn" }));
        }
    } catch (error) {
        handleRequestError(error);
    }

    return true;
};

export default removeFromDroplist;
