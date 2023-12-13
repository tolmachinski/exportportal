<script>
$(document).ready(function(){

});

	function callbackUpdateIndustry(resp){
		$('#table_industry tbody').find('#trindustry-'+resp.id_category)
			.find('.seo-b').html('<span title="'+resp.h1_category+'">H1 |</span>\
							<span title="'+resp.description_category+'">Description |</span>\
							<span title="'+resp.keywords_category+'">Keywords</span>');
	}

	function callbackAddIndustry(resp){
		$('#table_industry tbody').append('<tr id="trindustry-'+resp.id_category+'">\
                        <td class="tac w-50" >'+resp.id_category+'</td>\
                        <td class="tac name-b">'+resp.category_name+'</td>\
                        <td class="tac seo-b">\
							<span title="'+resp.h1_category+'">H1 |</span>\
							<span title="'+resp.description_category+'">Description |</span>\
							<span title="'+resp.keywords_category+'">Keywords</span>\
						</td>\
                        <td class="tac w-80">\
							<a class="ep-icon ep-icon_pencil fancybox.ajax fancyboxValidateModal" href="directory/popup_forms/update_company_industry/'+resp.id_category+'" data-title="Edit directory industry" title="Edit directory industry"></a>\
							<a class="ep-icon ep-icon_remove txt-red confirm-dialog" data-category="'+resp.id_category+'" data-callback="removeDirectoryIndustry" data-message="Are you sure you want to delete this industry?" href="#"></a>\
                        </td>\
                    </tr>');
	}

	var removeDirectoryIndustry = function(obj){
		var $this = $(obj);
		var category = $this.data('category');

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL?>directory/ajax_company_industry_operation/remove_company_industry',
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
        <h3 class="titlehdr mt-10 mb-10">Directory industry list <a class="btn btn-primary btn-sm pull-right fancybox.ajax fancyboxValidateModal" data-title="Add industry" href="<?php echo __SITE_URL;?>directory/popup_forms/add_company_industry">Add industry</a></h3>

        <table id="table_industry" cellspacing="0" cellpadding="0" class="data table-striped table-bordered vam w-100pr">
            <thead>
                <tr>
                    <th class="w-50">#</th>
					<th>Category</th>
					<th>SEO</th>
                    <th class="w-80">Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if(isset($industry_list) && count($industry_list)){?>
                <?php foreach($industry_list as $industry_item){?>
					<tr id="trindustry-<?php echo $industry_item['id_category']?>">
                        <td class="tac w-50" ><?php echo $industry_item['id_category']?></td>
                        <td class="tac name-b"><?php echo $industry_item['name']?></td>
                        <td class="tac seo-b">
							<span title="<?php echo $industry_item['h1_category']?>">H1 |</span>
							<span title="<?php echo $industry_item['description_category']?>">Description |</span>
							<span title="<?php echo $industry_item['keywords_category']?>">Keywords</span>
						</td>
                        <td class="tac w-80">
							<a class="ep-icon ep-icon_pencil fancybox.ajax fancyboxValidateModal" href="<?php echo __SITE_URL;?>directory/popup_forms/update_company_industry/<?php echo $industry_item['id_category']?>" data-title="Edit directory industry" title="Edit directory industry"></a>
                        	<a class="ep-icon ep-icon_remove txt-red confirm-dialog" data-category="<?php echo $industry_item['id_category']?>" data-callback="removeDirectoryIndustry" data-message="Are you sure you want to delete this industry?" href="#"></a>
                        </td>
                    </tr>
                <?php } ?>
            <?php } else{ ?>
                <tr><td colspan="4">Directory industry not exist still.</td></tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>
