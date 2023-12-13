<?php if($seller_view && have_right('have_news') || have_right('moderate_content')){?>
    <?php if($seller_view && have_right('have_news')){?>
	    <?php tmvc::instance()->controller->view->display('new/file_upload_scripts'); ?>
    <?php }?>
	<script type="text/javascript">
        <?php if($seller_view && have_right('have_news')){?>
            function callbackAddSellerNews(resp){
                if(typeof dtNews === 'undefined'){
                    _notifyContentChangeCallback();
                } else{
                    dtNews.fnDraw();
                }
            }

            function callbackEditSellerNews(resp){
                if(typeof dtNews === 'undefined'){
                    _notifyContentChangeCallback();
                } else{
                    dtNews.fnDraw();
                }
            }
        <?php }?>

        <?php if(have_right('moderate_content')){?>
            var moderate_seller_news = function(obj){
                var $this = $(obj);
                var news = $this.data('news');

                $.ajax({
                    type: 'POST',
                    url: '<?php echo __SITE_URL?>seller_news/ajax_news_operations/moderate_news',
                    data: { news : news},
                    dataType: 'json',
                    success: function(data){
                        systemMessages( data.message, data.mess_type );

                        if(data.mess_type == 'success'){
                            $this.remove();
                        }
                    }
                });
            }
        <?php }?>

		var delete_news = function(opener){
			var $this = $(opener);
			var news = $this.data('news');

			$.ajax({
				type: 'POST',
				url: "<?php echo __SITE_URL ?>seller_news/ajax_news_operations/delete_news",
				dataType: "JSON",
				data: {news: news},
				success: function(resp) {
					systemMessages(resp.message, resp.mess_type);
					if (resp.mess_type == 'success'){
                        if(typeof dtNews === 'undefined'){
                            $this.closest('.spersonal-news__item').remove();
                            if($('#seller_news_block .spersonal-news__item').length < 1){
                                $('#seller_news_block').html('<div class="empty_news info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> <span><?php echo translate('seller_news_no_news_yet_message');?></span></div>');
                            }
                        } else{
                            dtNews.fnDraw();
                        }
					}
				}
			});
		}
	</script>
<?php }?>