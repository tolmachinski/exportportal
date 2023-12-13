<?php
/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [15.12.2021]
 * Controller Refactoring
 */
class Cr_domains_Controller extends TinyMVC_Controller {

	// DONE
	private function _load_main() {
		$this->load->model('Cr_domains_Model', 'cr_domains');
	}

	// DONE
	function administration() {
		checkAdmin('manage_cr_domain');

		$this->_load_main();

		$data['title'] = 'Countries representatives';

		$this->view->assign($data);
		$this->view->display('admin/header_view');
		$this->view->display('admin/cr/domains/index_view');
		$this->view->display('admin/footer_view');
	}

	// DONE
	function ajax_operations(){
		if (!isAjaxRequest())
			headerRedirect();

		$this->_load_main();

		$option = $this->uri->segment(3);

		switch ($option) {
			// DONE
			case 'add_cr_domain':
				checkAdminAjax('manage_cr_domain');

				$validator_rules = array(
					array(
						'field' => 'country',
						'label' => 'Country',
						'rules' => array('required' => '')
					),
                    array(
						'field' => 'short_description',
						'label' => 'Short description',
						'rules' => array('required' => '', 'max_len[500]' => '')
					),
                    array(
                        'field' => 'domain_photo',
                        'label' => 'Header image',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'video',
                        'label' => 'Video link',
                        'rules' => array('valid_url' => '')
                    )
				);

				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate()){
					jsonResponse($this->validator->get_array_errors());
				}

				$this->load->model('Country_Model', 'country');
				$id_country = (int) $_POST['country'];
				$country = $this->country->get_country($id_country);
				if(empty($country)){
					jsonResponse('Error: The country does not exist.');
				}

				$cr_domain = $this->cr_domains->get_cr_domain(array('id_country' => $id_country));
				if(!empty($cr_domain)){
					jsonResponse('Error: The domain for this country already exists.');
				}

				$domain_photo = '';
				if(!empty($_POST['domain_photo'])){
					$domain_photo = $_POST['domain_photo'];
				}

				$insert = array(
					'id_country' => $id_country,
					'domain_photo' => $domain_photo,
					'short_description' => cleanInput($_POST['short_description'])
				);

                if (!empty($_POST['video'])) {
                    $this->load->library('videothumb');
                    $video_link = $this->videothumb->getVID($_POST['video']);
                    $video_link['link'] = $_POST['video'];
                    $insert['video_data'] = json_encode($video_link);
                }

				$this->cr_domains->set_cr_domain($insert);

				jsonResponse('Success: The country domain has been added.', 'success');
			break;
			// DONE
			case 'edit_cr_domain':
				checkAdminAjax('manage_cr_domain');

				$validator_rules = array(
					array(
						'field' => 'id_domain',
						'label' => 'Country domain',
						'rules' => array('required' => '')
					),
                    array(
                        'field' => 'short_description',
                        'label' => 'Short description',
                        'rules' => array('required' => '', 'max_len[500]' => '')
                    ),
                    array(
                        'field' => 'domain_photo',
                        'label' => 'Header image',
                        'rules' => array('required' => '')
                    ),
                    array(
						'field' => 'video',
						'label' => 'Video link',
						'rules' => array('valid_url' => '')
					)
				);

				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate()){
					jsonResponse($this->validator->get_array_errors());
				}

				$id_domain = (int) $_POST['id_domain'];
				$cr_domain = $this->cr_domains->get_cr_domain(array('id_domain' => $id_domain));
				if (empty($cr_domain)){
					jsonResponse('Error: The domain does not exists.');
				}

				$update = array(
                    'short_description' => cleanInput($_POST['short_description'])
                );

				if (!empty($_POST['domain_photo'])){
					$update['domain_photo'] = $_POST['domain_photo'];
				}

                if (!empty($_POST['video'])) {
                    $this->load->library('videothumb');
                    $video_link = $this->videothumb->getVID($_POST['video']);
                    $video_link['link'] = $_POST['video'];
                    $update['video_data'] = json_encode($video_link);
                }

				if (!empty($update)){
					$this->cr_domains->update_cr_domain($id_domain, $update);
				}

				jsonResponse('Success: The country domain has been updated.', 'success');
			break;
			// DONE
			case 'delete_cr_domain':
				checkAdminAjax('manage_cr_domain');

				$validator_rules = array(
					array(
						'field' => 'id_domain',
						'label' => 'Country domain',
						'rules' => array('required' => '')
					)
				);

				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate()){
					jsonResponse($this->validator->get_array_errors());
				}

				$id_domain = (int) $_POST['id_domain'];
				$cr_domain = $this->cr_domains->get_cr_domain(array('id_domain' => $id_domain));
				if(empty($cr_domain)){
					jsonResponse('Error: The domain does not exists.');
				}

				if(!empty($cr_domain['domain_photo'])){
					@unlink('public/img/country_representative/'.$cr_domain['domain_photo']);
				}

				$this->cr_domains->delete_cr_domain($id_domain);
				jsonResponse('Success: The country domain has been deleted.', 'success');
			break;
			// DONE
			case 'cr_list_dt':
				checkAdminAjaxDT('manage_cr_domain');

                if (isset($_POST['iDisplayStart']) && ($_POST['iDisplayLength'] != -1)) {
					$from = intval(cleanInput($_POST['iDisplayStart']));
					$till = intval(cleanInput($_POST['iDisplayLength']));
					$conditions['limit'] = ['as' => 'pc.country', 'key' => $from . ',' . $till, 'type' => 'cleanInput'];
				}

                $conditions = array_merge(
                    [
                        'sort_by' => flat_dt_ordering($_POST, [
                            'dt_id' => 'crd.id_domain',
                            'dt_country' => 'pc.country'
                        ])
                    ],
                    dtConditions($_POST, [ $conditions['limit'] ])
                );

				$records = $this->cr_domains->get_cr_domains($conditions);
				$records_total = $this->cr_domains->get_cr_domains_count($conditions);

				$output = array(
					"sEcho" => intval($_POST['sEcho']),
					"iTotalRecords" => $records_total,
					"iTotalDisplayRecords" => $records_total,
					'aaData' => array()
				);

				if(empty($records)){
					jsonResponse('', 'success', $output);
				}

				foreach ($records as $record) {
					$output['aaData'][] = array(
						'dt_id' 		=> $record['id_domain'],
						'dt_country' 	=> $record['country'],
						'dt_flag' 		=> '<img width="24" height="24" src="' . getCountryFlag($record['country']) . '" title="' . $record['country'] . '" alt="' . $record['country'] . '"/>',
						'dt_domain' 	=> $record['country_alias'],
						'dt_date' 		=> formatDate($record['domain_date_created']),
						'dt_actions' 	=> '<a class="ep-icon ep-icon_link" href="'.getSubDomainURL($record['country_alias']).'" target="_blank" data-title="Visit country representative"></a>
											<a class="ep-icon ep-icon_pencil fancyboxValidateModalDT fancybox.ajax" href="'.__SITE_URL.'cr_domains/popup_forms/edit_cr_domain/' . $record['id_domain'] . '" data-table="dtCr" data-title="Edit country representative domain" title="Edit country representative domain"></a>
											<a class="ep-icon ep-icon_remove txt-red confirm-dialog" data-callback="delete_cr_domain" data-domain="'.$record['id_domain'].'" data-message="Are you sure you want to delete this country representative domain?" href="#" title="Delete country representative domain"></a>',
					);
				}

				jsonResponse('', 'success', $output);
			break;
			// DONE
			case 'upload_cr_image':
				checkAdminAjax('manage_cr_domain');

				if (empty($_FILES['files'])) {
					jsonResponse('Error: Please select file to upload.');
				}

				$path = $this->cr_domains->path_folder;
				create_dir($path);

				global $tmvc;
				$id_domain = (int) $this->uri->segment(4);
				if($id_domain){
					$cr_domain = $this->cr_domains->get_cr_domain(array('id_domain' => $id_domain));
					if (empty($cr_domain)) {
						jsonResponse('Error: Country domain does not exist.');
					}

					if(!empty($cr_domain['domain_photo'])){
						jsonResponse('Error: Please delete current photo before upload the new one.');
					}
				}

				$conditions = array(
					'files' => $_FILES['files'],
					'destination' => $path,
					'rules' => array(
						'size' => $tmvc->my_config['fileupload_max_file_size']
					)
				);
				$upload_data = $this->upload->upload_images_new($conditions);

				if (!empty($upload_data['errors'])) {
					jsonResponse($upload_data['errors']);
				}else{
					$result = array(
						'file' => array(
							'path' => $path,
							'name' => $upload_data[0]['new_name']
						)
					);

					jsonResponse('', 'success', $result);
				}
			break;
			// DONE
			case 'delete_cr_image':
				checkAdminAjax('manage_cr_domain');

				$id_domain = (int) $this->uri->segment(4);
				if($id_domain){
					$cr_domain = $this->cr_domains->get_cr_domain($id_domain);
					if (empty($cr_domain)) {
						jsonResponse('Error: Country domain does not exist.');
					}

					if(empty($cr_domain['domain_photo'])){
						jsonResponse('Error: Country domain photo is empty.');
					}

					$file_name = $cr_domain['domain_photo'];
					$update = array(
						'domain_photo' => ''
					);
					$this->cr_domains->update_cr_domain($id_domain, $update);
				} else{
					$validator_rules = array(
						array(
							'field' => 'file',
							'label' => 'File',
							'rules' => array('required' => '')
						)
					);

					$this->validator->set_rules($validator_rules);
					if (!$this->validator->validate()){
						jsonResponse($this->validator->get_array_errors());
					}

					$file_name = $_POST['file'];
				}

				@unlink('public/img/country_representative/'.$file_name);
				jsonResponse('Success: The photo has been deleted.', 'success');
			break;
			// DONE
            case 'update_cr_configs':
				checkAdminAjax('manage_cr_domain');

                $domains_names = array();
				$domains = $this->cr_domains->get_cr_domains();
				if(!empty($domains)){
					foreach($domains as $domain){
						$domains_names[] = $domain['country_alias'];
					}
				}

				$file_path = TMVC_MYAPPDIR . "configs". DS ."cr_domains_config.php";
				$f = fopen($file_path, "w");
				fwrite($f, '<?php return  ' . var_export($domains_names, true) . ';');
				fclose($f);

                jsonResponse("Configs are updated successfully", "success");
            break;
		}
	}

	// DONE
	function popup_forms() {
		if (!isAjaxRequest()){
			headerRedirect();
		}

		$this->_load_main();
		$op = $this->uri->segment(3);
		switch ($op) {
			// DONE
			case 'add_cr_domain':
				checkAdminAjaxModal('manage_cr_domain');

				global $tmvc;
				$this->load->model('Country_Model', 'country');
				$data['countries'] = $this->country->get_countries();
				$data['fileupload_max_file_size'] = $tmvc->my_config['fileupload_max_file_size'];
				$this->view->assign($data);
				$this->view->display('admin/cr/domains/cr_domain_form_view');
			break;
			// DONE
			case 'edit_cr_domain':
				checkAdminAjaxModal('manage_cr_domain');

				$id_domain = intval($this->uri->segment(4));
				$data['cr_domain'] = $this->cr_domains->get_cr_domain(array('id_domain' => $id_domain));
				if (empty($data['cr_domain'])) {
					messageInModal('Error: Country domain does not exist.');
				}

				global $tmvc;
				$data['fileupload_max_file_size'] = $tmvc->my_config['fileupload_max_file_size'];
				if (!empty($data['cr_domain']['video_data'])) {
				    $video_data = json_decode($data['cr_domain']['video_data'], true);
				    $data['video_link'] = $video_data['link'];
                }
				$this->view->assign($data);
				$this->view->display('admin/cr/domains/cr_domain_form_view');
			break;
		}
	}
}
