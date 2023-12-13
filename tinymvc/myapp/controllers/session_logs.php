<?php
/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [18.12.2021]
 * Controller Refactoring
 */
class Session_logs_Controller extends TinyMVC_Controller {

    function popup_forms(){
        checkIsAjax();
        checkIsLoggedAjaxModal();

        $action = $this->uri->segment(3);
        switch ($action) {
            case 'by_user':
                checkPermisionAjaxModal('view_session_logs');

                $id_user = (int) $this->uri->segment(4);
                $data['user_info'] = model('user')->getUser($id_user);
                if(empty($data['user_info'])){
                    messageInModal(translate("systmess_error_user_does_not_exist"));
                }

				$this->view->assign($data);
				$this->view->display('admin/user/statistic/session_logs_view');
            break;
        }
    }

    function ajax_operations(){
        if (!isAjaxRequest())
            headerRedirect();

        if (!logged_in())
            jsonResponse(translate("systmess_error_should_be_logged"));

        $action = $this->uri->segment(3);
        switch ($action) {
            case 'by_user_dt':
                checkAdminAjaxDT('view_session_logs');

                $id_user = (int) $this->uri->segment(4);
                $user_info = model('user')->getUser($id_user);
                if(empty($user_info)){
                    jsonDTResponse(translate("systmess_error_user_does_not_exist"));
                }

                $params = array(
                    'id_user' => $id_user,
                    'per_p'   => (int) $_POST['iDisplayLength'],
                    'start'   => (int) $_POST['iDisplayStart'],
                );

                $sort_by = flat_dt_ordering($_POST, array(
                    'dt_date' => 'log_date'
                ));

                if (!empty($sort_by)) {
                    $params['sort_by'] = $sort_by;
                }

                if (isset($_POST["date_from"]) && validateDate($_POST["date_from"], 'm/d/Y')) {
                    $user_params["date_from"] = getDateFormat($_POST["date_from"], 'm/d/Y', 'Y-m-d');
                }

                if (isset($_POST["date_to"]) && validateDate($_POST["date_to"], 'm/d/Y')) {
                    $user_params["date_to"] = getDateFormat($_POST["date_to"], 'm/d/Y', 'Y-m-d');
                }

                $records = model('session_logs')->handler_get_all($params);
                $records_total = model('session_logs')->handler_get_count($params);

                $output = array(
                    'sEcho'                => intval($_POST['sEcho']),
                    'iTotalRecords'        => $records_total,
                    'iTotalDisplayRecords' => $records_total,
                    'aaData'               => array(),
                );

                if (empty($records)) {
                    jsonResponse('', 'success', $output);
                }

                foreach ($records as $record) {
                    $output['aaData'][] = array(
                        'dt_date'       => getDateFormat($record['log_date'], 'Y-m-d H:i:s', 'm/d/Y H:i:s'),
                        'dt_message'    => $record['log_message']
                    );
                }

                jsonResponse('', 'success', $output);
            break;
        }
    }
}
