<?php
use GuzzleHttp\Client;
use Fnash\GraphQL\Query;
use App\Common\Transformers\Analytics\TargetsTransformer;
use App\Common\Transformers\Analytics\ListTransformer;
use GuzzleHttp\Exception\BadResponseException;

use function GuzzleHttp\Psr7\get_message_body_summary;

/**
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [15.12.2021]
 * Controller Refactoring
 */
class Analytics_Controller extends TinyMVC_Controller
{
    private $analytics_script_path = "public/plug/analytics-core/[[VERSION]]/dist/analytics.js[[POSTFIX]]";

    private function _load_main(){
        $this->load->model('Analytics_Model', 'analytics');
    }

    public function index()
    {
		show_404();
	}

    public function ga_pageviews(){
        checkPermision('manage_analytics');

        $this->load->model('Analytics_Model', 'analytics');

        $data['target_types'] = $this->analytics->target_types;
        $data['targets'] = arrayByKey($this->analytics->get_targets(array('target_type' => 'page', 'target_active_ga' => 1)), 'id_target');
        $this->view->assign($data);
        $this->view->display('admin/header_view');
        $this->view->display('admin/analytics/reports/ga_pageviews/index_view');
        $this->view->display('admin/footer_view');
    }

    public function ga_countries(){
        checkPermision('manage_analytics');

        $this->load->model('Analytics_Model', 'analytics');

        $data['countries'] = $this->analytics->get_google_countries();
        $this->view->assign($data);
        $this->view->display('admin/header_view');
        $this->view->display('admin/analytics/reports/ga_countries/index_view');
        $this->view->display('admin/footer_view');
    }

    public function ga_referrals(){
        checkPermision('manage_analytics');

        $this->load->model('Analytics_Model', 'analytics');

        $data['ga_referrals'] = $this->analytics->get_google_unique_referrals();
        $this->view->assign($data);
        $this->view->display('admin/header_view');
        $this->view->display('admin/analytics/reports/ga_referrals/index_view');
        $this->view->display('admin/footer_view');
    }

    public function pageviews(){
        checkPermision('manage_analytics');

        $this->load->model('Analytics_Model', 'analytics');

        $data['target_types'] = $this->analytics->target_types;
        $data['targets'] = arrayByKey($this->analytics->get_targets(array('target_type' => 'page', 'target_active_oa' => 1)), 'id_target');
        $this->view->assign($data);
        $this->view->display('admin/header_view');
        $this->view->display('admin/analytics/reports/pageviews/index_view');
        $this->view->display('admin/footer_view');
    }

    public function forms_filled(){
        checkPermision('manage_analytics');

        $this->load->model('Analytics_Model', 'analytics');

        $data['target_types'] = $this->analytics->target_types;
        $data['targets'] = arrayByKey($this->analytics->get_targets(array('target_type' => 'form', 'target_active_oa' => 1)), 'id_target');
        $this->view->assign($data);
        $this->view->display('admin/header_view');
        $this->view->display('admin/analytics/reports/forms_filled/index_view');
        $this->view->display('admin/footer_view');
    }

    public function targets(){
        checkPermision('manage_analytics_targets');

        $this->view->display('admin/header_view');
        $this->view->display('admin/analytics/targets/index_view');
        $this->view->display('admin/footer_view');
    }

	public function popup() {
		if (!isAjaxRequest()){
			headerRedirect();
		}

		if (!logged_in()){
			messageInModal(translate("systmess_error_should_be_logged"));
        }

        $this->_load_main();

		$op = $this->uri->segment(3);
		switch ($op) {
			case 'add_target':
				checkPermisionAjaxModal('manage_analytics_targets');

                $data['target_operators'] = $this->analytics->target_operators;
                $this->view->assign($data);
				$this->view->display('admin/analytics/targets/form_view');
			break;
			case 'edit_target':
				checkPermisionAjaxModal('blogs_administration');

				$id_target = $this->uri->segment(4);
                $data['target'] = $this->analytics->get_target($id_target);
                if(empty($data['target'])){
                    messageInModal(translate("systmess_error_sended_data_not_valid"));
                }

                $data['target_operators'] = $this->analytics->target_operators;
                $this->view->assign($data);
				$this->view->display('admin/analytics/targets/form_view');
			break;
		}
    }

	public function ajax_operations() {
		if (!isAjaxRequest()){
			headerRedirect();
		}

        $this->_load_main();

		$op = $this->uri->segment(3);
		switch ($op) {
			case 'add_target':
				checkPermisionAjax('manage_analytics_targets');

				$validator_rules = array(
					array(
						'field' => 'target_name',
						'label' => 'Target name',
						'rules' => array('required' => '', 'max_len[250]' => '')
					),
					array(
						'field' => 'target_type',
						'label' => 'Target type',
						'rules' => array('required' => '')
					)
				);
				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate()){
					jsonResponse($this->validator->get_array_errors());
                }

                $insert = array(
                    'target_name' => cleanInput($_POST['target_name']),
                    'target_type' => cleanInput($_POST['target_type']),
                    'target_operator' => ($_POST['target_operator'] && in_array($_POST['target_operator'], $this->analytics->target_operators))?$_POST['target_operator']:'',
                    'target_active_ga' => ($_POST['target_active_ga'])?1:0,
                    'target_active_oa' => ($_POST['target_active_oa'])?1:0,
                );

                if (!empty($_POST['aliases'])) {
                    $aliases = array();
                    foreach ($_POST['aliases'] as $alias) {
                        $alias = trim($alias);
                        if(empty($alias)){
                            continue;
                        }

                        $aliases[] = array(
                            'value' => $alias
                        );
                    }

                    if (!empty($aliases)) {
                        $insert['target_aliases'] = json_encode($aliases);
                    }
                }

                $this->analytics->insert_target($insert);
                jsonResponse(translate("systmess_success_target_has_been_added"), 'success');
			break;
			case 'edit_target':
                checkPermisionAjax('manage_analytics_targets');

				$validator_rules = array(
					array(
						'field' => 'id_target',
						'label' => 'Target info',
						'rules' => array('required' => '', 'integer' => '')
					),
					array(
						'field' => 'target_name',
						'label' => 'Target name',
						'rules' => array('required' => '', 'max_len[250]' => '')
					),
					array(
						'field' => 'target_type',
						'label' => 'Target type',
						'rules' => array('required' => '')
					)
				);
				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate()){
					jsonResponse($this->validator->get_array_errors());
                }

                $id_target = (int)$_POST['id_target'];
				$target = $this->analytics->get_target($id_target);
				if (empty($target)){
					jsonResponse(translate('systmess_error_invalid_data'));
                }

                $update = array(
                    'target_name' => cleanInput($_POST['target_name']),
                    'target_type' => cleanInput($_POST['target_type']),
                    'target_operator' => ($_POST['target_operator'] && in_array($_POST['target_operator'], $this->analytics->target_operators))?$_POST['target_operator']:'',
                    'target_active_ga' => ($_POST['target_active_ga'])?1:0,
                    'target_active_oa' => ($_POST['target_active_oa'])?1:0,
                );

                if (!empty($_POST['aliases'])) {
                    $aliases = array();
                    foreach ($_POST['aliases'] as $alias) {
                        $alias = trim($alias);
                        if(empty($alias)){
                            continue;
                        }

                        $aliases[] = array(
                            'value' => $alias
                        );
                    }

                    if (!empty($aliases)) {
                        $update['target_aliases'] = json_encode($aliases);
                    }
                }

                $this->analytics->update_target($id_target, $update);
                jsonResponse(translate("systmess_success_changes_has_been_saved"), 'success');
			break;
			case 'change_target_state':
				checkPermisionAjax('manage_analytics_targets');

				$id_target = (int)$_POST['id_target'];
				$target = $this->analytics->get_target($id_target);
				if (empty($target)){
					jsonResponse(translate('systmess_error_invalid_data'));
                }

                $target_state = cleanInput($_POST['target_state']);
                if(!in_array($target_state, array('ga', 'oa'))){
					jsonResponse(translate('systmess_error_invalid_data'));
                }

                $state_column = "target_active_{$target_state}";
				$update = array(
                    $state_column => (int)!$target[$state_column]
                );
                $this->analytics->update_target($id_target, $update);
                jsonResponse(translate("systmess_success_visibility_state_changed"), 'success');
			break;
			case 'delete_target':
                checkPermisionAjax('manage_analytics_targets');

				$id_target = (int)$_POST['id_target'];
				$this->analytics->delete_target($id_target);

				jsonResponse(translate("systmess_success_target_has_been_deleted"), 'success');
			break;
			case 'list_targets_dt':
                checkPermisionAjax('manage_analytics_targets');

				$params = array(
                    'sort_by' => flat_dt_ordering($_POST, array(
                        'dt_id' => 'id_target'
                    ))
                );

				$records = $this->analytics->get_targets($params);
				$records_count = $this->analytics->count_targets($params);

				$output = array(
					"sEcho" => intval($_POST['sEcho']),
					"iTotalRecords" => $records_count,
					"iTotalDisplayRecords" => $records_count,
					'aaData' => array()
				);

				if(empty($records)) {
					jsonDTResponse('', array(), 'success');
                }

				foreach ($records as $record) {
                    $target_ga =
					$output['aaData'][] = array(
						'dt_id'   		=> $record['id_target'],
						'dt_name'       => $record['target_name'],
						'dt_type'      	=> $record['target_type'],
						'dt_active_ga'  => '<a href="" class="ep-icon '.(($record['target_active_ga'] == 1)?'ep-icon_ok-circle txt-green':'ep-icon_minus-circle txt-red').' call-function" data-callback="change_target_state" data-state="ga" data-target="'.$record['id_target'].'"></a>',
						'dt_active_oa'  => '<a href="" class="ep-icon '.(($record['target_active_oa'] == 1)?'ep-icon_ok-circle txt-green':'ep-icon_minus-circle txt-red').' call-function" data-callback="change_target_state" data-state="oa" data-target="'.$record['id_target'].'"></a>',
                        'dt_actions'    => '<a class="ep-icon ep-icon_pencil fancyboxValidateModalDT fancybox.ajax" href="'.__SITE_URL.'analytics/popup/edit_target/'.$record['id_target'].'" data-title="Edit target"></a>
                                            <a class="ep-icon ep-icon_trash txt-red confirm-dialog" data-message="' . translate("systmess_confirm_sure_want_to_delete_target") . '" data-callback="delete_target" href="#" data-title="Delete target" data-target="'.$record['id_target'].'"></a>'
					);
				}

				jsonResponse('', 'success', $output);
			break;
			case 'google_report_dt':
                checkPermisionAjax('manage_analytics');

                $params = array_merge(
                    array(
                        'start' => $_POST['iDisplayStart'],
                        'limit' => $_POST['iDisplayLength'],
                        'sort_by' => flat_dt_ordering($_POST, array(
                            'dt_date' => 'analytic_date',
                            'dt_users' => 'users',
                            'dt_new_users' => 'new_users',
                            'new_visitors' => 'new_visitors',
                            'returning_visitors' => 'returning_visitors',
                            'dt_sessions' => 'sessions',
                            'dt_bounces' => 'bounces',
                            'dt_pageviews' => 'pageviews',
                            'dt_avg_time_on_page' => 'avg_time_on_page',
                            'dt_entrances' => 'entrances',
                            'dt_exits' => 'exits',
                        ))
                    ),
                    dtConditions($_POST, array(
                        array('as' => 'id_target',     'key' => 'id_target',     'type' => 'int'),
                        array('as' => 'analytic_date', 'key' => 'analytic_date', 'type' => 'getDateFormat:m/d/Y,Y-m-d')
                    ))
                );

				$targets = arrayByKey($this->analytics->get_targets(array('target_type' => 'page', 'target_active_ga' => 1)), 'id_target');
                $records = $this->analytics->get_ga($params);
                $records_count = $this->analytics->count_ga($params);

				$output = array(
					"sEcho" => intval($_POST['sEcho']),
					"iTotalRecords" => $records_count,
					"iTotalDisplayRecords" => $records_count,
					'aaData' => array()
				);

				if(empty($records)) {
					jsonDTResponse('', array(), 'success');
                }

                $this->load->model("Usergroup_Model", "usergroup");
                $this->load->model('User_Model', 'users');
                $users_params = array(
                    'registration_start_date' => $params['analytic_date'],
                    'registration_end_date' => $params['analytic_date']
                );
                $groups_users_count = arrayByKey($this->usergroup->countUsersByGroups($users_params), "idgroup");

                $counter_by_groups = array();
                foreach ($groups_users_count as $group_users) {
                    if ($group_users['counter'] > 0) {
                        $counter_by_groups[] = '<span class="label label-primary">'.$group_users['gr_name'].': '.$group_users['counter'].'</span>';
                    }
                }

                $registered_users = !empty($counter_by_groups)?'<p class="tal">'.implode('<br>', $counter_by_groups).'</p>':0;

				foreach ($records as $record) {
                    $target_url = normalize_url(__SITE_URL.$record['target_path']);
					$output['aaData'][] = array(
                        'dt_target'   		    => '<strong>'.$this->analytics->target_types[$targets[$record['id_target']]['target_type']]['name'] .': '. $targets[$record['id_target']]['target_name'].'</strong><br>
                                                    <a href="'.$target_url.'" target="_blank">'.$target_url.'</a>',
						'dt_users'   		    => $record['users'],
						'dt_registered'   		=> $registered_users,
						'dt_new_users'   		=> $record['new_users'],
						'dt_new_visitors'   	=> $record['new_visitors'],
						'dt_returning_visitors' => $record['returning_visitors'],
						'dt_sessions'   		=> $record['sessions'],
						'dt_bounces'   		    => $record['bounces'].'<br>'.($record['bounce_rate']*100).' %',
						'dt_pageviews'   		=> $record['pageviews'],
						'dt_avg_time_on_page'   => number_format($record['avg_time_on_page'], 2),
						'dt_entrances'   		=> $record['entrances'].'<br>'.($record['entrance_rate']*100).' %',
						'dt_exits'   		    => $record['exits'].'<br>'.$record['exit_rate'].' %'
					);
				}

				jsonResponse('', 'success', $output);
			break;
			case 'google_countries_dt':
                checkPermisionAjax('manage_analytics');

				$params = array(
                    'start' => (int) $_POST['iDisplayStart'],
                    'limit' => (int) $_POST['iDisplayLength'],
                );

                $sort_by = flat_dt_ordering($_POST, array(
                    'dt_date' => 'analytic_date',
                    'dt_users' => 'users',
                    'dt_new_users' => 'new_users',
                    'new_visitors' => 'new_visitors',
                    'returning_visitors' => 'returning_visitors',
                    'dt_sessions' => 'sessions',
                    'dt_bounces' => 'bounces',
                    'dt_pageviews' => 'pageviews',
                    'dt_avg_time_on_page' => 'avg_time_on_page',
                    'dt_entrances' => 'entrances',
                    'dt_exits' => 'exits',
                ));

                if(!empty($sort_by)){
                    $params['sort_by'] = $sort_by;
                }

                if (isset($_POST['analytic_date']) && validateDate($_POST['analytic_date'], 'm/d/Y')) {
                    $params['analytic_date'] = getDateFormat($_POST['analytic_date'], 'm/d/Y', 'Y-m-d');
                }

                if (isset($_POST['id_country'])) {
                    $params['id_country'] = (int) $_POST['id_country'];
                }

                if (isset($_POST['ga_country'])) {
                    $params['ga_country'] = cleanInput($_POST['ga_country']);
                }

				$records = $this->analytics->get_ga_countries($params);
                $records_count = $this->analytics->count_ga_countries($params);

				$output = array(
					"sEcho" => intval($_POST['sEcho']),
					"iTotalRecords" => $records_count,
					"iTotalDisplayRecords" => $records_count,
					'aaData' => array()
				);

				if(empty($records)) {
					jsonDTResponse('', array(), 'success');
                }

				foreach ($records as $record) {
					$output['aaData'][] = array(
                        'dt_target'   		    => '<strong>'.$record['ga_country'].'</strong>',
						'dt_users'   		    => $record['users'],
						'dt_new_users'   		=> $record['new_users'],
						'dt_new_visitors'   	=> $record['new_visitors'],
						'dt_returning_visitors' => $record['returning_visitors'],
						'dt_sessions'   		=> $record['sessions'],
						'dt_bounces'   		    => $record['bounces'].'<br>'.($record['bounce_rate']*100).' %',
						'dt_pageviews'   		=> $record['pageviews'],
						'dt_avg_time_on_page'   => number_format($record['avg_time_on_page'], 2),
						'dt_entrances'   		=> $record['entrances'].'<br>'.($record['entrance_rate']*100).' %',
						'dt_exits'   		    => $record['exits'].'<br>'.$record['exit_rate'].' %'
					);
				}

				jsonResponse('', 'success', $output);
			break;
			case 'google_referrals_dt':
                checkPermisionAjax('manage_analytics');

				$params = array(
                    'start' => (int) $_POST['iDisplayStart'],
                    'limit' => (int) $_POST['iDisplayLength'],
                );

                $sort_by = flat_dt_ordering($_POST, array(
                    'dt_date' => 'analytic_date',
                    'dt_users' => 'users',
                    'dt_new_users' => 'new_users',
                    'new_visitors' => 'new_visitors',
                    'returning_visitors' => 'returning_visitors',
                    'dt_sessions' => 'sessions',
                    'dt_bounces' => 'bounces',
                    'dt_pageviews' => 'pageviews',
                    'dt_avg_time_on_page' => 'avg_time_on_page',
                    'dt_entrances' => 'entrances',
                    'dt_exits' => 'exits',
                ));

                if(!empty($sort_by)){
                    $params['sort_by'] = $sort_by;
                }

                if (isset($_POST['analytic_date']) && validateDate($_POST['analytic_date'], 'm/d/Y')) {
                    $params['analytic_date'] = getDateFormat($_POST['analytic_date'], 'm/d/Y', 'Y-m-d');
                }

                if (isset($_POST['referrer_source'])) {
                    $params['referrer_source'] = cleanInput($_POST['referrer_source']);
                }

				$records = $this->analytics->get_ga_referrals($params);
                $records_count = $this->analytics->count_ga_referrals($params);

				$output = array(
					"sEcho" => intval($_POST['sEcho']),
					"iTotalRecords" => $records_count,
					"iTotalDisplayRecords" => $records_count,
					'aaData' => array()
				);

				if(empty($records)) {
					jsonDTResponse('', array(), 'success');
                }

				foreach ($records as $record) {
					$output['aaData'][] = array(
                        'dt_target'   		    => '<strong>'.$record['referrer_source'].'</strong><br>'.$record['referrer_full_path'],
						'dt_users'   		    => $record['users'],
						'dt_new_users'   		=> $record['new_users'],
						'dt_new_visitors'   	=> $record['new_visitors'],
						'dt_returning_visitors' => $record['returning_visitors'],
						'dt_sessions'   		=> $record['sessions'],
						'dt_bounces'   		    => $record['bounces'].'<br>'.($record['bounce_rate']*100).' %',
						'dt_pageviews'   		=> $record['pageviews'],
						'dt_avg_time_on_page'   => number_format($record['avg_time_on_page'], 2),
						'dt_entrances'   		=> $record['entrances'].'<br>'.($record['entrance_rate']*100).' %',
						'dt_exits'   		    => $record['exits'].'<br>'.$record['exit_rate'].' %'
					);
				}

				jsonResponse('', 'success', $output);
			break;
			case 'pageview_report_dt':
                checkPermisionAjax('manage_analytics');

                $params = array_merge(
                    array(
                        'start' => $_POST['iDisplayStart'],
                        'limit' => $_POST['iDisplayLength'],
                        'sort_by' => flat_dt_ordering($_POST, array(
                            'dt_date' => 'analytic_date',
                            'dt_users' => 'users',
                            'dt_sessions' => 'sessions',
                            'dt_visits' => 'visits',
                        ))
                    ),
                    dtConditions($_POST, array(
                        array('as' => 'id_target',     'key' => 'id_target',     'type' => 'int'),
                        array('as' => 'analytic_date', 'key' => 'analytic_date', 'type' => 'getDateFormat:m/d/Y,Y-m-d')
                    ))
                );

				$targets = arrayByKey($this->analytics->get_targets(array('target_type' => 'page', 'target_active_oa' => 1)), 'id_target');
				$records = $this->analytics->get_pageviews($params);
                $records_count = $this->analytics->count_pageviews($params);

				$output = array(
					"sEcho" => intval($_POST['sEcho']),
					"iTotalRecords" => $records_count,
					"iTotalDisplayRecords" => $records_count,
					'aaData' => array()
				);

				if(empty($records)) {
					jsonDTResponse('', array(), 'success');
                }

                $this->load->model("Usergroup_Model", "usergroup");
                $this->load->model('User_Model', 'users');
                $users_params = array(
                    'registration_start_date' => $params['analytic_date'],
                    'registration_end_date' => $params['analytic_date']
                );
                $groups_users_count = arrayByKey($this->usergroup->countUsersByGroups($users_params), "idgroup");
                $counter_by_groups = array();
                foreach ($groups_users_count as $group_users) {
                    if ($group_users['counter'] > 0) {
                        $counter_by_groups[] = '<span class="label label-primary">'.$group_users['gr_name'].': '.$group_users['counter'].'</span>';
                    }
                }

                $registered_users = !empty($counter_by_groups)?'<p class="tal">'.implode('<br>', $counter_by_groups).'</p>':0;

				foreach ($records as $record) {
                    $target_url = normalize_url(__SITE_URL.$record['target_path']);
					$output['aaData'][] = array(
                        'dt_target'   		    => '<strong>'.$this->analytics->target_types[$targets[$record['id_target']]['target_type']]['name'] .': '. $targets[$record['id_target']]['target_name'].'</strong><br>
                                                    <a href="'.$target_url.'" target="_blank">'.$target_url.'</a>',
						'dt_users'   		    => $record['users'],
						'dt_registered'   		=> $registered_users,
						'dt_sessions'   		=> $record['sessions'],
						'dt_visits'   		=> $record['visits']
					);
				}

				jsonResponse('', 'success', $output);
			break;
			case 'forms_filled_report_dt':
                checkPermisionAjax('manage_analytics');

				$params = array(
                    'start' => $_POST['iDisplayStart'],
                    'limit' => $_POST['iDisplayLength'],
                );

                $params = array_merge(
                    array(
                        'start' => $_POST['iDisplayStart'],
                        'limit' => $_POST['iDisplayLength'],
                        'sort_by' => flat_dt_ordering($_POST, array(
                            'dt_date' => 'analytic_date'
                        ))
                    ),
                    dtConditions($_POST, array(
                        array('as' => 'id_target',     'key' => 'id_target',     'type' => 'int'),
                        array('as' => 'analytic_date', 'key' => 'analytic_date', 'type' => 'getDateFormat:m/d/Y,Y-m-d')
                    ))
                );

				$targets = arrayByKey($this->analytics->get_targets(array('target_type' => 'form', 'target_active_oa' => 1)), 'id_target');
				$records = $this->analytics->get_forms($params);
                $records_count = $this->analytics->count_forms($params);

				$output = array(
					"sEcho" => intval($_POST['sEcho']),
					"iTotalRecords" => $records_count,
					"iTotalDisplayRecords" => $records_count,
					'aaData' => array()
				);

				if(empty($records)) {
					jsonDTResponse('', array(), 'success');
                }

                $this->load->model("Usergroup_Model", "usergroup");
                $this->load->model('User_Model', 'users');
                foreach ($records as $record) {
                    $target_url = normalize_url(__SITE_URL.$record['target_path']);
					$output['aaData'][] = array(
                        'dt_target'   		    => '<strong>'.$this->analytics->target_types[$targets[$record['id_target']]['target_type']]['name'] .': '. $targets[$record['id_target']]['target_name'].'</strong>',
						'dt_filled_users'	    => $record['filled_users'],
						'dt_filled_sessions'   	=> $record['filled_sessions'],
						'dt_submits_users'	    => $record['submits_users'],
						'dt_submits_sessions'   => $record['submits_sessions'],
						'dt_submits'   		    => $record['submits'],
						'dt_success_submits'    => $record['success_submits']
					);
				}

				jsonResponse('', 'success', $output);
			break;
		}
    }
}
