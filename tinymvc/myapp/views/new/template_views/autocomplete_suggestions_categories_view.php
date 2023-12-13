<?php if (!empty($suggestions)) { ?>
    <?php foreach ($suggestions as $suggest) { ?>
        <li class="autocomplete-suggestion-list__item call-action js-search-autocomplete-suggestions-list-item" data-js-action="autocomplete:suggestions-item.click" role="presentation">
            <p class="autocomplete-suggestion-list__text js-search-autocomplete-suggestions-option" role="option">
                <?php echo $suggest['text']; ?>
            </p>
            <?php if (!empty($suggest['breadcrumbs'])) { ?>
                <div class="autocomplete-suggestion-list__detail">
                    in
                    <?php foreach ($suggest['breadcrumbs'] as $breadcrumb) { ?>
                        <a class="autocomplete-suggestion-list__link" href="<?php echo $breadcrumb['link']; ?>"><?php echo $breadcrumb['name']; ?></a>
                    <?php } ?>
                </div>
            <?php } ?>
        </li>
    <?php } ?>
<?php } ?>