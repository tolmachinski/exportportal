<?php if (!empty($breadcrumbs)) { ?>
    <?php
        $listItem = [];

        foreach ($breadcrumbs as $key => $item) {
            $link = $item['link'];

            if (!isset($breadcrumbs[$key + 1]) && empty($item['link'])) {
                $link = current_url();
            }

            array_push($listItem, [
                '@type'    => 'ListItem',
                'position' => $key,
                'item'     => [
                    '@id'  => $link,
                    'name' => $item['title'],
                ],
            ]);
        }

        $jsonLdArray = [
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => $listItem,
        ];
    ?>

    <div class="breadcrumbs">
        <ul id="js-breadcrumbs-wr" class="breadcrumbs__inner">
            <?php

            $default = [
                'link'  => __SITE_URL,
                'title' => translate('breadcrumb_home'),
            ];

            if (isset($breadcrumbs['home'])) {
                $default = $breadcrumbs['home'];
                unset($breadcrumbs['home']);
            }
            $countItems = count($breadcrumbs);

            if ($countItems > 1) {
                $out[] = '<li>
                    <a
                        class="breadcrumbs__referer"
                        href="' . $breadcrumbs[$countItems - 2]['link'] . '"
                    >
                        <span class="crafted-arrow left"></span>
                        <div class="txt">Back</div>
                    </a>
                </li>';
            }

            $out[] = '<li class="breadcrumbs__item js-breadcrumbs-item">
                        <a class="link" href="' . $default['link'] . '" title="' . cleanOutput($default['title']) . '" >' . $default['title'] . '</a>
                    </li>';
            $i = 0;

            foreach ($breadcrumbs as $key => $item) {
                $link = $item['link'];

                if (!isset($breadcrumbs[$key + 1]) && empty($item['link'])) {
                    $link = current_url();
                }

                if (++$i === $countItems) {
                    $out[] = '<li class="breadcrumbs__item' .
                                ((isset($item['hide_mobile'])) ? ' breadcrumbs__item--hide' : '') .
                                ((isset($item['max'])) ? ' breadcrumbs__item--max' : '') .
                                ' js-breadcrumbs-item"
                            >
                                <span class="link" title="' . cleanOutput($item['title']) . '">' .
                                cleanOutput($item['title']) . '</span>' .
                            '</li>';
                } else {
                    $out[] = '<li class="breadcrumbs__item' .
                                ((isset($item['hide_mobile'])) ? ' breadcrumbs__item--hide' : '') .
                                ((isset($item['max'])) ? ' breadcrumbs__item--max' : '') .
                                ' js-breadcrumbs-item"
                            >
                                <a class="link" href="' . $link . '" title="' . cleanOutput($item['title']) . '">' .
                                    ' <div class="txt">' . cleanOutput($item['title']) . '</div>
                                </a>
                            </li>';
                }
            }

            echo implode('', $out);

            ?>
        </ul>
    </div>

    <script type="application/ld+json">
        <?php echo json_encode($jsonLdArray, JSON_PRETTY_PRINT); ?>
    </script>
<?php } ?>
