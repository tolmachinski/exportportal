import $ from "jquery";

import { SUBDOMAIN_URL } from "@src/common/constants";
import onResizeCallback from "@src/util/dom/on-resize-callback";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";
import simpleHideHeaderBottom from "@src/components/navigation/fragments/simple-hide-header-bottom";
import hideSideNav from "@src/components/navigation/fragments/hide-side-nav";
import hideDashboardMenu from "@src/components/dashboard/fragments/hide-dashboard-menu";

const scrollToEl = function ($wrapper, $minus) {
    let scrollTop = 0;
    let delay = 0;

    if ($("#js-epuser-subline:visible")) {
        delay = 500;
    }

    if ($("#js-mep-header-dashboard:visible")) {
        simpleHideHeaderBottom();
    }

    if ($(".maintenance-mode").length) {
        scrollTop = $wrapper.offset().top - $minus - 70;
    } else {
        scrollTop = $wrapper.offset().top - $minus - 30;
    }

    $("html,body").delay(delay).animate(
        {
            scrollTop,
        },
        500
    );
};

const loadSearchForm = function ($this, $haderSearch) {
    const $mainHaderUserLine = $(".js-main-user-line");
    const url = `${SUBDOMAIN_URL}community_questions/ajax_operation/get_search`;

    $this.prop("disabled", true);

    postRequest(url, {})
        .then(response => {
            if (response.mess_type === "success") {
                $haderSearch.html(response.html).slideDown(() => {
                    $this.prop("disabled", false);

                    scrollToEl($haderSearch, $mainHaderUserLine.outerHeight());
                });
            }
        })
        .catch(handleRequestError);
};

const showSearchForm = function (e, $this) {
    e.preventDefault();
    const $mainHaderSearch = $("#js-search-questions");
    const $communitySearch = $(".js-community-search-form");
    const $mainHaderUserLine = $(".js-main-user-line");
    let headerLineTop = 0;
    let headerLineHeight = 0;
    let delay = 0;

    if ($("#js-epuser-subline:visible").length) {
        delay = 500;
        hideDashboardMenu();
    }

    if ($("#js-mep-header-dashboard:visible")) {
        delay = 500;
        simpleHideHeaderBottom();
    }

    if ($("#js-ep-header-top:visible").length) {
        hideSideNav();
    }

    if ($mainHaderUserLine.length && $mainHaderUserLine.is(":visible")) {
        headerLineTop = $mainHaderUserLine.offset().top;
        headerLineHeight = $mainHaderUserLine.outerHeight();
    }

    if (!$mainHaderSearch.length || !$communitySearch.is(":visible")) {
        const $searchQuestions = $("#js-header-search-questions");
        const $haderSearch = $(".js-community-search-header");

        if ($searchQuestions.length) {
            if (headerLineTop === 0 || headerLineTop === 40) {
                $haderSearch.delay(delay).slideToggle();
            } else {
                $("html")
                    .delay(delay)
                    .animate(
                        {
                            scrollTop: $haderSearch.offset().top - headerLineHeight,
                        },
                        500,
                        () => {
                            scrollToEl($haderSearch, headerLineTop);
                            if (!$searchQuestions.is(":visible")) {
                                setTimeout(() => {
                                    $haderSearch.delay(delay).slideToggle();
                                }, 400);
                            }
                        }
                    );
            }
        } else {
            loadSearchForm($this, $haderSearch);
        }
    } else {
        scrollToEl($mainHaderSearch, headerLineHeight);
    }
};

const toggleSearchForm = () => {
    const $headerSearchForm = $(".js-community-search-header");
    const $searchForm = $("#js-search-questions");

    onResizeCallback(() => {
        if ($(window).width() > 767 && $searchForm.is(":visible") && $headerSearchForm.is(":visible")) {
            $headerSearchForm.slideToggle();
        }
    });
};

export { showSearchForm, toggleSearchForm };
