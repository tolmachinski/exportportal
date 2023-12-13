var btnlaodSavedList = function($this){
    var type = $this.data('type');
    var txt = $this.find('.txt').text();
    var count = $this.find('.count').text();
    var $dropdown = $('#js-epuser-saved-menu-dropdown');

    $this.addClass('active').siblings().removeClass('active');
    if($this.closest('.epuser-subline-nav2__item').length){
        $dropdown.find('.dropdown-item[data-type="' + type + '"]').addClass('active').siblings().removeClass('active');
    }else{
        var $tabs = $('#js-epuser-saved-menu').find('.epuser-subline-nav2__item');
        $tabs.find('.link[data-type="' + type + '"]').addClass('active').siblings().removeClass('active');
    }

    var $dropdownTop = $dropdown.find('[data-toggle="dropdown"]');
    $dropdownTop.find('.txt').text(txt);
    $dropdownTop.find('.count').text(count);

    if($dropdown.find('.dropdown-menu').is(':visible')){
        $dropdown.find('a[data-toggle="dropdown"]').dropdown('toggle');
    }

    laodSavedList(type);
}

// load saved items
var laodSavedList = function(type, page){
    if (page === undefined)
        page = 1;

    $.ajax({
        url: __current_sub_domain_url + type + '/ajax_get_saved',
        type: 'POST',
        data: {page: page},
        dataType: 'JSON',
        beforeSend: function () {
            showLoader('#epuser-saved2');
        },
        success: function (resp) {
            hideLoader('#epuser-saved2');
            $.fancybox.update();

            if (resp.mess_type == 'success') {
                if (resp.counter != undefined)
                    $('.epuser-subline-nav2 a[data-type=' + type + ']').find('.count').text(resp.counter);

                $("#epuser-saved2").find('.js-epuser-saved-page').remove();
                $("#epuser-saved2").find('.js-epuser-saved-content').remove();
                $(resp.message).insertAfter($("#js-epuser-saved-menu"));
            } else {
                systemMessages(resp.message, resp.mess_type);
            }
        }
    })
}
//end load saved items

//remove contact item
var remove_header_contact = function (opener) {
    var $this = $(opener);
    $.ajax({
        url: __current_sub_domain_url + 'contact/ajax_contact_operations/remove/' + ($this.data('id')),
        type: 'POST',
        dataType: 'JSON',
        success: function (resp) {
            systemMessages(resp.message, resp.mess_type);

            if (resp.mess_type == 'success') {
                $this.closest('.ppersonal-followers__item').fadeOut('normal', function(){
                    $(this).remove();
                });

                laodSavedList('contact', 1);
            }
        }
    });
}
//end remove contact item

//remove saved sellers
var remove_header_company = function (opener) {
    var $this = $(opener);
    $.ajax({
        url: __current_sub_domain_url + 'directory/ajax_company_operations/remove_company_saved',
        type: 'POST',
        dataType: 'JSON',
        data: {company: $this.data('company')},
        success: function (resp) {
            systemMessages(resp.message, resp.mess_type);

            if (resp.mess_type == 'success') {
                $this.closest('.companies-wr').fadeOut('normal', function(){
                    $(this).remove();
                });

                laodSavedList('directory', 1);
            }
        }
    });
}
//end remove saved sellers

//remove saved shippers
var remove_header_shipper = function (opener) {
    var $this = $(opener);
    $.ajax({
        url: __current_sub_domain_url + 'shipper/ajax_shipper_operation/remove_shipper_saved',
        type: 'POST',
        dataType: 'JSON',
        data: {company: $this.data('shipper')},
        success: function (resp) {
            systemMessages(resp.message, resp.mess_type);

            if (resp.mess_type == 'success') {
                $this.closest('.companies-wr').fadeOut('normal', function(){
                    $(this).remove();
                });

                laodSavedList('shippers', 1);
            }
        }
    });
}
//end remove saved shippers

//remove saved b2b partners
var remove_header_b2b_partners = function (opener) {
    var $this = $(opener);
    $.ajax({
        url: __current_sub_domain_url + 'b2b/ajax_b2b_operation/delete_partner',
        type: 'POST',
        dataType: 'JSON',
        data: {partner: $this.data('partner'), company: $this.data('company')},
        success: function (resp) {
            systemMessages(resp.message, resp.mess_type);
            if (resp.mess_type == 'success') {
                $this.closest('.companies-wr').fadeOut('normal', function(){
                    $(this).remove();
                });

                laodSavedList('b2b', 1);
            }
        }
    });
}
//end remove b2b partners

//remove saved product
var remove_header_product = function (opener) {
    var $this = $(opener);
    $.ajax({
        url:  __current_sub_domain_url + 'items/ajax_saveproduct_operations/remove_product_saved',
        type: 'POST',
        dataType: 'JSON',
        data: {product: $this.data('product')},
        success: function (resp) {
            systemMessages(resp.message, resp.mess_type);
            if (resp.mess_type == 'success') {
                $this.closest('.products-mini__wr').fadeOut('normal', function(){
                    $(this).remove();
                });

                var item = $('.js-products-favorites-btn[data-item="' + $this.data('product') + '"]');

                item.data("jsAction", "favorites:save-product")
                    .attr("title", translate_js({ plug: "general_i18n", text: "item_card_remove_from_favorites_tag_title" }))
                    .find(".ep-icon")
                    .toggleClass("ep-icon_favorite ep-icon_favorite-empty")

                var itemText = item.find("span");
                if (itemText.length) {
                    itemText.text(translate_js({ plug: "general_i18n", text: "item_card_label_favorite" }));
                }

                laodSavedList('items', 1);
            }
        }
    });
}
//end remove saved product

//remove saved search
var remove_header_search = function (opener) {
    var $this = $(opener);
    $.ajax({
        url: __current_sub_domain_url + 'save_search/ajax_savesearch_operations/remove_search_saved',
        type: 'POST',
        dataType: 'JSON',
        data: {search: $this.data('search')},
        success: function (resp) {

            systemMessages(resp.message, resp.mess_type);
            if (resp.mess_type == 'success') {
                $this.closest('.saved-search__item').fadeOut('normal', function(){
                    $(this).remove();
                });

                laodSavedList('save_search', 1);
            }
        }
    });
}
//end remove saved search

$(function(){
    var $savedTab = $("#js-epuser-saved-menu").find('.epuser-subline-nav2__item .link:not(.disabled)').first();

    if($savedTab.length){
        $savedTab.trigger('click');
    }

});
