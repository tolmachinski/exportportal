<?php

declare(strict_types=1);

use App\Email\DemoWebinarEpNext;
use App\Email\DemoWebinarEpRegistered;
use App\Email\DemoWebinarEpRequesting;
use App\Email\DemoWebinarEpUnRegistered;
use App\Validators\EmailValidator;
use App\Validators\PhoneValidator;
use App\Services\PhoneCodesService;
use App\Validators\WebinarRequestValidator;
use App\Common\Validation\ConstraintViolation;
use App\Common\Validation\Legacy\ValidatorAdapter;
use App\Common\Validation\Standalone\AggregateValidator;
use App\Common\Contracts\Entities\CountryCodeInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

/**
 * Controller Webinar_requests
 */
class Webinar_requests_Controller extends TinyMVC_Controller
{
    public function index()
    {
        show_404();
    }
    /**
     * Index page
     */
    public function administration(): void
    {
        checkIsLogged();
        checkPermision('webinars_administration');

        views(
            [
            'admin/header_view',
            'admin/webinar_requests/index_view',
            'admin/footer_view'],
            [
            'title' => 'Webinar Requests'
        ]
        );
    }

    public function ajaxDtAdministration()
    {
        checkIsAjax();
        checkIsLoggedAjax();
        checkPermisionAjaxDT('webinars_administration');

        $request = request()->request;

        $dtFilters =  dtConditions($request->all(), [
            ['as' => 'requested_from',   'key' => 'requested_from',   'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'requested_to',     'key' => 'requested_to',     'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'country',          'key' => 'country',          'type' => 'int'],
            ['as' => 'status',           'key' => 'status',           'type' => 'string|trim'],
            ['as' => 'registered',       'key' => 'registered',       'type' => 'int'],
            ['as' => 'webinar',          'key' => 'webinar',          'type' => 'int'],
            ['as' => 'email',            'key' => 'email',            'type' => 'cut_str:200|trim']
        ]);

        $perPage = $request->getInt('iDisplayLength', 10);
        $page = $request->getInt('iDisplayStart', 0) / $perPage + 1;

        try {

            /** @var Webinar_Requests_Model $requestsModel*/
            $requestsModel = model(Webinar_Requests_Model::class);

            $paginator = $requestsModel->paginate(
                [
                    'columns' => [
                        '*',
                        'CONCAT_WS(" ", `fname`, `lname`) as user_name'
                    ],
                    'conditions' => array_merge($dtFilters),
                    'with' => ['user', 'country', 'webinar'],
                    'order'      => \array_column(
                        \dtOrdering(
                            request()->request->all(),
                            [
                                'dt_id'        => "`{$requestsModel->getTable()}`.`{$requestsModel->getPrimaryKey()}`",
                                'dt_user_type' => "`{$requestsModel->getTable()}`.`USER_TYPE`",
                                'dt_status'    => "`{$requestsModel->getTable()}`.`status`",
                                'dt_requested' => "`{$requestsModel->getTable()}`.`requested_date`",
                            ]
                        ),
                        'direction',
                        'column'
                    ),
                ],
                $perPage,
                $page
            );

            foreach ($paginator['data'] as $row) {

                #region user
                $userPersonalPageUrl = getUserLink($row['user_name'], $row['id_user'], 'buyer');
                if (!empty($row['id_user'])) {
                    $user =
                    <<<USER_INFO_COLUMN
                        <div class="tal">
                            <a class="ep-icon ep-icon_user" href="{$userPersonalPageUrl}" title="View personal page of {$row['user_name']}" target="_blank"></a>
                        </div>
                        <div>{$row['user_name']}</div>
                    USER_INFO_COLUMN;
                } else {
                    $user =
                    <<<USER_INFO_COLUMN
                        <div>{$row['user_name']}</div>
                    USER_INFO_COLUMN;
                }
                #endregion user

                #region contacts
                $contacts =
                    <<<USER_CONTACTS
                        <strong>Email:</strong> {$row['email']}
                    USER_CONTACTS;

                if (!empty($row['phone'])) {
                    $fullNumber = trim("{$row['phone_code']} {$row['phone']}");
                    $contacts =
                    <<<USER_CONTACTS
                        <strong>Email:</strong> {$row['email']}<br/>
                        <strong>Phone:</strong> {$fullNumber}
                    USER_CONTACTS;
                }
                #endregion contacts

                $editUrl = __SITE_URL . 'webinar_requests/popup_forms/admin_edit/' . $row['id'];
                $editButton = <<<EDIT_BUTTON
                <a
                    class="ep-icon ep-icon_pencil fancyboxValidateModalDT fancybox.ajax"
                    href="{$editUrl}"
                    title="Schedule webinar"
                    data-title="Schedule webinar"
                ></a>
                EDIT_BUTTON;

                #region country column
                $country = '';
                if (!empty($row['country']['id'])) {
                    $countryFlag = getCountryFlag($row['country']['name']);
                    $country =
                    <<<COUNTRY_INFO
                        <a class="country dt_filter pull-left"
                            data-name="country"
                            data-title="Country"
                            data-value="{$row['id_country']}"
                            data-value-text="{$row['country']['name']}">
                            <img width="24" height="24" src="{$countryFlag}" title="Filter by: {$row['country']['name']}" alt="{$row['country']['name']}"/> <span class="display-ib mt-5">{$row['country']['name']}</span>
                        </a>
                    COUNTRY_INFO;
                }
                #endregion country column

                #region status column
                $attendedDate = '';
                switch ($row['status']) {
                    case 'new':
                        $statusClass = 'label label-warning';
                        $statusName = 'New';
                        break;
                    case 'attended':
                        $statusClass = 'label label-success';
                        $statusName = 'Attended';
                        $attendedDate = '<br/>' .getDateFormat($row['attended_date'], "Y-m-d", 'j M, Y');
                        break;
                    case 'not_attended':
                        $statusClass = 'label label-danger';
                        $statusName = 'Not Attended';
                        break;
                }
                $status =
                <<<STATUS_INFO
                    <span class="{$statusClass}">{$statusName}</span>{$attendedDate}
                STATUS_INFO;
                #endregion status column

                $attendButton = $attendNotButton = '';
                if($row['status'] == 'new'){
                    $link =  __SITE_URL . 'webinar_requests/ajax_operations/attend_webinar';
                    $attendButton = <<<ATTEND_BUTTON
                        <a href="#"
                            class="ep-icon ep-icon_ok txt-green confirm-dialog"
                            title="Mark as attended"
                            data-link="{$link}"
                            data-callback="status_record"
                            data-status="attended"
                            data-id="{$row['id']}"
                            data-message="Do you want to mark as attended?">
                        </a>
                    ATTEND_BUTTON;

                    $attendNotButton = <<<ATTEND_NOT_BUTTON
                        <a href="#"
                            class="ep-icon ep-icon_remove txt-red confirm-dialog"
                            title="Mark as not attended"
                            data-link="{$link}"
                            data-callback="status_record"
                            data-status="not_attended"
                            data-id="{$row['id']}"
                            data-message="Do you want to mark as not attended?">
                        </a>
                    ATTEND_NOT_BUTTON;
                }

                #region webinar
                $webinar = '';
                if (!empty($row['id_webinar'])) {
                    $linkWebinar =  __SITE_URL . 'webinars/administration/webinar-' . $row['id_webinar'];
                    $webinar = <<<WEBINAR_INFO
                        <a class="ep-icon ep-icon_filter txt-green dt_filter"
                            data-name="webinar"
                            data-title="Webinar"
                            data-value="{$row['id_webinar']}"
                            data-value-text="{$row['webinar']['title']}"
                            title="Webinar"></a>
                        <a href="{$linkWebinar}" target="_blank">{$row['webinar']['title']}</a>
                    WEBINAR_INFO;
                }
                #endregion webinar

                $aaData[] = [
                    'dt_id'        => $row['id'],
                    'dt_user'      => $user,
                    'dt_webinar'   => $webinar,
                    'dt_contacts'  => $contacts,
                    'dt_country'   => $country,
                    'dt_user_type' => $row['user_type'],
                    'dt_status'    => $status,
                    'dt_requested' => getDateFormat($row['requested_date']),
                    'dt_actions'   => $editButton . $attendButton . $attendNotButton,
                ];
            }
        } catch (\Throwable $th) {
            $aaData = [];
        }

        jsonResponse('', 'success', [
            'sEcho'                => $request->getInt('sEcho', 0),
            'iTotalRecords'        => $paginator['total'] ?? 0,
            'iTotalDisplayRecords' => $paginator['total'] ?? 0,
            'aaData'               => $aaData ?? [],
        ]);
    }

    public function popup_forms()
    {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        $op = uri()->segment(3);

        switch ($op) {
            case 'schedule_a_demo':

                /** @var Country_Model $countryModel*/
                $countryModel = model(Country_Model::class);

                /** @var Users_Model $usersModel*/
                $usersModel = model(Users_Model::class);

                $user = [];
                if (null !== $hash = request()->query->get('request')) {
                     /** @var Webinar_Requests_Model $requestsModel */
                    $requestsModel = model(Webinar_Requests_Model::class);

                    $user = $requestsModel->findOneBy([
                        'conditions' => [
                            'request_hash' => $hash,
                        ],
                        'order' => ['requested_date' => 'DESC'],
                    ]);
                }

                if(!empty($user)){
                    $groupLabel = $user['user_type'];
                    $user['country'] = $user['id_country'];
                }else{
                    $user = $usersModel->findOne(id_session());
                    $groupLabel = getGroupNameById($user['user_group'] ?? 0, false);
                }

                #region Phone codes
                $phoneCodesService = new PhoneCodesService($countryModel);
                $phoneCode = $phoneCodesService->findAllMatchingCountryCodes(
                    !empty($user['phone_code_id']) ? (int) $user['phone_code_id'] : null,
                    !empty($user['phone_code']) ? (string) $user['phone_code'] : null,
                    isset($user['country']) ? (int) $user['country'] : null,
                    PhoneCodesService::SORT_BY_PRIORITY
                )->first();
                #endregion Phone codes

                #region user groups
                $userGroups = [
                    'Buyer'             => 'Buyer',
                    'Seller'            => 'Seller',
                    'Manufacturer'      => 'Manufacturer',
                    'Freight Forwarder' => 'Freight Forwarder',
                    'Other'             => 'Other',
                ];
                #endregion user groups

                jsonResponse('', 'success', [
                        'content'           => views()->fetch('new/webinar_requests/popups/schedule_a_demo_view', [
                        'phoneCodes'        => $phoneCodesService->getCountryCodes(),
                        'selectedPhoneCode' => $phoneCode,
                        'portCountry'       => $countryModel->fetch_port_country(),
                        'webpackData'       => request()->query->get('webpackData') ?? null,
                        'userData'          => $user,
                        'userGroups'        => $userGroups,
                        'userType'          => $groupLabel,
                    ])
                ]);
            break;
            case 'admin_edit':
                checkPermisionAjaxDT('webinars_administration');
                $id = (int) uri()->segment(4);

                if (empty($id)) {
                    messageInModal(translate('systmess_error_invalid_data'));
                }

                /** @var Webinar_Requests_Model $requestsModel*/
                $requestsModel = model(Webinar_Requests_Model::class);

                $webinarRequest = $requestsModel->findOne($id);
                if (empty($webinarRequest)) {
                    messageInModal('No such webinar request found!');
                }

                /** @var Webinar_Model $webinarModel */
                $webinarModel = model(Webinar_Model::class);

                views()->display('admin/webinar_requests/form_view', [
                    'url'       => 'webinars/ajax_operations/edit',
                    'id'        => $id,
                    'webinars'  => $webinarModel->findAll(['order' => [
                        'start_date' => 'desc',
                    ]])
                ]);
            break;
        }
    }

    public function ajax_operations()
    {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        $op = uri()->segment(3);

        switch ($op) {
            case 'requesting_a_demo':
                $adapter = new ValidatorAdapter(library(TinyMVC_Library_validator::class));
                $validators = [
                    new PhoneValidator(
                        $adapter,
                        [
                            'code.invalid'       => translate('validation_invalid_phone_code', ['[COLUMN_NAME]' => translate('schedule_a_demo_popup_phone_label')]),
                            'phone.invalid'      => translate('validation_invalid_phone_number', ['[COLUMN_NAME]' => translate('schedule_a_demo_popup_phone_label')]),
                            'phone.unacceptable' => translate('validation_unacceptable_phone_number', ['[COLUMN_NAME]' => translate('schedule_a_demo_popup_phone_label')]),
                        ],
                        ['phone' => 'Phone', 'code' => 'Phone code'],
                        ['phone' => 'phone', 'code' => 'code']
                    ),
                    new EmailValidator($adapter, null, null, ['email' => 'email']),
                    new WebinarRequestValidator($adapter),
                ];
                $validator = new AggregateValidator($validators);

                if (!$validator->validate(request()->request->all())) {
                    \jsonResponse(
                        \array_map(
                            fn (ConstraintViolation $violation): ?string => $violation->getMessage(),
                            \iterator_to_array($validator->getViolations()->getIterator())
                        )
                    );
                }

                $request = request()->request;

                /** @var Webinar_Requests_Model $webinarRequestsModel */
                $webinarRequestsModel = model(Webinar_Requests_Model::class);

                $existing = $webinarRequestsModel->findOneBy(['conditions' => [
                    'email'     => $request->get('email'),
                    'status'    => 'new',
                    'webinar'   => 0,
                ]]);

                if(!empty($existing)){
                    jsonResponse(translate('systmess_already_sent_demonstration_request'));
                }
                #region Phone
                $phoneCodes = new PhoneCodesService(model(Country_Model::class));
                /** @var CountryCodeInterface $phoneCode */
                $phoneCode = $phoneCodes->findAllMatchingCountryCodes((int) $request->get('code'))->first();
                #endregion Phone

                #region insert request in DB
                 $formRequest = [
                    'id_user'       => id_session() ?? null,
                    'fname'         => $request->get('fname') ?? null,
                    'lname'         => $request->get('lname') ?? null,
                    'email'         => $request->get('email') ?? null,
                    'phone'         => $request->get('phone') ?? null,
                    'phone_code'    => $phoneCode ? $phoneCode->getName() : null,
                    'phone_code_id' => $phoneCode ? $phoneCode->getId() : null,
                    'id_country'    => $request->get('country') ?? null,
                    'user_type'     => $request->get('user_type') ?? null,
                    'request_hash'  => hash('sha3-512', $request->get('email')),
                ];

                if (empty($idRequest = $webinarRequestsModel->insertOne($formRequest))) {
                    jsonResponse(translate('systmess_internal_server_error'));
                } else {
                    #region mail user
                    /** @var MailerInterface $mailer */
                    $mailer = $this->getContainer()->get(MailerInterface::class);
                    $mailer->send(
                        (new DemoWebinarEpRequesting("{$formRequest['fname']} {$formRequest['lname']}"))
                            ->to(new Address($formRequest['email']))
                    );
                    #endregion mail user

                    /** @var Users_Model $usersModel */
                    $usersModel = model(Users_Model::class);

                    #region notify admin
                    /** @var Notify_Model $notifyModel */
                    $notifyModel = model(Notify_Model::class);
                    $notifyModel->send_notify([
                        'mess_code' => 'new_webinar_request',
                        'id_users'  => array_column($usersModel->findUsersWithRights(['webinars_administration']) ?? [], 'idu'),
                        'replace'   => [
                            '[LINK]' => __SITE_URL . 'webinar_requests/administration',
                        ],
                        'systmess' => true,
                    ]);
                    #region notify admin

                    #region store user as lead in Zoho
                    if ('prod' === config('env.APP_ENV'))
                    {
                        $userData = $usersModel->findOneBy([
                            'conditions' => ['email' => $formRequest['email']]
                        ]);

                        if (empty($userData)) {
                            /** @var TinyMVC_Library_Zoho_crm $crmLibrary */
                            $crmLibrary = library(TinyMVC_Library_Zoho_crm::class);

                            $crmLibrary->createLead([
                                'first_name'  => $formRequest['fname'],
                                'last_name'   => $formRequest['lname'],
                                'email'       => $formRequest['email'],
                                'phone'       => "{$formRequest['phone_code']}-{$formRequest['phone']}",
                                'lead_type'   => 'Schedule a demo webinar',
                                'lead_source' => 'ExportPortal API',
                            ]);

                            $webinarRequestsModel->updateOne($idRequest, ['converted_to_lead' => 1]);
                        }
                    }
                    #endregion store user as lead in Zoho
                }
                #endregion insert request in DB

                jsonResponse(translate("systmess_success_demonstration_request"), 'success');
            break;
            case 'attach_webinar':

                $idRequest = request()->request->getInt('id');
                $idWebinar = request()->request->getInt('webinar');

                /** @var Webinar_Requests_Model $requestsModel*/
                $requestsModel = model(Webinar_Requests_Model::class);

                $webinarRequest = $requestsModel->findOne($idRequest);
                if (empty($webinarRequest)) {
                    jsonResponse('No such webinar request found!');
                }

                /** @var Webinar_Model $webinarModel */
                $webinarModel = model(Webinar_Model::class);

                $webinar = $webinarModel->findOne($idWebinar);
                if(empty($webinar)){
                    jsonResponse('No such webinar found!');
                }

                $requestsModel->updateOne($idRequest, [
                    'id_webinar' => $idWebinar
                ]);

                jsonResponse('Webinar attached successfully!', 'success');
            break;
            case 'attend_webinar':

                $idRequest = request()->request->getInt('id');
                $status = request()->request->get('status');

                /** @var Webinar_Requests_Model $requestsModel*/
                $requestsModel = model(Webinar_Requests_Model::class);

                $webinarRequest = $requestsModel->findOne($idRequest);
                if (empty($webinarRequest)) {
                    jsonResponse('No such webinar request found!');
                }

                if(!in_array($status, ['attended', 'not_attended'])){
                    jsonResponse('No such status exists!');
                }

                if(empty($webinarRequest['id_webinar'])){
                    jsonResponse('A webinar was not assigned to this request yet!');
                }

                /** @var Webinar_Model $webinarModel*/
                $webinarModel = model(Webinar_Model::class);

                $nextWebinar = $webinarModel->findOneBy([
                    'conditions' => ['start_from' => new \DateTimeImmutable()],
                    'order'      => ['start_date' => 'asc']
                ]);

                if(empty($nextWebinar) && 'not_attended' === $status){
                    jsonResponse('No upcoming webinar set yet! Please add a new webinar first');
                }

                $requestsModel->updateOne($idRequest, [
                    'status'        => $status,
                    'attended_date' => new \DateTimeImmutable()
                ]);

                /** @var MailerInterface $mailer */
                $mailer = $this->getContainer()->get(MailerInterface::class);

                if('not_attended' === $status)
                {

                    $mailer->send(
                        (new DemoWebinarEpNext("{$webinarRequest['fname']} {$webinarRequest['lname']}", $nextWebinar['start_date']->format('j M, Y'), $nextWebinar['start_date']->format('H:i'), $webinarRequest['request_hash']))
                            ->to(new Address($webinarRequest['email']))
                    );

                }else{

                    if (!empty($webinarRequest['id_user'])) {
                        $mailer->send(
                            (new DemoWebinarEpRegistered("{$webinarRequest['fname']} {$webinarRequest['lname']}"))
                                ->to(new Address($webinarRequest['email']))
                        );
                    } else {
                        $mailer->send(
                            (new DemoWebinarEpUnRegistered("{$webinarRequest['fname']} {$webinarRequest['lname']}"))
                                ->to(new Address($webinarRequest['email']))
                        );
                    }
                }

                jsonResponse('Webinar status changed and email sent successfully!', 'success');
            break;
        }
    }
}
// End of file webinar_requests.php
// Location: /tinymvc/myapp/controllers/webinar_requests.php
