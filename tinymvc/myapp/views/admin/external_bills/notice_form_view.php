<?php if(!empty($external_bill['notice'])){?>
<form class="validateModal relative-b">
	<div class="wr-form-content w-600">
        <table cellspacing="0" cellpadding="0" class="table table-bordered table-striped table-hover mt-15 m-auto vam-table">
            <tbody>
                <tr>
                    <td>
                        <div class="user-notices w-100pr mh-250 overflow-y-a">
                            <ul>
                                <?php foreach ($external_bill['notice'] as $notice) {
                                    if(!empty($notice)){?>
                                        <li class="pb-5 pt-5 bdb-1-gray lh-16 txt-blue">
                                            <div class="clearfix">
                                                <strong class="pull-left">by <?php echo $notice['add_by'] ?></strong>
                                                <strong class="pull-right"><?php echo $notice['add_date'] ?></strong>
                                            </div>
                                            <div class="clearfix">
                                                <?php echo $notice['title'] ?><br/>
                                                <?php echo $notice['notice'] ?>
                                            </div>
                                        </li>
                                    <?php }
                                    }?>
                            </ul>
                        </div>
                    </td>
                </tr>
                <?php if($external_bill['status'] == 'waiting'){?>
                <tr>
                    <td>
                        <textarea name="notice" class="validate[required] w-100pr h-100"></textarea>

                    </td>
                </tr>
                <?php }?>
            <tbody>
        </table>
	</div>
    <?php if($external_bill['status'] == 'waiting'){?>
        <div class="wr-form-btns clearfix">
            <?php if(!empty($return_to_modal_url)){?>
                <a class="pull-left btn btn-default fancybox fancybox.ajax" href="<?php echo $return_to_modal_url;?>" data-title="All bills">
                    <i class="ep-icon ep-icon_arrows-left "></i> Go back
                </a>
            <?php }?>
            <input type="hidden" name="id" value="<?php echo $external_bill['id']?>" />
            <button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Add notice</button>
        </div>
    <?php }?>
</form>
<script>
function modalFormCallBack(form){
	var $form = $(form);
		$.ajax( {
			dataType: "JSON",
			type: "POST",
			url: "<?php echo __SITE_URL ?>external_bills/ajax_external_bills_operation/add_notice",
			data: $form.serialize(),
			beforeSend: function(){
				showLoader(form);
			},
			success: function (json) {
				if(json.mess_type != 'error'){
					closeFancyBox();
					if($('.user-notices ul').length == 0){
						$('.user-notices').html('<ul></ul>');
					}
					$('.user-notices ul').prepend('<li class="pb-5 pt-5 bdb-1-gray lh-16 txt-blue"><strong>' + json.add_date + '</strong> - <u>by ' + json.add_by + '</u> : ' + json.notice + '</ul>');
					$form[0].reset();
				}
				systemMessages(json.message, 'message-' + json.mess_type);
				hideLoader(form);
			},

		});
}
</script>
<?php }else{ ?>
    <div class="alert alert-info mb-0">There are no notices for this external bill request.</div>
<?php } ?>
