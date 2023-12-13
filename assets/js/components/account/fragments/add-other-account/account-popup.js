import $ from "jquery";

import { translate } from "@src/i18n";
import { runFormTracking } from "@src/util/tracking";
import { hideLoader, showLoader } from "@src/util/common/loader";
import { disableFormValidation, enableFormValidation, validateElement } from "@src/plugins/validation-engine/index";
import { systemMessages } from "@src/util/system-messages/index";
import onResizeCallback from "@src/util/dom/on-resize-callback";
import { SITE_URL } from "@src/common/constants";
import delay from "@src/util/async/delay";

class AccountPopup {
    constructor(params = {}) {
        this.tabsElements = {};
        this.$modalRegister = $(".js-modal-register-additional");
        this.$formTabs = $("#js-popup-register-form");
        this.$tabsNav = $("#js-popup-register-nav-tabs");
        this.$anotherAccountCheckbox = $("#js-another-account-checkbox");
        this.registerAnotherAccount = ".js-register-another-account input[type=checkbox]";
        this.$registerAnotherAccount = $(this.registerAnotherAccount);
        this.$registerAnotherAccountAll = $(".js-register-another-account-all");
        this.$useExistingInfoBlock = $("#js-use-existing-info-block");
        this.$copyInformationBlock = $("#js-copy-information-block");
        this.copyPersonalInformation = ".js-copy-info-radio";
        this.$copyPersonalInformation = $(this.copyPersonalInformation);
        this.copyPersonalInformationAllRadio = $(".js-copy-personal-info");
        this.copyCompanyInformationRadio = ".js-copy-company-info-radio";
        this.copyCompanyInformation = ".js-copy-company-info";
        this.$copyCompanyInformation = $(this.copyCompanyInformation);
        this.$copyCompanyInformationBlock = $("#js-copy-company-info-block");
        this.$jsBtnPrev = this.$modalRegister.find(".js-btn-prev");
        this.$jsBtnNext = this.$modalRegister.find(".js-btn-next");
        this.$jsBtnSubmit = this.$modalRegister.find(".js-btn-submit");
        this.$jsBtnDone = this.$modalRegister.find(".js-btn-done");
        this.multipleSelect = $(".js-multiple-epselect-inputs").parent();
        this.multipleSelectFormGroup = this.multipleSelect.closest(".form-group");

        this.currentStep = 1;
        this.totalSteps = 2;
        this.totalAnotherCheckbox = this.$anotherAccountCheckbox.find(this.registerAnotherAccount).length - 1;
        this.totalAnotherCheckboxChecked = 0;
        this.width = $(globalThis).width();
        this.titleDefault = translate({ plug: "general_i18n", text: "login_add_another_account" });
        this.currentGroupType = params.groupType;
        this.canCopyPersonalInfo = params.canCopyPersonalInfo;
        this.showExistingInfoBlock = true;

        this.initPlug();
        this.setTitle();
        this.initRadio();
    }

    initPlug() {
        setTimeout(() => {
            this.initTabs();
        }, 100);
        this.initAccountCheckbox();
        this.validateTabInit();

        onResizeCallback(() => {
            this.initTabs();
        });

        onResizeCallback(() => {
            this.initTabs();
        }, this.$formTabs);
    }

    initTabs() {
        let indexPrev;
        const that = this;

        this.$tabsNav.find(".delimeter").remove();

        const formProgress = function () {
            const $this = $(this);
            const $link = $this.find(".link");
            const $point = $this.find(".tabs-circle__point");
            const element = {};
            element.index = $this.index();
            element.width = $this.outerWidth();
            element.left = $this.position().left;
            element.leftTotal = element.left + element.width;
            element.link = {};
            element.link.width = $point.outerWidth();
            element.link.left = $point.position().left;
            let progress = "";

            if ($this.hasClass("complete") || $link.hasClass("active") || $this.hasClass("additional")) {
                progress = "progress";
            }

            if (that.tabsElements[indexPrev] !== undefined) {
                const prevElement = that.tabsElements[indexPrev];
                const delimeter = {};
                delimeter.plusElementWidth = (element.width - element.link.width) / 2;
                delimeter.plusAllWidth = delimeter.plusElementWidth + (prevElement.width - prevElement.link.width) / 2;
                delimeter.width = element.left + delimeter.plusAllWidth - prevElement.leftTotal;
                delimeter.minusPosition = delimeter.width - delimeter.plusElementWidth;

                $this.append(`<div class="delimeter ${progress}" style="width: ${delimeter.width}px; left: -${delimeter.minusPosition}px;"></div>`);
            }

            indexPrev = element.index;
            that.tabsElements[element.index] = element;
        };
        this.$tabsNav.find(".tabs-circle__item:visible").each(formProgress);
    }

    setTitle() {
        if (this.width <= 767) {
            const txt = this.$tabsNav.find(".link.active .tabs-circle__txt").text();
            this.$modalRegister.find(".bootstrap-dialog-title").text(txt);
        } else if (this.titleDefault !== this.$modalRegister.find(".bootstrap-dialog-title").text()) {
            this.$modalRegister.find(".bootstrap-dialog-title").text(this.titleDefault);
        }
    }

    initAccountCheckbox() {
        const that = this;
        let personalCheckboxChecked = null;
        let companyCheckboxChecked = null;
        const onCheckAnotherAccount = function () {
            const $checkbox = $(this);
            const val = $checkbox.val();
            let checkedCount = $(that.registerAnotherAccount).find("input[type=checkbox]").length;

            if (that.$useExistingInfoBlock.length) {
                if (val === "buyer" && !that.canCopyPersonalInfo) {
                    that.showExistingInfoBlock = false;
                } else {
                    if (val === "buyer" && checkedCount < 1) {
                        that.$copyCompanyInformationBlock.removeClass("display-n_i");
                    }

                    that.showExistingInfoBlock = true;
                }
            }

            if (that.showExistingInfoBlock) {
                that.$useExistingInfoBlock.fadeIn();
            }

            if (val !== "all") {
                that.totalAnotherCheckboxChecked = $(".js-register-another-account input[type=checkbox]:checked").length;

                if ($checkbox.prop("checked")) {
                    that.$copyCompanyInformationBlock.removeClass("display-n_i");
                    if (that.totalAnotherCheckboxChecked === that.totalAnotherCheckbox) {
                        that.$registerAnotherAccountAll.find("input[type=checkbox]").prop("checked", true);
                    }
                } else {
                    that.$registerAnotherAccountAll.find("input[type=checkbox]").prop("checked", false);
                    checkedCount += 1;

                    if (!that.totalAnotherCheckboxChecked) {
                        that.showExistingInfoBlock = false;
                    } else {
                        that.showExistingInfoBlock = true;
                    }

                    if (val !== "buyer" && !that.$copyCompanyInformationBlock.hasClass("display-n_i")) {
                        that.$copyCompanyInformationBlock.removeClass("display-n_i");
                        that.$copyCompanyInformation.find("input:checked").prop("checked", false);
                    }

                    if (!checkedCount || !that.showExistingInfoBlock) {
                        that.onHideUseExistingInfoBlock();
                    }
                }
            } else if ($checkbox.prop("checked")) {
                $('div[class*="js-account-registration-another-"]').show();
                that.totalAnotherCheckboxChecked = that.totalAnotherCheckbox;

                $(that.registerAnotherAccount).prop("checked", true);
            } else {
                $('div[class*="js-account-registration-another-"]').hide();
                that.totalAnotherCheckboxChecked = 0;

                $(that.registerAnotherAccount).prop("checked", false);

                that.onHideUseExistingInfoBlock();
            }
        };

        const onCheckCopyPersonalInfo = async function (e) {
            e.preventDefault();
            await delay(50);

            this.checked = !this.checked;

            personalCheckboxChecked = $(that.copyPersonalInformationAllRadio).find("input:checked").length;

            if (companyCheckboxChecked) {
                that.$jsBtnNext.prop("disabled", false);
            } else {
                that.$jsBtnNext.prop("disabled", !this.checked);
            }
        };

        const onCopyCompanyInformation = async function (e) {
            e.preventDefault();
            await delay(50);

            this.checked = !this.checked;

            companyCheckboxChecked = $(that.copyCompanyInformation).find("input:checked").length;

            if (personalCheckboxChecked) {
                that.$jsBtnNext.prop("disabled", false);
            } else {
                that.$jsBtnNext.prop("disabled", !this.checked);
            }

            if (!this.checked) {
                that.multipleSelect.removeClass("validate[required]");
                that.multipleSelectFormGroup.hide();
            } else {
                that.multipleSelect.addClass("validate[required]");
                that.multipleSelectFormGroup.show();
            }
        };

        this.$formTabs.on("click", this.registerAnotherAccount, onCheckAnotherAccount);
        this.$formTabs.on("click", this.copyPersonalInformation, onCheckCopyPersonalInfo);
        this.$formTabs.on("click", this.copyCompanyInformationRadio, onCopyCompanyInformation);
    }

    validateChooseType() {
        const $checkBox = this.$formTabs.find('input[name="type_another_account"]:checked');
        if ($checkBox.length === 0) {
            systemMessages(translate({ plug: "general_i18n", text: "validate_error_message" }), "error");

            return;
        }

        let checkBoxVal = "all";

        if ($checkBox.length !== this.totalAnotherCheckbox + 1) {
            checkBoxVal = $checkBox.val();
        }

        if (checkBoxVal === "all") {
            this.$tabsNav.find(".tabs-circle__item--min-simple").removeClass("display-n");
            this.totalSteps = 4;
        } else {
            this.$tabsNav
                .find(`.tabs-circle__item--min-simple[data-type="${checkBoxVal}"]`)
                .removeClass("display-n")
                .siblings(".tabs-circle__item--min-simple")
                .addClass("display-n");
            this.totalSteps = 3;
        }

        this.onNextRegisterStep();
        // this.$tabsNav.find('.link.active').closest('.tabs-circle__item').next('.tabs-circle__item').find('.link').trigger('click');
    }

    validateTab(step) {
        const that = this;

        validateElement(this.$formTabs, {
            updatePromptsPosition: true,
            promptPosition: "topLeft:0",
            autoPositionUpdate: true,
            focusFirstField: false,
            scroll: false,
            showArrow: false,
            addFailureCssClassToField: "validengine-border",
            onValidationComplete(form, status) {
                if (status) {
                    that.validateRegisterStep(that.$formTabs, step);
                } else {
                    systemMessages(translate({ plug: "general_i18n", text: "validate_error_message" }), "error");
                }
            },
        });
    }

    onNextRegisterSteps() {
        // var step = $this.data('step');
        const step = this.$formTabs.find(".tab-pane.show.active").data("link");

        if (this.currentStep === 1) {
            this.validateChooseType();
        } else {
            this.validateTab(step);
        }
    }

    updatePopup() {
        this.setTitle();

        setTimeout(() => {
            this.initTabs();
        }, 100);
    }

    nextRegisterStepsSuccess() {
        this.onNextRegisterStep();
    }

    onNextRegisterStep(status = null) {
        const statusText = status || "next";

        const $next = this.$tabsNav.find(".link.active").closest(".tabs-circle__item").addClass("complete").nextAll(".tabs-circle__item:visible").first();

        $next.find(".link").trigger("click").next(".delimeter").addClass("progress");

        if (statusText === "finish") {
            $next.addClass("complete");
        }

        this.currentStep += 1;

        this.actualizeBtnNavigate();

        setTimeout(() => {
            this.updatePopup();
            hideLoader(this.$formTabs);
        }, 200);
    }

    onPrevRegisterSteps() {
        this.onPrevRegisterStep();
    }

    onPrevRegisterStep() {
        this.$jsBtnPrev.prop("disabled", false);

        this.$tabsNav
            .find(".link.active")
            .next(".delimeter")
            .removeClass("progress")
            .closest(".tabs-circle__item")
            .prevAll(".tabs-circle__item:visible")
            .first()
            .removeClass("complete")
            .find(".link")
            .trigger("click");

        this.currentStep -= 1;
        this.actualizeBtnNavigate();
        this.updatePopup();
    }

    actualizeBtnNavigate() {
        if (this.currentStep === 1) {
            this.$jsBtnNext.removeClass("display-n");
            this.$jsBtnPrev.addClass("display-n");
            this.$jsBtnSubmit.addClass("display-n");
        } else if (this.currentStep === this.totalSteps - 1) {
            this.$jsBtnNext.addClass("display-n");
            this.$jsBtnPrev.removeClass("display-n");
            this.$jsBtnSubmit.removeClass("display-n");
            this.$jsBtnDone.addClass("display-n");
        } else if (this.currentStep === this.totalSteps) {
            this.$jsBtnNext.addClass("display-n");
            this.$jsBtnPrev.addClass("display-n");
            this.$jsBtnSubmit.addClass("display-n");
            this.$jsBtnDone.removeClass("display-n");
        } else {
            this.$jsBtnNext.removeClass("display-n");
            this.$jsBtnPrev.removeClass("display-n");
            this.$jsBtnSubmit.addClass("display-n");
        }

        setTimeout(() => {
            if (this.$jsBtnPrev.attr("disabled")) {
                this.$jsBtnPrev.removeAttr("disabled");
            }
        }, 100);
    }

    validateRegisterStep($form, step) {
        const that = this;
        const fdata = $form.serialize();
        const $modalContent = $form.closest(".modal-content");

        $.ajax({
            type: "POST",
            // eslint-disable-next-line no-underscore-dangle
            url: `${SITE_URL}register/ajax_operations/validate_add_accounts/${step}`,
            data: fdata,
            beforeSend() {
                showLoader($modalContent);
            },
            dataType: "json",
            success(resp) {
                hideLoader($modalContent);

                if (resp.mess_type === "success") {
                    that.nextRegisterStepsSuccess();
                } else {
                    systemMessages(resp.message, resp.mess_type);
                }
            },
        });
        return false;
    }

    async onValidateTabSubmit($this) {
        await this.validateTabInit($this);
        this.$formTabs.trigger("submit");
    }

    validateTabInit(btn) {
        return new Promise(resolve => {
            disableFormValidation(this.$formTabs);
            enableFormValidation(
                this.$formTabs,
                {
                    updatePromptsPosition: true,
                    promptPosition: "topLeft:0",
                    autoPositionUpdate: true,
                    focusFirstField: false,
                    scroll: false,
                    showArrow: false,
                    addFailureCssClassToField: "validengine-border",
                },
                btn
            );
            resolve();
        });
    }

    onPopupRegisterForm(form) {
        const $form = $(form);

        this.sendRequest($form);

        return false;
    }

    sendRequest($form) {
        const that = this;
        const fdata = $form.serialize();
        // eslint-disable-next-line no-underscore-dangle
        const url = `${SITE_URL}register/ajax_operations/save_another_account`;
        const $modalContent = $form.closest(".modal-content");

        $.ajax({
            type: "POST",
            url,
            data: fdata,
            beforeSend() {
                showLoader($modalContent);
            },
            dataType: "json",
            success(resp) {
                hideLoader($modalContent);
                runFormTracking($form, resp.mess_type === "success");

                if (resp.mess_type === "success") {
                    that.cleanTabs();
                    that.onNextRegisterStep("finish");
                } else {
                    systemMessages(resp.message, resp.mess_type);
                }
            },
        });
    }

    cleanTabs() {
        this.$formTabs.find('.tab-content .tab-pane[data-link*="validate_"]').html("");
    }

    async initRadio() {
        const that = this;

        const onToggleVisibilityCopyInformationBlock = function () {
            if ($(this).val() === "yes") {
                that.$jsBtnNext.prop("disabled", "true");
                that.$copyInformationBlock.fadeIn();
            } else {
                that.$jsBtnNext.removeAttr("disabled");
                that.$copyInformationBlock.fadeOut();
                that.$copyInformationBlock.find(".js-copy-info-radio").prop("checked", false);
            }

            if (!that.multipleSelect.hasClass("validate[required]")) {
                that.multipleSelect.addClass("validate[required]");
                that.multipleSelectFormGroup.show();
            }
        };

        const element = $(".js-radio-blue");

        element.on("change", onToggleVisibilityCopyInformationBlock);
    }

    onHideUseExistingInfoBlock() {
        const existingInfoBlock = $("#js-use-existing-info-block");
        const that = this;

        existingInfoBlock.fadeOut();

        if (!that.multipleSelect.hasClass("validate[required]")) {
            that.multipleSelect.addClass("validate[required]");
            that.multipleSelectFormGroup.show();
        }
    }
}

export default AccountPopup;
