<?php

use App\Common\Buttons\ChatButton;

use const App\Logger\Activity\ResourceTypes\BUYER_COMPANY;
use const App\Logger\Activity\ResourceTypes\COMPANY;
use const App\Logger\Activity\ResourceTypes\ITEM;
use const App\Logger\Activity\ResourceTypes\USER;
use const App\Logger\Activity\ResourceTypes\PERSONAL_DOCUMENT;
use const App\Logger\Activity\ResourceTypes\SHIPPER_COMPANY;

/**
 * Activity application controller.
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [15.12.2021]
 * Controller Refactoring
 *
 * @property \Activity_Logs_Model             $activity
 * @property \TinyMVC_Load                    $load
 * @property \TinyMVC_View                    $view
 * @property \TinyMVC_Library_URI             $uri
 * @property \TinyMVC_Library_Session         $session
 * @property \TinyMVC_Library_Cookies         $cookies
 * @property \TinyMVC_Library_Upload          $upload
 * @property \TinyMVC_Library_validator       $validator
 * @property \TinyMVC_Library_Activity_Logger $activity_logger
 *
 * @author Anton Zencenco
 */
class Activity_Controller extends TinyMVC_Controller
{
    private $log_level_label_color_map = [
        TinyMVC_Library_Activity_Logger::EMERGENCY => 'danger',
        TinyMVC_Library_Activity_Logger::ALERT     => 'danger',
        TinyMVC_Library_Activity_Logger::CRITICAL  => 'danger',
        TinyMVC_Library_Activity_Logger::ERROR     => 'danger',
        TinyMVC_Library_Activity_Logger::WARNING   => 'warning',
        TinyMVC_Library_Activity_Logger::NOTICE    => 'primary',
        TinyMVC_Library_Activity_Logger::INFO      => 'info',
        TinyMVC_Library_Activity_Logger::DEBUG     => 'default',
    ];

    public function index()
    {
		show_404();
	}

    /**
     * Show activity dashboard page
     *
     * @deprecated
     * @return void
     */
    public function administration()
    {
        show_404();

        checkPermision('admin_site');

        $this->view->assign(array(
            'title'           => 'Activity',
            'visibility'      => $this->activity->get_visibility(),
            'log_levels'      => TinyMVC_Library_Activity_Logger::getLevels(),
            'resource_types'  => array_column($this->activity->get_resource_types(array('order' => array('name' =>  'ASC'))), 'name', 'id_type'),
            'operation_types' => array_column($this->activity->get_operation_types(array('order' => array('name' =>  'ASC'))), 'name', 'id_type'),
        ));
        $this->view->display('admin/header_view');
        $this->view->display('admin/activity/index_view');
        $this->view->display('admin/footer_view');
    }

    public function ajax_operations()
    {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        if (!logged_in()) {
            jsonResponse(translate('systmess_error_should_be_logged_in'));
        }

        $this->load->model('Activity_Logs_Model', 'activity');

        $action = (string) cleanInput($this->uri->segment(3));
        switch ($action) {
            case 'view':
                checkAdminAjax('admin_site');

                $log_id = (int) $this->uri->segment(4);
                if (
                    empty($log_id) ||
                    empty($log = $this->activity->get_log($log_id))
                ) {
                    jsonResponse(translate('systmess_error_activity_log_not_found'));
                }

                if(!$this->activity->mark_viewed($log_id, id_session())) {
                    jsonResponse(translate('systmess_error_activity_log_mark_viewed'));
                }

                jsonResponse(translate('systmess_success_activity_log_mark_viewed'), 'success');
            break;
            case 'administration_dt':
                checkAdminAjaxDT('manage_users_activity');

                //region Configuration

                $order = array();
                $with = array('resources' => true, 'operations' => true, 'companies' => false);
                $columns = array(
                    'LOGS.id_log as id',
                    'LOGS.id_resource as resource',
                    'LOGS.id_initiator as initiator',
                    'LOGS.id_examinator as examinator',
                    'LOGS.examined_at as examination_date',
                    'RESOURCES.id_type as resource_type',
                    'COALESCE(RESOURCES.name, "N/A") as resource_type_name',
                    'LOGS.id_operation_type as operation_type',
                    'COALESCE(OPERATIONS.name, "N/A") as operation_type_name',
                    'LOGS.level',
                    'LOGS.level_name',
                    'LOGS.date as datetime',
                    'LOGS.message',
                    'LOGS.context',
                    'LOGS.is_viewed',
                );
                $conditions = array();
                $limit = !empty($_POST['iDisplayLength']) ? $_POST['iDisplayLength'] : null;
                $skip = !empty($_POST['iDisplayStart']) ? $_POST['iDisplayStart'] : null;
                if ($_POST['iSortingCols'] > 0) {
                    for ($i = 0; $i < $_POST['iSortingCols']; ++$i) {
                        switch ($_POST['mDataProp_' . intval($_POST['iSortCol_' . $i])]) {
                            case 'dt_id':
                                $order['id_log'] = $_POST['sSortDir_' . $i];

                            break;
                            case 'dt_datetime':
                                $order['date'] = $_POST['sSortDir_' . $i];

                            break;
                        }
                    }
                }

                $conditions['level'] = isset($_POST['level']) ? (int) $_POST['level'] : null;
                $conditions['viewed'] = isset($_POST['viewed']) ? (int) $_POST['viewed'] : null;;
                $conditions['resource'] = isset($_POST['resource']) ? (int) $_POST['resource'] : null;
                $conditions['initiator'] = isset($_POST['initiator']) ? (int) $_POST['initiator'] : null;
                $conditions['resource_type'] = isset($_POST['resource_type']) ? (int) $_POST['resource_type'] : null;
                $conditions['operation_type'] = isset($_POST['operation_type']) ? (int) $_POST['operation_type'] : null;
                $conditions['date_from'] = isset($_POST['date_from']) ? formatDate(cleanInput($_POST['date_from']) . " 00:00:00", 'Y-m-d H:i:s') : null;
                $conditions['date_to'] = isset($_POST['date_to']) ? formatDate(cleanInput($_POST['date_to']) . " 23:59:59", 'Y-m-d H:i:s') : null;
                $conditions['initiator_name'] = isset($_POST['initiator_name']) ? cleanInput($_POST['initiator_name']) : null;
                $conditions['initiator_email'] = isset($_POST['initiator_email']) ? cleanInput($_POST['initiator_email']) : null;
                $conditions['resource_name'] = isset($_POST['resource_name']) ? cleanInput($_POST['resource_name']) : null;
                if(isset($conditions['resource_type'])) {
                    $conditions['resource_name_field'] = $this->get_resource_context_key($conditions['resource_type']);
                }
                //endregion Configuration

                $params = compact('columns', 'with', 'conditions', 'order', 'limit', 'skip');
                $logs = $this->activity->get_logs($params);
                $logs_count = $this->activity->count_logs($params);
                $logs_visibility = $this->activity->get_visibility($params);
                $log_examinators_ids = array_flip(array_flip(array_filter(array_column($logs, 'examinator'), function($item) {
                    return null !== $item;
                })));
                $log_examinators = array();
                $output = array(
                    'sEcho'                => (int) $_POST['sEcho'],
                    'iTotalRecords'        => $logs_count,
                    'iTotalDisplayRecords' => $logs_count,
                    'aoVisibility'         => $logs_visibility,
                    'aaData'               => array(),
                );

                if (empty($logs)) {
                    jsonResponse(null, 'success', $output);
                }

                //region Get examinators

                if(!empty($log_examinators_ids)) {
                    $log_examinators = arrayByKey($this->activity->get_examinators(array(
                        'columns' => array(
                            "idu as id",
                            "email",
                            "TRIM(CONCAT(fname, ' ', lname)) as fullname"
                        ),
                        'conditions' => array(
                            'list' => $log_examinators_ids
                        )
                    )), 'id');
                }

                //endregion Get examinators

                //region Build output

                foreach ($logs as $log) {
                    $log_id = (int) $log['id'];
                    $log_level = (int) $log['level'];
                    $log_context = json_decode($log['context'], true);
                    $log_message = $log['message'];
                    $log_datetime = new \DateTime($log['datetime']);
                    $log_is_viewed = filter_var($log['is_viewed'], FILTER_VALIDATE_BOOLEAN);
                    $log_level_name = mb_strtoupper($log['level_name']);
                    $log_initiator_id = null !== $log['initiator'] ? (int) $log['initiator'] : null;
                    $log_examinator_id = null !== $log['examinator'] ? (int) $log['examinator'] : null;
                    $log_examinator = isset($log_examinators[$log_examinator_id]) ? $log_examinators[$log_examinator_id] : null;
                    $log_examination_date = null !== $log['examination_date'] ? new \DateTime($log['examination_date']) : null;
                    $log_resource = null !== $log['resource'] ? (int) $log['resource'] : null;
                    $log_resource_type = null !== $log['resource_type'] ? (int) $log['resource_type'] : null;
                    $log_resource_type_name = $log['resource_type_name'];
                    $log_operation_type = null !== $log['operation_type'] ? (int) $log['operation_type'] : null;
                    $log_operation_type_name = $log['operation_type_name'];
                    $log_preview_url = __SITE_URL . "/activity/popup_modals/preview/{$log_id}";
                    $log_resource_url = null;

                    //region Initiator

                    $log_initator = "";
                    if(null !== $log_initiator_id) {
                        $log_initiator_name = !empty($log_context['user']['name']) ? $log_context['user']['name'] : 'Anonymous user';
                        $log_initator_url = $this->get_initiator_url($log_context, $log_initiator_id, $log_initiator_name);
                        $user_link =
                            !empty($log_initator_url)
                            ?   "<a href=\"{$log_initator_url}\" target=\"_blank\" title=\"Visit personal page\">
                                    <i class=\"ep-icon ep-icon_user\"></i>
                                </a>"
                            : '';

                        //TODO: admin chat hidden
                        $btnChatUser = new ChatButton(['hide' => true, 'recipient' => $log_initiator_id, 'recipientStatus' => 'active'], ['classes' => 'btn-chat-now', 'text' => '']);
                        $btnChat = $btnChatUser->button();

                        $log_initiator_mail_url = __SITE_URL . "contact/popup_forms/email_user/{$log_initiator_id}";
                        $log_initator = "
                            <div class=\"tal\">
                                <a class=\"txt-green dt_filter\"
                                    title=\"Filter by initiator\"
                                    data-value=\"{$log_initiator_id}\"
                                    data-name=\"initiator\"
                                    data-title=\"Initiator\"
                                    data-value-text=\"{$log_initiator_name}\">
                                    <i class=\"ep-icon ep-icon_filter\"></i>
                                </a>
                                {$user_link}
                                {$btnChat}
                                <a href=\"{$log_initiator_mail_url}\"
                                    class=\" fancyboxValidateModal fancybox.ajax\"
                                    data-title=\"Email {$log_initiator_name}\">
                                    <i class=\"ep-icon ep-icon_envelope-send\"></i>
                                </a>
                            </div>
                            <div class=\"pull-left\"><span>{$log_initiator_name}<span></div>
                        ";
                    }

                    //endregion Initiator

                    //region Resource

                    $log_resource_label = "";
                    $log_resource_filter = "";
                    $log_resource_type_label = "";
                    $log_resource_type_filter = "";
                    $log_operation_type_label = "";
                    $log_operation_type_filter = "";
                    if(null !== $log_resource) {
                        $log_resource_url = $this->get_resource_url($log_context, $log_resource, $log_resource_type, $log_resource_type_name);
                        $log_resource_name = $this->get_resource_name($log_context, $log_resource_type, $log_resource_type_name);
                        $log_resource_link = '';
                        if (null !== $log_resource_url && !empty($log_resource_url)) {
                            $log_resource_link = "
                                <a href=\"{$log_resource_url}\" target=\"_blank\" title=\"Open resource\">
                                    <i class=\"ep-icon ep-icon_link\"></i>
                                </a>
                            ";
                        } else {
                            switch ($log_resource_type) {
                                case PERSONAL_DOCUMENT:
                                    $log_resource_link = "
                                        <a class=\"fancyboxValidateModalDT fancybox.ajax\"
                                            href=\"". __SITE_URL ."verification/popup_forms/user_verification_documents/{$log_context['target_user']['id']}\"
                                            data-title=\"Verification documents of {$log_context['target_user']['name']}\"
                                            title=\"Open resource\"
                                        >
                                            <i class=\"ep-icon ep-icon_link\"></i>
                                        </a>
                                    ";
                                    break;
                            }
                        }

                        $log_resource_label = "
                            <div class=\"clearfix\">
                                <div class=\"tal\">
                                    <a class=\"txt-green dt_filter\"
                                        title=\"Filter by resource\"
                                        data-value=\"{$log_resource}\"
                                        data-name=\"resource\"
                                        data-title=\"Resource\"
                                        data-value-text=\"{$log_resource_name}\">
                                        <i class=\"ep-icon ep-icon_filter\"></i>
                                    </a>
                                    {$log_resource_link}
                                    <span>{$log_resource_name}</span>
                                </div>
                            </div>
                        ";
                    }
                    if(null !== $log_resource_type) {
                        $log_resource_type_label = "
                            <div class=\"clearfix\">
                                <div class=\"tal\">
                                    <a class=\"txt-green dt_filter\"
                                        title=\"Filter by resource type\"
                                        data-value=\"{$log_resource_type}\"
                                        data-name=\"resource_type\"
                                        data-title=\"Resource type\"
                                        data-value-text=\"{$log_resource_type_name}\">
                                        <i class=\"ep-icon ep-icon_filter\"></i>
                                    </a>
                                    <span>{$log_resource_type_name}</span>
                                </div>
                            </div>
                        ";
                    }
                    if(null !== $log_operation_type) {
                        $log_operation_type_label = "
                            <div class=\"clearfix\">
                                <div class=\"tal\">
                                    <a class=\"txt-green dt_filter\"
                                        title=\"Filter by operation type\"
                                        data-value=\"{$log_operation_type}\"
                                        data-name=\"operation_type\"
                                        data-title=\"Operation type\"
                                        data-value-text=\"{$log_operation_type_name}\">
                                        <i class=\"ep-icon ep-icon_filter\"></i>
                                    </a>
                                    <span>{$log_operation_type_name}</span>
                                </div>
                            </div>
                        ";
                    }

                    $log_resource = implode("", array_filter(array(
                        $log_resource_label,
                        $log_resource_type_label,
                        $log_operation_type_label
                    )));

                    //endregion Resource

                    //region View status

                    $log_view_value = (int) $log_is_viewed;
                    $log_view_color = $log_is_viewed ? 'txt-default' : 'txt-red';
                    $log_view_title = 'Not viewed yet';
                    if($log_is_viewed) {
                        $log_view_title = "Viewed";
                        if(null !== $log_examinator) {
                            $log_view_title = "{$log_view_title} by {$log_examinator['fullname']}";
                        }
                        if(null !== $log_examination_date) {
                            $log_view_title = "{$log_view_title} at {$log_examination_date->format('m/d/Y h:i:s A')}";
                        }
                    }
                    $log_view_status = "<i class=\"ep-icon ep-icon_circle fs-18 {$log_view_color}\" title=\"{$log_view_title}\"></i>";

                    //endregion View status

                    //region Label

                    $log_label_color = isset($this->log_level_label_color_map[$log_level]) ? $this->log_level_label_color_map[$log_level] : 'default';
                    $log_level_label = "<span class=\"fs-12 label label-{$log_label_color}\">{$log_level_name}</span>";

                    //endregion Label

                    //region Actions

                    $log_actions = "
                        <a href=\"{$log_preview_url}\"
                            class=\"fancyboxValidateModalDT fancybox.ajax\"
                            title=\"View activity log\"
                            data-title=\"Activity log #{$log_id} - {$log_datetime->format('m/d/Y h:i:s A')}\">
                            <i class=\"ep-icon ep-icon_visible fs-20\"></i>
                        </a>
                    ";

                    //endregion Actions

                    $output['aaData'][] = array(
                        'dt_id'             => $log_id,
                        'dt_level'          => trim($log_level_label),
                        'dt_message'        => trim($log_message),
                        'dt_datetime'       => $log_datetime->format('m/d/Y h:i:s A'),
                        'dt_resource'       => trim($log_resource),
                        'dt_initiator'      => trim($log_initator),
                        'dt_viewed'         => trim($log_view_status),
                        'dt_actions'        => trim($log_actions),
                    );
                }

                //endregion Build output

                jsonResponse(null, 'success', $output);

                break;
            default:
                show_404();

                break;
        }
    }

    public function popup_modals()
    {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        if (!logged_in()) {
            messageInModal(translate('systmess_error_should_be_logged_in'));
        }

        $this->load->model('Activity_Logs_Model', 'activity');

        $action = (string) cleanInput($this->uri->segment(3));

        switch ($action) {
            case 'preview':
                checkAdminAjaxModal('admin_site');

                $log_id = (int) $this->uri->segment(4);
                if (
                    empty($log_id) ||
                    empty($log = $this->activity->get_log($log_id, array(
                        'columns' => array(
                            'LOGS.id_log as id',
                            'LOGS.id_resource as resource',
                            'RESOURCES.id_type as resource_type',
                            'COALESCE(RESOURCES.key, "") as resource_key',
                            'COALESCE(RESOURCES.name, "N/A") as resource_type_name',
                            'COALESCE(OPERATIONS.name, "N/A") as operation_type_name',
                            'COALESCE(OPERATIONS.key, "") as operation_key',
                            'LOGS.level',
                            'LOGS.level_name',
                            'LOGS.date as datetime',
                            'LOGS.message',
                            'LOGS.context',
                            'LOGS.is_viewed',
                        ),
                        'with'    => array('resources' => true, 'operations' => true)
                    )))
                ) {
                    messageInModal(translate('systmess_error_activity_log_not_found'));
                }

                $context = json_decode($log['context'], true);
                $changes = !empty($context['changes']) ? $context['changes']: array();
                $change_base = !empty($context['changes']['old']) ? $context['changes']['old'] : $context['changes']['current'];
                $changed_keys = !empty($change_base) ? $this->normalize_changes_keys(array_keys($change_base), $log['resource_type']) : array();

                $resource_key = trim($log['resource_key']);
                $operation_key = trim($log['operation_key']);
                $base_path = TMVC_BASEDIR . "myapp/views";
                $log_view_directory = "admin/activity/forms/partials";
                $log_view_path = "{$log_view_directory}/default_log_view";
                if(!empty($resource_key) && !empty($operation_key)) {
                    $path_prefix = $resource_key;
                    $path_prefix_full = trim("{$resource_key}/{$operation_key}", '/');
                    if(file_exists("{$base_path}/{$log_view_directory}/{$path_prefix_full}_log_view.php")) {
                        $log_view_path = "{$log_view_directory}/{$path_prefix_full}_log_view";
                    } else if(file_exists("{$base_path}/{$log_view_directory}/{$path_prefix}/default_log_view.php")){
                        $log_view_path = "{$log_view_directory}/{$path_prefix}/default_log_view";
                    }
                }

                $this->view->assign(array(
                    'level'       => mb_strtoupper($log['level_name']),
                    'label_color' => isset($this->log_level_label_color_map[$log['level']]) ? $this->log_level_label_color_map[$log['level']] : 'default',
                    'message'     => $log['message'],
                    'context'     => $context,
                    'changes'     => array(
                        'keys'    => $changed_keys,
                        'old'     => !empty($context['changes']['old']) ? $context['changes']['old'] : array(),
                        'current' => !empty($context['changes']['current']) ? $context['changes']['current'] : array(),
                    ),
                    'datetime'    => new \DateTime($log['datetime']),
                    'resource'    => array(
                        'id'   => $log['resource'],
                        'url'  => $this->get_resource_url($context, $log['resource'], $log['resource_type'], $log['resource_type_name']),
                        'name' => $this->get_resource_name($context, $log['resource_type'], null),
                        'type' => array(
                            'id'   => $log['resource_type'],
                            'name' => $log['resource_type_name'],
                        ),
                    ),
                    'operation'   => array(
                        'id'   => $log['operation_type'],
                        'name' => $log['operation_type_name']
                    ),
                    'is_viewed'   => filter_var($log['is_viewed'], FILTER_VALIDATE_BOOLEAN),
                    'view_path'   => $log_view_path,
                    'view_url'    => __SITE_URL . "activity/ajax_operations/view/{$log_id}",
                ));
                $this->view->display('admin/activity/forms/preview_form_view');

                break;

            default:
                show_404();

                break;
        }
    }

    private function get_resource_url(array $context, $resource, $type, $name)
    {
        switch ($type) {
            case COMPANY:
                if (!empty($context['company']['url'])) {
                    return $context['company']['url'];
                }
                $slug = strForURL(!empty($context['company']['name']) ? $context['company']['name'] : $name);

                return __SITE_URL . "seller/{$slug}-{$resource}";
            case SHIPPER_COMPANY:
                if (!empty($context['company']['url'])) {
                    return $context['company']['url'];
                }

                return getShipperURL(array(
                    'co_name' => !empty($context['company']['name']) ? $context['company']['name'] : $name,
                    'id'      => $resource,
                ));
            case ITEM:
                if (!empty($context['item']['url'])) {
                    return $context['item']['url'];
                }
                $slug = strForURL(!empty($context['item']['name']) ? $context['item']['name'] : $name);

                return __SITE_URL . "item/{$slug}-{$resource}";
            case USER:
                return $this->get_initiator_url(
                    $context,
                    privileged_user_id(),
                    !empty($context['user']['name']) ? $context['user']['name'] : 'Anonymous user'
                );
        }

        return null;
    }

    private function get_resource_name(array $context, $type, $name)
    {
        switch ($type) {
            case COMPANY:
            case BUYER_COMPANY:
            case SHIPPER_COMPANY:
                if (!empty($context['company']['name'])) {
                    return $context['company']['name'];
                }

                return $name;
            case ITEM:
                if (!empty($context['item']['name'])) {
                    return $context['item']['name'];
                }

                return $name;
            case USER:
                if (!empty($context['user']['name'])) {
                    return $context['user']['name'];
                }

                return $name;
            case PERSONAL_DOCUMENT:
                if (!empty($context['document']['title'])) {
                    return $context['document']['title'];
                }

                return $name;
            default:
                return $name;
        }

        return null;
    }

    private function get_resource_context_key($type)
    {
        $resource = $this->activity->get_resource_type($type);
        if(null !== $resource) {
            return $resource['key'];
        }

        return null;
    }

    private function get_initiator_url(array $context, $initiator, $name)
    {
        if(!empty($context['user']['profile']['relUrl']))  {
            return __SITE_URL . "{$context['user']['profile']['relUrl']}";
        }

        if(!empty($context['user']['profile']['url'])) {
            $url = $context['user']['profile']['url'];
            if(strpos($url, __SITE_URL) === 0) {
                return $url;
            }

            $url_parts = parse_url($url);
            $url_parts = array_filter(array(
                    isset($url_parts['path']) ? ltrim($url_parts['path'], '/') : null,
                    isset($url_parts['query']) ? "?{$url_parts['query']}" : null,
                    isset($url_parts['fragment']) ? "#{$url_parts['fragment']}" : null,
                )
            );

            return __SITE_URL . implode('', $url_parts);
        }

        return getUserLink(
            $name,
            $initiator,
            !empty($context['user']['group']['type']) ? $context['user']['group']['type'] : 'buyer'
        );
    }

    private function normalize_changes_keys(array $keys, $type)
    {
        $normalized = array();

        switch ($type) {
            default:
                foreach ($keys as $key) {
                    $normalized[$key] = ucfirst(str_replace('_', ' ', $key));
                }

                break;
        }

        return $normalized;
    }
}
