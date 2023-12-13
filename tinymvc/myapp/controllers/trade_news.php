<?php

use App\Common\Contracts\CommentType;
use App\Filesystem\FilePathGenerator;
use App\Filesystem\TradeNewsPathGenerator;
use ExportPortal\Contracts\Filesystem\FilesystemOperator;
use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Trade_News_Controller extends TinyMVC_Controller {
    const IMAGES_AMOUNT_EXCEEDED = 15001;
    const IMAGES_INVALID_DOMAIN = 15002;

    private $breadcrumbs = array();
    private FilesystemOperator $storage;
    private FilesystemOperator $tempStorage;

     /**
     * Controller constructor.
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        /** @var FilesystemProviderInterface */
        $storageProvider = $container->get(FilesystemProviderInterface::class);

        $this->storage = $storageProvider->storage('public.storage');
        $this->tempStorage = $storageProvider->storage('temp.storage');
    }

    public function index(){
        $uri = uri()->uri_to_assoc(4);

        checkURI($uri, array('page'));
        checkIsValidPage($uri['page']);

        $this->breadcrumbs[] = array(
			'link' 	=> __SITE_URL . 'trade_news',
			'title'	=> 'Trade news'
        );

        $results_limit = (int) config('env.ENTITIES_RESULT_LIMIT');

        $per_page = (int) config('trade_news_all_per_page', 10);
        $page = (int) ($uri['page'] ?? 1);

        if ($page * $per_page > $results_limit) {
            views()->assign(array(
                'header_out_content'    => 'new/trade_news/header_view',
                'footer_out_content'    => 'new/about/bottom_who_we_are_view',
                'trade_news_list'       => array(),
                'configs_library'       => model(Config_Lib_Model::class)->get_lib_configs(),
                'main_content'          => 'new/trade_news/all_view',
                'library_page'          => 'trade_news',
                'breadcrumbs'           => $this->breadcrumbs,
            ));

            views()->display('new/index_template_view');
            return;
        }

        $count_news = model(Trade_news_Model::class)->count_trade_news(array('visible' => 1));
        $trade_news_list = model(Trade_news_Model::class)->get_trade_news(array('start' => $per_page * ($page - 1), 'limit' => $per_page, 'visible' => 1));

        $paginator_config = array(
            'full_tag_open'      => "<ul class=\"pagination pagination--right\">",
            'base_url'      => __SITE_URL . 'trade_news',
            'first_url'     => __SITE_URL . 'trade_news',
			'total_rows'    => $count_news,
			'per_page'      => $per_page,
			'cur_page'		=> $page,
        );

        library(TinyMVC_Library_Pagination::class)->initialize($paginator_config);

        $data = array(
            'header_out_content'    => 'new/trade_news/header_view',
            'footer_out_content'    => 'new/about/bottom_who_we_are_view',
            'trade_news_list'       => model(Trade_news_Model::class)->get_trade_news(array('start' => $per_page * ($page - 1), 'limit' => $per_page, 'visible' => 1)),
            'configs_library'       => model(Config_Lib_Model::class)->get_lib_configs(),
            'main_content'          => 'new/trade_news/all_view',
            'library_page'          => 'trade_news',
            'breadcrumbs'           => $this->breadcrumbs,
            'pagination'            => empty($trade_news_list) ? null : library(TinyMVC_Library_Pagination::class)->create_links(),
            'limit'                 => $per_page,
            'count'                 => $count_news,
        );

        if ($page > 1) {
            $data['meta_params']['[PAGE]'] = $page;
        }

		views()->assign($data);
		views()->display('new/index_template_view');
    }

    public function detail(){

        $id_news = id_from_link($this->uri->segment(3));
        $limit = config('trade_news_recommend_per_page');

        $data['trade_news_detail'] = model('trade_news')->get_one_trade_news($id_news);

        if (
            empty($data['trade_news_detail'])
            || (!$data['trade_news_detail']['is_visible'] && !have_right_or('trade_news_administration'))
        ){
			show_404();
        }

        $data['trade_news_list'] = model('trade_news')->get_trade_news(array('start' => 0, 'limit' => $limit, 'order_by' => 'RAND()', 'not_trade_news' => $id_news, 'visible' => 1));
        $data['configs_library'] = model('Config_Lib')->get_lib_configs();
        $data['library_page'] = 'trade_news';

		$this->breadcrumbs[] = array(
			'link' 	=> __SITE_URL.'trade_news/all',
			'title'	=> 'Trade news'
        );
        $this->breadcrumbs[] = array(
			'link' 	=> '',
			'title'	=> $data['trade_news_detail']['title']
        );
        $data['meta_params']['[TITLE]'] = $data['trade_news_detail']['title'];

        $data['breadcrumbs'] = $this->breadcrumbs;

        $data['main_content'] = 'new/trade_news/detail_view';
        $data['sidebar_right_content'] = 'new/trade_news/sidebar_view';

        if(!empty($data['trade_news_list'])){
            $data['footer_content'] = ['new/trade_news/mobile_wrapper','new/trade_news/we_recommend_view'];
        }

        $data['mobile_comments'] = 'new/trade_news/mobile_wrapper';

        $data['comments'] = array(
            'hash_components'   => tradeNewsCommentsResourceHashComponents($id_news),
            'type_id'           => CommentType::TRADE_NEWS()->value,
        );

		$this->view->assign($data);
		$this->view->display('new/index_template_view');
    }

    public function administration() {
        checkAdmin('trade_news_administration');

		$data = array(
			'upload_folder'  => encriptedFolderName(),
			'title' => 'Trade News'
		);

        $this->view->assign($data);
        $this->view->display('admin/header_view');
        $this->view->display('admin/trade_news/index_view');
        $this->view->display('admin/footer_view');
    }

    function ajax_trade_news_administration() {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        checkAdminAjaxDT('trade_news_administration');

        $params = array('limit' => intVal($_POST['iDisplayLength']), 'start' => intVal($_POST['iDisplayStart']));

        if ($_POST['iSortingCols'] > 0) {
            for ($i = 0; $i < $_POST['iSortingCols']; $i++) {
                switch ($_POST["mDataProp_" . intVal($_POST['iSortCol_' . $i])]) {
                    case 'dt_id': $params['sort_by'][] = 'tn.id_trade_news-' . $_POST['sSortDir_' . $i];break;
                    case 'dt_title': $params['sort_by'][] = 'tn.title-' . $_POST['sSortDir_' . $i];break;
                    case 'dt_date': $params['sort_by'][] = 'tn.date-' . $_POST['sSortDir_' . $i];break;
                    case 'dt_date_update': $params['sort_by'][] = 'tn.date_update-' . $_POST['sSortDir_' . $i];break;
                }
            }
        }

        if (isset($_POST['date_to'])) {
            $params['date_to'] = cleanInput($_POST['date_to']);
        }

        if (isset($_POST['date_from'])) {
            $params['date_from'] = cleanInput($_POST['date_from']);
        }

        if (isset($_POST['date_update_to'])) {
            $params['date_update_to'] = cleanInput($_POST['date_update_to']);
        }

        if (isset($_POST['date_update_from'])) {
            $params['date_update_from'] = cleanInput($_POST['date_update_from']);
        }

        if (isset($_POST['visible'])) {
            $params['visible'] = intVal($_POST['visible']);
        }

        if (isset($_POST['keywords'])) {
            $params['keywords'] = cleanInput(cut_str($_POST['keywords']));
        }

        $trade_news = model('trade_news')->get_trade_news($params);
        $trade_news_count = model('trade_news')->count_trade_news($params);

        $output = array(
            "sEcho" => intval($_POST['sEcho']),
            "iTotalRecords" => $trade_news_count,
            "iTotalDisplayRecords" => $trade_news_count,
			'aaData' => array()
        );

        if(empty($trade_news)) {
			jsonResponse('', 'success', $output);
        }

        $module = 'trade_news.main';

		foreach ($trade_news as $one_news) {
            $visible_btn = 'ep-icon_visible';
            if (!$one_news['is_visible']) {
                $visible_btn = 'ep-icon_invisible ';
            }

			$output["aaData"][] = array(
				'dt_id' => $one_news['id_trade_news'],
                'dt_title' =>
                    '<a href="'.__SITE_URL.'trade_news/detail/'.$one_news['title_slug'].'-'.$one_news['id_trade_news'].'"  target="_blank">'
                        . $one_news['title'] .
                    '</a>',
                'dt_main_image' => '<a
                        class="fancyboxGallery"
                        href="' .
                            getDisplayImageLink(array('{ID}' => $one_news['id_trade_news'], '{FILE_NAME}' => $one_news['photo']), $module)
                        . '"
                        data-title="' . $one_news['title'] . '"
                        title="' . $one_news['title'] . '"
                    >
                        <img
                            class="mw-150 mh-100"
                            src="' .  getDisplayImageLink(array('{ID}' => $one_news['id_trade_news'], '{FILE_NAME}' => $one_news['photo']), $module, array( 'thumb_size' => 5 )) . '"
                            alt="' . $one_news['title'] . '"
                        >
                    </a>',
				'dt_short_description' => $one_news['short_description'],
				'dt_date_update' => formatDate($one_news['date_update']),
				'dt_date' => formatDate($one_news['date']),
                'dt_actions' =>
                    '<a
                        class="ep-icon ep-icon_pencil txt-blue fancyboxValidateModalDT fancybox.ajax"
                        href="'.__SITE_URL.'trade_news/popup_forms/edit_news/' . $one_news['id_trade_news'] . '"
                        data-title="Edit this news"
                        data-table="dtTradeNews"
                        title="Edit this news">
                    </a>'
                    .'<a
                        class="ep-icon txt-blue ' . $visible_btn . ' confirm-dialog"
                        data-callback="change_visible_news"
                        data-news="' . $one_news['id_trade_news'] . '"
                        data-message="Are you sure you want to change the visibility status of this news?"
                        href="#"
                        title="Set news ' . (($one_news['is_visible']) ? 'inactive' : 'active') . '">
                    </a>'
                    .'<a
                        class="ep-icon ep-icon_remove txt-red confirm-dialog"
                        data-callback="remove_news"
                        data-news="' . $one_news['id_trade_news'] . '"
                        title="Remove this news"
                        data-message="Are you sure you want to delete this news?" href="#" >
                    </a>',
			);
		}

        jsonResponse("", "success", $output);
    }

    public function popup_forms(){
        checkIsAjax();
        checkIsLoggedAjaxModal();

        $op = $this->uri->segment(3);

        switch ($op) {
            case 'add_news':
                checkPermisionAjaxModal('trade_news_administration');

                $data['upload_folder'] = encriptedFolderName();
                $module_main ='trade_news.main';
                $mime_main_properties = getMimePropertiesFromFormats(config("img.{$module_main}.rules.format"));

                $data['fileupload_crop'] = array(
                    'link_main_image'        => getDisplayImageLink(array('{ID}' => 'none', '{FILE_NAME}' => 'none'), $module_main),
                    'link_thumb_main_image'  => getDisplayImageLink(array('{ID}' => 'none', '{FILE_NAME}' => 'none'), $module_main, array('thumb_size' => 5)),
                    'title_text_popup'       => 'Main image',
                    'btn_text_save_picture'  => 'Set new main image',
                    'croppper_limit_by_min'  => true,
                    'rules'                  => config("img.{$module_main}.rules"),
                    'url'                    => array(
                                                    'upload' => __SITE_URL . "trade_news/ajax_news_upload_photo/".$data['upload_folder']
                                                ),
                    'accept' => arrayGet($mime_main_properties, 'accept'),
                );

                $this->view->assign($data);
                $this->view->display('admin/trade_news/news_form_view');
            break;
            case 'edit_news':
                checkPermisionAjaxModal('trade_news_administration');

                $id_news = $this->uri->segment(4);
                $news_info = model('trade_news')->get_one_trade_news($id_news);

                $data['upload_folder'] = encriptedFolderName();
                $module_main ='trade_news.main';
                $mime_main_properties = getMimePropertiesFromFormats(config("img.{$module_main}.rules.format"));

                $data['fileupload_crop'] = array(
                    // 'link_main_image'        => getDisplayImageLink(array('{ID}' => $id_news, '{FILE_NAME}' => $news_info['photo']), $module_main),
                    'link_main_image'        => $this->storage->url(TradeNewsPathGenerator::mainUploadPath($id_news) . $news_info['photo']),
                    'link_thumb_main_image'  => getDisplayImageLink(array('{ID}' => $id_news, '{FILE_NAME}' => $news_info['photo']), $module_main, array('thumb_size' => 5)),
                    'title_text_popup'       => 'Main image',
                    'btn_text_save_picture'  => 'Set new main image',
                    'croppper_limit_by_min'  => true,
                    'rules'                  => config("img.{$module_main}.rules"),
                    'url'                    => array(
                                                    'upload' => __SITE_URL . "trade_news/ajax_news_upload_photo/".$data['upload_folder']
                                                ),
                    'accept' => arrayGet($mime_main_properties, 'accept'),
                );

                $data['news_info'] = $news_info;

                $this->view->assign($data);
                $this->view->display('admin/trade_news/news_form_view');
            break;
        }
    }

    public function ajax_news_operation(){
        checkIsAjax();

        $op = $this->uri->segment(3);

        switch ($op) {
            case 'preview_content':
                checkPermisionAjax('trade_news_administration');

                if (!logged_in()) {
                    jsonResponse(translate("systmess_error_should_be_logged"));
                }

                $sanitizer = tap(library('Cleanhtml', 'clean'), function ($sanitizer) {
                    $sanitizer->allowIframes();
                    $sanitizer->defaultTextarea(array(
                        'attribute' => 'data-video'
                    ));
                    $sanitizer->addAdditionalTags('<img><h3><h4><h5><h6><p><span><strong><em><b><i><u><a><ol><ul><li><br>');
                });

                jsonResponse('', 'success', array('content' => $this->clean->sanitize($_POST['content'])));
            break;
            case 'add_news':
                checkPermisionAjax('trade_news_administration');

                //region Validation
                $validator_rules = array(
                    array(
                        'field' => 'title',
                        'label' => 'Title',
                        'rules' => array('required' => '', 'max_len[250]' => ''),
                    ),
                    array(
                        'field' => 'short_description',
                        'label' => 'Short description',
                        'rules' => array('required' => '', 'max_len[500]' => ''),
                    ),
                    array(
                        'field' => 'content',
                        'label' => 'Content',
                        'rules' => array('required' => '', 'html_max_len[20000]' => ''),
                    ),
                    array(
                        'field' => 'upload_folder',
                        'label' => 'Upload folder',
                        'rules' => array('required' => ''),
                    ),
                );

                if (empty($_POST['images_main'])) {
                    jsonResponse('Upload main photo.');
                }

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }
                //endregion Validation

                //region Directory check
                // Check folder with files
                $upload_folder = checkEncriptedFolder(cleanInput($_POST['upload_folder']));
                if (false === $upload_folder) {
                    jsonResponse('File upload path is not correct.');
                }
                //endregion Directory check

                //region Sanitizer
                // Load sanitize library
                $sanitizer = tap(library('Cleanhtml', 'clean'), function ($sanitizer) {
                    $sanitizer->allowIframes();
                    $sanitizer->defaultTextarea();
                    $sanitizer->addAdditionalTags('<img><h3><h4><h5><h6><p><span><strong><em><b><i><u><a><ol><ul><li><br>');
                });
                //endregion Sanitizer

                //region Create post
                // Prepare insert data
                $post_title = cleanInput($_POST['title']);
                $insert = array(
                    'title'             => $post_title,
                    'title_slug'        => strForUrl($post_title),
                    'short_description' => cleanInput($_POST['short_description']),
                    'is_visible'        => (int) filter_var($_POST['visible'], FILTER_VALIDATE_BOOLEAN),
                    'date'              => date('Y-m-d')
                );

                $post_id = model('trade_news')->set_trade_news($insert);
                if (!$post_id) {
                    jsonResponse('Error: You cannot add news now. Please try again later.');
                }
                //endregion Create post

                //region Text & image processing
                $update = array();
                $post_images = array();
                $post_images_raw = array();

                try {
                    $allowed_amount_of_images = (int) config('max_trade_news_photos_in_text');
                    $post_processed_images = $this->process_content_images($_POST['content'], $allowed_amount_of_images, $post_id);
                    if (!empty($post_processed_images['paths'])) {
                        $post_images = array_flip(array_flip($post_processed_images['paths']));
                    }
                    if (!empty($post_processed_images['collected'])) {
                        $post_images_raw = array_flip(array_flip($post_processed_images['collected']));
                    }

                    $update['content'] = $sanitizer->sanitize($this->change_content_paths(
                        $_POST['content'],
                        $post_id,
                        $post_images,
                        $upload_folder,
                        $post_images_replaced
                    ));
                } catch (\Exception $exception) {
                    switch ($exception->getCode()) {
                        case self::IMAGES_AMOUNT_EXCEEDED:
                            $message = "Error: You cannot upload more than {$allowed_amount_of_images} photos.";

                            break;
                        case self::IMAGES_INVALID_DOMAIN:
                            $message = 'Error: Image links cannot have external link.';

                            break;
                        default:
                            $message = 'Error: You cannot change this news now. Please try again later.';

                            break;
                    }

                    $this->delete_news_post($post_id);
                    jsonResponse($message);
                }
                //endregion Text & image processing

                /**
                * @todo Refactoring Library
                */

                //region Headline image
                // Copy article image
                if (!empty($_POST['images_main'])) {
                    $module_main = 'trade_news.main';

                    /** @var FilesystemProviderInterface */
                    $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                    $tempPrefixer = $storageProvider->prefixer('temp.storage');
                    $publicDisk = $storageProvider->storage('public.storage');
                    $tempDisk = $storageProvider->storage('temp.storage');
                    $publicPrefixer = $storageProvider->prefixer('public.storage');

                    $folderMainPath = TradeNewsPathGenerator::mainUploadPath($post_id);
                    $main_path = $publicPrefixer->prefixPath($folderMainPath);
                    $imageName = pathinfo($_POST['images_main'], PATHINFO_BASENAME);
                    $tempImagePath = FilePathGenerator::uploadedFile($imageName);
                    $imagePath = $tempPrefixer->prefixPath($tempImagePath);

                    $publicDisk->createDirectory($folderMainPath);

                    if (!empty($imagePath)) {
                        $copy_result = $this->upload->copy_images_data(array(
                            'images'      => $imagePath,
                            'destination' => $main_path,
                            'resize'      => config("img.{$module_main}.resize"),
                            'thumbs'      => config("img.{$module_main}.thumbs")
                        ));

                        if (!empty($copy_result['errors'])) {
                            $this->delete_news_post($post_id);

                            jsonResponse($copy_result['errors']);
                        }

                        $update['photo'] = $copy_result[0]['new_name'];
                    }
                }
                //endregion Headline image

                //region Inline images
                // Copying inline images
                if (!empty($post_images)) {
                    $folderPath = TradeNewsPathGenerator::inlineUploadPath($post_id);
                    $photos_path = $publicPrefixer->prefixPath($folderPath);

                    $publicDisk->createDirectory($folderPath);

                    $images = $images = array_filter(array_map(
                        function ($path) use ($tempDisk, $tempPrefixer) {
                            $path = ltrim($path, '/');

                            if ($tempDisk->fileExists($tempImagePath = FilePathGenerator::uploadedFile(pathinfo($path, PATHINFO_BASENAME)))) {
                                return $tempPrefixer->prefixPath($tempImagePath);
                            }
                        },
                        $post_images
                    ));

                    /**
                    * @todo Refactoring Library
                    */

                    if (!empty($images)) {
                        $copy_result = $this->upload->copy_images_data(array(
                            'images'      => $images,
                            'destination' => $photos_path,
                            'change_name' => false
                        ));

                        if (!empty($copy_result['errors'])) {
                            $this->delete_news_post($post_id);

                            jsonResponse($copy_result['errors']);
                        }
                    }
                }

                // Update image stats
                $post_processed_images = $this->get_images_from_text($update['content']);
                $post_inline_images = $this->get_images_stats($post_processed_images);
                $update['inline_images'] = json_encode(array_values($post_inline_images), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                //endregion Inline images

                //region Update
                if (!model('trade_news')->update_trade_news($post_id, $update)) {
                    $this->delete_news_post($post_id);
                }
                //endregion Update

                jsonResponse('Your trade news has been successfully saved.', 'success');
            break;
            case 'edit_news':
                checkPermisionAjax('trade_news_administration');

                //region Validation
                $validator_rules = array(
                    array(
                        'field' => 'title',
                        'label' => 'Title',
                        'rules' => array('required' => '', 'max_len[250]' => ''),
                    ),
                    array(
                        'field' => 'short_description',
                        'label' => 'Short description',
                        'rules' => array('required' => '', 'max_len[500]' => ''),
                    ),
                    array(
                        'field' => 'content',
                        'label' => 'Content',
                        'rules' => array('required' => '', 'html_max_len[20000]' => ''),
                    ),
                    array(
                        'field' => 'upload_folder',
                        'label' => 'Upload folder',
                        'rules' => array('required' => ''),
                    ),
                );

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }
                //endregion Validation

                //region news post check
                $post_id = (int) $_POST['post'];
                if (
                    empty($post_id) ||
                    empty($post = model('trade_news')->get_one_trade_news($post_id))
                ) {
                    jsonResponse('Error: The news does not exist.');
                }
                //endregion news post check

                //region Directory check
                // Check folder with files
                $upload_folder = checkEncriptedFolder(cleanInput($_POST['upload_folder']));
                if (false === $upload_folder) {
                    jsonResponse('File upload path is not correct.');
                }
                //endregion Directory check

                //region Sanitizer
                // Load sanitize library
                $sanitizer = tap(library('Cleanhtml', 'clean'), function ($sanitizer) {
                    $sanitizer->allowIframes();
                    $sanitizer->defaultTextarea(array(
                        'attribute' => 'data-video'
                    ));
                    $sanitizer->addAdditionalTags('<img><h3><h4><h5><h6><p><span><strong><em><b><i><u><a><ol><ul><li><br>');
                });
                //endregion Sanitizer

                //region Text & image processing
                $update = array();
                $post_images = array();
                $post_images_raw = array();

                try {
                    $allowed_amount_of_images = (int) config('max_trade_news_photos_in_text');
                    $post_processed_images = $this->process_content_images($_POST['content'], $allowed_amount_of_images, $post_id);
                    if (!empty($post_processed_images['paths'])) {
                        $post_images = array_flip(array_flip($post_processed_images['paths']));
                    }
                    if (!empty($post_processed_images['collected'])) {
                        $post_images_raw = array_flip(array_flip($post_processed_images['collected']));
                    }

                    $post_content = $sanitizer->sanitize($this->change_content_paths(
                        $_POST['content'],
                        $post_id,
                        $post_images,
                        $upload_folder,
                        $post_images_replaced
                    ));
                } catch (\Exception $exception) {
                    switch ($exception->getCode()) {
                        case self::IMAGES_AMOUNT_EXCEEDED:
                            $message = "Error: You cannot upload more than {$allowed_amount_of_images} photos.";

                            break;
                        case self::IMAGES_INVALID_DOMAIN:
                            $message = 'Error: Image links cannot have external link.';

                            break;
                        default:
                            $message = 'Error: You cannot change this news now. Please try again later.';

                            break;
                    }

                    jsonResponse($message);
                }
                //endregion Text & image processing

                //region Inline images
                // Coppying inline images
                /** @var FilesystemProviderInterface */
                $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                $publicDisk = $storageProvider->storage('public.storage');
                $tempDisk = $storageProvider->storage('temp.storage');
                $publicPrefixer = $storageProvider->prefixer('public.storage');
                $tempPrefixer = $storageProvider->prefixer('temp.storage');
                $folderPath = TradeNewsPathGenerator::inlineUploadPath($post_id);
                $photos_path = $publicPrefixer->prefixPath($folderPath);

                $publicDisk->createDirectory($folderPath);

                if (!empty($post_images)) {

                    $images = $images = array_filter(array_map(
                        function ($path) use ($tempDisk, $tempPrefixer) {
                            $path = ltrim($path, '/');

                            if ($tempDisk->fileExists($tempImagePath = FilePathGenerator::uploadedFile(pathinfo($path, PATHINFO_BASENAME)))) {
                                return $tempPrefixer->prefixPath($tempImagePath);
                            }
                        },
                        $post_images
                    ));

                    /**
                    * @todo Refactoring Library
                    */

                    if (!empty($images)) {
                        $copy_result = $this->upload->copy_images_data(array(
                            'images'      => $images,
                            'destination' => $photos_path,
                            'change_name' => false
                        ));

                        if (!empty($copy_result['errors'])) {
                            jsonResponse($copy_result['errors']);
                        }
                    }
                }

                // Update image stats
                $post_processed_images = $this->get_images_from_text($post_content);
                $post_inline_images = $this->get_images_stats($post_processed_images);
                $post_inline_images = arrayByKey($post_inline_images, 'name');
                $post_inline_files = $publicDisk->listContents($folderPath);
                $post_delete_queue = array();
                foreach ($post_inline_files as $file) {
                    $filename = basename($file->path());
                    if (!isset($post_inline_images[$filename])) {
                        $post_delete_queue[] = $file->path();
                    }
                }
                $post_inline_images = json_encode(array_values($post_inline_images), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                //endregion Inline images

                /**
                * @todo Refactoring Library
                */

                //region Headline image
                // Copy article image
                $photo = null;
                if (!empty($_POST['images_main'])) {
                    $module_main = 'trade_news.main';

                    $tempPrefixer = $storageProvider->prefixer('temp.storage');
                    $folderMainPath = TradeNewsPathGenerator::mainUploadPath($post_id);
                    $main_path = $publicPrefixer->prefixPath($folderMainPath);

                    $imageRelativePath = TradeNewsPathGenerator::mainUploadPath($post_id);
                    $delete_queue = $publicDisk->listContents($folderMainPath);

                        foreach ($delete_queue as $file) {
                            $filename = basename($file->path());
                            if ($publicDisk->fileExists($imageRelativePath.$filename)) {
                                try {
                                    $publicDisk->delete($imageRelativePath.$filename);
                                } catch (\Throwable $th) {
                                    jsonResponse(translate('validation_images_delete_fail'));
                                }
                            }
                        }

                    $publicDisk->createDirectory($folderMainPath);

                    $imageName = pathinfo($_POST['images_main'], PATHINFO_BASENAME);
                    $imagePath = $tempPrefixer->prefixPath(FilePathGenerator::uploadedFile($imageName));

                    if (!empty($imagePath)) {
                        $copy_result = $this->upload->copy_images_data(array(
                            'images'      => $imagePath,
                            'destination' => $main_path,
                            'resize'      => config("img.{$module_main}.resize"),
                            'thumbs'      => config("img.{$module_main}.thumbs")
                        ));

                        if (!empty($copy_result['errors'])) {
                            jsonResponse($copy_result['errors']);
                        }

                        $photo = $copy_result[0]['new_name'];
                    }
                }
                //endregion Headline image

                //region Update
                $post_title = cleanInput($_POST['title']);
                $update = array(
                    'title'                      => $post_title,
                    'title_slug'                 => strForUrl($post_title),
                    'short_description'          => cleanInput($_POST['short_description']),
                    'content'                    => $post_content,
                    'is_visible'                 => (int) filter_var($_POST['visible'], FILTER_VALIDATE_BOOLEAN),
                    'inline_images'              => $post_inline_images,
                );

                if (null !== $photo) {
                    $update['photo'] = $photo;
                }

                if (!model('trade_news')->update_trade_news($post_id, $update)) {
                    jsonResponse('Error: You cannot change this news now. Please try again later.');
                }
                //endregion Update

                //region Clean inline images
                if (!empty($post_delete_queue)) {
                    foreach ($post_delete_queue as $image) {
                        $publicDisk->delete($image);
                    }
                }
                //endregion Clean inline images

                jsonResponse('The changes has been successfully saved.', 'success');
            break;
            case 'change_visible_news':
                checkPermisionAjax('trade_news_administration');

                $id_news = (int) $_POST['news'];
                $news_info = model('trade_news')->get_one_trade_news($id_news);
                if (empty($news_info)) {
                    jsonResponse('The news does not exist.');
                }

                $update = array();
                if ($news_info['is_visible']) {
                    $update['is_visible'] = 0;
                } else {
                    $update['is_visible'] = 1;
                }

                if (model('trade_news')->update_trade_news($id_news, $update)) {
                    jsonResponse('The news has been successfully changed.', 'success');
                }

                jsonResponse('Error: You cannot change this news now. Please try again later.');
            break;
            case 'remove_news':
                checkPermisionAjax('trade_news_administration');

                $news_id = (int) $_POST['news'];
                if (
                    empty($news_id)
                    || !model('trade_news')->exist_trade_news($news_id)
                ) {
                    jsonResponse('The news does not exist.');
                }

                if (!$this->delete_news_post($news_id)) {
                    jsonResponse('Error: You cannot remove this news now. Please try again later.');
                }

                jsonResponse('The news has been successfully removed.', 'success');

            break;
        }
    }

    private function get_images_stats($raw){
        if (null === $raw) {
            return array();
        }

        /** @var FilesystemProviderInterface */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $publicDisk = $storageProvider->storage('public.storage');
        $publicPrefixer = $storageProvider->prefixer('public.storage');
        $post_id = (int) $_POST['post'];

        $images = array();
        foreach ($raw as $key => $url) {
            if (null === ($host = parse_url($url, PHP_URL_HOST))) {
                $path = $url;
            } else {
                list(, $path) = explode($host, $url);
            }

            list(, $path) = explode($host, $url);
            list($realpath) = explode('?', $path);
            $imageName = pathinfo($realpath, PATHINFO_BASENAME);

            $relativePath = TradeNewsPathGenerator::inlineImageUploadPath($post_id, $imageName);
            $fullpath = $publicPrefixer->prefixPath($relativePath);

            if ($publicDisk->fileExists($relativePath)) {
                $imagesize = getimagesize($fullpath);
                $imageinfo = pathinfo($fullpath);
                $post_image = array(
                    'url'       => $url,
                    'path'      => $path,
                    'name'      => $imageinfo['basename'],
                    'filename'  => $imageinfo['filename'],
                    'extension' => $imageinfo['extension'],
                    'width'     => $imagesize[0],
                    'height'    => $imagesize[1],
                    'mime'      => $imagesize['mime'],
                );

                $images[] = $post_image;
            }
        }

        return $images;
    }

    private function change_content_paths($text, $post_id, $source, $upload_folder, &$result = array()){
        if (empty($source)) {
            return $text;
        }

        /** @var FilesystemProviderInterface */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $publicDisk = $storageProvider->storage('public.storage');
        $tempPrefixer = $storageProvider->prefixer('temp.storage');

        /** @var null|UploadedFile */
        $uploadedFile = request()->request->get('images')[0];
        $imageName = pathinfo($uploadedFile, PATHINFO_FILENAME);
        $temp_path = $tempPrefixer->prefixPath(dirname(FilePathGenerator::uploadedFile($imageName)));

        $module = 'trade_news.photos';
        $path = getImgPath($module, array('{ID}' => $post_id));
        $publicDisk->createDirectory($path);

        $base_url = __IMG_URL . $path;
        $base_temp_url = __IMG_URL . $temp_path;

        $replacements = array();
        foreach ($source as $index => $image) {
            if (false !== strpos($text, $temp_url = __IMG_URL . trim($image, '/'))) {
                $result[$index] = $replacements[$temp_url] = $base_url . substr($temp_url, mb_strlen($base_temp_url));

                continue;
            }

            if (false === strpos($image, $temp_path)) {
                $image_name = basename($image);
                $result[$index] = $replacements[$image] = "{$base_url}/{$image_name}";

                continue;
            }

            $result[$index] = $replacements[$image] = $base_url . substr(trim($image, '/'), mb_strlen($temp_path));
        }

        return !empty($replacements) ? strtr($text, $replacements) : $text;
    }

    private function get_images_from_text($text, &$matches = array()){
        /** @var FilesystemProviderInterface */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $publicDisk = $storageProvider->storage('public.storage');

        $urlPath = \parse_url($publicDisk->url('/'), PHP_URL_PATH);
        $escapedPath = preg_quote($urlPath, '/');

        $host = preg_quote(__HTTP_HOST_ORIGIN);
        $pattern = '/<img[^>]*?src=["\'](((\/?' . \ltrim($escapedPath, '\\/') . '[^"\'>]+)|(https?\:\/\/([\w]+\.)?' . $host . $escapedPath . '[^"\'>]+))|(https?\:\/\/([\w]+\.)?' . $host . '[^"\'>]+)|([^"\'\s>]+))["\'][^>]*?>/m';
        preg_match_all($pattern, $text, $matches, PREG_PATTERN_ORDER);
        $images_in_text = array_filter($matches[1]);
        if (empty($images_in_text)) {
            return array();
        }

        return $images_in_text;
    }

    private function process_content_images($text, $allowed_amount, $post_id = null){
        $matches = array();
        $images_in_text = $this->get_images_from_text($text, $matches);
        if (empty($images_in_text)) {
            return array();
        }

        if (count($images_in_text) > $allowed_amount) {
            throw new Exception('Allowed amount of the images in text exceeded', self::IMAGES_AMOUNT_EXCEEDED);
        }

        // Collect external images
        $external_paths = array();
        $petential_threats = $images_in_text;
        foreach ($petential_threats as $image_url) {
            if (!filter_var($image_url, FILTER_VALIDATE_URL)) {
                continue;
            }

            $image_host = parse_url($image_url, PHP_URL_HOST);
            if (
                __HTTP_HOST_ORIGIN !== $image_host &&
                !endsWith($image_host, __HTTP_HOST_ORIGIN)
            ) {
                $external_paths[] = $image_url;
            }
        }
        $external_paths = array_filter($external_paths);
        if (!empty($external_paths)) {
            throw new Exception('Images from external domains are not allowed', self::IMAGES_INVALID_DOMAIN);
        }

        $path = null;
        if (null !== $post_id) {
            $module = 'trade_news.photos';
            $path = getImgPath($module, array('{ID}' => $post_id));
        }

        $images_paths = array();
        $temporary_images = array_filter($matches[1]);
        foreach ($temporary_images as $key => $image) {
            if (null !== parse_url($image, PHP_URL_HOST)) {
                if (null !== $path && false !== strpos($image, $path)) {
                    continue;
                }

                if (false === strpos($image, __HTTP_HOST_ORIGIN)) {
                    throw new Exception('Images from external domains are not allowed', self::IMAGES_INVALID_DOMAIN);

                    continue;
                }

                list(, $path) = explode(__HTTP_HOST_ORIGIN, $image);
                $image = '/' . trim($path, '/');
            }

            $images_paths[] = $image;
        }

        return array(
            'collected' => $temporary_images,
            'paths'     => $images_paths,
        );
    }

    private function delete_news_post($post_id){
        if (empty($post_id)) {
            return false;
        }

        if (!model('trade_news')->delete_trade_news((int) $post_id)) {
            return false;
        }

        /** @var FilesystemProviderInterface */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $publicDisk = $storageProvider->storage('public.storage');

        try {
            $publicDisk->deleteDirectory(TradeNewsPathGenerator::mainUploadPath($post_id));
        } catch (\Throwable $th) {
            //
        }

        return true;
    }

    public function ajax_news_upload_photo(){
        checkIsAjax();
        checkIsLoggedAjax();
        checkPermisionAjax('trade_news_administration');

        $files = arrayGet($_FILES, 'files');
		if (null === $files) {
			jsonResponse('Error: Please select file to upload.');
        }

        $upload_folder = $this->uri->segment(3);
		if(!($upload_folder=checkEncriptedFolder($upload_folder))){
			jsonResponse('Error: File upload path is not correct.');
        }

        /**
        * @todo Refactoring Library
        */
        /** @var null|UploadedFile */
        $uploadedFile = (request()->files->get('files'));
        if (empty($uploadedFile)) {
            jsonResponse('Please select file to upload.');
        }

        /** @var FilesystemProviderInterface */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $tempDisk = $storageProvider->storage('temp.storage');
        $tempDiskPrefixer = $storageProvider->prefixer('temp.storage');
        $imageName = sprintf('%s.%s', \bin2hex(\random_bytes(16)), 'jpg');

        $tempDisk->createDirectory(
            $path = dirname(FilePathGenerator::uploadedFile($imageName))
        );

        $imagePath = $tempDisk->url(FilePathGenerator::uploadedFile($imageName));
        $module = 'trade_news.main';

        $copy_result = library('upload')->upload_images_data(array(
            'files'             => ['tmp_name' => $uploadedFile->getRealPath(), 'name' => $imageName],
            'use_original_name' => true,
			'destination'       => $tempDiskPrefixer->prefixDirectoryPath($path),
			'resize'            => config("img.{$module}.resize"),
			'rules'             => config("img.{$module}.rules")
		));

		if (!empty($copy_result['errors'])) {
			jsonResponse($copy_result['errors']);
        }

        $files = array(
			"path"    => $imagePath,
			"thumb"   => $imagePath,
			"tmp_url" => $imagePath,
		);

		jsonResponse('Main photo was successfully uploaded.', 'success', $files);
    }

    /**
     * @deprecated Alexei Tolmachinski [2022-06-06]
     */
    // public function ajax_news_delete_files(){
    //     checkIsAjax();
    //     checkIsLoggedAjax();
    //     checkPermisionAjax('trade_news_administration');

    //     if (empty($_POST['file'])) {
    //         jsonResponse('File name is not correct.');
    //     }

    //     $name = cleanInput($_POST['file']);
	// 	$upload_folder = $this->uri->segment(3);
	// 	if(!($upload_folder=checkEncriptedFolder($upload_folder))){
	// 		jsonResponse('Error: File upload path is not correct.');
	// 	}

	// 	$module = 'trade_news.main';
	// 	$id_user = id_session();
	// 	$path = getTempImgPath($module, array('{ID}' => $id_user, '{FOLDER}' => $upload_folder));

	// 	if(!is_dir($path)){
	// 		jsonResponse('Error: Upload path is not correct.');
	// 	}

	// 	removeFileByPatternIfExists($path.$name, $path.'*'.$name);

    //     jsonResponse('','success');
    // }

    public function upload_photo(){
        checkIsLoggedAjax();
        checkPermisionAjax('trade_news_administration');

        $files = arrayGet($_FILES, 'userfile');
		if (null === $files) {
			jsonResponse('Error: Please select file to upload.');
        }

		$upload_folder = $this->uri->segment(3);
		if(!($upload_folder=checkEncriptedFolder($upload_folder))){
			jsonResponse('Error: File upload path is not correct.');
        }

        /**
        * @todo Refactorin Library
        */
        /** @var null|UploadedFile */
        $uploadedFile = request()->files->get('userfile');

        if (empty($uploadedFile)) {
            jsonResponse('Please select file to upload.');
        }

        /** @var FilesystemProviderInterface */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $tempDisk = $storageProvider->storage('temp.storage');
        $tempDiskPrefixer = $storageProvider->prefixer('temp.storage');
        $imageName = sprintf('%s.%s', \bin2hex(\random_bytes(16)), 'jpg');

        $tempDisk->createDirectory(
            $path = dirname(FilePathGenerator::uploadedFile($imageName))
        );

        $module = 'trade_news.photos';

		$upload_result = library('upload')->upload_images_data(array(
			'files'             => ['tmp_name' => $uploadedFile->getRealPath(), 'name' => $imageName],
            'use_original_name' => true,
			'destination'       => $tempDiskPrefixer->prefixDirectoryPath($path),
			'resize'            => config("img.{$module}.resize"),
			'rules'             => config("img.{$module}.rules"),
		));

		if (!empty($upload_result['errors'])) {
			jsonResponse($upload_result['errors']);
        }

		jsonResponse('Company pictures was successfully uploaded.', 'success', array('file' => '/public/temp/' . (FilePathGenerator::uploadedFile($imageName))));
    }
}
?>
