import $ from "jquery";

const afterClose = () => {
    // Check if there are other instances
    const instance = $.fancybox.getInstance();

    if (!instance) {
        $("body").removeClass("fancybox-active-mobile");
    }
};

export default afterClose;
