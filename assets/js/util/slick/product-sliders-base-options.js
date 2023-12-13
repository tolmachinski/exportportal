/**
 * It returns an object with the options for the Slick slider
 * @param {Object} params - slider params
 * @param {number} [params.slidesToShow=5] - number of slides to show
 * @param {number} [params.slidesToScroll=1] - number of slides to scroll
 * @param {number} [params.itemsCount=12] - number of items in slider
 * @param {boolean} [params.autoplay=false] - enable autoplay
 * @param {Array.<Object>} [params.breakpoints=[]] - slider breakpoints which will be added to defaultBreakpoints
 * @returns An object with slider properties
 */
const getProductSlidersOptions = function ({ slidesToShow = 5, slidesToScroll = 1, itemsCount = 12, autoplay = false, breakpoints = [] } = {}) {
    const defaultBreakPoints = [
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

export default getProductSlidersOptions;
