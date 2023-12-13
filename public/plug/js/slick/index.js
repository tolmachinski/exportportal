var getSwipeCount = function (breakpoints) {
    var keys = Object.keys(breakpoints).sort((a, b) => a - b);
    var swipeCount = Object.values(breakpoints)[keys.length - 1];
    keys.every(key => {
        if (window.matchMedia(`(max-width:${key}px)`).matches) {
            swipeCount = breakpoints[key];
        }

        return !window.matchMedia(`(max-width:${key}px)`).matches;
    });

    return swipeCount;
};

var customSlickButtons = function (slider, slick, breakpoints) {
    if (slider.find("button.slick-arrow-custom").length) {
        return;
    }

    slider.append(`
        <button class="slick-arrow-custom slick-prev slick-arrow js-slick-btn-prev" aria-label="Previous"><i class="ep-icon ep-icon_arrow-left"></i></button>
        <button class="slick-arrow-custom slick-next slick-arrow js-slick-btn-next" aria-label="Next"><i class="ep-icon ep-icon_arrow-right"></i></button>
    `);

    slider.on("click", ".js-slick-btn-prev", function () {
        var { currentSlide, slideCount } = slick;
        var swipeCount = getSwipeCount(breakpoints);
        if (currentSlide - swipeCount < 0) {
            var c = currentSlide - swipeCount;
            slick.goTo(-1, c < -1);
            if (c < -1) {
                slick.goTo(slideCount + c);
            }
        } else {
            slick.goTo(currentSlide - swipeCount);
        }
    });

    slider.on("click", ".js-slick-btn-next", function () {
        slick.goTo(slick.currentSlide + getSwipeCount(breakpoints));
    });
};

var fixGetNavigableIndexesSlick = slick => {
    // eslint-disable-next-line no-param-reassign
    slick.getNavigableIndexes = function getNavigableIndexes() {
        var that = this;
        var breakPoint = 0;
        var counter = 0;
        var indexes = [];
        var max;

        if (that.options.infinite === false) {
            max = that.slideCount;
        } else {
            breakPoint = that.slideCount * -1;
            counter = that.slideCount * -1;
            max = that.slideCount * 2;
        }

        while (breakPoint < max) {
            indexes.push(breakPoint);
            breakPoint = counter + that.options.slidesToScroll;
            counter += that.options.slidesToScroll <= that.options.slidesToShow ? that.options.slidesToScroll : that.options.slidesToShow;
        }

        return indexes;
    };
};

var onInitSlickSlider = (sliderNode, buttonsBreakpoints) => {
    var slider = $(sliderNode);
    slider.on("init", (e, slick) => {
        $(this).removeClass("loading");
        lazyLoadingInstance(`${sliderNode} .js-lazy`);
        fixGetNavigableIndexesSlick(slick);

        if (!$.isEmptyObject(buttonsBreakpoints)) {
            customSlickButtons(slider, slick, buttonsBreakpoints);

            jQuery(window).on("resizestop", function () {
                if ($(this).width() > 991) {
                    customSlickButtons(slider, slick, buttonsBreakpoints);
                }

                if (!slider.hasClass("slick-initialized")) {
                    slider.find(".slick-arrow").remove();
                }
            });
        }
    });
};

var getProductSlidersOptions = function (slidesToShow = 5, slidesToScroll = 1, itemsCount = 12, autoplay = false, breakpoints = []) {
    var defaultBreakPoints = [
        {
            breakpoint: 992,
            settings: {
                slidesToShow: 3,
                slidesToScroll,
                dots: itemsCount > 3,
            },
        },
        {
            breakpoint: 476,
            settings: {
                slidesToShow: 2,
                slidesToScroll,
                rows: itemsCount > 2 ? 2 : 1,
                dots: itemsCount > 4,
            },
        },
    ];

    return {
        slidesToShow,
        slidesToScroll,
        autoplay,
        autoplaySpeed: 5000,
        swipeToSlide: true,
        arrows: false,
        infinite: true,
        dots: false,
        variableWidth: true,
        rows: itemsCount > 12 ? 2 : 1,
        responsive: breakpoints.concat(defaultBreakPoints),
    };
};
