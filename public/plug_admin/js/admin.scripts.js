var bootstrapDialogMessageAttach,
	btnOpenBootstrapDialogMessageAttach;

$(document).ready(function() {
    // Add some specific configurations to ajax
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

	//	validationEngine
	$.fn.validationEngineLanguage = function(){};
	$.validationEngineLanguage = {
		newLang: function(){
			$.validationEngineLanguage.allRules = translate_js_lang({plug:'validationEngine'});
		}
	};
	$.validationEngineLanguage.newLang();
//	END validationEngine

	if($('#ajax_res p, #ajax_res ul').length > 0){
		$('#ajax_res').delay(5000).fadeOut('slow', function(){ $(this).empty() });
	}
	$( window ).resize(function() {
        $.fancybox.reposition();
    });
	$(".validengine").validationEngine('attach',
	    {
            promptPosition : "centerRight",
            autoPositionUpdate : true,
            scroll: false,
            onFailure: function(){alert('failure')}
	    }
	);

	$('body').on('click', 'a[rel=view_filter_panel]', function(e) {
		$('.filter-admin-panel').slideToggle('slow');
		e.preventDefault();
	});

	$("span.toggle").click(function(){
		var elem = $(this).next();
			  span = $(this);
			elem.slideToggle(200,function (){
				var txt = elem.is(':visible') ? 'collapse' : 'show';
				span.find("i").html(txt);
			});
	});

	var widthWrFilter = 0;
	//filter panel
	$('body').on('click', '.wr-filter-admin-panel .btn-display', function(){
		var $thisBtn = $(this);

		if(!$thisBtn.hasClass('hide-btn')){
			widthWrFilter = $thisBtn.closest('.wr-filter-admin-panel').css('width');
			$thisBtn.closest('.wr-filter-admin-panel').animate({'left': "-"+widthWrFilter}, 500, function(){
				$thisBtn.addClass('hide-btn').children('span').html('&raquo;');
				$('.wr-filter-admin-panel .wr-hidden').hide();
			});
		}else{
			$thisBtn.closest('.wr-filter-admin-panel').animate({'left': 0}, 500, function(){
				$thisBtn.removeClass('hide-btn').children('span').html('&laquo;');
				$('.wr-filter-admin-panel .wr-hidden').show();
			});
		}
	});

	$('body').on('click', '.wr-filter-admin-panel .wr-hidden', function(){
		var $thisBlock = $(this);

		if($thisBlock.is(':visible')){
			$thisBlock.closest('.wr-filter-admin-panel').animate({'left': "-"+widthWrFilter}, 500, function(){
				$('.wr-filter-admin-panel .btn-display').addClass('hide-btn').children('span').html('&raquo;');
				$thisBlock.hide();
			});

		}
	});

	$('.wr-filter-admin-panel .btn-display').trigger('click');

	$('#btn-scrollup').click(function(){
		$("html, body").animate({ scrollTop: 0 }, 600);
	});

	$('body').on('click', '#btn-show-new-items', function(e){
		e.preventDefault();
		e.stopPropagation();
		$(this).hide();
	});

	$("body").on('click', '.confirm-dialog', function(e){
		var $thisBtn = $(this);
		e.preventDefault();

		BootstrapDialog.show({
			message: $thisBtn.data('message'),
			closable: false,
			draggable: true,
            onshown: function(){
                var $button = $('.js-button-atas');
                var atas = $thisBtn.data("atas") || undefined;

                if (atas) {
                    $button.attr("atas", atas);
                } else if ($thisBtn.attr("atas")) {
                    $button.attr("atas", "global_confirm-dialog_ok_btn");
                }
            },
			buttons: [{
				label: translate_js({plug:'BootstrapDialog', text: 'ok'}),
				cssClass: 'btn-success js-button-atas',
				hotkey: 13,
				action: function(dialogRef){
					var callBack = $thisBtn.data('callback');
					var $button = this;
                    $button.disable();

					window[callBack]($thisBtn);
					dialogRef.close();
				}
			},
			{
				label: translate_js({plug:'BootstrapDialog', text: 'cancel'}),
				hotkey: 32,
				action: function(dialogRef){
					dialogRef.close();
				}
			}]
		});
	});

	$(".fancyboxVideo").fancybox({
		loop : false,
		helpers : {
			title: {
				type: 'outside',
				position: 'top'
			},
			media:{}
		},
		modal: true,
		padding: 0,
		closeBtn : true,
		closeBtnWrapper: '.fancybox-title-outside-wrap',
		beforeLoad : function() {
			var $elem = this.element;
			if($elem.data('title'))
				this.title = $elem.data('title');

			if($elem.data('h')){
				this.autoHeight = false;
				this.height = $elem.data('h');
			}

			if($elem.data('w')){
				this.width = $elem.data('w')
				this.autoWidth = false;
			}

			this.keys = {
				close  : [27]
			};
		}
	},".fancyboxVideo");

    $(".fancybox").fancybox({
        loop : false,
		helpers : {
			title: {
				type: 'over',
				position: 'top'
			}
		},
        modal: true,
        padding: 0,
		closeBtn : true,
		closeBtnWrapper: '.fancybox-skin .fancybox-title',
        beforeLoad : function() {
            var $elem = this.element;

			if($elem.data("before-callback") != undefined){
				window[$elem.data("before-callback")](this);
			}

            if($elem.data('title'))
                this.title = $elem.data('title');

            if($elem.data('h')){
                this.autoHeight = false;
                this.height = $elem.data('h');
            }

            if($elem.data('w')){
                this.width = $elem.data('w')
                this.autoWidth = false;
            }

			this.keys = {
				close  : [27]
			};
        }
    },".fancybox");

	$(".fancyboxGallery").fancybox({
		loop : false,
		helpers : {
			title: {
				type: 'outside',
				position: 'top'
			}
		},
		padding: 0,
		closeBtn : true,
		closeBtnWrapper: '.fancybox-title-outside-wrap',
		beforeLoad : function() {
			var $elem = this.element;
			this.title = "&nbsp;";

			if($elem.data('title'))
				this.title = $elem.data('title');

		}
	},".fancyboxGallery");

	$(".fancyboxIframe").fancybox({
		loop : false,
		helpers : {
			title: {
				type: 'over',
				position: 'top'
			}
		},
		modal: true,
		closeBtn : true,
		closeBtnWrapper: '.fancybox-skin .fancybox-title',
		type: "iframe",
		iframe: {
		   preload : false // this will prevent to place map off center
		},
		padding: 0,
		beforeLoad : function() {
			var $elem = this.element;

			if($elem.data('title'))
				this.title = $elem.data('title');

			if($elem.data('h')){
				this.autoHeight = false;
				this.height = $elem.data('h');
			}

			if($elem.data('w')){
				this.width = $elem.data('w')
				this.autoWidth = false;
			}

			this.keys = {
				close  : [27]
			};
		}
	},".fancyboxIframe");

    $(".fancyboxValidateModal").fancybox({
		minWidth: 500,
        loop : false,
		helpers : {
			title: {
				type: 'over',
				position: 'top'
			}
		},
        modal: true,
        padding: 0,
		closeBtn : true,
		closeBtnWrapper: '.fancybox-skin .fancybox-title',
		onCancel: function () {
			if (this.inner) {
				this.inner.trigger('fancybox:on-cancel');
			}
		},
        beforeLoad : function() {
			var $elem = this.element;
			if ($elem) {
				if($elem.data("before-callback") != undefined){
					window[$elem.data("before-callback")](this);
				}

				if($elem.data('title'))
					this.title = $elem.data('title');

				if($elem.data('h')){
					this.autoHeight = false;
					this.height = $elem.data('h');
				}

				if($elem.data('w')){
					this.width = $elem.data('w')
					this.autoWidth = false;
				}
			}

			this.keys = {
				close  : [27]
			};

			this.ajax.form_submit_callback = window[$($elem).data('submit-callback')];

			if (this.inner) {
				this.inner.trigger('fancybox:before-load');
			}
        },
		afterLoad : function () {
			if (this.inner) {
				this.inner.trigger('fancybox:after-load');
			}
		},
		beforeShow : function () {
			if (this.inner) {
				this.inner.trigger('fancybox:before-show');
			}
		},
		afterShow : function () {
			if (this.inner) {
				this.inner.trigger('fancybox:after-show');
			}
		},
		beforeClose : function () {
			if (this.inner) {
				this.inner.trigger('fancybox:before-close');
			}
		},
		afterClose : function () {
			if (this.inner) {
				this.inner.trigger('fancybox:after-close');
			}
		},
		onUpdate: function () {
			if (this.inner) {
				this.inner.trigger('fancybox:on-update');
			}
		},
        ajax: {
            complete: function(jqXHR, textStatus) {
                var form_submit_callback = this.form_submit_callback;

				$(".validateModal").validationEngine('attach', {
					promptPosition : "topLeft:0",
					autoPositionUpdate : true,
					scroll: false,
                    focusFirstField: false,
					onValidationComplete: function(form, status) {
						if (status) {
                            var callback = $(form).data("callback") || null;
							if (callback) {
                                callFunction(callback, form, form_submit_callback);
                            } else {
								modalFormCallBack(form, form_submit_callback);
                            }
						} else {
							systemMessages(translate_js({ plug: 'general_i18n', text: 'validate_error_message' }), 'error');
						}
					}
				});
            }
        }
    }, ".fancyboxValidateModal");

    $(".fancyboxValidateModalDT").fancybox( {
        loop: false,
		helpers: {
			title: {
				type: 'over',
				position: 'top'
			},
            overlay: {
                locked: true,
            }
		},
        modal: true,
        padding: 0,
		closeBtn: true,
		closeBtnWrapper: '.fancybox-skin .fancybox-title',
		onCancel: function () {
			if (this.inner) {
				this.inner.trigger('fancybox:on-cancel');
			}
		},
		beforeLoad: function() {
            var $elem = this.element;

			if($elem.data("before-callback") !== undefined){
				window[$elem.data("before-callback")](this);
			}

            if ($elem.data('title')) {
                this.title = $elem.data('title');
			}

            if ($elem.data('h')) {
                this.autoHeight = false;
                this.height = $elem.data('h');
            }

            if ($elem.data('w')) {
                this.width = $elem.data('w');
                this.autoWidth = false;
            }

			this.keys = {
				close: [27]
			};

			this.ajax.data_table_var = window[$($elem).data('table')];

			if (this.ajax.data_table_var === undefined) {
                this.ajax.data_table_var = window[$($elem).parents('table').first().attr('id')];
			}

			this.ajax.form_submit_callback = window[$($elem).data('submit-callback')];

			if (this.inner) {
				this.inner.trigger('fancybox:before-load');
			}
        },
		afterLoad : function () {
			if (this.inner) {
				this.inner.trigger('fancybox:after-load');
			}
		},
		beforeShow : function () {
			if (this.inner) {
				this.inner.trigger('fancybox:before-show');
			}
		},
		afterShow : function () {
			if (this.inner) {
				this.inner.trigger('fancybox:after-show');
			}
		},
		beforeClose : function () {
			if (this.inner) {
				this.inner.trigger('fancybox:before-close');
			}
		},
		afterClose : function () {
			if (this.inner) {
				this.inner.trigger('fancybox:after-close');
			}
		},
		onUpdate: function () {
			if (this.inner) {
				this.inner.trigger('fancybox:on-update');
			}
		},
        ajax: {
            complete: function(jqXHR, textStatus) {
				var data_table_var = this.data_table_var;
				var form_submit_callback = this.form_submit_callback;

				$(".validateModal").validationEngine('attach', {
					promptPosition : "topLeft:0",
					autoPositionUpdate : true,
					scroll: false,
                    focusFirstField: false,
					onValidationComplete: function(form, status) {
						if (status) {
                            var callback = $(form).data("callback") || null;
							if (callback) {
                                callFunction(callback, form, data_table_var, form_submit_callback);
                            } else {
								modalFormCallBack(form, data_table_var, form_submit_callback);
                            }
						} else {
							systemMessages(translate_js({ plug: 'general_i18n', text: 'validate_error_message' }), 'error');
						}
					}
				});
            }
        }
    }, ".fancyboxValidateModalDT");

	$(".fancybox-ttl-inside").fancybox({
		loop : false,
		helpers : {
			title: {
				type: 'inside',
				position: 'top'
			}
		},
		modal: true,
		padding: 0,
		closeBtn : true,
		closeBtnWrapper: '.fancybox-skin .fancybox-title',
		beforeLoad : function() {
			var $elem = this.element;
			if($elem.data('title'))
				this.title = $elem.data('title');

			if($elem.data('h')){
				this.autoHeight = false;
				this.height = $elem.data('h');
			}

			if($elem.data('w')){
				this.width = $elem.data('w')
				this.autoWidth = false;
			}
		}
	},".fancybox-ttl-inside");

	$(".fancyboxValidateModalMessages").fancybox({
		loop : false,
		helpers : {
			title: {
				type: 'inside',
				position: 'top'
			},
			overlay: {
				locked: true
			}
		},
		modal: true,
		padding: 0,
		closeBtn : true,
		closeBtnWrapper: '.fancybox-skin .fancybox-title',
		beforeClose : function () {
			clearInterval(messagesPopupTree.getNewMessagesInterval);
		},
		beforeShow : function() {
			var $elem = this.element;

			if($elem.data('dashboard-class') != undefined){
				$('.fancybox-inner').addClass($elem.data('dashboard-class'));
			}
		},
		beforeLoad : function() {
			var $elem = this.element;
			if($elem.data("before-callback") != undefined){
				if(window[$elem.data("before-callback")](this) == false)
					return false;
			}

			if($elem.data('title') != undefined){
				this.title = htmlEscape($elem.data('title'))+"&emsp;";
			}

			if($elem.data('title-type') != undefined){
				this.title = $elem.data('title')+"&emsp;";
			}

			if($elem.data('h')){
				this.autoHeight = false;
				this.height = $elem.data('h');
			}

			if($elem.data('w')){
				this.width = $elem.data('w')
				this.autoWidth = false;
			}

			if($elem.data('mw')){
				this.maxWidth = $elem.data('mw')
			}

			if($elem.data('p') != undefined){
				this.padding = [$elem.data('p'),$elem.data('p'),$elem.data('p'),$elem.data('p')]

				if( $elem.data('p') == 0){
					this.wrapCSS = 'fancybox-title--close';
				}
			}

			this.ajax.caller_btn = $elem;
		}
	}, ".fancyboxValidateModalMessages");

	$('body').on('click', ".call-systmess", function(e){
		e.preventDefault();
		var $thisBtn = $(this);
		var mess = $thisBtn.data('message');
		var type = $thisBtn.data('type');
		systemMessages(mess, type);

		return false;
	});

	$('body').on('click', ".not-call-function", function(e){
		e.preventDefault();
	});

	$('body').on('change', ".fancybox-inner form :input", function() {
		$('.fancybox-title').find('a[data-callback="closeFancyBox"]').removeClass('call-function').addClass('confirm-dialog');
	});

	$('body').on('click', '.shadow-header-top', function(){
		headerNavRef();
	});

	$('body').on('click', '.popup-header-nav-top .btn-close', function(){
		headerNavRef();
	});

	$('body').on('keyup', '.call-count-characters', function(){
		var $block = $(this);
		var $countBlock = $('#' + $block.data('count'));
		var values = $block.val();

		$countBlock.text(values.length);
	});

	$('.rating-bootstrap').rating();

	$('.rating-bootstrap').each(function () {
		var $this = $(this);
		ratingBootstrap($this);
	});

	$('body').on('click', ".info-dialog", function(e){
		var $thisBtn = $(this);
		e.preventDefault();

		var storedMessage = $thisBtn.data('message') || null;
		var storedContent = $thisBtn.data('content') || null;
		var message = '';

		if(null !== storedMessage){
			message = storedMessage;
		} else if(null !== storedContent){
			message = $(storedContent).html();
		}

		open_info_dialog($thisBtn.data('title'), message, false);
	});

	var clipboard = new Clipboard('.link-clipboard');
	clipboard.on('success', function(e) {
		systemMessages('The text has been copied to clipboard.', 'message-success');
		e.clearSelection();
	}).on('error', function(e) {
		systemMessages('The text cannot be copied to clipboard.', 'message-error');
	});

	$('body').on('mouseover mouseenter', '*[title]', function(){
		if ($(this).attr('title') != '') {
			if(!$(this).hasClass('tooltipstered')){
				$(this).tooltipster();
			}
			$(this).tooltipster('show');
		}
    });

	updateActivity();
	setInterval(function(){
		updateActivity();
	}, 600000);
});

function callModalMessageAttach($this){
	btnOpenBootstrapDialogMessageAttach = $this;
	bootstrapDialogMessageAttach = BootstrapDialog.show({
		cssClass: 'info-bootstrap-dialog inputs-40',
		title: 'Upload the document',
		type: 'type-light',
		size: 'size-wide',
		closable: true,
		closeByBackdrop: false,
		closeByKeyboard: false,
		draggable: false,
		animate: true,
		nl2br: false,
		onshow: function(dialog) {
			var $modal_dialog = dialog.getModalDialog();
			$modal_dialog.addClass('modal-dialog-centered');
			dialog.getModalBody().addClass('mnh-100');
			showLoader($modal_dialog.find('.modal-body'), 'Loading...');

			$.get(__group_site_url + '/chats/popupForms/attachFiles').done(function( html_resp ) {
				setTimeout(function(){

					dialog.getModalBody().html(html_resp);
					dialog.getModalFooter().append('<button class="btn btn-primary mnw-130 call-function" data-callback="attachFilesToMessage" type="button" disabled>Attach Files to Message</button>').css({'display': 'flex'});

				}, 200);
			});

		},
		onshown: function(dialogRef){
			// hideLoader(cropperImg.popupCropperShow);
		},
		onhidden: function(dialogRef){
			// $modalMessageAttachWr.append($modalMessageAttachInner);
		}
	});
}

function ratingBootstrap($this){
	var rating = $this.val();
	var text = '';

	if(rating.length){
//		console.log(rating);
		text = ratingBootstrapStatus(rating);
		$this.next('.rating-bootstrap-status').text(text);
	}
}

function ratingBootstrapStatus(rating){
	var text = '';

	switch(true){
		case rating < 2:
			text = 'Terrible';
		break;
		case rating < 3:
			text = 'Poor';
		break;
		case rating < 4:
			text = 'Ok';
		break;
		case rating < 5:
			text = 'Good';
		break;
		case rating < 6:
			text = 'Excellent';
		break;
	}

	return text;
}

function fancyboxPostParams($obj){
	$obj.ajax.type = 'POST';
	var $elem = $obj.element;

	if($elem.data('params')){
		$obj.ajax.data = $elem.data('params');
	}
}

function closeFancyBox(){
	$('.wr-modal-b form').validationEngine('detach');
	$('.wr-modal-flex form').validationEngine('detach');
	$.fancybox.close();
}

$(window).scroll(function(){

	//show button to top
	if ($(this).scrollTop() > 500)
		$('#btn-scrollup').fadeIn();
	else
		$('#btn-scrollup').fadeOut();

});

function showMessage(message, type){
    //alert(message);
    html = "<p class=" + type + ">" + message + "</p>";
	$("html, body").animate({ scrollTop: 0 }, 600);
    $('#ajax_res').html(html).fadeIn('fast').delay(3000).fadeOut('slow');
}

function scrollToElement(element){
    $("html, body").animate({
        scrollTop: $(element).offset().top
    }, 2000);
}

function scrollToElementModal(element, block){
    $(block).animate({
        scrollTop: $(element).position().top
    }, 1000);
}

function validate_field(block, element) {
        var nr = 0;
        nr = $(block).next().find(element).length;
        if (nr == 0) {
            $(block).validationEngine('showPrompt', '*This field is required', 'prompt', 'topLeft', true);
            return false;
        } else {
            $(block).validationEngine('hide');
            return nr;
        }
}

function dump(obj) {
    var out = "";
    if(obj && (typeof(obj) == "object" || typeof(obj) == "Array")){
        for (var i in obj) {
            out += i + ": " + obj[i] + "\n";
        }
    } else {
        out = obj;
    }
    alert(out);
}

//show a bloc
//if one param => will show child block
//if both param => will show parent of the child
function showBlock(child, block){
	if(block == undefined)
		$(child).fadeIn('slow');
	else
		$(child).closest(block).fadeIn('slow');
}

//show a bloc
//if one param => will close this block
//if both param => will close parent of the caller
function hideBlock(caller, block){
	if(block == undefined)
		$(caller).fadeOut('slow');
	else
		$(caller).closest(block).fadeOut('slow');
}

var idStartItemNew = '';

function checkAdminNewItems(url){

	if(!$('#btn-show-new-items').length)
		$('.wr-filter-admin-panel .btn-display').append('<div id="btn-show-new-items" class="dt-filter-reset-buttons" style="display: none;"></div>');

	$.ajax({
		type: "POST",
		url: url,
		data: {lastId: idStartItemNew},
		dataType: 'json',
		success: function(data) {

			if (data.mess_type == 'success'){
				idStartItemNew = data.lastId;
				if(data.nr_new > 0){
					$('#btn-show-new-items').text(data.nr_new).show('slow');
				}
			}
		}
	});
}

function startCheckAdminNewItems(url){
	setInterval(function() { checkAdminNewItems(url); },60000);
}

jQuery.fn.highlight = function (str, className) {
	var regex = new RegExp(str, "gi");
	return this.each(function () {
		$(this).contents().filter(function() {
			return this.nodeType == 3 && regex.test(this.nodeValue);
		}).replaceWith(function() {
			return (this.nodeValue || "").replace(regex, function(match) {
				return "<span class=\"" + className + "\">" + match + "</span>";
			});
		});
	});
};

$.fn.setValHookType = function (type) {
	this.each(function () {
		this.type = type;
	});

	return this;
};

function headerNavRef(){
	var nav = $('.header-nav-top');
	$('.shadow-header-top').hide();
	nav.find('.main-login-form-b').hide();
	nav.children('a').removeClass('active');
	$('.popup-header-nav-top').children('div').hide().end()
			.css({'width':'97%'}).hide();

//	$('body').removeClass('noscroll');
	$('html').removeClass('fancybox-margin fancybox-lock');
	$('.main-socials-list').removeClass('fancybox-margin');
}

function toOrderNumber(str){
	str = str.replace(/^#/, '');
	if(str == parseInt(str, 10)){
		str = str.substr(-11);
		var pad = "#00000000000";
		return pad.substring(0, pad.length - str.length) + str;
	}else
		return false;
}

var initSelectCity = function($selectCity){
	$selectCity.select2({
		ajax: {
			type: 'POST',
			url: "location/ajax_get_cities",
			dataType: 'json',
			delay: 250,
			data: function (params) {
			  return {
				search: params.term, // search term
				page: params.page,
				state: selectState
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
		theme: "default ep-select2-h30",
		width: '100%',
		placeholder: translate_js({plug:'general_i18n', text:'form_placeholder_select2_state_first'}),
		minimumInputLength: 2,
		escapeMarkup: function(markup) { return markup; },
		templateResult: formatCity,
		templateSelection: formatCitySelection,
	}).data('select2').on("results:message", function (e) {
		this.dropdown._positionDropdown();
	});
}

function formatCity (repo) {
	if (repo.loading) return repo.text;

	var markup = repo.name;

	return markup;
}

function formatCitySelection (repo) {
	return repo.name || repo.text;
}

function updateActivity(){
	$.get('authenticate/checkSession');
}

function selectCountry(selectCountry, statesSelectElement){
    var country = $(selectCountry).val();
    $.ajax({
        type: "POST",
		dataType: 'JSON',
        url: "location/ajax_get_states",
        data: {country: country},
        success: function(resp) {
            $(statesSelectElement).html(resp.states);
        }
    });
}

function formatCcode (cCode) {
	if (cCode.loading){
		return cCode.text;
	}

	var markup = $(
		'<span>\
			<img src="'+ cCode.element.dataset.countryFlag + '" alt="'+cCode.element.dataset.countryName+'"/> \
			' + cCode.element.value + ' ' + cCode.element.dataset.countryName + '\
		</span>'
	);

	return markup;
}

function intval(num){
	if (typeof num == 'number' || typeof num == 'string'){
		num = num.toString();
		var dotLocation = num.indexOf('.');
		if (dotLocation > 0){
			num = num.substr(0, dotLocation);
		}

		if (isNaN(Number(num))){
			num = parseInt(num);
		}

		if (isNaN(num)){
			return 0;
		}

		return Number(num);
	} else if (typeof num == 'object' && num.length != null && num.length > 0){
		return 1;
	} else if (typeof num == 'boolean' && num === true){
		return 1;
	}

	return 0;
}

function floatval(mixed_var) {
	return (parseFloat(mixed_var) || 0);
}

function uniqid (prefix, more_entropy) {
	// %     	note 1: Uses an internal counter (in php_js global) to avoid collision
	// *     example 1: uniqid();
	// *     returns 1: 'a30285b160c14'
	// *     example 2: uniqid('foo');
	// *     returns 2: 'fooa30285b1cd361'
	// *     example 3: uniqid('bar', true);
	// *     returns 3: 'bara20285b23dfd1.31879087'
	if (typeof prefix === 'undefined') {
		prefix = "";
	}

	var retId;
	var formatSeed = function (seed, reqWidth) {
		seed = parseInt(seed, 10).toString(16); // to hex str
		if (reqWidth < seed.length) { // so long we split
		return seed.slice(seed.length - reqWidth);
		}
		if (reqWidth > seed.length) { // so short we pad
		return Array(1 + (reqWidth - seed.length)).join('0') + seed;
		}
		return seed;
	};

	// BEGIN REDUNDANT
	if (!this.php_js) {
		this.php_js = {};
	}
	// END REDUNDANT
	if (!this.php_js.uniqidSeed) { // init seed with big random int
		this.php_js.uniqidSeed = Math.floor(Math.random() * 0x75bcd15);
	}
	this.php_js.uniqidSeed++;

	retId = prefix; // start with prefix, add current milliseconds hex string
	retId += formatSeed(parseInt(new Date().getTime() / 1000, 10), 8);
	retId += formatSeed(this.php_js.uniqidSeed, 5); // add seed hex string
	if (more_entropy) {
		// for more entropy we add a float lower to 10
		retId += (Math.random() * 10).toFixed(8).toString();
	}

	return retId;
}

if (!Object.keys) {
    Object.keys = function (obj) {
        var arr = [],
            key;
        for (key in obj) {
            if (obj.hasOwnProperty(key)) {
                arr.push(key);
            }
        }
        return arr;
    };
}

var sendBoundRequest = function (caller) {
    var button = $(caller);
    var datagridSelector = buton.data('datagrid') || null;
    var forceDraw = Boolean(~~button.data('draw') || null);
    var url = button.data('url') || null;
    var onRequestSuccess = function(resposne) {
        systemMessages(resposne.message, resposne.mess_type);
        if(resposne.mess_type === 'success') {
            if(null !== datagridSelector) {
                var table = $(datagridSelector);
                if(table.length && $.fn.dataTable.isDataTable(table)) {
                    table.fnDraw(!forceDraw);
                }
            }
        }
    }

    if(null !== url) {
        $.post(url, null, null, 'json').done(onRequestSuccess).fail(onRequestError);
    }
}

function open_info_dialog(title, content, is_ajax, buttons){
	is_ajax = is_ajax || 0;

	buttons = buttons || [];

	BootstrapDialog.show({
		cssClass: 'info-bootstrap-dialog',
		title: title,
		message: $('<div class="ep-tinymce-text"></div>'),
		onshow: function(dialog) {
			var $modal_dialog = dialog.getModalDialog();
			$modal_dialog.addClass('modal-dialog-centered');

			if(is_ajax){
				showLoader($modal_dialog.find('.modal-content'), 'Loading...');

				$.get(content).done(function( html_resp ) {
					setTimeout(function(){
						dialog.getMessage().append(html_resp);
						hideLoader($modal_dialog.find('.modal-content'));
					}, 200);
				});
			} else{
				dialog.getMessage().append(content);
			}
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
}

var calcHeightDashboard = function($blocksArray, widthChanged, heightChanged, minusMain){
	if($blocksArray == undefined )
		return false;

	if(widthChanged == undefined )
		widthChanged = true;

	if(heightChanged == undefined )
		heightChanged = true;

	if(minusMain == undefined )
		var minusMain = (64 + 131 + 44);

	// Main content block
	var browserHeight = parseInt($('body').height());
	var finalHeightMain = 532;

	if(browserHeight > (finalHeightMain+minusMain)){
		finalHeightMain = (browserHeight - minusMain);
		$mainContent.css({'height':finalHeightMain+'px'});
	}else{
		$mainContent.css({'height':'532px'});
	}

//	console.log('---------');
	for(var key in $blocksArray) {
		if($mainContent.height() > 532 && heightChanged){
			calcHeightBlockSimple($blocksArray[key], widthChanged, heightChanged, finalHeightMain);
		}else if( $blocksArray[key].width === true && widthChanged){
			calcHeightBlockSimple($blocksArray[key], widthChanged, heightChanged, finalHeightMain);
		}
	};
}

function calcHeightBlockSimple(objectBlock, widthChanged, heightChanged, finalHeightMain){
	var nameBlock = objectBlock.name;
	var apiBlock = window[nameBlock+'Api'];

	//if apiBlock is undefined
	if(apiBlock == undefined)
		return false;

	//if width and height not was changed
	if(!widthChanged && !heightChanged)
		return false;

//	console.log(nameBlock);
	var $block = window['$'+nameBlock];
	var blockTimeout = window[nameBlock+'Timeout'];
	var minusBlock = objectBlock.minus;

	showLoader($block,'');

	//if need change width
	if(widthChanged){
		$block.css({'width':'auto'})
			.find('.jspContainer').css({'width':'100%'}).end()
			.find('.jspPane').css({'width':'100%'});
	}

	//if need change height
	if(heightChanged){
		//remove height
		$block.css({'height':'auto'}).find('.jspContainer').css({'height':'100%'});

		//if timeout of changed height was finished
		if (!blockTimeout) {
			//calc final height for block
			var finalHeightBlock = (finalHeightMain - minusBlock);
			//set final height for block
			$block.css({'height':finalHeightBlock+'px'})
				.find('.jspContainer').css({'height':finalHeightBlock+'px'});
		}
	}

	//if timeout of changed height was finished
	if(!blockTimeout) {
		//init timeout for reinitialise height
		blockTimeout = setTimeout(
			function(){
				apiBlock.reinitialise();
				hideLoader($block,'');
				blockTimeout = null;
			},
		50);
	}
}
