<?php

use App\Filesystem\FilePathGenerator;
use App\Filesystem\MassMediaFilesPathGenerator;
use ExportPortal\Contracts\Filesystem\FilesystemOperator;
use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;
use Intervention\Image\ImageManager;
use League\Flysystem\PathPrefixer;
use League\Flysystem\UnableToDeleteFile;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 */
class Mass_media_Controller extends TinyMVC_Controller {

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

	function index(){
		$uri = $this->uri->uri_to_assoc(4);

		checkURI($uri, array('page', 'channel'));
		checkIsValidPage($uri['page']);

		/**
         * @var Mass_media_Model $mass_media_model
         */
		$mass_media_model = model(Mass_media_Model::class);

		$this->breadcrumbs = array(
			array(
				'link' 	=> __SITE_URL . 'about',
				'title'	=> translate('about_us_nav_about_us')
			),
			array(
				'link' 	=> __SITE_URL . 'about/in_the_news',
				'title'	=> translate('about_us_nav_in_the_news')
			),
			array(
				'link' 	=> __SITE_URL . 'mass_media',
				'title'	=> translate('breadcrumb_about_mass_media')
			)
		);

		$links_map = array(
            'channel' => array(
                'type' => 'uri',
                'deny' => array('channel', 'page'),
            ),
            'page' => array(
				'type' => 'uri',
				'deny' => array('page'),
			),
			'keywords' => array(
                'type' => 'get',
				'deny' => array('keywords', 'page'),
            ),
		);

		$links_tpl = uri()->make_templates($links_map, $uri);
		$links_tpl_without = uri()->make_templates($links_map, $uri, true);

		$meta_params = array();
		$per_page = (int) config('mass_media_per_page', 10);
		$page = (int) ($uri['page'] ?? 1);

		$mass_media_params = array(
			'published'	=> 1,
			'order_by'	=> 'date_news DESC, id_news DESC',
			'limit' 	=> ($page - 1) * $per_page . ',' . $per_page,
			'lang' 		=> __SITE_LANG,
		);

		if (!empty($uri['channel'])) {
			$id_channel = id_from_link($uri['channel']);

			$channel = model(Mass_media_Model::class)->get_one_media($id_channel);
			$channel_slug = strForUrl($channel['title_media']) . '-' . $channel['id_media'];

			if (empty($channel) || $uri['channel'] != $channel_slug) {
				show_404();
			}

			$this->breadcrumbs[] = array(
				'link' 	=> __SITE_URL . 'mass_media/channel/' . $channel_slug,
				'title'	=> $channel['title_media']
			);

			$mass_media_params['id_channel'] = $id_channel;
			$channel_title = $meta_params['[CHANNEL]'] = $channel['title_media'];
		}

		if ($page > 1) {
			$meta_params['[PAGE]'] = $page;
		}

		$keywords = '';
		if (!empty($_GET['keywords'])) {
			$keywords = $_GET['keywords'];
			$mass_media_params['keywords'] = cleanInput(cut_str($keywords));
		}

		$count_news = $mass_media_model->count_news($mass_media_params);

		$paginator_config = array(
            'replace_url'   => true,
			'total_rows'    => $count_news,
			'first_url'     => rtrim('mass_media/' . $links_tpl_without['page'], '/'),
            'base_url'      => 'mass_media/' . $links_tpl['page'],
			'per_page'      => $per_page,
			'suffix'		=> empty($keywords) ? '' : '?' . http_build_query(array('keywords' => $keywords)),
		);

		library('pagination')->initialize($paginator_config);

        $newsList = $mass_media_model->get_news($mass_media_params);

        foreach ($newsList as &$news) {
            $news['imageUrl'] = $this->storage->url(MassMediaFilesPathGenerator::defaultNewsPublicImagePath($news['img_news']));
            $news['logoUrl'] = $this->storage->url(MassMediaFilesPathGenerator::defaultMediaPublicImagePath($news['logo_media']));
        }

		$data = array(
			'apply_channel_link_tpl'	=> $links_tpl['channel'],
			'sidebar_right_content'		=> 'new/in_the_news/sidebar_view',
			'partial_search_params'		=> array(
				'input_text_placeholder' 	=> translate('mass_media_sidebar_search_block_keywords_placeholder'),
				'btn_text_submit' 			=> translate('mass_media_search_block_submit_btn'),
				'keywords' 					=> $keywords,
				'action' 					=> __SITE_URL . rtrim('mass_media/' . $links_tpl_without['keywords'], '/'),
				'title' 					=> translate('mass_media_search_block_title'),
			),
			'header_out_content'		=> 'new/about/in_the_news/header_view',
			'reset_channel_link'		=> __SITE_URL . rtrim('mass_media/' . $links_tpl_without['channel'], '/'),
			'main_content'				=> 'new/in_the_news/index_view',
			'breadcrumbs'				=> $this->breadcrumbs,
			'meta_params'				=> $meta_params,
			'pagination'				=> library('pagination')->create_links(),
			'nav_active'				=> 'in the news',
			'news_list'					=> $newsList,
			'per_page'					=> $per_page,
			'keywords'					=> $keywords,
			'channel'					=> $channel_title ?? null,
			'count'						=> $count_news,
            'page'						=> $page,
            'header_title'              => translate('about_us_in_the_news_press_releases_header_title'),
            'header_img'                => 'press_releases_header.jpg'
		);

		$this->view->assign($data);
		$this->view->display('new/index_template_view');
	}

	function detail(){
		checkURI(uri()->uri_to_assoc(5), array());

		/**
         * @var Mass_media_Model $mass_media_model
         */
		$mass_media_model = model(Mass_media_Model::class);

		$uri_news_slug = uri()->segment(3);
		$id_news = id_from_link($uri_news_slug);
		if (empty($id_news) || empty($news = $mass_media_model->get_one_news($id_news)) || $uri_news_slug != $news['url']) {
			show_404();
		}

		$this->breadcrumbs = array(
			array(
				'link' 	=> __SITE_URL . 'about',
				'title'	=> translate('about_us_nav_about_us')
			),
			array(
				'link' 	=> __SITE_URL . 'about/in_the_news?hash=press_realeases',
				'title'	=> translate('about_us_nav_in_the_news')
			),
			array(
				'link' 	=> __SITE_URL . 'mass_media',
				'title'	=> translate('breadcrumb_about_mass_media')
			),
			array(
				'link' 	=> get_dynamic_url('mass_media/detail/' . $news['url'], __SITE_URL, true),
				'title'	=> truncWords($news['title_news'], 10)
			),
		);

		$news_by_media = $mass_media_model->get_news(array(
			'id_channel'	=> $news['id_media'],
			'published' 	=> 1,
			'limit' 		=> '0,10',
		));

		$news_last_added = $mass_media_model->get_news(array(
			'not_id_news'	=> $id_news,
			'published'		=> 1,
			'order_by' 		=> 'date_news DESC',
			'limit' 		=> '0,8',
			'lang' 			=> true,
		));

        $newsPath = MassMediaFilesPathGenerator::defaultNewsPublicImagePath($news['img_news'] ?: 'no-image.jpg');
        $mediaPath = MassMediaFilesPathGenerator::defaultMediaPublicImagePath($news['logo_media'] ?: 'no-image.jpg');

        foreach ($news_last_added as &$newsOtherItem) {
            $newsOtherItem['imageUrl'] = $this->storage->url($newsPath);
            $newsOtherItem['mediaUrl'] = $this->storage->url($mediaPath);
        }

        $news['imageUrl'] = $this->storage->url($newsPath);
        $news['mediaUrl'] = $this->storage->url($mediaPath);

		$data = array(
			'partial_search_params'	=> array(
				'input_text_placeholder' 	=> translate('mass_media_sidebar_search_block_keywords_placeholder'),
				'btn_text_submit' 			=> translate('mass_media_search_block_submit_btn'),
				'keywords' 					=> '',
				'action' 					=> __SITE_URL . 'mass_media',
				'title' 					=> translate('mass_media_search_block_title'),
			),
			'sidebar_right_content'	=> 'new/in_the_news/details/sidebar_view',
			'footer_out_content'	=> 'new/about/bottom_need_help_view',
			'news_last_added'		=> array_splice($news_last_added, 0, 4),
			'news_by_media'			=> $news_by_media,
			'main_content'			=> 'new/in_the_news/details/index_view',
			'breadcrumbs'			=> $this->breadcrumbs,
			'meta_params'			=> array('[NEWS_NAME]' => $news['title_news']),
			'nav_active'			=> 'in the news',
			'news_other'			=> array_splice($news_last_added, 0, 4),
			'news'					=> $news,
		);

		$this->view->assign($data);
		$this->view->display('new/index_template_view');
	}

	function administration_news(){
		checkAdmin('manage_content');

        views(
            [
                'admin/header_view',
                'admin/mass_media/news_view',
                'admin/footer_view'
            ],
            [
                'title' => 'News',
            ],
        );
	}

	function ajax_news_administration() {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        if (!logged_in()) {
            jsonResponse(translate("systmess_error_should_be_logged"));
        }

        if (!have_right('moderate_content')) {
            jsonResponse(translate("systmess_error_page_permision"));
        }

        $this->load->model('Mass_media_Model', 'mass_media');

		$params = array(
            "limit" => intVal($_POST["iDisplayStart"]).', '.intVal($_POST["iDisplayLength"]),
            'sort_by' => flat_dt_ordering($_POST, [
                'dt_date' => 'date_news',
            ])
        );

        $params['sort_by'] = empty($params['sort_by']) ? ["date_news-desc"] : $params['sort_by'];

        $news_list = $this->mass_media->get_news($params);
        $records_count = $this->mass_media->count_news($params);

		$output = array(
            "sEcho" => intval($_POST['sEcho']),
            "iTotalRecords" => $records_count,
            "iTotalDisplayRecords" => $records_count,
			'aaData' => array()
        );

		if(empty($news_list)) {
			jsonResponse('', 'success', $output);
        }

        foreach ($news_list as $news) {
            if (empty($news['img_news'])) {
                $imagePath = getNoPhoto('other');
            } else {
                $imagePath = $this->storage->url(MassMediaFilesPathGenerator::defaultNewsPublicImagePath($news['img_news']));
            }

            $output['aaData'][] = array(
                'dt_id_news' => $news['id_news'],
                'dt_title' => '<a href="'. $news['link_news'] .'" target="_blank">'. $news['title_news'].'</a>',
                'dt_date' => formatDate($news['date_news'], 'm/d/Y'),
                'dt_type' => $news['type_news'],
                'dt_description' => $news['description_news'],
                'dt_img' => '<img class="mw-150 mh-150" src="' . $imagePath . '" title="'. $news['title_media'].'" alt="' . $news['title_media'] . '" />',
                'dt_country' => '<img width="24" height="24" src="' . getCountryFlag($news['country']) . '" title="' . $news['country'] . '" alt="' . $news['country'] . '"/>',
            	'dt_visible' =>  ($news['published_news'])?'Yes':'No',
                'dt_lang' => $news['lang'],
				'dt_actions' => '<a class="fancyboxValidateModalDT fancybox.ajax ep-icon ep-icon_pencil fs-16 mr-5" data-title="Edit news" href="mass_media/news_popups/edit_news/' . $news['id_news'] . '" title="Edit this news"></a>'
					.'<a class="confirm-dialog ep-icon ep-icon_remove txt-red fs-16 mr-5" data-news="'.$news['id_news'].'" data-callback="newsRemove" data-message="Are you sure you want to delete this news?" href="#" title="Delete"></a>'
            );
        }

        jsonResponse('', 'success', $output);
    }

	public function news_popups() {
        checkIsAjax();
        checkIsLoggedAjaxModal();

        switch (uri()->segment(3)) {
            case 'add_news':
                /** @var Mass_media_Model $massMediaModel */
                $massMediaModel = model(Mass_media_Model::class);

                /** @var Country_Model $countryModel */
                $countryModel = model(Country_Model::class);

                views(
                    'admin/mass_media/form_news_view',
                    [
                        'fileupload_max_file_size'  => config('fileupload_max_file_size'),
                        'upload_folder'             => encriptedFolderName(),
                        'languages'                 => $this->translations->get_languages(),
                        'country'                   => $countryModel->get_countries(),
                        'media'                     => $massMediaModel->get_media(array('order_by' => 'title_media ASC')),
                    ],
                );
                break;
			case 'edit_news':
                /** @var Mass_media_Model $massMediaModel */
                $massMediaModel = model(Mass_media_Model::class);

                if (empty($newsId = uri()->segment(4)) || empty($news = $massMediaModel->get_one_news($newsId))) {
                    messageInModal(translate('systmess_error_invalid_data'));
                }

                /** @var Country_Model $countryModel */
                $countryModel = model(Country_Model::class);

                views(
                    'admin/mass_media/form_news_view',
                    [
                        'fileupload_max_file_size'  => config('fileupload_max_file_size'),
                        'upload_folder'             => encriptedFolderName(),
                        'languages'                 => $this->translations->get_languages(),
                        'country'                   => $countryModel->fetch_port_country(),
                        'media'                     => $massMediaModel->get_media(['order_by' => 'title_media ASC']),
                        'news'                      => $news,
                    ],
                );
            break;
        }
    }

	function ajax_news_operation() {
        if (!isAjaxRequest())
            headerRedirect();

        if (!logged_in())
            jsonResponse(translate("systmess_error_should_be_logged"));

        if (!have_right('moderate_content'))
            jsonResponse(translate("systmess_error_page_permision"));

        $this->load->model('Mass_media_Model', 'mass_media');
        $op = $this->uri->segment(3);

		switch ($op) {
			case 'get_rss':
				$validator = $this->validator;

				$validator_rules = array(
					array(
						'field' => 'url',
						'label' => 'Link from RSS',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'title',
						'label' => 'Title news',
						'rules' => array('required' => '')
					)
				);

				$this->validator->set_rules($validator_rules);

				if (!$validator->validate()) {
					jsonResponse($validator->get_array_errors());
				}

				$rss = cleanInput($_POST['url']);
				$title = cleanInput($_POST['title']);

				if(!empty($rss) && !empty($title)){
					$xml = @simplexml_load_file($rss);
					$namespace = "http://search.yahoo.com/mrss/";

					foreach ($xml->xpath('//item') as $item) {
						if(((string) $item->title) == $title){
							$our_item['description'] = strval($item->description);
							$our_item['title'] =  strval($item->title);
							$our_item['link'] =  strval($item->link);
							$our_item['date'] = formatDate(strval($item->pubDate), "d-m-Y H:i:s");
							$tmp_image = $item->children($namespace)->thumbnail->attributes();
							$image_link = $tmp_image['url'];

							if(!empty($image_link))
								$our_item['img'] = strval($image_link);
							break;
						}
					}
				}

				if(isset($our_item))
					jsonResponse('The news has been found.', 'success', array('news' => $our_item));
				else
					jsonResponse('Error: The news has not been found.');
            break;
            case 'delete_news':
				$id_news = intval($_POST['news']);
				if(empty($id_news))
					jsonResponse('The news doesn\'t exist.');

				$news_info = $this->mass_media->get_one_news($id_news);

				if(empty($news_info))
					jsonResponse('The news doesn\'t exist.');

				if(!empty($news_info['img_news'])){

                    /** @var FilesystemProviderInterface */
                    $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                    $publicDisk = $storageProvider->storage('public.storage');
                    $path = MassMediaFilesPathGenerator::defaultNewsPublicImagePath($news_info['img_news']);

                    try {
                        $publicDisk->delete($path);
                    } catch (\Throwable $th) {
                        jsonResponse(translate('systmess_error_delete_mass_news_image_fail'));
                    }
				}

                if($this->mass_media->delete_news($id_news))
                    jsonResponse('The news was deleted successfully.', 'success');
                else
                    jsonResponse('Error: The news wasn\'t deleted.');
            break;
			case "create_news":
				$validator_rules = array(
					array(
						"field" => "lang",
						"label" => "Lang news",
						"rules" => array("required" => "", "max_len[2]" => "")
					),
					array(
						"field" => "channel",
						"label" => "Channel news",
						"rules" => array("required" => "", "is_natural_no_zero" => "", "max[999]" => "")
					),
					array(
						"field" => "country",
						"label" => "Country news",
						"rules" => array("required" => "", "is_natural_no_zero" => "", "max[999]" => "")
					),
					array(
						"field" => "title",
						"label" => "Title news",
						"rules" => array("required" => "", "max_len[255]" => "")
					),
					array(
						"field" => "type",
						"label" => "Title news",
						"rules" => array("required" => "")
					),
					array(
						"field" => "date",
						"label" => "Date news",
						"rules" => array("required" => "")
					),
					array(
						"field" => "description",
						"label" => "Description news",
						"rules" => array("required" => "", "max_len[1000]" => "")
					)
				);

				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate()) {
					jsonResponse($this->validator->get_array_errors());
				}

                $request = request()->request;

                $title = cleanInput($request->get('title'));
				$insert = [
                    'description_news'  => cleanInput($request->get('description')),
                    'published_news'    => empty($request->get('published')) ? 0 : 1,
					'title_news'        => $title,
					'id_country'        => (int) $request->getInt('country'),
					'type_news'         => cleanInput($request->get('type')),
					'date_news'         => formatDate($request->get('date'), "Y-m-d H:i:s"),
					'id_media'          => $request->getInt('channel'),
                    'lang'              => cleanInput($request->get('lang')),
                ];

                $tempImage = $request->get('image');
                $imageName = pathinfo($tempImage, PATHINFO_BASENAME);
                $imagePath = FilePathGenerator::uploadedFile($imageName);

                /** @var FilesystemProviderInterface */
                $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                $publicDisk = $storageProvider->storage('public.storage');

                switch ($insert['type_news']) {
                    case 'site':
                        $insert['link_news'] = $request->get('link_site');

                        if (!empty($tempImage)){
                            $imageName = pathinfo($tempImage, PATHINFO_BASENAME);
                            try {
                                $publicDisk->write(
                                    MassMediaFilesPathGenerator::defaultNewsPublicImagePath($imageName),
                                    $this->tempStorage->read($imagePath)
                                );
                            } catch (\Throwable $th) {
                                jsonResponse(translate('systmess_error_mass_news_image_save_fail'));
                            }

                            $insert['img_news'] = $imageName;
                        }

                        break;
                    case 'manually':
                        $insert['fulltext_news'] = $request->get('full_description');

                        if (!empty($tempImage)){
                            $imageName = pathinfo($tempImage, PATHINFO_BASENAME);

                            try {
                                $publicDisk->write(
                                    MassMediaFilesPathGenerator::defaultNewsPublicImagePath($imageName),
                                    $this->tempStorage->read($imagePath)
                                );
                            } catch (\Throwable $th) {
                                jsonResponse(translate('systmess_error_mass_news_image_save_fail'));
                            }

                            $insert['img_news'] = $imageName;
                        }

                        break;
                    case 'rss':
                        $insert['link_news'] = $request->get('link');

                        if (!empty($rssImage = $request->get('img_rss'))) {
                            $imageName = time() . '.jpg';
                            $file = file_get_contents($rssImage);
                            file_put_contents($imagePath, $file);

                            (new ImageManager())->make($imagePath)
                                ->resize(
                                    325,
                                    null,
                                    function ($constraint) {
                                        $constraint->aspectRatio();
                                    }
                                )
                                ->save($imagePath);

                            $insert['img_news'] = $imageName;
                        }

                        break;
                }

                /** @var Mass_media_Model $massMediaModel */
                $massMediaModel = model(Mass_media_Model::class);

                if (empty($newsId = $massMediaModel->set_news($insert))) {
                    jsonResponse('The news wasn\'t added.');
                }

				if ('manually' == $insert['type_news']) {
                    $massMediaModel->update_news($newsId, ['url' => strForUrl($title . ' ' . $newsId)]);
                }

                jsonResponse('The news was added successfully.', 'success');

			break;
			case 'update_news':
				$validator_rules = array(
					array(
						"field" => "lang",
						"label" => "Lang news",
						"rules" => array("required" => "", "max_len[2]" => "")
					),
					array(
						"field" => "channel",
						"label" => "Channel news",
						"rules" => array("required" => "", "is_natural_no_zero" => "", "max[999]" => "")
					),
					array(
						"field" => "country",
						"label" => "Country news",
						"rules" => array("required" => "", "is_natural_no_zero" => "", "max[999]" => "")
					),
					array(
						"field" => "title",
						"label" => "Title news",
						"rules" => array("required" => "", "max_len[255]" => "")
					),
					array(
						"field" => "type",
						"label" => "Title news",
						"rules" => array("required" => "")
					),
					array(
						"field" => "date",
						"label" => "Date news",
						"rules" => array("required" => "")
					),
					array(
						"field" => "description",
						"label" => "Description news",
						"rules" => array("required" => "", "max_len[1000]" => "")
					)
				);

				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate()) {
					jsonResponse($this->validator->get_array_errors());
                }

                /** @var Mass_media_Model $massMediaModel */
                $massMediaModel = model(Mass_media_Model::class);

                $request = request()->request;
                if (empty($newsId = $request->getInt('news')) || empty($news = $massMediaModel->get_one_news($newsId))) {
                    jsonResponse('The news doesn\'t exist.');
                }

                $title = cleanInput($request->get('title'));
				$update = [
					'description_news'  => cleanInput($request->get('description')),
					'fulltext_news'     => '',
					'title_news'        => $title,
					'id_country'        => $request->getInt('country'),
					'type_news'         => cleanInput($request->get('type')),
					'date_news'         => formatDate($request->get('date'), 'Y-m-d'),
					'link_news'         => '',
					'id_media'          => $request->getInt('channel'),
                    'lang'              => cleanInput($request->get('lang')),
                    'published_news'    => empty($request->get('published')) ? 0 : 1,
                ];

                $tempImage = $request->get('image');
                $imageName = pathinfo($tempImage, PATHINFO_BASENAME);
                $imagePath = FilePathGenerator::uploadedFile($imageName);

                /** @var FilesystemProviderInterface */
                $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                $publicDisk = $storageProvider->storage('public.storage');

                if ('manually' == $update['type_news']) {
                    $update['url'] = strForUrl($title . ' ' . $newsId);
                    $update['fulltext_news'] = $request->get('full_description');

                    if (!empty($tempImage)){
                        try {
                            $publicDisk->write(
                                MassMediaFilesPathGenerator::defaultNewsPublicImagePath($imageName),
                                $this->tempStorage->read($imagePath)
                            );
                        } catch (\Throwable $th) {
                            jsonResponse(translate('systmess_error_mass_news_image_save_fail'));
                        }

						$update['img_news'] = $imageName;
					}
                } elseif ('site' == $update['type_news']) {
                    $update['link_news'] = $request->get('link_site');

                    if (!empty($tempImage)){
                        $imageName = pathinfo($tempImage, PATHINFO_BASENAME);

                        try {
                            $publicDisk->write(
                                MassMediaFilesPathGenerator::defaultNewsPublicImagePath($imageName),
                                $this->tempStorage->read($imagePath)
                            );
                        } catch (\Throwable $th) {
                            jsonResponse(translate('systmess_error_mass_news_image_save_fail'));
                        }

						$update['img_news'] = $imageName;
					}
                } elseif ('rss' == $update['type_news']) {
                    $update['link_news'] = $request->get('link');

                    if (!empty($rssImage = $request->get('img_rss'))) {
						$imageName = time() . '.jpg';
						$file = file_get_contents($rssImage);
                        file_put_contents($imagePath, $file);

                        (new ImageManager())->make($this->prefixer->prefixPath($imagePath))
                            ->resize(
                                325,
                                null,
                                function ($constraint) {
                                    $constraint->aspectRatio();
                                }
                            )
                            ->save($imagePath);

						$update['img_news'] = $imageName;
					}
                }

				if (!empty($update['img_news']) && !empty($news['img_news'])) {
                    try {
                        $publicDisk->delete(MassMediaFilesPathGenerator::defaultNewsPublicImagePath($news['img_news']));
                    } catch (\Throwable $th) {
                        jsonResponse(translate('systmess_error_delete_mass_news_image_fail'));
                    }
				}

				if (!$massMediaModel->update_news($newsId, $update)) {
                    jsonResponse('The information about the news wasn\'t updated.');
                }

                jsonResponse('The information about the news was updated succefully.', 'success');
			break;
        }
    }

	function ajax_news_upload_photo() {
        checkPermisionAjax('manage_content');

        if (empty($files = $_FILES['files'])) {
			jsonResponse('Please select file to upload.');
        }

        if (!empty($newsId = (int) uri()->segment(4))){
            /** @var Mass_media_Model $massMediaModel */
            $massMediaModel = model(Mass_media_Model::class);

            if (!empty($massMediaModel->get_one_news($newsId)['img_news'])) {
                jsonResponse('This news already has a photo. Before uploading a new photo, please remove the old photo first.');
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

        $imageConfigModule = 'press_releases.main';
        $imageName = sprintf('%s.%s', \bin2hex(\random_bytes(16)), 'jpg');
        $imageTempPath = FilePathGenerator::uploadedFile($imageName);

        $tempDirectory = dirname($imageTempPath);

        $fullPath = $this->tempPrefixer->prefixPath($tempDirectory);
        $this->tempStorage->createDirectory($tempDirectory);

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
                    'resize'    => config("img.{$imageConfigModule}.resize"),
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

	function ajax_news_delete_photo() {
        if (!isAjaxRequest())
            headerRedirect();

        if (!logged_in())
            jsonResponse(translate("systmess_error_should_be_logged"));

        if (empty($name = request()->request->get('file')))
            jsonResponse('Error: File name is not correct.');

        /** @var FilesystemProviderInterface */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $tempDisk = $storageProvider->storage('temp.storage');
        $name = $_POST["file"];
        $path = FilePathGenerator::uploadedFile($name);

        if (!$tempDisk->fileExists($path))
            jsonResponse('Error: Upload path is not correct.');
        try {
            $tempDisk->delete($path);
        } catch (\Throwable $th) {
            jsonResponse(translate('systmess_error_delete_mass_news_image_fail'));
        }

		jsonResponse('','success');
    }

	function ajax_news_delete_db_photo() {
        if (!isAjaxRequest()) {
            headerRedirect();
        }
        if (!logged_in()) {
            jsonResponse(translate("systmess_error_should_be_logged"));
        }

		if (!have_right('manage_content'))
            jsonResponse(translate("systmess_error_rights_perform_this_action"));

		if (empty($_POST['file']))
            jsonResponse('Error: File name is not correct.');

        $id_news = intVal($_POST['file']);
		$this->load->model('Mass_media_Model', 'mass_media');

        $news = $this->mass_media->get_one_news($id_news);

        /** @var FilesystemProviderInterface */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $publicDisk = $storageProvider->storage('public.storage');
        $path = MassMediaFilesPathGenerator::defaultNewsPublicImagePath($news['img_news']);

        try {
            $publicDisk->delete($path);
            $this->mass_media->update_news($id_news, array('img_news' => ''));
        } catch (UnableToDeleteFile $e) {
            jsonResponse(throwableToMessage($e, translate('systmess_error_delete_mass_news_image_fail')));
        }

        jsonResponse('News image was deleted.', 'success');
    }

	function administration_media(){
		checkAdmin('manage_content');

        views(
            [
                'admin/header_view',
                'admin/mass_media/media_view',
                'admin/footer_view'
            ],
            [
                'title' => 'Media'
            ],
        );
	}

	function ajax_media_administration() {
        if (!isAjaxRequest())
            headerRedirect();

        if (!logged_in())
            jsonResponse(translate("systmess_error_should_be_logged"));

        if (!have_right('moderate_content'))
            jsonResponse(translate("systmess_error_page_permision"));

        $this->load->model('Mass_media_Model', 'mass_media');
        $media_list = $this->mass_media->get_media();
		$records_count = $this->mass_media->count_media();

		$output = array(
            "sEcho" => intval($_POST['sEcho']),
            "iTotalRecords" => $records_count,
            "iTotalDisplayRecords" => $records_count,
			'aaData' => array()
        );

        if (empty($media_list)) {
            jsonResponse('', 'success', $output);
        }

        foreach ($media_list as $media) {
            if (empty($media['logo_media'])) {
                $imagePath = getNoPhoto('other');
            } else {
                $imagePath = $this->storage->url(MassMediaFilesPathGenerator::defaultMediaPublicImagePath($media['logo_media']));
            }
            $output['aaData'][] = [
                'dt_id_media' => $media['id_media'],
                'dt_title'    => '<a href="' . $media['website_media'] . '" target="_blank">' . $media['title_media'] . '(<span class="upper-case">' . $media['type_media'] . '</span>)</a>',
                'dt_logo'     => '<img class="mw-40 mh-40" src="' . $imagePath . '" title="' . $media['title_media'] . '" alt="' . $media['title_media'] . '"/>',
                'dt_actions' => '<a class="fancyboxValidateModalDT fancybox.ajax ep-icon ep-icon_pencil" data-title="Edit media" href="mass_media/media_popups/edit_media/' . $media['id_media'] . '" title="Edit this media"></a>'
                    . '<a class="confirm-dialog ep-icon ep-icon_remove txt-red" data-media="' . $media['id_media'] . '" data-callback="mediaRemove" data-message="Are you sure you want to delete this media?" href="#" title="Delete"></a>',
            ];
        }

        jsonResponse('', 'success', $output);
    }

	public function media_popups() {
        checkIsAjax();

        switch (uri()->segment(3)) {
            case 'add_media':
                checkIsLoggedAjaxModal();

                views(
                    'admin/mass_media/form_media_view',
                    [
                        'fileupload_max_file_size'  => config('fileupload_max_file_size'),
                        'upload_folder'             => encriptedFolderName(),
                    ],
                );

            break;
			case 'edit_media':
                checkIsLoggedAjaxModal();

                /** @var Mass_media_Model $massMediaModel */
                $massMediaModel = model(Mass_media_Model::class);

                if (empty($massMediaId = uri()->segment(4)) || empty($massMedia = $massMediaModel->get_one_media($massMediaId))) {
                    messageInModal(translate('systmess_error_invalid_data'));
                }

                $massMedia['imageUrl'] = $this->storage->url(MassMediaFilesPathGenerator::defaultMediaPublicImagePath($massMedia['logo_media']));

                views(
                    'admin/mass_media/form_media_view',
                    [
                        'upload_folder' => encriptedFolderName(),
                        'fileupload_max_file_size'  => config('fileupload_max_file_size'),
                        'media' => $massMedia,
                    ],
                );

            break;
        }
    }

	function ajax_media_operation() {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        if (!logged_in()) {
            jsonResponse(translate("systmess_error_should_be_logged"));
        }

        if (!have_right('moderate_content')) {
            jsonResponse(translate("systmess_error_page_permision"));
        }

        $this->load->model('Mass_media_Model', 'mass_media');
        $op = $this->uri->segment(3);

		switch ($op) {
            case 'delete_media':
				$id_media = intval($_POST['media']);
				if(empty($id_media))
					jsonResponse('The media doesn\'t exist.');

				$media_info = $this->mass_media->get_one_media($id_media);

				if(empty($media_info))
					jsonResponse('The media doesn\'t exist.');

				if(!empty($media_info['logo_media'])){
                    /** @var FilesystemProviderInterface */
                    $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                    $publicDisk = $storageProvider->storage('public.storage');

                    try {
                        $publicDisk->delete(MassMediaFilesPathGenerator::defaultMediaPublicImagePath($media_info['logo_media']));
                    } catch (\Throwable $th) {
                        jsonResponse(translate('systmess_error_mass_media_image_delete_fail'));
                    }
				}

                if($this->mass_media->delete_media($id_media))
                    jsonResponse('The media was deleted successfully.', 'success');
                else
                    jsonResponse('Error: The media wasn\'t deleted.');
            break;
			case 'create_media':
				$validator = $this->validator;

				$validator_rules = array(
					array(
						'field' => 'title',
						'label' => 'Name media',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'type',
						'label' => 'Name media',
						'rules' => array('required' => '')
					)
				);

				$this->validator->set_rules($validator_rules);
				if (!$validator->validate()) {
					jsonResponse($validator->get_array_errors());
				}

                $request = request()->request;

                if (!empty($tempImage = $request->get('image'))) {
                    $imageName = pathinfo($tempImage, PATHINFO_BASENAME);
                    $imagePath = FilePathGenerator::uploadedFile($imageName);

                    /** @var FilesystemProviderInterface */
                    $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                    $publicDisk = $storageProvider->storage('public.storage');

                    try {
                        $publicDisk->write(
                            MassMediaFilesPathGenerator::defaultMediaPublicImagePath($imageName),
                            $this->tempStorage->read($imagePath)
                        );
                    } catch (\Throwable $th) {
                        jsonResponse(translate('systmess_error_mass_media_image_save_fail'));
                    }
                }

                /** @var Mass_media_Model $massMediaModel */
                $massMediaModel = model(Mass_media_Model::class);

				if (!$massMediaModel->set_media([
                    'website_media' => cleanInput($request->get('website')),
					'title_media'   => cleanInput($request->get('title')),
					'type_media'    => cleanInput($request->get('type')),
                    'logo_media'    => $imageName,
                ])) {
                    jsonResponse('The media wasn\'t added.');
                }

                jsonResponse('The media was added successfully.', 'success');

			break;
			case 'update_media':
				$validator = $this->validator;

				$validator_rules = array(
					array(
						'field' => 'title',
						'label' => 'Name media',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'type',
						'label' => 'Name media',
						'rules' => array('required' => '')
					)
				);

				$this->validator->set_rules($validator_rules);
				if (!$validator->validate()) {
					jsonResponse($validator->get_array_errors());
                }

                $request = request()->request;

                /** @var Mass_media_Model $massMediaModel */
                $massMediaModel = model(Mass_media_Model::class);

				if (empty($massMediaId = $request->getInt('media')) || !$massMediaModel->exist_media($massMediaId)) {
					jsonResponse('The media doesn\'t exist.');
                }

                $update = [
					'website_media' => cleanInput($request->get('website')),
                    'title_media'   => cleanInput($request->get('title')),
					'type_media'    => cleanInput($request->get('type')),
                ];

                if (!empty($tempImage = $request->get('image'))) {
                    $imageName = pathinfo($tempImage, PATHINFO_BASENAME);
                    $imagePath = FilePathGenerator::uploadedFile($imageName);

                    /** @var FilesystemProviderInterface */
                    $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                    $publicDisk = $storageProvider->storage('public.storage');

                    try {
                        $publicDisk->write(
                            MassMediaFilesPathGenerator::defaultMediaPublicImagePath($imageName),
                            $this->tempStorage->read($imagePath)
                        );
                    } catch (\Throwable $th) {
                        jsonResponse(translate('systmess_error_mass_media_image_save_fail'));
                    }
                    $update['logo_media'] = $imageName;
                }

				if (!$massMediaModel->update_media($massMediaId, $update)) {
                    jsonResponse('The information about the media wasn\'t updated.');
                }

                jsonResponse('The information about the media was updated succefully.', 'success');

			break;
        }
    }

	function ajax_media_upload_photo() {
        checkPermisionAjax('manage_content');

        if (empty($files = $_FILES['files'])) {
			jsonResponse('Please select file to upload.');
        }

        if (!empty($massMediaId = uri()->segment(4))) {
            /** @var Mass_media_Model $massMediaModel */
            $massMediaModel = model(Mass_media_Model::class);

            if (!empty($massMediaModel->get_one_media($massMediaId)['logo_media'])) {
                jsonResponse('This media already has a photo. Before uploading a new photo, please remove the old photo first.');
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

        $imageConfigModule = 'mass_media.logo';
        $imageName = sprintf('%s.%s', \bin2hex(\random_bytes(16)), 'jpg');
        $imageTempPath = FilePathGenerator::uploadedFile($imageName);

        $tempDirectory = dirname($imageTempPath);

        $fullPath = $this->tempPrefixer->prefixPath($tempDirectory);
        $this->tempStorage->createDirectory($tempDirectory);

		// Count number of files in this folder, to prevent upload more files than photo limit
		$fi = new FilesystemIterator($fullPath, FilesystemIterator::SKIP_DOTS);

		if (iterator_count($fi) >= 1 || count($files['name']) > 1){
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
                'destination'       => $fullPath,
                'use_original_name' => true,
                'rules'             => config("img.{$imageConfigModule }.rules"),
                'handlers'          => [
                    'resize'    => config("img.{$imageConfigModule}.resize"),
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
                        'path'  => $this->tempStorage->url($imageTempPath),
                        'name'  => $images[0]['new_name'],
                    ],
                ],
            ],
        );
    }

	function ajax_media_delete_photo() {
        if (!isAjaxRequest())
            headerRedirect();

        if (!logged_in())
            jsonResponse(translate("systmess_error_should_be_logged"));

        if (empty($name = request()->request->get('file')))
            jsonResponse('Error: File name is not correct.');

        /** @var FilesystemProviderInterface */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $tempDisk = $storageProvider->storage('temp.storage');
        $path = FilePathGenerator::uploadedFile($name);

        if (!$tempDisk->fileExists($path))
            jsonResponse('Error: Upload path is not correct.');
        try {
            $tempDisk->delete($path);
        } catch (\Throwable $th) {
            jsonResponse(translate('systmess_error_mass_media_image_delete_fail'));
        }

		jsonResponse('','success');
    }

	function ajax_media_delete_db_photo() {
        if (!isAjaxRequest()) {
            headerRedirect();
        }
        if (!logged_in()) {
            jsonResponse(translate("systmess_error_should_be_logged"));
        }

		if (!have_right('manage_content'))
            jsonResponse(translate("systmess_error_rights_perform_this_action"));

		if (empty($_POST['file']))
            jsonResponse('Error: File name is not correct.');

        $id_media = intVal($_POST['file']);
		$this->load->model('Mass_media_Model', 'mass_media');

        $media = $this->mass_media->get_one_media($id_media);

        try {
            $this->storage->delete(MassMediaFilesPathGenerator::defaultMediaPublicImagePath($media['logo_media']));
        } catch (\Throwable $th) {
            jsonResponse(translate('systmess_error_mass_media_image_delete_fail'));
        }

        $this->mass_media->update_media($id_media, array('logo_media' => ''));
        jsonResponse('News image was deleted.', 'success');
    }
}
