<div class="container-center dashboard-container">
	<div class="row">
		<div class="col-12 col-md-10 col-md-offset-1">
			<div class="dashboard-line">
				<h1 class="dashboard-line__ttl"><?php echo $document['menu_title'];?></h1>
			</div>

			<?php if(!empty($document['menu_breadcrumbs']) && count($document['menu_breadcrumbs']) > 1){?>
                <p class="epdoc-description-bread epdoc-description-bread--raquo clearfix">
                    <strong class="epdoc-description-bread__ttl">Previous sections:</strong>
                    <?php foreach ($document['menu_breadcrumbs'] as $bread){
                            foreach ($bread as $id_menu => $menu_title){
                                echo '<a class="epdoc-description-bread__link fancybox fancybox.ajax" href="' . __SITE_URL . 'user_guide/popup_forms/show_doc/' . $id_menu . '?user_type=' . $user_type . '" data-title="' . $menu_title . '" title="' . $menu_title . '">'.$menu_title.'</a>';
                            }
                        }?>
                </p>
			<?php }?>

			<?php if(!empty($document_childrens)){?>
                <p class="epdoc-description-bread clearfix">
                    <strong class="epdoc-description-bread__ttl">Sub sections:</strong>
                    <?php foreach ($document_childrens as $children){
                            echo '<a class="epdoc-description-bread__link fancybox fancybox.ajax" href="'.__SITE_URL.'user_guide/popup_forms/show_doc/' . $children['id_menu'] . '?user_type=' . $user_type . '" data-title="' . $children['menu_title'] . '" title="' . $children['menu_title'] . '">'.$children['menu_title'].'</a>';
                        }?>
                </p>
			<?php }?>

			<div class="ep-tinymce-text pt-30">
				<?php echo $document['menu_description'];?>
			</div>
		</div>
	</div>

</div>
