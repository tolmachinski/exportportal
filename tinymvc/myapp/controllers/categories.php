<?php

use App\DataProvider\IndexedProductDataProvider;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [15.12.2021]
 * Controller Refactoring
 */
class Categories_Controller extends TinyMVC_Controller
{
    private IndexedProductDataProvider $indexedProductDataProvider;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->indexedProductDataProvider = $container->get(IndexedProductDataProvider::class);
    }

    private function __prepare_category_breadcrumbs($categories)
    {
        foreach ($categories as $key => $category) {
            $categories[$key]['breadcrumbs'] = json_decode('[' . $category['breadcrumbs'] . ']', true);

            $last_bread_name = '';
            $last_bread = array_values(end($categories[$key]['breadcrumbs']));

            foreach ($categories[$key]['breadcrumbs'] as $bread_key => $one_bread) {
                foreach ($one_bread as $id_bread => $name_bread) {
                    $breadcrumb_link = '<a href="' . __SITE_URL . 'category/' . strForURL($name_bread) . '/' . $id_bread . '" target="_blank">' . $name_bread . '</a>';

                    if ((0 != $category['parent']) && (2 == $category['cat_type']) && ($last_bread[0] == $name_bread)) {
                        $breadcrumb_link = '<a href="' . __SITE_URL . 'category/' . strForURL($last_bread_name . ' ' . $name_bread) . '/' . $id_bread . '" target="_blank">' . $name_bread . '</a>';
                        $categories[$key]['parent_for_link'] = $last_bread_name;
                    }

                    $categories[$key]['breadcrumbs'][$bread_key] = $breadcrumb_link;
                }

                $last_bread_name = end($one_bread);
            }
        }

        return $categories;
    }

    public function index()
    {
        if (!empty($_GET['keywords'])) {
            $data['keywords'] = cut_str(decodeUrlString($_GET['keywords']));

            $params = [
                'keywords' => $data['keywords'],
                'start'    => 0,
                'limit'    => 30,
            ];

            model('Search_Log_Model')->log($data['keywords']);
            $categories_model = model('elasticsearch_category');
            $categories_model->get_categories($params);
            $data['categories'] = $categories_model->categories_records;
            $data['categories_count'] = $categories_model->categories_count;

            $data['categories'] = $this->__prepare_category_breadcrumbs($data['categories']);
        }

        $this->breadcrumbs[] = [
            'link'  => __SITE_URL . 'categories',
            'title' => 'All categories',
        ];

        $data['breadcrumbs'] = $this->breadcrumbs;

        $data['category_groups'] = model('category_groups')->get_category_groups();

        $data['mostPopularItems'] = $this->indexedProductDataProvider->getCategoriesItems(
            8, 0, 1, 'views', 'desc'
        );
        $itemsCount = count($data['mostPopularItems']);

        if ($itemsCount > 4 && $itemsCount % 2 !== 0) {
            array_pop($data['mostPopularItems']);
            $itemsCount--;
        }

        $data['latestItems'] = $this->indexedProductDataProvider->getCategoriesItems(
            8, 0, 1, 'create_date', 'desc'
        );
        $itemsCount = count($data['latestItems']);

        if ($itemsCount > 4 && $itemsCount % 2 !== 0) {
            array_pop($data['latestItems']);
            $itemsCount--;
        }

        $data['featuredItems'] = $this->indexedProductDataProvider->getCategoriesItems(
            8, 1, 1, 'featured_from_date', 'desc'
        );
        $itemsCount = count($data['featuredItems']);

        if ($itemsCount > 4 && $itemsCount % 2 !== 0) {
            array_pop($data['featuredItems']);
            $itemsCount--;
        }

        /** @var Items_Model $itemsModel */
        $itemsModel = model(Items_Model::class);
        $savedList = $itemsModel->get_items_saved(id_session());

        $data['savedItems'] = explode(',', $savedList);

        $data["webpackData"] = [
            'styleCritical' => 'categories',
            'pageConnect'   => 'categories',
        ];

        $data['templateViews'] = [
            'headerOutContent'  => 'categories/header_group_categories_view',
            'mainOutContent'    => 'categories/main_group_categories_view',
        ];

        views()->display_template($data);
    }

    public function ajax_category_group_operation()
    {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        $operation = $this->uri->segment(3);

        switch ($operation) {
            case 'main_categories_list':
                $category = (int) $_POST['id_category'];
                $data['products'] = [];

                $categories_group_id = [];
                $data['categories'] = model('category_groups')->get_categories_by_group($category);

                foreach ($data['categories'] as $key => $category_group_item) {
                    $data['categories'][$key]['url'] = __SITE_URL . 'category/' . strForURL($category_group_item['name']) . '/' . $category_group_item['category_id'];
                    $categories_group_id[] = $category_group_item['category_id'];
                }

                if ((int) config("handmade_parent_category", 4) === $category) {
                    $data['banner'] = [
                        'name'  => translate("handmade_category_name"),
                        'img'   => asset('public/build/images/categories/handmade-nav.jpg'),
                        'link'  => __SITE_URL . 'landing/handmade',
                        'label' => config('handmade_label_expired') > date('Y-m-d h:i:s')
                    ];
                }

                jsonResponse('', 'success', $data);

            break;
            case 'next_categories_list':
                $category = (int) $_POST['id_category'];

                model('elasticsearch_category')->get_categories(['parent' => [$category], 'sort_by' => 'name_asc']);

                $data['categories'] = model('elasticsearch_category')->categories_records;
                $data['categories_count'] = model('elasticsearch_category')->categories_count;

                $categories_count_product_condition = [
                    'category'                    => $category,
                    'aggregate_category_counters' => true,
                    'aggregate_id_category'       => $category,
                ];
                model('elasticsearch_items')->get_items($categories_count_product_condition);
                $data['categories_count_product'] = model('elasticsearch_items')->aggregates['categories'];

                jsonResponse('', 'success', $data);
            break;
            case 'last_categories_list':
                $category = (int) $_POST['id_category'];

                model('elasticsearch_category')->get_categories(['parent' => [$category], 'sort_by' => 'name_asc']);

                $categories = model('elasticsearch_category')->categories_records;
                $categories_id = [];
                foreach ($categories as $key => $item) {
                    $categories_id[] = $item['category_id'];
                }

                $categories_count_product_condition = [
                    'categories'                  => $categories_id,
                    'aggregate_category_counters' => true,
                    'aggregate_category_by_id'    => true,
                ];
                model('elasticsearch_items')->get_items($categories_count_product_condition);
                $categories_product_count = model('elasticsearch_items')->aggregates['categories'];

                model('elasticsearch_category')->get_categories([
                    'parent'  => $categories_id,
                    'sort_by' => 'name_asc',
                ]);

                $children = array_map(function ($child) use ($categories_product_count) {
                    $child['product_count'] = array_key_exists($child['category_id'], $categories_product_count) ? $categories_product_count[$child['category_id']] : 0;

                    return $child;
                }, model('elasticsearch_category')->categories_records);
                $children = arrayByKey($children, 'parent', true);

                $data['categories'] = array_map(function ($category) use ($children, $categories_product_count) {
                    $category['children'] = !empty($children[$category['category_id']]) ? $children[$category['category_id']] : [];
                    $category['product_count'] = array_key_exists($category['category_id'], $categories_product_count) ? $categories_product_count[$category['category_id']] : 0;

                    return $category;
                }, $categories);

                jsonResponse('', 'success', $data);

            break;
            case 'check_age':
                $validation_rules = [
                    [
                        'field' => 'day',
                        'label' => 'Day',
                        'rules' => ['required' => '', 'min[1]' => '', 'max[31]' => ''],
                    ],
                    [
                        'field' => 'month',
                        'label' => 'Month',
                        'rules' => ['required' => '', 'min[1]' => '', 'max[12]' => ''],
                    ],
                    [
                        'field' => 'year',
                        'label' => 'Year',
                        'rules' => ['required' => ''],
                    ],
                ];

                $this->validator->set_rules($validation_rules);

                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $year = (int) $_POST['year'];
                $month = str_pad((int) $_POST['month'], 2, "0", STR_PAD_LEFT);
                $day = str_pad((int) $_POST['day'], 2, "0", STR_PAD_LEFT);
                $date = "{$year}-{$month}-{$day}";

                if (!validateDate($date, 'Y-m-d')) {
                    jsonResponse(translate('systmess_error_invalid_data', 'error'));
                }

                if (validateAge($date)) {
                    if (!cookies()->exist_cookie('ep_age_verification')) {
                        cookies()->setCookieParam('ep_age_verification', true, 604800);
                    }

                    jsonResponse('', 'success');
                }

                jsonResponse('You cannot access the site, you are not old enough.', 'error', ['date' => $date]);
            break;
            case 'group_categories_list':
                $categoriesGroup = model(Category_Groups_Model::class)->get_category_groups();
                $categories = views()->fetch('new/categories/side_categories_view', ['categoriesGroup' => $categoriesGroup]);
                jsonResponse('', 'success', ['html' => $categories]);
            break;
        }
    }

    public function getcategories()
    {
        checkIsAjax();

        $request = request()->request;

        $this->load->model('Category_Model', 'category');

        $op = $_POST['op'];
        $data['cat'] = intval($_POST['cat']);
        $data['class'] = $_POST['cl'];
        $data['level'] = intval($_POST['level']) + 1;

        if (isset($_POST['type'])) {
            $data['type'] = $_POST['type'];
        }

        $data['select_name'] = $request->get('select_name') ?: null;
        $data['not_filter'] = $request->get('not_filter') ?: null;

        switch ($op) {
            case 'select':
                if (!logged_in()) {
                    jsonResponse(translate("systmess_error_should_be_logged"));
                }

                if (!have_right('manage_content')) {
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));
                }

                $data['categories'] = $this->category->getCategories(['parent' => $data['cat'],  'columns' => 'category_id, name']);
                jsonResponse('', 'success', ['content' => $this->view->fetch('admin/categories/catinselect_view', $data)]);

            break;
            case 'select_new':
                if (!logged_in()) {
                    jsonResponse(translate("systmess_error_should_be_logged"));
                }

                $params = [
                    'parent'  => $data['cat'],
                    'columns' => 'category_id, name',
                    'p_or_m'  => 1,
                ];

                $p_or_m = intval($_POST['type']);
                if (1 != $p_or_m) {
                    $params['p_or_m'] = $p_or_m;
                }

                $categories = $this->category->getCategories($params);
                jsonResponse('', 'success', ['categories' => $categories]);

            break;
            case 'find':
                checkIsLoggedAjax();
                jsonResponse(null, 'success', ['categories' => model('category')->getCategories([
                    'parent'  => $data['cat'],
                    'columns' => 'category_id, name',
                ])]);

            break;
            case 'table':
                checkIsLoggedAjax();
                checkPermisionAjax('manage_content');

                $data['categories'] = $this->category->getCategories(['parent' => $data['cat']]);
                jsonResponse('', 'success', ['content' => $this->view->fetch('admin/categories/catintable_view', $data)]);

            break;
            case 'search':
                $load_more = false;
                if (isset($_POST['start'])) {
                    $load_more = true;
                }

                if (!$load_more) {
                    $validation_rules = [
                        [
                            'field' => 'keywords',
                            'label' => 'Keywords',
                            'rules' => [
                                'min_len[3]' => '',
                                function ($attr, $value, $fail) {
                                    if (!strlen($value)) {
                                        $fail('Field "Keywords" is required.');
                                    }
                                },
                            ],
                        ],
                    ];
                    $this->validator->set_rules($validation_rules);

                    if (!$this->validator->validate()) {
                        jsonResponse($this->validator->get_array_errors());
                    }
                }

                $content_type = cleanInput($_POST['content_type']);

                $params = [
                    'keywords' => cleanInput(cut_str($_POST['keywords'])),
                    'start'    => 0,
                    'limit'    => 30,
                ];

                if ($load_more) {
                    $params['start'] = (int) $_POST['start'];
                }

                $categories_model = model('elasticsearch_category');
                $categories_model->get_categories($params);
                $data['categories'] = $categories_model->categories_records;
                $data['categories_count'] = $categories_model->categories_count;

                if ('popup' == $content_type) {
                    if ($load_more) {
                        $name_view = 'new/item/add_item/partials/dropdown_search_items_view';
                    } else {
                        $name_view = 'new/item/add_item/partials/dropdown_search_categories_view';
                    }
                } else {
                    $data['categories'] = $this->__prepare_category_breadcrumbs($data['categories']);

                    if ($load_more) {
                        $name_view = 'new/categories/search_items';
                    } else {
                        $name_view = 'new/categories/search_categories_view';
                    }
                }

                jsonResponse('', 'success', ['content' => $this->view->fetch($name_view, $data), 'categories_count' => $data['categories_count']]);

            break;
        }
    }

    public function ajax_categories_dt()
    {
        checkIsAjax();
        checkIsLoggedAjaxDT();
        checkPermisionAjaxDT('manage_content');

        /**
         * @var Category_Model $categoryModel
         */
        $categoryModel = model(Category_Model::class);

        $sortBy = flat_dt_ordering($_POST, [
            'dt_p_or_m'  => 'p_or_m',
            'dt_name'    => 'name',
            'dt_id'      => 'category_id',
        ]);

        $params = [
            'sort_by'   => empty($sortBy) ? ['name-asc'] : $sortBy,
            'per_p'     => (int) $_POST['iDisplayLength'],
            'start'     => (int) $_POST['iDisplayStart'],
        ];

        if (!empty($_POST['keywords'])) {
            $params['search'] = cleanInput(cut_str($_POST['keywords']));
        }

        if (isset($_POST['parent']) && -1 != $_POST['parent']) {
            $parent = (int) $_POST['parent'];

            if (empty($parent)) {
                $params['parent'] = 0;
            } else {
                $params['category'] = $parent;
            }
        }

        $categories = $categoryModel->getCategories($params);
        $records_total = $categoryModel->getCategoriesCounter($params);

        $output = [
            'iTotalDisplayRecords'  => $records_total,
            'iTotalRecords'         => $records_total,
            'aaData'                => [],
            'sEcho'                 => intval($_POST['sEcho']),
        ];

        if (empty($categories)) {
            jsonResponse('', 'success', $output);
        }

        foreach ($categories as $key => $category) {
            $meta = '';
            $type = '';
            $p_or_m = (1 == $category['p_or_m']) ? 'Product' : 'Motor';
            $p_or_m .= (1 == $category['vin']) ? '(VIN)' : '';

            if (!empty($category['title'])) {
                $meta .= '<span">Title |</span>';
            }

            if (!empty($category['h1'])) {
                $meta .= '<span">H1 |</span>';
            }

            if (!empty($category['description'])) {
                $meta .= '<span">Description |</span>';
            }

            if (!empty($category['keywords'])) {
                $meta .= '<span>Keywords </span>';
            }

            switch ($category['cat_type']) {
                case 1: $type = 'Make';

                    break;
                case 2: $type = 'Model';

                    break;
                case 3:
                default: $type = 'Simple';

                    break;
            }

            $actions = '<a class="ep-icon ep-icon_pencil txt-blue fancyboxValidateModalDT fancybox.ajax" href="/categories/popup_forms/edit_category/' . $category['category_id'] . '" title="Edit Category" data-title="Edit Category"></a>';

            $category['breadcrumbs'] = json_decode('[' . $category['breadcrumbs'] . ']', true);
            $cat_str = '---';
            if (count($category['breadcrumbs']) > 1) {
                $cat_str = '<div class="breadcrumbs-b mt-5">';

                $out = [];
                foreach ($category['breadcrumbs'] as $bread) {
                    foreach ($bread as $cat_id => $cat_title) {
                        $out[] = '<a href="' . __SITE_URL . 'category/' . strForURL($cat_title) . '/' . $cat_id . '">' . $cat_title . '</a>';
                    }
                }

                $cat_str .= implode('<span class="crumbs-delimiter fs-16 pr-5 pl-5">&raquo;</span>', $out);
            }

            $cat_str .= '</div>';

            $langs = [];
            $langs_category = array_filter(json_decode($category['translations_data'], true));
            $langs_category_list = ['English'];
            if (!empty($langs_category)) {
                foreach ($langs_category as $lang_key => $lang_category) {
                    if ('en' == $lang_key) {
                        continue;
                    }

                    $langs[] = '<li>
                                    <div class="flex-display">
                                        <span class="display-ib_i lh-30 pl-5 pr-10 txt-nowrap-simple flex--1">' . $lang_category['lang_name'] . '</span>
                                        <a class="display-ib_i lh-30 mb-0 p-0 ep-icon ep-icon_remove txt-red pull-right confirm-dialog" data-callback="remove_category_i18n" data-category="' . $category['category_id'] . '" data-lang="' . $lang_category['abbr_iso2'] . '" title="Delete" data-message="Are you sure you want to delete category translation?" href="#" ></a>
                                        <a href="' . __SITE_URL . 'categories/popup_forms/edit_category_i18n/' . $category['category_id'] . '/' . $lang_category['abbr_iso2'] . '" data-title="Edit category translation" title="Edit" class="display-ib_i lh-30 mb-0 p-0 ep-icon ep-icon_pencil fancyboxValidateModalDT fancybox.ajax pull-right"></a>
                                    </div>
                                </li>';
                    $langs_category_list[] = $lang_category['lang_name'];
                }
                $langs[] = '<li role="separator" class="divider"></li>';
            }

            $langs_dropdown = '<div class="dropdown">
                                <a class="ep-icon ep-icon_globe-circle m-0 fs-24 dropdown-toggle" data-toggle="dropdown"></a>
                                <ul class="dropdown-menu">
                                    ' . implode('', $langs) . '
                                    <li><a href="' . __SITE_URL . 'categories/popup_forms/add_category_i18n/' . $category['category_id'] . '" data-title="Add category translation" title="Add translation" class="fancyboxValidateModalDT fancybox.ajax">Add translation</a></li>
                                </ul>
                            </div>';

            $output['aaData'][] = [
                'dt_id'          => $category['category_id'],
                'dt_name'        => $category['name'],
                'dt_meta'        => $meta,
                'dt_type'        => $type,
                'dt_p_or_m'      => $p_or_m,
                'dt_actions'     => $actions,
                'dt_tlangs'      => $langs_dropdown,
                'dt_tlangs_list' => implode(', ', $langs_category_list),
                'dt_breadcrumbs' => $cat_str,
            ];
        }

        jsonResponse('', 'success', $output);
    }

    public function popup_forms()
    {
        checkIsAjax();

        $op = $this->uri->segment(3);
        switch ($op) {
            case 'preview_subcategories':
                $categoryId = (int) uri()->segment(4);

                model('elasticsearch_items')->get_items([
                    'category'                      => $categoryId,
                    'page'                          => 1,
                    'per_p'                         => 1,
                    'featured_order'                => 1,
                    'aggregate_category_counters'   => 1,
                    'aggregate_id_category'         => $categoryId,
                ]);

                if (!empty(model('elasticsearch_items')->aggregates['categories'])) {
                    $subcategoriesList = array_keys(model('elasticsearch_items')->aggregates['categories']);
                    $subcategories = model('category')->getCategories(['columns' => 'category_id, name, p_or_m, cat_type', 'cat_list' => implode(',', $subcategoriesList)]);
                    foreach ($subcategories as $subcategory) {
                        if ($subcategory['category_id'] == $categoryId) {
                            continue;
                        }
                        $subcategory['counter'] = model('elasticsearch_items')->aggregates['categories'][$subcategory['category_id']];
                        $data['subcats'][] = $subcategory;
                    }
                }

                $this->view->display('new/item/category/preview_subcategories_view', $data);
            break;
            case 'edit_category':
                checkIsLoggedAjaxModal();
                checkAdminAjax('manage_content');

                $data['cat'] = (int) $this->uri->segment(4);
                $data['category'] = model(Category_Model::class)->get_category($data['cat']);
                $data['categoryGolden'] = model(Category_Groups_Model::class)->get_child_golden_category($data['cat']);
                $data['categoryGroups'] = model(Category_Groups_Model::class)->get_category_groups();
                $data['categories'] = model(Category_Model::class)->getCategories(['parent' => 0, 'columns' => 'category_id, name']);
                $data['category']['breadcrumbs'] = json_decode('[' . $data['category']['breadcrumbs'] . ']', true);
                $meta_rules = json_decode(model('meta')->get_meta_by_key('category_index')['rules'], true);
                $data['meta_rules'] = $meta_rules['title'];
                $this->view->display('admin/categories/form_view', $data);

            break;
            case 'add_category':
                checkIsLoggedAjaxModal();
                checkAdminAjax('manage_content');
                $data['categories'] = model(Category_Model::class)->getCategories(['parent' => 0, 'columns' => 'category_id, name']);
                $data['categoryGroups'] = model(Category_Groups_Model::class)->get_category_groups();
                $meta_rules = json_decode(model('meta')->get_meta_by_key('category_index')['rules'], true);
                $data['meta_rules'] = $meta_rules['title'];
                $this->view->display('admin/categories/form_view', $data);

            break;
            case 'add_category_i18n':
                checkIsLoggedAjaxModal();
                $this->load->model('Category_Model', 'category');

                $id_category = (int) $this->uri->segment(4);
                $data['category'] = $this->category->get_category($id_category);
                $data['category']['breadcrumbs'] = json_decode('[' . $data['category']['breadcrumbs'] . ']', true);
                $data['category']['translations_data'] = json_decode($data['category']['translations_data'], true);
                $data['tlanguages'] = $this->translations->get_languages();
                $this->view->display('admin/categories/form_i18n_view', $data);

            break;
            case 'edit_category_i18n':
                checkIsLoggedAjaxModal();
                $this->load->model('Category_Model', 'category');
                $id_category = (int) $this->uri->segment(4);
                $lang_category = $this->uri->segment(5);
                $data['category_i18n'] = $this->category->get_category_i18n(['category_id' => $id_category, 'lang_category' => $lang_category]);
                $data['category'] = $this->category->get_category($id_category);
                $data['lang_category'] = $this->translations->get_language_by_iso2($lang_category);
                $data['category_i18n']['breadcrumbs'] = json_decode('[' . $data['category_i18n']['breadcrumbs'] . ']', true);
                $data['category_i18n']['translations_data'] = json_decode($data['category_i18n']['translations_data'], true);
                $this->view->display('admin/categories/form_i18n_view', $data);

            break;
            case 'choose_category':
                checkIsLoggedAjaxModal();
                $this->view->display('new/categories/popup_choose_category_view');
            break;
            case 'industries_of_interest':
                checkIsLoggedAjaxModal();
                checkPermisionAjaxModal('users_administration');

                /** @var \Buyer_Item_Categories_Stats_Model $buyerStatsModel */
                $buyerStatsModel = model(\Buyer_Item_Categories_Stats_Model::class);
                $buyerStatsTable = $buyerStatsModel->getTable();

                /** @var \Item_Category_Model $itemCategories */
                $itemCategories = model(\Item_Category_Model::class);

                $queryParams = [
                    'columns'    => [
                        "COUNT({$buyerStatsTable}.`idu`) AS users_interested_in",
                        "{$buyerStatsTable}.`id_category`",
                    ],
                    'scopes'     => ['iduIsNotNull' => true],
                    'with'       => ['category as industry'],
                    'group'      => [
                        "{$buyerStatsTable}.`id_category`",
                    ],
                ];

                if (!empty($userId = (int) uri()->segment(4))) {
                    $queryParams['scopes']['id_user'] = $userId;
                }

                if (empty($categoriesStatistics = $buyerStatsModel->findAllBy($queryParams))) {
                    messageInModal(translate('systmess_no_industries_of_interest'), 'info');
                }

                $allIndustries = array_column(
                    $itemCategories->findAllBy([
                        'scopes' => [
                            'parent' => 0,
                        ],
                    ]),
                    null,
                    'category_id'
                );

                $industriesStatistics = [];
                foreach ($categoriesStatistics as $category) {
                    $industriesStatistics[$category['industry']['industry_id']] = [
                        'users_interested_in' => ($industriesStatistics[$category['industry']['industry_id']]['users_interested_in'] ?? 0) + $category['users_interested_in'],
                        'industry_id'         => $category['industry']['industry_id'],
                        'name'                => $allIndustries[$category['industry']['industry_id']]['name'],
                    ];
                }

                usort($industriesStatistics, fn($a, $b) => $b['users_interested_in'] - $a['users_interested_in']);

                views(
                    'admin/categories/popup_industries_of_interest_view',
                    [
                        'industriesStatistics' => $industriesStatistics,
                        'userId'               => $userId ?: null,
                    ]
                );

			break;
		}
	}

    public function ajax_category_operation()
    {
        checkIsAjax();
        checkIsLoggedAjax();
        checkPermisionAjax('manage_content');

        $operation = $this->uri->segment(3);
        $this->load->model('Category_Model', 'category');

        switch ($operation) {
            case 'preview_category_link':
                $validation_rules = [
                    [
                        'field' => 'title',
                        'label' => 'Title',
                        'rules' => ['required' => '', 'max_len[255]' => ''],
                    ],
                ];
                $this->validator->set_rules($validation_rules);

				if(!$this->validator->validate()) {
					jsonResponse($this->validator->get_array_errors());
                }

                $title = cleanInput($_POST['title']);
                jsonResponse('', 'success', ['category_link' => strForUrl($title)]);

            break;
            case 'add_category':
                $validation_rules = [
                    [
                        'field' => 'golden_12_parent',
                        'label' => 'Golden 12',
                        'rules' => ['required' => '', 'integer' => '', 'min[0]' => ''],
                    ],
                    [
                        'field' => 'parent',
                        'label' => 'Parent',
                        'rules' => ['required' => '', 'integer' => '', 'min[0]' => ''],
                    ],
                    [
                        'field' => 'name',
                        'label' => 'Title',
                        'rules' => ['required' => '', 'max_len[255]' => ''],
                    ],
                    [
                        'field' => 'is_restricted',
                        'label' => 'Is restricted (18+)',
                        'rules' => ['required' => '', 'integer' => '', 'min[0]' => ''],
                    ],
                    [
                        'field' => 'p_or_m',
                        'label' => 'Product/Motor',
                        'rules' => ['required' => '', 'integer' => '', 'min[1]' => '', 'max[2]' => ''],
                    ],
                    [
                        'field' => 'cat_type',
                        'label' => 'Type',
                        'rules' => ['required' => '', 'integer' => '', 'min[0]' => ''],
                    ],
                ];

                $this->validator->set_rules($validation_rules);

                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                if (empty($_POST['parent']) && empty($_POST['golden_12_parent'])) {
                    jsonResponse('Select Golden 12 category or category parent!');
                }

                $title = cleanInput($_POST['title']);
                $categoryGolden = ['golden12Parent' => $_POST['golden_12_parent']];
                $category = [
                    'parent'            => $_POST['parent'],
                    'name'              => cleanInput($_POST['name']),
                    'link'              => strForUrl($title),
                    'p_or_m'            => $_POST['p_or_m'],
                    'cat_type'          => $_POST['cat_type'],
                    'h1'                => cleanInput($_POST['h1']),
                    'title'             => cleanInput($_POST['title']),
                    'hs_tariff_number'  => cleanInput($_POST['hs_tariff_number']),
                    'keywords'          => cleanInput($_POST['keywords']),
                    'description'       => cleanInput($_POST['description']),
                    'translations_data' => json_encode(['en' => ['abbr_iso2' => 'en', 'lang_name' => 'English']]),
                    'is_restricted'     => $_POST['is_restricted'],
                ];

                if (isset($_POST['vin'])) {
                    $category['vin'] = $_POST['vin'];
                }

                if (0 != $categoryGolden['golden12Parent']) {
                    $golden12Parent = model(Category_Model::class)->get_category($categoryGolden['golden12Parent']);

                    if (empty($golden12Parent)) {
                        jsonResponse('Golden 12 parent category does not exist');
                    }
                }

                if (0 != $category['parent']) {
                    $parent = model(Category_Model::class)->get_category($category['parent']);

                    if (empty($parent)) {
                        jsonResponse('Parent category does not exist');
                    }

                    if (1 == $parent['is_restricted']) {
                        $category['is_restricted'] = 1;
                    }
                }

                if ($id_cat = model(Category_Model::class)->set_category($category)) {
                    model('elasticsearch_category')->sync($id_cat);

                    $breadcrumbs = json_encode([$id_cat => $category['name']]);
                    if (0 != $categoryGolden['golden12Parent']) {
                        $parent = model(Category_Groups_Model::class)->get_golden_category($categoryGolden['golden12Parent']);
                        model(Category_Groups_Model::class)->append_children_to_golden(['id_group' => $parent['id_group'], 'id_category' => $id_cat]);
                    }
                    if (0 != $category['parent']) {
                        //insert in parents
                        $parent = model(Category_Model::class)->get_category($category['parent']);

                        $parents = model(Category_Model::class)->parents_from_breadcrumbs($parent['breadcrumbs']);

                        model(Category_Model::class)->append_children_to_parents($id_cat, $parents);
                        $breadcrumbs = $parent['breadcrumbs'] . ',' . $breadcrumbs;
                    }
                    //update breadcrumbs
                    model(Category_Model::class)->simple_update_category($id_cat, ['breadcrumbs' => $breadcrumbs, 'actualized' => 1]);

                    jsonResponse('Category was added succesfully', 'success');
                } else {
                    jsonResponse('Error: You cannot add the category now. Please try again later.');
                }

            break;
            case 'update_category':
                $validation_rules = [
                    [
                        'field' => 'parent',
                        'label' => 'Parent',
                        'rules' => ['integer' => '', 'min[0]' => ''],
                    ],
                    [
                        'field' => 'name',
                        'label' => 'Title',
                        'rules' => ['required' => '', 'max_len[255]' => ''],
                    ],
                    [
                        'field' => 'p_or_m',
                        'label' => 'Product/Motor',
                        'rules' => ['required' => '', 'integer' => '', 'min[1]' => '', 'max[2]' => ''],
                    ],
                    [
                        'field' => 'cat_type',
                        'label' => 'Type',
                        'rules' => ['required' => '', 'integer' => ''],
                    ],
                    [
                        'field' => 'category_id',
                        'label' => 'Category',
                        'rules' => ['required' => '', 'integer' => '', 'min[0]' => ''],
                    ],
                ];

                $this->validator->set_rules($validation_rules);

                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                if (empty($_POST['parent']) && empty($_POST['golden_12_parent'])) {
                    jsonResponse('Select Golden 12 category or category parent!');
                }

                $idCategory = $_POST['category_id'];
                $categoryGolden = ['golden12Parent' => (int) $_POST['golden_12_parent']];
                $old_cat = model(Category_Model::class)->get_category($idCategory);

                $title = cleanInput($_POST['title']);
                $category = [
                    'category_id'      => $idCategory,
                    'parent'           => $_POST['parent'],
                    'name'             => filter($_POST['name']),
                    'link'             => strForUrl(filter($_POST['name'])),
                    'cat_type'         => $_POST['cat_type'],
                    'h1'               => cleanInput($_POST['h1']),
                    'p_or_m'           => $_POST['p_or_m'],
                    'title'            => $title,
                    'hs_tariff_number' => cleanInput($_POST['hs_tariff_number']),
                    'keywords'         => cleanInput($_POST['keywords']),
                    'description'      => cleanInput($_POST['description']),
                    'is_restricted'    => $_POST['is_restricted'],
                ];

                $golden12Parent = model(Category_Model::class)->get_category($categoryGolden['golden12Parent']);
                $parent = model(Category_Model::class)->get_category($category['parent']);

                if (1 == $parent['is_restricted']) {
                    $category['is_restricted'] = 1;
                }

                $children_list = $old_cat['cat_childrens'];
                $children_array = array_filter(explode(',', $children_list));
                if (in_array($category['parent'], $children_array)) {
                    jsonResponse('Error: New parent cannot be from children category. Please try again later.');
                }

                if (model(Category_Model::class)->simple_update_category($idCategory, $category)) {
                    model('elasticsearch_category')->sync($idCategory);

                    //bread
                    $breadcrumbs = json_encode([$idCategory => $category['name']]);
                    $old_breadcrumbs = $old_cat['breadcrumbs'];

                    if ($category['parent'] != $old_cat['parent']) {
                        //actualize categories breadcrumb if parent changed

                        array_push($children_array, $idCategory);

                        if (0 != $old_cat['parent']) {
                            //delete children from old parents
                            $old_parents_list = model(Category_Model::class)->parents_from_breadcrumbs($old_cat['breadcrumbs'], $idCategory);

                            $old_parents = model(Category_Model::class)->getCategories(['cat_list' => $old_parents_list, 'columns' => 'category_id, cat_childrens']);
                            $cleaned_old_par = [];
                            foreach ($old_parents as $old_par) {
                                $data['cat_childrens'] = implode(',', array_diff(explode(',', $old_par['cat_childrens']), $children_array));
                                model(Category_Model::class)->simple_update_category($old_par['category_id'], $data);
                            }
                        }

                        if (0 != $category['parent']) {
                            //insert into new parents
                            $parent = model(Category_Model::class)->get_category($category['parent']);

                            $golden12Parent = model(Category_Groups_Model::class)->get_child_golden_category($idCategory);

                            if (!empty($golden12Parent)) {
                                $golden12Parent = model(Category_Groups_Model::class)->delete_groups_relation($idCategory);
                            }

                            $parents = model(Category_Model::class)->parents_from_breadcrumbs($parent['breadcrumbs'], $idCategory);

                            model(Category_Model::class)->append_children_to_parents(implode(',', $children_array), $parents);
                            $breadcrumbs = $parent['breadcrumbs'] . ',' . $breadcrumbs;
                        }
                    }

                    if (0 != $categoryGolden['golden12Parent']) {
                        $parent = model(Category_Groups_Model::class)->get_golden_category($categoryGolden['golden12Parent']);
                        model(Category_Groups_Model::class)->update_children_to_golden($idCategory, ['id_group' => $parent['id_group'], 'id_category' => $idCategory]);
                    }

                    if ($category['name'] != $old_cat['name'] && !empty($old_cat['breadcrumbs'])) {
                        //actualize categories breadcrumb if name changed
                        $breadcrumbs = str_replace(json_encode([$idCategory => $old_cat['name']]), $breadcrumbs, $old_cat['breadcrumbs']);
                    }

                    if ($category['parent'] != $old_cat['parent'] || $category['name'] != $old_cat['name']) {
                        //actualize children breadcrumb
                        $list_for_replace = $idCategory;
                        if (!empty($children_list)) {
                            $list_for_replace .= ',' . $children_list;
                        }

                        model(Category_Model::class)->replace_cat_breadcrumbs_part($old_breadcrumbs, $breadcrumbs, $list_for_replace);
                    }

                    //children actualization
                    if ($old_cat['p_or_m'] != $_POST['p_or_m']) {
                        $update_children['p_or_m'] = $category['p_or_m'];
                    }

                    if ((2 == $_POST['p_or_m']) && ($old_cat['vin'] != $_POST['vin'])) {
                        $update_children['vin'] = $category['vin'];
                    } elseif (1 == $_POST['p_or_m'] && 1 == $old_cat['vin']) {
                        $update_children['vin'] = 0;
                    }

                    model(Category_Model::class)->change_children_data($idCategory, $update_children, $children_list);

                    jsonResponse('Category was updated succesfully', 'success');
                } else {
                    jsonResponse('Error: You cannot update the category now. Please try again later.');
                }

            break;
            case 'add_category_i18n':
                $validator = $this->validator;
                $validator_rules = [
                    [
                        'field' => 'category_id',
                        'label' => 'Category',
                        'rules' => ['required' => '', 'integer' => '', 'min[0]' => ''],
                    ],
                    [
                        'field' => 'name',
                        'label' => 'Title',
                        'rules' => ['required' => '', 'max_len[255]' => ''],
                    ],
                    [
                        'field' => 'title',
                        'label' => 'Meta title',
                        'rules' => ['required' => '', 'max_len[255]' => ''],
                    ],
                    [
                        'field' => 'h1',
                        'label' => 'H1',
                        'rules' => ['required' => '', 'max_len[50]' => ''],
                    ],
                    [
                        'field' => 'description',
                        'label' => 'Meta description',
                        'rules' => ['required' => ''],
                    ],
                    [
                        'field' => 'keywords',
                        'label' => 'Meta keywords',
                        'rules' => ['required' => ''],
                    ],
                ];

                $this->validator->set_rules($validator_rules);

                if (!$validator->validate()) {
                    jsonResponse($validator->get_array_errors());
                }

                $id_category = $_POST['category_id'];
                $category = $this->category->get_category($id_category);
                if (empty($category)) {
                    jsonResponse('Error: Category does not exist.');
                }

                $lang_category = $_POST['lang_category'];
                $tlang = $this->translations->get_language_by_iso2($lang_category);
                if (empty($tlang)) {
                    jsonResponse('Error: Language does not exist.');
                }

                $category_translations_data = json_decode($category['translations_data'], true);
                if (array_key_exists($lang_category, $category_translations_data)) {
                    jsonResponse('Error: Category translation for this language already exist.');
                }

                $category_translations_data[$lang_category] = [
                    'lang_name' => $tlang['lang_name'],
                    'abbr_iso2' => $tlang['lang_iso2'],
                ];

                $title = cleanInput($_POST['title']);
                $insert = [
                    'category_id'   => $id_category,
                    'name'          => filter($_POST['name']),
                    'title'         => $title,
                    'link'          => strForUrl($title),
                    'h1'            => cleanInput($_POST['h1']),
                    'keywords'      => cleanInput($_POST['keywords']),
                    'description'   => cleanInput($_POST['description']),
                    'category_lang' => $lang_category,
                ];

                if ($this->category->set_category_i18n($insert)) {
                    $this->category->simple_update_category($id_category, ['translations_data' => json_encode($category_translations_data)]);
                    jsonResponse('The category translation has been successfully added', 'success');
                }

                jsonResponse('Error: Cannot insert now. Please try later.');

            break;
            case 'update_category_i18n':
                $validator = $this->validator;
                $validator_rules = [
                    [
                        'field' => 'category_id_i18n',
                        'label' => 'Category info',
                        'rules' => ['required' => '', 'integer' => '', 'min[0]' => ''],
                    ],
                    [
                        'field' => 'name',
                        'label' => 'Title',
                        'rules' => ['required' => '', 'max_len[255]' => ''],
                    ],
                    [
                        'field' => 'title',
                        'label' => 'Meta title',
                        'rules' => ['required' => '', 'max_len[255]' => ''],
                    ],
                    [
                        'field' => 'h1',
                        'label' => 'H1',
                        'rules' => ['required' => '', 'max_len[50]' => ''],
                    ],
                    [
                        'field' => 'description',
                        'label' => 'Meta description',
                        'rules' => ['required' => ''],
                    ],
                    [
                        'field' => 'keywords',
                        'label' => 'Meta keywords',
                        'rules' => ['required' => ''],
                    ],
                ];

                $this->validator->set_rules($validator_rules);

                if (!$validator->validate()) {
                    jsonResponse($validator->get_array_errors());
                }

                $id_category_i18n = $_POST['category_id_i18n'];
                $category = $this->category->get_category_i18n(['id_category_i18n' => $id_category_i18n]);
                if (empty($category)) {
                    jsonResponse('Error: Category does not exist.');
                }

                $title = cleanInput($_POST['title']);
                $update = [
                    'name'        => filter($_POST['name']),
                    'title'       => $title,
                    'link'        => strForUrl($title),
                    'h1'          => cleanInput($_POST['h1']),
                    'keywords'    => cleanInput($_POST['keywords']),
                    'description' => cleanInput($_POST['description']),
                ];

                if ($this->category->update_category_i18n($id_category_i18n, $update)) {
                    jsonResponse('The category translation has been successfully updated', 'success');
                }

                jsonResponse('Error: Cannot update category translation now. Please try later.');

            break;
            case 'delete_category_i18n':
                $validator = $this->validator;
                $validator_rules = [
                    [
                        'field' => 'category',
                        'label' => 'Category',
                        'rules' => ['required' => '', 'integer' => '', 'min[0]' => ''],
                    ],
                    [
                        'field' => 'lang',
                        'label' => 'Language',
                        'rules' => ['required' => '', 'max_len[5]' => ''],
                    ],
                ];

                $this->validator->set_rules($validator_rules);

                if (!$validator->validate()) {
                    jsonResponse($validator->get_array_errors());
                }
                $id_category = $_POST['category'];
                $lang_category = cleanInput($_POST['lang']);
                $category_i18n = $this->category->get_category_i18n(['category_id' => $id_category, 'lang_category' => $lang_category]);
                if (empty($category_i18n)) {
                    jsonResponse('Error: The category translation does not exist.');
                }

                $category = $this->category->get_category($category_i18n['category_id']);
                $category_translations_data = json_decode($category['translations_data'], true);
                unset($category_translations_data[$lang_category]);
                $this->category->simple_update_category($category_i18n['category_id'], ['translations_data' => json_encode($category_translations_data)]);
                $this->category->delete_category_i18n($category_i18n['category_id_i18n']);
                jsonResponse('The category translation has been successfully deleted.', 'success');

            break;
            case 'get_categories':
                $params = [
                    'columns' => 'category_id, name',
                ];

                if ((int) $_POST['parent'] > 0) {
                    $params['parent'] = (int) $_POST['parent'];
                }

                if (isset($_POST['p_or_m'])) {
                    $params['p_or_m'] = (int) $_POST['p_or_m'];
                }

                $categories = $this->category->getCategories($params);
                jsonResponse('', 'success', ['categories' => $categories]);

            break;
        }
	}

    public function ajax_delete_category()
    {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        if (!logged_in()) {
            jsonResponse(translate("systmess_error_should_be_logged"));
        }

        if (!have_right('manage_content')) {
            jsonResponse(translate("systmess_error_rights_perform_this_action"));
        }

        if (!isset($_POST['id'])) {
            jsonResponse('Error: Identifier of the category was not sent.');
        }

        $this->load->model('Category_Model', 'category');

        if ($this->category->delete_category((int) $_POST['id'])) {
            jsonResponse('Category was deleted successfully', 'success');
        } else {
            jsonResponse('Error: You cannot delete the category now. Please try again later.');
        }
    }

    public function administration()
    {
        checkAdmin('items_categories_administration');

        $this->load->model('Category_Model', 'category');
        $data['categories'] = $this->category->getCategories(['parent' => 0]);
        $data['title'] = 'Categories';

        $this->view->assign($data);
        $this->view->display('admin/header_view');
        $this->view->display('admin/categories/index_view');
        $this->view->display('admin/footer_view');
    }

	function actualize(){
		ini_set('max_execution_time', 0);
        ini_set('request_terminate_timeout', 0);

        checkIsAjax();
        checkPermisionAjax('sync_item_categories');

		$dbCategories = model(Category_Model::class)->getCategories([
            'order_by' => 'category_id ASC',
            'columns' => 'category_id, parent, name',
            'temp_is_actualized' => 0
        ]);

        $categories = [];
        foreach ($dbCategories as $dbCategory) {
            $dbCategory['temp_category_breadcrumbs'] = json_encode([$dbCategory['category_id'] => $dbCategory['name']]);
            $dbCategory['temp_category_breadcrumbs_full'] = [
				'category_id' => $dbCategory['category_id'],
				'name' => $dbCategory['name']
			];
            $categories[$dbCategory['category_id']] = $dbCategory;
        }

        $parents = [0];
        do {
            $updateCategoriesBreadcrumbs = model(Category_Model::class)->categoriesSyncMeta($categories, $parents);
            $categories = $updateCategoriesBreadcrumbs['categories'];
            $parents = $updateCategoriesBreadcrumbs['parents'];
		} while (!empty($parents));

        model(Category_Model::class)->emptySyncCategories();

        $i = 1;
        $insertBatchCategories = [];
		foreach ($categories as $category) {
			$insertBatchCategories[] = [
                'category_id' => $category['category_id'],
                'children' => !empty($category['children']) ? implode(',', $category['children']) : null,
                'breadcrumbs' => $category['full_breadcrumbs'],
                'breadcrumbs_object' => json_encode($category['full_breadcrumbs_object']),
                'parents' => !empty($category['parents']) ? implode(',', $category['parents']) : null
            ];

            if($i == 500 ){
                model(Category_Model::class)->insertSyncCategories($insertBatchCategories);
                $insertBatchCategories = [];
                $i = 1;
                continue;
            }

            $i++;
        }

        if($i < 500 && !empty($insertBatchCategories)){
            model(Category_Model::class)->insertSyncCategories($insertBatchCategories);
        }

        model(Category_Model::class)->updateMainCategoriesWithSyncCategories();

		jsonResponse('Categories were actualized.', 'success');

    }

    public function age_verification()
    {
        $op = $this->uri->segment(3);
        switch ($op) {
            case 'check_age':
                $data['list_of_months'] = [1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October',  11 => 'November', 12 => 'December'];
                if (request()->query->getInt('webpackData')) {
                    $data['webpackData'] = request()->query->getInt('webpackData');
                }

                jsonResponse('', 'success', [
                    'content' => $this->view->fetch('new/categories/age_verification_view', $data),
                ]);
            break;
        }
    }
}
