<form method="post" class="validateModal relative-b">
   <div class="wr-form-content w-900 mt-10">
		<table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr vam-table">
			<tbody>
				<tr>
					<td class="w-200">
                        Email template
					</td>
					<td>
						<select name="email_template" class="w-100pr">
							<option value="">Select email type</option>
							<?php foreach($email_templates as $key => $email_template){?>
								<option value="<?php echo $key?>"><?php echo $email_template['title'];?></option>
							<?php }?>
						</select>
					</td>
					<td class="w-150 tac">
						<div class="btn-group">
							<button class="btn btn-primary call-function" data-callback="viewTemplate" data-user-type="seller" title="View Seller template"><i class="ep-icon ep-icon_visible txt-white m-0"></i></button>
							<button class="btn btn-success call-function" data-callback="viewTemplate" data-user-type="buyer" title="View Buyer template"><i class="ep-icon ep-icon_visible txt-white m-0"></i></button>
							<button class="btn btn-warning call-function" data-callback="viewTemplate" data-user-type="shipper" title="View Freight Forwarder template"><i class="ep-icon ep-icon_visible txt-white m-0"></i></button>
						</div>
					</td>
				</tr>
			</tbody>
		</table>

		<iframe id="view-template" src="" width="100%"></iframe>
	</div>
	<div class="wr-form-btns clearfix">
		<input type="hidden" name="users" value="<?php if(isset($id_user)){echo $id_user;}?>">
		<button class="pull-right btn btn-success" type="submit"><span class="ep-icon ep-icon_envelope"></span> Send emails</button>
	</div>
</form>
<script>
	function modalFormCallBack(form, data_table){
		var $form = $(form);

		<?php if(!isset($id_user)){?>
			var checked_users = [];

			$.each($('.check-user:checked'), function(){
				checked_users.push($(this).data('user'))
			});

			$form.find('input[name="users"]').val(checked_users.join());
		<?php }?>

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL ?>users/ajax_operations/send_multi_email',
			data: $form.serialize(),
			beforeSend: function () {
				showLoader($form);
			},
			dataType: 'json',
			success: function(data){
				systemMessages( data.message, 'message-' + data.mess_type );

				if(data.mess_type == 'success'){
					closeFancyBox();
					if(data_table != undefined)
						data_table.fnDraw(false);
				}else{
					hideLoader($form);
				}
			}
		});
	}

	function viewTemplate(obj){
		var $this = $(obj);
		var user_type = $this.data('user-type');
		var email = $('.wr-form-content').find('select[name="email_template"]').val();

		if(email.length){
			$("#view-template").attr("src", "users/view_email_template?user_type="+user_type+'&email='+email).css({'height': 700});
			$.fancybox.reposition();
		}else{
			systemMessages( 'Warning: Select email template.', 'message-warning' );
		}
	}
</script>
