<div itemscope itemtype="http://schema.org/ItemList">
	<div class="display-n">
		<span itemprop="numberOfItems"><?php echo count($companies_list); ?></span>
	</div>

	<?php if(!empty($companies_list)){?>
		<ul class="companies">
			<?php foreach($companies_list as $key => $item){?>
				<li class="companies-wr" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
					<?php views()->display('new/directory/list_item_view', array('item' => $item));?>
					<div class="display-n">
						<span itemprop="position"><?php echo $key;?></span>
					</div>
				</li>
			<?php }?>
		</ul>
	<?php } else{?>
		<?php if($companies_not_cheerup){?>
			<div class="w-100pr doc-info-b mb-10">
				<div class="info-alert-b">
					<i class="ep-icon ep-icon_info-stroke"></i><?php echo translate('seller_partners_no_companies');?>
				</div>
			</div>
		<?php }else{?>
  			<?php views()->display('new/search/cheerup_view'); ?>
		<?php }?>
	<?php }?>
</div>
