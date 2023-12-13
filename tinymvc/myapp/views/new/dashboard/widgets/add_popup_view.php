<link rel="stylesheet" type="text/css" media="screen" href="<?php echo fileModificationTime('public/css/widget/style_new.css'); ?>" />

<div class="js-modal-flex wr-modal-flex inputs-40">
    <div class="modal-flex__form">
		<ul class="nav nav-tabs nav-widgets nav--borders flex-display flex-jc--sb" role="tablist">
			<li class="nav-item mr-0">
				<a class="nav-link active pl-0" href="#widget-type" aria-controls="title" role="tab" data-toggle="tab">Widget type</a>
			</li>
			<li class="nav-item mr-0">
				<a class="nav-link" href="#widget-items" aria-controls="title" role="tab" data-toggle="tab">Items</a>
			</li>
			<li class="nav-item mr-0">
				<a class="nav-link pr-0" href="#widget-options" aria-controls="title" role="tab" data-toggle="tab">Options</a>
			</li>
		</ul>
        <div class="modal-flex__content">
			<div class="tab-content tab-content--borders pt-30">
				<div role="tabpanel" class="tab-pane fade show active" id="widget-type">
					<h3 class="epwidget-title">Select widget type</h3>

					<div id="widget-wrapper-1" class="epwidget-type mb-13">
						<?php tmvc::instance()->controller->view->display('new/dashboard/widgets/widget_1_view'); ?>

						<div class="epwidget-type__hover">
							Click to select this widget type
						</div>
					</div>

					<div id="widget-wrapper-2" class="epwidget-type">
						<?php tmvc::instance()->controller->view->display('new/dashboard/widgets/widget_2_view'); ?>

						<div class="epwidget-type__hover">
							Click to select this widget type
						</div>
					</div>
				</div>
				<div role="tabpanel" class="tab-pane fade" id="widget-items">
					<h3 class="epwidget-title">Select items</h3>

					<div class="epwidget-products">
						<?php if ($widget === false) {
							foreach ($sellerItems as $item) { ?>
								<div class="epwidget-products__item flex-card" data-id="<?php echo $item['id']; ?>" data-link="<?php echo __SITE_URL . 'item/' . strForURL($item['title']) . '-' . $item['id']; ?>">
									<div class="epwidget-products__img flex-card__fixed image-card3">
										<span class="link">
                                            <?php
                                                $item_img_link = getDisplayImageLink(array('{ID}' => $item['id'], '{FILE_NAME}' => $item['photo_name']), 'items.main', array( 'thumb_size' => 1 ));
                                            ?>
                                            <img
                                                class="image"
                                                src="<?php echo $item_img_link; ?>"
                                                alt="<?php echo $item['title']; ?>"
                                            />
										</span>
									</div>
									<div class="epwidget-products__ttl flex-card__float">
										<?php echo $item['title']; ?>
									</div>
									<div class="epwidget-products__actions">
										<i title="Add this item" class="add ep-icon ep-icon_ok-circle"></i>
										<i title="Remove this item" class="remove ep-icon ep-icon_remove-circle"></i>
									</div>
								</div>
							<?php }
						} else {
							$selectedItems = explode(',', $widget['items']);
							foreach ($sellerItems as $item) {
								$selected = in_array($item['id'], $selectedItems) ? 'selected' : '';
								?>
								<div class="epwidget-products__item flex-card <?php echo $selected; ?>" data-id="<?php echo $item['id']; ?>" data-link="<?php echo __SITE_URL . 'item/' . strForURL($item['title']) . '-' . $item['id']; ?>">
									<div class="epwidget-products__img flex-card__fixed image-card3">
										<span class="link">
                                            <img
                                                class="image"
                                                src="<?php echo getDisplayImageLink(array('{ID}' => $item['id'], '{FILE_NAME}' => $item['photo_name']), 'items.main', array( 'thumb_size' => 1 ));?>"
                                                alt="<?php echo $item['title']; ?>"
                                            />
										</span>
									</div>
									<div class="epwidget-products__ttl flex-card__float">
										<?php echo $item['title']; ?>
									</div>
									<div class="epwidget-products__actions">
										<i title="Add this item" class="add ep-icon ep-icon_ok-circle"></i>
										<i title="Remove this item" class="remove ep-icon ep-icon_remove-circle"></i>
									</div>
								</div>
							<?php }
						}
						?>
					</div>
				</div>
				<div role="tabpanel" class="tab-pane fade" id="widget-options">

					<div class="widget-size container-fluid-modal">
						<h3 class="epwidget-title pb-0">Choose the widget size &amp; target site</h3>

						<div class="widget-size-wrapper row">
							<div class="col-6">
								<label class="input-label">Width</label>
								<input id="widget-width" value="<?php echo $widget === false ? '' : $widget['width']; ?>" type="text" placeholder="Width in 'px' or '%'">
							</div>
							<div class="col-6">
								<label class="input-label">Height</label>
								<input id="widget-height" value="<?php echo $widget === false ? '' : $widget['height']; ?>" type="text" placeholder="Height in 'px'">
							</div>
							<div class="col-12">
								<label class="input-label">Target site</label>
								<input id="widget-target-site" value="<?php echo $widget === false ? '' : $widget['site']; ?>" type="text" placeholder="http://example.com">
							</div>
						</div>
					</div>

					<h3 class="epwidget-title pt-25">Widget preview</h3>
					<div style="display: none;" class="widget-selected-type" id="selected-widget-container"></div>
				</div>
			</div>
        </div>
        <div class="modal-flex__btns modal-flex__btns--50pr">
            <div class="modal-flex__btns-left">
                <a id="back-widget" class="btn btn-dark display-n call-function" data-callback="prevAddItem" href="#">
                    Back
                </a>
            </div>

            <div class="modal-flex__btns-right">
                <a id="next-widget" class="btn btn-primary call-function" data-callback="nextAddItem" href="#">
                    Next
                </a>

                <a id="save-widget" class="btn btn-success display-n" href="#">
                    Save widget
                </a>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">

    window.widgetApp = {
        selectedWidget: null,
        widgetData: false,
        init: function(widgetData) {
            var self = this;

            self.widgetData = widgetData;
            if (self.widgetData) {
                self.selectWidgetType($('#widget-wrapper-' + self.widgetData['widget_type']));
            }

            $('#widget-width').on('change', function () {
                self.selectedWidget.setWidth($(this).val());
            });

            $('#widget-height').on('change', function () {
                self.selectedWidget.setHeight($(this).val());
            });

            $('#save-widget').on('click', function (e) {
                e.preventDefault();
                if (self.selectedWidget === null) return systemMessages('Please select widget type', 'error');
                self.selectedWidget.saveData();
            });

            $('.epwidget-type').on('click', function () {
                self.selectWidgetType($(this));
            });

            $('#select-widget-type').on('click', function (e) {
                e.preventDefault();
                $('#select-widget-type').hide();
                $('#step-2').hide();
                //$('#widgets-container').show();
                $.fancybox.reposition();
                self.selectedWidget = null;
            });


            $('.epwidget-products__item').on('click', function () {
                var $this = $(this),
                    isSelected = $this.hasClass('selected');

                if (self.selectedWidget === null) {
                    return systemMessages('Please select widget type', 'error');
                }

                self.selectedWidget[isSelected ? 'removeItem' : 'addItem']($this);
            });
        },
        selectWidgetType: function($this) {
            var $widget = $('.widget-types', $this).clone(),
                self = this;

            //$('#widgets-container').hide();
            $('#selected-widget-container').show().html($widget);
			$this.addClass('active').siblings().removeClass('active');
			$this.find('.epwidget-type__hover').text('Selected widget type.');
			$this.siblings().find('.epwidget-type__hover').text('Click to select this widget type.');

            try {
                self.selectedWidget = new Widget($widget);
            } catch (e) {
                return systemMessages(e.message, 'error');
            }

            $('#select-widget-type').show();
            $('#step-2').show();
            $.fancybox.reposition();
            $('.epwidget-products__item').each(function () {
                var $this = $(this);
                if ($this.hasClass('selected')) {
                    self.selectedWidget.addItem($this);
                } else {
                    self.selectedWidget.removeItem($this);
                }
            });
        }
    };

    function Widget($widget) {
        this.$el = $widget;
        if (this.$el.length === 0) throw new Error('Widget not found');

        if (!this.$el.data('type')) throw new Error('Widget type not specified');

        this.$body = $('.widget-types__body', this.$el);
        if (this.$body.length === 0) throw new Error('Widget body not found');

        this.$template = $('.item-template', this.$el);
        if (this.$template.length === 0) throw new Error('Widget item template not found');

        this.$header = $('.widget-types__header', this.$el);
        if (this.$header.length === 0) throw new Error('Widget header not found');


        this.items = [];

        this.type = this.$el.data('type');

        this.defaultWidth = 470;
        this.defaultHeight = 320;

        this.width = null;
        this.widgetHeight = null;
        this.headerHeight = this.$header.outerHeight();
        this.bodyHeight = this.defaultHeight - this.headerHeight;

        // console.log(this);
        this.setWidth($('#widget-width').val());
        this.setHeight($('#widget-height').val());
    }

    Widget.prototype.setWidth = function(width) {
        width = width.trim();
        if (width !== '') {
            if (width.indexOf('px') !== -1 || width.indexOf('%') === -1) {
                width = parseInt(width.replace('px', ''));
                width = (width === 0 ? this.defaultWidth : width) + 'px';
            } else {
                width = parseInt(width.replace('%', ''));
                width = width === 0 ? this.defaultWidth + 'px' : width + '%';
            }

            this.width = width;
        } else {
            this.width = this.defaultWidth + 'px';
        }

        this.$el.css('width', this.width);
    };

    Widget.prototype.setHeight = function(height) {
        height = height.trim();
        if (height !== '') {
            if (height.indexOf('px') === -1) {
                height = parseInt(height);
            } else {
                height = parseInt(height.replace('px', ''));
                height = height === 0 ? this.defaultHeight : height;
            }

            this.bodyHeight = (height - this.headerHeight) + 'px';
            this.widgetHeight = height + 'px';
        } else {
            this.bodyHeight = (this.defaultHeight - this.headerHeight) + 'px';
            this.widgetHeight = this.defaultHeight + 'px';
        }

        this.$el.css('height', this.widgetHeight);
        this.$body.css('height', this.bodyHeight);
        $.fancybox.reposition();
    };

    Widget.prototype.removeDefaultItems = function() {
        $('.widget-types__item.default', this.$body).remove();
    };

    Widget.prototype.hasItem = function($item) {
        return $('.seller-item-' + $item.data('id'), this.$el).length > 0;
    };

    Widget.prototype.addItem = function($item) {
        this.removeDefaultItems();

        if (this.hasItem($item)) return;

        var $template = this.$template.clone().removeClass('item-template'),
            $titleFiled = $template.find('.widget-types__item-title'),
            itemTitle = $item.find('.epwidget-products__ttl').text(),
            itemId = $item.data('id');

        $template.addClass('seller-item-' + itemId);
        $template.attr('title', itemTitle)
            .attr('href', $item.data('link'));
        $template.find('.widget-types__item-image .image')
            .attr('src', $item.find('.image').attr('src'))
            .attr('alt', itemTitle);

        if ($titleFiled.length) {
            $titleFiled.text(itemTitle);
        }

        this.$body.append($template);

        $item.addClass('selected');

        this.items.indexOf(itemId) === -1 && this.items.push(itemId);
    };

    Widget.prototype.removeItem = function($item) {
        var itemId = $item.data('id');

        $('.seller-item-' + itemId, this.$body).remove();
        $item.removeClass('selected');

        var itemIndex = this.items.indexOf(itemId);
        itemIndex !== -1 && this.items.splice(itemIndex, 1);
    };

    Widget.prototype.getTargetSite = function () {
        var expression = /^https?:\/\/(www\.)?[-a-zA-Z0-9]{2,256}\.[a-z]{2,6}\/?$/,
            url = $('#widget-target-site').val(),
            isValidUrl = (new RegExp(expression)).test(url.toString());

        return isValidUrl ? url : null;
    };

    Widget.prototype.getData = function () {
        return {
            items: this.items.join(','),
            type: this.type,
            width: this.width,
            widgetHeight: this.widgetHeight,
            bodyHeight: this.bodyHeight,
            targetSite: this.getTargetSite(),
            id: widgetApp.widgetData ? widgetApp.widgetData['id'] : ''
        };
    };

    Widget.prototype.saveData = function () {
        if (this.items.length === 0) return systemMessages('Please add at least one item to the widget', 'error');
        if (this.getTargetSite() === null) return systemMessages('Please enter a valid URL address of target site', 'error');

        showLoader('.js-modal-flex');
        $.ajax({
            type: 'post',
            url: '/dashboard/save_widget',
            data: this.getData(),
            dataType: 'json',
            success: function (response) {
                systemMessages(response.message, response.mess_type);
                if (response.mess_type === 'success') {
                    setTimeout(function () {
                        $('<a style="display: none;" class="fancybox fancybox.ajax" data-title="Widget code" href="<?php echo __SITE_URL; ?>dashboard/widget_code_popup?id=' + response.id + '"></a>').appendTo($('body')).trigger('click');
                    }, 0);
                    closeFancyBox();
                    window.widgetsTable && window.widgetsTable.fnDraw(false);
                } else {
                    hideLoader('.js-modal-flex');
                }
            }
        });
    };

    var nextAddItem = function(){
        $('.nav-widgets .active').closest('.nav-item').next('.nav-item').find('.nav-link').trigger('click');
    }

    var prevAddItem = function(){
        $('.nav-widgets .active').closest('.nav-item').prev('.nav-item').find('.nav-link').trigger('click');
    }

	$(function(){
        $('.nav-widgets a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            e.target // newly activated tab
            e.relatedTarget // previous active tab

            var $this = $(e.target);
            var $navCurrent = $this.closest('.nav-item');
            var navCurrentIndex = $navCurrent.index();

            var $backWidget = $('#back-widget');
            var $nextWidget = $('#next-widget');
            var $saveWidget = $('#save-widget');

            if(navCurrentIndex == 0){
                $backWidget.addClass('display-n');
                $saveWidget.addClass('display-n');
            }else if(navCurrentIndex == 1){
                $backWidget.removeClass('display-n');
                $nextWidget.removeClass('display-n');
                $saveWidget.addClass('display-n');
            } else if(navCurrentIndex == 2){
                $backWidget.removeClass('display-n');
                $nextWidget.addClass('display-n');
                $saveWidget.removeClass('display-n');
            }
        });

		<?php if(!empty($widget)){ ?>
		widgetApp.init(<?php echo json_encode($widget); ?>);
		<?php }else{ ?>
		widgetApp.init({widget_type: 1});
		<?php } ?>
	});
</script>
