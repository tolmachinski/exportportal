<?php if (!empty($feedbacks)) {?>
	<?php foreach ($feedbacks as $feedback) {?>
		<li
            id="li-feedback-<?php echo $feedback['id_feedback']?>"
            class="product-comments__item"
            <?php echo addQaUniqueIdentifier('global__company-feedbacks__item'); ?>
        >
			<div
                class="product-comments__object"
                <?php echo addQaUniqueIdentifier('global__company-feedbacks__item_title'); ?>
            ><?php echo $feedback['title'];?></div>

			<div class="flex-card">
				<div class="product-comments__img flex-card__fixed image-card2">
					<span class="link">
						<?php if (!$feedback_written) {?>
							<img
                                class="image"
                                src="<?php echo getDisplayImageLink(array('{ID}' => $feedback['id_poster'], '{FILE_NAME}' => $feedback['poster']['user_photo']), 'users.main', array( 'thumb_size' => 0, 'no_image_group' => $feedback['user_group'] )); ?>"
                                alt="<?php echo $feedback['poster']['fname'].' '.$feedback['poster']['lname'];?>"
                                <?php echo addQaUniqueIdentifier('global__company-feedbacks__item_photo'); ?>
                            />
						<?php } else {?>
							<img
                                class="image"
                                src="<?php echo getDisplayImageLink(array('{ID}' => $feedback['id_user'], '{FILE_NAME}' => $feedback['user']['user_photo']), 'users.main', array( 'thumb_size' => 0, 'no_image_group' => $feedback['user_group'] )); ?>"
                                alt="<?php echo $feedback['poster']['fname'].' '.$feedback['user']['lname'];?>"
                                <?php echo addQaUniqueIdentifier('global__company-feedbacks__item_photo'); ?>
                            />
						<?php }?>
					</span>
				</div>

				<div class="product-comments__detail flex-card__float">
					<div class="product-comments__ttl">
						<?php if (!$feedback_written) {?>
							<div class="">
								<div class="product-comments__name pt-20">
									<a
                                        class="link"
                                        href="<?php echo __SITE_URL . 'usr/' . strForURL($feedback['poster']['fname'] . ' ' . $feedback['poster']['lname']) . '-' . $feedback['poster']['idu'];?>"
                                        <?php echo addQaUniqueIdentifier('global__company-feedbacks__item_user-name'); ?>
                                    ><?php echo $feedback['poster']['fname'] . ' ' . $feedback['poster']['lname'];?></a>
								</div>

								<div class="product-comments__country">
									<img
                                        width="24"
                                        height="24"
                                        src="<?php echo getCountryFlag($feedback['poster']['user_country']);?>"
                                        alt="<?php echo $feedback['poster']['user_country']?>"
                                        title="<?php echo $feedback['poster']['user_country']?>"
                                        <?php echo addQaUniqueIdentifier('global__company-feedbacks__item_country-flag'); ?>
                                    >
                                    <span
                                        <?php echo addQaUniqueIdentifier('global__company-feedbacks__item_country-name'); ?>
                                    >
                                        <?php echo $feedback['poster']['user_country'];?>
                                    </span>
								</div>
							</div>
						<?php } else {?>
							<div class="">
								<div class="product-comments__name pt-10">
									<a
                                        class="link"
                                        href="<?php echo __SITE_URL . 'usr/' . strForURL($feedback['user']['fname'] . ' ' . $feedback['user']['lname']) . '-' . $feedback['user']['idu'];?>"
                                        <?php echo addQaUniqueIdentifier('global__company-feedbacks__item_user-name'); ?>
                                    ><?php echo $feedback['user']['fname'] . ' ' . $feedback['user']['lname'];?></a>
								</div>

								<div class="product-comments__country">
									<img
                                        width="24"
                                        height="24"
                                        src="<?php echo getCountryFlag($feedback['user']['user_country']);?>"
                                        alt="<?php echo $feedback['user']['user_country']?>"
                                        title="<?php echo $feedback['user']['user_country']?>"
                                    >
									<span
                                        <?php echo addQaUniqueIdentifier('global__company-feedbacks__item_country-name'); ?>
                                    >
                                        <?php echo $feedback['user']['user_country'];?>
                                    </span>
								</div>
							</div>
						<?php }?>

						<div class="flex-display">
							<div class="product-comments__rating">
								<div class="product-comments__rating-center bg-green" <?php echo addQaUniqueIdentifier('global__rating-circle'); ?>><?php echo $feedback['rating'];?></div>
								<input class="rating-bootstrap" data-filled="ep-icon ep-icon_diamond txt-green fs-12" data-empty="ep-icon ep-icon_diamond txt-gray-light fs-12" type="hidden" name="val" value="<?php echo $feedback['rating'];?>" data-readonly>
							</div>

							<?php if (!empty($feedback['services'])) { ?>
								<?php $one_mark = (106 / 5); ?>
								<div class="product-comments__statistic">
									<?php foreach ($feedback['services'] as $key => $service_rating) {?>
										<div class="product-comments__statistic-item" data-toggle="popover" data-content="<?php echo $service_rating; ?> rating">
											<div class="product-comments__statistic-name"><?php echo $key; ?></div>
											<div class="product-comments__statistic-line">
												<div class="product-comments__statistic-line-bg" <?php echo addQaUniqueIdentifier('global__rating-circle__static-line-bg'); ?> style="width:<?php echo $one_mark * $service_rating; ?>px"></div>
											</div>
										</div>
									<?php }?>
								</div>
							<?php }?>
						</div>
					</div>

					<?php if (!empty($feedback['order_summary'])) {?>
						<div class="flex-display flex-jc--sb pt-20">
							<div class="">
								<span class="txt-gray"><?php echo $feedback['poster_group'] == 'Buyer' ? translate('user_feedback_bought_item') : translate('user_feedback_sold_item');?></span>

								<?php foreach ($feedback['order_summary'] as $key => $item) {?>
									<a
                                        class="product-comments__name-link"
                                        href="<?php echo __SITE_URL . 'items/ordered/' . strForURL($item['title']) . '-' . $item['id_ordered'];?>"
                                        target="_blank"
                                        <?php echo addQaUniqueIdentifier('global__company-feedbacks__item_product-title'); ?>
                                    ><?php echo $item['title'];?></a>
									<?php echo isset($feedback['order_summary'][$key + 1]) ? ',' : '';?>
								<?php }?>
							</div>

							<span
                                class="product-comments__date"
                                itemprop="datePublished"
                                <?php echo addQaUniqueIdentifier('global__company-feedbacks__item_date'); ?>
                            ><?php echo getDateFormat($feedback['create_date'], null, 'M d, Y');?></span>
						</div>
					<?php }?>

					<div
                        class="product-comments__text"
                        <?php echo addQaUniqueIdentifier('global__company-feedbacks__item_text'); ?>
                    ><?php echo $feedback['text'];?></div>

					<?php
						$feedback_actions = array(
							'can_edit' => $feedback['status'] == 'new' && (is_privileged('user', $feedback['id_poster'] ,'leave_feedback') && empty($feedback['reply_text']) || have_right('moderate_content')),
							'can_moderate' => $feedback['status'] == 'new' && have_right('moderate_content'),
							'can_reply' => is_privileged('user', $feedback['id_user'], 'leave_feedback') && empty($feedback['reply_text']),
							'can_report' => logged_in()
						);
					?>

					<?php if (
							$feedback_actions['can_edit']
							|| $feedback_actions['can_moderate']
							|| $feedback_actions['can_reply']
							|| ($feedback_actions['can_report'] && id_session() != $feedback['id_poster'])
						) {?>
						<div class="product-comments__actions">
							<span class="product-comments__left"></span>
							<div class="dropup">
								<a
                                    class="dropdown-toggle"
                                    data-toggle="dropdown"
                                    aria-haspopup="true"
                                    aria-expanded="false"
                                    href="#"
                                    <?php echo addQaUniqueIdentifier('global__company-feedbacks__item_dropdown-btn'); ?>
                                >
									<i class="ep-icon ep-icon_menu-circles"></i>
								</a>

								<div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">
									<?php if ($feedback_actions['can_edit']) {?>
										<a
                                            class="dropdown-item fancybox.ajax fancyboxValidateModal"
                                            data-title="<?php echo translate('edit_user_ep_feedback_btn_tag_title', null, true);?>"
                                            href="<?php echo __SITE_URL . 'feedbacks/popup_forms/edit_user_feedback/' . $feedback['id_feedback']?>"
                                            title="<?php echo translate('edit_user_ep_feedback_btn_tag_title', null, true);?>"
                                            <?php echo addQaUniqueIdentifier('global__company-feedbacks__item_dropdown-menu_edit-btn'); ?>
                                        >
											<i class="ep-icon ep-icon_pencil"></i><span class="txt"><?php echo translate('edit_user_ep_feedback_btn');?></span>
										</a>
									<?php }?>

									<?php if ($feedback_actions['can_moderate']) {?>
										<a class="dropdown-item confirm-dialog" data-callback="moderate_feedback" data-message="<?php echo translate('moderate_user_ep_feedback_confirm_dialog_msg');?>" data-feedback="<?php echo $feedback['id_feedback']?>" href="#" title="<?php echo translate('moderate_user_ep_feedback_btn_tag_title', null, true);?>">
											<i class="ep-icon ep-icon_sheild-ok"></i><span class="txt"><?php echo translate('moderate_user_ep_feedback_btn');?></span>
										</a>
									<?php }?>

									<?php if ($feedback_actions['can_reply']) {?>
										<a
                                            class="dropdown-item btn-reply fancybox.ajax fancyboxValidateModal"
                                            data-title="<?php echo translate('reply_user_ep_feedback_btn_tag_title', null, true);?>"
                                            title="<?php echo translate('reply_user_ep_feedback_btn_tag_title', null, true);?>"
                                            href="<?php echo 'feedbacks/popup_forms/add_reply/' . $feedback['id_feedback'];?>"
                                            <?php echo addQaUniqueIdentifier('global__company-feedbacks__item_dropdown-menu_reply-btn'); ?>
                                        >
											<i class="ep-icon ep-icon_reply-left-empty"></i><span class="txt"><?php echo translate('reply_user_ep_feedback_btn');?></span>
										</a>
									<?php }?>

									<?php if (
											$feedback_actions['can_report']
											&& id_session() != $feedback['id_poster']
										) {?>
										<a
                                            class="dropdown-item fancyboxValidateModal fancybox.ajax"
                                            href="<?php echo __SITE_URL . 'complains/popup_forms/add_complain/feedback/' . $feedback['id_feedback'] . '/' . $feedback['id_poster'] . '/' .  $feedback['id_user'];?>"
                                            data-title="<?php echo translate('report_user_ep_feedback_btn_tag_title', null, true);?>"
                                            title="<?php echo translate('report_user_ep_feedback_btn_tag_title', null, true);?>"
                                            <?php echo addQaUniqueIdentifier('global__company-feedbacks__item_dropdown-menu_report-btn'); ?>
                                        >
											<i class="ep-icon ep-icon_warning-circle-stroke"></i><span class="txt"><?php echo translate('report_user_ep_feedback_btn');?></span>
										</a>
									<?php }?>
								</div>
							</div>
						</div>
					<?php }?>
					<ul class="product-comments product-comments--reply" id="feedback-<?php echo $feedback['id_feedback']?>-reply-block">
						<?php
							if (!empty($feedback['reply_text'])) {
								views()->display('new/users_feedbacks/item_reply_view', array('feedback' => $feedback));
							}
						?>
					</ul>
				</div>
			</div>
		</li>
	<?php }?>
<?php } else {?>
	<li class="w-100pr p-0"><div class="info-alert-b no-feedback"><i class="ep-icon ep-icon_info-stroke"></i><?php echo translate('seller_all_feedback_no_ep_feedback');?></div></li>
<?php }?>
