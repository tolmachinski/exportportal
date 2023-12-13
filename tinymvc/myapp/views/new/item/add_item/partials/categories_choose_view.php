<?php if(!empty($categories)){?>
	<div class="form-categories__toggle">
		<div class="form-categories__selected">
			<i class="ep-icon ep-icon_arrow-line-left call-function" data-callback="showFormCategories"></i>
			<div class="form-categories__item-name"></div>
		</div>

		<ul class="form-categories__list active" data-level="<?php echo $level?>" data-item="<?php echo $category;?>">
			<?php foreach($categories as $category) { ?>
				<li class="form-categories__item" data-id="<?php echo $category['category_id']?>"><?php echo $category['name']?></li>
			<?php } ?>
		</ul>
	</div>
<?php } ?>
