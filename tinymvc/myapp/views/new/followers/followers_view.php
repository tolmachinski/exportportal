<?php
	$showList = count($followers);
?>

<div class="wr-all-follower-list relative-b">
	<ul class="ppersonal-followers pb-0">
		<?php tmvc::instance()->controller->view->display('new/followers/follower_item_view'); ?>
	</ul>

	<?php if ($showList < $followers_count) { ?>
		<a class="btn btn-outline-dark btn-sm btn-block button-more" data-type="<?php echo $type;?>" data-user="<?php echo $id_user;?>">view more</a>
	<?php } ?>
</div>
