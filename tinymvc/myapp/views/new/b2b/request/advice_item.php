<?php foreach($advices as $item){?>

	<li class="advices-b2b__item" id="li-advice-<?php echo $item['id_advice'];?>">

		<div class="advices-b2b__img image-card2">
			<span class="link">
				<img class="image" src="<?php echo getDisplayImageLink(array('{ID}' => $item['id_user'], '{FILE_NAME}' => $item['user_photo']), 'users.main', array( 'thumb_size' => 0 ));?>" alt="<?php echo $item['username'];?>">
			</span>
		</div>

		<div class="advices-b2b__text">

			<div class="advices-b2b__text-top">

				<a class="advices-b2b__name" href="<?php echo __SITE_URL.'usr/'.strForURL($item['username']).'-'.$item['id_user'];?>" <?php echo addQaUniqueIdentifier("b2b__advice-name")?>>
					<?php echo $item['username'];?>
				</a>

				<div class="advices-b2b__date" <?php echo addQaUniqueIdentifier("b2b__advice-date")?>><?php echo formatDate($item['date_advice']);?></div>

				<div class="dropdown">

                    <button
                        id="dropdownMenuButton"
                        class="dropdown-toggle"
                        data-toggle="dropdown"
                        aria-haspopup="true"
                        aria-expanded="false"
                    >
                        <i class="ep-icon ep-icon_menu-circles"></i>
                    </button>

                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
						<?php if(logged_in()) { ?>
                            <?php echo !empty($item['btnChat']) ? $item['btnChat'] : ''; ?>

                            <?php if(is_privileged('user',$item['id_user'],'manage_b2b_requests')){ ?>
								<button
                                    class="dropdown-item fancybox.ajax fancyboxValidateModal"
                                    data-title="Edit advice"
                                    title="Edit advice"
                                    data-fancybox-href="<?php echo __SITE_URL . 'b2b/popup_forms/edit_advice/' . $item['id_advice']; ?>"
                                    type="button"
                                >
									<i class="ep-icon ep-icon_pencil"></i><span class="txt">Edit</span>
								</button>
                            <?php } ?>
                            <?php if(have_right('moderate_content') && !$item['moderated']){ ?>
								<button
                                    class="dropdown-item confirm-dialog"
                                    data-callback="moderate_advice"
                                    data-advice="<?php echo $item['id_advice'];?>"
                                    data-message="Are you sure you want to moderate this advice?"
                                    type="button"
                                >
									<i class="ep-icon ep-icon_sheild-ok"></i><span class="txt">Moderate</span>
								</button>
                            <?php } ?>
                        <?php } else { ?>
                            <button
                                class="dropdown-item call-systmess"
                                data-message="<?php echo translate("systmess_error_should_be_logged", null, true); ?>"
                                data-type="error"
                                title="Contact user"
                                type="button"
                            >
                                <i class="ep-icon ep-icon_envelope"></i><span class="txt">Contact user</span>
                            </button>
                        <?php } ?>
                    </div>
                </div>

			</div>

			<div class="advices-b2b__message" <?php echo addQaUniqueIdentifier("b2b__advice-text")?>>
				<?php echo $item['message_advice'];?>
			</div>

            <div class="did-help <?php if(isset($helpful[$item['id_advice']])){?>rate-didhelp<?php }?>">
                <div class="did-help__txt">Did it help?</div>
                <?php
                    $disabledClass = $item['id_user'] == id_session() ? ' disabled' : '';
                    $eventListenerClass = logged_in() ? 'js-didhelp-btn' : 'js-require-logged-systmess';
                    $issetMyHelpfulAdvice = isset($helpful[$item['id_advice']]);

                    $btnCountPlusClass = ($issetMyHelpfulAdvice && $helpful[$item['id_advice']] == 1) ? ' txt-blue2' : '';
                    $btnCountMinusClass = ($issetMyHelpfulAdvice && $helpful[$item['id_advice']] == 0) ? ' txt-blue2' : '';
                ?>
                <span class="i-up didhelp-btn <?php echo $eventListenerClass . $disabledClass;?>"
                    data-item="<?php echo $item['id_advice']?>"
                    data-page="b2b"
                    data-type="advice"
                    data-action="y">
                    <span class="counter-b js-counter-plus"><?php echo $item['count_plus']?></span>
                    <span class="ep-icon ep-icon_arrow-line-up js-arrow-up<?php echo $btnCountPlusClass;?>"></span>
                </span>
                <span class="i-down didhelp-btn <?php echo $eventListenerClass . $disabledClass;?>"
                    data-item="<?php echo $item['id_advice']?>"
                    data-page="b2b"
                    data-type="advice"
                    data-action="n">
                    <span class="counter-b js-counter-minus"><?php echo $item['count_minus']?></span>
                    <span class="ep-icon ep-icon_arrow-line-down js-arrow-down<?php echo $btnCountMinusClass?>"></span>
                </span>
            </div>

		</div>

	</li>
<?php }?>
