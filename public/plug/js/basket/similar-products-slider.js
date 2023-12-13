var countSimilarProducts = null;
var similarProductsSliderSelector = "#js-basket-similar-products-slider";
var similarProductsSlider = null;

$(function () {
    $(".js-user-list-item").on("click", function () {
        var idSeller = $(this).data("seller");

        if (typeof similarList[idSeller] !== undefined) {
            $("#js-similar-container-wr").html(similarList[idSeller]);

            similarProductsSlider = $(similarProductsSliderSelector);
            countSimilarProducts = similarProductsSlider.data("itemsCount");

            if (slickDependencyFromResolution()) {
                initBasketSimilarProductsSlider();
            }
        }
    });
});

jQuery(window).on("resizestop", function () {
    if (slickDependencyFromResolution()) {
        initBasketSimilarProductsSlider();
    }
});

function initBasketSimilarProductsSlider() {
    onInitSlickSlider(similarProductsSliderSelector, {
        1920: 4,
        1417: 3,
        1166: 2,
    });

    var sliderOptions = getProductSlidersOptions(4, 1, countSimilarProducts, false, [
        {
            breakpoint: 1417,
            settings: {
                arrows: false,
                slidesToShow: 3,
            },
        },
        {
            breakpoint: 1166,
            settings: {
                arrows: false,
                slidesToShow: 2,
            },
        },
    ]);
    similarProductsSlider.not(".slick-initialized").slick(sliderOptions);
}

function slickDependencyFromResolution() {
    const windowWidth = $(globalThis).width();

    if (
        countSimilarProducts > 4 ||
        (countSimilarProducts > 3 && windowWidth <= 1417) ||
        (countSimilarProducts > 2 && windowWidth > 992 && windowWidth <= 1166)
    ) {
        return true;
    }

    if (similarProductsSlider.hasClass("slick-initialized")) {
        similarProductsSlider.slick("unslick");
    }

    lazyLoadingInstance(`${similarProductsSliderSelector} .js-lazy`);

    return false;
}
