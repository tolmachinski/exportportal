<?php

use App\Common\Buttons\ChatButton;
use App\Common\Contracts\Bill\BillStatus;
use App\Common\Contracts\Bill\BillTypes;
use App\Common\Contracts\BuyerIndustries\CollectTypes;
use App\Common\Contracts\Droplist\ItemStatus;
use App\Common\Contracts\Droplist\NotificationType;
use App\Common\Contracts\FeaturedProduct\FeaturedStatus;
use App\Common\Contracts\HighlightedProduct\HighlightedStatus;
use App\Common\Contracts\Product\ProductDescriptionStatus;
use App\Common\DependencyInjection\ServiceLocator\LibraryLocator;
use App\Common\Encryption\MasterKeyAwareTrait;
use App\Common\Exceptions\Items\ItemNotFoundException;
use App\Common\Traits\FileuploadOptionsAwareTrait;
use App\Common\Traits\Items\DraftColumnsMetadataAwareTrait;
use App\Common\Traits\Items\FormValidationMetadataAware;
use App\Common\Traits\Items\ProductCardPricesTrait;
use App\Common\Validation\ConstraintViolation;
use App\Common\Validation\ConstraintViolationInterface;
use App\Common\Validation\FlatValidationData;
use App\Common\Validation\Legacy\NonStrictValidator;
use App\Common\Validation\Legacy\ValidatorAdapter as LegacyValidatorAdapter;
use App\Common\Validation\Standalone\AggregateValidator;
use App\Common\Validation\Standalone\ValidatorInterface;
use App\Common\Validation\ValidationException;
use App\DataProvider\DroplistItemsDataProvider;
use App\DataProvider\IndexedProductDataProvider;
use App\Email\OutOfStockBackInStock;
use App\Email\ShareItem;
use App\Filesystem\CompanyLogoFilePathGenerator;
use App\Filesystem\FilePathGenerator;
use App\Filesystem\ItemDroplistFilePathGenerator;
use App\Filesystem\ItemPathGenerator;
use App\Messenger\Message\Command\Media\CopyFileToStorage;
use App\Messenger\Message\Command\SaveBuyerIndustryOfInterest;
use App\Messenger\Message\Command\SaveViewItemsLog;
use App\Messenger\Message\Event\Product\ProductChangedVisibilityEvent;
use App\Messenger\Message\Event\Product\ProductWasUpdatedEvent;
use App\Validators\DraftConfigurationsValidator;
use App\Validators\DraftEntryValidator;
use App\Validators\DraftExtendRequestValidator;
use App\Validators\DraftFileValidator;
use App\Validators\ImagesSetValidator;
use App\Validators\ItemAddressValidator;
use App\Validators\ItemTranslateDescriptionValidator;
use App\Validators\ItemValidator;
use App\Validators\ItemVariantsValidator;
use App\Validators\TranslationDescriptionValidator;
use App\Validators\UploadedFileMimeTypeValidator;
use App\Validators\UploadedFileSizeValidator;
use App\Validators\VinValidator;
use Doctrine\Common\Collections\ArrayCollection;
use ExportPortal\Bridge\Mailer\Mime\RefAddress;
use ExportPortal\Bridge\Symfony\Component\Messenger\MessengerInterface;
use ExportPortal\Contracts\Filesystem\Exception\ReadException;
use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;
use Hoa\Mime\Mime;
use League\Flysystem\UnableToDeleteDirectory;
use League\Flysystem\UnableToWriteFile;
use Money\Money;
use ParagonIE\Halite\Asymmetric\Crypto;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\MimeTypes;

use const App\Common\DB_DATE_FORMAT;
use const App\Logger\Activity\OperationTypes\ADD;
use const App\Logger\Activity\OperationTypes\EDIT;
use const App\Logger\Activity\ResourceTypes\ITEM;
use const App\Moderation\Types\TYPE_ITEM;

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 */
class Items_Controller extends TinyMVC_Controller
{
    use MasterKeyAwareTrait;
    use FileuploadOptionsAwareTrait;
    use FormValidationMetadataAware;
    use DraftColumnsMetadataAwareTrait;
    use ProductCardPricesTrait;

    private $breadcrumbs = [];
    private $id_item = 0;
    private $item = [];
    private $moderateValues = ['title', 'video', 'tags', 'description'];

    private IndexedProductDataProvider $indexedProductDataProvider;

    private DroplistItemsDataProvider $droplistItemsDataProvider;

    private MessageBusInterface $eventBus;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->indexedProductDataProvider = $container->get(IndexedProductDataProvider::class);

        $this->droplistItemsDataProvider = $container->get(DroplistItemsDataProvider::class);

        $messenger = $container->get(MessengerInterface::class);
        $this->eventBus = $messenger->bus('event.bus');
    }


    private function _load_main()
    {
        $this->load->model('Category_Model', 'category');
        $this->load->model('Catattributes_Model', 'catattr');
        $this->load->model('Items_Model', 'items');
        $this->load->model('User_Model', 'user');
        $this->load->model('ItemQuestions_Model', 'itemquestions');
    }

    public function index()
    {
        show_404();
    }

    public function latest() {
        /** @var Country_Model $countryModel */
        $countryModel = model(Country_Model::class);

        /** @var Category_Model $categoryModel */
        $categoryModel = model(Category_Model::class);

        $app = tmvc::instance();
        $data = array();

        $search_uri_components = $app->site_urls['items/latest']['replace_uri_components'];
        $current_uri = array_filter($this->uri->uri_to_assoc(4, $app->route_url_segments));

        $perPage = (int) config('item_default_perpage');
        $page = isset($current_uri['page']) ? (int) $current_uri['page'] : 1;

        ($page <= 1) ?: $data['metaParams']['[PAGE]'] = $page;

        if ($page < 1) {
            show_404();
        }

        $last_items_conditions = array(
            'aggregate_category_counters'	=> true,
            'aggregate_countries_counters'	=> true,
            'sort_by'						=> array('create_date-desc'),
            'per_p' 						=> $perPage,
            'page' 							=> $page
        );

        $get_items_by_period = config('latest_items_by_period');

        if ($get_items_by_period) {
            $date = new DateTime('now');
            $last_items_conditions['start_from'] = $date->sub(new DateInterval('P' . config('latest_items_period', 1) . 'D'))->format('Y-m-d');
        } elseif ($page > (int) config('latest_items_total_pages')) {
            show_404();
        }

        model('elasticsearch_items')->get_items($last_items_conditions);
        $countriesCounters = model('elasticsearch_items')->aggregates['countries'];

        if (!empty($countriesCounters)) {
            $countries_ids = array_keys($countriesCounters);
            $searchCountries = $countryModel->get_simple_countries(implode(",", $countries_ids));
        }

        $categories = model('elasticsearch_items')->aggregates['categories'];
        $category_keys = '';
        $categories_counters = array();

        foreach ($categories as $category => $count) {
            $explode = explode(',', $category);
            $end = end($explode);
            $category_keys .= ',' . $end;
            $categories_counters[$end] = $count;
        }

        if (!empty($category_keys)) {
            $mysql_categories = $categoryModel->getCategories(array('cat_list' => substr($category_keys, 1)));
            foreach($mysql_categories as &$mysql_category) {
                $mysql_category['counter'] = $categories_counters[$mysql_category['category_id']];
            }
            $counterCategories = $categoryModel->_categories_map($mysql_categories);
        }

        $items = $this->formatProductPrice(model('elasticsearch_items')->items);

        $elastic_count_items = model('elasticsearch_items')->itemsCount;
        $config_count_items = (int) config('latest_items_total_pages') * $perPage;

        $countItems = ( ! $get_items_by_period && ($config_count_items < $elastic_count_items)) ? $config_count_items : $elastic_count_items;

        // check if curent page value is valid
        if (($page > ceil($countItems / $perPage)) && $page != 1) {
            show_404();
        }

        $items_country_ids = array_column($items, 'p_country', 'p_country');

        if (!empty($items_country_ids)) {
            $itemsCountries = $countryModel->get_simple_countries(implode(",", $items_country_ids));
        }

        $sellers_ids = array_column($items, 'id_seller', 'id_seller');

        if (!empty($sellers_ids)) {
            $sellers = model('user')->getSellersForList(implode(',',$sellers_ids), true);
            $sellers_list = arrayByKey($sellers, 'idu');

            foreach ($items as $key => $item) {
                $items[$key]['seller'] = empty($sellers_list[$item['id_seller']]) ? array() : $sellers_list[$item['id_seller']];
            }
        }

        $links_map = array(
            $search_uri_components['page'] => array(
                'type' => 'uri',
                'deny' => array($search_uri_components['page'])
            )
        );

        $links_tpl_without = $this->uri->make_templates($links_map, $current_uri, true);
        $links_tpl = $this->uri->make_templates($links_map, $current_uri);

        // make links template for countries and categories found
        $get_params['sort_by'] = 'create_date-desc';
        if (isset($_GET['lang'])) {
            $get_params['lang'] = $_GET['lang'];
        }

        $get_params = http_build_query($get_params);

        // make pagination
        $paginator_config = array(
            'base_url'      => get_dynamic_url($links_tpl[$search_uri_components['page']], 'items/latest'),
            'first_url'     => get_dynamic_url($links_tpl_without[$search_uri_components['page']], 'items/latest'),
            'total_rows'    => $countItems,
            'per_page'      => $perPage,
            'cur_page'		=> $page,
            'replace_url'   => true
        );

        library('pagination')->initialize($paginator_config);

        $this->breadcrumbs[] = array('link' => get_dynamic_url('items/latest'), 'title' => translate('products_section_breadcrumb_title'));

        $data = [
            'sidebarContent'    => 'new/item/latest_products/sidebar_view',
            'countriesCounters' => $countriesCounters,
            'counterCategories' => $counterCategories,
            'searchCountries'   => $searchCountries,
            'categoryLink'      => 'category/' . config('replace_uri_template') . '?' . $get_params,
            'categoryGroups'    => model('category_groups')->get_category_groups(),
            'countryLink'       => 'search/country/' . config('replace_uri_template') . '?' . $get_params . '&returnToPage=1',
            'breadcrumbs'       => $this->breadcrumbs,
            'pagination'        => library(TinyMVC_Library_Pagination::class)->create_links(),
            'countries'         => $countryModel->get_countries(),
            'mainCats'          => $categoryModel->getCategories([
                'parent'  => 0,
                'columns' => 'category_id, name, parent'
            ]),
            'perPage'           => $perPage,
            'page'              => $page,
            'count'             => $countItems,
            'items'             => $items,
            'content'           => 'item/latest_products/index_view',
            'styleCritical'     => 'latest_items',
            'customEncoreLinks' => true,
            'items_country'     => $itemsCountries,
        ];

        if (logged_in()) {

            /** @var Items_Model $itemsModel */
            $itemsModel = model(Items_Model::class);

            $savedList = $itemsModel->get_items_saved(id_session());
            $data['savedItems'] = explode(',', $savedList);
        }

        views()->displayWebpackTemplate($data);
    }

    /* Used on home page and on item detail */
    public function ajax_get_latest_items() {
        checkIsAjax();

        $request = request()->request;
        $isPromoItems = $request->getBoolean('promoItems') ?: false;
        $perPage = (int) config('home_latest_items_per_page', 12);

        if ($isPromoItems) {
            $perPage = (int) config('promo_items_per_page', 8);
        } elseif (have_right('manage_personal_items')) {
            $perPage = (int) config('home_latest_items_per_page_for_logged', 8);
        }

        $latestItems = $this->indexedProductDataProvider->getLatestItems(
            model(\User_Model::class),
            $perPage,
            config('latest_items_by_period') ? (new DateTime())->sub(new DateInterval('P' . config('latest_items_period', 1) . 'D'))->format('Y-m-d') : null,
        );

        if (empty($latestItems)) {
            jsonResponse('', 'success', ['items' => '']);
        }

        $itemsCount = count($latestItems);
        if ($itemsCount > 4 && $itemsCount % 2 !== 0) {
            array_pop($latestItems);
            $itemsCount--;
        }

        jsonResponse('', 'success', [
            'items'      => $this->prepareItemsView($latestItems, null, false, true, $isPromoItems),
            'itemsCount' => $itemsCount,
        ]);
    }

    /**
     * Get items for you
     */
    public function ajax_get_items_for_you(): void
    {
        checkIsAjax();
        checkIsLogged();

        /** @var Buyer_Item_Categories_Stats_Model $userCategoriesStatsModel */
        $userCategoriesStatsModel = model(Buyer_Item_Categories_Stats_Model::class);
        $userStatsCategories = $userCategoriesStatsModel->getUserStatsCategories((int) session()->id);

        $items = [];
        $itemsCount = 0;

        if (!empty($userStatsCategories)) {
            $items = $this->indexedProductDataProvider->getJustForYouItems(
                (int) session()->id,
                $userStatsCategories,
                config('home_just_for_you_items_per_page', 12)
            );

            $itemsCount = count($items);
            if ($itemsCount < 4) {
                jsonResponse('', 'success', ['items' => '']);
            }
        } else {
            jsonResponse('', 'success', ['items' => '']);
        }

        if ($itemsCount > 4 && $itemsCount % 2 !== 0) {
            array_pop($items);
            $itemsCount--;
        }

        jsonResponse('', 'success', [
            'items'      => $this->prepareItemsView($items, 'just-for-you'),
            'itemsCount' => $itemsCount,
        ]);
    }

    public function featured() {
        $current_uri = array_filter(uri()->uri_to_assoc(4));

        checkURI($current_uri, array('page'));
        checkIsValidPage($current_uri['page']);

        $this->breadcrumbs[] = array(
            'link' => get_dynamic_url('items/featured'),
            'title' => translate('featured_items_breadcrumb')
        );

        $page = (int) ($current_uri['page'] ?? 1);
        $per_page = (int) config('item_default_perpage');
        $results_limit = (int) config('env.ENTITIES_RESULT_LIMIT', 10000);

        $countries = model(Country_Model::class)->get_countries();
        $industries = model(Category_Model::class)->getCategories(array('columns' => 'category_id, name', 'parent' => 0));

        if ($page * $per_page > $results_limit) {
            views()->displayWebpackTemplate([
                'sidebarContent' => 'item/featured_products/sidebar_view',
                'cheerupMessage' => translate('max_resuts_exceed', array('[MAX_RESULTS]' => $results_limit)),
                'content'        => 'search/cheerup_view',
                'breadcrumbs'    => $this->breadcrumbs,
                'countries'      => $countries,
                'mainCats'       => $industries
            ]);
            return;
        }

        $links_map = array(
            'page' => array(
                'type' => 'uri',
                'deny' => array('page')
            )
        );

		$links_tpl = uri()->make_templates($links_map, $current_uri);
        $links_tpl_without = uri()->make_templates($links_map, $current_uri, true);

		$featured_items_conditions = array(
			'aggregate_category_counters'	=> true,
			'aggregate_countries_counters'	=> true,
            'featured'                      => 1,
			'sort_by'						=> array('featured_from_date-desc'),
			'per_p' 						=> $per_page,
            'page' 							=> $page,
        );

        model(Elasticsearch_Items_Model::class)->get_items($featured_items_conditions);

		if (!empty($countries_counters = model(Elasticsearch_Items_Model::class)->aggregates['countries'])) {
            $countries_ids = array_keys($countries_counters);
            $search_countries = model(Country_Model::class)->get_simple_countries(implode(",", $countries_ids));
        }

        if (!empty($data_categories = model(Elasticsearch_Items_Model::class)->aggregates['categories'])) {
            $categories_counters = array();

            foreach ($data_categories as $category_tree => $count_items_in_last_category) {
                $categories_ids = explode(',', $category_tree);
                $last_category_id = end($categories_ids);
                $categories_counters[$last_category_id] = $count_items_in_last_category;
            }

            $categories = model(Category_Model::class)->getCategories(array('cat_list' => array_keys($categories_counters)));

			foreach($categories as &$category) {
				$category['counter'] = $categories_counters[$category['category_id']];
            }

			$counter_categories = model(Category_Model::class)->_categories_map($categories);
        }

        $featured_items = $this->formatProductPrice(model(Elasticsearch_Items_Model::class)->items);
        $count_items = model(Elasticsearch_Items_Model::class)->itemsCount;

        if (!empty($featured_items)) {
            $sellers_ids = array_column($featured_items, 'id_seller', 'id_seller');
            $sellers = model(User_Model::class)->getSellersForList(implode(',', $sellers_ids), true);
            $sellers_list = array_column($sellers, null, 'idu');

            foreach ($featured_items as $key => $item) {
                $featured_items[$key]['seller'] = $sellers_list[$item['id_seller']] ?? array();
            }
        }

        // make links template for countries and categories found
		$get_params = array(
            'featured'  => 1,
        );

		if (isset($_GET['lang'])) {
			$get_params['lang'] = $_GET['lang'];
        }

        $get_params = http_build_query($get_params);

        // make pagination
        $paginator_config = array(
            'base_url'      => get_dynamic_url($links_tpl['page'], 'items/featured'),
            'first_url'     => get_dynamic_url($links_tpl_without['page'], 'items/featured'),
			'total_rows'    => $count_items,
			'per_page'      => $per_page,
			'cur_page'		=> $page,
            'replace_url'   => true
        );

        library(TinyMVC_Library_Pagination::class)->initialize($paginator_config);

        /** @var Category_groups_Model $categoryGroupsModel */
        $categoryGroupsModel = model(Category_groups_Model::class);

        $data = [
            'sidebarContent'    => 'new/item/featured_products/sidebar_view',
            'countriesCounters' => $countries_counters,
            'counterCategories' => $counter_categories,
            'searchCountries'   => $search_countries,
            'categoryLink'      => 'category/' . config('replace_uri_template') . '?' . $get_params,
            'categoryGroups'    => $categoryGroupsModel->get_category_groups(),
            'countryLink'       => 'search/country/' . config('replace_uri_template') . '?' . $get_params . '&returnToPage=1',
            'breadcrumbs'       => $this->breadcrumbs,
            'pagination'        => library(TinyMVC_Library_Pagination::class)->create_links(),
            'countries'         => $countries,
            'mainCats'          => $industries,
            'perPage'           => $per_page,
            'page'              => $page,
            'count'             => $count_items,
            'items'             => $featured_items,
            'content'           => 'item/featured_products/index_view',
            'styleCritical'     => 'featured_items',
            'customEncoreLinks' => true,
        ];

        if (logged_in()) {
            $saved_list = model(Items_Model::class)->get_items_saved(id_session());
            $data['savedItems'] = explode(',', $saved_list);
        }

        if ($page > 1) {
            $data['metaParams']['[PAGE]'] = $page;
        }

        views()->displayWebpackTemplate($data);
	}

    /* Used on home page and on item detail */
    public function ajax_get_featured_items() {
        checkIsAjax();

        $request = request()->request;
        $isPromoItems = $request->getBoolean('promoItems') ?: false;
        $perPage = $isPromoItems ? (int) config('promo_items_per_page', 8) : (int) config('home_featured_items_per_page', 12);
        $featuredItems = $this->indexedProductDataProvider->getFeaturedItems(
            $perPage,
            model(User_Model::class)
        );

        if (empty($featuredItems)) {
            jsonResponse('', 'success', ['items' => '']);
        }

        $itemsCount = count($featuredItems);
        if ($itemsCount > 4 && $itemsCount % 2 !== 0) {
            array_pop($featuredItems);
            $itemsCount--;
        }

        jsonResponse('', 'success', [
            'items'      => $this->prepareItemsView($featuredItems, null, false, true, $isPromoItems),
            'itemsCount' => $itemsCount,
        ]);
    }

    public function popular()
    {
        /** @var Country_Model $countryModel */
        $countryModel = model(Country_Model::class);

        /** @var Category_Model $categoryModel */
        $categoryModel = model(Category_Model::class);

        #region current uri
        $app = tmvc::instance();
        $data = [];

        $searchUriComponents = $app->site_urls['items/popular']['replace_uri_components'];
        $currentUri = array_filter($this->uri->uri_to_assoc(4, $app->route_url_segments));

        $perPage = (int) config('item_default_perpage');
        $page = isset($currentUri['page']) ? (int) $currentUri['page'] : 1;
        $configCountItems = (int) config('popular_max_found_count', 50);

        if ($page < 1) {
            show_404();
        }
        #endregion current uri

        /** @var Elasticsearch_Items_Model $esItemModel */
        $esItemModel = model(Elasticsearch_Items::class);

        $esItemModel->get_items([
            'aggregate_category_counters'	=> true,
            'aggregate_countries_counters'	=> true,
            'featured'                      => 0,
            'notOutOfStock'                 => true,
            'sort_by'						=> ['views-desc'],
            'per_p' 						=> $page == ceil($configCountItems / $perPage) ? $configCountItems - (($page - 1) * $perPage) : $perPage,
            'page' 							=> $page,
        ]);

        #region get countries and categories
        $countriesCounters = $esItemModel->aggregates['countries'];
        $searchCountries = [];
        if (!empty($countriesCounters)) {
            $searchCountries = $countryModel->get_simple_countries(implode(",", array_keys($countriesCounters)));
        }

        $categories = $esItemModel->aggregates['categories'];
        $categoryKeys = '';
        $categoriesCounters = [];

        foreach ($categories as $category => $count)
        {
            $explode = explode(',', $category);
            $end = end($explode);
            $categoryKeys .= ',' . $end;
            $categoriesCounters[$end] = $count;
        }

        if (!empty($categoryKeys)) {
            $mysqlCategories = $categoryModel->getCategories(array('cat_list' => substr($categoryKeys, 1)));
            foreach($mysqlCategories as &$mysqlCategory) {
                $mysqlCategory['counter'] = $categoriesCounters[$mysqlCategory['category_id']];
            }
            $counterCategories = $categoryModel->_categories_map($mysqlCategories);
        }
        #endregion get countries and categories

        // check if curent page value is valid

        $count = ($configCountItems < $esItemModel->itemsCount) ? $configCountItems : $esItemModel->itemsCount;
        if (($page > ceil($count / $perPage)) && $page != 1) {
            show_404();
        }

        #region seller info add to items
        $items = $this->formatProductPrice($esItemModel->items);
        $sellersIds = array_column($items, 'id_seller', 'id_seller');

        if (!empty($sellersIds))
        {
            /** @var User_Model $userModel */
            $userModel = model(User_Model::class);

            $sellers = $userModel->getSellersForList(implode(',', $sellersIds), true);
            $sellersList = arrayByKey($sellers, 'idu');

            foreach ($items as $key => $item) {
                $items[$key]['seller'] = empty($sellersList[$item['id_seller']]) ? [] : $sellersList[$item['id_seller']];
            }
        }
        #endregion seller info add to items

        $linksMap = [
            $searchUriComponents['page'] => [
                'type' => 'uri',
                'deny' => [$searchUriComponents['page']],
            ],
        ];

        $linksTplWithout = $this->uri->make_templates($linksMap, $currentUri, true);
        $linksTpl = $this->uri->make_templates($linksMap, $currentUri);

        // make links template for countries and categories found
        $getParams['sort_by'] = 'views-desc';
        $lang = request()->query->get('lang');
        if (isset($lang)) {
            $getParams['lang'] = $lang;
        }

        $getParams = http_build_query($getParams);

        #region pagination
        $paginatorConfig = array(
            'base_url'      => get_dynamic_url($linksTpl[$searchUriComponents['page']], 'items/popular'),
            'first_url'     => get_dynamic_url($linksTplWithout[$searchUriComponents['page']], 'items/popular'),
            'total_rows'    => $count,
            'per_page'      => $perPage,
            'cur_page'		=> $page,
            'replace_url'   => true
        );

        library(TinyMVC_Library_Pagination::class)->initialize($paginatorConfig);
        #endregion pagination

        $this->breadcrumbs[] = array('link' => get_dynamic_url('items/popular'), 'title' => translate('most_popular_items_breadcrumb'));

        /** @var Category_groups_Model $categoryGroupsModel */
        $categoryGroupsModel = model(Category_groups_Model::class);

        $data = [
            'sidebarContent'    => 'new/item/most_popular_products/sidebar_view',
            'content'           => 'item/most_popular_products/index_view',
            'countriesCounters' => $countriesCounters,
            'counterCategories' => $counterCategories,
            'searchCountries'   => $searchCountries,
            'categoryLink'      => 'category/' . config('replace_uri_template') . '?' . $getParams,
            'countryLink'       => 'search/country/' . config('replace_uri_template') . '?' . $getParams . '&returnToPage=1',
            'categoryGroups'    => $categoryGroupsModel->get_category_groups(),
            'breadcrumbs'       => $this->breadcrumbs,
            'pagination'        => library(TinyMVC_Library_Pagination::class)->create_links(),
            'countries'         => $countryModel->get_countries(),
            'mainCats'          => $categoryModel->getCategories([
                'parent'  => 0,
                'columns' => 'category_id, name, parent',
            ]),
            'perPage'           => $perPage,
            'page'              => $page,
            'count'             => $count,
            'items'             => $items,
            'styleCritical'     => 'popular_items',
            'customEncoreLinks' => true,
        ];

        ($page <= 1) ?: $data['metaParams']['[PAGE]'] = $page;

        if (logged_in()) {

            /** @var Items_Model $itemsModel */
            $itemsModel = model(Items_Model::class);

            $savedList = $itemsModel->get_items_saved(id_session());
            $data['savedItems'] = explode(',', $savedList);
        }

        views()->displayWebpackTemplate($data);
	}

    /* Used on home page and on item detail page */
    public function ajax_get_popular_items() {
        checkIsAjax();

        $request = request()->request;
        $isPromoItems = $request->getBoolean('promoItems') ?: false;
        $perPage = $isPromoItems ? (int) config('promo_items_per_page', 8) : (int) config('home_popular_items_per_page', 12);
        $popularItems = $this->indexedProductDataProvider->getPopularItems($perPage);
        $itemsCount = count($popularItems);

        if ($itemsCount > 4 && $itemsCount % 2 !== 0) {
            array_pop($popularItems);
            $itemsCount--;
        }

        jsonResponse('', 'success', [
            'items'      => $this->prepareItemsView($popularItems, null, false, false, $isPromoItems),
            'itemsCount' => $itemsCount,
        ]);
    }

    /* Used on home page */
    public function ajax_get_items_compilations() {
        checkIsAjax();

        /** @var Items_Compilation_Model $itemsCompilationModel */
        $itemsCompilationModel = model(Items_Compilation_Model::class);

        /** @var Elasticsearch_Items_Model $elasticsearchItemsModel */
        $elasticsearchItemsModel = model(Elasticsearch_Items_Model::class);

        $itemsCompilations = $itemsCompilationModel->findAllBy([
            'conditions'    => [
                'isPublished'   => 1,
            ],
            'with'          => ['itemsRelations'],
            'limit'         => 3,
        ]);

        if (empty($itemsCompilations)) {
            jsonResponse('', 'success', ['itemsCompilations' => []]);
        }

        foreach ($itemsCompilations as $key => $itemsCompilation) {
            if (null === ($itemsCompilation['items_relations'] ?? null)) {
                continue;
            }

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
                        'image'     => $item['photo_name'],
                    ];
                }
            }
        }

        jsonResponse('', 'success', ['itemsCompilations' => views()->fetch('new/home/components/ajax/exclusive_deals_view', compact('itemsCompilations'))]);
    }

    /* Used on item detail page */
    public function ajax_get_similar_items() {
        checkIsAjax();

        $request = request()->request;

        if (!empty($itemId = $request->getInt('item')) && !empty($categoryId = $request->getInt('category'))) {
            $similarItems = $this->indexedProductDataProvider->getPreviewItems($itemId, $categoryId, 8);
            $itemsCount = count($similarItems);


            if ($itemsCount > 4 && $itemsCount % 2 !== 0) {
                array_pop($similarItems);
                $itemsCount--;
            }
        }

        jsonResponse('', 'success', [
            'items'      => $this->prepareItemsView($similarItems, null, false, false),
            'itemsCount' => $itemsCount,
        ]);
    }

    /* Used on item detail page */
    public function ajax_get_all_promo_items() {
        checkIsAjax();

        $mostPopularItems = $this->indexedProductDataProvider->getItemsByCriteria(
            [
                'featured'      => false,
                'notOutOfStock' => true,
                'per_p'         => (int) config('promo_items_per_page', 8),
                'sort_by'       => ['views-desc']
            ]
        );

        $latestItems = $this->indexedProductDataProvider->getItemsByCriteria(
            [
                'featured'      => false,
                'notOutOfStock' => true,
                'per_p'         => (int) config('promo_items_per_page', 8),
                'sort_by'       => ['create_date-desc']
            ]
        );

        $featuredItems = $this->indexedProductDataProvider->getItemsByCriteria(
            [
                'featured'      => true,
                'notOutOfStock' => true,
                'per_p'         => (int) config('promo_items_per_page', 8),
                'sort_by'       => ['featured_from_date-desc']
            ]
        );

        $productsType = [
            'featured' => [
                'title'     => translate('item_detail_featured_items_slider_ttl_new'),
                'desc'      => translate('item_detail_featured_items_slider_desc'),
                'img'       => 'featured_items_bg',
                'products'  => $featuredItems,
                'btn_url'   => __SITE_URL . 'items/featured',
                'slider_wr' => 'js-featured-items-slider-wr',
                'name'      => 'featured-items',
            ],
            'popular' => [
                'title'     => translate('item_detail_popular_items_slider_ttl_new'),
                'desc'      => translate('item_detail_popular_items_slider_desc'),
                'img'       => 'popular_items_bg',
                'products'  => $mostPopularItems,
                'btn_url'   => __SITE_URL . 'items/popular',
                'slider_wr' => 'js-popular-items-slider-wr',
                'name'      => 'popular-items',
            ],
            'latest' => [
                'title'     => translate('item_detail_latest_items_slider_ttl_new'),
                'desc'      => translate('item_detail_latest_items_slider_desc'),
                'img'       => 'latest_items_bg',
                'products'  => $latestItems,
                'btn_url'   => __SITE_URL . 'items/latest',
                'slider_wr' => 'js-latest-items-slider-wr',
                'name'      => 'latest-items',
            ],
        ];

        /** @var Items_Model $itemsModel */
        $itemsModel = model(Items_Model::class);

        $savedList = $itemsModel->get_items_saved(id_session());

        jsonResponse('', 'success', [
            'items' => views()->fetch('new/item/promo_products_slider_item_view', [
                'productsType' => $productsType,
                'savedItems'   => explode(',', $savedList),
            ]),
        ]);
    }

    public function add() {
        checkIsAjax();
        checkIsLoggedAjaxModal();
        checkHaveCompanyAjaxModal();
        checkPermisionAjaxModal('manage_personal_items');

        $this->_load_main(); /* load main models */

        $uri_assoc = $this->uri->uri_to_assoc(2);

        //region categories all
        $company_categories = model('company')->get_relation_industry_by_id(my_company_id(), true);

        $industrie_all = array();
        $categories_model = model('elasticsearch_category');
        $categories_model->get_categories(array('parent' => array(0), 'sort_by' => 'name_asc'));
        $industrie_all = $categories_model->categories_records;

        $data['prepared_categories_all'] = $this->_prepared_categories_all($industrie_all, $company_categories);
        //endregion categories all

        $this->load->model('Country_Model', 'country');

        // Form action
        $data['action'] = getUrlForGroup("items/ajax_item_operation/add_item");

        //Edit product
        $id_item = id_from_link($this->uri->segment(3));

        //region Adress
        $data['company_info'] = model('company')->get_seller_base_company(privileged_user_id());
        $company_location = model('company')->get_company_location($data['company_info']['id_company']);

        $address_parts = array_filter(array(
            $company_location['country'] ?? null,
            $company_location['region'] ?? null,
            $company_location['city'] ?? null,
            $company_location['zip_company'] ?? null,
        ));
        $data['address'] = !empty($address_parts) ? implode(', ', $address_parts) : null;
        //endregion Adress

        if($id_item){
            checkPermisionAjaxModal('edit_item');

            // Form action
            $data['action'] = getUrlForGroup("items/ajax_item_operation/edit_item");
            $data['item'] = $this->items->get_item($id_item);
            if (!is_privileged('user', $data['item']['id_seller'], 'manage_personal_items')) {
                messageInModal(translate('systmess_error_invalid_data'), 'errors');
            }

            //region categories selected
            $product_categories = empty($data['item']['item_categories']) ? [] : explode(',', $data['item']['item_categories']);
            $data['product_categories'] = $this->_prepared_selected_categories($product_categories);
            //endregion categories selected

            $this->load->model('User_Model', 'user');
            $data['user_main'] = $this->user->getUser($data['item']['id_seller']);
            $data['category'] = $id_category = $data['item']['id_cat'] ?? 0;

            $data['cat_names'] = $this->category->get_cat_names($id_category, 'parents', ' <i class="ep-icon ep-icon_arrows-right fs-10 lh-14"></i> ');
            $data['currencies'] = $this->items->get_currency();
            $data['cat_option'] = $this->category->get_category($id_category);
            $data['item']['vin'] = $this->items->get_vin($id_item);

            $data['item']['attrs'] = $this->items->get_cat_attrs($id_item);
            if ($data['cat_option']['vin'] == 1)
                $data['vin_info'] = $this->items->get_vin_info($id_item);

            /* categories attributes */
            $attributes = $this->catattr->get_attributes($id_category);
            if(!empty($attributes)){
                $attributes_keys = array_keys($attributes);
                $attributes_values = $this->catattr->get_attr_values(implode(',', $attributes_keys));

                foreach ($attributes as $key => $attribute) {
                    foreach ($attributes_values as $value) {
                        if ($value['attribute'] == $key)
                            $attributes[$key]['attr_values'][] = $value;
                    }
                }
                $data['attributes'] = $attributes;
            }

            //region Sizes
            if (!empty($data['item']['size'])) {
                list($length, $width, $height) = explode('x', $data['item']['size']);
                $data['item']['item_length'] = $data['item']['item_length'] ?? $length ?? 0;
                $data['item']['item_width'] = $data['item']['item_width'] ?? $width ?? 0;
                $data['item']['item_height'] = $data['item']['item_height'] ?? $height ?? 0;
            }
            //endregion Sizes

            //user attributes
            $data['item']['u_attr'] = arrayByKey($this->items->get_user_attrs($id_item), 'id');

            //locations
            if (!empty($data['item']['p_country'])){
                $data['states'] = $this->country->get_states($data['item']['p_country']);
                $data['city_selected'] = $this->country->get_city($data['item']['p_city']);
            }

            $data['exchange_rate'] = json_decode(file_get_contents('current_exchange_rate.json'), true);

            //region Fileupload options
            /** @var Products_Photo_Model $productsPhotoModel */
            $productsPhotoModel = model(Products_Photo_Model::class);

            $data['photos'] = $productsPhotoModel->findAllBy([
                'scopes' => [
                    'itemId' => $id_item,
                ],
                'order' => ['id' => 'ASC']
            ]);

            $data['photo_main'] = [];

            foreach($data['photos'] as $photos_key => $photos_item){
                if ((int)$photos_item['main_photo'] > 0) {
                    $photos_item['photo_url'] = getDisplayImageLink(array('{ID}' => $id_item, '{FILE_NAME}' => $photos_item['photo_name']), 'items.photos', array('thumb_size' => 1));
                    $data['photo_main'] = $photos_item;
                    unset($data['photos'][$photos_key]);

                    continue;
                }

                $data['photos'][$photos_key]['photo_url'] = getDisplayImageLink(array('{ID}' => $id_item, '{FILE_NAME}' => $photos_item['photo_name']), 'items.photos', array('thumb_size' => 1));
                $data['photos'][$photos_key]['orig_url'] = getDisplayImageLink(array('{ID}' => $id_item, '{FILE_NAME}' => $photos_item['photo_name']), 'items.photos', array('thumb_size' => 4));
            }

            $module_main ='items.main';
            $mime_main_properties = getMimePropertiesFromFormats(config("img.{$module_main}.rules.format"));

            if(!empty($data['photo_main']['photo_name'])){
                $link_main_image = getDisplayImageLink(array('{ID}' => $id_item, '{FILE_NAME}' => $data['photo_main']['photo_name']), $module_main);
                $link_thumb_main_image = getDisplayImageLink(array('{ID}' => $id_item, '{FILE_NAME}' => $data['photo_main']['photo_name']), $module_main, array('thumb_size' => 2));
            }else{
                $link_main_image = $link_thumb_main_image = __SITE_URL.'public/img/no_image/group/main-image.svg';
            }

            $data['fileupload_crop'] = [
                'class_preview'		 	    => 'cropper-preview--product',
                'preview_fanacy'		    => false,
                'crop_img_height'		    => 900,
                'link_main_image'           => $link_main_image,
                'link_thumb_main_image'     => $link_thumb_main_image,
                'title_text_popup'          => 'Main image',
                'btn_text_save_picture'     => 'Set new main image',
                'croppper_limit_by_min'     => true,
                'rules'                     => config("img.{$module_main}.rules"),
                'url'                       => ['upload' => __SITE_URL . 'items/ajax_item_upload_main_photo'],
                'accept'                    => arrayGet($mime_main_properties, 'accept'),
            ];

            $module_photos ='items.photos';
            $mime_photos_properties = getMimePropertiesFromFormats(config("img.{$module_photos}.rules.format"));

            $data['fileupload_photos'] = [
                'limits'    => [
                    'amount' => [
                        'total'   => (int) config("img.{$module_photos}.limit"),
                        'current' => count($data['photos']),
                    ],
                    'accept'            => arrayGet($mime_photos_properties, 'accept'),
                    'formats'           => arrayGet($mime_photos_properties, 'formats'),
                    'mimetypes'         => arrayGet($mime_photos_properties, 'mimetypes'),
                ],
                'rules' => config("img.{$module_photos}.rules"),
                'url'   => [
                    'upload' => __SITE_URL . 'items/ajax_item_upload_images',
                    'delete' => __SITE_URL . 'items/ajax_add_item_delete_photo',
                ],
            ];

            //endregion Fileupload options

            $address_parts_seller = array_filter(array(
                'country'       => $company_location['country_id'] ?? null,
                'state'         => $company_location['region_id'] ?? null,
                'city'          => $company_location['city_id'] ?? null,
                'postal_code'   => $company_location['zip_company'] ?? null,
            ));

            $address_parts_item = array_filter(array(
                'country'       => $data['item']['p_country'] ?? null,
                'state'         => $data['item']['state'] ?? null,
                'city'          => $data['item']['p_city'] ?? null,
                'postal_code'   => $data['item']['item_zip'] ?? null,
            ));

            $data['other_address_input_value'] = null;
            $data['other_address_data'] = null;
            if (count(array_intersect($address_parts_item, $address_parts_seller)) !== 4) {
                $item_location = $this->items->get_item_location($id_item);

                $address_parts_item = array_filter(array(
                    $item_location['country'] ?? null,
                    $item_location['region'] ?? null,
                    $item_location['city'] ?? null,
                    $item_location['item_zip'] ?? null,
                ));
                $data['other_address_input_value'] = !empty($address_parts_item) ? implode(', ', $address_parts_item) : null;

                $data['other_location'] = array(
                    'country' => array(
                        'value' => $item_location['country_id'],
                        'name'  => $item_location['country'],
                    ),
                    'state' => array(
                        'value' => $item_location['region_id'],
                        'name'  => $item_location['region'],
                    ),
                    'city' => array(
                        'value' => $item_location['city_id'],
                        'name'  => $item_location['city'],
                    ),
                    'postal_code' => array(
                        'value' => $item_location['item_zip'],
                        'name'  => $item_location['item_zip'],
                    )
                );
            }

            $data['item_description'] = model('Items_Descriptions')->get_descriptions_by_item($id_item);

            if(!empty($data['item_description'])){

                if($data['item_description']['status'] == 'removed'){
                    $data['item_description'] = json_encode(
                                                    array(
                                                        'status'    => $data['item_description']['status'],
                                                        'translate' => $data['item_description']['need_translate']
                                                    )
                                                );
                }else{
                    $data['item_description'] = json_encode(
                        array(
                            'languageName'  => $data['item_description']['lang_name'],
                            'language'      => $data['item_description']['descriptions_lang'],
                            'description'   => $data['item_description']['item_description'],
                            'translate'     => $data['item_description']['need_translate'],
                            'status'        => $data['item_description']['status'],
                        )
                    );
                }
            }

            /* variants */
            /** @var Items_Variants_Model $itemsVariantsModel */
            $itemsVariantsModel = model(Items_Variants_Model::class);
            $itemVariants = $itemsVariantsModel->getItemVariants((int) $id_item, true);
            foreach ($itemVariants['properties'] as $propertiesKey => $propertiesItem) {
                $itemVariants['properties'][$propertiesKey]['property_options'] = null === $propertiesItem['property_options'] ? [] : $propertiesItem['property_options']->toArray();
            }

            foreach ($itemVariants['variants'] as $itemVariantsKey => $itemVariantsItem) {
                $itemVariants['variants'][$itemVariantsKey]['final_price'] = moneyToDecimal($itemVariantsItem['final_price']);
                $itemVariants['variants'][$itemVariantsKey]['price'] = moneyToDecimal($itemVariantsItem['price']);
                $itemVariants['variants'][$itemVariantsKey]['property_options'] = null === $itemVariantsItem['property_options'] ? [] : $itemVariantsItem['property_options']->toArray();

                $imgVariants = [
                    'class' => '',
                    'name'  => 'main',
                    'src'   => __SITE_URL.'public/img/no_image/group/main-image.svg',
                ];

                if(isset($itemVariantsItem['image']) && !empty($itemVariantsItem['image'])){
                    if($itemVariantsItem['image'] === 'main'){
                        $imgVariants['class'] = 'js-add-item-change-main-photo';

                        if(!empty($data['photo_main']['photo_name'])){
                            $imgVariants['src'] = $link_main_image;
                        }
                    } else {
                        $imgVariants['name'] = $itemVariantsItem['image'];
                        $imgVariants['src'] = getDisplayImageLink(['{ID}' => $id_item, '{FILE_NAME}' => $itemVariantsItem['image']], 'items.photos', ['thumb_size' => 1]);
                    }
                }

                $itemVariants['variants'][$itemVariantsKey]['img'] = $imgVariants;
            }
            $data['itemVariants'] = $itemVariants;
        } else{
            //region categories selected
            $get_category_id = intval($uri_assoc['category']);

            if(!empty($get_category_id)){
                $category_info = model('category')->get_category($get_category_id);
                if(!empty($category_info)){
                    $get_categories = json_decode('['.$category_info['breadcrumbs'].']', true);
                    $product_categories = array();

                    foreach($get_categories as $get_categories_item){
                        $product_categories[] = array_keys($get_categories_item)[0];
                    }

                    $data['product_categories'] = $this->_prepared_selected_categories($product_categories);
                }
            }
            //endregion categories selected

            //region Fileupload options
            $module_main ='items.main';
            $mime_main_properties = getMimePropertiesFromFormats(config("img.{$module_main}.rules.format"));

            $data['fileupload_crop'] = [
                'class_preview'		 	    => 'cropper-preview--product',
                'link_main_image'           => __SITE_URL.'public/img/no_image/group/main-image.svg',
                'link_thumb_main_image'     => __SITE_URL.'public/img/no_image/group/main-image.svg',
                'title_text_popup'          => 'Main image',
                'btn_text_save_picture'     => 'Set new main image',
                'croppper_limit_by_min'     => true,
                'rules'                     => config("img.{$module_main}.rules"),
                'url'                       => ['upload' => __SITE_URL . 'items/ajax_item_upload_main_photo'],
                'accept'                    => arrayGet($mime_main_properties, 'accept'),
            ];

            $module_photos ='items.photos';
            $mime_photos_properties = getMimePropertiesFromFormats(config("img.{$module_photos}.rules.format"));

            $data['fileupload_photos'] = [
                'limits'    => [
                    'amount'    => [
                        'total'     => (int) config("img.{$module_photos}.limit"),
                        'current'   => 0,
                    ],
                    'accept'            => arrayGet($mime_photos_properties, 'accept'),
                    'formats'           => arrayGet($mime_photos_properties, 'formats'),
                    'mimetypes'         => arrayGet($mime_photos_properties, 'mimetypes'),
                ],
                'rules'     => config("img.{$module_photos}.rules"),
                'url'       => [
                    'upload' => __SITE_URL . 'items/ajax_item_upload_images',
                    'delete' => __SITE_URL . 'items/ajax_add_item_delete_photo',
                ],
            ];
            //endregion Fileupload options
        }
        //EOF Edit product

        $data['u_types'] = $this->items->get_unit_types();
        $data['port_country'] = $this->country->fetch_port_country();

        $this->load->model('Text_block_Model', 'text_block');
        $block_info_list = array('about_tag_info', 'what_is_add_to_basket', 'featured_product', 'highlighted_product');
        if(have_right('create_sample_order')){
            $block_info_list[] = 'what_is_sample_order';
        }
        if(have_right('manage_seller_offers')){
            $block_info_list[] = 'what_is_offer';
        }
        if(have_right('manage_seller_estimate')){
            $block_info_list[] = 'what_is_estimate';
        }
        if(have_right('manage_seller_inquiries')){
            $block_info_list[] = 'what_is_inquiry';
        }
        if(have_right('manage_seller_po')){
            $block_info_list[] = 'what_is_producing_request';
        }
        $data['block_info'] = arrayByKey($this->text_block->get_text_block_by_shortnames($block_info_list),'short_name');

        $data['block_info']['hr_tariff_number'] = model('user_guide')->get_user_guide_by_alias('hr_tariff_number');
        $data['validation'] = $this->getItemValidationMetadata();
        $data['maxProperties'] = [
            'elements'           => (int) config('item_max_variation_values', 5),
            'options'            => (int) config('item_max_variation_option', 5),
            'optionCharacters'   => (int) config('item_max_variation_option_characters', 30),
            'propertyCharacters' => (int) config('item_variant_property_max_length', 255),
        ];

        $this->view->assign($data);
        $this->view->display('new/item/add_item/popup_form_view');
    }

    function ajax_select_category() {
        checkIsAjax();
        checkIsLoggedAjax();

        $request = request();

        if (empty($request->request->get('category'))) {
            jsonResponse('You should be select category.');
        }

        /** @var Category_Model $categoryModel */
        $categoryModel = model(Category_Model::class);

        $categoryId = $request->request->getInt('category');
        if (empty($category = $categoryModel->get_category($categoryId))) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }

        $data = [];
        $operation = uri()->segment(3);

        switch ($operation) {
            case 'search':
                $categoryBreadcrumbs = json_decode('[' . $category['breadcrumbs'] . ']', true);
                $categoriesIds = [];

                foreach($categoryBreadcrumbs as $categoryCrumb){
                    $categoriesIds[] = array_keys($categoryCrumb)[0];
                }

                $data['product_categories'] = $this->_prepared_selected_categories($categoriesIds);
            break;
            case 'one':
                /** @var Elasticsearch_Category_Model $elasticsearchCategoryModel */
                $elasticsearchCategoryModel = model(Elasticsearch_Category_Model::class);

                $elasticsearchCategoryModel->get_categories(['parent' => [$categoryId], 'sort_by' => 'name_asc']);
                $parentCategories = $elasticsearchCategoryModel->categories_records;

                $preparedCategories = [];
                if (!empty($parentCategories)) {
                    foreach ($parentCategories as $parentCategory){
                        $preparedCategories[$parentCategory['category_id']] = [
                            'category_id'   => $parentCategory['category_id'],
                            'children'      => $parentCategory['has_children'],
                            'isAdult'       => $parentCategory['is_restricted'],
                            'name'          => $parentCategory['name'],
                        ];

                        if ((int) $parentCategory['has_vin'] > 0){
                            $preparedCategories[$parentCategory['category_id']]['has_vin'] = $parentCategory['has_vin'];
                        }
                    }
                }

                $data = [
                    'list' => $preparedCategories,
                    'vin' => $category['vin']
                ];

            break;
        }

        jsonResponse('', 'success', $data);
    }

    private function _prepared_categories_all($categories_all = array(), $company_categories = array()){
        $selected = array();
        $prepared_categories = array(
            'selected' => array(),
            'all' => array(),
        );

        foreach($company_categories as $company_categories_item){
            $selected[] = $company_categories_item['category_id'];
        }

        foreach($categories_all as $categories_all_item){
            $name_array = 'all';

            if(in_array($categories_all_item['category_id'], $selected)){
                $name_array = 'selected';
            }

            $prepared_categories[$name_array][$categories_all_item['category_id']] = array(
                'category_id' => $categories_all_item['category_id'],
                'name' => $categories_all_item['name'],
                'children' => $categories_all_item['has_children'],
                'isAdult' => $categories_all_item['is_restricted'],
            );

            if((int)$categories_all_item['has_vin'] > 0){
                $prepared_categories[$name_array][$categories_all_item['category_id']]['has_vin'] = $categories_all_item['has_vin'];
            }
        }

        return $prepared_categories;
    }

    private function _prepared_selected_categories($product_categories){
        $prepared_categories = array(
            'first' => 0,
            'last'  => 0,
            'list'  => array(),
        );

        if(!empty($product_categories)){
            $prev_product_categories_id = 0;
            $temp_product_categories = array();
            $last_id = end($product_categories);

            foreach($product_categories as $product_categories_item){
                $parent_categories = array();
                $categories_model = model('elasticsearch_category');
                $categories_model->get_categories(array('parent' => array($product_categories_item), 'sort_by' => 'name_asc'));
                $parent_categories = $categories_model->categories_records;

                if($prev_product_categories_id != 0){
                    $prepared_categories['list'][$product_categories_item] = $temp_product_categories;
                }else{
                    $prepared_categories['first'] = $product_categories_item;
                }

                if(!empty($parent_categories)){
                    $prev_product_categories_id = $product_categories_item;

                    $temp_product_categories = array();
                    foreach($parent_categories as $parent_categories_item){
                        $temp_product_categories[$parent_categories_item['category_id']] = array(
                            'category_id' => $parent_categories_item['category_id'],
                            'name' => $parent_categories_item['name'],
                            'children' => $parent_categories_item['has_children'],
                        );

                        if((int)$parent_categories_item['has_vin'] > 0){
                            $temp_product_categories[$parent_categories_item['category_id']]['has_vin'] = $parent_categories_item['has_vin'];
                        }
                    }

                    if($last_id == $product_categories_item){
                        $prepared_categories['list']['last'] = $temp_product_categories;
                    }

                }else{
                    $prepared_categories['last'] = $product_categories_item;
                }
            }
        }

        return $prepared_categories;
    }

    function choose_category() {
        if (!logged_in()){
            headerRedirect(__SITE_URL . 'login');
        }

        if (!have_right('manage_personal_items')) {
            $this->session->setMessages(translate("systmess_error_rights_perform_this_action"), 'errors');
            headerRedirect(__SITE_URL);
        }

        if (!i_have_company()) {
            $this->session->setMessages(translate("systmess_error_must_have_company_to_access_page"), 'errors');
            headerRedirect();
        }

        headerRedirect(__SITE_URL . 'items/my?popup_add=open');
    }

    function ajax_saveproduct_operations(){
        checkIsAjax();
        checkIsLoggedAjax();

        $this->load->model('Items_Model', 'items');
        $item = (int) $_POST['product'];

        if (!$this->items->item_exist($item)) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }

        $op = $this->uri->segment(3);
        switch($op) {
            case 'remove_product_saved':
                is_allowed("freq_allowed_saved");

                if (!$this->items->iSaveIt(id_session(), $item)) {
                    jsonResponse(translate('systmess_error_remove_product_saved_not_saved_item'));
                }

                if (!$this->items->deleteSavedItemByUser($this->session->id, $item)){
                    jsonResponse(translate('systmess_internal_server_error'));
                }

                jsonResponse(translate('systmess_succes_remove_product_saved'), 'success');
            break;
            case 'add_product_saved':
                is_allowed("freq_allowed_saved");

                if ($this->items->iSaveIt(id_session(), $item)) {
                    jsonResponse(translate('systmess_error_add_product_saved_already_saved_item'));
                }

                if (!$this->items->setSavedItem(id_session(), $item)) {
                    jsonResponse(translate('systmess_internal_server_error'));
                }

                jsonResponse(translate('systmess_succes_add_product_saved'), 'success');

            break;
        }
    }

    public function ajax_get_saved() {
        checkIsAjax();
        checkIsLoggedAjax();

        /** @var Items_Variants_Model $itemsVariantsModel */
        $itemsVariantsModel = model(Items_Variants_Model::class);

        $data['per_page'] = 8;
        $data['curr_page'] = $page = (int) $_POST['page'];
        $data['counter'] = model('items')->getSavedCounter(id_session());

        $data['items'] = array_map(function($item) {
            $item['slug'] = strForURL("{$item['title']} {$item['id_item']}");
            $item['url'] = __SITE_URL . "item/{$item['slug']}";
            $item['reviews_url'] = __SITE_URL . "reviews/modal_by_item/{$item['id_item']}";
            $item_img_link = getDisplayImageLink(array('{ID}' => $item['id_item'], '{FILE_NAME}' => $item['photo_name']), 'items.main', array( 'thumb_size' => 1 ));
            $item['image'] = $item_img_link;

            return $item;
        }, model('items')->getSavedByUser($this->session->id, array('page' => $page, 'per_p' => $data['per_page'])));

        foreach ((array) $data['items'] as $key => $item) {
            if ($item['has_variants']) {
                $minPrice = $maxPrice = $minFinalPrice = $maxFinalPrice = null;

                $itemVariants = $itemsVariantsModel->findAllBy([
                    'conditions'    => [
                        'itemId'    => (int) $item['id_item'],
                    ],
                ]);

                foreach ($itemVariants as $itemVariant) {
                    if (null === $minFinalPrice || $itemVariant['final_price']->lessThan($minFinalPrice)) {
                        $minFinalPrice = $itemVariant['final_price'];
                    }

                    if (null === $maxFinalPrice || $itemVariant['final_price']->greaterThan($maxFinalPrice)) {
                        $maxFinalPrice = $itemVariant['final_price'];
                    }

                    if (!empty($itemVariant['discount'])) {
                        if (null === $minPrice || $itemVariant['price']->lessThan($minPrice)) {
                            $minPrice = $itemVariant['price'];
                        }

                        if (null === $maxPrice || $itemVariant['price']->greaterThan($maxPrice)) {
                            $maxPrice = $itemVariant['price'];
                        }
                    }
                }

                //if the price is greater than 9999, then we remove the decimals of the price
                if (!empty($minFinalPrice)) {
                    $minFinalPrice = (float) moneyToDecimal($minFinalPrice);
                    $data['items'][$key]['card_prices']['min_final_price'] = (int) ($minFinalPrice / 10_000) >= 1 ? (int) $minFinalPrice : (float) $minFinalPrice;
                }

                if (!empty($maxFinalPrice)) {
                    $maxFinalPrice = (float) moneyToDecimal($maxFinalPrice);
                    if ($maxFinalPrice != $minFinalPrice) {
                        $data['items'][$key]['card_prices']['max_final_price'] = (int) ($maxFinalPrice / 10_000) >= 1 ? (int) $maxFinalPrice : (float) $maxFinalPrice;
                    }
                }

                if (!empty($minPrice)) {
                    $minPrice = (float) moneyToDecimal($minPrice);
                    $data['items'][$key]['card_prices']['min_price'] = (int) ($minPrice / 10_000) >= 1 ? (int) $minPrice : (float) $minPrice;
                }

                if (!empty($maxPrice)) {
                    $maxPrice = (float) moneyToDecimal($maxPrice);
                    if ($maxPrice != $minPrice) {
                        $data['items'][$key]['card_prices']['max_price'] = (int) ($maxPrice / 10_000) >= 1 ? (int) $maxPrice : (float) $maxPrice;
                    }
                }
            } else {
                //if the price is greater than 9999, then we remove the decimals of the price
                $data['items'][$key]['card_prices']['min_final_price'] = (float) $item['final_price'];

                if (!empty($item['discount'])) {
                    $data['items'][$key]['card_prices']['min_price'] = (float) $item['price'];
                }
            }
        }

        jsonResponse(
            views()->fetch('new/nav_header/saved/product_saved_list_view', $data),
            'success',
            ['counter' => $data['counter']]
        );
    }

    private function check_is_visible_item_page() {
        $this->id_item = id_from_link($this->uri->segment(3));
        if (
                $this->id_item == 0
                || !model('items')->check_item($this->id_item)
                || empty($this->item = model('items')->get_item($this->id_item))
            )
        {
            show_404();
        }

        if (
            1 === (int) $this->item['fake_user']
            && !(
                    is_privileged('user', (int) $this->item['id_seller'])
                    || have_right('moderate_content')
                )
            )
        {
            show_404();
        }

        if ($this->item['draft'] && id_session() != $this->item['id_seller']) {
            show_403();
        }

        if(1 === (int) $this->item['fake_user']){
            header("X-Robots-Tag: noindex");
        }

        $isNotBlocked = 0 === (int) $this->item['blocked'];
        $isVisible = filter_var((int) $this->item['visible'], FILTER_VALIDATE_BOOLEAN);
        $isModerated = filter_var((int) $this->item['moderation_is_approved'], FILTER_VALIDATE_BOOLEAN);
        $isActiveAccount = 'active' === $this->item['user_status'];
        if(
            !(
                $isNotBlocked && $isVisible && $isModerated && $isActiveAccount
                || is_privileged('user', (int) $this->item['id_seller'])
                || have_right('moderate_content')
            )
        ){
            show_blocked();
        }

        if (strForURL("{$this->item['title']} $this->id_item") !== uri()->segment(3)) {
            headerRedirect(makeItemUrl($this->id_item, $this->item['title']), 301);
        }
    }

    private function get_item_detail_main_data() {
        $itemId = $id_item = (int) $this->id_item;
        $item = $this->item;
        $not_cached_data = $cached_data = [];

        /** @var Company_Model $companyModel */
        $companyModel = model(Company_Model::class);

        /** @var Items_Model $itemModel */
        $itemModel = model(Items_Model::class);

        /** @var Products_Model $productsModel */
        $productsModel = model(Products_Model::class);

        if (!is_privileged('user', $item['id_seller'])) {
            if (null === session()->get("is_viewed_{$itemId}")) {
                session()->set("is_viewed_{$itemId}", 1);

                $itemModel->increment_views($itemId);

                /** @var Elasticsearch_Items_Model $elasticsearchItemsModel */
                $elasticsearchItemsModel = model(Elasticsearch_Items_Model::class);
                $elasticsearchItemsModel->incrementViews($itemId);

                /** @var MessengerInterface $messenger */
                $messenger = container()->get(MessengerInterface::class);
                $messenger->bus('command.bus')->dispatch(
                    new SaveViewItemsLog(
                        $itemId,
                        id_session() ?: null,
                    )
                );
            }
        }

        if (__CACHE_ENABLE) {
            $c_config = model('cache_config')->get_cache_options('item');

            if ( ! empty($c_config) && $c_config['enable']) {
                library('cache')->init(array('securityKey' => $c_config['folder']));
                $cached_data = library('cache')->get('item_general_' . $id_item);
            }
        }

        //region cached data
        if (empty($cached_data)) {
            $this->breadcrumbs[] = array(
                'link' => __SITE_URL . 'categories',
                'title' => 'All categories',
            );

            $crumbs = model('category')->breadcrumbs($item['id_cat']);
            $last_category_child = end($crumbs);
            $item_location = model('country')->get_country_city($item['p_country'], $item['p_city']);
            $seller_company = $companyModel->get_company(array('id_user' => $item['id_seller']));

            $companyType = $companyModel->get_company_type((int) $seller_company['id_type']);
            if (!empty($companyType['group_name_suffix'])) {
                $seller_company['group_name_suffix'] = $companyType['group_name_suffix'];
            }

            //region photos
            $photos = array_column($itemModel->get_items_photo($id_item, null, 'id ASC'), null, 'photo_name');
            $photo_main = array();

            foreach($photos as $photos_key => &$photos_item){
                $photoPath = getDisplayImagePath(['{ID}' => $item['id'], '{FILE_NAME}' => $photos_item['photo_name']], 'items.photos');
                list($width, $height) = getimagesize($photoPath) ?: [737, 737];
                $photos_item['width'] = $width;
                $photos_item['height'] = $height;

                if (empty($photo_main) && (int) $photos_item['main_photo'] > 0) {
                    $photo_main = $photos_item;
                    unset($photos[$photos_key]);
                }
            }
            //endregion photos

            $this->breadcrumbs = array_merge($this->breadcrumbs, $crumbs);
            $this->breadcrumbs[] = array(
                'max' => 1,
                'link' => __SITE_URL . 'item/' . strForUrl($item['title']) . '-' . $item['id'],
                'title' => $item['title'],
            );

            $meta_params['[COUNTRY]'] = $item_location['country'];
            $meta_params['[ITEM_TITLE]'] = $item['title'];
            $meta_params['[COMPANY_NAME]'] = $seller_company['name_company'];
            $meta_params['[CATEGORIES]'] = implode(',', array_map(function($value){
                return $value['title'];
            }, $crumbs));

            if ( ! empty($photo_main['photo_name'])) {
                $item_img_link = getDisplayImageLink(array('{ID}' => $id_item, '{FILE_NAME}' => $photo_main['photo_name']), 'items.main');
                $meta_params['[image]'] = $item_img_link;
            }

            //region item variants
            /** @var Items_Variants_Model $itemsVariantsModel */
            $itemsVariantsModel = model(Items_Variants_Model::class);
            $itemVariants = $itemsVariantsModel->getItemVariants((int) $id_item, true);
            foreach ($itemVariants['properties'] as $propertiesKey => $propertiesItem) {
                $itemVariants['properties'][$propertiesKey]['property_options'] = null === $propertiesItem['property_options'] ? [] : $propertiesItem['property_options']->toArray();
            }

            foreach ($itemVariants['variants'] as $itemVariantsKey => $itemVariantsItem) {
                $itemVariants['variants'][$itemVariantsKey]['price'] = moneyToDecimal($itemVariantsItem['price']);
                $itemVariants['variants'][$itemVariantsKey]['final_price'] = moneyToDecimal($itemVariantsItem['final_price']);
                $itemVariants['variants'][$itemVariantsKey]['property_options'] = null === $itemVariantsItem['property_options'] ? [] : $itemVariantsItem['property_options']->toArray();
            }
            //endregion item variants

            //region Video
            if (!empty($item['video'])) {
                $video_metadata = library(TinyMVC_Library_VideoThumb::class)->getVID($item['video']);
                $item['video_code'] = $item['video_code'] ?? $video_metadata['v_id'] ?? null;
                $item['video_source'] = $item['video_source'] ?? $video_metadata['type'] ?? null;

                try {
                    $item['video'] = library(TinyMVC_Library_VideoThumb::class)->getEmbedUrl($item['video']);
                } catch (RuntimeException $exception) {
                    // Skip
                }

                if (empty($item['video_image'])) {
                    $item['video_in_porcessing'] = true;
                    try {
                        $item['video_image'] = library(TinyMVC_Library_VideoThumb::class)->getVideoThumbnailUrl($item['video']);
                    } catch (RuntimeException $exception) {
                        $item['video_image'] = null;
                    }
                }
            }
            //endregion Video

            if (logged_in()) {
                $chatBtn = new ChatButton(['recipient' => $seller_company['id_user'], 'recipientStatus' => $seller_company['status']]);
                $seller_company['btnChat'] = $chatBtn->button();
            }

            $chatBtnByItem = new ChatButton(['recipient' => $seller_company["id_user"], 'recipientStatus' => 'active', 'item' => $item['id']], ['classes' => 'product-sidebar__chat-btn', 'text' => 'Chat now']);

            $cached_data = [
                'item'					=> $item,
                'featured' 				=> $itemModel->isFeatured($id_item),
                'featured_status'		=> $itemModel->getFeaturedStatus($id_item),
                'sold_counter'			=> $itemModel->soldCounter($id_item),
                'photos'				=> $photos,
                'photo_main'			=> $photo_main,
                'location'				=> $item_location,
                'meta_params' 			=> $meta_params,
                'more_featured_link'	=> $last_category_child['link'] . '?featured=yes',
                'more_recomended_link'	=> $last_category_child['link'],
                'itemVariants'			=> $itemVariants,
                'company_user'			=> $seller_company,
                'chatBtnByItem'			=> $chatBtnByItem,
                'view_user_review'      => true,
            ];

            if (__CACHE_ENABLE && $c_config['enable']) {
                library('cache')->set('item_general_' . $id_item, $cached_data, $c_config['cache_time']);
            }
        }
        //endregion cached data

        //region not cached data
        if (logged_in()) {
            $id_user = id_session();

            $not_cached_data = array(
                'user_logged_locations'		=> model('user')->get_user_location($id_user),
                'saved'						=> $itemModel->iSaveIt($id_user, $id_item),
            );
        }

        $idNotLogged = getEpClientIdCookieValue();

        $not_cached_data['last_viewed_items'] = $productsModel->runWithoutAllCasts(
            fn () => $productsModel->getItemsForLastViewed(
                $idNotLogged,
                (int) $id_item,
                false,
                config('limit_last_viewed_for_not_logged_users')
            )
        );

        foreach ((array) $not_cached_data['last_viewed_items'] as $key => $lastViewedItem) {
            if ($lastViewedItem['has_variants']) {
                $minPrice = $maxPrice = $minFinalPrice = $maxFinalPrice = null;

                $itemVariants = $itemsVariantsModel->findAllBy([
                    'conditions'    => [
                        'itemId'    => (int) $lastViewedItem['id'],
                    ],
                ]);

                foreach ($itemVariants as $itemVariant) {
                    if (null === $minFinalPrice || $itemVariant['final_price']->lessThan($minFinalPrice)) {
                        $minFinalPrice = $itemVariant['final_price'];
                    }

                    if (null === $maxFinalPrice || $itemVariant['final_price']->greaterThan($maxFinalPrice)) {
                        $maxFinalPrice = $itemVariant['final_price'];
                    }

                    if (!empty($itemVariant['discount'])) {
                        if (null === $minPrice || $itemVariant['price']->lessThan($minPrice)) {
                            $minPrice = $itemVariant['price'];
                        }

                        if (null === $maxPrice || $itemVariant['price']->greaterThan($maxPrice)) {
                            $maxPrice = $itemVariant['price'];
                        }
                    }
                }

                //if the price is greater than 9999, then we remove the decimals of the price
                if (!empty($minFinalPrice)) {
                    $minFinalPrice = (float) moneyToDecimal($minFinalPrice);
                    $not_cached_data['last_viewed_items'][$key]['card_prices']['min_final_price'] = (int) ($minFinalPrice / 10_000) >= 1 ? (int) $minFinalPrice : (float) $minFinalPrice;
                }

                if (!empty($maxFinalPrice)) {
                    $maxFinalPrice = (float) moneyToDecimal($maxFinalPrice);
                    if ($maxFinalPrice != $minFinalPrice) {
                        $not_cached_data['last_viewed_items'][$key]['card_prices']['max_final_price'] = (int) ($maxFinalPrice / 10_000) >= 1 ? (int) $maxFinalPrice : (float) $maxFinalPrice;
                    }
                }

                if (!empty($minPrice)) {
                    $minPrice = (float) moneyToDecimal($minPrice);
                    $not_cached_data['last_viewed_items'][$key]['card_prices']['min_price'] = (int) ($minPrice / 10_000) >= 1 ? (int) $minPrice : (float) $minPrice;
                }

                if (!empty($maxPrice)) {
                    $maxPrice = (float) moneyToDecimal($maxPrice);
                    if ($maxPrice != $minPrice) {
                        $not_cached_data['last_viewed_items'][$key]['card_prices']['max_price'] = (int) ($maxPrice / 10_000) >= 1 ? (int) $maxPrice : (float) $maxPrice;
                    }
                }
            } else {
                //if the price is greater than 9999, then we remove the decimals of the price
                $not_cached_data['last_viewed_items'][$key]['card_prices']['min_final_price'] = (float) $lastViewedItem['final_price'];

                if (!empty($lastViewedItem['discount'])) {
                    $not_cached_data['last_viewed_items'][$key]['card_prices']['min_price'] = (float) $lastViewedItem['price'];
                }
            }
        }

        /** @var Last_Viewed_Items_Model $lastViewedItemsModel */
        $lastViewedItemsModel = model(Last_Viewed_Items_Model::class);

        if((!logged_in() || is_buyer()) && !$lastViewedItemsModel->existsViewedToday($id_item, getEpClientIdCookieValue())){
            /** @var MessengerInterface $messenger */
            $messenger = container()->get(MessengerInterface::class);
            $messenger->bus('command.bus')->dispatch(
                new SaveBuyerIndustryOfInterest(
                    $item['id_cat'],
                    id_session(),
                    getEpClientIdCookieValue(),
                    CollectTypes::ITEM()
                )
            );
        }

        $lastViewedItemsModel->incrementLastViewedItemCounter(getEpClientIdCookieValue(), $id_item);

        $not_cached_data['similarItems'] = $this->indexedProductDataProvider->getItemsByCriteria(
            [
                'list_exclude_item' => [$id_item],
                'notOutOfStock'     => true,
                'per_p'			    => 8,
                'category'          => $item['id_cat'],
                'sort_by'           => ['create_date-desc']
            ]
        );
        $itemsCount = count($not_cached_data['similarItems']);

        if ($itemsCount > 4 && $itemsCount % 2 !== 0) {
            array_pop($not_cached_data['similarItems']);
            $itemsCount--;
        }

        $idsSimilar = array_column($not_cached_data['similarItems'], 'id');
        $idsSimilar[] = $id_item;

        $not_cached_data['we_recommend'] = $this->indexedProductDataProvider->getItemsByCriteria(
            [
                'list_exclude_item' => $idsSimilar,
                'category'          => $item['id_cat'],
                'per_p'			    => 4,
                'sort_by'           => ['views-desc']
            ]
        );

        $not_cached_data['mostPopularItems'] = $this->indexedProductDataProvider->getItemsByCriteria(
            [
                'featured'      => false,
                'notOutOfStock' => true,
                'per_p'			=> (int) config('promo_items_per_page', 8),
                'sort_by'       => ['views-desc']
            ]
        );
        $itemsCount = count($not_cached_data['mostPopularItems']);

        if ($itemsCount > 4 && $itemsCount % 2 !== 0) {
            array_pop($not_cached_data['mostPopularItems']);
            $itemsCount--;
        }

        $not_cached_data['latestItems'] = $this->indexedProductDataProvider->getItemsByCriteria(
            [
                'featured'      => false,
                'notOutOfStock' => true,
                'per_p'			=> (int) config('promo_items_per_page', 8),
                'sort_by'       => ['create_date-desc']
            ]
        );
        $itemsCount = count($not_cached_data['latestItems']);

        if ($itemsCount > 4 && $itemsCount % 2 !== 0) {
            array_pop($not_cached_data['latestItems']);
            $itemsCount--;
        }

        $not_cached_data['featuredItems'] = $this->indexedProductDataProvider->getItemsByCriteria(
            [
                'featured'      => true,
                'notOutOfStock' => true,
                'per_p'			=> (int) config('promo_items_per_page', 8),
                'sort_by'       => ['featured_from_date-desc']
            ]
        );
        $itemsCount = count($not_cached_data['featuredItems']);

        if ($itemsCount > 4 && $itemsCount % 2 !== 0) {
            array_pop($not_cached_data['featuredItems']);
            $itemsCount--;
        }

        //endregion not cached data

        $not_cached_data['item_description'] = model('Items_Descriptions')->get_descriptions_by_item($id_item, array('not_status' => 'removed'));

        /** @var Items_Model $itemsModel */
        $itemsModel = model(Items_Model::class);

        $savedList = $itemsModel->get_items_saved(id_session());
        $not_cached_data['savedItems'] = explode(',', $savedList);

        return array_merge($cached_data, $not_cached_data);
    }

    public function detail() {
        $this->check_is_visible_item_page();
        $id_item = $this->id_item;
        $item = $this->item;

        $item_page_main_data = $this->get_item_detail_main_data();
        $item_detail_page_cached_data = array();

        if (__CACHE_ENABLE) {
            $c_config = model('cache_config')->get_cache_options('item');

            if ( ! empty($c_config) && $c_config['enable']) {
                library('cache')->init(array('securityKey' => $c_config['folder']));
                $item_detail_page_cached_data = library('cache')->get('item_detail_' . $id_item);
            }
        }

        //region only item detail cached data
        if (empty($item_detail_page_cached_data)) {
            $item_detail_page_cached_data = array(
                'questions_user_info'	=> true,
                'count_questions'		=> model('itemquestions')->count_questions(array('item' => $id_item)),
                'count_comments'		=> model('itemcomments')->count_comments(array('item' => $id_item)),
                'breadcrumbs'			=> $this->breadcrumbs,
                'item_attrs' 			=> model('catattributes')->get_item_attr_full_values($id_item),
                'user_attrs' 			=> model('items')->get_user_attrs($id_item),
                'seller_b2b'			=> model('seller_b2b')->get_seller_b2b($item['id_seller']),
                'vin_info'				=> model('items')->get_vin_info($id_item),
            );

            if (__CACHE_ENABLE && $c_config['enable']) {
                library('cache')->set('item_detail_' . $id_item, $item_detail_page_cached_data, $c_config['cache_time']);
            }
        }
        //endregion only item detail cached data

        //region Only item detail not cached data
        $item_detail_page_not_cached_data = array();

        //region comments item
        $comment_params = array(
            'parent' 	=> 0,
            'per_p' 	=> 2,
            'order' 	=> 'date_asc',
            'item' 		=> $id_item,
        );

        $comments = model('itemcomments')->get_comments($comment_params);

        if ( ! empty($comments)) {
            $comments_list = array_column($comments, 'id_comm');

            $comments_children_params = array(
                'general_comment' 	=> implode(',', $comments_list),
                'map_tree' 			=> false,
                'order' 			=> 'date_asc',
                'per_p' 			=> 5,
                'item' 				=> $comment_params['item'],
            );

            $comments_children = model('itemcomments')->get_comments($comments_children_params);

            $item_comments = array_merge($comments, $comments_children);
            $item_detail_page_not_cached_data['comments'] = model('itemcomments')->comment_map($item_comments);
        }
        //endregion comments item

        //region questions item
        $questions_params = array(
            'per_p' => 2,
            'order' => 'date_desc',
            'item' 	=> $item['id'],
        );

        $item_detail_page_not_cached_data['questions'] = $questions = model('itemquestions')->get_questions($questions_params);

        if ( ! empty($questions) && logged_in()) {
            $questions_ids = array_column($questions, 'id_q');

            $item_detail_page_not_cached_data['helpful'] = model('itemquestions')->get_helpful_by_question(implode(',', $questions_ids), id_session());
        }
        //endregion questions item

        //endregion only item detail not cached data

        $data = array_merge($item_page_main_data, $item_detail_page_cached_data, $item_detail_page_not_cached_data);
        $data['is_my_item'] = is_my($item['id_seller']);

        //region product reviews
        /** @var Product_Reviews_Model $productReviewsModel */
        $productReviewsModel = model(Product_Reviews_Model::class);

        /** @var Product_Reviews_Images_Model $reviewImagesModel */
        $reviewImagesModel = model(Product_Reviews_Images_Model::class);

        /** @var ItemsReview_Model $itemsReviewModel */
        $itemsReviewModel = model(ItemsReview_Model::class);

        $data['limitReviews'] = 3;
        $data['productReviews'] = array_column(
            $itemsReviewModel->get_user_reviews([
                'per_p' => $data['limitReviews'],
                'item' 	=> $id_item,
                'page' 	=> 1,
            ]),
            null,
            'id_review'
        );

        $data['countProductReviews'] = count($data['productReviews']) < $data['limitReviews']
            ? count($data['productReviews'])
            : $productReviewsModel->countAllBy(['conditions' => ['itemId' => (int) $id_item]]);

        if (!empty($data['productReviews'])) {
            $reviewsImages = $reviewImagesModel->findAllBy([
                'conditions' => [
                    'reviewsIds' => array_column($data['productReviews'], 'id_review')
                ],
            ]);

            foreach ($reviewsImages as $reviewImage) {
                $data['productReviews'][$reviewImage['review_id']]['images'][] = $reviewImage['name'];
            }
        }
        //endregion product reviews

        $data['inDroplist'] = false;

        /** @var Items_Droplist_Model $droplistModel */
        $droplistModel = model(\Items_Droplist_Model::class);
        if (0 < $droplistModel->countAllBy([
            'scopes'    => [
                'item_id'   => (int) $id_item,
                'user_id'   => (int) session()->id,
            ]
        ])) {
            $data['inDroplist'] = true;
        }

        $data['header_content'] = 'new/item/detail_top_view';
        $data['sidebar_left_content'] = 'new/item/detail_sidebar_view';
        $data['footer_out_content'] = 'new/item/detail_footer_content_view';
        $data['isItemDetailPage'] = true;
        $data['main_content'] = 'new/item/detail_n_view';
        $data['webpackData'] = logged_in() ? null : [
            'styleCritical'     => 'item_detail',
            'pageConnect'       => 'item_detail_page',
            'customEncoreLinks' => true,
        ];
        $this->view->assign($data);
        $this->view->display('new/item/product_template_view');
    }

    // used only for critical css for item detail page
    public function item_detail_critical_css() {
        if ("true" !== request()->query->get('critical_css')) {
            show_404();
        }

        $item = $this->indexedProductDataProvider->getItemsByCriteria(
            [
                'featured'      => false,
                'notOutOfStock' => true,
                'per_p'			=> 1,
            ]
        );

        headerRedirect(makeItemUrl($item[0]['id'], $item[0]['title']));
    }

    public function preview() {
        $request = request()->request;

        if (empty($request->all())) {
            show_404();
        }

        /** @var TinyMVC_Library_Cleanhtml $cleanHtmlLibrary */
        $cleanHtmlLibrary = library(TinyMVC_Library_Cleanhtml::class);

        $cleanHtmlLibrary->addAdditionalTags('<br>');
        $title = $request->get('title');

        $item = [
            'id' 			    => $request->getInt('item'),
            'views' 		    => 0,
            'title' 		    => $title ?: unknowValueHtml(),
            'price' 		    => $request->getInt('price_in_dol'),
            'final_price' 	    => $request->getInt('final_price'),
            'discount'          => $request->getInt('discount'),
            'min_sale_q' 	    => $request->getInt('min_quantity'),
            'max_sale_q' 	    => $request->getInt('max_quantity'),
            'quantity' 		    => $request->getInt('quantity'),
            'weight'            => $request->getInt('weight'),
            'year' 			    => $request->getInt('year'),
            'unit_name'         => $request->get('unit_type') ?: unknowValueHtml(),
            'description'       => $cleanHtmlLibrary->sanitizeUserInput($request->get('description')),
            'is_distributor'    => $request->getInt('is_distributor'),
        ];

        if (!empty($item['final_price'])) {
            $itemPrice = priceToUsdMoney($item['price']);
            $itemDiscountPrice = priceToUsdMoney($item['final_price']);
            $itemFinalPrice = $itemDiscountPrice->isZero() ? $itemPrice : $itemDiscountPrice;
            $itemDiscount = (1 - $itemFinalPrice->ratioOf($itemPrice)) * 100;

            $item['discount'] = (int) round($itemDiscount, 12);
        }

        //region user attrs
        $userAttributes = (array) $request->get('u_attr');
        $user_attrs = [];

        if (!empty($userAttributes['name'])) {
            $attr_names = (array) $userAttributes['name'];
            $attr_vals = (array) $userAttributes['val'];

            foreach ($attr_names as $key => $name) {
                $user_attr_name = cleanInput($name);
                $user_attr_value = cleanInput(trim($attr_vals[$key]));

                if (empty($user_attr_name) || empty($user_attr_value)){
                    continue;
                }

                if(strlen($user_attr_name) > 50 || strlen($user_attr_value) > 50){
                    continue;
                }

                $user_attrs[] = [
                    'p_name' => $user_attr_name,
                    'p_value' => $user_attr_value
                ];
            }
        }
        //endregion user attrs

        //region purchase options
        if (!empty($purchaseOptions = (array) $request->get('purchase_options'))) {
            $allowed_purchase_options = [
                'order_now'     => 'sell_item',
                'samples'       => 'create_sample_order',
                'offers'        => 'manage_seller_offers',
                'inquiry'       => 'manage_seller_inquiries',
                'estimate'      => 'manage_seller_estimate',
                'po'            => 'manage_seller_po'
            ];

            foreach ($allowed_purchase_options as $purchase_option => $required_right){
                $item[$purchase_option] = 0;
                if (have_right($required_right) && in_array($purchase_option, $purchaseOptions)) {
                    $item[$purchase_option] = 1;
                }
            }
        }
        //endregion purchase options

        //region unit type
        if (!empty($unitTypeId = $request->getInt('unit_type'))) {
            /** @var Unit_Types_Model $unitTypesModel */
            $unitTypesModel = model(Unit_Types_Model::class);

            $item['unit_name'] = $unitTypesModel->findOne($unitTypeId)['unit_name'] ?? null;
        }
        //endregion unit type

        //region address
        if (in_array($addressType = $request->get('address_type'), ['custom', 'stored'])) {
            if ('stored' === $addressType) {
                $company = model('company')->get_seller_base_company(privileged_user_id());
                $companyLocation = model('company')->get_company_location($company['id_company']);
            }

            $item['p_country'] = ($companyLocation['country_id'] ?? $request->getInt('country')) ?: null;
            $item['state'] = ($companyLocation['region_id'] ?? $request->getInt('state')) ?: null;
            $item['p_city'] = ($companyLocation['city_id'] ?? $request->getInt('city')) ?: null;
            $item['item_zip'] = ($companyLocation['zip_company'] ?? cleaninput($request->get('postal_code'))) ?: null;
        }
        //endregion address

        //region item photos
        $photos = $mainPhoto = [];
        if (!empty($item['id'])){
            $photos_db = model('items')->get_items_photo($item['id'], null, 'id ASC');
            $photos_remove = (array) $request->get('images_remove');

            foreach ($photos_db as $photos_item) {
                $photoPath = getDisplayImagePath(['{ID}' => $item['id'], '{FILE_NAME}' => $photos_item['photo_name']], 'items.photos');
                list($width, $height) = getimagesize($photoPath) ?: [737, 737];
                $photos_item['width'] = $width;
                $photos_item['height'] = $height;

                if ((int) $photos_item['main_photo'] > 0) {
                    $mainPhoto = $photos_item;
                } else {
                    if (!in_array($photos_item['id'], $photos_remove)) {
                        $photos[$photos_item['photo_name']] = $photos_item;
                    }
                }
            }
        }

        if (!empty($mainPhotoName = $request->get('images_main')) && file_exists($mainPhotoPath = \App\Common\ROOT_PATH . '/' . $mainPhotoName)) {
            list($width, $height) = getimagesize($mainPhotoPath) ?: [737, 737];
            $mainPhoto = [
                'width'      => $width,
                'height'     => $height,
                'photo_name' => $mainPhotoName,
                'photo_type' => 'temp',
            ];
        }

        if (!empty($tempImages = $request->get('images'))) {
            foreach ((array) $tempImages as $tempImage) {
                $photoPath = \App\Common\ROOT_PATH . "/" . $tempImage;
                if (!file_exists($photoPath)) {
                    continue;
                }

                list($width, $height) = getimagesize($photoPath) ?: [737, 737];
                $photos[pathinfo($tempImage, PATHINFO_BASENAME)] = [
                    'width'      => $width,
                    'height'     => $height,
                    'photo_name' => $tempImage,
                    'photo_type' => 'temp',
                ];
            }
        }
        //endregion item photos

        //region item variants
        $itemVariantsOutput = [];
        if (!empty($itemProperties = (array) $request->get('properties')) && !empty($itemVariants = (array) $request->get('combinations'))) {
            $validator = new ItemVariantsValidator(
                new LegacyValidatorAdapter(
                    library(TinyMVC_Library_validator::class)
                ),
                $item['id'] ?: null
            );

            if ($validator->validate($request->all())) {
                $propertyNames = $optionNames = [];
                $propertyPriority = 1;

                foreach ($itemProperties as $itemProperty) {
                    $propertyOptions = [];
                    $propertyNames[$itemProperty['id']] = $itemProperty['name'];

                    foreach ((array) $itemProperty['options'] as $propertyOption) {
                        $optionNames[$propertyOption['id']] = $propertyOption['name'];
                        $propertyOptions[] = [
                            'id'            => $propertyOption['id'],
                            'id_property'   => $itemProperty['id'],
                            'name'          => $propertyOption['name'],
                        ];
                    }

                    $itemVariantsOutput['properties'][$itemProperty['id']] = [
                        'id'                => $itemProperty['id'],
                        'id_item'           => $item['id'] ?? null,
                        'name'              => $itemProperty['name'],
                        'priority'          => $propertyPriority++,
                        'property_options'  => $propertyOptions,
                    ];
                }

                foreach ($itemVariants as $itemVariant) {
                    $variantOptions = [];

                    foreach ((array) $itemVariant['variants'] as $variantOption) {
                        $variantOptions[] = [
                            'id'            => $variantOption['option_id'],
                            'id_variant'    => $itemVariant['id'],
                            'id_property'   => $variantOption['property_id'],
                            'name'          => $optionNames[$variantOption['option_id']],
                            'propertyName'  => $propertyNames[$variantOption['property_id']],
                        ];

                        $itemVariantsOutput['optionUsages'][$variantOption['option_id']][$itemVariant['id']] = $itemVariant['id'];
                    }

                    $price = priceToUsdMoney($itemVariant['price']);
                    $discountPrice = priceToUsdMoney($itemVariant['final_price']);
                    $finalPrice = $discountPrice->isZero() ? $price : $discountPrice;
                    $discount = (1 - $finalPrice->ratioOf($price)) * 100;

                    $itemVariantsOutput['variants'][$itemVariant['id']] = [
                        'id'                => $itemVariant['id'],
                        'id_item'           => $item['id'] ?? null,
                        'price'             => $itemVariant['price'],
                        'final_price'       => $itemVariant['final_price'],
                        'discount'          => (int) round($discount, 12),
                        'quantity'          => $itemVariant['quantity'],
                        'image'             => $itemVariant['img'],
                        'property_options'  => $variantOptions,
                    ];
                }

                if (!empty($itemVariantsOutput['variants'])) {
                    uasort(
                        $itemVariantsOutput['variants'],
                        fn ($currentVariant, $nextVariant) => $currentVariant['final_price'] > $nextVariant['final_price'] ? 1 : -1
                    );
                }
            }
        }
        //endregion item variants

        //region Video
        if (!empty($itemVideo = $request->get('video'))) {
            $video_metadata = library(TinyMVC_Library_VideoThumb::class)->getVID($itemVideo);
            $item['video_code'] = $video_metadata['v_id'] ?? null;
            $item['video_source'] = $video_metadata['type'] ?? null;

            try {
                $item['video'] = library(TinyMVC_Library_VideoThumb::class)->getEmbedUrl($itemVideo);
            } catch (RuntimeException $exception) {
                $item['video'] = $itemVideo;
            }

            $item['video_in_porcessing'] = true;
            try {
                $item['video_image'] = library(TinyMVC_Library_VideoThumb::class)->getVideoThumbnailUrl($item['video']);
            } catch (RuntimeException $exception) {
                $item['video_image'] = null;
            }
        }
        //endregion Video

        //region other description
        if (
            !empty($desctiption = $request->get('additional_description_text'))
            && !empty($languageId = $request->getInt('additional_description_language'))
        ) {
            $language = model('translations')->get_language($languageId);
            $item_description['lang_name'] = $language['lang_name'];
            $item_description['item_description'] = $cleanHtmlLibrary->sanitizeUserInput($desctiption);
        }
        //endregion other description

        $breadcrumbs[] = [
            'link' => __SITE_URL . 'categories',
            'title' => 'All categories',
        ];

        /** @var Elasticsearch_Items_Model $elasticsearchItemsModel */
        $elasticsearchItemsModel = model(Elasticsearch_Items::class);

        if (!empty($categoryId = $request->getInt('category'))) {
            $crumbs = model('category')->breadcrumbs($categoryId);
            $breadcrumbs = array_merge($breadcrumbs, $crumbs);

            $similarItems = $this->indexedProductDataProvider->getPreviewItems($item['id'], $categoryId, 8);
            $itemsCount = count($similarItems);

            if ($itemsCount > 4 && $itemsCount % 2 !== 0) {
                array_pop($similarItems);
                $itemsCount--;
            }
        }

        $breadcrumbs[] = [
            'title' => $title ?: 'N/A',
            'max'   => 1,
        ];

        $sellerId = id_session();

        views(
            'new/item/product_template_view',
            [
                'user_logged_locations' => model('user')->get_user_location($sellerId),
                'sidebar_left_content'  => 'new/item/detail_sidebar_view',
                'mostPopularItems'      => $elasticsearchItemsModel->get_items([
                    'notOutOfStock' => true,
                    'featured'      => false,
                    'sort_by'       => ['views-desc'],
                    'per_p'			=> 8,
                ]),
                'latestItems'           => $elasticsearchItemsModel->get_items([
                    'notOutOfStock' => true,
                    'featured'      => false,
                    'sort_by'       => ['create_date-desc'],
                    'per_p'			=> 8,
                ]),
                'featuredItems'         => $elasticsearchItemsModel->get_items([
                    'notOutOfStock' => true,
                    'featured'      => true,
                    'sort_by'       => ['featured_from_date-desc'],
                    'per_p'			=> 8,
                ]),
                'item_description'	    => $item_description,
                'header_content2'       => 'new/item/detail_top_view',
                'header_content'        => 'new/item/detail_top_preview_view',
                'chatBtnByItem'         => new ChatButton(['recipient' => $sellerId, 'recipientStatus' => 'active', 'item' => $item['id']], ['classes' => 'product-sidebar__chat-btn', 'text' => 'Chat now']),
                'itemVariants'			=> $itemVariantsOutput,
                'company_user' 	        => model('company')->get_company(array('id_user' => $sellerId)),
                'preview_item' 	        => true,
                'main_content'          => 'new/item/detail_n_view',
                'similarItems'          => $similarItems ?? null,
                'breadcrumbs' 	        => $breadcrumbs,
                'photo_main'            => $mainPhoto,
                'user_attrs' 		    => $user_attrs,
                'location'              => (!empty($item['p_country']) && !empty($item['p_city'])) ? model('country')->get_country_city($item['p_country'], $item['p_city']) : unknowValueHtml(),
                'photos'                => $photos,
                'item' 			        => $item,
            ]
        );
    }

    public function comments() {
        $this->check_is_visible_item_page();
        $id_item = $this->id_item;
        $item = $this->item;

        $item_page_main_data = $this->get_item_detail_main_data();
        $item_comments_page_cached_data = array();

        if (__CACHE_ENABLE) {
            $c_config = model('cache_config')->get_cache_options('item');

            if ( ! empty($c_config) && $c_config['enable']) {
                library('cache')->init(array('securityKey' => $c_config['folder']));
                $item_comments_page_cached_data = library('cache')->get('item_comments_' . $id_item);
            }
        }

        //region only item comments cached data
        if (empty($item_comments_page_cached_data)) {
            $this->breadcrumbs[] = array(
                'link' => __SITE_URL . 'item/' . strForUrl($item['title']) . '-' . $id_item . '/comments',
                'title' => 'Comments'
            );

            $comment_params = array(
                'order' 	=> 'date_asc',
                'item' 		=> $id_item,
            );

            $item_comments_page_cached_data = array(
                'page_comments_all'		=> true,
                'count_questions'		=> model('itemquestions')->count_questions(array('item' => $id_item)),
                'count_comments'		=> model('itemcomments')->count_comments(array('item' => $id_item)),
                'comments'				=> model('itemcomments')->get_comments($comment_params),
                'breadcrumbs'			=> $this->breadcrumbs,
                'page_name' 			=> 'comments',
            );

            if (__CACHE_ENABLE && $c_config['enable']) {
                library('cache')->set('item_comments_' . $id_item, $item_comments_page_cached_data, $c_config['cache_time']);
            }
        }
        //endregion only item comments cached data

        //region only item comments not cached data
        $item_comments_page_not_cached_data = array();
        //endregion only item comments not cached data

        $data = array_merge($item_page_main_data, $item_comments_page_cached_data, $item_comments_page_not_cached_data);

        $data['header_content'] = 'new/item/detail_top_view';
        $data['sidebar_left_content'] = 'new/item/detail_sidebar_view';
        $data['main_content'] = 'new/item/detail_comments_view';
        $this->view->assign($data);
        $this->view->display('new/item/product_template_view');
    }

    public function questions() {
        $this->check_is_visible_item_page();
        $id_item = $this->id_item;
        $item = $this->item;

        $item_page_main_data = $this->get_item_detail_main_data();
        $item_questions_page_cached_data = array();

        if (__CACHE_ENABLE) {
            $c_config = model('cache_config')->get_cache_options('item');

            if ( ! empty($c_config) && $c_config['enable']) {
                library('cache')->init(array('securityKey' => $c_config['folder']));
                $item_questions_page_cached_data = library('cache')->get('item_questions_' . $id_item);
            }
        }

        //region only item questions cached data
        if (empty($item_questions_page_cached_data)) {
            $this->breadcrumbs[] = array(
                'link' => __SITE_URL . 'item/' . strForUrl($item['title']) . '-' . $id_item . '/questions',
                'title' => 'Questions'
            );

            $questions = model('itemquestions')->get_questions(array('item' => $item['id'], 'order' => 'date_desc'));

            $item_questions_page_cached_data = array(
                'questions_user_info'	=> true,
                'page_questions_all'	=> true,
                'count_questions'		=> model('itemquestions')->count_questions(array('item' => $id_item)),
                'breadcrumbs'			=> $this->breadcrumbs,
                'questions'				=> $questions,
                'page_name'				=> 'questions',
            );

            if (__CACHE_ENABLE && $c_config['enable']) {
                library('cache')->set('item_questions_' . $id_item, $item_questions_page_cached_data, $c_config['cache_time']);
            }
        }
        //endregion only item questions cached data

        //region only item questions not cached data
        $item_questions_page_not_cached_data = array();

        if ( ! empty($questions) && logged_in()) {
            $questions_ids = array_column($questions, 'id_q');

            $item_questions_page_not_cached_data['helpful'] = model('itemquestions')->get_helpful_by_question(implode(',', $questions_ids), id_session());
        }
        //endregion only item questions not cached data

        $data = array_merge($item_page_main_data, $item_questions_page_cached_data, $item_questions_page_not_cached_data);

        $data['header_content'] = 'new/item/detail_top_view';
        $data['sidebar_left_content'] = 'new/item/detail_sidebar_view';
        $data['main_content'] = 'new/item/detail_questions_view';
        $this->view->assign($data);
        $this->view->display('new/item/product_template_view');
    }

    public function reviews() {
        $this->check_is_visible_item_page();
        $id_item = $this->id_item;
        $item = $this->item;

        $item_page_main_data = $this->get_item_detail_main_data();
        $item_reviews_page_cached_data = array();

        if (__CACHE_ENABLE) {
            $c_config = model('cache_config')->get_cache_options('item');

            if ( ! empty($c_config) && $c_config['enable']) {
                library('cache')->init(array('securityKey' => $c_config['folder']));
                $item_reviews_page_cached_data = library('cache')->get('item_reviews_' . $id_item);
            }
        }

        //region only item reviews cached data
        if (empty($item_reviews_page_cached_data)) {
            $this->breadcrumbs[] = array(
                'link' => __SITE_URL . 'item/' . strForUrl($item['title']) . '-' . $id_item . '/reviews',
                'title' => 'Reviews'
            );

            $rank_counters_ep = arrayByKey(model('itemsreview')->get_all_rating_counter($id_item), 'rating');
            $rank_counters_external = arrayByKey(model('external_feedbacks')->get_all_rating_counter_review($id_item), 'rating');

            $rank_counters = array(
                '5' => array('count' => 0, 'name' => 'Excellent'),
                '4' => array('count' => 0, 'name' => 'Good'),
                '3' => array('count' => 0, 'name' => 'Average'),
                '2' => array('count' => 0, 'name' => 'Poor'),
                '1' => array('count' => 0, 'name' => 'Terrible')
            );

            foreach ($rank_counters as $key => $rank_counter) {
                $rank_counters[$key]['count'] = (int) $rank_counters_ep[$key]['count_rating'] + (int) $rank_counters_external[$key]['count_rating'];
            }

            /** @var Product_Reviews_Model $productReviewsModel */
            $productReviewsModel = model(Product_Reviews_Model::class);
            $limitReviews = 5;

            $reviews_ep_conditions = array(
                'per_p' => $limitReviews,
                'item' 	=> $id_item,
                'page' 	=> 1,
            );

            $reviews_ep = array_column(
                model(ItemsReview_Model::class)->get_user_reviews($reviews_ep_conditions),
                null,
                'id_review'
            );

            $countProductReviews = count($reviews_ep) < $limitReviews
                ? count($reviews_ep)
                : $productReviewsModel->countAllBy(['conditions' => ['itemId' => (int) $id_item]]);

            if (!empty($reviews_ep)) {
                /** @var Product_Reviews_Images_Model $reviewImagesModel */
                $reviewImagesModel = model(Product_Reviews_Images_Model::class);

                $reviewsImages = $reviewImagesModel->findAllBy([
                    'conditions' => [
                        'reviewsIds' => array_column($reviews_ep, 'id_review'),
                    ],
                ]);

                foreach ($reviewsImages as $reviewImage) {
                    $reviews_ep[$reviewImage['review_id']]['images'][] = $reviewImage['name'];
                }
            }

            $external_reviews_conditions = array(
                'confirmed' => 1,
                'id_item' => $id_item,
                'per_p' => 5,
                'page' => 1,
            );

            $count_external_reviews = model('external_feedbacks')->exist_external_review($external_reviews_conditions);
            $reviews_external = array();

            if ($count_external_reviews) {
                $external_reviews_conditions['count'] = $count_external_reviews;
                $reviews_external = model('external_feedbacks')->get_external_reviews($external_reviews_conditions);
            }

            $item_reviews_page_cached_data = array(
                'countProductReviews'   => $countProductReviews,
                'view_user_review'		=> true,
                'reviews_external'		=> $reviews_external,
                'rank_counters'			=> $rank_counters,
                'reviews_count'			=> model('itemsreview')->counter_by_conditions(array('item' => $id_item)),
                'limitReviews'          => $limitReviews,
                'breadcrumbs'			=> $this->breadcrumbs,
                'reviews_ep'			=> $reviews_ep,
                'page_name'				=> 'reviews',
            );

            if (!empty($reviews_ep) && logged_in()) {
                $item_reviews_page_cached_data['helpful_reviews'] = model('itemsreview')->get_helpful_by_review(implode(',', array_column($reviews_ep, 'id_review')), id_session());
            }

            if (__CACHE_ENABLE && $c_config['enable']) {
                library('cache')->set('item_reviews_' . $id_item, $item_reviews_page_cached_data, $c_config['cache_time']);
            }
        }
        //endregion only item reviews cached data

        //region only item reviews not cached data
        $item_reviews_page_not_cached_data = array();
        //endregion only item reviews not cached data

        $data = array_merge($item_page_main_data, $item_reviews_page_cached_data, $item_reviews_page_not_cached_data);

        $data['header_content'] = 'new/item/detail_top_view';
        $data['sidebar_left_content'] = 'new/item/detail_sidebar_view';
        $data['main_content'] = 'new/item/detail_reviews_view';
        $this->view->assign($data);
        $this->view->display('new/item/product_template_view');
    }

    public function reviews_ep() {
        $this->check_is_visible_item_page();
        $id_item = $this->id_item;
        $item = $this->item;

        $item_page_main_data = $this->get_item_detail_main_data();
        $item_ep_reviews_page_cached_data = array();

        if (__CACHE_ENABLE) {
            $c_config = model('cache_config')->get_cache_options('item');

            if ( ! empty($c_config) && $c_config['enable']) {
                library('cache')->init(array('securityKey' => $c_config['folder']));
                $item_ep_reviews_page_cached_data = library('cache')->get('item_ep_reviews_' . $id_item);
            }
        }

        //region only item ep_reviews cached data
        if (empty($item_ep_reviews_page_cached_data)) {
            $this->breadcrumbs[] = array(
                'link' => __SITE_URL . 'item/' . strForUrl($item['title']) . '-' . $id_item . '/reviews',
                'title' => 'Reviews'
            );

            $this->breadcrumbs[] = array(
                'link' => __SITE_URL . 'item/' . strForUrl($item['title']) . '-' . $id_item . '/reviews_ep',
                'title' => 'EP Reviews'
            );

            /** @var ItemsReview_Model $itemReviewsModel */
            $itemReviewsModel = model(ItemsReview_Model::class);

            $ep_reviews = array_column(
                $itemReviewsModel->get_user_reviews(['item' => $id_item]),
                null,
                'id_review'
            );

            if (!empty($ep_reviews)) {
                /** @var Product_Reviews_Images_Model $reviewImagesModel */
                $reviewImagesModel = model(Product_Reviews_Images_Model::class);

                $reviewsImages = $reviewImagesModel->findAllBy([
                    'conditions' => [
                        'reviewsIds' => array_column($ep_reviews, 'id_review'),
                    ],
                ]);

                foreach ($reviewsImages as $reviewImage) {
                    $ep_reviews[$reviewImage['review_id']]['images'][] = $reviewImage['name'];
                }
            }

            $rank_counters_ep = arrayByKey(model('itemsreview')->get_all_rating_counter($id_item), 'rating');
            $rank_counters_external = arrayByKey(model('external_feedbacks')->get_all_rating_counter_review($id_item), 'rating');

            $rank_counters = array(
                '5' => array('count' => 0, 'name' => 'Excellent'),
                '4' => array('count' => 0, 'name' => 'Good'),
                '3' => array('count' => 0, 'name' => 'Average'),
                '2' => array('count' => 0, 'name' => 'Poor'),
                '1' => array('count' => 0, 'name' => 'Terrible')
            );

            foreach ($rank_counters as $key => $rank_counter) {
                $rank_counters[$key]['count'] = (int) $rank_counters_ep[$key]['count_rating'] + (int) $rank_counters_external[$key]['count_rating'];
            }

            $item_ep_reviews_page_cached_data = array(
                'view_user_review'		=> true,
                'reviews_count'			=> model('itemsreview')->counter_by_conditions(array('item' => $id_item)),
                'rank_counters'			=> $rank_counters,
                'page_name'				=> 'reviews_ep',
                'breadcrumbs'			=> $this->breadcrumbs,
                'reviews'				=> $ep_reviews,
            );

            if (!empty($ep_reviews) && logged_in()) {
                $item_ep_reviews_page_cached_data['helpful_reviews'] = model('itemsreview')->get_helpful_by_review(implode(',', array_column($ep_reviews, 'id_review')), id_session());
            }

            if (__CACHE_ENABLE && $c_config['enable']) {
                library('cache')->set('item_ep_reviews_' . $id_item, $item_ep_reviews_page_cached_data, $c_config['cache_time']);
            }
        }
        //endregion only item ep_reviews cached data

        //region only item ep_reviews not cached data
        $item_ep_reviews_page_not_cached_data = array();
        //endregion only item ep_reviews not cached data

        $data = array_merge($item_page_main_data, $item_ep_reviews_page_cached_data, $item_ep_reviews_page_not_cached_data);

        $data['header_content'] = 'new/item/detail_top_view';
        $data['sidebar_left_content'] = 'new/item/detail_sidebar_view';
        $data['main_content'] = 'new/item/detail_reviews_ep_view';
        $this->view->assign($data);
        $this->view->display('new/item/product_template_view');
    }

    public function reviews_external() {
        $this->check_is_visible_item_page();
        $id_item = $this->id_item;
        $item = $this->item;

        $item_page_main_data = $this->get_item_detail_main_data();
        $item_external_reviews_page_cached_data = array();

        if (__CACHE_ENABLE) {
            $c_config = model('cache_config')->get_cache_options('item');

            if ( ! empty($c_config) && $c_config['enable']) {
                library('cache')->init(array('securityKey' => $c_config['folder']));
                $item_external_reviews_page_cached_data = library('cache')->get('item_external_reviews_' . $id_item);
            }
        }

        //region only item external_reviews cached data
        if (empty($item_external_reviews_page_cached_data)) {
            $this->breadcrumbs[] = array(
                'link' => __SITE_URL . 'item/' . strForUrl($item['title']) . '-' . $id_item . '/reviews',
                'title' => 'Reviews'
            );

            $this->breadcrumbs[] = array(
                'link' => __SITE_URL . 'item/' . strForUrl($item['title']) . '-' . $id_item . '/reviews_external',
                'title' => 'External Reviews'
            );

            $external_reviews_conditions = array(
                'confirmed' => 1,
                'id_item' => $id_item,
            );

            $count_external_reviews = model('external_feedbacks')->exist_external_review($external_reviews_conditions);
            $reviews_external = array();

            if ($count_external_reviews) {
                $external_reviews_conditions['count'] = $count_external_reviews;
                $reviews_external = model('external_feedbacks')->get_external_reviews($external_reviews_conditions);
            }

            $rank_counters_ep = arrayByKey(model('itemsreview')->get_all_rating_counter($id_item), 'rating');
            $rank_counters_external = arrayByKey(model('external_feedbacks')->get_all_rating_counter_review($id_item), 'rating');

            $rank_counters = array(
                '5' => array('count' => 0, 'name' => 'Excellent'),
                '4' => array('count' => 0, 'name' => 'Good'),
                '3' => array('count' => 0, 'name' => 'Average'),
                '2' => array('count' => 0, 'name' => 'Poor'),
                '1' => array('count' => 0, 'name' => 'Terrible')
            );

            foreach ($rank_counters as $key => $rank_counter) {
                $rank_counters[$key]['count'] = (int) $rank_counters_ep[$key]['count_rating'] + (int) $rank_counters_external[$key]['count_rating'];
            }

            $item_external_reviews_page_cached_data = array(
                'view_user_review'		=> true,
                'reviews_external'		=> $reviews_external,
                'rank_counters'			=> $rank_counters,
                'breadcrumbs'			=> $this->breadcrumbs,
                'page_name'				=> 'reviews_external',
            );

            if (__CACHE_ENABLE && $c_config['enable']) {
                library('cache')->set('item_external_reviews_' . $id_item, $item_external_reviews_page_cached_data, $c_config['cache_time']);
            }
        }
        //endregion only item external_reviews cached data

        //region only item external_reviews not cached data
        $item_external_reviews_page_not_cached_data = array();
        //endregion only item external_reviews not cached data

        $data = array_merge($item_page_main_data, $item_external_reviews_page_cached_data, $item_external_reviews_page_not_cached_data);

        $data['header_content'] = 'new/item/detail_top_view';
        $data['sidebar_left_content'] = 'new/item/detail_sidebar_view';
        $data['main_content'] = 'new/item/detail_reviews_external_view';
        $this->view->assign($data);
        $this->view->display('new/item/product_template_view');
    }

    public function ajax_item_operation() {
        if (!isAjaxRequest())
            headerRedirect();

        if (!logged_in())
            jsonResponse(translate("systmess_error_should_be_logged"));

        $this->_load_main();
        $this->load->model('Country_Model', 'country');
        $this->load->model('Item_Snapshot_Model', 'snapshot');
        $tmvc = tmvc::instance();

        $type = $this->uri->segment(3);
        switch ($type) {
            case 'check_new_highlight_items':
                if (!have_right('manage_content'))
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));

                $lastId = $_POST['lastId'];
                $highlight_items_count = $this->items->get_count_new_highlight_items($lastId);

                if ($highlight_items_count) {
                    $last_highlight_items_id = $this->items->get_highlight_items_last_id();
                    jsonResponse('', 'success', array('nr_new' => $highlight_items_count, 'lastId' => $last_highlight_items_id));
                } else
                    jsonResponse('Error: Newly highlighted items do not exist');
            break;
            case 'draft_extend_request':
                if (!have_right('manage_personal_items')){
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));
                }

                $idRequest = request()->request->getInt('id');
                $currentUserId = (int) id_session();

                /** @var Draft_Extend_Requests_Model $extendModel */
                $extendModel = model(Draft_Extend_Requests_Model::class);

                //region check if request is set or it is new
                if (!empty($idRequest)) {
                    //if reset id is set but in our database there is no record of such (or not the owner) then error
                    if(0 == $extendModel->countBy(['conditions' => [
                        'id'      => $idRequest,
                        'id_user' => $currentUserId,
                    ]])){
                        jsonResponse(translate('systmess_error_invalid_data'));
                    }

                    //if exists then get the expiration date (need for min date for validation)
                    $request = $extendModel->findOneBy([
                        'conditions' => ['id' => $idRequest, 'id_user' => $currentUserId]
                    ]);
                    $minDate = DateTime::createFromImmutable($request['expiration_date']);
                    $minDate->add(new DateInterval('P1D'));
                }else{
                    //if request is not set then take min date as 10 days from today
                    $minDate = new DateTime();
                    $minDate->add(new DateInterval('P' . (config('draft_items_days_expire', 10) + 1) . 'D'));
                }
                //endregion check if request is set or it is new

                //region Validation
                $adapter = new LegacyValidatorAdapter(library(TinyMVC_Library_validator::class));
                $validator = new DraftExtendRequestValidator($adapter, $minDate);

                if (!$validator->validate(request()->request->all())) {
                    jsonResponse(
                        \array_map(
                            function (ConstraintViolation $violation) {
                                return $violation->getMessage();
                            },
                            \iterator_to_array($validator->getViolations()->getIterator())
                        )
                    );
                }
                //endregion Validation

                $extend = [
                    'extend_date'    => new DateTimeImmutable(request()->get('extend_date')),
                    'extend_reason'  => request()->get('extend_reason'),
                    'requested_date' => new DateTimeImmutable(),
                    'is_requested'   => 1
                ];

                /** @var Items_Model $items */
                $items = model(Items_Model::class);

                if (empty($idRequest)) {
                    /** @var Products_Model $productsModel */
                    $productsModel = model(Products_Model::class);
                    $draftItems = $productsModel->findOneBy([
                        'columns' => ['GROUP_CONCAT(id) as items'],
                        'scopes' => [
                            'sellerId' => $currentUserId,
                            'draft'    => 1,
                        ],
                    ]);
                    if (empty($draftItems)) {
                        jsonResponse(translate('systmess_request_exted_draft_not_items_message'));
                    }

                    $extendModel->deleteAllBy([
                        'scopes' => [
                            'id_user' => $currentUserId,
                            'status'  => 'new',
                        ],
                    ]);

                    $extend['expiration_date'] = (new DateTimeImmutable())->add(new DateInterval('P' . (config('draft_items_days_expire', 10) + 1) . 'D'));
                    $extend['id_user'] = $currentUserId;
                    $extend['items'] = $draftItems['items'];
                    $extendModel->insertOne($extend);

                    jsonResponse(translate('systmess_request_exted_draft_sent_success_message'), 'success');
                }

                $extendModel->updateOne($idRequest, $extend);

                jsonResponse(translate('systmess_request_exted_draft_sent_success_message'), 'success');
            break;
            case 'email_when_available':
                $userId = id_session();
                $idsList = request()->request->get('item');

                $ids = explode(',', $idsList);

                if(empty($ids)){
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                if (!model(Items_Model::class)->hasAllItems($ids)){
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                if(count($ids) == 1 && model(Items_Model::class)->existsOutOfStockNotify($userId, $ids[0])){
                    jsonResponse(translate('systmess_error_already_subscribed_about_back_in_stock_item'), 'info');
                }

                foreach($ids as $itemId){
                    if(!model(Items_Model::class)->existsOutOfStockNotify($userId, $itemId)){
                        if(!model(Items_Model::class)->insertOutOfStockNotify($userId, $itemId)){
                            jsonResponse(translate('systmess_internal_server_error'));
                        }
                    }
                }

                jsonResponse(translate('systmess_success_subscribe_to_back_in_stock_notification'), 'success');

            break;
            case 'check_new_featured_items':
                if (!have_right('manage_content'))
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));
                $lastId = $_POST['lastId'];
                $featured_items_count = $this->items->get_count_new_featured_items($lastId);

                if ($featured_items_count) {
                    $last_featured_items_id = $this->items->get_featured_items_last_id();
                    jsonResponse('', 'success', array('nr_new' => $featured_items_count, 'lastId' => $last_featured_items_id));
                } else
                    jsonResponse('Error: Newly featured items do not exist');
            break;
            case 'check_new':
                if (!have_right('manage_content'))
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));
                $lastId = $_POST['lastId'];
                $items_count = $this->items->get_count_new_items($lastId);

                if ($items_count) {
                    $last_items_id = $this->items->get_items_last_id();
                    jsonResponse('', 'success', array('nr_new' => $items_count, 'lastId' => $last_items_id));
                } else
                    jsonResponse('Error: New items do not exist');
            break;
            case 'admin_update_item':
                if (!have_right('manage_content')){
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));
                }

                $request = request()->request;

                if (
                    empty($itemId = $request->getInt('item'))
                    || empty($itemFromDb = $this->items->get_item($itemId))
                ) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                //region validation
                $adapter = new LegacyValidatorAdapter(library(TinyMVC_Library_validator::class));
                $itemValidator = new ItemValidator($adapter, date('Y'), (bool) $itemFromDb['has_variants'], null, [
                    'price'           => 'price_in_dol',
                    'finalPrice'      => 'final_price',
                    'minSaleQuantity' => 'min_quantity',
                    'maxSaleQuantity' => 'max_quantity',
                ]);

                $exceptionRules = ['title', 'price_in_dol'];
                if($itemFromDb['draft']){
                    $exceptionRules = ['title'];
                }

                $validators = [new NonStrictValidator($itemValidator, ['*' => ['required']], $exceptionRules, true)];

                if (!empty($translation = $request->get('translation_description'))) {
                    $validators[] = new TranslationDescriptionValidator($adapter);
                }

                if ($itemFromDb['has_variants']) {
                    $validators[] = new ItemVariantsValidator($adapter, $itemId, ItemVariantsValidator::VALIDATE_PROPERTIES);
                }

                $validator = new AggregateValidator($validators);

                if (!$validator->validate($request->all())) {
                    jsonResponse(\array_merge(
                        \array_map(
                            function (ConstraintViolation $violation) { return $violation->getMessage(); },
                            \iterator_to_array($validator->getViolations()->getIterator())
                        ),
                    ));
                }
                //endregion validation
                $categories = request()->get('category');
                $tags = $this->get_filtered_tags(request());

                $this->load->library('Cleanhtml', 'clean');
                $item = [
                    'id'                    => $itemId,
                    'hs_tariff_number'      => cleanInput(request()->get('hs_tariff_number')),
                    'item_categories'       => implode(',', $categories),
                    'title'                 => cleanInput(request()->get('title')),
                    'id_cat'                => end($categories),
                    'item_length'           => floatval(request()->get('item_length')),
                    'item_width'            => floatval(request()->get('item_width')),
                    'item_height'           => floatval(request()->get('item_height')),
                    'weight'                => (float) request()->get('weight'),
                    'description'           => $this->clean->sanitizeUserInput(request()->get('description')),
                    'update_date'           => date('Y-m-d H:i:s'),
                    'tags'                  => implode(',', $tags),
                    'min_sale_q'            => (int) request()->get('min_quantity'),
                    'max_sale_q'            => (int) request()->get('max_quantity'),
                    'is_distributor'        => (int) request()->request->get('is_distributor'),
                ];

                if (!$itemFromDb['has_variants']) {
                    $price_in_dol = $request->get('price_in_dol');

                    if(!$itemFromDb['draft'] || !empty($price_in_dol)){
                        $price = priceToUsdMoney($price_in_dol);
                        $discountPrice = priceToUsdMoney($request->get('final_price'));
                        $finalPrice = $discountPrice->isZero() ? $price : $discountPrice;
                        $finalDiscount = (1 - $finalPrice->ratioOf($price)) * 100;
                    } else {
                        $price = priceToUsdMoney(0);
                        $discountPrice = priceToUsdMoney(0);
                        $finalPrice = $price;
                        $finalDiscount = 0;
                    }

                    $item['quantity'] = $request->getInt('quantity');
                    $item['price'] = ($price > 0?moneyToDecimal($price):0);
                    $item['final_price'] = ($finalPrice > 0?moneyToDecimal($finalPrice):0);
                    $item['discount'] = (int) round($finalDiscount, 12);
                    $item['is_out_of_stock'] = (int) ($item['quantity'] < $item['min_sale_q']);
                }

                $item['size'] = $item['item_length'].'x'.$item['item_width'].'x'.$item['item_height'];
                $unitType = request()->get('unit_type');
                if (isset($unitType)){
                    $item['unit_type'] = (int) $unitType;
                }

                //region variants edit
                if ($itemFromDb['has_variants']) {
                    /** @var Items_Variants_Properties_Model $propertiesModel */
                    $propertiesModel = model(Items_Variants_Properties_Model::class);

                    /** @var Items_Variants_Properties_Options_Model $propertiesOptionsModel */
                    $propertiesOptionsModel = model(Items_Variants_Properties_Options_Model::class);

                    $properties = (array) $request->get('properties');

                    foreach ($properties as $property) {
                        $propertiesModel->updateOne((int) $property['id'], ['name' => $property['name']]);

                        foreach ((array) $property['options'] as $propertyOption) {
                            $propertiesOptionsModel->updateOne((int) $propertyOption['id'], ['name' => $propertyOption['name']]);
                        }
                    }
                }
                //endregion variants edit

                if (!$this->items->update_item($item)) {
                    jsonResponse('The changes have not been saved. Please try again later');
                }

                // Fire event on item update
                $this->eventBus->dispatch(new ProductWasUpdatedEvent($item['id'], with($item, function (array $diff) {
                    if (isset($diff['price']) && !($diff['price'] instanceof Money)) {
                        $diff['price'] = \priceToUsdMoney($diff['price']);
                    }
                    if (isset($diff['final_price']) && !($diff['final_price'] instanceof Money)) {
                        $diff['final_price'] = \priceToUsdMoney($diff['final_price']);
                    }

                    return $diff;
                })));

                //region User attributes
                $uAttr = request()->get('u_attr');
                $uAttrsInsert = array();
                if (isset($uAttr) && !empty($uAttr['name'])) {
                    $uAttrs = $uAttr;
                    $attrNames = $uAttrs['name'];
                    $attrVals = $uAttrs['val'];

                    foreach ($attrNames as $key => $name) {
                        $userAttrName = cleanInput($name);
                        $userAttrValue = cleanInput(trim($attrVals[$key]));

                        if (empty($userAttrName) || empty($userAttrValue))
                            continue;

                        if(strlen($userAttrName) > 50 || strlen($userAttrValue) > 50)
                            continue;

                        $uAttrsInsert[$key] = [
                            'id_item'   => $itemId,
                            'p_name'    => $userAttrName,
                            'p_value'   => $userAttrValue
                        ];
                    }
                }
                //endregion User attributes

                //insert user attr
                $this->items->delete_user_attrs_by_item($itemId);
                if (!empty($uAttrsInsert)){
                    $this->items->insert_item_user_attr_batch($uAttrsInsert);
                }

                //region item translation
                if(isset($translation)){
                    if (empty($description = model(Items_Descriptions_Model::class)->get_descriptions_by_item($itemId))) {
                        jsonResponse('Error: The item description does not exist.');
                    }

                    $translationsLangs = model(Translations_Model::class)->get_languages(array('lang_default' => 0));
                    $translationsLangsIds = array_column($translationsLangs, 'id_lang');
                    $idLang = (int)  request()->get('translation_language');

                    if(!in_array($idLang, $translationsLangsIds)){
                        jsonResponse('No such language');
                    }

                    $this->clean->addAdditionalTags('<br>');
                    model(Items_Descriptions_Model::class)->change_descriptions_by_item(
                        $itemId,
                        [
                            'descriptions_lang'  => $idLang,
                            'item_description'   => $this->clean->sanitizeUserInput($translation)
                        ]
                    );
                }
                //endregion item translation

                $updates = [
                    'id'            => $itemId,
                    'search_info'   => $this->items->get_search_info($itemId)
                ];

                //region cancel out of stock quantity
                if (!$itemFromDb['has_variants'] && $itemFromDb['is_out_of_stock'] && !$item['is_out_of_stock']) {
                    $updates['date_out_of_stock'] = null;

                    /** @var Company_Model $companyModel */
                    $companyModel = model(Company_Model::class);

                    /** @var Items_Model $itemModel */
                    $itemModel = model(Items_Model::class);

                    $companyInfo = $companyModel->get_seller_base_company($itemFromDb['id_seller'], "cb.name_company");
                    $notifyUsers = array_column($itemModel->getOutOfStockNotifyByItem($item['id']), 'id_user');

                    $this->sendItemAvailable($item, $notifyUsers, $companyInfo);
                }
                //endregion cancel out of stock quantity

                $this->items->update_item($updates);

                // CREATE SNAPSHOT
                $this->_create_item_snapshot($itemId);

                //INDEX
                model(Elasticsearch_Items_Model::class)->index($itemId);

				//region systmess
				$dataSystmess = [
					'mess_code'     => 'item_was_moderated',
					'id_users'      => [$itemFromDb['id_seller']],
					'replace'       => [
						'[ITEM_NAME]' => cleanOutput($itemFromDb['title']),
						'[ITEM_LINK]' => makeItemUrl($itemFromDb['id'], $itemFromDb['title'])
					],
					'systmess'      => true
				];
				//endregion systmess

                model(Notify_Model::class)->send_notify($dataSystmess);

                jsonResponse('The item has been updated successfully.', 'success');
            break;
            case 'free_feature_item':
                checkPermisionAjax('items_administration');

                $id_item = (int) $_POST['item'];
                if (empty($id_item) || empty($item_detail = model(Items_Model::class)->get_item($id_item, ' id, id_seller, id_cat, title '))) {
                    jsonResponse('The item does not exist.');
                }

                if ($item_detail['draft']) {
                    jsonResponse('Item draft cannot be featured.');
                }

                if (model(Items_Model::class)->count_feature_request(array('item' => $id_item)) > 0){
                    jsonResponse('The request to feature this item already exist.', 'info');
                }

                $period = config('item_featured_default_period', 10);
                $end_date = date_plus($period);

                $insert = array(
                    'featured_from_date'    => (new \DateTime())->format('Y-m-d H:i:s'),
                    'auto_extend'	        => 0,
                    'create_date' 	        => date("Y-m-d"),
                    'end_date' 		        => $end_date,
                    'id_item' 		        => $id_item,
                    'status' 		        => 'active',
                    'extend' 		        => 0,
                    'price' 		        => 0,
                    'paid' 			        => 1,
                    'notice' 		        => json_encode(array(
                                                    'add_date' => (new \DateTime())->format('Y-m-d H:i:s'),
                                                    'add_by' => $this->session->lname . ' ' . $this->session->fname,
                                                    'notice' => 'The item has been featured.'
                                                )
                                            )
                );

                $id_featured = model(Items_Model::class)->set_feature_request($insert);
                model(Items_Model::class)->update_item(array('id' => $id_item, 'featured' => 1));
                model(Elasticsearch_Items_Model::class)->index($id_item);

                if ($item_detail['featured'] == 0) {
                    model(User_Statistic_Model::class)->set_users_statistic(array(
                        $item_detail['id_seller'] => array('active_featured_items' => 1)
                    ));
                }

				$data_systmess = [
					'mess_code' => 'feature_item_for_free',
					'id_item'   => $id_featured,
					'id_users'  => [$item_detail['id_seller']],
					'replace'   => [
						'[ITEM_TITLE]' => cleanOutput($item_detail['title']),
						'[ITEM_LINK]'  => makeItemUrl($item_detail['id'], $item_detail['title']),
						'[END_DATE]'   => getDateFormat($end_date, null, 'j M, Y'),
						'[LINK]'       => __SITE_URL . 'items/my?filter_featured=1'
					],
					'systmess' => true
				];


                model(Notify_Model::class)->send_notify($data_systmess);

                jsonResponse('The item has been featured.', 'success');
            break;
            case 'free_extend_feature_item':
                checkPermisionAjax('items_administration');

                $id_featured = (int) $_POST['item'];
                $featured_request = model(Items_Featured_Model::class)->get_featured_item_id($id_featured);

                if (empty($featured_request)) {
                    jsonResponse(translate("systmess_error_request_does_not_exist"));
                }

                if ($featured_request['extend'] == 1) {
                    jsonResponse('The extend request already exist. Please check the coresponding bills.', 'info');
                }

                $item = model(Items_Model::class)->get_item($featured_request['id_item']);
                if ($item['draft']) {
                    jsonResponse('Item draft cannot be featured.');
                }

                $period = config('item_featured_default_period');
                $extendFromDate = isDateExpired($featured_request['end_date']) ? false : $featured_request['end_date'];
                $end_date = date_plus($period, 'days', $extendFromDate);
                $json_notice = json_encode(
                                array(
                                    'add_date'  => (new \DateTime())->format('Y-m-d H:i:s'),
                                    'add_by'    => $this->session->lname . ' ' . $this->session->fname,
                                    'notice'    => 'The "Feature item" request has been extended till ' . getDateFormat($end_date, null, 'j M, Y') . '.'
                                )
                            );

                $update = array(
                    'featured_from_date'    => (new \DateTime())->format('Y-m-d H:i:s'),
                    'end_date'              => $end_date,
                    'notice'                => $json_notice . ', ' . $featured_request['notice'],
                    'extend'                => 0,
                    'status'                => 'active',
                    'price'                 => 0,
                    'paid'                  => 1,
                );

                model(Items_Model::class)->update_item(array('id' => $featured_request['id_item'], 'featured' => 1));
                model(Items_Model::class)->update_feature_request($id_featured, $update);
                model(Elasticsearch_Items_Model::class)->index($featured_request['id_item']);

				$data_systmess = [
					'mess_code' => 're_feature_item_for_free',
					'id_item'   => $id_featured,
					'id_users'  => [$featured_request['id_seller']],
					'replace'   => [
						'[ITEM_TITLE]' => cleanOutput($featured_request['title']),
						'[ITEM_LINK]'  => __SITE_URL . 'item/' . strForURL($featured_request['title']) . '-' . $featured_request['id_item'],
						'[END_DATE]'   => getDateFormat($end_date, null, 'j M, Y'),
						'[LINK]'       => __SITE_URL . 'items/my?filter_featured=1'
					],
					'systmess' => true
				];

                model(Notify_Model::class)->send_notify($data_systmess);

                jsonResponse('Feature item time has been extended.', 'success');
            break;
            case 'apply_feature':
                if (!have_right('manage_personal_items')){
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));
                }

                is_allowed("freq_allowed_user_operations");

                $this->load->model('User_Bills_Model', 'user_bills');
                $this->load->model('Notify_Model', 'notify');
                $item = intval($_POST['item']);
                $item_detail = $this->items->get_item($item, ' id, id_seller, id_cat, title ');
                if (!is_privileged('user', $item_detail['id_seller'], 'manage_personal_items')){
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                if ($this->items->count_feature_request(array('item' => $item)) > 0){
                    jsonResponse(translate('systmess_error_already_requested_to_feature_item'), 'info');
                }

                $is_free_featured_items_process = (int) config('is_free_featured_items');
 				$price = $is_free_featured_items_process ? 0 : $this->category->get_cat_feature_price($item_detail['id_cat']);
                $data = array(
                    'id_item' => $item,
                    'status' => 'init',
                    'price' => $price,
                    'create_date' => date('Y-m-d'),
                    'notice' => json_encode(array(
                                    'add_date' => getDateFormat(date('Y-m-d H:i:s')),
                                    'add_by' => $this->session->lname . ' ' . $this->session->fname,
                                    'notice' => 'The feature was initiated'
                                )
                            )
                );
                if ($is_free_featured_items_process) {
                    $data['status'] = 'active';
                    $data['end_date'] = date_plus((int) config('item_featured_default_period', 10));
                    $data['paid'] = 1;
                    $data['featured_from_date'] = (new \DateTime())->format('Y-m-d H:i:s');
                }

                $id_featured_item = $this->items->set_feature_request($data);

                if ($is_free_featured_items_process) {
                    model(Items_Model::class)->update_item(array('id' => $item, 'featured' => 1));
                    model(Elasticsearch_Items_Model::class)->index($item);
                }

                model(User_Statistic_Model::class)->set_users_statistic(array(privileged_user_id() => array('total_featured_items' => 1)));

                $id_seller = privileged_user_id();

                $user_bill_data = array('bill_description' => 'This bill is for payment of feature item - '.$item_detail['title'].' request.',
                     'id_user' => $id_seller,
                     'id_type_bill' => 3,
                     'id_item' => $id_featured_item,
                     'balance' => $price,
                     'total_balance' => $price,
                     'due_date' => date('Y-m-d', strtotime("+".$tmvc->my_config['item_featured_bill_period']." days"))
                 );

                $id_bill = $is_free_featured_items_process ? $this->user_bills->set_free_user_bill($user_bill_data) : $this->user_bills->set_user_bill($user_bill_data);

                if (!$id_bill) {
                    jsonResponse(translate('systmess_internal_server_error'));
                }

                if ($is_free_featured_items_process) {
                    $data_systmess = array(
                        'mess_code' => 'free_feature_item',
                        'id_users' => array($id_seller),
                        'replace' => array(
                            '[ITEM_TITLE]' => cleanOutput($item_detail['title']),
                            '[ITEM_LINK]' => __SITE_URL . 'item/' . strForURL($item_detail['title']) . '-' . $item,
                            '[END_DATE]' => getDateFormat($data['end_date'], null, 'j M, Y')
                        ),
                        'systmess' => true
                    );
                } else {

					$data_systmess = [
						'mess_code' => 'request_feature_item',
						'id_item'   => $id_bill,
						'id_users'  => [$id_seller],
						'replace'   => [
							'[ITEM_TITLE]' => cleanOutput($item_detail['title']),
							'[ITEM_LINK]'  => __SITE_URL . 'item/' . strForURL($item_detail['title']) . '-' . $item,
							'[BILL_ID]'    => orderNumber($id_bill),
							'[BILL_LINK]'  => __SITE_URL . 'billing/my/bill/' . $id_bill,
							'[LINK]'       => __SITE_URL . 'billing/my'
						],
						'systmess' => true
					];

                }

                $this->notify->send_notify($data_systmess);

                if ($is_free_featured_items_process) {
                    jsonResponse(translate('systmess_success_free_feature_item'), 'success');
                } else {
                    jsonResponse(translate('systmess_success_requested_feature_item'), 'success');
                }
            break;
            case 'apply_highlight':
                checkPermisionAjax('manage_personal_items');

                is_allowed("freq_allowed_user_operations");

                $this->load->model('User_Bills_Model', 'user_bills');
                $this->load->model('Notify_Model', 'notify');
                $item = intval($_POST['item']);
                $item_detail = $this->items->get_item($item, ' id, id_seller, id_cat, title ');

                if (!is_privileged('user', $item_detail['id_seller'], 'manage_personal_items')) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                if ($this->items->count_highlight_request(array('item' => $item, 'status_list' => array('init')))) {
                    jsonResponse(translate('systmess_error_already_requested_to_highlight_item'), 'info');
                }

                $is_free_highlight_items_process = (int) config('is_free_highlight_items');
 				$price = $is_free_highlight_items_process ? 0 : $this->category->get_cat_highlight_price($item_detail['id_cat']);
                $data = array(
                    'id_item' => $item,
                    'status' => 'init',
                    'price' => $price,
                    'create_date' => date("Y-m-d"),
                    'notice' => json_encode(array(
                                    'add_date' => getDateFormat(date('Y-m-d H:i:s')),
                                    'add_by' => $this->session->lname . ' ' . $this->session->fname,
                                    'notice' => $is_free_highlight_items_process ? 'Item was free highlighted' : 'The highlight was initiated.'
                                )
                            )
                );

                if ($is_free_highlight_items_process) {
                    $data['status'] = 'active';
                    $data['end_date'] = date_plus((int) config('item_highlight_default_period', 10));
                    $data['paid'] = 1;
                }

                $id_high = $this->items->set_highlight_request($data);

                 if ($is_free_highlight_items_process) {
                     model(Items_Model::class)->update_item(array('id' => $item, 'highlight' => 1));
                     model(Elasticsearch_Items_Model::class)->index($item);
                 }

                 $id_seller = privileged_user_id();
                 model(User_Statistic_Model::class)->set_users_statistic(array($id_seller => array('total_highlight_items' => 1)));

                 $user_bill_data = array(
                     'bill_description' => 'This bill is for payment of highlight item - '.$item_detail['title'].' request.',
                     'id_user' => $id_seller,
                     'id_type_bill' => 4,
                     'id_item' => $id_high,
                     'balance' => $price,
                     'total_balance' => $price,
                     'due_date' => date('Y-m-d', strtotime("+" . config('item_highlight_bill_period') . " days"))
                 );

                 $id_bill = $is_free_highlight_items_process ? $this->user_bills->set_free_user_bill($user_bill_data) : $this->user_bills->set_user_bill($user_bill_data);
                 if (!$id_bill) {
                     jsonResponse(translate('systmess_internal_server_error'));
                 }

                 if ($is_free_highlight_items_process) {

					 $data_systmess = [
					 	'mess_code' => 'free_highlight_item',
					 	'id_users'  => [$id_seller],
					 	'replace'   => [
					 		'[ITEM_TITLE]' => cleanOutput($item_detail['title']),
					 		'[ITEM_LINK]'  => __SITE_URL . 'item/' . strForURL($item_detail['title']) . '-' . $item,
					 		'[END_DATE]'   => getDateFormat($data['end_date'], null, 'j M, Y')
					 	],
					 	'systmess' => true
					 ];

                 } else {
					 $data_systmess = [
					 	'mess_code' => 'request_highlight_item',
					 	'id_item'   => $id_bill,
					 	'id_users'  => [$id_seller],
					 	'replace'   => [
					 		'[ITEM_TITLE]' => cleanOutput($item_detail['title']),
					 		'[ITEM_LINK]'  => __SITE_URL . 'item/' . strForURL($item_detail['title']) . '-' . $item,
					 		'[BILL_ID]'    => orderNumber($id_bill),
					 		'[BILL_LINK]'  => __SITE_URL . 'billing/my/bill/' . $id_bill,
					 		'[LINK]'       => __SITE_URL . 'billing/my'
					 	],
					 	'systmess' => true
					 ];

                 }

                 $this->notify->send_notify($data_systmess);

                 if ($is_free_highlight_items_process) {
                     jsonResponse(translate('systmess_succes_free_highlight_item'), 'success');
                 } else {
                     jsonResponse(translate('systmess_success_requested_highlight_item'), 'success');
                 }
            break;
            case 'get_category_attrs':
                if (!have_right('manage_personal_items') && !have_right('manage_content'))
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));

                $categories = cleanInput($_POST['categories']);
                $attributes = $this->catattr->get_attributes($categories, true);

                if(!empty($attributes)){
                    foreach ($attributes as $key => $attribute) {
                        if (in_array($attribute['attr_type'], array('select', 'multiselect'))) {
                            $attr_ids[] = $attribute['id'];
                        }
                    }
                }

                if (!empty($attr_ids)) {
                    $attrs_vals = $this->catattr->get_attr_values_by_list(implode(',', $attr_ids), array('categories_list' => $categories));
                    foreach ($attributes as $key => $attribute) {
                        if (in_array($attribute['attr_type'], array('select', 'multiselect'))) {
                            foreach ($attrs_vals as $val) {
                                if ($attribute['id'] == $val['attribute']) {
                                    $attributes[$key]['attr_values'][] = $val;
                                }
                            }
                        }
                    }
                }
                $data['attributes'] = $attributes;
                $content = $this->view->fetch($this->view_folder.'admin/item/cat_attrs_view', $data);
                jsonResponse('', 'success', array('content' => $content));
            break;
            case 'delete_draft':
                checkPermisionAjax('manage_personal_items,moderate_content,disable_item');

                $idItem = (int) request()->get('item');

                /** @var Items_Model $itemModel */
                $itemModel = model(Items_Model::class);

                $itemDetail = $this->items->get_item($idItem, ' id, id_seller, id_cat, visible, draft ');
                if (empty($itemDetail)){
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                if (have_right('manage_personal_items') && (int) $itemDetail['id_seller'] != privileged_user_id()){
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                if(0 == (int) $itemDetail['draft']){
                    jsonResponse(translate('systmess_add_item_delete_not_draft_message'), 'warning');
                }

                $itemModel->delete_item($idItem);

                /** @var Elasticsearch_Items_Model $elasticsearchItemsModel */
                $elasticsearchItemsModel = model(Elasticsearch_Items_Model::class);
                $elasticsearchItemsModel->removeItemById($idItem);

                /** @var Crm_Model $crmModel */
                $crmModel = model(Crm_Model::class);

                $crmModel->create_or_update_record($itemDetail['id_seller']);

                jsonResponse(translate('systmess_add_item_deleted_draft_success_message'), 'success');

            break;
            case 'change_visibility':
                checkPermisionAjax('manage_personal_items,moderate_content,disable_item');


                /** @var Products_Model $productsModel */
                $productsModel = model(Products_Model::class);

                $itemId = request()->request->getInt('item');

                $item = $productsModel->findOne($itemId);
                if (empty($item)) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                if (have_right('manage_personal_items') && (int) $item['id_seller'] != privileged_user_id()) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                if ((int) $item['draft'] > 0) {
                    jsonResponse(translate('systmess_info_cannot_published_draft_item'), 'warning');
                }

                $itemUpdates = [
                    'visible' => $item['visible'] ? 0 : 1
                ];

                if ($itemUpdates['visible']) {
                    $itemUpdates['is_archived'] = 0;
                }

                if (!$productsModel->updateOne($itemId, $itemUpdates)) {
                    jsonResponse(translate('systmess_internal_server_error'));
                }

                /** @var Elasticsearch_Items_Model $elasticItemsModel */
                $elasticItemsModel = model(\Elasticsearch_Items_Model::class);
                $itemUpdates['visible']
                    ? $elasticItemsModel->index($itemId)
                    : $elasticItemsModel->deleteItemFromIndex($itemId);


                model(Crm_Model::class)->create_or_update_record($item['id_seller']);

                // Change visible item in droplist
                $this->eventBus->dispatch(
                    new ProductChangedVisibilityEvent($itemId, $itemUpdates['visible'])
                );

                jsonResponse(translate('systmess_success_change_visibility_status_of_item'), 'success');

            break;
            case 'change_highlight':
                if (!have_right('moderate_content')) {
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));
                }

                is_allowed("freq_allowed_user_operations");

                $post = explode('-', $_POST['dataItem']);
                $id_item = (int) $post[1];
                $highlight = $post[2];
                $main_cond['id'] = $id_item;

                if($this->items->isInitHighlight($id_item)){
                    jsonResponse('Info: There are "Highlight request" for this item and must be paid.', 'info');
                }

                $this->items->updateHightlight($id_item, array('end_date' => date('Y-m-d'), 'status' => 'expired'), array('status' => 'active'));
                if ($highlight) {
                    $main_cond['highlight'] = 0;
                    $message = 'Item was Unhighlighted successfully.';
                } else {
                    global $tmvc;
                    $main_cond['highlight'] = 1;
                    $message = 'Item was Highlighted successfully.';
                    $item_detail = $this->items->get_item($id_item, ' id, id_seller, id_cat ');
                    $data = array(
                        'id_item' => $id_item,
                        'status' => 'active',
                        'price' => 0,
                        'end_date' => date('Y-m-d', strtotime("+" . $tmvc->my_config['item_highlight_default_period'] . " days")),
                        'paid' => 1
                    );
                    $this->items->setHightlight($data);

                    $this->load->model('User_Bills_Model', 'user_bills');
                    $id_bill = $this->user_bills->set_free_user_bill(array('id_user' => $item_detail['id_seller'], 'id_type_bill' => 4, 'id_item' => $id_item, 'balance' => 0));
                    if ($id_bill) {

						$data_systmess = [
							'mess_code' => 'free_bill',
							'id_item'   => $id_bill,
							'id_users'  => [$item_detail['id_seller']],
							'replace'   => [
								'[BILL_ID]'   => orderNumber($id_bill),
								'[BILL_LINK]' => __SITE_URL . 'billing/my/bill/' . $id_bill
							],
							'systmess' => true
						];

                        $this->load->model('Notify_Model', 'notify');
                        $this->notify->send_notify($data_systmess);
                    }
                }
                if ($this->items->update_item($main_cond))
                    jsonResponse($message, 'success');
                else
                    jsonResponse('Error: You cannot perform this action now. Please try again later.');
            break;
            case 'change_partnership':
                if (!have_right('moderate_content')) {
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));
                }

                $data = array(
                    'id' => intVal($_POST['id_item']),
                    'is_partners_item' => intVal((bool)$_POST['change_to'])
                );
                if ($this->items->update_item($data)) {
                    jsonResponse('The status of partnership was successfully changed.', 'success');
                } else
                    jsonResponse('Error: You cannot perform this action now. Please try again later.');
            break;
            case 'add_item':
                is_allowed("freq_allowed_user_operations");
                checkHaveCompanyAjax();
                checkPermisionAjax('manage_personal_items');

                //region Category
                $id_category = (int) $_POST['category'];
                if(empty($id_category)){
                    jsonResponse(translate('validation_is_required', ["%s" => 'Category']));
                }

                $category = model('category')->get_category($id_category);
                if(empty($category)){
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                if(!empty($category['cat_childrens'])){
                    jsonResponse(translate('systmess_error_invalid_data'));
                }
                //endregion Category

                //region Validation
                $this->validate_item(
                    $request = request(),
                    $category,
                    false,
                    $save_draft = isset($_POST['save_draft']) && $_POST['save_draft'],
                    $has_additional_description = isset($_POST['additional_description_language'])
                );
                //endregion Validation

                $tags = $this->get_filtered_tags($request);
                $good_images = $this->get_filtered_images($request);
                $address_parts = $this->get_address_parts($request);
                $minSaleQuantity = $request->request->get('min_quantity');
                $maxSaleQuantity = $request->request->get('max_quantity');
                $update_user_statistic = array();
                $item_length = floatval($_POST['item_length']);
                $item_width = floatval($_POST['item_width']);
                $item_height = floatval($_POST['item_height']);
                $itemQuantity = $request->request->getInt('quantity');

                $price = priceToUsdMoney($_POST['price_in_dol'] ?: 0);
                $discount_price = priceToUsdMoney($_POST['final_price'] ?: 0);
                $final_price = $discount_price->isZero() ? $price : $discount_price;
                $final_discount = $final_price->isZero() ? 0 : (1 - $final_price->ratioOf($price)) * 100;

                // VIN INFO
                if($category['p_or_m'] == 2 && $category['vin'] && isset($_POST['vin_code'])) {
                    $this->load->library('Vindecoder', 'vindecoder');
                    $vin_code = cleanInput($_POST["vin_code"]);
                    if ($this->vindecoder->is_used($vin_code))
                        jsonResponse(translate('systmess_error_item_vin_is_already_used'));

                    $vin_decode = $this->vindecoder->decode($vin_code, 'both');
                    if(empty($vin_decode)){
                        jsonResponse(translate('systmess_error_item_vin_is_not_correct'));
                    }

                    $vin_info = array(
                        'vin_numb'          => $vin_code,
                        'vin_info'          => json_encode($vin_decode['array']),
                        'vin_search_info'   => $vin_decode['string']
                    );
                }

                if (!$save_draft || !empty($_POST['origin_country'])) {
                    $this->load->model('Country_Model', 'country');
                    $origin_country = $this->country->get_country(intval($_POST['origin_country']));
                    if(empty($origin_country)){
                        jsonResponse(translate('systmess_error_item_origin_country_not_valid'));
                    }
                } else {
                    $origin_country['abr'] = 'NN';
                }

                $jsonBreadcrumb = json_decode("[{$category['breadcrumbs']}]", true);
                $commaBreadcrumb = implode(",", array_map(function($ar) { $ark = array_keys($ar); return $ark[0]; }, $jsonBreadcrumb));

                $id_seller = privileged_user_id();

                $this->load->library('Cleanhtml', 'clean');
                $this->clean->addAdditionalTags('<br>');
                $item = [
                    'hs_tariff_number'      => cleanInput($_POST['hs_tariff_number']),
                    'item_categories'       => $commaBreadcrumb,
                    'title'                 => cleanInput($_POST['title']),
                    'year'                  => (int) $_POST['year'],
                    'id_cat'                => $id_category,
                    'out_of_stock_quantity' => $request->request->getInt('out_of_stock_quantity'),
                    'price'                 => ($price > 0?moneyToDecimal($price):0),
                    'final_price'           => ($final_price > 0?moneyToDecimal($final_price):0),
                    'discount'              => (int) round($final_discount, 12),
                    'item_length'           => $item_length,
                    'item_width'            => $item_width,
                    'item_height'           => $item_height,
                    'weight'                => (float) $_POST['weight'],
                    'id_seller'             => $id_seller,
                    'p_country'             => $address_parts['country'] ?? 0,
                    'state'                 => $address_parts['state'] ?? 0,
                    'p_city'                => $address_parts['city'] ?? 0,
                    'item_zip'              => $address_parts['postal_code'] ?? '',
                    'origin_country'        => (int) $_POST['origin_country'] ?? 0,
                    'origin_country_abr'    => $origin_country['abr'] ?? '',
                    'description'           => $this->clean->sanitizeUserInput($_POST['description']),
                    'create_date'           => date('Y-m-d H:i:s'),
                    'update_date'           => date('Y-m-d H:i:s'),
                    'tags'                  => implode(',', $tags),
                    'min_sale_q'            => $minSaleQuantity,
                    'max_sale_q'            => $maxSaleQuantity,
                    'quantity'              => (int) $_POST['quantity'],
                    'is_distributor'        => (int) $request->request->get('is_distributor'),
                    'draft'                 => $save_draft ? 1 : 0,
                    'is_out_of_stock'       => (int) ($itemQuantity < $minSaleQuantity),
                ];

                $seller_info = model('user')->getSimpleUser($id_seller);
                if ($seller_info['status'] != 'active'){
                    $item['blocked'] = 2;
                }

                if($save_draft){
                    $item['visible'] = 0;
                    $daysExpire = config('draft_items_days_expire', 10);
                    $dateExpire = new DateTime();
                    $dateExpire->modify("+$daysExpire day");
                    $item['draft_expire_date'] = $dateExpire->format('Y-m-d');
                }

                $item['size'] = $item['item_length'].'x'.$item['item_width'].'x'.$item['item_height'];

                if (isset($_POST['unit_type'])){
                    $item['unit_type'] = (int) $_POST['unit_type'];
                }

                $allowed_purchase_options = array(
                    'order_now'     => 'sell_item',
                    'samples'       => 'create_sample_order',
                    'offers'        => 'manage_seller_offers',
                    'inquiry'       => 'manage_seller_inquiries',
                    'estimate'      => 'manage_seller_estimate',
                    'po'            => 'manage_seller_po'
                );
                foreach($allowed_purchase_options as $purchase_option => $required_right){
                    $item[$purchase_option] = 0;
                    if(have_right($required_right) && in_array($purchase_option, $_POST['purchase_options'])){
                        $item[$purchase_option] = 1;
                    }
                }

                $id_item = (int) $this->items->insert_item($item);

                /** @var Products_Photo_Model $productsPhotoModel */
                $productsPhotoModel = model(Products_Photo_Model::class);

                // Next we need to take our filesystem for temp directory
                /** @var FilesystemProviderInterface */
                $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);

                $tempPrefixer = $storageProvider->prefixer('temp.storage');
                $tempDisk = $storageProvider->storage('temp.storage');
                $publicDisk = $storageProvider->storage('public.storage');

                //region main image

                $mainImageTempFullPath = (string) $request->request->get('images_main');
                if (!empty($mainImageTempFullPath)) {
                    $mainImageName = pathinfo($mainImageTempFullPath, PATHINFO_BASENAME);
                    $tempPathToFile = FilePathGenerator::makePathToUploadedFile($mainImageName);
                    $tempFolder = pathinfo($tempPathToFile, PATHINFO_DIRNAME) . DS;
                    $publicFolder = str_replace('{ID}', $id_item, config('img.items.main.relative_disk_path'));

                    try {
                        $publicDisk->write($publicFolder . $mainImageName, $tempDisk->read($tempPathToFile));
                    } catch (\Throwable $th) {
                        $this->items->delete_item($id_item);

                        jsonResponse(translate('systmess_internal_server_error'));
                    }

                    if (!empty($mainImageThumbs = config("img.items.main.thumbs"))) {
                        $tempImagePath = pathinfo($mainImageTempFullPath, PATHINFO_DIRNAME);

                        foreach ($mainImageThumbs as $mainImageThumb) {
                            $thumbName = str_replace('{THUMB_NAME}', $mainImageName, $mainImageThumb['name']);

                            try {
                                $publicDisk->write($publicFolder . $thumbName, $tempDisk->read($tempFolder . $thumbName));
                            } catch (\Throwable $th) {
                                $this->items->delete_item($id_item);

                                try {
                                    $publicDisk->deleteDirectory($publicFolder);
                                } catch (\Throwable $th) {
                                    //nothing critical
                                }

                                jsonResponse(translate('systmess_internal_server_error'));
                            }
                        }
                    }

                    $productsPhotoModel->insertOne([
                        'main_photo'    => 1,
                        'photo_name'    => $mainImageName,
                        'type_photo'    => 'portrait',
                        'sale_id'       => $id_item,
                    ]);
                }
                //endregion main image

                //region photos

                if (!empty($good_images)) {
                    $mainParentImage = $request->request->get('parent');

                    $uploadedPhotos = [];
                    $photoThumbs = config("img.items.photos.thumbs");

                    foreach($good_images as $good){
                        $photoName = pathinfo($good, PATHINFO_BASENAME);
                        $tempPathToFile = FilePathGenerator::makePathToUploadedFile($photoName);
                        $publicFolder = str_replace('{ID}', $id_item, config('img.items.main.relative_disk_path'));
                        $tempFolder = pathinfo($tempPathToFile, PATHINFO_DIRNAME) . DS;

                        try {
                            $publicDisk->write($publicFolder . $photoName, $tempDisk->read($tempPathToFile));
                        } catch (\Throwable $th) {
                            $this->items->delete_item($id_item);

                            try {
                                $publicDisk->deleteDirectory($publicFolder);
                            } catch (\Throwable $th) {
                                //nothing critical
                            }

                            jsonResponse(translate('systmess_internal_server_error'));
                        }

                        if (!empty($photoThumbs)) {
                            foreach ($photoThumbs as $photoThumb) {
                                $thumbName = str_replace('{THUMB_NAME}', $photoName, $photoThumb['name']);

                                try {
                                    $publicDisk->write($publicFolder . $thumbName, $tempDisk->read($tempFolder . $thumbName));
                                } catch (\Throwable $th) {
                                    $this->items->delete_item($id_item);

                                    try {
                                        $publicDisk->deleteDirectory($publicFolder);
                                    } catch (\Throwable $th) {
                                        //nothing critical
                                    }

                                    jsonResponse(translate('systmess_internal_server_error'));
                                }
                            }
                        }

                        $uploadedPhotos[] = [
                            'photo_name'    => $photoName,
                            'type_photo'    => 'portrait',
                            'sale_id'       => $id_item,
                            'main_parent'   => $mainParentImage === $photoName ? 1 : null,
                        ];
                    }

                    if (!empty($uploadedPhotos)) {
                        $productsPhotoModel->insertMany($uploadedPhotos);
                    }
                }
                //endregion photos

                //region item variants
                if (!empty($variantProperties = (array) $request->request->get('properties'))) {
                    /** @var Items_Variants_Model $itemsVariantsModel */
                    $itemsVariantsModel = model(Items_Variants_Model::class);

                    /** @var Items_Variants_Properties_Model $itemsPropertiesModel */
                    $itemsPropertiesModel = model(Items_Variants_Properties_Model::class);

                    /** @var Items_Variants_Properties_Options_Model $itemsPropertiesOptionsModel */
                    $itemsPropertiesOptionsModel = model(Items_Variants_Properties_Options_Model::class);

                    /** @var Items_Variants_Properties_Relation_Model $itemsVariantsPropertiesRelationModel */
                    $itemsVariantsPropertiesRelationModel = model(Items_Variants_Properties_Relation_Model::class);

                    /** @var Products_Model $productsModel */
                    $productsModel = model(Products_Model::class);

                    $propertyPriority = 1;
                    $propertiesIds = [];
                    $propertyOptionsIds = [];

                    /** @var \Money\Money $minPrice */
                    $minPrice = null;
                    /** @var \Money\Money $minFinalPrice */
                    $minFinalPrice = null;
                    $minDiscount = 0;

                    foreach ($variantProperties as $variantPropertyKey => $variantProperty) {
                        $propertiesIds[$variantPropertyKey] = $propertyId = $itemsPropertiesModel->insertOne([
                            'priority'  => $propertyPriority++,
                            'id_item'   => $id_item,
                            'name'      => $variantProperty['name'],
                        ]);

                        foreach ((array) $variantProperty['options'] as $optionKey => $propertyOption) {
                            $propertyOptionsIds[$propertyId][$optionKey] = $itemsPropertiesOptionsModel->insertOne([
                                'id_property'   => $propertyId,
                                'name'          => $propertyOption['name'],
                            ]);
                        }
                    }

                    foreach ((array) $request->request->get('combinations') as $itemVariant) {
                        $price = priceToUsdMoney($itemVariant['price'] ?: 0);
                        $finalPrice = priceToUsdMoney($itemVariant['final_price'] ?: 0);
                        $finalPrice = $finalPrice->isZero() ? $price : $finalPrice;
                        $discount = $finalPrice->isZero() ? 0 : (1 - $finalPrice->ratioOf($price)) * 100;

                        //compare float numbers
                        if (null == $minFinalPrice || $finalPrice->lessThan($minFinalPrice) || ($finalPrice->equals($minFinalPrice) && $price->greaterThan($minPrice))) {
                            //we save price values of variant with minimal finalPrice
                            $minFinalPrice = $finalPrice;
                            $minPrice = $price;
                            $minDiscount = $discount;
                        }

                        $variantId = $itemsVariantsModel->insertOne([
                            'final_price'   => moneyToDecimal($finalPrice),
                            'discount'      => (int) round($discount, 12),
                            'quantity'      => $itemVariant['quantity'],
                            'id_item'       => $id_item,
                            'price'         => moneyToDecimal($price),
                            'image'         => $itemVariant['img'],
                        ]);

                        foreach ((array) $itemVariant['variants'] as $variantOption) {
                            $itemsVariantsPropertiesRelationModel->insertOne([
                                'id_property_option'    => $propertyOptionsIds[$propertiesIds[$variantOption['property_id']]][$variantOption['option_id']],
                                'id_variant'            => $variantId,
                            ]);
                        }
                    }

                    $productsModel->updateOne(
                        $id_item,
                        [
                            'is_out_of_stock'   => 0,
                            'has_variants'      => 1,
                            'final_price'       => $minFinalPrice > 0 ? moneyToDecimal($minFinalPrice) : 0,
                            'discount'          => (int) round($minDiscount, 12),
                            'price'             => $minPrice > 0 ? moneyToDecimal($minPrice) : 0,
                        ]
                    );
                }
                //endregion item variants

                if (isset($vin_info)) {
                    $vin_info['id_motor'] = $id_item;
                    $this->items->set_vin_info($vin_info);
                }

                if($has_additional_description){
                    $this->_update_items_descriptions(
                        $id_item,
                        (int)$_POST['additional_description_language'],
                        $this->clean->sanitizeUserInput($_POST['additional_description_text']),
                        (int)$_POST['additional_description_translate']
                    );
                }

                //region Attributes
                // PREPARE CATEGORIES ATTRIBUTES
                // $attributes = $this->catattr->get_attributes($id_category);
                // if(!empty($attributes)){
                // 	$attributes_keys = array_keys($attributes);
                // 	$attributes_values = $this->catattr->get_attr_values(implode(',', $attributes_keys));

                // 	$attrs_insert = array();
                // 	if (isset($_POST['attrs'])) {
                // 		$attrs = $_POST['attrs'];

                // 		foreach ($attrs as $id => $val) {
                // 			$attribute = $attributes[$id];
                // 			if (in_array($attribute['attr_type'], array('text', 'range'))) {
                // 				$val = trim($val);
                // 				$val = cleanInput($val);
                // 				if(empty($val))
                // 					continue;

                // 				$attrs_insert[] = array(
                // 					'item' => $id_item,
                // 					'attr' => $id,
                // 					'attr_value' => $val
                // 				);
                // 			} elseif (in_array($attribute['attr_type'], array('select', 'multiselect'))) {
                // 				if(!empty($val)){
                // 					foreach ($val as $one){
                // 						$one = trim($one);
                // 						$one = cleanInput($one);

                // 						if(empty($one))
                // 							continue;

                // 						$attrs_insert[] = array(
                // 							'item' => $id_item,
                // 							'attr' => $id,
                // 							'attr_value' => $one
                // 						);
                // 					}
                // 				}
                // 			}
                // 		}
                // 	}
                // }
                //endregion Attributes

                //region User attributes
                // PREPARE USER ATTRIBUTES
                $u_attrs_insert = array();
                if (isset($_POST['u_attr']) && !empty($_POST['u_attr']['name'])) {
                    $u_attrs = $_POST['u_attr'];
                    $attr_names = $u_attrs['name'];
                    $attr_vals = $u_attrs['val'];

                    foreach ($attr_names as $key => $name) {
                        $user_attr_name = cleanInput($name);
                        $user_attr_value = cleanInput(trim($attr_vals[$key]));

                        if (empty($user_attr_name) || empty($user_attr_value))
                            continue;

                        if(strlen($user_attr_name) > 50 || strlen($user_attr_value) > 50)
                            continue;

                        $u_attrs_insert[] = array(
                            'id_item'   => $id_item,
                            'p_name'    => $user_attr_name,
                            'p_value'   => $user_attr_value
                        );
                    }
                }
                //endregion User attributes

                // INSERT CATEGORIES ATTRIBUTES
                // if (!empty($attrs_insert))
                // 	$this->items->insert_item_attr_batch($attrs_insert);

                // INSERT USER ATTRIBUTES
                if (!empty($u_attrs_insert)) {
                    $this->items->insert_item_user_attr_batch($u_attrs_insert);
                }

                // UPDATE ITEM SEARCH INFO
                $update = array(
                    'id' => $id_item,
                    'search_info' => $this->items->get_search_info($id_item)
                );

                if (isset($_POST['video']) && !empty($_POST['video'])) {
                    $this->load->library('videothumb');
                    $video_link = $this->videothumb->getVID($_POST['video']);
                    $new_video = $this->videothumb->process($_POST['video']);

                    if(empty($new_video['error'])) {
                        $update['video_source'] = $video_link['type'];

                        $path = getImgPath('items.main', array('{ID}' => $id_item));
                        if (!is_dir($path))
                            mkdir($path, 0777);

                        $file_video[] = $new_video['image'];
                        $conditions = array(
                            'images'        => $file_video,
                            'destination'   => $path,
                            'resize'        => '730xR'
                        );
                        $res_video = $this->upload->copy_images_new($conditions);
                        if (empty($res_video['errors'])) {
                            @unlink($new_video['image']);

                            $update['video_image'] = $res_video[0]['new_name'];
                            $update['video'] = $_POST['video'];
                            $update['video_code'] = $new_video['v_id'];
                        }
                    }

                }
                model(Items_Model::class)->update_item($update);

                if($item['draft'] === 0){
                    model('complete_profile')->update_user_profile_option(privileged_user_id(), 'company_items');

                    /** @var TinyMVC_Library_Auth $authenticationLibrary */
                    $authenticationLibrary = library(TinyMVC_Library_Auth::class);
                    $authenticationLibrary->setUserCompleteProfile(privileged_user_id());
                }

                // CREATE SNAPSHOT
                if (!$save_draft) {
                    $this->_create_item_snapshot($id_item);
                    library('wall')->add(array(
                        'type'      => 'item',
                        'operation' => 'add',
                        'id_item'   => $id_item
                    ));
                }else{
                    $dataSystmess = [
                        'mess_code' => 'add_item_draft_type_save',
                        'id_users'  => [$id_seller],
                        'replace'   => [
                            '[DAYS]'      => config('draft_items_days_expire', 10),
                            '[REQUEST_LINK]' => __SITE_URL . 'items/my?request=0',
                        ],
                        'systmess' => true,
                    ];

                    /** @var Notify_Model $notifyModel */
                    $notifyModel = model(Notify_Model::class);

                    $notifyModel->send_notify($dataSystmess);
                }

                // UPDATE ACTIVITY LOG
                $this->load->model('Activity_Log_Messages_Model', 'activity_messages');
                $context = get_user_activity_context();
                $context['item'] = array('id' => $id_item, 'name' => $item['title'], 'url' => makeItemUrl($id_item, $item['title']));
                $context['changes'] = array('old' => array(), 'current' => array());
                $this->activity_logger->setOperationType(ADD);
                $this->activity_logger->setResourceType(ITEM);
                $this->activity_logger->setResource($id_item);
                $this->activity_logger->info($this->activity_messages->get_message(ITEM, ADD), $context);

                $success_message = translate('systmess_success_add_item');
                if ($save_draft) {
                    $success_message = translate('systmess_success_add_draft_item');
                } elseif ('pending' === $seller_info['status']) {
                    $success_message = translate('systmess_success_add_item_in_status_pending');
                }

                //region block user content
                if(in_array($seller_info['status'], array('blocked', 'restricted'))){
                    model('blocking')->change_blocked_users_items(array(
                        'blocked'       => 0,
                        'users_list'    => array($id_seller)
                    ), array('blocked' => 2));
                }
                //endregion block user content

                model(Crm_Model::class)->create_or_update_record($id_seller);

                jsonResponse($success_message, 'success', array('id_item' => $id_item));
            break;
            case 'edit_item':
                is_allowed("freq_allowed_user_operations");
                checkHaveCompanyAjax();
                checkPermisionAjax('manage_personal_items,manage_content');

                /** @var Products_Model $productsModel */
                $productsModel = model(Products_Model::class);

                $itemId = request()->request->getInt('item');
                if (empty($item_info = $this->items->get_item($itemId))){
                    jsonResponse('Error: This item does not exist.');
                }

                if (!$this->items->my_item(privileged_user_id(), $itemId) && !have_right('manage_content')){
                    jsonResponse('Error: This is not your item.');
                }

                //region Category
                $id_category = (int) $_POST['category'];
                if(empty($id_category)){
                    jsonResponse('Please chose the Category first.');
                }

                $category = model('category')->get_category($id_category);
                if(empty($category)){
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                if(!empty($category['cat_childrens'])){
                    jsonResponse(translate('systmess_error_invalid_data'));
                }
                //endregion Category

                //region Validation
                $this->validate_item(
                    $request = request(),
                    $category,
                    true,
                    $save_draft = $item_info['draft'] && isset($_POST['save_draft']) && $_POST['save_draft'],
                    $has_additional_description = isset($_POST['additional_description_language']),
                    (int) $itemId
                );
                //endregion Validation

                $tags = $this->get_filtered_tags($request);
                $good_images = $this->get_filtered_images($request);
                $address_parts = $this->get_address_parts($request);
                $itemQuantity = $request->request->getInt('quantity');
                $minSaleQuantity = $request->request->getInt('min_quantity');
                $maxSaleQuantity = $request->request->getInt('max_quantity');

                $update_user_statistic = array();

                $item_length = floatval($_POST['item_length']);
                $item_width = floatval($_POST['item_width']);
                $item_height = floatval($_POST['item_height']);

                $price = priceToUsdMoney($_POST['price_in_dol'] ?: 0);
                $discount_price = priceToUsdMoney($_POST['final_price'] ?: 0);
                $final_price = $discount_price->isZero() ? $price : $discount_price;
                $final_discount = $final_price->isZero() ? 0 : (1 - $final_price->ratioOf($price)) * 100;

                // UPDATE VIN INFO
                if ($category['p_or_m'] == 2 && $category['vin'] && isset($_POST['vin_code'],$_POST['old_vin_code']) && ($_POST['vin_code'] != $_POST['old_vin_code'])) {
                    $this->load->library('Vindecoder', 'vindecoder');
                    $vin_code = cleanInput($_POST["vin_code"]);
                    if ($this->vindecoder->is_used($vin_code))
                        jsonResponse('Error: This VIN is already used by other vehicle.');

                    $vin_decode = $this->vindecoder->decode($vin_code, 'both');
                    if(empty($vin_decode)){
                        jsonResponse('Error: Incorrect VIN number.');
                    }

                    $vin_info = array(
                        'vin_numb' => $vin_code,
                        'vin_info' => json_encode($vin_decode['array']),
                        'vin_search_info' => $vin_decode['string']
                    );

                    model('items')->update_vin_info($itemId, $vin_info);
                }else if($category['p_or_m'] == 1){
                    $vin_info = model('items')->get_vin_info($itemId);

                    if(!empty($vin_info)){
                        model('items')->delete_vin($itemId);
                    }
                }

                //region Attributes
                /* categories attributes */
                // $attributes = $this->catattr->get_attributes($id_category);
                // if(!empty($attributes)){
                // 	$attributes_keys = array_keys($attributes);
                // 	$attributes_values = $this->catattr->get_attr_values(implode(',', $attributes_keys));

                // 	$attrs_insert = array();
                // 	if (isset($_POST['attrs'])) {
                // 		$attrs = $_POST['attrs'];

                // 		foreach ($attrs as $id => $val) {
                // 			$attribute = $attributes[$id];
                // 			if (in_array($attribute['attr_type'], array('text', 'range'))) {
                // 				$val = trim($val);
                // 				$val = cleanInput($val);

                // 				if (empty($val))
                // 					continue;

                // 				$attrs_insert[] = array(
                // 					'item' => $itemId,
                // 					'attr' => $id,
                // 					'attr_value' => $val
                // 				);
                // 			} elseif (in_array($attribute['attr_type'], array('select', 'multiselect'))) {
                // 				if(!empty($val)){
                // 					foreach ($val as $one){
                // 						$one = trim($one);
                // 						$one = cleanInput($one);

                // 						if (empty($one))
                // 							continue;

                // 						$attrs_insert[] = array(
                // 							'item' => $itemId,
                // 							'attr' => $id,
                // 							'attr_value' => $one
                // 						);
                // 					}
                // 				}
                // 			}
                // 		}
                // 	}
                // }
                //endregion Attributes

                //region User attributes
                $u_attrs_insert = array();
                if (isset($_POST['u_attr']) && !empty($_POST['u_attr']['name'])) {
                    $u_attrs = $_POST['u_attr'];
                    $attr_names = $u_attrs['name'];
                    $attr_vals = $u_attrs['val'];

                    foreach ($attr_names as $key => $name) {
                        $user_attr_name = cleanInput($name);
                        $user_attr_value = cleanInput(trim($attr_vals[$key]));

                        if (empty($user_attr_name) || empty($user_attr_value))
                            continue;

                        if(strlen($user_attr_name) > 50 || strlen($user_attr_value) > 50)
                            continue;

                        $u_attrs_insert[$key] = array(
                            'id_item' => $itemId,
                            'p_name' => $user_attr_name,
                            'p_value' => $user_attr_value
                        );
                    }
                }
                //endregion User attributes

                //insert new cat attr
                // $this->items->delete_cat_attr_by_item($itemId);
                // if (!empty($attrs_insert)){
                // 	$this->items->insert_item_attr_batch($attrs_insert);
                // }

                //insert user attr


                /**
                 * Get old attributes
                 */
                $itemOldSpecs = $productsModel->getUserAttrs($itemId);

                $this->items->delete_user_attrs_by_item($itemId);
                if (!empty($u_attrs_insert)){
                    $this->items->insert_item_user_attr_batch($u_attrs_insert);
                }

                if (!$save_draft || !empty($_POST['origin_country'])) {
                    $this->load->model('Country_Model', 'country');
                    $origin_country = $this->country->get_country(intval($_POST['origin_country']));
                    if(empty($origin_country)){
                        jsonResponse('Error: The Country of origin is not valid.');
                    }
                }else{
                    $origin_country['abr'] = 'NN';
                }

                //breadcrumb
                $jsonBreadcrumb = json_decode("[{$category['breadcrumbs']}]", true);
                $commaBreadcrumb = implode(",", array_map(function($ar) { $ark = array_keys($ar); return $ark[0]; }, $jsonBreadcrumb));

                $this->load->library('Cleanhtml', 'clean');
                $this->clean->addAdditionalTags('<br>');
                // main item info
                $update = array(
                    'id_cat'                => $id_category,
                    'id'                    => $itemId,
                    'item_categories'       => $commaBreadcrumb,
                    'hs_tariff_number'      => cleanInput($_POST['hs_tariff_number']),
                    'title'                 => cleanInput($_POST['title']),
                    'year'                  => (int) $_POST['year'],
                    'out_of_stock_quantity' => $request->request->getInt('out_of_stock_quantity'),
                    'price'                 => ($price > 0?moneyToDecimal($price):0),
                    'final_price'           => ($final_price > 0?moneyToDecimal($final_price):0),
                    'discount'              => (int) round($final_discount, 12),
                    'item_length'           => $item_length,
                    'item_width'            => $item_width,
                    'item_height'           => $item_height,
                    'weight'                => (float) $_POST['weight'],
                    'p_country'             => $address_parts['country'],
                    'state'                 => $address_parts['state'],
                    'p_city'                => $address_parts['city'],
                    'item_zip'              => $address_parts['postal_code'],
                    'origin_country'        => (int) $_POST['origin_country'],
                    'origin_country_abr'    => $origin_country['abr'],
                    'description'           => $this->clean->sanitizeUserInput($_POST['description']),
                    'video'                 => $this->clean->sanitizeUserIframe($_POST['video']),
                    'changed'               => 0,
                    'tags'                  => implode(',', $tags),
                    'has_translation'       => 'no',
                    'min_sale_q'            => $minSaleQuantity,
                    'max_sale_q'            => $maxSaleQuantity,
                    'quantity'              => $itemQuantity,
                    'draft'                 => ($save_draft ? 1 : 0),
                    'update_date'           => (new DateTimeImmutable())->format(DB_DATE_FORMAT),
                    'is_distributor'        => $request->request->getInt('is_distributor'),
                    'is_out_of_stock'       => (int) ($itemQuantity < $minSaleQuantity),
                    'has_variants'          => !empty($request->request->get('properties')),
                );

                //if saving as draft
                if($save_draft){
                    $update['visible'] = 0;

                    //if item was not draft before this edit
                    if(0 == $item_info['draft'])
                    {
                        $daysExpire = config('draft_items_days_expire', 10);
                        $dateExpire = new DateTime();
                        $dateExpire->modify("+$daysExpire day");
                        $update['draft_expire_date'] = $dateExpire->format('Y-m-d');

                        $dataSystmess = [
                            'mess_code' => 'add_item_draft_type_save',
                            'id_users'  => [$item_info['id_seller']],
                            'replace'   => [
                                '[DAYS]'      => $daysExpire,
                                '[REQUEST_LINK]' => __SITE_URL . 'items/my?request=0',
                            ],
                            'systmess' => true,
                        ];

                        /** @var Notify_Model $notifyModel */
                        $notifyModel = model(Notify_Model::class);

                        $notifyModel->send_notify($dataSystmess);
                    }
                }else{

                    //if itme was draft and now is not
                    if (1 == $item_info['draft'])
                    {
                        $today = new DateTime();
                        $expirationDate = new DateTime($item_info['draft_expire_date']);
                        $interval = $expirationDate->diff($today);

                        //make featured if the item has only five days till expiration
                        if(config('draft_items_day_number_to_make_featured', 5) == $interval->days + 1)
                        {
                            $period = config('item_featured_draft_publish_period', 1);
                            $end_date = date_plus($period);

                            $insertFeatured = [
                                'featured_from_date' => (new \DateTime())->format('Y-m-d H:i:s'),
                                'auto_extend'	     => 0,
                                'create_date' 	     => date('Y-m-d'),
                                'end_date' 		     => $end_date,
                                'id_item' 		     => $itemId,
                                'status' 		     => 'active',
                                'extend' 	         => 0,
                                'price' 		     => 0,
                                'paid' 			     => 1,
                                'notice'             => json_encode([
                                    'add_date' => (new \DateTime())->format('Y-m-d H:i:s'),
                                    'add_by'   => $this->session->lname . ' ' . $this->session->fname,
                                    'notice'   => 'The item has been featured.',
                                ])
                            ];

                            $idFeatured = model(Items_Model::class)->set_feature_request($insertFeatured);

                            if (0 == $item_info['featured']) {
                                model(User_Statistic_Model::class)->set_users_statistic([
                                    $item_info['id_seller'] => ['active_featured_items' => 1],
                                ]);
                            }

                            $data_systmess = [
                                'mess_code' => 'feature_item_for_free',
                                'id_item'   => $idFeatured,
                                'id_users'  => [$item_info['id_seller']],
                                'replace'   => [
                                    '[ITEM_TITLE]' => cleanOutput($update['title']),
                                    '[ITEM_LINK]'  => makeItemUrl($itemId, $update['title']),
                                    '[END_DATE]'   => getDateFormat($end_date, null, 'j M, Y'),
                                    '[LINK]'       => __SITE_URL . 'items/my?filter_featured=1',
                                ],
                                'systmess' => true,
                            ];

                            model(Notify_Model::class)->send_notify($data_systmess);

                            $update['featured'] = 1;
                        }

                        $update['draft_expire_date'] = null;
                    }
                }

                if (!$update['is_out_of_stock']) {
                    $update['date_out_of_stock'] = null;
                }

                if ($item_info['video'] != $_POST['video'] && !empty($_POST['video'])) {
                    $this->load->library('videothumb');
                    $video_link = $this->videothumb->getVID($_POST['video']);
                    $new_video = $this->videothumb->process($_POST['video']);

                    if (isset($new_video['error'])) {
                        jsonResponse($new_video['error']);
                    }

                    $update['video_source'] = $video_link['type'];

                    $path = getImgPath('items.main', array('{ID}' => $itemId));
                    if (!is_dir($path))
                        mkdir($path, 0777);

                    $file_video[] = $new_video['image'];
                    $conditions = array(
                        'images' => $file_video,
                        'destination' => $path,
                        'resize' => '730xR'
                    );
                    $res = $this->upload->copy_images_new($conditions);

                    if (!empty($res['errors'])) {
                        jsonResponse($res['errors']);
                    }
                    @unlink($path . '/' . $item_info['video_image']);
                    @unlink($new_video['image']);

                    $update['video_image'] = $res[0]['new_name'];
                    $update['video'] = $_POST['video'];
                    $update['video_code'] = $new_video['v_id'];
                }

                if($has_additional_description){
                    $this->_update_items_descriptions(
                        $itemId,
                        (int)$_POST['additional_description_language'],
                        $this->clean->sanitizeUserInput($_POST['additional_description_text']),
                        (int)$_POST['additional_description_translate']
                    );
                }elseif(isset($_POST['additional_description_remove'])){
                    if(model('Items_Descriptions')->exist_descriptions($itemId)){
                        model('Items_Descriptions')->change_descriptions_by_item($itemId, array('status' => 'removed'));
                    }
                }

                $update['size'] = $update['item_length'].'x'.$update['item_width'].'x'.$update['item_height'];

                if (isset($_POST['unit_type'])) {
                    $update['unit_type'] = (int) $_POST['unit_type'];
                }

                $allowed_purchase_options = array(
                    'order_now' => 'sell_item',
                    'samples' => 'create_sample_order',
                    'offers' => 'manage_seller_offers',
                    'inquiry' => 'manage_seller_inquiries',
                    'estimate' => 'manage_seller_estimate',
                    'po' => 'manage_seller_po'
                );
                foreach($allowed_purchase_options as $purchase_option => $required_right){
                    $update[$purchase_option] = 0;
                    if(have_right($required_right) && in_array($purchase_option, $_POST['purchase_options'])){
                        $update[$purchase_option] = 1;
                    }
                }

                $seller_info = model('user')->getSimpleUser(privileged_user_id());
                if($seller_info['status'] != 'active'){
                    $update['visible'] = 0;

                    if($seller_info['status'] === 'restricted'){
                        unset($update['visible']);
                    }
                }

                /** @var Products_Photo_Model $productsPhotoModel */
                $productsPhotoModel = model(Products_Photo_Model::class);

                // Next we need to take our filesystem for temp directory
                /** @var FilesystemProviderInterface */
                $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);

                $tempPrefixer = $storageProvider->prefixer('temp.storage');
                $tempDisk = $storageProvider->storage('temp.storage');
                $publicDisk = $storageProvider->storage('public.storage');

                //region upload main photo
                if (isset($_POST['images_main']) && !empty($mainImageTempFullPath = $_POST['images_main'])) {
                    $mainParentImage = $request->request->get('parent');
                    $mainImageName = pathinfo($mainImageTempFullPath, PATHINFO_BASENAME);
                    $tempPathToFile = FilePathGenerator::makePathToUploadedFile($mainImageName);
                    $tempFolder = pathinfo($tempPathToFile, PATHINFO_DIRNAME) . DS;
                    $publicFolder = str_replace('{ID}', $itemId, config('img.items.main.relative_disk_path'));

                    try {
                        $publicDisk->write($publicFolder . $mainImageName, $tempDisk->read($tempPathToFile));
                    } catch (\Throwable $th) {
                        jsonResponse(translate('systmess_internal_server_error'));
                    }

                    if (!empty($mainImageThumbs = config("img.items.main.thumbs"))) {
                        $tempImagePath = pathinfo($mainImageTempFullPath, PATHINFO_DIRNAME);

                        foreach ($mainImageThumbs as $mainImageThumb) {
                            $thumbName = str_replace('{THUMB_NAME}', $mainImageName, $mainImageThumb['name']);

                            try {
                                $publicDisk->write($publicFolder . $thumbName, $tempDisk->read($tempFolder . $thumbName));
                            } catch (\Throwable $th) {
                                jsonResponse(translate('systmess_internal_server_error'));
                            }
                        }
                    }

                    if (!empty($old_photo = $this->items->get_item_main_photo($itemId))) {
                        $this->items->delete_item_photo($itemId, $old_photo['id']);

                        try {
                            $publicDisk->delete($publicFolder . $old_photo['photo_name']);

                            foreach ($mainImageThumbs ?: [] as $mainImageThumb) {
                                $thumbName = str_replace('{THUMB_NAME}', $old_photo['photo_name'], $mainImageThumb['name']);
                                $publicDisk->delete($publicFolder . $thumbName);
                            }
                        } catch (\Throwable $th) {
                            //not critical
                        }
                    }

                    $productsPhotoModel->insertOne([
                        'main_photo'    => 1,
                        'photo_name'    => $mainImageName,
                        'type_photo'    => 'portrait',
                        'sale_id'       => $itemId,
                    ]);
                }
                //endregion upload main photo

                //region remove photos
                if (!empty($_POST['images_remove'])){
                    $removedImagesIds = [];
                    foreach ((array) (request()->request->get('images_remove') ?: []) as $removedImageId) {
                        $removedImagesIds[] = (int) $removedImageId;
                    }

                    if (!empty($removedImagesIds)) {
                        $removedImages = $this->items->get_items_photos(['images_list' => implode(',', $removedImagesIds), 'id_item' => $itemId]);
                        $publicGalleryFolder = str_replace('{ID}', $itemId, config('img.items.photos.relative_disk_path'));
                        $galleryThumbs = config("img.items.photos.thumbs");

                        foreach ($removedImages ?: [] as $removedImage) {
                            if ((int) $removedImage['main_photo'] || ($removedImage['main_parent'] && empty($mainParentImage))) {
                                continue;
                            }

                            if ($this->items->delete_item_photo($itemId, $removedImage['id'])) {
                                try {
                                    $publicDisk->delete($publicGalleryFolder . $removedImage['photo_name']);

                                    foreach ($galleryThumbs ?: [] as $galleryThumb) {
                                        $thumbName = str_replace('{THUMB_NAME}', $removedImage['photo_name'], $galleryThumb['name']);
                                        $publicDisk->delete($publicGalleryFolder . $thumbName);
                                    }
                                } catch (\Throwable $th) {
                                    //not critical
                                }

                                // @TODO REMAKE ACTIVITY LOG TO MULTIPLE INSERT
                                // $context = get_user_activity_context();
                                // $context['item'] = array('id' => $itemId, 'name' => $update['title'], 'url' => makeItemUrl($itemId, $update['title']));
                                // $context['changes'] = array('old' => array('photo' => $good_images_remove_item), 'current' => array());
                                // $this->activity_logger->setOperationType(DELETE_IMAGE);
                                // $this->activity_logger->setResourceType(ITEM);
                                // $this->activity_logger->setResource($itemId);
                                // $this->activity_logger->info(model('activity_log_messages')->get_message(ITEM, DELETE_IMAGE), $context);
                            }
                        }
                    }
                }
                //endregion remove photos

                if (!empty($newImages = $_POST['images'])) {
                    $uploadedPhotos = [];
                    $galleryThumbs = config("img.items.photos.thumbs");
                    $totalDisponibleImages = (int) config("img.items.photos.limit");
                    $totalExistImages = (int) $productsPhotoModel->countAllBy([
                        'conditions' => [
                            'isMainPhoto'   => 0,
                            'itemId'        => $itemId,
                        ],
                    ]);

                    if ((count($newImages) + $totalExistImages) > $totalDisponibleImages) {
                        jsonResponse(translate('validation_cannot_upload_more_than_photos_messge', ['{{NUMBER}}' => $totalDisponibleImages]));
                    }

                    $publicGalleryFolder = str_replace('{ID}', $itemId, config('img.items.photos.relative_disk_path'));

                    foreach ($newImages as $newImage) {
                        $imageName = pathinfo($newImage, PATHINFO_BASENAME);
                        $tempPathToFile = FilePathGenerator::makePathToUploadedFile($imageName);
                        $tempFolder = pathinfo($tempPathToFile, PATHINFO_DIRNAME) . DS;

                        try {
                            if (!$tempDisk->fileExists($tempPathToFile)) {
                                continue;
                            }
                        } catch (\Throwable $th) {
                            continue;
                        }

                        try {
                            $publicDisk->write($publicGalleryFolder . $imageName, $tempDisk->read($tempPathToFile));
                        } catch (\Throwable $th) {
                            jsonResponse(translate('systmess_internal_server_error'));
                        }

                        foreach ($galleryThumbs ?: [] as $galleryThumb) {
                            $thumbName = str_replace('{THUMB_NAME}', $imageName, $galleryThumb['name']);

                            try {
                                $publicDisk->write($publicGalleryFolder . $thumbName, $tempDisk->read($tempFolder . $thumbName));
                            } catch (\Throwable $th) {
                                jsonResponse(translate('systmess_internal_server_error'));
                            }
                        }

                        $uploadedPhotos[] = [
                            'photo_name'    => $imageName,
                            'type_photo'    => 'portrait',
                            'sale_id'       => $itemId,
                        ];
                    }

                    if (!empty($uploadedPhotos)) {
                        $productsPhotoModel->insertMany($uploadedPhotos);
                    }
                }

                if (!empty($mainParentImage) && !empty($productsPhotoModel->countAllBy([
                    'scopes'    => [
                        'itemId'        => $itemId,
                        'photoName'     => $mainParentImage,
                        'isMainParent'  => false,
                    ],
                ]))) {
                    $productsPhotoModel->updateMany(
                        ['main_parent' => null],
                        [
                            'scopes' => [
                                'itemId'        => $itemId,
                                'isMainParent'  => true,
                            ]
                        ]
                    );

                    $productsPhotoModel->updateMany(
                        ['main_parent' => 1],
                        [
                            'scopes' => [
                                'itemId'    => $itemId,
                                'photoName' => $mainParentImage,
                            ]
                        ]
                    );
                }

                if (!$this->items->update_item($update)) {
                    jsonResponse('The changes have not been saved. Please try again later');
                }

                //if the item appears in stock, than we notify users about it
                if ($item_info['is_out_of_stock'] && !$update['is_out_of_stock']) {
                    /** @var Out_Of_Stock_Model $outOfStockModel */
                    $outOfStockModel = model(Out_Of_Stock_Model::class);

                    if (!empty($notifyUsers = $outOfStockModel->findAllBy([
                        'conditions'    => [
                            'itemId'        => $itemId,
                            'wasNotified'   => 0,
                        ]
                    ]))) {
                        $this->sendItemAvailable(
                            [
                                'id'    => $itemId,
                                'title' => $update['title'],
                            ],
                            array_column($notifyUsers, 'id_user')
                        );
                    }
                }

                //region item variants
                /** @var Items_Variants_Model $itemsVariantsModel */
                $itemsVariantsModel = model(Items_Variants_Model::class);

                /** @var Items_Variants_Properties_Model $itemsPropertiesModel */
                $itemsPropertiesModel = model(Items_Variants_Properties_Model::class);

                $currentItemVariants = $itemsVariantsModel->getItemVariants($itemId);
                $currentVariantsIds = (array) array_column($currentItemVariants['variants'], 'id');
                $currentPropertiesIds = (array) array_column($currentItemVariants['properties'], 'id');

                $incomingPropertiesIds = $incomingVariantsIds = [];

                if (!empty($variantProperties = (array) $request->request->get('properties'))) {

                    /** @var Items_Variants_Properties_Options_Model $itemsPropertiesOptionsModel */
                    $itemsPropertiesOptionsModel = model(Items_Variants_Properties_Options_Model::class);

                    /** @var Items_Variants_Properties_Relation_Model $itemsVariantsPropertiesRelationModel */
                    $itemsVariantsPropertiesRelationModel = model(Items_Variants_Properties_Relation_Model::class);

                    $propertyPriority = 1;
                    $propertyOptionsIds = [];

                    /** @var \Money\Money $minPrice */
                    $minPrice = null;
                    /** @var \Money\Money $minFinalPrice */
                    $minFinalPrice = null;
                    $minDiscount = 0;

                    foreach ($variantProperties as $variantPropertyKey => $variantProperty) {
                        $propertyId = $variantProperty['id'];

                        if ('new' === $variantProperty['type']) {
                            $propertyId = $itemsPropertiesModel->insertOne([
                                'priority'  => $propertyPriority,
                                'id_item'   => $itemId,
                                'name'      => $variantProperty['name'],
                            ]);
                        } else {
                            $itemsPropertiesModel->updateOne(
                                $propertyId,
                                [
                                    'priority' => $propertyPriority,
                                ]
                            );
                        }

                        foreach ((array) $variantProperty['options'] as $optionKey => $propertyOption) {
                            $optionId = (int) $propertyOption['id'];

                            if ('new' === $variantProperty['type']) {
                                $optionId = (int) $itemsPropertiesOptionsModel->insertOne([
                                    'id_property'   => $propertyId,
                                    'name'          => $propertyOption['name'],
                                ]);
                            }

                            $propertyOptionsIds[$propertyId][$optionKey] = $optionId;
                        }

                        $incomingPropertiesIds[$variantPropertyKey] = $propertyId;
                        $propertyPriority++;
                    }

                    foreach ((array) $request->get('combinations') as $itemVariant) {
                        $variantId = $itemVariant['id'];

                        $price = priceToUsdMoney($itemVariant['price'] ?: 0);
                        $finalPrice = priceToUsdMoney($itemVariant['final_price'] ?: 0);
                        $finalPrice = $finalPrice->isZero() ? $price : $finalPrice;
                        $discount = $finalPrice->isZero() ? 0 : (1 - $finalPrice->ratioOf($price)) * 100;

                        //compare float numbers
                        if (null === $minFinalPrice || $finalPrice->lessThan($minFinalPrice) || ($finalPrice->equals($minFinalPrice) && $price->greaterThan($minPrice))) {
                            //we save price values of variant with minimal finalPrice
                            $minFinalPrice = $finalPrice;
                            $minPrice = $price;
                            $minDiscount = $discount;
                        }

                        if ('new' === $itemVariant['type']) {
                            $variantId = $itemsVariantsModel->insertOne([
                                'final_price'   => moneyToDecimal($finalPrice),
                                'discount'      => (int) round($discount, 12),
                                'quantity'      => $itemVariant['quantity'],
                                'id_item'       => $itemId,
                                'price'         => moneyToDecimal($price),
                                'image'         => $itemVariant['img'],
                            ]);

                            foreach ((array) $itemVariant['variants'] as $variantOption) {
                                $itemsVariantsPropertiesRelationModel->insertOne([
                                    'id_property_option'    => $propertyOptionsIds[$incomingPropertiesIds[$variantOption['property_id']]][$variantOption['option_id']],
                                    'id_variant'            => $variantId,
                                ]);
                            }
                        }

                        $incomingVariantsIds[] = $variantId;
                    }

                    $productsModel->updateOne(
                        $itemId,
                        $variantUpdate = [
                            'is_out_of_stock'   => 0,
                            'final_price'       => moneyToDecimal($minFinalPrice),
                            'discount'          => (int) round($minDiscount, 12),
                            'price'             => moneyToDecimal($minPrice),
                        ]
                    );

                    $update = array_merge($update, $variantUpdate);
                }

                if (!empty($deletedVariantsIds = array_diff($currentVariantsIds, $incomingVariantsIds))) {
                    $itemsVariantsModel->deleteAllBy([
                        'conditions' => [
                            'ids'    => $deletedVariantsIds,
                        ],
                    ]);
                }

                if (!empty($deletedPropertiesIds = array_diff($currentPropertiesIds, $incomingPropertiesIds))) {
                    $itemsPropertiesModel->deleteAllBy([
                        'conditions' => [
                            'ids'    => $deletedPropertiesIds,
                        ],
                    ]);
                }

                if (!empty($good_images_remove)) {
                    $itemsVariantsModel->updateMany(
                        [
                            'image' => 'main',
                        ],
                        [
                            'conditions' => [
                                'itemId'        => $itemId,
                                'variantImages' => array_column($good_images_remove, 'photo_name'),
                            ],
                        ]
                    );
                }
                //endregion item variants

                $update_search_info = array(
                    'id' => $itemId,
                    'search_info' => $this->items->get_search_info($itemId)
                );
                $this->items->update_item($update_search_info);

                // CREATE SNAPSHOT
                if (!$save_draft) {
                    $this->_create_item_snapshot($itemId);
                }

                // Detect if item need to be moderated by changes
                $needModerateItem = isset($uploadedPhotos)
                    || isset($mainImageName)
                    || array_column($itemOldSpecs, 'p_value', 'p_name') != array_column($u_attrs_insert, 'p_value', 'p_name');

                    if (!$needModerateItem ) {
                        foreach ($this->moderateValues as $itemField) {
                            if ($item_info[$itemField] !== $update[$itemField]) {
                                $needModerateItem = true;
                                break;
                            }
                        }
                    }

                /** @var Elasticsearch_Items $itemsElasticSearchModel */
                $itemsElasticSearchModel = model(Elasticsearch_Items_Model::class);

                if ($needModerateItem) {
                    /** @var Moderation_Model $itemsModerationModel */
                    $itemsModerationModel = model(Moderation_Model::class);
                    $itemsModerationModel->immoderate($itemId, TYPE_ITEM);

                    //Remove item from ElasticSearch
                    $itemsElasticSearchModel->removeItemById((int) $itemId);
                } else {
                    $updatedItem = $this->indexedProductDataProvider->getItemForIndex($itemId);
                    if (!empty($updatedItem)) {
                        try {
                            $itemsElasticSearchModel->update($itemId, $updatedItem);
                        } catch (ItemNotFoundException $exception) {
                            $itemsElasticSearchModel->index($itemId);
                        }
                    }
                }

                if(!$save_draft) {
                    library('wall')->add([
                        'type'      => 'item',
                        'operation' => 'edit',
                        'id_item'   => $itemId,
                    ]);
                }

                array_walk($item_info, function (&$value, $key) {
                    if (in_array($key, array('final_price', 'item_height', 'item_length', 'item_width', 'price', 'weight'))) {
                        $value = (float) $value;
                    }
                });

                // UPDATE ACTIVITY LOG
                list($old, $current) = array_unified_diff($item_info, $update);
                $context = get_user_activity_context();
                $context['item'] = array('id' => $itemId, 'name' => $update['title'], 'url' => __SITE_URL . 'item/' . strForURL($update['title']) . "-{$itemId}");
                if(!empty($old) && !empty($current)) {
                    $context['changes'] = compact('old', 'current');
                }

                $this->activity_logger->setOperationType(EDIT);
                $this->activity_logger->setResourceType(ITEM);
                $this->activity_logger->setResource($itemId);
                $this->activity_logger->info(model('activity_log_messages')->get_message(ITEM, EDIT), $context);

                $success_message = 'The item information has been successfully updated.';
                if ($save_draft) {
                    $success_message = translate('systmess_success_edit_draft_item');
                } elseif ('pending' === $seller_info['status']) {
                    $success_message .= ' Please, log in to your account upon activation to make it visible.';
                }

                // Success mesage
                if ($needModerateItem) {
                    $success_message = translate('systmess_item_moderation_imoderate');
                }

                $success_data = [];
                if($save_draft){
                    $success_data['images'] = $this->items->get_items_photos(array('id_item' => $itemId, 'main_photo' => 0, 'order_by' => 'id ASC' ));

                    foreach($success_data['images'] as $photos_key => $photos_item){
                        $success_data['images'][$photos_key]['photo_url'] = getDisplayImageLink(array('{ID}' => $itemId, '{FILE_NAME}' => $photos_item['photo_name']), 'items.photos', array('thumb_size' => 1));
                    }
                }

                if($update['draft'] === 0){
                    model('complete_profile')->update_user_profile_option(privileged_user_id(), 'company_items');
                    /** @var TinyMVC_Library_Auth $authenticationLibrary */
                    $authenticationLibrary = library(TinyMVC_Library_Auth::class);
                    $authenticationLibrary->setUserCompleteProfile(privileged_user_id());
                }

                model(Crm_Model::class)->create_or_update_record($seller_info['idu']);

                $update['moderate_request'] = $needModerateItem;

                $itemDiff = array_udiff_assoc($update, $item_info, function ($newValue, $oldValue) {
                    if ($oldValue instanceof Money) {
                        // If old value is of type Money\Money and new one is not
                        // we need to transform new value in the object of Money\Money
                        // to properly compare them
                        if (!$newValue instanceof Money) {
                            $newValue = \priceToUsdMoney($newValue);
                        }

                        return !$newValue->equals($oldValue) ? 1 : -1;
                    }

                    return $newValue != $oldValue ? 1 : -1;
                });

                // Fire event about product update
                $this->eventBus->dispatch(new ProductWasUpdatedEvent($itemId, with($itemDiff, function (array $diff) {
                    if (isset($diff['price']) && !($diff['price'] instanceof Money)) {
                        $diff['price'] = \priceToUsdMoney($diff['price']);
                    }
                    if (isset($diff['final_price']) && !($diff['final_price'] instanceof Money)) {
                        $diff['final_price'] = \priceToUsdMoney($diff['final_price']);
                    }

                    return $diff;
                })));

                jsonResponse($success_message, 'success', $success_data);
            break;
            case 'stop_auto_extend_feature_items':
                checkPermisionAjax('items_administration');

                $id_featured = (int) $_POST['id_featured'];
                if (empty($id_featured) || empty(model(Items_Model::class)->get_featured($id_featured))) {
                    jsonResponse(translate('systmess_error_featured_item_not_exist'));
                }

                if(!model(Items_Model::class)->updateFeaturedItem($id_featured, array('auto_extend' => 0))){
                    jsonResponse(translate('systmess_internal_server_error'));
                }

                jsonResponse(translate('systmess_success_auto_extend_was_successfully_stopped'), 'success');
            break;
            case 'un_feature_item':
                checkPermisionAjax('items_administration');

                $id_featured = (int) $_POST['id_featured'];
                if (empty($id_featured) || empty($featured_item = model(Items_Model::class)->get_featured($id_featured))) {
                    jsonResponse(translate('systmess_error_featured_item_not_exist'));
                }

                if (empty($item = model(Items_Model::class)->get_item((int) $featured_item['id_item'], 'id_seller'))) {
                    jsonResponse(translate('systmess_error_item_does_not_exist'));
                }

                if (!model(Items_Model::class)->updateFeaturedItem($id_featured, array('status' => 'expired'))) {
                    jsonResponse('Failed to update featured status in table item_featured');
                }

                if (!model('items')->change_items_feature_by_list(array($featured_item['id_item']))) {
                    jsonResponse('Failed to update featured status in table items');
                }

                if (!model(User_Statistic_Model::class)->set_users_statistic(array($item['id_seller'] => array('active_featured_items' => -1)))) {
                    jsonResponse('Failed to update active_featured_items in table user_statistic');
                }

                jsonResponse('Success', 'success');

            break;
            case 'prepare_item':
                checkPermisionAjax('manage_personal_items,moderate_content,disable_item');

                $idItem = request()->request->getInt('id');

                /** @var Items_Model $itemModel **/
                $itemModel = model(Items_Model::class);
                if(!$itemModel->item_exist($idItem)){
                    jsonResponse('No such item');
                }

                /** @var Elasticsearch_Items_Model $esItemModel **/
                $esItemModel = model(Elasticsearch_Items_Model::class);
                $esItemModel->index($idItem);

                //region Notify users about availability
                $itemDetails = $itemModel->get_item($idItem);
                if(!$itemDetails['is_out_of_stock']){
                    $notifyUsers = array_column($itemModel->getOutOfStockNotifyByItem($idItem), 'id_user');
                    if(!empty($notifyUsers)){
                        $this->sendItemAvailable($itemDetails, $notifyUsers);
                    }
                }
                //endregion Notify users about availability

                jsonResponse('The item was succesfully indexed', 'success');
            break;
            case 'save_draft':
                checkPermisionAjax('manage_draft_items');
                is_allowed('freq_allowed_user_operations');

                $this->create_item_draft(
                    (int) privileged_user_id(),
                    (int) arrayGet($_POST, 'id_upload'),
                    (array) arrayGet($_POST, 'xls_columns', array()),
                    (array) arrayGet($_POST, 'ep_columns', array())
                );

                break;
            case 'download_draft_example':
                checkPermisionAjax('manage_draft_items');

                $this->download_drafts_example();

                break;
            case 'upload_draft_list':
                checkPermisionAjax('manage_draft_items');

                $this->upload_item_drafts_list((int) privileged_user_id(), arrayGet($_FILES, 'files', array()), uri()->segment(4));

                break;
            case 'save_draft_config':
                checkPermisionAjax('manage_draft_items');

                is_allowed('freq_allowed_items_draft_save_config');

                $this->save_bulk_upload_configuration(
                    (int) privileged_user_id(),
                    array_map('cleanInput', arrayGet($_POST, 'xls_columns', array())),
                    arrayGet($_POST, 'ep_columns', array()),
                    filter_var(arrayGet($_POST, 'first_row'), FILTER_VALIDATE_BOOLEAN)
                );

                break;
            case 'show_drafts_configurations':
                checkPermisionAjax('manage_draft_items');

                /** @var FilesystemProviderInterface */
                $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                $publicPrefixer = $storageProvider->prefixer('temp.storage');

                $this->show_bulk_upload_configurations(
                    (int) privileged_user_id(),
                    arrayGet($_POST, 'file', array()),
                    arrayGet($_POST, 'file.name'),
                    $publicPrefixer->prefixPath(FilePathGenerator::makePathToUploadedFile(arrayGet($_POST, 'file.name'))),
                    filter_var(arrayGet($_POST, 'user_config'), FILTER_VALIDATE_BOOLEAN),
                    filter_var(arrayGet($_POST, 'first_row'), FILTER_VALIDATE_BOOLEAN)
                );

                break;
            case 'add_description_translation':
                checkPermisionAjax('items_administration');

                $validator_rules = array(
                    array(
                        'field' => 'translation',
                        'label' => 'Translation',
                        'rules' => array('required' => '', 'html_max_len[20000]' => ''),
                    )
                );

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $id_item = (int) $_POST['id_item'];
                if (
                    empty($id_item) ||
                    empty($description = model('Items_Descriptions')->get_descriptions_by_item($id_item))
                ) {
                    jsonResponse('Error: The item description does not exist.');
                }

                if(!in_array($description['status'], array('need_translate', 'translated'))){
                    jsonResponse('Error: You can not add translation on the item description.');
                }

                $this->load->library('Cleanhtml', 'clean');
                $this->clean->addAdditionalTags('<br>');

                $item_description = $this->clean->sanitizeUserInput($_POST['translation']);
                model('Items_Descriptions')->change_descriptions_by_item(
                    $id_item,
                    array(
                        'need_translate' => 2,
                        'status' => 'translated'
                    )
                );

                model('Items')->update_item(array('id' => $id_item, 'description' => $item_description));

                jsonResponse('The changes has been successfully saved.', 'success');
                break;

            case 'add_to_archive':
                checkPermisionAjax('manage_personal_items');

                /** @var Products_Model $productsModel */
                $productsModel = model(Products_Model::class);

                if (
                    empty($itemId = request()->request->getInt('item'))
                    || empty($item = $productsModel->findOne($itemId))
                    || id_session() !== $item['id_seller']
                ) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                if (
                    $item['visible']
                    && !$item['draft']
                    && 1 !== $item['blocked']
                ) {
                    jsonResponse(translate('systmess_error_add_item_to_archive'), 'warning');
                }

                if (!$productsModel->updateOne($itemId, ['is_archived' => 1])) {
                    jsonResponse(translate('systmess_internal_server_error'));
                }

                jsonResponse(translate('systmess_success_archived_item'), 'success');

                break;
            case 'return_from_archive':
                checkPermisionAjax('manage_personal_items');

                /** @var Products_Model $productsModel */
                $productsModel = model(Products_Model::class);

                if (
                    empty($itemId = request()->request->getInt('item'))
                    || empty($item = $productsModel->findOne($itemId))
                    || id_session() !== $item['id_seller']
                    || !$item['is_archived']
                ) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                if (!$productsModel->updateOne($itemId, ['is_archived' => 0])) {
                    jsonResponse(translate('systmess_internal_server_error'));
                }

                jsonResponse(translate('systmess_success_returned_item_from_archive'), 'success');

                break;

            default:
                jsonResponse('The provided path is not found on this server.');

                break;

        }
    }

    private function _update_items_descriptions($id_item, $language, $description, $translate = 0) {
        $notify_admin = false;
        if(model('Items_Descriptions')->exist_descriptions($id_item)){
            $desc_detail = model('Items_Descriptions')->get_descriptions_by_item($id_item);

            $update = array(
                'item_description'  => $description,
                'descriptions_lang' => $language,
            );

            if((int)$desc_detail['need_translate'] != 2){
                $update['need_translate'] = $translate;
                $update['status'] = (((int)$translate == 1)?'need_translate':'init');
            }else{
                $update['status'] = 'translated';
            }

            model('Items_Descriptions')->change_descriptions_by_item(
                $id_item,
                $update
            );

            if(
                (
                    $desc_detail['item_description'] != $description ||
                    (int) $desc_detail['descriptions_lang'] === (int) $language
                )
                && (int) $translate === 1
                && (int) $desc_detail['need_translate'] === 0
            ){
                $notify_admin = true;
            }
        }else{
            model('Items_Descriptions')->insert_descriptions(
                array(
                    'id_item'           => $id_item,
                    'item_description'  => $description,
                    'descriptions_lang' => $language,
                    'need_translate'    => $translate,
                    'create_date'       => date('Y-m-d H:i:s'),
                    'status'            => (((int)$translate == 1)?'need_translate':'init')
                )
            );

            $notify_admin = (int) $translate === 1;
        }

        if($notify_admin){
            $notify_ep_managers = model('user')->get_users_by_additional_right('translate_item_description_notification');
            if(!empty($notify_ep_managers)){

				$data_systmess = [
					'mess_code' => 'user_added_translation',
					'id_users'  => array_column($notify_ep_managers, 'idu'),
					'replace'   => [
						'[USER]'      => cleanOutput(user_name_session()),
						'[USER_LINK]' => getMyProfileLink(),
						'[ITEM_LINK]' => __SITE_URL . 'items/administration/?id_item=' . $id_item
					],
					'systmess' => true
				];

                model(Notify_Model::class)->send_notify($data_systmess);
            }
        }
    }

    public function ajax_item_upload_images() {
        checkIsAjax();
        checkIsLogged();
        checkGroupExpire('ajax');
        checkPermisionAjax('manage_personal_items');

        is_allowed('freq_item_temp_image_uploading');

        /** @var UploadedFile $uploadedFile */
        $uploadedFile = request()->files->get('files');

        if (null === $uploadedFile) {
            jsonResponse(translate('systmess_error_select_file_to_upload'));
        }

        if (is_array($uploadedFile) || !$uploadedFile->isValid()) {
			jsonResponse(translate('validation_invalid_file_provided'));
		}

        // Next we need to take our filesystem for temp directory
        /** @var FilesystemProviderInterface */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $tempDisk = $storageProvider->storage('temp.storage');
        $tempPrefixer = $storageProvider->prefixer('temp.storage');

        /** @var LegacyImageHandler $imageHandler */
        $imageHandler = $this->getContainer()->get(LibraryLocator::class)->get(TinyMVC_Library_Image_intervention::class);
        // Given that we need to validate the file and it would take a long time
        // to write the new proper validation we can only use what we have.
        try {
            $imageHandler->assertImageIsValid(
                $imageHandler->makeImageFromFile($uploadedFile),
                config("img.items.photos.rules"),
                $uploadedFile->getClientOriginalName()
            );
        } catch (ValidationException $e) {
            jsonResponse(
                \array_map(
                    fn (ConstraintViolationInterface $violation) => $violation->getMessage(),
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
        $fileName = sprintf('%s.%s', \bin2hex(\random_bytes(16)), 'jpg');
        $pathToFile = FilePathGenerator::makePathToUploadedFile($fileName);
        $relativeFolderPath = pathinfo($pathToFile, PATHINFO_DIRNAME);
        $sourcePath = $tempPrefixer->prefixPath($pathToFile);
        $folderPath = pathinfo($sourcePath, PATHINFO_DIRNAME);

        // And write file there
        try {
            $tempDisk->write($pathToFile, $uploadedFile->getContent());
        } catch (UnableToWriteFile $e) {
            jsonResponse(translate('validation_images_upload_fail'));
        }

        $imageModule = 'items.photos';
        $thumbs = config("img.{$imageModule}.thumbs");

        /** @var TinyMVC_Library_Image_intervention $interventionImageLibrary */
        $interventionImageLibrary = library(TinyMVC_Library_Image_intervention::class);

        $result = $interventionImageLibrary->image_processing(
            [
                'tmp_name'  => $sourcePath,
                'name'      => \basename($sourcePath),
            ],
            [
                'use_original_name' => true,
                'destination'       => $folderPath,
                'handlers'          => [
                    'create_thumbs' => $thumbs,
                    'watermark'     => config("img.{$imageModule}.watermark"),
                    'resize'        => config("img.{$imageModule}.resize"),
                ],
            ]
        );

        if (!empty($result['errors'])) {
            jsonResponse($result['errors']);
        }

        $uploadedFiles = [];
        foreach ($result as $key => $item) {
            $uploadedFiles[] = [
                'id_picture'    => $key,
                'photo_name'    => $item['new_name'],
                'thumb'         => $tempDisk->url($relativeFolderPath . DS . getTempImgThumb($imageModule, 1, $item['new_name'])),
                'name'          => $item['new_name'],
                'path'          => $tempDisk->url($relativeFolderPath . DS . $item['new_name']),
                'orig_path'     => $tempDisk->url($relativeFolderPath . DS . getTempImgThumb($imageModule, 4, $item['new_name'])),
            ];
        }

        jsonResponse(
            'Image was successfully uploaded.',
            'success',
            [
                'files' => $uploadedFiles
            ],
        );
    }

    public function ajax_add_item_delete_photo() {
        checkIsAjax();
		checkPermisionAjax('manage_personal_items');

        if (empty($fileName = request()->request->get('file'))) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }

        /** @var FilesystemProviderInterface */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);

        try {
            $storageProvider->storage('temp.storage')->deleteDirectory(pathinfo(FilePathGenerator::makePathToUploadedFile($fileName), PATHINFO_DIRNAME));
        } catch (\Throwable $th) {
            // nothing critical
        }

		jsonResponse('','success');
	}

    function ajax_item_upload_main_photo()
    {
        checkIsAjax();
        checkIsLogged();
        checkPermisionAjax('manage_personal_items');

        /** @var UploadedFile $uploadedFile */
        $uploadedFile = request()->files->get('files');

        if (null === $uploadedFile) {
            jsonResponse(translate('systmess_error_select_file_to_upload'));
        }

        if (is_array($uploadedFile) || !$uploadedFile->isValid()) {
			jsonResponse(translate('validation_invalid_file_provided'));
		}

        // Next we need to take our filesystem for temp directory
        /** @var FilesystemProviderInterface */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $tempDisk = $storageProvider->storage('temp.storage');
        $tempPrefixer = $storageProvider->prefixer('temp.storage');
        $mainImageConfigs = config('img.items.main');

        /** @var LegacyImageHandler $imageHandler */
        $imageHandler = $this->getContainer()->get(LibraryLocator::class)->get(TinyMVC_Library_Image_intervention::class);
        // Given that we need to validate the file and it would take a long time
        // to write the new proper validation we can only use what we have.
        try {
            $imageHandler->assertImageIsValid(
                $imageHandler->makeImageFromFile($uploadedFile),
                $mainImageConfigs['rules'] ?: []
            );
        } catch (ValidationException $e) {
            jsonResponse(
                \array_map(
                    fn (ConstraintViolationInterface $violation) => $violation->getMessage(),
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
        $fileName = sprintf('%s.%s', \bin2hex(\random_bytes(16)), 'jpg');
        $pathToFile = FilePathGenerator::makePathToUploadedFile($fileName);
        $sourcePath = $tempPrefixer->prefixPath($pathToFile);

        // And write file there
        try {
            $tempDisk->write($pathToFile, $uploadedFile->getContent());
        } catch (UnableToWriteFile $e) {
            jsonResponse(translate('validation_images_upload_fail'));
        }

        /** @var TinyMVC_Library_Image_intervention $interventionImageLibrary */
        $interventionImageLibrary = library(TinyMVC_Library_Image_intervention::class);

        $result = $interventionImageLibrary->image_processing(
            [
                'tmp_name'  => $sourcePath,
                'name'      => \basename($sourcePath),
            ],
            [
                'use_original_name' => true,
                'destination'       => pathinfo($sourcePath, PATHINFO_DIRNAME),
                'handlers'          => [
                    'create_thumbs' => $mainImageConfigs['thumbs'],
                    'watermark'     => $mainImageConfigs['watermark'],
                    'resize'        => $mainImageConfigs['resize'],
                ],
            ]
        );

        if (!empty($result['errors'])) {
            jsonResponse($result['errors']);
        }

        $pathToImage = $tempDisk->url($pathToFile);

        jsonResponse(
            'Main photo was successfully uploaded.',
            'success',
            [
                "tmp_url"   => $pathToImage,
                "thumb"     => $pathToImage,
                "path"      => $pathToImage,
                "name"      => $result[0]['new_name'],
                "nonce"     => Crypto::sign($sourcePath, $this->getMasterKey()->getSecretKey())
            ]
        );
    }

    public function export_items()
    {
        if (!have_right('manage_content')) {
            return;
        }

        $excel = new Spreadsheet();
        $excel->setActiveSheetIndex(0);
        $activeSheet = $excel->getActiveSheet();
        $activeSheet->setTitle('EP Products');

        $rowIndex = 1;

        //region first row
        $activeSheet->getColumnDimension('A')->setWidth(10);
        $activeSheet->setCellValue("A{$rowIndex}", 'Id');

        $activeSheet->getColumnDimension('B')->setWidth(40);
        $activeSheet->setCellValue("B{$rowIndex}", 'Title');

        $activeSheet->getColumnDimension('C')->setWidth(60);
        $activeSheet->setCellValue("C{$rowIndex}", 'Description');

        $activeSheet->getColumnDimension('D')->setWidth(50);
        $activeSheet->setCellValue("D{$rowIndex}", 'Link');

        $activeSheet->getColumnDimension('E')->setWidth(50);
        $activeSheet->setCellValue("E{$rowIndex}", 'Image Link');

        $activeSheet->getColumnDimension('F')->setWidth(20);
        $activeSheet->setCellValue("F{$rowIndex}", 'Availability');

        $activeSheet->getColumnDimension('G')->setWidth(20);
        $activeSheet->setCellValue("G{$rowIndex}", 'Price');

        $activeSheet->getColumnDimension('H')->setWidth(20);
        $activeSheet->setCellValue("H{$rowIndex}", 'Sale Price');

        $activeSheet->getColumnDimension('I')->setWidth(60);
        $activeSheet->setCellValue("I{$rowIndex}", 'Product Type');

        $activeSheet->getColumnDimension('J')->setWidth(40);
        $activeSheet->setCellValue("J{$rowIndex}", 'Brand');

        $activeSheet->getColumnDimension('K')->setWidth(15);
        $activeSheet->setCellValue("K{$rowIndex}", 'Condition');

        $activeSheet->getColumnDimension('L')->setWidth(10);
        $activeSheet->setCellValue("L{$rowIndex}", 'Adult');

        $activeSheet->getColumnDimension('M')->setWidth(15);
        $activeSheet->setCellValue("M{$rowIndex}", 'Age Group');

        $activeSheet->getRowDimension($rowIndex)->setRowHeight(20);

        $activeSheet->getStyle("A{$rowIndex}:M{$rowIndex}")
            ->getFont()
            ->setSize(12)
            ->setBold(true);

        $activeSheet->freezePane("M2");

        //endregion first row

        //region get items
        /** @var Products_Model $productsModel */
        $productsModel = model(Products_Model::class);

        $queryConditions = dtConditions(
            request()->query->all(),
            [
                ['as' => 'categoryIds',         'key' => 'parent',                      'type'  => function ($categoryId) {
                    /** @var Categories_Model $categoriesModel */
                    $categoriesModel = model(Categories_Model::class);

                    if (
                        empty($categoryId = (int) $categoryId)
                        || empty($category = $categoriesModel->findOne($categoryId))
                    ) {
                        return null;
                    }

                    return array_merge([$categoryId], array_filter($category['cat_childrens']));
                }],
                ['as' => 'keywords',            'key' => 'search_by_keywords',          'type' => 'trim'],
                ['as' => 'featured',            'key' => 'featured',                    'type' => 'int'],
                ['as' => 'highlighted',         'key' => 'highlight',                   'type' => 'int'],
                ['as' => 'partneredItem',       'key' => 'partnered_item',              'type' => 'int'],
                ['as' => 'visible',             'key' => 'visible',                     'type' => 'int'],
                ['as' => 'isBlocked',           'key' => 'blocked',                     'type' => 'int'],
                ['as' => 'draft',               'key' => 'draft',                       'type' => 'int'],
                ['as' => 'sellerId',            'key' => 'seller',                      'type' => 'int'],
                ['as' => 'id',                  'key' => 'id_item',                     'type' => 'int'],
                ['as' => 'countryId',           'key' => 'country',                     'type' => 'int'],
                ['as' => 'cityId',              'key' => 'city',                        'type' => 'int'],
                ['as' => 'createdFromDateGte',  'key' => 'start_from',                  'type' => 'getDateFormat:m/d/Y,Y-m-d'],
                ['as' => 'createdFromDateLte',  'key' => 'start_to',                    'type' => 'getDateFormat:m/d/Y,Y-m-d'],
                ['as' => 'draftExpireDate',     'key' => 'expire',                      'type' => 'getDateFormat:Y-m-d,Y-m-d'],
                ['as' => 'updatedFromDateGte',  'key' => 'update_from',                 'type' => 'getDateFormat:m/d/Y,Y-m-d'],
                ['as' => 'updatedFromDateLte',  'key' => 'update_to',                   'type' => 'getDateFormat:m/d/Y,Y-m-d'],
                ['as' => 'descriptionStatus',   'key' => 'translation_status',          'type' => fn ($status) => ProductDescriptionStatus::tryFrom($status)],
                ['as' => 'searchByNameOrEmail', 'key' => 'search_by_username_email',    'type' => 'cleanInput'],
                ['as' => 'searchByCompanyName', 'key' => 'search_by_company',           'type' => 'cleanInput'],
                ['as' => 'fakeUser',            'key' => 'fake_item',                   'type' => 'bool|int'],
                ['as' => 'archived',            'key' => 'archived',                    'type' => 'int'],
            ]
        );

        $products = $productsModel->findAllBy([
            'conditions'    => $queryConditions,
            'with'          => [
                'category',
                'sellerCompany',
                'mainPhoto',
            ],
            'joins'         => array_filter([
                isset($queryConditions['descriptionStatus']) ? 'descriptions' : null,
                isset($queryConditions['searchByNameOrEmail']) || isset($queryConditions['fakeUser']) ? 'seller' : null,
                isset($queryConditions['searchByCompanyName']) ? 'sellerCompany' : null,
            ]),
        ]);
        //endregion get items

        /** @var TinyMVC_Library_Cleanhtml $cleanHtmlLibrary */
        $cleanHtmlLibrary = library(TinyMVC_Library_Cleanhtml::class);

        //region main content
        foreach ($products as $product) {
            $rowIndex++;

            //[id]
            $activeSheet->setCellValueExplicit("A{$rowIndex}", $product['id'], DataType::TYPE_NUMERIC);
            //[title]
            $activeSheet->setCellValueExplicit("B{$rowIndex}", ucfirst(strtolower(substr($product['title'], 0, 150))), DataType::TYPE_STRING);
            //[description]
            $activeSheet->setCellValueExplicit("C{$rowIndex}", $cleanHtmlLibrary->sanitize($product['description']), DataType::TYPE_STRING);
            //[link]
            $activeSheet->setCellValueExplicit("D{$rowIndex}", makeItemUrl($product['id'], $product['title']), DataType::TYPE_STRING);
            //[image_link]
            $activeSheet->setCellValueExplicit("E{$rowIndex}", getDisplayImageLink(['{ID}' => $product['id'], '{FILE_NAME}' => $product['main_photo']['photo_name']], 'items.main'), DataType::TYPE_STRING);
            //[availability]
            $activeSheet->setCellValueExplicit("F{$rowIndex}", $product['is_out_of_stock'] ? 'out_of_stock' : 'in_stock', DataType::TYPE_STRING);
            //[price]
            $activeSheet->setCellValueExplicit("G{$rowIndex}", moneyToDecimal($product['price']) . ' USD', DataType::TYPE_STRING);
            //[sale_price]
            $activeSheet->setCellValueExplicit("H{$rowIndex}", moneyToDecimal($product['final_price']) . ' USD', DataType::TYPE_STRING);
            //[product_type]
            $activeSheet->setCellValueExplicit("I{$rowIndex}", implode(' > ', array_merge(...(array) $product['category']['breadcrumbs'])), DataType::TYPE_STRING);
            //[brand]
            $activeSheet->setCellValueExplicit("J{$rowIndex}", decodeCleanInput($product['seller_company']['name_company']), DataType::TYPE_STRING);
            //[condition]
            $activeSheet->setCellValueExplicit("K{$rowIndex}", 'new', DataType::TYPE_STRING);
            //[adult]
            $activeSheet->setCellValueExplicit("L{$rowIndex}", $product['category']['is_restricted'] ? 'yes' : 'no', DataType::TYPE_STRING);
            //[age_group]
            $activeSheet->setCellValueExplicit("M{$rowIndex}", 'adult', DataType::TYPE_STRING);
            // here be dragons
        }
        //endregion main content

        $activeSheet->getStyle("A1:M{$rowIndex}")
            ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="EP products ' . date('Y-m-d') . '.xlsx"');
		header('Cache-Control: max-age=0');
		$objWriter = IOFactory::createWriter($excel, 'Xlsx');
        $objWriter->save('php://output');
    }

    function popup_forms() {
        checkIsAjax();
        checkIsLoggedAjaxModal();

        $this->_load_main();
        $op = $this->uri->segment(3);
        $id = (int) $this->uri->segment(4);

        switch ($op) {
            case 'bills_list':
                // CHECK USER RIGHTS - Manage bills
                if (!have_right('manage_bills'))
                    messageInModal(translate("systmess_error_rights_perform_this_action"));

                $this->load->model('User_Bills_Model', 'user_bills');
                switch ($this->uri->segment(5)) {
                    case 3:
                        $data['title'] = 'Bills to feature item number: ' . orderNumber($id);
                        $params = array('bills_type' => 3);
                    break;
                    case 4:
                        $data['title'] = 'Bills to highlight item number: ' . orderNumber($id);
                        $params = array('bills_type' => 4);
                    break;
                }
                $params['id_item'] = $id;
                $params['encript_detail'] = 1;
                $params['pagination'] = false;
                $data['bills'] = $this->user_bills->get_user_bills($params);
                if (empty($data['bills'])) {
                    messageInModal('Info: There are no bills for this status.', 'info');
                }

                $data['status'] = $this->user_bills->get_bills_statuses();
                $data['return_to_modal_url'] = __SITE_URL.'items/popup_forms/bills_list/'.$id.'/'.$params['bills_type'];

                $this->view->assign($data);
                $this->view->display('admin/item/popup_bills_list_view');
            break;
            case 'make_pick_of_the_month':
                if (!have_right('manage_picks_of_the_month')){
                    messageInModal(translate("systmess_error_rights_perform_this_action"));
                }

                #region check if item exists
                /** @var Products_Model $itemsModel */
                $itemsModel = model(Products_Model::class);

                $item = $itemsModel->findOne($id);
                if(empty($item)){
                    messageInModal('Error: This item does not exist.');
                }
                #endregion check if item exists

                #region check if item is currently pick of the month
                /** @var Pick_Of_The_Month_Item_Model $pickItems */
                $pickItems = model(Pick_Of_The_Month_Item_Model::class);

                $sameItem = $pickItems->findOneBy([
                    'conditions' => [
                        'idItem'      => $id,
                        'dateBetween' => new DateTime()
                    ]
                ]);

                $infoMessage = '';
                if(!empty($sameItem)){
                    $infoMessage = 'This item is already pick of the month currently';
                }
                #endregion check if item is currently pick of the month

                views()->assign([
                    'idItem'      => $id,
                    'infoMessage' => $infoMessage,
                    'type'        => 'item'
                ]);
                views()->display('admin/pick_of_the_month/pick_of_the_month_popup_view');
            break;
            case 'email_item':
                if (!isAjaxRequest()){
                    headerRedirect();
                }

                checkPermisionAjaxModal('email_this');

                if(!model('items')->item_exist($id)){
                    messageInModal(translate('systmess_error_invalid_data'));
                }

                $data['type'] = "email";
                $data['id_item'] = $id;
                $data['action'] = "items/ajax_send_email/email_item";
                $data['message'] = translate("share_form_message_item");

                $this->view->display('new/user/share/popup_email_share_view', $data);
            break;
            case 'share_item':
                if (!isAjaxRequest()){
                    headerRedirect();
                }

                checkPermisionAjaxModal('share_this');

                if(!model('items')->item_exist($id)){
                    messageInModal(translate('systmess_error_invalid_data'));
                }

                $data['id_item'] = $id;
                $data['action'] = "items/ajax_send_email/share_item";
                $data['message'] = translate("share_form_message_item");
                $this->view->display('new/user/share/popup_email_share_view', $data);
            break;
            case 'thumbs_actualize_log':
                if (!have_right('manage_content'))
                    messageInModal(translate("systmess_error_rights_perform_this_action"));

                $data['content'] = file_get_contents('public/items/thumbs_actualize_log.txt');
                $this->view->display('admin/item/thumbs_actualize_log', $data);
            break;
            case 'weight_calculator':
                $this->view->display('new/item/add_item/weight_calculator_view', array(
                    'validation' => $this->getItemValidationMetadata(),
                ));
            break;
            case 'admin_edit_item':
                checkPermisionAjaxModal('manage_content');

                $data['item'] = $this->items->get_item($id);
                if(empty($data['item'])){
                    messageInModal('Error: This item does not exist.');
                }

                $data['categories'] = $this->category->getCategories(['parent' => 0]);
                $data['product_categories'] = $product_categories = (!empty($data['item']['item_categories'])?explode(',', $data['item']['item_categories']):'');

                if(!empty($product_categories)){
                    foreach($product_categories as $child){
                        $data['categories_' . $child] = $this->category->getCategories(['parent' => $child,  'columns' => 'category_id, name']);
                    }
                }
                $data['u_types'] = $this->items->get_unit_types();

                /** @var Items_Variants_Model $itemsVariantsModel */
                $itemsVariantsModel = model(Items_Variants_Model::class);
                $itemVariants = $itemsVariantsModel->getItemVariants((int) $id, true);
                $data['itemVariants'] = $itemVariants;

                $data['item']['u_attr'] = arrayByKey($this->items->get_user_attrs($id), 'id');
                $data['item_description'] = model('Items_Descriptions')->get_descriptions_by_item($id);

                if(!empty($data['item_description'])){

                    if($data['item_description']['status'] == 'removed'){
                        $data['item_description'] = json_encode(
                            [
                                'status'    => $data['item_description']['status'],
                                'translate' => $data['item_description']['need_translate']
                            ]
                        );
                    }else{
                        $data['item_description'] = json_encode(
                            [
                                'languageName'  => $data['item_description']['lang_name'],
                                'language'      => $data['item_description']['descriptions_lang'],
                                'description'   => $data['item_description']['item_description'],
                                'translate'     => $data['item_description']['need_translate'],
                                'status'        => $data['item_description']['status'],
                            ]
                        );
                    }
                }

                $data['languages'] = model('translations')->get_languages(['lang_default' => 0]);

                $this->view->assign($data);
                $this->view->display($this->view_folder.'admin/item/edit_item_view');
            break;
            case 'bulk_upload':
                checkPermisionAjaxModal('manage_draft_items');

                $this->show_bulk_upload_form();

                break;

            case 'add_description_another_lang':
                checkHaveCompanyAjaxModal();
                checkPermisionAjaxModal('manage_personal_items');

                $data['languages'] = model('translations')->get_languages(array('lang_default' => 0));

                $this->view->display('new/item/add_item/partials/description_another_lang_view', $data);
                break;
            case 'show_draft_request':
                checkHaveCompanyAjaxModal();
                checkPermisionAjaxModal('manage_personal_items');

                $id = (int) uri()->segment(4);

                /** @var Draft_Extend_Requests_Model $extendModel */
                $extendModel = model(Draft_Extend_Requests_Model::class);

                $today = new DateTime();
                $today->add(new DateInterval('P' . (config('draft_items_days_expire', 10) + 1) . 'D'));

                $data = [
                    'expireMin' => $today->format('m/d/Y')
                ];

                $maxDate = $today->add(new DateInterval('P30D'));
                $data['expireMax'] = $maxDate->format('m/d/Y');

                if(!empty($id)){
                    $request = $extendModel->findOneBy([
                        'conditions' => ['id' => $id, 'id_user' => id_session()]
                    ]);

                    if(empty($request)){
                        messageInModal(translate('systmess_request_exted_draft_not_exist_message'));
                    }

                    if(1 == $request['is_requested']){
                        messageInModal(translate('systmess_request_exted_draft_already_extended_message'));
                    }

                    $data['request'] = $request;
                    $data['expireMin'] = $request['expiration_date']->add(new DateInterval('P1D'))->format('m/d/Y');
                    $maxDate = $request['expiration_date']->add(new DateInterval('P31D'));
                    $data['expireMax'] = $maxDate->format('m/d/Y');
                }

                $this->view->display('new/item/request_draft_extend_view', $data);
                break;
            case 'admin_add_translation':
                checkIsAjax();
                checkPermisionAjaxModal('manage_content');

                $items_description = model('Items_Descriptions')->get_descriptions_by_item($id);
                if(
                    empty($items_description)
                    || !in_array($items_description['status'], array('need_translate', 'translated'))
                ){
                    messageInModal(translate("systmess_error_rights_perform_this_action"));
                }

                $item_detail = model('Items')->get_item_simple($id);

                $data['items_description'] = $items_description;
                $data['item']['description'] = $item_detail['description'];
                $this->view->display('admin/item/popup_add_translation_view', $data);
                break;
            case 'adult_items_policy':
                checkIsAjax();
                $this->view->display('new/item/add_item/adult_items_policy_popup_view');
            break;

            default:
                messageInModal('The provided path is not found on this server.');

            break;
        }
    }

    function ajax_send_email() {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        if (!logged_in()) {
            jsonResponse('Error: You must be logged in to send emails.');
        }

        $this->_load_main(); /* load main models */

        $op = $this->uri->segment(3);
        switch ($op) {
            case 'share_item':
                checkPermisionAjax('share_this');

                $validator_rules = array(
                    array(
                        'field' => 'message',
                        'label' => 'Message',
                        'rules' => array('required' => '', 'max_len[1000]' => '')
                    ),
                    array(
                        'field' => 'id_item',
                        'label' => 'Item',
                        'rules' => array('required' => '')
                    )
                );

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $idItem = intVal($_POST['id_item']);
                $item = model(Items_Model::class)->get_item($idItem);
                $photoItem = model(Items_Model::class)->get_item_main_photo($idItem);

                if (empty($item)) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                $idUser = privileged_user_id();
                $filteredEmails = model(Followers_Model::class)->getFollowersEmails($idUser);

                if (empty($filteredEmails)) {
                    jsonResponse(translate('systmess_error_share_item_no_followers'));
                }

                $userName = user_name_session();

                try {
                    /** @var MailerInterface $mailer */
                    $mailer = $this->getContainer()->get(MailerInterface::class);
                    $mailer->send(
                        (new ShareItem($userName, cleanInput(request()->request->get('message')), $item, $photoItem['photo_name']))
                            ->to(...array_map(fn (int $id, string $to) => new RefAddress((string) $id, new Address($to)), array_column($filteredEmails, 'idu', 'idu'), array_column($filteredEmails, 'email', 'email')))
                            ->subjectContext(['[userName]' => $userName])
                    );
                } catch (\Throwable $th) {
                    jsonResponse(translate('email_has_not_been_sent'));
                }

                /** @var Share_Statistic_Model $shareStatisticRepository */
                $shareStatisticRepository = model(Share_Statistic_Model::class);
                $shareStatisticRepository->add([
                    'type'          => 'item',
                    'type_sharing'  => 'share this',
                    'id_item'       => $item['id'],
                    'id_user'       => id_session(),
                ]);

                jsonResponse(translate('systmess_successfully_sent_email'), 'success');

            break;
            case 'email_item':
                if (!have_right('email_this')) {
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));
                }

                $validator_rules = array(
                    array(
                        'field' => 'emails',
                        'label' => 'Emails',
                        'rules' => array('required' => '', 'no_whitespaces' => '', 'valid_emails' => '', 'max_emails_count[' . config('email_this_max_email_count') . ']' => '')
                    ),
                    array(
                        'field' => 'message',
                        'label' => 'Message',
                        'rules' => array('required' => '', 'max_len[1000]' => '')
                    ),
                    array(
                        'field' => 'id_item',
                        'label' => 'Item',
                        'rules' => array('required' => '')
                    )
                );
                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $filteredEmails = filter_email($_POST['emails']);
                if(empty($filteredEmails)){
                    jsonResponse(translate('systmess_error_validation_email'));
                }

                $idItem = intVal($_POST['id_item']);
                $item = model(Items_Model::class)->get_item($idItem);
                $photoItem = model(Items_Model::class)->get_item_main_photo($idItem);

                if(empty($item)){
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                $userName = user_name_session();

                try {
                    /** @var MailerInterface $mailer */
                    $mailer = $this->getContainer()->get(MailerInterface::class);
                    $mailer->send(
                        (new ShareItem($userName, cleanInput(request()->request->get('message')), $item, $photoItem['photo_name']))
                            ->to(...array_map(fn (string $to) => new Address($to), $filteredEmails))
                            ->subjectContext(['[userName]' => $userName])
                    );
                } catch (\Throwable $th) {
                    jsonResponse(translate('email_has_not_been_sent'));
                }

                if (config('env.APP_ENV') === 'prod') {
                    /** @var TinyMVC_Library_Zoho_crm $crmLibrary */
                    $crmLibrary = library(TinyMVC_Library_Zoho_crm::class);

                    foreach ($filteredEmails as $filteredEmailsItem) {
                        if (empty($crmLibrary->getLeadsByUserEmail($filteredEmailsItem))) {
                            $crmLibrary->createLead([
                                'first_name'  => 'No First Name',
                                'last_name'   => 'No Last Name',
                                'email'       => $filteredEmailsItem,
                                'lead_source' => 'ExportPortal API',
                            ]);
                        }
                    }
                }

                /** @var Share_Statistic_Model $shareStatisticRepository */
                $shareStatisticRepository = model(Share_Statistic_Model::class);
                $shareStatisticRepository->add([
                    'type'          => 'item',
                    'type_sharing'  => 'email this',
                    'id_item'       => $item['id'],
                    'id_user'       => id_session(),
                ]);
                jsonResponse(translate('systmess_successfully_sent_email'), 'success');
            break;
        }
    }

    function featured_administration() {
        checkAdmin("manage_content");

        $this->_load_main();
        $data["categories"] = $this->category->getCategories(array("parent" => 0));
        $data["statuses"] = array(
            "init" => "Initiated",
            "active" => "Active",
            "expired" => "Expired"
        );
        $data["last_featured_items_id"] = $this->items->get_featured_items_last_id();

        $this->view->assign($data);
        $this->view->assign("title", "Featured items list");
        $this->view->display("admin/header_view");
        $this->view->display("admin/item/featured/index_view");
        $this->view->display("admin/footer_view");
    }

    function dt_ajax_administration_featured_items() {
        if (!isAjaxRequest())
            headerRedirect();

        checkAdminAjaxDT('moderate_content');

        $this->_load_main();

        $product_status = array("null", "New", "Active", "Featured", "Expired", "Ordered", "Sold");
        $params['seller_info'] = true;

        $sorting = [
            'per_p' => intVal($_POST['iDisplayLength']),
            'start' => intVal($_POST['iDisplayStart']),
            'seller_info' => true,
            'sort_by' => flat_dt_ordering($_POST, [
                'dt_update_date' => 'itf.update_date',
                'dt_expire_date' => 'itf.end_date'
            ])
        ];

        $filters = dtConditions($_POST, [
            ['as' => 'id_item', 'key' => 'id_item', 'type' => 'int'],
            ['as' => 'visible', 'key' => 'visible', 'type' => 'int'],
            ['as' => 'seller', 'key' => 'seller', 'type' => 'int'],
            ['as' => 'country', 'key' => 'country', 'type' => 'int'],
            ['as' => 'city', 'key' => 'city', 'type' => 'int'],
            ['as' => 'status', 'key' => 'status', 'type' => 'int'],
            ['as' => 'itf_status', 'key' => 'itf_status', 'type' => 'cleanInput'],
            ['as' => 'auto_extend', 'key' => 'auto_extend', 'type' => 'int'],
            ['as' => 'paid', 'key' => 'paid', 'type' => 'int'],
            ['as' => 'start_from',  'key' => 'start_from', 'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'start_to',  'key' => 'start_to', 'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'end_from',  'key' => 'end_from', 'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'end_to',  'key' => 'end_to', 'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'keywords', 'key' => 'sSearch', 'type' => function($search_term) {
                if (!empty($search_term)) {
                    return $params['keywords'] = cleanInput($_POST['sSearch']);
                }
            }]
        ]);

        $sorting['sort_by'] = empty($sorting['sort_by']) ? ["itf.update_date-asc"] : $sorting['sort_by'];

        $params = array_merge($sorting, $filters);

        $id_category = intVal($_POST['parent']);

        if ($id_category) {
            $category = $this->category->get_category($id_category);
            if (strlen($category['cat_childrens']))
                $params['categories_list'] = $category['cat_childrens'] . "," . $id_category;
            else
                $params['categories_list'] = $id_category;
        }

        $products_count = $this->items->count_featured_items($params);
        $products = $this->items->get_featured_items($params);

        $output = array(
            "sEcho" => intval($_POST['sEcho']),
            "iTotalRecords" => $products_count,
            "iTotalDisplayRecords" => $products_count,
            "aaData" => array()
        );

        if(empty($products))
            jsonResponse('', 'success', $output);

        foreach ($products as $item)
            $items_list[] = $item['id'];

        $products_location = $this->items->getItemsLocation(implode(',', $items_list));
        // $products_statistics = $this->items->get_items_statistics(implode(',', $items_list));
        // $images = $this->items->count_photo(array('items_list' => implode(',', $items_list)));
        $main_images = $this->items->items_main_photo(array('main_photo' => 1, 'items_list' => implode(',', $items_list)));

        $f_statuses = array(
            'init' => 'Initiated',
            'active' => 'Active',
            'expired' => 'Expired'
        );

        foreach ($products as $row) {
            $cat_breadcrumbs = array();
            $item_breadcrumbs = json_decode('[' . $row['breadcrumbs'] . ']', true);
            if (!empty($item_breadcrumbs)) {
                foreach ($item_breadcrumbs as $bread) {
                    foreach ($bread as $cat_id => $cat_title)
                        $cat_breadcrumbs[] = '<a class="pull-left dt_filter" data-name="parent" href="#" onclick="javascript: return false;" data-title="Category" data-value="' . $cat_id . '" data-value-text="' . $cat_title . '">' . $cat_title . '</a>';
                }
            }
            $company_link = __SITE_URL;
            if (!empty($row['index_name']))
                $company_link .= $row['index_name'];
            elseif ($row['type_company'] == 'branch')
                $company_link .= "branch/" . strForURL($row['name_company']) . "-" . $row['id_company'];
            else
                $company_link .= "seller/" . strForURL($row['name_company']) . "-" . $row['id_company'];

            $company_icon = "<a class='ep-icon ep-icon_building' title='View page of company " . $row['name_company'] . "' target='_blank' href='" . $company_link . "'></a>";
            $company_title = "<div class='clearfix'></div><span>(" . $row['name_company'] . ")</span>";

            $re_feature_btn = '';
            if($row['itf_status'] == 'active' && $row['itf_extend'] != 1){
                $re_feature_btn = '<a class="ep-icon ep-icon_refresh txt-orange confirm-dialog" data-message="Are you sure you want to extend the featured status for this item for free?" data-callback="free_extend_feature_item" data-item="'.$row['id_featured'].'" title="FREE re-Feature"></a>';
            }

            $stop_auto_extend_btn = '';
            if ($row['auto_extend'] && 'active' == $row['itf_status']) {
                $stop_auto_extend_btn = '<a class="ep-icon ep-icon_minus-circle txt-black confirm-dialog" data-message="Are you sure you want to stop auto-extend featured status for this item?" data-callback="stop_auto_extend_item" data-item="' . $row['id_featured'] . '" title="Stop auto-extend"></a>';
            }

            $unfeature_btn = '';
            if ('active' == $row['itf_status']) {
                $unfeature_btn = '<a class="ep-icon ep-icon_unfeatured txt-red confirm-dialog" data-message="Are you sure you want to un-feature this item?" data-callback="un_feature_item" data-item="' . $row['id_featured'] . '" title="Un-feature item"></a>';
            }

            $main_image = search_in_array($main_images, 'sale_id', $row['id']);
            $item_img_link = getDisplayImageLink(array('{ID}' => $row['id'], '{FILE_NAME}' => $main_image['photo_name']), 'items.main');

            //TODO: admin chat hidden
            $btnChatUser = new ChatButton(['hide' => true, 'recipient' => $row['id_seller'], 'recipientStatus' => 'active', 'module' => 15, 'item' => $row['id']], ['classes' => 'btn-chat-now', 'text' => '']);
            $btnChat = $btnChatUser->button();

            $output['aaData'][] = array(
                'dt_image' => '<div class="img-prod pull-left w-90">
                                    <input class="rating-bootstrap" data-filled="ep-icon ep-icon_star txt-blue fs-18 m-0" data-empty="ep-icon ep-icon_star-empty txt-blue fs-18 m-0" type="hidden" name="val" value="' . $row['rating'] . '" data-readonly>
                                    <img
                                        class="w-100pr mt-5"
                                        src="' . $item_img_link . '"
                                        alt="' . $row['title'] . '"
                                    />
                                </div>',
                'dt_item' => '<div class="pull-left">'
                        . '<div class="clearfix"><strong class="pull-left lh-16 pr-5">Title: </strong> '
                            . '<a class="pull-left dt_filter" data-name="id_item" href="#" onclick="javascript: return false;" data-title="Item" data-value="' . $row['id'] . '" data-value-text="' . $row['title'] . '">' . $row['title'] . '</a>'
                        . '</div>'
                        . '<div class="clearfix"><strong class="pull-left lh-16 pr-5">Category: </strong> ' . implode('<span class="pull-left lh-16 pr-2 pl-2"> &raquo; </span>', $cat_breadcrumbs) . '</div>'
                        . '<div class="clearfix"><strong class="pull-left lh-16 pr-5">Status: </strong> <a class="pull-left dt_filter" data-name="status" href="#" onclick="javascript: return false;" data-title="Status" data-value="' . $row['status'] . '" data-value-text="' . $product_status[$row['status']] . '">' . $product_status[$row['status']] . '</a></div>'
                    . '</div>',
                'dt_seller' => '<div class="pull-left">'
                        . '<a class="ep-icon ep-icon_filter txt-green dt_filter" data-title="Seller" title="Filter by ' . $row['user_name'] . '" data-value-text="' . $row['user_name'] . '" data-value="' . $row['id_seller'] . '" data-name="seller"></a>'
                        . '<a class="ep-icon ep-icon_user" title="View personal page of ' . $row['user_name'] . '" href="' . __SITE_URL . 'usr/' . strForURL($row['user_name']) . '-' . $row['id_seller'] . '"></a>'
                        . $company_icon
                    . '</div>'
                    . '<div class="clearfix"></div>'
                    . '<span>' . $row['user_name'] . '</span>'
                    . $company_title,
                'dt_paid' =>'<p class="lh-24"><strong>Bills: </strong><a href="items/popup_forms/bills_list/' . $row['id_featured'] . '/3" class="fancybox.ajax fancybox" data-title="Bills list" title="Bills list">View all</a></p>
                            <p class="lh-24"><strong>Status: </strong><a href="#" class="dt_filter" data-name="paid" title="Filter by payment status" data-title="Payment status" data-value="' . $row['paid'] . '" data-value-text="' . (($row['paid']) ? 'Paid' : 'Waiting for paiment') . '">' . (($row['paid']) ? 'Paid' : 'Waiting for paiment') . '</a></p>',
                'dt_fstatus' => '<a href="#" class="dt_filter lh-24" title="Filter by featured status" data-name="itf_status" data-title="Featured status" data-value="' . $row['itf_status'] . '" data-value-text="' . $f_statuses[$row['itf_status']] . '">' . $f_statuses[$row['itf_status']] . '</a>',
                'dt_price' => $row['itf_price'],
                'dt_update_date' => getDateFormat($row['update_date']),
                'dt_expire_date' => validateDate($row['end_date'], 'Y-m-d') ? getDateFormat($row['end_date'], 'Y-m-d', 'j M, Y') : '&mdash;',
                'dt_address' => '<a class="country dt_filter pull-left" data-name="country" data-title="Country" data-value="' . $row['p_country'] . '" data-value-text="' . $row['country'] . '">'
                    . '<img class="mr-5" width="24" height="24" src="' . getCountryFlag($row['country']) . '" title="Filter by: ' . $row['country'] . '" alt="' . $row['country'] . '"/></a> '
                    . '<a href="#" class="dt_filter lh-24" title="Filter by: ' . $products_location[$row['id']]['item_city'] . '" data-name="city" data-title="City" data-value="' . $row['p_city'] . '" data-value-text="' . $products_location[$row['id']]['item_city'] . '">' . $products_location[$row['id']]['item_city'] . '</a>',
                'dt_actions' => $btnChat
                                . $re_feature_btn
                                . $stop_auto_extend_btn
                                . $unfeature_btn
            );
        }

        jsonResponse('', 'success', $output);
    }

    function highlight_administration() {
        checkAdmin('manage_content');

        $this->_load_main();
        $data['categories'] = $this->category->getCategories(array('parent' => 0));
        $data['statuses'] = array(
            'init' => 'Initiated',
            'active' => 'Active',
            'expired' => 'Expired'
        );

        $data['last_highlight_items_id'] = $this->items->get_highlight_items_last_id();

        $this->view->assign('title', 'Highlight items list');
        $this->view->assign($data);
        $this->view->display('admin/header_view');
        $this->view->display('admin/item/highlight/index_view');
        $this->view->display('admin/footer_view');
    }

    function dt_ajax_administration_highlight_items() {
        if (!isAjaxRequest())
            headerRedirect();

        if (!logged_in())
            jsonDTResponse(translate("systmess_error_should_be_logged"));

        if (!have_right('moderate_content'))
            jsonDTResponse(translate("systmess_error_rights_perform_this_action"));

        $this->_load_main();

        $product_status = array("null", "New", "Active", "Featured", "Expired", "Ordered", "Sold");

        $sorting = [
            'per_p' => intVal($_POST['iDisplayLength']),
            'start' => intVal($_POST['iDisplayStart']),
            'seller_info' => true,
            'sort_by' => flat_dt_ordering($_POST, [
                'dt_update_date' => 'ith.update_date',
                'dt_expire_date' => 'ith.end_date'
            ])
        ];

        $filters = dtConditions($_POST, [
            ['as' => 'id_item', 'key' => 'id_item', 'type' => 'int'],
            ['as' => 'visible', 'key' => 'visible', 'type' => 'int'],
            ['as' => 'seller', 'key' => 'seller', 'type' => 'int'],
            ['as' => 'country', 'key' => 'country', 'type' => 'int'],
            ['as' => 'city', 'key' => 'city', 'type' => 'int'],
            ['as' => 'status', 'key' => 'status', 'type' => 'int'],
            ['as' => 'ith_status', 'key' => 'ith_status', 'type' => 'cleanInput'],
            ['as' => 'auto_extend', 'key' => 'auto_extend', 'type' => 'int'],
            ['as' => 'paid', 'key' => 'paid', 'type' => 'int'],
            ['as' => 'start_from',  'key' => 'start_from', 'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'start_to',  'key' => 'start_to', 'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'end_from',  'key' => 'end_from', 'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'end_to',  'key' => 'end_to', 'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'keywords', 'key' => 'sSearch', 'type' => function($search_term) {
                if (!empty($search_term)) {
                    return $params['keywords'] = cleanInput($_POST['sSearch']);
                }
            }]
        ]);

        $sorting['sort_by'] = empty($sorting['sort_by']) ? ["ith.update_date-asc"] : $sorting['sort_by'];

        $params = array_merge($sorting, $filters);

        $id_category = intVal($_POST['parent']);

        if ($id_category) {
            $category = $this->category->get_category($id_category);
            if (strlen($category['cat_childrens']))
                $params['categories_list'] = $category['cat_childrens'] . "," . $id_category;
            else
                $params['categories_list'] = $id_category;
        }

        $products_count = $this->items->count_highlight_items($params);
        $products = $this->items->get_highlight_items($params);

        $output = array(
            "sEcho" => intval($_POST['sEcho']),
            "iTotalRecords" => $products_count,
            "iTotalDisplayRecords" => $products_count,
            "aaData" => array()
        );

        if (empty($products))
            jsonResponse('', 'success', $output);

        foreach ($products as $item)
            $items_list[] = $item['id'];

        $products_location = $this->items->getItemsLocation(implode(',', $items_list));
        // $products_statistics = $this->items->get_items_statistics(implode(',', $items_list));
        // $images = $this->items->count_photo(array('items_list' => implode(',', $items_list)));
        $main_images = $this->items->items_main_photo(array('main_photo' => 1, 'items_list' => implode(',', $items_list)));

        $f_statuses = array(
            'init' => 'Initiated',
            'active' => 'Active',
            'expired' => 'Expired'
        );

        foreach ($products as $row) {
            $cat_breadcrumbs = array();
            $item_breadcrumbs = json_decode('[' . $row['breadcrumbs'] . ']', true);
            if (!empty($item_breadcrumbs)) {
                foreach ($item_breadcrumbs as $bread) {
                    foreach ($bread as $cat_id => $cat_title)
                        $cat_breadcrumbs[] = '<a class="pull-left dt_filter" data-name="parent" href="#" onclick="javascript: return false;" data-title="Category" data-value="' . $cat_id . '" data-value-text="' . $cat_title . '">' . $cat_title . '</a>';
                }
            }


            $highlight_btn = '<a data-callback="hightlight_item" class="ep-icon ep-icon_highlight txt-lblue-darker confirm-dialog" data-message="Are you sure want to higlight this item?" title="Highlight this item" data-item="item-' . $row['id'] . '-' . $row['highlight'] . '"></a>';
            if ($row['highlight'])
                $highlight_btn = '<a data-callback="hightlight_item" class="ep-icon ep-icon_unhighlight txt-lblue-darker confirm-dialog" data-message="Are you sure want to unhiglight this item?" title="Unhighlight this item" data-item="item-' . $row['id'] . '-' . $row['highlight'] . '"></a>';

            $company_link = __SITE_URL;
            if (!empty($row['index_name']))
                $company_link .= $row['index_name'];
            elseif ($row['type_company'] == 'branch')
                $company_link .= "branch/" . strForURL($row['name_company']) . "-" . $row['id_company'];
            else
                $company_link .= "seller/" . strForURL($row['name_company']) . "-" . $row['id_company'];

            $company_icon = "<a class='ep-icon ep-icon_building' title='View page of company " . $row['name_company'] . "' target='_blank' href='" . $company_link . "'></a>";
            $company_title = "<div class='clearfix'></div><span>(" . $row['name_company'] . ")</span>";

            $main_image = search_in_array($main_images, 'sale_id', $row['id']);
            $item_img_link = getDisplayImageLink(array('{ID}' => $row['id'], '{FILE_NAME}' => $main_image['photo_name']), 'items.main');

            //TODO: admin chat hidden
            $btnChatUser = new ChatButton(['hide' => true, 'recipient' => $row['id_seller'], 'recipientStatus' => 'active', 'module' => 15, 'item' => $row['id']], ['classes' => 'btn-chat-now', 'text' => '']);
            $btnChat = $btnChatUser->button();

            $output['aaData'][] = array(
                'dt_image' => '<div class="img-prod pull-left w-90">
                                <input class="rating-bootstrap" data-filled="ep-icon ep-icon_star txt-blue fs-18 m-0" data-empty="ep-icon ep-icon_star-empty txt-blue fs-18 m-0" type="hidden" name="val" value="' . $row['rating'] . '" data-readonly>
                                <img
                                    class="w-100pr mt-5"
                                    src="' . $item_img_link . '"
                                    alt="' . $row['title'] . '"
                                />
                            </div>',
                'dt_item' => '<div class="pull-left">'
                        . '<div class="clearfix"><strong class="pull-left lh-16 pr-5">Title: </strong> '
                            . '<a class="pull-left dt_filter" data-name="id_item" href="#" onclick="javascript: return false;" data-title="Item" data-value="' . $row['id'] . '" data-value-text="' . $row['title'] . '">' . $row['title'] . '</a>'
                        . '</div>'
                        . '<div class="clearfix"><strong class="pull-left lh-16 pr-5">Category: </strong> ' . implode('<span class="pull-left lh-16 pr-2 pl-2"> &raquo; </span>', $cat_breadcrumbs) . '</div>'
                        . '<div class="clearfix"><strong class="pull-left lh-16 pr-5">Status: </strong> <a class="pull-left dt_filter" data-name="status" href="#" onclick="javascript: return false;" data-title="Status" data-value="' . $row['status'] . '" data-value-text="' . $product_status[$row['status']] . '">' . $product_status[$row['status']] . '</a></div>'
                    . '</div>',
                'dt_seller' => '<div class="pull-left">'
                        . '<a class="ep-icon ep-icon_filter txt-green dt_filter" data-title="Seller" title="Filter by ' . $row['user_name'] . '" data-value-text="' . $row['user_name'] . '" data-value="' . $row['id_seller'] . '" data-name="seller"></a>'
                        . '<a class="ep-icon ep-icon_user" title="View personal page of ' . $row['user_name'] . '" href="' . __SITE_URL . 'usr/' . strForURL($row['user_name']) . '-' . $row['id_seller'] . '"></a>'
                        . $company_icon
                    . '</div>'
                    . '<div class="clearfix"></div>'
                    . '<span>' . $row['user_name'] . '</span>'
                . $company_title,
                'dt_paid' => '<p class="lh-24"><strong>Bills: </strong><a href="items/popup_forms/bills_list/' . $row['id_highlight'] . '/4" class="fancybox.ajax fancybox" data-title="View bills list" title="View all bills">View all</a></p>
                                <p class="lh-24"><strong>Status: </strong><a href="#" class="dt_filter" title="Filter by payment status" data-name="paid" data-title="Highlight paid" data-value="' . $row['paid'] . '" data-value-text="' . (($row['paid']) ? 'Paid' : 'Waiting for paiment') . '">' . (($row['paid']) ? 'Paid' : 'Waiting for paiment') . '</a></p>',
                'dt_fstatus' => '<a href="#" class="dt_filter lh-24" title="Filter by highlight status" data-name="ith_status" data-title="Highlight status" data-value="' . $row['ith_status'] . '" data-value-text="' . $f_statuses[$row['ith_status']] . '">' . $f_statuses[$row['ith_status']] . '</a>',
                'dt_price' => $row['itf_price'],
                'dt_update_date' => formatDate($row['update_date']),
                'dt_expire_date' => 'init' == $row['ith_status'] ? 'Not payed yet' : getDateFormat($row['end_date'], 'Y-m-d', 'j M, Y'),
                'dt_address' => '<a class="country dt_filter pull-left" data-name="country" data-title="Country" data-value="' . $row['p_country'] . '" data-value-text="' . $row['country'] . '">'
                    . '<img width="24" height="24" src="' . getCountryFlag($row['country']) . '" title="Filter by: ' . $row['country'] . '" alt="' . $row['country'] . '"/></a> '
                    . '<a href="#" class="dt_filter lh-24" title="Filter by: ' . $products_location[$row['id']]['item_city'] . '" data-name="city" data-title="City" data-value="' . $row['p_city'] . '" data-value-text="' . $products_location[$row['id']]['item_city'] . '">' . $products_location[$row['id']]['item_city'] . '</a>',
                'dt_actions' => $btnChat
                                . $highlight_btn
            );
        }

        jsonResponse('', 'success', $output);
    }

    function administration() {
        checkAdmin('manage_content');

        $this->_load_main();

        views(
            [
                'admin/header_view',
                'admin/item/index_view',
                'admin/footer_view'
            ],
            [
                'last_items_id' => $this->items->get_items_last_id(),
                'categories'    => $this->category->getCategories(['parent' => 0]),
                'title'         => 'Items list',
                'filters'       => [
                    'id_item' => request()->query->getInt('id_item'),
                    'seller'  => request()->query->getInt('seller'),
                    'draft'   => request()->query->getInt('draft'),
                    'label'   => request()->query->getInt('label'),
                    'expire'  => request()->query->get('expire'),
                ],
            ]
        );
    }

    public function ajax_administration_items()
    {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        if (!logged_in()) {
            jsonDTResponse(translate("systmess_error_should_be_logged"));
        }

        if (!have_right('moderate_content')) {
            jsonDTResponse(translate("systmess_error_rights_perform_this_action"));
        }

        $this->_load_main();

        $params = [
            'per_p' => $_POST['iDisplayLength'],
            'start' => $_POST['iDisplayStart']
        ];

        $id_category = intVal($_POST['parent']);
        if ($id_category) {
            $category = $this->category->get_category($id_category);
            if (strlen($category['cat_childrens'])) {
                $params['categories_list'] = $category['cat_childrens'] . "," . $id_category;
            } else {
                $params['categories_list'] = $id_category;
            }
        }

        if (!empty($_POST['search_by_keywords'])) {
            $params['keywords'] = $_POST['search_by_keywords'];
        }

        if (!empty(request()->request->get('search_by_title'))) {
            $params['search_by_title'] = request()->request->get('search_by_title');
        }

        $order = array_column(dt_ordering($_POST, [
            'dt_create_date'    => 'it.create_date',
            'dt_update_date'    => 'it.update_date',
            'dt_seller'         => 'it.id_seller',
        ]), 'direction', 'column');

        $sort_by = [];
        foreach ($order as $column => $direction) {
            $sort_by[] = $column . ' ' . $direction;
        }

        if (!empty($sort_by)) {
            $params['sort_by'] = implode(',', $sort_by);
        }

        $attrs = [];
        foreach ($_POST as $key => $value) {
            if (preg_match('/^attrs_/', $key)) {
                $attrs[end(explode('_', $key))] = $value;
            }
        }

        if (!empty($attrs)) {
            $params['attrs'] = $attrs;
        }

        $r_attrs = [];
        foreach ($_POST as $key => $value) {
            if (preg_match('/^range_attrs_/', $key)) {
                $components = explode('_', $key);
                $r_attrs[$components[3]][$components[2]] = $value;
            }
        }

        if (!empty($r_attrs)) {
            $params['r_attrs'] = $r_attrs;
        }

        $t_attrs = [];
        foreach ($_POST as $key => $value) {
            if (preg_match('/^text_attrs_/', $key)) {
                $t_attrs[end(explode('_', $key))] = $value;
            }
        }

        if (!empty($t_attrs)) {
            $params['t_attrs'] = $t_attrs;
        }

        $params['seller_info'] = true;

        if (isset($_POST['featured'])) {
            $params['featured'] = intVal($_POST['featured']);
        }

        if (isset($_POST['highlight'])) {
            $params['highlight'] = intVal($_POST['highlight']);
        }

        if (isset($_POST['partnered_item'])) {
            $params['partnered_item'] = intVal($_POST['partnered_item']);
        }

        if (isset($_POST['visible'])) {
            $params['visible'] = intVal($_POST['visible']);
        }

        if (isset($_POST['blocked'])) {
            $params['blocked'] = intVal($_POST['blocked']);
        }

        if (isset($_POST['draft'])) {
            $params['is_draft'] = (int) $_POST['draft'];
        }

        if (request()->request->has('label')) {
            $params['label'] = (int) request()->request->get('label');
        }

        if (isset($_POST['seller'])) {
            $params['seller'] = intVal($_POST['seller']);
        }

        if (isset($_POST['id_item'])) {
            $params['list_item'] = intVal($_POST['id_item']);
        }

        if (isset($_POST['country'])) {
            $params['country'] = intVal($_POST['country']);
        }

        if (isset($_POST['city'])) {
            $params['city'] = intVal($_POST['city']);
        }

        if (isset($_POST['start_from'])) {
            $params['start_from'] = formatDate(cleanInput($_POST['start_from']), 'Y-m-d');
        }

        if (isset($_POST['start_to'])) {
            $params['start_to'] = formatDate(cleanInput($_POST['start_to']), 'Y-m-d');
        }

        if (isset($_POST['expire'])) {
            $params['expire'] = getDateFormat(cleanInput($_POST['expire']), 'Y-m-d', 'Y-m-d');
        }

        if (isset($_POST['update_from'])) {
            $params['update_from'] = formatDate(cleanInput($_POST['update_from']), 'Y-m-d');
        }

        if (isset($_POST['update_to'])) {
            $params['update_to'] = formatDate(cleanInput($_POST['update_to']), 'Y-m-d');
        }

        if (isset($_POST['translation_status'])) {
            $params['translation_status'] = cleanInput($_POST['translation_status']);
        }

        if (isset($_POST['search_by_username_email'])) {
            $params['search_by_username_email'] = cleanInput($_POST["search_by_username_email"]);
        }

        if (isset($_POST['search_by_company'])) {
            $params['search_by_company'] = cleanInput($_POST["search_by_company"]);
        }

        if (isset($_POST['fake_item'])) {
            $params['fake_item'] = (bool) $_POST['fake_item'];
        }

        if (isset($_POST['archived'])) {
            $params['is_archived'] = (int) $_POST['archived'];
        }

        $products_count = $this->items->count_items($params);
        $products = $this->items->get_items($params);

        $output = [
            "sEcho"                 => intval($_POST['sEcho']),
            "iTotalRecords"         => $products_count,
            "iTotalDisplayRecords"  => $products_count,
            'aaData'                => []
        ];

        if (empty($products)) {
            jsonResponse('', 'success', $output);
        }

        $sellers_ids = array_column($products, 'id_seller');
        $sellers = model(User_Model::class)->getSimpleUsers(implode(',', $sellers_ids), 'idu, fake_user');
        $sellers_by_id = array_column($sellers, null, 'idu');

        $items_list = array_column($products, 'id');

        $items_list_ids = implode(',', $items_list);
        $products_location = $this->items->getItemsLocation($items_list_ids);
        $products_statistics = $this->items->get_items_statistics($items_list_ids);

        $main_images = $this->items->items_main_photo(['main_photo' => 1, 'items_list' => $items_list_ids]);

        foreach ($products as $row) {
            $cat_breadcrumbs = [];
            $item_breadcrumbs = json_decode('[' . $row['breadcrumbs'] . ']', true);

            if (!empty($item_breadcrumbs)) {
                foreach ($item_breadcrumbs as $bread) {
                    foreach ($bread as $cat_id => $cat_title) {
                        $cat_breadcrumbs[] = '<span class="pull-left">
                                <a href="'.__SITE_URL.'category/'. strForURL($cat_title) . '/'.$cat_id.'" target="_blank">'. $cat_title . '</a>
                                <a class="ep-icon ep-icon_filter txt-green m-0 dt_filter" data-name="parent" data-title="Category" title="Filter by:'.$cat_title.'" data-value="' . $cat_id . '" data-value-text="' . $cat_title . '"></a>
                            </span>';
                    }
                }
            }

            $main_image = search_in_array($main_images, 'sale_id', $row['id']);

            $partner_btn = '<a data-callback="change_partnership" class="ep-icon ep-icon_partners confirm-dialog txt-red" title="Make this item as a partnered item" data-change-to="1" data-item="'.$row['id'].'" data-message="Are you sure want to make this item as a partnered item?"></a>';

            if ($row['is_partners_item']) {
                $partner_btn = '<a data-callback="change_partnership" class="ep-icon ep-icon_partners confirm-dialog txt-green" title="Remove this item from partners" data-change-to="0" data-item="'.$row['id'].'" data-message="Are you sure want to remove this item from partners?"></a>';
            }

            $company_link = __SITE_URL;
            if (!empty($row['index_name'])) {
                $company_link .= $row['index_name'];
            } elseif ($row['type_company'] == 'branch') {
                $company_link .= "branch/" . strForURL($row['name_company']) . "-" . $row['id_company'];
            } else {
                $company_link .= "seller/" . strForURL($row['name_company']) . "-" . $row['id_company'];
            }

            $company_icon = "<a class='ep-icon ep-icon_building' title='View page of company " . $row['name_company'] . "' target='_blank' href='" . $company_link . "'></a>";
            $company_title = "<div>(" . $row['name_company'] . ")</div>";

            $rating = '<input class="rating-bootstrap" data-filled="ep-icon ep-icon_star txt-blue fs-18 m-0" data-empty="ep-icon ep-icon_star-empty txt-blue fs-18 m-0" type="hidden" name="val" value="0" data-readonly>';
            if ($row['rev_numb']) {
                $rating = '<input class="rating-bootstrap" data-filled="ep-icon ep-icon_star txt-blue fs-18 m-0" data-empty="ep-icon ep-icon_star-empty txt-blue fs-18 m-0" type="hidden" name="val" value="' . $row['rating'] . '" data-readonly>';
            }

            $handmade_button = null;
            if (have_right('mark_item_as_handmade')) {
                $action = $row['is_handmade'] ? 'unmark' : 'mark';
                $handmade_button_message = cleanOutput("Do you really want to {$action} this item as handmade?");
                $handmade_button_text = cleanOutput(ucfirst($action) . ' as Handmade');
                $handmade_button_url = __SITE_URL . "items/ajaxHandmadeItem/{$row['id']}";
                $handmade_button = "
                    <a class=\"dropdown-item confirm-dialog\"
                        data-url=\"{$handmade_button_url}\"
                        data-type=\"items\"
                        data-message=\"{$handmade_button_message}\"
                        data-callback=\"moderateResource\"
                        data-resource=\"{$row['id']}\">
                        <i class=\"ep-icon ep-icon_handshake txt-green\" title=\"{$handmade_button_text}\"></i>
                    </a>
                ";
            }

            $blocked_btn = '';
            $block_button_type = TYPE_ITEM;
            if ($row['blocked'] == 0) {
                $block_button_url = __SITE_URL . "moderation/popup_modals/block/{$block_button_type}/{$row['id']}";
                $blocked_btn = "
                    <a class=\"dropdown-item fancybox.ajax fancyboxValidateModal\"
                        data-fancybox-href=\"{$block_button_url}\"
                        data-title=\"Block item\"
                        title=\"Block item\">
                        <i class=\"ep-icon ep-icon_locked txt-red\"></i>
                    </a>
                ";

            } elseif ($row['blocked'] == 1) {
                $unblock_button_url = __SITE_URL . "moderation/ajax_operations/unblock/{$block_button_type}/{$row['id']}";
                $blocked_btn = "
                    <a class=\"dropdown-item confirm-dialog\"
                        title=\"Unblock item\"
                        data-url=\"{$unblock_button_url}\"
                        data-type=\"{$block_button_type}\"
                        data-message=\"Do you really want to unblock this item?\"
                        data-callback=\"unblockResource\"
                        data-resource=\"{$row['id']}\">
                        <i class=\"ep-icon ep-icon_unlocked txt-green\"></i>
                    </a>
                ";

            }

            $visible_btn_filter = '<div class="pt-5 flex-display">
                    <i class="ep-icon ep-icon_visible txt-blue mb-0"></i>
                    <span>Visible </span>
                    <a class="dt_filter ep-icon ep-icon_filter txt-green m-0 ml-5" data-name="visible" data-title="Visible" data-value="1" data-value-text="Yes" title="Filter by: Visible"></a>
                </div>';

            if (!$row['visible']) {
                $visible_btn_filter = '<div class="pt-5 flex-display">
                        <i class="ep-icon ep-icon_invisible txt-blue mb-0"></i>
                        <span>Unvisible </span>
                        <a class="dt_filter ep-icon ep-icon_filter txt-green m-0 ml-5" data-name="visible" data-title="Unvisible" data-value="0" data-value-text="No" title="Filter by: Unvisible"></a>
                    </div>';
            }

            $blocked_btn_filter = '<div class="pt-5 flex-display">
                    <i class="ep-icon ep-icon_unlocked mb-0 txt-green"></i>
                    <span>Unlocked</span>
                    <a class="dt_filter ep-icon ep-icon_filter txt-green m-0 ml-5" data-name="blocked" data-title="Locked" data-value="0" data-value-text="No" title="Filter by: Unlocked"></a>
                </div>';

            if ($row['blocked']) {
                $blocked_btn_filter = '<div class="pt-5 flex-display">
                        <i class="ep-icon ep-icon_locked mb-0 txt-red"></i>
                        <span>Locked</span>
                        <a class="dt_filter ep-icon ep-icon_filter txt-green m-0 ml-5" data-name="blocked" data-title="Locked" data-value="1" data-value-text="Yes" title="Filter by: Locked"></a>
                    </div>';
            }

            $fake_user_btn_filter = '<div class="pt-5 flex-display">
                    <i class="ep-icon ep-icon_minus-circle mb-0 txt-red"></i>
                    <span>' . translate('ep_administration_demo_user_text') . '</span>
                    <a class="dt_filter ep-icon ep-icon_filter txt-green m-0 ml-5" data-name="fake_item" data-title="Fake items" data-value="1" data-value-text="Yes" title="Filter by: Fake items"></a>
            </div>';

            if (!$sellers_by_id[$row['id_seller']]['fake_user']) {
                $fake_user_btn_filter = '<div class="pt-5 flex-display">
                        <i class="ep-icon ep-icon_smile txt-green mb-0"></i>
                        <span>Real user</span>
                        <a class="dt_filter ep-icon ep-icon_filter txt-green m-0 ml-5" data-name="fake_item" data-title="Fake item" data-value="0" data-value-text="No" title="Filter by: Not fake items"></a>
                </div>';
            }

            $archivedBtnFilter = '';
            if ($row['is_archived']) {
                $archivedBtnFilter = <<<BTN
                    <div class="pt-5 flex-display">
                        <i class="ep-icon ep-icon_box-close txt-orange mb-0"></i>
                        <span>Archived</span>
                        <a class="dt_filter ep-icon ep-icon_filter txt-green m-0 ml-5" data-name="archived" data-title="Archived" data-value="1" data-value-text="Yes" title="Filter by: Archived items"></a>
                    </div>
                BTN;
            }

            $partnered_item_filter = '<div class="pt-5 flex-display">
                            <i class="ep-icon ep-icon_partners mb-0 txt-red"></i>
                            <span>Not a partner</span>
                            <a class="dt_filter ep-icon ep-icon_filter txt-green m-0 ml-5" data-name="partnered_item" data-title="Item of partner" data-value="0" data-value-text="No" title="Filter by: Not a partner"></a>
                    </div>';

            if ($row['is_partners_item']) {
                $partnered_item_filter = '<div class="pt-5 flex-display">
                            <i class="ep-icon ep-icon_partners txt-green mb-0"></i>
                            <span>Partner</span>
                            <a class="dt_filter ep-icon ep-icon_filter txt-green m-0 ml-5" data-name="partnered_item" data-title="Partner" data-value="1" data-value-text="Yes" title="Filter by: Locked"></a>
                    </div>';
            }

            $highlight_item_filter = '';
            if ($row['highlight']) {
                $highlight_item_filter = '<div class="pt-5 flex-display">
                            <i class="ep-icon ep-icon_highlight txt-lblue-darker mb-0 txt-red"></i>
                            <span>Highlighted</span>
                            <a class="dt_filter ep-icon ep-icon_filter txt-green m-0 ml-5" data-name="highlight" data-title="Highlighted" data-value="1" data-value-text="Yes" title="Filter by: Highlighted"></a>
                    </div>';
            }

            $featured_item_filter = '';
            if ($row['featured']) {
                $featured_item_filter = '<div class="pt-5 flex-display">
                            <i class="ep-icon ep-icon_featured txt-orange mb-0 txt-red"></i>
                            <span>Featured</span>
                            <a class="dt_filter ep-icon ep-icon_filter txt-green m-0 ml-5" data-name="featured" data-title="Featured" data-value="1" data-value-text="Yes" title="Filter by: Featured"></a>
                    </div>';
            }

            $feature_btn = '';
            if (!$row['draft']) {
                if (!empty($row['featured_status'])) {
                    if (in_array($row['featured_status'], ['active', 'expired']) && $row['extend_feature'] != 1) {
                        $feature_btn = '<a class="ep-icon ep-icon_refresh txt-orange confirm-dialog" data-message="Are you sure you want to extend the featured status for this item for free?" data-callback="free_extend_feature_item" data-item="'.$row['id_featured'].'" title="FREE re-Feature"></a>';
                    }
                } else{
                    $feature_btn = '<a class="ep-icon ep-icon_featured txt-orange confirm-dialog" data-message="Are you sure want to feature this item for free?" data-callback="free_feature_item" data-item="'.$row['id'].'" title="FREE Feature"></a>';
                }
            }
            $item_img_link = getDisplayImageLink(['{ID}' => $row['id'], '{FILE_NAME}' => $main_image['photo_name']], 'items.main');

            $item_status = '<div class="[COLOR]">[TEXT]</div>';
            if ((int) $row['draft'] > 0) {
                $item_status = str_replace(
                    ['[COLOR]', '[TEXT]'],
                    ['txt-red', sprintf('Draft (expires: %s)', getDateFormatIfNotEmpty($row['draft_expire_date'], 'Y-m-d', 'j M, Y'))],
                    $item_status
                );
            } elseif (0 !== (int) $row['blocked']) {
                $item_status = str_replace(['[COLOR]', '[TEXT]'], ['txt-red', 'Blocked'], $item_status);
            } elseif (
                !filter_var((int) $row['visible'], FILTER_VALIDATE_BOOLEAN)
                || !filter_var((int) $row['moderation_is_approved'], FILTER_VALIDATE_BOOLEAN)
                || 'active' !== $row['user_status']
            ) {
                $item_status = str_replace(['[COLOR]', '[TEXT]'], ['txt-orange', 'Pending'], $item_status);
            } else {
                $item_status = str_replace(['[COLOR]', '[TEXT]'], ['txt-green', 'Published'], $item_status);
            }

            $translate_action = '<a class="ep-icon ep-icon_library fancyboxValidateModalDT fancybox.ajax" title="Add translation" href="items/popup_forms/admin_add_translation/' . $row['id'] . '" data-title="Add translation"></a>';
            switch($row['translated_status']){
                case 'removed':
                    $translate_bage = '<span class="label label-danger">removed</span>';
                    $translate_action = '';
                    break;
                case 'need_translate':
                    $translate_bage = '<span class="label label-warning">need translate</span>';
                    break;
                case 'translated':
                    $translate_bage = '<span class="label label-success">translated</span>';
                    break;
                default:
                    $translate_bage = '<span class="label label-default">None</span>';
                    $translate_action = '';
            }

            $pickMonthBtn = '';
            if(have_right('manage_picks_of_the_month')
                && (int) $row['visible'] == 1
                && (int) $row['moderation_is_approved'] == 1
                && (int) $row['draft'] == 0
                && (int) $row['blocked'] == 0
                && !$sellers_by_id[$row['id_seller']]['fake_user']
                )
            {
                $pickMonthBtn = '<a class="ep-icon ep-icon_star fancybox.ajax fancyboxValidateModalDT" href="items/popup_forms/make_pick_of_the_month/' . $row['id'] . '" data-title="Make this item pick of the month" title="Make pick of the month"></a>';
            }

            //TODO: admin chat hidden
            $btnChatUser = new ChatButton(['hide' => true, 'recipient' => $row['id_seller'], 'recipientStatus' => $row['user_status'], 'module' => 15, 'item' => $row['id']], ['classes' => 'btn-chat-now', 'text' => '']);
            $btnChat = $btnChatUser->button();
            $countryWithFlag = '';
            $filterByCountry = '';
            if (!empty($row['country'])) {
                $countryWithFlag = '<img class="mr-5" width="24" height="24" src="' . getCountryFlag($row['country']) . '" title="' . $row['country'] . '" alt="' . $row['country'] . '"/>' . $row['country'];
                $filterByCountry = '<a class="dt_filter ep-icon ep-icon_filter txt-green m-0" data-name="country" data-title="Country" data-value="' . $row['p_country'] . '" data-value-text="' . $row['country'] . '"></a>';
            }

            $cityName = '';
            $filterByCity = '';
            if (!empty($row['p_city']) && !empty($products_location[$row['id']]['item_city'])) {
                $cityName = $products_location[$row['id']]['item_city'];
                $filterByCity = '<a class="dt_filter ep-icon ep-icon_filter txt-green m-0" title="Filter by: ' . $products_location[$row['id']]['item_city'] . '" data-name="city" data-title="City" data-value="' . $row['p_city'] . '" data-value-text="' . $products_location[$row['id']]['item_city'] . '"></a>';
            }

            $output['aaData'][] = [
                'dt_item' =>
                    '<div class="flex-card">
                        <div class="flex-card__fixed w-100 h-100 image-card">
                            <img
                                class="image"
                                src="' . $item_img_link . '"
                                alt="' . $row['title'] . '"
                            />
                        </div>
                        <div class="flex-card__float">
                            <div class="flex-display flex-jc--sb">
                                <div class="w-100pr flex--1">'
                                    .$item_status
                                    .'<div class="clearfix">
                                        <strong class="pull-left lh-16 pr-5">Title: </strong>
                                        <a title="View item" href="' . __SITE_URL . 'item/' . strForURL($row['title']).'-'.$row['id'] . '" target="_blank">' . $row['title'] . '</a>
                                    </div>
                                    <div class="clearfix">
                                        <strong class="pull-left lh-16 pr-5">Category: </strong>
                                        ' . implode('<span class="pull-left lh-16 pr-2 pl-2"> &raquo; </span>', $cat_breadcrumbs)
                                    . '</div>
                                </div>
                                <div class="w-120">'
                                    . $rating
                                    . $highlight_item_filter
                                    . $featured_item_filter
                                    . $blocked_btn_filter
                                    . $visible_btn_filter
                                    . $partnered_item_filter
                                    . $fake_user_btn_filter
                                    . $archivedBtnFilter
                                .'</div>
                            </div>
                        </div>
                    </div>',
                'dt_translation' => $translate_bage,
                'dt_seller' =>
                    '<div>
                        <a class="ep-icon ep-icon_filter txt-green dt_filter" data-title="Seller" title="Filter by ' . $row['user_name'] . '" data-value-text="' . $row['user_name'] . '" data-value="' . $row['id_seller'] . '" data-name="seller"></a>
                        <a class="ep-icon ep-icon_user" title="View personal page of ' . $row['user_name'] . '" href="' . __SITE_URL . 'usr/' . strForURL($row['user_name']) . '-' . $row['id_seller'] . '" target="_blank"></a>'
                        . $company_icon
                    . '</div>
                    <div>' . $row['user_name'] . '</div>'
                    . $company_title,
                'dt_address' =>
                    '<div class="lh-24">' . $countryWithFlag . $filterByCountry .'</div>
                    <div>
                        ' . $cityName . $filterByCity . '
                    </div>',
                'dt_price_qty' => '<div class="clearfix"><strong class="pull-left lh-16 pr-5">Price: </strong> ' . $row['curr_entity'] . ' ' . $row['price'] . '</div>'
                    . '<div class="clearfix"><strong class="pull-left lh-16 pr-5">Discount: </strong> ' . $row['discount'] . '%</div>'
                    . '<div class="clearfix"><strong class="pull-left lh-16 pr-5">Quantity: </strong> ' . $row['quantity'] . '</div>'
                    . '<div class="clearfix"><strong class="pull-left lh-16 pr-5" title="Min. sale quantity">M.S. quantity: </strong> ' . $row['min_sale_q'] . '</div>',
                'dt_quantity' => '<div class="clearfix"><strong class="pull-left lh-16 pr-5">Price: </strong> ' . $row['curr_entity'] . ' ' . $row['price'] . '</div>'
                    . '<div class="clearfix"><strong class="pull-left lh-16 pr-5">Discount: </strong> ' . $row['discount'] . '%</div>'
                    . '<div class="clearfix"><strong class="pull-left lh-16 pr-5">Quantity: </strong> ' . $row['quantity'] . '</div>'
                    . '<div class="clearfix"><strong class="pull-left lh-16 pr-5" title="Min. sale quantity">M.S. quantity: </strong> ' . $row['min_sale_q'] . '</div>',
                'dt_create_date' => formatDate($row['create_date']),
                'dt_update_date' => formatDate($row['update_date']),
                'dt_statistics' =>
                    '<div class="pull-left w-50pr">
                        <span title="Number ordered">
                            <i class="ep-icon ep-icon_billing mb-0"></i>: '
                            . (($products_statistics[$row['id']]['count_ordered'] != '') ? $products_statistics[$row['id']]['count_ordered'] : '0')
                        .'</span>
                    </div>
                    <div class="pull-left w-50pr">
                        <a title="Number comments" href="' . __SITE_URL . 'items_comments/administration/item-' . $row['id'] . '" target="_blank">
                            <i class="ep-icon ep-icon_comment mb-0"></i>: '
                            . (($products_statistics[$row['id']]['count_comments'] != '') ? $products_statistics[$row['id']]['count_comments'] : '0')
                        . '</a>
                    </div>
                    <div class="pull-left w-50pr pt-5">
                        <a title="Number questions" href="' . __SITE_URL . 'items_questions/administration/item-' . $row['id'] . '" target="_blank">
                            <i class="ep-icon ep-icon_questions mb-0"></i>: '
                            . (($products_statistics[$row['id']]['count_questions'] != '') ? $products_statistics[$row['id']]['count_questions'] : '0')
                        . '</a>
                    </div>
                    <div class="pull-left w-50pr pt-5">
                        <a title="Number answered questions" href="' . __SITE_URL . 'items_questions/administration/item-' . $row['id'] . '/replied" target="_blank">
                            <i class="ep-icon ep-icon_support mb-0"></i>: '
                            . (($products_statistics[$row['id']]['count_q_answered'] != '') ? $products_statistics[$row['id']]['count_q_answered'] : '0')
                        . '</a>
                    </div>
                    <div class="pull-left w-50pr pt-5">
                        <a title="Number reviews" href="' . __SITE_URL . 'reviews/administration/item-' . $row['id'] . '" target="_blank">
                            <i class="ep-icon ep-icon_reviews mb-0"></i>: '
                            . $row['rev_numb']
                        . '</a>
                    </div>',
                'dt_actions' =>
                    '<a class="ep-icon ep-icon_pencil fancybox.ajax fancyboxValidateModalDT" title="Edit item" href="items/popup_forms/admin_edit_item/' . $row['id'] . '" data-title="Edit item" id="item-' . $row['id'] . '"></a>'
                    . $feature_btn
                    . $blocked_btn
                    . $pickMonthBtn
                    . $partner_btn
                    . $btnChat
                    . $translate_action
                    . $handmade_button
            ];
        }

        jsonResponse('', 'success', $output);
    }

    public function ajaxHandmadeItem ()
    {
        checkIsAjax();

        checkPermisionAjax('mark_item_as_handmade');

        $itemId = (int) $this->uri->segment(3);
        if (empty($itemId)) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }

        /** @var Products_Model $productsModel */
        $productsModel = model(Products_Model::class);

        $product = $productsModel->findOne($itemId);
        if (!empty($product)) {
            $isHandmade = !$product['is_handmade'];
            $productsModel->updateOne($itemId, ['is_handmade' => $isHandmade]);

            $message = translate('systmess_item_set_handmade');
            if (!$isHandmade) {
                $message = translate('systmess_item_unset_handmade');
            }
            jsonResponse($message, 'success');
        }
        jsonResponse(translate('systmess_error_invalid_data'));
    }

    function my() {
        if (!logged_in()){
            headerRedirect(__SITE_URL . 'login');
        }

        if (!have_right('manage_personal_items')) {
            $this->session->setMessages(translate("systmess_error_rights_perform_this_action"), 'errors');
            headerRedirect(__SITE_URL);
        }

        if (!i_have_company()) {
            $this->session->setMessages(translate("systmess_error_must_have_company_to_access_page"), 'errors');
            headerRedirect();
        }

        $this->load->model('Video_Tour_model', 'video_tour');

        checkGroupExpire();

        $id_user = privileged_user_id();
        $this->_load_main();

        if(isset($_GET['filter_featured'])){
            $data['filter_featured'] = (int) $_GET['filter_featured'];
        }

        if(isset($_GET['select_category'])){
            $data['select_category'] = intVal($_GET['select_category']);
        }

        if(isset($_GET['popup_add'])){
            $data['popup_add'] = 1;
        }

        $getParams = request()->query->all();
        if(isset($getParams['request'])){
            $data['requestDraftExtend'] = (int) cleanOutput($getParams['request']);
        }

        if(isset($getParams['expire'])){
            $data['filterExpiresDraft'] = getDateFormat($getParams['expire'], 'Y-m-d', 'Y-m-d');
        }

        $params = array('seller' => $id_user);
        $data['counter_categories'] = $this->category->get_cat_tree($params);
        $data['video_tour'] = $this->video_tour->get_video_tour(array("page" => "items/my", "user_group" => user_group_type()));
        $data['googleAnalyticsEvents'] = true;

        views(['new/header_view', 'new/item/my/index_view', 'new/footer_view'], $data);
    }

    public function ajax_my_items() {
        checkIsAjax();
        checkPermisionAjaxDT('manage_personal_items');

        $idUser = privileged_user_id();
        $params = [
            'seller' => $idUser,
            'per_p' => request()->request->getInt('iDisplayLength'),
            'start' => request()->request->getInt('iDisplayStart'),
            'seller_info' => true,
            'sort_by' => flat_dt_ordering($_POST, [
                'dt_item'        => 'it.title',
                'dt_quantity'    => 'it.quantity',
                'dt_update_date' => 'it.update_date'
            ])
        ];

        $conditions = [
            ['as' => 'featured',                'key' => 'featured',                'type' => 'int'],
            ['as' => 'highlight',               'key' => 'highlight',               'type' => 'int'],
            ['as' => 'visible',                 'key' => 'visible',                 'type' => 'int'],
            ['as' => 'country',                 'key' => 'country',                 'type' => 'int'],
            ['as' => 'blocked',                 'key' => 'blocked',                 'type' => 'int'],
            ['as' => 'is_archived',             'key' => 'archived',                'type' => 'int'],
            ['as' => 'city',                    'key' => 'city',                    'type' => 'int'],
            ['as' => 'start_from',              'key' => 'start_from',              'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'start_to',                'key' => 'start_to',                'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'update_from',             'key' => 'update_from',             'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'update_to',               'key' => 'update_to',               'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'expire',                  'key' => 'expiration_date',         'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'keywords',                'key' => 'sSearch',                 'type' => 'cleanInput'],
        ];

        $params = array_merge($params, dtConditions($_POST, $conditions));

        $idCategory = request()->request->getInt('parent');
        if ($idCategory) {
            $category = model(Category_Model::class)->get_category($idCategory);
            $params['categories_list'] = [$idCategory];
            if (!empty($categoryChildren = array_filter(explode(',', $category['cat_childrens'])))){
                $params['categories_list'] = array_merge($params['categories_list'], $categoryChildren);
            }

            $params['categories_list'] = implode(',', $params['categories_list']);
        }

        $attrs = array();
        foreach ($_POST as $key => $value) {
            if (preg_match('/^attrs_/', $key)) {
                $key = explode('_', $key);
                $key = end($key);
                $attrs[$key] = $value;
            }
        }

        if (!empty($attrs)){
            $params['attrs'] = $attrs;
        }

        $r_attrs = array();
        foreach ($_POST as $key => $value) {
            if (preg_match('/^range_attrs_/', $key)) {
                $components = explode('_', $key);
                $r_attrs[$components[3]][$components[2]] = $value;
            }
        }

        if (!empty($r_attrs)){
            $params['r_attrs'] = $r_attrs;
        }

        $t_attrs = array();
        foreach ($_POST as $key => $value) {
            if (preg_match('/^text_attrs_/', $key)) {
                $t_attrs[end(explode('_', $key))] = $value;
            }
        }

        if (!empty($t_attrs)){
            $params['t_attrs'] = $t_attrs;
        }

        $data['products'] = model(Items_Model::class)->get_items($params);
        $products_count = model(Items_Model::class)->count_items($params);

        $output = array(
            "sEcho" => request()->request->getInt('sEcho'),
            "iTotalRecords" => $products_count,
            "iTotalDisplayRecords" => $products_count,
            'aaData' => []
        );

        if(empty($data['products'])){
            jsonDTResponse('', $output, 'success');
        }

        $statisticsConditions = [];
        if (have_right('write_comments_on_item')) {
            $statisticsConditions['comments']['user'] = id_session();
		} else {
			$statisticsConditions['comments']['not_id_user'] = privileged_user_id();
		}

        $idItems = implode(',', array_column($data['products'], 'id'));
        $data['products_location'] = model(Items_Model::class)->getItemsLocation($idItems);
        $data['products_statistics'] = model(Items_Model::class)->get_items_statistics($idItems, $statisticsConditions);
        $data['main_images'] = model(Items_Model::class)->items_main_photo(array('main_photo' => 1, 'items_list' => $idItems));

        $output['aaData'] = $this->_my_items($data);

        jsonDTResponse('', $output, 'success');
    }

    private function _my_items($data) {
        /** @var Featured_Products_Model $featuredProductsModel */
        $featuredProductsModel = model(Featured_Products_Model::class);

        $featuredItems = array_column(
            $featuredProductsModel->findAllBy([
                'columns'       => [
                    "`{$featuredProductsModel->getTable()}`.*",
                ],
                'conditions'    => [
                    'sellerId'  => privileged_user_id(),
                    'itemIds'   => array_column($data['products'], 'id'),
                ],
                'joins'         => [
                    'items',
                ],
            ]),
            null,
            'id_item'
        );

        if (!empty($featuredItems)) {
            $featuredItemsByStatus = arrayByKey($featuredItems, 'status', true);

            if (!empty($featuredItemsByStatus[(string) FeaturedStatus::INIT()])) {
                /** @var Bills_Model $billsModel */
                $billsModel = model(Bills_Model::class);

                $featuredItemsPaidBills = array_column(
                    $billsModel->findAllBy([
                        'scopes'    => [
                            'itemIds'  => array_column($featuredItemsByStatus[(string) FeaturedStatus::INIT()], 'id_featured'),
                            'status'    => BillStatus::PAID(),
                            'type'      => BillTypes::getId(BillTypes::FEATURE_ITEM()),
                        ],
                    ]),
                    null,
                    'id_item'
                );
            }
        }

        /** @var Highlighted_Products_Model $highlightedProductsModel */
        $highlightedProductsModel = model(Highlighted_Products_Model::class);

        $highlightedItems = array_column(
            $highlightedProductsModel->findAllBy([
                'columns'       => [
                    "`{$highlightedProductsModel->getTable()}`.*",
                ],
                'conditions'    => [
                    'sellerId'  => privileged_user_id(),
                    'itemIds'   => array_column($data['products'], 'id'),
                ],
                'joins'         => [
                    'items',
                ],
            ]),
            null,
            'id_item'
        );

        if (!empty($highlightedItems)) {
            $highlightedItemsByStatus = arrayByKey($highlightedItems, 'status', true);

            if (!empty($highlightedItemsByStatus[HighlightedStatus::INIT])) {
                /** @var Bills_Model $billsModel */
                $billsModel = model(Bills_Model::class);

                $highlightedItemsPaidBills = array_column(
                    $billsModel->findAllBy([
                        'scopes'    => [
                            'itemIds'  => array_column($highlightedItemsByStatus[HighlightedStatus::INIT], 'id_highlight'),
                            'status'    => BillStatus::PAID(),
                            'type'      => BillTypes::getId(BillTypes::HIGHLIGHT_ITEM()),
                        ],
                    ]),
                    null,
                    'id_item'
                );
            }
        }

        $is_free_featured_items_process = (int) config('is_free_featured_items');
        $have_right_feature_item = have_right('feature_item');

        $is_free_highlight_items_process = (int) config('is_free_highlight_items');
        $have_right_highlight_item = have_right('highlight_item');

        $productsIds = array_column($data['products'], 'id');

        /** @var Items_Variants_Model $itemsVariantsModel */
        $itemsVariantsModel = model(Items_Variants_Model::class);

        $productsVarianst = arrayByKey(
            $itemsVariantsModel->findAllBy([
                'conditions' => [
                    'itemIds'   => $productsIds,
                ],
            ]),
            'id_item',
            true
        );

        foreach ($data['products'] as $row) {
            $cat_breadcrumbs = array();
            $item_breadcrumbs = json_decode('[' . $row['breadcrumbs'] . ']', true);

            if (!empty($item_breadcrumbs)) {
                foreach ($item_breadcrumbs as $bread) {
                    foreach ($bread as $cat_id => $cat_title)
                        $cat_breadcrumbs[] = '<a class="link" href="'.__SITE_URL.'category/'. strForURL($cat_title) . '/'.$cat_id.'" target="_blank">'. $cat_title . '</a>';
                }
            }

            $main_image = search_in_array($data['main_images'], 'sale_id', $row['id']);

            $rating = '<input class="rating-bootstrap" data-filled="ep-icon ep-icon_star txt-orange fs-16" data-empty="ep-icon ep-icon_star txt-gray-light fs-16" type="hidden" name="val" value="0" data-readonly>';
            if($row['rev_numb']){
                $rating = '<input class="rating-bootstrap" data-filled="ep-icon ep-icon_star txt-orange fs-16" data-empty="ep-icon ep-icon_star txt-gray-light fs-16" type="hidden" name="val" value="' . $row['rating'] . '" data-readonly>';
            }

            $visible_btn_filter = '';
            $opacity_product = '';
            if (!$row['visible']){
                $visible_btn_filter = '<i class="ep-icon ep-icon_invisible mr-10"></i>';
                $opacity_product = 'main-data-table__item-opacity';
            }

            $blocked_btn_filter = '';
            if (1 == $row['blocked']) {
                $blocked_btn_filter = '<i class="ep-icon ep-icon_locked txt-red mr-10"></i>';
            } elseif (2 == $row['blocked'] && $row['visible']) {
                $blocked_btn_filter = '<i class="ep-icon ep-icon_info txt-blue2 mr-10" title="This item will be visible to users after account activation."></i>';
            }

            $highlight_btn = '';
            $reinit_highlight = '';
            if ($have_right_highlight_item && !$row['draft']) {
                if (!$row['highlight'] && !$row['highlight_status']) {
                    $highlight_btn = "
                        <a class=\"dropdown-item confirm-dialog\"
                            data-message=\"" . translate('systmess_confirm_highlight_item', null, true) . "\"
                            data-callback=\"highlight_item\"
                            data-item=\"{$row['id']}\"
                            title=\"Highlight this item\"
                            rel=\"highlight\">
                            <i class=\"ep-icon ep-icon_highlight\"></i>
                            <span>Highlight this item</span>
                        </a>
                    ";
                }

                if ($row['highlight_status']) {
                    if (!$row['extend_highlight'] && ($row['highlight_status'] != 'init')) {
                        if ('active' === $row['highlight_status']) {
                            if ($is_free_highlight_items_process) {
                                $reinit_highlight = "
                                    <a class=\"dropdown-item call-systmess disabled\"
                                        data-message=\"" . translate('systmess_info_extend_free_highlighted_item', null, true) . "\"
                                        data-type=\"info\"
                                        title=\"Extend the active highlight status\">
                                        <i class=\"ep-icon ep-icon_hourglass-plus\"></i>
                                        <span>Extend the highlight</span>
                                    </a>
                                ";
                            } else {
                                $reinit_highlight = "
                                    <a class=\"dropdown-item confirm-dialog\"
                                        data-message=\"" . translate('systmess_confirm_extend_highlight_item', null, true) . "\"
                                        data-callback=\"rehighlight_item\"
                                        data-item=\"{$row['id_highlight']}\"
                                        title=\"Extend the active highlight status\">
                                        <i class=\"ep-icon ep-icon_hourglass-plus\"></i>
                                        <span>Extend the highlight</span>
                                    </a>
                                ";
                            }
                        } else {
                            $reinit_highlight = "
                                <a class=\"dropdown-item confirm-dialog\"
                                    data-message=\"" . translate('systmess_confirm_renew_highlight_item', null, true) . "\"
                                    data-callback=\"rehighlight_item\"
                                    data-item=\"{$row['id_highlight']}\"
                                    title=\"Renew the highlight status\">
                                    <i class=\"ep-icon ep-icon_refresh\"></i>
                                    <span>Renew the highlight</span>
                                </a>
                            ";
                        }
                    } else {
                        $reinit_highlight_url = getUrlForGroup("highlight/my/highlight_number/{$row['id_highlight']}", arrayGet($row, 'gr_type', 'seller'));
                        $reinit_highlight = "
                            <a href=\"{$reinit_highlight_url}\"
                                class=\"dropdown-item\"
                                title=\"Your request for being highlight is being processed. View more detail.\">
                                <i class=\"ep-icon ep-icon_hourglass-processing\"></i>
                                <span>Highlight detail</span>
                            </a>
                        ";
                    }
                }
            }

            $feature_btn = '';
            $reinit_feature = '';
            if ($have_right_feature_item && !$row['draft']) {
                if (!$row['featured'] && !$row['featured_status']) {
                    $feature_btn = "
                        <a class=\"dropdown-item confirm-dialog\"
                            data-message=\"" . translate('systmess_confirm_feature_item', null, true) . "\"
                            data-callback=\"feature_item\"
                            data-item=\"{$row['id']}\"
                            title=\"Feature this item\"
                            rel=\"feature\">
                            <i class=\"ep-icon ep-icon_arrow-line-up\"></i>
                            <span>Feature this item</span>
                        </a>
                    ";
                }

                if ($row['featured_status']) {
                    if (!$row['extend_feature'] && ($row['featured_status'] != 'init')) {
                        if ('active' === $row['featured_status']) {
                            if ($is_free_featured_items_process) {
                                $reinit_feature = "
                                    <a class=\"dropdown-item disabled call-systmess\"
                                        data-message=\"" . translate('systmess_info_extend_free_featured_item', null, true) . "\"
                                        data-type=\"info\"
                                        title=\"Extend the active featured status\">
                                        <i class=\"ep-icon ep-icon_hourglass-plus\"></i>
                                        <span>Extend the featured</span>
                                    </a>
                                ";
                            } else {
                                $reinit_feature = "
                                    <a class=\"dropdown-item confirm-dialog\"
                                        data-message=\"" . translate('systmess_confirm_extend_feature_item', null, true) . "\"
                                        data-callback=\"refeature_item\"
                                        data-item=\"{$row['id_featured']}\"
                                        title=\"Extend the active featured status\">
                                        <i class=\"ep-icon ep-icon_hourglass-plus\"></i>
                                        <span>Extend the featured</span>
                                    </a>
                                ";
                            }
                        } else {
                            $reinit_feature = "
                                <a class=\"dropdown-item confirm-dialog\"
                                    data-message=\"" . translate('systmess_confirm_renew_feature_item', null, true) . "\"
                                    data-callback=\"refeature_item\"
                                    data-item=\"{$row['id_featured']}\"
                                    title=\"Renew the featured status\">
                                    <i class=\"ep-icon ep-icon_refresh\"></i>
                                    <span>Renew the featured</span>
                                </a>
                            ";
                        }
                    } else {
                        $reinit_feature_url = getUrlForGroup("featured/my/featured_number/{$row['id_featured']}", arrayGet($row, 'gr_type', 'seller'));
                        $reinit_feature = "
                            <a href=\"{$reinit_feature_url}\"
                                class=\"dropdown-item\"
                                title=\"Your request for being featured is being processed. View more detail.\">
                                <i class=\"ep-icon ep-icon_hourglass-processing\"></i>
                                <span>Featured detail</span>
                            </a>
                        ";
                    }
                }
            }

            $visible_btn = '<a class="dropdown-item confirm-dialog" data-callback="visibility_item" data-message="' . translate('systmess_confirm_set_inactive_item', null, true) . '" data-item="' . $row['id'] . '" data-visible="0" title="Set item inactive">
                                <i class="ep-icon ep-icon_invisible"></i>
                                <span>Set item inactive</span>
                            </a>';

            if (!$row['visible']) {
                if (!$row['draft']) {
                    $visible_btn = '<a class="dropdown-item confirm-dialog" data-callback="visibility_item" data-message="' . translate('systmess_confirm_set_active_item', null, true) . '" data-item="' . $row['id'] . '" data-visible="1" title="Set item active">
                        <i class="ep-icon ep-icon_visible"></i>
                        <span>Set item active</span>
                    </a>';
                } else {
                    $visible_btn = '<a class="dropdown-item call-systmess" data-type="warning" data-message="' . translate('systmess_info_cannot_published_draft_item', null, true) . '" title="Set item active">
                        <i class="ep-icon ep-icon_visible"></i>
                        <span>Set item active</span>
                    </a>';
                }
            }

            $deleteDraftBtn = '';
            if($row['draft']){
                $deleteDraftBtn = "<a
                    class=\"dropdown-item confirm-dialog\"
                    data-callback=\"delete_draft_item\"
                    data-message=\"" . translate('systmess_confirm_delete_draft_item', null, true) . "\"
                    data-item=\"" . $row['id'] . "\"
                    title=\"Delete draft\">
                        <i class=\"ep-icon ep-icon_trash-stroke\"></i>
                        <span>Delete draft</span>
                </a>";
            }

            $archiveBtn = '';
            if ($row['is_archived']) {
                $archiveBtn = "<a
                    class=\"dropdown-item confirm-dialog\"
                    data-callback=\"return_from_archive\"
                    data-message=\"" . translate('systmess_confirm_return_item_from_archive', null, true) . "\"
                    data-item=\"" . $row['id'] . "\"
                    title=\"Return from Archive\">
                        <i class=\"ep-icon ep-icon_box\"></i>
                        <span>Return from Archive</span>
                </a>";
            } else {
                if (
                    $row['draft']
                    || !$row['visible']
                    || 1 == $row['blocked']
                ) {
                    $archiveBtn = "<a
                        class=\"dropdown-item confirm-dialog\"
                        data-callback=\"add_to_archive\"
                        data-message=\"" . translate('systmess_confirm_add_item_to_archive', null, true) . "\"
                        data-item=\"" . $row['id'] . "\"
                        title=\"Archive\">
                            <i class=\"ep-icon ep-icon_box-close\"></i>
                            <span>Archive</span>
                    </a>";
                } else {
                    $archiveBtn = "<a
                        class=\"dropdown-item call-systmess txt-gray\"
                        data-message=\"" . translate('systmess_error_add_item_to_archive', null, true) . "\"
                        data-type=\"info\"
                        title=\"Archive\">
                            <i class=\"ep-icon ep-icon_box-close\"></i>
                            <span>Archive</span>
                    </a>";
                }
            }

            $item_info = "";

            if (isset($featuredItems[$row['id']])) {
                if (FeaturedStatus::INIT() === $featuredItems[$row['id']]['status']) {
                    $item_info .= sprintf(
                        <<<PAY_FEATURE_BILL
                        <div class="main-data-table__item-action bg-gray">
                            <a href="%s"
                                class="text"
                                target="_blank">
                                %s
                            </a>
                        </div>
                        PAY_FEATURE_BILL,
                        __SITE_URL . "billing/my/type/feature_item/featured/{$row['id_featured']}",
                        isset($featuredItemsPaidBills[$row['id_featured']])
                            ? translate('featured_item_waiting_to_confirm_bill', null, true)
                            : translate('featured_items_need_to_pay_bill', null, true)
                    );
                } elseif (FeaturedStatus::ACTIVE() === $featuredItems[$row['id']]['status']) {
                    if ($featuredItems[$row['id']]['end_date'] > (new \DateTime())) {
                        $item_info .= sprintf(
                            <<<FEATURED_TILL
                                <div class="main-data-table__item-action bg-orange">
                                    <div class="text">
                                        Featured till %s
                                    </div>
                                </div>
                            FEATURED_TILL,
                            $featuredItems[$row['id']]['end_date']->format('j M, Y')
                        );
                    } else {
                        $item_info .= <<<FEATURED
                            <div class="main-data-table__item-action bg-orange">
                                <div class="text">
                                    Featured
                                </div>
                            </div>
                        FEATURED;
                    }
                }
            }

            if (isset($highlightedItems[$row['id']]) && HighlightedStatus::INIT() === $highlightedItems[$row['id']]['status']) {
                $item_info .= sprintf(
                    <<<PAY_HIGHLIGHT_BILL
                        <div class="main-data-table__item-action bg-gray">
                            <a href="%s" class="text" target="_blank">
                                %s
                            </a>
                        </div>
                    PAY_HIGHLIGHT_BILL,
                    __SITE_URL . 'billing/my/type/highlight_item/highlight/' . $row['id_highlight'],
                    isset($highlightedItemsPaidBills[$row['id_highlight']])
                        ? translate('highlighted_item_waiting_to_confirm_bill', null, true)
                        : translate('highlighted_items_need_to_pay_bill', null, true)
                );
            }

            if ($row['highlight']) {
                if ($highlightedItems[$row['id']]['end_date'] > (new \DateTime())) {
                    $item_info .= sprintf(<<<HIGHLIGHTED_TILL
                            <div class="main-data-table__item-action bg-blue2">
                                <div class="text">
                                    Highlighted till %s
                                </div>
                            </div>
                        HIGHLIGHTED_TILL,
                        $highlightedItems[$row['id']]['end_date']->format('j M, Y')
                    );
                } else {
                    $item_info .= <<<HIGHLIGHTED
                        <div class="main-data-table__item-action bg-blue2">
                            <div class="text">
                                Highlighted
                            </div>
                        </div>
                    HIGHLIGHTED;
                }
            }

            $image_item = '<img
                                class="image"
                                src="' . getDisplayImageLink(array('{ID}' => $row['id'], '{FILE_NAME}' => $main_image['photo_name']), 'items.main', array( 'thumb_size' => 1 )) . '"
                                alt="' . $row['title'] . '"
                            />';


            $item_status = '<div class="main-data-table__item-status [COLOR]">[TEXT]</div>';
            if ((int) $row['draft'] > 0) {
                $item_status = str_replace(
                    ['[COLOR]', '[TEXT]'],
                    ['txt-red', translate('items_datagrid_status_draft_text', ['[DATE]' => getDateFormatIfNotEmpty($row['draft_expire_date'], 'Y-m-d', 'j M, Y')], true)],
                    $item_status
                );
            } elseif (0 !== (int) $row['blocked']) {
                $item_status = str_replace(['[COLOR]', '[TEXT]'], ['txt-red', translate('items_datagrid_status_blocked_text', null, true)], $item_status);
            } elseif (
                !filter_var((int) $row['visible'], FILTER_VALIDATE_BOOLEAN)
                || !filter_var((int) $row['moderation_is_approved'], FILTER_VALIDATE_BOOLEAN)
                || 'active' !== $row['user_status']
            ) {
                $item_status = str_replace(['[COLOR]', '[TEXT]'], ['txt-orange', translate('items_datagrid_status_pending_text', null, true)], $item_status);
            } else {
                $item_status = str_replace(['[COLOR]', '[TEXT]'], ['txt-green', translate('items_datagrid_status_published_text', null, true)], $item_status);
            }

            if ($row['is_out_of_stock']) {
                $opacity_product = 'main-data-table__item-opacity';
            }

            //region set item quantity
            if ($row['is_out_of_stock']) {
                $itemQuantity = '<span class="fs-14 txt-gray">OUT OF STOCK</span>';

                if ($row['samples']) {
                    $itemQuantity .= '<br/><span class="fs-14 txt-gray">SAMPLES ONLY</span>';
                }
            } else {
                $totalQuantity = $row['quantity'];
                if (isset($productsVarianst[$row['id']])) {
                    $totalQuantity = array_sum(array_column($productsVarianst[$row['id']], 'quantity'));
                }

                $itemQuantity = <<<ITEM_QUANTITY
                <div class="dtable-params__item">
                    <span class="txt-gray">Quantity: </span>{$totalQuantity}
                </div>
                <div class="dtable-params__item">
                    <span class="txt-gray" title="Min. sale quantity">M.S. quantity: </span>$row[min_sale_q]
                </div>
                ITEM_QUANTITY;
            }
            //endregion set item quantity

            //region set item price
            if (isset($productsVarianst[$row['id']])) {
                $variantPrices = array_column($productsVarianst[$row['id']], 'final_price');
                $minPrice = $row['curr_entity'] . moneyToDecimal(Money::min(...$variantPrices));
                $maxPrice = $row['curr_entity'] . moneyToDecimal(Money::max(...$variantPrices));

                $itemPrice = <<<ITEM_PRICE
                <div class="dtable__params">
                    <div class="dtable-params__item">
                        <span class="txt-gray">Min price: </span>{$minPrice}
                    </div>
                    <div class="dtable-params__item">
                        <span class="txt-gray">Max Price: </span>{$maxPrice}
                    </div>
                </div>
                ITEM_PRICE;
            } else {
                $itemPrice = <<<ITEM_PRICE
                <div class="dtable__params">
                    <div class="dtable-params__item">
                        <span class="txt-gray">Price: </span>
                        {$row['curr_entity']} {$row['price']}
                    </div>
                </div>
                ITEM_PRICE;

                if (!empty($row['discount'])) {
                    $itemPrice .= <<<ITEM_PRICE
                    <div class="dtable-params__item">
                        <span class="txt-gray">Final price: </span>
                        {$row['curr_entity']} {$row['final_price']}
                    </div>
                    <div class="dtable-params__item">
                        <span class="txt-gray">Discount: </span>
                        {$row['discount']}%
                    </div>
                    ITEM_PRICE;
                }
            }
            //endregion set item price

            $output[] = array(
                'dt_item' =>
                    '<div class="flex-card relative-b '.$opacity_product.'">
                        <div class="main-data-table__item-actions">' . $item_info . '</div>
                        <div class="flex-card__fixed main-data-table__item-img main-data-table__item-img--h-auto image-card3">
                            <span class="link">' . $image_item . '</span>
                        </div>
                        <div class="flex-card__float">'
                            .$item_status.
                            '<div class="main-data-table__item-ttl">'
                                . $visible_btn_filter
                                . $blocked_btn_filter
                                .'<a class="display-ib link-black txt-medium" title="View item" href="' . __SITE_URL . 'item/' . strForURL($row['title']).'-'.$row['id'] . '"' . ' target="_blank">'
                                    . $row['title']
                                . '</a>
                            </div>
                            <div class="">'. $rating .'</div>
                            <div class="links-black">'. implode('<span class=""> / </span>', $cat_breadcrumbs). '</div>
                        </div>
                    </div>',
                'dt_address' =>
                    '<div class="">
                        <img width="24" height="24" src="' . getCountryFlag($row['country']) . '" title="' . $row['country'] . '" alt="' . $row['country'] . '"/>
                        '.$row['country'].'
                    </div>
                    <div class="txt-gray">
                        ' . $data['products_location'][$row['id']]['item_city']. '
                    </div>',
                'dt_price' => $itemPrice,
                'dt_quantity' => $itemQuantity,
                'dt_create_date' => formatDate($row['create_date'], 'j M, Y H:i'),
                'dt_update_date' => formatDate($row['update_date'], 'j M, Y H:i'),
                'dt_statistics' =>
                    '<div class="dtable-params">
                        <div class="dtable-params__item">
                            <span class="txt-gray">Orders: </span>
                            <a class="link" title="Number ordered" href="'.__SITE_URL.'order/my/item/'.$row['id'].'" target="_blank">'
                                . (($data['products_statistics'][$row['id']]['count_ordered'] != '') ? $data['products_statistics'][$row['id']]['count_ordered'] : '0')
                            .'</a>
                        </div>
                        <div class="dtable-params__item">
                            <span class="txt-gray">Comments: </span>
                            <a class="link" title="Item comments" href="' . __SITE_URL . 'items_comments/my/item/' . $row['id'] . '">'
                                . (($data['products_statistics'][$row['id']]['count_comments'] != '') ? $data['products_statistics'][$row['id']]['count_comments'] : '0')
                            .'</a>
                        </div>
                        <div class="dtable-params__item">
                            <span class="txt-gray">Questions: </span>
                            <a class="link" title="Number questions" href="'.__SITE_URL.'items_questions/my/item/'.$row['id'].'" target="_blank">'
                                . (($data['products_statistics'][$row['id']]['count_questions'] != '') ? $data['products_statistics'][$row['id']]['count_questions'] : '0')
                            .'</a>
                        </div>
                        <div class="dtable-params__item">
                            <span class="txt-gray">Answers: </span>
                            <a class="link" title="Number answered questions" href="'.__SITE_URL.'items_questions/my/item/'.$row['id'].'" target="_blank">'
                                . (($data['products_statistics'][$row['id']]['count_q_answered'] != '') ? $data['products_statistics'][$row['id']]['count_q_answered'] : '0')
                            .'</a>
                        </div>
                        <div class="dtable-params__item">
                            <span class="txt-gray">Reviews: </span>
                            <a class="link" title="Number reviews" href="'.__SITE_URL.'reviews/my/item/'.$row['id'].'" target="_blank">' . $row['rev_numb']
                            .'</a>
                        </div>
                    </div>',
                'dt_actions' =>
                        '<div class="dropdown" '. addQaUniqueIdentifier("items-my__dropdown-" . $row['id']) .'>
                            <a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" ' . addQaUniqueIdentifier("items-my__dropdown-btn") . '>
                                <i class="ep-icon ep-icon_menu-circles"></i>
                            </a>

                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item fancyboxAddItem fancybox.ajax js-fancyboxEditItem" href="' . __SITE_URL . 'items/add/' . strForURL($row['title']) . '-'. $row['id'] . '" data-title="Edit item" title="Edit item" target="_blank" ' . addQaUniqueIdentifier("items-my__edit-item") . '>
                                    <i class="ep-icon ep-icon_pencil"></i>
                                    <span>Edit item</span>
                                </a>'
                                . $visible_btn
                                . $feature_btn
                                . $highlight_btn
                                . $reinit_feature
                                . $reinit_highlight
                                . $deleteDraftBtn
                                . $archiveBtn
                                .'<a class="dropdown-item d-none d-md-block d-lg-block d-xl-none call-function" data-callback="dataTableAllInfo" href="#" target="_blank">
                                    <i class="ep-icon ep-icon_info-stroke"></i>
                                    <span>All info</span>
                                </a>
                            </div>
                        </div>',
            );
        }

        return $output;
    }

    function ordered(){
        /** @var Products_Model $productsModel */
        $productsModel = model(Products_Model::class);

        $this->load->model('Items_Model', 'items');
        $this->load->model('Company_Model', 'company');
        $this->load->model('User_Model', 'user');

        $this->load->model('UserGroup_Model', 'user_group');
        $this->load->model('Orders_Model', 'orders');

        $id_user = privileged_user_id();
        $id_ordered = id_from_link($this->uri->segment(3));
        $data['item'] = $this->orders->get_ordered_item($id_ordered);
        if(empty($data['item'])){
            show_404();
        }

        $search_ids = array($data['item']['id_buyer'], $data['item']['id_seller'], $data['item']['ep_manager']);
        if (!empty($data['item']['id_shipper'])) {
            $search_ids[] = $data['item']['id_shipper'];
        }

        //if is member of order or shipper have order rate for this order
        $data['member_of_order'] = false;
        if(
            in_array($id_user, $search_ids)
            || have_right('manage_shipper_orders')
        ) {
            $data['member_of_order'] = true;
        }

        $current_item = $this->items->get_item($data['item']['id_item']);
        $data['company_info'] = $this->company->get_company(array('id_user' => $current_item['id_seller']));
        if(empty($data['company_info'])){
            show_404();
        }

        if(!empty($data['item']['aditional_info'])){
            $data['aditional_info'] = unserialize($data['item']['aditional_info']);
            if(!empty($data['aditional_info']['attr_info'])){
                $data['aditional_info']['attr_info'] = explode('|', $data['aditional_info']['attr_info']);
            }

            if(!empty($data['aditional_info']['vin_info'])){
                $data['aditional_info']['vin_info'] = explode('|', $data['aditional_info']['vin_info']);
            }
        }

        $data['last_viewed_items'] = $productsModel->runWithoutAllCasts(
            fn () => $productsModel->getItemsForLastViewed(
                getEpClientIdCookieValue(),
                $data['item']['id_item'],
                false,
                config('limit_last_viewed_for_not_logged_users')
            )
        );

        $data['last_viewed_items'] = $this->formatProductPrice($data['last_viewed_items']);

        if (logged_in()) {
            $chatBtn = new ChatButton(['recipient' => $data['company_info']['id_user'], 'recipientStatus' => $data['company_info']['status']]);
            $data['company_info']['btnChat'] = $chatBtn->button();
        }

        $this->breadcrumbs[] = array(
            'link'	=> __SITE_URL.'items/ordered/'.strForUrl($data['item']['title'].' '.$id_ordered),
            'title'	=> $data['item']['title']
        );

        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['main_content'] = 'new/item/items_ordered/index_view';
        $this->view->assign($data);
        $this->view->display('new/index_template_view');
    }

    /** @deprecated */
    public function change_info() {
        if (!isAjaxRequest())
            headerRedirect();

        if (!logged_in())
            jsonResponse('Error: You must be logged in to make changes.');

        if (!have_right('manage_personal_items'))
            jsonResponse(translate("systmess_error_rights_perform_this_action"));

        if (isset($_POST['name'])) {
            $this->load->model('Items_Model', 'items');
            $type_name = explode('-', $_POST['name']);
            list($change_type, $change_name) = explode('-', $_POST['name']);

            switch ($change_type) {
                case 'main':
                    $id_item = intVal($_POST['id']);
                    if (!$this->items->my_item(privileged_user_id(), $id_item))
                        jsonResponse(translate("systmess_error_rights_perform_this_action"));

                    $this->items->validation_rule_add = array();
                    switch($change_name){
                        case 'title' :
                            $this->items->validation_rule_add[] = array(
                                'field' => 'value',
                                'label' => 'Title',
                                'rules' => array('required' => '', 'valide_title' => '', 'min_len[4]' => '', 'max_len[255]' => '')
                            );

                            $value = cleanInput($_POST['value']);
                        break;
                        case 'price' :
                            $this->items->validation_rule_add[] = array(
                                'field' => 'value',
                                'label' => 'Price',
                                'rules' => array('required' => '', 'positive_number' => '', 'min[1]' => '')
                            );

                            $value = floatval($_POST['value']);
                        break;
                        case 'discount' :
                            $this->items->validation_rule_add[] = array(
                                'field' => 'value',
                                'label' => 'Discount',
                                'rules' => array('required' => '', 'min[0]' => '', 'max[100]' => '', 'integer' => '')
                            );

                            $value = intval($_POST['value']);
                        break;
                        case 'quantity' :
                            $this->items->validation_rule_add[] = array(
                                'field' => 'value',
                                'label' => 'Quantity',
                                'rules' => array('required' => '', 'integer' => '')
                            );

                            $value = intval($_POST['value']);
                        break;
                        case 'min_sale_q' :
                            $this->items->validation_rule_add[] = array(
                                'field' => 'value',
                                'label' => 'Minimal sale quantity',
                                'rules' => array('required' => '', 'integer' => '', 'min[1]' => '')
                            );

                            $value = intval($_POST['value']);
                        break;
                        case 'unit_type' :
                            $this->items->validation_rule_add[] = array(
                                'field' => 'value',
                                'label' => 'Unit type',
                                'rules' => array('required' => '', 'natural' => '')
                            );

                            $value = intval($_POST['value']);
                        break;
                        default:
                            jsonResponse('Error: Information cannot be changed.');
                        break;
                    }

                    $this->validator->set_rules($this->items->validation_rule_add);

                    if (!$this->validator->validate())
                        jsonResponse($this->validator->get_array_errors());

                    $update = array(
                        'id' => $id_item,
                        $change_name => $value,
                        'changed' => 1
                    );

                    if ($change_name == 'discount' || $change_name == 'price') {
                        $item = $this->items->get_item($id_item);

                        if ($change_name == 'discount'){
                            $finalPrice = ($item['price'] - ($item['price'] * intVal($_POST['value']) / 100));
                        }

                        if ($change_name == 'price'){
                            $finalPrice = (intVal($_POST['value']) - (intVal($_POST['value']) * $item['discount'] / 100));
                        }

                        $update['final_price'] = $finalPrice;
                    }

                    if ($this->items->update_item($update)) {
                        $this->load->model('Elasticsearch_Items_Model', 'elasticsearch_items');
                        $this->elasticsearch_items->index($id_item);

                        jsonResponse('', 'success', array('val' => $value));
                    } else {
                        jsonResponse('Error: Information cannot be changed. Please try again later.');
                    }
                break;
                case 'html':
                    $id_item = intVal($_POST['id']);
                    if (!$this->items->my_item(privileged_user_id(), $id_item))
                        jsonResponse(translate("systmess_error_rights_perform_this_action"));

                    $this->items->validation_rule_add = array();
                    $this->load->library('Cleanhtml', 'clean');

                    $value = '';
                    $update = array();
                    switch($change_name){
                        case 'video' :
                            $this->items->validation_rule_add[] = array(
                                'field' => 'value',
                                'label' => 'Video',
                                'rules' => array('required' => '','valid_url' => '', 'max_len[200]' => '')
                            );

                            $this->validator->set_rules($this->items->validation_rule_add);

                            if (!$this->validator->validate())
                                jsonResponse($this->validator->get_array_errors());

                            $item_info = $this->items->get_item($id_item);
                            if ($item_info['video'] != $_POST['value']) {
                                $this->load->library('videothumb');
                                $video_link = $this->videothumb->getVID($_POST['value']);
                                $new_video = $this->videothumb->process($_POST['value']);

                                if (isset($new_video['error'])) {
                                    jsonResponse($new_video['error']);
                                }

                                $update['video_source'] = $video_link['type'];

                                $path = getImgPath('items.main', array('{ID}' => $id_item));
                                if (!is_dir($path))
                                    mkdir($path, 0777);

                                $file_video[] = $new_video['image'];
                                $conditions = array(
                                    'images' => $file_video,
                                    'destination' => $path,
                                    'resize' => '730xR'
                                );
                                $res = $this->upload->copy_images_new($conditions);

                                if (!empty($res['errors'])) {
                                    jsonResponse($res['errors']);
                                }
                                @unlink($path . '/' . $item_info['video_image']);
                                @unlink($new_video['image']);

                                $update['video_image'] = $res[0]['new_name'];
                                $update['video'] = $_POST['value'];
                                $update['video_code'] = $new_video['v_id'];

                                $value = sprintf(
                                    <<<HTML
                                        <a class="ppersonal-company-video wr-video-link fancybox.iframe fancyboxVideo" href="%s" data-title="Item overview">
                                            <div class="bg"><i class="ep-icon ep-icon_play"></i></div>
                                            <img class="image" src="%s" alt="%s">
                                        </a>
                                    HTML,
                                    get_video_link($update['video_code'], $update['video_source']),
                                    getDisplayImageLink(['{ID}' => $id_item, '{FILE_NAME}' => $update['video_image']], 'items.main'),
                                    cleanOutput($item_info['title'])
                                );
                            } else{
                                $value = $item_info['video'];
                            }
                        break;
                        case 'description' :
                            $value = $this->clean->sanitizeUserInput($_POST['value']);
                            $this->items->validation_rule_add[] = array(
                                'field' => 'value',
                                'label' => 'Description',
                                'rules' => array('required' => '', 'html_max_len[20000]' => '')
                            );

                            $this->validator->set_rules($this->items->validation_rule_add);

                            if (!$this->validator->validate())
                                jsonResponse($this->validator->get_array_errors());

                            $update[$change_name] = $value;
                        break;
                        default:
                            jsonResponse('Error: Information cannot be changed.');
                        break;
                    }

                    $update['id'] = $id_item;
                    $update['changed'] = 1;

                    if ($this->items->update_item($update))
                        jsonResponse('', 'success', array('val' => $value));
                    else
                        jsonResponse('Error: Information cannot be changed. Please try again later.');
                break;
                case 'c_attr':
                    // $id_item = intVal($_POST['item']);
                    // if (!$this->items->my_item(privileged_user_id(), $id_item))
                    // 	jsonResponse(translate("systmess_error_rights_perform_this_action"));

                    // $item_info = $this->items->get_item($id_item);
                    // $this->load->model('Catattributes_Model', 'catattr');
                    // $attrs_insert = array();
                    // if(!empty($_POST['c_attr'])){
                    // 	$attributes = $this->catattr->get_attributes($item_info['id_cat']);
                    // 	if(!empty($attributes)){
                    // 		$attributes_keys = array_keys($attributes);
                    // 		$attributes_values = $this->catattr->get_attr_values(implode(',', $attributes_keys));
                    // 		$attrs = $_POST['c_attr'];

                    // 		foreach($attrs as $id => $val){
                    // 			if(!in_array($id, $attributes_keys)){
                    // 				continue;
                    // 			}

                    // 			$attribute = $attributes[$id];
                    // 			if (in_array($attribute['attr_type'], array('text', 'range'))) {
                    // 				$val = trim($val);
                    // 				$val = cleanInput($val);

                    // 				if (empty($val))
                    // 					continue;

                    // 				if(strlen($val) > 50){
                    // 					$errors[] = array(
                    // 						'Error: The value for "'.$attribute['attr_name'].'" cannot contain more than 50 characters.'
                    // 					);
                    // 				}
                    // 				$attrs_insert[] = array(
                    // 					'item' => $id_item,
                    // 					'attr' => $id,
                    // 					'attr_value' => $val
                    // 				);

                    // 				$search_info_attr[] = $search_info[] = $attribute['attr_name'] . " : " . $val;
                    // 			} elseif (in_array($attribute['attr_type'], array('select', 'multiselect'))) {
                    // 				if(!empty($val)){
                    // 					if(is_array($val)){
                    // 						foreach ($val as $one){
                    // 							if (!isset($attributes_values[$one]))
                    // 								continue;

                    // 							$attrs_insert[] = array(
                    // 								'item' => $id_item,
                    // 								'attr' => $id,
                    // 								'attr_value' => $one
                    // 							);
                    // 						}
                    // 					} else{
                    // 						if (!isset($attributes_values[$val]))
                    // 							continue;

                    // 						$attrs_insert[] = array(
                    // 							'item' => $id_item,
                    // 							'attr' => $id,
                    // 							'attr_value' => $val
                    // 						);
                    // 					}
                    // 				}
                    // 			}
                    // 		}

                    // 		if(!empty($errors)){
                    // 			jsonResponse($errors);
                    // 		}
                    // 	}
                    // }

                    // //UPDATE ITEM ATTRIBUTES BY CATEGORY
                    // $this->items->delete_cat_attr_by_item($id_item);
                    // if (!empty($attrs_insert)){
                    // 	$this->items->insert_item_attr_batch($attrs_insert);
                    // }

                    jsonResponse('This information has been successfully changed.', 'success');
                break;
                case 'u_attr':
                    $id_item = intVal($_POST['item']);
                    if (!$this->items->my_item(privileged_user_id(), $id_item))
                        jsonResponse(translate("systmess_error_rights_perform_this_action"));

                    $u_attrs_insert = array();
                    if (isset($_POST['u_attr']) && !empty($_POST['u_attr']['name'])) {

                        $attr_names = $_POST['u_attr']['name'];
                        $attr_vals = $_POST['u_attr']['value'];

                        foreach ($attr_names as $key => $name) {
                            $user_attr_name = cleanInput($name);
                            $user_attr_value = cleanInput(trim($attr_vals[$key]));

                            if (empty($user_attr_name) || empty($user_attr_value)){
                                jsonResponse('Error: Fields Name and Value cannot be empty.');
                            }

                            if(strlen($user_attr_name) > 50 || strlen($user_attr_value) > 50){
                                jsonResponse('Error: Fields Name and Value cannot contain more than 50 characters.');
                            }

                            $u_attrs_insert[] = array(
                                'id_item' => $id_item,
                                'p_name' => $user_attr_name,
                                'p_value' => $user_attr_value
                            );
                        }
                    }

                    $this->items->delete_user_attrs_by_item($id_item);
                    if (!empty($u_attrs_insert)){
                        $this->items->insert_item_user_attr_batch($u_attrs_insert);
                    }

                    $update = array(
                        'id' => $id_item,
                        'changed' => 1,
                    );

                    if ($this->items->update_item($update))
                        jsonResponse('', 'success');
                    else
                        jsonResponse('Error: Information cannot be changed. Please try again later.');
                break;
                case 'search_info':
                    $id_item = intVal($_POST['id']);
                    $id_seller = privileged_user_id();

                    if (!$this->items->my_item($id_seller, $id_item))
                        jsonResponse(translate("systmess_error_rights_perform_this_action"));

                    $update = array(
                        'id' => $id_item,
                        'search_info' => $this->items->get_search_info($id_item),
                        'changed' => 0,
                    );

                    if ($this->items->update_item($update) && $this->_create_item_snapshot($id_item)){
                        jsonResponse('This item is now active for buyers.', 'success');
                    } else{
                        jsonResponse('Error: You cannot save any changes now. Please try again later.');
                    }
                break;
            }
        }
    }

    function vin_decode() {
        if (!isAjaxRequest()){
            headerRedirect();
        }

        checkPermisionAjax('manage_personal_items,manage_content');

        if (empty($_POST["code"])){
            jsonResponse('Error: VIN was not sent.');
        }

        $this->load->library('Vindecoder', 'vindecoder');
        $vin_code = cleanInput($_POST["code"]);
        if ($this->vindecoder->is_used($vin_code)){
            jsonResponse('Error: This VIN is already used by other vehicle.');
        }

        $data['vin_info'] = $this->vindecoder->decode($vin_code);
        if(empty($data['vin_info'])){
            jsonResponse('Error: Incorrect VIN.');
        }

        $this->view->assign($data);

        $content = $this->view->fetch('new/item/vin_table');

        jsonResponse('', 'success', array('html' => $content));
    }

    /**
     * User droplist page
     */
    public function droplist()
    {
        checkPermision('droplist_access', '/403');

        views(
            [
                'new/header_view',
                'new/item/droplist/index_view',
                'new/footer_view',
            ],
            [
                'categories' => $this->droplistItemsDataProvider->getItemsCategoriesTree(),
            ]
        );
    }

    /**
     * Show modal droplist item
     */
    public function ajax_add_to_droplist()
    {
        checkIsAjax();
        checkPermisionAjaxModal('droplist_access');

        $itemId = $this->uri->segment(3);
        if (!empty($itemId)) {
            /** @var Products_Model $productsModel */
            $productsModel = model(\Products_Model::class);

            $item = $productsModel->findOneBy([
                'columns'   => [
                    'id',
                    'title',
                    'final_price',
                ],
                'scopes'    => [
                    'id'        => (int) $itemId,
                ],
                'with'      => [
                    'mainPhoto'
                ]
            ]);

            if (empty($item)) {
                messageInModal(translate('system_message_item_not_found'));
            }

            /** @var FilesystemProviderInterface */
            $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
            $publicDisk = $storageProvider->storage('public.storage');

            $item['main_photo']['url'] = $publicDisk->url(
                ItemPathGenerator::itemMainPhotoPath(
                    $item['id'], $item['main_photo']['photo_name']
                )
            );

            views()->display('new/item/add_to_droplist_view', [
                'item'  => $item,
                'notificationTypes' => [
                    'Website'   => [
                        'value' => NotificationType::getFormValue(NotificationType::WEBSITE())
                    ],
                    'Email'     => [
                        'value' => NotificationType::getFormValue(NotificationType::EMAIL())
                    ]
                ]
            ]);
        }
    }

    /**
     * Remove from droplist
     */
    public function ajax_remove_from_droplist()
    {
        checkIsAjax();
        checkPermisionAjax('droplist_access');

        $itemId = $this->uri->segment(3);
        if (!empty($itemId)) {
            /** @var Items_Droplist_Model $droplistModel */
            $droplistModel = model(\Items_Droplist_Model::class);
            $droplistItem = $droplistModel->findOneBy(
                [
                    'scopes'    => [
                        'itemId'   => (int) $itemId,
                        'userId'   => (int) session()->id,
                    ]
                ]
            );

            if (empty($droplistItem)) {
                jsonResponse(translate('system_message_item_not_found'));
            }

            $droplistModel->deleteOne($droplistItem['id']);

            $canAddToDroplist = true;

            /** @var Products_Model $productModel */
            $productModel = model(\Products_Model::class);
            $product = $productModel->findOne($droplistItem['item_id']);
            if (empty($product) || (int) $product['draft'] || (int) $product['is_out_of_stock'] || (int) $product['blocked']) {
                $canAddToDroplist = false;
            }

            /** @var FilesystemProviderInterface */
            $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
            $publicDisk = $storageProvider->storage('public.storage');

            try {
                $publicDisk->deleteDirectory(
                    ItemDroplistFilePathGenerator::droplistDirectoryPath($droplistItem['id'])
                );
            } catch (UnableToDeleteDirectory $error) {
                jsonResponse(translate('systmess_internal_server_error'));
            }

            jsonResponse(translate('system_message_item_removed_from_droplist'), 'success', [
                'mess_title'        => 'Success!',
                'canAddToDroplist'  => $canAddToDroplist
            ]);
        }
    }

    /**
     * Show modal droplist EDIT item
     */
    public function ajax_edit_droplist_item()
    {
        checkIsAjax();
        checkPermisionAjaxModal('droplist_access');

        $droplistId = $this->uri->segment(3);
        if (!empty($droplistId)) {
            /** @var Items_Droplist_Model $droplistModel */
            $droplistModel = model(\Items_Droplist_Model::class);
            $droplistItem = $droplistModel->findOneBy(
                [
                    'scopes' => [
                        'id'        => (int) $droplistId,
                        'user_id'   => (int) session()->id,
                    ]
                ]
            );

            if (empty($droplistItem)) {
                messageInModal(translate('system_message_item_not_found'));
            }

            if (ItemStatus::ACTIVE() !== $droplistItem['item_status']) {
                messageInModal(translate('static_text_droplist_item_unaviable_description'));
            }

            /** @var Products_Model $productsModel */
            $productsModel = model(\Products_Model::class);

            $item = $productsModel->findOneBy([
                'columns'   => [
                    'id',
                    'title',
                    'final_price',
                ],
                'scopes'    => [
                    'id'    => (int) $droplistItem['item_id'],
                ],
                'with'      => [
                    'mainPhoto'
                ]
            ]);

            if (empty($item)) {
                messageInModal(translate('system_message_item_not_found'));
            }

        views()->display('new/item/edit_to_droplist_view', [
                'droplistItem'  => $droplistItem,
                'selected'      => $droplistItem['notification_type'],
                'notificationTypes' => [
                    'Website'   => [
                        'label' => NotificationType::WEBSITE()->value,
                        'value' => NotificationType::getFormValue(NotificationType::WEBSITE())
                    ],
                    'Email'     => [
                        'label' => NotificationType::EMAIL()->value,
                        'value' => NotificationType::getFormValue(NotificationType::EMAIL())
                    ],
                    'Both'      => [
                        'label' => NotificationType::BOTH()->value,
                        'value' => NotificationType::getFormValue(NotificationType::BOTH())
                    ]
                ]
            ]);
        }
    }

    /**
     * Edit item in droplist
     */
    public function edit_droplist_item()
    {
        checkIsAjax();
        checkPermisionAjax('droplist_access');

        $request = request()->request;

        $notificationTypes = $request->get('notification-types');
        if (empty($notificationTypes)) {
            jsonResponse(translate('system_message_notification_type_not_selected'));
        }

        $notificationType = NotificationType::fromFormValue(
            array_sum(array_map(fn ($v) => (int) $v, (array) $notificationTypes))
        );

        $droplistId = $request->get('droplist-id');
        if (!empty($droplistId)) {
            /** @var Items_Droplist_Model $droplistModel */
            $droplistModel = model(\Items_Droplist_Model::class);
            $droplistItem = $droplistModel->findOneBy(
                [
                    'scopes'    => [
                        'id'        => (int) $droplistId,
                        'userId'   => (int) session()->id,
                    ]
                ]
            );

            if (empty($droplistItem)) {
                jsonResponse(translate('system_message_item_not_found'));
            }

            if (ItemStatus::ACTIVE() !== $droplistItem['item_status']) {
                jsonResponse(translate('system_message_item_not_found'), 'warning', [
                    'modal_subtitle'    => translate('static_text_droplist_item_unaviable'),
                    'modal_text'        => translate('static_text_droplist_item_unaviable_description'),
                ]);
            }

            $update = $droplistModel->updateOne($droplistItem['id'], [
                'notification_type' => $notificationType
            ]);

            if ($update) {
                jsonResponse(translate('system_message_droplist_item_updated'), 'success');
            } else {
                jsonResponse(translate('systmess_internal_server_error'));
            }
        }
    }

    /**
     * Store item in Droplist
     */
    public function add_to_droplist()
    {
        checkIsAjax();
        checkPermisionAjax('droplist_access');

        $request = request()->request;

        $itemId = $request->get('item-id');
        if (empty($itemId)) {
            jsonResponse(translate('system_message_item_not_found'));
        }

        $notificationTypes = $request->get('notification-types');
        if (empty($notificationTypes)) {
            jsonResponse(translate('system_message_notification_type_not_selected'));
        }

        $notificationType = NotificationType::fromFormValue(
            array_sum(array_map(fn ($v) => (int) $v, (array) $notificationTypes))
        );

        /** @var Items_Droplist_Model $dropListModel */
        $dropListModel = model(\Items_Droplist_Model::class);

        $dropListItem = $dropListModel->countAllBy([
            'scopes'    => [
                'item_id'   => (int) $itemId,
                'user_id'   => (int) session()->id,
            ]
        ]);

        if ($dropListItem > 0) {
            jsonResponse(translate('system_message_droplist_item_exists'));
        }

        /** @var Products_Model $productsModel */
        $productsModel = model(\Products_Model::class);

        $item = $productsModel->findOneBy([
            'columns'   => [
                'id',
                'title',
                'final_price',
                'id_seller',
                'is_out_of_stock',
                'draft',
                'blocked',
                'moderation_is_approved'
            ],
            'scopes'    => [
                'id'    => (int) $itemId,
            ],
            'with'      => [
                'sellerCompany',
                'mainPhoto'
            ],
        ]);

        if (empty($item)) {
            jsonResponse(translate('system_message_item_not_found'));
        }

        if ((int) $item['draft'] || (int) $item['is_out_of_stock'] || (int) $item['blocked'] || !$item['moderation_is_approved']) {
            jsonResponse(translate('droplist_item_not_active'));
        }

        $droplistItemId = $dropListModel->insertOne(
            [
                'item_id'           => $item['id'],
                'user_id'           => (int) session()->id,
                'company_id'        => $item['seller_company']['id_company'],
                'item_title'        => $item['title'],
                'item_image'        => $item['main_photo']['photo_name'],
                'item_status'       => ItemStatus::ACTIVE(),
                'item_price'        => \get_price($item['final_price'], false),
                'droplist_price'    => \get_price($item['final_price'], false),
                'price_changed_at'  => DateTimeImmutable::createFromFormat('Y-m-d', date('Y-m-d')),
                'notification_type' => $notificationType,
            ]
        );

        $this->eventBus->dispatch(new CopyFileToStorage(
            ItemPathGenerator::itemMainPhotoPath($item['id'], $item['main_photo']['photo_name']),
            ItemDroplistFilePathGenerator::droplistImagePath($droplistItemId, $item['main_photo']['photo_name']),
            'public.storage'
        ));

        jsonResponse(translate('system_message_item_added_in_droplist'), 'success');
    }

    /**
     * Datatable with droplist
     */
    public function ajax_droplist_datatable()
    {
        checkIsAjax();
        checkPermisionAjaxDT('droplist_access');

        $request = request()->request;

        /** @var FilesystemProviderInterface */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $publicDisk = $storageProvider->storage('public.storage');

        $items = array_map(function ($item) use ($publicDisk) {
            $itemImageUrl = $publicDisk->url(
                ItemDroplistFilePathGenerator::droplistImagePath($item['id'], $item['item_image'] ?: 'no-image.png')
            );

            $companyImageUrl = $publicDisk->url(
                CompanyLogoFilePathGenerator::logoPath($item['seller_company']['id_company'], $item['seller_company']['logo_company'])
            );

            $itemPriceChangesClass = $item['item_price']->greaterThan($item['droplist_price']) ? 'dt-new__price--increase' : 'dt-new__price--decrease';
            $itemPriceIconChangesClass = $item['item_price']->greaterThan($item['droplist_price']) ? 'arrow-increase' : 'arrow-reduction';
            if (!$item['item_price']->compare($item['droplist_price'])) {
                $itemPriceChangesClass = '';
                $itemPriceIconChangesClass = null;
            }

            $discount = "";
            if (0 < $item['product']['discount']) {
                $discount = <<<DISCOUNT
                    <div class="dt-new__item-discount">Discount: {$item['product']['discount']}%</div>
                DISCOUNT;
            }

            $editBtn = [
                'text'  => translate('items_droplist_edit'),
                'title' => translate('edit_items_droplist_popup_subttl'),
                'url'   => __SITE_URL ."/items/ajax_edit_droplist_item/{$item['id']}"
            ];

            $atasEdit = addQaUniqueIdentifier('page__droplist__edit-btn');
            $actionBtnEdit = "";
            if (ItemStatus::ACTIVE() === $item['item_status']) {
                $actionBtnEdit = <<<EDIT_ACTION
                    <a class="dropdown-item js-fancybox-validate-modal fancybox.ajax" href="{$editBtn['url']}" title="{$editBtn['title']}" target="_blank" data-mw="470" data-class-modificator="droplist" $atasEdit>
                    <i class="ep-icon ep-icon_pencil"></i>
                    <span>{$editBtn['text']}</span>
                    </a>
                EDIT_ACTION;
            }

            $removeBtn = [
                'title'     => translate('items_droplist_remove_ttl'),
                'message'   => translate('items_droplist_remove_subttl') ,
                'text'      => translate('items_droplist_remove')
            ];
            $atasRemove = addQaUniqueIdentifier('page__droplist__remove-btn');
            $actionBtnRemove = <<<REMOVE_ACTION
                <button class="dropdown-item call-function" data-callback="removeDroplistItem" data-item-id="{$item['item_id']}" data-title="{$removeBtn['title']}" data-message="{$removeBtn['message']}" $atasRemove>
                    <i class="ep-icon ep-icon_trash-stroke"></i>
                    <span>{$removeBtn['text']}</span>
                </button>
            REMOVE_ACTION;


            $infoBtn = [
                'text'      => translate('items_droplist_all_info')
            ];
            $actionBtnInfo = <<<INFO_ACTION
                <a class="dropdown-item d-none d-md-block d-lg-block d-xl-none call-function" data-callback="dataTableAllInfo" href="#" target="_blank">
                    <i class="ep-icon ep-icon_info-stroke"></i>
                    <span>{$infoBtn['text']}</span>
                </a>
            INFO_ACTION;

            $itemUrl = makeItemUrl($item['item_id'], $item['item_title'], true);
            $active = ItemStatus::ACTIVE() === $item['item_status'] ? 'true' : 'false';

            $atasDropDown = addQaUniqueIdentifier('page__droplist__dropdown-btn');
            $atasItemImg = addQaUniqueIdentifier('page__droplist__item-img');
            $atasItemTitle = addQaUniqueIdentifier('page__droplist__item-title');
            $atasSellerName = addQaUniqueIdentifier('page__droplist__seller-name');
            $atasFlagImg = addQaUniqueIdentifier('page__droplist__flag-img');
            $atasCountryName = addQaUniqueIdentifier('page__droplist__country-name');
            $atasPrice = addQaUniqueIdentifier('page__droplist__price');
            $atasDate = addQaUniqueIdentifier('page__droplist__date');

            $dropdownClass = "";
            if ('true' !== $active) {
                $dropdownClass = "dropdown-toggle-not-available";
            }

            return [
                'dt_item'   => "
                    <div class=\"dt-new__item-td\" data-active=\"{$active}\">
                        <div class=\"dt-new__item-img-wrap\">
                            <img src=\"{$itemImageUrl}\" alt=\"\" class=\"image js-fs-image\" data-fsw=\"100\" data-fsh=\"80\" width=\"100px\" height=\"80px\" $atasItemImg>
                        </div>
                        <div class=\"dt-new__item-content\">
                            <a href=\"{$itemUrl}\" class=\"link-black\" target='\"_blank\"'>
                                <div class=\"dt-new__item-text\" $atasItemTitle>
                                    {$item['item_title']}
                                </div>
                            </a>
                            {$discount}
                        </div>
                    </div>"
                ,
                'dt_seller' => "
                    <div class=\"dt-new__company\">
                        <div class=\"dt-new__company-img\">
                            <img src=\"{$companyImageUrl}\" alt=\"\" class=\"image js-fs-image\" data-fsw=\"50\" data-fsh=\"50\" width=\"50px\" height=\"50px\" $atasItemImg/>
                        </div>
                        <div class=\"dt-new__company-content\">
                            <div class=\"dt-new__company-ttl\" $atasSellerName>
                                {$item['seller_company']['name_company']}
                            </div>
                            <div class=\"dt-new__company-country\">
                                <div class=\"dt-new__company-flag-wrap\">
                                    <img src=\"" . getCountryFlag($item['seller_company']['country']) . "\" alt=\"\" class=\"image js-fs-image\" data-fsw=\"24\" data-fsh=\"24\" width=\"24px\" height=\"24px\" $atasFlagImg>
                                </div>
                                <div class=\"dt-new__company-country-ttl\" $atasCountryName>" . $item['seller_company']['country'] . "</div>
                            </div>
                        </div>
                    </div>
                ",
                'dt_droplist_price'     => "<div class=\"dt-new__price\" $atasPrice>" . get_price($item['droplist_price']) . "</div>",
                'dt_current_price'      => "<div class=\"dt-new__price {$itemPriceChangesClass}\" $atasPrice>" . get_price($item['item_price']) . widgetGetSvgIcon($itemPriceIconChangesClass, 11, 11) . "</div>",
                'dt_added_date'         => "<div class=\"dt-new__date\" $atasDate>" . getDateFormatIfNotEmpty($item['created_at']) . "</div>",
                "dt_price_change_date"  => "<div class=\"dt-new__date\" $atasDate>" . getDateFormatIfNotEmpty($item['price_changed_at']) . "</div>",
                'dt_actions'            => "
                    <div class=\"dropdown\">
                        <a class=\"dropdown-toggle {$dropdownClass}\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\" $atasDropDown>
                            <i class=\"ep-icon ep-icon_menu-circles\"></i>
                        </a>
                        <div class=\"dropdown-menu dropdown-menu-right\">
                            $actionBtnEdit
                            $actionBtnRemove
                            $actionBtnInfo
                        </div>
                    </div>
                "
            ];
        }, $this->droplistItemsDataProvider->getDatatableListItems($request, session()->id, true, true));

        $output = [
            'iTotalRecords'         => $this->droplistItemsDataProvider->getDatatableListItemsCount($request, session()->id, true),
            'iTotalDisplayRecords'  => $this->droplistItemsDataProvider->getDatatableListItemsCount($request, session()->id, true),
            'aaData'                => $items,
            'sEcho'                 => $request->getInt('sEcho')
        ];

        jsonResponse('', 'success', $output);
    }

    private function _create_item_snapshot($id_item)
    {
        $this->load->model('Items_Model', 'items');
        $this->load->model('Catattributes_Model', 'catattr');
        $this->load->model('Item_Snapshot_Model', 'snapshot');

        /** @var Products_Model $productsModel */
        $productsModel = model(Products_Model::class);

        $item = $productsModel->findOne($id_item, [
            'with'  => [
                'productUnitType',
                'productCurrency',
                'productCountry',
                'productState',
                'productCity',
            ],
        ]);

        $itemLocationComponents = array_filter([
            $item['product_country']['country'],
            $item['product_state']['state'],
            $item['product_city']['city'],
        ]);

        $main_photo = $this->items->get_item_main_photo($id_item);

        // DELETE UNSUSED SNAPSHOTS
        $unused_snapshots = $this->snapshot->get_unused_item_snapshot($id_item);
        if (!empty($unused_snapshots)) {
            $this->snapshot->delete_unused_item_snapshots($id_item, $unused_snapshots);
        }

        $this->snapshot->update_item_snapshots($id_item, 'item', ['is_last_snapshot' => 0]);

        // COLECT ATTRIBUTES
        $aditional_info = array();
        $cat_attr = $this->catattr->get_item_attr_full_values($id_item);
        if (!empty($cat_attr)) {
            foreach ($cat_attr as $attribute) {
                if (in_array($attribute['attr_type'], array('select', 'multiselect'))) {
                    $aditional_info['attr_info'][] = array(
                        'name' => $attribute['attr_name'],
                        'value' => $attribute['attr_values']
                    );
                }else {
                    $aditional_info['attr_info'][] = array(
                        'name' => $attribute['attr_name'],
                        'value' => $attribute['attr_value']
                    );
                }
            }
        }

        $user_attrs = $this->items->get_user_attrs($id_item);
        if(!empty($user_attrs)){
            foreach($user_attrs as $user_attr){
                $aditional_info['attr_info'] = array(
                    'name' => $user_attr['p_name'],
                    'value' => $user_attr['p_value']
                );
            }
        }

        // GET VIN INFO
        $vin_info = $this->items->get_vin_info($id_item);
        if(!empty($vin_info)){
            $aditional_info['vin_info'] = $vin_info;
        }

        if ($item['has_variants']) {
            /** @var Items_Variants_Model $itemsVariantsModel */
            $itemsVariantsModel = model(Items_Variants_Model::class);

            /** @var Items_Variants_Properties_Model $itemsVariantsPropertiesModel */
            $itemsVariantsPropertiesModel = model(Items_Variants_Properties_Model::class);

            //escape from type Money and over object types
            $itemVariants = $itemsVariantsModel->runWithoutAllCasts(
                fn () => $itemsVariantsModel->findAllBy([
                    'conditions'    => [
                        'itemId'    => (int) $id_item,
                    ],
                    'with'  => [
                        'propertyOptions',
                    ],
                ])
            );

            //escape from array collection type
            foreach ($itemVariants as &$itemVariant) {
                $itemVariant['property_options'] = $itemVariant['property_options']->toArray();
            }

            $itemProperties = $itemsVariantsPropertiesModel->findAllBy([
                'conditions'    => [
                    'itemId'    => (int) $id_item,
                ],
                'with'          => [
                    'propertyOptions'
                ],
            ]);

            foreach ($itemProperties as &$itemProperty) {
                $itemProperty['property_options'] = $itemProperty['property_options']->toArray();
            }

            $aditional_info['item_variants'] = [
                'variants'      => array_column($itemVariants, null, 'id'),
                'properties'    => $itemProperties,
            ];
        }

        $snapshotId = $this->snapshot->insert_item_snapshot([
            'snapshot_reviews_count'    => $item['rev_numb'],
            'hs_tariff_number'          => $item['hs_tariff_number'],
            'snapshot_rating'           => $item['rating'],
            'aditional_info'            => json_encode($aditional_info),
            'item_variants'             => empty($aditional_info['item_variants']) ? '[]' : json_encode($aditional_info['item_variants']),
            'description'               => $item['description'],
            'item_length'               => $item['item_length'],
            'item_height'               => $item['item_height'],
            'item_weight'               => $item['weight'],
            'country_abr'               => $item['origin_country_abr'],
            'main_image'                => $main_photo['photo_name'] ?: '',
            'item_width'                => $item['item_width'],
            'unit_name'                 => $item['product_unit_type']['unit_name'],
            'id_seller'                 => $item['id_seller'],
            'discount'                  => $item['discount'],
            'currency'                  => $item['product_currency']['curr_entity'] ?? '',
            'id_item'                   => $id_item,
            'country'                   => implode(', ', $itemLocationComponents),
            'title'                     => $item['title'],
            'price'                     => moneyToDecimal($item['final_price']),
        ]);

        if (!empty($main_photo['photo_name'])) {
            $module = 'items.main';
            $main_image_link = getImgSrc($module, 'original', array('{ID}' => $id_item, '{FILE_NAME}' => $main_photo['photo_name']));

            /** @var FilesystemProviderInterface */
            $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);

            $publicDisk = $storageProvider->storage('public.storage');
            $publicLegacyPrefixer = $storageProvider->prefixer('public.storage');
            $rootStorage = $storageProvider->storage('root.storage');
            $projectDir = $this->getContainer()->getParameter('kernel.project_dir');
            $strippedPrefixerPath = $publicLegacyPrefixer->stripPrefix($projectDir . '/' . $main_image_link);

            if (
                $rootStorage->fileExists($main_image_link)
                && $rootStorage->fileExists($main_image_link)
            ) {
                try {
                    $publicDisk->write(
                        ItemPathGenerator::snapshotDraftUpload($snapshotId, $main_photo['photo_name']),
                        $publicDisk->read($strippedPrefixerPath)
                    );
                    $publicDisk->write(
                        ItemPathGenerator::snapshotDraftUpload($snapshotId, '/thumb_1_' . $main_photo['photo_name']),
                        $publicDisk->read($strippedPrefixerPath)
                    );

                } catch (\Throwable $th) {
                    try {
                        $publicDisk->deleteDirectory(ItemPathGenerator::snapshotDirectory($snapshotId));
                    } catch (\Throwable $th) {

                    }
                    return false;
                }
            }
        }

        return TRUE;
    }

    /**
     * Shows the popup form for the item drafts bulk upload.
     */
    private function show_bulk_upload_form()
    {
        views()->display('new/items_draft/my/upload_form_view', array(
            'upload_folder' => encriptedFolderName(),
            'upload_config' => model(Items_draft_Model::class)->get_user_upload_config((int) privileged_user_id()),
            'fileupload'    => $this->getFileuploadOptions(
                explode(',', config('fileupload_item_drafts_format', 'xls,xlsx')),
                1,
                1,
                (int) config('fileupload_max_file_size', 10 * 1024 * 1024),
                config('fileupload_max_file_size_placeholder', '10MB'),
                array(),
                getUrlForGroup('/items/ajax_item_operation/upload_draft_list')
            ),
            'urls'          => array(
                'draft'        => array('href' => getUrlForGroup('/items/ajax_item_operation/save_draft')),
                'upload_file'  => array('href' => getUrlForGroup('/items/ajax_item_operation/upload_draft_list')),
                'save_configs' => array('href' => getUrlForGroup('/items/ajax_item_operation/save_draft_config')),
                'show_configs' => array('href' => getUrlForGroup('/items/ajax_item_operation/show_drafts_configurations')),
            ),
        ));
    }

    private function download_drafts_example(): void
    {
        $filepath = config('env.ITEM_BULK_UPLOAD_EXAMPLE_FILE') ?? null;
        if (null === $filepath || !file_exists($filepath)) {
            jsonResponse(translate('seller_products_dashboard_download_example_not_found_error'));
        }

        try {
            if (false === $content = file_get_contents($filepath)) {
                throw new RuntimeException("File is empty");
            }

            list('basename' => $name, 'extension' => $extension) = pathinfo($filepath);
            $encoded = base64_encode($content);
            $mime_type = (new MimeTypes())->guessMimeType($filepath)
                ?? Mime::getMimeFromExtension($extension)
                ?? "application/octet-stream";
        } catch (\Throwable $exception) {
            jsonResponse(
                translate('seller_products_dashboard_download_example_download_error'),
                'error',
                withDebugInformation(array(), array('exception' => throwableToArray($exception)))
            );
        }

        jsonResponse(null, 'success', array(
            'name' => $name,
            'mime' => $mime_type,
            'file' => "data:{$mime_type};base64,{$encoded}",
        ));
    }

    /**
     * Uploads the item drafts list.
     *
     * @param int    $user_id
     * @param array  $files
     * @param string $directory_checksum
     */
    private function upload_item_drafts_list($user_id, array $files = array(), $directory_checksum = null): void
    {
        is_allowed('freq_bulk_upload_uploading');

        /** @var UploadedFile $uploadedFile */
        $uploadedFile = array_shift(request()->files->get('files'));

        //region Files check
        if (
            empty($files)
            || !checkEncriptedFolder($directory_checksum)
        ) {
            jsonResponse(translate('systmess_error_file_upload_path_not_correct'));
        }
        //endregion Files check
        try {
            $adapter = new LegacyValidatorAdapter(library(TinyMVC_Library_validator::class));
            $validator = new AggregateValidator([
                new UploadedFileSizeValidator($adapter, (int) config('fileupload_max_file_size', 10 * 1024 * 1024)),
                new UploadedFileMimeTypeValidator($adapter, array_reduce(
                    explode(',', config('fileupload_item_drafts_format', 'xls,xlsx')),
                    fn (array $carry, string $extension) => array_merge($carry, (new MimeTypes())->getMimeTypes($extension)),
                    []
                )),
            ]);

            if (!$validator->validate(['file' => $uploadedFile])) {
                throw new ValidationException('Validation failed', 0, null, $validator->getViolations());
            }
        } catch (ValidationException $e){
            jsonResponse(
                \array_merge(
                    \array_map(
                        fn (ConstraintViolation $violation) => $violation->getMessage(),
                        \iterator_to_array($e->getValidationErrors()->getIterator())
                    ),
                ),
                'error',
                !DEBUG_MODE ? [] : ['errors' => [throwableToArray($e)]]
            );

        }

        /** @var FilesystemProviderInterface */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $tempDisk = $storageProvider->storage('temp.storage');
        $fileName = sprintf('%s.%s', \bin2hex(\random_bytes(16)), $uploadedFile->getClientOriginalExtension());
        try {
            $tempDisk->write(FilePathGenerator::makePathToUploadedFile($fileName), $uploadedFile->getContent());
        } catch (UnableToWriteFile $e) {
            jsonResponse(translate('systmess_error_failed_upload_draft_items'));
        }

        jsonResponse(translate('systmess_success_file_upload'), 'success', [
            'files' => [
                [
                    'type' => \pathinfo($fileName, \PATHINFO_EXTENSION),
                    'name' => $fileName,
                    'path' => $fileName,
                ],
            ]
        ]);
    }

    /**
     * Shows the content for bulk upload configurations.
     *
     * @param int    $user_id
     * @param array  $file
     * @param string $name
     * @param string $path
     * @param bool   $use_config
     * @param bool   $use_first_raw
     */
    private function show_bulk_upload_configurations($user_id, array $file, $name, $path, $use_config = false, $use_first_raw = false): void
    {
        //region File
        if (empty($file) || empty($name)) {
            jsonResponse(translate('systmess_error_uploaded_file_not_found'));
        }
        //endregion File

        //region Validation
        /** @var TinyMVC_Library_excel $parser */
        $parser = library(TinyMVC_Library_excel::class);

        try {
            $parser->set_file($path);
        } catch (RuntimeException $e) {
            jsonResponse(translate('systmess_error_uploaded_file_not_found'));
        }

        $data = array(
            'file'  => $path,
            'title' => $name,
            'records' => new FlatValidationData(array('entries' => $parser->extract_content(), 'has_titles' => $use_first_raw)),
        );
        $adapter = new LegacyValidatorAdapter(library(TinyMVC_Library_validator::class));
        $validator = new DraftFileValidator($adapter);
        if (!$validator->validate($data)) {
            jsonResponse(
                \array_map(
                    function (ConstraintViolation $violation) { return $violation->getMessage(); },
                    \iterator_to_array($validator->getViolations()->getIterator())
                )
            );
        }
        //endregion Validation

        //region Draft metadata
        $records = $parser->extract_content();
        if (
            !($metadata_id = model(Items_draft_Model::class)->insert_upload_data(array(
                'id_user'            => $user_id,
                'upload_file_name'   => $name,
                'upload_first_row'   => $use_first_raw ? 'column_name' : 'data',
                'upload_xls_columns' => json_encode(($xls_columns = $use_first_raw ? array_values($records[1]) : array_keys($records[1]))),
            )))
        ) {
            jsonResponse(translate('systmess_internal_server_error'));
        }
        //endregion Draft metadata

        //region Upload config
        $upload_config = array();
        if (
            $use_config
            && !empty($upload_config = model(Items_draft_Model::class)->get_user_upload_config($user_id))
        ) {
            $upload_config['config_data'] = json_decode($upload_config['config_data'], true);
        }

        $xls_columns_config = arrayGet($upload_config, 'config_data.xls_columns', array());
        $saved_columns_config = arrayGet($upload_config, 'config_data.ep_columns', array());
        //endregion Upload config

        //region View vars
        $columns = array_map(
            function ($name) { return array('value' => md5($name), 'text' => $name); },
            array_filter(
                $xls_columns,
                function ($value) {
                    return null !== $value && (is_string($value) && 0 !== mb_strlen(trim($value)));
                }
            )
        );

        $payload = array(
            'id_upload'          => (int) $metadata_id,
            'countries'          => model('country')->fetch_port_country(),
            'xls_columns'        => array_values($columns),
            'selected_category'  => array(),
            'xls_columns_config' => !empty($xls_columns_config) ? $xls_columns_config : array(),
            'ep_columns_config'  => !empty($saved_columns_config) ? $saved_columns_config : array(),
            'city_selected'      => null,
            'categories'         => array(
                'levels'   => array(arrayByKey(model('category')->getCategories(array('parent' => 0)), 'category_id')),
                'selected' => array(),
            ),
            'states'             => null,
            'state'              => null,
        );

        //region Location
        if (!empty($country_id = (int) arrayGet($saved_columns_config, 'product_country'))) {
            $payload['states'] = model('country')->get_states($country_id);
        }
        if (!empty($state_id = (int) arrayGet($saved_columns_config, 'product_country'))) {
            $payload['state'] = (int) $state_id;
        }
        if (!empty($city_id = (int) arrayGet($saved_columns_config, 'product_city'))) {
            $payload['city_selected'] = model('country')->get_city($city_id);
        }
        //endregion Location

        //region Category
        if (!empty($selected_categories = arrayGet($saved_columns_config, 'categories', array()))) {
            $catedories_ids = $payload['categories']['selected'] = array_column($selected_categories, 'id');
            $child_categories = arrayByKey(model('category')->getCategories(array('parent' => $catedories_ids)), 'parent', true);
            foreach ($selected_categories as $entry) {
                if (isset($child_categories[$entry['parent']])) {
                    $payload['categories']['levels'][] = $child_categories[$entry['parent']];
                }
            }
        }
        //endregion Category
        //endregion View vars

        jsonResponse(null, 'success', array(
            'file'               => $file,
            'state'              => $state_id,
            'config_form'        => views()->fetch('new/items_draft/my/bulk_config_form_view', $payload),
            'upload_config'      => $upload_config,
            'xls_columns_config' => $xls_columns_config,
        ));
    }

    /**
     * Saves the bulk upload configurations.
     *
     * @param int   $user_id
     * @param array $raw_columns
     * @param array $raw_metadata
     * @param bool  $use_first_raw
     */
    private function save_bulk_upload_configuration($user_id, array $raw_columns = array(), array $raw_metadata = array(), $use_first_raw = false): void
    {
        if (!empty($raw_metadata['categories']) && empty(array_filter((array) $raw_metadata['categories']))) {
            unset($raw_metadata['categories']);
        }

        //region Clean columns and metadata
        $columns = array_filter(array_intersect_key($raw_columns, $this->getXlsColumnsMetadata()));
        $metadata = array_filter(array_intersect_key($raw_metadata, $this->getStorageColumnsMetadata()));
        if (empty($columns) && empty($metadata)) {
            jsonResponse('There is nothing to save.');
        }
        //endregion Clean columns and metadata

        //region Validation
        $adapter = new LegacyValidatorAdapter(library(TinyMVC_Library_validator::class));
        $validator = new DraftConfigurationsValidator($adapter);
        if (!$validator->validate($metadata)) {
            jsonResponse(
                \array_map(
                    function (ConstraintViolation $violation) { return $violation->getMessage(); },
                    \iterator_to_array($validator->getViolations()->getIterator())
                )
            );
        }

        //region Categories check
        if (!empty($categories_ids = array_filter(array_map('intval', arrayGet($metadata, 'categories', array()))))) {
            $categories = array_replace(
                array_fill_keys($categories_ids, null),
                arrayByKey(model('category')->getCategories(array('cat_list' => $categories_ids)), 'category_id')
            );
            if (empty($categories) || count($categories) !== count($categories)) {
                jsonResponse('At least one of the Product(s) category does not appear to be valid. Please choose the correct categories from the list.');
            }

            $metadata['categories'] = array_values(array_map(
                function ($category) { return array('id' => $category['category_id'], 'parent' => $category['parent']); },
                $categories
            ));
        }
        //endregion Categories check
        //endregion Validation

        //region Collect configurations
        $configs = array(
            'config_name'      => sprintf('Bulk upload %s', date('m/d/Y H:i:s')),
            'config_data'      => json_encode(array('xls_columns' => $columns, 'ep_columns' => $metadata)),
            'config_date'      => date('Y-m-d H:i:s'),
            'config_first_row' => $use_first_raw ? 'column_name' : 'data',
        );
        //endregion Collect configurations

        //region Save configurations
        if (!empty($saved_configs = model(Items_draft_Model::class)->get_user_upload_config($user_id))) {
            if (!model(Items_draft_Model::class)->update_upload_config($saved_configs['id_config'], $configs)) {
                jsonResponse('Failed to save the configuration. Please try again later');
            }
        } else {
            if (!model(Items_draft_Model::class)->insert_upload_config(array_merge(
                array('id_user' => $user_id),
                $configs
            ))) {
                jsonResponse('Failed to save the configuration. Please try again later');
            }
        }
        //endregion Save configurations

        jsonResponse('The configuration has been saved.', 'success');
    }

    /**
     * Creates the item draft.
     *
     * @param int   $user_id
     * @param int   $upload_id
     * @param array $columns
     */
    private function create_item_draft($user_id, $upload_id, array $xls_columns = array(), array $ep_columns = array()): void
    {
        //region Upload check
        if (
            empty($upload_id)
            || empty($upload_information = model(Items_draft_Model::class)->get_upload_data($upload_id))
        ) {
            jsonResponse(translate('systmess_error_bulk_upload_file_not_exist'));
        }
        //endregion Upload check

        //region User check
        if (!is_privileged('user', $upload_information['id_user'])) {
            jsonResponse(translate('systmess_error_permission_not_granted'));
        }
        //endregion User check

        //region Columns metadata
        $known_xls_columns = array_filter($this->getXlsColumnsMetadata());
        $known_ep_columns = array_filter($this->getStorageColumnsMetadata());
        $xls_columns = array_intersect_key(array_filter($xls_columns), $known_xls_columns);
        $ep_columns = with(
            array_intersect_key(array_filter($ep_columns), $known_ep_columns),
            function($columns) {
                if (isset($columns['categories']) && !empty($columns['categories'])) {
                    $categories = array_filter($columns['categories']);
                    $columns['category'] = end($categories);
                    unset($columns['categories']);
                }

                return $columns;
            }
        );
                //endregion Columns metadata
        /** @var FilesystemProviderInterface */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $publicPrefixer = $storageProvider->prefixer('temp.storage');

        //region Validation
        $has_named_columns = 'column_name' === $upload_information['upload_first_row'];
        $file_path = $publicPrefixer->prefixPath(FilePathGenerator::makePathToUploadedFile($upload_information['upload_file_name']));
        /** @var TinyMVC_Library_excel $parser */
        $parser = library(TinyMVC_Library_excel::class);
        $parser->set_file($file_path);
        $data = array_merge(
            array(
                'file'    => $file_path,
                'title'   => arrayGet($xls_columns, 'title'),
                'records' => new FlatValidationData(array('entries' => $parser->extract_content(), 'has_titles' => $has_named_columns)),
            ),
            $ep_columns
        );

        $adapter = new LegacyValidatorAdapter(library(TinyMVC_Library_validator::class));
        $validator = new AggregateValidator(array(new DraftFileValidator($adapter), new DraftConfigurationsValidator($adapter)));
        if (!$validator->validate($data)) {
            jsonResponse(
                \array_map(
                    function (ConstraintViolation $violation) { return $violation->getMessage(); },
                    \iterator_to_array($validator->getViolations()->getIterator())
                )
            );
        }
        //endregion Validation

        //region Vars
        $records = $parser->extract_content();
        $exclude_row = $has_named_columns ? 1 : 0;
        $config_columns['xls_columns'] = array_flip($xls_columns);
        $config_columns['ep_columns'] = $ep_columns;

        $daysExpire = config('draft_items_days_expire', 10);
        $dateExpire = new DateTime();
        $dateExpire->modify("+$daysExpire day");

        $default_values = [
            'id_cat'                     => 0,
            'title'                      => null,
            'price'                      => null,
            'draft'                      => 1,
            'size'                       => '0x0x0',
            'state'                      => 0,
            'video'                      => '',
            'weight'                     => null,
            'p_city'                     => 0,
            'visible'                    => 0,
            'discount'                   => 0,
            'quantity'                   => 0,
            'p_country'                  => 0,
            'min_sale_q'                 => 0,
            'max_sale_q'                 => 0,
            'video_code'                 => '',
            'item_width'                 => null,
            'item_length'                => null,
            'item_height'                => null,
            'final_price'                => null,
            'video_source'               => '',
            'origin_country'             => 0,
            'draft_expire_date'          => $dateExpire->format('Y-m-d'),
        ];

        $seller_info = model('user')->getSimpleUser($user_id);
        if ($seller_info['status'] != 'active'){
            $default_values['blocked'] = 2;
        }

        $ep_insert_data = array_merge(
            array('id_seller' => $user_id, 'create_date' => date('Y-m-d H:i:s')),
            with(
                $ep_columns,
                function ($columns) use ($known_ep_columns) {
                    $payload = array();
                    foreach ($columns as $column_name => $value) {
                        if (!arrayHas($known_ep_columns, "{$column_name}.db_column")) {
                            continue;
                        }

                        $field_name = $known_ep_columns[$column_name]['db_column'];
                        $payload[$field_name] = $value;
                    }

                    return $payload;
                }
            )
        );
        $file_columns = with(
            $records[1],
            function ($headline) use ($has_named_columns, $config_columns) {
                $columns = array();
                foreach ($headline as $column_key => $column_name) {
                    $md5_key_column = $has_named_columns ? md5($column_name) : md5($column_key);
                    if (array_key_exists($md5_key_column, $config_columns['xls_columns'])) {
                        $columns[$column_key] = $config_columns['xls_columns'][$md5_key_column];
                    }
                }

                return $columns;
            }
        );
        //endregion Vars

        //region Prepare inserts
        $bulk_insert = array();
        $invalidated_rows = array();
        $entry_validator = new NonStrictValidator(new DraftEntryValidator($adapter), array('*' => array('required')), array('title'), true);
        $draftItemsCategories = null;

        foreach ($records as $key_record => $record_columns) {
            if ($key_record === $exclude_row) {
                continue;
            }

            $insert_data = array();
            $column_data = array();
            foreach ($record_columns as $column_name => $column_value) {
                if (!isset($file_columns[$column_name])) {
                    continue;
                }

                $column_key = $file_columns[$column_name];
                $storage_column = $known_xls_columns[$column_key]['db_column'];
                $column_data[$column_key] = $column_value;
                $insert_data[$storage_column] = null !== $column_value ? cleanInput($column_value) : $column_value;
            }

            if (!$entry_validator->validate($column_data)) {
                $invalidated_rows[$key_record] = \array_map(
                    function (ConstraintViolation $violation) { return $violation->getMessage(); },
                    \iterator_to_array($entry_validator->getViolations()->getIterator())
                );
            }

            if (!empty($insert_data['video'])) {
                $video_metadata = library(TinyMVC_Library_VideoThumb::class)->getVID($insert_data['video']) ?? array();
                $insert_data['video_code'] = $video_metadata['v_id'] ?? null;
                $insert_data['video_source'] = $video_metadata['type'] ?? null;
            }

            if (!empty($insert_data['size'])) {
                list($length, $width, $height) = explode('x', $insert_data['size']);
                $insert_data['item_length'] = $length ?? 0;
                $insert_data['item_width'] = $width ?? 0;
                $insert_data['item_height'] = $height ?? 0;
            }

            $draft = array_merge($default_values, $insert_data, $ep_insert_data);
            $draft['id_cat'] = $draft['id_cat'] ?: null;

            // At the moment, the selected category refers to all items, therefore, the processing of categories is performed only once per foreach
            if (null !== $draft['id_cat'] && null === $draftItemsCategories) {
                /**
                 * @var Category_Model $categoryModel
                 */
                $categoryModel = model(Category_Model::class);
                // The category is assumed to be unambiguously valid at this point, thanks to the validation in DraftConfigurationsValidator
                $category = $categoryModel->get_category((int) $draft['id_cat']);

                $jsonBreadcrumb = json_decode("[{$category['breadcrumbs']}]", true);
                $draftItemsCategories = implode(",", array_map(function($ar) { $ark = array_keys($ar); return $ark[0]; }, $jsonBreadcrumb));
            }

            $draft['item_categories'] = $draftItemsCategories ?? '';

            $bulk_insert[] = $draft;
        }
        //endregion Prepare inserts

        //region Errors output
        if (!empty($invalidated_rows)) {
            jsonResponse(null, 'error', array(
                'has_record_errors' => true,
                'upload_results'    => views()->fetch('new/items_draft/my/upload_errors_view', array(
                    'unvalidated_rows' => $invalidated_rows,
                )),
            ));
        }
        //endregion Errors output

        //region Add drafts
        if (empty($bulk_insert)) {
            jsonResponse(translate('systmess_error_bulk_upload_empty_valid_rows'));
        }

        if (!($total_inserts = model(Items_Model::class)->insert_many_items($bulk_insert, $user_id))) {
            jsonResponse(translate('systmess_error_failed_import_draft_items'));
        }

        model(Crm_Model::class)->create_or_update_record($user_id);

        if ($total_inserts !== count($bulk_insert)) {
            jsonResponse(translate('systmess_error_partially_failed_import_draft_items'));
        }
        //endregion Add drafts

        //region Update information
        model(Items_draft_Model::class)->update_upload_data($upload_id, array(
            'upload_count'      => $total_inserts,
            'upload_errors_log' => json_encode($invalidated_rows),
        ));
        //endregion Update information

        //region systemess about draft
        $dataSystmess = [
            'mess_code' => 'add_item_draft_bulk_type_save',
            'id_users'  => [$user_id],
            'replace'   => [
                '[DAYS]'      => config('draft_items_days_expire', 10),
                '[REQUEST_LINK]' => __SITE_URL . 'items/my?request=0',
            ],
            'systmess' => true,
        ];
        //endregion systemess about draft

        /** @var Notify_Model $notifyModel */
        $notifyModel = model(Notify_Model::class);

        $notifyModel->send_notify($dataSystmess);

        jsonResponse(translate('systmess_success_bulk_upload'), 'success');
    }

    /**
     * Validates item.
     */
    private function validate_item(Request $request, array $category, bool $is_edit = false, bool $is_draft = false, bool $has_additional_description = false, ?int $itemId = null): void
    {
        $validator = $this->get_items_validator(
            $request,
            !$is_draft,
            $is_edit,
            2 === (int) $category['p_or_m'] && $category['vin'],
            cleanInput($request->request->get('address_type', 'seller')),
            $has_additional_description,
            $itemId
        );

        if (!$validator->validate(tap(new FlatValidationData($request->request->all()), function (FlatValidationData $data) {
            if ($data->has('images')) {
                $data->set('images', new ArrayCollection($data->get('images') ?? array()));
            }

            if ($data->has('properties')) {
                $data->set('properties', new ArrayCollection($data->get('properties') ?? []));
            }

            if ($data->has('combinations')) {
                $data->set('combinations', new ArrayCollection($data->get('combinations') ?? []));
            }
        }))) {
            jsonResponse(\array_merge(
                \array_map(
                    function (ConstraintViolation $violation) { return $violation->getMessage(); },
                    \iterator_to_array($validator->getViolations()->getIterator())
                ),
            ));
        }
    }

    /**
     * Returns the item validator.
     */
    private function get_items_validator(
        Request $request,
        bool $is_required,
        bool $is_edit,
        bool $has_vin,
        string $address_type,
        bool $has_additional_description,
        ?int $itemId = null
    ): ValidatorInterface {
        $itemHasVariants = !empty($request->request->get('combinations'));
        $adapter = new LegacyValidatorAdapter(library(TinyMVC_Library_validator::class));
        $validators = array(
            new ItemValidator(
                $adapter,
                date('Y') + config('item_year_plus_value'),
                $itemHasVariants,
                null,
                null,
                [
                    'price'           => 'price_in_dol',
                    'finalPrice'      => 'final_price',
                    'minSaleQuantity' => 'min_quantity',
                    'maxSaleQuantity' => 'max_quantity',
                    'outOfStock'      => 'out_of_stock_quantity',
                ]
            ),
            new ImagesSetValidator(
                $adapter,
                $this->getContainer()->get(FilesystemProviderInterface::class),
                $this->getMasterKey()->getPublicKey(),
                model(Products_Photo_Model::class),
                $is_edit,
                [
                    'mainImage.notEmpty'     => translate('validation_item_main_image_not_empty'),
                    'mainImage.notFound'     => translate('validation_item_main_image_not_found'),
                    'otherImages.notEmpty'   => translate('validation_item_other_images_not_empty'),
                    'otherImages.atLeastOne' => translate('validation_item_other_images_at_least_one'),
                    'mainImage.wrongNonce'   => translate('systmess_error_items_main_image_wrong'),
                    'mainImage.emptyParent'  => translate('systmess_error_items_main_image_wrong'),
                    'mainImage.wrongParent'  => translate('systmess_error_items_main_image_wrong'),
                ],
                null,
                array('mainImage' => 'images_main', 'otherImages' => 'images')
            )
        );
        // If type of the address is custom thatn the validator for item address must be added.
        if("custom" === $address_type) {
            $validators[] = new ItemAddressValidator($adapter, null, array('state' => 'State', 'postalCode' => 'Zip/Postal Code'));
        }
        // If item has VIN code, then add validator for VIN code.
        if($has_vin) {
            $validators[] = new VinValidator($adapter, library(TinyMVC_Library_Vindecoder::class));
        }
        // If item has additional description, then add validator for additional description.
        if($has_additional_description) {
            $validators[] = new ItemTranslateDescriptionValidator($adapter);
        }
        // If item is draft, then wrap everything into non-strict validator.
        if (!$is_required) {
            $validators = array_map(
                function (ValidatorInterface $validator) {
                    return new NonStrictValidator($validator, array('*' => array('required')), array('title'), true);
                },
                $validators
            );
        }

        if ($itemHasVariants) {
            $validators[] = new ItemVariantsValidator($adapter, $itemId);
        }

        return new AggregateValidator($validators);
    }

    /**
     * Returns the address parts for request.
     */
    private function get_address_parts(Request $request): array
    {
        if ("custom" === cleanInput($request->request->get('address_type', 'seller'))) {
            return [
                'country'       => (int) $request->request->get('country') ?: 0,
                'state'         => (int) $request->request->get('state') ?: 0,
                'city'          => (int) $request->request->get('city') ?: 0,
                'postal_code'   => cleaninput($request->request->get('postal_code')) ?: ''
            ];
        }

        $company_info = model(Company_Model::class)->get_seller_base_company(privileged_user_id());
        $company_location = model(Company_Model::class)->get_company_location($company_info['id_company']);

        return [
            'country'       => (int) ($company_location['country_id'] ?? null) ?: 0,
            'state'         => (int) ($company_location['region_id'] ?? null) ?: 0,
            'city'          => (int) ($company_location['city_id'] ?? null) ?: 0,
            'postal_code'   => $company_location['zip_company'] ?? ''
        ];
    }

    /**
     * Returns filtered image addreses from request.
     */
    private function get_filtered_images(Request $request): array
    {
        /** @var FilesystemProviderInterface */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);

        $tempDisk = $storageProvider->storage('temp.storage');

        return array_filter(
            (array) $request->request->get('images', array()),
            function (string $image) use ($tempDisk) {
                return $tempDisk->fileExists(FilePathGenerator::makePathToUploadedFile(pathinfo($image, PATHINFO_BASENAME)));
            },
        );
    }

    /**
     * Returns filtered tags from request.
     */
    private function get_filtered_tags(Request $request): array
    {
        return array_filter(
            array_map(
                function (string $tag) {
                    $tag = cleanInput($tag);
                    if (!library(TinyMVC_Library_validator::class)->valid_tag($tag)) {
                        return null;
                    }

                    return $tag;
                },
                explode(';', $request->request->get('tags')),
            )
        );
    }

    private function make_item_options(array $groups = array(), array $combinations = array())
    {
        if(empty($groups)) {
            return array();
        }

        $filtered_options = array();
        foreach ($groups as $group_key => $group) {
            $filtered_options['variant_groups'][$group_key] = array(
                'group_name'  => $group['group_name'],
                'group_order' => (int) arrayGet($group, 'group_order'),
            );

            foreach ($group['variants'] as $option_key => $option) {
                $filtered_options['variant_groups'][$group_key]['variants'][$option_key] = $option;
            }
        }

        if (empty($combinations)) {
            return $filtered_options;
        }

        foreach ($combinations as $combination_key => &$combination) {
            ksort($combination['combination']);
            foreach ($combination['combination'] as $group_key => &$combination_value) {
                if (is_array($combination_value)) {
                    ksort($combination_value);
                }
            }

            $combination['price'] = moneyToDecimal(priceToUsdMoney($combination['price']));
            $filtered_options['combinations'][$combination_key] = $combination;
        }

        return $filtered_options;
    }

    /**
     * Sends notification and email for users that asked subscribed to item being available
     *
     * @param array $item - item details
     * @param array $notifyUsers - ids of users to notify
     * @param array $companyDetails - company details (if not then current user)
     */
    private function sendItemAvailable($item, $notifyUsers, $companyDetails = array())
    {
        if(empty($notifyUsers)){
            return;
        }

        /** @var Notify_Model $notifyModel */
        $notifyModel = model(Notify_Model::class);
        $companyName = empty($companyDetails) ? my_company_name() : $companyDetails['name_company'];

		$notifyModel->send_notify([
			'mess_code' => 'item_out_of_stock_available',
			'id_users'  => $notifyUsers,
			'replace'   => [
				'[SELLER_COMPANY]' => cleanOutput($companyName),
				'[ITEM_NAME]'      => cleanOutput($item['title']),
				'[ITEM_LINK]'      => __SITE_URL . 'item/' . strForURL($item['title']) . '-' . $item['id']
			],
			'systmess' => true
		]);

        /** @var User_Model $userModel */
        $userModel = model(User_Model::class);

        /** @var MailerInterface $mailer */
        $mailer = $this->getContainer()->get(MailerInterface::class);

        foreach($userModel->getSimpleUsers(implode(',', $notifyUsers)) ?? [] as $notifyUser)
        {
            $mailer->send(
                (new OutOfStockBackInStock(cleanOutput(trim($notifyUser['fname']) . " " . trim($notifyUser['lname'])), $companyName, $item))
                    ->to(new RefAddress((string) $notifyUser['idu'], new Address($notifyUser['email'])))
                    ->subjectContext([
                        '[itemName]' => $item['title'],
                    ])
            );
        }

        /** @var Items_Model $itemsModel */
        $itemsModel = model(Items_Model::class);

        $itemsModel->markOutOfStockNotified($item['id']);
    }

    /**
     * Get User saved items
     * @return mixed
     */
    private function getSavedItems ()
    {
        $savedItems = null;

        if (logged_in()) {

            /** @var User_Saved_Items_Model $userSavedItems */
            $userSavedItems = model(User_Saved_Items_Model::class);

            $savedItems = array_column(
                $userSavedItems->findAllBy([
                    'columns'       => 'id_item',
                    'conditions'    => [
                        'userId'    => (int) session()->id,
                    ],
                ]),
                'id_item'
            );
        }

        return $savedItems;
    }

    /**
     * Prepare items slider view
     *
     * @param array $items
     * @param string $key
     * @param bool $has_hover
     * @param bool $has_mobile_seller
     * @param bool $isSmCard
     * @return array|string
     */
    private function prepareItemsView (array $items, ?string $key, ?bool $hasHover = false, ?bool $hasMobileSeller = true, ?bool $isSmCard = false)
    {

        if (empty($items)) {
            return [];
        }

        return views()->fetch(
            'new/item/list_item_view',
            [
                'view_key'          => $key,
                'items'             => $items,
                'has_hover'         => $hasHover,
                'has_mobile_seller' => $hasMobileSeller,
                'isSmCard'          => $isSmCard,
                'savedItems'        => $this->getSavedItems(),
            ]
        );
    }
}
