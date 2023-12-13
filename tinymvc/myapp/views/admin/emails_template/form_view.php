<?php
    $isInsert = empty($template['id_emails_template']);
?>

<form method="post" class="validateModal relative-b">
	<div class="wr-form-content w-1000 mh-700">
        <table class="table table-striped vam-table">
            <tbody>
                <?php if(!$isInsert) { ?>
                <tr>
                    <td class="w-100">ID</td>
                    <td>
                        <?php echo $template['id_emails_template']; ?>
                    </td>
                </tr>
                <?php } ?>
                <tr>
                    <td>Alias</td>
                    <td>
                        <div class="form-group">
                            <input
                                class="w-100pr validate[required,maxSize[150]]"
                                type="text"
                                name="alias_template"
                                value="<?php echo !empty($template['alias_template']) ? $template['alias_template'] : ""; ?>"
                                placeholder="Email template alias"
                                data-prompt-position="bottomLeft:0"
                                <?php if (!$isInsert) {?>disabled<?php }?>
                            />
                        </div>
                    </td>
                </tr>

                <tr>
                    <td class="w-100">Template structure</td>
                    <td>
                        <div class="form-group">
                            <select
                                class="js-select-template-structure validate[required]"
                                name="template_structure"
                            >
                                <?php if ($isInsert) {?>
                                    <option value="" selected disabled>Select template structure</option>
                                <?php } ?>

                                <?php foreach($emailsTemplateStructure as $emailsTemplateStructureItem){?>
                                    <option
                                        value="<?php echo $emailsTemplateStructureItem['id_emails_template_structure']; ?>"
                                        <?php
                                            if(
                                                !empty($template['id_emails_template_structure'])
                                                && $emailsTemplateStructureItem['id_emails_template_structure'] === $template['id_emails_template_structure']
                                            ){
                                                echo 'selected';
                                            }
                                        ?>
                                        data-json="<?php echo (int)$emailsTemplateStructureItem['json_structure']; ?>"
                                    >
                                        <?php echo $emailsTemplateStructureItem['name']; ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>Name</td>
                    <td>
                        <div class="form-group">
                            <input
                                class="w-100pr validate[required, minSize[2], maxSize[150]]"
                                type="text"
                                name="name"
                                value="<?php echo !empty($template['name']) ? $template['name'] : ""; ?>"
                                placeholder="Email template name"
                            />
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>Subject</td>
                    <td>
                        <div class="form-group">
                            <input
                                class="w-100pr validate[minSize[2], maxSize[150]]"
                                type="text"
                                name="subject"
                                value="<?php echo !empty($template['subject']) ? $template['subject'] : ""; ?>"
                                placeholder="Subject template"
                            />
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>Header</td>
                    <td>
                        <div class="form-group">
                            <input
                                class="w-100pr validate[minSize[2], maxSize[150]]"
                                type="text"
                                name="header"
                                value="<?php echo !empty($template['header']) ? $template['header'] : ""; ?>"
                                placeholder="Header template"
                            />
                        </div>
                    </td>
                </tr>

                <!--    start html for json content-->
                <tr class="js-block-content-json <?php echo ($isInsert || (int)$template['json_structure'] === 0) ? "display-n" : ""; ?>">
                    <td>Content json</td>
                    <td>
                        <div class="form-group">
                            <div class="js-template-content-json bg-white">
                            <?php
                                if (!empty($template['content_json'])) {
                                    $key = 0;
                                    $contentData = json_decode($template['content_json'], true);
                                    $contentDataOutput = "";
                                    $contentUpBtn = '<button class="btn btn-primary call-function" data-callback="upContentTemplateData" type="button"><i class="ep-icon ep-icon_up"></i></button>';
                                    $contentDownBtn = '<button class="btn btn-primary call-function" data-callback="downContentTemplateData" type="button"><i class="ep-icon ep-icon_down"></i></button>';
                                    $contentRemoveBtn = "<button class='btn btn-danger call-function' data-callback='removeContentTemplateData' type='button'><i class='ep-icon ep-icon_remove'></i></button>";

                                    foreach($contentData as $contentKey => $contentItem){
                                        $emailElement = $emailElements[$contentItem['name']];

                                        $contentDataOutput .= "<div class='js-content-data-item mb-10 p-10 bd-2-gray' data-element='{$contentItem['name']}'>
                                                                    <div class='flex-card flex-jc--sb'>
                                                                        <div class='flex-card__fixed mr-15'>
                                                                            <strong class='lh-30'>{$emailElement['displayName']}</strong>
                                                                            <input type='hidden' name='content_template_data[{$key}][name]' value='{$contentItem['name']}' >
                                                                        </div>
                                                                        <div class='flex-card__float tar'>
                                                                            {$contentUpBtn}
                                                                            {$contentDownBtn}
                                                                            {$contentRemoveBtn}
                                                                        </div>
                                                                    </div>";

                                        if (!empty($contentItem['params'])) {
                                            $keyParam = 0;
                                            foreach ($contentItem['params'] as $paramsKey => $paramsItem){
                                                $contentDataOutput .= "<div class=\"flex-display w-100pr mt-5\">
                                                                            <div class=\"w-30pr lh-30\">{$paramsKey}</div>";

                                                $inputName = "content_template_data[{$key}][params][{$paramsKey}]";
                                                switch($emailElement['params'][$paramsKey]['type']){
                                                    case 'textarea':
                                                        $contentDataOutput .= "<textarea class='w-70pr mnh-95' name='{$inputName}'>{$paramsItem}</textarea>";
                                                        break;
                                                    case 'radio':
                                                        $inputCheckedYes = (int)$paramsItem ? "checked" : "";
                                                        $inputCheckedNo = !(int)$paramsItem ? "checked" : "";
                                                        $contentDataOutput .= "<div class='w-70pr lh-30'>
                                                                                    <label><input type='radio' name='{$inputName}' value='1' {$inputCheckedYes}> Yes</label>
                                                                                    <label><input type='radio' name='{$inputName}' value='0' {$inputCheckedNo}> No</label>
                                                                                </div>";
                                                        break;
                                                    default:
                                                        $contentDataOutput .= "<input class='w-70pr' type='text' name='{$inputName}' value='{$paramsItem}'>";
                                                        break;
                                                }

                                                $contentDataOutput .= "</div>";
                                                $keyParam += 1;
                                            }
                                        }

                                        $contentDataOutput .= "</div>";

                                        $key += 1;
                                    }

                                    echo $contentDataOutput;
                                }
                            ?>
                            </div>

                            <div class="flex-display flex-jc--fe">
                                <select class="js-select-email-elements w-200 mr-15">
                                    <option value="" selected disabled>Select element</option>
                                <?php foreach($emailElements as $emailElementsKey => $emailElementsItem){ ?>
                                    <option
                                        value="<?php echo $emailElementsKey; ?>"
                                        data-used="<?php echo $emailElementsItem['used']; ?>"
                                    ><?php echo $emailElementsItem['displayName']; ?></option>
                                <?php } ?>
                                <select>

                                <button class="btn btn-success call-function" data-callback="addEmailElements" type="button"><i class="ep-icon ep-icon_plus"></i></button>
                            </div>
                        </div>
                    </td>
                </tr>
                <!--    end js html json content-->

                <!--    start html for html content-->
                <tr class="js-block-content-html <?php echo ($isInsert || (int)$template['json_structure'] === 1) ? "display-n" : ""; ?>">
                    <td>Content</td>
                    <td>
                        <div class="form-group">
                            <textarea id="js-content-email-template-textarea" class="display-n" name="content"></textarea>

                            <div
                                id="js-content-email-template"
                                class="h-350"
                            ><?php echo !empty($template['content']) ? htmlspecialchars($template['content']) : ""; ?></div>
                        </div>
                    </td>
                </tr>
                <!--    end js html html content-->

                <tr>
                    <td>Triggered information</td>
                    <td>
                        <div class="form-group">
                            <textarea
                                id="js-triggered-information"
                                class="w-100pr mnh-50"
                                name="triggered_information"
                            ><?php echo !empty($template['triggered_information']) ? $template['triggered_information'] : ""; ?></textarea>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>Preview template data</td>
                    <td>
                        <div class="form-group">
                            <div id="js-preview-data-wrapper">
                                <?php
                                    $previewTemplateDataOutput = '';

                                    if(!empty($template['preview_template_data'])){
                                        $previewTemplateData = json_decode($template['preview_template_data'], true);
                                        $previewTemplateCount = 0;
                                        $previewTemplateRemoveBtn = '';

                                        if(have_right('edit_emails_template_preview_data')){
                                            $previewTemplateRemoveBtn = '<a class="btn btn-danger call-function" data-callback="removePreviewTemplateData"><i class="ep-icon ep-icon_remove"></i></a>';
                                        }

                                        foreach($previewTemplateData as $previewTemplateDataKey => $previewTemplateDataItem){
                                            $previewTemplateDataOutput .= '<div class="js-preview-data-item flex-display">
                                                                            <input class="w-30pr" type="text" name="preview_template_data['.$previewTemplateCount.'][name]" value="'.$previewTemplateDataKey.'">
                                                                            <input class="w-70pr" type="text" name="preview_template_data['.$previewTemplateCount.'][value]" value="'.$previewTemplateDataItem.'">
                                                                            '.$previewTemplateRemoveBtn.'
                                                                        </div>';
                                            $previewTemplateCount++;
                                        }
                                    }

                                    echo $previewTemplateDataOutput;
                                ?>
                            </div>

                            <?php if(have_right('edit_emails_template_preview_data')){ ?>
                                <a class="btn btn-primary call-function" data-callback="addPreviewTemplateData"><i class="ep-icon ep-icon_plus"></i></a>
                            <?php } ?>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <?php if (!$isInsert) {?>
		<input type="hidden" name="id_emails_template" value="<?php echo $template['id_emails_template'];?>"/>
        <?php } ?>
	</div>
	<div class="wr-form-btns clearfix">
        <?php if(have_right('manage_proofread')){?>
            <div class="pull-left">
                <label>
                    <input
                        type="checkbox"
                        name="proofread"
                        value="1"
                        <?php echo ((bool)(int)$template['proofread'])?'checked':'';?>
                    >
                    Proofread
                </label>
            </div>
        <?php }?>
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>

<script type="text/javascript" src="<?php echo __SITE_URL; ?>public/plug_admin/tinymce-4-3-10/tinymce.min.js"></script>
<script>
    var haveRemoveBtn = true;
    <?php if(have_right('edit_emails_template_preview_data')){?>
        haveRemoveBtn = false;
    <?php }?>

    var emailElements = <?php echo json_encode($emailElements); ?>;
</script>
<script src="<?php echo fileModificationTime('public/plug_admin/js/emails_template/index.js');?>"></script>

<!--    start js for json content-->
    <script src="<?php echo fileModificationTime('public/plug_admin/js/emails_template/content_json.js');?>"></script>

    <script type="text/template" id="js-template-content-json-element-parameter">
        <div class="flex-display w-100pr mt-5">
            <div class="w-30pr lh-30">{{paramName}}</div>
            {{input}}
        </div>
    </script>

    <script type="text/template" id="js-template-content-json-element">
        <div class="js-content-data-item mb-10 p-10 bd-2-gray" data-element="{{elementName}}">
            <div class="flex-card flex-jc--sb">
                <div class="flex-card__fixed mr-15">
                    <strong class="lh-30">{{elementDisplayName}}</strong>
                    <input type='hidden' name="content_template_data[{{key}}][name]" value="{{elementName}}" >
                </div>
                <div class="flex-card__float tar">
                    <button class="btn btn-primary call-function" data-callback="upContentTemplateData" type="button"><i class="ep-icon ep-icon_up"></i></button>
                    <button class="btn btn-primary call-function" data-callback="downContentTemplateData" type="button"><i class="ep-icon ep-icon_down"></i></button>
                    <button class="btn btn-danger call-function" data-callback="removeContentTemplateData" type="button"><i class="ep-icon ep-icon_remove"></i></button>
                </div>
            </div>
            {{params}}
        </div>
    </script>
<!--    end js for json content-->

<!--    start js for html content-->
    <script src="<?php echo __SITE_URL;?>public/plug_admin/ace-builds-1-4-12/ace.js" type="text/javascript" charset="utf-8"></script>
    <script src="<?php echo __SITE_URL;?>public/plug_admin/ace-builds-1-4-12/theme-chrome.js" type="text/javascript" charset="utf-8"></script>
    <script src="<?php echo __SITE_URL;?>public/plug_admin/ace-builds-1-4-12/mode-html.js" type="text/javascript" charset="utf-8"></script>
    <script src="<?php echo fileModificationTime('public/plug_admin/js/emails_template/content_html.js');?>"></script>
<!--    end js for html content-->
