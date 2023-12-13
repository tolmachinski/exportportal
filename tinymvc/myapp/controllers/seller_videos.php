<?php

use App\Common\Contracts\Media\SellerVideosPhotosThumb;
use App\Common\Exceptions\DependencyException;
use App\Common\Exceptions\NotFoundException;
use App\Common\Exceptions\NotUniqueException;
use App\Common\Exceptions\OwnershipException;
use App\Email\EmailFriendAboutVideo;
use App\Filesystem\CompanyVideosFilePathGenerator;
use ExportPortal\Bridge\Mailer\Mime\RefAddress;
use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;
use League\Flysystem\UnableToDeleteFile;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

/**
 * Seller videos controller.
 *
 * @property \Company_Model              $company
 * @property \Followers_model            $followers
 * @property \Notify_Model               $notify
 * @property \Seller_Videos_Model        $seller_videos
 * @property \TinyMVC_Library_VideoThumb $videothumb
 * @property \TinyMVC_Load               $load
 * @property \TinyMVC_View               $view
 * @property \TinyMVC_Library_URI        $uri
 * @property \TinyMVC_Library_Session    $session
 * @property \TinyMVC_Library_Cookies    $cookies
 * @property \TinyMVC_Library_Upload     $upload
 * @property \TinyMVC_Library_validator  $validator
 * @property \TinyMVC_Library_Wall       $wall
 * @property \User_Model                 $user
 * @property \User_Statistic_Model       $statistic
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [18.12.2021]
 * Controller Refactoring
 */
class Seller_Videos_Controller extends TinyMVC_Controller
{
    public function index()
    {
        show_404();
    }

    public function my()
    {
        checkIsLogged();
        checkHaveCompany();
        checkPermision('have_videos');
        checkGroupExpire();

        /** @var Seller_Videos_Model $sellerVideosModel */
        $sellerVideosModel = model(Seller_Videos_Model::class);

        views(
            [
                'new/header_view',
                'new/user/seller/videos/my/index_view',
                'new/footer_view'
            ],
            [
                'videos_categories' => $sellerVideosModel->get_video_categories([
                    'order'      => ['category_title' => 'asc'],
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
        checkPermision('have_videos');
        checkGroupExpire();

        $this->view->assign(array(
            'title'       => translate('seller_videos_categories_dashboard_page_title_text', null, true),
        ));

        $this->view->display('new/header_view');
        $this->view->display('new/user/seller/videos/categories/index_view');
        $this->view->display('new/footer_view');
    }

    public function popup_forms()
    {
        checkIsAjax();
        checkIsLoggedAjaxModal();

        $this->load->model('Seller_Videos_Model', 'seller_videos');

        switch ($this->uri->segment(3)) {
            case 'edit_comment':
                checkPermisionAjaxModal('write_comments');

                $user_id = (int) privileged_user_id();
                $comment_id = (int) $this->uri->segment(4);

                try {
                    $comment = $this->seller_videos->find_video_comment($comment_id, $user_id);
                } catch (NotFoundException $exception) {
                    messageInModal(translate('general_comment_not_exist_message'));
                } catch (OwnershipException $exception) {
                    messageInModal(translate('general_no_permission_message'));
                }

                if (filter_var($comment['moderated'], FILTER_VALIDATE_BOOLEAN)) {
                    messageInModal(translate('general_comment_already_moderated_message'));
                }
                if (filter_var($comment['censored'], FILTER_VALIDATE_BOOLEAN)) {
                    messageInModal(translate('general_comment_censored_message'));
                }

                $this->view->display(
                    'new/user/seller/videos/comment_edit_form_view',
                    array(
                        'action'  => __SITE_URL . 'seller_videos/ajax_videos_operation/edit_comment',
                        'comment' => $comment,
                    )
                );

            break;
            case 'add_comment':
                checkPermisionAjaxModal('write_comments');

                $video_id = (int) $this->uri->segment(4);

                try {
                    $video = $this->seller_videos->find_company_video($video_id);
                } catch (NotFoundException $exception) {
                    messageInModal(translate('seller_videos_not_exist_message'));
                }

                $this->view->display(
                    'new/user/seller/videos/comment_add_form_view',
                    array(
                        'action'   => __SITE_URL . 'seller_videos/ajax_videos_operation/add_comment',
                        'id_video' => $video_id,
                    )
                );

            break;
            case 'email':
                checkPermisionAjaxModal('email_this');

                $video_id = (int) $this->uri->segment(4);

                try {
                    $video = $this->seller_videos->find_company_video($video_id);
                } catch (NotFoundException $exception) {
                    messageInModal(translate('seller_videos_not_exist_message'));
                }

                $this->view->display(
                    'new/user/seller/videos/popup_email_view',
                    array(
                        'action'     => __SITE_URL . 'seller_videos/ajax_videos_operation/email',
                        'max_emails' => config('email_this_max_email_count', 10),
                        'id_video'   => $video_id,
                    )
                );

            break;
            case 'share':
                checkPermisionAjaxModal('share_this');

                $video_id = (int) $this->uri->segment(4);

                try {
                    $video = $this->seller_videos->find_company_video($video_id);
                } catch (NotFoundException $exception) {
                    messageInModal(translate('seller_videos_not_exist_message'));
                }

                $this->view->display(
                    'new/user/seller/videos/popup_share_view',
                    array(
                        'action'    => __SITE_URL . 'seller_videos/ajax_videos_operation/share',
                        'id_video'  => $video_id,
                    )
                );

            break;
            case 'add_video':
                checkHaveCompanyAjaxModal();
                checkPermisionAjaxModal('have_videos');
                checkGroupExpire('modal');

                $this->view->display(
                    'new/user/seller/videos/my/video_form_view',
                    array(
                        'action'            => __SITE_URL . 'seller_videos/ajax_videos_operation/add_video',
                        'category_url'      => __SITE_URL . 'seller_videos/popup_forms/add_category?add_videos=1',
                        'videos_categories' => $this->seller_videos->get_video_categories(array(
                            'order'      => array('category_title' => 'asc'),
                            'conditions' => array(
                                'company' => my_company_id(),
                                'seller'  => privileged_user_id(),
                            ),
                        )),
                    )
                );

            break;
            case 'edit_video':
                checkHaveCompanyAjaxModal();
                checkPermisionAjaxModal('have_videos');
                checkGroupExpire('modal');

                $video_id = (int) $this->uri->segment(4);
                $seller_id = (int) privileged_user_id();
                $company_id = (int) my_company_id();

                try {
                    $video = $this->seller_videos->find_company_video($video_id, $company_id);
                } catch (NotFoundException $exception) {
                    messageInModal(translate('seller_videos_not_exist_message'));
                } catch (OwnershipException $exception) {
                    messageInModal(translate('seller_videos_not_your_video_message'));
                }

                $this->view->display(
                    'new/user/seller/videos/my/video_form_view',
                    array(
                        'action'            => __SITE_URL . "seller_videos/ajax_videos_operation/edit_video/{$video_id}",
                        'category_url'      => __SITE_URL . "seller_videos/popup_forms/add_category?edit_videos={$video_id}",
                        'video'             => $video,
                        'videos_categories' => $this->seller_videos->get_video_categories(array(
                            'order'      => array('category_title' => 'asc'),
                            'conditions' => array(
                                'company' => $company_id,
                                'seller'  => $seller_id,
                            ),
                        )),
                    )
                );

            break;
            case 'add_category':
                checkHaveCompanyAjaxModal();
                checkPermisionAjaxModal('have_videos');
                checkGroupExpire('modal');

                $this->view->display(
                    'new/user/seller/videos/categories/add_form_view',
                    array(
                        'action'          => __SITE_URL . 'seller_videos/ajax_videos_operation/add_category',
                        'add_videos'      => isset($_GET['add_videos']),
                        'edit_videos'     => isset($_GET['edit_videos']) ? (int) cleanInput($_GET['edit_videos']) : false,
                        'add_videos_url'  => __SITE_URL . 'seller_videos/popup_forms/add_video',
                        'edit_videos_url' => __SITE_URL . 'seller_videos/popup_forms/edit_video/' . (int) cleanInput($_GET['edit_videos']),
                    )
                );

            break;
            case 'edit_category':
                checkHaveCompanyAjaxModal();
                checkPermisionAjaxModal('have_videos');
                checkGroupExpire('modal');

                try {
                    $category_id = (int) $this->uri->segment(4);
                    $category = $this->seller_videos->find_category(
                        $category_id,
                        privileged_user_id()
                    );
                } catch (NotFoundException $exception) {
                    messageInModal(translate('seller_videos_category_not_exist_message'));
                } catch (OwnershipException $exception) {
                    messageInModal(translate('seller_videos_category_not_yours_message'));
                }

                $this->view->display(
                    'new/user/seller/videos/categories/edit_form_view',
                    array(
                        'action'   => __SITE_URL . 'seller_videos/ajax_videos_operation/edit_category',
                        'category' => $category,
                    )
                );

            break;
            default:
                show_404();

            break;
        }
    }

    public function ajax_videos_operation()
    {
        checkIsAjax();
        checkIsLoggedAjax();

        $this->load->model('Seller_Videos_Model', 'seller_videos');

        switch ($this->uri->segment(3)) {
            case 'add_category':
                checkHaveCompanyAjax();
                checkPermisionAjax('have_videos');
                checkGroupExpire('ajax');

                $validator_rules = array(
                    array(
                        'field' => 'title',
                        'label' => translate('seller_videos_categories_dashboard_modal_field_name_label_text', null, true),
                        'rules' => array('required' => '', 'max_len[50]' => ''),
                    ),
                );
                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                try {
                    $seller_id = privileged_user_id();
                    $category_title = cleanInput($_POST['title']);
                    $is_created = $this->seller_videos->create_video_category($category_title, $seller_id);
                } catch (NotUniqueException $exception) {
                    jsonResponse(translate('seller_videos_category_name_exists_message'));
                }

                if (!$is_created) {
                    jsonResponse(translate('seller_videos_failed_add_category_message'));
                }

                jsonResponse(translate('seller_news_category_added_message'), 'success');

            break;
            case 'edit_category':
                checkHaveCompanyAjax();
                checkPermisionAjax('have_videos');
                checkGroupExpire('ajax');

                $validator_rules = array(
                    array(
                        'field' => 'title',
                        'label' => translate('seller_videos_categories_dashboard_modal_field_name_label_text', null, true),
                        'rules' => array('required' => '', 'max_len[50]' => ''),
                    ),
                    array(
                        'field' => 'id_category',
                        'label' => translate('seller_videos_categories_dashboard_modal_field_category_label_text', null, true),
                        'rules' => array('required' => '', 'integer' => ''),
                    ),
                );
                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                try {
                    $seller_id = privileged_user_id();
                    $category_id = (int) $_POST['id_category'];
                    $category_title = cleanInput($_POST['title']);
                    $category = $this->seller_videos->find_category($category_id, $seller_id);
                    $is_updated = $this->seller_videos->rename_video_category($category_id, $category_title, $seller_id);
                } catch (NotFoundException $exception) {
                    jsonResponse(translate('seller_videos_category_not_exist_message'));
                } catch (OwnershipException $exception) {
                    jsonResponse(translate('seller_videos_category_not_yours_message'));
                } catch (NotUniqueException $exception) {
                    jsonResponse(translate('seller_videos_category_not_yours_message'));
                }

                if (!$is_updated) {
                    jsonResponse(translate('seller_videos_failed_to_update_message'));
                }

                jsonResponse(translate('seller_videos_category_updated_message'), 'success');

            break;
            case 'delete_category':
                checkHaveCompanyAjax();
                checkPermisionAjax('have_videos');
                checkGroupExpire('ajax');

                try {
                    $seller_id = privileged_user_id();
                    $category_id = (int) $_POST['category'];
                    $category = $this->seller_videos->find_category($category_id, $seller_id);
                    $is_deleted = $this->seller_videos->remove_video_category($category_id);
                } catch (NotFoundException $exception) {
                    jsonResponse(translate('seller_videos_category_not_exist_message'));
                } catch (OwnershipException $exception) {
                    jsonResponse(translate('seller_videos_category_not_yours_message'));
                } catch (DependencyException $exception) {
                    jsonResponse(translate('seller_videos_category_used_message'));
                }

                if (!$is_deleted) {
                    jsonResponse(translate('seller_videos_failed_delete_message'));
                }

                jsonResponse(translate('seller_videos_category_deleted'), 'success');

            break;
            case 'get_categories':
                checkHaveCompanyAjax();
                checkPermisionAjax('have_videos');
                checkGroupExpire('ajax');

                jsonResponse('', 'success', array(
                    'categories' => $this->seller_videos->get_video_categories(array(
                        'order'      => array('category_title' => 'asc'),
                        'conditions' => array(
                            'seller' => privileged_user_id(),
                        ),
                    )),
                ));

            break;
            case 'add_video':
                checkHaveCompanyAjax();
                checkPermisionAjax('have_videos');
                checkGroupExpire('ajax');
                is_allowed('freq_allowed_companies_posts');

                $this->load->model('User_Statistic_Model', 'statistic');
                $this->load->model('Company_Model', 'company');
                $this->load->library('videothumb');

                $validator_rules = array(
                    array(
                        'field' => 'title',
                        'label' => translate('seller_videos_dashboard_modal_field_title_label_text', null, true),
                        'rules' => array('required' => '', 'max_len[200]' => ''),
                    ),
                    array(
                        'field' => 'link',
                        'label' => translate('seller_videos_dashboard_modal_field_link_label_text', null, true),
                        'rules' => array('required' => '', 'valid_url' => '', 'max_len[200]' => ''),
                    ),
                    array(
                        'field' => 'text',
                        'label' => translate('seller_videos_dashboard_modal_field_description_label_text', null, true),
                        'rules' => array('required' => '', 'max_len[2000]' => ''),
                    ),
                );
                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                try {
                    $seller_id = (int) privileged_user_id();
                    $company_id = (int) my_company_id();
                    $category_id = (int) $_POST['category'];
                    $category_new = cleanInput($_POST['new_category']);

                    if (empty($category_id) && !empty($category_new)) {

                        if ($this->seller_videos->has_video_category($category_new, ['seller' => $seller_id])) {
                            jsonResponse(translate('seller_videos_category_name_exists_message'));
                        }

                        $category_id = (int) $this->seller_videos->add_video_category(array(
                            'id_seller'     => $seller_id,
                            'category_title' => $category_new,
                        ));
                    }

                    if (!$category_id) {
                        jsonResponse(translate('seller_videos_failed_add_category_message'));
                    }

                   $category = $this->seller_videos->find_category($category_id, $seller_id);
                } catch (NotFoundException $exception) {
                    jsonResponse(translate('seller_videos_category_not_exist_message'));
                } catch (OwnershipException $exception) {
                    jsonResponse(translate('seller_videos_category_not_yours_message'));
                }

                /**
                 * @todo Refactoring Library [2022-06-02]
                 */

                $video_data = $this->videothumb->process($_POST['link']);
                $video_link = $this->videothumb->getVID($_POST['link']);
                if (!empty($video_data['error'])) {
                    jsonResponse($video_data['error']);
                }

                /** @var FilesystemProviderInterface  $storageProvider */
                $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                $publicDisk = $storageProvider->storage('public.storage');
                $publicPrefixer = $storageProvider->prefixer('public.storage');
                $path = CompanyVideosFilePathGenerator::videosFolder($company_id);
                $publicDisk->createDirectory($path);
                $thumbs = $this->upload->copy_images_new(array(
                    'images'      => array($video_data['image']),
                    'resize'      => config('img.companies.videos.resize_inline', '800xR'),
                    'thumbs'      => config('img.companies.videos.thumbs_inline', '140xR,220xR,400xR'),
                    'destination' => $publicPrefixer->prefixPath($path),
                ));

                if (!empty($thumbs['errors'])) {
                    jsonResponse($thumbs['errors']);
                }

                try {
                    $video_id = $is_created = $this->seller_videos->create_video(
                        $category_id,
                        $company_id,
                        $seller_id,
                        cleanInput($_POST['title']),
                        cleanInput($_POST['text']),
                        cleanInput($_POST['link']),
                        arrayGet($video_data, 'v_id'),
                        arrayGet($video_link, 'type'),
                        arrayGet($thumbs, '0.new_name')
                    );
                } catch (NotUniqueException $exception) {
                    try {
                        $publicDisk->delete(
                            CompanyVideosFilePathGenerator::videosPath($company_id, arrayGet($thumbs, '0.new_name'))
                        );
                    } catch(UnableToDeleteFile $e){
                        // nothing
                    }

                    jsonResponse(translate('seller_videos_title_already_exists_message'));
                }

                if (!$is_created) {
                    jsonResponse(translate('seller_videos_cannot_add_video_message'));
                }

                $this->statistic->set_users_statistic(array($seller_id => array('company_posts_videos' => 1)));
                if (!empty($_POST['post_wall'])) {
                    library('wall')->add(array(
                        'operation'  => 'add',
                        'type'       => 'video',
                        'id_item'    => $video_id,
                        'id_company' => $company_id,
                        'id_seller'  => $seller_id,
                    ));
                }

                //region block user content
                $seller_info = model('user')->getSimpleUser(privileged_user_id());
                if(in_array($seller_info['status'], array('blocked', 'restricted'))){
                    model('blocking')->change_blocked_users_videos(array(
                        'blocked' => 0,
                        'users_list' => array(privileged_user_id())
                    ), array('blocked' => 2));
                }
                //endregion block user content

                jsonResponse(translate('seller_videos_added_successfully_message'), 'success');

            break;
            case 'edit_video':
                checkHaveCompanyAjax();
                checkPermisionAjax('have_videos');
                checkGroupExpire('ajax');
                is_allowed('freq_allowed_companies_posts');

                $this->load->library('videothumb');

                $validator_rules = array(
                    array(
                        'field' => 'title',
                        'label' => translate('seller_videos_dashboard_modal_field_title_label_text', null, true),
                        'rules' => array('required' => '', 'max_len[200]' => ''),
                    ),
                    array(
                        'field' => 'link',
                        'label' => translate('seller_videos_dashboard_modal_field_link_label_text', null, true),
                        'rules' => array('required' => '', 'valid_url' => '', 'max_len[200]' => ''),
                    ),
                    array(
                        'field' => 'text',
                        'label' => translate('seller_videos_dashboard_modal_field_description_label_text', null, true),
                        'rules' => array('required' => '', 'max_len[2000]' => ''),
                    ),
                );
                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }


                $seller_id = (int) privileged_user_id();
                $company_id = (int) my_company_id();
                $category_id = (int) $_POST['category'];
                $video_id = (int) $_POST['video'];

                try {
                    $category_new = cleanInput($_POST['new_category']);

                    if (empty($category_id) && !empty($category_new)) {

                        if ($this->seller_videos->has_video_category($category_new)) {
                            jsonResponse(translate('seller_videos_category_name_exists_message'));
                        }

                        $category_id = (int) $this->seller_videos->add_video_category(array(
                            'id_seller'     => $seller_id,
                            'category_title' => $category_new,
                        ));
                    }

                    if (!$category_id) {
                        jsonResponse(translate('seller_videos_failed_add_category_message'));
                    }

                    $video = $this->seller_videos->find_company_video($video_id, $company_id);
                    $category = $this->seller_videos->find_category($category_id, $seller_id);

                } catch (NotFoundException $exception) {
                    jsonResponse(
                        empty($video)
                            ? translate('seller_videos_not_exist_message')
                            : translate('seller_videos_category_not_exist_message')
                    );
                } catch (OwnershipException $exception) {
                    jsonResponse(
                        empty($video)
                            ? translate('seller_videos_not_your_video_message')
                            : translate('seller_videos_category_not_yours_message')
                    );
                }

                /** @var FilesystemProviderInterface  $storageProvider */
                $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                $publicDisk = $storageProvider->storage('public.storage');
                $publicPrefixer = $storageProvider->prefixer('public.storage');

                $video_title = cleanInput($_POST['title']);
                $video_description = cleanInput($_POST['text']);
                $video_url = $_POST['link'] !== $video['url_video'] ? cleanInput($_POST['link']) : null;
                $video_type = null;
                $video_thumb = null;
                $video_link_id = null;

                if (empty($video['url_video']) || null !== $video_url) {
                    $video_link = $this->videothumb->getVID($video_url);
                    $video_data = $this->videothumb->process($video_url);
                    if (!empty($video_data['error'])) {
                        jsonResponse($video_data['error']);
                    }
                    $path = CompanyVideosFilePathGenerator::videosFolder($company_id);
                    $publicDisk->createDirectory($path);
                    $thumbs = $this->upload->copy_images_new(array(
                        'images'      => array($video_data['image']),
                        'resize'      => config('img.companies.videos.resize_inline', '800xR'),
                        'thumbs'      => config('img.companies.videos.thumbs_inline', '140xR,220xR,400xR'),
                        'destination' => $publicPrefixer->prefixPath($path),
                    ));

                    if (!empty($thumbs['errors'])) {
                        jsonResponse($thumbs['errors']);
                    }

                    $video_type = arrayGet($video_link, 'type');
                    $video_thumb = arrayGet($thumbs, '0.new_name');
                    $video_link_id = arrayGet($video_data, 'v_id');

                    $oldPath = CompanyVideosFilePathGenerator::videosPath($company_id, $video['image_video']);
                    try {
                        $publicDisk->delete($oldPath);
                        foreach(SellerVideosPhotosThumb::cases() as $thumb){
                            $publicDisk->delete(CompanyVideosFilePathGenerator::thumbImage($company_id, $video['image_video'], $thumb));
                        }
                    } catch (UnableToDeleteFile $e) {
                        jsonResponse(translate('seller_videos_cannot_delete_now_message'));
                    }
                }

                try {
                    $is_updated = $this->seller_videos->change_video(
                        $video_id,
                        $category_id,
                        $company_id,
                        $seller_id,
                        $video_title,
                        $video_description,
                        $video_url,
                        $video_link_id,
                        $video_type,
                        $video_thumb
                    );
                } catch (NotUniqueException $exception) {
                    jsonResponse(translate('seller_videos_title_already_exists_message'));
                }

                if (!$is_updated) {
                    jsonResponse(translate('seller_videos_cannot_update_now_message'));
                }

                try {
                    $publicDisk->delete(CompanyVideosFilePathGenerator::videosPath($company_id, $video['image_video']));
                } catch (UnableToDeleteFile $e){
                    //nothing
                }
                if (!empty($_POST['post_wall'])) {
                    library('wall')->add(array(
                        'operation'  => 'edit',
                        'type'       => 'video',
                        'id_item'    => $video_id,
                        'id_company' => $company_id,
                        'id_seller'  => $seller_id,
                    ));
                }

                jsonResponse(translate('seller_videos_updated_successfully_message'), 'success', array(
                    'newTitle'       => $video_title,
                    'newDescription' => truncWords($video_description, 15),
                    'newThumb'       => null !== $video_thumb ? $video_thumb : $video['image_video'],
                    'video'          => $video_id,
                ));

            break;
            case 'delete_video':
                checkHaveCompanyAjax();
                checkPermisionAjax('have_videos');
                checkGroupExpire('ajax');

                $this->load->model('User_Statistic_Model', 'statistic');
                /** @var FilesystemProviderInterface */
                $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                $publicDisk = $storageProvider->storage('public.storage');

                $video_id = (int) $_POST['video'];
                $seller_id = (int) privileged_user_id();
                $company_id = (int) my_company_id();

                try {
                    $video = $this->seller_videos->find_company_video($video_id, $company_id);
                } catch (NotFoundException $exception) {
                    jsonResponse(translate('seller_videos_not_exist_message'));
                } catch (OwnershipException $exception) {
                    jsonResponse(translate('seller_videos_not_your_video_message'));
                }

                if (!empty($video['image_video'])) {
                    $path = CompanyVideosFilePathGenerator::videosPath($company_id, $video['image_video']);
                    try {
                        $publicDisk->delete($path);
                        foreach(SellerVideosPhotosThumb::cases() as $thumb){
                            $publicDisk->delete(CompanyVideosFilePathGenerator::thumbImage($company_id, $video['image_video'], $thumb));
                        }
                    } catch (UnableToDeleteFile $e) {
                        jsonResponse(translate('seller_videos_cannot_delete_now_message'));
                    }
                }
                /**
                 * @todo Refactoring Library
                 */

                if (!$this->seller_videos->delete_video($video_id)) {
                    jsonResponse(translate('seller_videos_cannot_delete_now_message'));
                }

                library('wall')->remove(array(
                    'type'       => 'video',
                    'id_item'    => $video_id
                ));

                $this->seller_videos->delete_all_video_comments($video_id);
                $this->statistic->set_users_statistic(array($seller_id => array('company_posts_videos' => -1)));

                jsonResponse(translate('seller_videos_deleted_successfully_message'), 'success', array('video' => $video_id));

            break;
            case 'delete_videos':
                checkHaveCompanyAjax();
                checkPermisionAjax('have_videos');
                checkGroupExpire('ajax');

                $this->load->model('User_Statistic_Model', 'statistic');

                /** @var FilesystemProviderInterface */
                $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                $publicDisk = $storageProvider->storage('public.storage');

                $videos_ids = array_map('intval', $_POST['video']);
                if (empty($videos_ids)) {
                    jsonResponse(translate('seller_videos_select_one_video_message'));
                }

                $seller_id = (int) privileged_user_id();
                $company_id = (int) my_company_id();
                $videos = $this->seller_videos->get_videos(array(
                    'conditions' => array(
                        'seller'  => $seller_id,
                        'company' => $company_id,
                        'videos'  => $videos_ids,
                    ),
                ));
                if (empty($videos)) {
                    jsonResponse(translate('seller_videos_must_select_for_delete_message'));
                }

                $removal_queue = array_column($videos, 'id_video');
                foreach ($videos as $video) {
                    if ((int) $company_id !== (int) $video['id_company']) {
                        jsonResponse(translate('seller_videos_not_your_video_message'));
                    }

                    if (!empty($video['image_video'])) {
                        $path = CompanyVideosFilePathGenerator::videosPath($company_id, $video['image_video']);
                        try {
                            $publicDisk->delete($path);
                            foreach(SellerVideosPhotosThumb::cases() as $thumb){
                                $publicDisk->delete(CompanyVideosFilePathGenerator::thumbImage($company_id, $video['image_video'], $thumb));
                            }
                        } catch (UnableToDeleteFile $e) {
                            jsonResponse(translate('seller_videos_cannot_delete_now_message'));
                        }
                    }
                }

                /**
                 * @todo Refactoring Library
                 */

                if (!$this->seller_videos->delete_videos($removal_queue)) {
                    jsonResponse(translate(translate('seller_videos_cannot_delete_videos_message')));
                }

                library('wall')->remove(array(
                    'type'       => 'video',
                    'id_item'    => $removal_queue,
                ));

                $this->statistic->set_users_statistic(array($seller_id => array('company_posts_videos' => -count($removal_queue))));
                $this->seller_videos->delete_all_video_comments($removal_queue);

                jsonResponse(translate('seller_videos_deleted_videos_mesage'), 'success', array('video' => $removal_queue));

            break;
            case 'add_comment':
                checkPermisionAjax('write_comments');

                is_allowed('freq_allowed_add_comment');

                $validator_rules = array(
                    array(
                        'field' => 'message',
                        'label' => translate('general_modal_comment_field_message_label_text', null, true),
                        'rules' => array('required' => '', 'max_len[500]' => ''),
                    ),
                    array(
                        'field' => 'video',
                        'label' => translate('seller_videos_dashboard_modal_field_video_label_text', null, true),
                        'rules' => array('required' => ''),
                    ),
                );
                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $user_id = (int) privileged_user_id();
                $video_id = (int) $_POST['video'];

                try {
                    $video = $this->seller_videos->find_company_video($video_id);
                } catch (NotFoundException $exception) {
                    jsonResponse(translate('seller_videos_not_exist_message'));
                }

                if (
                    !(
                        $comment_id = $this->seller_videos->add_video_comment(array(
                            'id_user'          => $user_id,
                            'id_video'         => $video_id,
                            'reply_to_comment' => 0,
                            'message_comment'  => cleanInput($_POST['message']),
                        ))
                    )
                ) {
                    jsonResponse(translate('seller_news_cannot_add_comments_message'));
                }

                $this->seller_videos->update_video_comment_counter($video_id, 1);

                jsonResponse(translate('seller_videos_comment_added_successfully_message'), 'success');

            break;
            case 'edit_comment':
                checkPermisionAjax('write_comments');

                is_allowed('freq_allowed_add_comment');

                $validator_rules = array(
                    array(
                        'field' => 'message',
                        'label' => translate('general_modal_comment_field_title_placeholder_text', null, true),
                        'rules' => array('required' => '', 'max_len[500]' => ''),
                    ),
                    array(
                        'field' => 'comment',
                        'label' => translate('general_modal_comment_field_comment_label_text', null, true),
                        'rules' => array('required' => ''),
                    ),
                );
                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $user_id = (int) privileged_user_id();
                $comment_id = (int) $_POST['comment'];

                try {
                    $comment = $this->seller_videos->find_video_comment($comment_id, $user_id);
                } catch (NotFoundException $exception) {
                    jsonResponse(translate('general_comment_not_exist_message'));
                } catch (OwnershipException $exception) {
                    jsonResponse(translate('general_no_permission_message'));
                }

                if (filter_var($comment['moderated'], FILTER_VALIDATE_BOOLEAN)) {
                    jsonResponse(translate('general_comment_already_moderated_message'));
                }
                if (filter_var($comment['censored'], FILTER_VALIDATE_BOOLEAN)) {
                    jsonResponse(translate('general_comment_censored_message'));
                }

                $comment_message = cleanInput($_POST['message']);
                if (
                    !$this->seller_videos->update_comment($comment_id, array(
                        'message_comment' => $comment_message,
                    ))
                ) {
                    jsonResponse(translate('seller_videos_cannot_update_comments_message'));
                }

                jsonResponse(translate('seller_videos_comment_successfully_updated_message'), 'success', array(
                    'parent'          => 0,
                    'comment'         => $comment_id,
                    'message_comment' => $comment_message,
                ));

            break;
            case 'censor_comment':
                checkPermisionAjax('moderate_content');

                $comment_id = (int) $_POST['comment'];

                try {
                    $comment = $this->seller_videos->find_video_comment($comment_id);
                } catch (NotFoundException $exception) {
                    jsonResponse(translate('general_comment_not_exist_message'));
                }

                if (filter_var($comment['censored'], FILTER_VALIDATE_BOOLEAN)) {
                    jsonResponse(translate('general_comment_censored_message'));
                }

                if (!$this->seller_videos->censor_comment($comment_id)) {
                    jsonResponse(translate('seller_videos_cannot_censore_comments_now_message'));
                }

                jsonResponse(translate('seller_videos_comment_censored_message'), 'success', array(
                    'parent'  => (int) $comment['reply_to_comment'],
                    'comment' => $comment_id,
                ));

            break;
            case 'moderate_comment':
                checkPermisionAjax('moderate_content');

                $comment_id = (int) $_POST['comment'];

                try {
                    $comment = $this->seller_videos->find_video_comment($comment_id);
                } catch (NotFoundException $exception) {
                    jsonResponse(translate('general_comment_not_exist_message'));
                }

                if (filter_var($comment['moderated'], FILTER_VALIDATE_BOOLEAN)) {
                    jsonResponse(translate('general_comment_already_moderated_message'));
                }

                if (!$this->seller_videos->moderate_comment($comment_id)) {
                    jsonResponse(translate('seller_videos_cannot_moderate_now_messag'));
                }

                jsonResponse(translate('seller_comment_moderated_message'), 'success', array(
                    'parent'     => $comment['reply_to_comment'],
                    'id_comment' => $comment_id,
                ));

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
                        'label' => translate('seller_videos_dashboard_modal_field_video_label_text', null, true),
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

                $videoId = (int) $_POST['id'];

                try {
                    $video = $this->seller_videos->find_company_video($videoId);
                } catch (NotFoundException $exception) {
                    jsonResponse(translate('seller_videos_not_exist_message'));
                }

                $companyId = (int) $video['id_company'];
                $company = model(Company_Model::class)->get_company(['id_company' => $companyId]);

                if (empty($company)) {
                    jsonResponse(translate('general_company_not_exist_message'));
                }

                $userName = user_name_session();

                try {
                    /** @var FilesystemProviderInterface  $storageProvider */
                    $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                    $publicDisk = $storageProvider->storage('public.storage');
                    $pathToFile = CompanyVideosFilePathGenerator::thumbImage($video['id_company'], $video['image_video'], SellerVideosPhotosThumb::BIG());
                    /** @var MailerInterface $mailer */
                    $mailer = $this->getContainer()->get(MailerInterface::class);
                    $mailer->send(
                        (new EmailFriendAboutVideo($userName, cleanInput(request()->request->get('message')), $company, $video, $publicDisk->url($pathToFile)))
                            ->to(...array_map(fn (string $to) => new Address($to), $filteredEmails))
                            ->subjectContext(['[userName]' => $userName])
                    );
                } catch (\Throwable $th) {
                    jsonResponse(translate('email_has_not_been_sent'));
                }

                jsonResponse(translate('seller_videos_email_sent_message'), 'success');

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
                        'label' => translate('seller_videos_dashboard_modal_field_video_label_text', null, true),
                        'rules' => array('required' => ''),
                    ),
                );

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $videoId = (int) $_POST['id'];

                try {
                    $video = $this->seller_videos->find_company_video($videoId);
                } catch (NotFoundException $exception) {
                    jsonResponse(translate('seller_videos_not_exist_message'));
                }

                $companyId = (int) $video['id_company'];
                $company = model(Company_Model::class)->get_company(array('id_company' => $companyId));

                if (empty($company)) {
                    jsonResponse(translate('general_company_not_exist_message'));
                }

                $userId = privileged_user_id();
                $filteredEmails = model(Followers_Model::class)->getFollowersEmails($userId);

                if (empty($filteredEmails)) {
                    jsonResponse(translate('seller_videos_no_followers_message'));
                }

                $userName = user_name_session();

                try {
                    /** @var FilesystemProviderInterface  $storageProvider */
                    $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                    $publicDisk = $storageProvider->storage('public.storage');
                    $pathToFile = CompanyVideosFilePathGenerator::thumbImage($video['id_company'], $video['image_video'], SellerVideosPhotosThumb::BIG());
                    /** @var MailerInterface $mailer */
                    $mailer = $this->getContainer()->get(MailerInterface::class);
                    $mailer->send(
                        (new EmailFriendAboutVideo($userName, cleanInput(request()->request->get('message')), $company, $video, $publicDisk->url($pathToFile)))
                            ->to(...array_map(fn (int $id, string $to) => new RefAddress((string) $id, new Address($to)), array_column($filteredEmails, 'idu', 'idu'), array_column($filteredEmails, 'email', "email")))
                            ->subjectContext(['[userName]' => $userName])
                    );
                } catch (\Throwable $th) {
                    jsonResponse(translate('email_has_not_been_sent'));
                }

                jsonResponse(translate('seller_videos_email_sent_message'), 'success');

            break;
            default:
                show_404();

            break;
        }
    }

    public function ajax_video_list_dt()
    {
        checkIsAjax();
        checkIsLoggedAjaxDT();
        checkHaveCompanyAjaxDT();
        checkPermisionAjaxDT('have_videos');
        checkGroupExpire('dt');

        /** @var Seller_Videos_Model $sellerVideosModel */
        $sellerVideosModel = model(Seller_Videos_Model::class);

        $request = request()->request;

        $skip = $request->getInt('iDisplayStart');
        $limit = $request->getInt('iDisplayLength');
        $with = array('categories' => true, 'companies' => true);
        $columns = [
            'VIDEOS.*',
            'COMPANIES.logo_company',
            'COMPANIES.index_name',
            'COMPANIES.name_company',
            'COMPANIES.type_company',
            'COMPANIES.visible_company',
            'CATEGORIES.category_title',
        ];

        $conditions = array_merge(
            dtConditions($request->all(), [
                ['as' => 'search',                      'key' => 'keywords',                    'type' => 'cleanInput|cut_str:200'],
                ['as' => 'created_from',                'key' => 'created_from',                'type' => 'concat: 00:00:00|getDateFormat:m/d/Y H:i:s,Y-m-d H:i:s'],
                ['as' => 'created_to',                  'key' => 'created_to',                  'type' => 'concat: 23:59:59|getDateFormat:m/d/Y H:i:s,Y-m-d H:i:s'],
                ['as' => 'updated_from',                'key' => 'updated_from',                'type' => 'concat: 00:00:00|getDateFormat:m/d/Y H:i:s,Y-m-d H:i:s'],
                ['as' => 'updated_to',                  'key' => 'updated_to',                  'type' => 'concat: 23:59:59|getDateFormat:m/d/Y H:i:s,Y-m-d H:i:s'],
                ['as' => 'category',                    'key' => 'videos_category',             'type' => 'int'],
                ['as' => 'source',                      'key' => 'source',                      'type' => fn ($value) => in_array($value, ['youtube', 'vimeo']) ? $value : null],
            ]),
            [
                'company' => my_company_id(),
                'seller'  => privileged_user_id(),
            ]
        );

        $order = [
            'add_date_video'    => 'desc',
            'title_video'       => 'asc',
        ];

        $params = compact('conditions', 'order', 'limit', 'with', 'skip');
        $data['videos_list'] = $sellerVideosModel->get_videos(array_merge($params, compact('columns')));
        $totalVideos = $sellerVideosModel->count_videos($params);
        $output = array(
            'sEcho'                => $request->getInt('sEcho'),
            'iTotalRecords'        => $totalVideos,
            'iTotalDisplayRecords' => $totalVideos,
            'aaData'               => [],
        );

        if (!empty($data['videos_list'])) {
            $output['aaData'] = $this->my_seller_videos($data['videos_list']);
        }

        jsonResponse('', 'success', $output);
    }

    public function ajax_videos_categories_dt()
    {
        checkIsAjax();
        checkIsLoggedAjaxDT();
        checkHaveCompanyAjaxDT();
        checkPermisionAjaxDT('have_videos');
        checkGroupExpire('dt');

        $this->load->model('Seller_Videos_Model', 'seller_videos');

        $skip = isset($_POST['iDisplayStart']) ? (int) cleanInput($_POST['iDisplayStart']) : null;
        $limit = isset($_POST['iDisplayLength']) ? (int) cleanInput($_POST['iDisplayLength']) : null;
        $order = array();

        $conditions = array_merge(
            [
                'company' => my_company_id(),
                'seller'  => privileged_user_id(),
            ],
            dtConditions($_POST, [
                ['as' => 'search',              'key' => 'keywords',            'type' => 'cleanInput|cut_str:50'],
                ['as' => 'created_from',        'key' => 'created_from',        'type' => 'concat: 00:00:00|getDateFormat:m/d/Y H:i:s,Y-m-d H:i:s'],
                ['as' => 'created_to',          'key' => 'created_to',          'type' => 'concat: 23:59:59|getDateFormat:m/d/Y H:i:s,Y-m-d H:i:s'],
                ['as' => 'updated_from',        'key' => 'updated_from',        'type' => 'concat: 00:00:00|getDateFormat:m/d/Y H:i:s,Y-m-d H:i:s'],
                ['as' => 'updated_to',          'key' => 'updated_to',          'type' => 'concat: 23:59:59|getDateFormat:m/d/Y H:i:s,Y-m-d H:i:s'],
            ])
        );

        $order = array_column(dt_ordering($_POST, array(
            'category'   => 'category_title',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at',
        )), 'direction', 'column');

        $params = compact('conditions', 'order', 'limit', 'skip');
        $data['categories'] = $this->seller_videos->get_video_categories($params);
        $records_total = $this->seller_videos->count_video_categories($params);
        $output = array(
            'sEcho'                => (int) $_POST['sEcho'],
            'iTotalRecords'        => $records_total,
            'iTotalDisplayRecords' => $records_total,
            'aaData'               => array(),
        );

        if (!empty($data['categories'])) {
            $output['aaData'] = $this->videos_categories($data['categories']);
        }

        jsonResponse('', 'success', $output);
    }

    private function my_seller_videos($videos)
    {
        $output = [];
        /** @var FilesystemProviderInterface  $storageProvider */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $publicDisk = $storageProvider->storage('public.storage');
        foreach ($videos as $video) {
            $video_id = (int) $video['id_video'];
            $company_id = (int) $video['id_company'];
            $video_title = cleanOutput($video['title_video']);

            //region Video
            $video_image_name = $video['image_video'];
            $video_category_name = cleanOutput($video['category_title']);
            $video_comments_text = translate('seller_videos_dashboard_dt_video_comments_text', array('{amount}' => (int) $video['comments_count']), true);
            $video_image_url = $publicDisk->url(CompanyVideosFilePathGenerator::thumbImage($company_id, $video_image_name, SellerVideosPhotosThumb::MEDIUM()));
            $video_url = getCompanyUrl($video) . '/video/' . strForUrl($video_title) . "-{$video_id}";
            $video_link = get_video_link($video['short_url_video'], $video['source_video']);
            $video_preview = "
                <div class=\"flex-card\">
                    <div class=\"flex-card__fixed spersonal-pictures__img spersonal-pictures__img--w mh-100 image-card2\" " . addQaUniqueIdentifier('seller-videos-my__table_item-image') . ">
                        <a class=\"link fancybox.iframe fancyboxVideo\"
                            rel=\"videoItem\"
                            href=\"{$video_link}\"
                            data-h=\"350\"
                            data-title=\"{$video_title}\">
                            <div class=\"video-play\">
                                <div class=\"video-play__circle\"></div>
                                <i class=\"ep-icon ep-icon_videos\"></i>
                            </div>
                            <img class=\"image\" src=\"{$video_image_url}\" alt=\"{$video_title}\"/>
                        </a>
                    </div>
                    <div class=\"spersonal-pictures__desc2 flex-card__float\">
                        <div class=\"main-data-table__item-ttl\" " . addQaUniqueIdentifier('seller-videos-my__table_item-title') . ">
                            <a class=\"link display-ib link-black txt-medium\" href=\"{$video_url}\"
                                title=\"{$video_title}\"
                                target=\"_blank\">
                                {$video_title}
                            </a>
                        </div>
                        <div class=\"links-black\" " . addQaUniqueIdentifier('seller-videos-my__table_item-name') . ">{$video_category_name}</div>
                        <div class=\"txt-gray\" " . addQaUniqueIdentifier('seller-videos-my__table_item-comments') . ">{$video_comments_text}</div>
                    </div>
                </div>
            ";
            //endregion Video

            //region Description
            $description = '&mdash;';
            if (!empty($video['description_video'])) {
                $description_text = cleanOutput(strLimit($video['description_video'], 300));
                $description = "
                    <div class=\"grid-text\">
                        <div class=\"grid-text__item\" " . addQaUniqueIdentifier('seller-videos-my__table_item-description') . ">
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
            $edit_button_url = __SITE_URL . "seller_videos/popup_forms/edit_video/{$video_id}";
            $edit_button_text = translate('general_button_edit_text', null, true);
            $edit_button_modal_title = translate('seller_videos_dashboard_dt_button_edit_video_modal_title', null, true);
            $edit_button = "
                <a rel=\"edit\"
                    " . addQaUniqueIdentifier('seller-videos-my__table_item-dropdown-menu_edit-btn') . "
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
            $delete_button_message = translate('seller_videos_dashboard_dt_button_delete_video_message', null, true);
            $delete_button = "
                <a class=\"dropdown-item confirm-dialog\"
                    " . addQaUniqueIdentifier('seller-videos-my__table_item-dropdown-menu_delete-btn') . "
                    data-message=\"{$delete_button_message}\"
                    data-callback=\"deleteVideo\"
                    data-video=\"{$video_id}\">
                    <i class=\"ep-icon ep-icon_trash-stroke\"></i>
                    <span>{$delete_button_text}</span>
                </a>
            ";
            //endregion Delete button

            //region All button
            $all_button_text = translate('general_dt_info_all_text', null, true);
            $all_button = "
                <a class=\"dropdown-item d-none d-md-block d-lg-block d-xl-none call-function\"
                " . addQaUniqueIdentifier('seller-videos-my__table_item-dropdown-menu_info-btn') . "
                    data-callback=\"dataTableAllInfo\"
                    target=\"_blank\">
                    <i class=\"ep-icon ep-icon_info-stroke\"></i>
                    <span>{$all_button_text}</span>
                </a>
            ";
            //endregion All button

            $actions = "
                <div class=\"dropdown\">
                    <a class=\"dropdown-toggle\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\" " . addQaUniqueIdentifier('seller-videos-my__table_item_dropdown-btn') . ">
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
                'video'       => $video_preview,
                'description' => $description,
                'created_at'  => getDateFormatIfNotEmpty($video['add_date_video']),
                'updated_at'  => getDateFormatIfNotEmpty($video['edit_date_video']),
                'actions'     => $actions,
            );
        }

        return $output;
    }

    private function videos_categories($categories)
    {
        $output = array();
        foreach ($categories as $category) {
            $category_id = (int) $category['id_category'];
            $category_title = cleanOutput($category['category_title']);

            //region Category
            $category_preview = "
                <div class=\"grid-text\">
                    <div class=\"grid-text__item\" " . addQaUniqueIdentifier('seller-videos-categories__table_category-title') . ">
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
            $edit_button_url = __SITE_URL . "seller_videos/popup_forms/edit_category/{$category_id}";
            $edit_button_text = translate('general_button_edit_text', null, true);
            $edit_button_modal_title = translate('seller_videos_categories_dashboard_dt_button_edit_category_modal_title', null, true);
            $edit_button = "
                <a rel=\"edit\"
                    " . addQaUniqueIdentifier('seller-videos-categories__table_item-dropdown-menu_edit-btn') . "
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
            $delete_button_message = translate('seller_videos_categories_dashboard_dt_button_delete_category_message', null, true);
            $delete_button = "
                <a class=\"dropdown-item confirm-dialog\"
                    " . addQaUniqueIdentifier('seller-videos-categories__table_item-dropdown-menu_delete-btn') . "
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
                    <a class=\"dropdown-toggle\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\" " . addQaUniqueIdentifier('seller-videos-categories__table_item_dropdown-btn') . ">
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
