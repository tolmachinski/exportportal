<div class="wr-modal-flex mnh-50">
	<div class="modal-flex__form">
		<div class="modal-flex__content mnh-300 mh-500">
			<div class="ep-tinymce-text">
				<?php echo $document['menu_description'];?>
			</div>
		</div>
		<div class="modal-flex__btns">
			<?php if(!empty($document['menu_breadcrumbs']) && count($document['menu_breadcrumbs']) > 1){?>
			<p class="epdoc-description-bread epdoc-description-bread--raquo clearfix">
				<strong class="epdoc-description-bread__ttl">Previous sections:</strong>
				<?php foreach ($document['menu_breadcrumbs'] as $bread){
						foreach ($bread as $id_menu => $menu_title){
							echo '<a class="epdoc-description-bread__link fancybox fancybox.ajax" href="'.__SITE_URL.'user_guide/popup_forms/show_doc/' . $id_menu . '?user_type='.$user_type.'" data-title="' . $menu_title . '" title="' . $menu_title . '">'.$menu_title.'</a>';
						}
					}?>
			</p>
			<?php }?>

			<?php if(!empty($document_childrens)){?>
			<p class="epdoc-description-bread clearfix">
				<strong class="epdoc-description-bread__ttl">Sub sections:</strong>
				<?php foreach ($document_childrens as $children){
						echo '<a class="epdoc-description-bread__link fancybox fancybox.ajax" href="'.__SITE_URL.'user_guide/popup_forms/show_doc/' . $children['id_menu'] . '?user_type='.$user_type.'" data-title="' . $children['menu_title'] . '" title="' . $children['menu_title'] . '">'.$children['menu_title'].'</a>';
					}?>
			</p>
			<?php }?>
<!--
		<div class="col-3 lh-40 tar h-40">
			<?php //if($user_type == 'buyer' && !empty($document['menu_video_buyer'])){?>
				<a class="btn btn-primary" href="<?php echo $document['menu_video_buyer'];?>" target="_blank">
					<i class="ep-icon ep-icon_play"></i>
					Video guide
				</a>
			<?php //}elseif($user_type == 'seller' && !empty($document['menu_video_seller'])){?>
				<a class="btn btn-primary" href="<?php echo $document['menu_video_seller'];?>" target="_blank">
					<i class="ep-icon ep-icon_play"></i>
					Video guide
				</a>
			<?php //}elseif($user_type == 'shipper' && !empty($document['menu_video_shipper'])){?>
				<a class="btn btn-primary" href="<?php echo $document['menu_video_shipper'];?>" target="_blank">
					<i class="ep-icon ep-icon_play"></i>
					Video guide
				</a>
			<?php //}?>
		</div>
-->
		</div>
	</div>
</div>