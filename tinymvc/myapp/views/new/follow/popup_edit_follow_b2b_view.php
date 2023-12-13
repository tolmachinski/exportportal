<div class="wr-modal-flex">
   <form class="modal-flex__form validateModal" data-js-action="b2b:edit-follow-form.submit">
	   <div class="modal-flex__content">
			<label class="input-label input-label--required">Message</label>
			<textarea class="validate[required] js-textcounter-message" data-max="1000" name="message" placeholder="Message"><?php echo $follower['notice_follower'];?></textarea>
		</div>
		<div class="modal-flex__btns">
			<input type="hidden" name="follower" value="<?php echo $follower['id_follower'];?>"/>

			<div class="modal-flex__btns-right">
				<button class="btn btn-primary" type="submit">Send</button>
			</div>
		</div>
   </form>
</div>

<?php echo dispatchDynamicFragment('lazy-loading:b2b-partners-edit-follow'); ?>
