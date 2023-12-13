<input type="hidden" name="user" value="<?php echo $user;?>"/>
<label class="input-label input-label--required"><?php echo translate('feedback_form_label_for_rate_order_performances');?></label>
<div class="feedback-popup__table">
    <?php foreach($user_services_form as $service){?>
        <div class="feedback-popup__table-item">
            <div class="feedback-popup__table-name"><?php echo $service['s_title']; ?></div>
            <div class="feedback-popup__table-rating">
                <input id="rating-service-<?php echo $service['id_service']; ?>" type="hidden" name="services[<?php echo $service['id_service']; ?>]" value="0">
                <div class="rating-slider" data-service="<?php echo $service['id_service']; ?>"></div>
                <span class="feedback-popup__table-status"></span>
            </div>
        </div>
    <?php }?>
</div>
