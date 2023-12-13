<div class="js-modal-flex wr-modal-flex inputs-40">
   <form
        class="modal-flex__form validateModal"
        data-callback="newPopupSaveSearchFormCallBack"
        data-js-action="save-search:form-submit"
    >
	   <div class="modal-flex__content">
	   		<p class="pt-10 pb-10">Please, write the description of the given search result</p>
			<p><?php echo $link;?></p>

			<label class="input-label input-label--required">Description</label>
			<textarea class="validate[required]" name="description" placeholder="Description"></textarea>
            <input type="hidden" name="link" value="<?php echo $link;?>"/>
            <input type="hidden" name="type" value="<?php echo $type;?>"/>
	   </div>
	   <div class="modal-flex__btns">
			<div class="modal-flex__btns-right">
				<button class="btn btn-primary" type="submit">Save</button>
	   		</div>
	   </div>
   </form>
</div>

<?php
    echo dispatchDynamicFragmentInCompatMode(
        "save-search:form-fragment",
        asset('public/plug/js/popups/save_search/index.js', 'legacy'),
        null,
        null,
        true
    );
?>
