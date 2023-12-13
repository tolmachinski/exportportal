<script>
    const php_form_action = '<?php echo $library_search;?>';
    const php_library_name = '<?php echo $library_name;?>';

    var library_search = function(btn){
        var form = btn.closest('form');
        var country = form.find('select.country');
        var countryVal = country.val();
        var inputs = form.find('input[name="keywords"]');
        var keywords = inputs.length ? inputs.val().trim() : "";
        var form_action = "";

        if (php_library_name == 'library_consulates' && !countryVal || !inputs.length && !countryVal) {
            return systemMessages('Country is required.', 'error');
        }

        if (inputs.length && !keywords && !countryVal){
            return systemMessages('Please select at least one search parameters.', 'error');
        }

        // PREPARE KEYWORDS
        if (keywords && keywords.length < 3) {
            return systemMessages('Search terms must be at least 3 characters in length.', 'error');
        }

        inputs.val(keywords);

        // GET COUTRY VALUE
        form_action = php_form_action + (countryVal ? "/country/" + countryVal : "");
        // CHANGE FORM ACTION AND SUBMIT
        var query = form.serializeArray().filter(function (i) {
            if(i.name === "country") return;
            return i.value;
        });

        window.location.href = form_action + (query.length > 0 ? '?' + $.param(query) : '');
    }

    $(function() {
        $('#library_form_serch').on('submit', function(e){
            e.preventDefault();
            library_search($('#js-form-submit-btn'));
        });
    });
</script>

<?php if (!empty($keywords) || !empty($country_name)) {?>
    <h3 class="minfo-sidebar-ttl">
        <span class="minfo-sidebar-ttl__txt">Active Filters</span>
    </h3>

    <div class="minfo-sidebar-box">
        <div class="minfo-sidebar-box__desc">
            <ul class="minfo-sidebar-params">
                <?php if (!empty($keywords)) {?>
                    <li class="minfo-sidebar-params__item">
                        <div class="minfo-sidebar-params__ttl">
                            <div class="minfo-sidebar-params__name">Keywords:</div>
                        </div>

                        <ul class="minfo-sidebar-params__sub">
                            <li class="minfo-sidebar-params__sub-item">
                                <div class="minfo-sidebar-params__sub-ttl"><?php echo cleanOutput($keywords);?></div>
                                <a class="minfo-sidebar-params__sub-close ep-icon ep-icon_remove-stroke" href="<?php echo $link_to_reset_keywords;?>"></a>
                            </li>
                        </ul>
                    </li>
                <?php }?>
                <?php if (!empty($country_name)) {?>
                    <li class="minfo-sidebar-params__item">
                        <div class="minfo-sidebar-params__ttl">
                            <div class="minfo-sidebar-params__name">Country:</div>
                        </div>

                        <ul class="minfo-sidebar-params__sub">
                            <li class="minfo-sidebar-params__sub-item">
                                <div class="minfo-sidebar-params__sub-ttl"><?php echo $country_name;?></div>
                                <a class="minfo-sidebar-params__sub-close ep-icon ep-icon_remove-stroke" href="<?php echo $link_to_reset_country;?>"></a>
                            </li>
                        </ul>
                    </li>
                <?php }?>
                <li>
                    <a class="btn btn-light btn-block txt-blue2" href="<?php echo $link_to_reset_all_filters;?>">Clear all</a>
                </li>
            </ul>
        </div>
    </div>
<?php }?>

<?php if (empty($hide_search_block)) { ?>
    <h3 class="minfo-sidebar-ttl mt-50">
        <span class="minfo-sidebar-ttl__txt">Search</span>
    </h3>
    <div class="minfo-sidebar-box">
        <div class="minfo-sidebar-box__desc">
            <form method="get" id="library_form_serch">
                <?php if (empty($hide_search_keywords)) { ?>
                    <input placeholder="Keywords" class="minfo-form__input2" type="text" name="keywords" maxlength="50" value="<?php echo cleanOutput($keywords);?>">
                <?php } ?>
                <?php if (!empty($list_countries)) {?>
                    <select class="minfo-form__input2 country" name="country" id="port_city">
                        <?php
                            $port_countries = array_map(function($country){
                                $country['country_url'] = strForURL($country['country'] . ' ' . $country['id']);

                                return $country;
                            }, $list_countries);

                            echo getCountrySelectOptions($port_countries, empty($country_selected) ? 0 : id_from_link($country_selected), array('value' => 'country_url'));
                        ?>
                    </select>
                <?php } ?>
                <button class="btn btn-dark btn-block minfo-form__btn2 call-function" id="js-form-submit-btn" data-callback="library_search" type="button">Search</button>
            </form>
        </div>
    </div>
<?php } ?>

<?php if (!empty($standards) && count($standards) > 1) { ?>
    <h3 class="minfo-sidebar-ttl">
        <span class="minfo-sidebar-ttl__txt">Standards</span>
    </h3>
    <div class="minfo-sidebar-box">
        <div class="minfo-sidebar-box__desc">
            <ul class="minfo-sidebar-box__list">
                <?php foreach($standards as $standard) {?>
                    <li class="minfo-sidebar-box__list-item">
                        <a class="minfo-sidebar-box__list-link js-achor-link" href="<?php echo "#{$standard['standard_link']}"; ?>"><?php echo $standard['standard_title']; ?></a>
                    </li>
                <?php }?>
            </ul>
        </div>
    </div>
<?php } ?>

<?php views()->display('new/who_we_are_view');?>

<?php views()->display('new/subscribe/subscribe_view'); ?>
