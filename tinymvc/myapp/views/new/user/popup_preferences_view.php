<?php
    $useGoogleTranslate = (bool) (int) config('env.USE_ONLY_GOOGLE_TRANSLATE');
?>

<div id="popup-preferences-content" class="notranslate" style="display: none;">
    <div class="wr-modal-flex inputs-40">
        <form class="user-preferences-form modal-flex__form validateModal" data-js-action="toolbar:set-user-preferences" data-callback="changeUserPreferences">
            <div class="modal-flex__content">
                <div class="form-group">
                    <label class="input-label">Language</label>
                    <select class="validate[required] notranslate" name="language" no-translate>
                        <?php foreach ($tlanguages as $tlanguageKey => $tlanguage) { ?>
                            <?php $lang = strtoupper(cleanOutput($tlanguage['lang_iso2'])) . '&nbsp;&nbsp; | &nbsp;&nbsp;' . $tlanguage['lang_name_original']; ?>
                            <?php if ('domain' === $tlanguage['lang_url_type'] || (($tlanguage['is_translated_page'] ?? false) && !$useGoogleTranslate)) { ?>
                                <option
                                    value="<?php echo cleanOutput($tlanguage['lang_iso2']); ?>"
                                    <?php echo ($currentLangIso2 == $tlanguage['lang_iso2']) ? 'selected' : '';?>
                                    data-translate="default"
                                    data-redirect="<?php echo get_dynamic_url($langUrls[$tlanguage['lang_iso2']], $siteUrl, false);?>"
                                    data-lang="<?php echo cleanOutput($tlanguage['lang_iso2']); ?>"
                                >
                                    <?php echo $lang; ?>
                                </option>
                            <?php } else { ?>
                                <option
                                    value="<?php echo cleanOutput($tlanguage['lang_iso2']); ?>"
                                    <?php echo ($currentLangIso2 == $tlanguage['lang_iso2']) ? 'selected' : '';?>
                                    data-translate="google"
                                    data-redirect="<?php echo get_dynamic_url($langUrls['en'], $siteUrl, false);?>"
                                    data-lang="<?php echo cleanOutput($tlanguage['lang_iso2']); ?>"
                                >
                                    <?php echo $lang; ?>
                                </option>
                            <?php } ?>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="input-label input-label--info input-label--required">
                        <span>Currency</span>
                        <a
                            class="info-dialog ep-icon ep-icon_info pl-5 txt-dec-none"
                            data-message="<?php echo translate('header_navigation_preferences_currency_info_text', ['[[BR]]' => '<br>', '[[LINK]]' => '<a class="no-underline" href="https://currencylayer.com/" target="_blank">currencylayer.com</a>'], true); ?>"
                            data-title="<?php echo translate('header_navigation_link_currency_title', null, true); ?>"
                            data-keep-modal="true"
                            href="#">
                        </a>
                    </label>
                    <select class="validate[required] notranslate" name="currency">
                        <?php foreach ($currencyes as $curr) { ?>
                                <option value="<?php echo $curr['code']; ?>" <?php echo cookies()->getCookieParam('currency_key') === $curr['code'] ? 'selected' : ''; ?>>
                                    <?php echo '&nbsp;'. $curr['curr_entity'] . '&nbsp;&nbsp;&nbsp; | &nbsp; &nbsp;' . $curr['code']; ?>
                                </option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <div class="modal-flex__btns">
                <div class="modal-flex__btns-right">
                    <button class="btn btn-primary" type="submit">Submit</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php echo dispatchDynamicFragmentInCompatMode(
    'popup:preferences-form',
    asset('public/plug/js/preferences/popup_preferences.js', 'legacy'),
    sprintf(
        'function () { new callUserPreferences(%s, %s, %s, %s, true); }',
        (bool) (int) $connectGtrans ? 'true' : 'false',
        $domainLangs,
        $googleLangs,
        $availableLangs
    ),
    [
        (bool) (int) $connectGtrans ? 'true' : 'false',
        json_decode($domainLangs, true),
        json_decode($googleLangs, true),
        json_decode($availableLangs, true),
        true
    ],
    true
); ?>
