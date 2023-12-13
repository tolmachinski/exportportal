<script type="text/javascript" src="<?php echo fileModificationTime('public/plug_bloggers/jquery-fileupload-5-42-3/jquery.ui.widget.js');?>"></script>
<script type="text/javascript" src="<?php echo fileModificationTime('public/plug_bloggers/jquery-fileupload-5-42-3/jquery.iframe-transport.js');?>"></script>
<script type="text/javascript" src="<?php echo fileModificationTime('public/plug_bloggers/jquery-fileupload-5-42-3/jquery.fileupload.js');?>"></script>
<script type="text/javascript" src="<?php echo fileModificationTime('public/plug_bloggers/jquery-fileupload-5-42-3/jquery.fileupload-process.js');?>"></script>
<script type="text/javascript" src="<?php echo fileModificationTime('public/plug_bloggers/jquery-fileupload-5-42-3/jquery.fileupload-validate.js');?>"></script>
<script type="text/javascript">
    var fileuploadRemove = function ($thisBtn){
        $.ajax({
            type: 'POST',
            url: $thisBtn.data('action'),
            data: {file: $thisBtn.data('file')},
            dataType: 'json',
            success: function(data){
                if(data.mess_type == 'success'){
                    $thisBtn.closest('.uploadify-queue-item').remove();
                    $('#progress').fadeOut().html('');
                    if($thisBtn.data('additional-callback') != undefined){
                        window[$thisBtn.data('additional-callback')]($thisBtn);
                    }
                } else {
                    Messenger.notification(data.mess_type, data.message);
                }
            }
        });
    };

    var templateFileUpload = function (type, className, index, iconClassName){
        if(type == 'files'){
            templateHtml = '<div id="fileupload-item-'+index+'" class="uploadify-queue-item '+className+' icon">'+
                            '<div class="img-b icon-files-'+iconClassName+'-middle"></div>'+
                            '<div class="cancel"></div>'+
                        '</div>';
        }else{
            templateHtml = '<div id="fileupload-item-'+index+'" class="uploadify-queue-item '+className+'">'+
                            '<div class="img-b"></div>'+
                            '<div class="cancel"></div>'+
                        '</div>';
        }
        return templateHtml;
    };
</script>