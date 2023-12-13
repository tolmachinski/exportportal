import $ from "jquery";

import "@src/boot/jquery-hooks";
import "@src/boot/http-api";

import { isIoS } from "@src/util/platform";
import { toggleMinMax } from "@src/util/minMax";
import { didHelp, loadComments, loadMoreAnswers, loadMoreComments } from "@src/pages/community/posts/index";
import { showSearchForm, toggleSearchForm } from "@src/pages/community/forms/search-form";
import searchQuestions from "@src/pages/community/forms/search-questions";
import EventHub from "@src/event-hub";

import "@scss/community_help/general_styles.scss";
import "@scss/community_help/toolbar_styles.scss";

const checkValue = elements => {
    elements.each(function each() {
        if ($(this).val()) {
            const parent = $(this).parent();
            const resetBtn = parent.children(".reset-btn");
            resetBtn.removeClass("display-n_i");
        }
    });
};

toggleSearchForm();

$(() => {
    const body = $(document.getElementsByTagName("body"));

    EventHub.on("form:search_question", async (e, button) => {
        await searchQuestions(e, button, $("#js-search-questions"));
    });
    EventHub.on("form:header_search_question", async (e, button) => {
        await searchQuestions(e, button, $("#js-header-search-questions"));
    });
    EventHub.on("answer:load_comments", (e, button) => {
        loadComments(button);
    });
    EventHub.on("answer:load_more_comments", (e, button) => {
        loadMoreComments(button);
    });
    EventHub.on("answer:load_more_answers", (e, button) => {
        loadMoreAnswers(button);
    });
    EventHub.on("form:show_search_form", (e, button) => {
        showSearchForm(e, button);
    });
    EventHub.on("minMax:toggle", (e, button) => {
        toggleMinMax(button);
    });

    checkValue($(".js-search-form-input"));

    if (isIoS()) {
        body.on("touchend", ".js-didhelp-btn", didHelp);
    } else {
        body.on("click", ".js-didhelp-btn", didHelp);
    }

    $(".js-search-form-input").on("change", function onChange() {
        const parent = $(this).parent();
        const resetBtn = parent.children(".reset-btn");
        if (!$(this).val()) {
            resetBtn.addClass("display-n_i");
        }
    });
});
