<form class="relative-b validateModal">
	<div class="wr-form-content w-700 mh-700">
        <table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr mt-15 vam-table">
            <tbody>
                <tr>
                    <td class="w-150">Request for</td>
                    <td>
                        <div class="input-group input-group--checks">
							<label class="input-group-addon">
								<input  type="radio" name="type" data-title="Status" value="buyer" checked="checked">
								<span class="input-group__desc">Buyer</span>
							</label>
							<label class="input-group-addon">
								<input  type="radio" name="type" data-title="Status" value="seller">
								<span class="input-group__desc">Seller</span>
							</label>
							<label class="input-group-addon">
								<input  type="radio" name="type" data-title="Status" value="shipper">
								<span class="input-group__desc">Freight Forwarder</span>
							</label>
						</div>
                    </td>
                </tr>
                <tr class="users_list">
                    <td>Search users</td>
                    <td>
                        <input type="text" class="w-300 pull-left" name="keywords" maxlength="50" placeholder="Keywords (user name, email or freight forwarder name)">
                        <span class="btn btn-primary ml-5 h-30 w-45 search_by_type"><i class="ep-icon ep-icon_magnifier lh-18"></i></span>
                    </td>
                </tr>
                <tr class="users_list">
                    <td>Select the user</td>
                    <td>
                        <select name="user" class="w-100pr">
						    <option value="">All users</option>
                        </select>
                    </td>
                </tr>
                <tr class="shippers_list" style="display:none;">
                    <td>Select the freight forwarder</td>
                    <td>
                        <select name="shipper" class="w-100pr">
						    <option value="">Select the freight forwarder</option>
						    <?php foreach($shippers as $shipper){?>
                                <option value="<?php echo $shipper['id'];?>">
                                    <?php echo $shipper['co_name'];?>
                                </option>
						    <?php }?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="w-100">Pay amount</td>
                    <td>
                        <input type="text" name="pay_amount" class="validate[custom[positive_number]] w-100pr" value="" placeholder="Pay amount"/>
                    </td>
                </tr>
                <tr>
                    <td class="w-100">Notes</td>
                    <td>
                        <textarea class="w-100pr"  name="pay_notes" rows="5" placeholder="Pay notes"></textarea>
                    </td>
                </tr>
            </tbody>
        </table>
	</div>
	<div class="wr-form-btns clearfix">
		<a title="Cancel" class="pull-right ml-10 btn btn-danger call-function" href="#" data-callback="closeFancyBox" data-message="Are you sure you want to close this window?">Cancel</a>
		<button class="pull-right btn btn-success" type="submit"><span class="ep-icon ep-icon_ok"></span> Send</button>
	</div>
</form>
<script type="text/javascript">
	function modalFormCallBack(form, data_table){
		var $form = $(form);
		$.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL ?>external_bills/ajax_external_bills_operation/add_request/special',
			data: $form.serialize(),
            beforeSend: function () {
                showLoader($form);
            },
            dataType: 'json',
			success: function(data){
				systemMessages( data.message, 'message-' + data.mess_type );

				if(data.mess_type == 'success'){
					closeFancyBox();
					data_table.fnDraw(false);
				}else{
					hideLoader($form);
				}
			}
        });
	}
    $(function(){
        $('input[name=type]').change(function(e){
            e.preventDefault();
            var type_for = $(this).val();
            var $form = $(this).closest('form');
            switch(type_for){
                case 'buyer':
                case 'seller':
                    $('tr.users_list').show();
                    $form.find('select[name=user]').html('<option value="">All users</option>');
                    $form.find('input[name=keywords]').val('');
                    $('tr.shippers_list').hide();
                break;
                default:
                    $('tr.users_list').hide();
                    $form.find('select[name=user]').html('<option value="">All users</option>');
                    $form.find('input[name=keywords]').val('');
                    $('tr.shippers_list').show();
                break;
            }
        });
        $('.search_by_type').click(function(e){
            e.preventDefault();
            var $form = $(this).closest('form');
            $.ajax({
                type: 'POST',
                url: '<?php echo __SITE_URL ?>external_bills/ajax_external_bills_operation/search_recipient',
                data: $form.serialize(),
                beforeSend: function () {
                    showLoader($form);
                },
                dataType: 'json',
                success: function(data){
                    hideLoader($form);
                    if(data.mess_type == 'success'){
                        var users = [];
                        users.push('<option>Select the user</option>');
                        $.each(data.users, function(index, user){
                            users.push('<option value="'+user.idu+'">'+user.user_name+' (Group: '+user.gr_name+'; Email: '+user.email+')</option>');
                        });
                        $('select[name=user]').html(users.join(''));
                    } else{
                        systemMessages( data.message, 'message-' + data.mess_type );
                        $('select[name=user]').html('<option value="">All users</option>');
                    }
                }
            });
        });
    });

</script>
