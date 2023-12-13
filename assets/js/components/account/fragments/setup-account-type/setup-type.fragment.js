import $ from "jquery";

export default () => {
    // eslint-disable-next-line func-names
    $("#js-buyer-type-check input").on("change", function () {
        // eslint-disable-next-line consistent-this
        const self = $(this);
        const formWrapper = $("#js-buyer-entity-form");

        // eslint-disable-next-line func-names
        setTimeout(function () {
            if (self.val() === "1") {
                formWrapper.show();
            } else {
                formWrapper.hide();
            }
        }, 100);
    });
};
