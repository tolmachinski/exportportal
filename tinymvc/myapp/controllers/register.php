<?php

use App\Common\Database\Relations\RelationInterface;
use App\Email\ConfirmEmail;
use App\Email\EplConfirmEmail;
use App\Email\RegisterBrandAmbassador;
use App\Email\WelcomeToExportPortal;
use App\Filesystem\CompanyLogoFilePathGenerator;
use App\Filesystem\CompanyVideoFilePathGenerator;
use App\Messenger\Message\Event\Lifecycle\UserHasRegisteredEvent;
use App\Services\PhoneCodesService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Query\QueryBuilder;
use ExportPortal\Bridge\Mailer\Mime\RefAddress;
use ExportPortal\Bridge\Symfony\Component\Messenger\MessengerInterface;
use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

use const App\Logger\Activity\OperationTypes\REGISTRATION;
use const App\Logger\Activity\OperationTypes\REGISTRATION_NO_PROFILE;
use const App\Logger\Activity\ResourceTypes\USER;

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [18.12.2021]
 * Controller Refactoring
 */
class Register_Controller extends TinyMVC_Controller
{
	/**
	 *
	 * Array with industries from post
	 */
	private $post_industries = array();

	/**
	 *
	 * Array with industries from database
	 */
	private $industries = array();

	/**
	 * The array with all the insert data for users table
	 */
	private $insert = null;

    /**
	 * The array with all the insert data for company_base table
	 */
	private $companyInsert = null;

    /**
     * Source company ID
     *
     * @var null|int $sourceCompanyId
     */
    private $sourceCompanyId = null;

	/**
	 * The ids of the user (including additional accounts) registered in the users table
	 */
	private $id_users = array();

	/**
	 * True only when user is logged in and adds new accounts
	 */
	private $adding_additional_accounts = false;

	/**
	 * Array with the gmap config (not to make a query again)
	 */
	private $gmap_geodata = array();

	/**
	 * Type of packages for all type of accounts
	 */
	private $packages_types = array(
		'seller' => array(
						'group_name' => 'Seller',
						'package' => 2,
						'id_type' => 2
					),
		'manufacturer' => array(
						'group_name' => 'Manufacturer',
						'package' => 3,
						'id_type' => 1
					),
		'distributor' => array(
						'group_name' => 'Distributor',
						'package' => 2,
						'id_type' => 7
					),
		'buyer' => array(
						'group_name' => 'Buyer',
						'package' => 1,
						'id_type' => 0
					)
    );

    private $viewData = [];

	public function index()
    {
		if (logged_in()) {
			__CURRENT_SUB_DOMAIN === getSubDomains()['shippers'] ? headerRedirect(__SHIPPER_URL) : headerRedirect(__SITE_URL);
        }

        if (__CURRENT_SUB_DOMAIN === getSubDomains()['shippers']) {
            headerRedirect(__SHIPPER_URL . 'register/ff');
            return;
        }

        $this->viewData['webpackData'] = [
            'styleCritical' => 'register',
            'pageConnect' 	=> 'register_page',
        ];

        $this->viewData['templateViews'] = [
            'headerOutContent' => 'register/header_view',
            'mainOutContent'   => 'register/register_content_view',
        ];

        views()->display_template($this->viewData);
    }

    public function ref(){
        $this->load->model('User_Model', 'users');
		$id_user = (int) $this->uri->segment(3);
		$user = $this->users->getUser($id_user);

		// IF EXIST AFFILIATE SET COOKIE FOR 10 DAYS
		if(!empty($user)){
			$this->cookies->setCookieParam('_ep_ref', referal_encode(array(
                'id' => $id_user,
                'type' => 'user'
            )), 864000);
        }

        $this->viewData['has_refferal'] = true;

        $this->index();

		// headerRedirect(get_static_url('register/index'));
    }

    private function get_referrer(){
        if(!$this->cookies->exist_cookie('_ep_ref')){
            return null;
        }

        $referrer_info = referal_decode($this->cookies->getCookieParam('_ep_ref'));
        $this->cookies->removeCookie('_ep_ref');

        switch ($referrer_info['type']) {
            case 'user':
                $id_user = (int) $referrer_info['id'];
				$user = model(User_Model::class)->getUser($id_user);

				// IF EXIST AFFILIATE SET COOKIE FOR 10 DAYS
				if(!empty($user)){
					return array(
                        'user_find_type' => 'user',
                        'user_find_info' => $id_user
					);
				}
            break;
            case 'campaign':
                $id_campaign = (int) $referrer_info['id'];
                $campaign = model(Campaigns_Model::class)->get_campaign($id_campaign);

                if(!empty($campaign) && $campaign['campaign_active'] == 1){
                    return array(
                        'user_find_type' => 'campaign',
                        'user_find_info' => $id_campaign
					);
                }
            break;
        }

        return null;
    }

	public function co(){
		$this->load->model('Campaigns_Model', 'campaigns');
		$id_campaign = (int) $this->uri->segment(3);
		$campaign = $this->campaigns->get_campaign($id_campaign);

		// IF EXIST CAMPAINGN SET COOKIE FOR 10 DAYS
		if(!empty($campaign) && $campaign['campaign_active'] == 1){
			$this->cookies->setCookieParam('_ep_ref', referal_encode(array('type' => 'campaign', 'id' => $id_campaign)), 864000);
		}

        $this->viewData['has_refferal'] = true;

        $this->index();

		// headerRedirect(get_static_url('register/index'));
	}

	private function view_registration_pages($type_specific_data)
	{
		if (logged_in()){
			headerRedirect(__SITE_URL);
		}

		$data = [
			'email'                => request()->query->get('email'),
			'port_country'         => model('country')->fetch_port_country(),
			'phone_codes'          => (new PhoneCodesService(model('country')))->getCountryCodes(),
        ];

        $type_specific_data['templateViews']['mainContent'] = 'register/register_form_page_view';
        $type_specific_data['templateViews']['mainOutContent'] = 'register/register_banner_view';
        $type_specific_data['templateViews']['customEncoreLinks'] = true;
        $industrie_all = model('category')->get_industries(['has_children' => 1]);
        // $industrie_all = array();
        // $categories_model = model('elasticsearch_category');
        // $categories_model->get_categories(['parent' => 0, 'has_children' => true, 'sort_by' => 'name_asc']);
        // $industrie_all = $categories_model->categories_records;

        // if(empty($industrie_all)){
        // }

        $data['industries'] = $industrie_all;
        $data['multipleselect_industries'] = array(
            'industries'                => $industrie_all,
            'max_selected_industries'   => (int) config('multipleselect_max_industries', 3),
        );

        $data = array_merge($data, $type_specific_data);

        views()->display_template($data);
	}

	public function buyer(){

		$data_buyer = array(
			'register_type'      	 => 'buyer',
			'register_ttl'       	 => translate('register_form_title_buyer'),
			'company_type'       	 => 'buyer',
            'simple_location'    	 => false,
            'templateViews'          => [
                'footerOutContent' => 'register/content_view',
            ],
            'content'                => [
                'undertitle'     => translate('register_benefits_header_buyer'),
                'title'          => translate('register_benefits_header_buyer_sub_text'),
                'subtitle'       => translate('register_benefits_header_we_provide'),
                'advantages'     => [
                    [
                        'icon' => 'ep-icon_globe',
                        'text' => translate('register_benefits_we_provide_buyer_global_sellers'),
                    ],
                    [
                        'icon' => 'ep-icon_truck-right',
                        'text' => translate('register_benefits_we_provide_buyer_shipping'),
                    ],
                    [
                        'icon' => 'ep-icon_group-stroke',
                        'text' => translate('register_benefits_we_provide_buyer_buying_options'),
                    ],
                ],
                'benefits_title' => translate('register_benefits_header_meet_benefits'),
                'benefits'       => [
                    [
                        'icon'      => 'ep-icon_sheild-ok-stroke',
                        'title'     => translate('register_benefits_meet_benefits_block_buyer_legal_header'),
                        'subtitle'  => translate('register_benefits_meet_benefits_block_buyer_legal_header_sub'),
                        'text'      => translate('register_benefits_meet_benefits_block_buyer_legal_desc'),
                    ],
                    [
                        'icon'      => 'ep-icon_locked',
                        'title'     => translate('register_benefits_meet_benefits_block_buyer_secure_header'),
                        'subtitle'  => translate('register_benefits_meet_benefits_block_buyer_secure_header_sub'),
                        'text'      => translate('register_benefits_meet_benefits_block_buyer_secure_desc'),
                    ],
                    [
                        'icon'      => 'ep-icon_partners',
                        'title'     => translate('register_benefits_meet_benefits_block_buyer_safe_header'),
                        'subtitle'  => translate('register_benefits_meet_benefits_block_buyer_safe_header_sub'),
                        'text'      => translate('register_benefits_meet_benefits_block_buyer_safe_desc'),
                    ],
                    [
                        'icon'      => 'ep-icon_highlight',
                        'title'     => translate('register_benefits_meet_benefits_block_buyer_guidance_header'),
                        'subtitle'  => translate('register_benefits_meet_benefits_block_buyer_guidance_header_sub'),
                        'text'      => translate('register_benefits_meet_benefits_block_buyer_guidance_desc'),
                    ],
                    [
                        'icon'      => 'ep-icon_globe-circle',
                        'title'     => translate('register_benefits_meet_benefits_block_buyer_global_sellers_header'),
                        'subtitle'  => translate('register_benefits_meet_benefits_block_buyer_global_sellers_header_sub'),
                        'text'      => translate('register_benefits_meet_benefits_block_buyer_global_sellers_desc'),
                    ],
                    [
                        'icon'      => 'ep-icon_support2',
                        'title'     => translate('register_benefits_meet_benefits_block_buyer_shipping_header'),
                        'subtitle'  => translate('register_benefits_meet_benefits_block_buyer_shipping_header_sub'),
                        'text'      => translate('register_benefits_meet_benefits_block_buyer_shipping_desc'),
                    ],
                ],
                'reviews_title'  => translate('register_testiminials_header'),
                'slides'         => [
                    [
                        'text'       => translate('register_testiminials_buyer_1_text'),
                        'image_lazy' => getLazyImage(54, 36),
                        'image_link' => asset('public/build/images/register_forms/testimonials/danielle_mendosa.jpg'),
                        'name'        => translate('register_testiminials_buyer_1_name'),
                    ],
                    [
                        'text'       => translate('register_testiminials_buyer_2_text'),
                        'image_lazy' => getLazyImage(54, 36),
                        'image_link' => asset('public/build/images/register_forms/testimonials/alessandra_himenes.jpg'),
                        'name'        => translate('register_testiminials_buyer_2_name'),
                    ],
                    [
                        'text'       => translate('register_testiminials_buyer_3_text'),
                        'image_lazy' => getLazyImage(54, 54),
                        'image_link' => asset('public/build/images/register_forms/testimonials/asim_kaphiri.jpg'),
                        'name'        => translate('register_testiminials_buyer_3_name'),
                    ],
                    [
                        'text'       => translate('register_testiminials_buyer_4_text'),
                        'image_lazy' => getLazyImage(54, 41),
                        'image_link' => asset('public/build/images/register_forms/testimonials/thomas_akker.jpg'),
                        'name'        => translate('register_testiminials_buyer_4_name'),
                    ],
                    [
                        'text'       => translate('register_testiminials_buyer_5_text'),
                        'image_lazy' => getLazyImage(54, 54),
                        'image_link' => asset('public/build/images/register_forms/testimonials/rajput_chatha.jpg'),
                        'name'        => translate('register_testiminials_buyer_5_name'),
                    ],
                ],
            ],
            'webpackData'            => [
                'styleCritical' => 'register_buyer',
                'pageConnect' 	=> 'register_forms',
            ]
        );

		$this->view_registration_pages($data_buyer);
	}

	public function seller()
	{
		$data_seller = array(
			'register_type'          => 'seller',
			'register_ttl'       	 => translate('register_form_title_seller'),
			'company_type'       	 => 'seller',
			'header_seller'      	 => true,
            'simple_location'    	 => true,
            'templateViews'          => [
                'footerOutContent' => 'register/content_view',
            ],
            'content'                => [
                'undertitle'     => translate('register_benefits_header_seller'),
                'title'          => translate('register_benefits_header_seller_sub_text'),
                'subtitle'       => translate('register_benefits_header_we_provide'),
                'advantages'     => [
                    [
                        'icon' => 'ep-icon_globe',
                        'text' => translate('register_benefits_we_provide_seller_global_customers'),
                    ],
                    [
                        'icon' => 'ep-icon_truck',
                        'text' => translate('register_benefits_we_provide_seller_shipping'),
                    ],
                    [
                        'icon' => 'ep-icon_group-stroke',
                        'text' => translate('register_benefits_we_provide_seller_b2b'),
                    ],
                ],
                'benefits_title' => translate('register_benefits_header_meet_benefits'),
                'benefits'       => [
                    [
                        'icon'      => 'ep-icon_locked',
                        'title'     => translate('register_benefits_meet_benefits_block_seller_secure_header'),
                        'subtitle'  => translate('register_benefits_meet_benefits_block_seller_secure_header_sub'),
                        'text'      => translate('register_benefits_meet_benefits_block_seller_secure_desc'),
                    ],
                    [
                        'icon'      => 'ep-icon_globe-circle',
                        'title'     => translate('register_benefits_meet_benefits_block_seller_customers_header'),
                        'subtitle'  => translate('register_benefits_meet_benefits_block_seller_customers_header_sub'),
                        'text'      => translate('register_benefits_meet_benefits_block_seller_customers_desc'),
                    ],
                    [
                        'icon'      => 'ep-icon_sheild-ok-stroke',
                        'title'     => translate('register_benefits_meet_benefits_block_seller_safe_deal_header'),
                        'subtitle'  => translate('register_benefits_meet_benefits_block_seller_safe_deal_header_sub'),
                        'text'      => translate('register_benefits_meet_benefits_block_seller_safe_deal_desc'),
                    ],
                    [
                        'icon'      => 'ep-icon_highlight',
                        'title'     => translate('register_benefits_meet_benefits_block_seller_guidance_header'),
                        'subtitle'  => translate('register_benefits_meet_benefits_block_seller_guidance_header_sub'),
                        'text'      => translate('register_benefits_meet_benefits_block_seller_guidance_desc'),
                    ],
                    [
                        'icon'      => 'ep-icon_partners',
                        'title'     => translate('register_benefits_meet_benefits_block_seller_b2b_header'),
                        'subtitle'  => translate('register_benefits_meet_benefits_block_seller_b2b_header_sub'),
                        'text'      => translate('register_benefits_meet_benefits_block_seller_b2b_desc'),
                    ],
                    [
                        'icon'      => 'ep-icon_support2',
                        'title'     => translate('register_benefits_meet_benefits_block_seller_shipping_header'),
                        'subtitle'  => translate('register_benefits_meet_benefits_block_seller_shipping_header_sub'),
                        'text'      => translate('register_benefits_meet_benefits_block_seller_shipping_desc'),
                    ],
                ],
                'reviews_title'  => translate('register_testiminials_header'),
                'slides'         => [
                    [
                        'text'       => translate('register_testiminials_seller_1_text'),
                        'image_lazy' => getLazyImage(54, 57),
                        'image_link' => asset('public/build/images/register_forms/testimonials/peter_gray.jpg'),
                        'name'        => translate('register_testiminials_seller_1_name'),
                    ],
                    [
                        'text'       => translate('register_testiminials_seller_2_text'),
                        'image_lazy' => getLazyImage(54, 36),
                        'image_link' => asset('public/build/images/register_forms/testimonials/david_young.jpg'),
                        'name'        => translate('register_testiminials_seller_2_name'),
                    ],
                    [
                        'text'       => translate('register_testiminials_seller_3_text'),
                        'image_lazy' => getLazyImage(54, 60),
                        'image_link' => asset('public/build/images/register_forms/testimonials/sebastian_vogel.jpg'),
                        'name'        => translate('register_testiminials_seller_3_name'),
                    ],
                    [
                        'text'       => translate('register_testiminials_seller_4_text'),
                        'image_lazy' => getLazyImage(54, 42),
                        'image_link' => asset('public/build/images/register_forms/testimonials/thakur_shan.jpg'),
                        'name'        => translate('register_testiminials_seller_4_name'),
                    ],
                    [
                        'text'       => translate('register_testiminials_seller_5_text'),
                        'image_lazy' => getLazyImage(54, 42),
                        'image_link' => asset('public/build/images/register_forms/testimonials/elena_kuznetsova.jpg'),
                        'name'        => translate('register_testiminials_seller_5_name'),
                    ],
                ],
            ],
            'webpackData'            => [
                'styleCritical' => 'register_buyer',
                'pageConnect' 	=> 'register_forms',
            ]
		);

		$this->view_registration_pages($data_seller);

	}

	public function manufacturer()
	{
		$data_manufacturer = array(
			'register_type'      	 => 'seller',
			'register_ttl'       	 => translate('register_form_title_manufacturer'),
			'company_type'       	 => 'manufacturer',
			'header_seller'      	 => true,
            'simple_location'    	 => true,
            'templateViews'          => [
                'footerOutContent' => 'register/content_view',
            ],
            'content'                => [
                'undertitle'     => translate('register_benefits_header_manufacturer'),
                'title'          => translate('register_benefits_header_seller_sub_text'),
                'subtitle'       => translate('register_benefits_header_we_provide'),
                'advantages'     => [
                    [
                        'icon' => 'ep-icon_globe',
                        'text' => translate('register_benefits_we_provide_seller_global_customers'),
                    ],
                    [
                        'icon' => 'ep-icon_truck',
                        'text' => translate('register_benefits_we_provide_seller_shipping'),
                    ],
                    [
                        'icon' => 'ep-icon_group-stroke',
                        'text' => translate('register_benefits_we_provide_seller_b2b'),
                    ],
                ],
                'benefits_title' => translate('register_benefits_header_meet_benefits'),
                'benefits'       => [
                    [
                        'icon'      => 'ep-icon_locked',
                        'title'     => translate('register_benefits_meet_benefits_block_manufacturer_secure_header'),
                        'subtitle'  => translate('register_benefits_meet_benefits_block_seller_secure_header_sub'),
                        'text'      => translate('register_benefits_meet_benefits_block_seller_secure_desc'),
                    ],
                    [
                        'icon'      => 'ep-icon_globe-circle',
                        'title'     => translate('register_benefits_meet_benefits_block_seller_customers_header'),
                        'subtitle'  => translate('register_benefits_meet_benefits_block_seller_customers_header_sub'),
                        'text'      => translate('register_benefits_meet_benefits_block_manufacturer_customers_desc'),
                    ],
                    [
                        'icon'      => 'ep-icon_sheild-ok-stroke',
                        'title'     => translate('register_benefits_meet_benefits_block_seller_safe_deal_header'),
                        'subtitle'  => translate('register_benefits_meet_benefits_block_seller_safe_deal_header_sub'),
                        'text'      => translate('register_benefits_meet_benefits_block_seller_safe_deal_desc'),
                    ],
                    [
                        'icon'      => 'ep-icon_highlight',
                        'title'     => translate('register_benefits_meet_benefits_block_seller_guidance_header'),
                        'subtitle'  => translate('register_benefits_meet_benefits_block_seller_guidance_header_sub'),
                        'text'      => translate('register_benefits_meet_benefits_block_seller_guidance_desc'),
                    ],
                    [
                        'icon'      => 'ep-icon_partners',
                        'title'     => translate('register_benefits_meet_benefits_block_seller_b2b_header'),
                        'subtitle'  => translate('register_benefits_meet_benefits_block_seller_b2b_header_sub'),
                        'text'      => translate('register_benefits_meet_benefits_block_manufacturer_b2b_desc'),
                    ],
                    [
                        'icon'      => 'ep-icon_support2',
                        'title'     => translate('register_benefits_meet_benefits_block_seller_shipping_header'),
                        'subtitle'  => translate('register_benefits_meet_benefits_block_seller_shipping_header_sub'),
                        'text'      => translate('register_benefits_meet_benefits_block_seller_shipping_desc'),
                    ],
                ],
                'reviews_title'  => translate('register_testiminials_header'),
                'slides'         => [
                    [
                        'text'       => translate('register_testiminials_seller_1_text'),
                        'image_lazy' => getLazyImage(54, 57),
                        'image_link' => asset('public/build/images/register_forms/testimonials/peter_gray.jpg'),
                        'name'        => translate('register_testiminials_seller_1_name'),
                    ],
                    [
                        'text'       => translate('register_testiminials_seller_2_text'),
                        'image_lazy' => getLazyImage(54, 36),
                        'image_link' => asset('public/build/images/register_forms/testimonials/david_young.jpg'),
                        'name'        => translate('register_testiminials_seller_2_name'),
                    ],
                    [
                        'text'       => translate('register_testiminials_seller_3_text'),
                        'image_lazy' => getLazyImage(54, 60),
                        'image_link' => asset('public/build/images/register_forms/testimonials/sebastian_vogel.jpg'),
                        'name'        => translate('register_testiminials_seller_3_name'),
                    ],
                    [
                        'text'       => translate('register_testiminials_seller_4_text'),
                        'image_lazy' => getLazyImage(54, 42),
                        'image_link' => asset('public/build/images/register_forms/testimonials/thakur_shan.jpg'),
                        'name'        => translate('register_testiminials_seller_4_name'),
                    ],
                    [
                        'text'       => translate('register_testiminials_seller_5_text'),
                        'image_lazy' => getLazyImage(54, 42),
                        'image_link' => asset('public/build/images/register_forms/testimonials/elena_kuznetsova.jpg'),
                        'name'        => translate('register_testiminials_seller_5_name'),
                    ],
                ],
            ],
            'webpackData'            => [
                'styleCritical' => 'register_buyer',
                'pageConnect' 	=> 'register_forms',
            ]
		);

		$this->view_registration_pages($data_manufacturer);

	}

    public function ff()
    {
        if (logged_in()) {
            headerRedirect(__SHIPPER_URL);
        }

        if (__CURRENT_SUB_DOMAIN !== getSubDomains()['shippers']) {
            headerRedirect(__SHIPPER_URL . 'register/ff');
            return;
        }

        $data = array_merge([
            'registerType'        => 'shipper',
            'companyType'         => 'shipper',
            'phoneCodes'          => (new PhoneCodesService(model('country')))->getCountryCodes(),
            'showSubscribePopup'  => false,
            'isRegisterPage'      => true,
            'templateViews'       => [
                'mainOutContent'   => 'epl/register/index_view',
            ],
            'webpackData'         => [
                'styleCritical' => 'epl_critical_styles_register',
                'pageConnect'   => 'epl_register_page',
            ],
        ], $this->viewData);

        views()->assign($data);
        views()->display('new/epl/template/index_view');
    }

	private function __validate_recaptcha($parameters)
    {
        if (false === filter_var(config('env.RECAPTCHA_ENABLED', false), FILTER_VALIDATE_BOOLEAN)) {
            return array(null, null);
        }

		$error_message = translate('register_recaptcha_error_message');
        $token = (null !== $parameters->get('token')) ? $parameters->get('token') : null;
        if(empty($token)) {
            jsonResponse($error_message, 'error', withDebugInformation(
                array(
                    'errors' => array(
                        array(
                            'title'  => "Token is empty",
                            "detail" => "Verification token recieved from request is empty",
                        )
                    )
                ),
                array('token' => $token)
            ));
        }

        $url = 'https://www.recaptcha.net/recaptcha/api/siteverify';
		$verify = file_get_contents($url, false, stream_context_create(array(
			'http' => array(
		    	'method' => 'POST',
			    'content' => http_build_query(array(
                    'secret'   => config('env.RECAPTCHA_PRIVATE_TOKEN_REGISTER'),
                    'response' => $token
                ))
            )
        )));
        if(false === $verify) {
            jsonResponse($error_message, 'error', withDebugInformation(
                array(
                    'errors' => array(
                        array(
                            'title'  => "Verification server is unresponsive",
                            "detail" => "The capthca verification server failed to respond in time or returned empty response",
                        )
                    )
                ),
                array('token' => $token)
            ));
        }
        $captcha_success = json_decode($verify);
        if(null === $captcha_success || json_last_error()) {
            jsonResponse($error_message, 'error', withDebugInformation(
                array(
                    'errors' => array(
                        array(
                            'title'  => "Malformed response",
                            "detail" => "The captchs verification server returned malformed response",
                        )
                    )
                ),
                array('token' => $token, 'challenge' => $captcha_success)
            ));
		}

		if (false === $captcha_success->success) {
            jsonResponse($error_message, 'error', withDebugInformation(
                array(
                    'errors' => array(
                        array(
                            'title'  => "Bot-check failed",
                            "detail" => "The score in bot-check is too low",
                            'meta'   => array(
                                'score' => $captcha_success->score,
                                'codes' => $captcha_success->{"error-codes"},
                            )
                        )
                    )
                ),
                array('token' => $token, 'challenge' => $captcha_success)
            ));
        }

        return array($token, $captcha_success);
    }

	private function __process_with_validator($validator_rules)
	{
		if(!empty($validator_rules)){
			$this->validator->set_rules($validator_rules);
			if(!$this->validator->validate()){
				jsonResponse($this->validator->get_array_errors());
			}
		}

	}

	private function __rules_step_1_all($parameters){

		$validator_rules = array(
			array(
				'field' => 'fname',
				'label' => translate('register_label_first_name'),
				'rules' => array('required' => '','valid_user_name' => '', 'min_len[2]' => '', 'max_len[50]' => '')
			),
			array(
				'field' => 'lname',
				'label' => translate('register_label_last_name'),
				'rules' => array('required' => '','valid_user_name' => '', 'min_len[2]' => '', 'max_len[50]' => '')
			),
			array(
				'field' => 'email',
				'label' => translate('register_label_email'),
				'rules' => array('required' => '','no_whitespaces' => '', 'valid_email' => '', 'max_len[100]' => '')
			),
			array(
				'field' => 'password',
				'label' => translate('register_label_password'),
                'rules' => array('required' => '', 'valid_password' => '')
			),
			array(
				'field' => 'confirm_password',
				'label' => translate('register_label_confirm_password'),
				'rules' => array('required' => '','matches[password]' => '')
			),
			array(
				'field' => 'country_code',
				'label' => translate('register_label_country_code'),
				'rules' => array(
					'required' => '',
					function ($attr, $phone_code_id, $fail){
						if (empty($phone_code_id) || !model('country')->has_country_code($phone_code_id)) {
							$fail(translate('register_label_unknown_value', array('[COLUMN_NAME]' => translate('register_label_country_code'))));
						}
					}
				)
			),
			array(
				'field' => 'phone',
				'label' => translate('register_label_phone'),
				'rules' => array(
					'required' => '',
					function ($attr, $phone, $fail) use ($parameters) {
						$phone_util = PhoneNumberUtil::getInstance();
						$phone_code_id = $parameters->getInt('country_code');
						$phone_code = model('country')->get_country_code($phone_code_id)['ccode'] ?? null;
						$raw_number = trim("{$phone_code} {$phone}");

						try {
							if(!$phone_util->isViablePhoneNumber($raw_number)){
								$fail(translate('register_error_invalid_phone', array('[COLUMN_NAME]' => translate('register_label_phone'))));
							}

							$phone_number = $phone_util->parse($raw_number);
							if(!$phone_util->isValidNumber($phone_number)){
								$fail(translate('register_error_country_unacceptable_phone', array('[COLUMN_NAME]' => translate('register_label_phone'))));
							}
						} catch (NumberParseException $exception) {
							$fail(translate('register_error_invalid_phone', array('[COLUMN_NAME]' => translate('register_label_phone'))));
						}
					}
				)
			),
		);

		return $validator_rules;
	}

	private function __rules_step_2_buyer($suffix = '', $parameters){

		$validator_rules = array(
			array(
				'field' => 'type_buyer'.$suffix,
				'label' => translate('register_label_buyer_type'),
				'rules' => array('required' => '')
			)
		);

		if(!empty($parameters->get('type_buyer'.$suffix)) && $parameters->getInt('type_buyer'.$suffix) == 1){
			$validator_rules[] = array(
				'field' => 'company_legal_name'.$suffix,
				'label' => translate('registration_label_company_name'),
				'rules' => array('required' => '', 'min_len[3]' => '', 'max_len[50]' => '', 'company_title' => '')
			);
			$validator_rules[] = array(
				'field' => 'company_name'.$suffix,
				'label' => translate('registration_label_company_displayed_name'),
				'rules' => array('required' => '','min_len[3]' => '', 'max_len[50]' => '', 'company_title' => '' )
			);
		}

		return $validator_rules;
	}

	private function __rules_step_2_seller(){
		$validator_rules = array(
			array(
				'field' => 'company_legal_name',
				'label' => translate('registration_label_company_name'),
				'rules' => array('required' => '', 'min_len[3]' => '', 'max_len[50]' => '', 'company_title' => '')
			),
			array(
				'field' => 'company_name',
				'label' => translate('registration_label_company_displayed_name'),
				'rules' => array('required' => '', 'min_len[3]' => '', 'max_len[50]' => '', 'company_title' => '')
			),
			array(
				'field' => 'company_type',
				'label' => translate('register_label_company_type'),
				'rules' => array('required' => '')
			),
			array(
				'field' => 'industriesSelected',
				'label' => translate('register_label_industries'),
				'rules' => array('required' => '')
			),
		);

		return $validator_rules;
	}

	private function __rules_step_2_shipper(){
		$validator_rules = array(
			array(
				'field' => 'company_legal_name',
				'label' => translate('registration_label_company_name'),
				'rules' => array('required' => '','company_title' => '', 'min_len[3]' => '', 'max_len[50]' => '')
			),
			array(
				'field' => 'company_name',
				'label' => translate('registration_label_company_displayed_name'),
				'rules' => array('required' => '', 'min_len[3]' => '', 'max_len[50]' => '', 'company_title' => '')
			),
			array(
				'field' => 'company_offices_number',
				'label' => translate('register_label_number_of_offices'),
				'rules' => array('required' => '','natural' => '', 'max[999999]' => '')
			),
			array(
				'field' => 'company_teu',
				'label' => translate('register_label_company_teu'),
				'rules' => array('required' => '','natural' => '', 'max[9999999999]' => '')
			),
		);

		return $validator_rules;
	}

    private function __rules_step_last_all(Request $request, bool $simple_mode = false): array
    {
        if ($simple_mode) {
            $location_rules = array(
                array(
                    'field' => 'country',
                    'label' => translate('register_label_country'),
                    'rules' => array('required' => '','natural' => '')
                ),
                array(
                    'field' => 'states',
                    'label' => translate('register_label_state_region'),
                    'rules' => array('natural' => '')
                ),
                array(
                    'field' => 'port_city',
                    'label' => translate('register_label_city'),
                    'rules' => array('natural' => '')
                ),
            );
        } else {
            $location_rules = array(
                array(
                    'field' => 'country',
                    'label' => translate('register_label_country'),
                    'rules' => array('required' => '','natural' => '')
                ),
            );

            if ($request->request->getInt('custom_location') ?: false) {
                $location_rules[] = array(
                    'field' => 'location',
                    'label' => translate('register_label_location'),
                    'rules' => array(
                        'required'        => '',
                        'min_length[2]'   => '',
                        'max_length[300]' => '',
                    )
                );
            } else {
                $location_rules[] = array(
                    'field' => 'states',
                    'label' => translate('register_label_state_region'),
                    'rules' => array(
                        'required' => '',
                        'natural'  => '',
                    )
                );
                $location_rules[] = array(
                    'field' => 'port_city',
                    'label' => translate('register_label_city'),
                    'rules' => array(
                        'required' => '',
                        'natural'  => '',
                    )
                );
            }
        }

		return array_merge(
            $location_rules,
            array(
                array(
                    'field' => 'address',
                    'label' => translate('register_label_company_address'),
                    'rules' => array('required' => '', 'min_len[3]' => '', 'max_len[255]' => '')
                ),
                array(
                    'field' => 'zip',
                    'label' => translate('register_label_zip'),
                    'rules' => array('required' => '', 'zip_code' => '', 'max_len[20]' => '')
                ),
                array(
                    'field' => 'terms_cond',
                    'label' => translate('register_label_terms_and_conditions'),
                    'rules' => array('required' => '')
                )
            )
        );
	}

	private function __validate_max_industries($suffix = '', $parameters){
		$post_industries_count = count($parameters->get('industriesSelected'.$suffix));
		$multipleselect_max_industries = (int) config('multipleselect_max_industries', 3);

		if($post_industries_count > $multipleselect_max_industries){
			jsonResponse(translate('multipleselect_max_industries', array('[COUNT]' => $multipleselect_max_industries)), 'warning');
		}
	}

	private function __validate_industries($suffix = '', $parameters){
        /** @var Company_Model $companyModel */
        $companyModel = model(Company_Model::class);

		$industriesSelected = array_filter(array_map('intval', (array) $parameters->get('industriesSelected' . $suffix)));
        $categoriesSelected = array_filter(array_map('intval', (array) $parameters->get('categoriesSelected' . $suffix)));
        $needCopyCompanyInformation = (int) $parameters->get('copy_company_info');

        if (!empty($needCopyCompanyInformation)) {
            //During the copying process, information about the company, industries, and categories is not transmitted to the server
            if (!empty($industriesSelected) || !empty($categoriesSelected)) {
                jsonResponse(translate('systmess_error_invalid_data'));
            }

            return;
        }

		if (empty($industriesSelected)) {
			jsonResponse(translate('register_error_no_industries'));
		}

        if (empty($categoriesSelected)) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }

		$post_industries_count = count($industriesSelected);
		$industries_count = $companyModel->count_categories_by_conditions([
            'category_list' => implode(',', $industriesSelected),
            'parent' => 0,
        ]);

		if ($post_industries_count !== (int) $industries_count) {
			jsonResponse(translate('register_error_industries_not_exist'), 'error');
		}

        $postCategoryCount = count($categoriesSelected);
        $categoryCount = $companyModel->count_categories_by_conditions([
            'category_list' => implode(',', $categoriesSelected),
            'parent_not' => 0,
        ]);

        if ($postCategoryCount !== (int) $categoryCount) {
            jsonResponse(translate('systmess_error_invalid_data'), 'error');
        }
	}

	private function __get_post_industries($suffix = '', $parameters)	{
		$this->post_industries[$suffix] = array();
        $this->post_industries[$suffix] = $parameters->get('industriesSelected'.$suffix);
		$this->post_industries[$suffix] = array_map('intval', $this->post_industries[$suffix]);
		$this->post_industries[$suffix] = array_filter($this->post_industries[$suffix]);

		$industries_all = model('category')->getCategories(array('columns' => 'category_id', 'cat_list' => $this->post_industries[$suffix]));
		$this->industries[$suffix] = array();
        $this->industries[$suffix] = arrayByKey($industries_all, 'category_id');
	}

	private function __rules_another_seller($suffix = 'seller', $requiredIndustries = true){
		$suffix = '_additional_' . $suffix;

		$validatorRules = [
			[
				'field' => 'company_legal_name' . $suffix,
				'label' => translate('register_label_company_country'),
				'rules' => ['required' => '','company_title' => '', 'min_len[3]' => '', 'max_len[50]' => '']
            ],
			[
				'field' => 'company_name' . $suffix,
				'label' => translate('registration_label_company_displayed_name'),
				'rules' => ['required' => '', 'min_len[3]' => '', 'max_len[50]' => '', 'company_title' => '']
            ],
		];

        if ($requiredIndustries) {
            $validatorRules[] = [
				'field' => 'industriesSelected' . $suffix,
				'label' => translate('register_label_industries'),
				'rules' => ['required' => '']
            ];
        }

		return $validatorRules;
	}

	private function __validate_types_of_account($type_another_account)	{
		$another_types = array('buyer', 'seller', 'manufacturer', 'all');
		if(!in_array($type_another_account, $another_types)){
			jsonResponse(translate('register_error_no_such_additional_account'));
		}
	}

	private function __get_additional_accounts(){
		$accounts = session()->__get('accounts');

		if(!isset($accounts) || empty($accounts)){
			$accounts = model('user')->get_related_users_by_id_principal(session()->__get('id_principal'));
		}

		return $accounts;
	}

	private function __validate_user_type_exist($type_another_account){

		$accounts = $this->__get_additional_accounts();

		if(count($accounts) >= 3){
			jsonResponse(translate('login_already_all_types_of_accounts_message'));
		}

		if(!empty($accounts)){
			$types = array_map(function ($account) {
				return returnTrueUserGroupName($account['user_group'], $account['gr_type']);
			}, $accounts);

			if($type_another_account == 'all'){
				$current_type = returnTrueUserGroupName();
				switch($current_type){
					case 'buyer':
						$additional_types_available = array('manufacturer', 'seller');
					break;
					case 'manufacturer':
						$additional_types_available = array('buyer', 'seller');
					break;
					case 'seller':
						$additional_types_available = array('buyer', 'manufacturer');
					break;
				}
				if(!empty($already_registered = array_intersect($additional_types_available, $types))){
					jsonResponse(translate('login_already_have_account_message', array('[[TYPE_ACCOUNTS]]' => implode(',', $already_registered))));
				}
			}
			if(in_array($type_another_account, $types)){
				jsonResponse(translate('login_already_have_accounts_message', array('[[TYPE_ACCOUNT]]' => ucfirst($type_another_account))));
			}
		}
	}

	private function __validate_all_steps_additional($parameters){
		$company_type = cleanInput($parameters->get('company_type'));
		$type_another_account = cleanInput($parameters->get('type_another_account'));

		if(
			(isset($type_another_account)
			&& !empty($type_another_account))
			|| $this->adding_additional_accounts
		){
			$validator_rules = array();

			$this->__validate_types_of_account($type_another_account);

			if($type_another_account == 'all'){
				$selected_account = $company_type . '_select_all';
			}else{
				$selected_account = $type_another_account;
			}

			switch($selected_account){
				case 'buyer':
					$suffix = '_additional_'.$type_another_account;

					$validator_rules = $this->__rules_step_2_buyer($suffix, $parameters);
				break;
				case 'seller':
				case 'manufacturer':
					$suffix = '_additional_'.$type_another_account;
					$this->__validate_max_industries($suffix, $parameters);
					$this->__validate_industries($suffix, $parameters);

					$validator_rules = $this->__rules_another_seller($type_another_account, empty($parameters->get('copy_company_info')));
				break;
				case 'buyer_select_all':
					$suffix_seller = '_additional_seller';
					$this->__validate_max_industries($suffix_seller, $parameters);
					$this->__validate_industries($suffix_seller, $parameters);

					$suffix_manufacturer = '_additional_manufacturer';
					$this->__validate_max_industries($suffix_manufacturer, $parameters);
					$this->__validate_industries($suffix_manufacturer, $parameters);

					$validator_rules = array_merge(
						$this->__rules_another_seller('seller', empty($parameters->get('copy_company_info'))),
						$this->__rules_another_seller('manufacturer', empty($parameters->get('copy_company_info')))
					);
				break;
				case 'seller_select_all':
					$suffix = '_additional_buyer';

					$suffix_manufacturer = '_additional_manufacturer';
					$this->__validate_max_industries($suffix_manufacturer, $parameters);
					$this->__validate_industries($suffix_manufacturer, $parameters);

					$validator_rules = array_merge(
						$this->__rules_step_2_buyer($suffix, $parameters),
						$this->__rules_another_seller('manufacturer', empty($parameters->get('copy_company_info')))
					);
				break;
				case 'manufacturer_select_all':
					$suffix = '_additional_buyer';

					$suffix_seller = '_additional_seller';
					$this->__validate_max_industries($suffix_seller, $parameters);
					$this->__validate_industries($suffix_seller, $parameters);

					$validator_rules = array_merge(
						$this->__rules_step_2_buyer($suffix, $parameters),
						$this->__rules_another_seller('seller', empty($parameters->get('copy_company_info')))
					);
				break;
			}

			$this->__process_with_validator($validator_rules);
		}

		return false;
	}

	function ajax_operations(){
		if (!isAjaxRequest()){
			headerRedirect();
		}

        $action = $this->uri->segment(3);
        $request = request();
        $parameters = $request->request;

		switch($action) {
			case 'resend_confirmation_email':
				if (logged_in()){
					jsonResponse(translate('systmess_info_already_logged_in'), 'info');
                }
				$validator_rules = array(
					array(
						'field' => 'email',
						'label' => translate('register_label_email'),
						'rules' => array('required' => '','no_whitespaces' => '', 'valid_email' => '')
					)
				);

				$this->__process_with_validator($validator_rules);

				$email = cleanInput($parameters->get('email'), true);
				$user_info = model('user')->getSimpleUserByEmail($email);
                $invalidDataForFF = $user_info['user_type'] === "shipper" && __CURRENT_SUB_DOMAIN !== getSubDomains()['shippers'];
                $invalidDataForUser = $user_info['user_type'] !== "shipper" && __CURRENT_SUB_DOMAIN === getSubDomains()['shippers'];

				if (empty($user_info) || $user_info['status'] != 'new' || $invalidDataForUser  || $invalidDataForFF) {
					jsonResponse(translate('systmess_error_invalid_data'));
				}

				if($user_info['resend_accreditation_email'] >= config('resend_registration_confirmation_email_limit', 10)){
					jsonResponse(translate('system_message_resend_registration_confirmation_email_limit_exited', array('{contact_page_link}' => __SITE_URL.'contact', '{support_email}' => config('noreply_email', 'support@exportportal.com'))), 'info');
				}

				//region send confirmation email
				$this->send_confirmation_email((int) $user_info['idu']);
				//endregion send confirmation email

				model('user')->set_notice($user_info['idu'], array(
					'add_date' => date('Y/m/d H:i:s'),
					'add_by' => $user_info['fname'] . '' . $user_info['lname'],
					'notice' => 'Request to resend registration confirmation email with accreditation link. Email has been sent.'
				));

				model('user')->updateUserMain($user_info['idu'], array(
					'resend_accreditation_email' => $user_info['resend_accreditation_email'] + 1,
					'resend_email_date' => date('Y-m-d H:i:s')
				));

				jsonResponse(translate('systmess_success_email_has_been_sent'), 'success');
			break;
			case 'validate_step':
				if (logged_in()){
					jsonResponse(translate('systmess_info_already_logged_in'), 'info');
				}
				$step = $this->uri->segment(4);
                $dataShipper = [
                    'registerType' => 'shipper',
                    'companyType'  => 'shipper',
                    'portCountry'  => model(Country::class)->fetch_port_country(),
                ];

				switch($step){
					case 'validate_step_1_all':
						$validator_rules = $this->__rules_step_1_all($parameters);

						$email = cleanInput($parameters->get('email'), true);

						$encrypted_email = getEncryptedEmail($email);

						if(model(Auth_Model::class)->exists_hash($encrypted_email)){
							jsonResponse(translate('register_error_email_already_registered'));
						}

						// region check email status through API
						if(config('env.APP_ENV') === 'dev'){
							session()->__set('email_delivarable_status', 'Bad');
						}else{
							//if not the same email
							if($email != session()->__get('email_to_register'))
							{
								if((int) session()->__get('email_check_tries_count') < (int) config('env.EMAILCHECKER_API_MAX_TRIES'))
								{
									$email_status_response = checkEmailDeliverability($email);
									session()->__set('email_check_tries_count', ((int) session()->__get('email_check_tries_count') + 1));
									session()->__set('email_to_register', $email);
									session()->__set('email_delivarable_status', $email_status_response);

								}else{
                                    checkEmailDeliverability($email);

									//if max tries set status to null
									session()->clear('email_delivarable_status');
								}
							}

							if('Bad' == session()->__get('email_delivarable_status')){
								jsonResponse(translate('register_error_undeliverable_email', array('[USER_EMAIL]' => $email)));
							}

						}
						// endregion check email status through API

                        $content = views()->fetch("new/epl/register/step_2_view", $dataShipper);
					break;
					case 'validate_step_2_seller':
						$this->__validate_max_industries('', $parameters);
						$this->__validate_industries('', $parameters);
						$validator_rules = $this->__rules_step_2_seller();
					break;
					case 'validate_step_2_buyer':
						$validator_rules = $this->__rules_step_2_buyer('', $parameters);
					break;
					case 'validate_step_2_shipper':
						$validator_rules = $this->__rules_step_2_shipper();
                        $content = views()->fetch("new/epl/register/step_3_view", $dataShipper);
					break;
					case 'validate_step_additional':
						$this->__validate_all_steps_additional($parameters);
					break;
				}

				$this->__process_with_validator($validator_rules);

                if (__CURRENT_SUB_DOMAIN === getSubDomains()['shippers']) {
                    jsonResponse('','success', ['content' => $content]);
                } else {
                    jsonResponse('','success');
                }
			break;
			case 'shipper':
				if (logged_in()){
					jsonResponse(translate('systmess_info_already_logged_in'), 'info');
				}
				list($token, $captcha_challenge) = $this->__validate_recaptcha($parameters);

				$this->__process_with_validator(array_merge(
                    $this->__rules_step_1_all($parameters),
                    $this->__rules_step_2_shipper(),
                    $this->__rules_step_last_all($request)
                ));

				$this->load->model('User_Model', 'users');
				$this->load->model('Country_model', 'country');
				$this->load->model('Shippers_Model', 'shipper');

				$email = cleanInput($parameters->get('email'), true);
				$encrypted_email = getEncryptedEmail($email);
				if(model(Auth_Model::class)->exists_hash($encrypted_email)){
					jsonResponse(translate('systmess_error_email_already_registered'));
				}

				//region Phone code
				$phone_code = (new PhoneCodesService(model('country')))->findAllMatchingCountryCodes($parameters->getInt('country_code'))->first();
				//endregion Phone code

                $user_group = 31;
				$this->insert = array(
					'fname'                  => cleanInput($parameters->get('fname')),
					'lname'                  => cleanInput($parameters->get('lname')),
					'email'                  => $email,
					'email_status'			 => session()->__get('email_delivarable_status') ?? null,
					'user_ip'                => getVisitorIP(),
					'registration_date'      => date('Y-m-d H:i:s'),
					'activation_code'        => get_sha1_token($email),
					'confirmation_token'     => get_sha1_token($email),
					'user_type'              => 'shipper',
					'user_group'             => $user_group,
					'status'                 => 'new',
					'accreditation_token'    => get_sha1_token($email),
					'paid'                   => 1,
					'paid_until'             => '0000-00-00',
					'country'                => $request->request->getInt('country'),
					'state'                  => null,
					'city'                   => null,
					'zip'                    => cleanInput($parameters->get('zip')),
					'phone_code_id'          => $phone_code ? $phone_code->getId() : null,
					'phone_code'             => $phone_code ? $phone_code->getName() : null,
					'phone'                  => cleanInput($parameters->get('phone')),
					'address'                => cleanInput($parameters->get('address')),
					'user_initial_lang_code' => __SITE_LANG,
                );

                //region REFERRER
                $refferer_info = $this->get_referrer();
                if(!empty($refferer_info)){
                    $this->insert = array_merge($this->insert, $refferer_info);
                }
                //endregion REFERRER

                $uses_custom_location = $request->request->getInt('custom_location') ?: false;
                if (!$uses_custom_location) {
                    $this->insert['state'] = $request->request->getInt('states') ?: null;
                    $this->insert['city'] = $request->request->getInt('port_city') ?: null;
                }

				// Get verification documents
				$verification_documents = $this->collect_verification_documents($user_group, $parameters->getInt('country'));

				$this->insert['notice'] = $this->__get_notices('freight forwarder');

				$this->__get_and_update_city_info();

				$id_principal = $this->insert['id_principal'] = (int) $this->__save_hashes($parameters) ?: null;
				$id_user = $this->users->setUserMain($this->insert);

				if(!$id_user){
					jsonResponse(translate('systmess_error_db_insert_error'));
                }

				array_push($this->id_users, $id_user);

				$insert_company = array(
					'id_user'        => $id_user,
					'legal_co_name'  => cleanInput($parameters->get('company_legal_name')),
					'co_name'        => cleanInput($parameters->get('company_name')),
					'offices_number' => $parameters->getInt('company_offices_number'),
					'id_country'     => $this->insert['country'],
					'id_state'       => $this->insert['state'],
					'id_city'        => $this->insert['city'],
					'email'          => $email,
					'id_phone_code'  => $phone_code ? $phone_code->getId() : null,
					'phone_code'     => $phone_code ? $phone_code->getName() : null,
					'phone'          => cleanInput($parameters->get('phone')),
					'zip'            => cleanInput($parameters->get('zip')),
					'address'        => cleanInput($parameters->get('address')),
					'co_teu'         => intVal($parameters->get('company_teu'))
				);

				if (!empty($verification_documents)) {
					$insert_company['accreditation'] = 0;
				}

				$id_shipper = $this->shipper->insert_shipper($insert_company);
				$this->shipper->set_shipper_user_relation(array('id_shipper' => $id_shipper, 'id_user' => $id_user));

                // Add default notification settings
                model('users_systmess_settings')->add_default_settings($id_user);

				// Add verification documents
                $this->add_verification_documents($id_user, $id_principal, $verification_documents);

                // Add custom location request if exists
                if ($uses_custom_location) {
                    model(Custom_Locations_Model::class)->insertOne(array(
                        'id_principal' => $id_principal,
                        'location'     => $request->request->get('location'),
                    ));
                }

				//region send confirmation email
				$this->send_confirmation_email($id_user);
				//endregion send confirmation email

                // $this->session->clear('secpic');
                $this->session->clear('email_delivarable_status');

				// UPDATE ACTIVITY LOG
                $this->update_user_activity_log($id_user);
                set_verification_session_data($id_user, $user_group, 'Shipper');

                // Tumbling down the rabbit hole
                $this->get(MessengerInterface::class)->bus('event.bus')->dispatch(new UserHasRegisteredEvent((int) $id_user));

                $messageSuccess = views()->fetch('new/epl/register/message_success_view', [
                    'email'        => $this->insert['email'],
                    'registerType' => 'shipper',
                ]);
                jsonResponse($messageSuccess, 'success', withDebugInformation(['email' => $this->insert['email']], [
                    'token'     => $token,
                    'challenge' => $captcha_challenge,
                ]));
			break;
			case 'seller' :
				if (logged_in()){
					jsonResponse(translate('systmess_info_already_logged_in'), 'info');
				}
				list($token, $captcha_challenge) = $this->__validate_recaptcha($parameters);

				$this->__validate_max_industries('', $parameters);
                $this->__validate_industries('', $parameters);
				$this->__process_with_validator(array_merge(
					$this->__rules_step_1_all($parameters),
					$this->__rules_step_2_seller(),
					$this->__rules_step_last_all($request, true),
				));

				$this->__validate_all_steps_additional($parameters);

				$this->load->model('User_Model', 'users');
				$this->load->model('Packages_Model', 'packages');
                $this->load->model('Country_model', 'country');

				$email = cleanInput($parameters->get('email'), true);
				$encrypted_email = getEncryptedEmail($email);
				if(model(Auth_Model::class)->exists_hash($encrypted_email)){
					jsonResponse(translate('systmess_error_email_already_registered'));
				}

				$company_type = cleanInput($parameters->get('company_type'));
				if(!in_array($company_type, array('seller', 'distributor', 'manufacturer'))){
					jsonResponse(translate('register_error_cannot_register_as', array('[COMPANY_TYPE]' => $company_type)));
                }

                $is_distributor = $parameters->getInt('is_distributor');
                $company_type = $is_distributor ? 'distributor' : $company_type;

				$package = $this->packages_types[$company_type]['package'];
				$id_type = $this->packages_types[$company_type]['id_type'];
				$email_group_name = $this->packages_types[$company_type]['group_name'];

				$packageInfo = $this->packages->getGrPackage($package);

				if(empty($packageInfo)){
					jsonResponse(translate('systmess_error_sended_data_not_valid'));
				}

				switch($packageInfo['abr']){
					case 'F':
						$paid_until = '0000-00-00';
					break;
					default:
						$end_date = date_plus($packageInfo['days']);
						$paid_until = formatDate($end_date, 'Y-m-d');
					break;
				}

				//region Phone code
				$phone_code = (new PhoneCodesService(model('country')))->findAllMatchingCountryCodes($parameters->getInt('country_code'))->first();
				//endregion Phone code

				$this->insert = array(
					'fname'                  => cleanInput($parameters->get('fname')),
					'lname'                  => cleanInput($parameters->get('lname')),
					'email'                  => $email,
					'email_status'			 => session()->__get('email_delivarable_status') ?? null,
					'user_ip'                => getVisitorIP(),
					'registration_date'      => date('Y-m-d H:i:s'),
					'activation_code'        => get_sha1_token($email),
					'confirmation_token'     => get_sha1_token($email),
					'user_type'              => 'user',
					'user_group'             => $packageInfo['gr_to'],
					'status'                 => 'new',
                    'accreditation_token'    => get_sha1_token($email),
					'paid_until'             => $paid_until,
					'country'                => $request->request->getInt('country'),
					'state'                  => null,
					'city'                   => null,
					'zip'                    => cleanInput($parameters->get('zip')),
					'address'                => cleanInput($parameters->get('address')),
					'paid'                   => 1,
					'phone_code_id'          => $phone_code ? $phone_code->getId() : null,
					'phone_code'             => $phone_code ? $phone_code->getName() : null,
                    'phone'                  => cleanInput($parameters->get('phone')),
					'user_initial_lang_code' => __SITE_LANG,
                );

                //region REFERRER
                $refferer_info = $this->get_referrer();
                if(!empty($refferer_info)){
                    $this->insert = array_merge($this->insert, $refferer_info);
                }
                //endregion REFERRER

                $uses_custom_location = $request->request->getInt('custom_location') ?: false;
                if (!$uses_custom_location) {
                    $this->insert['state'] = $request->request->getInt('states') ?: null;
                    $this->insert['city'] = $request->request->getInt('port_city') ?: null;
                }

				// Get verification documents
				$verification_documents = $this->collect_verification_documents((int) $packageInfo['gr_to'], $parameters->getInt('country'));

                if ($is_distributor) {
                    /** @var Verification_Document_Types_Model */
                    $varificationTypes = model(Verification_Document_Types_Model::class);
                    $additional_verification_documents = $varificationTypes->findAllBy([
                        'exists' => [
                            $varificationTypes
                                ->getRelationsRuleBuilder()
                                ->whereHas('groupsReference', function (QueryBuilder $builder, RelationInterface $relation) use ($packageInfo) {
                                    $relation->getRelated()->getScope('userGroup')($builder, (int) $packageInfo['gr_to']);
                                }),
                            $varificationTypes
                                ->getRelationsRuleBuilder()
                                ->whereHas('countriesReference', function (QueryBuilder $builder, RelationInterface $relation) use ($parameters) {
                                    $relation->getRelated()->getScope('country')($builder, $parameters->getInt('country'));
                                }),
                        ],
                        'scopes' => [
                            'hasAdditionalOptions' => true
                        ],
                    ]);

                    if (!empty($additional_verification_documents)) {
                        foreach ($additional_verification_documents as $document) {
                            $additional_options = $document['document_additional_options'] ?? null;
                            if (isset($additional_options['company_types']) && in_array($id_type, $additional_options['company_types'])) {
                                $verification_documents[] = (int) $document['id_document'];
                            }
                        }
                    }
                }

				$this->insert['notice'] = $this->__get_notices($email_group_name);

				$this->__get_and_update_city_info();

				$id_principal = $this->insert['id_principal'] = $this->__save_hashes($parameters);
				$id_user = $this->users->setUserMain($this->insert);

				if(!$id_user){
					jsonResponse(translate('systmess_error_db_insert_error'));
				}
				array_push($this->id_users, $id_user);

				$insert_company = array(
					'id_user'               => $id_user,
					'id_type'               => $id_type,
					'name_company'          => cleanInput($parameters->get('company_name')),
					'legal_name_company'    => cleanInput($parameters->get('company_legal_name')),
					'id_country'            => $this->insert['country'],
					'id_state'              => $this->insert['state'],
					'id_city'               => $this->insert['city'],
					'zip_company'           => cleanInput($parameters->get('zip')),
					'email_company'         => $email,
					'id_phone_code_company' => $phone_code ? $phone_code->getId() : null,
					'phone_code_company'    => $phone_code ? $phone_code->getName() : null,
					'phone_company'         => cleanInput($parameters->get('phone')),
					'address_company'       => cleanInput($parameters->get('address')),
				);

				if (!empty($verification_documents)) {
					$insert_company['accreditation'] = 0;
				}

                /** @var Company_Model $companyModel */
                $companyModel = model(Company_Model::class);

				$id_company = $companyModel->set_company($insert_company);
                $companyModel->set_company_user_rel(array(array('id_company'=>$id_company, 'id_user' => $id_user, 'company_type'=>'company')));

                $parameters->set('industries', new ArrayCollection(
                    array_filter(array_map('intval', (array) $parameters->get('industriesSelected', array())))
                ));
                $parameters->remove('industriesSelected');

				$companyModel->set_relation_industry(
                    $id_company,
                    $parameters->get('industries', new ArrayCollection())->getValues()
                );

				if(!empty($parameters->get('categoriesSelected'))){
					$companyModel->set_relation_category($id_company, $parameters->get('categoriesSelected'));
				}

                // Add default notification settings
                model('users_systmess_settings')->add_default_settings($id_user);

				// Add verification documents
                $this->add_verification_documents($id_user, $id_principal, $verification_documents);

                // Add custom location request if exists
                if ($uses_custom_location) {
                    model(Custom_Locations_Model::class)->insertOne(array(
                        'id_principal' => $id_principal,
                        'location'     => $request->request->get('location'),
                    ));
                }

				//region gmap
				$this->__set_gmap_data($id_company, $id_user, $parameters);
				//endregion gmap

				//region send confirmation email
				$this->send_confirmation_email($id_user);
				//endregion send confirmation email

                // $this->session->clear('secpic');
                $this->session->clear('email_delivarable_status');

                // UPDATE ACTIVITY LOG
                $this->update_user_activity_log($id_user);

				//register additional accounts, current is buyer
				$this->__register_additional_accounts($company_type, $parameters);
                set_verification_session_data($id_user, $packageInfo['gr_to'], $packageInfo['gt_name']);

				$this->__set_seller_popups($id_user);
                // Tumbling down the rabbit hole
                $this->get(MessengerInterface::class)->bus('event.bus')->dispatch(new UserHasRegisteredEvent((int) $id_user));

				$message_success = $this->view->fetch('new/register/message_success_view', array('email' => $this->insert['email']));
                jsonResponse($message_success, 'success', withDebugInformation(array('email' => $this->insert['email']), array(
                    'token'     => $token,
                    'challenge' => $captcha_challenge,
                )));
			break;
			case 'buyer':
				if (logged_in()){
					jsonResponse(translate('systmess_info_already_logged_in'), 'info');
				}
				list($token, $captcha_challenge) = $this->__validate_recaptcha($parameters);

                $this->__process_with_validator(array_merge(
                    $this->__rules_step_1_all($parameters),
                    $this->__rules_step_2_buyer('', $parameters),
                    $this->__rules_step_last_all($request = request()),
                ));

				$this->__validate_all_steps_additional($parameters);

				$this->load->model('User_Model', 'users');
				$this->load->model('Packages_Model', 'packages');
				$this->load->model('Country_model', 'country');

				$email = cleanInput($parameters->get('email'), true);
				$encrypted_email = getEncryptedEmail($email);
				if(model(Auth_Model::class)->exists_hash($encrypted_email)){
					jsonResponse(translate('systmess_error_email_already_registered'));
				}

				$package = $this->packages_types['buyer']['package'];
				$packageInfo = $this->packages->getGrPackage($package);

				if(empty($packageInfo)){
					jsonResponse(translate('systmess_error_sended_data_not_valid'));
				}

				switch($packageInfo['abr']){
					case 'F':
						$paid_until = '0000-00-00';
					break;
					default:
						$end_date = date_plus($packageInfo['days']);
						$paid_until = formatDate($end_date, 'Y-m-d');
					break;
				}

				//region Phone code
				$phone_code = (new PhoneCodesService(model('country')))->findAllMatchingCountryCodes($parameters->getInt('country_code'))->first();
				//endregion Phone code

				$this->insert = array(
					'fname'                  => cleanInput($parameters->get('fname')),
					'lname'                  => cleanInput($parameters->get('lname')),
					'email'                  => $email,
					'email_status'			 => session()->__get('email_delivarable_status') ?? null,
					'user_ip'                => getVisitorIP(),
					'registration_date'      => date('Y-m-d H:i:s'),
					'activation_code'        => get_sha1_token($email),
					'confirmation_token'     => get_sha1_token($email),
					'user_type'              => 'user',
					'user_group'             => $packageInfo['gr_to'],
					'status'                 => 'new',
                    'accreditation_token'    => get_sha1_token($email),
					'paid_until'             => $paid_until,
					'country'                => $request->request->getInt('country'),
					'state'                  => null,
					'city'                   => null,
					'zip'                    => cleanInput($parameters->get('zip')),
					'address'                => cleanInput($parameters->get('address')),
					'paid'                   => 1,
					'phone_code_id'          => $phone_code ? $phone_code->getId() : null,
					'phone_code'             => $phone_code ? $phone_code->getName() : null,
                    'phone'                  => cleanInput($parameters->get('phone')),
                    'user_initial_lang_code' => __SITE_LANG,
                );

                //region REFERRER
                $refferer_info = $this->get_referrer();
                if(!empty($refferer_info)){
                    $this->insert = array_merge($this->insert, $refferer_info);
                }
                //endregion REFERRER

                $uses_custom_location = $request->request->getInt('custom_location') ?: false;
                if (!$uses_custom_location) {
                    $this->insert['state'] = $request->request->getInt('states') ?: null;
                    $this->insert['city'] = $request->request->getInt('port_city') ?: null;
                }

				// Get verification documents
				$verification_documents = $this->collect_verification_documents((int) $packageInfo['gr_to'], $parameters->getInt('country'));

				$this->insert['notice'] = $this->__get_notices('buyer');

				$this->__get_and_update_city_info();

                $id_principal = $this->insert['id_principal'] = $this->__save_hashes($parameters);
				$id_user = $this->users->setUserMain($this->insert);

				if(!$id_user){
					jsonResponse(translate('systmess_error_db_insert_error'));
                }
				array_push($this->id_users, $id_user);

				if((null !== $parameters->get('type_buyer')) && $parameters->getInt('type_buyer') == 1){
					$this->__add_company_for_buyer($id_user, array(
						'company_name' => cleanInput($parameters->get('company_name')),
						'company_legal_name' => cleanInput($parameters->get('company_legal_name'))
					));
				} else {
					//region Update profile completion
					model('complete_profile')->update_user_profile_option($id_user, 'buyer_company');
					//endregion Update profile completion
				}

                // Add default notification settings
                model('users_systmess_settings')->add_default_settings($id_user);

				// Add verification documents
                $this->add_verification_documents($id_user, $id_principal, $verification_documents);

                // Add custom location request if exists
                if ($uses_custom_location) {
                    model(Custom_Locations_Model::class)->insertOne(array(
                        'id_principal' => $id_principal,
                        'location'     => $request->request->get('location'),
                    ));
                }

				//register additional accounts, current is buyer
				$this->__register_additional_accounts('buyer', $parameters);

				//region send confirmation email
				$this->send_confirmation_email($id_user);
				//endregion send confirmation email

				$this->update_user_activity_log($id_user);

				// $this->session->clear('secpic');
                $this->session->clear('email_delivarable_status');
                set_verification_session_data($id_user, $packageInfo['gr_to'], $packageInfo['gt_name']);

                // Tumbling down the rabbit hole
                $this->get(MessengerInterface::class)->bus('event.bus')->dispatch(new UserHasRegisteredEvent((int) $id_user));

                $message_success = $this->view->fetch('new/register/message_success_view', array('email' => $this->insert['email']));
				jsonResponse($message_success, 'success', withDebugInformation(array('email' => $this->insert['email']), array(
                    'token'     => $token,
                    'challenge' => $captcha_challenge,
                )));
			break;
			case 'brand_ambassador' :
				if (logged_in()){
					jsonResponse(translate('systmess_info_already_logged_in'), 'info');
                }

                if(!ajax_validate_google_recaptcha()){
                    jsonResponse(translate('register_recaptcha_error_message'));
                }

				$validator_rules = array(
					array(
						'field' => 'fname',
						'label' => 'First name',
						'rules' => array('required' => '', 'valid_user_name' => '', 'min_len[2]' => '', 'max_len[50]' => '')
					),
					array(
						'field' => 'lname',
						'label' => 'Last name',
						'rules' => array('required' => '', 'valid_user_name' => '', 'min_len[2]' => '', 'max_len[50]' => '')
					),
					array(
						'field' => 'email',
						'label' => 'Email',
						'rules' => array('required' => '','no_whitespaces' => '', 'valid_email' => '')
					),
					array(
						'field' => 'confirm_email',
						'label' => 'Confirm email',
						'rules' => array('required' => '','no_whitespaces' => '', 'valid_email' => '','matches[email]' => '')
					),
					array(
						'field' => 'country',
						'label' => 'Country',
						'rules' => array('required' => '','natural' => '')
					),
					array(
						'field' => 'port_city',
						'label' => 'City',
						'rules' => array('required' => '','natural' => '')
					),
					array(
						'field' => 'zip',
						'label' => 'Zip',
						'rules' => array('zip_code' => '', 'max_len[20]' => '')
					),
				);

				$this->__process_with_validator($validator_rules);

				$this->load->model('User_Model', 'users');
				$this->load->model('Cr_users_Model', 'cr_users');
				$this->load->model('Country_Model', 'country');
				$this->load->model('Auth_Model', 'auth');
				$email = cleanInput($parameters->get('email'), true);

				//region check email status through API
				if(config('env.APP_ENV') === 'dev'){
					$email_status_response = 'Bad';
				}else{
					if(session()->__get('email_check_tries_count_ba') <= config('env.EMAILCHECKER_API_MAX_TRIES'))
					{
						//if same bad email
						if($email == session()->__get('email_to_register_ba')){
							jsonResponse(translate('register_error_undeliverable_email', array('[USER_EMAIL]' => $email)));
						}

						$email_status_response = checkEmailDeliverability($email, true);
						session()->__set('email_check_tries_count_ba', session()->__get('email_check_tries_count_ba')+1);

						if('Bad' == $email_status_response){
							session()->__set('email_to_register_ba', $email);
							jsonResponse(translate('register_error_undeliverable_email', array('[USER_EMAIL]' => $email)));
						}
					}
				}
				//endregion check email status through API

				$encrypted_email = getEncryptedEmail($email);
				if($this->auth->exists_hash($encrypted_email)){
					jsonResponse(translate('systmess_error_email_already_registered'));
				}

				if($this->cr_users->cr_exist_user_request(array('email' => $email))){
					jsonResponse(translate('systmess_error_email_already_registered'));
				}

				$insert = array(
					'applicant_fname' => cleanInput($parameters->get('fname')),
					'applicant_lname' => cleanInput($parameters->get('lname')),
					'applicant_email' => $email,
					'applicant_email_status' => $email_status_response ?? null,
					'applicant_ip' => getVisitorIP(),
					'applicant_status' => 'new',
					'applicant_domains' => $parameters->get('id_domain'),
					'id_country' => $parameters->getInt('country'),
					'id_state' => $parameters->getInt('states'),
					'id_city' => $parameters->getInt('port_city')
				);

				$city_info = $this->country->get_city($insert['id_city']);
				if(!empty($city_info)){
					if($city_info['lat_lng_need_complet'] == 0){
						$this->country->update_city($city_info['id'], array('lat_lng_need_complet' => 1));
					}
				}

				$id_request = $this->cr_users->cr_set_user_request($insert);

				if(!$id_request){
					jsonResponse(translate('systmess_error_db_insert_error'));
				}

				//email preparation
                try {
                    /** @var MailerInterface $mailer */
                    $mailer = $this->getContainer()->get(MailerInterface::class);
                    $mailer->send(
                        (new RegisterBrandAmbassador())
                            ->from(config('epcountryambassador_email'))
                            ->to(new Address($insert['applicant_email']))
                    );
                } catch (\Throwable $th) {
                    jsonResponse(translate('email_has_not_been_sent'));
                }

				jsonResponse(translate('systmess_success_ba_submit_request'), 'success');
			break;
			case 'validate_add_accounts':
				if (!logged_in()){
					jsonResponse(translate('register_add_another_account_logged_in'), 'info');
				}
				$step = $this->uri->segment(4);

				switch($step){
					case 'validate_step_1':
						$type_another_account = cleanInput($parameters->get('type_another_account'));
						$this->__validate_types_of_account($type_another_account);
						$this->__validate_user_type_exist($type_another_account);
					break;
					case 'validate_step_2_buyer':
						$suffix = '_additional_buyer';
						$validator_rules = $this->__rules_step_2_buyer($suffix, $parameters);
					break;
					case 'validate_step_2_seller':
						$suffix = '_additional_seller';
						$this->__validate_max_industries($suffix, $parameters);
						$this->__validate_industries($suffix, $parameters);
						$validator_rules = $this->__rules_another_seller('seller');
					break;
					case 'validate_step_2_manufacturer':
						$suffix = '_additional_manufacturer';
						$this->__validate_max_industries($suffix, $parameters);
						$this->__validate_industries($suffix, $parameters);
						$validator_rules = $this->__rules_another_seller('manufacturer');
					break;
				}

				$this->__process_with_validator($validator_rules);

				jsonResponse('','success');
			break;
			case 'save_another_account':
				if (!logged_in()){
					jsonResponse(translate('login_only_logged_in_add_accout_message'), 'info');
				}

				$this->adding_additional_accounts = true;

				$this->__validate_all_steps_additional($parameters);
				$this->__validate_user_type_exist(cleanInput($parameters->get('type_another_account')));

                /** @var User_Model $userModel */
                $userModel = model(User_Model::class);

                // check if the new account requires to copy personal information from another account
				if (null !== $parameters->get('copy_personal_info')) {
                    if (!$this->validator->integer($parameters->get('copy_personal_info'))) {
                        jsonResponse(translate('systmess_error_invalid_data'));
                    }

                    // check if the user has an associated account with this id
                    if (empty($personalInfoSourceAccount = $userModel->get_user_by_condition([
                        'status_is_not' => ['deleted'],
                        'id_principal'  => principal_id(),
                        'id_user'       => $parameters->get('copy_personal_info'),
                    ]))) {
                        jsonResponse(translate('systmess_error_invalid_data'));
                    }

                    // check if the account from which we plan to copy personal information has previously been filled in
                    // if the user has the active status, we assume that the profile has been completely filled in
                    if ('active' !== $personalInfoSourceAccount['status']) {
                        /** @var Complete_Profile_Model $completeProfileModel */
                        $completeProfileModel = model(Complete_Profile_Model::class);
                        $profileCompletion = array_column($completeProfileModel->get_user_profile_options($personalInfoSourceAccount['idu']), null, 'option_alias');

                        if (! (int) $profileCompletion['account_preferences']['option_completed']) {
                            jsonResponse(translate('systmess_error_invalid_data'));
                        }
                    }
                }

                // check if the new account requires to copy company information from another account
                // do not forget that you can only copy company information to an account of the seller type from another account of the seller type
                if (null !== $parameters->get('copy_company_info') && in_array($parameters->get('type_another_account'), ['all', 'seller', 'manufacturer'])) {
                    // In order not to duplicate the validation, we perform it only if the account from which we will copy the company information differs from the one from which we will copy personal information
                    if ($parameters->get('copy_personal_info') !== $parameters->get('copy_company_info')) {
                        if (!$this->validator->integer($parameters->get('copy_company_info'))) {
                            jsonResponse(translate('systmess_error_invalid_data'));
                        }

                        // check if the user has an associated account with this id
                        if (empty($companyInfoSourceAccount = $userModel->get_user_by_condition([
                            'status_is_not' => ['deleted'],
                            'id_principal'  => principal_id(),
                            'user_group'    => [2, 3, 5, 6],
                            'id_user'       => $parameters->get('copy_company_info'),
                        ]))) {
                            jsonResponse(translate('systmess_error_invalid_data'));
                        }

                        // check if the account from which we plan to copy company information has previously been filled in
                        // if the user has the active status, we assume that the profile has been completely filled in
                        if ('active' !== $companyInfoSourceAccount['status']) {
                            /** @var Complete_Profile_Model $completeProfileModel */
                            $completeProfileModel = $completeProfileModel ?? model(Complete_Profile_Model::class);
                            $profileCompletion = array_column($completeProfileModel->get_user_profile_options($companyInfoSourceAccount['idu']), null, 'option_alias');

                            if (! (int) $profileCompletion['company_main']['option_completed']) {
                                jsonResponse(translate('systmess_error_invalid_data'));
                            }
                        }
                    } elseif ('active' !== $personalInfoSourceAccount['status']) {
                        if (! (int) $profileCompletion['company_main']['option_completed']) {
                            jsonResponse(translate('systmess_error_invalid_data'));
                        }
                    }

                    /** @var Company_Model $companyModel */
                    $companyModel = model(Company_Model::class);

                    $this->companyInsert = $companyModel->getSimpleCompanyByIdUser(
                        $parameters->get('copy_company_info'),
                        'id_company, fax_company, fax_code_company, id_fax_code_company, logo_company, employees_company, revenue_company, description_company, video_company,
                        video_company_code, video_company_source, video_company_image',
                    );

                    $this->sourceCompanyId = (int) $this->companyInsert['id_company'];
                    unset($this->companyInsert['id_company']);
                }

                $this->load->model('User_Model', 'users');
				$this->load->model('Packages_Model', 'packages');
				$this->load->model('Country_model', 'country');

				$current_user_id = session()->__get('id');
				$this->insert = $userModel->getUserForAddAccounts($parameters->get('copy_personal_info') ?? $current_user_id);

				$this->insert = array_merge($this->insert, array(
					'status'				 => 'pending',
                    'email_confirmed'        => '1',
					'paid_until'			 => '0000-00-00',
					'paid'                   => 1,
					'registration_date'      => date('Y-m-d H:i:s'),
					'confirmation_token'     => get_sha1_token($this->insert['email']),
				    'accreditation_token'    => get_sha1_token($this->insert['email']),
				));

				$this->__register_additional_accounts(returnTrueUserGroupName(), $parameters);

                if (null !== $parameters->get('copy_personal_info')) {
                    /** @var Complete_Profile_Model $completeProfileModel */
                    $completeProfileModel = model(Complete_Profile_Model::class);

                    foreach ($this->id_users as $userId) {
                        $completeProfileModel->update_user_profile_option($userId, 'account_preferences');
                    }
                }

				$this->load->library('Auth', 'auth_lib');
				$this->auth_lib->set_accounts_in_session($current_user_id);

				jsonResponse('', 'success');
			break;
		}
	}

    private function update_user_activity_log($id_user)
    {
        if (empty($id_user) || empty($user = model('user')->getSimpleUser((int) $id_user))) {
            jsonResponse(translate('systmess_error_user_does_not_exist'));
        }

        $fullname = trim("{$user['fname']} {$user['lname']}");
        $url = getUserLink($fullname, $id_user, $user['gr_type']);
        $rel_ulr = substr($url, strlen(__SITE_URL));

        $operation = empty($url) ? REGISTRATION_NO_PROFILE : REGISTRATION;
        $context = array(
            'user' => array(
                'name'     => $fullname,
                'email'    => $user['email'],
                'group'    => array(
                    'id'   => $user['user_group'],
                    'type' => $user['user_type'],
                    'name' => $user['gr_name'],
                ),
                'profile'  => array(
                    'url'    => $url,
                    'relUrl' => $rel_ulr,
                )
            )
        );
        $this->activity_logger->setOperationType(REGISTRATION);
        $this->activity_logger->setResourceType(USER);
        $this->activity_logger->setResource($id_user);
        $this->activity_logger->setInitiator($id_user);
        $this->activity_logger->info(model('activity_log_messages')->get_message(USER, $operation), $context);
    }

	private function send_confirmation_email($user_id)
	{
		//region User access
        if (empty($user_id) || empty($user = model('user')->getSimpleUser((int) $user_id))) {
            jsonResponse(translate('systmess_error_user_does_not_exist'));
        }
        //endregion User access

        /** @var MailerInterface $mailer */
        $mailer = $this->getContainer()->get(MailerInterface::class);

        try {
            if (__CURRENT_SUB_DOMAIN === getSubDomains()['shippers']) {
                $mailer->send(
                    (new EplConfirmEmail("{$user['fname']} {$user['lname']}", $user['confirmation_token']))
                        ->to(new RefAddress((string) $user_id, new Address($user['email'])))
                );
            } else {
                $mailer->send(
                    (new ConfirmEmail("{$user['fname']} {$user['lname']}", $user['confirmation_token']))
                        ->to(new RefAddress((string) $user_id, new Address($user['email'])))
                );
            }
        } catch (\Throwable $th) {
            jsonResponse(translate('email_has_not_been_sent'));
        }
	}

	function popup_forms() {
		if (!isAjaxRequest()){
			headerRedirect();
		}

		$op = $this->uri->segment(3);
		switch ($op) {
			case 'add_another_account':
				if (!logged_in()) {
					messageInModal(translate('login_only_logged_in_add_accout_message'));
				}

				if (!checkRightSwitchGroup()) {
					messageInModal(translate('login_cannot_add_another_account_message'));
				}

                $accounts = $this->__get_additional_accounts();

				if (count($accounts) >= 3) {
					messageInModal(translate('login_already_all_types_of_accounts_message'));
				}

                $accounts = array_column($accounts, null, 'idu');

                /** @var Complete_Profile_Model $completeProfileModel */
                $completeProfileModel = model(Complete_Profile_Model::class);
                $profileCompletion = $completeProfileModel->get_users_profile_options(array_column($accounts, 'idu'));

                $canCopyInformationFrom = [];
                $canCopyPersonalInfo = $canCopyCompanyInfo = false;
                $hasSellerAccount = $hasManufacturerAccount = false;
                foreach ($profileCompletion as $userId => $profileCompletionPerAccount) {
                    $profileCompletionPerAccount = array_column($profileCompletionPerAccount, null, 'option_alias');

                    $copyInformationLabel = translate('add_another_account_copy_info_from_buyer_account_option');
                    if (is_verified_seller($accounts[$userId]['user_group']) || is_certified_seller($accounts[$userId]['user_group'])) {
                        $hasSellerAccount = true;
                        $copyInformationLabel = translate('add_another_account_copy_info_from_seller_account_option');
                    } elseif (is_verified_manufacturer($accounts[$userId]['user_group']) || is_certified_manufacturer($accounts[$userId]['user_group'])) {
                        $hasManufacturerAccount = true;
                        $copyInformationLabel = translate('add_another_account_copy_info_from_manufacturer_account_option');
                    }

                    if ($profileCompletionPerAccount['account_preferences']['option_completed'] ?? null) {
                        $canCopyPersonalInfo = true;
                        $canCopyInformationFrom['account_preferences'][$userId] = $copyInformationLabel;
                    }

                    if ($profileCompletionPerAccount['company_main']['option_completed'] ?? null) {
                        $canCopyCompanyInfo = true;
                        $canCopyInformationFrom['company_main'][$userId] = $copyInformationLabel;
                    }
                }

                // if the user already has an account of the seller and the manufacturer, then only the buyer's account remains, to which the company information cannot be copied
                if ($hasSellerAccount && $hasManufacturerAccount) {
                    $canCopyCompanyInfo = false;
                }

                /** @var Elasticsearch_Category_Model $elasticsearchCategoryModel */
                $elasticsearchCategoryModel = model(Elasticsearch_Category_Model::class);
				$elasticsearchCategoryModel->get_categories(['parent' => [0], 'has_children' => [true], 'sort_by' => 'name_asc']);

                views()->assign([
                    'canCopyPersonalInfo'       => $canCopyPersonalInfo,
                    'canCopyCompanyInfo'        => $canCopyCompanyInfo,
                    'canCopyInformationFrom'    => $canCopyInformationFrom,
                    'industries'                => $elasticsearchCategoryModel->categories_records,
                    'multipleselect_industries' => [
                        'categories_selected_by_id' => [],
                        'industries_selected'       => [],
                        'max_industries'            => (int) config('multipleselect_max_industries', 3),
                        'industries'                => $elasticsearchCategoryModel->categories_records,
                        'categories'                => [],
                        'dispatchDynamicFragment'   => true,
                    ],
                    'existing_accounts'         =>  array_map(function ($account) {
                        return returnTrueUserGroupName($account['user_group'], $account['gr_type']);
                    }, $accounts)
                ]);

                views()->display('new/register/popup_register_form_view');
			break;
			case 'brand_ambassador':
				if(logged_in() && user_group_type() == 'CR Affiliate'){
					messageInModal(translate('systmess_info_already_logged_in'), 'info');
				}

				$this->load->model("Country_model", 'country');
				$data = array(
					'port_country' => $this->country->fetch_port_country(),
					'id_domain' => (int) $this->uri->segment(4)
				);

				$this->view->assign($data);
				$this->view->display('new/cr/popup_register_view');
			break;
			case 'resend_confirmation_email':

				if(logged_in()){
					messageInModal(translate('systmess_info_already_logged_in'), 'info');
				}

				if(!empty($_GET['email'])){
					$data['email'] = cleanInput($_GET['email'], true);
					$this->view->assign($data);
				}

                if (__CURRENT_SUB_DOMAIN === getSubDomains()['shippers']) {
                    views()->display('new/epl/register/resend_registration_email_view');
                } else {
                    views()->display('new/register/resend_registration_email_view');
                }
			break;
		}
	}

	public function confirm_email()
	{
		if (logged_in()){
			headerRedirect(__CURRENT_SUB_DOMAIN_URL);
		}

		$this->load->model('User_Model', 'users');
		$token = cleanInput($this->uri->segment(3));
		if(empty($token)){
			show_404();
		}

		$users = model('user')->get_users_by_confirm_token($token);
		if (empty($users)){
			show_404();
		}

		if (!in_array($users[0]['gr_type'], ['Buyer', 'Seller', 'Shipper']) || $users[0]['email_confirmed']) {
			$this->session->setMessages(translate('register_already_accessed_confirmation_link'), 'info');
			headerRedirect(__CURRENT_SUB_DOMAIN_URL . 'login');
		}

		foreach($users as $user){
			confirmUserAccount((int) $user['idu'], $user);
		}

        $user = array_shift($users);

        /** @var MailerInterface $mailer */
        $mailer = $this->getContainer()->get(MailerInterface::class);
        $mailer->send(
            (new WelcomeToExportPortal("{$user['fname']} {$user['lname']}"))
                ->to(new RefAddress((string) $user['idu'], new Address($user['email'])))
        );

		$this->session->setMessages(translate('register_address_confirmed_message'), 'success');
		headerRedirect(__CURRENT_SUB_DOMAIN_URL . 'login');
	}

	private function collect_verification_documents($group_id, $country_id)
	{
        /** @var Verification_Document_Types_Model */
        $varificationTypes = model(Verification_Document_Types_Model::class);
        $exists = [];
        if (!empty($group_id)) {
            $exists[] = $varificationTypes
                ->getRelationsRuleBuilder()
                ->whereHas('groupsReference', function (QueryBuilder $builder, RelationInterface $relation) use ($group_id) {
                    $related = $relation->getRelated();
                    $related->getScope('userGroup')($builder, (int) $group_id);
                    $related->getScope('isRequired')($builder, true);
                })
            ;
        }
        if (!empty($country_id)) {
            $exists[] = $varificationTypes
                ->getRelationsRuleBuilder()
                ->whereHas('countriesReference', function (QueryBuilder $builder, RelationInterface $relation) use ($country_id) {
                    $relation->getRelated()->getScope('country')($builder, (int) $country_id);
                })
            ;
        }

		return array_filter(array_map(
			function ($type) {
				if (empty($type_id = (int) arrayGet($type, 'id_document'))) {
					return null;
				}

				return $type_id;
			},
            array_filter($varificationTypes->findAllBy([
                'exists' => $exists,
                'scopes' => [
                    'hasAdditionalOptions' => false
                ],
            ])),
		));
    }

	private function add_verification_documents($user_id, ?int $id_principal, array $verification_documents = array())
	{
		$documents = array_map(
			function ($document_id) use ($user_id, $id_principal) {
				return array(
					'id_type'      => (int) $document_id,
					'id_user'      => (int) $user_id,
					'id_principal' => $id_principal,
				);
			},
			$verification_documents
		);

		if (!empty($documents)) {
			model('user_personal_documents')->create_documents($documents);
		}
	}

	private function __save_hashes($parameters)
	{
		$hash_insert = array(
			'token_email' 	 => getEncryptedEmail($this->insert['email']),
			'token_password' => getEncryptedPassword($parameters->get('password'))
		);

		$id_principal = model('principals')->insert_last_id();
		model(Auth_Model::class)->add_hash($id_principal, $hash_insert);

		return $id_principal;
	}

	private function __register_additional_accounts($current_type, $parameters)
	{
		if( null == $parameters->get('type_another_account')){
			return;
		}
		$type_account = cleanInput($parameters->get('type_another_account'));

		if($type_account == $current_type){
			jsonResponse(translate('register_cannot_register_twice_as_same', array('[COMPANY_TYPE]' => '\"$type_account\"')));
		}

		if(in_array($type_account, array('buyer', 'manufacturer', 'seller'))){
			$this->{'__register_additional_' . $type_account}($parameters);
		}elseif($type_account == 'all'){

			switch(strtolower($current_type)){
				case 'buyer':
					$this->__register_additional_manufacturer($parameters);
					$this->__register_additional_seller($parameters);
				break;
				case 'manufacturer':
					$this->__register_additional_buyer($parameters);
					$this->__register_additional_seller($parameters);
                break;
                case 'distributor':
				case 'seller':
					$this->__register_additional_buyer($parameters);
					$this->__register_additional_manufacturer($parameters);
				break;
			}

		}else{
			jsonResponse(translate('register_error_cannot_register_as', array('[COMPANY_TYPE]' => $type_account)));
		}

	}

	private function __register_additional_buyer($parameters)
	{
		//set another group type
		$packageInfo = $this->packages->getGrPackage($this->packages_types['buyer']['package']);
		$this->insert['user_group'] = (int) $packageInfo['gr_to'];
		$this->insert['activation_code'] = get_sha1_token($this->insert['email']);

        $id_user = $this->users->setUserMain($this->insert);
        $id_principal = (int) $this->insert['id_principal'] ?: null;
		array_push($this->id_users, $id_user);

		//region add company optional to buyer
		if(( null !== $parameters->get('type_buyer_additional_buyer') ) && $parameters->get('type_buyer_additional_buyer') == 1)
		{
			$this->__add_company_for_buyer($id_user, array(
				'company_name' => cleanInput($parameters->get('company_name_additional_buyer')),
				'company_legal_name' => cleanInput($parameters->get('company_legal_name_additional_buyer'))
			));
		} else{
			model('complete_profile')->update_user_profile_option($id_user, 'buyer_company');
		}
		//endregion add company optional to buyer

		// Get verification documents
		$verification_documents = $this->collect_verification_documents($this->insert['user_group'], $this->insert['country']);

		// Add verification documents
		$this->add_verification_documents($id_user, $id_principal, $verification_documents);

		// Add default notification settings
		model('users_systmess_settings')->add_default_settings($id_user);

		//add aditional user activity log
		$this->update_user_activity_log($id_user);

        // Tumbling down the rabbit hole
        $this->get(MessengerInterface::class)->bus('event.bus')->dispatch(new UserHasRegisteredEvent((int) $id_user));
	}

	private function __register_additional_manufacturer($parameters)
	{
		$this->__register_additional_seller_main('manufacturer', $parameters);
	}

	private function __register_additional_seller($parameters)
	{
		$this->__register_additional_seller_main('seller', $parameters);
	}

	private function __register_additional_seller_main($type_seller, $parameters)
	{
		//set another group type
		$packageInfo = $this->packages->getGrPackage($this->packages_types[$type_seller]['package']);
		$this->insert['user_group'] = (int) $packageInfo['gr_to'];
		$this->insert['activation_code'] = get_sha1_token($this->insert['email']);

        $id_user = $this->users->setUserMain($this->insert);
        $id_principal = (int) $this->insert['id_principal'] ?: null;
		array_push($this->id_users, $id_user);

		// Get verification documents
		$verification_documents = $this->collect_verification_documents((int) $packageInfo['gr_to'], $this->insert['country']);

		$id_type = $this->packages_types['seller']['id_type'];

		$insert_company = array(
			'id_user'               => $id_user,
			'id_type'               => $id_type,
			'name_company'          => cleanInput($parameters->get('company_name_additional_' . $type_seller)),
			'legal_name_company'    => cleanInput($parameters->get('company_legal_name_additional_' . $type_seller)),
			'id_country'            => $this->insert['country'],
			'id_city'               => $this->insert['city'],
			'id_state'           	=> $this->insert['state'],
			'zip_company'           => $this->insert['zip'],
			'address_company'       => $this->insert['address'],
			'email_company'         => $this->insert['email'],
			'id_phone_code_company' => $this->insert['phone_code_id'],
			'phone_code_company'    => $this->insert['phone_code'],
			'phone_company'         => $this->insert['phone'],
		);

		if (!empty($verification_documents)) {
			$insert_company['accreditation'] = 0;
		}

        $insert_company = array_merge($insert_company, $this->companyInsert ?: []);

        /** @var Company_Model $companyModel */
        $companyModel = model(Company_Model::class);

		$id_company = $companyModel->set_company($insert_company);
        $companyModel->set_company_user_rel(array(array('id_company'=>$id_company, 'id_user' => $id_user, 'company_type'=>'company')));

        //Check whether we need to copy the industries from the previously added account or whether we need to save the industries that were selected now
        if (null === $this->companyInsert) {
            $nameIndustriesSelectedAdditional = 'industriesSelected_additional_' . $type_seller;
            $parameters->set($nameIndustriesSelectedAdditional, new ArrayCollection(
                array_filter(array_map('intval', (array) $parameters->get($nameIndustriesSelectedAdditional, [])))
            ));

            $companyModel->set_relation_industry(
                $id_company,
                $parameters->get($nameIndustriesSelectedAdditional, new ArrayCollection())->getValues()
            );

            $nameCategoriesSelectedAdditional = "categoriesSelected_additional_{$type_seller}";
            if(!empty($parameters->get($nameCategoriesSelectedAdditional))){
                $companyModel->set_relation_category($id_company, $parameters->get($nameCategoriesSelectedAdditional));
            }
        } else {
            $sourceIndustries = $companyModel->get_relation_industry_by_company_id($this->sourceCompanyId);
            if (!empty($sourceIndustries)) {
                $companyModel->set_relation_industry($id_company, array_column($sourceIndustries, 'id_industry'));
            }

            $sourceCategories = $companyModel->get_relation_category_by_company_id($this->sourceCompanyId);
            if (!empty($sourceCategories)) {
                $companyModel->set_relation_category($id_company, array_column($sourceCategories, 'id_category'));
            }
        }

		// Add default notification settings
		model('users_systmess_settings')->add_default_settings($id_user);

		// Add verification documents
		$this->add_verification_documents($id_user, $id_principal, $verification_documents);

		//region gmap
		if($this->adding_additional_accounts){
			$this->__set_gmap_data($id_company, $id_user, $parameters);
		}
		//endregion gmap

		// UPDATE ACTIVITY LOG
		$this->update_user_activity_log($id_user);
        $this->__set_seller_popups($id_user);

        if (!empty($this->companyInsert)) {
            /** @var FilesystemProviderInterface */
            $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
            $storage = $storageProvider->storage('public.storage');
            $newCompanyPath = "/{$id_company}";
            $sourceCompanyPath = "/{$this->sourceCompanyId}";

            $storage->copy(CompanyLogoFilePathGenerator::logoPath($this->sourceCompanyId, $this->companyInsert['logo_company']), CompanyLogoFilePathGenerator::logoPath($id_company, $this->companyInsert['logo_company']));

            $companyLogoThumbs = config('img.companies.main.thumbs');
            foreach ($companyLogoThumbs ?: [] as $thumb) {
                $thumbImageName = str_replace('{THUMB_NAME}', $this->companyInsert['logo_company'], $thumb['name']);
                $storage->copy(CompanyLogoFilePathGenerator::logoPath($this->sourceCompanyId, $thumbImageName), CompanyLogoFilePathGenerator::logoPath($id_company, $thumbImageName));
            }

            if (!empty($this->companyInsert['video_company_image'])) {
                $storage->copy(CompanyVideoFilePathGenerator::videoPath($this->sourceCompanyId, $this->companyInsert['video_company_image']), CompanyVideoFilePathGenerator::videoPath($newCompanyPath, $this->companyInsert['video_company_image']));
            }

            /** @var Complete_Profile_Model $completeProfileModel */
            $completeProfileModel = model(Complete_Profile_Model::class);

            $completeProfileModel->update_user_profile_option($id_user, 'company_main');
        }

        // Tumbling down the rabbit hole
        $this->get(MessengerInterface::class)->bus('event.bus')->dispatch(new UserHasRegisteredEvent((int) $id_user));

		return;
	}

	private function __add_company_for_buyer($id_user, $company_names)
	{
		$company_insert = array(
			'id_user'               => $id_user,
			'company_name'          => $company_names['company_name'],
			'company_legal_name'    => $company_names['company_legal_name'],
			'company_id_country'    => $this->insert['country'],
			'company_id_state'      => $this->insert['state'],
			'company_id_city'       => $this->insert['city'],
			'company_address'       => $this->insert['address'],
			'company_zip'           => $this->insert['zip'],
			'company_phone_code_id' => $this->insert['phone_code_id'],
			'company_phone_code'    => $this->insert['phone_code'],
			'company_phone'         => $this->insert['phone']
		);

		$this->load->model('Company_Buyer_Model', 'company_buyer');
		$this->company_buyer->set_company($company_insert);
	}

	private function __get_notices($email_group_name)
	{
		$notices[] = json_encode(array(
			'add_date' => date('Y/m/d H:i:s'),
			'add_by'   => 'System',
			'notice'   => 'Account registered as '.$email_group_name.'.'
		));
		$notices[] = json_encode(array(
			'add_date' => date('Y/m/d H:i:s'),
			'add_by'   => 'System',
			'notice'   => 'The confirmation email has been sent.'
		));
		return implode(',', $notices);
	}

	private function __get_and_update_city_info()
	{
		$city_info = $this->country->get_city($this->insert['city']);
		if(!empty($city_info)){
			if($city_info['lat_lng_need_complet'] == 0){
				$this->country->update_city($city_info['id'], array('lat_lng_need_complet' => 1));
			}

			if($city_info['lat_lng_need_complet'] == 2){
				$this->insert['user_city_lat'] = $city_info['city_lat'];
				$this->insert['user_city_lng'] = $city_info['city_lng'];
			}
		}
	}

	private function __set_gmap_data($id_company, $id_user, $parameters){

		if(empty($this->gmap_geodata)){
			$location_data = $this->country->get_country_state_city($parameters->getInt('port_city'));
			$this->load->library('Gmap', 'gmap');
			$gmap_config = array(
				'address' => cleanInput($parameters->get('address')),
				'zip' => cleanInput($parameters->get('zip')),
				'country' => $location_data['country'],
				'state' => $location_data['state'],
				'city' => $location_data['city']
			);
			$this->gmap_geodata = $this->gmap->get_geocode($gmap_config);
		}

		if($this->gmap_geodata['status'] === 'OK'){
			$update_company = array(
				'latitude' => $this->gmap_geodata['results'][0]['geometry']['location']['lat'],
				'longitude' => $this->gmap_geodata['results'][0]['geometry']['location']['lng']
			);

            /** @var Company_Model $companyModel */
            $companyModel = model(Company_Model::class);

			$companyModel->update_company($id_company, $update_company);
		} else{
			$notice = array(
				'add_date' => date('Y/m/d H:i:s'),
				'add_by' => 'System',
				'notice' => 'Could not retrieve Company Geo data from Google.'
			);
			$this->users->set_notice($id_user, $notice);
		}
    }

	private function __set_seller_popups($id_user){

        $insertPopup = [
            'id_user'	=> $id_user,
            'id_popup'	=> 24,
            'is_viewed' => 0,
            'add_date'  => new DateTimeImmutable(date('Y-m-d H:i:s')),
        ];

        /** @var User_Popups_Model $popupUsers */
        $popupUsers = model(User_Popups_Model::class);
        $popupUsers->insertOne($insertPopup);
    }
}
