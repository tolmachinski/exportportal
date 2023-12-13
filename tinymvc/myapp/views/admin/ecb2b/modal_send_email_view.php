<form method="post" class="validateModal relative-b">
    <div class="wr-form-content pt-5 pb-5 w-700">
        <label class="modal-b__label">Subject</label>
        <input class="form-control validate[required]" type="text" name="subject" placeholder="Enter subject" />

        <label class="modal-b__label">Message</label>
        <textarea class="form-control validate[required]" type="text" name="message" placeholder="Enter message"></textarea>

        <div class="checkbox mt-20">
            <label>
                <input name="attach_document" checked type="checkbox"> Attach <?php echo $ecb2bRequest['type']; ?> document
                (<a target="_blank" href="<?php echo __SITE_URL . $file; ?>">open document</a>)
            </label>
        </div>
    </div>
    <div class="wr-form-btns clearfix">
        <input type="hidden" name="id" value="<?php echo $ecb2bRequest['id']; ?>">
        <button class="pull-right btn btn-default" type="submit">
            <span class="ep-icon ep-icon_ok"></span> Send
        </button>
    </div>
</form>

<script type="text/javascript">
    function modalFormCallBack($form, data_table){
        $.ajax({
            type: 'POST',
            url: 'ecb2b/send_email_action',
            data: $form.serialize(),
            beforeSend: function () {
                showLoader($form);
            },
            dataType: 'json',
            success: function(data) {
                systemMessages(data.message, 'message-' + data.mess_type);

                if(data.mess_type === 'success'){
                    closeFancyBox();
                    data_table && data_table.fnDraw();
                } else {
                    hideLoader($form);
                }
            }
        });
    }
</script>
