<?php
/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [18.12.2021]
 * Controller Refactoring
 */
class Text_block_Controller extends TinyMVC_Controller {

	function administration() {
		checkAdmin('manage_content,manage_translations');

        $data = array();
        $data['languages'] = $this->translations->get_allowed_languages(array('skip' => 'en'));
        $this->view->assign($data);

        $this->view->assign('title', 'Text block');
        $this->view->display('admin/header_view');
        $this->view->display('admin/text_blocks/index_view');
        $this->view->display('admin/footer_view');
    }

	function ajax_text_blocks_administration_dt(){
		if (!isAjaxRequest()) {
            headerRedirect();
        }

        if (!logged_in()) {
            jsonResponse(translate("systmess_error_should_be_logged"));
        }

		checkAdmin('manage_content,manage_translations');

        $this->load->model('Text_block_Model', 'text_block');

        $params = array('per_p' => (int)$_POST['iDisplayLength'], 'start' => (int)$_POST['iDisplayStart']);

        if ($_POST['iSortingCols'] > 0) {
            for ($i = 0; $i < $_POST['iSortingCols']; $i++) {
                switch ($_POST["mDataProp_" . (int)$_POST['iSortCol_' . $i]]) {
                    case 'dt_id':
                        $params['sort_by'][] = 'id_block-' . $_POST['sSortDir_' . $i];
                        break;
                    case 'dt_title':
                        $params['sort_by'][] = 'title_block-' . $_POST['sSortDir_' . $i];
                        break;
                    case 'dt_short_name':
                        $params['sort_by'][] = 'short_name-' . $_POST['sSortDir_' . $i];
                        break;
                    case 'dt_updated_at':
                        $params['sort_by'][] = 'updated_at-' . $_POST['sSortDir_' . $i];
                        break;
                }
            }
        }

        if (isset($_POST['keywords'])) {
            $params['keywords'] = cleanInput($_POST['keywords']);
        }

        if (isset($_POST['short_name'])) {
            $params['short_name'] = cleanInput($_POST['short_name']);
        }

        if(isset($_POST['translated_in'])) {
            $params['translated_in'] = $_POST['translated_in'];
        }

        if(isset($_POST['not_translated_in'])) {
            $params['not_translated_in'] = $_POST['not_translated_in'];
        }

		if(isset($_POST['en_updated_to']) && validateDate($_POST['en_updated_to'], 'm/d/Y')) {
			$params['en_updated_to'] = getDateFormat($_POST['en_updated_to'], 'm/d/Y', 'Y-m-d');
		}

		if(isset($_POST['en_updated_from']) && validateDate($_POST['en_updated_from'], 'm/d/Y')) {
			$params['en_updated_from'] = getDateFormat($_POST['en_updated_from'], 'm/d/Y', 'Y-m-d');
		}

        $records = $this->text_block->get_text_blocks($params);
        $records_count = $this->text_block->get_text_blocks_count($params);

        $output = array(
            'sEcho' => (int)$_POST['sEcho'],
            'iTotalRecords' => $records_count,
            'iTotalDisplayRecords' => $records_count,
			'aaData' => array()
		);

		if(empty($records)) {
			jsonResponse('', 'success', $output);
		}

		$languages = arrayByKey($this->translations->get_allowed_languages(array('skip' => array('en'))), 'lang_iso2');
		foreach ($records as $record) {
			$i18n_used = array();
			$i18n_list = array();
			$i18n_meta = array_filter(json_decode($record['translations_data'], true));
            $text_updated_date = getDateFormat($i18n_meta['en']['updated_at'], 'Y-m-d H:i:s');
            foreach ($i18n_meta as $lang_code => $i18n) {
				if(!array_key_exists($lang_code, $languages)) {
					continue;
				}

				if($this->session->group_lang_restriction && !in_array($languages[$lang_code]['id_lang'], $this->session->group_lang_restriction_list)){
					continue;
				}

				$i18n_used[$lang_code] = $lang_code;
				$i18n_update_date = getDateFormat($i18n['updated_at'], 'Y-m-d H:i:s');
				$i18n_list[] = '<a href="'.__SITE_URL.'text_block/popup_forms/edit_text_block_i18n/'.$record['id_block'].'/'.$lang_code.'"
                                    class="btn btn-xs tt-uppercase mnw-30 '.(($i18n['updated_at'] < $i18n_meta['en']['updated_at'])?'btn-danger':'btn-primary').' mb-5 fancyboxValidateModalDT fancybox.ajax"
                                    data-title="Edit translation"
                                    title="Last update: '.$i18n_update_date.'">
                                    '.$lang_code.'
                                </a>';

			}

			if(empty($i18n_list)){
				$i18n_list[] = '&mdash;';
            }

			$actions = array();

			if(have_right('manage_translations') && !empty(array_diff_key($languages, $i18n_used))) {
				$actions[] = '<a href="'.__SITE_URL.'text_block/popup_forms/add_text_block_i18n/'.$record['id_block'].'"
                                data-title="Add translation"
                                title="Add translation"
                                class="fancyboxValidateModalDT fancybox.ajax">
                                <i class="ep-icon ep-icon_globe-circle"></i>
                            </a>';
            }

            $actions[] = '<a class="fancybox fancybox.ajax"
                            href="'.__SITE_URL.'info_block/popup_forms/view/'.$record['short_name'].'"
                            title="'.$record['title_block'].'"
                            data-title="'.$record['title_block'].'">
                            <i class="ep-icon ep-icon_info fs-16"></i>
                        </a>';

			if(have_right('moderate_content')) {
				$actions[] = '<a class="ep-icon ep-icon_pencil fancyboxValidateModalDT fancybox.ajax"
								title="Edit text block"
								href="'.__SITE_URL.'text_block/popup_forms/edit_text_block/'.$record['id_block'].'"
								data-title="Edit text block">
							</a>';
				$actions[] = '<a class="ep-icon ep-icon_remove txt-red confirm-dialog"
									data-callback="remove_text_block"
									data-message="Are you sure want delete this text block?"
									title="Delete text block"
									data-id="'.$record['id_block'].'">
							</a>';
			}

            $output['aaData'][] = array(
                'dt_id' => $record['id_block'],
                'dt_title' => $record['title_block'],
                'dt_short_name' => $record['short_name'],
                'dt_description' => $record['description_block'],
                'dt_updated_at' => $text_updated_date,
                'dt_tlangs_list' => implode(' ', $i18n_list),
                'dt_actions' => implode(' ', $actions)
            );
        }

        jsonResponse('', 'success', $output);
	}

	public function ajax_text_block_operation() {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        if (!logged_in()) {
            jsonResponse(translate("systmess_error_should_be_logged"));
        }


		$id_user = $this->session->id;
        $op = $this->uri->segment(3);

        switch ($op) {
            case 'create_text_block':
                checkAdmin('manage_content');
                $validator = $this->validator;

				$validator_rules = array(
					array(
						'field' => 'short_name',
						'label' => 'Short name',
						'rules' => array('required' => '', 'max_len[50]' => '')
					),
					array(
						'field' => 'text_block',
						'label' => 'Text block',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'description_block',
						'label' => 'Description block',
						'rules' => array('required' => '', 'max_len[255]' => '')
                    ),
                    array(
                        'field' => 'title_block',
                        'label' => 'Title block',
                        'rules' => array('required' => '', 'max_len[500]' => '')
                    )
				);

				$this->validator->set_rules($validator_rules);

				if(!$validator->validate()){
					jsonResponse($validator->get_array_errors());
				}

				$this->load->model('Text_block_Model', 'text_block');

				$translations_data = array(
					'en' => array(
						'lang_name' => 'English',
                        'abbr_iso2' => 'en',
                        'updated_at' => date('Y-m-d H:i:s')
					)
				);

				$insert = array(
					'title_block' => cleanInput($_POST['title_block']),
					'short_name' => cleanInput($_POST['short_name']),
					'description_block' => cleanInput($_POST['description_block']),
                    'text_block' => $_POST['text_block'],
                    'translations_data' => json_encode($translations_data)
				);

				if($this->text_block->set_text_block($insert)) {
					jsonResponse('The text block has been successfully inserted', 'success');
                }

				jsonResponse('Error: Cannot insert now. Please try later.');
			break;
			case 'edit_text_block':
                checkAdmin('manage_content');
                $validator = $this->validator;

				$validator_rules = array(
					array(
						'field' => 'short_name',
						'label' => 'Short name',
						'rules' => array('required' => '', 'max_len[50]' => '')
					),
					array(
						'field' => 'text_block',
						'label' => 'Text block',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'description_block',
						'label' => 'Description block',
						'rules' => array('required' => '', 'max_len[255]' => '')
                    ),
                    array(
                        'field' => 'title_block',
                        'label' => 'Title block',
                        'rules' => array('required' => '', 'max_len[500]' => '')
                    ),
                    array(
                        'field' => 'id_block',
                        'label' => 'Id text block',
                        'rules' => array('required' => '', 'integer' => '')
                    )
				);

				$this->validator->set_rules($validator_rules);

				if(!$validator->validate()){
					jsonResponse($validator->get_array_errors());
				}

				$this->load->model('Text_block_Model', 'text_block');
                $id_block = (int) $_POST['id_block'];
                $text_block_model = $this->text_block->get_text_block($id_block);
                if(empty($text_block_model)) {
                    jsonResponse('Could not find the text block.');
                }

                $title_block = cleanInput($_POST['title_block']);
                $short_name = cleanInput($_POST['short_name']);
                $description_block = cleanInput($_POST['description_block']);
                $text_block = $_POST['text_block'];

                if( $title_block === $text_block_model['title_block']
                    && $short_name === $text_block_model['short_name']
                    && $description_block === $text_block_model['description_block']
                    && $text_block === $text_block_model['text_block']
                ) {
                    jsonResponse('The same data', 'error');
                }

                $translations_data = json_decode($text_block_model['translations_data'], true);
                if(empty($translations_data)) {
                    $translations_data = array(
                        'en' => array(
                            'lang_name' => 'English',
                            'abbr_iso2' => 'en',
                        )
                    );
                }
                $translations_data['en']['updated_at'] = date('Y-m-d H:i:s');

				$update = array(
					'title_block' => $title_block,
					'short_name' => $short_name,
					'description_block' => $description_block,
                    'text_block' => $text_block,
                    'translations_data' => json_encode($translations_data)
				);

				if($this->text_block->update_text_block($id_block, $update)) {
					jsonResponse('The text block has been successfully updated.', 'success');
                }

				jsonResponse('Cannot update this text block now Please try later.');
			break;
            case 'create_text_block_i18n':
                checkAdmin('manage_translations');
                $validator = $this->validator;

				$validator_rules = array(
					array(
						'field' => 'id_block',
						'label' => 'Block info',
						'rules' => array('required' => '', 'integer' => '')
					),
					array(
						'field' => 'title_block',
						'label' => 'Title block',
						'rules' => array('required' => '', 'max_len[500]' => '')
					),
					array(
						'field' => 'lang_id',
						'label' => 'Language',
						'rules' => array('required' => '', 'integer' => '')
					),
					array(
						'field' => 'description_block',
						'label' => 'Description block',
						'rules' => array('required' => '', 'max_len[255]' => '')
					),
					array(
						'field' => 'text_block',
						'label' => 'Text block',
						'rules' => array('required' => '')
					)
				);

				$this->validator->set_rules($validator_rules);

				if(!$validator->validate()){
					jsonResponse($validator->get_array_errors());
				}

                $languages = $this->translations->get_allowed_languages(array('id_lang' => $_POST['lang_id']));
                if(empty($languages)) {
                    jsonResponse('Could not find the language or language is not allowed');
                }
                $language = $languages[0];

				$this->load->model('Text_block_Model', 'text_block');
				$id_block = (int) $_POST['id_block'];
                $text_block_model = $this->text_block->get_text_block($id_block);
                if(empty($text_block_model)) {
                    jsonResponse('Could not find text block.');
                }

                $translations_data = json_decode($text_block_model['translations_data'], true);
                if(empty($translations_data)) {
                    $translations_data = array(
                        'en' => array(
                            'lang_name' => 'English',
                            'abbr_iso2' => 'en',
                            'updated_at'=> date('Y-m-d H:i:s')
                        )
                    );
                }
                if(!empty($translations_data[$language['lang_iso2']])) {
                    jsonResponse('The translation for this language just exists.');
                }

				$insert_i18n = array(
					'id_block' => $id_block,
					'title_block' => cleanInput($_POST['title_block']),
					'lang_block' => $language['lang_iso2'],
					'description_block' => cleanInput($_POST['description_block']),
					'text_block' => $_POST['text_block'],
				);

				if($this->text_block->set_text_block_i18n($insert_i18n)){
                    $translations_data[$language['lang_iso2']] = array(
                        'lang_name' => $language['lang_name'],
                        'abbr_iso2' => $language['lang_iso2'],
                        'updated_at' => date('Y-m-d H:i:s')
                    );

                    $update = array(
                        'translations_data' => json_encode($translations_data)
                    );

                    if(!$this->text_block->update_text_block($id_block, $update)) {
                        jsonResponse('Could not update the text block.');
                    }

					jsonResponse('The text block translation has been successfully added', 'success');
				}

				jsonResponse('Error: Cannot insert now. Please try later.');
			break;
			case 'edit_text_block_i18n':
                checkAdmin('manage_translations');

                $validator = $this->validator;

				$validator_rules = array(
					array(
						'field' => 'id_block_i18n',
						'label' => 'Block info',
						'rules' => array('required' => '', 'integer' => '')
					),
					array(
						'field' => 'title_block',
						'label' => 'Title block',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'text_block',
						'label' => 'Text block',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'description_block',
						'label' => 'Description block',
						'rules' => array('required' => '')
                    )
				);

				$this->validator->set_rules($validator_rules);
				if(!$validator->validate()){
					jsonResponse($validator->get_array_errors());
				}

				$this->load->model('Text_block_Model', 'text_block');
				$id_block_i18n = $_POST['id_block_i18n'];
                $text_block_i18n = $this->text_block->get_text_block_i18n(array('id_block_i18n' => $id_block_i18n));
				if(empty($text_block_i18n)){
					jsonResponse('Text block translation does not exist.');
				}

                $languages = $this->translations->get_allowed_languages(array('lang_iso2' => $text_block_i18n['lang_block']));
                if(empty($languages)) {
                    jsonResponse('Could not find the language or language is not allowed');
                }
                $language = $languages[0];

                $text_block = $this->text_block->get_text_block($text_block_i18n['id_block']);
                if(empty($text_block)) {
                    jsonResponse('Could not find the text block');
                }

				$update_i18n = array(
					'title_block' => cleanInput($_POST['title_block']),
					'description_block' => cleanInput($_POST['description_block']),
					'text_block' => $_POST['text_block']
				);

				if($this->text_block->update_text_block_i18n($id_block_i18n, $update_i18n)){
                    $translations_data = json_decode($text_block['translations_data'], true);
                    $translations_data[$language['lang_iso2']]['updated_at'] = date('Y-m-d H:i:s');
                    $update = array(
                        'translations_data' => json_encode($translations_data)
                    );

                    if(!$this->text_block->update_text_block($text_block_i18n['id_block'], $update)) {
                        jsonResponse('Cannot update this text block now Please try later.');
                    }

					jsonResponse('The text block translation has been successfully updated.', 'success');
				}

				jsonResponse('Cannot update this text block now Please try later.');
			break;
			case 'delete':
                checkAdmin('manage_content');
				$this->load->model('Text_block_Model', 'text_block');
				$id_block = (int)$_POST['id'];
            	if($this->text_block->delete_text_block($id_block)) {
                	jsonResponse('The text block has been successfully deleted.', 'success');
                }

				jsonResponse('Error: Cannot delete this text block now. Please try later.');
            break;
		}
	}


	function popup_forms(){
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        if (!logged_in()) {
            jsonResponse(translate("systmess_error_should_be_logged"));
        }


        $op = $this->uri->segment(3);
        $id_block = (int)$this->uri->segment(4);

        switch($op){
			case 'add_text_block':
                checkAdmin('manage_content');

                $this->view->display('admin/text_blocks/modal_form_view');
            break;
			case 'add_text_block_i18n':
                checkPermisionAjaxModal('manage_translations');
				$this->load->model('Text_block_Model', 'text_block');

                $data['text_block'] = $this->text_block->get_text_block($id_block);
                if(empty($data['text_block'])){
                    messageInModal('The text block does not exist.');
                }

                $data['languages'] = $this->translations->get_allowed_languages(array('skip' => array('en')));
                if(empty($data['languages'])){
                    messageInModal('There are no languages available.');
                }

                $this->view->display('admin/text_blocks/modal_form_i18n_view', $data);
            break;
            case 'edit_text_block':
                checkAdmin('manage_content');
				$this->load->model('Text_block_Model', 'text_block');

                $data['block_info'] = $this->text_block->get_text_block($id_block);
                $this->view->display('admin/text_blocks/modal_form_view', $data);
            break;
            case 'edit_text_block_i18n':
                checkAdmin('manage_translations');
				$this->load->model('Text_block_Model', 'text_block');

                $data['text_block'] = $this->text_block->get_text_block($id_block);
                if(empty($data['text_block'])){
                    messageInModal('The text block does not exist.');
                }

				$lang_block = $this->uri->segment(5);
				$data['language'] = $this->translations->get_language_by_iso2($lang_block);
				if(empty($data['language'])){
					messageInModal('The language does not exist.');
                }

                $data['text_block_i18n'] = $this->text_block->get_text_block_i18n(array('id_block' => $id_block, 'lang_block' => $lang_block));
                if(empty($data['text_block_i18n'])){
                    messageInModal('The translation does not exist.');
                }

				if($this->session->group_lang_restriction && !in_array($data['language']['id_lang'], $this->session->group_lang_restriction_list)){
					messageInModal('You are not privileged to translate in this language.');
				}

                $this->view->display('admin/text_blocks/modal_form_i18n_view', $data);
            break;
        }
    }
}
