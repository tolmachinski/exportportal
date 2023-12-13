<?php

use Symfony\Component\DependencyInjection\ContainerInterface;
use App\DataProvider\IndexedProductDataProvider;


class HandMade_Controller extends TinyMVC_Controller
{
    private IndexedProductDataProvider $indexedProductDataProvider;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->indexedProductDataProvider = $container->get(IndexedProductDataProvider::class);
    }

    /**
     * Index method
     */
    public function index(): void
    {
        show_404();
    }

    /**
     * Get items for slider with popular handmade items
     */
    public function ajax_get_popular ()
    {
        checkIsAjax();

        $popularItems = $this->indexedProductDataProvider->getPopularItems(
            config('home_popular_items_per_page', 12), true
        );

        $itemsCount = count($popularItems);
        if ($itemsCount > 4 && $itemsCount % 2 !== 0) {
            array_pop($popularItems);
            $itemsCount--;
        }

        jsonResponse('', 'success',
            [
                'itemsCount' => $itemsCount,
                'items'      => !empty($popularItems) ? views()->fetch(
                    'new/item/list_item_view',
                    [
                        'view_key'          => null,
                        'items'             => $popularItems,
                        'has_hover'         => false,
                        'has_mobile_seller' => false,
                        'savedItems'        => []
                    ]
                ) : ''
            ]
        );
    }

    /**
     * Get items for slider with latest handmade items
     */
    public function ajax_get_latest_items()
    {
        checkIsAjax();

        $latestItems = $this->indexedProductDataProvider->getLatestItems(
            model(\User_Model::class),
            config('latest_items_handmade_slider', 26),
            config('latest_items_by_period') ? (new DateTime())->sub(new DateInterval('P' . config('latest_items_period', 1) . 'D'))->format('Y-m-d') : null,
            true
        );

        if (empty($latestItems)) {
            jsonResponse('', 'success', ['items' => '']);
        }

        $itemsCount = count($latestItems);
        if ($itemsCount > 4 && $itemsCount % 2 !== 0) {
            array_pop($latestItems);
            $itemsCount--;
        }

        jsonResponse('', 'success',
            [
                'itemsCount' => $itemsCount,
                'items'      => !empty($latestItems) ? views()->fetch(
                    'new/item/list_item_view',
                    [
                        'items'             => $latestItems,
                        'has_hover'         => false,
                        'has_mobile_seller' => true,
                        'savedItems'        => []
                    ]
                ) : ''
            ]
        );
    }
}
