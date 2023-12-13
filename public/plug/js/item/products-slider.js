var isInitProductsSlider = false;
var productsSliderSelector = ".js-promo-items-slider-wr";
var featuredItemsSliderSelector = ".js-featured-items-slider-wr";
var popularProductsSliderSelector = ".js-popular-items-slider-wr";
var latestItemsSliderSelector = ".js-latest-items-slider-wr";
var similarProductsSliderSelector = "#js-similar-products-slider";
var productsSlider = $(productsSliderSelector);
var featuredItemsSlider = $(featuredItemsSliderSelector);
var popularProductsSlider = $(popularProductsSliderSelector);
var latestItemsSlider = $(latestItemsSliderSelector);
var similarProductsSlider = $(similarProductsSliderSelector);
var countSimilarProducts = similarProductsSlider.data("itemsCount");

$(function () {
    if (slickDependencyFromResolution()) {
        initSimilarProductsSlider();
    }

    if ($(window).width() > 1200) {
        initProductsSlider();
    } else {
        initFeaturedProductsSlider();
        initPopularProductsSlider();
        initLatestProductsSlider();
    }
});

jQuery(window).on("resizestop", function () {
    if (slickDependencyFromResolution()) {
        initSimilarProductsSlider();
    }

    if ($(this).width() <= 1200) {
        initFeaturedProductsSlider();
        initPopularProductsSlider();
        initLatestProductsSlider();
    } else {
        initProductsSlider();
    }
});

function slickDependencyFromResolution() {
    const windowWidth = $(globalThis).width();

    if (countSimilarProducts > 4 || (countSimilarProducts > 3 && windowWidth <= 1109)) {
        return true;
    }

    if (similarProductsSlider.hasClass("slick-initialized")) {
        similarProductsSlider.slick("unslick");
    }

    lazyLoadingInstance(`${similarProductsSliderSelector} .js-lazy`);

    return false;
}

function initSimilarProductsSlider() {
    onInitSlickSlider(similarProductsSliderSelector, {
        1920: 4,
        1105: 3,
    });

    var sliderOptions = getProductSlidersOptions(4, 1, countSimilarProducts, false, [
        {
            breakpoint: 1105,
            settings: {
                slidesToShow: 3,
                infinite: true,
            },
        },
    ]);

    similarProductsSlider.not(".slick-initialized").slick(sliderOptions);
}

function initProductsSlider() {
    if (productsSlider.hasClass("slick-initialized")) {
        return;
    }

    onInitSlickSlider(productsSliderSelector, {
        1920: 1,
    });

    var sliderOptions = getProductSlidersOptions(1, 1, 3, !__backstop_test_mode);
    productsSlider.not(".slick-initialized").slick(sliderOptions);
}

function initFeaturedProductsSlider() {
    if (featuredItemsSlider.hasClass("slick-initialized")) {
        return;
    }

    onInitSlickSlider(featuredItemsSliderSelector);

    var sliderOptions = getProductSlidersOptions(3);
    sliderOptions = $.extend({}, sliderOptions, { dots: true });
    featuredItemsSlider.not(".slick-initialized").slick(sliderOptions);
}

function initPopularProductsSlider() {
    if (popularProductsSlider.hasClass("slick-initialized")) {
        return;
    }

    onInitSlickSlider(popularProductsSliderSelector);

    var sliderOptions = getProductSlidersOptions(3);
    sliderOptions = $.extend({}, sliderOptions, { dots: true });
    popularProductsSlider.not(".slick-initialized").slick(sliderOptions);
}

function initLatestProductsSlider() {
    if (latestItemsSlider.hasClass("slick-initialized")) {
        return;
    }

    onInitSlickSlider(latestItemsSliderSelector);

    var sliderOptions = getProductSlidersOptions(3);
    sliderOptions = $.extend({}, sliderOptions, { dots: true });
    latestItemsSlider.not(".slick-initialized").slick(sliderOptions);
}
