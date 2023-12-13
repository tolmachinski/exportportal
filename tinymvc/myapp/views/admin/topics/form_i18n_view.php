<div class="wr-modal-b">
    <form method="post" class="validateModal relative-b">
		<?php if(isset($topic_i18n)){?>
			<input type="hidden" name="id_topic_i18n" value="<?php echo $topic_i18n['id_topic_i18n'];?>" />
		<?php } else{?>
			<input type="hidden" name="id_topic" value="<?php echo $topic['id_topic'];?>" />
		<?php }?>
		<div class="modal-b__content pb-0 w-900 mh-700">
            <div class="row">
                <div class="col-xs-12 initial-b">
                    <label class="modal-b__label">Language</label>
					<?php if(empty($topic_i18n)){?>
						<?php $translations_data = json_decode($topic['translations_data'], true);?>
						<select class="form-control validate[required]" name="id_lang">
                            <option value="">Select Language</option>
							<?php foreach($tlanguages as $lang){?>
								<option value="<?php echo $lang['id_lang'];?>" <?php if(array_key_exists($lang['lang_iso2'], $translations_data)){echo 'disabled';}?>><?php echo $lang['lang_name'];?></option>
							<?php } ?>
						</select>
					<?php } else {?>
						<div class="form-control mb-10"><?php echo $lang_block['lang_name'];?></div>
					<?php }?>
                </div>

                <div class="col-xs-12"> <!--title-->
                    <label class="modal-b__label">Title</label>
                    <div class="form-control mb-10"><?php echo $topic['title_topic']?></div>
                    <input type="text" name="title_topic" class="form-control validate[required,maxSize[100]]" value="<?php echo (!empty($topic_i18n)) ? $topic_i18n['title_topic'] : ''?>" />
                </div>
                <div class="col-xs-12"> <!--Small text-->
                    <label class="modal-b__label">Small Text</label>
					<div class="form-control w-100pr h-100 mb-10"><?php echo $topic['text_topic_small']?></div>
					<textarea class="form-control w-100pr h-100 validate[required,maxSize[200]]" name="text_topic_small"><?php echo (!empty($topic_i18n)) ? $topic_i18n['text_topic_small'] : ''?></textarea>
                </div>
                <div class="col-xs-12"> <!--text-->
                    <label class="modal-b__label">Text</label>
					<textarea id="text_block" class="form-control w-100pr h-200 mb-10" disabled><?php echo $topic['text_topic']?></textarea>
					<textarea id="text_block1" class="form-control w-100pr h-200 validate[required]" name="text_topic"><?php echo (!empty($topic_i18n)) ? $topic_i18n['text_topic'] : ''?></textarea>
                </div>
            </div>
        </div>
        <div class="modal-b__btns clearfix">
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
        toolbar: false,
		resize: false,
        readonly: true
	});

	tinymce.init({
		selector:'#text_block1',
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

		<?php if(!empty($topic_i18n)){?>
			var url = '<?php echo __SITE_URL;?>topics/ajax_topics_operation/edit_topic_i18n';
		<?php }else{?>
			var url = '<?php echo __SITE_URL;?>topics/ajax_topics_operation/add_topic_i18n';
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
