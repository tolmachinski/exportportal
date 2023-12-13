import $ from "jquery";

const hideMaxList = function (options) {
    /** @type {JQuery} self */
    const self = this;
    const defaults = {
        max: 5,
        moreText: "Show More",
        lessText: "Show Less",
        moreHTML: '<div class="js-maxlist-more"><button type="button"></button></div>',
        lessHTML: '<div class="js-maxlist-more"><button type="button"></button></div>',
    };
    const optionsInner = $.extend(defaults, options);

    return self.each(function maxList() {
        const list = $(this);
        const mainWr = list.parent();
        const op = optionsInner;
        const totalListItems = list.children().length;

        if (totalListItems > 0 && totalListItems > op.max) {
            list.children().each(function maxlistHidden(index) {
                if (index + 1 > op.max) {
                    $(this).addClass("js-maxlist-hidden").hide(0);
                }
            });

            const btnMoreEl = $(op.moreHTML).attr("data-type", "more");
            const btnLessEl = $(op.lessHTML).attr("data-type", "less").hide();
            list.after(btnMoreEl).after(btnLessEl);

            const btnMore = mainWr.find('.js-maxlist-more[data-type="more"]');
            const btnLess = mainWr.find('.js-maxlist-more[data-type="less"]');
            btnMore.find("button").text(op.moreText);
            btnLess.find("button").text(op.lessText);

            mainWr.on("click", ".js-maxlist-more button", function maxlistMore(e) {
                e.preventDefault();
                const btn = $(this);
                const $listElements = mainWr.find(".js-maxlist-hidden");
                const type = btn.parent().data("type");
                btn.parent().hide();

                if (type === "more") {
                    btnLess.show();
                    $listElements.show();
                } else {
                    btnMore.show();
                    $listElements.hide();
                }
            });
        }
    });
};

export default hideMaxList;
export { hideMaxList };
