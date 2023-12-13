<script>
	var scrollElem = function($obj){
		var thisId = $obj.attr('href');
		scrollToElement(thisId, 60);
	};

	var attend_event = function ($btn) {
        $.ajax({
            method: 'POST',
            crossDomain: true,
            headers: { "X-Requested-With": "XMLHttpRequest" },
            url: '<?php echo __SITE_URL; ?>cr_events/ajax_attend_logged_operation',
            dataType: 'json',
            data: { id_event: $btn.data('eventId') },
            success: function (resp) {
                systemMessages(resp.message, "message-" + resp.mess_type);
            }
        });
    };
</script>
<a class="btn btn-primary btn-panel-right fancyboxSidebar fancybox mb-25" data-title="Category" href="#main-flex-card__fixed-right">
	<i class="ep-icon ep-icon_items"></i>
	Sidebar
</a>

<div class="title-public pt-0">
	<h1 class="title-public__txt">
		<?php echo $event['event_name'];?>
	</h1>
</div>

<ul class="cr-events-detail">
	<div class="cr-events-detail__img image-card2">
		<span class="link">
			<img class="image" src="<?php echo __IMG_URL . getImage('public/img/cr_event_images/'. $event['id_event'] . '/' . $event['event_image'], 'public/img/no_image/no-image-512x512.png');?>" alt="<?php echo $event['event_name']?>"/>
		</span>
	</div>

	<div class="cr-events-detail__actions">
        <?php if (logged_in()) { ?>
            <?php if ($allow_attend) { ?>
                <button class="btn btn-primary btn-block w-50pr mr-15 confirm-dialog" data-callback="attend_event" data-event-id="<?php echo $event['id_event']; ?>" data-message="Are you sure you want to attend this event?">
                    Attend
                </button>
            <?php } else { ?>
                <button class="btn btn-primary btn-block w-50pr mr-15">
                    Attended
                </button>
            <?php } ?>
        <?php } else { ?>
            <a class="btn btn-primary btn-block w-50pr mr-15 fancyboxValidateModal fancybox.ajax" data-title="Attend" href="<?php echo get_dynamic_url('cr_events/popup_forms/attend_event/' . $event['id_event'], __SITE_URL); ?>">
                Attend
            </a>
        <?php } ?>

		<div class="w-50pr">
			<div class="dropdown">
				<button class="btn btn-light btn-block dropdown-toggle" type="button" data-toggle="dropdown">
					More actions

					<i class="pl-10 ep-icon ep-icon_menu-circles"></i>
				</button>
				<div class="dropdown-menu">
					<a class="dropdown-item fancyboxValidateModal fancybox.ajax" href="<?php echo __SITE_URL;?>cr_events/popup_forms/email_event/<?php echo $event['id_event'];?>" data-title="Email item">
						<i class="ep-icon ep-icon_envelope-send"></i> Email this
					</a>
					<a class="dropdown-item fancyboxValidateModal fancybox.ajax" href="<?php echo __SITE_URL;?>cr_events/popup_forms/share_event/<?php echo $event['id_event'];?>" data-title="Share item">
						<i class="ep-icon ep-icon_share-stroke"></i> Share this
					</a>
				</div>
			</div>
		</div>
	</div>

	<div class="cr-events-detail__info">
		<div class="cr-events-detail__params">
			<div class="cr-events-detail__params-l">
				<div class="cr-events-detail__ba">
					<a class="link call-function" data-callback="scrollElem" href="#company-repr-ttl">Company Representatives Attending - <?php echo $event['event_count_ambassadors'];?></a>
				</div>
				<div class="cr-events-detail__type"><?php echo $event['event_type_name'];?></div>
			</div>
			<div class="cr-events-detail__params-r">
			<?php
				$start_month = formatDate($event['event_date_start'], 'M');
				$end_month = formatDate($event['event_date_end'], 'M');
				$start_day = formatDate($event['event_date_start'],'d');
				$end_day = formatDate($event['event_date_end'],'d');
				?>
				<?php if($start_month != $end_month){?>
					<div class="cr-events-list__date"><strong><?php echo $start_month; ?></strong> <?php echo $start_day; ?></div>
					<div class="cr-events-list__date"><strong><?php echo $end_month; ?></strong> <?php echo $end_day; ?></div>
				<?php } else { ?>
					<div class="cr-events-list__date"><strong><?php echo $start_month; ?></strong></div>
					<div class="cr-events-list__date">
						<?php
						if ($start_day == $end_day) {
							echo $start_day;
						} else {
							echo $start_day . ' - ' . $end_day;
						}
						?>
					</div>
				<?php } ?>
			</div>
		</div>

		<div class="cr-events-detail__desc ep-tinymce-text">
			<?php echo $event['event_description'];?>
		</div>
	</div>

	<div class="title-public" id="company-repr-ttl">
		<h2 class="title-public__txt">Company Representatives</h2>
	</div>

	<?php if (!empty($ambassadors)) { ?>
		<div class="ambassador-blocks">
		<?php foreach ($ambassadors as $ambassador) {
			tmvc::instance()->controller->view->display('new/cr/representative_user_view', array(
				'cr_user' => $ambassador
			));
		} ?>
		</div>
	<?php }else{ ?>
		<div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> <span>No Company Representatives.</span></div>
	<?php } ?>
</ul>