<div class="js-modal-flex wr-modal-flex inputs-40">
	<form class="modal-flex__form validateModal" data-callback="date_for_pickup">
		<div class="modal-flex__content">
			<label class="input-label">Date for pick-up</label>
			<input class="js-pickup-datepicker js-datepicker-validate validate[required]" type="text" name="pickup_date" autocomplete="pickup-date" readonly>
            <input type="hidden" name="order" value="<?php echo $order_info['id'];?>"/>
		</div>
		<div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" type="submit">Save</button>
            </div>
		</div>
	</form>
</div>
<script>
    $(document).ready(function(){
        var dateToday = new Date();
		dateToday.setDate(dateToday.getDate() + 1);

		$(".js-pickup-datepicker").datepicker({
			minDate: dateToday,
			beforeShow: function (input, instance) {
				$('#ui-datepicker-div').addClass('dtfilter-ui-datepicker');
			},
		});
    });

	var date_for_pickup = function(form){
		var $form = $(form);
		var $wrapper = $form.closest('.js-modal-flex');

		$.ajax({
			type: 'POST',
			url: '<?php echo getUrlForGroup('order/ajax_order_operations/confirm_ready_pickup');?>',
			data: $form.serialize(),
			beforeSend: function(){ showLoader($wrapper); },
			dataType: 'json',
			success: function(resp){
				if(resp.mess_type == 'success'){
					showOrder(resp.order);
				    current_status = 'shipping_ready_for_pickup';
                    current_page = 1;
					loadOrderList(true);
					update_status_counter_active('Ready for pickup', current_status);
					closeFancyBox();
				}
				hideLoader($wrapper);
				systemMessages( resp.message, resp.mess_type );
			}
		});
	}
</script>
