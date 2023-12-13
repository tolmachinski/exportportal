<script src="<?php echo fileModificationTime('public/plug/hidemaxlistitem-1-3-4/hideMaxListItem-min.js'); ?>"></script>
<script src="<?php echo fileModificationTime('public/plug/zabuto-calendar/js/zabuto_calendar.min.js'); ?>"></script>
<script>
    $(function(){
        $('.hideMaxList').hideMaxListItems({
            'max': 6,
        });

        $("#my-calendar").zabuto_calendar({
            data: <?php echo json_encode($events_date); ?>,
            weekstartson: 0,
            nav_icon: {
                prev: '<i class="ep-icon ep-icon_arrow-left"></i>',
                next: '<i class="ep-icon ep-icon_arrow-right"></i>'
            },
            action: function () {
                if($(this).hasClass('event')){
                    var date = $('#' + this.id).data('date');
                    var template = '<?php echo __CURRENT_SUB_DOMAIN_URL . $links_tpl['date']; ?>';
                    document.location.href = template.replace('._-RPC._-', date);
                }
            }
        });
    });
</script>

<?php if (!empty($search_params)) { ?>
    <h3 class="minfo-sidebar-ttl">
        <span class="minfo-sidebar-ttl__txt">Active filters</span>
    </h3>

    <div class="minfo-sidebar-box">
        <div class="minfo-sidebar-box__desc">
            <ul class="minfo-sidebar-params">
                <?php foreach ($search_params as $item) { ?>
                    <li class="minfo-sidebar-params__item">
                        <div class="minfo-sidebar-params__ttl">
                            <div class="minfo-sidebar-params__name"><?php echo $item['title']?></div>
                            <a class="minfo-sidebar-params__close ep-icon ep-icon_remove-stroke" href="<?php echo $item['link']?>"></a>
                        </div>

                        <ul class="minfo-sidebar-params__sub">
                            <li class="minfo-sidebar-params__sub-item">
                                <div class="minfo-sidebar-params__sub-ttl"><?php echo $item['param']; ?></div>
                                <a class="minfo-sidebar-params__sub-close ep-icon ep-icon_remove-stroke" href="<?php echo $item['link']?>"></a>
                            </li>
                        </ul>
                    </li>
                <?php } ?>

                <li>
                    <a class="btn btn-light btn-block txt-blue2" href="<?php echo __CURRENT_SUB_DOMAIN_URL . 'events'; ?>">Clear all</a>
                </li>
            </ul>
        </div>
    </div>
<?php } ?>

<h3 class="minfo-sidebar-ttl">
    <span class="minfo-sidebar-ttl__txt">Search</span>
</h3>

<div class="minfo-sidebar-box">
	<div class="minfo-sidebar-box__desc">
		<form class="minfo-form mb-0" action="<?php echo get_dynamic_url($search_params_links_tpl['keywords'], __CURRENT_SUB_DOMAIN_URL); ?>" method="get">
            <input class="minfo-form__input2" type="text" name="keywords" placeholder="Keywords">
			<button class="btn btn-dark btn-block minfo-form__btn2" type="submit">Search</button>
		</form>
	</div>
</div>

<h3 class="minfo-sidebar-ttl">
    <span class="minfo-sidebar-ttl__txt">Type</span>
</h3>

<div class="minfo-sidebar-box">
    <div class="minfo-sidebar-box__desc">
        <ul class="minfo-sidebar-box__list">
            <?php foreach ($types as $type) { ?>
                <li class="minfo-sidebar-box__list-item">
                    <a class="minfo-sidebar-box__list-link" href="<?php echo replace_dynamic_uri(strForURL($type['event_type_name'] . ' ' . $type['id']), $links_tpl['type'], __CURRENT_SUB_DOMAIN_URL); ?>">
                        <?php echo $type['event_type_name']; ?>
                    </a>
                    <span class="minfo-sidebar-box__list-counter">(<?php echo isset($events_types_counters[$type['id']]) ? $events_types_counters[$type['id']]['counter'] : 0; ?>)</span>
                </li>
            <?php } ?>
        </ul>
    </div>
</div>

<h3 class="minfo-sidebar-ttl">
	<span class="minfo-sidebar-ttl__txt">Months</span>
</h3>


<?php
$current_year = date('Y');
$current_month = date('n');

$months_from_current = array();

for ($i = $current_month - 1; $i < count($months); $i++) {
    $months[$i]['year'] = $current_year;
    $months_from_current[] = $months[$i];
}

for ($i = 0; $i < $current_month - 1; $i++) {
    $months[$i]['year'] = $current_year + 1;
    $months[$i]['name'] .=  ' ' . ($current_year + 1);
    $months_from_current[] = $months[$i];
}

?>

<div class="minfo-sidebar-box">
	<div class="minfo-sidebar-box__desc">
		<ul class="minfo-sidebar-box__list hideMaxList">
            <?php
                foreach ($months_from_current as $month) {
                $k = $month['id'] . '-' . $month['year'];
            ?>
                <li class="minfo-sidebar-box__list-item">
                    <a class="minfo-sidebar-box__list-link" href="<?php echo replace_dynamic_uri($k, $links_tpl['month'], __CURRENT_SUB_DOMAIN_URL); ?>">
                        <?php echo $month['name'] ; ?>
                    </a>
                    <span class="minfo-sidebar-box__list-counter">(<?php echo isset($events_months_counters[$k]) ? $events_months_counters[$k]['counter'] : 0; ?>)</span>
                </li>
            <?php } ?>
		</ul>
	</div>
</div>

<div id="my-calendar"></div>

<?php if (!empty($other_countries)) { ?>
<h3 class="minfo-sidebar-ttl">
	<span class="minfo-sidebar-ttl__txt">Other countries</span>
</h3>

<div class="minfo-sidebar-box">
	<div class="minfo-sidebar-box__desc">
		<ul class="minfo-sidebar-box__list hideMaxList">
            <?php foreach ($other_countries as $other_country) { ?>
                <li class="minfo-sidebar-box__list-item">
                    <a class="minfo-sidebar-box__list-link w-160" href="<?php echo getSubDomainURL($other_country['country_alias']); ?>/events">
                        <?php echo $other_country['country']; ?>
                    </a>
                    <span class="minfo-sidebar-box__list-counter">(<?php echo isset($events_countries_counters[$other_country['id_country']]) ? $events_countries_counters[$other_country['id_country']]['counter'] : 0; ?>)</span>
                </li>
            <?php } ?>
		</ul>
	</div>
</div>
<?php } ?>
