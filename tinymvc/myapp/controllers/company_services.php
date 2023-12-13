<?php

use App\Common\Exceptions\NotFoundException;
use App\Common\Exceptions\NotUniqueException;
use App\Common\Exceptions\OwnershipException;
use App\Entities\Phones\CountryCode as PhoneCountryCode;
use App\Services\PhoneCodesService;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

/**
 * Company services controller.
 *
 * @property \Country_Model             $countries
 * @property \Company_Services_Model    $company_services
 * @property \TinyMVC_Load              $load
 * @property \TinyMVC_View              $view
 * @property \TinyMVC_Library_URI       $uri
 * @property \TinyMVC_Library_Session   $session
 * @property \TinyMVC_Library_Cookies   $cookies
 * @property \TinyMVC_Library_Upload    $upload
 * @property \TinyMVC_Library_validator $validator
 * @property \TinyMVC_Library_Wall      $wall
 * @property \User_Model                $user
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [15.12.2021]
 * Controller Refactoring
 */
class Company_Services_Controller extends TinyMVC_Controller
{
    public function index()
    {
        show_404();
    }

    public function my()
    {
        checkIsLogged();
        checkHaveCompany();
        checkPermision('have_services_contacts');
        checkGroupExpire();

        $this->view->assign(array(
            'title'      => translate('company_services_dashboard_page_title_text', null, true),
        ));

        $this->view->display('new/header_view');
        $this->view->display('new/user/seller/services/my/index_view');
        $this->view->display('new/footer_view');
    }

    public function popup_forms()
    {
        checkIsAjax();
        checkIsLoggedAjaxModal();
        checkHaveCompanyAjaxModal();
        checkPermisionAjaxModal('have_services_contacts');
        checkGroupExpire('modal');

        $this->load->model('Company_Services_Model', 'company_services');
        $this->load->model('Country_Model', 'countries');

        switch ($this->uri->segment(3)) {
            case 'add_service':
                $this->show_add_service_form(new PhoneCodesService(model('country')));

                break;
            case 'edit_service':
                $this->show_edit_service_form((int) my_company_id(), (int) uri()->segment(4), new PhoneCodesService(model('country')));

            break;
            default:
                show_404();

            break;
        }
    }

    public function ajax_services_list_dt()
    {
        checkIsAjax();
        checkIsLoggedAjaxDT();
        checkHaveCompanyAjaxDT();
        checkPermisionAjaxDT('have_services_contacts');
        checkGroupExpire('dt');

        /** @var Company_Services_Model $companyServicesModel */
        $companyServicesModel = model(Company_Services_Model::class);

        $request = request()->request;

        $skip = (int) $request->get('iDisplayStart');
        $limit = (int) $request->get('iDisplayLength');
        $with = ['companies' => true];
        $columns = [
            'SERVICES.*',
            'COMPANIES.logo_company',
            'COMPANIES.index_name',
            'COMPANIES.name_company',
            'COMPANIES.type_company',
            'COMPANIES.visible_company',
        ];

        $conditions = array_merge(
            dtConditions($request->all(), [
                ['as' => 'search',          'key' => 'keywords',        'type' => 'cleanInput|cut_str:200'],
                ['as' => 'created_from',    'key' => 'created_from',    'type' => 'concat: 00:00:00|getDateFormat:m/d/Y H:i:s, Y-m-d H:i:s'],
                ['as' => 'created_to',      'key' => 'created_to',      'type' => 'concat: 23:59:59|getDateFormat:m/d/Y H:i:s, Y-m-d H:i:s'],
                ['as' => 'updated_from',    'key' => 'updated_from',    'type' => 'concat: 00:00:00|getDateFormat:m/d/Y H:i:s, Y-m-d H:i:s'],
                ['as' => 'updated_to',      'key' => 'updated_to',      'type' => 'concat: 23:59:59|getDateFormat:m/d/Y H:i:s, Y-m-d H:i:s'],
            ]),
            [
                'company' => my_company_id()
            ]
        );

        $order = array_column(dt_ordering($request->all(), [
            'service'    => 'title_service',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at',
        ]), 'direction', 'column');

        $servicesList = $companyServicesModel->get_services(compact('conditions', 'columns', 'order', 'limit', 'with', 'skip'));
        $totalServices = $companyServicesModel->count_services(compact('conditions', 'order', 'limit', 'with', 'skip'));
        $output = [
            'sEcho'                => $request->getInt('sEcho'),
            'iTotalRecords'        => $totalServices,
            'iTotalDisplayRecords' => $totalServices,
            'aaData'               => [],
        ];

        if (empty($servicesList)) {
            jsonResponse('', 'success', $output);
        }

        $output['aaData'] = $this->my_company_services($servicesList);

        jsonResponse('', 'success', $output);
    }

    public function ajax_services_operations()
    {
        checkIsAjax();
        checkIsLoggedAjax();
        checkHaveCompanyAjax();
        checkPermisionAjax('have_services_contacts');
        checkGroupExpire('ajax');

        $this->load->model('Company_Services_Model', 'company_services');
        $this->load->model('Country_Model', 'countries');

        switch ($this->uri->segment(3)) {
            case 'add_service':
                is_allowed('freq_allowed_services_operations');

                $this->create_service(request()->request->all(), new PhoneCodesService(model('country')));

            break;
            case 'edit_service':
                is_allowed('freq_allowed_services_operations');

                $this->edit_service((int) my_company_id(), (int) uri()->segment(4), request()->request->all(), new PhoneCodesService(model('country')));

            break;
            case 'delete_service':
                $this->delete_service((int) $_POST['service']);

            break;
            case 'delete_services':
                $this->delete_services(array_map('intval', $_POST['service']));

            break;
            default:
                show_404();

            break;
        }
    }

    private function my_company_services($services)
    {
        $output = [];
        $phoneUtil = PhoneNumberUtil::getInstance();

        foreach ($services as $service) {
            $service_id = (int) $service['id_service'];
            $service_title = cleanOutput($service['title_service']);

            //region Service
            $service_email = cleanOutput($service['email_service']);

            try {
                $service_phone = $phoneUtil->parse(trim("{$service['phone_code']} {$service['phone_service']}"));
                $service_phone_inline = $phoneUtil->format($service_phone, PhoneNumberFormat::INTERNATIONAL);
                $service_phone_link = "
                    <div class=\"links-black\">
                        <a href=\"{$phoneUtil->format($service_phone, PhoneNumberFormat::RFC3966)}\"
                            class=\"link-black txt-medium text-nowrap\"
                            title=\"Phone to {$service_phone_inline}\">
                            {$service_phone_inline}
                        </a>
                    </div>
                ";
            } catch (NumberParseException $exception) {
                $service_phone_link = '&mdash;';
            }
            $service_preview = "
                <div class=\"flex-card\">
                    <div class=\"flex-card__float\">
                        <div class=\"main-data-table__item-ttl\">
                            {$service_title}
                        </div>
                        <div class=\"main-data-table__item-ttl\">
                            <a href=\"mailto:{$service_email}\"
                                class=\"display-ib link-black txt-medium\"
                                title=\"Mail to {$service_email}\">
                                {$service_email}
                            </a>
                        </div>
                        {$service_phone_link}
                    </div>
                </div>
            ";
            //endregion Service

            //region Description
            $description = '&mdash;';
            if (!empty($service['info_service'])) {
                $description_text = cleanOutput(strLimit($service['info_service'], 300));
                $description = "
                    <div class=\"grid-text\">
                        <div class=\"grid-text__item\">
                            <div>
                                {$description_text}
                            </div>
                        </div>
                    </div>
                ";
            }
            //endregion Description

            //region Actions
            //region Edit button
            $edit_button_url = __SITE_URL . "company_services/popup_forms/edit_service/{$service_id}";
            $edit_button_text = translate('general_button_edit_text', null, true);
            $edit_button_modal_title = translate('company_services_dashboard_dt_button_edit_service_modal_title', null, true);
            $edit_button = "
                <a rel=\"edit\"
                    class=\"dropdown-item fancybox.ajax fancyboxValidateModal\"
                    data-fancybox-href=\"{$edit_button_url}\"
                    data-title=\"{$edit_button_modal_title}\">
                    <i class=\"ep-icon ep-icon_pencil\"></i>
                    <span>{$edit_button_text}</span>
                </a>
            ";
            //endregion Edit button

            //region Delete button
            $delete_button_text = translate('general_button_delete_text', null, true);
            $delete_button_message = translate('company_services_dashboard_dt_button_delete_service_message', null, true);
            $delete_button = "
                <a class=\"dropdown-item confirm-dialog\"
                    data-message=\"{$delete_button_message}\"
                    data-callback=\"deleteService\"
                    data-service=\"{$service_id}\">
                    <i class=\"ep-icon ep-icon_trash-stroke\"></i>
                    <span>{$delete_button_text}</span>
                </a>
            ";
            //endregion Delete button

            //region All button
            $all_button_text = translate('general_dt_info_all_text', null, true);
            $all_button = "
                <a class=\"dropdown-item d-none d-md-block d-lg-block d-xl-none call-function\"
                    data-callback=\"dataTableAllInfo\"
                    target=\"_blank\">
                    <i class=\"ep-icon ep-icon_info-stroke\"></i>
                    <span>{$all_button_text}</span>
                </a>
            ";
            //endregion All button

            $actions = "
                <div class=\"dropdown\">
                    <a class=\"dropdown-toggle\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\">
                        <i class=\"ep-icon ep-icon_menu-circles\"></i>
                    </a>
                    <div class=\"dropdown-menu dropdown-menu-right\">
                        {$edit_button}
                        {$delete_button}
                        {$all_button}
                    </div>
                </div>
            ";
            //endregion Actions

            $output[] = [
                'service'     => $service_preview,
                'description' => $description,
                'created_at'  => getDateFormatIfNotEmpty($service['created_at']),
                'updated_at'  => getDateFormatIfNotEmpty($service['updated_at']),
                'actions'     => $actions,
            ];
        }

        return $output;
    }

    private function show_add_service_form(PhoneCodesService $phone_codes_service)
    {
        $this->view->display(
            'new/user/seller/services/my/add_service_form_view',
            array(
                'action'      => getUrlForGroup('company_services/ajax_services_operations/add_service'),
                'phone_codes' => $phone_codes_service->getCountryCodes(),
            )
        );
    }

    private function show_edit_service_form($company_id, $service_id, PhoneCodesService $phone_codes_service)
    {
        if (
            empty($company_id)
            || empty($company = model('company')->get_company(array('id_company' => $company_id)))
        ) {
            messageInModal('The company is not found.');
        }

        try {
            $service = $this->company_services->find_company_service($service_id, $company_id);
        } catch (NotFoundException $exception) {
            messageInModal('Department was not found.');
        } catch (OwnershipException $exception) {
            messageInModal('This department does not belongs to you.');
        }

        $this->view->display(
            'new/user/seller/services/my/edit_service_form_view',
            array(
                'action'              => __SITE_URL . "company_services/ajax_services_operations/edit_service/{$service_id}",
                'phone_codes'         => $phone_codes_service->getCountryCodes(),
                'service'             => $service_id,
                'service_info'        => $service,
                'selected_phone_code' => $phone_codes_service->findAllMatchingCountryCodes(
                    !empty($service['id_phone_code']) ? (int) $service['id_phone_code'] : null,
                    !empty($service['phone_code']) ? (string) $service['phone_code'] : null,
                    !empty($company['id_country']) ? (int) $company['id_country'] : null,
                    PhoneCodesService::SORT_BY_PRIORITY
                )->first(),
            )
        );
    }

    private function create_service(array $postdata, PhoneCodesService $phone_codes_service)
    {
        //region Validation
        $validator_rules = array(
            array(
                'field' => 'title',
                'label' => translate('company_services_dashboard_modal_field_name_label_text', null, true),
                'rules' => array('required' => '', 'max_len[100]' => ''),
            ),
            array(
                'field' => 'phone_code',
                'label' => translate('company_services_dashboard_modal_field_phone_code_label_text', null, true),
                'rules' => array(
                    'required' => '',
                    function ($attr, $phone_code_id, $fail) use ($phone_codes_service) {
						if (
                            empty($phone_code_id)
                            || !model('country')->has_country_code($phone_code_id)
                            || 0 === $phone_codes_service->findAllMatchingCountryCodes($phone_code_id)->count()
                        ) {
							$fail(sprintf('Field "%s" contains unknown value.', $attr));
						}
					}
                ),
            ),
            array(
                'field' => 'phone',
                'label' => translate('company_services_dashboard_modal_field_phone_label_text', null, true),
                'rules' => array('required' => '', 'viable_phone' => '', 'max_len[60]' => ''),
            ),
            array(
                'field' => 'email',
                'label' => translate('company_services_dashboard_modal_field_email_label_text', null, true),
                'rules' => array('required' => '', 'no_whitespaces' => '', 'valid_email' => '', 'max_len[100]' => ''),
            ),
            array(
                'field' => 'text',
                'label' => translate('company_services_dashboard_modal_field_description_label_text', null, true),
                'rules' => array('required' => '', 'max_len[500]' => ''),
            ),
        );

        $phone_code = null;
        if (isset($postdata['phone']) && isset($postdata['phone_code'])) {
            /** @var null|PhoneCountryCode $phone_code */
            $phone_code = $phone_codes_service->findAllMatchingCountryCodes((int) arrayGet($postdata, 'phone_code'))->first();
            $postdata['real_phone'] = $phone_code ? "{$phone_code->getName()} {$postdata['phone']}" : $postdata['phone'];
            $validator_rules[] = array(
                'field' => 'real_phone',
                'label' => translate('company_services_dashboard_modal_field_phone_label_text', null, true),
                'rules' => array(
                    'required'    => '',
                    'max_len[60]' => '',
                    'valid_phone' => '',
                ),
            );
        }

        $this->validator->reset_postdata();
        $this->validator->clear_array_errors();
        $this->validator->validate_data = $postdata;
        $this->validator->set_rules($validator_rules);
        if (!$this->validator->validate()) {
            jsonResponse($this->validator->get_array_errors());
        }
        //endregion Validation

        $seller_id = (int) privileged_user_id();
        $company_id = (int) my_company_id();
        $phone = ltrim(preg_replace("/[^0-9]/", '', cleanInput($postdata['phone'])), '0');
        $email = cleanInput($postdata['email']);
        $title = cleanInput($postdata['title']);
        $description = cleanInput($postdata['text']);

        try {
            $service_id = $is_created = $this->company_services->create_service(
                $company_id,
                $title,
                $description,
                $email,
                $phone_code ? $phone_code : null,
                $phone
            );
        } catch (NotUniqueException $exception) {
            jsonResponse('The company department with this name already exists.');
        }

        if (!$is_created) {
            jsonResponse('Error: you cannot add this department now. Please try again later.');
        }

        model('user_statistic')->set_users_statistic(array($seller_id => array('company_services' => 1)));

        jsonResponse('Department information added successfully.', 'success', array(
            'id'          => $service_id,
            'id_company'  => $company_id,
            'phone_code'  => $phone_code ? $phone_code->getName() : null,
            'phone'       => $phone,
            'title'       => $title,
            'email'       => $email,
            'text'        => $description,
        ));
    }

    private function edit_service($company_id, $service_id, array $postdata, PhoneCodesService $phone_codes_service)
    {
        //region Validation
        $validator_rules = array(
            array(
                'field' => 'title',
                'label' => translate('company_services_dashboard_modal_field_name_label_text', null, true),
                'rules' => array('required' => '', 'max_len[100]' => ''),
            ),
            array(
                'field' => 'phone_code',
                'label' => translate('company_services_dashboard_modal_field_phone_code_label_text', null, true),
                'rules' => array(
                    'required' => '',
                    function ($attr, $phone_code_id, $fail) use ($phone_codes_service) {
						if (
                            empty($phone_code_id)
                            || !model('country')->has_country_code($phone_code_id)
                            || 0 === $phone_codes_service->findAllMatchingCountryCodes($phone_code_id)->count()
                        ) {
							$fail(sprintf('Field "%s" contains unknown value.', $attr));
						}
					}
                ),
            ),
            array(
                'field' => 'phone',
                'label' => translate('company_services_dashboard_modal_field_phone_label_text', null, true),
                'rules' => array('required' => '', 'viable_phone' => '', 'max_len[60]' => ''),
            ),
            array(
                'field' => 'email',
                'label' => translate('company_services_dashboard_modal_field_email_label_text', null, true),
                'rules' => array('required' => '', 'no_whitespaces' => '', 'valid_email' => '', 'max_len[100]' => ''),
            ),
            array(
                'field' => 'text',
                'label' => translate('company_services_dashboard_modal_field_description_label_text', null, true),
                'rules' => array('required' => '', 'max_len[500]' => ''),
            ),
            array(
                'field' => 'info',
                'label' => translate('company_services_dashboard_modal_field_service_label_text', null, true),
                'rules' => array('required' => '', 'integer' => ''),
            ),
        );

        $phone_code = null;
        if (isset($postdata['phone']) && isset($postdata['phone_code'])) {
            /** @var null|PhoneCountryCode $phone_code */
            $phone_code = $phone_codes_service->findAllMatchingCountryCodes((int) arrayGet($postdata, 'phone_code'))->first();
            $postdata['real_phone'] = $phone_code ? "{$phone_code->getName()} {$postdata['phone']}" : $postdata['phone'];
            $validator_rules[] = array(
                'field' => 'real_phone',
                'label' => translate('company_services_dashboard_modal_field_phone_label_text', null, true),
                'rules' => array(
                    'required'    => '',
                    'max_len[60]' => '',
                    'valid_phone' => '',
                ),
            );
        }

        $this->validator->reset_postdata();
        $this->validator->clear_array_errors();
        $this->validator->validate_data = $postdata;
        $this->validator->set_rules($validator_rules);
        if (!$this->validator->validate()) {
            jsonResponse($this->validator->get_array_errors());
        }
        //endregion Validation

        try {
            $this->company_services->find_company_service($service_id, $company_id);
        } catch (NotFoundException $exception) {
            jsonResponse('This department does not exist.');
        } catch (OwnershipException $exception) {
            jsonResponse("This company department doesn't belongs to you.");
        }

        $phone = ltrim(preg_replace("/[^0-9]/", '', cleanInput($postdata['phone'])), '0');
        $email = cleanInput($postdata['email']);
        $title = cleanInput($postdata['title']);
        $description = cleanInput($postdata['text']);

        try {
            $is_updated = $this->company_services->change_service(
                $service_id,
                $company_id,
                $title,
                $description,
                $email,
                $phone_code ? $phone_code : null,
                $phone
            );
        } catch (NotUniqueException $exception) {
            jsonResponse('The company department with this name already exists.');
        }

        if (!$is_updated) {
            jsonResponse('Error: you cannot update department info now. Please try again later.');
        }

        jsonResponse('Department information was updated successfully.', 'success', array(
            'id'         => $service_id,
            'text'       => $description,
            'title'      => $title,
            'email'      => $email,
            'phone'      => $phone,
            'phone_code' => $phone_code ? $phone_code->getName() : null,
        ));
    }

    private function delete_service($service_id)
    {
        $this->load->model('User_Statistic_Model', 'statistic');

        $seller_id = (int) privileged_user_id();
        $company_id = (int) my_company_id();

        try {
            $is_deleted = $this->company_services->drop_company_service($service_id, $company_id);
        } catch (NotFoundException $exception) {
            jsonResponse('Error: This department does not exist.');
        } catch (OwnershipException $exception) {
            jsonResponse("This company department doesn't belongs to you.");
        } catch (NotUniqueException $exception) {
            jsonResponse('The company department with this name already exists.');
        }

        if (!$is_deleted) {
            jsonResponse('Error: Department(s) was(were) not deleted. Please try again later.');
        }

        $this->statistic->set_users_statistic(array($seller_id => array('company_services' => -1)));

        jsonResponse('Department(s) was deleted.', 'success');
    }

    private function delete_services(array $services_ids)
    {
        if (empty($services_ids)) {
            jsonResponse('Error: Please select at least one department.');
        }

        $this->load->model('User_Statistic_Model', 'statistic');

        $seller_id = (int) privileged_user_id();
        $company_id = (int) my_company_id();
        $services = $this->company_services->get_services(array(
            'conditions' => array(
                'company'  => $company_id,
                'services' => $services_ids,
            ),
        ));
        if (empty($services)) {
            jsonResponse('Error: Please select at least one department.');
        }

        $queue = array_column($services, 'id_service');
        foreach ($services as $service) {
            if ((int) $company_id !== (int) $service['id_company']) {
                jsonResponse("This company department doesn't belongs to you.");
            }
        }

        if (!$this->company_services->delete_services($queue)) {
            jsonResponse('Error: Department(s) was(were) not deleted. Please try again later.');
        }

        $this->statistic->set_users_statistic(array($seller_id => array('company_services' => -count($queue))));

        jsonResponse('Department(s) was deleted.', 'success');
    }
}
