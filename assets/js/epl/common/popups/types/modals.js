import $ from "jquery";

import { GROUP_SITE_URL } from "@src/common/constants";
import { openFancyboxPopup } from "@src/plugins/fancybox/v3/index";

const openAttachFilesDialogEpl = async (roomId, userId) => {
    openFancyboxPopup(
        {
            title: "Attach files",
            type: "ajax",
            src: `${GROUP_SITE_URL}chats/popupForms/attachFiles`,
        },
        {
            beforeShow: () => {},
            afterShow: () => {
                $("#js-modal-message-attach-btns").append(
                    `<div class="modal-flex__btns-right">
                        <button
                                id="js-chat-app-attach-files-modal-dialog"
                                class="btn btn-primary mnw-130 call-action"
                                data-js-action="chat:room-attach-files"
                                form="js-modal-message-attach-inner"
                                type="button"
                                data-user="${userId}"
                                data-room="${roomId}"
                                disabled
                        >Send file(s)</button>
                    </div>`
                );
            },
        }
    );
};

export default openAttachFilesDialogEpl;
