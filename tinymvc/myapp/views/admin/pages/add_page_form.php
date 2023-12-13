<div class="wr-modal-b">
    <form method="post" action="<?php echo $action; ?>" class="validateModal modal-b__form" id="pages--form">
        <div class="modal-b__content pb-0 w-750 mh-750 h-600 mt-10 pl-23 pr-23">
            <div class="form-group row mb-5">
                <label class="col-xs-12 col-sm-3 col-form-label vam h-35 lh-35"
                    for="pages--form-field--page-name">
                    Page name
                </label>
                <div class="col-xs-12 col-sm-9 initial-b">
                    <input type="text"
                        name="name"
                        id="pages--form-field--page-name"
                        class="form-control validate[required,maxSize[250]]"
                        placeholder="Enter the page name">
                </div>
            </div>

            <div class="form-group row mb-5">
                <label class="col-xs-12 col-sm-3 col-form-label vam h-35 lh-35"
                    for="pages--form-field--page-controller-name">
                    Page controller name
                </label>
                <div class="col-xs-12 col-sm-9">
                    <input type="text"
                        name="controller"
                        id="pages--form-field--page-controller-name"
                        class="form-control validate[required,maxSize[250]]"
                        placeholder="Enter the page controller name">
                </div>
            </div>

            <div class="form-group row mb-5">
                <label class="col-xs-12 col-sm-3 col-form-label vam h-35 lh-35"
                    for="pages--form-field--page-action-name">
                    Page action name
                </label>
                <div class="col-xs-12 col-sm-9">
                    <input type="text"
                        name="action"
                        id="pages--form-field--page-action-name"
                        class="form-control validate[required,maxSize[250]]"
                        placeholder="Enter the page action name">
                </div>
            </div>

            <div class="form-group row mb-5">
                <label class="col-xs-12 col-sm-3 col-form-label vam h-35 lh-35"
                    for="pages--form-field--page-url-pattern">
                    Page URL pattern
                </label>
                <div class="col-xs-12 col-sm-9">
                    <input type="url"
                        name="url"
                        id="pages--form-field--page-url-pattern"
                        class="form-control validate[maxSize[2000]]"
                        placeholder="Enter the page URL pattern">

                    <div class="info-alert-b mt-5">
                        <i class="ep-icon ep-icon_info"></i>
                        <div>This field contains the URL regular expression pattern that must follow the rules: </div>
                        <div> • <mark>__SEGMENT__</mark> placeholder is equivalent to <mark>([^/]+)</mark> - all characters except "/"</div>
                        <div> • <mark>__NUMBER__</mark> placeholder is equivalent to <mark>([0-9]+)</mark> - only numbers</div>
                        <div> • <mark>__ANY__</mark> placeholder is equivalent to <mark>(.+)</mark> - any character sequence</div>
                        <div> • <mark>__SEGMENT?__</mark>, <mark>__ANY?__</mark> and <mark>__NUMBER?__</mark> placeholders can be used to add optional patterns</mark></div>
                        <div> • It is recommeneded to escape characters that are part of the regular expression syntax, such as <mark> . \ + * ? [ ^ ] $ ( ) { } = ! < > | : - </mark></div>
                    </div>
                </div>

            </div>

            <div class="form-group row mb-5">
                <label class="col-xs-12 col-sm-3 col-form-label vam h-35 lh-35"
                    for="pages--form-field--page-description">
                    Page description
                </label>
                <div class="col-xs-12 col-sm-9">
                    <textarea name="description"
                        id="pages--form-field--page-description"
                        class="form-control validate[maxSize[500]]"
                        placeholder="Enter the page description"></textarea>
                </div>
            </div>

            <div class="form-group row mb-5">
                <label class="col-xs-12 col-sm-3 col-form-label vam h-35 lh-35"
                    for="pages--form-field--page-modules">
                    Modules
                </label>
                <div class="col-xs-12 col-sm-9">
                    <select id="page--form-field--page-modules" name="modules[]" multiple>
                        <option></options>
                        <?php foreach ($modules as $module) { ?>
                            <option value="<?php echo $module['id_module']; ?>"><?php echo cleanOutput($module['name_module']); ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>

            <div class="form-group row mb-5">
                <label class="col-xs-12 col-sm-3 col-form-label vam h-35 lh-35">
                    Page view files
                </label>
                <div class="col-xs-12 col-sm-9" id="pages--form-field--page-file-views-wrapper">
                    <div class="input-group mb-5">
                        <span class="input-group-addon">../views/</span>
                        <input type="text"
                            name="views[]"
                            id="pages--form-field--page-file-view-1"
                            class="form-control validate[required,maxSize[250]]"
                            placeholder="Enter the page view file name">
                        <a role="button"
                            class="btn btn-default input-group-addon remove-page-view-file-input">
                            <i class="ep-icon ep-icon_remove"></i>
                        </a>
                    </div>
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
            <label class="lh-30 vam pull-left mr-10">
                <input class="vam" type="checkbox" name="is_public" <?php echo $page['is_public'] ? 'checked="checked"' : '';?>/>
                Public page
            </label>
            <button class="pull-right btn btn-success" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
        </div>
    </form>
</div>
<script type="text/template" id="page-view-file-template">
    <div class="input-group mb-5">
        <span class="input-group-addon">../views/</span>
        <input type="text"
            name="views[]"
            id="pages--form-field--page-file-view-"
            class="form-control validate[required,maxSize[250]]"
            placeholder="Enter the page view file name">
        <a role="button"
            class="btn btn-default input-group-addon remove-page-view-file-input">
            <i class="ep-icon ep-icon_remove"></i>
        </a>
    </div>
</script>
<script type="application/javascript">
    var modalFormCallBack = function (form, pagesTable){
        var pageForm = $(form);
        var data = pageForm.serializeArray();
        var url = pageForm.attr('action');
        var onSuccess = function (response) {
            systemMessages(response.message, 'message-' + response.mess_type);
            if('success' === response.mess_type) {
                pagesTable.DataTable().draw(false);
                $.fancybox.close();
            }
        };

        showLoader(pageForm);
        $.post(url, data, null, 'json').done(onSuccess).fail(onRequestError).always(function(){
            hideLoader(pageForm);
        });
    }

    $(document).ready(function() {
        var body = $('body');
        var form = $("#pages--form");
        var modules = form.find('#page--form-field--page-modules');
        var pageViewSpawner = form.find('#add-page-file-views');
        var pageViewsWrapper = form.find('#pages--form-field--page-file-views-wrapper');
        var pageViewsCleaners = form.find('.remove-page-view-file-input');
        var pageViewsTemplate = $('#page-view-file-template')
        var addPageView = function (e) {
            e.preventDefault();

            var self = $(this);
            var elements = $.parseHTML(pageViewsTemplate.text().trim());
            var viewGroup = $(elements);
            var viewInput = viewGroup.find('input');
            viewInput.prop('id', viewInput.prop('id') + (pageViewsWrapper.children().length + 1));
            viewGroup.find('a').on('click', removePageView);
            pageViewsWrapper.append(viewGroup);
            validateReInit(form)
        };
        var removePageView = function (e) {
            e.preventDefault();

            var self = $(this);
            var group = self.closest('.input-group');
            if(group.length) {
                group.remove();
                validateReInit(form);
            }
        };

        pageViewsCleaners.on('click', removePageView);
        pageViewSpawner.on('click', addPageView);
        modules.select2({
            width: '100%',
            minimumResultsForSearch: 1,
            placeholder: "Select modules",
        });
    });
</script>
