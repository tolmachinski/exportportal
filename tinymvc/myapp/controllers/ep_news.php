<?php

use App\Common\Contracts\CommentType;
use App\Filesystem\EpNewsImagePathGenerator;
use App\Filesystem\FilePathGenerator;
use App\Filesystem\MassMediaFilesPathGenerator;
use ExportPortal\Contracts\Filesystem\FilesystemOperator;
use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;
use League\Flysystem\PathPrefixer;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
* @author Bendiucov Tatiana
* @todo Refactoring [15.12.2021]
* Controller Refactoring
 */
class Ep_News_Controller extends TinyMVC_Controller {

    private FilesystemOperator $storage;

    private FilesystemOperator $tempStorage;

    private PathPrefixer $prefixer;

    private PathPrefixer $tempPrefixer;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        /** @var FilesystemProviderInterface */
        $storageProvider = $container->get(FilesystemProviderInterface::class);

        $this->storage = $storageProvider->storage('public.storage');
        $this->tempStorage = $storageProvider->storage('temp.storage');

        $this->prefixer = $storageProvider->prefixer('public.storage');
        $this->tempPrefixer = $storageProvider->prefixer('temp.storage');
    }

	function index() {
        $uri = uri()->uri_to_assoc(4);

        checkURI($uri, array('page'));
        checkIsValidPage($uri['page']);

        /**
         * @var Ep_News_Model $ep_news_model
         */
        $ep_news_model = model(Ep_News_Model::class);

        $this->breadcrumbs = array(
            array(
                'link' 	=> __SITE_URL . 'about',
                'title'	=> translate('about_us_nav_about_us')
            ),
            array(
                'link' 	=> __SITE_URL.'about/in_the_news?hash=ep_news',
                'title'	=> translate('about_us_nav_in_the_news')
            ),
            array(
                'link'	=> __SITE_URL . 'ep_news',
                'title'	=> translate('breadcrumb_ep_news')
            )
        );

		$links_map = array(
            'page' => array(
				'type' => 'uri',
				'deny' => array('page'),
            ),
            'keywords' => array(
                'type' => 'get',
				'deny' => array('page', 'keywords'),
            ),
        );

        $links_tpl = uri()->make_templates($links_map, $uri);
        $links_tpl_without = uri()->make_templates($links_map, $uri, true);

        $per_page = (int) config('ep_news_per_page', 10);
        $page = empty($uri['page']) ? 1 : (int) $uri['page'];

        $news_params = array(
            'select'    => 'id, title, description, content, date_time, main_image, url',
            'per_p'     => $per_page,
            'from'      => $per_page * ($page - 1),
        );

        $keywords = $_GET['keywords'] ?? '';
        if (!empty($keywords)) {
            $search = trim(cleanInput(cut_str($_GET['keywords'])));

            if (!empty($search)) {
                $news_params['keywords'] = $search;
            }
        }

        $total_news = $ep_news_model->get_list_ep_news_public_count($news_params);

        $paginator_config = array(
            'replace_url'   => true,
            'total_rows'    => $total_news,
            'first_url'     => rtrim('ep_news/' . $links_tpl_without['page'], '/'),
            'base_url'      => 'ep_news/' . $links_tpl['page'],
            'per_page'      => $per_page,
        );

        if (!empty($keywords)) {
            $paginator_config['suffix'] = '?' . http_build_query(array('keywords' => $keywords));
        }

        library('pagination')->initialize($paginator_config);

        $partial_search_params = array(
            'action' => __SITE_URL . 'ep_news',
            'keywords' => $keywords,
            'title' => translate('ep_news_sidebar_search_block_title'),
            'input_text_placeholder' => translate('ep_news_sidebar_search_block_keywords_placeholder', null, true),
            'btn_text_submit' => translate('ep_news_sidebar_search_block_submit_btn'),
        );

        $epNews = $ep_news_model->get_list_ep_news_public($news_params);

        foreach ($epNews as &$oneNews) {
            $oneNews['imageUrl'] = $this->storage->url(EpNewsImagePathGenerator::defaultPublicImagePath($oneNews['main_image']));
        }

        $data = array(
            'sidebar_right_content' => 'new/partial_sidebar_search_view',
            'header_out_content'    => 'new/about/in_the_news/header_view',
            'main_content'          => 'new/ep_news/index_view',
            'breadcrumbs'           => $this->breadcrumbs,
            'pagination'            => library('pagination')->create_links(),
            'nav_active'            => 'in the news',
            'keywords'              => $keywords,
            'ep_news'               => $epNews,
            'per_p'                 => $per_page,
            'count'                 => $total_news,
            'page'                  => $page,
            'partial_search_params' => $partial_search_params,
            'header_title'          => translate('about_us_in_the_news_news_header_title'),
            'header_img'            => 'in_the_news_header3.jpg'
        );

        if ($page > 1) {
            $data['meta_params'] = array('[PAGE]' => $page);
        }

        views()->assign($data);
        views()->display('new/index_template_view');
    }

	function detail(){
        $uri = uri()->uri_to_assoc(5);
        checkURI($uri, array());

        /**
         * @var Ep_News_Model $ep_news_model
         */
        $ep_news_model = model(Ep_News_Model::class);

        $id_news = id_from_link(uri()->segment(3));
        if (empty($id_news) || empty($news_details = $ep_news_model->get_one_ep_news_public($id_news, __SITE_LANG))) {
            show_404();
        }

        $news_details['imageUrl'] = $this->storage->url(EpNewsImagePathGenerator::defaultPublicImagePath($news_details['main_image']));

        $this->breadcrumbs = array(
            array(
                'link' 	=> __SITE_URL.'about',
                'title'	=> translate('about_us_nav_about_us')
            ),
            array(
                'link' 	=> __SITE_URL.'about/in_the_news?hash=ep_news',
                'title'	=> translate('about_us_nav_in_the_news')
            ),
            array(
                'link'	=> __SITE_URL . 'ep_news',
                'title'	=> translate('breadcrumb_ep_news')
            ),
            array(
                'link'  => '',
                'title' => cleanOutput($news_details['title'])
            )
        );

        $partial_search_params = array(
            'action' => __SITE_URL . 'ep_news',
            'keywords' => '',
            'title' => translate('ep_news_sidebar_search_block_title'),
            'input_text_placeholder' => translate('ep_news_sidebar_search_block_keywords_placeholder', null, true),
            'btn_text_submit' => translate('ep_news_sidebar_search_block_submit_btn'),
        );

        $epNews = $ep_news_model->get_other_ep_news($id_news, __SITE_LANG, config('count_other_news_on_news_detail', 10));

        foreach ($epNews as &$epOneNews) {
            $epOneNews['imageUrl'] = $this->storage->url(EpNewsImagePathGenerator::defaultPublicImagePath($epOneNews['main_image'] ?: 'no-mage.jpg'));
        }

        $data = array(
            'sidebar_right_content' => 'new/partial_sidebar_search_view',
            'main_content'          => 'new/ep_news/details/index_view',
            'footer_content'        => 'new/trade_news/mobile_wrapper',
            'meta_params'           => array('[NEWS_NAME]' => $news_details['title']),
            'news_detail'           => $news_details,
            'breadcrumbs'           => $this->breadcrumbs,
            'nav_active'            => 'in the news',
            'ep_news'               => $epNews,
            'partial_search_params' => $partial_search_params,
            'comments'              => array(
                'hash_components'   => newsCommentsResourceHashComponents($id_news),
                'type_id'           => CommentType::NEWS()->value,
            ),
        );

        views()->assign($data);
        views()->display('new/index_template_view');
	}

    public function administration() {
        checkAdmin('ep_news_administration');

        views(
            [
                'admin/header_view',
                'admin/ep_news/index_view',
                'admin/footer_view'
            ],
            [
                'title' => 'EP News'
            ]
        );
    }

    function ajax_ep_news_administration() {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        checkAdminAjaxDT('ep_news_administration');

        /** @var Ep_News_Model $epNewsModel */
        $epNewsModel = model(Ep_News_Model::class);

        $params = array_merge(
            dtConditions($_POST, [
                ['as' => 'date_to',     'key' => 'date_to',     'type' => 'getDateFormat:m/d/Y,Y-m-d'],
                ['as' => 'date_from',   'key' => 'date_from',   'type' => 'getDateFormat:m/d/Y,Y-m-d'],
                ['as' => 'visible',     'key' => 'visible',     'type' => 'int'],
                ['as' => 'keywords',    'key' => 'keywords',    'type' => 'cleanInput']
            ]),
            [
                'per_p'     => (int) $_POST['iDisplayLength'],
                'start'     => (int) $_POST['iDisplayStart'],
                'sort_by'   => flat_dt_ordering($_POST, [
                    'dt_date_time'  => 'date_time',
                    'dt_title'      => 'title',
                    'dt_id'         => 'id',
                ]),
            ]
        );

        $ep_news = $epNewsModel->get_ep_news($params);
        $ep_news_count = $epNewsModel->get_ep_news_counter($params);

        $output = [
            "iTotalDisplayRecords"  => $ep_news_count,
            "iTotalRecords"         => $ep_news_count,
			'aaData'                => [],
            "sEcho"                 => (int) $_POST['sEcho'],
        ];

        if (empty($ep_news)) {
			jsonResponse('', 'success', $output);
        }

		foreach ($ep_news as $one_news) {
			$visible_btn = '<a class="ep-icon ep-icon_' . (($one_news['visible']) ? '' : 'in') . 'visible confirm-dialog" data-callback="change_visible_ep_news" data-id="' . $one_news['id'] . '" data-message="Are you sure you want to change the visibility status of this EP news?" href="#" title="Set EP news ' . (($one_news['visible']) ? 'active' : 'inactive') . '"></a>';

            $langs = array();
            $langs_record = array_filter(json_decode($one_news["translations_data"], true));
            $langs_record_list = array("English");
            if(!empty($langs_record)){
                foreach ($langs_record as $lang_key => $lang_record) {
                    if($lang_key == "en"){
                        continue;
                    }

                    $langs[] = '<li>
                                    <div class="flex-display">
                                        <span class="display-ib_i mw-150 lh-30 pl-5 pr-10 text-nowrap">'.$lang_record["lang_name"].'</span>
                                        <a class="display-ib_i lh-30 mb-0 p-0 ep-icon ep-icon_remove txt-red confirm-dialog" data-callback="delete_ep_news_i18n" data-ep-news-id="' . $one_news["id"] . '" data-ep-news-i18n-lang="'.$lang_record['abbr_iso2'].'" title="Delete" data-message="Are you sure you want to delete the ep news translation?" href="#" ></a>
                                        <a class="display-ib_i lh-30 mb-0 p-0 ep-icon ep-icon_pencil fancyboxValidateModalDT fancybox.ajax" href="'.__SITE_URL.'ep_news/popup_forms/edit_ep_news_i18n/'.$one_news["id"]."/".$lang_record["abbr_iso2"].'" data-title="Edit ep news translation" title="Edit"></a>
                                    </div>
                                </li>';
                    $langs_record_list[] = $lang_record["lang_name"];
                }
                $langs[] = '<li role="separator" class="divider"></li>';
            }

            $langs_dropdown = '<div class="dropdown">
                                <a class="ep-icon ep-icon_globe-circle m-0 fs-24 dropdown-toggle" data-toggle="dropdown"></a>
                                <ul class="dropdown-menu">
                                    '.implode("", $langs).'
                                    <li><a href="'.__SITE_URL.'ep_news/popup_forms/add_ep_news_i18n/'.$one_news["id"].'" data-title="Add translation" title="Add translation" class="fancyboxValidateModalDT fancybox.ajax">Add translation</a></li>
                                </ul>
                            </div>';

            $imageUrl = $this->storage->url(EpNewsImagePathGenerator::defaultPublicImagePath($one_news['main_image']));
            $thumbImageUrl = $this->storage->url(EpNewsImagePathGenerator::thumbPublicImagePath($one_news['main_image']));

			$output["aaData"][] = array(
				'dt_id' => $one_news['id'] . '<br/><a rel="view_details" title="View details" class="ep-icon ep-icon_plus"></a>',
				'dt_title' => $one_news['title'],
				'dt_main_image' => '<a class="fancyboxGallery" href="'. $imageUrl . '" data-title="'.$one_news['title'].'" title="'.$one_news['title'].'"><img class="h-100" src="'. $thumbImageUrl .'" alt="news"></a>',
				'dt_content' => $one_news['content'],
				'dt_description' => $one_news['description'],
				'dt_date_time' => getDateFormat($one_news['date_time']),
				'dt_actions' =>
					$visible_btn
					. '<a href="ep_news/popup_forms/edit_ep_news/'. $one_news['id'] . '" class="fancyboxValidateModalDT fancybox.ajax ep-icon ep-icon_pencil" data-title="Edit EP news" title="Edit this EP news"></a>'
					. '<a class="ep-icon ep-icon_remove txt-red confirm-dialog" data-callback="remove_ep_news" data-id="' . $one_news['id'] . '" title="Remove this EP news" data-message="Are you sure you want to delete this EP news?" href="#" ></a>',
                "dt_tlangs" => $langs_dropdown,
                "dt_tlangs_list" => implode(", ", $langs_record_list)
			);
		}

        jsonResponse("", "success", $output);
    }

    public function popup_forms() {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        checkAdminAjaxModal('ep_news_administration');

        $op = $this->uri->segment(3);
        switch ($op) {
            case 'add_ep_news':
				$data['upload_folder'] = encriptedFolderName();

                global $tmvc;
				$this->load->model('Ep_News_Model', 'ep_news');

                $formats = explode(',', config('fileupload_image_formats', 'jpg,jpeg,png,gif,bmp'));
                $mimetypes = array_filter(array_unique(array_map(
                    function ($extension) {
                        return Hoa\Mime\Mime::getMimeFromExtension($extension);
                    },
                    $formats
                )));
                $data['accept'] = implode(', ', $mimetypes);
                $data['formats'] = implode('|', $formats);
                $data['mimetypes'] = implode('|', array_map(function ($type) { return preg_quote($type); }, $mimetypes));

		        $data['fileupload_max_file_size'] = $tmvc->my_config['fileupload_max_file_size'];
                $this->view->display('admin/ep_news/modal_form_view', $data);
			break;
            case 'edit_ep_news':
                $data['upload_folder'] = encriptedFolderName();

                $formats = explode(',', 'jpg,jpeg');
                $mimetypes = array_filter(array_unique(array_map(
                    function ($extension) {
                        return Hoa\Mime\Mime::getMimeFromExtension($extension);
                    },
                    $formats
                )));
                $data['accept'] = implode(', ', $mimetypes);
                $data['formats'] = implode('|', $formats);
                $data['mimetypes'] = implode('|', array_map(function ($type) { return preg_quote($type); }, $mimetypes));

                $id_ep_news = (int)$this->uri->segment(4);
				$this->load->model('Ep_News_Model', 'ep_news');
				$data['ep_news'] = $this->ep_news->get_one_ep_news($id_ep_news);
                $data['ep_news']['imageUrl'] = $this->storage->url(EpNewsImagePathGenerator::defaultPublicImagePath($data['ep_news']['main_image']));

                $data['fileupload_max_file_size'] = config('fileupload_max_file_size');
				$this->view->display('admin/ep_news/modal_form_view', $data);
			break;
            case "add_ep_news_i18n":
				$this->load->model("Ep_News_Model", "ep_news");
                $id_ep_news = intval($this->uri->segment(4));
				$data["ep_news"] = $this->ep_news->get_one_ep_news($id_ep_news);
				$data["tlanguages"] = $this->translations->get_languages();

                $this->view->display("admin/ep_news/form_i18n_view", $data);
            break;
            case "edit_ep_news_i18n":
                $ep_news_i18n_lang = $this->uri->segment(5);
                if(empty($ep_news_i18n_lang)) {
                    messageInModal("Error: Lang is not setted.", $type = "errors");
                }

                $data = array();

				$this->load->model("Ep_News_Model", "ep_news");
				$id_ep_news = intval($this->uri->segment(4));
				$data["ep_news"] = $this->ep_news->get_one_ep_news($id_ep_news);
                if(empty($data['ep_news'])) {
                    messageInModal('Error: Could not find the article.', $type = 'errors');
                }

                $data['ep_news_i18n'] = $this->ep_news->get_one_ep_news_i18n(array("id_ep_news" => $id_ep_news, "ep_news_i18n_lang" => $ep_news_i18n_lang));
                if(empty($data['ep_news_i18n'])) {
                    messageInModal('Error: Could not find the translation.', $type = 'errors');
                }

				$data['tlanguages'] = $this->translations->get_languages();

                $this->view->display('admin/ep_news/form_i18n_view', $data);
            break;
        }
    }

    function ajax_ep_news_upload_photo() {
        checkAdminAjax('ep_news_administration');

        if (empty($files = $_FILES['files'])) {
			jsonResponse('Please select file to upload.');
        }

        if (!empty($epNewsId = (int) uri()->segment(4))) {
            /** @var Ep_News_Model $epNewsModel */
            $epNewsModel = model(Ep_News_Model::class);

            if (empty($epNews = $epNewsModel->get_one_ep_news($epNewsId, 'main_image'))) {
                jsonResponse(translate('systmess_error_invalid_data'));
            }

            if (!empty($epNews['main_image'])) {
                jsonResponse('This EP news already has a photo. Before uploading a new photo, please remove the old one.');
            }
        }

        /** @var null|UploadedFile */
        $uploadedFile = request()->files->get('files')[0] ?? null;
        if (null === $uploadedFile) {
            jsonResponse(translate('events_speaker_pictures_select_file_message'));
		}

        if (!$uploadedFile->isValid()) {
            jsonResponse(translate('events_speaker_pictures_invalid_file_message'));
		}

        $imageConfigModule = 'ep_news.main';
        $imageName = sprintf('%s.%s', \bin2hex(\random_bytes(16)), 'jpg');
        $imageTempPath = FilePathGenerator::uploadedFile($imageName);

        $tempDirectory = dirname($imageTempPath);

        $fullPath = $this->tempPrefixer->prefixPath($tempDirectory);
        $this->tempStorage->createDirectory($tempDirectory);

		// Count number of files in this folder, to prevent upload more files than photo limit
		$fi = new FilesystemIterator($fullPath, FilesystemIterator::SKIP_DOTS);
        if (iterator_count($fi) >= 1 || count($files['name']) > 1) {
            jsonResponse('You cannot upload more than 1 photo.');
        }

        /** @var TinyMVC_Library_Image_intervention $interventionImageLibrary */
        $interventionImageLibrary = library(TinyMVC_Library_Image_intervention::class);

        $images = $interventionImageLibrary->image_processing(
            [
                'tmp_name' => $uploadedFile->getRealPath(),
                'name' => pathinfo($imageName, PATHINFO_FILENAME)
            ],
            [
                'destination'   => $fullPath,
                'use_original_name' => true,
                'rules'         => config("img.{$imageConfigModule}.rules"),
                'handlers'      => [
                    'create_thumbs' => config("img.{$imageConfigModule}.thumbs"),
                    'resize'        => config("img.{$imageConfigModule}.resize"),
                ],
            ]
        );

        if (!empty($images['errors'])) {
            jsonResponse($images['errors']);
        }

        jsonResponse(
            '',
            'success',
            [
                'files' => [
                    [
                        'path' => $this->tempStorage->url($imageTempPath),
                        'name' => $images[0]['new_name'],
                    ],
                ],
            ],
        );
    }

    function ajax_ep_news_delete_files() {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        checkAdminAjax('ep_news_administration');

        if (empty($_POST["file"])) {
            jsonResponse("Error: File name is not correct.");
        }

        $name = $_POST["file"];
        $path = FilePathGenerator::uploadedFile($name);
        if ($this->tempStorage->fileExists($path)) {
            jsonResponse("Error: Upload path is not correct.");
        }

        /** @var FilesystemProviderInterface */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $tempDisk = $storageProvider->storage('temp.storage');

        try {
            $tempDisk->delete($path);
        } catch (\Throwable $th) {
           jsonResponse(translate('systmess_error_news_image_delete_fail'));
        }

        if (!empty($mainImageThumbs = config("img.ep_news.main.thumbs"))) {

            foreach ($mainImageThumbs as $mainImageThumb) {
                $thumbName = str_replace('{THUMB_NAME}', $name, $mainImageThumb['name']);
                try {
                    $tempDisk->delete(dirname($path). '/' . $thumbName);
                } catch (\Throwable $th) {
                    jsonResponse(translate('systmess_error_news_image_delete_fail'));
                }

            }
        }

		jsonResponse("","success");
    }

    function ajax_ep_news_delete_db_photo() {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        checkAdminAjax('ep_news_administration');

		if (empty($_POST['file'])) {
            jsonResponse('Error: File name is not correct.');
        }

        $id_ep_news = intVal($_POST['file']);
        $this->load->model('Ep_News_Model', 'ep_news');
        $ep_news_info = $this->ep_news->get_one_ep_news($id_ep_news, 'main_image');

        $name = $ep_news_info['main_image'];

        try {
            $this->storage->delete(EpNewsImagePathGenerator::defaultPublicImagePath($name)) ;
        } catch (\Throwable $th) {
           jsonResponse(translate('systmess_error_news_image_delete_fail'));
        }

        if (!empty($mainImageThumbs = config("img.ep_news.main.thumbs"))) {
            foreach ($mainImageThumbs as $mainImageThumb) {
                $thumbName = str_replace('{THUMB_NAME}', $name, $mainImageThumb['name']);

                try {
                    $this->storage->delete(EpNewsImagePathGenerator::defaultPublicImagePath($thumbName),
                );
                } catch (\Throwable $th) {
                    jsonResponse(translate('systmess_error_news_image_save_failed'));
                }
            }
        }

        $this->ep_news->update_ep_news($id_ep_news, array('main_image' => ''));
        jsonResponse('EP news image was deleted.', 'success');
    }

    public function ajax_ep_news_operations() {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        checkAdminAjax('ep_news_administration');

        $op = $this->uri->segment(3);

        switch ($op) {
            case 'change_visible_ep_news':
                $id_ep_news = intVal($_POST['id']);
                $this->load->model('Ep_News_Model', 'ep_news');

                $ep_news_info = $this->ep_news->get_one_ep_news($id_ep_news, 'visible');

                if (empty($ep_news_info)) {
                    jsonResponse('Error: This EP news does not exist.');
                }

				$update = array('visible' => intVal(!(bool)$ep_news_info['visible']));

                if ($this->ep_news->update_ep_news($id_ep_news, $update)) {
                    jsonResponse('The EP news has been successfully changed.', 'success');
                } else {
                    jsonResponse('Error: You cannot change this EP news now. Please try again later.');
                }
            break;
            case 'remove_ep_news':
                $id_ep_news = intVal($_POST['id']);
				$this->load->model('Ep_News_Model', 'ep_news');

                $ep_news_info = $this->ep_news->get_one_ep_news($id_ep_news, 'main_image');
                if (empty($ep_news_info)) {
                    jsonResponse('This EP news does not exist.');
                }

                /** @var FilesystemProviderInterface */
                $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                $publicDisk = $storageProvider->storage('public.storage');

                $name = $ep_news_info['main_image'];

                try {
                    $publicDisk->delete(EpNewsImagePathGenerator::defaultPublicImagePath($name)) ;
                } catch (\Throwable $th) {
                    jsonResponse(translate('systmess_error_news_image_delete_fail'));
                }

                if (!empty($mainImageThumbs = config("img.ep_news.main.thumbs"))) {

                    foreach ($mainImageThumbs as $mainImageThumb) {
                        $thumbName = str_replace('{THUMB_NAME}', $name, $mainImageThumb['name']);
                        try {
                            $publicDisk->delete(EpNewsImagePathGenerator::defaultPublicImagePath($thumbName));
                        } catch (\Throwable $th) {
                            jsonResponse(translate('systmess_error_news_image_delete_fail'));
                        }
                    }
                }

                if ($this->ep_news->delete_ep_news($id_ep_news)) {
                    jsonResponse('The EP news has been successfully removed.', 'success');
                } else {
                    jsonResponse('Error: You cannot remove this EP news now. Please try again later.');
                }
            break;
            case 'edit_ep_news':
                $validator_rules = array(
                    array(
                        'field' => 'title',
                        'label' => 'Title',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'content',
                        'label' => 'Content',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'description',
                        'label' => 'Short description',
                        'rules' => array('required' => '')
                    )
                );

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                /** @var TinyMVC_Library_Cleanhtml $cleanhtmlLibrary */
                $cleanhtmlLibrary = library(TinyMVC_Library_Cleanhtml::class);

                $request = request()->request;
                $epNewsId = $request->getInt('id');

                $title = cleanInput($request->get('title'));
                $update = [
                    'title'         => $title,
                    'content'       => $cleanhtmlLibrary->sanitizeUserInput($request->get('content')),
                    'description'   => cleanInput($request->get('description')),
                    'visible'       => $request->getInt('visible'),
                    'url'           => strForUrl($title) . '-' . $epNewsId
                ];

                if (!empty($tempImage = $request->get('image'))) {
                    $imageName = pathinfo($tempImage, PATHINFO_BASENAME);

                    /** @var FilesystemProviderInterface */
                    $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                    $publicDisk = $storageProvider->storage('public.storage');

                    try {
                        $publicDisk->write(EpNewsImagePathGenerator::defaultPublicImagePath($imageName),
                            $this->tempStorage->read(FilePathGenerator::uploadedFile($imageName))
                        );
                    } catch (\Throwable $th) {
                        jsonResponse(translate('systmess_error_news_image_save_failed'));
                    }

                    if (!empty($mainImageThumbs = config("img.ep_news.main.thumbs"))) {
                        $imageTempPath = dirname(FilePathGenerator::uploadedFile($imageName));
                        foreach ($mainImageThumbs as $mainImageThumb) {
                            $thumbName = str_replace('{THUMB_NAME}', $imageName, $mainImageThumb['name']);

                            try {
                                $publicDisk->write(EpNewsImagePathGenerator::defaultPublicImagePath($thumbName),
                                $this->tempStorage->read($imageTempPath . '/' . $thumbName)
                            );
                            } catch (\Throwable $th) {
                                jsonResponse(translate('systmess_error_news_image_save_failed'));
                            }
                        }
                    }

					$update['main_image'] = $imageName;
				}

                /** @var Ep_News_Model $epNewsModel */
                $epNewsModel = model(Ep_News_Model::class);

                if (!$epNewsModel->update_ep_news($epNewsId, $update)) {
                    jsonResponse('You cannot change this EP news now. Please try again later.');
                }

                jsonResponse('The EP news has been successfully changed.', 'success');
            break;
            case "add_ep_news":
                $validator_rules = array(
                    array(
                        "field" => "title",
                        "label" => "Title",
                        "rules" => array("required" => "")
                    ),
                    array(
                        "field" => "content",
                        "label" => "Content",
                        "rules" => array("required" => "")
                    ),
                    array(
                        "field" => "description",
                        "label" => "Short description",
                        "rules" => array("required" => "")
                    )
                );

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $request = request()->request;

                /** @var TinyMVC_Library_Cleanhtml $cleanhtmlLibrary */
                $cleanhtmlLibrary = library(TinyMVC_Library_Cleanhtml::class);

                $title = cleanInput($request->get('title'));

                $insert = [
                    'description'   => cleanInput($request->get('description')),
                    'content'       => $cleanhtmlLibrary->sanitizeUserInput($request->get('content')),
                    'title'         => $title,
                    'visible'       => empty($request->get('visible')) ? 0 : 1,
                ];

				if (!empty($tempImage = $request->get('image'))) {
                    $imageName = pathinfo($tempImage, PATHINFO_BASENAME);

                    /** @var FilesystemProviderInterface */
                    $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                    $publicDisk = $storageProvider->storage('public.storage');

                    try {
                        $publicDisk->write(EpNewsImagePathGenerator::defaultPublicImagePath($imageName),
                            $this->tempStorage->read(FilePathGenerator::uploadedFile($imageName))
                        );
                    } catch (\Throwable $th) {
                        jsonResponse(translate('systmess_error_news_image_save_failed'));
                    }

                    if (!empty($mainImageThumbs = config("img.ep_news.main.thumbs"))) {
                        $imageTempPath = dirname(FilePathGenerator::uploadedFile($imageName));
                        foreach ($mainImageThumbs as $mainImageThumb) {
                            $thumbName = str_replace('{THUMB_NAME}', $imageName, $mainImageThumb['name']);

                            try {
                                $publicDisk->write(EpNewsImagePathGenerator::defaultPublicImagePath($thumbName),
                                    $this->tempStorage->read($imageTempPath . '/' . $thumbName)
                                );
                            } catch (\Throwable $th) {
                                jsonResponse(translate('systmess_error_news_image_save_failed'));
                            }

                        }
                    }

					$insert['main_image'] = $imageName;
				}

                /** @var Ep_News_Model $epNewsModel */
                $epNewsModel = model(Ep_News_Model::class);

                if (empty($epNewsId = $epNewsModel->insert_ep_news($insert))) {
                    jsonResponse('You cannot add EP news now. Please try again later.');
                }

                $epNewsModel->update_ep_news(
                    $epNewsId,
                    [
                        'url' => strForUrl($title) . '-' . $epNewsId
                    ],
                );

                jsonResponse("The EP news has been successfully changed.", "success");
            break;
            case "delete_ep_news_i18n":
				$this->load->model("Ep_News_Model", "ep_news");
				$id_ep_news = intval($_POST["id_ep_news"]);
                $ep_news= $this->ep_news->get_one_ep_news($id_ep_news);
                if (empty($ep_news)) {
                    jsonResponse("This EP news does not exist.");
                }

				$ep_news_i18n_lang = cleanInput($_POST["ep_news_i18n_lang"]);
				$ep_news_i18n = $this->ep_news->get_one_ep_news_i18n(array("id_ep_news" => $id_ep_news, "ep_news_i18n_lang" => $ep_news_i18n_lang));
				if(empty($ep_news_i18n)){
					jsonResponse("Error: The ep news translation does not exist.");
				}

				$translations_data = json_decode($ep_news["translations_data"], true);
				unset($translations_data[$ep_news_i18n_lang]);
				$this->ep_news->update_ep_news($id_ep_news, array("translations_data" => json_encode($translations_data)));
				$this->ep_news->delete_ep_news_i18n($ep_news_i18n["id_ep_news_i18n"]);

				jsonResponse("The article translation has been successfully deleted.", "success");
            break;
            case "save_ep_news_i18n":
                $validator_rules = array(
                    array(
                        "field" => "ep_news_i18n_title",
                        "label" => "Title",
                        "rules" => array("required" => "", "max_len[200]" => "")
                    ),
                    array(
                        "field" => "ep_news_i18n_description",
                        "label" => "Description",
                        "rules" => array("required" => "", "max_len[500]" => "")
                    ),
                    array(
                        "field" => "ep_news_i18n_content",
                        "label" => "Content",
                        "rules" => array("required" => "")
                    ),
                    array(
                        "field" => "ep_news_i18n_lang",
                        "label" => "Language",
                        "rules" => array("required" => "")
                    )
                );
                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

				$this->load->model('Ep_News_Model', 'ep_news');
                $id_ep_news = (int)$this->uri->segment(4);

                $ep_news= $this->ep_news->get_one_ep_news($id_ep_news);
                if (empty($ep_news)) {
                    jsonResponse('This EP news does not exist.');
                }

                $ep_news_i18n_lang = cleanInput($_POST['ep_news_i18n_lang']);
                $tlang = $this->translations->get_language_by_iso2($ep_news_i18n_lang);
                if(empty($tlang)){
                    jsonResponse('Error: Language does not exist.');
                }

                $translations_data = json_decode($ep_news['translations_data'], true);
                if(array_key_exists($ep_news_i18n_lang, $translations_data)){
                    jsonResponse('Error: Ep news translation for this language already exist.');
                }

                $translations_data[$ep_news_i18n_lang] = array(
                    'lang_name' => $tlang['lang_name'],
                    'abbr_iso2' => $tlang['lang_iso2']
                );

				$this->load->library("Cleanhtml", "clean");
                $ep_news_i18n_title = cleanInput($_POST["ep_news_i18n_title"]);
                $insert = array(
                    "id_ep_news" => $id_ep_news,
                    "ep_news_i18n_lang" => $ep_news_i18n_lang,
                    "ep_news_i18n_content" => $this->clean->sanitizeUserInput($_POST["ep_news_i18n_content"]),
                    "ep_news_i18n_description" => cleanInput($_POST["ep_news_i18n_description"]),
                    "ep_news_i18n_title" => $ep_news_i18n_title,
                    "ep_news_i18n_url" => strForUrl($ep_news_i18n_title)."-".$id_ep_news
                );

                if($this->ep_news->set_ep_news_i18n($insert)){
                    $this->ep_news->update_ep_news($id_ep_news, array("translations_data" => json_encode($translations_data)));
                    jsonResponse("The translation has been successfully added", "success");
                }

                jsonResponse("Error: Cannot add translation now. Please try later.");
            break;
            case "edit_ep_news_i18n":
                $validator_rules = array(
                    array(
                        "field" => "ep_news_i18n_title",
                        "label" => "Title",
                        "rules" => array("required" => "", "max_len[200]" => "")
                    ),
                    array(
                        "field" => "ep_news_i18n_description",
                        "label" => "Description",
                        "rules" => array("required" => "", "max_len[500]" => "")
                    ),
                    array(
                        "field" => "ep_news_i18n_content",
                        "label" => "Content",
                        "rules" => array("required" => "")
                    ),
                    array(
                        "field" => "ep_news_i18n_lang",
                        "label" => "Language",
                        "rules" => array("required" => "")
                    )
                );
                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

				$this->load->model('Ep_News_Model', 'ep_news');
                $id_ep_news = $this->uri->segment(4);
                $ep_news= $this->ep_news->get_one_ep_news($id_ep_news);
                if (empty($ep_news)) {
                    jsonResponse('This EP news does not exist.');
                }

                $ep_news_i18n_lang = cleanInput($_POST['ep_news_i18n_lang']);
                $tlang = $this->translations->get_language_by_iso2($ep_news_i18n_lang);
                if(empty($tlang)){
                    jsonResponse('Error: Language does not exist.');
                }

                $translations_data = json_decode($ep_news['translations_data'], true);
                if(!array_key_exists($ep_news_i18n_lang, $translations_data)){
                    jsonResponse('Error: Ep news translation for this language already exist.');
                }

                $translations_data[$ep_news_i18n_lang] = array(
                    'lang_name' => $tlang['lang_name'],
                    'abbr_iso2' => $tlang['lang_iso2']
                );

				$this->load->library("Cleanhtml", "clean");
                $ep_news_i18n_title = cleanInput($_POST["ep_news_i18n_title"]);
                $update_i18n = array(
                    "id_ep_news" => $id_ep_news,
                    "ep_news_i18n_lang" => $ep_news_i18n_lang,
                    "ep_news_i18n_content" => $this->clean->sanitizeUserInput($_POST["ep_news_i18n_content"]),
                    "ep_news_i18n_description" => cleanInput($_POST["ep_news_i18n_description"]),
                    "ep_news_i18n_title" => $ep_news_i18n_title,
                    "ep_news_i18n_url" => strForUrl($ep_news_i18n_title)."-".$id_ep_news
                );

                $id_ep_news_i18n = intVal($_POST["id_ep_news_i18n"]);
                if($this->ep_news->update_ep_news_i18n($id_ep_news_i18n, $update_i18n)){
                    jsonResponse("The translation has been successfully edited", "success");
                }

                jsonResponse("Error: Cannot add translation now. Please try later.");
            break;
        }
    }
}

