<script type="text/javascript">
    $(document).ready(function () {

        $('#search_directory_form').on('submit', function (e) {
            e.preventDefault();
            var $form = $(this).closest('form');
            var form_action = $form.attr('action');

            var val = $form.find('input[name="keywords"]').val();
            var keywords = val.replace(/(\s)+/g, "$1");
            if (keywords != '' && keywords != '+')
                form_action += '?keywords=' + keywords;

            window.location = form_action;
        });

        <?php if(isset($work_in_continent)){?>
        $('#work-in-continent .minfo-sidebar-mlist__item[data-continent=<?php echo $work_in_continent;?>]').addClass('active')
            .find('.ep-icon').toggleClass('ep-icon_arrow-right ep-icon_arrow-down');
        <?php }?>

        <?php if(isset($country_continent)){?>
        $('#country-continent .minfo-sidebar-mlist__item[data-continent=<?php echo $country_continent;?>]').addClass('active')
            .find('.ep-icon').toggleClass('ep-icon_arrow-right ep-icon_arrow-down');
        <?php }?>
    });
</script>

<?php if (!empty($search_params) || !empty($search_attr_params)) { ?>
    <div class="minfo-sidebar-ttl">
        <h2 class="minfo-sidebar-ttl__txt">Active Filters</h2>
    </div>

    <div class="minfo-sidebar-box">
        <div class="minfo-sidebar-box__desc">
            <ul class="minfo-sidebar-params">
                <?php foreach ($search_params as $item) { ?>
                    <li class="minfo-sidebar-params__item">
                        <div class="minfo-sidebar-params__ttl">
                            <div class="minfo-sidebar-params__name"><?php echo $item['param']; ?>:</div>
                            <a class="minfo-sidebar-params__close ep-icon ep-icon_remove-stroke" href="<?php echo $item['link']; ?>"></a>
                        </div>
                        <ul class="minfo-sidebar-params__sub">
                            <li class="minfo-sidebar-params__sub-item">
                                <div class="minfo-sidebar-params__sub-ttl"><?php echo $item['title']; ?></div>
                            </li>
                        </ul>
                    </li>
                <?php } ?>
                <li>
                    <a class="btn btn-light btn-block txt-blue2" href="<?php echo __SITE_URL; ?>shippers/directory">Clear all</a>
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
        <form id="search_directory_form" action="<?php echo $curr_link; ?>">
            <input type="text" name="keywords" maxlength="50" placeholder="Keywords" value="<?php echo $keywords ?>">
            <button class="btn btn-dark btn-block minfo-form__btn2 mt-25" type="submit">Search</button>
        </form>
    </div>
</div>

<h3 class="minfo-sidebar-ttl">
    <span class="minfo-sidebar-ttl__txt">Origin country</span>
</h3>

<div class="minfo-sidebar-box">
    <div class="minfo-sidebar-box__desc">
        <div class="minfo-sidebar-box__inset clearfix">
            <ul id="country-continent" class="minfo-sidebar-mlist ">
                <?php foreach ($count_countries_by_continents as $key => $continent) { ?>
                    <li class="minfo-sidebar-mlist__item" data-continent="<?php echo $key; ?>">
                        <div class="minfo-sidebar-mlist__ttl">
							<span class="minfo-sidebar-mlist__ico ep-icon ep-icon_plus-stroke"></span>
                            <span class="minfo-sidebar-mlist__link"><?php echo $continent['name']; ?></span>
                            <span class="minfo-sidebar-mlist__counter">(<?php echo $continent['count']; ?>)</span>
                        </div>

                        <div class="minfo-sidebar-mlist__sub">
                            <?php foreach ($continent['countries'] as $country) { ?>
								<?php $countryReplace = strForURL($country['country'].' '.$country['id']);?>
                                <a class="minfo-sidebar-mlist__sub-item cur-pointer" href="<?php echo replace_dynamic_uri($countryReplace, $links_tpl['country'], '/shippers/directory') ?>">
                                    <span class="minfo-sidebar-mlist__sub-link"><?php echo $country['country']; ?></span>
                                    <span class="minfo-sidebar-mlist__sub-counter">(<?php echo $country['counter']; ?>)</span>
                                </a>
                            <?php } ?>
                        </div>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </div>
</div>


<h3 class="minfo-sidebar-ttl">
    <span class="minfo-sidebar-ttl__txt">Work in countries</span>
</h3>

<div class="minfo-sidebar-box">
    <div class="minfo-sidebar-box__desc">
        <div class="minfo-sidebar-box__inset clearfix">
            <ul id="work-in-continent" class="minfo-sidebar-mlist ">
                <?php foreach ($countries_by_continents as $key => $continent) { ?>
                    <li class="minfo-sidebar-mlist__item" data-continent="<?php echo $key; ?>">
                        <div class="minfo-sidebar-mlist__ttl">
							<span class="minfo-sidebar-mlist__ico ep-icon ep-icon_plus-stroke"></span>
                            <span class="minfo-sidebar-mlist__link"><?php echo $continent['name']; ?></span>
                        </div>

                        <div class="minfo-sidebar-mlist__sub">
                            <?php foreach ($continent['countries'] as $country) { ?>
								<?php $countryReplace = strForURL($country['country'].' '.$country['id']); ?>
                                <a class="minfo-sidebar-mlist__sub-item" href="<?php echo replace_dynamic_uri($countryReplace, $links_tpl['work_in_country'], '/shippers/directory') ?>">
                                    <span class="minfo-sidebar-mlist__sub-link"><?php echo $country['country']; ?></span>
                                </a>
                            <?php } ?>
                        </div>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </div>
</div>



