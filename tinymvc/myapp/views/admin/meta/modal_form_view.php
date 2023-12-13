<form method="post" class="validateModal relative-b">
   <div class="wr-form-content w-700">
	<div>
		<table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr vam-table">
			<tbody>
				<tr>
					<td class="w-100">Page key</td>
					<td>
						<input id="page-key" class="validate[required] w-100pr" type="text" name="page_key" value="<?php echo $meta['page_key']?>" <?php echo ((isset($meta)) ? 'disabled="disabled"' : '')?>/>
					</td>
				</tr>
				<tr>
					<td class="w-100">Link</td>
					<td>
						<input class="validate[required] w-100pr" type="text" name="link" value="<?php echo $meta['link']?>"/>
					</td>
				</tr>
				<tr>
					<td class="w-100">Language</td>
					<td>
						<?php if(!isset($meta)){?>


						<div class="input-group">
							<select class="form-control" id="lang-select" name="id_lang">
								<?php foreach($translations as $lang){?>
									<option value="<?php echo $lang['id_lang'];?>" <?php echo selected($lang['id_lang'], $meta['id_lang']); ?>><?php echo $lang['lang_name'];?></option>
								<?php } ?>
							</select>
							<span class="input-group-btn">
								<button class="btn btn-success w-100 call-function" data-callback="verifayLang">Verify</button>
							</span>
						</div>

						<?php }else{ ?>
							<span class="lh-24">
								<img
                                    width="24"
                                    height="24"
                                    src="<?php echo getCountryFlag($meta['lang_icon']); ?>"
                                    alt="<?php echo $meta['lang_icon'];?>"
                                >
								<?php echo $meta['lang_name'];?>
							</span>
						<?php } ?>
					</td>
				</tr>
				<tr>
					<td class="w-100">Image</td>
					<td>
						<input class="w-100pr" type="text" name="image" value="<?php echo $meta['image']?>"/>
					</td>
				</tr>
				<tr>
					<td class="w-100">Image replace</td>
					<td>
						<?php if(!empty($meta['rules']['image'])){?>
							<?php foreach($meta['rules']['image'] as $rule_value){?>
							<input class="w-100pr" type="text" name="rules[image][image]" value="<?php echo $rule_value; ?>"/>
							<?php }?>
						<?php }else{?>
							<input class="w-100pr" type="text" name="rules[image][image]" value=""/>
						<?php }?>
					</td>
				</tr>
			</tbody>
		</table>

		<ul class="nav nav-tabs clearfix" role="tablist">
			<li role="presentation" class="active"><a href="#title" aria-controls="title" role="tab" data-toggle="tab">Title</a></li>
			<li role="presentation"><a href="#description" aria-controls="description" role="tab" data-toggle="tab">Description</a></li>
			<li role="presentation"><a href="#keywords" aria-controls="keywords" role="tab" data-toggle="tab">Keywords</a></li>
		</ul>

	  <!-- Tab panes -->
		<div class="tab-content">
			<div role="tabpanel" class="tab-pane active meta-title-block" id="title">
				<table cellspacing="0" cellpadding="0" class="data table-bordered table-striped w-100pr vam-table">
					<tbody>
						<tr>
							<td class="w-100">Preview</td>
							<td class="preview h-100">

							</td>
						</tr>
						<tr>
							<td>
								Template for title
								<div class="fs-14 txt-blue tac" id="count-title"><?php if(isset($meta['title'])){ echo strlen($meta['title']); }?></div>
							</td>
							<td>
								<textarea class="call-count-characters validate[required] w-100pr h-150" data-count="count-title" name="title"><?php echo ((isset($meta['title']) ? $meta['title'] : ''))?></textarea>
								<p>In the main template each key enclosed in [square brackets] will be replaced to the value from the list "replace" accordingly.</p>
							</td>
						</tr>
						<tr>
							<td>Replace</td>
							<td>
								<table class="data replace-values table-striped table-bordered">
									<thead>
										<tr class="value-container">
											<th class="w-50">Visible</th>
											<th class="w-180">Key</th>
											<th class="w-280">Value</th>
											<th class="w-70">Actions</th>
										</tr>
									</thead>
									<tbody>
									<?php if(!empty($meta['rules']['title'])){
										foreach($meta['rules']['title'] as $rule_name => $rule_value){?>
										<tr class="value-container">
											<td class="tac w-50 rule-visible">
												<input type="checkbox" checked="checked" class="check-visible"/>
											</td>
											<td class="lh-30 w-180 rule-key"><?php echo $rule_name?></td>
											<td class="w-280 rule-value">
												<input data-key="<?php echo $rule_name?>" class="w-100pr meta-rules validate[required]" type="text" name="rules[title]<?php echo $rule_name?>" value="<?php echo $rule_value?>"/>
											</td>
											<td class="tac lh-30 w-70 rule-actions">
												<a class="ep-icon ep-icon_remove txt-gray remove-value"></a>
											</td>
										</tr>
										<?php }
									}?>
									</tbody>
								</table>
								<a class="add-value ep-icon ep-icon_plus pull-right mt-10"></a>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div role="tabpanel" class="tab-pane meta-description-block" id="description">
				<table cellspacing="0" cellpadding="0" class="data table-bordered table-striped w-100pr vam-table">
					<tbody>
						<tr>
							<td class="w-100">Preview</td>
							<td class="preview h-100">

							</td>
						</tr>
						<tr>
							<td>
								Template for description
								<div class="fs-14 txt-blue tac" id="count-description"><?php if(isset($meta['description'])){ echo strlen($meta['description']); }?></div>
							</td>
							<td>
								<textarea class="call-count-characters validate[required] w-100pr h-150" data-count="count-description" name="description"><?php echo ((isset($meta['description']) ? $meta['description'] : ''))?></textarea>
								<p>In the main template each key enclosed in [square brackets] will be replaced to the value from the list "replace" accordingly.</p>
							</td>
						</tr>
						<tr>
							<td>Replace</td>
							<td>
								<table class="replace-values data table-bordered table-striped">
									<thead>
										<tr class="value-container">
											<th class="w-50">Visible</th>
											<th class="w-180">Key</th>
											<th class="w-280">Value</th>
											<th class="w-70">Actions</th>
										</tr>
									</thead>
									<tbody>
									<?php if(!empty($meta['rules']['description'])){
										foreach($meta['rules']['description'] as $rule_name => $rule_value){?>
										<tr class="value-container">
											<td class="tac w-50 rule-visible">
												<input type="checkbox" checked="checked" class="w-100pr check-visible"/>
											</td>
											<td class="lh-30 w-180 rule-key"><?php echo $rule_name?></td>
											<td class="w-280 rule-value">
												<input data-key="<?php echo $rule_name?>" class="w-100pr meta-rules validate[required]" type="text" name="rules[description]<?php echo $rule_name?>" value="<?php echo $rule_value?>"/>
											</td>
											<td class="tac lh-30 w-70 rule-actions">
												<a class="ep-icon ep-icon_remove txt-gray remove-value"></a>
											</td>
										</tr>
										<?php }
									}?>
									</tbody>
								</table>
								<a class="add-value ep-icon ep-icon_plus pull-right mt-10"></a>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div role="tabpanel" class="tab-pane meta-keywords-block" id="keywords">
				<table cellspacing="0" cellpadding="0" class="data table-bordered table-striped w-100pr vam-table">
					<tbody>
						<tr>
							<td class="w-100">Preview</td>
							<td class="preview h-100">

							</td>
						</tr>
						<tr>
							<td>
								Template for keywords
								<div class="fs-14 txt-blue tac" id="count-keywords"><?php if(isset($meta['keywords'])){ echo strlen($meta['keywords']); }?></div>
							</td>
							<td>
								<textarea class="call-count-characters w-100pr h-150" data-count="count-keywords" name="keywords"><?php echo ((isset($meta['keywords']) ? $meta['keywords'] : ''))?></textarea>
								<p>In the main template each key enclosed in [square brackets] will be replaced to the value from the list "replace" accordingly.</p>
							</td>
						</tr>
						<tr>
							<td>Replace</td>
							<td>
								<table class="replace-values data table-striped table-bordered">
									<thead>
										<tr class="value-container">
											<th class="w-50">Visible</th>
											<th class="w-180">Key</th>
											<th class="w-280">Value</th>
											<th class="w-70">Actions</th>
										</tr>
									</thead>
									<tbody>
									<?php if(!empty($meta['rules']['keywords'])){
										foreach($meta['rules']['keywords'] as $rule_name => $rule_value){?>
										<tr class="value-container">
											<td class="tac w-50 rule-visible">
												<input type="checkbox" checked="checked" class="check-visible"/>
											</td>
											<td class="lh-30 w-180 rule-key"><?php echo $rule_name?></td>
											<td class="w-280 rule-value">
												<input data-key="<?php echo $rule_name?>" class="w-100pr meta-rules validate[required]" type="text" name="rules[keywords]<?php echo $rule_name?>" value="<?php echo $rule_value?>"/>
											</td>
											<td class="tac lh-30 w-70 rule-actions">
												<a class="ep-icon ep-icon_remove txt-gray remove-value"></a>
											</td>
										</tr>
										<?php }
									}?>
									</tbody>
								</table>
								<a class="add-value ep-icon ep-icon_plus pull-right mt-10"></a>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>

	</div>
	<div class="wr-form-btns clearfix">
		<?php if(isset($meta['id'])){?><input type="hidden" name="id" value="<?php echo $meta['id'];?>"/><?php }?>
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>
<script>
function renew_preview($block){
	var result = $block.find('textarea').val();
	var clear_list = [];

	$.each($block.find('.replace-values input[type="text"]'), function(){
		var cur_elem = $(this);
		var key = cur_elem.data('key').toString();
		console.log(key);
		if(cur_elem.closest('.value-container').find('.check-visible').is(':checked')){
			key = key.replace('[', "\\[");
			key = key.replace(']', "\\]");

			var regex = new RegExp(key, 'g');
			result = result.replace(regex, '<b>'+cur_elem.val()+'</b>');
		}else{
			clear_list.push(key);
		}
	});

	if(clear_list.length > 0){
		$.each(clear_list, function(index, value){
			result = result.replace(value, '');
		});
	}

	$block.find('.preview').html(result);
}

$('.wr-form-content').on('keyup', '.meta-rules', function(){
	renew_preview($(this).closest('.tab-pane'));
});

$('.wr-form-content').on('keyup', 'textarea', function(){
	renew_preview($(this).closest('.tab-pane'));
});

$('.wr-form-content').on('keyup', 'textarea', function(){
	renew_preview($(this).closest('.tab-pane'));
});

renew_preview($('.meta-title-block'));
renew_preview($('.meta-description-block'));
renew_preview($('.meta-keywords-block'));

/****** ******/

$('.wr-form-content').on('click', '.remove-value', function(){
	var $this = $(this);
	var $cur_tab = $this.closest('.tab-pane');
	$this.closest('.value-container').remove();
	renew_preview($cur_tab);
});

$('.wr-form-content').on('click', '.confirm-rule', function(){
	var $this = $(this);
	var $cur_tab = $this.closest('.tab-pane');
	var $key_container = $this.closest('.value-container').find('.rule-key');
	var $meta_rules = $this.closest('.value-container').find('.meta-rules');
	var key_value = $key_container.find('input').val();

	$meta_rules.attr('name', $meta_rules.attr('name')+key_value).data('key', key_value);
	$this.closest('.value-container').find('.rule-visible').html('<input type="checkbox" checked="checked" class="check-visible"/>');
	$this.closest('.value-container').find('.rule-actions').html('<a class="ep-icon ep-icon_remove txt-gray remove-value"></a>');
	$key_container.html(key_value);
	renew_preview($cur_tab);
});

$('.wr-form-content').on('change', '.check-visible', function(){
	renew_preview($(this).closest('.tab-pane'));
});

$('.add-value').on('click', function(){
	var cur_tab_id = $(this).closest('.tab-pane').attr('id');
	var input_str = '<tr class="value-container">\
						<td class="tac rule-visible w-50"></td>\
						<td class="rule-key w-180"><input type="text" class="w-100pr value-key validate[required]"></td>\
						<td class="rule-value w-280">\
							<input class="w-100pr pull-right meta-rules validate[required]" type="text" name="rules['+cur_tab_id+']">\
						</td>\
						<td class="tac rule-actions w-70">\
							<a class="ep-icon ep-icon_ok txt-green confirm-rule"></a>\
							<a class="ep-icon ep-icon_remove txt-gray remove-value"></a>\
						</td>\
					</tr>';
	$(this).parent().find('.replace-values tbody').append(input_str);
});

function modalFormCallBack(form, data_table){
	if($('.confirm-rule').length > 0){
		systemMessages( 'Error: You must save all replace keys.', 'message-error' );
		return false;
	}

	var $form = $(form);
	$.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL ?>meta/ajax_meta_operations/<?php echo ((isset($meta) ? "edit" : "add"))?>_meta',
		data: $form.serialize(),
		beforeSend: function () {
			showLoader($form);
		},
		dataType: 'json',
		success: function(data){
			systemMessages( data.message, 'message-' + data.mess_type );
			hideLoader($form);

			if(data.mess_type == 'success'){
				closeFancyBox();
				if(data_table != undefined)
					data_table.fnDraw(false);
			}else{

			}
		}
	});
}

var verifayLang = function(obj){
	var $this = $(obj);
	var lang = $('#lang-select').val();
	var page_key = $('#page-key').val();

	if(page_key != ""){
		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL?>meta/ajax_meta_operations/verify_lang',
			data: { lang : lang, page_key: page_key},
			beforeSend: function(){ },
			dataType: 'json',
			success: function(resp){
				systemMessages( resp.message, 'message-' + resp.mess_type );

				if(resp.mess_type == 'success'){

				}
			}
		});
	}else{
		systemMessages( "Error: Page key is empty.", 'message-error');
	}


}
</script>
