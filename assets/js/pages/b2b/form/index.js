import $ from "jquery";
import createEditor from "@src/components/editor/tinymvc-editor";
import tagsInput from "@src/plugins/tags-input/index";
import EventHub, { removeListeners } from "@src/event-hub";
import initTinymceValidator from "@src/components/editor/tinymce-validator";
import initCropperValidator from "@src/plugins/cropper/cropper-validator";

import "@scss/user_pages/b2b/form/index.scss";

$(async () => {
    const [editor] = await createEditor("#js-b2b-request-form-description", {
        height: 245,
        toolbar: "bold italic underline | link | numlist bullist",
        // eslint-disable-next-line camelcase
        init_instance_callback: initTinymceValidator.bind(this, { validate: "validate[required,maxSize[3000]]", valHook: "editorB2bRequest" }),
    });

    $.valHooks.editorB2bRequest = {
        get() {
            return editor.getContent({ format: "text" }) || "";
        },
    };

    // region input tags
    const inputTagsSelector = ".js-b2b-request-form-tags-input";
    const inputTags = $(inputTagsSelector);

    tagsInput(inputTagsSelector, {
        width: "100%",
        height: "auto",
        minChars: 3,
        maxChars: 30,
        delimiter: [";"],
        defaultText: "Enter the tags",
    });

    $("body")
        .on("focus", ".tagsinput input", function onFocus() {
            $(this).closest(".tagsinput").prev(".formError").addClass("show").removeClass("hide");
        })
        .on("blur", ".tagsinput input", function onBlur() {
            $(this).closest(".tagsinput").prev(".formError").addClass("hide").removeClass("show");
        });

    // @ts-ignore
    inputTags.next(".tagsinput").addClass("validate[required]").setValHookType("tagsinput");
    $.valHooks.tagsinput = {
        get() {
            return inputTags.val() || [];
        },
        set(_el, val) {
            inputTags.val(val);
        },
    };
    // endregion input tags

    // init validation for select file btn
    initCropperValidator($(".js-fileinput-button"), $("#js-view-main-photo"));

    // region listeners
    EventHub.on("b2b-form:locate-partner.click", async () => {
        const { default: locatePartnerListener } = await import("@src/pages/b2b/form/locate-partner-by");
        removeListeners("b2b-form:locate-partner.click");
        locatePartnerListener();
    });

    EventHub.on("b2b-form:company-branch.click", async () => {
        const { default: companyBranchListener } = await import("@src/pages/b2b/form/get-company-branch");
        removeListeners("b2b-form:company-branch.click");
        companyBranchListener();
    });

    EventHub.off("b2b-form:submit");
    EventHub.on("b2b-form:submit", async (_e, form) => {
        const { default: submitB2bRequestForm } = await import("@src/pages/b2b/form/submit-b2b-request-form");
        submitB2bRequestForm(form);
    });

    if ($("#js-b2b-request-location-select").val() === "country") {
        EventHub.trigger("b2b-form:locate-partner.click");
    }
    // endregion listeners
});
