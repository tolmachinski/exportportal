<script type="text/javascript">
$(document).ready(function(){

})

var deleteVideo = function(obj){
	var $this = $(obj);
	var video = $this.data('video');

	$.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL?>video/ajax_video_operation/delete_video',
		data: { video : video},
		beforeSend: function(){  },
		dataType: 'json',
		success: function(resp){
			systemMessages( resp.message, 'message-' + resp.mess_type );

			if(resp.mess_type == 'success'){
				$this.closest('tr').fadeOut(function(){
					$(this).remove();
				});
			}
		}
	});
}

function callbackUpdateVideo(resp){
	$('#table-video #trvideo-'+ resp.id_video)
		.find('.title-b').text(resp.title_video).end()
		.find('.short-name-b').text(resp.short_name).end()
		.find('.src-b').text(resp.src_video);
}

function callbackCreateVideo(resp){
	$('#table-video tbody').append( '<tr id="trvideo-' + resp.id_video + '">\
						<td class="w-50 tac">' + resp.id_video + '</td>\
						<td class="tal w-250 title-b">' + resp.title_video + '</td>\
						<td class="tal short-name-b">' + resp.short_name + '</td>\
						<td class="w-120 tac src-b">' + resp.src_video + '</td>\
						<td class="tac w-80">\
							<a class="ep-icon ep-icon_pencil fancybox.ajax fancyboxValidateModal" href="video/popup_forms/edit_video/' + resp.id_video + '" data-title="Edit video" title="Edit video"></a>\
							<a class="ep-icon ep-icon_remove txt-red confirm-dialog" data-callback="deleteVideo" data-message="Are you sure you want to delete this video?" data-video="' + resp.id_video + '" href="#" title="Delete video"></a>\
						</td>\
					</tr>');
}
</script>
<?php $type = array(
		'youtube' => 'YouTube',
		'vimeo' => 'Vimeo',
		'html5' => 'HTML5'
	); ?>
	
<div class="row">
    <div class="col-xs-12">
        <div class="titlehdr h-30">Video's list <a class="btn btn-primary btn-sm fancybox.ajax fancyboxValidateModal pull-right" href="<?php echo __SITE_URL;?>video/popup_forms/add_video" data-title="Add video" title="Add video">Add video</a></div>
        <table id="table-video" cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr">
            <thead>
                <tr>
                    <th class="w-50">#</th>
                    <th>Title</th>
                    <th>Short name</th>
                    <th>Source</th>
                    <th class="w-80">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if(isset($videos_list) && count($videos_list)){?>
                <?php foreach($videos_list as $videos_item){?>
                    <tr id="trvideo-<?php echo $videos_item['id_video']?>">
                        <td class="w-50 tac"><?php echo $videos_item['id_video']?></td>
                        <td class="tal w-250 title-b"> <div class="text-overflow w-150"><?php echo $videos_item['title_video']?></div></td>
                        <td class="tal short-name-b"><?php echo $videos_item['short_name']?></td>
                        <td class="w-120 tac src-b"><?php echo $type[$videos_item['src_video']]?></td>
                        <td class="tac w-80">
                            <a class="ep-icon ep-icon_pencil fancybox.ajax fancyboxValidateModal" href="<?php echo __SITE_URL;?>video/popup_forms/edit_video/<?php echo $videos_item['id_video']?>" data-title="Edit video" title="Edit video"></a>
                            <a class="ep-icon ep-icon_remove txt-red confirm-dialog" data-callback="deleteVideo" data-message="Are you sure you want to delete this video?" data-video="<?php echo $videos_item['id_video']?>" href="#" title="Delete video"></a>
                        </td>
                    </tr>
                <?php } ?>
            <?php } else{ ?>
                <tr><td colspan="5">Videos not exist still.</td></tr>
            <?php } ?>
            </tbody>	
        </table>
    </div>
</div>
