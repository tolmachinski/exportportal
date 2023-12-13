import $ from "jquery";

import { onPrevTabStep, onNextTabSteps, onValidateTabSubmit } from "@src/common/popup-info-steps/popup-info-steps";
import EventHub, { removeListeners } from "@src/event-hub";
import editCompanyForm from "@src/pages/company/form/edit-company-form";
import processTabs from "@src/common/process-tabs/lazy-process-tabs";
import getElement from "@src/util/dom/get-element";
// Styles
import "@scss/user_pages/edit_info_popup/index.scss";

export default (wrapperSelector, state = {}) => {
    const { url, marker } = state;
    const wrapper = $(wrapperSelector);
    const form = getElement(wrapper.data("form"));
    const navigation = getElement(wrapper.data("navigation"));

    processTabs(navigation);
    editCompanyForm(form, url, marker);
    removeListeners("company:edit-form.validate-step", "company:edit-form.previous-step", "company:edit-form.next-step");

    EventHub.on("company:edit-form.validate-step", (e, button) => onValidateTabSubmit(form, button));
    EventHub.on("company:edit-form.previous-step", () => onPrevTabStep(navigation));
    EventHub.on("company:edit-form.next-step", (e, button) => onNextTabSteps(form, navigation, button));
};
