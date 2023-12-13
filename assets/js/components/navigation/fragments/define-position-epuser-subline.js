import $ from "jquery";

const definePositionEpuserSubline = (closePosition = 0) => {
    if ($("#js-ep-header-bottom").css("position") !== "fixed") {
        return;
    }

    $("#js-epuser-subline").animate(
        {
            top: closePosition || $("#js-ep-header-bottom").height(),
        },
        220,
        function afterAnimation() {
            if (closePosition) {
                this.style.top = "";
            }
        }
    );
};

export default definePositionEpuserSubline;
