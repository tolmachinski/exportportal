<?php $session = tmvc::instance()->controller->session; ?>
<div class="widget-type-1 widget-types" style="width: <?php echo empty($width) ? '100%' : $width; ?>; height: <?php echo empty($widgetHeight) ? '320px' : $widgetHeight; ?>;" data-type="1">
    <div class="widget-types__header">
		<a class="widget-types__ttl" href="<?php echo __SITE_URL; ?>" target="_blank">
			<img class="image" src="/public/img/ep-logo/ep_logo_x82.png" alt="exportportal"> My Export Portal
		</a>
        <div class="widget-types__seller-section">
            <div class="widget-types__seller-photo">
				<span class="link">
					<img class="image" src="<?php echo getDisplayImageLink(array('{ID}' => $session->id, '{FILE_NAME}' => $session->user_photo), 'users.main', array( 'thumb_size' => 0, 'no_image_group' => group_session() ));?>" alt="photo">
				</span>
			</div>
            <div class="widget-types__seller-info">
                <a class="widget-types__seller-name" href="<?php echo getMyCompanyURL(); ?>" target="_blank"><?php echo my_company_name(); ?></a>
                <span class="widget-types__items-count"><?php echo $sellerItemsCount; ?> items</span>
				<a class="widget-types__profile-link" href="<?php echo getMyCompanyURL(); ?>" target="_blank">View profile</a>
            </div>
        </div>
    </div>
    
    <div class="widget-types__body" <?php echo !empty($bodyHeight) ? "style=\"height: $bodyHeight;\"" : ''; ?>>
	<?php if ($withTemplate) { ?>
		<a class="widget-types__item item-template item-link" target="_blank" href="#">
			<div class="widget-types__item-image image-card3">
				<span class="link">
					<img class="image" src="http://placehold.it/300x500" alt="item">
				</span>
			</div>
		</a>
	<?php } ?>

	<?php foreach ($widgetItems as $item) { ?>
		<a class="widget-types__item default item-link seller-item-<?php echo $item['id']; ?>" href="<?php echo $item['link']; ?>" title="<?php echo $item['title']; ?>" target="_blank">
			<div class="widget-types__item-image image-card3">
				<span class="link">
					<img class="image" src="<?php echo $item['image']; ?>" alt="<?php echo $item['title']; ?>">
				</span>
			</div>
		</a>
	<?php } ?>
    </div>
</div>
