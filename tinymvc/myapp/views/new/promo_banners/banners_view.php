<?php
    if(!empty($banners)) {
        if(count($banners) > 1){
            encoreEntryScriptTags('promo_banner');
        }
?>
    <div class="js-<?php echo $mainClass; ?>-wr <?php echo $mainClass; ?>-wr <?php echo (!empty($bannerClass))? $bannerClass : ''; ?>" <?php echo addQaUniqueIdentifier("global__{$mainClass}-widget-banner"); ?>>
        <div class="js-<?php echo $mainClass; ?> <?php echo $mainClass; ?>" data-lazy-name="<?php echo $mainClass; ?>">
            <?php foreach ($banners as $bannerIndex => $bannersItem) { ?>
                <div class="<?php echo $mainClass; ?>__item">
                    <?php if ($bannersItem["will_open_popup"]) { ?>
                        <button
                            class="promo-banner__btn call-action <?php echo !empty($bannersItem['popup_legacy_action']) ? 'call-function' : ''; ?>"
                            data-href="<?php echo $bannersItem['link']; ?>"
                            data-js-action="<?php echo $bannersItem['popup_action']; ?>"
                            <?php if(!empty($bannersItem['popup_bg_path'])) { ?>
                            data-popup-bg="<?php echo asset("public/build/images/{$bannersItem['popup_bg_path']}"); ?>"
                            <?php } ?>
                            <?php if(!empty($bannersItem['popup_legacy_action'])) { ?>
                            data-callback="<?php echo $bannersItem['popup_legacy_action'];?>"
                            <?php } ?>
                            <?php echo addQaUniqueIdentifier('global__banner-btn'); ?>
                        >
                    <?php } elseif (!empty($bannersItem['link'])) { ?>
                        <a
                            class="<?php echo $mainClass; ?>__link"
                            href="<?php echo getBannerDynamicUrl((string) $bannersItem['alias'], $bannersItem['link']); ?>"
                            target="_blank"
                        >
                    <?php } else { ?>
                        <span
                            class="<?php echo $mainClass; ?>__link"
                        >
                    <?php } ?>
                        <picture
                            class="<?php echo $mainClass; ?>__picture"
                            <?php echo addQaUniqueIdentifier('global__banner-picture')?>
                        >
                            <?php
                                $bannerImagesSize = json_decode($bannersItem['image_size'], true);
                                $bannerImages = json_decode($bannersItem['image'], true);
                                $mainImages = $bannerImages['desktop'];
                                $mainImagesSize = $bannerImagesSize['desktop']['size'];
                                $modulePromoBanner = 'promo_banners.main';
                                unset($bannerImages['desktop']);

                                if(!empty($bannerImages)){
                                    foreach ($bannerImages as $bannerImagesKey => $bannerImagesItem) {
                                        if(!isset($bannerImagesSize[$bannerImagesKey])){
                                            continue;
                                        }

                                        $imageMedia = $bannerImagesSize[$bannerImagesKey]['media'];
                                        $imageSize = $bannerImagesSize[$bannerImagesKey]['size'];
                                        $imageBanner = getDisplayImageLink(
                                            ['{ID}' => $bannersItem['id_promo_banners'], '{FILE_NAME}' => $bannerImagesItem],
                                            $modulePromoBanner,
                                            ['no_image_group' => 'dynamic', 'image_size' => ['w' => $imageSize['w'], 'h' => $imageSize['h']]]
                                        );
                                        $imageMediaCss = [];

                                        foreach($imageMedia as $imageMediaKey => $imageMediaItem){
                                            $imageMediaCss[] = "({$imageMediaKey}:{$imageMediaItem}px)";
                                        }

                                        if ($firstContentPaint && $bannerIndex === 0) { ?>
                                            <source
                                                media="<?php echo implode(' and ', $imageMediaCss); ?>"
                                                srcset="<?php echo $imageBanner; ?>"
                                            >
                                            <?php if ($bannerImagesKey === "mobile") {
                                                $preloadedImageLink = $imageBanner;
                                            }
                                        } else { ?>
                                            <source
                                                media="<?php echo implode(' and ', $imageMediaCss); ?>"
                                                srcset="<?php echo getLazyImage($imageSize['w'], $imageSize['h']); ?>"
                                                data-srcset="<?php echo $imageBanner; ?>"
                                            >
                            <?php       }
                                    }
                                } ?>

                            <?php if ($firstContentPaint && $bannerIndex === 0) { ?>
                                <img class="<?php echo $mainClass; ?>__image"
                                    src="<?php echo getDisplayImageLink(
                                        ['{ID}' => $bannersItem['id_promo_banners'], '{FILE_NAME}' => $mainImages],
                                        $modulePromoBanner,
                                        ['no_image_group' => 'dynamic', 'image_size' => ['w' => $mainImagesSize['w'], 'h' => $mainImagesSize['h']]]
                                    );?>"
                                    alt="<?php echo $bannersItem['title'];?>"
                                >
                            <?php } else { ?>
                                <img class="<?php echo $mainClass; ?>__image js-lazy"
                                    data-src="<?php echo getDisplayImageLink(
                                        ['{ID}' => $bannersItem['id_promo_banners'], '{FILE_NAME}' => $mainImages],
                                        $modulePromoBanner,
                                        ['no_image_group' => 'dynamic', 'image_size' => ['w' => $mainImagesSize['w'], 'h' => $mainImagesSize['h']]]
                                    );?>"
                                    src="<?php echo getLazyImage($mainImagesSize['w'], $mainImagesSize['h']); ?>"
                                    alt="<?php echo $bannersItem['title'];?>"
                                >
                            <?php } ?>
                        </picture>

                        <?php if (isset($preloadedImageLink)) { ?>
                            <link rel="preload" href="<?php echo preload($preloadedImageLink, ['as' => 'image']); ?>" as="image">
                        <?php } ?>
                    <?php if ($bannersItem["will_open_popup"]) { ?>
                        </button>
                    <?php } elseif (!empty($bannersItem['link'])) { ?>
                        </a>
                    <?php } else { ?>
                        </span>
                    <?php } ?>

                </div>
            <?php } ?>
        </div>
    </div>
<?php } ?>
