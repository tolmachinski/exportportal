<?php
/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [18.12.2021]
 * Controller Refactoring
 */
class User_Statistic_Controller extends TinyMVC_Controller {

    function administration() {
        checkAdmin('user_statistic_administration');

        $this->load->model('UserGroup_Model', 'groups');

        $data['groups'] = $this->groups->getGroupsByType(array('type' =>"'Buyer','Seller','Shipper'"));

        $this->view->assign($data);
        $this->view->assign('title', 'User Statistic');
        $this->view->display('admin/header_view');
        $this->view->display('admin/statistic/index_view');
        $this->view->display('admin/footer_view');
    }

    public function ajax_get_statistic_dt(){
        if (!isAjaxRequest())
            headerRedirect();

        if (!logged_in())
            jsonResponse(translate("systmess_error_should_be_logged"));

        checkAdmin('manage_content');

        $this->load->model('UserGroup_Model', 'groups');
        $this->load->model('User_Statistic_Model', 'statistic');

        $params = array('per_p' => intVal($_POST['iDisplayLength']), 'start' => intVal($_POST['iDisplayStart']));

        if ($_POST['iSortingCols'] > 0) {
            for ($i = 0; $i < $_POST['iSortingCols']; $i++) {
                switch ($_POST["mDataProp_" . intVal($_POST['iSortCol_' . $i])]) {
                    case 'dt_name': $params['order_by'] = ' a.COLUMN_COMMENT ' . $_POST['sSortDir_' . $i];break;
                }
            }
        }

        if (!empty($_POST['sSearch']))
            $params['keywords'] = cleanInput($_POST['sSearch']);

        $columns = $this->statistic->get_statistic_columns($params);
        $columns_counter = $this->statistic->get_statistic_columns_count($params);

        $groups = $this->groups->getGroupsByType(array('type' =>"'Buyer','Seller','Shipper'"));

        $output = array(
            "sEcho" => intval($_POST['sEcho']),
            "iTotalRecords" => $columns_counter,
            "iTotalDisplayRecords" => $columns_counter,
            "aaData" => array()
        );

        foreach($groups as $group){
            $group_statistic[$group['idgroup']] = explode(',', $group['statistic_columns']);
        }

        foreach ($columns as $column) {
            $temp_arr = array(
                'dt_name' => $column['Comment'] . ' (' . $column['Field'].')',
                'dt_actions' => '<a class="fancyboxValidateModalDT fancybox.ajax ep-icon ep-icon_pencil" href="/user_statistic/popup_forms/edit_statistic/'.$column['Field'].'" data-column="'.$column['Field'].'" data-title="Edit statistic" title="Edit statistic"></a>'
                    .'<a class="ep-icon ep-icon_trash txt-red confirm-dialog" data-callback="delete_statisctic" data-column="'.$column['Field'].'" data-message="Are you sure want to remove this statistic?" title="Remove statistic"></a>'
            );

            foreach($groups as $group){
                if(in_array($column['Field'], $group_statistic[$group['idgroup']])){
                    $temp_arr['dt_group' . $group['idgroup']] = '<a data-type="to_dis" data-group="'.$group['idgroup'].'" data-column="'.$column['Field'].'" class="btn-change ep-icon ep-icon_ok txt-green" title="Change to disable"></a>';
                }else{
                    $temp_arr['dt_group' . $group['idgroup']] = '<a data-type="to_en" data-group="'.$group['idgroup'].'" data-column="'.$column['Field'].'" class="btn-change ep-icon ep-icon_remove txt-red" title="Change to disable"></a>';
                }
            }

            $output['aaData'][] = $temp_arr;
        }

        jsonResponse('', 'success', $output);
    }

    public function ajax_columns_operation(){
        if (!isAjaxRequest())
            headerRedirect();

        if (!logged_in())
            jsonResponse(translate("systmess_error_should_be_logged"));

        if (!have_right('moderate_content'))
            jsonResponse(translate("systmess_error_rights_perform_this_action"));

        $this->load->model('User_Statistic_Model', 'statistic');

        $op = $this->uri->segment(3);
        switch($op){
            case 'add' :
                $this->load->model('UserGroup_Model', 'groups');

                $data['column'] = $column_name = cleanInput($_POST['short_name']);
                $data['comment'] = $comment = cleanInput($_POST['comment']);
                $data['groups'] = $this->groups->getGroupsByType(array('type' =>"'Buyer','Seller'"));

                $return['tr'] = $this->view->fetch('admin/statistic/row_view', $data);

                if($this->statistic->add_statistic_column($column_name, $comment))
                    jsonResponse('The parameter has been added successfully', 'success', $return);
                else
                    jsonResponse('Error: you cannot add the parameter. Please try again later.');
                break;
            case 'edit':
                $column_name = cleanInput($_POST['short_name']);
                $comment = filter(cleanInput($_POST['comment']));

                if($this->statistic->update_statistic_column($column_name, $comment))
                    jsonResponse('The parameter has been updated successfully', 'success', array('comment' => $comment));
                else
                    jsonResponse('Error: you cannot update the parameter now. Please try again later.');
                break;
            case 'delete':
                $column = cleanInput($_POST['column']);

                if($this->statistic->delete_statistic_column($column))
                    jsonResponse('The parameter has been deleted successfully', 'success');
                else
                    jsonResponse('Error: you cannot delete the parameter. Please try again later.');
                break;
            case 'enable':
                $column_name = cleanInput($_POST['column']);
                $user_group = intval($_POST['idgroup']);
                if($this->statistic->enable_statistic_column($user_group,$column_name))
                    jsonResponse('The parameter has been successfully enabled for this group', 'success');
                else
                    jsonResponse('Error: you cannot enable the parameter now. Please try again later.');
                break;
            case 'disable':
                $column_name = cleanInput($_POST['column']);
                $user_group = intval($_POST['idgroup']);
                if($this->statistic->disable_statistic_column($user_group,$column_name))
                    jsonResponse('The parameter has been disabled successfully', 'success');
                else
                    jsonResponse('Error: you cannot disable the parameter. Please try again later.');
                break;
        }

    }

    public function ajax_get_statistic(){
        if (!isAjaxRequest())
            headerRedirect();

        if (!logged_in())
            jsonResponse(translate("systmess_error_should_be_logged"));

        if(user_type('users_staff')){
            $this->load->model('User_Model', 'user');
            $user = my_seller();
            $seller = $this->user->getSimpleUser($user, 'users.user_group');
            $group = $seller['user_group'];

        } else{
            $user = $this->session->id;
            $group = $this->session->group;
        }

        $this->load->model('User_Statistic_Model', 'statistic');

        echo json_encode($this->statistic->get_user_statistic($user, $group));
    }

    function my(){
        checkIsLogged();
        checkGroupExpire();
        checkDomainForGroup();

        if (!have_right('have_statistic') || is_user_staff()) {
            show_403();
        }

		$id_user = id_session();
        $group = group_session();

        $data['title'] = 'My statistics';

        $data['statistic'] = model('user_statistic')->get_detail_statistic($id_user, $group);

        usort($data['statistic'], function ($a,$b){
			return ($a["description"] <= $b["description"]) ? -1 : 1;
		});

        if (__CURRENT_SUB_DOMAIN === getSubDomains()['shippers']) {
            $this->userStatisticEpl($data);
        } else {
            $this->userStatisticAll($data);
        }
    }

    private function userStatisticEpl($data){
        $data['templateViews'] = [
            'mainOutContent'    => 'statistic/my/index_view',
        ];
        $data['webpackData'] = [
            'dashboardOldPage' => true,
            // 'styleCritical' => 'epl_styles_home',
            // 'pageConnect' 	=> 'epl_index_page',
        ];

        views(["new/epl/template/index_view"], $data);
    }

    private function userStatisticAll($data){
        views(["new/header_view", "new/statistic/my/index_view", "new/footer_view"], $data);
    }

    function popup_forms(){
        if (!logged_in())
            jsonResponse(translate("systmess_error_should_be_logged"));

        if (!have_right('moderate_content'))
            jsonResponse(translate("systmess_error_rights_perform_this_action"));


        $op = $this->uri->segment(3);
        $key = $this->uri->segment(4);


        switch($op){
            case 'add_statistic':
                $this->view->display('admin/statistic/form_view');
            break;
            case 'edit_statistic':
                $this->load->model('User_Statistic_Model', 'statistic');

                $data = array(
                    'parameter' => $this->statistic->get_detail_stat($key)
                );

                $this->view->display('admin/statistic/form_view', $data);
            break;
        }
    }
}
