/* eslint-disable camelcase */
/* eslint-disable no-underscore-dangle */
import $ from "jquery";

import { hideLoader, showLoader } from "@src/util/common/loader";
import { SITE_URL } from "@src/common/constants";

import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";
import onResizeCallback from "@src/util/dom/on-resize-callback";
import EventHub from "@src/event-hub";

const selectors = {
    sideCategoriesWr: "#js-sidebar-categories-selected",
    categoriesGroupWr: "#js-sidebar-categories",
    firstCategoryList: "#js-first-side-category-list",
    centerCategoryList: "#js-center-side-category-list",
    lastCategoryList: "#js-last-side-category-list",
    categoriesGroup: "#js-sidebar-categories-group",
    templateCategoriesGroupList: "#js-template-side-categories-group-list",
    templateCategoriesGroupListItem: "#js-template-side-categories-group-list-item",
    templateCategoriesGroupListItemToggle: "#js-template-side-categories-group-list-item-toggle",
    categoriesGroupMep: "#js-side-categories-group-mep",
    categoriesGroupListWr: ".js-wr-category-group",
    categoriesList: ".js-sidebar-categories-list",
    categoriesSelected: "#js-sidebar-categories-selected",
};

const bannerTemplate = (img, link, name) => {
    return `<li class="popup-categories-list__item">
                <a class="popup-categories-list__name popup-categories-list__name--h-95" href="${link}" target="_blank">
                    <picture class="popup-categories-list__image">
                        <img
                            class="image js-lazy"
                            src="${img}"
                            alt="${name}"
                        >
                    </picture>
                    <span class="popup-categories-list__text">${name}</span>
                </a>
            </li>`;
};

const categoriesGroupSelectMep = (step, $this) => {
    const $categoriesGroupMep = $(selectors.categoriesGroupMep);
    const $categoriesSelected = $(selectors.categoriesSelected);
    const $categoriesGroup = $(selectors.categoriesGroup);

    if (step === 1) {
        $categoriesGroupMep.find(`ul[data-step="${step}"]`).html($this.clone());
        $categoriesGroupMep.find(selectors.categoriesGroupListWr).html("");
    } else {
        $categoriesGroupMep.find(`${selectors.categoriesGroupListWr}[data-step="${step}"]`).html($this.clone()).addClass("active");

        $categoriesGroupMep.find(`${selectors.categoriesGroupListWr}[data-step="${step + 1}"]`).html("");
    }

    if ($(window).width() < 1250) {
        const $categoriesList = $(selectors.categoriesList);

        $categoriesGroupMep.addClass("active").removeClass("display-n_i").nextAll().addClass("display-n_i");

        if (step === 1) {
            $categoriesGroupMep.find(`ul[data-step="${step}"]`).removeClass("display-n_i").addClass("active");
        } else {
            $categoriesSelected
                .find(`${selectors.categoriesGroupListWr}[data-step="${step}"]`)
                .find(`[data-category="${$this.data("category")}"]`)
                .parent()
                .addClass("active");

            $categoriesGroupMep.find(`${selectors.categoriesGroupListWr}[data-step="${step}"]`).removeClass("display-n_i").addClass("active");
        }

        $categoriesList.css("max-height", `calc(100vh - ${80 + step * 40}px)`);
    }

    const $nextCategory = $categoriesGroup.next().find(`${selectors.categoriesGroupListWr}[data-step="${step + 1}"]`);
    $categoriesGroupMep.find(`${selectors.categoriesGroupListWr}[data-step="${step + 1}"]`).html($nextCategory.html());

    $categoriesGroupMep
        .find(`[data-category="${$this.data("category")}"]`)
        .data("callback", "showPrevCategoryMep")
        .data("jsAction", "side-categories:show-prev-category-mep");

    if ($(window).width() < 1250) {
        $categoriesGroupMep.find(`${selectors.categoriesGroupListWr}[data-step="${step + 1}"]`).removeClass("display-n_i");
    }
};

// eslint-disable-next-line consistent-return
const categoriesGroupMainSelect = btn => {
    if (btn.hasClass("active")) {
        return false;
    }

    btn.addClass("active").siblings().removeClass("active");

    const category = btn.data("category");
    const $categoriesGroup = $(selectors.categoriesGroup);

    showLoader($categoriesGroup, "");
    postRequest(`${SITE_URL}categories/ajax_category_group_operation/main_categories_list`, { id_category: category }, "json")
        .then(resp => {
            if (resp.mess_type !== "success") {
                return;
            }

            const categoryList = $(selectors.templateCategoriesGroupList).text();
            const categoryListItem = $(selectors.templateCategoriesGroupListItem).text();
            const $centerCategoryList = $(selectors.centerCategoryList);
            const $lastCategoryList = $(selectors.lastCategoryList);
            const $firstCategoryList = $(selectors.firstCategoryList);
            let htmlCategoryListItem = "";

            resp.categories.forEach(value => {
                let link = `<a
                                class="popup-categories-list__name call-action"
                                data-js-action="side-categories:get-categories-list"
                                data-category="${value.category_id}"
                                href="#"
                            >
                                {{ICON_LEFT}}${value.name}{{ICON_RIGHT}}
                            </a>`;

                if (value.cat_childrens === "") {
                    link = `<a
                                class="popup-categories-list__name"
                                href="${value.url}"
                                target="_blank"
                            >
                                {{ICON_LEFT}}${value.name}{{ICON_RIGHT}}
                            </a>`;
                }

                htmlCategoryListItem += categoryListItem
                    .replace("{{LINK}}", link)
                    .replace(
                        "{{ICON_LEFT}}",
                        value.cat_childrens
                            ? `<span class="popup-categories-list__icon popup-categories-list__icon--left">
                                    <i class="ep-icon ep-icon_arrow-left"></i>
                                </span>`
                            : ""
                    )
                    .replace(
                        "{{ICON_RIGHT}}",
                        value.cat_childrens
                            ? `<span class="popup-categories-list__icon popup-categories-list__icon--right">
                                    <i class="ep-icon ep-icon_arrow-right"></i>
                                </span>`
                            : ""
                    );
            });

            $centerCategoryList.addClass("display-n_i").find("> ul").addClass("display-n_i");
            $lastCategoryList.addClass("display-n_i");
            $firstCategoryList.find("> ul").addClass("display-n_i");
            $firstCategoryList.removeClass("display-n_i").append(categoryList.replace("{{ID}}", category).replace("{{ITEM}}", htmlCategoryListItem));
            if (resp.banner !== undefined) {
                $firstCategoryList.find("> ul").append(bannerTemplate(resp.banner.img, resp.banner.link, resp.banner.name));
            }

            btn.data("jsAction", "side-categories:group-main-select-toggle");
            btn.data("callback", "sideCategoriesGroupMainSelectToggle");

            categoriesGroupSelectMep(1, btn);
        })
        .catch(handleRequestError)
        .finally(() => hideLoader($categoriesGroup));
};

// eslint-disable-next-line consistent-return
const categoriesGroupMainSelectToggle = btn => {
    if (btn.hasClass("active")) {
        return false;
    }

    const category = btn.data("category");
    const $firstCategoryList = $(selectors.firstCategoryList);
    const $centerCategoryList = $(selectors.centerCategoryList);
    const $lastCategoryList = $(selectors.lastCategoryList);
    const $categoriesSelected = $(selectors.categoriesSelected);

    if ($categoriesSelected.hasClass("display-n_i")) {
        $categoriesSelected.removeClass("display-n_i");
    }

    btn.addClass("active").siblings().removeClass("active");
    $firstCategoryList.removeClass("display-n_i").find(`ul[data-list-category="${category}"]`).removeClass("display-n_i").siblings().addClass("display-n_i");

    categoriesGroupSelectMep(1, btn);

    $firstCategoryList.find(".active").removeClass("active");
    $centerCategoryList.addClass("display-n_i").find(".active").removeClass("active").end().find("> ul").addClass("display-n_i");
    $lastCategoryList.addClass("display-n_i").find(".active").removeClass("active");
};

// eslint-disable-next-line consistent-return
const getCategoriesList = btn => {
    const $item = btn.closest("li");

    if ($item.hasClass("active")) {
        return false;
    }

    $item.addClass("active").siblings().removeClass("active");

    const category = btn.data("category");
    const $sideCategoriesWr = $(selectors.sideCategoriesWr);

    showLoader($sideCategoriesWr, "");
    postRequest(`${SITE_URL}categories/ajax_category_group_operation/next_categories_list`, { id_category: category }, "json")
        .then(resp => {
            if (resp.mess_type !== "success") {
                return;
            }

            const categoryList = $(selectors.templateCategoriesGroupList).text();
            const categoryListItem = $(selectors.templateCategoriesGroupListItem).text();
            const $centerCategoryList = $(selectors.centerCategoryList);
            const $lastCategoryList = $(selectors.lastCategoryList);
            let htmlCategoryListItem = "";

            resp.categories.forEach(value => {
                let link = `<a
                                class="popup-categories-list__name"
                                href="${SITE_URL}category/${value.slug}"
                                target="_blank"
                            >
                                {{ICON_LEFT}}${value.name}{{ICON_RIGHT}}
                            </a>`;

                if (value.has_children) {
                    link = `<a
                                class="popup-categories-list__name call-action"
                                data-js-action="side-categories:get-last-categories-list"
                                href="${SITE_URL}category/${value.slug}"
                                target="_blank"
                                data-category="${value.category_id}"
                            >
                                {{ICON_LEFT}}${value.name}{{ICON_RIGHT}}
                            </a>`;
                }

                htmlCategoryListItem += categoryListItem
                    .replace("{{LINK}}", link)
                    .replace(
                        "{{ICON_LEFT}}",
                        value.has_children
                            ? `<span class="popup-categories-list__icon popup-categories-list__icon--left">
                                    <i class="ep-icon ep-icon_arrow-left"></i>
                                </span>`
                            : ""
                    )
                    .replace(
                        "{{ICON_RIGHT}}",
                        value.has_children
                            ? `<span class="popup-categories-list__icon popup-categories-list__icon--right">
                                    <i class="ep-icon ep-icon_arrow-right"></i>
                                </span>`
                            : ""
                    );
            });

            $centerCategoryList.find("> ul").addClass("display-n_i");
            $centerCategoryList.removeClass("display-n_i").append(categoryList.replace("{{ID}}", category).replace("{{ITEM}}", htmlCategoryListItem));
            btn.data("jsAction", "side-categories:get-categories-list-toggle");
            btn.data("callback", "getSideCategoriesListToggle");
            $lastCategoryList.addClass("display-n_i").find(".active").removeClass("active");

            categoriesGroupSelectMep(2, btn);
        })
        .catch(handleRequestError)
        .finally(() => hideLoader($sideCategoriesWr));
};

// eslint-disable-next-line consistent-return
const getCategoriesListToggle = btn => {
    const $item = btn.closest("li");

    if ($item.hasClass("active")) {
        return false;
    }

    const category = btn.data("category");
    const $centerCategoryList = $(selectors.centerCategoryList);
    const $lastCategoryList = $(selectors.lastCategoryList);

    $item.addClass("active").siblings().removeClass("active");
    $centerCategoryList.removeClass("display-n_i").find(`ul[data-list-category="${category}"]`).removeClass("display-n_i").siblings().addClass("display-n_i");
    $centerCategoryList.find(".active").removeClass("active");
    $lastCategoryList.addClass("display-n_i").find(".active").removeClass("active");
};

// eslint-disable-next-line consistent-return
const getLastcategoriesList = btn => {
    const $item = btn.closest("li");

    if ($item.hasClass("active")) {
        return false;
    }

    $item.addClass("active").siblings().removeClass("active");
    const category = btn.data("category");
    const $sideCategoriesWr = $(selectors.sideCategoriesWr);

    showLoader($sideCategoriesWr, "");
    postRequest(`${SITE_URL}categories/ajax_category_group_operation/last_categories_list`, { id_category: category }, "json")
        .then(resp => {
            if (resp.mess_type !== "success") {
                return;
            }

            const categoryList = $(selectors.templateCategoriesGroupList).text();
            const categoryListItem = $(selectors.templateCategoriesGroupListItemToggle).text();
            const categoryListItemSub = $(selectors.templateCategoriesGroupListItem).text();
            const $lastCategoryList = $(selectors.lastCategoryList);
            let htmlCategoryListItem = "";

            resp.categories.forEach(value => {
                let htmlCategoryListItemSub = "";
                let link = `<a
                                class="popup-categories-list__name"
                                href="${SITE_URL}category/${value.slug}"
                                target="_blank"
                            >
                                {{ICON_LEFT}}${value.name}{{ICON_RIGHT}}
                            </a>`;

                if (value.has_children) {
                    link = `<a
                                class="popup-categories-list__name call-action"
                                data-js-action="side-categories:toggle-category-group-list"
                                href="${SITE_URL}category/${value.slug}"
                                target="_blank"
                                data-category="${value.category_id}"
                            >
                                {{ICON_LEFT}}${value.name}{{ICON_RIGHT}}
                            </a>`;
                }

                let htmlCategoryListSub = "";

                if (value.children.length) {
                    value.children.forEach(children => {
                        const linkChildren = `<a
                                                class="popup-categories-list__name"
                                                href="${SITE_URL}category/${children.slug}"
                                                target="_blank"
                                            >
                                                ${children.name}
                                            </a>`;

                        htmlCategoryListItemSub += categoryListItemSub
                            .replace("{{LINK}}", linkChildren)
                            .replace("{{ICON_LEFT}}", "")
                            .replace("{{ICON_RIGHT}}", "");
                    });

                    htmlCategoryListSub = categoryList.replace("{{ITEM}}", htmlCategoryListItemSub);
                }

                htmlCategoryListItem += categoryListItem
                    .replace("{{LINK}}", link)
                    .replace(
                        "{{ICON_LEFT}}",
                        value.has_children
                            ? `<span class="popup-categories-list__icon popup-categories-list__icon--left">
                                    <i class="ep-icon ep-icon_plus-stroke"></i>
                                </span>`
                            : ""
                    )
                    .replace(
                        "{{ICON_RIGHT}}",
                        value.has_children
                            ? `<span class="popup-categories-list__icon popup-categories-list__icon--right">
                                    <i class="ep-icon ep-icon_plus-stroke"></i>
                                </span>`
                            : ""
                    )
                    .replace("{{LIST}}", htmlCategoryListSub);
            });

            $lastCategoryList
                .removeClass("display-n_i")
                .find("> ul")
                .addClass("display-n_i")
                .end()
                .append(categoryList.replace("{{ID}}", category).replace("{{ITEM}}", htmlCategoryListItem));

            categoriesGroupSelectMep(3, btn);

            btn.data("jsAction", "side-categories:get-last-categories-list-toggle");
            btn.data("callback", "getLastSideCategoriesListToggle");
        })
        .catch(handleRequestError)
        .finally(() => hideLoader($sideCategoriesWr));
};

// eslint-disable-next-line consistent-return
const getLastCategoriesListToggle = btn => {
    const $item = btn.closest("li");

    if ($item.hasClass("active")) {
        return false;
    }

    $item.addClass("active").siblings().removeClass("active");

    const category = btn.data("category");
    const $lastCategoryList = $(selectors.lastCategoryList);
    const $categoryList = $lastCategoryList.find(`ul[data-list-category="${category}"]`);

    $lastCategoryList
        .removeClass("display-n_i")
        .find(".active-toggle")
        .removeClass("active-toggle")
        .end()
        .find(".ep-icon_minus-stroke")
        .toggleClass("ep-icon_minus-stroke ep-icon_plus-stroke");

    $lastCategoryList.find("> ul").addClass("display-n_i");
    $categoryList.removeClass("display-n_i");
};

const prevCategory = btn => {
    const $wr = btn.parent();
    const step = $wr.data("step");
    const $categoriesGroupMep = $(selectors.categoriesGroupMep);
    const $categoriesGroup = $(selectors.categoriesGroup);
    const $categoriesSelected = $(selectors.categoriesSelected);

    if (step === 1) {
        $categoriesGroupMep.addClass("display-n_i").find(selectors.categoriesGroupListWr).removeClass("active");
        $categoriesGroup.removeClass("display-n_i").find("li.active").removeClass("active");
    } else {
        const $prevCategory = $categoriesGroup.next().find(`${selectors.categoriesGroupListWr}[data-step="${step}"]`);

        $categoriesGroupMep.find(`[data-step="${step}"]`).removeClass("active").html($prevCategory.html()).find("li.active").removeClass("active");

        if ($(window).width() < 1250) {
            const $categoriesList = $(selectors.categoriesList);

            $categoriesList.css("max-height", `calc(100vh - ${40 + step * 40}px)`);
        }
    }

    $categoriesGroupMep.find(`[data-step="${step}"]`).nextAll(selectors.categoriesGroupListWr).removeClass("active").html("");
    $(`${selectors.categoriesGroupListWr}[data-step="${step}"]`).find("li.active").removeClass("active");
    $categoriesSelected.find(`${selectors.categoriesGroupListWr}[data-step="${step}"]`).nextAll().addClass("display-n_i");
};

const toggleCategoryGroupList = btn => {
    btn.closest("li").toggleClass("active-toggle").find(".ep-icon").toggleClass("ep-icon_plus-stroke ep-icon_minus-stroke");
};

const toggleCategoryGroupMep = () => {
    const $categoriesGroupMep = $(selectors.categoriesGroupMep);
    const $categoriesGroupListWr = $(selectors.categoriesGroupListWr);
    const $categoriesGroupWr = $(selectors.categoriesGroupWr);
    const $firstCategoryList = $(selectors.firstCategoryList);

    onResizeCallback(() => {
        if ($(window).outerWidth() > 1250 && $categoriesGroupMep.is(":visible")) {
            const $categoriesList = $(selectors.categoriesList);
            $categoriesGroupMep.addClass("display-n_i").nextAll().removeClass("display-n_i");
            $categoriesList.css("max-height", "calc(100vh - 5px)");
        } else if ($(window).outerWidth() < 1250 && $categoriesGroupWr.is(":visible")) {
            if ($firstCategoryList.is(":visible")) {
                $categoriesGroupMep.removeClass("display-n_i").children(":first").removeClass("display-n_i").addClass("active");
                $categoriesGroupMep.find($categoriesGroupListWr).removeClass("display-n_i");
                $categoriesGroupMep.nextAll().addClass("display-n_i");
            }
        }
    });
};

const initSideCategories = () => {
    toggleCategoryGroupMep();

    const $categoriesGroupWr = $("#js-sidebar-categories");
    $categoriesGroupWr.on("click", e => {
        if (e.target === e.currentTarget) {
            const $firstCategoryList = $("#js-first-side-category-list");
            const $centerCategoryList = $("#js-center-side-category-list");
            const $lastCategoryList = $("#js-last-side-category-list");
            const $categoriesGroup = $("#js-sidebar-categories-group");
            const $categoriesGroupMep = $("#js-side-categories-group-mep");

            $firstCategoryList.addClass("display-n_i");
            $centerCategoryList.addClass("display-n_i");
            $lastCategoryList.addClass("display-n_i");
            $categoriesGroup.fadeOut(500).removeClass("active").removeClass("display-n_i");
            $categoriesGroupWr.delay(300).fadeOut(200).find(".active").removeClass("active");
            $categoriesGroupMep.addClass("display-n_i");
            $("body").css("overflow", "auto");
        }
    });

    EventHub.on("side-categories:group-main-select", (e, btn) => categoriesGroupMainSelect(btn));
    EventHub.on("side-categories:group-main-select-toggle", (e, btn) => categoriesGroupMainSelectToggle(btn));
    EventHub.on("side-categories:get-categories-list", (e, btn) => getCategoriesList(btn));
    EventHub.on("side-categories:get-last-categories-list", (e, btn) => getLastcategoriesList(btn));
    EventHub.on("side-categories:get-last-categories-list-toggle", (e, btn) => getLastCategoriesListToggle(btn));
    EventHub.on("side-categories:get-categories-list-toggle", (e, btn) => getCategoriesListToggle(btn));
    EventHub.on("side-categories:toggle-category-group-list", (e, btn) => toggleCategoryGroupList(btn));
    EventHub.on("side-categories:show-prev-category-mep", (e, btn) => prevCategory(btn));
};

export default () => {
    const body = $("body");
    const categoriesGroupWr = $("#js-sidebar-categories");

    if (categoriesGroupWr.length) {
        const categoriesGroup = $("#js-sidebar-categories-group");
        const categoriesSelected = $("#js-sidebar-categories-selected");

        if (categoriesSelected.hasClass("display-n_i")) {
            categoriesSelected.removeClass("display-n_i");
        }

        $("body").css("overflow", "hidden");
        categoriesGroupWr.fadeIn();
        categoriesGroup.fadeIn().addClass("active");
    } else {
        showLoader(body, "Load categories...", "absolute", 102);
        postRequest(`${SITE_URL}categories/ajax_category_group_operation/group_categories_list`)
            .then(async resp => {
                await import("@scss/user_pages/sidebar_categories/index.scss");
                $("body > main").append($(resp.html));
            })
            .catch(handleRequestError)
            .finally(() => {
                hideLoader(body);
                $("body").css("overflow", "hidden");
                $("#js-sidebar-categories").fadeIn();
                $("#js-sidebar-categories-group").fadeIn().addClass("active");
                initSideCategories();
            });
    }
};
