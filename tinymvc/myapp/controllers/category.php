<?php
use App\Messenger\Message\Command\SaveBuyerIndustryOfInterest;
use App\Common\Contracts\BuyerIndustries\CollectTypes;
use ExportPortal\Bridge\Symfony\Component\Messenger\MessengerInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;

use App\Common\Traits\Items\ProductCardPricesTrait;
use App\Filesystem\CategoryArticlesFilePathGenerator;
use App\Filesystem\CountryArticlesFilePathGenerator;
use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [15.12.2021]
 * Controller Refactoring
 */
class Category_Controller extends TinyMVC_Controller
{
    use ProductCardPricesTrait;

    private $breadcrumbs = [];

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
    }

    public function index()
    {
        $id_category = (int) $this->uri->segment(4);

        if (!$id_category || !model('category')->validate_category_id($id_category)) {
            show_404();
        }

        $data['categoryUriComponents'] = $category_uri_components = tmvc::instance()->site_urls['category/index']['replace_uri_components'];
        $data['metaData'] = $data['category'] = model('category')->get_category($id_category);

        model('elasticsearch_items')->get_items([
            'category'                      => $id_category,
            'page'                          => 1,
            'per_p'                         => 1,
            'featured_order'                => 1,
            'aggregate_category_counters'   => 1,
            'aggregate_id_category'         => $id_category,
        ]);

        if (!empty(model('elasticsearch_items')->aggregates['categories'])) {
            $data['categoryItemsCount'] = array_reduce(
                model('elasticsearch_items')->aggregates['categories'],
                function ($total, $item) {
                    $total += $item;

                    return $total;
                }
            );
        }

        $catlink = strForURL($data['category']['name']);
        $parent_cat = model('category')->get_category($data['category']['parent']);

        $category_image_header = true;

        if (2 == $data['category']['cat_type'] && !empty($parent_cat)) {// if is model
            $catlink = strForURL($parent_cat['name']) . '-' . $catlink;
        }

        $uri = array_filter($this->uri->uri_to_assoc(1, tmvc::instance()->route_url_segments));
        $uri[$category_uri_components['category']] = $catlink . '/' . $id_category;
        checkURI($uri, [$category_uri_components['category'], $catlink, $category_uri_components['country'], $category_uri_components['city'], $category_uri_components['page']]);

        $featured = 0;
        $highlighted = 0;
        $handmade = 0;

        $data['countries'] = model('country')->get_countries();
        $data['mainCats'] = model('category')->getCategories([
            'parent'  => 0,
            'columns' => 'category_id, name, parent, cat_type, is_restricted',
        ]);

        $subcat_cond['category'] = $id_category;
        $data['metaParams']['[TITLE]'] = $data['category']['name'];

        $main_cond['category'] = $id_category;
        $main_cond['is_restricted'] = $data['mainCats']['is_restricted'];

        $links_map = [
            $category_uri_components['category'] => [
                'type' => 'uri',
                'deny' => [$category_uri_components['page']],
            ],
            $category_uri_components['country'] => [
                'type' => 'uri',
                'deny' => [$category_uri_components['city'], $category_uri_components['page']],
            ],
            $category_uri_components['city'] => [
                'type' => 'uri',
                'deny' => [$category_uri_components['page']],
            ],
            $category_uri_components['page'] => [
                'type' => 'uri',
                'deny' => [$category_uri_components['page']],
            ],
            'per_p' => [
                'type' => 'get',
                'deny' => [$category_uri_components['page']],
            ],
            'sort_by' => [
                'type' => 'get',
                'deny' => [$category_uri_components['page']],
            ],
            'keywords' => [
                'type' => 'get',
                'deny' => [$category_uri_components['page']],
            ],
            'year_from' => [
                'type' => 'get',
                'deny' => [$category_uri_components['page']],
            ],
            'year_to' => [
                'type' => 'get',
                'deny' => [$category_uri_components['page']],
            ],
            'price_from' => [
                'type' => 'get',
                'deny' => [$category_uri_components['page']],
            ],
            'price_to' => [
                'type' => 'get',
                'deny' => [$category_uri_components['page']],
            ],
            'attributes' => [
                'type' => 'get',
                'deny' => [$category_uri_components['page']],
            ],
            'featured' => [
                'type' => 'get',
                'deny' => [$category_uri_components['page']],
            ],
            'highlighted' => [
                'type' => 'get',
                'deny' => [$category_uri_components['page']],
            ],
            'handmade' => [
                'type' => 'get',
                'deny' => [$category_uri_components['page']],
            ],
        ];

        $breadcrumbsLinksMap = [
            $category_uri_components['category'] => [
                'type' => 'uri',
                'deny' => [$category_uri_components['country'], $category_uri_components['city'], $category_uri_components['page']],
            ],
            $category_uri_components['country'] => [
                'type' => 'uri',
                'deny' => [$category_uri_components['city'], $category_uri_components['page']],
            ],
            $category_uri_components['city'] => [
                'type' => 'uri',
                'deny' => [$category_uri_components['page']],
            ],
            $category_uri_components['page'] => [
                'type' => 'uri',
                'deny' => [$category_uri_components['page']],
            ],
            'per_p' => [
                'type' => 'get',
                'deny' => [$category_uri_components['page']],
            ],
            'sort_by' => [
                'type' => 'get',
                'deny' => [$category_uri_components['page']],
            ],
            'keywords' => [
                'type' => 'get',
                'deny' => [$category_uri_components['page']],
            ],
            'year_from' => [
                'type' => 'get',
                'deny' => [$category_uri_components['page']],
            ],
            'year_to' => [
                'type' => 'get',
                'deny' => [$category_uri_components['page']],
            ],
            'price_from' => [
                'type' => 'get',
                'deny' => [$category_uri_components['page']],
            ],
            'price_to' => [
                'type' => 'get',
                'deny' => [$category_uri_components['page']],
            ],
            'attributes' => [
                'type' => 'get',
                'deny' => [$category_uri_components['page']],
            ],
            'featured' => [
                'type' => 'get',
                'deny' => [$category_uri_components['page']],
            ],
            'highlighted' => [
                'type' => 'get',
                'deny' => [$category_uri_components['page']],
            ],
            'handmade' => [
                'type' => 'get',
                'deny' => [$category_uri_components['page']],
            ],
        ];

        $search_params_links_map = [
            $category_uri_components['page'] => [
                'type' => 'get',
                'deny' => [$category_uri_components['page']],
            ],
            'sort_by' => [
                'type' => 'get',
                'deny' => ['sort_by', $category_uri_components['page']],
            ],
            'keywords' => [
                'type' => 'get',
                'deny' => ['keywords', $category_uri_components['page']],
            ],
            'year_from' => [
                'type' => 'get',
                'deny' => ['year_from', $category_uri_components['page']],
            ],
            'year_to' => [
                'type' => 'get',
                'deny' => ['year_to', $category_uri_components['page']],
            ],
            'year' => [
                'type' => 'get',
                'deny' => ['year_to', 'year_from', $category_uri_components['page']],
            ],
            'price_from' => [
                'type' => 'get',
                'deny' => ['price_from', $category_uri_components['page']],
            ],
            'price_to' => [
                'type' => 'get',
                'deny' => ['price_to', $category_uri_components['page']],
            ],
            'price' => [
                'type' => 'get',
                'deny' => ['price_to', 'price_from', $category_uri_components['page']],
            ],
            'attributes' => [
                'type' => 'get',
                'deny' => ['attributes', $category_uri_components['page']],
            ],
            'filter' => [
                'type' => 'get',
                'deny' => ['filter', 'keywords', 'year_from', 'year_to', 'price_from', 'price_to', 'attributes', 'featured', 'highlighted', 'handmade', $category_uri_components['page']],
            ],
            'featured' => [
                'type' => 'get',
                'deny' => ['featured', $category_uri_components['page']],
            ],
            'highlighted' => [
                'type' => 'get',
                'deny' => ['highlighted', $category_uri_components['page']],
            ],
            'handmade' => [
                'type' => 'get',
                'deny' => ['handmade', $category_uri_components['page']],
            ],
        ];

        $breadcrumbsLinksTpl = uri()->make_templates($breadcrumbsLinksMap, $uri);
        $data['linksTpl'] = $this->uri->make_templates($links_map, $uri);
        $data['searchParamsLinksTpl'] = $search_params_links_tpl = $this->uri->make_templates($search_params_links_map, $uri, true);

        $data['page'] = $main_cond['page'] = 1;

        if (!empty($_SERVER['QUERY_STRING'])) {
            $data['getParams'] = cleanOutput(cleanInput(arrayToGET($_GET)));
            $get_parameters = $_GET;
            foreach ($get_parameters as $key => $one_param) {
                $cleaned_value = cleanOutput(cleanInput($one_param));

                if ('' === $cleaned_value) {
                    continue;
                }

                $get_parameters[$key] = $cleaned_value;
            }
        }
        /** @var FilesystemProviderInterface $storageProvider */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $storage = $storageProvider->storage('public.storage');

        if (isset($uri[$category_uri_components['country']])) {
            $id_country = id_from_link($uri[$category_uri_components['country']]);
            $data['country'] = model('country')->get_country($id_country);
            if (empty($data['country'])) {
                show_404();
            }

            $main_cond['country'] = $id_country;
            $subcat_cond['country'] = $id_country;

            $featured = 0;
            $highlighted = 0;
            $handmade = 0;

            $this->load->model('country_articles_Model', 'country_articles');
            $this->load->model('Requirement_Model', 'requirement');

            /** @var Country_Article_Model $articleRepository */
            $articleRepository = model(Country_Article_Model::class);
            $data['articleInfo'] = $articleRepository->findAllBy([
                'scopes' => [
                    'countryId' => $id_country
                ],
                'with' => ['country'],
            ]);

            foreach ($data['articleInfo'] ?: [] as &$articleInfo) {
                $imageLink = CountryArticlesFilePathGenerator::mainImagePath((int) $articleInfo['id'], $articleInfo['photo']);

                if ($storage->fileExists($imageLink)) {
                    $articleInfo['photoLink'] = $storage->url($imageLink);
                }
            }

            $data['requirementInfo'] = model('requirement')->get_requirement(['country' => $id_country, 'visible' => 1]);

            if (!empty($data['requirementInfo'])) {
                $data['requirementInfo']['country_name'] = $data['country']['country'];
            }

            $category_image_header = false;
        }

        if (isset($uri[$category_uri_components['city']])) {
            $id_city = id_from_link($uri[$category_uri_components['city']]);
            $city_info = model('country')->get_city($id_city);

            if (empty($city_info)) {
                show_404();
            }
            $main_cond['city'] = $id_city;
            $subcat_cond['city'] = $id_city;
            $featured = 0;
            $highlighted = 0;
            $handmade = 0;

            $category_image_header = false;
        }

        $this->breadcrumbs[] = [
            'link'  => __SITE_URL . 'categories',
            'title' => 'All categories',
        ];
        $category_breadcrumbs = model('category')->breadcrumbs_tpl($id_category, $breadcrumbsLinksTpl[$category_uri_components['category']]);
        $this->breadcrumbs = array_merge($this->breadcrumbs, $category_breadcrumbs);

        if (have_right('sell_item')) {
            $data['linkAddItem'] = $id_category;
        }

        if (isset($uri[$category_uri_components['page']])) {
            $uri[$category_uri_components['page']] = (int) $uri[$category_uri_components['page']];
            if ($uri[$category_uri_components['page']] <= 0) {
                show_404();
            }

            $data['page'] = $main_cond['page'] = $uri[$category_uri_components['page']];
            $data['metaParams']['[PAGE]'] = $data['page'];

            $category_image_header = false;
        }

        $data['perPage'] = $main_cond['per_p'] = config('item_default_perpage', 15);

        if (!empty($get_parameters['featured'])) {
            $data['metaParams']['[FEATURED]'] = $data['featured'] = $main_cond['featured'] = 1;

            $category_image_header = false;
        }

        if (!empty($get_parameters['highlighted'])) {
            $data['metaParams']['[HIGHLIGHTED]'] = $data['highlight'] = $main_cond['highlight'] = 1;

            $category_image_header = false;
        }

        $data['sortByLinks'] = [
            'items' => [
                'rel-desc'         => 'Best match',
                'create_date-desc' => 'Newest',
                'create_date-asc'  => 'Oldest',
                'final_price-asc'  => 'Lowest price',
                'final_price-desc' => 'Highest price',
            ],
            'selected' => 'rel-desc',
            'default'  => 'rel-desc',
        ];

        // search params
        if (!empty($get_parameters['keywords'])) {
            $data['metaParams']['[KEYWORDS]'] = $data['filters']['keywords'] = cleanOutput($_GET['keywords']);
            $main_cond['keywords'] = cleanInput(cut_str($get_parameters['keywords']));
            $featured = 0;
            $highlighted = 0;
            $handmade = 0;

            $category_image_header = false;
        }

        if (!empty($get_parameters['sort_by']) && array_key_exists($get_parameters['sort_by'], $data['sortByLinks']['items']) && 'rel-desc' != $get_parameters['sort_by']) {
            $data['sortByLinks']['selected'] = $data['sortBy'] = $main_cond['sort_by'][] = $get_parameters['sort_by'];
            $data['metaParams']['[SORT_BY]'] = $data['sortByLinks']['items'][$data['sortBy']];

            $featured = 0;
            $highlighted = 0;
            $handmade = 0;

            $category_image_header = false;
        }

        //for most popular referral only
        if (!empty($get_parameters['sort_by']) && 'views-desc' == $get_parameters['sort_by']) {
            $data['sortBy'] = $main_cond['sort_by'][] = $get_parameters['sort_by'];
            $data['metaParams']['[SORT_BY]'] = 'Most popular';
            $main_cond['notOutOfStock'] = true;
        }

        if (!empty($get_parameters['year_from'])) {
            $data['metaParams']['[YEAR_FROM]'] = $data['filters']['year_from'] = $main_cond['year_from'] = (int) $get_parameters['year_from'];

            $featured = 0;
            $highlighted = 0;
            $handmade = 0;

            $category_image_header = false;
        }

        if (!empty($get_parameters['year_to'])) {
            $data['metaParams']['[YEAR_TO]'] = $data['filters']['year_to'] = $main_cond['year_to'] = (int) $get_parameters['year_to'];

            $featured = 0;
            $highlighted = 0;
            $handmade = 0;

            $category_image_header = false;
        }

        if (!empty($get_parameters['price_from'])) {
            $data['metaParams']['[PRICE_FROM]'] = $data['filters']['price_from'] = $main_cond['price_from'] = (int) $get_parameters['price_from'];

            $featured = 0;
            $highlighted = 0;
            $handmade = 0;

            $category_image_header = false;
        }

        if (!empty($get_parameters['price_to'])) {
            $data['metaParams']['[PRICE_TO]'] = $data['filters']['price_to'] = $main_cond['price_to'] = (int) $get_parameters['price_to'];

            $featured = 0;
            $highlighted = 0;
            $handmade = 0;

            $category_image_header = false;
        }

        if (!empty($get_parameters['featured'])) {
            $data['metaParams']['[FEATURED]'] = $data['filters']['featured'] = $main_cond['featured'] = (bool) $get_parameters['featured'];
            $featured = 1;

            $category_image_header = false;
        }

        if (!empty($get_parameters['highlighted'])) {
            $data['metaParams']['[HIGHLIGHTED]'] = $data['filters']['highlighted'] = $main_cond['highlight'] = (bool) $get_parameters['highlight'];
            $highlighted = 1;

            $category_image_header = false;
        }

        if (!empty($get_parameters['handmade'])) {
            $data['metaParams']['[HANDMADE]'] = $data['handmade'] = $data['filters']['handmade'] = $main_cond['handmade'] = (bool) $get_parameters['handmade'];
            $handmade = 1;

            $category_image_header = false;
        }

        // locations
        if (isset($id_country)) { //country
            $seo_loc['current'] = $data['country']['country'];
            $this->breadcrumbs[] = [
                'link'  => replace_dynamic_uri(strForURL($data['country']['country']) . '-' . $id_country, $breadcrumbsLinksTpl[$category_uri_components['country']]),
                'title' => $data['country']['country'],
            ];
        }

        if (!empty($seo_loc['current'])) {
            $data['metaParams']['[LOCATION]'] = $seo_loc['current'];
        }

        if (isset($id_city)) {
            $this->breadcrumbs[] = [
                'link'  => replace_dynamic_uri($uri[$category_uri_components['city']], $breadcrumbsLinksTpl[$category_uri_components['city']]),
                'title' => $city_info['city'],
            ];
            $seo_loc['current'] .= ', ' . $city_info['city'];
            $data['metaParams']['[CITY]'] = $city_info['city'];
        }

        $category_i18n = model('category')->get_category_i18n(['category_id' => $data['category']['category_id'], 'lang_category' => __SITE_LANG]);

        $data['metaData'] = [
            'base_seo' => empty($category_i18n) ? $data['category'] : $category_i18n,
            'seo_loc'  => $seo_loc,
        ];

        if (0 != $featured) {
            $main_cond['featured_order'] = 1;
        }

        if (0 != $highlighted) {
            $main_cond['highlight'] = 1;
        }

        if (0 != $handmade) {
            $main_cond['handmade'] = 1;
        }

        $main_cond['aggregate_category_counters'] = true;
        //HEREE
        if (empty($data['category']['cat_childrens']) && !empty($parent_cat)) {
            $is_last_category_level = true;
            $main_cond['aggregate_id_category'] = $parent_cat['category_id'];
            $data['parentCategory'] = $parent_cat;
        } else {
            $is_last_category_level = false;
            $main_cond['aggregate_id_category'] = $id_category;
            $data['parentCategory'] = $data['category'];
        }

        if (!isset($id_country)) {
            $main_cond['aggregate_countries_counters'] = true;
        } else {
            $main_cond['aggregate_cities_counters'] = true;
        }

        $main_cond['aggregate_attrs_select'] = true;

        model('elasticsearch_items')->get_items($main_cond);

        $data['items'] = model('elasticsearch_items')->items;

        $main_cond['count'] = $data['count'] = model('elasticsearch_items')->itemsCount;

        $items_country_ids = [];

        if (empty($data['items'])) {
            model('elasticsearch_items')->get_items([
                'per_p' => 6,
            ]);

            $data['itemsRecommended'] = $this->formatProductPrice(model('elasticsearch_items')->items);

            if (!empty($data['itemsRecommended'])) {
                $sellers_list = array_column($data['itemsRecommended'], 'id_seller', 'id_seller');
                $sellers = model('user')->getSellersForList(implode(',', $sellers_list), true);

                foreach ($data['itemsRecommended'] as $key => $item) {
                    $items_country_ids[$item['p_country']] = $item['p_country'];
                    $data['itemsRecommended'][$key]['seller'] = search_in_array($sellers, 'idu', $item['id_seller']);
                }
            }
        }

        foreach ($data['items'] as $item) {
            $items_country_ids[$item['p_country']] = $item['p_country'];
            $sellers_list[$item['id_seller']] = $item['id_seller'];
            $items_list[] = $item['id'];
        }

        if (!empty($items_country_ids)) {
            $data['items_country'] = model('country')->get_simple_countries(implode(',', $items_country_ids));
        }
        $attrs_select = model('elasticsearch_items')->aggregates['attrs_select'];

        if (!empty($attrs_select)) {
            foreach ($attrs_select as $attrs_select_key => $attrs_select_items_count) {
                $key_components = explode('_', $attrs_select_key);
                if (isset($data['attributes'][$key_components[1]]['attribute_values'][$key_components[3]])) {
                    $data['attributes'][$key_components[1]]['attribute_values'][$key_components[3]]['items_count'] = $attrs_select_items_count;
                }
            }
        }

        // locations
        $data['locations'] = [];
        if (!isset($id_country)) {
            if (!empty(model('elasticsearch_items')->aggregates['countries'])) {
                $countries_list = array_keys(model('elasticsearch_items')->aggregates['countries']);
                $countries = model('country')->get_simple_countries(implode(',', $countries_list));
                foreach ($countries as $country_info) {
                    $data['locations'][$country_info['id']] = [
                        'loc_id'    => $country_info['id'],
                        'loc_name'  => $country_info['country'],
                        'loc_type'  => 'country',
                        'loc_count' => model('elasticsearch_items')->aggregates['countries'][$country_info['id']],
                    ];
                }
            }
        } else {
            $location_sidebar_block = 'locations';
            if (isset($id_city)) {
                $location_sidebar_block = 'otherLocations';
                $location_siblings_cond = $main_cond;
                unset($location_siblings_cond['city']);
                model('elasticsearch_items')->get_items($location_siblings_cond);
            }

            if (!empty(model('elasticsearch_items')->aggregates['cities'])) {
                $cities_list = array_keys(model('elasticsearch_items')->aggregates['cities']);
                $cities = model('country')->get_cities_state(implode(',', $cities_list));
                foreach ($cities as $city_id => $city_name) {
                    if ($id_city == $city_id) {
                        $breadcrumb_city_data['loc_name'] = $city_name;

                        continue;
                    }

                    $data[$location_sidebar_block][$city_id] = [
                        'loc_id'    => $city_id,
                        'loc_name'  => $city_name,
                        'loc_type'  => 'city',
                        'loc_count' => model('elasticsearch_items')->aggregates['cities'][$city_id],
                    ];
                }
            }
        }

        $subcategories_sidebar_block = 'subcats';

        if ($is_last_category_level) {
            $subcategories_sidebar_block = 'otherCategories';
            $category_siblings_cond = $main_cond;
            $category_siblings_cond['category'] = $parent_cat['category_id'];

            model('elasticsearch_items')->get_items($category_siblings_cond);
        }

        if (!empty(model('elasticsearch_items')->aggregates['categories'])) {
            $subcategories_list = array_keys(model('elasticsearch_items')->aggregates['categories']);
            $subcategories = model('category')->getCategories(['columns' => 'category_id, name, p_or_m, cat_type', 'cat_list' => implode(',', $subcategories_list)]);
            foreach ($subcategories as $subcategory) {
                if ($subcategory['category_id'] == $data['category']['category_id']) {
                    continue;
                }
                $subcategory['counter'] = model('elasticsearch_items')->aggregates['categories'][$subcategory['category_id']];
                $data[$subcategories_sidebar_block][] = $subcategory;
            }
        }

        if (!empty($sellers_list)) {
            $sellers = model('user')->getSellersForList(implode(',', $sellers_list), true);
        }

        foreach ($data['items'] as $key => $item) {
            $data['items'][$key]['seller'] = search_in_array($sellers, 'idu', $item['id_seller']);
        }

        $data['items'] = $this->formatProductPrice($data['items']);

        //page link
        list($data['page_link'], $temp_page_link_get) = explode('?', get_dynamic_url($search_params_links_tpl[$category_uri_components['page']]));

        // PREPARE GET PARAMS FOR PER_PAGE_LINK, SORT_BY_LINK AND FEATURED LINK
        if (!empty($get_parameters['per_p'])) {
            $get_per_page = $get_parameters;
            unset($get_per_page['per_p']);
            $data['get_per_p'] = arrayToGET($get_per_page);
        } else {
            $data['get_per_p'] = arrayToGET($get_parameters);
        }

        if (!empty($get_parameters['sort_by'])) {
            $get_sort_by = $get_parameters;
            unset($get_sort_by['sort_by']);
            $data['get_sort_by'] = arrayToGET($get_sort_by);
        } else {
            $data['get_sort_by'] = arrayToGET($get_parameters);
        }

        if (!empty($get_parameters['featured'])) {
            $data['featuredLink'] = get_dynamic_url($search_params_links_tpl['featured']);
        } else {
            $data['featuredLink'] = replace_dynamic_uri(1, $data['linksTpl']['featured']);
        }

        list($data['sortby_link'], $data['sortby_link_get']) = explode('?', get_dynamic_url($search_params_links_tpl['sort_by']));
        $data['report_link'] = str_replace($category_uri_components['category'], 'report/category_report', replace_dynamic_uri($uri[$category_uri_components['category']], $data['linksTpl'][$category_uri_components['category']]));
        $data['makeFormLink'] = get_dynamic_url($search_params_links_tpl['filter']);

        if (!empty($get_parameters['keywords'])) {
            $data['searchParams'][] = [
                'link'       => get_dynamic_url($search_params_links_tpl['keywords']),
                'title'      => 'Keywords',
                'subParams'  => [
                    [
                        'link'  => get_dynamic_url($search_params_links_tpl['keywords']),
                        'title' => cleanOutput(cut_str($_GET['keywords'])),
                    ],
                ],
            ];
        }

        if (!empty($get_parameters['year_from']) || !empty($get_parameters['year_to'])) {
            $search_subparam_year = [];
            if (!empty($get_parameters['year_from'])) {
                $year_from = intval($get_parameters['year_from']);
                $search_subparam_year[] = [
                    'link'  => get_dynamic_url($search_params_links_tpl['year_from']),
                    'title' => 'from ' . $year_from,
                ];
                $data['metaParams']['[YEAR_FROM]'] = $year_from;
            }

            if (!empty($get_parameters['year_to'])) {
                $year_to = intval($get_parameters['year_to']);
                $search_subparam_year[] = [
                    'link'  => get_dynamic_url($search_params_links_tpl['year_to']),
                    'title' => 'to ' . $year_to,
                ];
                $data['metaParams']['[YEAR_TO]'] = $year_to;
            }

            $data['searchParams'][] = [
                'link'       => get_dynamic_url($search_params_links_tpl['year']),
                'title'      => 'Year',
                'subParams'  => $search_subparam_year,
            ];
        }

        if (!empty($get_parameters['price_from']) || !empty($get_parameters['price_to'])) {
            $search_subparam_price = [];
            if (!empty($get_parameters['price_from'])) {
                $price_from = get_price($get_parameters['price_from']);
                $search_subparam_price[] = [
                    'link'  => get_dynamic_url($search_params_links_tpl['price_from']),
                    'title' => 'from ' . $price_from,
                ];
                $data['metaParams']['[PRICE_FROM]'] = $price_from;
            }

            if (!empty($get_parameters['price_to'])) {
                $price_to = get_price($get_parameters['price_to']);
                $search_subparam_price[] = [
                    'link'  => get_dynamic_url($search_params_links_tpl['price_to']),
                    'title' => 'to ' . $price_to,
                ];
                $data['metaParams']['[PRICE_TO]'] = $price_to;
            }

            $data['searchParams'][] = [
                'title'      => 'Price',
                'subParams'  => $search_subparam_price,
            ];
        }

        if (logged_in()) {
            $saved_list = model('items')->get_items_saved(id_session());
            $data['savedItems'] = explode(',', $saved_list);
        }

        $data['breadcrumbs'] = $this->breadcrumbs;

        $data['catArticle'] = model('item_category_articles')->get_one_cat_art($id_category);
        if(!empty($data['catArticle'])){
            $imageLink = CategoryArticlesFilePathGenerator::mainImagePath($data['catArticle']['id'], $data['catArticle']['photo']);
            if($storage->fileExists($imageLink)){
                $data['catArticle']['photoLink'] = $storage->url($imageLink);
            }
        }

        $paginator_config = [
            'prefix'		      => "{$category_uri_components['page']}/",
            'base_url'      => $data['linksTpl'][$category_uri_components['page']],
            'first_url'     => get_dynamic_url($search_params_links_tpl[$category_uri_components['page']]),
            'replace_url'   => true,
            'total_rows'    => $main_cond['count'],
            'per_page'      => $data['perPage'],
            'cur_page'		=> $data['page'],
        ];

        library('pagination')->initialize($paginator_config);
        $data['pagination'] = library('pagination')->create_links();

        $data['list_item_grid'] = $this->cookies->getCookieParam('_product_view');
        if (empty($data['list_item_grid'])) {
            $this->cookies->setCookieParam('_product_view', 'grid');
            $data['list_item_grid'] = 'grid';
        }

        $data['sidebarContent'] = 'new/item/category/sidebar_view';
        $data['content'] = 'item/category/index_view';
        $data['styleCritical'] = 'category_simple';
        $data['customEncoreLinks'] = true;

        if ($category_image_header && empty($parent_cat)) {
            $data['categoryHeader'] = true;
            $data['styleCritical'] = 'category_img';
            $data['headerContent'] = 'new/item/category/image_header_view';
        }

        /** @var Buyer_Item_Categories_Stats_Model $buyerStatsModel */
        $buyerStatsModel = model(Buyer_Item_Categories_Stats_Model::class);

        if ((!logged_in() || is_buyer())
        && !empty($parent_cat)
        && !$buyerStatsModel->existsViewedToday($data['category']['category_id'], CollectTypes::CATEGORY_PAGE(), getEpClientIdCookieValue())
        ) {
            /** @var MessengerInterface $messenger */
            $messenger = container()->get(MessengerInterface::class);
            $messenger->bus('command.bus')->dispatch(
                new SaveBuyerIndustryOfInterest(
                    $data['category']['category_id'],
                    id_session(),
                    getEpClientIdCookieValue(),
                    CollectTypes::CATEGORY_PAGE()
                )
            );
        }

        views()->displayWebpackTemplate($data);
    }
}
