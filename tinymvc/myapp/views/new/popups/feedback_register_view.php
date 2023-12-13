<form
    class="js-widget-feedback pseudo-modal pseudo-modal--hide-content validateModalLeaveFeedback"
    data-callback="submitModalLeaveFeedback"
    data-type="<?php if(isset($popup_name)){ echo $popup_name; }?>"
>
    <div class="pseudo-modal__title">
        <a
            class="pseudo-modal__btn-close call-action"
            data-js-action="popup-feedback:close"
            data-message="Are you sure you want to close this window?"
            data-type="<?php if(isset($popup_name)){ echo $popup_name; }?>"
            title="Close"
        ><span class="ep-icon ep-icon_remove-stroke"></span></a>

        We want to hear from you!
    </div>
    <div class="pseudo-modal__content">
        <div class="widget-leave-feedback-min">
            <div class="widget-leave-feedback-min__title">Please leave your feedback.</div>
            <a
                class="widget-leave-feedback-min__btn btn btn-primary call-action"
                data-js-action="popup-feedback:leave-feedback"
                href="#"
            >Leave Feedback</a>
        </div>

        <div class="js-widget-hidden pseudo-modal__hidden inputs-40">
            <div class="form-group">
                <label class="input-label">1. How would you rate the information you got about who Export Portal is?</label>
                <div class="rating-smyle">
                    <label class="rating-smyle__item rating-smyle__item--happy">
                        <input class="js-widget-leave-feedback-rate validate[required]" type="radio" name="rate" value="1">
                        <div class="rating-smyle__checkmark"></div>
                    </label>
                    <label class="rating-smyle__item rating-smyle__item--confused">
                        <input class="js-widget-leave-feedback-rate validate[required]" type="radio" name="rate" value="2">
                        <div class="rating-smyle__checkmark"></div>
                    </label>
                    <label class="rating-smyle__item rating-smyle__item--sad">
                        <input class="js-widget-leave-feedback-rate validate[required]" type="radio" name="rate" value="3">
                        <div class="rating-smyle__checkmark"></div>
                    </label>
                </div>
            </div>
            <div class="form-group">
                <label class="input-label">2. Are you a ..?</label>
                <?php
                    $checked_user_type = 'none';
                    if(!logged_in()){
                        $checked_user_type = trim(str_replace('verified', '', session()->__get('user_verification_type')));
                    }else{
                        $checked_user_type = returnTrueUserGroupName();
                    }
                ?>

                <?php if(isset($popup_name) && $popup_name == 'feedback_certification'){?>
                    <div class="widget-leave-feedback__user-list">
                        <div class="widget-leave-feedback__user-list-group">
                            <label class="widget-leave-feedback__user-list-item custom-radio">
                                <input class="js-widget-leave-feedback-user validate[required]" type="radio" name="user_type" value="2"<?php echo ' '.checked($checked_user_type, 'seller');?>>
                                <span class="custom-radio__text">Seller</span>
                            </label>
                        </div>
                        <div class="widget-leave-feedback__user-list-group">
                            <label class="widget-leave-feedback__user-list-item custom-radio">
                                <input class="js-widget-leave-feedback-user validate[required]" type="radio" name="user_type" value="5"<?php echo ' '.checked($checked_user_type, 'manufacturer');?>>
                                <span class="custom-radio__text">Manufacturer</span>
                            </label>
                        </div>
                    </div>
                <?php }else{?>
                    <div class="widget-leave-feedback__user-list">
                        <div class="widget-leave-feedback__user-list-group">
                            <label class="widget-leave-feedback__user-list-item custom-radio">
                                <input class="js-widget-leave-feedback-user validate[required]" type="radio" name="user_type" value="1"<?php echo ' '.checked($checked_user_type, 'buyer');?>>
                                <span class="custom-radio__text">Buyer</span>
                            </label>
                            <label class="widget-leave-feedback__user-list-item custom-radio">
                                <input class="js-widget-leave-feedback-user validate[required]" type="radio" name="user_type" value="2"<?php echo ' '.checked($checked_user_type, 'seller');?>>
                                <span class="custom-radio__text">Seller</span>
                            </label>
                        </div>
                        <div class="widget-leave-feedback__user-list-group">
                            <label class="widget-leave-feedback__user-list-item custom-radio">
                                <input class="js-widget-leave-feedback-user validate[required]" type="radio" name="user_type" value="5"<?php echo ' '.checked($checked_user_type, 'manufacturer');?>>
                                <span class="custom-radio__text">Manufacturer</span>
                            </label>
                            <label class="widget-leave-feedback__user-list-item custom-radio">
                                <input class="js-widget-leave-feedback-user validate[required]" type="radio" name="user_type" value="31"<?php echo ' '.checked($checked_user_type, 'shipper');?>>
                                <span class="custom-radio__text">Freight Forwarder</span>
                            </label>
                        </div>
                    </div>
                <?php }?>
            </div>
            <div class="js-widget-leave-feedback-description form-group display-n">
                <label class="input-label">3. If you didn't get enough information, how would you recommend we improve?</label>
                <textarea
                    class="validate[required, maxSize[500]] widget-leave-feedback__description"
                    data-max="500"
                    name="description"
                    placeholder="Please write your suggestions of what we can improve."
                ></textarea>
            </div>
        </div>
    </div>
    <div class="js-widget-hidden pseudo-modal__footer pseudo-modal__hidden inputs-40">
        <div class="pseudo-modal__footer-right">
            <button class="btn btn-primary" type="submit">Send</button>
        </div>
    </div>
</form>

<?php
    echo dispatchDynamicFragment(
        "popup:feedback_registration",
        [["isLoggedIn" => logged_in()]],
        true
    );
?>
