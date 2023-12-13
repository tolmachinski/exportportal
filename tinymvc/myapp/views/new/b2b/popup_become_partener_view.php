<div class="js-modal-flex wr-modal-flex">
	<form class="modal-flex__form validateModal" data-js-action="b2b:become-a-partner-form.submit">
		<div class="modal-flex__content">
			<label class="input-label input-label--required">Please, choose Your Company/Branch</label>
			<select class="validate[required]" name="company">
				<option value="">Select Company/Branch</option>
				<optgroup label="Company">
					<option value="<?php echo my_company_id();?>"><?php echo my_company_name();?></option>
				</optgroup>
				<?php if(!empty($branches)){?>
					<optgroup label="Branches">
					<?php foreach($branches as $branch){?>
						<option value="<?php echo $branch['id_company'];?>"><?php echo $branch['name_company'];?></option>
					<?php }?>
					</optgroup>
				<?php }?>
			</select>
			<label class="input-label input-label--required">Request message</label>
			<textarea class="validate[required] js-textcounter-message" data-max="1000" name="message" placeholder="Write your request message here"></textarea>
		</div>
		<div class="modal-flex__btns">
			<input type="hidden" name="request" value="<?php echo $id_request;?>" />

			<div class="modal-flex__btns-right">
				<button class="btn btn-primary" type="submit">Send</button>
			</div>
		</div>
	</form>
</div>

<?php echo dispatchDynamicFragment('lazy-loading:b2b-become-a-partner'); ?>

