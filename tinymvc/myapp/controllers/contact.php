<?php

use App\Common\Traits\DocumentsApiAwareTrait;
use App\Common\Validation\ConstraintViolation;
use App\Common\Validation\FlatValidationData;
use App\Common\Validation\Legacy\ValidatorAdapter as LegacyValidatorAdapter;
use App\Email\ContactAdmin;
use App\Email\EmailUser;
use App\Services\PhoneCodesService;
use ExportPortal\Bridge\Mailer\Mime\RefAddress;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;
use App\Common\Buttons\ChatButton;
use App\Validators\AdministrationMessageValidator;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

/**
 * contact.php.
 *
 * contact application controller
 *
 * @author
 *
 * @deprecated
 */
class Contact_Controller extends TinyMVC_Controller
{
    use DocumentsApiAwareTrait;

    private $breadcrumbs = array();
    private $messages_per_page = 10;

    public function index()
    {
        $data = session()->getMessages();
        $data['page'] = 1;
        $data['per_p'] = 2;
        $data['ep_address'] = config('ep_address');
        $data['ep_phone_number'] = config('ep_phone_number');
        $data['email_contact_us'] = config('email_contact_us');
        $data['ep_phone_whatsapp'] = config('ep_phone_whatsapp');
        $data['ep_phone_number_free'] = config('ep_phone_number_free');
        $data['quest_cats'] = model('questions')->getCategories(array('visible' => 1));
        $data['countries'] = model('country')->fetch_port_country();
        // $data['offices'] = $this->offices->get_office_location(array('order_by' => 'RAND()', 'limit' => 2));
        $data['questions'] = model('questions')->getQuestions(array(
            'page'  => $data['page'],
            'per_p' => $data['per_p'],
        ));

        foreach ($data['questions'] as $key => $question) {
            $data['questions'][$key]['answers'] = model('questions')->getAnswers(array('id_question' => $question['id_question']));
            if (!empty($data['questions'][$key]['answers'])) {
                $answers_keys = implode(',', array_keys(arrayByKey($data['questions'][$key]['answers'], 'id_answer')));
                if (session()->loggedIn) {
                    $data['questions'][$key]['helpful_answers'] = model('questions')->get_helpful_by_answer($answers_keys, session()->id);
                }
            }
        }

        $data['phone_codes'] = (new PhoneCodesService(model('country')))->getCountryCodes();

        $this->breadcrumbs[] = array(
            'link'  => __SITE_URL . 'help',
            'title' => translate('breadcrumb_help'),
        );

        $this->breadcrumbs[] = array(
            'link'  => '',
            'title' => translate('help_contact_us'),
        );
        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['nav_page'] = 'contact us';

        $data['current_page'] = 'contact';
        $data['header_out_content'] = 'new/contact/header_view';
        $data['sidebar_right_content'] = 'new/contact/sidebar_view';
        $data['main_content'] = 'new/contact/index_view';
        $data['footer_out_content'] = 'new/contact/bottom_view';
        $data['googleAnalyticsEvents'] = true;
        $this->view->assign($data);
        $this->view->display('new/index_template_view');
    }

    public function administration()
    {
        checkAdmin('manage_content');

        /**
         * @var Ep_Modules_Model $epModulesModel
         */
        $epModulesModel = model(Ep_Modules_Model::class);

        $activeFilters = [];
        if (isset($_GET['module']) && is_numeric($_GET['module'])) {
            $activeFilters['module'] = (int) $_GET['module'];
        }

        if (isset($_GET['user']) && is_numeric($_GET['user'])) {
            $activeFilters['user'] = (int) $_GET['user'];
        }

        $data = [
            'title'         => 'Contact',
            'modules'       => $epModulesModel->get_all_modules(),
            'activeFilters' => $activeFilters,
        ];

        views(['admin/header_view', 'admin/contact/index_view', 'admin/footer_view'], $data);
    }

    public function ajax_contact_admin_dt()
    {
        checkIsAjax();
        checkIsLoggedAjaxDT();
        checkPermisionAjaxDT('manage_content');

        /**
         * @var Admin_Contact_Model $adminContactModel
         */
        $adminContactModel = model(Admin_Contact_Model::class);

        $conditions = array_merge(
            [
                'per_p'        => (int) arrayGet($_POST, 'iDisplayLength', 10),
                'start'        => (int) arrayGet($_POST, 'iDisplayStart', 0),
                'base_columns' => implode(', ', [
                    'ac.`id`',
                    'ac.`id_sender`',
                    'ac.`id_staff`',
                    'ac.`subject`',
                    'ac.`content`',
                    'ac.`date_time`',
                    '0 AS `isModerated`',
                    '0 AS `isDeleted`',
                    '0 AS `isRead`',
                    '1 AS `isNew`',
                ]),
                'sort_by'      => flat_dt_ordering($_POST, [
                    'dt_id'        => 'id',
                    'dt_user'      => 'user_name',
                    'dt_subject'   => 'subject',
                    'dt_date_time' => 'date_time',
                ]),
            ],
            dtConditions($_POST, [
                ['as' => 'user',      'key' => 'user',      'type' => 'int'],
                ['as' => 'online',    'key' => 'online',    'type' => 'int'],
                ['as' => 'date_from', 'key' => 'date_from', 'type' => 'formatDate:Y-m-d'],
                ['as' => 'date_to',   'key' => 'date_to',   'type' => 'formatDate:Y-m-d'],
            ])
        );

        $messages = $adminContactModel->get_contact_admin_messages($conditions);
        $records_total = $adminContactModel->get_contact_admin_messages_count($conditions);
        $output = [
            'sEcho'                => (int) arrayGet($_POST, 'sEcho', 0),
            'iTotalRecords'        => $records_total,
            'iTotalDisplayRecords' => $records_total,
            'aaData'               => $this->get_contact_messages_list_payload((int) privileged_user_id(), $messages),
        ];

        jsonResponse(null, 'success', $output);
    }

    public function ajax_contact_operations()
    {
        checkIsAjax();

        switch (uri()->segment(3)) {
            case 'add':
                checkIsLoggedAjax();
                is_allowed('freq_allowed_saved');

                $this->load->model('Contact_User_Model', 'contact');
                $my_id = $this->session->id;
                $id = $this->uri->segment(4);

                if (!$id) {
                    jsonResponse(translate('systmess_error_select_user'));
                }

                if ($my_id == $id) {
                    jsonResponse(translate('systmess_error_cannot_contact_yourself'));
                }

                if ($this->contact->is_in_contact($my_id, $id)) {
                    jsonResponse(translate('systmess_error_user_is_already_in_your_contacts'));
                }

                if (!$this->contact->can_add_user($id)) {
                    jsonResponse(translate('systmess_error_cannot_add_user_to_contact'));
                }

                $insert = array(
                    'id_user'         => $my_id,
                    'id_contact_user' => $id,
                );

                if ($this->contact->set_contact($insert)) {
                    jsonResponse(translate('systmess_success_user_saved_in_the_favorites'), 'success');
                }

                jsonResponse(translate('systmess_error_cannot_add_user_to_contact'));

                break;
            case 'remove':
                checkIsLoggedAjax();
                is_allowed('freq_allowed_saved');

                $this->load->model('Contact_User_Model', 'contact');
                $my_id = $this->session->id;
                $id = (int) $this->uri->segment(4);

                if (!(bool) $id) {
                    jsonResponse(translate('systmess_error_select_user'));
                }

                if ($this->contact->delete_contact($my_id, $id)) {
                    jsonResponse(translate('systmess_success_user_removed_from_the_favorites'), 'success');
                }

                jsonResponse(translate('systmess_error_cannot_remove_user_from_contact'));

                break;
            case 'email_contact_admin':

                if(!ajax_validate_google_recaptcha()) {
                    jsonResponse(translate('systmess_error_you_didnt_pass_bot_check'));
                }

                $request = request();
                $parameters = $request->request;

                $validator_rules = array(
                    array(
                        'field' => 'fname',
                        'label' => translate('contact_page_first_name'),
                        'rules' => array('required' => '', 'max_len[50]' => '', 'min_len[2]' => '', 'valid_user_name' => ''),
                    ),
                    array(
                        'field' => 'lname',
                        'label' => translate('contact_page_last_name'),
                        'rules' => array('required' => '', 'max_len[50]' => '', 'min_len[2]' => '', 'valid_user_name' => ''),
                    ),
                    array(
                        'field' => 'subject',
                        'label' => translate('contact_page_subject'),
                        'rules' => array('required' => '', 'max_len[100]' => ''),
                    ),
                    array(
                        'field' => 'from',
                        'label' => translate('contact_page_email'),
                        'rules' => array('required' => '', 'no_whitespaces' => '', 'valid_email' => ''),
                    ),
                    array(
                        'field' => 'content',
                        'label' => translate('contact_page_message'),
                        'rules' => array('required' => '', 'max_len[500]' => ''),
                    ),
                    array(
                        'field' => 'country_code',
                        'label' => translate('country_code_field_label'),
                        'rules' => array(
                            'required' => '',
                            function ($attr, $phone_code_id, $fail){
                                if (empty($phone_code_id) || !model('country')->has_country_code($phone_code_id)) {
                                    $fail(translate('systmess_error_field_contains_unknown_value', array('[COLUMN_NAME]' => translate('country_code_field_label'))));
                                }
                            }
                        )
                    ),
                    array(
                        'field' => 'phone',
                        'label' => translate('contact_page_phone'),
                        'rules' => array(
                            'required' => '',
                            function ($attr, $phone, $fail) use ($parameters) {
                                $phone_util = PhoneNumberUtil::getInstance();
                                $phone_code_id = $parameters->getInt('country_code');
                                $phone_code = model('country')->get_country_code($phone_code_id)['ccode'] ?? null;
                                $raw_number = trim("{$phone_code} {$phone}");

                                try {
                                    if(!$phone_util->isViablePhoneNumber($raw_number)){
                                        $fail(translate('systmess_error_invalid_phone_number', array('[COLUMN_NAME]' => translate('register_label_phone'))));
                                    }

                                    $phone_number = $phone_util->parse($raw_number);
                                    if(!$phone_util->isValidNumber($phone_number)){
                                        $fail(translate('systmess_error_wrong_phone_number', array('[COLUMN_NAME]' => translate('register_label_phone'))));
                                    }
                                } catch (NumberParseException $exception) {
                                    $fail(translate('systmess_error_invalid_phone_number', array('[COLUMN_NAME]' => translate('register_label_phone'))));
                                }
                            }
                        )
                    ),
                );

                $this->validator->set_rules($validator_rules);

                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $email = cleanInput($_POST['from']);

                //region Phone code
				$phone_code = (new PhoneCodesService(model('country')))->findAllMatchingCountryCodes($parameters->getInt('country_code'))->first();
                $phone_number = ($phone_code ? $phone_code->getName() : null) . ' ' . cleanInput($parameters->get('phone'));
				//endregion Phone code

                try {
                    /** @var MailerInterface $mailer */
                    $mailer = $this->getContainer()->get(MailerInterface::class);
                    $mailer->send(
                        (new ContactAdmin(cleanInput(request()->request->get('fname')) . ' ' . cleanInput(request()->request->get('lname')), $email, $phone_number, cleanInput(request()->request->get('content'))))
                            ->bcc(config('contact_us_bcc_emails'))
                            ->to(new Address(config('email_contact_us')))
                            ->subject(cleanInput(request()->request->get('subject')))

                    );
                } catch (\Throwable $th) {
                    jsonResponse(translate('email_has_not_been_sent'));
                }

                jsonResponse(translate('contact_page_email_sent'), 'success');

                break;
            case 'send_admin_message':
                is_allowed('freq_allowed_send_message');
                $id_user = id_session();
                $staff_id = !user_type('Shipper' == user_group_type() ? 'shipper_staff' : 'users_staff') ? 0 : $id_user;
                $this->contact_administation(model(Users_Model::class), model(Admin_Contact_Model::class), (int) privileged_user_id(), (int) $staff_id, $_POST);

                break;
            case 'email_user':
                checkIsLoggedAjax();
                checkPermisionAjax('manage_content');

                $validator_rules = array(
                    array(
                        'field' => 'id_user',
                        'label' => 'User info',
                        'rules' => array('required' => '', 'integer'=>''),
                    ),
                    array(
                        'field' => 'subject',
                        'label' => 'Subject',
                        'rules' => array('required' => ''),
                    ),
                    array(
                        'field' => 'content',
                        'label' => 'Content',
                        'rules' => array('required' => ''),
                    ),
                );

                $this->validator->set_rules($validator_rules);

                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $idUser = intval($_POST['id_user']);
                $this->load->model('User_Model', 'user');

                $userInfo = $this->user->getSimpleUser($idUser);

                if (empty($userInfo)) {
                    jsonResponse(translate("systmess_error_user_does_not_exist"));
                }

                try {
                    /** @var MailerInterface $mailer */
                    $mailer = $this->getContainer()->get(MailerInterface::class);

                    if ('CR Affiliate' === $userInfo['gr_type']) {
                        $mailer->send(
                            (new EmailUser("{$userInfo['fname']} {$userInfo['lname']}", cleanInput(request()->request->get('content'))))
                                ->subject(cleanInput(request()->request->get('subject')))
                                ->from(config('epcountryambassador_email'))
                                ->to(new RefAddress((string) $idUser, new Address($userInfo['email'])))
                        );
                    } else {
                        $mailer->send(
                            (new EmailUser("{$userInfo['fname']} {$userInfo['lname']}", cleanInput(request()->request->get('content'))))
                                ->subject(cleanInput(request()->request->get('subject')))
                                ->to(new RefAddress((string) $idUser, new Address($userInfo['email'])))
                        );
                    }
                } catch (\Throwable $th) {
                    jsonResponse(translate('email_has_not_been_sent'));
                }

                jsonResponse(translate('systmess_success_email_has_been_sent'), 'success');

                break;
            default:
                json(null, 404);

                break;
        }
    }

    /**
     * @author Alexandr Usinevici
     * @todo Remove [05.01.2022]
     *
     * Reason: this code is duplicate of ecb2b/api_contact_admin
     */
    // public function api_contact_admin()
    // {
    //     $token = $_POST['token'];
    //     $url = 'https://www.recaptcha.net/recaptcha/api/siteverify';
    //     $data = array(
    //         'secret'   => '6LcD84kUAAAAAFPLTnAfRD5SSu-hUZ4Qnv14KSrF',
    //         'response' => $token,
    //     );
    //     $options = array(
    //         'http' => array(
    //             'method'  => 'POST',
    //             'content' => http_build_query($data),
    //         ),
    //     );
    //     $context = stream_context_create($options);
    //     $verify = file_get_contents($url, false, $context);
    //     $captcha_success = json_decode($verify);

    //     if (false == $captcha_success->success) {
    //         jsonResponse('Error: Cannot send email now. Please try agan later.');
    //     }

    //     $validator_rules = array(
    //         array(
    //             'field' => 'subject',
    //             'label' => 'Subject',
    //             'rules' => array('required' => '', 'max_len[100]' => ''),
    //         ),
    //         array(
    //             'field' => 'from',
    //             'label' => 'From',
    //             'rules' => array('required' => '', 'no_whitespaces' => '', 'valid_email' => ''),
    //         ),
    //         array(
    //             'field' => 'content',
    //             'label' => 'Content',
    //             'rules' => array('required' => '', 'max_len[500]' => ''),
    //         ),
    //     );

    //     $this->validator->set_rules($validator_rules);

    //     if (!$this->validator->validate()) {
    //         jsonResponse($this->validator->get_array_errors());
    //     }

    //     try {
    //         /** @var MailerInterface $mailer */
    //         $mailer = $this->getContainer()->get(MailerInterface::class);
    //         $mailer->send(
    //             (new ContactAdmin(cleanInput(request()->request->get('fname')) . ' ' . cleanInput(request()->request->get('lname')), request()->request->get('from'), cleanInput(request()->request->get('phone')), cleanInput(request()->request->get('content'))))
    //                 ->bcc(config('contact_us_bcc_emails'))
    //                 ->to(new Address(config('email_contact_us')))
    //                 ->subject(cleanInput(request()->request->get('subject')))

    //         );
    //     } catch (\Throwable $th) {
    //         jsonResponse(translate('email_has_not_been_sent'));
    //     }

    //     jsonResponse('E-mail was sent successfully.', 'success');
    // }

    public function ajax_get_saved()
    {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        if (!logged_in()) {
            jsonResponse(translate("systmess_error_should_be_logged"));
        }

        $this->load->model('Contact_User_Model', 'contact');

        $data = array(
            'curr_page' => abs(intval($_POST['page'])),
            'per_page'  => 8,
        );
        $params = array(
            'per_p'    => &$data['per_page'],
            'from'     => ($data['curr_page'] - 1) * $data['per_page'],
            'id_user'  => $this->session->id,
            'order_by' => 'u.fname',
        );
        $data['counter'] = $this->contact->get_count_contacts($params);
        $contacts = $this->contact->get_contacts($params);

        $data['contacts'] = [];
		if (!empty($contacts)) {
            $data['contacts'] = array_map(
                function ($contactsItem) {
                    $chatBtn = new ChatButton(['recipient' => $contactsItem['idu'], 'recipientStatus' => $contactsItem['status']]);
                    $contactsItem['btnChat'] = $chatBtn->button();
                    return $contactsItem;
                },
                $contacts
            );
        }

        $content = $this->view->fetch('new/nav_header/saved/contact_header_list_view', $data);

        jsonResponse($content, 'success', array('counter' => $data['counter']));
    }

    public function popup_forms()
    {
        checkIsAjax();

        switch (uri()->segment(3)) {
            case 'email_user':
                checkIsLoggedAjaxModal();
                checkPermisionAjaxModal('manage_content');

                $this->show_email_user_popup(
                    (int) privileged_user_id(),
                    with(uri()->segment(4), function ($user_id): ?int { return $user_id ? (int) $user_id : null; }),
                );

                break;

            case 'contact_us':
                $webpackData = "webpack" === request()->headers->get("X-Script-Mode", "legacy");
                $this->show_contact_us_popup($webpackData);

                break;
            default:
                messageInModal(translate('systmess_error_route_not_found'));

                break;
        }
    }

    /**
     * Shows the contact us popup.
     */
    protected function show_contact_us_popup($webpackData): void
    {
        $data = [];
        if($webpackData){
            $data['webpackData'] = true;
        }

        if (__CURRENT_SUB_DOMAIN === getSubDomains()['shippers']) {
            $data['phoneCodes'] = (new PhoneCodesService(model(Country_Model::class)))->getCountryCodes();
            views()->display('new/epl/popups/contact_us_popup_view', $data);
        } else {
            $data['phone_codes'] = (new PhoneCodesService(model(Country_Model::class)))->getCountryCodes();
            views()->display('new/contact/contact_us_view', $data);
        }
    }

    /**
     * Shows the form that allows to email user.
     *
     * @param int      $user_id
     * @param null|int $recipient_id
     */
    protected function show_email_user_popup(int $user_id, ?int $recipient_id = null): void
    {
        //region Check recipient
        if (
            empty($recipient_id)
            || empty($recipient = model('user')->get_user_by_condition(array('id_user' => $recipient_id)))
        ) {
            messageInModal(translate('systmess_error_invalid_data'));
        }

        if ($user_id === $recipient_id) {
            messageInModal(translate('systmess_error_cannot_contact_yourself'));
        }

        //region Recipient photo
        $recipient['photo'] = getDisplayImageLink(
            array('{ID}' => $recipient['idu'] ?? null, '{FILE_NAME}' => $recipient['user_photo'] ?? null),
            'users.main',
            array('thumb_size' => 0, 'no_image_group' => $recipient['user_group'] ?? null)
        );
        //endregion Recipient photo
        //endregion Check recipient

        //region Assign vars
        views()->assign(array('user_info' => $recipient));
        //endregion Assign vars

        $this->view->display('admin/user/email_form_view');
    }

    /**
     * Returns the user's companies.
     *
     * @param int         $user_id
     * @param null|string $type
     *
     * @return null|array
     */
    private function get_user_companies(int $user_id, ?string $type): ?array
    {
        $factories = array(
            'buyer'   => function (int $user_id): array {
                return array(
                    $user_id => array(
                        'name_company' => model('company_buyer')->get_company_by_user($user_id)['company_name'] ?? null,
                    ),
                );
            },
            'seller'  => function (int $user_id): array {
                return arrayByKey(
                    model('company')->get_sellers_base_company($user_id, 'id_company, name_company, index_name, id_user, type_company'),
                    'id_user'
                );
            },
            'shipper' => function (int $user_id): array {
                $company_shipper = model('shippers')->get_shipper_by_user($user_id);

                if (empty($company_shipper)) {
                    return array();
                }

                return array(
                    $user_id => array_merge(
                        $company_shipper,
                        array('name_company' => $company_shipper['co_name'])
                    )
                );
            },
        );

        /** @var null|Closure $factory */
        $factory = $factories[\mb_strtolower(\trim($type))] ?? null;
        if (null === $factory) {
            return null;
        }

        return $factory($user_id);
    }

    /**
     * Returns the DT payload for the message list.
     *
     * @param int        $user_id
     * @param null|array $messages
     *
     * @return array
     */
    private function get_contact_messages_list_payload(int $user_id, ?array $messages): array
    {
        if (empty($messages)) {
            return [];
        }

        $output = [];
        foreach ($messages as $record_details) {
            $is_logged = filter_var($record_details['logged'], FILTER_VALIDATE_BOOLEAN);

            //region User label
            //region Online
            $online_label_class = $is_logged ? 'txt-green' : 'txt-red';
            $online_label_value = (int) $is_logged;
            $online_label = "
                <a class=\"ep-icon ep-icon_onoff {$online_label_class} dt_filter\" data-name=\"online\" data-value=\"{$online_label_value}\"></a>
            ";
            //endregion Online

            $user_name = cleanOutput($record_details['user_name']);
            $user_group = $record_details['gr_type'] ?? 'buyer';
            $user_profile_url = getUrlForGroup('usr/' . strForURL($record_details['user_name']) . '-' . $record_details['id_sender'], $user_group);
            $user_label = "
                <div class=\"pull-left\">
                    <a class=\"ep-icon ep-icon_filter txt-green dt_filter\"
                        data-title=\"User\"
                        title=\"Filter by {$user_name}\"
                        data-value-text=\"{$user_name}\"
                        data-value=\"{$record_details['id_sender']}\"
                        data-name=\"user\">
                    </a>
                    {$online_label}
                    <a href=\"{$user_profile_url}\"
                        class=\"ep-icon ep-icon_user\"
                        title=\"View personal page of {$user_name}\"
                        target=\"_blank\">
                    </a>
                </div>
                <div class=\"clearfix\"></div>
                <div class=\"pull-left\">{$user_name}</div>
            ";
            //endregion User label

            $output[] = [
                'dt_id'        => $record_details['id'],
                'dt_user'      => $user_label,
                'dt_subject'     => cleanOutput($record_details['subject'] ?? '—'),
                'dt_content'   => $record_details['content'] ?? '—',
                'dt_date_time' => getDateFormatIfNotEmpty($record_details['date_time'] ?? null),
                'dt_actions'   => '', //$actions_label,
            ];
        }

        return $output;
    }

    /**
     * Contacts the administration.
     *
     * @param int        $idUser
     * @param null|int   $idStaff
     * @param null|array $postdata
     */
    private function contact_administation(
        Users_Model $usersModel,
        Admin_Contact_Model $adminContactModel,
        int $idUser,
        ?int $idStaff,
        ?array $postdata
    ): void {
        //region Validation
        /** @var Validator $legacyValidator */
        $legacyValidator = library(Validator::class);
        $validator = new AdministrationMessageValidator(new LegacyValidatorAdapter($legacyValidator), true);
        if (!$validator->validate(new FlatValidationData($postdata ?? array()))) {
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

        //region User name
        $userName = user_name_session();
        if (
            user_type('users_staff')
            && !empty($user = $usersModel->findOne($idUser))
        ) {
            $userName = trim(implode(' ', array($user['fname'] ?? null, $user['lname'] ?? null)));
        }
        //endregion User name

        //region Save message
        if (
            !$adminContactModel->send_admin_message(array(
                'subject'      => $subject = arrayGet($postdata, 'subject', ''),
                'id_staff'     => $idStaff,
                'id_sender'    => $idUser,
                'content'      => arrayGet($postdata, 'content'),
                'search_info'  => implode(' ', tokenizeSearchText(trim("{$subject} {$userName}"))),
            ))
        ) {
            jsonResponse(translate('contact_page_cannot_send_message'));
        }
        //endregion Save message

        jsonResponse(translate('contact_page_message_sent_successfully'), 'success');
    }
}
