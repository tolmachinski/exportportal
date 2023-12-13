import $ from "jquery";
import advisorBenefitsSlider from "@src/landings/advisors/fragments/advisors-benefits-slider";
// advisors scss
import "@scss/plug/slick-1-8-1/slick-theme.scss";
import "@scss/plug/slick-1-8-1/slick.scss";
import "@scss/landings/advisors/index_page.scss";

$(() => {
    advisorBenefitsSlider("#js-advisors-benefits-slider");
    advisorBenefitsSlider("#js-advisors-difference-slider");
});
