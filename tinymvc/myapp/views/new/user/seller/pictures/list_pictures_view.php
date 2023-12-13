<script>
	<?php if($seller_view && have_right('have_pictures')){?>
		function callbackEditSellerPictures(resp){
			$('#picture-'+resp.photo+'-block').find('.spersonal-pictures__ttl .link').html(resp.newTitle);
		}
	<?php }?>

	<?php if( $seller_view || have_right('moderate_content') ){?>
		var delete_picture_seller = function(obj){
			var $this = $(obj);
			var picture = $this.data('picture');

			$.ajax({
				type: 'POST',
				url: '<?php echo __SITE_URL?>seller_pictures/ajax_pictures_operation/delete_picture',
				data: { picture : picture},
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
	<?php }?>
</script>

<?php if(!empty($pictures)){?>
	<ul class="spersonal-pictures">
		<?php foreach($pictures as $item){?>
			<li class="spersonal-pictures__item hmedia" id="picture-<?php echo $item['id_photo'];?>-block" <?php echo addQaUniqueIdentifier('page__company-pictures__item'); ?>>
				<div class="spersonal-pictures__wr">
					<div class="spersonal-pictures__img image-card2">
						<a
							class="link fancyboxGallery"
							rel="photoItem"
							href="<?php echo $item['imageLink'];?>"
                            data-title="<?php echo $item['title_photo'];?>"
                        >
							<img
								class="image"
                                <?php echo addQaUniqueIdentifier('page__company-pictures__item-image'); ?>
                                src="<?php echo $item['imageThumbLink'];?>"
                                alt="<?php echo $item['title_photo'];?>"
                            />
						</a>
					</div>
					<div class="spersonal-pictures__desc">
						<div class="spersonal-pictures__top">
							<h4 class="spersonal-pictures__ttl fn">
								<a class="link" <?php echo addQaUniqueIdentifier('page__company-pictures__item-title'); ?> href="<?php echo $base_company_url;?>/picture/<?php echo strForUrl($item['title_photo']);?>-<?php echo $item['id_photo'];?>"><?php echo $item['title_photo'];?></a>
							</h4>

							<div class="dropdown">
								<a class="dropdown-toggle" <?php echo addQaUniqueIdentifier('page__company-pictures__item_dropdown-btn'); ?> data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#">
									<i class="ep-icon ep-icon_menu-circles"></i>
								</a>

								<div class="dropdown-menu dropdown-menu-right">
									<?php if(logged_in()){?>
                                        <button
                                            class="dropdown-item fancyboxValidateModal fancybox.ajax"
                                            <?php echo addQaUniqueIdentifier('page__company-pictures__item_dropdown-menu_share-btn'); ?>
                                            data-title="<?php echo translate('seller_pictures_share_this_text', null, true);?>"
                                            data-fancybox-href="<?php echo __SITE_URL;?>seller_pictures/popup_forms/share/<?php echo $item['id_photo'];?>"
                                            type="button"
                                        >
											<i class="ep-icon ep-icon_share-stroke"></i> <span><?php echo translate('seller_pictures_share_word');?></span>
										</button>
                                        <button
                                            class="dropdown-item fancyboxValidateModal fancybox.ajax"
                                            <?php echo addQaUniqueIdentifier('page__company-pictures__item_dropdown-menu_email-btn'); ?>
                                            data-title="<?php echo translate('seller_pictures_email_this_message', null, true);?>"
                                            data-fancybox-href="<?php echo __SITE_URL;?>seller_pictures/popup_forms/email/<?php echo $item['id_photo'];?>"
                                            type="button"
                                        >
											<i class="ep-icon ep-icon_envelope-send"></i> <span><?php echo translate('seller_pictures_email_word');?></span>
										</button>
										<?php if($seller_view && have_right('have_pictures') || have_right('moderate_content')){?>
											<?php if($seller_view && have_right('have_pictures') && !$item['moderated']){?>
                                                <button
                                                    class="dropdown-item fancybox.ajax fancyboxValidateModal"
                                                    <?php echo addQaUniqueIdentifier('page__company-pictures__item_dropdown-menu_edit-btn'); ?>
                                                    data-title="<?php echo translate('seller_pictures_edit_picture_message', null, true);?>"
                                                    title="<?php echo translate('seller_pictures_edit_picture_message', null, true);?>"
                                                    data-fancybox-href="<?php echo __SITE_URL;?>seller_pictures/popup_forms/edit_picture/<?php echo $item['id_photo'];?>"
                                                    type="button"
                                                >
                                                    <i class="ep-icon ep-icon_pencil"></i>
                                                    <?php echo translate('seller_pictures_edit_word');?>
                                                </button>
                                            <?php }?>

                                            <button
                                                class="dropdown-item confirm-dialog"
                                                <?php echo addQaUniqueIdentifier('page__company-pictures__item_dropdown-menu_remove-btn'); ?>
                                                data-callback="delete_picture_seller"
                                                data-picture="<?php echo $item['id_photo'];?>"
                                                data-message="<?php echo translate('seller_pictures_delete_picture_question', null, true);?>"
                                                title="<?php echo translate('seller_pictures_delete_picture_message', null, true);?>"
                                                type="button"
                                            >
												<i class="ep-icon ep-icon_trash-stroke"></i>
												<?php echo translate('seller_pictures_remove_word');?>
											</button>
										<?php }?>
									<?php }else{?>
                                        <button
                                            class="dropdown-item call-systmess"
                                            <?php echo addQaUniqueIdentifier('page__company-pictures__item_dropdown-menu_share-this-btn'); ?>
                                            data-message="<?php echo translate('seller_updates_not_logged_in_error_text', null, true);?>"
                                            data-type="error"
                                            title="<?php echo translate('seller_pictures_share_this_text', null, true);?>"
                                            type="button"
                                        >
											<i class="ep-icon ep-icon_share-stroke"></i>  <?php echo translate('seller_pictures_share_this_text');?>
										</button>
                                        <button
                                            class="dropdown-item call-systmess"
                                            <?php echo addQaUniqueIdentifier('page__company-pictures__item_dropdown-menu_email-this-btn'); ?>
                                            data-message="<?php echo translate('seller_updates_not_logged_in_error_text', null, true);?>"
                                            data-type="error"
                                            title="<?php echo translate('seller_pictures_email_this_message', null, true);?>"
                                            type="button"
                                        >
											<i class="ep-icon ep-icon_envelope"></i> <?php echo translate('seller_pictures_email_this_message');?>
										</button>
									<?php }?>
								</div>
							</div>
						</div>

						<div class="spersonal-pictures__bottom">
							<div class="spersonal-pictures__category" <?php echo addQaUniqueIdentifier('page__company-pictures__item-category'); ?>><?php echo $item['category_title'];?></div>

							<a class="spersonal-pictures__comment fancybox.ajax fancyboxValidateModal" <?php echo addQaUniqueIdentifier('page__company-pictures__item-comments'); ?> data-title="<?php echo translate('general_button_add_comment_text', null ,true);?>" href="<?php echo __SITE_URL;?>seller_pictures/popup_forms/add_comment/<?php echo $item['id_photo'];?>">
								<span <?php echo addQaUniqueIdentifier('page__company-pictures__item-comments-count'); ?>><?php echo $item['comments_count'];?></span>
								<i class="ep-icon ep-icon_comments-stroke"></i>
							</a>
						</div>
					</div>
				</div>
			</li>
		<?php }?>
	</ul>
	<?php if ($count_pictures > count($pictures) && !isset($pagination)) {?>
		<div class="flex-display flex-jc--c">
			<a class="btn btn-outline-dark btn-block mw-280" href="<?php echo $more_pictures_btn_link;?>"><?php echo translate('general_view_more_btn');?></a>
		</div>
	<?php }?>
<?php }else{?>
	<div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> <?php echo translate('seller_pictures_no_pictures_found_message');?></div>
<?php }?>
