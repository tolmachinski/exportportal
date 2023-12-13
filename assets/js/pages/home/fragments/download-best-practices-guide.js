import $ from "jquery";
import { SUBDOMAIN_URL } from "@src/common/constants";
import { systemMessages } from "@src/util/system-messages/index";
import FileSaver from "file-saver";
import postRequest from "@src/util/http/post-request";
import handleRequestError from "@src/util/http/handle-request-error";

const downloadBestPracticesGuide = btn => {
    const { guideName, lang, group } = $(btn).data();

    postRequest(`${SUBDOMAIN_URL}user_guide/get_guides/${guideName}/${lang}/${group}`)
        .then(response => {
            if (response.mess_type === "success") {
                FileSaver.saveAs(`${SUBDOMAIN_URL}user_guide/download/${guideName}/${lang}/${group}`);
            } else {
                systemMessages(response.message, response.mess_type);
            }
        })
        .catch(error => {
            handleRequestError(error);
        });
};

export default downloadBestPracticesGuide;
