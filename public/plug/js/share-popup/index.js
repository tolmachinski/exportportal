function shareWithFamilyFriends($this) {
    var params = $this.closest(".js-share-with-family-friends");
    var type = $this.data("type");
    var popupUrl = "";
    var popupTitle = "Share with ";
    var popupOptions = "width=550,height=400,0,status=0";
    var socialUrl = params.data("url");
    var socialTitleText = params.data("type") === "item" ? "product" : "company";
    var socialTitle = `Hello! Check out this ${socialTitleText} on Export Portal!`;

    switch (type) {
        case "facebook":
            popupUrl = `https://www.facebook.com/share.php?u=${socialUrl}&quote=${socialTitle}`;
            popupTitle += "facebook";
            break;
        case "twitter":
            popupUrl = `https://twitter.com/intent/tweet?text=${socialTitle}&url=${socialUrl}`;
            popupTitle += "twitter";
            break;
        case "pinterest":
            popupUrl = `https://pinterest.com/pin/create/bookmarklet/?media=${params.data("img")}&url=${socialUrl}&is_video=false&description=${socialTitle}`;
            popupTitle += "pinterest";
            popupOptions = "width=750,height=400,0,status=0";
            break;
        case "linkedin":
            popupUrl = `https://www.linkedin.com/shareArticle?mini=true&url=${socialUrl}&title=${socialTitle}`;
            popupTitle = "linkedin";
            break;
    }

    winPopup(popupUrl, popupTitle, popupOptions);
}

const shareSetStatistic = function ($this) {
    const params = $this.closest(".js-share-with-family-friends");
    const type = params.data("type");
    const id = params.data("id");
    const typeSharing = $this.data("type");

    return postRequest(`${__current_sub_domain_url}user/ajax_user_operation/share_statistic`, { type, id, typeSharing }).then().catch(onRequestError);
};

var callShareWithFamilyFriends = function ($this) {
    if ($this.data("fancybox-href") !== undefined) {
        openFancyboxValidateModal($this.data("fancybox-href"), $this.data("title"));
        bootstrapDialogCloseAll();
    } else {
        shareSetStatistic($this);
        shareWithFamilyFriends($this);
    }
}
