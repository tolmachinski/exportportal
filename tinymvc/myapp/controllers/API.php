<?php

/**
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [15.12.2021]
 * Controller Refactoring
 */
class API_Controller extends TinyMVC_Controller {

	private function _api_response($message = 'Incorect request', $status = 'FAILED', $data = array()){
		$data['message'] = $message;
		$data['status'] = $status;
		exit(json_encode($data));
	}

	private function _check_key(){
		$this->load->model('API_Keys_Model', 'api_keys');
		if(!$this->api_keys->check_api_key($_GET['key']))
			$this->_api_response('Inexistent API Key or API Key your is disabled');
	}

	public function items(){
		$this->_check_key();

		global $tmvc;
		$main_cond['per_p'] = $tmvc->my_config['item_default_perpage'];
		$main_cond['page'] = 1;
		$main_cond['status'] = '1, 2, 3';
		$main_cond['item_columns'] = " it.id, it.title, it.id_cat, it.year, it.price, it.discount, it.final_price,
					it.currency, it.quantity, it.min_sale_q, it.unit_type,
					it.create_date, it.expire_date, it.id_seller, it.p_country, it.p_city,
					it.state, it.status, it.visible, it.offers, it.featured, it.highlight,
					it.rev_numb, it.rating, it.changed ";
		$seller_info = true;

		if(!empty($_GET['category'])){
			$id_category = intval($_GET['category']);

			$this->load->model('Category_Model', 'category');

			if(!$this->category->validate_category_id($id_category))
				$this->_api_response('Incorrect ID of category');

			$main_cond['category'] = $id_category;

			$search_categories = $id_category;
			$subcat_cond['category'] = $id_category;
			$data['category'] = $this->category->get_category($id_category);

			if(strlen($data['category']['cat_childrens']))
				$search_categories = $data['category']['cat_childrens'] . "," . $search_categories;

			$main_cond['categories_list'] = $search_categories;
		}

		if(!empty($_GET['seller_info']))
			$seller_info = intval($_GET['seller_info']);

		if(!empty($_GET['country']))
			$main_cond['country'] = intval($_GET['country']);

		if(!empty($_GET['city']))
			$main_cond['city'] = intval($_GET['city']);

		if(!empty($_GET['keywords']))
			$main_cond['keywords'] = cleanInput(cut_str($_GET['keywords']));

		if(!empty($_GET['year_from']))
			$main_cond['year_from'] = intval($_GET['year_from']);

		if(!empty($_GET['year_to']))
			$main_cond['year_to'] = intval($_GET['year_to']);

		if(!empty($_GET['price_from']))
			$main_cond['price_from'] = intval($_GET['price_from']);

		if(!empty($_GET['price_to']))
			$main_cond['price_to'] = (int)$_GET['price_to'];

		if(!empty($_GET['featured']))
			$main_cond['featured'] = 1;

		if(!empty($_GET['highlight']))
			$main_cond['highlight'] = 1;

		if(!empty($_GET['attributes'])){
			$this->load->model('Catattributes_Model', 'catattr');

			$selected_attrs =  $this->catattr->attrs_from_string($_GET['attributes']);

			// attributes
			$attrs_keys = array_keys($selected_attrs);
			$attributes = $this->catattr->get_attributes_list($attrs_keys);

			$attrs = array();
			$r_attrs = array();
			$t_attrs = array();

			foreach($attributes as $key => $attribute){
				switch ($attribute['attr_type']) {
					case 'range':
						$r_attrs[$attribute['id']] = $selected_attrs[$attribute['id']];
						break;
					case 'text':
						$t_attrs[$attribute['id']] = $selected_attrs[$attribute['id']];
						break;
					default:
						$attrs[$attribute['id']] = $selected_attrs[$attribute['id']];
						break;
				}
			}

			if(count($attrs))
				$main_cond['attrs'] = $attrs;

			if(count($r_attrs))
				$main_cond['r_attrs'] = $r_attrs;

			if(count($t_attrs))
				$main_cond['t_attrs'] = $t_attrs;
		}

		if(!empty($_GET['start']))
			$main_cond['start'] = $_GET['start'];

		if(!empty($_GET['limit']))
			$main_cond['limit'] = $_GET['limit'];

		if(!empty($_GET['sort_by']))
			$main_cond['sort_by'] = $_GET['sort_by'];

		// items
        $this->load->model('Items_Model', 'items');
		$main_cond['count'] = $data['count'] = $this->items->count_items($main_cond);
		$data['list'] = $this->items->get_items($main_cond);

		foreach($data['list'] as $item){
			if($seller_info)
				$sellers_list[$item['id_seller']] = $item['id_seller'];

			$items_list[] = $item['id'];
		}

		if($seller_info && count($sellers_list)){
			$this->load->model('User_Model', 'user');
			$sellers = arrayByKey($this->user->getSellersForList(implode(',',$sellers_list), true), 'idu');
		}

		$main_images = arrayByKey($this->items->items_main_photo(array('main_photo' => 1,'items_list' => implode(',', $items_list))), 'sale_id');

		foreach($data['list'] as $key => $item){
			if($seller_info)
				$data['list'][$key]['seller'] = $sellers[$item['id_seller']];

			$data['list'][$key]['images']['photo_name'] = $main_images[$item['id']]['photo_name'];
		}
		//echo "<pre>" .print_r($main_cond, true). print_r($data, true). "</pre>";

		$this->_api_response('OK', 'OK', $data);

	}

	public function sellers() {
		$this->_check_key();
		$this->load->model('Company_Model', 'company');

		$main_cond['per_p'] = 10;
		$main_cond['page'] = 1;
		$group_info = true;

		if (!empty($_GET['group_info']))
			$group_info = intval($_GET['group_info']);

		if (!empty($_GET['type']))
			$main_cond['type'] = intval($_GET['type']);

		if (!empty($_GET['country']))
			$main_cond['country'] = intval($uri['country']);

		if (!empty($_GET['industry']))
			$main_cond['industry'] = intval($_GET['industry']);

		if (!empty($_GET['category']))
			$main_cond['category'] = intval($_GET['category']);

		if (isset($_GET['sort_by']))
			$main_cond['sort_by'] = $_GET['sort_by'];

		if (isset($_GET['per_p']) && abs(intVal($_GET['per_p'])))
			$main_cond['per_p'] = abs(intVal($_GET['per_p']));

		if (isset($_GET['page']))
			$main_cond['page'] =  $_GET['page'];

		if(!empty($_GET['start']))
			$main_cond['start'] = $_GET['start'];

		if(!empty($_GET['limit']))
			$main_cond['limit'] = $_GET['limit'];

		if (!empty($_GET['keywords']))
			$main_cond['keywords'] = cleanInput(cut_str($_GET['keywords']));


		$main_cond['count'] = $data['count'] = $this->company->count_companies($main_cond);
		$data['list'] = $this->company->get_companies($main_cond);

		if($group_info){
			$this->load->model('UserGroup_Model', 'user_group');
			$user_group = arrayByKey($this->user_group->getGroups(array('fields' => 'gr_name, idgroup, stamp_pic')), 'idgroup');

			if (!empty($data['list'])) {
				foreach ($data['list'] as $key => $company) {
					$data['list'][$key]['group_detail'] = $user_group[$company['user_group']];
				}
			}
		}
		//echo "<pre>" .print_r($main_cond, true). print_r($data['companies_list'], true). "</pre>";
		$this->_api_response('OK', 'OK', $data);
    }


}
