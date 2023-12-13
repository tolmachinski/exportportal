<?php

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 *
 * @package	TinyMVC
 *
 * @property \TinyMVC_Load                    $load
 * @property \TinyMVC_View                    $view
 * @property \TinyMVC_Library_URI             $uri
 * @property \TinyMVC_Library_Session         $session
 * @property \TinyMVC_Library_Cookies         $cookies
 * @property \TinyMVC_Library_Upload          $upload
 * @property \TinyMVC_Library_validator       $validator
 * @property \Translations_Model              $translations
 * @property \Packages_Model                  $packages
 */
class Group_Packages_Controller extends TinyMVC_Controller
{
    public function administration()
    {
		checkAdmin('gr_packages_administration,manage_translations');

		$this->load->model('Packages_Model', 'packages');
		$this->load->model('UserGroup_Model', 'groups');

		$data = array(
			'title'     => 'Packages',
			'periods'   => $this->packages->selectPeriods(),
			'groups'    => $this->groups->getGroupsByType(array('type' => '"Buyer", "Seller"')),
            'languages' => $this->translations->get_allowed_languages(array('skip' => array('en'))),
		);

		$this->view->assign($data);
		$this->view->display('admin/header_view');
		$this->view->display('admin/packages/group_packages/index_view');
		$this->view->display('admin/footer_view');
	}

    public function ajax_group_packages_dt()
    {
		if (!isAjaxRequest()) {
			headerRedirect();
        }

		if (!logged_in()) {
			jsonDTResponse(translate("systmess_error_should_be_logged_in"));
        }

		checkAdminAjaxDT('manage_grouprights,manage_translations');

		$this->load->model('Packages_Model', 'packages');

        $sorting = [
            'per_p' => intVal($_POST['iDisplayLength']),
            'start' => intVal($_POST['iDisplayStart']),
            'sort_by' => flat_dt_ordering($_POST, [
                'dt_id'     => 'idpack',
                'dt_from'   => 'gr_from',
                'dt_to'     => 'gr_to',
                'dt_period' => 'period',
                'dt_price'  => 'price'
            ])
        ];

        $filters = dtConditions($_POST, [
            ['as' => 'translated_in', 'key' => 'translated_in', 'type' => 'cleanInput'],
            ['as' => 'not_translated_in', 'not_translated_in' => 'seller', 'type' => 'cleanInput'],
            ['as' => 'gr_from', 'key' => 'gr_from', 'type' => 'int'],
            ['as' => 'gr_to', 'key' => 'gr_to', 'type' => 'int'],
            ['as' => 'period', 'key' => 'period', 'type' => 'int'],
            ['as' => 'default', 'key' => 'default', 'type' => 'int'],
        ]);

        $sorting['sort_by'] = empty($sorting['sort_by']) ? ["gr_from-asc"] : $sorting['sort_by'];

        $conditions = array_merge($sorting, $filters);

        if(isset($_POST['en_updated_to'])) {
            $date = \DateTime::createFromFormat('m/d/Y', $_POST['en_updated_to']);
            if(empty($date)) {
                jsonDTResponse(translate("systmess_error_invalid_date_format_in_field", ["[FIELD_NAME]" => "EN update date to"]));
            }
            $conditions['en_updated_to'] = $date->format('Y-m-d');
        }

        if(isset($_POST['en_updated_from'])) {
            $date = \DateTime::createFromFormat('m/d/Y', $_POST['en_updated_from']);
            if(empty($date)) {
                jsonDTResponse(translate("systmess_error_invalid_date_format_in_field", ["[FIELD_NAME]" => "EN update date from"]));
            }
            $conditions['en_updated_from'] = $date->format('Y-m-d');
        }

		$gr_packages = $this->packages->get_gr_packages($conditions);
        $records_total = $this->packages->get_gr_packages_count($conditions);
        $languages = arrayByKey($this->translations->get_allowed_languages(array('skip' => array('en'))), 'lang_iso2');
        // $records_translations = array_map(
        //     function($list) { return array_column($list, 'idpack_i18n', 'lang_pack'); },
        //     arrayByKey($this->packages->getGrPackageI18nList(array(
        //         'columns'    => array('idpack', 'idpack_i18n', 'lang_pack'),
        //         'conditions' => array('packages' => array_column($gr_packages, 'idpack'), 'languages' => array_column($language, 'lang_iso2')),
        //     )), 'idpack', true)
        // );

		$output = array(
			'sEcho' => intval($_POST['sEcho']),
			'iTotalRecords' => $records_total,
			'iTotalDisplayRecords' => $records_total,
			'aaData' => array()
		);

		foreach ($gr_packages as $pack) {
            $pack_id = $pack['idpack'];
            $pack_text_updated_date = null !== $pack['en_updated_at'] ? new DateTime($pack['en_updated_at']) : null;
			$default = '<a class="ep-icon ep-icon_sheild-nok confirm-dialog" title="Change to default" data-callback="change_default_status" data-id="'.$pack['idpack'].'" data-message="Are sure want to change default status?"></a>';
			if($pack['def']) {
				$default = '<a class="ep-icon ep-icon_sheild-ok confirm-dialog" title="Change from default"  data-callback="change_default_status" data-id="'.$pack['idpack'].'" data-message="Are sure want to change default status?"></a>';
            }

			$name = 'Default';
			if(!empty($pack['gf_name'])) {
				$name = $pack['gf_name'];
            }

			$btn_visible = '<a class="ep-icon ep-icon_visible confirm-dialog" data-callback="change_status_group_package" data-message="Are you sure you want to disable this Account upgrade package?" title="Disable Account upgrade package" data-package-param="is_disabled" data-id="' . $pack['idpack'] . '"></a>';
			if($pack['is_disabled'] == 1) {
				$btn_visible = '<a class="ep-icon ep-icon_invisible confirm-dialog" data-callback="change_status_group_package" data-message="Are you sure you want to enable this Account upgrade package?"  title="Enable Account upgrade package" data-package-param="is_disabled" data-id="' . $pack['idpack'] . '"></a>';
            }

            $btn_active = '<a class="ep-icon ep-icon_unlocked confirm-dialog" data-callback="change_status_group_package" data-message="Are you sure you want to deactivate this Account upgrade package?"  title="Deactivate Account upgrade package" data-package-param="is_active" data-id="' . $pack['idpack'] . '"></a>';
			if($pack['is_active'] == 0) {
				$btn_active = '<a class="ep-icon ep-icon_locked confirm-dialog" data-callback="change_status_group_package" data-message="Are you sure you want to activate this Account upgrade package?"  title="Activate Account upgrade package" data-package-param="is_active" data-id="' . $pack['idpack'] . '"></a>';
            }

            $langs = array();
            $pack_i18n_list = array();
            $langs_record = json_decode($pack['translations_data'], true);
            $langs_used = array();
            if(!empty($langs_record)){
                foreach ($langs_record as $lang_code => $lang_record) {
                    if(empty($languages[$lang_code])) {
                        continue;
                    }

                    $langs_used[] = $lang_code;
                    $lang_name = $lang_record['lang_name'];
                    $lang_code_uppercase = mb_strtoupper($lang_record['abbr_iso2']);
                    $pack_i18n_edit_url = __SITE_URL . "group_packages/popup_forms/edit_package_i18n/{$pack_id}/{$lang_code}";
                    $pack_i18n_label_color = 'btn-primary';
                    $pack_i18n_update_notice = "Translated in language: '{$lang_name}'";
                    $pack_i18n_update_date = null !== $lang_record['updated_at'] ? new DateTime($lang_record['updated_at']) : null;
                    if(null !== $pack_i18n_update_date) {
                        $pack_i18n_update_notice = "{$pack_i18n_update_notice}. Last update: {$pack_i18n_update_date->format('Y-m-d H:i:s')}";
                    }
                    if(
                        null !== $pack_text_updated_date &&
                        (
                            null === $pack_i18n_update_date || $pack_i18n_update_date < $pack_text_updated_date
                        )
                    ) {
                        $pack_i18n_label_color = 'btn-danger';
                        $pack_i18n_update_notice = "{$pack_i18n_update_notice}. Update required";
                    }
                    $pack_i18n_list[] = "
                        <a href=\"{$pack_i18n_edit_url}\"
                            class=\"btn btn-xs {$pack_i18n_label_color} mb-5 mnw-25 w-30 fancyboxValidateModalDT fancybox.ajax\"
                            data-title=\"Edit translation\"
                            title=\"{$pack_i18n_update_notice}\">
                                {$lang_code_uppercase}
                            </a>
                        ";
                    }
            }

            if(null === $lang_record || !empty(array_diff($languages, $langs_used))) {
                $pack_i18n_add_url = __SITE_URL . "group_packages/popup_forms/add_package_i18n/{$pack_id}";
                $pack_i18n_add_button = "
                    <a href=\"{$pack_i18n_add_url}\"
                        data-title=\"Add translation\"
                        title=\"Add translation\"
                        class=\"fancyboxValidateModalDT fancybox.ajax\">
                        <i class=\"ep-icon ep-icon_globe-circle\"></i>
                        </a>
                    ";
                }

            $actions = $pack_i18n_add_button;
            if(have_right('manage_grouprights')) {
                $actions = "
                    {$actions}
                    {$default}
                    {$btn_visible}
                    {$btn_active}
                    <a href=\"group_packages/popup_forms/update_group_package/{$pack['idpack']}\"
                        class=\"ep-icon ep-icon_pencil fancyboxValidateModalDT fancybox.ajax\"
                        title=\"Update package info\"
                        data-title=\"Update package info\">
                    </a>
                    <a class=\"ep-icon ep-icon_remove txt-red confirm-dialog\"
                        data-callback=\"remove_group_package\"
                        data-message=\"Are you sure you want to delete this Account upgrade package?\"
                        title=\"Delete Account upgrade package\"
                        data-id=\"{$pack['idpack']}\">
                    </a>
                ";
            }

			$output['aaData'][] = array(
                'dt_id'           => $pack['idpack'],
				'dt_from'         => '<a class="txt-green ep-icon ep-icon_filter dt_filter pull-left" data-title="From group" title="Filter by '.$name.'"  data-value-text="'.$name.'" data-value="'.$pack['gr_from'].'" data-name="gr_from"></a><br/>' . $name,
				'dt_to'           => '<a class="txt-green ep-icon ep-icon_filter dt_filter pull-left" data-title="On group" title="Filter by '.$pack['gt_name'].'"  data-value-text="'.$pack['gt_name'].'" data-value="'.$pack['gr_to'].'" data-name="gr_to"></a><br/>' . $pack['gt_name'],
				'dt_downgrade_to' => '<a class="txt-green ep-icon ep-icon_filter dt_filter pull-left" data-title="Downgrade to" title="Filter by '.$pack['downgrade_gr_name'].'"  data-value-text="'.$pack['downgrade_gr_name'].'" data-value="'.$pack['downgrade_gr_to'].'" data-name="downgrade_gr_to"></a><br/>' . $pack['downgrade_gr_name'],
				'dt_period'       => '<a class="txt-green ep-icon ep-icon_filter dt_filter pull-left" data-title="Period" title="Filter by '.$pack['full'].'"  data-value-text="'.$pack['full'].'" data-value="'.$pack['period'].'" data-name="period"></a><br/>' . $pack['full'],
				'dt_price'        => '$'.$pack['price'],
                'dt_tlangs_list'  => implode(' ', $pack_i18n_list),
                'dt_actions'      => $actions
            );
		}

		jsonResponse(null, 'success', $output);
	}

    public function ajax_group_packages_operations()
    {
		if (!isAjaxRequest()) {
			headerRedirect();
        }

        checkIsLoggedAjax();

		$op = uri()->segment(3);

		switch ($op) {
			case 'edit_package':
                checkAdminAjax('manage_grouprights');

				$validator_rules = array(
					array(
						'field' => 'gr_from',
						'label' => 'Group from',
						'rules' => array('required' => '', 'integer' => '')
					),
					array(
						'field' => 'gr_to',
						'label' => 'Group to',
						'rules' => array('required' => '', 'integer' => '')
					),
					array(
						'field' => 'downgrade_gr_to',
						'label' => 'Downgrade to',
						'rules' => array('required' => '', 'integer' => '')
                    ),
					array(
						'field' => 'price',
						'label' => 'Period',
						'rules' => array('required' => '', 'float' => '')
					),
					array(
						'field' => 'description',
						'label' => 'Description',
						'rules' => array('required' => '', )
					),
					array(
						'field' => 'id',
						'label' => 'Id',
						'rules' => array('required' => '', 'integer' => '')
					)
				);

				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate()) {
					jsonResponse($this->validator->get_array_errors());
                }

                if (empty($package_info = model(Packages_Model::class)->getSimpleGrPackage($_POST['id']))) {
                    jsonResponse ('Could not find the package.');
                }

				$change_to = (int)(bool) $_POST['def'];
				if ($change_to === 1) {
					model(Packages_Model::class)->clear_default();
                }

				$update = array(
					'gr_from'         => $_POST['gr_from'],
					'gr_to'           => $_POST['gr_to'],
					'downgrade_gr_to' => $_POST['downgrade_gr_to'],
					'price'           => $_POST['price'],
					'description'     => $_POST['description'],
					'def'             => $change_to
                );

                foreach (array_keys($update) as $key) {
                    if ($update[$key] == $package_info[$key]) {
                        continue;
                    }

                    $update['updated_at'] = date('Y-m-d H:i:s');
                    break;
                }

                if ($update['description'] != $package_info['description']) {
                    $update['en_updated_at'] = date('Y-m-d H:i:s');
                }

				if (model(Packages_Model::class)->update_package($_POST['id'], $update)) {
					jsonResponse ('The package was successfuly updated.', 'success');
                }

				jsonResponse ('Cannot update package. Try later');
			break;
			case 'add_package':
                checkAdminAjax('manage_grouprights');

				$validator_rules = array(
					array(
						'field' => 'gr_from',
						'label' => 'Group from',
						'rules' => array('required' => '', 'integer' => '')
					),
					array(
						'field' => 'gr_to',
						'label' => 'Group to',
						'rules' => array('required' => '', 'integer' => '')
					),
					array(
						'field' => 'downgrade_gr_to',
						'label' => 'Downgrade to',
						'rules' => array('required' => '', 'integer' => '')
					),
					array(
						'field' => 'period',
						'label' => 'Period',
						'rules' => array('required' => '', 'integer' => '')
					),
					array(
						'field' => 'price',
						'label' => 'Period',
						'rules' => array('required' => '', 'float' => '')
					),
					array(
						'field' => 'description',
						'label' => 'Description',
						'rules' => array('required' => '')
					)
				);

				$this->validator->set_rules($validator_rules);

				if (!$this->validator->validate()) {
					jsonResponse($this->validator->get_array_errors());
                }

				$this->load->model('Packages_Model', 'packages');

				$change_to = intVal((bool)$_POST['def']);
				if($change_to === 1) {
					$this->packages->clear_default();
                }

				$translations_data = array(
					'en' => array(
						'lang_name' => 'English',
                        'abbr_iso2' => 'en',
                        'updated_at' => date('Y-m-d H:i:s')
					)
				);

				$insert = array(
					'gr_from' => $_POST['gr_from'],
					'gr_to' => $_POST['gr_to'],
					'downgrade_gr_to' => $_POST['downgrade_gr_to'],
					'period' => $_POST['period'],
					'price' => $_POST['price'],
					'description' => $_POST['description'],
                    'def' => $change_to,
                    'translations_data' => json_encode($translations_data),
				);

                $insert['updated_at'] = $insert['en_updated_at'] = date('Y-m-d H:i:s');

				if($this->packages->insert_package($insert)) {
					jsonResponse ('The package was successfuly updated.', 'success');
                }

				jsonResponse ('Error: Cannot update package. Try later');
			break;
            case 'edit_package_i18n':
                checkAdminAjax('manage_grouprights');

                $this->load->model('Packages_Model', 'packages');

				$validator_rules = array(
					array(
						'field' => 'language',
						'label' => 'Lang Package',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'description',
						'label' => 'Translated Description',
						'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'package',
                        'label' => 'Package Id',
                        'rules' => array('required' => '', 'integer' => '')
                    )
				);

				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate()) {
					jsonResponse($this->validator->get_array_errors());
                }

                $lang_id = (int) $_POST['language'];
                if(
                    empty($lang_id) ||
                    empty($language = $this->translations->get_language($lang_id))
                ) {
                    jsonResponse("The language with such ID is not found on this server");
                }

                if(
                    $this->session->group_lang_restriction &&
                    !in_array($lang_id, $this->session->group_lang_restriction_list)
                ) {
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));
                }

                $pack_id = (int) $_POST['package'];
                if(
                    empty($pack_id) ||
                    empty($package = $this->packages->getGrPackage($pack_id))
                ) {
                    jsonResponse("The package with such ID is not found on this server");
                }

                $lang_code = $language['lang_iso2'];
                $package_i18n = $this->packages->getGrPackageI18n(array(
                    'conditions' => array('package' => $pack_id, 'language' => $lang_code)
                ));
                if(null === $package_i18n) {
                    jsonResponse("Error: Package translation for this language already doesn't exist.");
                }

                $translations_data = json_decode($package['translations_data'], true);
                $translations_data[$lang_code]['updated_at'] = date('Y-m-d H:i:s');
                $update_i18n = array(
                    'description' => trim($_POST['description']),
                    'updated_at'  => date('Y-m-d H:i:s')
                );

				if($this->packages->updateGrPackage_i18n($package_i18n['idpack_i18n'], $update_i18n)) {
                    $this->packages->updateGrPackage($pack_id, array('translations_data' => json_encode($translations_data)));
					jsonResponse ('The package translation was successfuly updated.', 'success');
                }

                jsonResponse ('Error: Cannot update package. Try later');
            break;
            case 'add_package_i18n':
                checkAdminAjax('manage_grouprights');

                $this->load->model('Packages_Model', 'packages');

				$validator_rules = array(
					array(
						'field' => 'language',
						'label' => 'Lang Package',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'description',
						'label' => 'Translated Description',
						'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'package',
                        'label' => 'Package Id',
                        'rules' => array('required' => '', 'integer' => '')
                    )
				);

				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate()) {
					jsonResponse($this->validator->get_array_errors());
                }

                $lang_id = (int) $_POST['language'];
                if(
                    empty($lang_id) ||
                    empty($language = $this->translations->get_language($lang_id))
                ) {
                    jsonResponse("The language with such ID is not found on this server");
                }

                if(
                    $this->session->group_lang_restriction &&
                    !in_array($lang_id, $this->session->group_lang_restriction_list)
                ) {
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));
				}

                $pack_id = (int) $_POST['package'];
                if(
                    empty($pack_id) ||
                    empty($package = $this->packages->getGrPackage($pack_id))
                ) {
                    jsonResponse("The package with such ID is not found on this server");
                }

                $lang_code = $language['lang_iso2'];
                if($this->packages->hasGrPackageI18n($pack_id, $lang_code)) {
                    jsonResponse('Error: Package translation for this language already exist.');
                }

                $translations_data = json_decode($package['translations_data'], true);
				$translations_data[$lang_code] = array(
					'lang_name'  => $language['lang_name'],
                    'abbr_iso2'  => $lang_code,
                    'updated_at' => date('Y-m-d H:i:s')
				);

                $insert_i18n = array(
                    'idpack'      => $pack_id,
                    'description' => trim($_POST['description']),
                    'lang_pack'   => $lang_code,
                    'updated_at'  => date('Y-m-d H:i:s')
                );

				if($this->packages->setGrPackage_i18n($insert_i18n)) {
                    $this->packages->updateGrPackage($pack_id,  array('translations_data' => json_encode($translations_data)));

					jsonResponse ('The package translation was successfuly added.', 'success');
                }

				jsonResponse ('Error: Cannot update package. Try later');
            break;
			case 'change_status':
                checkAdminAjax('manage_grouprights');

                $package_param = $_POST['param'];
                if (!in_array($package_param, array('is_disabled', 'is_active'))) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

				$id_package = (int) $_POST['id'];
				if (empty($group_package = model(Packages_Model::class)->getSimpleGrPackage($id_package))) {
					jsonResponse('The package does not exist.');
				}

				if (model(Packages_Model::class)->update_package($id_package, array($package_param => (int) empty($group_package[$package_param])))) {
					jsonResponse('The package info has been updated.', 'success');
                }

				jsonResponse('Cannot update package info. Please try agaib late.');

			break;
			case 'change_default':
                checkAdminAjax('manage_grouprights');

				$this->load->model('Packages_Model', 'packages');
				$id_package = intVal($_POST['id']);
				$group_package = $this->packages->getSimpleGrPackage($id_package);
				if(empty($group_package)){
					jsonResponse('Error: The package does not exist.');
				}

				$change_to = 1;
				if($group_package['def'] == 1) {
					$change_to = 0;
                }

				if($this->packages->update_package($id_package, array('def' => $change_to))) {
					jsonResponse('The package info was updated.', 'success');
                }

				jsonResponse('Error: Cannot update package info.');

			break;
			case 'delete_group_package':
                checkAdminAjax('manage_grouprights');

                $id_package = (int) $_POST['id'];
                $upgrade_request = model(Upgrade_Model::class)->get_request(array(
                    'limit'         => 1,
                    'conditions'    => array('id_package' => $id_package)
                ));

                if (!empty($upgrade_request)) {
                    jsonResponse('The package can not be deleted, cause there are users which upgraded their accounts using this package.', 'warning');
                }

				if (model(Packages_Model::class)->deleteGrPackage($id_package)){
					jsonResponse('The package was deleted.', 'success');
				}

				jsonResponse('Error: Cannot delete package.');

            break;
			case 'delete_group_package_i18n':
                checkAdminAjax('manage_grouprights,manage_translations');

				$idpack = intval($_POST['idpack']);
				$lang_pack = cleanInput($_POST['lang']);
				$this->load->model('Packages_Model', 'packages');
				$pack_i18n = $this->packages->getGrPackageI18n(array(
                    'conditions' => array('package' => $idpack, 'language' => $lang_pack),
                    'with'       => array('package' => true)
                ));
				if(empty($pack_i18n)){
					jsonResponse('Error: The package translation does not exist.');
				}

				$translations_data = json_decode($pack_i18n['translations_data'], true);
				unset($translations_data[$lang_pack]);
				$this->packages->updateGrPackage($idpack, array('translations_data' => json_encode($translations_data)));
				$this->packages->deleteGrPackage_i18n($pack_i18n['idpack_i18n']);
				jsonResponse('The package translation has been successfully deleted.', 'success');

			break;
		}
	}

    public function popup_forms()
    {
		if (!isAjaxRequest()) {
			headerRedirect();
        }

		if (!logged_in()) {
			messageInModal(translate("systmess_error_should_be_logged"), 'errors');
        }

		$id_user = $this->session->id;
		$op = $this->uri->segment(3);
		switch ($op) {
			case 'update_group_package':
                checkAdminAjaxModal('manage_grouprights');

                $id = (int) uri()->segment(4);
                $upgrade_request = model(Upgrade_Model::class)->get_request(array(
                    'limit'         => 1,
                    'conditions'    => array('id_package' => $id)
                ));

				$data = array(
                    'is_used_package'   => !empty($upgrade_request),
					'package_info'      => model(Packages_Model::class)->getGrPackage($id),
					'periods'           => model(Packages_Model::class)->selectPeriods(),
                    'groups'            => model(UserGroup_Model::class)->getGroupsByType(array('type' => "'Buyer', 'Seller', 'Shipper'")),
				);

				views()->display('admin/packages/group_packages/group_package_form_view', $data);

			break;
			case 'edit_package_i18n':
                checkAdminAjaxModal('manage_grouprights,manage_translations');

                $this->load->model('Packages_Model', 'packages');

				$pack_id = (int) $this->uri->segment(4);
                $lang_code = cleanInput($this->uri->segment(5));

                if(
                    empty($lang_code) ||
                    empty($language = $this->translations->get_language_by_iso2($lang_code))
                ) {
                    messageInModal("The language with such code is not found on this server");
                }

                if(
                    $this->session->group_lang_restriction &&
                    !in_array($language['id_lang'], $this->session->group_lang_restriction_list)
                ) {
                    messageInModal(translate("systmess_error_rights_perform_this_action"));
                }

                if(
                    empty($pack_id) ||
                    empty($package = $this->packages->getGrPackageI18n(array(
                        'conditions' => array('package' => $pack_id, 'language' => $lang_code),
                        // @todo The code below is a legacy relation code. Remake with model Packages_Model::class
                        'with'       => array('package' => function($db) { $db->select("I18N.*, P.description as original"); })
                    )))
                ) {
                    messageInModal("The package with such ID is not found on this server");
                }

                $action = __SITE_URL . "group_packages/ajax_group_packages_operations/edit_package_i18n";

                $this->view->display('admin/packages/group_packages/group_package_form_view_edit_i18n', compact('action', 'package', 'language'));
            break;
			case 'add_package_i18n':
                checkAdminAjaxModal('manage_grouprights,manage_translations');

				$this->load->model('Packages_Model', 'packages');

                $pack_id = (int) $this->uri->segment(4);
                if(
                    empty($pack_id) ||
                    empty($package = $this->packages->getGrPackage($pack_id))
                ) {
                    messageInModal("The package with such ID is not found on this server");
                }

                $action = __SITE_URL . "group_packages/ajax_group_packages_operations/add_package_i18n";
                $languages = arrayByKey($this->translations->get_allowed_languages(array('skip' => array('en'))), 'id_lang');
                $translations = array_column(
                    $this->packages->getGrPackageI18nList(array(
                        'conditions' => array('package' => $pack_id, 'languages' => array_column($languages, 'lang_iso2')))
                    ),
                    'lang_pack'
                );

                $this->view->display('admin/packages/group_packages/group_package_form_view_add_i18n', compact('action', 'package', 'languages', 'translations'));
            break;
			case 'insert_group_package':
                checkAdminAjaxModal('manage_grouprights');

				$data = array(
                    'is_used_package'   => false,
					'periods'           => model(Packages_Model::class)->selectPeriods(),
                    'groups'            => model(UserGroup_Model::class)->getGroupsByType(array('type' => '"Buyer", "Seller", "Shipper"')),
				);

				views()->display('admin/packages/group_packages/group_package_form_view', $data);
			break;
		}
    }

    public function update_translation_meta()
    {
        if (!logged_in()){
            show_404();
        }

        $this->load->model('Packages_Model', 'packages');

        $packages = arrayByKey($this->packages->get_gr_packages(), 'idpack');
        $packages_i18n = arrayByKey($this->packages->getGrPackageI18nList(array(
            'columns' => array('idpack', 'idpack_i18n', 'lang_pack as lang_code', 'lang_name', 'updated_at'),
            'with'    => array('language' => true)
        )), 'idpack', true);

        foreach ($packages as $package_id => $package) {
            $update = array(
                "en" => array(
                    "abbr_iso2"  => "en",
                    "lang_name"  => "English",
                    "updated_at" => date("Y-m-d H:i:s")
                )
            );

            if(isset($packages_i18n[$package_id])) {
                foreach ($packages_i18n[$package_id] as $i18n) {
                    $update[$i18n["lang_code"]] = array(
                        "abbr_iso2"  => $i18n["lang_code"],
                        "lang_name"  => $i18n['lang_name'],
                        "updated_at" => null !== $i18n['updated_at'] ? $i18n['updated_at'] : date("Y-m-d H:i:s"),
                    );
                }
            }

            $this->packages->update_package($package_id, array('translations_data' => json_encode($update, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)));
        }
    }
}
