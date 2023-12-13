<?php tmvc::instance()->controller->view->display('admin/file_upload_scripts'); ?>

<script type="text/javascript">
    var delete_item_category_article_i18n = function(obj){
        var $this = $(obj);
        var item_category_article = $this.data('item_category_article');
        var lang_item_category_article = $this.data('lang');

        $.ajax({
        type: 'POST',
            url: '<?php echo __SITE_URL?>categories_articles/ajax_categories_articles_operation/delete_item_category_article_i18n',
            data: {category_article: item_category_article, lang:lang_item_category_article},
            beforeSend: function(){ },
            dataType: 'json',
            success: function(resp){
                systemMessages( resp.message, 'message-' + resp.mess_type );
                if(resp.mess_type == 'success'){
                    callbackManageArticles(resp);
                }
            }
        });
    }

    var articlesFilters, cur_lvl_cats, dtCatsArticles;
	var remove_cat_art = function (obj) {
		var $this = $(obj);
		var id = $this.data('cat-art');

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL ?>categories_articles/ajax_categories_articles_operation/remove_category_article',
			data: {id_cat_art: id},
			beforeSend: function () {
				showLoader(dtCatsArticles);
			},
			dataType: 'json',
			success: function (data) {
				hideLoader(dtCatsArticles);
				systemMessages(data.message, 'message-' + data.mess_type);
				if (data.mess_type == 'success') {
					dtCatsArticles.fnDraw(false);
				}
			}
		});
	}

    var change_visible_cats_arts = function (obj) {
		var $this = $(obj);
		var id = $this.data('cat-art');

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL ?>categories_articles/ajax_categories_articles_operation/change_visible_category_article',
			data: {id_cat_art: id},
			beforeSend: function () {
				showLoader(dtCatsArticles);
			},
			dataType: 'json',
			success: function (data) {
				hideLoader(dtCatsArticles);
				systemMessages(data.message, 'message-' + data.mess_type);
				if (data.mess_type == 'success') {
					dtCatsArticles.fnDraw(false);
				}
			}
		});
	}

    function callbackManageArticles(resp){
        dtCatsArticles .fnDraw(false);
    }

    $(document).ready(function () {
        dtCatsArticles = $('#dtCatsArticles').dataTable({
            "sDom": '<"top"lp>rt<"bottom"ip><"clear">',
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo __SITE_URL . 'categories_articles/ajax_categories_articles_dt';?>",
            "sServerMethod": "POST",
            "aoColumnDefs": [
                {"sClass": "w-40 tac", "aTargets": ['dt_id'], "mData": "dt_id"},
                {"sClass": "w-150 tac", "aTargets": ['dt_photo'], "mData": "dt_photo", "bSortable": false},
                {"sClass": "w-400", "aTargets": ['dt_category'], "mData": "dt_category"},
                {"sClass": "", "aTargets": ['dt_text'], "mData": "dt_text", "bSortable": false},
                {"sClass": "w-150 tac", "aTargets": ['dt_tlangs_list'], "mData": "dt_tlangs_list", "bSortable": false  },
                {"sClass": "w-150 tac", "aTargets": ['dt_tlangs'], "mData": "dt_tlangs", "bSortable": false  },
                {"sClass": "tac w-80", "aTargets": ['dt_actions'], "mData": "dt_actions", "bSortable": false}
            ],

            "fnServerData": function (sSource, aoData, fnCallback) {

                if (!articlesFilters) {
                    articlesFilters = $('.dt_filter').dtFilters('.dt_filter',{
                        'container': '.wr-filter-list',
                        'debug': false,
                        callBack: function () {
                            dtCatsArticles.fnDraw();
                        },
                        onSet: function (callerObj, filterObj) {

                        },
                        onDelete: function (filter) {
                            if (filter.name == 'parent') {
                                $('select[name="parent"]').siblings().remove();
                            }
                        }
                    });
                }

                aoData = aoData.concat(articlesFilters.getDTFilter());
                $.ajax({
                    "dataType": 'JSON',
                    "type": "POST",
                    "url": sSource,
                    "data": aoData,
                    "success": function (data, textStatus, jqXHR) {
                        if (data.mess_type == 'error')
                            systemMessages(data.message, 'message-' + data.mess_type);
                        if (data.mess_type == 'info')
                            systemMessages(data.message, 'message-' + data.mess_type);

                        fnCallback(data, textStatus, jqXHR);

                    }
                });
            },
            "sPaginationType": "full_numbers",
            "fnDrawCallback": function (oSettings) {
                var keywordsSearch = $('.filter-admin-panel').find('input[name=keywords]').val();
                if (keywordsSearch !== '')
                    $("#dtArticles tbody *").highlight(keywordsSearch, "highlight");
        }
    });

    // DataTables text more
    $('body').on('click', '.btn-article-more', function (e) {
        e.preventDefault();
        var $thisBtn = $(this);
        var $textB = $thisBtn.closest('td').find('.hidden-b');
        $textB.toggleClass('h-50');

        ($textB.hasClass('h-50')) ? $thisBtn.attr('title', 'view more') : $thisBtn.attr('title', 'hide more');
        $thisBtn.toggleClass('ep-icon_arrows-down ep-icon_arrows-up');
    });

    $('body').on('click', '.view-next-subcat', function(){
        var $this = $(this);
        var $block = $this.closest('.cats-level');
        var id_cat = $block.find('select').val();

        $.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL ?>categories_articles/ajax_categories_articles_operation/get_subcats',
            data: { id_cat: id_cat},
            beforeSend: function () {
                showLoader($block);
            },
            dataType: 'json',
            success: function (data) {
                hideLoader($block);
                if(data.mess_type == 'success'){
                    if (data.cats.length == 0){
                        systemMessages(data.message, 'message-' + data.mess_type);
                        return ;
                    }
                    $this.removeClass('view-next-subcat ep-icon_plus').addClass('clear-cat ep-icon_minus');
                    cur_lvl_cats++;
                    var append_html = '<div class="w-100pr cats-level" data-level="' + cur_lvl_cats + '"><select class="w-95pr pull-left validate[required] category-name mt-10" name="category[' + cur_lvl_cats + ']">';
                    $.each(data.cats, function(key, value){
                        append_html += '<option value="' + value.category_id + '">' + value.name + '</option>';
                    });
                    append_html += '</select><span class="ep-icon ep-icon_plus txt-blue view-next-subcat w-3pr pull-right mt-17"></span></div>';

                    $block.parent().append(append_html);
                }else{
                    systemMessages(data.message, 'message-' + data.mess_type);
                }
            }
        });
    });

    $('body').on('click', '.clear-cat', function(){
        $semn = $(this);
        cur_lvl_cats = $semn.closest('.cats-level').data("level") * 1;
        $semn.removeClass("clear-cat ep-icon_minus").addClass("view-next-subcat ep-icon_plus");
        $(".cats-level").each( function( index ){
            if($(this).data("level")*1 > cur_lvl_cats)
                $(this).remove();
        });
    });

    $('body').on('change', '.category-name', function(){
        $(this).closest('.cats-level').find('.clear-cat').click();
    });

	$('body').on('change', 'select.categ1', function(){
		var select = this;
		var cat = select.value;
		var control = select.id;

		var level = $(select).attr('level');
		$('td.select_category div.subcategories').each(function (){
			thislevel = $(this).attr('level');
			if(thislevel > level) $(this).remove();
		});

		if(cat != 0){
			if(cat != control){
				$.ajax({
					type: 'POST',
					url: 'categories_articles/ajax_categories_articles_operation/get_filter_subcategories',
					dataType: 'JSON',
					data: { cat: cat, level : level},
					beforeSend: function(){ showLoader('.full_block'); },
					success: function(json){
						if(json.mess_type == 'success'){
							$('.select_category').append(json.content);
							$('select.categ1').css('color', 'black');
							$(select).css('color', 'red');
						}else{
							systemMessages(json.message,  'message-' + json.mess_type);
						}
						hideLoader('.full_block');
					},
					error: function(){alert('ERROR')}
				});
			}else{
				$('select.categ1').css('color', 'black');
				$('select.categ1[level='+(level-1)+']').css('color', 'red');
			}
		} else{
			$('.subcategories').remove();
		}
	});

});
</script>

<div class="row">
    <div class="col-xs-12">
        <div class="titlehdr h-30">
        	<span>Categories articles</span>
        	<a class="pull-right ep-icon ep-icon_plus-circle txt-green fancyboxValidateModalDT fancybox.ajax" href="categories_articles/popup_forms/add_category_article" data-title="Add category article" data-table="dtCatsArticles"></a>
		</div>

        <?php tmvc::instance()->controller->view->display('admin/categories_articles/filter_panel_view'); ?>
        <div class="wr-filter-list clearfix mt-10"></div>

        <table class="data table-striped table-bordered w-100pr" id="dtCatsArticles" cellspacing="0" cellpadding="0" >
            <thead>
                <tr>
                    <th class="dt_id">#</th>
                    <th class="dt_photo">Photo</th>
                    <th class="dt_category">Category</th>
                    <th class="dt_text">Text</th>
					<th class="dt_tlangs_list">Translated in</th>
					<th class="dt_tlangs">Translate</th>
                    <th class="dt_actions">Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
