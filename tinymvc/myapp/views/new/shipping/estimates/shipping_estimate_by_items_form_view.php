<div class="js-modal-flex wr-modal-flex inputs-40">
    <form
        id="add-estimate--form"
        class="modal-flex__form validateModal inputs-40"
        data-callback="estimateShippingByItemsFormCallBack"
        action="<?php echo $action; ?>"
    >
        <input type="hidden" name="type" value="<?php echo $type; ?>">
        <div class="modal-flex__content">
            <div class="container-fluid-modal">
                <div class="row mt-10 start_request">
                    <div class="col-12 col-md-6">
                        <h2>Shipping from</h2>
                        <div class="from-shipping form_select">
                            <div id="item_country_td" data-select_action="from">
                                <label class="input-label input-label--required">Country</label>
                                <select name="port_country_from"
                                    id="add-estimate--form-field--from-country"
                                    class="validate[required] country"
                                    data-select_action="from">
                                    <option value="">Select Country</option>
                                    <?php if (!empty($port_country)) { ?>
                                        <?php foreach($port_country as $row) { ?>
                                            <option value='<?php echo $row['id']; ?>' <?php echo selected($items_info[0]['p_country'], $row['id']); ?>>
                                                <?php echo $row['country']; ?>
                                            </option>
                                        <?php } ?>
                                    <?php } ?>
                                </select>
                            </div>
                            <div id="item_state_td" data-select_action="from">
                                <label class="input-label input-label--required">State</label>
                                <select name="states_from"
                                    id="add-estimate--form-field--from-state"
                                    class="validate[required] states"
                                    data-select_action="from">
                                    <option value="">Select state or province</option>
                                    <?php if(!empty($item_states)){ ?>
                                        <?php foreach($item_states as $state){ ?>
                                            <option value="<?php echo $state['id']; ?>" <?php echo selected($items_info[0]['state'], $state['id']); ?>>
                                                <?php echo $state['state']; ?>
                                            </option>
                                        <?php } ?>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="wr-select2-h35" id="item_city_td" data-select_action="from">
                                <label class="input-label input-label--required">City</label>
                                <select name="port_city_from"
                                    id="add-estimate--form-field--from-city"
                                    class="validate[required] item-select-city"
                                    data-select_action="from"
                                    data-selected="<?php echo arrayGet($items_info, "0.state"); ?>">
                                    <option value="">Select country first</option>
                                    <?php if(isset($item_city_selected) && !empty($item_city_selected)) { ?>
                                        <option value="<?php echo $item_city_selected['id']; ?>" selected>
                                            <?php echo $item_city_selected['city']; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div>
                                <label class="input-label input-label--required">Zip</label>
                                <input type="text"
                                    name="zip_code_from"
                                    id="add-estimate--form-field--from-zip"
                                    class="validate[required,custom[zip_code],maxSize[20]] zip_code_from"
                                    maxlength="20"
                                    value="<?php echo $items_info[0]['item_zip']; ?>">
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <h2>Recipient</h2>
                        <div class="to-shipping form_select">
                            <div id="country_td" data-select_action="to">
                                <label class="input-label input-label--required">Country</label>
                                <select name="port_country_to"
                                    id="add-estimate--form-field--to-country"
                                    class="validate[required] country"
                                    data-select_action="to">
                                    <option value="">Select Country</option>
                                    <?php foreach($port_country as $row) { ?>
                                        <option value='<?php echo $row['id']; ?>' <?php echo selected($user_info['country'], $row['id']); ?>>
                                            <?php echo $row['country']; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div id="state_td" data-select_action="to">
                                <label class="input-label input-label--required">State</label>
                                <select name="states_to"
                                    id="add-estimate--form-field--to-state"
                                    class="validate[required] states"
                                    data-select_action="to">
                                    <option value="">Select state or province</option>
                                    <?php if(!empty($states)){ ?>
                                        <?php foreach($states as $state){ ?>
                                            <option value="<?php echo $state['id'];?>" <?php echo selected($user_info['state'], $state['id']); ?>>
                                                <?php echo $state['state']; ?>
                                            </option>
                                        <?php } ?>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="wr-select2-h35" id="city_td" data-select_action="to">
                                <label class="input-label input-label--required">City</label>
                                <select name="port_city_to"
                                    id="add-estimate--form-field--to-city"
                                    class="validate[required]"
                                    data-select_action="to"
                                    data-selected="<?php echo arrayGet($user_info, 'state'); ?>">
                                    <option value="">Select country first</option>
                                    <?php if(isset($city_selected) && !empty($city_selected)){ ?>
                                        <option value="<?php echo $city_selected['id']; ?>" selected>
                                            <?php echo $city_selected['city']; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div>
                                <label class="input-label input-label--required">Zip</label>
                                <input type="text"
                                    name="zip_code_to"
                                    id="add-estimate--form-field--to-zip"
                                    class="validate[required,custom[zip_code],maxSize[20]] zip_code_to"
                                    maxlength="20"
                                    value="<?php echo $user_info['zip']; ?>">
                            </div>
                        </div>
                    </div>

                    <?php if(!empty($items_info)) { ?>
                        <div class="col-12">
                            <label class="input-label input-label--required">Title</label>
                            <div class="input-group">
                                <?php if(count($items_info) > 1){?>
                                    <input type="text"
                                        name="title"
                                        id="add-estimate--form-field--title"
                                        class="form-control validate[required,maxSize[250]]"
                                        value="<?php echo $company['name_company']. ' - Total item(s): ' .count($items_info); ?>">
                                <?php } else{ ?>
                                    <input type="text"
                                        name="title"
                                        id="add-estimate--form-field--title"
                                        class="form-control validate[required,maxSize[250]]"
                                        value="<?php echo $company['name_company']. ' - ' .$items_info[0]['title']; ?>">
                                <?php }?>
                                <div class="input-group-prepend">
                                    <div class="input-info">
                                        <div class="input-info__icon">
                                            <i class="ep-icon ep-icon_info-stroke"></i>
                                        </div>
                                        <div class="input-info__txt">
                                            This field is used to make the further search of your estimate easier. It will be visible only to you.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>

                    <div class="col-12">
                        <label class="input-label">Comment</label>
                        <textarea name="comments"
                            id="add-estimate--form-field--comment"
                            class="validate[maxSize[500]] textcounter-estimate_comment"
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
                                        <label class="input-label">Items</label>
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
                                                class="tac validate[required,max[<?php echo $item['quantity']?>],custom[positive_integer]]"
                                                value="<?php echo $items_quantity[$item['id']];?>">
                                        </td>
                                        <td class="vam">
                                            <a href="<?php echo  __SITE_URL . 'item/' . strForURL($item['title']) . '-' . $item['id']?>" target="_blank">
                                                <?php echo $item['title']?>
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
        var initShippingEstimateSelectCity = function (city){
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

        var initShippingEstimateItemSelectCity = function (city){
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

        var comment = $('#add-estimate--form-field--comment');
        var shippingEstimateSelectCity = $("#add-estimate--form-field--to-city");
        var shippingEstimateItemSelectCity = $("#add-estimate--form-field--from-city");
        var counterOptions = {
            countDown: true,
			countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
			countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'}),
        };

        comment.textcounter(counterOptions);
		initShippingEstimateSelectCity(shippingEstimateSelectCity);
		initShippingEstimateItemSelectCity(shippingEstimateItemSelectCity);

        window.estimateShippingByItemsFormCallBack = onSave;
    });
</script>
