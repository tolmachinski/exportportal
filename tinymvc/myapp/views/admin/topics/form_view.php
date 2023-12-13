<div class="wr-modal-b">
	<form class="modal-b__form validateModal">
		<?php if(isset($topic)){?>
			<input type="hidden" name="id_topic" value="<?php echo $topic['id_topic']?>" />
		<?php }?>
		<div class="modal-b__content pb-0 w-900">
            <div class="row">
                <div class="col-xs-12"><!--title-->
                    <label class="modal-b__label">Title</label>
                    <input type="text" name="title_topic" class="form-control w-100pr validate[required,maxSize[100]]" value="<?php echo $topic['title_topic']?>" />
                </div>
                <div class="col-xs-12"><!--small text-->
                    <label class="modal-b__label">Small text</label>
					<textarea class="w-100pr h-100 validate[required,maxSize[200]]" name="text_topic_small"><?php echo $topic['text_topic_small']?></textarea>
                </div>
                <div class="col-xs-12"><!--text-->
                    <label class="modal-b__label">Text</label>
					<textarea id="text_block" class="w-100pr h-200 validate[required]" name="text_topic"><?php echo $topic['text_topic']?></textarea>
                </div>
            </div>
        </div>
        <div class="modal-b__btns clearfix"><!-- buttons -->
                <div class="col-xs-6"><!--visible-->
                    <label class="modal-b__label">Visible</label>
                    <div class="radio">
                        <label class="mr-10"> <input class="validate[required]" type="radio" name="visible" <?php echo checked($topic['visible_topic'], '1'); ?> value="1"> Yes</label>
                        <label><input class="validate[required]" type="radio" name="visible" <?php echo checked($topic['visible_topic'], '0'); ?> value="0"> No</label>
                    </div>
                </div>
            <button class="btn btn-success pull-right" type="submit">
                <span class="ep-icon ep-icon_ok"></span> Save
            </button>
        </div>
    </form>
</div>

<script type="text/javascript" src="<?php echo 'public/plug_admin/tinymce-4-3-10/tinymce.min.js'; ?>"></script>
<script type="text/javascript">
	tinymce.init({
		selector:'#text_block',
		menubar: false,
		statusbar : false,
		plugins: ["autolink lists link image textcolor code"],
		toolbar: "code bold italic underline forecolor backcolor | link image | numlist bullist",
		resize: false
	});

	function modalFormCallBack(form){
		var $form = $(form);
		var fdata = $form.serialize();
		var $wrform = $form.closest('.wr-modal-b');

		<?php if(!empty($topic)){?>
			var url = '<?php echo __SITE_URL;?>topics/ajax_topics_operation/edit_topic';
		<?php }else{?>
			var url = '<?php echo __SITE_URL;?>topics/ajax_topics_operation/add_topic';
		<?php }?>

		$.ajax({
			type: 'POST',
			url: url,
			data: fdata,
			dataType: 'JSON',
			beforeSend: function(){
				showFormLoader($wrform, 'Sending topic...');
				$form.find('button[type=submit]').addClass('disabled');
			},
			success: function(resp){
				hideFormLoader($wrform);
				systemMessages( resp.message, 'message-' + resp.mess_type );

				if(resp.mess_type == 'success'){
					callbackManageTopics(resp);
					closeFancyBox();
				}else{
					$form.find('button[type=submit]').removeClass('disabled');
				}
			}
		});
	}
</script>
