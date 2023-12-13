<?php

use App\Common\Traits\VersionMetadataTrait;
use App\Documents\File\File;
use App\Documents\User\Manager;
use App\Documents\Versioning\VersionList;
use App\Documents\Versioning\PendingVersion;
use App\Documents\Versioning\AcceptedVersion;
use App\Documents\Versioning\RejectedVersion;
use App\Documents\Serializer\VersionSerializerStatic;
use App\Plugins\EPDocs\Rest\Objects\User;
use App\Plugins\EPDocs\Rest\Resources\File as FileResource;
use App\Plugins\EPDocs\Rest\Resources\FilePermissions as FilePermissionsResource;
use App\Plugins\EPDocs\Rest\Resources\User as UserResource;
use Doctrine\Common\Collections\ArrayCollection;
use Ramsey\Uuid\UuidInterface;
use App\Documents\Versioning\VersionInterface;
use App\Documents\File\FileAwareInterface;
use ExportPortal\Contracts\Filesystem\FilesystemOperator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Accreditation application controller.
 *
 * @author Bendiucov Tatiana
 * @todo Remove [15.12.2021]
 * Use of new controller Verification_Document_Types_Controller
 *
 * @property Accreditation_Model $accreditation
 * @property Translations_Model  $translations
 * @property User_Model          $users
 * @property Validator           $validator
 *
 * @deprecated v2.28.6 in favor of Verification_Document_Types_Controller
 */
class Accreditation_Controller extends TinyMVC_Controller
{
	use VersionMetadataTrait;

    /* private function _load_main()
    {
		$this->load->model('Accreditation_Model', 'accreditation');
		$this->load->model('Packages_Model', 'packages');
		$this->load->model('User_Model', 'users');
	} */

    public function index()
    {
		show_404();
	}

    /**
     * @deprecated v2.28.6 in favor of Verification_Document_Types_Controller::administration()
     */
    public function documents_administration(): Response
    {
		return new RedirectResponse(getUrlForGroup("/verification_document_types/administration"));

        checkAdmin('moderate_content');

		$this->view->assign('languages', $this->translations->get_allowed_languages(array('skip' => array('en'))));
		$this->view->assign('title', 'Accreditation documents');
		$this->view->display('admin/header_view');
		$this->view->display('admin/accreditation/documents_view');
		$this->view->display('admin/footer_view');
	}

    /**
     * @deprecated v2.28.6 in favor of Verification_Document_Types_Controller::ajax_operation()
     *
     * @return void
     */
    /* public function documents_administration_dt()
    {
        checkAdminAjaxDT('moderate_content');

        $sorting = [
            'per_p' => intVal($_POST['iDisplayLength']),
            'start' => intVal($_POST['iDisplayStart']),
            'sort_by' => flat_dt_ordering($_POST, [
                'dt_id' => 'id_document',
                'dt_title' => 'document_title',
                'dt_update' => 'document_base_text_updated_at',
            ])
        ];

        $conditions = dtConditions($_POST, [
            ['as' => 'i18n_with_lang',    'key' => 'lang',             'type' => 'cleanInput'],
            ['as' => 'i18n_without_lang', 'key' => 'not_lang',         'type' => 'cleanInput'],
            ['as' => 'base_update_from',  'key' => 'base_update_from', 'type' => 'getDateFormat:m/d/Y,Y-m-d|concat: 00:00:00'],
            ['as' => 'base_update_to',    'key' => 'base_update_to',   'type' => 'getDateFormat:m/d/Y,Y-m-d|concat: 23:59:59'],
        ]);

        $sorting['sort_by'] = empty($sorting['sort_by']) ? ["document_title-asc"] : $sorting['sort_by'];

        $params = array_merge($sorting, $conditions);

		$this->_load_main();

		$records = $this->accreditation->get_documents($params);
		$records_count = $this->accreditation->count_documents($params);
		$output = array(
			'sEcho' => intval($_POST['sEcho']),
			'iTotalRecords' => $records_count,
			'iTotalDisplayRecords' => $records_count,
			'aaData' => array()
		);

        $languages = arrayByKey($this->translations->get_allowed_languages(array('skip' => array('en'))), 'lang_iso2');
		foreach ($records as $record) {
            $document_id = $record['id_document'];
            $document_text_update_date = null !== $record['document_base_text_updated_at'] ? new \DateTime($record['document_base_text_updated_at']) : null;
            $document_edit_url = __SITE_URL . "accreditation/popup_forms/edit_accreditation_document/{$document_id}";
            $document_add_i18n_url = __SITE_URL . "accreditation/popup_forms/add_accreditation_document_i18n/{$document_id}";

            $document_i18n = array();
            $document_i18n_used = array();
            $document_i18n_list = json_decode($record['document_i18n'], true);
            if(!empty($document_i18n_list)) {
                foreach ($document_i18n_list as $lang_code => $i18n) {
                    if(!isset($languages[$lang_code])) {
                        continue;
                    }
                    if(empty($i18n['title']['value'])) {
                        continue;
                    }

                    $lang_id = $languages[$lang_code]['id_lang'];
                    $lang_name = $languages[$lang_code]['lang_name'];
                    $lang_code_uppercase = mb_strtoupper($lang_code);
                    $document_i18n_used[] = $lang_code;
                    $document_i18n_edit_url = __SITE_URL . "accreditation/popup_forms/edit_accreditation_document_i18n/{$document_id}/{$lang_id}";
                    $document_i18n_update_date = null;
                    $document_i18n_label_color = 'btn-primary';
                    $document_i18n_update_notice = "Translated in language: '{$lang_name}'";

                    if(null !== $i18n['title']['updated_at']) {
                        $document_i18n_update_date = new DateTime($i18n['title']['updated_at']);
                    }
                    if(null !== $document_i18n_update_date) {
                        $document_i18n_update_notice = "{$document_i18n_update_notice}. Last update: {$document_i18n_update_date->format('Y-m-d H:i:s')}";
                    }
                    if(
                        null !== $document_text_update_date &&
                        (
                            null === $document_i18n_update_date || $document_i18n_update_date < $document_text_update_date
                        )
                    ) {
                        $document_i18n_label_color = 'btn-danger';
                        $document_i18n_update_notice = "{$document_i18n_update_notice}. Update required";
                    }

                    $document_i18n[] = "
                        <a href=\"{$document_i18n_edit_url}\"
                            class=\"btn btn-xs {$document_i18n_label_color} mnw-30 w-30 mb-5 fancyboxValidateModalDT fancybox.ajax\"
                            data-title=\"Edit translation\"
                            title=\"{$document_i18n_update_notice}\">
                            {$lang_code_uppercase}
                        </a>
                    ";
                }
            }

            $document_edit_button = "";
            $document_delete_button = "";
            $document_add_i18n_button = "";
            if (have_right_or('moderate_content')) {
                $document_edit_button = "
                    <a href=\"{$document_edit_url}\"
                        class=\"ep-icon ep-icon_pencil fancyboxValidateModalDT fancybox.ajax\"
                        title=\"Edit document\"
                        data-title=\"Edit document\">
                    </a>
                ";

                $document_delete_button = '
                    <a href="#"
                        class="ep-icon ep-icon_remove txt-red confirm-dialog"
                        title="Delete the document"
                        data-callback="delete_doc"
                        data-doc="' . $record['id_document'] . '"
                        data-message="' . translate("systmess_confirm_delete_this_document") . '">
                    </a>
                ';
            }

            if(have_right('manage_translations') && !empty(array_diff_key($languages, array_flip($document_i18n_used)))) {
                $document_add_i18n_button = "
                    <a href=\"{$document_add_i18n_url}\"
                        class=\"ep-icon ep-icon_globe-circle fancyboxValidateModalDT fancybox.ajax fs-24\"
                        title=\"Translate document\"
                        data-title=\"Add translation\">
                    </a>
                ";
            }

			$output['aaData'][] = array(
				'dt_id'           => $document_id,
                'dt_title'        => $record['document_title'],
                'dt_translations' => implode('', $document_i18n),
                'dt_update'       => null !== $document_text_update_date ? $document_text_update_date->format('j M, Y H:i') : '',
                'dt_actions'      => "
                    {$document_add_i18n_button}
                    {$document_edit_button}
                    {$document_delete_button}
                ",
			);
		}

		jsonResponse(null, 'success', $output);
	} */

    /**
     * @deprecated v2.28.6 in favor of Verification_Document_Types_Controller::popup_forms()
     *
     * @return void
     */
    /* public function popup_forms(){
		checkIsAjax();

		if (!logged_in() && !logged_in_by_token()) {
			messageInModal(translate('systmess_error_should_be_logged_in'),'error');
        }

		$this->_load_main();

		$action = $this->uri->segment(3);
		$id_user = privileged_user_id();
        switch($action){
            case 'add_accreditation_document':
                if(!have_right('moderate_content')) {
                    messageInModal(translate('systmess_error_rights_perform_this_action'));
                }

                $data = array(
                    'groups' => $this->accreditation->get_groups(),
                    'languages' => $this->translations->get_languages(),
                    'countries' => $this->accreditation->get_countries(),
                    'industries' => model('category')->get_industries(),
                    'company_types' => model(Company_Model::class)->get_company_types(),
                );

				views(array('admin/accreditation/add_accreditation_document_view'), $data);
            break;
            case 'edit_accreditation_document':
                if(!have_right('moderate_content')){
                    messageInModal(translate('systmess_error_rights_perform_this_action'));
                }

                $id_document = intVal($this->uri->segment(4));
                $data['document'] = $this->accreditation->get_document($id_document);
                if(empty($data['document'])){
                    messageInModal(translate('systmess_error_document_does_not_exist'));
                }

                $data['document']['document_i18n'] = json_decode($data['document']['document_i18n'], true);
                $data['document']['document_titles'] = json_decode($data['document']['document_titles'], true);
                $data['groups_selected'] = explode(',', $data['document']['document_groups']);
                $data['groups_required_selected'] = explode(',', $data['document']['document_groups_required']);
                $data['countries_selected'] = explode(',', $data['document']['document_countries']);
                $data['industries_selected'] = explode(',', $data['document']['document_industries']);

                $data['groups'] = $this->accreditation->get_groups();
                $data['languages'] = $this->translations->get_languages();
                $data['countries'] = $this->accreditation->get_countries();
                $data['industries'] = model('category')->get_industries();

                $data['groups_all'] = count($data['groups_selected']) == count($data['groups']);
                $data['industries_all'] = count($data['industries_selected']) == count($data['industries']);
                $data['countries_all'] = count($data['countries_selected']) == count($data['countries']);

                $data['company_types'] = model(Company_Model::class)->get_company_types();
                $additional_options = empty($data['document']['document_additional_options']) ? array() : json_decode($data['document']['document_additional_options'], TRUE);
                $data['selected_company_types'] = $additional_options['company_types'] ?? array();

				$this->view->assign($data);
				$this->view->display('admin/accreditation/edit_accreditation_document_view');
            break;
            case 'add_accreditation_document_i18n':
                checkAdminAjax('manage_content,manage_translations');

                $document_id = (int) $this->uri->segment(4);
                if(
                    empty($document_id) ||
                    empty($document = $this->accreditation->get_document($document_id))
                ) {
                    messageInModal(translate('systmess_error_accreditation_document_id_not_found'));
                }

                $languages = arrayByKey($this->translations->get_allowed_languages(array('skip' => array('en'))), 'lang_iso2');
                $translations = array_keys(array_filter(json_decode($document['document_i18n'], true), function($lang_code) use($languages) {
                    return isset($languages[$lang_code]);
                }, ARRAY_FILTER_USE_KEY));

                $this->view->display('admin/accreditation/add_accreditation_document_i18n_form_view', array(
                    'action'       => __SITE_URL . "accreditation/ajax_operations/add_accreditation_document_i18n/{$document_id}",
                    'document'     => $document,
                    'languages'    => arrayByKey($languages, 'id_lang'),
                    'translations' => $translations,
                ));
            break;
            case 'edit_accreditation_document_i18n':
                checkAdminAjax('manage_content,manage_translations');

                $lang_id = (int) $this->uri->segment(5);
                $document_id = (int) $this->uri->segment(4);

                if(
                    empty($lang_id) ||
                    empty($language = $this->translations->get_language($lang_id))
                ) {
                    messageInModal(translate('systmess_error_lang_id_not_found'));
                }

                if(
                    $this->session->group_lang_restriction &&
                    !in_array($lang_id, $this->session->group_lang_restriction_list)
                ) {
                    messageInModal(translate('systmess_error_permission_not_granted'));
                }

                $lang_code = $language['lang_iso2'];
                $lang_name = $language['lang_name'];
                if(
                    empty($document_id) ||
                    empty($document = $this->accreditation->get_accreditation_document($document_id))
                ) {
                    messageInModal(translate('systmess_error_accreditation_document_id_not_found'));
                }

                $translations = json_decode($document['document_i18n'], true);
                $translation = !empty($translations[$lang_code]['title']['value']) ? $translations[$lang_code]['title'] : null;
                if(null === $translation) {
                    messageInModal(translate('systmess_error_accreditation_document_i18n_not_found'));
                }

                $this->view->display('admin/accreditation/edit_accreditation_document_i18n_form_view', array(
                    'action'       => __SITE_URL . "accreditation/ajax_operations/edit_accreditation_document_i18n/{$document_id}/{$lang_id}",
                    'document'     => array(
                        'id'             => $document_id,
                        'title'          => $translation['value'],
                        'title_original' => $document['document_title']
                    ),
                    'language'     => array(
                        'id'   => $lang_id,
                        'name' => $lang_name
                    ),
                ));
            break;
        }
    } */

    /**
     * @deprecated v2.28.6 in favor of Verification_Document_Types_Controller::ajax_operation()
     *
     * @return void
     */
    /* public function ajax_operations(){
		if (!isAjaxRequest()) {
			headerRedirect();
        }

		if (!logged_in() && !logged_in_by_token()) {
			jsonResponse(translate('systmess_error_should_be_logged_in'));
        }

		$this->_load_main();
		$action = $this->uri->segment(3);
        switch($action){
            case 'add_accreditation_document' :
                if(!have_right('moderate_content')){
                    jsonResponse(translate('systmess_error_rights_perform_this_action'));
                }

                $validator_rules = array(
                    array(
                        'field' => 'document_title',
                        'label' => "Document title",
                        'rules' => array('required' => '', 'max_len[250]' => ''),
                    ),
                );

                $groups = $_POST['groups'];
                if(!is_array($groups) || empty($groups)){
                    $validator_rules[] = array(
						'field' => 'groups',
						'label' => 'Users groups',
						'rules' => array('required' => '')
                    );
                }

                $groups_required = $_POST['groups_required'];
                if(!empty($groups_required)){
                    $validator_rules[] = array(
						'field' => 'groups_required',
						'label' => 'Users groups required',
						'rules' => array('required' => '')
                    );
                }

                $industries = $_POST['industries'];
                if(!is_array($industries) || empty($industries)){
                    $validator_rules[] = array(
						'field' => 'industries',
						'label' => 'Industries',
						'rules' => array('required' => '')
                    );
                }

                $countries = $_POST['countries'];
                if(!is_array($countries) || empty($countries)){
                    $validator_rules[] = array(
						'field' => 'countries',
						'label' => 'Countries',
						'rules' => array('required' => '')
                    );
                }

                $document_titles = array_map('cleanInput', $_POST['document_titles']);
                $document_titles = array_filter($document_titles, function ($title) {
                    return !empty($title);
                });

                if(!empty($validator_rules)) {
                    $this->validator->reset_postdata();
                    $this->validator->clear_array_errors();
                    $this->validator->validate_data = $_POST;
                    $this->validator->set_rules($validator_rules);
                    if (!$this->validator->validate()) {
                        jsonResponse($this->validator->get_array_errors());
                    }
                }

                if (!empty($_POST['company_types'])) {
                    $additional_options = array('company_types' => array_map('intval', $_POST['company_types']));
                }

                $now = new \DateTime();
                $document_title = cleanInput($_POST['document_title']);
                $insert = array(
                    'document_title'                => $document_title,
                    'document_groups'               => implode(',', $groups),
                    'document_countries'            => implode(',', $countries),
                    'document_titles'               => json_encode($document_titles),
                    'document_industries'           => implode(',', $industries),
                    'document_groups_required'      => '',
                    'document_additional_options'  => empty($additional_options) ? null : json_encode($additional_options),
                    'document_i18n'                 => json_encode(array(
                        'en' => array(
                            'title' => array(
                                'value'      => $document_title,
                                'created_at' => $now->format('Y-m-d H:i:s'),
                                'updated_at' => $now->format('Y-m-d H:i:s')
                            )
                        )
                    ), 192),
                );

				if(!empty($groups_required)) {
                    $insert['document_groups_required'] = implode(',',$groups_required);
                }

                $all_countries = $this->accreditation->get_countries();
                foreach ($all_countries as $country) {
                    $countries_ids[] = $country['id'];
                }

                $countries_diff = array_diff($countries_ids, $countries);
                if(empty($countries_diff)){
                    $insert['document_general_countries'] = 1;
                } else {
                    $insert['document_general_countries'] = 0;
                }

                $all_industries = model('category')->get_industries();
                foreach($all_industries as $industry){
                    $industries_ids[] = $industry['category_id'];
                }

                $industries_diff = array_diff($industries_ids, $industries);
                if(empty($industries_diff)){
                    $insert['document_general_industries'] = 1;
                } else {
                    $insert['document_general_industries'] = 0;
                }

                if($id_document = $this->accreditation->insert_doc($insert)){
					// Refresh groups relationships
					$this->accreditation->delete_doc_groups_relation($id_document);
                    $this->accreditation->insert_doc_groups_relation(array_map(
                        function ($group) use ($id_document, $groups_required) {
                            return  array(
                                'id_group'    => $group,
                                'id_document' => $id_document,
                                'is_required' => in_array($group, $groups_required) ? 1 : 0,
                            );
                        },
                        $groups ?? array()
                    ));

                    // Refresh countries relationships
                    $this->accreditation->delete_doc_countries_relation($id_document);
                    $this->accreditation->insert_doc_countries_relation(array_map(
                        function ($group) use ($id_document) {
                            return  array('id_country' => $group, 'id_document' => $id_document);
                        },
                        $countries ?? array()
                    ));

                    // Refresh industries relationships
                    $this->accreditation->delete_doc_industries_relation($id_document);
                    $this->accreditation->insert_doc_industries_relation(array_map(
                        function ($group) use ($id_document) {
                            return  array('id_industry' => $group, 'id_document' => $id_document);
                        },
                        $industries ?? array()
                    ));
				}
                jsonResponse(translate('systmess_success_document_added'), 'success');
            break;
            case 'edit_accreditation_document' :
                if(!have_right('moderate_content')){
                    jsonResponse(translate('systmess_error_rights_perform_this_action'));
                }

                $data_validate = $_POST;
                $validator_rules = array(
                    array(
                        'field' => 'document_title',
                        'label' => "Document title",
                        'rules' => array('required' => '', 'max_len[250]' => ''),
                    ),
                );

                $groups = $_POST['groups'];
                if(!is_array($groups) || empty($groups)){
                    $validator_rules[] = array(
						'field' => 'groups',
						'label' => 'Users groups',
						'rules' => array('required' => '')
                    );
                }

				$groups_required = $_POST['groups_required'];
                if(!empty($groups_required)){
                    $validator_rules[] = array(
						'field' => 'groups_required',
						'label' => 'Users groups required',
						'rules' => array('required' => '')
                    );
                }

                $industries = $_POST['industries'];
                if(!is_array($industries) || empty($industries)){
                    $validator_rules[] = array(
						'field' => 'industries',
						'label' => 'Industries',
						'rules' => array('required' => '')
                    );
                }

                $countries = $_POST['countries'];
                if(!is_array($countries) || empty($countries)){
                    $validator_rules[] = array(
						'field' => 'countries',
						'label' => 'Countries',
						'rules' => array('required' => '')
                    );
                }

                $document_titles = array_map('cleanInput', $_POST['document_titles']);
                $document_titles = array_filter($document_titles, function ($title) {
                    return !empty($title);
                });

                $all_countries = arrayByKey($this->accreditation->get_countries(), 'id');
                foreach($all_countries as $country){
                    $countries_ids[] = $country['id'];
                }

                foreach ($document_titles as $key_country => $title) {
                    $data_validate["document_title_{$key_country}"] = $title;
                    $validator_rules[] = array(
                        'field' => "document_title_{$key_country}",
                        'label' => "Document title for country " . $all_countries[$key_country]['country'],
                        'rules' => array('max_len[100]' => '')
                    );
                }

                $this->validator->reset_postdata();
                $this->validator->clear_array_errors();
                $this->validator->validate_data = $data_validate;
                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $id_document = intVal($_POST['id_document']);
                $document = $this->accreditation->get_document($id_document);
                if(empty($document)){
                    jsonResponse(translate('systmess_error_document_does_not_exist'));
                }

                $document['document_i18n'] = json_decode($document['document_i18n'], 1);
                if(empty($document['document_i18n'])) {
                    $document['document_i18n'] = [];
                }

                if (!empty($_POST['company_types'])) {
                    $additional_options = array('company_types' => array_map('intval', $_POST['company_types']));
                }

                $update = array(
                    'document_title'              => cleanInput($_POST['document_title']),
                    'document_groups'             => implode(',', $groups),
                    'document_countries'          => implode(',', $countries),
                    'document_industries'         => implode(',', $industries),
                    'document_titles'             => json_encode($document_titles),
                    'document_additional_options' => empty($additional_options) ? null : json_encode($additional_options),
                    'document_general_countries'  => 0,
                    'document_general_industries' => 0,
                    'document_groups_required'    => '',
                );

                $updated_at = new \DateTime();
                if($update['document_title'] !== $document['document_title'] || empty($document['document_i18n']['en']['title'])) {
                    $document['document_i18n']['en']['title']['value'] = $update['document_title'];
                    $document['document_i18n']['en']['title']['updated_at'] = $updated_at->format('Y-m-d H:i:s');
                    if(empty($document['document_i18n']['en']['title']['created_at'])) {
                        $document['document_i18n']['en']['title']['created_at'] = $updated_at->format('Y-m-d H:i:s');
                    }
                }
                if($update['document_title'] !== $document['document_title']) {
                    $update['document_base_text_updated_at'] = $updated_at->format('Y-m-d H:i:s');
                }
                $update['document_i18n'] = json_encode($document['document_i18n'], 192);

				if(!empty($groups_required)) {
                    $update['document_groups_required'] = implode(',', $groups_required);
                }

                $all_countries = $this->accreditation->get_countries();
                foreach($all_countries as $country){
                    $countries_ids[] = $country['id'];
                }

                $countries_diff = array_diff($countries_ids, $countries);
                if(empty($countries_diff)){
                    $update['document_general_countries'] = 1;
                } else {
                    $update['document_general_countries'] = 0;
                }

                $all_industries = model('category')->get_industries();
                foreach($all_industries as $industry){
                    $industries_ids[] = $industry['category_id'];
                }

                $industries_diff = array_diff($industries_ids, $industries);
                if(empty($industries_diff)){
                    $update['document_general_industries'] = 1;
                } else {
                    $update['document_general_industries'] = 0;
                }

                if($this->accreditation->update_doc($id_document, $update)){
                    // Refresh groups relationships
					$this->accreditation->delete_doc_groups_relation($id_document);
                    $this->accreditation->insert_doc_groups_relation(array_map(
                        function ($group) use ($id_document, $groups_required) {
                            return  array(
                                'id_group'    => $group,
                                'id_document' => $id_document,
                                'is_required' => in_array($group, $groups_required) ? 1 : 0,
                            );
                        },
                        $groups ?? array()
                    ));

                    // Refresh countries relationships
                    $this->accreditation->delete_doc_countries_relation($id_document);
                    $this->accreditation->insert_doc_countries_relation(array_map(
                        function ($group) use ($id_document) {
                            return  array('id_country' => $group, 'id_document' => $id_document);
                        },
                        $countries ?? array()
                    ));

                    // Refresh industries relationships
                    $this->accreditation->delete_doc_industries_relation($id_document);
                    $this->accreditation->insert_doc_industries_relation(array_map(
                        function ($group) use ($id_document) {
                            return  array('id_industry' => $group, 'id_document' => $id_document);
                        },
                        $industries ?? array()
                    ));
                }

                jsonResponse(translate('systmess_success_document_updated'), 'success');
            break;
            case 'add_accreditation_document_i18n':
                checkAdminAjax('manage_content');

                $validator_rules = array(
					array(
						'field' => 'id',
						'label' => 'Document',
						'rules' => array('required' => '', 'integer' => '')
					),
					array(
						'field' => 'language',
						'label' => 'Language',
						'rules' => array('required' => '', 'integer' => '')
					),
					array(
						'field' => 'title',
						'label' => 'Title',
						'rules' => array('required' => '', 'max_len[250]' => '')
                    )
                );

                $this->validator->set_rules($validator_rules);
				if (!$this->validator->validate()) {
					jsonResponse($this->validator->get_array_errors());
                }

                $lang_id = (int) $_POST['language'];
                $document_id = (int) $this->uri->segment(4);

                if(
                    empty($lang_id) ||
                    empty($language = $this->translations->get_language($lang_id))
                ) {
                    jsonResponse(translate('systmess_error_lang_id_not_found'));
                }

                if(
                    $this->session->group_lang_restriction &&
                    !in_array($lang_id, $this->session->group_lang_restriction_list)
                ) {
                    jsonResponse(translate('systmess_error_permission_not_granted'));
                }

                if(
                    empty($document_id) ||
                    empty($document = $this->accreditation->get_accreditation_document($document_id))
                ) {
                    jsonResponse(translate('systmess_error_accreditation_document_id_not_found'));
                }

                $lang_code = $language['lang_iso2'];
                $lang_name = $language['lang_name'];
                $translations = json_decode($document['document_i18n'], true);
                $translation = !empty($translations[$lang_code]['title']['value']) ? $translations[$lang_code]['title'] : null;
                if(null !== $translation) {
                    jsonResponse(translate("systmess_error_accreditation_document_i18n_exists"));
                }

                $translations[$lang_code] = array(
                    'title' => array(
                        'value'      => cleanInput($_POST['title']),
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ),
                    'lang' => array(
                        'id'        => $lang_id,
                        'abbr_iso2' => $lang_code,
                        'lang_name' => $lang_name
                    ),
                );
                $update = array(
                    'document_i18n'                   => json_encode($translations, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                    'document_translation_updated_at' => date('Y-m-d H:i:s'),
                );
                if(!$this->accreditation->update_doc($document_id, $update)){
                    jsonResponse(translate('systmess_error_accreditation_document_i18n_insert'));
                }

                jsonResponse(translate('systmess_success_accreditation_document_i18n_insert'), 'success');
            break;
            case 'edit_accreditation_document_i18n':
                checkAdminAjax('manage_content');

                $validator_rules = array(
                    array(
                        'field' => 'id',
                        'label' => 'Document',
                        'rules' => array('required' => '', 'integer' => '')
                    ),
                    array(
                        'field' => 'language',
                        'label' => 'Language',
                        'rules' => array('required' => '', 'integer' => '')
                    ),
                    array(
                        'field' => 'title',
                        'label' => 'Title',
                        'rules' => array('required' => '', 'max_len[250]' => '')
                    )
                );

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $lang_id = (int) $_POST['language'];
                $document_id = (int) $this->uri->segment(4);

                if(
                    empty($lang_id) ||
                    empty($language = $this->translations->get_language($lang_id))
                ) {
                    jsonResponse(translate('systmess_error_lang_id_not_found'));
                }

                if(
                    $this->session->group_lang_restriction &&
                    !in_array($lang_id, $this->session->group_lang_restriction_list)
                ) {
                    jsonResponse(translate('systmess_error_permission_not_granted'));
                }

                if(
                    empty($document_id) ||
                    empty($document = $this->accreditation->get_accreditation_document($document_id))
                ) {
                    jsonResponse(translate('systmess_error_accreditation_document_id_not_found'));
                }

                $lang_code = $language['lang_iso2'];
                $lang_name = $language['lang_name'];
                $translations = json_decode($document['document_i18n'], true);
                $translation = !empty($translations[$lang_code]['title']['value']) ? $translations[$lang_code]['title'] : null;
                if(null === $translation) {
                    jsonResponse(translate("systmess_error_accreditation_document_i18n_not_found"));
                }

                $translations[$lang_code]['title']['value'] = trim(cleanInput($_POST['title']));
                $translations[$lang_code]['title']['updated_at'] = date('Y-m-d H:i:s');
                $translations[$lang_code]['lang'] = array(
                    'id'        => $lang_id,
                    'abbr_iso2' => $lang_code,
                    'lang_name' => $lang_name
                );
                $update = array(
                    'document_i18n'                   => json_encode($translations, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                    'document_translation_updated_at' => date('Y-m-d H:i:s'),
                );
                if(!$this->accreditation->update_doc($document_id, $update)){
                    jsonResponse(translate('systmess_error_accreditation_document_i18n_update'));
                }

                jsonResponse(translate('systmess_success_accreditation_document_i18n_update'), 'success');
            break;
            case 'delete_accreditation_document':
                if(!have_right('moderate_content')){
                    jsonResponse(translate('systmess_error_rights_perform_this_action'));
                }

				$validator = $this->validator;
				$validator_rules = array(
					array(
						'field' => 'id_document',
						'label' => 'Document info',
						'rules' => array('required' => '', 'integer' => '')
					)
				);

				$this->validator->set_rules($validator_rules);
				if (!$validator->validate()) {
					jsonResponse($this->validator->get_array_errors());
                }

                $id_document = intVal($_POST['id_document']);
                $this->accreditation->delete_doc($id_document);
				$this->accreditation->delete_doc_groups_relation($id_document);
                jsonResponse(translate('systmess_succes_document_deleted'), 'success');
            break;
        }
	} */

    /**
     * @deprecated
     */
	/* function upgrade_bills_list(){
		if (!logged_in_by_token()) {
			messageInModal(translate('systmess_error_sended_data_not_valid'));
        }

		$this->_load_main();

		$data['token'] = cleanInput($this->uri->segment(3));
		$user = $this->accreditation->get_user_by_token($data['token']);
		if(empty($user)) {
			messageInModal(translate('systmess_error_sended_data_not_valid'));
		}

		$id_package = $user['upgrade_package'];
		$data['group_package'] = $this->packages->get_upgrade_package($id_package);
		if (empty($data['group_package'])) {
			messageInModal(translate('systmess_info_no_bills_for_payment'), 'info');
		}

		$params = array(
			'id_user' => $user['idu'],
			'id_item' => $id_package,
			'encript_detail' => 1,
			'bills_type' => '5',
			'pagination' => false
		);

		$this->load->model('User_Bills_Model', 'user_bills');
		$data['bills'] = $this->user_bills->get_user_bills($params);
		if (empty($data['bills'])) {
			messageInModal(translate('systmess_info_no_bills_for_payment'), 'info');
		}

		$data['status'] = $this->user_bills->get_bills_statuses();

		$this->view->assign($data);
		$this->view->display('new/accreditation/user/modal_bills_list_view');
	} */

    /**
     * @deprecated
     */
	/* function start_upgrade(){
		if (!isAjaxRequest()) {
			headerRedirect();
        }

		$validator_rules = array(
			array(
				'field' => 'package',
				'label' => 'Package info',
				'rules' => array('required' => '', 'integer' => '')
			)
		);

		$this->validator->set_rules($validator_rules);
		if (!$this->validator->validate()) {
			jsonResponse($this->validator->get_array_errors());
        }

		$this->_load_main();

		$token = cleanInput($this->uri->segment(3));
		$user = $this->accreditation->get_user_by_token($token);
		if(empty($user)){
			jsonResponse(translate('systmess_error_sended_data_not_valid'));
		}

		$id_package = intVal($_POST['package']);
		$group_package = $this->packages->getGrPackage($id_package);
		if(empty($group_package)) {
			jsonResponse(translate('systmess_error_group_package_does_not_exist'));
		}

		if($user['user_group'] != $group_package['gr_from']) {
			jsonResponse(translate('systmess_error_cannot_upgrade_to_this_package'));
		}

		$this->load->model('User_Bills_Model', 'user_bills');
		$count_group_bills = $this->user_bills->get_bills_count(array('id_user' => $user['idu'], 'status' => "'init', 'paid'", 'bills_type' => 5));
		if($count_group_bills) {
			jsonResponse(translate('systmess_info_upgrade_requested_check_confirmed'), 'info');
		}

		$upgrade_date = date('Y-m-d H:i:s');
		$insert_bill = array(
			'id_user' => $user['idu'],
			'bill_description' => 'This bill is for upgrading from ' . $group_package['gf_name'].' to ' . $group_package['gt_name'].'.',
			'id_type_bill' => 5, // Account upgrade
			'id_item' => $id_package,
			'due_date' => date_plus(7, 'days', $upgrade_date, true),
			'balance' => $group_package['price'],
			'pay_percents' => 100,
			'total_balance' => $group_package['price']
		);

		if($group_package['price'] == 0) {
			$id_bill = $this->user_bills->set_free_user_bill($insert_bill);
		} else {
			$id_bill = $this->user_bills->set_user_bill($insert_bill);
		}

		$industries = array();
		if($user['gr_type'] == 'Seller'){
			$industries = $this->accreditation->get_company_industries($user['idu']);
		}

		$accreditation_params = array(
			'group' => $group_package['gr_to'],
			'country' => $user['country'],
			'industries' => explode(',',$industries['industries']),
			'required' => 1,
            'return_type' => 'array',
            'language' => 'en',
		);

		$aditional_documents = array();
		$documents = $this->accreditation->get_accreditation_docs($accreditation_params);
		$documents_list = json_decode('['.$user['accreditation_docs'].']', true);
		$documents_list = arrayByKey($documents_list, 'id_document');
		foreach($documents as $valid_document){
			if(!isset($documents_list[$valid_document['id_document']])){
				$document_json = array(
					'title' => $valid_document['title'],
					'id_document' => $valid_document['id_document'],
					'status' => 'init',
					'status_title' => 'Not uploaded'
				);
				$aditional_documents[] = json_encode($document_json);
			}
		}

		if(!empty($aditional_documents)){
			if(!empty($user['accreditation_docs'])){
				$update = array(
					'accreditation_docs' => $user['accreditation_docs'] .','.implode(',', $aditional_documents)
				);
			} else{
				$update = array(
					'accreditation_docs' => implode(',', $aditional_documents)
				);
			}
		}

		$update['accreditation_files'] = 0;
		$update['upgrade_package'] = $id_package;

		$this->users->updateUserMain($user['idu'], $update);
		$notice = array(
			'add_date' => date('Y/m/d H:i:s'),
			'add_by' => 'System',
			'notice' => 'The user started the upgrade process.'
		);
		$this->users->set_notice($user['idu'], $notice);

		// EMAIL USER ABOUT START UPGRADE
		$this->load->model('Notify_Model', 'notify');

		$fname = $user['fname'];
		$lname = $user['lname'];
		$email = $user['email'];

		if(!empty($user['accreditation_transfer'])) {
			$transfer_user = json_decode($user['accreditation_transfer'], true);

			$fname = $transfer_user['fname'];
			$lname = $transfer_user['lname'];
			$email = $transfer_user['email'];
		}

		$data_email = array(
			'subject' 		=> 'Start account upgrade on Export Portal',
			'to' 			=> $email,
			'template_data' => array(
				'fname' 				=> $fname,
				'lname' 				=> $lname,
				'accreditation_token' 	=> $user['accreditation_token'],
				'new_group' 			=> $group_package['gt_name'],
				'price' 				=> $group_package['price'],
				'email_key_link' 		=> get_sha1_token($email, false),
				'template' 				=> 'user/upgrade/upgrade_start_view'
			)
		);

		$this->notify->add_email_to_queue($data_email);
		jsonResponse('', 'success');
	} */

    /**
     * @deprecated
     */
	public function update_verification_status()
    {
		checkIsLogged();
		checkPermision('moderate_content');

        set_time_limit(0);
        ini_set('memory_limit', '-1');

		foreach ($this->process_users_documents($this->get_user_documents()) as $user_id => $is_processed) {
			if (!$is_processed) {
				dump(sprintf("The users #%s verification state is not updated.", $user_id));
			}
		}

		dump("Users' verification state processing is finished.");
	}

    /**
     * @deprecated
     */
	private function get_user_documents()
	{
		$all_documents = model('user_personal_documents')->get_documents();
		foreach (arrayByKey($all_documents, 'id_user', true) as $user_id => $documents) {
			yield $user_id => $documents;
		}
	}

    /**
     * @deprecated
     */
	private function process_users_documents(\Iterator $list)
	{
		foreach ($list as $user_id => $documents) {
			yield $user_id => $this->set_verification_state($user_id, $documents);
		}
	}

    /**
     * @deprecated
     */
	private function set_verification_state($user_id, $documents)
	{
		$total_uploads = count($documents);
        $current_uploads = array_reduce($documents, function ($carry, $document) {
            if (
                null !== $document
            ) {
				$latest_version = VersionSerializerStatic::deserialize($document['latest_version'], VersionInterface::class, 'json');
				$metadata = $this->getVersionMetadata($latest_version);
				if (
					$metadata['is_version_pending']
					|| (
						$metadata['is_version_accepted'] && !$metadata['is_expired']
					)
				) {
					return $carry + 1;
				}
            }

            return $carry;
		}, 0);

		$upload_progress = 'none';
        if ($current_uploads === $total_uploads) {
            $upload_progress = 'full';
        } elseif ($current_uploads < $total_uploads) {
            $upload_progress = 'partial';
		}

		return model('user')->updateUserMain($user_id, array('verfication_upload_progress' => $upload_progress));
	}

    /**
     * @deprecated [2022-05-20]
     * @author Alexei Tolmachinski
     */
	// private function transform_user_documents(
	// 	$user_id,
	// 	$documents,
	// 	FileResource $files,
	// 	UserResource $users,
	// 	FilePermissionsResource $permissions,
	// 	FilesystemOperator $filesystem,
	// 	UuidInterface $manager_uuid = null,
	// 	\DateTimeInterface $date = null,
	// 	&$failures = array()
	// ) {
	// 	$records = new ArrayCollection();
	// 	$forget_all = function ($index, &$failures) use ($user_id, $records, $files) {
	// 		foreach ($records as $record) {
	// 			if (null !== ($version = $record['version']) && $version->hasFile()) {
	// 				try {
	// 					/** @var FileAwareInterface $version */
	// 					$files->deleteFile($version->getFile()->getId());
	// 				} catch (\Exception $exception) {
	// 					$failures['unknown-failure-reasons'][1][] = array("Failed to delete file in document nr. {$index} for user {$user_id}", $exception);
	// 				}
	// 			}
	// 		}
	// 	};

	// 	foreach ($documents as $index => $document) {
	// 		$status = arrayGet($document, 'status', 'init');
	// 		$type_id = with(arrayGet($document, 'id_document'), function ($id) { return null === $id ? $id : (int) $id; });
	// 		$version_index = $records->filter(function($record) use ($type_id) {
	// 			return $record['id_type'] === $type_id;
	// 		})->count() + 1;

	// 		$file = null;
	// 		$file_path = null;
	// 		$file_name = arrayGet($document, 'file_name', null);
	// 		$file_extension = arrayGet($document, 'file_ext', null);
	// 		if (!empty($file_name) && !empty($file_extension)) {
	// 			$file_path = "users_accreditation/{$user_id}/{$file_name}";
	// 			if ($filesystem->fileExists($file_path)) {
	// 				try {
	// 					$file_resource = $filesystem->readStream($file_path);
	// 					if (false === $file_resource) {
	// 						$file_resource = fopen('php://memory','r+');
	// 						fwrite($file_resource, $filesystem->read($file_path));
	// 						rewind($file_resource);
	// 					}

	// 					if (is_resource($file_resource)) {
	// 						$owner = $this->get_api_user($users, $user_id); // Create or get file owner
	// 						$user_file = $files->createFileFromResource($owner->getId(), $file_resource, $file_name, 'large_document');
	// 						fclose($file_resource);
	// 					}
	// 				} catch (\Exception $exception) {
	// 					$failures['file-not-created'][1][] = array("Failed to create file in document nr. {$index} for user {$user_id}", $exception);
	// 					$forget_all($index, $failures);

	// 					return false;
	// 				}

	// 				try {
	// 					$permissions->createPermissions($user_file->getId(), $manager_uuid, FilePermissionsResource::PERMISSION_READ | FilePermissionsResource::PERMISSION_WRITE); // Create permissions for manager
	// 					$file = new File(
	// 						$user_file->getId(),
	// 						$user_file->getName(),
	// 						$user_file->getExtension(),
	// 						$user_file->getSize(),
	// 						$user_file->getType(),
	// 						$user_file->getOriginalName()
	// 					);
	// 				} catch (\Exception $exception) {
	// 					$failures['permissions-not-created'][1][] = array("Failed to extend permisions for file document in nr. {$index} for user {$user_id}", $exception);
	// 					$forget_all($index, $failures);
	// 					if (null !== $user_file)  {
	// 						$files->deleteFile($user_file->getId());
	// 					}

	// 					return false;
	// 				}
	// 			} else {
	// 				$failures['file-not-found'][1][] = array("File is not found in document nr. {$index} for user {$user_id}");
	// 				$forget_all($index, $failures);

	// 				return false;
	// 			}
	// 		} else {
	// 			if (!in_array($status, array('init', 'decline'))) {
	// 				$failures['file-empty-for-status'][1][] = array("File is empty for non-init or non-declned status in document nr. {$index} for user {$user_id}");
	// 				$forget_all($index, $failures);

	// 				return false;
	// 			}
	// 		}

	// 		$manager = null;
	// 		if (null !== $manager_uuid) {
	// 			$manager = new Manager(null, null, $manager_uuid);
	// 		}

	// 		$expiration_date = null;
	// 		$expires_at = arrayGet($document, 'expire_on');
	// 		if (null !== $expires_at) {
	// 			$expiration_date = \DateTimeImmutable::createFromFormat("Y-m-d", $expires_at);
	// 			if (false === $expiration_date) {
	// 				$expiration_date = null;
	// 			}
	// 		}

	// 		$version = null;
	// 		$versions = new VersionList();
	// 		if ('processing' === $status) {
	// 			$version = new PendingVersion("v{$version_index}", null, $manager, $file);
	// 		} else if ('confirmed' === $status) {
	// 			$version = new AcceptedVersion("v{$version_index}", null, $manager, $file, null, $expiration_date);
	// 		} else if ('decline' === $status) {
	// 			$version = new RejectedVersion("v{$version_index}", null, $manager, $file, null, arrayGet($document, 'file_reason'));
	// 		}

	// 		if (null !== $version) {
	// 			if (null !== $date) {
	// 				$version = $version->withCreationDate($date);
	// 				if ($version instanceof AcceptedVersion) {
	// 					$version = $version->withAcceptanceDate($date);
	// 				}
	// 				if ($version instanceof RejectedVersion) {
	// 					$version = $version->withRejectionDate($date);
	// 				}
	// 			}

	// 			$versions->add($version);
	// 		}

	// 		$records->add(array(
	// 			'id_type'  => $type_id,
	// 			'id_user'  => (int) $user_id,
	// 			'versions' => !empty($status) && 'init' !== $status ? $versions : null,
	// 		));
	// 	}

	// 	if (!$records->isEmpty()) {
	// 		return model('user_personal_documents')->create_documents(array_map(function($record) {
	// 			if (null !== $record['versions']) {
	// 				$record['versions'] = VersionSerializerStatic::serialize($record['versions'], 'json');
	// 			}

	// 			return $record;
	// 		}, $records->toArray()));
	// 	}

	// 	return false;
	// }

	/**
     * Returns generic admin user object.
     *
     * @return User
     *
     * @deprecated
     */
    private function get_admin_generic_user(UserResource $users)
    {
        $cache_pool = library('fastcache')->pool('epdocs');
        if (
            !$cache_pool->has('generic_user')
            || null === ($manager = User::fromArray((array) $cache_pool->get('generic_user')))
            || null === $manager->getId()
        ) {
            $manager = $users->findUserIfNotCreate($this->get_user_api_context(config('env.EP_DOCS_ADMIN_SALT'))); // Create or get manager
            $cache_pool->set('generic_user', $manager->toArray(), 12 * 60 * 60);
        }

        return $manager;
	}

	/**
     * Returns generic admin user object.
     *
     * @return User
     *
     * @deprecated
     */
    private function get_api_user(UserResource $users, $user_id)
    {
        $cache_pool = library('fastcache')->pool('epdocs');
        if (
            !$cache_pool->has("user-{$user_id}")
            || null === ($user = User::fromArray((array) $cache_pool->get("user-{$user_id}")))
            || null === $user->getId()
        ) {
            $user = $users->findUserIfNotCreate($this->get_user_api_context($user_id)); // Create or get manager
            $cache_pool->set("user-{$user_id}", $user->toArray(), 12 * 60 * 60);
		}

        return $user;
	}

	/**
     * Returns the API context for given user
     *
     * @param int $user_id
     *
     * @return array
     *
     * @deprecated
     */
    private function get_user_api_context($user_id)
    {
        return array(
            'id'     => $user_id,
            'origin' => config('env.EP_DOCS_REFERRER', 'http://localhost'),
        );
    }
}
