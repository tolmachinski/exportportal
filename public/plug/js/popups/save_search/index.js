function newPopupSaveSearchFormCallBack(form){
    var $form = $(form);
    var $wrform = $form.closest('.js-modal-flex');
    var fdata = $form.serialize();

    $.ajax({
        type: 'POST',
        url: __site_url + "save_search/ajax_savesearch_operations/add_search_saved",
        data: fdata,
        dataType: 'JSON',
        beforeSend: function(){
            showLoader($wrform);
            $form.find('button[type=submit]').addClass('disabled');
        },
        success: function(resp){
            hideLoader($wrform);
            systemMessages( resp.message, resp.mess_type );

            if(resp.mess_type == 'success'){
                closeFancyBox();
            }else{
                $form.find('button[type=submit]').removeClass('disabled');
            }
        }
    });
}
