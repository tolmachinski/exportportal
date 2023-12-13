import $ from "jquery";

import { hideLoader, showLoader } from "@src/util/common/loader";
import { systemMessages } from "@src/util/system-messages/index";
import { SHIPPER_PAGE, SUBDOMAIN_URL } from "@src/common/constants";
import { updateFancyboxPopup } from "@src/plugins/fancybox/v2/util";
import { updateFancyboxPopup3 } from "@src/plugins/fancybox/v3/util";
import { translate } from "@src/i18n";
import EventHub from "@src/event-hub";

const laodSavedList = function (type, page) {
    const pageParam = page === undefined ? 1 : page;

    $.ajax({
        url: `${SUBDOMAIN_URL}${type}/ajax_get_saved`,
        type: "POST",
        data: { page: pageParam },
        dataType: "JSON",
        beforeSend() {
            showLoader($("#epuser-saved2"));
        },
        success(resp) {
            hideLoader($("#epuser-saved2"));

            if (SHIPPER_PAGE) {
                updateFancyboxPopup3();
            } else {
                updateFancyboxPopup();
            }

            if (resp.mess_type === "success") {
                if (resp.counter !== undefined) $(`.epuser-subline-nav2 a[data-type=${type}]`).find(".count").text(resp.counter);

                $("#epuser-saved2").find(".js-epuser-saved-page").remove();
                $("#epuser-saved2").find(".js-epuser-saved-content").remove();
                $(resp.message).insertAfter($("#js-epuser-saved-menu"));
            } else {
                systemMessages(resp.message, resp.mess_type);
            }
        },
    });
};

const btnlaodSavedList = function ($this) {
    const type = $this.data("type");
    const txt = $this.find(".txt").text();
    const count = $this.find(".count").text();
    const $dropdown = $("#js-epuser-saved-menu-dropdown");

    $this.addClass("active").siblings().removeClass("active");
    if ($this.closest(".epuser-subline-nav2__item").length) {
        $dropdown.find(`.dropdown-item[data-type="${type}"]`).addClass("active").siblings().removeClass("active");
    } else {
        const $tabs = $("#js-epuser-saved-menu").find(".epuser-subline-nav2__item");
        $tabs.find(`.link[data-type="${type}"]`).addClass("active").siblings().removeClass("active");
    }

    const $dropdownTop = $dropdown.find('[data-toggle="dropdown"]');
    $dropdownTop.find(".txt").text(txt);
    $dropdownTop.find(".count").text(count);

    if ($dropdown.find(".dropdown-menu").is(":visible")) {
        $dropdown.find('a[data-toggle="dropdown"]').dropdown("toggle");
    }

    laodSavedList(type);
};

const removeHeaderContact = function (opener) {
    const $this = $(opener);

    $.ajax({
        url: `${SUBDOMAIN_URL}contact/ajax_contact_operations/remove/${$this.data("id")}`,
        type: "POST",
        dataType: "JSON",
        success(resp) {
            systemMessages(resp.message, resp.mess_type);

            if (resp.mess_type === "success") {
                $this.closest(".ppersonal-followers__item").fadeOut("normal", function () {
                    $(this).remove();
                });

                laodSavedList("contact", 1);
            }
        },
    });
};

const removeHeaderCompany = function (opener) {
    const $this = $(opener);

    $.ajax({
        url: `${SUBDOMAIN_URL}directory/ajax_company_operations/remove_company_saved`,
        type: "POST",
        dataType: "JSON",
        data: { company: $this.data("company") },
        success(resp) {
            systemMessages(resp.message, resp.mess_type);

            if (resp.mess_type === "success") {
                $this.closest(".companies-wr").fadeOut("normal", function () {
                    $(this).remove();
                });

                laodSavedList("directory", 1);
            }
        },
    });
};

const removeHeaderShipper = function (opener) {
    const $this = $(opener);

    $.ajax({
        url: `${SUBDOMAIN_URL}shipper/ajax_shipper_operation/remove_shipper_saved`,
        type: "POST",
        dataType: "JSON",
        data: { company: $this.data("shipper") },
        success(resp) {
            systemMessages(resp.message, resp.mess_type);

            if (resp.mess_type === "success") {
                $this.closest(".companies-wr").fadeOut("normal", function () {
                    $(this).remove();
                });

                laodSavedList("shippers", 1);
            }
        },
    });
};

const removeHeadeB2bPartners = function (opener) {
    const $this = $(opener);

    $.ajax({
        url: `${SUBDOMAIN_URL}b2b/ajax_b2b_operation/delete_partner`,
        type: "POST",
        dataType: "JSON",
        data: { partner: $this.data("partner"), company: $this.data("company") },
        success(resp) {
            systemMessages(resp.message, resp.mess_type);

            if (resp.mess_type === "success") {
                $this.closest(".companies-wr").fadeOut("normal", function () {
                    $(this).remove();
                });

                laodSavedList("b2b", 1);
            }
        },
    });
};

const removeHeaderProduct = function (opener) {
    const $this = $(opener);

    $.ajax({
        url: `${SUBDOMAIN_URL}items/ajax_saveproduct_operations/remove_product_saved`,
        type: "POST",
        dataType: "JSON",
        data: { product: $this.data("product") },
        success(resp) {
            systemMessages(resp.message, resp.mess_type);

            if (resp.mess_type === "success") {
                $this.closest(".products-mini__wr").fadeOut("normal", function () {
                    $(this).remove();
                });

                const item = $(`.js-products-favorites-btn[data-item="${$this.data("item")}"]`);

                item.data("jsAction", "favorites:save-product")
                    .attr("title", translate({ plug: "general_i18n", text: "item_card_remove_from_favorites_tag_title" }))
                    .find(".ep-icon")
                    .toggleClass("ep-icon_favorite ep-icon_favorite-empty");

                const itemText = item.find("span");
                if (itemText.length) {
                    itemText.text(translate({ plug: "general_i18n", text: "item_card_label_favorite" }));
                }

                laodSavedList("items", 1);
            }
        },
    });
};

const removeHeaderSearch = function (opener) {
    const $this = $(opener);

    $.ajax({
        url: `${SUBDOMAIN_URL}save_search/ajax_savesearch_operations/remove_search_saved`,
        type: "POST",
        dataType: "JSON",
        data: { search: $this.data("search") },
        success(resp) {
            systemMessages(resp.message, resp.mess_type);

            if (resp.mess_type === "success") {
                $this.closest(".saved-search__item").fadeOut("normal", function () {
                    $(this).remove();
                });

                laodSavedList("save_search", 1);
            }
        },
    });
};

const callPagination = opener => {
    if (opener.hasClass("disabled") || opener.hasClass("active")) {
        return true;
    }

    const type = opener.data("type");
    const page = opener.data("page");
    laodSavedList(type, page);

    return true;
};

export default async () => {
    if (SHIPPER_PAGE) {
        // @ts-ignore
        await import(/*  webpackChunkName: "popup-favorites" */ "@scss/epl/components/favorites/index.scss");
    }

    [
        "saved:laod-saved-list",
        "saved:remove-header-contact",
        "saved:remove-header-company",
        "saved:remove-header-shipper",
        "saved:remove-header-b2b-partners",
        "saved:remove-header-product",
        "saved:remove-header-search",
        "saved:pagination",
    ].forEach(e => EventHub.off(e));

    $(() => {
        EventHub.on("saved:laod-saved-list", (e, button) => btnlaodSavedList(button));
        EventHub.on("saved:remove-header-contact", (e, button) => removeHeaderContact(button));
        EventHub.on("saved:remove-header-company", (e, button) => removeHeaderCompany(button));
        EventHub.on("saved:remove-header-shipper", (e, button) => removeHeaderShipper(button));
        EventHub.on("saved:remove-header-b2b-partners", (e, button) => removeHeadeB2bPartners(button));
        EventHub.on("saved:remove-header-product", (e, button) => removeHeaderProduct(button));
        EventHub.on("saved:remove-header-search", (e, button) => removeHeaderSearch(button));
        EventHub.on("saved:pagination", (e, button) => callPagination(button));

        const $savedTab = $("#js-epuser-saved-menu").find(".epuser-subline-nav2__item .link:not(.disabled)").first();

        if ($savedTab.length && $savedTab.data("type") !== "contact") {
            $savedTab.trigger("click");
        } else {
            $savedTab.addClass("active");
        }
    });
};
