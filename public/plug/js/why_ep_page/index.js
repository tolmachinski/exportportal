var initReviewsSlider = function () {
    var selector = "#js-reviews-slider";
    var slider = $(selector)
    slider.on("init", function () {
        lazyLoadingInstance(selector + " .js-lazy");
    });
    slider.slick({
        dots: true,
        arrows: true,
        slidesToShow: 3,
        slidesToScroll: 3,
        nextArrow: '<button class="slick-arrow-custom slick-next" aria-label="Next"><i class="ep-icon ep-icon_arrow-right"></i></button>',
        prevArrow: '<button class="slick-arrow-custom slick-prev" aria-label="Previous"><i class="ep-icon ep-icon_arrow-left"></i></button>',
        autoplay: true,
        autoplaySpeed: 5000,
        responsive: [
            {
                breakpoint: 1360,
                settings: {
                    arrows: false,
                },
            },
            {
                breakpoint: 1024,
                settings: {
                    arrows: false,
                    slidesToShow: 2,
                    slidesToScroll: 2,
                    pauseOnFocus: false,
                    pauseOnDotsHover: false,
                },
            },
            {
                breakpoint: 620,
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
};

$(function () {
    initReviewsSlider();

    $("#js-reviews-slider").on('touchcancel touchmove', function() {
        $(this).slick('slickPlay');
    });

    const url = new URL(globalThis.location.href);
    if (url.searchParams.get("openNewReviewPopup") === "1") {
        setTimeout(function() {
            $("#js-open-write-review-popup").trigger("click");
            url.searchParams.delete("openNewReviewPopup");
            globalThis.history.pushState({}, document.title, url.href);
        }, 100);
    }
});
