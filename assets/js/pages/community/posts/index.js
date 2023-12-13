import $ from "jquery";
import { translate } from "@src/i18n";
import { hideLoader, showLoader } from "@src/util/common/loader";
import stopScrollLoadMore from "@src/util/dom/stop-scroll-load-more";
import { systemMessages } from "@src/util/system-messages/index";
import { SUBDOMAIN_URL } from "@src/common/constants";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";
import lazyLoadingInstance from "@src/plugins/lazy/index";

const loadComments = obj => {
    const element = $(obj);
    const commentsLabelElement = element.find(".js-comments-label");
    const commentsLabelValue =
        commentsLabelElement.html() === translate({ plug: "general_i18n", text: "community_word_view" })
            ? translate({ plug: "general_i18n", text: "community_word_hide" })
            : translate({ plug: "general_i18n", text: "community_word_view" });

    if (element.hasClass("load-hide")) {
        element.closest(".questions-answers__item").find(".questions-comments").slideToggle();
        element.closest(".questions-answers__item").find(".js-load-more-comments").slideToggle();
        commentsLabelElement.html(commentsLabelValue);
        return;
    }

    const idAnswer = element.data("answer");
    const url = `${SUBDOMAIN_URL}community_questions/ajax_comments_load_blocks/list`;

    postRequest(url, { answer: idAnswer })
        .then(response => {
            if (response.mess_type === "success") {
                element.closest(".questions-answers__item").find(".questions-comments").html(response.content).show();
                element.closest(".questions-answers__item").find(".questions-comments").parent().find(".js-load-more-comments").show();

                element.toggleClass("load-ajax load-hide");
                commentsLabelElement.html(commentsLabelValue);
                lazyLoadingInstance(".js-lazy", { threshhold: "10px" });
            }
        })
        .catch(handleRequestError);
};

const didHelp = function () {
    const btn = $(this);
    const item = btn.data("item");
    const type = btn.data("type");
    const action = btn.data("action");
    const page = btn.data("page");
    const votedClass = "active";
    const url = `${SUBDOMAIN_URL + page}/ajax_${type}_operation/help`;
    const didHelpWrapper = btn.parent();
    const arrowUpElement = didHelpWrapper.find(".js-arrow-up");
    const arrowDownElement = didHelpWrapper.find(".js-arrow-down");

    return postRequest(url, { id: item, type: action })
        .then(response => {
            if (typeof response.counter_plus !== "undefined") {
                didHelpWrapper.find(".js-counter-plus").text(response.counter_plus);
            }

            if (typeof response.counter_minus !== "undefined") {
                didHelpWrapper.find(".js-counter-minus").text(response.counter_minus);
            }

            if (typeof response.select_plus !== "undefined" && !arrowUpElement.hasClass(votedClass)) {
                arrowUpElement.addClass(votedClass);
            }

            if (typeof response.remove_plus !== "undefined") {
                arrowUpElement.removeClass(votedClass);
            }

            if (typeof response.select_minus !== "undefined" && !arrowDownElement.hasClass(votedClass)) {
                arrowDownElement.addClass(votedClass);
            }

            if (typeof response.remove_minus !== "undefined") {
                arrowDownElement.removeClass(votedClass);
            }

            if (response.mess_type !== "success") {
                systemMessages(response.message, response.mess_type);
            }
        })
        .catch(handleRequestError);
};

const loadMoreAnswers = function (el) {
    const $list = $(".questions-answers");
    const start = $list.find(".questions-answers__item").length;
    const url = `${SUBDOMAIN_URL}community_questions/ajax_answers_more`;

    stopScrollLoadMore(el);
    showLoader(el);

    // eslint-disable-next-line camelcase
    return postRequest(url, { id_question: el.data("id_question"), start })
        .then(response => {
            $($list).append(response.html);

            if ($list.find(".questions-answers__item").length >= response.count) {
                el.fadeOut("slow", function () {
                    $(this).remove();
                });
            }
            lazyLoadingInstance(".js-lazy", { threshhold: "10px" });
        })
        .catch(handleRequestError)
        .finally(() => hideLoader(el));
};

const loadMoreComments = function (el) {
    const $list = el.parent().closest(".questions-answers__item").find(".questions-comments");
    const start = $list.find(".questions-comments__item").length;
    const url = `${SUBDOMAIN_URL}community_questions/ajax_comments_load_blocks/list`;

    stopScrollLoadMore(el);
    showLoader(el);
    el.addClass("disabled");

    return postRequest(url, { answer: el.data("id_answer"), start })
        .then(response => {
            if (response.mess_type === "success") {
                $($list).append(response.content);
                if ($list.find(".questions-comments__item").length >= response.count) {
                    el.fadeOut(function remove() {
                        $(this).remove();
                    });
                }
                el.removeClass("disabled");
            }

            $list.find(".info-alert-b").remove();
            lazyLoadingInstance(".js-lazy", { threshhold: "10px" });
        })
        .catch(handleRequestError)
        .finally(() => hideLoader(el));
};

export { loadComments, didHelp, loadMoreAnswers, loadMoreComments };
