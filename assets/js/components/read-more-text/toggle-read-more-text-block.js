import $ from "jquery";

/**
 * It toggles the active class on the parent of the button that was clicked, and then changes the text
 * of the button to "Read less" if the parent has the active class, or "Read more" if it doesn't
 * @param {JQuery} btn
 */
const toggleReadMoreTextBlock = btn => {
    const parent = btn.parent();

    parent.toggleClass("active");
    $(".js-read-more-btn-text").html(parent.hasClass("active") ? "Read less" : "Read more");
};

export default toggleReadMoreTextBlock;
