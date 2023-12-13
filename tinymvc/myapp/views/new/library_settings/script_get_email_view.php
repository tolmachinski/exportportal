<script>
    var get_email = function ($this) {
        $.ajax({
            url: '<?php echo __SITE_URL . $library_name;?>/get_email_by_id',
            type: 'POST',
            dataType: 'JSON',
            data: {
                item_id: $this.data('item_id')
            },
            success: function (resp) {
                if (resp.mess_type === 'success') {
                    systemMessages('Email: ' + resp.email, resp.mess_type);
                } else {
                    systemMessages(resp.message, resp.mess_type);
                }

            }
        });
    };
</script>
