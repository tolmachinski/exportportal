<?php if (logged_in()) { ?>
    <script>
        remove_shipper_company = function (opener) {
            var $this = $(opener);
            $.ajax({
                url: 'shipper/ajax_shipper_operation/remove_shipper_saved',
                type: 'POST',
                dataType: 'JSON',
                data: {company: $this.data('company')},
                success: function (resp) {
                    systemMessages(resp.message, resp.mess_type);
                    if (resp.mess_type === 'success') {
                        $this.data('callback', 'add_shipper_company').html('<i class="ep-icon ep-icon_favorite-empty"></i> Save');
                    }
                }
            });
        };

        add_shipper_company = function (opener) {
            var $this = $(opener);
            $.ajax({
                url: 'shipper/ajax_shipper_operation/add_shipper_saved',
                type: 'POST',
                dataType: 'JSON',
                data: {company: $this.data('company')},
                success: function (resp) {
                    systemMessages(resp.message, resp.mess_type);
                    if (resp.mess_type === 'success') {
                        $this.data('callback', 'remove_shipper_company').html('<i class="ep-icon ep-icon_favorite"></i> Unsave');
                    }
                }
            });
        };

        var makeShipperPartner = function (obj) {
            var $this = $(obj);
            var shipper = $this.data('shipper');

            $.ajax({
                type: 'POST',
                url: '<?php echo __SITE_URL?>shippers/ajax_shippers_operation/partnership',
                data: {shipper: shipper},
                beforeSend: function () {
                },
                dataType: 'json',
                success: function (resp) {
                    systemMessages(resp.message, resp.mess_type);

                    if (resp.mess_type === 'success') {
                        $this.toggleClass('call-function not-call-function').addClass('txt-gray-light').html('<i class="ep-icon ep-icon_hourglass-processing"></i> Request sent');
                    }
                }
            });
        };
    </script>
<?php } ?>

<div class="mb-15">
    <iframe class=" bd-none" style="width: 100%; height: 400px;" src="https://www.youtube.com/embed/2DAiFDHlsOs?controls=0&showinfo=0" allowfullscreen></iframe>
</div>

<a class="btn btn-primary btn-panel-left mb-25 fancyboxSidebar fancybox" data-title="Category" href="#main-flex-card__fixed-left">
	<i class="ep-icon ep-icon_items"></i>
	Sidebar
</a>

<div class="clearfix pb-15 pt-3">
    <?php tmvc::instance()->controller->view->display('new/search_counter_view'); ?>
    <?php tmvc::instance()->controller->view->display('new/shippers/directory/nav_list_grid_category_view'); ?>
</div>


<?php tmvc::instance()->controller->view->display('new/shippers/directory/list_view'); ?>

<div class="pt-10 clearfix">
    <?php tmvc::instance()->controller->view->display('new/paginator_view'); ?>
</div>