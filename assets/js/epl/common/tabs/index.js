import $ from "jquery";

export default (e, button) => {
    e.preventDefault();

    const tabButtons = $(".js-tab-btn");
    const contents = $(".js-tab-pane");
    const id = button.attr("href");

    if (id) {
        tabButtons.each((i, btn) => {
            $(btn).removeClass("active");
        });

        button.addClass("active");

        contents.each((i, content) => {
            $(content).removeClass("active");
        });

        $(id).addClass("active");
    }
};
