import $ from "jquery";

const showMarkerUserMepAction = (type, val) => {
    const button = $(".js-mep-user-actions");
    const marker = button.find(".js-epuser-line-circle-sign");
    button.data(type, val);

    if (val && !marker.length) {
        button.find(".js-mep-user-actions-inner").append('<span class="js-epuser-line-circle-sign epuser-line__circle-sign bg-orange"></span>');
    } else if (marker.length) {
        marker.remove();
    }
};

export default showMarkerUserMepAction;
