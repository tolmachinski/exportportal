<?php //if(!empty($search_params) || !empty($search_attr_params)){?>
<!-- <div class="minfo-sidebar-ttl">
	<h2 class="minfo-sidebar-ttl__txt">Active Filters</h2>
</div>

<div class="minfo-sidebar-box">
	<div class="minfo-sidebar-box__desc">
		<ul class="minfo-sidebar-params">
			<?php //foreach($search_params as $item){?>
				<li class="minfo-sidebar-params__item">
					<div class="minfo-sidebar-params__ttl">
						<div class="minfo-sidebar-params__name"><?php //echo $item['title']?>:</div>
						<a class="minfo-sidebar-params__close ep-icon ep-icon_remove-stroke" href="<?php //echo $item['link']?>"></a>
					</div>
					<?php// if(!empty($item['sub_params'])){?>
						<ul class="minfo-sidebar-params__sub">
							<?php //foreach($item['sub_params'] as $search_sub_param){?>
								<li class="minfo-sidebar-params__sub-item">
									<div class="minfo-sidebar-params__sub-ttl"><?php //echo $search_sub_param['title']?></div>
									<a class="minfo-sidebar-params__sub-close ep-icon ep-icon_remove-stroke" href="<?php //echo $search_sub_param['link'];?>"></a>
								</li>
							<?php //}?>
						</ul>
					<?php //}?>
				</li>
			<?php //} ?>
		</ul>
	</div>
</div> -->
<?php //} ?>

<!-- <div class="minfo-sidebar-box">
    <div class="minfo-sidebar-box__desc">
        <form class="minfo-form mb-0" action="<?php //echo get_dynamic_url($search_params_links_tpl['keywords'], __CURRENT_SUB_DOMAIN_URL);?>" method="GET">
            <input class="minfo-form__input2" type="text" name="keywords" placeholder="Search keywords">
            <button class="btn btn-dark btn-block minfo-form__btn2" type="submit">Search</button>
        </form>
    </div>
</div> -->

<h3 class="minfo-sidebar-ttl">
	<span class="minfo-sidebar-ttl__txt">WANT TO BE A BRAND AMBASSADOR?</span>
</h3>

<a class="btn btn-primary btn-block fancybox.ajax fancyboxValidateModal" href="<?php echo __CURRENT_SUB_DOMAIN_URL . 'register/popup_forms/brand_ambassador/' . $cr_domain['id_domain'];?>" data-title="WANT TO BE A BRAND AMBASSADOR?">APPLY NOW</a>

<h3 class="minfo-sidebar-ttl mt-40">
	<span class="minfo-sidebar-ttl__txt">REGISTER ON<br>EXPORT PORTAL</span>
</h3>

<a class="btn btn-outline-dark btn-block" href="<?php echo get_static_url('register/buyer');?>">BUYER</a>
<a class="btn btn-outline-dark btn-block" href="<?php echo get_static_url('register/seller');?>">SELLER</a>
<a class="btn btn-outline-dark btn-block" href="<?php echo get_static_url('register/manufacturer');?>">MANUFACTURER</a>
<a class="btn btn-outline-dark btn-block mb-40" href="<?php echo __SHIPPER_URL . 'register/ff'; ?>">FREIGHT FORWARDER</a>

<?php //if(!empty($cr_types)){?>
    <!-- <h3 class="minfo-sidebar-ttl">
        <span class="minfo-sidebar-ttl__txt">Type</span>
    </h3>

    <div class="minfo-sidebar-box">
        <div class="minfo-sidebar-box__desc">
            <ul class="minfo-sidebar-box__list">
                <?php //if(!empty($cr_types)){?>
                    <?php //foreach($cr_types as $cr_type){?>
                        <li class="minfo-sidebar-box__list-item">
                            <a class="minfo-sidebar-box__list-link" href="<?php //echo replace_dynamic_uri($cr_type['gr_alias'], $links_tpl['type'], __CURRENT_SUB_DOMAIN_URL);?>">
                                <?php //echo $cr_type['gr_name'];?>
                            </a>
                            <span class="minfo-sidebar-box__list-counter">(<?php //echo $cr_type['total_users'];?>)</span>
                        </li>
                    <?php //}?>
                <?php //}?>
            </ul>
        </div>
    </div> -->
<?php //}?>

<?php if(!empty($cr_events)){?>

    <h3 class="minfo-sidebar-ttl">
        <span class="minfo-sidebar-ttl__txt">Events in <?php echo $cr_domain['country'];?></span>
    </h3>

    <div class="minfo-sidebar-box">
        <div class="minfo-sidebar-box__img">
            <img class="image" src="<?php echo __IMG_URL.getImage('public/img/cr_event_images/' . $cr_events[0]['id_event'] . '/' . $cr_events[0]['event_image'], 'public/img/no_image/no-image-512x512.png');?>" alt="Event">
        </div>
        <div class="minfo-sidebar-box__desc">
            <ul class="hide-max-list minfo-sidebar-box__list">
                <?php foreach($cr_events as $cr_event){?>
                    <li class="minfo-sidebar-box__list-item flex-d--c">
                        <a class="minfo-sidebar-box__list-link lh-18" href="<?php echo get_dynamic_url('event/'.$cr_event['event_url'], __CURRENT_SUB_DOMAIN_URL);?>">
                            <?php echo $cr_event['event_name'];?>
                        </a>
                        <div class="tt-uppercase w-100pr fs-14 txt-medium">
                            <?php
                                $start_month = formatDate($cr_event['event_date_start'], 'M');
                                $end_month = formatDate($cr_event['event_date_end'], 'M');
                                $start_day = formatDate($cr_event['event_date_start'],'d');
                                $end_day = formatDate($cr_event['event_date_end'],'d');
                            ?>
                            <?php if($start_month != $end_month){?>
                                    <?php echo $start_month; ?> <?php echo $start_day; ?>
                                    -
                                    <?php echo $end_month; ?> <?php echo $end_day; ?>
                            <?php } else { ?>
                                    <?php echo $start_month; ?>

                                    <?php
                                    if ($start_day == $end_day) {
                                        echo $start_day;
                                    } else {
                                        echo $start_day . ' - ' . $end_day;
                                    }?>
                            <?php } ?>
                        </div>
                    </li>
                <?php }?>
            </ul>

            <a class="btn btn-light btn-block txt-blue2 mt-20" href="<?php echo get_dynamic_url('events', __CURRENT_SUB_DOMAIN_URL);?>">More</a>
        </div>
    </div>
<?php }?>

<?php if(!empty($blogs)){?>
	<h3 class="minfo-sidebar-ttl">
		<span class="minfo-sidebar-ttl__txt">BLOGS</span>
	</h3>

	<div class="minfo-sidebar-box">
        <div class="minfo-sidebar-box__img">
            <img class="image" src="<?php echo $blogs[0]['imagePath']?>" alt="<?php echo $blogs[0]['title'];?>">
        </div>

		<div class="minfo-sidebar-box__desc">
			<ul class="minfo-sidebar-box__list">
				<?php foreach ($blogs as $blog_item) {?>
					<li class="minfo-sidebar-box__list-item">
						<a class="minfo-sidebar-box__list-link" href="<?php echo getBlogUrl($blog_item);?>">
							<?php echo $blog_item['title'];?>
						</a>
					</li>
				<?php }?>
			</ul>

            <a class="btn btn-light btn-block txt-blue2 mt-20" href="<?php echo __BLOG_URL . 'country/' . $cr_domain['country_alias'].'-'.$cr_domain['id_country'];?>?lang=en">More</a>
		</div>
	</div>
<?php }?>

<?php //if(!empty($other_countries)){?>
    <!-- <h3 class="minfo-sidebar-ttl">
        <span class="minfo-sidebar-ttl__txt">Other Countries</span>
    </h3>

    <div class="minfo-sidebar-box">
        <div class="minfo-sidebar-box__desc">
            <ul class="hide-max-list minfo-sidebar-box__list">
                <?php //foreach($other_countries as $other_country){?>
                    <li class="minfo-sidebar-box__list-item">
                        <a class="minfo-sidebar-box__list-link w-160" href="<?php //echo getSubDomainURL($other_country['country_alias']);?>">
                            <?php //echo $other_country['country'];?>
                        </a>
                    </li>
                <?php //}?>
            </ul>
        </div>
    </div> -->
<?php //}?>
