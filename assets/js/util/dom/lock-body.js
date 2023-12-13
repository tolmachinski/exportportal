import $ from "jquery";

const lockBody = (remove = false) => {
    const html = $("html");

    if (remove) {
        html.removeClass("lock-body");
        $(".lock-body__margin").removeClass("lock-body__margin");
    } else if (!html.hasClass("lock-body")) {
        html.addClass("lock-body");

        $("*:not(object)")
            .filter(function filterElements() {
                return $(this).css("position") === "fixed";
            })
            .addClass("lock-body__margin");
    }
};

export default lockBody;
