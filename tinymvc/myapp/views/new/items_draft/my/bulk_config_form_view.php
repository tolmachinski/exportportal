<p>Here are a list of available fields. Fields marked with <strong class="txt-red fs-18">*</strong> are required!</p>
<table id="js-table-bulk" class="main-data-table dataTable w-100pr">
    <thead>
        <tr>
            <th>Field</th>
            <th class="mnw-240">XLS Column</th>
            <th>Aditional columns</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="vam"><label class="input-label input-label--required m-0">Title</label></td>
            <td class="vam">
                <select
                    id="bulk-items-upload--formfield--configurations-title"
                    name="xls_columns[title]"
                    class="js-column-selector"
                    <?php echo !empty($xls_columns_config['title']) ? "data-old-value=\"{$xls_columns_config['title']}\"" : ''; ?>>
                    <option value="">Select column</option>
                    <?php foreach ($xls_columns as $xls_column) { ?>
                        <option value="<?php echo $xls_column['value']; ?>"
                            <?php echo !empty($xls_columns_config['title']) ? selected($xls_columns_config['title'], $xls_column['value']) : ''; ?>>
                            <?php echo cleanOutput($xls_column['text']); ?>
                        </option>
                    <?php } ?>
                </select>
            </td>
            <td class="vam">&mdash;</td>
        </tr>
        <tr>
            <td class="vam"><label class="input-label m-0">Price</label></td>
            <td class="vam">
                <select
                    id="bulk-items-upload--formfield--configurations-price"
                    name="xls_columns[price]"
                    class="js-column-selector"
                    <?php echo !empty($xls_columns_config['price']) ? "data-old-value=\"{$xls_columns_config['price']}\"" : ''; ?>>
                    <option value="">Select column</option>
                    <?php foreach ($xls_columns as $xls_column) { ?>
                        <option value="<?php echo $xls_column['value']; ?>"
                            <?php echo !empty($xls_columns_config['price']) ? selected($xls_columns_config['price'], $xls_column['value']) : ''; ?>>
                            <?php echo cleanOutput($xls_column['text']); ?>
                        </option>
                    <?php } ?>
                </select>
            </td>
            <td class="vam">&mdash;</td>
        </tr>
        <tr>
            <td class="vam"><label class="input-label m-0">Discount Price</label></td>
            <td class="vam">
                <select
                    id="bulk-items-upload--formfield--configurations-discount-price"
                    name="xls_columns[discount_price]"
                    class="js-column-selector"
                    <?php echo !empty($xls_columns_config['discount_price']) ? "data-old-value=\"{$xls_columns_config['discount_price']}\"" : ''; ?>>
                    <option value="">Select column</option>
                    <?php foreach ($xls_columns as $xls_column) { ?>
                        <option value="<?php echo $xls_column['value']; ?>"
                            <?php echo !empty($xls_columns_config['discount_price']) ? selected($xls_columns_config['discount_price'], $xls_column['value']) : ''; ?>>
                            <?php echo cleanOutput($xls_column['text']); ?>
                        </option>
                    <?php } ?>
                </select>
            </td>
            <td class="vam">&mdash;</td>
        </tr>
        <tr>
            <td class="vam"><label class="input-label m-0">Quantity</label></td>
            <td class="vam">
                <select
                    id="bulk-items-upload--formfield--configurations-quantity"
                    name="xls_columns[quantity]"
                    class="js-column-selector"
                    <?php echo !empty($xls_columns_config['quantity']) ? "data-old-value=\"{$xls_columns_config['quantity']}\"" : ''; ?>>
                    <option value="">Select column</option>
                    <?php foreach ($xls_columns as $xls_column) { ?>
                        <option value="<?php echo $xls_column['value']; ?>"
                            <?php echo !empty($xls_columns_config['quantity']) ? selected($xls_columns_config['quantity'], $xls_column['value']) : ''; ?>>
                            <?php echo cleanOutput($xls_column['text']); ?>
                        </option>
                    <?php } ?>
                </select>
            </td>
            <td class="vam">&mdash;</td>
        </tr>
        <tr>
            <td class="vam"><label class="input-label m-0">Min sale quantity</label></td>
            <td class="vam">
                <select
                    id="bulk-items-upload--formfield--configurations-minimal-quantity"
                    name="xls_columns[min_sale_quantity]"
                    class="js-column-selector"
                    <?php echo !empty($xls_columns_config['min_sale_quantity']) ? "data-old-value=\"{$xls_columns_config['min_sale_quantity']}\"" : ''; ?>>
                    <option value="">Select column</option>
                    <?php foreach ($xls_columns as $xls_column) { ?>
                        <option value="<?php echo $xls_column['value']; ?>"
                            <?php echo !empty($xls_columns_config['min_sale_quantity']) ? selected($xls_columns_config['min_sale_quantity'], $xls_column['value']) : ''; ?>>
                            <?php echo cleanOutput($xls_column['text']); ?>
                        </option>
                    <?php } ?>
                </select>
            </td>
            <td class="vam">&mdash;</td>
        </tr>
        <tr>
            <td class="vam"><label class="input-label m-0">Max sale quantity</label></td>
            <td class="vam">
                <select
                    id="bulk-items-upload--formfield--configurations-maximal-quantity"
                    name="xls_columns[max_sale_quantity]"
                    class="js-column-selector"
                    <?php echo !empty($xls_columns_config['max_sale_quantity']) ? "data-old-value=\"{$xls_columns_config['max_sale_quantity']}\"" : ''; ?>>
                    <option value="">Select column</option>
                    <?php foreach ($xls_columns as $xls_column) { ?>
                        <option value="<?php echo $xls_column['value']; ?>"
                            <?php echo !empty($xls_columns_config['max_sale_quantity']) ? selected($xls_columns_config['max_sale_quantity'], $xls_column['value']) : ''; ?>>
                            <?php echo cleanOutput($xls_column['text']); ?>
                        </option>
                    <?php } ?>
                </select>
            </td>
            <td class="vam">&mdash;</td>
        </tr>
        <tr>
            <td class="vam"><label class="input-label m-0">Weight, (kg)</label></td>
            <td class="vam">
                <select
                    id="bulk-items-upload--formfield--configurations-weight"
                    name="xls_columns[weight]"
                    class="js-column-selector"
                    <?php echo !empty($xls_columns_config['weight']) ? "data-old-value=\"{$xls_columns_config['weight']}\"" : ''; ?>>
                    <option value="">Select column</option>
                    <?php foreach ($xls_columns as $xls_column) { ?>
                        <option value="<?php echo $xls_column['value']; ?>"
                            <?php echo !empty($xls_columns_config['weight']) ? selected($xls_columns_config['weight'], $xls_column['value']) : ''; ?>>
                            <?php echo cleanOutput($xls_column['text']); ?>
                        </option>
                    <?php } ?>
                </select>
            </td>
            <td class="vam">&mdash;</td>
        </tr>
        <tr>
            <td class="vam"><label class="input-label m-0">Sizes, (cm) LxWxH</label></td>
            <td class="vam">
                <select
                    id="bulk-items-upload--formfield--configurations-sizes"
                    name="xls_columns[sizes]"
                    class="js-column-selector"
                    <?php echo !empty($xls_columns_config['sizes']) ? "data-old-value=\"{$xls_columns_config['sizes']}\"" : ''; ?>>
                    <option value="">Select column</option>
                    <?php foreach ($xls_columns as $xls_column) { ?>
                        <option value="<?php echo $xls_column['value']; ?>"
                            <?php echo !empty($xls_columns_config['sizes']) ? selected($xls_columns_config['sizes'], $xls_column['value']) : ''; ?>>
                            <?php echo cleanOutput($xls_column['text']); ?>
                        </option>
                    <?php } ?>
                </select>
            </td>
            <td class="vam">&mdash;</td>
        </tr>
        <tr>
            <td class="vam"><label class="input-label m-0">Video, (youtube or vimeo link)</label></td>
            <td class="vam">
                <select
                    id="bulk-items-upload--formfield--configurations-video"
                    name="xls_columns[video]"
                    class="js-column-selector"
                    <?php echo !empty($xls_columns_config['video']) ? "data-old-value=\"{$xls_columns_config['video']}\"" : ''; ?>>
                    <option value="">Select column</option>
                    <?php foreach ($xls_columns as $xls_column) { ?>
                        <option value="<?php echo $xls_column['value']; ?>"
                            <?php echo !empty($xls_columns_config['price']) ? selected($xls_columns_config['price'], $xls_column['value']) : ''; ?>>
                            <?php echo cleanOutput($xls_column['text']); ?>
                        </option>
                    <?php } ?>
                </select>
            </td>
            <td class="vam">&mdash;</td>
        </tr>
        <tr>
            <td class="vam"><label class="input-label m-0">Country of origin</label></td>
            <td class="vam">&mdash;</td>
            <td class="vam">
                <select name="ep_columns[country_of_origin]" id="bulk-items-upload--formfield--configurations-origin-country">
                    <?php echo getCountrySelectOptions($countries, empty($ep_columns_config['country_of_origin']) ? 0 : $ep_columns_config['country_of_origin']);?>
                </select>
            </td>
        </tr>
        <tr>
            <td class="vam"><label class="input-label m-0">Product(s) location</label></td>
            <td class="vam">&mdash;</td>
            <td class="vam">
                <select name="ep_columns[product_country]" class="mb-5" id="bulk-items-upload--formfield--configurations-country">
                    <?php echo getCountrySelectOptions($countries, empty($ep_columns_config['product_country']) ? 0 : $ep_columns_config['product_country']);?>
                </select>
                <select
                    id="bulk-items-upload--formfield--configurations-states"
                    name="ep_columns[product_state]"
                    class="mb-5"
                    <?php if (empty($ep_columns_config['product_state'])) { ?>disabled<?php } ?>>
                    <option value="">Select state or province</option>
                    <?php if(!empty($states)) { ?>
                        <?php foreach($states as $state) {?>
                            <?php if(!empty($ep_columns_config['product_state'])){ ?>
                                <option value="<?php echo $state['id'];?>" <?php echo selected($ep_columns_config['product_state'], $state['id']); ?>>
                                    <?php echo cleanOutput($state['state']); ?>
                                </option>
                            <?php } else { ?>
                                <option value="<?php echo $state['id']; ?>">
                                    <?php echo cleanOutput($state['state']); ?>
                                </option>
                            <?php } ?>
                        <?php } ?>
                    <?php } ?>
                </select>
                <div class="wr-select2-h35">
                    <select
                        id="bulk-items-upload--formfield--configurations-city"
                        name="ep_columns[product_city]"
                        class="select-city"
                        <?php if (empty($city_selected)) { ?>disabled<?php } ?>>
                        <option value="" <?php if (empty($city_selected)) { ?>selected<?php } ?> disabled>Select country first</option>
                        <?php if(!empty($city_selected)){ ?>
                            <option value="<?php echo $city_selected['id'];?>" selected>
                                <?php echo cleanOutput($city_selected['city']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
            </td>
        </tr>
        <?php if (!empty($categories)) { ?>
            <tr>
                <td class="vam"><label class="input-label m-0">Product(s) category</label></td>
                <td class="vam">&mdash;</td>
                <td class="vam" id="bulk-items-upload--formfield--configurations-categories-wrapper">
                    <?php if (!empty($categories['levels'])) { ?>
                        <?php foreach($categories['levels'] as $level => $categories_list) { ?>
                            <select
                                id="bulk-items-upload--formfield--configurations-categories-level-<?php echo $level; ?>"
                                name="ep_columns[categories][]"
                                class="mt-5"
                                data-level="<?php echo $level; ?>">
                                <option value selected>Select category</option>
                                <?php foreach($categories_list as $category) { ?>
                                    <option
                                        value="<?php echo $category_id = $category['category_id']; ?>"
                                        <?php echo !empty($categories['selected']) && in_array($category_id, $categories['selected']) ? 'selected' : null; ?>>
                                        <?php echo cleanOutput($category['name']); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        <?php } ?>
                    <?php } ?>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>
<input type="hidden" name="id_upload" value="<?php echo $id_upload; ?>">

<script type="text/template" id="bulk-items-upload--formfield--configurations-category-list-template">
    <select
        id="bulk-items-upload--formfield--configurations-categories-level-{{level}}"
        name="ep_columns[categories][]"
        class="mt-5"
        data-level="{{level}}">
        <option value selected>Select category</option>
        {{options}}
    </select>
</script>
<script type="text/template" id="bulk-items-upload--formfield--configurations-category-list-entry-template">
    <option value="{{id}}">{{name}}</option>
</script>
<script>
    $(function () {
        var ColumnsPersister = (function (global, $) {
            var mutateState = function (state, mutation) {
                var states = Array.isArray(state) ? state : [state];
                var mutations = [];
                for (var index = 0; index < states.length; index++) {
                    var stateIndex = this.currentState.indexOf(states[index]);
                    if (stateIndex !== -1) {
                        mutations[stateIndex] = Object.assign({}, states[index], typeof mutation === 'function' ? mutation(states[index]) : (mutation || {}));
                    }
                }

                this.currentState = Object.assign([], this.currentState, mutations);
            };
            var findState = function (conditions) {
                if (typeof conditions === 'function') {
                    return this.currentState.find(conditions);
                }

                return this.currentState.find(function (state) {
                    return Object.keys(conditions).reduce(function (accumulator, key) {
                        return accumulator && (state[key] === conditions[key]);
                    }, true)
                });
            };
            var filterState = function (conditions) {
                if (typeof conditions === 'function') {
                    return this.currentState.filter(conditions);
                }

                return this.currentState.filter(function (state) {
                    return Object.keys(conditions).reduce(function (accumulator, key) {
                        return accumulator && (state[key] === conditions[key]);
                    }, true)
                });
            };
            var createStore = function (options) {
                var elements = options || [];
                var state = [];

                for (var index = 0; index < elements.length; index++) {
                    var element = elements[index];
                    var node = $(element);
                    var found = state.find(function (meta) { return meta.element === element; });
                    if (found) {
                        continue;
                    }

                    state.push({
                        children: node.children().toArray(),
                        selected: node.is(':selected'),
                        disabled: node.is(':disabled'),
                        element: element,
                        parent: node.parent().get(0),
                        value: node.attr('value') || null,
                        text: node.text() || null,
                    });
                }

                return Object.create({
                    initialState: Object.assign({}, state),
                    currentState: state,
                    previousState: state,
                    findState: findState,
                    filterState: filterState,
                    mutateState: mutateState,
                });
            };

            var ColumnsPersister = function (options) {
                this.store = createStore(options instanceof jQuery ? options.toArray() : (options || []));
            };
            ColumnsPersister.prototype.disableColumns = function (filter) {
                filter = filter || {};
                var value = filter.value || null;
                var element = filter.element || null;
                var parent = null!== element ? (element.parentElement || element.parentNode) : null;
                var exclude = function (state) { return state.element !== element && state.value === value; };
                var siblings = function (state) { return state.parent === parent && state.element !== element; };
                var currentState = this.store.findState({ element: element });
                if (!currentState) {
                    return;
                }

                this.store.mutateState(this.store.filterState(exclude), { disabled: true });
                this.store.mutateState(this.store.filterState(siblings), { selected: false });
                this.store.mutateState(currentState, { disabled: false, selected: true });
                this.setState();
            };
            ColumnsPersister.prototype.enableColumns = function (filter) {
                filter = filter || {};
                var value = filter.value || null;
                var element = filter.element || null;
                var parent = null!== element ? (element.parentElement || element.parentNode) : null;
                var exclude = function (state) { return state.element !== element && state.value === value; };
                var siblings = function (state) { return state.parent === parent && state.element !== element; };
                var currentState = this.store.findState({ element: element });
                if (!currentState) {
                    return;
                }

                this.store.mutateState(this.store.filterState(exclude), { disabled: false });
                this.store.mutateState(this.store.filterState(siblings), { selected: false });
                this.store.mutateState(currentState, { disabled: false, selected: true });
                this.setState();
            };
            ColumnsPersister.prototype.setState = function () {
                for (var index = 0; index < this.store.currentState.length; index++) {
                    var currentState = this.store.currentState[index];
                    var previousState = this.store.previousState[index] || null;
                    if (null === previousState || null === currentState.element) {
                        continue;
                    }

                    if (currentState !== previousState) {
                        var element = currentState.element;
                        var node = $(element);
                        (currentState.text !== previousState.text) && (node.text(currentState.text));
                        (currentState.value !== previousState.value) && (node.val(currentState.val));
                        (currentState.parent !== previousState.parent) && (node.appendTo(currentState.parent));
                        (currentState.disabled !== previousState.disabled) && (node.prop('disabled', currentState.disabled));
                        (currentState.selected !== previousState.selected) && (node.prop('selected', currentState.selected));
                        (currentState.children !== previousState.children) && (node.empty().append(currentState.children || []));
                    }
                }

                this.store.previousState = this.store.currentState;
            };

            return ColumnsPersister;
        } (window, $));
        var onFileColumnUpdate = function (event) {
            var self = $(this);
            var newValue = self.val() || null;
            var oldValue = self.data('old-value') || null;
            var selectedOption = self.find('option').filter(':selected').get(0);
            if (null !== newValue) {
                persister.disableColumns({ value: newValue, element: selectedOption });
            }

            persister.enableColumns({ value: oldValue, element: selectedOption });
            self.data('old-value', newValue);
        };
        var onChangeCountry = function (event) {
            var self = $(this);

            mix(window, { selectState: 0 }, false);
            selectCountry(self, '#' + statesList.attr('id')).done(function () { statesList.prop("disabled", false); });
            citiesList.empty().trigger("change").prop("disabled", true);
        };
        var onChangeState = function () {
            var self = $(this);
            var state = self.val() || null;
            var placeholder = translate_js({plug:'general_i18n', text:'form_placeholder_select2_state_first'});
            var isEnabled = Boolean(~~state);
            if (state) {
                placeholder = translate_js({plug:'general_i18n', text:'form_placeholder_select2_city'});
            }

            mix(window, { selectState: state }, false);
            citiesList.empty().trigger("change").prop("disabled", !isEnabled);
            citiesList.siblings('.select2').find('.select2-selection__placeholder').text(placeholder);
        };
        var onChangeCategory = function () {
            var self = $(this);
            var category = self.val() || null;
            var categoryLevel = self.data('level') || 0;
            if (null === category && !category) {
                categoriesContainer.find('select').each(function (index, element) {
                    var self = $(this);
                    var currentLevel = self.data('level') || 0;
                    if (currentLevel >= categoryLevel + 1) {
                        self.remove();
                    }
                });

                return;
            }

            return getCategories(category)
                .then(function (categories) { return makeCategoryList(categories, categoriesContainer, categoryLevel + 1); })
                .catch(onRequestError)
        };
        var getCategories = function (category) {
            return postRequest(__group_site_url + 'categories/getcategories', { op : 'find', cat: category })
                .then(function (response) { return response.categories || []; })
                .catch(onRequestError);
        };
        var makeCategoryList = function (categories, container, level) {
            var lists = container.find('select');
            var listSelector = 'select[data-level="' + level + '"]';
            var cleanList = function () {
                var self = $(this);
                var currentLevel = self.data('level') || 0;
                if(currentLevel >= level) {
                    self.remove();
                }
            };
            var inflect = function (template, paramters) {
                var contents = template;
                for (var key in paramters) {
                    if (paramters.hasOwnProperty(key)) {
                        var value = paramters[key];
                        var pattern = new RegExp('{{' + key + '}}', 'g');

                        contents = contents.replace(pattern, value);
                    }
                }

                return contents;
            };
            var makeOption = function (template, category) {
                return inflect(template, {
                    id: category.category_id || null,
                    name: category.name || null,
                });
            };

            lists.each(cleanList);
            if (!Object.keys(categories).length) {
                return;
            }

            container.append(inflect(categoryListTemplate, {
                level: level,
                options: categories.map(makeOption.bind(null, categoryListEntryTemplate)).join('')
            }));
            container.find(listSelector).on('change', onChangeCategory);
        };
        var cleanOrientationChangeHandler = function(element) {
			$(element).off('resizestop', onOrientationChange);
		};
        var onOrientationChange = function(wrapper, tables) {
            return function () {
                var normalize = function () {
                    normalizeTables(tables);
                };

                if (!$('body').find('#' + wrapper.attr('id')).length) {
                    cleanOrientationChangeHandler(this);

                    return;
                }

                normalize();
                setTimeout(normalize, 500);
            };
        };
        var adjustTables = function (tables, wrapper, global) {
			mobileDataTable(tables, false);
			normalizeTables(tables);

			global.on('resizestop', onOrientationChange(wrapper, tables));
		};

        var persister;
        var tableBulk = $('#js-table-bulk');
        var container = $('#bulk-items-upload--formwrapper');
        var citiesList = $('#bulk-items-upload--formfield--configurations-city');
        var statesList = $('#bulk-items-upload--formfield--configurations-states');
        var countriesList = $('#bulk-items-upload--formfield--configurations-country');
        var columnsSelects = $('select[name^="xls_columns"].js-column-selector');
        var categoriesLists = $('select[id^="bulk-items-upload--formfield--configurations-categories-level-"]');
        var categoriesBaseList = $('#bulk-items-upload--formfield--configurations-categories-level-0');
        var categoriesContainer = $('#bulk-items-upload--formfield--configurations-categories-wrapper');
        var categoryListTemplate = $('#bulk-items-upload--formfield--configurations-category-list-template').text() || null;
        var categoryListEntryTemplate = $('#bulk-items-upload--formfield--configurations-category-list-entry-template').text() || null;
        var columns = <?php echo !empty($xls_columns) ? json_encode($xls_columns, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) : '[]'; ?>;
        var columnsMeta = <?php echo !empty($xls_columns_config) ? json_encode($xls_columns_config, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) : '{}'; ?>;
        if (!$.isEmptyObject(columnsMeta)) {
            for (var columnName in columnsMeta) {
                if (columnsMeta.hasOwnProperty(columnName)) {
                    var columnValue = columnsMeta[columnName];
                    var select = columnsSelects.filter('[name="xls_columns[' + columnName + ']"]');
                    var node = select.find('option[value="' + columnValue + '"]');
                    if (node.length) {
                        node.prop('selected', true);
                        select.data('old-value', columnValue);
                        columnsSelects
                            .not(select)
                            .find('option[value="' + columnValue + '"]')
                            .prop('disabled', true);
                    }
                }
            }
        }

        persister = new ColumnsPersister(columnsSelects.find('option').toArray())
        categoriesLists.on('change', onChangeCategory);
        columnsSelects.on('change', onFileColumnUpdate);
        countriesList.on('change', onChangeCountry);
        statesList.on('change', onChangeState);
        adjustTables(tableBulk, container, $(window));
        mobileDataTable(tableBulk);
        initSelectCity(citiesList);
    });
</script>
