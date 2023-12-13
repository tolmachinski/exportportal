<?php

use const App\Common\Autocomplete\TYPE_ITEMS;

use App\DataProvider\IndexedProductDataProvider;
use App\Common\Traits\Items\ProductCardPricesTrait;
use App\Filesystem\CountryArticlesFilePathGenerator;
use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [18.12.2021]
 * Controller Refactoring
 */
class Search_Controller extends TinyMVC_Controller
{

    use ProductCardPricesTrait;

    private IndexedProductDataProvider $indexedProductDataProvider;

    private $breadcrumbs = [];

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->indexedProductDataProvider = $container->get(IndexedProductDataProvider::class);
    }

    private function load_main(){
        $this->load->model('Category_Model', 'category');
        $this->load->model('Country_Model', 'country');
        $this->load->model('Items_Model', 'items');
    }

    function index() {
		$pageUriComponents = tmvc::instance()->site_urls['search/index']['replace_uri_components'];
        $uri = array_filter(uri()->uri_to_assoc(3, tmvc::instance()->route_url_segments));

        $links_map = [
            $pageUriComponents['page'] => [
                'type' => 'uri',
                'deny' => [$pageUriComponents['page']],
            ],
            'per_p' => [
                'type' => 'get',
                'deny' => ['per_p', $pageUriComponents['page']],
            ],
            'sort_by' => [
                'type' => 'get',
                'deny' => [$pageUriComponents['page'], 'sort_by'],
            ],
            'keywords' => [
                'type' => 'get',
                'deny' => [$pageUriComponents['page'], $pageUriComponents['country'], 'keywords', 'sort_by'],
            ],
            'featured' => [
                'type' => 'get',
                'deny' => [$pageUriComponents['page'], 'featured'],
            ],
            'recommended' => [
                'type' => 'get',
                'deny' => [$pageUriComponents['page'], 'recommended'],
            ],
            $pageUriComponents['country'] => [
                'type' => 'uri',
                'deny' => [$pageUriComponents['country'], $pageUriComponents['page']],
            ],
            $pageUriComponents['category'] => [
                'type' => 'uri',
                'deny' => [$pageUriComponents['category'], $pageUriComponents['page'], 'recommended'],
            ],
            'search_form' => [
                'type' => 'uri',
                'deny' => [$pageUriComponents['page'], $pageUriComponents['category'], $pageUriComponents['country'], 'keywords', 'search_form'],
            ],
        ];

        //if the URL contains not allowed uri segments we will show 404
        checkURI($uri, [$pageUriComponents['page'], $pageUriComponents['country'], $pageUriComponents['category'], 'search_form']);

        $links_tpl_without = $this->uri->make_templates($links_map, $uri, true);
        $links_tpl = $this->uri->make_templates($links_map, $uri);

        $data = [
            'metaParams' => [
                '[TITLE_KEYWORDS]' => '',
                '[TITLE_LOCATION]' => '',
                '[DESCRIPTION_KEYWORDS]' => '',
                '[DESCRIPTION_LOCATION]' => '',
                '[KEYWORDS]' => '',
            ],
            'search_uri_components' => $pageUriComponents,
        ];

        $issetFilterByRecommended = 1 == request()->query->getInt('recommended');

        /** @var User_Model $userModel */
        $userModel = model(User_Model::class);
        /** @var Elasticsearch_Items_Model $elasticsearchItemsModel */
        $elasticsearchItemsModel = model(Elasticsearch_Items_Model::class);

        $this->load_main();

        if ($issetFilterByRecommended) {
            if (!logged_in()) {
                session()->setMessages(translate('systmess_info_search_log_into_buyer_account'), 'info');
                headerRedirect(__SITE_URL . 'login');
            }

            if (!is_buyer()) {
                session()->setMessages(translate('systmess_info_search_log_into_buyer_account'), 'info');
            } else {

                /** @var \Buyer_Item_Categories_Stats_Model $buyerStats */
                $buyerStats = model(\Buyer_Item_Categories_Stats_Model::class);

                $industriesOfInterest = $buyerStats->getUserRelationIndustries(id_session());
                $itemsIndustriesIds = array_column($industriesOfInterest, 'id_category');
            }
        }

        // breadcrumbs
        $this->breadcrumbs[]= array(
            'link'     => __SITE_URL . 'search/',
            'title'    => 'Search'
        );

        $data['sortByLinks'] = [
            'items' => [
                'rel-desc'  => 'Best match',
                'create_date-desc' => 'Newest',
                'create_date-asc' => 'Oldest',
                'final_price-asc' => 'Lowest price',
                'final_price-desc' => 'Highest price'
            ],
            'selected' => 'rel-desc',
            'default' => 'rel-desc'
        ];

        $data['mainCats'] = $this->category->getCategories(array(
            'parent' => 0,
            'columns' => 'category_id, name, parent'
        ));

        $data['countries'] = $this->country->get_countries();

        $select_cond = array();
        $data['page'] = 1;
        $data['perPage'] = $select_cond['per_p'] = config('item_default_perpage', 15);

        if (isset($_GET['sort_by']) && isset($data['sortByLinks']['items'][$_GET['sort_by']]) && $_GET['sort_by'] != 'rel-desc') {
            $data['sortByLinks']['selected'] = $data['sortBy'] = $select_cond['sort_by'][] = cleanInput($_GET['sort_by']);
            $data['metaParams']['[SORT_BY]'] = $data['sortByLinks']['items'][$data['sortBy']];
        }

        //for most popular referral only
        if(!empty(request()->query->get('sort_by')) &&request()->query->get('sort_by') == 'views-desc'){
            $data['sortBy'] = $select_cond['sort_by'][] = request()->query->get('sort_by');
            $data['metaParams']['[SORT_BY]'] = 'Most popular';
            $select_cond['notOutOfStock'] = true;
        }

        if(isset($_GET['featured'])) {
            $data['metaParams']['[FEATURED]'] = $data['featured'] = $select_cond['featured'] = 1;
        } else {
            $data['featured'] = 0;
        }

        if(isset($uri['page'])) {
            $data['page'] = $select_cond['page'] = $uri['page'];
            $data['metaParams']['[PAGE]'] = intVal($uri['page']);
        }

        $exceptkeys = array();

        if(isset($uri[$pageUriComponents['country']])){
            $select_cond['country'] = id_from_link($uri[$pageUriComponents['country']]);
            $data['countrySelected'] = $uri[$pageUriComponents['country']];

            $country_info = $this->country->get_country($select_cond['country']);

            if (empty($country_info) || strForURL("{$country_info['country']} {$country_info['id']}") !== $uri[$pageUriComponents['country']]) {
                show_404(); // bad url
            }
        } else{
            $data['countrySelected'] = '';
        }

        if (!empty($_SERVER['QUERY_STRING'])){
            $data['get_params'] = cleanOutput(cleanInput(arrayToGET($_GET)));
            $get_parameters = array_diff_key($_GET, array_flip($exceptkeys));

            foreach($get_parameters as $key => $one_param){
                $get_parameters[$key] = cleanOutput(cleanInput($one_param));
            }
        }

        if (!empty($_GET['keywords'])) {
            library(TinyMVC_Library_Search_Autocomplete::class)->handleSearchRequest(request(), 'keywords', TYPE_ITEMS);
            model(Search_Log_Model::class)->log($clean_keywords = cut_str($_GET['keywords']));


            $select_cond['keywords'] = $clean_keywords;
            $data['keywords'] = cleanOutput(cut_str(decodeUrlString($_GET['keywords'])));
            $data['metaParams']['[TITLE_KEYWORDS]'] = 'for ' . $data['keywords'];
            $data['metaParams']['[DESCRIPTION_KEYWORDS]'] = $data['keywords'];
            $data['metaParams']['[KEYWORDS]'] = $data['keywords'];

        }

        $select_cond["aggregate_category_counters"] = true;
        $select_cond["aggregate_countries_counters"] = true;

        $data['items'] = [];
        $data['count'] = 0;
        if (!empty($_GET['keywords']) || !empty($country_info) || !empty($itemsIndustriesIds ?? null)) {
            $select_cond['categories'] = $itemsIndustriesIds ?? null;
            $elasticsearchItemsModel->get_items($select_cond);
            $countries_counters = $elasticsearchItemsModel->aggregates['countries'];
            $countries_ids = array_keys($countries_counters);
            if(!empty($countries_ids) && !$data["countrySelected"]) {
                $data['searchCountries'] = [];
                $search_countries = $this->country->get_simple_countries(implode(",", $countries_ids));
                foreach($search_countries as $country) {
                    $country['loc_name'] = $country['country_name'];
                    $country['loc_type'] = "country";
                    $country['loc_id'] = $country['id'];
                    $country['loc_count'] = $countries_counters[$country['loc_id']];
                    $data['searchCountries'][] = $country;
                }
            }

            $categories = $elasticsearchItemsModel->aggregates['categories'];
            $category_keys = '';
            $categories_counters = [];
            foreach($categories as $category => $count) {
                $explode = explode(',', $category);
                $end = end($explode);
                $category_keys .= ',' . $end;
                $categories_counters[$end] = $count;
            }

            if(!empty($category_keys)) {
                $mysql_categories = $this->category->getCategories(array("cat_list" => substr($category_keys, 1)));
                foreach($mysql_categories as &$mysql_category) {
                    $mysql_category['counter'] = $categories_counters[$mysql_category["category_id"]];
                }
                $data['counterCategories'] = $this->category->_categories_map($mysql_categories);
            }

            $data['items'] = $elasticsearchItemsModel->items;
            // de([
            //     'params'        => $select_cond,
            //     'count'         => count($elasticsearchItemsModel->items),
            //     'items_count'   => $elasticsearchItemsModel->itemsCount
            // ]);
            $data['count'] = $elasticsearchItemsModel->itemsCount;

            if (!empty($data['items'])) {
                $sellers_list = array_column($data['items'], 'id_seller', 'id_seller');

                if (!empty($sellers_list)) {
                    $sellers = $userModel->getSellersForList(implode(',',$sellers_list), true);
                }

                $items_country_ids = array();

                foreach($data['items'] as $key => $item){
                    $data['items'][$key]['seller'] = search_in_array($sellers, 'idu', $item['id_seller']);
                    $items_country_ids[$item['p_country']] = $item['p_country'];
                }

                $data['items'] = $this->formatProductPrice($data['items']);

                if (!empty($items_country_ids)) {
                    $data['itemsCountry'] = model('country')->get_simple_countries(implode(",", $items_country_ids));
                }
            }
        }

        if(empty($data['items'])) {
            $data['mostPopularItems'] = $this->indexedProductDataProvider->getItemsByCriteria(
                [
                    'featured'      => false,
                    'notOutOfStock' => true,
                    'per_p'			=> 8,
                    'sort_by'       => ['views-desc']
                ]
            );
            $itemsCount = count($data['mostPopularItems']);

            if ($itemsCount > 4 && $itemsCount % 2 !== 0) {
                array_pop($data['mostPopularItems']);
                $itemsCount--;
            }

            $data['latestItems'] = $this->indexedProductDataProvider->getItemsByCriteria(
                [
                    'featured'      => false,
                    'notOutOfStock' => true,
                    'per_p'			=> 8,
                    'sort_by'       => ['create_date-desc']
                ]
            );
            $itemsCount = count($data['latestItems']);

            if ($itemsCount > 4 && $itemsCount % 2 !== 0) {
                array_pop($data['latestItems']);
                $itemsCount--;
            }

            $data['featuredItems'] = $this->indexedProductDataProvider->getItemsByCriteria(
                [
                    'featured'      => true,
                    'notOutOfStock' => true,
                    'per_p'			=> 8,
                    'sort_by'       => ['featured_from_date-desc']
                ]
            );
            $itemsCount = count($data['featuredItems']);

            if ($itemsCount > 4 && $itemsCount % 2 !== 0) {
                array_pop($data['featuredItems']);
                $itemsCount--;
            }
        }

        $returnToPage = request()->query->get('returnToPage');
        $referer = str_replace([parse_url(request()->server->get('HTTP_REFERER'), PHP_URL_QUERY), '?'], '', request()->server->get('HTTP_REFERER'));
        $data['searchParamsLinksTpl']['filter'] = !empty($returnToPage) && __CURRENT_URL_NO_QUERY !== $referer ? str_replace(__SITE_URL, '', request()->server->get('HTTP_REFERER')) : 'search';

        $data['linksTpl'] = $links_tpl;
        $data['linksTpl']['sort_by'] = 'search/' . $data['linksTpl']['sort_by'];
        $data['categoryLink'] = $links_tpl_without[$pageUriComponents['category']];
        $data['searchFormLink'] = $links_tpl_without['search_form'];
        $data['countryLink'] = get_dynamic_url($links_tpl[$pageUriComponents['country']], 'search');

        list($data['page_link'], $data['get_per_p']) = explode('?', get_dynamic_url('search' . '/' . $links_tpl['per_p']));
        list($data['curr_link'], $data['get_sort_by']) = explode('?', get_dynamic_url('search' . '/' . $links_tpl['sort_by']));

        $data['featuredLink'] = get_dynamic_url('search' . '/' . $links_tpl_without['featured']);
        if(empty($_GET['featured'])) {
            if(strpos($data['featuredLink'], '?') === false) {
                $data['featuredLink'] .= '?featured=1';
            } else {
                $data['featuredLink'] .= '&featured=1';
            }
        }

        if (!empty($_GET['keywords'])) {
            $this->breadcrumbs[] = array(
                'link'    => get_dynamic_url('search' . '/' . $links_tpl_without[$pageUriComponents['country']]),
                'title'    => 'For: '. cleanOutput(cut_str(decodeUrlString($_GET['keywords'])))
            );

            $data['searchParams'][] = [
                'title' => 'Keywords',
                'subParams' => [
                    [
                        'link' => get_dynamic_url('search' . '/' . $links_tpl_without['keywords']),
                        'title' => cleanOutput(decodeUrlString($_GET['keywords'])),
                    ]
                ]
            ];
        }

        if (!empty($itemsIndustriesIds ?? null)) {
            $data['searchParams'][] = [
                'title' => 'Recommended',
                'subParams' => [
                    [
                        'link' => get_dynamic_url('search' . '/' . $links_tpl_without['recommended']),
                        'title' => 'Yes',
                    ]
                ]
            ];
        }

        if (isset($uri[$pageUriComponents['country']])) {
            $this->load->model('country_articles_Model', 'country_articles');
            $this->load->model('Requirement_Model', 'requirement');

            $data['searchParams'][] = [
                'title'     => 'Country',
                'subParams' => [
                    [
                        'link' => get_dynamic_url('search' . '/' . $links_tpl_without[$pageUriComponents['country']]),
                        'title' => $country_info['country'],
                    ]
                ]
            ];

            $this->breadcrumbs[]= array(
                'link'     => get_dynamic_url('search' . '/' . $links_tpl_without[$pageUriComponents['category']]),
                'title'    => "In: {$country_info['country']}",
            );
            $params_article_info = array('country' => id_from_link($uri[$pageUriComponents['country']]));

            /** @var Country_Article_Model $articleRepository */
            $articleRepository = model(Country_Article_Model::class);
            $data['articleInfo'] = $articleRepository->findOneBy(['scopes' => ['countryId' => id_from_link($uri[$pageUriComponents['country']])]]);

            if(!empty($data['articleInfo'])){
                /** @var FilesystemProviderInterface $storageProvider */
                $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                $storage = $storageProvider->storage('public.storage');
                $imageLink = CountryArticlesFilePathGenerator::mainImagePath($data['articleInfo']['id'], $data['articleInfo']['photo']);
                if($storage->fileExists($imageLink)){
                    $data['articleInfo']['photoLink'] = $storage->url($imageLink);
                }
            }
            $params_article_info['visible'] = 1;
            $data['requirementInfo']= $this->requirement->get_requirement($params_article_info);

            if (!empty($data['requirementInfo'])) {
                $data['requirementInfo']['country_name'] = $country_info['country'];
            }

            $data['metaParams']['[TITLE_LOCATION]'] = "in {$country_info['country']}";
            $data['metaParams']['[DESCRIPTION_LOCATION]'] = "in {$country_info['country']}";
        }

        if (logged_in()) {
            $saved_list = $this->items->get_items_saved(id_session());

            $data['savedItems'] = explode(',', $saved_list);
        }

        $data['breadcrumbs'] = $this->breadcrumbs;

        $paginator_config = array(
            'base_url'      => get_dynamic_url($links_tpl[$pageUriComponents['page']], 'search'),
            'first_url'     => get_dynamic_url($links_tpl_without[$pageUriComponents['page']], 'search'),
            'total_rows'    => $data['count'],
            'per_page'      => $data['perPage'],
			'cur_page'		=> $data['page'],
            'replace_url'   => true
        );

        if( !$this->is_pc ){
			$paginator_config['last_link'] = false;
			$paginator_config['first_link'] = false;
        }

        $this->load->library('Pagination', 'pagination');
        $this->pagination->initialize($paginator_config);
        $data['pagination'] = $this->pagination->create_links();
        $data['sidebarContent'] = 'new/search/sidebar_view';
        $data['content'] = 'search/index_view';
        $data['styleCritical'] = $data['count'] ? 'search' : 'search_empty';
        $data['customEncoreLinks'] = true;

        views()->displayWebpackTemplate($data);
    }
}
