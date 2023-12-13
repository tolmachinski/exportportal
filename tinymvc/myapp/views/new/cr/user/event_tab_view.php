<?php if(!empty($assigned_events)){?>
	<?php foreach($assigned_events as $assigned_event){?>
		<div class="middle-event col-12 col-md-12 col-lg-6 flex-card" itemscope itemtype="http://schema.org/Event">
			<div class="display-n">
				<span itemprop="startDate" content="<?php echo formatDate($assigned_event['event_date_start']);?>"><?php echo formatDate($assigned_event['event_date_start']);?></span>
				<span itemprop="endDate" content="<?php echo formatDate($assigned_event['event_date_end']);?>"><?php echo formatDate($assigned_event['event_date_end']);?></span>
				<img itemprop="image" src="<?php echo __IMG_URL.getImage('public/img/cr_event_images/' . $assigned_event['id_event'] . '/thumb_200xR_' . $assigned_event['event_image'], 'public/img/no_image/no-image-80x80.png');?>" />
				
				<span itemprop="location" itemscope itemtype="http://schema.org/Place">
					<span itemprop="name"><?php echo $assigned_event['user_location']; ?></span>
					<span itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">
						<span itemprop="streetAddress"><?php echo $assigned_event['user_location'];?></span>
						<span itemprop="addressLocality"><?php echo $assigned_event['country'];?></span>,
						<span itemprop="postalCode"><?php echo $assigned_event['country'];?></span>
					</span>
				</span>

				<span itemprop="offers" itemscope itemtype="http://schema.org/Offer">
					<a itemprop="url" href="<?php echo get_dynamic_url('/event/' . $assigned_event['event_url'], getSubDomainURL($assigned_event['country_alias'])); ?>"><?php echo $assigned_event['event_name'];?></a>
					<span itemprop="price">0</span>
					<span itemprop="priceCurrency">USD</span>
					<link itemprop="availability" href="http://schema.org/InStock" />
					<span itemprop="validFrom"><?php echo $assigned_event['event_date_start']; ?></span>
				</span>

				<span itemprop="performer" itemscope itemtype="http://schema.org/PerformingGroup">
					<span itemprop="name"><?php echo $assigned_event['name_company'];?></span>
				</span>
			</div>

			<div class="middle-event__img2 flex-card__fixed image-card2">
				<span class="link">
					<img class="image" src="<?php echo __IMG_URL.getImage('public/img/cr_event_images/' . $assigned_event['id_event'] . '/thumb_200xR_' . $assigned_event['event_image'], 'public/img/no_image/no-image-80x80.png');?>" alt="event" />
				</span>
			</div>

			<div class="middle-event__detail flex-card__float">
				<div>
					<a class="middle-event__category lh-24" href="<?php echo getSubDomainURL($assigned_event['country_alias'], 'events/type/'.strForUrl($assigned_event['event_type_name'].' '.$assigned_event['event_id_type'])); ?>" ><?php echo $assigned_event['event_type_name'];?></a>
				</div>

				<h2 class="middle-event__ttl">
					<a class="link" href="<?php echo get_dynamic_url('/event/' . $assigned_event['event_url'], getSubDomainURL($assigned_event['country_alias'])); ?>">
						<span itemprop="name"><?php echo $assigned_event['event_name'];?></span>
					</a>
				</h2>

				<div class="middle-event__country">
					<a class="link" href="<?php echo getSubDomainURL($assigned_event['country_alias'], 'events'); ?>">
						<?php echo $assigned_event['user_location'];?>
					</a>
				</div>

				<div class="middle-event__date">
					<?php echo getTimeInterval($assigned_event['event_date_start'], $assigned_event['event_date_end']);?>
				</div>
			</div>
		</div>
	<?php }?>
<?php } else{?>
	<div class="col-12">
		<div class="info-alert-b">
			<i class="ep-icon ep-icon_info-stroke"></i> 
			<span>No events.</span>
		</div>
	</div>
<?php }?>