<script>	
	function callbackUpdateCategory(resp){
		$('#table-category').find('#trcategory-'+resp.id_category)
			.find('.seo-b').html('<span title="'+resp.h1_category+'">H1 |</span>\
							<span title="'+resp.description_category+'">Description |</span>\
							<span title="'+resp.keywords_category+'">Keywords</span>');
	}
	
	function callbackAddCategory(resp){
		$('#table-category tbody').append('<tr id="trcategory-'+resp.id_category+'">\
                        <td class="tac w-50" >'+resp.id_category+'</td>\
                        <td class="tac name-b">'+resp.category_name+'</td>\
                        <td class="tac seo-b">\
							<span title="'+resp.h1_category+'">H1 |</span>\
							<span title="'+resp.description_category+'">Description |</span>\
							<span title="'+resp.keywords_category+'">Keywords</span>\
						</td>\
                        <td class="tac w-60">\
							<a class="ep-icon ep-icon_pencil fancybox.ajax fancyboxValidateModal" href="directory/popup_forms/update_company_category/'+resp.id_category+'" data-title="Update directory category" title="Edit directory category"></a>\
							<a class="ep-icon ep-icon_remove confirm-dialog txt-red" data-message="Are you sure you want to delete this category?" data-callback="removeDirectoryCategory" data-category="'+resp.id_category+'" href="#" title="Remove category"></a>\
                        </td>\
                    </tr>');
	}

	var removeDirectoryCategory = function(obj){
		var $this = $(obj);
		var category = $this.data('category');

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL?>directory/ajax_company_category_operation/remove_company_category',
			data: { category : category},
			beforeSend: function(){  },
			dataType: 'json',
			success: function(resp){
				systemMessages( resp.message, 'message-' + resp.mess_type );

				if(resp.mess_type == 'success'){
					$this.closest('tr').fadeOut(function(){
						$(this).remove();
					});
				}
			}
		});
	}
</script>

<div class="row">
    <div class="col-xs-12">
        <h3 class="titlehdr mt-10 mb-10">
        	Directory category list
        	<a class="btn btn-primary btn-sm pull-right mb-10 fancybox.ajax fancyboxValidateModal" href="<?php echo __SITE_URL;?>directory/popup_forms/add_company_category" data-title="Add category">Add category</a>
        </h3>

        <table id="table-category" cellspacing="0" cellpadding="0" class="data table-striped table-bordered vam w-100pr">
            <thead>
                <tr>
                    <th >#</th>
					<th>Category</th>
					<th>SEO</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if(isset($category_list) && count($category_list)){?>
                <?php foreach($category_list as $category_item){?>
					<tr id="trcategory-<?php echo $category_item['id_category']?>">
                        <td class="tac w-50" ><?php echo $category_item['id_category']?></td>
                        <td class="tac name-b"><?php echo $category_item['name']?></td>
                        <td class="tac seo-b">
							<span title="<?php echo $category_item['h1_category']?>">H1 |</span>
							<span title="<?php echo $category_item['description_category']?>">Description |</span>
							<span title="<?php echo $category_item['keywords_category']?>">Keywords</span>
						</td>
                        <td class="tac w-60">
							<a class="ep-icon ep-icon_pencil fancybox.ajax fancyboxValidateModal" href="<?php echo __SITE_URL;?>directory/popup_forms/update_company_category/<?php echo $category_item['id_category']?>" data-title="Update directory category" title="Edit directory category"></a>
                        	<a class="ep-icon ep-icon_remove confirm-dialog txt-red" data-message="Are you sure you want to delete this category?" data-callback="removeDirectoryCategory" data-category="<?php echo $category_item['id_category']?>" href="#" title="Remove category"></a>
                        </td>
                    </tr>
                <?php } ?>
            <?php } else{ ?>
                <tr><td colspan="4">Directory category not exist still.</td></tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>
