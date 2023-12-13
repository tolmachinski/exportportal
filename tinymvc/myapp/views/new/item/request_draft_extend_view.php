<div class="js-modal-flex wr-modal-flex">
	<form id="requestForm" class="modal-flex__form validateModal inputs-40"  data-callback="createExtendRequest" autocomplete="off">
		<div class="modal-flex__content updateValidationErrorsPosition">
			<div>
				<strong>Expire on: <?php echo getDateFormat($request['expiration_date'] ?? date_plus('10'), null, 'm/d/Y');?></strong>
			</div>

			<label class="input-label input-label--required">Extend until:</label>
			<input
                <?php echo addQaUniqueIdentifier('items-my__add-extend-request__date-input'); ?>
                id="extendDate"
                class="form-control js-datepicker-validate validate[required] datepicker-init"
                type="text"
                name="extend_date"
                placeholder="Extend date"
                readonly>

			<label class="input-label input-label--required">Write the reason:</label>
			<textarea
                <?php echo addQaUniqueIdentifier('items-my__add-extend-request__reason-textarea'); ?>
                class="validate[required] textcounter_extend-request"
                data-max="500" name="extend_reason"
                placeholder="Extend reason"></textarea>

            <input type="hidden" name="id" value="<?php echo isset($request) ? $request['id'] : '';?>"/>
		</div>
		<div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button
                    <?php echo addQaUniqueIdentifier('items-my__add-extend-request__submit-button'); ?>
                    class="btn btn-primary"

                    type="submit">Submit request</button>
            </div>
		</div>
	</form>
</div>
<script>
	$(function(){
        $("#extendDate").datepicker({
            beforeShow: function (input, instance) {
                $('#ui-datepicker-div').addClass('dtfilter-ui-datepicker');
            },
            minDate: "<?php echo $expireMin; ?>",//"+30d",
            maxDate: "<?php echo $expireMax; ?>"
        });

		$('.textcounter_extend-request').textcounter({
			countDown: true,
			countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
			countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'})
		});
	});

	var createExtendRequest = function(form){
		var form = $(form);
        var wrapper = form.closest('.js-modal-flex');
        var url = '<?php echo __SITE_URL;?>items/ajax_item_operation/draft_extend_request';

        var beforeSend = function() {
            showLoader(wrapper);
        };

        var onRequestEnd = function(resp) {
            console.log(resp);
            if(resp.mess_type == 'success'){
                closeFancyBox();
            }
            systemMessages(resp.message, resp.mess_type );
        };

        beforeSend();
        postRequest(url, form.serialize())
            .then(onRequestEnd)
            .catch(onRequestError)
            .finally(function () {
                hideLoader(wrapper);
            });

	}
</script>
