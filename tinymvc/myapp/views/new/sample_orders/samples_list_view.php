<?php foreach ($samples ?? array() as $sample) { ?>
	<?php $buyer = $sample['buyer'] ?? array(); ?>
	<?php $seller = $sample['seller'] ?? array(); ?>
	<?php $status = $sample['status']; ?>
	<?php $fullname = $is_seller ? ($buyer['fullname'] ?? null) : ($seller['company_name'] ?? null); ?>
	<?php $photo = $is_seller ? ($buyer['photo'] ?? null) : ($seller['logo'] ?? null); ?>
	<?php $url = $is_seller ? ($buyer['url'] ?? null) : ($seller['url'] ?? null); ?>

	<li class="order-users-list__item flex-card js-sample-item" data-order="<?php echo cleanOutput($sample['id']); ?>">
		<div class="order-users-list__img image-card-center flex-card__fixed">
            <?php if(!empty($url)){ ?>
                <a class="link" href="<?php echo cleanOutput($url ?? '#'); ?>" target="_blank">
                    <img class="image" src="<?php echo cleanOutput($photo ?? null); ?>" alt="<?php echo cleanOutput($fullname ?? null); ?>">
                </a>
            <?php }else{ ?>
                <span class="link">
                    <img class="image" src="<?php echo cleanOutput($photo ?? null); ?>" alt="<?php echo cleanOutput($fullname ?? null); ?>">
                </span>
            <?php } ?>
		</div>

		<div class="order-users-list__detail flex-card__float">
			<div class="order-users-list__number">
				<?php echo cleanOutput(orderNumber($sample['id'])); ?>

				<div class="flex-display">
					<?php if (in_array($status['alias'] ?? null, array('new-order')) ) { ?>
						<i class="ep-icon ep-icon_circle fs-10 pt-3 mr-5 txt-red"></i>
					<?php } ?>

					<i class="order-popover js-popover js-sample-status-info-popover ep-icon <?php echo cleanOutput($status['icon'] ?? null); ?>"
						data-content="<?php echo cleanOutput($status['id']); ?>"
						data-placement="top">
					</i>
				</div>
			</div>

			<div class="order-users-list__price"><?php echo cleanOutput(get_price($sample['final_price'] ?? 0 )); ?></div>
			<div class="order-users-list__date"><?php echo cleanOutput(getDateFormatIfNotEmpty($sample['creation_date'] ?? null)); ?></div>
			<div class="order-users-list__company">
				<?php if (!empty($fullname)) { ?>
					by <span class="link"><?php echo cleanOutput($fullname ?? null); ?></span>
				<?php } ?>
			</div>
		</div>
    </li>
    <?php echo $delimiter ?? ''; ?>
<?php } ?>
