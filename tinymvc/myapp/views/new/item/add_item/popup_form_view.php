<?php views()->display('new/file_upload_scripts'); ?>

<div class="wr-modal-flex inputs-40">
	<div class="modal-flex__form js-form-wrapper">
		<ul id="js-add-item-nav-tabs" class="nav tabs-circle tabs-circle--hide-mobile" role="tablist">
			<li class="tabs-circle__item<?php if (!empty($product_categories['last'])) { ?> complete<?php } ?>">
				<a
					class="link <?php if (empty($product_categories['last'])) { ?>active<?php }?>"
					href="#js-add-item-tab-choose-category"
					aria-controls="title"
					role="tab"
					data-toggle="tab"
					data-name="product_category"
                    <?php echo addQaUniqueIdentifier("items-my-add__form-step-1-mobile")?>
				>
					<div class="tabs-circle__point"><i class="ep-icon ep-icon_ok-stroke2"></i></div>

					<div class="tabs-circle__txt">Product Category</div>
				</a>
			</li>

			<li class="tabs-circle__item">
				<a
					class="link <?php echo empty($product_categories['last']) ? 'disabled' : 'active';?>"
					href="#js-add-item-tab-main-information"
					aria-controls="title"
					role="tab"
					data-toggle="tab"
					data-name="specifications"
                    <?php echo addQaUniqueIdentifier("items-my-add__form-step-2-mobile")?>
				>
					<div class="tabs-circle__point"><i class="ep-icon ep-icon_ok-stroke2"></i></div>

					<div class="tabs-circle__txt">Specifications</div>
				</a>
			</li>

			<li class="tabs-circle__item">
				<a
					class="link <?php if (empty($product_categories['last'])) { ?>disabled<?php } ?>"
					href="#js-add-item-tab-product-description"
					aria-controls="title"
					role="tab"
					data-toggle="tab"
					data-name="shipping_information"
                    <?php echo addQaUniqueIdentifier("items-my-add__form-step-3-mobile")?>
				>
					<div class="tabs-circle__point"><i class="ep-icon ep-icon_ok-stroke2"></i></div>

					<div class="tabs-circle__txt">Shipping Information</div>
				</a>
			</li>

			<li class="tabs-circle__item">
				<a
					class="link <?php if (empty($product_categories['last'])) { ?>disabled<?php } ?>"
					href="#js-add-item-tab-photo-video"
					aria-controls="title"
					role="tab"
					data-toggle="tab"
					data-name="visuals"
                    <?php echo addQaUniqueIdentifier("items-my-add__form-step-4-mobile")?>
				>
					<div class="tabs-circle__point"><i class="ep-icon ep-icon_ok-stroke2"></i></div>

					<div class="tabs-circle__txt">Visuals</div>
				</a>
			</li>

			<li class="tabs-circle__item">
				<a
					class="link <?php if (empty($product_categories['last'])) { ?>disabled<?php } ?>"
					href="#js-add-item-tab-prices-variants"
					aria-controls="title"
					role="tab"
					data-toggle="tab"
					data-name="price_variations"
                    <?php echo addQaUniqueIdentifier("items-my-add__form-step-5-mobile")?>
				>
					<div class="tabs-circle__point"><i class="ep-icon ep-icon_ok-stroke2"></i></div>

					<div class="tabs-circle__txt">Price and Variations</div>
				</a>
			</li>
		</ul>

		<form id="js-add-item-form" class="modal-flex__content validateModalTabs" name="mortgage_calc_form" autocomplete="off" action="<?php echo $action; ?>">
			<div class="tab-content tab-content--pt-0">
				<div
					id="js-add-item-tab-choose-category"
					class="tab-pane fade <?php if (empty($product_categories['last'])) { ?>show active<?php } ?>"
					role="tabpanel"
                    <?php echo addQaUniqueIdentifier("items-my-add__form-step-1")?>
				>
					<?php views()->display('new/item/add_item/choose_category_view'); ?>
				</div>
				<!-- END tab 1 -->
				<div
					id="js-add-item-tab-main-information"
					class="tab-pane js-add-item-tab-pane-submit fade<?php if (!empty($product_categories['last'])) { ?> show active<?php } ?>"
					role="tabpanel"
                    <?php echo addQaUniqueIdentifier("items-my-add__form-step-2")?>
				>
					<?php views()->display('new/item/add_item/specifications_tab_view'); ?>
				</div>
				<!-- END tab 2 -->
				<div
					id="js-add-item-tab-product-description"
					class="tab-pane js-add-item-tab-pane-submit fade"
					role="tabpanel"
                    <?php echo addQaUniqueIdentifier("items-my-add__form-step-3")?>
				>
					<?php views()->display('new/item/add_item/shipping_information_tab_view'); ?>
				</div>
				<!-- END tab 3 -->
				<div
					id="js-add-item-tab-photo-video"
					class="tab-pane js-add-item-tab-pane-submit fade"
					role="tabpanel"
                    <?php echo addQaUniqueIdentifier("items-my-add__form-step-4")?>
				>
					<?php views()->display('new/item/add_item/visuals_tab_view'); ?>
				</div>
				<!-- END tab 4 -->
				<div
					id="js-add-item-tab-prices-variants"
					class="tab-pane js-add-item-tab-pane-submit fade"
					role="tabpanel"
                    <?php echo addQaUniqueIdentifier("items-my-add__form-step-5")?>
				>
					<?php views()->display('new/item/add_item/price_variations_tab_view'); ?>
				</div>
				<!-- END tab 5 -->
			</div>
			<input id="js-add-item-category-input" type="hidden" name="category" value="<?php if (isset($category)) { echo $category; } ?>">

			<?php if (isset($item['id'])) { ?>
				<input type="hidden" value="<?php echo $item['id']?>" name="item" />
			<?php } ?>
		</form>

		<div
            id="js-footer-choose-category"
			class="modal-flex__btns"
			<?php if (!empty($product_categories['last'])) { ?>style="display: none;"<?php } ?>
        >
			<div class="modal-flex__btns-right">
				<button
                    <?php echo addQaUniqueIdentifier("items-my-add__submit-choose-category")?>
					id="js-btn-choose-category"
					class="btn btn-primary call-function"
					data-callback="nextChooseCategory"
					<?php if (!empty($category)) { ?>
						data-category="<?php echo $category; ?>"
					<?php } else { ?>
						disabled
					<?php } ?>>
					Next
				</button>
			</div>
		</div>

        <div
            id="js-footer-form-item"
            class="modal-flex__btns<?php if (empty($product_categories['last'])) { ?> display-n_i<?php } ?>"
        >
            <div class="modal-flex__btns-left">
                <button class="btn btn-dark mnw-50-lg call-function" data-callback="prevAddItem" <?php echo addQaUniqueIdentifier("items-my-add__previous-step")?>>
                    <i class="ep-icon ep-icon_arrow-line-left dn-lg-min"></i>
                    <span class="dn-lg">Back</span>
                </button>
            </div>

            <div class="modal-flex__btns-right">
                <div class="js-add-item-draft-btns flex-display">
                    <div class="dropdown display-ib dn-lg-min">
                        <a class="btn btn-light dropdown-toggle js-sample_order_btns_dropdown " <?php echo addQaUniqueIdentifier("items-my-add__dropdown-menu")?> data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="ep-icon ep-icon_menu-circles fs-20 txt-blue2"></i>
                        </a>

                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item cur-pointer call-function" <?php echo addQaUniqueIdentifier("items-my-add__preview-mob-button")?> data-callback="previewAddItem" href="#">
                                <i class="ep-icon ep-icon_photo-gallery"></i><span class="txt">Preview</span>
                            </a>

                            <?php if (empty($item) || $item['draft']) {?>
                                <a class="dropdown-item cur-pointer confirm-dialog" data-message="Do you want to save this as a draft?" data-callback="saveDraftAddItem" href="#">
                                    <i class="ep-icon ep-icon_save"></i><span class="txt">Save draft</span>
                                </a>
                            <?php }?>
                        </div>
                    </div>
                    <button class="btn btn-light dn-lg call-function" data-callback="previewAddItem" <?php echo addQaUniqueIdentifier("items-my-add__preview")?>>Preview</button>
                    <?php if (empty($item) || $item['draft']) {?>
                        <button class="btn btn-light dn-lg confirm-dialog" data-message="Do you want to save this as a draft?" data-callback="saveDraftAddItem" <?php echo addQaUniqueIdentifier("items-my-add__save-draft")?>>Save draft</button>
                    <?php }?>
                </div>

                <button class="btn btn-primary js-intermediate-button call-function" data-callback="nextAddItem" <?php echo addQaUniqueIdentifier("items-my-add__submit-step")?>>Next</button>
                <button class="btn btn-success js-final-button call-function display-n" data-callback="saveItem" <?php echo addQaUniqueIdentifier("items-my-add__submit-save-item")?>><?php if (empty($item['id'])) { ?>Add Item<?php }else{?>Save Edits<?php }?></button>
            </div>
        </div>
	</div>
</div>

<div id="js-wr-terms-add-item" class="display-n">
    <form
        class="add-items-terms-and-conditions-form validateModal"
        data-callback="callModalValidateTerms"
    >
        <div class="add-items-terms-and-conditions-form__item">
            <p class="add-items-terms-and-conditions-form__text">
                <?php echo translate('add_items_popup_tc_photo_and_video_upload', [
                    '{{START_TAG}}' => '<a class="txt-underline" href="' . __SITE_URL . 'terms_and_conditions/tc_photo_and_video_upload"target="_blank"'.addQaUniqueIdentifier("items-my-add__terms-conditions-link").'>',
                    '{{END_TAG}}' => '</a>',
                ]); ?>
            </p>
            <div class="checkbox-list">
                <div class="checkbox-list__item">
                    <label class="checkbox-list__label custom-checkbox" <?php echo addQaUniqueIdentifier("global-terms-popup__checkbox")?>>
                        <input class="js-add-terms validate[required]" type="checkbox" name="terms" <?php if (!empty($item['id'])) echo "checked" ?>>
                        <span class="checkbox-list__txt custom-checkbox__text-agreement"><?php echo translate('add_items_popup_terms_checkbox_txt'); ?></span>
                    </label>
                </div>
            </div>
        </div>

        <div class="add-items-terms-and-conditions-form__item">
            <p class="add-items-terms-and-conditions-form__text"><?php echo translate('add_items_popup_tc_seo_friendly'); ?></p>
            <div class="checkbox-list">
                <div class="checkbox-list__item">
                    <label class="checkbox-list__label custom-checkbox" <?php echo addQaUniqueIdentifier("global-terms-popup__checkbox_seo")?>>
                        <input class="js-add-terms validate[required]" type="checkbox" name="seo" <?php if (!empty($item['id'])) echo "checked" ?>>
                        <span class="checkbox-list__txt custom-checkbox__text-agreement"><?php echo translate('add_items_popup_tc_seo_friendly_checkbox'); ?></span>
                    </label>
                </div>
            </div>
        </div>
    </form>
</div>

<?php views()->display('new/item/add_item/popup_adult_items_policy_view');?>

<script type="text/javascript">
	(function() {
		"use strict";

		window.popupAddItem = ({
			init: function (params) {
				popupAddItem.self = this;
                popupAddItem.variantsOptionShowWrapper = $("#js-add-item-properties-wr");
                popupAddItem.priceWrapper = $("#js-add-price-wrapper");
                popupAddItem.variationsWrapper = $("#js-add-variations-wrapper");
                popupAddItem.variantsOptionSelectWrapper = $("#js-add-item-properties-select-wr");
                popupAddItem.variantsOptionCombinationWrapper = $("#js-add-item-variants-wr");
                popupAddItem.variantsOptionCombination = $("#js-add-item-variants");

				popupAddItem.tabsElements = {};
				popupAddItem.$tabsNav = $('#js-add-item-nav-tabs');
				popupAddItem.$formAdditem = $('#js-add-item-form');
				popupAddItem.wrapper = popupAddItem.$formAdditem.closest('.js-form-wrapper');
				popupAddItem.navbar = popupAddItem.wrapper.find('#js-add-item-nav-tabs');
				popupAddItem.tabs = popupAddItem.navbar.find('a[data-toggle="tab"]');
				popupAddItem.itemDescription = $('#js-add-item-description');
				popupAddItem.draftBtns = $('.js-add-item-draft-btns');
                popupAddItem.$footerFormItem = $('#js-footer-form-item');
	            popupAddItem.viewMainPhoto = $('#js-view-main-photo');

                popupAddItem.currentLocation = <?php echo json_encode($other_location ?? ''); ?>;
                popupAddItem.currentLocation = Object.keys(popupAddItem.currentLocation).map(function (key) {
                                                    if (!/^.*_show$/i.test(key)) {
                                                        return { name: key, value: this[key] ? this[key].value || null : null };
                                                    }
                                                }, popupAddItem.currentLocation).filter(function (f) { return f; });
				popupAddItem.termsSubmit = false;
				popupAddItem.currentCircle = popupAddItem.navbar.find('.active').closest('.tabs-circle__item').index();
				popupAddItem.popupAddScrollPosition = 0;
				popupAddItem.hasItem = Boolean(~~'<?php echo (int) isset($item['id']); ?>');
				popupAddItem.statusValidate = false;
				popupAddItem.ttlText = '<?php echo !empty($modal_title) ? $modal_title : (!isset($item['id']) ? 'Add Item' : 'Edit Item'); ?>';
				popupAddItem.inputs = {
					poduct_category: {
                        category: "category"
					},
					specifications: {
                        title: "title",
                        year: "year",
                        country: "origin_country",
                        tags: "tags",
                        purchase_options: "purchase_options[]"
					},
					shipping_information: {
                        harmonized_tariff: "hs_tariff_number",
                        min_quantity: "min_quantity",
                        max_quantity: "max_quantity",
                        unit_type: "unit_type",
                        weight: "weight",
                        item_length: "item_length",
                        item_width: "item_width",
                        item_height: "item_height",
                        address_typy: "address_type"
					},
					visuals: {
                        images: "images[] images_validate[]",
                        images_main: "images_main images_main_validate",
					},
					price_variations: {
                        price: "price_in_dol",
                        quantity: "quantity",
					}
				};

				popupAddItem.self.initPlug();
				popupAddItem.self.initListeners();
			},
			initPlug: function(){
				popupAddItem.self.openAddItemTerms();

				setTimeout(function(){
					popupAddItem.self.initTabs();
				}, 100);

				jQuery(window).on('resizestop', function () {
                    setTimeout(function(){
                        popupAddItem.self.initTabs();
                    }, 300);
				});

				$(window).on('resizestart', function () {
					popupAddItem.popupAddScrollPosition = popupAddItem.$formAdditem.scrollTop();
				});
				$(window).on('resizestop', function () {
					popupAddItem.self.changePopupTitle();
					popupAddItem.$formAdditem.scrollTop(popupAddItem.popupAddScrollPosition);
				});
				setTimeout(function () { popupAddItem.self.changePopupTitle(); }, 100);

				if (popupAddItem.hasItem) {
					popupAddItem.self.validateTab();
				}

				if (tinymce) {
					tinymce.remove('#' + popupAddItem.itemDescription.attr('id'));
					tinymce.init({
                        content_css : "/public/css/tinymce-content-style.css",
						selector:'#js-add-item-description',
						menubar: false,
						statusbar : true,
						placeholder : 'Type the product description in English',
						height : 300,
						plugins: ["placeholder lists charactercount powerpaste"],
						style_formats: [
							{title: 'H3', block: 'h3'},
							{title: 'H4', block: 'h4'},
							{title: 'H5', block: 'h5'},
							{title: 'H6', block: 'h6'},
						],
						powerpaste_html_import: "merge",
						toolbar: "styleselect | bold italic underline | numlist bullist ",
						resize: false,
                        mobile: {
                            theme: 'mobile'
                        }
					});
                }

                chooseCategory.self.scrollToCategorySelected();
			},
			initListeners: function(){
				popupAddItem.$formAdditem.on('submit', popupAddItem.self.onSubmitForm);
                popupAddItem.tabs.on('shown.bs.tab', popupAddItem.self.onShowTab);

                $(window).on("locations:override-location", function (e, data) {
                    popupAddItem.currentLocation = data.serialized || [];
                });

				mix(window, {
					saveItem: popupAddItem.self.saveItem,
					nextAddItem: popupAddItem.self.showNextTab,
					prevAddItem: popupAddItem.self.showPrevTab,
					validateTabInit: popupAddItem.self.validateTab,
					callModalValidateTerms: popupAddItem.self.onCallModalValidateTerms,
					previewAddItem: popupAddItem.self.onPreviewAddItem,
					callbackReplaceCropImages: popupAddItem.self.onCallbackReplaceCropImages,
					saveDraftAddItem: popupAddItem.self.onSaveDraftAddItem,
				}, false);
			},
			initTabs: function(){
				var indexPrev;

				popupAddItem.$tabsNav.find('.tabs-circle__item:visible').each(function(){
					var $this = $(this);
					var $link = $this.find('.link');
					var $point = $this.find('.tabs-circle__point');
					var $delimeter = $this.find('.delimeter');
					var element = {};
					element.index = $this.index();
					element.width = $this.outerWidth();
					element.left = $this.position().left;
					element.leftTotal = element.left + element.width;
					element.link = {};
					element.link.width = $point.outerWidth();
					element.link.left = $point.position().left;
					var progress = '';

					if(
						$this.hasClass('complete')
						|| $link.hasClass('active')
						|| $this.hasClass('additional')
					){
						progress = ' progress';
					}else if(
                        $delimeter.length
                        && $delimeter.hasClass('progress')
                    ){
                        progress = ' progress';
                    }

					if(popupAddItem.tabsElements[indexPrev] != undefined){
						var prevElement = popupAddItem.tabsElements[indexPrev];
						var delimeter = {};
						delimeter.plusElementWidth = ((element.width - element.link.width) / 2);
						delimeter.plusAllWidth = delimeter.plusElementWidth + ((prevElement.width - prevElement.link.width) / 2);
						delimeter.width = (element.left + delimeter.plusAllWidth) - prevElement.leftTotal;
						delimeter.minusPosition = delimeter.width - delimeter.plusElementWidth;

                        $this.find('.delimeter').remove();
						$this.append('<div class="delimeter' + progress + '" style="width: ' + delimeter.width + 'px; left: -' + delimeter.minusPosition + 'px;"></div>');
					}

					indexPrev = element.index;
					popupAddItem.tabsElements[element.index] = element;
				});
			},
			openAddItemTerms: function(){
                var content = $('#js-wr-terms-add-item').html();
                var buttons = [{
						label: 'Submit',
						cssClass: 'btn-primary mnw-130',
						action: function(dialogItself){
							popupAddItem.self.submitTerms(dialogItself);
						}
                    }];
                var validate = true;

                BootstrapDialog.show({
                    cssClass: 'add-items-terms-modal info-bootstrap-dialog info-bootstrap-dialog--mw-570',
                    title: 'Terms & Conditions',
                    message: $('<div></div>'),
                    onhide: function(dialogRef){
                        if(!popupAddItem.termsSubmit){
                            closeFancyBox();
                        }
                    },
                    onshow: function(dialog) {
                        var $modal_dialog = dialog.getModalDialog();
                        var addValidationIfPossible = function () {
                            if(!validate){
                                return;
                            }

                            enableFormValidation(dialog.getMessage().find(".validateModal"));
                        }
                        $modal_dialog.addClass('modal-dialog-centered');

                        dialog.getMessage().append(content);
                        addValidationIfPossible();
                    },
                    buttons:buttons,
                    type: 'type-light',
                    size: 'size-wide',
                    closable: true,
                    closeByBackdrop: false,
                    closeByKeyboard: false,
                    draggable: false,
                    animate: true,
                    nl2br: false
                });
			},
			onCallModalValidateTerms: function(){
                popupAddItem.termsSubmit = true;
                BootstrapDialog.closeAll();
            },
			submitTerms: function(dialogItself){
                dialogItself.getMessage().find('.validateModal').submit();
			},
			validateCircle: function($el){
				var $newItem = $el.closest('.tabs-circle__item');
				var prevIndex = popupAddItem.currentCircle;
				var $prevItem = popupAddItem.$tabsNav.find('.tabs-circle__item:nth-child('+(prevIndex + 1)+')');
				var newIndex = $newItem.index();

				if(prevIndex < newIndex){
                    popupAddItem.self.eachTabs($prevItem);
					$newItem
						.find('.delimeter')
						.addClass('progress');

                    var interval = (newIndex - prevIndex);

                    if( interval > 1 ){
                        for(var i = newIndex; i > prevIndex; i--){
                            var $tab = popupAddItem.$tabsNav.find('.tabs-circle__item:nth-child(' + i + ')');
                            popupAddItem.self.eachTabs($tab);

                            $tab.find('.delimeter')
                                .addClass('progress');
                        }
                    }

				}else{
					$newItem
						.nextAll()
						.removeClass('complete incomplete')
						.find('.delimeter')
						.removeClass('progress');
				}

				popupAddItem.currentCircle = newIndex;
			},
			eachTabs: function($el){
				var name = $el.find('.link').data('name');
				var items = popupAddItem.inputs[name];
				var total = Object.size(items);
				var totalNow = 0;

                popupAddItem.self.saveText(tinyMCE.activeEditor)
                    .then(function () {
                        $.each(items, function(indexInputs, valueInputs){
                            var splitedName = valueInputs.split(' ');

                            if(splitedName.length > 1){
                                var val = "";

                                $.each(splitedName, function(indexSplit, valueSplit){
                                    var inputFind = popupAddItem.$formAdditem.find('[name="' + valueSplit + '"]');

                                    if(inputFind.length){
                                        var valSplit = inputFind.val();

                                        if(valSplit != ""){
                                            val = valSplit;
                                        }
                                    }
                                });

                                if(val != ""){
                                    totalNow++;
                                }
                            }else{
                                var inputFind = popupAddItem.$formAdditem.find('[name="' + valueInputs + '"]');
                                var val = "";

                                if(inputFind.length){
                                    if(inputFind.val() != ""){
                                        val = inputFind.val();
                                    }
                                }

                                if(val != ""){
                                    totalNow++;
                                }
                            }

                        });

                        if(total == totalNow){
                            $el.addClass('complete').removeClass('incomplete');
                        }else{
                            $el.addClass('incomplete').removeClass('complete');
                        }
                    }).catch(function (error) {
                        onRequestError(error);
                        onRequestEnd();
                    });
			},
			showTab: function(number){
				popupAddItem.navbar.find('.tabs-circle__item:nth-child(' + number + ') .link').tab('show');
			},
			showNextTab: function(){
                popupAddItem.self.showTab(popupAddItem.currentCircle + 2);
			},
			showPrevTab: function(){
                popupAddItem.self.showTab(popupAddItem.currentCircle);
			},
			hideContent: function () {
				showLoader('.fancybox-outer');
			},
			showContent: function () {
				hideLoader('.fancybox-outer');
			},
			changePopupTitle: function () {
				var $this = popupAddItem.navbar.find('.link.active');
				var $closeBtn = '<a title="Close" class="pull-right call-function" data-callback="closeFancyBox" data-message="Are you sure you want to close this window?"><span class="ep-icon ep-icon_remove-stroke"></span></a>';
				if($this.closest('.fancybox-skin').find('.fancybox-title a').length){
					$closeBtn = $this.closest('.fancybox-skin').find('.fancybox-title a')[0].outerHTML;
				}

				if($(window).width() < 768){
					$this.closest('.fancybox-skin').find('.fancybox-title').html(
						$this.find('.tabs-circle__txt').text() + $closeBtn
					);
				} else {
					$this.closest('.fancybox-skin').find('.fancybox-title').html(
						'<span class="fancybox-ttl">' + popupAddItem.ttlText + '</span> ' + $closeBtn
					);
				}
			},
			highlightTabsWithErros: function (form) {
				popupAddItem.navbar.find('.tabs-circle__item').removeClass('danger');
				popupAddItem.$formAdditem.find('.js-add-item-tab-pane-submit').each(function (index, element) {
					var tab = $(element);
					var formError = tab.find('.formError');
					if (formError.length) {
						popupAddItem.navbar
							.find('.link[href="#' + tab.attr('id') + '"]')
							.closest('.tabs-circle__item')
							.addClass('danger');

                        if (!tab.hasClass("active")) {
                            formError.remove();
                        }
					}
				});
			},
			validateVariants: function () {
                return new Promise(function (resolve, reject) {
                    if (popupAddItem.variationsWrapper.hasClass("active") && !popupAddItem.variantsOptionCombination.find(".js-item-add-variant").length) {
                        reject("At least one of the Combination of Variations is required.");
                    } else {
                        resolve(true);
                    }
                });
            },
			validate: function (form, button) {
				return popupAddItem.self.validateVariants()
                    .then(() => {
                        return popupAddItem.self.validateFields(form, button)
                        .catch(function (errors) {
                            popupAddItem.self.highlightTabsWithErros(form);
                            systemMessages(errors, 'error');

                            return false;
                        });
                    })
                    .catch(function (messages) {
                        systemMessages(messages, 'warning');

                        return false;
                    });
			},
			validateTab: function () {
				popupAddItem.$formAdditem.validationEngine("detach");
				popupAddItem.$formAdditem.validationEngine("attach", {
					updatePromptsPosition: true,
					promptPosition: "topLeft:0",
					autoPositionUpdate: true,
					focusFirstField: false,
					scroll: false,
					showArrow: false,
					addFailureCssClassToField: 'validengine-border',
					onValidationComplete: function(form, status){
						if (!status) {
							systemMessages(translate_js({ plug: 'general_i18n', text: 'validate_error_message' }), 'error');
						} else if (form) {
							var submitButton = form.find('.js-final-button');
							var submitAction = submitButton.data('callback') || null;
							if (null !== submitAction) {
								callFunction(submitAction, submitButton);
							}
						}
					}
				});
			},
			validateFields: function (form, button) {
				return new Promise(function (resolve, reject) {
					popupAddItem.statusValidate = false;

					form.validationEngine("validate", {
						validateNonVisibleFields: true,
						updatePromptsPosition: true,
						promptPosition: "topLeft:0",
						autoPositionUpdate: true,
						focusFirstField: false,
						scroll: false,
						showArrow: false,
						addFailureCssClassToField : 'validengine-border',
						onValidationComplete: function (validatedForm, status) {
							popupAddItem.statusValidate = true;

							if (!status) {
								reject(translate_js({ plug: 'general_i18n', text: 'validate_error_message' }));
							} else {
								resolve(true);
							}
						}
					});
				});
			},
			saveText: function (editor) {
				return new Promise(function (resolve) {
					var handler = function (event) {
						editor.off('SaveContent', handler);
						resolve(true);
					};

					editor.on('SaveContent', handler);
					tinyMCE.triggerSave();
				});
			},
			save: function (button) {
				return popupAddItem.self.validate(popupAddItem.$formAdditem, button).then(function (canSend) {
                    popupAddItem.$tabsNav.find('.link').addClass('disabled');

					if (canSend) {
						popupAddItem.self.hideContent();

						return popupAddItem.self.send(popupAddItem.$formAdditem, button);
					}
				});
			},
			send: function (form, button, custom) {
				var isCustomUrl = custom || Boolean(~~0);
				var formElement = $(form);
				var url = isCustomUrl ? (button.data('url') || null) : (formElement.attr('action') || null);
				var onRequestStart = function(){
					button.prop('disabled', true);
				};
				var onRequestEnd = function(){
					button.prop('disabled', false);
                };

                <?php if(verifyNeedCertifyUpgrade() && !isset($item['id'])){?>
                    var onRequestSuccess = function (response) {
                        onRequestEnd();
                        popupAddItem.self.callItemAddedSuccess();

                        return { response: response, button: button, form: formElement };
                    }
                <?php } else {?>
                    var onRequestSuccess = function (response) {
                        open_result_modal({
                            title: "Success",
                            subTitle: response.message,
                            type: response.mess_type,
                            buttons: [{
                                label: translate_js({plug:'BootstrapDialog', text: 'ok'}),
                                action: function(dialogRef){
                                    dialogRef.close();
									if(!dataT) {
										window.location.reload();
									}
                                }
                            }]
                        });
                        onRequestEnd();

                        return { response: response, button: button, form: formElement };
                    };
                <?php }?>

				if (null === url) {
					return false;
				}

				onRequestStart();
				popupAddItem.self.updatePriceAndVariations();

				return popupAddItem.self.saveText(tinyMCE.activeEditor)
					.then(function () {
                        var formData = formElement
                                        .serializeArray()
                                        .concat(popupAddItem.currentLocation || [])
                                        .filter(function (f) {
                                            return f;
                                        });
                        return postRequest(url, formData);
                    })
					.then(function (response) {
                        if (!popupAddItem.hasItem) {
                            callGAEvent('add_item_success', 'add-item');
                            callGAEventCoolDown();
                        }
                        return onRequestSuccess(response);
                    })
					.catch(function (error) {
						onRequestError(error);
						onRequestEnd();
					});
			},
			finalize: function (params) {
				if(params && params.response && params.response.mess_type == 'success'){
					$.fancybox.close();
					if (dataT) {
						dataT.fnDraw();
                    }
				}
			},
			saveItem: function (button) {
				return popupAddItem.self.save(button).then(function (params) {
                    popupAddItem.$tabsNav.find('.link').removeClass('disabled');

					if (params) {
						return popupAddItem.self.finalize(params);
					} else {
						popupAddItem.self.showContent();
					}
				});
            },
			onSubmitForm: function (e) {
				e.preventDefault;

				return false;
			},
			onShowTab: function (e) {
				// e.target - newly activated tab
				// e.relatedTarget - previous active tab
				var $this = $(e.target);
				var $navCurrent = $this.closest('.tabs-circle__item');
				var navCurrentIndex = $navCurrent.index();
				var navLength = popupAddItem.navbar.find('.tabs-circle__item').length;

				popupAddItem.self.validateCircle($this);

				popupAddItem.$formAdditem.scrollTop(0);
				$navCurrent.removeClass('danger');

				if (navCurrentIndex == 0) {
					$('#js-footer-choose-category').show();
                    popupAddItem.$footerFormItem.addClass('display-n_i');
                    popupAddItem.draftBtns.hide();
                    chooseCategory.self.scrollToCategorySelected();
				} else {
					$('#js-footer-choose-category').hide();
					popupAddItem.$footerFormItem.removeClass('display-n_i');
                    popupAddItem.draftBtns.show();

					if (navLength == (navCurrentIndex + 1)) {
						popupAddItem.$footerFormItem.find('.js-intermediate-button').hide();
                        popupAddItem.$footerFormItem.find('.js-final-button').show();
					} else {
						popupAddItem.$footerFormItem.find('.js-intermediate-button').show();
                        popupAddItem.$footerFormItem.find('.js-final-button').hide();
					}
				}

				if (popupAddItem.statusValidate) {
					popupAddItem.$formAdditem.validationEngine('validate', {
						updatePromptsPosition: true,
						promptPosition: "topLeft:0",
						autoPositionUpdate: true,
						focusFirstField: false,
						scroll: false,
						showArrow: false,
						addFailureCssClassToField: 'validengine-border'
					});
				}

				popupAddItem.self.changePopupTitle();
			},
            updatePriceAndVariations: function () {
                if(!popupAddItem.priceWrapper.hasClass("active")){
                    popupAddItem.priceWrapper.find("input").val("");
                } else if (!popupAddItem.variationsWrapper.hasClass("active")){
                    popupAddItem.variantsOptionShowWrapper.html("");
                    popupAddItem.variantsOptionSelectWrapper.html("");
                    popupAddItem.variantsOptionCombinationWrapper.html("");
                    popupAddItem.variantsOptionCombination.addClass("display-n");
                }
            },
            onPreviewAddItem: function ($this) {
                var inputs = '';
                popupAddItem.self.updatePriceAndVariations();
                popupAddItem.self.saveText(tinyMCE.activeEditor)
                    .then(function () {
                        var data = popupAddItem.$formAdditem.serializeArray();
                        $.each(data, function(index, el){
                            inputs += '<input type="hidden" name="' + el.name + '" value="' + el.value + '">';
                        });

                        var hash = (+new Date).toString(36);

                        $('body').append(
                                $('<form id="js-add-item-preview-form" action="' + __site_url + 'items/preview?=' + hash + '" method="post" target="_blank"></form>')
                                .append(inputs)
                            );

                        setTimeout(function(){
                            var $newForm = $('body #js-add-item-preview-form');
                            $newForm.submit();

                          setTimeout(function(){
                                $newForm.remove();
                            }, 1500);
                        }, 300);


                    })
                    .catch(function (error) {
                        $this.find('.txt').html(error);
                        onRequestError(error);
                    });
            },
            onCallbackReplaceCropImages: function(resp){
                if(popupAddItem.$formAdditem.find('input[name="item"]').length && dataT){
                    dtItemsList.fnDraw();
                }

                var d = new Date();
                var n = d.getTime().toString();

                // cropperImg.$mainImg.attr('href', resp.path + '?a=' + n);
                $('.js-add-item-change-main-photo').attr('src', resp.thumb + '?a=' + n);
            },
            <?php if( verifyNeedCertifyUpgrade() ){?>
                callItemAddedSuccess: function (e) {
                    var $this = $(this),
                        type = 'success',
                        subTitle = 'Your item has been successfully added.',
                        link = 'upgrade/popup_forms/item_added_success';

                    open_result_modal({
                        type: type,
                        subTitle: subTitle,
                        content: link,
                        isAjax: true,
                        delimeterClass: 'bootstrap-dialog--content-delimeter2',
                        validate: false,
                        classes: '',
                        closable: true,
                        buttons: [
                            {
                                label: 'Ok',
                                cssClass: "btn btn-light",
                                action: function (dialog) {
                                    dialog.close();
                                },
                            },
                            {
                                label: 'Get started',
                                cssClass: "btn btn-primary",
                                action: function (dialog) {
                                    location.href = __site_url + 'upgrade'
                                },
                            }
                        ]
                    });
                },
            <?php }?>
            <?php if (empty($item) || $item['draft']) {?>
                onSaveDraftAddItem: function(resp){
                    var url = popupAddItem.$formAdditem.attr('action') || null;
                    var idItem = popupAddItem.$formAdditem.find('input[name="item"]').val() || 0;
                    var urlImg = __site_url + 'public/storage/items/' + idItem + '/thumb_1_';

                    var onRequestSuccess = function (response) {
                        systemMessages(response.message, response.mess_type);
                        hideLoader(popupAddItem.wrapper);

                        if(response.mess_type == 'success'){
                            popupAddItem.$formAdditem.find('input[name="images_main"]').remove();
                            popupAddItem.$formAdditem.find('input[name="images_remove[]"]').remove();

                            popupAddItem.$formAdditem.find('input[name="images[]"]').each(function(){
                                var $this = $(this);
                                var imgName = $this.data('name');
                                $this.remove();

                                $('.js-select-variant-images .select-variant-images__option[data-image="' + imgName + '"]')
                                    .find('.image').attr('src', urlImg + imgName);

                                $('#js-add-item-variants-wr .js-item-add-variant[data-img="' + imgName + '"]')
                                    .find('.image').attr('src', urlImg + imgName);
                            })

                            if (typeof dataT !== 'undefined') {
                                dataT.fnDraw();
                            }

                            if(!popupAddItem.$formAdditem.find('input[name="item"]').length){
                                closeFancyBox();
                            }

                            if(response.images != undefined){
                                var htmlImages = '';

                                $.each(response.images, function(index, el){
                                    htmlImages += '<div class="fileupload2__item image-card3 js-fileupload-item">\
                                                        <span class="link js-fileupload-image">\
                                                            <img\
                                                                class="image"\
                                                                src="' + el.photo_url + '"\
                                                                alt="image upload" />\
                                                        </span>\
                                                        <div class="js-fileupload-actions fileupload2__actions">\
                                                            <a\
                                                                class="btn btn-light pl-10 pr-10 w-40 call-function"\
                                                                data-callback="fileploadRemoveItemImage"\
                                                                data-file="' + el.id + '"\
                                                                data-name="' + el.photo_name + '"\
                                                                data-message="Are you sure you want to delete this image?"\
                                                                title="Delete">\
                                                                <i class="ep-icon ep-icon_trash-stroke fs-17"></i>\
                                                            </a>\
                                                            <input type="hidden" name="images_validate[]" value="' + el.photo_name + '">\
                                                        </div>\
                                                    </div>';
                                });

                                $('#js-add-edit-item--formfield--image-wrapper').html(htmlImages);
                            }
                            callGAEvent("add_item_save_draft", "add-item");
                            callGAEventCoolDown();
                        }
                    };

                    if (null === url) {
                        return false;
                    }

                    showLoader(popupAddItem.wrapper);
                    popupAddItem.self.updatePriceAndVariations();

                    return popupAddItem.self.saveText(tinyMCE.activeEditor)
                            .then(function () {
                                var formData = popupAddItem.$formAdditem
                                                .serializeArray()
                                                .concat(popupAddItem.currentLocation || [])
                                                .concat({ name: 'save_draft', value: true })
                                                .filter(function (f) {
                                                    return f;
                                                });
                                return postRequest(url, formData);
                            })
                            .then(onRequestSuccess)
                            .catch(function (error) {
                                hideLoader(popupAddItem.wrapper);
                                onRequestError(error);
                            });
                },
            <?php }?>
		});
	}());

	$(function () {
		popupAddItem.init();
    });
</script>
