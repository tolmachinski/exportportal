<?php

use const App\Logger\Activity\ResourceTypes\COMPANY;
use const App\Logger\Activity\ResourceTypes\ITEM;

/**
 * Admin application controller.
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [15.12.2021]
 * Controller Refactoring
 *
 * @property \Activity_Logs_Model             $activity
 * @property \Company_Model                   $companies
 * @property \Items_Model                     $items
 * @property \TinyMVC_Load                    $load
 * @property \TinyMVC_View                    $view
 * @property \TinyMVC_Library_URI             $uri
 * @property \TinyMVC_Library_Session         $session
 * @property \TinyMVC_Library_Cookies         $cookies
 * @property \TinyMVC_Library_Upload          $upload
 * @property \TinyMVC_Library_validator       $validator
 * @property \TinyMVC_Library_Activity_Logger $activity_logger
 * @property \User_Model                      $user
 *
 * @author Litra Andrei
 */
class admin_Controller extends TinyMVC_Controller {

    private function _load_main() {
        $this->load->model('User_Model', 'user');
    }

    function index() {
        if(!in_array(user_group_type(), array('EP Staff','Admin'))){
            headerRedirect();
        }

        if(have_right('manage_users_activity')){
            $this->_load_main();
            $data = array(
                'title'           => 'Administration Area',
                'visibility'      => $this->activity->get_visibility(),
                'log_levels'      => TinyMVC_Library_Activity_Logger::getLevels(),
                'resource_types'  => array_column($this->activity->get_resource_types(array('order' => array('name' =>  'ASC'))), 'name', 'id_type'),
                'operation_types' => array_column($this->activity->get_operation_types(array('order' => array('name' =>  'ASC'))), 'name', 'id_type'),
            );

            if(isset($_GET['date'])) {
                try {
                    $date = new DateTime($_GET['date']);
                } catch (\Exception $exception) {
                    $date = null;
                }

                if(null !== $date) {
                    $data['filters']['date_from'] = $date->format('Y/m/d');
                    $data['filters']['date_to'] = $date->format('Y/m/d');
                }
            }

            if(isset($_GET['user'])){
                $user_id = (int) $_GET['user'];
                $data['filters']['initiator'] = $this->user->getSimpleUser($user_id);
            }

            if(isset($_GET['company'])) {
                $this->load->model('Company_Model', 'companies');

                $company = $this->companies->get_company(array('id_company' => (int)  $_GET['company']));
                if(!empty($company)) {
                    $data['filters']['resource_type'] = COMPANY;
                    $data['filters']['resource'] = array(
                        'id'    => $company['id_company'],
                        'title' => $company['name_company'],
                    );
                }
            }

            if(isset($_GET['item'])) {
                $this->load->model('Items_Model', 'items');

                $item = $this->items->get_item((int) $_GET['item'], "id, title");
                if(!empty($item)) {
                    $data['filters']['resource_type'] = ITEM;
                    $data['filters']['resource'] = $item;
                }

            }

            $this->view->assign($data);
            $this->view->display('admin/header_view');
            $this->view->display('admin/activity/index_view');
            $this->view->display('admin/footer_view');
        } else{
            $this->view->display('admin/header_view');
            $this->view->display('admin/footer_view');
        }
    }

    function groupright() {
        checkAdmin('manage_grouprights');

        $this->load->model('UserGroup_Model', 'groups');

        $group_params = array(
            'counter' => false
        );
        $type = $this->uri->segment(3);
        switch ($type) {
            case 'ep_staff':
                $group_params['type'] = "'EP Staff','Admin'";
            break;
            case 'ba':
                $group_params['type'] = "'CR Affiliate'";
            break;
            default:
                $group_params['type'] = "'Buyer','Seller','Company Staff','Shipper','Shipper Staff'";
            break;
        }
        $data['groups'] = $this->groups->getGroupsByType($group_params);

        $data['bymodules'] = $this->groups->getRModules();
        $data['relations'] = $this->groups->getRightGroupRelation();

        $this->view->assign($data);
        $this->view->assign('title', 'Groups and rights');
        $this->view->display('admin/header_view');
        $this->view->display('admin/user/grouprights/groupright_view');
        $this->view->display('admin/footer_view');
    }

    function grouprightad() {
        checkAdmin('manage_grouprights');

        $this->load->model('UserGroup_Model', 'groups');
        $data = $this->session->getMessages();

        $data['groups'] = $this->groups->getGroups();
        $data['bymodules'] = $this->groups->getRModules();
        $this->view->assign($data);
        $this->view->display('admin/header_view');
        $this->view->display('admin/user/grouprights/crud_view');
        $this->view->display('admin/footer_view');
    }

    function user_services() {
        checkAdmin('user_services_administration');

        $this->load->model('Userfeedback_Model', 'feedback');
        $this->load->model('UserGroup_Model', 'groups');

        $data['services'] = $this->feedback->getServices();

        $data['groups'] = $this->groups->getGroupsByType(array('type' =>"'Buyer','Seller','Shipper'"));
        $data['relations'] = $this->feedback->getServiceGroupRelations();

        $this->view->assign('title', 'User\'s services');
        $this->view->assign($data);
        $this->view->display('admin/header_view');
        $this->view->display('admin/user/services_view');
        $this->view->display('admin/footer_view');
    }

    public function ajax_admin_operation() {
        if (!isAjaxRequest())
            headerRedirect();

        if (!logged_in())
            jsonResponse(translate("systmess_error_should_be_logged"));

        $id_user = $this->session->id;
        $op = $this->uri->segment(3);

        switch ($op) {
            case 'create_group_service':
                $validator_rules = array(
                    array(
                        'field' => 's_title',
                        'label' => 'Name',
                        'rules' => array('required' => '','alpha' => '')
                    )
                );

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate())
                    jsonResponse($this->validator->get_array_errors());

                $this->load->model('Userfeedback_Model', 'feedback');

                $data = array('s_title' => cleanInput($_POST['s_title']));

                if($this->feedback->existService($data['s_title']))
                    jsonResponse(translate("systmess_error_service_already_exists"));

                $data['id_service'] = $this->feedback->setService($data);

                if($data['id_service'])
                    jsonResponse(translate("systmess_success_service_created"), 'success', $data);
                else
                    jsonResponse(translate("systmess_error_service_create"));
            break;
            case 'update_group_service':
                $validator_rules = array(
                    array(
                        'field' => 's_title',
                        'label' => 'Name',
                        'rules' => array('required' => '','alpha' => '')
                    )
                );

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate())
                    jsonResponse($this->validator->get_array_errors());

                $this->load->model('Userfeedback_Model', 'feedback');

                $data = array('s_title' => cleanInput($_POST['s_title']));
                $id_service = intval($_POST['id_service']);

                if($this->feedback->updateService($id_service, $data)){
                    $data['id_service'] = $id_service;
                    jsonResponse(translate("systmess_success_service_updated"), 'success', $data);
                }else
                    jsonResponse(translate("systmess_error_service_update"));
            break;
            case 'delete_group_service':
                $id_service = intval($_POST['service']);
                $this->load->model('Userfeedback_Model', 'feedback');

                if($this->feedback->deleteService($id_service))
                    jsonResponse(translate("systmess_success_service_deleted"), 'success');
            break;
            case 'create_relation_service':
                $this->load->model('Userfeedback_Model', 'feedback');
                $data = array(
                    'id_group' => intval($_POST['group']),
                    'id_service' => intval($_POST['service']));

                if (!$this->feedback->setServiceGroupRelation($data))
                    jsonResponse(translate("systmess_success_relation_service_create"), 'success', $data);
                else
                    jsonResponse(translate("systmess_error_relation_service_create"));
            break;
            case 'delete_relation_service':
                $this->load->model('Userfeedback_Model', 'feedback');
                $data = array(
                    'id_group' => intval($_POST['group']),
                    'id_service' => intval($_POST['service']));

                if ($this->feedback->deleteServiceGroupRelation($data['id_group'], $data['id_service']))
                    jsonResponse(translate("systmess_success_relation_service_removed"), 'success');
                else
                    jsonResponse(translate("systmess_error_relation_service_remove"));
            break;
        }
    }

    function popup_forms() {
        if (!isAjaxRequest())
            headerRedirect();

        if (!logged_in())
            messageInModal(translate("systmess_error_should_be_logged"));

        $op = $this->uri->segment(3);
        switch ($op) {
            case 'edit_group_service':
                $this->load->model('Userfeedback_Model', 'feedback');
                $id_service = intval($this->uri->segment(4));
                $data['edit_service'] = $this->feedback->getService($id_service);

                $this->view->assign($data);
                $this->view->display('admin/user/popup_services_form_view');
            break;
            case 'add_group_service':
                $this->view->display('admin/user/popup_services_form_view');
            break;
        }
    }

    function pages(){
        checkAdmin('moderate_content');

        $this->view->display('admin/header_view');
        $this->view->display('new/admin/pages/index_view');
        $this->view->display('admin/footer_view');
    }
}
