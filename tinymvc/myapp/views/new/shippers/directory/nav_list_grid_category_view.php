<?php
    $sort_array = array(
        'date_asc' => 'Oldest',
        'date_desc' => 'Newest',
        'title_asc' => 'Title A-Z',
        'title_desc' => 'Title Z-A',
    );
?>
<div class="minfo-save-search">
	<div class="minfo-save-search__item">
		<span class="minfo-save-search__ttl">Sort by</span>
		<div class="dropdown show dropdown--select">
			<a class="dropdown-toggle" href="#" role="button" id="categorySortByLinks" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
				<?php echo $sort_array[$sort_by];?>
				<i class="ep-icon ep-icon_arrow-down"></i>
			</a>

			<div class="dropdown-menu" aria-labelledby="categorySortByLinks">
                <?php foreach ($sort_array as $key => $item) { ?>
                    <a href="<?php echo $curr_link; ?>?sort_by=<?php echo $key; ?>" class="dropdown-item"><?php echo $item; ?></a>
                <?php } ?>
			</div>
		</div>
	</div>

	<div class="minfo-save-search__item">
	<?php if($count){?>
		<button
            class="minfo-save-search__btn fancybox.ajax fancyboxValidateModal"
            data-title="Save search"
            data-fancybox-href="<?php echo __SITE_URL . 'save_search/popup_save_search/shippers/?curr_link=' . urlencode(__CURRENT_URL);?>"
            title="Save search"
            type="button"
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
            type="button"
        >
			<i class="ep-icon ep-icon_favorite-empty"></i>
			<span class="text">Save search</span>
		</button>
	<?php }?>
	</div>
</div>
