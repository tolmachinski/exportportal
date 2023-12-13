import $ from "jquery";
import { KEYWORDS } from "@src/plugins/jquery-validation/rules";
import EventHub from "@src/event-hub";

// @ts-ignore
import "@scss/epl/pages/resources/index.scss";
import lazyLoadingScriptOnScroll from "@src/common/lazy/lazy-script";

$(() => {
    // FAQ Search Form
    const faqSearchForm = $("#js-faq-search-form");
    const faqSearchFormValidation = async () => {
        const validationOptions = {
            rules: {
                keywords: KEYWORDS,
            },
        };
        const { default: initJqueryValidation } = await import("@src/plugins/jquery-validation/lazy");
        const { default: searchFaq } = await import("@src/epl/pages/resources/callbacks/search-faq");
        initJqueryValidation("#js-faq-search-form", searchFaq.bind(null, faqSearchForm), validationOptions);
    };

    lazyLoadingScriptOnScroll(faqSearchForm, faqSearchFormValidation, "50%");
    faqSearchForm.on("submit", faqSearchFormValidation);

    // Document Upload Form
    const documentUploadForm = $("#js-epl-resources-document-upload-form");
    const documentUploadFormValidation = async () => {
        const validationOptions = {
            rules: {
                language: {
                    required: true,
                },
            },
        };
        const { default: initJqueryValidation } = await import("@src/plugins/jquery-validation/lazy");
        const { default: submitGuideForm } = await import("@src/epl/pages/resources/callbacks/document-upload-form");
        initJqueryValidation("#js-epl-resources-document-upload-form", submitGuideForm.bind(null, documentUploadForm), validationOptions);
    };

    lazyLoadingScriptOnScroll(documentUploadForm, documentUploadFormValidation, "50%");
    documentUploadForm.on("submit", documentUploadFormValidation);

    // Download PDF guide
    EventHub.on("epl-resources:download-pdf-guide", async (e, button) => {
        const { default: downloadGuide } = await import("@src/epl/pages/resources/callbacks/download-guide");
        downloadGuide(button);
    });
});
