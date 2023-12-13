var downloadGuide = function(btn) {
    var name = $(btn).data("guideName");
    var lang = $(btn).data("lang");
    var group = $(btn).data("group");

    var url = __site_url + "user_guide/get_guides/" + name + "/" + lang + "/" + group;
    var onRequestSuccess = function(data){
        if (data.mess_type === 'success') {
            CustomFileSaver.saveAs(__site_url + "user_guide/download/" + name + "/" + lang + "/" + group);
        } else {
            systemMessages(data.message, data.mess_type);
        }
    };

    postRequest(url)
        .then(onRequestSuccess)
        .catch(onRequestError);
};

var submitGuideForm = function(form) {
    var $form = $(form);
    var btn = $form.find('.js-download-btn');
    var userType = $form.find('.js-ug-select-user_type').val();
    var lang = $form.find('.js-ug-select-lang').val();

    btn.data('group', userType);
    btn.data('lang', lang);

    downloadGuide(btn);
};
