/**
 * It adds a "read more" button to a text if it's longer than 260 characters
 */
const initReadMoreText = ({ item, content }) => {
    if (content.text().trim().length <= 260) {
        return false;
    }

    item.append(
        `<div class="read-more-text__gradient"></div>
        <button class="read-more-text__btn call-action" data-js-action="b2b-request:read-more-text.toggle"${
            content.attr("atas") ? ` atas="general__read-more-btn"` : ""
        }>
            <span class="read-more-text__btn-txt js-read-more-btn-text">Read more</span><i class="ep-icon ep-icon_arrow-down"></i>
        </button>`
    ).addClass("initialized");
    return true;
};

export default initReadMoreText;
