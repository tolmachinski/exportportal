<div>
	<?php if (empty($feedbacks)) {?>
		<div class="default-alert-b no-feedback"><i class="ep-icon ep-icon_info-stroke"></i><?php echo translate('seller_all_feedback_no_ep_feedback');?></div>
	<?php }?>

	<ul class="js-community-list product-comments">
        <?php
            if (!empty($feedbacks)) {
                $additionals = array('feedbacks' => $feedbacks);

                if (isset($helpful_feedbacks)) {
                    $additionals['helpful_feedbacks'] = $helpful_feedbacks;
                }

                $additionals['feedback_written'] = isset($feedback_written) ? $feedback_written : false;

                if (isset($feedbacks_services)) {
                    $additionals['feedbacks_services'] = $feedbacks_services;
                }

                views()->display('new/users_feedbacks/item_view', $additionals);
            }
        ?>
	</ul>
</div>
