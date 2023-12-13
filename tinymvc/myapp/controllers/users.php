<?php

use App\Common\Buttons\ChatButton;
use App\Common\Contracts\Cancel\CancellationRequestStatus;
use App\Common\Contracts\Email\EmailTemplate;
use App\Common\Contracts\Group\GroupType;
use App\Common\Contracts\User\RestrictionType;
use App\Common\Contracts\User\UserStatus;
use App\Common\Contracts\User\UserType;
use App\Common\Validation\ConstraintViolation;
use App\Common\Validation\Legacy\ValidatorAdapter;
use App\Common\Validation\Standalone\AggregateValidator;
use App\DataProvider\User\UserDataListProvider;
use App\Email\ActivateAccount;
use App\Email\GroupEmailTemplates;
use App\Email\RestoreAccountEmail;
use App\Email\StartUsingEpAgain;
use App\Filesystem\CompanyLogoFilePathGenerator;
use App\Filesystem\ItemPathGenerator;
use App\Filesystem\UserPhotoPathGenerator;
use App\Messenger\Message\Event\Lifecycle\UserWasBlockedEvent;
use App\Messenger\Message\Event\Lifecycle\UserWasDeletedEvent;
use App\Messenger\Message\Event\Lifecycle\UserWasMarkedFakeEvent;
use App\Messenger\Message\Event\Lifecycle\UserWasMarkedRealEvent;
use App\Messenger\Message\Event\Lifecycle\UserWasRestoredEvent;
use App\Messenger\Message\Event\Lifecycle\UserWasRestrictedEvent;
use App\Messenger\Message\Event\Lifecycle\UserWasUnblockedEvent;
use App\Messenger\Message\Event\Lifecycle\UserWasUnrestrictedEvent;
use App\Services\MatchmakingService;
use App\Validators\RestoreCompanyNameValidator;
use App\Validators\UserRestoreDataValidator;
use ExportPortal\Bridge\Mailer\Mime\RefAddress;
use ExportPortal\Bridge\Symfony\Component\Messenger\MessengerInterface;
use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\BodyRendererInterface;

use const App\Common\DB_DATE_FORMAT;
use const App\Common\PUBLIC_DATETIME_FORMAT;

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [18.12.2021]
 * Controller Refactoring
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
 * @author
 */
class Users_Controller extends TinyMVC_Controller
{
    private const SURVEY_POPUP_ONBOARDING = 9;
    private $statuses = [
        'new' => [
            'name'  => 'New',
            'label' => '<span class="label label-info">New</span>'
        ],
        'pending' => [
            'name'  => 'Pending',
            'label' => '<span class="label label-warning">Pending</span>'
        ],
        'active' => [
            'name'  => 'Activated',
            'label' => '<span class="label label-success">Activated</span>'
        ],
        'restricted' => [
            'name'  => 'Restricted',
            'label' => '<span class="label label-default">Restricted</span>'
        ],
        'banned' => [
            'name'  => 'Banned',
            'label' => '<span class="label label-danger">Banned</span>'
        ],
        'deleted' => [
            'name'  => 'Deleted',
            'label' => '<span class="label label-danger">Deleted</span>'
        ],
        'blocked' => [
            'name'  => 'Blocked',
            'label' => '<span class="label label-danger">Blocked</span>'
        ],
    ];

    private UserDataListProvider $userDataListProvider;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->userDataListProvider = $container->get(UserDataListProvider::class);
    }

    public function reason_messages()
    {
        checkAdmin('notification_messages_administration');

        $this->load->model('User_Model', 'users');

        $this->view->assign('title', 'Reason messages');

        $this->view->display('admin/header_view');
        $this->view->display('admin/user/notification_messages/index_view');
        $this->view->display('admin/footer_view');
    }

    public function notification_messages_dt()
    {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        if (!logged_in()) {
            jsonDTResponse(translate("systmess_error_should_be_logged_in"));
        }

        if (!have_right('manage_content')) {
            jsonDTResponse(translate("systmess_error_rights_perform_this_action"));
        }

        $this->load->model('User_Model', 'users');

        $request = request()->request;

        $params = [
            'per_p' => $request->getInt('iDisplayLength'),
            'start' => $request->getInt('iDisplayStart'),
        ];

        $iSortingCols = $request->getInt('iSortingCols');
        if (0 < $iSortingCols) {
            $iSortCol_0 = $request->getInt('iSortCol_0');
			for ($i = 0; $i < $iSortingCols; $i++) {
				switch ($_POST["mDataProp_" . $iSortCol_0]) {
					case 'dt_id':
                        $params['sort_by'][] = 'id_message-' . $request->get("sSortDir_{$i}");
                    break;
					case 'dt_title':
                        $params['sort_by'][] = 'message_title-' . $request->get("sSortDir_{$i}");
                    break;
					case 'dt_module':
                        $params['sort_by'][] = 'message_module-' . $request->get("sSortDir_{$i}");
                    break;
				}
			}
		}

        if ($request->has('message_module')) {
            $params['message_module'] = cleanInput($request->get('message_module'));
        }

        $records = $this->users->get_notification_messages($params);
        $total_records = $this->users->count_notification_messages($params);

        $output = [
            'sEcho'                => $request->getInt('sEcho'),
            'iTotalRecords'        => $total_records,
            'iTotalDisplayRecords' => $total_records,
            'aaData'               => [],
        ];


		if (empty($records)) {
            jsonResponse('', 'success', $output);
        }

        $message_groups = [
            'accreditation' => 'Accreditation',
            'billing'       => 'Billing',
        ];

        foreach ($records as $record) {
            $output['aaData'][] = [
                'dt_id'          => $record['id_message'],
                'dt_title'       => $record['message_title'],
                'dt_description' => $record['message_text'],
                'dt_module'      => $message_groups[$record['message_module']],
                'dt_actions'     => '<a href="' . __SITE_URL . '" class="ep-icon ep-icon_remove txt-red confirm-dialog" title="Remove message"
                    data-message="Are you sure you want to remove this message?"
                    data-callback="delete_message" data-message-slug="' . $record['id_message'] . '"></a>
                    <a class="ep-icon ep-icon_pencil fancyboxValidateModalDT fancybox.ajax" title="Edit message" href="' . __SITE_URL . 'users/popup_forms/edit_notification_message/' . $record['id_message'] . '" data-title="Edit message"></a>',
            ];
        }

        jsonResponse('', 'success', $output);
    }

    public function calling_statuses()
    {
        checkAdmin('calling_statuses_administration');

        $this->load->model('User_Model', 'users');

        $this->view->assign(
            ['title' => 'Calling statuses']
        );

        $this->view->display('admin/header_view');
        $this->view->display('admin/user/calling_statuses/index_view');
        $this->view->display('admin/footer_view');
    }

    public function calling_statuses_dt()
    {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        if (!logged_in()) {
            jsonDTResponse(translate("systmess_error_should_be_logged_in"));
        }

        if (!have_right('manage_content')) {
            jsonDTResponse(translate("systmess_error_rights_perform_this_action"));
        }

        $this->load->model('User_Model', 'users');

        $params = array(
            'per_p' => intVal($_POST['iDisplayLength']),
            'start' => intVal($_POST['iDisplayStart'])
        );

        if ($_POST['iSortingCols'] > 0) {
            for ($i = 0; $i < $_POST['iSortingCols']; $i++) {
                switch ($_POST['iSortCol_' . $i]) {
                    default:
                    case '1':
                        $params['sort_by'][] = 'status_title-' . $_POST['sSortDir_' . $i];
                    break;
                    case '2':
                        $params['sort_by'][] = 'status_color-' . $_POST['sSortDir_' . $i];
                    break;
                }
            }
        }

        $records = $this->users->get_calling_statuses($params);
        $total_records = $this->users->count_calling_statuses($params);

        $output = array(
            "sEcho" => intval($_POST['sEcho']),
            "iTotalRecords" => $total_records,
            "iTotalDisplayRecords" => $total_records,
            "aaData" => array()
        );

		if(empty($records))
			jsonResponse('', 'success', $output);

        foreach ($records as $record) {
            $output['aaData'][] = array(
                "dt_id" => $record['id_status'],
                "dt_title" => $record['status_title'],
                "dt_description" => $record['status_description'],
                "dt_color" => '<i class="ep-icon ep-icon_support fs-30" style="color:'.$record['status_color'].';" title="'.$record['status_title'].'"></i>',
                "dt_actions" => '<a href="'.__SITE_URL.'" class="ep-icon ep-icon_remove txt-red confirm-dialog" title="Remove status" data-message="Are you sure you want to remove this status?" data-callback="delete_status" data-status="'.$record['id_status'].'"></a>
                                <a class="ep-icon ep-icon_pencil fancyboxValidateModalDT fancybox.ajax" title="Edit status" href="'.__SITE_URL.'users/popup_forms/edit_calling_status/' . $record['id_status'] . '" data-title="Edit status"></a>'
            );
        }

        jsonResponse('', 'success', $output);
    }

    public function popup_forms()
    {
        checkIsAjax();
        checkIsLoggedAjaxModal();

        $action = $this->uri->segment(3);
        switch ($action) {
            case 'view_log':
                checkPermisionAjaxModal('users_administration');

                $idUser = (int) uri()->segment(4);
                if (empty($idUser)) {
                    jsonResponse('ID user is required');
                }

                if (!model(User_Model::class)->exist_user($idUser)){
                    jsonResponse('Error: This user does not exist.');
                }

				$conditions = [
					'conditions' => [
						'user' => $idUser
					],
				];

                /** @var Monolog_Logs_Model $monologModel */
                $monologModel = model(Monolog_Logs_Model::class);

                views()->assign([
                    'idUser' => $idUser,
                    'logs'   => $monologModel->findByUser($conditions)
                ]);

                views()->display('admin/user/popup_logs_view');
            break;
            case 'restore_user':
                checkPermisionAjaxModal('users_administration');

                $data['idUser'] = $idUser = (int) uri()->segment(4);

                /** @var Users_Model $usersModel */
                $usersModel = model(Users_Model::class);

                if (empty($idUser) || (empty($data['user'] = $user = $usersModel->findOne($idUser)))) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                if($user['status'] != UserStatus::DELETED){
                    jsonResponse('User status is not deleted!');
                }

                switch ($user['user_group']) {
                    case 1: //buyer
                        /** @var Buyer_Companies_Model $buyerCompaniesModel */
                        $buyerCompaniesModel = model(Buyer_Companies_Model::class);

                        $buyerCompany = $buyerCompaniesModel->findOneBy([
                            'conditions'    => [
                                'userId'    => $idUser,
                            ],
                        ]);

                        if(!empty($buyerCompany))
                        {
                            $data['companyData'] = [
                                'id_company'                => $buyerCompany['id'],
                                'company_name'              => $buyerCompany['company_name'],
                                'company_legal_name'        => $buyerCompany['company_legal_name'],
                                'company_name_label'        => 'Buyer Company Name',
                                'company_legal_name_label'  => 'Buyer Company Legal Name',
                            ];
                        }

                        break;
                    case 31: //shipper
                        /** @var Shipper_Companies_Model $shipperCompaniesModel */
                        $shipperCompaniesModel = model(Shipper_Companies_Model::class);

                        $shipperCompany = $shipperCompaniesModel->findOneBy([
                            'conditions'    => [
                                'userId'    => $idUser,
                            ],
                        ]);

                        $data['companyData'] = [
                            'id_company'               => $shipperCompany['id'],
                            'company_name'             => $shipperCompany['co_name'],
                            'company_legal_name'       => $shipperCompany['legal_co_name'],
                            'company_name_label'       => 'FF Company Name',
                            'company_legal_name_label' => 'FF Company Legal Name'
                        ];

                        break;
                    default: //seller
                        /** @var Seller_Companies_Model $sellerCompaniesModel */
                        $sellerCompaniesModel = model(Seller_Companies_Model::class);

                        $sellerCompany = $sellerCompaniesModel->findOneBy([
                            'conditions'    => [
                                'userId'    => $idUser,
                            ],
                        ]);

                        $data['companyData'] = [
                            'id_company'               => $sellerCompany['id'],
                            'company_name'             => $sellerCompany['name_company'],
                            'company_legal_name'       => $sellerCompany['legal_name_company'],
                            'company_name_label'       => 'Seller Company Name',
                            'company_legal_name_label' => 'Seller Company Legal Name'
                        ];

                        break;
                }

                views()->assign($data);
                views()->display('admin/user/popup_restore_view');
            break;
            case 'block_user':
                checkPermisionAjaxModal('users_administration');

                $userId = (int) uri()->segment(4);
                if (empty($userId)) {
                    messageInModal(translate('systmess_error_invalid_data'));
                }

                /** @var Users_Model $usersModel */
                $usersModel = model(Users_Model::class);
                if (empty($user = $usersModel->findOne($userId))) {
                    messageInModal(translate('systmess_error_invalid_data'));
                }

                if (UserType::EP_STAFF() === $user['user_type']) {
                    messageInModal(translate('systmess_error_permission_not_granted'));
                }

                views(
                    'admin/user/popup_block_view',
                    [
                        'userId' => $userId,
                        'action' => __SITE_URL . 'users/ajax_operations/block_user'
                    ]
                );

            break;
            case 'block_ep_staff':
                checkPermisionAjaxModal('block_ep_staff');
                $userId = (int) uri()->segment(4);

                if (empty($userId)) {
                    messageInModal(translate('systmess_error_invalid_data'));
                }

                /** @var Users_Model $usersModel */
                $usersModel = model(Users_Model::class);

                $user = $usersModel->findOne($userId);

                if (empty($user) || UserType::EP_STAFF() !== $user['user_type']) {
                    messageInModal(translate('systmess_error_invalid_data'));
                }

                views(
                    'admin/user/popup_block_view',
                    [
                        'userId' => $userId,
                        'action' => __SITE_URL . 'users/ajax_operations/block_ep_staff'
                    ]
                );

            break;
            case 'restrict_user':
                checkPermisionAjaxModal('users_administration');
                messageInModal('This action is not available now.');
                $id_user = (int) $this->uri->segment(4);
                $method = $this->uri->segment(5);
                if (empty($id_user)) {
                    jsonResponse('ID user is required');
                }

                $data = array(
                    'id_user' => $id_user
                );
                $this->view->display('admin/user/popup_restrict_view', $data);
            break;
            case 'add_calling_status':
                if (!have_right('manage_content'))
                    messageInModal(translate("systmess_error_rights_perform_this_action"));

                $this->view->display('admin/user/calling_statuses/add_status_view');
            break;
            case 'edit_calling_status':
                if (!have_right('manage_content'))
                    messageInModal(translate("systmess_error_rights_perform_this_action"));

                $this->load->model('User_Model', 'users');
                $id_status = intVal($this->uri->segment(4));
                $data['status'] = $this->users->get_calling_status($id_status);
                if(empty($data['status'])){
                    jsonResponse('Error: The calling status does not exist.');
                }
                $this->view->display('admin/user/calling_statuses/edit_status_view', $data);
            break;
            case 'calling_notices':
                if (!have_right('manage_content'))
                    messageInModal(translate("systmess_error_rights_perform_this_action"));

                $data['id_user'] = intval($this->uri->segment(4));

                $this->load->model('User_model', 'users');
                $data['user'] = $this->users->getUser($data['id_user']);
                if (empty($data['user']))
                    messageInModal(translate("systmess_error_user_does_not_exist"));

		        $data['calling_statuses'] = $this->users->get_calling_statuses();
                $data['notices'] = $this->users->get_notice($data['id_user']);
                $this->view->display('admin/user/calling_notices_view', $data);
            break;
            case 'add_notification_message':
                if (!have_right('manage_content'))
                    messageInModal(translate("systmess_error_rights_perform_this_action"));

                $this->view->display('admin/user/notification_messages/add_message_view');
            break;
            case 'edit_notification_message':
                if (!have_right('manage_content'))
                    messageInModal(translate("systmess_error_rights_perform_this_action"));

                $this->load->model('User_Model', 'users');
                $id_message = intVal($this->uri->segment(4));
                $data['message'] = $this->users->get_notification_message($id_message);
                if(empty($data['message'])){
                    jsonResponse('Error: The message does not exist.');
                }
                $this->view->display('admin/user/notification_messages/edit_message_view', $data);
            break;
            case 'send_multi_email':
                if(!have_right('users_administration')){
                    messageInModal(translate("systmess_error_rights_perform_this_action"));
                }

                $template_params = array(
                    'list_of_keys' => array(
                        'complete_profile_remind',
                        'add_products_remind'
                    )
                );

                $id_user = (int)$this->uri->segment(4);

                if (!empty($id_user)) {
                    $user = model('User')->getUser($id_user);
                    if (empty($user)) {
                        messageInModal(translate("systmess_error_user_does_not_exist"));
                    }

                    $data['id_user'] = $id_user;
                    $template_params['group_type'] = $user['gr_type'];
                }

                $templateCall = new GroupEmailTemplates();
                $data['email_templates'] = $templateCall->getVerificationTemplates($template_params);

				$this->view->assign($data);
				$this->view->display('admin/user/emails/send_multi_email_view');
            break;
            case 'export':
                checkAdminAjaxModal('export_users');

				/** @var Country_Model $countryModel*/
				$countryModel = model(Country_Model::class);

				/** @var Usergroup_Model $userGroupModel*/
				$userGroupModel = model(Usergroup_Model::class);

				/** @var User_Model $userModel*/
				$userModel = model(User_Model::class);
				$types = $userModel->users_export_types();

				$typeKeys = array_keys($types);
				$typeFields = [];
				foreach ($types as $key => $type) {
					$typeFields[$key] = array_keys($type);
				}
				views()->assign([
					'groups'    => $userGroupModel->getGroupsByType([
                        'type'      => implode(',', array_map(function ($type) { return "'$type'"; }, $typeKeys))
					]),
					'types'     => $typeFields,
					'countries' => $countryModel->get_countries(),
					'statuses'  => [
						'new', 'active', 'inactive', 'banned', 'deleted', 'staff', 'pending', 'awaiting', 'blocked', 'restricted'
					]
				]);

				views()->display('admin/user/export_view');
            break;
        }
    }

    public function ajax_operations()
    {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        if (!logged_in()) {
            jsonResponse(translate("systmess_error_should_be_logged"));
        }

        $action = $this->uri->segment(3);
        switch ($action) {
            case 'marker_users_details':
                checkAdminAjaxDT('users_administration');

                $this->load->model("Usergroup_Model", "usergroup");
                $user_params = array(
                    'city_list' => $_POST['id_city'],
                    "groups_list" => "'Buyer','Seller','Shipper'"
                );

                if (!empty($_POST["search"])) {
                    $user_params["keywords"] = cleanInput($_POST["search"]);
                }

                if (!empty($_POST["id_user"])) {
                    $user_params["users_list"] = intval($_POST["id_user"]);
                }

                if (isset($_POST["group"])) {
                    $user_params["group"] = intval($_POST["group"]);
                } else {
                    $groups = $this->usergroup->getGroupsByType(array("type" => "'Buyer','Seller','Shipper'", "counter" => false));
                    $lists = arrayToListKeys($groups, array("idgroup"));
                    $user_params["group"] = $lists["idgroup"];
                }

                if (isset($_POST["country"])) {
                    $filtered_countries = explode(',', $_POST["country"]);
                    $filtered_countries = array_filter($filtered_countries);
                    if(!empty($filtered_countries)){
                        if(count($filtered_countries) > 1){
                            $user_params["country_list"] = implode(',', $filtered_countries);
                        } else{
                            $user_params["country"] = (int)$_POST["country"];
                        }
                    }
                }

                if (isset($_POST["state"])) {
                    $user_params["state"] = intval($_POST["state"]);
                }

                if (isset($_POST["city"])) {
                    $user_params["city"] = intval($_POST["city"]);
                }

                if (isset($_POST["ip"])) {
                    $user_params["ip"] = cleanInput($_POST["ip"]);
                }

                if (isset($_POST["fake_user"])) {
                    $user_params["fake_user"] = (int)$_POST["fake_user"];
                }

                if (isset($_POST["accreditation_files"])) {
                    $user_params["accreditation_files"] = intVal($_POST["accreditation_files"]);
                }

                if (isset($_POST["status"])) {
                    $user_params["status"] = cleanInput($_POST['status']);
                }

                if (isset($_POST["email_status"])) {
                    $user_params["email_status"] = "'" . cleanInput($_POST['email_status']) . "'";
                }

                if (isset($_POST["reg_info"])) {
                    $user_params["user_find_type"] = cleanInput($_POST["reg_info"]);

                    if (isset($_POST["campaign"])) {
                        $user_params["user_find_info"] = (int)$_POST["campaign"];
                    }

                    if (isset($_POST["brand_ambassador"])) {
                        $user_params["user_find_info"] = (int)$_POST["brand_ambassador"];
                    }
                }

                if (isset($_POST["online"])) {
                    $user_params["logged"] = intval($_POST["online"]);
                }

                if (isset($_POST["reg_date_from"])) {
                    $user_params["registration_start_date"] = formatDate($_POST["reg_date_from"], "Y-m-d");
                }

                if (isset($_POST["reg_date_to"])) {
                    $user_params["registration_end_date"] = formatDate($_POST["reg_date_to"], "Y-m-d");
                }

                if (isset($_POST["resend_date_from"])) {
                    $user_params["resend_email_from_date"] = formatDate($_POST["resend_date_from"], "Y-m-d");
                }

                if (isset($_POST["resend_date_to"])) {
                    $user_params["resend_email_to_date"] = formatDate($_POST["resend_date_to"], "Y-m-d");
                }

                if (isset($_POST["activity_date_from"])) {
                    $user_params["activity_start_date"] = formatDate($_POST["activity_date_from"], "Y-m-d");
                }

                if (isset($_POST["activity_date_to"])) {
                    $user_params["activity_end_date"] = formatDate($_POST["activity_date_to"], "Y-m-d");
                }

                $statistic_filter = array();
                if(isset($_POST["statistic_items_total_from"])){
                    $statistic_filter["items_total"]["from"] = (int) $_POST["statistic_items_total_from"];
                }

                if(isset($_POST["statistic_items_total_to"])){
                    $statistic_filter["items_total"]["to"] = (int) $_POST["statistic_items_total_to"];
                }

                if (!empty($statistic_filter)) {
                    $user_params["statistic_filter"] = $statistic_filter;
                }

                if(isset($_POST['swlat'], $_POST['swlng'], $_POST['nelat'], $_POST['nelng'])){
                    $user_params['gmap_bounds'] = array(
                        'swlat' => $_POST['swlat'],
                        'swlng' => $_POST['swlng'],
                        'nelat' => $_POST['nelat'],
                        'nelng' => $_POST['nelng']
                    );
                }

                if(isset($_POST['continent'])){
                    $user_params['id_continent'] = (int)$_POST['continent'];
                }

                $groups_users_count = arrayByKey($this->usergroup->countUsersByGroups($user_params), "idgroup");
                jsonResponse('', 'success', array('groups_users_count' => $groups_users_count));
            break;
            case 'add_calling_notices':
                if (!have_right('manage_content'))
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));

				$validator_rules = array(
					array(
						'field' => 'calling_status',
						'label' => 'Calling status',
						'rules' => array('required' => '', 'integer' => '')
					),
                    array(
						'field' => 'id_user',
						'label' => 'User info',
						'rules' => array('required' => '', 'integer' => '')
					)
				);

                $this->load->model('User_Model', 'users');

                $id_user = intval($_POST['id_user']);
                $user = $this->users->getUser($id_user);
                if(empty($user)){
                    jsonResponse(translate("systmess_error_user_does_not_exist"));
                }

                $current_date = date('Y-m-d H:i:s');
                $id_status = intval($_POST['calling_status']);
                $add_to_notice = '';
                if($id_status != $user['calling_status']){
                    $status = $this->users->get_calling_status($id_status);
                    if(empty($status)){
                        jsonResponse('Error: The calling status does not exist.');
                    }

                    $add_to_notice = 'Calling status has been changed to: '.$status['status_title'].'. ';
                    $this->users->updateUserMain($id_user, array('calling_status' => $id_status, 'calling_date_last' => $current_date));
                }

                $notice = array(
                    'add_date' => formatDate($current_date, 'Y/m/d H:i:s'),
                    'add_by' => 'Call center - '.$this->session->fname . ' ' . $this->session->lname,
                    'notice' => $add_to_notice.cleanInput($_POST['notice'])
                );

                if (!$this->users->exist_user($id_user))
                    jsonResponse(translate("systmess_error_user_does_not_exist"));

                $content = '<li class="list-group-item"><strong>' . $notice['add_date'] . '</strong> - <u>by ' . $notice['add_by'] . '</u> : ' . $notice['notice'] . '</li>';
                if ($this->users->set_notice($id_user, $notice)){
                    jsonResponse('Notice has been added successfully', 'success', array('content' => $content));
                } else{
                    jsonResponse('Error: Failed to add the notice. Please try again later.');
                }
            break;
            case 'add_calling_status':
                if (!have_right('manage_content'))
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));

				$validator_rules = array(
					array(
						'field' => 'status_title',
						'label' => 'Status name',
						'rules' => array('required' => '', 'max_len[50]' => '')
					),
                    array(
						'field' => 'status_color',
						'label' => 'Status color',
						'rules' => array('required' => '')
					),
                    array(
						'field' => 'status_description',
						'label' => 'Status description',
						'rules' => array('required' => '', 'max_len[500]' => '')
					)
				);

                $this->load->model('User_Model', 'users');

                $insert = array(
                    'status_title' => cleanInput($_POST['status_title']),
                    'status_color' => cleanInput($_POST['status_color']),
                    'status_description' => cleanInput($_POST['status_description'])
                );
                $this->users->insert_call_status($insert);
                jsonResponse('The status has been added.', 'success');
            break;
            case 'edit_calling_status':
                if (!have_right('manage_content'))
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));

				$validator_rules = array(
					array(
						'field' => 'id_status',
						'label' => 'Status info',
						'rules' => array('required' => '', 'integer' => '')
					),
					array(
						'field' => 'status_title',
						'label' => 'Status name',
						'rules' => array('required' => '', 'max_len[50]' => '')
					),
                    array(
						'field' => 'status_color',
						'label' => 'Status color',
						'rules' => array('required' => '')
					),
                    array(
						'field' => 'status_description',
						'label' => 'Status description',
						'rules' => array('required' => '', 'max_len[500]' => '')
					)
				);

                $this->load->model('User_Model', 'users');

                $id_status = intval($_POST['id_status']);
                $update = array(
                    'status_title' => cleanInput($_POST['status_title']),
                    'status_color' => cleanInput($_POST['status_color']),
                    'status_description' => cleanInput($_POST['status_description'])
                );
                $this->users->update_call_status($id_status, $update);
                jsonResponse('The changes has been saved.', 'success');
            break;
            case 'delete_calling_status':
                if (!have_right('manage_content'))
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));

				$validator_rules = array(
					array(
						'field' => 'id_status',
						'label' => 'Status info',
						'rules' => array('required' => '', 'integer' => '')
					)
				);

                $this->load->model('User_Model', 'users');
                $id_status = intval($_POST['id_status']);
                $status = $this->users->get_calling_status($id_status);
                if(empty($status)){
                    jsonResponse('Error: The calling status does not exist.');
                }

                if($this->users->is_used_calling_status($id_status)){
                    jsonResponse('Error: This status is used for some users. Please please check the users calling statuses before deleting this one.');
                }

                $this->users->delete_call_status($id_status);
                jsonResponse('The status has been removed.', 'success');
            break;
            case 'add_notification_message':
                if (!have_right('manage_content'))
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));

				$validator_rules = array(
					array(
						'field' => 'message_title',
						'label' => 'Message title',
						'rules' => array('required' => '', 'max_len[250]' => '')
					),
                    array(
						'field' => 'message_module',
						'label' => 'Message module',
						'rules' => array('required' => '')
					),
                    array(
						'field' => 'message_text',
						'label' => 'Message text',
						'rules' => array('required' => '')
					)
				);

                $this->load->model('User_Model', 'users');

                $insert = array(
                    'message_title' => cleanInput($_POST['message_title']),
                    'message_module' => cleanInput($_POST['message_module']),
                    'message_text' => cleanInput($_POST['message_text'])
                );
                $this->users->insert_notification_message($insert);
                jsonResponse('The message has been added.', 'success');
            break;
            case 'edit_notification_message':
                if (!have_right('manage_content'))
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));

				$validator_rules = array(
					array(
						'field' => 'id_message',
						'label' => 'Message info',
						'rules' => array('required' => '', 'integer' => '')
					),
					array(
						'field' => 'message_title',
						'label' => 'Message title',
						'rules' => array('required' => '', 'max_len[250]' => '')
					),
                    array(
						'field' => 'message_module',
						'label' => 'Message module',
						'rules' => array('required' => '')
					),
                    array(
						'field' => 'message_text',
						'label' => 'Message text',
						'rules' => array('required' => '')
					)
				);

                $this->load->model('User_Model', 'users');

                $id_message = intval($_POST['id_message']);
                $update = array(
                    'message_title' => cleanInput($_POST['message_title']),
                    'message_module' => cleanInput($_POST['message_module']),
                    'message_text' => cleanInput($_POST['message_text'])
                );
                $this->users->update_notification_message($id_message, $update);
                jsonResponse('The changes has been saved.', 'success');
            break;
            case 'delete_notification_message':
                if (!have_right('manage_content'))
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));

                $this->load->model('User_Model', 'users');

                $id_message = intval($_POST['id_message']);

                $this->users->delete_notification_message($id_message);

                jsonResponse('The message has been removed.', 'success');
            break;
            case 'activate_user':
                //region check permition
                checkPermisionAjax('activate_user');
                //endregion check permition

                //region validate $_POST
                $validator_rules = array(
                    array(
                        'field' => 'user',
                        'label' => 'User info',
                        'rules' => array('required' => '')
                    )
                );
				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate()){
					jsonResponse($this->validator->get_array_errors());
                }
                //endregion validate $_POST

                //region get/check user info
                if (empty($userId = request()->request->getInt('user'))) {
                    jsonResponse('User id is required.');
                }

                /** @var Users_Model $usersModel */
                $usersModel = model(Users_Model::class);

                if (empty($user = $usersModel->findOne(
                    $userId,
                    [
                        'with' => ['group'],
                        'columns'   => [
                            '*',
                            'CONCAT(fname, " ", lname) AS user_name',
                        ],
                    ]
                ))) {
                    jsonResponse('User not found.');
                }

                if ('pending' !== $user['status']->value) {
                    jsonResponse('You can activate only users with Pending status.');
                }
                //endregion get user info

                /** @var User_Cancellation_Requests_Model $userCancellationModel */
                $userCancellationModel = model(User_Cancellation_Requests_Model::class);
                $cancellationRequest = $userCancellationModel->findOneBy([
                    'conditions' => [
                        'statuses'  => [CancellationRequestStatus::INIT(), CancellationRequestStatus::CONFIRMED()],
                        'userId'    => $userId,
                    ],
                ]);

                if (!empty($cancellationRequest)) {
                    jsonResponse('This user has a cancellation request, and his account can\'t be activated.');
                }

                //region update user status
                model('user')->updateUserMain($userId, [
                    'activation_account_date'   => date('Y-m-d H:i:s'),
                    'resend_email_date'         => date('Y-m-d H:i:s'),
                    'status'                    => 'active',
                ]);

                /** @var Elasticsearch_Users_Model $elasticsearchUsersModel */
                $elasticsearchUsersModel = model(Elasticsearch_Users_Model::class);
                $elasticsearchUsersModel->index((int) $userId);
                //endregion update user status

                $needActivateItems = false;
                //region Update related features for Seller/Shipper
                switch ($user['group']['gr_type']->value) {
                    case 'Seller':
                        $seller_company = model('company')->get_seller_base_company($userId);
                        if(!empty($seller_company) && (int) $seller_company['visible_company'] != 1){
                            model('company')->update_company((int) $seller_company['id_company'], array('visible_company' => 1));
                        }

                        /** @var Products_Model $productsModel */
                        $productsModel = model(Products_Model::class);

                        $productsModel->updateMany(
                            [
                                'blocked'   => 0,
                            ],
                            [
                                'conditions'    => [
                                    'blockedValue'  => 2,
                                    'sellerId'      => $userId,
                                ],
                            ],
                        );

                    break;
                    case 'Shipper':
                        $shipper_company = model('shippers')->get_shipper_by_user($userId);
                        if(!empty($shipper_company) && (int) $shipper_company['visible'] != 1){
                            model('shippers')->update_shipper(array('visible' => 1), (int) $shipper_company['id']);
                        }
                    break;
                }
                //endregion Update company for Seller/Shipper

                //region set user notice
                $notice = array(
                    'add_date' => date('Y/m/d H:i:s'),
                    'add_by' => user_name_session(),
                    'notice' => 'The user has been activated. The email notification has been send.'
                );
                model('user')->set_notice($userId, $notice);
                //endregion set user notice
                //region notify user about account activation by email
                try {
                    /** @var MailerInterface $mailer */
                    $mailer = $this->getContainer()->get(MailerInterface::class);
                    $mailer->send(
                        (new ActivateAccount(cleanOutput($user['user_name']), $user['group']['gr_type']->value))
                            ->to(new RefAddress((string) $userId, new Address($user['email'])))
                    );
                } catch (\Throwable $th) {
                    jsonResponse(translate('email_has_not_been_sent'));
                }
                //endregion notify user about account activation by email

                // insert onboarding_survey
                /** @var User_Popups_Model $popupSurveys */
                $popupSurveys = model(User_Popups_Model::class);
                $popupSurveys->insertOne([
                    "id_user"   => $userId,
                    "id_popup"  => static::SURVEY_POPUP_ONBOARDING,
                ]);

				jsonResponse('The user status has been changed to Active.', 'success', array('needActivateItems' => $needActivateItems));
            break;
            case 'send_multi_email':
                checkPermisionAjax('activate_user');

				$validator_rules = array(
					array(
						'field' => 'email_template',
						'label' => 'Email template',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'users',
						'label' => 'Users info',
						'rules' => array('required' => '')
					)
				);

				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate()){
                    jsonResponse($this->validator->get_array_errors());
                }

                $templateKey = cleanInput($_POST['email_template']);
                $templateCall = new GroupEmailTemplates();
                $templateData = $templateCall->getVerificationTemplate($templateKey);

                if(empty($templateData)){
                    jsonResponse(translate('email_template_does_not_exist'));
                }

				$templateFilterParams = [
                    'add_products_remind' => [
                        'is_verified'   => 1,
                        'status'        => "'active'",
                        'group_type'    => "'Seller'"
                    ]
                ];

                $params = ['users_list' => $_POST['users']];

                if (isset($templateFilterParams[$templateKey])) {
                    $params = array_merge($params, $templateFilterParams[$templateKey]);
                }

                $users = model(User_Model::class)->getUsers($params);

				if (empty($users)) {
					jsonResponse('The users list are empty for this type of emails.');
				}

				foreach ($users as $user) {

                    $templateCall->sentEmailTemplate($templateData['template_name'], [
                        "userId"        => $user['idu'],
                        "email"         => $user['email'],
                        "userName"      => "{$user['fname']} {$user['lname']}",
                    ]);

					$notice = array(
						'add_date' => date('Y/m/d H:i:s'),
						'add_by' => user_name_session(),
						'notice' => '"' . $templateData['title'] . '" email has been sent.'
					);
                    model(User_Model::class)->set_notice($user['idu'], $notice);

					$update = array(
						'resend_email_date' => date('Y-m-d H:i:s'),
                        'activation_code' => $user['activation_code']
					);
					model(User_Model::class)->updateUserMain($user['idu'], $update);
				}
				jsonResponse('Success: The email has been successfuly sent.', 'success');
            break;
            case 'check_export_params':
                checkAdminAjax('export_users');

                if (empty($_POST['groups'])) {
                    jsonResponse('Please select at least one user group');
                }

                if (empty($_POST['fields'])) {
                    jsonResponse('Please select at least one field');
                }

                $this->load->model('User_Model', 'users');

                $dependencies = [
                    'ug',
                    'z',
                    'pcu',
                ];

                $types = $this->users->users_export_types();

                $fields = array();
                foreach ($types as $type) {
                    $fields = array_merge($fields, $type);
                }

                $select_fields = array();
                $joins = [];
                if (isset($_POST['fields'])) {
                    foreach ($_POST['fields'] as $field) {
                        $field_group = explode('|', $fields[$field]);
                        if (count($field_group) == 2) {
                            $select_fields[] = "$field_group[1] as `$field`";
                            if(in_array($field_group[0], $dependencies)){
                                $joins[] = $field_group[0];
                            }
                        } else {
                            $select_fields[] = "$field_group[0] as `$field`";
                        }

                    }
                }

                $select_fields = implode(', ', $select_fields);
                $groups = implode(',', $_POST['groups']);
                $country = empty($_POST['country']) ? null : intval($_POST['country']);
                $joins = array_filter(array_unique($joins));
                $users = $this->users->get_users_for_export(compact('select_fields', 'joins', 'groups', 'country'));

                if (empty($users)) {
                    jsonResponse('No users found');
                }

                jsonResponse('', 'success');
            break;
            case 'delete_user':
                checkPermisionAjax('delete_user');

                /** @var Users_Model $usersModel */
                $usersModel = model(Users_Model::class);

                $userId = request()->request->getInt('user');
                if (empty($userId) || (empty($user = $usersModel->findOne($userId)))) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                if (UserStatus::FRESH() !== $user['status'] || !in_array($user['user_group'], [1, 2, 3, 5, 6, 31])) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                $usersModel->deleteOne($userId);

                $countRelatedAccounts = $usersModel->countAllBy([
                    'conditions' => [
                        'principal' => (int) $user['id_principal'],
                    ],
                ]);

                if (empty($countRelatedAccounts)) {
                    /** @var Principals_Model $principalsModel */
                    $principalsModel = model(Principals_Model::class);

                    $principalsModel->deleteAllBy([
                        'conditions' => [
                            'id'    => $user['id_principal']
                        ],
                    ]);
                }

                if ('prod' === config('env.APP_ENV') && !empty($user['zoho_id_record'])) {
                    /** @var TinyMVC_Library_Zoho_crm $crmLibrary */
                    $crmLibrary = library(TinyMVC_Library_Zoho_crm::class);

                    $crmLibrary->remove_contact((int) $user['zoho_id_record']);
                }

                // Goodbye sweet prince
                $this->get(MessengerInterface::class)->bus('event.bus')->dispatch(new UserWasDeletedEvent((int) $userId));

				jsonResponse(translate('systmess_success_user_has_been_deleted'), 'success');
            break;
            case 'delete_ep_staff':
                checkPermisionAjax('delete_ep_staff');

                /** @var Users_Model $usersModel */
                $usersModel = model(Users_Model::class);

                $userId = request()->request->getInt('user');
                if (empty($userId) || (empty($user = $usersModel->findOne($userId)))) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                if ('ep_staff' != $user['user_type']) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                $user_updates = array(
                    'clean_session_token'   => '',
                    'subscription_email'    => 0,
                    'notify_email' 		    => 0,
                    'cookie_salt'           => genRandStr(8),
                    'status'                => 'deleted',
                    'fname'                 => 'User',
                    'lname'                 => $user['idu'],
                    'email'                 => time() . $user['email'],
                    'logged'                => 0,
                );

                if (!model(User_Model::class)->updateUserMain($user['idu'], $user_updates)) {
                    jsonResponse('Failed to update user data.');
                }

                if (!model(Auth_Model::class)->change_hash($user['id_principal'], array(
                    'token_password' => getEncryptedPassword(base64_encode(random_bytes(16))),
                    'token_email'       => getEncryptedEmail($user_updates['email']),
                    'is_legacy'         => 0,
                ))){
                    jsonResponse('Failed to change user credentials.');
                }

                session()->destroyBySessionId($user['ssid']);

                model(User_Model::class)->set_notice($user['idu'], [
                    'add_date' => date('Y/m/d H:i:s'),
                    'add_by' => 'System',
                    'notice' => 'Admin account has been deleted.'
                ]);

				jsonResponse(translate('systmess_success_user_has_been_deleted'), 'success');
            break;
            case 'restrict_user':
                // This functionality is also used in cron: restrict_users
                checkPermisionAjax('users_administration');
                jsonResponse('This action is not available now.');
                $validator_rules = array(
					array(
						'field' => 'user',
						'label' => 'User info',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'notice',
						'label' => 'Reason',
						'rules' => array('required' => '')
					)
				);

				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate()){
                    jsonResponse($this->validator->get_array_errors());
                }

                //region User
                $id_user = (int) arrayGet($_POST, 'user');
                if (empty($user = model('user')->getSimpleUser($id_user))) {
                    jsonResponse("The user with such ID was not found.");
                }
                //endregion User

                //region Reason
                $notice_message = cleanInput($_POST['notice']);
                $reason = "<br><strong>Reason</strong>: {$notice_message}";
                //endregion Reason

                /** @var Blocking_Model $blockingModel */
                $blockingModel = model(Blocking_Model::class);
                $blockingModel->block_user_content($id_user);

                //region set user notice
                $status_before = ucfirst($user['status']);
                $notice = array(
                    'add_date' => date('Y/m/d H:i:s'),
                    'add_by' => user_name_session(),
                    'notice' => "The user has been Restricted. Status before restriction: {$status_before}. {$reason}"
                );
                model('user')->set_notice($id_user, $notice);
                //endregion set user notice

                model('user')->updateUserMain($id_user, array(
                    'status'      => 'restricted',
                    'status_temp' => $user['status']
                ));

                /** @var Elasticsearch_Users_Model $elasticsearchUsersModel */
                $elasticsearchUsersModel = model(Elasticsearch_Users_Model::class);
                $elasticsearchUsersModel->deleteUser((int) $id_user);

                /** @var Users_Blocking_Statistics_Model $usersBlockingStatisticsModel */
                $usersBlockingStatisticsModel = model(Users_Blocking_Statistics_Model::class);

                $usersBlockingStatisticsModel->insertOne([
                    'id_user'   => $id_user,
                    'type'      => RestrictionType::RESTRICTION(),
                ]);

                // Sleep well, Neo
                $this->get(MessengerInterface::class)->bus('event.bus')->dispatch(new UserWasRestrictedEvent((int) $id_user));

                jsonResponse('The user has been Restricted.', 'success');
            break;
            case 'unrestrict_user':
                checkPermisionAjax('users_administration');

                //region User
                $userId = (int) arrayGet($_POST, 'user');
                if (empty($user = model('user')->getSimpleUser($userId))) {
                    jsonResponse("The user with such ID was not found.");
                }
                //endregion User

                if (empty($user['status_temp'])) {
                    jsonResponse('User status information, before restriction, is empty. Please contact DEV Team for support.');
                }

                model('blocking')->unblock_user_content($userId);

                //region set user notice
                $notice = array(
                    'add_date' => date('Y/m/d H:i:s'),
                    'add_by' => user_name_session(),
                    'notice' => "The user has been un-restricted. Status after un-restriction: {$user['status_temp']}."
                );
                model('user')->set_notice($userId, $notice);
                //endregion set user notice

                model('user')->updateUserMain($userId, array(
                    'status'      => $user['status_temp'],
                    'status_temp' => null
                ));

                /** @var Elasticsearch_Users_Model $elasticsearchUsersModel */
                $elasticsearchUsersModel = model(Elasticsearch_Users_Model::class);
                $elasticsearchUsersModel->index((int) $userId);

                /** @var MailerInterface $mailer */
                $mailer = $this->getContainer()->get(MailerInterface::class);
                $mailer->send(
                    (new StartUsingEpAgain("{$user['fname']} {$user['lname']}"))
                        ->to(new RefAddress((string) $userId, new Address($user['email'])))
                );

                /** @var Users_Blocking_Statistics_Model $usersBlockingStatisticsModel */
                $usersBlockingStatisticsModel = model(Users_Blocking_Statistics_Model::class);
                $usersBlockingStatisticsTable = $usersBlockingStatisticsModel->getTable();

                $blockingRecord = $usersBlockingStatisticsModel->findOneBy([
                    'conditions' => [
                        'restrictionType'   => RestrictionType::RESTRICTION(),
                        'userId'            => $userId,
                    ],
                    'order'     => [
                        "{$usersBlockingStatisticsTable}.`id`" => 'desc',
                    ],
                ]);

                if (!empty($blockingRecord) && null === $blockingRecord['cancel_date']) {
                    $usersBlockingStatisticsModel->updateOne(
                        $blockingRecord['id'],
                        [
                            'cancel_date'   => new \DateTimeImmutable()
                        ],
                    );
                }

                // Wake up, Neo
                $this->get(MessengerInterface::class)->bus('event.bus')->dispatch(new UserWasUnrestrictedEvent((int) $userId));

                jsonResponse('The user has been un-restricted.', 'success');
            break;
            case 'restore_user':
                checkPermisionAjax('users_administration');

                $this->restoreUserData();
            break;
            case 'fake_user':
                checkPermisionAjax('users_administration');

                /** @var Users_Model $usersModel */
                $usersModel = model(Users_Model::class);

                if (
                    empty($userId = request()->request->getInt('user'))
                    || empty($user = $usersModel->findOne($userId))
                ) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                if ($user['fake_user']) {
                    checkPermisionAjax('unmark_user_as_fake');

                    $this->unmarkUserAsFake($user, $usersModel);

                    jsonResponse('The user has been unmarked as demo and unblocked.', 'success');
                }

                $this->markUserAsFake($user, $usersModel);

                jsonResponse('The user has been marked as demo and blocked.', 'success');

            break;
            case 'model_user':
                checkPermisionAjax('users_administration');

                /** @var Users_Model $usersModel */
                $usersModel = model(Users_Model::class);

                if (
                    empty($userId = request()->request->getInt('user'))
                    || empty($user = $usersModel->findOne($userId))
                ) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                $usersModel->updateOne($userId, ['is_model' => !$user['is_model']]);
                $user['is_model'] ? $this->markUserAsFake($user, $usersModel) : $this->unmarkUserAsFake($user, $usersModel);

                jsonResponse('The user model status has changed.', 'success');
            break;
            case 'block_user':
                checkPermisionAjax('users_administration');
                $validator_rules = array(
					array(
						'field' => 'user',
						'label' => 'User info',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'notice',
						'label' => 'Reason',
						'rules' => array('required' => '')
					)
				);

                //region User
                $id_user = (int) arrayGet($_POST, 'user');
                if (empty($user = model('user')->getSimpleUser($id_user))) {
                    jsonResponse("The user with such ID was not found.");
                }
                //endregion User

                //region Reason
                $notice_message = cleanInput($_POST['notice']);
                $reason = "<br><strong>Reason</strong>: {$notice_message}";
                //endregion Reason

                model('blocking')->block_user_content($id_user);

                //region set user notice
                $status_before = ucfirst($user['status']);
                $notice = array(
                    'add_date' => date('Y/m/d H:i:s'),
                    'add_by' => user_name_session(),
                    'notice' => "The user has been blocked. Status before blocking: {$status_before}. {$reason}"
                );
                model('user')->set_notice($id_user, $notice);
                //endregion set user notice

                model('user')->updateUserMain($id_user, array(
                    'status'      => 'blocked',
                    'status_temp' => $user['status']
                ));

                /** @var Elasticsearch_Users_Model $elasticsearchUsersModel */
                $elasticsearchUsersModel = model(Elasticsearch_Users_Model::class);
                $elasticsearchUsersModel->deleteUser((int) $id_user);

                /** @var Users_Blocking_Statistics_Model $usersBlockingStatisticsModel */
                $usersBlockingStatisticsModel = model(Users_Blocking_Statistics_Model::class);

                $usersBlockingStatisticsModel->insertOne([
                    'id_user'   => $id_user,
                    'type'      => RestrictionType::BLOCKING(),
                ]);

                // Sleep well, Neo
                $this->get(MessengerInterface::class)->bus('event.bus')->dispatch(new UserWasBlockedEvent((int) $id_user));

                destroyUserSession($id_user);

                jsonResponse('The user has been blocked.', 'success');
            break;
            case 'unblock_user':
                checkPermisionAjax('users_administration');

                //region User
                $userId = request()->request->getInt('user');
                if (empty($user = model('user')->getSimpleUser($userId))) {
                    jsonResponse("The user with such ID was not found.");
                }
                //endregion User

                if (empty($user['status_temp'])) {
                    jsonResponse('User status information, before blocking, is empty. Please contact DEV Team for support.');
                }

                model('blocking')->unblock_user_content($userId);

                //region set user notice
                $notice = array(
                    'add_date' => date('Y/m/d H:i:s'),
                    'add_by' => user_name_session(),
                    'notice' => "The user has been unblocked. Status after unblocking: {$user['status_temp']}."
                );
                model('user')->set_notice($userId, $notice);
                //endregion set user notice

                model('user')->updateUserMain($userId, array(
                    'status'      => $user['status_temp'],
                    'status_temp' => null
                ));

                /** @var Elasticsearch_Users_Model $elasticsearchUsersModel */
                $elasticsearchUsersModel = model(Elasticsearch_Users_Model::class);
                $elasticsearchUsersModel->index((int) $userId);

                /** @var Users_Blocking_Statistics_Model $usersBlockingStatisticsModel */
                $usersBlockingStatisticsModel = model(Users_Blocking_Statistics_Model::class);
                $usersBlockingStatisticsTable = $usersBlockingStatisticsModel->getTable();

                $blockingRecord = $usersBlockingStatisticsModel->findOneBy([
                    'conditions' => [
                        'restrictionType'   => RestrictionType::BLOCKING(),
                        'userId'            => $userId,
                    ],
                    'order'     => [
                        "{$usersBlockingStatisticsTable}.`id`" => 'desc',
                    ],
                ]);

                if (!empty($blockingRecord) && null === $blockingRecord['cancel_date']) {
                    $usersBlockingStatisticsModel->updateOne(
                        $blockingRecord['id'],
                        [
                            'cancel_date'   => new \DateTimeImmutable()
                        ],
                    );
                }

                // Wake up, Neo
                $this->get(MessengerInterface::class)->bus('event.bus')->dispatch(new UserWasUnblockedEvent((int) $userId));

                jsonResponse('The user has been unblocked.', 'success');
            break;
            case 'block_ep_staff':
                checkPermisionAjax('block_ep_staff');

                $request = request()->request;
                if (empty($userId = $request->getInt('user'))) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                if (empty($reason = cleanInput($request->get('notice')))) {
                    jsonResponse(sprintf(translate('validation_is_required'), 'Reason'));
                }

                /** @var Users_Model $usersModel */
                $usersModel = model(Users_Model::class);

                $user = $usersModel->findOne($userId);
                if (empty($user) || UserType::EP_STAFF() !== $user['user_type']) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                if (is_my($user['idu'])) {
                    jsonResponse(translate('systmess_error_block_ep_staff_block_yourself'));
                }

                if (UserStatus::ACTIVE() !== $user['status']) {
                    jsonResponse(translate('systmess_error_block_ep_staff_wrong_status'));
                }

                /** @var Blocking_Model $blockingModel */
                $blockingModel = model(Blocking_Model::class);
                $blockingModel->block_user_content($userId);

                //region set user notice
                $statusBeforeBlocking = ucfirst((string) $user['status']);
                $user['notice'][] = [
                    'add_date' => (new DateTime())->format('Y/m/d H:i:s'),
                    'add_by'   => user_name_session(),
                    'notice'   => "The user has been blocked. Status before blocking: {$statusBeforeBlocking}. <br><strong>Reason</strong>: {$reason}"
                ];

                $usersModel->updateOne($userId, [
                    'status_temp' => $user['status'],
                    'status'      => UserStatus::BLOCKED(),
                    'notice'      => $user['notice'],
                ]);

                /** @var Users_Blocking_Statistics_Model $usersBlockingStatisticsModel */
                $usersBlockingStatisticsModel = model(Users_Blocking_Statistics_Model::class);
                $usersBlockingStatisticsModel->insertOne([
                    'id_user'   => $userId,
                    'type'      => RestrictionType::BLOCKING(),
                ]);

                // Sleep well, Neo
                $this->get(MessengerInterface::class)->bus('event.bus')->dispatch(new UserWasBlockedEvent((int) $userId));

                destroyUserSession($userId);

                jsonResponse(translate('systmess_success_blocked_user'), 'success');
            break;
            case 'unblock_ep_staff':
                checkPermisionAjax('block_ep_staff');

                if (empty($userId = request()->request->getInt('user'))) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                /** @var Users_Model $usersModel */
                $usersModel = model(Users_Model::class);

                $user = $usersModel->findOne($userId);
                if (empty($user) || UserType::EP_STAFF() !== $user['user_type']) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                if (UserStatus::BLOCKED() !== $user['status']) {
                    jsonResponse(translate('systmess_error_unblock_ep_staff_wrong_status'));
                }

                if (empty($user['status_temp'])) {
                    jsonResponse(translate('systmess_error_unblock_user_empty_previous_status'));
                }

                /** @var Blocking_Model $blockingModel */
                $blockingModel = model(Blocking_Model::class);
                $blockingModel->unblock_user_content($userId);

                $userTempStatus = ucfirst((string) $user['status_temp']);
                $user['notice'][] = [
                    'add_date' => (new DateTime)->format('Y/m/d H:i:s'),
                    'add_by' => user_name_session(),
                    'notice' => "The user has been unblocked. Status after unblocking: {$userTempStatus}."
                ];

                $usersModel->updateOne($userId, [
                    'status_temp' => null,
                    'notice'      => $user['notice'],
                    'status'      => $user['status_temp'],
                ]);

                /** @var Users_Blocking_Statistics_Model $usersBlockingStatisticsModel */
                $usersBlockingStatisticsModel = model(Users_Blocking_Statistics_Model::class);
                $usersBlockingStatisticsTable = $usersBlockingStatisticsModel->getTable();

                $blockingRecord = $usersBlockingStatisticsModel->findOneBy([
                    'conditions' => [
                        'restrictionType'   => RestrictionType::BLOCKING(),
                        'userId'            => $userId,
                    ],
                    'order'     => [
                        "{$usersBlockingStatisticsTable}.`id`" => 'desc',
                    ],
                ]);

                if (!empty($blockingRecord) && null === $blockingRecord['cancel_date']) {
                    $usersBlockingStatisticsModel->updateOne(
                        $blockingRecord['id'],
                        [
                            'cancel_date'   => new \DateTimeImmutable()
                        ],
                    );
                }

                // Wake up, Neo
                $this->get(MessengerInterface::class)->bus('event.bus')->dispatch(new UserWasUnblockedEvent((int) $userId));

                jsonResponse(translate('systmess_success_unblock_user'), 'success');
            break;
            case 'confirm_user':
                checkPermisionAjax('users_administration');

                //region User
                $user_id = (int) arrayGet($_POST, 'user');
                if (
                    empty($user_id)
                    || empty($user = model('user')->getSimpleUser($user_id))
                ) {
                    jsonResponse("The user with such ID is not found on this server.");
                }
                //endregion User

                //region Check
                if (!in_array($user['gr_type'], array('Buyer', 'Seller', 'Shipper'))) {
                    jsonResponse("User of this type cannot be confirmed.", 'warning');
                }
                if ('new' !== $user['status']) {
                    jsonResponse("Only new users can be confirmed.", 'warning');
                }
                //endregion Check

                //region Confirm
                confirmUserAccount($user_id, $user, true);
                //endregion Confirm

                jsonResponse("The user was successfully confirmed.", 'success');

            break;
            case 'sending_matchmaking_emails':
                checkPermisionAjax('manage_matchmaking');

                /** @var User_Model $userModel */
                $userModel = model(User_Model::class);

                if (
                    empty($userId = request()->request->getInt('user'))
                    || empty($user = $userModel->getSimpleUser($userId))
                    || $user['fake_user']
                    || $user['is_model']
                    || !(is_buyer((int) $user['user_group']) || is_seller((int) $user['user_group']) || is_manufacturer((int) $user['user_group']))
                ) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                $acceptEmails = $user['accept_matchmaking_email'] ? 0 : 1;

                $userModel->updateUserMain($user['idu'], ['accept_matchmaking_email' => $acceptEmails]);

                jsonResponse(translate('systmess_success_matchmaking_swich_sending_by_cron', ['{{OPERATION}}' => $acceptEmails ? 'started' : 'stopped']), 'success');
            break;
            case 'send_matchmaking_email':
                checkPermisionAjax('manage_matchmaking');

                /** @var User_Model $userModel */
                $userModel = model(User_Model::class);

                if (
                    empty($userId = request()->request->getInt('user'))
                    || empty($user = $userModel->getSimpleUser($userId))
                    || $user['fake_user']
                    || $user['is_model']
                    || !(is_buyer((int) $user['user_group']) || is_seller((int) $user['user_group']) || is_manufacturer((int) $user['user_group']))
                ) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                /** @var MatchmakingService $matchmakingService */
                $matchmakingService = new MatchmakingService();

                $templateCall = new GroupEmailTemplates();

                if (is_buyer((int) $user['user_group'])) {
                    list($countSellers, $countItems) = $matchmakingService->counSellersItems((int) $user['idu']);

                    if ($countSellers < 2) {
                        jsonResponse(translate('systmess_error_send_matchmaking_email_no_companies_found', ['{{COUNT_COMPANIES}}' => $countSellers]));
                    }

                    if ($countItems < 2) {
                        jsonResponse(translate('systmess_error_send_matchmaking_email_no_items_found', ['{{COUNT_ITEMS}}' => $countItems]));
                    }

                    $templateCall->sentMatchmakingEmailTemplate('buyer', $user['status'], [
                        'userId'        => $user['idu'],
                        'email'         => $user['email'],
                        'userName'      => "{$user['fname']} {$user['lname']}",
                        'countSellers'  => $countSellers,
                        'countItems'    => $countItems,
                    ]);
                } else {
                    $countBuyers = $matchmakingService->countBuyers((int) $user['idu']);

                    if ($countBuyers < 2) {
                        jsonResponse(translate('systmess_error_send_matchmaking_email_no_buyers_found', ['{{COUNT_BUYERS}}' => $countBuyers]));
                    }

                    $templateCall->sentMatchmakingEmailTemplate('seller', $user['status'], [
                        'userId'        => $user['idu'],
                        'email'         => $user['email'],
                        'userName'      => "{$user['fname']} {$user['lname']}",
                        'countBuyers'   => $countBuyers,
                    ]);
                }

                $userModel->updateUserMain($user['idu'], ['matchmaking_email_date' => (new \DateTime())->format('Y-m-d H:i:s')]);

                jsonResponse(translate('systmess_success_email_has_been_sent'), 'success');
            break;
            default: jsonResponse(translate('sysmtess_provided_path_not_found'));
        }
    }

    function export_action() {
        $action = uri()->segment(3);
        $validStatuses = ['new', 'active', 'inactive', 'banned', 'deleted', 'staff', 'pending', 'awaiting', 'blocked', 'restricted'];

        switch ($action) {
            case 'check':
                if (!isAjaxRequest()){
                    headerRedirect();
                }

                checkAdminAjax('export_users');

                if (empty(request()->request->get('groups'))) {
                    jsonResponse('Please select at least one user group');
                }

                if (empty(request()->request->get('fields'))) {
                    jsonResponse('Please select at least one field');
                }

                $status = request()->request->get('status');
                if(!empty($status) && !in_array($status, $validStatuses)){
                    jsonResponse('No such status');
                }

                $form_data = request()->request->all();
            break;
            case 'download':
                checkAdmin('export_users');

                if (empty(request()->query->get('groups'))) {
                    return false;
                }

                if (empty(request()->query->get('fields'))) {
                    return false;
                }

                $status = request()->query->get('status');
                if(!empty($status) && !in_array($status, $validStatuses)){
                    return false;
                }

                $form_data = request()->query->all();
            break;
            default:
                return false;
            break;
        }

        $dependencies = [
            'ug',
            'z',
            'pcu',
            'cb' ,
            'pcc',
            'os',
        ];

        /** @var User_Model $userModel*/
        $userModel = model(User_Model::class);
        $types = $userModel->users_export_types();

        $fields = array();
        foreach ($types as $type) {
            $fields = array_merge($fields, $type);
        }

        $select_fields = [];
        $joins = [];
        if (isset($form_data['fields'])) {
            foreach ($form_data['fields'] as $field) {
                $field_group = explode('|', $fields[$field]);
                if (count($field_group) == 2) {
                    $select_fields[] = "$field_group[1] as `$field`";
                    if(in_array($field_group[0], $dependencies)){
                        $joins[] = $field_group[0];
                    }
                } else {
                    $select_fields[] = "$field_group[0] as `$field`";
                }

            }
        }

        $users = $userModel->get_users_for_export([
            'restrictedFrom'    => getDateFormat($form_data['restricted_from'] ?: null, 'm/d/Y', 'Y-m-d'),
            'select_fields'     => implode(', ', $select_fields),
            'restrictedTo'      => getDateFormat($form_data['restricted_to'] ?: null, 'm/d/Y', 'Y-m-d'),
            'blockedFrom'       => getDateFormat($form_data['blocked_from'] ?: null, 'm/d/Y', 'Y-m-d'),
            'blockedTo'         => getDateFormat($form_data['blocked_to'] ?: null, 'm/d/Y', 'Y-m-d'),
            'reg_from'          => getDateFormat($form_data['reg_from'] ?: null, 'm/d/Y', 'Y-m-d'),
            'country'           => (int) ($form_data['country'] ?: 0),
            'groups'            => implode(',', $form_data['groups']),
            'reg_to'            => getDateFormat($form_data['reg_to'] ?: null, 'm/d/Y', 'Y-m-d'),
            'status'            => $form_data['status'] ?: null,
            'joins'             => array_filter(array_unique($joins)),
        ]);

        if (empty($users)) {
            if($action == 'check'){
                jsonResponse('No users found');
            } else{
                return false;
            }
        }

        if($action == 'check'){
            jsonResponse('', 'success');
        }

        $headers = array_keys($users[0]);

        if (in_array('User phone', $headers)) {
            $libPhoneUtils = PhoneNumberUtil::getInstance();

            foreach ($users as &$user) {
                try {
                    //try to convert phone number to the international format
                    $phoneNumber = $libPhoneUtils->parse($user['User phone']);
                    $user['User phone'] = $libPhoneUtils->format($phoneNumber, PhoneNumberFormat::INTERNATIONAL);
                } catch (NumberParseException $e) {
                    //do nothing
                }
            }
        }

        $filename = 'export-portal-users';
        if (!empty(request()->query->get('filename'))) {
            $filename = request()->query->get('filename');
        }

        $this->_export_csv($headers, $users, "{$filename}.csv");
    }

    private function _export_csv($headings, $rows, $filename) {
        if (empty($headings) || empty($rows)) {
            return false;
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $output = fopen('php://output', 'w');
        fputcsv($output, $headings);

        foreach($rows as $row) {
            fputcsv($output, $row);
        }
    }

    public function administration()
    {
        checkAdmin('users_administration');

        $this->load->model('Usergroup_Model', 'usergroup');
        $this->load->model('User_Model', 'users');
        $this->load->model('Cr_users_Model', 'cr_users');
        $this->load->model('Country_Model', 'country');
        $this->load->model('Campaigns_Model', 'campaigns');

        $data['last_users_id'] = $this->users->get_users_last_id();
        $data['groups'] = $this->usergroup->getGroupsByType(array('type' => "Buyer,Seller,Shipper"));
        $data['list_country'] = $this->country->get_countries();
        $data['continents'] = $this->country->get_continents();
        $data['campaigns'] = $this->campaigns->get_campaigns();
        $data['cr_users'] = $this->cr_users->cr_get_users(array('group_type' => "CR Affiliate", 'status' => 'active'));
		$data['industries'] = model('category')->getCategories(array('industries_only' => true));
		$data['filters'] = with(uri()->uri_to_assoc(4), function ($params) {
            $filters = array();
            $raw = array(
                'group' => arrayGet($params, 'group'),
                'user' => arrayGet($params, 'user'),
            );
            foreach (array_filter($raw) as $key => $value) {
                $filters[$key] = array('value' => (int) $value, 'placeholder' => orderNumber($value));
            }

            return $filters;
        });

        $this->view->assign($data);
        $this->view->assign('title', 'User');
        $this->view->display('admin/header_view');
        $this->view->display('admin/user/users_view');
        $this->view->display('admin/footer_view');
    }

    public function ajax_admin_dt()
    {
        checkAdminAjaxDT('users_administration');

        $request = request()->request;

        /** @var FilesystemProviderInterface $filesystemProvider */
        $filesystemProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $diskProvider = $filesystemProvider->storage('public.storage');

        /** @var Users_Complete_Profile_Options_Model $usersCompleteProfileOprionsModel */
        $usersCompleteProfileOprionsModel = model(Users_Complete_Profile_Options_Model::class);

        $users = $this->userDataListProvider->getDataList($request, false);
        $usersCount = $this->userDataListProvider->getDataCount($request);
        $groups = $this->userDataListProvider->getDataList($request, true);

        $output = [
            'sEcho'                => intval($request->getInt('sEcho')),
            'iTotalRecords'        => $usersCount,
            'iTotalDisplayRecords' => $usersCount,
            'groups_users_count'   => arrayByKey($groups, "user_group"),
            'google_map_data'      => [],
            'aaData'               => [],
        ];


		if (empty($users)) {
			jsonResponse("", "success", $output);
        }

        $haveRightManageMatchmaking = have_right('manage_matchmaking');

        $libPhoneUtils = PhoneNumberUtil::getInstance();

        $usersProfileOptions = $usersCompleteProfileOprionsModel->getUsersProfileOptions(array_column($users, 'idu')) ?: [];
        $haveRightUnmarkAsFake = have_right('unmark_user_as_fake');

        foreach ($users as $key => $user) {
            $dtCountry = [];
            $userId = (int) $user['idu'];
            $companyIcon = "";
            $companyTitle = "";
            $countryLabel = "--";
            $userDetail = [];
            $userFullname = trim("{$user['fname']} {$user['lname']}");

            $usersBlockingStatistics = [];
            if (!empty($user['account_limitation_statistics'])) {
                $usersBlockingStatistics = arrayByKey($user['account_limitation_statistics']->toArray(), 'type');
            }

            $countBlockedTimes = $usersBlockingStatistics['blocking']['counter'] ?? 0;
            $countRestrictedTimes = $usersBlockingStatistics['restriction']['counter'] ?? 0;

            $userFindInfo = '';
            switch ($user['user_find_type']) {
                case 'campaign':
                    $userFindInfo = '<br><span class="label label-primary">'.$user['user_campings']['campaign_name'].'</span>';
                break;
                case 'user':
                    $referrerUser = $this->users->getSimpleUser((int) $user['user_find_info']);
                    if (!empty($referrerUser)) {
                        $userFindInfo = '<br><a href="'.getUserLink($referrerUser['fname'].' '.$referrerUser['lname'], $referrerUser['idu'], $referrerUser['gr_type']).'" class="label label-primary" target="_blank">'.$referrerUser['fname'].' '.$referrerUser['lname'].'</a>';
                    }
                break;
            }

            $profileOptions = $usersProfileOptions[$user['idu']] ?: [];
			$profileStatus = [
                'registration_info' => "<div><span class=\"ep-icon ep-icon_ok-circle txt-green\"></span> Registration info (20%)</div>"
            ];

            $totalQuestions = count($profileOptions) + 1;
            $totalCompletedQuestions = 1;
            $totalCompleted = 20;
            $profileCompletionLabel = '';
            $legalNameLabel = '';

			if (!empty($profileOptions)) {
				foreach ($profileOptions as $profileOption) {
					$profileStatus[$profileOption['option_alias']] = '<div><span class="ep-icon '.(($profileOption['option_completed'] == 1)?'ep-icon_ok-circle txt-green':'ep-icon_minus-circle txt-red').'"></span> '. $profileOption['option_name'] .'</div>';

					if ($profileOption['option_completed'] == 1) {
						$totalCompleted += (int) $profileOption['option_percent'];
						$totalCompletedQuestions += 1;
					}
                }

                $userDetail[] = '<tr>
                                    <td class="w-100">Profile completed:</td>
                                    <td><div>Questions completed '.$totalCompletedQuestions.' of '.$totalQuestions.', total '.$totalCompleted.'% <br>Last updated: '.((validateDate($user['company_profile_updated'], 'Y-m-d H:i:s')) ? getDateFormat($user['company_profile_updated'], 'Y-m-d H:i:s', 'm/d/Y') : '&mdash;').'</div>' . implode('', $profileStatus).'</td>
                                  </tr>';

                if ($totalCompleted <= 20) {
                    $profileCompletionLabelClass = 'danger';
                } elseif ($totalCompleted < 100) {
                    $profileCompletionLabelClass = 'primary';
                } else {
                    $profileCompletionLabelClass = 'success';
                }

                $profileCompletionLabel = '<div class="completion-tooltip text-nowrap" data-tooltip-content="#profile_completion_'.$user['idu'].'">
                        <span class="label label-'.$profileCompletionLabelClass.'">'.$totalCompleted.'% ('.$totalCompletedQuestions.' of '.$totalQuestions.')</span>
                        <span class="label label-default">'.((validateDate($user['date_profile_updated']))?getDateFormat($user['date_profile_updated'], 'Y-m-d H:i:s', 'm/d/Y'):'&mdash;').'</span>
                    </div>
                    <div class="display-n"><div id="profile_completion_'.$user['idu'].'"><div>Questions completed '.$totalCompletedQuestions.' of '.$totalQuestions.', total '.$totalCompleted.'%</div>' . implode('', $profileStatus).'</div></div>'
                ;
			}

            $personalPageBtn = "";

            $userPhotoUrl = $diskProvider->url(
                UserPhotoPathGenerator::userMainPhotoImagePath($user['idu'], $user['user_photo'] ?: 'no-image.jpg')
            );

            $photo = "<img class='w-50 h-50 js-fs-image' src='" . $userPhotoUrl . "' alt='". $user["name_company"] ."'/>";

            if ($user["user_type"] == "user") {
                $personalPageBtn = "<a class='ep-icon ep-icon_user' title='View personal page of " . $user["fname"] . "' target='_blank' href='" . __SITE_URL . "usr/" . strForURL($user["fname"] . " " . $user["lname"]) . "-" . $user["idu"] . "'></a>";
            }

            $online = ($user["logged"]) ? "online" : "offline";

            if (!empty($user['showed_status'])) {
                $userDetail[] = '<tr>
                        <td class="w-100">Showed status:</td>
                        <td>' . $user['showed_status'] . '</td>
                    </tr>'
                ;
            }

            if (!empty($user['description'])) {
                $userDetail[] = '<tr>
                        <td class="w-100">Description:</td>
                        <td>' . $user['description'] . '</td>
                    </tr>'
                ;
            }

            if (!empty($user['seller_company']["name_company"])) {
                $companyLogoUrl = $diskProvider->url(
                    CompanyLogoFilePathGenerator::logoPath($user['seller_company']['id_company'], $user['seller_company']['logo_company'])
                );

                $companyUrl = getCompanyURL(
                    [
                        'id_company'    => $user['seller_company']['id_company'],
                        'name_company'  => $user['seller_company']['name_company'],
                        'type_company'  => $user['seller_company']['type_company'],
                        'index_name'    => $user['seller_company']['index_name']
                    ]
                );

                $companyIcon = "<a class='ep-icon ep-icon_building' title='View page of company " . cleanOutput($user['seller_company']["name_company"]) . "' target='_blank' href='" . $companyUrl . "'></a>";
                $companyTitle = "<div>(" . cleanOutput($user['seller_company']["name_company"]) . ")</div>";

                $userDetail[] = '<tr>
                    <td class="w-100">Company:</td>
                    <td>
                        <img class="pull-left mw-150 mh-150" src="' . $companyLogoUrl . '" alt="'.cleanOutput($user['seller_company']["name_company"]).'"/>
                        <div>
                            <div>
                                <strong>Display Name: </strong> <a target="_blank" href="' . $companyUrl . '">' . cleanOutput($user['seller_company']["name_company"]) . '</a>
                            </div>
                            <div>
                                <strong>Legal Name: </strong> ' . cleanOutput($user['seller_company']['legal_name_company'] ?? '-') . '
                            </div>
                            <div>
                                <strong>Rating: </strong>' . $user['seller_company']["rating_company"] . '
                            </div>
                        </div>
                    </tr>'
                ;
            }

            if ($user["user_type"] == "shipper" && null !== ($shipperCompany = $user['shipper_company'] ?? null)) {
                $companyIcon = "<a class='ep-icon ep-icon_building' title='View page of freight forwarder " . $shipperCompany["co_name"] . "' target='_blank' href='" . __SITE_URL."shipper/".strForUrl($shipperCompany["co_name"] ." ". $shipperCompany["id"]) . "'></a>";
                $companyTitle = "<div>(" . $shipperCompany["co_name"] . ")</div>";

                $shiperCompanyLogoUrl = $diskProvider->url(
                    CompanyLogoFilePathGenerator::shiperLogoPath($shipperCompany['id'], $shipperCompany['logo'])
                );

                $userDetail[] = '<tr>
                    <td class="w-100">Company:</td>
                    <td>
                        <img class="pull-left mw-150 mh-150" src="' . $shiperCompanyLogoUrl . '" alt="'.$shipperCompany["co_name"].'"/>
                        <div>
                            <div>
                                <strong>Display Name: </strong> <a target="_blank" href="' . __SITE_URL ."shipper/".strForUrl($shipperCompany["co_name"] ." ". $shipperCompany["id"]) . '">' . $shipperCompany["co_name"] . '</a>
                            </div>
                            <div>
                                <strong>Legal Name: </strong> ' . cleanOutput($user['legal_name_company'] ?? '-') . '
                            </div>
                        </div>
                    </td>
                    </tr>'
                ;
            }

            $emailStatusLabel = '<br>
                <span class="label label-' . $this->userDataListProvider->emailStatusesLabels[(string) $user['email_status']] . '" title="Email status: ' . $user['email_status'] . '"
                >' . $user['email_status'] . '</span>'
            ;

            if (!empty($user["country"])) {
                $countryId = $user["country"];
                $countryName = cleanOutput($user['location_country']["country"]);
                $countryFlag = getCountryFlag($user['location_country']["country"]);
                $countryCode = $user['personal_phone_code']['ccode'];
                $locationWarning = null;

                if (empty($user['state']) || empty($user['city'])) {
                    $locationWarning = <<<WARNING
                            <br><span class="label label-danger">Incompleted location</span>
                        WARNING
                    ;
                }

                $dtCountry[] = $countryLabel = <<<COUNTRY
                        <a class="dt_filter" data-value-text="{$countryName}" data-value="{$countryId}" data-title="Country" data-name="country">
                            <img width="24" height="24" src="{$countryFlag}" title="{$countryName} {$countryCode}" alt="{$countryName}">
                        </a>
                    {$locationWarning}
                    COUNTRY
                ;

                if (!empty($user['location_city']['timezone'])) {
                    try {
                        $userDateTime = (new DateTime())->setTimezone(new DateTimeZone($user['location_city']['timezone']));

                        $dtCountry[] = "<span title=\"Timezone\">{$userDateTime->format('e P')}</span>";
                        $dtCountry[] = "<span title=\"Current time\">{$userDateTime->format('j M, Y H:i')}</span>";
                    } catch (\Throwable $th) {
                        //do nothing
                    }
                }

                $userDetail[] = <<<DETAILS
                    <tr>
                        <td class="w-100">Country:</td>
                        <td>{$countryLabel}</td>
                    </tr>
                    DETAILS
                ;
            } else {
                $countryLabel .= '<br><span class="label label-danger">Incompleted location</span>';
                $userDetail[] = <<<DETAILS
                    <tr>
                        <td class="w-100">Country:</td>
                        <td><br><span class="label label-danger">Incompleted location</span></td>
                    </tr>
                    DETAILS
                ;
            }

            if (!empty($customLocation = $user['user_location']['location'] ?? null)) {
                $customLocation = cleanOutput($customLocation);
                $userDetail[] = <<<CUSTOM_LOCATION
                    <tr>
                        <td class="w-100">Custom Location:</td>
                        <td>{$customLocation}</td>
                    </tr>
                    CUSTOM_LOCATION
                ;
            }

            if (!empty($user['legal_name'])) {
                $legalNameLabel = '<span class="label label-default">'.$user['legal_name'].'</span>';
            }

            $userDetail[] = '<tr>
                    <td class="w-100">Address:</td>
                    <td>' . $user["address"] . ", " . $user["zip"] . ", " . $user["location_state"]['state'] . ", " . $user["location_country"]['country'] ."</td>
                </tr>"
            ;

            $userDetail[] = '<tr>
                    <td class="w-100">Email:</td>
                    <td>' . $user["email"]."</td>
                </tr>"
            ;

            $userDetail[] = '<tr>
                    <td class="w-100">IP:</td>
                    <td>' . $user["user_ip"] ."</td>
                </tr>"
            ;

            if (!empty($user["phone"])) {
                try {
                    $phoneNumber = $libPhoneUtils->parse("{$user['phone_code']} {$user['phone']}");
                    $internationalFormat = $libPhoneUtils->format($phoneNumber, PhoneNumberFormat::INTERNATIONAL);
                } catch (NumberParseException $e) {
                    $internationalFormat = "{$user['phone_code']} {$user['phone']}";
                }

                $userDetail[] = '<tr>
                        <td class="w-100">Phone:</td>
                        <td>' . $internationalFormat . "</td>
                    </tr>"
                ;
            }

            if (!empty($user["fax"])) {
                $userDetail[] = '<tr>
                        <td class="w-100">Fax:</td>
                        <td>' . $user["fax_code"] ." ". $user["fax"]."</td>
                    </tr>"
                ;
            }

            $fakeButton = '';
            $modelButton = '';
            $blockButton = '';

            if ($user['fake_user'] == 0) {
                $fakeButton = <<<MAKE_USER_FAKE_BUTTON
                    <li>
                        <a data-callback="fakeUser" data-user="$userId"
                            class="confirm-dialog"
                            data-message="Are you sure you want to mark this user as demo?">
                            <i class="ep-icon ep-icon_minus-circle"></i> Mark as demo
                        </a>
                    </li>
                    MAKE_USER_FAKE_BUTTON
                ;
            } elseif ($haveRightUnmarkAsFake) {
                $fakeButton = <<<MAKE_USER_FAKE_BUTTON
                    <li>
                        <a data-callback="fakeUser" data-user="$userId"
                            class="confirm-dialog"
                            data-message="Are you sure you want to Mark as real this user?">
                            <i class="ep-icon ep-icon_smile"></i> Mark as real
                        </a>
                    </li>
                    MAKE_USER_FAKE_BUTTON
                ;
            }

            if ($user['is_model'] == 0) {
                $modelButton = <<<MAKE_USER_MODEL_BUTTON
                    <li>
                        <a data-callback="modelUser" data-user="$userId"
                            class="confirm-dialog"
                            data-message="Are you sure you want to mark this user as model?">
                            <i class="ep-icon ep-icon_user-staff"></i> Mark as model
                        </a>
                    </li>
                    MAKE_USER_MODEL_BUTTON
                ;
            } else {
                $modelButton = <<<MAKE_USER_MODEL_BUTTON
                    <li>
                        <a data-callback="modelUser" data-user="$userId"
                            class="confirm-dialog"
                            data-message="Are you sure you want to unmark this user as model?">
                            <i class="ep-icon ep-icon_user-staff txt-red"></i> Unmark as model
                        </a>
                    </li>
                    MAKE_USER_MODEL_BUTTON
                ;
            }

            if (UserStatus::BLOCKED() === $user['status']) {
                $blockButton = '<li>
                        <a class="confirm-dialog" data-message="Are you sure you want to unblock this user?" data-callback="unblockUser" data-user="' . $user["idu"] . '">
                            <span class="ep-icon ep-icon_unlocked"></span> Unblock user
                        </a>
                    </li>'
                ;
            } elseif (UserStatus::RESTRICTED() !== $user['status']) {
                $blockButton = '
                    <li>
                        <a
                            class="fancyboxValidateModalDT fancybox.ajax"
                            href="users/popup_forms/block_user/'.$user['idu'].'"
                            data-user="'.$user['idu'].'"
                            data-title="Block user '.$userFullname.'"
                        >
                            <span class="ep-icon ep-icon_locked"></span> Block user
                        </a>
                    </li>'
                ;
            }

            $restrictButton = '';
            if (UserStatus::RESTRICTED() === $user['status']) {
                $restrictButton = '<li>
                        <a class="confirm-dialog" data-message="Are you sure you want to un-Restrict this user?" data-callback="unRestrictUser" data-user="' . $user["idu"] . '">
                            <span class="ep-icon ep-icon_user-ok"></span> un-Restrict user
                        </a>
                    </li>'
                ;
            }

            if (UserStatus::DELETED() === $user['status']) {
                $restoreButton =
                    sprintf(
                        <<<RESTORE_BUTTON
                        <li>
                            <a href="%s"
                                class="fancybox.ajax fancyboxValidateModalDT"
                                title="Restore user"
                                data-title="Restore user">
                                <i class="ep-icon ep-icon_updates txt-green"></i> Restore user
                            </a>
                        </li>
                        RESTORE_BUTTON,
                        getUrlForGroup("/users/popup_forms/restore_user/{$userId}")
                    )
                ;
            }

            if (!empty($user["user_photos_list"])) {
                $photosList = [];
                foreach ($user["user_photos_list"] as $photoItem) {

                    $photoListPhotoUrl = $diskProvider->url(
                        UserPhotoPathGenerator::userMainPhotoImagePath($user['idu'], $photoItem['name_photo'])
                    );

                    $photosList[] = '<div class="img-list-b pull-left mr-5 mb-5 relative-b">
                        <img src="' . $photoListPhotoUrl . '" alt="img"/>
                        <a class="ep-icon ep-icon_remove txt-red absolute-b pos-r0 m-0 bg-white confirm-dialog" data-message="Are you sure you want to delete the user photo?" title="Delete user foto" data-callback="delete_user_image" data-image="' . $photoItem["id_photo"] . '" data-user="' . $user["idu"] . '"></a></div>'
                    ;
                }

                $photosList = implode("", $photosList);
                $userDetail[] = '<tr>
                        <td class="w-100">Photos:</td>
                        <td>' . $photosList ."</td>
                    </tr>"
                ;
            }

            $statisicsBtn = "";
            if (UserStatus::FRESH() !== $user['status']) {
                $statisicsBtn = '<a class="ep-icon ep-icon_statistic fancybox fancybox.ajax" href="' . __SITE_URL . "users/popup_show_statistic/" . $user['idu'] . '/"  title="Statistics" data-title="Statistics for user '. $user["fname"] . " " . $user["lname"] .'"></a>'
                ;
            }

			$checkbox = '<input type="checkbox" class="check-user mt-1" data-user="' . $user["idu"] . '">';

            $exploreUserBtn = "";
            if (have_right("login_as_user")) {
                $exploreUserBtn = '<li>
                        <a class="confirm-dialog" data-message="Are you sure you want to explore user '. $user["fname"] . " " . $user["lname"] .'?" data-callback="explore_user" data-user="'.$user["idu"].'" href="#" data-title="Login as '. $user["fname"] . " " . $user["lname"] .'">
                            <span class="ep-icon ep-icon_login"></span> Explore user
                        </a>
                    </li>'
                ;
            }

            $fakeUser = '<a
                    class="ep-icon ep-icon_minus-circle txt-red dt_filter"
                    title="' . translate('ep_administration_demo_user_text', null, true) . '"
                    data-value="1"
                    data-value-text="' . translate('ep_administration_demo_users_text', null, true) . '"
                    data-title="' . translate('ep_administration_demo_real_users_text', null, true) . '"
                    data-name="fake_user">
                </a>'
            ;

            if ($user["fake_user"] == 0) {
                $fakeUser = '<a
                        class="ep-icon ep-icon_smile txt-green dt_filter"
                        title="' . translate('ep_administration_real_user_text', null, true) . '"
                        data-value="0"
                        data-value-text="' . translate('ep_administration_real_users_text', null, true) . '"
                        data-title="' . translate('ep_administration_demo_real_users_text', null, true) . '"
                        data-name="fake_user">
                    </a>'
                ;
            }

            $modelUser = '';
            if ($user["is_model"] == 1) {
                $modelUser = '<a class="ep-icon ep-icon_user-staff txt-green dt_filter" title="Model users" data-value="1" data-value-text="Yes" data-title="Model Users" data-name="is_model"></a>'
                ;
            }

            $crmUser = '<a class="ep-icon ep-icon_user-card txt-red dt_filter" title="Not in CRM" data-value="0" data-value-text="Not in CRM" data-title="In CRM:NO" data-name="in_crm"></a>';
            if (!empty($user['zoho_id_record'])) {
                $crmUser = '<a class="ep-icon ep-icon_user-card txt-green dt_filter" title="Yes, in CRM" data-value="1" data-value-text="Yes, in CRM" data-title="In CRM:YES" data-name="in_crm"></a>'
                ;
            }

            $industriesOfInterestBtn = "";
            if ($user['user_group_data']['gr_type'] == 'Buyer') {
                $industriesOfInterestBtn = '<li>
                        <a class="fancybox fancybox.ajax" data-callback="explore_user" data-user="'.$user["idu"].'" href="'.__SITE_URL.'categories/popup_forms/industries_of_interest/'.$user["idu"].'" data-title="Industries of interest for '.$user["fname"] . " " . $user["lname"].'" title="Industries of interest '.$user["fname"] . " " . $user["lname"].'">
                            <span class="ep-icon ep-icon_connection"></span> Industries of interest
                        </a>
                    </li>'
                ;
            }

            $sessionLogsBtn = '';
            if (have_right('view_session_logs')) {
                $sessionLogsBtn = '<li>
                        <a class="fancyboxValidateModalDT fancybox.ajax" href="'.__SITE_URL.'session_logs/popup_forms/by_user/'.$user["idu"].'" data-title="Session logs for '. $user["fname"] . " " . $user["lname"] .'">
                            <span class="ep-icon ep-icon_clock"></span> Session Logs
                        </a>
                    </li>'
                ;
            }

            $confirmButton = null;
            if (UserStatus::FRESH() === $user['status']) {
                $confirmButtonMessage = sprintf("Are you sure you want to confirm user %s?", cleanOutput($userFullname));
                $confirmButton = "
                    <li>
                        <a
                            href=\"#\"
                            class=\"confirm-dialog\"
                            data-callback=\"confirm_user\"
                            data-user=\"{$user['idu']}\"
                            data-message=\"{$confirmButtonMessage}\">
                            <span class=\"ep-icon ep-icon_ok txt-green\"></span> Confirm user
                        </a>
                    </li>
                ";
            }

            $editCompanyNameButton = null;
            if (!empty($user['seller_company']) || !empty($user['shipper_company']) || !empty($user['buyer_company'])) {
                switch (true) {
                    case !empty($user['seller_company']):
                        $companyId = (int) $user['seller_company']['id_company'];
                        $companyType = 'seller';
                    break;
                    case isset($user['shipper_company']):
                        $companyId = (int) $user['shipper_company']['id'];
                        $companyType = 'shipper';
                    break;
                    case isset($user['buyer_company']):
                        $companyId = (int) $user['buyer_company']['id'];
                        $companyType = 'buyer';
                    break;
                }

                $editCompanyNameButton = sprintf(
                    <<<EDIT_COMANY_NAME_BUTTON
                    <li>
                        <a href="%s"
                            class="fancybox.ajax fancyboxValidateModalDT"
                            title="Edit company name"
                            data-title="Edit company name">
                            <i class="ep-icon ep-icon_file-edit"></i> Edit company name
                        </a>
                    </li>
                    EDIT_COMANY_NAME_BUTTON,
                    getUrlForGroup("/company/popup_forms/edit_company_name/{$companyId}?type={$companyType}")
                );
            }

            $activateButton = '';
            if (UserStatus::PENDING() === $user['status']) {
                $activateButton = '<li>
                        <a class="confirm-dialog"
                            data-callback="activateUser"
                            data-user="' . $user["idu"] . '"
                            data-message="Are you sure you want to activate this user?"
                            data-title="Activate user '. $user["fname"] . " " . $user["lname"] .'"
                            href="#"
                            title="Activate user"
                        >
                            <span class="ep-icon ep-icon_user"></span> Activate user
                        </a>
                    </li>'
                ;
            }

            //region Delete user button
            $deleteButton = '';
            if (UserStatus::FRESH() === $user['status']) {
                $deleteButton = '<li>
                        <a class="confirm-dialog"
                            data-callback="delete_user"
                            data-user="' . $user["idu"] . '"
                            title="Delete user"
                            data-message="Are you sure you want to delete user: '. $user["fname"] . ' ' . $user["lname"] .'"
                        >
                            <span class="ep-icon ep-icon_remove txt-red"></span> Delete user
                        </a>
                    <li>'
                ;
            }
            //endregion Delete user button

            //region logs btn
            $logsBtn = <<<VIEW_LOG_BUTTON
                    <li>
                        <a
                            class="fancybox fancybox.ajax"
                            href="users/popup_forms/view_log/{$user['idu']}"
                            data-title="View {$userFullname}'s log"
                        >
                            <span class="ep-icon ep-icon_verification"></span> View log
                        </a>
                    </li>
                VIEW_LOG_BUTTON
            ;
            //endregion logs btn

            //region Matchmaking button
            $matchmakingButton = '';
            $matchmakingEmailStatusButton = '';
            $sendMatchmakingEmailButton = '';
            if ($haveRightManageMatchmaking && !$user['fake_user'] && !$user['is_model'] && in_array(strtolower($user['user_group_data']['gr_type']), ['buyer', 'seller'])) {
                $matchmakingButton = <<<MATCHMAKING_BUTTON
                        <li>
                            <a href="matchmaking/user/{$user['idu']}" target="_blank">
                            <i class="ep-icon ep-icon_group-stroke"></i> Matchmaking
                            </a>
                        </li>
                    MATCHMAKING_BUTTON
                ;

                $buttonIcon = $user['accept_matchmaking_email'] ? 'ep-icon_envelope' : 'ep-icon_envelope-stroke';
                $buttonText = $user['accept_matchmaking_email'] ? 'Stop sending matchmaking' : 'Start sending matchmaking';

                $matchmakingEmailStatusButton = <<<MATCHMAKING_EMAIL_STATUS_BUTTON
                        <li>
                            <a class="call-function" href="" data-callback="sendingMatchmakingEmail" data-user="{$user['idu']}">
                                <i class="ep-icon {$buttonIcon}"></i> {$buttonText}
                            </a>
                        </li>
                    MATCHMAKING_EMAIL_STATUS_BUTTON
                ;

                $sendMatchmakingEmailButton = <<<SEND_MATCHMAKING_EMAIL_BUTTON
                        <li>
                            <a class="confirm-dialog" data-message="Are you sure you want to send an email to {$user['fname']} {$user['lname']}?" data-callback="sendMatchmakingEmail" data-user="{$user['idu']}">
                                <i class="ep-icon ep-icon_envelope-send"></i> Send matchmaking email
                            </a>
                        </li>
                    SEND_MATCHMAKING_EMAIL_BUTTON
                ;
            }

            //endregion Matchmaking button

            $actionsDt = '<div class="dropdown">
                                <a class="ep-icon ep-icon_menu-circles dropdown-toggle" type="button" data-toggle="dropdown" ' . addQaUniqueIdentifier('admin-users__datatable__verification-dropdown-toggle-button') . '></a>
                                <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu2">
                                    <li>
                                        <a class="fancyboxValidateModalDT fancybox.ajax" ' . addQaUniqueIdentifier('admin-users__datatable__verification-dropdown__verification-button') . ' href="'.__SITE_URL.'verification/popup_forms/user_verification_documents/' . $user['idu'] . '" id="user-' . $user['idu'] . '" title="Verification documents" data-title="Verification documents of '. $user['fname'] . " " . $user['lname'] .'">
                                            <span class="ep-icon ep-icon_items"></span> Verification documents
                                        </a>
                                    </li>
                                    '.$restoreButton.'
                                    '.$matchmakingButton.'
                                    '.$sendMatchmakingEmailButton.'
                                    '.$matchmakingEmailStatusButton.'
                                    '.$confirmButton.'
                                    '.$activateButton.'
                                    '.$fakeButton.'
                                    '.$modelButton.'
                                    '.$blockButton.'
                                    '.$restrictButton.'
                                    '.$deleteButton.'
                                    '.$editCompanyNameButton.'
                                    <li>
                                        <a href="'.__SITE_URL.'admin?user=' . $user['idu'] . '" title="User\'s activity logs" target="_blank">
                                            <span class="ep-icon ep-icon_text-more"></span> Activity logs
                                        </a>
                                    </li>
                                    '.$sessionLogsBtn.'
                                    '.$industriesOfInterestBtn.'
                                    '.$exploreUserBtn.'
                                    '.$logsBtn.'
                                    <li>
                                        <a class="fancyboxValidateModalDT fancybox.ajax" href="'.__SITE_URL.'users/popup_forms/send_multi_email/'.$user["idu"].'" data-title="Email '. $user["fname"] . " " . $user["lname"] .' using template">
                                            <span class="ep-icon ep-icon_envelope-send txt-green"></span> Email using template
                                        </a>
                                    </li>
                                </ul>
                            </div>';


            $output["aaData"][] = [
				"dt_idu"	    => $user["idu"]."<br/>".$checkbox." <a rel='user_details' title='View details' class='ep-icon ep-icon_plus'></a>",
                "dt_fullname"   => "<div class='tal'>"
                                        ."<a class='ep-icon ep-icon_onoff " . (($user["logged"]) ? "txt-green" : "txt-red") . " dt_filter' title='Filter just " . $online . "' data-value='" . $user['logged'] . "' data-name='online'></a>"
                                        .$personalPageBtn
                                        .'<a class="ep-icon ep-icon_envelope-send fancyboxValidateModal fancybox.ajax" href="' . __SITE_URL . 'contact/popup_forms/email_user/'.$user['idu'].'" title="Email '. $user['fname'] . " " . $user['lname'] .'" data-title="Email '. $user['fname'] . " " . $user['lname'] .'"></a>'
                                        .$companyIcon . $fakeUser . $modelUser . $crmUser .
                                        "<span class=\"label label-danger\" title=\"Blocked {$countBlockedTimes} times\">{$countBlockedTimes}</span>" .
                                        "<span class=\"label label-warning\" title=\"Restricted {$countRestrictedTimes} times\">{$countRestrictedTimes}</span>"
                                    . "</div>"
                                    . "<div>" . $user["fname"] . " " . $user["lname"] . "</div>"
                                    . $legalNameLabel
                                    . $companyTitle,
                "dt_email"      => $user["email"] . $emailStatusLabel,
                "dt_country"    => implode('<br>', $dtCountry),
                "dt_gr_name"    => "<div class='tal'>"
                                    . "<a class='ep-icon ep-icon_filter txt-green dt_filter' title='Group " . $user['user_group_data']['gr_name'] . "' data-value='" . $user["user_group"] . "' data-name='group' data-title='Group' data-value-text='" . $user['user_group_data']['gr_name'] . "'></a>"
                                . "</div>"
                                . capitalWord($user['user_group_data']['gr_name'])
                                . '<div>'.$profileCompletionLabel.'</div>'
                                . "<div class='tac mt-5'>"
                                    . "<a class=\"btn btn-primary btn-sm fancyboxValidateModalDT fancybox.ajax\" " . addQaUniqueIdentifier('admin-users__datatable__verification-button') . " href=\"".__SITE_URL."verification/popup_forms/user_verification_documents/" . $user['idu'] . "\" id=\"user-" . $user['idu'] . "\" title=\"Verification documents\" data-title=\"Verification documents of {$user['fname']} {$user['lname']}\">
                                            Verification
                                        </a>"
                                . "</div>",
                "dt_reset_pass_date" => '<div class="tac">' . getDateFormatIfNotEmpty($user["reset_password_date"]) . '</div>',
                "dt_registered" => getDateFormatIfNotEmpty($user["registration_date"], DB_DATE_FORMAT, PUBLIC_DATETIME_FORMAT) . $userFindInfo,
				"dt_resend_email_date" => getDateFormatIfNotEmpty($user["resend_email_date"], DB_DATE_FORMAT, PUBLIC_DATETIME_FORMAT),
                "dt_activity"   => getDateFormatIfNotEmpty($user["last_active"], DB_DATE_FORMAT, PUBLIC_DATETIME_FORMAT),
                "dt_status"     => '<div>' . (null !== $user['cancellation_requests_status'] ? '<span class="label label-danger">Cancelation Request</span><br>' : '') . $this->statuses[(string) $user['status']]['label'] . (empty($user['status_temp']) ? '' : '<br>Previous Status<br>' . $this->statuses[(string) $user['status_temp']]['label']) . '</div>',
                "dt_records"    => '<a class="ep-icon ep-icon_notice fancyboxValidateModal fancybox.ajax" title="Notices" href="' . __SITE_URL . "users/popup_show_notice/" . $user["idu"] . '" data-title="Notice for user '. $user["fname"] . " " . $user["lname"] .'"></a>'
                                    .$statisicsBtn,
                "dt_actions"    => $actionsDt,
                "dt_photo"      => $photo,
                "dt_detail"     => implode("", $userDetail)
            ];
        }

        if ($request->getInt('get_gmap_users') > 0) {
            $gmapUsers = $this->userDataListProvider->getDataList($request, false);
            foreach ($gmapUsers as $gmapUser) {
                if (empty($user["user_city_lat"]) || empty($user["user_city_lng"])) {
                    continue;
                }

                $output['gmap_data'][] = [
                    'latitude'  => $gmapUser['user_city_lat'],
                    'longitude' => $gmapUser['user_city_lng'],
                    'id_city'   => $gmapUser['city'],
                    'user_id'   => $gmapUser['idu'],
                    'user_name' => $gmapUser['user_name'],
                ];
            }
        } else {
            $output["gmap_data"] = [];
        }

        jsonResponse("", "success", $output);
    }

    public function ep_staff()
    {
        checkAdmin('ep_staff_administration');

        $this->load->model('Usergroup_Model', 'usergroup');
        $this->load->model('User_Model', 'users');

        $data['groups'] = $this->usergroup->getGroupsByType(array('type' => "'EP Staff','Admin'"));
        $group = $this->uri->segment(4);
        if ($group) {
            $data['group'] = $group;
        }

        $data['filter_country'] = false;
        $this->view->assign($data);
        $this->view->assign('title', 'EP Staff');
        $this->view->display('admin/header_view');
        $this->view->display('admin/user/ep_staff_view');
        $this->view->display('admin/footer_view');
    }

    function ajax_staff_dt() {
        checkIsAjax();
        checkPermisionAjaxDT('manage_content');

        $this->load->model('User_Model', 'users');
        $this->load->model('Usergroup_Model', 'usergroup');
        $this->load->model('User_Photo_Model', 'userphoto');

        $request = request()->request;

        $user_params = array(
            'per_p' => intVal($_POST['iDisplayLength']),
            'start' => intVal($_POST['iDisplayStart']),
            'additional' => true,
            'company_info' => true,
            'city_detail' => true
        );

        if ($request->has('id_user')) {
            $user_params['id_user'] = $request->getInt('id_user');
        }

        if (!empty($_POST['search']))
            $user_params['keywords'] = cleanInput($_POST['search']);


        if (isset($_POST['group']))
            $user_params['group'] = intval($_POST['group']);
        else {

            $groups = $this->usergroup->getGroupsByType(array('type' => "'EP Staff','Admin'", 'counter' => false));
            $lists = arrayToListKeys($groups, array('idgroup'));
            $user_params['group'] = $lists['idgroup'];
        }

        if (isset($_POST['ip']))
            $user_params['ip'] = cleanInput($_POST['ip']);

        if (isset($_POST['status']))
            $user_params['status'] = "'" . cleanInput($_POST['status']) . "'";

        if (isset($_POST['email_status']))
            $user_params['email_status'] = "'" . cleanInput($_POST['email_status']) . "'";

        if (isset($_POST['online'])) {
            $user_params['logged'] = intval($_POST['online']);
        }

        if (isset($_POST['date_type'])) {
            if ($_POST['date_type'] == 'registered') {
                if (isset($_POST['start_date']))
                    $user_params['registration_start_date'] = $_POST['start_date'];

                if (isset($_POST['end_date']))
                    $user_params['registration_end_date'] = $_POST['end_date'];
            }elseif ($_POST['date_type'] == 'activity') {
                if (isset($_POST['start_date']))
                    $user_params['activity_start_date'] = $_POST['start_date'];

                if (isset($_POST['end_date']))
                    $user_params['activity_end_date'] = $_POST['end_date'];
            }
        }

        if ($_POST['iSortingCols'] > 0) {
            for ($i = 0; $i < $_POST['iSortingCols']; $i++) {
                switch ($_POST['iSortCol_' . $i]) {
                    case '1': $user_params['sort_by'][] = 'CONCAT(fname, lname)-' . $_POST['sSortDir_' . $i];
                        break;
                    case '2': $user_params['sort_by'][] = 'email-' . $_POST['sSortDir_' . $i];
                        break;
                    case '5': $user_params['sort_by'][] = 'last_active-' . $_POST['sSortDir_' . $i];
                        break;
                    case '4':
                    default:
                        $user_params['sort_by'][] = 'registration_date-' . $_POST['sSortDir_' . $i];
                        break;
                }
            }
        }

        $user_params['count'] = $this->users->count_users($user_params);
        $users_total_counter = $this->users->count_users();
        $users = $this->users->getUsers($user_params);

        $output = array(
            "sEcho" => intval($_POST['sEcho']),
            "iTotalRecords" => $users_total_counter,
            "iTotalDisplayRecords" => $user_params['count'],
            "aaData" => array()
        );

		if(empty($users))
			jsonResponse('', 'success', $output);

        $users = arrayByKey($users, 'idu');
        $online_statuses = array(
            array(
                'color_class'  => "txt-red",
                "filter_title" => "Filter offline users",
            ),
            array(
                'color_class'  => "txt-green",
                "filter_title" => "Filter online users",
            ),
        );

        $email_status_labels = $this->users->get_emails_status_labels();
        $haveRightBlockEpStaff = have_right('block_ep_staff');
        $haveRightEditEpStaff = have_right('edit_ep_staff');
        foreach ($users as $key => $user) {
            $online = isset($online_statuses[$user['logged']]) ? $online_statuses[$user['logged']] : $online_statuses[0];

            $explore_user_btn = "";
            if(have_right("login_as_user")){
                $explore_user_btn = '<a class="ep-icon ep-icon_login confirm-dialog" data-message="Are you sure you want to explore user '. $user["fname"] . " " . $user["lname"] .'?" data-callback="explore_user" data-user="'.$user["idu"].'" title="Login as '. $user["fname"] . " " . $user["lname"] .'" href="#" data-title="Login as '. $user["fname"] . " " . $user["lname"] .'"></a>';
            }

            $email_status_label = '<br>
                                    <span
                                        class="label label-' . $email_status_labels[$user['email_status']]. '"
                                        title="Email status: ' . $user['email_status'] . '"
                                    >' . $user['email_status'] . '</span>';

            //TODO: admin chat hidden
            $btnChatUser = new ChatButton(['hide' => true, 'recipient' => $user['idu'], 'recipientStatus' => $user['status']], ['classes' => 'btn-chat-now', 'text' => '']);
            $btnChat = $btnChatUser->button();

            $deleteEpStaffBtn = null;
            if(have_right('delete_ep_staff') && 'deleted' !== $user['status']){
                $deleteEpStaffBtn = '<a class="ep-icon ep-icon_minus-circle txt-red confirm-dialog" data-message="Are you sure you want to delete user '. $user["fname"] . " " . $user["lname"] .'?" data-callback="deleteEpStaff" data-user="'.$user["idu"].'" title="Delete EP Staff account: '. $user["fname"] . " " . $user["lname"] .'" href="#" data-title="Delete EP Staff account: '. $user["fname"] . " " . $user["lname"] .'"></a>';
            }

            $blockUserBtn = '';
            if ($haveRightBlockEpStaff && !is_my($user['idu'])) {
                if ('blocked' === $user['status']){
                    $blockUserBtn = sprintf(
                        <<<BLOCK_BTN
                            <a class="confirm-dialog" data-message="%s" data-callback="unblockUser" data-user="{$user['idu']}" title="%s">
                                <span class="ep-icon ep-icon_unlocked"></span>
                            </a>
                        BLOCK_BTN,
                        'Are you sure you want to unblock this user?',
                        cleanOutput("Unblock user {$user['fname']} {$user['lname']}"),
                    );
                } elseif ('active' === $user['status']) {
                    $blockUserBtn = sprintf(
                        <<<BLOCK_BTN
                            <a class="fancyboxValidateModalDT fancybox.ajax" href="%s" data-user="{$user['idu']}" data-title="%s" title="%s">
                                <span class="ep-icon ep-icon_locked"></span>
                            </a>
                        BLOCK_BTN,
                        __SITE_URL . "users/popup_forms/block_ep_staff/{$user['idu']}",
                        cleanOutput("Block user {$user['fname']} {$user['lname']}"),
                        cleanOutput("Block user {$user['fname']} {$user['lname']}")
                    );
                }
            }

            $editUserBtn = '';
            if ($haveRightEditEpStaff) {
                $editUserBtn = sprintf(<<<EDIT_BTN
                    <a class="ep-icon ep-icon_pencil fancyboxValidateModalDT fancybox.ajax" title="Edit user" href="%s" id="user-{$user['idu']}"  data-title="Edit user info %s"></a>
                    EDIT_BTN,
                    __SITE_URL . "user/popup_forms/edit_ep_staff/{$user['idu']}",
                    cleanOutput($user['fname'] . ' ' . $user['lname'])
                );
            }

            $output['aaData'][] = array(
                "dt_idu" => $user['idu'],
                "dt_online" => "<a class='ep-icon ep-icon_onoff " . $online['color_class'] . " dt_filter' title='" . $online['filter_title'] . "' data-value='" . $user['logged'] . "' data-name='online'></a>",
                "dt_fullname" => $user['fname'] . " " . $user['lname'],
                "dt_email" => $user['email'] . $email_status_label,
                "dt_gr_name" => "<div class='pull-left'>"
                                . "<a class='ep-icon ep-icon_filter txt-green dt_filter' title='Group " . $user['gr_name'] . "' data-value='" . $user['user_group'] . "' data-name='group' data-title='IP' data-value-text='" . $user['gr_name'] . "'></a>"
                                . "</div>"
                                . "<div class='clearfix'></div>"
                                . capitalWord($user['gr_name']),
                "dt_ip" => "<div class='pull-left'>"
                            . "<a class='ep-icon ep-icon_filter txt-green dt_filter' title='IP:" . $user['user_ip'] . "' data-value='" . $user['user_ip'] . "' data-name='ip' data-title='IP' data-value-text='" . $user['user_ip'] . "'></a>"
                            . "</div>"
                            . "<div class='clearfix'></div>"
                            . "<span>" . $user['user_ip'] . "</span>",
                "dt_registered" => formatDate($user['registration_date']),
                "dt_activity" => formatDate($user['last_active']),
                "dt_status" => "<div class='pull-left'>"
                                . "<a class='ep-icon ep-icon_filter txt-green dt_filter' title='Filter just " . capitalWord($user['status']) . "' data-value='" . $user['status'] . "' data-name='status'></a>"
                                . "</div>"
                                . "<div class='clearfix'></div>"
                                . "<span>" . capitalWord($user['status']) . "</span>",
                "dt_actions" => $btnChat
                                . '<a title="Notices" href="' . __SITE_URL . 'users/popup_show_notice/' . $user['idu'] . '/" class="ep-icon ep-icon_notice fancyboxValidateModal fancybox.ajax" data-title="Add notice for '.$user['fname'] . " " . $user['lname'].'"></a>'
                                . $editUserBtn
                                . $explore_user_btn
                                . $deleteEpStaffBtn
                                . $blockUserBtn
            );
        }

        jsonResponse('', 'success', $output);
    }

    function ajax_add_notice() {
        if (!isAjaxRequest())
            headerRedirect();

        if (!logged_in())
            jsonResponse(translate("systmess_error_should_be_logged"));

        if (!have_right('manage_content'))
            jsonResponse(translate("systmess_error_rights_perform_this_action"));

        $this->load->model('User_model', 'users');

        $iduser = intval($_POST['user']);
        $notice = array(
            'add_date' => date('Y/m/d H:i:s'),
            'add_by' => $this->session->fname . ' ' . $this->session->lname,
            'notice' => cleanInput($_POST['notice'])
        );

        if (!$this->users->exist_user($iduser))
            jsonResponse(translate("systmess_error_user_does_not_exist"));

        $content = '<li class="pb-5 pt-5 bdb-1-gray lh-16 txt-blue"><strong>' . $notice['add_date'] . '</strong> - <u>by ' . $notice['add_by'] . '</u> : ' . $notice['notice'] . '</li>';
        if ($this->users->set_notice($iduser, $notice))
            jsonResponse('Notice has been added successfully', 'success', array('content' => $content));
        else
            jsonResponse('Error: Failed to add the notice. Please try again later.');
    }

    function popup_show_statistic() {
        if (!isAjaxRequest())
            headerRedirect();

        if (!logged_in())
            messageInModal(translate("systmess_error_should_be_logged"));

        if (!have_right('manage_content'))
            messageInModal(translate("systmess_error_rights_perform_this_action"));

        $iduser = $this->uri->segment(3);

        $this->load->model('User_model', 'users');

        $data['user'] = $this->users->getSimpleUser($iduser, 'users.idu, users.fname, users.lname, users.user_group');
        if (!count($data['user']))
            messageInModal(translate("systmess_error_user_does_not_exist"));

        $this->load->model('User_Statistic_Model', 'statistic');
        $data['statistics'] = $this->statistic->get_user_statistic($iduser, $data['user']['user_group']);
        $data['statistic_names'] = arrayByKey($this->statistic->getStatistiColumns(), 'Field');
        $this->view->display('admin/user/statistic/by_user_view', $data);
    }

    function popup_show_notice() {
        if (!isAjaxRequest())
            headerRedirect();

        if (!logged_in())
            messageInModal(translate("systmess_error_should_be_logged"));

        if (!have_right('manage_content'))
            messageInModal(translate("systmess_error_rights_perform_this_action"));

        $data['iduser'] = $this->uri->segment(3);

        $this->load->model('User_model', 'users');

        $data['user'] = $this->users->getUser($data['iduser']);
        if (!count($data['user']))
            messageInModal(translate("systmess_error_user_does_not_exist"));

        $data['notices'] = $this->users->get_notice($data['iduser']);
        $this->view->display('admin/user/notices_view', $data);
    }

    function statistic_administration() {
        $this->load->model('User_Statistic_Model', 'user_statistic');
    }

    function statistic() {
        $this->load->model('User_Statistic_Model', 'user_statistic');
        $this->user_statistic->add_statistic_column('count_reviews', 'Count of the reviews');
        $this->user_statistic->delete_statistic_column('count_reviews');
        $this->user_statistic->set_user_statistic(1, array('count_reviews' => 1));
    }

    function ajax_admin_delete_user_photo() {
		if (!isAjaxRequest()) {
			headerRedirect();
		}
		if (!logged_in()) {
			jsonResponse(translate("systmess_error_should_be_logged"));
		}
		if (!have_right('manage_content')) {
			jsonResponse(translate("systmess_error_rights_perform_this_action"));
		}

		$id_user = intVal($_POST['user']);
		$id_photo = intVal($_POST['image']);
		$this->load->model('User_photo_Model', 'user_photo');
        $this->load->model('User_Model', 'user');

		$photo = $this->user_photo->get_photo(array('id_photo' => $id_photo, 'id_user' => $id_user));

		if(empty($photo))
			jsonResponse('Error: Photo not exist.');

		if($this->user_photo->delete_photo($photo['id_photo'])){
			@unlink($this->user_photo->path_to_logo_img. '/'. $id_user . '/' . $photo['name_photo']);
			$thumbs = unserialize($photo['thumb_photo']);
			remove_files($thumbs, $this->user_photo->path_to_logo_img. '/'. $id_user);

			$this->load->model('User_Statistic_Model', 'statistic');
			$this->statistic->set_users_statistic(array(
				$this->session->id => array(
					'user_photo' => -1
				)
			));

            $user_info = $this->user->getSimpleUser($id_user);
            if($user_info['user_photo'] == $photo['name_photo']){
                $this->user->updateUserMain($id_user, array('user_photo' => ''));
            }
			jsonResponse('User photo has been deleted.', 'success');
		}else
			jsonResponse('Error: User photo cannot be deleted.');
	}

	function view_email_template(){
		if (!logged_in()){
            // REFACTOR
			exit(translate("systmess_error_should_be_logged"));
		}

        if (!have_right('manage_content')) {
            exit(translate("systmess_error_rights_perform_this_action"));
        }

        $userType = ucfirst(cleanInput($_GET['user_type']));
        $templateMess = translate('email_template_user_type', ['{{userType}}' => $userType]);
        $email = cleanInput($_GET['email'], true);
        $templateCall = new GroupEmailTemplates();
        $templateData = $templateCall->getVerificationTemplate($email);

        if (in_array($userType, $templateData['restrict_gr_access'])) {
            $emailContent = model(Emails_Template_Model::class)->getEmailTemplateByAlias($email);

            if (!$emailContent) {
                jsonResponse(translate('email_template_does_not_exist'));
            }

            /** @var BodyRendererInterface $bodyRenderer */
            $bodyRenderer = $this->getContainer()->get(BodyRendererInterface::class);
            $templateCase = EmailTemplate::from($emailContent['alias_template']);
            $className = $templateCase->className();
            $email = new $className(...$templateCase->templateData());
            $email->templateReplacements([]);
            $bodyRenderer->render($email);
            $html = $email->getHtmlBody();
        } else {
            $templateMess = translate('email_template_does_not_exist');
            $html = '';
        }

        views()->display('admin/user/emails/multi_email_template_view', [
            'template'     => $html,
            'templateMess' => $templateMess,
        ]);

        return;
    }

    function block(){
        ini_set('max_execution_time', 0);
        ini_set('request_terminate_timeout', 0);
        $id_user = (int) $_GET['user'];
        $user = model('user')->getSimpleUser($id_user);

        if(empty($user)){
            return true;
        }

        $group_rights = model('blocking')->get_group_rights((int) $user['user_group']);
        $additional_rights = model('blocking')->get_aditional_rights(array('users_list' => $id_user));
        $block_rights = array_unique(
            array_merge(
                array_filter(explode(',', $group_rights['rights_list'])),
                array_filter(explode(',', $additional_rights['rights_list']))
            )
        );

        if(empty($block_rights)){
            return true;
        }

        $users_list_by_rights = array_fill_keys($block_rights, $id_user);
        // dd('Stopped before blocking content for this rights:', $users_list_by_rights);

        // $this->blocking->block_users_data_by_rights($users_list_by_rights);

        // $params = array(
        //     'users_list' => $id_user,
        //     'user_page_blocked' => 0
        // );
        // $this->blocking->change_blocked_users($params, array('user_page_blocked' => 2));

        $this->blocking->unblock_user_data_by_rights($id_user, (int) $user['user_group']);

        $params = array(
            'users_list' => $id_user,
            'user_page_blocked' => 2
        );
        $this->blocking->change_blocked_users($params, array('user_page_blocked' => 0));
    }

    public function download_activity_report()
    {
        $idUser = uri()->segment(3);
        if (empty($idUser)) {
            jsonResponse('ID user is required');
        }

        /** @var User_Model $userModel */
        $userModel = model(User_Model::class);

        $userDetails = $userModel->getUser($idUser);
        if(empty($userDetails)){
            jsonResponse('No such user!');
        }

        /** @var Monolog_Logs_Model $monologModel */
        $monologModel = model(Monolog_Logs_Model::class);

        $conditions = [
            'conditions' => [
                'user' => (int) $idUser
            ],
        ];

        $logs = $monologModel->findByUser($conditions)->toArray();
        $now = date('Y-m-d-H_i');
        $this->returnReportForUser($logs, $userDetails, "{$idUser}_all_activity_{$now}.xlsx");
    }

    /**
     * Get report
     *
     * @param array $userLog - log data
     * @param array $userDetails - user data
     * @param string $fileName - name of the file with extension
     *
     */
    private function returnReportForUser($userLog, $userDetails, $fileName = 'activity.xlsx')
    {
        $excel = new Spreadsheet();
        $excel->setActiveSheetIndex(0);
        $activeSheet = $excel->getActiveSheet();
        $activeSheet->setTitle('User Activity');

        $dateLog = 'A';
        $logType = 'B';
        $message = 'C';
        $links = array('D','E','F','G');
        $lastColumnLetter = 68; //D

		//region set the first row as the name of the user and link to user profile
		$rowIndex = 1;
		$activeSheet->getColumnDimension($dateLog)->setWidth(60);
		$activeSheet->getStyle($dateLog . $rowIndex)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$activeSheet->getStyle($dateLog . $rowIndex)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
		$activeSheet->setCellValue($dateLog . $rowIndex, "{$userDetails['fname']} {$userDetails['lname']}")
                        ->getStyle($dateLog . $rowIndex)
                            ->getFont()
                                ->setSize(16)
                                    ->setBold(true)
                                        ->getColor()
                                            ->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLUE); ;
		$activeSheet->getCell($dateLog . $rowIndex)->getHyperlink()->setUrl(getUserLink("{$userDetails['fname']} {$userDetails['lname']}", $userDetails['idu'], $userDetails['gr_type']));
		//endregion set the first row as the name of the user and link to user profile
		$rowIndex = 2;

		//region generate headings

        $activeSheet->getColumnDimension($dateLog)->setWidth(40);
        $activeSheet->getStyle($dateLog . $rowIndex)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $activeSheet->getStyle($dateLog . $rowIndex)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $activeSheet->setCellValue($dateLog . $rowIndex, 'Date')
                        ->getStyle($dateLog . $rowIndex)
                            ->getFont()
                                ->setSize(14)
                                    ->setBold(true);

        $activeSheet->getColumnDimension($logType)->setWidth(15);
        $activeSheet->getStyle($logType . $rowIndex)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $activeSheet->getStyle($logType . $rowIndex)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $activeSheet->setCellValue($logType . $rowIndex, 'Type')
                        ->getStyle($logType . $rowIndex)
                            ->getFont()
                                ->setSize(14)
                                    ->setBold(true);

        $activeSheet->getColumnDimension($message)->setWidth(130);
        $activeSheet->getStyle($message . $rowIndex)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $activeSheet->getStyle($message . $rowIndex)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $activeSheet->setCellValue($message . $rowIndex, 'Message')
                        ->getStyle($message . $rowIndex)
                            ->getFont()
                                ->setSize(14)
                                    ->setBold(true);

        foreach ($links as $linkHeading) {
            $activeSheet->getColumnDimension($linkHeading)->setWidth(50);
            $activeSheet->getStyle($links[0] . $rowIndex)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $activeSheet->mergeCells($links[0] . $rowIndex . ':' . end($links) . $rowIndex);
            $activeSheet->setCellValue($links[0] . $rowIndex, 'Links')
                            ->getStyle($links[0] . $rowIndex)
                                ->getFont()
                                    ->setSize(14)
                                        ->setBold(true);
        }
        //endregion generate headings

        //region introduce data
        $rowIndex = 3;
        $excel->getDefaultStyle()->getAlignment()->setWrapText(true);
        foreach($userLog as $log){
            $letterLastIndex = $lastColumnLetter;

            $activeSheet
            ->setCellValue("{$dateLog}$rowIndex", getDateFormat($log['date_log']))
            ->setCellValue("{$logType}$rowIndex", $log['type'])
            ->setCellValue("{$message}$rowIndex", strip_tags($log['message']));

            $messageLinks = getLinksFromText($log['message']);
            foreach($messageLinks as $link)
            {
                $letter = chr($letterLastIndex);
                $cellName = "{$letter}{$rowIndex}";
                $activeSheet->setCellValue($cellName, $link);
                $activeSheet->getCell($cellName)
                                ->getStyle($cellName)
                                    ->getFont()
                                        ->getColor()
                                            ->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLUE);

                $activeSheet->getCell($cellName)->getHyperlink()->setUrl($link);
                $letterLastIndex++;
            }

            $rowIndex++;
        }
        //endregion introduce data

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

		$objWriter = IOFactory::createWriter($excel, 'Xlsx');
        $objWriter->save('php://output');
    }

    private function restoreUserData()
    {
        #region validation
        $adapter = new ValidatorAdapter(library(TinyMVC_Library_validator::class));
        $validators[] = new UserRestoreDataValidator($adapter);

        if(null !== request()->request->get('company_name')){
            $validators[] = new RestoreCompanyNameValidator($adapter);
        }

        $validator = new AggregateValidator($validators);
        if (!$validator->validate(request()->request->all())) {
            \jsonResponse(
                \array_map(
                    function (ConstraintViolation $violation) { return $violation->getMessage(); },
                    \iterator_to_array($validator->getViolations()->getIterator())
                )
            );
        }

        $idUser = request()->request->getInt('user');

        /** @var Users_Model $usersModel */
        $usersModel = model(Users_Model::class);

        if (empty($idUser) || (empty($user = $usersModel->findOne($idUser)))) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }

        if($user['status'] != UserStatus::DELETED){
            jsonResponse('User status is not deleted!');
        }

        #endregion validation

        #region auth update
        $userAccounts = $usersModel->findAllBy([
            'conditions'    => [
                'principal' => $user['id_principal'],
                'notStatus' => UserStatus::DELETED()
            ],
            'columns'       => [
                'idu'
            ],
        ]);

        if (empty($userAccounts))
        {
            /** @var Auth_Model $authModel */
            $authModel = model(Auth_Model::class);

            $encryptedEmail = getEncryptedEmail(request()->request->get('email'));

            if($authModel->exists_hash($encryptedEmail)){
                jsonResponse(translate('register_error_email_already_registered'));
            }

            if (!$authModel->change_hash($user['id_principal'], [
                'token_email' => $encryptedEmail
            ])){
                jsonResponse('Failed to change user credentials.');
            }

        }
        #endregion auth update

        $userUpdates = [
            'email'               => request()->request->get('email'),
            'status'              => UserStatus::from(request()->request->get('status')),
            'fname'               => request()->request->get('fname'),
            'lname'               => request()->request->get('lname'),
            'notify_email'        => 1,
            'subscription_email'  => 1,
            'activation_code'     => get_sha1_token(request()->request->get('email')),
        ];
        $usersModel->updateOne($idUser, $userUpdates);

        #region unblock user content
        /** @var Blocking_Model $blockingModel */
        $blockingModel = model(Blocking_Model::class);

        $blockingModel->unblock_user_content($idUser);
        #endregion unblock user content

        switch ($user['user_group']) {
            case 1: //buyer
                /** @var Buyer_Companies_Model $buyerCompaniesModel */
                $buyerCompaniesModel = model(Buyer_Companies_Model::class);

                $buyerCompany = $buyerCompaniesModel->findOneBy([
                    'conditions'    => [
                        'userId'    => $idUser,
                    ],
                ]);

                if(!empty($buyerCompany)){
                    $buyerCompaniesModel->updateOne($buyerCompany['id'], [
                        'company_name'       => request()->request->get('company_name'),
                        'company_legal_name' => request()->request->get('company_legal_name')
                    ]);
                }

                break;
            case 31: //shipper
                /** @var Shipper_Companies_Model $shipperCompaniesModel */
                $shipperCompaniesModel = model(Shipper_Companies_Model::class);

                $shipperCompany = $shipperCompaniesModel->findOneBy([
                    'conditions'    => [
                        'userId'    => $idUser,
                    ],
                ]);

                $shipperCompaniesModel->updateOne($shipperCompany['id'], [
                    'co_name'       => request()->request->get('company_name'),
                    'legal_co_name' => request()->request->get('company_legal_name')
                ]);

                break;
            default: //seller
                /** @var Seller_Companies_Model $sellerCompaniesModel */
                $sellerCompaniesModel = model(Seller_Companies_Model::class);

                $sellerCompany = $sellerCompaniesModel->findOneBy([
                    'conditions'    => [
                        'userId'    => $idUser,
                    ],
                ]);

                $sellerCompaniesModel->updateOne($sellerCompany['id_company'], [
                    'name_company'       => request()->request->get('company_name'),
                    'legal_name_company' => request()->request->get('company_legal_name')
                ]);

                break;
        }

        #region email reset pass
        try {

            $fullName = request()->request->get('fname') . ' ' . request()->request->get('lname');

            /** @var MailerInterface $mailer */
            $mailer = $this->getContainer()->get(MailerInterface::class);
            $mailer->send(
                    (new RestoreAccountEmail("{$fullName}", getUrlForGroup("login")))
                    ->to(new RefAddress((string) $user['idu'], new Address(request()->request->get('email'))))
            );
        } catch (\Throwable $th) {
            jsonResponse(translate('email_has_not_been_sent'));
        }

        #region ep reviews

        /** @var Ep_Reviews_Model $epReviewsModel */
        $epReviewsModel = model(Ep_Reviews_Model::class);

        $epReviewsModel->updateMany(
            [
                'is_published' => 1
            ],
            [
                'conditions' => [
                    'userId'    => (int) $user['idu'],
                ],
            ]
        );
        #endregion ep reviews

        /** @var Users_Model $userModel */
        $userModel = model(Users_Model::class);

        $userModel->setNotice($user['idu'], [
            'add_date' => date('Y/m/d H:i:s'),
            'add_by'   => 'System',
            'notice'   => "The user has been restored by admin.",
        ]);

        // Restore user matrix account
        $this->getContainer()->get(MessengerInterface::class)->bus('event.bus')->dispatch(new UserWasRestoredEvent((int) $user['idu']));

        jsonResponse('The user has been restored succesfully', 'success');
    }

    public function fake_users()
    {
        checkPermisionAjax('users_administration');

        /** @var Users_Model $usersModel */
        $usersModel = model(Users_Model::class);

        if (empty(array_filter($usersIds = (array) request()->request->get('checkedUsers')))) {
           jsonResponse(translate('systmess_error_user_id_not_selected'));
        }

        $limitCount = (int) config('mark_demo_users_limit');

        if (count($usersIds) > $limitCount) {
            jsonResponse(translate('systmess_error_many_demo_user_selected', ['{{NUMBER}}' => $limitCount]));
        }

        $users = $usersModel->findAllBy([
            'limit'  => $limitCount,
            'scopes' => [
                'ids'        => $usersIds,
                'isFake'     => false,
                'groupTypes' => [
                    GroupType::BUYER(),
                    GroupType::SELLER(),
                    GroupType::SHIPPER(),
                ],
            ],
            'joins'  => [
                'userGroups'
            ]
        ]);

        if (count($users) !== count($usersIds)) {
            jsonResponse(translate('systmess_error_demo_user_selected'));
        }

        foreach ($users as $user) {
            $this->markUserAsFake($user,$usersModel);
        }

        jsonResponse(translate('systmess_success_demo_users_were_marked'), 'success');
    }

    private function markUserAsFake(array $user, Users_Model $usersModel)
    {
        $usersModel->updateOne(
            $user['idu'],
            [
                'zoho_id_record' => null,
                'fake_user'      => true,
            ]
        );

        /** @var Blocking_Model $blockingModel */
        $blockingModel = model(Blocking_Model::class);
        $blockingModel->block_user_content($user['idu'], ['id_generated']);

        if ('prod' === config('env.APP_ENV')) {
            if (isset($user['zoho_id_record'])) {
                /** @var TinyMVC_Library_Zoho_crm $crmLibrary */
                $crmLibrary = library(TinyMVC_Library_Zoho_crm::class);
                $crmLibrary->remove_contact((int) $user['zoho_id_record']);
            }

            /** @var Crm_Model $crmQueueModel */
            $crmQueueModel = model(Crm_Model::class);
            $crmQueueModel->delete_records_by_users_ids([$user['idu']]);
        }

        /** @var Elasticsearch_Users_Model $elasticsearchUsersModel */
        $elasticsearchUsersModel = model(Elasticsearch_Users_Model::class);
        $elasticsearchUsersModel->deleteUser((int) $user['idu']);

        $this->get(MessengerInterface::class)->bus('event.bus')->dispatch(new UserWasMarkedFakeEvent((int) $user['idu']));

        destroyUserSession((int) $user['idu']);
    }

    private function unmarkUserAsFake(array $user, Users_Model $usersModel)
    {
        $usersModel->updateOne($user['idu'], ['fake_user' => false]);

        /** @var Blocking_Model $blockingModel */
        $blockingModel = model(Blocking_Model::class);
        $blockingModel->unblock_user_content($user['idu']);

        /** @var Elasticsearch_Users_Model $elasticsearchUsersModel */
        $elasticsearchUsersModel = model(Elasticsearch_Users_Model::class);
        $elasticsearchUsersModel->index((int) $user['idu']);

        $this->get(MessengerInterface::class)->bus('event.bus')->dispatch(new UserWasMarkedRealEvent((int) $user['idu']));
    }
}
