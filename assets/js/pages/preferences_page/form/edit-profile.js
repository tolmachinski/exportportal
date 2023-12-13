import $ from "jquery";

import { onPrevTabStep, onNextTabSteps, onValidateTabSubmit } from "@src/common/popup-info-steps/popup-info-steps";
import EventHub, { removeListeners } from "@src/event-hub";
import editProfileForm from "@src/pages/preferences_page/form/edit-profile-form";
import processTabs from "@src/common/process-tabs/lazy-process-tabs";
import getElement from "@src/util/dom/get-element";

export default (wrapperSelector, state = {}) => {
    const { url } = state;
    const wrapper = $(wrapperSelector);
    const form = getElement(wrapper.data("form"));
    const navigation = getElement(wrapper.data("navigation"));

    processTabs(navigation);
    editProfileForm(form, url);
    removeListeners("user:profile-form.previous-step", "user:profile-form.next-step", "user:profile-form.validate-step");

    EventHub.on("user:profile-form.validate-step", (e, button) => onValidateTabSubmit(form, button));
    EventHub.on("user:profile-form.previous-step", () => onPrevTabStep(navigation));
    EventHub.on("user:profile-form.next-step", (e, button) => onNextTabSteps(form, navigation, button));
};
