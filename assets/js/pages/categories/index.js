import $ from "jquery";

import autoToggleBannerPictures from "@src/pages/categories/fragments/banner";
import initializeSliders from "@src/pages/categories/fragments/sliders";
import onResizeCallback from "@src/util/dom/on-resize-callback";
import EventHub from "@src/event-hub";

// CSS
import "@scss/user_pages/categories/index.scss";

initializeSliders();

const checkCategoryInLocationGet = async () => {
    // @ts-ignore
    const url = new URL(globalThis.location);
    if (url.searchParams.has("category")) {
        const { default: onGoldenCategorySelect } = await import("@src/pages/categories/fragments/golden-categories");
        onGoldenCategorySelect($(`.js-category-${url.searchParams.get("category")}`));
        globalThis.history.pushState({}, document.title, globalThis.location.pathname);
    }
};

const onSubcategorySelect = (e, btn) => {
    btn.closest("li").toggleClass("active-toggle").find(".ep-icon").toggleClass("ep-icon_plus-stroke ep-icon_minus-stroke");
};

$(() => {
    if (new URL(globalThis.location.href).searchParams.get("keywords")) {
        import("@src/pages/categories/fragments/search-category").then(({ default: searchCategoriesByKeywords }) => searchCategoriesByKeywords());
    }
    // Select golden category
    EventHub.on("categories:golden-category-select", async (e, btn) => {
        const { default: onGoldenCategorySelect } = await import("@src/pages/categories/fragments/golden-categories");
        onGoldenCategorySelect(btn);
    });
    // Select main category
    EventHub.on("categories:main-category-select", async (e, btn) => {
        const { default: onMainCategorySelect } = await import("@src/pages/categories/fragments/main-categories");
        onMainCategorySelect(btn);
    });
    // Select category
    EventHub.on("categories:category-select", async (e, btn) => {
        const { default: onCategorySelect } = await import("@src/pages/categories/fragments/categories");
        onCategorySelect(btn);
    });
    // Select subcategory
    EventHub.on("categories:subcategory-select", onSubcategorySelect);
    // Open verification age popup
    EventHub.on("categories:open-age-verification-modal", async (e, btn) => {
        const { onOpenAgeVerificationModal } = await import("@src/pages/categories/fragments/age-verification");
        onOpenAgeVerificationModal(btn);
    });
    // Select breadcrumb
    EventHub.on("categories:select-category-breadcrumb", async (e, btn) => {
        const { default: onSelectCategoryBreadcrumb } = await import("@src/pages/categories/fragments/breadcrumbs");
        onSelectCategoryBreadcrumb(btn);
    });

    onResizeCallback(() => {
        if (window.matchMedia("(max-width:991px)").matches) {
            const eachGroupCallback = function () {
                if ($(this).find(">ul:visible .active").length || $(this).find(".active").length) {
                    $(this).addClass("display-n_i");
                }
            };
            $(".js-wr-category-group").each(eachGroupCallback);
        } else {
            const categoriesGroupSelectedTitle = $("#js-categories-group-selected-title");
            if (categoriesGroupSelectedTitle.text()) {
                categoriesGroupSelectedTitle.removeClass("display-n_i");
            }
            const eachGroupCallback = function () {
                if ($(this).find(">ul:visible .active").length || $(this).find(".active").length) {
                    $(this).removeClass("display-n_i");
                }
            };
            $(".js-wr-category-group").each(eachGroupCallback);
        }
    }, globalThis);

    checkCategoryInLocationGet();
    autoToggleBannerPictures();
});
