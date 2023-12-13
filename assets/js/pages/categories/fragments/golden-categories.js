import $ from "jquery";
import { SITE_URL } from "@src/common/constants";

import { categoriesGroupSelectBreadcrumb } from "@src/pages/categories/fragments/breadcrumbs";
import { hideLoader, showLoader } from "@src/util/common/loader";
import handleRequestError from "@src/util/http/handle-request-error";
import lazyLoadingInstance from "@src/plugins/lazy/index";
import postRequest from "@src/util/http/post-request";
import getCookie from "@src/util/cookies/get-cookie";

const cookiesAgeVerification = getCookie("ep_age_verification") || false;
const categoriesGroupSelected = $("#js-categories-group-selected");
const categoriesGroupWr = $("#js-categories-group-wr");
const categoriesGroupSelectedTitle = $("#js-categories-group-selected-title");
const firstCategoryList = $("#js-first-category-list");
const centerCategoryList = $("#js-center-category-list");
const lastCategoryList = $("#js-last-category-list");
const templateCategoriesGroupList = $("#js-template-categories-group-list");
const templateCategoriesGroupListItemSimple = $("#js-template-categories-group-list-item-simple");

const getGoldenCategoryCachedInDOM = (btn, category) => {
    const name = btn.find("h2").text();
    categoriesGroupSelectedTitle.text(name);
    categoriesGroupSelectBreadcrumb(1, name, btn);

    btn.addClass("active").siblings().removeClass("active");
    firstCategoryList.removeClass("display-n_i").find(`ul[data-list-category="${category}"]`).removeClass("display-n_i").siblings().addClass("display-n_i");
    firstCategoryList.find(".active").removeClass("active");
    centerCategoryList.addClass("display-n_i").find(".active").removeClass("active").end().find("> ul").addClass("display-n_i");
    lastCategoryList.addClass("display-n_i").find(".active").removeClass("active");
};

const bannerTemplate = (name, link, label) => {
    const labelTemplate = `<span class="label-new">New</span>`;

    return `<li class="categories-group-list__item" atas="categories__select-subcategory">
                <a class="categories-group-list__name" href="${link}" target="_blank">
                    ${name} ${label ? `${labelTemplate}` : ``}
                </a>
            </li>`;
};

const onGoldenCategorySelect = async btn => {
    if (btn.hasClass("active")) {
        return;
    }

    const category = btn.data("category");
    if (btn.data("cached")) {
        getGoldenCategoryCachedInDOM(btn, category);

        return;
    }

    btn.addClass("active").siblings().removeClass("active");
    categoriesGroupSelectBreadcrumb(1, btn.find("h2").text(), btn);

    try {
        showLoader(categoriesGroupWr);
        const { categories, mess_type: messType, banner } = await postRequest(`${SITE_URL}categories/ajax_category_group_operation/main_categories_list`, {
            // eslint-disable-next-line camelcase
            id_category: category,
        });
        categoriesGroupSelected.addClass("active");

        if (messType !== "success") {
            return;
        }

        const categoryList = templateCategoriesGroupList.text();
        const categoryListItem = templateCategoriesGroupListItemSimple.text();
        let htmlCategoryListItem = "";
        categoriesGroupSelectedTitle.text(btn.find("h2").text()).removeClass("display-n_i");
        categories.forEach(value => {
            let link = `<a class="categories-group-list__name call-action" data-js-action="categories:main-category-select" data-category="${value.category_id}">${value.name}</a>`;

            if (!cookiesAgeVerification && value.is_restricted === "1" && !value.cat_childrens) {
                link = `<a class="categories-group-list__name call-action" data-js-action="categories:open-age-verification-modal" data-redirect="${value.url}">${value.name}</a>`;
            }

            if (value.cat_childrens === "") {
                if (!cookiesAgeVerification && value.is_restricted === "1") {
                    link = `<a class="categories-group-list__name call-action" data-js-action="categories:open-age-verification-modal" data-redirect="${value.url}">${value.name}</a>`;
                } else {
                    link = `<a class="categories-group-list__name" href="${value.url}" target="_blank">${value.name}</a>`;
                }
            }

            htmlCategoryListItem += categoryListItem.replace(new RegExp("{{LINK}}", "g"), link);
        });

        centerCategoryList.addClass("display-n_i").find("> ul").addClass("display-n_i");
        lastCategoryList.addClass("display-n_i");
        firstCategoryList.find("> ul").addClass("display-n_i");
        firstCategoryList
            .removeClass("display-n_i")
            .append(categoryList.replace(new RegExp("{{ID}}", "g"), category).replace(new RegExp("{{ITEM}}", "g"), htmlCategoryListItem))
            .find(".categories-group-list:not(.categories-group-list--main)")
            .addClass("categories-group-list--main");

        btn.data("cached", 1);

        if (banner !== undefined) {
            firstCategoryList.find("> ul").append(bannerTemplate(banner.name, banner.link, banner.label));
        }

        lazyLoadingInstance(".js-lazy", { threshhold: "10px" });
    } catch (error) {
        btn.removeClass("active");
        handleRequestError(error);
    } finally {
        hideLoader(categoriesGroupWr);
    }
};

export default onGoldenCategorySelect;
