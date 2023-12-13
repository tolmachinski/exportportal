<?php
$image_main = getDisplayImageLink(array('{ID}' => $user_main['idu'], '{FILE_NAME}' => $user_main['user_photo']), 'users.main', array( 'no_image_group' => $user_main['user_group'] ));
$badgeTitle = (1 == session()->__get('user_photo_with_badge')) ? translate('user_reset_certified_image_title') : translate('user_set_certified_image_title');
?>

<script type="application/ld+json">
{
	"@context": "http://schema.org",
	"@type": "Person",
	"address": {
		"@type": "PostalAddress",
		"addressLocality": "<?php echo $user_country['country_name']?><?php if(!empty($state['state'])){?>,<?php echo $state['state']; ?><?php }?><?php if(!empty($city['city'])){?>,<?php echo $city['city']; ?><?php }?>",
		"postalCode": "<?php echo $user_main['zip']; ?>",
		"streetAddress": "<?php echo $user_main['address']; ?>"
	},
	"description": "<?php echo strip_tags(truncWords($user_main['description_company'])); ?>",
	"image": "<?php echo $image_main;?>",
	"name": "<?php echo $user_main['fname'].' '.$user_main['lname']?>",
	"url": "<?php echo __SITE_URL.'usr/'.strForURL($user_main['fname'].' '.$user_main['lname']).'-'.$user_main['idu']?>"
}
</script>

<div class="display-n" itemscope itemtype="http://schema.org/Person">
	<a itemprop="url" href="<?php echo __SITE_URL.'usr/'.strForURL($user_main['fname'].' '.$user_main['lname']).'-'.$user_main['idu']?>"><?php echo $user_main['fname'].' '.$user_main['lname']?></a>
	<h2 itemprop="name"><?php echo $user_main['fname'].' '.$user_main['lname']?></h2>
	<img itemprop="image" src="<?php echo $image_main;?>" alt="<?php echo $user_main['fname'].' '.$user_main['lname']?>" />

	<?php if(!empty($user_main['description'])){ ?>
	<div itemprop="description">
		<?php echo strip_tags(truncWords($user_main['description_company'])); ?>
	</div>
	<?php } ?>

	<div itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">
		<span itemprop="addressLocality"><?php echo $user_country['country_name']?><?php if(!empty($state['state'])){?>,<?php echo $state['state']; ?><?php }?><?php if(!empty($city['city'])){?>,<?php echo $city['city']; ?><?php }?></span>
		<span itemprop="streetAddress"><?php echo $user_main['address']; ?></span>
		<span itemprop="postalCode"><?php echo $user_main['zip']; ?></span>
	</div>
</div>

<div class="ppersonal-logo">
    <?php if(logged_in() && is_my($user_main['idu'])){ ?>
    <div class="ppersonal-logo__edit dropdown">
        <a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#">
            <i class="ep-icon ep-icon_menu-circles"></i>
        </a>

        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            <a class="dropdown-item" href="<?php echo __SITE_URL.'user/photo';?>" data-title="Edit image" title="Edit image">
                <i class="ep-icon ep-icon_pencil"></i> Edit
            </a>
            <?php if(is_certified()){ ?>
            <a <?php if(!empty(session()->__get('user_photo')) && is_certified()){?>
                id="js-reset-image"
                data-badge="<?php echo session()->__get('user_photo_with_badge'); ?>"
                <?php } ?>
                class="dropdown-item"
                href="<?php echo __SITE_URL.'user/photo';?>" data-title="<?php echo $badgeTitle ?>" title="<?php echo $badgeTitle ?>">
                <i class="ep-icon ep-icon_photo-gallery "></i> <?php echo $badgeTitle ?>
            </a>
            <?php } ?>
        </div>
    </div>
    <?php } ?>

    <div class="ppersonal-logo__inner">
        <img
            id="js-profile-image"
            <?php echo addQaUniqueIdentifier('page__user-info__image'); ?>
            class="image"
            src="<?php echo $image_main;?>"
            alt="<?php echo $user_main['fname'].' '.$user_main['lname'];?>" />
    </div>
</div>

<?php if(!is_my($user_main['idu'])){?>
	<div class="dropdown">
		<a class="dropdown-toggle btn btn-light btn-block txt-blue2" id="dropdownMenuButton" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
			More actions
			<i class="ep-icon ep-icon_menu-circles pl-10"></i>
		</a>

		<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">

			<?php echo $chatBtn;?>

			<?php
				$is_logged_in = logged_in();
				$is_followed = $is_logged_in && in_session('followed', $user_main['idu']);
			?>

			<?php if ($is_logged_in && $is_in_contact) {?>
				<a class="dropdown-item contact-operation" data-user="<?php echo $user_main['idu'];?>" data-operation="remove" href="#" title="Remove from Favorites">
					<i class="ep-icon ep-icon_favorite"></i>
					<span class="txt">Favorited</span>
				</a>
			<?php } else {?>
				<a class="dropdown-item <?php echo $is_logged_in ? 'contact-operation' : 'js-require-logged-systmess'?>" data-user="<?php echo $user_main['idu'];?>" data-operation="add" href="#" title="Add to Favorites">
					<i class="ep-icon ep-icon_favorite-empty"></i>
					<span class="txt">Favorite</span>
				</a>
			<?php }?>

			<?php if ($is_followed) {?>
				<a class="dropdown-item call-function" data-user="<?php echo $user_main['idu'];?>" data-callback="unfollow_user" title="Unfollow this user" href="#">
					<i class="ep-icon ep-icon_follow"></i>
					<span class="txt">Unfollow this user</span>
				</a>
			<?php } else {?>
				<a class="dropdown-item <?php echo $is_logged_in ? 'fancybox.ajax fancyboxValidateModal' : 'js-require-logged-systmess';?>" data-title="Follow this user" href="<?php echo __SITE_URL . 'followers/popup_followers/follow_user/' . $user_main['idu'];?>" title="Follow this user">
					<i class="ep-icon ep-icon_unfollow"></i>
					<span class="txt">Follow this user</span>
				</a>
			<?php }?>

			<a class="dropdown-item <?php echo $is_logged_in ? 'fancybox.ajax fancyboxValidateModal' : 'js-require-logged-systmess';?>" data-title="Share this profile with your followers" href="<?php echo __SITE_URL . 'user/popup_forms/share_user/' . $user_main['idu'];?>" title="Share this profile with your followers">
				<i class="ep-icon ep-icon_share-stroke"></i>
				<span class="txt">Share this profile</span>
			</a>

			<a class="dropdown-item <?php echo $is_logged_in ? 'fancybox.ajax fancyboxValidateModal' : 'js-require-logged-systmess';?>" data-title="Send an email about this profile" href="<?php echo __SITE_URL . 'user/popup_forms/email_user/' . $user_main['idu'];?>" title="Send an email about this profile">
				<i class="ep-icon ep-icon_envelope-send"></i>
				<span class="txt">Send an email</span>
			</a>

			<a class="dropdown-item <?php echo $is_logged_in ? 'fancybox.ajax fancyboxValidateModal' : 'js-require-logged-systmess';?>" href="<?php echo __SITE_URL . 'complains/popup_forms/add_complain/user/' . $user_main['idu'] . '/' . $user_main['idu'];?>" data-title="Report this profile">
				<i class="ep-icon ep-icon_warning-circle-stroke"></i>
				<span class="txt">Report this profile</span>
			</a>

		</div>
	</div>
<?php }?>

<h3 class="minfo-sidebar-ttl mb-17 mt-40">
	<span class="minfo-sidebar-ttl__txt">Additional info</span>
</h3>

<div class="minfo-sidebar-box">
	<div class="minfo-sidebar-box__desc">
		<ul class="minfo-sidebar-box__list">
            <li class="minfo-sidebar-box__list-item <?php echo userGroupNameColor($user_main['gr_name']); ?>" <?php echo addQaUniqueIdentifier('global__additional-info__user'); ?>>
                <?php echo $user_main['is_verified'] ? $user_main['gr_name'] : trim(str_replace('Verified', '', $user_main['gr_name']));?>
			</li>
			<li class="minfo-sidebar-box__list-item">
				<span class="minfo-sidebar-box__list-ico">
					<img
                        width="24"
                        height="24"
                        src="<?php echo getCountryFlag($user_country['country_name']);?>"
                        <?php echo addQaUniqueIdentifier('global__additional-info__country-flag'); ?>
                        alt="<?php echo $user_country['country_name']?>"
                    >
				</span>
				<span <?php echo addQaUniqueIdentifier('global__additional-info__country-name'); ?>><?php echo $user_country['country_name']?></span>
			</li>
			<li class="minfo-sidebar-box__list-item">
				<span class="minfo-sidebar-box__list-ico">
					<i class="ep-icon ep-icon_marker-stroke"></i>
				</span>
				<span <?php echo addQaUniqueIdentifier('global__additional-info__location-city'); ?>>
                    <?php echo isset($city['city']) ? $city['city'] : ''; ?><?php if(!empty($state['state'])){?><?php echo (isset($city) && !empty($city['city']) ? ', ' : '') . $state['state']; }?>
                </span>
			</li>
		</ul>

		<?php if(!empty($user_rights)){?>
		<div class="clearfix mt-20">
			<?php $links_connect = array(
					'Website' => 'link',
					'Twitter' => 'twitter-square txt-twitter',
					'Facebook' => 'facebook-square txt-facebook',
					'LinkedIn' => 'linkedin-square txt-linkedin'
				); ?>
			<?php foreach($user_rights as $right){?>
				<?php if(isset($links_connect[$right['r_name']])){?>
					<a class="pull-left mr-6 fs-30 ep-icon ep-icon_<?php echo $links_connect[$right['r_name']];?>" href="<?php echo $right['value_field']; ?>" target="_blank"></a>
				<?php }?>
			<?php }?>
		</div>
		<?php }?>
	</div>
</div>

<?php if(logged_in() && is_my($user_main['idu']) && is_certified()){?>
<script>

$(function() {
    $('#js-reset-image').on('click', function (e) {
        e.preventDefault();
        setCertifiedImage(1 == $(this).data('badge') ? false : true);
    });
});
</script>
<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/js/user_page/photo-badge.js'); ?>"></script>
<?php } ?>
