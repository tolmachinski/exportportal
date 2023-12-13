<div class="js-modal-flex wr-modal-flex">
	<form class="modal-flex__form validateModal inputs-40" data-callback="purchase_order_notes" id="purchase-order-discussion--form">
		<div class="modal-flex__content">
            <div class="overflow-y-a mh-350 mb-15">
                <?php views()->display('new/order/po_form/notes_list_view', array(
                    'timeline' => arrayGet($order, 'purchase_order_timeline', array()),
                )); ?>
            </div>

            <textarea class="validate[required,maxSize[1000]]" id="purchase_order_note--textcounter" data-max="1000" name="message" placeholder="Write your message here..."></textarea>
		</div>
		<div class="modal-flex__btns">
            <input type="hidden" name="id_order" value="<?php echo $order['id']; ?>" />

            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" type="submit">Send</button>
            </div>
		</div>
	</form>
</div>

<script>
    $(function(){
        var discussionTables = $('#purchase-order-discussion--form .main-data-table');
        var onSendNotes = function (form) {
            var $form = $(form);
            var fdata = $form.serialize();
            var loader_wrapper = $form.closest('.js-modal-flex');

            $.ajax({
                type: 'POST',
                url: '<?php echo __SITE_URL?>order/ajax_order_operations/purchase_order_notes',
                data: fdata,
                beforeSend: function(){
                    $form.find('button[type="submit"]').prop('disabled', true);
                    showLoader(loader_wrapper);
                },
                dataType: 'json',
                success: function(resp){
                    systemMessages( resp.message, resp.mess_type );

                    if (resp.mess_type == 'success') {
                        closeFancyBox();
                    } else{
                        $form.find('button[type="submit"]').prop('disabled', false);
                        hideLoader(loader_wrapper);
                    }
                }
            });
        };
        var normalizeTables = function (tables) {
			if(tables.length !== 0){
				if($(window).width() < 768) {
					tables.addClass('main-data-table--mobile');
				} else {
					tables.removeClass('main-data-table--mobile');
				}
			}
        };

        $('#purchase_order_note--textcounter').textcounter({
            countDown: true,
            countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
            countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'})
        });
        $('#purchase-order-discussion--form .modal-flex__content a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            var $currentTarget = $(e.target);
            if ($currentTarget.data('tab') == 'purchase_order-discuss') {
                $('.modal-flex__btns').css({'display': 'flex'});
            } else {
                $('.modal-flex__btns').hide();
            }

            $.fancybox.update();
        });

        mobileDataTable(discussionTables);
        normalizeTables(discussionTables);
        mix(window, { purchase_order_notes: onSendNotes }, false);
    });
</script>
