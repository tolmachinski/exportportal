<script type="text/javascript">
//to DELETE
// var userBasketApi;
// var $userBasket;
// var userBasketTimeout;

// var centerListApi;
// var $centerList;
// var centerListTimeout;

var $selectCity;
var selectState;

// var $shippingEstimateSelectCity;
// var shippingEstimateSelectState;

// var $shippingEstimateItemSelectCity;
// var shippingEstimateItemSelectState;

var widthChanged = false;
var heightChanged = false;

//to DELETE
//var calcHeightClasses = [{'minus':0, 'name': 'userBasket', 'width': true}];

$(document).ready(function() {
    //to DELETE
	// $centerList = $('#basket-users-list');
	// centerListApi = $centerList.jScrollPane().data('jsp');
	// $userBasket = $('#basket-user');
	// userBasketApi = $userBasket.jScrollPane().data('jsp');

	/* create-order form */
	$('body').on('change', ".create-order select#states", function(){
		selectState = this.value;
		$selectCity.empty().trigger("change").prop("disabled", false);

		if(selectState != '' || selectState != 0){
			var select_text = translate_js({plug:'general_i18n', text:'form_placeholder_select2_city'});
		} else{
			var select_text = translate_js({plug:'general_i18n', text:'form_placeholder_select2_state_first'});
			$selectCity.prop("disabled", true);
		}
		$selectCity.siblings('.select2').find('.select2-selection__placeholder').text(select_text);
	});

	$('body').on('change', ".create-order #country", function(){
		selectCountry($(this), 'select#states');
		selectState = 0;
		$selectCity.empty().trigger("change").prop("disabled", true);
	});
	/* end create-order form */
	<?php if(!empty($select_item)){?>
		mainBlockActualize(<?php echo $select_item;?>);
	<?php }?>

	//change item quantity
	$('body').on('change', ".basket-user__item .quantity-val", function(){
		calculateItemBasket($(this).closest('.basket-user__list-item'));
	});

	$('body').on('click', '.order-users-list__item', function(e){
		e.preventDefault();
		var company = $(this).data('company');
		mainBlockActualize(company);
	});

	reinitBlocks($(window).width());
});

jQuery(window).on('resizestop', function () {
	if($(this).width() != widthBrowser){
		widthBrowser = $(this).width();

		reinitBlocks($(this).width());
	}
});

var reinitBlocks = function(width){
	if(width > 990){
		$('#basket-users-list').show();

        //to DELETE
		// if(centerListApi == undefined){
		// 	// console.log('init');
		// 	centerListApi = $('#basket-users-list').jScrollPane().data('jsp');
		// 	userBasketApi = $('#basket-user').jScrollPane().data('jsp');
		// }

	// }else{
	// 	if(centerListApi !== undefined){
	// 		// console.log('destroy');
	// 		centerListApi.destroy();
	// 		centerListApi = undefined;
	// 		userBasketApi.destroy();
	// 		userBasketApi = undefined;
	// 	}
	}
}

var callAllItems = function(){
	showAllItems();
    //toDELETE
	//calcWidthBlockSimple(calcHeightClasses, true);
}

function calculateBasket(parentUl,id_item){
	var parentDiv = parentUl.parent('.basket-user__item');
	var company = parentDiv.data('company');
	var calcTotal = 0;
	var count = parentUl.children('.basket-user__list-item').length;

	parentUl.find('.basket-user__list-item').each(function(){
		var $li = $(this);
		var priceB = $li.find('.price-val');
		var quantityB = $li.find('.quantity-val');
		var totalB = $li.find('.total-val');

		var price = parseFloat(priceB.val());
		var quantity = parseInt(quantityB.val());
		var total = price * quantity;
		totalB.html(get_price(total.toFixed(2), true));
		calcTotal += parseFloat(total,10);
	});

	calcTotal = calcTotal.toFixed(2);
	var calcTotalCurrency = get_price(calcTotal, true);

	//title item group
	parentUl.prev('.basket-user__title').find('.basket-user__title-nr-val').text(count);
	//total item group
	parentUl.next('.basket-user__footer').find('.basket-user__total .value').html(calcTotalCurrency);
	parentUl.next('.basket-user__footer').find('.basket-user__real-price .value').html('$' + calcTotal);
	//user item group
	$('#basket-users-list .order-users-list__item[data-company="' + company + '"]').find('.basket-user__title-nr-val').html(count);

	//change header basket
	var $basketHeaderItem = $('.show-basket-b').find('#HeaderBasket-'+company);
	if($basketHeaderItem.length){
		var $nrBasketHeaderItem = $basketHeaderItem.find('.basket-user__title .basket-user__title-nr-val');
		var nrBasketHeaderItem = parseInt($nrBasketHeaderItem.html());
		if(nrBasketHeaderItem > 0)
			$nrBasketHeaderItem.html(nrBasketHeaderItem - 1)
		$basketHeaderItem.find('.basket-user__list-item[data-item='+id_item+']').remove();
	}
}

function removeItemGroup(parentDiv, company){
	parentDiv.remove();

	if(!$('#basket-user .basket-user__item').length){
		$('#columns-content-right').html('<div class="default-alert-b"><i class="ep-icon ep-icon_remove-circle"></i> <span>No items in the basket.</span></div>');
	}

	$('#basket-users-list .order-users-list__item[data-company="'+company+'"]').remove();

	if($('#basket-users-list .order-users-list__item').length <= 0){
		$('#basket-users-list .order-users-list-ul').html('<li class="default-alert-b"><i class="ep-icon ep-icon_remove-circle"></i> <span>No items in the basket.</span></li>');
	}

	if($('#basket-user .basket-user__item').length <= 0 && $('#basket-users-list .order-users-list__item').length <= 0){
		$('.epuser-line__item-basket span.epuser-line__circle-sign').remove();
	}

	showAllItems();
    //toDELETE
	//calcWidthBlockSimple(calcHeightClasses);
}

function showAllItems(){
    var $similarProductsSlider = $("#js-basket-similar-products-slider");

	$('#basket-users-list .order-users-list__item').removeClass('active');
	$('.basket-user__item').show().removeClass('active');

    if ($similarProductsSlider.length) {
        $similarProductsSlider.slick("destroy");
        $("#js-similar-container-wr").html("");
    }
}

function calculateItemBasket(parentLi){
	var parentUl = parentLi.closest('.basket-user__list');
	var item = parentLi.data('basket-item');
	var quantityB = parentLi.find('.quantity-val');
	var quantity = intval(quantityB.val());
	var calcTotal = 0;

	$.ajax({
		url: 'basket/ajax_basket_operation/check_item_quantity',
		type: 'POST',
		data:  {item: item, quantity: quantity},
		beforeSend: function(){
			showLoader(parentLi, 'Checking quantity...');
		},
		dataType: 'json',
		success: function(resp){
			hideLoader(parentLi);

			if(resp.mess_type != 'success'){
				quantity = resp.max_quantity;
				quantityB.val(quantity);
				systemMessages(resp.message, resp.mess_type);
			}

			parentUl.find('.basket-user__list-item').each(function(){
				var $li = $(this);
				var priceB = $li.find('input[name=price]');
				var quantityB = $li.find('.quantity-val');
				var $totalB = $li.find('.total-val');
				var $realtotalB = $li.find('.real-val');

				var price = floatval(priceB.val());
				quantity = intval(quantityB.val());
				quantityB.val(quantity);
				var total = price * quantity;

				$totalB.html(get_price(total,true));
				$realtotalB.html('$'+get_price(total,false));

				calcTotal += floatval(total);
			});

			parentUl.next('.basket-user__footer').find('.basket-user__total .value').html(get_price(calcTotal,true));
			parentUl.next('.basket-user__footer').find('.basket-user__real-price .value').html('$'+get_price(calcTotal, false));
		}
	});
}

function locationSelect($thisForm){
	var locationArray = '';

	//country
	var $selectCountry = $thisForm.find('select[name="port_country"]');
	var id_country = $selectCountry.val();
	if(id_country != '')
		locationArray += $selectCountry.children('option[value='+id_country+']').text();

	//state
	if($thisForm.find('select[name="states"]').length){
		var $selectStates = $thisForm.find('select[name="states"]');
		var id_state = $selectStates.val();
		if(id_state != '')
			locationArray += ', '+$selectStates.children('option[value='+id_state+']').text();
	}

	//city
	if($thisForm.find('select[name="port_city"]').length){
		var $selectCity = $thisForm.find('select[name="port_city"]');
		var id_city = $selectCity.val();
		if(id_city != '')
			locationArray += ', '+$selectCity.children('option[value='+id_city+']').text();
	}

	locationArray += ', '+$thisForm.find('input[name="zip"]').val();
	locationArray += ', '+$thisForm.find('textarea[name="address"]').val();

	return locationArray;
}

function mainBlockActualize(company){
    var similarProductsSliderSelector = "#js-basket-similar-products-slider";

    if ($(similarProductsSliderSelector).length) {
        lazyLoadingInstance(similarProductsSliderSelector + " .js-lazy");
    }

	$('#basket-users-list .order-users-list__item[data-company=' + company + ']').addClass('active').siblings().removeClass('active');
	$('#basket-user .basket-user__item[data-company=' + company + ']').show().siblings().hide();
    if($.fancybox.isOpen) {
        $.fancybox.close();
    }
    //toDELETE
	//calcWidthBlockSimple(calcHeightClasses, true);
}

var removeBasketItem = function(obj){
	var $this = $(obj);
	var item = $this.data('item');
	var $listItem = $this.closest('.basket-user__list');
	showLoader($listItem, 'Remove...');

	$.ajax({
		url: 'basket/ajax_basket_operation/delete_one',
		type: 'POST',
		data:  {id : item},
		dataType: 'json',
		success: function(resp){
			hideLoader($listItem);
			systemMessages(resp.message, resp.mess_type);

			if(resp.mess_type == 'success'){
				var parentDiv = $this.closest(".basket-user__item");
				var parentLi = $this.closest(".basket-user__list-item");
				var parentUl = $this.closest(".basket-user__list");
				var company = parentDiv.data('company');
				parentLi.remove();

                if (!Number(resp.items_total)) {
                    var circleSign = $(".epuser-line__icons-item--basket .epuser-line__circle-sign");
                    if (circleSign.length) {
                        circleSign.remove();
                    }
                }

				if(parentUl.find('.basket-user__list-item').length == 0)
					removeItemGroup(parentDiv, company);
				else{
					calculateBasket(parentUl, item);
                    //toDELETE
					//userBasketApi.reinitialise();
				}
			}
		}
	});
}

var confirmOrder = function(seller, basket_item){

	if(basket_item > 0){
		var parentLi = $('.basket-user__item .basket-user__list-item#item-' + basket_item);
		var parentDiv = parentLi.closest(".basket-user__item");
		var parentUl = parentLi.closest(".basket-user__list");
		var company = parentDiv.data('company');
		parentLi.remove();

		if(parentUl.find('.basket-user__list-item').length == 0)
			removeItemGroup(parentDiv, company);
		else
			calculateBasket(parentUl,basket_item);
	}else{
		removeItemGroup($('.basket-user__item[data-company="' + seller + '"]'), seller);
	}

}

function set_data_item(opener){
	var $this = $(opener);
	if($this.data('action') == 'by_item'){
		var quantity = parseInt($this.closest('.basket-user__list-item').find('.quantity-val').val(), 10);

		if(quantity == 0)
			return false;

		$this.data('items', $this.data('item')+ 'x' + quantity);
	}else{
		var $items = $this.closest('.basket-user__item').find('.basket-user__list-item');
		var items = [];

		$items.each(function(){
			var quantity = parseInt($(this).find('.quantity-val').val(), 10);
			items.push($(this).data('item')+ 'x' + quantity);
		});

		if(items.length){
			$this.data('items', items.join());
		}else{
			return false;
		}
	}
}
</script>

<div class="container-center dashboard-container">
	<div class="dashboard-line">
        <h1 class="dashboard-line__ttl">Basket</h1>

        <div class="dashboard-line__actions">
			<!-- <a class="btn btn-light fancybox fancybox.ajax" href="<?php echo __SITE_URL;?>user_guide/popup_forms/show_doc/35" title="View basket documentation" data-title="View basket documentation" target="_blank">User guide</a> -->

			<a class="btn btn-primary dn-lg-min mnw-100pr-sm fancybox" href="#basket-users-list" data-mnh="100%" data-title="Sellers">
                Sellers
            </a>
		</div>
	</div>

	<div class="columns-content inputs-40">
		<div id="columns-content-left" class="columns-content__one dn-lg w-30pr-lg columns-content__one--350">
			<div class="columns-content__ttl">
				<span>By Sellers</span>
				<a class="call-function txt-normal" data-callback="callAllItems" href="#">All items</a>
			</div>

			<div id="basket-users-list" class="order-users-list basket-users-list jscroll-init" >
				<ul class="order-users-list-ul clearfix">
					<?php tmvc::instance()->controller->view->display('new/basket/users_list_view'); ?>
                </ul>
            </div><!-- offer-users-list -->

		</div><!-- sidebar -->

		<div id="columns-content-right" class="columns-content__one">
			<?php tmvc::instance()->controller->view->display('new/basket/basket_list_view'); ?>
		</div><!-- right block -->
	</div><!-- my-order-main-content -->
</div>
