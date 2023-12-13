var removeBasketItemPopup = function (obj) {
    const basketElement = $("#header-toggle-basket");

    $.ajax({
        url: "basket/ajax_basket_operation/delete_one",
        type: "POST",
        data: { id: $(obj).data("item") },
        beforeSend: function() {
            showLoader(basketElement);
        },
        dataType: "json",
        success: function(resp) {
            systemMessages(resp.message, resp.mess_type);

            if (resp.mess_type == "success") {
                basketElement.html(resp.basket);

                if (!Number(resp.items_total)) {
                    var circleSign = $(".epuser-line__icons-item--basket .epuser-line__circle-sign");
                    if (circleSign.length) {
                        circleSign.remove();
                    }
                }
            }
        },
        complete: function(resp) {
            hideLoader(basketElement);
        },
    });
};
