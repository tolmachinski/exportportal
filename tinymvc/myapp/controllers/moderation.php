<?php

use App\Email\AbuseAlert;
use App\Email\BlockResources;
use App\Email\UnblockResources;
use Symfony\Component\Mime\Address;
use const App\Moderation\Types\TYPE_B2B;
use const App\Moderation\Types\TYPE_ITEM;
use const App\Moderation\Types\TYPE_COMPANY;
use const App\Moderation\Types\TYPE_B2B_NAME;
use Symfony\Component\Mailer\MailerInterface;
use const App\Moderation\Types\TYPE_ITEM_NAME;
use const App\Moderation\Types\TYPE_COMPANY_NAME;
use Symfony\Component\Messenger\MessageBusInterface;
use const App\Moderation\Types\TYPE_B2B_NAME_CAPITALIZED;
use const App\Moderation\Types\TYPE_ITEM_NAME_CAPITALIZED;
use const App\Moderation\Types\TYPE_COMPANY_NAME_CAPITALIZED;
use Symfony\Component\DependencyInjection\ContainerInterface;
use App\Messenger\Message\Event\Product\ProductWasBlockedEvent;
use App\Messenger\Message\Event\Product\ProductWasModeratedEvent;
use App\Messenger\Message\Event\Product\ProductWasUnblockedEvent;
use ExportPortal\Bridge\Symfony\Component\Messenger\MessengerInterface;

/**
 * Pages application controller.
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 *
 * @property \Blocking_Model            $blocking
 * @property \Branch_Model              $branches
 * @property \Complains_Model           $complaints
 * @property \Moderation_Model          $moderation
 * @property \Notify_Model              $notify
 * @property \TinyMVC_Load              $load
 * @property \TinyMVC_View              $view
 * @property \TinyMVC_Library_URI       $uri
 * @property \TinyMVC_Library_Session   $session
 * @property \TinyMVC_Library_Cookies   $cookies
 * @property \TinyMVC_Library_Upload    $upload
 * @property \TinyMVC_Library_validator $validator
 * @property \Translations_Model        $translations
 *
 * @author Anton Zencenco
 */
class Moderation_Controller extends TinyMVC_Controller
{
    private MessageBusInterface $eventBus;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $messenger = $container->get(MessengerInterface::class);
        $this->eventBus = $messenger->bus('event.bus');
    }

    private $types = array(
        TYPE_B2B     => array(
            'page_title'        => 'B2B requests',
            'title'             => TYPE_B2B_NAME,
            'nameCapitalized'   => TYPE_B2B_NAME_CAPITALIZED,
        ),
        TYPE_ITEM    => array(
            'page_title'        => 'Items',
            'title'             => TYPE_ITEM_NAME,
            'nameCapitalized'   => TYPE_ITEM_NAME_CAPITALIZED,
        ),
        TYPE_COMPANY => array(
            'page_title'        => 'Companies',
            'title'             => TYPE_COMPANY_NAME,
            'nameCapitalized'   => TYPE_COMPANY_NAME_CAPITALIZED,
        ),
    );

    public function administration()
    {
        checkAdmin('moderate_content');

        $type = uri()->segment(3);
        if (!isset($this->types[$type])) {
            show_404();
        }

        /** @var Moderation_Model $moderationModel */
        $moderationModel = model(Moderation_Model::class);

        views(
            [
                'admin/header_view',
                'admin/moderation/index_view',
                'admin/footer_view',
            ],
            [
                'accessibility' => $moderationModel->get_accessibility($type),
                'title'         => "Moderation - {$this->types[$type]['title']}",
                'url'           => 'moderation/ajax_dt_moderation_list',
                'type'          => $type,
            ]
        );
    }

    public function ajax_dt_moderation_list()
    {
        checkAdminAjaxDT('moderate_content');

        $type = uri()->segment(3);
        if (!isset($this->types[$type])) {
            show_404();
        }

        $request = request()->request;

        $conditions = dtConditions(
            $request->all(),
            [
                ['as' => 'is_draft',                    'key' => 'localParameter',      'type' => fn () => TYPE_ITEM === $type ? 0 : null],
                ['as' => 'blocked',                     'key' => 'blocked',             'type' => 'int'],
                ['as' => 'keywords',                    'key' => 'keywords',            'type' => 'cleanInput'],
                ['as' => 'updated_company_from',        'key' => 'updated_from',        'type' => fn ($updatedFrom) => TYPE_COMPANY === $type && validateDate($updatedFrom, 'm/d/Y') ? getDateFormat($updatedFrom, 'm/d/Y', 'Y:m:d 00:00:00') : null],
                ['as' => 'updated_from',                'key' => 'updated_from',        'type' => fn ($updatedFrom) => TYPE_COMPANY !== $type && validateDate($updatedFrom, 'm/d/Y') ? getDateFormat($updatedFrom, 'm/d/Y', 'Y:m:d 00:00:00') : null],
                ['as' => 'updated_company_to',          'key' => 'updated_to',          'type' => fn ($updatedTo) => TYPE_COMPANY === $type && validateDate($updatedTo, 'm/d/Y') ? getDateFormat($updatedTo, 'm/d/Y', 'Y:m:d 23:59:59') : null],
                ['as' => 'updated_to',                  'key' => 'updated_to',          'type' => fn ($updatedTo) => TYPE_COMPANY !== $type && validateDate($updatedTo, 'm/d/Y') ? getDateFormat($updatedTo, 'm/d/Y', 'Y:m:d 23:59:59') : null],
                ['as' => 'registered_company_from',     'key' => 'created_from',        'type' => fn ($createdFrom) => TYPE_COMPANY === $type && validateDate($createdFrom, 'm/d/Y') ? getDateFormat($createdFrom, 'm/d/Y', 'Y:m:d 00:00:00') : null],
                ['as' => 'created_from',                'key' => 'created_from',        'type' => fn ($createdFrom) => TYPE_COMPANY !== $type && validateDate($createdFrom, 'm/d/Y') ? getDateFormat($createdFrom, 'm/d/Y', 'Y:m:d 00:00:00') : null],
                ['as' => 'registered_company_to',       'key' => 'created_to',          'type' => fn ($createdTo) => TYPE_COMPANY === $type && validateDate($createdTo, 'm/d/Y') ? getDateFormat($createdTo, 'm/d/Y', 'Y:m:d 23:59:59') : null],
                ['as' => 'created_to',                  'key' => 'created_to',          'type' => fn ($createdTo) => TYPE_COMPANY !== $type && validateDate($createdTo, 'm/d/Y') ? getDateFormat($createdTo, 'm/d/Y', 'Y:m:d 23:59:59') : null],
                ['as' => 'activated_from',              'key' => 'activated_from',      'type' => fn ($activatedFrom) => validateDate($activatedFrom, 'm/d/Y') ? getDateFormat($activatedFrom, 'm/d/Y', 'Y:m:d') : null],
                ['as' => 'activated_to',                'key' => 'activated_to',        'type' => fn ($activatedTo) => validateDate($activatedTo, 'm/d/Y') ? getDateFormat($activatedTo, 'm/d/Y', 'Y:m:d') : null],
                ['as' => 'status',                      'key' => 'status',              'type' => fn ($userStatus) => in_array($userStatus, ['new', 'pending', 'active', 'restricted', 'blocked', 'deleted']) ? $userStatus : null],
            ]
        );

        $order = array_column(dtOrdering(
            $request->all(),
            [
                'acctivation_account_date'   => 'users.`activation_account_date`',
                'created_at'                => '`created_at`',
                'updated_at'                => '`updated_at`',
            ]
        ), 'direction', 'column');

        $params = [
            'conditions' => $conditions,
            'limit' => $request->getInt('iDisplayLength') ?: null,
            'skip'  => $request->getInt('iDisplayStart') ?: null,
            'with' => [
                'author' => true,
            ],
            'order' => $order,
        ];

        /** @var Moderation_Model $moderationModel */
        $moderationModel = model(Moderation_Model::class);

        $entries = $moderationModel->find_not_moderated($type, $params);
        $amount = $moderationModel->count_not_moderated($type, $params);
        $accessibility = $moderationModel->get_accessibility($type, $params);

        $output = array(
            'sEcho'                => (int) $_POST['sEcho'],
            'iTotalRecords'        => $amount,
            'iTotalDisplayRecords' => $amount,
            'aoAccessibility'      => $accessibility,
            'aaData'               => $this->make_datagrid_output($entries, $type),
        );

        jsonResponse(null, 'success', $output);
    }

    public function popup_modals()
    {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        if (!logged_in()) {
            messageInModal(translate('systmess_error_should_be_logged_in'));
        }

        checkAdminAjaxModal('moderate_content');

        $action = (string) cleanInput($this->uri->segment(3));
        $type = (string) cleanInput($this->uri->segment(4));
        $resource_id = $this->uri->segment(5) ? (int) cleanInput($this->uri->segment(5)) : null;
        if (!isset($this->types[$type])) {
            show_404();
        }

        try {
            switch ($action) {
                case 'log':
                    return $this->show_log($resource_id, $type);
                case 'images':
                    return $this->images($resource_id, $type);
                case 'alert':
                    return $this->open_alert_form($resource_id, $type);
                case 'block':
                    return $this->open_blocking_form($resource_id, $type);
                case 'locks':
                    return $this->show_lock_history($resource_id, $type);
                default:
                    show_404();

                    break;
            }
        } catch (\Exception $exception) {
            $errorCode = $exception->getCode();
            if (400 === $errorCode) {
                messageInModal($exception->getMessage(), 'warning');
            }
            if (404 === $errorCode || 500 === $errorCode) {
                messageInModal($exception->getMessage());
            }

            messageInModal('Failed to process request due to server error');
        }
    }

    public function ajax_operations()
    {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        if (!logged_in()) {
            jsonResponse(translate('systmess_error_should_be_logged_in'));
        }

        checkAdminAjax('moderate_content');

        $action = (string) cleanInput($this->uri->segment(3));
        $type = (string) cleanInput($this->uri->segment(4));
        $resource_id = $this->uri->segment(5) ? (int) cleanInput($this->uri->segment(5)) : null;
        if (!isset($this->types[$type])) {
            show_404();
        }

        try {
            switch ($action) {
                case 'moderate':
                    return $this->moderate_resource($resource_id, $type);
                case 'alert':
                    return $this->alert_author($resource_id, $type);
                case 'block':
                    return $this->block_resource($resource_id, $type);
                case 'unblock':
                    return $this->unblock_resource($resource_id, $type);
                case 'download_original_image':
                    return $this->download_image($resource_id, $type);
                default:
                    show_404();

                    break;
            }
        } catch (\Exception $exception) {
            $errorCode = $exception->getCode();
            if (400 === $errorCode) {
                jsonResponse($exception->getMessage(), 'warning');
            }
            if (404 === $errorCode || 500 === $errorCode) {
                jsonResponse($exception->getMessage());
            }

            jsonResponse('Failed to process request due to server error');
        }
    }

    private function make_datagrid_output(array $resources, $type)
    {
        if (empty($resources)) {
            return [];
        }

        $output = [];
        foreach ($resources as $resource) {
            $resource_id = $resource['id'];
            $resource_title = cleanOutput($resource['title']);
            $resource_url = $this->get_resource_url($resource, $type);
            $resource_type_name = $this->types[$type]['title'];
            /** @var null|\DateTime $resource_updated_date */
            $resource_updated_date = $resource['updated_at'];
            /** @var null|\DateTime $resource_created_date */
            $resource_created_date = $resource['created_at'];
            /** @var bool $is_blocked */
            $is_blocked = 1 === $resource['moderation_is_blocked'];
            /** @var int $block_level */
            $block_level = $resource['moderation_is_blocked'];

            //region Title
            //region Block reason
            $block_reason = null;
            if ($is_blocked && !empty($resource['moderation_blocking'])) {
                $last_reason = end($resource['moderation_blocking']);
                $last_reason_text = $last_reason['reason'];
                $last_reason_message = $last_reason['message'];
                $block_reason = "
                    <div class=\"bdt-1-gray mt-5 pt-5\">
                        <i class=\"ep-icon ep-icon_warning txt-red\"></i> {$last_reason_text}
                        <div class=\"display-n\"></div>
                    </div>
                ";
            }
            //endregion Block reason

            $title_url = null !== $resource_url ? $resource_url : '#';
            $preTitle = '';

            if (TYPE_ITEM == $type) {
                $preTitle = 'Distributor: <span class="label label-default">No</span><br/>';
                if (1 == (int) $resource['is_distributor']) {
                    $preTitle = 'Distributor: <span class="label label-success">Yes</span><br/>';
                }
            }

            $title = "
                <div class=\"clearfix\">
                    <div class=\"tal\">
                    {$preTitle}
                        <a href=\"{$title_url}\" target=\"_blank\">
                            {$resource_title}
                        </a>
                    </div>
                    {$block_reason}
                </div>
            ";
            //endregion Title

            $handmade_button = null;
            if (TYPE_ITEM === $type) {
                if (have_right('mark_item_as_handmade')) {
                    $action = $resource['is_handmade'] ? 'unmark' : 'mark';
                    $handmade_button_message = cleanOutput("Do you really want to {$action} this {$resource_type_name} as handmade?");
                    $handmade_button_text = cleanOutput(ucfirst($action) . ' as Handmade');
                    $handmade_button_url = $this->get_resource_handmade_url($resource);
                    $handmade_button = "
                            <li>
                                <a class=\"dropdown-item confirm-dialog\"
                                    data-url=\"{$handmade_button_url}\"
                                    data-type=\"{$type}\"
                                    data-message=\"{$handmade_button_message}\"
                                    data-callback=\"moderateResource\"
                                    data-resource=\"{$resource_id}\">
                                    <i class=\"ep-icon ep-icon_handshake txt-green\"></i>
                                    <span>{$handmade_button_text}</span>
                                </a>
                            </li>
                    ";
                }
            }


            //region Actions
            //region Moderate button
            $moderate_button = null;
            if (!$is_blocked) {
                $moderate_button_message = cleanOutput("Do you really want to moderate this {$resource_type_name}?");
                $moderate_button_text = cleanOutput('Moderate');
                $moderate_button_url = $this->get_resource_moderate_url($resource, $type);
                $moderate_button = "
                    <li>
                        <a class=\"dropdown-item confirm-dialog\"
                            data-url=\"{$moderate_button_url}\"
                            data-type=\"{$type}\"
                            data-message=\"{$moderate_button_message}\"
                            data-callback=\"moderateResource\"
                            data-resource=\"{$resource_id}\">
                            <i class=\"ep-icon ep-icon_sheild-ok txt-green\"></i>
                            <span>{$moderate_button_text}</span>
                        </a>
                    </li>
                ";
            }
            //endregion Moderate button

            //region Block button
            $block_button = null;
            if (!$is_blocked && 2 !== $block_level) {
                $block_button_modal_title = cleanOutput("Block {$resource_type_name}");
                $block_button_text = cleanOutput('Block');
                $block_button_url = __SITE_URL . "moderation/popup_modals/block/{$type}/{$resource_id}";
                $block_button = "
                    <li>
                        <a class=\"dropdown-item fancybox.ajax fancyboxValidateModal\"
                            data-fancybox-href=\"{$block_button_url}\"
                            data-title=\"{$block_button_modal_title}\">
                            <i class=\"ep-icon ep-icon_locked txt-red\"></i>
                            <span>{$block_button_text}</span>
                        </a>
                    </li>
                ";
            }
            //endregion Block button

            //region Unblock button
            $unblock_button = null;
            if ($is_blocked && 2 !== $block_level) {
                $unblock_button_message = cleanOutput("Do you really want to unblock this {$resource_type_name}?");
                $unblock_button_text = cleanOutput('Unblock');
                $unblock_button_url = $this->get_resource_unblock_url($resource, $type);
                $unblock_button = "
                    <li>
                        <a class=\"dropdown-item confirm-dialog\"
                            data-url=\"{$unblock_button_url}\"
                            data-type=\"{$type}\"
                            data-message=\"{$unblock_button_message}\"
                            data-callback=\"unblockResource\"
                            data-resource=\"{$resource_id}\">
                            <i class=\"ep-icon ep-icon_unlocked txt-green\"></i>
                            <span>{$unblock_button_text}</span>
                        </a>
                    </li>
                ";
            }
            //endregion Unblock button

            //region Send Abuse Alert button
            $alert_button = null;
            if (!$is_blocked) {
                $alert_button_modal_title = cleanOutput('Abuse notice');
                $alert_button_text = cleanOutput('Send notice');
                $alert_button_url = __SITE_URL . "moderation/popup_modals/alert/{$type}/{$resource_id}";
                $alert_button = "
                    <li>
                        <a class=\"dropdown-item fancybox.ajax fancyboxValidateModal\"
                            data-fancybox-href=\"{$alert_button_url}\"
                            data-title=\"{$alert_button_modal_title}\">
                            <i class=\"ep-icon ep-icon_warning-stroke txt-orange\"></i>
                            <span>{$alert_button_text}</span>
                        </a>
                    </li>
                ";
            }

            //endregion Send Abuse Alert button

            //region Image item
            $imagesButton = null;
            if(TYPE_ITEM === $type)
            {
                $imagesButtonText = $imagesButtonTitle = 'Original Images';
                $imagesButtonLink = __SITE_URL . "moderation/popup_modals/images/{$type}/{$resource_id}";
                $imagesButton = "
                    <li>
                        <a class=\"dropdown-item fancybox.ajax fancyboxValidateModal\"
                            data-fancybox-href=\"{$imagesButtonLink}\"
                            data-title=\"{$imagesButtonTitle}\">
                            <i class=\"ep-icon ep-icon_photo-gallery txt-green\"></i>
                            <span>{$imagesButtonText}</span>
                        </a>
                    </li>
                ";
            }
            //endregion Send Abuse Alert button

            //region Log
            $log_button_modal_title = cleanOutput('Operations log');
            $log_button_text = cleanOutput('Open log');
            $log_button_url = __SITE_URL . "moderation/popup_modals/log/{$type}/{$resource_id}";
            $log_button = "
                <li>
                    <a class=\"dropdown-item fancybox.ajax fancyboxValidateModal\"
                        data-fancybox-href=\"{$log_button_url}\"
                        data-title=\"{$log_button_modal_title}\">
                        <i class=\"ep-icon ep-icon_text-more\"></i>
                        <span>{$log_button_text}</span>
                    </a>
                </li>
            ";
            //endregion Log

            //region Lock history
            $lock_history = null;
            if (!empty($resource['moderation_blocking'])) {
                $lock_history_modal_title = cleanOutput('Lock history');
                $lock_history_text = cleanOutput('Open lock history');
                $lock_history_url = __SITE_URL . "moderation/popup_modals/locks/{$type}/{$resource_id}";
                $lock_history = "
                    <li>
                        <a class=\"dropdown-item fancybox.ajax fancyboxValidateModal\"
                            data-fancybox-href=\"{$lock_history_url}\"
                            data-title=\"{$lock_history_modal_title}\">
                            <i class=\"ep-icon ep-icon_megaphone-stroke txt-red fs-18\"></i>
                            <span>{$lock_history_text}</span>
                        </a>
                    </li>
                ";
            }
            //endregion Lock history

            $explore_user = "
                <li>
                    <a class=\"confirm-dialog\" data-message=\"Are you sure you want to explore user?\" data-callback=\"explore_user\" data-user=\"{$resource['id_user']}\" href=\"#\" data-title=\"Login as user\">
                        <span class=\"ep-icon ep-icon_login\"></span> Explore user
                    </a>
                </li>
            ";

            //region Activity
            $activity_button = null;
            $activity_button_url = null;
            if(TYPE_COMPANY === $type) {
                $activity_button_url = __SITE_URL . "/admin?company={$resource_id}" . (null !== $resource_updated_date ? "&date={$resource_updated_date->format('Y-m-d')}" : '') ;
            } elseif (TYPE_ITEM === $type) {
                $activity_button_url = __SITE_URL . "/admin?item={$resource_id}" . (null !== $resource_updated_date ? "&date={$resource_updated_date->format('Y-m-d')}" : '') ;
            }
            if(null !== $activity_button_url) {
                $activity_button = "
                    <li>
                        <a href=\"{$activity_button_url}\" target=\"_blank\">
                            <i class=\"ep-icon ep-icon_items\"></i> Show activity
                        </a>
                    </li>
                ";
            }
            //endregion Activity

            $actions = "
                <div class=\"dropdown\">
                    <a class=\"ep-icon ep-icon_menu-circles dropdown-toggle\" type=\"button\" data-toggle=\"dropdown\"></a>
                    <ul class=\"dropdown-menu dropdown-menu-right\" aria-labelledby=\"dropdownMenu2\">
                        {$moderate_button}
                        {$handmade_button}
                        {$imagesButton}
                        {$alert_button}
                        {$unblock_button}
                        {$block_button}
                        {$activity_button}
                        {$log_button}
                        {$lock_history}
                        {$explore_user}
                    </ul>
                </div>
            ";
            //endregion Actions

            //region Availability
            $accessibility_text = $is_blocked ? 'Blocked' : (2 === $block_level ? 'Locked' : 'Accessible');
            $accessibility_title = $is_blocked ? 'The resource is blocked' : (2 === $block_level ? 'The resource is locked' : 'The resource is accessible');
            $accessibility_icon = $is_blocked ? 'ep-icon_locked' : (2 === $block_level ? 'ep-icon_locked' : 'ep-icon_unlocked');
            $accessibility_color = $is_blocked ? 'txt-red' : (2 === $block_level ? 'txt-orange' : 'txt-gray');
            $accessibility = "
                <div class=\"clearfix\">
                    <div class=\"tac\">
                        <i class=\"ep-icon {$accessibility_icon} {$accessibility_color} fs-22\"></i>
                    </div>
                    <div class=\"tac\">
                        {$accessibility_text}
                    </div>
                </div>
            ";
            //endregion Availability

            $output[] = [
                'acctivation_account_date'  => null === $resource['activation_account_date'] ? '&mdash;' : getDateFormat($resource['activation_account_date']),
                'accessibility'             => $accessibility,
                'updated_at'                => null !== $resource_updated_date ? $resource_updated_date->format('m/d/Y h:i A') : '-',
                'created_at'                => null !== $resource_created_date ? $resource_created_date->format('m/d/Y h:i A') : '-',
                'actions'                   => $actions,
                'name'                      => $title,
                'id'                        => $resource_id,
            ];
        }

        return $output;
    }

    private function moderate_resource($resource_id, $type)
    {
        $resource = $this->get_resource($resource_id, $type);
        if (1 === $resource['moderation_is_blocked']) {
            throw new \RuntimeException('The resource is blocked and cannot be moderated', 400);
        }

        if ($resource['moderation_is_approved']) {
            throw new \RuntimeException('The resource is already moderated', 400);
        }

        if (!$this->moderation->moderate($resource_id, $type, [
            'moderator' => [
                'id'       => $this->session->id,
                'email'    => $this->session->email,
                'fullname' => trim("{$this->session->fname} {$this->session->lname}"),
            ],
        ])) {
            throw new \RuntimeException('Failed to moderate resource due to server error. Please try again later', 500);
        }

        if (TYPE_ITEM === $type) {
            $this->eventBus->dispatch(new ProductWasModeratedEvent($resource['id']));
        }

        jsonResponse('The resource was successfully moderated', 'success');
    }

    private function block_resource($resource_id, $type)
    {
        $this->load->model('Notify_Model', 'notify');

        $validator_rules = array(
            array(
                'field' => 'resource',
                'label' => 'Resource ID',
                'rules' => array('required' => '', 'integer' => ''),
            ),
            array(
                'field' => 'author',
                'label' => 'Author ID',
                'rules' => array('required' => '', 'integer' => ''),
            ),
            array(
                'field' => 'abuse',
                'label' => 'Type of abuse',
                'rules' => array('required' => ''),
            ),
            array(
                'field' => 'content',
                'label' => 'Content',
                'rules' => array('required' => '', 'max_len[1000]' => ''),
            ),
        );
        if (isset($_POST['abuse']) && 'other' === $_POST['abuse']) {
            $validator_rules[] = array(
                'field' => 'abuse_other',
                'label' => 'Type of abuse',
                'rules' => array('required' => '', 'max_len[500]' => ''),
            );
        }

        $this->validator->set_rules($validator_rules);
        if (!$this->validator->validate()) {
            jsonResponse($this->validator->get_array_errors());
        }

        $resource = $this->get_resource($resource_id, $type);
        if (0 !== $resource['moderation_is_blocked']) {
            throw new \RuntimeException('The resource is already blocked', 400);
        }

        $resourceTypeForHeader = isset($this->types[$type]) ? $this->types[$type]['nameCapitalized'] : $type;
        $resource_type = isset($this->types[$type]) ? $this->types[$type]['title'] : $type;
        $content = cleanInput($_POST['content']);
        $abuse = $this->get_abuse_type($_POST['abuse'], true, $resource['author_lang_code']);

        // Block resource
        switch ($type) {
            case TYPE_COMPANY:
                $companyId = $resource['id'];
                $companiesIds = [$companyId];
                if (0 === $resource['parent']) {
                    $this->load->model('Branch_Model', 'branches');

                    $branches = array_column($this->branches->get_company_branches($companyId), 'id_company');
                    $companiesIds = array_merge($companiesIds, $branches);
                    if (!$this->moderation->block_list($companiesIds, $type, $abuse, $content)) {
                        throw new \RuntimeException('Failed to block resource due to server error');
                    }
                } elseif (!$this->moderation->block($resource_id, $type, $abuse, $content)) {
                    throw new \RuntimeException('Failed to block resource due to server error');
                }

                /** @var Elasticsearch_B2b_Model $elasticsearchB2bModel */
                $elasticsearchB2bModel = model(Elasticsearch_B2b_Model::class);

                foreach ($companiesIds as $companyId) {
                    $elasticsearchB2bModel->removeB2bRequestsByConditions(['companyId' => (int) $companyId]);
                }
                break;
            case TYPE_ITEM:
                if (!$this->moderation->block($resource_id, $type, $abuse, $content)) {
                    throw new \RuntimeException('Failed to block resource due to server error');
                }

                /** @var Items_Model $itemsModel */
                $itemsModel = model(Items_Model::class);
                $itemDetails = $itemsModel->get_item($resource_id, 'id, id_seller');

                /** @var Crm_Model $crmModel */
                $crmModel = model(Crm_Model::class);
                $crmModel->create_or_update_record($itemDetails['id_seller']);

                /**
                 * Run event
                 */
                $this->eventBus->dispatch(new ProductWasBlockedEvent($resource['id']));
                break;
            case TYPE_B2B:
                if (!$this->moderation->block($resource_id, $type, $abuse, $content)) {
                    throw new \RuntimeException('Failed to block resource due to server error');
                }

                /** @var Elasticsearch_B2b_Model $elasticsearchB2bModel */
                $elasticsearchB2bModel = model(Elasticsearch_B2b_Model::class);
                $elasticsearchB2bModel->removeB2bRequestById((int) $resource_id);

                if ('prod' === config('env.APP_ENV')) {
                    /** @var B2b_Model $b2bModel */
                    $b2bModel = model(B2b_Model::class);
                    $b2bRequest = $b2bModel->get_simple_b2b_request($resource_id);

                    if (!empty($b2bRequest['id_ticket'])) {
                        /** @var TinyMVC_Library_Zoho_Desk $zohoDeskLibrary */
                        $zohoDeskLibrary = library(TinyMVC_Library_Zoho_Desk::class);

                        try {
                            $zohoDeskLibrary->createTicketComment((int) $b2bRequest['id_ticket'], [
                                'contentType'   => 'plainText',
                                'isPublic'      => false,
                                'content'       => 'The B2B request was blocked by the EP Administrator on ' . (new DateTime())->format('j M, Y H:i'),
                            ]);
                        } catch (Exception $e) {

                        }
                    }
                }

                break;
            default:
                throw new \RuntimeException('Unknown type of the resource provided');
        }

        // Mail user
        try {
            /** @var MailerInterface $mailer */
            $mailer = $this->getContainer()->get(MailerInterface::class);
            $mailer->send(
                (new BlockResources($resource['author_fullname'], cleanOutput($abuse), $resourceTypeForHeader, $resource_type, $resource['title']))
                    ->from(config('moderation_email'))
                    ->to(new Address($resource['author_email']))
                    ->subjectContext([
                        '[typeHeader]'  => $resourceTypeForHeader,
                        '[type]'        => $resource_type,
                        '[title]'       => $resource['title'],
                    ])
            );
        } catch (\Throwable $th) {
            throw new \RuntimeException('The email was not sent due to server error.', 500);
        }

        jsonResponse('The resource was successfully blocked', 'success');
    }

    private function download_image($resourceId, $type)
    {
        if(TYPE_ITEM !== $type){
            jsonResponse(translate('systmess_error_invalid_data'));
        }

        $imageId = request()->request->get('image');

        /** @var Items_Model $itemsModel */
        $itemsModel = model(Items_Model::class);

        $image = $itemsModel->get_item_photo($imageId);

        if(empty($image)){
            jsonResponse(translate('systmess_error_invalid_data'));
        }

        $pathImage = getImgSrc('items.photos', 'original', array('{ID}' => $resourceId, '{FILE_NAME}' => 'orig_' . $image['photo_name']));

        if(!file_exists($pathImage)){
            jsonResponse('The original image of this item does not exist.');
        }

        jsonResponse(null, 'success', [
            'file' => $pathImage,
            'name' =>  $image['photo_name'],
        ]);
    }

    private function unblock_resource($resource_id, $type)
    {
        $resource = $this->get_resource($resource_id, $type);
        $resourceTypeForHeader = isset($this->types[$type]) ? $this->types[$type]['nameCapitalized'] : $type;
        $resource_type = isset($this->types[$type]) ? $this->types[$type]['title'] : $type;
        if (0 === $resource['moderation_is_blocked']) {
            throw new \RuntimeException('The resource is not blocked', 400);
        }

        // Unblock resource
        switch ($type) {
            case TYPE_COMPANY:
                $companyId = $resource['id'];
                $companiesIds = [$companyId];

                if (0 === $resource['parent']) {
                    $this->load->model('Branch_Model', 'branches');

                    $branches = array_column($this->branches->get_company_branches($companyId), 'id_company');
                    $companiesIds = array_merge($companiesIds, $branches);
                    if (!$this->moderation->unblock_list($companiesIds, $type)) {
                        throw new \RuntimeException('Failed to unblock resource due to server error');
                    }
                } elseif (!$this->moderation->unblock($resource_id, $type)) {
                    throw new \RuntimeException('Failed to unblock resource due to server error');
                }

                /** @var Elasticsearch_B2b_Model $elasticsearchB2bModel */
                $elasticsearchB2bModel = model(Elasticsearch_B2b_Model::class);

                foreach ($companiesIds as $companyId) {
                    $elasticsearchB2bModel->indexByConditions(['companyId' => (int) $companyId]);
                }
                break;
            case TYPE_ITEM:
                if (!$this->moderation->unblock($resource_id, $type)) {
                    throw new \RuntimeException('Failed to unblock resource due to server error');
                }

                /** @var Items_Model $itemsModel */
                $itemsModel = model(Items_Model::class);
                $itemDetail = $itemsModel->get_item($resource_id, 'id, id_seller');

                /** @var Crm_Model $crmModel */
                $crmModel = model(Crm_Model::class);
                $crmModel->create_or_update_record($itemDetail['id_seller']);

                /**
                 * Run event
                 */
                $this->eventBus->dispatch(new ProductWasUnblockedEvent($resource_id));
                break;
            case TYPE_B2B:
                if (!$this->moderation->unblock($resource_id, $type)) {
                    throw new \RuntimeException('Failed to unblock resource due to server error');
                }

                /** @var Elasticsearch_B2b_Model $elasticsearchB2bModel */
                $elasticsearchB2bModel = model(Elasticsearch_B2b_Model::class);
                $elasticsearchB2bModel->index((int) $resource_id);

                if ('prod' === config('env.APP_ENV')) {
                    /** @var B2b_Model $b2bModel */
                    $b2bModel = model(B2b_Model::class);
                    $b2bRequest = $b2bModel->get_simple_b2b_request($resource_id);

                    if (!empty($b2bRequest['id_ticket'])) {
                        /** @var TinyMVC_Library_Zoho_Desk $zohoDeskLibrary */
                        $zohoDeskLibrary = library(TinyMVC_Library_Zoho_Desk::class);

                        try {
                            $zohoDeskLibrary->createTicketComment((int) $b2bRequest['id_ticket'], [
                                'contentType'   => 'plainText',
                                'isPublic'      => false,
                                'content'       => 'The B2B request was unblocked by the EP Administrator on ' . (new DateTime())->format('j M, Y H:i'),
                            ]);
                        } catch (Exception $e) {

                        }
                    }
                }
                break;
            default:
                throw new \RuntimeException('Unknown type of the resource provided');
        }

        // Mail user
        try {
            /** @var MailerInterface $mailer */
            $mailer = $this->getContainer()->get(MailerInterface::class);
            $mailer->send(
                (new UnblockResources($resource['author_fullname'], $resourceTypeForHeader, $resource_type, $resource['title']))
                    ->from(config('moderation_email'))
                    ->to(new Address($resource['author_email']))
                    ->subjectContext([
                        '[typeHeader]'  => $resourceTypeForHeader,
                        '[type]'        => $resource_type,
                        '[title]'       => $resource['title'],
                    ])
            );
        } catch (\Throwable $th) {
            throw new \RuntimeException('The email was not sent due to server error.', 500);
        }

        jsonResponse('The resource was successfully unblocked', 'success');
    }

    private function alert_author($resource_id, $type)
    {
        $validator_rules = array(
            array(
                'field' => 'resource',
                'label' => 'Resource ID',
                'rules' => array('required' => '', 'integer' => ''),
            ),
            array(
                'field' => 'author',
                'label' => 'Author ID',
                'rules' => array('required' => '', 'integer' => ''),
            ),
            array(
                'field' => 'subject',
                'label' => 'Subject',
                'rules' => array('required' => '', 'max_len[500]' => ''),
            ),
            array(
                'field' => 'date',
                'label' => 'Date',
                'rules' => array('required' => '', 'valid_date[Y/m/d]' => ''),
            ),
            array(
                'field' => 'abuse',
                'label' => 'Type of abuse',
                'rules' => array('required' => ''),
            ),
            array(
                'field' => 'content',
                'label' => 'Content',
                'rules' => array('required' => '', 'max_len[5000]' => ''),
            ),
        );
        if (isset($_POST['abuse']) && 'other' === $_POST['abuse']) {
            $validator_rules[] = array(
                'field' => 'abuse_other',
                'label' => 'Type of abuse',
                'rules' => array('required' => '', 'max_len[500]' => ''),
            );
        }

        $this->validator->set_rules($validator_rules);
        if (!$this->validator->validate()) {
            jsonResponse($this->validator->get_array_errors());
        }

        $sanitizer = library('Cleanhtml');
        $sanitizer->resetAll();

        $subject = cleanInput($_POST['subject']);
        $content = $sanitizer->sanitize($_POST['content']);
        $resource = $this->get_resource($resource_id, $type);
        $resourceUrl = $this->get_resource_url($resource, $type);
        $abuse = $this->get_abuse_type($_POST['abuse'], true, $resource['author_lang_code']);
        $date = \DateTime::createFromFormat('Y/m/d', cleanInput($_POST['date']));
        if(false === $date)  {
            $interval = config('moderation_alert_interval_days', 5);
            $date = new \DateTime();
            $date->modify("+ {$interval}days");
        }

        // Log alert
        if (!$this->moderation->notice($resource_id, $type, $abuse, $subject, $content)) {
            throw new \RuntimeException('Failed to log resource activity due to server error', 500);
        }

        $resourceType = isset($this->types[$type]) ? $this->types[$type]['title'] : $type;

        // Mail user
        try {
            /** @var MailerInterface $mailer */
            $mailer = $this->getContainer()->get(MailerInterface::class);
            $mailer->send(
                (new AbuseAlert($resource['author_fullname'], $resourceType, $resource['title'], $resourceUrl, nl2br(cleanOutput($content)), cleanOutput($abuse), $date->format('M d, Y')))
                    ->subject($subject)
                    ->from(config('moderation_email'))
                    ->to(new Address($resource['author_email']))
            );
        } catch (\Throwable $th) {
            throw new \RuntimeException('The email was not sent due to server error.', 500);
        }

        jsonResponse('The abuse alert has been sent.', 'success');
    }

    private function show_log($resource_id, $type)
    {
        $resource = $this->get_resource($resource_id, $type);
        $normalizeDate = function ($item) {
            $item['date'] = !empty($item['date']) ? new \DateTime($item['date']) : null;

            return $item;
        };
        $notices = arrayByKey($resource['moderation_notices'], 'ref');
        $blocks = arrayByKey($resource['moderation_blocking'], 'ref');
        $activity = array_map($normalizeDate, $resource['moderation_activity']);
        foreach ($activity as &$log) {
            if (!isset($log['ref'])) {
                continue;
            }

            if (isset($notices[$log['ref']])) {
                $log['notice'] = $notices[$log['ref']];
            }
            if (isset($blocks[$log['ref']])) {
                $log['block'] = $blocks[$log['ref']];
            }
        }
        rsort($activity);

        $this->view->display('admin/moderation/log_view', array(
            'resource' => $resource,
            'activity' => $activity,
            'notices'  => $notices,
            'blocks'   => $blocks,
        ));
    }

    private function images($resourceId, $type)
    {
        if(TYPE_ITEM !== $type){
            messageInModal('Wrong type of resource');
        }

        /** @var Items_Model $itemsModel */
        $itemsModel = model(Items_Model::class);

        if(empty($resourceId)){
            messageInModal('No such resource');
        }

        $images = $itemsModel->get_items_photo($resourceId);

        $mainImage = array_shift($images);

        $images = array_filter(array_map(
            fn ($image) => file_exists(getImgSrc('items.photos', 'original', ['{ID}' => $resourceId, '{FILE_NAME}' => 'orig_' . $image['photo_name']])) ? $image : null,
            $images
        ));

        $this->view->display('admin/moderation/photos_view', [
            'images'        => $images,
            'main'          => $mainImage,
            'resource_id'   => $resourceId,
        ]);
    }

    private function show_lock_history($resource_id, $type)
    {
        $resource = $this->get_resource($resource_id, $type);
        $lock_history = $resource['moderation_blocking'];
        foreach ($lock_history as &$entry) {
            $entry['date'] = !empty($entry['date']) ? new \DateTime($entry['date']) : null;
        }
        rsort($lock_history);

        $this->view->display('admin/moderation/lock_history_view', array(
            'resource'     => $resource,
            'lock_history' => $lock_history,
        ));
    }

    private function open_alert_form($resource_id, $type)
    {
        $this->load->model('Complains_Model', 'complaints');

        $interval = (int) config('moderation_alert_interval_days', 5);
        $date = new \DateTime();
        $date->modify("+ {$interval}days");

        $this->view->display('admin/moderation/alert_form_view', array(
            'date'     => $date,
            'resource' => $this->get_resource($resource_id, $type),
            'themes'   => array_map(function($row) {
                $row['i18n'] = null !== ($decoded = json_decode($row['i18n'], true)) ? $decoded : array();

                return $row;
            } , $this->complaints->getComplainsThemes()),
            'action'   => __SITE_URL . "moderation/ajax_operations/alert/{$type}/{$resource_id}",
        ));
    }

    private function open_blocking_form($resource_id, $type)
    {
        $this->load->model('Complains_Model', 'complaints');

        $resource = $this->get_resource($resource_id, $type);
        if (0 !== $resource['moderation_is_blocked']) {
            throw new \RuntimeException('The resource is already blocked', 400);
        }

        $this->view->display('admin/moderation/block_form_view', array(
            'resource' => $resource,
            'themes'   => array_map(function($row) {
                $row['i18n'] = null !== ($decoded = json_decode($row['i18n'], true)) ? $decoded : array();

                return $row;
            } , $this->complaints->getComplainsThemes()),
            'action'   => __SITE_URL . "moderation/ajax_operations/block/{$type}/{$resource_id}",
        ));
    }

    private function get_resource_url(array $resource, $type)
    {
        switch ($type) {
            case TYPE_COMPANY:
                return getCompanyURL(array(
                    'id_company'   => $resource['id'],
                    'index_name'   => $resource['index_title'],
                    'name_company' => $resource['title'],
                    'type_company' => $resource['type'],
                ));
            case TYPE_B2B:
                $slug = strForURL(!empty($resource['title']) ? $resource['title'] : '');

                return __SITE_URL . "b2b/detail/{$slug}-{$resource['id']}";
            case TYPE_ITEM:
                $slug = strForURL(!empty($resource['title']) ? $resource['title'] : '');

                return __SITE_URL . "item/{$slug}-{$resource['id']}";
        }

        return null;
    }

    private function get_resource_moderate_url(array $resource, $type)
    {
        return __SITE_URL . "moderation/ajax_operations/moderate/{$type}/{$resource['id']}";
    }

    /**
     * Return link for mark item as Handmade
     */
    private function get_resource_handmade_url(array $resource): string
    {
        return __SITE_URL . "items/ajaxHandmadeItem/{$resource['id']}";
    }

    private function get_resource_unblock_url(array $resource, $type)
    {
        return __SITE_URL . "moderation/ajax_operations/unblock/{$type}/{$resource['id']}";
    }

    private function get_resource($resource_id, $type)
    {
        $this->load->model('Moderation_model', 'moderation');

        if (
            empty($resource_id) ||
            empty($resource = $this->moderation->get_resource($resource_id, $type, array(
                'columns' => array(
                    'idu as author',
                    'users.user_photo as author_avatar',
                    'users.user_type as author_type',
                    'user_groups.gr_name as author_group',
                    'users.email as author_email',
                    'users.fname as author_firstname',
                    'users.lname as author_lastname',
                    'users.user_initial_lang_code as author_lang_code',
                    'TRIM(CONCAT(users.fname, " ", users.lname)) as author_fullname',
                ),
                'with' => array(
                    'author' => true,
                    'group'  => true,
                ),
            )))
        ) {
            $resource_type = isset($this->types[$type]) ? $this->types[$type]['title'] : $type;
            $message = "The resource of type '{$resource_type}' with such ID is not found on this server";

            throw new \RuntimeException($message, 404);
        }

        $resource['author_avatar'] = getDisplayImageLink(array('{ID}' => $resource['author'], '{FILE_NAME}' => $resource['author_avatar']), 'users.main', array( 'thumb_size' => 0, 'no_image_group' => $resource['author_group'] ));

        return $resource;
    }

    private function get_abuse_type($abuse_id, $translate = false, $lang = __SITE_LANG)
    {
        $this->load->model('Complains_Model', 'complaints');

        if ('other' === $abuse_id) {
            return cleanInput($_POST['abuse_other']);
        }

        if (
            empty($abuse_id) ||
            empty($abuse = $this->complaints->getTheme($abuse_id))
        ) {
            throw new \RuntimeException('The provided type of abuse is not found on this server', 400);
        }

        $abuse['i18n'] = null !== ($decoded = json_decode($abuse['i18n'], true)) ? $decoded : array();

        $i18n = $abuse['i18n'];

        return $translate ? arrayGet(record_i18n($i18n, 'theme', $lang), 'value', $abuse['theme']) : $abuse['theme'];
    }
}
