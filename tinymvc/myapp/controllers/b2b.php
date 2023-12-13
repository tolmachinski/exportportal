<?php

use App\Common\Buttons\ChatButton;
use App\Common\Contracts\B2B\B2bRequestLocationType;
use App\Common\Contracts\Media\CompanyLogoThumb;
use App\Common\Database\Exceptions\WriteException;
use App\Common\DependencyInjection\ServiceLocator\LibraryLocator;
use App\Common\Exceptions\B2bRequestNotFoundException;
use App\Common\Exceptions\QueryException;
use App\Common\Http\Request;
use App\DataProvider\IndexedProductDataProvider;
use App\Common\Traits\Items\ProductCardPricesTrait;
use App\Common\Validation\ConstraintViolationInterface;
use App\Common\Validation\Legacy\ValidatorAdapter;
use App\Common\Validation\Standalone\AggregateValidator;
use App\Common\Validation\ValidationException;
use App\DataProvider\B2bIndexedRequestProvider;
use App\DataProvider\B2bRequestProvider;
use App\Email\EmailFriendAboutB2b;
use App\Email\ResetPasswordEmail;
use App\Filesystem\B2bRequestFilePathGenerator;
use App\Filesystem\CompanyLogoFilePathGenerator;
use App\Filesystem\FilePathGenerator;
use App\Messenger\Message\Command\ElasticSearch\IndexB2bRequest;
use App\Messenger\Message\Command\ElasticSearch\ReIndexB2bRequest;
use App\Plugins\EPDocs\NotFoundException;
use App\Services\B2b\B2bRequestProcessingService;
use App\Services\Company\CompanyGuardService;
use App\Validators\B2bRequestPartnerCountriesValidator;
use App\Validators\B2bRequestRadiusValidator;
use App\Validators\B2bRequestValidator;
use ExportPortal\Bridge\Mailer\Mime\RefAddress;
use ExportPortal\Bridge\Symfony\Component\Messenger\MessengerInterface;
use ExportPortal\Contracts\Filesystem\Exception\ReadException;
use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToWriteFile;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Mime\Address;
use TinyMVC_Library_Image_intervention as LegacyImageHandler;

use const App\Common\ROOT_PATH;
use const App\Moderation\Types\TYPE_B2B;

/**
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [15.12.2021]
 * Controller Refactoring
 */
class B2b_Controller extends TinyMVC_Controller
{
    use ProductCardPricesTrait;

    private IndexedProductDataProvider $indexedProductDataProvider;

    private $breadcrumbs = [];

    /**
     * The b2b request provider instance.
     */
    private B2bRequestProvider $b2bRequestProvider;

    /**
     * The indexed (elastic) b2b request provider instance.
     */
    private B2bIndexedRequestProvider $b2bIndexedRequestProvider;

    /**
     * Controller constructor.
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->b2bRequestProvider = $container->get(B2bRequestProvider::class);
        $this->b2bIndexedRequestProvider = $container->get(B2bIndexedRequestProvider::class);
        $this->indexedProductDataProvider = $container->get(IndexedProductDataProvider::class);
    }

    /**
     * The landing page.
     */
    public function index()
    {
        /** @var Category_groups_Model $categoryGroupsModel */
        $categoryGroupsModel = model(Category_groups_Model::class);
        /** @var B2b_Request_Country_Pivot_Model $countryB2bPivotRepository */
        $countryB2bPivotRepository = model(B2b_Request_Country_Pivot_Model::class);
        $countryB2bPivotTableName = $countryB2bPivotRepository->getTable();
        /** @var Countries_Model $portCountryRepository */
        $portCountryRepository = model(Countries_Model::class);
        $portCountryTable = $portCountryRepository->getTable();
        //get the top countries with counter for how many requests
        $topCountries = $countryB2bPivotRepository->findAllBy([
            'columns' => [
                "{$portCountryTable}.`country`",
                "{$portCountryTable}.`id`",
                "COUNT({$countryB2bPivotTableName}.request_id) AS counter",
            ],
            'joins'         => ['countries', 'activeRequests'],
            'group'         => [
                "{$countryB2bPivotTableName}.`country_id`",
            ],
            'limit'  => (int) config('top_countries_for_b2b', 10),
            'order'  => ['counter' => 'DESC'],
        ]);
        $b2bRepository = $this->b2bRequestProvider->getRepository();
        $globally = $b2bRepository->countAllBy([
            'scopes' => [
                'typeLocation' => B2bRequestLocationType::GLOBALLY,
                'active'       => 1,
                'status'       => 'enabled',
                'blocked'      => 0,
            ],
            'joins' => ['activeCompanies'],
        ]);

        $topCountries = array_map(function ($country) use ($globally) {
            $country['counter'] += $globally;

            return $country;
        }, $topCountries);

        //get the latest requests from elastic
        $latestRequests = $this->b2bIndexedRequestProvider->getLatestRequests((int) config('b2b_landing_number_of_latest_requests', 4));
        $isCurrentUserLogged = logged_in();
        $latestRequests = array_map(
            function ($otherRequest) use ($isCurrentUserLogged) {
                if ($isCurrentUserLogged) {
                    $chatBtn = new ChatButton(['recipient' => $otherRequest['id_user'], 'status' => 'active']);
                    $otherRequest['btnChat'] = $chatBtn->button();
                }
                //create the link to the user logo
                $otherRequest['mainImageLink'] = getDisplayImageLink(
                    [
                        '{FOLDER_PATH}' => $otherRequest['id_request'],
                        '{FILE_NAME}'   => $otherRequest['mainImage']['photo'],
                    ],
                    'b2b_request.main',
                    [
                        'thumb_size'     => 2,
                        'no_image_group' => 'dynamic',
                        'image_size'     => ['w' => 213, 'h' => 160],
                    ]
                );

                return $otherRequest;
            },
            $latestRequests
        );

        views()->displayWebpackTemplate([
            'topCountries'   => $topCountries,
            'latestRequests' => $latestRequests,
            'categoryGroups' => $categoryGroupsModel->get_category_groups(),
            'currentPage'    => 'b2b_landing',
            'headerContent'  => 'new/b2b/landing/header_view',
            'content'        => 'b2b/landing/index_view',
            'styleCritical'  => 'b2b_landing',
        ]);
    }

    /**
     * All b2b requests.
     */
    public function all()
    {
        //available only for logged in users
        if (!logged_in()) {
            show_403();
        }
        //check for valid uri
        $uri = uri()->uri_to_assoc(2);
        checkURI($uri, ['b2b', 'country', 'industry', 'category', 'page']);
        checkIsValidPage($uri['page'] ?? null);
        //get the query params
        $query = request()->query;
        $showFoundB2bRequests = false;
        $this->breadcrumbs[] = [
            'link'  => __SITE_URL . 'b2b',
            'title' => 'B2B',
        ];
        $this->breadcrumbs[] = [
            'link'  => __SITE_URL . 'b2b/all',
            'title' => 'B2B Requests',
        ];
        //filters links map
        $linksMap = [
            'b2b' => [
                'type' => 'uri',
                'deny' => ['industry', 'category', 'country', 'states', 'port_city', 'page'],
            ],
            'country' => [
                'type' => 'uri',
                'deny' => ['country', 'states', 'port_city', 'page'],
            ],
            'industry' => [
                'type' => 'uri',
                'deny' => ['industry', 'category', 'page'],
            ],
            'category' => [
                'type' => 'uri',
                'deny' => ['category', 'page'],
            ],
            'page' => [
                'type' => 'uri',
                'deny' => ['page'],
            ],
            'golden_category' => [
                'type' => 'uri',
                'deny' => ['golden_category', 'page'],
            ],
            // @depracated
            //commented so far because there are no filters by state or city yet
            // 'states' => [
            //     'type' => 'get',
            //     'deny' => ['states', 'port_city', 'page'],
            // ],
            // 'port_city' => [
            //     'type' => 'get',
            //     'deny' => ['port_city', 'page'],
            // ],
            'partener_type' => [
                'type' => 'get',
                'deny' => ['partener_type', 'page'],
            ],
            'keywords' => [
                'type' => 'get',
                'deny' => ['keywords', 'page'],
            ],
            'tag' => [
                'type' => 'get',
                'deny' => ['tag', 'industry', 'category', 'country', 'page'],
            ],
            // @depracated
            //commented so far because there is no filter by radius yet
            // 'radius' => [
            //     'type' => 'get',
            //     'deny' => ['radius', 'page'],
            // ],
        ];
        //links template
        $linksTemplates = uri()->make_templates($linksMap, $uri);
        $linksTemplatesWithout = uri()->make_templates($linksMap, $uri, true);

        //region golden categories
        /** @var Golden_Categories_Model $goldenCategoriesModel */
        $goldenCategoriesModel = model(Golden_Categories_Model::class);
        if ($query->has('golden_category') &&!empty($goldenGroupId = $query->getInt('golden_category'))) {
            $industriesByGolden = $goldenCategoriesModel->findOneBy([
                'with'    => ['industries'],
                'scopes'  => ['id' => $goldenGroupId]
            ]);
            if(!empty($industriesByGolden['industries'])){
                $appliedFilters['byGoldenCategory'] = $goldenGroupId;
                $selectedGroupindustries = array_column($industriesByGolden['industries']->toArray(), 'category_id');
                $searchParams[] = [
                    'title' => 'Golden Category',
                    'subParams' => [
                        [
                            'link'  => get_dynamic_url($linksTemplatesWithout['golden_category']),
                            'title' => $industriesByGolden['title'],
                        ]
                    ]
                ];
            }
        }
        //endregions golden categories

        //region get all categories and industries
        /** @var Item_Category_Model $categoryRepository */
        $categoryRepository = model(Item_Category_Model::class);
        $categoryTable = $categoryRepository->getTable();
        $allIndustries = array_column(
            $categoryRepository->findAllBy([
                'columns' => "*, IF('' != `{$categoryTable}`.`cat_childrens`, 1, 0) AS `has_children`",
                'scopes'  => ['parent' => 0],
                'order'   => ['name' => 'asc'],
                'with'    => ['goldenCategory']
            ]),
            null,
            'category_id'
        );
        $allSecondLevelCategories = array_column(
            $categoryRepository->findAllBy([
                'scopes'  => ['parent_ids' => array_keys($allIndustries)],
                'order'   => ['name' => 'asc'],
            ]),
            null,
            'category_id'
        );
        //endregion get all categories and industries

        //check if the industry filter was applied
        if (!empty($uri['industry'])) {
            $appliedFilters['byIndustry'] = $industryId = id_from_link($uri['industry']);
            if (!isset($allIndustries[$industryId]) || strForUrl($allIndustries[$industryId]['name'] . ' ' . $industryId) != $uri['industry']) {
                show_404();
            }
            $showFoundB2bRequests = true;
            $searchParams[] = [
                'title' => 'Industry',
                'subParams' => [
                    [
                        'link'  => get_dynamic_url($linksTemplatesWithout['industry']),
                        'title' => $allIndustries[$industryId]['name'],
                    ]
                ]
            ];
            $this->breadcrumbs[] = [
                'link'  => __SITE_URL . 'b2b/all/industry/' . $uri['industry'],
                'title' => $allIndustries[$industryId]['name'],
            ];
            $metaParams['[INDUSTRY]'] = $allIndustries[$industryId]['name'];
        }

        //check if the category filter was applied
        if (!empty($uri['category'])) {
            $appliedFilters['byCategory'] = $categoryId = id_from_link($uri['category']);
            if (empty($uri['industry']) || !isset($allSecondLevelCategories[$categoryId]) || strForUrl($allSecondLevelCategories[$categoryId]['name'] . ' ' . $categoryId) != $uri['category']) {
                show_404();
            }
            $showFoundB2bRequests = true;
            $searchParams[] = [
                'title' => 'Category',
                'subParams' => [
                    [
                        'link'  => get_dynamic_url($linksTemplatesWithout['category']),
                        'title' => $allSecondLevelCategories[$categoryId]['name'],
                    ]
                ]
            ];
            $this->breadcrumbs[] = [
                'link'  => __SITE_URL . 'b2b/all/category/' . $uri['category'],
                'title' => $allSecondLevelCategories[$categoryId]['name'],
            ];
            $metaParams['[CATEGORY]'] = $allSecondLevelCategories[$categoryId]['name'];
        }

        //region get all countries
        /** @var Countries_Model $countryRepository */
        $countryRepository = model(Countries_Model::class);
        $allCountries = array_column(
            $countryRepository->findAll([
                'order' => ['country' => 'asc'],
            ]),
            null,
            'id'
        );
        //endregion get all countries

        //check if the country filter was applied
        if (!empty($uri['country'])) {
            $appliedFilters['byCountry'] = $countryId = id_from_link($uri['country']);
            if (!isset($allCountries[$countryId]) || strForURL($allCountries[$countryId]['country'] . ' ' . $countryId) != $uri['country']) {
                show_404();
            }
            $showFoundB2bRequests = true;
            $searchParams[] = [
                'title' => 'Country',
                'subParams' => [
                    [
                        'link'  => get_dynamic_url($linksTemplatesWithout['country']),
                        'title' => $allCountries[$countryId]['country'],
                    ]
                ]
            ];
            $this->breadcrumbs[] = [
                'link'  => __SITE_URL . 'b2b/all/country/' . $uri['country'],
                'title' => $allCountries[$countryId]['country'],
            ];
            $metaParams['[COUNTRY]'] = $allCountries[$countryId]['country'];

            // @deprecated
            //state filter commented so far as there is no state filter
            //
            //$searchStates = array_column((array) $countryModel->get_states($countryId), null, 'id');
            //check if the state filter was applied
            // if (!empty($stateId = $query->getInt('states'))) {
            //     $appliedFilters['byState'] = $stateId;
            //     if (isset($searchStates[$stateId])) {
            //         $searchParams[] = [
            //             'link'  => get_dynamic_url($linksTemplatesWithout['states']),
            //             'title' => $searchStates[$stateId]['state'],
            //             'param' => 'State',
            //         ];

            //         // city filter commented for now as there are no such filters
            //         // //check if the city filter was applied
            //         // if (!empty($cityId = $query->getInt('port_city'))) {
            //         //     $appliedFilters['byCity'] = $cityId;

            //         //     $city = $countryModel->get_city($cityId, 'id, state, city');
            //         //     if (!empty($city) && $city['state'] == $stateId) {
            //         //         $selectedCity = $city;

            //         //         $searchParams[] = [
            //         //             'link'  => get_dynamic_url($linksTemplatesWithout['port_city']),
            //         //             'title' => $city['city'],
            //         //             'param' => 'City',
            //         //         ];
            //         //     }
            //         // }
            //     }
            // }
        }

        //region get all partener types
        /** @var Partners_Types_Model $partnersRepository */
        $partnersRepository = model(Partners_Types_Model::class);
        $allPartnersTypes = array_column($partnersRepository->findAll(), null, 'id_type');
        //endregion get all partener types
        //check if the partner type filter was applied
        if (!empty($partnerTypeId = $query->getInt('partener_type'))) {
            $appliedFilters['byPartnerType'] = $partnerTypeId;

            if (isset($allPartnersTypes[$partnerTypeId])) {
                $showFoundB2bRequests = true;
                $searchParams[] = [
                    'title' => 'Partener type',
                    'subParams' => [
                        [
                            'link'  => get_dynamic_url($linksTemplatesWithout['partener_type']),
                            'title' => $allPartnersTypes[$partnerTypeId]['name'],
                        ]
                    ]
                ];
            }
        }
        //check if the keywords filter was applied
        if (!empty($keywords = $query->get('keywords'))) {
            $appliedFilters['byKeywords'] = cut_str(decodeUrlString($keywords));
            $showFoundB2bRequests = true;
            /** @var Search_Log_Model $searchLogModel */
            $searchLogModel = model(Search_Log_Model::class);
            $searchLogModel->log($keywords);
            $searchParams[] = [
                'title' => 'Keywords',
                'subParams' => [
                    [
                        'link'  => get_dynamic_url($linksTemplatesWithout['keywords']),
                        'title' => cleanOutput(decodeUrlString($keywords)),
                    ]
                ]
            ];
            $metaParams['[KEYWORDS]'] = cleanOutput($appliedFilters['byKeywords']);
        }
        // @deprecated
        // zip commented as it is not used now
        //
        // check if the zip filter was applied
        // if (!empty($zip = $query->get('zip'))) {
        //     $appliedFilters['byZip'] = cut_str($zip);
        //     $showFoundB2bRequests = true;

        //     $searchParams[] = [
        //         'link'  => get_dynamic_url($linksTemplatesWithout['zip']),
        //         'title' => cleanOutput($zip),
        //         'param' => 'Zip',
        //     ];

        //     $metaParams['[ZIP]'] = cleanOutput($zip);
        // }

        // @deprecated
        //radius commented cause it is not used for now
        //
        //check if the radius filter was applied
        // if (!empty($radius = $query->getInt('radius'))) {
        //     $appliedFilters['byRadius'] = $radius;
        //     $showFoundB2bRequests = true;

        //     $searchParams[] = [
        //         'link'  => get_dynamic_url($linksTemplatesWithout['radius']),
        //         'title' => $radius,
        //         'param' => 'Radius',
        //     ];
        // }

        //get requests by condition from elastic
        $b2bRequestsConditions = [
            'partnerTypeId' => $appliedFilters['byPartnerType'] ?? null,
            'industryIds'   => $selectedGroupindustries ?? null,
            'industryId'    => $appliedFilters['byIndustry'] ?? null,
            'categoryId'    => $appliedFilters['byCategory'] ?? null,
            'countryId'     => $appliedFilters['byCountry'] ?? null,
            'keywords'      => $appliedFilters['byKeywords'] ?? null,
            'perPage'       => (int) config('b2b_per_page', 20),
            'page'          => (int) ($uri['page'] ?? 1),
        ];

        $b2bRequests = $this->b2bIndexedRequestProvider->getAllRequestsByFilters($b2bRequestsConditions);

        //we prepare data for company
        foreach ($b2bRequests['requests'] as &$b2bRequest) {
            $b2bRequest['company']['url'] = getCompanyURL([
                'index_name'    => $b2bRequest['company']['index_name'],
                'name_company'  => $b2bRequest['company']['name_company'],
                'type_company'  => $b2bRequest['company']['type_company'],
                'id_company'    => $b2bRequest['company']['id_company'],
            ]);
            $b2bRequest['company']['customAddress'] = implode(', ', array_filter([
                $b2bRequest['company']['state_name'] ?: null,
                $b2bRequest['company']['city'] ?: null,
            ]));
            $btnChat = new ChatButton(['recipient' => $b2bRequest['id_user'], 'module' => 2, 'recipientStatus' => 'active', 'item' => $b2bRequest['id_request']]);
            $b2bRequest['btnChat'] = $btnChat->button();

            $b2bRequest['mainImageLink'] = getDisplayImageLink(
                [
                    '{FOLDER_PATH}' => $b2bRequest['id_request'],
                    '{FILE_NAME}'   => $b2bRequest['mainImage']['photo'],
                ],
                'b2b_request.main',
                [
                    'thumb_size'     => 2,
                    'no_image_group' => 'dynamic',
                    'image_size'     => ['w' => 213, 'h' => 160],
                ]
            );
        }
        //configure pagination
        /** @var TinyMVC_Library_Pagination $paginationLibrary */
        $paginationLibrary = library(TinyMVC_Library_Pagination::class);
        $paginationLibrary->initialize([
            'base_url'      => $linksTemplates['page'],
            'first_url'     => $linksTemplatesWithout['page'],
            'total_rows'    => $b2bRequests['total'],
            'per_page'      => $b2bRequestsConditions['perPage'],
            'replace_url'   => true,
        ]);
        $searchParamsLinksTpl['filter'] = 'b2b/all';

        //display data
        views()->displayWebpackTemplate([
            'categoriesCountersByIndustry' => arrayByKey((array) $b2bRequests['allCategoriesAggregation'], 'industryId', true),
            'allCategoriesByIndustry'      => arrayByKey($allSecondLevelCategories, 'parent', true),
            'metaParams'                   => $metaParams ?? null,
            'showFoundB2bRequests'         => $showFoundB2bRequests,
            'goldenCategories'             => $goldenCategoriesModel->findAll(),
            'allPartnersTypes'             => $allPartnersTypes,
            'appliedFilters'               => $appliedFilters ?? [],
            'allIndustries'                => $allIndustries,
            'allCountries'                 => $allCountries,
            'searchParams'                 => $searchParams ?? null,
            'b2bindustries'                => $b2bRequests['allIndustriesAggregation'],
            'b2bcountries'                 => $b2bRequests['allCountriesAggregation'],
            'currentPage'                  => 'b2b',
            'b2bRequests'                  => $b2bRequests['requests'],
            'breadcrumbs'                  => $this->breadcrumbs,
            'pagination'                   => $paginationLibrary->create_links(),
            'linksTpl'                     => $linksTemplates,
            'perPage'                      => $b2bRequestsConditions['perPage'],
            'count'                        => $b2bRequests['total'],
            'page'                         => $b2bRequestsConditions['page'],
            'searchParamsLinksTpl'         => $searchParamsLinksTpl,
            'headerContent'                => 'new/b2b/all/header_view',
            'sidebarContent'               => 'new/b2b/all/sidebar_view',
            'content'                      => 'b2b/all/index_view',
            'styleCritical'                => 'b2b_requests_all',
        ]);
    }

    public function detail()
    {
        //available only for logged in users
        if (!logged_in()) {
            show_403();
        }

        $requestId = id_from_link(uri()->segment(3));
        try {
            $request = $this->b2bRequestProvider->getRequestDetailsFullData($requestId);
        } catch (B2bRequestNotFoundException $e) {
            show_404();
        }
        //check for blocked request
        if ($request['blocked'] > 0) {
            show_blocked();
        }
        //region categories and industries
        $industriesContent = '-';
        $categoriesContent = '-';
        if (!empty($request['categories']) && !empty($request['industries'])) {
            //get the categories links
            foreach ($request['categories'] as $categoryId => $category) {
                $linkIndustry = strForURL("{$request['industries'][$category['industry_id']]['name']} {$request['industries'][$category['industry_id']]['category_id']}");
                $linkCategory = strForURL("{$category['name']} {$categoryId}");
                $stockCategories[] = sprintf(
                    '<a class="b2b-detail__info-link" href="%s" title="%s">%s</a>',
                    __SITE_URL . "b2b/all/industry/{$linkIndustry}/category/{$linkCategory}",
                    cleanoutput($category['name']),
                    $category['name']
                );
            }
            //get the industries links
            foreach ($request['industries'] as $industry) {
                $linkIndustry = strForURL("{$industry['name']} {$industry['category_id']}");
                $stockIndustries[] = sprintf(
                    '<a class="b2b-detail__info-link" href="%s" title="%s">%s</a>',
                    __SITE_URL . "b2b/all/industry/{$linkIndustry}",
                    cleanoutput($industry['name']),
                    $industry['name']
                );
            }

            $industriesContent = implode(', ', $stockIndustries);
            $categoriesContent = implode(', ', $stockCategories);
        }
        //region categories and industries

        //region photos
        if (empty($request['photos'])) {
            $request['main_image']['url'] = getNoPhoto('dynamic', ['w' => 300, 'h' => 226]);
        }
        foreach ($request['photos'] as $photoKey => $photo) {
            if ($photo['is_main']) {
                $request['main_image'] = $photo;
                $request['main_image']['url'] = getDisplayImageLink(
                    ['{FOLDER_PATH}' => $request['id_request'], '{FILE_NAME}' => $photo['photo']],
                    'b2b_request.main',
                    [
                        'thumb_size'     => 2,
                        'no_image_group' => 'dynamic',
                        'image_size'     => ['w' => 300, 'h' => 226],
                    ]
                );
                unset($request['photos'][$photoKey]);

                continue;
            }
            $request['photos'][$photoKey]['url'] = getDisplayImageLink(['{FOLDER_PATH}' => $request['id_request'], '{FILE_NAME}' => $photo['photo']], 'b2b_request.photos', ['thumb_size' => 1]);
            $request['photos'][$photoKey]['original_url'] = getDisplayImageLink(['{FOLDER_PATH}' => $request['id_request'], '{FILE_NAME}' => $photo['photo']], 'b2b_request.photos');
        }
        //endregion photos

        //region partners block data
        /** @var B2b_Partners_Model $b2bPartnersRepository */
        $b2bPartnersRepository = model(B2b_Partners_Model::class);
        $request['partners'] = $b2bPartnersRepository->findAllBy([
            'scopes' => ['companyId' => $request['id_company']],
            'with'   => ['partner as company'],
            'limit'  => (int) config('b2b_detail_page_partners_per_page', 6),
            'order'  => ['date_partnership' => 'DESC'],
        ]);

        if (!empty($request['partners'])) {
            //add chat btn to all partners
            //and image logo
            $isCurrentUserLogged = logged_in();
            $request['partners'] = array_map(
                function ($partner) use ($isCurrentUserLogged) {
                    if ($isCurrentUserLogged) {
                        $chatBtn = new ChatButton(['recipient' => $partner['id_partner'], 'recipientStatus' => $partner['company']['user_status']]);
                        $partner['btnChat'] = $chatBtn->button();
                    }
                    $partner['logoLink'] = getDisplayImageLink(
                        ['{ID}' => $partner['id_partner'], '{FILE_NAME}' => $partner['company']['logo_company']],
                        'companies.main',
                        [
                            'thumb_size'     => 0,
                            'no_image_group' => 'dynamic',
                            'image_size'     => ['w' => 88, 'h' => 88],
                        ]
                    );

                    return $partner;
                },
                $request['partners']
            );
        }
        //get the total number of partners
        $request['countPartners'] = $b2bPartnersRepository->countAllBy(['scopes' => ['companyId' => $request['id_company']]]);
        //endregion partners block data

        //region advice block data
        $currentUserId = privileged_user_id();
        if (!empty($request['advice'])) {
            $adviceIds = array_keys($request['advice']);
            $isCurrentUserLogged = logged_in();
            $request['advice'] = array_map(
                function ($userAdvice) use ($isCurrentUserLogged) {
                    if ($isCurrentUserLogged) {
                        $chatBtn = new ChatButton(['recipient' => $userAdvice['id_user'], 'recipientStatus' => $userAdvice['status']]);
                        $userAdvice['btnChat'] = $chatBtn->button();
                    }
                    $userAdvice['logoLink'] = getDisplayImageLink(
                        ['{ID}' => $userAdvice['id_user'], '{FILE_NAME}' => $userAdvice['user_photo']],
                        'users.main',
                        [
                            'thumb_size'     => 0,
                            'no_image_group' => $userAdvice['user_group'],
                        ]
                    );

                    return $userAdvice;
                },
                $request['advice']
            );
            //get helpful by current user for the list of advices
            /** @var B2b_Advice_Helpful_Model $b2bAdviceHelpfulRepository */
            $b2bAdviceHelpfulRepository = model(B2b_Advice_Helpful_Model::class);
            $request['helpful'] = array_column($b2bAdviceHelpfulRepository->findAllBy([
                'scopes' => ['adviceIds' => $adviceIds, 'userId' => $currentUserId],
            ]), 'help', 'id_advice');
        }
        /** @var B2b_Advice_Model $b2bAdviceRepository */
        $b2bAdviceRepository = model(B2b_Advice_Model::class);
        $writeAdvice = false;
        if (i_have_company()) {
            //check if current user's companies have
            //the current request's company as partner
            /** @var Seller_Companies_Model $sellerCompanyRepository */
            $sellerCompanyRepository = model(Seller_Companies_Model::class);
            $myCompanies = $sellerCompanyRepository->findAllBy(['columns' => ['id_company'], 'scopes' => ['userId' => $currentUserId]]);
            $myPartners = array_column($b2bPartnersRepository->findAllBy(['scopes' => ['companyIds' => array_column($myCompanies, 'id_company')]]), 'id_partner');
            //if current user who views the request
            //has the company of the request as partner
            //and has not written any advice yet, then we
            //allow him to write advice
            if (!empty($myPartners)
                && in_array($request['id_company'], $myPartners)
                && !(bool) $b2bAdviceRepository->countAllBy(['scopes' => [
                    'requestId' => $request['id_request'],
                    'userId'    => $currentUserId,
                ],
                ])
            ) {
                $writeAdvice = true;
            }
        }
        //total number of advice
        $request['countAdvice'] = $b2bAdviceRepository->countAllBy(['scopes' => ['requestId' => $request['id_request']]]);
        //endregion advice block data

        //region google map configuration
        $marker[] = [
            'lat'       => $request['company']['latitude'],
            'lng'       => $request['company']['longitude'],
            'type'      => 'coords',
            'main_info' => $request,
            'type_info' => 'company',
            'title'     => $request['company']['name_company'],
            'radius'    => $request['b2b_radius'],
        ];
        //endregion google map configuration

        //region followers block data
        /** @var B2b_Followers_Model $b2bFollowersRepository */
        $b2bFollowersRepository = model(B2b_Followers_Model::class);
        //get the current users follow status of t he current b2b
        $iFollowed = $b2bFollowersRepository->countAllBy([
            'scopes' => [
                'requestId' => $request['id_request'],
                'userId'    => id_session(),
            ],
        ]);
        //if user is logged in and request has followers
        //then we add chat button to all followers
        if (!empty($request['followers'])) {
            $isCurrentUserLogged = logged_in();
            $request['followers'] = array_map(
                function ($userFollower) use ($isCurrentUserLogged) {
                    if ($isCurrentUserLogged) {
                        $chatBtn = new ChatButton(['recipient' => $userFollower['id_user'], 'recipientStatus' => $userFollower['status']]);
                        $userFollower['btnChat'] = $chatBtn->button();
                    }
                    //create the link to the user logo
                    $userFollower['logoLink'] = getDisplayImageLink(
                        ['{ID}' => $userFollower['id_user'], '{FILE_NAME}' => $userFollower['user_photo']],
                        'users.main',
                        [
                            'thumb_size'     => 0,
                            'no_image_group' => $userFollower['user_group'],
                        ]
                    );

                    return $userFollower;
                },
                $request['followers']
            );
        }
        //get the number of all followers for the request
        $currentNumberOfFollowers = count($request['followers']);
        $request['countFollowers'] = $currentNumberOfFollowers < (int) config('b2b_detail_page_followers_per_page', 8)
                                ? $currentNumberOfFollowers
                                : $b2bFollowersRepository->countAllBy(['scopes' => ['requestId' => $request['id_request']]]);
        //endregion followers block data

        //region other b2b requests
        $request['userRequests'] = $this->b2bRequestProvider->getOtherRequestsThan(
            $request['id_request'],
            $request['id_user'],
            (int) config('b2b_detail_page_other_b2b_limit', 4)
        );
        $isCurrentUserLogged = logged_in();
        $request['userRequests'] = array_map(
            function ($otherRequest) use ($isCurrentUserLogged) {
                if ($isCurrentUserLogged) {
                    $chatBtn = new ChatButton(['recipient' => $otherRequest['id_user'], 'recipientStatus' => 'active']);
                    $otherRequest['btnChat'] = $chatBtn->button();
                }
                //create the link to the user logo
                $otherRequest['mainImageLink'] = getDisplayImageLink(
                    [
                        '{FOLDER_PATH}' => $otherRequest['id_request'],
                        '{FILE_NAME}'   => $otherRequest['mainImage']['photo'],
                    ],
                    'b2b_request.main',
                    [
                        'thumb_size'     => 2,
                        'no_image_group' => 'dynamic',
                        'image_size'     => ['w' => 213, 'h' => 160],
                    ]
                );

                return $otherRequest;
            },
            $request['userRequests']
        );
        $request['countOtherRequests'] = $this->b2bRequestProvider->getCountOtherRequestsThan($request['id_request'], $request['id_user']);
        //endregion other b2b requests

        //region get users items
        $userItems = $this->indexedProductDataProvider->getItemsByCriteria(
            [
                'seller'        => $request['id_user'],
                'notOutOfStock' => 1,
                'per_p'			=> 12,
                'sort_by'       => ['featured_from_date-desc'],
            ]
        );
        $itemsCount = count($userItems);

        if ($itemsCount > 4 && $itemsCount % 2 !== 0) {
            array_pop($userItems);
            $itemsCount--;
        }
        //endregion get users items

        //region breadcrumbs
        $this->breadcrumbs[] = [
            'link'  => __SITE_URL . 'b2b',
            'title' => 'B2B',
        ];
        $this->breadcrumbs[] = [
            'link'  => __SITE_URL . 'b2b/all',
            'title' => 'B2B Requests',
        ];
        $this->breadcrumbs[] = [
            'link'  => __SITE_URL . 'b2b/detail/' . strForURL($request['b2b_title']) . '-' . $request['id_request'],
            'title' => $request['b2b_title'],
        ];
        //endregion breadcrumbs

        //region increment views for current b2b request
        if (!cookies()->exist_cookie('viewed_request_' . $request['id_request'])) {
            //if not viewed in the last hour then set as viewed again
            cookies()->setCookieParam('viewed_request_' . $request['id_request'], $request['id_request']);
            $this->b2bRequestProvider->getRepository()->updateOne($request['id_request'], ['viewed_count' => $request['viewed_count'] + 1]);
            //update in elastic the view count too
            /** @var Elasticsearch_B2b_Model $elasticsearchB2bModel */
            $elasticsearchB2bModel = model(Elasticsearch_B2b_Model::class);
            $elasticsearchB2bModel->incrementViews($request['id_request']);
        }
        //endregion increment views for current b2b request

        //chatButton
        if (logged_in()) {
            $btnChat = new ChatButton(['recipient' => $request['id_user'], 'recipientStatus' => 'active', 'module' => 2, 'item' => $request['id_request']]);
            $btnChatButton = $btnChat->button();
        }

        views()->displayWebpackTemplate([
            'url'                      => uri()->segment(3),
            'title'                    => 'FIND BUSINESS PARTNERS WORLDWIDE',
            'writeAdvice'              => $writeAdvice,
            'industriesContent'        => $industriesContent,
            'categoriesContent'        => $categoriesContent,
            'content'                  => 'b2b/detail/index_view',
            'request'                  => $request,
            'tagLink'                  => __SITE_URL . 'b2b/all?keywords=',
            'btnChat'                  => $btnChatButton ?? null,
            'iFollowed'                => $iFollowed ?? null,
            'breadcrumbs'              => $this->breadcrumbs,
            'userItems'                => $userItems,
            'countUserItems'           => $itemsCount,
            'myMapConfig'              => [
                'markers' => json_encode($marker, JSON_FORCE_OBJECT),
            ],
            'markers_type' => 'new',
            'metaParams'   => [
                '[TITLE]'       => $request['b2b_title'],
                '[DESCRIPTION]' => cleanInput($request['b2b_message']),
                '[image]'       => $request['main_image']['url'],
            ],
        ]);
    }

    public function reg()
    {
        checkIsLogged();
        checkHaveCompany();
        checkGroupExpire();
        checkPermision('manage_b2b_requests');

        /** @var Products_Model $itemRepository */
        $itemRepository = model(Products_Model::class);

        if (!is_buyer() && 0 === $itemRepository->countAllBy(['scopes' => [
            'sellerId'      => (int) id_session(),
            'visible'       => 1,
            'moderation'    => 1,
            'blockedValue'  => 0,
            'draft'         => 0,
        ]])) {
            session()->setMessages(translate('systmess_b2b_no_items_to_register_new_request'), 'info');
            headerRedirect();
        }
        /** @var Countries_Model $countriesRepository */
        $countriesRepository = model(Countries_Model::class);

        /** @var Branch_Model $branchRepository */
        $branchRepository = model(Branch_Model::class);

        /** @var Item_Category_Model $categoryRepository */
        $categoryRepository = model(Item_Category_Model::class);

        /** @var Seller_Companies_Categories_Pivot_Model $sellerCategoriesRepository */
        $sellerCategoriesRepository = model(Seller_Companies_Categories_Pivot_Model::class);

        /** @var Seller_Companies_Industries_Pivot_Model $sellerIndustriesRepository */
        $sellerIndustriesRepository = model(Seller_Companies_Industries_Pivot_Model::class);

        /** @var Textual_Block_Model $textBlockRepository */
        $textBlockRepository = model(Textual_Block_Model::class);

        $companyCategories = array_filter(
            (array) $categoryRepository->findAllBy([
                'columns'  => 'category_id, name, parent, cat_childrens',
                'scopes'   => [
                    'ids' => array_column(
                        array_filter((array) $sellerCategoriesRepository->findAllBy([
                            'scopes' => ['company' => (int) my_company_id()],
                        ])),
                        'id_category'
                    ),
                ],
            ])
        );
        $companyIndustries = array_intersect_key(
            arrayByKey(
                array_filter((array) $categoryRepository->findAllBy([
                    'columns'  => 'category_id, name, parent, cat_childrens, p_or_m',
                    'scopes'   => [
                        'ids' => array_column(
                            array_filter(
                                (array) $sellerIndustriesRepository->findAllBy([
                                    'scopes'  => ['company' => (int) my_company_id()],
                                    'limit'   => 3,
                                ])
                            ),
                            'id_industry'
                        ),
                    ],
                ])),
                'category_id'
            ),
            $selectedCategories = arrayByKey($companyCategories, 'parent', true)
        );

        // region main image cropper parameters
        $cropperParameters = [
            'url'                    => ['upload' => getUrlForGroup('b2b/ajax_b2b_upload_main_image')],
            'rules'                  => config('img.b2b_request.main.rules'),
            'accept'                 => getMimePropertiesFromFormats(config('img.b2b_request.main.rules.format'))['accept'] ?? [],
            'title_text_popup'       => 'Main image',
            'crop_img_height'		      => 500,
            'croppper_limit_by_min'  => true,
            'btn_text_save_picture'  => 'Set main image',
            'link_thumb_main_image'  => __IMG_URL . 'public/img/no_image/group/main-image.svg',
            'link_main_image'        => __IMG_URL . 'public/img/no_image/group/main-image.svg',
        ];
        // endregion main image cropper parameters

        //region additional pictures upload parameters
        $mimePhotosProperties = getMimePropertiesFromFormats(config('img.b2b_request.photos.rules.format'));
        $uploaderParameters = [
            'limits' => [
                'accept'    => arrayGet($mimePhotosProperties, 'accept'),
                'formats'   => arrayGet($mimePhotosProperties, 'formats'),
                'mimetypes' => arrayGet($mimePhotosProperties, 'mimetypes'),
                'amount'    => [
                    'total'   => (int) config('img.b2b_request.photos.limit'),
                    'current' => 0,
                ],
            ],
            'rules'  => config('img.b2b_request.photos.rules'),
            'url'    => [
                'upload' => getUrlForGroup('b2b/ajax_b2b_upload_photo'),
                'delete' => getUrlForGroup('b2b/ajax_remove_image'),
            ],
        ];
        //endregion additional pictures upload parameters
        views()->displayWebpackTemplate([
            'title'                     => 'FIND BUSINESS PARTNERS WORLDWIDE',
            'branches'                  => $branchRepository->get_company_branches((int) my_company_id()),
            'partnersType'              => $branchRepository->get_parteners_type(),
            'portCountry'               => $countriesRepository->findAll(),
            'locationTypes'             => B2bRequestLocationType::getAllLocationWithLabels(),
            'blockInfo'                 => ['about_tag_info' => $textBlockRepository->findOneBy(['scopes' => ['short_name' => 'about_tag_info']])],
            'cropperParameters'         => $cropperParameters,
            'uploaderParameters'        => $uploaderParameters,
            'content'                   => 'b2b/form/reg_view',
            'styleCritical'             => 'b2b_reg',
            'multipleselectIndustries'  => [
                'industries'                => $companyIndustries,
                'categories'                => $selectedCategories,
                'industries_selected'       => $companyIndustries,
                'categories_selected_by_id' => arrayByKey($companyCategories, 'category_id'),
                'max_industries'            => (int) config('multipleselect_max_industries', 3),
            ],
        ]);
    }

    public function edit()
    {
        checkIsLogged();
        checkGroupExpire();
        checkPermision('manage_b2b_requests');

        /** @var Item_Category_Model $categoryRepository */
        $categoryRepository = model(Item_Category_Model::class);

        /** @var Seller_Companies_Categories_Pivot_Model $sellerCategoriesRepository */
        $sellerCategoriesRepository = model(Seller_Companies_Categories_Pivot_Model::class);

        /** @var Seller_Companies_Industries_Pivot_Model $sellerIndustriesRepository */
        $sellerIndustriesRepository = model(Seller_Companies_Industries_Pivot_Model::class);

        /** @var Countries_Model $countriesRepository */
        $countriesRepository = model(Countries_Model::class);

        /** @var Branch_Model $branchRepository */
        $branchRepository = model(Branch_Model::class);

        $requestId = (int) uri()->segment(3);

        $b2bRepository = $this->b2bRequestProvider->getRepository();
        if (0 === $b2bRepository->countAllBy(['scopes'=> ['id' => $requestId, 'user_id' => privileged_user_id()]])) {
            $this->session->setMessages(translate('systmess_error_rights_perform_this_action'), 'errors');
            headerRedirect();
        }

        try {
            $request = $this->b2bRequestProvider->getRequestWithRelationData($requestId);
        } catch (B2bRequestNotFoundException $e) {
            $this->session->setMessages(translate('systmess_error_rights_perform_this_action'), 'errors');
            headerRedirect();
        }

        $companyId = $request['id_company'];
        $companyIndustries = arrayByKey(
            array_filter(
                (array) $categoryRepository->findAllBy([
                    'columns'  => 'category_id, name, parent, cat_childrens, p_or_m',
                    'scopes'   => [
                        'ids' => array_column(
                            array_filter(
                                (array) $sellerIndustriesRepository->findAllBy([
                                    'scopes'  => ['company' => $companyId],
                                    'limit'   => 3,
                                ])
                            ),
                            'id_industry'
                        ),
                    ],
                ])
            ),
            'category_id'
        );
        $companyCategories = arrayByKey(
            array_filter(
                (array) $categoryRepository->findAllBy([
                    'columns'  => 'category_id, name, parent, cat_childrens',
                    'scopes'   => [
                        'ids' => array_column(
                            array_filter((array) $sellerCategoriesRepository->findAllBy([
                                'scopes' => ['company' => $companyId],
                            ])),
                            'id_category'
                        ),
                    ],
                ])
            ),
            'parent',
            true
        );
        //get selected industreis and categories
        $selectedIndustries = array_column($request['industries']->toArray(), null, 'category_id');
        $selectedCategories = array_column($request['categories']->toArray(), null, 'category_id');
        //set request countries and photos as arrays
        $request['countries'] = null === $request['countries'] ? [] : array_column($request['countries']->toArray(), null, 'id');
        $request['photos'] = null === $request['photos'] ? [] : array_column($request['photos']->toArray(), null, 'id');
        //get main image from photos and leave in the 'photos' all images without main
        $mainImage = null;
        foreach ($request['photos'] as $photoKey => $photo) {
            if ($photo['is_main']) {
                $mainImage = $photo;
                unset($request['photos'][$photoKey]);

                continue;
            }
            $request['photos'][$photoKey]['url'] = getDisplayImageLink(['{FOLDER_PATH}' => $requestId, '{FILE_NAME}' => $photo['photo']], 'b2b_request.photos', ['thumb_size' => 1]);
        }
        // region main image cropper parameters
        $cropperParameters = [
            'url'                    => ['upload' => getUrlForGroup('b2b/ajax_b2b_upload_main_image')],
            'rules'                  => config('img.b2b_request.main.rules'),
            'accept'                 => getMimePropertiesFromFormats(config('img.b2b_request.main.rules.format'))['accept'] ?? [],
            'title_text_popup'       => 'Main image',
            'crop_img_height'		 => 500,
            'croppper_limit_by_min'  => true,
            'btn_text_save_picture'  => 'Set main image',
            'link_thumb_main_image'  => getDisplayImageLink(['{FOLDER_PATH}' => $requestId, '{FILE_NAME}' => $mainImage['photo']], 'b2b_request.main', ['thumb_size' => 2]),
            'link_main_image'        => getDisplayImageLink(['{FOLDER_PATH}' => $requestId, '{FILE_NAME}' => $mainImage['photo']], 'b2b_request.main'),
        ];
        // endregion main image cropper parameters

        //region additional pictures upload parameters
        $mimePhotosProperties = getMimePropertiesFromFormats(config('img.b2b_request.photos.rules.format'));
        $uploaderParameters = [
            'limits' => [
                'accept'    => arrayGet($mimePhotosProperties, 'accept'),
                'formats'   => arrayGet($mimePhotosProperties, 'formats'),
                'mimetypes' => arrayGet($mimePhotosProperties, 'mimetypes'),
                'amount'    => [
                    'total'   => (int) config('img.b2b_request.photos.limit'),
                    'current' => 0,
                ],
            ],
            'rules'  => config('img.b2b_request.photos.rules'),
            'url'    => [
                'upload' => getUrlForGroup('b2b/ajax_b2b_upload_photo'),
                'delete' => getUrlForGroup('b2b/ajax_remove_image'),
            ],
        ];
        //endregion additional pictures upload parameters

        /** @var Textual_Block_Model $textBlockRepository */
        $textBlockRepository = model(Textual_Block_Model::class);

        views()->displayWebpackTemplate([
            'branches'                  => $branchRepository->get_company_branches((int) my_company_id()),
            'partnersType'              => $branchRepository->get_parteners_type(),
            'portCountry'               => $countriesRepository->findAll(),
            'locationTypes'             => B2bRequestLocationType::getAllLocationWithLabels(),
            'request'                   => $request,
            'blockInfo'                 => ['about_tag_info' => $textBlockRepository->findOneBy(['scopes' => ['short_name' => 'about_tag_info']])],
            'cropperParameters'         => $cropperParameters,
            'uploaderParameters'        => $uploaderParameters,
            'content'                   => 'b2b/form/reg_view',
            'styleCritical'             => 'b2b_reg',
            'multipleselectIndustries'  => [
                'industries'                => array_intersect_key($companyIndustries, $companyCategories),
                'categories'                => $companyCategories,
                'industries_selected'       => $selectedIndustries,
                'categories_selected_by_id' => $selectedCategories,
                'max_industries'            => (int) config('multipleselect_max_industries', 3),
            ],
        ]);
    }

    public function my_requests()
    {
        checkPermision('manage_b2b_requests,mange_ff_partnership_requests');
        checkGroupExpire();

        // $this->breadcrumbs[] = array(
        // 	'link' => __SITE_URL . 'b2b/all',
        // 	'title' => 'B2B'
        // );
        // $this->breadcrumbs[] = array(
        // 	'link' => __SITE_URL . 'b2b/my_requests',
        // 	'title' => 'My requests'
        // );
        // $data['breadcrumbs'] = $this->breadcrumbs;
        $data['request_per_page'] = config('user_requests_per_page');

        switch (user_group_type()) {
            case 'Shipper':
                $this->_shipper_requests($data);

                break;
            case 'Seller':
                $this->_seller_requests($data);

                break;
        }
    }

    public function my_partners()
    {
        checkPermision('manage_b2b_requests,mange_ff_partnership_requests');
        checkGroupExpire();

        $this->load->model('Country_model', 'country');

        switch (user_group_type()) {
            case 'Shipper':
                $this->_shipper_partners();

                break;
            case 'Seller':
                $this->_seller_partners();

                break;
        }
    }

    public function ajax_get_map(): Response
    {
        checkIsAjax();

        $relativeUrl = asset('public/build/images/b2b/landing/country-map.svg');
        if (\filter_var($relativeUrl, \FILTER_VALIDATE_URL)) {
            $relativeUrl = \parse_url($relativeUrl, \PHP_URL_PATH);
        }

        $filePath = sprintf('%s/%s', ROOT_PATH, ltrim($relativeUrl, '\\/'));
        if (!file_exists($filePath)) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        return new Response(file_get_contents($filePath), Response::HTTP_OK);
    }

    public function ajax_shipper_requests()
    {
        checkIsAjax();
        checkIsLoggedAjaxDT();
        checkHaveShipperCompanyAjaxDT();
        checkPermisionAjaxDT('mange_ff_partnership_requests');

        /**
         * @var B2b_Model $b2bModel
         */
        $b2bModel = model(B2b_Model::class);

        /**
         * @var Country_Model $countryModel
         */
        $countryModel = model(Country_Model::class);

        /**
         * @var UserGroup_Model $userGroupModel
         */
        $userGroupModel = model(UserGroup_Model::class);

        $sortBy = flat_dt_ordering($_POST, [
            'partner_dt'          => 'cb.name_company',
            'address_dt'          => 'cb.address_company',
            'email_dt'            => 'cb.email_company',
            'phone_dt'            => 'cb.phone_company',
            'date_partnership_dt' => 'sp.date_partner',
        ]);

        $userFilters = [
            'are_partners'  => 0,
            'id_shipper'    => id_session(),
            'sort_by'       => empty($sortBy) ? ['sp.date_partner-desc'] : $sortBy,
            'per_p'         => (int) $_POST['iDisplayLength'],
            'start'         => (int) $_POST['iDisplayStart'],
        ];

        $conditions = dtConditions($_POST, [
            ['as' => 'added_start',         'key' => 'start_from',      'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'added_finish',        'key' => 'start_to',        'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'id_partner',          'key' => 'id_partner',      'type' => 'int'],
            ['as' => 'id_country',          'key' => 'id_country',      'type' => 'int'],
            ['as' => 'id_city',             'key' => 'id_city',         'type' => 'int'],
            ['as' => 'keywords',            'key' => 'keywords',        'type' => 'cleanInput|cut_str'],
        ]);

        $userFilters = array_merge(
            $userFilters,
            $conditions
        );

        $userFilters['count'] = $b2bModel->count_b2b_partners($userFilters);
        $partners = $b2bModel->get_b2b_partners($userFilters);

        $output = [
            'iTotalDisplayRecords'  => $userFilters['count'],
            'iTotalRecords'         => $userFilters['count'],
            'aaData'                => [],
            'sEcho'                 => (int) $_POST['sEcho'],
        ];

        if (empty($partners)) {
            jsonResponse('', 'success', $output);
        }

        $list_countries = $list_cities = $list_states = $list_state_cities = $list_groups = [];
        $item_user_groups = ['Certified Seller', 'Certified Manufacturer'];

        foreach ($partners as $item) {
            $list_groups[] = $item['user_group'];
            $list_countries[$item['id_country']] = $item['id_country'];
            if (!empty($item['id_state']) && $item['id_state'] > 0) {
                $list_states[$item['id_state']] = $item['id_state'];
                $list_state_cities[$item['id_city']] = $item['id_city'];
            } else {
                $list_cities[$item['id_city']] = $item['id_city'];
            }
        }

        $list_countries = array_filter($list_countries);
        $list_states = array_filter($list_states);
        $list_state_cities = array_filter($list_state_cities);
        $list_cities = array_filter($list_cities);

        $groups = $userGroupModel->getGroups(['id_groups' => implode(',', $list_groups)]);

        if (!empty($groups)) {
            $groups = arrayByKey($groups, 'idgroup');
        }

        if (!empty($list_countries)) {
            $names_countries = $countryModel->get_simple_countries(implode(',', $list_countries));
        }

        if (!empty($list_states)) {
            $names_states = $countryModel->get_simple_states(implode(',', $list_states));
        }

        if (!empty($list_state_cities)) {
            $names_state_cities = $countryModel->get_simple_cities_by_state(implode(',', $list_state_cities));
        }

        if (!empty($list_cities)) {
            $names_cities = $countryModel->get_simple_cities(implode(',', $list_cities));
        }

        foreach ($partners as $partner) {
            $partner_link = getUrlForGroup();
            if ('' != $partner['index_name']) {
                $partner_link .= $partner['index_name'];
            } else {
                $partner_link .= 'seller/' . strForURL($partner['name_company']) . '-' . $partner['id_company'];
            }

            // if ($partner['type_company'] == 'company') {
            //     if ($partner['index_name'] != '') {
            //         $partner_link .= $partner['index_name'];
            //     } else {
            //         $partner_link .= 'seller/' . strForURL($partner['name_company']) . '-' . $partner['id_partner'];
            //     }
            // } else {
            //     $partner_link .= 'branch/' . strForURL($partner['name_company']) . '-' . $partner['id_partner'];
            // }

            $full_address = [];
            if (!empty($names_states[$partner['id_state']]['state'])) {
                $full_address[] = $names_states[$partner['id_state']]['state'];
                $full_address[] = $names_state_cities[$partner['id_city']];
            } elseif (!empty($names_cities[$partner['id_city']]['city'])) {
                $full_address[] = $names_cities[$partner['id_city']];
            }

            $group_name = '';
            if (!empty($groups[$partner['user_group']])) {
                $group_name = $groups[$partner['user_group']]['gr_name'];
            }

            $partner_image_url = getDisplayImageLink(['{ID}' => $partner['id_partner'], '{FILE_NAME}' => $partner['logo_company']], 'companies.main', ['thumb_size' => 1]);

            $_partner_block = '<div class="flex-card">
									<div class="flex-card__fixed main-data-table__item-img image-card">
										<a class="link" href="' . $partner_link . '">
											<img class="image" src="' . $partner_image_url . '"/>
										</a>
									</div>
									<div class="flex-card__float">
										<div class="main-data-table__item-ttl" title="' . cleanOutput($partner['name_company']) . '">
											<a class="display-ib link-black txt-medium" href="' . $partner_link . '">'
                . $partner['name_company']
                . '</a>
										</div>

										<div class="companies__group ' . (in_array($group_name, $item_user_groups) ? 'txt-orange' : '') . '">' . $group_name . '</div>
									</div>
								</div>';

            $chatBtn = new ChatButton(['recipient' => $partner['id_seller'], 'recipientStatus' => $partner['user_status']]);

            $output['aaData'][] = [
                'partner_dt'     => $_partner_block,
                'address_dt'     => '<div>
								<img width="24" height="24" src="' . getCountryFlag($names_countries[$partner['id_country']]['country']) . '" title="' . $names_countries[$partner['id_country']]['country'] . '" alt="' . $names_countries[$partner['id_country']]['country'] . '"/> '
                    . $names_countries[$partner['id_country']]['country']
                    . '</div>' .
                    '<div class="txt-gray">' . implode(', ', $full_address) . '</div>',
                'contact_dt'              => "{$partner['email_company']}<br/><a class=\"link-black txt-medium text-nowrap\" href=\"tel:{$partner['phone_company']}\">{$partner['phone_company']}</a>",
                'date_partnership_dt'     => getDateFormat($partner['date_partner'], null, 'j M, Y H:i'),
                'actions_dt'              => '<div class="dropdown">
                                    <a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="ep-icon ep-icon_menu-circles"></i>
                                    </a>

                                    <div class="dropdown-menu">'
                                        . $chatBtn->button() .
                                        '<a class="dropdown-item confirm-dialog" data-callback="confirm_request" data-message="Are you sure you want to confirm this request?" data-partner="' . $partner['id_partner'] . '" data-seller="' . $partner['id_seller'] . '" href="#" title="Confirm request">
                                            <i class="ep-icon ep-icon_ok-stroke"></i>
                                            <span class="txt">Confirm request</span>
                                        </a>
                                        <a class="dropdown-item confirm-dialog" data-callback="decline_request" data-message="Are you sure you want to decline this request?" data-partner="' . $partner['id_partner'] . '" data-seller="' . $partner['id_seller'] . '" href="#" title="Decline request">
                                            <i class="ep-icon ep-icon_remove-stroke"></i>
                                            <span class="txt">Decline request</span>
                                        </a>
                                    </div>
                                </div>',
            ];
        }

        jsonResponse('', 'success', $output);
    }

    public function ajax_shipper_partners()
    {
        checkIsAjax();
        checkIsLoggedAjaxDT();
        checkHaveShipperCompanyAjaxDT();
        checkPermisionAjaxDT('mange_ff_partnership_requests');

        /**
         * @var B2b_Model $b2bModel
         */
        $b2bModel = model(B2b_Model::class);

        /**
         * @var Country_Model $countryModel
         */
        $countryModel = model(Country_Model::class);

        /**
         * @var Usergroup_Model $userGroupModel
         */
        $userGroupModel = model(Usergroup_Model::class);

        $sortBy = flat_dt_ordering($_POST, [
            'date_partnership_dt'   => 'sp.date_partner',
            'partner_dt'            => 'cb.name_company',
            'address_dt'            => 'cb.address_company',
            'email_dt'              => 'cb.email_company',
            'phone_dt'              => 'cb.phone_company',
        ]);

        $userFilters = [
            'are_partners'  => 1,
            'id_shipper'    => id_session(),
            'per_p'         => (int) $_POST['iDisplayLength'],
            'start'         => (int) $_POST['iDisplayStart'],
            'sort_by'       => empty($sortBy) ? ['sp.date_partner-desc'] : $sortBy,
        ];

        $conditions = dtConditions($_POST, [
            ['as' => 'added_start',       'key' => 'start_from',     'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'added_finish',      'key' => 'start_to',       'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'id_partner',        'key' => 'id_partner',     'type' => 'int'],
            ['as' => 'id_country',        'key' => 'id_country',     'type' => 'int'],
            ['as' => 'id_city',           'key' => 'id_city',        'type' => 'int'],
            ['as' => 'keywords',          'key' => 'keywords',       'type' => 'cleanInput|cut_str'],
        ]);

        $userFilters = array_merge($userFilters, $conditions);

        $userFilters['count'] = $b2bModel->count_b2b_partners($userFilters);
        $partners = $b2bModel->get_b2b_partners($userFilters);

        $output = [
            'iTotalDisplayRecords'  => $userFilters['count'],
            'iTotalRecords'         => $userFilters['count'],
            'aaData'                => [],
            'sEcho'                 => (int) $_POST['sEcho'],
        ];

        if (empty($partners)) {
            jsonResponse('', 'success', $output);
        }

        $list_countries = $list_cities = $list_states = $list_state_cities = $list_groups = [];
        $item_user_groups = ['Certified Seller', 'Certified Manufacturer'];

        foreach ($partners as $item) {
            $list_groups[] = $item['user_group'];
            $list_countries[$item['id_country']] = $item['id_country'];
            if (!empty($item['id_state']) && $item['id_state'] > 0) {
                $list_states[$item['id_state']] = $item['id_state'];
                $list_state_cities[$item['id_city']] = $item['id_city'];
            } else {
                $list_cities[$item['id_city']] = $item['id_city'];
            }
        }

        $list_countries = array_filter($list_countries);
        $list_states = array_filter($list_states);
        $list_state_cities = array_filter($list_state_cities);
        $list_cities = array_filter($list_cities);

        $groups = $userGroupModel->getGroups(['id_groups' => implode(',', $list_groups)]);

        if (!empty($groups)) {
            $groups = arrayByKey($groups, 'idgroup');
        }

        if (!empty($list_countries)) {
            $names_countries = $countryModel->get_simple_countries(implode(',', $list_countries));
        }

        if (!empty($list_states)) {
            $names_states = $countryModel->get_simple_states(implode(',', $list_states));
        }

        if (!empty($list_state_cities)) {
            $names_state_cities = $countryModel->get_simple_cities_by_state(implode(',', $list_state_cities));
        }

        if (!empty($list_cities)) {
            $names_cities = $countryModel->get_simple_cities(implode(',', $list_cities));
        }

        foreach ($partners as $partner) {
            $partner_link = getUrlForGroup();
            if ('' != $partner['index_name']) {
                $partner_link .= $partner['index_name'];
            } else {
                $partner_link .= 'seller/' . strForURL($partner['name_company']) . '-' . $partner['id_company'];
            }

            // if ($partner['type_company'] == 'company') {
            // 	if ($partner['index_name'] != '') {
            // 		$partner_link .= $partner['index_name'];
            // 	} else {
            // 		$partner_link .= 'seller/' . strForURL($partner['name_company']) . '-' . $partner['id_partner'];
            // 	}
            // } else {
            // 	$partner_link .= 'branch/' . strForURL($partner['name_company']) . '-' . $partner['id_partner'];
            // }

            $full_address = [];
            if (!empty($names_states[$partner['id_state']]['state'])) {
                $full_address[] = $names_states[$partner['id_state']]['state'];
                $full_address[] = $names_state_cities[$partner['id_city']];
            } elseif (!empty($names_cities[$partner['id_city']]['city'])) {
                $full_address[] = $names_cities[$partner['id_city']];
            }

            $group_name = '';
            if (!empty($groups[$partner['user_group']])) {
                $group_name = $groups[$partner['user_group']]['gr_name'];
            }

            $partner_image_url = getDisplayImageLink(['{ID}' => $partner['id_partner'], '{FILE_NAME}' => $partner['logo_company']], 'companies.main', ['thumb_size' => 1]);

            $_partner_block = '<div class="flex-card">
									<div class="flex-card__fixed main-data-table__item-img image-card">
										<a class="link" href="' . $partner_link . '">
											<img class="image" src="' . $partner_image_url . '"/>
										</a>
									</div>
									<div class="flex-card__float">
										<div class="main-data-table__item-ttl" title="' . cleanOutput($partner['name_company']) . '">
											<a class="display-ib link-black txt-medium" href="' . $partner_link . '">'
                . $partner['name_company']
                . '</a>
										</div>

										<div class="companies__group ' . (in_array($group_name, $item_user_groups) ? 'txt-orange' : '') . '">' . $group_name . '</div>
									</div>
								</div>';

            $chatBtn = new ChatButton(['recipient' => $partner['id_seller'], 'recipientStatus' => $partner['user_status']]);

            $output['aaData'][] = [
                'partner_dt'    => $_partner_block,
                'address_dt'    => '<div>
                                <img width="24" height="24" src="' . getCountryFlag($names_countries[$partner['id_country']]['country']) . '" title="' . $names_countries[$partner['id_country']]['country'] . '" alt="' . $names_countries[$partner['id_country']]['country'] . '"/> '
                    . $names_countries[$partner['id_country']]['country']
                    . '</div>'
                    . '<div class="txt-gray">' . implode(', ', $full_address) . '</div>',
                'contact_dt'              => "{$partner['email_company']}<br/><a class=\"link-black txt-medium text-nowrap\" href=\"tel:{$partner['phone_company']}\">{$partner['phone_company']}</a>",
                'date_partnership_dt'     => getDateFormat($partner['date_partner'], null, 'j M, Y H:i'),
                'actions_dt'              => '<div class="dropdown">
                                    <a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="ep-icon ep-icon_menu-circles"></i>
                                    </a>

                                    <div class="dropdown-menu">'
                                        . $chatBtn->button() .
                                        '<a class="dropdown-item confirm-dialog" data-callback="delete_partner" data-message="Are you sure you want to delete this partner?" data-partner="' . $partner['id_partner'] . '" data-seller="' . $partner['id_seller'] . '" href="#" title="Delete partner">
                                            <i class="ep-icon ep-icon_trash-stroke"></i>
                                            <span class="txt">Delete partner</span>
                                        </a>
                                    </div>
                                </div>',
            ];
        }

        jsonResponse('', 'success', $output);
    }

    public function ajax_my_partners()
    {
        checkIsAjax();
        checkIsLoggedAjaxDT();
        checkHaveCompanyAjaxDT();
        checkPermisionAjaxDT('manage_b2b_requests');

        /**
         * @var Company_Model $companyModel
         */
        $companyModel = model(Company_Model::class);

        /**
         * @var Country_Model $countryModel
         */
        $countryModel = model(Country_Model::class);

        /**
         * @var B2b_Model $b2bModel
         */
        $b2bModel = model(B2b_Model::class);

        /**
         * @var UserGroup_Model $userGroupModel
         */
        $userGroupModel = model(UserGroup_Model::class);

        $user_companies = implode(',', $this->session->companies);

        $sortBy = flat_dt_ordering($_POST, [
            'date_partnership_dt' => 'bp.date_partnership',
        ]);

        $userFilters = [
            'companies_list'    => $user_companies,
            'sort_by'           => empty($sortBy) ? ['bp.date_partnership-desc'] : $sortBy,
            'per_p'             => (int) $_POST['iDisplayLength'],
            'from'              => (int) $_POST['iDisplayStart'],
        ];

        $conditions = dtConditions($_POST, [
            ['as' => 'added_start',       'key' => 'start_from',     'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'added_finish',      'key' => 'start_to',       'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'id_partner',        'key' => 'id_partner',     'type' => 'int'],
            ['as' => 'id_country',        'key' => 'id_country',     'type' => 'int'],
            ['as' => 'id_city',           'key' => 'id_city',        'type' => 'int'],
            ['as' => 'keywords',          'key' => 'keywords',       'type' => 'cleanInput|cut_str'],
        ]);

        $userFilters = array_merge($userFilters, $conditions);

        $partners = $b2bModel->get_partners($userFilters);
        $total_partners = $b2bModel->get_count_partners($userFilters);

        $output = [
            'sEcho'                 => (int) $_POST['sEcho'],
            'iTotalRecords'         => $total_partners,
            'iTotalDisplayRecords'  => $total_partners,
            'aaData'                => [],
        ];

        if (empty($partners)) {
            jsonResponse('', 'success', $output);
        }

        $companies = $companyModel->get_companies_main_info(['companies_list' => $user_companies]);
        $companies = arrayByKey($companies, 'id_company');

        $list_countries = $list_cities = $list_states = $list_state_cities = $list_groups = [];
        $item_user_groups = ['Certified Seller', 'Certified Manufacturer'];

        foreach ($partners as $item) {
            $list_groups[] = $item['user_group'];
            $list_countries[$item['id_country']] = $item['id_country'];

            if (!empty($item['id_state']) && $item['id_state'] > 0) {
                $list_states[$item['id_state']] = $item['id_state'];
                $list_state_cities[$item['id_city']] = $item['id_city'];
            } else {
                $list_cities[$item['id_city']] = $item['id_city'];
            }
        }

        $list_countries = array_filter($list_countries);
        $list_states = array_filter($list_states);
        $list_state_cities = array_filter($list_state_cities);
        $list_cities = array_filter($list_cities);
        $groups = $userGroupModel->getGroups(['id_groups' => implode(',', $list_groups)]);

        if (!empty($groups)) {
            $groups = arrayByKey($groups, 'idgroup');
        }

        if (!empty($list_countries)) {
            $names_countries = $countryModel->get_simple_countries(implode(',', $list_countries));
        }

        if (!empty($list_states)) {
            $names_states = $countryModel->get_simple_states(implode(',', $list_states));
        }

        if (!empty($list_state_cities)) {
            $names_state_cities = $countryModel->get_simple_cities_by_state(implode(',', $list_state_cities));
        }

        if (!empty($list_cities)) {
            $names_cities = $countryModel->get_simple_cities(implode(',', $list_cities));
        }

        foreach ($partners as $partner) {
            $partnerUrlComponents = [
                'type_company'  => $partner['type_company'],
                'index_name'    => $partner['index_name'],
                'name_company'  => $partner['name_company'],
                'id_company'    => $partner['id_partner'],
            ];

            $partner_link = getCompanyURL($partnerUrlComponents);
            $company_link = getCompanyURL($companies[$partner['id_company']]);

            $full_address = [];
            if (!empty($names_states[$partner['id_state']]['state'])) {
                $full_address[] = $names_states[$partner['id_state']]['state'];
                $full_address[] = $names_state_cities[$partner['id_city']];
            } elseif (!empty($names_cities[$partner['id_city']]['city'])) {
                $full_address[] = $names_cities[$partner['id_city']];
            }

            $group_name = '';
            if (!empty($groups[$partner['user_group']])) {
                $group_name = $groups[$partner['user_group']]['gr_name'];
            }

            $partner_image_url = getDisplayImageLink(['{ID}' => $partner['id_partner'], '{FILE_NAME}' => $partner['logo_company']], 'companies.main', ['thumb_size' => 1]);
            $partnerImageAtas = addQaUniqueIdentifier('b2b-my-partners__table_partner-image');
            $partnerTitleAtas = addQaUniqueIdentifier('b2b-my-partners__table_partner-name');
            $partnerGroupAtas = addQaUniqueIdentifier('b2b-my-partners__table_partner-group');
            $partnerCompanyTitleAtas = addQaUniqueIdentifier('b2b-my-partners__table_partner-company');
            $_partner_block = '<div class="flex-card">
									<div class="companies__img image-card-center flex-card__fixed w-100 h-100">
										<a class="link" href="' . $partner_link . '" target="_blank">
											<img class="image" ' . $partnerImageAtas . ' itemprop="logo" src="' . $partner_image_url . '"/>
										</a>
									</div>
									<div class="companies__detail flex-card__float pr-0 pt-0 pb-0 mw-300">
										<div class="companies__ttl companies__ttl--mw-207" title="' . cleanOutput($partner['name_company']) . '">
											<a class="link" itemprop="url" href="' . $partner_link . '" ' . $partnerTitleAtas . '>
												<span itemprop="name">' . $partner['name_company'] . '</span>
											</a>
										</div>

										<div class="companies__row">
											<div class="companies__group ' . (in_array($group_name, $item_user_groups) ? 'txt-orange' : '') . '" ' . $partnerGroupAtas . '>' . $group_name . '</div>
										</div>

										<div class="main-data-table__item-ttl">
											<span class="txt-gray">Partner of: </span>
											<a class="display-ib link-black txt-medium" ' . $partnerCompanyTitleAtas . ' href="' . $company_link . '" title="' . cleanOutput($companies[$partner['id_company']]['name_company']) . '" target="_blank">
												' . $companies[$partner['id_company']]['name_company'] . '
											</a>
										</div>
									</div>
                                </div>';

            $chatBtn = new ChatButton(['recipient' => $partner['id_user'], 'recipientStatus' => $partner['user_status']]);
            $partnerCountryFlag = addQaUniqueIdentifier('b2b-my-partners__table_partner-flag-image');
            $partnerCountryAtas = addQaUniqueIdentifier('b2b-my-partners__table_partner-country');
            $partnerAddressAtas = addQaUniqueIdentifier('b2b-my-partners__table_partner-address');
            $partnerEmailAtas = addQaUniqueIdentifier('b2b-my-partners__table_partner-email');
            $partnerPhoneAtas = addQaUniqueIdentifier('b2b-my-partners__table_partner-phone');
            $dropdownBtnAtas = addQaUniqueIdentifier('b2b-my-partners__table_dropdown-btn');
            $deletePartnerBtnAtas = addQaUniqueIdentifier('b2b-my-partners__table_dropdown_delete-partner-btn');
            $output['aaData'][] = [
                'partner_dt' => $_partner_block,
                'address_dt' => '<img width="24" height="24" src="' . getCountryFlag($names_countries[$partner['id_country']]['country']) . '" ' . $partnerCountryFlag . ' title="' . $names_countries[$partner['id_country']]['country'] . '" alt="' . $names_countries[$partner['id_country']]['country'] . '"/> <span ' . $partnerCountryAtas . '>' .
                    implode(', ', $full_address) . '</span><br/> <span ' . $partnerAddressAtas . '>' . $partner['address_company'] . '</span>',
                'contact_dt'          => "<span {$partnerEmailAtas}>{$partner['email_company']}</span><br/><a class=\"link-black txt-medium text-nowrap\" {$partnerPhoneAtas} href=\"tel:{$partner['phone_company']}\">{$partner['phone_company']}</a>",
                'date_partnership_dt' => getDateFormat($partner['date_partnership'], null, 'j M, Y H:i'),
                'actions_dt'          => '<div class="dropdown">
                                    <a class="dropdown-toggle" ' . $dropdownBtnAtas . ' data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="ep-icon ep-icon_menu-circles"></i>
                                    </a>

                                    <div class="dropdown-menu">'
                                        . $chatBtn->button() .
                                        '<a class="dropdown-item confirm-dialog" ' . $deletePartnerBtnAtas . ' data-callback="delete_partner" data-message="Are you sure you want to delete this partner?" data-partner="' . $partner['id_partner'] . '" data-company="' . $partner['id_company'] . '" href="#" title="Delete partner">
                                            <i class="ep-icon ep-icon_trash-stroke"></i>
                                            <span class="txt">Delete partner</span>
                                        </a>
                                    </div>
                                </div>',
            ];
        }

        jsonResponse('', 'success', $output);
    }

    public function popup_forms()
    {
        checkIsAjax();
        checkIsLoggedAjaxModal();

        $op = $this->uri->segment(3);
        $id = intval($this->uri->segment(4));

        switch ($op) {
            case 'email':
                $data['id_request'] = $id;
                $data['webpackData'] = "webpack" === request()->headers->get("X-Script-Mode", "legacy");
                $this->view->assign($data);
                $this->view->display('new/b2b/popup_email_view');

                break;
            case 'share':
                $data['id_request'] = $id;
                $data['webpackData'] = "webpack" === request()->headers->get("X-Script-Mode", "legacy");
                $this->view->assign($data);
                $this->view->display('new/b2b/popup_share_view');

                break;
            case 'become_partener':
                checkPermisionAjaxModal('manage_b2b_requests');
                //checkHaveCompanyAjaxModal();

                if (!$id) {
                    messageInModal('Error: Request information is not set. Please refresh this page.');
                }

                $this->load->model('B2b_Model', 'b2b');
                $this->load->model('Branch_Model', 'branch');

                if ($this->b2b->is_my_request($id, privileged_user_id())) {
                    messageInModal('Error: You cannot respond to your request. Please close this window.');
                }

                $data = [
                    'branches'   => $this->branch->get_company_branches(my_company_id()),
                    'id_request' => $id,
                ];

                $this->view->assign($data);
                $this->view->display('new/b2b/popup_become_partener_view');

                break;
            case 'add_advice':
                checkHaveCompanyAjaxModal();

                $data['id_request'] = $id;
                if (empty($data['id_request'])) {
                    messageInModal(translate('systmess_error_request_does_not_exist'));
                }

                $data['write_advice'] = false;
                $id_user = privileged_user_id();

                $this->load->model('Company_Model', 'company');
                $this->load->model('B2b_Model', 'b2b');

                $request_info = $this->b2b->get_simple_b2b_request($data['id_request']);
                if (empty($request_info)) {
                    messageInModal(translate('systmess_error_request_does_not_exist'));
                }

                $my_companies = $this->company->get_user_companies($id_user);
                $my_partners = $this->b2b->get_partners(['companies_list' => $my_companies]);

                if (!empty($my_partners)) {
                    $my_partners_list = [];
                    foreach ($my_partners as $my_partner) {
                        $my_partners_list[] = $my_partner['id_partner'];
                    }

                    if (in_array($request_info['id_company'], $my_partners_list) && !$this->b2b->exist_request_advice($data['id_request'], $id_user)) {
                        $data['write_advice'] = true;
                    }
                }

                if (!$data['write_advice']) {
                    messageInModal('The advice can be left only once. You already wrote an advice.', 'info');
                }

                $this->view->assign($data);
                $this->view->display('new/b2b/request/add_advice_view');

                break;
            case 'edit_advice':
                checkHaveCompanyAjaxModal();

                $this->load->model('B2b_Model', 'b2b');

                if (!$this->b2b->exist_advice($id, id_session())) {
                    messageInModal('Error: This advice does not exist.');
                }

                if ($this->b2b->advice_moderated($id, id_session())) {
                    messageInModal('Error: This advice has been already moderated and cannot be edited.');
                }

                $data['advice'] = $this->b2b->get_advice(['id_advice' => $id]);

                $this->view->assign($data);
                $this->view->display('new/b2b/request/edit_advice_view');

                break;
        }
    }

    public function ajax_send_email()
    {
        checkIsAjax();
        checkIsLoggedAjax();

        $this->load->model('B2b_Model', 'b2b');
        $this->load->model('User_Model', 'user');

        $op = $this->uri->segment(3);

        is_allowed('freq_allowed_send_email_to_user');

        switch ($op) {
            case 'email':
                checkPermisionAjax('email_this');

                global $tmvc;
                $validator_rules = [
                    [
                        'field' => 'mess',
                        'label' => 'Message',
                        'rules' => ['required' => '', 'max_len[1000]' => ''],
                    ],
                    [
                        'field' => 'inp',
                        'label' => 'Email address',
                        'rules' => ['required' => '', 'no_whitespaces' => '', 'valid_emails' => '', 'max_emails_count[' . $tmvc->my_config['email_this_max_email_count'] . ']' => ''],
                    ],
                    [
                        'field' => 'id',
                        'label' => 'Request info',
                        'rules' => ['required' => '', 'integer' => ''],
                    ],
                ];

                $this->validator->set_rules($validator_rules);

                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $filteredEmails = filter_email($_POST['inp']);

                if (empty($filteredEmails)) {
                    jsonResponse('Error: Please write at least one valid email address.');
                }

                $idRequest = intval($_POST['id']);
                $request = $this->b2b->get_b2b_request($idRequest);

                if (empty($request)) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                try {
                    //get image link for the news
                    /** @var FilesystemProviderInterface  $storageProvider */
                    $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                    $publicDisk = $storageProvider->storage('public.storage');
                    $pathToFile = CompanyLogoFilePathGenerator::thumbImage($request['id_company'], $request['logo_company'], CompanyLogoThumb::MEDIUM());
                    //Email friends about B2B request
                    /** @var MailerInterface $mailer */
                    $mailer = $this->getContainer()->get(MailerInterface::class);
                    $mailer->send(
                        (new EmailFriendAboutB2b(cleanInput(request()->request->get('mess')), $request, $publicDisk->url($pathToFile)))
                            ->to(...array_map(fn (string $to) => new Address($to), $filteredEmails))
                    );
                } catch (\Throwable $th) {
                    jsonResponse(translate('email_has_not_been_sent'));
                }

                jsonResponse('Your email has been successfully sent.', 'success');

                break;
            case 'share':
                checkPermisionAjax('share_this');

                $validator_rules = [
                    [
                        'field' => 'mess',
                        'label' => 'Message',
                        'rules' => ['required' => '', 'max_len[1000]' => ''],
                    ],
                    [
                        'field' => 'id',
                        'label' => 'Request info',
                        'rules' => ['required' => '', 'integer' => ''],
                    ],
                ];

                $this->validator->set_rules($validator_rules);

                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $this->load->model('Followers_model', 'followers');

                $idUser = privileged_user_id();
                $filteredEmails = $this->followers->getFollowersEmails($idUser);

                if (empty($filteredEmails)) {
                    jsonResponse('You have no followers. The message has not been sent.');
                }

                $idRequest = (int) $_POST['id'];
                $request = $this->b2b->get_b2b_request($idRequest);

                if (empty($request)) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                //Email followers about B2B request
                try {
                    /** @var FilesystemProviderInterface  $storageProvider */
                    $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                    $publicDisk = $storageProvider->storage('public.storage');
                    $pathToFile = CompanyLogoFilePathGenerator::thumbImage($request['id_company'], $request['logo_company'], CompanyLogoThumb::MEDIUM());
                    /** @var MailerInterface $mailer */
                    $mailer = $this->getContainer()->get(MailerInterface::class);
                    $mailer->send(
                        (new EmailFriendAboutB2b(cleanInput(request()->request->get('mess')), $request, $publicDisk->url($pathToFile)))
                            ->to(...array_map(fn (int $id, string $to) => new RefAddress((string) $id, new Address($to)), array_column($filteredEmails, 'idu', 'idu'), array_column($filteredEmails, 'email', 'email')))
                    );
                } catch (\Throwable $th) {
                    jsonResponse(translate('email_has_not_been_sent'));
                }

                jsonResponse('Your email has been successfully sent.', 'success');

                break;
        }
    }

    public function ajax_b2b_operation()
    {
        checkIsAjax();
        checkIsLoggedAjax();

        $option = $this->uri->segment(3);
        $request = request();
        switch ($option) {
            case 'more_followers':
                //get the data sent from post
                $requestId = request()->request->getInt('request');
                $from = request()->request->getInt('start');
                if (empty($requestId)) {
                    jsonResponse(translate('systmess_error_resource_id_not_found'));
                }

                /** @var B2b_Followers_Model $b2bFollowersRepository */
                $b2bFollowersRepository = model(B2b_Followers_Model::class);
                /** @var Users_Model $usersModel */
                $usersModel = model(Users_Model::class);
                $usersTable = $usersModel->getTable();
                /** @var User_Groups_Model $usersGroupsModel */
                $usersGroupsModel = model(User_Groups_Model::class);
                $usersGroupsTable = $usersGroupsModel->getTable();
                //get the followers from database with limit and skip
                $followers = $b2bFollowersRepository->findAllBy([
                    'columns' => [
                        "{$b2bFollowersRepository->getTable()}.*",
                        "CONCAT({$usersTable}.fname, ' ', {$usersTable}.lname) as username",
                        "{$usersTable}.user_photo",
                        "{$usersTable}.status",
                        "{$usersTable}.user_group",
                        "{$usersGroupsTable}.gr_name as group_name",
                    ],
                    'scopes'  => ['requestId' => $requestId],
                    'joins'   => ['extendedUsers'],
                    'limit'   => (int) config('b2b_detail_page_followers_per_page', 8),
                    'skip'    => $from,
                    'order'   => ['date_follow'=>'desc'],
                ]);
                //get the total number of followers
                $followersCount = $b2bFollowersRepository->countAllBy(['scopes' => ['requestId' => $requestId]]);
                if (empty($followers)) {
                    jsonResponse('', 'no more', ['total' => $followersCount]);
                }
                //add chat btn and image url to each follower
                $isCurrentUserLogged = logged_in();
                $followers = array_map(
                    function ($userFollower) use ($isCurrentUserLogged) {
                        if ($isCurrentUserLogged) {
                            $chatBtn = new ChatButton(['recipient' => $userFollower['id_user'], 'recipientStatus' => $userFollower['status']]);
                            $userFollower['btnChat'] = $chatBtn->button();
                        }
                        //create the link to the user logo
                        $userFollower['logoLink'] = getDisplayImageLink(
                            ['{ID}' => $userFollower['id_user'], '{FILE_NAME}' => $userFollower['user_photo']],
                            'users.main',
                            [
                                'thumb_size'     => 0,
                                'no_image_group' => $userFollower['user_group'],
                            ]
                        );

                        return $userFollower;
                    },
                    $followers
                );
                //if there are followers then we return the view with them
                $followersContent = $this->view->fetch('new/b2b/detail/followers_view', ['followers' => $followers]);
                jsonResponse('', 'success', ['content' => $followersContent, 'totalCount' => $followersCount]);

                break;
            case 'more_partners':
                //get the data sent from post
                $requestId = request()->request->getInt('request');
                $from = request()->request->getInt('start');
                //if no id is set return error
                if (empty($requestId)) {
                    jsonResponse(translate('systmess_error_resource_id_not_found'));
                }
                //get the request or return error
                try {
                    $request = $this->b2bRequestProvider->getRequest($requestId);
                } catch (B2bRequestNotFoundException $e) {
                    jsonResponse(translate('systmess_error_resource_id_not_found'));
                }
                /** @var B2b_Partners_Model $b2bPartnersRepository */
                $b2bPartnersRepository = model(B2b_Partners_Model::class);
                $partners = $b2bPartnersRepository->findAllBy([
                    'scopes' => ['companyId' => $request['id_company']],
                    'with'   => ['partner as company'],
                    'limit'  => (int) config('b2b_detail_page_partners_per_page', 6),
                    'skip'   => $from,
                    'order'  => ['date_partnership' => 'DESC'],
                ]);
                //get the total number of partners
                $countPartners = $b2bPartnersRepository->countAllBy(['scopes' => ['companyId' => $request['id_company']]]);
                if (empty($partners)) {
                    jsonResponse('', 'no more', ['total' => $countPartners]);
                }
                //add image and chat button to all partners
                $isCurrentUserLogged = logged_in();
                $partners = array_map(
                    function ($partner) use ($isCurrentUserLogged) {
                        if ($isCurrentUserLogged) {
                            $chatBtn = new ChatButton(['recipient' => $partner['id_partner'], 'recipientStatus' => $partner['company']['user_status']]);
                            $partner['btnChat'] = $chatBtn->button();
                        }
                        $partner['logoLink'] = getDisplayImageLink(
                            ['{ID}' => $partner['id_partner'], '{FILE_NAME}' => $partner['company']['logo_company']],
                            'companies.main',
                            [
                                'thumb_size'     => 0,
                                'no_image_group' => 'dynamic',
                                'image_size'     => ['w' => 88, 'h' => 88],
                            ]
                        );

                        return $partner;
                    },
                    $partners
                );
                //if there are followers then we return the view with them
                $partnersContent = $this->view->fetch('new/b2b/detail/partners_view', ['partners' => $partners]);
                jsonResponse('', 'success', ['content' => $partnersContent, 'totalCount' => $countPartners]);

                break;
            case 'more_advices':
                //get the data sent from post
                $requestId = request()->request->getInt('request');
                $from = request()->request->getInt('start');
                //if no id is set return error
                if (empty($requestId)) {
                    jsonResponse(translate('systmess_error_resource_id_not_found'));
                }
                //get the request or return error
                try {
                    $request = $this->b2bRequestProvider->getRequest($requestId);
                } catch (B2bRequestNotFoundException $e) {
                    jsonResponse(translate('systmess_error_resource_id_not_found'));
                }
                /** @var B2b_Advice_Model $b2bAdviceRepository */
                $b2bAdviceRepository = model(B2b_Advice_Model::class);

                /** @var Users_Model $usersModel */
                $usersModel = model(Users_Model::class);
                $usersTable = $usersModel->getTable();

                $adviceData = $b2bAdviceRepository->findAllBy([
                    'columns' => [
                        "{$b2bAdviceRepository->getTable()}.*",
                        "CONCAT({$usersTable}.fname, ' ', {$usersTable}.lname) as username",
                        "{$usersTable}.user_photo",
                        "{$usersTable}.user_group",
                        "{$usersTable}.status",
                    ],
                    'scopes' => ['requestId' => $requestId],
                    'joins'  => ['users'],
                    'limit'  => (int) config('b2b_detail_page_advice_per_page', 3),
                    'skip'   => $from,
                    'order'  => ['date_advice' => 'DESC'],
                ]);
                //get the total number of advice
                $countAdvice = $b2bAdviceRepository->countAllBy(['scopes' => ['requestId' => $request['id_request']]]);
                if (empty($adviceData)) {
                    jsonResponse('', 'no more', ['total' => $countAdvice]);
                }
                //add image and chat button to all advice
                $isCurrentUserLogged = logged_in();
                $adviceData = array_map(
                    function ($advice) use ($isCurrentUserLogged) {
                        if ($isCurrentUserLogged) {
                            $chatBtn = new ChatButton(['recipient' => $advice['id_user'], 'recipientStatus' => $advice['status']]);
                            $advice['btnChat'] = $chatBtn->button();
                        }
                        $advice['logoLink'] = getDisplayImageLink(
                            ['{ID}' => $advice['id_user'], '{FILE_NAME}' => $advice['user_photo']],
                            'users.main',
                            [
                                'thumb_size'     => 0,
                                'no_image_group' => $advice['user_group'],
                            ]
                        );

                        return $advice;
                    },
                    $adviceData
                );
                //get helpful by current user for the list of advices
                /** @var B2b_Advice_Helpful_Model $b2bAdviceHelpfulRepository */
                $b2bAdviceHelpfulRepository = model(B2b_Advice_Helpful_Model::class);
                $helpful = array_column($b2bAdviceHelpfulRepository->findAllBy([
                    'scopes' => ['adviceIds' => array_column($adviceData, 'id_advice'), 'userId' => privileged_user_id()],
                ]), 'help', 'id_advice');

                //if there are followers then we return the view with them
                $adviceContent = $this->view->fetch('new/b2b/detail/advices_view', ['advices' => $adviceData, 'helpful' => $helpful]);
                jsonResponse('', 'success', ['content' => $adviceContent, 'totalCount' => $countAdvice]);

                break;
            case 'more_b2b_requests':
                //get the data sent from post
                $requestId = request()->request->getInt('request');
                $from = request()->request->getInt('start');
                //if no id is set return error
                if (empty($requestId)) {
                    jsonResponse(translate('systmess_error_resource_id_not_found'));
                }
                //get the request or return error
                try {
                    $request = $this->b2bRequestProvider->getRequest($requestId);
                } catch (B2bRequestNotFoundException $e) {
                    jsonResponse(translate('systmess_error_resource_id_not_found'));
                }
                //region other b2b requests
                $otherRequests = $this->b2bRequestProvider->getOtherRequestsThan(
                    $request['id_request'],
                    $request['id_user'],
                    (int) config('b2b_detail_page_other_b2b_limit', 4),
                    $from
                );
                //get the total number of requests
                $countRequests = $this->b2bRequestProvider->getCountOtherRequestsThan($request['id_request'], $request['id_user']);
                if (empty($otherRequests)) {
                    jsonResponse('', 'no more', ['total' => $countRequests]);
                }
                $isCurrentUserLogged = logged_in();
                $otherRequests = array_map(
                    function ($otherRequest) use ($isCurrentUserLogged) {
                        if ($isCurrentUserLogged) {
                            $chatBtn = new ChatButton(['recipient' => $otherRequest['id_user'], 'recipientStatus' => 'active']);
                            $otherRequest['btnChat'] = $chatBtn->button();
                        }
                        //create the link to the user logo
                        $otherRequest['mainImageLink'] = getDisplayImageLink(
                            [
                                '{FOLDER_PATH}' => $otherRequest['id_request'],
                                '{FILE_NAME}'   => $otherRequest['mainImage']['photo'],
                            ],
                            'b2b_request.main',
                            [
                                'thumb_size'     => 2,
                                'no_image_group' => 'dynamic',
                                'image_size'     => ['w' => 213, 'h' => 160],
                            ]
                        );

                        return $otherRequest;
                    },
                    $otherRequests
                );
                //if there are requests then we return the view with them
                $requestsContent = $this->view->fetch('new/b2b/detail/other_requests_view', ['userRequests' => $otherRequests]);
                jsonResponse('', 'success', ['content' => $requestsContent, 'totalCount' => $countRequests]);

                break;
            case 'get_requests':
                checkHaveCompany();
                checkPermisionAjax('manage_b2b_requests');

                $company = intval($_POST['company']);
                if (!$company) {
                    jsonResponse('Error: This company does not exist.');
                }

                $this->load->model('Company_Model', 'company');
                $this->load->model('Country_Model', 'country');
                $this->load->model('B2b_Model', 'b2b');

                $id_user = privileged_user_id();
                if (!$this->company->is_my_company_branch(['id_company' => $company], $id_user)) {
                    jsonResponse('Error: This company is not yours.');
                }

                // CALCULATE LIMIT - FOR DASHBOARD PAGINATION
                global $tmvc;
                $per_page = $tmvc->my_config['user_requests_per_page'];
                $page = intval($_POST['page']);

                $main_cond = [
                    'id_user'    => privileged_user_id(),
                    'status'     => 'all',
                    'id_company' => $company,
                ];
                $total_requests_by_company = $this->b2b->count_b2b_requests($main_cond);

                $main_cond['count'] = $total_requests_by_company;
                $main_cond['page'] = $page;
                $main_cond['per_p'] = $per_page;

                $data['b2b_requests'] = $this->b2b->get_b2b_requests($main_cond);

                if (empty($data['b2b_requests'])) {
                    jsonResponse('', 'info', ['total_requests_by_company' => 0]);
                }

                $list_requests = [];
                // commented because there is no id_country in requests anymore
                //$list_countries = [];
                foreach ($data['b2b_requests'] as $item) {
                    //$list_countries[$item['id_country']] = $item['id_country'];
                    $list_requests[] = $item['id_request'];
                }

                // $list_countries = array_filter($list_countries);
                // if (!empty($list_countries)) {
                //     $data['countries'] = $this->country->get_simple_countries(implode(',', $list_countries));
                // }

                $count_b2b_response = $this->b2b->count_b2b_response(implode(',', $list_requests));
                $data['count_b2b_response'] = arrayByKey($count_b2b_response, 'id_request');

                $content = $this->view->fetch('new/b2b/request/requests_list_view', $data);

                jsonResponse('', 'success', ['requests' => $content, 'total_requests_by_company' => $total_requests_by_company]);

                break;
            case 'change_request_status':
                checkHaveCompany();
                checkPermisionAjax('manage_b2b_requests');

                /** @var B2b_Model $b2bModel */
                $b2bModel = model(B2b_Model::class);

                $b2bRequestId = request()->request->getInt('request');
                $userId = privileged_user_id();

                if (empty($b2bRequestId) || empty($b2bRequest = $b2bModel->get_simple_b2b_request($b2bRequestId)) || $userId != $b2bRequest['id_user']) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                $b2bModel->update_request($b2bRequestId, ['status' => 'enabled' == $b2bRequest['status'] ? 'disabled' : 'enabled']);

                /** @var Elasticsearch_B2b_Model $elasticsearchB2bModel */
                $elasticsearchB2bModel = model(Elasticsearch_B2b_Model::class);

                if ('enabled' == $b2bRequest['status']) {
                    $elasticsearchB2bModel->removeB2bRequestById($b2bRequestId);
                } else {
                    $elasticsearchB2bModel->index($b2bRequestId);
                }

                if ('prod' === config('env.APP_ENV') && !empty($b2bRequest['id_ticket'])) {
                    /** @var TinyMVC_Library_Zoho_Desk $zohoDeskLibrary */
                    $zohoDeskLibrary = library(TinyMVC_Library_Zoho_Desk::class);

                    try {
                        $zohoDeskLibrary->createTicketComment((int) $b2bRequest['id_ticket'], [
                            'contentType'   => 'plainText',
                            'isPublic'      => false,
                            'content'       => 'The B2B request was ' . ('enabled' == $b2bRequest['status'] ? 'unpublished' : 're-published') . ' by the seller on ' . (new DateTime())->format('j M, Y H:i'),
                        ]);
                    } catch (Exception $e) {
                    }
                }

                jsonResponse(
                    'Request status was successfully changed.',
                    'success',
                    [
                        'status'        => 'enabled' == $b2bRequest['status'] ? 'invisible' : 'visible',
                        'remove_status' => 'enabled' == $b2bRequest['status'] ? 'visible' : 'invisible',
                    ]
                );

                break;
            case 'delete_request':
                checkHaveCompany();
                checkPermisionAjax('manage_b2b_requests');

                /** @var B2b_Model $b2bModel */
                $b2bModel = model(B2b_Model::class);

                $b2bRequestId = request()->request->getInt('request');
                $userId = privileged_user_id();

                if (empty($b2bRequestId) || empty($b2bRequest = $b2bModel->get_simple_b2b_request($b2bRequestId)) || $userId != $b2bRequest['id_user']) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                $b2bModel->delete_advice(['id_request' => $b2bRequestId]);
                $b2bModel->delete_response(['id_request' => $b2bRequestId]);
                $b2bModel->delete_request(['id_request' => $b2bRequestId]);
                $b2bModel->delete_request_relation_industry($b2bRequestId);
                $b2bModel->delete_request_relation_category($b2bRequestId);

                /** @var TinyMVC_Library_Wall $wallLibrary */
                $wallLibrary = library(TinyMVC_Library_Wall::class);
                $wallLibrary->remove([
                    'type'       => 'b2b_request',
                    'id_item'    => $b2bRequestId,
                ]);

                /** @var User_Statistic_Model $userStatisticsModel */
                $userStatisticsModel = model(User_Statistic_Model::class);
                $userStatisticsModel->set_users_statistic([
                    $userId => ['b2b_requests' => -1],
                ]);

                /** @var Elasticsearch_B2b_Model $elasticsearchB2bModel */
                $elasticsearchB2bModel = model(Elasticsearch_B2b_Model::class);
                $elasticsearchB2bModel->removeB2bRequestById($b2bRequestId);

                if ('prod' === config('env.APP_ENV') && !empty($b2bRequest['id_ticket'])) {
                    /** @var TinyMVC_Library_Zoho_Desk $zohoDeskLibrary */
                    $zohoDeskLibrary = library(TinyMVC_Library_Zoho_Desk::class);

                    try {
                        $zohoDeskLibrary->createTicketComment((int) $b2bRequest['id_ticket'], [
                            'contentType'   => 'plainText',
                            'isPublic'      => false,
                            'content'       => 'The B2B request was deleted by the seller on ' . (new DateTime())->format('j M, Y H:i'),
                        ]);
                    } catch (Exception $e) {
                    }
                }

                jsonResponse('The request was deleted successfully.', 'success');

                break;
            case 'get_request_response':
                checkHaveCompany();
                checkPermisionAjax('manage_b2b_requests');

                $request = intval($_POST['request']);
                if (!$request) {
                    jsonResponse(translate('systmess_error_request_does_not_exist'));
                }

                $this->load->model('B2b_Model', 'b2b');

                $id_user = privileged_user_id();
                if (!$this->b2b->is_my_request($request, $id_user)) {
                    jsonResponse('Error: This request is not yours.');
                }

                // CALCULATE LIMIT - FOR DASHBOARD PAGINATION
                $per_page = config('user_requests_per_page');
                $page = 1;
                if (!empty($_POST['page']) && intval($_POST['page']) > 1) {
                    $page = intval($_POST['page']);
                }

                $start_from = (1 == $page) ? 0 : ($page * $per_page) - $per_page;

                $conditions = ['id_request' => $request];
                $conditions['limit'] = $start_from . ', ' . $per_page;

                $status = cleaninput($_POST['status']);
                if (!empty($status)) {
                    $conditions['status'] = $status;
                }

                $responses = $this->b2b->get_request_responses($conditions);
                $total_response_by_status = $this->b2b->count_request_responses($conditions);

                if (empty($responses)) {
                    jsonResponse('', 'info', ['total_response_by_status' => 0]);
                }

                $data['responses'] = [];

                foreach ($responses as $responsesItem) {
                    $chatBtn = new ChatButton(['recipient' => $responsesItem['id_user'], 'recipientStatus' => 'active', 'module' => 3, 'item' => $responsesItem['id_response']]);
                    $responsesItem['btnChat'] = $chatBtn->button();
                    $data['responses'][] = $responsesItem;
                }

                $content = $this->view->fetch('new/b2b/request/responses_list_view', $data);

                jsonResponse('', 'success', ['responses' => $content, 'total_response_by_status' => $total_response_by_status]);

                break;
            case 'remove_response':
                checkHaveCompany();
                checkPermisionAjax('manage_b2b_requests');

                $this->load->model('B2b_Model', 'b2b');

                $response = (int) $_POST['response'];
                if (!$this->b2b->exist_response(['id_response' => $response])) {
                    jsonResponse('Error: This response does not exist.');
                }

                $id_user = privileged_user_id();
                if (!$this->b2b->is_for_me_response($response, $id_user)) {
                    jsonResponse('Error: This response is not for you.');
                }

                $this->b2b->delete_response(['response' => $response]);
                jsonResponse('Response was deleted successfully', 'success');

                break;
            case 'set_partnership':
                checkHaveCompany();
                checkPermisionAjax('manage_b2b_requests');
                is_allowed('freq_allowed_b2b_operations');

                $id_partner = (int) $_POST['partner'];
                $id_company = (int) $_POST['company'];
                $id_user = privileged_user_id();

                if (empty($id_partner) or empty($id_company)) {
                    jsonResponse('The information you try to send is not correct. Please close this window and refresh the page.');
                }

                $this->load->model('Company_Model', 'company');
                if (!$this->company->exist_company_branch(['company' => $id_partner])) {
                    jsonResponse('Error: This company does not exist.');
                }

                if (!$this->company->is_my_company_branch(['id_company' => $id_company], $id_user)) {
                    jsonResponse('Error: This company is not yours.');
                }

                $this->load->model('B2b_Model', 'b2b');
                if (!$this->b2b->exist_response(['id_partner' => $id_partner, 'id_company' => $id_company])) {
                    jsonResponse('Error: This partner did not respond to your request.');
                }

                if ($this->b2b->exist_partnership($id_company, $id_partner)) {
                    jsonResponse('Error: This company is already your partner.');
                }

                $company = $this->company->get_company(['id_company' => $id_company, 'type_company' => 'all']);
                $partner = $this->company->get_company(['id_company' => $id_partner, 'type_company' => 'all']);

                $this->load->model('Country_Model', 'country');
                $company['location'] = $this->country->get_country_city($company['id_country'], $company['id_city']);
                $partner['location'] = $this->country->get_country_city($partner['id_country'], $partner['id_city']);

                $insert = [
                    [
                        'id_company' => $id_company,
                        'id_partner' => $id_partner,
                        'for_search' => $company['name_company'] . ', ' . $company['location']['country'] . ', ' . $company['location']['city'] . ', ' . $company['address_company'] . ', ' . $company['email_company'] . ', ' . $partner['name_company'] . ', ' . $partner['location']['country'] . ', ' . $partner['location']['city'] . ', ' . $partner['address_company'] . ', ' . $partner['email_company'],
                    ],
                    [
                        'id_company' => $id_partner,
                        'id_partner' => $id_company,
                        'for_search' => $company['name_company'] . ',' . $company['location']['country'] . ', ' . $company['location']['city'] . ', ' . $company['address_company'] . ', ' . $company['email_company'] . ', ' . $partner['name_company'] . ', ' . $partner['location']['country'] . ', ' . $partner['location']['city'] . ', ' . $partner['address_company'] . ', ' . $partner['email_company'],
                    ],
                ];

                if (!$this->b2b->set_partners($insert)) {
                    jsonResponse('Error: You cannot save this company as your partner now. Please try again later.');
                }

                $this->load->model('User_Statistic_Model', 'statistic');
                $this->statistic->set_users_statistic([
                    $company['id_user'] => ['b2b_partners' => 1],
                    $partner['id_user'] => ['b2b_partners' => 1],
                ]);
                $this->b2b->update_response($id_company, $id_partner, ['status' => 'approved']);

                $id_request = $this->b2b->get_request_id_from_response($id_company, $id_partner);

                $data_systmess = [
                    'mess_code' => 'aprove_partnership',
                    'id_item'   => $id_request,
                    'id_users'  => [$partner['id_user']],
                    'replace'   => [
                        '[COMPANY_LINK]' => getCompanyURL($company),
                        '[COMPANY_NAME]' => cleanOutput($company['name_company']),
                        '[LINK]'         => __SITE_URL . 'b2b/my_partners',
                    ],
                    'systmess' => true,
                ];

                $this->load->model('Notify_Model', 'notify');
                $this->notify->send_notify($data_systmess);

                $data_calendar = [
                    'mess_code' => 'company_aprove_partnership',
                    'id_item'   => $id_request,
                    'id_users'  => [$company['id_user']],
                    'replace'   => [
                        '[PARTNER_LINK]' => getCompanyURL($partner),
                        '[PARTNER_NAME]' => cleanOutput($partner['name_company']),
                        '[LINK]'         => __SITE_URL . 'b2b/my_partners',
                    ],
                    'systmess' => false,
                ];
                $this->notify->send_notify($data_calendar);

                jsonResponse('Partnership was aproved successfully.', 'success');

                break;
            case 'delete_partner':
                checkHaveCompany();
                checkPermisionAjax('manage_b2b_requests');

                $partner = intval($_POST['partner']);
                $company = intval($_POST['company']);
                if (!$partner) {
                    jsonResponse('Error: Partner company information is not correct.');
                }

                if (!$company) {
                    jsonResponse('Error: Your company information is not correct.');
                }

                $this->load->model('Country_Model', 'country');
                $this->load->model('B2b_Model', 'b2b');
                $this->load->model('Company_Model', 'company');
                $this->load->model('User_Model', 'user');

                if (!$this->company->exist_company_branch(['company' => $partner])) {
                    jsonResponse('Error: This company does not exist.');
                }

                $id_user = privileged_user_id();
                if (!$this->company->is_my_company_branch(['id_company' => $company], $id_user)) {
                    jsonResponse('Error: This company is not yours.');
                }

                // if (!$this->b2b->exist_response(array('id_partner' => $partner, 'id_company' => $company)) && !$this->b2b->exist_response(array('id_partner' => $company, 'id_company' => $partner)))
                // 	jsonResponse('Error: This partner did not respond to your request.');

                if (!$this->b2b->exist_partnership($company, $partner)) {
                    jsonResponse('Error: This company is not your partner.');
                }

                $data['company'] = $this->company->get_company(['id_company' => $company, 'type_company' => 'all']);
                $data['company']['location'] = $this->country->get_country_city($data['company']['id_country'], $data['company']['id_city']);
                $data['partner'] = $this->company->get_company(['id_company' => $partner, 'type_company' => 'all']);
                $data['partner']['location'] = $this->country->get_country_city($data['partner']['id_country'], $data['partner']['id_city']);

                $this->b2b->delete_partner($company, $partner);
                $this->b2b->delete_response(['company' => $company, 'partner' => $partner]);
                $this->b2b->delete_response(['company' => $partner, 'partner' => $company]);

                $this->load->model('User_Statistic_Model', 'statistic');
                $this->statistic->set_users_statistic([
                    $data['company']['id_user'] => ['b2b_partners' => -1],
                    $data['partner']['id_user'] => ['b2b_partners' => -1],
                ]);

                // EMAIL USER ABOUT CANCELED THE PARTNERSHIP
                $this->load->model('Notify_Model', 'notify');

                $partner = $this->user->getUser($data['partner']['id_user']);

                $data_systmess = [
                    'mess_code' => 'shipping_partnership_canceled',
                    'id_users'  => [$partner['idu']],
                    'replace'   => [
                        '[COMPANY_NAME]' => cleanOutput($data['company']['name_company']),
                        '[PARTNER_NAME]' => cleanOutput($data['partner']['name_company']),
                    ],
                    'systmess' => true,
                ];
                // notify response
                $this->notify->send_notify($data_systmess);

                jsonResponse('Partnership was canceled successfully.', 'success');

                break;
            case 'confirm_shipper_partner':
                checkHaveShipperCompanyAjax();
                checkPermisionAjax('mange_ff_partnership_requests');

                $this->load->model('B2b_Model', 'b2b');

                $id_seller = (int) $_POST['seller'];
                $id_partner = (int) $_POST['partner'];
                $id_shipper = id_session();

                if (!$this->b2b->exist_shipper_partnership(['id_partner' => $id_partner, 'id_shipper' => $id_shipper, 'id_seller' => $id_seller])) {
                    jsonResponse('Error: Request for partnership does not exist.');
                }

                if ($this->b2b->update_shipper_partner($id_partner, ['are_partners' => 1])) {
                    $this->load->model('Shippers_Model', 'shipper');
                    $shipper_info = $this->shipper->get_shipper_by_user($id_shipper);

                    $data_systmess = [
                        'mess_code' => 'shipping_partnership_accepted',
                        'id_users'  => [$id_seller],
                        'replace'   => [
                            '[COMPANY_NAME]' => cleanOutput($shipper_info['co_name']),
                            '[COMPANY_LINK]' => __SITE_URL . 'shipper/' . strForURL($shipper_info['co_name'] . ' ' . $shipper_info['id']),
                            '[LINK]'         => __SITE_URL . 'shippers/my_partners/ep_shippers',
                        ],
                        'systmess' => true,
                    ];
                    // notify response
                    $this->load->model('Notify_Model', 'notify');
                    $this->notify->send_notify($data_systmess);

                    $this->load->model('User_Statistic_Model', 'statistic');
                    $statistics = [
                        $id_shipper => ['b2b_shippers_partners' => 1],
                        $id_seller  => ['b2b_shippers_partners' => 1],
                    ];
                    $this->statistic->set_users_statistic($statistics);
                    jsonResponse('The request for partnership was confirmed successfully.', 'success');
                } else {
                    jsonResponse('Error: You can not confirm request for partnership now. Please try again later.');
                }

                break;
            case 'delete_shipper_partner':
                checkHaveShipperCompanyAjax();
                checkPermisionAjax('mange_ff_partnership_requests');
                is_allowed('freq_allowed_b2b_operations');

                $this->load->model('B2b_Model', 'b2b');

                $id_seller = (int) $_POST['seller'];
                $id_partner = (int) $_POST['partner'];
                $id_shipper = id_session();

                if (!$this->b2b->exist_shipper_partnership(['id_partner' => $id_partner, 'id_shipper' => $id_shipper, 'id_seller' => $id_seller])) {
                    jsonResponse('Error: Request for partnership does not exist.');
                }

                if ($this->b2b->delete_shipper_partner($id_partner)) {
                    $shipper_info = model('shippers')->get_shipper_by_user($id_shipper);

                    $data_systmess = [
                        'mess_code' => 'shipping_partnership_deleted',
                        'id_users'  => [$id_seller],
                        'replace'   => [
                            '[COMPANY_NAME]' => cleanOutput($shipper_info['co_name']),
                            '[COMPANY_LINK]' => __SITE_URL . 'shipper/' . strForURL($shipper_info['co_name'] . ' ' . $shipper_info['id']),
                            '[LINK]'         => __SITE_URL . 'shippers/my_partners',
                        ],
                        'systmess' => true,
                    ];

                    model('notify')->send_notify($data_systmess);

                    $statistics = [
                        $id_shipper => ['b2b_shippers_partners' => -1],
                        $id_seller  => ['b2b_shippers_partners' => -1],
                    ];
                    model('User_Statistic')->set_users_statistic($statistics);
                    jsonResponse('The partnership was removed successfully.', 'success');
                } else {
                    jsonResponse('Error: You can not remove partnership now. Please try again later.');
                }

                break;
            case 'decline_partnership':
                checkHaveCompanyAjax();
                checkPermisionAjax('manage_b2b_requests');
                is_allowed('freq_allowed_b2b_operations');

                $id_partner = (int) $_POST['partner'];
                $id_company = (int) $_POST['company'];
                $id_response = (int) $_POST['response'];

                if (empty($id_partner) or empty($id_company) or empty($id_response)) {
                    jsonResponse('The information you try to send is not correct. Please close this window and refresh the page.');
                }

                $this->load->model('Company_Model', 'company');
                if (!$this->company->exist_company_branch(['company' => $id_partner])) {
                    jsonResponse('Error: This company does not exist.');
                }

                $id_user = privileged_user_id();
                if (!$this->company->is_my_company_branch(['id_company' => $id_company], $id_user)) {
                    jsonResponse('Error: This company is not yours.');
                }

                $this->load->model('B2b_Model', 'b2b');

                if (!$this->b2b->exist_response(['id_response' => $id_response, 'id_partner' => $id_partner, 'id_company' => $id_company])) {
                    jsonResponse('Error: This partner did not respond to your request.');
                }

                if ($this->b2b->exist_partnership($id_company, $id_partner)) {
                    jsonResponse('Error: This company is already your partner. Please unset partnership first.');
                }

                $data['company'] = $this->company->get_company(['id_company' => $id_company, 'type_company' => 'all']);
                $data['partner'] = $this->company->get_company(['id_company' => $id_partner, 'type_company' => 'all']);

                $this->load->model('Notify_Model', 'notify');

                $data_systmess = [
                    'mess_code' => 'decline_partnership',
                    'id_item'   => $data['response']['id_request'],
                    'id_users'  => [$data['partner']['id_user']],
                    'replace'   => [
                        '[COMPANY_NAME]' => cleanOutput($data['company']['name_company']),
                        '[COMPANY_LINK]' => getCompanyURL($data['company']),
                        '[LINK]'         => __SITE_URL . 'b2b/all',
                    ],
                    'systmess' => true,
                ];
                $this->notify->send_notify($data_systmess);

                $data_calendar = [
                    'mess_code' => 'company_decline_partnership',
                    'id_item'   => $data['response']['id_request'],
                    'id_users'  => [$data['company']['id_user']],
                    'replace'   => [
                        '[PARTNER_NAME]' => cleanOutput($data['partner']['name_company']),
                        '[PARTNER_LINK]' => getCompanyURL($data['partner']),
                        '[COMPANY_NAME]' => cleanOutput($data['company']['name_company']),
                        '[COMPANY_LINK]' => getCompanyURL($data['company']),
                        '[LINK]'         => __SITE_URL . 'b2b/my_requests',
                    ],
                    'systmess' => false,
                ];
                $this->notify->send_notify($data_calendar);
                $this->b2b->update_response($id_company, $id_partner, ['status' => 'declined']);

                jsonResponse('This response was declined successfully.', 'success', ['request' => $data['response']['id_request'], 'old_status' => $data['response']['status']]);

                break;
            case 'request_partnership':
                checkHaveCompany();
                checkPermisionAjax('manage_b2b_requests');
                is_allowed('freq_allowed_b2b_operations');

                $validator_rules = [
                    [
                        'field' => 'message',
                        'label' => 'Request message',
                        'rules' => ['required' => '', 'max_len[1000]' => ''],
                    ],
                    [
                        'field' => 'request',
                        'label' => 'Request info',
                        'rules' => ['required' => '', 'integer' => ''],
                    ],
                    [
                        'field' => 'company',
                        'label' => 'Company/branch',
                        'rules' => ['required' => ''],
                    ],
                    [
                        'field' => 'message',
                        'label' => 'Message',
                        'rules' => ['required' => '', 'max_len[1000]' => ''],
                    ],
                ];
                $this->validator->set_rules($validator_rules);

                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $id_user = privileged_user_id();
                $id_request = (int) $_POST['request'];
                $id_company = (int) $_POST['company'];
                $message = cleanInput($_POST['message']);

                $this->load->model('B2b_Model', 'b2b');
                if ($this->b2b->is_my_request($id_request, $id_user)) {
                    jsonResponse('Error: You cannot respond to your request.');
                }

                $request = $this->b2b->get_b2b_request($id_request);

                if ($this->b2b->isMyPartner($request['id_company'], $id_company)) {
                    jsonResponse('Error: This company is already your partner.');
                }

                if ($this->b2b->iRequested($id_request, $request['id_company'], $id_company)) {
                    jsonResponse('Error: You have already sent a partnership request to this company.');
                }

                $insert = [
                    'message_partner' 	=> $message,
                    'id_request' 		    => $id_request,
                    'id_company' 		    => $request['id_company'],
                    'id_partner' 		    => $id_company,
                ];

                $id_partner = $this->b2b->set_b2b_partner($insert);

                $this->load->model('Company_Model', 'company');
                $partner = $this->company->get_company(['id_company' => $id_company, 'type_company' => 'all']);

                // SELLER STAFF REMAKE
                // $this->load->model("User_Model", "user");
                // if (user_type('users_staff')) {
                // 	$seller = $this->user->getSimpleUser($id_user, 'users.fname, users.lname, users.email');
                // 	$from = $seller['fname'] . " " . $seller['lname'] . ";" . $seller['email'];
                // } else {
                // 	$from = $this->session->fname . " " . $this->session->lname . ";" . $this->session->email;
                // }

                $data_systmess = [
                    'mess_code' => 'partnership_request',
                    'id_item'   => $request['id_request'],
                    'id_users'  => [$request['id_user']],
                    'replace'   => [
                        '[PARTNER_NAME]' => cleanOutput($partner['name_company']),
                        '[PARTNER_LINK]' => getCompanyURL($partner),
                        '[COMPANY_NAME]' => cleanOutput($request['name_company']),
                        '[COMPANY_LINK]' => getCompanyURL($request),
                        '[LINK]' 		      => __SITE_URL . 'b2b/my_requests',
                    ],
                    'systmess' => true,
                ];

                $this->load->model('Notify_Model', 'notify');
                $this->notify->send_notify($data_systmess);

                $data_calendar = [
                    'mess_code' => 'partnership_request',
                    'id_item'   => $request['id_request'],
                    'id_users'  => [$partner['id_user']],
                    'replace'   => [
                        '[PARTNER_NAME]' => cleanOutput($partner['name_company']),
                        '[PARTNER_LINK]' => getCompanyURL($partner),
                        '[COMPANY_NAME]' => cleanOutput($request['name_company']),
                        '[COMPANY_LINK]' => getCompanyURL($request),
                        '[LINK]' 		      => __SITE_URL . 'b2b/my_requests',
                    ],
                    'systmess' => false,
                ];
                $this->notify->send_notify($data_calendar);

                jsonResponse('The partnership request has been successfully approved.', 'success');

                break;
            case 'register':
                checkHaveCompany();
                checkPermisionAjax('manage_b2b_requests');
                is_allowed('freq_allowed_b2b_operations');

                try {
                    return $this->registerNewB2bRequest(
                        request(),
                        $this->getContainer()->get(B2bRequestProcessingService::class),
                        $this->getContainer()->get(CompanyGuardService::class),
                    );
                } catch (ValidationException $exception) {
                    jsonResponse(
                        \array_map(
                            function (ConstraintViolationInterface $violation) { return $violation->getMessage(); },
                            \iterator_to_array($exception->getValidationErrors()->getIterator())
                        )
                    );
                }

                break;
            case 'edit':
                checkHaveCompany();
                checkPermisionAjax('manage_b2b_requests');
                is_allowed('freq_allowed_b2b_operations');

                try {
                    return $this->modifyB2bRequest(
                        request(),
                        $this->getContainer()->get(B2bRequestProcessingService::class),
                        $this->getContainer()->get(CompanyGuardService::class),
                    );
                } catch (ValidationException $exception) {
                    jsonResponse(
                        \array_map(
                            function (ConstraintViolationInterface $violation) { return $violation->getMessage(); },
                            \iterator_to_array($exception->getValidationErrors()->getIterator())
                        )
                    );
                }

                break;
            case 'add_advice':
                checkHaveCompany();
                checkPermisionAjax('manage_b2b_requests');
                is_allowed('freq_allowed_b2b_operations');

                $validator_rules = [
                    [
                        'field' => 'message',
                        'label' => 'Message',
                        'rules' => ['required' => '', 'max_len[1000]' => ''],
                    ],
                    [
                        'field' => 'request',
                        'label' => 'Request information',
                        'rules' => ['required' => '', 'integer' => ''],
                    ],
                ];
                $this->validator->set_rules($validator_rules);

                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $this->load->model('B2b_Model', 'b2b');
                $id_request = intval($_POST['request']);
                $id_user = privileged_user_id();

                if (empty($id_request)) {
                    jsonResponse(translate('systmess_error_request_does_not_exist'));
                }

                $request_info = $this->b2b->get_simple_b2b_request($id_request);
                if (empty($request_info)) {
                    jsonResponse(translate('systmess_error_request_does_not_exist'));
                }

                if ($this->b2b->exist_request_advice($id_request, $id_user)) {
                    jsonResponse('Error: You have already given advice to this partner.');
                }

                $my_companies = implode(',', $this->session->companies);
                $my_partners = $this->b2b->get_partners(['companies_list' => $my_companies]);

                if (empty($my_partners)) {
                    jsonResponse('Error: Advice can be written only by partners.');
                }

                $my_partners_list = [];
                foreach ($my_partners as $my_partner) {
                    $my_partners_list[] = $my_partner['id_partner'];
                }

                if (!in_array($request_info['id_company'], $my_partners_list)) {
                    jsonResponse('Error: This company is not your partner.');
                }

                $insert = [
                    'id_request'     => $id_request,
                    'id_user'        => $id_user,
                    'message_advice' => cleanInput($_POST['message']),
                ];

                $advice = $this->b2b->set_advice($insert);

                /** @var B2b_Advice_Model $b2bAdviceRepository */
                $b2bAdviceRepository = model(B2b_Advice_Model::class);

                /** @var Users_Model $usersModel */
                $usersModel = model(Users_Model::class);
                $usersTable = $usersModel->getTable();

                $advices = $b2bAdviceRepository->findAllBy([
                    'columns' => [
                        "{$b2bAdviceRepository->getTable()}.*",
                        "CONCAT({$usersTable}.fname, ' ', {$usersTable}.lname) as username",
                        "{$usersTable}.user_photo",
                        "{$usersTable}.user_group",
                        "{$usersTable}.status",
                    ],
                    'scopes' => ['id' => (int) $advice],
                    'joins'  => ['users'],
                    'limit'  => 1,
                ]);

                $data['advices'] = [];
                if (!empty($advices) && logged_in()) {

                    $isCurrentUserLogged = logged_in();
                    $data['advices'] = array_map(
                        function ($userAdvice) use ($isCurrentUserLogged){
                            if($isCurrentUserLogged){
                                $chatBtn = new ChatButton(['recipient' => $userAdvice['id_user'], 'recipientStatus' => $userAdvice['status']]);
                                $userAdvice['btnChat'] = $chatBtn->button();
                            }

                            $userAdvice['logoLink'] = getDisplayImageLink(
                                ['{ID}' => $userAdvice['id_user'], '{FILE_NAME}' => $userAdvice['user_photo']],
                                'users.main',
                                [
                                    'thumb_size'     => 0,
                                    'no_image_group' => $userAdvice['user_group'],
                                ]
                            );
                            return $userAdvice;
                        },
                        $advices
                    );
                }

                /** @var Elasticsearch_B2b_Model $elasticsearchB2bModel */
                $elasticsearchB2bModel = model(Elasticsearch_B2b_Model::class);
                $elasticsearchB2bModel->updateB2bRequestById($id_request);

                jsonResponse('Your advice was successfully saved.', 'success', ['advice' => views()->fetch('new/b2b/detail/advices_view', $data)]);

                break;
            case 'edit_advice':
                checkHaveCompany();
                checkPermisionAjax('manage_b2b_requests');
                is_allowed('freq_allowed_b2b_operations');

                $validator_rules = [
                    [
                        'field' => 'message',
                        'label' => 'Message',
                        'rules' => ['required' => '', 'max_len[1000]' => ''],
                    ],
                    [
                        'field' => 'advice',
                        'label' => 'Advice information',
                        'rules' => ['required' => '', 'integer' => ''],
                    ],
                ];
                $this->validator->set_rules($validator_rules);

                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $this->load->model('B2b_Model', 'b2b');

                $id_user = privileged_user_id();
                $id_advice = intval($_POST['advice']);

                if (!$this->b2b->exist_advice($id_advice, $id_user)) {
                    jsonResponse('Error: You cannot edit this advice.');
                }

                if ($this->b2b->advice_moderated($id_advice, $id_user)) {
                    jsonResponse('Error: This advice has been already moderated and cannot be edited.');
                }

                $update = [
                    'message_advice' => cleanInput($_POST['message']),
                ];
                $this->b2b->update_advice($id_advice, $update);
                jsonResponse('Your advice was successfully updated.', 'success', ['id_advice' => $id_advice, 'text' => cleanInput($_POST['message'])]);

                break;
            case 'moderate_advice':
                checkPermisionAjax('moderate_content');

                $this->load->model('B2b_Model', 'b2b');

                $update = [
                    'moderated' => 1,
                ];
                $this->b2b->update_advice((int) $_POST['advice'], $update);
                jsonResponse('Advice has been successfully moderated.', 'success');

                break;
            case 'admin_change_request_status':
                checkPermisionAjax('moderate_content');

                $newRequestStatus = request()->request->get('change_to');
                if (!in_array($newRequestStatus, ['enabled', 'disabled'])) {
                    jsonResponse('Error: Invalid request status.');
                }

                /** @var B2b_Model $b2bModel */
                $b2bModel = model(B2b_Model::class);

                $b2bRequestId = request()->request->getInt('id');
                if (empty($b2bRequestId) || empty($b2bRequest = $b2bModel->get_simple_b2b_request($b2bRequestId))) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                if (!$b2bModel->update_request($b2bRequestId, ['status' => $newRequestStatus])) {
                    jsonResponse('Cannot change request status now. Please try later.');
                }

                /** @var Elasticsearch_B2b_Model $elasticsearchB2bModel */
                $elasticsearchB2bModel = model(Elasticsearch_B2b_Model::class);

                if ('enabled' == $newRequestStatus) {
                    $elasticsearchB2bModel->index($b2bRequestId);
                } else {
                    $elasticsearchB2bModel->removeB2bRequestById($b2bRequestId);
                }

                if ('prod' === config('env.APP_ENV') && !empty($b2bRequest['id_ticket'])) {
                    /** @var TinyMVC_Library_Zoho_Desk $zohoDeskLibrary */
                    $zohoDeskLibrary = library(TinyMVC_Library_Zoho_Desk::class);

                    try {
                        $zohoDeskLibrary->createTicketComment((int) $b2bRequest['id_ticket'], [
                            'contentType'   => 'plainText',
                            'isPublic'      => false,
                            'content'       => 'The B2B request was ' . ('enabled' == $newRequestStatus ? 're-published' : 'unpublished') . ' by the EP Administrator on ' . (new DateTime())->format('j M, Y H:i'),
                        ]);
                    } catch (Exception $e) {
                    }
                }

                jsonResponse('Request status was successfully changed.', 'success');

                break;
        }
    }

    public function ajax_advice_operation()
    {
        checkIsAjax();
        checkIsLogged();

        $option = $this->uri->segment(3);
        switch ($option) {
            case 'help':
                $this->load->model('Country_Model', 'country');
                $this->load->model('B2b_Model', 'b2b');

                $postData = $_POST;
                $type = cleanInput($postData['type']);
                $idUser = id_session();

                if (!in_array($type, ['y', 'n'])) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                $type = 'y' == $type ? 1 : 0;

                $idAdvice = intval($_POST['id']);

                if (empty($idAdvice)) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                $adviceInfo = $this->b2b->get_advice(['id_advice' => $idAdvice]);

                $responseData = [
                    'counter_plus'  => $adviceInfo['count_plus'],
                    'counter_minus' => $adviceInfo['count_minus'],
                ];

                if (empty($adviceInfo)) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                if (is_privileged('user', $adviceInfo['id_user'])) {
                    jsonResponse("Error: You can't vote for yourself.");
                }

                $existHelpful = $this->b2b->exist_helpful($idAdvice, $idUser);
                $action = $type ? 'plus' : 'minus';

                // If this is the first vote for this advice
                if (!$existHelpful['help'] && !$existHelpful['counter']) {
                    $insert = [
                        'id_advice'	=> $idAdvice,
                        'id_user'	  => $idUser,
                        'help'		    => $type,
                    ];

                    $columns['count_' . $action] = '+';

                    if (!$this->b2b->set_helpful($insert)) {
                        jsonResponse(translate('systmess_error_db_insert_error'));
                    }

                    if (!$this->b2b->modify_counter_helpfull($idAdvice, $columns)) {
                        jsonResponse(translate('systmess_error_db_insert_error'));
                    }

                    $responseData['counter_' . $action] = ++$responseData['counter_' . $action];
                    $responseData['select_' . $action] = true;

                    jsonResponse('Thank you for your opinion.', 'success', $responseData);
                }

                //If it is a vote cancellation
                if ($existHelpful['help'] == $type) {
                    $this->b2b->delete_advices_helpful($idAdvice);

                    $columns['count_' . $action] = '-';
                    $this->b2b->modify_counter_helpfull($idAdvice, $columns);

                    $responseData['counter_' . $action] = --$responseData['counter_' . $action];
                    $responseData['remove_' . $action] = true;

                    jsonResponse('Thank you for your opinion.', 'success', $responseData);
                }

                // If a vote has been changed
                $update['help'] = $type;
                $columns = [
                    'count_plus'  => $type ? '+' : '-',
                    'count_minus' => $type ? '-' : '+',
                ];

                if (!$this->b2b->update_helpful($idAdvice, $update, $idUser)) {
                    jsonResponse(translate('systmess_error_db_insert_error'));
                }

                $this->b2b->modify_counter_helpfull($idAdvice, $columns);

                $oppositeAction = 'plus' == $action ? 'minus' : 'plus';

                $responseData['counter_' . $action] = ++$responseData['counter_' . $action];
                $responseData['counter_' . $oppositeAction] = --$responseData['counter_' . $oppositeAction];
                $responseData['select_' . $action] = true;
                $responseData['remove_' . $oppositeAction] = true;

                jsonResponse('Thank you for your opinion.', 'success', $responseData);

                break;
        }
    }

    public function ajax_make_follower_moderated()
    {
        checkIsAjax();
        checkIsLogged();
        checkPermisionAjax('moderate_content');

        $this->load->model('B2b_Model', 'b2b');
        $main_cond = [];

        $main_cond['id_follower'] = intval($_POST['follower']);
        $this->b2b->setFollowerModerated($main_cond);

        jsonResponse('The follower has been successfully moderated.', 'success');
    }

    public function ajax_category_operation()
    {
        checkIsAjax();
        checkIsLogged();
        checkPermisionAjax('manage_b2b_requests');

        $op = $this->uri->segment(3);

        switch ($op) {
            case 'get_industries_by_item':
                $this->load->model('Company_Model', 'company');

                $item = explode('_', $_POST['company_branch']);
                $html = '';
                $id_company = (int) $item[1];
                $industries = $this->company->get_relation_industry_by_id($id_company, true);

                foreach ($industries as $industry) {
                    $html .= '<li data-value="' . $industry['category_id'] . '">
                        <span>' . $industry['name'] . '</span>
                        <i class="ep-icon ep-icon_arrows-right move-to-selected"></i>
                    </li>';
                }

                if ('' == $html) {
                    $html = '<li data-value="" disabled="disabled">No industries</li>';
                }

                exit($html);

                break;
            case 'get_industries_by_item_new':
                $this->load->model('Company_Model', 'company');

                $html = '';
                $id_company = (int) $_POST['company_branch'];
                $industries = $this->company->get_relation_industry_by_id($id_company, true);

                if (!empty($industries)) {
                    foreach ($industries as $key_industry => $industry) {
                        if ($industry['parent']) {
                            unset($industries[$key_industry]);

                            continue;
                        }
                    }
                }

                $data = [
                    'widget_id'                 => implode('-', explode('.', uniqid('', true))),
                    'industries'                => $industries,
                    'max_industries'            => (int) config('multipleselect_max_industries', 3),
                    'industries_only'           => false,
                    'industries_count'          => count($industries),
                    'industries_selected_count' => 0,
                    'industries_select_all'     => false,
                    'industries_selected'       => [],
                    'categories'                => [],
                    'categories_selected_by_id' => [],
                    'industries_required'       => true,
                    'industries_top'            => [],
                    'show_maxindustry_text'     => true,
                    'input_suffix'              => '',
                    'input_placeholder'         => '',
                    'selected_cat_json'         => json_encode([], JSON_FORCE_OBJECT),
                    'selected_categories_array' => [],
                ];

                $content = $this->view->fetch('new/multiple_epselect_view', $data);
                jsonResponse('Industry categories.', 'success', ['plug' => $content]);

                break;
            case 'get_categories_by_industry':
                $list_industries_selected = intval($_POST['industries']);
                if (!isset($list_industries_selected)) {
                    jsonResponse('Error: Select industry categories.');
                }

                $this->load->model('Category_Model', 'category');
                $this->load->model('Company_Model', 'company');

                $columns = 'category_id, name, parent, p_or_m';
                $industries = $this->category->getCategories(
                    [
                        'cat_list' => $list_industries_selected,
                        'columns'  => $columns,
                    ]
                );

                $ctype = explode('_', $_POST['ctype']);
                if ('c' == $ctype[0]) {
                    $categories = $this->company->get_company_industry_categories(['company' => my_company_id(), 'parent' => $list_industries_selected]);
                }

                if ('b' == $ctype[0]) {
                    if (!in_session('companies', $ctype[1])) {
                        jsonResponse('Error: This branch is not yours.');
                    }

                    $categories = $this->company->get_company_industry_categories(['company' => $ctype[1], 'parent' => $list_industries_selected]);
                }

                $categories_all = [];
                foreach ($categories as $categories_item) {
                    $categories_all[$categories_item['parent']][] = $categories_item;
                }

                $html = '';
                foreach ($industries as $industry) {
                    $html .= '<li class="group-b" data-value="' . $industry['category_id'] . '">
						<div class="ttl-b">'
                        . $industry['name'] .
                        ' <i class="ep-icon ep-icon_arrows-right"></i>
						</div>';
                    $html .= '<ul>';

                    if (!empty($categories)) {
                        $no_categories = 0;

                        foreach ($categories_all[$industry['category_id']] as $category) {
                            ++$no_categories;
                            $html .= '<li data-value="' . $category['category_id'] . '">
								<span>' . $category['name'] . '</span>
								<i class="ep-icon ep-icon_arrows-right"></i>
							</li>';
                        }

                        if (!$no_categories) {
                            $html .= '<li data-value="' . $industry['category_id'] . '">
								<span>' . $industry['name'] . '</span>
								<i class="ep-icon ep-icon_arrows-right"></i>
							</li>';
                        }
                    } else {
                        $html .= '<li data-value="' . $industry['category_id'] . '">
							<span>' . $industry['name'] . '</span>
							<i class="ep-icon ep-icon_arrows-right"></i>
						</li>';
                    }

                    $html .= '</ul></li>';
                }

                if ('' == $html) {
                    $html = '<li value="" disabled="disabled">No categories for this industry</li>';
                }

                jsonResponse('Industry categories.', 'success', ['categories' => $html]);

                break;
        }
    }

    public function ajax_get_saved()
    {
        checkIsAjax();
        checkIsLoggedAjax();
        checkHaveCompanyAjax();

        $data['per_page'] = 9;
        $data['page'] = abs((int) $_POST['page']);
        $data['counter'] = 0;
        $id_partners = model(B2b_Model::class)->get_partners_saved(my_company_id());
        $id_partners = array_filter(array_map('intval', explode(',', $id_partners)));
        if (!empty($id_partners)) {
            $elasticsearchCompanyModel = model(Elasticsearch_Company_Model::class);
            $elasticsearchCompanyModel->get_companies([
                'list_company_id' => implode(',', $id_partners),
                'per_p'           => $data['per_page'],
                'page'            => $data['page'],
            ]);

            $partners = $elasticsearchCompanyModel->records;

            $data['partners'] = [];
            if (!empty($partners)) {
                $data['partners'] = array_map(
                    function ($partnersItem) {
                        $chatBtn = new ChatButton(['recipient' => $partnersItem['id_user'], 'recipientStatus' => $partnersItem['status']]);
                        $partnersItem['btnChat'] = $chatBtn->button();

                        return $partnersItem;
                    },
                    $partners
                );
            }

            $data['counter'] = $elasticsearchCompanyModel->count;
        }

        $content = $this->view->fetch('new/nav_header/saved/b2b_header_list_view', $data);

        jsonResponse($content, 'success', ['counter' => $data['counter']]);
    }

    public function administration()
    {
        checkIsLogged();
        checkAdmin('manage_content');

        $this->view->assign('title', 'B2B Administration');
        $this->view->display('admin/header_view');
        $this->view->display('admin/b2b/request/index_view');
        $this->view->display('admin/footer_view');
    }

    public function ajax_requests_administration()
    {
        checkIsAjax();
        checkIsLoggedAjax();
        checkPermisionAjaxDT('moderate_content');

        /**
         * @var B2b_Model $b2bModel
         */
        $b2bModel = model(B2b_Model::class);

        /**
         * @var Country_Model $countryModel
         */
        $countryModel = model(Country_Model::class);

        $sortBy = flat_dt_ordering($_POST, [
            'dt_id'         => 'br.id_request',
            'dt_title'      => 'br.b2b_title',
            'dt_company'    => 'cb.name_company',
            'dt_created_at' => 'br.b2b_date_register',
        ]);

        $conditions = dtConditions($_POST, [
            ['as' => 'start_date_from',         'key' => 'start_date_from',     'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'start_date_to',           'key' => 'start_date_to',       'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'country',                 'key' => 'country',             'type' => 'int'],
            ['as' => 'state',                   'key' => 'state',               'type' => 'int'],
            ['as' => 'city',                    'key' => 'city',                'type' => 'int'],
            ['as' => 'status',                  'key' => 'status',              'type' => 'cleanInput'],
            ['as' => 'blocked',                 'key' => 'blocked',             'type' => 'int'],
            ['as' => 'keywords',                'key' => 'keywords',            'type' => 'cleanInput|cut_str'],
        ]);

        $params = array_merge(
            [
                'sort_by'   => empty($sortBy) ? ['br.id_request-desc'] : $sortBy,
                'per_p'     => (int) $_POST['iDisplayLength'],
                'start'     => (int) $_POST['iDisplayStart'],
            ],
            $conditions
        );

        $requests = $b2bModel->get_b2b_requests_dt($params);
        $requests_count = $b2bModel->get_b2b_requests_dt_count($params);

        $output = [
            'iTotalDisplayRecords'  => $requests_count,
            'iTotalRecords'         => $requests_count,
            'aaData'                => [],
            'sEcho'                 => (int) $_POST['sEcho'],
        ];

        if (empty($requests)) {
            jsonResponse('', 'success', $output);
        }

        foreach ($requests as $request) {
            $visible_btn = sprintf(
                <<<VISIBILTY_BTN
                    <a
                        class="ep-icon txt-blue ep-icon ep-icon_invisible confirm-dialog"
                        data-change-to="enabled"
                        data-callback="change_visible"
                        data-id="{$request['id_request']}"
                        data-message="%s"
                        href="#"
                        title="Set blog active"
                    ></a>
                VISIBILTY_BTN,
                translate('systmess_confirm_b2b_change_visibility_status', null, true)
            );

            if ('enabled' == $request['status']) {
                $visible_btn = sprintf(
                        <<<VISIBILTY_BTN
                            <a
                                class="ep-icon txt-blue ep-icon ep-icon_visible confirm-dialog"
                                data-change-to="disabled"
                                data-callback="change_visible"
                                data-id="{$request['id_request']}"
                                data-message="%s"
                                href="#"
                                title="Set blog inactive"
                            ></a>
                        VISIBILTY_BTN,
                    translate('systmess_confirm_b2b_change_visibility_status', null, true)
                );
            }

            $blocked_btn = '';
            $block_button_type = TYPE_B2B;
            if (0 == $request['blocked']) {
                $block_button_url = __SITE_URL . "moderation/popup_modals/block/{$block_button_type}/{$request['id_request']}";
                $blocked_btn = "
                    <a class=\"dropdown-item fancybox.ajax fancyboxValidateModal\"
                        data-fancybox-href=\"{$block_button_url}\"
                        data-title=\"Block B2B request\"
                        title=\"Block B2B request\">
                        <i class=\"ep-icon ep-icon_locked txt-red\"></i>
                    </a>
                ";

            // $blocked_btn = '<a class="ep-icon txt-blue ep-icon ep-icon_unlocked confirm-dialog" data-change-to="1" data-callback="change_blocked" data-id="' . $request['id_request'] . '" data-message="Are you sure you want to change status of this b2b?" href="#" title="Set blog blocked"></a>';
            } elseif (1 == $request['blocked']) {
                $unblock_button_url = __SITE_URL . "moderation/ajax_operations/unblock/{$block_button_type}/{$request['id_request']}";
                $blocked_btn = "
                    <a class=\"dropdown-item confirm-dialog\"
                        title=\"Unblock B2B request\"
                        data-url=\"{$unblock_button_url}\"
                        data-type=\"b2b\"
                        data-message=\"Do you really want to unblock this B2B request?\"
                        data-callback=\"unblockResource\"
                        data-resource=\"{$request['id_request']}\">
                        <i class=\"ep-icon ep-icon_unlocked txt-green\"></i>
                    </a>
                ";

                // $blocked_btn = '<a class="ep-icon txt-blue ep-icon ep-icon_locked confirm-dialog" data-change-to="0" data-callback="change_blocked" data-id="' . $request['id_request'] . '" data-message="Are you sure you want to change status of this b2b?" href="#" title="Set blog unblocked"></a>';
            }

            $location_details =
                $countries[$request['id_country']]['country'] . '<a class="dt_filter ep-icon ep-icon_filter txt-green" data-name="country" href="#" data-title="Country" data-value="' . $request['id_country'] . '" title="Filter by country" data-value-text="' . $countries[$request['id_country']]['country'] . '"></a>'
                . (
                    $states[$request['id_state']]['state'] ?
                    '<br/>' . $states[$request['id_state']]['state'] . '<a class="dt_filter ep-icon ep-icon_filter txt-green" data-name="state" href="#" data-title="State" data-value="' . $request['id_state'] . '" title="Filter by state" data-value-text="' . $states[$request['id_state']]['state'] . '"></a>'
                    :
                    ''
                )
                . (
                    $cities[$request['id_city']] ?
                    '<br/>' . $cities[$request['id_city']]['city'] . '<a class="dt_filter ep-icon ep-icon_filter txt-green" data-name="city" href="#" data-title="City" data-value="' . $cities[$request['id_city']]['city'] . '"></a>'
                    :
                    ''
                );

            $company_logo = getDisplayImageLink(['{ID}' => $request['id_company'], '{FILE_NAME}' => $request['logo_company']], 'companies.main', ['thumb_size' => 1]);

            $output['aaData'][] = [
                'dt_id'         => $request['id_request'] . '<br/><a rel="view_details" title="View details" class="ep-icon ep-icon_plus"></a>',
                'dt_title'      => $request['b2b_title'],
                'dt_message'    => $request['b2b_message'],
                'dt_tags'       => $request['b2b_tags'],
                'dt_views'      => $request['viewed_count'],
                'dt_zip'        => $request['b2b_zip'],
                'dt_radius'     => $request['b2b_radius'],
                'dt_locations'  => $location_details,
                'dt_created_at' => getDateFormat($request['b2b_date_register']),
                'dt_company'    => '<div class="img-prod pull-left w-100">'
                    . '<img class="w-100pr" src="' . $company_logo . '" alt="' . $request['name_company'] . '"/>'
                    . '</div>'
                    . '<div class="pull-right w-58pr">'
                    . '<div >
							<a class="ep-icon ep-icon_building" title="View company\'s profile" href="' . getCompanyURL($request) . '"></a>'
                    . '<a class="ep-icon ep-icon_visible fancybox.ajax fancybox" title="View details about this company" href="' . __SITE_URL . 'directory/popup_forms/company_details/' . $request['id_user'] . '" data-title="View details about company ' . $request['name_company'] . '"></a>'
                    . '</div>'
                    . '<div class="clearfix"><strong class="pull-left lh-16 pr-5">Title: </strong> ' . $request['name_company'] . '</div>'
                    . '<div class="clearfix"><strong class="pull-left lh-16 pr-5">Company type: </strong>' . ucfirst($request['type_company']) . '</div>'
                    . '</div>',
                'dt_views'   => $request['viewed_count'],
                'dt_actions' => $visible_btn
                    . $blocked_btn,
            ];
        }

        jsonResponse('', 'success', $output);
    }

    /**
     * Upload main image in temp.
     */
    public function ajax_b2b_upload_main_image()
    {
        $this->tempUploadImage('img.b2b_request.main.rules');
    }

    /**
     * Upload additional photo in temp.
     */
    public function ajax_b2b_upload_photo()
    {
        $this->tempUploadImage('img.b2b_request.photos.rules', false);
    }

    protected function modifyB2bRequest(
        Request $request,
        B2bRequestProcessingService $processingService,
        CompanyGuardService $companyGuardService
    ): void {
        //region access
        $userId = (int) privileged_user_id();
        $companyId = $request->request->getInt('company_branch');
        if (!$companyGuardService->checkOwnsCompany($companyId, $userId)) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }
        //endregion access
        //validation
        $this->validateB2bRequest($request);
        // Find the requesta and check ownership
        $b2bId = $request->request->getInt('request');
        if (
            empty($b2bId)
            || null === $existingB2b = $this->b2bRequestProvider->getRepository()->findOne($b2bId)
        ) {
            jsonResponse(translate('systmess_b2b_request_not_found', ['[REQUEST_ID]' => $b2bId]));
        }
        if ($existingB2b['id_user'] != $userId) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }
        //region Save
        try {
            // Save request
            $b2bRequest = $processingService->updateB2bRequest(
                $b2bId,
                $request,
                $userId
            );
        } catch (WriteException $e) {
            jsonResponse(translate('systmess_company_info_failed_to_updated_message'), 'error', withDebugInformation([], [
                'exception' => throwableToArray($e),
            ]));
        }
        //set main image
        if ($request->request->has('main')) {
            $this->setB2bRequestMainImage($b2bId, (string) $request->request->get('main'), true);
        }
        //remove images if user marked to remove any photo
        $imagesToRemove = (array) $request->request->get('images_remove');
        if (!empty($imagesToRemove)) {
            $this->removeImages($imagesToRemove, $b2bId);
        }
        //set additional photos
        $this->setB2bRequestAdditionalPhotos($b2bId, (array) $request->request->get('images'));
        // Immoderate B2B request
        /** @var Moderation_Model $moderationModel */
        $moderationModel = model(Moderation_Model::class);
        $moderationModel->immoderate($b2bId, TYPE_B2B);
        //region update/create a ticket in Zoho Desk
        if ('prod' === config('env.APP_ENV') && !empty($existingB2b['id_ticket'])) {
            /** @var TinyMVC_Library_Zoho_Desk $zohoDeskLibrary */
            $zohoDeskLibrary = $this->getContainer()->get(LibraryLocator::class)->get(TinyMVC_Library_Zoho_Desk::class);
            $session = $this->getContainer()->get(LibraryLocator::class)->get(TinyMVC_Library_Session::class);
            try {
                $zohoDeskLibrary->updateTicket((int) $existingB2b['id_ticket'], $this->prepareZohoTicketData($b2bRequest, $session));
            } catch (Exception $e) {
            }
        }
        //region index b2b request in elasticsearch
        /** @var MessengerInterface $messenger */
        $messenger = container()->get(MessengerInterface::class);
        $messenger->bus('command.bus')->dispatch(new ReIndexB2bRequest($b2bId), [new AmqpStamp('elastic.b2b_request.index')]);
        //endregion index b2b request in elasticsearch

        jsonResponse(translate('systmess_b2b_request_success_saved'), 'success');
    }

    /**
     * Register a new b2b request.
     */
    protected function registerNewB2bRequest(
        Request $request,
        B2bRequestProcessingService $processingService,
        CompanyGuardService $companyGuardService
    ): void {
        //region access
        $userId = (int) privileged_user_id();
        $companyId = $request->request->getInt('company_branch');
        if (!$companyGuardService->checkOwnsCompany($companyId, $userId)) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }
        //endregion access
        //validation
        $this->validateB2bRequest($request);
        //region Save to database and elastic
        try {
            // Save request
            $b2bRequest = $processingService->saveB2bRequest(
                $request,
                $userId
            );
            $requestId = $b2bRequest['id_request'];
        } catch (WriteException $e) {
            jsonResponse(translate('systmess_company_info_failed_to_updated_message'), 'error', withDebugInformation([], [
                'exception' => throwableToArray($e),
            ]));
        }
        //endregion Save
        //region main Image
        $this->setB2bRequestMainImage($requestId, $request->request->get('main'));
        //endregion main Image
        //region photos
        $images = (array) $request->request->get('images');
        if (!empty($images)) {
            $this->setB2bRequestAdditionalPhotos($requestId, (array) $request->request->get('images'));
        }
        //endregion photos

        //region wall add new post
        //TODO to crate a command bus for this if there is time
        /** @var TinyMVC_Library_Wall $wallLibrary */
        $wallLibrary = library(TinyMVC_Library_Wall::class);
        $wallLibrary->add([
            'type'      => 'b2b_request',
            'operation' => 'add',
            'id_item'   => $requestId,
        ]);
        //end region wall add new post

        //region Create ticket in Zoho Desk
        //TODO as service
        $session = $this->getContainer()->get(LibraryLocator::class)->get(TinyMVC_Library_Session::class);
        if ('prod' === config('env.APP_ENV') && !$session->get('fakeModel') && !$session->get('isModel')) {
            /** @var TinyMVC_Library_Zoho_Desk $zohoDeskLibrary */
            $zohoDeskLibrary = $this->getContainer()->get(LibraryLocator::class)->get(TinyMVC_Library_Zoho_Desk::class);
            //b2b model
            //create the ticket and update the id of the ticket in our database
            try {
                if (!empty($ticket = $zohoDeskLibrary->createTicket($this->prepareZohoTicketData($b2bRequest, $session)))) {
                    /** @var B2b_Requests_Model $b2bRepository */
                    $b2bRepository = $this->b2bRequestProvider->getRepository();
                    $b2bRepository->updateOne($requestId, [
                        'id_ticket' => $ticket['id'],
                    ]);
                }
            } catch (Exception $e) {
            }
        }
        //endregion Create ticket in Zoho Desk
        //region index b2b request in elasticsearch
        /** @var MessengerInterface $messenger */
        $messenger = container()->get(MessengerInterface::class);
        $messenger->bus('command.bus')->dispatch(new IndexB2bRequest($requestId), [new AmqpStamp('elastic.b2b_request.index')]);
        //endregion index b2b request in elasticsearch

        jsonResponse(translate('systmess_b2b_request_success_saved'), 'success');
    }

    private function _shipper_requests()
    {
        checkDomainForGroup();
        checkHaveShipperCompany();

        $data = [
            'port_country' => model('country')->fetch_port_country(),
            'title'        => 'My B2B requests',
        ];

        $data['templateViews'] = [
            'mainOutContent'    => 'epl/b2b/requests_view',
        ];
        $data['webpackData'] = [
            'dashboardOldPage' => true,
            // 'styleCritical' => 'epl_styles_home',
            // 'pageConnect' 	=> 'epl_index_page',
        ];

        views(['new/epl/template/index_view'], $data);
    }

    private function _seller_requests($data)
    {
        checkHaveCompany();

        $this->load->model('Company_Model', 'company');
        $this->load->model('Country_Model', 'country');

        $data['companies'] = $this->company->get_companies_main_info(['id_user' => privileged_user_id()]);

        $list_countries = [];
        foreach ($data['companies'] as $item) {
            $list_countries[$item['id_country']] = $item['id_country'];
        }
        $list_countries = array_filter($list_countries);
        if (!empty($list_countries)) {
            $data['countries'] = $this->country->get_simple_countries(implode(',', $list_countries));
        }

        $this->view->assign($data);

        $this->view->display('new/header_view');
        $this->view->display('new/b2b/request/index_view');
        $this->view->display('new/footer_view');
    }

    private function _shipper_partners()
    {
        checkDomainForGroup();
        checkHaveShipperCompany();

        $data = [
            'port_country' => $this->country->fetch_port_country(),
            'title'        => 'My B2B requests',
        ];

        $data['templateViews'] = [
            'mainOutContent'    => 'epl/b2b/partners_view',
        ];
        $data['webpackData'] = [
            'dashboardOldPage' => true,
            // 'styleCritical' => 'epl_styles_home',
            // 'pageConnect' 	=> 'epl_index_page',
        ];

        views(['new/epl/template/index_view'], $data);
    }

    private function _seller_partners()
    {
        checkHaveCompany();

        $data = [
            'port_country' => $this->country->fetch_port_country(),
            'title'        => 'My B2B requests',
        ];
        $this->view->assign($data);
        $this->view->display('new/header_view');
        $this->view->display('new/b2b/my/partners');
        $this->view->display('new/footer_view');
    }

    /**
     * Returns filtered tags from request.
     *
     * @param mixed $request
     */
    private function get_filtered_tags($request): array
    {
        return array_filter(
            array_map(
                function (string $tag) {
                    $tag = cleanInput($tag);
                    if (!library(TinyMVC_Library_validator::class)->valid_tag($tag)) {
                        jsonResponse(translate('validation_b2b_request_tags_not_valid'));

                        return null;
                    }

                    return $tag;
                },
                explode(';', $request->request->get('tags')),
            )
        );
    }

    /**
     * Returns array with zoho ticket data.
     *
     * @param array                   $b2bRequest - array with request
     * @param TinyMVC_Library_Session $session
     */
    private function prepareZohoTicketData(array $b2bRequest, $session): array
    {
        /** @var Partners_Types_Model $partnersTypesModel */
        $partnersTypesModel = model(Partners_Types_Model::class);
        //getall industries names for this request
        /** @var B2b_Request_Industry_Pivot_Model $b2bIndustriesRepository */
        $b2bIndustriesRepository = model(\B2b_Request_Industry_Pivot_Model::class);
        $industries = $b2bIndustriesRepository->findAllBy([
            'columns'       => [
                '`name`',
            ],
            'scopes' => ['id_request' => $b2bRequest['id_request']],
            'joins'  => ['industries'],
        ]);
        $countryList = $b2bRequest['type_location'] === B2bRequestLocationType::RADIUS() ? "Radius: {$b2bRequest['b2b_radius']} km" : "Globally";
        if($b2bRequest['type_location'] === B2bRequestLocationType::COUNTRY())
        {
            //get all countries names for this request
            /** @var B2b_Request_Country_Pivot_Model $countryB2bPivotRepository */
            $countryB2bPivotRepository = model(B2b_Request_Country_Pivot_Model::class);
            $countries = $countryB2bPivotRepository->findAllBy([
                'columns'       => [
                    '`country`',
                ],
                'scopes' => ['id_request' => $b2bRequest['id_request']],
                'joins'  => ['countries'],
            ]);
            $countryList = implode('; ', array_column($countries, 'country'));
        }

        return [
            'departmentId'          => (int) config('env.ZOHO_EP_DEPARTMENT_ID'),
            'classification'        => 'Request',
            'category'              => 'B2B request', // don't change it
            'prepareDescription'    => true,
            'subject'               => cut_str('DEV TEST: B2BRequest: ' . cleanInput($b2bRequest['b2b_title']), 255), //limited by zoho desk ticket
            'email'                 => $session->get('email'),
            'contact'               => [
                'lastName'  => $session->get('lname'),
                'firstName' => $session->get('fname'),
                'email'     => $session->get('email'),
            ],
            'description'           => [
                'User id'               => session()->id,
                'User name'             => user_name_session(),
                'Company id'            => $b2bRequest['id_company'],
                'Company name'          => my_company_name(),
                'Title'                 => $b2bRequest['b2b_title'],
                'Tags'                  => $b2bRequest['b2b_tags'],
                'Partner\'s type'       => $partnersTypesModel->findOne($b2bRequest['id_type'])['name'],
                'Industries'            => implode('; ', array_column($industries, 'name')),
                'Countries'             => $countryList,
                'Message'               => $b2bRequest['b2b_message'],
            ],
        ];
    }

    private function validateB2bRequest(Request $request)
    {
        $adapter = new ValidatorAdapter(\library(TinyMVC_Library_validator::class));
        $validators[] = new B2bRequestValidator($adapter);

        $request = $request->request;
        if (!empty($request->get('type_location')) && B2bRequestLocationType::COUNTRY() === B2bRequestLocationType::tryFrom($request->get('type_location'))) {
            $validators[] = new B2bRequestPartnerCountriesValidator($adapter, (array) $request->get('countries'));
        }

        if (!empty($request->get('type_location')) && B2bRequestLocationType::RADIUS() === B2bRequestLocationType::tryFrom($request->get('type_location'))) {
            $validators[] = new B2bRequestRadiusValidator($adapter);
        }

        $validator = new AggregateValidator($validators);
        if (!$validator->validate($request->all())) {
            throw new ValidationException('Failed to create edit request due to validation errors.', 0, null, $validator->getViolations());
        }
    }

    /**
     * The code for validation and upload of the image in the temp directory.
     *
     * @param string $config      - the configuration for the image ex: "img.b2b_request.main.rules"
     * @param mixed  $isMainImage
     */
    private function tempUploadImage(string $config, $isMainImage = true)
    {
        checkIsAjax();
        checkIsLogged();
        checkHaveCompany();
        checkPermisionAjax('manage_b2b_requests');

        $request = request();
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('files');

        if (null === $uploadedFile) {
            jsonResponse(translate('validation_image_required'));
        }
        if (is_array($uploadedFile) || !$uploadedFile->isValid()) {
            jsonResponse(translate('validation_invalid_file_provided'));
        }

        // We need to take our filesystem for temp directory
        /** @var FilesystemProviderInterface $filesystemProvider */
        $filesystemProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $tempDisk = $filesystemProvider->storage('temp.storage');
        /** @var LegacyImageHandler $imageHandler */
        $imageHandler = $this->getContainer()->get(LibraryLocator::class)->get(LegacyImageHandler::class);
        // Given that we need to validate the file and it would take a long time
        // to write the new proper validation we can only use what we have.
        try {
            $imageHandler->assertImageIsValid(
                $imageHandler->makeImageFromFile($uploadedFile),
                config($config)
            );
        } catch (ValidationException $e) {
            jsonResponse(
                \array_map(
                    function (ConstraintViolationInterface $violation) { return $violation->getMessage(); },
                    \iterator_to_array($e->getValidationErrors()->getIterator())
                )
            );
        } catch (ReadException $e) {
            jsonResponse(translate('validation_images_upload_fail'), 'error', withDebugInformation(
                [],
                ['exception' => throwableToArray($e)]
            ));
        }

        // But first we need to get the full path to the file
        $fileName = sprintf('%s.%s', \bin2hex(\random_bytes(16)), $uploadedFile->getClientOriginalExtension());
        $pathToFile = FilePathGenerator::uploadedFile($fileName);
        // And write file there
        try {
            $tempDisk->write($pathToFile, $uploadedFile->getContent());
        } catch (UnableToWriteFile $e) {
            jsonResponse(translate('validation_images_upload_fail'));
        }

        $responseDataName = $isMainImage ? 'image' : 'files';

        jsonResponse(null, 'success', [
            $responseDataName => ['path' => $pathToFile, 'name' => $fileName, 'fullPath' => $tempDisk->url($pathToFile)],
        ]);
    }

    /**
     * Delete photos from disk and database.
     *
     * @param int[] $images    - the images ids list
     * @param int   $requestId - the id of the request
     */
    private function removeImages(array $images, int $requestId)
    {
        /** @var B2b_Request_Photo_Model $b2bRequestPhotoRepository */
        $b2bRequestPhotoRepository = model(B2b_Request_Photo_Model::class);
        /** @var FilesystemProviderInterface $filesystemProvider */
        $filesystemProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        //get all images from model to delete
        $imagesToDelete = $b2bRequestPhotoRepository->findAllBy(['scopes' => ['ids' => array_filter($images), 'request_id' => $requestId]]);
        if (empty($imagesToDelete)) {
            return;
        }
        //get the disk and the thumbs config to use for the deletion
        $b2bRequestsDisk = $filesystemProvider->storage('public.storage');
        $imageThumbs = config('img.b2b_request.photos.thumbs');

        //for each image we try to delete it and the thumbs from the disk
        foreach ($imagesToDelete as $image) {
            //delete from the databse
            $b2bRequestPhotoRepository->deleteOne($image['id']);
            try {
                $b2bRequestsDisk->delete(B2bRequestFilePathGenerator::b2bRequestPhoto($requestId, $image['photo']));
                foreach ($imageThumbs as $imageThumb) {
                    $thumbName = str_replace('{THUMB_NAME}', $image['photo'], $imageThumb['name']);
                    $b2bRequestsDisk->delete(B2bRequestFilePathGenerator::b2bRequestPhoto($requestId, $thumbName));
                }
            } catch (UnableToDeleteFile $e) {
                jsonResponse(translate('systmess_internal_server_error'));
            }
        }
    }

    /**
     * Update the main image for the b2b request (upload for adding and delete and insert for the edit).
     *
     * @param int  $requestId - the id of the request
     * @param bool $update    - if true then deletes the old main image first
     */
    private function setB2bRequestMainImage(int $requestId, string $image, bool $update = false)
    {
        /** @var FilesystemProviderInterface $filesystemProvider */
        $filesystemProvider = $this->getContainer()->get(FilesystemProviderInterface::class);

        /** @var B2b_Request_Photo_Model $b2bRequestPhotoRepository */
        $b2bRequestPhotoRepository = model(B2b_Request_Photo_Model::class);
        $b2bRequestsDisk = $filesystemProvider->storage('public.storage');
        //if update is set to true,
        //then we try to delete the existing image first
        if ($update) {
            if (!empty($existingImage = $b2bRequestPhotoRepository->findOneBy(['scopes' => ['request_id' => $requestId, 'is_main' => true]]))) {
                $imageThumbs = config('img.b2b_request.main.thumbs');
                $b2bRequestPhotoRepository->deleteOne($existingImage['id']);
                try {
                    $b2bRequestsDisk->delete(B2bRequestFilePathGenerator::b2bRequestMainImage($requestId, $existingImage['photo']));
                    //also delete all the thumbs of the image
                    foreach ($imageThumbs as $imageThumb) {
                        $thumbName = str_replace('{THUMB_NAME}', $existingImage['photo'], $imageThumb['name']);
                        $b2bRequestsDisk->delete(B2bRequestFilePathGenerator::b2bRequestMainImage($requestId, $thumbName));
                    }
                } catch (UnableToDeleteFile $e) {
                    //silent fail
                }
            }
        }

        /** @var TinyMVC_Library_Image_intervention $interventionImageLibrary */
        $interventionImageLibrary = library(TinyMVC_Library_Image_intervention::class);

        //Get the temp disk and load the intervation library
        $b2bRequestsDisk = $filesystemProvider->storage('public.storage');
        //get the full path to the main image using the path prefixer to get it
        $prefixerTemp = $filesystemProvider->prefixer('temp.storage');
        $prefixerPublic = $filesystemProvider->prefixer('public.storage');
        $pathToMainImage = $prefixerTemp->prefixPath($image);
        //create the directory for the b2b request
        $b2bRequestsDisk->createDirectory(B2bRequestFilePathGenerator::b2bRequestMainImageFolder($requestId));
        //get the config for the b2b request images
        $imagesMainConfig = config('img.b2b_request.main');
        //process the image (create the thumbs)
        $imageProcessingResult = $interventionImageLibrary->image_processing(
            [
                'tmp_name' => $pathToMainImage,
                'name'     => \basename($pathToMainImage),
            ],
            [
                'destination'   => $prefixerPublic->prefixPath(B2bRequestFilePathGenerator::b2bRequestMainImageFolder($requestId)),
                'handlers'      => [
                    'create_thumbs' => $imagesMainConfig['thumbs'] ?? [],
                    'watermark'     => $imagesMainConfig['watermark'] ?? [],
                ],
            ]
        );

        if (!empty($imageProcessingResult['errors'])) {
            jsonResponse($imageProcessingResult['errors']);
        }
        //save the image in the database with the new name
        $b2bRequestPhotoRepository->insertOne([
            'request_id' => $requestId,
            'photo'      => $imageProcessingResult[0]['new_name'],
            'is_main'    => 1,
        ]);
    }

    private function setB2bRequestAdditionalPhotos(int $requestId, array $images)
    {
        /** @var FilesystemProviderInterface $filesystemProvider */
        $filesystemProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        /** @var TinyMVC_Library_Image_intervention $interventionImageLibrary */
        $interventionImageLibrary = library(TinyMVC_Library_Image_intervention::class);
        $imagesPhotosConfig = config('img.b2b_request.photos');
        //get the full path to the image
        //using the path prefixer to get it
        $prefixerTemp = $filesystemProvider->prefixer('temp.storage');
        $prefixerPublic = $filesystemProvider->prefixer('public.storage');
        //create the directory for the images
        $filesystemProvider->storage('public.storage')->createDirectory(B2bRequestFilePathGenerator::b2bRequestPhotosFolder($requestId));
        //process each image
        $uploadedImages = [];
        foreach ($images as $photo) {
            $photoPath = $prefixerTemp->prefixPath($photo);
            $imageProcessingResult = $interventionImageLibrary->image_processing(
                [
                    'tmp_name' => $photoPath,
                    'name'     => \basename($photoPath),
                ],
                [
                    'destination'   => $prefixerPublic->prefixPath(B2bRequestFilePathGenerator::b2bRequestPhotosFolder($requestId)),
                    'handlers'      => [
                        'create_thumbs' => $imagesPhotosConfig['thumbs'] ?? [],
                        'watermark'     => $imagesPhotosConfig['watermark'] ?? [],
                        'resize'        => $imagesPhotosConfig['resize'] ?? [],
                    ],
                ]
            );
            //check if there are no errors
            if (!empty($imageProcessingResult['errors'])) {
                jsonResponse($imageProcessingResult['errors']);
            }
            //else save thew new image to insert into the database later
            $uploadedImages[] = $imageProcessingResult[0]['new_name'];
        }
        /** @var B2b_Request_Photo_Model $b2bRequestPhotoRepository */
        $b2bRequestPhotoRepository = model(B2b_Request_Photo_Model::class);
        //save the images in the database with the new name
        if (!empty($uploadedImages)) {
            $b2bRequestPhotoRepository->insertMany(\array_map(
                fn (string $name) => ['request_id' => $requestId, 'photo' => $name],
                $uploadedImages
            ));
        }
    }
}
