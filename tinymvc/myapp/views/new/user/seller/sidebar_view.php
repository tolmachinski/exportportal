<script>
$(document).ready(function () {
	<?php if(isset($external_invite_code)){?>
		<?php $code = md5($company['id_company'].'code');

			if($feedback_code == $code){?>
				callOpenExternalFeedback();
		<?php }?>
	<?php }?>

	$('.spersonal-nav').on('click', '.spersonal-nav__name', function(e){
		e.preventDefault();
		$(this).closest('.spersonal-nav__item').find('.spersonal-subnav').slideToggle().end()
			.find('.spersonal-nav__name .ep-icon').toggleClass('ep-icon_arrow-right ep-icon_arrow-down');
	});
});

<?php if(isset($external_invite_code)){?>
function callOpenExternalFeedback(){
	callFunction('openExternalFeedback', intval('<?php echo $company['id_company'];?>'), "<?php echo $feedback_code;?>");
}
<?php }?>

<?php if(!logged_in()){?>
	var openExternalFeedback = function($company, $code){
		$.fancybox({
			href: '<?php echo __SITE_URL;?>external_feedbacks/popup_feedback/'+$code+'/'+$company,
			type : 'ajax',
			padding: 0,
			lang : __site_lang,
			i18n : translate_js_one({plug:'fancybox'}),
			closeBtn : true,
			closeBtnWrapper: '.fancybox-skin .fancybox-title',
			helpers : {
				title: {
					type: 'outside',
					position: 'top'
				},
				overlay : {
					closeClick: false,
					locked: true
				}
			} ,
			beforeLoad : function() {
				this.title = 'Add external feedback';
			},
			beforeClose: function() {
				$('.validateModal').validationEngine('detach');
			},

			ajax: {
				complete: function(jqXHR, textStatus) {
					var $caller_btn = this.caller_btn;

					$(".validateModal").validationEngine('attach', {
						promptPosition : "topLeft:0",
						autoPositionUpdate : true,
						scroll: false,
						onValidationComplete: function(form, status){
							if(status){
								if($(form).data("callback") != undefined)
									window[$(form).data("callback")](form, $caller_btn);
								else
									modalFormCallBack(form, $caller_btn);
							}else{
								systemMessages(translate_js({ plug: 'general_i18n', text: 'validate_error_message' }), 'error');
							}
						}
					});
				}
			}
		});
	}
<?php }?>
</script>

<div class="hide-767">
	<?php if (logged_in() && is_privileged('company', $company['id_company'], 'edit_company')) { ?>
		<div class="clearfix">
			<div class="dropdown pull-right" <?php echo addQaUniqueIdentifier('seller__sidebar_edit_menu'); ?>>
				<a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#">
					<i class="ep-icon ep-icon_menu-circles"></i>
				</a>

				<div class="dropdown-menu">
					<a class="dropdown-item" href="<?php echo __SITE_URL . 'company/edit/' . strForURL($company['name_company']) . '-' . $company['id_company'] ?>">
						<i class="ep-icon ep-icon_pencil"></i><?php echo translate('seller_home_page_sidebar_edit_company_btn');?>
					</a>
				</div>
			</div>
		</div>
	<?php } ?>

	<div class="spersonal-logo">
		<img
			class="image"
            <?php echo addQaUniqueIdentifier('seller__sidebar_company-logo'); ?>
			src="<?php echo $company['logoImageLink']; ?>"
			alt="<?php echo $company['name_company'] . ' Seller'; ?>" />
	</div>

    <div class="spersonal-btns">
        <?php if (logged_in()) { ?>
            <?php echo !empty($btnChat) ? $btnChat : ''; ?>
        <?php } else { ?>
            <button class="btn btn-primary btn-block js-require-logged-systmess" <?php echo addQaUniqueIdentifier('seller__sidebar_contacts_btn'); ?>><span class="txt">Chat now</span></button>
        <?php } ?>
        <a class="btn btn-outline-primary btn-block" href="<?php echo $base_company_url . '/products';?>"><?php echo translate('seller_home_page_sidebar_all_items_btn');?></a>
    </div>

    <h2 class="spersonal-name link-bl" <?php echo addQaUniqueIdentifier('seller__sidebar_company-name'); ?>><?php echo $company['name_company'];?></h2>
</div>

<span class="<?php echo $user_main['logged'] ? 'txt-green' : 'txt-red';?>" <?php echo addQaUniqueIdentifier('global__user-online-status'); ?>>
	<?php echo $user_main['logged'] ? translate('user_logged_status_online') : translate('user_logged_status_offline');?>
</span>

<ul class="spersonal-nav">
	<li class="spersonal-nav__item">
		<div class="spersonal-nav__home">
			<a class="link" href="<?php echo $base_company_url; ?>"><?php echo translate('seller_home_page_sidebar_menu_home');?></a>
		</div>
	</li>
	<li class="spersonal-nav__item">
		<div class="spersonal-nav__name" <?php echo addQaUniqueIdentifier('seller__sidebar_nav_btn_menu'); ?>>
			<a class="link" href="#"><?php echo translate('seller_home_page_sidebar_menu_about');?></a>
			<i class="ep-icon ep-icon_arrow-right"></i>
		</div>

		<ul class="spersonal-subnav">
			<li class="spersonal-subnav__item">
				<a class="link" href="<?php echo $base_company_url . '/about'; ?>"><?php echo translate('seller_home_page_sidebar_menu_about_company', array('{{COMPANY_NAME}}' => $company['name_company'])); ?></a>
			</li>
			<li class="spersonal-subnav__item">
				<a class="link" href="<?php echo $base_company_url . '/feedbacks';?>"><?php echo translate('seller_home_page_sidebar_menu_about_feedback'); ?></a>
            </li>
            <li class="spersonal-subnav__item">
				<a class="link" href="<?php echo $base_company_url . '/contact'; ?>"><?php echo translate('seller_home_page_sidebar_menu_about_contact'); ?></a>
			</li>
		</ul>
	</li>

    <?php if (have_right_or('have_news,have_updates', $user_rights)) { ?>
        <li class="spersonal-nav__item">
            <div class="spersonal-nav__name" <?php echo addQaUniqueIdentifier('seller__sidebar_nav_btn_menu'); ?>>
                <a class="link" href="#"><?php echo translate('seller_home_page_sidebar_menu_posts');?></a>
                <i class="ep-icon ep-icon_arrow-right"></i>
            </div>

            <ul class="spersonal-subnav">
                <?php if (have_right('have_news', $user_rights)) { ?>
                    <li class="spersonal-subnav__item">
                        <a class="link" href="<?php echo $base_company_url . '/news';?>"><?php echo translate('seller_home_page_sidebar_menu_posts_news');?></a>
                    </li>
                <?php } ?>
                <?php if (have_right('have_updates', $user_rights)) { ?>
                    <li class="spersonal-subnav__item">
                        <a
                            class="link"
                            href="<?php echo $base_company_url . '/updates';?>"
                            <?php echo addQaUniqueIdentifier('seller-company__sidebar_nav_posts-updates-btn'); ?>
                        >
                            <?php echo translate('seller_home_page_sidebar_menu_posts_updates');?>
                        </a>
                    </li>
                <?php } ?>
            </ul>
        </li>
    <?php } ?>

    <?php if (have_right_or('have_pictures,have_videos,have_library', $user_rights)) { ?>
        <li class="spersonal-nav__item">
            <div class="spersonal-nav__name" <?php echo addQaUniqueIdentifier('seller__sidebar_nav_btn_menu'); ?>>
                <a class="link" href="#"><?php echo translate('seller_home_page_sidebar_menu_media');?></a>
                <i class="ep-icon ep-icon_arrow-right"></i>
            </div>

            <ul class="spersonal-subnav">
                <?php if (have_right('have_pictures', $user_rights)) { ?>
                    <li class="spersonal-subnav__item">
                        <a class="link" href="<?php echo $base_company_url . '/pictures';?>" <?php echo addQaUniqueIdentifier('page__company-page__sidebar_picture'); ?>><?php echo translate('seller_home_page_sidebar_menu_media_picture');?></a>
                    </li>
                <?php } ?>
                <?php if (have_right('have_videos', $user_rights)) { ?>
                    <li class="spersonal-subnav__item">
                        <a class="link" href="<?php echo $base_company_url . '/videos';?>" <?php echo addQaUniqueIdentifier('seller__sidebar_nav_btn_menu-video'); ?>><?php echo translate('seller_home_page_sidebar_menu_media_video');?></a>
                    </li>
                <?php } ?>
                <?php if (have_right('have_library', $user_rights)) { ?>
                    <li class="spersonal-subnav__item">
                        <a class="link" href="<?php echo $base_company_url . '/library';?>"><?php echo translate('seller_home_page_sidebar_menu_media_library');?></a>
                    </li>
                <?php } ?>
            </ul>
        </li>
    <?php }?>

	<li class="spersonal-nav__item">
		<div class="spersonal-nav__name" <?php echo addQaUniqueIdentifier('seller__sidebar_nav_btn_menu'); ?>>
			<a class="link" href="#"><?php echo translate('seller_home_page_sidebar_menu_products');?></a>
			<i class="ep-icon ep-icon_arrow-right"></i>
		</div>

		<ul class="spersonal-subnav">
			<li class="spersonal-subnav__item flex-display flex-jc--sb">
                <a class="link" href="<?php echo $base_company_url . '/products';?>"><?php echo translate('seller_home_page_sidebar_menu_products_items');?></a>
                <span class="txt-blue2" <?php echo addQaUniqueIdentifier('seller__sidebar_nav_items-counter'); ?>><?php if (!empty($sidebar_items_count)) { echo $sidebar_items_count; }?></span>
			</li>
			<li class="spersonal-subnav__item">
				<a class="link" href="<?php echo $base_company_url . '/reviews';?>"><?php echo translate('seller_home_page_sidebar_menu_products_reviews');?></a>
			</li>
			<li class="spersonal-subnav__item">
				<a class="link" href="<?php echo $base_company_url . '/questions';?>"><?php echo translate('seller_home_page_sidebar_menu_products_questions');?></a>
			</li>
			<!-- <li class="spersonal-subnav__item">
				<a class="link" href="<?php //echo $base_company_url.'/comments' ?>">Comments</a>
			</li> -->
		</ul>
	</li>
	<li class="spersonal-nav__item">
		<div class="spersonal-nav__name" <?php echo addQaUniqueIdentifier('seller__sidebar_nav_btn_menu'); ?>>
			<a class="link" href="#"><?php echo translate('seller_home_page_sidebar_menu_community');?></a>
			<i class="ep-icon ep-icon_arrow-right"></i>
		</div>

		<ul class="spersonal-subnav">
			<li class="spersonal-subnav__item">
				<a class="link" href="<?php echo $base_company_url . '/partners';?>"><?php echo translate('seller_home_page_sidebar_menu_community_partners');?></a>
			</li>
			<li class="spersonal-subnav__item">
				<a class="link" href="<?php echo $base_company_url . '/followers';?>"><?php echo translate('seller_home_page_sidebar_menu_community_followers');?></a>
			</li>
		</ul>
	</li>
</ul>

<div class="dropdown">
	<a class="dropdown-toggle btn btn-light btn-block txt-blue2" <?php echo addQaUniqueIdentifier('seller__sidebar_more_actions_menu'); ?> id="dropdownMenuButton" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
		<?php echo translate('seller_home_page_sidebar_menu_more_actions');?>
		<i class="ep-icon ep-icon_menu-circles pl-10"></i>
	</a>
	<?php $is_logged_in = logged_in();?>
	<div class="dropdown-menu" <?php echo addQaUniqueIdentifier('seller__sidebar_more-actions-dropdown'); ?> aria-labelledby="dropdownMenuButton">
		<?php if ( ! is_my($company['id_user'])) {?>

			<a class="dropdown-item <?php echo $is_logged_in ? 'fancybox.ajax fancyboxValidateModal' : 'js-require-logged-systmess';?>" data-title="<?php echo translate('seller_home_page_sidebar_menu_dropdown_add_feedback', null, true);?>" <?php echo addQaUniqueIdentifier('seller__sidebar_more-actions-dropdown_feedback'); ?> href="<?php echo __SITE_URL . 'feedbacks/popup_forms/add_feedback/user/' . $company['id_user'];?>" title="<?php echo translate('seller_home_page_sidebar_menu_dropdown_add_feedback', null, true);?>">
				<i class="ep-icon ep-icon_star"></i><span class="txt"><?php echo translate('seller_home_page_sidebar_menu_dropdown_add_feedback');?></span>
			</a>
			<?php if ($is_logged_in && in_session('company_saved', $company['id_company'])) {?>
				<a class="dropdown-item call-function" data-callback="remove_company" data-company="<?php echo $company['id_company']; ?>" href="#" <?php echo addQaUniqueIdentifier('seller__sidebar_more-actions-dropdown_favorite'); ?> title="<?php echo translate('seller_home_page_sidebar_menu_dropdown_favorited_tag_title', null, true);?>">
					<i class="ep-icon ep-icon_favorite"></i><span class="txt"><?php echo translate('seller_home_page_sidebar_menu_dropdown_favorited');?></span>
				</a>
			<?php } else {?>
				<a class="dropdown-item <?php echo $is_logged_in ? 'call-function' : 'js-require-logged-systmess';?>" data-callback="add_company" data-company="<?php echo $company['id_company']; ?>" <?php echo addQaUniqueIdentifier('seller__sidebar_more-actions-dropdown_favorite'); ?> href="#" title="<?php echo translate('seller_home_page_sidebar_menu_dropdown_favorite_tag_title', null, true);?>">
					<i class="ep-icon ep-icon_favorite-empty"></i><span class="txt"><?php echo translate('seller_home_page_sidebar_menu_dropdown_favorite');?></span>
				</a>
			<?php }?>

			<?php if ($is_logged_in && in_session('followed', $company['id_user'])) {?>
				<a class="dropdown-item call-function follow-<?php echo $company['id_user'];?>" data-user="<?php echo $company['id_user'];?>" data-callback="unfollow_user" title="<?php echo translate('seller_home_page_sidebar_menu_dropdown_unfollow_user', null, true);?>" href="#">
					<i class="ep-icon ep-icon_follow"></i><span class="txt"><?php echo translate('seller_home_page_sidebar_menu_dropdown_unfollow_user');?></span>
				</a>
			<?php } else {?>
				<a class="dropdown-item <?php echo $is_logged_in ? 'fancybox.ajax fancyboxValidateModal' : 'js-require-logged-systmess';?> follow-<?php echo $company['id_user'];?>" data-title="<?php echo translate('seller_home_page_sidebar_menu_dropdown_follow_user', null, true);?>" <?php echo addQaUniqueIdentifier('seller__sidebar_more-actions-dropdown_follow'); ?> href="<?php echo __SITE_URL . 'followers/popup_followers/follow_user/' . $company['id_user'];?>"  title="<?php echo translate('seller_home_page_sidebar_menu_dropdown_follow_user', null, true);?>">
					<i class="ep-icon ep-icon_follow"></i><span class="txt"><?php echo translate('seller_home_page_sidebar_menu_dropdown_follow_user');?></span>
				</a>
			<?php }?>
		<?php }?>

        <button
            class="dropdown-item call-function call-action"
            title="<?php echo translate('seller_home_page_sidebar_menu_dropdown_email_company_tag_title', null, true);?>"
            data-callback="userSharePopup"
            data-js-action="user:share-popup"
            data-type="company"
            data-item="<?php echo $company['id_company'];?>"
            type="button"
            <?php echo addQaUniqueIdentifier("seller__sidebar_more-actions-dropdown_share"); ?>
        >
            <i class="ep-icon ep-icon_share-stroke3"></i> <?php echo translate('seller_home_page_sidebar_menu_dropdown_share_company');?>
        </button>

		<a class="dropdown-item <?php echo $is_logged_in ? 'fancybox.ajax fancyboxValidateModal' : 'js-require-logged-systmess';?>" <?php echo addQaUniqueIdentifier('seller__sidebar_more-actions-dropdown_report'); ?> href="<?php echo __SITE_URL . 'complains/popup_forms/add_complain/company/' . $company['id_company'];?>/<?php echo $company['id_user'];?>/<?php echo $company['id_company'];?>" data-title="<?php echo translate('seller_home_page_sidebar_menu_dropdown_report_company_tag_title', null, true);?>" title="<?php echo translate('seller_home_page_sidebar_menu_dropdown_report_company_tag_title', null, true);?>">
			<i class="ep-icon ep-icon_warning-circle-stroke"></i><span class="txt"><?php echo translate('seller_home_page_sidebar_menu_dropdown_report_company');?></span>
		</a>

		<?php if (isset($external_invite_code)) {?>
			<a class="dropdown-item fancybox.ajax fancyboxValidateModal" data-external-type="feedback" data-title="<?php echo translate('seller_home_page_sidebar_menu_dropdown_leave_feedback', null, true);?>" href="<?php echo __SITE_URL . 'external_feedbacks/popup_feedback/' . $external_invite_code . '/' . $company['id_company'] . '?type=feedback';?>">
				<i class="ep-icon ep-icon_comment-stroke"></i><span class="txt"><?php echo translate('seller_home_page_sidebar_menu_dropdown_leave_feedback');?></span>
			</a>

			<a class="dropdown-item fancybox.ajax fancyboxValidateModal" data-external-type="review" data-title="<?php echo translate('seller_home_page_sidebar_menu_dropdown_leave_review', null, true);?>" href="<?php echo __SITE_URL . 'external_feedbacks/popup_feedback/' . $external_invite_code . '/' . $company['id_company'] . '?type=review';?>">
				<i class="ep-icon ep-icon_comment-stroke"></i><span class="txt"><?php echo translate('seller_home_page_sidebar_menu_dropdown_leave_review');?></span>
			</a>

			<script>
				$(function() { $('a[data-external-type="<?php echo $external_invite_type; ?>"]').click(); });
			</script>
		<?php }?>
	</div>
</div>

<?php if ($current_page == 'library') {?>
	<?php if ( ! empty($keywords)) {?>
		<h3 class="minfo-sidebar-ttl mt-50">
			<span class="minfo-sidebar-ttl__txt"><?php echo translate('active_filters_block_title');?></span>
		</h3>
		<div class="minfo-sidebar-box">
			<div class="minfo-sidebar-box__desc">
				<ul class="minfo-sidebar-params">
					<li class="minfo-sidebar-params__item">
						<div class="minfo-sidebar-params__ttl">
							<div class="minfo-sidebar-params__name"><?php echo translate('active_filters_keywords_label');?></div>
						</div>
						<ul class="minfo-sidebar-params__sub">
							<li class="minfo-sidebar-params__sub-item">
								<div class="minfo-sidebar-params__sub-ttl"><?php echo $keywords;?></div>
								<a class="minfo-sidebar-params__sub-close ep-icon ep-icon_remove-stroke" href="<?php echo $links_tpl_reset_keywords;?>"></a>
							</li>
						</ul>
					</li>
					<li>
						<a class="btn btn-light btn-block txt-blue2" href="<?php echo $reset_all_filters_link;?>"><?php echo translate('clear_all_filters_btn');?></a>
					</li>
				</ul>
			</div>
		</div>
	<?php }?>

	<h3 class="minfo-sidebar-ttl mt-50">
		<span class="minfo-sidebar-ttl__txt"><?php echo translate('seller_home_page_sidebar_search_title');?></span>
	</h3>

	<div class="minfo-sidebar-box">
		<div class="minfo-sidebar-box__desc">
			<form class="minfo-form mb-0" action="<?php echo $search_form_link;?>" method="GET">
				<input class="minfo-form__input2" type="text" name="keywords" maxlength="50" value="<?php echo isset($keywords) ? $keywords : '';?>" placeholder="<?php echo translate('seller_home_page_sidebar_keywords_placeholder', null, true);?>">
				<button class="btn btn-dark btn-block minfo-form__btn2" type="submit"><?php echo translate('seller_home_page_sidebar_search_button');?></button>
			</form>
		</div>
	</div>
<?php }?>

<?php if ($current_page == 'products') {?>
	<?php if (!empty($keywords) || !empty($current_category)){?>
		<h3 class="minfo-sidebar-ttl mt-50">
			<span class="minfo-sidebar-ttl__txt"><?php echo translate('active_filters_block_title');?></span>
		</h3>
		<div class="minfo-sidebar-box">
			<div class="minfo-sidebar-box__desc">
				<ul class="minfo-sidebar-params">
					<?php if (!empty($keywords)) {?>
						<li class="minfo-sidebar-params__item">
							<div class="minfo-sidebar-params__ttl">
								<div class="minfo-sidebar-params__name"><?php echo translate('active_filters_keywords_label');?></div>
							</div>
							<ul class="minfo-sidebar-params__sub">
								<li class="minfo-sidebar-params__sub-item">
									<div class="minfo-sidebar-params__sub-ttl"><?php echo $keywords;?></div>
									<a class="minfo-sidebar-params__sub-close ep-icon ep-icon_remove-stroke" href="<?php echo $links_tpl_reset_keywords;?>"></a>
								</li>
							</ul>
						</li>
					<?php }?>

					<?php if (!empty($current_category) && !empty($cat_crumbs)) {?>
						<?php $main_category = array_shift($cat_crumbs);?>

						<li class="minfo-sidebar-params__item">
							<div class="minfo-sidebar-params__ttl">
								<div class="minfo-sidebar-params__name"><?php echo translate('active_filters_category_label');?></div>
							</div>
							<ul class="minfo-sidebar-params__sub">
								<li class="minfo-sidebar-params__sub-item">
									<div class="minfo-sidebar-params__sub-ttl"><?php echo cleanOutput($main_category['title']);?></div>
									<a class="minfo-sidebar-params__sub-close ep-icon ep-icon_remove-stroke" href="<?php echo $links_tpl_reset_category;?>"></a>
								</li>

								<?php if (!empty($cat_crumbs)) {?>
									<?php $category_slug = end(explode('/', $main_category['link']));?>

									<?php foreach ($cat_crumbs as $category) {?>
										<li class="minfo-sidebar-params__sub-item">
											<div class="minfo-sidebar-params__sub-ttl"><?php echo cleanOutput($category['title']);?></div>
											<a class="minfo-sidebar-params__sub-close ep-icon ep-icon_remove-stroke" href="<?php echo replace_dynamic_uri($category_slug, $links_tpl_category);?>"></a>
										</li>

										<?php $category_slug = end(explode('/', $category['link']));?>
									<?php }?>
								<?php }?>
							</ul>
						</li>
					<?php }?>

					<li>
						<a class="btn btn-light btn-block txt-blue2" href="<?php echo $reset_all_filters_link;?>"><?php echo translate('clear_all_filters_btn');?></a>
					</li>
				</ul>
			</div>
		</div>
	<?php }?>

	<h3 class="minfo-sidebar-ttl mt-50">
		<span class="minfo-sidebar-ttl__txt"><?php echo translate('seller_products_search_block_title');?></span>
	</h3>

	<div class="minfo-sidebar-box">
		<div class="minfo-sidebar-box__desc">
			<form class="minfo-form mb-0" action="<?php echo $search_form_link;?>" method="GET">
				<input class="minfo-form__input2" type="text" name="keywords" maxlength="50" value="<?php echo isset($keywords) ? $keywords : '';?>" placeholder="<?php echo translate('general_dt_filters_entity_search_placeholder', null, true);?>">
				<button class="btn btn-dark btn-block minfo-form__btn2" type="submit"><?php echo translate('seller_products_search_btn');?></button>
			</form>
		</div>
	</div>

	<?php if (!empty($counter_categories)) {?>
		<h3 class="minfo-sidebar-ttl mt-50">
			<span class="minfo-sidebar-ttl__txt"><?php echo translate('filter_by_category_block_title');?></span>
		</h3>

		<div class="minfo-sidebar-box">
			<div class="minfo-sidebar-box__desc">
				<ul class="minfo-sidebar-box__list">
					<?php foreach ($counter_categories as $item) {?>
						<li class="minfo-sidebar-box__list-item">
							<a class="minfo-sidebar-box__list-link" href="<?php echo replace_dynamic_uri(strForURL($item['name']).'-'.$item['category_id'], $links_tpl_category); ;?>" <?php echo addQaUniqueIdentifier("global__sidebar-category")?>>
							<?php echo capitalWord($item['name']); ?>
							</a>
							<span class="minfo-sidebar-box__list-counter" <?php echo addQaUniqueIdentifier("global__sidebar-counter")?>>(<?php echo $item['counter']?>)</span>
						</li>
					<?php }?>
				</ul>
			</div>
		</div>
	<?php }?>
<?php }?>

<?php if ($current_page == 'index') {?>
	<div class="display-n show-1024 pt-25">
		<?php views()->display('new/user/seller/sidebar_seller_info_view');?>
	</div>
<?php }?>
