<div class="wr-modal-b">
    <form method="post" action="<?php echo $action; ?>" class="validateModal modal-b__form" id="accreditation-documents--form">
        <div class="modal-b__content pb-0 w-700 mh-700 mt-10">
            <div class="row">
                <div class="form-group col-xs-12 mb-5">
                    <label class="col-xs-12 col-sm-3 col-form-label vam h-35 lh-35"
                        for="accreditation-documents--form-field--document-title">
                        Document title
                    </label>
                    <div class="col-xs-12 col-sm-9">
                        <input type="text"
                            id="accreditation-documents--form-field--document-title"
                            name="document_title"
                            class="form-control validate[required,maxSize[250]]"
                            placeholder="Enter the document title">
                    </div>
                </div>

                <div class="form-group col-xs-12 mb-5">
                    <label class="col-xs-12 col-sm-3 col-form-label vam h-35 lh-35" for="accreditation-documents--form-field--document-category">
                        Category
                    </label>
                    <div class="col-xs-12 col-sm-9">
                        <select name="category" class="form-control validate[required]" id="accreditation-documents--form-field--document-category">
                            <option value="" selected disabled>Select category</option>
                            <?php foreach ($categories as list($category, $categoryLabel)) { ?>
                                <option value="<?php echo cleanOutput((string) $category); ?>">
                                    <?php echo cleanOutput($categoryLabel); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <ul class="nav nav-tabs display-ib w-100pr" role="tablist">
                    <li class="active">
                        <a href="#accreditation-user-groups" role="tab" data-toggle="tab">User groups</a>
                    </li>
                    <li>
                        <a href="#accreditation-industries" role="tab" data-toggle="tab">Industries</a>
                    </li>
                    <li>
                        <a href="#accreditation-countries" role="tab" data-toggle="tab">Countries</a>
                    </li>
                    <li>
                        <a href="#other-options" role="tab" data-toggle="tab">Other options</a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane active" id="accreditation-user-groups">
                        <div class="row-fluid" style="height: 602px !important">
                            <div class="mt-10">
                                <div class="form-group col-xs-12 mb-10 pb-15 bdb-1-gray">
                                    <div class="form-check">
                                        <label class="form-check-label cur-pointer"
                                            for="accreditation-documents--form-field--user-groups-all">
                                            <input type="checkbox"
                                                id="accreditation-documents--form-field--user-groups-all"
                                                name="select_all"
                                                class="form-check-input pull-left mt-1 mr-5 accreditation-documents__user-groups-all cur-pointer"
                                                data-select="groups"
                                                value="1">
                                            Select all
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-20">
                                <?php foreach ($groups as $index => $group) { ?>
                                    <div class="form-group col-xs-12 mb-10">
                                        <div class="clearfix bdb-1-gray pb-5">
                                            <div class="form-check">
                                                <label class="form-check-label pull-left cur-pointer"
                                                    for="accreditation-documents--form-field--user-groups-<?php echo $index; ?>">
                                                    <input type="checkbox"
                                                        name="groups[]"
                                                        id="accreditation-documents--form-field--user-groups-<?php echo $index; ?>"
                                                        class="pull-left mt-1 mr-5 accreditation-documents__user-groups"
                                                        value="<?php echo $group['idgroup'];?>">
                                                    <?php echo $group['gr_name'];?>
                                                </label>
                                            </div>
                                            <div class="form-check pull-right">
                                                <label class="form-check-label"
                                                    for="accreditation-documents--form-field--user-groups-required-<?php echo $index; ?>">
                                                    <span class="txt-red">*</span>
                                                    <input type="checkbox"
                                                        name="groups_required[]"
                                                        id="accreditation-documents--form-field--user-groups-required-<?php echo $index; ?>"
                                                        class="pull-right mt-1 ml-2 accreditation-documents__user-groups-require"
                                                        value="<?php echo $group['idgroup'];?>">
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>

                    <div role="tabpanel" class="tab-pane" id="accreditation-industries">
                        <div class="mt-10">
                            <div class="form-group col-xs-12 mb-10 pb-15 bdb-1-gray">
                                <div class="form-check">
                                    <label class="form-check-label cur-pointer" for="accreditation-documents--form-field--industries-all">
                                        <input type="checkbox"
                                            id="accreditation-documents--form-field--industries-all"
                                            name="select_all"
                                            class="form-check-input pull-left mt-1 mr-5 accreditation-documents__industries-all"
                                            data-select="industries"
                                            value="1">
                                        Select all
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="mt-20">
                            <?php foreach ($industries as $index => $industry) { ?>
                                <div class="form-group col-xs-12 mb-10">
                                    <div class="clearfix bdb-1-gray pb-5">
                                        <div class="form-check">
                                            <label class="form-check-label"
                                                for="accreditation-documents--form-field--industries-<?php echo $index; ?>">
                                                <input type="checkbox"
                                                    name="industries[]"
                                                    id="accreditation-documents--form-field--industries-<?php echo $index; ?>"
                                                    class="pull-left mt-1 mr-5 accreditation-documents__industries"
                                                    value="<?php echo $industry['category_id'];?>">
                                                <?php echo $industry['name'];?>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>

                    <div role="tabpanel" class="tab-pane" id="accreditation-countries">
                        <table class="data table-bordered table-striped">
                            <thead style="padding-bottom: 10px;">
                                <tr>
                                    <th class="tal vam">
                                        <input  class="form-check-input mt-1 mr-5 accreditation-documents__industries-all"
                                                id="accreditation-documents--form-field--industries-all"
                                                type="checkbox"
                                                name="select_all"
                                                data-select="countries"
                                                value="1"
                                                <?php if($countries_all) { echo 'checked'; } ?>>
                                    </th>
                                    <th class="tal vam w-180">Country</th>
                                    <th>Document title</th>
                                </tr>
                            </thead>
                            <tbody>
                                    <?php foreach ($countries as $index => $country) { ?>
                                        <tr>
                                            <td class="tal vam">
                                                <input type="checkbox"
                                                            name="countries[]"
                                                            class="mt-1 mr-5 accreditation-documents__countries"
                                                            id="accreditation-documents--form-field--countries-<?php echo $index; ?>"
                                                            value="<?php echo $country['id'];?>"
                                                            <?php echo checked($country['id'], $countries_selected)?>>
                                            </td>
                                            <td class="tal vam">
                                                <img
                                                    class="ico-country"
                                                    width="24"
                                                    height="24"
                                                    src="<?php echo getCountryFlag($country['country']); ?>"
                                                    alt="<?php echo $country['country']?>" title="<?php echo $country['country']?>"
                                                >
                                                <span class="display-ib mt-4 ml-5"><?php echo $country['country'];?></span>
                                            </td>
                                            <td><input type="text"
                                                            name="document_titles[<?php echo $country['id']; ?>]"
                                                            placeholder="Enter document title"
                                                            value="<?php echo arrayGet($document['document_titles'], $country['id'], '') ?>">
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                    </div>

                    <div role="tabpanel" class="tab-pane" id="other-options">
                        <div class="row-fluid" style="height: 602px !important">
                            <div class="mt-20">
                                <label class="modal-b__label" >Select company types</label>
                                <select name="company_types[]" class="form-control" id="accreditation-documents--form-field-company-types" multiple>
                                    <option></option>
                                    <?php foreach ($company_types as $company_type) {?>
                                        <option value="<?php echo $company_type['id_type'];?>"><?php echo cleanOutput($company_type['name_type']);?></option>
                                    <?php }?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-b__btns clearfix">
            <button class="pull-right btn btn-success" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
        </div>
    </form>
</div>
<script type="application/javascript">
    var modalFormCallBack = function (formNode, data_table){
        var form = $(formNode);
        var url = form.attr('action');
        var data = form.serializeArray();

        $.ajax({
            type: 'POST',
            url: url,
            data: data,
            beforeSend: function () {
                showLoader(form);
            },
            dataType: 'json',
            success: function(data){
                systemMessages( data.message, 'message-' + data.mess_type );

                if(data.mess_type == 'success'){
                    closeFancyBox();
                    if(data_table != undefined)
                        data_table.fnDraw();
                }else{
                    hideLoader(form);
                }
            }
        });
    }

    $(document).ready(function() {
        var form = $('#accreditation-documents--form');
        var checkboxes = form.find('input[type=checkbox]:not([name=select_all],[name="groups_required[]"])');
        var groupSwitchers = form.find('input[name=select_all]');
        var groupFields = form.find('.accreditation-documents__user-groups');
        var groupRequireFields = form.find('.accreditation-documents__user-groups-require');
        var selectFullGroup = function() {
            var self = $(this);
            var toggle = self.data('select');
            var state = self.prop('checked');
            var targets = $('input[name="' + toggle + '[]"]');

            targets.prop('checked', state);
            if('groups' === toggle) {
                targets.closest('.form-group').find('.accreditation-documents__user-groups-require').prop('checked', state);
            }
        };
        var onGroupChanges = function() {
            var self = $(this);
            var state = self.prop('checked');
            var boundField = self.closest('.form-group').find('.accreditation-documents__user-groups-require');
            if(boundField.length) {
                boundField.prop('checked', state);
            }
        };
        var onGroupRequireChanges = function() {
            var self = $(this);
            var state = self.prop('checked');
            var boundField = self.closest('.form-group').find('.accreditation-documents__user-groups');
            if(state && boundField.length) {
                boundField.prop('checked', state);
            }
        };
        var deselectGroupSwitcher = function() {
            var self = $(this);
            var state = self.prop('checked');
            if(!state) {
                var groupSwitcher = self.closest('.tab-pane').find('input[type=checkbox][name=select_all]');
                if(groupSwitcher.length) {
                    groupSwitcher.prop('checked', false);
                }
            }
        };

        $('#accreditation-documents--form-field-company-types').select2({
            width: '100%',
            multiple: true,
            placeholder: "Selected company types",
            minimumResultsForSearch: 2,
        });

        checkboxes.on('change', deselectGroupSwitcher);
        groupSwitchers.on('change', selectFullGroup);
        groupFields.on('change', onGroupChanges);
        groupRequireFields.on('change', onGroupRequireChanges);
    });
</script>
