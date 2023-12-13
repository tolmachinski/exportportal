<?php

/**
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [18.12.2021]
 * Controller Refactoring
 */
class Topics_Controller extends TinyMVC_Controller
{
    private $breadcrumbs = [];

    public function index()
    {
        headerRedirect(__SITE_URL . 'topics/help', 301);
    }

    public function help()
    {
        $uri_params = uri()->uri_to_assoc(4);
        $links_map = [
            'page' => [
                'type' => 'uri',
                'deny' => ['page'],
            ],
            'keywords' => [
                'type' => 'get',
                'deny' => ['keywords'],
            ],
        ];

        checkURI($uri_params, ['page']);

        $page = 1;
        $meta_params = [];
        if (!empty($uri_params['page'])) {
            if (!is_numeric($uri_params['page']) || $uri_params['page'] < 1) {
                show_404();
            }

            $page = (int) $uri_params['page'];
            $meta_params['[PAGE]'] = $page;
        }

        $this->breadcrumbs[] = [
            'link'     => __SITE_URL . 'help',
            'title'    => translate('breadcrumb_help'),
        ];

        $this->breadcrumbs[] = [
            'link'     => __SITE_URL . 'topics',
            'title'    => translate('breadcrumb_topics'),
        ];

        $links_tpl = uri()->make_templates($links_map, $uri_params);
        $links_tpl_without = uri()->make_templates($links_map, $uri_params, true);

        $per_page = config('topics_help_per_page', 20);

        $conditions = [
            'visible_topic' => 1,
            'start'                  => $per_page * ($page - 1),
            'limit'                  => $per_page,
        ];

        if (isset($_GET['keywords']) && !empty($keywords = cleanOutput(cleanInput(cut_str($_GET['keywords']))))) {
            $conditions['keywords'] = $keywords;
            $meta_params['[KEYWORDS]'] = $keywords;

            model('search_log')->log($keywords);
        }

        model('elasticsearch_topics')->get_topics($conditions);
        $topics = model('elasticsearch_topics')->topics_records;
        $count_topics = empty($topics) ? 0 : model('elasticsearch_topics')->topics_count;

        $paginator_config = [
            'base_url'      => 'topics/help/' . $links_tpl['page'],
            'first_url'           => get_dynamic_url('topics/help/' . $links_tpl_without['page']),
            'total_rows'    => $count_topics,
            'per_page'      => $per_page,
            'replace_url'   => true,
        ];

        library('pagination')->initialize($paginator_config);

        $partial_search_params = [
            'action'                 => __SITE_URL . 'topics/help',
            'keywords'               => $keywords,
            'title'                  => translate('help_search_btn'),
            'input_text_placeholder' => translate('help_search_placeholder', null, true),
            'btn_text_submit'        => translate('help_search_btn'),
        ];

        $data = [
            'sidebar_right_content'      => 'new/topics/sidebar_view',
            'header_out_content'         => 'new/topics/header_view',
            'footer_out_content'         => 'new/about/bottom_become_member_view',
            'current_page'                     => 'topics',
            'main_content'                     => 'new/topics/index_view',
            'breadcrumbs'                      => $this->breadcrumbs,
            'pagination'                       => library('pagination')->create_links(),
            'page_link'                           => 'topics/help',
            'curr_link'                           => 'topics/help',
            'nav_page'                            => 'topics',
            'keywords'                            => $keywords,
            'topics'                              => $topics,
            'per_p'                                  => $per_page,
            'count'                                  => $count_topics,
            'page'                                   => $page,
            'meta_params'                      => $meta_params,
            'partial_search_params'            => $partial_search_params,
        ];

        $this->view->assign($data);
        $this->view->display('new/index_template_view');
    }

    public function detail()
    {
        $this->_load_main();

        $id_topic = (int) $this->uri->segment(4);

        if (!empty($id_topic)) {
            $data['topic'] = $this->topics->get_topic($id_topic);
        } else {
            $this->session->setMessages('Error: You should indicate the topic.', 'errors');
            headerRedirect(__SITE_URL . 'topics/help');
        }

        $popular_topics_limit = (int) config('popular_topics_sidebar_limit', 5);
        $data['topics'] = $this->topics->get_topics(['limit' => "0, {$popular_topics_limit}"]);

        $partial_search_params = [
            'action'                 => __SITE_URL . 'topics/help',
            'keywords'               => '',
            'title'                  => translate('help_search_btn'),
            'input_text_placeholder' => translate('help_search_placeholder', null, true),
            'btn_text_submit'        => translate('help_search_btn'),
        ];

        $data['partial_search_params'] = $partial_search_params;
        $data['meta_params']['[TOPIC_NAME]'] = $data['topic']['title_topic'];

        $this->breadcrumbs[] = [
            'link'     => __SITE_URL . 'help',
            'title'    => translate('breadcrumb_help'),
        ];

        $this->breadcrumbs[] = [
            'link'     => __SITE_URL . 'topics',
            'title'    => translate('breadcrumb_topics'),
        ];

        $this->breadcrumbs[] = [
            'link'     => '',
            'title'    => truncWords($data['topic']['title_topic'], 20),
        ];

        $data['title'] = 'Popular topic';
        $data['breadcrumbs'] = $this->breadcrumbs;

        $data['current_page'] = 'topics';
        $data['header_out_content'] = 'new/topics/header_view';
        $data['sidebar_right_content'] = 'new/topics/detail_sidebar_view';
        $data['main_content'] = 'new/topics/detail_view';
        $data['footer_out_content'] = 'new/about/bottom_become_partner_view';
        $this->view->assign($data);
        $this->view->display('new/index_template_view');
    }

    public function administration()
    {
        if (!logged_in()) {
            $this->session->setMessages(translate('systmess_error_should_be_logged_in'), 'errors');
            headerRedirect(__SITE_URL . 'login');
        }

        if (!have_right_or('moderate_content,manage_translations')) {
            headerRedirect(__SITE_URL . 'topics');
        }

        $this->_load_main();

        $data = [];
        $data['languages'] = $this->translations->get_allowed_languages();

        $this->view->assign('title', 'Popular topics');
        $this->view->assign($data);
        $this->view->display('admin/header_view');
        $this->view->display('admin/topics/index_view');
        $this->view->display('admin/footer_view');
    }

    public function ajax_topics_operation()
    {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        if (!logged_in()) {
            jsonResponse(translate('systmess_error_should_be_logged'));
        }

        $this->_load_main();

        $op = $this->uri->segment(3);
        switch ($op) {
            case 'add_topic':
                checkAdminAjax('manage_content');

                $validator_rules = [
                    [
                        'field' => 'title_topic',
                        'label' => 'Title topic',
                        'rules' => ['required' => '', 'max_len[100]' => ''],
                    ],
                    [
                        'field' => 'text_topic_small',
                        'label' => 'Small text topic',
                        'rules' => ['required' => '', 'max_len[200]' => ''],
                    ],
                    [
                        'field' => 'text_topic',
                        'label' => 'Text topic',
                        'rules' => ['required' => ''],
                    ],
                    [
                        'field' => 'visible',
                        'label' => 'Visible',
                        'rules' => ['required' => '', 'max[1]' => '', 'min[0]' => ''],
                    ],
                ];

                $this->validator->set_rules($validator_rules);

                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $translations_data = [
                    'en' => [
                        'lang_name'  => 'English',
                        'abbr_iso2'  => 'en',
                        'updated_at' => date('Y-m-d H:i:s'),
                    ],
                ];

                $insert = [
                    'title_topic'       => cleanInput($_POST['title_topic']),
                    'text_topic_small'  => $_POST['text_topic_small'],
                    'text_topic'        => $_POST['text_topic'],
                    'visible_topic'     => (int) $_POST['visible'],
                    'translations_data' => json_encode($translations_data),
                ];

                $id_topic = $this->topics->set_topics($insert);
                $this->elastictopics->sync($id_topic);
                jsonResponse('The topic was successfully inserted.', 'success');

            break;
            case 'add_topic_i18n':
                checkAdminAjax('manage_translations');

                $validator_rules = [
                    [
                        'field' => 'id_topic',
                        'label' => 'Topic info',
                        'rules' => ['required' => '', 'integer' => ''],
                    ],
                    [
                        'field' => 'id_lang',
                        'label' => 'Language',
                        'rules' => ['required' => '', 'integer' => ''],
                    ],
                    [
                        'field' => 'title_topic',
                        'label' => 'Title topic',
                        'rules' => ['required' => '', 'max_len[100]' => ''],
                    ],
                    [
                        'field' => 'text_topic_small',
                        'label' => 'Small text topic',
                        'rules' => ['required' => '', 'max_len[200]' => ''],
                    ],
                    [
                        'field' => 'text_topic',
                        'label' => 'Text topic',
                        'rules' => ['required' => ''],
                    ],
                ];

                $this->validator->set_rules($validator_rules);

                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $id_topic = $_POST['id_topic'];
                $topic = $this->topics->get_topic($id_topic);
                if (empty($topic)) {
                    jsonResponse('Error: The topic does not exist.');
                }

                $id_lang = $_POST['id_lang'];
                $lang_topic = $this->translations->get_allowed_languages(['id_lang' => $id_lang]);
                if (empty($lang_topic)) {
                    jsonResponse('Error: Language does not exist.');
                }
                $lang_topic = $lang_topic[0];

                $translations_data = json_decode($topic['translations_data'], true);
                if (array_key_exists($lang_topic['lang_iso2'], $translations_data)) {
                    jsonResponse('Error: Topic translation for this language already exist.');
                }

                $translations_data[$lang_topic['lang_iso2']] = [
                    'lang_name'  => $lang_topic['lang_name'],
                    'abbr_iso2'  => $lang_topic['lang_iso2'],
                    'updated_at' => date('Y-m-d H:i:s'),
                ];

                $insert = [
                    'id_topic'         => $id_topic,
                    'title_topic'      => cleanInput($_POST['title_topic']),
                    'text_topic_small' => $_POST['text_topic_small'],
                    'text_topic'       => $_POST['text_topic'],
                    'lang_topic'       => $lang_topic['lang_iso2'],
                ];

                if ($this->topics->set_topic_i18n($insert)) {
                    $this->topics->update_topic($id_topic, ['translations_data' => json_encode($translations_data)]);
                    $this->elastictopics->sync($insert['id_topic']);
                    jsonResponse('The translation has been successfully added', 'success');
                }

                jsonResponse('Error: Cannot add translation now. Please try later.');

            break;
            case 'edit_topic':
                checkAdminAjax('manage_content');

                $validator_rules = [
                    [
                        'field' => 'title_topic',
                        'label' => 'Title topic',
                        'rules' => ['required' => '', 'max_len[100]' => ''],
                    ],
                    [
                        'field' => 'text_topic_small',
                        'label' => 'Small text topic',
                        'rules' => ['required' => '', 'max_len[200]' => ''],
                    ],
                    [
                        'field' => 'text_topic',
                        'label' => 'Text topic',
                        'rules' => ['required' => ''],
                    ],
                    [
                        'field' => 'visible',
                        'label' => 'Visible',
                        'rules' => ['required' => '', 'min[0]' => '', 'max[1]' => ''],
                    ],
                    [
                        'field' => 'id_topic',
                        'label' => 'Topic id',
                        'rules' => ['required' => '', 'integer' => ''],
                    ],
                ];

                $this->validator->set_rules($validator_rules);

                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $id_topic = $_POST['id_topic'];
                $topic = $this->topics->get_topic($id_topic, 'en');
                if (empty($topic)) {
                    jsonResponse('Error: The topic does not exist.');
                }

                $title_topic = cleanInput($_POST['title_topic']);
                $text_topic_small = $_POST['text_topic_small'];
                $text_topic = $_POST['text_topic'];
                $visible_topic = (int) $_POST['visible'];

                $english_changes = $title_topic == $topic['title_topic'] && $text_topic_small == $topic['text_topic_small'] && $text_topic == $topic['text_topic'];

                if ($english_changes && $visible_topic == $topic['visible_topic']) {
                    jsonResponse('The topic was successfully updated.', 'success');
                }

                $update = [
                    'title_topic'      => $title_topic,
                    'text_topic_small' => $text_topic_small,
                    'text_topic'       => $text_topic,
                    'visible_topic'    => $visible_topic,
                ];

                if (!$english_changes) {
                    $translations_data = json_decode($topic['translations_data'], true);
                    $translations_data['en']['updated_at'] = date('Y-m-d H:i:s');
                    $update['translations_data'] = json_encode($translations_data);
                }

                $this->topics->update_topic($id_topic, $update);
                $this->elastictopics->sync($id_topic);
                jsonResponse('The topic was successfully updated.', 'success', $update);

            break;
            case 'edit_topic_i18n':
                checkAdminAjax('manage_translations');

                $validator_rules = [
                    [
                        'field' => 'id_topic_i18n',
                        'label' => 'Topic info',
                        'rules' => ['required' => '', 'integer' => ''],
                    ],
                    [
                        'field' => 'title_topic',
                        'label' => 'Title topic',
                        'rules' => ['required' => '', 'max_len[100]' => ''],
                    ],
                    [
                        'field' => 'text_topic_small',
                        'label' => 'Small text topic',
                        'rules' => ['required' => '', 'max_len[200]' => ''],
                    ],
                    [
                        'field' => 'text_topic',
                        'label' => 'Text topic',
                        'rules' => ['required' => ''],
                    ],
                ];

                $this->validator->set_rules($validator_rules);

                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $id_topic_i18n = $_POST['id_topic_i18n'];
                $topic_i18n = $this->topics->get_topic_i18n(['id_topic_i18n' => $id_topic_i18n]);
                if (empty($topic_i18n)) {
                    jsonResponse('Error: The topic translation does not exist.');
                }

                $topic = $this->topics->get_topic($topic_i18n['id_topic']);
                if (empty($topic)) {
                    jsonResponse('Error: The topic could not be found.');
                }

                $translations_data = json_decode($topic['translations_data'], true);
                $translations_data[$topic_i18n['lang_topic']]['updated_at'] = date('Y-m-d H:i:s');

                $update = [
                    'translations_data' => json_encode($translations_data),
                ];

                $update_i18n = [
                    'title_topic'      => cleanInput($_POST['title_topic']),
                    'text_topic_small' => $_POST['text_topic_small'],
                    'text_topic'       => $_POST['text_topic'],
                ];

                if ($this->topics->update_topic_i18n($id_topic_i18n, $update_i18n)) {
                    if (!$this->topics->update_topic($topic_i18n['id_topic'], $update)) {
                        jsonResponse('Could not save topic');
                    }
                    $this->elastictopics->sync($topic_i18n['id_topic']);

                    jsonResponse('The translation has been successfully updated.', 'success');
                }

                jsonResponse('Error: Cannot add translation now. Please try later.');

            break;
            case 'delete_topic':
                checkAdminAjax('manage_content');

                $id_topic = (int) $_POST['topic'];
                if ($this->topics->delete_topic($id_topic)) {
                    $this->elastictopics->sync($id_topic);
                    jsonResponse('The topic has been successfully deleted.', 'success');
                }

                jsonResponse('Error: Cannot delete the topic now. Please try later.');

            break;
            case 'administration_dt':
                if (!have_right_or('manage_content,manage_translations')) {
                    jsonResponse('You do not have rights.');
                }

                $params = [
                    'per_p' => (int) $_POST['iDisplayLength'],
                    'start' => (int) $_POST['iDisplayStart'],
                    'lang'     => 'en',
                ];

                if (isset($_POST['translated_in'])) {
                    $params['translated_in'] = $_POST['translated_in'];
                }

                if (isset($_POST['not_translated_in'])) {
                    $params['not_translated_in'] = $_POST['not_translated_in'];
                }

                if (isset($_POST['en_updated_to']) && validateDate($_POST['en_updated_to'], 'm/d/Y')) {
                    $params['en_updated_to'] = getDateFormat($_POST['en_updated_to'], 'm/d/Y', 'Y-m-d');
                }

                if (isset($_POST['en_updated_from']) && validateDate($_POST['en_updated_from'], 'm/d/Y')) {
                    $params['en_updated_from'] = getDateFormat($_POST['en_updated_from'], 'm/d/Y', 'Y-m-d');
                }

                if ($_POST['iSortingCols'] > 0) {
                    for ($i = 0; $i < $_POST['iSortingCols']; ++$i) {
                        switch ($_POST['mDataProp_' . (int) $_POST['iSortCol_' . $i]]) {
                            case 'dt_id':
                                $params['sort_by'][] = 'id_topic-' . $_POST['sSortDir_' . $i];

                            break;
                            case 'dt_title':
                                $params['sort_by'][] = 'title_topic-' . $_POST['sSortDir_' . $i];

                            break;
                            case 'dt_updated_at':
                                $params['sort_by'][] = 'updated_at-' . $_POST['sSortDir_' . $i];

                            break;
                        }
                    }
                }

                if (!empty($_POST['sSearch'])) {
                    $params['keywords'] = cleanInput($_POST['sSearch']);
                }

                $topics = $this->topics->get_topics($params);
                $topics_total = $this->topics->count_topics($params);

                $output = [
                    'sEcho'                => (int) $_POST['sEcho'],
                    'iTotalRecords'        => $topics_total,
                    'iTotalDisplayRecords' => $topics_total,
                    'aaData'               => [],
                ];

                $languages = arrayByKey($this->translations->get_allowed_languages(['skip' => 'en']), 'lang_iso2');
                foreach ($topics as $topic) {
                    $i18n_meta = array_filter(json_decode($topic['translations_data'], true));
                    $i18n_list = [];
                    $text_updated_date = getDateFormat($i18n_meta['en']['updated_at'], 'Y-m-d H:i:s');

                    foreach ($i18n_meta as $lang_key => $i18n) {
                        if (!array_key_exists($lang_key, $languages)) {
                            continue;
                        }

                        $updated_at = getDateFormat($i18n['updated_at'], 'Y-m-d H:i:s');

                        $i18n_list[] = '<a
                                            href="' . __SITE_URL . 'topics/popup_forms/edit_topic_i18n/' . $topic['id_topic'] . '/' . $i18n['abbr_iso2'] . '"
                                            class="btn btn-xs tt-uppercase mb-5 mnw-25 w-30 fancyboxValidateModalDT fancybox.ajax ' . ($i18n['updated_at'] < $i18n_meta['en']['updated_at'] ? ' btn-danger ' : ' btn-primary ') . '"
                                            data-title="Edit translation"
                                            data-value-text="' . $i18n['lang_name'] . '"
                                            data-value="' . $i18n['abbr_iso2'] . '"
                                            data-name="translated_in"
                                            title="Last update at: ' . $updated_at . '">
                                            ' . $i18n['abbr_iso2'] . '
                                        </a>';
                    }

                    $actions = [];
                    if (have_right('manage_translations') && array_diff_key($languages, $i18n_meta)) {
                        $actions[] = '<a class="ep-icon ep-icon_globe-circle dropdown-toggle fancyboxValidateModalDT fancybox.ajax" href="' . __SITE_URL . 'topics/popup_forms/add_topic_i18n/' . $topic['id_topic'] . '" data-title="Add translation" title="Add translation"></a>';
                    }

                    if (have_right('manage_content')) {
                        $actions[] = '<a
                                        class="ep-icon ep-icon_pencil txt-blue fancyboxValidateModalDT fancybox.ajax"
                                        href="' . __SITE_URL . 'topics/popup_forms/edit_topic/' . $topic['id_topic'] . '"
                                        title="Edit topic"
                                        data-title="Edit topic">
                                    </a>';
                        $actions[] = '<a
                                        class="ep-icon ep-icon_remove txt-red confirm-dialog"
                                        data-callback="delete_topic" data-topic="' . $topic['id_topic'] . '"
                                        title="Remove topic"
                                        data-message="Are you sure you want to delete this topic?"
                                        href="#">
                                    </a>';
                    }

                    if (empty($actions)) {
                        $actions[] = '&mdash;';
                    }

                    $output['aaData'][] = [
                        'dt_id'          => $topic['id_topic'],
                        'dt_title'       => $topic['title_topic'],
                        'dt_updated_at'  => $text_updated_date,
                        'dt_actions'     => implode(' ', $actions),
                        'dt_tlangs_list' => implode(' ', $i18n_list),
                    ];
                }
                jsonResponse('', 'success', $output);

            break;
        }
    }

    public function popup_forms()
    {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        if (!logged_in()) {
            messageInModal(translate('systmess_error_should_be_logged'));
        }

        $this->_load_main();

        $op = $this->uri->segment(3);
        switch ($op) {
            case 'add_topic':
                checkPermisionAjaxModal('manage_content');

                $this->view->display('admin/topics/form_view');

            break;
            case 'add_topic_i18n':
                checkPermisionAjaxModal('manage_translations');

                $id_topic = (int) $this->uri->segment(4);
                $data['topic'] = $this->topics->get_topic($id_topic);
                if (empty($data['topic'])) {
                    messageInModal('Could not find the topic.');
                }

                $data['tlanguages'] = $this->translations->get_allowed_languages(['skip' => 'en']);
                if (empty($data['tlanguages'])) {
                    messageInModal('You haven\'t rights to add/edit translations');
                }

                $this->view->display('admin/topics/form_i18n_view', $data);

            break;
            case 'edit_topic':
                checkPermisionAjaxModal('manage_content');

                $id_topic = (int) $this->uri->segment(4);
                $data['topic'] = $this->topics->get_topic($id_topic);
                $this->view->display('admin/topics/form_view', $data);

            break;
            case 'edit_topic_i18n':
                checkPermisionAjaxModal('manage_translations');

                $id_topic = (int) $this->uri->segment(4);
                $lang_iso2 = $this->uri->segment(5);
                $language = $this->translations->get_language_by_iso2($lang_iso2);
                if (empty($language)) {
                    messageInModal('Error: the language could not be found');
                }

                if ($this->session->group_lang_restriction && !in_array($language['id_lang'], $this->session->group_lang_restriction_list)) {
                    messageInModal('Error: wrong language for translate');
                }

                $data['topic'] = $this->topics->get_topic($id_topic);
                if (empty($data['topic'])) {
                    messageInModal('Error: Could not find the topic.');
                }

                $data['topic_i18n'] = $this->topics->get_topic_i18n(['id_topic' => $id_topic, 'lang_topic' => $lang_iso2]);
                if (empty($data['topic_i18n'])) {
                    messageInModal('Error: Could not find the translation');
                }

                $data['lang_block'] = $language;
                $this->view->display('admin/topics/form_i18n_view', $data);

            break;
        }
    }

    private function _load_main()
    {
        $this->load->model('Topics_Model', 'topics');
        $this->load->model('Elasticsearch_Topics_Model', 'elastictopics');
    }
}
