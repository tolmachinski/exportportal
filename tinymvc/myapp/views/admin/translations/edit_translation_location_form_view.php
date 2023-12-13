<div class="wr-modal-b">
    <form method="post" action="<?php echo $action; ?>" class="validateModal modal-b__form" id="translation-location--form">
        <input type="hidden" name="page" id="translation-location--form-field-page-id" value="<?php echo $translation['id_key']; ?>">

        <div class="modal-b__content pb-0 w-750 mh-750 h-600 mt-10 pl-23 pr-23">

            <div class="form-group row mb-5">
                <label class="col-xs-12 col-form-label vam h-35 lh-35">
                    Locations
                </label>
                <div class="col-xs-12" id="translation-location--form-field--translation-key-files-wrapper">
                    <?php if($locations) { ?>
                        <?php foreach ($locations as $index => $location) { ?>
                            <div class="input-group mb-5">
                                <span class="input-group-addon">../</span>
                                <input type="text"
                                    name="files[]"
                                    id="translation-location--form-field--translation-key-file-<?php echo $index + 1; ?>"
                                    class="form-control validate[required,maxSize[1000]]"
                                    value="<?php echo !empty($location) ? cleanOutput($location): ''; ?>"
                                    placeholder="Enter the translation key location">
                                <a role="button"
                                    class="btn btn-default input-group-addon remove-key-location-file-input">
                                    <i class="ep-icon ep-icon_remove"></i>
                                </a>
                            </div>
                        <?php } ?>
                    <?php } else { ?>
                        <div class="input-group mb-5">
                            <span class="input-group-addon">../</span>
                            <input type="text"
                                name="files[]"
                                id="translation-location--form-field--translation-key-file-1"
                                class="form-control validate[required,maxSize[1000]]"
                                placeholder="Enter the translation key location">
                            <a role="button"
                                class="btn btn-default input-group-addon remove-key-location-file-input">
                                <i class="ep-icon ep-icon_remove"></i>
                            </a>
                        </div>
                    <?php } ?>
                </div>
                <div class="col-xs-12">
                    <a role="button"
                        id="add-page-file-views"
                        class="btn btn-default pull-right">
                        <i class="ep-icon ep-icon_plus"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="modal-b__btns clearfix">
            <button class="pull-right btn btn-success" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
        </div>
    </form>
</div>
<script type="text/template" id="page-view-file-template">
    <div class="input-group mb-5">
        <span class="input-group-addon">../</span>
        <input type="text"
            name="files[]"
            id="translation-location--form-field--translation-key-file-"
            class="form-control validate[required,maxSize[1000]]"
            placeholder="Enter the translation key location">
        <a role="button"
            class="btn btn-default input-group-addon remove-key-location-file-input">
            <i class="ep-icon ep-icon_remove"></i>
        </a>
    </div>
</script>
<script type="application/javascript">
    var modalFormCallBack = function (formElement, dataGrid){
        var form = $(formElement);
        var data = form.serializeArray();
        var url = form.attr('action');
        var onSuccess = function (response) {
            systemMessages(response.message, 'message-' + response.mess_type);
            if('success' === response.mess_type) {
                dataGrid.DataTable().draw(false);
                $.fancybox.close();
            }
        };

        showLoader(form);
        $.post(url, data, null, 'json').done(onSuccess).fail(onRequestError).always(function(){
            hideLoader(form);
        });
    }

    $(document).ready(function() {
        var body = $('body');
        var form = $("#translation-location--form");
        var modules = form.find('#page--form-field--page-modules');
        var pageViewSpawner = form.find('#add-page-file-views');
        var pageViewsWrapper = form.find('#translation-location--form-field--translation-key-files-wrapper');
        var pageViewsCleaners = form.find('.remove-key-location-file-input');
        var pageViewsTemplate = $('#page-view-file-template')
        var addFileEntry = function (e) {
            e.preventDefault();

            var self = $(this);
            var elements = $.parseHTML(pageViewsTemplate.text().trim());
            var viewGroup = $(elements);
            var viewInput = viewGroup.find('input');
            viewInput.prop('id', viewInput.prop('id') + (pageViewsWrapper.children().length + 1));
            viewGroup.find('a').on('click', removeFileEntry);
            pageViewsWrapper.append(viewGroup);
            validateReInit(form)
        };
        var removeFileEntry = function (e) {
            e.preventDefault();

            var self = $(this);
            var group = self.closest('.input-group');
            if(group.length) {
                if(group.length > 1) {
                    group.remove();
                    validateReInit(form);
                } else {
                    group.find('input').val(null);
                }
            }
        };

        pageViewsCleaners.on('click', removeFileEntry);
        pageViewSpawner.on('click', addFileEntry);
    });
</script>