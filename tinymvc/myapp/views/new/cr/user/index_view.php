<script>
var ppersonalPicturesMore = function(btn){
	var $thisBtn = $(btn);

	$thisBtn.closest('.ppersonal-pictures').find('.display-n').fadeIn();
	$thisBtn.remove();
}
</script>
<a class="btn btn-primary btn-panel-left fancyboxSidebar fancybox mb-15" data-title="Category" href="#main-flex-card__fixed-left">
	<i class="ep-icon ep-icon_items"></i>
	Sidebar
</a>

<div class="ppersonal-user-top bdb-none">
	<div class="ppersonal-user-status <?php if($user_main['logged'] || is_my($user_main['idu'])){?>txt-green<?php } else { ?>txt-red<?php } ?>">
		<?php if($user_main['logged'] || is_my($user_main['idu'])){?>
			Online
		<?php } else { ?>
			Offline
		<?php } ?>
	</div>

	<h1 class="ppersonal-names">
		<span class="ppersonal-names__user"><?php echo $user_main['fname'].' '.$user_main['lname']?></span>

		<span class="ppersonal-names__txt">as</span>

		<?php if(have_right('cr_international', $ugroup_rights)){?>
			<span class="ppersonal-names__company">
				<?php echo $user_main['gr_name'];?>
			</span>
		<?php } else{?>
			<a class="ppersonal-names__company" href="<?php echo getSubDomainURL($user_domain['country_alias']);?>">
				<?php echo $user_domain['country'];?> <?php echo $user_main['gr_name'];?>
			</a>
		<?php }?>
	</h1>

	<div class="row pt-30">
		<?php if(!empty($header_event)){?>
			<script>
				var attend_event = function ($btn) {
					$.ajax({
						type: 'POST',
						url: '<?php echo __SITE_URL; ?>cr_events/attend_logged_operation',
						data: {
							id_event: $btn.data('eventId')
						},
						dataType: 'JSON',
						success: function (resp) {
							systemMessages(resp.message, 'message-' + resp.mess_type);
							if(resp.mess_type == 'success'){
								$btn.replaceWith('<button class="btn btn-primary pl-40 pr-40">Attended</button>');
							}
						}
					});
				};
			</script>
			<div class="col-12 col-md-6">
				<h3 class="pb-5">Find me at this Event</h3>
				<a class="fs-18 txt-medium txt-black" href="<?php echo get_dynamic_url('/event/' . $header_event['event_url'], getSubDomainURL($header_event['country_alias'])); ?>"><?php echo $header_event['event_name'];?></a>
				<div class="txt-gray pt-5"><?php echo $header_event['user_location'];?></div>
				<div class="pt-5 pb-10">
					<?php
						$start_year = formatDate($header_event['event_date_start'], 'Y');
						$end_year = formatDate($header_event['event_date_end'], 'Y');
						$start_month = formatDate($header_event['event_date_start'], 'M');
						$end_month = formatDate($header_event['event_date_end'], 'M');
						$start_day = formatDate($header_event['event_date_start'],'d');
						$end_day = formatDate($header_event['event_date_end'],'d');

						$event_date = array(
							'start' => $start_month.' '.$start_day,
							'end' => ' - '.$end_day
						);

						if($start_month != $end_month){
							$event_date['end'] = ' - '.$end_month.' '.$end_day;
						}

						if($start_year != $end_year){
							$event_date['start'] .= ' '.$start_year;
						}
						$event_date['end'] .= ' '.$end_year;

						echo implode('', $event_date);
					?>
				</div>

				<?php if (logged_in()) { ?>
					<?php if ($allow_attend) { ?>
						<button class="btn btn-outline-dark mnw-130 confirm-dialog" data-callback="attend_event" data-event-id="<?php echo $header_event['id_event']; ?>" data-message="Are you sure you want to attend this event?">
							Attend
						</button>
					<?php } else { ?>
						<button class="btn btn-outline-dark mnw-130">
							Attended
						</button>
					<?php } ?>
				<?php } else { ?>
					<a class="btn btn-outline-dark mnw-130 fancyboxValidateModal fancybox.ajax" data-title="Attend" href="<?php echo get_dynamic_url('cr_events/popup_forms/attend_event/' . $header_event['id_event']); ?>">
						Attend
					</a>
				<?php } ?>
			</div>
		<?php }?>
		<div class="col-12 col-md-6">
			<div class="lh-22 pb-25">
				In case you are satisfied with the services offered, I highly suggest you to register on our e-Commerce website!
			</div>
			<?php if(!logged_in()){?>
				<a class="btn btn-light mnw-130" href="<?php echo get_static_url('register/index', __SITE_URL, 'ba/'.$user_main['idu']);?>">Register</a>
			<?php } else{?>
				<button class="btn btn-light mnw-130 call-systmess" data-type="info" data-message="Info: You are already registered.">Register</button>
			<?php }?>
		</div>
	</div>
</div>

<?php
	$user_speak_langs = '';
	if(!empty($user_additional['user_speak_langs'])){
		$user_speak_langs = json_decode($user_additional['user_speak_langs'], true);
	}

	$user_skills = '';
	if(!empty($user_additional['user_skills'])){
		$user_skills = json_decode($user_additional['user_skills'], true);
	}

	$user_awards = '';
	if(!empty($user_additional['user_awards'])){
		$user_awards = json_decode($user_additional['user_awards'], true);
	}

	$user_jobs = '';
	if(!empty($user_additional['user_jobs'])){
		$user_jobs = json_decode($user_additional['user_jobs'], true);
	}

	$user_educations = '';
	if(!empty($user_additional['user_educations'])){
		$user_educations = json_decode($user_additional['user_educations'], true);
	}

	$user_certificates = '';
	if(!empty($user_additional['user_certificates'])){
		$user_certificates = json_decode($user_additional['user_certificates'], true);
	}
?>

<ul class="nav nav-tabs nav--borders nav--480">
	<li class="nav-item">
		<a class="nav-link active" href="#cr-about-li" aria-controls="title" data-toggle="tab" rel="nofollow">
			About
		</a>
	</li>
	<?php if(!empty($user_educations)){?>
	<li class="nav-item">
		<a class="nav-link" href="#cr-educations-li" aria-controls="title" data-toggle="tab" rel="nofollow">
			Education
		</a>
	</li>
	<?php }?>

	<?php if(!empty($user_jobs)){?>
	<li class="nav-item">
		<a class="nav-link" href="#cr-jobs-li" aria-controls="title" data-toggle="tab" rel="nofollow">
			Work History
		</a>
	</li>
	<?php }?>

	<?php if(!empty($user_awards)){?>
	<li class="nav-item">
		<a class="nav-link" href="#cr-awards-li" aria-controls="title" data-toggle="tab" rel="nofollow">
			Awards
		</a>
	</li>
	<?php }?>

	<?php if(!empty($user_skills)){?>
	<li class="nav-item">
		<a class="nav-link" href="#cr-skills-li" aria-controls="title" data-toggle="tab" rel="nofollow">
			Skills
		</a>
	</li>
	<?php }?>


	<?php if(!empty($user_speak_langs)){?>
	<li class="nav-item">
		<a class="nav-link" href="#cr-speak-langs-li" aria-controls="title" data-toggle="tab" rel="nofollow">
			Languages Spoken
		</a>
	</li>
	<?php }?>

	<?php if(!empty($user_certificates)){?>
	<li class="nav-item">
		<a class="nav-link" href="#cr-certificates-li" aria-controls="title" data-toggle="tab" rel="nofollow">
			Certificates
		</a>
	</li>
	<?php }?>
</ul>

<div class="tab-content tab-content--borders">
	<div role="tabpanel" class="tab-pane fade show active" id="cr-about-li">
		<?php if(!empty($user_main['description'])){?>
			<div class="ep-tinymce-text">
				<?php echo $user_main['description'];?>
			</div>
		<?php } else{?>
			<div class="info-alert-b">
				<i class="ep-icon ep-icon_info-stroke"></i>
				<span>No description.</span>
			</div>
		<?php }?>
	</div>

	<?php if(!empty($user_educations)){?>
	<div role="tabpanel" class="tab-pane fade" id="cr-educations-li">
		<ul class="cr-user-history pt-0">
		<?php foreach($user_educations as $user_educations_item){?>
			<li class="cr-user-history__item">
				<h3 class="cr-user-history__ttl"><?php echo $user_educations_item['school'];?></h3>
				<div class="cr-user-history__top pb-10">
					<div class="cr-user-history__name">
						<span class="txt-gray">Degree:</span>
						<?php echo $user_educations_item['degree'];?>
					</div>
					<div class="cr-user-history__date">
						<?php echo $user_educations_item['year_from'];?>
						<span>-</span>
						<?php echo $user_educations_item['year_to'];?>
					</div>
				</div>

				<div class="pl-15 pr-15">
					<div class="pb-10">
						<span class="txt-gray">Study:</span>
						<?php echo $user_educations_item['field_of_study'];?>
					</div>

					<?php if(!empty($user_educations_item['grade'])){?>
					<div class="pb-10">
						<span class="txt-gray">Grade:</span>
						<?php echo $user_educations_item['grade'];?>
					</div>
					<?php }?>

					<?php if(!empty($user_educations_item['description'])){?>
					<div class="lh-20">
						<?php echo $user_educations_item['description'];?>
					</div>
					<?php }?>
				</div>
			</li>
		<?php }?>
		</ul>
	</div>
	<?php }?>

	<?php if(!empty($user_jobs)){?>
	<div role="tabpanel" class="tab-pane fade" id="cr-jobs-li">
		<ul class="cr-user-history pt-0">
			<?php foreach($user_jobs as $user_jobs_item){
				$date_to = strtotime($user_jobs_item['date_to']);?>
				<li class="cr-user-history__item">
					<h3 class="cr-user-history__ttl"><?php echo $user_jobs_item['place'];?></h3>
					<div class="cr-user-history__top pb-10">
						<div class="cr-user-history__name"><?php echo $user_jobs_item['position'];?></div>
						<div class="cr-user-history__date">
							<?php echo formatDate($user_jobs_item['date_from'],'M Y');?> - <?php echo ($date_to)?formatDate($user_jobs_item['date_to'],'M Y'):'Present';?>
							<?php //if(!empty($date_to)){?>
								<!-- (<?php //echo timeAgo($user_jobs_item['date_from'], 'Y,m,d', $date_to)?>) -->
							<?php //}?>
						</div>
					</div>

					<?php if(!empty($user_jobs_item['skills'])){?>
					<ul class="cr-user-history__skills">
						<?php foreach($user_jobs_item['skills'] as $skill_item){?>
						<li class="cr-user-history__skills-item">- <?php echo $skill_item;?></li>
						<?php }?>
					</ul>
					<?php }?>
				</li>
			<?php }?>
		</ul>
	</div>
	<?php }?>

	<?php if(!empty($user_awards)){?>
	<div role="tabpanel" class="tab-pane fade" id="cr-awards-li">
		<ul class="cr-user-history pt-0">
		<?php foreach($user_awards as $user_awards_item){?>
			<li class="cr-user-history__item">
				<h3 class="cr-user-history__ttl"><?php echo $user_awards_item['title'];?></h3>

				<div class="cr-user-history__top">
					<?php if(!empty($user_awards_item['issuer'])){?>
					<div class="cr-user-history__name">
						<span class="txt-gray">Issuer:</span>
						<?php echo $user_awards_item['issuer'];?>
					</div>
					<?php }?>
					<div class="cr-user-history__date">
						<?php echo formatDate($user_awards_item['award_date'],'M Y');?>
					</div>
				</div>

				<?php if(!empty($user_awards_item['description'])){?>
				<div class="">
					<?php echo $user_awards_item['description'];?>
				</div>
				<?php }?>
			</li>
		<?php }?>
		</ul>
	</div>
	<?php }?>

	<?php if(!empty($user_skills)){?>
	<div role="tabpanel" class="tab-pane fade" id="cr-skills-li">
		<div class="row">
			<?php foreach($user_skills as $user_skills_item){?>
				<div class="col-4 mb-15 lh-24">
					- <?php echo $user_skills_item;?>
				</div>
			<?php }?>
		</div>
	</div>
	<?php }?>

	<?php if(!empty($user_speak_langs)){?>
	<div role="tabpanel" class="tab-pane fade" id="cr-speak-langs-li">
		<div class="row">
			<?php foreach($user_speak_langs as $user_speak_langs_item){?>
				<div class="col-4 mb-15 lh-24">
					<?php echo $user_speak_langs_item['name'];?> -
					<strong class="tt-uppercase" title="<?php echo $langs_proficiencies[$user_speak_langs_item['proficiency']]?>"><?php echo $user_speak_langs_item['proficiency'];?></strong>
				</div>
			<?php }?>
		</div>
	</div>
	<?php }?>

	<?php if(!empty($user_certificates)){?>
	<div role="tabpanel" class="tab-pane fade" id="cr-certificates-li">
		<div class="row">
			<?php foreach($user_certificates as $user_certificates_item){?>
				<div class="col-4 mb-15 lh-24">
					- <?php echo $user_certificates_item;?>
				</div>
			<?php }?>
		</div>
	</div>
	<?php }?>
</div>

<?php if(count($user_photo)){ ?>
	<div class="title-public">
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

	<ul class="ppersonal-pictures pb-0">
		<?php $total_photo = count($user_photo);
		foreach($user_photo as $key => $photo){?>
			<li class="ppersonal-pictures__item <?php echo ($key>2)?'display-n':'';?>">
				<a
					class="link fancyboxGallery"
					rel="galleryUser"
					href="<?php echo getDisplayImageLink(array('{ID}' => $user_main['idu'], '{FILE_NAME}' => $photo['main']), 'users.photos'); ?>" data-title="<?php echo $user_main['fname'].' '.$user_main['lname']?>" title="<?php echo $user_main['fname'].' '.$user_main['lname']?>">
					<img class="image" src="<?php echo getDisplayImageLink(array('{ID}' => $user_main['idu'], '{FILE_NAME}' => $photo['main']), 'users.photos', array( 'thumb_size' => 1 )); ?>" alt="<?php echo $user_main['fname'].' '.$user_main['lname']?>"/>
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

<?php $user_video = '';
if(!empty($user_additional['user_video'])){
	$user_video = json_decode($user_additional['user_video'], true);
}

if(!empty($user_video)){?>
	<div class="title-public">
		<h2 class="title-public__txt">Video</h2>
	</div>

	<div class="video-wrapper">
		<iframe width="100%" height="450" src="https://www.youtube.com/embed/<?php echo $user_video['code']; ?>?rel=0" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
	</div>
<?php }?>

<div class="title-public">
	<h2 class="title-public__txt">Events</h2>
</div>

<ul class="nav nav-tabs nav--borders">
	<li class="nav-item">
		<a class="nav-link active" href="#cr-events-future-li" aria-controls="title" data-toggle="tab" rel="nofollow">
			Future events
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="#cr-events-past-li" aria-controls="title" data-toggle="tab" rel="nofollow">
			Past events
		</a>
	</li>
</ul>

<div class="tab-content tab-content--borders">
	<div class="tab-pane fade active show" id="cr-events-future-li">
		<div class="row middle-event--users">
			<?php tmvc::instance()->controller->view->display('new/cr/user/event_tab_view'); ?>
		</div>
	</div>

	<div class="tab-pane fade" id="cr-events-past-li">
		<div class="row">
			<?php tmvc::instance()->controller->view->display('new/cr/user/event_tab_view', array('assigned_events' => $assigned_events_expired)); ?>
		</div>
	</div>
</div>

<?php if(have_right('cr_international', $ugroup_rights)){?>
	<div class="title-public">
		<h2 class="title-public__txt">Official Representative of Export Portal in following countries</h2>
	</div>

	<p>If you want to be Export Portal’s Official Brand Ambassador in your country, just contact us by email, or simply apply for the vacancies you see below.</p>

	<div class="clearfix pt-10 pb-50">
		<?php foreach($user_domains as $user_domain){?>
			<div class="col-tn-12 col-6 col-md-3 countries-tab__item">
				<div class="user-domains-item lh-24 p-5">
					<a class="link" href="<?php echo getSubDomainURL($user_domain['country_alias']); ?>">
						<img
                            class="image"
                            src="<?php echo getCountryFlag($user_domain['country']); ?>"
                            alt="<?php echo $user_domain['country'];?>"
                            width="32"
                            height="32"
                        />
						<?php echo $user_domain['country'];?>
					</a>
				</div>
			</div>
		<?php }?>
	</div>
<?php }?>

<?php if(!empty($domain_users)){?>
	<div class="title-public">
		<h2 class="title-public__txt">Looking for a different representative in this country/countries?</h2>
		<div class="dropdown">
			<a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#">
				<i class="ep-icon ep-icon_menu-circles"></i>
			</a>

			<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
				<a class="dropdown-item fancybox.ajax fancybox " href="<?php echo get_dynamic_url('cr_users/popup_forms/same_country/'.strForUrl($user_main['fname'].' '.$user_main['lname'].' '.$user_main['idu']));?>" data-title="View different representative">
					<i class="ep-icon ep-icon_items"></i> All users
				</a>
			</div>
		</div>
	</div>

	<ul class="ambassador-blocks">
		<?php foreach ($domain_users as $domain_user) {
			tmvc::instance()->controller->view->display('new/cr/representative_user_view', array(
				'cr_user' => $domain_user
			));
		} ?>
	</ul>
<?php }?>
