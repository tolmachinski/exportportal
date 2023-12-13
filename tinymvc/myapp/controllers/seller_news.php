<?php

use App\Common\Contracts\Media\SellerNewsPhotoThumb;
use App\Common\DependencyInjection\ServiceLocator\LibraryLocator;
use App\Common\Validation\ConstraintViolationInterface;
use App\Common\Validation\ValidationException;
use App\Filesystem\FilePathGenerator;
use TinyMVC_Library_Image_intervention as LegacyImageHandler;
use App\Email\EmailFriendAboutNews;
use App\Filesystem\CompanyNewsFilePathGenerator;
use ExportPortal\Bridge\Mailer\Mime\RefAddress;
use ExportPortal\Contracts\Filesystem\Exception\ReadException;
use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToWriteFile;
use Symfony\Component\HttpFoundation\File\UploadedFile as FileUploadedFile;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 */
class Seller_News_Controller extends TinyMVC_Controller
{
    private $breadcrumbs = array();

    public function my()
    {
        checkPermision('have_news');

        if (!i_have_company()) {
            $this->session->setMessages(translate('seller_news_no_company_message'), 'errors');
            headerRedirect();
        }

        checkGroupExpire();

        $data = array(
            'upload_folder' => encriptedFolderName(),
            'title'         => 'Company news',
            'seller_view'   => true,
        );

        $this->view->assign($data);
        $this->view->display('new/header_view');
        $this->view->display('new/user/seller/news/my/index_view');
        $this->view->display('new/footer_view');
    }

    public function ajax_news_list_dt()
    {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        checkPermisionAjaxDT('have_news');

        /** @var Company_Model $companyModel */
        $companyModel = model(Company_Model::class);

        $request = request()->request;

        $conditions = array_merge(
            dtConditions($request->all(), [
                ['as' => 'added_start',     'key' => 'start_from',  'type' => 'getDateFormat:m/d/Y,Y-m-d'],
                ['as' => 'added_finish',    'key' => 'start_to',    'type' => 'getDateFormat:m/d/Y,Y-m-d'],
                ['as' => 'keywords',        'key' => 'keywords',    'type' => 'cleanInput|cut_str']
            ]),
            [
                'type_company'  => 'all',
                'id_company'    => my_company_id(),
                'limit'         => $request->getInt('iDisplayStart') . ',' . $request->getInt('iDisplayLength')
            ]
        );

        $data['news_list'] = $companyModel->get_companies_news($conditions);
        $totalNews = $companyModel->count_companies_news($conditions);

        $output = [
            'sEcho'                => $request->getInt('sEcho'),
            'iTotalRecords'        => $totalNews,
            'iTotalDisplayRecords' => $totalNews,
            'aaData'               => [],
        ];

        if (empty($data['news_list'])) {
            jsonResponse('', 'success', $output);
        }

        $data['company_link'] = getCompanyURL($data['news_list'][0]);
        $output['aaData'] = $this->_dt_news($data);

        jsonResponse('', 'success', $output);
    }

    public function popup_forms()
    {
        checkIsAjax();
        checkIsLoggedAjaxModal();

        $id = (int) $this->uri->segment(4);
        $op = $this->uri->segment(3);
        switch ($op) {
            case 'add_comment_form':
                $this->load->model('Seller_News_Model', 'seller_news');
                $data['news'] = $this->seller_news->getNews($id);
                if (empty($data['news'])) {
                    messageInModal(translate('seller_news_not_exist_message'));
                }

                $this->view->assign($data);
                $this->view->display('new/user/seller/news/add_comment_form_view', $data);
            break;
            case 'edit_comment_form':
                $this->load->model('Seller_News_Model', 'seller_news');
                $data['item'] = $this->seller_news->getComment($id);
                if (empty($data['item'])) {
                    messageInModal(translate('seller_news_comment_not_exist_warning'));
                }

                if (!is_privileged('user', $data['item']['id_user'], true)) {
                    messageInModal(translate('seller_news_comment_not_exist_warning'));
                }

                $this->view->assign($data);
                $this->view->display('new/user/seller/news/edit_news_comment_form_view', $data);
            break;
            case 'add_news_form':
                checkPermisionAjaxModal('have_news');

                $filesize = config('fileupload_max_file_size', 10 * 1024 * 1024);
                $mime_properties = getMimePropertiesFromFormats(config('fileupload_image_formats', 'jpg,jpeg,png,gif,bmp'));
                $upload_directory = encriptedFolderName();

                views()->assign(array(
                    'action'                   => __SITE_URL . 'seller_news/ajax_news_operations/add_news',
                    'upload_folder'            => $upload_directory,
                    'fileupload_max_file_size' => $filesize,
                    'fileupload'               => array(
                        'directory' => $upload_directory,
                        'limits'    => array(
                            'width'             => config('news_picture_min_width', 150),
                            'height'            => config('news_picture_min_height', 150),
                            'amount'            => 1,
                            'accept'            => arrayGet($mime_properties, 'accept'),
                            'formats'           => arrayGet($mime_properties, 'formats'),
                            'mimetypes'         => arrayGet($mime_properties, 'mimetypes'),
                            'filesize'          => $filesize,
                            'filesize_readable' => config('fileupload_max_file_size_placeholder', '10MB'),
                        ),
                        'url'       => array(
                            'upload' => __SITE_URL . "seller_news/ajax_news_upload_photo/{$upload_directory}",
                            'delete' => __SITE_URL . "seller_news/ajax_news_delete_files/{$upload_directory}",
                        ),
                    ),
                ));

                $this->view->display('new/user/seller/news/news_form_view');
            break;
            case 'edit_news_form':
                checkPermisionAjaxModal('have_news');

                $news_id = (int) $this->uri->segment(4);
                if (
                    empty($news_id) ||
                    empty($news = model('seller_news')->getNews($news_id))
                ) {
                    messageInModal(translate('seller_news_not_exist_message'));
                }

                if (!is_privileged('user', $news['id_seller'], true)) {
                    messageInModal(translate('seller_news_not_yours_message'));
                }

                $filesize = config('fileupload_max_file_size', 10 * 1024 * 1024);
                $mime_properties = getMimePropertiesFromFormats(config('fileupload_image_formats', 'jpg,jpeg,png,gif,bmp'));
                $upload_directory = encriptedFolderName();
                /** @var FilesystemProviderInterface $storageProvider */
                $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                $storage = $storageProvider->storage('public.storage');
                $imageLink = $storage->url(CompanyNewsFilePathGenerator::newsPath($news['id_company'], $news['image_news']));

                views()->assign(array(
                    'news' 					   => $news,
                    'imageLink' 			   => $imageLink,
                    'action'                   => __SITE_URL . 'seller_news/ajax_news_operations/edit_news',
                    'upload_folder'            => $upload_directory,
                    'fileupload_max_file_size' => $filesize,
                    'fileupload'               => array(
                        'directory' => $upload_directory,
                        'limits'    => array(
                            'width'             => config('news_picture_min_width', 150),
                            'height'            => config('news_picture_min_height', 150),
                            'amount'            => 1,
                            'accept'            => arrayGet($mime_properties, 'accept'),
                            'formats'           => arrayGet($mime_properties, 'formats'),
                            'mimetypes'         => arrayGet($mime_properties, 'mimetypes'),
                            'filesize'          => $filesize,
                            'filesize_readable' => config('fileupload_max_file_size_placeholder', '10MB'),
                        ),
                        'url'       => array(
                            'upload' => __SITE_URL . "seller_news/ajax_news_upload_photo/{$upload_directory}",
                            'delete' => __SITE_URL . "seller_news/ajax_news_delete_files/{$upload_directory}",
                        ),
                    ),
                ));

                $this->view->display('new/user/seller/news/news_form_view');
            break;
            case 'admin_edit_news_form':
                checkPermisionAjaxModal('moderate_content');

                $this->load->model('Seller_News_Model', 'seller_news');

                $data['item'] = $this->seller_news->getNews($id);
                if (empty($data['item'])) {
                    messageInModal(translate('seller_news_not_exist_message'));
                }
                $provider = $this->getContainer()->get(FilesystemProviderInterface::class);
                $storage = $provider->storage('public.storage');
                $data['item']['imageLink'] = $storage->url(CompanyNewsFilePathGenerator::newsPath($data['item']['id_company'], $data['item']['image_news']));
                $this->view->assign($data);
                $this->view->display('admin/directory/news/edit_news_form_view');

            break;
            case 'email':
                checkPermisionAjaxModal('email_this');

                $this->load->model('Seller_News_Model', 'seller_news');
                if (!$this->seller_news->exist_news($id)) {
                    messageInModal(translate('seller_updates_not_correct_message'));
                }

                $data['id_news'] = $id;
                $this->view->assign($data);

                $this->view->display('new/user/seller/news/popup_email_view', $data);
            break;
            case 'share':
                checkPermisionAjaxModal('share_this');

                $this->load->model('Seller_News_Model', 'seller_news');
                if (!$this->seller_news->exist_news($id)) {
                    messageInModal(translate('seller_updates_not_correct_message'));
                }

                $data['id_news'] = $id;
                $this->view->assign($data);

                $this->view->display('new/user/seller/news/share_news_form_view');
            break;
        }
    }

    public function ajax_news_operations()
    {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        if (!logged_in()) {
            jsonResponse(translate('seller_news_not_logged_in_message'));
        }

        $this->load->model('Seller_News_Model', 'seller_news');

        $op = $this->uri->segment(3);
        switch ($op) {
			case 'add_news':
				checkHaveCompanyAjax();
                checkPermisionAjax('have_news');
                is_allowed('freq_allowed_companies_posts');

                $validator_rules = array(
                    array(
                        'field' => 'title',
                        'label' => translate('seller_news_title_label_text'),
                        'rules' => array('required' => '', 'max_len[200]' => ''),
                    ),
                    array(
                        'field' => 'text',
                        'label' => translate('seller_news_content_label_text'),
                        'rules' => array('required' => '', 'html_max_len[20000]' => ''),
                    ),
                );
                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $request = request()->request;
                $companyId = my_company_id();
                $sellerId = privileged_user_id();

                /** @var TinyMVC_Library_Cleanhtml $cleanHtmlLibrary */
                $cleanHtmlLibrary = library(TinyMVC_Library_Cleanhtml::class);

                $insert = [
                    'title_news' => cleanInput($request->get('title')),
                    'id_seller'  => $sellerId,
                    'id_company' => $companyId,
                    'text_news'  => $cleanHtmlLibrary->sanitizeUserInput($request->get('text')),
                ];

                if (!empty($tempImage = $request->get('image'))) {
                    /** @var TinyMVC_Library_Image_intervention $interventionImageLibrary */
                    $interventionImageLibrary = library(TinyMVC_Library_Image_intervention::class);
                    /** @var FilesystemProviderInterface  $storageProvider */
                    $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                    $publicDisk = $storageProvider->storage('public.storage');
                    $publicPrefixer = $storageProvider->prefixer('public.storage');
                    $prefixerTemp = $storageProvider->prefixer('temp.storage');
                    $path = CompanyNewsFilePathGenerator::newsFolder($companyId);
                    $publicDisk->createDirectory($path);

                    $imageConfigModule = config("img.seller_news.main");
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
                    $insert['image_news'] = $images[0]['new_name'];
                }

                $id_news = $this->seller_news->setSellerNews($insert);
                if ($id_news) {
                    $this->load->model('Company_Model', 'company');
                    $this->load->model('User_Statistic_Model', 'statistic');
                    $this->statistic->set_users_statistic(array(
                        $sellerId => array('company_posts_news' => 1),
					));

					if (!empty($_POST['post_wall'])) {
						library('wall')->add(array(
							'operation'  => 'add',
							'type'       => 'news',
							'id_item'    => $id_news,
							'id_company' => $companyId,
							'id_seller'  => $sellerId,
						));
					}

                    //region block user content
                    $seller_info = model('user')->getSimpleUser(privileged_user_id());
                    if(in_array($seller_info['status'], array('blocked', 'restricted'))){
                        model('blocking')->change_blocked_users_news(array(
                            'blocked' => 0,
                            'users_list' => array(privileged_user_id())
                        ), array('blocked' => 2));
                    }
                    //endregion block user content

                    jsonResponse(translate('seller_news_saved_message'), 'success');
                }
                jsonResponse(translate('seller_news_cannot_add_now_message'));

            break;
			case 'edit_news':
				checkPermisionAjax('have_news,moderate_content');
				if(!have_right('moderate_content')) {
					checkHaveCompanyAjax();
				}
                is_allowed('freq_allowed_companies_posts');

                $validator_rules = array(
                    array(
                        'field' => 'title',
                        'label' => translate('seller_news_title_label_text'),
                        'rules' => array('required' => '', 'max_len[200]' => ''),
                    ),
                    array(
                        'field' => 'text',
                        'label' => translate('seller_news_content_label_text'),
                        'rules' => array('required' => '', 'html_max_len[20000]' => ''),
                    ),
                );
                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $request = request()->request;

                /** @var Seller_News_Model $sellerNewsModel */
                $sellerNewsModel = model(Seller_News_Model::class);

                if (
					empty($newsId = $request->getInt('id_news')) ||
					empty($news = $sellerNewsModel->getNews($newsId))
				) {
                    jsonResponse(translate('seller_news_not_exist_message'));
                }

                if (have_right('have_news')) {
                    $companyId = my_company_id();
                    if (!is_privileged('user', $news['id_seller'], true)) {
                        jsonResponse(translate('seller_news_not_exist_message'));
                    }
                } else {
                    $companyId = $news['id_company'];
				}

                /** @var TinyMVC_Library_Cleanhtml $cleanHtmlLibrary */
                $cleanHtmlLibrary = library(TinyMVC_Library_Cleanhtml::class);

                $title = cleanInput($request->get('title'));
                $text = $cleanHtmlLibrary->sanitizeUserInput($request->get('text'));

                $update = [
                    'title_news' => $title,
                    'text_news'  => $text,
                    'moderated'  => ($news['title_news'] != $title || $news['text_news'] != $text) ? 0 : $news['moderated'],
                ];

				$oldImage = $news['image_news'];
				$is_old_image_removed = ! $request->getBoolean('old_image');

                if (!empty($newImage = $request->get('image'))) {
                    /** @var TinyMVC_Library_Image_intervention $interventionImageLibrary */
                    $interventionImageLibrary = library(TinyMVC_Library_Image_intervention::class);
                    /** @var FilesystemProviderInterface  $storageProvider */
                    $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                    $publicDisk = $storageProvider->storage('public.storage');
                    $publicPrefixer = $storageProvider->prefixer('public.storage');
                    $prefixerTemp = $storageProvider->prefixer('temp.storage');
                    $path = CompanyNewsFilePathGenerator::newsFolder($companyId);
                    $publicDisk->createDirectory($path);

                    $imageConfigModule =  config("img.seller_news.main");
                    $images = $interventionImageLibrary->image_processing(
                        [
                            'tmp_name' => $prefixerTemp->prefixPath($newImage),
                            'name'     => pathinfo($newImage, PATHINFO_FILENAME)
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
                    if (!empty($images['errors'])) {
                        jsonResponse($images['errors']);
                    }

                    $update['image_news'] = $images[0]['new_name'];
				} else if ($is_old_image_removed) {
					$update['image_news'] = '';
				}

				if (!$sellerNewsModel->updateSellerNews($newsId, $update)) {
					jsonResponse(translate('seller_news_cannot_update_news_now_message'));
				}

				if (!empty($newImage) || $is_old_image_removed) {
                    $this->deleteImageFromStorage($companyId, $oldImage);
				}

				if (!empty($request->get('post_wall'))) {
                    /** @var TinyMVC_Library_Wall $wallLibrary */
                    $wallLibrary = library(TinyMVC_Library_Wall::class);
                    $wallLibrary->add(
                        [
                            'id_company' => $companyId,
                            'id_seller'  => $news['id_seller'],
                            'operation'  => 'edit',
                            'id_item'    => $newsId,
                            'type'       => 'news',
                        ]
                    );
                }

				jsonResponse(translate('seller_news_saved_message'), 'success', array(
					'id_news' => $newsId,
					'news'    => null,
				));

            break;
            case 'add_comment':
                is_allowed('freq_allowed_add_comment');

                $validator_rules = array(
                    array(
                        'field' => 'news',
                        'label' => translate('seller_news_information_label'),
                        'rules' => array('required' => ''),
                    ),
                    array(
                        'field' => 'mess',
                        'label' => translate('seller_news_comment_word'),
                        'rules' => array('required' => '', 'max_len[500]' => ''),
                    ),
                );
                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $insert = array(
                    'id_news'      => (int) $_POST['news'],
                    'id_user'      => privileged_user_id(),
                    'text_comment' => cleanInput($_POST['mess']),
                );

                if ($id_comment = $this->seller_news->setComment($insert)) {
                    $counter = $this->seller_news->updateNewsCommentCounter($insert['id_news'], 1);
                    jsonResponse(translate('seller_news_comment_added'), 'success');
                }

                jsonResponse(translate('seller_news_cannot_add_comments_message'));

            break;
            case 'edit_comment':
                $validator_rules = array(
                    array(
                        'field' => 'id',
                        'label' => translate('seller_news_comment_info_label'),
                        'rules' => array('required' => ''),
                    ),
                    array(
                        'field' => 'mess',
                        'label' => translate('seller_news_comment_word'),
                        'rules' => array('required' => '', 'max_len[500]' => ''),
                    ),
                );
                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $id_comment = (int) $_POST['id'];
                $comment = $this->seller_news->getComment($id_comment);
                if (empty($comment)) {
                    jsonResponse(translate('seller_news_comment_not_exist_message'));
                }

                if (!is_privileged('user', $comment['id_user'], true)) {
                    jsonResponse(translate('seller_news_comment_not_exist_message'));
                }

                $_new_message = cleanInput($_POST['mess']);
                $update = array(
                    'text_comment' => $_new_message,
                    'moderated'    => ($comment['text_comment'] != $_new_message) ? 0 : $comment['moderated'],
                );

                if ($this->seller_news->update_comment($id_comment, $update)) {
                    jsonResponse(translate('seller_news_comment_modified_message'), 'success');
                }
                jsonResponse(translate('seller_news_cannot_edit_comment_message'));

            break;
            case 'moderate_comment':
                checkPermisionAjax('moderate_content');

                $id_comment = (int) $_POST['comment'];
                if ($this->seller_news->moderateComment($id_comment)) {
                    jsonResponse(translate('seller_news_comment_moderated_message'), 'success');
                }

                jsonResponse(translate('seller_news_comment_not_moderated_message'));

            break;
            case 'delete_comment':
                $id_comment = intval($_POST['comment']);
                $id_user = id_session();

                $comment = $this->seller_news->getComment($id_comment);
                if (empty($comment)) {
                    jsonResponse(translate('seller_news_comment_not_exist_message'));
                }

                if (!is_privileged('user', $comment['id_user'], true) && !have_right('moderate_content')) {
                    jsonResponse(translate('general_no_permission_message'));
                }

                if ($this->seller_news->deleteComment($id_comment)) {
                    $this->seller_news->updateNewsCommentCounter($comment['id_news'], -1);
                    jsonResponse(translate('seller_news_comment_successfully_deleted_news'), 'success');
                }
                jsonResponse(translate('seller_news_cannot_delete_comment_message'));

            break;
            case 'moderate_news':
                checkPermisionAjax('moderate_content');

                $id_news = intval($_POST['news']);
                if ($this->seller_news->moderateNews($id_news)) {
                    jsonResponse(translate('seller_news_moderated_message'), 'success');
                }

                jsonResponse(translate('seller_news_cannot_moderate_now_message'));

            break;
            case 'delete_news':
                checkPermisionAjax('have_news');

                $id_news = intval($_POST['news']);
                $news = $this->seller_news->getNews($id_news);
                if (!in_session('companies', $news['id_company']) && !have_right('moderate_content')) {
                    jsonResponse(translate('general_no_permission_message'));
                }

                if ($this->seller_news->deleteNews($news['id_news'])) {
                    $this->load->model('User_Statistic_Model', 'statistic');
                    $this->statistic->set_users_statistic(array(
                        privileged_user_id() => array('company_posts_news' => -1),
                    ));

                    if (!empty($news['image_news'])) {

                        /** @var FilesystemProviderInterface $storageProvider */
                        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                        $publicDisk = $storageProvider->storage('public.storage');
                        $path = CompanyNewsFilePathGenerator::newsPath($news['id_company'], $news['image_news']);
                        try {
                            $publicDisk->delete($path);
                            foreach(SellerNewsPhotoThumb::cases() as $thumb){
                                $publicDisk->delete(CompanyNewsFilePathGenerator::thumbImage($news['id_company'], $news['image_news'], $thumb));
                            }
                        } catch (UnableToDeleteFile $e) {
                            jsonResponse(translate('validation_images_delete_fail'));
                        }
                    }
                    $this->seller_news->deleteNewsComments($id_news);

                    library('wall')->remove(array(
                        'type'       => 'news',
                        'id_item'    => $id_news
                    ));

                    jsonResponse(translate('seller_news_deleted_news_message'), 'success');
                }

                jsonResponse(translate('seller_news_cannot_delete_news_now_message'));

            break;
            case 'delete':
                if (!i_have_company()) {
                    jsonResponse(translate('seller_news_no_company_message'));
                }

                if (!have_right('have_news')) {
                    jsonResponse(translate('general_no_permission_message'));
                }

                $ids = array();
                foreach ($_POST['news'] as $id) {
                    $ids[] = intval($id);
                }

                $ids = implode(',', $ids);
                $id_company = my_company_id();
                $news = $this->seller_news->getSimpleSellerNews(array('news_list' => $ids, 'id_company' => $id_company));
                /** @var FilesystemProviderInterface $storageProvider */
                $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                $publicDisk = $storageProvider->storage('public.storage');
                foreach ($news as $news_item) {
                    $path = CompanyNewsFilePathGenerator::newsPath($news_item['id_company'], $news_item['image_news']);
                    try {
                        $publicDisk->delete($path);
                        foreach(SellerNewsPhotoThumb::cases() as $thumb){
                            $publicDisk->delete(CompanyNewsFilePathGenerator::thumbImage($news_item['id_company'], $news_item['image_news'], $thumb));
                        }
                    } catch (UnableToDeleteFile $e) {
                        jsonResponse(translate('validation_images_delete_fail'));
                    }

                    $news_list[] = $news_item['id_news'];
                }

                if (empty($news_list)) {
                    jsonResponse(translate('seller_news_select_news_text'));
                }

                if ($this->seller_news->deleteNews(implode(',', $news_list))) {
                    $this->load->model('User_Statistic_Model', 'statistic');
                    $this->statistic->set_users_statistic(array(
                        privileged_user_id() => array('company_posts_news' => -count($news_list)),
                    ));
                    $this->seller_news->deleteNewsComments(implode(',', $news_list));

                    library('wall')->remove(array(
                        'type'       => 'news',
                        'id_item'    => $news_list
                    ));

                    jsonResponse(translate('seller_news_deleted_news_message'), 'success');
                }

                jsonResponse(translate('seller_news_cannot_delete_news_now_message'));

            break;
            case 'email':
                checkPermisionAjax('email_this');
                is_allowed('freq_allowed_send_email_to_user');

                $max_emails = config('email_this_max_email_count', 10);
                $validator_rules = array(
                    array(
                        'field' => 'emails',
                        'label' => translate('seller_news_email_address_word'),
                        'rules' => array('required' => '', 'no_whitespaces' => '', 'valid_emails' => '', 'max_emails_count[' . $max_emails . ']' => ''),
                    ),
                    array(
                        'field' => 'message',
                        'label' => translate('seller_news_message_word'),
                        'rules' => array('required' => '', 'max_len[500]' => ''),
                    ),
                    array(
                        'field' => 'id',
                        'label' => translate('seller_news_information_label'),
                        'rules' => array('required' => ''),
                    ),
                );

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $filteredEmails = filter_email($_POST['emails']);

                if (empty($filteredEmails)) {
                    jsonResponse(translate('seller_news_valid_email_message'));
                }

                $idNews = (int) $_POST['id'];
                $newsInfo = model(Seller_News_Model::class)->getNews($idNews);

                if (empty($newsInfo)) {
                    jsonResponse(translate('seller_news_not_exist_warning'));
                }

                $company = model(Company_Model::class)->get_company(['id_company' => $newsInfo['id_company']]);

                if (empty($company)) {
                    jsonResponse(translate('seller_updates_not_correct_message'));
                }

                $userName = user_name_session();

                try {
                    /** @var MailerInterface $mailer */
                    $mailer = $this->getContainer()->get(MailerInterface::class);
                    //get image link for the news
                    /** @var FilesystemProviderInterface  $storageProvider */
                    $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                    $publicDisk = $storageProvider->storage('public.storage');
                    $pathToFile = __IMG_URL . 'public/img/no_image/no-image-80x80.png';
                    if($publicDisk->fileExists(CompanyNewsFilePathGenerator::thumbImage($newsInfo['id_company'], $newsInfo['image_news'], SellerNewsPhotoThumb::MEDIUM()))){
                        $pathToFile = $publicDisk->url(CompanyNewsFilePathGenerator::thumbImage($newsInfo['id_company'], $newsInfo['image_news'], SellerNewsPhotoThumb::MEDIUM()));
                    }
                    //send email
                    $mailer->send(
                        (new EmailFriendAboutNews($userName, cleanInput(request()->request->get('message')), $company, $newsInfo, null, null, $pathToFile))
                            ->to(...array_map(fn (string $to) => new Address($to), $filteredEmails))
                            ->subjectContext(['[userName]' => $userName])
                    );
                } catch (\Throwable $th) {
                    jsonResponse(translate('email_has_not_been_sent'));
                }

                jsonResponse(translate('seller_news_email_sent_message'), 'success');
            break;
            case 'share':
                checkPermisionAjax('share_this');
                is_allowed('freq_allowed_save_search');

                $validator_rules = array(
                    array(
                        'field' => 'message',
                        'label' => translate('seller_news_message_word'),
                        'rules' => array('required' => '', 'max_len[500]' => ''),
                    ),
                    array(
                        'field' => 'id',
                        'label' => translate('seller_news_information_label'),
                        'rules' => array('required' => ''),
                    ),
                );
                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $idNews = (int) $_POST['id'];
                $newsInfo = model(Seller_News_Model::class)->getNews($idNews);

                if (empty($newsInfo)) {
                    jsonResponse(translate('seller_news_not_exist_warning'));
                }

                $filteredEmails = model(Followers_Model::class)->getFollowersEmails(id_session());

                if (empty($filteredEmails)) {
                    jsonResponse(translate('seller_news_no_followers_message'));
                }

                $company = model(Company_Model::class)->get_company(['id_company' => $newsInfo['id_company']]);

                if (empty($company)) {
                    jsonResponse(translate('seller_updates_not_correct_message'));
                }

                $userName = user_name_session();

                try {
                    /** @var MailerInterface $mailer */
                    $mailer = $this->getContainer()->get(MailerInterface::class);
                    //get image link for the news
                    /** @var FilesystemProviderInterface  $storageProvider */
                    $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                    $publicDisk = $storageProvider->storage('public.storage');
                    $pathToFile = CompanyNewsFilePathGenerator::thumbImage($newsInfo['id_company'], $newsInfo['image_news'], SellerNewsPhotoThumb::MEDIUM());
                    //send email
                    $mailer->send(
                        (new EmailFriendAboutNews($userName, cleanInput(request()->request->get('message')), $company, $newsInfo, null, null, $publicDisk->url($pathToFile)))
                            ->to(...array_map(fn (int $id, string $to) => new RefAddress((string) $id, new Address($to)), array_column($filteredEmails, 'idu', 'idu'), array_column($filteredEmails, 'email', 'email')))
                            ->subjectContext(['[userName]' => $userName])
                    );
                } catch (\Throwable $th) {
                    jsonResponse(translate('email_has_not_been_sent'));
                }

                jsonResponse(translate('seller_news_email_sent_message'), 'success');
            break;
        }
    }

    private function deleteImageFromStorage(int $companyId, string $imageName)
    {
        /** @var FilesystemProviderInterface $storageProvider */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $publicDisk = $storageProvider->storage('public.storage');
        $path = CompanyNewsFilePathGenerator::newsPath($companyId, $imageName);
        try {
            $publicDisk->delete($path);
            foreach(SellerNewsPhotoThumb::cases() as $thumb){
                $publicDisk->delete(CompanyNewsFilePathGenerator::thumbImage($companyId, $imageName, $thumb));
            }
        } catch (UnableToDeleteFile $e) {
            jsonResponse(translate('validation_images_delete_fail'));
        }
    }

    public function ajax_news_upload_photo()
    {
        checkIsAjax();
        checkIsLoggedAjax();
        checkHaveCompanyAjax();
        checkPermisionAjax('have_news');
        checkGroupExpire('ajax');

        $request = request();
        /** @var FileUploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('files');
        if (null === $uploadedFile) {
			jsonResponse(translate('seller_pictures_select_file_message'));
		}
        if (is_array($uploadedFile) || !$uploadedFile->isValid()) {
			jsonResponse(translate('seller_pictures_invalid_file_message'));
		}
        $config = 'img.seller_news.main.rules';
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
        // And write file
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


    public function ajax_news_delete_db_photo()
    {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        checkPermisionAjax('have_news');

        if (!i_have_company()) {
            jsonResponse(translate('seller_news_no_company_yet_message'));
        }

        $id_news = intval($_POST['file']);
        $this->load->model('Seller_News_Model', 'seller_news');
        $news = $this->seller_news->getNews($id_news);

        if (!in_session('companies', $news['id_company'])) {
            jsonResponse(translate('general_no_permission_message'));
        }

        $this->deleteImageFromStorage($news['id_company'], $news['image_news']);

        $this->seller_news->updateSellerNews($id_news, array('image_news' => ''));
        jsonResponse(translate('seller_news_image_deleted_message'), 'success');
    }

    public function ajax_news_delete_files()
    {
        checkIsAjax();
        checkIsLoggedAjax();
        checkHaveCompanyAjax();
        checkPermisionAjax('have_news');
        checkGroupExpire('ajax');

        if (empty($file = request()->request->get('file'))) {
            jsonResponse(translate('seller_updates_filename_not_correct_message'));
        }
        /** @var FilesystemProviderInterface  $storageProvider */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $tempDisk = $storageProvider->storage('temp.storage');
        $pathToFile = FilePathGenerator::uploadedFile($file);

        if (!$tempDisk->fileExists($pathToFile)) {
            jsonResponse(translate('validation_images_delete_fail'));
        }
        try{
            $tempDisk->delete($pathToFile);
        } catch (UnableToDeleteFile $e) {
            jsonResponse(translate('validation_images_delete_fail'));
        }

        jsonResponse(null, 'success');
    }

    private function _dt_news($data)
    {
        extract($data);
        $provider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $storage = $provider->storage('public.storage');
        foreach ($news_list as $record) {
            $imageLink = $storage->url(CompanyNewsFilePathGenerator::newsPath($record['id_company'], $record['image_news']));
            $_image = '';
            if ('' != $record['image_news']) {
                $_image = '<div class="flex-card__fixed main-data-table__item-img image-card">
								<a
                                    class="link fancyboxGallery"
                                    data-title="' . $record['title_news'] . '"
                                    href="' . $imageLink . '"'.
                                    addQaUniqueIdentifier("seller-news-my__table_novelty-image-link").'
                                >
									<img
                                        class="image"
                                        src="' . $imageLink . '"'.
                                        addQaUniqueIdentifier("seller-news-my__table_novelty-image").'
                                    />
								</a>
							</div>';
            }

            $_link = $company_link . '/view_news/' . strForUrl($record['title_news'] . ' ' . $record['id_news']);
            $output[] = array(
                'dt_news' 		=> '<div class="flex-card">
										' . $_image . '
										<div class="flex-card__float">
											<div class="grid-text">
												<div class="grid-text__item">
													<a
                                                        href="' . $_link . '"
                                                        class="txt-black"
                                                        title="Details"
                                                        target="_blank"'.
                                                        addQaUniqueIdentifier("seller-news-my__table_novelty-title") . '
                                                    >'.
                                                        $record['title_news']
                                                    . '</a>
												</div>
											</div>
											<div class="txt-gray">Comments: ' . $record['comments_count'] . '</div>
										</div>
									</div>',
                'dt_date' 		  => getDateFormat($record['date_news'], 'Y-m-d H:i:s', 'j M, Y H:i'),
                'dt_actions' 	=> '<div class="dropdown">
										<a
                                            class="dropdown-toggle"
                                            data-toggle="dropdown"
                                            aria-haspopup="true"
                                            aria-expanded="false"' .
                                            addQaUniqueIdentifier("seller-news-my__table_dropdown-btn") .'
                                        >
											<i class="ep-icon ep-icon_menu-circles"></i>
										</a>

										<div class="dropdown-menu dropdown-menu-right">
											<a
                                                class="dropdown-item fancybox.ajax fancyboxValidateModal"
                                                data-title="' . translate('general_button_edit_text') . '"
                                                title="' . translate('general_button_edit_text') . '"
                                                href="' . __SITE_URL . 'seller_news/popup_forms/edit_news_form/' . $record['id_news'] . '"'.
                                                addQaUniqueIdentifier("seller-news-my__table_dropdown-menu_edit-btn") .'
                                            >
												<i class="ep-icon ep-icon_pencil"></i>
												<span>' . translate('general_button_edit_text') . '</span>
											</a>
											<a
                                                class="dropdown-item confirm-dialog"
                                                data-callback="delete_news"
                                                title="' . translate('general_button_delete_text') . '"
                                                data-message="Are you sure you want to delete this news?"
                                                data-news="' . $record['id_news'] . '"
                                                href="#"'.
                                                addQaUniqueIdentifier("seller-news-my__table_dropdown-menu_delete-btn") .'
                                            >
												<i class="ep-icon ep-icon_trash-stroke"></i>
												<span>' . translate('general_button_delete_text') . '</span>
											</a>
											<a
                                                class="dropdown-item"
                                                href="' . $_link . '"
                                                title="' . translate('general_button_details_text') . '"
                                                data-title="' . translate('general_button_details_text') . '"
                                                target="_blank"'.
                                                addQaUniqueIdentifier("seller-news-my__table_dropdown-menu_details-btn") .'
                                            >
												<i class="ep-icon ep-icon_info-stroke"></i>
												<span>' . translate('general_button_details_text') . '</span>
											</a>
										</div>
									</div>',
            );
        }

        return $output;
    }
}
