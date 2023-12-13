$(function(){
    $('.js-textcounter-follow-user-message').textcounter({
        countDown: true,
        countDownTextBefore: translate_js({plug: 'textcounter', text: 'count_down_text_before'}),
        countDownTextAfter: translate_js({plug: 'textcounter', text: 'count_down_text_after'})
    });
});

function followersPopupFollowFormCallBack(form, $caller_btn){
    var $form = $(form);
    var $wrform = $form.closest('.js-modal-flex');
    var fdata = $form.serialize();

    $.ajax({
        type: 'POST',
        url: 'followers/ajax_followers_operation/follow_user',
        data: fdata,
        dataType: 'JSON',
        beforeSend: function(){
            showLoader($wrform, translate_js({plug: 'general_i18n', text: 'sending_message_form_loader'}));
            $form.find('button[type=submit]').addClass('disabled');
        },
        success: function(resp){
            hideLoader($wrform);
            systemMessages( resp.message, resp.mess_type );

            if(resp.mess_type == 'success'){
                $caller_btn.removeClass('fancybox.ajax fancyboxValidateModal')
                    .attr('title', translate_js({plug: 'general_i18n', text: 'seller_home_page_sidebar_menu_dropdown_unfollow_user'})).addClass('call-function')
                    .data('user', resp.user)
                    .data('callback', 'unfollow_user')
                    .data('title', translate_js({plug: 'general_i18n', text: 'seller_home_page_sidebar_menu_dropdown_unfollow_user'}))
                    .attr('href', '#');

                if($caller_btn.find('i').length){
                    $caller_btn.find('i').toggleClass('ep-icon_reply-right-empty ep-icon_reply-left-empty');
                    $caller_btn.find('span').html(translate_js({plug: 'general_i18n', text: 'seller_home_page_sidebar_menu_dropdown_unfollow_user'}));
                }else{
                    $caller_btn.toggleClass('ep-icon_reply-right-empty ep-icon_reply-left-empty');
                }

                callFunction('callbackFollowedPopup', resp.user);
                closeFancyBox();
            } else{
                $form.find('button[type=submit]').removeClass('disabled');
            }
        }
    });
}
