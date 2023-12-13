import downloadGuide from "@src/epl/pages/resources/callbacks/download-guide";

const submitGuideForm = form => {
    const downloadBtn = form.find("#js-document-upload-download-btn");

    downloadBtn.data("lang", form.find("#js-epl-resources-ug-select-lang").val());

    downloadGuide(downloadBtn);
};

export default submitGuideForm;
