<?php

use App\Common\Buttons\ChatButton;
use App\Common\Contracts\Media\CompanyPhotosThumb;
use App\Common\Contracts\Media\SellerNewsPhotoThumb;
use App\Common\Contracts\Media\SellerUpdatesPhotoThumb;
use App\Common\Contracts\Media\SellerVideosPhotosThumb;
use App\Email\FeaturedCompany;
use App\Filesystem\CompanyLibraryFilePathGenerator;
use App\Filesystem\CompanyLogoFilePathGenerator;
use App\Filesystem\CompanyNewsFilePathGenerator;
use App\Filesystem\CompanyPhotosFilePathGenerator;
use App\Filesystem\CompanyUpdatesFilePathGenerator;
use App\Filesystem\CompanyVideosFilePathGenerator;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use ExportPortal\Bridge\Mailer\Mime\RefAddress;
use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;
use League\Flysystem\UnableToDeleteDirectory;
use League\Flysystem\UnableToDeleteFile;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

use const App\Moderation\Types\TYPE_COMPANY;

/**
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [15.12.2021]
 * Controller Refactoring
 */
class Directory_Controller extends TinyMVC_Controller {

	private $breadcrumbs = array();

	/* load main models */

	private function _load_main() {
		$this->load->model('Category_Model', 'category');
		$this->load->model('Country_Model', 'country');
		$this->load->model('Company_Model', 'company');
		$this->load->model('User_Model', 'user');
	}

	function index() {
		show_404();
	}

	function all() {
        show_404();
        /**
         * @author Cravciuc Andrei
         * @todo Remove [16.03.2022]
         * The business decides that we do not need to have this page in order to avoid data parsing.
        */
        /*
        global $tmvc;
		$data['directory_uri_components'] = $directory_uri_components = $tmvc->site_urls['directory/all']['replace_uri_components'];
		$uri = $this->uri->uri_to_assoc(2,$tmvc->route_url_segments);
		checkURI($uri, array($directory_uri_components['directory'], $directory_uri_components['type'], $directory_uri_components['country'], $directory_uri_components['industry'], $directory_uri_components['category'], $directory_uri_components['page']));

		$links_map = array(
			$directory_uri_components['directory'] => array(
				'type' => 'uri',
				'deny' => array($directory_uri_components['type'], $directory_uri_components['country'], $directory_uri_components['industry'], $directory_uri_components['category'], $directory_uri_components['page']),
			),
			$directory_uri_components['type'] => array(
				'type' => 'uri',
				'deny' => array('page'),
			),
			$directory_uri_components['country'] => array(
				'type' => 'uri',
				'deny' => array('page'),
			),
			$directory_uri_components['industry'] => array(
				'type' => 'uri',
				'deny' => array('page'),
			),
			$directory_uri_components['category'] => array(
				'type' => 'uri',
				'deny' => array('page'),
			),
			$directory_uri_components['page'] => array(
				'type' => 'uri',
				'deny' => array(),
			),
			'sort_by' => array(
				'type' => 'get',
				'deny' => array('page'),
			),
			'keywords' => array(
				'type' => 'get',
				'deny' => array('page'),
			)
		);

		$search_params_links_map = array(
			$directory_uri_components['directory'] => array(
				'type' => 'uri',
				'deny' => array($directory_uri_components['type'], $directory_uri_components['country'], $directory_uri_components['industry'], $directory_uri_components['category'], $directory_uri_components['page']),
			),
			$directory_uri_components['type'] => array(
				'type' => 'uri',
				'deny' => array($directory_uri_components['type'], $directory_uri_components['page']),
			),
			$directory_uri_components['country'] => array(
				'type' => 'uri',
				'deny' => array($directory_uri_components['country'], $directory_uri_components['page']),
			),
			$directory_uri_components['industry'] => array(
				'type' => 'uri',
				'deny' => array($directory_uri_components['industry'], $directory_uri_components['page']),
			),
			$directory_uri_components['category'] => array(
				'type' => 'uri',
				'deny' => array($directory_uri_components['category'], $directory_uri_components['page']),
			),
			$directory_uri_components['page'] => array(
				'type' => 'uri',
				'deny' => array($directory_uri_components['page']),
			),
			'sort_by' => array(
				'type' => 'get',
				'deny' => array('sort_by', 'page'),
			),
			'keywords' => array(
				'type' => 'get',
				'deny' => array('keywords', 'page'),
			)
		);

		$breadcrumb_params_links_map = array(
			$directory_uri_components['directory'] => array(
				'type' => 'uri',
				'deny' => array($directory_uri_components['type'], $directory_uri_components['country'], $directory_uri_components['industry'], $directory_uri_components['category'], $directory_uri_components['page']),
			),
			$directory_uri_components['type'] => array(
				'type' => 'uri',
				'deny' => array($directory_uri_components['country'], $directory_uri_components['industry'], $directory_uri_components['category'], $directory_uri_components['page']),
			),
			$directory_uri_components['country'] => array(
				'type' => 'uri',
				'deny' => array($directory_uri_components['type'], $directory_uri_components['industry'], $directory_uri_components['category'], $directory_uri_components['page']),
			),
			$directory_uri_components['industry'] => array(
				'type' => 'uri',
				'deny' => array($directory_uri_components['type'], $directory_uri_components['country'], $directory_uri_components['category'], $directory_uri_components['page']),
			),
			$directory_uri_components['category'] => array(
				'type' => 'uri',
				'deny' => array($directory_uri_components['type'], $directory_uri_components['country'], $directory_uri_components['industry'], $directory_uri_components['page']),
			),
			'sort_by' => array(
				'type' => 'get',
				'deny' => array('sort_by', 'page'),
			),
			'keywords' => array(
				'type' => 'get',
				'deny' => array('keywords', 'page'),
			)
		);

		$data['links_tpl'] = $this->uri->make_templates($links_map, $uri);
		$search_params_links_tpl = $this->uri->make_templates($search_params_links_map, $uri, true);
		$breadcrumb_params_links_tpl = $this->uri->make_templates($breadcrumb_params_links_map, $uri, true);

		$this->_load_main();
		$this->load->model('UserGroup_Model', 'user_group');
		$this->load->model('Userfeedback_Model', 'user_feedbacks');
		$this->load->model('Meta_Model', 'meta');
		$this->load->model("Followers_model", 'followers');
        $this->load->model("Elasticsearch_Company_Model", "elasticcompany");
		$main_cond = array(
			'type_company' => 'all',
			'visibility' => 1,
			'blocked' => 0
		);

		$data['per_p'] = $main_cond['per_p'] = $tmvc->my_config['directories_per_page'];
		$data['page'] = $main_cond['page'] = 1;

		$this->breadcrumbs[] = array(
			'link' => get_dynamic_url($search_params_links_tpl[$directory_uri_components['directory']]),
			'title' => 'Directory'
		);

		if (isset($uri[$directory_uri_components['type']])) {
			$main_cond['type'] = intVal(id_from_link($uri[$directory_uri_components['type']]));
			$data['type_selected'] = $uri[$directory_uri_components['type']];

			$type_info = $this->company->get_company_type($main_cond['type']);
			if(empty($type_info)){
				show_404();
			}

			$type = $type_info['name_type'];
			$data['search_params'][] = array(
				'link' => get_dynamic_url($search_params_links_tpl[$directory_uri_components['type']]),
				'title' => $type_info['name_type'],
				'param' => 'Type',
			);
			$data['meta_params']['[TYPE]'] = $type_info['name_type'];
		}

		if (isset($uri[$directory_uri_components['country']])) {
			$main_cond['country'] = intVal(id_from_link($uri[$directory_uri_components['country']]));
			$data['country_selected'] = $uri[$directory_uri_components['country']];

			$country_info = $this->country->get_country($main_cond['country']);
			if(empty($country_info)){
				show_404();
			}

			$data['search_params'][] = array(
				'link' => get_dynamic_url($search_params_links_tpl[$directory_uri_components['country']]),
				'title' => $country_info['country'],
				'param' => 'Country'
			);

			$this->breadcrumbs[] = array(
				'link' => get_dynamic_url($breadcrumb_params_links_tpl[$directory_uri_components['country']]),
				'title' => $country_info['country']
			);
			$data['meta_params']['[COUNTRY]'] = $country_info['country'];
		}

		if (isset($uri[$directory_uri_components['industry']])) {
			$main_cond['industry'] = intVal(id_from_link($uri[$directory_uri_components['industry']]));
			$data['industry_selected'] = $uri[$directory_uri_components['industry']];

			$industry_info = $this->category->get_category($main_cond['industry']);
			if(empty($industry_info)){
				show_404();
			}

			$data['search_params'][] = array(
				'link' => get_dynamic_url($search_params_links_tpl[$directory_uri_components['industry']]),
				'title' => $industry_info['name'],
				'param' => 'Industry',
			);
			$this->breadcrumbs[] = array(
				'link' => get_dynamic_url($breadcrumb_params_links_tpl[$directory_uri_components['industry']]),
				'title' => $industry_info['name']
			);
			$data['meta_params']['[INDUSTRY]'] = $industry_info['name'];
        }

        if (isset($uri[$directory_uri_components['category']])) {
            $main_cond['category'] = intVal(id_from_link($uri[$directory_uri_components['category']]));
            $data['category_selected'] = $uri[$directory_uri_components['category']];

            $category_info = $this->category->get_category($main_cond['category']);
            if(empty($category_info)){
                show_404();
            }

            $data['search_params'][] = array(
                'link' => get_dynamic_url($search_params_links_tpl[$directory_uri_components['category']]),
                'title' => $category_info['name'],
                'param' => 'Category',
            );
            $this->breadcrumbs[] = array(
                'link' => get_dynamic_url($breadcrumb_params_links_tpl[$directory_uri_components['category']]),
                'title' => $category_info['name']
            );
            $data['meta_params']['[CATEGORY]'] = $category_info['name'];
        }

		if (isset($uri[$directory_uri_components['page']])) {
			$data['page'] = $main_cond['page'] = abs((int)$uri[$directory_uri_components['page']]);
			$data['meta_params']['[PAGE]'] = $data['page'];
		}

		$data['sort_by_links'] = array(
            'items' => array(
                'date_asc' => 'Oldest',
                'date_desc' => 'Newest',
                'title_asc' => 'Title A-Z',
                'title_desc' => 'Title Z-A'
            ),
            'selected' => 'date_desc'
		);

		if (!empty($_SERVER['QUERY_STRING'])) {
			$data['get_params'] = cleanOutput(cleanInput(arrayToGET($_GET)));
            $get_parameters = $_GET;
            foreach($get_parameters as $key => $one_param){
                $get_parameters[$key] = cleanOutput(cleanInput($one_param));
            }
		}

		if (!empty($get_parameters['keywords'])) {
            $main_cond['keywords'] = cleanInput(cut_str($get_parameters['keywords']));
			$data['keywords'] = cleanOutput($get_parameters['keywords']);
			model("Search_Log_Model")->log($data['keywords']);

			$data['search_params'][] = array(
				'link' => get_dynamic_url($search_params_links_tpl['keywords']),
				'title' => $data['keywords'],
				'param' => translate('search_params_label_keywords')
			);

            $data['sort_by_links']['items']['rel-desc'] = 'Best match';
            $data['sort_by_links']['selected'] = 'rel-desc';
			$data['meta_params']['[KEYWORDS]'] = $data['keywords'];
        }

        if (!empty($get_parameters['sort_by']) && array_key_exists($get_parameters['sort_by'], $data['sort_by_links']['items']) && $get_parameters['sort_by'] != 'rel-desc') {
			$data['meta_params']['[SORT_BY]'] = $data['sort_by_links']['items'][$get_parameters['sort_by']];
			$data['sort_by_links']['selected'] = $get_parameters['sort_by'];
		}

		$main_cond['sort_by'] = $data['sort_by_links']['selected'];

		//page link
		$data['page_link'] = get_dynamic_url($search_params_links_tpl[$directory_uri_components['page']]);
		$data['report_link'] = str_replace($directory_uri_components['directory'], 'report/directory_report', replace_dynamic_uri($uri[$directory_uri_components['directory']], $data['links_tpl'][$directory_uri_components['directory']]));

		//search form link
		$data['search_form_link'] = get_dynamic_url($search_params_links_tpl[$directory_uri_components['directory']]);

		$this->elasticcompany->get_companies($main_cond);

        $companies_list = $this->elasticcompany->records;
		$data['count'] = $this->elasticcompany->count;
		$data['countries'] = array_filter($this->elasticcompany->aggregates['countries'], function ($country) {
            return $country['counter'] > 0;
        });
		$data['types'] = array_filter($this->elasticcompany->aggregates['types'], function ($type) {
            return $type['counter'] > 0;
        });
		$search_countries_ids = array_keys($data['countries']);
		$data['search_countries'] = !empty($search_countries_ids) ? model('country')->get_simple_countries(implode(',', $search_countries_ids)) : array();

		$data['industries'] = array();
		$categories_list = array_merge(array_keys($this->elasticcompany->aggregates['industries']), array_keys($this->elasticcompany->aggregates['categories']));
		if (!empty($categories_list)) {
			$categories = $this->category->getCategories(array('cat_list' => implode(',', $categories_list)));
			foreach ($categories as $key => $category) {
				if($category['parent'] == 0){
					$categories[$key]['directory_count'] = $this->elasticcompany->aggregates['industries'][$category['category_id']];
				} else{
					$categories[$key]['directory_count'] = $this->elasticcompany->aggregates['categories'][$category['category_id']];
				}
			}
			$data['industries'] = array_filter($this->category->_categories_map($categories), function ($industry) {
                return $industry['directory_count'] > 0;
            });
		}

		$user_group = $this->user_group->getGroups(array('fields' => 'gr_name, idgroup, stamp_pic'));
		foreach ($user_group as $group) {
			$data['user_group'][$group['idgroup']] = array(
				'gr_name' => $group['gr_name'],
				'stamp_pic' => $group['stamp_pic']
			);
		}

		$data['companies_list'] = [];
		if (!empty($companies_list)) {
            $list_id_user_feedbacks = array();
			foreach ($companies_list as $key => $company) {
				if (!in_array($company['id_user'], $list_id_user_feedbacks)){
					$list_id_user_feedbacks[$company['id_user']] = $company['id_user'];
                }
			}

			$data['count_feedbacks'] = $this->user_feedbacks->count_feedbacks(implode(',', $list_id_user_feedbacks));

			if (logged_in()) {
				$data['companies_list'] = array_map(
					function ($companiesItem) {
						$chatBtn = new ChatButton(['recipient' => $companiesItem['id_user'], 'recipientStatus' => $companiesItem['status']]);
						$companiesItem['btnChat'] = $chatBtn->button();
						return $companiesItem;
					},
					$companies_list
				);
			}else{
				$data['companies_list'] = $companies_list;
			}
		}

		list($data['sortby_link'],$data['sortby_link_get']) = explode('?',get_dynamic_url($search_params_links_tpl['sort_by']));

		$data['breadcrumbs'] = $this->breadcrumbs;

		$paginator_config = array(
			'prefix'		=> "{$directory_uri_components['page']}/",
            'base_url'      => $data['links_tpl'][$directory_uri_components['page']],
            'first_url'     => get_dynamic_url($search_params_links_tpl[$directory_uri_components['page']]),
            'replace_url'   => true,
            'total_rows'    => $data['count'],
            'per_page'      => $data['per_p'],
			'cur_page'		=> $data['page']
        );

		if( !$this->is_pc ){
			$paginator_config['last_link'] = false;
			$paginator_config['first_link'] = false;
		}

		$this->load->library('Pagination', 'pagination');
		$this->pagination->initialize($paginator_config);
		$data['pagination'] = $this->pagination->create_links();

		if($_GET['vr']){
			$this->cookies->setCookieParam('device_type', $_GET['vr']);
		}

        $data['followed'] = explode(',', $this->followers->get_user_followed(id_session()));

		$data['sidebar_left_content'] = 'new/directory/sidebar_view';
        $data['main_content'] = 'new/directory/search_n_view';
        $this->view->assign($data);
        $this->view->display('new/index_template_view');
        */
	}

	function ajax_company_operations(){
		if (!isAjaxRequest()) {
			headerRedirect();
		}

		if (!logged_in()) {
			jsonResponse(translate('systmess_error_should_be_logged_in'));
		}

		$this->load->model('Company_Model', 'company');
		$op = $this->uri->segment(3);

		switch($op){
			case 'remove_company_type':
				$id_type = intval($_POST['type']);

				if ($this->company->delete_company_type($id_type)) {
					jsonResponse('Company type was deleted successfully.','success');
                } else {
					jsonResponse('ERROR: This company type doesn\'t exist.');
                }
			break;
			case 'update_company_type':
				$validator = $this->validator;

				$validator_rules = array(
					array(
						'field' => 'name',
						'label' => 'Name type',
						'rules' => array('required' => '')
					)
				);

				$this->validator->set_rules($validator_rules);

				if (!$validator->validate())
					jsonResponse($validator->get_array_errors());

				$update = array('name_type' => cleanInput($_POST['name']));
				$id_type = intval($_POST['id_type']);

				if(!$this->company->exist_company_type($id_type))
					jsonResponse('Error: This type doesn\'t exist');

				if($this->company->update_company_type($id_type, $update))
					jsonResponse('The company type has been successfully updated.', 'success', array('name_type' => $update['name_type'], 'id_type' => $id_type));
				else
					jsonResponse('Error: You cannot updated this company type now. Please try again later.');
			break;
			case 'create_company_type':
				$validator = $this->validator;

				$validator_rules = array(
					array(
						'field' => 'name',
						'label' => 'Name type',
						'rules' => array('required' => '')
					)
				);

				$this->validator->set_rules($validator_rules);

				if (!$validator->validate())
					jsonResponse($validator->get_array_errors());

				$insert = array('name_type' => cleanInput($_POST['name']));
				$id_type = $this->company->set_company_type($insert);

				if($id_type)
					jsonResponse('The company type has been successfully insert.', 'success', array('name_type' => $insert['name_type'], 'id_type' => $id_type));
				else
					jsonResponse('Error: You cannot insert this company type now. Please try again later.');
			break;
			case 'remove_company_saved':
				is_allowed("freq_allowed_saved");

				$company = intval($_POST['company']);
				if (!in_array($company, $this->session->company_saved)) {
					jsonResponse(translate('systmess_error_invalid_data'));
				}

				if (!$this->company->delete_saved_company(id_session(), $company)) {
					jsonResponse(translate('systmess_internal_server_error'));
				}

				$this->session->clear_val('company_saved', $company);
				jsonResponse(translate('systmess_success_company_was_removed_from_saved'), 'success');
			break;
			case 'add_company_saved':
				is_allowed("freq_allowed_saved");

				$company = intval($_POST['company']);
				if (in_array($company, $this->session->company_saved)) {
					jsonResponse(translate('systmess_error_invalid_data'));
				}

				if (!$this->company->set_company_saved(array('user_id' => id_session(), 'company_id' => $company))) {
					jsonResponse(translate('systmess_internal_server_error'));
				}

				$this->session->__push('company_saved', $company);
				jsonResponse(translate('systmess_success_company_was_saved_in_saved'), 'success');
			break;
		}

	}

	public function ajax_get_saved() {
        checkIsAjax();
        checkIsLoggedAjax();

        $data['per_page'] = 9;
        $data['page'] = abs((int) $_POST['page']);
        $data['counter'] = 0;
        $id_companies = model(Company_Model::class)->getSavedCompanies(id_session());
        $id_companies = array_filter(array_map('intval', explode(',', $id_companies)));
        if(!empty($id_companies)){
            $elasticsearchCompanyModel = model(Elasticsearch_Company_Model::class);
            $elasticsearchCompanyModel->get_companies(array(
                'list_company_id' => implode(',', $id_companies),
                'per_p' => $data['per_page'],
                'page' => $data['page']
            ));

            $companies = $elasticsearchCompanyModel->records;

			$data['companies'] = [];
			if (!empty($companies)) {
				$data['companies'] = array_map(
					function ($companiesItem) {
						$chatBtn = new ChatButton(['recipient' => $companiesItem['id_user'], 'recipientStatus' => $companiesItem['status']]);
						$companiesItem['btnChat'] = $chatBtn->button();
						return $companiesItem;
					},
					$companies
				);
			}

            $data['counter'] = $elasticsearchCompanyModel->count;
        }

		$content = $this->view->fetch('new/nav_header/saved/directory_saved_list_view', $data);

		jsonResponse($content, 'success', array('counter' => $data['counter']));
	}

	function administration_types() {
		checkAdmin('directory_administration');

		$this->_load_main();

		$data = $this->session->getMessages();

		$data['type_list'] = $this->company->get_company_types();

		$this->view->assign('title', 'company type');
		$this->view->assign($data);
		$this->view->display('admin/header_view');
		$this->view->display('admin/directory/type/index_view');
		$this->view->display('admin/footer_view');
	}

	function administration_industries() {
		checkAdmin('directory_administration');

		$this->_load_main();

		$data = $this->session->getMessages();

		$data['industry_list'] = $this->company->get_company_industries();

		$this->view->assign('title', 'company industries');
		$this->view->assign($data);
		$this->view->display('admin/header_view');
		$this->view->display('admin/directory/industries/index_view');
		$this->view->display('admin/footer_view');
	}

	function ajax_company_industry_operation() {
		if(!isAjaxRequest())
			headerRedirect();

		if(!logged_in())
			jsonResponse(translate("systmess_error_should_be_logged"));

		$this->load->model('Company_Model', 'company');
		$op = $this->uri->segment(3);

		switch($op){
			case 'remove_company_industry':
				$this->load->model('Category_Model', 'category');

				$id_category = intval($_POST['category']);

				if ($this->category->delete_company_category_seo($id_category))
					jsonResponse('Company industry type was removed successfully.','success');
				else
					jsonResponse('Error: You cannot removed this directory industry now. Please try again later.');
			break;
			case 'create_company_industry':
				$validator_rules = array(
					array(
						'field' => 'industry',
						'label' => 'Name industry',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'h1',
						'label' => 'H1',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'keywords',
						'label' => 'Keywords industry',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'description',
						'label' => 'Description industry',
						'rules' => array('required' => '')
					),
				);

				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate())
					jsonResponse($this->validator->get_array_errors());

				$this->load->model('Category_Model', 'category');

				$insert = array(
					'id_category' => intval($_POST['industry']),
					'h1_category' => cleanInput($_POST['h1']),
					'keywords_category' => cleanInput($_POST['keywords']),
					'description_category' => cleanInput($_POST['description']),
				);

				$this->category->set_company_category_seo($insert);
				$category_name = $this->category->get_category($insert['id_category'], 'name');
				$insert['category_name'] = $category_name['name'];

				jsonResponse('Company industry was deleted successfully.','success',$insert);
			break;
			case 'update_company_industry':
				$validator_rules = array(
					array(
						'field' => 'h1',
						'label' => 'H1',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'keywords',
						'label' => 'Keywords industry',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'description',
						'label' => 'Description industry',
						'rules' => array('required' => '')
					),
				);

				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate())
					jsonResponse($this->validator->get_array_errors());

				$id_industry = intval($_POST['id_category']);

				$update = array(
					'h1_category' => cleanInput($_POST['h1']),
					'keywords_category' => cleanInput($_POST['keywords']),
					'description_category' => cleanInput($_POST['description']),
				);

				if (!$this->company->exist_company_industry($id_industry))
					jsonResponse('Error: This industry doesn\'t exist');

				if($this->company->update_company_industry($id_industry, $update)){
					$update['id_category'] = $id_industry;
					jsonResponse('The company industry has been successfully updated.','success',$update);
				}else
					jsonResponse('Error: You cannot updated this company industry now. Please try again later.');
			break;
		}
	}

	function administration_categories() {
		checkAdmin('directory_administration');

		$this->_load_main();

		$data['category_list'] = $this->company->get_company_categories();

		$this->view->assign('title', 'company categories');
		$this->view->assign($data);
		$this->view->display('admin/header_view');
		$this->view->display('admin/directory/categories/index_view');
		$this->view->display('admin/footer_view');
	}

	function ajax_company_category_operation() {
		if(!isAjaxRequest())
			headerRedirect();

		if(!logged_in())
			jsonResponse(translate("systmess_error_should_be_logged"));

		$this->load->model('Company_Model', 'company');
		$op = $this->uri->segment(3);

		switch($op){
			case 'remove_company_category':
				$this->load->model('Category_Model', 'category');

				$id_category = intval($_POST['category']);

				if ($this->category->delete_company_category_seo($id_category))
					jsonResponse('Company category type was removed successfully.','success');
				else
					jsonResponse('Error: You cannot removed this directory category now. Please try again later.');
			break;
			case 'update_company_category':
				$validator_rules = array(
					array(
						'field' => 'h1',
						'label' => 'H1',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'keywords',
						'label' => 'Keywords category',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'description',
						'label' => 'Description category',
						'rules' => array('required' => '')
					),
				);

				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate())
					jsonResponse($this->validator->get_array_errors());

				$update = array(
					'h1_category' => cleanInput($_POST['h1']),
					'keywords_category' => cleanInput($_POST['keywords']),
					'description_category' => cleanInput($_POST['description']),
				);

				$id_category = intval($_POST['id_category']);

				if (!$this->company->exist_company_category($id_category))
					jsonResponse('Error: This category doesn\'t exist');

				if($this->company->update_company_category($id_category, $update)){
					$update['id_category'] = $id_category;
					jsonResponse('The company category has been successfully updated.', 'success',$update);
				}else
					jsonResponse('Error: You cannot updated this company category now. Please try again later.');
			break;
			case 'create_company_category':
				$validator_rules = array(
					array(
						'field' => 'category',
						'label' => 'Name category',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'h1',
						'label' => 'H1',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'keywords',
						'label' => 'Keywords category',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'description',
						'label' => 'Description category',
						'rules' => array('required' => '')
					),
				);

				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate())
					jsonResponse($this->validator->get_array_errors());

				$this->load->model('Category_Model', 'category');

				$insert = array(
					'id_category' => intval($_POST['category']),
					'h1_category' => cleanInput($_POST['h1']),
					'keywords_category' => cleanInput($_POST['keywords']),
					'description_category' => cleanInput($_POST['description']),
				);

				$this->category->set_company_category_seo($insert);
				$category_name = $this->category->get_category($insert['id_category'], 'name');
				$insert['category_name'] = $category_name['name'];

				jsonResponse('Company category was inserted successfully.','success',$insert);
			break;
			case 'get_categories_by_industry':
				$this->load->model('Category_Model', 'category');
				$industry = intval($_POST['industry']);
				$html = '<option value="0">Select category</option>';
				$categories = $this->category->getCategories(array('parent' => $industry));

				if (!empty($categories)) {

					$categories_seo = $this->company->get_simple_company_categories();
					$categories_seo_id = array();

					foreach($categories_seo as $item)
						$categories_seo_id[] = $item['id_category'];

					foreach ($categories as $category) {
						if(!in_array($category['category_id'],$categories_seo_id))
							$html .= '<option value="' . $category['category_id'] . '">' . $category['name'] . '</option>';
					}
				} else {
					$html = '<option value="0">No categories for this industry</option>';
				}
				echo $html;
			break;
		}
	}

	function ajax_company_category() {
		if (!isAjaxRequest())
			headerRedirect();

		if (!logged_in())
			jsonResponse(translate("systmess_error_should_be_logged"));

		$list_industries_selected = intval($_POST['industries']);
		if(!isset($list_industries_selected))
			jsonResponse(translate('systmess_company_info_select_categories_validation'));

		$this->load->model('Category_Model', 'category');
		$this->load->model('Company_Model', 'company');

		$data['all_options'] = '';
		$data['selected_options'] = '';

		$columns = "category_id, name, parent, p_or_m";
		$industries = $this->category->getCategories(
			array(
				'cat_list' => $list_industries_selected,
				'columns' => $columns
			)
		);

		if($_POST['company_edit'] == 1)
			$categories = $this->category->getCategories( array( 'parent' => $list_industries_selected, 'columns' => $columns ) );
		else
			$categories = $this->company->get_company_industry_categories(array('parent' => $list_industries_selected));

		$categories_all = array();
		foreach($categories as $categories_item){
			$categories_all[$categories_item['parent']][] = $categories_item;
		}

		$html = '';

		foreach ($industries as $industry) {
			$html .= '<li class="group-b" data-value="' . $industry['category_id'] . '"><div class="ttl-b">' . $industry['name'] . ' <i class="ep-icon ep-icon_arrows-right"></i></div>';
			$html .= '<ul>';

			if(!empty($categories)){
				$no_categories = 0;

				foreach ($categories_all[$industry['category_id']] as $category) {
					$no_categories++;
					$html .= '<li data-value="' . $category['category_id'] . '"><span>' . $category['name'] . '</span> <i class="ep-icon ep-icon_arrows-right"></i></li>';
				}

				if(!$no_categories){
					$html .= '<li data-value="' . $industry['category_id'] . '"><span>' . $industry['name'] . '</span> <i class="ep-icon ep-icon_arrows-right"></i></li>';
				}
			}else{
				$html .= '<li data-value="' . $industry['category_id'] . '"><span>' . $industry['name'] . '</span> <i class="ep-icon ep-icon_arrows-right"></i></li>';
			}

			$html .= '</ul></li>';
		}

		if ($html == '') {
			$html = '<li value="" disabled="disabled">No categories for this industry</li>';
		}

		jsonResponse('', 'success', array('categories' => $html));
	}

	function ajax_company_category_new() {
		if (!isAjaxRequest())
			headerRedirect();

		$industry = intval($_POST['industry']);
		if(!isset($industry))
			jsonResponse(translate('systmess_company_info_select_categories_validation'));

		$this->load->model('Category_Model', 'category');
		$categories = $this->category->getCategories( array( 'parent' => $industry, 'columns' => "category_id, name, parent, p_or_m" ) );
		$categories_count = $this->category->getCategoriesCounter( array( 'parent' => $industry ) );

		jsonResponse('', 'success', array('categories' => $categories, 'categories_count' => $categories_count));
	}

	function administration() {
        checkAdmin('moderate_content');

        $this->_load_main();
		$data['last_companies_id'] = $this->company->get_companies_last_id();
		$data['type_search'] = $this->company->count_types();
		$data['industry_search'] = array_column($this->company->count_industry(), 'name', 'id_industry');
        $data['category_search'] = arrayByKey($this->company->count_category(), 'parent', true);

		$this->view->assign($data);
		$this->view->assign('title', 'Companies');
		$this->view->display('admin/header_view');
		$this->view->display('admin/directory/directory_list_view');
		$this->view->display('admin/footer_view');
	}

	function ajax_list_directory_dt() {
        checkIsAjax();
        checkIsLoggedAjaxDT();
        checkPermisionAjaxDT('moderate_content');

        $conditions = array_merge(
            dtConditions($_POST, [
                ['as' => 'search_by_username_email',    'key' => 'search_by_username_email',    'type' => 'cleanInput'],
                ['as' => 'search_by_item',              'key' => 'search_by_item',              'type' => 'cleanInput'],
                ['as' => 'accreditation',               'key' => 'accreditation',               'type' => 'cleanInput'],
                ['as' => 'added_finish',                'key' => 'finish_date',                 'type' => 'getDateFormat:m/d/Y,Y-m-d'],
                ['as' => 'company_name',                'key' => 'company_name',                'type' => 'cleanInput'],
                ['as' => 'user_status',                 'key' => 'user_status',                 'type' => 'cleanInput'],
                ['as' => 'added_start',                 'key' => 'start_date',                  'type' => 'getDateFormat:m/d/Y,Y-m-d'],
                ['as' => 'be_featured',                 'key' => 'be_featured',                 'type' => 'int'],
                ['as' => 'fake_user',                   'key' => 'fake_user',                   'type' => 'int'],
                ['as' => 'featured',                    'key' => 'featured',                    'type' => 'int'],
                ['as' => 'category',                    'key' => 'category',                    'type' => 'int'],
                ['as' => 'industry',                    'key' => 'industry',                    'type' => 'int'],
                ['as' => 'keywords',                    'key' => 'keywords',                    'type' => 'cleanInput'],
                ['as' => 'country',                     'key' => 'country',                     'type' => 'int'],
                ['as' => 'blocked',                     'key' => 'blocked',                     'type' => 'int'],
                ['as' => 'seller',                      'key' => 'id_seller',                   'type' => 'int'],
                ['as' => 'state',                       'key' => 'state',                       'type' => 'int'],
                ['as' => 'type',                        'key' => 'type',                        'type' => 'int'],
            ]),
            [
               'get_administration_info'    => 1,
               'start'                      => (int) $_POST['iDisplayStart'],
               'limit'                      => (int) $_POST['iDisplayLength'],
               'multiple_sort_by'           => flat_dt_ordering($_POST, [
                    'company'    => 'name_company',
                    'type'       => 'name_type',
                    'seller'     => 'id_user',
                    'country'    => 'country',
                    'state'      => 'state',
                    'city'       => 'city',
                    'registered' => 'registered_company',
                    'rating'     => 'rating_company'
                ]),
            ],
        );

        // if the country has no states we should search both by id_city and id_state should be 0
		if (!empty($_POST['city'])) {
			$conditions['city'] = intval($_POST['city']);
			$conditions['state'] = 0;
		}

		// if the country has states we should search both by id_city and id_state
		if (!empty($_POST['city_state'])) {
			$value = explode('-', cleanInput($_POST['city_state']));
			$conditions['city'] = intval($value[0]);
			$conditions['state'] = intval($value[1]);
		}

		if (!empty($_POST['type_company'])) {
			$conditions['type_company'] = "company";
			if (intval($_POST['type_company']) == 2) {
				$conditions['type_company'] = "branch";
            }
		} else {
			$conditions['type_company'] = "all";
		}

		if (isset($_POST['visibility_company'])) {
			$conditions['visibility'] = intval($_POST['visibility_company']);
			if (intval($_POST['visibility_company']) == 2) {
				$conditions['visibility'] = 0;
            }
        }

        /** @var Company_Model $companyModel */
        $companyModel = model(Company_Model::class);

		$conditions['count'] = $records_total = $companyModel->count_companies($conditions);
		$companies_list = $companyModel->get_companies($conditions);

		$output = [
            'iTotalDisplayRecords'  => $records_total,
			'iTotalRecords'         => $records_total,
			'aaData'                => [],
			'sEcho'                 => (int) $_POST['sEcho'],
        ];

		if (empty($companies_list)) {
			jsonResponse('', 'success', $output);
        }

        $libPhoneUtils = PhoneNumberUtil::getInstance();

		foreach ($companies_list as $company) {
			// $edit_action = '<a class="ep-icon ep-icon_pencil fancybox.ajax fancyboxValidateModalDT" title="Edit company" href="' . __SITE_URL . 'directory/popup_forms/edit_company/' . $company['id_company'] . '" data-title="Edit company"></a>';

            /** @var Complete_Profile_Model $completeProfileModel */
            $completeProfileModel = model(Complete_Profile_Model::class);

			$profile_options = $completeProfileModel->get_user_profile_options($company['id_user']);
			$profile_status = [
                'registration_info' => '<div><span class="ep-icon ep-icon_ok-circle txt-green"></span> Registration info (20%)</div>'
            ];

            $total_completed_questions = 1;
            $total_questions = count($profile_options) + 1;
            $total_completed = 20;

			if(!empty($profile_options)){
				foreach ($profile_options as $profile_option) {
					$profile_status[$profile_option['option_alias']] = '<div><span class="ep-icon '.(($profile_option['option_completed'] == 1)?'ep-icon_ok-circle txt-green':'ep-icon_minus-circle txt-red').'"></span> '. $profile_option['option_name'] .'</div>';

					if($profile_option['option_completed'] == 1){
						$total_completed += (int) $profile_option['option_percent'];
						$total_completed_questions += 1;
					}
				}
			}

			$logo = '<div class="img-list-b relative-b w-170 h-140 display-tc vam">'
					. '<img class="mw-140" src="' . __IMG_URL . 'public/img/no_image/no-image-80x80.png' . '" alt="image"/></div>';

			$company_type_id = 1;
            $social_links = '-';

            $editCompanyNameButton = sprintf(
                <<<EDIT_COMANY_NAME_BUTTON
                <a href="%s"
                    class="ep-icon ep-icon_file-edit fancybox.ajax fancyboxValidateModalDT"
                    title="Edit company name"
                    data-title="Edit company name">
                </a>
                EDIT_COMANY_NAME_BUTTON,
                getUrlForGroup("/company/popup_forms/edit_company_name/{$company['id_company']}?type=seller")
            );

			$visible = '<a class="ep-icon ep-icon_visible confirm-dialog" data-callback="change_visibility" data-state="visible" title="Set company invisible" data-id="' . $company['id_company'] . '" data-message="Are you sure want to change visibility?"></a>';

			$type = '<div class="tal">'
					. '<a class="ep-icon ep-icon_filter txt-green dt_filter" data-value-text="' . $company['name_type'] . '" data-value="' . $company['id_type'] . '" data-title="Type" data-name="type" title="Filter by type"></a>'
					. "</div>" .
					"<span>" . $company['name_type'] . "</span>";

			$city = '<a class="pull-left lh-24 dt_filter" data-value-text="' . $company['city'] . '" data-value="' . $company['id_city'] . '" data-title="City" data-name="city" title="Filter by: ' . $company['city'] . '">'
					. "<span>" . $company['city'] . "</span></a>";

			// if the country has states we should search both by id_city and id_state
			if ($company['id_state'] != 0) {
				$city = '<a class="pull-left lh-24 dt_filter" data-value-text="' . $company['city'] . '" data-value="' . $company['id_city'] . "-" . $company['id_state'] . '" data-title="City" data-name="city_state" title="Filter by: ' . $company['city'] . '">' . "<span>" . $company['city'] . "</span></a>";
            }

			if ($company['type_company'] == "branch") {
				$company_type_id = 2;
            }

			$type_company = '<a class="pull-left dt_filter" data-name="type_company" href="#" data-title="Company type" data-value="' . $company_type_id . '" data-value-text="' . $company['type_company'] . '"><p>' . ucfirst($company['type_company']) . '</p></a>';

			if (!($company['visible_company'])) {
				$visible = '<a class="ep-icon ep-icon_invisible confirm-dialog" data-callback="change_visibility" data-state="invisible" title="Set company visible" data-id="' . $company['id_company'] . '" data-message="Are you sure want to change visibility?"></a>';
            }

            $block_company = null;
            $block_button_type = TYPE_COMPANY;
            if($company['blocked'] == 0){
                $block_button_url = __SITE_URL . "moderation/popup_modals/block/{$block_button_type}/{$company['id_company']}";
                $block_company = "
                    <a class=\"dropdown-item fancybox.ajax fancyboxValidateModal\"
                        data-fancybox-href=\"{$block_button_url}\"
                        data-title=\"Block company\"
                        title=\"Block company\">
                        <i class=\"ep-icon ep-icon_locked txt-red\"></i>
                    </a>
                ";
			} elseif($company['blocked'] == 1){
                $unblock_button_url = __SITE_URL . "moderation/ajax_operations/unblock/{$block_button_type}/{$company['id_company']}";
                $block_company = "
                    <a class=\"dropdown-item confirm-dialog\"
                        title=\"Unblock company\"
                        data-url=\"{$unblock_button_url}\"
                        data-type=\"{$block_button_type}\"
                        data-message=\"Do you really want to unblock this company?\"
                        data-callback=\"unblockResource\"
                        data-resource=\"{$company['id_company']}\">
                        <i class=\"ep-icon ep-icon_unlocked txt-green\"></i>
                    </a>
                ";
			}

			$featured_company_btn = null;
			if ($company['is_featured']) {
				$featured_company_btn = "
					<a class=\"confirm-dialog\"
						title=\"Set as non-featured\"
						data-callback=\"change_featured_status\"
						data-message=\"Do you really want to take off this company from featured?\"
						data-id-company=\"{$company['id_company']}\"
						data-current-state=\"{$company['is_featured']}\">
						<i class=\"ep-icon ep-icon_arrow-line-up txt-orange\"></i>
					</a>
				";
			} elseif (!$company['fake_user'] && $company['is_verified'] && $company['visible_company'] && !$company['blocked'] && $company['type_company'] === 'company') {

                $featured_company_btn = "
					<a class=\"confirm-dialog\"
						title=\"Set as featured\"
						data-callback=\"change_featured_status\"
						data-message=\"Do you really want to set this company as featured?\"
						data-id-company=\"{$company['id_company']}\"
						data-current-state=\"{$company['is_featured']}\">
						<i class=\"ep-icon ep-icon_arrow-line-up txt-green\"></i>
					</a>
				";
			}

            $pickOfTheMonthAddPopup = '';
            if(have_right('manage_picks_of_the_month')
                && !$company['fake_user']
                && $company['visible_company']
                && !$company['blocked']
                && $company['type_company'] === 'company'
                && filter_var($company['is_verified'] ?? false, FILTER_VALIDATE_BOOL))
            {
                $pickOfTheMonthAddPopup = sprintf(
                    <<<PICK_OF_THE_MONTH_BUTTON
                    <a href="%s"
                        class="ep-icon ep-icon_star fancybox.ajax fancyboxValidateModalDT"
                        title="Make pick of the month"
                        data-title="Make pick of the month">
                    </a>
                    PICK_OF_THE_MONTH_BUTTON,
                    getUrlForGroup("/directory/popup_forms/pick_of_the_month/{$company['id_company']}")
                );
            }

			$fake_user = '<a class="ep-icon ep-icon_minus-circle txt-red" title="' . translate('ep_administration_demo_user_text', null, true) . '"></a>';
            if ($company['fake_user'] == 0){
                $fake_user = '<a class="ep-icon ep-icon_smile txt-green" title="Real user"></a>';
            }

			if (!empty($company['logo_company'])) {
				$logo_url = getDisplayImageLink(array('{ID}' => $company['id_company'], '{FILE_NAME}' => $company['logo_company']), 'companies.main', array( 'thumb_size' => 1 ));

				$logo = '<div class="img-list-b relative-b w-170 h-140 display-tc vam">'
							. '<img class="mw-140 tac" src="' . $logo_url . '"/>'
							. '<a class="delete_logo ep-icon ep-icon_remove txt-red confirm-dialog '. 'absolute-b pos-r0 m-0 bg-white" data-callback="delete_logo" data-message="Are you sure want to delete company photo?" data-id="' . $company['id_company'] . '"></a>
						</div>';
            }

			$accreditation_status = '<a class="ep-icon ep-icon_remove txt-red fs-14 dt_filter" data-value-text="No" data-value="0" data-title="Accreditation" data-name="accreditation" title="Not received accreditation"></a>';
			if($company['accreditation'] == 1) {
				$accreditation_status = '<a class="ep-icon ep-icon_ok txt-green fs-14 dt_filter" data-value-text="Yes" data-value="1" data-title="Accreditation" data-name="accreditation" title="Received accreditation"></a>';
			} elseif($company['accreditation'] == 2) {
				$accreditation_status = '<a class="ep-icon ep-icon_hourglass-processing txt-orange fs-14 dt_filter" data-value-text="Process" data-value="2" data-title="Accreditation" data-name="accreditation" title="Received accreditation in process"></a>';
			}

            $company_logo = getDisplayImageLink(array('{ID}' => $company['id_company'], '{FILE_NAME}' => $company['logo_company']), 'companies.main', array( 'thumb_size' => 0 ));

            $company_legal_name = !empty($company['legal_name_company']) ? $company['legal_name_company'] : " - ";

            //TODO: admin chat hidden
            $btnChatUser = new ChatButton(['hide' => true, 'recipient' => $company['id_user'], 'recipientStatus' => $company['status']], ['classes' => 'btn-chat-now', 'text' => '']);
            $btnChat = $btnChatUser->button();

            try {
                //try to convert phone number to the international format
                $phoneNumber = $libPhoneUtils->parse("{$company['phone_code_company']} {$company['phone_company']}");
                $companyPhone = $libPhoneUtils->format($phoneNumber, PhoneNumberFormat::INTERNATIONAL);
            } catch (NumberParseException $e) {
                $companyPhone = "{$company['phone_code_company']} {$company['phone_company']}";
            }

			$output['aaData'][] = array(
				'id' => $company['id_company'] . '<input type="checkbox" class="check-company pull-left" data-id-company="' .$company['id_company'] . '">
					</br><a rel="company_details" title="View details" class="ep-icon ep-icon_plus"></a>',
				"company" => '
							<div style="background:url('.$company_logo.') no-repeat 0 center;background-size:70px auto;padding-left:80px;">
								<div>
									<a class="ep-icon ep-icon_building" title="View company\'s profile" href="' . getCompanyURL($company) . '"></a>
									<a class="ep-icon ep-icon_video fancybox.ajax fancybox" data-title="View company video" title="View company video" href="' . __SITE_URL . 'directory/popup_forms/video_company/' . $company['id_company'] . '"></a>
									<a class="ep-icon ep-icon_visible fancybox.ajax fancybox" title="View details about this company" href="' . __SITE_URL . 'directory/popup_forms/company_details/' . $company['id_user'] . '" data-title="View details about company '.$company['name_company'].'"></a>
									<a class="ep-icon ep-icon_statistic fancybox.ajax fancybox" title="View company statistic" href="' . __SITE_URL . 'directory/popup_forms/company_statistic/' . $company['id_company'] . '" data-title="View company\'s '.$company['name_company'].' statistic"></a>
								</div>
                                <div class="clearfix"><strong class="pull-left lh-16 pr-5">Display Name: </strong> ' . $company['name_company'] . '</div>
                                <div class="clearfix"><strong class="pull-left lh-16 pr-5">Legal Name: </strong> ' . $company_legal_name . '</div>
								<div class="clearfix"><strong class="pull-left lh-16 pr-5">Company type: </strong>' . $type_company . '</div>
								<div class="clearfix"><strong class="pull-left lh-16 pr-5">Accreditation: </strong>' . $accreditation_status . '</div>
							</div>',
				'type' => $type,
				'seller' =>
					'<div class="tal">'
						. '<a class="ep-icon ep-icon_filter txt-green dt_filter" title="Filter by seller" data-value-text="' . $company['user_name'] . '" data-value="' . $company['id_user'] . '" data-title="Seller" data-name="id_seller"></a>'
						. '<a class="ep-icon ep-icon_user" title="View seller\'s profile" href="' . __SITE_URL . 'usr/' . strForURL($company['user_name']) . '-' . $company['id_user'] . '"' . '></a>'
						. $btnChat
						. '<a class="ep-icon ep-icon_envelope-send txt-green fancyboxValidateModalDT fancybox.ajax" href="'.__SITE_URL.'users/popup_forms/send_multi_email/'.$company['id_user'].'" data-title="Email '. $company['user_name'] .' using template" title="Email '. $company['user_name'] .' using template"></a>'
						. $fake_user
					. "</div>"
					. "<span>" . $company['user_name'] . "</br> <span title='Date of registration'> (" . formatDate($company['registration_date'], 'm/d/Y') . ")</span></span>",
				'country' => '<a class="pull-left dt_filter" data-value-text="' . $company['country'] . '" data-value="' . $company['id_country'] . '" data-title="Country" data-name="country">'
					. '<img class="mr-5" width="24" height="24" src="' . getCountryFlag($company['country']) . '" title="Filter by: ' . $company['country'] . '" alt="' . $company['country'] . '"/></a>'
					. $city,
				'registered' => formatDate($company['registered_company']),
				'rating' => $company['rating_company'],
                'actions' => implode('', [
                    // '<a class="ep-icon ep-icon_pencil fancybox.ajax fancyboxValidateModalDT" title="Edit company" href="' . __SITE_URL . 'directory/popup_forms/edit_company/' . $company['id_company'] . '" data-title="Edit company"></a>',
                    $editCompanyNameButton,
                    $visible,
                    $block_company,
                    $featured_company_btn,
                    $pickOfTheMonthAddPopup
                ]),
				'logo' => $logo,
				'email' => $company['email_company'],
				'phone' => $companyPhone,
				'fax' => $company['fax_company'],
				'longitude' => $company['longitude'],
				'latitude' => $company['latitude'],
				'address' => $company['address_company'],
				'zip' => $company['zip_company'],
				'description' => $company['description_company'],
				'employees' => $company['employees_company'],
				'revenue' => $company['revenue_company'],
				'add_rating' =>
					' <input class="rating-bootstrap" data-filled="ep-icon ep-icon_star txt-orange fs-15 m-0" data-empty="ep-icon ep-icon_star-empty txt-orange fs-15 m-0" type="hidden" name="val" value="' . $company['rating_company'] . '" data-readonly>
					<span class="rating-bootstrap-status txt-green fs-14 lh-15 display-ib"></span>',
				'social' => $social_links,
				'profile_completion' => (!empty($profile_options))?implode('', $profile_status):'&mdash;',
				'profile_completion_percent' => (!empty($profile_options))?'<div>Completed: '.$total_completed.'%</div><div>Questions completed: '.$total_completed_questions.' of '.$total_questions:'&mdash;'
			);
		}

		jsonResponse('', 'success', $output);
	}

	function ajax_company_branch_delete_video() {
		if (!isAjaxRequest()) {
			show_404();
        }

		if (!logged_in()) {
			jsonDTResponse(translate("systmess_error_should_be_logged"));
        }

		if (!have_right('moderate_content')) {
			jsonDTResponse(translate("systmess_error_rights_perform_this_action"));
        }

		$id = intval($_POST['id']);

		$this->_load_main();

		if ($this->company->update_company($id, array('video_company' => ''))) {
			jsonResponse('The video has been successfully deleted', 'success');
        } else {
			jsonResponse('Error: The video wasn\'t  deleted');
        }
	}

	function ajax_company_branch_delete_logo() {
		if (!isAjaxRequest()) {
			show_404();
        }

		if (!logged_in()) {
			jsonDTResponse(translate("systmess_error_should_be_logged"));
        }

		if (!have_right('moderate_content')) {
			jsonDTResponse(translate("systmess_error_page_permision"));
        }

		$id = intval($_POST['id']);

		$this->_load_main();
        /** @var FilesystemProviderInterface  $storageProvider */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $publicDisk = $storageProvider->storage('public.storage');

        if (!empty($id)) {
			try{
                $publicDisk->deleteDirectory(CompanyLogoFilePathGenerator::logoFolder($id));
            } catch (UnableToDeleteDirectory $e){
                jsonResponse('Error: Couldn\'t delete file');
            }
        }

		if ($this->company->update_company($id, array('logo_company' => ''))) {
			jsonResponse('The logo has been successfully deleted', 'success');
		} else {
			jsonResponse('Error: The logo wasn\'t  deleted');
        }
	}

	function popup_forms() {
		if (!isAjaxRequest()) {
			show_404();
        }

		if (!logged_in()) {
			messageInModal(translate("systmess_error_should_be_logged"));
        }

		if (!have_right('moderate_content')) {
			messageInModal(translate("systmess_error_rights_perform_this_action"));
        }

		$action = cleanInput($this->uri->segment(3));
		$company_id = intval($this->uri->segment(4));
		$this->_load_main();

		switch ($action) {
			case 'update_company_category':
				$id_category = intval($this->uri->segment(4));
				$data['category_info'] = $this->company->get_company_category($id_category);
				$this->view->display('admin/directory/categories/form_view', $data);
			break;
            case 'pick_of_the_month':
				if (!have_right('manage_picks_of_the_month')){
                    messageInModal(translate("systmess_error_rights_perform_this_action"));
                }

                #region check if exists
                /** @var Company_Model $companyModel */
                $companyModel = model(Company_Model::class);

                $company = $companyModel->get_simple_company($company_id);
                if(empty($company)){
                    messageInModal('Error: This item does not exist.');
                }
                #endregion check if exists

                #region check if is currently pick of the month
                /** @var Pick_Of_The_Month_Company_Model $pickItems */
                $pickCompany = model(Pick_Of_The_Month_Company_Model::class);

                $sameCompany = $pickCompany->findOneBy([
                    'conditions' => [
                        'idCompany'   => $company_id,
                        'dateBetween' => new DateTime()
                    ]
                ]);

                $infoMessage = '';
                if(!empty($sameCompany)){
                    $infoMessage = 'This company is already pick of the month currently';
                }
                #endregion check if is currently pick of the month

                #region check if has items
                /** @var Products_Model $itemsModel */
                $itemsModel = model(Products_Model::class);

                $nrItems = $itemsModel->countBy([
                    'conditions' => [
                        'sellerId'      => (int) $company['id_user'],
                        'visible'       => 1,
                        'moderation'    => 1,
                        'blockedValue'  => 0,
                        'draft'         => 0,
                    ],
                ]);
                if($nrItems == 0){
                    $warningMessage = 'This company has no published items';
                }
                #endregion check if has items

                views()->assign([
                    'idCompany'      => $company_id,
                    'infoMessage'    => $infoMessage,
                    'warningMessage' => $warningMessage,
                    'type'           => 'company'
                ]);
                views()->display('admin/pick_of_the_month/pick_of_the_month_popup_view');
			break;
			case 'add_company_category':
				$data['industries'] = model('category')->get_industries();
				$this->view->display('admin/directory/categories/form_view', $data);
			break;
			case 'update_company_industry':
				$id_industry = intval($this->uri->segment(4));
				$data['industry_info'] = $this->company->get_company_industry($id_industry);

				$this->view->display('admin/directory/industries/form_view', $data);
			break;
			case 'add_company_industry':
				$categories_seo = $this->company->get_simple_company_categories();
				$data['categories_seo_id'] = array();

				foreach($categories_seo as $item) {
					$data['categories_seo_id'][] = $item['id_category'];
                }

				$data['categories'] = model('category')->get_industries();

				$this->view->display('admin/directory/industries/form_view', $data);
			break;
			case 'update_company_type':
				$id_type = intval($this->uri->segment(4));

				$data['type_info'] = $this->company->get_company_type($id_type);
				$this->view->display('admin/directory/type/form_view', $data);
			break;
			case 'add_company_type':
				$this->view->display('admin/directory/type/form_view');
			break;
            case 'edit_company': // @deprecated
                messageInModal('This action is deprecated');

				$info = $this->company->get_company(array('id_company' => $company_id, 'type_company' => 'all'));
				$data['company'] = array(
					'name' => $info['name_company'],
					'index_name' => $info['index_name'],
					'country' => $info['id_country'],
					'states' => $info['id_state'],
					'port_city' => $info['id_city'],
					'type' => $info['id_type'],
					'address' => $info['address_company'],
					'lat' => $info['latitude'],
					'long' => $info['longitude'],
					'zip' => $info['zip_company'],
					'phone' => $info['phone_company'],
					'fax' => $info['fax_company'],
					'email' => $info['email_company'],
					'employees' => $info['employees_company'],
					'revenue' => $info['revenue_company'],
					'description' => $info['description_company'],
					'video' => $info['video_company'],
					'edit_info' => 'edit',
					'company_id' => $company_id,
					'logo_company' => $info['logo_company']
				);

				if (!empty($info['logo_company'])) {
					$data['company']['logo'] = $info['logo_company'];
                }

				$data['port_country'] = $this->country->fetch_port_country();

				$data['types'] = $this->company->get_company_types();

				//get industries
				$data['industries'] = model('category')->get_industries();

				//get categories selected
				$relation_category = $this->company->get_relation_category_by_company_id((int) $company_id);
				$data['company']['category'] = array();

				if(!empty($relation_category)){
					foreach($relation_category as $item) {
						$list_categories_selected[] = $item['id_category'];
                    }

					$data['company']['category'] = $list_categories_selected;
				}

				//get industries selected
				$relation_industry = $this->company->get_relation_industry_by_id($company_id);
				$data['company']['industry'] = array();

				if(!empty($relation_industry)){
					foreach($relation_industry as $item) {
						$list_industries_selected[] = $item['id_industry'];
                    }

					//industries selected
					$data['company']['industry'] = $list_industries_selected;

					//industries selected
					$data['industries_selected'] = $this->category->getCategories(
						array(
							'cat_list' => implode(',', $list_industries_selected),
							'columns' => "category_id, name, parent, p_or_m"
						)
					);
				}

				//categories selected
				if(!empty($list_industries_selected)) {
					// $data['categories'] = $this->company->get_company_industry_categories(array('parent' => implode(',', $list_industries_selected)));

					$data['categories'] = array();
					$categories_all = $this->category->getCategories(array('parent' => implode(',', $list_industries_selected)));

					foreach($categories_all as $categories_all_item){
						$data['categories'][$categories_all_item['parent']][] = $categories_all_item;
					}
				}

				//categories selected
				if(!empty($list_categories_selected)) {
					// $data['categories_selected'] = $this->company->get_categories_by_conditions(array('category_list' => implode(',', $list_categories_selected)));

					$data['categories_selected'] = array();
					$categories_selected_all = $this->company->get_categories_by_conditions(array('category_list' => implode(',', $list_categories_selected)));

					foreach($categories_selected_all as $categories_selected_all_item){
						$data['categories_selected'][$categories_selected_all_item['parent']][] = $categories_selected_all_item;
					}
				}

				if (!empty($data['company']['country'])) {
					$data['states'] = $this->country->get_states($data['company']['country']);
                }

				$data['city_selected'] = $this->country->get_city($data['company']['port_city']);

				$this->view->assign($data);
				$this->view->display("admin/directory/edit_company_view");

			break;
			case 'company_details':
				$this->load->model('Seller_About_Model', 'seller_about');
				$id_seller = intval($this->uri->segment(4));

				$data['about_page'] = $this->seller_about->getPageAbout($id_seller);
				$data['about_page_aditional'] = $this->seller_about->getPageAboutAditional($id_seller);

				$this->view->assign($data);
				$this->view->display('admin/directory/company_details_view');
			break;
			case 'video_company':
				$info = $this->company->get_company(['id_company' => $company_id, 'type_company' => 'all']);
				if (empty($info['video_company'])) {
					messageInModal('Info: There are no video to display for this company.', 'info');
				}

				$this->view->display('admin/directory/video_company_view', [
                    'name_company' => $info['name_company'],
					'video'        => [
                        'urlId'  => $info['video_company_code'],
                        'source' => $info['video_company_source'],
                    ],
                ]);
			break;
			case 'company_statistic':
				$data['company_statistic'] = $this->company->get_company_statistics($company_id);
				$this->view->display('admin/directory/company_statistic_view', $data);
			break;
		}
	}

	function ajax_company_administration() {
		if (!isAjaxRequest()) {
			show_404();
        }

		if (!logged_in()) {
            {
			    jsonResponse(translate("systmess_error_should_be_logged"));
            }
		}

		if (!have_right('moderate_content')) {
			jsonResponse('Error: You do not have permission to edit this company.');
		}

		$this->_load_main();
		$option = $this->uri->segment(3);
		switch ($option) {
			case 'check_new':
				$lastId = $_POST['lastId'];
				$companies_count = $this->company->get_count_new_companies($lastId);

				if (!empty($companies_count)) {
					$last_companies_id = $this->company->get_companies_last_id();
					jsonResponse('', 'success', array('nr_new' => $companies_count, 'lastId' => $last_companies_id));
				} else {
					jsonResponse('Error: New companies do not exist');
                }
			break;
            case 'edit': // @deprecated
                // jsonResponse('This action is deprecated');

				// $validator = $this->validator;
				// $validator_rules = array(
				// 	array(
				// 		'field' => 'name',
				// 		'label' => 'Company name',
				// 		'rules' => array('required' => '')
				// 	),
				// 	array(
				// 		'field' => 'type',
				// 		'label' => 'Company type',
				// 		'rules' => array('required' => '')
				// 	),
				// 	array(
				// 		'field' => 'country',
				// 		'label' => 'Company country',
				// 		'rules' => array('required' => '')
				// 	),
				// 	array(
				// 		'field' => 'port_city',
				// 		'label' => 'Company city',
				// 		'rules' => array('required' => '')
				// 	),
				// 	array(
				// 		'field' => 'address',
				// 		'label' => 'Company address',
				// 		'rules' => array('required' => '')
				// 	),
				// 	array(
				// 		'field' => 'zip',
				// 		'label' => 'Company zip',
				// 		'rules' => array('required' => '', 'zip_code' => '', 'max_len[20]' => '')
				// 	),
				// 	array(
				// 		'field' => 'phone',
				// 		'label' => 'Company phone',
				// 		'rules' => array('required' => '')
				// 	),
				// 	array(
				// 		'field' => 'email',
				// 		'label' => 'Company email',
				// 		'rules' => array('required' => '')
				// 	),
				// 	array(
				// 		'field' => 'video',
				// 		'label' => 'Company video',
				// 		'rules' => array('valid_url' => '', 'max_len[200]' => '')
				// 	)
				// );

				// $this->validator->set_rules($validator_rules);

				// if (!$this->validator->validate()) {
				// 	jsonResponse($this->validator->get_array_errors());
				// }

				// if(!empty($_POST['industriesSelected'])){
				// 	$post_industries_count = count($_POST['industriesSelected']);
				// 	$industries_count = $this->company->count_categories_by_conditions(array('category_list' => implode(',', $_POST['industriesSelected']), 'parent' => 0));

				// 	if($post_industries_count !== intval($industries_count)){
				// 		jsonResponse('Some industries selected does not exist.', 'error');
				// 	}
				// }

				// if(!empty($_POST['categoriesSelected'])){
				// 	$post_category_count = count($_POST['categoriesSelected']);
				// 	$category_count = $this->company->count_categories_by_conditions(array('category_list' => implode(',',$_POST['categoriesSelected']), 'parent_not' => 0));

				// 	if($post_category_count !== intval($category_count)){
				// 		jsonResponse('Some industries selected does not exist.', 'error');
				// 	}
				// }

				// $id_company = intval($_POST['id_company']);
				// $company = $this->company->get_company(array('id_company' => $id_company));
				// $update = array(
				// 	'name_company' => cleanInput($_POST['name']),
				// 	'id_type' => intVal($_POST['type']),
				// 	'id_country' => intVal($_POST['country']),
				// 	'id_state' => intVal($_POST['states']),
				// 	'id_city' => intVal($_POST['port_city']),
				// 	'address_company' => cleanInput($_POST['address']),
				// 	'longitude' => cleanInput($_POST['long']),
				// 	'latitude' => cleanInput($_POST['lat']),
				// 	'zip_company' => cleanInput($_POST['zip']),
				// 	'phone_company' => cleanInput($_POST['phone']),
				// 	'fax_company' => cleanInput($_POST['fax']),
				// 	'email_company' => cleanInput($_POST['email'], true),
				// 	'employees_company' => cleanInput($_POST['employees']),
				// 	'revenue_company' => cleanInput($_POST['revenue']),
				// 	'description_company' => $_POST['description']
				// );

				// if ($company['video_company'] != $_POST['video']) {
				// 	$this->load->library('videothumb');
				// 	$video_link = $this->videothumb->getVID($_POST['video']);
				// 	$new_video = $this->videothumb->process($_POST['video']);

				// 	if (isset($new_video['error'])) {
				// 		jsonResponse($new_video['error']);
				// 	}

				// 	$update['video_company_source'] = $video_link['type'];

				// 	$path = 'public/img/company/'.$id_company;
				// 	if (!is_dir($path))
				// 		mkdir($path, 0777);

				// 	$file_video[] = $new_video['image'];
				// 	$conditions = array(
				// 		'images' => $file_video,
				// 		'destination' => $path,
				// 		'resize' => '730xR'
				// 	);
				// 	$res = $this->upload->copy_images_new($conditions);

				// 	if (count($res['errors'])) {
				// 		jsonResponse($res['errors']);
				// 	}
				// 	@unlink($path . '/' . $company['video_company_image']);
				// 	@unlink($new_video['image']);

				// 	$update['video_company_image'] = $res[0]['new_name'];
				// 	$update['video_company'] = $_POST['video'];
				// 	$update['video_company_code'] = $new_video['v_id'];
				// }

				// // $this->company->delete_relation_industry_by_company($id_company);
				// // if(!empty($_POST['industries'])){
				// // 	$this->company->set_relation_industry($id_company, $_POST['industries']);
				// // }

				// // $this->company->delete_relation_category_by_company($id_company);
				// // if(!empty($_POST['categories'])){
				// // 	$this->company->set_relation_category($id_company, $_POST['categories']);
				// // }

				// if(!empty($_POST['industriesSelected'])){
				// 	$this->company->delete_relation_industry_by_company($id_company);
				// 	$this->company->set_relation_industry($id_company, $_POST['industriesSelected']);
				// }

				// if(!empty($_POST['categoriesSelected'])){
				// 	$this->company->delete_relation_category_by_company($id_company);
				// 	$this->company->set_relation_category($id_company, $_POST['categoriesSelected']);
				// }

				// $this->company->update_company($id_company, $update);

				// jsonResponse('All changes were successfully saved.', 'success');

			break;
			case 'change_visibility':
                $companyId = request()->request->getInt('id');
                $currentState = request()->request->get('state');

                $update = ['visible_company' => (int) ('visible' != $currentState)];

                /** @var Company_Model $companyModel */
                $companyModel = model(Company_Model::class);

                if (!$companyModel->update_company($companyId, $update)) {
                    jsonResponse(translate('systmess_internal_server_error'));
                }

                /** @var Elasticsearch_B2b_Model $elasticsearchB2bModel */
                $elasticsearchB2bModel = model(Elasticsearch_B2b_Model::class);

                if ('visible' == $currentState) {
                    $elasticsearchB2bModel->removeB2bRequestsByConditions(['companyId' => $companyId]);
                } else {
                    $elasticsearchB2bModel->indexByConditions(['companyId' => $companyId]);
                }

				jsonResponse(
                    'The visibility of the company was successfully changed',
                    'success',
                    [
                        'state' => 'visible' == $currentState ? 'invisible' : 'visible',
                        'id'    => $companyId,
                    ]
                );
			break;
			case 'change_featured_status':
				$id_company = (int) $_POST['id'];

				if (empty($id_company) || ! isset($_POST['current_state'])) {
					jsonResponse(translate('systmess_error_invalid_data'));
				}

				$current_state = (int) $_POST['current_state'];
				$company = model('company')->get_simple_company($id_company, 'is_featured, id_user, blocked, visible_company, type_company');

				if ($company['type_company'] !== 'company' || $current_state != $company['is_featured']) {
					jsonResponse(translate('systmess_error_invalid_data'));
                }

				if ( ! $current_state) { // if it is an attempt to set as featured
					$seller = model('user')->getUser($company['id_user']);

					if ($seller['fake_user']) {
						jsonResponse('The seller of this company is DEMO.');
					}

					if ( ! $seller['is_verified']) {
						jsonResponse('The seller of this company is NOT VERIFIED.');
					}

					if ($company['blocked']) {
						jsonResponse('The company is BLOCKED.');
					}

					if ( ! $company['visible_company']) {
						jsonResponse('The company is not visible');
					}

					$max_allowable_companies_count = config('home_featured_companies_max_count', 12);
					$count_featured_companies = model('company')->count_companies(array('featured' => 1));

					if ($count_featured_companies >= $max_allowable_companies_count) {
						jsonResponse('You should not exceed the limit of ' . $max_allowable_companies_count . ' featured companies.', 'warning');
					}
				}

				$update_company_data = array(
					'is_featured' => (int) !$current_state
                );

				if ( ! model('company')->update_company($id_company, $update_company_data)) {
					jsonResponse(translate('systmess_internal_server_error'));
				}

				if ( !$current_state) {
					$companies_block_id = 'featured-companies';

					model('notify')->send_notify([
						'systmess'      => true,
						'mess_code'     => 'company_featured',
						'id_users'		=> [$company['id_user']],
						'replace'       => [
                            '[LINK_TO_COMPANIES]' 	=> __SITE_URL . '#' . $companies_block_id,
                            '[COMPANIES_BLOCK_ID]'	=> $companies_block_id,
                        ],
                    ]);

                    try {
                        /** @var MailerInterface $mailer */
                        $mailer = $this->getContainer()->get(MailerInterface::class);
                        $mailer->send(
                            (new FeaturedCompany("{$seller['fname']} {$seller['lname']}"))
                                ->to(new RefAddress((string) $company['id_user'], new Address($seller['email'])))
                        );
                    } catch (\Throwable $th) {
                        jsonResponse(translate('email_has_not_been_sent'));
                    }
                }

				jsonResponse('The company was successfully set as ' . ($current_state ? 'non-featured.' : 'featured.') , 'success');
            break;
            case 'change_blocked':

                $companies_ids = explode(',', cleanInput($_POST['id']));
                $state = (int) $_POST['state'];

                if ( empty($companies_ids) && !isset($state) ) {
					jsonResponse(translate('systmess_error_invalid_data'));
                }

                foreach($companies_ids as $id) {
                    if (!$this->company->update_company( (int) $id, ['blocked' => $state ? 1 : 0])) {
						jsonResponse('Error: The company wasn\'t setted blocked');
                    }
                }

                jsonResponse($state ? 'The companies are blocked' : 'The companies are unblocked', 'success');
            break;
		}
	}

	function videos_administration() {
		checkAdmin('moderate_content');

		$this->_load_main();
		$data['last_videos_id'] = $this->company->get_videos_last_id();
		$data['title'] = 'Videos';

		$this->view->assign($data);
		$this->view->display('admin/header_view');
		$this->view->display('admin/directory/videos/index_view');
		$this->view->display('admin/footer_view');
	}

	function ajax_video_list_dt() {
        checkIsAjax();
        checkIsLoggedAjaxDT();
        checkPermisionAjaxDT('moderate_content');

		$this->_load_main();

		$conditions = array();
		if (isset($_POST['iDisplayStart'])) {
			$from = intval(cleanInput($_POST['iDisplayStart']));
			$till = intval(cleanInput($_POST['iDisplayLength']));
			$conditions['limit'] = $from . ',' . $till;
		}

        $conditions['multiple_sort_by'] = flat_dt_ordering($_POST, [
            'company'     => 'name_company',
            'title'       => 'title_video',
            'description' => 'description_video',
            'added'       => 'add_date_video',
            'comments'    => 'comments_count',
            'user'        => 'user_name'
        ]);

        $conditions = array_merge($conditions,
            dtConditions($_POST, [
                ['as' => 'added_start', 'key' => 'start_date', 'type' => 'getDateFormat:m/d/Y,Y-m-d'],
                ['as' => 'added_finish', 'key' => 'finish_date', 'type' => 'getDateFormat:m/d/Y,Y-m-d'],
                ['as' => 'seller', 'key' => 'id_seller', 'type' => 'int'],
                ['as' => 'id_company', 'key' => 'company', 'type' => 'int'],
                ['as' => 'keywords', 'key' => 'keywords', 'type' => 'cleanInput'],
                ['as' => 'visibility', 'key' => 'visibility', 'type' => 'cleanInput']
            ])
        );

        if (!empty($_POST['type_company'])) {
			$conditions['type_company'] = 'company';
			if (intval($_POST['type_company']) == 2) {
				$conditions['type_company'] = 'branch';
            }
		} else {
			$conditions['type_company'] = 'all';
		}

		$videos_list = $this->company->get_companies_videos($conditions);
		$records_total = $this->company->count_companies_videos($conditions);
		$output = array(
			'sEcho' => intval($_POST['sEcho']),
			'iTotalRecords' => $records_total,
			'iTotalDisplayRecords' => $records_total,
			'aaData' => array()
		);

		if (empty($videos_list)) {
			jsonResponse('', 'success', $output);
        }
        /** @var FilesystemProviderInterface  $storageProvider */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $publicDisk = $storageProvider->storage('public.storage');

        /** @var FilesystemProviderInterface */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $publicDisk = $storageProvider->storage('public.storage');

		foreach ($videos_list as $video) {
			$company_type_id = 1; //company
			$title = "<p class='tac'>-</p>";
			$full_title = "There is no title";
			$description = "<p class='tac'>-</p>";
			$full_description = "There is no description";
			$visible = '<a class="pull-left dt_filter" data-name="visibility" href="#" data-title="Visibility" data-value="1" data-value-text="Visible">Yes</a>';
			$moderate_btn = '<a class="ep-icon ep-icon_sheild-ok txt-green confirm-dialog" data-callback="moderate_video" data-message="Are you sure want to moderate this video?" title="Moderate video" data-id="' . $video['id_video'] . '"></a>';

			if ($video['type_company'] == "branch") {
				$company_type_id = 2;
            }
            $image_link = $publicDisk->url(CompanyVideosFilePathGenerator::videosPath($video['id_company'], $video['image_video']));
            $video_link = '<a href="'. getCompanyURL($video) . '/video/' . $video['id_video'] . '" class="wr-video-link">'
					. '<div class="bg"><i class="ep-icon ep-icon_play"></i></div>'
					. '<div class="img-b">'
					. '<img src="'. $image_link . '" width="100px"></div></a>';

			$type_company = '<a class="pull-left dt_filter" data-name="type_company" href="#" data-title="Company type" data-value="' . $company_type_id . '" data-value-text="' . $video['type_company'] . '"><p>' . ucfirst($video['type_company']) . '</p></a>';

			if (!empty($video['title_video'])) {
				$title = "<p class='h-50 hidden-b' title='" . $video['title_video'] . "'>" . $video['title_video'] . "</p>" . ((strlen($video['title_video']) > 150) ? "<a rel='video_details' title='View details'><p class='tac'>...</p></a>" : "");
				$full_title = $video['title_video'];
			}

			if (!empty($video['description_video'])) {
				$description = "<p class='h-50 hidden-b'>" . $video['description_video'] . "</p>" . ((strlen($video['description_video']) > 150) ? "<a rel='video_details' class='ep-icon ep-icon_plus' title='View details'></a>" : "");
				$full_description = $video['description_video'];
			}

			if ($video['visible_company'] == 0) {
				$visible = '<a class="pull-left dt_filter" data-name="visibility" href="#" data-title="Visibility" data-value="0" data-value-text="Invisible">No</a>';
            }

			if ($video['moderated']) {
				$moderate_btn = '<a class="ep-icon ep-icon_sheild-nok txt-red" title="Moderated video"/>';
			}

			$company_logo = getDisplayImageLink(array('{ID}' => $video['id_company'], '{FILE_NAME}' => $video['logo_company']), 'companies.main', array( 'thumb_size' => 0 ));

            //TODO: admin chat hidden
            $btnChatUser = new ChatButton(['hide' => true, 'recipient' => $video['id_user'], 'recipientStatus' => $video['status']], ['classes' => 'btn-chat-now', 'text' => '']);
            $btnChat = $btnChatUser->button();

			$output['aaData'][] = array(
				'id' => $video['id_video'] . '<input type="checkbox" class="check-video pull-left" data-id-video="' .$video['id_video'] . '">
					</br><a rel="video_details" title="View details" class="ep-icon ep-icon_plus"></a>',
				"company" => '<div class="img-prod pull-left w-40pr">'
						. '<img class="w-100pr" src="' . $company_logo . '" alt="' . $video['name_company'] . '"/>'
					. '</div>'
					. '<div class="pull-right w-58pr">'
						. '<div class="pull-left w-100pr">'
							. '<div class="pull-left">'
								. '<a class="ep-icon ep-icon_filter txt-green dt_filter" data-title="Company" title="Filter by ' . $video['name_company'] . '" data-value-text="' . $video['name_company'] . '" data-value="' . $video['id_company'] . '" data-name="company"></a>'
								. '<a class="ep-icon ep-icon_building" title="View company\'s profile" href="' . getCompanyURL($video) . '"></a>'
								. '<a class="ep-icon ep-icon_video fancybox.ajax fancybox" data-title="View company video" title="View company video" href="' . __SITE_URL . 'directory/popup_forms/video_company/' . $video['id_company'] . '"></a>'
								. '<a class="ep-icon ep-icon_visible fancybox.ajax fancybox" data-title="View details about this company" title="View details about this company" href="' . __SITE_URL . 'directory/popup_forms/company_details/' . $video['id_user'] . '"></a>'
							. '</div>'
							. '<div class="pull-right">
									<input class="rating-bootstrap" data-filled="ep-icon ep-icon_star txt-blue fs-18 m-0" data-empty="ep-icon ep-icon_star-empty txt-blue fs-18 m-0" type="hidden" name="val" value="' . $video['rating'] . '" data-readonly>
								</div>'
						. '</div>'
						. '<div class="clearfix"><strong class="pull-left lh-16 pr-5">Title: </strong> <a class="pull-left dt_filter" data-name="id_company" href="#" data-title="Company" data-value="' . $video['id_company'] . '" data-value-text="' . $video['name_company'] . '">' . $video['name_company'] . '</a></div>'
						. '<div class="clearfix"><strong class="pull-left lh-16 pr-5">Company type: </strong>' . $type_company . '</div>'
						. '<div class="clearfix"><strong class="pull-left lh-16 pr-5">Visible: </strong>' . $visible . '</div>'
					. '</div>',
				"user" => '<div class="pull-left">'
						. '<a class="ep-icon ep-icon_filter txt-green dt_filter" data-title="Seller" title="Filter by ' . $video['user_name'] . '" data-value-text="' . $video['user_name'] . '" data-value="' . $video['id_user'] . '" data-name="seller"></a>'
						. '<a class="ep-icon ep-icon_user" title="View personal page of ' . $video['user_name'] . '" href="' . __SITE_URL . 'usr/' . strForURL($video['user_name']) . '-' . $video['id_user'] . '"></a>'
					. '</div>'
					. '<div class="clearfix"></div>'
					. '<span>' . $video['user_name'] . '</span>',
				"video" => $video_link,
				"title" => $title,
				"full_title" => $full_title,
				"description" => $description,
				"full_description" => $full_description,
				"added" => formatDate($video['add_date_video']),
				"comments" => $video['comments_count'],
				"actions" => $moderate_btn
					. '<a class="ep-icon ep-icon_remove txt-red confirm-dialog" data-message="Are you sure want to delete this video?" title="Delete video" data-id="' . $video['id_video'] . '" data-callback="delete_video"></a>'
					. $btnChat,
			);
		}

		jsonResponse('', 'success', $output);
	}

	function photos_administration() {
		checkAdmin('moderate_content');

		$this->_load_main();
		$data['last_photos_id'] = $this->company->get_photos_last_id();

		$this->view->assign($data);
		$this->view->assign('title', 'Photos');
		$this->view->display('admin/header_view');
		$this->view->display('admin/directory/photos/index_view');
		$this->view->display('admin/footer_view');
	}

	function ajax_photo_list_dt() {
		checkIsAjax();
        checkIsLoggedAjaxDT();
        checkPermisionAjaxDT('moderate_content');

		$this->_load_main();

		$conditions = array();
		if (isset($_POST['iDisplayStart'])) {
			$from = intval(cleanInput($_POST['iDisplayStart']));
			$till = intval(cleanInput($_POST['iDisplayLength']));
			$conditions['limit'] = $from . ',' . $till;
		}

        $conditions['multiple_sort_by'] = flat_dt_ordering($_POST, [
            'company'     => 'name_company',
            'title'       => 'title_photo',
            'description' => 'description_photo',
            'added'       => 'add_date_photo',
            'comments'    => 'comments_count',
            'user'        => 'user_name'
        ]);

        $conditions = array_merge($conditions,
            dtConditions($_POST, [
                ['as' => 'added_start', 'key' => 'start_date', 'type' => 'getDateFormat:m/d/Y,Y-m-d'],
                ['as' => 'added_finish', 'key' => 'finish_date', 'type' => 'getDateFormat:m/d/Y,Y-m-d'],
                ['as' => 'seller', 'key' => 'id_seller', 'type' => 'int'],
                ['as' => 'id_company', 'key' => 'company', 'type' => 'int'],
                ['as' => 'keywords', 'key' => 'keywords', 'type' => 'cleanInput'],
                ['as' => 'visibility', 'key' => 'visibility', 'type' => 'int']
            ])
        );

        if (!empty($_POST['type_company'])) {
			$conditions['type_company'] = 'company';
			if (intval($_POST['type_company']) == 2) {
				$conditions['type_company'] = 'branch';
            }
		} else {
			$conditions['type_company'] = 'all';
		}

		$photos_list = $this->company->get_companies_photos($conditions);
		$records_total = $this->company->count_companies_photos($conditions);

		$output = array(
			'sEcho' => intval($_POST['sEcho']),
			'iTotalRecords' => $records_total,
			'iTotalDisplayRecords' => $records_total,
			'aaData' => array()
		);

		if(empty($photos_list)) {
			jsonResponse('', 'success', $output);
        }

        /** @var FilesystemProviderInterface  $storageProvider */
        $provider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $storage = $provider->storage('public.storage');

		foreach ($photos_list as $photo) {
			$company_type_id = 1; //company
			$title = "<p class='tac'>-</p>";
			$full_title = "There is no title";
			$description = "<p class='tac'>-</p>";
			$full_description = "There is no description";
			$visible = '<a class="pull-left dt_filter" data-name="visibility" href="#" data-title="Visibility" data-value="1" data-value-text="Visible">Yes</a>';
			$moderate_btn = '<a class="ep-icon ep-icon_sheild-nok txt-red confirm-dialog" data-callback="moderate_photo" title="Moderate photo" data-message="Are you sure want to moderate this photo?" data-id="' . $photo['id_photo'] . '"></a>';

			if ($photo['type_company'] == 'branch') {
				$company_type_id = 2;
            }

			$type_company = '<a class="pull-left dt_filter" data-name="type_company" href="#" data-title="Company type" data-value="' . $company_type_id . '" data-value-text="' . $photo['type_company'] . '"><p>' . ucfirst($photo['type_company']) . '</p></a>';

			if (!empty($photo['title_photo'])) {
				$title = "<p class='h-50 hidden-b' title='" . $photo['title_photo'] . "'>" . $photo['title_photo'] . "</p>" . ((strlen($photo['title_photo']) > 150) ? "<a rel='photo_details' title='View details'><p class='tac'>...</p></a>" : "");
				$full_title = $photo['title_photo'];
			}


			if (!empty($photo['description_photo'])) {
				$description = "<p class='h-50 hidden-b'>" . $photo['description_photo'] . "</p>" . ((strlen($photo['description_photo']) > 150) ? "<a rel='photo_details' class='ep-icon ep-icon_plus' title='View details'></a>" : "");
				$full_description = $photo['description_photo'];
			}

			if ($photo['visible_company'] == 0) {
				$visible = '<a class="pull-left dt_filter" data-name="visibility" href="#" data-title="Visibility" data-value="0" data-value-text="Invisible">No</a>';
            }

			if ($photo['moderated']) {
				$moderate_btn = '<a class="ep-icon ep-icon_sheild-ok txt-green" title="Moderated photo"/>';
			}

			// $banned_btn = '<a class="ep-icon ep-icon_user-plus txt-green user_' . $photo['id_user'] . ' fancyboxValidateModalDT fancybox.ajax" href="user/popup_forms/bann_user/' . $photo['id_user'] . '" title="Ban this user" data-title="Ban this user" data-user="' . $photo['id_user'] . '"></a>';
			// if ($photo['status'] == 'banned') {
			// 	$banned_btn = '<a class="ep-icon ep-icon_user-minus txt-red user_' . $photo['id_user'] . ' confirm-dialog" data-callback="unban_user" title="Unban this user" data-user="' . $photo['id_user'] . '" data-message="Are you sure want to unban this user?"></a>';
			// }

			$company_logo = getDisplayImageLink(array('{ID}' => $photo['id_company'], '{FILE_NAME}' => $photo['logo_company']), 'companies.main', array( 'thumb_size' => 1 ));

            //TODO: admin chat hidden
            $btnChatUser = new ChatButton(['hide' => true, 'recipient' => $photo['id_user'], 'recipientStatus' => $photo['status']], ['classes' => 'btn-chat-now', 'text' => '']);
            $btnChat = $btnChatUser->button();

            $photoLink = $storage->url(CompanyPhotosFilePathGenerator::thumbImage($photo['id_company'], $photo['path_photo'], CompanyPhotosThumb::SMALL()));
			$output['aaData'][] = array(
				'id' => $photo['id_photo'] . '<input type="checkbox" class="check-photo pull-left" data-id-photo="' .$photo['id_photo'] . '">
					</br><a rel="photo_details" title="View details" class="ep-icon ep-icon_plus"></a>',
				'company' => '<div class="img-prod pull-left w-40pr">'
						. '<img class="w-100pr" src="' . $company_logo . '" alt="' . $photo['name_company'] . '"/>'
					. '</div>'
					. '<div class="pull-right w-58pr">'
						. '<div class="pull-left w-100pr">'
							. '<div class="pull-left">'
								. '<a class="ep-icon ep-icon_filter txt-green dt_filter" data-title="Company" title="Filter by ' . $photo['name_company'] . '" data-value-text="' . $photo['name_company'] . '" data-value="' . $photo['id_company'] . '" data-name="company"></a>'
								. '<a class="ep-icon ep-icon_building" title="View company\'s profile" href="' . getCompanyURL($photo) . '"></a>'
								. '<a class="ep-icon ep-icon_video fancybox.ajax fancybox" data-title="View company video" title="View company video" href="' . __SITE_URL . 'directory/popup_forms/video_company/' . $photo['id_company'] . '"></a>'
								. '<a class="ep-icon ep-icon_visible fancybox.ajax fancybox" title="View details about this company" data-title="View details about this company" href="' . __SITE_URL . 'directory/popup_forms/company_details/' . $photo['id_user'] . '"></a>'
							. '</div>'
							. '<div class="pull-right">
								<input class="rating-bootstrap" data-filled="ep-icon ep-icon_star txt-blue fs-18 m-0" data-empty="ep-icon ep-icon_star-empty txt-blue fs-18 m-0" type="hidden" name="val" value="' . $photo['rating'] . '" data-readonly>
							</div>'
						. '</div>'
						. '<div class="clearfix"><strong class="pull-left lh-16 pr-5">Title: </strong> <a class="pull-left dt_filter" data-name="id_company" href="#" data-title="Company" data-value="' . $photo['id_company'] . '" data-value-text="' . $photo['name_company'] . '">' . $photo['name_company'] . '</a></div>'
						. '<div class="clearfix"><strong class="pull-left lh-16 pr-5">Company type: </strong>' . $type_company . '</div>'
						. '<div class="clearfix"><strong class="pull-left lh-16 pr-5">Visible: </strong>' . $visible . '</div>'
					. '</div>',
				'user' => '<div class="pull-left">'
						. '<a class="ep-icon ep-icon_filter txt-green dt_filter" data-title="Seller" title="Filter by ' . $photo['user_name'] . '" data-value-text="' . $photo['user_name'] . '" data-value="' . $photo['id_user'] . '" data-name="seller"></a>'
						. '<a class="ep-icon ep-icon_user" title="View personal page of ' . $photo['user_name'] . '" href="' . __SITE_URL . 'usr/' . strForURL($photo['user_name']) . '-' . $photo['id_user'] . '"></a>'
					. '</div>'
					. '<div class="clearfix"></div>'
					. '<span>' . $photo['user_name'] . '</span>',
				'photo' => '<img src="' . $photoLink . '"/>',
				'title' => $title,
				'full_title' => $full_title,
				'description' => $description,
				'full_description' => $full_description,
				'added' => formatDate($photo['add_date_photo']),
				'comments' => $photo['comments_count'],
				'actions' => $moderate_btn
					. '<a class="ep-icon ep-icon_remove txt-red confirm-dialog" data-callback="delete_photo" data-message="Are you sure want to delete this photo?" title="Delete photo" data-id="' . $photo['id_photo'] . '"></a>'
					. $btnChat
			);
		}

		jsonResponse('', 'success', $output);
	}

	function news_administration() {
		checkAdmin('moderate_content');

		$this->_load_main();

		$data['last_news_id'] = $this->company->get_news_last_id();

		$this->view->assign($data);
		$this->view->assign('title', 'News');
		$this->view->display('admin/header_view');
		$this->view->display('admin/directory/news/index_view');
		$this->view->display('admin/footer_view');
	}

	function ajax_news_list_dt() {
		checkIsAjax();
        checkIsLoggedAjaxDT();
        checkPermisionAjaxDT('moderate_content');

		$this->_load_main();

		$conditions = array();
		if (isset($_POST['iDisplayStart'])) {
			$from = intval(cleanInput($_POST['iDisplayStart']));
			$till = intval(cleanInput($_POST['iDisplayLength']));
			$conditions['limit'] = $from . ',' . $till;
		}

        $conditions['multiple_sort_by'] = flat_dt_ordering($_POST, [
            'company'     => 'name_company',
            'title'       => 'title_news',
            'description' => 'text_news',
            'added'       => 'date_news',
            'comments'    => 'comments_count',
            'user'        => 'user_name'
        ]);

        $conditions = array_merge($conditions,
            dtConditions($_POST, [
                ['as' => 'added_start', 'key' => 'start_date', 'type' => 'getDateFormat:m/d/Y,Y-m-d'],
                ['as' => 'added_finish', 'key' => 'finish_date', 'type' => 'getDateFormat:m/d/Y,Y-m-d'],
                ['as' => 'seller', 'key' => 'id_seller', 'type' => 'int'],
                ['as' => 'id_company', 'key' => 'company', 'type' => 'int'],
                ['as' => 'keywords', 'key' => 'keywords', 'type' => 'cleanInput'],
                ['as' => 'visibility', 'key' => 'visibility', 'type' => 'int']
            ])
        );

        if (!empty($_POST['type_company'])) {
			$conditions['type_company'] = 'company';
			if (intval($_POST['type_company']) == 2) {
				$conditions['type_company'] = 'branch';
            }
		} else {
			$conditions['type_company'] = 'all';
		}

		$news_list = $this->company->get_companies_news($conditions);
		$records_total = $this->company->count_companies_news($conditions);

		$output = array(
			'sEcho' => intval($_POST['sEcho']),
			'iTotalRecords' => $records_total,
			'iTotalDisplayRecords' => $records_total,
			'aaData' => array()
		);

		if (empty($news_list)) {
			jsonResponse('', 'success', $output);
        }

		foreach ($news_list as $news) {
			$company_type_id = 1; //company
			$title = "<p class='tac'>-</p>";
			$full_title = "There is no title";
			$description = "<p class='tac'>-</p>";
			$full_description = "There is no description";
			$visible = '<a class="pull-left dt_filter" data-name="visibility" href="#" data-title="Visibility" data-value="1" data-value-text="Visible">Yes</a>';
			$moderate_btn = '<a class="ep-icon ep-icon_sheild-nok txt-red confirm-dialog" data-callback="moderate_one" title="Moderate news" data-message="Are you sure want to moderate this news?" data-id="' . $news['id_news'] . '"></a>';

			if ('branch' === $news['type_company']) {
				$company_type_id = 2;
            }

			$provider = $this->getContainer()->get(FilesystemProviderInterface::class);
			$storage = $provider->storage('public.storage');

			$news_image = '-';
			if ('' !== $news['image_news']) {
				$news_image = '<img class="mh-80" src="' .
					$storage->url(CompanyNewsFilePathGenerator::thumbImage($news['id_company'], $news['image_news'] ?: 'no-image.jpeg', SellerNewsPhotoThumb::MEDIUM()))
			. '"/>';
			}

			$type_company = '<a class="pull-left dt_filter" data-name="type_company" href="#" data-title="Company type" data-value="' . $company_type_id . '" data-value-text="' . $news['type_company'] . '"><p>' . ucfirst($news['type_company']) . '</p></a>';

			if (!empty($news['title_news'])) {
				$title = "<p class='h-50 hidden-b' title='" . $news['title_news'] . "'>" . $news['title_news'] . "</p>" . ((strlen($news['title_news']) > 150) ? "<a rel='photo_details' title='View details'><p class='tac'>...</p></a>" : "");
				$full_title = $news['title_news'];
			}

			if (!empty($news['text_news'])) {
				$description = "<div class='h-50 hidden-b'>" . $news['text_news'] . "</div>" . ((strlen($news['text_news']) > 150) ? "<a rel='photo_details' class='ep-icon ep-icon_plus' title='View details'></a>" : "");
				$full_description = $news['text_news'];
			}

			if ($news['visible_company'] == 0) {
				$visible = '<a class="pull-left dt_filter" data-name="visibility" href="#" data-title="Visibility" data-value="0" data-value-text="Invisible">No</a>';
            }

			if ($news['moderated']) {
				$moderate_btn = '<a class="ep-icon ep-icon_sheild-ok txt-green" title="Moderated news"></a>';
			}

			// $banned_btn = '<a class="ep-icon ep-icon_user-plus txt-green user_' . $news['id_user'] . ' fancybox.ajax fancyboxValidateModalDT" href="user/popup_forms/bann_user/' . $news['id_user'] . '" title="Ban this user" data-title="Ban this user"></a>';
			// if ($news['status'] == 'banned') {
			// 	$banned_btn = '<a class="ep-icon ep-icon_user-minus txt-red user_' . $news['id_user'] . ' confirm-dialog" title="Unban this user" data-user="' . $news['id_user'] . '" data-message="Are you sure want to unban this user?" data-callback="unban_user"></a>';
			// }

			$company_logo = getDisplayImageLink(array('{ID}' => $news['id_company'], '{FILE_NAME}' => $news['logo_company']), 'companies.main', array( 'thumb_size' => 0 ));

            //TODO: admin chat hidden
            $btnChatUser = new ChatButton(['hide' => true, 'recipient' => $news['id_user'], 'recipientStatus' => $news['status']], ['classes' => 'btn-chat-now', 'text' => '']);
            $btnChat = $btnChatUser->button();

			$output['aaData'][] = array(
				'id' => $news['id_news'] . '<input type="checkbox" class="check-news pull-left" data-id-news="' .$news['id_news'] . '"> <br>
					<a rel="photo_details" title="View details" class="ep-icon ep-icon_plus"></a>',
				'company' => '<div class="img-prod pull-left mw-100">'
						. '<img class="w-100pr mh-80" src="' . $company_logo . '" alt="' . $news['name_company'] . '"/>'
					. '</div>'
					. '<div class="pull-right w-58pr">'
						.'<div class="pull-left w-100pr">'
							. '<div class="pull-left">'
								. '<a class="ep-icon ep-icon_filter txt-green dt_filter" data-title="Company" title="Filter by ' . $news['name_company'] . '" data-value-text="' . $news['name_company'] . '" data-value="' . $news['id_company'] . '" data-name="company"></a>'
								. '<a class="ep-icon ep-icon_building" title="View company\'s profile" href="' . getCompanyURL($news) . '"></a>'
								. '<a class="ep-icon ep-icon_video fancybox.ajax fancybox" data-title="View company video" title="View company video" href="' . __SITE_URL . 'directory/popup_forms/video_company/' . $news['id_company'] . '"></a>'
								. '<a class="ep-icon ep-icon_visible fancybox.ajax fancybox" data-title="View details about company '.$news['name_company'].'" title="View details about this company" href="' . __SITE_URL . 'directory/popup_forms/company_details/' . $news['id_user'] . '"></a>'
							. '</div>'
							. '<div class="pull-right">
								<input class="rating-bootstrap" data-filled="ep-icon ep-icon_star txt-blue fs-18 m-0" data-empty="ep-icon ep-icon_star-empty txt-blue fs-18 m-0" type="hidden" name="val" value="' . $news['rating'] . '" data-readonly>
							</div>'
						. '</div>'
						. '<div class="pull-left"><strong class="pull-left lh-16 pr-5">Title: </strong> <a class="pull-left dt_filter" data-name="company" href="#" data-title="Company" data-value="' . $news['id_company'] . '" data-value-text="' . $news['name_company'] . '">' . $news['name_company'] . '</a></div>'
						. '<div class="pull-left"><strong class="pull-left lh-16 pr-5">Company type: </strong>' . $type_company . '</div>'
						. '<div class="pull-left"><strong class="pull-left lh-16 pr-5">Visible: </strong>' . $visible . '</div>'
					. '</div>',
				'user' => '<div class="tal">'
						. '<a class="ep-icon ep-icon_filter txt-green dt_filter" data-title="Seller" title="Filter by ' . $news['user_name'] . '" data-value-text="' . $news['user_name'] . '" data-value="' . $news['id_user'] . '" data-name="seller"></a>'
						. '<a class="ep-icon ep-icon_user" title="View personal page of ' . $news['user_name'] . '" href="' . __SITE_URL . 'usr/' . strForURL($news['user_name']) . '-' . $news['id_user'] . '"></a>'
					. '</div>'
					. '<div>' . $news['user_name'] . '</div>',
				'photo' => $news_image,
				'title' => $title,
				'full_title' => $full_title,
				'description' => $description,
				'full_description' => $full_description,
				'added' => formatDate($news['date_news']),
				'comments' => $news['comments_count'],
				'actions' =>
					'<a class="ep-icon ep-icon_pencil fancyboxValidateModalDT fancybox.ajax" title="Edit news" href="seller_news/popup_forms/admin_edit_news_form/' . $news['id_news'] . '" id="event-' . $news['id_news'] . '" data-title="Edit news"></a>'
					. '<a class="ep-icon ep-icon_remove txt-red confirm-dialog" data-message="Are you sure want to delete this news?" title="Delete news" data-id="' . $news['id_news'] . '" data-callback="delete_news_one"></a>'
					. $moderate_btn
					. $btnChat
			);
		}

		jsonResponse('', 'success', $output);
	}

	function updates_administration() {
		checkAdmin('moderate_content');

		$this->_load_main();
		$data['last_updates_id'] = $this->company->get_updates_last_id();

		$this->view->assign($data);
		$this->view->assign('title', 'Updates');
		$this->view->display('admin/header_view');
		$this->view->display('admin/directory/updates/index_view');
		$this->view->display('admin/footer_view');
	}

	function ajax_updates_list_dt() {
		checkIsAjax();
        checkIsLoggedAjaxDT();
        checkPermisionAjaxDT('moderate_content');

		$this->_load_main();

		$conditions = array();
		if (isset($_POST['iDisplayStart'])) {
			$from = intval(cleanInput($_POST['iDisplayStart']));
			$till = intval(cleanInput($_POST['iDisplayLength']));
			$conditions['limit'] = $from . ',' . $till;
		}

        $conditions['multiple_sort_by'] = flat_dt_ordering($_POST, [
            'company'     => 'name_company',
            'description' => 'text_update',
            'added'       => 'date_update',
            'user'        => 'user_name'
        ]);

        $conditions = array_merge($conditions,
            dtConditions($_POST, [
                ['as' => 'added_start', 'key' => 'start_date', 'type' => 'getDateFormat:m/d/Y,Y-m-d'],
                ['as' => 'added_finish', 'key' => 'finish_date', 'type' => 'getDateFormat:m/d/Y,Y-m-d'],
                ['as' => 'seller', 'key' => 'id_seller', 'type' => 'int'],
                ['as' => 'id_company', 'key' => 'company', 'type' => 'int'],
                ['as' => 'keywords', 'key' => 'keywords', 'type' => 'cleanInput'],
                ['as' => 'visibility', 'key' => 'visibility', 'type' => 'int']
            ])
        );

        if (!empty($_POST['type_company'])) {
			$conditions['type_company'] = 'company';
			if (intval($_POST['type_company']) == 2) {
				$conditions['type_company'] = 'branch';
            }
		} else {
			$conditions['type_company'] = 'all';
		}

		$updates_list = $this->company->get_companies_updates($conditions);
		$records_total = $this->company->count_companies_updates($conditions);

		$output = array(
			'sEcho' => intval($_POST['sEcho']),
			'iTotalRecords' => $records_total,
			'iTotalDisplayRecords' => $records_total,
			'aaData' => array()
		);

		if(empty($updates_list)) {
			jsonResponse('', 'success', $output);
        }
        /** @var FilesystemProviderInterface  $storageProvider */
        $provider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $storage = $provider->storage('public.storage');

		foreach ($updates_list as $update) {
			$company_type_id = 1;
			$description = "<p class='tac'>-</p>";
			$visible = '<a class="pull-left dt_filter" data-name="visibility" href="#" data-title="Visibility" data-value="1" data-value-text="Visible">Yes</a>';

			if ($update['type_company'] == 'branch') {
				$company_type_id = 2;
            }

			if ($update['photo_path'] != '') {
                $imageLink = $storage->url(CompanyUpdatesFilePathGenerator::thumbImage($update['id_company'], $update['photo_path'], SellerUpdatesPhotoThumb::MEDIUM()));
				$update_image = '<img class="mh-80" src="' . $imageLink . '"/>';
            } else {
				$update_image = "No photo";
            }

			$type_company = '<a class="pull-left dt_filter" data-name="type_company" href="#" data-title="Company type" data-value="' . $company_type_id . '" data-value-text="' . $update['type_company'] . '"><p>' . ucfirst($update['type_company']) . '</p></a>';

			if (!empty($update['text_update'])) {
				$description = $update['text_update'];
			}

			if ($update['visible_company'] == 0) {
				$visible = '<a class="pull-left dt_filter" data-name="visibility" href="#" data-title="Visibility" data-value="0" data-value-text="Invisible">No</a>';
			}

			// $banned_btn = '<a href="user/popup_forms/bann_user/' . $update['id_user'] . '" class="ep-icon ep-icon_user-plus txt-green user_' . $update['id_user'] . ' fancyboxValidateModalDT fancybox.ajax" title="Ban this user" data-title="Ban user '.$update['user_name'].'" data-user="' . $update['id_user'] . '"></a>';
			// if ($update['status'] == 'banned') {
			// 	$banned_btn = '<a data-callback="unban_user" class="ep-icon ep-icon_user-minus txt-red user_' . $update['id_user'] . ' confirm-dialog" title="Unban this user" data-user="' . $update['id_user'] . '" data-message="Are you sure want to unban user '.$update['user_name'].'?"></a>';
			// }

            $company_logo = getDisplayImageLink(array('{ID}' => $update['id_company'], '{FILE_NAME}' => $update['logo_company']), 'companies.main', array( 'thumb_size' => 0 ));

            //TODO: admin chat hidden
            $btnChatUser = new ChatButton(['hide' => true, 'recipient' => $update['id_user'], 'recipientStatus' => $update['status']], ['classes' => 'btn-chat-now', 'text' => '']);
            $btnChat = $btnChatUser->button();

			$output['aaData'][] = array(
				'id' => $update['id_update'] . '<input type="checkbox" class="check-update pull-left" data-id-update="' . $update['id_update'] . '">',
				'photo' => $update_image,
				'company' => '<div class="img-prod pull-left w-100"">'
					. '<img class=" mh-80 mw-100" src="' . $company_logo . '" alt="' . $update['name_company'] . '"/>'
					. '</div>'
					. '<div class="pull-right w-58pr">'
						. '<div class="pull-left w-100pr">'
							. '<div class="pull-left">'
								. '<a class="ep-icon ep-icon_filter txt-green dt_filter" data-title="Company" title="Filter by ' . $update['name_company'] . '" data-value-text="' . $update['name_company'] . '" data-value="' . $update['id_company'] . '" data-name="company"></a>'
								. '<a class="ep-icon ep-icon_building" title="View company\'s profile" href="' . getCompanyURL($update) . '"></a>'
								. '<a class="ep-icon ep-icon_video fancybox fancybox.ajax" title="View company video" href="' . __SITE_URL . 'directory/popup_forms/video_company/' . $update['id_company'] . '" data-title="View company video"></a>'
								. '<a class="ep-icon ep-icon_visible fancybox fancybox.ajax" title="View details about this company" href="' . __SITE_URL . 'directory/popup_forms/company_details/' . $update['id_user'] . '" data-title="View company details?"></a>'
							. '</div>'
							. '<div class="pull-right">
								<input class="rating-bootstrap" data-filled="ep-icon ep-icon_star txt-blue fs-18 m-0" data-empty="ep-icon ep-icon_star-empty txt-blue fs-18 m-0" type="hidden" name="val" value="' . $update['rating'] . '" data-readonly>
							</div>'
						. '</div>'
						. '<div class="clearfix"><strong class="pull-left lh-16 pr-5">Title: </strong> <a class="pull-left dt_filter" data-name="id_company" href="#" data-title="Company" data-value="' . $update['id_company'] . '" data-value-text="' . $update['name_company'] . '">' . $update['name_company'] . '</a></div>'
						. '<div class="clearfix"><strong class="pull-left lh-16 pr-5">Company type: </strong>' . $type_company . '</div>'
						. '<div class="clearfix"><strong class="pull-left lh-16 pr-5">Visible: </strong>' . $visible . '</div>'
					. '</div>',
				'user' => '<div class="pull-left">'
						. '<a class="ep-icon ep-icon_filter txt-green dt_filter" data-title="Seller" title="Filter by ' . $update['user_name'] . '" data-value-text="' . $update['user_name'] . '" data-value="' . $update['id_user'] . '" data-name="seller"></a>'
						. '<a class="ep-icon ep-icon_user" title="View personal page of ' . $update['user_name'] . '" href="' . __SITE_URL . 'usr/' . strForURL($update['user_name']) . '-' . $update['id_user'] . '"></a>'
					. '</div>'
					. '<div class="clearfix"></div>'
					. '<span>' . $update['user_name'] . '</span>',
				'description' => $description,
				'added' => formatDate($update['date_update']),
				'actions' => '<a class="ep-icon ep-icon_pencil fancybox.ajax fancyboxValidateModalDT" title="Edit update" data-w="700" href="seller_updates/popup_forms/admin_edit_update/' . $update['id_update'] . '" data-title="Edit update"></a>'
					. '<a class="ep-icon ep-icon_remove txt-red confirm-dialog" data-update="' . $update['id_update'] . '" title="Delete update" data-callback="remove_seller_update" data-message="Are you sure want to delete this update?"></a>'
					. $btnChat
			);
		}

		jsonResponse('', 'success', $output);
	}

	function library_administration() {
		checkAdmin('moderate_content');

		$this->_load_main();
		$data['last_libraries_id'] = $this->company->get_libraries_last_id();

		$this->view->assign($data);
		$this->view->assign('title', 'Library');
		$this->view->display('admin/header_view');
		$this->view->display('admin/directory/library/index_view');
		$this->view->display('admin/footer_view');
	}

	function ajax_library_list_dt() {
		checkIsAjax();
        checkIsLoggedAjaxDT();
        checkPermisionAjaxDT('moderate_content');

        $this->_load_main();
        $this->load->model('Seller_Library_Model', 'seller_library');

		$conditions = array();
		if (isset($_POST['iDisplayStart'])) {
			$from = intval(cleanInput($_POST['iDisplayStart']));
			$till = intval(cleanInput($_POST['iDisplayLength']));
			$conditions['limit'] = $from . ',' . $till;
		}

        $conditions['multiple_sort_by'] = flat_dt_ordering($_POST, [
            'company'     => 'name_company',
            'title'       => 'title_file',
            'description' => 'description_file',
            'added'       => 'add_date_file',
            'user'        => 'user_name',
            'access'      => 'type_file',
            'type'        => 'extension_file'
        ]);

        $conditions = array_merge($conditions,
            dtConditions($_POST, [
                ['as' => 'added_start', 'key' => 'start_date', 'type' => 'getDateFormat:m/d/Y,Y-m-d'],
                ['as' => 'added_finish', 'key' => 'finish_date', 'type' => 'getDateFormat:m/d/Y,Y-m-d'],
                ['as' => 'seller', 'key' => 'seller', 'type' => 'int'],
                ['as' => 'access', 'key' => 'access', 'type' => 'cleanInput'],
                ['as' => 'id_company', 'key' => 'company', 'type' => 'int'],
                ['as' => 'keywords', 'key' => 'keywords', 'type' => 'cleanInput'],
                ['as' => 'visibility', 'key' => 'visibility', 'type' => 'int']
            ])
        );

        if (!empty($_POST['type_company'])) {
			$conditions['type_company'] = 'company';
			if (intval($_POST['type_company']) == 2) {
				$conditions['type_company'] = 'branch';
            }
		} else {
			$conditions['type_company'] = 'all';
		}

		$library_list = $this->company->get_companies_library($conditions);
		$records_total = $this->company->count_companies_library($conditions);

		$output = array(
			'sEcho' => intval($_POST['sEcho']),
			'iTotalRecords' => $records_total,
			'iTotalDisplayRecords' => $records_total,
			'aaData' => array()
		);

		if (empty($library_list)) {
			jsonResponse('', 'success', $output);
        }

		foreach ($library_list as $doc) {
			$company_type_id = 1;
			$description = "<p class='tac'>-</p>";
			$visible = '<a class="pull-left dt_filter" data-name="visibility" href="#" data-title="Visibility" data-value="1" data-value-text="Visible">Yes</a>';

			if ($doc['type_company'] == 'branch') {
				$company_type_id = 2;
            }

			$type_company = '<a class="pull-left dt_filter" data-name="type_company" href="#" data-title="Company type" data-value="' . $company_type_id . '" data-value-text="' . $doc['type_company'] . '"><p>' . ucfirst($doc['type_company']) . '</p></a>';

			if (!empty($doc['description_file'])) {
				$description = $doc['description_file'];
			}

			if ($doc['visible_company'] == 0) {
				$visible = '<a class="pull-left dt_filter" data-name="visibility" href="#" data-title="Visibility" data-value="0" data-value-text="Invisible">No</a>';
			}

			// $banned_btn = '<a href="user/popup_forms/bann_user/' . $doc['id_user'] . '" class="fancyboxValidateModalDT fancybox.ajax ep-icon ep-icon_user-plus txt-green user_' . $doc['id_user'] . '" title="Ban this user" data-title="Ban this user" data-user="' . $doc['id_user'] . '"></a>';
			// if ($doc['status'] == 'banned') {
			// 	$banned_btn = '<a class="ep-icon ep-icon_user-minus txt-red user_' . $doc['id_user'] . ' confirm-dialog" title="Unban this user" data-message="Are you sure want to unban this user?" data-user="' . $doc['id_user'] . '" data-callback="unban_user"></a>';
			// }

			$company_logo = getDisplayImageLink(array('{ID}' => $doc['id_company'], '{FILE_NAME}' => $doc['logo_company']), 'companies.main', array( 'thumb_size' => 0 ));

            //TODO: admin chat hidden
            $btnChatUser = new ChatButton(['hide' => true, 'recipient' => $doc['id_user'], 'recipientStatus' => $doc['status']], ['classes' => 'btn-chat-now', 'text' => '']);
            $btnChat = $btnChatUser->button();
            /** @var FilesystemProviderInterface  $storageProvider */
            $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
            $publicDisk = $storageProvider->storage('public.storage');
            $pathToFile = $publicDisk->url(CompanyLibraryFilePathGenerator::libraryPath($doc['id_company'], $doc['path_file']));
			$output['aaData'][] = array(
				'id' => $doc['id_file'] . '<input type="checkbox" class="check-document pull-left" data-id-document="' . $doc['id_file'] . '">',
				'type' => '<a class="fancyboxIframe fancybox.iframe" data-w="1000" data-title="View document" href="' . getCompanyURL($doc) . '/document/' . $doc['id_file'] . '"><div class="img-b h-50 icon-files-' . $doc['extension_file'] . '-middle"></div></a><p>File size: ' . fileSizeSuffix($pathToFile) . '</p>',
				'access' => '<a class="dt_filter" data-name="access" href="#" data-title="Access" data-value="' . $doc['type_file'] . '" data-value-text="' . ucfirst($doc['type_file']) . '"><strong>' . ucfirst($doc['type_file']) . '</strong</a>',
				'company' => '<div class="img-prod pull-left mh-80 mw-100">'
						. '<img class="w-100pr" src="' . $company_logo . '" alt="' . $doc['name_company'] . '"/>'
					. '</div>'
					. '<div class="pull-right w-58pr">'
						. '<div class="pull-left w-100pr">'
							. '<div class="pull-left">'
								. '<a class="ep-icon ep-icon_filter txt-green dt_filter" data-title="Company" title="Filter by ' . $doc['name_company'] . '" data-value-text="' . $doc['name_company'] . '" data-value="' . $doc['id_company'] . '" data-name="company"></a>'
								. '<a class="ep-icon ep-icon_building" title="View company\'s profile" href="' . getCompanyURL($doc) . '"></a>'
								. '<a class="ep-icon ep-icon_video fancybox fancybox.ajax" data-title="View company video" title="View company video" href="' . __SITE_URL . 'directory/popup_forms/video_company/' . $doc['id_company'] . '"></a>'
								. '<a class="ep-icon ep-icon_visible fancybox fancybox.ajax" data-title="View details about this company" title="View details about this company" href="' . __SITE_URL . 'directory/popup_forms/company_details/' . $doc['id_user'] . '"></a>'
							. '</div>'
							. '<div class="pull-right">
									<input class="rating-bootstrap" data-filled="ep-icon ep-icon_star txt-blue fs-18 m-0" data-empty="ep-icon ep-icon_star-empty txt-blue fs-18 m-0" type="hidden" name="val" value="' . $doc['rating'] . '" data-readonly>
							</div>'
						. '</div>'
						. '<div class="clearfix"><strong class="pull-left lh-16 pr-5">Title: </strong> <a class="pull-left dt_filter" data-name="id_company" href="#" data-title="Company" data-value="' . $doc['id_company'] . '" data-value-text="' . $doc['name_company'] . '">' . $doc['name_company'] . '</a></div>'
						. '<div class="clearfix"><strong class="pull-left lh-16 pr-5">Company type: </strong>' . $type_company . '</div>'
						. '<div class="clearfix"><strong class="pull-left lh-16 pr-5">Visible: </strong>' . $visible . '</div>'
					. '</div>',
				'user' => '<div class="pull-left">'
						. '<a class="ep-icon ep-icon_filter txt-green dt_filter" data-title="Seller" title="Filter by ' . $doc['user_name'] . '" data-value-text="' . $doc['user_name'] . '" data-value="' . $doc['id_user'] . '" data-name="seller"></a>'
						. '<a class="ep-icon ep-icon_user" title="View personal page of ' . $doc['user_name'] . '" href="' . __SITE_URL . 'usr/' . strForURL($doc['user_name']) . '-' . $doc['id_user'] . '"></a>'
					. '</div>'
					. '<div class="clearfix"></div>'
					. '<span>' . $doc['user_name'] . '</span>',
				'title' => $doc['title_file'],
				'description' => $description,
				'added' => formatDate($doc['add_date_file']),
				'actions' =>
					'<a class="ep-icon ep-icon_pencil fancybox.ajax fancyboxValidateModalDT" title="Edit document" data-title="Edit document" href="seller_library/popup_forms/admin_edit_document/' . $doc['id_file'] . '"></a>'
					. '<a class="ep-icon ep-icon_remove txt-red confirm-dialog" data-callback="delete_library_file" data-message="Are you sure want delete this document?" title="Delete document" data-id="' . $doc['id_file'] . '"></a>'
					. $btnChat
			);
		}

		jsonResponse('', 'success', $output);
	}

	function ajax_company_photo_operation() {
		if (!isAjaxRequest()) {
			show_404();
        }

		if (!logged_in()) {
			jsonResponse(translate("systmess_error_should_be_logged"));
        }

		if (!have_right('moderate_content')) {
			jsonResponse('Error: You have no rights to moderate questions.');
        }

		$this->_load_main();
		$type = $this->uri->segment(3);

		$ids = cleanInput($_POST['id']);
		switch ($type) {
			case 'check_new':
				$lastId = $_POST['lastId'];
				$photos_count = $this->company->get_count_new_photos($lastId);

				if ($photos_count) {
					$last_photos_id = $this->company->get_photos_last_id();
					jsonResponse('', 'success', array('nr_new' => $photos_count, 'lastId' => $last_photos_id));
				} else {
					jsonResponse('Error: New photos do not exist');
                }
				break;
			case 'moderate':
				if ($this->company->moderatePhotos($ids)) {
					if (isset($_POST['multiple'])) {
						jsonResponse('Photos have been successfully moderated', 'success');
                    } else {
						jsonResponse('The photo has been successfully moderated', 'success');
                    }
				} else
					jsonResponse('Error: You cannot moderate this photo now. Please try again later.');
			break;
			case 'delete':
				$ids_to_delete = '';

				// get images name to delete
				$photos = $this->company->get_companies_photos(array('id_photo' => $ids));
                /** @var FilesystemProviderInterface  $storageProvider */
                $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                $publicDisk = $storageProvider->storage('public.storage');

				foreach ($photos as $photo) {
                    try{
                        $publicDisk->delete(CompanyPhotosFilePathGenerator::photosPath($photo['id_company'], $photo['path_photo']));
                        foreach(CompanyPhotosThumb::cases() as $thumb){
                            $publicDisk->delete(CompanyPhotosFilePathGenerator::thumbImage($photo['id_company'], $photo['path_photo'], $thumb));
                        }
                    } catch (UnableToDeleteFile $e) {
                        jsonResponse(translate('seller_pictures_cannot_delete_now_message'));
                    }
					// save id to delete from database
					$ids_to_delete .= $ids;
				}

				if ($this->company->delete_company_photo($ids_to_delete)) {
					$this->load->model('User_Statistic_Model', 'statistic');
					$wrote_statistic = $this->statistic->prepare_user_array(
						$photos,
						'id_user',
						array('company_posts_pictures' => -1)
					);

					$this->statistic->set_users_statistic($wrote_statistic);
					$this->company->delete_company_photo_comments($ids_to_delete);
					if (isset($_POST['multiple']) && intval($_POST['multiple'])) {
						jsonResponse('Photos were deleted', 'success');
                    } else {
						jsonResponse('Photo was deleted', 'success');
                    }
				} else {
					jsonResponse('Photo was not deleted');
                }

				break;
		}
	}

	function ajax_company_video_operation() {
		if (!isAjaxRequest()) {
			show_404();
        }

		if (!logged_in()) {
			jsonResponse(translate("systmess_error_should_be_logged"));
        }

		if (!have_right('moderate_content')) {
			jsonResponse('Error: You have no rights to moderate questions.');
        }

		$this->_load_main();
		$op = $this->uri->segment(3);

		$ids = cleanInput($_POST['id']);
		switch ($op) {
			case 'check_new':
				$lastId = $_POST['lastId'];
				$videos_count = $this->company->get_count_new_videos($lastId);

				if ($videos_count) {
					$last_videos_id = $this->company->get_videos_last_id();
					jsonResponse('', 'success', array('nr_new' => $videos_count, 'lastId' => $last_videos_id));
				} else {
					jsonResponse('Error: New videos do not exist');
                }
				break;
            case 'moderate':
				if ($this->company->moderateVideos(explode(',', $ids))) {
					if (isset($_POST['multiple'])) {
						jsonResponse('Videos have been moderated', 'success');
                    } else {
						jsonResponse('Video has been moderated', 'success');
                    }
				} else
					jsonResponse('Error: You cannot moderate this video now. Please try again later.');
				break;
			case 'delete':
				// get images name to delete
				$videos = $this->company->get_companies_videos(array('id_video' => $ids));
                /** @var FilesystemProviderInterface  $storageProvider */
                $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                $publicDisk = $storageProvider->storage('public.storage');

				foreach ($videos as $video) {
                    try {
                        $publicDisk->delete(CompanyVideosFilePathGenerator::videosPath($videos[0]['id_company'], $video['image_video']));
                        foreach(SellerVideosPhotosThumb::cases() as $thumb){
                            $publicDisk->delete(CompanyVideosFilePathGenerator::thumbImage($videos[0]['id_company'], $video['image_video'], $thumb));
                        }
                    } catch (UnableToDeleteFile $e) {
                        //well, it's not terrible
                    }
				}

				if ($this->company->delete_company_video($ids)) {
					$this->load->model('User_Statistic_Model', 'statistic');
					$wrote_statistic = $this->statistic->prepare_user_array(
						$videos,
						'id_user',
						array('company_posts_videos' => -1)
					);

					$this->statistic->set_users_statistic($wrote_statistic);
					$this->company->delete_company_video_comments($ids);
					if (isset($_POST['multiple']) && intval($_POST['multiple'])) {
						jsonResponse('Videos were deleted.', 'success');
                    } else {
						jsonResponse('Video was deleted.', 'success');
                    }
				} else
					jsonResponse('Video was not deleted.');

				break;
		}
	}

	function ajax_company_news_operation() {
		if (!isAjaxRequest()) {
			show_404();
        }

		if (!logged_in()) {
			jsonResponse(translate("systmess_error_should_be_logged"));
        }

		if (!have_right('moderate_content')) {
			jsonResponse('Error: You have no rights to moderate questions.');
        }

		$this->_load_main();
		$op = $this->uri->segment(3);

		$ids = cleanInput($_POST['id']);
		switch ($op) {
			case 'check_new':
				$lastId = $_POST['lastId'];
				$news_count = $this->company->get_count_new_news($lastId);

				if ($news_count) {
					$last_news_id = $this->company->get_news_last_id();
					jsonResponse('', 'success', array('nr_new' => $news_count, 'lastId' => $last_news_id));
				} else {
					jsonResponse('Error: This news does not exist');
                }
				break;
			case 'moderate':
				if ($this->company->moderateNews($ids)) {
					if (isset($_POST['multiple'])) {
						jsonResponse('News has been moderated', 'success');
                    } else {
						jsonResponse('News has been moderated', 'success');
                    }
				} else
					jsonResponse('Error: You cannot moderate this news now. Please try again later.');
				break;
			case 'delete':
				// get images name to delete
				$newsList = $this->company->get_companies_news(array('id_news' => $ids));
                /** @var FilesystemProviderInterface $storageProvider */
                $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                $publicDisk = $storageProvider->storage('public.storage');
				foreach ($newsList as $news) {
                    if ($news['image_news']) {
						$path = CompanyNewsFilePathGenerator::newsPath($news['id_company'], $news['image_news']);
						try {
							$publicDisk->delete($path);

							$thumbs = SellerNewsPhotoThumb::cases();
							foreach ($thumbs as $thumb) {
								try {
									$publicDisk->delete(CompanyNewsFilePathGenerator::thumbImage($news['id_company'], $news['image_news'], $thumb));
								} catch (UnableToDeleteFile $e) {
									jsonResponse(translate('validation_images_delete_fail'));
								}
							}
						} catch (UnableToDeleteFile $e) {
							jsonResponse(translate('validation_images_delete_fail'));
						}
					}
            	}

				if ($this->company->delete_company_news($ids)) {
					$this->load->model('User_Statistic_Model', 'statistic');
					$wrote_statistic = $this->statistic->prepare_user_array(
						$newsList,
						'id_seller',
						array('company_posts_news' => -1)
					);

					$this->statistic->set_users_statistic($wrote_statistic);

					$this->company->delete_company_news_comments($ids);
					if (isset($_POST['multiple']) && intval($_POST['multiple'])) {
						jsonResponse('The news was deleted.', 'success');
                    } else {
						jsonResponse('The news was deleted.', 'success');
                    }
				} else
					jsonResponse('The news was not deleted.');

				break;
			case 'edit_news':
				$validator_rules = array(
					array(
						'field' => 'title',
						'label' => 'News title',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'text',
						'label' => 'News text',
						'rules' => array('required' => '')
					)
				);
				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate()) {
					jsonResponse($this->validator->get_array_errors());
				}
				$this->load->model('Seller_News_Model', 'seller_news');
				$data = array(
					'title_news' => cleanInput($_POST['title']),
					'text_news' => $_POST['text']
				);
				if ($this->seller_news->updateSellerNews((int) $_POST['id'], $data)) {
					jsonResponse('The news was saved successfully.', 'success');
				} else {
					jsonResponse('Error: You cannot update this news now. Please try again later.');
				}
				break;
			case 'delete_image':
				$this->load->model('Seller_News_Model', 'seller_news');
				$news = $this->seller_news->getNews(intval($_POST['news']));

                /** @var FilesystemProviderInterface $storageProvider */
                $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                $publicDisk = $storageProvider->storage('public.storage');
				$path = CompanyNewsFilePathGenerator::newsPath($news['id_company'], $news['image_news']);
                try {
                    $publicDisk->delete($path);
                    $thumbs = SellerNewsPhotoThumb::cases();
                    foreach($thumbs as $thumb){
                        $publicDisk->delete(CompanyNewsFilePathGenerator::thumbImage($news['id_company'], $news['image_news'], $thumb));
                    }
                } catch (UnableToDeleteFile $e) {
                    jsonResponse(translate('validation_images_delete_fail'));
                }

				if ($this->seller_news->updateSellerNews(intval($_POST['news']), array('image_news' => '', 'image_thumb_news' => ''))) {
					jsonResponse('The news photo was removed.', 'success');
				} else {
					jsonResponse('Error: You cannot remove this news photo now. Please try again later.');
				}
				break;
		}
	}

	function ajax_company_updates_operation() {
		if (!isAjaxRequest()) {
			show_404();
        }

		if (!logged_in()) {
			jsonResponse(translate("systmess_error_should_be_logged"));
        }

		if (!have_right('moderate_content')) {
			jsonResponse('Error: You have no rights to moderate questions.');
        }

		$this->_load_main();
		$op = $this->uri->segment(3);

		switch ($op) {
			case 'check_new':
				$lastId = $_POST['lastId'];
				$updates_count = $this->company->get_count_new_updates($lastId);

				if ($updates_count) {
					$last_updates_id = $this->company->get_updates_last_id();
					jsonResponse('', 'success', array('nr_new' => $updates_count, 'lastId' => $last_updates_id));
				} else {
					jsonResponse('Error: New updates do not exist');
                }
			break;
			case 'delete':
				if(empty($_POST['update'])) {
					jsonResponse('The update was not deleted.');
                }
				$ids = array();

				foreach($_POST['update'] as $id) {
					$ids[] = intval($id);
                }

				$ids = implode(',', $ids);
				$updates = $this->company->get_companies_updates(array('id_updates' => $ids));
				foreach ($updates as $update) {
                    /** @var FilesystemProviderInterface $storageProvider */
                    $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                    $publicDisk = $storageProvider->storage('public.storage');
                    $path = CompanyUpdatesFilePathGenerator::updatesPath($update['id_company'], $update['photo_path']);
                    try {
                        $publicDisk->delete($path);
                        foreach(SellerUpdatesPhotoThumb::cases() as $thumb){
                            $publicDisk->delete(CompanyUpdatesFilePathGenerator::thumbImage($update['id_company'], $update['photo_path'], $thumb));
                        }
                    } catch (UnableToDeleteFile $e) {
                        jsonResponse(translate('seller_updates_failed_delete_message'));
                    }
				}

				if ($this->company->delete_company_updates($ids)) {
					$this->load->model('User_Statistic_Model', 'statistic');
					$wrote_statistic = $this->statistic->prepare_user_array(
						$updates,
						'id_user',
						array('company_posts_updates' => -1)
					);

					$this->statistic->set_users_statistic($wrote_statistic);

					jsonResponse('Update(s) was deleted.', 'success');
				} else {
					jsonResponse('Update(s) was not deleted.');
                }

			break;
			case 'edit_update':
				$validator_rules = array(
					array(
						'field' => 'text',
						'label' => 'Update message',
						'rules' => array('required' => '', 'html_max_len[250]' => '')
					),
					array(
						'field' => 'id',
						'label' => 'Update information',
						'rules' => array('required' => '')
					)
				);
				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate()) {
					jsonResponse($this->validator->get_array_errors());
				}
				$this->load->model('Seller_Updates_Model', 'seller_updates');
				$id_update = intVal($_POST['id']);
				$updateColumn = array(
					'text_update' => $_POST['text']
				);

				if ($this->seller_updates->change_update($id_update, $updateColumn)) {
					jsonResponse('All changes have been successfully saved.', 'success');
				} else {
					jsonResponse('Error: You cannot save any changes now. Please try again later.');
				}
			break;
			case 'delete_image':
				$this->load->model('Seller_Updates_Model', 'seller_updates');
				$update = $this->seller_updates->get_update(intval($_POST['update']));
				/** @var FilesystemProviderInterface $storageProvider */
                $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                $publicDisk = $storageProvider->storage('public.storage');
                $path = CompanyUpdatesFilePathGenerator::updatesPath($update['id_company'], $update['photo_path']);
                try {
                    $publicDisk->delete($path);
                    foreach(SellerUpdatesPhotoThumb::cases() as $thumb){
                        $publicDisk->delete(CompanyUpdatesFilePathGenerator::thumbImage($update['id_company'], $update['photo_path'], $thumb));
                    }
                } catch (UnableToDeleteFile $e) {
                    jsonResponse(translate('seller_updates_failed_delete_message'));
                }

				if ($this->seller_updates->change_update(intval($_POST['update']), array('photo_path' => ''))) {
					jsonResponse('Update photo was removed.', 'success');
				} else {
					jsonResponse('Error: You cannot remove update photo now. Please try again later.');
				}
			break;
		}
	}

	function ajax_company_library_operation() {
		if (!isAjaxRequest()) {
			show_404();
        }

		if (!logged_in()) {
			jsonResponse(translate("systmess_error_should_be_logged"));
        }

		if (!have_right('moderate_content')) {
			jsonResponse('Error: You have no rights to moderate questions.');
        }

		$this->_load_main();
		$op = $this->uri->segment(3);

		$ids = cleanInput($_POST['id']);
		switch ($op) {
			case 'check_new':
				$lastId = $_POST['lastId'];
				$libraries_count = $this->company->get_count_new_libraries($lastId);

				if ($libraries_count) {
					$last_libraries_id = $this->company->get_libraries_last_id();
					jsonResponse('', 'success', array('nr_new' => $libraries_count, 'lastId' => $last_libraries_id));
				} else
					jsonResponse('Error: New libraries do not exist');
				break;
			case 'delete':
				$documents = $this->company->get_companies_library(array('id_file' => $ids));
                /** @var FilesystemProviderInterface  $storageProvider */
                $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                $publicDisk = $storageProvider->storage('public.storage');
				foreach ($documents as $file) {
                    $path = CompanyLibraryFilePathGenerator::libraryPath($file['id_company'], $file['path_file']);
                    try {
                        $publicDisk->delete($path);
                    } catch (UnableToDeleteFile $e) {
                        jsonResponse('Failed to delete the document');
                    }
				}
				if ($this->company->delete_company_documents($ids)) {
					$this->load->model('User_Statistic_Model', 'statistic');
					$wrote_statistic = $this->statistic->prepare_user_array(
						$documents,
						'id_seller',
						array('company_posts_library' => -1)
					);

					$this->statistic->set_users_statistic($wrote_statistic);
					if (isset($_POST['multiple']) && intval($_POST['multiple'])) {
						jsonResponse('The documents were deleted.', 'success');
                    } else {
						jsonResponse('The document was deleted.', 'success');
                    }
				} else
					jsonResponse('The document was not deleted.');
				break;
			case 'edit_document':
				$validator_rules = array(
					array(
						'field' => 'title',
						'label' => 'Headline or title of your document',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'file_type',
						'label' => 'Type of your document',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'text',
						'label' => 'Document description',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'id',
						'label' => 'Document info',
						'rules' => array('required' => '')
					)
				);
				$this->validator->set_rules($validator_rules);

				if (!$this->validator->validate()) {
					jsonResponse($this->validator->get_array_errors());
				}
				$this->load->model('Seller_Library_Model', 'seller_library');
				$id_document = intVal($_POST['id']);
				$updateColumn = array(
					'description_file' => cleanInput($_POST['text']),
					'title_file' => cleanInput($_POST['title']),
					'type_file' => cleanInput($_POST['file_type'])
				);

				if ($this->seller_library->update_document($id_document, $updateColumn)) {
					jsonResponse('All changes have been successfully saved.', 'success');
				} else {
					jsonResponse('Error: You cannot save any changes now. Please try again later.');
				}
				break;
		}
    }


    /**************************************** Scopes ****************************************/

    /**
     * Scope a query to filter by message notification id.
     *
     * @param int|string $id_message
     */
    protected function scopeNotificationMessage(QueryBuilder $builder, int $id_message)
    {
        $builder->where(
            $builder->expr()->eq(
                'id_message',
                $builder->createNamedParameter($id_message, ParameterType::INTEGER, $this->nameScopeParameter('id_message'))
            )
        );
    }

}
