import $ from "jquery";

const categoriesGroupBreadcrumbs = $("#js-categories-group-breadcrumbs");
const categoriesGroupSelectedTitle = $("#js-categories-group-selected-title");

const onSelectCategoryBreadcrumb = btn => {
    const wrapper = btn.closest("div");
    const step = wrapper.data("step");

    categoriesGroupBreadcrumbs.find(`[data-step="${step}"]`).nextAll().addClass("display-n_i");

    if (step === 1) {
        categoriesGroupBreadcrumbs.find(`[data-step="${step}"]`).addClass("display-n_i");

        categoriesGroupSelectedTitle.text("").addClass("display-n_i");
    }

    $(`.js-wr-category-group[data-step="${step}"]`).removeClass("display-n_i").find(".active").removeClass("active");

    for (let i = step + 1; i < 5; i += 1) {
        $(`.js-wr-category-group[data-step="${i}"]`).addClass("display-n_i").find(".active").removeClass("active").end().find(">ul").addClass("display-n_i");
    }
};

const categoriesGroupSelectBreadcrumb = (step, text, btn) => {
    const target = $("#js-categories-group-breadcrumbs");
    target.find(`[data-step="${step}"]`).removeClass("display-n_i").find(".link").data("js-action", "categories:select-category-breadcrumb");
    target
        .find(`[data-step="${step + 1}"]`)
        .removeClass("display-n_i")
        .find(".link")
        .text(text)
        .end()
        .nextAll()
        .addClass("display-n_i");

    if (window.matchMedia("(max-width: 991px)").matches) {
        btn.closest(".js-wr-category-group").addClass("display-n_i");
    }
};

export { categoriesGroupSelectBreadcrumb };
export default onSelectCategoryBreadcrumb;
