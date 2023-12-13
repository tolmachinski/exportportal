<div class="wr-modal-flex inputs-40">
    <div class="modal-flex__form">
        <div class="modal-flex__content">
            <table id="js-admin-due-date-container" class="table-striped table-bordered w-100pr vam-table">
            <?php foreach ($recipients as $key => $recipient) { ?>
                <tr>
                    <td>
                        <span><?php echo $recipient['routing_order']; ?>.</span>
                        <span><?php echo $assignees[$recipient['id_user']]['name']; ?></span>
                    </td>
                    <td><?php echo translate("order_documents_recipient_types_{$recipient['type']}_list_option_text"); ?></td>
                    <td><input class="form-control js-due-date-datepicker"
                            data-id="<?php echo $recipient['id']; ?>"
                            type="text"
                            placeholder="Due Date"
                            value="<?php echo !empty($recipient['due_date']) ? $recipient['due_date']->format('m/d/Y') : ''; ?>"
                    ></td>
            </tr>
            <?php }?>
            </table>
        </div>
        <button
            type="submit"
            class="btn btn-primary w-150 call-function"
            data-callback="modalFormCallBack"
        >Save</button>
    </div>
</div>
<script>

    $(function () {
        getScript('<?php echo asset('public/plug_admin/js/documents/orders/due-dates.js', 'legacy'); ?>', true).then(function () {
            ExtendDueDateModule.default(
                <?php echo ((int) $envelope['id'] ?? null) ?: null; ?>,
                <?php echo json_encode(
                    $selectors = [
                        'datepicker' => ".js-due-date-datepicker",
                        'table'      => "#js-admin-due-date-container",
                    ]
                ); ?>,
                "<?php echo $url ?? ''; ?>",
                <?php echo (int) config('envelope_document_max_calendar_days', 60); ?>
            );
        });
    });
</script>
