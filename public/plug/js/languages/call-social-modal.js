function callSocialModal(button) {
    open_modal_dialog({
        btn: button,
        title: $(button).attr("title"),
        content: $("#share-social").html(),
        classes: "modal-share",
        buttons: []
    });
}
