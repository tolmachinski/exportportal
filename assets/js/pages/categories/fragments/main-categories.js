import $ from "jquery";
import { SITE_URL } from "@src/common/constants";

import { categoriesGroupSelectBreadcrumb } from "@src/pages/categories/fragments/breadcrumbs";
import { hideLoader, showLoader } from "@src/util/common/loader";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";
import getCookie from "@src/util/cookies/get-cookie";

const cookiesAgeVerification = getCookie("ep_age_verification") || false;
const categoriesGroupWr = $("#js-categories-group-wr");
const centerCategoryList = $("#js-center-category-list");
const lastCategoryList = $("#js-last-category-list");
const templateCategoriesGroupList = $("#js-template-categories-group-list");
const templateCategoriesGroupListItem = $("#js-template-categories-group-list-item");

const getMainCategoryCachedInDOM = (btn, item, category) => {
    categoriesGroupSelectBreadcrumb(2, btn.text(), btn);
    item.addClass("active").siblings().removeClass("active");
    centerCategoryList.removeClass("display-n_i").find(`ul[data-list-category="${category}"]`).removeClass("display-n_i").siblings().addClass("display-n_i");
    centerCategoryList.find(".active").removeClass("active");
    lastCategoryList.addClass("display-n_i").find(".active").removeClass("active");
};

const onMainCategorySelect = async btn => {
    const item = btn.closest("li");
    const category = btn.data("category");

    if (item.hasClass("active")) {
        return;
    }

    if (btn.data("cached")) {
        getMainCategoryCachedInDOM(btn, item, category);

        return;
    }

    categoriesGroupSelectBreadcrumb(2, btn.text(), btn);
    item.addClass("active").siblings().removeClass("active");

    try {
        showLoader(categoriesGroupWr);
        // eslint-disable-next-line camelcase
        const { categories, mess_type: messType, categories_count_product: categoriesCountProduct } = await postRequest(
            `${SITE_URL}categories/ajax_category_group_operation/next_categories_list`,
            {
                // eslint-disable-next-line camelcase
                id_category: category,
            }
        );
        if (messType !== "success") {
            return;
        }

        const categoryList = templateCategoriesGroupList.text();
        const categoryListItem = templateCategoriesGroupListItem.text();
        let htmlCategoryListItem = "";

        categories.forEach(value => {
            let link = `<a class="categories-group-list__name" href="${SITE_URL}category/${value.slug}" target="_blank">${value.name}</a>`;

            if (!cookiesAgeVerification && value.is_restricted === "1") {
                link = `<a class="categories-group-list__name call-action" data-js-action="categories:open-age-verification-modal" data-redirect="${SITE_URL}category/${value.slug}">${value.name}</a>`;
            }

            if (value.has_children) {
                link = `<a class="categories-group-list__name call-action" data-js-action="categories:category-select" href="${SITE_URL}category/${value.slug}" target="_blank" data-category="${value.category_id}">${value.name}</a>`;
            }

            htmlCategoryListItem += categoryListItem
                .replace(new RegExp("{{LINK}}", "g"), link)
                .replace(
                    new RegExp("{{ICON}}", "g"),
                    value.has_children ? `<i class="ep-icon ep-icon_arrow-right"></i>` : `<span class="categories-group-list__noicon"></span>`
                )
                .replace(new RegExp("{{COUNT}}", "g"), categoriesCountProduct[value.category_id] || 0);
        });

        centerCategoryList.find("> ul").addClass("display-n_i");

        centerCategoryList
            .removeClass("display-n_i")
            .append(categoryList.replace(new RegExp("{{ID}}", "g"), category).replace(new RegExp("{{ITEM}}", "g"), htmlCategoryListItem));

        lastCategoryList.addClass("display-n_i").find(".active").removeClass("active");
        btn.data("cached", 1);
    } catch (error) {
        item.removeClass("active");
        handleRequestError(error);
    } finally {
        hideLoader(categoriesGroupWr);
    }
};

export default onMainCategorySelect;
