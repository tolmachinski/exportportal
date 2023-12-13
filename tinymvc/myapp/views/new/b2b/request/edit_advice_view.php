<div class="js-modal-flex wr-modal-flex inputs-40">
	<form class="modal-flex__form validateModal" data-js-action="b2b:edit-advice-form.submit">
		<div class="modal-flex__content">
			<label class="input-label">Message</label>
			<textarea class="validate[required] js-textcounter-message" data-max="1000" name="message" placeholder="Write your advice message here"><?php echo $advice['message_advice'];?></textarea>
            <input type="hidden" name="advice" value="<?php echo $advice['id_advice'];?>" />
		</div>
		<div class="modal-flex__btns">
			<div class="modal-flex__btns-right">
				<button class="btn btn-primary" type="submit">Submit</button>
			</div>
		</div>
	</form>
</div>

<?php echo dispatchDynamicFragment('lazy-loading:b2b-edit-advice'); ?>
