import $ from "jquery";
import { isMobile } from "@src/util/platform";

const onInit = () => {
    if (isMobile()) {
        $("body").addClass("fancybox-active-mobile");
    }
};

export default onInit;
