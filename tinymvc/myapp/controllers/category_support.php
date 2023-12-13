<?php
/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [15.12.2021]
 * Controller Refactoring
 */
class Category_Support_Controller extends TinyMVC_Controller {
    private function load_main(){
        $this->load->model('Category_Support_Model', 'category_support');
    }

    public function administration(){
        checkAdmin('admin_site');
        $data['title'] = 'Category support';

        $this->view->assign($data);
        $this->view->display('admin/header_view');
        $this->view->display('admin/category_support/index_view');
        $this->view->display('admin/footer_view');
    }

    public function ajax_dt_cat_support(){
        checkAdmin('admin_site');

        $this->load_main();

        $params = array('per_p' => $_POST['iDisplayLength'], 'start' => $_POST['iDisplayStart']);

        if ($_POST['iSortingCols'] > 0) {
            for ($i = 0; $i < $_POST['iSortingCols']; $i++) {
                switch ($_POST["mDataProp_" . intVal($_POST['iSortCol_' . $i])]) {
                    case 'dt_id_record': $params['sort_by'][] = 'id_spcat-' . $_POST['sSortDir_' . $i];break;
                    case 'dt_category':  $params['sort_by'][] = 'category-' . $_POST['sSortDir_' . $i];break;
                }
            }
        }

        if (isset($_POST['keywords']))
            $params['keywords'] = cleanInput(cut_str($_POST['keywords']));

        $categoriesSupport   = $this->category_support->get_support_categories($params);
        $categorySupportCount= $this->category_support->get_support_category_count($params);

        $output = array(
            "sEcho" => intval($_POST['sEcho']),
            "iTotalRecords" => $categorySupportCount,
            "iTotalDisplayRecords" => $categorySupportCount,
			'aaData' => array()
        );

        if(empty($categoriesSupport))
			jsonResponse('', 'success', $output);

		foreach($categoriesSupport as $category){
			$output['aaData'][] = array(
				'dt_id_record'  =>  $category['id_spcat'],
				'dt_category'   =>  $category['category'],
				'dt_assign'     =>  $category['full_name'],
				'dt_actions'    =>  '<a class="fancyboxValidateModalDT fancybox.ajax ep-icon ep-icon_pencil" href="' . __SITE_URL . 'category_support/popup_forms/edit_category_support/'. $category['id_spcat'] . '" data-title="Edit category of support" title="Edit category of support"></a>
									 <a class="ep-icon ep-icon_remove txt-red confirm-dialog" data-callback="remove_category_support" data-record="' . $category['id_spcat'] . '" title="Remove this category of support" data-message="Are you sure you want to delete this category of support ?" href="#" ></a>',
			);
		}

        jsonResponse('', 'success', $output);
    }

    public function popup_forms(){
        checkAdminAjaxModal('admin_site');

        $this->load_main();

        $form_action= $this->uri->segment(3);
        switch ($form_action) {
            case 'add_category_support':
                $data['list_ep_staff' ] = $this->category_support->get_staff_ep();

                $this->view->display('admin/category_support/add_category_form_view', $data);
            break;

            case 'edit_category_support':
                $data['list_ep_staff' ] = $this->category_support->get_staff_ep();

                $id_record = $this->uri->segment(4);
                $data['record'] = $this->category_support->get_support_category(array('id_record' => $id_record));

                if(empty($data['record']))
                    messageInModal('Error: This category of support doesn\'t found.');

                $this->view->display('admin/category_support/edit_category_form_view', $data);
            break;
        }
    }

    public function ajax_category_support_operation(){
        checkAdminAjax('admin_site');

        $this->load_main();

        $operation = $this->uri->segment(3);

        switch($operation){
            case 'add_category_support':
                $validator_rules = array(
                    array(
                        'field' => 'category_name',
                        'label' => 'Name category',
                        'rules' => array('required' => '', 'max_len[50]' => '')
                    ),
                    array(
                        'field' => 'user_id',
                        'label' => 'Id users',
                        'rules' => array('required' => '')
                    ),

                );
                $this->validator->set_rules($validator_rules);

                if(!$this->validator->validate())
                    jsonResponse($this->validator->get_array_errors());

                $newCategory = array(
                    'category' => cleanInput($_POST['category_name']),
                    'user_list' => implode(',', $_POST['user_id']),
                );

                if($this->category_support->check_support_category(array('category' => $newCategory['category'])))
                    jsonResponse('Category of support with this name already exist.');

                $resultId = $this->category_support->set_support_category($newCategory);

                if($resultId){
                    jsonResponse('Category of support has been added successfully.', 'success');
                } else {
                    jsonResponse('Error: You cannot added this category of support now. Please try later.');
                }
            break;

            case 'edit_category_support':
                $validator_rules = array(
                    array(
                        'field' => 'category_name',
                        'label' => 'Name category',
                        'rules' => array('required' => '', 'max_len[50]' => '')
                    ),
                    array(
                        'field' => 'user_id',
                        'label' => 'Id users',
                        'rules' => array('required' => '',)
                    ),

                );
                $this->validator->set_rules($validator_rules);

                if(!$this->validator->validate())
                    jsonResponse($this->validator->get_array_errors());

                $updateCategory = array(
                    'category' => cleanInput($_POST['category_name']),
                    'user_list' => implode(',', $_POST['user_id']),
                );

                $id_record = intVal($_POST['id_record']);

                if($this->category_support->check_support_category(array('category' => $updateCategory['category'], 'not_id_record' => $id_record)))
                    jsonResponse('Category of support with this name already exist.');

                $resultId = $this->category_support->update_support_category($id_record, $updateCategory);

                if($resultId){
                    jsonResponse('Category of support was changed successfully.', 'success');
                } else {
                    jsonResponse('Error: You cannot changed this category of support now. Please try later.');
                }
            break;

            case 'remove_category_support':
                $id_record = intVal($_POST['record']);
                $record = $this->category_support->get_support_category(array('id_record' => $id_record));

                if(empty($record))
                    jsonResponse('This category of support doesn\'t found.');

                if($this->category_support->delete_support_category($id_record))
                    jsonResponse('Category of support has been successfully removed.', 'success');
                else
                    jsonResponse('Error: You cannot remove this category of support now. Please try again later.');
            break;
        }
    }
}
