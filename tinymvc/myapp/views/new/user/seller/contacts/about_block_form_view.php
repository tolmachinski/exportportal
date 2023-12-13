<div class="js-modal-flex wr-modal-flex inputs-40">
    <form
        class="modal-flex__form validateModal"
        data-callback="sellerAboutBlockFormCallBack"
        autocomplete="off"
    >
	   <div class="modal-flex__content">
			<label class="input-label input-label--required">Title block</label>
			<input class="validate[required, maxSize[50]]" type="text" name="title"  placeholder="Title block" value="<?php if(isset($block['title_block'])) { echo $block['title_block']; }?>"/>

			<label class="input-label input-label--required">Description block</label>
			<textarea class="validate[required]" id="add_about_text_block" name="text" placeholder="Write your text here"><?php if(isset($block['text_block'])) { echo $block['text_block']; }?></textarea>
            <?php if(isset($block['id_block'])){?>
			    <input type="hidden" name="block" value="<?php echo $block['id_block'];?>"/>
			<?php }?>
        </div>
        <div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" type="submit">Submit</button>
            </div>
        </div>
   </form>
</div>

<script>

$(function(){
	tinymce.remove('#add_about_text_block');
	tinymce.init({
		selector:'#add_about_text_block',
		menubar: false,
		statusbar : false,
		height : 250,
		plugins: [ "autolink lists link"],
		style_formats: [
			{title: 'H3', block: 'h3'},
			{title: 'H4', block: 'h4'},
			{title: 'H5', block: 'h5'},
			{title: 'H6', block: 'h6'},
		],
		toolbar: "styleselect | bold italic underline | link | numlist bullist | removeformat ",
		resize: false
	});
});

function sellerAboutBlockFormCallBack(form){
	var $form = $(form);
	var $wrform = $form.closest('.js-modal-flex');
	var fdata = $form.serialize();

	<?php if(isset($block['id_block'])){?>
	var url = 'seller_about/ajax_about_operation/edit_about_aditional_block';
	<?php }else{?>
	var url = 'seller_about/ajax_about_operation/add_about_block';
	<?php }?>

	$.ajax({
		type: 'POST',
		url: url,
		data: fdata,
		dataType: 'JSON',
		beforeSend: function(){
			showLoader($wrform);
			$form.find('button[type=submit]').addClass('disabled');
		},
		success: function(resp){
			hideLoader($wrform);
			systemMessages( resp.message, resp.mess_type );

			if(resp.mess_type == 'success'){
				closeFancyBox();

				<?php if(isset($block['id_block'])){?>
					callbackEditAboutBlock(resp);
				<?php }else{?>
					callbackAddAboutBlock();
				<?php }?>

			}else{
				$form.find('button[type=submit]').removeClass('disabled');
			}
		}
	});
}
</script>
