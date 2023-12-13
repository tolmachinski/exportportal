<script>
var dtCategoryList;
$(document).ready(function(){
	//slect with categories

    $('body').on('change', '.categ_table', function(){
        var select = this;
        var cat = select.value;
        var sClass = select.className;
        var control = select.id; //alert(cat + '-- '+ control);
        var level = $(select).attr('level');
        $('div#select_category div.subcategories').each(function (){
            thislevel = $(this).attr('level');
            if(thislevel > level) $(this).remove();
        });

        if(cat == -1 && $('#keywords').val() == ''){
			systemMessages("Please write search keywords.", "message-info");
		}else{
			if(cat != control && cat != 0){
				$.ajax({
					type: 'POST',
					url: '/categories/getcategories',
					dataType: 'JSON',
					data: { op : 'select', cat: cat, level : level, cl : sClass},
					success: function(json){
						if(json.mess_type = 'success'){
							$('div#select_category').append(json.content);
							$('select.categ_table').css('color', 'black');
							$(select).css('color', 'red');
						}else{
							systemMessages(json.message,  'message-' + json.mess_type);
							return false;
						}
					},
					error: function(){alert('ERROR')}
				});
			}else{
				$('select.categ_table').css('color', 'black');
				$('select.categ_table[level='+(level-1)+']').css('color', 'red');
			}
		}

		dtCategoryList.fnDraw();
    });



    dtCategoryList = $('#dtCategoryList').dataTable({
		"sDom": '<"top"lpf>rt<"bottom"ip><"clear">',
		"bProcessing": true,
		"bServerSide": true,
		"sPaginationType": "full_numbers",
		"sAjaxSource": "<?php echo __SITE_URL ?>categories/ajax_categories_dt",
		"sServerMethod": "POST",
		"bFilter": false,
		"aoColumnDefs": [
			{ "sClass": "w-50 tac vam", "aTargets": ["dt_id"], "mData": "dt_id"},
			{ "sClass": "w-300", "aTargets": ["dt_name"], "mData": "dt_name"},
			{ "sClass": "", "aTargets": ["dt_breadcrumbs"], "mData": "dt_breadcrumbs", "bSortable": false},
			{ "sClass": "w-200 tac", "aTargets": ["dt_meta"], "mData": "dt_meta", "bSortable": false},
			{ "sClass": "w-120 tac", "aTargets": ["dt_type"], "mData": "dt_type" , "bSortable": false},
			{ "sClass": "w-120 tac", "aTargets": ["dt_p_or_m"], "mData": "dt_p_or_m"},
			{ "sClass": "w-150 tac", "aTargets": ['dt_tlangs_list'], "mData": "dt_tlangs_list", "bSortable": false  },
			{ "sClass": "w-150 tac", "aTargets": ['dt_tlangs'], "mData": "dt_tlangs", "bSortable": false  },
			{ "sClass": "w-80 tac vam", "aTargets": ["dt_actions"], "mData": "dt_actions" , "bSortable": false },

		],
		"iDisplayLength": 25,
		"sorting": [[1, "asc"]],
		"fnServerParams": function(aoData) {

			aoData.push({"name":"parent", "value":getParent('.categ_table')});
			aoData.push({"name":"keywords", "value":$('#keywords').val()});
		},
		"fnDrawCallback": function(oSettings) {

		}
	});

	function getParent(select_class){
	    return $(select_class).last().val();
	}

	$('#cat-actualized').on('click', function(e){
		e.preventDefault();

		$.ajax({
			type: 'POST',
			url: 'categories/actualize',
			dataType: 'JSON',
			beforeSend: function(){
				showLoader('.full_block');
			},
			success: function(json){
				systemMessages(json.message, 'message-' + json.mess_type);
				hideLoader('.full_block');
			},
			error: function(){alert('ERROR')}
		})
	});

	$('#keywords').on('change', function(){
		dtCategoryList.fnDraw();
	});
});

var remove_category_i18n = function(obj){
	var $this = $(obj);
	var category = $this.data('category');
	var lang_category = $this.data('lang');

	$.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL?>categories/ajax_category_operation/delete_category_i18n',
		data: {category: category, lang:lang_category},
		beforeSend: function(){ },
		dataType: 'json',
		success: function(data){
			systemMessages( data.message, 'message-' + data.mess_type );
			if(data.mess_type == 'success'){
				dtCategoryList.fnDraw();
			}
		}
	});
}
</script>

<div class="row">
    <div class="col-xs-12 full_block">
		<div class="titlehdr h-30"><span id="tb_title">Main Categories</span>
			<div class="pull-right">
				<a id="cat-actualized" class="ep-icon ep-icon_branches txt-blue" href="#" title="Actualize"></a>
				<a class="ep-icon ep-icon_plus-circle txt-green fancyboxValidateModalDT fancybox.ajax" href="<?php echo __SITE_URL;?>categories/popup_forms/add_category" title="Add category" data-title="Add category"></a>
			</div>
		</div>
		<div id="select_category" class="pb-10 clearfix">
			<div class="pull-left mt-10 w-100"> Category tree </div>
			<select class="categ_table pull-left mr-5 mt-5" level="1">
				<option value="-1">All Categories</option>
				<option value="0" selected="selected">Main Categories</option>
				<?php if(count($categories)){
					foreach($categories as $category){?>
					<option  value="<?php echo $category['category_id']?>"><?php echo $category['name']?></option>
					<?php }
				}?>
			</select>
		</div>
		<div id="search" class="pb-10 clearfix">
			<div class="pull-left mt-10 w-100"> Keywords </div>
			<input type="text" class="w-365 pull-left" id="keywords" placeholder="Enter your keywords"/>
			<div class="pt-10 pl-10 pull-left">*The selected categories and keywords are interconnected.</div>
		</div>
		<div id="tb_content">
			<table id="dtCategoryList" class="data table-striped table-bordered w-100pr" >
				<thead>
					<tr>
						<th class="dt_id">#</th>
						<th class="dt_name">Category</th>
						<th class="dt_breadcrumbs">Breadcrumbs</th>
						<th class="dt_meta">Meta data</th>
						<th class="dt_type">Type</th>
						<th class="dt_p_or_m">Product/Motor</th>
						<th class="dt_tlangs_list">Translated in</th>
						<th class="dt_tlangs">Translate</th>
						<th class="dt_actions">Actions</th>
					</tr>
				</thead>
				<tbody></tbody>
			</table>
		</div>
    </div>
</div>
