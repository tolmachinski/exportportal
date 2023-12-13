<?php

use App\Common\Database\Relations\RelationInterface;
use App\Common\Exceptions\NotFoundException;
use App\Common\Exceptions\OwnershipException;
use App\Email\ConfirmEventAttend;
use App\Email\ShareCrEvent;
use App\Filesystem\CrEventFilePathGenerator;
use App\Services\PhoneCodesService;
use ExportPortal\Bridge\Mailer\Mime\RefAddress;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;


use League\Flysystem\FilesystemOperator;
use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Company services controller.
 *
 * @property \Cr_events_Model           $cr_events
 * @property \Cr_domains_Model          $cr_domains
 * @property \Cr_users_Model            $cr_users
 * @property \Tinymvc_Library_Cleanhtml $clean
 * @property \TinyMVC_Library_Cookies   $cookies
 * @property \TinyMVC_Library_Session   $session
 * @property \TinyMVC_Library_URI       $uri
 * @property \TinyMVC_Library_Upload    $upload
 * @property \TinyMVC_Library_validator $validator
 * @property \TinyMVC_Library_Wall      $wall
 * @property \TinyMVC_Load              $load
 * @property \TinyMVC_View              $view
 * @property \User_Model                $user
 */
class Cr_events_Controller extends TinyMVC_Controller
{
    const IMAGE_TEMP_PATH = 'temp/cr_events';

    private FilesystemOperator $storage;

    public function __construct(ContainerInterface $container)
    {
        /** @var FilesystemProviderInterface */
        $storageProvider = $container->get(FilesystemProviderInterface::class);
        $this->storage = $storageProvider->storage('public.storage');
    }

    public function administration()
    {
        checkIsLogged();
        checkPermision('cr_events_administration');

        $this->view->assign([
            'title'     => 'CR Events',
            'countries' => model('cr_domains', 'cr_domains')->get_cr_domains(),
        ]);

        $this->view->display('admin/header_view');
        $this->view->display('admin/cr/events/index_view');
        $this->view->display('admin/footer_view');
    }

    public function my()
    {
        checkIsLogged();
        checkPermision('attend_cr_event,manage_cr_personal_events');

        $this->view->assign([
            'title'       => translate('cr_events_dashboard_page_title_text', null, true),
            'countries'   => model('cr_domains')->get_cr_domains(),
            'categories'  => model('cr_events')->get_types(),
        ]);

        $this->view->display('new/header_view');
        $this->view->display('new/cr/events/my/index_view');
        $this->view->display('new/footer_view');
    }

    public function confirm_register()
    {
        /** @var \Cr_events_Model $model */
        $model = model('cr_events');
        $hash = $this->uri->segment(5);
        $event_id = (int) $this->uri->segment(3);
        $attendance_id = (int) $this->uri->segment(4);

        $event = $model->get_event($event_id);
        if (empty($event_id) || empty($event)) {
            show_404();
        }

        $attendance = $model->get_attend_record($attendance_id);
        if (empty($attendance_id) || empty($attendance)) {
            show_404();
        }

        if (md5($attendance['attend_email'] . $attendance_id) !== $hash) {
            show_404();
        }

        $event_url = getSubDomainURL($event['country_alias'], "event/{$event['event_url']}");
        if ('confirmed' === $attendance['attend_status']) {
            $this->session->setMessages(translate("systmess_error_email_is_confirmed"), 'errors');
            headerRedirect($event_url);
        }

        $model->update_attend_record($attendance_id, ['attend_status' => 'confirmed']);
        $this->session->setMessages(translate("systmess_success_registered_for_the_event"), 'success');
        headerRedirect($event_url);
    }

    public function popup_forms()
    {
        checkIsAjax();

        switch ($this->uri->segment(3)) {
            case 'share_event':
                checkPermisionAjaxModal('share_this');

                $event_id = (int) $this->uri->segment(4);
                if (
                    empty($event_id) ||
                    !model('cr_events')->is_event_exists($event_id)
                ) {
                    messageInModal('Error: This event does not exist. Please refresh this page.');
                }

                $this->view->display('new/cr/events/popup_share_view', [
                    'action'     => 'cr_events/ajax_send_email/share',
                    'id_event'   => $event_id,
                ]);

                break;
            case 'email_event':
                checkPermisionAjaxModal('email_this');

                $event_id = (int) $this->uri->segment(4);
                if (
                    empty($event_id) ||
                    !model('cr_events')->is_event_exists($event_id)
                ) {
                    messageInModal('Error: This event does not exist. Please refresh this page.');
                }

                $this->view->display('new/cr/events/popup_email_view', [
                    'action'     => 'cr_events/ajax_send_email/email',
                    'max_emails' => config('email_this_max_email_count', 10),
                    'id_event'   => $event_id,
                ]);

                break;
            case 'add_event':
                checkIsLoggedAjaxModal();
                checkPermisionAjaxModal('cr_events_administration,manage_cr_personal_events');

                if (have_right('cr_events_administration')) {
                    $view = 'admin/cr/events/event_form_view';
                } elseif (have_right('manage_cr_personal_events')) {
                    $view = 'new/cr/events/my/add_event_form_view';
                }

                // Prepare rule for allowed file types
                $formats = explode(',', config('fileupload_image_formats', 'jpg,jpeg,png,gif,bmp'));
                $mimetypes = array_filter(array_unique(array_map(
                    function ($extension) {
                        return \Hoa\Mime\Mime::getMimeFromExtension($extension);
                    },
                    $formats
                )));

                $accept = implode(', ', $mimetypes);
                $formats = implode('|', $formats);
                $mimetypes = implode('|', array_map(function ($type) { return preg_quote($type); }, $mimetypes));

                $this->view->display($view, [
                    'action'                   => __SITE_URL . 'cr_events/ajax_create_event',
                    'image_path'               => model('cr_events')->event_images_path,
                    'countries'                => model('cr_domains')->get_cr_domains(),
                    'types'                    => model('cr_events')->get_types(),
                    'upload_folder'            => encriptedFolderName(),
                    'fileupload_max_file_size' => config('fileupload_cr_image_max_file_size', 1024 * 1024 * 3),
                    'fileupload_limits'        => [
                        'amount'              => 1,
                        'accept'              => $accept,
                        'formats'             => $formats,
                        'mimetypes'           => $mimetypes,
                        'image_size'          => config('fileupload_cr_image_max_file_size', 1024 * 1024 * 3),
                        'image_size_readable' => config('fileupload_cr_image_max_file_size_placeholder', '3MB'),
                        'image_width'         => config('fileupload_cr_image_min_width', 800),
                        'image_height'        => config('fileupload_cr_image_min_height', 600),
                    ],
                ]);

                break;
            case 'edit_event':
                checkIsLoggedAjaxModal();
                checkPermisionAjaxModal('cr_events_administration,manage_cr_personal_events');

                try {
                    $user_id = have_right('cr_events_administration') ? null : (int) privileged_user_id();
                    $eventId = (int) $this->uri->segment(4);
                    $event = model('cr_events')->find_event($eventId, $user_id);

                } catch (NotFoundException $exception) {
                    messageInModal('Error: This event does not exist. Please refresh this page.');

                } catch (OwnershipException $exception) {
                    messageInModal(' This event does not belongs to you.');
                }

                if (have_right('cr_events_administration')) {
                    $view = 'admin/cr/events/event_form_view';
                } elseif (have_right('manage_cr_personal_events')) {
                    $view = 'new/cr/events/my/edit_event_form_view';
                }

                // Resolve event image
                $event['event_image_url'] = null;
                $event['event_image_path'] = null;
                if (!empty($event['event_image'])) {
                    /**
                     * Refactoring on hold
                     */
                    $eventImagePath = CrEventFilePathGenerator::eventPath($eventId, $event['event_image']);
                    if ($this->storage->fileExists($eventImagePath)) {
                        $event['event_image_url'] = getDisplayImageLink([
                            '{ID}' => $eventId,
                            '{FILE_NAME}' => $event['event_image']
                        ], 'cr.event', [
                            'thumbSize' => 'original'
                        ]);

                        $event['event_image_path'] = $eventImagePath;
                    } else {
                        $event['event_image'] = null;
                    }
                }

                // Prepare rule for allowed file types
                $formats = explode(',', config('fileupload_image_formats', 'jpg,jpeg,png,gif,bmp'));
                $mimetypes = array_filter(array_unique(array_map(
                    function ($extension) {
                        return \Hoa\Mime\Mime::getMimeFromExtension($extension);
                    },
                    $formats
                )));

                $accept = implode(', ', $mimetypes);
                $formats = implode('|', $formats);
                $mimetypes = implode('|', array_map(function ($type) { return preg_quote($type); }, $mimetypes));

                $this->view->display($view, [
                    'action'                   => __SITE_URL . 'cr_events/ajax_update_event',
                    'event'                    => $event,
                    'image_path'               => model('cr_events')->event_images_path,
                    'countries'                => model('cr_domains')->get_cr_domains(),
                    'states'                   => model('country')->get_states((int) $event['event_id_country']),
                    'city'                     => model('country')->get_city((int) $event['event_id_city']),
                    'types'                    => model('cr_events')->get_types(),
                    'upload_folder'            => encriptedFolderName(),
                    'fileupload_max_file_size' => config('fileupload_cr_image_max_file_size', 1024 * 1024 * 3),
                    'fileupload_limits'        => [
                        'amount'              => 1,
                        'accept'              => $accept,
                        'formats'             => $formats,
                        'mimetypes'           => $mimetypes,
                        'image_size'          => config('fileupload_cr_image_max_file_size', 1024 * 1024 * 3),
                        'image_size_readable' => config('fileupload_cr_image_max_file_size_placeholder', '3MB'),
                        'image_width'         => config('fileupload_cr_image_min_width', 800),
                        'image_height'        => config('fileupload_cr_image_min_height', 600),
                    ],
                ]);
            break;
            case 'attend_users_list':
                checkIsLoggedAjaxModal();
                checkPermisionAjaxModal('cr_events_administration,manage_cr_personal_events');

                $event_id = (int) $this->uri->segment(4);
                if (
                    empty($event_id) ||
                    !model('cr_events')->is_event_exists($event_id)
                ) {
                    messageInModal('Error: This event does not exist. Please refresh this page.');
                }

                $phoneUtil = PhoneNumberUtil::getInstance();
                $users = model('cr_events')->get_attend_records_by_event($event_id);
                $new_users = [];
                $registered_users = [];

                foreach ($users as $user) {
                    $user['attend_fullname'] = cleanOutput(trim("{$user['attend_fname']} {$user['attend_lname']}"));
                    $user['attend_email'] = cleanOutput($user['attend_email']);
                    $user['attend_email_link'] = "mailto:{$user['attend_email']}";

                    try {
                        $user_phone = $phoneUtil->parse(trim("{$user['attend_phone_code']} {$user['attend_phone']}"));
                        $user['attend_phone_link'] = $phoneUtil->format($user_phone, PhoneNumberFormat::RFC3966);
                        $user['attend_phone'] = $phoneUtil->format($user_phone, PhoneNumberFormat::INTERNATIONAL);

                    } catch (NumberParseException $exception) {
                        $user['attend_phone_link'] = null;
                        $user['attend_phone'] = '&mdash;';
                    }

                    if (empty($user['id_user'])) {
                        $new_users[] = $user;
                    } else {
                        $registered_users[] = $user;
                    }
                }

                $this->view->display(
                    'new/cr/events/my/attendees_form_view',
                    [
                        'new_users'        => $new_users,
                        'registered_users' => $registered_users,
                    ]
                );
            break;
            case 'attended_users':
                checkIsLoggedAjaxModal();
                checkPermisionAjaxModal('cr_events_administration');

                $event_id = (int) $this->uri->segment(4);
                if (
                    empty($event_id) ||
                    !model('cr_events')->is_event_exists($event_id)
                ) {
                    messageInModal('Error: This event does not exist. Please refresh this page.');
                }

                $phoneUtil = PhoneNumberUtil::getInstance();
                $users = model('cr_events')->get_attend_records_by_event($event_id);
                $new_users = [];
                $registered_users = [];

                foreach ($users as $user) {
                    $user['attend_fullname'] = cleanOutput(trim("{$user['attend_fname']} {$user['attend_lname']}"));
                    $user['attend_email'] = cleanOutput($user['attend_email']);
                    $user['attend_email_link'] = "mailto:{$user['attend_email']}";

                    try {
                        $user_phone = $phoneUtil->parse(trim("{$user['attend_phone_code']} {$user['attend_phone']}"));
                        $user['attend_phone_link'] = $phoneUtil->format($user_phone, PhoneNumberFormat::RFC3966);
                        $user['attend_phone'] = $phoneUtil->format($user_phone, PhoneNumberFormat::INTERNATIONAL);

                    } catch (NumberParseException $exception) {
                        $user['attend_phone_link'] = null;
                        $user['attend_phone'] = '&mdash;';
                    }

                    if (empty($user['id_user'])) {
                        $new_users[] = $user;
                    } else {
                        $registered_users[] = $user;
                    }
                }

                $this->view->display(
                    'admin/cr/user/events/attend_users_view',
                    [
                        'new_users'        => $new_users,
                        'registered_users' => $registered_users,
                    ]
                );
            break;
            case 'attend_event':
                $event_id = (int) $this->uri->segment(4);
                if (
                    empty($event_id) ||
                    !model('cr_events')->is_event_exists($event_id)
                ) {
                    messageInModal('Error: This event does not exist. Please refresh this page.');
                }

                $this->view->display('new/cr/events/popup_attend_view', [
                    'phone_codes' => (new PhoneCodesService(model('country')))->getCountryCodes(),
                    'action'      => __SITE_URL . 'cr_events/ajax_attend_operation',
                    'id_event'    => $event_id,
                ]);

                break;
            default:
                messageInModal('Wrong data has been send.');

                break;
        }
    }

    public function ajax_send_email()
    {
        checkIsAjax();
        checkIsLoggedAjax();
        is_allowed('freq_allowed_send_email_to_user');

        $op = $this->uri->segment(3);

        switch ($op) {
            case 'email':
                checkPermisionAjax('email_this');

                $emails_limit = config('email_this_max_email_count', 10);
                $validator_rules = [
                    [
                        'field' => 'emails',
                        'label' => translate('general_modal_send_mail_field_addresses_label_text', null, true),
                        'rules' => ['required' => '', 'no_whitespaces' => '', 'valid_emails' => '', "max_emails_count[{$emails_limit}]" => ''],
                    ],
                    [
                        'field' => 'message',
                        'label' => translate('general_modal_send_mail_field_message_label_text', null, true),
                        'rules' => ['required' => '', 'max_len[1000]' => ''],
                    ],
                    [
                        'field' => 'id',
                        'label' => translate('cr_events_dasboard_modal_event_field_event_label_text', null, true),
                        'rules' => ['required' => '', 'integer' => ''],
                    ],
                ];

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $user_id = (int) privileged_user_id();
                $event_id = (int) arrayGet($_POST, 'id');

                try {
                    $event = model('cr_events')->find_event($event_id);

                } catch (NotFoundException $exception) {
                    jsonResponse('Error: This event does not exist. Please refresh this page.');
                }

                $filteredEmails = filter_email(arrayGet($_POST, 'emails', []));
                if (empty($filteredEmails)) {
                    jsonResponse('Error: Please write at least one valid email address.');
                }

                $crDomain = model('cr_domains')->get_cr_domain(['id_country' => (int) $event['event_id_country']]);
                $eventLink = getSubDomainURL(arrayGet($crDomain, 'country_alias'), "event/{$event['event_url']}");
                $userName = user_name_session();

                try {
                    /** @var MailerInterface $mailer */
                    $mailer = $this->getContainer()->get(MailerInterface::class);
                    $mailer->send(
                        (new ShareCrEvent($userName, cleanInput(request()->request->get('message')), $eventLink, $event))
                            ->to(...array_map(fn (string $to) => new Address($to), $filteredEmails))
                            ->subjectContext([
                                '[userName]' => $userName,
                            ])
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
                        'field' => 'id',
                        'label' => translate('cr_events_dasboard_modal_event_field_event_label_text', null, true),
                        'rules' => ['required' => '', 'integer' => ''],
                    ],
                    [
                        'field' => 'message',
                        'label' => translate('general_modal_share_field_message_label_text', null, true),
                        'rules' => ['required' => '', 'max_len[1000]' => ''],
                    ],
                ];

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $user_id = (int) privileged_user_id();
                $event_id = (int) arrayGet($_POST, 'id');

                try {
                    $event = model('cr_events')->find_event($event_id);

                } catch (NotFoundException $exception) {
                    jsonResponse('Error: This event does not exist. Please refresh this page.');
                }

                $filteredEmails = model(Followers_Model::class)->getFollowersEmails($user_id);
                if (empty($filteredEmails)) {
                    jsonResponse('You have no followers. The message has not been sent.');
                }

                $crDomain = model(Cr_Domains_Model::class)->get_cr_domain(['id_country' => (int) $event['event_id_country']]);
                $eventLink = getSubDomainURL(arrayGet($crDomain, 'country_alias'), "event/{$event['event_url']}");
                $userName = user_name_session();

                try {
                    /** @var MailerInterface $mailer */
                    $mailer = $this->getContainer()->get(MailerInterface::class);
                    $mailer->send(
                        (new ShareCrEvent($userName, cleanInput(request()->request->get('message')), $eventLink, $event))
                            ->to(...array_map(fn (string $to) => new Address($to), $filteredEmails))
                            ->subjectContext([
                                '[userName]' => $userName,
                            ])
                    );

                } catch (\Throwable $th) {
                    jsonResponse(translate('email_has_not_been_sent'));
                }

                jsonResponse('You have successfully shared this information with your followers.', 'success');

            break;
        }
    }

    public function ajax_list_my_events_dt()
    {
        checkIsAjax();
        checkIsLoggedAjaxDT();
        checkPermisionAjaxDT('attend_cr_event,manage_cr_personal_events');

        $skip = isset($_POST['iDisplayStart']) ? (int) cleanInput($_POST['iDisplayStart']) : null;
        $limit = isset($_POST['iDisplayLength']) ? (int) cleanInput($_POST['iDisplayLength']) : null;
        $order = [];
        $with = [
            'type'     => function (RelationInterface $relation) { $relation->getQuery()->select('id, event_type_name as name'); },
            'country'  => function (RelationInterface $relation) { $relation->getQuery()->select('id, country as name, country_alias as alias'); },
            'state'    => function (RelationInterface $relation) { $relation->getQuery()->select('id, state as name'); },
            'city'     => function (RelationInterface $relation) { $relation->getQuery()->select('id, city as name'); },
        ];

        if (have_right('manage_cr_personal_events')) {
            $conditions = [
                'assigned_user' => privileged_user_id(),
                'visible'       => true,
            ];
        } else {
            $conditions = [
                'attendee' => privileged_user_id(),
                'visible'  => true,
            ];
        }

        if (!empty($_POST['keywords'])) {
            $conditions['search'] = cut_str(cleanInput($_POST['keywords'], 200));
        }

        if (isset($_POST['status'])) {
            $conditions['status'] = cleanInput($_POST['status']);
        }

        if (isset($_POST['country'])) {
            $conditions['country'] = (int) $_POST['country'];
        }

        if (isset($_POST['state'])) {
            $conditions['state'] = (int) $_POST['state'];
        }

        if (isset($_POST['city'])) {
            $conditions['city'] = (int) $_POST['city'];
        }

        if (isset($_POST['category'])) {
            $conditions['type'] = (int) $_POST['category'];
        }

        if (isset($_POST['event_type'])) {
            if ('expired' === $_POST['event_type']) {
                $conditions['expired_today'] = true;
            }

            if ('active' === $_POST['event_type']) {
                $conditions['active_today'] = true;
            }
        }

        if (isset($_POST['created_from'])) {
            $conditions['created_from'] = formatDate(cleanInput($_POST['created_from']) . ' 00:00:00', 'Y-m-d H:i:s');
        }

        if (isset($_POST['created_to'])) {
            $conditions['created_to'] = formatDate(cleanInput($_POST['created_to']) . ' 23:59:59', 'Y-m-d H:i:s');
        }

        if (isset($_POST['start_from'])) {
            $conditions['start_from'] = formatDate(cleanInput($_POST['start_from']) . ' 00:00:00', 'Y-m-d H:i:s');
        }

        if (isset($_POST['start_to'])) {
            $conditions['start_to'] = formatDate(cleanInput($_POST['start_to']) . ' 23:59:59', 'Y-m-d H:i:s');
        }

        if (isset($_POST['end_from'])) {
            $conditions['end_from'] = formatDate(cleanInput($_POST['end_from']) . ' 00:00:00', 'Y-m-d H:i:s');
        }

        if (isset($_POST['end_to'])) {
            $conditions['end_to'] = formatDate(cleanInput($_POST['end_to']) . ' 23:59:59', 'Y-m-d H:i:s');
        }

        $order = array_column(dt_ordering($_POST, [
            'event'     => 'event_name',
            'starts_at' => 'event_date_start',
            'ends_at'   => 'event_date_end',
        ]), 'direction', 'column');

        /** @var \Cr_events_Model $model */
        $model = model('cr_events');
        $events = $model->get_events(compact('conditions', 'order', 'limit', 'with', 'skip'));
        $total = $model->count_events(compact('conditions'));

        $output = [
            'sEcho'                => (int) $_POST['sEcho'],
            'iTotalRecords'        => $total,
            'iTotalDisplayRecords' => $total,
            'aaData'               => [],
        ];

        if (!empty($events)) {
            $output['aaData'] = $this->my_events($events);
        }

        jsonResponse('', 'success', $output);
    }

    public function ajax_events_administration()
    {
        checkIsAjax();
        checkIsLoggedAjaxDT();
        checkPermisionAjaxDT('cr_events_administration');

        $skip = isset($_POST['iDisplayStart']) ? (int) cleanInput($_POST['iDisplayStart']) : null;
        $limit = isset($_POST['iDisplayLength']) ? (int) cleanInput($_POST['iDisplayLength']) : null;
        $order = [];
        $with = [
            'type'     => function (RelationInterface $relation) { $relation->getQuery()->select('id, event_type_name as name'); },
            'country'  => function (RelationInterface $relation) { $relation->getQuery()->select('id, country as name'); },
            'state'    => function (RelationInterface $relation) { $relation->getQuery()->select('id, state as name'); },
            'city'     => function (RelationInterface $relation) { $relation->getQuery()->select('id, city as name'); },
        ];

        if (!empty($_POST['keywords'])) {
            $conditions['search'] = cut_str(cleanInput($_POST['keywords'], 200));
        }

        if (isset($_POST['visible'])) {
            $conditions['visible'] = (int) $_POST['visible'];
        }

        if (isset($_POST['status'])) {
            $conditions['status'] = cleanInput($_POST['status']);
        }

        if (isset($_POST['country'])) {
            $conditions['country'] = (int) $_POST['country'];
        }

        if (isset($_POST['states'])) {
            $conditions['state'] = (int) $_POST['states'];
        }

        if (isset($_POST['city'])) {
            $conditions['city'] = (int) $_POST['city'];
        }

        if (isset($_POST['category'])) {
            $conditions['type'] = (int) $_POST['category'];
        }

        if (isset($_POST['event_type'])) {
            if ('expired' === $_POST['event_type']) {
                $conditions['expired_today'] = true;
            }

            if ('active' === $_POST['event_type']) {
                $conditions['active_today'] = true;
            }
        }

        if (isset($_POST['date_start_from'])) {
            $conditions['start_from'] = formatDate(cleanInput($_POST['date_start_from']) . ' 00:00:00', 'Y-m-d H:i:s');
        }

        if (isset($_POST['date_start_to'])) {
            $conditions['start_to'] = formatDate(cleanInput($_POST['date_start_to']) . ' 23:59:59', 'Y-m-d H:i:s');
        }

        if (isset($_POST['date_end_from'])) {
            $conditions['end_from'] = formatDate(cleanInput($_POST['date_end_from']) . ' 00:00:00', 'Y-m-d H:i:s');
        }

        if (isset($_POST['date_end_to'])) {
            $conditions['end_to'] = formatDate(cleanInput($_POST['date_end_to']) . ' 23:59:59', 'Y-m-d H:i:s');
        }

        if (isset($_POST['approved_type']) && 'mine' === $_POST['approved_type']) {
            $conditions['manager'] = id_session();
        }

        $order = array_column(dt_ordering($_POST, [
            'dt_id'                => 'id_event',
            'dt_date_start'        => 'event_date_start',
            'dt_date_end'          => 'event_date_end',
            'dt_count_ambassadors' => 'event_count_ambassadors',
            'dt_count_users'       => 'event_count_users',
            'dt_status'            => 'event_status',
            'dt_visible'           => 'event_is_visible',
        ]), 'direction', 'column');

        /** @var \Cr_events_Model $model */
        $model = model('cr_events');
        $events = $model->get_events(compact('conditions', 'order', 'limit', 'with', 'skip'));
        $total = $model->count_events(compact('conditions'));

        jsonResponse('', 'success', [
            'sEcho'                => (int) $_POST['sEcho'],
            'iTotalRecords'        => $total,
            'iTotalDisplayRecords' => $total,
            'aaData'               => $this->administration_events($events),
        ]);
    }

    public function ajax_toggle_visible()
    {
        checkIsAjax();
        checkIsLoggedAjax();
        checkPermisionAjax('cr_events_administration');

        /** @var \Cr_events_Model $model */
        $model = model('cr_events');
        $event_id = (int) arrayGet($_POST, 'id');
        $visibility = (int) arrayGet($_POST, 'visible_value');

        if (
            empty($event_id) ||
            !$model->is_event_exists($event_id)
        ) {
            jsonResponse('Error: This event does not exist. Please refresh this page.');
        }

        if (!$model->update_event($event_id, ['event_is_visible' => $visibility])) {
            jsonResponse('Failed to change visibility of the event. Please try again later.');
        }

        jsonResponse('', 'success');
    }

    public function ajax_remove_event()
    {
        checkIsAjax();
        checkIsLoggedAjax();
        checkPermisionAjax('cr_events_administration');

        /** @var \Cr_events_Model $model */
        $model = model('cr_events');
        $event_id = (int) arrayGet($_POST, 'id');

        // Find event
        try {
            $event = $model->find_event($event_id);

        } catch (NotFoundException $exception) {
            jsonResponse('Error: This event does not exist. Please refresh this page.');
        }

        // Remove images
        $basepath = "{$model->event_images_path}/{$event['id_event']}";
        if (!file_exists($basepath) || !is_dir($basepath)) {
            jsonResponse('The directory with file is not found.');
        }

        if (!empty($event['event_image'])) {
            if (!file_exists("{$basepath}/{$event['event_image']}")) {
                jsonResponse('Error: event image not found');
            }

            $files = glob("{$basepath}/*{$event['event_image']}");
            foreach ($files as $file) {
                if (!@unlink($file)) {
                    jsonResponse('Failed to properly delete event image. Please try again later.');
                }
            }
        }
        // Remove directory
        removeDirectory($basepath);

        // Remove event
        if (!$model->remove_event($event_id)) {
            jsonResponse('Failed to delete event. Please try again later.');
        }

        jsonResponse('', 'success');
    }

    public function ajax_approve_event()
    {
        checkIsAjax();
        checkIsLoggedAjax();
        checkPermisionAjax('cr_events_administration');

        /** @var \Cr_events_Model $model */
        $model = model('cr_events');
        $event_id = (int) arrayGet($_POST, 'id');
        if (
            empty($event_id) ||
            !$model->is_event_exists($event_id)
        ) {
            jsonResponse('Error: This event does not exist. Please refresh this page.');
        }

        if (
            !$model->update_event($event_id, [
                'event_ep_manager' => id_session(),
                'event_status'     => 'approved',
            ])
        ) {
            jsonResponse('Failed to approve of the event. Please try again later.');
        }

        jsonResponse('', 'success');
    }

    public function ajax_upload_image()
    {
        checkIsAjax();
        checkIsLoggedAjax();
        checkPermisionAjaxDT('cr_events_administration,attend_cr_event,manage_cr_personal_events');

        $files = arrayGet($_FILES, 'files');
        if (null === $files) {
            jsonResponse('Error: Please select file to upload.');
        }

        $upload_folder = checkEncriptedFolder(cleanInput($this->uri->segment(3)));
        if (false === $upload_folder) {
            jsonResponse('Error: File upload path is not correct.');
        }

        $session_id = id_session();
        $basepath = self::IMAGE_TEMP_PATH . "/{$session_id}/{$upload_folder}/main";
        create_dir($basepath);

        $result = $this->upload->upload_images_new([
            'files'       => $files,
            'destination' => $basepath,
            'resize'      => '800xR',
            'rules'       => [
                'size'       => config('fileupload_cr_image_max_file_size', 3 * 1024 * 1024),
                'min_width'  => config('fileupload_cr_image_min_width', 800),
                'min_height' => config('fileupload_cr_image_min_height', 600),
            ],
        ]);

        if (!empty($result['errors'])) {
            jsonResponse(implode('<br>', $result['errors']));
        }

        $file = arrayGet($result, '0');
        if (null === $file) {
            jsonResponse('Failed to upload file properly. Please contact administration to resolve this issue.');
        }

        jsonResponse('', 'success', [
            'path' => "{$basepath}/{$file['new_name']}",
            'name' => $file['new_name'],
            'old'  => $file['old_name'],
        ]);
    }

    public function ajax_event_delete_temp_files()
    {
        checkIsAjax();
        checkIsLoggedAjax();
        checkPermisionAjaxDT('attend_cr_event,manage_cr_personal_events');

        $file = arrayGet($_POST, 'file');
        $upload_folder = checkEncriptedFolder($this->uri->segment(3));
        if (null === $file) {
            jsonResponse('Error: File name is not correct.');
        }

        if (false === $upload_folder) {
            jsonResponse('Error: File upload path is not correct.');
        }

        $session_id = id_session();
        $basepath = self::IMAGE_TEMP_PATH . "/{$session_id}/{$upload_folder}/main";
        $filepath = "{$basepath}/{$file}";
        if (!file_exists($basepath) || !is_dir($basepath)) {
            jsonResponse('Error: Upload path is not correct.');
        }

        if (!file_exists($filepath) || !is_readable($filepath)) {
            jsonResponse('File is not found');
        }

        if (!@unlink($filepath)) {
            jsonResponse('Failed to delete file right now. Please try again later');
        }

        jsonResponse('', 'success');
    }

    public function ajax_event_delete_files()
    {
        checkIsAjax();
        checkIsLoggedAjax();
        checkPermisionAjax('cr_events_administration,manage_cr_personal_events');

        /** @var \Cr_events_Model $model */
        $model = model('cr_events');
        $event_id = (int) arrayGet($_GET, 'id', function () { return arrayGet($_POST, 'file'); });
        $user_id = (int) privileged_user_id();

        // Find event
        try {
            if (have_right('cr_events_administration')) {
                $event = $model->find_event($event_id);
            } else {
                $event = $model->find_event($event_id, $user_id);
            }

        } catch (NotFoundException $exception) {
            jsonResponse('Error: This event does not exist. Please refresh this page.');

        } catch (OwnershipException $exception) {
            jsonResponse(' This event does not belongs to you.');
        }

        $basepath = "{$this->cr_events->event_images_path}/{$event['id_event']}";
        if (!file_exists($basepath) || !is_dir($basepath)) {
            jsonResponse('The directory with file is not found.');
        }

        if (!empty($event['event_image'])) {
            if (!file_exists("{$basepath}/{$event['event_image']}")) {
                jsonResponse('Error: event image not found');
            }

            $files = glob("{$basepath}/*{$event['event_image']}");
            foreach ($files as $file) {
                if (!@unlink($file)) {
                    jsonResponse('Failed to properly delete image. Please contact administration to resolve this issue');
                }
            }
        }

        jsonResponse('', 'success');
    }

    public function ajax_attend_operation()
    {

        if (!ajax_validate_google_recaptcha()) {
            jsonResponse(translate('systmess_error_you_didnt_pass_bot_check'));
        }

        $phone_codes_service = new PhoneCodesService(model('country'));
        $validator_rules = [
            [
                'field' => 'id_event',
                'label' => translate('cr_events_dasboard_modal_event_field_event_label_text', null, true),
                'rules' => ['required' => ''],
            ],
            [
                'field' => 'first_name',
                'label' => translate('cr_events_dashboard_modal_event_field_first_name_label_text', null, true),
                'rules' => ['required' => '', 'max_len[250]' => ''],
            ],
            [
                'field' => 'last_name',
                'label' => translate('cr_events_dashboard_modal_event_field_last_name_label_text', null, true),
                'rules' => ['required' => '', 'max_len[250]' => ''],
            ],
            [
                'field' => 'phone_code',
                'label' => translate('cr_events_dashboard_modal_event_field_phone_code_label_text', null, true),
                'rules' => [
                    'required' => '',
                    function ($attr, $phone_code_id, $fail) use ($phone_codes_service) {
						if (
                            empty($phone_code_id)
                            || !model('country')->has_country_code($phone_code_id)
                            || 0 === $phone_codes_service->findAllMatchingCountryCodes($phone_code_id)->count()
                        ) {
							$fail(sprintf('Field "%s" contains unknown value.', $attr));
						}
					}
                ],
            ],
            [
                'field' => 'phone',
                'label' => translate('cr_events_dashboard_modal_event_field_phone_label_text', null, true),
                'rules' => ['required' => '', 'viable_phone' => '', 'max_len[100]' => ''],
            ],
            [
                'field' => 'email',
                'label' => translate('cr_events_dashboard_modal_event_field_email_label_text', null, true),
                'rules' => ['required' => '', 'no_whitespaces' => '', 'valid_emails' => '', 'max_len[250]' => ''],
            ],
        ];

        $phone_code = null;
        if (isset($_POST['phone']) && isset($_POST['phone_code'])) {
            /** @var null|PhoneCountryCode $phone_code */
            $phone_code = $phone_codes_service->findAllMatchingCountryCodes((int) arrayGet($_POST, 'phone_code'))->first();
            $_POST['real_phone'] = $phone_code ? "{$phone_code->getName()} {$_POST['phone']}" : $_POST['phone'];
            $validator_rules[] = [
                'field' => 'real_phone',
                'label' => translate('cr_events_dashboard_modal_event_field_phone_label_text', null, true),
                'rules' => [
                    'required'     => '',
                    'max_len[110]' => '',
                    'valid_phone'  => '',
                ],
            ];
        }

        $this->validator->reset_postdata();
        $this->validator->clear_array_errors();
        $this->validator->validate_data = $_POST;
        $this->validator->set_rules($validator_rules);
        if (!$this->validator->validate()) {
            jsonResponse($this->validator->get_array_errors());
        }

        /** @var \Cr_events_Model $model */
        $model = model('cr_events');
        $email = cleanInput($_POST['email']);
        $event_id = (int) arrayGet($_POST, 'id_event');

        try {
            $event = model('cr_events')->find_event($event_id);

        } catch (NotFoundException $exception) {
            jsonResponse('Error: This event does not exist. Please refresh this page.');
        }

        if ($model->has_attendance_for_email($event_id, $email)) {
            jsonResponse('A user with this email address already attends this event');
        }

        $user = [
            'id_event'          => $event_id,
            'id_user'           => logged_in() ? privileged_user_id() : 0,
            'id_phone_code'     => $phone_code ? $phone_code->getId() : null,
            'attend_fname'      => cleanInput($_POST['first_name']),
            'attend_lname'      => cleanInput($_POST['last_name']),
            'attend_phone'      => cleanInput($_POST['phone']),
            'attend_phone_code' => $phone_code ? $phone_code->getName() : null,
            'attend_email'      => $email,
        ];

        if (!($attendanceId = $this->cr_events->attend_event($user))) {
            jsonResponse('Error while inserting record');
        }

        try {
            /** @var MailerInterface $mailer */
            $mailer = $this->getContainer()->get(MailerInterface::class);
            $mailer->send(
                (new ConfirmEventAttend($user['attend_fname'] . ' ' . $user['attend_lname'], $event, $attendanceId, $user['attend_email']))
                    ->to(0 != $user['id_user'] ? new RefAddress((string) $user['id_user'], new Address($email)) : new Address($email) )
                    ->subjectContext([
                        '[eventName]' => $event['event_name'],
                    ])
            );

        } catch (\Throwable $th) {
            jsonResponse(translate('email_has_not_been_sent'));
        }

        $model->update_event($event_id, ['event_count_users' => $event['event_count_users'] + 1]);

        jsonResponse('Your request has been successfully sent. Please, verify your email for further instructions', 'success');
    }

    public function ajax_attend_logged_operation()
    {
        checkIsAjax();
        checkIsLoggedAjax();
        checkPermisionAjax('cr_attend_cr_event,attend_cr_event');

        /** @var \Cr_events_Model $cr_events */
        $cr_events = model('cr_events');
        /** @var \User_Model $users */
        $users = model('user');

        // Get event info
        try {
            $event_id = (int) arrayGet($_POST, 'id_event');
            $event = $cr_events->find_event($event_id);

        } catch (NotFoundException $exception) {
            jsonResponse('Error: This event does not exist. Please refresh this page.');
        }

        // Get user info
        $user_id = (int) privileged_user_id();
        $user = $users->getUser($user_id);
        if (empty($event)) {
            jsonResponse('This user is not found. Please refresh this page.');
        }

        if ('CR Affiliate' === $user['gr_type']) {
            // Registes Ambassador
            if ($cr_events->is_user_assigned($user_id, $event_id)) {
                jsonResponse('You are already assigned to this event');
            }

            $statistic = ['event_count_ambassadors' => (int) $event['event_count_ambassadors'] + 1];
            $is_registered = $cr_events->assign_users([$user_id], $event_id, false);
        } else {
            // Register simple user
            if ($cr_events->has_attendance_for_email($event_id, $user['email'])) {
                jsonResponse('A user with this email address already attends this event');
            }

            $statistic = ['event_count_users' => (int) $event['event_count_users'] + 1];
            $is_registered = $cr_events->attend_event([
                'id_user'           => $user_id,
                'id_event'          => $event_id,
                'attend_fname'      => $user['fname'],
                'attend_lname'      => $user['lname'],
                'attend_phone_code' => $user['phone_code'],
                'attend_phone'      => $user['phone'],
                'attend_email'      => $user['email'],
                'attend_status'     => 'confirmed',
            ]);
        }

        if (!$is_registered) {
            jsonResponse('Failed to register for the event. Please try again later');
        }

        // Update statistic
        $cr_events->update_event($event_id, $statistic);

        jsonResponse('You have been successfully registered for the event', 'success');
    }

    public function ajax_create_event()
    {
        checkIsAjax();
        checkIsLoggedAjax();
        checkPermisionAjax('cr_events_administration,manage_cr_personal_events');

        // Validate request
        $validator_rules = [
            [
                'field' => 'name',
                'label' => translate('cr_events_dasboard_modal_event_field_name_label_text', null, true),
                'rules' => ['required' => '', 'max_len[240]' => ''],
            ],
            [
                'field' => 'type',
                'label' => translate('cr_events_dasboard_modal_event_field_type_label_text', null, true),
                'rules' => ['required' => '', 'integer' => ''],
            ],
            [
                'field' => 'main_image',
                'label' => translate('cr_events_dasboard_modal_event_field_image_label_text', null, true),
                'rules' => ['required' => ''],
            ],
            [
                'field' => 'date_start',
                'label' => translate('cr_events_dasboard_modal_event_field_start_date_label_text', null, true),
                'rules' => ['required' => ''],
            ],
            [
                'field' => 'date_end',
                'label' => translate('cr_events_dasboard_modal_event_field_end_date_label_text', null, true),
                'rules' => ['required' => ''],
            ],
            [
                'field' => 'country',
                'label' => translate('cr_events_dasboard_modal_event_field_country_label_text', null, true),
                'rules' => ['required' => '', 'integer' => ''],
            ],
            [
                'field' => 'state',
                'label' => translate('cr_events_dasboard_modal_event_field_state_label_text', null, true),
                'rules' => ['required' => '', 'integer' => ''],
            ],
            [
                'field' => 'city',
                'label' => translate('cr_events_dasboard_modal_event_field_city_label_text', null, true),
                'rules' => ['required' => '', 'integer' => ''],
            ],
            [
                'field' => 'zip',
                'label' => translate('cr_events_dasboard_modal_event_field_postal_code_label_text', null, true),
                'rules' => ['required' => '', 'max_len[20]' => ''],
            ],
            [
                'field' => 'address',
                'label' => translate('cr_events_dasboard_modal_event_field_address_label_text', null, true),
                'rules' => ['required' => '', 'max_len[255]' => ''],
            ],
            [
                'field' => 'short_description',
                'label' => translate('cr_events_dasboard_modal_event_field_short_description_label_text', null, true),
                'rules' => ['required' => '', 'max_len[1000]' => ''],
            ],
            [
                'field' => 'description',
                'label' => translate('cr_events_dasboard_modal_event_field_full_description_label_text', null, true),
                'rules' => ['required' => '', 'html_max_len[60000]' => ''],
            ],
            [
                'field' => 'upload_folder',
                'label' => translate('general_dashboard_modal_upload_folder_placeholder', null, true),
                'rules' => ['required' => ''],
            ],
        ];

        $this->validator->set_rules($validator_rules);
        if (!$this->validator->validate()) {
            jsonResponse($this->validator->get_array_errors());
        }

        // Date validation
        $current_date = new \DateTimeImmutable();

        try {
            $start_date = new \DateTimeImmutable(cleanInput($_POST['date_start']));

        } catch (\Exception $exception) {
            jsonResponse('Invalid event start date provided');
        }

        try {
            $end_date = new \DateTimeImmutable(cleanInput($_POST['date_end']));

        } catch (\Exception $exception) {
            jsonResponse('Invalid event end date provided');
        }

        if ($start_date < $current_date) {
            jsonResponse('Event start date can not be in past.');
        }

        if ($start_date >= $end_date) {
            jsonResponse('Event\'s end date can not occur earlier when the event\'s start date.');
        }

        $user_id = (int) privileged_user_id();

        // Check folder with files
        $upload_folder = checkEncriptedFolder(cleanInput($_POST['upload_folder']));
        if (false === $upload_folder) {
            jsonResponse('File upload path is not correct.');
        }

        $image_path = cleanInput($_POST['main_image']);
        if (!startsWith($image_path, self::IMAGE_TEMP_PATH . "/{$user_id}/{$upload_folder}")) {
            jsonResponse('Image was uploaded incorrectly. Please try to upload it again');
        }

        // Loading models
        $this->load->model('Cr_events_Model', 'cr_events');

        // Init HTML sanitize library
        $this->load->library('Cleanhtml', 'clean');
        $this->clean->allowIframes();
        $this->clean->defaultTextarea(['style' => 'text-align']);
        $this->clean->addAdditionalTags('<img><h3><h4><h5><h6><p><span><strong><em><b><i><u><a><ol><ul><li><br>');

        // Create event
        $event = [
            'event_id_user'           => $user_id,
            'event_id_type'           => (int) cleanInput($_POST['type']),
            'event_id_country'        => (int) cleanInput($_POST['country']),
            'event_id_state'          => (int) cleanInput($_POST['state']),
            'event_id_city'           => (int) cleanInput($_POST['city']),
            'event_name'              => cleanInput($_POST['name']),
            'event_short_description' => strip_tags(cleanInput($_POST['short_description'])),
            'event_description'       => $this->clean->sanitize($_POST['description']),
            'event_address'           => cleanInput($_POST['address']),
            'event_zip'               => cleanInput($_POST['zip']),
            'event_date_start'        => $start_date->format('Y-m-d H:i:s'),
            'event_date_end'          => $end_date->format('Y-m-d H:i:s'),
        ];

        if (have_right('manage_cr_personal_events')) {
            $event['event_status'] = 'init';
            $event['event_count_ambassadors'] = 1;
        }

        if (have_right('cr_events_administration')) {
            $event['event_status'] = 'approved';
            $event['event_ep_manager'] = id_session();
        }

        if (!($event_id = $this->cr_events->add_event($event))) {
            jsonResponse('You can not add events now. Please try again later.');
        }

        $this->cr_events->update_event($event_id, ['event_url' => strForURL("{$event['event_name']} {$event_id}")]);
        if (have_right('manage_cr_personal_events')) {
            $this->cr_events->assign_users([$user_id], $event_id);
        }

        create_dir($basepath = "{$this->cr_events->event_images_path}/{$event_id}");
        $result = $this->upload->copy_images_new([
            'images'      => [$image_path],
            'destination' => $basepath,
            'resize'      => '800xR',
            'thumbs'      => '200xR',
            'rules'       => [
                'min_width'  => config('fileupload_cr_image_min_width', 800),
                'min_height' => config('fileupload_cr_image_min_height', 600),
            ],
        ]);

        if (!empty($result['errors'])) {
            jsonResponse(implode(', ', $result['errors']));
        }

        $this->cr_events->update_event($event_id, ['event_image' => arrayGet($result, '0.new_name', '')]);

        jsonResponse('The event was successfully added', 'success');
    }

    public function ajax_update_event()
    {
        checkIsAjax();
        checkIsLoggedAjax();
        checkPermisionAjax('cr_events_administration,manage_cr_personal_events');

        // Validate request
        $validator_rules = [
            [
                'field' => 'name',
                'label' => translate('cr_events_dasboard_modal_event_field_name_label_text', null, true),
                'rules' => ['required' => '', 'max_len[240]' => ''],
            ],
            [
                'field' => 'type',
                'label' => translate('cr_events_dasboard_modal_event_field_type_label_text', null, true),
                'rules' => ['required' => '', 'integer' => ''],
            ],
            [
                'field' => 'main_image',
                'label' => translate('cr_events_dasboard_modal_event_field_image_label_text', null, true),
                'rules' => ['required' => ''],
            ],
            [
                'field' => 'date_start',
                'label' => translate('cr_events_dasboard_modal_event_field_start_date_label_text', null, true),
                'rules' => ['required' => '', 'valid_date[m/d/Y]' => ''],
            ],
            [
                'field' => 'date_end',
                'label' => translate('cr_events_dasboard_modal_event_field_end_date_label_text', null, true),
                'rules' => ['required' => '', 'valid_date[m/d/Y]' => ''],
            ],
            [
                'field' => 'state',
                'label' => translate('cr_events_dasboard_modal_event_field_state_label_text', null, true),
                'rules' => ['required' => '', 'integer' => ''],
            ],
            [
                'field' => 'city',
                'label' => translate('cr_events_dasboard_modal_event_field_city_label_text', null, true),
                'rules' => ['required' => '', 'integer' => ''],
            ],
            [
                'field' => 'zip',
                'label' => translate('cr_events_dasboard_modal_event_field_postal_code_label_text', null, true),
                'rules' => ['required' => '', 'max_len[20]' => ''],
            ],
            [
                'field' => 'address',
                'label' => translate('cr_events_dasboard_modal_event_field_address_label_text', null, true),
                'rules' => ['required' => '', 'max_len[255]' => ''],
            ],
            [
                'field' => 'short_description',
                'label' => translate('cr_events_dasboard_modal_event_field_short_description_label_text', null, true),
                'rules' => ['required' => '', 'max_len[1000]' => ''],
            ],
            [
                'field' => 'description',
                'label' => translate('cr_events_dasboard_modal_event_field_full_description_label_text', null, true),
                'rules' => ['required' => '', 'html_max_len[60000]' => ''],
            ],
            [
                'field' => 'upload_folder',
                'label' => translate('general_dashboard_modal_upload_folder_placeholder', null, true),
                'rules' => ['required' => ''],
            ],
        ];

        $this->validator->set_rules($validator_rules);
        if (!$this->validator->validate()) {
            jsonResponse($this->validator->get_array_errors());
        }

        // Date validation
        $current_date = new \DateTimeImmutable();

        try {
            $start_date = new \DateTimeImmutable(cleanInput($_POST['date_start']));

        } catch (\Exception $exception) {
            jsonResponse('Invalid event start date provided');
        }

        try {
            $end_date = new \DateTimeImmutable(cleanInput($_POST['date_end']));

        } catch (\Exception $exception) {
            jsonResponse('Invalid event end date provided');
        }

        if ($start_date < $current_date) {
            jsonResponse('Event start date can not be in past.');
        }

        if ($start_date >= $end_date) {
            jsonResponse('Event\'s end date can not occur earlier when the event\'s start date.');
        }

        $user_id = (int) privileged_user_id();
        $event_id = (int) $_POST['id_event'];

        // Check folder with files
        $upload_folder = checkEncriptedFolder(cleanInput($_POST['upload_folder']));
        if (false === $upload_folder) {
            jsonResponse('File upload path is not correct.');
        }

        // Loading models
        $this->load->model('Cr_events_Model', 'cr_events');

        try {
            if (have_right('cr_events_administration')) {
                $event = $this->cr_events->find_event($event_id);
            } else {
                $event = $this->cr_events->find_event($event_id, $user_id);
            }

        } catch (NotFoundException $exception) {
            jsonResponse('Error: This event does not exist. Please refresh this page.');
        } catch (OwnershipException $exception) {
            jsonResponse(' This event does not belongs to you.');
        }

        // Resolve event image
        $image_path = cleanInput($_POST['main_image']);
        $event_image = $event['event_image'];
        $event_image_path = "{$this->cr_events->event_images_path}/{$event_id}/{$event_image}";
        if ($image_path !== $event_image_path) {
            if (
                empty($image_path) ||
                !startsWith($image_path, self::IMAGE_TEMP_PATH . "/{$user_id}/{$upload_folder}")
            ) {
                jsonResponse('Image was uploaded incorrectly. Please try to upload it again');
            }

            create_dir($basepath = "{$this->cr_events->event_images_path}/{$event_id}");

            $result = $this->upload->copy_images_new([
                'images'      => [$image_path],
                'destination' => $basepath,
                'resize'      => '800xR',
                'thumbs'      => '200xR',
                'rules'       => [
                    'min_width'  => config('fileupload_cr_image_min_width', 800),
                    'min_height' => config('fileupload_cr_image_min_height', 600),
                ],
            ]);

            if (!empty($result['errors'])) {
                jsonResponse(implode(', ', $result['errors']));
            }

            $event_image = arrayGet($result, '0.new_name');
            if (null === $event_image) {
                jsonResponse('Failed to upload event image');
            }
        }

        // Init HTML sanitize library
        $this->load->library('Cleanhtml', 'clean');
        $this->clean->allowIframes();
        $this->clean->defaultTextarea(['style' => 'text-align']);
        $this->clean->addAdditionalTags('<img><h3><h4><h5><h6><p><span><strong><em><b><i><u><a><ol><ul><li><br>');

        // Create event
        $event_name = cleanInput($_POST['name']);
        $update = [
            'event_id_type'           => (int) cleanInput($_POST['type']),
            'event_id_state'          => (int) cleanInput($_POST['state']),
            'event_id_city'           => (int) cleanInput($_POST['city']),
            'event_name'              => $event_name,
            'event_short_description' => strip_tags(cleanInput($_POST['short_description'])),
            'event_description'       => $this->clean->sanitize($_POST['description']),
            'event_image'             => $event_image,
            'event_url'               => strForURL("{$event_name} {$event['id_event']}"),
            'event_address'           => cleanInput($_POST['address']),
            'event_zip'               => cleanInput($_POST['zip']),
            'event_date_start'        => $start_date->format('Y-m-d H:i:s'),
            'event_date_end'          => $end_date->format('Y-m-d H:i:s'),
        ];

        if (!$this->cr_events->update_event($event_id, $update)) {
            jsonResponse('You cannot update events now. Please try again later.');
        }

        jsonResponse('The event was successfully updated', 'success');
    }

    private function my_events($events)
    {
        $output = [];

        foreach ($events as $event) {
            $event_id = (int) $event['id_event'];
            $is_my_event = (bool) is_my($event['event_id_user']);
            $can_control = (bool) have_right('manage_cr_personal_events');

            //region Event
            //Event info
            $event_title = cleanOutput($event['event_name']);
            $event_url = getSubDomainURL(arrayGet($event, 'country.alias', ''), "event/{$event['event_url']}");
            $event_image_name = $event['event_image'];
            $event_image_url = __IMG_URL . getImage(
                "{$this->cr_events->event_images_path}/{$event_id}/thumb_200xR_{$event_image_name}",
                'public/img/no_image/no-image-166x138.png'
            );
            // Type info
            $event_type_name = cleanOutput(arrayGet($event, 'type.name', ''));
            // Status info
            $event_status_name = $can_control ? translate("cr_events_dashboard_dt_event_status_{$event['event_status']}_label_text", null, true) : '';
            // Users info
            $event_ambassadors_label = translate('cr_events_dashboard_dt_event_ambassadors_amount_label_text', [
                '{amount}' => arrayGet($event, 'event_count_ambassadors', 0),
            ], true);

            $event_users_label = translate('cr_events_dashboard_dt_event_users_amount_label_text', [
                '{amount}' => arrayGet($event, 'event_count_users', 0),
            ], true);

            $event_details = "
                <div class=\"flex-card\">
                    <div class=\"flex-card__fixed main-data-table__item-img image-card\">
                        <span class=\"link\">
                            <img class=\"image\" src=\"{$event_image_url}\" alt=\"{$event_title}\"/>
                        </span>
                    </div>
                    <div class=\"flex-card__float\">
                        <div class=\"main-data-table__item-ttl\">
                            <a href=\"{$event_url}\"
                                class=\"display-ib link-black txt-medium\"
                                title=\"{$event_title}\"
                                target=\"_blank\">
                                {$event_title}
                            </a>
                        </div>
                        <div class=\"main-data-table__item-ttl mw-335\">
                            <div class=\"txt-gray\" title=\"{$event_type_name}\">
                                {$event_type_name}
                            </div>
                        </div>
                        <div class=\"main-data-table__item-ttl mw-335\">
                            <div class=\"txt-gray\" title=\"{$event_status_name}\">
                                {$event_status_name}
                            </div>
                        </div>
                        <div class=\"main-data-table__item-ttl mw-335\">
                            <div class=\"txt-gray\" title=\"{$event_ambassadors_label} / {$event_users_label}\">
                                {$event_ambassadors_label} / {$event_users_label}
                            </div>
                        </div>
                    </div>
                </div>
            ";
            //endregion Event

            //region Description
            $description = '&mdash;';
            if (!empty($event['event_short_description'])) {
                $description_text = cleanOutput(strLimit($event['event_short_description'], 200));
                $description = "
                    <div class=\"grid-text\">
                        <div class=\"grid-text__item\">
                            <div>
                                {$description_text}
                            </div>
                        </div>
                    </div>
                ";
            }
            //endregion Description

            //region Location
            $location = '&mdash;';
            $country_id = (int) arrayGet($event, 'country.id');
            if (null !== $country_id && 0 !== $country_id) {
                //region Country
                $country_name = cleanOutput(arrayGet($event, 'country.name'));
                $country_flag = getCountryFlag($country_name);
                $country_label = "
                    <div>
                        <img width=\"24\" height=\"24\" src=\"{$country_flag}\" title=\"{$country_name}\" alt=\"{$country_name}\"/> {$country_name}
                    </div>
                ";
                //endregion Country

                //region Region
                $region_parts = array_filter([
                    cleanOutput(arrayGet($event, 'state.name')),
                    cleanOutput(arrayGet($event, 'city.name')),
                ]);

                $region = implode(', ', $region_parts);
                $region_label = null;
                if (!empty($region)) {
                    $region_label = "
                        <div class=\"txt-gray\" title=\"{$region}\">{$region}</div>
                    ";
                }
                //endregion Region

                //region Address
                $address_label = null;
                $address_parts = array_filter([
                    cleanOutput($event['event_address']),
                    cleanOutput($event['event_zip']),
                ]);

                $address = implode(', ', $address_parts);
                if (!empty($address)) {
                    $address_label = "<div class=\"txt-gray\">{$address}</div>";
                }
                //endregion Address

                $location = "
                    <div class=\"tal\">
                        {$country_label}
                        {$region_label}
                        {$address_label}
                    </div>
                ";
            }
            //endregion Location

            //region Actions
            $actions = null;
            if ($can_control) {
                //region Edit button
                $edit_button = null;
                if ($is_my_event) {
                    $edit_button_url = __SITE_URL . "cr_events/popup_forms/edit_event/{$event_id}";
                    $edit_button_text = translate('general_button_edit_text', null, true);
                    $edit_button_modal_title = translate('cr_events_dashboard_dt_button_edit_event_modal_title', null, true);
                    $edit_button = "
                        <a class=\"dropdown-item fancybox.ajax fancyboxValidateModal\"
                            data-fancybox-href=\"{$edit_button_url}\"
                            data-title=\"{$edit_button_modal_title}\">
                            <i class=\"ep-icon ep-icon_pencil\"></i>
                            <span>{$edit_button_text}</span>
                        </a>
                    ";
                }
                //endregion Edit button

                //region Attendees button
                $attendees_button_url = __SITE_URL . "cr_events/popup_forms/attend_users_list/{$event_id}";
                $attendees_button_text = translate('cr_events_dashboard_dt_button_list_attendees_button_text', null, true);
                $attendees_button_modal_title = translate('cr_events_dashboard_dt_button_list_attendees_modal_title', null, true);
                $attendees_button = "
                    <a class=\"dropdown-item fancybox.ajax fancyboxValidateModal\"
                        data-fancybox-href=\"{$attendees_button_url}\"
                        data-title=\"{$attendees_button_modal_title}\">
                        <i class=\"ep-icon ep-icon_user-special\"></i>
                        <span>{$attendees_button_text}</span>
                    </a>
                ";
                //endregion Attendees button

                //region All button
                $all_button_text = translate('general_dt_info_all_text', null, true);
                $all_button = "
                    <a class=\"dropdown-item d-none d-md-block d-lg-block d-xl-none call-function\"
                        data-callback=\"dataTableAllInfo\"
                        target=\"_blank\">
                        <i class=\"ep-icon ep-icon_info-stroke\"></i>
                        <span>{$all_button_text}</span>
                    </a>
                ";
                //endregion All button

                $actions = "
                    <div class=\"dropdown\">
                        <a class=\"dropdown-toggle\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\">
                            <i class=\"ep-icon ep-icon_menu-circles\"></i>
                        </a>
                        <div class=\"dropdown-menu dropdown-menu-right\">
                            {$edit_button}
                            {$attendees_button}
                            {$all_button}
                        </div>
                    </div>
                ";
            }
            //endregion Actions

            $output[] = [
                'event'       => $event_details,
                'description' => $description,
                'location'    => $location,
                'starts_at'   => getDateFormatIfNotEmpty(arrayGet($event, 'event_date_start'), 'Y-m-d H:i:s', 'j M, Y'),
                'ends_at'     => getDateFormatIfNotEmpty(arrayGet($event, 'event_date_end'), 'Y-m-d H:i:s', 'j M, Y'),
                'actions'     => $actions,
            ];
        }

        return $output;
    }

    private function administration_events($events)
    {
        $output = [];

        foreach ($events as $event) {
            $location_details = implode(', ', array_filter([
                arrayGet($event, 'country.name'),
                arrayGet($event, 'state.name'),
                arrayGet($event, 'city.name'),
            ]));
            $location_details = "{$location_details}</br>{$event['event_address']}, {$event['event_zip']}";

            $remove_btn = '<a class="ep-icon txt-red ep-icon_remove confirm-dialog" data-callback="remove_event" data-id="' . $event['id_event'] . '" data-message="Are you sure you want to remove this event?" href="#" title="Remove event"></a>';
            $edit_btn = '<a class="ep-icon txt-blue ep-icon_pencil fancyboxValidateModalDT fancybox.ajax" data-table="crEvents" href="' . __SITE_URL . 'cr_events/popup_forms/edit_event/' . $event['id_event'] . '" title="Edit event" data-title="Edit event"></a>';
            $visible_btn = '<a class="ep-icon txt-blue ep-icon_invisible confirm-dialog" data-callback="toggle_visible" data-visible="' . $event['event_is_visible'] . '" data-id="' . $event['id_event'] . '" data-message="Are you sure you want to change the visible status of this event?" href="#" title="Set event ' . ($event['event_is_visible'] ? 'invisible' : 'visible') . '"></a>';
            $add_ambassadors_btn = '<a class="ep-icon txt-blue ep-icon_user-plus fancyboxValidateModalDT fancybox.ajax" data-submit-callback="on_users_selected" data-table="crEvents" href="' . __SITE_URL . 'cr_users/popup_forms/assign_users?type=event&id_item=' . $event['id_event'] . '" title="Assign ambassadors" data-title="Assign ambassadors"></a>';
            $users_list_btn = '<a class="ep-icon txt-blue ep-icon_user-special fancybox fancybox.ajax" href="' . __SITE_URL . 'cr_events/popup_forms/attended_users/' . $event['id_event'] . '" title="Users list" data-title="Users list"></a>';

            if ('init' === $event['event_status']) {
                $approve_btn = '<a class="ep-icon txt-blue ep-icon_ok confirm-dialog" data-callback="approve_event" data-id="' . $event['id_event'] . '" data-message="Are you sure you want to approve this event?" href="#" title="Approve event"></a>';
            } else {
                $approve_btn = '';
            }

            $output[] = [
                'dt_id'                => $event['id_event'],
                'dt_name'              => $event['event_name'],
                'dt_location'          => $location_details,
                'dt_date_start'        => formatDate($event['event_date_start']),
                'dt_date_end'          => formatDate($event['event_date_end']),
                'dt_count_ambassadors' => '<span>' . $event['event_count_ambassadors'] . '</span>' . '&nbsp;&nbsp;&nbsp;' . $add_ambassadors_btn,
                'dt_count_users'       => '<span>' . $event['event_count_users'] . '</span>' . '&nbsp;&nbsp;&nbsp;' . $users_list_btn,
                'dt_status'            => ucfirst($event['event_status']),
                'dt_visible'           => 1 == $event['event_is_visible'] ? 'Visible' : 'Invisible',
                'dt_actions'           => $edit_btn . $approve_btn . $visible_btn . $remove_btn,
            ];
        }

        return $output;
    }
}
