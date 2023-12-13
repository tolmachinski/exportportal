import $ from "jquery";

import lazyLoadingScriptOnScroll from "@src/common/lazy/lazy-script";

export default () => {
    lazyLoadingScriptOnScroll(
        $(".js-latest-products-slider"),
        async () => {
            const { default: initTestimonialsSlider } = await import("@src/pages/blog/fragments/init_testimonials_slider");
            initTestimonialsSlider();
        },
        "50px"
    );
};
