<?php

use Doctrine\DBAL\Schema\View;

/**
 * Controller Newsletter
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 *
 * @property \TinyMVC_Load              $load
 * @property \TinyMVC_View              $view
 * @property \TinyMVC_Library_URI       $uri
 * @property \TinyMVC_Library_Session   $session
 * @property \TinyMVC_Library_Cookies   $cookies
 * @property \TinyMVC_Library_Upload    $upload
 * @property \TinyMVC_Library_validator $validator
 * @property \Translations_Model        $translations
 *
 */
class Newsletter_Controller extends TinyMVC_Controller
{

    public function index()
    {
        show_404();
    }

    function all() {

        $newsArchiveModel = model(Ep_News_Archive_Model::class);

        $links_map = array(
            'page' => array(
                'type' => 'uri',
                'deny' => array('page'),
            )
        );
        $uri = $this->uri->uri_to_assoc(4);
        $year = (int) $this->uri->segment(3);
        $links_tpl = $this->uri->make_templates($links_map, $uri);

        $data['per_p'] = $params['per_p'] = 10;
        $data['page'] = !empty($uri['page']) ? $uri['page'] : 1;

        $params = [
            'limit'  => $data['per_p'],
            'page'   => (int) $data['page'],
            'offset' => $data['per_p'] * ($data['page'] - 1),
            'year'   => !empty($uri['year']) ? (int) $uri['year'] : ''
        ];

        $collection = $newsArchiveModel->getNewsArchives($params);
        $yearsCollection = $newsArchiveModel->getArchivesGroupedByYears();

        $data['selector_links'] = [];
        $data['count'] = $collection['total'];
        $data['selected_year'] = $params['year'];
        $data['newsletter_archive']= $collection['data'];

        foreach($yearsCollection as $row) {
            $year = getDateFormat($row['published_on'], 'Y-m-d H:i:s', 'Y');
            $data['selector_links'][$year] = "{$this->uri->hostname()}/newsletter/all/year/{$year}";
        }

        $page_link = replace_dynamic_uri($data['page'], $links_tpl['page'], "newsletter/all/", false);
        list($data['page_link'], $data['get_per_p']) = explode('?', $page_link);

        $paginator_config = array(
            'base_url'      => "newsletter/all/" . $links_tpl['page'],
            'total_rows'    => $data['count'],
            'per_page'      => $data['per_p'],
            'replace_url'   => true,
        );
        $this->load->library('Pagination', 'pagination');
        $this->pagination->initialize($paginator_config);

        $breadcrumbs = array(
			array(
				'link' 	=> __SITE_URL . 'about',
				'title'	=> translate('about_us_nav_about_us', null, true)
			),
			array(
				'link' 	=> 'about/in_the_news',
				'title'	=> translate('about_us_nav_in_the_news', null, true)
            ),
			array(
				'link' 	=> '',
				'title'	=> translate('about_us_in_the_news_newsletter_archive_tab_title', null, true)
			),
		);

        $data['pagination'] = $this->pagination->create_links();
        $data['nav_active'] = 'in the news';
        $data['header_out_content'] = 'new/about/in_the_news/header_view';
        $data['main_content'] = 'new/newsletter/index_view';
        $data['header_title'] = translate('about_us_in_the_news_newsletter_archive_header_title');
        $data['header_img'] = 'newsletter_archive_header.jpg';
        $data['breadcrumbs'] = $breadcrumbs;

        $this->view->assign($data);
        $this->view->display('new/index_template_view');
    }

    public function ajax_load_archive() {

        $newsArchiveModel = model(Ep_News_Archive_Model::class);

        checkIsAjax();

        $id = request()->request->getInt('id');
        $type = request()->request->getInt('type');

        $singleRecord = $newsArchiveModel->find($id);

        if (empty($singleRecord)) {
            jsonResponse(translate('no_newsletter_archives'), 'error');
        }

        $records = $newsArchiveModel->getPreviousOrNextRecords($singleRecord['published_on'], $type);

        $templatePath  = "public/newsletter_archive/{$records[0]['id_archive']}/index.html";
        $completePath  = "{$this->uri->hostname()}/{$templatePath}";
        $dummyPath     = "{$this->uri->hostname()}/public/newsletter_archive/no_template.html";

        jsonResponse(!file_exists($templatePath) ? $dummyPath : $completePath, 'success', [
                "id"   => $records[0]['id_archive'],
                "last" => empty($records[1]) ? true: false
            ]
        );
    }

    public function archive()
    {
        $id = (int) uri()->segment(3);
        $templatePath = "public/newsletter_archive/{$id}/index.html";
        $completePath = "{$this->uri->hostname()}/{$templatePath}";

        if (!file_exists($templatePath)) {
            show_404();
        }

        views('new/newsletter/template', ["archive_path" => $completePath, "archive_id" => $id]);
    }

    public function archive_administration()
    {
        checkAdmin('ep_news_administration');

        views(array('admin/header_view', 'new/newsletter/admin/archive_view', 'admin/footer_view'));
    }

    public function ajax_ep_news_operations() {
        checkIsAjax();
        checkIsLoggedAjax();
        checkAdminAjax('ep_news_administration');

        switch ($this->uri->segment(3)) {
            case 'ep_news_archive':
                checkIsAjax();
                checkAdminAjaxDT('ep_news_administration');

                $params = array(
                    'limit'     => intval(request()->get('iDisplayLength'), null),
                    'offset'    => intval(request()->get('iDisplayStart'), null),
                    'sort_by'   => flat_dt_ordering($_POST, array(
                        'dt_id_archive' => 'id_archive'
                    ))
                );

                $ep_news = model('ep_news_archive')->get_news_archive($params);
                $ep_news_count = model('ep_news_archive')->get_count_news_archive($params);

                $output = array(
                    "sEcho"                 => intval(request()->get('sEcho'), null),
                    "iTotalRecords"         => $ep_news_count,
                    "iTotalDisplayRecords"  => $ep_news_count,
                    'aaData'                => array()
                );

                if(empty($ep_news)) {
                    jsonResponse('', 'success', $output);
                }

                $edit_prefix_url = __SITE_URL . 'newsletter/popup_forms/edit_news_archive/';
                foreach ($ep_news as $one_news) {
                    $edit_url = $edit_prefix_url . $one_news['id_archive'];
                    $id_archive = $one_news['id_archive'];
                    $actions = "<a class=\"ep-icon ep-icon_pencil fancyboxValidateModalDT fancybox.ajax\"
                            title=\"Edit News archive\"
                            href=\"$edit_url\"
                            data-title=\"Edit newsletter archive\">
                        </a>
                        <a class=\"ep-icon ep-icon_remove txt-red confirm-dialog\"
                            data-callback=\"delete_news\"
                            data-message=\"Are you sure want delete this news?\"
                            title=\"Delete newsletter archive\"
                            data-news=\"$id_archive\">
                        </a>";
                    $title = '
                        <a class="news-block__title" target="_blank" href="'. get_dynamic_url("newsletter/archive/".$one_news["id_archive"], __SITE_URL, true) .'">
                            ' . $one_news['title'] . '
                        </a>
                    ';
                    $output["aaData"][] = array(
                        'dt_id_archive'     => $one_news['id_archive'],
                        'dt_title'          => $title,
                        'dt_description'    => $one_news['description'],
                        'dt_actions'        => $actions
                    );
                }

                jsonResponse("", "success", $output);
            break;
            case 'add_news_archive':
                $this->validator->set_rules(array(
                    array(
                        'field' => 'title',
                        'label' => 'Title',
                        'rules' => array('required' => '', 'max_len[200]' => '')
                    ),
                    array(
                        'field' => 'description',
                        'label' => 'Description',
                        'rules' => array('required' => '', 'max_len[500]' => '')
                    ),
                    array(
                        'field' => 'file_news_archive',
                        'label' => 'Archive with template',
                        'rules' => array('required' => '', 'max_len[500]' => '')
                    ),
                    array(
                        'field' => 'published_on',
                        'label' => 'Published on',
                        'rules' => array('required' => '', 'valid_date[m/d/Y]' => '')
                    )
                ));
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                if (!file_exists($file_news_archive = request()->get('file_news_archive'))) {
                    jsonResponse('Error: File path is not correct');
                }

                $published_timestamp = request()->get('published_on') . " " . date("h:i:s");

                $insert = array(
                    'title'         => cleanInput(request()->get('title')),
                    'description'   => cleanInput(request()->get('description')),
                    'published_on'  => getDateFormat($published_timestamp, 'm/d/Y H:i:s', 'Y-m-d H:i:s')
                );

                $id_archive = model('ep_news_archive')->insert($insert);
                if (!$id_archive) {
                    jsonResponse('Error: You cannot add this EP news archive now. Please try again later.');
                }

                $newsletter_archives_path = 'public/newsletter_archive';
                $archive_path = "{$newsletter_archives_path}/{$id_archive}";
                $zip = new ZipArchive;
                $res = $zip->open($file_news_archive);

                if ($res === true) {
                    $zip->extractTo($archive_path);
                    $zip->close();
                } else {
                    jsonResponse('Error: Invalid archive');
                }

                $replaced_index = preg_replace(
                    '/{BASE_URL}/',
                    __SITE_URL . $archive_path . '/',
                    file_get_contents("{$archive_path}/index.html")
                );
                file_put_contents("{$archive_path}/index.html", $replaced_index);

                jsonResponse("The EP news archive has been successfully added.", "success");


            break;
            case 'edit_news_archive':
                $this->validator->set_rules(array(
                    array(
                        'field' => 'news_archive',
                        'label' => 'Id archive',
                        'rules' => array('required' => '', 'integer' => '')
                    ),
                    array(
                        'field' => 'title',
                        'label' => 'Title',
                        'rules' => array('required' => '', 'max_len[200]' => '')
                    ),
                    array(
                        'field' => 'description',
                        'label' => 'Description',
                        'rules' => array('required' => '', 'max_len[500]' => '')
                    ),
                    array(
                        'field' => 'published_on',
                        'label' => 'Published on',
                        'rules' => array('required' => '', 'valid_date[m/d/Y]' => '')
                    )
                ));
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $id_archive = intval(request()->get('news_archive'));
                if (!model('ep_news_archive')->check_exist_archive($id_archive)) {
                    jsonResponse('News archive not found');
                }

                $published_timestamp = request()->get('published_on') . " " . date("h:i:s");

                $update = array(
                    'title'         => cleanInput(request()->get('title')),
                    'description'   => cleanInput(request()->get('description')),
                    'published_on'  => getDateFormat($published_timestamp, 'm/d/Y H:i:s', 'Y-m-d H:i:s')
                );

                if (model('ep_news_archive')->update($id_archive, $update)) {
                    jsonResponse("The EP news archive has been successfully changed.", "success");
                }

                jsonResponse('Error: You cannot add this EP news archive now. Please try again later.');
            break;
            case 'delete_news_archive':
                $id_archive = intval(request()->get('news'));

                if (!model('ep_news_archive')->delete($id_archive)) {
                    jsonResponse('Error: You cannot remove this EP news archive now. Please try again later.');
                }

                $newsletter_archive_path = 'public/newsletter_archive';
                remove_dir("{$newsletter_archive_path}/{$id_archive}");
                jsonResponse('The EP news archive has been successfully removed.', 'success');
            break;
        }
    }

    public function popup_forms()
    {
        checkIsAjax();
        checkIsLoggedAjax();
        checkAdminAjaxModal('ep_news_administration');

        switch ($this->uri->segment(3)) {
            case 'add_news_archive':
                $data = array(
                    'upload_folder'          => encriptedFolderName(),
                    'max_document_file_size' => config('fileupload_max_document_file_size', 1024 * 1024 * 2)
                );

                $this->view->display('new/newsletter/admin/popup_news_archive_form_view', $data);
            break;
            case 'edit_news_archive':
                $id_archive = (int) $this->uri->segment(4);
                $data['news_archive'] = model('ep_news_archive')->get_one($id_archive);

                $this->view->display('new/newsletter/admin/popup_news_archive_form_view', $data);
            break;
        }
    }

    public function ajax_upload_news_archive()
    {
        checkIsAjax();
        checkAdminAjax('ep_news_administration');

        if (empty($_FILES['files'])) {
            jsonResponse('Error: Please select file to upload.');
        }
        if (count($_FILES['files']['name']) > 1) {
            jsonResponse('Error: You cannot upload more than 1 document(s).');
        }

        $upload_folder = checkEncriptedFolder($this->uri->segment(3));

        if (!$upload_folder) {
            jsonResponse('Error: File upload path is not correct.');
        }
        $session_id = id_session();
        $path = "temp/newsletter_archive/{$session_id}/{$upload_folder}";
        create_dir($path);

        $result = array();
        $files = $this->upload->upload_files_new(array(
            'data_files' => $_FILES['files'],
            'path'       => $path,
            'rules'      => array(
                'size'   => 1024 * 1024 * 5,
                'format' => 'zip',
            ),
        ));

        if (!empty($files['errors'])) {
            jsonResponse($files['errors']);
        }

        foreach ($files as $file) {
            $file_path = "{$path}/{$file['new_name']}";

            $zip = new ZipArchive;
            if (true === $zip->open($file_path)) {
                $index_exists = false;
                for ($i = 0; $i < $zip->count(); $i++) {
                    $stat_file = $zip->statIndex($i);
                    if (0 !== $stat_file['size'] && !preg_match('/\.(?:jpg|jpeg|gif|png|html)/', $stat_file['name'])) {
                        jsonResponse("Error: Invalid file in archive. {$zip->getNameIndex($i)}");
                    }
                    if ('index.html' === $zip->getNameIndex($i)) {
                        $index_exists = true;
                    }
                }
                $zip->close();

                if (!$index_exists) {
                    jsonResponse("Error: File index.html not found");
                }
            } else {
                jsonResponse('Error: Invalid archive');
            }

            $result['files'][] = array(
                'path' => $file_path,
                'name' => $file['new_name'],
                'type' => $file['type'],
            );
        }

        jsonResponse('', 'success', $result);
    }
}

/* End of file newsletter.php */
/* Location: /tinymvc/myapp/controllers/newsletter.php */
