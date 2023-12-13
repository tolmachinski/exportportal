<div class="minfo-sidebar-mlist__sub">
	<?php foreach($subcats as $subitem){?>
			<a class="minfo-sidebar-box-multilist__sub-item" href="<?php echo 'category/' . strForURL($subitem['name']) . '/' . $subitem['category_id'] . '/' . $category_link;?>">
				<span class="minfo-sidebar-box-multilist__sub-link" <?php echo addQaUniqueIdentifier('global__sidebar-toggled-category')?>>
					<i class="ep-icon ep-icon_minus"></i>
					<span><?php echo $subitem['name']; ?></span>
				</span>
				<span class="minfo-sidebar-box-multilist__sub-counter"  <?php echo addQaUniqueIdentifier('global__sidebar-toggled-counter')?>>(<?php echo $subitem['counter']; ?>)</span>
			</a>
			<?php if(isset($subitem['subcats'])){
				tmvc::instance()->controller->_categories_tree_sidebar_new($subitem['subcats']);
			} ?>
	<?php }?>
</div>
