<script>
    var currentCategory;
    var showProprieties = function(category)
    {
        var selectedCategory = category || currentCategory;
        var elements = $('div.table-header__column.' + selectedCategory);

        $('div.table-default__column--fixed > div.table-default__row').each(function () {
            var element = $(this);
            element.hide();
        });

        $('div.table-default__column-block').each(function () {
            var element = $(this);
            element.find('div.table-default__row').each(function () {
                $(this).hide();
            });
        });

        elements.each(function () {
            var element = $(this),
                item = element.find('div.expand-column__delete').data('id');

            $('div.table-default__column--fixed').find('.' + item).each(function () {
                var current = $(this),
                    elementIndex = current.index();
                current.show();
                $('div.table-default__column-block').find('.table-default__column.' + selectedCategory).each(function () {
                    var property = $(this).find('div.table-default__row');
                    $(property[elementIndex]).show();
                });
            });
        });

        alignCellHeight(
            $(".table-default__row"),
            $(".table-default__column--fixed .table-default__row"),
            $('.table-default__column'),
            '.table-default__row'
        );
    }

    var calculateItems = function ()
    {
        $('div[category-aim]').each(function(){
            var element = $(this),
                category = element.attr('category-aim'),
                numberItems = $('.table-header__column-block').find('.'+category).length;

            element.find('.dropdown-drop__count').html(numberItems);
        });
    }

    var setMainElementMenu = function () {
        var menuFirstElement = $('div.table-header__dropdown').find('ul.dropdown-drop__list').find('li').first();

        if (menuFirstElement.find('.dropdown-drop__title-block').first().length) {
            menuFirstElement
                .find('.dropdown-drop__title-block')
                .first()
                .trigger('click');
        }

        if ($('div.table-header__dropdown').find('ul.dropdown-drop__list').find('li').length <= 1) {
            $('div.table-default__header').remove();
            $('div.table-default').remove();
            $('div.info-alert-b').removeClass('d-none');
        }
    }

    var removeItemElement = function (listItems, id) {
        if (listItems.includes(id)) {
            var index = listItems.indexOf(id);
            if (index > -1) {
                listItems.splice(index, 1);
            }
        }

        return listItems;
    }

    var removeMenuCategory = function (category) {
        var menuElement = $('.dropdown-drop__title-block[category-aim="' + category + '"]'),
            itemsCount = $('div.table-header__column-block').find('div.' + category).length;

        if (itemsCount == 0) {
            menuElement.parents().each(function () {
                var element = $(this),
                    currentCategory = element.find('li.dropdown-drop__list-item').find('.dropdown-drop__title-block').attr('category-aim');

                if ($('.table-default__column-block').find('.table-default__column.' + currentCategory).length == 0) {
                    element.remove();
                }

                if (element.hasClass('dropdown-drop')) {
                    return false;
                }
            });

            setMainElementMenu();
        }
    }

    $(document).ready(function() {
        $('body').on('click', function(e){
            var $choice = $('.table-header__dropdown');
            var $choiceDrop = $('.dropdown-drop');

            if ($(e.target)[0] === $choice[0] || $(e.target).parents('.table-header__dropdown')[0] === $choice[0]) {
                return;
            }

            if (
                (
                    $(e.target)[0] === $choiceDrop[0]
                    || $(e.target).parents('.dropdown-drop')[0]
                    !== $choiceDrop[0]
                )
                &&$choiceDrop.is(':visible')
            ) {
                $choiceDrop.slideUp();
            }
        });

        currentCategory = $('div.table-header__dropdown>a.btn-block').attr('id');
        calculateItems();
        showProprieties();

        $('div.dropdown-drop__title-block').on('click', function () {
            var element = $(this),
                categoryName = element.find('.dropdown-drop__category').html(),
                categoryId = element.attr('category-aim');
            showProprieties(categoryId);
            $('.table-header__dropdown > a.drop-next > span').html(categoryName);
            $('.table-header__dropdown > a.drop-next').attr('title', categoryName);
            $('.table-header__dropdown > a.drop-next').attr('id', categoryId);
        });

        var last_scroll_pos = 0;

        $( '.table-header__column-block' ).scroll(function() {

            var elem = $(this);

            var header_scroll = elem.scrollLeft();
            $( '.table-default__column-block' ).scrollLeft(header_scroll);

            $('.table-header__column-hidden').html('');

            var block_col = elem.find('.expand-column');

            block_col.each(function() {
                $(this).show();
            });

            var block_width = elem.outerWidth();

            var cols_number = elem.find('.table-header__column:visible').length;

            var cols_total_width = 0;

            elem.find('.table-header__column:visible').each(function () {
                cols_total_width = cols_total_width + $(this).outerWidth();
            });

            if(cols_total_width > block_width) {
                if(elem.scrollLeft() > last_scroll_pos) {
                    elem.parent().find('.table-header__overlay-left').fadeOut();
                    elem.parent().find('.table-header__overlay-right').fadeIn();
                } else {
                    elem.parent().find('.table-header__overlay-right').fadeOut();
                    elem.parent().find('.table-header__overlay-left').fadeIn();
                }
            }

            last_scroll_pos = elem.scrollLeft();
        });

        if (typeof window.ontouchstart == 'undefined') {

            $('.table-default__row').hover(function() {
                var row_index = $(this).index();

                $('.table-default__row').each( function() {
                    if($(this).index() === row_index) {
                        $(this).addClass('hovered');
                    } else {
                        $(this).removeClass('hovered');
                    }
                });
            });

            $('.table-default__row').on('mouseleave', function() {
                var row_index = $(this).index();

                $('.table-default__row').removeClass('hovered');
            });

        }

        $('.table-default__row').on('click', function() {
            var row_index = $(this).index();

            $('.table-default__row').each( function() {
                if($(this).index() === row_index && !$(this).hasClass('active')) {
                    $(this).addClass('active');
                } else if ($(this).index() === row_index && $(this).hasClass('active')) {
                    $(this).removeClass('active');
                }
            });
        });

        $( '.table-header__column-block' ).on('mouseup touchend', function() {
            $('.table-header__overlay-left').fadeOut();
            $('.table-header__overlay-right').fadeOut();
        });

        $( '.expand-column' ).on('mouseenter touchstart', function() {

            var elem = $(this);
            var cloned = elem.clone().addClass('expand-column--hover');

            var hidden_col = elem.closest('.table-header__column-container').find('.table-header__column-hidden');
            var table_column = elem.closest('.table-header__column').position().left;

            elem.closest('.table-header__column-block').find('.expand-column').show();
            $('.expand-column').css('pointer-events', 'auto');
            elem.css('pointer-events', 'none');

            var total_cols = $('.table-header__column-block').find('.table-header__column').length;
            var parent_index = elem.parent().index() + 1;

            if(total_cols === parent_index) {
                hidden_col.css('left', table_column - 45);
            } else {
                hidden_col.css('left', table_column);
            }

            hidden_col.html(cloned);

            var new_col = hidden_col.find('.expand-column');

            $(new_col).find('.expand-column__delete').on('click', function() {
                var btn = $(this),
                    itemId = btn.attr('data-id'),
                    category = btn.attr('data-category'),
                    cookie_compare_name = 'ep_compare',
                    id = 0;
                    <?php if(logged_in()){?>
                        cookie_compare_name = 'user_<?php echo id_session();?>_compare';
                    <?php }?>

                var listItems = JSON.parse(getCookie(cookie_compare_name));

                btn.closest('div.expand-column').remove();
                $('div.expand-column__delete[data-id="' + itemId + '"]').closest('div.'+category).remove();
                $('div#'+itemId).remove();
                removeMenuCategory(category);
                calculateItems();

                id = itemId.replace('item-', '');
                listItems = removeItemElement(listItems, id);

                if(listItems.length > 0){
                    setCookie(cookie_compare_name, listItems);
                }else{
                    removeCookie(cookie_compare_name);
                    _actualize_compare();
                }
            });

            $(new_col).on('mouseleave', function() {
                elem.css('pointer-events', 'auto');
                $(this).parent().html('');
            });

        });

        $( '.drop-next' ).on('click', function() {
            $(this).next().toggle();
        });

        $( 'div.dropdown-drop__close' ).on('click', function() {
            var menuElement = $(this).closest('li.dropdown-drop__list-item')
                category = $(this).closest('.dropdown-drop__title-block').attr('category-aim'),
                cookie_compare_name = 'ep_compare';
                <?php if(logged_in()){?>
                    cookie_compare_name = 'user_<?php echo id_session();?>_compare';
                <?php }?>

            var listItems = JSON.parse(getCookie(cookie_compare_name));

            menuElement.find('div[category-aim]').each(function(){
                var currentElement = $(this),
                    category = currentElement.attr('category-aim'),
                    items = $('.table-default__column-block').find('.'+category);

                items.each(function () {
                    var element = $(this),
                        itemId = (element.attr('id')).replace('item-', '');

                    listItems = removeItemElement(listItems, itemId);
                });

                if ($('.'+category).length > 0) {
                    $('.'+category).remove();
                }
            });
            removeMenuCategory(category);

            if(listItems.length > 0){
                setCookie(cookie_compare_name, listItems);
            }else{
                removeCookie(cookie_compare_name);
                _actualize_compare();
            }
        });

        $( '.dropdown-drop__title-block' ).on('click', function(e) {
            e.preventDefault();
            $('.dropdown-drop__list .active').removeClass('active');
            $(this).addClass('active');
            $('.table-header__column').hide();
            $('.table-default__column').hide();
            var category_aim = String($(this).attr('category-aim'));

            $('.' + category_aim).each(function() {
                $(this).find('.expand-column__delete').attr('data-category', category_aim);
                $(this).fadeIn();
            });

            alignCellHeight(
                $(".table-default__row"),
                $(".table-default__column--fixed .table-default__row"),
                $('.table-default__column'),
                '.table-default__row'
            );
        });

        $( '.dropdown-drop__trigger' ).on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            var icon_plus = $(this).find('.ep-icon_plus-stroke');
            var icon_minus = $(this).find('.ep-icon_minus-stroke');

            if(icon_plus) {
                icon_plus.addClass('ep-icon_minus-stroke');
                icon_plus.removeClass('ep-icon_plus-stroke');
            }
            if (icon_minus) {
                icon_minus.removeClass('ep-icon_minus-stroke');
                icon_minus.addClass('ep-icon_plus-stroke');
            }

            $(this).closest('.dropdown-drop__title-block').next('.dropdown-drop__list').slideToggle();
        });

        if(isMobile() ) {
            $('.table-header__column-hidden').hide();
        }
    });
</script>