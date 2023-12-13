<?php
/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [15.12.2021]
 * Controller Refactoring
 */
class Compare_Controller extends TinyMVC_Controller
{
    function index(){
        if (!config('env.SHOW_COMPARE_FUNCTIONALITY')) {
            show_404();
        }

        $data = $this->_new_get_compare();
        $data['main_content'] = 'new/item/compare_new_window_view';
        $this->view->assign($data);
        $this->view->display('new/index_template_view');
    }

	function ajax_compare_items(){
		if (!isAjaxRequest()) {
            headerRedirect();
        }

        $data = $this->_new_get_compare();
        $this->view->display('new/index_template_view', $data);

	}

	private function _new_get_compare()
    {
        $this->load->model('Category_Model', 'category');
        $this->load->model('Catattributes_Model', 'catattr');
        $this->load->model('Items_Model', 'items');

        $compare_cookie = 'ep_compare';
        if (logged_in()) {
            $compare_cookie = 'user_'. id_session() .'_compare';
        }
        // $items = cleanInput($_POST['items']);
        $cookie_items = $this->cookies->getCookieParam($compare_cookie, array());
        if (!empty($cookie_items)) {
            $items = $cookie_items;
            if (!empty($items)) {
                $items = json_decode($items, true);
                $items = implode(',', $items);
            }
        }

        $data['items'] = array();

        if (!empty($items)) {
            model('elasticsearch_items')->get_items(array(
                'list_item' => explode(',', $items)
            ));
            $data['items'] = model('elasticsearch_items')->items;
            $data['companies'] = arrayByKey(
                model('company')->get_companies(array(
                    'users_list' => implode(',', array_column($data['items'], 'id_seller'))
                )),
                'id_user'
            );
        }

        if (logged_in()) {
            $saved_list = $this->items->get_items_saved(id_session());
            $data['saved_items'] = explode(',', $saved_list);
        }

        if (empty($data['items'])) {
            return array('info' => true);
        }

        $data['in_modal'] = false;
        if ($this->uri->segment(3) == 'modal') {
            $data['in_modal'] = true;
        }

        $user_attrs = $this->catattr->get_items_user_attr($items);
        $data['user_attrs'] = array();

        foreach ($user_attrs as $uattr) {
            $data['user_attrs'][$uattr['id_item']][] = '<strong>'.ucfirst($uattr['p_name']).': </strong>'.$uattr['p_value'].'<br>';
        }

        $cat_list = array();
        $data['items_list'] = array();
        foreach ($data['items'] as $item) {
            $cat_list[$item['id_cat']] = $item['id_cat'];
            $data['items_list'][] = 'it'.$item['id'];
        }

        $cat_list = implode(',', $cat_list);
        $categories = $this->category->getCategories(array('cat_list'=>$cat_list, 'columns'=>'category_id, breadcrumbs'));

        $categories_list = array();
        $data['c_parents'] = array();

        foreach ($categories as $category) {
            $cat_parents = json_decode('['.$category['breadcrumbs'].']', true);

            $cat_bread = array();
            foreach ($cat_parents as $cat) {
                $cat_id = array_keys($cat);
                if (!empty($cat_id[0])) {
                    $cat_bread[$cat_id[0]] = $cat_id[0];
                }
            }

            $categories_list = array_merge($categories_list, $cat_bread);
            $cat_key = end($cat_bread);
            if (!isset($data['c_parents'][$cat_key])) {
                foreach($cat_bread as $key => $c_item) {
                    $cat_bread[$key] = $c_item;
                }
                $data['c_parents'][$cat_key] = $cat_bread;
            }
        }

        $categories_list = implode(',',$categories_list);

        $cat_tree = $this->category->getCategories(array('cat_list' => $categories_list, 'columns' => 'category_id, parent, name, cat_type'));
        $cat_tree = $this->category->_categories_map($cat_tree);

        $data['categories'] = views()->fetch('new/categories/compare_view', ['categories' => $cat_tree]);
        $temp_cat = $cat_tree;
        $data['category_menu'] = array_shift($temp_cat);

        $data['main_categories'] = array();
        if (!empty($cat_tree)) {
            $temp_category = array_keys($cat_tree);
            $data['main_categories'] = $temp_category[0];
            $temp = array();
            foreach ($data['c_parents'] as $category_list) {
                if (array_key_exists($data['main_categories'], $category_list)) {
                    $cat_list = array_keys($category_list);
                    $temp = array_merge($temp, $cat_list);
                }
            }
            $data['main_categories'] = $temp;
        }

        $data['attrs'] = $this->catattr->get_categories_attr($categories_list);
        $attrs_values = $this->catattr->get_items_attr_values($items);
        $data['items_attr_vals'] = array();

        foreach ($attrs_values as $value) {
            $value_translations = !empty($value['translation_data']) ? array_filter(json_decode($value['translation_data'], true)) : array();
            $data['items_attr_vals'][$value['item']][$value['attr']][] = (empty($value['value']))?$value['attr_value']:((__SITE_LANG != 'en' && !empty($value_translations[__SITE_LANG]))?$value_translations[__SITE_LANG]['value']:$value['value']);
        }

        return $data;
    }
}
