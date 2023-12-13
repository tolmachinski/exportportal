<?php if (!empty($suggestions)) {?>
    <?php foreach ($suggestions as $suggest) {?>
        <li
            class="autocomplete-suggestion-list__item call-action js-search-autocomplete-suggestions-list-item"
            data-js-action="autocomplete:suggestions-item.click"
            role="presentation"
        >
            <p class="autocomplete-suggestion-list__text js-search-autocomplete-suggestions-option" role="option">
                <?php echo $suggest; ?>
            </p>

            <!-- TODO: for additional autocomplete-->
            <!--<div class="autocomplete-suggestion-list__detail">
                in <a class="autocomplete-suggestion-list__link" href="#">Shoes and Accessories</a> /
                <a class="autocomplete-suggestion-list__link" href="#">Shoes</a>
            </div> -->
        </li>
    <?php }
}?>
