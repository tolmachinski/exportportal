<?php

declare(strict_types=1);

namespace App\Common\Transformers;

use App\Filesystem\ItemPathGenerator;
use ExportPortal\Contracts\Filesystem\FilesystemOperator;
use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;
use League\Fractal\TransformerAbstract;

class ItemPickOfTheMonthForBaiduTransformer extends TransformerAbstract
{
    /**
     * The base URL.
     */
    private string $baseUrl;

    /**
     * The file storage.
     */
    private FilesystemOperator $storage;

    /**
     * @param string                      $baseUrl           the base URL
     * @param FilesystemProviderInterface $filesystePorvider The file storage provider
     */
    public function __construct(FilesystemProviderInterface $filesystePorvider, string $baseUrl)
    {
        $this->baseUrl = $baseUrl;
        $this->storage = $filesystePorvider->storage('public.storage');
    }

    public function transform(array $item)
    {
        return [
            'id'                  => $item['id'],
            'discount'            => $item['discount'],
            'price'               => $item['price'],
            'final_price'         => $item['final_price'],
            'title'               => $item['title'],
            'country'             => $item['country_name'],
            'link'                => makeItemUrl($item['id'], $item['title']),
            'country_flag'        => \sprintf('%s/%s', rtrim($this->baseUrl, '/'), \ltrim(getCountryFlag($item['country_name']), '/')),
            'photo'               => $this->storage->url(ItemPathGenerator::pickOfMonth(
                (int) $item['id'],
                !empty($item['photo_name']) ? $item['photo_name'] : 'no-image.jpg'
            )),
        ];
    }
}
