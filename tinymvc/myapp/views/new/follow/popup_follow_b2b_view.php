<div class="js-modal-flex wr-modal-flex">
	<form class="modal-flex__form validateModal" data-js-action="b2b:follow-form.submit">
		<div class="modal-flex__content">
			<label class="input-label input-label--required">Message</label>
			<textarea class="validate[required] js-textcounter-message" data-max="1000" name="message" id="stf_message" placeholder="Message"></textarea>
		</div>
		<div class="modal-flex__btns">
			<input type="hidden" name="id" value="<?php echo $idRequest;?>"/>

			<div class="modal-flex__btns-right">
				<button class="btn btn-primary" type="submit">Send</button>
			</div>
		</div>
	</form>
</div>

<?php echo dispatchDynamicFragment('lazy-loading:b2b-actions-follow', ['idRequest' => $idRequest]); ?>
