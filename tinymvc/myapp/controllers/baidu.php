<?php

declare(strict_types=1);

use App\Common\Transformers\CompanyPickOfTheMonthForBaiduTransformer;
use App\Common\Transformers\ItemPickOfTheMonthForBaiduTransformer;
use App\Common\Transformers\ItemsCompilationsForBaiduTransformer;
use App\DataProvider\IndexedBlogDataProvider;
use App\DataProvider\IndexedProductDataProvider;
use Spatie\Fractalistic\Fractal;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Controller Baidu.
 */
class Baidu_Controller extends TinyMVC_Controller
{
    private IndexedProductDataProvider $indexedProductDataProvider;
    private IndexedBlogDataProvider $indexedBlogDataProvider;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->indexedProductDataProvider = $container->get(IndexedProductDataProvider::class);
        $this->indexedBlogDataProvider = $container->get(IndexedBlogDataProvider::class);
    }

    /**
     * Index page.
     */
    public function index()
    {
        $authHeader = request()->headers->get('authorization');
        if (!isset($authHeader) || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches) || config('env.BAIDU_API_TOKEN') !== $matches[1]) {
            return new JsonResponse('', 404);
        }

        return new JsonResponse([
            'blogs'              => $this->getBlogs(),
            'items-latest'       => $this->getLatestItems(),
            'items-popular'      => $this->getMostPopularItems(),
            'items-featured'     => $this->getFeaturedItems(),
            'items-compilation'  => $this->getCompilations(),
            'picks-of-the-month' => $this->getPickOfTheMonth(),
        ]);
    }

    private function getLatestItems()
    {
        return $this->indexedProductDataProvider->getLatestItemsForBaidu(
            (int) config('home_latest_items_per_page', 12),
            config('latest_items_by_period')
                ? (new DateTime())->sub(new DateInterval('P' . config('latest_items_period', 1) . 'D'))->format('Y-m-d')
                : null
        );
    }

    private function getMostPopularItems()
    {
        return $this->indexedProductDataProvider->getPopularItemsForBaidu((int) config('home_popular_items_per_page', 12));
    }

    private function getFeaturedItems()
    {
        return $this->indexedProductDataProvider->getFeaturedItemsForBaidu((int) config('home_featured_items_per_page', 12));
    }

    private function getPickOfTheMonth()
    {
        //TODO change this to a provider here and on home page too  (needs refactoring)
        /** @var Pick_Of_The_Month_Company_Model $pickCompanyModel */
        $pickCompanyModel = model(Pick_Of_The_Month_Company_Model::class);

        /** @var Pick_Of_The_Month_Item_Model $pickItemModel */
        $pickItemModel = model(Pick_Of_The_Month_Item_Model::class);

        /** @var Elasticsearch_Items_Model $elasticsearchItemsModel */
        $elasticsearchItemsModel = model(Elasticsearch_Items_Model::class);

        if (!empty($pickCompany = $pickCompanyModel->findOneBy(['conditions' => ['dateBetween' => new DateTime()]]))) {
            /** @var Elasticsearch_Company_Model $elasticCompany */
            $elasticCompany = model(Elasticsearch_Company_Model::class);

            $elasticCompany->get_companies([
                'list_company_id' => $pickCompany['id_company'],
                'per_p'           => 1,
            ]);
            $elasticCompanies = $elasticCompany->records ?: [];
            $companyPickOfTheMonth = array_pop($elasticCompanies);
        }

        if (!empty($pickItem = $pickItemModel->findOneBy(['conditions' => ['dateBetween' => new DateTime()]]))) {
            $elasticsearchItemsModel->get_items([
                'list_item' => [$pickItem['id_item']],
                'per_p'     => 1,
            ]);

            $elasticItems = $elasticsearchItemsModel->items_records ?: [];
            $itemPickOfTheMonth = array_pop($elasticItems);
        }

        return [
            'item'    => !empty($itemPickOfTheMonth)
                ? Fractal::create()
                    ->item($itemPickOfTheMonth)
                    ->transformWith($this->getContainer()->get(ItemPickOfTheMonthForBaiduTransformer::class))
                    ->toArray()['data']
                : [],
            'company' => !empty($companyPickOfTheMonth)
                ? Fractal::create()
                    ->item($companyPickOfTheMonth)
                    ->transformWith($this->getContainer()->get(CompanyPickOfTheMonthForBaiduTransformer::class))
                    ->toArray()['data']
                : [],
        ];
    }

    private function getBlogs()
    {
        return $this->indexedBlogDataProvider->getBlogsForHomePageBaidu();
    }

    private function getCompilations()
    {
        //TODO change this to a provider here and on home page too (needs refactoring)
        /** @var Items_Compilation_Model $itemsCompilationModel */
        $itemsCompilationModel = model(Items_Compilation_Model::class);

        /** @var Elasticsearch_Items_Model $elasticsearchItemsModel */
        $elasticsearchItemsModel = model(Elasticsearch_Items_Model::class);

        $itemsCompilations = $itemsCompilationModel->findAllBy([
            'with'          => ['itemsRelations'],
            'exists'        => [
                $itemsCompilationModel->getRelationsRuleBuilder()->has('itemsRelations'),
            ],
            'scopes'    => [
                'isPublished' => 1,
            ],
            'limit'         => 3,
        ]);

        if (empty($itemsCompilations)) {
            return [];
        }

        foreach ($itemsCompilations as $key => $itemsCompilation) {
            $items = $elasticsearchItemsModel->get_items([
                'random_score'  => true,
                'list_item'     => array_column($itemsCompilation['items_relations']->toArray(), 'id'),
                'per_p'         => 4,
            ]);

            foreach ($items as $item) {
                if (!empty($item['photo_name'])) {
                    $itemsCompilations[$key]['items'][] = [
                        'id'        => (int) $item['id'],
                        'title'     => $item['title'],
                        'photo'     => getDisplayImageLink(['{ID}' => $item['id'], '{FILE_NAME}' => $item['photo_name']], 'items.main', ['thumb_size' => 2]),
                    ];
                }
            }
        }

        return Fractal::create()
            ->collection($itemsCompilations)
            ->transformWith(new ItemsCompilationsForBaiduTransformer())
            ->toArray()['data'];
    }
}
// End of file baidu.php
// Location: /tinymvc/myapp/controllers/baidu.php
