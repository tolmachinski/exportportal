<div class="minfo-save-search">
	<div class="minfo-save-search__item">
		<span class="minfo-save-search__ttl">Sort by</span>
		<div class="dropdown show dropdown--select">
			<a class="dropdown-toggle" href="#" role="button" id="categorySortByLinks" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <?php echo $sort_by_links['items'][$sort_by_links['selected']];?>
				<i class="ep-icon ep-icon_arrow-down"></i>
			</a>

			<div class="dropdown-menu" aria-labelledby="categorySortByLinks">
				<?php foreach($sort_by_links['items'] as $sort_by_link_key => $sort_by_link){?>
					<a class="dropdown-item" href="<?php echo replace_dynamic_uri($sort_by_link_key, $links_tpl['sort_by']);?>"><?php echo $sort_by_link;?></a>
				<?php }?>
			</div>
		</div>
	</div>

	<div class="minfo-save-search__item">
	<?php if($count){?>
		<button
            class="minfo-save-search__btn fancybox.ajax fancyboxValidateModal"
            data-title="Save search"
            data-fancybox-href="<?php echo __SITE_URL . 'save_search/popup_save_search/directory/?curr_link=' . urlencode(__CURRENT_URL);?>"
            title="Save search"
        >
			<i class="ep-icon ep-icon_favorite-empty"></i>
			<span class="text">Save search</span>
		</button>
	<?php }else{?>
		<button
            class="minfo-save-search__btn call-systmess"
            data-message="<?php echo translate("systmess_error_nothing_found_at_your_request")?>"
            data-type="warning"
            title="Save search"
        >
			<i class="ep-icon ep-icon_favorite-empty"></i>
			<span class="text">Save search</span>
		</button>
	<?php }?>
	</div>
</div>

