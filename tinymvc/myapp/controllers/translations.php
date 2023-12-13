<?php

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

/**
 * Translations_Controller.php
 *
 * Translations application controller
 *
 * @property \TinyMVC_Load                    $load
 * @property \TinyMVC_View                    $view
 * @property \TinyMVC_Library_URI             $uri
 * @property \TinyMVC_Library_Session         $session
 * @property \TinyMVC_Library_Cookies         $cookies
 * @property \TinyMVC_Library_Upload          $upload
 * @property \TinyMVC_Library_validator       $validator
 * @property \Translations_Model              $translations
 * @property \Ep_Modules_Model                $modules
 * @property \Pages_Model                     $pages
 * @property \TinyMVC_Library_I18n_Key_Parser $i81n
 *
 * @author Cravciuc Andrei
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [18.12.2021]
 * Controller Refactoring
 */
class Translations_Controller extends TinyMVC_Controller
{
    /**
     * @author Usinevici Alexandr
     * @deprecated [24.12.2021]
     * Reason: Old functionality. Not used
     */
    // public function proxy_clbk_item()
    // {
    //     $json = file_get_contents("php://input");

	// 	$this->load->library('ProxyTranslator', 'proxytranslator');
	// 	$this->load->model('Items_Model', 'items');
    //     $proxyInstance = $this->proxytranslator->fromJson($json);

    //     $allOk = true;
    //     foreach($proxyInstance->getTranslations() as $translations) {
    //         $lang_to = $translations['lang_to'];

    //         foreach($translations['translation'] as $item_id => $translation) {
    //             $allOk = $allOk && $this->items->save_item_i18n($item_id, $lang_to, $translation);
    //         }
    //     }

    //     if($allOk) {
    //         global $tmvc;
    //         $config_langs = explode(",", $tmvc->my_config['proxy_translation_langs']);
    //         $config_langs_count = count($config_langs);
    //         $langs_to = $proxyInstance->getLanguagesTo();

    //         $update = array(
    //             'has_translation' =>  $config_langs_count == count($langs_to) ? 'yes' : 'partial',
    //             'translations_data' => json_encode($langs_to)
    //         );

    //         $ids = explode(",", $proxyInstance->getCustomdata());
    //         $rez = $this->items->update_items_same_data(array("items_list" => $ids), $update);

    //         echo "done";
    //     }
    // }

    public function ajax_operations()
    {
		if (!isAjaxRequest()) {
			headerRedirect();
        }

		if (!logged_in()) {
			jsonResponse(translate("systmess_error_should_be_logged"));
        }

		$type = $this->uri->segment(3);
		switch ($type) {
			case 'add_language':
				checkAdminAjax('manage_content');

				$validator_rules = array(
					array(
						'field' => 'lang_name',
						'label' => 'Language name',
						'rules' => array('required' => '', 'max_len[50]' => '')
					),
					array(
						'field' => 'lang_name_original',
						'label' => 'Language original name',
						'rules' => array('required' => '', 'max_len[50]' => '')
					),
					array(
						'field' => 'lang_iso2',
						'label' => 'ISO 2 letter',
						'rules' => array('required' => '', 'min_len[2]' => '', 'max_len[10]' => '')
					),
					array(
						'field' => 'lang_spec',
						'label' => 'Lnaguage specification',
						'rules' => array('required' => '', 'min_len[2]' => '', 'max_len[10]' => '')
					),
					array(
						'field' => 'lang_google_abbr',
						'label' => 'Google translate language abbreviation',
						'rules' => array('required' => '', 'min_len[2]' => '', 'max_len[10]' => '')
					),
					array(
						'field' => 'lang_url_type',
						'label' => 'Translation type',
						'rules' => array('required' => '')
					)
				);

				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate()) {
					jsonResponse($this->validator->get_array_errors());
				}

				$insert = array(
					'lang_name' => cleanInput($_POST['lang_name']),
					'lang_name_original' => cleanInput($_POST['lang_name_original']),
					'lang_iso2' => strtolower(cleanInput($_POST['lang_iso2'])),
					'lang_spec' => cleanInput($_POST['lang_spec']),
					'lang_google_abbr' => cleanInput($_POST['lang_google_abbr']),
					'lang_url_type' => cleanInput($_POST['lang_url_type']),
					'lang_icon' => cleanInput($_POST['lang_icon']),
					'lang_default' => (isset($_POST['lang_default']))?1:0,
					'lang_active' => (isset($_POST['lang_active']))?1:0,
					'lang_created' => date('Y-m-d H:i:s')
				);

				$this->translations->insert_language($insert);

				jsonResponse('The new language has been added.', 'success');
			break;
			case 'edit_language':
                checkAdminAjax('manage_content');

				$validator_rules = array(
					array(
						'field' => 'id_lang',
						'label' => 'Language info',
						'rules' => array('required' => '', 'integer' => '')
					),
					array(
						'field' => 'lang_name',
						'label' => 'Language name',
						'rules' => array('required' => '', 'max_len[50]' => '')
					),
					array(
						'field' => 'lang_name_original',
						'label' => 'Language original name',
						'rules' => array('required' => '', 'max_len[50]' => '')
					),
					array(
						'field' => 'lang_iso2',
						'label' => 'ISO 2 letter',
						'rules' => array('required' => '', 'min_len[2]' => '', 'max_len[10]' => '')
					),
					array(
						'field' => 'lang_spec',
						'label' => 'Lnaguage specification',
						'rules' => array('required' => '', 'min_len[2]' => '', 'max_len[10]' => '')
					),
					array(
						'field' => 'lang_google_abbr',
						'label' => 'Google translate language abbreviation',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'lang_url_type',
						'label' => 'Translation type',
						'rules' => array('required' => '')
					)
				);

				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate()) {
					jsonResponse($this->validator->get_array_errors());
				}

				$id_lang = (int) $_POST['id_lang'];
				$language = $this->translations->get_language($id_lang);

				if(empty($language)){
					jsonResponse('Error: The language does not exist.');
				}

				$update = array(
					'lang_name' => cleanInput($_POST['lang_name']),
					'lang_name_original' => cleanInput($_POST['lang_name_original']),
					'lang_iso2' => strtolower(cleanInput($_POST['lang_iso2'])),
					'lang_spec' => cleanInput($_POST['lang_spec']),
					'lang_google_abbr' => cleanInput($_POST['lang_google_abbr']),
					'lang_url_type' => cleanInput($_POST['lang_url_type']),
					'lang_icon' => cleanInput($_POST['lang_icon']),
					'lang_active' => (isset($_POST['lang_active']))?1:0,
				);

				$this->translations->update_language($id_lang, $update);

				jsonResponse('The new language has been added.', 'success');
			break;
			case 'add_route':
                checkAdminAjax('manage_content');

				$routes = $_POST['routes'];
				$insert = array(
					'route_controller' => $routes['route_controller'],
					'route_action' => $routes['route_action'],
					'route_key' => $routes['route_controller'].'/'.$routes['route_action'],
					'route_replace' => $routes['route_replace']
				);

                $default_route_json = '';
				$default_uri_components = array_filter(explode('/', $routes['lang']['en']['uri_components']));
				foreach ($routes['lang'] as $lkey => $lroute) {
					$route_segments = array_filter(explode('/', $lroute['replace_uri_string']));
					$replace_route_segments = array();
					if(!empty($route_segments)){
						foreach ($route_segments as $route_segment_index => $route_segment) {
							if($route_segment == '{%DINAMIC_URI%}'){
								$replace_route_segments[] = $route_segment_index;
							}
						}
					}
					$lroute['route_segments'] = $route_segments;
					$lroute['replace_route_segments'] = $replace_route_segments;

					if(!empty($default_uri_components)){
						$lroute['uri_components'] = $lroute['uri_components'];
						$uri_components = array_filter(explode('/', $lroute['uri_components']));
						$replace_uri_components = array();
						foreach ($default_uri_components as $key_component => $default_uri_component) {
							$replace_uri_components[$default_uri_component] = (array_key_exists($key_component, $uri_components))?$uri_components[$key_component]:$default_uri_component;
						}
						$lroute['replace_uri_components'] = $replace_uri_components;
					}

                    $insert['lang_' . $lkey] = json_encode($lroute);

                    if ($lkey === 'en') {
                        $default_route_json = $insert['lang_' . $lkey];
                    }
                }

                // processing other languages
                $non_domain_languages = model('translations')->get_languages(array('not_domain' => true));
                foreach ($non_domain_languages as $language) {
                    $insert['lang_' . $language['lang_iso2']] = $default_route_json;
                }

				$last_route_weight = $this->translations->get_route_weight();
				$route_weight = 1;
				if(!empty($last_route_weight)){
					$route_weight = $last_route_weight['route_weight'] + 1;
				}
				$insert['route_weight'] = $route_weight;
				$this->translations->insert_routing($insert);
				jsonResponse('The new routing has been added.', 'success');
			break;
			case 'edit_route':
                checkAdminAjax('manage_content');

				$validator_rules = array(
					array(
						'field' => 'id_route',
						'label' => 'Routing info',
						'rules' => array('required' => '', 'integer' => '')
					)
				);

				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate()) {
					jsonResponse($this->validator->get_array_errors());
				}

				$id_route = (int) $_POST['id_route'];
				$troute = $this->translations->get_routing($id_route);

				if(empty($troute)){
					jsonResponse('Error: The routing does not exist.');
				}

				$routes = $_POST['routes'];
				$update = array(
					'route_controller' => $routes['route_controller'],
					'route_action' => $routes['route_action'],
					'route_key' => $routes['route_controller'].'/'.$routes['route_action'],
					'route_replace' => $routes['route_replace']
				);

                $default_route_json = '';
                $default_uri_components = array_filter(explode('/', $routes['lang']['en']['uri_components']));
                // processing domain languages
				foreach ($routes['lang'] as $lkey => $lroute) {
					$route_segments = array_filter(explode('/', $lroute['replace_uri_string']));
					$replace_route_segments = array();
					if(!empty($route_segments)){
						foreach ($route_segments as $route_segment_index => $route_segment) {
							if($route_segment == '{%DINAMIC_URI%}'){
								$replace_route_segments[] = $route_segment_index;
							}
						}
					}
					$lroute['route_segments'] = $route_segments;
					$lroute['replace_route_segments'] = $replace_route_segments;
					if(!empty($default_uri_components)){
						$lroute['uri_components'] = $lroute['uri_components'];
						$uri_components = array_filter(explode('/', $lroute['uri_components']));
						$replace_uri_components = array();
						foreach ($default_uri_components as $key_component => $default_uri_component) {
							$replace_uri_components[$default_uri_component] = (array_key_exists($key_component, $uri_components))?$uri_components[$key_component]:$default_uri_component;
						}
						$lroute['replace_uri_components'] = $replace_uri_components;
					}
                    $update['lang_' . $lkey] = json_encode($lroute);

                    if ($lkey === 'en') {
                        $default_route_json = $update['lang_' . $lkey];
                    }
                }

                // processing other languages
                $non_domain_languages = model('translations')->get_languages(array('not_domain' => true));
                foreach ($non_domain_languages as $language) {
                    $update['lang_' . $language['lang_iso2']] = $default_route_json;
                }

				$this->translations->update_routing($id_route, $update);
				jsonResponse('The routing has been updated.', 'success');
			break;
			case 'change_route_weight':
                checkAdminAjax('manage_content');

				$validator_rules = array(
					array(
						'field' => 'id_route',
						'label' => 'Routing info',
						'rules' => array('required' => '', 'integer' => '')
					)
				);

				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate()) {
					jsonResponse($this->validator->get_array_errors());
				}

				$id_route = (int) $_POST['id_route'];
				$troute = $this->translations->get_routing($id_route);

				if(empty($troute)){
					jsonResponse('Error: The routing does not exist.');
				}

				$params = array(
					'route_weight' => $troute['route_weight']
				);
				if($_POST['direction'] != 'down'){
					$params['direction'] = 'up';
				}
				$weight_route = $this->translations->get_route_weight($params);
				if(!empty($weight_route)){
					$route_weight = $weight_route['route_weight'];
					$update = array(
						'route_weight' => $route_weight
					);
					$this->translations->update_routing($id_route, $update);
					$update = array(
						'route_weight' => $troute['route_weight']
					);
					$this->translations->update_routing($weight_route['id_route'], $update);
				}

				jsonResponse('Saved.','success', array('new_pos' => $route_weight, 'old_pos' => $troute['route_weight']));
			break;
            case 'regenerate_route':
                checkAdminAjax('manage_content');

				$tlanguages = $this->translations->get_languages();
				$route_langs = array();
				foreach ($tlanguages as $tlanguage) {
					$route_langs[] = $tlanguage['lang_iso2'];
				}

				$langs_urls = array();
				$routes_records = $this->translations->get_routings();
				$lang_routes_file = TMVC_MYAPPDIR . 'configs/translations/routings/all_langs_routes.php';
				$f = fopen($lang_routes_file, "w");
				fwrite($f, '<?php '."\r\n");
				foreach($routes_records as $route_record){
                    $recorded_routes = array();
                    $default_route_search = json_decode($route_record['lang_en'], true);
					foreach ($route_langs as $route_lang_key) {
						$route_search = json_decode($route_record['lang_'.$route_lang_key], true);

						$langs_urls[$route_lang_key]['urls'][$route_record['route_key']]['replace_uri_string'] = (empty($route_search['replace_uri_string']))?$default_route_search['replace_uri_string']:$route_search['replace_uri_string'];
						if(!empty($route_search['replace_uri_components'])){
							$langs_urls[$route_lang_key]['urls'][$route_record['route_key']]['replace_uri_components'] = $route_search['replace_uri_components'];
							$langs_urls[$route_lang_key]['urls'][$route_record['route_key']]['flipped_uri_components'] = array_flip($route_search['replace_uri_components']);
						}
						if(!empty($route_search['route_search'])){
                            $langs_urls[$route_lang_key]['routings']['search'][] = $route_search['route_search'];
                            if (!in_array($route_search['route_search'], $recorded_routes)) {
                                $recorded_routes[] = $route_search['route_search'];

                                fwrite($f, '$config[\'routing\'][\'search\'][] = \''.$route_search['route_search'].'\';'."\r\n");
							    fwrite($f, '$config[\'routing\'][\'replace\'][] = \''.$route_record['route_replace'].'\';'."\r\n\r\n");
                            }
						} else{
							$langs_urls[$route_lang_key]['routings']['search'][] = $default_route_search['route_search'];
						}
						$langs_urls[$route_lang_key]['routings']['replace'][] = $route_record['route_replace'];
					}
				}
				fclose($f);

				$lang_urls_path = TMVC_MYAPPDIR . "configs/translations/urls";
				$lang_routings_path = TMVC_MYAPPDIR . "configs/translations/routings";
				create_dir($lang_urls_path);
				create_dir($lang_routings_path);
				foreach ($langs_urls as $langs_url_key => $langs_url) {
					$lang_url_file =  $lang_urls_path. DS . "lang_{$langs_url_key}.php";
					$f = fopen($lang_url_file, "w");
					fwrite($f, '<?php '."\r\n");
					fwrite($f, 'return '.var_export($langs_url['urls'], true).';');
					fclose($f);
					$lang_routing_file =  $lang_routings_path. DS . "lang_{$langs_url_key}.php";
					$f = fopen($lang_routing_file, "w");
					fwrite($f, '<?php '."\r\n");
					foreach ($langs_url['routings']['search'] as $route_key => $route_search) {
						fwrite($f, '$config[\'routing\'][\'search\'][] = \''.$route_search.'\';'."\r\n");
						fwrite($f, '$config[\'routing\'][\'replace\'][] = \''.$langs_url['routings']['replace'][$route_key].'\';'."\r\n\r\n");
					}
					fclose($f);
				}

                jsonResponse("Lang routes are regenerated successfully", "success", array('langs_urls' => $langs_urls));
            break;
			case 'change_language_active':
                checkAdminAjax('manage_content');

				$validator_rules = array(
					array(
						'field' => 'id_lang',
						'label' => 'Language info',
						'rules' => array('required' => '', 'integer' => '')
					)
				);

				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate()) {
					jsonResponse($this->validator->get_array_errors());
				}

				$id_lang = (int) $_POST['id_lang'];
				$language = $this->translations->get_language($id_lang);

				if(empty($language)){
					jsonResponse('Error: The language does not exist.');
				}

				if($language['lang_active'] == 1){
					$lang_active = 0;
				} else{
					$lang_active = 1;
				}

				$update = array(
					'lang_active' => $lang_active
				);

				$this->translations->update_language($id_lang, $update);

				jsonResponse('The language visibility status has been changed.', 'success');
			break;
			case 'languages_list_dt':
                checkAdminAjax('manage_content');

				$params = array();

				if ($_POST['iSortingCols'] > 0) {
					for ($i = 0; $i < $_POST['iSortingCols']; $i++) {
						switch ($_POST["mDataProp_" . intval($_POST['iSortCol_' . $i])]) {
							case 'dt_id':
								$params['sort_by'][] = 'id_lang-' . $_POST['sSortDir_' . $i];
							break;
							case 'dt_lang_url_type':
								$params['sort_by'][] = 'lang_url_type-' . $_POST['sSortDir_' . $i];
							break;
						}
					}
				}

				$records = $this->translations->get_languages($params);
				$records_count = $this->translations->count_languages($params);

				$output = array(
					"sEcho" => intval($_POST['sEcho']),
					"iTotalRecords" => $records_count,
					"iTotalDisplayRecords" => $records_count,
					'aaData' => array()
				);

				if(empty($records))
					jsonDTResponse('', array(), 'success');

				foreach ($records as $record) {
					$default_btn = '<span class="ep-icon ep-icon_minus-circle txt-gray-light fs-24"></span>';
					if($record['lang_default'] == 1){
						$default_btn = '<span class="ep-icon ep-icon_ok-circle txt-green fs-24"></span>';
					}

					$active_btn = '<a class="ep-icon ep-icon_minus-circle txt-gray-light fs-24 confirm-dialog" data-message="Are you sure you want to activate this language?" data-lang="'.$record['id_lang'].'" data-callback="change_lang_active"></span>';
					if($record['lang_active'] == 1){
						$active_btn = '<a class="ep-icon ep-icon_ok-circle txt-green fs-24 confirm-dialog" data-message="Are you sure you want to deactivate this language?" data-lang="'.$record['id_lang'].'" data-callback="change_lang_active"></span>';
					}

					$output['aaData'][] = array(
						'dt_id'   				=> $record['id_lang'],
						'dt_lang_name'     		=> $record['lang_name'],
						'dt_lang_iso2'      	=> $record['lang_iso2'],
						'dt_lang_google_abbr'   => $record['lang_google_abbr'],
						'dt_lang_url_type'      => $this->translations->translation_type[$record['lang_url_type']]['title'],
						'dt_lang_default'       => $default_btn,
						'dt_active'   			=> $active_btn,
						'dt_updated'			=> formatDate($record['lang_updated']),
						'dt_actions'    		=> '<a class="ep-icon ep-icon_pencil fancyboxValidateModalDT fancybox.ajax" href="'.__SITE_URL.'translations/popup_forms/edit_language_form/'.$record['id_lang'].'" data-title="Edit language"></a>'
					);
				}

				jsonResponse('', 'success', $output);
			break;
			case 'routings_list_dt':
                checkAdminAjax('manage_content');

				$params = array();

				if ($_POST['iSortingCols'] > 0) {
					for ($i = 0; $i < $_POST['iSortingCols']; $i++) {
						switch ($_POST["mDataProp_" . intval($_POST['iSortCol_' . $i])]) {
							case 'dt_id':
								$params['sort_by'][] = 'id_route-' . $_POST['sSortDir_' . $i];
							break;
						}
					}
				}

				$records = $this->translations->get_routings($params);
				$records_count = $this->translations->count_routings($params);

				$output = array(
					"sEcho" => intval($_POST['sEcho']),
					"iTotalRecords" => $records_count,
					"iTotalDisplayRecords" => $records_count,
					'aaData' => array()
				);

				if(empty($records)) {
					jsonDTResponse('', array(), 'success');
                }

				foreach ($records as $record) {
					$output['aaData'][] = array(
						'dt_id'   				=> $record['id_route'],
						'dt_route_controller'   => $record['route_controller'],
						'dt_route_action'      	=> $record['route_action'],
						'dt_route_replace'   	=> $record['route_replace'],
						'dt_route_position'		=>  '<a class="ep-icon ep-icon_arrows-up call-function" href="#" title="Raise higher" data-callback="change_route_weight" data-route="'.$record['id_route'].'" data-direction="up"></a>
											 		<a class="ep-icon ep-icon_arrows-down call-function" title="Lower below" data-callback="change_route_weight" data-route="'.$record['id_route'].'" data-direction="down"></a>',
						'dt_actions'    		=> '<a class="ep-icon ep-icon_pencil fancyboxValidateModalDT fancybox.ajax" href="'.__SITE_URL.'translations/popup_forms/edit_routing_form/'.$record['id_route'].'" data-title="Edit routing"></a>'
					);
				}

				jsonResponse('', 'success', $output);
			break;
			case 'translation_files_list_dt':
				if (!have_right('manage_translations')){
					jsonDTResponse(translate("systmess_error_page_permision"));
				}

				$params = array();
				$params = array(
                    'is_systmess'   => 0,
					'limit'         => (int) $_POST['iDisplayLength'],
                    'start'         => (int) $_POST['iDisplayStart'],
				);

				if(isset($_POST['translation_file'])){
					$params['translation_file'] = $_POST['translation_file'];
				}

				if(isset($_POST['translation_key'])){
					$params['translation_key'] = $_POST['translation_key'];
				}

				if(isset($_POST['keywords'])){
					$params['keywords'] = cleanInput($_POST['keywords']);
                }

                if(isset($_POST['module'])) {
                    $params['module'] = (int) cleanInput($_POST['module']);
                }

                if(isset($_POST['page'])) {
                    $params['page'] = (int) cleanInput($_POST['page']);
                }

                if(isset($_POST['tag'])) {
                    $params['tag'] = (int) cleanInput($_POST['tag']);
                }

                if(isset($_POST['page_url'])) {
                    $params['page_url'] = cleanInput($_POST['page_url']);
                }

                if (isset($_POST['lang'])) {
                    $params['with_lang'] = cleanInput($_POST['lang']);
                }

                if (isset($_POST['not_lang'])) {
                    $params['without_lang'] = cleanInput($_POST['not_lang']);
                }

                if (isset($_POST['need_review_lang'])) {
                    $params['need_review_lang'] = cleanInput($_POST['need_review_lang']);
                }

                if (isset($_POST['translation_updated_from'])) {
                    $translation_updated_from = cleanInput($_POST['translation_updated_from']);
                    $params['translation_updated_from'] = \DateTimeImmutable::createFromFormat('m/d/Y', $translation_updated_from)->format('Y-m-d');
                }

                if (isset($_POST['translation_updated_to'])) {
                    $translation_updated_to = cleanInput($_POST['translation_updated_to']);
                    $params['translation_updated_to'] = \DateTimeImmutable::createFromFormat('m/d/Y', $translation_updated_to)->format('Y-m-d');
                }

                if (isset($_POST['is_reviewed'])) {
                    $params['is_reviewed'] = (int) $_POST['is_reviewed'];
                }

				$records = $this->translations->get_translation_files($params);
				$records_count = $this->translations->count_translation_files($params);

				$output = array(
					"sEcho" => intval($_POST['sEcho']),
					"iTotalRecords" => $records_count,
					"iTotalDisplayRecords" => $records_count,
					'aaData' => array()
				);

				if(empty($records)){
					jsonDTResponse('', array(), 'success');
                }

                $languages = arrayByKey($this->translations->get_languages(), 'lang_iso2');
                $allowed_languages = $this->session->group_lang_restriction ? $this->session->group_lang_restriction_list : array();
                $keys_ids = array_flip(array_flip(array_column($records, 'id_key')));
                $pages_list = array();
                $modules_list = array();
                if(!empty($keys_ids)) {
                    $pages_meta = arrayByKey($this->translations->get_related_pages($keys_ids, array(
                        'columns' => array('`kpr`.`id_key`', '`p`.`id_page` as `page`', '`p`.`page_name`', '`m`.`id_module` as `module`', '`m`.`name_module` as `module_name`'),
                        'with'    => array('pages' => true, 'modules' => true),
                        'order'   => array('p.page_name' => 'asc')
                    )), 'id_key', true);
                }

                $have_rights_super_admin = have_right('super_admin');

				foreach ($records as $record) {
                    $pages = array();
                    $modules = array();
                    $key_id = $record['id_key'];
                    $record_i18n = array();
                    $record_i18n_filter = array();
                    $record_text_updated_date = null !== $record['translation_text_updated_at'] ? new DateTime($record['translation_text_updated_at']) : null;
                    $record_i18n_columns = json_decode($record['translation_localizations'], true);
                    if(null !== $record_i18n_columns) {
                        if($this->session->group_lang_restriction) {
                            $record_i18n_columns = array_filter($record_i18n_columns, function($i18n) use ($allowed_languages) {
                                return in_array($i18n['lang']['id'], $allowed_languages);
                            });
                        }

                        foreach ($record_i18n_columns as $lang_code => $i81n) {
                            if('en' === $lang_code || empty($i81n['text']['value'])) {
                                continue;
                            }

                            $lang_name = "Unknown";
                            $language = $languages[$lang_code];
                            $lang_code_upper = strtoupper($lang_code);
                            if(isset($languages[$lang_code])) {
                                $lang_name = !empty($language['lang_name']) ? $language['lang_name'] : $lang_name;
                            } else {
                                $lang_name = !empty($i81n['lang']['lang_name']) ? $i81n['lang']['lang_name'] : $lang_name;
                            }

                            $record_i18n_edit_url = __SITE_URL . "translations/popup_forms/edit_key_i18n/{$key_id}/lang/{$lang_code}";
                            $record_i18n_update_date = null;
                            $record_i18n_label_color = 'btn-primary';
                            $record_i18n_update_notice = "Translated in language: '{$lang_name}'";
                            if(null !== $i81n['text']['updated_at']) {
                                $record_i18n_update_date = new DateTime($i81n['text']['updated_at']);
                            } else if(null !== $i81n['text']['created_at']) {
                                $record_i18n_update_date = new DateTime($i81n['text']['created_at']);
                            }
                            if(null !== $record_i18n_update_date) {
                                $record_i18n_update_notice = "{$record_i18n_update_notice}. Last update: {$record_i18n_update_date->format('Y-m-d H:i:s')}";
                            }
                            if(
                                null !== $record_text_updated_date &&
                                (
                                    null === $record_i18n_update_date || $record_i18n_update_date < $record_text_updated_date
                                )
                            ) {
                                $record_i18n_label_color = 'btn-danger';
                                $record_i18n_update_notice = "{$record_i18n_update_notice}. Update required";
                            }

                            $record_i18n_filter[] = "
                                <a  href=\"{$record_i18n_edit_url}\"
                                    class=\"btn btn-xs {$record_i18n_label_color} mb-5 fancyboxValidateModalDT fancybox.ajax\"
                                    data-title=\"Edit translation\"
                                    title=\"{$record_i18n_update_notice}\">
                                    {$lang_code_upper}
                                </a>
                            ";
                        }
                    }

                    if(isset($pages_meta[$key_id])) {
                        $pages_list = array_column($pages_meta[$key_id], 'page_name', 'page');
                        $modules_list = array_column($pages_meta[$key_id], 'module_name', 'module');

                        foreach ($pages_list as $page_id => $page_name) {
                            $page_name = cleanOutput($page_name);
                            $pages[] = "
                                <li><a class=\"dt_filter\"
                                    data-title=\"Used on page\"
                                    data-value-text=\"{$page_name}\"
                                    data-value=\"{$page_id}\"
                                    data-name=\"page\"
                                    title=\"Used on page: '{$page_name}'\">
                                    {$page_name}
                                </a></li>
                            ";
                        }
                        foreach ($modules_list as $module_id => $module_name) {
                            $module_name = cleanOutput($module_name);
                            $modules[] = "
                                <a class=\"dt_filter\"
                                    data-title=\"Used in module\"
                                    data-value-text=\"{$module_name}\"
                                    data-value=\"{$module_id}\"
                                    data-name=\"module\"
                                    title=\"Used in module: '{$module_name}'\">
                                    {$module_name}
                                </a>
                            ";
                        }
                    }
                    $pages_dropdown_dt = '';
                    if(!empty($pages)){
                        $pages_dropdown_dt = '
                        <div class="dropdown">
                            <a class="ep-icon ep-icon_menu-circles dropdown-toggle" data-toggle="dropdown"></a>
                            <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu2">
                            ' . implode('', $pages) . '
                            </ul>
                        </div>';
                    }

                    $edit_button = "";
                    $parse_button = "";
                    $files_button = "";
                    $delete_button = "";
                    $i18n_add_button = "";
                    $record_i18n_filter = implode('', $record_i18n_filter);
                    if(have_right('manage_content')) {
                        $edit_url = __SITE_URL . "translations/popup_forms/edit_file_translation_key_form/{$record['id_key']}";
                        $edit_button = "
                            <a href=\"{$edit_url}\"
                                class=\"fancyboxValidateModalDT fancybox.ajax\"
                                title=\"Edit static text\"
                                data-title=\"Edit static text\">
                                <i class=\"ep-icon ep-icon_pencil \"></i>
                            </a>
                        ";

                        if ($have_rights_super_admin) {
                            $delete_button = "
                            <a href=\"#\"
                                class=\"confirm-dialog\"
                                title=\"Delete static text\"
                                data-callback=\"translation_key_delete\"
                                data-key=\"{$record['id_key']}\"
                                data-title=\"Delete static text\"
                                data-message=\"Are you sure you want to delete this static text?\">
                                <i class=\"ep-icon ep-icon_remove txt-red\"></i>
                            </a>
                        ";
                        }

                        $files_url = __SITE_URL . "translations/popup_forms/edit_keys_file_entries_form/{$record['id_key']}";
                        $files_button = "
                            <a href=\"{$files_url}\"
                                class=\"fancyboxValidateModalDT fancybox.ajax\"
                                title=\"Edit the key location\"
                                data-title=\"Edit the key location\">
                                <i class=\"ep-icon ep-icon_file-edit fs-20 lh-20\"></i>
                            </a>
                        ";

                        $parse_ulr = __SITE_URL . "translations/ajax_operations/parse_files_for_key/{$record['id_key']}";
                        $parse_button = "
                            <a href=\"#\"
                                class=\"call-function\"
                                title=\"Update key location\"
                                data-url=\"{$parse_ulr}\"
                                data-key=\"{$record['id_key']}\"
                                data-title=\"Update key location\"
                                data-callback=\"translationKeyLocationUpdate\">
                                <i class=\"ep-icon ep-icon_file-view txt-green fs-20 lh-20\"></i>
                            </a>
                        ";

                        $reviewUrl = __SITE_URL . "translations/ajax_operations/switch_reviewed_status/{$record['id_key']}";
                        $reviewButtonTitle = $record['is_reviewed'] ? 'Mark as not-reviewed' : 'Mark as reviewed';
                        $reviewButtonIconColor = $record['is_reviewed'] ? 'green' : 'red';

                        $reviewButton = "
                            <a href=\"#\"
                                class=\"confirm-dialog\"
                                title=\"{$reviewButtonTitle}\"
                                data-url=\"{$reviewUrl}\"
                                data-title=\"{$reviewButtonTitle}\"
                                data-message=\"Are you sure you want to switch reviewed status?\"
                                data-callback=\"switchReviewedStatus\">
                                <i class=\"ep-icon ep-icon_sheild-ok txt-{$reviewButtonIconColor} fs-20 lh-20\"></i>
                            </a>
                        ";
                    }
                    if(have_right('manage_translations')) {
                        if(null === $record_i18n_columns || !empty(array_diff_key($languages, $record_i18n_columns))) {
                            $i18n_add_url = __SITE_URL . "translations/popup_forms/add_key_i18n/{$key_id}";
                            $i18n_add_button = "
                                <a href=\"{$i18n_add_url}\"
                                    data-title=\"Add translation\"
                                    title=\"Add translation\"
                                    class=\"fancyboxValidateModalDT fancybox.ajax\">
                                    <i class=\"ep-icon ep-icon_globe-circle\"></i>
                                </a>
                            ";
                        }
                    }

					$output['aaData'][] = array(
						'dt_id'   			=> $record['id_key'],
						'dt_key'			=> $record['translation_key'],
						'dt_default_value'  => $record['translation_text'],
                        'dt_filename'   	=> have_right('manage_content') ? $record['file_name'] : null,
                        'dt_pages'          => $pages_dropdown_dt,
                        'dt_modules'        => implode('', $modules),
                        'dt_translations'   => $record_i18n_filter,
                        'dt_actions'    	=> "
                            {$i18n_add_button}
                            {$edit_button}
                            {$parse_button}
                            {$files_button}
                            {$delete_button}
                            {$reviewButton}
                        "
					);
				}

				jsonResponse('', 'success', $output);
			break;
            case 'system_messages_dt':
                checkPermisionAjaxDT('manage_content,manage_translations');

                $dtFilters = dtConditions($_POST, [
                    ['key' => 'translation_updated_from',   'as' => 'translation_updated_from',     'type' => 'getDateFormat:m/d/Y,Y-m-d'],
                    ['key' => 'translation_updated_to',     'as' => 'translation_updated_to',       'type' => 'getDateFormat:m/d/Y,Y-m-d'],
                    ['key' => 'need_review_lang',           'as' => 'need_review_lang',             'type' => 'cleanInput'],
                    ['key' => 'translation_key',            'as' => 'translation_key',              'type' => 'cleanInput'],
                    ['key' => 'keywords',                   'as' => 'keywords',                     'type' => 'cleanInput'],
                    ['key' => 'page_url',                   'as' => 'page_url',                     'type' => 'cleanInput'],
                    ['key' => 'not_lang',                   'as' => 'without_lang',                 'type' => 'cleanInput'],
                    ['key' => 'module',                     'as' => 'module',                       'type' => 'int'],
                    ['key' => 'page',                       'as' => 'page',                         'type' => 'int'],
                    ['key' => 'tag',                        'as' => 'tag',                          'type' => 'int'],
                    ['key' => 'lang',                       'as' => 'with_lang',                    'type' => 'cleanInput'],
                    ['key' => 'is_reviewed',                'as' => 'is_reviewed',                  'type' => 'int'],
                ]);

                $conditions = array_merge(
                    $dtFilters,
                    [
                        'is_systmess'   => 1,
                        'limit'         => (int) $_POST['iDisplayLength'],
                        'start'         => (int) $_POST['iDisplayStart'],
                    ]
                );

                /** @var Translations_Model $translationModel*/
                $translationModel = model(Translations_Model::class);

				$records = $translationModel->get_translation_files($conditions);
				$recordsCount = $translationModel->count_translation_files($conditions);

				$output = [
					"iTotalDisplayRecords"  => $recordsCount,
					"iTotalRecords"         => $recordsCount,
					'aaData'                => [],
					"sEcho"                 => (int) $_POST['sEcho'],
                ];

				if (empty($records)) {
                    jsonResponse('', 'success', $output);
                }

                $languages = arrayByKey($translationModel->get_languages(), 'lang_iso2');
                $allowedLanguages = session()->group_lang_restriction ? session()->group_lang_restriction_list : [];
                $keysIds = array_column($records, 'id_key');

                if (!empty($keysIds)) {
                    $pagesMeta = arrayByKey($translationModel->get_related_pages($keysIds, [
                        'columns' => ['`kpr`.`id_key`', '`p`.`id_page` as `page`', '`p`.`page_name`', '`m`.`id_module` as `module`', '`m`.`name_module` as `module_name`'],
                        'with'    => ['pages' => true, 'modules' => true],
                        'order'   => ['p.page_name' => 'asc'],
                    ]), 'id_key', true);
                }

                $haveRightSuperAdmin = have_right('super_admin');
                $haveRightManageTranslations = have_right('manage_translations');
                $haveRightManageContent = have_right('manage_content');

				foreach ($records as $record) {
                    $pages = $modules = $recordI18nFilter = [];
                    $recordTextUpdatedDate = isset($record['translation_text_updated_at']) ? new DateTime($record['translation_text_updated_at']) : null;
                    $recordI18nColumns = json_decode($record['translation_localizations'], true);
                    if (null !== $recordI18nColumns) {
                        if (session()->group_lang_restriction) {
                            $recordI18nColumns = array_filter($recordI18nColumns, function($i18n) use ($allowedLanguages) {
                                return in_array($i18n['lang']['id'], $allowedLanguages);
                            });
                        }

                        foreach ($recordI18nColumns as $langCode => $i81n) {
                            if ('en' === $langCode || empty($i81n['text']['value'])) {
                                continue;
                            }

                            $langCodeUpper = strtoupper($langCode);
                            $langName = empty($languages[$langCode]['lang_name']) ? (empty($i81n['lang']['lang_name']) ? 'Unknown' : $i81n['lang']['lang_name']) : $languages[$langCode]['lang_name'];

                            if (null !== $i81n['text']['updated_at']) {
                                $recordI18nUpdateDate = new DateTime($i81n['text']['updated_at']);
                            } elseif(null !== $i81n['text']['created_at']) {
                                $recordI18nUpdateDate = new DateTime($i81n['text']['created_at']);
                            }

                            $recordI18nUpdateNotice = "Translated in language: '{$langName}'";
                            if (isset($recordI18nUpdateDate)) {
                                $recordI18nUpdateNotice .= ". Last update: {$recordI18nUpdateDate->format('Y-m-d H:i:s')}";
                            }

                            $recordI18nLabelColor = 'btn-primary';

                            if (isset($recordTextUpdatedDate) && (!isset($recordI18nUpdateDate) || $recordI18nUpdateDate < $recordTextUpdatedDate)) {
                                $recordI18nLabelColor = 'btn-danger';
                                $recordI18nUpdateNotice .= ". Update required";
                            }

                            $recordI18nEditUrl = __SITE_URL . "translations/popup_forms/edit_key_i18n/{$record['id_key']}/lang/{$langCode}";

                            $recordI18nFilter[] = "
                                <a  href=\"{$recordI18nEditUrl}\"
                                    class=\"btn btn-xs {$recordI18nLabelColor} mb-5 fancyboxValidateModalDT fancybox.ajax\"
                                    data-title=\"Edit translation\"
                                    title=\"{$recordI18nUpdateNotice}\">
                                    {$langCodeUpper}
                                </a>
                            ";
                        }
                    }

                    if (isset($pagesMeta[$record['id_key']])) {
                        $pagesList = array_column($pagesMeta[$record['id_key']], 'page_name', 'page');
                        $modulesList = array_column($pagesMeta[$record['id_key']], 'module_name', 'module');

                        foreach ($pagesList as $pageId => $pageName) {
                            $pageName = cleanOutput($pageName);
                            $pages[] = "
                                <li><a class=\"dt_filter\"
                                    data-title=\"Used on page\"
                                    data-value-text=\"{$pageName}\"
                                    data-value=\"{$pageId}\"
                                    data-name=\"page\"
                                    title=\"Used on page: '{$pageName}'\">
                                    {$pageName}
                                </a></li>
                            ";
                        }

                        foreach ($modulesList as $moduleId => $moduleName) {
                            $moduleName = cleanOutput($moduleName);
                            $modules[] = "
                                <a class=\"dt_filter\"
                                    data-title=\"Used in module\"
                                    data-value-text=\"{$moduleName}\"
                                    data-value=\"{$moduleId}\"
                                    data-name=\"module\"
                                    title=\"Used in module: '{$moduleName}'\">
                                    {$moduleName}
                                </a>
                            ";
                        }
                    }

                    if (!empty($pages)) {
                        $pagesDropdown = '
                        <div class="dropdown">
                            <a class="ep-icon ep-icon_menu-circles dropdown-toggle" data-toggle="dropdown"></a>
                            <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu2">
                            ' . implode('', $pages) . '
                            </ul>
                        </div>';
                    }

                    $editButton = $parseButton = $filesButton = $deleteButton = $i18nAddButton = "";
                    $recordI18nFilter = implode('', $recordI18nFilter);

                    if ($haveRightManageContent) {
                        $editUrl = __SITE_URL . "translations/popup_forms/edit_file_translation_key_form/{$record['id_key']}";
                        $editButton = "
                            <a href=\"{$editUrl}\"
                                class=\"fancyboxValidateModalDT fancybox.ajax\"
                                title=\"Edit static text\"
                                data-title=\"Edit static text\">
                                <i class=\"ep-icon ep-icon_pencil \"></i>
                            </a>
                        ";

                        if ($haveRightSuperAdmin) {
                            $deleteButton = "
                            <a href=\"#\"
                                class=\"confirm-dialog\"
                                title=\"Delete static text\"
                                data-callback=\"translation_key_delete\"
                                data-key=\"{$record['id_key']}\"
                                data-title=\"Delete static text\"
                                data-message=\"Are you sure you want to delete this static text?\">
                                <i class=\"ep-icon ep-icon_remove txt-red\"></i>
                            </a>
                        ";
                        }

                        $filesUrl = __SITE_URL . "translations/popup_forms/edit_keys_file_entries_form/{$record['id_key']}";
                        $filesButton = "
                            <a href=\"{$filesUrl}\"
                                class=\"fancyboxValidateModalDT fancybox.ajax\"
                                title=\"Edit the key location\"
                                data-title=\"Edit the key location\">
                                <i class=\"ep-icon ep-icon_file-edit fs-20 lh-20\"></i>
                            </a>
                        ";

                        $parseUrl = __SITE_URL . "translations/ajax_operations/parse_files_for_key/{$record['id_key']}";
                        $parseButton = "
                            <a href=\"#\"
                                class=\"call-function\"
                                title=\"Update key location\"
                                data-url=\"{$parseUrl}\"
                                data-key=\"{$record['id_key']}\"
                                data-title=\"Update key location\"
                                data-callback=\"translationKeyLocationUpdate\">
                                <i class=\"ep-icon ep-icon_file-view txt-green fs-20 lh-20\"></i>
                            </a>
                        ";

                        $reviewUrl = __SITE_URL . "translations/ajax_operations/switch_reviewed_status/{$record['id_key']}";
                        $reviewButtonTitle = $record['is_reviewed'] ? 'Mark as not-reviewed' : 'Mark as reviewed';
                        $reviewButtonIconColor = $record['is_reviewed'] ? 'green' : 'red';

                        $reviewButton = "
                            <a href=\"#\"
                                class=\"confirm-dialog\"
                                title=\"{$reviewButtonTitle}\"
                                data-url=\"{$reviewUrl}\"
                                data-title=\"{$reviewButtonTitle}\"
                                data-message=\"Are you sure you want to switch reviewed status?\"
                                data-callback=\"switchReviewedStatus\">
                                <i class=\"ep-icon ep-icon_sheild-ok txt-{$reviewButtonIconColor} fs-20 lh-20\"></i>
                            </a>
                        ";
                    }

                    if ($haveRightManageTranslations) {
                        if (null === $recordI18nColumns || !empty(array_diff_key($languages, $recordI18nColumns))) {
                            $i18nAddUrl = __SITE_URL . "translations/popup_forms/add_key_i18n/{$record['id_key']}";
                            $i18nAddButton = "
                                <a href=\"{$i18nAddUrl}\"
                                    data-title=\"Add translation\"
                                    title=\"Add translation\"
                                    class=\"fancyboxValidateModalDT fancybox.ajax\">
                                    <i class=\"ep-icon ep-icon_globe-circle\"></i>
                                </a>
                            ";
                        }
                    }

					$output['aaData'][] = [
						'dt_id'   			=> $record['id_key'],
						'dt_key'			=> $record['translation_key'],
						'dt_default_value'  => $record['translation_text'],
                        'dt_filename'   	=> $haveRightManageContent ? $record['file_name'] : null,
                        'dt_pages'          => $pagesDropdown ?? '',
                        'dt_modules'        => implode('', $modules),
                        'dt_translations'   => $recordI18nFilter,
                        'dt_actions'    	=> "
                            {$i18nAddButton}
                            {$editButton}
                            {$parseButton}
                            {$filesButton}
                            {$deleteButton}
                            {$reviewButton}
                        "
                    ];
				}

				jsonResponse('', 'success', $output);
			break;
            case 'translation_files_to_db':
                jsonResponse("This action is deprecated");

                checkAdminAjax('manage_content');

				$langs_keys = array();
				$insert = array();
				$lang_folders = array('ar','cn','es','hi','pt','ru','vi');

				foreach(glob('languages/en/*_lang.php') as $lang_file) {
					$file_name = str_replace('languages/en/','',$lang_file);
					if(in_array($file_name, array('categories_lang.php','industries_lang.php', 'countries_lang.php'))){
						continue;
					}

					$lang = array();
					require $lang_file;

					foreach ($lang as $lang_key => $lang_value) {
						$insert[$lang_key] = array(
							'translation_key' => $lang_key,
							'translation_text' => $lang_value,
							'file_name' => $file_name,
							'lang_en' => $lang_value,
							'lang_ar' => '',
							'lang_cn' => '',
							'lang_es' => '',
							'lang_hi' => '',
							'lang_pt' => '',
							'lang_ru' => '',
						);
					}


					foreach ($lang_folders as $lang_folder_name) {
						if(!file_exists('languages/'.$lang_folder_name.'/'.$file_name)){
							continue;
						}

						$lang = array();
						require 'languages/'.$lang_folder_name.'/'.$file_name;

						foreach ($lang as $lang_key => $lang_value) {
							if(isset($insert[$lang_key])){
								$insert[$lang_key]['lang_'.$lang_folder_name] = $lang_value;
							}
						}
					}
				}

				$insert_db = array_map(function($row){ return $b[] = $row;}, $insert, $b = array());
				$this->translations->insert_translations_files_batch($insert_db);
				jsonResponse('The files has been inserted into DB.', 'success');
			break;
            case 'translation_files_db_to_files':
                checkAdminAjax('manage_content,manage_translations');

                $languages = $this->translations->get_active_languages('lang_iso2');
				$records = $this->translations->get_translation_files(array("file_type" => "php"));
                $this->save_lang_js_file($languages);
                $this->save_lang_js_files($languages);
				$files_data = array();
				foreach ($records as $record) {
                    $localizations = json_decode($record['translation_localizations'], true);
                    $localizations = !empty($localizations) ? $localizations : array();

                    $files_data[$record['file_name']]['en'][$record['translation_key']] = trim(addslashes($record['translation_text']));
					foreach ($languages as $lang) {
						if('en' === $lang || !isset($localizations[$lang]) || empty($localizations[$lang]['text']['value'])){
							continue;
						}

						$files_data[$record['file_name']][$lang][$record['translation_key']] = trim(addslashes($localizations[$lang]['text']['value']));
					}
                }

				foreach($files_data as $file_name => $file_data){
					foreach ($file_data as $lang_key => $lang_values) {
						$this->translations->save_lang_file($lang_key, $file_name, $lang_values);
					}
				}

				jsonResponse('The translations has been inserted into files.', 'success');
			break;
            case "translation_key_edit":
                checkAdminAjax('manage_content');

				$validator_rules = array(
					array(
						'field' => 'id_key',
						'label' => 'Translation key',
						'rules' => array('required' => '', 'integer' => '')
                    ),
                    array(
                        'field' => "text",
                        'label' => "File type",
                        'rules' => array('required' => '', 'max_len[63000]' => ''),
                    ),
                    array(
                        'field' => "usage",
                        'label' => "Usage example",
                        'rules' => array('max_len[63000]' => ''),
                    )
				);

                $this->validator->set_rules($validator_rules);

				if (!$this->validator->validate()) {
					jsonResponse($this->validator->get_array_errors());
				}

				$id_key = (int) $_POST['id_key'];
                $translation = $this->translations->get_translation_file($id_key);

				if (empty($translation)) {
					jsonResponse('Error: The translation key does not exist.');
				}

				$update = array(
					'usage_example'   => cleanInput($_POST['usage_example']),
					'translation_old'  => (int) filter_var($_POST['translation_old'], FILTER_VALIDATE_BOOLEAN),
					'translation_text' => trim($_POST['text']),
					'lang_en'          => trim($_POST['text']),
                );

                $is_updated_text = false;

                if ($translation['translation_text'] !== $update['translation_text']) {
                    $is_updated_text = true;
                    $now = new DateTime();
                    $update['translation_text_updated_at'] = $now->format('Y-m-d H:i:s');
                }

				if (!$this->translations->update_translation_key($id_key, $update)) {
                    jsonResponse('The translation changes were not saved due to a server error.');
                }

                $new_pages = array_filter(array_map(
                    function($page) { return (int) cleanInput($page); },
                    !empty($_POST['pages']) ? $_POST['pages'] : array()
                ));

                $old_pages = array_filter(array_map(
                    function($old_page) { return (int) cleanInput($old_page); },
                    !empty($_POST['old_pages']) ? $_POST['old_pages'] : array()
                ));

                $added_pages = array_diff($new_pages, $old_pages);
                $removed_pages = array_diff($old_pages, $new_pages);
                $is_updated_pages = false;

                if (!empty($added_pages) || !empty($removed_pages)) {
                    $is_updated_pages = true;

                    if (!$this->translations->replace_pages_relationship($id_key, $new_pages)) {
                        jsonResponse('The translation changes were only partially saved due to a server error.');
                    };
                }

                $tags = array_filter(array_map(
                    function($tag) { return (int) cleanInput($tag); },
                    !empty(request()->get('tags')) ? request()->get('tags') : []
                ));


                $oldTags = array_filter(array_map(
                    function($tag) { return (int) cleanInput($tag); },
                    !empty(request()->get('old_tags')) ? request()->get('old_tags') : []
                ));

                $addedTags = array_diff($tags, $oldTags);
                $removedTags = array_diff($oldTags, $tags);
                $isUpdatedTags = false;

                if (!empty($addedTags) || !empty($removedTags)) {
                    $isUpdatedTags = true;

                    if (!$this->translations->replaceTagsRelationship($id_key, $tags)) {
                        jsonResponse('The translation changes were only partially saved due to a server error.');
                    };
                }

                if ($is_updated_text) {
                    $log_data = array(
                        'id_user'           => privileged_user_id(),
                        'id_key'            => $id_key,
                        'id_lang'           => 1,
                        'translation_key'   => $translation['translation_key'],
                        'action'            => 'edit original text',
                        'new_value'         => $update['translation_text']
                    );

                    $this->write_translation_log($log_data);
                }

                if ($is_updated_pages) {
                    $changed_pages_ids = array_merge($added_pages, $removed_pages);
                    $changed_pages_data = model('pages')->get_pages(array('columns'=> 'id_page,page_name', 'conditions' => array('page_in' => implode(',', $changed_pages_ids))));
                    $changed_pages_data = array_column($changed_pages_data, 'page_name', 'id_page');

                    if (!empty($removed_pages)) {
                        foreach ($removed_pages as $removed_page_id) {
                            $removed_pages_data[] = $changed_pages_data[$removed_page_id];
                        }

                        $log_msg[] = 'Removed page(s): ' . implode(',', $removed_pages_data);
                    }

                    if (!empty($added_pages)) {
                        foreach ($added_pages as $added_page_id) {
                            $added_pages_data[] = $changed_pages_data[$added_page_id];
                        }

                        $log_msg[] = 'Added page(s): ' . implode(',', $added_pages_data);
                    }

                    $log_data = array(
                        'id_user'           => privileged_user_id(),
                        'id_key'            => $id_key,
                        'id_lang'           => 1,
                        'translation_key'   => $translation['translation_key'],
                        'action'            => 'change on pages',
                        'new_value'         => implode(' || ', $log_msg),
                    );

                    $this->write_translation_log($log_data);
                }

                if ($isUpdatedTags) {
                    $changedTagsIds = array_merge($addedTags, $removedTags);
                    $changedTagsData = $this->translations->getTags($changedTagsIds);
                    $changedTagsData = array_column($changedTagsData, 'name', 'id');

                    if (!empty($removedTags)) {
                        $removedTagsData = [];
                        foreach ($removedTags as $removedTagId) {
                            $removedTagsData[] = $removedTagsData[$removedTagId];
                        }

                        $logMsg[] = 'Removed tag(s): ' . implode(',', $removedTagsData);
                    }

                    if (!empty($addedTags)) {
                        $addedTagsData = [];
                        foreach ($addedTags as $addedId) {
                            $addedTagsData[] = $changedTagsData[$addedId];
                        }

                        $logMsg[] = 'Added tag(s): ' . implode(',', $addedTagsData);
                    }

                    $logData = array(
                        'id_user'           => privileged_user_id(),
                        'id_key'            => $id_key,
                        'id_lang'           => 1,
                        'translation_key'   => $translation['translation_key'],
                        'action'            => 'change on tags',
                        'new_value'         => implode(' || ', $logMsg),
                    );

                    $this->write_translation_log($logData);
                }

                jsonResponse('The translation changes has been saved.', 'success');

			break;
			case "translation_key_add":
                checkAdminAjax('manage_content');

                $validation_rules = array(
                    array(
                        'field' => "translation_key",
                        'label' => "Translation key",
                        'rules' => array('required' => '', 'max_len[1000]' => ''),
                    ),
                    array(
                        'field' => "pages",
                        'label' => "Pages",
                        'rules' => array(/*'required' => ''*/),
                    ),
                    array(
                        'field' => "file_name",
                        'label' => "File name",
                        'rules' => array(/*'required' => '',*/ 'max_len[50]' => ''),
                    ),
                    array(
                        'field' => "file_type",
                        'label' => "File type",
                        'rules' => array('required' => '', 'in[php,js]' => '', 'message' => 'OMG'),
                    ),
                    array(
                        'field' => "text",
                        'label' => "File type",
                        'rules' => array('required' => '', 'max_len[63000]' => ''),
                    ),
                    array(
                        'field' => "usage",
                        'label' => "Usage example",
                        'rules' => array('max_len[63000]' => ''),
                    )
                );

                if (!empty($validation_rules)) {
                    $this->validator->set_rules($validation_rules);
                    if (!$this->validator->validate()) {
                        jsonResponse($this->validator->get_array_errors());
                    }
                }

				$file_extension = "php";
                $extensions = array('php', 'js');
				if (!empty($_POST["file_name"])) {
                    $file_extension = pathinfo($_POST["file_name"], PATHINFO_EXTENSION);
                    if (!in_array($file_extension, $extensions)) {
                        jsonResponse('Error: File extension is not supported.');
                    }
                }

                if (!in_array($_POST["file_type"], $extensions) || $file_extension != $_POST["file_type"]) {
                    jsonResponse('Error: The file extension doesn\'t match the selected file type.');
                }

				$translation_key = cleanInput($_POST['translation_key']);
				$translation_file = $this->translations->get_translation_file_key($translation_key);
				if (!empty($translation_file)) {
					jsonResponse('Error: The translation key already exists.');
				}

				$insert = array(
					'translation_key'  => $translation_key,
					'translation_old'  => (int) filter_var($_POST['old'], FILTER_VALIDATE_BOOLEAN),
					'translation_text' => trim($_POST['text']),
					'lang_en'          => trim($_POST['text']),
					'file_name'        => cleanInput($_POST['file_name']),
                    'file_type'        => cleanInput($_POST['file_type']),
                    'usage_example'    => cleanInput($_POST['usage']),
                    'is_systmess'      => (int) filter_var($_POST['is_systmess'], FILTER_VALIDATE_BOOLEAN)
                );

                $key_id = $this->translations->insert_translation_key($insert);
                if (!$key_id) {
                    jsonResponse('The translation was not added due to server error.');
                }

                $pages = array_filter(array_map(
                    function($page) { return (int) cleanInput($page); },
                    !empty($_POST['pages']) ? $_POST['pages'] : array()
                ));

                if (!empty($pages)) {
                    if(!$this->translations->create_pages_relationship($key_id, $pages)) {
                        jsonResponse('The translation was only partially added due to a server error.');
                    }
                }

                $tags = array_filter(array_map(
                    function($tag) { return (int) cleanInput($tag); },
                    !empty(request()->get('tags')) ? request()->get('tags') : []
                ));

                if (!empty($tags)) {
                    if(!$this->translations->createTagsRelationship($key_id, $tags)) {
                        jsonResponse('The translation was only partially added due to a server error.');
                    }
                }

                $log_data = array(
                    'id_user'           => privileged_user_id(),
                    'id_key'            => $key_id,
                    'id_lang'           => 1,
                    'translation_key'   => $insert['translation_key'],
                    'action'            => 'create key',
                    'new_value'         => $insert['translation_text']
                );

                $this->write_translation_log($log_data);

				jsonResponse('The translation has been added.', 'success');
            break;
            case "translation_key_multiple_add":
                checkAdminAjax('manage_content');

                //region validation
                $validation_rules = array(
                    array(
                        'field' => "pages",
                        'label' => "Pages",
                        'rules' => array('required' => ''),
                    ),
                    array(
                        'field' => "file_name",
                        'label' => "File name",
                        'rules' => array('required' => '', 'max_len[50]' => ''),
                    ),
                    array(
                        'field' => "file_type",
                        'label' => "File type",
                        'rules' => array('required' => '', 'in[php,js]' => '', 'message' => 'OMG'),
                    )
                );

                if (!empty($validation_rules)) {
                    $this->validator->set_rules($validation_rules);
                    if (!$this->validator->validate()) {
                        jsonResponse($this->validator->get_array_errors());
                    }
                }
                //endregion validation

                //region file
				$file_extension = "php";
                $extensions = array('php', 'js');
				if (!empty(arrayGet($_POST, "file_name"))) {
                    $file_extension = pathinfo(arrayGet($_POST, "file_name"), PATHINFO_EXTENSION);
                    if (!in_array($file_extension, $extensions)) {
                        jsonResponse('Error: File extension is not supported.');
                    }
                }

                if (!in_array(arrayGet($_POST, "file_type"), $extensions) || $file_extension != arrayGet($_POST, "file_type")) {
                    jsonResponse('Error: The file extension doesn\'t match the selected file type.');
                }
                //endregion file

                $translation_old = (int) filter_var(arrayGet($_POST, 'old'), FILTER_VALIDATE_BOOLEAN);

                //region translations key pairs
                $translations_pairs = arrayGet($_POST, 'translation_keys');
				if (isset($translations_pairs)) {
					$all_translation_keys = $translations_pairs['key'];
                    $all_translation_values = $translations_pairs['value'];

                    foreach ($all_translation_keys as $key_number => $key)
                    {
						$translation_key = cleanInput($key);
						$translation_value = trim($all_translation_values[$key_number]);

						if (empty($translation_key) || empty($translation_value)){
                            continue;
                        }

                        $translation_file = $this->translations->get_translation_file_key($translation_key);

                        if (!empty($translation_file))
                        {
                            jsonResponse('Error: The translation key "' . $translation_key . '" already exists.');
                        }
                        $translations_insert[] = array(
                            'translation_key'  => $translation_key,
                            'translation_text' => $translation_value,
                            'translation_old'  => $translation_old,
                            'lang_en'          => $translation_value,
                            'file_name'        => arrayGet($_POST, 'file_name'),
                            'file_type'        => arrayGet($_POST, 'file_type')
                        );
					}
                }
                //endregion translations key pairs

                if (!$this->translations->insert_translations_batch($translations_insert)) {
					jsonResponse('Error: You cannot add translations now. Please try again later.');
                }

                $last_added_translations = $this->translations->get_translations_by_keys($all_translation_keys);
                $last_inserted_ids = array_column($last_added_translations, 'id_key');

                //region page relationships add
                $pages = array_filter(array_map(
                    function($page) {
                        return (int) cleanInput($page);
                    },
                    arrayGet($_POST, 'pages')
                ));
                if (!empty($pages)) {
                    if(!$this->translations->create_pages_relationship_multiple_keys($last_inserted_ids, $pages)) {
                        jsonResponse('The translations were only partially added due to a server error.');
                    }
                }
                //endregion page relationships add

                //region log add
                foreach($last_added_translations as $one_trans){
                    $log_data[] = array(
                        'id_user'           => privileged_user_id(),
                        'id_key'            => $one_trans['id_key'],
                        'id_lang'           => 1,
                        'translation_key'   => $one_trans['translation_key'],
                        'action'            => 'create key',
                        'new_value'         => $one_trans['translation_text']
                    );
                }

                if(!$this->translations->log_batch($log_data)){
                    jsonResponse('A server error occurred during the process of adding translation log');
                }
                //endregion log add

                jsonResponse('The translation has been added.', 'success');
            break;
			case "translation_key_delete":
                checkAdminAjax('super_admin');

				$validator_rules = array(
					array(
						'field' => 'id_key',
						'label' => 'Translation key',
						'rules' => array('required' => '', 'integer' => '')
					)
				);

				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate()) {
					jsonResponse($this->validator->get_array_errors());
				}

                $id_key = (int)$_POST['id_key'];
                $key_data = $this->translations->get_translation_file($id_key);

				if(
                    !$this->translations->remove_pages_relationship($id_key) ||
                    !$this->translations->delete_translation_key($id_key)
                ) {
                    jsonResponse('The translation key was not deleted due to a server error.');
                }

                $log_data = array(
                    'id_user'           => privileged_user_id(),
                    'id_key'            => $id_key,
                    'id_lang'           => 1,
                    'translation_key'   => $key_data['translation_key'],
                    'action'            => 'delete key'
                );

                $this->write_translation_log($log_data);

				jsonResponse('The translation key has been deleted.', 'success');
			break;
			case "check_create_xls_single":
                checkAdminAjax('manage_content,manage_translations');

				$validator_rules = array(
					array(
						'field' => 'page',
						'label' => 'Page',
						'rules' => array()
					),
					array(
						'field' => 'file_name',
						'label' => 'File to translate',
						'rules' => array()
					),
					array(
						'field' => 'translate_to',
						'label' => 'Language',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'type_records',
						'label' => 'Records type',
						'rules' => array()
					)
				);

				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate()) {
					jsonResponse($this->validator->get_array_errors());
                }

                $get_params = array();

                $get_params['translate_to'] = $tlang = cleanInput($_POST['translate_to']);

                $get_params['records_type'] = $records_type = cleanInput($_POST['type_records']);
                $lang_file = '';

                switch ($records_type) {
                    case 'translated':
                        $params['with_lang'] = $tlang;
                    break;
                    case 'not_translated':
                        $params['without_lang'] = $tlang;
                    break;
                }

                if (!empty(cleanInput($_POST['file_name']))) {
                    $get_params['lang_file_name'] = $lang_file_name = cleanInput($_POST['file_name']);
                    $lang_file = pathinfo($lang_file_name, PATHINFO_FILENAME);
                    $params['translation_file'] = $lang_file_name;
                }

                if (!empty($_POST['page'])) {
                    $get_params['page'] = $params['page'] = (int) $_POST['page'];
                }

                $iframe_url = __SITE_URL . 'translations/create_xls_single';

                if (!empty($get_params)) {
                    $iframe_url .= '?' . http_build_query($get_params);
                }

				switch ($lang_file) {
					case 'industries_lang':
						# code...
					break;

					case 'categories_lang':
						# code...
					break;

                    case 'countries_lang':
                        #code ...
                    break;
					default:
						$this->translation_records = $translation_records = $this->translations->get_translation_files($params);
						if(empty($translation_records)){
							jsonResponse('Info: There are no content for translation.', 'info');
						}
					break;
				}

				jsonResponse('', 'success', array('url' => $iframe_url));
            break;
            case 'parse_files';
                checkAdminAjax('manage_content');

                $this->load->library('I18n_Key_Parser', 'i81n');
                $this->i81n->withBasePath($_SERVER['DOCUMENT_ROOT'])
                    ->clearExtensions()
                    ->withPath("/tinymvc/myapp")
                    ->withPath("/public/plug")
                    ->withExtension('js')
                    ->withExtension('php')
                    ->withPattern("/translate_js\(\{([^\}]+)?(text\:(\s+)?(?>(([\"'])((?:(?=(\\\\?))\\7.)*?)\\5))(\s+)?)([^\}]+)?\}\)/m", 6)
                    ->withGuess("/^calendar_m_(.+)/", function() {
                        $keys = array();
                        for ($i = 1; $i < 12; $i++) {
                            $keys[] = "calendar_m_" . str_pad($i, 2, '0', STR_PAD_LEFT);
                        }

                        return $keys;
                    })
                    ->withGuess("/^accreditation_documents_status_(.+)/", [
                        "accreditation_documents_status_init",
                        "accreditation_documents_status_decline",
                        "accreditation_documents_status_confirmed",
                        "accreditation_documents_status_processing",
                    ])
                    ->withKeys($this->i81n->makeI18nFromRaw($this->translations->get_translation_files()));

                try {
                    if(!$this->translations->replace_translation_keys_file_entries($this->i81n->parse()->toImportArray())) {
                        jsonResponse("Failed to write found records due to databse error");
                    };
                } catch (\Exception $exception) {
                    jsonResponse("Failed to find keys in files due to error: {$exception->getMessage()}");
                }

                jsonResponse("Translation keys lookup in files is finished successfully", "success");
            break;
            case 'parse_files_for_key':
                checkAdminAjax('manage_content');

                $id = (int) $this->uri->segment(4);
                $translation = $this->translations->get_translation_file($id);
                if(empty($translation)) {
                    jsonResponse('The i18n key is not found on this server');
                }

                $this->load->library('I18n_Key_Parser', 'i81n');
                $this->i81n->withBasePath($_SERVER['DOCUMENT_ROOT'])
                    ->clearPatterns()
                    ->clearGuesses()
                    ->clearExtensions()
                    ->withPath("/tinymvc/myapp")
                    ->withPath("/public/plug")
                    ->withExtension('js')
                    ->withExtension('php')
                    ->withPattern("/translate\((?>(([\"'])({$translation['translation_key']})\\2))(.*?)?\)/m", 3)
                    ->withPattern("/translate\((?>(([\"'])({$translation['translation_key']})\\2))/m", 3)
                    ->withPattern("/translate_js\(\{([^\}]+)?(text\:(\s+)?(?>(([\"'])({$translation['translation_key']})([\"'])))(\s+)?)([^\}]+)?\}\)/m", 6)
                    ->withKeys($this->i81n->makeI18nFromRaw(array($translation)));

                try {
                    $import = $this->i81n->parse()->toImportArray();
                    if(empty($import)) {
                        jsonResponse("Nothing to import for this key - no matches found", 'warning');
                    }

                    if(!$this->translations->replace_translation_keys_file_entries($import)) {
                        jsonResponse("Failed to write found records due to databse error");
                    };
                } catch (\Exception $exception) {
                    jsonResponse("Failed to find key in files due to error: {$exception->getMessage()}");
                }

                jsonResponse("Translation key lookup in files is finished successfully", "success");
            break;
            case 'update_translation_key_locations':
                checkAdminAjax('manage_content');

                $validation_rules = array();
                $validation_data = array();
                $files = !empty($_POST['files']) ? $_POST['files'] : array();
                if(!empty($files)) {
                    foreach ($files as $index => $file) {
                        $validation_data["file_{$index}"]  = $file;
                        $validation_rules[] = array(
                            'field' => "file_{$index}",
                            'label' => "File nr. " . ($index + 1),
                            'rules' => array('required' => '', 'max_len[1000]' => ''),
                        );
                    }
                }

				if(!empty($validation_rules)) {
                    $this->validator->reset_postdata();
                    $this->validator->clear_array_errors();
                    $this->validator->validate_data = $validation_data;
                    $this->validator->set_rules($validation_rules);
                    if (!$this->validator->validate()) {
                        jsonResponse($this->validator->get_array_errors());
                    }
                }

                $id = (int) $this->uri->segment(4);
                $translation = $this->translations->get_translation_file($id);
                if(empty($translation)) {
                    jsonResponse('The i18n key is not found on this server');
                }

                $files = array_filter(
                    array_map(function($file) {
                        $file = cleanInput($file);
                        if(empty($file)) {
                            return null;
                        }

                        return "/" . ltrim($file, '/');
                    }, $files)
                );

                $import = array(
                    array(
                        'id_key'                   => $translation['id_key'],
                        'translation_file_entries' => array(
                            'list' => $files
                        )
                    ),
                );
                if(!$this->translations->replace_translation_keys_file_entries($import)) {
                    jsonResponse("Failed to write file records due to databse error");
                };

                jsonResponse("Translation key file records were successfully updated", "success");
            break;
            case 'add_key_i18n':
                checkAdminAjax('manage_content,manage_translations');

                $validation_rules = array(
                    array(
                        'field' => "language",
                        'label' => "Language",
                        'rules' => array('required' => ''),
                    ),
                    array(
                        'field' => "translation",
                        'label' => "Translation",
                        'rules' => array('required' => ''),
                    )
                );

                if(!empty($validation_rules)) {
                    $this->validator->set_rules($validation_rules);
                    if (!$this->validator->validate()) {
                        jsonResponse($this->validator->get_array_errors());
                    }
                }

                $key_id = (int) $this->uri->segment(4);
                if(
                    empty($key_id) ||
                    empty($key = $this->translations->get_translation_file($key_id))
                ) {
                    jsonResponse("Translation key with this ID is not found on this server");
                }

                $lang_id = (int) cleanInput($_POST['language']);
                if($this->session->group_lang_restriction && !in_array($lang_id, $this->session->group_lang_restriction_list)) {
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));
                }
                if(
                    empty($lang_id) ||
                    empty($language = $this->translations->get_language($lang_id))
                ) {
                    jsonResponse("Language with this ID is not found on this server");
                }

                $now = new \DateTime();
                $lang_code = $language['lang_iso2'];
                $lang_name = $language['lang_name'];
                $translation = cleanInput($_POST['translation']);
                $localization = json_decode($key['translation_localizations'], true);
                $localization = !empty($localization) ? $localization : array();
                $localization[$lang_code] = array(
                    'text' => array('value' => $translation, 'created_at' => $now->format('Y-m-d H:i:s'), 'updated_at' => $now->format('Y-m-d H:i:s')),
                    'lang' => array('id' => $lang_id, 'abbr_iso2' => $lang_code, 'lang_name' => $lang_name),
                );

                $update_data = array(
                    "lang_{$lang_code}" => $translation,
                    'translation_localizations' => json_encode($localization, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                );
                if(!$this->translations->translation_file_column_exists("lang_{$lang_code}")) {
                    unset($update_data["lang_{$lang_code}"]);
                }

                if(!$this->translations->update_translation_key($key_id, $update_data)) {
                    jsonResponse("Failed to add static text translation due to error on save");
                }

                $log_data = array(
                    'id_user'           => privileged_user_id(),
                    'id_key'            => $key_id,
                    'id_lang'           => $lang_id,
                    'translation_key'   => $key['translation_key'],
                    'action'            => 'add translation',
                    'new_value'         => $translation
                );

                $this->write_translation_log($log_data);

                jsonResponse("New static text translation was added", 'success');
            break;
            case 'edit_key_i18n':
                checkAdminAjax('manage_content,manage_translations');

                $validation_rules = array(
                    array(
                        'field' => "language",
                        'label' => "Language",
                        'rules' => array('required' => ''),
                    ),
                    array(
                        'field' => "translation",
                        'label' => "Translation",
                        'rules' => array('required' => ''),
                    )
                );

                if(!empty($validation_rules)) {
                    $this->validator->set_rules($validation_rules);
                    if (!$this->validator->validate()) {
                        jsonResponse($this->validator->get_array_errors());
                    }
                }

                $key_id = (int) $this->uri->segment(4);
                if(
                    empty($key_id) ||
                    empty($key = $this->translations->get_translation_file($key_id))
                ) {
                    jsonResponse("Translation key with this ID is not found on this server");
                }

                $lang_id = (int) cleanInput($_POST['language']);
                if($this->session->group_lang_restriction && !in_array($lang_id, $this->session->group_lang_restriction_list)) {
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));
                }
                if(
                    empty($lang_id) ||
                    empty($language = $this->translations->get_language($lang_id))
                ) {
                    jsonResponse("Language with this ID is not found on this server");
                }


                $now = new \DateTime();
                $lang_code = $language['lang_iso2'];
                $lang_name = $language['lang_name'];
                $translation = $_POST['translation'];
                $localization = json_decode($key['translation_localizations'], true);
                $localization = !empty($localization) ? $localization : array();
                $localization_lang = array(
                    'id'        => $lang_id,
                    'lang_name' => $lang_name,
                    'abbr_iso2' => $lang_code,
                );
                if(!isset($localization[$lang_code])) {
                    jsonResponse("Translation to this language is not found");
                }

                $localization[$lang_code]['text']['value'] = $translation;
                $localization[$lang_code]['text']['updated_at'] = $now->format('Y-m-d H:i:s');
                if($localization[$lang_code]['lang'] != $localization_lang) {
                    $localization[$lang_code]['lang'] = $localization_lang;
                }

                $update_data = array(
                    "lang_{$lang_code}" => $translation,
                    'translation_localizations' => json_encode($localization, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                );
                if(!$this->translations->translation_file_column_exists("lang_{$lang_code}")) {
                    unset($update_data["lang_{$lang_code}"]);
                }

                if(!$this->translations->update_translation_key($key_id, $update_data)) {
                    jsonResponse("Failed to update static text translation due to error on save");
                }

                $log_data = array(
                    'id_user'           => privileged_user_id(),
                    'id_key'            => $key_id,
                    'id_lang'           => $lang_id,
                    'translation_key'   => $key['translation_key'],
                    'action'            => 'actualize translation',
                    'new_value'         => $translation
                );

                $this->write_translation_log($log_data);

                jsonResponse("Static text translation was updated", 'success');
            break;
            case 'switch_reviewed_status':
                /** @var Translations_Model $translationModel */
                $translationModel = model(Translations_Model::class);

                $idKey = (int) uri()->segment(4);

                if (empty($idKey) || empty($key = $translationModel->get_translation_file($idKey))) {
                    jsonResponse('The translation key is wrong.');
                }
                $isReviewed = filter_var($key['is_reviewed'], FILTER_VALIDATE_BOOL);
                $update = [
                    'is_reviewed'   => (int) !$isReviewed,
                    'reviewed_date' => $isReviewed ? null : (new \DateTime())->format('Y-m-d H:i:s'),
                ];

                $translationModel->update_translation_key($idKey, $update);

                jsonResponse('Reviewed status was successfully switched.', 'success');
            break;
		}
    }

    public function popup_forms()
    {
		if (!isAjaxRequest()) {
			headerRedirect();
        }

		$op = $this->uri->segment(3);
		switch ($op) {
            case 'upload_country_translation_form':
                checkAdminAjaxModal('manage_content');

				global $tmvc;
				$data['languages'] = $this->translations->get_languages();
				$data['upload_folder'] = encriptedFolderName();
				$data['fileupload_max_file_size'] = $tmvc->my_config['fileupload_max_file_size'];
				$this->view->assign($data);
				$this->view->display('admin/translations/upload_country_translations_form_view');
            break;
            case 'upload_industry_translation_form':
                checkAdminAjaxModal('manage_content');

                global $tmvc;
                $data['languages'] = $this->translations->get_languages();
                $data['upload_folder'] = encriptedFolderName();
                $data['fileupload_max_file_size'] = $tmvc->my_config['fileupload_max_file_size'];
                $this->view->assign($data);
                $this->view->display('admin/translations/upload_industry_translations_form_view');
            break;
			case 'upload_translate_form':
                checkAdminAjaxModal('manage_content');

				global $tmvc;
				$data['upload_folder'] = encriptedFolderName();
				$data['fileupload_max_file_size'] = $tmvc->my_config['fileupload_max_file_size'];
				$this->view->assign($data);
				$this->view->display('admin/translations/upload_translate_form_view');
			break;
            case 'create_xls_form':
                checkAdminAjaxModal('manage_content,manage_translations');
                $data['files'] = $this->translations->get_files_translations();

                $data['pages'] = array_column(model('pages')->get_pages(array(
                    'columns' => array('`id_page` as `id`', '`page_name` as `name`')
                )), 'name', 'id');

				$params = array(
					'lang_active' => 1
				);
				$data['tlanguages'] = $this->translations->get_languages($params);
				$this->view->assign($data);
				$this->view->display('admin/translations/create_xls_form_view');
			break;
			case 'add_language_form':
                checkAdminAjaxModal('manage_content');

				$this->view->display('admin/translations/add_language_form_view');
			break;
			case 'edit_language_form':
                checkAdminAjaxModal('manage_content');

				$id_lang = (int)$this->uri->segment(4);
				$data['tlanguage'] = $this->translations->get_language($id_lang);

				if(empty($data['tlanguage'])){
					messageInModal('Error: The language does not exist.');
				}

				$this->view->assign($data);
				$this->view->display('admin/translations/edit_language_form_view');
			break;
			case 'add_routing_form':
                checkAdminAjaxModal('manage_content');

				$params = array(
					'lang_active' => 1,
					'lang_url_type' => "'domain'"
				);
				$data['tlanguages'] = $this->translations->get_languages($params);
				$this->view->display('admin/translations/add_routing_form_view', $data);
			break;
			case 'edit_routing_form':
                checkAdminAjaxModal('manage_content');

				$id_route = (int)$this->uri->segment(4);
				$data['troute'] = $this->translations->get_routing($id_route);

				if(empty($data['troute'])){
					messageInModal('Error: The routing does not exist.');
				}

				$params = array(
					'lang_active' => 1,
					'lang_url_type' => "'domain','get_variable'"
				);
				$data['tlanguages'] = $this->translations->get_languages($params);
				$this->view->display('admin/translations/edit_routing_form_view', $data);
			break;
			case 'add_file_translation_key_form':
                checkAdminAjaxModal('manage_content');

                $this->load->model('Ep_Modules_Model', 'modules');
                $this->load->model('Pages_Model', 'pages');

                // Load the list of pages
                $data['pages'] = array_column($this->pages->get_pages(array(
                    'columns' => array('`id_page` as `id`', '`page_name` as `name`')
                )), 'name', 'id');

                $data['tags'] = array_column($this->translations->getTags(), 'name', 'id');
                // Load modules
                $data['modules'] = $this->modules->get_all_modules();
                $data['action'] = __SITE_URL . 'translations/ajax_operations/translation_key_add';
                $data['file_names'] = array();
                $files = $this->translations->get_files_translations();
                foreach ($files as $file) {
                    if(in_array($file, array('categories_lang.php','industries_lang.php', 'countries_lang.php'))){
                        continue;
                    }
                    $data['file_names'][] = $file;
                }

                if((bool) arrayGet($_GET, "systmess") === true){
                    $data["systmess"] = true;
                }

				$this->view->display('admin/translations/add_file_translation_key_form_view', $data);
            break;
            case 'add_file_translation_key_multiple_form':
                checkAdminAjaxModal('manage_content');

                $this->load->model('Ep_Modules_Model', 'modules');
                $this->load->model('Pages_Model', 'pages');

                // Load the list of pages
                $data['pages'] = array_column($this->pages->get_pages(array(
                    'columns' => array('`id_page` as `id`', '`page_name` as `name`')
                )), 'name', 'id');

                // Load modules
                $data['modules'] = $this->modules->get_all_modules();
                $data['action'] = __SITE_URL . 'translations/ajax_operations/translation_key_multiple_add';
                $data['file_names'] = array();

                $files = $this->translations->get_files_translations();
                foreach ($files as $file) {
                    if(in_array($file, array('categories_lang.php', 'industries_lang.php', 'countries_lang.php'))){
                        continue;
                    }
                    $data['file_names'][] = $file;
                }

				$this->view->display('admin/translations/add_file_translation_key_multiple_form_view', $data);
			break;
			case 'edit_file_translation_key_form':
                checkAdminAjaxModal('manage_content');

                $this->load->model('Ep_Modules_Model', 'modules');
                $this->load->model('Pages_model', 'pages');

                $id_key = (int)$this->uri->segment(4);
				$data['translation_file'] = $this->translations->get_translation_file($id_key);
				if(empty($data['translation_file'])){
					messageInModal('Error: The key does not exist.');
				}

                $pages = $this->pages->get_pages(array(
                    'columns' => array('`id_page` as `id`', '`page_name` as `name`'),
                ));
                $data['selected_pages'] = $selected_pages = array_column($this->translations->get_related_pages($id_key, array(
                    'columns' => array('p.id_page as id', 'p.page_name as name'),
                    'with'    => array('pages' => true),
                    'order'   => array('p.page_name' => 'asc')
                )),'name', 'id');
                foreach ($pages as &$page) {
                    $page['selected'] = isset($selected_pages[$page['id']]);
                }
                $data['pages'] = $pages;

                $data['tags'] = array_column($this->translations->getTags(), 'name', 'id');
                $data['oldTags'] = array_column($this->translations->getSelectedTags($id_key), 'name', 'id_tag');

                $data['modules'] = $this->modules->get_all_modules();

				$this->view->display('admin/translations/edit_file_translation_key_form_view', $data);
			break;
			case 'edit_keys_file_entries_form':
                checkAdminAjaxModal('manage_content');

                $this->load->model('Ep_Modules_Model', 'modules');
                $this->load->model('Pages_model', 'pages');

                $id = (int)$this->uri->segment(4);
                $translation = $this->translations->get_translation_file($id);
				if(empty($translation)){
					messageInModal('Error: The key does not exist.');
                }

                $location = json_decode($translation['translation_file_entries'], true);
                $location['list'] = !empty($location['list']) ? $location['list'] : array();
                $location['updated_at'] = new \DateTime($location['updated_at']);
                $this->view->display('admin/translations/edit_translation_location_form_view', [
                    'action'      => __SITE_URL . "translations/ajax_operations/update_translation_key_locations/{$translation['id_key']}",
                    'translation' => $translation,
                    'locations'   => array_map(function($file) {
                        return ltrim($file, '/');
                    }, $location['list']),
                ]);
            break;
            case 'add_key_i18n':
                checkAdminAjaxModal('manage_content,manage_translations');

                $key_id = (int) $this->uri->segment(4);
                if(
                    empty($key_id) ||
                    empty($key = $this->translations->get_translation_file($key_id))
                ) {
                    messageInModal("Translation key with this ID is not found on this server");
                }
                $translations = json_decode($key['translation_localizations'], true);
                $translations = empty($translations) ? array() : array_filter($translations, function($entry, $lang_code) {
                    return 'en' !== $lang_code && !empty($entry['text']['value']);
                }, ARRAY_FILTER_USE_BOTH);

                $languages = $this->translations->get_languages();
                if($this->session->group_lang_restriction) {
                    $allowed_languages = $this->session->group_lang_restriction_list;
                    $languages = array_filter($languages, function($lang) use($allowed_languages) {
                        return in_array($lang['id_lang'], $allowed_languages);
                    });
                }

                $this->view->display('admin/translations/add_key_i18n_form_view', array(
                    'original'     => !empty($key['translation_text']) ? $key['translation_text'] : '',
                    'usage'        => !empty($key['usage_example']) ? $key['usage_example'] : '',
                    'action'       => __SITE_URL . "translations/ajax_operations/add_key_i18n/{$key_id}",
                    'translations' => array_keys($translations),
                    'languages'    => arrayByKey(array_filter($languages, function($lang) {
                            return 'en' !== $lang['lang_iso2'];
                        }),
                        'id_lang'
                    ),
                ));
            break;
            case 'edit_key_i18n':
                checkAdminAjaxModal('manage_content,manage_translations');

                $key_id = (int) $this->uri->segment(4);
                $lang_code = (string) $this->uri->segment(6);
                if(
                    empty($key_id) ||
                    empty($key = $this->translations->get_translation_file($key_id))
                ) {
                    messageInModal("Translation key with this ID is not found on this server");
                }

                $translations = json_decode($key['translation_localizations'], true);
                $translations = empty($translations) ? array() : array_filter($translations, function($entry, $lang_code) {
                    return 'en' !== $lang_code && !empty($entry['text']['value']);
                }, ARRAY_FILTER_USE_BOTH);

                if(
                    empty($lang_code) ||
                    empty($language = $this->translations->get_language_by_iso2($lang_code))
                ) {
                    messageInModal("Language with this code is not found on this server");
                }
                if(!isset($translations[$lang_code])) {
                    messageInModal("Tranlslation for this language is not found on this server");
                }
                if($this->session->group_lang_restriction && !in_array($language['id_lang'], $this->session->group_lang_restriction_list)) {
                    messageInModal(translate("systmess_error_rights_perform_this_action"));
                }

                $this->view->display('admin/translations/edit_key_i18n_form_view', array(
                    'language'     => $language,
                    'original'     => !empty($key['translation_text']) ? $key['translation_text'] : '',
                    'usage'        => !empty($key['usage_example']) ? $key['usage_example'] : '',
                    'translated'   => !empty($translations[$lang_code]['text']['value']) ? $translations[$lang_code]['text']['value'] : '',
                    'action'       => __SITE_URL . "translations/ajax_operations/edit_key_i18n/{$key_id}",
                    'translations' => array_keys($translations),
                ));
            break;
		}
    }

    public function languages()
    {
		if (!logged_in()) {
			headerRedirect(__SITE_URL . 'login');
        }

        checkAdmin('super_admin');

        $this->view->assign('title', 'Translations - Languages');
		$this->view->display('admin/header_view');
		$this->view->display('admin/translations/languages_view');
		$this->view->display('admin/footer_view');
	}

    public function routings()
    {
		if (!logged_in()) {
			headerRedirect(__SITE_URL . 'login');
        }

		checkAdmin('super_admin');

        $this->view->assign('title', 'Translations - Routings');
		$this->view->display('admin/header_view');
		$this->view->display('admin/translations/routings_view');
		$this->view->display('admin/footer_view');
	}

    public function administration()
    {
		if (!logged_in()) {
			headerRedirect(__SITE_URL . 'login');
        }

        checkAdmin('manage_content,manage_translations');

        $this->load->model('Ep_Modules_Model', 'modules');
        $this->load->model('Pages_Model', 'pages');

        // Load the list of pages
        $data['pages'] = $this->pages->get_pages(array('order' => array('page_name' => 'asc')));
        $data['tags'] = $this->translations->getTags(null, 'name ASC');
        $data['pages_url'] = __SITE_URL . 'pages/ajax_operations/load_module_pages';
        // Load list of modules
        $data['modules'] = $this->modules->get_all_modules();
        $data['languages'] = $this->translations->get_languages();
        if($this->session->group_lang_restriction) {
            $allowed_languages = $this->session->group_lang_restriction_list;
            $data['languages'] = array_filter($data['languages'], function($lang) use($allowed_languages) {
                return in_array($lang['id_lang'], $allowed_languages);
            });
        }
        // Load the list of files
        $files = $this->translations->get_files_translations();
        $data['file_names'] = array();
        foreach ($files as $file) {
            if(in_array($file, array('categories_lang.php','industries_lang.php', 'countries_lang.php'))){
                continue;
            }
            $data['file_names'][] = $file;
        }
        $this->view->assign('title', 'Translations');
		$this->view->assign($data);
		$this->view->display('admin/header_view');
		$this->view->display('admin/translations/index_view');
		$this->view->display('admin/footer_view');
	}

    public function ajax_upload_industry_translation()
    {
		if(!logged_in()){
			jsonResponse(translate("systmess_error_should_be_logged"));
		}

		checkAdminAjax('manage_content');

		if (empty($_FILES['translations_file'])) {
			jsonResponse('Error: Please select file to upload.');
		}

        if (empty($_POST['lang'])) {
            jsonResponse('Error: lang is not setted');
        }

        $langinfo = $this->translations->get_language_by_iso2($_POST['lang']);
        if(empty($langinfo)) {
            jsonResponse('Error: could not find the lang');
        }

		global $tmvc;
		$upload_folder = $this->uri->segment(3);
		if(!($upload_folder = checkEncriptedFolder($upload_folder))){
			jsonResponse('Error: File upload path is not correct.');
		}

		$id_user = privileged_user_id();
		$path = $tmvc->my_config['default_translations_upload_folder'];
		create_dir($path);

		$params = array(
			'data_files' => $_FILES['translations_file'],
			'path' => $path,
			'rules' => array(
				'size' => $tmvc->my_config['fileupload_max_file_size'],
				'format' => 'xls,xlsx'
			)
		);

		$res = $this->upload->upload_files_new($params);

		if (count($res['errors'])) {
			$result['result'] = implode(', ', $res['errors']);
			$result['resultcode'] = 'failed';
			jsonResponse($res['errors']);
		} else {
            $filepath = $path . '/' . $res[0]['new_name'];
            $filename = $res[0]['new_name'];
            $filetype = $res[0]['type'];

			$this->load->library('excel', 'excel');
			$this->excel->set_file($filepath);
			$records = $this->excel->extract_content_all();
			if(empty($records)){
				jsonResponse('Warning: The file is empty.', 'warning');
			}

            $records = reset($records); //get first sheet
            $this->load->model('Category_Model', 'category');

            //check ids
            foreach($records as $record) {
                $id = $record['A'];
                $category = $this->category->get_category($id);
                if(empty($category)) {
                    jsonResponse('The category id ' .  $id . 'could not be found');
                };

                if($category['parent'] != 0) {
                    jsonResponse('The category id ' . $id . ' is not the id of and industry');
                }
            }

            $lang_categories = array();
			foreach ($records as $record) {
                $id = $record['A'];
                $english = $record['B'];
                $translation = $record['C'];

                if($id === null || $english == null || $translation == null) {
                    continue;
                }

                $category = $this->category->get_category($id);
                $translations_data = $category['translations_data'];
                $translations_data = json_decode($translations_data, true);
                $translations_data[$langinfo['lang_iso2']] = array(
                    'abbr_iso2' => $langinfo['lang_iso2'],
                    'lang_name' => $langinfo['lang_name']
                );
                $translations_data = json_encode($translations_data);

                $this->category->update_category(array('category_id' => $id, 'translations_data' => $translations_data));
                $category_i18n = $this->category->get_category_i18n(array('id_category' => $id, 'lang_category' => $langinfo['lang_iso2']));
                $category_i18n_data = array(
                    'name' => $translation,
                    'category_lang' => $langinfo['lang_iso2'],
                    'category_id' => $id
                );


                if(empty($category_i18n)) {
                    $this->category->set_category_i18n($category_i18n_data);
                } else {
                    $this->category->update_category_i18n($category_i18n['category_id_i18n'], $category_i18n_data);
                }
                $lang_categories['category_'.$id] = addslashes($translation);
			}

            $rez = $this->translations->save_lang_file($langinfo['lang_iso2'], 'industries_lang.php', $lang_categories);

			jsonResponse('Language file has been created/updated.', 'success');
		}
    }

    public function ajax_upload_country_translation()
    {
		if(!logged_in()){
			jsonResponse(translate("systmess_error_should_be_logged"));
		}

        checkAdminAjax('manage_content');

		if (empty($_FILES['translations_file'])) {
			jsonResponse('Error: Please select file to upload.');
		}

        if (empty($_POST['lang'])) {
            jsonResponse('Error: lang is not setted');
        }

        $langinfo = $this->translations->get_language_by_iso2($_POST['lang']);

        if(empty($langinfo)) {
            jsonResponse('Error: could not find the lang');
        }

		global $tmvc;
		$upload_folder = $this->uri->segment(3);
		if(!($upload_folder = checkEncriptedFolder($upload_folder))){
			jsonResponse('Error: File upload path is not correct.');
		}

		$id_user = privileged_user_id();
		$path = $tmvc->my_config['default_translations_upload_folder'];
		create_dir($path);

		$params = array(
			'data_files' => $_FILES['translations_file'],
			'path' => $path,
			'rules' => array(
				'size' => $tmvc->my_config['fileupload_max_file_size'],
				'format' => 'xls,xlsx'
			)
		);

		$res = $this->upload->upload_files_new($params);

		if (count($res['errors'])) {
			$result['result'] = implode(', ', $res['errors']);
			$result['resultcode'] = 'failed';
			jsonResponse($res['errors']);
		} else {
            $filepath = $path . '/' . $res[0]['new_name'];
            $filename = $res[0]['new_name'];
            $filetype = $res[0]['type'];

			$this->load->library('excel', 'excel');
			$this->excel->set_file($filepath);
			$records = $this->excel->extract_content_all();
			if(empty($records)){
				jsonResponse('Warning: The file is empty.', 'warning');
			}

            $records = reset($records); //get first sheet
            $this->load->model('Country_Model', 'country');

            //check ids
            foreach($records as $record) {
                $id = $record['A'];
                if(empty($this->country->get_country($id))) {
                    jsonResponse('The country id ' .  $id . 'could not be found');
                };
            }

            $lang_countries = array();
			foreach ($records as $record) {
                $id = $record['A'];
                $english = $record['B'];
                $translation = $record['C'];

                $this->country->update_country_translation($id, $langinfo['lang_iso2'], $translation);
                $lang_countries['country_'.$id] = addslashes($translation);
			}

            $rez = $this->translations->save_lang_file($langinfo['lang_iso2'], 'countries_lang.php', $lang_countries);

			jsonResponse('Language file has been created/updated.', 'success');
		}
    }

    public function ajax_upload_file()
    {
		if(!logged_in()){
			jsonResponse(translate("systmess_error_should_be_logged"));
		}

		checkAdminAjax('manage_content');

		if (empty($_FILES['translations_file'])) {
			jsonResponse('Error: Please select file to upload.');
		}

		global $tmvc;
		$upload_folder = $this->uri->segment(3);
		if(!($upload_folder = checkEncriptedFolder($upload_folder))){
			jsonResponse('Error: File upload path is not correct.');
		}

		$id_user = privileged_user_id();
		$path = $tmvc->my_config['default_translations_upload_folder'];
		create_dir($path);

		$params = array(
			'data_files' => $_FILES['translations_file'],
			'path'       => $path,
			'rules'      => array(
				'size'   => $tmvc->my_config['fileupload_max_file_size'],
				'format' => 'xls,xlsx'
			)
		);

        $res = $this->upload->upload_files_new($params);
		if (count($res['errors'])) {
			$result['result'] = implode(', ', $res['errors']);
            $result['resultcode'] = 'failed';

			jsonResponse($res['errors']);
        }

        foreach($res as $item){
            $result['file'] = array('path'=> $path . '/' . $item['new_name'],'name' => $item['new_name'],'type' => $item['type']);
        }

        $file_name = $result['file']['name'];
        $this->load->library('excel', 'excel');
        $this->excel->set_file($result['file']['path']);
        $languages = arrayByKey($this->translations->get_languages(), 'lang_iso2');
        $records = $this->excel->extract_content_all();
        if(empty($records)){
            jsonResponse('Warning: The file is empty.', 'warning');
        }

        foreach ($records as $file_key => $translations) {
            $lang_key = '';
            $file_data = array();
            foreach ($translations as $tkey => $tdata) {
                if($tkey == 1){
                    $lang_key = strtolower($tdata['C']);

                    continue;
                }

                $translation_key = !empty($tdata['A']) ? $tdata['A'] : null;
                $translation_text = !empty($tdata['C']) ? $tdata['C'] : null;
                if(null === $translation_key) {
                    continue;
                }

                if('en' === $lang_key) {
                    $translation = $this->translations->get_translation_file_key($translation_key);
                    if(!empty($translation)){
                        $translation_update = array(
                            "translation_text" => trim($translation_text),
                            "lang_{$lang_key}" => trim($translation_text),
                        );
                        if($translation['translation_text'] !== $translation_update['translation_text']) {
                            $now = new DateTime();
                            $translation_update['translation_text_updated_at'] = $now->format('Y-m-d H:i:s');
                        }
                        $this->translations->update_translation_key_alias($translation_key, $translation_update);
                    } else {
                        $this->translations->insert_translation_key(array(
                            "file_name"                 => $file_key,
                            "translation_key"           => $translation_key,
                            "translation_text"          => trim($translation_text),
                            "lang_{$lang_key}"          => trim($translation_text),
                            "translation_localizations" => json_encode(new stdClass())
                        ));
                    }

                    continue;
                }


                $now = new \DateTime();
                $created_at = $updated_at = $now->format('Y-m-d H:i:s');
                $localization = array(
                    'text' => array(
                        'value'      => $translation_text,
                        'updated_at' => $updated_at,
                    )
                );
                if(isset($languages[$lang_key])) {
                    $localization['lang'] = array(
                        'id'        => $languages[$lang_key]['id_lang'],
                        'abbr_iso2' => $languages[$lang_key]['lang_iso2'],
                        'lang_name' => $languages[$lang_key]['lang_name'],
                    );
                }

                if(!$this->translations->exist_translation_file_key($translation_key)){
                    // $translation_text_original = !empty($tdata['B']) ? $tdata['B'] : null;
                    // if(null !== $translation_text_original) {
                    //     $this->translations->insert_translation_key(array(
                    //         "translation_key"  => $translation_key,
                    //         "translation_text" => $translation_text_original,
                    //         "lang_{$lang_key}" => $translation_text_original,
                    //         "file_name"       => $file_name,
                    //     ));
                    // } else {
                    //     $this->translations->insert_translation_key(array(
                    //         "translation_key"  => $translation_key,
                    //         "file_name"       => $file_name,
                    //     ));
                    // }
                    // $localization['text']['created_at'] = $created_at;

                    continue;
                }

                $this->translations->update_translation_file_lang_entry_by_key(
                    $translation_key,
                    $translation_text,
                    $lang_key,
                    $localization
                );
            }
        }

        jsonResponse('Language file has been created/updated.', 'success');
	}

    public function industries()
    {
        if (!logged_in()) {
			headerRedirect(__SITE_URL . 'login');
        }

		checkAdmin('manage_content');

		$this->load->model('Category_Model', 'category');
		$categories = $this->category->getCategories(array('columns' => 'category_id, parent, name, cat_type', 'order_by' => ' category_id ASC', 'industries_only' => true));
		$lang_categories = array();
		foreach ($categories as $category) {
			$lang_categories['category_'.$category['category_id']] = addslashes($category['name']);
		}
		$this->translations->save_lang_file('en', 'industries_lang.php', $lang_categories);
	}

    public function categories()
    {
        if (!logged_in()) {
			headerRedirect(__SITE_URL . 'login');
        }

		checkAdmin('manage_content');

		$this->load->model('Category_Model', 'category');
		$categories = $this->category->getCategories(array('columns' => 'category_id, parent, name, cat_type', 'order_by' => ' category_id ASC', 'categories_only' => true));
		$lang_categories = array();
		foreach ($categories as $category) {
			$lang_categories['category_'.$category['category_id']] = addslashes($category['name']);
		}
		$this->translations->save_lang_file('en', 'categories_lang.php', $lang_categories);
	}

    public function create_xls_single()
    {
        if (!logged_in()) {
			headerRedirect(__SITE_URL . 'login');
        }

		checkAdmin('manage_content,manage_translations');

        $tlang = $_GET['translate_to'];
        $records_type = $_GET['records_type'];
        $lang_file = $_GET['lang_file_name'];
        $lang_file_name = pathinfo($lang_file, PATHINFO_FILENAME);
        $id_page = (int) $_GET['page'];

        $xls_file_name_params = array();
        if (!empty($lang_file)) {
            $xls_sheet_name_params[] = $lang_file;
        }

        if (!empty($id_page)) {
            $page_details = model('pages')->find_page($id_page);

            if (empty($page_details)) {
                unset($id_page);
            } else {
                $xls_sheet_name_params[] = strForURL($page_details['page_name']);
            }
        }

        $xls_file_name = empty($xls_sheet_name_params) ? 'all_files_and_pages' : implode('_',  $xls_sheet_name_params);
		$excel = new Spreadsheet();
		$excel->setActiveSheetIndex(0);
		$excel->getActiveSheet()->setTitle('EP Translation Keys');
        // $excel->getActiveSheet()->getColumnDimension('A')->setWidth(50);
        $excel->getActiveSheet()
            ->setCellValue('A1', 'TAlias')
            ->getStyle('A1')
                ->getFont()
                    ->setSize(12)
                    ->setBold(true);

        $excel->getActiveSheet()->getColumnDimension('B')->setWidth(50);
		$excel->getActiveSheet()
            ->setCellValue('B1', 'EN')
            ->getStyle('B1')
                ->getFont()
                    ->setSize(12)
                    ->setBold(true);

        $excel->getActiveSheet()->getColumnDimension('C')->setWidth(50);
		$excel->getActiveSheet()
            ->setCellValue('C1', strtoupper($tlang))
            ->getStyle('C1')
                ->getFont()
                    ->setSize(12)
                    ->setBold(true);

		$params = array(
			'translation_file' => $lang_file
		);

		switch ($records_type) {
			case 'translated':
				$params['with_lang'] = $tlang;
			break;
			case 'not_translated':
                $params['without_lang'] = $tlang;
			break;
        }

        if (!empty($id_page)) {
            $params['page'] = $id_page;
        }

        switch ($lang_file_name) {
            case 'industries_lang':
                $this->load->model('Category_Model', 'category');
                $industries = $this->category->getCategories(array('columns' => 'category_id, parent, name, cat_type', 'order_by' => ' category_id ASC', 'industries_only' => true));
                $row_index = 2;
                foreach ($industries as $industry) {
                    $excel->getActiveSheet()
                        ->setCellValueExplicit("A{$row_index}", "industry_{$industry['category_id']}", DataType::TYPE_STRING)
                        ->setCellValueExplicit("B{$row_index}", $industry['name'], DataType::TYPE_STRING)
                        ->setCellValueExplicit("C{$row_index}", '', DataType::TYPE_STRING);
                    $row_index++;
                }
                break;
            case 'countries_lang':
                //todo
                break;
            default:
                $excel->getActiveSheet()->getColumnDimension('D')->setWidth(50);
                $excel->getActiveSheet()
                    ->setCellValue('D1', 'Usage example')
                    ->getStyle('D1')
                        ->getFont()
                        ->setSize(12)
                        ->setBold(true);

                $translation_records = $this->translations->get_translation_files($params);
                $row_index = 2;
                foreach ($translation_records as $translation_record) {
                    $translated_text = record_i18n($translation_record['translation_localizations'], 'text', $tlang);
                    $translated_text = !empty($translated_text['value']) ? $translated_text['value'] : '';

                    $excel->getActiveSheet()
                        ->setCellValueExplicit("A{$row_index}", $translation_record['translation_key'], DataType::TYPE_STRING)
                        ->setCellValueExplicit("B{$row_index}", trim($translation_record['translation_text']), DataType::TYPE_STRING)
                        ->setCellValueExplicit("C{$row_index}", trim($translated_text), DataType::TYPE_STRING)
                        ->setCellValueExplicit("D{$row_index}", $translation_record["usage_example"], DataType::TYPE_STRING);
                    $row_index++;
                }

                $excel->getActiveSheet()->getStyle('B')->getAlignment()->setWrapText(true);
                $excel->getActiveSheet()->getStyle('C')->getAlignment()->setWrapText(true);
                $excel->getActiveSheet()->getStyle('D')->getAlignment()->setWrapText(true);
                break;
        }

		$filename = $xls_file_name . "_" .date('d-m-Y_H:i:s') . "_{$records_type}_lang_{$tlang}.xlsx";
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="'.$filename.'"');
		header('Cache-Control: max-age=0');
		$objWriter = IOFactory::createWriter($excel, 'Xlsx');
		$objWriter->save('php://output');
    }

    public function morph()
    {
        if (!logged_in() || !have_right('admin_site')) {
			headerRedirect(__SITE_URL . '404');
        }

        $languages = $this->translations->get_languages();
        $morph_query = $this->translations->morph_translation_data_format($languages);
    }

    public function system_messages()
    {
        checkIsLogged();
        checkPermision('manage_content,manage_translations');

        /** @var Ep_Modules_Model $epModulesModel */
        $epModulesModel = model(Ep_Modules_Model::class);

        /** @var Pages_Model $pagesModel */
        $pagesModel = model(Pages_Model::class);

        /** @var Translations_Model $translationModel*/
        $translationModel = model(Translations_Model::class);

        $languages = $translationModel->get_languages();
        if (session()->group_lang_restriction) {
            $allowedLanguages = session()->group_lang_restriction_list;
            $languages = array_filter($languages, fn ($lang) => in_array($lang['id_lang'], $allowedLanguages));
        }

        $fileNames = [];
        $files = $translationModel->get_files_translations();
        foreach ($files as $file) {
            if (in_array($file, ['categories_lang.php','industries_lang.php', 'countries_lang.php'])) {
                continue;
            }

            $fileNames[] = $file;
        }

        views(['admin/header_view', 'admin/translations/system_messages_view', 'admin/footer_view'], [
            'file_names'    => $fileNames,
            'pages_url'     => __SITE_URL . 'pages/ajax_operations/load_module_pages',
            'languages'     => $languages,
            'modules'       => $epModulesModel->get_all_modules(),
            'title'         => 'System Messages',
            'pages'         => $pagesModel->get_pages(['order' => ['page_name' => 'asc']]),
            'tags'          => $translationModel->getTags(null, 'name ASC')
        ]);
    }

    public function export_syst_mess()
    {
        /** @var Translations_Model $translationModel*/
        $translationModel = model(Translations_Model::class);

        $data = $translationModel->get_translation_files(['is_systmess'   => 1]);
        $now = date('Y-m-d-H_i');
        $this->returnReport($data, "syst_mess_{$now}.xlsx");
    }

    /**
     * Get report
     *
     * @param array $data - log data
     * @param string $fileName - name of the file with extension
     *
     */
    private function returnReport($data, $fileName = 'systmess.xlsx')
    {
        $excel = new Spreadsheet();
        $excel->setActiveSheetIndex(0);
        $activeSheet = $excel->getActiveSheet();
        $activeSheet->setTitle('User Activity');

        $headerColumns = [
            'A' => ['name' => 'Translation Key', 'width' => 40],
            'B' => ['name' => 'Text',            'width' => 70],
            'C' => ['name' => 'Usage Example',   'width' => 90],
            'D' => ['name' => 'Updated on',      'width' => 30],
            'E' => ['name' => 'Proofread',       'width' => 20]
        ];

		//region generate headings
		$rowIndex = 1;

        foreach($headerColumns as $letter => $heading)
        {
            $activeSheet->getColumnDimension($letter)->setWidth($heading['width']);
            $activeSheet->getStyle($letter . $rowIndex)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $activeSheet->getStyle($letter . $rowIndex)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $activeSheet->setCellValue($letter . $rowIndex, $heading['name'])
                        ->getStyle($letter . $rowIndex)
                            ->getFont()
                                ->setSize(14)
                                    ->setBold(true);
        }
        //endregion generate headings

        //region introduce data
        $rowIndex = 2;
        $excel->getDefaultStyle()->getAlignment()->setWrapText(true);
        foreach($data as $one)
        {
            $activeSheet
                ->setCellValue("A$rowIndex", $one['translation_key'])
                ->setCellValue("B$rowIndex", $one['lang_en'])
                ->setCellValue("C$rowIndex", $one['usage_example'])
                ->setCellValue("D$rowIndex", getDateFormat($one['update_date']))
                ->setCellValue("E$rowIndex", $one['is_reviewed'] ? 'Yes' : 'No');

            $rowIndex++;
        }
        //endregion introduce data

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

		$objWriter = IOFactory::createWriter($excel, 'Xlsx');
        $objWriter->save('php://output');
    }

    private function save_lang_js_files($langs = array())
    {
        if (empty($langs)) {
            return false;
        }

        $records = $this->translations->get_translation_files();
        if (empty($records)) {
            return false;
        }

        $data["records"] = arrayByKey($records, "translation_key");

        $files_path = array(
            'general' => 'public/plug/general/lang',
            'fancybox' => 'public/plug/jquery-fancybox-2-1-7/lang',
            'fancybox3' => 'public/plug/fancybox-3-5-7/lang',
            'validation_engine' => 'public/plug/jquery-validation-engine-2-6-2/lang',
            'bootstrap_dialog' => 'public/plug/bootstrap-dialog-1-35-4/lang',
            'textcounter' => 'public/plug/textcounter-0-3-6/lang',
            'jquery_validation' => 'public/plug/jquery-validation/lang',
        );

        foreach ($langs as $lang) {
            $data["lang"] = $lang;

            foreach ($files_path as $files_path_key => $files_path_item) {
                create_dir($files_path_item);

                $data["key"] = $files_path_key;
                $content = $this->view->fetch("admin/translations/javascript/{$files_path_key}", $data);

                $save_file = $files_path_item . '/' . $lang . '.js';
                $f = fopen($save_file, "w");
                fwrite($f, $content);
                fclose($f);
            }

        }

        return true;
    }

    private function save_lang_js_file($langs = array(), $single_file = true)
    {
        if (empty($langs)) {
            return false;
        }

        $records = $this->translations->get_translation_files();
        if (empty($records)) {
            return false;
        }

        $translate_js = array();
        foreach ($records as $record) {
            $translate_js['lang_new.js'][$record["translation_key"]] = $record;
        }

        if ($single_file) {
            $file_path = $this->translations->lang_folder_js;
            create_dir($file_path);
            foreach ($translate_js as $file => $js) {
                $data["records"] = $js;
                $data["langs"] = $langs;

                $file_name = pathinfo($file, PATHINFO_FILENAME);
                $content = $this->view->fetch("admin/translations/javascript/{$file_name}", $data);

                $save_file = $file_path . '/' . $file;
                $f = fopen($save_file, "w");
                fwrite($f, $content);
                fclose($f);
            }
        }

        return true;
    }

    private function write_translation_log($log_data, $return_error_msg = true)
    {
        if (empty($log_data)) {
            return false;
        }

        $result = $this->translations->log($log_data);

        if ($return_error_msg && !$result) {
            jsonResponse('Occurred server error on process of adding translation log');
            return false;
        }

        return $result;
    }
}
