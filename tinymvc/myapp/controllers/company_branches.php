<?php

use App\Common\DependencyInjection\ServiceLocator\LibraryLocator;
use App\Common\Validation\ConstraintViolationInterface;
use App\Common\Validation\ValidationException;
use App\Filesystem\CompanyLogoFilePathGenerator;
use App\Filesystem\CompanyPhotosFilePathGenerator;
use App\Filesystem\CompanyVideoFilePathGenerator;
use App\Filesystem\FilePathGenerator;
use App\Messenger\Message\Command\ElasticSearch\RemoveB2bRequest;
use App\Services\PhoneCodesService;
use ExportPortal\Bridge\Symfony\Component\Messenger\MessengerInterface;
use ExportPortal\Contracts\Filesystem\Exception\ReadException;
use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToWriteFile;
use TinyMVC_Library_Image_intervention as LegacyImageHandler;

/**
 * Company_Services_Controller
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 */
class Company_Branches_Controller extends TinyMVC_Controller {
	private $breadcrumbs = array();
	private $image_logo = array(
		'min_height' => 140,
		'min_width' => 250
	);

	private $image_photos = array(
		'min_height' => 600,
		'min_width' => 600
	);

    function index() {
        header('location: ' . __SITE_URL);
    }

    private function load_main(){
        $this->load->model('User_Model', 'user');
        $this->load->model('Company_Model', 'company');
        $this->load->model('Category_Model', 'category');
        $this->load->model('Branch_Model', 'branch');
    }

	function my(){
		checkIsLogged();
		checkPermision('manage_branches');

		if (!i_have_company()) {
            $this->session->setMessages(translate("systmess_error_must_have_company_to_access_page"), 'errors');
            headerRedirect();
        }

		checkGroupExpire();

		$this->load_main();

		// Select user navigation menu active item
		$data['company_types'] = $this->company->get_company_types();

		$this->view->assign('title', 'Company branches');
		$this->view->assign($data);
		$this->view->display('new/header_view');
		$this->view->display('new/directory/branch/my/index_view');
		$this->view->display('new/footer_view');
    }

    function ajax_list_branches_dt() {
        if (!isAjaxRequest())
            show_404();

        if(!logged_in())
			headerRedirect(__SITE_URL . 'login');

		if (!have_right('manage_branches')){
			$this->session->setMessages(translate("systmess_error_page_permision"),'errors');
			headerRedirect(__SITE_URL);
		}

		if (!i_have_company()) {
            $this->session->setMessages(translate("systmess_error_must_have_company_to_access_page"), 'errors');
            headerRedirect();
        }

        $this->load_main();

        $conditions = array();
		$conditions['limit'] = (int) $_POST['iDisplayStart'] . ',' .  (int) $_POST['iDisplayLength'];

        $conditions['seller'] = privileged_user_id();
		$conditions['type_company'] = "branch";

		if (!empty($_POST['start_date']) && validateDate($_POST['start_date'], 'm/d/Y')) {
			$conditions['added_start'] = getDateFormat($_POST['start_date'], 'm/d/Y', 'Y-m-d 00:00:00');
		}

		if (!empty($_POST['finish_date']) && validateDate($_POST['finish_date'], 'm/d/Y')) {
			$conditions['added_finish'] = getDateFormat($_POST['finish_date'], 'm/d/Y', 'Y-m-d 23:59:59');
		}

        if (!empty($_POST['country']))
            $conditions['country'] = intval($_POST['country']);

        if (!empty($_POST['state']))
            $conditions['state'] = intval($_POST['state']);

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

        if (!empty($_POST['visibility_company'])) {
            $conditions['visibility'] = 1;
            if (intval($_POST['visibility_company']) == 2)
            	$conditions['visibility'] = 0;
        }

        if (!empty($_POST['keywords']))
            $conditions['keywords'] = cleanInput(cut_str($_POST['keywords']));

        if (!empty($_POST['type'])){
			$conditions['type'] = intval($_POST['type']);
		}

		$sort_by = flat_dt_ordering($_POST, array(
			'dt_company' => 'name_company',
			'dt_country' => 'country',
			'dt_registered' => 'registered_company',
			'dt_type' => 'name_type',
			'dt_state' => 'state',
			'dt_city' => 'city'
		));

		if(!empty($sort_by)){
			$conditions['multiple_sort_by'] = $sort_by;
		}

        $conditions['get_administration_info'] = 1;

        $data['companies_list'] = $this->branch->get_companies($conditions);
        $records_total = $this->branch->count_companies($conditions);

        $output = array(
            "sEcho" => intval($_POST['sEcho']),
            "iTotalRecords" => $records_total,
            "iTotalDisplayRecords" => $records_total,
            "aaData" => array()
        );

		if(empty($data['companies_list']))
			jsonResponse('', 'success', $output);

		$output['aaData'] = $this->_my_company_branches($data);

		jsonResponse('', 'success', $output);
    }

	private function _my_company_branches($data){
		extract($data);

		// social fields
		if(!user_type('users_staff')){
			$id_user = id_session();
			$group = group_session();
		} else{
			$id_user = privileged_user_id();
			$user_info = $this->user->getSimpleUser($id_user);
			$group = $user_info['user_group'];
		}

		$this->load->model('UserGroup_Model', 'groups');
		$fields_social = $this->groups->getFiledsByGroup($group, "'social'");
		$fields_values = $this->groups->getUsersRightFields($id_user);
		$social_links = "";
		foreach($fields_social as $field_social){
			if(!empty($fields_values[$field_social['id_right']])){
				if($field_social['name_field'] == 'Skype'){
					$social_links .= '<a href="skype:'.$fields_values[$field_social['id_right']].'"><i class="fs-20 mr-5 ep-icon ep-icon_'.$field_social['icon'].'"></i></a>';
				} else{
					$social_links .= '<a href="'.$fields_values[$field_social['id_right']].'" target="_blank"><i class="fs-20 mr-5 ep-icon ep-icon_'.$field_social['icon'].'"></i></a>';
				}
			}
		}

		if ($social_links == "")
			$social_links = "-";

		$module = 'company_branches.main';

        foreach ($companies_list as $company) {
            $city = "-";
            $categories_str = array();
			$industries_str = array();
			$visible_icon = '';

            $city = '<div class="txt-gray">' . $company['city'] . '</div>';
			$logo = getDisplayImageLink(array('{ID}' => $company['id_company'], '{FILE_NAME}' => $company['logo_company']), $module, array( 'thumb_size' => 1 ));

            if ($company['visible_company'] == 0){
                $visible_icon = '<i class="ep-icon ep-icon_invisible mr-5"></i>';
			}

			$all_actions = '<div class="dropdown">
				<a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					<i class="ep-icon ep-icon_menu-circles"></i>
				</a>

				<div class="dropdown-menu dropdown-menu-right">';

			if(in_session('companies', $company['id_company'])){
				$all_actions .= '
						<a class="dropdown-item fancybox.ajax fancyboxValidateModal" data-title="Edit company" title="Edit company" href="' . __SITE_URL . 'company_branches/popup_forms/edit_branch/' . $company['id_company'] . '">
							<i class="ep-icon ep-icon_pencil"></i><span class="txt">Edit</span>
						</a>';

				if($company['visible_company'] == 1){
					$all_actions .= '<a class="dropdown-item confirm-dialog" data-message="Are you sure you want to perform this operation?" data-state="visible" data-callback="change_visibility" title="Set company invisible" data-company="'. $company['id_company'].'">
										<i class="ep-icon ep-icon_invisible"></i><span class="txt">Set invisible</span>
									</a>';
				} else{
					$all_actions .= '<a class="dropdown-item confirm-dialog" data-message="Are you sure you want to perform this operation?" data-state="invisible" data-callback="change_visibility" title="Set company visible" data-company="'. $company['id_company'].'">
										<i class="ep-icon ep-icon_visible"></i><span class="txt">Set visible</span>
									</a>';
				}

				$all_actions .= '<a class="dropdown-item confirm-dialog" data-message="Are you sure you want to delete this branch?" data-callback="delete_branch" title="Delete company branch" data-company="'. $company['id_company'].'">
							<i class="ep-icon ep-icon_trash-stroke"></i><span class="txt">Delete</span>
						</a>';
			}

			$all_actions .= '<a class="dropdown-item call-function" data-callback="companyInformationFancybox" title="View company info" href="#info-company-'. $company['id_company'].'">
						<i class="ep-icon ep-icon_info-stroke"></i><span class="txt">Company info</span>
					</a>';

			$all_actions .= '</div>
			</div>';

            $output[] = array(
                "logo"          =>  '<div class="img-list-b relative-b w-170 h-140 display-tc vam"><img class="mw-140" src="' . $logo . '" alt="'.$company['name_company'].'"/></div>',
                "email"         =>  $company['email_company'],
                "phone"         =>  $company['phone_company'],
                "fax"           =>  $company['fax_company'],
                "longitude"     =>  $company['longitude'],
                "latitude"      =>  $company['latitude'],
                "address"       =>  $company['address_company'],
                "zip"           =>  $company['zip_company'],
                "description"   =>  $company['description_company'],
                "employees"     =>  $company['employees_company'],
                "revenue"       =>  $company['revenue_company'],
				"social"        =>  $social_links,

				"dt_company"    =>  '
				<div class="flex-card">
					<div class="flex-card__fixed main-data-table__item-img image-card">
						<span class="link">
							<img class="image" src="' . $logo . '" alt="' . $row['title'] . '"/>
						</span>
					</div>
					<div class="flex-card__float">
						<div class="main-data-table__item-ttl">'
							. $visible_icon .
							'<a class="display-ib link-black txt-medium" href="'.__SITE_URL.'branch/'.strForURL($company['name_company'].' '.$company['id_company']).'">' . $company['name_company'] . '</a>
						</div>'
						. $company['name_type'] .
					'</div>
				</div>',
				"dt_country"    =>
					'<div>
						<img width="24" height="24" src="' . getCountryFlag($company['country']) . '" title="' . $company['country'] . '" alt="' . $company['country'] .'"/> '
						. $company['country'] .'
					</div>'
					. $city,
                "dt_registered" =>  formatDate($company['registered_company']),
				"dt_actions"    => $all_actions
            );
        }

		return $output;
	}

    function popup_forms() {
		checkIsAjax();
		checkIsLoggedAjaxModal();
		checkPermisionAjaxModal('manage_branches');

		if (!i_have_company()){
			messageInModal('Error: To add branch, you must have a company.');
		}

        $action = cleanInput($this->uri->segment(3));
        $this->load_main();

        switch ($action) {
            case 'add_branch':
                $this->load->model("Country_model", 'country');
				$data['upload_folder'] = encriptedFolderName();

				$industries = model('category')->get_industries();

				$company_industries = array_keys(
					arrayByKey(
						array_filter((array) model('category')->getCategories([
							'columns'  => "category_id, name, parent, cat_childrens, p_or_m",
							'cat_list' => array_column(
								array_filter((array) model('company')->get_relation_industry_by_id((int) my_company_id(), false, array('limit' => '0,3'))),
								'id_industry'
							),
						])),
						'category_id'
					)
				);
				// $company_categories = array_filter((array) model('category')->getCategories([
				// 	'columns'  => "category_id, name, parent, cat_childrens",
				// 	'cat_list' => array_column(
				// 		array_filter((array) model('company')->get_relation_category_by_company_id((int) my_company_id())),
				// 		'id_category'
				// 	),
				// ]));

				// $company_industries = array_intersect_key(
				// 	arrayByKey(
				// 		array_filter((array) model('category')->getCategories([
				// 			'columns'  => "category_id, name, parent, cat_childrens, p_or_m",
				// 			'cat_list' => array_column(
				// 				array_filter((array) model('company')->get_relation_industry_by_id((int) my_company_id(), false, array('limit' => '0,3'))),
				// 				'id_industry'
				// 			),
				// 		])),
				// 		'category_id'
				// 	),
				// 	$selected_categories = arrayByKey($company_categories, 'parent', true)
				// );

				$data['multipleselect_industries'] = array(
					'industries'                => $industries,
					'industries_top'            => $company_industries,
					'max_industries'            => (int) config('multipleselect_max_industries', 3),
				);

                $data['types'] = $this->company->get_company_types();
				$data['port_country'] = $this->country->fetch_port_country();

				//region Phone & Fax codes
				$company = model('company')->get_company(array('id_company' => (int) my_company_id()));
				$phone_codes_service = new PhoneCodesService(model('country'));
				$phone_codes = $fax_codes = $phone_codes_service->getCountryCodes();

				//region Phone code
				$phone_code = $phone_codes_service->findAllMatchingCountryCodes(
					!empty($company['id_phone_code_company']) ? (int) $company['id_phone_code_company'] : null,
					!empty($company['phone_code_company']) ? (string) $company['phone_code_company'] : null, // Fallback to old phone code system
					!empty($company['id_country']) ? (int) $company['id_country'] : null, // Or falling back to parent company country
					PhoneCodesService::SORT_BY_PRIORITY
				)->first();
				//endregion Phone code

				//region Fax code
				$fax_code = $phone_codes_service->findAllMatchingCountryCodes(
					!empty($company['id_fax_code_company']) ? (int) $company['id_fax_code_company'] : null,
					!empty($company['fax_code_company']) ? (string) $company['fax_code_company'] : null, // Fallback to old phone code system
					!empty($company['id_country']) ? (int) $company['id_country'] : null, // Or falling back to parent company country
					PhoneCodesService::SORT_BY_PRIORITY
				)->first();
				//endregion Fax code

				$data['fax_codes'] = $fax_codes;
				$data['phone_codes'] = $phone_codes;
				$data['selected_fax_code'] = $fax_code;
				$data['selected_phone_code'] = $phone_code;
				//endregion Phone & Fax codes

				$module_main ='company_branches.main';
				$mime_main_properties = getMimePropertiesFromFormats(config("img.{$module_main}.rules.format"));

				$data['fileupload_crop'] = array(
					'link_main_image'        => getDisplayImageLink(array('{ID}' => 'none', '{FILE_NAME}' => 'none'), $module_main),
					'link_thumb_main_image'  => getDisplayImageLink(array('{ID}' => 'none', '{FILE_NAME}' => 'none'), $module_main, array('thumb_size' => 1)),
					'title_text_popup'       => 'Logo',
					'btn_text_save_picture'  => 'Set new logo',
					'croppper_limit_by_min'  => true,
					'rules'                  => config("img.{$module_main}.rules"),
					'url'                    => array(
													'upload' => __SITE_URL . "company_branches/ajax_branch_upload_logo/"
												),
					'accept' => arrayGet($mime_main_properties, 'accept'),
				);

				$module_photos ='company_branches.photos';
				$mime_photos_properties = getMimePropertiesFromFormats(config("img.{$module_photos}.rules.format"));

				$data['fileupload_photos'] = array(
					'limits'    => array(
						'amount'            => array(
							'total'   => (int) config("img.{$module_photos}.limit"),
							'current' => 0,
						),
						'accept'            => arrayGet($mime_photos_properties, 'accept'),
						'formats'           => arrayGet($mime_photos_properties, 'formats'),
						'mimetypes'         => arrayGet($mime_photos_properties, 'mimetypes'),
					),
					'rules'             => config("img.{$module_photos}.rules"),
					'url'       => array(
						'upload' => __SITE_URL . "company_branches/ajax_branch_upload_pictures/".$data['upload_folder'],
						'delete' => __SITE_URL . "company_branches/ajax_branch_delete_pictures/".$data['upload_folder'],
					),
				);

				// array(
				// 	'limit' => config('branch_photo_limit'),
				// 	'fileupload_max_file_size_placeholder' => config('fileupload_max_file_size_placeholder'),
				// 	'fileupload_max_file_size' => config('fileupload_max_file_size'),
				// 	'min_width' => $this->image_photos['min_width'],
				// 	'min_height' => $this->image_photos['min_height'],
				// 	'formats' => 'jpg,jpeg,png,gif,bmp',
				// );

				$this->view->assign($data);
				$this->view->display("new/directory/branch/branch_form_view");
            break;
            case 'edit_branch':
                $id_branch = intval($this->uri->segment(4));
                $this->load->model("Country_model", 'country');
                $data['upload_folder'] = encriptedFolderName();

				if(!in_session('companies', $id_branch)){
					messageInModal(translate("systmess_error_rights_perform_this_action"));
				}

                // Get branch info
				$data['branch'] = $company = $this->company->get_company(array('id_company' => $id_branch, 'id_user'=>privileged_user_id(), 'type_company' => 'branch'));

				// Validation branch info
                if(empty($data['branch'])){
                    messageInModal(translate("systmess_error_rights_perform_this_action"));
				}

				$data['types'] = $this->company->get_company_types();
                // Preparing data to use in edit form view

				$branch_images = $this->branch->get_branch_images(array('id_company' => $id_branch));
                if(!empty($branch_images)){
                    foreach($branch_images as $key => $image){
                        $images[$key]['id_photo'] = $image['id_photo'];
                        $images[$key]['photo_name'] = $image['path_photo'];
                        $images[$key]['thumbs'] = unserialize($image['thumbs_photo']);
                    }
                    $data['branch']['pictures'] = $images;
                }

                $data['port_country'] = $this->country->fetch_port_country();
                if($data['branch']['id_country'] != 0){
                    $data['states'] = $this->country->get_states($data['branch']['id_country']);
                }

				$data['city_selected'] = $this->country->get_city($data['branch']['id_city']);

				//region Phone & Fax codes
				$phone_codes_service = new PhoneCodesService(model('country'));
				$phone_codes = $fax_codes = $phone_codes_service->getCountryCodes();

				//region Phone code
				$phone_code = $phone_codes_service->findAllMatchingCountryCodes(
					!empty($company['id_phone_code_company']) ? (int) $company['id_phone_code_company'] : null,
					!empty($company['phone_code_company']) ? (string) $company['phone_code_company'] : null, // Fallback to old phone code system
					!empty($company['id_country']) ? (int) $company['id_country'] : null, // Or falling back to parent company country
					PhoneCodesService::SORT_BY_PRIORITY
				)->first();
				//endregion Phone code

				//region Fax code
				$fax_code = $phone_codes_service->findAllMatchingCountryCodes(
					!empty($company['id_fax_code_company']) ? (int) $company['id_fax_code_company'] : null,
					!empty($company['fax_code_company']) ? (string) $company['fax_code_company'] : null, // Fallback to old phone code system
					!empty($company['id_country']) ? (int) $company['id_country'] : null, // Or falling back to parent company country
					PhoneCodesService::SORT_BY_PRIORITY
				)->first();
				//endregion Fax code

				$data['fax_codes'] = $fax_codes;
				$data['phone_codes'] = $phone_codes;
				$data['selected_fax_code'] = $fax_code;
				$data['selected_phone_code'] = $phone_code;
				//endregion Phone & Fax codes

				// $data['image_logo'] = array(
				// 	'limit' => 1,
				// 	'fileupload_max_file_size_placeholder' => config('fileupload_max_file_size_placeholder'),
				// 	'fileupload_max_file_size' => config('fileupload_max_file_size'),
				// 	'min_width' => $this->image_logo['min_width'],
				// 	'min_height' => $this->image_logo['min_height'],
				// 	'formats' => 'jpg,jpeg,png,gif,bmp',
				// );

				// $data['image_photos'] = array(
				// 	'limit' => config('branch_photo_limit'),
				// 	'fileupload_max_file_size_placeholder' => config('fileupload_max_file_size_placeholder'),
				// 	'fileupload_max_file_size' => config('fileupload_max_file_size'),
				// 	'min_width' => $this->image_photos['min_width'],
				// 	'min_height' => $this->image_photos['min_height'],
				// 	'formats' => 'jpg,jpeg,png,gif,bmp',
				// );

				$module_main ='company_branches.main';
				$mime_main_properties = getMimePropertiesFromFormats(config("img.{$module_main}.rules.format"));

				$data['fileupload_crop'] = array(
					'link_main_image'        => getDisplayImageLink(array('{ID}' => $id_branch, '{FILE_NAME}' => $data['branch']['logo_company']), $module_main),
					'link_thumb_main_image'  => getDisplayImageLink(array('{ID}' => $id_branch, '{FILE_NAME}' => $data['branch']['logo_company']), $module_main, array('thumb_size' => 1)),
					'title_text_popup'       => 'Logo',
					'btn_text_save_picture'  => 'Set new logo',
					'croppper_limit_by_min'  => true,
					'rules'                  => config("img.{$module_main}.rules"),
					'url'                    => array(
													'upload' => __SITE_URL . "company_branches/ajax_branch_upload_db_logo/".$id_branch
												),
					'accept' => arrayGet($mime_main_properties, 'accept'),
				);

				$module_photos ='company_branches.photos';
				$mime_photos_properties = getMimePropertiesFromFormats(config("img.{$module_photos}.rules.format"));

				$data['fileupload_photos'] = array(
					'limits'          => array(
						'amount'      => array(
							'total'   => (int) config("img.{$module_photos}.limit"),
							'current' => count($branch_images),
						),
						'accept'      => arrayGet($mime_photos_properties, 'accept'),
						'formats'     => arrayGet($mime_photos_properties, 'formats'),
						'mimetypes'   => arrayGet($mime_photos_properties, 'mimetypes'),
					),
					'rules'           => config("img.{$module_photos}.rules"),
					'url'             => [
											'upload' => __SITE_URL . "company_branches/ajax_branch_upload_pictures/{$data['upload_folder']}/{$id_branch}",
											'delete' => __SITE_URL . "company_branches/ajax_branch_delete_pictures/{$data['upload_folder']}/{$id_branch}",
                                        ],
				);

				$company_id = $data['branch']['parent_company'];
				$company_industries = model('category')->get_industries();
				// $company_industries = arrayByKey(
				// 	array_filter((array) model('category')->getCategories([
				// 		'columns'  => "category_id, name, parent, cat_childrens, p_or_m",
				// 		'cat_list' => array_column(
				// 			array_filter((array) model('company')->get_relation_industry_by_id((int) $id_branch, false, array('limit' => '0,3'))),
				// 			'id_industry'
				// 		),
				// 	])),
				// 	'category_id'
				// );

				$selected_industries = arrayByKey(
					array_filter((array) model('category')->getCategories([
						'columns'  => "category_id, name, parent, cat_childrens, p_or_m",
						'cat_list' => $selected_industries_ids = array_column(
							array_filter((array) model('company')->get_relation_industry_by_id((int) $id_branch, false, array('limit' => '0,3'))),
							'id_industry'
						),
					])),
					'category_id'
				);

				$company_categories = arrayByKey(model('category')->getCategories(array('parent' => implode(',', $selected_industries_ids))), 'parent', true);
				// $company_categories = arrayByKey(
				// 	array_filter((array) model('category')->getCategories([
				// 		'columns'  => "category_id, name, parent, cat_childrens",
				// 		'cat_list' => array_column(
				// 			array_filter((array) model('company')->get_relation_category_by_company_id((int) $id_branch)),
				// 			'id_category'
				// 		),
				// 	])),
				// 	'parent',
				// 	true
				// );

				$selected_categories = arrayByKey(
					array_filter((array) model('category')->getCategories([
						'columns'  => "category_id, name, parent, cat_childrens",
						'cat_list' => array_column(
							array_filter((array) model('company')->get_relation_category_by_company_id((int) $id_branch)),
							'id_category'
						),
					])),
					'category_id',
				);
				$data['multipleselect_industries'] = array(
					// 'industries'                => array_intersect_key($company_industries, $company_categories),
					'industries'                => $company_industries,
					'categories'                => $company_categories,
					'industries_selected'       => $selected_industries,
					'categories_selected_by_id' => $selected_categories,
					'max_industries'            => (int) config('multipleselect_max_industries', 3),
				);

				$this->view->assign($data);
				$this->view->display("new/directory/branch/branch_form_view");
            break;
        }
    }

	function ajax_branch_operation(){
		checkIsAjax();
		checkIsLoggedAjax();

		if (!i_have_company()) {
			jsonResponse('You must have a company to perform this action.');
		}

		checkPermisionAjax('manage_branches');

		$this->load_main();
		$option = $this->uri->segment(3);

		switch($option){
			case 'add':
				is_allowed("freq_allowed_brances_operations");

				$validator_rules = array(
					array(
						'field' => 'images_main',
						'label' => 'Branch logo',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'name',
						'label' => 'Branch name',
						'rules' => array('required' => '', 'max_len[100]' => '')
					),
					array(
						'field' => 'type',
						'label' => 'Branch type',
						'rules' => array('required' => '', 'integer' => '')
					),
					array(
						'field' => 'country',
						'label' => 'Branch country',
						'rules' => array('required' => '', 'integer' => '')
					),
					array(
						'field' => 'port_city',
						'label' => 'Branch city',
						'rules' => array('required' => '', 'integer' => '')
					),
					array(
						'field' => 'address',
						'label' => 'Branch address',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'lat',
						'label' => 'Branch latitude',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'long',
						'label' => 'Branch longitude',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'phone_code_company',
						'label' => 'Phone code',
						'rules' => array(
							'required' => '',
							function ($attr, $phone_code_id, $fail) {
								if (empty($phone_code_id) || !model('country')->has_country_code($phone_code_id)) {
									$fail(sprintf('Field "%s" contains unknown value.', $attr));
								}
							}
						)
					),
					array(
						'field' => 'phone',
						'label' => 'Branch phone',
						'rules' => array('required' => '','valid_phone_number' => '')
					),
					array(
						'field' => 'fax',
						'label' => 'Branch fax',
						'rules' => array('valid_phone_number' => '')
					),
					array(
						'field' => 'fax_code_company',
						'label' => 'Fax code',
						'rules' => array(
							function ($attr, $phone_code_id, $fail) {
								if (empty($phone_code_id) || !model('country')->has_country_code($phone_code_id)) {
									$fail(sprintf('Field "%s" contains unknown value.', $attr));
								}
							}
						)
					),
					array(
						'field' => 'email',
						'label' => 'Branch email',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'zip',
						'label' => 'Branch zip',
						'rules' => array('required' => '', 'zip_code' => '', 'max_len[20]' => '')
					),
					array(
						'field' => 'description',
						'label' => 'Branch description',
						'rules' => array('html_max_len[20000]' => '')
					),
					array(
						'field' => 'video',
						'label' => 'Branch video',
						'rules' => array('valid_url' => '', 'max_len[200]' => '')
					)
				);

				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate()) {
					jsonResponse($this->validator->get_array_errors());
				}

                $request = request()->request;

                $selectedIndustries = array_filter(array_unique(array_map(
                    fn ($industryId) => (int) $industryId,
                    (array) $request->get('industriesSelected')
                )));

                if (empty($selectedIndustries)) {
                    jsonResponse(translate('validation_company_industry_required'), 'error');
                }

                $countSelectedIndustries = count($selectedIndustries);
                $selectedIndustriesLimit = (int) config('multipleselect_max_industries', 3);

                if ($countSelectedIndustries > $selectedIndustriesLimit){
                    jsonResponse(translate('multipleselect_max_industries', array('[COUNT]' => $selectedIndustriesLimit)), 'warning');
                }

                /** @var Company_Model $companyModel */
                $companyModel = model(Company_Model::class);

                $countOfValidIndustries = (int) $companyModel->count_categories_by_conditions(['category_list' => $selectedIndustries, 'parent' => 0]);
                if ($countSelectedIndustries != $countOfValidIndustries){
                    jsonResponse(translate('systmess_error_invalid_data'), 'error');
                }

				if (!empty($selectedCategories = $request->get('categoriesSelected'))) {
                    $countSelectedCategories = count($selectedCategories);

                    $selectedCategories = array_filter(array_unique(array_map(
                        fn ($categoryId) => (int) $categoryId,
                        (array) $selectedCategories
                    )));

                    $countOfValidCategories = (int) $companyModel->count_categories_by_conditions(['category_list' => $selectedCategories, 'parent_list' => $selectedIndustries]);
					if ($countSelectedCategories != $countOfValidCategories) {
						jsonResponse(translate('systmess_error_invalid_data'), 'error');
					}
				}

				//region Phone & Fax codes
				$phoneCodesService = new PhoneCodesService(model(Country_Model::class));
				/** @var CountryCodeInterface $phone_code */
				$phoneCode = $phoneCodesService->findAllMatchingCountryCodes($request->getInt('phone_code_company'))->first();
				/** @var CountryCodeInterface $fax_code */
				$faxCode = $phoneCodesService->findAllMatchingCountryCodes($request->getInt('fax_code_company'))->first();
				//endregion Phone & Fax codes

                /** @var TinyMVC_Library_Cleanhtml $cleanHtmlLibrary */
                $cleanHtmlLibrary = library(TinyMVC_Library_Cleanhtml::class);

				$insert = [
                    'id_phone_code_company' => $phoneCode ? $phoneCode->getId() : null,
					'id_fax_code_company'   => $faxCode ? $faxCode->getId() : null,
					'description_company'   => $cleanHtmlLibrary->sanitizeUserInput($request->get('description')),
                    'phone_code_company'    => $phoneCode ? $phoneCode->getName() : null,
					'employees_company'     => $request->getInt('employees'),
					'fax_code_company'      => $faxCode ? $faxCode->getName() : null,
					'address_company'       => cleanInput($request->get('address')),
					'revenue_company'       => cleanInput($request->get('revenue')),
					'visible_company'       => 1,
					'parent_company'        => my_company_id(),
					'phone_company'         => cleanInput($request->get('phone')),
					'email_company'         => cleanInput($request->get('email'), true),
					'name_company'          => cleanInput($request->get('name')),
					'type_company'          => 'branch',
					'zip_company'           => cleanInput($request->get('zip')),
					'fax_company'           => cleanInput($request->get('fax')),
					'id_country'            => $request->getInt('country'),
					'user_acces'            => id_session(),
					'longitude'             => $longitude = cleanInput($request->get('long')),
					'id_state'              => $request->getInt('states'),
					'latitude'              => $latitude = cleanInput($request->get('lat')),
					'id_user'               => id_session(),
					'id_type'               => $request->getInt('type'),
					'id_city'               => $request->getInt('port_city'),
                ];

				$branchId = $companyModel->set_company($insert);

				$companyModel->set_company_user_rel([
                    [
                        'company_type'  => 'branch',
                        'id_company'	=> $branchId,
                        'id_user'		=> id_session(),
                    ]
                ]);

                $companyModel->set_relation_industry($branchId, $selectedIndustries);

				if (!empty($selectedCategories)) {
					$companyModel->set_relation_category($branchId, $selectedCategories);
				}

                /** @var TinyMVC_Library_Upload $uploadLibrary */
                $uploadLibrary = library(TinyMVC_Library_Upload::class);

				//region main image
                /** @var FilesystemProviderInterface  $storageProvider */
                $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                $publicDisk = $storageProvider->storage('public.storage');
                $tempPrefixer = $storageProvider->prefixer('temp.storage');
                $publicPrefixer = $storageProvider->prefixer('public.storage');
				$mainImageModule = 'company_branches.main';
				$mainImagePath = CompanyLogoFilePathGenerator::logoFolder($branchId);
				$publicDisk->createDirectory($mainImagePath);
                /** @var TinyMVC_Library_Image_intervention $interventionImageLibrary */
                $interventionImageLibrary = library(TinyMVC_Library_Image_intervention::class);
                $uploadingResult = $interventionImageLibrary->image_processing(
                    [
                        'tmp_name' => $tempPrefixer->prefixPath($request->get('images_main')),
                        'name'     => \basename($request->get('images_main'))],
                    [
                        'destination'   => $publicPrefixer->prefixPath($mainImagePath),
                        'handlers'      => [
                            'create_thumbs' => config("img.{$mainImageModule}.thumbs"),
                            'resize'        => config("img.{$mainImageModule}.resize"),
                        ],
                    ]
                );

				if (!empty($uploadingResult['errors'])) {
					jsonResponse($uploadingResult['errors']);
				}

				$update['logo_company'] = $uploadingResult[0]['new_name'];
				//endregion main image

				if (!empty($video = $request->get('video'))) {
                    /** @var TinyMVC_Library_VideoThumb $videoThumbLibrary */
                    $videoThumbLibrary = library(TinyMVC_Library_VideoThumb::class);

					$videoLink = $videoThumbLibrary->getVID($video);
					$newVideo = $videoThumbLibrary->process($video);

					if (empty($newVideo['error'])) {
						$update['video_company_source'] = $videoLink['type'];
                        $publicPrefixer = $storageProvider->prefixer('public.storage');
                        $path = CompanyVideoFilePathGenerator::videoFolder($branchId);
                        $publicDisk->createDirectory($path);

						$uploadingVideoResult = $uploadLibrary->copy_images_new([
							'destination'   => $publicPrefixer->prefixPath($path),
                            'images'        => [$newVideo['image']],
							'resize'        => '730xR',
                        ]);

						if (empty($uploadingVideoResult['errors'])) {
							$update['video_company_image'] = $uploadingVideoResult[0]['new_name'];
							$update['video_company'] = $video;
							$update['video_company_code'] = $newVideo['v_id'];
						}
					}
				}

				$companyModel->update_company($branchId, $update);

                /** @var User_Statistic_Model $userStatisticsModel */
                $userStatisticsModel = model(User_Statistic_Model::class);

				$userStatisticsModel->set_users_statistic([
					id_session() => ['company_branches' => 1]
				]);

				//region photos
                if(!empty($goodPhotos = (array) $request->get('images_pictures')))
                {
                    $uploadedPhotos = [];
                    $module = 'company_branches.photos';
                    $photosPath = CompanyPhotosFilePathGenerator::photosFolder($branchId);
                    $publicPrefixer = $storageProvider->prefixer('public.storage');
                    $prefixerTemp = $storageProvider->prefixer('temp.storage');
                    $publicDisk->createDirectory($photosPath);
                    /** @var TinyMVC_Library_Image_intervention $interventionImageLibrary */
                    $interventionImageLibrary = library(TinyMVC_Library_Image_intervention::class);

                    foreach ($goodPhotos as $good)
                    {
                        $images = $interventionImageLibrary->image_processing(
                            [
                                'tmp_name' => $prefixerTemp->prefixPath($good),
                                'name'     => pathinfo($good, PATHINFO_FILENAME)
                            ],
                            [
                                'destination'   => $publicPrefixer->prefixPath($photosPath),
                                'rules'         => config("img.{$module}.rules"),
                                'handlers'      => [
                                    'create_thumbs' => config("img.{$module}.thumbs"),
                                    'resize'        => config("img.{$module}.resize"),
                                ],
                            ]
                        );
                        $uploadedPhotos[] = [
                            'path_photo' => $images[0]['new_name'],
                            'id_company' => $branchId,
                            'type_photo' => 'landscape',
                        ];
                    }
                    /** @var Branch_Model $branchModel */
                    $branchModel = model(Branch_Model::class);
                    $photos = $branchModel->set_branch_photos($uploadedPhotos);
                }
				//endregion photos

				session()->__push('companies', $branchId);

				//region block user content
                /** @var User_Model $usersModel */
                $usersModel = model(User_Model::class);

                $seller = $usersModel->getSimpleUser(privileged_user_id());
                if (in_array($seller['status'], ['blocked', 'restricted'])) {
                    /** @var Blocking_Model $blockingModel */
                    $blockingModel = model(Blocking_Model::class);

                    $blockingModel->change_blocked_users_companies(
                        [
                            'users_list'    => [privileged_user_id()],
                            'blocked'       => 0,
                        ],
                        ['blocked' => 2]
                    );
                }
                //endregion block user content

				jsonResponse(translate('systmess_success_company_branches_add'), 'success', ['id_item' => $branchId, 'bname' => strForURL($request->get('name'))]);
			break;
			case 'edit':
				checkPermisionAjax('manage_branches');

				$validator_rules = [
					[
						'field' => 'name',
						'label' => 'Branch name',
						'rules' => ['required' => '', 'max_len[100]' => '']
                    ],
					[
						'field' => 'type',
						'label' => 'Branch type',
						'rules' => ['required' => '', 'integer' => '']
                    ],
					[
						'field' => 'country',
						'label' => 'Branch country',
						'rules' => ['required' => '', 'integer' => '']
                    ],
					[
						'field' => 'port_city',
						'label' => 'Branch city',
						'rules' => ['required' => '', 'integer' => '']
                    ],
					[
						'field' => 'address',
						'label' => 'Branch address',
						'rules' => ['required' => '']
                    ],
					[
						'field' => 'lat',
						'label' => 'Branch latitude',
						'rules' => ['required' => '']
                    ],
					[
						'field' => 'long',
						'label' => 'Branch longitude',
						'rules' => ['required' => '']
                    ],
					[
						'field' => 'phone_code_company',
						'label' => 'Phone code',
						'rules' => [
							'required' => '',
							function ($attr, $phone_code_id, $fail) {
								if (empty($phone_code_id) || !model('country')->has_country_code($phone_code_id)) {
									$fail(sprintf('Field "%s" contains unknown value.', $attr));
								}
							}
                        ]
                    ],
					[
						'field' => 'phone',
						'label' => 'Branch phone',
						'rules' => ['required' => '','valid_phone_number' => '']
                    ],
					[
						'field' => 'fax',
						'label' => 'Branch fax',
						'rules' => ['valid_phone_number' => '']
                    ],
					[
						'field' => 'fax_code_company',
						'label' => 'Fax code',
						'rules' => [
							function ($attr, $phone_code_id, $fail) {
								if (empty($phone_code_id) || !model('country')->has_country_code($phone_code_id)) {
									$fail(sprintf('Field "%s" contains unknown value.', $attr));
								}
							}
                        ]
                    ],
					[
						'field' => 'email',
						'label' => 'Branch email',
						'rules' => ['required' => '']
                    ],
					[
						'field' => 'zip',
						'label' => 'Branch zip',
						'rules' => ['required' => '', 'zip_code' => '', 'max_len[20]' => '']
                    ],
					[
						'field' => 'branch',
						'label' => 'Branch info',
						'rules' => ['required' => '']
                    ],
					[
						'field' => 'description',
						'label' => 'Branch description',
						'rules' => ['html_max_len[20000]' => '']
                    ],
					[
						'field' => 'video',
						'label' => 'Branch video',
						'rules' => ['valid_url' => '', 'max_len[200]' => '']
                    ]
                ];

				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate()) {
					jsonResponse($this->validator->get_array_errors());
				}

                /** @var Branch_Model $branchModel */
                $branchModel = model(Branch_Model::class);

                $request = request()->request;

                $branchId = $request->getInt('branch');

				if (!$branchModel->is_my_branch($branchId, privileged_user_id())){
					jsonResponse(translate('systmess_error_permission_not_granted'));
				}

                $selectedIndustries = array_filter(array_unique(array_map(
                    fn ($industryId) => (int) $industryId,
                    (array) $request->get('industriesSelected')
                )));

                if (empty($selectedIndustries)) {
                    jsonResponse(translate('validation_company_industry_required'), 'error');
                }

                $countSelectedIndustries = count($selectedIndustries);
                $selectedIndustriesLimit = (int) config('multipleselect_max_industries', 3);

                if ($countSelectedIndustries > $selectedIndustriesLimit){
                    jsonResponse(translate('multipleselect_max_industries', array('[COUNT]' => $selectedIndustriesLimit)), 'warning');
                }

                /** @var Company_Model $companyModel */
                $companyModel = model(Company_Model::class);

                $countOfValidIndustries = (int) $companyModel->count_categories_by_conditions(['category_list' => $selectedIndustries, 'parent' => 0]);
                if ($countSelectedIndustries != $countOfValidIndustries){
                    jsonResponse(translate('systmess_error_invalid_data'), 'error');
                }

				if (!empty($selectedCategories = $request->get('categoriesSelected'))) {
                    $countSelectedCategories = count($selectedCategories);

                    $selectedCategories = array_filter(array_unique(array_map(
                        fn ($categoryId) => (int) $categoryId,
                        (array) $selectedCategories
                    )));

                    $countOfValidCategories = (int) $companyModel->count_categories_by_conditions(['category_list' => $selectedCategories, 'parent_list' => $selectedIndustries]);
					if ($countSelectedCategories != $countOfValidCategories) {
						jsonResponse(translate('systmess_error_invalid_data'), 'error');
					}
				}

				//region Phone & Fax codes
				$phoneCodesService = new PhoneCodesService(model(Country_Model::class));
				/** @var CountryCodeInterface $phone_code */
				$phoneCode = $phoneCodesService->findAllMatchingCountryCodes($request->getInt('phone_code_company'))->first();
				/** @var CountryCodeInterface $fax_code */
				$faxCode = $phoneCodesService->findAllMatchingCountryCodes($request->getInt('fax_code_company'))->first();
				//endregion Phone & Fax codes

				/** @var TinyMVC_Library_Cleanhtml $cleanHtmlLibrary */
                $cleanHtmlLibrary = library(TinyMVC_Library_Cleanhtml::class);

				$update = [
					'id_phone_code_company' => $phoneCode ? $phoneCode->getId() : null,
					'description_company'   => $cleanHtmlLibrary->sanitizeUserInput($_POST['description']),
					'id_fax_code_company'   => $faxCode ? $faxCode->getId() : null,
					'phone_code_company'    => $phoneCode ? $phoneCode->getName() : null,
					'employees_company'     => $request->getInt('employees'),
					'fax_code_company'      => $faxCode ? $faxCode->getName() : null,
					'address_company'       => cleanInput($request->get('address')),
					'revenue_company'       => cleanInput($request->get('revenue')),
					'phone_company'         => cleanInput($request->get('phone')),
					'email_company'         => cleanInput($request->get('email'), true),
					'name_company'          => cleanInput($request->get('name')),
					'zip_company'           => cleanInput($request->get('zip')),
					'fax_company'           => cleanInput($request->get('fax')),
					'id_country'            => $request->getInt('country'),
					'longitude'             => cleanInput($request->get('long')),
					'id_state'              => $request->getInt('states'),
					'latitude'              => cleanInput($request->get('lat')),
					'id_type'               => $request->getInt('type'),
					'id_city'               => $request->getInt('port_city'),
                ];

                $branch = $companyModel->get_company(['id_company' => $branchId, 'type_company' => 'all']);

                /** @var FilesystemProviderInterface $storageProvider */
                $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                $publicDisk = $storageProvider->storage('public.storage');
                $publicPrefixer = $storageProvider->prefixer('public.storage');
				if (!empty($request->get('video')) && $branch['video_company'] != ($video = $request->get('video'))) {
                    /** @var TinyMVC_Library_VideoThumb $videoThumbLibrary */
                    $videoThumbLibrary = library(TinyMVC_Library_VideoThumb::class);

					$videoLink = $videoThumbLibrary->getVID($_POST['video']);
					$newVideo = $videoThumbLibrary->process($_POST['video']);

					if (!empty($newVideo['error'])) {
						jsonResponse($newVideo['error']);
					}

					$update['video_company_source'] = $videoLink['type'];

                    $path = CompanyVideoFilePathGenerator::videoFolder($branchId);
                    $publicDisk->createDirectory($path);

                    /** @var TinyMVC_Library_Upload $uploadLibrary */
                    $uploadLibrary = library(TinyMVC_Library_Upload::class);

					$uploadingResult = $uploadLibrary->copy_images_new([
						'destination'   => $publicPrefixer->prefixPath($path),
						'images'        => [$newVideo['image']],
						'resize'        => '730xR',
                    ]);

					if (!empty($uploadingResult['errors'])) {
						jsonResponse($uploadingResult['errors']);
					}

                    try{
                        if(!empty($branch['video_company_image'])){
                            $publicDisk->delete(CompanyVideoFilePathGenerator::videoPath($branchId, $branch['video_company_image']));
                        }
                    } catch (UnableToDeleteFile $e) {
                        jsonResponse(translate('validation_images_delete_fail'));
                    }

					$update['video_company_image'] = $uploadingResult[0]['new_name'];
					$update['video_company'] = $video;
					$update['video_company_code'] = $newVideo['v_id'];
				}

                //region remove photos
                if (!empty($temp_images_remove = (array) $request->get('images_remove')))
                {
                    foreach($temp_images_remove as $photo_id){
                        if (
                            empty($photo_id) ||
                            empty($photo = $this->branch->get_branch_image_by_id($photo_id, $branchId))
                        ) {
                            jsonResponse(translate('systmess_error_invalid_data'));
                        }

                        if (!$this->branch->delete_branch_photo($photo['id_photo'], $branchId)) {
                            jsonResponse('Error: This image can\'t be deleted.');
                        }

                        $module = 'company_branches.photos';
                        $path = CompanyPhotosFilePathGenerator::photosPath($branchId, $photo['path_photo']);
                        try {
                            $publicDisk->delete($path);
                            $thumbs = config('img.company_branches.photos.thumbs');
                            foreach($thumbs as $thumb){
                                $thumbImageName = str_replace('{THUMB_NAME}', $photo['path_photo'], $thumb['name']);
                                $publicDisk->delete(CompanyPhotosFilePathGenerator::photosPath($branchId, $thumbImageName));
                                }
                        } catch (UnableToDeleteFile $e) {
                            jsonResponse(translate('validation_images_delete_fail'));
                        }
                    }
                }
                //endregion remove photos

                if(!empty($goodPhotos = (array) $request->get('images_pictures')))
                {
                    $count_db = $this->branch->count_branch_images(['id_company' => $branchId]);
                    $module = 'company_branches.photos';
                    $disponible = (int) config("img.{$module}.limit") - $count_db;

                    if($disponible <= 0) {
                        jsonResponse('Error: You cannot upload more than '.($disponible + $count_db).' photo(s).');
                    }


                    $uploadedPhotos = [];
                    $module = 'company_branches.photos';
                    $photosPath = CompanyPhotosFilePathGenerator::photosFolder($branchId);
                    $publicPrefixer = $storageProvider->prefixer('public.storage');
                    $prefixerTemp = $storageProvider->prefixer('temp.storage');
                    $publicDisk->createDirectory($photosPath);
                    /** @var TinyMVC_Library_Image_intervention $interventionImageLibrary */
                    $interventionImageLibrary = library(TinyMVC_Library_Image_intervention::class);

                    foreach ($goodPhotos as $good)
                    {
                        $images = $interventionImageLibrary->image_processing(
                            [
                                'tmp_name' => $prefixerTemp->prefixPath($good),
                                'name'     => pathinfo($good, PATHINFO_FILENAME)
                            ],
                            [
                                'destination'   => $publicPrefixer->prefixPath($photosPath),
                                'rules'         => config("img.{$module}.rules"),
                                'handlers'      => [
                                    'create_thumbs' => config("img.{$module}.thumbs"),
                                    'resize'        => config("img.{$module}.resize"),
                                ],
                            ]
                        );
                        $uploadedPhotos[] = [
                            'path_photo' => $images[0]['new_name'],
                            'id_company' => $branchId,
                            'type_photo' => 'landscape',
                        ];
                    }
                    /** @var Branch_Model $branchModel */
                    $branchModel = model(Branch_Model::class);
                    $photos = $branchModel->set_branch_photos($uploadedPhotos);
                }

				$companyModel->delete_relation_industry_by_company($branchId);
                $companyModel->set_relation_industry($branchId, $selectedIndustries);

				if (!empty($selectedCategories)) {
					$companyModel->delete_relation_category_by_company($branchId);
					$companyModel->set_relation_category($branchId, $selectedCategories);
				}

				$companyModel->update_company($branchId, $update);

				jsonResponse(translate('general_all_changes_saved_message'), 'success');
			break;
            case 'change_visibility':
				checkPermisionAjax('manage_branches');

                $id_company = (int) $_POST['company'];
				if(!in_session('companies', $id_company)){
					jsonResponse(translate("systmess_error_rights_perform_this_action"));
				}

				$is_visible = model('Company')->is_visible_company($id_company);
				$this->company->update_company($id_company, array('visible_company' => (int) !$is_visible));

                /** @var Elasticsearch_B2b_Model $elasticsearchB2bModel */
                $elasticsearchB2bModel = model(Elasticsearch_B2b_Model::class);
                if(0 == !$is_visible){
                    $elasticsearchB2bModel->removeB2bRequestsByConditions(['companyId' => (int) $id_company]);
                }

                jsonResponse(translate('systmess_success_company_branches_change_visibility'), 'success');
            break;
            case 'delete_branch':
                $id_branch = intval($_POST['company']);
				if(!in_session('companies', $id_branch))
					jsonResponse(translate("systmess_error_rights_perform_this_action"));

				if(!have_right('manage_branches'))
					jsonResponse(translate("systmess_error_rights_perform_this_action"));

                $branch = $this->company->get_company(array('id_company' => $id_branch, 'type_company' => 'branch'));

                if (empty($branch)) {
                    jsonResponse(translate('systmess_error_rights_perform_this_action'));
                }

				$this->company->delete_relation_industry_by_company($id_branch);
				$this->company->delete_relation_category_by_company($id_branch);
				$this->company->clear_company_users_rel($id_branch);

				$this->load->model('B2b_Model', 'b2b');
				$this->load->model('User_Statistic_Model', 'statistic');
				$b2b_requests = $this->b2b->get_company_b2b_requests($id_branch);
				$seller_statistic = array();
				if(!empty($b2b_requests)){
					$change_statistic = 0;
					$requests_list = array();
					foreach($b2b_requests as $request){
						$requests_list[] = $request['id_request'];
						$change_statistic--;
					}

					$requests_list = implode(',', $requests_list);
					$advices = $this->b2b->get_advices_simple($requests_list);
					if(!empty($advices)){
						$advices_list = array();
						foreach($advices as $advice){
							$advices_list[$advice['id_advice']] = $advice['id_advice'];
						}
						$this->b2b->delete_advices_helpful(implode(',', $advices_list));
						$this->b2b->delete_advice(array('requests_list' => $requests_list));
					}

					$this->b2b->delete_response(array('requests_list' => $requests_list));
					$this->b2b->delete_request(array('requests_list' => $requests_list));
					$this->b2b->delete_request_relation_industry($requests_list);
					$this->b2b->delete_request_relation_category($requests_list);
					$this->b2b->delete_followed_requests($requests_list);

                    // Remove B2B indexed requests from the ElasticSearch
                    /** @var MessengerInterface */
                    $messenger = $this->getContainer()->get(MessengerInterface::class);
                    $commandBus = $messenger->bus('command.bus');
                    foreach($b2b_requests as $request){
						$commandBus->dispatch(new RemoveB2bRequest((int) $request['id_request']));
					}

					$seller_statistic['b2b_requests'] = $change_statistic;
				}

				$company_partners = $this->b2b->get_company_partners($id_branch);
				if(!empty($company_partners)){
					$partners_statistic = array();
					foreach($company_partners as $company_partner){
						$partners_statistic[$company_partner['user_partner']]['b2b_partners'] = -1;
					}

					$seller_statistic['b2b_partners'] = -count($company_partners);
					$this->statistic->set_users_statistic($partners_statistic);
					$this->b2b->delete_company_partners($id_branch);
				}

				//remove photos

                /** @var FilesystemProviderInterface $storageProvider */
                $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                $publicDisk = $storageProvider->storage('public.storage');
				$photos = $this->branch->get_branch_images(array('id_company' => $id_branch));
				if(!empty($photos)){
                    $thumbs = config('img.company_branches.photos.thumbs');
                    foreach($photos as $photo){
                        $path = CompanyPhotosFilePathGenerator::photosPath($id_branch, $photo['path_photo']);
                        try {
                            $publicDisk->delete($path);
                            foreach($thumbs as $thumb){
                                $thumbImageName = str_replace('{THUMB_NAME}', $photo['path_photo'], $thumb['name']);
                                $publicDisk->delete(CompanyPhotosFilePathGenerator::photosPath($id_branch, $thumbImageName));
                            }
                        } catch (UnableToDeleteFile $e) {
                            jsonResponse(translate('validation_images_delete_fail'));
                        }
                    }

					$this->branch->delete_branch_photos($id_branch);
				}
				//end remove photos

				//remove logo
				$company_logo = $branch['logo_company'];
				if(!empty($company_logo)){
                    $thumbs = config('img.company_branches.main.thumbs');
                    $path = CompanyLogoFilePathGenerator::logoPath($id_branch, $company_logo);
                    try {
                        $publicDisk->delete($path);
                        foreach($thumbs as $thumb){
                            $thumbImageName = str_replace('{THUMB_NAME}', $company_logo, $thumb['name']);
                            $publicDisk->delete(CompanyLogoFilePathGenerator::logoPath($id_branch, $thumbImageName));
                        }
                    } catch (UnableToDeleteFile $e) {
                        jsonResponse(translate('validation_images_delete_fail'));
                    }
				}
				//end remove logo

				$this->branch->delete_branch($id_branch);
				$seller_statistic['company_branches'] = -1;
				$this->statistic->set_users_statistic(array(
					$branch['id_user'] => $seller_statistic
				));

                session()->companies = array_diff((array) session()->companies, [$id_branch]);

                jsonResponse(translate('systmess_success_company_branches_delete'), 'success');
            break;
		}
	}

	function ajax_branch_upload_logo(){
		checkIsAjax();
		checkIsLogged();
		checkPermisionAjax('manage_branches');
		$files = arrayGet($_FILES, 'files');
		if (null === $files) {
			jsonResponse('Error: Please select file to upload.');
		}

		if (is_array($files['name'])) {
			jsonResponse("Invalid file provided.");
		}

		$module ='company_branches.main';
        /** @var FilesystemProviderInterface  $storageProvider */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $pathToFile = FilePathGenerator::uploadedFile(bin2hex(random_bytes(16)));
        $tempPrefixer = $storageProvider->prefixer('temp.storage');
        $tempStorage = $storageProvider->storage('temp.storage');

		$copy_result = library('upload')->upload_images_data(array(
			'files'       => $files,
			'destination' => $tempPrefixer->prefixPath($pathToFile),
			'resize'      => config("img.{$module}.resize"),
			'rules'       => config("img.{$module}.rules")
		));

		if (!empty($copy_result['errors'])) {
			jsonResponse($copy_result['errors']);
		}

		$insert_photo = array();
		foreach($copy_result as $item){
			$insert_photo = array('logo' => $item['new_name']);
		}

		if (empty($insert_photo)) {
			jsonResponse('Error: You not select any pictures.');
		}

		$files = array(
			"path" => $pathToFile."/".$insert_photo['logo'],
			"thumb" => $tempStorage->url($pathToFile."/".$insert_photo['logo']),
			"tmp_url" => $pathToFile."/".$insert_photo['logo'],
		);

		jsonResponse('Company logo was successfully uploaded.', 'success', $files);
    }

	function ajax_branch_upload_db_logo(){
		checkIsAjax();
		checkIsLogged();
		checkPermisionAjax('manage_branches');
		$files = arrayGet($_FILES, 'files');
		if (null === $files) {
			jsonResponse('Error: Please select file to upload.');
		}

		if (is_array($files['name'])) {
			jsonResponse("Invalid file provided.");
		}

		$id_branch = (int) $this->uri->segment(3);
		$this->load->model('Company_Model', 'company');

		if(!in_session('companies', $id_branch)){
			jsonResponse(translate("systmess_error_rights_perform_this_action"));
		}

		$branch = $this->company->get_company(array('id_company' => $id_branch, 'type_company'=>'branch'));

		$company_logo = $branch['logo_company'];
		$module = 'company_branches.main';
        /** @var FilesystemProviderInterface  $storageProvider */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $publicDisk = $storageProvider->storage('public.storage');
        $publicPrefixer = $storageProvider->prefixer('public.storage');
        $path = CompanyLogoFilePathGenerator::logoFolder($id_branch);
        $publicDisk->createDirectory($path);

		$copy_result = library('upload')->upload_images_data(array(
			'files'       => $files,
			'destination' => $publicPrefixer->prefixPath($path),
			'resize'      => config("img.{$module}.resize"),
			'thumbs'      => config("img.{$module}.thumbs"),
			'rules'       => config("img.{$module}.rules")
		));

		if (!empty($copy_result['errors'])) {
			jsonResponse($copy_result['errors']);
		}

		$insert_photo = array();
		foreach($copy_result as $item){
			$insert_photo = array('logo_company' => $item['new_name']);
		}

		if (empty($insert_photo)) {
			jsonResponse('Error: You not select any pictures.');
		}

		if (!$this->company->update_company($id_branch, $insert_photo)) {
			jsonResponse("Failed to upload image(s)");
		}

		//remove files
		if(!empty($company_logo)){
            $thumbs = config('img.company_branches.main.thumbs');
            $path = CompanyLogoFilePathGenerator::logoPath($id_branch, $company_logo);
            try {
                $publicDisk->delete($path);
                foreach($thumbs as $thumb){
                    $thumbImageName = str_replace('{THUMB_NAME}', $company_logo, $thumb['name']);
                    $publicDisk->delete(CompanyLogoFilePathGenerator::logoPath($id_branch, $thumbImageName));
                }
            } catch (UnableToDeleteFile $e) {
                jsonResponse(translate('validation_images_delete_fail'));
            }
		}
		//end remove files

		$files = array(
			"path" => getDisplayImageLink(array('{ID}' => $id_branch, '{FILE_NAME}' => $insert_photo['logo_company']), $module),
			"thumb" => getDisplayImageLink(array('{ID}' => $id_branch, '{FILE_NAME}' => $insert_photo['logo_company']), $module, array('thumb_size' => 1)),
		);

		jsonResponse('Company logo was successfully updated.', 'success', $files);
    }

	public function ajax_branch_upload_pictures()
    {
		checkIsAjax();
		checkIsLogged();
		checkGroupExpire('ajax');
		checkPermisionAjax('manage_branches');

        $request = request();
        /** @var FileUploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('files');

        if (!empty($idBranch = (int) uri()->segment(4))) {
            if(!in_session('companies', $idBranch)){
                jsonResponse(translate("systmess_error_rights_perform_this_action"));
            }

        }
        /** @var FilesystemProviderInterface  $storageProvider */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $tempDisk = $storageProvider->storage('temp.storage');
        $config = 'img.company_branches.photos.rules';
        //get the intervention image handler
        /** @var LegacyImageHandler $imageHandler */
        $imageHandler = $this->getContainer()->get(LibraryLocator::class)->get(LegacyImageHandler::class);
        // Given that we need to validate the file and it would take a long time
        // to write the new proper validation we can only use what we have.
        try {
            $imageHandler->assertImageIsValid(
                $imageHandler->makeImageFromFile($uploadedFile),
                config($config)
            );
        } catch (ValidationException $e) {
            jsonResponse(
                \array_map(
                    function (ConstraintViolationInterface $violation) { return $violation->getMessage(); },
                    \iterator_to_array($e->getValidationErrors()->getIterator())
                )
            );
        } catch (ReadException $e) {
            jsonResponse(translate('validation_images_upload_fail'), 'error', withDebugInformation(
                [],
                ['exception' => throwableToArray($e)]
            ));
        }
        // But first we need to get the full path to the file
        $fileName = sprintf('%s.%s', \bin2hex(\random_bytes(16)), $uploadedFile->getClientOriginalExtension());
        $pathToFile = FilePathGenerator::uploadedFile($fileName);
        // And write file there
        try {
            $tempDisk->write($pathToFile, $uploadedFile->getContent());
        } catch (UnableToWriteFile $e) {
            jsonResponse(translate('validation_images_upload_fail'));
        }

        //refactor the way full path is returned
        jsonResponse(null, 'success', [
            'files' => ['path' => $pathToFile, 'name' => $fileName, 'fullPath' => $tempDisk->url($pathToFile)]
        ]);
	}

	public function ajax_branch_delete_pictures()
    {
		checkIsAjax();
		checkIsLogged();
		checkGroupExpire('ajax');
		checkPermisionAjax('manage_branches');

        if (empty($file = request()->request->get('file'))) {
            jsonResponse(translate('seller_updates_filename_not_correct_message'));
        }
        /** @var FilesystemProviderInterface  $storageProvider */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $tempDisk = $storageProvider->storage('temp.storage');
        $pathToFile = FilePathGenerator::uploadedFile($file);

        if (!$tempDisk->fileExists($pathToFile)) {
            jsonResponse(translate('seller_updates_upload_path_not_exist_message'));
        }
        try{
            $tempDisk->delete($pathToFile);
        } catch (UnableToDeleteFile $e) {
            jsonResponse(translate('systmess_error_rights_perform_this_action'));
        }

        jsonResponse(null, 'success');
	}
}
