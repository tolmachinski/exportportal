var blogSlider = false;
var productsSliderIsInit = false;
var productsSlider = null;
var countFeaturedProducts = null;

$(function () {
    var selector = "#js-index-products-slider";
    productsSlider = $(selector);
    productsSlider.on("init", () => lazyLoadingInstance(`${selector} .js-lazy`));

    countFeaturedProducts = productsSlider.data("countItems");

    if ($(window).width() >= 1200) {
        initBlogsHomeSlider();
    }

    if (
        countFeaturedProducts > 4 ||
        (countFeaturedProducts > 3 && $(window).width() <= 991) ||
        (countFeaturedProducts > 2 && $(window).width() <= 660) ||
        (countFeaturedProducts > 1 && $(window).width() <= 574)
    ) {
        initProductsHomeSlider();
    }
});

jQuery(window).on("resizestop", function () {
    if ($(this).width() < 1200) {
        if (blogSlider !== false) {
            $("#blogs-home-slider").slick("unslick");
            blogSlider = false;
        }
    } else {
        if (blogSlider == false) {
            initBlogsHomeSlider();
        }
    }

    if (
        countFeaturedProducts > 4 ||
        (countFeaturedProducts > 3 && $(window).outerWidth() <= 991) ||
        (countFeaturedProducts > 2 && $(window).outerWidth() <= 660) ||
        (countFeaturedProducts > 1 && $(window).outerWidth() <= 574)
    ) {
        if (!productsSliderIsInit) {
            initProductsHomeSlider();
        }
    } else if (productsSliderIsInit) {
        productsSlider.slick("unslick");
        productsSliderIsInit = false;
    }
});

var initBlogsHomeSlider = function () {
    var selector = "#blogs-home-slider";
    $(selector).on("init", function () {
        lazyLoadingInstance(selector + " .js-lazy");
    });
    blogSlider = $(selector).slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        centerMode: true,
        prevArrow: $(".blogs-s-arrows__prev"),
        nextArrow: $(".blogs-s-arrows__next"),
        variableWidth: true,
        focusOnSelect: true,
    });
};

var initProductsHomeSlider = function () {
    var selector = "#js-index-products-slider";
    var slider = $(selector);
    slider.on("init", function() {
        lazyLoadingInstance(selector + " .js-lazy");
    });
    productsSlider = slider.slick({
        dots: true,
        arrows: true,
        slidesToShow: 4,
        slidesToScroll: 4,
        nextArrow: '<button class="slick-arrow-custom slick-next" aria-label="Next"><i class="ep-icon ep-icon_arrow-right"></i></button>',
        prevArrow: '<button class="slick-arrow-custom slick-prev" aria-label="Previous"><i class="ep-icon ep-icon_arrow-left"></i></button>',
        autoplay: true,
        autoplaySpeed: 5000,
        responsive: [
            {
                breakpoint: 1250,
                settings: {
                    arrows: false,
                },
            },
            {
                breakpoint: 992,
                settings: {
                    arrows: false,
                    slidesToShow: 3,
                    slidesToScroll: 3,
                    pauseOnFocus: false,
                    pauseOnDotsHover: false,
                },
            },
            {
                breakpoint: 661,
                settings: {
                    arrows: false,
                    slidesToShow: 2,
                    slidesToScroll: 2,
                    pauseOnFocus: false,
                    pauseOnDotsHover: false,
                },
            },
            {
                breakpoint: 575,
                settings: {
                    arrows: false,
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    adaptiveHeight: true,
                    pauseOnFocus: false,
                    pauseOnDotsHover: false,
                },
            },
        ],
    });
    productsSliderIsInit = true;
};
