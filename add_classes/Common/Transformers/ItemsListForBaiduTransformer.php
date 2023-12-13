<?php

declare(strict_types=1);

namespace App\Common\Transformers;

use League\Fractal\TransformerAbstract;

class ItemsListForBaiduTransformer extends TransformerAbstract
{
    /**
     * The base URL.
     */
    private string $baseUrl;

    /**
     * @param string $baseUrl the base URL
     */
    public function __construct(string $baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    public function transform(array $item)
    {
        return [
            'id'                  => $item['id'],
            'is_out_of_stock'     => $item['is_out_of_stock'],
            'samples'             => $item['samples'],
            'discount'            => $item['discount'],
            'price'               => $item['price'],
            'final_price'         => $item['final_price'],
            'title'               => $item['title'],
            'country_name'        => $item['country_name'],
            'origin_country_name' => $item['origin_country_name'],
            'card_prices'         => $item['card_prices'],
            'link'                => makeItemUrl($item['id'], $item['title']),
            'country_flag'        => \sprintf('%s/%s', rtrim($this->baseUrl, '/'), \ltrim(getCountryFlag($item['country_name']), '/')),
            'photo'               => getDisplayImageLink(
                [
                    '{ID}'        => $item['id'],
                    '{FILE_NAME}' => $item['photo_name'],
                ],
                'items.main',
                [
                    'thumb_size'     => 3,
                    'no_image_group' => 'dynamic',
                    'image_size'     => ['w' => 375, 'h' => 281],
                ]
            ), //TODO change to storage url when there will be path generator for products (items)
        ];
    }
}
