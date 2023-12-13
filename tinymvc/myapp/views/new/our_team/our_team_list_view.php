<?php if (!empty($our_team)) { ?>
	<ul class="our-team">
		<?php foreach ($our_team as $info) { ?>
			<li class="our-team__item">
				<div class="our-team__item-wr">

					<div class="our-team__img-block">
						<img class="image" src="<?php echo $info['imageUrl']?>"  alt="<?php echo $info['post_person'] . ' ' . $info['name_person'] ?>"/>
					</div>

					<div class="our-team__text">
						<div class="our-team__name"><?php echo $info['name_person'] ?></div>
						<div class="our-team__post"><?php echo $info['post_person'] ?></div>
						<div class="dropdown">
							<a class="fancybox.ajax fancyboxValidateModal" data-title="<?php echo translate('about_us_team_about_member_modal_title', null, true);?>" href="<?php echo __SITE_URL . 'our_team/ourteam_popups/contact_person/' . $info['id_person'];?>">
								<i class="ep-icon ep-icon_menu-circles"></i>
							</a>
						</div>
					</div>
				</div>
			</li>
		<?php } ?>

	</ul>
<?php } else { ?>
	<div class="info-alert-b mb-15"><i class="ep-icon ep-icon_info-stroke"></i> <?php echo translate('about_us_team_members_not_found');?></div>
<?php } ?>
