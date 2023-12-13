<?php
$wordsList = array();
foreach ($words as $word) {
    $wordsList[] = $word['word'];
}
?>
<div class="wr-modal-b">
    <form class="modal-b__form validateModal" id="bad-words-form">
        <div class="modal-b__content w-700">
            <?php if ($language === false) { ?>
                <div class="mb-20 clearfix">
                    <label class="modal-b__label">Language</label>
                    <select class="validate[required]" name="language">
                        <option value="">Select a language</option>
                        <?php foreach ($languageList as $languageItem) { ?>
                            <option value="<?php echo $languageItem['lang_iso2']; ?>"><?php echo $languageItem['lang_name']; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <input type="hidden" name="action" value="add">
            <?php } else { ?>
                <input type="hidden" name="language" value="<?php echo $language; ?>">
                <input type="hidden" name="action" value="edit">
            <?php } ?>
            <div class="mb-20 clearfix">
                <label class="modal-b__label">Bad words</label>
                <textarea class="validate[required]" name="words" rows="10"><?php echo implode(', ', $wordsList); ?></textarea>
            </div>
        </div>

        <div class="modal-b__btns clearfix">
            <button class="btn btn-primary pull-right" type="submit"><i class="ep-icon ep-icon_ok"></i> Save</button>
        </div>
    </form>
</div>

<script>
    function modalFormCallBack($form, data_table) {
        $.ajax({
            type: 'post',
            url: '<?php echo __SITE_URL; ?>bad_words/save_bad_words',
            data: $form.serialize(),
            dataType: 'json',
            success: function (response) {
                if (response.mess_type === 'success') {
                    closeFancyBox();
                    window.dtBadWords && window.dtBadWords.fnDraw(false);
                } else {
                    systemMessages(response.message, 'message-error');
                }
            }
        });
    }
</script>
