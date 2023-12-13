<?php

use App\Common\Contracts\Media\SellerUpdatesPhotoThumb;
use App\Common\DependencyInjection\ServiceLocator\LibraryLocator;
use App\Common\Validation\ConstraintViolationInterface;
use App\Common\Validation\ValidationException;
use App\Email\EmailFriendAboutCompanyUpdates;
use App\Filesystem\CompanyUpdatesFilePathGenerator;
use App\Filesystem\FilePathGenerator;
use ExportPortal\Bridge\Mailer\Mime\RefAddress;
use ExportPortal\Contracts\Filesystem\Exception\ReadException;
use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;
use TinyMVC_Library_Image_intervention as LegacyImageHandler;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToWriteFile;
use Symfony\Component\HttpFoundation\File\UploadedFile as FileUploadedFile;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

/**
 * Seller updates controller.
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 *
 * @property \Company_Model             $company
 * @property \Followers_model           $followers
 * @property \Seller_Updates_Model      $seller_updates
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
 **/
class Seller_Updates_Controller extends TinyMVC_Controller
{
    public function index()
    {
        show_404();
    }

    public function my()
    {
        checkIsLogged();
        checkHaveCompany();
        checkPermision('have_updates');
        checkGroupExpire();

        views(['new/header_view', 'new/user/seller/updates/my/index_view', 'new/footer_view']);
    }

    public function ajax_updates_list_dt()
    {
        checkIsAjax();
        checkIsLoggedAjaxDT();
        checkHaveCompanyAjaxDT();
        checkPermisionAjaxDT('have_updates');
        checkGroupExpire('dt');

        /** @var Seller_Updates_Model $sellerUpdatesModel */
        $sellerUpdatesModel = model(Seller_Updates_Model::class);

        $request = request()->request;

        $limit = $request->getInt('iDisplayLength');
        $skip = $request->getInt('iDisplayStart');
        $order = ['date_update' => 'desc'];

        $conditions = array_merge(
            dtConditions($request->all(), [
                ['as' => 'search',          'key' => 'keywords',            'type' => 'cleanInput|cut_str:200'],
                ['as' => 'created_from',    'key' => 'created_from',        'type' => 'concat: 00:00:00|getDateFormat:m/d/Y H:i:s,Y-m-d H:i:s'],
                ['as' => 'created_to',      'key' => 'created_to',          'type' => 'concat: 23:59:59|getDateFormat:m/d/Y H:i:s,Y-m-d H:i:s'],
                ['as' => 'updated_from',    'key' => 'updated_from',        'type' => 'concat: 00:00:00|getDateFormat:m/d/Y H:i:s,Y-m-d H:i:s'],
                ['as' => 'updated_to',      'key' => 'updated_to',          'type' => 'concat: 23:59:59|getDateFormat:m/d/Y H:i:s,Y-m-d H:i:s'],
            ]),
            [
                'seller'  => privileged_user_id(),
                'company' => my_company_id(),
            ]
        );

        $params = compact('conditions', 'order', 'limit', 'skip');
        $data['updates_list'] = $sellerUpdatesModel->get_updates($params);
        $totalUpdates = $sellerUpdatesModel->count_updates($params);

        $output = [
            'sEcho'                => $request->getInt('sEcho'),
            'iTotalRecords'        => $totalUpdates,
            'iTotalDisplayRecords' => $totalUpdates,
            'aaData'               => [],
        ];

        if (!empty($data['updates_list'])) {
            $output['aaData'] = $this->my_seller_updates($data['updates_list']);
        }

        jsonResponse('', 'success', $output);
    }

    public function popup_forms()
    {
        checkIsAjax();
        checkIsLoggedAjaxModal();

        switch ($this->uri->segment(3)) {
            case 'add_update':
                checkHaveCompanyAjaxModal();
                checkPermisionAjaxModal('have_updates');
                checkGroupExpire('modal');

                // Prepare rule for allowed file types
                $formats = explode(',', config('fileupload_image_formats', 'jpg,jpeg,png,gif,bmp'));
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
                    'new/user/seller/updates/my/add_update_form_view',
                    array(
                        'action'                   => __SITE_URL . 'seller_updates/ajax_updates_operation/add_update',
                        'upload_folder'            => encriptedFolderName(),
                        'fileupload_max_file_size' => config('fileupload_small_images_max_file_size', 1024 * 1024 * 2),
                        'fileupload_limits'        => array(
                            'amount'              => 1,
                            'accept'              => $accept,
                            'formats'             => $formats,
                            'mimetypes'           => $mimetypes,
                            'image_size'          => config('fileupload_small_images_max_file_size', 1024 * 1024 * 2),
                            'image_size_readable' => config('fileupload_small_images_max_file_size_placeholder', '2MB'),
                            'image_width'         => 60,
                            'image_height'        => 60,
                        ),
                    )
                );

            break;
            case 'edit_update':
                checkHaveCompanyAjaxModal();
                checkPermisionAjaxModal('have_updates');
                checkGroupExpire('modal');

                $update_id = (int) $this->uri->segment(4);
                $company_id = (int) my_company_id();
                if (
                    empty($update_id) ||
                    empty($update = model('Seller_Updates', 'seller_updates')->get_update($update_id))
                ) {
                    messageInModal(translate('seller_updates_not_found_message'));
                }
                if ((int) $company_id !== (int) $update['id_company']) {
                    messageInModal(translate('seller_updates_not_your_update_message'));
                }

                $this->view->display(
                    'new/user/seller/updates/my/edit_update_form_view',
                    array(
                        'action' => __SITE_URL . 'seller_updates/ajax_updates_operation/edit_update',
                        'update' => $update,
                    )
                );

            break;
            case 'admin_edit_update':
                checkPermisionAjaxModal('moderate_content');

                $update_id = (int) $this->uri->segment(4);
                if (
                    empty($update_id) ||
                    empty($update = model('Seller_Updates', 'seller_updates')->get_update($update_id))
                ) {
                    messageInModal('The update was not found.');
                }
                /** @var FilesystemProviderInterface  $storageProvider */
                $provider = $this->getContainer()->get(FilesystemProviderInterface::class);
                $storage = $provider->storage('public.storage');
                $update['imageLink'] = $storage->url(CompanyUpdatesFilePathGenerator::thumbImage($update['id_company'], $update['photo_path'], SellerUpdatesPhotoThumb::MEDIUM()));

                $this->view->display('admin/directory/updates/edit_update_view', array(
                    'action' => __SITE_URL . 'seller_updates/ajax_updates_operation/edit_update',
                    'update' => $update,
                ));

            break;
            case 'email':
                checkPermisionAjaxModal('email_this');

                $update_id = (int) $this->uri->segment(4);
                if (!model('Seller_Updates', 'seller_updates')->exist_update($update_id)) {
                    messageInModal(translate('seller_updates_not_correct_message'));
                }

                $this->view->display(
                    'new/user/seller/updates/popup_email_view',
                    array(
                        'action'     => __SITE_URL . 'seller_updates/ajax_updates_operation/email',
                        'id_update'  => $update_id,
                        'max_emails' => config('email_this_max_email_count', 10),
                    )
                );

            break;
            case 'share':
                checkPermisionAjaxModal('share_this');

                $update_id = (int) $this->uri->segment(4);
                if (!model('Seller_Updates', 'seller_updates')->exist_update($update_id)) {
                    messageInModal(translate('seller_updates_not_correct_message'));
                }

                $this->view->display(
                    'new/user/seller/updates/popup_share_view',
                    array(
                        'action'    => __SITE_URL . 'seller_updates/ajax_updates_operation/share',
                        'id_update' => $update_id,
                    )
                );

            break;
            default:
                show_404();

            break;
        }
    }

    public function ajax_updates_operation()
    {
        checkIsAjax();
        checkIsLoggedAjax();

        $this->load->model('Seller_Updates_Model', 'seller_updates');

        switch (uri()->segment(3)) {
            case 'add_update':
                checkHaveCompanyAjax();
                checkPermisionAjax('have_updates');
                checkGroupExpire('ajax');

                is_allowed('freq_allowed_companies_posts');

                $validator_rules = array(
                    array(
                        'field' => 'text',
                        'label' => translate('seller_updates_dashboard_modal_field_description_label_text', null, true),
                        'rules' => array('required' => '', 'html_max_len[250]' => ''),
                    ),
                );
                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                /** @var TinyMVC_Library_Cleanhtml $cleanHtmlLibrary */
                $cleanHtmlLibrary = library(TinyMVC_Library_Cleanhtml::class);

                $request = request()->request;

                $userId = privileged_user_id();
                $companyId = my_company_id();
                $update = [
                    'id_company'  => $companyId,
                    'id_seller'   => $userId,
                    'text_update' => $cleanHtmlLibrary->sanitizeUserInput($_POST['text']),
                ];

                if (!empty($tempImage = $request->get('image'))) {
                    /** @var TinyMVC_Library_Image_intervention $interventionImageLibrary */
                    $interventionImageLibrary = library(TinyMVC_Library_Image_intervention::class);
                    /** @var FilesystemProviderInterface  $storageProvider */
                    $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                    $publicDisk = $storageProvider->storage('public.storage');
                    $publicPrefixer = $storageProvider->prefixer('public.storage');
                    $prefixerTemp = $storageProvider->prefixer('temp.storage');
                    $path = CompanyUpdatesFilePathGenerator::updatesFolder($companyId);
                    $publicDisk->createDirectory($path);

                    $imageConfigModule = config("img.seller_updates.main");
                    $images = $interventionImageLibrary->image_processing(
                        [
                            'tmp_name' => $prefixerTemp->prefixPath($tempImage),
                            'name'     => pathinfo($tempImage, PATHINFO_FILENAME)
                        ],
                        [
                            'destination'   => $publicPrefixer->prefixPath($path),
                            'rules'         => $imageConfigModule['rules'],
                            'handlers'      => [
                                'create_thumbs' => $imageConfigModule['thumbs'],
                                'resize'        => $imageConfigModule['resize'],
                            ],
                        ]
                    );
                    $update['photo_path'] = $images[0]['new_name'];
                }

                if (empty($updateId = $this->seller_updates->add_seller_update($update))) {
                    jsonResponse(translate('seller_updates_cannot_add_error_message'));
                }

                /** @var User_Statistic_Model $userStatisticModel */
                $userStatisticModel = model(User_Statistic_Model::class);

                $userStatisticModel->set_users_statistic([$userId => ['company_posts_updates' => 1]]);
                if (!empty($postOnWall = $request->get('post_wall'))) {
                    /** @var TinyMVC_Library_Wall $wallLibrary */
                    $wallLibrary = library(TinyMVC_Library_Wall::class);

                    $wallLibrary->add(
                        [
                            'id_company' => $companyId,
                            'operation'  => 'add',
                            'id_seller'  => $userId,
                            'id_item'    => $updateId,
                            'type'       => 'update',
                        ]
                    );
                }

                //region block user content
                /** @var User_Model $userModel */
                $userModel = model(User_Model::class);

                $seller_info = $userModel->getSimpleUser(privileged_user_id());

                if (in_array($seller_info['status'], ['blocked', 'restricted'])) {
                    /** @var Blocking_Model $blockingModel */
                    $blockingModel = model(Blocking_Model::class);
                    $blockingModel->change_blocked_users_updates(
                        [
                            'blocked' => 0,
                            'users_list' => [privileged_user_id()]
                        ],
                        ['blocked' => 2]
                    );
                }
                //endregion block user content

                jsonResponse(translate('seller_updates_added_successful_message'), 'success');

            break;
            case 'edit_update':
                checkHaveCompanyAjax();
                checkPermisionAjax('have_updates');
                checkGroupExpire('ajax');

                is_allowed('freq_allowed_companies_posts');

                $validator_rules = array(
                    array(
                        'field' => 'text',
                        'label' => translate('seller_updates_dashboard_modal_field_description_label_text', null, true),
                        'rules' => array('required' => '', 'html_max_len[250]' => ''),
                    ),
                    array(
                        'field' => 'update',
                        'label' => translate('seller_updates_dashboard_modal_field_update_label_text', null, true),
                        'rules' => array('required' => ''),
                    ),
                );
                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $seller_id = privileged_user_id();
                $company_id = my_company_id();
                $update_id = (int) $_POST['update'];
                if (
                    empty($update_id) ||
                    empty($update = $this->seller_updates->get_update($update_id))
                ) {
                    jsonResponse(translate('seller_updates_not_found_message'));
                }
                if ((int) $company_id !== (int) $update['id_company']) {
                    jsonResponse(translate('seller_updates_not_your_update_message'));
                }

                $update = array('text_update' => library('Cleanhtml')->sanitizeUserInput($_POST['text']));
                if (!$this->seller_updates->change_update($update_id, $update)) {
                    jsonResponse(translate('seller_updates_not_updated_message'));
                }

                if (!empty($_POST['post_wall'])) {
                    library('wall')->add(array(
                        'operation'  => 'edit',
                        'type'       => 'update',
                        'id_item'    => $update_id,
                        'id_company' => $company_id,
                        'id_seller'  => $seller_id,
                    ));
                }

                jsonResponse(translate('general_all_changes_saved_message'), 'success', array(
                    'id_update' => $update_id,
                    'new_text'  => $update['text_update'],
                ));

            break;
            case 'delete':
                checkHaveCompanyAjax();
                checkPermisionAjax('have_updates');
                checkGroupExpire('ajax');

                $this->load->model('User_Statistic_Model', 'statistic');

                $updates_ids = array_map('intval', $_POST['update']);
                if (empty($updates_ids)) {
                    jsonResponse(translate('seller_updates_no_update_selected_message'));
                }

                $company_id = my_company_id();
                $seller_id = privileged_user_id();
                $updates = $this->seller_updates->get_updates(array(
                    'conditions' => array(
                        'company' => $company_id,
                        'seller'  => $seller_id,
                        'updates' => $updates_ids,
                    ),
                ));
                if (empty($updates)) {
                    jsonResponse(translate("seller_updates_nothing_to_delete_message"));
                }

                $removal_queue = array_column($updates, 'id_update');
                foreach ($updates as $update) {
                    if ((int) $company_id !== (int) $update['id_company']) {
                        jsonResponse(translate('seller_updates_not_your_update_message'));
                    }

                    if (!empty($update['photo_path'])) {

                        /** @var FilesystemProviderInterface $storageProvider */
                        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                        $publicDisk = $storageProvider->storage('public.storage');
                        $path = CompanyUpdatesFilePathGenerator::updatesPath($company_id, $update['photo_path']);
                        try {
                            $publicDisk->delete($path);
                            foreach(SellerUpdatesPhotoThumb::cases() as $thumb){
                                $publicDisk->delete(CompanyUpdatesFilePathGenerator::thumbImage($company_id, $update['photo_path'], $thumb));
                            }
                        } catch (UnableToDeleteFile $e) {
                            jsonResponse(translate('seller_updates_failed_delete_message'));
                        }
                    }
                }

                if (!$this->seller_updates->delete_all_updates($removal_queue)) {
                    jsonResponse(translate('seller_updates_cannot_delete_message'));
                }

                library('wall')->remove(array(
                    'type'       => 'update',
                    'id_item'    => $removal_queue
                ));

                $this->statistic->set_users_statistic(array($seller_id => array('company_posts_updates' => -count($removal_queue))));

                jsonResponse(translate('seller_updates_successful_deleted_message'), 'success');

            break;
            case 'delete_update':
                checkHaveCompanyAjax();
                checkPermisionAjax('have_updates');
                checkGroupExpire('ajax');

                $this->load->model('User_Statistic_Model', 'statistic');

                $seller_id = privileged_user_id();
                $company_id = my_company_id();
                $update_id = (int) $_POST['update'];
                if (
                    empty($update_id) ||
                    empty($update = $this->seller_updates->get_update($update_id))
                ) {
                    jsonResponse(translate('seller_updates_not_found_message'));
                }
                if ((int) $company_id !== (int) $update['id_company']) {
                    jsonResponse(translate('seller_updates_not_your_update_message'));
                }

                if (!empty($update['photo_path'])) {
                    /** @var FilesystemProviderInterface $storageProvider */
                    $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                    $publicDisk = $storageProvider->storage('public.storage');
                    $path = CompanyUpdatesFilePathGenerator::updatesPath($company_id, $update['photo_path']);
                    try {
                        $publicDisk->delete($path);
                        foreach(SellerUpdatesPhotoThumb::cases() as $thumb){
                            $publicDisk->delete(CompanyUpdatesFilePathGenerator::thumbImage($company_id, $update['photo_path'], $thumb));
                        }
                    } catch (UnableToDeleteFile $e) {
                        jsonResponse(translate('seller_updates_failed_delete_message'));
                    }
                }

                if (!$this->seller_updates->delete_update($update_id)) {
                    jsonResponse(translate('seller_updates_failed_delete_message'));
                }

                library('wall')->remove(array(
                    'type'       => 'update',
                    'id_item'    => $update_id
                ));

                $this->statistic->set_users_statistic(array($seller_id => array('company_posts_updates' => -1)));

                jsonResponse(translate('seller_updates_successful_deleted_message'), 'success');

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
                        'label' => translate('seller_updates_dashboard_modal_field_update_label_text', null, true),
                        'rules' => array('required' => ''),
                    ),
                );
                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $filteredEmails = filter_email($_POST['emails']);
                if (empty($filteredEmails)) {
                    jsonResponse(translate('seller_updates_no_valid_email_message'));
                }

                $update_id = (int) $_POST['id'];

                if (empty($update_id) || empty($update = $this->seller_updates->get_update($update_id))) {
                    jsonResponse(translate('seller_updates_not_correct_message'));
                }

                $company = model(Company_Model::class)->get_company(['id_company' => $update['id_company']]);

                if (empty($company)) {
                    jsonResponse(translate('seller_updates_not_correct_message'));
                }
                /** @var FilesystemProviderInterface  $storageProvider */
                $provider = $this->getContainer()->get(FilesystemProviderInterface::class);
                $storage = $provider->storage('public.storage');
                if (!empty($update['photo_path'])) {
                    $imageUrl = $storage->url(CompanyUpdatesFilePathGenerator::thumbImage($update['id_company'], $update['photo_path'], SellerUpdatesPhotoThumb::MEDIUM()));
                } else {
                    $imageUrl = asset('/public/img/no_image/no-image-166x138.png', 'legacy');
                }
                $userName = user_name_session();

                try {
                    /** @var MailerInterface $mailer */
                    $mailer = $this->getContainer()->get(MailerInterface::class);
                    $mailer->send(
                        (new EmailFriendAboutCompanyUpdates($userName, cleanInput(request()->request->get('message')), $company, $update['text_update'], $imageUrl))
                            ->to(...array_map(fn (string $to) => new Address($to), $filteredEmails))
                            ->subjectContext(['[userName]' => $userName])
                    );
                } catch (\Throwable $th) {
                    jsonResponse(translate('email_has_not_been_sent'));
                }

                jsonResponse(translate('seller_updates_successful_email_sent_message'), 'success');

            break;
            case 'share':
                checkPermisionAjax('share_this');
                is_allowed('freq_allowed_send_email_to_user');

                $validator_rules = array(
                    array(
                        'field' => 'message',
                        'label' => translate('general_modal_send_mail_field_message_label_text', null, true),
                        'rules' => array('required' => '', 'max_len[500]' => ''),
                    ),
                    array(
                        'field' => 'id',
                        'label' => translate('seller_updates_dashboard_modal_field_update_label_text', null, true),
                        'rules' => array('required' => ''),
                    ),
                );

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $update_id = (int) $_POST['id'];
                if (empty($update_id) || empty($update = $this->seller_updates->get_update($update_id))) {
                    jsonResponse(translate('seller_updates_not_correct_message'));
                }

                $company = model(Company_Model::class)->get_company(['id_company' => $update['id_company']]);
                if (empty($company)) {
                    jsonResponse(translate('seller_updates_not_correct_message'));
                }

                $userId = privileged_user_id();
                $filteredEmails = model(Followers_Model::class)->getFollowersEmails($userId);

                if (empty($filteredEmails)) {
                    jsonResponse(translate('seller_updates_no_followers_message'));
                }
                /** @var FilesystemProviderInterface  $storageProvider */
                $provider = $this->getContainer()->get(FilesystemProviderInterface::class);
                $storage = $provider->storage('public.storage');
                if (!empty($update['photo_path'])) {
                    $imageUrl = $storage->url(CompanyUpdatesFilePathGenerator::thumbImage($update['id_company'], $update['photo_path'], SellerUpdatesPhotoThumb::MEDIUM()));
                } else {
                    $imageUrl = asset('/public/img/no_image/no-image-166x138.png', 'legacy');
                }
                $userName = user_name_session();

                try {
                    /** @var MailerInterface $mailer */
                    $mailer = $this->getContainer()->get(MailerInterface::class);
                    $mailer->send(
                        (new EmailFriendAboutCompanyUpdates($userName, cleanInput(request()->request->get('message')), $company, $update['text_update'], $imageUrl))
                            ->to(...array_map(fn (int $id, string $to) => new RefAddress((string) $id, new Address($to)), array_column($filteredEmails, 'idu', 'idu'), array_column($filteredEmails, 'email', 'email')))
                            ->subjectContext(['[userName]' => $userName])
                    );
                } catch (\Throwable $th) {
                    jsonResponse(translate('email_has_not_been_sent'));
                }

                jsonResponse(translate('seller_updates_successful_email_sent_message'), 'success');

            break;
        }
    }

    public function ajax_seller_updates_upload_photo()
    {
        checkIsAjax();
        checkIsLoggedAjax();
        checkHaveCompanyAjax();
        checkPermisionAjax('have_updates');
        checkGroupExpire('ajax');

        $request = request();
        /** @var FileUploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('file');
        if (null === $uploadedFile) {
			jsonResponse(translate('seller_pictures_select_file_message'));
		}
        if (is_array($uploadedFile) || !$uploadedFile->isValid()) {
			jsonResponse(translate('seller_pictures_invalid_file_message'));
		}
        $config = 'img.seller_updates.main.rules';
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
        /** @var FilesystemProviderInterface $storageProvider */
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

    public function ajax_seller_updates_delete_photo()
    {
        checkIsAjax();
        checkIsLoggedAjax();
        checkHaveCompanyAjax();
        checkPermisionAjax('have_updates');
        checkGroupExpire('ajax');

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
            jsonResponse(translate('seller_updates_failed_delete_image_message'));
        }

        jsonResponse(null, 'success');
    }

    private function my_seller_updates($updates)
    {
        $output = [];
        /** @var FilesystemProviderInterface  $storageProvider */
        $provider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $storage = $provider->storage('public.storage');
        foreach ($updates as $update) {
            $update_id = (int) $update['id_update'];
            $company_id = (int) $update['id_company'];

            //region Update
            if(!empty($update['photo_path'])) {
                $update_image_name = $update['photo_path'];
                $update_image_url = $storage->url(CompanyUpdatesFilePathGenerator::thumbImage($company_id, $update_image_name, SellerUpdatesPhotoThumb::MEDIUM()));

                $update_image_preview = "
                    <div class=\"flex-card\">
                        <div class=\"main-data-table__item-img image-card3\">
                            <span class=\"link\">
                                <img class=\"image h-auto\" src=\"{$update_image_url}\"" . addQaUniqueIdentifier('seller-updates-my__table_update-picture') . "/>
                            </span>
                        </div>
                    </div>
                ";
            } else {
                $update_image_preview = "
                    <div class=\"flex-card\">
                        <div class=\"main-data-table__item-img image-card3\">
                            <div class=\"tac link\">
                                <span class=\"vam display-ib_i\"" . addQaUniqueIdentifier('seller-updates-my__table_update-picture') . ">&mdash;</span>
                            </div>
                        </div>
                    </div>
                ";
            }
            //endregion Update

            //region Description
            $description = '&mdash;';
            $description_text = $update['text_update'];
            if (!empty($description_text)) {
                $description = "
                    <div class=\"grid-text\">
                        <div class=\"grid-text__item\">
                            <div " . addQaUniqueIdentifier('seller-updates-my__table_update-description') . ">
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
            $edit_button_url = __SITE_URL . "seller_updates/popup_forms/edit_update/{$update_id}";
            $edit_button_text = translate('general_button_edit_text', null, true);
            $edit_button_modal_title = translate('seller_updates_dt_button_edit_update_modal_title', null, true);
            $edit_button = "
                <a
                    rel=\"edit\"
                    class=\"dropdown-item fancybox.ajax fancyboxValidateModal\"
                    data-fancybox-href=\"{$edit_button_url}\"
                    data-title=\"{$edit_button_modal_title}\"
                    " . addQaUniqueIdentifier('seller-updates-my__table_dropdown-menu_edit-btn') . "
                >
                    <i class=\"ep-icon ep-icon_pencil\"></i>
                    <span>{$edit_button_text}</span>
                </a>
            ";
            //endregion Edit button

            //region Delete button
            $delete_button = null;
            $delete_button_text = translate('general_button_delete_text', null, true);
            $delete_button_message = translate('seller_updates_dt_button_delete_update_message', null, true);
            $delete_button = "
                <a class=\"dropdown-item confirm-dialog\"
                    data-message=\"{$delete_button_message}\"
                    data-callback=\"deleteUpdate\"
                    data-update=\"{$update_id}\"
                    " . addQaUniqueIdentifier('seller-updates-my__table_dropdown-menu_delete-btn') . "
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
                    " . addQaUniqueIdentifier('seller-updates-my__table_dropdown-menu_all-btn') . "
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
                        " . addQaUniqueIdentifier('seller-updates-my__table_dropdown_btn') . "
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
                'picture'     => $update_image_preview,
                'description' => $description,
                'created_at'  => getDateFormatIfNotEmpty($update['date_create']),
                'updated_at'  => getDateFormatIfNotEmpty($update['date_update']),
                'actions'     => $actions,
            );
        }

        return $output;
    }
}
