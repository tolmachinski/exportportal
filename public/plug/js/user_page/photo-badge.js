var urlCertified = __group_site_url + 'user/ajax_user_operation/badge_image';

var onRequestCertifiedSuccess = function(){
    location.reload();
}

var onRequestCertifiedError = function(response){
    var $checkbox = $(".js-certified-checkbox");

    if ($checkbox.prop( "checked")) {
        $checkbox.prop( "checked", true );
    } else {
        systemMessages(response.message, response.mess_type);
    }
}

var setCertifiedImage = function(checked){
    postRequest(urlCertified, {checked: checked ? 1 : 0})
        .then(onRequestCertifiedSuccess)
        .catch(onRequestCertifiedError);
}
