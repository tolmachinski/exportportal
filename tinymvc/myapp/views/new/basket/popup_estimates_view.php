<div class="js-modal-flex wr-modal-flex inputs-40">
	<div class="modal-flex__content">
		<?php if(!empty($shipping_estimates)){?>
			<ul class="list-items clearfix pb-10 pt-10">
			<?php foreach($shipping_estimates as $estimate){?>
				<li>
					<a href="<?php echo __SITE_URL;?>shippers/estimates_requests/group/<?php echo $estimate['group_key'] . "/" . strForURL(cut_str($estimate['group_title'], 100)); ?>"
                        target="_blank">
					<span class="pull-left w-450 text-nowrap"><?php echo $estimate['group_title'];?></span>
					<span class="pull-right"><?php echo formatDate($estimate['date_create']);?></span>
					</a>
				</li>
			<?php }?>
			</ul>
		<?php }?>
	</div>
</div>
