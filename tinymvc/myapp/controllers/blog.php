<?php

use App\Common\Contracts\Blogs\BlogPostImageThumb;
use App\Common\Contracts\CommentType;
use App\DataProvider\IndexedProductDataProvider;
use App\Filesystem\BlogsPathGenerator;
use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;
use GuzzleHttp\Psr7\Query;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 */
class Blog_Controller extends TinyMVC_Controller
{
    private $breadcrumbs = [];
    private $preview_blog = false;

    private IndexedProductDataProvider $indexedProductDataProvider;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->indexedProductDataProvider = $container->get(IndexedProductDataProvider::class);
    }

    public function index()
    {
        return new RedirectResponse('/', 301);
    }

    public function all()
    {
        if (__CURRENT_SUB_DOMAIN !== config('env.BLOG_SUBDOMAIN')) {
            show_404();
        }

        /** @var Blogs_Categories_Model $blogsCategoriesModel */
        $blogsCategoriesModel = model(Blogs_Categories_Model::class);

        /** @var Blogs_Categories_I18n_Model $blogsCategoriesI18nModel */
        $blogsCategoriesI18nModel = model(Blogs_Categories_I18n_Model::class);

        /** @var Blog_Model $mainBlogModel */
        $mainBlogModel = model(Blog_Model::class);
        $currentCategoryPage = false;

        $blog_uri_components = tmvc::instance()->site_urls['blog/all']['replace_uri_components'];

        $links_map = [
            $blog_uri_components['country'] => [
                'type' => 'uri',
                'deny' => [$blog_uri_components['page']],
            ],
            $blog_uri_components['category'] => [
                'type' => 'uri',
                'deny' => [$blog_uri_components['page'], $blog_uri_components['archived']],
            ],
            $blog_uri_components['author'] => [
                'type' => 'uri',
                'deny' => [$blog_uri_components['page']],
            ],
            $blog_uri_components['tags'] => [
                'type' => 'uri',
                'deny' => [$blog_uri_components['page']],
            ],
            $blog_uri_components['archived'] => [
                'type' => 'uri',
                'deny' => [$blog_uri_components['page'], $blog_uri_components['category']],
            ],
            $blog_uri_components['page'] => [
                'type' => 'uri',
                'deny' => [$blog_uri_components['page']],
            ],
            'per_p' => [
                'type' => 'get',
                'deny' => [$blog_uri_components['page']],
            ],
            'sort' => [
                'type' => 'get',
                'deny' => [$blog_uri_components['page']],
            ],
            'keywords' => [
                'type' => 'get',
                'deny' => [$blog_uri_components['page']],
            ],
        ];

        $search_params_links_map = [
            $blog_uri_components['country'] => [
                'type' => 'uri',
                'deny' => [$blog_uri_components['country'], $blog_uri_components['page']],
            ],
            $blog_uri_components['category'] => [
                'type' => 'uri',
                'deny' => [$blog_uri_components['category'], $blog_uri_components['page']],
            ],
            $blog_uri_components['author'] => [
                'type' => 'uri',
                'deny' => [$blog_uri_components['author'], $blog_uri_components['page']],
            ],
            $blog_uri_components['tags'] => [
                'type' => 'uri',
                'deny' => [$blog_uri_components['tags'], $blog_uri_components['page']],
            ],
            $blog_uri_components['archived'] => [
                'type' => 'uri',
                'deny' => [$blog_uri_components['archived'], $blog_uri_components['page']],
            ],
            $blog_uri_components['page'] => [
                'type' => 'uri',
                'deny' => [$blog_uri_components['page'], 'per_p', 'sort', 'keywords'],
            ],
            'keywords' => [
                'type' => 'get',
                'deny' => ['keywords', $blog_uri_components['page']],
            ],
        ];

        $uri = uri()->uri_to_assoc(4);

        if (!empty($uri) && !in_array(uri()->segment(3), $blog_uri_components)) {
            $uri_keys = array_keys($uri);
            $uri_values = array_filter(array_values($uri));
            $categorySlug = $sp_link = array_shift($uri_keys);

            // if is an access by category default url
            if (!empty($blogCategoryId = id_from_link($categorySlug))) {
                $blogCategory = $blogsCategoriesModel->findOne($blogCategoryId);
            } else {
                // if is an access by category special link
                $blogCategory = $blogsCategoriesModel->findOneBy([
                    'scopes' => [
                        'specialLink' => $categorySlug,
                    ],
                ]);
            }

            if (empty($blogCategory)) {
                show_404();
            }

            // check if need redirect to actual category slug
            if (!empty($blogCategory['special_link']) && $categorySlug !== $blogCategory['special_link']) {
                // eliminate category slug segment from uri
                $tempUri = uri()->uri_to_assoc(5);
                // push new category slug to uri
                array_unshift($tempUri, $blogCategory['special_link']);

                $redirectUri = trim(uri()->assoc_to_uri($tempUri), '/');
                $getParams = Query::build(request()->query->all());

                // make redirect from default category url to special category url
                return  new RedirectResponse(__BLOG_URL . rtrim("{$redirectUri}?{$getParams}", '?'), 301);
            }

            if (!empty($blogCategoryId)) {
                if ('en' === __SITE_LANG) {
                    if ($categorySlug !== "{$blogCategory['url']}-{$blogCategory['id_category']}") {
                        // eliminate category slug segment from uri
                        $tempUri = uri()->uri_to_assoc(5);
                        // push new category slug to uri
                        array_unshift($tempUri, "{$blogCategory['url']}-{$blogCategory['id_category']}");

                        $redirectUri = trim(uri()->assoc_to_uri($tempUri), '/');
                        $getParams = Query::build(request()->query->all());

                        // make redirect to en category blog
                        return new RedirectResponse(__BLOG_URL . rtrim("{$redirectUri}?{$getParams}", '?'), 301);
                    }
                } else {
                    $blogCategoryI18n = $blogsCategoriesI18nModel->findOneBy([
                        'scopes' => [
                            'categoryId' => $blogCategoryId,
                            'language'   => __SITE_LANG,
                        ],
                    ]);

                    if (empty($blogCategoryI18n)) {
                        // if this category is not translated in current language, url must be equals with en blog category slug
                        if ($categorySlug !== "{$blogCategory['url']}-{$blogCategory['id_category']}") {
                            // eliminate category slug segment from uri
                            $tempUri = uri()->uri_to_assoc(5);
                            // push new category slug to uri
                            array_unshift($tempUri, "{$blogCategory['url']}-{$blogCategory['id_category']}");

                            $redirectUri = trim(uri()->assoc_to_uri($tempUri), '/');
                            $getParams = Query::build(request()->query->all());

                            // make redirect to en category blog
                            return new RedirectResponse(__BLOG_URL . rtrim("{$redirectUri}?{$getParams}", '?'), 301);
                        }
                    } elseif ($categorySlug !== "{$blogCategoryI18n['url']}-{$blogCategory['id_category']}") {
                        // eliminate category slug segment from uri
                        $tempUri = uri()->uri_to_assoc(5);
                        // push new category slug to uri
                        array_unshift($tempUri, "{$blogCategoryI18n['url']}-{$blogCategoryI18n['id_category']}");

                        $redirectUri = trim(uri()->assoc_to_uri($tempUri), '/');
                        $getParams = Query::build(request()->query->all());

                        // make redirect to i18n category blog
                        return new RedirectResponse(__BLOG_URL . rtrim("{$redirectUri}?{$getParams}", '?'), 301);
                    }
                }
            }

            $uri = array_combine($uri_values, $uri_keys);

            unset($links_map[$blog_uri_components['category']]);
            $data['links_tpl'] = uri()->make_templates($links_map, $uri);
            $data['links_tpl'] = array_map(function ($link_template) use ($sp_link) {
                return !empty($link_template) ? $sp_link . '/' . $link_template : $sp_link;
            }, $data['links_tpl']);

            $data['links_tpl'][$blog_uri_components['category']] = "{$blog_uri_components['category']}/{$this->uri->replace_template}";

            $search_params_links_tpl = uri()->make_templates($search_params_links_map, $uri, true);
            $search_params_links_tpl = array_map(function ($link_template) use ($sp_link) {
                return !empty($link_template) ? $link_template : $sp_link;
            }, $search_params_links_tpl);

            if ($search_params_links_tpl[$blog_uri_components['category']] === $sp_link) {
                $search_params_links_tpl[$blog_uri_components['category']] = '';
            }
        } else {
            if (!empty($uri[$blog_uri_components['category']])) {
                if (
                    empty($blogCategoryId = id_from_link($uri[$blog_uri_components['category']]))
                    || empty($blogCategory = $blogsCategoriesModel->findOne($blogCategoryId))
                ) {
                    show_404();
                }

                // remove from uri category segment
                unset($uri[$blog_uri_components['category']]);

                if (!empty($blogCategory['special_link'])) {
                    // add to uri category special url
                    array_unshift($uri, $blogCategory['special_link']);
                } elseif ('en' === __SITE_LANG) {
                    // add to uri en category slug
                    array_unshift($uri, "{$blogCategory['url']}-{$blogCategory['id_category']}");
                } else {
                    $blogCategoryI18n = $categoryI18n = $blogsCategoriesI18nModel->findOneBy([
                        'scopes' => [
                            'categoryId' => $blogCategoryId,
                            'language'   => __SITE_LANG,
                        ],
                    ]);

                    if (empty($blogCategoryI18n)) {
                        // add to uri en category slug
                        array_unshift($uri, "{$blogCategory['url']}-{$blogCategory['id_category']}");
                    } else {
                        // add to uri i18n category slug
                        array_unshift($uri, "{$blogCategoryI18n['url']}-{$blogCategoryI18n['id_category']}");
                    }
                }

                $redirectUri = trim(uri()->assoc_to_uri($uri), '/');
                $getParams = Query::build(request()->query->all());

                return new RedirectResponse(__BLOG_URL . rtrim("{$redirectUri}?{$getParams}", '?'), 301);
            }

            $data['links_tpl'] = uri()->make_templates($links_map, $uri);
            $search_params_links_tpl = uri()->make_templates($search_params_links_map, $uri, true);
        }

        $valid_uri_segments = [
            $blog_uri_components['country'],
            $blog_uri_components['author'],
            $blog_uri_components['tags'],
            $blog_uri_components['archived'],
            $blog_uri_components['page'],
        ];

        checkURI($uri, $valid_uri_segments);

        $data['metaData'] = [
            'description'   => "From international trade advice to general news and trending events, Export Portal's Blog Page covers all. Stay in the know with us!",
            'title'         => 'Discover the Latest International Trade Updates on Export Portal',
            'keywords'      => 'import export news, export import information, export import blog',
            'image'         => 'blog.jpg',
        ];

        $this->breadcrumbs[] = [
            'link'  => __BLOG_URL,
            'title' => translate('breadcrumb_blog'),
        ];

        $params = [
            'status'    => 'moderated',
            'visible'   => 1,
            'published' => 1,
            'per_p'     => 10,
        ];

        $data['per_p'] = 10;
        $data['page'] = 1;
        $data['search_bar_active'] = 'blog';

        if (isset($uri[$blog_uri_components['country']])) {
            $params['country'] = id_from_link(cleanInput($uri[$blog_uri_components['country']]));
        }

        if (isset($uri[$blog_uri_components['author']])) {
            if ('export-portal' === $uri[$blog_uri_components['author']]) {
                $params['author_type'] = 'admin';
            } else {
                $params['user'] = id_from_link(cleanInput($uri[$blog_uri_components['author']]));
            }
        }

        if (isset($uri[$blog_uri_components['tags']])) {
            // $params['tags'] = strtolower(str_replace('_', ' ', $uri[$blog_uri_components['tags']]));
            $params['tags'] = cleanInput($uri[$blog_uri_components['tags']]);
        }

        if (isset($uri[$blog_uri_components['archived']]) && validateDate("01-{$uri[$blog_uri_components['archived']]}", 'd-m-Y')) {
            $params['archived'] = $uri[$blog_uri_components['archived']];
        }

        if (isset($uri[$blog_uri_components['page']])) {
            $data['page'] = $params['page'] = $uri[$blog_uri_components['page']];
        }

        if (isset($_GET['per_p']) && abs(intval($_GET['per_p']))) {
            $data['per_p'] = $params['per_p'] = abs(intval($_GET['per_p']));
        }

        $exceptkeys = [];
        $get_parameters = [];
        if (!empty($_SERVER['QUERY_STRING'])) {
            $data['get_params'] = cleanOutput(cleanInput(arrayToGET($_GET, implode(',', $exceptkeys))));
            $get_parameters = array_diff_key($_GET, array_flip($exceptkeys));
            foreach ($get_parameters as $key => $one_param) {
                $get_parameters[$key] = cleanOutput(cleanInput($one_param));
            }
        }

        // PREPARE GET PARAMS FOR PER_PAGE_LINK
        if (!empty($_GET['per_p'])) {
            $get_per_page = $get_parameters;
            unset($get_per_page['per_p']);
            $data['get_per_p'] = arrayToGET($get_per_page);
        } else {
            $data['get_per_p'] = arrayToGET($get_parameters);
        }

        if (!empty($_GET['keywords'])) {
            $params['keywords'] = cut_str(decodeUrlString($_GET['keywords']));
            $keywords = cleanOutput(cut_str(decodeUrlString($_GET['keywords'])));
            model('Search_Log_Model')->log($keywords);
            $data['search_params'][] = [
                'link'  => get_dynamic_url($search_params_links_tpl['keywords'], __BLOG_URL),
                'title' => $keywords,
                'param' => translate('search_params_blog_keywords'),
            ];
        }

        $tlanguages = app()->translations->get_languages(['lang_active' => 1]);
        if (!empty($blogCategory)) {
            $currentCategoryPage = true;
            $params['category'] = $blogCategory['id_category'];

            $category_blog_en = $mainBlogModel->get_blog_category($params['category']);
            if (empty($category_blog_en)) {
                show_404();
            }

            $categories_blog_i18n = arrayByKey($mainBlogModel->get_blog_categories_i18n(['id_category' => $params['category'], 'use_lang' => false]), 'lang_category');
            foreach ($tlanguages as $tlanguage) {
                if (array_key_exists($tlanguage['lang_iso2'], $categories_blog_i18n)) {
                    tmvc::instance()->routes_priority['category'][$tlanguage['lang_iso2']] = $categories_blog_i18n[$tlanguage['lang_iso2']]['url'] . '-' . $params['category'];
                } else {
                    tmvc::instance()->routes_priority['category'][$tlanguage['lang_iso2']] = $category_blog_en['url'] . '-' . $params['category'];
                }
            }

            if (__SITE_LANG == 'en') {
                $category_blog = $category_blog_en;
            } else {
                $category_blog = $mainBlogModel->get_blog_category_i18n(['id_category' => $params['category'], 'lang_category' => __SITE_LANG]);

                if (empty($category_blog)) {
                    $category_blog = $category_blog_en;
                }
            }

            $data['metaData'] = [
                'title'         => $category_blog['meta_title'] ?: ($category_blog_en['meta_title'] ?: $data['metaData']['title']),
                'description'   => $category_blog['meta_description'] ?: ($category_blog_en['meta_description'] ?: $data['metaData']['description']),
                'keywords'      => $category_blog['meta_keywords'] ?: ($category_blog_en['meta_keywords'] ?: $data['metaData']['keywords']),
                'image'         => 'blog.jpg',
            ];

            $data['categoryName'] = $category = $category_blog['name'];
            $data['search_params'][] = [
                'link'  => get_dynamic_url($search_params_links_tpl[$blog_uri_components['category']], __BLOG_URL),
                'title' => $category,
                'param' => translate('search_params_blog_category'),
            ];

            $this->breadcrumbs[] = [
                'link'  => get_dynamic_url("{$blog_uri_components['category']}/{$uri[$blog_uri_components['category']]}", __BLOG_URL),
                'title' => $category,
            ];

            $data['pageHeader'] = $category_blog['h1'] ?: $category_blog_en['h1'];
            $data['pageSubtitle'] = $category_blog['subtitle'] ?: $category_blog_en['subtitle'];
        }

        if (isset($uri[$blog_uri_components['author']])) {
            if ('export-portal' !== $uri[$blog_uri_components['author']] && empty($user_info = $this->user->getSimpleUser($params['user']))) {
                show_404();
            }

            $user_name = empty($user_info) ? 'Export Portal' : $user_info['fname'] . ' ' . $user_info['lname'];

            $data['search_params'][] = [
                'link'  => get_dynamic_url($search_params_links_tpl[$blog_uri_components['author']], __BLOG_URL),
                'title' => $user_name,
                'param' => translate('search_params_blog_author'),
            ];

            $this->breadcrumbs[] = [
                'link'  => get_dynamic_url("{$blog_uri_components['author']}/{$uri[$blog_uri_components['author']]}", __BLOG_URL),
                'title' => $user_name,
            ];
        }

        if (isset($uri[$blog_uri_components['tags']])) {
            $data['search_params'][] = [
                'link'  => get_dynamic_url($search_params_links_tpl[$blog_uri_components['tags']], __BLOG_URL),
                'title' => str_replace('_', ' ', $params['tags']),
                'param' => translate('search_params_blog_tags'),
            ];
        }

        if (isset($uri[$blog_uri_components['archived']])) {
            if (validateDate("01-{$uri[$blog_uri_components['archived']]}", 'd-m-Y')) {
                list($archived_month, $archived_year) = explode('-', $uri[$blog_uri_components['archived']]);
                $title_arch = translate("calendar_m_{$archived_month}") . ' ' . $archived_year;
                $data['search_params'][] = [
                    'link'  => get_dynamic_url($search_params_links_tpl[$blog_uri_components['archived']], __BLOG_URL),
                    'title' => $title_arch,
                    'param' => translate('search_params_blog_archived'),
                ];
            } else {
                show_404();
            }

            $data['metaData'] = [
                'description'   => "From international trade advice to Import-Export News, Export Portal's Blog Page covers all, {$title_arch}. Stay in the know with us!",
                'keywords'      => "import export news, export import information, export import blog, {$title_arch} Import-Export News",
                'title'         => "Discover {$title_arch} Import-Export News Updates on Export Portal",
                'image'         => 'blog.jpg',
            ];

            $data['pageHeader'] = "Learn {$title_arch} Import-Export News Updates";
        }

        // page link
        $data['page_link'] = get_dynamic_url($search_params_links_tpl[$blog_uri_components['page']], __BLOG_URL);

        // search form link
        $data['search_form_link'] = get_dynamic_url($search_params_links_tpl[$blog_uri_components['keywords']], __BLOG_URL);

        /** @var Elasticsearch_Blogs_Model $blogModel */
        $blogModel = model(Elasticsearch_Blogs_Model::class);
        $params['aggregate_category_counters'] = true;
        $params['aggregate_archives'] = true;
        $params['aggregate_archives_size'] = 5;
        $blogModel->get_blogs($params);
        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['count'] = $blogModel->records_total;
        $data['blogs'] = $blogModel->records;

        $data['latestPosts'] = false;
        if ($data['page'] > 1) {
            $data['latestPosts'] = true;
        }

        $data['olderPosts'] = true;
        if ($data['page'] >= ceil($data['count'] / $params['per_p'])) {
            $data['olderPosts'] = false;
        }

        $blogs_categories = arrayByKey($blogModel->aggregates['category_counter'], 'id_category');
        $categoryIds = implode(', ', array_keys($data['blogsCategories']));
        $categories = model('blog')->get_blog_categories(['cat_list' => $categoryIds]);
        $categories = array_column($categories, null, 'id_category');

        if ('en' !== __SITE_LANG) {
            $categoriesI18n = array_column(
                $blogsCategoriesI18nModel->findAllBy([
                    'scopes' => [
                        'categoryIds'   => $categoryIds,
                        'language'      => __SITE_LANG,
                    ],
                ]),
                null,
                'id_category'
            );
        }

        foreach ($blogs_categories as $categoryId => $category) {
            $category = array_merge($category, $categories[$categoryId], $categoriesI18n[$categoryId] ?: []);

            $category['link'] = replace_dynamic_uri(
                $category['special_link'] ?: "{$category['url']}-{$category['id_category']}",
                str_replace(
                    "{$blog_uri_components['category']}/{$this->uri->replace_template}",
                    $this->uri->replace_template,
                    $data['links_tpl'][$blog_uri_components['category']]
                ),
                __BLOG_URL
            );

            $data['blogsCategories'][$category['id_category']] = $category;
        }

        $data['blogsArchived'] = $blogModel->aggregates['archives'];

        /** @var FilesystemProviderInterface */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $publicDisk = $storageProvider->storage('public.storage');

        // region SME Spotlight
        $data['smeSpotlight'] = array_map(
            function (array $blogPost) use ($publicDisk) {
                $blogPost['photoUrl'] = $publicDisk->url(BlogsPathGenerator::publicImageBlogsPath($blogPost['id'], $blogPost['photo']));

                return $blogPost;
            },
            $blogModel->getSmeSpotlightBlogs()
        );
        // region SME Spotlight

        $data['paginator_config'] = $paginator_config = [
            'site_url'      => __BLOG_URL,
            'prefix'		=> "{$blog_uri_components['page']}/",
            'base_url'      => $data['links_tpl'][$blog_uri_components['page']], // $data['page_link'],
            'first_url'     => get_dynamic_url($search_params_links_tpl[$blog_uri_components['page']], __BLOG_URL),
            'replace_url'   => true,
            'total_rows'    => $data['count'],
            'per_page'      => $data['per_p'],
            'cur_page'		=> $data['page'],
        ];

        if (!empty($_GET)) {
            $paginator_config['suffix'] = $data['get_params'];
        }

        $this->load->library('Pagination', 'pagination');
        $this->pagination->initialize($paginator_config);
        $data['pagination'] = $this->pagination->create_links();

        $limitItems = 12;

        $elasticsearchItems = $this->indexedProductDataProvider->getBlogsItems($limitItems, 1);

        if (count($elasticsearchItems) < $limitItems) {
            $elasticsearchItems = array_merge(
                $elasticsearchItems,
                $this->indexedProductDataProvider->getBlogsItems($limitItems - count($elasticsearchItems), 0, true)
            );
        }

        $sellers_list = [];
        foreach ($elasticsearchItems as $item) {
            $sellers_list[$item['id_seller']] = $item['id_seller'];
        }

		if (!empty($sellers_list)) {
            /** @var User_Model $userModel */
            $userModel = model(User_Model::class);
			$sellers = $userModel->getSellersForList(implode(',', $sellers_list), true);
		}

        $items_country_ids = [];

        foreach ($elasticsearchItems as $key => $item) {
            $elasticsearchItems[$key]['seller'] = search_in_array($sellers, 'idu', $item['id_seller']);
            $items_country_ids[$item['p_country']] = $item['p_country'];
        }

        $data['last_items'] = $elasticsearchItems;
        $itemsCount = count($data['last_items']);

        if ($itemsCount > 4 && count($data['last_items']) % 2 !== 0) {
            array_pop($data['last_items']);
            $itemsCount--;
        }

        /** @var Country_Model $countryModel */
        $countryModel = model(Country_Model::class);
		$data['items_country'] = $countryModel->get_simple_countries(implode(",", $items_country_ids));

		if (logged_in()) {
            /** @var Items_Model $itemsModel */
            $itemsModel = model(Items_Model::class);
			$saved_list = $itemsModel->get_items_saved(id_session());
			$data['saved_items'] = explode(',', $saved_list);
		}
		//end last items

        $data['blogs'] = array_map(function($blog) use ($publicDisk)
        {
            $blog['imagePath'] = $publicDisk->url(
                BlogsPathGenerator::publicImageBlogsPath($blog['id'], $blog['photo'])
            );
            return $blog;
        },
        $data['blogs']);

        if ($data['count']) {
            $data['latestItemsPosition'] = 2;
            $data['smeSpotlightPosition'] = 5;

            if ($data['count'] < 6) {
                $data['latestItemsPosition'] = $data['count'] - 3 < 0 ? 0 : $data['count'] - 3;
                $data['smeSpotlightPosition'] = $data['count'] - 2 < 0 ? 0 : $data['count'] - 2;
            }

            $data['blogs'] = array_map(
                function ($blog) use ($publicDisk, $data, $blog_uri_components) {

                    if (!empty($spLink = $data['blogsCategories'][$blog['id_category']]['special_link'])) {
                        $blog['categoryLink'] = replace_dynamic_uri(
                            $spLink,
                            str_replace(
                                "{$blog_uri_components['category']}/{$this->uri->replace_template}",
                                $this->uri->replace_template,
                                $data['links_tpl'][$blog_uri_components['category']]
                            ),
                            __BLOG_URL
                        );
                    } else {
                        $blog['categoryLink'] = replace_dynamic_uri(strForURL($blog['category_name'] . ' ' . $blog['id_category']), $data['links_tpl'][$blog_uri_components['category']], __BLOG_URL);
                    }

                    $blog['photoSrc'] = $publicDisk->url(BlogsPathGenerator::publicImageBlogsPath($blog['id'], $blog['photo'] ?: 'no-image.jpg'));

                    $blog['description'] = cleanOutput(!empty($blog['description']) ? $blog['description'] : $blog['short_description']);

                    return $blog;
                },
                $data['blogs']
            );
        }


        /** @var Mass_media_Model $massMediaModel */
        $massMediaModel = model(Mass_media_Model::class);
        $data['newsList'] = $massMediaModel->get_news(['limit' => ' 0,3 ', 'published' => 1, 'lang' => true, 'order_by' => 'date_news Desc']);
        $data['blog_uri_components'] = $blog_uri_components;
        $data['currentCategoryPage'] = $currentCategoryPage;

        $this->view->assign('title', 'Search blogs');
        $data['sidebarContent'] = 'new/blog/sidebar_view';
        $data['content'] = 'blog/index_view';
        $data['customEncoreLinks'] = true;
        $data['styleCritical'] = 'blog';

        views()->displayWebpackTemplate($data);
    }

    public function detail()
    {
        if (__CURRENT_SUB_DOMAIN !== config('env.BLOG_SUBDOMAIN')) {
            show_404();
        }

        $data['blog_uri_components'] = tmvc::instance()->site_urls['blog/all']['replace_uri_components'];
        $id_blog = false !== $this->preview_blog ? $this->preview_blog : id_from_link(uri()->segment(2));

        /** @var Blog_Model $mainBlogModel */
        $mainBlogModel = model(Blog_Model::class);
        $data['blog'] = $blog = $mainBlogModel->get_public_blog($id_blog, true);
        if (empty($blog)) {
            show_404();
        }

        if (false !== $this->preview_blog && !is_privileged('user', $blog['id_user'], 'manage_blogs') && !have_right_or('moderate_content,blogs_administration')) {
            show_404();
        }

        $categoryUrl = uri()->segment(1);
        if ('detail' === $categoryUrl || $categoryUrl !== $blog['category_url'] || uri()->segment(2) !== "{$blog['title_slug']}-{$blog['id']}") {
            // make redirect from default category url to special category url
            // make redirect from another category lang to category blog lang
            // make redirect from wrong blog slug to good blog slug
            return new RedirectResponse(getBlogUrl($blog), 301);
        }

        $data['blog']['title'] = cleanOutput($data['blog']['title']);
        $data['blog']['description'] = cleanOutput(!empty($blog['description']) ? $blog['description'] : $blog['short_description']);

        /** @var FilesystemProviderInterface */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $publicDisk = $storageProvider->storage('public.storage');
        $publicPrefixer = $storageProvider->prefixer('public.storage');
        $blogImagePath = BlogsPathGenerator::publicImageBlogsPath($blog['id'], $blog['photo']);
        list($width, $height) = getimagesize($publicPrefixer->prefixPath($blogImagePath));
        $data['blog']['photo'] = [
            'url'       => $publicDisk->url($blogImagePath),
            'width'     => $width,
            'height'    => $height,
        ];

        if (!is_privileged('user', $blog['id_user'], true) && !$this->preview_blog) {
            if (is_null($this->session->__get('is_viewed_' . $id_blog))) {
                $this->session->__set('is_viewed_' . $id_blog, 1);
                $mainBlogModel->increment_blog_views($id_blog);

                /** @var Elasticsearch_Blogs_Model $blogModel */
                $blogModel = model(Elasticsearch_Blogs_Model::class);
                $blogModel->increment_blog_views($id_blog);
            }
        }

        if (!empty($blog['category_special_link'])) {
            $data['categoryUrl'] = __BLOG_URL . $blog['category_special_link'];
        } else {
            /** @var Blogs_Categories_Model $blogsCategoriesModel */
            $blogsCategoriesModel = model(Blogs_Categories_Model::class);
            $blogsCategory = $blogsCategoriesModel->findOne($blog['id_category']);

            if ('en' === __SITE_LANG) {
                $data['categoryUrl'] = __BLOG_URL . "{$blogsCategory['url']}-{$blog['id_category']}";
            } else {
                /** @var Blogs_Categories_I18n_Model $blogsCategoriesI18nModel */
                $blogsCategoriesI18nModel = model(Blogs_Categories_I18n_Model::class);

                $blogsCategoryI18n = $blogsCategoriesI18nModel->findOneBy([
                    'scopes'    => [
                        'categoryId' => (int) $blog['id_category'],
                        'language'   => __SITE_LANG,
                    ],
                ]);

                $categoryUrl = $blogsCategoryI18n['url'] ?: $blogsCategory['category_url'];
                $data['categoryUrl'] = __BLOG_URL . "{$categoryUrl}-{$blog['id_category']}";
            }
        }

        $data['search_form_link'] = __BLOG_URL;
        $data['search_bar_active'] = 'blog';

        $blogs_tags = $mainBlogModel->get_blog_tags();
        $blogs_tags = explode(',', $blogs_tags['all_tags']);
        $data['blogs_tags'] = array_count_values($blogs_tags);
        arsort($data['blogs_tags']);
        array_splice($data['blogs_tags'], 21);

        $this->breadcrumbs[] = [
            'link'  => __BLOG_URL,
            'title' => translate('breadcrumb_blog'),
        ];

        $user_name = ('user' == $blog['author_type']) ? $blog['user_name'] : 'Export Portal';
        $this->breadcrumbs[] = [
            'link'  => 'user' == $blog['author_type'] ? get_dynamic_url($data['blog_uri_components']['author'] . '/' . strForURL($user_name . ' ' . $blog['id_user']), __BLOG_URL) : get_dynamic_url($data['blog_uri_components']['author'] . '/export-portal', __BLOG_URL),
            'title' => $user_name,
        ];

        $this->breadcrumbs[] = [
            'link'  => $data['categoryUrl'],
            'title' => $blog['category_name'],
        ];

        $this->breadcrumbs[] = [
            'link'  => getBlogUrl($blog),
            'title' => $blog['title'],
        ];

        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['metaParams'] = [
            '[BLOG_NAME]'        => $blog['title'],
            '[BLOG_DESCRIPTION]' => $blog['short_description'],
        ];

		if (
            $publicDisk->fileExists(
                $photoPath = BlogsPathGenerator::publicImageBlogsPath($blog['id'], $blog['photo'])
            )
        ) {
			$data['meta_params']['[image]'] = $publicDisk->url($photoPath);
		} else {
			$data['meta_params']['[image]'] = asset('public/img/og-images/600x315_blog.jpg', 'legacy');
		}
        $limitItems = 12;

		$elasticsearchItems = $this->indexedProductDataProvider->getBlogsItems($limitItems, 1, false);

        if (count($elasticsearchItems) < $limitItems) {
            $elasticsearchItems = array_merge(
                $elasticsearchItems,
                $this->indexedProductDataProvider->getBlogsItems($limitItems, 0, true)
            );
        }

        if (!empty($elasticsearchItems)) {
            $sellers_list = array_column($elasticsearchItems, 'id_seller', 'id_seller');

			if (!empty($sellers_list)) {
                /** @var User_Model $userModel */
                $userModel = model(User_Model::class);
				$sellers = $userModel->getSellersForList(implode(',', $sellers_list), true);
			}

			$items_country_ids = [];

            foreach ($elasticsearchItems as $key => $item) {
                $elasticsearchItems[$key]['seller'] = search_in_array($sellers, 'idu', $item['id_seller']);
                $items_country_ids[$item['p_country']] = $item['p_country'];
            }

            /** @var Country_Model $countryModel */
            $countryModel = model(Country_Model::class);
			$data['items_country'] = $countryModel->get_simple_countries(implode(",", $items_country_ids));
		}

        $data['last_items'] = $elasticsearchItems;
        $itemsCount = count($data['last_items']);

        if ($itemsCount > 4 && count($data['last_items']) % 2 !== 0) {
            array_pop($data['last_items']);
            $itemsCount--;
        }
        // end last items

        $params_recommended = [
            'not_id_blog' => $id_blog,
            'status'      => 'moderated',
            'visible'     => 1,
            'published'   => 1,
            'per_p'       => (int) config('blogs_recommended_list_per_page'),
            'lang'        => __SITE_LANG,
        ];

        $data['blogs_count'] = $mainBlogModel->counter_by_conditions($params_recommended);
        $data['blogs'] = $mainBlogModel->get_blogs($params_recommended);

        $data['blogs'] = array_map(function ($blog) use ($publicDisk) {
            $blog['url'] = __BLOG_URL. "detail/{$blog['id']}/" . strForURL($blog['title']);
            $blog['photoSrc'] = $publicDisk->url(BlogsPathGenerator::thumb($blog['id'], $blog['photo'], BlogPostImageThumb::BIG()));
            $blog['photoMainSrc'] = $publicDisk->url(BlogsPathGenerator::publicImageBlogsPath($blog['id'], $blog['photo']));
            $blog['title'] = cleanOutput($blog['title']);

            return $blog;
        }, $data['blogs']);

        if (logged_in()) {
            /** @var Items_Model $itemsModel */
            $itemsModel = model(Items_Model::class);
            $saved_list = $itemsModel->get_items_saved(id_session());
            $data['saved_items'] = explode(',', $saved_list);
        }

        $data['comments'] = [
            'hash_components' => blogCommentsResourceHashComponents((int) $id_blog),
            'type_id'         => CommentType::BLOGS()->value,
        ];

        $this->view->assign($data);
        $this->view->assign('title', 'Search blogs');

        $data['styleCritical'] = 'blog_detail';
        $data['content'] = 'blog/detail_view';
        $data['customEncoreLinks'] = true;

        views()->displayWebpackTemplate($data);
    }

    public function preview_blog()
    {
        $this->preview_blog = intval($this->uri->segment(2));
        $this->detail();
    }
}
