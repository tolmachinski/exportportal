<?php

use App\Common\Buttons\ChatButton;
use App\Common\Contracts\BuyerIndustries\CollectTypes;
use App\Common\Contracts\Group\GroupType;
use App\Common\Contracts\Media\CompanyLogoThumb;
use App\Common\Contracts\Media\CompanyPhotosThumb;
use App\Common\Contracts\Media\SellerNewsPhotoThumb;
use App\Common\Contracts\Media\SellerUpdatesPhotoThumb;
use App\Common\Contracts\Media\SellerVideosPhotosThumb;
use App\Common\Traits\Items\ProductCardPricesTrait;
use App\Email\EmailFriendAboutCompany;
use App\Filesystem\CompanyLibraryFilePathGenerator;
use App\Filesystem\CompanyLogoFilePathGenerator;
use App\Filesystem\CompanyNewsFilePathGenerator;
use App\Filesystem\CompanyPhotosFilePathGenerator;
use App\Filesystem\CompanyUpdatesFilePathGenerator;
use App\Filesystem\CompanyVideoFilePathGenerator;
use App\Filesystem\CompanyVideosFilePathGenerator;
use App\Filesystem\VideoThumbsPathGenerator;
use App\Messenger\Message\Command\SaveBuyerIndustryOfInterest;
use ExportPortal\Bridge\Mailer\Mime\RefAddress;
use ExportPortal\Bridge\Symfony\Component\Messenger\MessengerInterface;
use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;
use GuzzleHttp\Psr7\Uri;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

/**
 * Controller SellerCompany
 *
 * @property \TinyMVC_Load              $load
 * @property \TinyMVC_View              $view
 * @property \TinyMVC_Library_URI       $uri
 * @property \TinyMVC_Library_Session   $session
 * @property \TinyMVC_Library_Cookies   $cookies
 * @property \TinyMVC_Library_Upload    $upload
 * @property \TinyMVC_Library_validator $validator
 * @property \Translations_Model        $translations
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [18.12.2021]
 * Controller Refactoring
 */
class Seller_Controller extends TinyMVC_Controller
{
    use ProductCardPricesTrait;


    private $breadcrumbs = array();
    private $base_company_url = '';
    private $is_acces_by_pesonalized_link = false;
    private $current_uri = array();
    private $is_page_detail = false;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->is_acces_by_pesonalized_link = tmvc::instance()->is_acces_by_pesonalized_link;
    }

    private function get_company_main_data(array $allowable_uri_segments = array())
    {
        $uri_segment_index = $this->is_acces_by_pesonalized_link ? 4 : 5;
        if ($this->is_page_detail) {
            $uri_segment_index++;
        }

        $this->current_uri = uri()->uri_to_assoc($uri_segment_index);

        checkURI($this->current_uri, $allowable_uri_segments);

        if (in_array('page', $allowable_uri_segments)) {
            checkIsValidPage($this->current_uri['page']);
        }

        $data = array();

        if ($this->is_acces_by_pesonalized_link) {
            $company_index_name = cleanInput(uri()->segment(1));

            if (empty($company_index_name)) {
                show_404();
            }

            $data['company'] = $company = model('company')->get_company([
                'index_name' => $company_index_name,
                'check_index_name_temp' => true
            ]);
        } else {
            $id_company = id_from_link(uri()->segment(3));
            if (empty($id_company)) {
                show_404();
            }

            $data['company'] = $company = model('company')->get_company([
                'id_company' => $id_company
            ]);
        }

        if (
            empty($company)
            || (
                1 === (int) $company['fake_user']
                && !(
                        is_privileged('user', (int) $company['id_user'])
                        || have_right('moderate_content')
                    )
                )
            )
        {
            show_404();
        }

        if(1 === (int) $company['fake_user']){
            header("X-Robots-Tag: noindex");
        }

        $data['base_company_url'] = $this->base_company_url = getCompanyURL($company);

        $uri_segments = $this->is_acces_by_pesonalized_link ? uri()->uri_to_assoc(4) : uri()->uri_to_assoc(5);

        $redirect_url = (string) Uri::withQueryValues(
            new Uri($this->base_company_url . (tmvc::instance()->action != 'index' ? '/' . tmvc::instance()->action : '') . (empty($uri_segments) ? '' : '/' . rtrim(uri()->assoc_to_uri($uri_segments), '/'))),
            $_GET ?? array()
        );

        if (isset($_GET['confirm'])) {
            $external_type = cleanInput($_GET['type']);
            $confirm_code = cleanInput($_GET['confirm']);

            $params_confirm = array(
                'confirm_code' => $confirm_code,
                'confirmed' => 0
            );

            switch ($external_type) {
                case 'feedback':
                    $external_feedback_info = model('external_feedbacks')->get_external_feedback($params_confirm);
                    if (empty($external_feedback_info)) {
                        break;
                    }

                    model('external_feedbacks')->update_external_feedback($confirm_code, array('confirmed' => 1));
                    model('UserFeedback')->up_company_rating_by_company($company['id_company'], $external_feedback_info['rating']);

                    $this->session->setMessages(translate('systmess_success_feedback_confirmed'), 'success');

                    headerRedirect($this->base_company_url . '/feedbacks_external');

                    break;
                case 'review':
                    $external_review_info = model('external_feedbacks')->get_external_review($params_confirm);
                    if (empty($external_review_info)) {
                        break;
                    }

                    model('external_feedbacks')->update_external_review($confirm_code, array('confirmed' => 1));
                    model('Items')->up_item_rating($external_review_info['id_item'], $external_review_info['rating']);

                    $this->session->setMessages(translate('systmess_success_review_confirmed'), 'success');

                    headerRedirect($this->base_company_url . '/reviews_external');

                    break;
            }
        }

        if ( ! $this->is_acces_by_pesonalized_link xor empty($company['index_name'])) {
            headerRedirect($redirect_url);
        }

        $isNotBlocked = 0 === (int) $company['blocked'];
        $isVisible = 1 === (int) $company['visible_company'];
        $isActiveAccount = 'active' === $company['status'];
        if (
            !(
                $isVisible && $isActiveAccount && $isNotBlocked
                || is_privileged('user', (int) $company['id_user'])
                || have_right('moderate_content')
            )
        ) {
			show_blocked();
        }

        if ( ! $this->is_acces_by_pesonalized_link && is_privileged('company', $company['id_company']) && have_right('id_generated') && empty($company['index_name']) && empty($company['index_name_temp'])) {
            session()->setMessages(translate('systmess_error_must_fill_personal_company_link', null, true), 'warning');
        }

		$this->breadcrumbs[] = array(
			'link'	=> $this->base_company_url,
			'title'	=> $company['name_company']
        );

        $data['user_main'] = $seller = model('user')->getUser($company['id_user']);
        $data['user_rights'] = model('UserGroup')->getUserRights($seller['user_group']);

        $aditional_rights = model('UserGroup')->get_aditional_rights($seller['idu']);
		if ( ! empty($aditional_rights)) {
			foreach ($aditional_rights as $aditional_right) {
				$data['user_rights'][] = $aditional_right['r_alias'];
			}
        }

        $data['seller_view'] = in_session('companies', $data['company']['id_company']);
        $data['current_page'] = uri()->segment(2) === false ? 'index' : uri()->segment(2);

        $company_verify_code = md5($company['id_company'] . 'code');

        if (isset($_GET['external']) && $_GET['external'] == $company_verify_code) {
            $data['feedback_code'] = cleanInput($_GET['external']);
            $data['external_invite_code'] = $company_verify_code;
            $data['external_invite_type'] = $_GET['type'] == 'review' ? 'review' : 'feedback';
        }

        $data['meta_params']['[COMPANY_NAME]'] = $company['name_company'];
		$data['meta_params']['[GROUP_NAME]'] = $seller['gr_name'];
        $data['meta_params']['[USER_NAME]'] = $seller['fname'] . ' ' . $seller['lname'];
        $data['meta_params']['[TYPE_NAME]'] = $company['name_type'];

        $company_logo = getImgSrc('companies.main', 'original', array('{ID}' => $company['id_company'], '{FILE_NAME}' => $company['logo_company']));
        if (image_exist($company_logo)) {
			$data['meta_params']['[image]'] = $company_logo;
        }

        $items_conditions = array(
			'per_p'			=> (int) config('item_default_perpage', 12),
			'page' 			=> 1,
			'seller'		=> $company['id_user'],
			'visible'		=> 1,
			'main_photo'	=> 1,
		);

        model('elasticsearch_items')->get_items($items_conditions);
		$data['sidebar_items_count'] = model('elasticsearch_items')->itemsCount;
        $company_type = model(Company_Model::class)->get_company_type($data['company']['id_type']);
        if (!empty($company_type['group_name_suffix'])) {
            $data['company']['group_name_suffix'] = $company_type['group_name_suffix'];
        }

        if (logged_in()) {
            $data['company']['btnChat'] = (new ChatButton(['recipient' => $data['company']['id_user'], 'recipientStatus' => $data['company']['status']]))->render();
            $data['btnChat'] = (new ChatButton(
                ['recipient' => $data['company']['id_user'], 'recipientStatus' => $data['company']['status']],
                [
                    'classes' => 'btn btn-primary btn-block',
                    'icon'    => '',
                    'tag'     => 'a',
                    'atas'    => 'seller_sidebar_contacts_btn'
                ]
            ))->render();
        }

        return $data;
    }

    public function index()
    {
        $data = $this->get_company_main_data();

        if ( ! empty($data['company']['description_company'])) {
			$data['meta_data']['description'] = truncWords(strip_tags($data['company']['description_company']), 30);
        }

        $data['user_social'] = model('UserGroup')->get_user_rights_fields_value($data['user_main']['idu'], array('type' => "'social'"));
        $data['user_statistic'] = model('user_statistic')->get_user_statistic_simple($data['company']['id_user'], ' followers_user, b2b_partners ');
		$data['attributes'] = model('company')->get_attributes_by_id($data['company']['id_company']);
        $data['breadcrumbs'] = $this->breadcrumbs;

        if (have_right('have_about_info', $data['user_rights'])) {
            $data['about_page'] = model('seller_about')->getPageAbout($data['company']['id_user']);
        }

        if (have_right('have_additional_about_info', $data['user_rights'])) {
            $data['about_page_additional'] = model('seller_about')->getPageAboutAditional(array(
                'id_seller' => $data['company']['id_user'],
                'id_company' => $data['company']['id_company']
            ));
        }

        // REVIEW SELLER WALL - COMMING SOON
        if (have_right('have_seller_wall', $data['user_rights'])) {
            $wall_info = $this->_show_wall($data['company']['id_user'], $data);
            $data = array_merge($data, $wall_info);
        }

        if (isBackstopEnabled()) {
            $data = $this->replaceDataForBackstop('index_' . request()->query->get('backstop'), $data);
        }
        //set the link to images
        /** @var FilesystemProviderInterface  $storageProvider */
        $provider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $storage = $provider->storage('public.storage');
        $data['company']['logoImageLink'] = $storage->url(CompanyLogoFilePathGenerator::logoPath($data['company']['id_company'], $data['company']['logo_company']));
        $data['videoImagePath'] = $storage->url(CompanyVideoFilePathGenerator::videoPath($data['company']['id_company'], $data['company']['video_company_image']));

        $data['sidebar_left_content'] = 'new/user/seller/sidebar_view';
        $data['main_content'] = 'new/user/seller/home_view';

        views()->assign($data);
        views()->display('new/index_template_view');
    }

    private function _show_wall($id_user, $data){
        $wall_info = array();
        $wall_info['wall_items'] = library('wall')->getItemsViews($id_user, $data, (int) config('seller_wall_items_limit', 10), 0);
        $wall_info['has_more_wall_items'] = library('wall')->hasItemsBeyond($id_user, (int) config('seller_wall_items_limit', 10), 0);

        return $wall_info;
    }

    public function about()
    {
        $data = $this->get_company_main_data();

        $this->breadcrumbs[] = array(
			'link'	=> $this->base_company_url . '/about',
			'title'	=> translate('breadcrumb_seller_about')
        );

        $data['breadcrumbs'] = $this->breadcrumbs;

        if (have_right('have_about_info', $data['user_rights'])) {
            $data['about_page'] = model('seller_about')->getPageAbout($data['company']['id_user']);
        }

        if (have_right('have_additional_about_info', $data['user_rights'])) {
            $data['about_page_additional'] = model('seller_about')->getPageAboutAditional(array(
                'id_seller'     => $data['company']['id_user'],
                'id_company'    => $data['company']['id_company']
            ));
        }

        if ( ! empty($data['company']['description_company'])) {
			$data['meta_data']['description'] = truncWords(strip_tags($data['company']['description_company']), 30);
		}

        //set the link to images
        /** @var FilesystemProviderInterface  $storageProvider */
        $provider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $storage = $provider->storage('public.storage');
        $data['company']['logoImageLink'] = $storage->url(CompanyLogoFilePathGenerator::logoPath($data['company']['id_company'], $data['company']['logo_company']));
        $data['videoImagePath'] = $storage->url(CompanyVideoFilePathGenerator::videoPath($data['company']['id_company'], $data['company']['video_company_image']));

        $data['sidebar_left_content'] = 'new/user/seller/sidebar_view';
        $data['main_content'] = 'new/user/seller/about/about_view';
        views()->assign($data);
        views()->display('new/index_template_view');
    }

    public function contact()
    {
        $data = $this->get_company_main_data();

        //region under construction for company staff
        // $data['staffs'] = model('company_staff')->get_staffs_of_company($data['company']['id_company']);
        // $data['online_staffs'] = model('company_staff')->get_staffs_with_rights($main['company']['id_company'], array('chat_users'));
        //endregion under construction for company staff

        $this->breadcrumbs[] = array(
			'link'	=> $this->base_company_url . '/contact',
			'title'	=> translate('breadcrumb_seller_contact')
        );

        $data['breadcrumbs'] = $this->breadcrumbs;

        if ( ! empty($data['company']['description_company'])) {
			$data['meta_data']['description'] = truncWords($data['company']['description_company'], 20);
        }

        if (have_right('manage_branches', $data['user_rights'])) {
            $data['branches'] = model('company')->get_companies_main_info(array('parent' => $data['company']['id_company'], 'visibility' => 1));

            if ( ! empty($data['branches'])) {
                foreach ($data['branches'] as $key => $branch){
                    $data['branches'][$key]['full_country_city'] = model('country')->get_country_city($branch['id_country'], $branch['id_city']);
                }
            }
        }

        if (have_right('have_services_contacts', $data['user_rights'])) {
            $data['services_contacts'] = model('company')->get_company_services_contacts(array('id_company' => $data['company']['id_company']));
        }
        //set the link to images
        /** @var FilesystemProviderInterface  $storageProvider */
        $provider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $storage = $provider->storage('public.storage');
        $data['company']['logoImageLink'] = $storage->url(CompanyLogoFilePathGenerator::logoPath($data['company']['id_company'], $data['company']['logo_company']));

        $data['sidebar_left_content'] = 'new/user/seller/sidebar_view';
        $data['main_content'] = 'new/user/seller/contact_view';
        views()->assign($data);
        views()->display('new/index_template_view');
    }

    public function products()
    {
        $data = $this->get_company_main_data(array('category', 'page'));

        $links_map = array(
			'category' => array(
				'type' => 'uri',
				'deny' => array('category', 'page'),
			),
			'page' => array(
				'type' => 'uri',
				'deny' => array('page'),
            ),
            'keywords' => array(
				'type' => 'get',
				'deny' => array('page', 'keywords'),
			)
        );

        $links_tpl = uri()->make_templates($links_map, $this->current_uri);
		$links_tpl_without = uri()->make_templates($links_map, $this->current_uri, true);
		$page_base_url = $this->base_company_url . '/products/';
        $page_base_url_without_site_url = getCompanyURL($data['company'], false) . '/products/';

        $this->breadcrumbs[] = array(
			'link'	=> $page_base_url,
			'title'	=> 'Products'
        );

        $data['items_not_cheerup'] = true;

		$items_conditions = array(
			'per_p'			=> (int) config('item_default_perpage', 12),
			'page' 			=> 1,
			'seller'		=> $data['company']['id_user'],
			'visible'		=> 1,
			'main_photo'	=> 1,
		);

		$page_link_suffix = '';
		if ( ! empty($_GET['keywords'])) {
			$data['keywords'] = cleanOutput(cleanInput(cut_str($_GET['keywords'])));
			$items_conditions['keywords'] = cleanInput(cut_str($_GET['keywords']));

			$data['meta_params']['[KEYWORDS]'] = cleanInput(cut_str($_GET['keywords']));
			$data['items_not_cheerup'] = false;
			$page_link_suffix = '?keywords=' . $data['keywords'];
		}

		if ( ! empty($this->current_uri['category'])) {
			$search_categories = $id_category = id_from_link($this->current_uri['category']);
			$data['current_category'] = model('category')->get_category($id_category);

			if (!empty($data['current_category']['cat_childrens'])) {
				$search_categories .=  ',' . $data['current_category']['cat_childrens'];
			}

			$items_conditions['categories_list'] = $search_categories;
			$items_conditions['category'] = $id_category;
			$data['counter_categories'] = model('category')->get_subcat_counter($items_conditions);
			$data['cat_crumbs'] = $cat_crumbs = model('category')->breadcrumbs($id_category, 'seller/' . strForURL($data['company']['name_company']).'-'.$data['company']['id_company'].'/products/category/', '-');

			$this->breadcrumbs = array_merge($this->breadcrumbs, $cat_crumbs);

			$data['cat_link'] = $page_base_url . 'category/' . $this->current_uri['category'];
			$data['meta_params']['[CATEGORY]'] = urlToStr((string) $this->current_uri['category']);
		} else {
			$items_conditions['parent'] = 0;
			$data['counter_categories'] = model('category')->get_cat_tree($items_conditions);
		}

        $data['page'] = $items_conditions['page'] = isset($this->current_uri['page']) ? (int) $this->current_uri['page'] : 1;
        ($data['page'] <= 1) ?: $data['meta_params']['[PAGE]'] = $data['page'];

		$data['search_form_link'] = rtrim($page_base_url . $links_tpl_without['keywords'], '/');
		$data['links_tpl_category'] = $page_base_url_without_site_url . $links_tpl['category'];
		$data['reset_all_filters_link'] = rtrim($page_base_url, '/');
		$data['links_tpl_reset_keywords'] = rtrim($page_base_url . $links_tpl_without['keywords'], '/');
		$data['links_tpl_reset_category'] = rtrim($page_base_url .  $links_tpl_without['category'], '/');

        /* items */
		model('elasticsearch_items')->get_items($items_conditions);
        $data['items'] = $this->formatProductPrice(model('elasticsearch_items')->items);
		$data['count'] = model('elasticsearch_items')->itemsCount;

		$seller_raw = model('user')->getSellersForList($data['company']['id_user'], true);
		$seller = array_shift($seller_raw);

		$data['items'] = array_map(function ($item) use ($seller) {
			$item['seller'] = $seller;
			return $item;
		}, $data['items']);

		if (logged_in()) {
			$saved_list = model('items')->get_items_saved(id_session());
			$data['saved_items'] = explode(',', $saved_list);
		}

		$paginator_config = array(
			'base_url'      => $page_base_url_without_site_url . $links_tpl['page'],
			'first_url'     => rtrim($page_base_url_without_site_url . $links_tpl_without['page'], '/'),
			'total_rows'    => $data['count'],
			'per_page'      => $items_conditions['per_p'],
			'suffix'        => $page_link_suffix,
			'replace_url'   => true,
        );

        if ( ! $this->is_acces_by_pesonalized_link) {
            $paginator_config['start_uri_segment'] = 1;
        }

        if (isBackstopEnabled()) {
            $data = $this->replaceDataForBackstop('products_' . request()->query->get('backstop'), $data);
        }

		library('pagination')->initialize($paginator_config);
		$data['pagination'] = library('pagination')->create_links();

		$data['breadcrumbs'] = $this->breadcrumbs;

        //set the link to images
        /** @var FilesystemProviderInterface  $storageProvider */
        $provider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $storage = $provider->storage('public.storage');
        $data['company']['logoImageLink'] = $storage->url(CompanyLogoFilePathGenerator::logoPath($data['company']['id_company'], $data['company']['logo_company']));

		$data['sidebar_left_content'] = 'new/user/seller/sidebar_view';
		$data['main_content'] = 'new/user/products/products_view';

        $categoriesItems = array_column($data['items'], 'id_cat', 'id_cat');

        /** @var MessengerInterface $messenger */
        $messenger = container()->get(MessengerInterface::class);

        /** @var Buyer_Item_Categories_Stats_Model $buyerStatsModel */
        $buyerStatsModel = model(Buyer_Item_Categories_Stats_Model::class);

        foreach($categoriesItems as $category){
            if((!logged_in() || is_buyer()) && !$buyerStatsModel->existsViewedToday($category, CollectTypes::SELLER_PAGE(), getEpClientIdCookieValue())){
                $messenger->bus('command.bus')->dispatch(
                    new SaveBuyerIndustryOfInterest(
                        $category,
                        id_session(),
                        getEpClientIdCookieValue(),
                        CollectTypes::SELLER_PAGE()
                    )
                );
            }
        }

		views()->assign($data);
		views()->display('new/index_template_view');
    }

    public function questions()
    {
        $data = $this->get_company_main_data(array('page'));

        $this->breadcrumbs[] = array(
			'link'	=> $this->base_company_url . '/questions',
			'title'	=> translate('breadcrumb_seller_questions')
        );

        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['questions_user_info'] = true;

        $links_map = array(
            'page' => array(
                'type' => 'uri',
                'deny' => array('page')
            )
        );

        $links_tpl = uri()->make_templates($links_map, $this->current_uri);
        $links_tpl_without = uri()->make_templates($links_map, $this->current_uri, true);
        $page_base_url_without_site_url = getCompanyURL($data['company'], false) . '/questions/';

        $data['per_p'] = $main_cond['per_p'] = (int) config('company_questions_per_page', 10);
        $main_cond['page'] = isset($this->current_uri['page']) ? (int) $this->current_uri['page'] : 1;
        $main_cond['seller'] = $data['company']['id_user'];
        ($main_cond['page'] <= 1) ?: $data['meta_params']['[PAGE]'] = $main_cond['page'];
        $data['count'] = $main_cond['count'] = model('itemquestions')->count_questions($main_cond);
        $data['questions'] = empty($data['count']) ? array() : model('itemquestions')->get_questions($main_cond);
        if ( ! empty($data['questions']) && logged_in()) {
            $questions_ids = array_column($data['questions'], 'id_q');

            $data['helpful'] = model('itemquestions')->get_helpful_by_question(implode(',', $questions_ids), id_session());
        }

        $paginator_config = array(
            'replace_url'   => true,
			'total_rows'    => empty($data['questions']) ? 0 : $main_cond['count'],
			'first_url'     => rtrim($page_base_url_without_site_url . $links_tpl_without['page'], '/'),
            'base_url'      => $page_base_url_without_site_url . $links_tpl['page'],
			'per_page'      => $main_cond['per_p'],
        );

        if ( ! $this->is_acces_by_pesonalized_link) {
            $paginator_config['start_uri_segment'] = 1;
        }

		library('pagination')->initialize($paginator_config);
		$data['pagination'] = library('pagination')->create_links();

        //set the link to images
        /** @var FilesystemProviderInterface  $storageProvider */
        $provider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $storage = $provider->storage('public.storage');
        $data['company']['logoImageLink'] = $storage->url(CompanyLogoFilePathGenerator::logoPath($data['company']['id_company'], $data['company']['logo_company']));

        $data['sidebar_left_content'] = 'new/user/seller/sidebar_view';
        $data['main_content'] = 'new/user/questions/questions_view';
        views()->assign($data);
        views()->display('new/index_template_view');
    }

    public function followers()
    {
        $data = $this->get_company_main_data(array('page'));

        $links_map = array(
            'page' => array(
                'type' => 'uri',
                'deny' => array('page')
            )
        );

        $links_tpl = uri()->make_templates($links_map, $this->current_uri);
        $links_tpl_without = uri()->make_templates($links_map, $this->current_uri, true);
        $page_base_url_without_site_url = getCompanyURL($data['company'], false) . '/followers/';

        $this->breadcrumbs[] = array(
			'link'	=> $this->base_company_url  .'/followers',
			'title'	=> translate('breadcrumb_seller_followers')
		);

        $data['breadcrumbs'] = $this->breadcrumbs;

        $data['meta_params']['[TITLE_VIDEO]'] = $data['video']['title_video'];
        $data['type'] = 'followers';

        $main_cond['per_p'] = $data['per_p'] = (int) config('company_followers_per_page', 10);
        $main_cond['page'] = $data['page'] = isset($this->current_uri['page']) ? (int) $this->current_uri['page'] : 1;
        ($data['page'] <= 1) ?: $data['meta_params']['[PAGE]'] = $data['page'];
        $main_cond['id_user'] = $data['company']['id_user'];

        $main_cond['count'] = $data['count'] = $data['followers_count'] = model('followers')->count_followers($main_cond);
        $followers = empty($data['count']) ? array() : model('followers')->get_followers($main_cond);

        $data['followers'] = [];
		if (!empty($followers) && logged_in()) {
            $data['followers'] = array_map(
                function ($userFollower) {
                    $chatBtn = new ChatButton(['recipient' => $userFollower['id_user_follower'], 'recipientStatus' => $userFollower['status']]);
                    $userFollower['btnChat'] = $chatBtn->button();
                    return $userFollower;
                },
                $followers
            );
        }elseif(!empty($followers)){
			$data['followers'] = $followers;
		}

        $paginator_config = array(
            'replace_url'   => true,
			'total_rows'    => empty($followers) ? 0 : $main_cond['count'],
			'first_url'     => rtrim($page_base_url_without_site_url . $links_tpl_without['page'], '/'),
			'base_url'      => $page_base_url_without_site_url . $links_tpl['page'],
			'per_page'      => $main_cond['per_p'],
        );

        if ( ! $this->is_acces_by_pesonalized_link) {
            $paginator_config['start_uri_segment'] = 1;
        }

		library('pagination')->initialize($paginator_config);
		$data['pagination'] = library('pagination')->create_links();

        //set the link to images
        /** @var FilesystemProviderInterface  $storageProvider */
        $provider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $storage = $provider->storage('public.storage');
        $data['company']['logoImageLink'] = $storage->url(CompanyLogoFilePathGenerator::logoPath($data['company']['id_company'], $data['company']['logo_company']));

		$data['sidebar_left_content'] = 'new/user/seller/sidebar_view';
		$data['main_content'] = 'new/user/seller/followers/followers_view';
		views()->assign($data);
		views()->display('new/index_template_view');
    }

    public function partners()
    {
        $data = $this->get_company_main_data();

        $this->breadcrumbs[] = array(
			'link'	=> $this->base_company_url.'/partners',
			'title'	=> translate('breadcrumb_seller_partners')
		);

        $data['breadcrumbs'] = $this->breadcrumbs;

        $company_partners = model('b2b')->get_company_partners($data['company']['id_company']);
        if ( ! empty($company_partners)) {
            $company_partners_ids = array_column($company_partners, 'id_partner');

            model('elasticsearch_company')->get_companies(array('list_company_id' => implode(',', $company_partners_ids), 'per_p' => 1000));
            $companies_list = model('elasticsearch_company')->records;

            if (logged_in()) {
                $data['companies_list'] = array_map(
                    function ($companyItem) {
                        $chatBtn = new ChatButton(['recipient' => $companyItem['id_user'], 'recipientStatus' => $companyItem['status']]);
                        $companyItem['btnChat'] = $chatBtn->button();
                        return $companyItem;
                    },
                    $companies_list
                );
            }else{
                $data['companies_list'] = $companies_list;
            }

        }

        $shipper_list = model('shippers')->get_seller_shipper_partners(array('id_seller' => $data['company']['id_user']));

        $data['shipper_list'] = [];
        if (!empty($shipper_list) && logged_in()) {
            $data['shipper_list'] = array_map(
                function ($shipperItem) {
                    $chatBtn = new ChatButton(['recipient' => $shipperItem['id_user'], 'recipientStatus' => $shipperItem['user_status']]);
                    $shipperItem['btnChat'] = $chatBtn->button();
                    return $shipperItem;
                },
                $shipper_list
            );
        }elseif(!empty($shipper_list)){
            $data['shipper_list'] = $shipper_list;
        }

		$data['seller_dashboard'] = 1;
		$data['companies_not_cheerup'] = true;

        //set the link to images
        /** @var FilesystemProviderInterface  $storageProvider */
        $provider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $storage = $provider->storage('public.storage');
        $data['company']['logoImageLink'] = $storage->url(CompanyLogoFilePathGenerator::logoPath($data['company']['id_company'], $data['company']['logo_company']));

        $data['sidebar_left_content'] = 'new/user/seller/sidebar_view';
		$data['main_content'] = 'new/user/seller/partners/partners_list_view';
		views()->assign($data);
		views()->display('new/index_template_view');
    }

    public function updates()
    {
        $data = $this->get_company_main_data(array('page'));
        if ( ! have_right('have_updates', $data['user_rights'])) {
			show_404();
        }

        $this->breadcrumbs[] = array(
			'link'	=> $this->base_company_url . '/updates',
			'title'	=> translate('seller_updates_h1_title')
        );

		$data['breadcrumbs'] = $this->breadcrumbs;

        $links_map = array(
            'page' => array(
                'type' => 'uri',
                'deny' => array('page')
            )
        );

		$links_tpl = uri()->make_templates($links_map, $this->current_uri);
        $links_tpl_without = uri()->make_templates($links_map, $this->current_uri, true);
        $page_base_url_without_site_url = getCompanyURL($data['company'], false) . '/updates/';

        $data['per_p'] = $main_cond['per_p'] = (int) config('company_updates_per_page', 10);
		$data['page'] = $main_cond['page'] = isset($this->current_uri['page']) ? (int) $this->current_uri['page'] : 1;
        ($data['page'] <= 1) ?: $data['meta_params']['[PAGE]'] = $data['page'];

		$main_cond['pagination'] = true;
		$main_cond['id_company'] = $data['company']['id_company'];
		$main_cond['count'] = $data['count'] = $data['count_updates'] = model('seller_updates')->count_seller_updates($main_cond);
        $data['updates'] = model('seller_updates')->get_seller_updates($main_cond);

        //set the link to images
        /** @var FilesystemProviderInterface  $storageProvider */
        $provider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $storage = $provider->storage('public.storage');
        $data['company']['logoImageLink'] = $storage->url(CompanyLogoFilePathGenerator::logoPath($data['company']['id_company'], $data['company']['logo_company']));

        if(!empty($data['updates'])){
            $data['updates'] = array_map(function (array $update) use ($storage) {
                if (!empty($update['photo_path'])) {
                    $update['imageLink'] = $storage->url(CompanyUpdatesFilePathGenerator::updatesPath($update['id_company'], (string) $update['photo_path']));
                    $update['imageThumbLink'] = $storage->url(CompanyUpdatesFilePathGenerator::thumbImage($update['id_company'], (string) $update['photo_path'], SellerUpdatesPhotoThumb::MEDIUM()));
                } else {
                    $update['imageThumbLink'] = $update['imageLink'] = asset('public/img/no_image/no-image-166x138.png', 'legacy');
                }

                return $update;
            }, $data['updates']);
        }

        $paginator_config = array(
            'replace_url'   => true,
			'total_rows'    => empty($data['updates']) ? 0 : $main_cond['count'],
			'first_url'     => rtrim($page_base_url_without_site_url . $links_tpl_without['page'], '/'),
            'base_url'      => $page_base_url_without_site_url . $links_tpl['page'],
			'per_page'      => $main_cond['per_p'],
        );

        if ( ! $this->is_acces_by_pesonalized_link) {
            $paginator_config['start_uri_segment'] = 1;
        }

		library('pagination')->initialize($paginator_config);
		$data['pagination'] = library('pagination')->create_links();

        $data['sidebar_left_content'] = 'new/user/seller/sidebar_view';
        $data['main_content'] = 'new/user/seller/updates/updates_view';
        views()->assign($data);
        views()->display('new/index_template_view');
    }

    public function pictures()
    {
        $data = $this->get_company_main_data(array('page'));
        if ( ! have_right('have_pictures', $data['user_rights'])) {
			show_404();
        }

        $this->breadcrumbs[] = array(
			'link'	=> $this->base_company_url . '/pictures',
			'title'	=> translate('seller_pictures_pictures_word')
		);

        $data['breadcrumbs'] = $this->breadcrumbs;

        $links_map = array(
            'page' => array(
                'type' => 'uri',
                'deny' => array('page')
            )
        );

        $links_tpl = uri()->make_templates($links_map, $this->current_uri);
        $links_tpl_without = uri()->make_templates($links_map, $this->current_uri, true);
        $page_base_url_without_site_url = getCompanyURL($data['company'], false) . '/pictures/';

        $data['per_p'] = $main_cond['per_p'] = (int) config('company_pictures_per_page', 9);
        $data['page'] = $main_cond['page'] = isset($this->current_uri['page']) ? (int) $this->current_uri['page'] : 1;
        ($data['page'] <= 1) ?: $data['meta_params']['[PAGE]'] = $data['page'];

        $main_cond['id_company'] = $data['company']['id_company'];
		$data['count_pictures'] = $data['count'] = $main_cond['count'] = model('seller_pictures')->count_seller_pictures($main_cond);
		$data['pictures'] = model('seller_pictures')->get_seller_pictures($main_cond);

        //set the link to images
        /** @var FilesystemProviderInterface  $storageProvider */
        $provider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $storage = $provider->storage('public.storage');
        $data['company']['logoImageLink'] = $storage->url(CompanyLogoFilePathGenerator::logoPath($data['company']['id_company'], $data['company']['logo_company']));

        if (!empty($data['pictures'])) {
            $data['pictures'] = array_map(function($picture) use ($storage) {
                $picture['imageLink'] = $storage->url(CompanyPhotosFilePathGenerator::photosPath($picture['id_company'], $picture['path_photo']));
                $picture['imageThumbLink'] = $storage->url(CompanyPhotosFilePathGenerator::thumbImage($picture['id_company'], $picture['path_photo'], CompanyPhotosThumb::BIG()));
                return $picture;
            }, $data['pictures']);
        }

        $paginator_config = array(
            'replace_url'   => true,
			'total_rows'    => empty($data['pictures']) ? 0 : $main_cond['count'],
			'first_url'     => rtrim($page_base_url_without_site_url . $links_tpl_without['page'], '/'),
            'base_url'      => $page_base_url_without_site_url . $links_tpl['page'],
			'per_page'      => $main_cond['per_p'],
        );

        if ( ! $this->is_acces_by_pesonalized_link) {
            $paginator_config['start_uri_segment'] = 1;
        }

		library('pagination')->initialize($paginator_config);
		$data['pagination'] = library('pagination')->create_links();

        $data['sidebar_left_content'] = 'new/user/seller/sidebar_view';
        $data['main_content'] = 'new/user/seller/pictures/pictures_view';
        views()->assign($data);
        views()->display('new/index_template_view');
    }

    public function picture()
    {
        $this->is_page_detail = true;
        $data = $this->get_company_main_data();
        if ( ! have_right('have_pictures', $data['user_rights'])){
			show_404();
        }

        $picture_uri_segment = $this->is_acces_by_pesonalized_link ? 3 : 4;
        $id_picture = id_from_link(uri()->segment($picture_uri_segment));

        if (empty($id_picture) || empty($data['picture'] = model('seller_pictures')->get_picture($id_picture))) {
            show_404();
        }

        $this->breadcrumbs[] = array(
			'link'	=> $this->base_company_url . '/pictures',
			'title'	=> translate('seller_pictures_pictures_word')
		);

		$this->breadcrumbs[] = array(
			'link'	=> $this->base_company_url . '/picture/' . strForURL($data['picture']['title_photo']) . '-' . $data['picture']['id_photo'],
			'title'	=> $data['picture']['title_photo']
		);

		$data['breadcrumbs'] = $this->breadcrumbs;
        $data['current_page'] = 'pictures';
        $data['meta_params']['[TITLE_PHOTO]'] = $data['picture']['title_photo'];

        $pictures_conditions = array(
            'id_company' => $data['picture']['id_company'],
            'not_photo'  => $id_picture,
			'sort_by'    => 'rand',
            'start'      => 0,
			'limit'      => (int) config('count_other_pictures_on_company_picture_details', 6),
        );

        $data['more_pictures_btn_link'] = $this->base_company_url . '/pictures';
        /** @var FilesystemProviderInterface  $storageProvider */
        $provider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $storage = $provider->storage('public.storage');

        $data['pictures'] = model('seller_pictures')->get_seller_pictures($pictures_conditions);
        if (!empty($data['pictures'])) {
            $data['pictures'] = array_map(function($picture) use ($storage) {
                $picture['imageLink'] = $storage->url(CompanyPhotosFilePathGenerator::photosPath($picture['id_company'], $picture['path_photo']));
                $picture['imageThumbLink'] = $storage->url(CompanyPhotosFilePathGenerator::thumbImage($picture['id_company'], $picture['path_photo'], CompanyPhotosThumb::BIG()));
                return $picture;
            }, $data['pictures']);
        }

        $data['comments'] = model('seller_pictures')->get_picture_comments($id_picture);
        $data['count_pictures'] = model('seller_pictures')->count_seller_pictures($pictures_conditions);

        //set the link to images
        $data['company']['logoImageLink'] = $storage->url(CompanyLogoFilePathGenerator::logoPath($data['company']['id_company'], $data['company']['logo_company']));
        $data['picture']['imageLink'] = $storage->url(CompanyPhotosFilePathGenerator::photosPath($data['picture']['id_company'], $data['picture']['path_photo']));
        $data['sidebar_left_content'] = 'new/user/seller/sidebar_view';
        $data['main_content'] = 'new/user/seller/pictures/detail_picture_view';
        views()->assign($data);
        views()->display('new/index_template_view');
    }

    public function videos()
    {
        $data = $this->get_company_main_data(array('page'));
        if ( ! have_right('have_videos', $data['user_rights'])) {
			show_404();
        }

        $this->breadcrumbs[] = array(
			'link'	=> $this->base_company_url . '/videos',
			'title'	=> translate('seller_videos_videos_word')
        );

        $data['breadcrumbs'] = $this->breadcrumbs;

        $links_map = array(
            'page' => array(
                'type' => 'uri',
                'deny' => array('page')
            )
        );

        $links_tpl = uri()->make_templates($links_map, $this->current_uri);
        $links_tpl_without = uri()->make_templates($links_map, $this->current_uri, true);
        $page_base_url_without_site_url = getCompanyURL($data['company'], false) . '/videos/';

        $data['per_p'] = $main_cond['per_p'] = (int) config('company_videos_per_page', 10);
		$data['page'] = $main_cond['page'] = isset($this->current_uri['page']) ? (int) $this->current_uri['page'] : 1;
        ($data['page'] <= 1) ?: $data['meta_params']['[PAGE]'] = $data['page'];

        $main_cond['pagination'] = true;
        $main_cond['id_company'] = $data['company']['id_company'];

        $data['count'] = $main_cond['count'] = model('seller_videos')->count_seller_videos($main_cond);
        $data['videos'] = empty($data['count']) ? array() : model('seller_videos')->get_seller_videos($main_cond);

        //set the link to images
        /** @var FilesystemProviderInterface  $storageProvider */
        $provider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $storage = $provider->storage('public.storage');
        $data['company']['logoImageLink'] = $storage->url(CompanyLogoFilePathGenerator::logoPath($data['company']['id_company'], $data['company']['logo_company']));

        if (!empty($data['videos'])) {
            $data['videos'] = array_map(function($video) use ($storage) {
                $video['imageThumbLink'] = $storage->url(CompanyVideosFilePathGenerator::thumbImage($video['id_company'], $video['image_video'], SellerVideosPhotosThumb::BIG()));
                return $video;
            }, $data['videos']);
        }
        $paginator_config = array(
            'replace_url'   => true,
			'total_rows'    => empty($data['videos']) ? 0 : $main_cond['count'],
			'first_url'     => rtrim($page_base_url_without_site_url . $links_tpl_without['page'], '/'),
            'base_url'      => $page_base_url_without_site_url . $links_tpl['page'],
			'per_page'      => $main_cond['per_p'],
        );

        if ( ! $this->is_acces_by_pesonalized_link) {
            $paginator_config['start_uri_segment'] = 1;
        }

		library('pagination')->initialize($paginator_config);
        $data['pagination'] = library('pagination')->create_links();

        $data['sidebar_left_content'] = 'new/user/seller/sidebar_view';
        $data['main_content'] = 'new/user/seller/videos/videos_view';
        views()->assign($data);
        views()->display('new/index_template_view');
    }

    public function video()
    {
        $this->is_page_detail = true;
        $data = $this->get_company_main_data();
        if (!(have_right('moderate_content') || have_right('have_videos', $data['user_rights']))) {
            show_404();
        }

        $video_uri_segment = $this->is_acces_by_pesonalized_link ? 3 : 4;
        $id_video = id_from_link(uri()->segment($video_uri_segment));

        if (empty($id_video) || empty($data['video'] = model('seller_videos')->get_video($id_video))) {
            show_404();
        }

        $this->breadcrumbs[] = array(
			'link'	=> $this->base_company_url.'/videos',
			'title'	=> translate('seller_videos_videos_word')
        );

		$this->breadcrumbs[] = array(
			'link'	=> $this->base_company_url. '/video/' . strForURL($data['video']['title_video']) . '-' . $data['video']['id_video'],
			'title'	=> $data['video']['title_video']
		);

        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['current_page'] = 'videos';
        $data['meta_params']['[TITLE_VIDEO]'] = $data['video']['title_video'];

        $count_videos = (int) config('count_other_videos_on_company_video_detail', 12);

        $videos_conditions = array(
            'not'        => $id_video,
			'id_company' => $data['video']['id_company'],
            'pagination' => true,
			'count'      => $count_videos,
			'per_p'      => $count_videos,
			'sort_by'    => 'rand',
        );
        /** @var FilesystemProviderInterface  $storageProvider */
        $provider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $storage = $provider->storage('public.storage');
        $data['company']['logoImageLink'] = $storage->url(CompanyLogoFilePathGenerator::logoPath($data['company']['id_company'], $data['company']['logo_company']));
        $data['video']['imageLink'] = $storage->url(CompanyVideosFilePathGenerator::videosPath($data['video']['id_company'], $data['video']['image_video']));
		$data['count_videos'] = model('seller_videos')->count_seller_videos($videos_conditions);
        $data['videos'] = empty($data['count_videos']) ? array() : model('seller_videos')->get_seller_videos($videos_conditions);
        if (!empty($data['videos'])) {
            $data['videos'] = array_map(function($video) use ($storage) {
                $video['imageThumbLink'] = $storage->url(CompanyVideosFilePathGenerator::thumbImage($video['id_company'], $video['image_video'], SellerVideosPhotosThumb::BIG()));
                return $video;
            }, $data['videos']);
        }
        $data['comments'] = model('seller_videos')->get_video_comments($id_video);
        $data['more_videos_btn_link'] = $this->base_company_url . '/videos';

        foreach ($data['videos'] as &$item) {
            $item['photoUrl'] = $storage->url(VideoThumbsPathGenerator::publicImageUploadPath($data['video']['id_company'], $item['image_video']));
        }

        $data['sidebar_left_content'] = 'new/user/seller/sidebar_view';
        $data['main_content'] = 'new/user/seller/videos/detail_video_view';
        views()->assign($data);
        views()->display('new/index_template_view');
    }

    public function library()
    {
        $data = $this->get_company_main_data(array('page'));
        if ( ! have_right('have_library', $data['user_rights'])) {
			show_404();
        }

        $this->breadcrumbs[] = array(
			'link'	=> $this->base_company_url.'/library',
			'title'	=> translate('seller_library_library_title')
        );

        $data['breadcrumbs'] = $this->breadcrumbs;

        $links_map = array(
            'page' => array(
                'type' => 'uri',
                'deny' => array('page')
            ),
            'keywords' => array(
                'type' => 'get',
                'deny' => array('page', 'keywords')
            )
        );

        $links_tpl = uri()->make_templates($links_map, $this->current_uri);
        $links_tpl_without = uri()->make_templates($links_map, $this->current_uri, true);
        $page_base_url_without_site_url = getCompanyURL($data['company'], false) . '/library/';
        $data['links_tpl_reset_keywords'] = $data['search_form_link'] = rtrim($this->base_company_url . '/library/' . $links_tpl_without['keywords'], '/');
        $data['reset_all_filters_link'] = $this->base_company_url . '/library';

        $data['per_p'] = $main_cond['per_p'] = (int) config('company_library_per_page', 10);
        $data['page'] = $main_cond['page'] = isset($this->current_uri['page']) ? (int) $this->current_uri['page'] : 1;
        ($data['page'] <= 1) ?: $data['meta_params']['[PAGE]'] = $data['page'];
        $company_followers = model('followers')->get_user_followers($data['company']['id_user']);
        if ( ! empty($company_followers)) {
            $followers_ids = array_column($company_followers, 'idu');
        }

        if (!(is_privileged('user', $data['company']['id_user']) || in_array(privileged_user_id(), $followers_ids ?: []))) {
            $main_cond['type'] = 'public';
        }

        if ( ! empty($_GET['keywords'])) {
            $data['keywords'] = cleanOutput(cleanInput($_GET['keywords']));
            $main_cond['keywords'] = cleanInput(cut_str($_GET['keywords']));
            $data['meta_params']['[KEYWORDS]'] = $data['keywords'];
        }

        $main_cond['id_company'] = $data['company']['id_company'];
		$data['count'] = $main_cond['count'] = model('seller_library')->count_seller_documents($main_cond);
        $data['documents'] = empty($data['count']) ? array() : model('seller_library')->get_seller_documents($main_cond);
        $paginator_config = array(
            'replace_url'   => true,
			'total_rows'    => empty($data['documents']) ? 0 : $main_cond['count'],
			'first_url'     => rtrim($page_base_url_without_site_url . $links_tpl_without['page'], '/'),
            'base_url'      => $page_base_url_without_site_url . $links_tpl['page'],
			'per_page'      => $main_cond['per_p'],
        );

        if ( ! $this->is_acces_by_pesonalized_link) {
            $paginator_config['start_uri_segment'] = 1;
        }

        if ( ! empty($data['keywords'])) {
            $paginator_config['suffix'] = '?' . http_build_query(array('keywords' => $data['keywords']));
        }

		library('pagination')->initialize($paginator_config);
        $data['pagination'] = library('pagination')->create_links();

        //set the link to images
        /** @var FilesystemProviderInterface  $storageProvider */
        $provider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $storage = $provider->storage('public.storage');
        $data['company']['logoImageLink'] = $storage->url(CompanyLogoFilePathGenerator::logoPath($data['company']['id_company'], $data['company']['logo_company']));

        $data['sidebar_left_content'] = 'new/user/seller/sidebar_view';
		$data['main_content'] = 'new/user/seller/library/library_view';
		views()->assign($data);
		views()->display('new/index_template_view');
    }

    public function document()
    {
        $this->is_page_detail = true;
        $document_uri_segment = $this->is_acces_by_pesonalized_link ? 3 : 4;
        $id_document = (int) uri()->segment($document_uri_segment);
        if ( ! isPositiveNumber($id_document)) {
            show_404();
        }

        $data = $this->get_company_main_data();
        if (
            !(
                !empty($data['document'] = model('seller_library')->get_document($id_document))
                && (have_right('have_library', $data['user_rights']) || have_right('manage_content'))
            )
        ) {
            show_404();
        }

        $this->breadcrumbs[] = array(
			'link'	=> $this->base_company_url . '/document/' . $id_document,
			'title'	=> translate('seller_library_document_word')
		);

        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['meta_params']['[TITLE_FILE]'] = $data['document']['title_file'];

        $followers_ids = [];
        $company_followers = model('followers')->get_user_followers($data['company']['id_user']);
        if (!empty($company_followers)) {
            $followers_ids = array_column($company_followers, 'idu');
        }

        $data['document_type'] = 'all';

        if (
            'private' === $data['document']['type_file']
            && !(
                in_array(privileged_user_id(), $followers_ids)
                || is_privileged('user', $data['company']['id_user'])
                || have_right('manage_content')
            )
        ) {
            show_404();
        }

        $data['document_type'] =  'private' === $data['document']['type_file'] ? 'private' : 'public';
        $library_cond['type'] = 'public';
        $library_cond['not_document'] = $id_document;
        $library_cond['sort_by'] = 'rand';
        $library_cond['id_seller'] = $data['company']['id_user'];
        $library_cond['per_p'] = $library_cond['count'] = (int) config('count_other_libraries_on_company_library_detail', 10);

        $data['count'] = model('seller_library')->count_seller_documents($library_cond);
        $data['documents'] = empty($data['count']) ? array() : model('seller_library')->get_seller_documents($library_cond);
        $data['more_documents_btn_link'] = $this->base_company_url . '/library';

        /** @var FilesystemProviderInterface $storageProvider */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $publicDisk = $storageProvider->storage('public.storage');
        $data['company']['logoImageLink'] = $publicDisk->url(CompanyLogoFilePathGenerator::logoPath($data['company']['id_company'], $data['company']['logo_company']));
        if (!empty($data['document']['path_file'])) {
            $data['document']['filePath'] = $publicDisk->url(CompanyLibraryFilePathGenerator::libraryPath($data['document']['id_company'], $data['document']['path_file']));
        }
        $data['sidebar_left_content'] = 'new/user/seller/sidebar_view';
		$data['main_content'] = 'new/user/seller/library/detail_document_view';
		views()->assign($data);
		views()->display('new/index_template_view');
    }

    public function news()
    {
        $data = $this->get_company_main_data(array('page'));
        if ( ! have_right('have_news', $data['user_rights'])) {
			show_404();
        }

        $this->breadcrumbs[] = array(
			'link'	=> $this->base_company_url . '/news',
			'title'	=> translate('seller_news_news_word')
        );

        $data['breadcrumbs'] = $this->breadcrumbs;

        $links_map = array(
            'page' => array(
                'type' => 'uri',
                'deny' => array('page')
            )
        );

        $links_tpl = uri()->make_templates($links_map, $this->current_uri);
        $links_tpl_without = uri()->make_templates($links_map, $this->current_uri, true);
        $page_base_url_without_site_url = getCompanyURL($data['company'], false) . '/news/';

        $news_conditions = array();
		$data['per_p'] = $news_conditions['per_p'] = (int) config('company_news_per_page', 10);
        $data['page'] = $news_conditions['page'] = isset($this->current_uri['page']) ? (int) $this->current_uri['page'] : 1;
        ($data['page'] <= 1) ?: $data['meta_params']['[PAGE]'] = $data['page'];

        $news_conditions['id_company'] = $data['company']['id_company'];
		$news_conditions['count'] = $data['count'] = model('seller_news')->countSellerNews($news_conditions);

        $data['news'] = empty($data['count']) ? array() : model('seller_news')->getSellerNews($news_conditions);

        $paginator_config = array(
            'replace_url'   => true,
			'total_rows'    => empty($data['news']) ? 0 : $news_conditions['count'],
			'first_url'     => rtrim($page_base_url_without_site_url . $links_tpl_without['page'], '/'),
            'base_url'      => $page_base_url_without_site_url . $links_tpl['page'],
			'per_page'      => $news_conditions['per_p'],
        );

        if ( ! $this->is_acces_by_pesonalized_link) {
            $paginator_config['start_uri_segment'] = 1;
        }

        library('pagination')->initialize($paginator_config);
        $data['pagination'] = library('pagination')->create_links();

        /** @var FilesystemProviderInterface  $storageProvider */
        $provider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $publicDisk = $provider->storage('public.storage');
        $data['company']['logoImageLink'] = $publicDisk->url(CompanyLogoFilePathGenerator::logoPath($data['company']['id_company'], $data['company']['logo_company']));

        $data['news'] = array_map(function($news) use ($publicDisk) {
            $news['imageThumbLink'] = $publicDisk->url(CompanyNewsFilePathGenerator::thumbImage($news['id_company'], $news['image_news'], SellerNewsPhotoThumb::MEDIUM()));
            return $news;
        }, $data['news']);

        $data['sidebar_left_content'] = 'new/user/seller/sidebar_view';
		$data['main_content'] = 'new/user/seller/news/news_view';
		views()->assign($data);
		views()->display('new/index_template_view');
    }

    public function view_news()
    {
        $this->is_page_detail = true;
        $news_uri_segment = $this->is_acces_by_pesonalized_link ? 3 : 4;
        $id_news = id_from_link(uri()->segment($news_uri_segment));
        if ( ! isPositiveNumber($id_news)) {
            show_404();
        }

        $data = $this->get_company_main_data();
        if ( ! have_right('have_news', $data['user_rights']) || empty($data['news'] = model('seller_news')->getNews($id_news))) {
			show_404();
        }

        $this->breadcrumbs[] = array(
			'link'	=> $this->base_company_url . '/news',
			'title'	=> translate('seller_news_news_word')
		);

		$this->breadcrumbs[] = array(
			'link'	=> $this->base_company_url . '/view_news/' . strForURL($data['news']['title_news']) . '-' . $data['news']['id_news'],
			'title'	=> truncWords($data['news']['title_news'], 10)
		);

        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['meta_params']['[TITLE_NEWS]'] = $data['news']['title_news'];

        $data['comments'] = model('seller_news')->getComments($id_news);

        /** @var FilesystemProviderInterface  $storageProvider */
        $provider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $publicDisk = $provider->storage('public.storage');
        $data['company']['logoImageLink'] = $publicDisk->url(CompanyLogoFilePathGenerator::logoPath($data['company']['id_company'], $data['company']['logo_company']));
        $data['imageLink'] = $publicDisk->url(CompanyNewsFilePathGenerator::newsPath($data['news']['id_company'], $data['news']['image_news']));
        $data['sidebar_left_content'] = 'new/user/seller/sidebar_view';
        $data['main_content'] = 'new/user/seller/news/detail_news_view';
        views()->assign($data);
        views()->display('new/index_template_view');
    }

    public function reviews()
    {
        $data = $this->get_company_main_data();

        $this->breadcrumbs[] = array(
			'link'	=> $this->base_company_url . '/reviews',
			'title'	=> translate('breadcrumb_seller_all_review')
		);

        $data['breadcrumbs'] = $this->breadcrumbs;

        $external_cond['per_p'] = $ep_cond['per_p'] = 5;
		$external_cond['page'] = $ep_cond['page'] = 1;
		$external_cond['id_company'] = $data['company']['id_company'];
		$external_cond['confirmed'] = 1;
        $ep_cond['id_seller'] = $data['company']['id_user'];

        $ep_cond['count'] = $data['count_reviews_ep'] = model('itemsreview')->counter_by_conditions(array('id_seller' => $data['company']['id_user']));
        $external_cond['count'] = $data['count_reviews_external'] = model('external_feedbacks')->exist_external_review($external_cond);

        $data['reviews_external'] = model('external_feedbacks')->get_external_reviews($external_cond);
        $data['reviews_ep'] = array_column(
            model('itemsreview')->get_user_reviews($ep_cond),
            null,
            'id_review'
        );

        if (!empty($data['reviews_ep'])) {
			$ep_reviews_ids = array_column($data['reviews_ep'], 'id_review');

            /** @var Product_Reviews_Images_Model $reviewImagesModel */
            $reviewImagesModel = model(Product_Reviews_Images_Model::class);

            $reviewsImages = $reviewImagesModel->findAllBy([
                'conditions' => [
                    'reviewsIds' => $ep_reviews_ids,
                ],
            ]);

            foreach ($reviewsImages as $reviewImage) {
                $data['reviews_ep'][$reviewImage['review_id']]['images'][] = $reviewImage['name'];
            }

            if (logged_in()) {
                $data['helpful_reviews'] = model('itemsreview')->get_helpful_by_review(implode(',', $ep_reviews_ids), id_session());
            }
		}
        /** @var FilesystemProviderInterface  $storageProvider */
        $provider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $publicDisk = $provider->storage('public.storage');
        $data['company']['logoImageLink'] = $publicDisk->url(CompanyLogoFilePathGenerator::logoPath($data['company']['id_company'], $data['company']['logo_company']));

        $data['sidebar_left_content'] = 'new/user/seller/sidebar_view';
        $data['main_content'] = 'new/user/reviews/index_view';
        views()->assign($data);
        views()->display('new/index_template_view');
    }

    public function reviews_ep()
    {
        $data = $this->get_company_main_data(array('page'));

        $this->breadcrumbs[] = array(
			'link'	=> $this->base_company_url . '/reviews_ep',
			'title'	=> translate('breadcrumb_seller_ep_reviews')
		);

        $data['breadcrumbs'] = $this->breadcrumbs;

        $links_map = array(
            'page' => array(
                'type' => 'uri',
                'deny' => array('page')
            )
        );

        $links_tpl = uri()->make_templates($links_map, $this->current_uri);
        $links_tpl_without = uri()->make_templates($links_map, $this->current_uri, true);
        $page_base_url_without_site_url = getCompanyURL($data['company'], false) . '/reviews_ep/';

        $data['user_ordered_items_for_reviews'] = array();

        if (logged_in() && have_right('buy_item')) {
            $orders = model('itemsreview')->get_orders_for_review(array('id_buyer' => privileged_user_id(), 'id_seller' => $data['company']['id_user']));

			if ( ! empty($orders)) {
				foreach ($orders as $order) {
					$data['user_ordered_items_for_reviews'][$order['id_order']]['order'] = orderNumber($order['id_order']);
					$data['user_ordered_items_for_reviews'][$order['id_order']]['items'][] = $order;
				}
			}
		}

        $reviews_conditions['per_p'] = $data['per_p'] = (int) config('company_reviews_ep_per_page', 10);
        $reviews_conditions['page'] = $data['page'] = isset($this->current_uri['page']) ? (int) $this->current_uri['page'] : 1;
        $reviews_conditions['id_seller'] = $data['company']['id_user'];
        ($data['page'] <= 1) ?: $data['meta_params']['[PAGE]'] = $data['page'];
        $reviews_conditions['count'] = $data['count'] = $data['reviews_count'] = model('itemsreview')->counter_by_conditions(array('id_seller' => $data['company']['id_user']));

        $data['reviews'] = array_column(
            empty($data['count']) ? [] : model('itemsreview')->get_user_reviews($reviews_conditions),
            null,
            'id_review'
        );

        if (!empty($data['reviews'])) {
            $reviews_ids = array_column($data['reviews'], 'id_review');

            /** @var Product_Reviews_Images_Model $reviewImagesModel */
            $reviewImagesModel = model(Product_Reviews_Images_Model::class);

            $reviewsImages = $reviewImagesModel->findAllBy([
                'conditions' => [
                    'reviewsIds' => $reviews_ids,
                ],
            ]);

            foreach ($reviewsImages as $reviewImage) {
                $data['reviews'][$reviewImage['review_id']]['images'][] = $reviewImage['name'];
            }

            if (logged_in()) {
                $data['helpful_reviews'] = model('itemsreview')->get_helpful_by_review(implode(',', $reviews_ids), privileged_user_id());
            }
        }

        $paginator_config = array(
            'replace_url'   => true,
			'total_rows'    => empty($data['reviews']) ? 0 : $reviews_conditions['count'],
			'first_url'     => rtrim($page_base_url_without_site_url . $links_tpl_without['page'], '/'),
            'base_url'      => $page_base_url_without_site_url . $links_tpl['page'],
			'per_page'      => $reviews_conditions['per_p'],
        );

        if ( ! $this->is_acces_by_pesonalized_link) {
            $paginator_config['start_uri_segment'] = 1;
        }

        library('pagination')->initialize($paginator_config);
        $data['pagination'] = library('pagination')->create_links();
        /** @var FilesystemProviderInterface  $storageProvider */
        $provider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $publicDisk = $provider->storage('public.storage');
        $data['company']['logoImageLink'] = $publicDisk->url(CompanyLogoFilePathGenerator::logoPath($data['company']['id_company'], $data['company']['logo_company']));

        $data['sidebar_left_content'] = 'new/user/seller/sidebar_view';
		$data['main_content'] = 'new/user/reviews_ep/reviews_view';
		views()->assign($data);
		views()->display('new/index_template_view');
    }

    public function reviews_external()
    {
        $data = $this->get_company_main_data(array('page'));

        $this->breadcrumbs[] = array(
			'link'	=> $this->base_company_url . '/reviews_external',
			'title'	=> translate('breadcrumb_external_reviews')
		);

        $data['breadcrumbs'] = $this->breadcrumbs;

        $links_map = array(
            'page' => array(
                'type' => 'uri',
                'deny' => array('page')
            )
        );

        $links_tpl = uri()->make_templates($links_map, $this->current_uri);
        $links_tpl_without = uri()->make_templates($links_map, $this->current_uri, true);
        $page_base_url_without_site_url = getCompanyURL($data['company'], false) . '/reviews_external/';

        $reviews_conditions['per_p'] = $data['per_p'] = (int) config('company_reviews_external_per_page', 10);
		$reviews_conditions['page'] = $data['page'] = isset($this->current_uri['page']) ? (int) $this->current_uri['page'] : 1;

		$reviews_conditions['id_company'] = $data['company']['id_company'];
		$reviews_conditions['confirmed'] = 1;
		$reviews_conditions['count'] = $data['count'] = $data['reviews_count'] = model('external_feedbacks')->exist_external_review($reviews_conditions);
        $data['reviews'] = model('external_feedbacks')->get_external_reviews($reviews_conditions);

        ($data['page'] <= 1) ?: $data['meta_params']['[PAGE]'] = $data['page'];

        $paginator_config = array(
            'replace_url'   => true,
			'total_rows'    => empty($data['reviews']) ? 0 : $reviews_conditions['count'],
			'first_url'     => rtrim($page_base_url_without_site_url . $links_tpl_without['page'], '/'),
            'base_url'      => $page_base_url_without_site_url . $links_tpl['page'],
			'per_page'      => $reviews_conditions['per_p'],
        );

        if ( ! $this->is_acces_by_pesonalized_link) {
            $paginator_config['start_uri_segment'] = 1;
        }

        library('pagination')->initialize($paginator_config);
        $data['pagination'] = library('pagination')->create_links();
        /** @var FilesystemProviderInterface  $storageProvider */
        $provider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $publicDisk = $provider->storage('public.storage');
        $data['company']['logoImageLink'] = $publicDisk->url(CompanyLogoFilePathGenerator::logoPath($data['company']['id_company'], $data['company']['logo_company']));

        $data['sidebar_left_content'] = 'new/user/seller/sidebar_view';
        $data['main_content'] = 'new/user/reviews_external/reviews_view';
        views()->assign($data);
        views()->display('new/index_template_view');
    }

    public function feedbacks()
    {
        $data = $this->get_company_main_data();

        $this->breadcrumbs[] = array(
			'link'	=> $this->base_company_url . '/feedbacks',
			'title'	=> translate('breadcrumb_seller_feedback')
		);

        $data['breadcrumbs'] = $this->breadcrumbs;

        $external_cond['per_p'] = $ep_cond['per_p'] = 5;
		$external_cond['page'] = $ep_cond['page'] = 1;
		$external_cond['id_company'] = $data['company']['id_company'];
		$external_cond['confirmed'] = 1;

		$ep_cond['id_user'] = $data['company']['id_user'];
        $ep_cond['db_keys'] = 'id_feedback';

        $ep_cond['count'] = $data['count_feedbacks_ep'] = model('userfeedback')->counter_by_conditions(array('user' => $data['company']['id_user']));
		$external_cond['count'] = $data['count_feedbacks_external'] = model('external_feedbacks')->exist_external_feedback($external_cond);

		$data['feedbacks_external'] = empty($data['count_feedbacks_external']) ? array() : model('external_feedbacks')->get_external_feedbacks($external_cond);
        $data['feedbacks_ep'] = empty($data['count_feedbacks_ep']) ? array() : model('userfeedback')->get_user_feedbacks($ep_cond);

        if ( ! empty($data['feedbacks_ep']) && logged_in()) {
			$data['helpful_feedbacks'] = model('userfeedback')->get_helpful_by_feedback(implode(',', array_keys($data['feedbacks_ep'])), privileged_user_id());
        }

        foreach ($data['feedbacks_ep'] as $key => $ep_feedback) {
			if ( ! empty($ep_feedback['services'])) {
				$data['feedbacks_ep'][$key]['services'] = unserialize($ep_feedback['services']);
			}

			if ( ! empty($ep_feedback['order_summary'])) {
				$data['feedbacks_ep'][$key]['order_summary'] = unserialize($ep_feedback['order_summary']);
			}
		}
        /** @var FilesystemProviderInterface  $storageProvider */
        $provider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $publicDisk = $provider->storage('public.storage');
        $data['company']['logoImageLink'] = $publicDisk->url(CompanyLogoFilePathGenerator::logoPath($data['company']['id_company'], $data['company']['logo_company']));

        $data['sidebar_left_content'] = 'new/user/seller/sidebar_view';
        $data['main_content'] = 'new/user/feedback/index_view';
        views()->assign($data);
        views()->display('new/index_template_view');
    }

    public function feedbacks_ep()
    {
        $data = $this->get_company_main_data(array('page'));

        $this->breadcrumbs[] = array(
			'link'	=> $this->base_company_url . '/feedbacks_ep',
			'title'	=> translate('breadcrumb_seller_ep_feedback')
        );

        $data['breadcrumbs'] = $this->breadcrumbs;

        $links_map = array(
            'page' => array(
                'type' => 'uri',
                'deny' => array('page')
            )
        );

        $links_tpl = uri()->make_templates($links_map, $this->current_uri);
        $links_tpl_without = uri()->make_templates($links_map, $this->current_uri, true);
        $page_base_url_without_site_url = getCompanyURL($data['company'], false) . '/feedbacks_ep/';

        if (logged_in() && have_right('buy_item')) {
			$params = array('id_seller' => $data['company']['id_user'], 'id_buyer' => privileged_user_id(), 'status' => 11);
			$data['user_ordered_for_feedback'] = model('userfeedback')->check_user_feedback($params);
        }

        $feedback_conditions['per_p'] = $data['per_p'] = (int) config('seller_feedback_per_page', 10);
        $feedback_conditions['page'] = $data['page'] = isset($this->current_uri['page']) ? (int) $this->current_uri['page'] : 1;
        $feedback_conditions['id_user'] = $data['company']['id_user'];
		$feedback_conditions['db_keys'] = 'id_feedback';
		$feedback_conditions['count'] = $data['count'] = $data['count_feedbacks'] = model('userfeedback')->counter_by_conditions(array('user' => $data['company']['id_user']));
        $data['feedbacks'] = empty($feedback_conditions['count']) ? array() : model('userfeedback')->get_user_feedbacks($feedback_conditions);

        ($data['page'] <= 1) ?: $data['meta_params']['[PAGE]'] = $data['page'];
        if ( ! empty($data['feedbacks'])) {
			foreach ($data['feedbacks'] as $key => $feedback) {
				if ( ! empty($feedback['services'])) {
					$data['feedbacks'][$key]['services'] = unserialize($feedback['services']);
				}

				if ( ! empty($feedback['order_summary'])) {
					$data['feedbacks'][$key]['order_summary'] = unserialize($feedback['order_summary']);
				}
            }

			if (logged_in()) {
                $feedbacks_keys = implode(',', array_keys($data['feedbacks']));
				$data['helpful_feedbacks'] = model('userfeedback')->get_helpful_by_feedback($feedbacks_keys, privileged_user_id());
			}
        }

        $paginator_config = array(
            'replace_url'   => true,
			'total_rows'    => empty($data['feedbacks']) ? 0 : $feedback_conditions['count'],
			'first_url'     => rtrim($page_base_url_without_site_url . $links_tpl_without['page'], '/'),
            'base_url'      => $page_base_url_without_site_url . $links_tpl['page'],
			'per_page'      => $feedback_conditions['per_p'],
        );

        if ( ! $this->is_acces_by_pesonalized_link) {
            $paginator_config['start_uri_segment'] = 1;
        }

        library('pagination')->initialize($paginator_config);
        $data['pagination'] = library('pagination')->create_links();
        /** @var FilesystemProviderInterface  $storageProvider */
        $provider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $publicDisk = $provider->storage('public.storage');
        $data['company']['logoImageLink'] = $publicDisk->url(CompanyLogoFilePathGenerator::logoPath($data['company']['id_company'], $data['company']['logo_company']));

        $data['sidebar_left_content'] = 'new/user/seller/sidebar_view';
        $data['main_content'] = 'new/user/feedback_ep/feedback_view';
        views()->assign($data);
        views()->display('new/index_template_view');
    }

    public function feedbacks_external()
    {
        $data = $this->get_company_main_data(array('page'));

        $this->breadcrumbs[] = array(
			'link'	=> $this->base_company_url . '/feedbacks_external',
			'title'	=> translate('breadcrumb_seller_external_feedback')
		);

        $data['breadcrumbs'] = $this->breadcrumbs;

        $links_map = array(
            'page' => array(
                'type' => 'uri',
                'deny' => array('page')
            )
        );

        $links_tpl = uri()->make_templates($links_map, $this->current_uri);
        $links_tpl_without = uri()->make_templates($links_map, $this->current_uri, true);
        $page_base_url_without_site_url = getCompanyURL($data['company'], false) . '/feedbacks_external/';

        $feedback_conditions['per_p'] = $data['per_p'] = (int) config('seller_feedback_per_page', 10);
		$feedback_conditions['page'] = $data['page'] = isset($this->current_uri['page']) ? (int) $this->current_uri['page'] : 1;
		$feedback_conditions['id_company'] = $data['company']['id_company'];
        $feedback_conditions['confirmed'] = 1;

		$data['count'] = $data['count_feedbacks'] = model('external_feedbacks')->exist_external_feedback($feedback_conditions);
		$data['feedbacks'] = empty($data['count']) ? array() : model('external_feedbacks')->get_external_feedbacks($feedback_conditions);

        $paginator_config = array(
            'replace_url'   => true,
			'total_rows'    => empty($data['feedbacks']) ? 0 : $data['count'],
			'first_url'     => rtrim($page_base_url_without_site_url . $links_tpl_without['page'], '/'),
            'base_url'      => $page_base_url_without_site_url . $links_tpl['page'],
			'per_page'      => $feedback_conditions['per_p'],
        );

        if ( ! $this->is_acces_by_pesonalized_link) {
            $paginator_config['start_uri_segment'] = 1;
        }

        library('pagination')->initialize($paginator_config);
        $data['pagination'] = library('pagination')->create_links();
        /** @var FilesystemProviderInterface  $storageProvider */
        $provider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $publicDisk = $provider->storage('public.storage');
        $data['company']['logoImageLink'] = $publicDisk->url(CompanyLogoFilePathGenerator::logoPath($data['company']['id_company'], $data['company']['logo_company']));

        $data['sidebar_left_content'] = 'new/user/seller/sidebar_view';
        $data['main_content'] = 'new/user/feedback_external/feedback_view';
        views()->assign($data);
        views()->display('new/index_template_view');
    }

    public function popup_forms()
    {
        checkIsAjax();
        checkIsLoggedAjaxModal();

        $option_segment = $this->is_acces_by_pesonalized_link ? 3 : 4;
        $id_segment = $this->is_acces_by_pesonalized_link ? 4 : 5;

		$op = uri()->segment($option_segment);
		$id = (int) uri()->segment($id_segment);

		switch($op){
            case 'share_company':
                checkPermisionAjaxModal('share_this');

				if ( ! model('company')->exist_company(array('company_or_branch' => $id))) {
					messageInModal(translate('systmess_error_invalid_data'));
                }

                $data['id_item'] = $id;
                $data['action'] = "seller/company_name/ajax_send_email/share";
                $data['message'] = translate("share_form_message_company");

				views()->assign($data);
                views()->display('new/user/share/popup_email_share_view');

			break;
            case 'email_company':
                checkPermisionAjaxModal('email_this');

				if ( ! model('company')->exist_company(array('company_or_branch' => $id))) {
					messageInModal(translate('systmess_error_invalid_data'));
                }

                $data['type'] = "email";
                $data['id_item'] = $id;
                $data['action'] = "seller/company_name/ajax_send_email/email";
                $data['message'] = translate("share_form_message_company");

				views()->assign($data);
                views()->display('new/user/share/popup_email_share_view');

			break;
		}
    }

    public function ajax_send_email(){
        checkIsAjax();
        checkIsLoggedAjax();

		is_allowed("freq_allowed_send_email_to_user");

		$op = uri()->segment(4);
		$id_user = privileged_user_id();

		switch($op){
            case 'email':
                checkPermisionAjax('email_this');

				$validator_rules = array(
					array(
						'field' => 'message',
						'label' => translate('company_email_popup_input_message_label'),
						'rules' => array('required' => '', 'max_len[1000]' => '')
					),
					array(
						'field' => 'emails',
						'label' => translate('company_email_popup_input_email_label'),
						'rules' => array('required' => '', 'no_whitespaces' => '', 'valid_emails' => '', 'max_emails_count[' . config('email_this_max_email_count') . ']' => '')
					),

				);

				$this->validator->set_rules($validator_rules);
				if ( ! $this->validator->validate()) {
					jsonResponse($this->validator->get_array_errors());
				}

				$id = (int) $_POST['id_item'];

				if (empty($id) || !model(Company_Model::class)->exist_company(['company_or_branch' => $id])) {
					jsonResponse(translate('systmess_error_invalid_data'));
				}

				$filteredEmails = filter_email($_POST['emails']);

				if (empty($filteredEmails)) {
					jsonResponse(translate('systmess_error_invalid_data'));
				}

				$company = model(Company_Model::class)->get_company(['id_company' => $id, 'type_company' => 'all']);
                $userName = user_name_session();

                try {
                    /** @var FilesystemProviderInterface  $storageProvider */
                    $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                    $publicDisk = $storageProvider->storage('public.storage');
                    $pathToFile = CompanyLogoFilePathGenerator::thumbImage($company['id_company'], $company['logo_company'], CompanyLogoThumb::MEDIUM());
                    /** @var MailerInterface $mailer */
                    $mailer = $this->getContainer()->get(MailerInterface::class);
                    $mailer->send(
                        (new EmailFriendAboutCompany($userName, cleanInput(request()->request->get('message')), $company, $publicDisk->url($pathToFile)))
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
                    'type'          => 'company',
                    'type_sharing'  => 'email this',
                    'id_item'       => $company['id_company'],
                    'id_user'       => id_session(),
                ]);
				jsonResponse(translate('systmess_success_email_has_been_sent'), 'success');
			break;
            case 'share':
                checkPermisionAjax('share_this');

				$validator_rules = array(
					array(
						'field' => 'message',
						'label' => translate('share_company_form_message_label'),
						'rules' => array('required' => '')
					)
				);

				$this->validator->set_rules($validator_rules);
				if ( ! $this->validator->validate()) {
					jsonResponse($this->validator->get_array_errors());
				}

				$id = (int)$_POST['id_item'];
				if (empty($id) || ! model('company')->exist_company(array('company_or_branch' => $id))) {
					jsonResponse(translate('systmess_error_invalid_data'));
				}

				$filteredEmails = model('followers')->getFollowersEmails($id_user);
				if (empty($filteredEmails)) {
					jsonResponse(translate('systmess_error_share_company_no_followers'));
				}

                $company = model('company')->get_company(['id_company' => $id, 'type_company' => 'all']);
                $userName = user_name_session();

                try {
                    /** @var FilesystemProviderInterface  $storageProvider */
                    $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                    $publicDisk = $storageProvider->storage('public.storage');
                    $pathToFile = CompanyLogoFilePathGenerator::thumbImage($company['id_company'], $company['logo_company'], CompanyLogoThumb::MEDIUM());
                    /** @var MailerInterface $mailer */
                    $mailer = $this->getContainer()->get(MailerInterface::class);
                    $mailer->send(
                        (new EmailFriendAboutCompany($userName, cleanInput(request()->request->get('message')), $company, $publicDisk->url($pathToFile)))
                            ->to(...array_map(fn (int $id, string $to) => new RefAddress((string) $id, new Address($to)), array_column($filteredEmails, 'idu', 'idu'), array_column($filteredEmails, 'email', 'email')))
                            ->subjectContext(['[userName]' => $userName])
                    );
                } catch (\Throwable $th) {
                    jsonResponse(translate('email_has_not_been_sent'));
                }

                /** @var Share_Statistic_Model $shareStatisticRepository */
                $shareStatisticRepository = model(Share_Statistic_Model::class);
                $shareStatisticRepository->add([
                    'type'          => 'company',
                    'type_sharing'  => 'share this',
                    'id_item'       => $company['id_company'],
                    'id_user'       => id_session(),
                ]);

                jsonResponse(translate('systmess_successfully_shared_company_information'), 'success');

			break;
		}
	}

    private function replaceDataForBackstop(string $dataKey, array $data = []): array
    {
        switch ($dataKey) {
            case 'index_1':
                /** @var UserGroup_Model $userGroupsModel */
                $userGroupsModel = model(UserGroup_Model::class);

                $lorem = "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.";
                $userGroup = 6;

                $data['user_rights'] = $userGroupsModel->getUserRights($userGroup);
                $data['wall_items'] = null;
                $data['company'] = array_merge(
                    $data['company'],
                    [
                        'description_company'   => $lorem,
                        'description_company'   => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.',
                        'user_group'            => 6,
                        'video_company'         => 'https://www.youtube.com/embed/LXb3EKWsInQ',
                    ]
                );

                $data['about_page'] = [
                    'text_about_us'                     => $lorem,
                    'text_history'                      => $lorem,
                    'text_what_we_sell'                 => $lorem,
                    'text_research_develop_abilities'   => $lorem,
                    'text_development_expansion_plans'  => $lorem,
                    'text_prod_process_management'      => $lorem,
                    'text_production_flow'              => $lorem,
                ];

                $data['about_page_additional'] = [
                    [
                        "title_block" => "Additional section 1",
                        "id_block" => 1,
                        "text_block" => $lorem,
                    ],
                    [
                        "title_block" => "Additional section 2",
                        "id_block" => 2,
                        "text_block" => $lorem,
                    ],
                ];

                break;
            case 'index_2':
                $wall = [
                    'certification' => [
                        "id" => 23361,
                        "id_seller" => 5385,
                        "id_item" => NULL,
                        "type" => "certification",
                        "operation" => NULL,
                        "date" => "2020-12-31 23:59:59",
                        "data" => "{\"groupName\":\"Certified Manufacturer\",\"groupId\":\"6\"}",
                        "search_data" => "",
                        "is_removed" => 0,
                    ],
                    'b2b' => [
                        "id" => 23139,
                        "id_seller" => 5385,
                        "id_item" => 6530,
                        "type" => "b2b_request",
                        "operation" => "add",
                        "date" => "2020-12-31 23:59:59",
                        "data" => "{\"id_request\":\"6530\",\"title\":\"Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's test.\",\"message\":\"<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. <\\/p>\",\"country_name\":\"Moldova\",\"radius\":\"777\"}",
                        "search_data" => "333 <p>3333</p> 333 Moldova",
                        "is_removed" => 0,
                    ],
                    'document' => [
                        "id" => 19844,
                        "id_seller" => 5385,
                        "id_item" => 228,
                        "type" => "document",
                        "operation" => "edit",
                        "date" => "2020-12-31 23:59:59",
                        "data" => "{\"id_file\":\"228\",\"title\":\"Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's test.\",\"description\":\"Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.\",\"file\":\"611fb7b4f0bd2.xlsx\",\"extension\":\"xlsx\"}",
                        "search_data" => "adasdasdas asdasdasdsa",
                        "is_removed" => 0,
                    ],
                    'banner' => [
                        "id" => 23,
                        "id_seller" => 5385,
                        "id_item" => 4,
                        "type" => "banner",
                        "operation" => "add",
                        "date" => "2020-12-31 23:59:59",
                        "data" => "{\"id_banner\":\"4\",\"link\":\"http:\\/\\/ep.loc\\/seller_banners\\/my\",\"image\":\"1511336375.jpg\"}",
                        "search_data" => "http://ep.loc/seller_banners/my",
                        "is_removed" => 0,
                    ],
                    'update' => [
                        "id" => 14019,
                        "id_seller" => 5385,
                        "id_item" => 244,
                        "type" => "update",
                        "operation" => "add",
                        "date" => "2020-12-31 23:59:59",
                        "data" => "{\"id_update\":\"244\",\"text\":\"<p><span>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.<\\/span><\\/p>\",\"photo\":\"605c950ff04a2.jpg\"}",
                        "search_data" => "In this extensive list, you&rsquo;ll find recent smashes like Knives Out, Frozen II, and Ford v Ferrari, along with earlier-in-the-year Audience favorites like John Wick: Chapter 3 &ndash; Parabellum and Captain Marvel.&nbsp;",
                        "is_removed" => 0,
                    ],
                    'video' => [
                        "id" => 5707,
                        "id_seller" => 5385,
                        "id_item" => 176,
                        "type" => "video",
                        "operation" => "add",
                        "date" => "2020-12-31 23:59:59",
                        "data" => "{\"id_video\":\"176\",\"id_seller\":5385,\"id_company\":\"5802\",\"title\":\"Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.\",\"description\":\"Positive squad never stressed\",\"image_video\":\"5f97cda9f2ab3.jpg\",\"url_video\":\"https:\\/\\/youtu.be\\/KJAUn2ya3tI\",\"short_url_video\":\"KJAUn2ya3tI\",\"source_video\":\"youtube\"}",
                        "search_data" => "I got up next I got up nextI got up next",
                        "is_removed" => 0,
                    ],
                    'photo' => [
                        "id" => 5710,
                        "id_seller" => 5385,
                        "id_item" => 370,
                        "type" => "photo",
                        "operation" => "add",
                        "date" => "2020-12-31 23:59:59",
                        "data" => "{\"id_photo\":\"370\",\"id_company\":\"5802\",\"id_category\":\"160\",\"path_photo\":\"5f97d073cca2c.jpg\",\"title\":\"Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.\"}",
                        "search_data" => "One more picture for media content",
                        "is_removed" => 0,
                    ],

                    'news' => [
                        "id" => 23213,
                        "id_seller" => 5385,
                        "id_item" => 241,
                        "type" => "news",
                        "operation" => "add",
                        "date" => "2020-12-31 23:59:59",
                        "data" => "{\"id_news\":\"206\",\"id_company\":\"5802\",\"title\":\"Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.\",\"image\":\"611cec546a3b8.jpg\",\"text\":\"Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.\"}",
                        "search_data" => "Lorem ipsum dolor sit amet, consectetuer adipiscin Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. In enim justo, rhoncus ut, imperdiet a, venenatis vitae, justo. Nullam dictum felis eu pede mollis pretium. Integer tincidunt. Cras dapibu",
                        "is_removed" => 0,
                    ],
                    'item' => [
                        "id" => 23341,
                        "id_seller" => 5385,
                        "id_item" => 3407,
                        "type" => "item",
                        "operation" => "add",
                        "date" => "2020-12-31 23:59:59",
                        "data" => "{\"id_item\":\"3407\",\"title\":\"Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.\",\"price\":\"99998.75\",\"discount\":\"7\",\"category_name\":\"Backstop category name\",\"final_price\":\"99999.99\",\"variants\":[],\"main_photo\":\"61c3423ac9bf5.jpg\",\"photos_count\":1,\"photos\":[\"61c3423b15f13.jpg\"]}",
                        "search_data" => "MSI Radeon R9 290 GAMING 4G 325.75 299.99",
                        "is_removed" => 0,
                    ],
                ];

                $data['has_more_wall_items'] = true;
                $data['wall_items'] = [
                    'certification' => views()->fetch(
                        'new/user/seller/wall/certification_view',
                        array_merge(
                            $data,
                            [
                                'base_company_url' => getCompanyURL($data['company']),
                                'data' => json_decode($wall['certification']['data'], true),
                                'wall_item' => $wall['certification'],
                                'is_link_user' => $wall['certification'],
                            ]
                        ),
                    ),
                    'b2b' => views()->fetch(
                        'new/user/seller/wall/b2b_view',
                        array_merge(
                            $data,
                            [
                                'base_company_url' => getCompanyURL($data['company']),
                                'data' => json_decode($wall['b2b']['data'], true),
                                'wall_item' => $wall['b2b'],
                                'is_link_user' => $wall['b2b'],
                            ]
                        ),
                    ),
                    'document' => views()->fetch(
                        'new/user/seller/wall/library_view',
                        array_merge(
                            $data,
                            [
                                'base_company_url' => getCompanyURL($data['company']),
                                'data' => json_decode($wall['document']['data'], true),
                                'wall_item' => $wall['document'],
                                'is_link_user' => $wall['document'],
                            ]
                        ),
                    ),
                    'banner' => views()->fetch(
                        'new/user/seller/wall/banner',
                        array_merge(
                            $data,
                            [
                                'base_company_url' => getCompanyURL($data['company']),
                                'data' => json_decode($wall['banner']['data'], true),
                                'wall_item' => $wall['banner'],
                                'is_link_user' => $wall['banner'],
                            ]
                        ),
                    ),
                    'update' => views()->fetch(
                        'new/user/seller/wall/update_view',
                        array_merge(
                            $data,
                            [
                                'base_company_url' => getCompanyURL($data['company']),
                                'data' => json_decode($wall['update']['data'], true),
                                'wall_item' => $wall['update'],
                                'is_link_user' => $wall['update'],
                            ]
                        ),
                    ),
                    'video' => views()->fetch(
                        'new/user/seller/wall/video_view',
                        array_merge(
                            $data,
                            [
                                'base_company_url' => getCompanyURL($data['company']),
                                'data' => json_decode($wall['video']['data'], true),
                                'wall_item' => $wall['video'],
                                'is_link_user' => $wall['video'],
                            ]
                        ),
                    ),
                    'photo' => views()->fetch(
                        'new/user/seller/wall/photos_view',
                        array_merge(
                            $data,
                            [
                                'base_company_url' => getCompanyURL($data['company']),
                                'data' => json_decode($wall['photo']['data'], true),
                                'wall_item' => $wall['photo'],
                                'is_link_user' => $wall['photo'],
                            ]
                        ),
                    ),
                    'news' => views()->fetch(
                        'new/user/seller/wall/news_view',
                        array_merge(
                            $data,
                            [
                                'base_company_url' => getCompanyURL($data['company']),
                                'data' => json_decode($wall['news']['data'], true),
                                'wall_item' => $wall['news'],
                                'is_link_user' => $wall['news'],
                            ]
                        ),
                    ),
                    'item' => views()->fetch(
                        'new/user/seller/wall/product_view',
                        array_merge(
                            $data,
                            [
                                'base_company_url' => getCompanyURL($data['company']),
                                'data' => json_decode($wall['item']['data'], true),
                                'wall_item' => $wall['item'],
                                'is_link_user' => $wall['item'],
                            ]
                        ),
                    ),
                ];

                break;
            case "products_1":
                if (request()->query->get("keywords") === "badSearchBackstop") {
                    $data['items'] = [];
                    return $data;
                }

                $clonedItem = $data['items'][0];
                $clonedItem['quantity'] = 99999;
                $clonedItem['title'] = "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s";
                $clonedItem['card_prices']['min_price'] = "9999999.00";
                $clonedItem['card_prices']['min_final_price'] = "9997999.00";
                $clonedItem['discount'] = "99";
                $clonedItem['p_country'] = "139";
                $clonedItem['country_name'] = "Moldova";
                $clonedItem['origin_country_name'] = "Andorra";
                $clonedItem['seller']['name_company'] = "Alexas L.T.D";
                $clonedItem['seller']['gr_name'] = "Certified Manufacturer";

                $data['items'] = array_fill(0, 12, $clonedItem);

                $data['items'][0]['is_out_of_stock'] = 1;
                $data['items'][0]['quantity'] = 0;

                $data['items'][1]['is_out_of_stock'] = 1;
                $data['items'][1]['quantity'] = 0;
                $data['items'][1]['highlight'] = '1';

                $data['items'][2]['card_prices']['min_price'] = "1.00";
                $data['items'][2]['card_prices']['min_final_price'] = "0.90";
                $data['items'][2]['discount'] = "1";

                $data['items'][3]['samples'] = '1';
                $data['items'][3]['quantity'] = 50;
                $data['items'][3]['min_sale_q'] = 510;

                $data['items'][4]['samples'] = '1';
                $data['items'][4]['quantity'] = 50;
                $data['items'][4]['min_sale_q'] = 510;
                $data['items'][4]['highlight'] = '1';

                $data['items'][5]['card_prices']['min_price'] = "1000.00";
                $data['items'][5]['card_prices']['min_final_price'] = "990.00";
                $data['items'][5]['discount'] = "1";
                $data['items'][5]['title'] = "Lorem Ipsum";

                $data['items'][6]['card_prices']['min_price'] = "10000.00";
                $data['items'][6]['card_prices']['min_final_price'] = "5000.00";
                $data['items'][6]['discount'] = "50";
                $data['items'][6]['highlight'] = '1';

                $data['items'][10]['featured'] = '1';

                break;
        }

        $data["backstopTest"] = true;

        return $data;
    }
}
