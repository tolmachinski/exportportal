<?php

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [15.12.2021]
 * Controller Refactoring
 */
class Cr_User_Controller extends TinyMVC_Controller {
	private $breadcrumbs = array();

	private function _load_main(){
		$this->load->model('User_Model', 'user');
        $this->load->model('Cr_users_Model', 'cr_users');
        $this->load->model('Cr_domains_Model', 'cr_domains');
	}

	function index() {
		$this->_load_main();

		$id_user = id_from_link($this->uri->segment(3));
		$data['user_main'] = $this->user->getUser($id_user);
		if(empty($data['user_main'])){
			show_404();
		}

		if($data['user_main']['gr_type'] != 'CR Affiliate'){
			show_404();
		}

		if($data['user_main']['user_page_blocked'] > 0){
			show_blocked();
		}

		$this->load->model('User_photo_Model', 'user_photo');
		$this->load->model("Country_model", 'country');
		$this->load->model('UserGroup_Model', 'groups');
		$this->load->model('Cr_Job_History_Model', 'job_history');

		$user_photo = $this->user_photo->get_photos(array('id_user' => $id_user));

		foreach ($user_photo as $key => $photo) {
			$data['user_photo'][$key] = unserialize($photo['thumb_photo']);
			$data['user_photo'][$key]['main'] = $photo['name_photo'];
		}

		$data['user_location'] = $this->country->get_country_city($data['user_main']['country'], $data['user_main']['city'], $data['user_main']['state']);
		$data['user_location'] = array_filter($data['user_location']);

		$data['ugroup_rights'] = $this->groups->getUserRights($data['user_main']['user_group']);
		$data['user_rights_fields'] = $this->groups->get_users_right($id_user);

		$data['meta_params']['[USER_NAME]'] = $data['user_main']['user_name'];
		$domain_users_params = array(
			'status' => 'active',
			'group_type' => "'CR Affiliate'",
			'domains_info' => true,
			'limit' => 10,
			'not_users_list' => $data['user_main']['idu'],
			'order_by' => 'u.last_active DESC'
		);
		if(have_right('cr_international', $data['ugroup_rights'])){
			$data['user_domains'] = $this->cr_domains->get_user_domains_relation($id_user);
			$domains_list = array();
			foreach ($data['user_domains'] as $user_domain) {
				$domains_list[] = $user_domain['id_domain'];
			}
			$domain_users_params['domains'] = implode(',', $domains_list);
			$data['meta_params']['[COUNTRY_REPRESENTATIVE]'] = $data['user_main']['gr_name'];
		} else{
			$data['user_domain'] = $this->cr_domains->get_user_domain_relation($id_user);

			$this->breadcrumbs[] = array(
				'link' => getSubDomainURL($data['user_domain']['country_alias']),
				'title' => $data['user_domain']['country']
			);

			$this->breadcrumbs[] = array(
				'link' => getSubDomainURL($data['user_domain']['country_alias'], "type/{$data['user_main']['gr_alias']}"),
				'title' => $data['user_main']['gr_name']
			);

			$domain_users_params['domains'] = $data['user_domain']['id_domain'];
			$data['meta_params']['[COUNTRY_REPRESENTATIVE]'] = $data['user_domain']['country'].' '.$data['user_main']['gr_name'];
		}

		$data['domain_users'] = $this->cr_users->cr_get_users($domain_users_params);

		$user_image = getDisplayImageLink(array('{ID}' => $data['user_main']['idu'], '{FILE_NAME}' => $data['user_main']['user_photo']), 'users.main', array( 'thumb_size' => 0, 'no_image_group' => $data['user_main']['user_group'] ));
		if (is_file($user_image)) {
			$data['meta_params']['[image]'] = $user_image;
		}

		if (!empty($data['user_main']['description'])) {
			$data['meta_data']['description'] = truncWords($data['user_main']['description'], 20);
		}

		$this->breadcrumbs[] = array(
 			'link' => __SITE_URL.'country_representative/'.strForUrl($data['user_main']['user_name']. ' ' . $data['user_main']['idu']),
			'title' => $data['user_main']['user_name']
		);

		$data['jobs_history'] = $this->job_history->get_jobs_histoy(array('id_user' => $id_user));

		// EVENTS ASSIGNED
		$this->load->model('Cr_events_Model', 'cr_events');
        $data['assigned_events'] = $this->cr_events->get_user_events(array(
            'limit' => 10,
            'order'      => array('event_date_start' => 'ASC'),
            'conditions' => array(
                'assigned_user' => $id_user,
                'active_today'  => true,
                'status'        => 'approved',
                'visible'       => 1,
            ),
        ));
		$data['assigned_events_expired'] = $this->cr_events->get_user_events(array(
            'limit' => 10,
            'order'      => array('event_date_start' => 'ASC'),
            'conditions' => array(
                'assigned_user' => $id_user,
                'expired_today' => true,
                'status'        => 'approved',
                'visible'       => 1,
            ),
        ));

		$data['allow_attend'] = true;
		if(!empty($data['assigned_events'])){
			$data['header_event'] = $data['assigned_events'][0];
			if (logged_in()) {
				$attend_record = $this->cr_events->get_attend_record_by_user(privileged_user_id(), $data['header_event']['id_event']);
				$data['allow_attend'] = empty($attend_record);
			}
		}

		$data['langs_proficiencies'] = $this->cr_users->speak_language_proficiencies;
		$data['user_additional'] = $this->cr_users->cr_get_user_additional($id_user);
		$data['breadcrumbs'] = $this->breadcrumbs;

		$this->view->assign($data);

		$data['sidebar_left_content'] = 'new/cr/user/sidebar_view';
        $data['main_content'] = 'new/cr/user/index_view';
        $this->view->assign($data);
        $this->view->display('new/index_template_view');
	}

	function short_bio(){
		checkPermision('manage_short_bio');
		global $tmvc;

		$this->_load_main();
		$id_user = id_session();

		$data['user'] = $this->user->getUser($id_user);
		$data['user_aditional'] = $this->cr_users->cr_get_user_additional($id_user);
		$data['langs_proficiencies'] = $this->cr_users->speak_language_proficiencies;

		$data['cr_speak_langs_limit'] = $tmvc->my_config['cr_speak_langs_limit'];
		$data['cr_skills_limit'] = $tmvc->my_config['cr_skills_limit'];
		$data['cr_awards_limit'] = $tmvc->my_config['cr_awards_limit'];
		$data['cr_jobs_limit'] = $tmvc->my_config['cr_jobs_limit'];
		$data['cr_education_limit'] = $tmvc->my_config['cr_education_limit'];
		$data['cr_certificates_limit'] = $tmvc->my_config['cr_certificates_limit'];
		$data['cr_contacts_limit'] = $tmvc->my_config['cr_contacts_limit'];

		$this->view->assign('title', 'Short Bio');

		$this->view->assign($data);
        $this->view->display('new/header_view');
        $this->view->display('new/user/cr_user/bio/bio_view');
        $this->view->display('new/footer_view');
	}

    function ajax_operations(){
        if (!isAjaxRequest()){
            headerRedirect();
        }

        $this->_load_main();

        $action = $this->uri->segment(3);
        switch ($action) {
            case 'update_languages':
                checkPermisionAjax('manage_short_bio');
				global $tmvc;

                $id_user = id_session();
				$data['langs_proficiencies'] = $this->cr_users->speak_language_proficiencies;

				$speak_langs = $_POST['speak_lang'];
				$filtered_langs = array();
				foreach ($speak_langs as $key => $speak_lang) {
					$lang_name = cleanInput(trim($speak_lang['name']));
					$lang_proficiency = $speak_lang['proficiency'];

					if(!empty($speak_lang) && !empty($lang_proficiency) && array_key_exists($lang_proficiency, $this->cr_users->speak_language_proficiencies)){
						$filtered_langs[$key] = array(
							'name' => $lang_name,
							'proficiency' => $lang_proficiency
						);
					}

				}

				if(count($filtered_langs) > $tmvc->my_config['cr_speak_langs_limit']){
					jsonResponse('You can not add more than '.$tmvc->my_config['cr_speak_langs_limit'].' speak languages.');
				}

				$this->cr_users->cr_update_user_additional($id_user, array('user_speak_langs' => json_encode($filtered_langs)));
				jsonResponse('Changes has been saved successfully.' , 'success');
            break;
            case 'update_skills':
                checkPermisionAjax('manage_short_bio');
				global $tmvc;

                $id_user = id_session();
				$skills = $_POST['user_skills'];
				$filtered_skills = array();
				foreach ($skills as $skill) {
					$skill = cleanInput(trim($skill));
					if(!empty($skill)){
						$filtered_skills[] = $skill;
					}
				}

				if(count($filtered_skills) > $tmvc->my_config['cr_skills_limit']){
					jsonResponse('You can not add more than '.$tmvc->my_config['cr_skills_limit'].' skills.');
				}

				$this->cr_users->cr_update_user_additional($id_user, array('user_skills' => json_encode($filtered_skills)));
				jsonResponse('Changes has been saved successfully.' , 'success');
            break;
            case 'update_video':
				checkPermisionAjax('manage_short_bio');
				$validator_rules = array(
					array(
						'field' => 'user_video',
						'label' => 'Video',
						'rules' => array('valid_url' => '', 'max_len[200]' => '')
					)
				);

				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate()) {
					jsonResponse($this->validator->get_array_errors());
				}

				$id_user = id_session();
				$user_video = array();
				if(!empty($_POST['user_video'])){
					$this->load->library('videothumb');
					$video_link = $this->videothumb->getVID($_POST['user_video']);
					if($video_link){
						$user_video = array(
							'url' => $_POST['user_video'],
							'type' => $video_link['type'],
							'code' => $video_link['v_id']
						);
					}
				}

				$this->cr_users->cr_update_user_additional($id_user, array('user_video' => json_encode($user_video)));
				jsonResponse('Changes has been saved successfully.' , 'success');
            break;
            case 'add_award':
				checkPermisionAjax('manage_short_bio');

				$validator_rules = array(
                    array(
                        'field' => 'title',
                        'label' => 'Title',
                        'rules' => array('required' => '', 'max_len[250]' => '')
                    ),
                    array(
                        'field' => 'award_date',
                        'label' => 'Date',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'issuer',
                        'label' => 'Issuer',
                        'rules' => array('max_len[250]' => '')
                    ),
                    array(
                        'field' => 'description',
                        'label' => 'Description',
                        'rules' => array('max_len[1000]' => '')
                    )
                );

                $this->validator->set_rules($validator_rules);
                if(!$this->validator->validate()){
                    jsonResponse($this->validator->get_array_errors());
				}

				if(!validateDate($_POST['award_date'], 'm/d/Y')){
					jsonResponse('The Date format is not correct.');
				}

				global $tmvc;

                $id_user = id_session();
				$user_aditional = $this->cr_users->cr_get_user_additional($id_user);
				$id_award = uniqid();
				$callback_award[$id_award] = $new_award = array(
					'id_award' => $id_award,
					'title' => cleanInput($_POST['title']),
					'issuer' => cleanInput($_POST['issuer']),
					'award_date' => formatDate($_POST['award_date']),
					'description' => cleanInput($_POST['description'])
				);
				$callback_award[$id_award]['award_date'] = formatDate($_POST['award_date'],'M Y');
				$user_awards = json_decode($user_aditional['user_awards'], true);
				$user_awards[$id_award] = $new_award;

				if(count($user_awards) > $tmvc->my_config['cr_awards_limit']){
					jsonResponse('You can not add more than '.$tmvc->my_config['cr_awards_limit'].' awards/acknowledgements.');
				}

				$this->cr_users->cr_update_user_additional($id_user, array('user_awards' => json_encode($user_awards)));

				$callback = array('user_awards' => $callback_award);

				jsonResponse('Changes has been saved successfully.' , 'success', $callback);
            break;
            case 'edit_award':
				checkPermisionAjax('manage_short_bio');

				$validator_rules = array(
                    array(
                        'field' => 'id_award',
                        'label' => 'Awards/Acknowledgements info',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'title',
                        'label' => 'Title',
                        'rules' => array('required' => '', 'max_len[250]' => '')
                    ),
                    array(
                        'field' => 'award_date',
                        'label' => 'Date',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'issuer',
                        'label' => 'Issuer',
                        'rules' => array('max_len[250]' => '')
                    ),
                    array(
                        'field' => 'description',
                        'label' => 'Description',
                        'rules' => array('max_len[1000]' => '')
                    )
                );

                $this->validator->set_rules($validator_rules);
                if(!$this->validator->validate()){
                    jsonResponse($this->validator->get_array_errors());
				}

				if(!validateDate($_POST['award_date'], 'm/d/Y')){
					jsonResponse('The Date format is not correct.');
				}

				global $tmvc;

                $id_user = id_session();
				$user_aditional = $this->cr_users->cr_get_user_additional($id_user);
				$user_awards = json_decode($user_aditional['user_awards'], true);
				$id_award = cleanInput($_POST['id_award']);
				if(!array_key_exists($id_award, $user_awards)){
					jsonResponse(translate('systmess_error_invalid_data'));
				}

				$callback_award[$id_award] = $user_awards[$id_award] = array(
					'id_award' => $id_award,
					'title' => cleanInput($_POST['title']),
					'issuer' => cleanInput($_POST['issuer']),
					'award_date' => formatDate($_POST['award_date']),
					'description' => cleanInput($_POST['description'])
				);
				$callback_award[$id_award]['award_date'] = formatDate($_POST['award_date'],'M Y');

				$this->cr_users->cr_update_user_additional($id_user, array('user_awards' => json_encode($user_awards)));

				$callback = array('user_awards' => $callback_award);

				jsonResponse('Changes has been saved successfully.' , 'success', $callback);
            break;
            case 'delete_award':
				checkPermisionAjax('manage_short_bio');

				$validator_rules = array(
                    array(
                        'field' => 'id_award',
                        'label' => 'Awards/Acknowledgements info',
                        'rules' => array('required' => '')
                    )
                );

                $this->validator->set_rules($validator_rules);
                if(!$this->validator->validate()){
                    jsonResponse($this->validator->get_array_errors());
				}

                $id_user = id_session();
				$user_aditional = $this->cr_users->cr_get_user_additional($id_user);
				$id_award = cleanInput($_POST['id_award']);
				$user_awards = json_decode($user_aditional['user_awards'], true);

				unset($user_awards[$id_award]);

				$this->cr_users->cr_update_user_additional($id_user, array('user_awards' => json_encode($user_awards)));
				jsonResponse('Changes has been saved successfully.' , 'success');
            break;
            case 'add_job':
				checkPermisionAjax('manage_short_bio');

				$validator_rules = array(
                    array(
                        'field' => 'place',
                        'label' => 'Place (Company name)',
                        'rules' => array('required' => '', 'max_len[250]' => '')
                    ),
                    array(
                        'field' => 'date_from',
                        'label' => 'Date from',
                        'rules' => array('required' => '', 'valid_date[m/d/Y]' => '')
                    ),
                    array(
                        'field' => 'position',
                        'label' => 'Position',
                        'rules' => array('max_len[250]' => '')
                    )
                );

                $this->validator->set_rules($validator_rules);
                if(!$this->validator->validate()){
                    jsonResponse($this->validator->get_array_errors());
				}

				$date_from = getDateFormat($_POST['date_from'], 'm/d/Y', 'm/d/Y');
				if(validateDate($_POST['date_to'], 'm/d/Y')){
					$date_to = getDateFormat($_POST['date_to'], 'm/d/Y', 'm/d/Y');
				} else{
					$date_to = null;
				}

                $id_user = id_session();
				$user_aditional = $this->cr_users->cr_get_user_additional($id_user);
				$id_job = uniqid();
				$new_job = array(
					'id_job' => $id_job,
					'place' => cleanInput($_POST['place']),
					'position' => cleanInput($_POST['position']),
					'date_from' => $date_from,
					'date_to' => $date_to,
					'skills' => array()
				);

				$skills = $_POST['skills'];
				if(!empty($skills)){
					foreach($skills as $skill){
						if(!empty($skill)){
							$new_job['skills'][] = cleanInput($skill);
						}
					}
				}

				$user_jobs = json_decode($user_aditional['user_jobs'], true);
				$callback_jobs[$id_job] = $user_jobs[$id_job] = $new_job;
				$callback_jobs[$id_job]['date_from'] = getDateFormat($date_from, 'm/d/Y', 'M Y');
				$callback_jobs[$id_job]['date_to'] = $date_to !== null ? getDateFormat($date_to, 'm/d/Y', 'M Y') : 'Present';

				$cr_jobs_limit = config('cr_jobs_limit', 10);
				if(count($user_jobs) > $cr_jobs_limit){
					jsonResponse('You can not add more than ' . $cr_jobs_limit . ' jobs.');
				}

				$this->cr_users->cr_update_user_additional($id_user, array('user_jobs' => json_encode($user_jobs)));

				$callback = array('user_jobs' => $callback_jobs);

				jsonResponse('Changes has been saved successfully.' , 'success', $callback);
            break;
            case 'edit_job':
				checkPermisionAjax('manage_short_bio');

				$validator_rules = array(
                    array(
                        'field' => 'id_job',
                        'label' => 'Job info',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'place',
                        'label' => 'Place (Company name)',
                        'rules' => array('required' => '', 'max_len[250]' => '')
                    ),
                    array(
                        'field' => 'date_from',
                        'label' => 'Date from',
                        'rules' => array('required' => '', 'valid_date[m/d/Y]' => '')
                    ),
                    array(
                        'field' => 'position',
                        'label' => 'Position',
                        'rules' => array('max_len[250]' => '')
                    )
                );

                $this->validator->set_rules($validator_rules);
                if(!$this->validator->validate()){
                    jsonResponse($this->validator->get_array_errors());
				}

				$date_from = getDateFormat($_POST['date_from'], 'm/d/Y', 'm/d/Y');
				if(validateDate($_POST['date_to'], 'm/d/Y')){
					$date_to = getDateFormat($_POST['date_to'], 'm/d/Y', 'm/d/Y');
				} else{
					$date_to = null;
				}

                $id_user = id_session();
				$user_aditional = $this->cr_users->cr_get_user_additional($id_user);
				$user_jobs = json_decode($user_aditional['user_jobs'], true);
				$id_job = cleanInput($_POST['id_job']);
				if(!array_key_exists($id_job, $user_jobs)){
					jsonResponse(translate('systmess_error_invalid_data'));
				}

				$callback_jobs[$id_job] = $user_jobs[$id_job] = array(
					'id_job' => $id_job,
					'place' => cleanInput($_POST['place']),
					'position' => cleanInput($_POST['position']),
					'date_from' => $date_from,
					'date_to' => $date_to,
					'skills' => array()
				);


				$callback_jobs[$id_job]['date_from'] = getDateFormat($date_from, 'm/d/Y', 'M Y');
				$callback_jobs[$id_job]['date_to'] = $date_to !== null ? getDateFormat($date_to, 'm/d/Y', 'M Y') : 'Present';

				$skills = $_POST['skills'];
				if(!empty($skills)){
					foreach($skills as $skill){
						if(!empty($skill)){
							$callback_jobs[$id_job]['skills'][] = $user_jobs[$id_job]['skills'][] = cleanInput($skill);
						}
					}
				}

				$this->cr_users->cr_update_user_additional($id_user, array('user_jobs' => json_encode($user_jobs)));

				$callback = array('user_jobs' => $callback_jobs);

				jsonResponse('Changes has been saved successfully.' , 'success', $callback);
            break;
            case 'delete_job':
				checkPermisionAjax('manage_short_bio');

				$validator_rules = array(
                    array(
                        'field' => 'id_job',
                        'label' => 'Job info',
                        'rules' => array('required' => '')
                    )
                );

                $this->validator->set_rules($validator_rules);
                if(!$this->validator->validate()){
                    jsonResponse($this->validator->get_array_errors());
				}

                $id_user = id_session();
				$user_aditional = $this->cr_users->cr_get_user_additional($id_user);
				$id_job = cleanInput($_POST['id_job']);
				$user_jobs = json_decode($user_aditional['user_jobs'], true);

				unset($user_jobs[$id_job]);

				$this->cr_users->cr_update_user_additional($id_user, array('user_jobs' => json_encode($user_jobs)));

				jsonResponse('Changes has been saved successfully.' , 'success');
            break;
            case 'add_education':
				checkPermisionAjax('manage_short_bio');
				$current_year = (int)date('Y');
				$from_year_max = $current_year;
				$from_year_min = $from_year_max - 60;
				$to_year_max = $current_year + 10;
				$to_year_min = $to_year_max - 70;
				$validator_rules = array(
                    array(
                        'field' => 'school',
                        'label' => 'School',
                        'rules' => array('required' => '', 'max_len[250]' => '')
                    ),
                    array(
                        'field' => 'degree',
                        'label' => 'Degree',
                        'rules' => array('required' => '', 'max_len[250]' => '')
                    ),
                    array(
                        'field' => 'field_of_study',
                        'label' => 'Field of study',
                        'rules' => array('required' => '', 'max_len[250]' => '')
                    ),
                    array(
                        'field' => 'grade',
                        'label' => 'Grade',
                        'rules' => array('max_len[250]' => '')
                    ),
                    array(
                        'field' => 'year_from',
                        'label' => 'From year',
                        'rules' => array('required' => '', 'min['.$from_year_min.']' => '', 'max['.$from_year_max.']' => '')
                    ),
                    array(
                        'field' => 'year_to',
                        'label' => 'To year (or expected)',
                        'rules' => array('required' => '', 'min['.$to_year_min.']' => '', 'max['.$to_year_max.']' => '')
                    ),
                    array(
                        'field' => 'description',
                        'label' => 'Description',
                        'rules' => array('max_len[1000]' => '')
                    )
                );

                $this->validator->set_rules($validator_rules);
                if(!$this->validator->validate()){
                    jsonResponse($this->validator->get_array_errors());
				}

				global $tmvc;

                $id_user = id_session();
				$user_aditional = $this->cr_users->cr_get_user_additional($id_user);
				$id_education = uniqid();
				$new_education = array(
					'id_education' => $id_education,
					'school' => cleanInput($_POST['school']),
					'degree' => cleanInput($_POST['degree']),
					'field_of_study' => cleanInput($_POST['field_of_study']),
					'grade' => cleanInput($_POST['grade']),
					'year_from' => (int)$_POST['year_from'],
					'year_to' => (int)$_POST['year_to'],
					'description' => cleanInput($_POST['description'])
				);

				$user_educations = json_decode($user_aditional['user_educations'], true);
				$callback_educations[$id_education] = $user_educations[$id_education] = $new_education;

				if(count($user_educations) > $tmvc->my_config['cr_educations_limit']){
					jsonResponse('You can not add more than '.$tmvc->my_config['cr_educations_limit'].' educations.');
				}

				$this->cr_users->cr_update_user_additional($id_user, array('user_educations' => json_encode($user_educations)));

				$callback = array('user_educations' => $callback_educations);

				jsonResponse('Changes has been saved successfully.' , 'success', $callback);
            break;
            case 'edit_education':
				checkPermisionAjax('manage_short_bio');
				$current_year = (int)date('Y');
				$from_year_max = $current_year;
				$from_year_min = $from_year_max - 60;
				$to_year_max = $current_year + 10;
				$to_year_min = $to_year_max - 70;
				$validator_rules = array(
                    array(
                        'field' => 'id_education',
                        'label' => 'Education info',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'school',
                        'label' => 'School',
                        'rules' => array('required' => '', 'max_len[250]' => '')
                    ),
                    array(
                        'field' => 'degree',
                        'label' => 'Degree',
                        'rules' => array('required' => '', 'max_len[250]' => '')
                    ),
                    array(
                        'field' => 'field_of_study',
                        'label' => 'Field of study',
                        'rules' => array('required' => '', 'max_len[250]' => '')
                    ),
                    array(
                        'field' => 'grade',
                        'label' => 'Grade',
                        'rules' => array('max_len[250]' => '')
                    ),
                    array(
                        'field' => 'year_from',
                        'label' => 'From year',
                        'rules' => array('required' => '', 'min['.$from_year_min.']' => '', 'max['.$from_year_max.']' => '')
                    ),
                    array(
                        'field' => 'year_to',
                        'label' => 'To year (or expected)',
                        'rules' => array('required' => '', 'min['.$to_year_min.']' => '', 'max['.$to_year_max.']' => '')
                    ),
                    array(
                        'field' => 'description',
                        'label' => 'Description',
                        'rules' => array('max_len[1000]' => '')
                    )
                );

                $this->validator->set_rules($validator_rules);
                if(!$this->validator->validate()){
                    jsonResponse($this->validator->get_array_errors());
				}

				global $tmvc;

                $id_user = id_session();
				$user_aditional = $this->cr_users->cr_get_user_additional($id_user);
				$user_educations = json_decode($user_aditional['user_educations'], true);
				$id_education = cleanInput($_POST['id_education']);
				if(!array_key_exists($id_education, $user_educations)){
					jsonResponse(translate('systmess_error_invalid_data'));
				}

				$callback_educations[$id_education] = $user_educations[$id_education] = array(
					'id_education' => $id_education,
					'school' => cleanInput($_POST['school']),
					'degree' => cleanInput($_POST['degree']),
					'field_of_study' => cleanInput($_POST['field_of_study']),
					'grade' => cleanInput($_POST['grade']),
					'year_from' => (int)$_POST['year_from'],
					'year_to' => (int)$_POST['year_to'],
					'description' => cleanInput($_POST['description'])
				);

				$this->cr_users->cr_update_user_additional($id_user, array('user_educations' => json_encode($user_educations)));

				$callback = array('user_educations' => $callback_educations);

				jsonResponse('Changes has been saved successfully.' , 'success', $callback);
            break;
            case 'delete_education':
				checkPermisionAjax('manage_short_bio');

				$validator_rules = array(
                    array(
                        'field' => 'id_education',
                        'label' => 'Education info',
                        'rules' => array('required' => '')
                    )
                );

                $this->validator->set_rules($validator_rules);
                if(!$this->validator->validate()){
                    jsonResponse($this->validator->get_array_errors());
				}

                $id_user = id_session();
				$user_aditional = $this->cr_users->cr_get_user_additional($id_user);
				$user_educations = json_decode($user_aditional['user_educations'], true);
				$id_education = cleanInput($_POST['id_education']);

				unset($user_educations[$id_education]);

				$this->cr_users->cr_update_user_additional($id_user, array('user_educations' => json_encode($user_educations)));
				jsonResponse('Changes has been saved successfully.' , 'success');
			break;
            case 'update_contacts':
                checkPermisionAjax('manage_short_bio');
                global $tmvc;
                $id_user = id_session();
                $user_contacts = $_POST['contacts'];
                $contacts = array();

                foreach ($user_contacts as $key => $contact) {
                    $contacts[$key] = array(
                        'name' => cleanInput(trim($contact['name'])),
                        'value' => cleanInput(trim($contact['value']))
                    );
                }

                if(count($contacts) > $tmvc->my_config['cr_contacts_limit']){
                    jsonResponse('You can not add more than '.$tmvc->my_config['cr_contacts_limit'].' contacts.');
                }

                $this->cr_users->cr_update_user_additional($id_user, array('user_contacts' => json_encode($contacts)));
                jsonResponse('Changes has been saved successfully.' , 'success');
            break;
            case 'update_certificate':
                checkPermisionAjax('manage_short_bio');
                global $tmvc;
                $id_user = id_session();
                $certificates = $_POST['user_certificates'];
                $filtered_certificates = array();

                foreach ($certificates as $certificate) {
                    $certificate = cleanInput(trim($certificate));
                    if(!empty($certificate)){
                        $filtered_certificates[] = $certificate;
                    }
                }

                if(count($filtered_certificates) > $tmvc->my_config['cr_certificates_limit']){
                    jsonResponse('You can not add more than '.$tmvc->my_config['cr_certificates_limit'].' certificates.');
                }
                $this->cr_users->cr_update_user_additional($id_user, array('user_certificates' => json_encode($filtered_certificates)));
                jsonResponse('Changes has been saved successfully.' , 'success');
            break;
        }
    }

	function popup_forms(){
		if(!isAjaxRequest()){
			headerRedirect();
		}

		$this->_load_main();
		$op = $this->uri->segment(3);

		switch($op){
			case 'add_adward':
				checkPermisionAjaxModal('manage_short_bio');

				$this->view->display('new/user/cr_user/bio/bio_adward_form_view');
			break;
			case 'edit_adward':
				checkPermisionAjaxModal('manage_short_bio');

				$id_award = $this->uri->segment(4);
                $id_user = id_session();
				$user_aditional = $this->cr_users->cr_get_user_additional($id_user);
				$user_awards = json_decode($user_aditional['user_awards'], true);

				if(!array_key_exists($id_award, $user_awards)){
					messageInModal(translate('systmess_error_invalid_data'));
				}

				$data['user_award'] = $user_awards[$id_award];
				$this->view->assign($data);

				$this->view->display('new/user/cr_user/bio/bio_adward_form_view');
			break;
			case 'add_job':
				checkPermisionAjaxModal('manage_short_bio');

				$this->view->display('new/user/cr_user/bio/bio_job_form_view');
			break;
			case 'edit_job':
				checkPermisionAjaxModal('manage_short_bio');

				$id_job = $this->uri->segment(4);
                $id_user = id_session();
				$user_aditional = $this->cr_users->cr_get_user_additional($id_user);
				$user_jobs = json_decode($user_aditional['user_jobs'], true);

				if(!array_key_exists($id_job, $user_jobs)){
					messageInModal(translate('systmess_error_invalid_data'));
				}

				$data['user_job'] = $user_jobs[$id_job];
				$this->view->assign($data);
				$this->view->display('new/user/cr_user/bio/bio_job_form_view');
			break;
			case 'add_education':
				checkPermisionAjaxModal('manage_short_bio');

				$this->view->display('new/user/cr_user/bio/bio_education_form_view');
			break;
			case 'edit_education':
				checkPermisionAjaxModal('manage_short_bio');

				$id_education = $this->uri->segment(4);
                $id_user = id_session();
				$user_aditional = $this->cr_users->cr_get_user_additional($id_user);
				$user_educations = json_decode($user_aditional['user_educations'], true);

				if(!array_key_exists($id_education, $user_educations)){
					messageInModal(translate('systmess_error_invalid_data'));
				}

				$data['user_education'] = $user_educations[$id_education];
				$this->view->assign($data);

				$this->view->display('new/user/cr_user/bio/bio_education_form_view');
			break;
		}
	}
}
