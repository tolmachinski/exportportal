<div class="epuser-subline-nav2">
	<div class="epuser-subline-nav2__item epuser-subline-nav2__item--hiden">
		<a class="link new-circle-notification call-function call-action<?php echo (empty($counters_unread))?' disabled':'';?><?php echo ($status == 'new')?' active':''?>" data-js-action="notification:list-all" data-callback="btnNotificationList" data-status="new" href="#">
			<span class="name">Unread</span>
			<span class="count"><?php echo (!empty($counters_unread))?$counters_unread:0;?></span>
		</a>
		<a class="link link--all call-function call-action<?php echo (empty($counters_all))?' disabled':'';?><?php echo ($status == 'all')?' active':''?>"  data-js-action="notification:list-all" data-callback="btnNotificationList" data-status="all" href="#">
			<span class="name">All notifications</span>
			<span class="count"><?php echo (!empty($counters_all))?$counters_all:0;?></span>
		</a>
		<a class="link call-function call-action<?php echo (empty($counters_deleted))?' disabled':'';?><?php echo ($status == 'deleted')?' active':''?>" data-js-action="notification:list-all" data-callback="btnNotificationList" data-status="deleted" href="#">
			<span class="name">Deleted</span>
			<span class="count"><?php echo (!empty($counters_deleted))?$counters_deleted:0;?></span>
		</a>
	</div>

	<div class="epuser-subline-filter--mobile">
		<div class="dropdown">
			<a class="btn btn-light btn-block dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
				<span class="txt">Filter by</span>
				<i class="ep-icon ep-icon_arrow-down fs-10"></i>
			</a>

			<div class="dropdown-menu dropdown-menu-right">
				<a class="dropdown-item pl-20 call-function call-action <?php echo ($type == 'notice')?' active':''?>" data-js-action="notification:filter-list" data-callback="filterNotificationList" data-status="<?php echo $status;?>" data-type="notice" href="#">
					<i class="ep-icon ep-icon_info-stroke txt-blue2"></i>
					<span class="txt">Info</span>
					<span class="count"><?php echo (!empty($counters['notice']))?$counters['notice']:0;?></span>
				</a>

				<a class="dropdown-item pl-20 call-function call-action <?php echo ($type == 'warning')?' active':''?>" data-js-action="notification:filter-list" data-callback="filterNotificationList" data-status="<?php echo $status;?>" data-type="warning" href="#">
					<i class="ep-icon ep-icon_warning-stroke txt-orange"></i>
					<span class="txt">Important</span>
					<span class="count"><?php echo (!empty($counters['warning']))?$counters['warning']:0;?></span>
				</a>
				<div class="dropdown-divider"></div>
				<a class="dropdown-item pl-45 call-function call-action<?php echo ($status == 'all')?' active':''?>"  data-js-action="notification:list-all" data-callback="btnNotificationList" data-status="all" href="#">
					<span class="txt">All</span>
					<span class="count"><?php echo (!empty($counters_all))?$counters_all:0;?></span>
				</a>
				<a class="dropdown-item pl-45 call-function call-action<?php echo ($status == 'deleted')?' active':''?>" data-js-action="notification:list-all" data-callback="btnNotificationList" data-status="deleted" href="#">
					<span class="txt">Deleted</span>
					<span class="count"><?php echo (!empty($counters_deleted))?$counters_deleted:0;?></span>
				</a>
			</div>
		</div>
	</div>
</div>

<div class="epuser-subline-additional2 epuser-subline-additional2--top">
	<div class="epuser-subline-filter">
		<div class="epuser-subline-filter__ttl">Filter by:</div>

		<a class="link call-function call-action <?php echo ($type == 'all')?' active':''?>" data-js-action="notification:filter-list" data-callback="filterNotificationList" data-status="<?php echo $status;?>" data-type="all" href="#">
			<span class="name m-0">All</span>
		</a>

		<a class="link call-function call-action <?php echo ($type == 'notice')?' active':''?>" data-js-action="notification:filter-list" data-callback="filterNotificationList" data-status="<?php echo $status;?>" data-type="notice" href="#">
			<i class="ep-icon ep-icon_info-stroke"></i>
			<span class="name">Info</span>
			<span class="count"><?php echo (!empty($counters['notice']))?$counters['notice']:0;?></span>
		</a>

		<a class="link call-function call-action <?php echo ($type == 'warning')?' active':''?>" data-js-action="notification:filter-list" data-callback="filterNotificationList" data-status="<?php echo $status;?>" data-type="warning" href="#">
			<i class="ep-icon ep-icon_warning-stroke txt-orange"></i>
			<span class="name">Important</span>
			<span class="count"><?php echo (!empty($counters['warning']))?$counters['warning']:0;?></span>
		</a>
	</div>
</div>

<?php $messages_icons = array('notice' => 'info-stroke txt-blue2', 'warning' => 'warning-stroke txt-orange');?>

<div class="epuser-popup__overflow">
	<?php if(!empty($messages)){?>
	<ul class="epuser-subline-list2 js-epuser-subline-list2">
		<?php foreach($messages as $mess){ ?>
		<li class="js-epuser-subline-list2__item<?php if($mess['status'] == 'seen') echo ' js-epuser-subline-list2__item--seen';?>">
			<div class="epuser-subline-list2__ttl custom-checkbox" data-notify="<?php echo $mess['id_um']; ?>">
				<input data-type="<?php echo $mess['mess_type']; ?>" type="checkbox" name="" value="<?php echo $mess['id_um']; ?>">

				<div class="epuser-subline-list2__ttl-detail call-function call-action" data-js-action="notification:show-detail" data-callback="showNotificationDetail">
					<i class="ep-icon ep-icon_<?php echo $messages_icons[$mess['mess_type']]; ?>"></i>

					<div class="epuser-subline-list2__ttl-detail-inner">
						<div class="epuser-subline-list2__ttl-txt custom-checkbox__text">
							<?php echo cleanOutput($mess['title']);?>
						</div>

						<div class="epuser-subline-list2__date">
							<?php echo formatDate($mess['init_date'])?>
						</div>
					</div>
				</div>
			</div>

			<div class="epuser-subline-list2__desc">
				<?php echo $mess['message']; ?>
			</div>
		</li>
		<?php } ?>
	</ul>
	<?php }else{ ?>
		<div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> You have no messages.</div>
	<?php }?>
</div>

<?php if(!empty($messages)){?>
<div class="epuser-subline-additional2">
	<div class="flex-display flex-ai--c">
		<label class="check-all2 js-check-all2 custom-checkbox">
			<input type="checkbox" name="">
			<span class="custom-checkbox__text">Check all</span>
		</label>
		<div class="actions">
			<a class="read-notification btn btn-light call-function call-action mnw-140 mr-10" data-message="Are you sure you want to mark as read this notification(s)?" data-js-action="notification:no-read" data-callback="no_read_notification2" href="#">
				<span class="text">Mark as read</span>
				<i class="ep-icon ep-icon_visible fs-19"></i>
			</a>
			<a id="js-remove-action" class="remove-notification btn btn-dark call-function call-action mnw-100" data-message="Are you sure you want to delete this notification(s)?" data-js-action="notification:no-remove" data-callback="no_remove_notification2" href="#">
				<span class="text">Delete</span>
				<i class="ep-icon ep-icon_trash-stroke"></i>
			</a>
			<?php if($status == 'deleted'){?>
			<a class="btn btn-light js-confirm-dialog confirm-dialog mnw-100 ml-10" data-message="Are you sure you want to clear all?" data-js-action="notification:clear-all" data-callback="empty_trash_notification2" href="#">
				<span class="text">Clear all</span>
				<i class="ep-icon ep-icon_broom fs-18"></i>
			</a>
			<?php } ?>
		</div>
	</div>

	<div class="flex-display">
		<?php
			$pagination_params = array(
				'count_total' => $counters[$type],
				'per_page' => $per_page,
				'cur_page' => $page,
				'status' => $status,
				'type' => $type,
			);

			if(isset($type_mess)){
				$pagination_params['type-mess'] = $type_mess;
			}

			views()->display('new/nav_header/pagination_block_view', $pagination_params);
		?>
	</div>
</div>
<?php }?>
