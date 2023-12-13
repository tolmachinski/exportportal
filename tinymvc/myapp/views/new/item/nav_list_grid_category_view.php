<div class="minfo-save-search">
	<div class="minfo-save-search__item">
		<span class="minfo-save-search__ttl"> Featured </span>

		<div class="dropdown show dropdown--select">
			<a class="dropdown-toggle dropdown-toggle--center" href="#" role="button" id="categoryFeaturedLinks" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
				<?php if(!$featured){?>
					Off
				<?php } else{?>
					On
				<?php }?>
                <?php echo getEpIconSvg('arrow-down', [9, 9]);?>
			</a>

			<div class="dropdown-menu" aria-labelledby="categoryFeaturedLinks">
				<a class="dropdown-item" href="<?php echo $featuredLink;?>">
					<?php if(!$featured){?>
						On
					<?php } else{?>
						Off
					<?php }?>
				</a>
			</div>
		</div>
	</div>

	<div class="minfo-save-search__item">
		<span class="minfo-save-search__ttl">Sort by</span>
		<div class="dropdown show dropdown--select">
			<a class="dropdown-toggle dropdown-toggle--center" href="#" role="button" id="categorySortByLinks" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
				<?php echo $sortByLinks['items'][$sortByLinks['selected']];?>
                <?php echo getEpIconSvg('arrow-down', [9, 9]);?>
			</a>

			<div class="dropdown-menu" aria-labelledby="categorySortByLinks">
				<?php foreach($sortByLinks['items'] as $sortByLinkKey => $sortByLink){?>
					<?php $linkKey = (isset($sortByLinks['default']) && $sortByLinks['default'] === $sortByLinkKey) ? '' : $sortByLinkKey;?>
					<a class="dropdown-item" href="<?php echo replace_dynamic_uri($linkKey, $links_tpl['sort_by']);?>"><?php echo $sortByLink;?></a>
				<?php }?>
			</div>
		</div>
	</div>

	<div class="minfo-save-search__item">
		<button
            class="<?php echo logged_in() ? '' : 'js-require-logged-systmess ';?>minfo-save-search__btn fancybox.ajax fancyboxValidateModal"
            data-title="Save search"
            data-fancybox-href="<?php echo __SITE_URL . 'save_search/popup_save_search/category/?curr_link=' . urlencode(__CURRENT_URL);?>"
            title="Save search"
            type="button"
        >
            <?php echo getEpIconSvg('favorite-empty', [18, 18]);?>
			<span class="text">Save search</span>
		</button>
	</div>
</div>

