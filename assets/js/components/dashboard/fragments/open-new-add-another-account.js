import { openModalPopup } from "@src/plugins/bootstrap-dialog/index";

import simpleHideHeaderBottom from "@src/components/navigation/fragments/simple-hide-header-bottom";
import hideDashboardMenu from "@src/components/dashboard/fragments/hide-dashboard-menu";

const openNewAddAnotherAccount = async function (button) {
    // @ts-ignore
    await import("@scss/community_help/add_another_account.scss");

    hideDashboardMenu();
    simpleHideHeaderBottom();

    const link = button.attr("href");
    const title = button.data("title");
    const classes = "info-bootstrap-dialog--mw-530 info-bootstrap-dialog--footer-custom wr-input-label inputs-40 js-modal-register-additional";
    const buttons = `
        <div class="modal-flex__btns w-100pr">
            <div class="modal-flex__btns-left">
                <button class="js-btn-prev btn btn-dark btn-block display-n call-action" data-js-action="accounts:add-other:prev-steps" type="button">Back</button>
            </div>
            <div class="modal-flex__btns-right">
                <button class="js-btn-next btn btn-primary btn-block call-action" data-js-action="accounts:add-other:next-steps" type="button">Next</button>
                <button class="js-btn-submit btn btn-success display-n call-action" data-js-action="accounts:add-other:validate" type="button">Finish</button>
                <button class="js-btn-done btn btn-dark display-n call-action" data-js-action="accounts:add-other:close-all" type="button">Done</button>
            </div>
        </div>
    `;

    openModalPopup({ title, content: link, isAjax: true, buttons, validate: true, classes });
};

export default openNewAddAnotherAccount;
