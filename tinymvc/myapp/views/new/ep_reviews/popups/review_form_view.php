<div class="write-review inputs-40">
    <form id="js-write-review-form" class="modal-flex__form validateModal" data-callback="submitWriteReviewForm" data-js-action="write-review:submit">
        <?php if (count($userAccounts) > 1) {?>
            <p class="write-review__info"><?php echo translate('reviews_main_paragraph_info'); ?></p>
        <?php }?>
        <div class="modal-flex__content">
            <?php if (count($userAccounts) > 1) {?>
                <p class="write-review__info write-review__info--bold"><?php echo translate("reviews_choose_account"); ?></p>
                <div class="write-review__choose-account">
                    <?php $currentUserId = id_session();?>
                    <?php foreach ($userAccounts as $userAccount) {?>
                        <label class="write-review__account" <?php echo addQaUniqueIdentifier("global__write-review-label-" . $userAccount['group']['gr_alias']->value . "_popup"); ?>>
                            <div class="custom-radio <?php echo $userAccount['idu'] === $currentUserId ? 'selected' : '';?>">
                                <input type="radio" value="<?php echo $userAccount['idu'];?>" name="user" <?php echo $userAccount['idu'] === $currentUserId ? 'checked' : '';?>>
                            </div>
                            <img src="<?php echo getUserAvatar($userAccount['idu'], $userAccount['user_photo'], (int) $userAccount['group']['idgroup'], 0);?>" alt="<?php echo cleanOutput($userAccount['fname'] . ' ' . $userAccount['lname']);?>">
                            <div class="write-review__account-info">
                                <div class="write-review__account-name"><?php echo $userAccount['fname'] . ' ' . $userAccount['lname'];?></div>
                                <div class="write-review__account-type <?php echo is_certified((int) $userAccount['group']['idgroup']) ? 'txt-orange' : 'txt-green';?>"><?php echo $userAccount['group']['gr_name'];?></div>
                                <?php if (isset($companies[$userAccount['idu']])) {?>
                                    <div class="write-review__account-company"><?php echo $companies[$userAccount['idu']]['name_company'];?></div>
                                <?php }?>
                            </div>
                        </label>
                    <?php }?>
                </div>
            <?php } else {?>
                <?php $userAccount = array_shift($userAccounts);?>
                <input type="hidden" value="<?php echo $userAccount['idu'];?>" name="user">
            <?php }?>
            <label class="write-review__info write-review__info--bold" for="write-review-textarea"><?php echo translate("reviews_share_your_thoughts"); ?></label>
            <textarea id="write-review-textarea" class="write-review__textarea js-write-review-textarea js-textcounter-message validate[required, maxSize[250]]" data-max="250" placeholder="<?php echo translate("placehorder_type_your_message"); ?>" name="message" <?php echo addQaUniqueIdentifier("global__write-review-textarea_popup"); ?>></textarea>
        </div>
        <div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-success" type="submit" disabled="disabled" <?php echo addQaUniqueIdentifier("global__write-review-submit_popup"); ?>><?php echo translate("general_modal_button_send_text"); ?></button>
            </div>
        </div>
    </form>
</div>

<?php echo dispatchDynamicFragment("write-review:popup"); ?>

