<?php views()->display('new/user/seller/company/company_menu_block'); ?>

<div class="title-public pt-0">
	<h1 class="title-public__txt"><?php echo translate('seller_videos_video_word'); ?></h1>

	<?php if (logged_in() && $seller_view && have_right('have_videos')) { ?>
        <div class="dropdown">
            <a
                class="dropdown-toggle"
                data-toggle="dropdown"
                aria-haspopup="true"
                aria-expanded="false"
                href="#"
                <?php echo addQaUniqueIdentifier('page__seller-videos__heading_dropdown-btn'); ?>
            >
                <i class="ep-icon ep-icon_menu-circles"></i>
            </a>

            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">
                <button
                    class="dropdown-item fancybox.ajax fancyboxValidateModal"
                    data-fancybox-href="<?php echo __SITE_URL;?>seller_videos/popup_forms/add_video"
                    data-title="<?php echo translate('seller_videos_add_video_text', null, true); ?>"
                    title="<?php echo translate('seller_videos_add_video_text', null, true); ?>"
                    type="button"
                    <?php echo addQaUniqueIdentifier('page__seller-videos__heading_dropdown-menu_add-video-btn'); ?>
                >
                    <i class="ep-icon ep-icon_pencil"></i> <?php echo translate('seller_videos_add_video_text'); ?>
                </button>
            </div>
        </div>
	<?php } ?>
</div>

<?php views()->display('new/user/seller/videos/list_video_view'); ?>

<div class="pt-35 flex-display flex-jc--sb flex-ai--c">
	<?php views()->display("new/paginator_view"); ?>
</div>

<?php if(have_right('have_videos')) { ?>
    <script type="text/javascript">
        var delete_video_seller = function(obj){
			var $this = $(obj);
			var video = $this.data('video') || null;

			$.ajax({
				type: 'POST',
				url: __site_url + 'seller_videos/ajax_videos_operation/delete_video',
				data: { video : video},
				dataType: 'json',
				success: function(data){
					systemMessages( data.message, data.mess_type );

					if(data.mess_type == 'success'){
						$this.closest('.spersonal-pictures__item').fadeOut('normal', function(){
							$(this).remove();
						});
					}
				}
			});
		}

		var callbackAddSellerVideos = function(response){
			_notifyContentChangeCallback();
		}

		var callbackEditSellerVideos = function(response){
			_notifyContentChangeCallback();
		}
	</script>
<?php } ?>
