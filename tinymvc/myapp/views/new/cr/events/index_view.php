<a class="btn btn-primary btn-panel-left fancyboxSidebar fancybox mb-25" data-title="Category" href="#main-flex-card__fixed-left">
	<i class="ep-icon ep-icon_items"></i>
	Sidebar
</a>

<div class="title-public pt-0">
	<h2 class="title-public__txt">Events Brand Ambassadors</h2>
</div>

<div class="minfo-save-search pb-25">

	<div class="minfo-save-search__item">
		<span class="minfo-save-search__ttl">Sort by</span>
		<div class="dropdown show dropdown--select">
            <a class="dropdown-toggle" href="#" role="button" id="ambasadorSortByLinks" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <?php echo $sort_by_links['items'][$sort_by_links['selected']]['title']; ?>
                <i class="ep-icon ep-icon_arrow-down"></i>
            </a>

            <div class="dropdown-menu" aria-labelledby="ambasadorSortByLinks">
                <?php foreach ($sort_by_links['items'] as $sort_by_link_key => $sort_by_link) { ?>
                    <a class="dropdown-item" href="<?php echo $sort_by_link['url']; ?>"><?php echo $sort_by_link['title']; ?></a>
                <?php } ?>
            </div>
		</div>
	</div>
</div>

<ul class="cr-events-list">

    <?php if (empty($events)) { ?>
        <div class="default-alert-b"><i class="ep-icon ep-icon_remove-circle"></i> <span>No events found</span></div>
    <?php } else {
        foreach($events as $key => $event_item){
            ?>
            <li class="cr-events-list__item">
                <div class="cr-events-list__inner flex-card">
                    <div class="cr-events-list__img flex-card__fixed image-card2">
                        <span
                            class="link call-function call-action"
                            data-callback="callMoveByLink"
                            data-js-action="link:move-by-link"
                            data-link="<?php echo get_dynamic_url('event/' . strForURL($event_item['event_name']) . '-' . $event_item['id_event'], __CURRENT_SUB_DOMAIN_URL); ?>"
                        >
                            <img class="image" src="<?php echo __IMG_URL.getImage('public/img/cr_event_images/' . $event_item['id_event'] . '/thumb_200xR_' . $event_item['event_image'], 'public/img/no_image/no-image-166x138.png');?>" alt="<?php echo $event_item['event_name']?>"/>
                        </span>
                    </div>
                    <div class="cr-events-list__detail flex-card__float">
                        <div class="cr-events-list__params">
                            <div class="cr-events-list__params-l">
                                <h4 class="cr-events-list__ttl">
                                    <a class="link" href="<?php echo get_dynamic_url('event/' . strForURL($event_item['event_name']) . '-' . $event_item['id_event'], __CURRENT_SUB_DOMAIN_URL); ?>">
                                        <?php echo $event_item['event_name'];?>
                                    </a>
                                </h4>
                                <div class="cr-events-list__rep">
                                    <i class="ep-icon ep-icon_user"></i>
                                    <span class="name">Company Representatives Attending - </span>
                                    <?php echo $event_item['event_count_ambassadors'];?>
                                </div>
                                <div class="cr-events-list__ba"><?php echo $event_item['city']['name']; ?></div>
                            </div>

                            <div class="cr-events-list__params-r">
                                <?php
                                    $start_month = formatDate($event_item['event_date_start'], 'M');
                                    $end_month = formatDate($event_item['event_date_end'], 'M');
                                    $start_day = formatDate($event_item['event_date_start'],'d');
                                    $end_day = formatDate($event_item['event_date_end'],'d');
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

                        <div class="cr-events-list__desc">
                            <?php echo $event_item['event_short_description'];?>
                        </div>
                    </div>
                </div>

                <div class="cr-events-list__hidden">
                    <h4
                        class="cr-events-list__ttl call-function call-action"
                        data-callback="callMoveByLink"
                        data-js-action="link:move-by-link"
                        data-link="<?php echo get_dynamic_url('event/' . strForURL($event_item['event_name']) . '-' . $event_item['id_event'], __CURRENT_SUB_DOMAIN_URL); ?>"
                    >
                        <span class="link"><?php echo $event_item['event_name'];?></span>
                    </h4>

                    <div class="cr-events-list__desc">
                        <?php echo $event_item['event_short_description'];?>
                    </div>
                </div>

                <?php //if (!empty($ambassadors[$event_item['id_event']])) { ?>
                    <!-- <div class="mt-50 ambassador-blocks">
                        <?php //foreach ($ambassadors[$event_item['id_event']] as $ambassador) {
                            //tmvc::instance()->controller->view->display('new/cr/representative_user_view', array(
                                //'cr_user' => $ambassador
                            //));
                        //} ?>
                    </div> -->
                <?php //} ?>
            </li>
            <?php
        }
    }
    ?>
</ul>

<div class="pt-10 clearfix">
	<div class="pull-right">
        <?php tmvc::instance()->controller->view->display("new/paginator_view"); ?>
    </div>
</div>
