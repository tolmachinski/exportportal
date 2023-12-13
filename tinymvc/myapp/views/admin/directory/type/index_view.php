<script>
$(document).ready(function(){

});

	function collbackAddCompanyType(resp){
		$('#table-type').find('tbody').append('<tr id="trtype-'+resp.id_type+'">\
                        <td class="tac w-50">'+resp.id_type+'</td>\
                        <td class="tac name-b">'+resp.name_type+'</td>\
                        <td class="tac w-80">\
							<a class="ep-icon ep-icon_pencil fancybox.ajax fancyboxValidateModal" href="directory/popup_forms/update_company_type/'+resp.id_type+'" data-title="Edit directory type" title="Edit directory type"></a>\
                            <a class="ep-icon ep-icon_remove txt-red confirm-dialog" data-callback="removeDirectoryType" data-message="Are you sure you want to delete this type?" data-type="'+resp.id_type+'" title="Delete directory type"></a>\
                        </td>\
                    </tr>');
	}
	
	function collbackUpdateCompanyType(resp){
		$('#table-type').find('#trtype-'+resp.id_type).find('.name-b').text(resp.name_type);
	}

	var removeDirectoryType = function(obj){
		var $this = $(obj);
		var type = $this.data('type');

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL?>directory/ajax_company_operations/remove_company_type',
			data: { type : type},
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
        	Directory type list
        	<a class="btn btn-primary btn-sm fancybox.ajax fancyboxValidateModal pull-right" href="<?php echo __SITE_URL;?>directory/popup_forms/add_company_type" data-title="Add type">Add type</a>
        </h3>
		
        <table id="table-type" cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr">
            <thead>
                <tr>
                    <th >#</th>
					<th>Name</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if(isset($type_list) && count($type_list)){?>
                <?php foreach($type_list as $type_item){?>
					<tr id="trtype-<?php echo $type_item['id_type']?>">
                        <td class="tac w-50"><?php echo $type_item['id_type']?></td>
                        <td class="tac name-b"><?php echo $type_item['name_type']?></td>
                        <td class="tac w-80">
							<a class="ep-icon ep-icon_pencil fancybox.ajax fancyboxValidateModal" href="<?php echo __SITE_URL;?>directory/popup_forms/update_company_type/<?php echo $type_item['id_type']?>" data-title="Edit directory type" title="Edit directory type"></a>
                            <a class="ep-icon ep-icon_remove txt-red confirm-dialog" data-callback="removeDirectoryType" data-message="Are you sure you want to delete this type?" data-type="<?php echo $type_item['id_type']?>" title="Delete directory type"></a>
                        </td>
                    </tr>
                <?php } ?>
            <?php } else{ ?>
                <tr><td colspan="3">Directory type not exist still.</td></tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>
