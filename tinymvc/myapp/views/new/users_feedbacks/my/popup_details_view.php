<div class="wr-modal-flex">
	<div class="modal-flex__form">
		<div class="modal-flex__content pb-0 mh-500">
			<ul class="product-comments">
                <li class="product-comments__item hidden-b" id="li-feedback-<?php echo $feedback['id_feedback'];?>">
                    <div class="product-comments__object"><?php echo $feedback['title'];?></div>
                    <div class="flex-card">
                        <div class="product-comments__img flex-card__fixed image-card2">
                            <span class="link">
                            <?php if(!$feedback_written){?>
                                <img class="image" src="<?php echo getDisplayImageLink(array('{ID}' => $feedback['id_poster'], '{FILE_NAME}' => $feedback['poster']['user_photo']), 'users.main', array( 'thumb_size' => 0, 'no_image_group' => $feedback['poster']['user_group'] )); ?>" alt="<?php echo $feedback['poster']['fname'] .' '. $feedback['poster']['lname'];?>" />
                            <?php }else{?>
                                <img class="image" src="<?php echo getDisplayImageLink(array('{ID}' => $feedback['id_user'], '{FILE_NAME}' => $feedback['user']['user_photo']), 'users.main', array( 'thumb_size' => 0, 'no_image_group' => $feedback['user']['user_group'] )); ?>" alt="<?php echo $feedback['user']['fname'] .' '. $feedback['user']['lname'];?>" />
                            <?php }?>
                            </span>
                        </div>

                        <div class="product-comments__detail flex-card__float">
                            <div class="product-comments__ttl">
                                <?php if(!$feedback_written){?>
                                    <div class="">
                                        <div class="product-comments__name pt-20">
                                            <a class="link" href="<?php echo __SITE_URL;?>usr/<?php echo strForURL($feedback['poster']['fname'] .' '. $feedback['poster']['lname']).'-'.$feedback['poster']['idu'];?>"><?php echo $feedback['poster']['fname'] .' '. $feedback['poster']['lname'];?></a>
                                        </div>

                                        <div class="product-comments__country">
                                            <img
                                                width="24"
                                                height="24"
                                                src="<?php echo getCountryFlag($feedback['poster']['user_country']);?>"
                                                alt="<?php echo $feedback['poster']['user_country']?>"
                                                title="<?php echo $feedback['poster']['user_country']?>"
                                            >
                                            <?php echo $feedback['poster']['user_country'];?>
                                        </div>
                                    </div>
                                <?php }else{?>
                                    <div class="">
                                        <div class="product-comments__name pt-10">
                                            <a class="link" href="<?php echo __SITE_URL;?>usr/<?php echo strForURL($feedback['user']['fname'] .' '. $feedback['user']['lname']).'-'.$feedback['user']['idu'];?>"><?php echo $feedback['user']['fname'] .' '. $feedback['user']['lname'];?></a>
                                        </div>

                                        <div class="product-comments__country">
                                            <img
                                                width="24"
                                                height="24"
                                                src="<?php echo getCountryFlag($feedback['user']['user_country']);?>"
                                                alt="<?php echo $feedback['user']['user_country']?>"
                                                title="<?php echo $feedback['user']['user_country']?>"
                                            >
                                            <?php echo $feedback['user']['user_country'];?>
                                        </div>
                                    </div>
                                <?php }?>

                                <div class="flex-display">
                                    <div class="product-comments__rating">
                                        <div class="product-comments__rating-center bg-green"><?php echo $feedback['rating'];?></div>
                                        <input class="rating-bootstrap" data-filled="ep-icon ep-icon_diamond txt-green fs-12" data-empty="ep-icon ep-icon_diamond txt-gray-light fs-12" type="hidden" name="val" value="<?php echo $feedback['rating'];?>" data-readonly>
                                    </div>

                                    <?php if(!empty($feedback['services'])){ ?>
                                        <?php $one_mark = (106 / 5); ?>
                                        <div class="product-comments__statistic">
                                        <?php foreach ($feedback['services'] as $key => $service_rating) {?>
                                            <div class="product-comments__statistic-item" data-toggle="popover" data-content="<?php echo $service_rating; ?> rating">
                                                <div class="product-comments__statistic-name"><?php echo $key; ?></div>
                                                <div class="product-comments__statistic-line">
                                                    <div class="product-comments__statistic-line-bg" style="width:<?php echo $one_mark * $service_rating; ?>px"></div>
                                                </div>
                                            </div>
                                        <?php } ?>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>

                            <?php if(!empty($feedback['order_summary'])){?>
                                <div class="flex-display flex-jc--sb pt-20">
                                    <div class="">
                                        <span class="txt-gray">I <?php echo ($feedback['poster_group'] == 'Buyer')? 'bought':'sold';?>:</span>

                                        <?php
                                            $ordered_items = array();
                                            foreach($feedback['order_summary'] as $key => $item){
                                                $ordered_items[] = '<a class="product-comments__name-link" href="'.__SITE_URL.'items/ordered/'.strForURL($item['title']).'-'.$item['id_ordered'].'" target="_blank">'.$item['title'].'</a>';
                                            }

                                            echo implode(', ', $ordered_items);
                                        ?>
                                    </div>

                                    <span class="product-comments__date" itemprop="datePublished"><?php echo formatDate($feedback['create_date'], 'M d, Y');?></span>
                                </div>
                            <?php }?>

                            <div class="product-comments__text"><?php echo $feedback['text'];?></div>
                            <?php
                                $feedback_actions_conditions = array(
                                    'edit' => $feedback['status'] == 'new' && is_privileged('user',$feedback['id_poster'],'leave_feedback') && empty($feedback['reply_text']),
                                    'add_reply' => is_privileged('user',$feedback['id_user'], 'leave_feedback') && empty($feedback['reply_text']),
                                    'report_this' => !is_privileged('user',$feedback['id_poster'],'leave_feedback')
                                );
                            ?>
                            <?php if(
                                    logged_in()
                                    &&
                                        (
                                            $feedback_actions_conditions['edit']
                                            || $feedback_actions_conditions['add_reply']
                                            || ($feedback_actions_conditions['report_this'] && id_session() != $feedback['id_poster'])
                                        )
                                    ){?>
                                <div class="product-comments__actions">
                                    <span class="product-comments__left"></span>

                                    <div class="dropdown">
                                        <a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#">
                                            <i class="ep-icon ep-icon_menu-circles"></i>
                                        </a>

                                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">
                                            <?php if($feedback_actions_conditions['edit']){?>
                                                <a class="dropdown-item fancybox.ajax fancyboxValidateModal" data-title="Edit feedback" href="<?php echo __SITE_URL?>feedbacks/popup_forms/edit_user_feedback/<?php echo $feedback['id_feedback']?>" title="Edit feedback" >
                                                    <i class="ep-icon ep-icon_pencil"></i><span class="txt">Edit</span>
                                                </a>
                                            <?php }?>

                                            <?php if($feedback_actions_conditions['add_reply']){?>
                                                <a class="dropdown-item btn-reply fancybox.ajax fancyboxValidateModal" data-title="Add feedback reply" title="Add feedback reply" href="feedbacks/popup_forms/add_reply/<?php echo $feedback['id_feedback'];?>">
                                                    <i class="ep-icon ep-icon_reply-left-empty"></i><span class="txt">Reply</span>
                                                </a>
                                            <?php }?>

                                            <?php if(
                                                    $feedback_actions_conditions['report_this']
                                                    && id_session() != $feedback['id_poster']
                                                ){?>
                                                <a class="dropdown-item fancyboxValidateModal fancybox.ajax" href="<?php echo __SITE_URL; ?>complains/popup_forms/add_complain/feedback/<?php echo $feedback['id_feedback']; ?>/<?php echo $feedback['id_poster'];?>/<?php echo $feedback['id_user'];?>" data-title="Report this feedback" title="Report this feedback">
                                                    <i class="ep-icon ep-icon_warning-circle-stroke"></i><span class="txt">Report this</span>
                                                </a>
                                            <?php }?>
                                        </div>
                                    </div>
                                </div>
                            <?php }?>
                            <ul class="product-comments product-comments--reply" id="feedback-<?php echo $feedback['id_feedback']?>-reply-block">
                                <?php if(!empty($feedback['reply_text'])){
                                    tmvc::instance()->controller->view->display('new/users_feedbacks/item_reply_view', array('feedback' => $feedback));
                                } ?>
                            </ul>
                        </div>
                    </div>
                </li>
			</ul>
		</div>
   </div>
</div>
<script>
    $(document).ready(function(){
		$('.rating-bootstrap').rating();

		$('.rating-bootstrap').each(function () {
			var $this = $(this);
			ratingBootstrap($this);
		});
    });
</script>
