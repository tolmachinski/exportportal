<?php if(!empty($stickers)){?>
	<?php foreach($stickers as $sticker){?>
	<li class="sticker-list__item <?php echo equals($sticker['priority'], 'important', 'anim_rotate-min1-plus1'); ?>" data-sticker="<?php echo $sticker['id_sticker'];?>">
		<div class="sticker-list__corners-top clearfix">
			<div class="sticker-list__status pull-left <?php echo equalsElse($sticker['priority'], 'important', 'txt-red', 'txt-green'); ?> tt-c"><i class="ep-icon ep-icon_circle"></i> <?php echo $sticker['priority'];?></div>
			<div class="pull-right pr-10 txt-blue-dark"><?php echo $sticker['status'];?></div>
		</div>

		<div class="sticker-list__body">
			<div class="sticker-list__img-wr">
				<img class="sticker-list__img" src="<?php echo $sticker['userImageUrl'] ?>" alt="<?php echo $sticker['fname'].' '.$sticker['lname'];?>"/>
			</div>
			<div class="sticker-list__info">
				<div class="sticker-list__date">Created: <?php echo formatDate($sticker['create_date']);?></div>
				<div class="sticker-list__date">Updated: <?php echo formatDate($sticker['update_date']);?></div>
				<a class="sticker-list__name" href="<?php echo __SITE_URL?>usr/<?php echo strForURL($sticker['fname'].' '.$sticker['lname']);?>-<?php echo $sticker['id_user_sender'];?>" target="_blank"><?php echo $sticker['fname'].' '.$sticker['lname'];?></a>
			</div>
			<div class="sticker-list__desc">
				<div class="sticker-list__subject"><?php echo $sticker['subject'];?></div>
				<p class="sticker-list__text"><?php echo $sticker['message'];?></p>
			</div>
		</div>

		<div class="sticker-list__corners-bottom">
			<?php $finished_status = array('read','archived', 'trash');?>
			<a href="#" data-message="Are you sure you want to delete this sticker?" data-callback="change_status" data-status="trash" class="ep-icon ep-icon_trash txt-red confirm-dialog pull-left" title="Delete"></a>
			<?php if($sticker['status'] != 'archived' && $sticker['status'] != 'trash'){?><a href="#" class="ep-icon ep-icon_archive txt-gray confirm-dialog pull-left ml-5" data-message="Are you sure you want add to archive this sticker?" data-callback="change_status" data-status="archived" title="Add to archive"></a><?php }?>
			<?php if( !in_array($sticker['status'], $finished_status)){?><a href="#" class="ep-icon ep-icon_ok txt-green confirm-dialog pull-left ml-5" data-message="Are you sure you want change status to read this sticker?" data-callback="change_status" data-status="read" title="Mark read"></a><?php }?>
		</div>
	</li>
	<?php }?>
<?php }else{?>
	<li class="w-100pr"><div class="info-alert-b"><i class="ep-icon ep-icon_info"></i> 0 stickers found by this search.</div></li>
<?php }?>
