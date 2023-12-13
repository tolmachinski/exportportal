import { SITE_URL } from "@src/common/constants";

const searchFaq = form => {
    let formAction = `${SITE_URL}faq/all`;
    const keywords = form.find("input[name=keywords]").val().replace(/(\s)+/g, "$1");

    if (keywords !== "") {
        formAction += `?keywords=${keywords}`;
        globalThis.location.href = formAction;
    }

    return false;
};

export default searchFaq;
