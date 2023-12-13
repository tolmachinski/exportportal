import $ from "jquery";
import { SITE_URL } from "@src/common/constants";

import { categoriesGroupSelectBreadcrumb } from "@src/pages/categories/fragments/breadcrumbs";
import { hideLoader, showLoader } from "@src/util/common/loader";
import handleRequestError from "@src/util/http/handle-request-error";
import postRequest from "@src/util/http/post-request";
import getCookie from "@src/util/cookies/get-cookie";

const cookiesAgeVerification = getCookie("ep_age_verification") || false;
const categoriesGroupWr = $("#js-categories-group-wr");
const lastCategoryList = $("#js-last-category-list");
const templateCategoriesGroupList = $("#js-template-categories-group-list");
const templateCategoriesGroupListItem = $("#js-template-categories-group-list-item");
const templateCategoriesGroupListItemToggle = $("#js-template-categories-group-list-item-toggle");

const getCategoryCachedInDOM = btn => {
    const item = btn.closest("li");
    const category = btn.data("category");

    if (item.hasClass("active")) {
        return;
    }

    categoriesGroupSelectBreadcrumb(3, btn.text(), btn);

    item.addClass("active").siblings().removeClass("active");
    lastCategoryList
        .removeClass("display-n_i")
        .find(".active-toggle")
        .removeClass("active-toggle")
        .end()
        .find(".ep-icon_minus-stroke")
        .toggleClass("ep-icon_minus-stroke ep-icon_plus-stroke");
    lastCategoryList.find("> ul").addClass("display-n_i");
    lastCategoryList.find(`ul[data-list-category="${category}"]`).removeClass("display-n_i");
};

const onCategorySelect = async btn => {
    const item = btn.closest("li");
    const category = btn.data("category");

    if (item.hasClass("active")) {
        return;
    }

    if (btn.data("cached")) {
        getCategoryCachedInDOM(btn);

        return;
    }

    categoriesGroupSelectBreadcrumb(3, btn.text(), btn);

    item.addClass("active").siblings().removeClass("active");

    try {
        showLoader(categoriesGroupWr);

        const { categories, mess_type: messType } = await postRequest(
            `${SITE_URL}categories/ajax_category_group_operation/last_categories_list`,
            // eslint-disable-next-line camelcase
            { id_category: category },
            "JSON"
        );

        if (messType !== "success") {
            return;
        }

        const categoryList = templateCategoriesGroupList.text();
        const categoryListItem = templateCategoriesGroupListItemToggle.text();
        const categoryListItemSub = templateCategoriesGroupListItem.text();
        let htmlCategoryListItem = "";

        categories.forEach(value => {
            let htmlCategoryListItemSub = "";

            let link = `<a class="categories-group-list__name" href="${SITE_URL}category/${value.slug}" target="_blank">${value.name}</a>`;

            if (cookiesAgeVerification === "0" && value.is_restricted === "1") {
                link = `<a class="categories-group-list__name call-action" data-js-action="categories:open-age-verification-modal" data-redirect="${SITE_URL}category/${value.slug}">${value.name}</a>`;
            }

            if (value.has_children) {
                link = `<a class="categories-group-list__name call-action" data-js-action="categories:subcategory-select" href="${SITE_URL}category/${value.slug}" target="_blank" data-category="${value.category_id}">${value.name}</a>`;
            }

            let htmlCategoryListSub = "";

            if (value.children.length) {
                Object.values(value.children).forEach(children => {
                    const linkChildren = `<a class="categories-group-list__name" href="${SITE_URL}category/${children.slug}" target="_blank">${children.name}</a>`;

                    htmlCategoryListItemSub += categoryListItemSub
                        .replace(new RegExp("{{LINK}}", "g"), linkChildren)
                        .replace(new RegExp("{{ICON}}", "g"), "")
                        .replace(new RegExp("{{COUNT}}", "g"), children.product_count);
                });

                htmlCategoryListSub = categoryList.replace(new RegExp("{{ITEM}}", "g"), htmlCategoryListItemSub);
            }

            htmlCategoryListItem += categoryListItem
                .replace(new RegExp("{{LINK}}", "g"), link)
                .replace(
                    new RegExp("{{ICON}}", "g"),
                    value.has_children ? '<i class="ep-icon ep-icon_plus-stroke"></i>' : '<span class="categories-group-list__noicon"></span>'
                )
                .replace(new RegExp("{{COUNT}}", "g"), value.product_count)
                .replace(new RegExp("{{LIST}}", "g"), htmlCategoryListSub);
        });

        lastCategoryList
            .removeClass("display-n_i")
            .find("> ul")
            .addClass("display-n_i")
            .end()
            .append(categoryList.replace(new RegExp("{{ID}}", "g"), category).replace(new RegExp("{{ITEM}}", "g"), htmlCategoryListItem));

        btn.data("cached", 1);
    } catch (error) {
        handleRequestError(error);
    } finally {
        hideLoader(categoriesGroupWr);
    }
};

export default onCategorySelect;
