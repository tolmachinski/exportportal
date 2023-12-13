<div class="wr-modal-flex">
    <form
        id="add-estimate--form"
        class="modal-flex__form validateModal inputs-40"
        data-callback="shippersEstimatesRequestsShippingByItemFormCallBack"
        action="<?php echo $action; ?>"
    >
        <input type="hidden" name="type" value="<?php echo $type; ?>">
        <div class="modal-flex__content">
            <div class="container-fluid-modal">
                <div class="row mt-10 start_request">
                    <div class="col-12">
                        <label class="input-label"><h2>From</h2></label>
                        <?php echo !empty($location) ? cleanOutput($location) : '&mdash;'; ?>
                    </div>

                    <div class="col-12">
                        <label class="input-label"><h2>To</h2></label>

                        <div class="to-shipping form_select">
                            <div id="country_td" data-select_action="to">
                                <label class="input-label input-label--required">Country</label>
                                <select name="port_country_to" id="add-estimate--form-field--to-country" class="validate[required] country" data-select_action="to">
                                    <?php echo getCountrySelectOptions($port_country, $user_info['country']);?>
                                </select>
                            </div>

                            <div id="state_td" data-select_action="to">
                                <label class="input-label input-label--required">State / Region</label>
                                <select name="states_to"
                                    id="add-estimate--form-field--to-region"
                                    class="validate[required] states"
                                    data-select_action="to">
                                    <option value="" disabled selected>Select state / region</option>
                                    <?php if(!empty($states)){ ?>
                                        <?php foreach($states as $state){ ?>
                                            <option value="<?php echo $state['id'];?>" <?php echo selected($user_info['state'], $state['id']); ?>>
                                                <?php echo cleanOutput($state['state']); ?>
                                            </option>
                                        <?php } ?>
                                    <?php } ?>
                                </select>
                            </div>

                            <?php $is_city_disabled = empty($states) || empty(array_filter($states, function($state) use ($user_info) {
                                return (int) $user_info['state'] === (int) $state['id'];
                            })); ?>
                            <div class="wr-select2-h35" id="city_td" data-select_action="to">
                                <label class="input-label input-label--required">City</label>
                                <select name="port_city_to"
                                    id="add-estimate--form-field--to-city"
                                    class="validate[required]"
                                    data-select_action="to"
                                    data-selected="<?php echo arrayGet($user_info, 'state'); ?>"
                                    <?php echo $is_city_disabled ? 'disabled' : '';?>>
                                    <option value="-2" disabled selected>Select state / region first</option>
                                    <option value="-1" disabled>Select city</option>
                                    <?php if(isset($city_selected) && !empty($city_selected)){ ?>
                                        <option value="<?php echo $city_selected['id']; ?>" selected>
                                            <?php echo cleanOutput($city_selected['city']); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div>
                                <label class="input-label input-label--required">Postal code</label>
                                <input type="text"
                                    maxlength="20"
                                    name="zip_code_to"
                                    id="add-estimate--form-field--to-zip"
                                    class="validate[required,custom[zip_code],maxSize[20]] zip_code_to"
                                    value="<?php echo cleanOutput($user_info['zip']); ?>"
                                    placeholder="Enter the postal code">
                            </div>
                        </div>
                    </div>

                    <?php if(!empty($items_info)) { ?>
                        <div class="col-12">
                            <label class="input-label input-label--required">Title</label>
                            <div class="input-group">
                                <?php if(count($items_info) > 1) { ?>
                                    <input type="text"
                                        name="title"
                                        id="add-estimate--form-field--title"
                                        class="form-control validate[required,maxSize[250]]"
                                        value="<?php echo  cleanOutput(strLimit(sprintf("%s - Total item(s): %d", $company['name_company'], count($items_info)), 245)); ?>"
                                        placeholder="Enter the title">
                                <?php } else { ?>
                                    <input type="text"
                                        name="title"
                                        id="add-estimate--form-field--title"
                                        class="form-control validate[required,maxSize[250]]"
                                        value="<?php echo cleanOutput(strLimit("{$company['name_company']} - {$items_info[0]['title']}", 245)); ?>"
                                        placeholder="Enter the title">
                                <?php } ?>
                                <div class="input-group-append">
                                    <button class="btn btn-primary info-dialog"
                                        type="button"
                                        data-title="What is this?"
                                        data-message="This field is used to make the further search of your estimate easier. It will be visible only to you.">
                                        <i class="ep-icon ep-icon_info-stroke txt-white"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php } ?>

                    <div class="col-12">
                        <label class="input-label">Comment</label>
                        <textarea name="comments"
                            id="add-estimate--form-field--comment"
                            class="validate[maxSize[500]] textcounter-estimate_comment"
                            placeholder="Enter the comment"
                            data-max="500"></textarea>
                    </div>

                    <div class="col-12">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th class="w-100">
                                        <label class="input-label input-label--required">Quantity</label>
                                    </th>
                                    <th>
                                        <label class="input-label">Product(s)</label>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($items_info as $item){?>
                                    <tr>
                                        <td class="vam">
                                            <input type="text"
                                                name="item_quantity[<?php echo $item['id'];?>]"
                                                id="add-estimate--form-field--quantity-<?php echo $item['id']; ?>"
                                                class="tac validate[required,max[<?php echo $item['max_sale_q']?>],min[1],custom[positive_integer]]"
                                                value="<?php echo cleanOutput($items_quantity[$item['id']]);?>"
                                                placeholder="0">
                                        </td>
                                        <td class="vam">
                                            <a href="<?php echo makeItemUrl($item['id'], $item['title']); ?>" target="_blank">
                                                <?php echo cleanOutput($item['title'])?>
                                            </a>
                                        </td>
                                    </tr>
                                <?php }?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" type="submit">Send request</button>
            </div>
        </div>
    </form>
</div>
<script type="text/javascript">
    $(function() {
        var formatShippingEstimateCity = function  (repo) {
            return repo.loading ? repo.text: repo.name;
        };
        var formatShippingEstimateCitySelection = function  (repo) {
            return repo.name || repo.text;
        };
        var initshippingEstimateDepartureCity = function (city){
            city.select2({
                ajax: {
                    type: 'POST',
                    url: "location/ajax_get_cities",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            page: params.page,
                            search: params.term,
                            state: city.data('selected') || null,
                        };
                    },
                    processResults: function (data, params) {
                        params.page = params.page || 1;

                        return {
                            results: data.items,
                            pagination: {
                                more: (params.page * data.per_p) < data.total_count
                            }
                        };
                    }
                },
                width: '100%',
                theme: "default ep-select2-h30",
                minimumInputLength: 2,
                escapeMarkup: function(markup) { return markup; },
                templateResult: formatShippingEstimateCity,
                templateSelection: formatShippingEstimateCitySelection,
            });
        };
        var initshippingEstimateDestinationCity = function (city){
            city.select2({
                ajax: {
                    type: 'POST',
                    url: "location/ajax_get_cities",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            page: params.page,
                            search: params.term,
                            state: city.data('selected') || null,
                        };
                    },
                    processResults: function (data, params) {
                        params.page = params.page || 1;

                        return {
                            results: data.items,
                            pagination: {
                                more: (params.page * data.per_p) < data.total_count
                            }
                        };
                    }
                },
                width: '100%',
                theme: "default ep-select2-h30",
                minimumInputLength: 2,
                escapeMarkup: function(markup) { return markup; },
                templateResult: formatShippingEstimateCity,
                templateSelection: formatShippingEstimateCitySelection,
            });

            city.data('select2').$container.attr('id', 'select-city--formfield--estimate-container')
                .addClass('validate[required]')
                .setValHookType('selectCityEstimate');

            $.valHooks.selectCityEstimate = {
                get: function (el) {
                    return city.val() || [];
                },
                set: function (el, val) {
                    city.val(val);
                }
            };
        };
        var onSave = function (formNode, dataGrid){
            var form = $(formNode);
            var saveButton = form.find('button[type=submit]');
            var url = form.attr('action');
            var data = form.serializeArray();
            var onBeforeSave = function() {
                saveButton.prop('disabled', true);
                showLoader(form);
            };
            var onAfterSave = function() {
                saveButton.prop('disabled', false);
                hideLoader(form);
            };
            var onRequestSuccess = function (response) {
                systemMessages(response.message, 'message-' + response.mess_type);
                if('success' === response.mess_type){
                    closeFancyBox();
                }
            };

            onBeforeSave();
            $.post(url, data, null, 'json').done(onRequestSuccess).fail(onRequestError).always(function() {
                onAfterSave();
            });
        };
        var onCountryChange = function (country, region, city, postalCode) {
            city.val(-2).trigger('change').attr('disabled', true);
            postalCode.val(null);
            selectCountry(country, region).then(function() {
                region.find('option').first().attr('disabled', true);
            });
        };
        var onStateChange = function (region, city, postalCode) {
            city.val(-1).trigger('change').attr('disabled', false).data('selected', region.val() || null);
            postalCode.val(null);
        }

        var comment = $('#add-estimate--form-field--comment');
        var shippingEstimateDeparturePostalCode = $("#add-estimate--form-field--from-zip");
        var shippingEstimateDepartureCountry = $("#add-estimate--form-field--from-country");
        var shippingEstimateDepartureRegion = $("#add-estimate--form-field--from-region");
        var shippingEstimateDepartureCity = $("#add-estimate--form-field--from-city");
        var onDepartureCountryChange = onCountryChange.bind(
            null,
            shippingEstimateDepartureCountry,
            shippingEstimateDepartureRegion,
            shippingEstimateDepartureCity,
            shippingEstimateDeparturePostalCode
        );
        var onDepartureStateChange = onStateChange.bind(
            null,
            shippingEstimateDepartureRegion,
            shippingEstimateDepartureCity,
            shippingEstimateDeparturePostalCode
        );
        var shippingEstimateDestinationPostalCode = $("#add-estimate--form-field--to-zip");
        var shippingEstimateDestinationCountry = $("#add-estimate--form-field--to-country");
        var shippingEstimateDestinationRegion = $("#add-estimate--form-field--to-region");
        var shippingEstimateDestinationCity = $("#add-estimate--form-field--to-city");
        var onDestinationCountryChange = onCountryChange.bind(
            null,
            shippingEstimateDestinationCountry,
            shippingEstimateDestinationRegion,
            shippingEstimateDestinationCity,
            shippingEstimateDestinationPostalCode
        );
        var onDestinationStateChange = onStateChange.bind(
            null,
            shippingEstimateDestinationRegion,
            shippingEstimateDestinationCity,
            shippingEstimateDestinationPostalCode
        );
        var counterOptions = {
            countDown: true,
			countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
			countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'}),
        };

        comment.textcounter(counterOptions);
		initshippingEstimateDepartureCity(shippingEstimateDepartureCity);
        initshippingEstimateDestinationCity(shippingEstimateDestinationCity);
        shippingEstimateDepartureCountry.on('change', onDepartureCountryChange);
        shippingEstimateDepartureRegion.on('change', onDepartureStateChange);
        shippingEstimateDestinationCountry.on('change', onDestinationCountryChange);
        shippingEstimateDestinationRegion.on('change', onDestinationStateChange);

        window.shippersEstimatesRequestsShippingByItemFormCallBack = onSave;
    });
</script>
