import { BLOG_URL, SITE_URL } from "@src/common/constants";
import { systemMessages } from "@src/util/system-messages/index";

const searchByItem = (e, $form) => {
    const type = $form.data("type");
    const keyword = $form.find("input[name=keywords]").val() || "";
    const country = $form.find("select[name=country]").val() || "";
    const category = $form.find("select[name=category]").val() || "";
    const industry = $form.find("select[name=industry]").val() || "";
    let searchPath = SITE_URL;
    let action = "";
    const page = $form.find("select[name=page]").val();
    const availablePages = {
        user_guide: "user_guide/search/?keywords=",
        faq: "faq/search/?keywords=",
        topics: "topics/help?keywords=",
        community: "questions?keywords=",
    };
    const time = $form.find("select[name=time]").val();
    const filterByType = $form.find("select[name=type]").val();
    const sorting = $form.find("input[name=sort]").val();
    const currentPage = $form.data("page");
    const filterByCategory = $form.find("input[name=category]").val();
    const featured = $form.find("input[name=featured]").val();
    const returnToPage = $form.find("input[name=return_to_page]").val();

    switch (type) {
        case "items":
            if (!category && !country && !keyword) {
                systemMessages("Error: Search parameters can't be empty.", "error");

                return false;
            }

            if (category) {
                action = `category/${category}`;
            } else {
                action = "search";
            }

            if (country) {
                action += `/country/${country}`;
            }

            if (keyword) {
                action += `?keywords=${encodeURIComponent(keyword)}`;
            }

            if (featured) {
                action += keyword ? `&featured=1` : `?featured=1`;
            }

            if (returnToPage) {
                action += keyword || featured ? `&returnToPage=1` : `?returnToPage=1`;
            }

            break;
        case "category":
            if (keyword.trim() === "") {
                systemMessages("Error: Search parameters can't be empty.", "error");

                return false;
            }

            action = `categories?keywords=${encodeURIComponent(keyword)}`;
            break;
        case "directory":
            if (!industry && !country && !keyword) {
                systemMessages("Error: Search parameters can't be empty.", "error");

                return false;
            }

            action = "directory/all";

            if (country !== "") {
                action += `/country/${country}`;
            }

            if (industry !== "") {
                action += `/industry/${industry}`;
            }

            if (keyword !== "") {
                action += `?keywords=${encodeURIComponent(keyword)}`;
            }
            break;
        case "b2b":
            if (!industry && !country && !keyword) {
                systemMessages("Error: Search parameters can't be empty.", "error");

                return false;
            }

            action = "b2b/all";

            if (keyword) {
                if (country) {
                    action += `/country/${country}`;
                }

                if (industry) {
                    action += `/industry/${industry}`;
                }

                action += `?keywords=${encodeURIComponent(keyword)}`;
            } else if (industry) {
                if (country) {
                    action += `/country/${country}`;
                }

                action += `/industry/${industry}`;
            } else if (country) {
                action += `/country/${country}`;
            }
            break;
        case "help":
            if (keyword.trim() === "") {
                systemMessages("Error: Search parameters can't be empty.", "message-error");

                return false;
            }

            action = `help/search/?keywords=${encodeURIComponent(keyword)}`;
            break;
        case "questions":
            if (!keyword || !page) {
                systemMessages("Error: Search parameters can't be empty.", "error");

                return false;
            }

            if (!(page in availablePages)) {
                systemMessages("Error: The data you sent does not appear to be valid.", "error");

                return false;
            }

            action = availablePages[page] + keyword;
            break;
        case "blogs":
            if (keyword.trim() === "") {
                systemMessages("Error: Search parameters can't be empty.", "error");

                return false;
            }

            action = `?keywords=${encodeURIComponent(keyword)}`;
            searchPath = BLOG_URL;
            break;
        case "events":
            if (!country && keyword.trim() === "" && !time && !filterByType) {
                systemMessages("Error: Search parameters can't be empty.", "error");

                return false;
            }

            action = "ep_events";
            if (time === "past" || (currentPage === "pastEvents" && !time)) {
                action += "/past";
            }

            if (country) {
                action += `/country/${country}`;
            }

            if (filterByCategory) {
                action += `/category/${filterByCategory}`;
            }

            if (filterByType) {
                action += `/type/${filterByType}`;
            }

            if (time && !(currentPage !== "pastEvents" && time === "past")) {
                action += `/time/${time}`;
            }

            if (keyword) {
                action += `?keywords=${encodeURIComponent(keyword)}`;
            }

            if (sorting) {
                action += `${keyword ? "&" : "?"}sort=${sorting}`;
            }
            break;
        default:
    }

    // For autocompletion
    $form.trigger("form:submit:autcomplete");

    const url = new URL(searchPath + action);
    globalThis.location.href = String(url);

    return true;
};

export default searchByItem;
