<div class="wr-modal-b">
    <form action="<?php echo __SITE_URL;?>analytics/ajax_operations/<?php echo (empty($target))?'add_target':'edit_target';?>" class="modal-b__form validateModal">
		<div class="modal-b__content pb-0 w-700">
			<div class="row">
                <div class="col-xs-12 initial-b">
                    <label class="modal-b__label">Target name</label>
                    <input type="text" class="form-control validate[required,maxSize[1000]]" name="target_name" placeholder="e.g. Learn more" value="<?php echo (!empty($target))?$target['target_name']:'';?>">
                </div>
                <div class="col-xs-6">
                    <label class="modal-b__label">Target type</label>
                    <select name="target_type" class="form-control validate[required]">
                        <option value="page" <?php if(!empty($target)){echo selected($target['target_type'], 'page');}?>>Page</option>
                        <option value="form" <?php if(!empty($target)){echo selected($target['target_type'], 'form');}?>>Form</option>
                        <option value="link" <?php if(!empty($target)){echo selected($target['target_type'], 'link');}?>>Link</option>
                    </select>
                </div>
                <div class="col-xs-6">
                    <label class="modal-b__label">Target operators ( <a href="https://developers.google.com/analytics/devguides/reporting/core/v4/rest/v4/reports/batchGet#Operator" target="_blank">see ga:documentation</a> )</label>
                    <select name="target_operator" class="form-control">
                        <option value="">Select operator</option>
                        <?php foreach($target_operators as $target_operator){?>
                            <option value="<?php echo $target_operator;?>" <?php if(!empty($target)){echo selected($target['target_operator'], $target_operator);}?>><?php echo $target_operator;?></option>
                        <?php }?>
                    </select>
                </div>
                <div class="col-xs-12">
                    <label class="modal-b__label" >Target aliases</label>
                    <div id="target_aliases">
                        <?php if(!empty($target)){?>
                            <?php $target_aliases = json_decode($target['target_aliases'], true);?>
                            <?php if(!empty($target_aliases)){?>
                                <?php foreach($target_aliases as $target_aliase){?>
                                    <div class="input-group mt-10">
                                        <input type="text" name="aliases[]" class="form-control" placeholder="e.g. ^\/(\\?lang=[a-z_]*)?$" value="<?php echo $target_aliase['value'];?>">
                                        <span class="input-group-btn">
                                            <button type="button" class="btn btn-default h-35 confirm-dialog" data-callback="delete_target_alias" data-message="Are you sure you want to delete this target alias?"><i class="ep-icon ep-icon_trash"></i></button>
                                        </span>
                                    </div>
                                <?php }?>
                            <?php }?>
                        <?php }?>
                    </div>
                    <button type="button" class="btn btn-default pull-left mt-10 call-function" data-callback="add_target_alias"><i class="ep-icon ep-icon_plus fs-12"></i> Add alias</button>
                </div>
			</div>
		</div>
        <div class="modal-b__btns clearfix">
            <label class="lh-30 vam mr-15">
                <input type="checkbox" name="target_active_ga" <?php if(!empty($target)){echo checked($target['target_active_ga'], 1);}?>> Google Analytics
            </label>
            <label class="lh-30 vam">
                <input type="checkbox" name="target_active_oa" <?php if(!empty($target)){echo checked($target['target_active_oa'], 1);}?>> Own Analytics
            </label>
            <button class="btn btn-success pull-right" type="submit">
                <span class="ep-icon ep-icon_ok"></span> Save
            </button>

            <?php if(!empty($target)){?>                
                <input type="hidden" name="id_target" value="<?php echo $target['id_target'];?>">
            <?php }?>
        </div>
    </form>
</div>
<script>
    var add_target_alias = function(){
        var template = '<div class="input-group mt-10">\
                            <input type="text" name="aliases[]" class="form-control" placeholder="e.g. ^\/(\\?lang=[a-z_]*)?$">\
                            <span class="input-group-btn">\
                                <button type="button" class="btn btn-default h-35 confirm-dialog" data-callback="delete_target_alias" data-message="Are you sure you want to delete this target alias?"><i class="ep-icon ep-icon_trash"></i></button>\
                            </span>\
                        </div>';
        $('#target_aliases').append(template);
    }

    var delete_target_alias = function(opener){
        $(opener).closest('.input-group').remove();
    }

    var modalFormCallBack = function (form, data_table){
        var $form = $(form);
        $.ajax({
            type: 'POST',
            url: $form.attr('action'),
            data: $form.serialize(),
            dataType: 'JSON',
            beforeSend: function(){
                showLoader($form);
            },
            success: function(resp){
                systemMessages( resp.message, 'message-' + resp.mess_type );

                if(resp.mess_type == 'success'){
                    closeFancyBox();
                    if(data_table != undefined){
						data_table.fnDraw();
                    }
                }

                hideLoader(form);
            },
            error: function(jqXHR, textStatus, errorThrown){
                hideLoader(form);
                systemMessages( 'The request can not be sent. Please try again later.', 'message-error' );
                jqXHR.abort();
            }
        });
    };

    $(function(){
        
    });
</script>
