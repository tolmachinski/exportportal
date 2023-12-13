import $ from "jquery";
import { SITE_URL } from "@src/common/constants";

import { systemMessages } from "@src/util/system-messages/index";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";
import EventHub from "@src/event-hub";

const removeBtnMore = function () {
    if ($(".all-categories__search-item").length && $(".all-categories__search-results").length) {
        if ($(".all-categories__search-item").length >= $(".all-categories__search-results").data("count-items")) {
            $(".block-search .js-btn-more-categories").remove();
        }
    }
};

const setLocationHistory = keywords => {
    const url = new URL(globalThis.location.href);
    if (keywords) {
        url.searchParams.set("keywords", keywords);
    } else {
        url.search = "";
    }
    globalThis.history.pushState({}, document.title, url.href);
};

const search = async (e, form) => {
    form.find("button[type=submit]").attr("disabled", "disabled");
    form.find(".fileinput-loader-btn").show();

    const keywords = form.find('input[name="keywords"]').val();
    try {
        const { message, mess_type: messType, content } = await postRequest(
            `${SITE_URL}categories/getcategories`,
            {
                keywords,
                op: "search",
            },
            "JSON"
        );

        $(".block-search").remove();
        if (messType === "success") {
            form.after(content);
            removeBtnMore();
            setLocationHistory(keywords);
        } else {
            systemMessages(message, messType);
        }
    } catch (error) {
        handleRequestError(error);
    } finally {
        form.find(".fileinput-loader-btn").hide();
        form.find("button[type=submit]").removeAttr("disabled");
    }
};

const getMoreItems = async function () {
    const btn = $(this);
    btn.attr("disabled", "disabled");

    try {
        const currentBlocks = $(".all-categories__search-item");
        const { message, mess_type: messType, content } = await postRequest(
            `${SITE_URL}categories/getcategories`,
            {
                keywords: $(".all-categories__input-group").find('input[name="keywords"]').val(),
                op: "search",
                start: currentBlocks.length,
            },
            "JSON"
        );

        if (messType === "success") {
            if (currentBlocks.length - 1 > 0) {
                $(currentBlocks[currentBlocks.length - 1]).after(content);
            }
            removeBtnMore();
        } else {
            systemMessages(message, messType);
        }
    } catch (error) {
        handleRequestError(error);
    } finally {
        btn.removeAttr("disabled");
    }
};

const cleanSearch = function () {
    $(".block-search").remove();
    $(this).next("input").val("");
    setLocationHistory("");
};

const submitAddProduct = function (e, btn) {
    globalThis.location.href = `${SITE_URL}items/my?select_category=${btn.data("category")}`;
};

export default () => {
    $(".js-clean-categ-search").on("click", cleanSearch);
    $("body").on("click", ".js-btn-more-categories", getMoreItems);
    EventHub.on("categories:search-form-submit", search);
    EventHub.on("categories:submit-add-product", submitAddProduct);

    removeBtnMore();
};
