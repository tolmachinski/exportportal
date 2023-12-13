<?php

use App\Email\EmailFriendAboutLibrary;
use App\Filesystem\CompanyLibraryFilePathGenerator;
use App\Filesystem\FilePathGenerator;
use ExportPortal\Bridge\Mailer\Mime\RefAddress;
use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToWriteFile;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 *
 * @property \Company_Model             $company
 * @property \Seller_Library_Model      $seller_library
 * @property \TinyMVC_Load              $load
 * @property \TinyMVC_View              $view
 * @property \TinyMVC_Library_URI       $uri
 * @property \TinyMVC_Library_Session   $session
 * @property \TinyMVC_Library_Cookies   $cookies
 * @property \TinyMVC_Library_Upload    $upload
 * @property \TinyMVC_Library_validator $validator
 * @property \TinyMVC_Library_Wall      $wall
 * @property \User_Model                $user
 * @property \User_Statistic_Model      $statistic
 *
 */
class Seller_Library_Controller extends TinyMVC_Controller
{
    public function index()
    {
        show_404();
    }

    public function my()
    {
        checkIsLogged();
        checkHaveCompany();
        checkPermision('have_library');
        checkGroupExpire();

        /** @var Seller_Library_Model $sellerLibraryModel */
        $sellerLibraryModel = model(Seller_Library_Model::class);

        views(
            [
                'new/header_view',
                'new/user/seller/library/my/index_view',
                'new/footer_view'
            ],
            [
                'library_categories' => $sellerLibraryModel->get_library_categories([
                    'conditions' => [
                        'seller' => privileged_user_id(),
                    ],
                ])
            ]
        );
    }

    public function categories()
    {
        checkIsLogged();
        checkHaveCompany();
        checkPermision('have_library');
        checkGroupExpire();

        $this->view->assign(array(
            'title'       => translate('seller_library_categories_dashboard_page_title_text', null, true),
        ));

        $this->view->display('new/header_view');
        $this->view->display('new/user/seller/library/categories/index_view');
        $this->view->display('new/footer_view');
    }

    public function ajax_library_list_dt()
    {
        checkIsAjax();
        checkIsLoggedAjaxDT();
        checkHaveCompanyAjaxDT();
        checkPermisionAjaxDT('have_library');
        checkGroupExpire('dt');

        /** @var Seller_Library_Model $sellerLibraryModel */
        $sellerLibraryModel = model(Seller_Library_Model::class);

        $request = request()->request;

        $skip = $request->getInt('iDisplayStart');
        $limit = $request->getInt('iDisplayLength');

        $conditions = array_merge(
            dtConditions($request->all(), [
                ['as' => 'search',              'key' => 'keywords',                'type' => 'cleanInput|cut_str:200'],
                ['as' => 'created_from',        'key' => 'created_from',            'type' => 'concat: 00:00:00|getDateFormat:m/d/Y H:i:s, Y-m-d H:i:s'],
                ['as' => 'created_to',          'key' => 'created_to',              'type' => 'concat: 23:59:59|getDateFormat:m/d/Y H:i:s, Y-m-d H:i:s'],
                ['as' => 'updated_from',        'key' => 'updated_from',            'type' => 'concat: 00:00:00|getDateFormat:m/d/Y H:i:s, Y-m-d H:i:s'],
                ['as' => 'updated_to',          'key' => 'updated_to',              'type' => 'concat: 23:59:59|getDateFormat:m/d/Y H:i:s, Y-m-d H:i:s'],
                ['as' => 'category',            'key' => 'library_category',        'type' => 'int'],
                ['as' => 'access',              'key' => 'access',                  'type' => fn ($value) => in_array($value, ['public', 'private']) ? $value : null],
            ]),
            [
                'company' => my_company_id(),
                'seller'  => privileged_user_id(),
            ]
        );

        $order = array_column(dt_ordering($request->all(), [
            'document'             => 'title_file',
            'created_at'           => 'add_date_file',
            'updated_at'           => 'edit_date_file',
        ]), 'direction', 'column');

        $params = compact('conditions', 'order', 'limit', 'skip');
        $data['library_list'] = $sellerLibraryModel->get_library_documents($params);
        $totalDocuments = $sellerLibraryModel->count_library_documents($params);

        $output = [
            'sEcho'                => $request->getInt('sEcho'),
            'iTotalRecords'        => $totalDocuments,
            'iTotalDisplayRecords' => $totalDocuments,
            'aaData'               => [],
        ];

        if (!empty($data['library_list'])) {
            $output['aaData'] = $this->my_seller_library($data['library_list']);
        }

        jsonResponse('', 'success', $output);
    }

    public function ajax_library_categories_dt()
    {
        checkIsAjax();
        checkIsLoggedAjaxDT();
        checkHaveCompanyAjaxDT();
        checkPermisionAjaxDT('have_library');
        checkGroupExpire('dt');

        /** @var Seller_Library_Model $sellerLibraryModel */
        $sellerLibraryModel = model(Seller_Library_Model::class);

        $request = request()->request;

        $skip = $request->getInt('iDisplayStart');
        $limit = $request->getInt('iDisplayLength');

        $conditions = array_merge(
            dtConditions($request->all(), [
                ['as' => 'search',              'key' => 'keywords',                'type' => 'cleanInput|cut_str:50'],
                ['as' => 'created_from',        'key' => 'created_from',            'type' => 'concat: 00:00:00|getDateFormat:m/d/Y H:i:s, Y-m-d H:i:s'],
                ['as' => 'created_to',          'key' => 'created_to',              'type' => 'concat: 23:59:59|getDateFormat:m/d/Y H:i:s, Y-m-d H:i:s'],
                ['as' => 'updated_from',        'key' => 'updated_from',            'type' => 'concat: 00:00:00|getDateFormat:m/d/Y H:i:s, Y-m-d H:i:s'],
                ['as' => 'updated_to',          'key' => 'updated_to',              'type' => 'concat: 23:59:59|getDateFormat:m/d/Y H:i:s, Y-m-d H:i:s'],
            ]),
            [
                'company' => my_company_id(),
                'seller'  => privileged_user_id(),
            ]
        );

        $order = array_column(dt_ordering($_POST, array(
            'category'   => 'category_title',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at',
        )), 'direction', 'column');

        $params = compact('conditions', 'order', 'limit', 'skip');
        $data['categories'] = $sellerLibraryModel->get_library_categories($params);
        $totalCategories = $sellerLibraryModel->count_library_categories($params);

        $output = [
            'sEcho'                => $request->getInt('sEcho'),
            'iTotalRecords'        => $totalCategories,
            'iTotalDisplayRecords' => $totalCategories,
            'aaData'               => [],
        ];

        if (!empty($data['categories'])) {
            $output['aaData'] = $this->library_categories($data['categories']);
        }

        jsonResponse('', 'success', $output);
    }

    public function popup_forms()
    {
        checkIsAjax();
        checkIsLoggedAjaxModal();

        $this->load->model('Seller_Library_Model', 'seller_library');

        $op = $this->uri->segment(3);
        $id = (int) $this->uri->segment(4);

        switch ($op) {
            case 'add_document':
                checkHaveCompanyAjaxModal();
                checkPermisionAjaxModal('have_library');
                checkGroupExpire('modal');

                // Prepare rule for allowed file types
                $formats = explode(',', config('fileupload_document_formats', 'pdf,doc,docx,xls,xlsx,tif,tiff'));
                $mimetypes = array_filter(array_unique(array_map(
                    function ($extension) {
                        return Hoa\Mime\Mime::getMimeFromExtension($extension);
                    },
                    $formats
                )));
                $accept = implode(', ', $mimetypes);
                $formats = implode('|', $formats);
                $mimetypes = implode('|', array_map(function ($type) { return preg_quote($type); }, $mimetypes));

                $this->view->display(
                    'new/user/seller/library/my/document_form_view',
                    array(
                        'action'                   => __SITE_URL . 'seller_library/ajax_library_operation/add_document',
                        'category_url'             => __SITE_URL . 'seller_library/popup_forms/add_category?add_library=1',
                        'upload_folder'            => encriptedFolderName(),
                        'library_categories'       => $this->seller_library->get_library_categories(array(
                            'conditions' => array(
                                'seller' => privileged_user_id(),
                            ),
                        )),
                        'fileupload_max_file_size' => config('fileupload_max_document_file_size', 1024 * 1024 * 2),
                        'fileupload_limits'        => array(
                            'amount'            => 1,
                            'accept'            => $accept,
                            'formats'           => $formats,
                            'mimetypes'         => $mimetypes,
                            'filesize'          => config('fileupload_max_document_file_size', 1024 * 1024 * 2),
                            'filesize_readable' => config('fileupload_max_document_file_size_placeh', '2MB'),
                        ),
                    )
                );

            break;
            case 'edit_document':
                checkHaveCompanyAjaxModal();
                checkPermisionAjaxModal('have_library');
                checkGroupExpire('modal');

                $seller_id = privileged_user_id();
                $document_id = (int) $id;
                if (
                    empty($document_id) ||
                    empty($document = $this->seller_library->get_document($document_id))
                ) {
                    messageInModal(translate('seller_library_document_not_exist'));
                }
                if ((int) $seller_id !== (int) $document['id_seller']) {
                    messageInModal(translate('systmess_error_invalid_data'));
                }

                $this->view->display(
                    'new/user/seller/library/my/document_form_view',
                    array(
                        'category_url'        => __SITE_URL . "seller_library/popup_forms/add_category?edit_library={$document_id}",
                        'action'              => __SITE_URL . 'seller_library/ajax_library_operation/edit_document',
                        'document'            => $document,
                        'library_categories'  => $this->seller_library->get_library_categories(array(
                            'conditions' => array(
                                'seller' => $seller_id,
                            ),
                        )),
                    )
                );

            break;
            case 'add_category':
                checkHaveCompanyAjaxModal();
                checkPermisionAjaxModal('have_library');
                checkGroupExpire('modal');

                $data['action'] = __SITE_URL . 'seller_library/ajax_library_operation/add_category';
                $data['add_library'] = false;
                $data['edit_library'] = false;
                if (isset($_GET['add_library'])) {
                    $data['add_library'] = true;
                    $data['add_library_url'] = __SITE_URL . 'seller_library/popup_forms/add_document';
                }
                if (isset($_GET['edit_library'])) {
                    $data['edit_library'] = (int) cleanInput($_GET['edit_library']);
                    $data['edit_library_url'] = __SITE_URL . 'seller_library/popup_forms/edit_document/' . (int) cleanInput($_GET['edit_library']);
                }

                $this->view->display(
                    'new/user/seller/library/categories/add_form_view',
                    $data
                );

            break;
            case 'edit_category':
                checkHaveCompanyAjaxModal();
                checkPermisionAjaxModal('have_library');
                checkGroupExpire('modal');

                $category_id = (int) $id;
                if (
                    empty($category_id) ||
                    empty($category = $this->seller_library->get_library_category(
                        $category_id,
                        array(
                            'seller' => privileged_user_id(),
                        )
                    ))
                ) {
                    messageInModal(translate('systmess_error_seller_library_category_not_exist'));
                }

                $this->view->display(
                    'new/user/seller/library/categories/edit_form_view',
                    array(
                        'action'   => __SITE_URL . 'seller_library/ajax_library_operation/edit_category',
                        'category' => $category,
                    )
                );

            break;
            case 'admin_edit_document':
                checkPermisionAjaxModal('moderate_content');

                $document_id = (int) $id;
                if (
                    empty($document_id) ||
                    empty($document = $this->seller_library->get_document($document_id))
                ) {
                    messageInModal(translate("systmess_error_document_does_not_exist"));
                }

                $this->view->display('admin/directory/library/edit_document_view', array(
                    'document' => $this->seller_library->get_document($document_id),
                ));

            break;
            case 'email':
                checkPermisionAjaxModal('email_this');

                $document_id = (int) $id;
                if (
                    empty($document_id) ||
                    empty($document = $this->seller_library->get_document($document_id))
                ) {
                    messageInModal(translate('seller_library_document_not_exist'));
                }

                if ('private' === $document['type_file']) {
                    messageInModal(translate('seller_library_cannot_send_email_about_doc_message'));
                }

                $this->view->display(
                    'new/user/seller/library/popup_email_view',
                    array(
                        'action'      => __SITE_URL . 'seller_library/ajax_library_operation/email',
                        'id_document' => $document_id,
                        'max_emails'  => config('email_this_max_email_count', 10),
                    )
                );

            break;
            case 'share':
                checkPermisionAjaxModal('share_this');

                $seller_id = privileged_user_id();
                $document_id = (int) $id;
                if (
                    empty($document_id) ||
                    empty($document = $this->seller_library->get_document($document_id))
                ) {
                    messageInModal(translate('seller_library_document_not_exist'));
                }

                if ('private' === $document['type_file'] && ((int) $seller_id !== (int) $document['id_seller'])) {
                    messageInModal(translate('seller_library_cannot_share_doc_message'));
                }

                $this->view->display(
                    'new/user/seller/library/popup_share_view',
                    array(
                        'action'      => __SITE_URL . 'seller_library/ajax_library_operation/share',
                        'id_document' => $document_id,
                    )
                );

            break;
            default:
                show_404();

            break;
        }
    }

    public function ajax_library_operation()
    {
        checkIsAjax();
        checkIsLoggedAjax();

        $this->load->model('Seller_Library_Model', 'seller_library');

        $op = $this->uri->segment(3);

        switch ($op) {
            case 'add_category':
                checkHaveCompanyAjax();
                checkPermisionAjax('have_library');
                checkGroupExpire('ajax');

                $validator_rules = array(
                    array(
                        'field' => 'title',
                        'label' => translate('seller_library_categories_dashboard_modal_field_name_label_text', null, true),
                        'rules' => array('required' => '', 'max_len[50]' => ''),
                    ),
                );
                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $seller_id = privileged_user_id();
                $category_title = cleanInput($_POST['title']);
                if ($this->seller_library->has_library_category(
                    $category_title,
                    array(
                        'seller' => $seller_id,
                    )
                )) {
                    jsonResponse(translate('seller_library_cat_name_already_exists'));
                }

                if (!$this->seller_library->add_library_category(array(
                    'id_seller'      => $seller_id,
                    'category_title' => $category_title,
                ))) {
                    jsonResponse(translate('seller_library_failed_to_add_cat_message'));
                }

                jsonResponse(translate('seller_library_category_added_message'), 'success');

            break;
            case 'edit_category':
                checkHaveCompanyAjax();
                checkPermisionAjax('have_library');
                checkGroupExpire('ajax');

                $validator_rules = array(
                    array(
                        'field' => 'title',
                        'label' => translate('seller_library_categories_dashboard_modal_field_name_label_text', null, true),
                        'rules' => array('required' => '', 'max_len[50]' => ''),
                    ),
                    array(
                        'field' => 'id_category',
                        'label' => translate('seller_library_categories_dashboard_modal_field_category_label_text', null, true),
                        'rules' => array('required' => '', 'integer' => ''),
                    ),
                );
                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $seller_id = privileged_user_id();
                $category_id = (int) $_POST['id_category'];
                if (
                    empty($category_id) ||
                    empty($category = $this->seller_library->get_library_category(
                        $category_id,
                        array(
                            'seller' => privileged_user_id(),
                        )
                    ))
                ) {
                    jsonResponse(translate('systmess_error_seller_library_category_not_exist'));
                }

                $category_title = cleanInput($_POST['title']);
                if ($this->seller_library->has_library_category(
                    $category_title,
                    array(
                        'not'    => $category_id,
                        'seller' => $seller_id,
                    )
                )) {
                    jsonResponse(translate('seller_library_cat_name_already_exists'));
                }

                if (!$this->seller_library->update_library_category($category_id, array(
                    'category_title' => $category_title,
                ))) {
                    jsonResponse(translate('systmess_error_seller_library_category_update'));
                }

                jsonResponse(translate('general_all_changes_saved_message'), 'success');

            break;
            case 'delete_category':
                checkHaveCompanyAjax();
                checkPermisionAjax('have_library');
                checkGroupExpire('ajax');

                $category_id = (int) $_POST['category'];
                if (
                    empty($category_id) ||
                    empty($category = $this->seller_library->get_library_category(
                        $category_id,
                        array(
                            'seller' => privileged_user_id(),
                        )
                    ))
                ) {
                    jsonResponse(translate('systmess_error_seller_library_category_not_exist'));
                }
                if ($this->seller_library->exist_documents_in_category($category_id)) {
                    jsonResponse(translate('systmess_error_seller_library_category_delete_used_category'));
                }

                if (!$this->seller_library->delete_library_category($category_id)) {
                    jsonResponse(translate('systmess_error_seller_library_category_failed_delete'));
                }

                jsonResponse(translate('systmess_success_seller_library_category_delete'), 'success');

            break;
            case 'add_document':
                checkHaveCompanyAjax();
                checkPermisionAjax('have_library');
                checkGroupExpire('ajax');
                is_allowed('freq_allowed_companies_posts');

                $this->load->model('User_Statistic_Model', 'statistic');
                $this->load->model('Company_Model', 'company');

                $validator_rules = array(
                    array(
                        'field' => 'document',
                        'label' => translate('seller_library_dashboard_modal_field_document_label_text', null, true),
                        'rules' => array('required' => ''),
                    ),
                    array(
                        'field' => 'title',
                        'label' => translate('seller_library_dashboard_modal_field_title_label_text', null, true),
                        'rules' => array('required' => '', 'max_len[50]' => ''),
                    ),
                    array(
                        'field' => 'file_type',
                        'label' => translate('seller_library_dashboard_modal_field_document_access_type_label_text', null, true),
                        'rules' => array('required' => '', 'in[public,private]' => ''),
                    ),
                    array(
                        'field' => 'text',
                        'label' => translate('seller_library_dashboard_modal_field_document_description_label_text', null, true),
                        'rules' => array('required' => '', 'max_len[250]' => ''),
                    ),
                );
                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $seller_id = privileged_user_id();
                $company_id = my_company_id();
                $category_id = (int) $_POST['category'];
                $category_new = cleanInput($_POST['new_category']);

                if (empty($category_id) && !empty($category_new)) {

                    if ($this->seller_library->has_library_category($category_new)) {
                        jsonResponse(translate('seller_library_cat_name_already_exists'));
                    }

                    $category_id = (int) $this->seller_library->add_library_category(array(
                        'id_seller'      => $seller_id,
                        'category_title' => $category_new,
                    ));
                }

                if (!$category_id) {
                    jsonResponse(translate('seller_videos_failed_add_category_message'));
                }

                // if (
                //     empty($category_id) ||
                //     empty($category = $this->seller_library->get_library_category(
                //         $category_id,
                //         array(
                //             'seller' => $seller_id,
                //         )
                //     ))
                // ) {
                //     jsonResponse(translate('seller_library_category_not_exist'));
                // }

                $document_type = cleanInput($_POST['file_type']);
                $document_title = cleanInput($_POST['title']);
                if ($this->seller_library->has_library_document(
                    $document_title,
                    array(
                        'seller'  => $seller_id,
                        'company' => $company_id,
                    )
                )) {
                    jsonResponse(translate('seller_library_document_name_exists_message'));
                }

                $insert = array(
                    'id_seller'        => $seller_id,
                    'id_company'       => $company_id,
                    'id_category'      => $category_id,
                    'title_file'       => $document_title,
                    'type_file'        => $document_type,
                    'description_file' => cleanInput($_POST['text']),
                );

                $document = request()->request->get('document');
                $newFileName = basename($document);
                $path = CompanyLibraryFilePathGenerator::libraryPath($company_id, $newFileName);
                /** @var FilesystemProviderInterface  $storageProvider */
                $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                $publicDisk = $storageProvider->storage('public.storage');
                $tempDisk = $storageProvider->storage('temp.storage');
                $publicDisk->createDirectory(CompanyLibraryFilePathGenerator::libraryFolder($company_id));
                try{
                    $publicDisk->write($path, $tempDisk->read($document));
                }catch(UnableToWriteFile $e){
                    jsonResponse(translate('systmess_error_uploaded_file_cannot_be_empty'));
                }

                $document_id = $this->seller_library->create_document(array_merge($insert, array(
                    'path_file'       => $newFileName,
                    'extension_file'  => pathinfo($newFileName, PATHINFO_EXTENSION),
                )));
                if (empty($document_id)) {
                    jsonResponse(translate('seller_library_cannot_save_doc_now_message'));
                }

                $this->statistic->set_users_statistic(array($seller_id => array('company_posts_library' => 1)));
                if ('public' === $document_type && isset($_POST['post_wall'])) {
                    library('wall')->add(array(
                        'operation'  => 'add',
                        'type'       => 'document',
                        'id_item'    => $document_id,
                        'id_company' => $company_id,
                        'id_seller'  => $seller_id,
                    ));
                }

                //region block user content
                $seller_info = model('user')->getSimpleUser(privileged_user_id());
                if(in_array($seller_info['status'], array('blocked', 'restricted'))){
                    model('blocking')->change_blocked_users_libraries(array(
                        'blocked' => 0,
                        'users_list' => array(privileged_user_id())
                    ), array('blocked' => 2));
                }
                //endregion block user content

                jsonResponse(translate('seller_library_document_saved_message'), 'success');

            break;
            case 'edit_document':
                checkHaveCompanyAjax();
                checkPermisionAjax('have_library');
                checkGroupExpire('ajax');
                is_allowed('freq_allowed_companies_posts');

                $validator_rules = array(
                    array(
                        'field' => 'title',
                        'label' => translate('seller_library_dashboard_modal_field_title_label_text', null, true),
                        'rules' => array('required' => '', 'max_len[50]' => ''),
                    ),
                    array(
                        'field' => 'file_type',
                        'label' => translate('seller_library_dashboard_modal_field_document_access_type_label_text', null, true),
                        'rules' => array('required' => '', 'in[public,private]' => ''),
                    ),
                    array(
                        'field' => 'text',
                        'label' => translate('seller_library_dashboard_modal_field_document_description_label_text', null, true),
                        'rules' => array('required' => '', 'max_len[250]' => ''),
                    ),
                );
                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $seller_id = privileged_user_id();
                $company_id = my_company_id();
                $document_id = (int) $_POST['id'];
                if (
                    empty($document_id) ||
                    empty($document = $this->seller_library->get_document($document_id))
                ) {
                    jsonResponse(translate('seller_library_document_not_exist'));
                }
                if ((int) $seller_id !== (int) $document['id_seller']) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                $category_id = (int) $_POST['category'];

                $category_new = cleanInput($_POST['new_category']);

                if (empty($category_id) && !empty($category_new)) {

                    if ($this->seller_library->has_library_category($category_new, ['seller' => $seller_id])) {
                        jsonResponse(translate('seller_library_cat_name_already_exists'));
                    }

                    $category_id = (int) $this->seller_library->add_library_category(array(
                        'id_seller'      => $seller_id,
                        'category_title' => $category_new,
                    ));
                }

                if (!$category_id) {
                    jsonResponse(translate('seller_library_failed_to_add_cat_message'));
                }

                $document_type = cleanInput($_POST['file_type']);
                $document_title = cleanInput($_POST['title']);
                if ($this->seller_library->has_library_document(
                    $document_title,
                    array(
                        'not'     => $document_id,
                        'seller'  => $seller_id,
                        'company' => $company_id,
                    )
                )) {
                    jsonResponse(translate('seller_libarary_document_name_exists'));
                }

                $updateColumn = array(
                    'id_category'      => $category_id,
                    'title_file'       => $document_title,
                    'type_file'        => $document_type,
                    'description_file' => cleanInput($_POST['text']),
                );

                if (!$this->seller_library->update_document($document_id, $updateColumn)) {
                    jsonResponse(translate('seller_library_document_not_updated'));
                }

                if ('public' === $document_type && isset($_POST['post_wall'])) {
                    library('wall')->add(array(
                        'operation'  => 'edit',
                        'type'       => 'document',
                        'id_item'    => $document_id,
                        'id_company' => $company_id,
                        'id_seller'  => $seller_id,
                    ));
                }

                jsonResponse(translate('seller_libarary_doc_successfully_updated'), 'success', array(
                    'id_doc'         => $document_id,
                    'newTitle'       => $updateColumn['title_file'],
                    'newDescription' => truncWords($updateColumn['description_file'], 30),
                ));

            break;
            case 'delete_document':
                checkHaveCompanyAjax();
                checkPermisionAjax('have_library');
                checkGroupExpire('ajax');

                $this->load->model('User_Statistic_Model', 'statistic');

                $seller_id = privileged_user_id();
                $company_id = my_company_id();
                $document_id = (int) $_POST['document'];
                if (
                    empty($document_id) ||
                    empty($document = $this->seller_library->get_document($document_id))
                ) {
                    jsonResponse(translate('seller_library_document_not_exist'));
                }
                if (((int) $company_id !== (int) $document['id_company'])) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                $path = CompanyLibraryFilePathGenerator::libraryPath($company_id, $document['path_file']);
                /** @var FilesystemProviderInterface  $storageProvider */
                $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                $publicDisk = $storageProvider->storage('public.storage');
                try {
                    $publicDisk->delete($path);
                }catch (UnableToDeleteFile $e){
                    //silent fail
                }

                if (!$this->seller_library->delete_document($document_id)) {
                    jsonResponse(translate('seller_library_cannot_delete_now'));
                }

                library('wall')->remove(array(
                    'type'       => 'document',
                    'id_item'    => $document_id
                ));

                $this->statistic->set_users_statistic(array($seller_id => array('company_posts_library' => -1)));

                jsonResponse(translate('seller_library_document_successfully_deleted'), 'success', array(
                    'document' => $document_id,
                ));

            break;
            case 'delete':
                checkHaveCompanyAjax();
                checkPermisionAjax('have_library');
                checkGroupExpire('ajax');

                $this->load->model('User_Statistic_Model', 'statistic');

                $documents_ids = array_map('intval', explode(',', cleanInput($_POST['id'])));
                if (empty($documents_ids)) {
                    jsonResponse(translate('seller_library_at_least_one_doc'));
                }

                $seller_id = privileged_user_id();
                $company_id = my_company_id();
                $documents = $this->seller_library->get_library_documents(array(
                    'conditions' => array(
                        'seller'    => $seller_id,
                        'company'   => $company_id,
                        'documents' => $documents_ids,
                    ),
                ));
                if (empty($documents)) {
                    jsonResponse(translate('seller_library_at_least_one_doc'));
                }

                $delete_list = array_column($documents, 'id_file');
                /** @var FilesystemProviderInterface  $storageProvider */
                $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                $publicDisk = $storageProvider->storage('public.storage');
                foreach ($documents as $document) {
                    if ((int) $company_id !== (int) $document['id_company']) {
                        jsonResponse(translate('seller_library_document_not_yours'));
                    }
                    $path = CompanyLibraryFilePathGenerator::libraryPath($company_id, $document['path_file']);
                    try {
                        $publicDisk->delete($path);
                    }catch (UnableToDeleteFile $e){
                        //silent fail
                    }
                }

                if (!$this->seller_library->delete_document($delete_list)) {
                    jsonResponse(translate('seller_library_doc_not_deleted'));
                }

                library('wall')->remove(array(
                    'type'       => 'document',
                    'id_item'    => $delete_list
                ));

                $this->statistic->set_users_statistic(array($seller_id => array('company_posts_library' => -count($delete_list))));
                if (!empty($_POST['multiple'])) {
                    jsonResponse(translate('seller_library_docs_deleted'), 'success');
                }

                jsonResponse(translate('seller_library_doc_not_deleted'), 'success');

            break;
            case 'email':
                checkPermisionAjax('email_this');
                is_allowed('freq_allowed_send_email_to_user');

                $max_emails = config('email_this_max_email_count');
                $validator_rules = array(
                    array(
                        'field' => 'id',
                        'label' => translate('seller_library_public_modal_field_document_label_text', null, true),
                        'rules' => array('required' => '', 'natural' => ''),
                    ),
                    array(
                        'field' => 'emails',
                        'label' => translate('general_modal_send_mail_field_addresses_label_text', null, true),
                        'rules' => array('required' => '', 'no_whitespaces' => '', 'valid_emails' => '', "max_emails_count[{$max_emails}]" => ''),
                    ),
                    array(
                        'field' => 'message',
                        'label' => translate('general_modal_send_mail_field_message_label_text', null, true),
                        'rules' => array('required' => '', 'max_len[1000]' => ''),
                    ),
                );

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $filteredEmails = filter_email($_POST['emails']);

                if (empty($filteredEmails)) {
                    jsonResponse(translate('seller_library_one_valid_email_message'));
                }

                $document_id = (int) $_POST['id'];
                if (
                    empty($document_id) ||
                    empty($document = $this->seller_library->get_document($document_id))
                ) {
                    jsonResponse(translate('seller_library_document_not_exist'));
                }

                if ('private' === $document['type_file']) {
                    jsonResponse(translate('seller_library_cannot_send_email_about_doc_message'));
                }

                $companyId = (int) $document['id_company'];
                if (
                    empty($companyId) ||
                    empty($company = model(Company_Model::class)->get_company(['id_company' => $document['id_company']]))
                ) {
                    jsonResponse(translate('seller_library_info_not_correct_message'));
                }

                $userName = user_name_session();

                try {
                    /** @var MailerInterface $mailer */
                    $mailer = $this->getContainer()->get(MailerInterface::class);
                    $mailer->send(
                        (new EmailFriendAboutLibrary($userName, cleanInput(request()->request->get('message')), $company, $document))
                            ->to(...array_map(fn (string $to) => new Address($to), $filteredEmails))
                            ->subjectContext(['[userName]' => $userName])
                    );
                } catch (\Throwable $th) {
                    jsonResponse(translate('email_has_not_been_sent'));
                }

                jsonResponse(translate('seller_library_email_sent'), 'success');

            break;
            case 'share':
                checkPermisionAjax('share_this');
                is_allowed('freq_allowed_send_email_to_user');

                $validator_rules = array(
                    array(
                        'field' => 'id',
                        'label' => translate('seller_library_public_modal_field_document_label_text', null, true),
                        'rules' => array('required' => '', 'natural' => ''),
                    ),
                    array(
                        'field' => 'message',
                        'label' => translate('general_modal_share_field_message_label_text', null, true),
                        'rules' => array('required' => '', 'max_len[1000]' => ''),
                    ),
                );
                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $document_id = (int) $_POST['id'];
                if (
                    empty($document_id) ||
                    empty($document = $this->seller_library->get_document($document_id))
                ) {
                    jsonResponse(translate('seller_library_document_not_exist'));
                }

                $companyId = (int) $document['id_company'];

                if (
                    empty($companyId) ||
                    empty($company = model(Company_Model::class)->get_company(['id_company' => $document['id_company']]))
                ) {
                    jsonResponse(translate('seller_library_info_not_correct_message'));
                }

                $filteredEmails = model(Followers_Model::class)->getFollowersEmails(privileged_user_id());

                if (empty($filteredEmails)) {
                    jsonResponse(translate('seller_library_no_followers_message'));
                }

                $userName = user_name_session();

                try {
                    /** @var MailerInterface $mailer */
                    $mailer = $this->getContainer()->get(MailerInterface::class);
                    $mailer->send(
                        (new EmailFriendAboutLibrary($userName, cleanInput(request()->request->get('message')), $company, $document))
                            ->to(...array_map(fn (int $id, string $to) => new RefAddress((string) $id, new Address($to)), array_column($filteredEmails, 'idu', 'idu'), array_column($filteredEmails, 'email', 'email')))
                            ->subjectContext(['[userName]' => $userName])
                    );
                } catch (\Throwable $th) {
                    jsonResponse(translate('email_has_not_been_sent'));
                }

                jsonResponse(translate('seller_library_email_sent'), 'success');

            break;
            case 'get_categories':
                checkHaveCompanyAjax();
                checkPermisionAjax('have_pictures');
                checkGroupExpire('ajax');

                jsonResponse('', 'success', array(
                    'categories' => $this->seller_library->get_library_categories(array(
                        'conditions' => array(
                            'seller' => privileged_user_id(),
                        ),
                    )),
                ));

            break;
            default:
                show_404();

            break;
        }
    }

    public function ajax_seller_upload_file()
    {
        checkIsAjax();
        checkIsLoggedAjax();
        checkHaveCompanyAjax();
        checkPermisionAjax('have_library');
        checkGroupExpire('ajax');

        if (empty($_FILES['files'])) {
            jsonResponse(translate('seller_library_select_file_to_upload'));
        }
        if (count($_FILES['files']['name']) > 1) {
            jsonResponse(translate('seller_library_cannot_upload_more_than_one'));
        }

        $session_id = id_session();
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = request()->files->get('files')[0];
        if (null === $uploadedFile) {
			jsonResponse(translate('validation_image_required'));
		}
        if (is_array($uploadedFile) || !$uploadedFile->isValid()) {
			jsonResponse(translate('validation_invalid_file_provided'));
		}
        /** @var FilesystemProviderInterface  $storageProvider */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $pathToFile = FilePathGenerator::uploadedFile(bin2hex(random_bytes(16)));
        $tempPrefixer = $storageProvider->prefixer('temp.storage');
        $tempStorage = $storageProvider->storage('temp.storage');
        $result = array();
        $files = $this->upload->upload_files_new([
            'data_files' => $_FILES['files'],
            'path'       => $tempPrefixer->prefixPath($pathToFile),
            'rules'      => [
                'size'   => config('fileupload_max_document_file_size', 1024 * 1024 * 2),
                'format' => config('fileupload_document_formats', 'pdf,doc,docx,xls,xlsx,tif,tiff'),
            ],
        ]);

        if (!empty($files['errors'])) {
            jsonResponse($files['errors']);
        }

        foreach ($files as $file) {
            $result['files'][] = array(
                'fullPath' => $tempStorage->url("{$pathToFile}/{$file['new_name']}"),
                'path'     => "{$pathToFile}/{$file['new_name']}",
                'name'     => $file['new_name'],
                'type'     => $file['type'],
            );
        }

        jsonResponse('', 'success', $result);
    }

    public function ajax_seller_delete_files()
    {
        checkIsAjax();
        checkIsLoggedAjax();
        checkHaveCompanyAjax();
        checkPermisionAjax('have_library');
        checkGroupExpire('ajax');

        if (empty($_POST['file'])) {
            jsonResponse(translate('seller_library_incorrect_file_name'));
        }

        /** @var FilesystemProviderInterface  $storageProvider */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $tempDisk = $storageProvider->storage('temp.storage');

        $filename = cleanInput($_POST['file']);
        if (!$tempDisk->fileExists($filename)) {
            jsonResponse(translate('seller_library_file_not_exist'));
        }
        try{
            $tempDisk->delete($filename);
        } catch (UnableToDeleteFile $e) {
            jsonResponse(translate('seller_library_failed_to_delete_file'));
        }

        jsonResponse('', 'success');
    }

    private function my_seller_library($documents)
    {
        $output = array();
        foreach ($documents as $document) {
            $document_id = (int) $document['id_file'];
            $company_id = (int) $document['id_company'];
            $document_title = cleanOutput($document['title_file']);

            //region Document
            $document_file = $document['path_file'];
            $document_extension = $document['extension_file'];
            $document_extension_suffix = mb_strtoupper($document['extension_file']);
            $document_access_type = ucfirst($document['type_file']);
            $document_filepath = CompanyLibraryFilePathGenerator::libraryPath($company_id,$document_file);
            $document_size_suffix = file_exists($document_filepath) ? fileSizeSuffix($document_filepath) : '0KB';
            $document_category_name = cleanOutput($document['category_title']);
            $document_url = getCompanyUrl($document) . "/document/{$document_id}-" . strForUrl($document_title);
            $document_preview = "
                <div class=\"flex-card\">
                    <div class=\"flex-card__fixed main-data-table__item-img image-card\">
                        <span class=\"link\">
                            <div
                                class=\"img-b h-100 icon-files-{$document_extension}-middle\"
                                " . addQaUniqueIdentifier('seller-library-my__table_document-img') . "
                            >
                            </div>
                        </span>
                    </div>
                    <div class=\"flex-card__float\">
                        <div class=\"main-data-table__item-ttl\">
                            <a href=\"{$document_url}\"
                                class=\"display-ib link-black txt-medium\"
                                title=\"{$document_title}\"
                                target=\"_blank\"
                                " . addQaUniqueIdentifier('seller-library-my__table_document-title') . "
                            >
                                {$document_title}
                            </a>
                        </div>
                        <div class=\"links-black\" " . addQaUniqueIdentifier('seller-library-my__table_document-category') . ">{$document_category_name}</div>
                        <div class=\"txt-gray\" " . addQaUniqueIdentifier('seller-library-my__table_document-access-type') . ">{$document_access_type}</div>
                        <div
                            class=\"txt-gray fs-14\"
                            " . addQaUniqueIdentifier('seller-library-my__table_document-extension') . "
                        >
                            {$document_extension_suffix}, {$document_size_suffix}
                        </div>
                    </div>
                </div>
            ";
            //endregion Document

            //region Description
            $description = '&mdash;';
            $description_text = $document['description_file'];
            if (!empty($description_text)) {
                $description = "
                    <div class=\"grid-text\">
                        <div class=\"grid-text__item\">
                            <div " . addQaUniqueIdentifier('seller-library-my__table_document-description') . ">
                                {$description_text}
                            </div>
                        </div>
                    </div>
                ";
            }
            //endregion Description

            //region Actions
            //region Edit button
            $edit_button = null;
            $edit_button_url = __SITE_URL . "seller_library/popup_forms/edit_document/{$document_id}";
            $edit_button_text = translate('general_button_edit_text', null, true);
            $edit_button_modal_title = translate('seller_library_dt_button_edit_document_modal_title', null, true);
            $edit_button = "
                <a rel=\"edit\"
                    class=\"dropdown-item fancybox.ajax fancyboxValidateModal\"
                    data-fancybox-href=\"{$edit_button_url}\"
                    data-title=\"{$edit_button_modal_title}\"
                    " . addQaUniqueIdentifier('seller-library-my__table_document-actions_dropdown-menu_edit-btn') . "
                >
                    <i class=\"ep-icon ep-icon_pencil\"></i>
                    <span>{$edit_button_text}</span>
                </a>
            ";
            //endregion Edit button

            //region Delete button
            $delete_button = null;
            $delete_button_text = translate('general_button_delete_text', null, true);
            $delete_button_message = translate('seller_library_dt_button_delete_document_message', null, true);
            $delete_button = "
                <a class=\"dropdown-item confirm-dialog\"
                    data-message=\"{$delete_button_message}\"
                    data-callback=\"deleteDocument\"
                    data-document=\"{$document_id}\"
                    " . addQaUniqueIdentifier('seller-library-my__table_document-actions_dropdown-menu_delete-btn') . "
                >
                    <i class=\"ep-icon ep-icon_trash-stroke\"></i>
                    <span>{$delete_button_text}</span>
                </a>
            ";
            //endregion Delete button

            //region All button
            $all_button_text = translate('general_dt_info_all_text');
            $all_button = "
                <a class=\"dropdown-item d-none d-md-block d-lg-block d-xl-none call-function\"
                    data-callback=\"dataTableAllInfo\"
                    target=\"_blank\"
                    " . addQaUniqueIdentifier('seller-library-my__table_document-actions_dropdown-menu_all-info-btn') . "
                >
                    <i class=\"ep-icon ep-icon_info-stroke\"></i>
                    <span>{$all_button_text}</span>
                </a>
            ";
            //endregion All button

            $actions = "
                <div class=\"dropdown\">
                    <a
                        class=\"dropdown-toggle\"
                        data-toggle=\"dropdown\"
                        aria-haspopup=\"true\"
                        aria-expanded=\"false\"
                        " . addQaUniqueIdentifier('seller-library-my__table_document-actions_dropdown-btn') . "
                    >
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

            $output[] = array(
                'document'    => $document_preview,
                'description' => $description,
                'created_at'  => getDateFormatIfNotEmpty($document['add_date_file']),
                'updated_at'  => getDateFormatIfNotEmpty($document['edit_date_file']),
                'actions'     => $actions,
            );
        }

        return $output;
    }

    private function library_categories($categories)
    {
        $output = array();
        foreach ($categories as $category) {
            $category_id = (int) $category['id_category'];
            $category_title = cleanOutput($category['category_title']);

            //region Category
            $category_preview = "
                <div class=\"grid-text\">
                    <div class=\"grid-text__item\">
                        <div " . addQaUniqueIdentifier('seller-library-categories__table_category-title') . ">
                            {$category_title}
                        </div>
                    </div>
                </div>
            ";
            //endregion Category

            //region Actions
            //region Edit button
            $edit_button = null;
            $edit_button_url = __SITE_URL . "seller_library/popup_forms/edit_category/{$category_id}";
            $edit_button_text = translate('general_button_edit_text', null, true);
            $edit_button_modal_title = translate('seller_library_categories_dt_button_edit_category_modal_title', null, true);
            $edit_button = "
                <a rel=\"edit\"
                    class=\"dropdown-item fancybox.ajax fancyboxValidateModal\"
                    data-fancybox-href=\"{$edit_button_url}\"
                    data-title=\"{$edit_button_modal_title}\"
                    " . addQaUniqueIdentifier('seller-library-categories__table_category-actions_dropdown-menu_edit-btn') . "
                >
                    <i class=\"ep-icon ep-icon_pencil\"></i>
                    <span>{$edit_button_text}</span>
                </a>
            ";
            //endregion Edit button

            //region Delete button
            $delete_button = null;
            $delete_button_text = translate('general_button_delete_text', null, true);
            $delete_button_message = translate('seller_library_categories_dt_button_delete_category_message', null, true);
            $delete_button = "
                <a class=\"dropdown-item confirm-dialog\"
                    data-message=\"{$delete_button_message}\"
                    data-callback=\"deleteCategory\"
                    data-category=\"{$category_id}\"
                    " . addQaUniqueIdentifier('seller-library-categories__table_category-actions_dropdown-menu_delete-btn') . "
                >
                    <i class=\"ep-icon ep-icon_trash-stroke\"></i>
                    <span>{$delete_button_text}</span>
                </a>
            ";
            //endregion Delete button

            //region All button
            $all_button_text = translate('general_dt_info_all_text');
            $all_button = "
                <a class=\"dropdown-item d-none d-md-block d-lg-block d-xl-none call-function\"
                    data-callback=\"dataTableAllInfo\"
                    target=\"_blank\"
                    " . addQaUniqueIdentifier('seller-library-categories__table_category-actions_dropdown-menu_all-info-btn') . "
                >
                    <i class=\"ep-icon ep-icon_info-stroke\"></i>
                    <span>{$all_button_text}</span>
                </a>
            ";
            //endregion All button

            $actions = "
                <div class=\"dropdown\">
                    <a
                        class=\"dropdown-toggle\"
                        data-toggle=\"dropdown\"
                        aria-haspopup=\"true\"
                        aria-expanded=\"false\"
                        " . addQaUniqueIdentifier('seller-library-categories__table_category-actions_dropdown-btn') . "
                    >
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

            $output[] = array(
                'category'    => $category_preview,
                'created_at'  => getDateFormatIfNotEmpty($category['created_at']),
                'updated_at'  => getDateFormatIfNotEmpty($category['updated_at']),
                'actions'     => $actions,
            );
        }

        return $output;
    }
}
