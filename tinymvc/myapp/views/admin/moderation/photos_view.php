
<div class="wr-modal-b">
    <div class="modal-b__content pb-0 w-900 mh-600">
        <div class="row">

            <?php if(!empty($main) && file_exists(getImgSrc('items.photos', 'original', array('{ID}' => $resource_id, '{FILE_NAME}' => 'orig_' . $main['photo_name'])))){ ?>
            <div class="col-xs-12 mb-15 flex-display flex-w--w orig-image-row">
                <h2>Main Image</h2>
                <div class="orig-image-block">
                    <div class="w-150 h-150 image-card3">
                        <span class="link">
                            <img
                                class="image"
                                src="<?php echo getDisplayImageLink(['{ID}' => $resource_id, '{FILE_NAME}' => $main['photo_name']], 'items.photos', ['thumb_size' => 1 ]); ?>"
                                alt="Item image" />
                        </span>
                    </div>
                    <button class="btn btn-primary display-b call-function" type="button" data-callback="downloadImage" data-resource="<?php echo $resource_id;?>" data-id="<?php echo $main['id']; ?>">Download original</button>
                </div>
            </div>
            <?php }?>

            <div class="col-xs-12 mb-15 flex-display flex-w--w orig-image-row">
                <h2>Photos</h2>
                <?php if(!empty($images))
                {
                    foreach($images as $key => $image){?>

                    <div class="orig-image-block">
                        <div class="w-150 h-150 image-card3">
                            <span class="link">
                                <img
                                    class="image"
                                    src="<?php echo getDisplayImageLink(['{ID}' => $resource_id, '{FILE_NAME}' => $image['photo_name']], 'items.photos', ['thumb_size' => 1 ]); ?>"
                                    alt="Item image" />
                            </span>
                        </div>
                        <button class="btn btn-primary display-b call-function" type="button" data-callback="downloadImage" data-resource="<?php echo $resource_id;?>" data-id="<?php echo $image['id']; ?>">Download original</button>

                    </div>
                <?php }
            }else{ ?>
                <p class="w-100pr">No original photos for this item</p>
            <?php } ?>
            </div>

        </div>
    </div>
</div>
<?php views()->display('new/download_script'); ?>
<script>
    function downloadImage (button)
    {
        button.addClass('disabled').prop('disabled', true);
        var idImage = button.data('id');
        var idItem = button.data('resource');

        postRequest(__site_url + 'moderation/ajax_operations/download_original_image/items/' + idItem, { image: idImage }, "json")
            .then(function (response) { downloadFile(response.file, response.name); })
            .catch(onRequestError)
            .finally(function () { button.removeClass('disabled').prop('disabled', false); })
    }
</script>
