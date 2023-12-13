<?php views()->display('new/users_reviews/reviews_scripts_view');?>
<?php views()->display('new/users_feedbacks/feedback_scripts_view');?>

<script type="text/javascript">
	$(document).ready(function(){
		$('body').on('click', '.wr-all-follower-list .button-more', function(e){
			e.preventDefault();
			var $thisBtn = $(this);
			var type = $thisBtn.data('type');
			var user = $thisBtn.data('user');
			var start = $('.wr-all-follower-list .ppersonal-followers__item').length;

			$.ajax({
				type: 'POST',
				async: false,
				url: "followers/ajax_followers_load/"+type+"/popup_view",
				data: {id : user, start : start},
				dataType: 'JSON',
				beforeSend: function(){
					showLoader('.wr-all-follower-list');
				},
				success: function(resp){
					$('.wr-all-follower-list .ppersonal-followers').append(resp.html);
					hideLoader('.wr-all-follower-list');

					if($('.wr-all-follower-list .ppersonal-followers__item').length == resp.count){
						$thisBtn.fadeOut('slow', function(){$(this).remove()});
					}
				},

			});
		});

		<?php if(logged_in()){?>
			remove_company = function(opener){
				var $this = $(opener);
				$.ajax({
					url: 'directory/ajax_company_operations/remove_company_saved',
					type: 'POST',
					dataType: 'JSON',
					data: {company : $this.data('company')},
					success: function (resp) {
						systemMessages(resp.message, resp.mess_type);
						if(resp.mess_type == 'success'){
							$this.data('callback','add_company').find('span').html('Add seller').end()
								.find('i').toggleClass('ep-icon_minus-circle ep-icon_plus-circle');
						}
					}
				});
			}

			add_company = function(opener){
				var $this = $(opener);
				$.ajax({
					url: 'directory/ajax_company_operations/add_company_saved',
					type: 'POST',
					dataType: 'JSON',
					data: {company : $this.data('company')},
					success: function (resp) {
						systemMessages(resp.message, resp.mess_type);
						if(resp.mess_type == 'success'){
							$this.data('callback','remove_company').find('span').html('Remove seller').end()
								.find('i').toggleClass('ep-icon_plus-circle ep-icon_minus-circle');
						}
					}
				});
			}

			<?php if(is_my($user_main['idu'])){?>
				$(".user-edit-status-form").validationEngine('attach', {
					promptPosition : "topLeft",
					autoPositionUpdate : true
				});
			<?php }?>
		<?php }?>

		$('[data-toggle="popover"]').popover({
			trigger: 'hover'
		});

	});

	$('body').on('click', '.contact-operation', function(e){
		e.preventDefault();
		var $this = $(this);

		$.ajax({
			type: 'POST',
			url: '/contact/ajax_contact_operations/'+($this.data('operation'))+'/'+($this.data('user')),
			dataType: 'JSON',
			success: function(resp){
				if(resp.mess_type == 'success'){
					if($this.data('operation') == 'add'){
						$this.data('operation', 'remove').html('<i class="ep-icon ep-icon_favorite"></i>Favorited');
					}else{
						$this.data('operation', 'add').html('<i class="ep-icon ep-icon_favorite-empty"></i>Favorite');
					}
				}
				systemMessages( resp.message, resp.mess_type );
			}
		});
	});

	var ppersonalPicturesMore = function(btn){
		var $thisBtn = $(btn);

		$thisBtn.closest('.ppersonal-pictures').find('.display-n').fadeIn();
		$thisBtn.remove();
	}

    var loadMoreUserCommunity = function($this){
        var type = $this.data('type');
        var $list = $this.closest('.js-tab-pane').find('.js-community-list');
        var start = $list.find(' > li').length;

        $.ajax({
            type: 'POST',
            async: false,
            url: "user/ajax_more_load/" + type,
            data: {user : <?php echo $user_main['idu'];?>, start : start},
            dataType: 'json',
            beforeSend: function(){
                showLoader($list);
            },
            success: function(resp){
                $($list).append(resp.html);

                if(type == 'feedbacks' || type == 'feedbacks_written' || type == 'reviews'){
                    $('.rating-bootstrap').rating();
                }

                if(type == 'community_answers'){
                    if($('.community-user-list__ansers-item').length >= resp.count){
                        $this.fadeOut('slow', function(){$(this).remove()});
                    }
                }
                else{
                    if($list.find(' > li').length == resp.count){
                        $this.fadeOut('slow', function(){$(this).remove()});
                    }
                }


                hideLoader($list);
            },
        });
    }

    var scrollToCommunityElement = function($this){
        var item = $this.data('href');
        var $tab = $('.nav-tabs a[href="'+item+'"]');

        $tab.tab('show');
        $("html, body").animate({ scrollTop: $tab.offset().top-60 }, "slow");
    }
</script>

<a class="btn btn-primary btn-panel-left fancyboxSidebar fancybox mb-15" data-title="User" href="#main-flex-card__fixed-left">
	<i class="ep-icon ep-icon_items"></i>
	Sidebar
</a>

<div class="ppersonal-user-top">
    <div class="flex-card">
        <div class="flex-card__float">
            <div class="ppersonal-user-status <?php if($user_main['logged'] || is_my($user_main['idu'])){?>txt-green<?php } else { ?>txt-red<?php } ?>">
                <?php if($user_main['logged'] || is_my($user_main['idu'])){?>
                    Online
                <?php } else { ?>
                    Offline
                <?php } ?>
            </div>

            <h1 class="ppersonal-names">
                <span class="ppersonal-names__user" <?php echo addQaUniqueIdentifier('page__user-info__name'); ?>><?php echo $user_main['fname'].' '.$user_main['lname']?></span>

                <?php if(isset($company) && !empty($company)){?>
                    <span class="ppersonal-names__txt">official of</span>
                    <a class="ppersonal-names__company" <?php echo addQaUniqueIdentifier('page__user-info__company-name'); ?> href="<?php echo getCompanyURL($company); ?>">
                        <?php echo $company['name_company'];?>
                    </a>
                <?php }else if(isset($company_buyer) && !empty($company_buyer)){?>
                    <span class="ppersonal-names__txt">official of</span>
                    <span class="ppersonal-names__company" <?php echo addQaUniqueIdentifier('page__user-info__name'); ?>>
                        <?php echo $company_buyer['company_name'];?>
                    </span>
                <?php }else if(isset($company_shipper) && !empty($company_shipper)){?>
                    <span class="ppersonal-names__txt">official of</span>
                    <a class="ppersonal-names__company" <?php echo addQaUniqueIdentifier('page__user-info__name'); ?> href="<?php echo getShipperURL($company_shipper); ?>">
                        <?php echo $company_shipper['co_name'];?>
                    </a>
                <?php }?>
            </h1>

            <ul class="ppersonal-reg-date clearfix">
                <li class="ppersonal-reg-date__item">
                    <div class="ppersonal-reg-date__name">
                        Registration date:
                    </div>
                    <div class="ppersonal-reg-date__desc" <?php echo addQaUniqueIdentifier('page__user-info__registration-date'); ?>>
                        <?php echo formatDate($user_main['registration_date'], 'm/d/Y')?>
                    </div>
                </li>
                <li class="ppersonal-reg-date__item">
                    <span class="ppersonal-reg-date__name">Last activity date:</span>
                    <span class="ppersonal-reg-date__desc" <?php echo addQaUniqueIdentifier('page__user-info__last-activity'); ?>><?php echo formatDate($user_main['last_active'], 'm/d/Y')?></span>
                </li>
            </ul>
        </div>
        <?php if (is_certified((int) $user_main['user_group'])) {?>
            <div class="flex-card__fixed ppersonal-user-is-certified">
                <img <?php echo addQaUniqueIdentifier('page__user-info__certified-image'); ?> src="<?php echo __IMG_URL . 'public/img/groups/user_certified.png" alt="Certified User'?>">
            </div>
        <?php  }?>
    </div>

	<?php if(empty($company) && $user_main['gr_type'] != 'Shipper'){?>
		<?php if(!empty($user_main['showed_status']) || is_my($user_main['idu'])){ ?>
		<div class="ppersonal-status">
			<div class="ppersonal-status__html">
				<div class="clearfix">
					<i class="ep-icon ep-icon_quotes-top"></i>

					<?php if(is_my($user_main['idu'])){?>
						<div class="dropdown pull-right">
							<a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#">
								<i class="ep-icon ep-icon_menu-circles"></i>
							</a>

							<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
								<a class="dropdown-item fancyboxValidateModal fancybox.ajax" data-callback="start_change_status" href="<?php echo __SITE_URL;?>user/popup_forms/change_user_status" title="Edit my status">
									<i class="ep-icon ep-icon_pencil"></i> Edit my status
								</a>
							</div>
						</div>
					<?php }?>
				</div>

				<div class="ppersonal-status__text <?php echo (empty($user_main['showed_status']))?'txt-gray':'';?>"><?php echo (empty($user_main['showed_status']))?'Share your status.':$user_main['showed_status'];?></div>
			</div>
		</div>
		<?php } ?>
	<?php }?>

	<ul class="ppersonal-statistic">
		<?php if($user_main['gr_type'] == 'Buyer'){?>
			<li class="ppersonal-statistic__item">
				<span class="ppersonal-statistic__name">Reviews<br> left:</span>
				<div class="ppersonal-statistic__nr" <?php echo addQaUniqueIdentifier('page__user-info__counter'); ?>>
                    <button
                        class="ppersonal-statistic__btn call-function"
                        data-href="#js-reviews-f-li"
                        data-callback="scrollToCommunityElement"
                        type="button"
                    >
                        <?php echo $user_statistic['item_reviews_wrote']?>
                    </button>
				</div>
			</li>
		<?php }?>
		<?php if($user_main['gr_type'] == 'Seller'){?>
			<li class="ppersonal-statistic__item">
				<span class="ppersonal-statistic__name">Reviews<br> received:</span>
				<div class="ppersonal-statistic__nr" <?php echo addQaUniqueIdentifier('page__user-info__counter'); ?>>
                    <button
                        class="ppersonal-statistic__btn call-function"
                        data-href="#js-reviews-f-li"
                        data-callback="scrollToCommunityElement"
                        type="button"
                    >
                        <?php echo $user_statistic['item_reviews_received']?>
                    </button>
				</div>
			</li>
		<?php }?>
        <?php if($user_main['gr_type'] == 'Buyer' || $user_main['gr_type'] == 'Seller'){?>
		<li class="ppersonal-statistic__item">
			<span class="ppersonal-statistic__name">Feedback<br> received:</span>
			<div class="ppersonal-statistic__nr" <?php echo addQaUniqueIdentifier('page__user-info__counter'); ?>>
                <button
                    class="ppersonal-statistic__btn call-function"
                    data-href="#js-all-user-f-li"
                    data-callback="scrollToCommunityElement"
                    type="button"
                >
                    <?php echo $user_statistic['feedbacks_received'];?>
                </button>
			</div>
		</li>
		<li class="ppersonal-statistic__item">
			<span class="ppersonal-statistic__name">Feedback<br> left:</span>
			<div class="ppersonal-statistic__nr" <?php echo addQaUniqueIdentifier('page__user-info__counter'); ?>>
                <button
                    class="ppersonal-statistic__btn call-function"
                    data-href="#js-all-f-li"
                    data-callback="scrollToCommunityElement"
                    type="button"
                >
                    <?php echo $user_statistic['feedbacks_wrote'];?>
                </button>
			</div>
		</li>
        <?php }?>
		<?php if($user_main['gr_type'] == 'Buyer'){?>
			<li class="ppersonal-statistic__item">
				<span class="ppersonal-statistic__name">Questions<br> asked:</span>
				<div class="ppersonal-statistic__nr" <?php echo addQaUniqueIdentifier('page__user-info__counter'); ?>>
                    <button
                        class="ppersonal-statistic__btn call-function"
                        data-href="#js-questions-f-li"
                        data-callback="scrollToCommunityElement"
                        type="button"
                    >
                        <?php echo $user_statistic['item_questions_wrote'];?>
                    </button>
				</div>
			</li>
		<?php }?>

		<?php if($user_main['gr_type'] == 'Seller'){?>
			<li class="ppersonal-statistic__item">
				<span class="ppersonal-statistic__name">Questions<br> answered:</span>
				<div class="ppersonal-statistic__nr" <?php echo addQaUniqueIdentifier('page__user-info__counter'); ?>>
                    <button
                        class="ppersonal-statistic__btn call-function"
                        data-href="#js-questions-f-li"
                        data-callback="scrollToCommunityElement"
                        type="button"
                    >
                        <?php echo $user_statistic['item_questions_answered'];?>
                    </button>
				</div>
			</li>
        <?php }?>

        <?php if($user_main['gr_type'] == 'Shipper'){?>
			<li class="ppersonal-statistic__item">
				<span class="ppersonal-statistic__name">Asked<br> questions:</span>
				<div class="ppersonal-statistic__nr" <?php echo addQaUniqueIdentifier('page__user-info__counter'); ?>>
                    <button
                        class="ppersonal-statistic__btn call-function"
                        data-href="#js-asked-f-li"
                        data-callback="scrollToCommunityElement"
                        type="button"
                    >
                        <?php echo $user_statistic['ep_questions_wrote'];?>
                    </button>
				</div>
			</li>
			<li class="ppersonal-statistic__item">
				<span class="ppersonal-statistic__name">Answered<br> questions:</span>
				<div class="ppersonal-statistic__nr" <?php echo addQaUniqueIdentifier('page__user-info__counter'); ?>>
                    <button
                        class="ppersonal-statistic__btn call-function"
                        data-href="#js-answered-f-li"
                        data-callback="scrollToCommunityElement"
                        type="button"
                    >
                        <?php echo $count_questions_with_answers;?>
                    </button>
				</div>
			</li>
		<?php }?>
	</ul>
</div>

<?php if(!empty($user_photo) && $user_main['gr_type'] != 'Shipper'){ ?>
    <div class="title-public pt-10">
        <h2 class="title-public__txt">Pictures</h2>

        <?php if(logged_in() && is_my($user_main['idu'])){?>
        <div class="dropdown">
            <a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#">
                <i class="ep-icon ep-icon_menu-circles"></i>
            </a>

            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                <a class="dropdown-item" href="<?php echo __SITE_URL;?>user/photo">
                    <i class="ep-icon ep-icon_pencil"></i> Edit my pictures
                </a>
            </div>
        </div>
        <?php }?>
    </div>

    <ul class="ppersonal-pictures">
        <?php
        $total_photo = count($user_photo);

        foreach($user_photo as $key => $photo){
            $link_img = getImgSrc('users.photos', 'original', array('{ID}' => $user_main['idu'], '{FILE_NAME}' => $photo['name_photo']));
        ?>
            <li
                class="ppersonal-pictures__item
                <?php echo ($key>2)?'display-n':'';?>"
            >
                <a
                    class="link fancyboxGallery"
                    rel="galleryUser"
                    href="<?php echo getDisplayImageLink(array('{ID}' => $user_main['idu'], '{FILE_NAME}' => $photo['name_photo']), 'users.photos');?>"
                    data-title="<?php echo $user_main['fname'].' '.$user_main['lname']?>"
                    title="<?php echo $user_main['fname'].' '.$user_main['lname']?>"
                >
                    <img
                        class="image <?php echo viewPictureType($photo['type_photo'], $link_img);?>"
                        src="<?php echo getDisplayImageLink(array('{ID}' => $user_main['idu'], '{FILE_NAME}' => $photo['name_photo']), 'users.photos', array( 'thumb_size' => 2 ));?>"
                        alt="<?php echo $user_main['fname'].' '.$user_main['lname']?>"
                    />
                </a>
            </li>

            <?php if( ($key == 2) && ($total_photo > 3) ){?>
                <li class="ppersonal-pictures__item call-function" data-callback="ppersonalPicturesMore">
                    <a class="ppersonal-pictures__more" href="#">
                        + <?php echo ($total_photo-3);?>
                        <span>photos</span>
                    </a>
                </li>
            <?php }?>
        <?php }?>
    </ul>
<?php } ?>

<?php if(!empty($user_followers)){ ?>
    <div class="title-public title-public--pt">
        <h2 class="title-public__txt">Followers</h2>
    </div>

    <ul class="ppersonal-followers">
        <?php views()->display('new/followers/follower_item_view', array('followers' => $user_followers, 'type' => 'followers')); ?>

        <?php if(count($user_followers) > 5){?>
            <li class="w-100pr">
                <a class="btn btn-outline-dark btn--more link fancybox.ajax fancybox" href="<?php echo __SITE_URL;?>followers/popup_followers/followers/<?php echo $user_main['idu'];?>" data-title="View followers">
                    View all followers
                </a>
            </li>
        <?php }?>
    </ul>
<?php } ?>

<?php if (!empty($user_followed)){ ?>
    <div class="title-public title-public--pt">
        <h2 class="title-public__txt">Following</h2>
    </div>

    <ul class="ppersonal-followers">
        <?php views()->display('new/followers/follower_item_view', array('followers' => $user_followed)); ?>

        <?php if(count($user_followed) > 5){?>
            <li class="w-100pr">
                <a class="btn btn-outline-dark btn--more link fancybox.ajax fancybox" href="<?php echo __SITE_URL;?>followers/popup_followers/followed/<?php echo $user_main['idu'];?>" title="Followers list">
                    View all following
                </a>
            </li>
        <?php } ?>
    </ul>
<?php } ?>

<?php if($user_main['gr_type'] == 'Buyer' || $user_main['gr_type'] == 'Seller'){?>
    <div class="title-public title-public--pt">
        <h2 class="title-public__txt">Feedback</h2>

        <?php if(logged_in() && !is_my($user_main['idu']) && have_right('leave_feedback') && !empty($user_ordered_for_feedback)){?>
        <div class="dropdown">
            <a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#">
                <i class="ep-icon ep-icon_menu-circles"></i>
            </a>

            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">
                <a class="dropdown-item fancybox.ajax fancyboxValidateModal" data-title="Add feedback" href="<?php echo __SITE_URL;?>feedbacks/popup_forms/add_feedback/user/<?php echo privileged_user_id();?>" title="Add feedback">
                    <i class="ep-icon ep-icon_star"></i>
                    Leave Feedback
                </a>
            </div>
        </div>
        <?php }?>
    </div>

    <ul class="nav nav-tabs nav--borders">
        <li class="nav-item">
            <a class="nav-link active" <?php echo addQaUniqueIdentifier('page__user-info__feedback_received'); ?> href="#js-all-user-f-li" role="tab" aria-controls="title" data-toggle="tab" aria-expanded="false">
                Received  (<span><?php echo $user_statistic['feedbacks_received'];?></span>)
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" <?php echo addQaUniqueIdentifier('page__user-info__feedback_written'); ?> href="#js-all-f-li" role="tab" aria-controls="title" data-toggle="tab" aria-expanded="false">
                Written (<span><?php echo $user_statistic['feedbacks_wrote'];?></span>)
            </a>
        </li>
    </ul>

    <div class="tab-content tab-content--borders pb-30">
        <div class="js-tab-pane tab-pane fade active show" id="js-all-user-f-li">
            <?php views()->display('new/users_feedbacks/list_view', array('feedbacks' => $feedbacks_user, 'helpful_feedbacks' => $helpful_feedbacks, 'feedbacks_services' => $feedbacks_user_services,'feedback_written' => false)); ?>

            <?php if((int)$user_statistic['feedbacks_received'] > 10){?>
                <a
                    class="btn-block btn btn-primary mt-40 call-function"
                    data-callback="loadMoreUserCommunity"
                    data-type="feedbacks"
                    href="#"
                >Show more feedback</a>
            <?php }?>
        </div>
        <div class="js-tab-pane tab-pane fade" id="js-all-f-li">
            <?php views()->display('new/users_feedbacks/list_view', array('feedbacks' => $feedbacks_written_user,'feedback_written' => true, 'helpful_feedbacks' => $helpful_feedbacks_written, 'feedbacks_services' => $feedbacks_written_user_services, 'block_name' => 'written')); ?>

            <?php if((int)$user_statistic['feedbacks_wrote'] > 10){?>
                <a
                    class="btn-block btn btn-primary mt-40 call-function"
                    data-callback="loadMoreUserCommunity"
                    data-type="feedbacks_written"
                    href="#"
                >Show more feedback</a>
            <?php }?>
        </div>
    </div>

    <div class="title-public title-public--pt pb-30">
        <h2 class="title-public__txt">On item</h2>

        <?php if(logged_in() && have_right('buy_item') && !empty($user_ordered_items_for_reviews)){?>
        <div class="dropdown">
            <a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#">
                <i class="ep-icon ep-icon_menu-circles"></i>
            </a>

            <?php if(isset($company) && !empty($company)){?>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">
                <a class="dropdown-item fancybox.ajax fancyboxValidateModal" data-title="Add review" href="<?php echo __SITE_URL;?>reviews/popup_forms/add_review/<?php echo $company['id_user'];?>" title="Add review">
                    <i class="ep-icon ep-icon_star"></i>
                    Post Review
                </a>
            </div>
            <?php }?>
        </div>
        <?php }?>
    </div>

    <ul class="nav nav-tabs nav--borders">
        <li class="nav-item">
            <a class="nav-link active" <?php echo addQaUniqueIdentifier('page__user-info__on-item_reviews'); ?> href="#js-reviews-f-li" aria-controls="js-reviews-f-li" role="tab" data-toggle="tab" aria-expanded="false">
                Reviews (<span><?php echo (($user_main['gr_type'] == 'Buyer')?$user_statistic['item_reviews_wrote']:$user_statistic['item_reviews_received']);?></span>)
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" <?php echo addQaUniqueIdentifier('page__user-info__on-item_questions'); ?> href="#js-questions-f-li" aria-controls="js-questions-f-li" role="tab" data-toggle="tab" aria-expanded="false">
                Questions (<?php echo $user_statistic['item_questions_wrote']?>)
            </a>
        </li>
    </ul>

    <div class="tab-content tab-content--borders">
        <div class="js-tab-pane tab-pane fade active show" id="js-reviews-f-li">
            <?php views()->display('new/users_reviews/list_view'); ?>

            <?php if((int)$user_statistic['item_reviews_wrote'] > 10){?>
                <a
                    class="btn-block btn btn-primary mt-40 call-function"
                    data-callback="loadMoreUserCommunity"
                    data-type="reviews"
                    href="#"
                >Show more reviews</a>
            <?php }?>
        </div>
        <div class="js-tab-pane tab-pane fade" id="js-questions-f-li">
            <?php views()->display('new/items_questions/list_view'); ?>

            <?php if((int)$user_statistic['item_questions_wrote'] > 10){?>
                <a
                    class="btn-block btn btn-primary mt-40 call-function"
                    data-callback="loadMoreUserCommunity"
                    data-type="questions"
                    href="#"
                >Show more questions</a>
            <?php }?>
        </div>
    </div>
<?php }?>

<?php if($user_main['gr_type'] == 'Shipper') {?>
    <div class="title-public title-public--pt">
        <h2 class="title-public__txt">Community help questions</h2>
    </div>

    <ul class="nav nav-tabs nav--borders nav--new">
        <li class="nav-item">
            <a class="nav-link active" href="#js-asked-f-li" aria-controls="js-asked-f-li" role="tab" data-toggle="tab" aria-expanded="false">
                Asked (<?php echo $user_statistic['ep_questions_wrote'];?>)
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#js-answered-f-li" aria-controls="js-answered-f-li" role="tab" data-toggle="tab" aria-expanded="false">
                Answered (<?php echo $count_questions_with_answers?>)
            </a>
        </li>
    </ul>

    <div class="tab-content tab-content--new">
        <div class="js-tab-pane tab-pane fade active show" id="js-asked-f-li">
            <?php
            if(!empty($community_questions)){
                views()->display('new/user/community_help/list_view', array('questions' => $community_questions, 'hide_user_info' => true));
            }else{?>
                <div class="default-alert-b"><i class="ep-icon ep-icon_info-stroke"></i><?php echo translate('seller_questions_no_questions_label');?></div>
            <?php }?>

            <?php if((int)$user_statistic['ep_questions_wrote'] > 10){?>
                <a
                    class="btn-block btn btn-primary mt-40 call-function"
                    data-callback="loadMoreUserCommunity"
                    data-type="community_questions"
                    href="#"
                >Show more questions</a>
            <?php }?>
        </div>
        <div class="js-tab-pane tab-pane fade" id="js-answered-f-li">
            <?php if(!empty($community_questions_answers)){
                views()->display('new/user/community_help/list_view', array('questions' => $community_questions_answers));
            }else{?>
                <div class="default-alert-b"><i class="ep-icon ep-icon_info-stroke"></i>No answers</div>
            <?php }?>

            <?php if((int)$user_statistic['ep_answers_wrote'] > $count_current_answers){?>
                <a
                    class="btn-block btn btn-primary mt-40 call-function"
                    data-callback="loadMoreUserCommunity"
                    data-type="community_answers"
                    href="#"
                >Show more answers</a>
            <?php }?>
        </div>
    </div>
<?php }?>
