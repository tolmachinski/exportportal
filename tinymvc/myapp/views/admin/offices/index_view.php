<script src="https://maps.googleapis.com/maps/api/js?v=3&key=AIzaSyAko8g1LP9autKH12-8d1VkUZn3UaIZB8E"></script>

<script>
	var removeOffice = function(obj){
		var $this = $(obj);
		var office = $this.data('office');

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL?>offices/ajax_offices_operation/delete_office',
			data: { office : office},
			beforeSend: function(){ },
			dataType: 'json',
			success: function(resp){
				systemMessages( resp.message, 'message-' + resp.mess_type );

				if(resp.mess_type == 'success'){
					$this.closest('tr').fadeOut(function(){
						$(this).remove();
					})
				}
			}
		});
	}

	function callbackUdateOffices(resp){
		$('#table-offices #troffice-'+resp.id_office)
			.find('.name-b').text(resp.name_office).end()
			.find('.phone-b').text(resp.phone_office).end()
			.find('.fax-b').text(resp.fax_office).end()
			.find('.email-b').text(resp.email_office).end()
			.find('.address-b').html('<span class="ep-icon ep-icon_marker"></span><span>'+resp.address_office+'</span>').end()
			.find('.country-b').text(resp.country_name);
	}

	function callbackCreateOffices(resp){
		$('#table-offices tbody').append('<tr id="troffice-'+resp.id_office+'">\
                        <td class="tac w-50" >'+resp.id_office+'</td>\
                        <td class="tac name-b">'+resp.name_office+'</td>\
                        <td class="w-150 phone-b">'+resp.phone_office+'</td>\
                        <td class="w-150 fax-b">'+resp.fax_office+'</td>\
                        <td class="w-250 email-b">'+resp.email_office+'</td>\
                        <td class="w-250 tac address-b"><span class="ep-icon ep-icon_marker"></span><span>'+resp.address_office+'</span></td>\
						<td class="w-150 vat country-b">'+resp.country_name+'</td>\
                        <td class="w-80">\
							<a class="ep-icon ep-icon_pencil fancybox.ajax fancyboxValidateModal" href="offices/popup_forms/update_office/'+resp.id_office+'" data-title="Edit office" title="Edit office"></a>\
							<a class="ep-icon ep-icon_remove txt-red confirm-dialog" data-message="Are you sure you want to delete this office?" data-callback="removeOffice" data-office="'+resp.id_office+'" title="Delete text block"></a>\
                        </td>\
                    </tr>');
	}
</script>

<div class="row">
    <div class="col-xs-12">
        <h3 class="titlehdr mt-10 mb-10">Offices list <a class="btn btn-primary btn-sm pull-right mb-10 fancybox.ajax fancyboxValidateModal" href="<?php echo __SITE_URL;?>offices/popup_forms/add_office" data-title="Add office">Add office</a></h3>

        <table id="table-offices" cellspacing="0" cellpadding="0" class="data table-striped table-bordered vam w-100pr">
            <thead>
                <tr>
                    <th>#</th>
					<th>Name</th>
                    <th>Phone</th>
                    <th>Fax</th>
                    <th>Email</th>
                    <th>Address</th>
					<th>Country</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if(isset($offices_list) && count($offices_list)){?>
                <?php foreach($offices_list as $office_item){?>
					<tr id="troffice-<?php echo $office_item['id_office']?>">
                        <td class="tac w-50" ><?php echo $office_item['id_office']?></td>
                        <td class="tac name-b"><?php echo $office_item['name_office']?></td>
                        <td class="w-150 phone-b"><?php echo $office_item['phone_office']?></td>
                        <td class="w-150 fax-b"><?php echo $office_item['fax_office']?></td>
                        <td class="w-250 email-b"><?php echo $office_item['email_office'] ?></td>
                        <td class="w-250 tac address-b"> <?php if(!empty($office_item['latitude']) && !empty($office_item['longitude'])) {?><span class="ep-icon ep-icon_marker"></span> <?php } ?> <span><?php echo $office_item['address_office']?></span></td>
						<td class="w-150 vat country-b">
							<img
                                width="32"
                                height="32"
                                src="<?php echo getCountryFlag($office_item['country']); ?>"
                                alt="<?php echo $office_item['country'];?>"
                            />
							<p class="ml-5"><?php echo $office_item['country']; ?></p>
						</td>
                        <td class="w-80">
							<a class="ep-icon ep-icon_pencil fancybox.ajax fancyboxValidateModal" href="<?php echo __SITE_URL;?>offices/popup_forms/update_office/<?php echo $office_item['id_office']?>" data-title="Edit office" title="Edit office"></a>
                            <a class="ep-icon ep-icon_remove txt-red confirm-dialog" data-message="Are you sure you want to delete this office?" data-callback="removeOffice" data-office="<?php echo $office_item['id_office']?>" title="Delete text block"></a>
                        </td>
                    </tr>
                <?php } ?>
            <?php } else{ ?>
                <tr><td colspan="8">Textual blocks not exist still.</td></tr>
            <?php } ?>

            </tbody>
        </table>
    </div>
</div>
