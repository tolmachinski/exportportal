<?php

declare(strict_types=1);

namespace App\DataProvider;

use DateTime;
use Products_Model;
use App\Common\Database\Model;
use Elasticsearch_Items_Model;
use Spatie\Fractalistic\Fractal;
use App\Common\Traits\Items\ProductCardPricesTrait;
use App\Common\Transformers\ItemsListForBaiduTransformer;
use App\Common\Transformers\ItemsToElasticsearchTransformer;

/**
 * The products data provider service.
 *
 * @author Vlad Afanasenco
 */
final class IndexedProductDataProvider
{
    use ProductCardPricesTrait;

    private Products_Model $productsModel;

    private Elasticsearch_Items_Model $elasticStorage;

    private ItemsListForBaiduTransformer $itemTransformer;

    /**
     * Construct class.
     */
    public function __construct(
        Model $productsModel,
        Elasticsearch_Items_Model $elasticStorage,
        ItemsListForBaiduTransformer $itemTransformer
    )
    {
        $this->productsModel = $productsModel;
        $this->elasticStorage = $elasticStorage;
        $this->itemTransformer = $itemTransformer;
    }

    /**
     * Get items for slider just for you.
     */
    public function getJustForYouItems(int $userId, array $userStatsCategories, int $perPage = 12): array
    {
        return $this->formatProductPrice(
            $this->elasticStorage->get_items([
                'random_score'        => true,
                'categories'          => $userStatsCategories,
                'list_exclude_seller' => $userId,
                'per_p'               => $perPage,
            ])
        );
    }

    /**
     * Get featured items for slider.
     *
     * @param mixed $usersModel
     */
    public function getFeaturedItems(int $perPage = 12, $usersModel): array
    {
        $items = $this->getFeaturedItemsFromIndex($perPage);

        $sellersIds = array_column($items, 'id_seller', 'id_seller');
        $sellers = array_column(
            empty($sellersIds) ? [] : $usersModel->getSellersForList($sellersIds, true),
            null,
            'idu'
        );

        foreach ($items as $index => $featuredItem) {
            $items[$index]['seller'] = $sellers[$featuredItem['id_seller']];
        }

        return $this->formatProductPrice($items);
    }

    /**
     * Get popular items transformed for baidu.
     */
    public function getFeaturedItemsForBaidu(int $perPage)
    {
        return Fractal::create()
            ->collection(
                $this->formatProductPrice(
                    $this->getFeaturedItemsFromIndex($perPage)
                )
            )
            ->transformWith($this->itemTransformer)
            ->toArray()['data'];
    }

    /**
     * Get popular items.
     */
    public function getPopularItems(int $perPage = 12, ?bool $isHandmade = false): array
    {
        return $this->formatProductPrice($this->getPopularItemsFromIndex($perPage, $isHandmade));
    }

    /**
     * Get popular items transformed for baidu.
     */
    public function getPopularItemsForBaidu(int $perPage = 12): array
    {
        return Fractal::create()
            ->collection(
                $this->formatProductPrice(
                    $this->getPopularItemsFromIndex($perPage)
                )
            )
            ->transformWith($this->itemTransformer)
            ->toArray()['data']
        ;
    }

    /**
     * Get latest items.
     *
     * @param mixed $usersModel
     */
    public function getLatestItems($usersModel, int $perPage = 12, ?DateTime $startFrom = null, bool $handmade = false): array
    {
        $items = $this->getLatestItemsFromIndex($perPage, $startFrom, $handmade);

        $sellersIds = array_column($items, 'id_seller', 'id_seller');
        $sellers = array_column(
            empty($sellersIds) ? [] : $usersModel->getSellersForList($sellersIds, true),
            null,
            'idu'
        );

        foreach ($items as $index => $latestItem) {
            $items[$index]['seller'] = $sellers[$latestItem['id_seller']];
        }

        return $this->formatProductPrice($items);
    }

    public function getLatestItemsForBaidu(int $perPage, ?DateTime $startFrom = null): array
    {
        return Fractal::create()
            ->collection($this->getLatestItemsFromIndex($perPage, $startFrom))
            ->transformWith($this->itemTransformer)
            ->toArray()['data'];
    }

    /**
     * Get basket items.
     */
    public function getBacketItems(array $data, int $perPage = 12): array
    {
        return $this->formatProductPrice(
            $this->elasticStorage->get_items([
                'list_exclude_item' => array_column($data, 'id_item'),
                'notOutOfStock'     => true,
                'per_p'             => $perPage,
                'categories'        => array_column($data, 'id_cat'),
                'sort_by'           => [
                    'create_date-desc',
                ],
            ])
        );
    }

    /**
     * Get blog items.
     */
    public function getBlogsItems(int $perPage = 12, int $featured = 1, bool $collapseBySeller = false): array
    {
        return $this->formatProductPrice(
            $this->elasticStorage->get_items(
                [
                    'per_p'        => $perPage,
                    'featured'     => $featured,
                    'random_score' => true,
                ] + ($collapseBySeller ? ['collapse_by_seller' => true] : [])
            )
        );
    }

    /**
     * Get bloggers items.
     */
    public function getBloggersItems(int $perPage = 12): array
    {
        return $this->formatProductPrice(
            $this->elasticStorage->get_items([
                'per_p'         => $perPage,
                'accreditation' => 1,
            ])
        );
    }

    /**
     * Get categories items.
     */
    public function getCategoriesItems(int $perPage = 12, int $featured, int $notOutOfStock, string $sort, string $ordering): array
    {
        return $this->formatProductPrice(
            $this->elasticStorage->get_items([
                'per_p'         => $perPage,
                'featured'      => $featured,
                'notOutOfStock' => $notOutOfStock,
                'sort_by'       => [
                    $sort . '-' . $ordering,
                ],
            ])
        );
    }

    /**
     * Get items by array.
     */
    public function getItemsByCriteria(array $cond): array
    {
        return $this->formatProductPrice($this->elasticStorage->get_items($cond));
    }

    /**
     * Get preview items.
     */
    public function getPreviewItems(?int $itemId, int $categoryId, int $perPage = 12): array
    {
        return $this->formatProductPrice(
            $this->elasticStorage->get_items([
                'list_exclude_item' => empty($itemId) ? null : [$itemId],
                'notOutOfStock'     => true,
                'per_p'	            => $perPage,
                'category'          => $categoryId,
                'sort_by'           => [
                    'create_date-desc',
                ],
            ])
        );
    }

    /**
     * Prepare single item for Elastic.
     */
    public function getItemForIndex(int $id): ?array
    {
        return array_shift($this->prepareItemsForElastic($id));
    }

    /**
     * Prepare items for elastic reindex.
     *
     * @param mixed $id
     *
     * @return array
     */
    public function prepareItemsForElastic($id = null): ?array
    {
        return Fractal::create()
            ->collection(
                $this->productsModel->getItemsForElastic($id)
            )
            ->transformWith(new ItemsToElasticsearchTransformer(
                library(\TinyMVC_Library_Elasticsearch::class),
                model(\Items_Variants_Model::class)
            ))
            ->toArray()['data']
        ;
    }

    /**
     * Primary query to elastic to get featured items.
     */
    private function getFeaturedItemsFromIndex(int $perPage)
    {
        return $this->elasticStorage->get_items([
            'featured'  => 1,
            'per_p'     => $perPage,
            'sort_by'   => [
                'featured_from_date-desc',
            ],
        ]) ?? [];
    }

    private function getPopularItemsFromIndex(int $perPage, bool $onlyHandmade = false)
    {
        $params = [
            'notOutOfStock' => 1,
            'featured'      => 0,
            'per_p'         => $perPage,
            'sort_by'       => [
                'views-desc',
            ],
        ];
        if ($onlyHandmade) {
            $params['is_handmade'] = 1;
        }

        return $this->elasticStorage->get_items($params) ?? [];
    }

    private function getLatestItemsFromIndex(int $perPage, ?DateTime $startFrom = null, bool $onlyHandmade = false): array
    {
        $params = [
            'start_from'    => $startFrom,
            'per_p'         => $perPage,
            'sort_by'       => [
                'create_date-desc',
            ],
        ];
        if ($onlyHandmade) {
            $params['is_handmade'] = 1;
        }

        return $this->elasticStorage->get_items($params) ?? [];
    }
}
