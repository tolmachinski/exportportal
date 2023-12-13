<script type="text/javascript">
var category = '';
var dtCatAttrFilters;
var dtCategoryAttribute;

$(document).ready(function(){
    dtCategoryAttribute = $('#dtCategoryAttribute').dataTable( {
        "sDom": '<"top"pf>rt<"bottom"ip><"clear">',
        "bProcessing": true,
        "bServerSide": true,
        "sAjaxSource": "<?php echo __SITE_URL?>catattr/ajax_cat_attr_dt",
        "sServerMethod": "POST",
        "aoColumnDefs": [
            { "sClass": "w-60 tac", "aTargets": ['dt_id'], "mData": "dt_id", "bSortable": false  },
            { "sClass": "tac", "aTargets": ['dt_name'], "mData": "dt_name", "bSortable": false  },
            { "sClass": "w-100 tac", "aTargets": ['dt_type'], "mData": "dt_type", "bSortable": false  },
            { "sClass": "w-200 tac", "aTargets": ['dt_values_type'], "mData": "dt_values_type", "bSortable": false },
            { "sClass": "w-150 tac", "aTargets": ['dt_tlangs'], "mData": "dt_tlangs", "bSortable": false },
            { "sClass": "w-200 tac", "aTargets": ['dt_tlangs_list'], "mData": "dt_tlangs_list", "bSortable": false },
            { "sClass": "tac w-100", "aTargets": ['dt_actions'], "mData": "dt_actions", "bSortable": false }
        ],
        "iDisplayLength": 100,
        "fnServerData": function ( sSource, aoData, fnCallback ) {
            if(!dtCatAttrFilters){
				dtCatAttrFilters = $('.dt_filter').dtFilters('.dt_filter',{
                    'container': '.wr-filter-list',
                    'debug'	: false,
                    callBack: function(filter){
                        dtCategoryAttribute.fnDraw();
                    },
                    onSet: function(callerObj, filterObj){},
                    onDelete: function(filterObj){
                        if(filterObj.name == 'category'){
                            $('.sub-categories-wr').html('');
                            $('select[name="category_parent"] option:first').prop('selected',true);
                            $('select[name="category_parent"]').css('color', 'black');
						}
                    },
                    onReset: function(){}
                });
			}

            aoData = aoData.concat(dtCatAttrFilters.getDTFilter());

            $.ajax( {
                "dataType": 'JSON',
                "type": "POST",
                "url": sSource,
                "data": aoData,
                "success": function (data, textStatus, jqXHR) {
                    if(data.mess_type == 'error')
                        systemMessages(data.message, 'message-' + data.mess_type);
                    if(data.mess_type == 'info')
                        systemMessages(data.message, 'message-' + data.mess_type);

                    fnCallback(data, textStatus, jqXHR);
                }
            });
        },
        "fnDrawCallback": function( oSettings ) {
            if($('.dataTables_filter input[type="text"]').val() != '' || oSettings.aoData.length == 0)
                $("table#dtCategoryAttribute tbody").sortable('disable');
            else
                $("table#dtCategoryAttribute tbody").sortable('enable');
        }
    });

    $('body').on('change', 'select[name="category_parent"]', function(){
        var $this = $(this);
        var $categories_wr = $('.sub-categories-wr');
        var category_level = intval($this.data('level'));

        $this.css('color', 'red');
        $('select[data-level="'+(category_level-1)+'"]').css('color', 'black');

        $categories_wr.find('select[data-level]').each(function (){
            var level = intval($(this).data('level'));
            if(level > category_level){
                $(this).remove();
            }
        });

        if($this.val() != ''){
            category = intval($this.val());
            var category_text = [];
            $('body').find('select[name="category_parent"]').each(function (){
                category_text.push($(this).find('option:selected').text());
            });
            $('.category_filter_wr').html('<input class="dt_filter" type="hidden" data-title="Category" name="category" value="'+category+'" data-value-text="'+category_text.join(' &raquo; ')+'">');
            $('.category_filter_wr').find('input[name="category"]').change();
            $.ajax({
                type: 'POST',
                url: '<?php echo __SITE_URL;?>categories/ajax_category_operation/get_categories',
                dataType: 'JSON',
                data: {parent: category},
                beforeSend: function(){},
                success: function(resp){
                    if(resp.mess_type == 'success'){
                        if(Object.keys(resp.categories).length > 0){
                            var template = '<select class="form-control" data-level="'+(category_level+1)+'" data-title="Category" name="category_parent">\
                                                <option value="">Select category</option>';
                            $.each(resp.categories, function(index, category){
                                template += '<option value="'+category.category_id+'">'+category.name+'</option>';
                            });
                            template += '</select>';

                            $categories_wr.append(template);
                        }
                        dtCatAttrFilters.reInit();
                    }
                }
            });
        }
    });

    $("table#dtCategoryAttribute tbody").sortable({
        opacity: 0.8,
        cursor: 'move',
        axis: 'y',
        cancel: '.sortable-disabled',
        change: function( event, ui ) {
            $('.sortable-disabled').closest('tr').remove();
        },
        update: function() {
            var order = $(this).sortable("serialize") + '&update=update&cat='+category;
            $.post(
                "catattr/update_order",
                order,
                function(json){
                    $("table#dtCategoryAttribute tbody").find('tr.odd, tr.even').removeClass('odd even');

                    systemMessages(json.message, 'message-' + json.mess_type);
                },
                'JSON'
            )
        }
    });
});

var get_attr_values = function(obj){
    var $this = $(obj);
    var nTr = $this.closest('tr');
    if (dtCategoryAttribute.fnIsOpen(nTr)){
        dtCategoryAttribute.fnClose(nTr);
    } else{
        $.ajax({
            url: '<?php echo __SITE_URL;?>catattr/ajax_attr_operation/get_attr_values',
            type: 'POST',
            dataType: 'JSON',
            data: {attr : $this.data('attr')},
            beforeSend: function(){},
            success: function(resp){
                if(resp.mess_type == 'success'){
                    dtCategoryAttribute.fnOpen(nTr, '<div class="dt-details">'+resp.content+'</div>', 'sortable-disabled');
                }
            }
        });
    }
}

var valueRemove = function(obj){
    var $this = $(obj);
    var val = $this.data('val');

    $.ajax({
        type: 'POST',
        url: '<?php echo __SITE_URL;?>catattr/ajax_attr_operation/delete_values',
        data: { val : val},
        dataType: 'json',
        success: function(data){
            systemMessages( data.message, 'message-' + data.mess_type );
            if(data.mess_type == 'success'){
                $this.closest('tr').remove();
            }
        }
    });
}

var attrRemove = function(obj){
    var $this = $(obj);
    var attr = $this.data('attr');

    cat = $('div#select_category select[name=parent]').last().val();
    if(cat == 0){
        systemMessages('Please select category', 'message-error');
        return false;
    }

    $.ajax({
        type: 'POST',
        url: '<?php echo __SITE_URL?>catattr/ajax_attr_operation/delete_attr',
        data: { attr : attr},
        dataType: 'json',
        beforeSend: function(){showLoader('.full_block', 'Loading...');},
        success: function(data){
            systemMessages( data.message, 'message-' + data.mess_type );
            hideLoader('.full_block');
            if(data.mess_type == 'success'){
                dtCategoryAttribute.fnDraw();
            }
        }
    });
}

var delete_attr_i18n = function(obj){
    var $this = $(obj);
    var attr = $this.data('attr');
    var lang_attr = $this.data('lang');
    $.ajax({
        type: 'POST',
        url: '<?php echo __SITE_URL?>catattr/ajax_attr_operation/delete_attr_i18n',
        data: { attr : attr, lang_attr:lang_attr },
        dataType: 'json',
        beforeSend: function(){
            showLoader('.full_block', 'Loading...');
        },
        success: function(data){
            systemMessages( data.message, 'message-' + data.mess_type );
            hideLoader('.full_block');
            if(data.mess_type == 'success'){
                dtCategoryAttribute.fnDraw(false);
            }
        }
    });
}

var delete_value_i18n = function(obj){
    var $this = $(obj);
    var attr = $this.data('attr');
    var value = $this.data('value');
    var lang_value = $this.data('lang');
    $.ajax({
        type: 'POST',
        url: '<?php echo __SITE_URL?>catattr/ajax_attr_operation/delete_value_i18n',
        data: { id_value : value, lang_value:lang_value },
        dataType: 'json',
        beforeSend: function(){
            showLoader('.full_block', 'Loading...');
        },
        success: function(data){
            systemMessages( data.message, 'message-' + data.mess_type );
            hideLoader('.full_block');
            if(data.mess_type == 'success'){
                $('.call-function[data-callback="get_attr_values"][data-attr="'+attr+'"]').click().click();
            }
        }
    });
}
</script>
<div class="row">
    <div class="col-xs-12">
        <div class="titlehdr h-30">
            <span id="tb_title">Attributes for Category</span>
            <a class="ep-icon fs-16 pull-right ep-icon_plus-circle txt-green fancyboxValidateModalDT fancybox.ajax" href="<?php echo __SITE_URL;?>catattr/popup_forms/add_category_attr" title="Add category attribute" data-title="Add category attribute"></a>
        </div>

		<?php tmvc::instance()->controller->view->display('admin/catattributes/filter_panel_view')?>
		<div class="wr-filter-list clearfix mt-10"></div>

        <table class="data table-striped table-bordered w-100pr" id="dtCategoryAttribute" cellspacing="0" cellpadding="0" >
             <thead>
                 <tr>
                    <th class="dt_id">#</th>
                    <th class="dt_name">Name</th>
                    <th class="dt_type">Type</th>
                    <th class="dt_values_type">Value's type</th>
                    <th class="dt_tlangs_list">Translated in</th>
                    <th class="dt_tlangs">Translate</th>
                    <th class="dt_actions">Actions</th>
                 </tr>
             </thead>
             <tbody></tbody>
         </table>
     </div>
</div>
