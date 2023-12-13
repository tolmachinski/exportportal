<div class="js-category-search-all category-fast-search search-btn-input input-group">
    <input
        class="search-btn-input__txt form-control"
        type="text"
        name="keywords"
        maxlength="50"
        value="<?php echo $keywords; ?>"
        placeholder="Keyword"
        <?php echo addQaUniqueIdentifier("items-my-add__search-category")?>
    >
	<div class="input-group-append search-btn-input__subbmit">
		<img class="js-search-category-loader category-fast-search__loader display-n" src="<?php echo __SITE_URL;?>public/img/loader.svg" alt="loader">
		<a <?php echo addQaUniqueIdentifier("items-my-add__search-category-button")?> class="js-search-category ep-icon ep-icon_magnifier" href="#"></a>
	</div>
	<input type="hidden" name="op" value="search">

	<div class="category-fast-search__result display-n"></div>
</div>
