<div class="js-modal-flex wr-modal-flex inputs-40">
	<div class="modal-flex__form">
		<div class="modal-flex__content updateValidationErrorsPosition">
			<div>
				 <strong>Date:</strong><?php echo formatDate($request_info['date_create']);?>
			</div>
			<div>
				<strong>Extend for:</strong> <?php echo $request_info['days'];?> day(s).
			</div>
			<div>
				<strong>Reason:</strong> <?php echo $request_info['extend_reason'];?>
			</div>
		</div>
		<div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <a class="btn btn-danger confirm-dialog" data-message="<?php echo translate('systmess_bill_cancel_extend_request_question_message', null, true);?>" href="#" data-callback="cancel_extend" data-action="decline_user" data-extend="<?php echo $request_info['id_extend'];?>">Cancel request</a>
            </div>
		</div>
	</div>
</div>
<script>
	var cancel_extend = function(opener){
        var $this = $(opener);
        var extend = $this.data('extend');
        var action = $this.data('action');
        $.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL;?>extend/ajax_operation/'+action,
            data: {extend:extend},
            dataType: 'JSON',
            success: function(resp){
                systemMessages( resp.message, resp.mess_type );

                if(resp.mess_type == 'success'){
                    closeFancyBox();
                    cancel_extend_callback(resp);
                }
            }
        });
    }
</script>
