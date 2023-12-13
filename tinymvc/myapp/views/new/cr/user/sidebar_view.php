
<?php if(logged_in() && is_my($user_main['idu'])){ ?>
	<div class="dropdown pull-right">
		<a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#">
			<i class="ep-icon ep-icon_menu-circles"></i>
		</a>

		<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
			<a class="dropdown-item" href="<?php echo __SITE_URL.'user/photo';?>">
				<i class="ep-icon ep-icon_pencil"></i> Edit
			</a>
		</div>
	</div>
<?php } ?>

<div class="ppersonal-logo">
    <div class="ppersonal-logo__inner">
        <img class="image" src="<?php echo getDisplayImageLink(array('{ID}' => $user_main['idu'], '{FILE_NAME}' => $user_main['user_photo']), 'users.main', array( 'thumb_size' => 2, 'no_image_group' => $user_main['user_group'] ));?>" alt="<?php echo $user_main['fname'].' '.$user_main['lname'];?>" />
    </div>
</div>

<h3 class="minfo-sidebar-ttl mt-50">
	<span class="minfo-sidebar-ttl__txt">Additional info</span>
</h3>

<div class="minfo-sidebar-box">
	<div class="minfo-sidebar-box__desc">
		<ul class="minfo-sidebar-box__list">
			<?php if(!empty($user_location)){?>
			<li class="minfo-sidebar-box__list-item">
				<span class="minfo-sidebar-box__list-ico">
					<img
                        width="24"
                        height="24"
                        src="<?php echo getCountryFlag($user_location['country']);?>"
                        alt="<?php echo $user_location['country'];?>"
                    >
				</span>
				<?php echo implode(', ', $user_location);?>
			</li>
			<?php }?>

			<li class="minfo-sidebar-box__list-item">
				<span class="minfo-sidebar-box__list-ico">
					<i class="ep-icon ep-icon_envelope-stroke"></i>
				</span>
				<div class="text-nowrap">
					<?php echo antispambot($user_main['email'], ''); ?>
				</div>
			</li>

			<?php if(!empty($user_main['phone'])){?>
			<li class="minfo-sidebar-box__list-item">
				<span class="minfo-sidebar-box__list-ico">
					<i class="ep-icon ep-icon_phone"></i>
				</span>
				<?php echo antispambot($user_main['phone_code'].' '.$user_main['phone'], 'flex-jc--fe'); ?>
			</li>
			<?php }?>

			<?php if(!empty($user_main['website'])){?>
			<li class="minfo-sidebar-box__list-item">
				<span class="minfo-sidebar-box__list-ico">
					<i class="ep-icon ep-icon_icon_link"></i>
				</span>
				<a class="text-nowrap" href="<?php echo $user_main['website']; ?>">Website link</a>
			</li>
			<?php }?>

			<li class="minfo-sidebar-box__list-item flex-jc--sb">
				<span class="minfo-sidebar-box__list-txt">
					Registratration date:
				</span>
				<?php echo formatDate($user_main['registration_date'], 'm/d/Y')?>
			</li>

			<li class="minfo-sidebar-box__list-item flex-jc--sb">
				<span class="minfo-sidebar-box__list-txt">
					Last activity date:
				</span>
				<?php echo formatDate($user_main['last_active'], 'm/d/Y')?>
			</li>

			<?php
				$user_contacts = '';
				if(!empty($user_additional['user_contacts'])){
					$user_contacts = json_decode($user_additional['user_contacts'], true);
				}

				if(!empty($user_contacts)){
			?>
				<?php foreach($user_contacts as $user_contacts_item){?>
				<li class="minfo-sidebar-box__list-item flex-jc--sb">
					<span class="minfo-sidebar-box__list-txt">
						<?php echo $user_contacts_item['name'];?>
					</span>
					<?php echo antispambot($user_contacts_item['value'], 'flex-jc--fe');?>
				</li>
				<?php }?>
			<?php }?>
		</ul>

		<?php $links_connect = array(
				'YouTube' => 'youtube-square',
				'Twitter' => 'twitter-square',
				'Facebook' => 'facebook-square',
				'LinkedIn' => 'linkedin-square',
				'Website' => 'link'
			); ?>
		<div class="clearfix mt-20">
			<?php foreach($user_rights_fields as $user_right_field){?>
				<a class="pull-left mr-6 fs-30 ep-icon ep-icon_<?php echo $links_connect[$user_right_field['r_name']];?>" href="<?php echo $user_right_field['value_field'];?>" target="_blank"></a>
			<?php }?>
		</div>
	</div>
</div>

