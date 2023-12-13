<?php

use App\Common\Contracts\Media\CompanyPhotosThumb;
use App\Common\DependencyInjection\ServiceLocator\LibraryLocator;
use App\Common\Validation\ConstraintViolationInterface;
use App\Common\Validation\ValidationException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Email\EmailFriendAboutPicture;
use App\Filesystem\CompanyPhotosFilePathGenerator;
use App\Filesystem\FilePathGenerator;
use ExportPortal\Bridge\Mailer\Mime\RefAddress;
use ExportPortal\Contracts\Filesystem\Exception\ReadException;
use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;
use TinyMVC_Library_Image_intervention as LegacyImageHandler;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToWriteFile;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

/**
 * Seller Pictures controller.
 *
 * @property \Company_Model             $company
 * @property \Seller_Pictures_Model     $seller_pictures
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
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 */
class Seller_Pictures_Controller extends TinyMVC_Controller
{
    public function index()
    {
        show_404();
    }

    public function my()
    {
        checkIsLogged();
        checkHaveCompany();
        checkPermision('have_pictures');
        checkGroupExpire();

        $this->load->model('Seller_Pictures_Model', 'seller_pictures');

        $this->view->assign(array(
            'pictures_categories' => $this->seller_pictures->get_pictures_categories(array(
                'conditions' => array(
                    'company' => my_company_id(),
                ),
            )),
        ));
        $this->view->display('new/header_view');
        $this->view->display('new/user/seller/pictures/my/index_view');
        $this->view->display('new/footer_view');
    }

    public function categories()
    {
        checkIsLogged();
        checkHaveCompany();
        checkPermision('have_pictures');
        checkGroupExpire();

        $this->view->assign(array(
            'title'       => translate('seller_pictures_categories_dashboard_title_text', null, true),
            'breadcrumbs' => $this->breadcrumbs,
        ));
        $this->view->display('new/header_view');
        $this->view->display('new/user/seller/pictures/categories/index_view');
        $this->view->display('new/footer_view');
    }

    public function popup_forms()
    {
        checkIsAjax();
        checkIsLoggedAjaxModal();

        $this->load->model('Seller_Pictures_Model', 'seller_pictures');

        $name = $this->uri->segment(5);
        $op = $this->uri->segment(3);
        $id = (int) $this->uri->segment(4);

        switch ($op) {
            case 'edit_comment':
                checkPermisionAjaxModal('write_comments');

                $comment_id = (int) $id;
                if (
                    empty($comment_id) ||
                    empty($comment = $this->seller_pictures->get_comment($comment_id))
                ) {
                    messageInModal(translate('general_comment_not_exist_message'));
                }
                if ((int) $comment['id_user'] !== (int) privileged_user_id()) {
                    messageInModal(translate('general_no_permission_message'));
                }

                $this->view->display(
                    'new/user/seller/pictures/comment_edit_form_view',
                    array(
                        'action'  => __SITE_URL . 'seller_pictures/ajax_pictures_operation/edit_comment',
                        'comment' => $comment
                    )
                );

            break;
            case 'add_comment_reply':
                messageInModal(translate('seller_pictures_feature_inaccessible'));

                checkPermisionAjaxModal('write_comments');

                $picture_id = (int) $id;
                if (
                    empty($picture_id) ||
                    !$this->seller_pictures->exist_picture($picture_id)
                ) {
                    messageInModal(translate('seller_pictures_not_exist_photo'));
                }
                $comment_id = (int) $this->uri->segment(5);
                if (
                    empty($comment_id) ||
                    empty($comment = $this->seller_pictures->get_comment($comment_id))
                ) {
                    messageInModal(translate('general_comment_not_exist_message'));
                }

                $this->view->display(
                    'new/user/seller/pictures/comment_add_form_view',
                    array(
                        'action'     => __SITE_URL . 'seller_pictures/ajax_pictures_operation/add_comment',
                        'id_photo'   => $picture_id,
                        'id_comment' => $comment_id,
                    )
                );

            break;
            case 'add_comment':
                checkPermisionAjaxModal('write_comments');

                $picture_id = (int) $id;
                if (
                    empty($picture_id) ||
                    !$this->seller_pictures->exist_picture($picture_id)
                ) {
                    messageInModal(translate('seller_pictures_not_exist_photo'));
                }

                $this->view->display(
                    'new/user/seller/pictures/comment_add_form_view',
                    array(
                        'action'   => __SITE_URL . 'seller_pictures/ajax_pictures_operation/add_comment',
                        'id_photo' => $picture_id
                    )
                );

            break;
            case 'email':
                checkPermisionAjaxModal('email_this');

                $picture_id = (int) $id;
                if (
                    empty($picture_id) ||
                    !$this->seller_pictures->exist_picture($picture_id)
                ) {
                    messageInModal(translate('seller_pictures_not_exist_picture'));
                }

                $this->view->display(
                    'new/user/seller/pictures/popup_email_view',
                    array(
                        'action'     => __SITE_URL . 'seller_pictures/ajax_pictures_operation/email',
                        'id_picture' => $picture_id,
                        'max_emails' => config('email_this_max_email_count', 10),
                    )
                );

            break;
            case 'share':
                checkPermisionAjaxModal('share_this');

                $picture_id = (int) $id;
                if (
                    empty($picture_id) ||
                    !$this->seller_pictures->exist_picture($picture_id)
                ) {
                    messageInModal(translate('seller_pictures_not_exist_picture'));
                }

                $this->view->display(
                    'new/user/seller/pictures/popup_share_view',
                    array(
                        'action'     => __SITE_URL . 'seller_pictures/ajax_pictures_operation/share',
                        'id_picture' => $picture_id,
                    )
                );

            break;
            case 'add_picture':
                checkHaveCompanyAjaxModal();
                checkPermisionAjaxModal('have_pictures');
                checkGroupExpire('modal');

                // Prepare rule for allowed file types
                // $formats = explode(',', config('fileupload_image_formats', 'jpg,jpeg,png,gif,bmp'));
                // $mimetypes = array_filter(array_unique(array_map(
                //     function ($extension) {
                //         return Hoa\Mime\Mime::getMimeFromExtension($extension);
                //     },
                //     $formats
                // )));
                // $accept = implode(', ', $mimetypes);
                // $formats = implode('|', $formats);
                // $mimetypes = implode('|', array_map(function ($type) { return preg_quote($type); }, $mimetypes));

                $data = array(
                    'action'                   => __SITE_URL . 'seller_pictures/ajax_pictures_operation/add_one_picture',
                    // 'category_url'             => __SITE_URL . 'seller_pictures/popup_forms/add_category?add_picture=1',

                    'pictures_categories'      => $this->seller_pictures->get_pictures_categories(array(
                        'conditions' => array(
                            'company' => my_company_id(),
                        ),
                    )),
                    // 'fileupload_max_file_size' => config('fileupload_max_file_size', 1024 * 1024 * 10),
                    // 'fileupload_limits'        => array(
                    //     'amount'              => 1,
                    //     'accept'              => $accept,
                    //     'formats'             => $formats,
                    //     'mimetypes'           => $mimetypes,
                    //     'image_size'          => config('fileupload_max_file_size', 1024 * 1024 * 10),
                    //     'image_size_readable' => config('fileupload_max_file_size_placeholder', '10MB'),
                    //     'image_width'         => config('seller_picture_min_width', 800),
                    //     'image_height'        => config('seller_picture_min_height', 450),
                    // ),
                );

                $module_photos = 'companies.photos';
                $mime_properties = getMimePropertiesFromFormats(config("img.{$module_photos}.rules.format"));

                $data['upload_folder'] = encriptedFolderName();
                $data['fileupload'] = array(
                    'limits'                => array(
                        'amount'            => array(
                            'total'         => (int) config("img.{$module_photos}.limit"),
                            'current'       => 0,
                        ),
                        'accept'            => arrayGet($mime_properties, 'accept'),
                        'formats'           => arrayGet($mime_properties, 'formats'),
                        'mimetypes'         => arrayGet($mime_properties, 'mimetypes'),
                    ),
                    'rules'             => config("img.{$module_photos}.rules"),
                    'url'       => array(
                        'upload' => __SITE_URL . "seller_pictures/ajax_seller_upload_photo/".$data['upload_folder'],
                        'delete' => __SITE_URL . "seller_pictures/ajax_seller_delete_photo/".$data['upload_folder'],
                    ),
                );

                $this->view->assign($data);
                $this->view->display('new/user/seller/pictures/my/pictures_form_view');

            break;
            case 'edit_picture':
                checkHaveCompanyAjaxModal();
                checkPermisionAjaxModal('have_pictures');
                checkGroupExpire('modal');

                $picture_id = (int) $id;
                if (
                    empty($picture_id) ||
                    empty($picture = $this->seller_pictures->get_picture($picture_id))
                ) {
                    messageInModal(translate('seller_pictures_not_exist_picture'));
                }
                if ((int) my_company_id() !== (int) $picture['id_company']) {
                    messageInModal(translate('general_no_rights_message'));
                }

                $this->view->display(
                    'new/user/seller/pictures/my/pictures_form_view',
                    array(
                        // 'category_url'        => __SITE_URL . "seller_pictures/popup_forms/add_category?edit_picture={$picture_id}",
                        'action'              => __SITE_URL . 'seller_pictures/ajax_pictures_operation/edit_picture',
                        'picture'             => $picture,
                        'pictures_categories' => $this->seller_pictures->get_pictures_categories(array(
                            'conditions' => array(
                                'company' => my_company_id(),
                            ),
                        )),
                    )
                );

            break;
            case 'add_category':
                checkHaveCompanyAjaxModal();
                checkPermisionAjaxModal('have_pictures');
                checkGroupExpire('modal');

                $data['action'] = __SITE_URL . 'seller_pictures/ajax_pictures_operation/add_category';
                $data['add_picture'] = false;
                $data['edit_picture'] = false;
                if (isset($_GET['add_picture'])) {
                    $data['add_picture'] = true;
                    $data['add_picture_url'] = __SITE_URL . 'seller_pictures/popup_forms/add_picture';
                }
                if (isset($_GET['edit_picture'])) {
                    $data['edit_picture'] = (int) cleanInput($_GET['edit_picture']);
                    $data['edit_picture_url'] = __SITE_URL . 'seller_pictures/popup_forms/edit_picture/' . (int) cleanInput($_GET['edit_picture']);
                }

                $this->view->display(
                    'new/user/seller/pictures/categories/add_form_view',
                    $data
                );

            break;
            case 'edit_category':
                checkHaveCompanyAjaxModal();
                checkPermisionAjaxModal('have_pictures');
                checkGroupExpire('modal');

                $category_id = (int) $id;
                if (
                    empty($category_id) ||
                    empty($category = $this->seller_pictures->get_pictures_category(
                        $category_id,
                        array(
                            'company' => my_company_id(),
                        )
                    ))
                ) {
                    messageInModal(translate('seller_pictures_category_not_exist_message'));
                }

                $this->view->display(
                    'new/user/seller/pictures/categories/edit_form_view',
                    array(
                        'action'   => __SITE_URL . 'seller_pictures/ajax_pictures_operation/edit_category',
                        'category' => $category,
                    )
                );

            break;
            default:
                messageInModal(translate('seller_pictures_path_not_found'));

            break;
        }
    }

    public function ajax_photo_list_dt()
    {
        checkIsAjax();
        checkIsLoggedAjaxDT();
        checkHaveCompanyAjaxDT();
        checkPermisionAjaxDT('have_pictures');
        checkGroupExpire('dt');

        /** @var Seller_Pictures_Model $sellerPicturesModel */
        $sellerPicturesModel = model(Seller_Pictures_Model::class);

        $request = request()->request;

        $skip = $request->getInt('iDisplayStart');
        $limit = $request->getInt('iDisplayLength');

        $conditions = array_merge(
            dtConditions($request->all(), [
                ['as' => 'search',              'key' => 'keywords',                'type' => 'cleanInput|cut_str:200'],
                ['as' => 'created_from',        'key' => 'created_from',            'type' => 'concat: 00:00:00|getDateFormat:m/d/Y H:i:s,Y-m-d H:i:s'],
                ['as' => 'created_to',          'key' => 'created_to',              'type' => 'concat: 23:59:59|getDateFormat:m/d/Y H:i:s,Y-m-d H:i:s'],
                ['as' => 'updated_from',        'key' => 'updated_from',            'type' => 'concat: 00:00:00|getDateFormat:m/d/Y H:i:s,Y-m-d H:i:s'],
                ['as' => 'updated_to',          'key' => 'updated_to',              'type' => 'concat: 23:59:59|getDateFormat:m/d/Y H:i:s,Y-m-d H:i:s'],
                ['as' => 'category',            'key' => 'pictures_category',       'type' => 'int'],
            ]),
            [
                'company' => my_company_id(),
                'seller'  => privileged_user_id(),
            ]
        );

        $order = array_column(dt_ordering($request->all(), [
            'picture'    => 'title_photo',
            'created_at' => 'add_date_photo',
            'updated_at' => 'edit_date_photo',
        ]), 'direction', 'column');

        $params = compact('conditions', 'order', 'limit', 'skip');
        $total = $sellerPicturesModel->count_pictures($params);
        $pictures = $sellerPicturesModel->get_pictures($params);

        $output = [
            'sEcho'                => $request->getInt('sEcho'),
            'iTotalRecords'        => $total,
            'iTotalDisplayRecords' => $total,
            'aaData'               => [],
        ];

        if (!empty($pictures)) {
            $output['aaData'] = $this->my_seller_pictures($pictures);
        }

        jsonResponse('', 'success', $output);
    }

    public function ajax_pictures_categories_dt()
    {
        checkIsAjax();
        checkIsLoggedAjaxDT();
        checkHaveCompanyAjaxDT();
        checkPermisionAjaxDT('have_pictures');
        checkGroupExpire('dt');

        /** @var Seller_Pictures_Model $sellerPicturesModel */
        $sellerPicturesModel = model(Seller_Pictures_Model::class);

        $request = request()->request;

        $skip = $request->getInt('iDisplayStart');
        $limit = $request->getInt('iDisplayLength');

        $conditions = array_merge(
            dtConditions($request->all(), [
                ['as' => 'search',              'key' => 'keywords',                'type' => 'cleanInput|cut_str:50'],
                ['as' => 'created_from',        'key' => 'created_from',            'type' => 'concat: 00:00:00|getDateFormat:m/d/Y H:i:s,Y-m-d H:i:s'],
                ['as' => 'created_to',          'key' => 'created_to',              'type' => 'concat: 23:59:59|getDateFormat:m/d/Y H:i:s,Y-m-d H:i:s'],
                ['as' => 'updated_from',        'key' => 'updated_from',            'type' => 'concat: 00:00:00|getDateFormat:m/d/Y H:i:s,Y-m-d H:i:s'],
                ['as' => 'updated_to',          'key' => 'updated_to',              'type' => 'concat: 23:59:59|getDateFormat:m/d/Y H:i:s,Y-m-d H:i:s'],
            ]),
            [
                'company' => my_company_id(),
                'seller'  => privileged_user_id(),
            ]
        );

        $order = array_column(dt_ordering($request->all(), [
            'category'   => 'category_title',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at',
        ]), 'direction', 'column');

        $params = compact('conditions', 'order', 'limit', 'skip');
        $total = $sellerPicturesModel->count_pictures_categories($params);
        $categories = $sellerPicturesModel->get_pictures_categories($params);

        $output = [
            'sEcho'                => $request->getInt('sEcho'),
            'iTotalRecords'        => $total,
            'iTotalDisplayRecords' => $total,
            'aaData'               => [],
        ];

        if (!empty($categories)) {
            $output['aaData'] = $this->picture_categories($categories);
        }

        jsonResponse('', 'success', $output);
    }

    public function ajax_pictures_operation()
    {
        checkIsAjax();
        checkIsLoggedAjax();

        /** @var Seller_Pictures_Model $sellerPicturesModel */
        $sellerPicturesModel = model(Seller_Pictures_Model::class);

        $this->load->model('Seller_Pictures_Model', 'seller_pictures');

        $op = $this->uri->segment(3);
        switch ($op) {
            case 'add_category':
                checkHaveCompanyAjax();
                checkPermisionAjax('have_pictures');
                checkGroupExpire('ajax');

                $validator_rules = array(
                    array(
                        'field' => 'title',
                        'label' => translate('seller_pictures_categories_dashboard_modal_field_name_label_text', null, true),
                        'rules' => array('required' => '', 'max_len[50]' => ''),
                    ),
                );
                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $company_id = my_company_id();
                $category_title = cleanInput($_POST['title']);
                if ($this->seller_pictures->has_pictures_category(
                    $category_title,
                    array(
                        'company' => $company_id,
                    )
                )) {
                    jsonResponse(translate('seller_pictures_category_name_exists_message'));
                }

                if (!$this->seller_pictures->add_pictures_category(array(
                    'id_company'     => $company_id,
                    'category_title' => $category_title,
                ))) {
                    jsonResponse(translate('seller_pictures_failed_add_category_message'));
                }

                jsonResponse(translate('seller_pictures_category_added_message'), 'success');

            break;
            case 'edit_category':
                checkHaveCompanyAjax();
                checkPermisionAjax('have_pictures');
                checkGroupExpire('ajax');

                $validator_rules = array(
                    array(
                        'field' => 'title',
                        'label' => translate('seller_pictures_categories_dashboard_modal_field_name_label_text', null, true),
                        'rules' => array('required' => '', 'max_len[50]' => ''),
                    ),
                    array(
                        'field' => 'id_category',
                        'label' => translate('seller_pictures_categories_dashboard_modal_field_category_label_text', null, true),
                        'rules' => array('required' => '', 'integer' => ''),
                    ),
                );
                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $company_id = my_company_id();
                $category_id = (int) $_POST['id_category'];
                if (
                    empty($category_id) ||
                    empty($category = $this->seller_pictures->get_pictures_category(
                        $category_id,
                        array(
                            'company' => $company_id,
                        )
                    ))
                ) {
                    jsonResponse(translate('seller_pictures_category_not_exist_message'));
                }

                $category_title = cleanInput($_POST['title']);
                if ($this->seller_pictures->has_pictures_category(
                    $category_title,
                    array(
                        'not'     => $category_id,
                        'company' => $company_id,
                    )
                )) {
                    jsonResponse(translate('seller_pictures_category_name_exists_message'));
                }

                if (!$this->seller_pictures->update_pictures_category($category_id, array(
                    'category_title' => $category_title,
                ))) {
                    jsonResponse(translate('seller_pictures_update_failed_message'));
                }

                jsonResponse(translate('seller_pictures_category_updated_message'), 'success');

            break;
            case 'delete_category':
                checkHaveCompanyAjax();
                checkPermisionAjax('have_pictures');
                checkGroupExpire('ajax');

                $category_id = (int) $_POST['category'];
                if (
                    empty($category_id) ||
                    empty($category = $sellerPicturesModel->get_pictures_category(
                        $category_id,
                        array(
                            'company' => my_company_id(),
                        )
                    ))
                ) {
                    jsonResponse(translate('seller_pictures_category_not_exist_message'));
                }
                if ($this->seller_pictures->exist_pictures_in_category($category_id)) {
                    jsonResponse(translate('seller_pictures_category_used_message'));
                }
                if (!$this->seller_pictures->delete_pictures_category($category_id)) {
                    jsonResponse(translate('seller_pictures_failed_delete_message'));
                }

                jsonResponse(translate('seller_pictures_category_deleted_message'), 'success');

            break;
            case 'get_categories':
                checkHaveCompanyAjax();
                checkPermisionAjax('have_pictures');
                checkGroupExpire('ajax');

                jsonResponse('', 'success', array(
                    'categories' => $this->seller_pictures->get_pictures_categories(array(
                        'conditions' => array(
                            'company' => my_company_id(),
                        ),
                    )),
                ));

            break;
            case 'add_pictures':
                checkHaveCompanyAjax();
                checkPermisionAjax('have_pictures');
                checkGroupExpire('ajax');

                $this->load->model('User_Statistic_Model', 'statistic');
                $this->load->model('Company_Model', 'company');
                $this->load->model('User_Model', 'user');

                $i = 1;
                $post = array('pictures_category' => $_POST['pictures_category']);
                $validator_rules = array(
                    array(
                        'field' => 'pictures_category',
                        'label' => translate('seller_pictures_dashboard_modal_field_categrory_label_text', null, true),
                        'rules' => array('required' => '', 'integer' => ''),
                    ),
                );
                foreach ($_POST['images'] as $key => $item) {
                    $post["title_{$key}"] = $item['title'];
                    $post["description_{$key}"] = $item['description'];

                    $validator_rules[] = array(
                        'field' => "title_{$key}",
                        'label' => translate('seller_pictures_image_title_label', array('[[IMAGE_ID]]' => $i), true),
                        'rules' => array('required' => '', 'max_len[200]' => ''),
                    );
                    $validator_rules[] = array(
                        'field' => "description_{$key}",
                        'label' => translate('seller_pictures_image_description_label', array('[[IMAGE_ID]]' => $i), true),
                        'rules' => array('required' => '', 'max_len[2000]' => ''),
                    );

                    ++$i;
                }
                $this->validator->validate_data = $post;
                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                if (empty($_POST['images'])) {
                    jsonResponse(translate('seller_pictures_select_image_message'));
                }

                $seller_id = privileged_user_id();
                $company_id = my_company_id();
                $category_id = (int) $_POST['pictures_category'];
                if (
                    empty($category_id) ||
                    empty($category = $this->seller_pictures->get_pictures_category(
                        $category_id,
                        array(
                            'company' => $company_id,
                        )
                    ))
                ) {
                    jsonResponse(translate('seller_pictures_category_not_exist_message'));
                }

                /** @var FilesystemProviderInterface  $storageProvider */
                $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                $publicDisk = $storageProvider->storage('public.storage');
                $publicPrefixer = $storageProvider->prefixer('public.storage');
                $path = CompanyPhotosFilePathGenerator::photosFolder($company_id);
                $publicDisk->createDirectory($path);

                $photos_count = 0;
                foreach ($_POST['images'] as $item) {
                    $picture_title = cleanInput($item['title']);
                    if ($this->seller_pictures->has_picture(
                        $picture_title,
                        array(
                            'company' => $company_id,
                        )
                    )) {
                        jsonResponse(translate('seller_pictures_picture_name_exists_message'));
                    }

                    $images = $this->upload->copy_images_new(array(
                        'images'      => array($item['img']),
                        'destination' => $publicPrefixer->prefixPath($path),
                        'resize'      => config('seller_picture_preview_image_size', '800xR'),
                        'thumbs'      => config('seller_picture_thumb_size', '100xR,220xR,400xR'),
                        'rules'       => array(
                            'size'       => config('fileupload_max_file_size', 1024 * 1024 * 10),
                            'format'     => config('fileupload_image_formats', 'jpg,jpeg,png,gif,bmp'),
                            'min_width'  => config('seller_picture_min_width', 800),
                            'min_height' => config('seller_picture_min_height', 450),
                        ),
                    ));
                    if (!empty($images['errors'])) {
                        jsonResponse($images['errors']);
                    }
                    if (empty($images[0])) {
                        continue;
                    }

                    $photo = $images[0];
                    $thumbs = array();
                    foreach ($photo['thumbs'] as $thumb) {
                        $thumbs[$thumb['thumb_key']] = $thumb['thumb_name'];
                    }

                    $photo_id = $this->seller_pictures->add_picture(array(
                        'id_seller'         => $seller_id,
                        'id_company'        => $company_id,
                        'id_category'       => $category_id,
                        'title_photo'       => cleanInput($item['title']),
                        'description_photo' => cleanInput($item['description']),
                        'path_photo'        => $photo['new_name'],
                        'thumbs_photo'      => serialize($thumbs),
                    ));

                    ++$photos_count;

                    if (!empty($_POST['post_wall'])) {
                        library('wall')->add(array(
                            'operation'  => 'add',
                            'type'       => 'photo',
                            'id_item'    => $photo_id,
                            'id_company' => $company_id,
                            'id_seller'  => $seller_id,
                        ));
                    }
                }

                $this->statistic->set_users_statistic(array($seller_id => array('company_posts_pictures' => $photos_count)));

                //region block user content
                $seller_info = model('user')->getSimpleUser(privileged_user_id());
                if(in_array($seller_info['status'], array('blocked', 'restricted'))){
                    model('blocking')->change_blocked_users_pictures(array(
                        'blocked' => 0,
                        'users_list' => array(privileged_user_id())
                    ), array('blocked' => 2));
                }
                //endregion block user content

                jsonResponse(translate('seller_pictures_added_successfully_message'), 'success');

            break;
            case 'add_one_picture':
                checkHaveCompanyAjax();
                checkPermisionAjax('have_pictures');
                checkGroupExpire('ajax');

                $this->load->model('User_Model', 'user');
                $this->load->model('Company_Model', 'company');
                $this->load->model('User_Statistic_Model', 'statistic');

                $validator_rules = array(
                    array(
                        'field' => 'title',
                        'label' => translate('seller_pictures_dashboard_modal_field_image_title_label_text', null, true),
                        'rules' => array('required' => '', 'max_len[200]' => ''),
                    ),
                    array(
                        'field' => 'description',
                        'label' => translate('seller_pictures_dashboard_modal_field_image_description_label_text', null, true),
                        'rules' => array('required' => '', 'max_len[2000]' => ''),
                    ),
                    array(
                        'field' => 'photo',
                        'label' => translate('seller_pictures_select_image_message'),
                        'rules' => array('required' => ''),
                    ),
                );
                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $seller_id = privileged_user_id();
                $company_id = my_company_id();

                $category_new = cleanInput($_POST['new_category']);

                if(!empty($category_new)){

                    if ($this->seller_pictures->has_pictures_category(
                        $category_new,
                        array(
                            'company' => $company_id,
                        )
                    )) {
                        jsonResponse(translate('seller_pictures_category_name_exists_message'));
                    }

                    $category_id = (int) $this->seller_pictures->add_pictures_category(array(
                        'id_company'     => $company_id,
                        'category_title' => $category_new,
                    ));

                    if (!$category_id) {
                        jsonResponse(translate('seller_pictures_failed_add_category_message'));
                    }

                }else{

                    $category_id = (int) $_POST['category'];
                    if (
                        empty($category_id)
                        || empty($this->seller_pictures->get_pictures_category(
                            $category_id,
                            array(
                                'company' => $company_id,
                            )
                        ))
                    ) {
                        jsonResponse(translate('seller_pictures_category_not_exist_message'));
                    }

                }

                $picture_title = cleanInput($_POST['title']);

                if ($this->seller_pictures->has_picture(
                    $picture_title,
                    array(
                        'company' => $company_id,
                    )
                )) {
                    jsonResponse(translate('seller_pictures_picture_name_exists_message'));
                }

                //region photos

                $tempImage = request()->request->get('photo');
                if(!empty($tempImage)){
                    $image = $this->saveImage($company_id, $tempImage);
                }

                $photo_id = $this->seller_pictures->add_picture(
                    [
                        'description_photo' => cleanInput($_POST['description']),
                        'id_category'       => $category_id,
                        'title_photo'       => $picture_title,
                        'id_company'        => $company_id,
                        'path_photo'        => $image['new_name'],
                        'type_photo'        => $image['image_type'],
                        'id_seller'         => $seller_id,
                    ]
                );

                if (empty($photo_id)) {
                    jsonResponse(translate('seller_pictures_failed_save_picture_message'));
                }

                if (!empty($_POST['post_wall'])) {
                    library('wall')->add(array(
                        'operation'  => 'add',
                        'type'       => 'photo',
                        'id_item'    => $photo_id,
                        'id_company' => $company_id,
                        'id_seller'  => $seller_id,
                    ));
                }

                $this->statistic->set_users_statistic(array($seller_id => array('company_posts_pictures' => 1)));

                //region block user content
                $seller_info = model('user')->getSimpleUser(privileged_user_id());
                if(in_array($seller_info['status'], array('blocked', 'restricted'))){
                    model('blocking')->change_blocked_users_pictures(array(
                        'blocked' => 0,
                        'users_list' => array(privileged_user_id())
                    ), array('blocked' => 2));
                }
                //endregion block user content

                jsonResponse(translate('seller_pictures_picture_added_successfully_message'), 'success');

            break;
            case 'edit_picture':
                checkHaveCompanyAjax();
                checkPermisionAjax('have_pictures');
                checkGroupExpire('ajax');

                $validator_rules = array(
                    array(
                        'field' => 'title',
                        'label' => translate('seller_pictures_dashboard_modal_field_image_title_label_text', null, true),
                        'rules' => array('required' => '', 'max_len[200]' => ''),
                    ),
                    array(
                        'field' => 'description',
                        'label' => translate('seller_pictures_dashboard_modal_field_image_description_label_text', null, true),
                        'rules' => array('required' => '', 'max_len[2000]' => ''),
                    ),
                    array(
                        'field' => 'photo',
                        'label' => translate('seller_pictures_dashboard_modal_field_picture_label_text', null, true),
                        'rules' => array('required' => ''),
                    ),
                );

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $picture_id = (int) $_POST['photo'];
                if (
                    empty($picture_id) ||
                    !$this->seller_pictures->exist_picture($picture_id)
                ) {
                    jsonResponse(translate('seller_pictures_not_exist_picture'));
                }

                if (!$this->seller_pictures->is_my_picture($picture_id, my_company_id())) {
                    jsonResponse(translate('seller_pictures_picture_not_yours_message'));
                }

                $seller_id = privileged_user_id();
                $company_id = my_company_id();

                $category_new = cleanInput($_POST['new_category']);

                if(!empty($category_new)){

                    if ($this->seller_pictures->has_pictures_category(
                        $category_new,
                        array(
                            'company' => $company_id,
                        )
                    )) {
                        jsonResponse(translate('seller_pictures_category_name_exists_message'));
                    }

                    $category_id = (int) $this->seller_pictures->add_pictures_category(array(
                        'id_company'     => $company_id,
                        'category_title' => $category_new,
                    ));

                    if (!$category_id) {
                        jsonResponse(translate('seller_pictures_failed_add_category_message'));
                    }

                }else{

                    $category_id = (int) $_POST['category'];
                    if (
                        empty($category_id)
                        || empty($this->seller_pictures->get_pictures_category(
                            $category_id,
                            array(
                                'company' => $company_id,
                            )
                        ))
                    ) {
                        jsonResponse(translate('seller_pictures_category_not_exist_message'));
                    }

                }

                $picture_title = cleanInput($_POST['title']);
                if ($this->seller_pictures->has_picture(
                    $picture_title,
                    array(
                        'not'     => $picture_id,
                        'company' => $company_id,
                    )
                )) {
                    jsonResponse(translate('seller_pictures_picture_name_exists_message'));
                }

                $updateColumn = array(
                    'id_category'       => $category_id,
                    'title_photo'       => $picture_title,
                    'description_photo' => cleanInput($_POST['description']),
                    'edit_date_photo'   => date('Y-m-d H:i:s'),
                );

                if (!$this->seller_pictures->update_picture($picture_id, $updateColumn)) {
                    jsonResponse(translate('seller_pictures_cannot_update_message'));
                }

                if (!empty($_POST['post_wall'])) {
                    library('wall')->add(array(
                        'operation'  => 'edit',
                        'type'       => 'photo',
                        'id_item'    => $picture_id,
                        'id_company' => $company_id,
                        'id_seller'  => $seller_id,
                    ));
                }

                jsonResponse(translate('seller_pictures_updated_successfully_message'), 'success', array(
                    'photo'          => $picture_id,
                    'newTitle'       => $updateColumn['title_photo'],
                    'newDescription' => truncWords($updateColumn['description_photo'], 15),
                ));

            break;
            // @deprecated
            // case 'delete':
            //     checkHaveCompanyAjax();
            //     checkPermisionAjax('have_pictures');
            //     checkGroupExpire('ajax');

            //     $this->load->model('User_Statistic_Model', 'statistic');

            //     $pictures_ids = array_map('intval', $_POST['picture']);
            //     if (empty($pictures_ids)) {
            //         jsonResponse(translate('seller_pictures_select_one_message'));
            //     }

            //     $seller_id = privileged_user_id();
            //     $company_id = my_company_id();
            //     $pictures = $this->seller_pictures->get_pictures(array(
            //         'conditions' => array(
            //             'company' => $company_id,
            //             'seller'  => $seller_id,
            //             'photos'  => $pictures_ids,
            //         ),
            //     ));
            //     if (empty($pictures)) {
            //         jsonResponse(translate('seller_pictures_no_pictures_to_delete_message'));
            //     }

            //     $removal_queue = array_column($pictures, 'id_photo');
            //     $file_removal_queue = array();
            //     foreach ($pictures as $picture) {
            //         if ((int) $picture['id_company'] !== (int) $company_id) {
            //             jsonResponse(translate('general_no_permission_message'));
            //         }

            //         $file_removal_queue[] = $picture['path_photo'];
            //         $thumbs = unserialize($picture['thumbs_photo']);
            //         foreach ($thumbs as $size => $thumb) {
            //             $file_removal_queue[] = $thumb;
            //         }
            //     }
            //     if (empty($file_removal_queue)) {
            //         jsonResponse(translate('seller_pictures_choose_pictures_message'));
            //     }

            //     remove_files($file_removal_queue, "{$this->seller_pictures->files_path}/{$company_id}/pictures");
            //     if (!$this->seller_pictures->delete_pictures($removal_queue)) {
            //         jsonResponse(translate('seller_pictures_not_deleted_message'));
            //     }

            //     library('wall')->remove(array(
            //         'type'       => 'photo',
            //         'id_item'    => $removal_queue,
            //     ));

            //     $this->seller_pictures->delete_all_picture_comments($removal_queue);
            //     $this->statistic->set_users_statistic(array($seller_id => array('company_posts_pictures' => -count($removal_queue))));

            //     jsonResponse(translate('seller_pictures_deleted_message'), 'success');

            // break;
            case 'delete_picture':
                if (!have_right('moderate_content')) {
                    checkHaveCompanyAjax();
                    checkPermisionAjax('have_pictures');
                    checkGroupExpire('ajax');
                }

                $this->load->model('User_Statistic_Model', 'statistic');

                $seller_id = privileged_user_id();
                $company_id = my_company_id();
                $picture_id = (int) $_POST['picture'];
                if (
                    empty($picture_id) ||
                    empty($picture = $this->seller_pictures->get_picture($picture_id))
                ) {
                    jsonResponse(translate('seller_pictures_not_exist_picture'));
                }

                if (!have_right('moderate_content')) {
                    if ((int) $picture['id_company'] !== (int) $company_id) {
                        jsonResponse(translate('general_no_permission_message'));
                    }
                }
                if (!$this->seller_pictures->delete_picture($picture_id)) {
                    jsonResponse(translate('seller_pictures_cannot_delete_now_message'));
                }
                //delete file from disk
                /** @var FilesystemProviderInterface  $storageProvider */
                $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                $publicDisk = $storageProvider->storage('public.storage');
                try{
                    $publicDisk->delete(CompanyPhotosFilePathGenerator::photosPath($company_id, $picture['path_photo']));
                    foreach(CompanyPhotosThumb::cases() as $thumb){
                        $publicDisk->delete(CompanyPhotosFilePathGenerator::thumbImage($company_id, $picture['path_photo'], $thumb));
                    }
                } catch (UnableToDeleteFile $e) {
                    jsonResponse(translate('seller_pictures_cannot_delete_now_message'));
                }
                library('wall')->remove(array(
                    'type'       => 'photo',
                    'id_item'    => $picture_id
                ));

                $this->seller_pictures->delete_all_picture_comments($picture_id);
                $this->statistic->set_users_statistic(array($seller_id => array('company_posts_pictures' => -1)));

                jsonResponse(translate('seller_pictures_successfully_deleted_message'), 'success', array('picture' => $picture_id));

            break;
            case 'add_comment':
                checkPermisionAjax('write_comments');
                is_allowed('freq_allowed_add_comment');

                $validator_rules = array(
                    array(
                        'field' => 'message',
                        'label' => translate('seller_pictures_comment_label'),
                        'rules' => array('required' => '', 'max_len[500]' => ''),
                    ),
                    array(
                        'field' => 'picture',
                        'label' => translate('seller_pictures_picture_info_label'),
                        'rules' => array('required' => ''),
                    ),
                );
                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $user_id = privileged_user_id();
                $picture_id = (int) $_POST['picture'];
                if (
                    empty($picture_id) ||
                    !$this->seller_pictures->exist_picture($picture_id)
                ) {
                    jsonResponse(translate('seller_pictures_not_exist_picture'));
                }

                $update = array(
                    'id_photo'         => $picture_id,
                    'id_user'          => $user_id,
                    'message_comment'  => cleanInput($_POST['message'])
                );

                $reply_id = $this->seller_pictures->add_comment($update);
                if (empty($reply_id)) {
                    jsonResponse(translate('seller_pictures_cannot_add_comments_message'));
                }

                $this->seller_pictures->update_picture_comments_counter($picture_id, 1);

                jsonResponse(translate('seller_pictures_comment_added_message'), 'success');

            break;
            case 'edit_comment':
                checkPermisionAjax('write_comments');
                is_allowed('freq_allowed_add_comment');

                $validator_rules = array(
                    array(
                        'field' => 'message',
                        'label' => translate('seller_pictures_comment_label'),
                        'rules' => array('required' => '', 'max_len[500]' => ''),
                    ),
                    array(
                        'field' => 'comment',
                        'label' => translate('seller_pictures_comment_info_label'),
                        'rules' => array('required' => ''),
                    ),
                );

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $user_id = privileged_user_id();
                $comment_id = (int) $_POST['comment'];
                if (
                    empty($comment_id) ||
                    empty($comment = $this->seller_pictures->get_comment($comment_id))
                ) {
                    jsonResponse(translate('general_comment_not_exist_message'));
                }
                if (!$this->seller_pictures->is_my_comment($comment_id, $user_id)) {
                    jsonResponse(translate('general_no_permission_message'));
                }
                if ($comment['moderated']) {
                    jsonResponse(translate('seller_pictures_comment_already_moderated_message'));
                }
                if ($comment['censored']) {
                    jsonResponse(translate('seller_pictures_comment_cencored'));
                }

                $update = array(
                    'message_comment' => cleanInput($_POST['message']),
                );
                if (!$this->seller_pictures->update_comment($comment_id, $update)) {
                    jsonResponse(translate('seller_pictures_cannot_update_comment_message'));
                }

                jsonResponse(translate('seller_pictures_comment_updated_message'), 'success', array(
                    'parent'          => $comment['reply_to_comment'],
                    'comment'         => $update['id_comment'],
                    'message_comment' => $update['message_comment'],
                ));

            break;
            case 'censor_comment':
                checkPermisionAjax('moderate_content');

                $comment_id = (int) $_POST['comment'];
                if (
                    empty($comment_id) ||
                    empty($comment = $this->seller_pictures->get_comment($comment_id))
                ) {
                    jsonResponse(translate('general_comment_not_exist_message'));
                }

                $update = array(
                    'id_comment' => $comment_id,
                    'censored'   => 1,
                );
                if (!$this->seller_pictures->censor_comment($comment_id)) {
                    jsonResponse(translate('seller_pictures_cannot_censore_comment_now_message'));
                }

                jsonResponse(translate('seller_pictures_comment_censored_message'), 'success', array(
                    'parent'     => $comment['reply_to_comment'],
                    'id_comment' => $comment_id,
                ));

            break;
            case 'moderate_comment':
                checkPermisionAjax('moderate_content');

                $comment_id = (int) $_POST['comment'];
                if (
                    empty($comment_id) ||
                    empty($comment = $this->seller_pictures->get_comment($comment_id))
                ) {
                    jsonResponse(translate('general_comment_not_exist_message'));
                }

                if (!$this->seller_pictures->moderate_comment($comment_id)) {
                    jsonResponse(translate('seller_pictures_cannot_moderate_now_message'));
                }

                jsonResponse(translate('seller_pictures_comment_moderated_message'), 'success');

            break;
            case 'email':
                checkPermisionAjax('email_this');
                is_allowed('freq_allowed_send_email_to_user');

                $emails_limit = config('email_this_max_email_count', 10);
                $validator_rules = array(
                    array(
                        'field' => 'emails',
                        'label' => translate('general_modal_send_mail_field_addresses_label_text', null, true),
                        'rules' => array('required' => '', 'no_whitespaces' => '', 'valid_emails' => '', "max_emails_count[{$emails_limit}]" => ''),
                    ),
                    array(
                        'field' => 'message',
                        'label' => translate('general_modal_send_mail_field_message_label_text', null, true),
                        'rules' => array('required' => '', 'max_len[500]' => ''),
                    ),
                    array(
                        'field' => 'id',
                        'label' => translate('seller_pictures_public_modal_field_picture_label_text', null, true),
                        'rules' => array('required' => ''),
                    ),
                );
                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $filteredEmails = filter_email($_POST['emails']);

                if (empty($filteredEmails)) {
                    jsonResponse(translate('seller_pictures_valid_email_message'));
                }

                $picture_id = (int) $_POST['id'];
                if (empty($picture_id) || empty($picture = $this->seller_pictures->get_picture($picture_id))) {
                    jsonResponse(translate('seller_pictures_not_exist_picture'));
                }

                $company = model(Company_Model::class)->get_company(['id_company' => $picture['id_company']]);

                if (empty($company)) {
                    jsonResponse(translate('general_company_not_exist_message'));
                }

                $userName = user_name_session();

                try {
                    /** @var FilesystemProviderInterface  $storageProvider */
                    $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                    $publicDisk = $storageProvider->storage('public.storage');
                    $pathToFile = CompanyPhotosFilePathGenerator::thumbImage($picture['id_company'], $picture['path_photo'], CompanyPhotosThumb::SMALL());
                    //send email
                    /** @var MailerInterface $mailer */
                    $mailer = $this->getContainer()->get(MailerInterface::class);
                    $mailer->send(
                        (new EmailFriendAboutPicture($userName, cleanInput(request()->request->get('message')), $company, $picture, null, null, $publicDisk->url($pathToFile)))
                            ->to(...array_map(fn (string $to) => new Address($to), $filteredEmails))
                            ->subjectContext(['[userName]' => $userName])
                    );
                } catch (\Throwable $th) {
                    jsonResponse(translate('email_has_not_been_sent'));
                }

                jsonResponse(translate('seller_pictures_email_sent_message'), 'success');

            break;
            case 'share':
                checkPermisionAjax('share_this');
                is_allowed('freq_allowed_send_email_to_user');

                $validator_rules = array(
                    array(
                        'field' => 'message',
                        'label' => translate('general_modal_share_field_message_label_text', null, true),
                        'rules' => array('required' => '', 'max_len[500]' => ''),
                    ),
                    array(
                        'field' => 'id',
                        'label' => translate('seller_pictures_public_modal_field_picture_label_text', null, true),
                        'rules' => array('required' => ''),
                    ),
                );

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $picture_id = (int) $_POST['id'];
                if (empty($picture_id) || empty($picture = $this->seller_pictures->get_picture($picture_id))) {
                    jsonResponse(translate('seller_pictures_not_exist_picture'));
                }

                $company = model(Company_Model::class)->get_company(['id_company' => $picture['id_company']]);

                if (empty($company)) {
                    jsonResponse(translate('general_company_not_exist_message'));
                }

                $userId = privileged_user_id();
                $filteredEmails = model(Followers_Model::class)->getFollowersEmails($userId);

                if (empty($filteredEmails)) {
                    jsonResponse(translate('seller_pictures_no_followers_message'));
                }

                $userName = user_name_session();

                try {
                    /** @var FilesystemProviderInterface  $storageProvider */
                    $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                    $publicDisk = $storageProvider->storage('public.storage');
                    $pathToFile = CompanyPhotosFilePathGenerator::thumbImage($picture['id_company'], $picture['path_photo'], CompanyPhotosThumb::SMALL());
                    //send email
                    /** @var MailerInterface $mailer */
                    $mailer = $this->getContainer()->get(MailerInterface::class);
                    $mailer->send(
                        (new EmailFriendAboutPicture($userName, cleanInput(request()->request->get('message')), $company, $picture, null, null, $publicDisk->url($pathToFile)))
                            ->to(...array_map(fn (int $id, string $to) => new RefAddress((string) $id, new Address($to)), array_column($filteredEmails, 'idu', 'idu'), array_column($filteredEmails, 'email', 'email')))
                            ->subjectContext(['[userName]' => $userName])
                    );
                } catch (\Throwable $th) {
                    jsonResponse(translate('email_has_not_been_sent'));
                }

                jsonResponse(translate('seller_pictures_email_sent_message'), 'success');

            break;
            default:
                jsonResponse(translate('seller_pictures_path_not_found'));

            break;
        }
    }

    public function ajax_seller_upload_photo()
    {
        checkIsAjax();
        checkIsLoggedAjax();
        checkHaveCompanyAjax();
        checkPermisionAjax('have_pictures');
        checkGroupExpire('ajax');

        $request = request();
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('files');
        if (null === $uploadedFile) {
			jsonResponse(translate('seller_pictures_select_file_message'));
		}
        if (is_array($uploadedFile) || !$uploadedFile->isValid()) {
			jsonResponse(translate('seller_pictures_invalid_file_message'));
		}
        $config = 'img.companies.photos.rules';
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
        //get the disks and prefixers
        /** @var FilesystemProviderInterface  $storageProvider */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $tempDisk = $storageProvider->storage('temp.storage');
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

    public function ajax_seller_delete_photo()
    {
        checkIsAjax();
        checkIsLoggedAjax();
        checkHaveCompanyAjax();
        checkPermisionAjax('have_pictures');
        checkGroupExpire('ajax');

        if (empty($file = request()->request->get('file'))) {
            jsonResponse('File name is not correct.');
        }
        /** @var FilesystemProviderInterface  $storageProvider */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $tempDisk = $storageProvider->storage('temp.storage');
        $pathToFile = FilePathGenerator::uploadedFile($file);

        if (!$tempDisk->fileExists($pathToFile)) {
            jsonResponse('Upload path is not correct.');
        }
        try{
            $tempDisk->delete($pathToFile);
        } catch (UnableToDeleteFile $e) {
            //silent fail
        }

        jsonResponse(null, 'success');
    }

    private function picture_categories(array $categories = array())
    {
        $output = array();
        foreach ($categories as $category) {
            $category_id = (int) $category['id_category'];
            $category_title = cleanOutput($category['category_title']);

            //region Category
            $category_preview = "
                <div class=\"grid-text\">
                    <div class=\"grid-text__item\" " . addQaUniqueIdentifier('page__seller-pictures-categories__table_item-title') . ">
                        <div>
                            {$category_title}
                        </div>
                    </div>
                </div>
            ";
            //endregion Category

            //region Actions
            //region Edit button
            $edit_button = null;
            $edit_button_url = __SITE_URL . "seller_pictures/popup_forms/edit_category/{$category_id}";
            $edit_button_text = translate('general_button_edit_text', null, true);
            $edit_button_title = translate('seller_pictures_categories_dt_button_edit_category_title', null, true);
            $edit_button_modal_title = translate('seller_pictures_categories_dt_button_edit_category_modal_title', null, true);
            $edit_button = "
                <a rel=\"edit\"
                    " . addQaUniqueIdentifier('page__seller-pictures-categories__table_dropdown-menu_edit-btn') . "
                    class=\"dropdown-item fancybox.ajax fancyboxValidateModal\"
                    data-fancybox-href=\"{$edit_button_url}\"
                    data-title=\"{$edit_button_modal_title}\"
                    title=\"{$edit_button_title}\">
                    <i class=\"ep-icon ep-icon_pencil\"></i>
                    <span>{$edit_button_text}</span>
                </a>
            ";
            //endregion Edit button

            //region Delete button
            $delete_button = null;
            $delete_button_text = translate('general_button_delete_text', null, true);
            $delete_button_title = translate('seller_pictures_categories_dt_button_delete_category_title', null, true);
            $delete_button_message = translate('seller_pictures_categories_dt_button_delete_category_message', null, true);
            $delete_button = "
                <a class=\"dropdown-item confirm-dialog\"
                    " . addQaUniqueIdentifier('page__seller-pictures-categories__table_dropdown-menu_delete-btn') . "
                    title=\"{$delete_button_title}\"
                    data-message=\"{$delete_button_message}\"
                    data-callback=\"deleteCategory\"
                    data-category=\"{$category_id}\">
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
                    target=\"_blank\">
                    <i class=\"ep-icon ep-icon_info-stroke\"></i>
                    <span>{$all_button_text}</span>
                </a>
            ";
            //endregion All button

            $actions = "
                <div class=\"dropdown\">
                    <a class=\"dropdown-toggle\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\" " . addQaUniqueIdentifier('page__seller-pictures-categories__table_dropdown-btn') . ">
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

    private function my_seller_pictures(array $pictures = array())
    {
        $output = array();
        foreach ($pictures as $picture) {
            $picture_id = (int) $picture['id_photo'];
            $company_id = (int) $picture['id_company'];
            $picture_title = cleanOutput($picture['title_photo']);

            //region Picture
            $picture_image_name = $picture['path_photo'];
            $picture_category_name = cleanOutput($picture['category_title']);
            $picture_comments_text = translate('seller_pictures_dt_picture_comments_text', array('{amount}' => (int) $picture['comments_count']), true);
            /** @var FilesystemProviderInterface $storageProvider */
            $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
            $storage = $storageProvider->storage('public.storage');
            $picture_image_url = $storage->url(CompanyPhotosFilePathGenerator::photosPath($company_id, $picture_image_name, CompanyPhotosThumb::SMALL()));
            $picture_url = getCompanyUrl($picture) . '/picture/' . strForUrl($picture_title) . "-{$picture_id}";

            $picture_preview = "
                <div class=\"flex-card\">
                    <div class=\"flex-card__fixed spersonal-pictures__img spersonal-pictures__img--w mh-100 image-card2\">
                        <span class=\"link\">
                            <img class=\"image\" src=\"{$picture_image_url}\" alt=\"{$picture_title}\"/ " . addQaUniqueIdentifier('page__seller-pictures-my__table_item-image') . ">
                        </span>
                    </div>
                    <div class=\"spersonal-pictures__desc2 flex-card__float\">
                        <div class=\"main-data-table__item-ttl\">
                            <a href=\"{$picture_url}\"
                                " . addQaUniqueIdentifier('page__seller-pictures-my__table_item-title') . "
                                class=\"display-ib link-black txt-medium\"
                                title=\"{$picture_title}\"
                                target=\"_blank\">
                                {$picture_title}
                            </a>
                        </div>
                        <div class=\"links-black\" " . addQaUniqueIdentifier('page__seller-pictures-my__table_item-category') . ">{$picture_category_name}</div>
                        <div class=\"txt-gray\" " . addQaUniqueIdentifier('page__seller-pictures-my__table_item-comments') . ">{$picture_comments_text}</div>
                    </div>
                </div>
            ";
            //endregion Picture

            //region Description
            $description = '&mdash;';
            if (!empty($picture['description_photo'])) {
                $description_text = cleanOutput(strLimit($picture['description_photo'], 300));
                $description = "
                    <div class=\"grid-text\">
                        <div class=\"grid-text__item\" " . addQaUniqueIdentifier('page__seller-pictures-my__table_item-description') . ">
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
            $edit_button = null;
            $edit_button_url = __SITE_URL . "seller_pictures/popup_forms/edit_picture/{$picture_id}";
            $edit_button_text = translate('general_button_edit_text', null, true);
            $edit_button_modal_title = translate('seller_pictures_dt_button_edit_picture_modal_title', null, true);
            $edit_button = "
                <a rel=\"edit\"
                    " . addQaUniqueIdentifier('page__seller-pictures-my__table_dropdown-menu_edit-btn') . "
                    class=\"dropdown-item fancybox.ajax fancyboxValidateModal\"
                    data-fancybox-href=\"{$edit_button_url}\"
                    data-title=\"{$edit_button_modal_title}\">
                    <i class=\"ep-icon ep-icon_pencil\"></i>
                    <span>{$edit_button_text}</span>
                </a>
            ";
            //endregion Edit button

            //region Delete button
            $delete_button = null;
            $delete_button_text = translate('general_button_delete_text', null, true);
            $delete_button_message = translate('seller_pictures_dt_button_delete_picture_message', null, true);
            $delete_button = "
                <a class=\"dropdown-item confirm-dialog\"
                    " . addQaUniqueIdentifier('page__seller-pictures-my__table_dropdown-menu_delete-btn') . "
                    data-message=\"{$delete_button_message}\"
                    data-callback=\"deletePicture\"
                    data-picture=\"{$picture_id}\">
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
                    target=\"_blank\">
                    <i class=\"ep-icon ep-icon_info-stroke\"></i>
                    <span>{$all_button_text}</span>
                </a>
            ";
            //endregion All button

            $actions = "
                <div class=\"dropdown\">
                    <a class=\"dropdown-toggle\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\" " . addQaUniqueIdentifier('page__seller-pictures-my__table_dropdown-btn') . ">
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
                'picture'     => $picture_preview,
                'description' => $description,
                'created_at'  => getDateFormatIfNotEmpty($picture['add_date_photo']),
                'updated_at'  => getDateFormatIfNotEmpty($picture['edit_date_photo']),
                'actions'     => $actions,
            );
        }

        return $output;
    }

    /**
     * Save image on disk and in database
     *
     * @param int $companyId - id of the article
     * @param string $mainImage - path to directory
     */
    private function saveImage(int $companyId, string $mainImage)
    {
        if(empty($companyId) || empty($mainImage)){
            jsonResponse(translate('validation_images_upload_fail'));
        }

        /** @var FilesystemProviderInterface  $storageProvider */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        //set the disks prefixers
        $prefixerTemp = $storageProvider->prefixer('temp.storage');
        $prefixerPublic = $storageProvider->prefixer('public.storage');
        $publicDisk = $storageProvider->storage('public.storage');
        //create the folder
        $publicDisk->createDirectory(CompanyPhotosFilePathGenerator::photosFolder($companyId));
        /** @var TinyMVC_Library_Image_intervention $interventionImageLibrary */
        $interventionImageLibrary = library(TinyMVC_Library_Image_intervention::class);
        $configPhotos = config("img.companies.photos");
        $images = $interventionImageLibrary->image_processing(
            [
                'tmp_name' => $prefixerTemp->prefixPath($mainImage),
                'name'     => \basename($mainImage),
            ],
            [
                'destination'   => $prefixerPublic->prefixPath(CompanyPhotosFilePathGenerator::photosFolder($companyId)),
                'rules'         => $configPhotos['rules'],
                'handlers'      => [
                    'resize'        => $configPhotos['resize'],
                    'create_thumbs' => $configPhotos['thumbs'],
                ],
            ]
        );
        //return the errors if there are any
        if (!empty($images['errors'])) {
            jsonResponse($images['errors']);
        }

        return [
            'new_name'   => $images[0]['new_name'],
            'image_type' => $images[0]['image_type'],
        ];
    }

}
