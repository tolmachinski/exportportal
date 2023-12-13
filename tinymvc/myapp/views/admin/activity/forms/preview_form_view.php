<div class="wr-modal-b">
    <div class="modal-b__content pb-0 w-900">
        <div class="row">
            <div class="col-xs-12 mb-15">
                <table cellspacing="0" cellpadding="0" class="data table-striped table-bordered mt-5 w-100pr vam-table">
                    <tbody>
                        <tr>
                            <td class="w-20pr tac">
                                <strong>Level</strong>
                                <br>
                                <span class="fs-12 label label-<?php echo $label_color; ?>"><?php echo $level; ?></span>
                            </td>
                            <td class="w-20pr tac">
                                <strong>Resource</strong>
                                <?php if(null !== $resource['name'])  { ?>
                                    <div>
                                        <a href="<?php echo $resource['url']; ?>" target="_blank"><?php echo $resource['name']; ?></a>
                                    </div>
                                <?php } else { ?>
                                    <div>-</div>
                                <?php } ?>
                            </td>
                            <td class="w-20pr tac">
                                <strong>Resource type</strong>
                                <?php if(!empty($resource['type']['name']))  { ?>
                                    <div><?php echo $resource['type']['name']; ?></div>
                                <?php } else { ?>
                                    <div>-</div>
                                <?php } ?>
                            </td>
                            <td class="w-20pr tac">
                                <strong>Operation</strong>
                                <?php if(!empty($operation['name']))  { ?>
                                    <div><?php echo $operation['name']; ?></div>
                                <?php } else { ?>
                                    <div>-</div>
                                <?php } ?>
                            </td>
                            <td class="w-20pr tac">
                                <strong>Date</strong>
                                <br>
                                <?php echo $datetime->format('m/d/Y h:i:s A'); ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 mb-15">
                <div class="form-control"><?php echo $message; ?></div>
            </div>
        </div>
        <?php if (null !== $view_path) { ?>
            <?php tmvc::instance()->controller->view->display($view_path); ?>
        <?php } ?>
    </div>
    <div class="modal-b__btns clearfix">
        <button type="submit"
            id="preview-activity--action--mark-viewed"
            class="btn btn-success pull-right"
            <?php echo $is_viewed ? 'disabled' : "data-url=\"{$view_url}\""; ?>
            data-table="dtActivity">
            <span class="ep-icon ep-icon_ok"></span> Mark as viewed
        </button>
    </div>
</div>
<script type="application/javascript">
    $(function(){
        var viewButton = $('#preview-activity--action--mark-viewed');
        var markAsViewed = function(e) {
            e.preventDefault();

            var self = $(this);
            var modal = self.closest('.wr-modal-b');
            var dataGridName = self.data('table') || null;
            var dataGrid = null !== dataGridName && dataGridName in window ? window[dataGridName] : null;
            var url = self.data('url') || null;
            var onRequestSuccess = function(response){
                systemMessages(response.message, 'message-' + response.mess_type);
                if(response.mess_type == 'success'){
                    closeFancyBox();
                    if(dataGrid) {
                        $(dataGrid).DataTable().draw(false);
                    }
                }
            };
            if(null === url) {
                return;
            }

            showLoader(modal);
            $.post(url, null, null, 'json').done(onRequestSuccess).fail(onRequestError).always(function(){
                hideLoader(modal);
            });
        }

        viewButton.on('click', markAsViewed);
    });
</script>