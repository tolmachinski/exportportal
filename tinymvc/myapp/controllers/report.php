<?php
class Report_Controller extends TinyMVC_Controller {

	/* load main models*/
	private function _load_main(){
		$this->load->model('Category_Model', 'category');
		$this->load->model('Country_Model', 'country');
		$this->load->model('Company_Model', 'company');
		$this->load->model('User_Model', 'user');
	}

    // NEED REFACTORING
	function category_report(){
        show_404();

		$this->_load_main();
		$this->load->model('Items_Model', 'items');
		$this->load->model('Catattributes_Model', 'catattr');
		global $tmvc;
		$featured = 1;
		$id_category = intval($this->uri->segment(4));

		if(!$id_category || !$this->category->validate_category_id($id_category)){
			$this->session->setMessages(translate("systmess_error_nothing_found_at_your_request"),'errors');
			show_404();
		}

		$uri = $this->uri->uri_to_assoc();

		if(!empty($_SERVER['QUERY_STRING']))
			$data['get_params'] =  arrayToGET($_GET);

		$data['per_p'] = $main_cond['per_p'] = $tmvc->my_config['item_default_perpage'];
		$data['page'] = $main_cond['page'] = 1;
		$main_cond['status'] = '1, 2, 3';
		$main_cond['accreditation'] = 1;
        $main_cond['visible'] = 1;
        $main_cond['blocked'] = 0;
        $main_cond['main_photo'] = 1;

		if(isset($uri['country'])){
			$id_country = id_from_link($uri['country']);
			$main_cond['country'] = $id_country;
			$featured = 0;
		}

		if(isset($uri['city'])){
			$id_city = id_from_link($uri['city']);
			$main_cond['city'] = $id_city;
			$featured = 0;
		}

		if (!empty($_GET['per_p']) && abs(intVal($_GET['per_p']))) {
            $data['per_p'] = $main_cond['per_p'] = abs(intVal($_GET['per_p']));
        }

		if(isset($uri['page'])){
			$uri['page'] = (int)$uri['page'];
			$data['page'] = $main_cond['page'] = $uri['page'];
		}

		if (!empty($_GET['sort_by'])) {
			$data['sort_by_links'] = array(
				'create_date-desc' => 'create_date-desc',
				'create_date-asc' => 'create_date-asc',
				'title.sorting-asc' => 'title-asc',
				'title.sorting-desc' => 'title-desc',
				'final_price-asc' => 'final_price-asc',
				'final_price-asc' => 'final_price-asc'
			);

			if(array_key_exists($_GET['sort_by'], $data['sort_by_links'])){
				$main_cond['sort_by'][] = $data['sort_by_links'][$_GET['sort_by']];
            	$featured = 0;
			}

        }

		$search_categories = $id_category;
		$data['category'] = $this->category->get_category($id_category);

		if(strlen($data['category']['cat_childrens']))
			$search_categories = $data['category']['cat_childrens'] . "," . $search_categories;

		$main_cond['categories_list'] = $search_categories;

		$data['crumbs'] = $this->category->breadcrumbs($id_category);

		/* search params */
		if(isset($_GET['keywords']) && !empty($_GET['keywords'])){
			$data['keywords'] = $main_cond['keywords'] = cleanInput(cut_str($_GET['keywords']));
			$featured = 0;
		}

		if(isset($_GET['attributes']) && !empty($_GET['attributes'])){
			$data['search_attributes'] = $_GET['attributes'];
			$featured = 0;
		}

		if(isset($_GET['year_from']) && !empty($_GET['year_from'])){
			$data['year_from'] = $main_cond['year_from'] = (int)$_GET['year_from'];
			$featured = 0;
		}

		if(isset($_GET['year_to']) && !empty($_GET['year_to'])){
			$data['year_to'] = $main_cond['year_to'] = (int)$_GET['year_to'];
			$featured = 0;
		}

		if(isset($_GET['price_from']) && !empty($_GET['price_from'])){
			$data['price_from'] = $main_cond['price_from'] = (int)$_GET['price_from'];
			$featured = 0;
		}

		if(isset($_GET['price_to']) && !empty($_GET['price_to'])){
			$data['price_to'] = $main_cond['price_to'] = (int)$_GET['price_to'];
			$featured = 0;
		}

		if(isset($_GET['attributes']) && !empty($_GET['attributes'])){
			$main_cond['attrs']= $this->catattr->attrs_from_string($_GET['attributes']);
			//print_r($data['real_attrs']);
			$data['attr_values'] = $this->catattr->attr_values_from_list($main_cond['attrs']);
		}

		if (!empty($_GET['featured'])) {
            $data['featured'] = $main_cond['featured'] = 1;
        }

		/* locations */
		if(!isset($id_country)){
			$data['locations'] = $this->category->fetch_country_by_category($main_cond);
		}else{
			$data['locations'] = $this->category->fetch_city_by_category($main_cond);
		}

		$country_cond = array();
		if(isset($id_country)){
			$country = $this->country->get_country($id_country);
			$data['crumbs'][] = array(
				'title' => $country['country'],
			);
		}
		if(isset($id_city)){
			$city = search_in_array($data['locations'], 'loc_id', $id_city);
			$data['crumbs'][] = array(
				'title' => $city['loc_name'],
			);
		}

		if($featured != 0)
            $main_cond['featured_order'] = 1;

        $main_cond['category'] = $id_category;
        /* items */
        model('elasticsearch_items')->get_items($main_cond);
        $data['items'] = model('elasticsearch_items')->items;
        $main_cond['count'] = $data['count'] = model('elasticsearch_items')->itemsCount;

		if(empty($data['items'])){
			$this->session->setMessages(translate("systmess_error_nothing_found_at_your_request"),'errors');
			header('Location:' . $_SERVER['HTTP_REFERER']);
			exit();
		}

		foreach($data['items'] as $item){
			$sellers_list[$item['id_seller']] = $item['id_seller'];
		}

		if(count($sellers_list)){
			$sellers = $this->user->getSellersForList(implode(',',$sellers_list), true);
		}

		foreach($data['items'] as $key => $item){
			$data['items'][$key]['seller'] = search_in_array($sellers, 'idu', $item['id_seller']);
		}

		$content = $this->view->fetch('new/pdf_templates/items_report_view', $data);
		$this->load->library('mpdf','mpdf');
		$mpdf = $this->mpdf->new_pdf();
        $footer = '<table width="100%" style="border:0;">
                        <tr>
                            <td align="left" width="50%" style="padding-left:25px;">
                                <span style="font-size:14pt; color:#b5b5b5;">Export Portal Team</span>
                            </td>
                            <td align="right" width="50%" style="padding-right:25px;">
                                <span style="font-size:14pt; color:#b5b5b5;">Page {PAGENO}</span>
                            </td>
                        </tr>
                    </table>';
        $mpdf->defaultfooterline = 0;
        $mpdf->setFooter($footer);
        $mpdf->WriteHTML($content);
        $mpdf->Output($file, "I");
	}

    // NEED REFACTORING
	function search_report() {
        show_404();

        $this->load->model('Elasticsearch_Items_Model', 'elasticsearch_items');
		$this->_load_main();
		$this->load->model('Items_Model', 'items');

		$uri = $this->uri->uri_to_assoc();
		$link[] = __SITE_URL . 'search';
		$data['port_country'] = $this->country->fetch_port_country();

		$select_cond = array();
        $select_cond['_source'] = array( "photo_name", "id", "title", "country", "rating", "rev_numb", "discount", "final_price", "quantity", "id_seller");
		$data['page'] =  $select_cond['page'] = 1;
		$data['per_p'] = $select_cond['per_p'] = 10;

		if(isset($uri['per_p'])){
			$data['per_p'] =  $select_cond['per_p'] = $link['per_p'] = $uri['per_p'];
		}

		if(isset($uri['page'])){
			$data['page'] = $select_cond['page'] = $link['page'] = $uri['page'];
		}

		if(isset($uri['sort_by'])){
			$data['sort_by'] = $select_cond['sort_by'] = $link['sort_by'] = $uri['sort_by'];
		}else{
			$select_cond['featured_order'] = 1;
		}

		if(isset($uri['country'])){
			$link['country'] = $uri['country'];
			$select_cond['country'] = id_from_link($uri['country']);
		}elseif(isset($_GET['country']) && !empty($_GET['country'])){
			$exceptkeys[] = 'country';
			$link['country'] = $_GET['country'];
			$select_cond['country'] = id_from_link($_GET['country']);
		}

		if(!empty($_SERVER['QUERY_STRING'])){
			$data['get_params'] = arrayToGET($_GET, implode(',',$exceptkeys));
		}

		/* search params */
        if(empty($_GET['keywords'])) {
			$this->session->setMessages('Empty keywords','errors');
			header('Location:' . $_SERVER['HTTP_REFERER']);
			exit();
        }

        $data['keywords'] = $select_cond['keywords'] = cleanInput(cut_str($_GET['keywords']));

		$data['countries'] = $this->country->get_countries();

        $select_cond["aggregate_category_counters"] = true;
        $select_cond["aggregate_countries_counters"] = true;

        $this->elasticsearch_items->get_items($select_cond);

        $data['items'] = $this->elasticsearch_items->items;
        $data['count'] = $this->elasticsearch_items->itemsCount;

		if(empty($data['items'])){
			$this->session->setMessages(translate("systmess_error_nothing_found_at_your_request"),'errors');
			header('Location:' . $_SERVER['HTTP_REFERER']);
			exit();
		}


        $countries_counters = $this->elasticsearch_items->aggregates['countries'];
        $countries_ids = array_keys($countries_counters);
        if(!empty($countries_ids)) {
            $data['search_countries'] = array();
            $search_countries = $this->country->get_simple_countries(implode(",", $countries_ids));
            foreach($search_countries as $country) {
                $country['loc_name'] = $country['country_name'];
                $country['loc_type'] = "country";
                $country['loc_id'] = $country['id'];
                $country['loc_count'] = $countries_counters[$country['loc_id']];
                $data['search_countries'][] = $country;
            }
        }

        $categories = $this->elasticsearch_items->aggregates['categories'];
        $category_keys = "";
        $categories_counters = array();
        foreach($categories as $category => $count) {
            $explode = explode(",", $category);
            $end = end($explode);
            $category_keys .= "," . $end;
            $categories_counters[$end] = $count;
        }

        if(!empty($category_keys)) {
            $mysql_categories = $this->category->getCategories(array("cat_list" => substr($category_keys, 1)));
            foreach($mysql_categories as &$mysql_category) {
                $mysql_category['counter'] = $categories_counters[$mysql_category["category_id"]];
            }
            $data['counter_categories'] = $this->category->_categories_map($mysql_categories);
        }

		foreach($data['items'] as $product){
		   $sellers_list[$product['id_seller']] = $product['id_seller'];
		}

		if(count($sellers_list)){
			$sellers = $this->user->getSellersForList(implode(',',$sellers_list), true);
		}

		foreach($data['items'] as $key => $item){
			$data['items'][$key]['seller'] = search_in_array($sellers, 'idu', $item['id_seller']);
		}

		if(isset($_GET['keywords']) && !empty($_GET['keywords'])){
			$data['search_params'][] = array(
					'link' => __SITE_URL.'directory/all/',
					'title' => cleanInput(cut_str($_GET['keywords'])),
					'param' => 'Keywords',
				);
		}

		if(isset($uri['country'])){
			$data['search_params'][] = array(
				'link' => $data['country_link'],
				'title' => urlToStr($uri['country']),
				'param' => 'Country',
			);
		}

		$content = $this->view->fetch('new/pdf_templates/items_report_view', $data);
		$this->load->library('mpdf','mpdf');
		$mpdf = $this->mpdf->new_pdf();
        $footer = '<table width="100%" style="border:0;">
                        <tr>
                            <td align="left" width="50%" style="padding-left:25px;">
                                <span style="font-size:14pt; color:#b5b5b5;">Export Portal Team</span>
                            </td>
                            <td align="right" width="50%" style="padding-right:25px;">
                                <span style="font-size:14pt; color:#b5b5b5;">Page {PAGENO}</span>
                            </td>
                        </tr>
                    </table>';
        $mpdf->defaultfooterline = 0;
        $mpdf->setFooter($footer);
        $mpdf->WriteHTML($content);
        $mpdf->Output($file, "I");
	}

    // NEED REFACTORING
	function directory_report(){
        show_404();

		$this->_load_main();/* load main models*/
		$this->load->model('UserGroup_Model', 'user_group');
		$this->load->model('Userfeedback_Model', 'user_feedbacks');
		$main_cond = array(
			'type_company' => 'all',
			'visibility' => 1,
			'blocked' => 0
		);

		$data['per_p'] = $main_cond['per_p'] = 10;
		$data['page'] = $main_cond['page'] = 1;
		$data['sort_by'] = $main_cond['sort_by'] = 'date_desc';

		$uri = $this->uri->uri_to_assoc();
		$link[] = __SITE_URL . 'directory/all';

		if(isset($uri['type'])){
			$main_cond['type'] = id_from_link($uri['type']);
		}elseif(isset($_GET['type']) && !empty($_GET['type'])){
			$exceptkeys[] = 'type';
			$main_cond['type'] = id_from_link($_GET['type']);
		}

		if(isset($uri['industry'])){
			$main_cond['industry'] = id_from_link($uri['industry']);
		}elseif(isset($_GET['industry']) && !empty($_GET['industry'])){
			$exceptkeys[] = 'industry';
			$main_cond['industry'] = id_from_link($_GET['industry']);
		}

		if(isset($uri['category'])){
			$main_cond['category'] = id_from_link($uri['category']);
		}elseif(isset($_GET['category']) && !empty($_GET['category'])){
			$exceptkeys[] = 'category';
			$main_cond['category'] = id_from_link($_GET['category']);
		}

		if(isset($uri['country'])){
			$main_cond['country'] = id_from_link($uri['country']);
		}

		if(isset($uri['per_p'])){
			$data['per_p'] =  $main_cond['per_p'] = $uri['per_p'];
		}

		if(isset($uri['page'])){
			$data['page'] = $main_cond['page'] =  $uri['page'];
		}

		if(!empty($_SERVER['QUERY_STRING'])){
			$data['get_params'] = arrayToGET($_GET, implode(',',$exceptkeys));
		}
		/* search params */
		if(isset($_GET['keywords']) && !empty($_GET['keywords'])){
			$data['keywords'] = $main_cond['keywords'] = cleanInput(cut_str($_GET['keywords']));
		}

		if(isset($_GET['keywords']) && !empty($_GET['keywords'])){
			$data['search_params'][] = array(
					'title' => cleanInput(cut_str($_GET['keywords'])),
					'param' => 'Keywords',
				);
		}

		if (isset($_GET['sort_by'])) {
			$data['sort_by'] = $main_cond['sort_by'] = cleanInput($_GET['sort_by']);
		}

		if(isset($uri['country'])){
			$data['search_params'][] = array(
				'title' => urlToStr($uri['country']),
				'param' => 'Country',
			);
		}

		if(isset($uri['industry'])){
			$data['search_params'][] = array(
				'title' => urlToStr($uri['industry']),
				'param' => 'Industry',
			);
		}elseif(isset($_GET['industry']) && !empty($_GET['industry'])){
			$data['search_params'][] = array(
				'title' => urlToStr($_GET['industry']),
				'param' => 'Industry',
			);
		}

		if(isset($uri['category'])){
			$data['search_params'][] = array(
				'title' => urlToStr($uri['category']),
				'param' => 'Category',
			);
		}elseif(isset($_GET['category']) && !empty($_GET['category'])){
			$data['search_params'][] = array(
				'title' => urlToStr($_GET['category']),
				'param' => 'Category',
			);
		}

		if(isset($uri['type'])){
			$data['search_params'][] = array(
				'title' => urlToStr($uri['type']),
				'param' => 'Type',
			);
		}elseif(isset($_GET['type']) && !empty($_GET['type'])){
			$data['search_params'][] = array(
				'title' => urlToStr($_GET['type']),
				'param' => 'Type',
			);
		}

		$main_cond['count'] = $data['count'] = $this->company->count_companies($main_cond);
		$data['companies_list'] = $this->company->get_companies($main_cond);

		if(empty($data['companies_list'])){
			$this->session->setMessages(translate("systmess_error_nothing_found_at_your_request"),'errors');
			header('Location:' . $_SERVER['HTTP_REFERER']);
			exit();
		}
		$user_group = $this->user_group->getGroups(array('fields' => 'gr_name, idgroup, stamp_pic'));

		foreach($user_group as $group){
			$data['user_group'][$group['idgroup']] = array(
				'gr_name' => $group['gr_name'],
				'stamp_pic' => $group['stamp_pic']);
		}
		foreach($data['companies_list'] as $key => $company){

			if($company['id_state'] != 0)
				$cities_with_states[] = $company['id_city'];
			else
				$cities_without_states[] = $company['id_city'];

			if(!in_array($company['id_user'], $list_id_user_feedbacks))
				$list_id_user_feedbacks[] = $company['id_user'];
		}

		if(!empty($cities_with_states))
			$data['cities_with_states'] = $this->country->get_state_cities(implode(',',$cities_with_states));

		if(!empty($cities_without_states))
			$data['cities_without_states'] = $this->country->get_cities_by_list(implode(',',$cities_without_states));

		$data['count_feedbacks'] = $this->user_feedbacks->count_feedbacks(implode(',',$list_id_user_feedbacks));

		$content = $this->view->fetch('new/pdf_templates/directory_report_view', $data);
		$this->load->library('mpdf','mpdf');
		$mpdf = $this->mpdf->new_pdf();
        $footer = '<table width="100%" style="border:0;">
                        <tr>
                            <td align="left" width="50%" style="padding-left:25px;">
                                <span style="font-size:14pt; color:#b5b5b5;">Export Portal Team</span>
                            </td>
                            <td align="right" width="50%" style="padding-right:25px;">
                                <span style="font-size:14pt; color:#b5b5b5;">Page {PAGENO}</span>
                            </td>
                        </tr>
                    </table>';
        $mpdf->defaultfooterline = 0;
        $mpdf->setFooter($footer);
        $mpdf->WriteHTML($content);
        $mpdf->Output($file, "I");
	}

    // NEED REFACTORING
	function shippers_report(){
        show_404();

		$this->load->model('Shippers_Model', 'shippers');
		$this->load->model('Country_Model', 'country');
		$data['per_p'] = $main_cond['per_p'] = 20;
		$data['page'] = $main_cond['page'] = 1;

		$uri = $this->uri->uri_to_assoc();
		$link[] = __SITE_URL . 'shippers/directory';

		if(isset($uri['country'])){
			$main_cond['country'] = id_from_link($uri['country']);

			$data['search_params'][] = array(
				'title' => urlToStr($uri['country']),
				'param' => 'Country',
			);
		}

		if(isset($uri['sort_by'])){
            $data['sort_by'] = $uri['sort_by'];


            switch ($data['sort_by']) {
                case 'title_asc':
                    $main_cond['sort_by'][] = 'os.co_name ASC';
                break;
                case 'title_desc':
                    $main_cond['sort_by'][] = 'os.co_name DESC';
                break;
                case 'date_asc':
                    $main_cond['sort_by'][] = 'os.create_date ASC';
                break;
                case 'date_desc':
                    $main_cond['sort_by'][] = 'os.create_date DESC';
                break;
                case 'rand':
                    $main_cond['sort_by'][] = ' RAND()';
                break;
            }
		}

		if(isset($uri['per_p'])){
			$data['per_p'] =  $main_cond['per_p'] = $uri['per_p'];
		}

		if(isset($uri['page'])){
			$data['page'] = $main_cond['page'] =  $uri['page'];
		}

		if(!empty($_SERVER['QUERY_STRING'])){
			$data['get_params'] = arrayToGET($_GET);
		}
		/* search params */
		if(isset($_GET['keywords']) && !empty($_GET['keywords'])){
			$data['keywords'] = $main_cond['keywords'] = cleanInput(cut_str($_GET['keywords']));

			$data['search_params'][] = array(
					'title' => cleanInput(cut_str($_GET['keywords'])),
					'param' => 'Keywords',
				);
		}

		$main_cond['count'] = $data['count'] = $this->shippers->count_shippers_by_conditions($main_cond);
		$data['shipper_list'] = $this->shippers->get_shippers_detail_by_conditions($main_cond);

		if(empty($data['shipper_list'])){
			$this->session->setMessages(translate("systmess_error_nothing_found_at_your_request"),'errors');
			header('Location:' . $_SERVER['HTTP_REFERER']);
			exit();
		}

		foreach($data['shipper_list'] as $company){

			if($company['id_state'] != 0)
				$cities_with_states[] = $company['id_city'];
			else
				$cities_without_states[] = $company['id_city'];
		}

		if(!empty($cities_with_states))
			$data['cities_with_states'] = $this->country->get_state_cities(implode(',',$cities_with_states));

		if(!empty($cities_without_states))
			$data['cities_without_states'] = $this->country->get_cities_by_list(implode(',',$cities_without_states));

		$content = $this->view->fetch('new/pdf_templates/shippers_report_view', $data);
		$this->load->library('mpdf','mpdf');
		$mpdf = $this->mpdf->new_pdf();
        $footer = '<table width="100%" style="border:0;">
                        <tr>
                            <td align="left" width="50%" style="padding-left:25px;">
                                <span style="font-size:14pt; color:#b5b5b5;">Export Portal Team</span>
                            </td>
                            <td align="right" width="50%" style="padding-right:25px;">
                                <span style="font-size:14pt; color:#b5b5b5;">Page {PAGENO}</span>
                            </td>
                        </tr>
                    </table>';
        $mpdf->defaultfooterline = 0;
        $mpdf->setFooter($footer);
        $mpdf->WriteHTML($content);
        $mpdf->Output($file, "I");
	}
}
?>
