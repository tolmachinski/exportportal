<div class="header-nav-widget__top clearfix">
	<div class="logo-b pull-left">
		<i class="ep-icon ep-icon_bell"></i>
		<div class="den-b"><?php echo translate('header_navigation_link_notifications_title');?></div>
	</div> <!-- logo-b -->

	<ul class="nav-tabs-list ico-wr pull-right">
		<li class="i-all <?php echo ($type == 'all')?'active':''?>">
			<a href="all">
				<i class="ep-icon ep-icon_bell txt-red"></i>
				<?php echo translate('header_navigation_link_notifications_type_new');?> (<span><?php echo (!empty($counters_all))?$counters_all:0;?></span>)
			</a>
		</li>
		<li class="i-notice <?php echo ($type == 'notice')?'active':''?>">
			<a href="notice">
				<i class="ep-icon ep-icon_info"></i>
				<?php echo translate('header_navigation_link_notifications_type_notice');?> (<span><?php echo (!empty($counters['notice']))?$counters['notice']:0;?></span>)
			</a>
		</li>
		<li class="i-warning <?php echo ($type == 'warning')?'active':''?>">
			<a href="warning">
				<i class="ep-icon ep-icon_warning txt-orange"></i>
				<?php echo translate('header_navigation_link_notifications_type_warning');?> (<span><?php echo (!empty($counters['warning']))?$counters['warning']:0;?></span>)
			</a>
		</li>
		<li class="i-deleted <?php echo ($type == 'deleted')?'active':''?>">
			<a href="deleted">
				<i class="ep-icon ep-icon_trash"></i>
				<?php echo translate('header_navigation_link_notifications_type_trash');?> (<span><?php echo (!empty($counters_deleted))?$counters_deleted:0;?></span>)
			</a>
		</li>
	</ul> <!-- nav-tabs -->
</div> <!-- header-nav-widget__top -->

<div class="center-b wr-system-mess-list clearfix">
	<?php if(!empty($messages)){?>
	<div class="clearfix pb-10 mb-5 bdb-1-blue-darker">
		<div class="all-notifications pull-left">
			<label class="cur-pointer"><input class="checkbox-17-blue" type="checkbox" name="" /> <span class="lh-18 pl-7"><?php echo translate('header_navigation_label_notifications_check_all');?></span></label>
		</div>

		<div class="pull-right">
			<?php if('deleted' == $type){?>
				<a class="btn btn-danger btn-xs pull-right ml-10 confirm-dialog" data-message="Are you sure you want to empty trash?" data-callback="empty_trash_notifications" href="#">Empty trash</a>
			<?php } ?>
			<a class="remove-notification btn btn-dark btn-xs call-function pull-right" data-message="Are you sure you want to delete this notification(s)?" data-callback="no_remove_notification" href="#"><?php echo translate('header_navigation_notifications_btn_text_delete');?></a>
			<?php if('deleted' !== $type){?>
				<a class="read-notification btn btn-success btn-xs call-function pull-right mr-10" data-message="Are you sure you want to mark as read this notification(s)?" data-callback="no_read_notification" href="#"><?php echo translate('header_navigation_notifications_btn_text_mark_read');?></a>
			<?php } ?>
		</div>
	</div>

	<?php $messages_icons = array('notice' => 'info txt-blue', 'warning' => 'warning txt-orange');?>

	<ul class="system-mess-list">
	<?php foreach($messages as $mess){ ?>
		<li class="system-mess-list__item <?php if($mess['status'] == 'seen') echo 'system-mess-list__item--seen';?>">
			<div class="row system-mess-list__ttl">
				<div class="col-xs-1">
					<input class="checkbox-17-blue" data-type="<?php echo $mess['mess_type']; ?>" type="checkbox" name="" value="<?php echo $mess['id_um']; ?>" />
					<span class="system-mess-list__delimeter"></span>
					<i class="ep-icon-notice ep-icon ep-icon_<?php echo $messages_icons[$mess['mess_type']]; ?>"></i>
				</div>
				<div class="col-xs-9">
					<a class="system-mess-list__ttl-txt" href="<?php echo $mess['id_um']; ?>"><?php echo $mess['title']; ?></a>
				</div>
				<div class="col-xs-2 system-mess-list__date">
					<?php echo formatDate($mess['init_date'])?>
				</div>
			</div>

			<div class="system-mess-list__txt"><?php echo $mess['message']; ?></div>
		</li>
		<?php } ?>
	</ul>
	<div class="all-notifications clearfix">
		<div class="pull-left">
			<label class="cur-pointer"><input class="checkbox-17-blue" type="checkbox" name="" /> <span class="lh-18 pl-7"><?php echo translate('header_navigation_label_notifications_check_all');?></span></label>
		</div>

		<?php
			$params = array(
				'count_total' => $counters[$type],
				'per_page' => $per_page,
				'cur_page' => $page,
				'parent_element_classes' => 'pull-right messages-pagination',
				'data' => array(
					'type' => $type
				),
				'visible_pages' => 5
			);

			if( $type == 'all' )
				$params['count_total'] = $counters_all;

			echo get_pagination_html($params);
		?>

		<div class="pull-right lh-20">
			<?php $start_count = ($page-1) * $per_page;
				echo ($start_count + 1).' - '.($start_count+count($messages));?>
			<?php echo translate('pagination_label_from');?>
			<?php if($type == 'all'){
					echo (!empty($counters_all))?$counters_all:0;
				}elseif($type == 'deleted'){
					echo (!empty($counters_deleted))?$counters_deleted:0;
				}else{
					echo $counters[$type];
				}?>
		</div>
	</div>

	<?php }else{ ?>
		<div class="info-alert-b"><i class="ep-icon ep-icon_info"></i> You have no messages.</div>
	<?php }?>
</div>

<label class="mt-5 pull-left" id="check-send-notification"><input class="checkbox-17-blue" type="checkbox" name="" <?php echo checked(notify_email(), 1);?>> <span class="lh-17"><?php echo translate('header_navigation_label_notifications_send_on_email');?></span></label>
