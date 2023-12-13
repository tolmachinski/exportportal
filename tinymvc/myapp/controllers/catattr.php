<?php
/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [15.12.2021]
 * Controller Refactoring
 */
class Catattr_Controller extends TinyMVC_Controller {
    function update_order(){
        if(!isAjaxRequest())
            headerRedirect();

        if(!logged_in())
            jsonResponse(translate("systmess_error_should_be_logged"));

        if(!have_right('manage_content'))
            jsonResponse(translate("systmess_error_rights_perform_this_action"));

        $this->load->model('Catattributes_Model', 'attributes');
        if ($_POST['update'] == "update"){
            $order = $_POST['order'];
            $cat = (int) $_POST['cat'];
            if($this->attributes->set_attr_order($cat, $order))
                jsonResponse('Attribute order was updated successfully', 'success');
            else
                jsonResponse('Error: Attribute order has not been updated');
        }
    }

    function update_forms(){
        if(!isAjaxRequest())
            headerRedirect();

        if(!logged_in())
            jsonResponse(translate("systmess_error_should_be_logged"));

        if(!have_right('manage_content'))
            jsonResponse(translate("systmess_error_rights_perform_this_action"));

        $op = $this->uri->segment(3);
        $id = $this->uri->segment(4);

        $this->load->model('Category_Model', 'category');
        $this->load->model('Catattributes_Model', 'attributes');
        switch($op){
            case 'attribute':
                $data['attr'] = $this->attributes->get_attribute($id);
                $data['categories'] = $this->category->getCategories(array('parent' => 0, 'columns' => 'category_id, name'));

                $this->view->display('admin/catattributes/modal_form_view', $data);
            break;
            case 'append':
                $data['attr'] = $this->attributes->get_attribute($id);
                $this->view->display('admin/catattributes/append_modal_form_view', $data);
            break;
            case 'value':
                $data['value'] = $this->attributes->get_attr_value($id);
                $this->view->display('admin/catattributes/valupdate_modal_form_view', $data);
            break;
        }
    }

    function administration(){
        checkAdmin('manage_content');

        $this->load->model('Category_Model', 'category');
        $data['categories'] = $this->category->getCategories(array('parent' => 0));

        $this->view->assign($data);
        $this->view->assign('title', 'Attributes');
        $this->view->display('admin/header_view');
        $this->view->display('admin/catattributes/index_view');
        $this->view->display('admin/footer_view');
    }

    function ajax_cat_attr_dt(){
        if(!isAjaxRequest())
            headerRedirect();

        if(!logged_in())
            jsonResponse(translate("systmess_error_should_be_logged"));

        if(!have_right('manage_content'))
            jsonResponse(translate("systmess_error_rights_perform_this_action"));

        $this->load->model('Catattributes_Model', 'attributes');

        $output = array(
            "sEcho" => intval($_POST['sEcho']),
            "iTotalRecords" => 0,
            "iTotalDisplayRecords" => 0,
            "aaData" => array()
        );

        $params = array_merge(
            array(
                'limit' => (int) $_POST['iDisplayLength'],
                'start' => (int) $_POST['iDisplayStart'],
                'sort_by' => flat_dt_ordering($_POST, array(
                    'dt_id' => 'category_id',
                    'dt_p_or_m' => 'p_or_m',
                    'dt_name' => 'name',
                ))
            ),
            dtConditions($_POST, array(
                array('as' => 'keywords', 'key' => 'sSearch', 'type' => 'cleanInput')
            ))
        );

        $category = (int)$_POST['category'];
        if($category > 0){
            $params['id_category'] = $category;
        }

        $attributes = $this->attributes->get_attributes_dt($params);
        $attributes_count = $this->attributes->get_attributes_dt_count($params);

        $output["iTotalRecords"] = $attributes_count;
        $output["iTotalDisplayRecords"] = $attributes_count;

        foreach($attributes as $attribute){
            $attr_type = '';
            switch($attribute['attr_type']){
                case 'select': $attr_type = 'Select'; break;
                case 'multiselect': $attr_type = 'Multi select'; break;
                case 'text': $attr_type = 'Text'; break;
                case 'range': $attr_type = 'Range'; break;
            }

            $value_type = '';
            switch($attribute['attr_value_type']){
                case 1: $value_type = 'Letters & Numbers'; break;
                case 2: $value_type = 'Only Letters'; break;
                case 3: $value_type = 'Only Numbers'; break;
            }

            $additional_actions = '';
            if(in_array($attribute['attr_type'], array('select', 'multiselect'))){
                $additional_actions = '<a class="ep-icon ep-icon_file-view txt-blue call-function" data-callback="get_attr_values" data-attr="'.$attribute['id'].'" title="View values"></a>';
            }

            $langs = array();
            $langs_record = array_filter(json_decode($attribute['translation_data'], true));
            $langs_record_list = array('English');
            if(!empty($langs_record)){
                foreach ($langs_record as $lang_key => $lang_record) {
                    if($lang_key == 'en'){
                        continue;
                    }

                    $langs[] = '<li>
                                    <div>
                                        <span class="display-ib_i lh-30 pl-5 pr-10">'.$lang_record['lang_name'].'</span>
                                        <a class="display-ib_i lh-30 mb-0 p-0 ep-icon ep-icon_remove txt-red pull-right confirm-dialog" data-callback="delete_attr_i18n" data-attr="' . $attribute['id'] . '" data-lang="'.$lang_key.'" title="Delete" data-message="Are you sure you want to delete the translation?" href="#" ></a>
                                        <a href="'.__SITE_URL.'catattr/popup_forms/edit_attr_i18n/'.$attribute['id'].'/'.$lang_key.'" data-title="Edit attribute translation" title="Edit" class="display-ib_i lh-30 mb-0 p-0 ep-icon ep-icon_pencil fancyboxValidateModalDT fancybox.ajax pull-right"></a>
                                    </div>
                                </li>';
                    $langs_record_list[] = $lang_record['lang_name'];
                }
                $langs[] = '<li role="separator" class="divider"></li>';
            }

            $langs_dropdown = '<div class="dropdown">
                                <a class="ep-icon ep-icon_globe-circle m-0 fs-24 dropdown-toggle" data-toggle="dropdown"></a>
                                <ul class="dropdown-menu">
                                    '.implode('', $langs).'
                                    <li><a href="'.__SITE_URL.'catattr/popup_forms/add_attr_i18n/'.$attribute['id'].'" data-title="Add translation" title="Add translation" class="fancyboxValidateModalDT fancybox.ajax">Add translation</a></li>
                                </ul>
                            </div>';

            $output['aaData'][] = array(
                'DT_RowId'=> 'order_'.$attribute['id'],
                'dt_id' => $attribute['id'],
                'dt_name' => (($attribute['attr_req']) ? '<span class="txt-red txt-bold fs-16" title="will be required">*</span> ' : '') . $attribute['attr_name'],
                'dt_type' => $attr_type,
                'dt_values_type' => $value_type,
                'dt_actions' => '<a class="ep-icon ep-icon_pencil txt-blue fancyboxValidateModalDT fancybox.ajax" href="'.__SITE_URL.'catattr/update_forms/attribute/'.$attribute['id'].'" title="Edit Attribute" data-title="Edit Attribute"></a>'.$additional_actions.'<a class="ep-icon ep-icon_remove txt-red confirm-dialog" data-attr="'.$attribute['id'].'" data-callback="attrRemove" data-message="Are you sure you want to delete this attribute?" title="Delete Attribute"></a>',
                'dt_tlangs' => $langs_dropdown,
                'dt_tlangs_list' => implode(', ', $langs_record_list)
            );
        }

        jsonResponse('', 'success', $output);
    }

    function ajax_attr_operation(){
        if(!isAjaxRequest())
            headerRedirect();

        if(!logged_in())
            jsonResponse(translate("systmess_error_should_be_logged"));

        if(!have_right('manage_content'))
            jsonResponse(translate("systmess_error_rights_perform_this_action"));

        //print_r($_POST);
        $this->load->model('Category_Model', 'category');
        $this->load->model('Catattributes_Model', 'attributes');
        $operation = $this->uri->segment(3);

        switch($operation){
            case 'add_attr':
                if($_POST['parent'] == 0)
                    jsonResponse('Error: Category of the attribute can\'t be empty.');

                $validation_rules = array(
                    array(
                    'field' => 'parent',
                    'label' => 'Cateogory',
                    'rules' => array('required' => '')
                    ),
                    array(
                    'field' => 'attribute',
                    'label' => 'Name',
                    'rules' => array('required' => '')
                    )
                );

                $type = $_POST['type'];
                $attr_info = array(
                    'category' => (int)$_POST['parent'],
                    'attributes' => cleanInput($_POST['attribute']),
                    'attr_type' => $type,
                    'attr_req' => (int)$_POST['attr_req']
                );
                switch ($type) {
                    case 'select':
                    case 'multiselect':
                    $validation_rules[] = array(
                        'field' => 'values',
                        'label' => 'Values',
                        'rules' => array('required' => '')
                    );
                    $this->validator->set_rules($validation_rules);

                    if(!$this->validator->validate())
                        jsonResponse($this->validator->get_array_errors());

                    $values = cleanInput($_POST['values']);
                    $attr_id = $this->attributes->insert_attr($attr_info);
                    $this->attributes->insert_attr_values($attr_id, $values);
                    break;
                    case 'text':
                    case 'range':
                    $this->validator->set_rules($validation_rules);

                    if(!$this->validator->validate())
                        jsonResponse($this->validator->get_array_errors());

                    $attr_info['attr_value_type'] = (int)$_POST['vtype'];//print_r($attr_info);
                    $attr_info['attr_sample'] = cleanInput($_POST['attr_sample']);
                    $attr_id = $this->attributes->insert_attr($attr_info);
                    break;
                }

                jsonResponse('Attribute was added successfully','success');
            break;
            case 'edit_attr':
                if($_POST['parent'] == 0)
                    jsonResponse('Error: Category of the attribute can\'t be 0.');

                $validation_rules = array(
                    array(
                    'field' => 'parent',
                    'label' => 'Cateogory',
                    'rules' => array('required' => '')
                    ),
                    array(
                    'field' => 'attribute',
                    'label' => 'Name',
                    'rules' => array('required' => '')
                    )
                );

                $type = $_POST['type'];
                $attr_id = (int)$_POST['id'];
                $attr_info = array(
                    'id' => $attr_id,
                    'category' => (int)$_POST['parent'],
                    'attr_name' => filter($_POST['attribute']),
                    'attr_type' => $type,
                    'attr_req' => (int)$_POST['attr_req']
                );

                switch ($type) {
                    case 'select':
                    case 'multiselect':
                        $this->validator->set_rules($validation_rules);

                        if(!$this->validator->validate())
                            jsonResponse($this->validator->get_array_errors());

                        $values = $_POST['values'];
                        $this->attributes->update_attr($attr_info);
                        $this->attributes->insert_attr_values($attr_id, $values);
                    break;
                    case 'text':
                    case 'range':
                        $this->validator->set_rules($validation_rules);

                        if(!$this->validator->validate())
                            jsonResponse($this->validator->get_array_errors());

                        $attr_old = $this->attributes->get_attribute($attr_id);
                        $attr_info['attr_value_type'] = (int)$_POST['vtype'];//print_r($attr_info);
                        $attr_info['attr_sample'] = cleanInput($_POST['attr_sample']);

                        if(in_array($attr_old['attr_type'], array('select', 'multiselect')))
                            $this->attributes->delete_attr_values($attr_id);

                        $this->attributes->update_attr($attr_info);
                    break;
                }

                jsonResponse('Attribute was updated successfully','success');
            break;
            case 'delete_attr':
                $attr_id = intval($_POST['attr']);
                    $attr = $this->attributes->get_attribute($attr_id);
                if(in_array($attr['attr_type'], array('select', 'multislect'))){
                    $this->attributes->delete_attr_values($attr_id);
                }
                if($this->attributes->delete_attr($attr_id))
                    jsonResponse('Attribute and its values have been successfully deleted', 'success');
                else
                    jsonResponse('Error: Failed to delete attribute.');
            break;
            case 'update_i18n_attr':
                $validator_rules = array(
                    array(
                        'field' => 'attribute',
                        'label' => 'Attribute',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'lang_attr',
                        'label' => 'Language',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'attr_name',
                        'label' => 'Attribute name',
                        'rules' => array('required' => '', 'max_len[250]' => '')
                    )
                );

                $this->validator->set_rules($validator_rules);

                if(!$this->validator->validate()){
                    jsonResponse($this->validator->get_array_errors());
                }

                $id_attr = intval($_POST['attribute']);
                $lang_attr = cleanInput($_POST['lang_attr']);
                $tlang = $this->translations->get_language_by_iso2($lang_attr);
                if(empty($tlang)){
                    jsonResponse('Error: Language does not exist.');
                }

                $attribute = $this->attributes->get_attribute($id_attr);
                if(empty($attribute)){
                    jsonResponse('Error: Attribute does not exist.');
                }

                $translation_data = array_filter(json_decode($attribute['translation_data'], true));
                $translation_data[$lang_attr] = array(
                    'lang_name' => $tlang['lang_name'],
                    'attr_name' => cleanInput($_POST['attr_name'])
                );

                $update = array(
                    'id' => $id_attr,
                    'translation_data' => json_encode($translation_data)
                );
                $this->attributes->update_attr($update);
                jsonResponse('The translation has been successfully added', 'success');
            break;
            case 'delete_attr_i18n':
                $validator_rules = array(
                    array(
                        'field' => 'attr',
                        'label' => 'Attribute',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'lang_attr',
                        'label' => 'Language',
                        'rules' => array('required' => '')
                    )
                );

                $this->validator->set_rules($validator_rules);

                if(!$this->validator->validate()){
                    jsonResponse($this->validator->get_array_errors());
                }

                $id_attr = intval($_POST['attr']);
                $lang_attr = cleanInput($_POST['lang_attr']);
                $tlang = $this->translations->get_language_by_iso2($lang_attr);
                if(empty($tlang)){
                    jsonResponse('Error: Language does not exist.');
                }

                $attribute = $this->attributes->get_attribute($id_attr);
                if(empty($attribute)){
                    jsonResponse('Error: Attribute does not exist.');
                }

                $translation_data = array_filter(json_decode($attribute['translation_data'], true));
                unset($translation_data[$lang_attr]);

                $update = array(
                    'id' => $id_attr,
                    'translation_data' => json_encode($translation_data)
                );
                $this->attributes->update_attr($update);
                jsonResponse('The translation has been successfully deleted', 'success');
            break;
            case 'append_values':
                $attr_id = intval($_POST['id']);
                $values = $_POST['values'];
                if($this->attributes->insert_attr_values($attr_id, $values))
                    jsonResponse('Values have been successfully added.', 'success');
                else
                    jsonResponse('Error: Failed to append values. Please try again later.');
            break;
            case 'delete_values':
                $id = intval($_POST['val']);
                if($this->attributes->delete_attr_value($id))
                    jsonResponse('Value was deleted successfully.', 'success');
                else
                    jsonResponse('Error: Failed to delete value. Please try again later.');
            break;
            case 'update_values':
                $info['value'] = cleanInput($_POST['value']);
                $info['id'] = intval($_POST['id']);
                if($this->attributes->update_attr_value($info))
                    jsonResponse('Value has been successfully updated', 'success');
                else
                    jsonResponse('Error: Failed to update value. Please try again later.');
            break;
            case 'update_value_i18n':
                $validator_rules = array(
                    array(
                        'field' => 'id',
                        'label' => 'Value info',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'lang_value',
                        'label' => 'Language',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'value',
                        'label' => 'Value',
                        'rules' => array('required' => '', 'max_len[250]' => '')
                    )
                );

                $this->validator->set_rules($validator_rules);

                if(!$this->validator->validate()){
                    jsonResponse($this->validator->get_array_errors());
                }

                $id_value = intval($_POST['id']);
                $lang_value = cleanInput($_POST['lang_value']);
                $tlang = $this->translations->get_language_by_iso2($lang_value);
                if(empty($tlang)){
                    jsonResponse('Error: Language does not exist.');
                }

                $attr_value = $this->attributes->get_attr_value($id_value);
                if(empty($attr_value)){
                    jsonResponse('Error: Attribute value does not exist.');
                }

                $translation_data = array_filter(json_decode($attr_value['translation_data'], true));
                $translation_data[$lang_value] = array(
                    'lang_name' => $tlang['lang_name'],
                    'value' => cleanInput($_POST['value'])
                );

                $update = array(
                    'id' => $id_value,
                    'translation_data' => json_encode($translation_data)
                );
                $this->attributes->update_attr_value($update);
                jsonResponse('The translation has been successfully added', 'success');
            break;
            case 'delete_value_i18n':
                $validator_rules = array(
                    array(
                        'field' => 'id_value',
                        'label' => 'Value info',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'lang_value',
                        'label' => 'Language',
                        'rules' => array('required' => '')
                    )
                );

                $this->validator->set_rules($validator_rules);

                if(!$this->validator->validate()){
                    jsonResponse($this->validator->get_array_errors());
                }

                $id_value = intval($_POST['id_value']);
                $lang_value = cleanInput($_POST['lang_value']);
                $tlang = $this->translations->get_language_by_iso2($lang_value);
                if(empty($tlang)){
                    jsonResponse('Error: Language does not exist.');
                }

                $attr_value = $this->attributes->get_attr_value($id_value);
                if(empty($attr_value)){
                    jsonResponse('Error: Attribute value does not exist.');
                }

                $translation_data = array_filter(json_decode($attr_value['translation_data'], true));
                unset($translation_data[$lang_value]);

                $update = array(
                    'id' => $id_value,
                    'translation_data' => json_encode($translation_data)
                );
                $this->attributes->update_attr_value($update);
                jsonResponse('The translation has been successfully deleted', 'success');
            break;
            case 'get_attr_values':
                $validation_rules = array(
                    array(
                    'field' => 'attr',
                    'label' => 'Attribute',
                    'rules' => array('required' => '')
                    )
                );
                $this->validator->set_rules($validation_rules);
                if(!$this->validator->validate()){
                    jsonResponse($this->validator->get_array_errors());
                }

                $attr = (int)$_POST['attr'];
                $data['attr'] = $this->attributes->get_attribute($attr);
                $data['values'] = $this->attributes->get_attr_values($attr);
                jsonResponse('', 'success', array('content' => $this->view->fetch('admin/catattributes/values_view', $data)));
            break;
        }

    }

    function popup_forms(){
        if (!isAjaxRequest())
            headerRedirect();

        if (!logged_in())
            messageInModal(translate("systmess_error_should_be_logged"), $type = 'errors');

        checkAdmin('manage_content');

        $op = $this->uri->segment(3);
        switch ($op) {
            case 'edit_category_attr':
                $this->load->model('Category_Model', 'category');

                $data['cat'] = (int) $this->uri->segment(4);
                $data['category'] = $this->category->get_category($data['cat']);
                $data['categories'] = $this->category->getCategories(array('parent' => 0, 'columns' => 'category_id, name'));
                $data['category']['breadcrumbs'] = json_decode('['.$data['category']['breadcrumbs'].']', true);

                $this->view->display('admin/categories/form_view', $data);
            break;
            case 'add_category_attr':
                $this->load->model('Category_Model', 'category');
                $data['categories'] = $this->category->getCategories(array('parent' => 0));

                $this->view->display('admin/catattributes/modal_form_view', $data);
            break;
            case 'add_attr_i18n':
                $this->load->model('Catattributes_Model', 'attributes');
                $id_attr = (int)$this->uri->segment(4);
                $data['attribute'] = $this->attributes->get_attribute($id_attr);
				$data['tlanguages'] = $this->translations->get_languages();

                $this->view->display('admin/catattributes/modal_form_i18n_view', $data);
            break;
            case 'edit_attr_i18n':
                $this->load->model('Catattributes_Model', 'attributes');
                $id_attr = (int)$this->uri->segment(4);
                $lang_attr = $this->uri->segment(5);
                $tlang = $this->translations->get_language_by_iso2($lang_attr);
                if(empty($tlang)){
                    messageInModal('Error: Language does not exist.');
                }

                $data['attribute'] = $this->attributes->get_attribute($id_attr);
                if(empty($data['attribute'])){
                    messageInModal('Error: Attribute does not exist.');
                }

                $data['lang_attr'] = $lang_attr;

                $this->view->display('admin/catattributes/modal_form_i18n_view', $data);
            break;
            case 'add_attr_value_i18n':
                $this->load->model('Catattributes_Model', 'attributes');
                $id_value = (int)$this->uri->segment(4);
                $data['value'] = $this->attributes->get_attr_value($id_value);
                if(empty($data['value'])){
                    messageInModal('Error: Attribute value does not exist.');
                }

				$data['tlanguages'] = $this->translations->get_languages();
                $this->view->display('admin/catattributes/valupdate_modal_form_i18n_view', $data);
            break;
            case 'edit_attr_value_i18n':
                $this->load->model('Catattributes_Model', 'attributes');
                $id_value = (int)$this->uri->segment(4);
                $lang_value = $this->uri->segment(5);
                $tlang = $this->translations->get_language_by_iso2($lang_value);
                if(empty($tlang)){
                    messageInModal('Error: Language does not exist.');
                }

                $data['value'] = $this->attributes->get_attr_value($id_value);
                if(empty($data['value'])){
                    messageInModal('Error: Attribute value does not exist.');
                }

                $data['lang_value'] = $lang_value;
                $this->view->display('admin/catattributes/valupdate_modal_form_i18n_view', $data);
            break;
        }
    }
}
?>
