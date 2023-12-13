<div class="wr-modal-b">
   <form class="modal-b__form validateModal" data-callback="make_sticker_form">
	   <div class="modal-b__content w-700 h-370">
			<label class="modal-b__label">From</label>
			<?php echo group_name_session();?> <a>&lt;<?php echo user_name_session();?>&gt;</a>

			<div class="row">
				<div class="col-xs-6">
					<label class="modal-b__label">Recipients</label>
					<div class="form-group">
						<select name="users[]" class="select-groups-list" multiple="multiple">
							<?php $prev_user_group = 0;
								foreach($users as $user){
								if($user['user_group'] != $prev_user_group){
									 if($prev_user_group != 0){?>
										</optgroup>
									<?php } ?>
									<optgroup label="<?php echo $users_group[$user['user_group']]['gr_name']?>">
								<?php }?>
									<option value="<?php echo $user['idu'];?>"><?php echo $user['fname'].' '.$user['lname'];?></option>
								<?php $prev_user_group = $user['user_group'];
								}?>
						</select>
					</div>
				</div>
				<div class="col-xs-6">
					<label class="modal-b__label">Type</label>
					<select class="validate[required]" name="type" >
						<option value="personal">Personal</option>
						<option value="important" <?php echo selected(group_session(), 16)?>>Important</option>
					</select>
				</div>
			</div>

			<label class="modal-b__label">Subject</label>
			<input class="validate[required]" type="text" name="subject" />

			<label class="modal-b__label">Message</label>
			<p id="max-length" >10 characters left</p>
			<textarea class="h-140 validate[required]" name="message" id="text-message"></textarea>
			<p class="pt-5 pull-left w-100pr">Recipients will get note about new sticker when they log in system <a href="#">(see what it looks like)</a>.</p>
	   </div>
	   <div class="modal-b__btns clearfix">
			<button class="btn btn-primary pull-right" type="submit"><i class="ep-icon ep-icon_ok"></i> Create sticker</button>
	   </div>
   </form>
</div>
<script type="text/javascript">
	$(document).ready(function() {
		//max charset
		var number = 500;

		$('#max-length').html(number + ' characters left');

		$('#text-message').blur(function() {
			var val = $(this).val();
			simbol(val, number);
		});// end blur

		$("#text-message").keyup(function () {
			var val = $(this).val();
			simbol(val, number);
		}); // end keyup
		var $selectGroups = $('.select-groups-list').multipleSelect({ 
			width: '100%', 
			placeholder: translate_js({plug:'multipleSelect', text: 'placeholder_users'}),
			selectAllText: translate_js({plug:'multipleSelect', text: 'select_all_text'}),
			allSelected: translate_js({plug:'multipleSelect', text: 'all_selected'}),
			countSelected: translate_js({plug:'multipleSelect', text: 'count_selected'}),
			noMatchesFound: translate_js({plug:'multipleSelect', text: 'no_matches_found'})
		});
	});

	function make_sticker_form(form){
    	var $form = $(form);
		var $wrform = $form.closest('.wr-modal-b');

		$.ajax({
			url: '<?php echo __SITE_URL?>sticker/ajax_sticker_operation/create_sticker',
			type: 'POST',
			data: $form.serialize(),
			dataType: 'JSON',
			beforeSend: function () {
				showFormLoader($wrform, 'Sending...');
			},
			dataType: 'JSON',
			success: function (resp) {
				if (resp.mess_type == 'success') {
					closeFancyBox();
					current_status = 'new';
					current_page = 1;
					search_keywords = '';
					resetSearchForm();
					$('.dashboard-sidebar-tree__subtree').find('a[data-status='+current_status+']')
						.parent('li').addClass('active').siblings().removeClass('active');
					loadStickers();
				} else{
					hideFormLoader($wrform);
				}
				systemMessages(resp.message, 'message-' + resp.mess_type);
			}
		});
	}
</script>
