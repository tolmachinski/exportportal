<div class="wr-modal-flex inputs-40">
	<form
        class="<?php if ($can_write) { ?>modal-flex__form<?php } ?> validateModal relative-b"
        data-callback="disputesMyNoticeFormCallBack"
    >
		<div class="modal-flex__content">
			<table id="js-table-dispute-timeline" class="main-data-table dataTable mt-15">
				<thead>
					<tr>
						<th class="w-130">Date</th>
						<th class="w-100">Member</th>
						<th class="mnw-100">Activity</th>
					</tr>
				</thead>
				<tbody class="tabMessage">
					<?php if(!empty($dispute['timeline'])){?>
						<?php foreach($dispute['timeline'] as $notice){?>
							<tr>
								<td data-title="Date"><?php echo $notice['add_date'];?></td>
								<td data-title="Member"><?php echo $notice['add_by'];?></td>
								<td data-title="Activity">
									<div class="grid-text">
										<div class="grid-text__item">
											<div class="txt-medium"><?php echo $notice['title'];?></div>
											<?php echo $notice['notice'];?>
										</div>
									</div>
								</td>
							</tr>
						<?php }?>
					<?php }?>
				</tbody>
			</table>
		</div>
		<?php if($can_write){?>
			<div class="modal-flex__btns">
				<input type="hidden" name="disput" value="<?php echo $dispute['id']?>" />

				<?php if($can_write){?>
					<textarea name="notice" class="validate[required,maxSize[500]] textcounter-dispute_notice" data-max="500"></textarea>
				<?php }?>

				<div class="modal-flex__btns-right mt-10">
					<button class="pull-right btn btn-primary" type="submit">Add notice</button>
				</div>
			</div>
		<?php }?>
	</form>
</div>
<?php if($can_write){?>
	<script>
	$(function(){
		var normalizeTables = function(tables) {
			if(tables.length !== 0){
				if($(window).width() < 768) {
					tables.addClass('main-data-table--mobile');
				} else {
					tables.removeClass('main-data-table--mobile');
				}
			}
        };

		$('.textcounter-dispute_notice').textcounter({
			countDown: true,
			countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
			countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'})
		});

		normalizeTables($('#js-table-dispute-timeline'));
	});

	function disputesMyNoticeFormCallBack(form){
		var $form = $(form);
			$.ajax( {
				dataType: "JSON",
				type: "POST",
				url: __group_site_url + "dispute/ajax_operation/add_notice",
				data: $form.serialize(),
				beforeSend: function(){
					showLoader(form);
				},
				success: function (resp) {
					if(resp.mess_type != 'error'){
						var template = '<tr>\
											<td>' + resp.add_date + '</td>\
											<td>' + resp.add_by + '</td>\
											<td>' + resp.title + '<br>' + resp.notice + '</td>\
										</tr>';
						$('.user-dispute-notices table.main-data-table .tabMessage').prepend(template);
						$form[0].reset();
					}
					systemMessages(resp.message, resp.mess_type);

					hideLoader(form);
				},

			});
	}
	</script>
<?php }?>
