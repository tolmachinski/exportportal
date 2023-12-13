var compare_item = function(obj){
	var $thisBtn = $(obj);
	var item = $thisBtn.data('item');

	item = item.toString();

	if(existCookie(cookie_compare_name)){
		var items = JSON.parse(getCookie(cookie_compare_name));
		if(!Array.isArray(items)){
			items = [items.toString()];
		}

		var position = $.inArray(item, items);

		if( position == -1){
			items.push(item);
			removeCookie(cookie_compare_name);
			setCookie(cookie_compare_name,items, 7);
			$thisBtn.find('span').html(translate_js({plug:'general_i18n', text:'item_card_label_in_compare'})).addClass('active');
		} else{
			items.splice(position,1);
			if(items.length){
				removeCookie(cookie_compare_name);
				setCookie(cookie_compare_name, items, 7);
			}else{
				removeCookie(cookie_compare_name);
			}
			$thisBtn.find('span').html(translate_js({plug:'general_i18n', text:'item_card_label_compare'})).removeClass('active');
		}
	} else{
		setCookie(cookie_compare_name, [item], 7);
		$thisBtn.find('span').html(translate_js({plug:'general_i18n', text:'item_card_label_in_compare'})).addClass('active');
	}

	_actualize_compare();
}

function _init_compare(){
	if(existCookie(cookie_compare_name)){

		var items = JSON.parse(getCookie(cookie_compare_name));
		$('body a.i-compare').each(function(){
			var $compareBtn = $(this);
			var item = $compareBtn.data('item');
				item = item.toString();

			if($.inArray(item, items) != -1){
				$compareBtn.find('span').html('In compare').addClass('active');
			}
		});

		$('.dynamic-status-compare').append('<span class="epuser-line__circle-sign bg-orange"></span>');
		showMarkerUserMepAction('compare', true);
	}else{
		$('.dynamic-status-compare .epuser-line__circle-sign').remove();
		showMarkerUserMepAction('compare', false);
	}
}

function _actualize_compare(){
	var $circles = $('.dynamic-status-compare .epuser-line__circle-sign');

	if(existCookie(cookie_compare_name)){
		if(!$circles.length){
			$('.dynamic-status-compare').append('<span class="epuser-line__circle-sign bg-orange"></span>');
			showMarkerUserMepAction('compare', true);
		}
	}else{
		if($circles.length){
			$circles.remove();
			showMarkerUserMepAction('compare', false);
		}
	}
}
