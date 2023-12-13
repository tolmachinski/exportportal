<label class="input-label">Search the category</label>

<?php views()->display('new/item/add_item/partials/category_search_view');?>

<label class="input-label">Or choose from the list</label>

<div
    class="container-fluid-modal"
>
    <div class="row">
        <div class="col-12 col-md-6">
            <div
                id="js-choose-category-lists"
                class="form-categories"
            >
                <div class="js-first-category form-categories__toggle">
                    <div
                        <?php echo addQaUniqueIdentifier("items-my-add__category-back")?>
                        class="form-categories__selected call-function"
                        data-callback="showFormCategories"
                    >
                        <i class="ep-icon ep-icon_arrow-line-left"></i>
                        <div class="form-categories__item-name"></div>
                    </div>

                    <ul
                        class="form-categories__list"
                    >
                        <?php if (!empty($prepared_categories_all['selected'])) {
                            foreach ($prepared_categories_all['selected'] as $selected_categories_item) {?>
                            <li
                                <?php echo addQaUniqueIdentifier("items-my-add__category")?>
                                class="form-categories__item"
                                data-id="<?php echo $selected_categories_item['category_id']; ?>"
                                data-children="<?php echo $selected_categories_item['children']; ?>"
                                <?php if(isset($selected_categories_item['has_vin'])){ ?>
                                data-has-vin="<?php echo $selected_categories_item['has_vin']; ?>"
                                <?php } ?>
                                data-adult="<?php echo $selected_categories_item['isAdult']?>"
                            >
                                <?php echo cleanOutput($selected_categories_item['name']);?>
                                <i class="ep-icon ep-icon_<?php echo ((int)$selected_categories_item['children'])?'arrow-line-right':'ok-stroke'; ?>"></i>
                            </li>
                            <?php } ?>
                        <?php } ?>

                        <li class="form-categories__item-delimeter"></li>

                        <?php if (!empty($prepared_categories_all['all'])) {
                            foreach($prepared_categories_all['all'] as $all_categories_item) {?>
                                <li
                                    <?php echo addQaUniqueIdentifier("items-my-add__category")?>
                                    class="form-categories__item"
                                    data-id="<?php echo $all_categories_item['category_id']; ?>"
                                    data-children="<?php echo $all_categories_item['children']; ?>"
                                    <?php if(isset($all_categories_item['has_vin'])){ ?>
                                    data-has-vin="<?php echo $all_categories_item['has_vin']; ?>"
                                    <?php } ?>
                                    data-adult="<?php echo $all_categories_item['isAdult']?>"
                                >
                                    <?php echo $all_categories_item['name']; ?>
                                    <i class="ep-icon ep-icon_<?php echo ((int)$all_categories_item['children'])?'arrow-line-right':'ok-stroke'; ?>"></i>
                                </li>
                            <?php } ?>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/template" id="js-template-categories-toggle">
    <div class="form-categories__toggle">
        <div
            <?php echo addQaUniqueIdentifier("items-my-add__category-back")?>
            class="form-categories__selected call-function {{classSelected}}"
            data-callback="showFormCategories"
        >
			<i class="ep-icon ep-icon_arrow-line-left"></i>
			<div class="form-categories__item-name">{{nameSelected}}</div>
		</div>

        <ul
            class="form-categories__list {{classDisplayList}}"
        >
            {{list}}
		</ul>
	</div>
</script>

<script type="text/template" id="js-template-categories-item">
    <li
        <?php echo addQaUniqueIdentifier("items-my-add__category-child")?>
        class="form-categories__item {{classItem}}"
        data-id="{{idItem}}"
        data-children="{{nrChildren}}"
        {{dataVin}}
        {{dataAdult}}
    >
        {{nameItem}}
        <i class="ep-icon ep-icon_{{classIconItem}}"></i>
    </li>
</script>

<script>
	(function() {
		"use strict";

		window.chooseCategory = ({
			init: function (params) {
				chooseCategory.self = this;

				chooseCategory.editData =  <?php echo empty($product_categories) ? '{}' : json_encode($product_categories); ?>;
				chooseCategory.vin =  0;
                chooseCategory.templateCategoriesToggle = $('#js-template-categories-toggle').text();
                chooseCategory.templateCategoriesItem = $('#js-template-categories-item').text();

				chooseCategory.$wrChooseCategoryLists = $('#js-choose-category-lists');
				chooseCategory.$formFastSearch = $('.js-category-search-all');
                chooseCategory.$formFastSearchDropdown = chooseCategory.$formFastSearch.find('.category-fast-search__result');
				chooseCategory.$vinBlock = $('#js-add-item-dynamic-vin');
				chooseCategory.tab1 = '#js-add-item-tab-choose-category';
				chooseCategory.$chooseCategoryTab = $(chooseCategory.tab1);
				chooseCategory.$footerChooseCategory = $('#js-footer-choose-category');
				chooseCategory.$btnChooseCategory = chooseCategory.$footerChooseCategory.find('#js-btn-choose-category');
				chooseCategory.$tabsNav = $('#js-add-item-nav-tabs');

				chooseCategory.self.initListiners();
				chooseCategory.self.initPlug();
			},
			initListiners: function(){
				chooseCategory.$wrChooseCategoryLists.on('click', '.form-categories__item:not(.current):not(.last)', function(e){
					e.preventDefault();
					var $this = $(this);
					$this.addClass('current').siblings().removeClass('current');
					chooseCategory.self.selectCategoryItem($this);
				});

                //region search categories
				chooseCategory.$chooseCategoryTab.on('click', '.js-search-category', function(e){
					e.preventDefault();
					chooseCategory.self.searchCategory();
				});

                var debouncedSearch = _.debounce(function(){chooseCategory.self.searchCategory();}, 500);
                chooseCategory.$chooseCategoryTab.on('keyup', 'input[type="text"]', debouncedSearch);

                chooseCategory.$formFastSearch.on('click', ".js-btn-fast-more-categories", function(e){
                    e.preventDefault();
                    var $thisBtn = $(this);
                    chooseCategory.self.getMoreCategories($thisBtn);
                });

                $('body').on('click', function(e){
                    var $choice = chooseCategory.$formFastSearch;

                    if ($(e.target)[0] === $choice[0] || $(e.target).parents('.js-category-search-all')[0] === $choice[0]) {
                        return;
                    }

                    if (
                        (
                            $(e.target)[0] === chooseCategory.$formFastSearchDropdown[0]
                            || $(e.target).parents('.category-fast-search__result')[0]
                            !== chooseCategory.$formFastSearchDropdown[0]
                        )
                        &&chooseCategory.$formFastSearchDropdown.is(':visible')
                    ) {
                        chooseCategory.$formFastSearchDropdown.hide();
                    }
                });
                //endregion search categories

                mix(
                    window,
                    {
                        submitAddProduct: chooseCategory.self.onSubmitAddProduct,
                        nextChooseCategory: chooseCategory.self.onNextChooseCategory,
                        showFormCategories: chooseCategory.self.onShowFormCategories,
                    },
                    false
                );
			},
			initPlug: function(){
                chooseCategory.self.initMainCategories();
                chooseCategory.self.initChoosedCategories();
                chooseCategory.self.initCategoryBtnChoose();
            },
			initMainCategories: function(){
                var $mainWr = $('.js-first-category');
                var mainId = chooseCategory.editData.first || 0;

                if(mainId > 0){
                    var $selected = $mainWr.find('.form-categories__item[data-id="' + mainId + '"]');
                    var name = $selected.text()
                    var haveChildren = parseInt($selected.data('children'));

                    $selected.addClass('current').siblings().removeClass('current');

                    if(haveChildren != 0){
                        $mainWr.find('.form-categories__selected').addClass('active');
                        $mainWr.find('.form-categories__item-name').text(name);
                        $mainWr.find('.form-categories__list').hide();
                    }else{
                        $mainWr.find('.form-categories__selected').removeClass('active');
                        $mainWr.find('.form-categories__item-name').text("");
                        $mainWr.find('.form-categories__list').show(function(){
                            chooseCategory.self.scrollToCategorySelected();
                        });
                    }
                }
            },
			initChoosedCategories: function(){
                var toggleCategories = '';

                if(Object.size(chooseCategory.editData.list) > 0){
                    $.each(chooseCategory.editData.list, function(index, element){
                        var items = '';

                        $.each(element, function(indexInner, elementInner){
                            items += chooseCategory.templateCategoriesItem
                                        .replace('{{classItem}}', ((index == indexInner)?'current':''))
                                        .replace('{{idItem}}', indexInner)
                                        .replace('{{dataVin}}', ((elementInner.has_vin != undefined)?'data-has-vin="1"':''))
                                        .replace('{{dataAdult}}', ((elementInner.isAdult == 1)?'data-adult="1"':'data-adult="0"'))
                                        .replace('{{nrChildren}}', elementInner.children)
                                        .replace('{{nameItem}}', elementInner.name)
                                        .replace('{{classIconItem}}', ((elementInner.children)?'arrow-line-right':'ok-stroke'));
                        });

                        toggleCategories += chooseCategory.templateCategoriesToggle
                                                            .replace('{{classSelected}}', ((index != 'last' && chooseCategory.editData.last != index)?'active':''))
                                                            .replace('{{nameSelected}}', ((index != 'last' && chooseCategory.editData.last != index)?element[index].name:''))
                                                            .replace('{{classDisplayList}}', ((index !='last' && chooseCategory.editData.last != index && element[index].children == 1)?'display-n':''))
                                                            .replace('{{list}}', items);
                    });
                }

                chooseCategory.$wrChooseCategoryLists.find('.form-categories__toggle').first().nextAll().remove();

                if(toggleCategories != ""){
                    chooseCategory.$wrChooseCategoryLists.append(toggleCategories);
                    chooseCategory.self.scrollToCategorySelected();
                }
            },
			initCategoryBtnChoose: function(){
				var category = 0;
                var categoryStatus = true;
                var $currentLast = chooseCategory.$wrChooseCategoryLists.find('.form-categories__item.current .ep-icon_ok-stroke');
                var $current = $currentLast.closest('.form-categories__item');

				if($currentLast.length){
					category = $current.data('id');
					chooseCategory.vin = $current.data('has-vin') || 0;
					categoryStatus = false;
				}

                var $inputVin = chooseCategory.$vinBlock.find('#js-add-item-vindecoder');

                if(chooseCategory.vin == 1){
                    chooseCategory.$vinBlock.show();
                    $inputVin.addClass($inputVin.data('template'));
                }else{
                    chooseCategory.$vinBlock.hide();
                    $inputVin.removeClass($inputVin.data('template')).removeClass('validengine-border');
                }

                chooseCategory.$btnChooseCategory.prop('disabled', categoryStatus);
                chooseCategory.$btnChooseCategory.data('category', category);
			},
			selectCategoryItem: function($this){
				var category = parseInt($this.data('id'));
				var children = parseInt($this.data('children'));
				var hasVin = $this.data('has-vin') || 0;
				var $wr = $this.closest('.form-categories__toggle');
				var $ul = $wr.find('.form-categories__list');
				var $selected = $wr.find('.form-categories__selected');

				if(category == 0){
					return false;
                }

                if(hasVin){
                    chooseCategory.vin = 1;
                }else{
                    chooseCategory.vin = 0;
                }

                if(children == 0){
                    $this.addClass('current').siblings('.current').removeClass('current');
                    chooseCategory.$btnChooseCategory.prop('disabled', false).data('category', category);
                    return false;
                }

				$.ajax({
                    type: 'POST',
                    url: __site_url + 'items/ajax_select_category/one',
                    dataType: 'JSON',
                    data: {category: category},
                    beforeSend : function(xhr, opts){
                        showLoader(chooseCategory.tab1, 'Loading...');
                    },
                    success: function(resp){
                        hideLoader(chooseCategory.tab1);

                        if(resp.mess_type == 'success'){
                            $this.addClass('current').siblings('.current').removeClass('current');

                            if( Object.size(resp.list) > 0 ){
                                var name = $this.text();
                                $wr.find('.form-categories__selected').addClass('active');
                                $wr.find('.form-categories__item-name').text(name);
                                $wr.find('.form-categories__list').hide();

                                var toggleCategories = '';
                                var items = '';
                                $.each(resp.list, function(indexInner, elementInner){
                                    items += chooseCategory.templateCategoriesItem
                                                .replace('{{classItem}}', '')
                                                .replace('{{idItem}}', indexInner)
                                                .replace('{{dataVin}}', ((elementInner.has_vin != undefined)?'data-has-vin="1"':''))
                                                .replace('{{dataAdult}}', ((elementInner.isAdult == 1)?'data-adult="1"':'data-adult="0"'))
                                                .replace('{{nrChildren}}', elementInner.children)
                                                .replace('{{nameItem}}', elementInner.name)
                                                .replace('{{classIconItem}}', ((elementInner.children)?'arrow-line-right':'ok-stroke'));
                                });

                                toggleCategories += chooseCategory.templateCategoriesToggle
                                                        .replace('{{classSelected}}', '')
                                                        .replace('{{nameSelected}}', '')
                                                        .replace('{{classDisplayList}}', '')
                                                        .replace('{{list}}', items);

                                chooseCategory.$wrChooseCategoryLists.append(toggleCategories);
                                chooseCategory.$btnChooseCategory.prop('disabled', true);
                            }else{
                                chooseCategory.$btnChooseCategory.prop('disabled', false).data('category', category);
                            }
                        }else{
                            systemMessages(resp.message, resp.mess_type);
                        }
                    },
                    error: function(){alert('ERROR')}
                });
			},
			onShowFormCategories: function ($this) {
                var $wr = $this.closest('.form-categories__toggle');

                $wr
                    .find('.form-categories__list')
                    .slideDown()
                    .end()
                    .find('.form-categories__selected')
                    .removeClass('active')
                    .end()
                    .find('.current')
                    .removeClass('current');

                $wr.nextAll().remove();
                chooseCategory.$btnChooseCategory.prop('disabled', true);
			},
			searchCategory: function(){
                var $form = chooseCategory.$formFastSearch;
				var $loader = $form.find('.js-search-category-loader');
				var $btn = $form.find('.js-search-category');
				var keywords = $form.find('input[name="keywords"]').val();
                var $dropdown = chooseCategory.$formFastSearchDropdown;

                if(keywords.length < 3){
                    $dropdown.hide();
                    return false;
                }

                $loader.show();
                $btn.hide();
                $dropdown.hide();

                $.ajax({
                    type: 'POST',
                    url: __site_url + 'categories/getcategories',
                    dataType: 'JSON',
                    data: {
                        keywords: keywords,
                        op: 'search',
                        content_type: 'popup',
                    },
                    beforeSend: function(){},
                    success: function(resp){
                        $loader.hide();
                        $btn.show();

                        if(resp.mess_type == 'success'){
                            $dropdown.show().html(resp.content);

                            if($form.find('.category-fast-search__result-item').length >= resp.categories_count){
                            	$form.find('.js-btn-fast-more-categories').remove();
                            }
                        }else{
                            systemMessages(resp.message, resp.mess_type);
                        }
                    }
                });
			},
			getMoreCategories: function ($this) {
                var $form = chooseCategory.$formFastSearch;
                var $currentBlocks = $form.find('.category-fast-search__result-item');
                var $inner = $form.find('.category-fast-search__result-inner');
                var currentBlocksTotal = $currentBlocks.length;
                var innerTotal = $inner.data('count-items');
                var keywords = $form.find('input[name="keywords"]').val();

				$.ajax({
					type: 'POST',
					url: __site_url + 'categories/getcategories',
					dataType: 'JSON',
					data: {
						keywords: keywords,
                        op: 'search',
                        content_type: 'popup',
						start: currentBlocksTotal
					},
					beforeSend: function(){
						showLoader(chooseCategory.tab1);
					},
					success: function(resp){
                        hideLoader(chooseCategory.tab1);

						if (resp.mess_type == 'success') {
							if ((currentBlocksTotal - 1) > 0) {
								$($currentBlocks[currentBlocksTotal - 1]).after(resp.content);
                            }

                            if (currentBlocksTotal >= innerTotal) {
                                $this.remove();
                            }
						} else {
							systemMessages(resp.message, resp.mess_type);
						}
					},
					error: function () {
						systemMessages("Internal server error", 'error');
					}
				});
			},
			onSubmitAddProduct: function($this){
				var category = $this.data('category');
                chooseCategory.$formFastSearchDropdown.hide();
                chooseCategory.$formFastSearch.find('input[type="text"]').val("");

                $.ajax({
					type: 'POST',
					url: __site_url + 'items/ajax_select_category/search',
					dataType: 'JSON',
					data: { category: category },
					beforeSend: function(){
						showLoader(chooseCategory.tab1);
					},
					success: function(resp){
                        hideLoader(chooseCategory.tab1);

						if (resp.mess_type == 'success') {
                            chooseCategory.editData =  resp.product_categories || {};
                            chooseCategory.self.initMainCategories();
                            chooseCategory.self.initChoosedCategories();
                            chooseCategory.self.initCategoryBtnChoose();
						} else {
							systemMessages(resp.message, resp.mess_type);
						}
					},
					error: function () {
						systemMessages("Internal server error", 'error');
					}
				});
			},
			onNextChooseCategory: function($this){
				var category = parseInt($this.data('category'));

                $('#js-add-item-category-input').val(category);
                chooseCategory.$btnChooseCategory.data('category', category);
                var $inputVin = chooseCategory.$vinBlock.find('#js-add-item-vindecoder');

                if(chooseCategory.vin == 1){
                    chooseCategory.$vinBlock.show();
                    $inputVin.addClass($inputVin.data('template'));
                }else{
                    chooseCategory.$vinBlock.hide();
                    $inputVin.removeClass($inputVin.data('template')).removeClass('validengine-border');
                }

                if(popupAddItem.draftBtns.length){
                    popupAddItem.draftBtns.show();
                }

                chooseCategory.self.navActive();
                validateTabInit();
			},
			navDisabled: function(){
				chooseCategory.$tabsNav.find('.link:not(.tabs-circle__item:first-child .link)').addClass('disabled');
			},
			navActive: function(){
				chooseCategory.$tabsNav.find('.link.disabled').removeClass('disabled');
				chooseCategory.$tabsNav.find('.tabs-circle__item:nth-child(2) .link').tab('show');
            },
            scrollToCategorySelected: function(){
				setTimeout(function(){
					var elements = $('.form-categories__toggle .current');
					elements.each(function (index, element) {
						var self = $(element);
						var position = self.position();
						self.closest('#js-choose-category-lists').animate({
							scrollTop: position.top
						}, 100);
					});
				}, 100);
			},
		});

	}());

	$(function(){
		window.chooseCategory.init();
    });
</script>
