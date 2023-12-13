<?php

/**
 * FAQ controller
 *
 * @property \Cleanhtml                       $clean
 * @property \Faq_Model                       $faq
 * @property \TinyMVC_Load                    $load
 * @property \TinyMVC_View                    $view
 * @property \TinyMVC_Library_URI             $uri
 * @property \TinyMVC_Library_Session         $session
 * @property \TinyMVC_Library_Cookies         $cookies
 * @property \TinyMVC_Library_Upload          $upload
 * @property \TinyMVC_Library_validator       $validator
 * @property \Translations_Model              $translations
 * @property \Faq_Model                       $faq
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 */
class Faq_Controller extends TinyMVC_Controller
{
    const IMAGES_INVALID_DOMAIN = 15002;

    private function _load_main()
    {
        $this->load->model('Faq_Model', 'faq');
        $this->load->model('Elasticsearch_Faq_Model', 'elasticfaq');
    }

    public function index()
    {
        headerRedirect('/faq/all', 301);
    }

    public function all()
    {
        $this->_load_main();

        $params = array();
        $data['faq_list'] = array();
        $data['count_faq_list'] = 0;

        $uri_params = $this->uri->uri_to_assoc(4);
        $data['keywords'] = cleanInput(cut_str($_GET['keywords']));

        $links_map = array(
            'page' => array(
                'type' => 'uri',
            ),
            'keywords' => array(
                'type' => 'get',
                'deny' => array('page', 'keywords')
            ),
            'tag' => array(
                'type' => 'uri',
                'deny' => array('page', 'tag')
            ),
        );

        $links_tpl = $this->uri->make_templates($links_map, $uri_params, false);
        $data['page'] = (int) arrayGet($uri_params, 'page', 1);
        $data['page'] = $data['page'] <= 0 ? 1 : $data['page'];
        ($data['page'] <= 1) ?: $data['meta_params']['[PAGE]'] = $data['page'];
        $data['tag_link'] = $links_tpl['tag'];
        $params['limit'] = $data['per_p'] = 10;
        $params['start'] = ($data['page'] * $data['per_p']) - $data['per_p'];

        if(!empty($data['keywords'])){
            if(mb_strlen(decodeCleanInput($data['keywords'])) < config('help_search_min_keyword_length')){
                $this->session->setMessages('Error: The search keywords must have at least ' . config('help_search_min_keyword_length') . ' characters.', 'errors');
                headerRedirect(get_dynamic_url('faq'));
            }

            $data['meta_params']['[KEYWORDS]'] = $params['keywords'] = $data['keywords'];
            model("Search_Log_Model")->log($params['keywords']);

            $links_tpl_without = $this->uri->make_templates($links_map, $uri_params, true);
            $data['search_params'][] = array(
                'link' => get_dynamic_url('faq/all/' . $links_tpl_without['keywords']),
                'title' => $data['keywords'],
                'param' => 'Keywords',
            );
        }

        /** @var Tags_Faq_Model $faqRepository */
        $faqRepository = model(Tags_Faq_Model::class);
        $faq_tags_list = $faqRepository->findAll();
        usort($faq_tags_list, function($value_1, $value_2){
            if ($value_1['top_priority'] == $value_2['top_priority']) {
                return 0;
            }
            return ($value_1['top_priority'] < $value_2['top_priority']) ? -1 : 1;
        });

        $data['faq_other_tags_list'] = $faq_tags_list = array_column($faq_tags_list, null, 'id_tag');

        if (isset($uri_params['tag'])) {
            $params['id_tag'] = (int) id_from_link($uri_params['tag']);
            $title = $data['meta_params']['[TAG]'] = $faq_tags_list[$params['id_tag']]['name'] ?? null;

            $links_tpl_without = $this->uri->make_templates($links_map, $uri_params, true);

            $data['search_params'][] = array(
                'link' => get_dynamic_url('faq/all/' . $links_tpl_without['tag']),
                'title' => $title,
                'param' => 'Tag',
            );
        }

        if ($data['page'] == 1 && empty($data['search_params'])) {
            $data['faq_tags_list'] = array_column(
                array_filter(
                    array_slice($faq_tags_list, 0, config('max_count_faq_top_tags')),
                    function ($tag) {
                        return 0 != $tag['top_priority'];
                    }
                ),
                null,
                'id_tag'
            );

            $data['faq_other_tags_list'] = array_column(
                array_slice($faq_tags_list, config('max_count_faq_top_tags')),
                null,
                'id_tag'
            );
        }

        $search_params_links_tpl = $this->uri->make_templates(
            array(
                'page' => array(
                    'deny' => array('page')
                )
            ),
            $uri_params,
            true
        );

        $this->elasticfaq->get_faq_list($params);
        $data['faq_list'] = $this->elasticfaq->faq_records;
        $data['count'] = $data['count_faq_list'] = $this->elasticfaq->faq_count;
        $data['faq_tags_counters'] = $this->elasticfaq->aggregates['tags'];
        $data['faq_tags_attached'] = arrayByKey(
            model('faq_tags_relation')->get_list(),
            'id_faq',
            true
        );

        $this->breadcrumbs[]= array(
            'link' 	=> __SITE_URL.'help',
            'title'	=> translate('breadcrumb_help')
        );

		$this->breadcrumbs[]= array(
			'link' 	=> __SITE_URL.'faq',
			'title'	=> translate('help_nav_header_faq')
		);

        $paginator_config = array(
			'base_url'      => 'faq/all/' . $links_tpl['page'],
			'total_rows'    => $data['count'],
			'per_page'      => $data['per_p'],
            'replace_url'   => true,
            'first_url'     => get_dynamic_url($search_params_links_tpl['page'], __SITE_URL . 'faq/all'),
            'cur_page'		=> $data['page']
		);

		$this->load->library('Pagination', 'pagination');
		$this->pagination->initialize($paginator_config);
        $data['pagination'] = $this->pagination->create_links();

		$data['breadcrumbs'] = $this->breadcrumbs;
        $data['current_page'] = "faq";

        if(empty($params['keywords'])){
            $data['header_out_content'] = 'new/faq/header_view';
        }else{
            $data['header_out_content'] = 'new/faq/header_min_view';
        }

        $data['sidebar_right_content'] = 'new/faq/sidebar_view';
        $data['main_content'] = 'new/faq/index_view';
        $data['footer_out_content'] = 'new/about/bottom_become_partner_view';
        $data['googleAnalyticsEvents'] = true;
        $this->view->assign($data);
        $this->view->display('new/index_template_view');
    }

    public function administration()
    {
		checkAdmin('moderate_content,manage_translations');

		$data['title'] = 'Moderate FAQ';
        $data['languages'] = $this->translations->get_allowed_languages(array('skip' => array('en')));

		$this->view->assign($data);
		$this->view->display('admin/header_view');
		$this->view->display('admin/faq/index_view');
		$this->view->display('admin/footer_view');
	}

    public function tags_administration()
    {
        checkAdmin('moderate_content,manage_translations');

        $data['title'] = 'Moderate FAQ';
        $data['languages'] = $this->translations->get_allowed_languages(array(
            'skip' => array('en')
        ));

        $this->view->assign($data);
        $this->view->display('admin/header_view');
        $this->view->display('admin/faq/tags_view');
        $this->view->display('admin/footer_view');
    }

    public function popup_forms()
    {
        if (!isAjaxRequest()){
			headerRedirect();
        }

		if (!logged_in()){
			messageInModal(translate("systmess_error_should_be_logged"));
		}

		$this->load->model('Faq_Model', 'faq');

		$op = $this->uri->segment(3);

		switch ($op) {
            case 'create_faq':
                checkAdminAjaxModal('moderate_content');

                /** @var Tags_Faq_Model $tagsFaqModel */
                $tagsFaqModel = model(Tags_Faq_Model::class);

                views(
                    [
                        'admin/faq/popup_faq_form_view'
                    ],
                    [
                        'uploadFolder'  => encriptedFolderName(),
                        'faq_tags'      => $tagsFaqModel->findAll([
                            'order' => [
                                'name' => 'asc',
                            ]
                        ]),
                    ],
                );
			break;
            case 'create_faq_tag':
                checkAdminAjaxModal('moderate_content');

				$this->view->display('admin/faq/popup_faq_tag_form_view');
			break;
            case 'add_faq_i18n':
                checkAdminAjaxModal('moderate_content,manage_translations');

                $faq_id = (int) $this->uri->segment(4);
                $data['faq'] = $this->faq->get_faq($faq_id);
                if(empty($data['faq'])) {
                    messageInModal('The FAQ does not exist.');
                }

                $data['languages'] = $this->translations->get_allowed_languages(array(
                    'skip' => array('en')
                ));
				if(empty($data['languages'])){
					messageInModal('There are no languages available.');
                }

                $this->view->display('admin/faq/add_method_i18n_form_view', $data);
			break;
            case 'edit_faq':
                checkAdminAjaxModal('moderate_content');

                /** @var Faq_Model $faqModel */
                $faqModel = model(Faq_Model::class);

                if (
                    empty($faqId = (int) uri()->segment(4))
                    || empty($faq = $faqModel->get_faq($faqId))
                ) {
                    messageInModal(translate('systmess_error_invalid_data'));
                }

                /** @var Tags_Faq_Model $tagsFaqModel */
                $tagsFaqModel = model(Tags_Faq_Model::class);

                /** @var Tags_Faq_Relation_Model $tagsFaqRelationModel */
                $tagsFaqRelationModel = model(Tags_Faq_Relation_Model::class);

                views(
                    [
                        'admin/faq/popup_faq_form_view'
                    ],
                    [
                        'attached_faq_tags' => array_column(
                            $tagsFaqRelationModel->findAllBy([
                                'conditions'    => [
                                    'faqId' => $faqId,
                                ],
                            ]),
                            'id_tag'
                        ),
                        'uploadFolder'      => encriptedFolderName(),
                        'faq_info'          => $faq,
                        'faq_tags'          => $tagsFaqModel->findAll([
                            'order' => ['name' => 'asc']
                        ]),
                    ]
                );
			break;
            case 'edit_faq_tag':
                checkAdminAjaxModal('moderate_content');

				$id_tag = (int) $this->uri->segment(4);
				$data['faq_tag'] = model('faq_tags')->get_one($id_tag);

				$this->view->assign($data);
				$this->view->display('admin/faq/popup_faq_tag_form_view');
			break;
            case 'edit_faq_i18n':
                checkAdminAjaxModal('moderate_content,manage_translations');

                $faq_id = (int) $this->uri->segment(4);
                $lang_code = cleanInput($this->uri->segment(5));
                $data['language'] = $this->translations->get_language_by_iso2($lang_code);
                if(empty($data['language'])){
                    messageInModal('The language does not exist.');
                }

                if($this->session->group_lang_restriction && !in_array($data['language']['id_lang'], $this->session->group_lang_restriction_list)){
					messageInModal('You are not privileged to translate in this language.');
				}

                $params = array(
                    'columns'    => array(
                        'I18N.id_faq_i18n as id',
                        'I18N.question',
                        'I18N.answer',
                        'I18N.lang_faq as lang_code',
                        'F.question as original_question',
                        'F.answer as original_answer'
                    ),
                    'conditions' => array('faq' => $faq_id, 'language' => $lang_code),
                    'with'       => array('faq' => true)
                );
                $data['faq'] = $this->faq->find_faq_i18n($params);
                if(empty($data['faq'])) {
                    messageInModal('The FAQ translation does not exist.');
                }

				$this->view->display('admin/faq/edit_method_i18n_form_view', $data);
			break;
		}
	}

    public function ajax_faq_operation()
    {
		if (!isAjaxRequest()){
			headerRedirect();
		}

		if (!logged_in()){
			jsonResponse(translate("systmess_error_should_be_logged"));
		}
        $this->_load_main();

		switch (uri()->segment(3)) {
            case 'add_faq':
                checkPermisionAjax('moderate_content');

                $request = request()->request;

				$this->validator->set_rules(
                    [
                        [
                            'field' => 'question',
                            'label' => 'Question',
                            'rules' => [
                                'required'      => '',
                                'max_len[250]'  => '',
                            ],
                        ],
                        [
                            'field' => 'answer',
                            'label' => 'Answer',
                            'rules' => [
                                'required'  => ''
                            ],
                        ],
                    ],
                );

				if (!$this->validator->validate()) {
					jsonResponse($this->validator->get_array_errors());
                }

                if (!empty($faqTags = (array) $request->get('faq_tags'))) {
                    $tagsLimit = (int) config('faq_tags_max_count', 3);

                    if (count($faqTags) > $tagsLimit) {
                        jsonResponse("The maximum number of tags is {$tagsLimit}");
                    }

                    /** @var Tags_Faq_Model $tagsFaqModel */
                    $tagsFaqModel = model(Tags_Faq_Model::class);

                    $validTags = $tagsFaqModel->findAllBy([
                        'conditions' => [
                            'tagsIds'   => $faqTags,
                        ],
                        'columns'   => [
                            "{$tagsFaqModel->getTable()}.`id_tag`",
                        ],
                    ]);

                    if (count($validTags) != count($faqTags)) {
                        jsonResponse("Invalid tags detected");
                    }

                    $validTagsIds = array_column($validTags, 'id_tag');
                }

                if (empty($uploadFolder = checkEncriptedFolder($request->get('encripted_folder')))) {
                    jsonResponse(translate('invalid_encrypted_folder_name'));
                }

                /** @var TinyMVC_Library_Cleanhtml $cleanHtmlLibrary */
                $cleanHtmlLibrary = library(TinyMVC_Library_Cleanhtml::class);

                $cleanHtmlLibrary->setStyle('text-decoration');
                $cleanHtmlLibrary->setAttribute('style,href,title,target,rel,src,alt,width,height');
                $cleanHtmlLibrary->addAdditionalTags('<img><p><span><strong><em><b><i><u><a><br><ol><ul><li>');

				$faq = [
                    'question'          => $request->get('question'),
					'answer'            => $cleanHtmlLibrary->sanitize($request->get('answer')),
                    'translations_data' => json_encode([
                        'en' => [
                            'updated_at'    => date('Y-m-d H:i:s'),
                            'lang_name'     => 'English',
                            'abbr_iso2'     => 'en',
                        ],
                    ]),
                ];

                /** @var Faq_Model $faqModel */
                $faqModel = model(Faq_Model::class);

                if (empty($faqId = $faqModel->set_faq($faq))) {
                    jsonResponse('This question wasn\'t added. Please try again later.');
                }

                //region Text & image processing
                $inlineImageConfig = 'faq.inline';
                $update = $postImages = [];

                try {
                    $postProcessedImages = $this->processContentImages($faq['answer'], $faqId);

                    if (!empty($postProcessedImages['paths'])) {
                        $postImages = array_flip(array_flip($postProcessedImages['paths']));
                    }

                    $update['answer'] = $cleanHtmlLibrary->sanitize($this->changeContentPaths(
                        $request->get('answer'),
                        $faqId,
                        $postImages,
                        $uploadFolder,
                        $inlineImageConfig,
                    ));
                } catch (\Exception $exception) {
                    switch ($exception->getCode()) {
                        case self::IMAGES_INVALID_DOMAIN:
                            $message = 'Image links cannot have external link.';

                            break;
                        default:
                            $message = jsonResponse(translate('systmess_internal_server_error'));

                            break;
                    }

                    $faqModel->delete_faq($faqId);
                    jsonResponse($message);
                }

                //endregion Text & image processing
                $imagesToOptimization = [];
                //region Inline images
                if (!empty($postImages)) {
                    $inlineImagesPath = getImgPath($inlineImageConfig, ['{FAQ_ID}' => $faqId]);
                    create_dir($inlineImagesPath);

                    foreach ($postImages as $inlineImage) {
                        $inlineImage = ltrim($inlineImage, '/');
                        if (!file_exists($inlineImage)) {
                            continue;
                        }

                        $inlineImageName = pathinfo($inlineImage, PATHINFO_BASENAME);
                        if (!rename($inlineImage, $inlineImagesPath . $inlineImageName)) {
                            remove_dir($inlineImagesPath);
                            $faqModel->delete_faq($faqId);

                            jsonResponse('An error occurred when copying a temporary image to a public folder.');
                        }

                        $imagesToOptimization[] = [
                            'file_path'	=> getcwd() . DS . $inlineImagesPath . $inlineImageName,
                            'type'		=> 'faq_inline_image',
                            'context'	=> ['id_faq' => $faqId],
                        ];
                    }
                }

                $update['inline_images'] = json_encode(array_values($this->get_images_stats($this->getImagesFromText($update['answer']))), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                //endregion Inline images

                if (!$faqModel->update_faq($faqId, $update)) {
                    remove_dir($inlineImagesPath);
                    $faqModel->delete_faq($faqId);

                    jsonResponse(translate('systmess_internal_server_error'));
                }

                if (!empty($validTagsIds)) {
                    $newTagsRelations = [];
                    foreach ($validTagsIds as $tagId) {
                        $newTagsRelations[] = [
                            'id_faq'    => $faqId,
                            'id_tag'    => $tagId,
                        ];
                    }

                    /** @var Tags_Faq_Relation_Model $tagsFaqRelationModel */
                    $tagsFaqRelationModel = model(Tags_Faq_Relation_Model::class);

                    $tagsFaqRelationModel->insertMany($newTagsRelations);
                }

                /** @var Elasticsearch_Faq_Model $elasticsearchFaqModel */
                $elasticsearchFaqModel = model(Elasticsearch_Faq_Model::class);

                $elasticsearchFaqModel->sync($faqId);

                if (!empty($imagesToOptimization)) {
                    /** @var Image_optimization_Model $imageOptimizationModel */
                    $imageOptimizationModel = model(Image_optimization_Model::class);

                    $imageOptimizationModel->insertMany($imagesToOptimization);
                }

                jsonResponse('Question has been successfully added.', 'success');

			break;
            case 'add_faq_tag':
                checkAdminAjax('moderate_content');

                $validator = $this->validator;
                $this->validator->set_rules(array(
                    array(
                        'field' => 'tag_name',
                        'label' => 'Tag name',
                        'rules' => array('required' => '', 'min_len[3]' => '', 'max_len[50]' => '')
                    ),
                    array(
                        'field' => 'top_priority',
                        'label' => 'Top priority',
                        'rules' => array('required' => '', 'integer' => '', 'min[1]' => '', 'max[100]' => '')
                    ),
				));

				if (!$validator->validate()) {
					jsonResponse($this->validator->get_array_errors());
                }

                $tag_name = cleanInput($_POST['tag_name']);
				$data_add = array(
                    'name' => $tag_name,
                    'top_priority' => (int) $_POST['top_priority']
				);

				if ($id_tag = model('faq_tags')->add($data_add)) {
                    model('faq_tags')->change($id_tag, array(
                        'slug' => strForURL($tag_name) . "-{$id_tag}"
                    ));
					jsonResponse('Tag has been successfully added.', 'success');
                } else {
					jsonResponse('Error: This tag wasn\'t added. Please try again later.');
                }
			break;
            case 'add_faq_i18n':
                checkAdminAjax('moderate_content');

				$validator_rules = array(
					array(
						'field' => 'id',
						'label' => 'FAQ info',
						'rules' => array('required' => '', 'integer' => '')
					),
					array(
						'field' => 'language',
						'label' => 'Language',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'question',
						'label' => 'Question',
						'rules' => array('required' => '', 'max_len[250]' => '')
					),
					array(
						'field' => 'answer',
						'label' => 'Answer',
						'rules' => array('required' => '')
					)
				);

				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate()) {
					jsonResponse($this->validator->get_array_errors());
                }

                $faq_id = (int) $_POST['id'];
                if(
                    empty($faq_id) ||
                    empty($faq = $this->faq->get_faq($faq_id))
                ) {
                    jsonResponse("FAQ record with this ID is not found on this server");
                }

                $lang_id = cleanInput($_POST['language']);
                if(
                    empty($lang_id) ||
                    empty($language = $this->translations->get_language($lang_id))
                ) {
                    jsonResponse("The language with such code is not found on this server");
                }

                if(
                    $this->session->group_lang_restriction &&
                    !in_array($lang_id, $this->session->group_lang_restriction_list)
                ) {
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));
                }

                $lang_code = $language['lang_iso2'];
                if($this->faq->has_faq_i18n($faq_id, $lang_code)) {
                    jsonResponse("FAQ record already has a translation in this language");
                }

                $translations_data = json_decode($faq['translations_data'], true);
				$translations_data[$lang_code] = array(
					'lang_name'  => $language['lang_name'],
                    'abbr_iso2'  => $language['lang_iso2'],
                    'updated_at' => date('Y-m-d H:i:s')
				);

				$this->load->library('Cleanhtml', 'clean');
                $this->clean->setStyle('text-decoration');
                $this->clean->setAttribute('style,href,title,target,rel');
                $this->clean->addAdditionalTags('<p><span><strong><em><b><i><u><a><br><ol><ul><li>');

				$insert = array(
					'id_faq'     => $faq_id,
					'question'   => cleanInput($_POST['question']),
					'answer'     => $this->clean->sanitize($_POST['answer']),
                    'lang_faq'   => $lang_code,
                    'updated_at' => date('Y-m-d H:i:s')
				);

				if($this->faq->set_faq_i18n($insert)){
                    $this->elasticfaq->sync($insert['id_faq']);
					$this->faq->update_faq($faq_id, array('translations_data' => json_encode($translations_data)));

                    jsonResponse('The translation has been successfully added', 'success');
				}

				jsonResponse('Error: Cannot add translation now. Please try later.');
			break;
            case 'edit_faq':
                checkAdminAjax('moderate_content');

                $request = request()->request;

                $this->validator->set_rules([
                    [
                        'field' => 'question',
                        'label' => 'Question',
                        'rules' => [
                            'required'      => '',
                            'max_len[250]'  => '',
                        ]
                    ],
                    [
                        'field' => 'answer',
                        'label' => 'Answer',
                        'rules' => [
                            'required'  => '',
                        ]
                    ],
                    [
                        'field' => 'faq',
                        'label' => 'faq ID',
                        'rules' => [
                            'required'  => '',
                            'integer'   => '',
                        ]
                    ]
                ]);

                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                /** @var Faq_Model $faqModel */
                $faqModel = model(Faq_Model::class);

                if (
                    empty($faqId = $request->getInt('faq'))
                    || empty($faq = $this->faq->get_faq($faqId, 'en'))
                ) {
                    jsonResponse('This faq does not exist.');
                }

                if (empty($uploadFolder = checkEncriptedFolder($request->get('encripted_folder')))) {
                    jsonResponse(translate('invalid_encrypted_folder_name'));
                }

                if (!empty($faqTags = (array) $request->get('faq_tags'))) {
                    $tagsLimit = (int) config('faq_tags_max_count', 3);

                    if (count($faqTags) > $tagsLimit) {
                        jsonResponse("The maximum number of tags is {$tagsLimit}");
                    }

                    /** @var Tags_Faq_Model $tagsFaqModel */
                    $tagsFaqModel = model(Tags_Faq_Model::class);

                    $validTags = $tagsFaqModel->findAllBy([
                        'conditions' => [
                            'tagsIds'   => $faqTags,
                        ],
                        'columns'   => [
                            "{$tagsFaqModel->getTable()}.`id_tag`",
                        ],
                    ]);

                    if (count($validTags) != count($faqTags)) {
                        jsonResponse("Invalid tags detected");
                    }

                    $validTagsIds = array_column($validTags, 'id_tag');
                }

                /** @var TinyMVC_Library_Cleanhtml $cleanHtmlLibrary */
                $cleanHtmlLibrary = library(TinyMVC_Library_Cleanhtml::class);

                $cleanHtmlLibrary->setStyle('text-decoration');
                $cleanHtmlLibrary->setAttribute('style,href,title,target,rel,src,alt,width,height');
                $cleanHtmlLibrary->addAdditionalTags('<img><p><span><strong><em><b><i><u><a><br><ol><ul><li>');

                //region Text & image processing
                $inlineImageConfig = 'faq.inline';
                $update = $postImages = [];

                try {
                    $answer = $cleanHtmlLibrary->sanitize($request->get('answer'));
                    $postProcessedImages = $this->processContentImages($answer, $faqId);

                    if (!empty($postProcessedImages['paths'])) {
                        $postImages = array_flip(array_flip($postProcessedImages['paths']));
                    }

                    $answer = $cleanHtmlLibrary->sanitize($this->changeContentPaths(
                        $answer,
                        $faqId,
                        $postImages,
                        $uploadFolder,
                        $inlineImageConfig,
                    ));
                } catch (\Exception $exception) {
                    switch ($exception->getCode()) {
                        case self::IMAGES_INVALID_DOMAIN:
                            $message = 'Image links cannot have external link.';

                            break;
                        default:
                            $message = jsonResponse(translate('systmess_internal_server_error'));

                            break;
                    }

                    jsonResponse($message);
                }

                //endregion Text & image processing
                $imagesToOptimization = [];
                //region Inline images
                if (!empty($postImages)) {
                    $inlineImagesPath = getImgPath($inlineImageConfig, ['{FAQ_ID}' => $faqId]);
                    $inlineImagesTempPath = getTempImgPath($inlineImageConfig, ['{ENCRYPTED_FOLDER_NAME}' => $uploadFolder]);
                    create_dir($inlineImagesPath);

                    foreach ($postImages as $inlineImage) {
                        $inlineImage = ltrim($inlineImage, '/');
                        if (!file_exists($inlineImage) || !startsWith($inlineImage, $inlineImagesTempPath)) {
                            continue;
                        }

                        $inlineImageName = pathinfo($inlineImage, PATHINFO_BASENAME);
                        if (!rename($inlineImage, $inlineImagesPath . $inlineImageName)) {
                            jsonResponse('An error occurred when copying a temporary image to a public folder.');
                        }

                        $imagesToOptimization[] = [
                            'file_path'	=> getcwd() . DS . $inlineImagesPath . $inlineImageName,
                            'type'		=> 'faq_inline_image',
                            'context'	=> ['id_faq' => $faqId],
                        ];
                    }
                }

                $newInlineImages = array_values($this->get_images_stats($this->getImagesFromText($answer)));
                $removedInlineImages = array_diff(
                    array_column(json_decode($faq['inline_images'], true), 'path'),
                    array_column($newInlineImages, 'path'),
                );
                //endregion Inline images

                $translationsData = json_decode($faq['translations_data'], true);
                $translationsData['en']['updated_at'] = date('Y-m-d H:i:s');

                if (
                    !$faqModel->update_faq(
                        $faqId,
                        [
                            'translations_data' => json_encode($translationsData),
                            'inline_images'     => json_encode($newInlineImages, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                            'question'          => $request->get('question'),
                            'answer'            => $answer,
                        ]
                    )
                ) {
                    jsonResponse('This question wasn\'t updated. Please try again later.');
                }

                /** @var Tags_Faq_Relation_Model $tagsFaqRelationModel */
                $tagsFaqRelationModel = model(Tags_Faq_Relation_Model::class);

                $tagsFaqRelationModel->deleteAllBy([
                    'conditions' => [
                        'faqId' => $faqId
                    ],
                ]);

                if (!empty($validTagsIds)) {
                    $faqTagsRelations = [];

                    foreach ($validTagsIds as $tagId) {
                        $faqTagsRelations[] = [
                            'id_faq'    => $faqId,
                            'id_tag'    => $tagId,
                        ];
                    }

                    $tagsFaqRelationModel->insertMany($faqTagsRelations);
                }

                if (!empty($removedInlineImages)) {
                    foreach ($removedInlineImages as $removedInlineImage) {
                        $pathInfo = pathinfo($removedInlineImage);

                        $imagePath = getImgSrc($inlineImageConfig, 'original', ['{FAQ_ID}' => $faqId, '{FILE_NAME}' => $pathInfo['basename']]);
                        $imagePathGlob = getImgSrc($inlineImageConfig, 'original', ['{FAQ_ID}' => $faqId, '{FILE_NAME}' => '*' . $pathInfo['filename'] . '.*']);

                        removeFileByPatternIfExists($imagePath, $imagePathGlob);
                    }
                }

                /** @var Elasticsearch_Faq_Model $elasticsearchFaqModel */
                $elasticsearchFaqModel = model(Elasticsearch_Faq_Model::class);

                $elasticsearchFaqModel->sync($faqId);

                if (!empty($imagesToOptimization)) {
                    /** @var Image_optimization_Model $imageOptimizationModel */
                    $imageOptimizationModel = model(Image_optimization_Model::class);

                    $imageOptimizationModel->insertMany($imagesToOptimization);
                }

                jsonResponse('Question has been successfully updated.', 'success');
			break;
            case 'edit_faq_tag':
                checkAdminAjax('moderate_content');

                $validator = $this->validator;
                $validator->set_rules(array(
                    array(
                        'field' => 'faq_tag',
                        'label' => 'ID',
                        'rules' => array('required' => '', 'integer' => '')
                    ),
                    array(
                        'field' => 'tag_name',
                        'label' => 'Tag name',
                        'rules' => array(
                            'required' => '',
                            'min_len[3]' => '',
                            'max_len[50]' => ''
                        )
                    ),
                    array(
                        'field' => 'top_priority',
                        'label' => 'Top priority',
                        'rules' => array(
                            'required' => '',
                            'integer' => '',
                            'min[1]' => '',
                            'max[100]' => ''
                        )
                    )
                ));
                if (!$validator->validate()) {
                    jsonResponse($validator->get_array_errors());
                }

				$id_faq_tag = (int) $_POST['faq_tag'];

                $faq_tag = model('faq_tags')->get_one($id_faq_tag);
                if(empty($faq_tag)) {
					jsonResponse('Error: This faq tag does not exist.');
                }

                $faq_tags_relation = model('faq_tags_relation')->get_list(array('id_tag' => $id_faq_tag));
                $tag_name = cleanInput($_POST['tag_name']);

                if (
                    model('faq_tags')->change(
                        $id_faq_tag,
                        array(
                            'name' => $tag_name,
                            'slug' => strForURL($tag_name) . "-{$id_faq_tag}",
                            'top_priority' => (int) $_POST['top_priority']
                        )
                    )
                ) {
                    $this->elasticfaq->sync(array_unique(
                        array_column($faq_tags_relation, 'id_faq')
                    ));

					jsonResponse('Tag has been successfully updated.', 'success');
                } else {
					jsonResponse('Error: This tag wasn\'t updated. Please try again later.');
                }
			break;
            case 'edit_faq_i18n':
                checkAdminAjax('moderate_content');


				$validator_rules = array(
					array(
						'field' => 'id',
						'label' => 'FAQ info',
						'rules' => array('required' => '', 'integer' => '')
					),
					array(
						'field' => 'question',
						'label' => 'Question',
						'rules' => array('required' => '', 'max_len[250]' => '')
					),
					array(
						'field' => 'answer',
						'label' => 'Answer',
						'rules' => array('required' => '')
                    )
                );

				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate()) {
					jsonResponse($this->validator->get_array_errors());
                }

				$faq_i18n_id = $_POST['id'];
				$faq_i18n = $this->faq->get_faq_i18n($faq_i18n_id, array(
                    'columns' => array(
                        'I18N.id_faq_i18n as id',
                        'I18N.id_faq as faq_id',
                        'I18N.question',
                        'I18N.answer',
                        'F.translations_data',
                        'L.id_lang as lang_id',
                        'L.lang_iso2 as lang_code',
                    ),
                    'with'    => array('faq' => true, 'language' => true)
                ));
				if(
                    empty($faq_i18n) ||
                    empty($faq_i18n)
                ){
					jsonResponse("FAQ record translation with such ID is not found on this server");
                }

                $lang_id = $faq_i18n['lang_id'];
                $lang_code = (string) $faq_i18n['lang_code'];
                if(
                    $this->session->group_lang_restriction &&
                    !in_array($lang_id, $this->session->group_lang_restriction_list)
                ) {
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));
                }

				$this->load->library('Cleanhtml', 'clean');
                $this->clean->setStyle('text-decoration');
                $this->clean->setAttribute('style,href,title,target,rel');
                $this->clean->addAdditionalTags('<p><span><strong><em><b><i><u><a><br><ol><ul><li>');

                $faq_id = $faq_i18n['faq_id'];
                $language = $this->translations->get_language($lang_id);
                $translations_data = json_decode($faq_i18n['translations_data'], true);

				$update = array(
					'question'   => cleanInput($_POST['question']),
					'answer'     => $this->clean->sanitize($_POST['answer']),
                    'updated_at' => date('Y-m-d H:i:s')
                );

                $translations_data[$lang_code] = array(
                    'lang_name'  => $language['lang_name'],
                    'abbr_iso2'  => $language['lang_iso2'],
                    'updated_at' => date('Y-m-d H:i:s')
                );

				if($this->faq->update_faq_i18n($faq_i18n_id, $update)){
                    $this->faq->update_faq($faq_id, array('translations_data' => json_encode($translations_data)));
                    $this->elasticfaq->sync($faq_id);
					jsonResponse('The translation has been successfully updated.', 'success');
				}

				jsonResponse('Error: Cannot add translation now. Please try later.');
			break;
            case 'delete_faq':
                checkAdminAjax('moderate_content');

				$id_faq = (int)$_POST['faq'];
				if(!$this->faq->exist_faq($id_faq)){
					jsonResponse('Error: This faq does not exist.');
				}

				if ($this->faq->delete_faq($id_faq)){
                    $this->elasticfaq->sync($id_faq);
					jsonResponse("This FAQ has been deleted successfully.", 'success');
				} else {
					jsonResponse('Error: Can not perform this operation now. Please try again later.');
				}
			break;
            case 'administration_list_dt':
                checkAdminAjaxDT('moderate_content,manage_translations');

                $sorting = [
                    'lang' => 'en',
                    'per_p' => (int)$_POST['iDisplayLength'],
                    'start' => (int)$_POST['iDisplayStart'],
                    'limit' => (int)$_POST['iDisplayStart'] . ',' . (int)$_POST['iDisplayLength'],
                    'sort_by' => flat_dt_ordering($_POST, [
                        'dt_id'         => 'id_faq',
                        'dt_question'   => 'question',
                        'dt_answer'     => 'answer',
                        'dt_updated_at' => 'updated_at',
                        'dt_weight'     => 'weight'
                    ])
                ];

                $conditions = dtConditions($_POST, [
                    ['as' => 'translated_in', 'key' => 'translated_in', 'type' => 'cleanInput'],
                    ['as' => 'not_translated_in', 'key' => 'not_translated_in', 'type' => 'cleanInput'],
                    ['as' => 'en_updated_to', 'key' => 'en_updated_to', 'type' => 'getDateFormat:m/d/Y,Y-m-d'],
                    ['as' => 'en_updated_from', 'key' => 'en_updated_from', 'type' => 'getDateFormat:m/d/Y,Y-m-d']
                ]);

                $sorting['sort_by'] = empty($sorting['sort_by']) ? ["id_faq-asc"] : $sorting['sort_by'];

                $params = array_merge($sorting, $conditions);

				$records = $this->faq->get_faq_list($params);
				$records_total = $this->faq->count_faq_list($params);
                $attached_faq_tags = model('faq_tags_relation')->get_list();
                $attached_faq_tags = arrayByKey($attached_faq_tags, 'id_faq', true);

				$output = array(
					'sEcho' => (int) $_POST['sEcho'],
					'iTotalRecords' => $records_total,
					'iTotalDisplayRecords' => $records_total,
					'aaData' => array()
				);

				if(empty($records)) {
					jsonResponse('', 'success', $output);
                }

                $languages = arrayByKey($this->translations->get_allowed_languages(array('skip' => array('en'))), 'lang_iso2');

				foreach ($records as $record) {
                    $i18n_used = array();
                    $i18n_list = array();
                    $i18n_meta = array_filter(json_decode($record['translations_data'], true));
                    $text_updated_date = getDateFormat($i18n_meta['en']['updated_at'], 'Y-m-d H:i:s');
                    $attached_tags = empty($attached_faq_tags[$record['id_faq']]) ? '-' : implode(', ', array_column(
                        $attached_faq_tags[$record['id_faq']],
                        'tag_name'
                    ));

                    foreach ($i18n_meta as $lang_code => $i18n) {
                        if(!isset($languages[$lang_code])) {
                            continue;
                        }

                        if($this->session->group_lang_restriction && !in_array($languages[$lang_code]['id_lang'], $this->session->group_lang_restriction_list)){
                            continue;
                        }

                        $i18n_used[$lang_code] = $lang_code;
                        $i18n_update_date = getDateFormat($i18n['updated_at'], 'Y-m-d H:i:s');
                        $i18n_list[] = '<a href="'.__SITE_URL.'faq/popup_forms/edit_faq_i18n/'.$record['id_faq'].'/'.$lang_code.'"
                                                class="btn btn-xs tt-uppercase mnw-30 w-30 '.(($i18n['updated_at'] < $i18n_meta['en']['updated_at'])?'btn-danger':'btn-primary').' mb-5 fancyboxValidateModalDT fancybox.ajax"
                                                data-title="Edit translation"
                                                title="Last update: '.$i18n_update_date.'">
                                                '.$lang_code.'
                                            </a>';
                    }

                    if(empty($i18n_list)){
                        $i18n_list[] = '&mdash;';
                    }

                    $actions = array();
                    if(have_right('manage_translations') && !empty(array_diff_key($languages, $i18n_used))) {
                        $actions[] = '<a href="'.__SITE_URL . 'faq/popup_forms/add_faq_i18n/'.$record['id_faq'].'"
                                        data-title="Add translation"
                                        title="Add translation"
                                        class="fancyboxValidateModalDT fancybox.ajax">
                                        <i class="ep-icon ep-icon_globe-circle"></i>
                                    </a>';
                    }

                    if(have_right('moderate_content')) {
                        $display_weight = '<div class="input-group">
                                                <input type="number" class="form-control" value="'. $record['weight'] .'" placeholder="'. $record['weight'] .'">
                                                <span class="input-group-btn confirm-dialog"
                                                    data-callback="save_weight"
                                                    data-message="Are you sure want save this value?"
                                                    data-id_faq="'.$record['id_faq'].'"
                                                    title="Save"
                                                >
                                                    <button class="btn btn-default" type="button">Save</button>
                                                </span>
                                            </div>';

                        $actions[] = '<a class="ep-icon ep-icon_pencil fancyboxValidateModalDT fancybox.ajax"
                                        title="Edit Question"
                                        href="'.__SITE_URL . 'faq/popup_forms/edit_faq/'.$record['id_faq'].'"
                                        data-title="Edit question">
                                    </a>';
                        $actions[] = '<a class="ep-icon ep-icon_remove txt-red confirm-dialog"
                                        data-callback="delete_faq"
                                        data-message="Are you sure want delete this qustion?"
                                        title="Delete question"
                                        data-faq="'.$record['id_faq'].'">
                                    </a>';
                    }

                    if(empty($actions)){
                        $actions[] = '&mdash;';
                    }

					$output['aaData'][] = array(
						'dt_id'          => $record['id_faq'],
						'dt_weight'      => $display_weight,
						'dt_question'    => $record['question'],
                        'dt_updated_at'  => $text_updated_date,
						'dt_tlangs_list' => implode(' ', $i18n_list),
						'dt_tags_list'   => $attached_tags,
                        'dt_actions'     => implode(' ', $actions),
					);
				}

				jsonResponse('', 'success', $output);
            break;
            case 'delete_faq_tag':
                checkAdminAjax('moderate_content');

                $id_tag = (int) $_POST['id_tag'];
                $faq_tags_relation = model('faq_tags_relation')->get_list(array('id_tag' => $id_tag));

                if (model('faq_tags')->delete($id_tag)){
                    $this->elasticfaq->sync(array_unique(
                        array_column($faq_tags_relation, 'id_faq')
                    ));

                    jsonResponse("This FAQ tag has been deleted successfully.", 'success');
                } else {
                    jsonResponse('Error: Can not perform this operation now. Please try again later.');
                }
            break;
            case 'save_tag_weight':
                $id_faq = (int) $_POST['id_faq'];
                $weight = (int) $_POST['weight'];

				$this->validator->set_rules(array(
                    array(
                        'field' => 'id_faq',
                        'label' => 'ID',
                        'rules' => array('required' => '', 'integer' => '')
                    ),
                    array(
                        'field' => 'weight',
                        'label' => 'Weight',
                        'rules' => array(
                            'required' => '',
                            'integer' => '',
                            'min[1]' => '',
                            'max[1000]' => ''
                        )
                    )
                ));
				if (!$this->validator->validate()) {
					jsonResponse($this->validator->get_array_errors());
                }

                $faq = model('faq');

				if(!$relation = $faq->get_faq($id_faq)){
					jsonResponse('Error: This tag does not exist.');
				}

				if ($faq->update_faq($id_faq, array('weight' => $weight))){
                    $this->elasticfaq->sync($relation['id_faq']);
					jsonResponse("This FAQ has been changed successfully.", 'success');
				} else {
					jsonResponse('Error: Can not perform this operation now. Please try again later.');
				}
            break;
            case 'tags_administration_list_dt':
                checkAdminAjaxDT('moderate_content,manage_translations');

                $conditions = array();

				$from = (int)$_POST['iDisplayStart'];
				$till = (int)$_POST['iDisplayLength'];
				$conditions['limit'] = $till;
				$conditions['offset'] = $from;

				if ($_POST['iSortingCols'] > 0) {
					for ($i = 0; $i < $_POST['iSortingCols']; $i++) {
						switch ($_POST["mDataProp_" . (int) $_POST['iSortCol_' . $i]]) {
                            case 'dt_top_priority':
                                $conditions['sort_by'][] = 'top_priority-' . $_POST['sSortDir_' . $i];
                            break;
						}
					}
				}

                $faq_tags = model('faq_tags');
                $records = $faq_tags->get_list($conditions);
				$records_total = $faq_tags->get_count($conditions);

				$output = array(
					'sEcho' => (int)$_POST['sEcho'],
					'iTotalRecords' => $records_total,
					'iTotalDisplayRecords' => $records_total,
					'aaData' => array()
				);

				if(empty($records)) {
					jsonResponse('', 'success', $output);
                }

				foreach ($records as $record) {

                    $actions = array();

                    if(have_right('moderate_content')) {
                        $actions[] = '<a class="ep-icon ep-icon_pencil fancyboxValidateModalDT fancybox.ajax"
                                        title="Edit tag"
                                        href="' . __SITE_URL . 'faq/popup_forms/edit_faq_tag/' . $record['id_tag'] . '"
                                        data-title="Edit tag">
                                    </a>';

                        $actions[] = '<a class="ep-icon ep-icon_remove txt-red confirm-dialog"
                                        data-callback="delete_faq_tag"
                                        data-message="Are you sure want delete this faq tag?"
                                        title="Delete faq tag"
                                        data-id_tag="'.$record['id_tag'].'">
                                    </a>';
                    }

                    if(empty($actions)){
                        $actions[] = '&mdash;';
                    }

					$output['aaData'][] = array(
                        'dt_tag_name' => $record['name'],
                        'dt_top_priority' => $record['top_priority'],
                        'dt_actions'     => implode(' ', $actions),
					);
				}

				jsonResponse('', 'success', $output);
			break;
            case 'upload_temp_inline_image':
                checkPermisionAjax('moderate_content');

                if (empty($files = $_FILES['faq_inline_image'])) {
                    jsonResponse('Please select file to upload.');
                }

                if (empty($uploadFolder = checkEncriptedFolder(uri()->segment(4)))) {
                    jsonResponse('File upload path is not correct.');
                }

                $imageModule = 'faq.inline';
                $tempPath = getTempImgPath($imageModule, ['{ENCRYPTED_FOLDER_NAME}' => $uploadFolder]);

                create_dir($tempPath);

                /** @var TinyMVC_Library_Image_intervention $interventionImageLibrary */
                $interventionImageLibrary = library(TinyMVC_Library_Image_intervention::class);

                $images = $interventionImageLibrary->image_processing(
                    $files,
                    [
                        'destination'   => $tempPath,
                        'rules'         => config("img.{$imageModule}.rules"),
                    ]
                );

                if (!empty($images['errors'])) {
                    jsonResponse($images['errors']);
                }

                jsonResponse('', 'success', ['path'=> $tempPath . $images[0]['new_name'], 'name' => $images[0]['new_name']]);
            break;
		}
    }

    public function update_translation_meta()
    {
        if (!logged_in()){
            show_404();
        }

        $this->load->model('Faq_Model', 'faq');

        $faq_list = arrayByKey($this->faq->get_faq_list(), 'id_faq');
        $faq_i18n_list = arrayByKey($this->faq->get_faq_i18n_list(array(
            'columns' => array('id_faq', 'id_faq_i18n', 'lang_faq as lang_code', 'lang_name', 'updated_at'),
            'with'    => array('language' => true)
        )), 'id_faq', true);

        foreach ($faq_list as $faq_id => $faq) {
            $update = array(
                "en" => array(
                    "abbr_iso2"  => "en",
                    "lang_name"  => "English",
                    "updated_at" => date("Y-m-d H:i:s")
                )
            );

            if(isset($faq_i18n_list[$faq_id])) {
                foreach ($faq_i18n_list[$faq_id] as $i18n) {
                    $update[$i18n["lang_code"]] = array(
                        "abbr_iso2"  => $i18n["lang_code"],
                        "lang_name"  => $i18n['lang_name'],
                        "updated_at" => null !== $i18n['updated_at'] ? $i18n['updated_at'] : date("Y-m-d H:i:s"),
                    );
                }
            }

            $this->faq->update_faq($faq_id, array('translations_data' => json_encode($update, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)));
        }
    }

    /**
     * @param string $text
     * @param int $faqId
     *
     * @return array
     */
    private function processContentImages(string $text, int $faqId): array
    {
        if (empty($inlineImages = $this->getImagesFromText($text))) {
            return [];
        }

        $inlineImagesPaths = [];
        $imagePath = getImgPath('faq.inline', ['{FAQ_ID}' => $faqId]);

        foreach ($inlineImages as $imagePath) {
            if (!filter_var($imagePath, FILTER_VALIDATE_URL)) {
                continue;
            }

            $imageHost = parse_url($imagePath, PHP_URL_HOST);

            if (
                __HTTP_HOST_ORIGIN !== $imageHost
                && !endsWith($imageHost, __HTTP_HOST_ORIGIN)
                && !empty($imagePath)
            ) {
                throw new Exception('Images from external domains are not allowed', self::IMAGES_INVALID_DOMAIN);
            }
        }

        foreach ($inlineImages as $key => $image) {
            if (null !== parse_url($image, PHP_URL_HOST)) {
                if (false !== strpos($image, $imagePath)) {
                    continue;
                }

                if (false === strpos($image, __HTTP_HOST_ORIGIN)) {
                    throw new Exception('Images from external domains are not allowed', self::IMAGES_INVALID_DOMAIN);

                    continue;
                }

                list(, $url) = explode(__HTTP_HOST_ORIGIN, $image);
                $image = '/' . trim($url, '/');
            } elseif (!startsWith(ltrim($image, '/'), 'temp')) {
                continue;
            }

            $inlineImagesPaths[] = $image;
        }

        return [
            'collected' => $inlineImages,
            'paths'     => $inlineImagesPaths,
        ];
    }

    private function changeContentPaths(string $text, int $faqId, array $inlineImages, string $uploadFolder, string $imageConfig)
    {
        if (empty($inlineImages)) {
            return $text;
        }

        $imageTempPath = getTempImgPath($imageConfig, ['{ENCRYPTED_FOLDER_NAME}' => $uploadFolder]);

        $replacements = [];
        foreach ($inlineImages as $index => $image) {
            if (!startsWith(ltrim($image, '/'), $imageTempPath)) {
                continue;
            }

            $replacements[$image] = __IMG_URL . getImgSrc($imageConfig, 'original', ['{FAQ_ID}' => $faqId, '{FILE_NAME}' => pathinfo($image, PATHINFO_BASENAME)]);
        }

        return empty($replacements) ? $text : strtr($text, $replacements);
    }

    private function get_images_stats(array $inlineImages)
    {
        $images = [];

        if (empty($inlineImages)) {
            return $images;
        }

        foreach ($inlineImages as $key => $url) {
            if (null === ($host = parse_url($url, PHP_URL_HOST))) {
                $path = $url;
            } else {
                list(, $path) = explode($host, $url);
            }

            list($realpath) = explode('?', $path);
            $fullpath = $_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($realpath, '/');
            if (file_exists($fullpath)) {
                $imagesize = getimagesize($fullpath);
                $imageinfo = pathinfo($fullpath);

                $images[] = [
                    'url'       => $url,
                    'path'      => $path,
                    'name'      => $imageinfo['basename'],
                    'filename'  => $imageinfo['filename'],
                    'extension' => $imageinfo['extension'],
                    'width'     => $imagesize[0],
                    'height'    => $imagesize[1],
                    'mime'      => $imagesize['mime'],
                ];
            }
        }

        return $images;
    }

    /**
     * @param string $text
     */
    private function getImagesFromText($text): array
    {
        $matches = [];
        $host = preg_quote(__HTTP_HOST_ORIGIN);
        $pattern = '/<img[^>]*?src=["\'](((\/?temp[^"\'>]+)|(https?\:\/\/([\w]+\.)?' . $host . '\/temp[^"\'>]+))|(https?\:\/\/([\w]+\.)?' . $host . '[^"\'>]+)|([^"\'\s>]+))["\'][^>]*?>/m';
        preg_match_all($pattern, $text, $matches, PREG_PATTERN_ORDER);
        $images = array_filter($matches[1]);

        return $images ?: [];
    }
}
