<?php

use App\Common\Validation\ConstraintViolationInterface;
use App\Common\Validation\ValidationException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Common\DependencyInjection\ServiceLocator\LibraryLocator;
use App\Filesystem\CategoryArticlesFilePathGenerator;
use App\Filesystem\CountryArticlesFilePathGenerator;
use App\Filesystem\CountryArticlesI18nFilePathGenerator;
use App\Filesystem\FilePathGenerator;
use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;
use ExportPortal\Contracts\Filesystem\Exception\ReadException;
use League\Flysystem\UnableToDeleteDirectory;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToWriteFile;
use TinyMVC_Library_Image_intervention as LegacyImageHandler;

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [15.12.2021]
 * Controller Refactoring
 */
class Country_articles_Controller extends TinyMVC_Controller {

    function index() {
        header('location: ' . __SITE_URL);
    }

    private function _load_main() {
        $this->load->model('country_articles_Model', 'country_articles');
        $this->load->model('Items_Model', 'items');
        $this->load->model('User_Model', 'user');
    }

    public function administration() {
        checkAdmin('manage_content');

        $this->load->model('Country_Model', 'country');

        $data['port_country'] = $this->country->fetch_port_country();

        $this->_load_main();

        $this->view->assign($data);
        $this->view->assign('title', 'Country articles');
        $this->view->display('admin/header_view');
        $this->view->display('admin/country_articles/index_view');
        $this->view->display('admin/footer_view');
    }

    function ajax_country_articles_administration() {
        if (!isAjaxRequest())
            headerRedirect();

        if (!logged_in())
            jsonResponse(translate("systmess_error_should_be_logged"));

        $this->_load_main();

        $params = array_merge(
            [
                'per_p' => intVal($_POST['iDisplayLength']),
                'start' => intVal($_POST['iDisplayStart']),
                'sort_by' => flat_dt_ordering($_POST, [
                    'dt_id_country_article' => 'ca.id',
                    'dt_country' => 'pc.country',
                    'dt_type' => 'ca.type',
                    'dt_visible' => 'ca.visible'
                ])
            ],
            dtConditions($_POST, [
                ['as' => 'country', 'key' => 'country', 'type' => 'cleanInput'],
                ['as' => 'type', 'key' => 'type_blog', 'type' => 'cleanInput'],
                ['as' => 'visible', 'key' => 'visible_blog', 'type' => 'int'],
                ['as' => 'keywords', 'key' => 'keywords', 'type' => 'cleanInput']
            ])
        );

        $country_articles = $this->country_articles->get_articles($params);
        $country_articles_count = $this->country_articles->counter_by_conditions($params);

        $output = array(
            "sEcho" => intval($_POST['sEcho']),
            "iTotalRecords" => $country_articles_count,
            "iTotalDisplayRecords" => $country_articles_count,
			'aaData' => array()
        );

		if(empty($country_articles))
			jsonResponse('', 'success', $output);

		foreach ($country_articles as $article) {

			$visible_btn = 'ep-icon_visible';
			if (!$article['visible'])
				$visible_btn = 'ep-icon_invisible';

			$article_dots = "";
			if (strlen($article['text']) > 70)
				$article_dots = '<p class="tac"><a class="btn-article-more ep-icon ep-icon_arrows-down" href="#" title="view more"></a></p>';

			$article_type = "";
			if ($article['type']) {
				$article_type = '<div class="tal">'
							. '<a class="ep-icon ep-icon_filter txt-green dt_filter" data-title="Type" title="Filter by type" data-value-text="Import" data-value="1" data-name="type_article"></a>'
						. '</div>'
						. 'Import';
			} else {
				$article_type = '<div class="tal">'
							. '<a class="ep-icon ep-icon_filter txt-green dt_filter" data-title="Type" title="Filter by type" data-value-text="Export" data-value="0" data-name="type_article"></a>'
						. '</div>'
						. 'Export';
			}

            $langs = array();
            $langs_record = array_filter(json_decode($article['translations_data'], true));
            $langs_record_list = array('English');
            if(!empty($langs_record)){
                foreach ($langs_record as $lang_key => $lang_record) {
                    if($lang_key == 'en'){
                        continue;
                    }

                    $langs[] = '<li>
                                    <div class="flex-display">
                                        <span class="display-ib_i lh-30 pl-5 pr-10 text-nowrap mw-150">'.$lang_record['lang_name'].'</span>
                                        <a class="display-ib_i lh-30 mb-0 p-0 ep-icon ep-icon_remove txt-red pull-right confirm-dialog" data-callback="delete_country_article_i18n" data-country_article="' . $article['id'] . '" data-lang="'.$lang_record['abbr_iso2'].'" title="Delete" data-message="Are you sure you want to delete the translation?" href="#" ></a>
                                        <a href="'.__SITE_URL.'country_articles/popup_forms/edit_article_i18n/' . $article['id'] . '/' . $lang_record['abbr_iso2'].'" data-title="Edit article translation" title="Edit" class="display-ib_i lh-30 mb-0 p-0 ep-icon ep-icon_pencil fancyboxValidateModalDT fancybox.ajax pull-right"></a>
                                    </div>
                                </li>';
                    $langs_record_list[] = $lang_record['lang_name'];
                }
                $langs[] = '<li role="separator" class="divider"></li>';
            }

            $langs_dropdown = '<div class="dropdown">
                                <a class="ep-icon ep-icon_globe-circle m-0 fs-24 dropdown-toggle" data-toggle="dropdown"></a>
                                <ul class="dropdown-menu">
                                    '.implode('', $langs).'
                                    <li><a href="' . __SITE_URL . 'country_articles/popup_forms/add_article_i18n/' . $article['id'] . '" data-title="Add translation" title="Add translation" class="fancyboxValidateModalDT fancybox.ajax">Add translation</a></li>
                                </ul>
                            </div>';
            /** @var FilesystemProviderInterface $storageProvider */
            $provider = $this->getContainer()->get(FilesystemProviderInterface::class);
            $storage = $provider->storage('public.storage');
            $photoLink = $storage->url(CountryArticlesFilePathGenerator::mainImagePath($article['id'], $article['photo']));

			$output['aaData'][] = array(
				'dt_id_country_article' => $article['id'],
				'dt_country' =>
					'<div class="tal">'
						. '<a class="ep-icon ep-icon_filter txt-green dt_filter" data-title="Country" title="Filter by country" data-value-text="' . $article['country'] . '" data-value="' . $article['id_country'] . '" data-name="country"></a>'
					. '</div>
					<a href="/search/country/'.strForURL($article['country']).'-'.$article['id_country'].'?keywords='.strForURL($article['country']).'" target="_blank">'. $article['country'].'</a><br/>'
					. '<img width="24" height="24" src="' . getCountryFlag($article['country']) . '" alt="' . $article['country'] . '" title="' . $article['country'] . '" />',
				'dt_type' => $article_type,
				'dt_meta_data' =>
					'<span title="' . $article['meta_key'] . '">Keywords</span> | ' . '<span title="' . $article['meta_desc'] . '">Description</span>',
				'dt_photo' => "<img class=\"mw-100\" src=\"$photoLink\" alt=\"photo\" />",
				'dt_text' => '<div class="h-50 hidden-b">' . $article['text'] . '</div>' . $article_dots,
				'dt_actions' =>
					'<a class="ep-icon ' . $visible_btn . ' confirm-dialog" data-callback="change_visible_article" data-article="' . $article['id'] . '" data-message="Are you sure you want to change the visibility status of this article?" href="#" title="Set article ' . (($article['visible']) ? 'active' : 'inactive') . '"></a>'
					. '<a class="fancyboxValidateModalDT fancybox.ajax ep-icon ep-icon_pencil" href="country_articles/popup_forms/edit_article/' . $article['id'] . '" data-title="Edit article" title="Edit this article" data-table="dtBlogs"></a>'
					. '<a class="ep-icon ep-icon_remove txt-red confirm-dialog" data-callback="remove_article" data-article="' . $article['id'] . '" title="Remove this article" data-message="Are you sure you want to delete this article?" href="#" ></a>',
                'dt_tlangs' => $langs_dropdown,
                'dt_tlangs_list' => implode(', ', $langs_record_list)
			);
		}

        jsonResponse('', 'success', $output);
    }

    public function popup_forms() {
        if (!isAjaxRequest())
            headerRedirect();

        if (!logged_in())
            messageInModal(translate("systmess_error_should_be_logged"), $type = 'errors');

        if (!have_right('manage_content'))
            messageInModal(translate("systmess_error_rights_perform_this_action"));


        $this->_load_main();
        $data['errors'] = array();
        $id_user = $this->session->id;

        $op = $this->uri->segment(3);
        switch ($op) {
            case 'add_article':
                /** @var Country_Model $countryModel */
                $countryModel = model(Country_Model::class);

                views(
                    'admin/country_articles/add_article_form_view',
                    [
                        'port_country'              => $countryModel->fetch_port_country(),
                        'fileupload_total'          => config('img.category_articles.main.limit'),
                        'fileupload_max_file_size'  => config('fileupload_max_file_size'),
                    ],
                );
            break;
            case 'edit_article':
                /** @var Country_Articles_Model $countryArticlesModel */
                $countryArticlesModel = model(Country_Articles_Model::class);

                if (empty($articleId = (int) uri()->segment(4)) || empty($article = $countryArticlesModel->get_article($articleId))) {
                    messageInModal(translate('systmess_error_invalid_data'));
                }

                /** @var Country_Model $countryModel */
                $countryModel = model(Country_Model::class);

                $article['photoLink'] = getDisplayImageLink(
                    [
                        '{ARTICLE_ID}'  => $articleId,
                        '{FILE_NAME}'   => $article['photo']
                    ],
                    'country_articles.main',
                );
                views(
                    'admin/country_articles/edit_article_form_view',
                    [
                        'fileupload_max_file_size'  => config('fileupload_max_file_size'),
                        'fileupload_total'          => config('img.category_articles.main.limit'),
                        'article_info'              => $article,
                        'port_country'              => $countryModel->fetch_port_country(),
                    ],
                );
            break;
            case "add_article_i18n":
                /** @var Country_Articles_Model $countryArticlesModel */
                $countryArticlesModel = model(Country_Articles_Model::class);

                $articleId = (int) uri()->segment(4);
                if (empty($articleId) || empty($article = $countryArticlesModel->get_article($articleId))) {
                    messageInModal(translate('systmess_error_invalid_data'));
                }

                /** @var Country_Model $countryModel */
                $countryModel = model(Country_Model::class);

                views(
                    'admin/country_articles/form_i18n_view',
                    [
                        'fileupload_max_file_size'  => config('fileupload_max_file_size'),
                        'port_country_name'         => $countryModel->fetch_port_country($article['id_country'])[0]['country_name'],
                        'fileupload_total'          => config('img.category_articles.main.limit'),
                        'tlanguages'                => $this->translations->get_languages(),
                        'article'                   => $article,
                    ],
                );
            break;
            case "edit_article_i18n":
                if (empty($lang = uri()->segment(5))) {
                    messageInModal('Lang is not setted.');
                }

                /** @var Country_Article_Translated_Model $translatedArticleRepository */
                $translatedArticleRepository = model(Country_Article_Translated_Model::class);
                $articleId = (int) uri()->segment(4);
                $tranlsatedArticle = $translatedArticleRepository->findOneBy([
                    'scopes' => [
                        'idMainArticle' => $articleId,
                        'language'      => $lang
                    ]
                ]);
                if(null == $tranlsatedArticle){
                    messageInModal('Error: The article translation does not exist.');
                }
                /** @var FilesystemProviderInterface $storageProvider */
                $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                $storage = $storageProvider->storage('public.storage');
                $imageLink = CountryArticlesI18nFilePathGenerator::mainImagePath($tranlsatedArticle['id_article_i18n'], $tranlsatedArticle['photo']);
                $tranlsatedArticle['photoLink'] = $storage->url($imageLink);

                /** @var Country_Articles_Model $countryArticlesModel */
                $countryArticlesModel = model(Country_Articles_Model::class);

                $articleId = (int) uri()->segment(4);
                if (empty($articleId) || empty($article = $countryArticlesModel->get_article($articleId))) {
                    messageInModal('Could not find the article.');
                }

                /** @var Country_Model $countryModel */
                $countryModel = model(Country_Model::class);

                views(
                    'admin/country_articles/form_i18n_view',
                    [
                        'fileupload_max_file_size'  => config('fileupload_max_file_size'),
                        'fileupload_total'          => config('img.category_articles.main.limit'),
                        'port_country_name'         => $countryModel->fetch_port_country($article['id_country'])[0]['country_name'],
                        'article_i18n'              => $tranlsatedArticle,
                        'tlanguages'                => $this->translations->get_languages(),
                        'article'                   => $article,
                    ],
                );

            break;
        }
    }

    public function ajax_upload_temp_photo()
    {
        checkIsLoggedAjax();

        $request = request();
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('files')[0];
        if (null === $uploadedFile) {
			jsonResponse(translate('validation_image_required'));
		}
        if (is_array($uploadedFile) || !$uploadedFile->isValid()) {
			jsonResponse(translate('validation_invalid_file_provided'));
		}
        $config = 'img.country_articles.main.rules';
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
        //get the filesystem provider
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
            'image' => ['path' => $pathToFile, 'name' => $fileName, 'fullPath' => $tempDisk->url($pathToFile)]
        ]);
    }

    public function ajax_articles_operation() {
        if (!isAjaxRequest())
            headerRedirect();

        if (!logged_in())
            jsonResponse(translate("systmess_error_should_be_logged"));

        $this->_load_main();
        $this->load->model('Notify_Model', 'notify');
        $id_user = $this->session->id;
        $op = $this->uri->segment(3);

        switch ($op) {
            case 'delete_country_article_i18n':
				$id_article = intval($_POST['country_article']);
                $article= $this->country_articles->get_article($id_article);
                if (empty($article))
                    jsonResponse('Error: This article does not exist.');

				$lang_article = cleanInput($_POST['lang']);
				$article_i18n = $this->country_articles->get_article_i18n(array('id_article' => $id_article, 'lang_article' => $lang_article));
				if(empty($article_i18n)){
					jsonResponse('Error: The article translation does not exist.');
				}

				$translations_data = json_decode($article['translations_data'], true);
				unset($translations_data[$lang_article]);
				$this->country_articles->update_article($id_article, array('translations_data' => json_encode($translations_data)));
				$this->country_articles->delete_article_i18n($article_i18n['id_article_i18n']);

                if(!empty($article_i18n)){
                    $this->deleteDirectoryImage(CountryArticlesI18nFilePathGenerator::mainImageFolder($article_i18n['id_article_i18n']));
                }

				jsonResponse('The article translation has been successfully deleted.', 'success');

            break;
            case 'change_visible_article':
                $id_article = intVal($_POST['article']);
                $article_info = $this->country_articles->get_article($id_article);

                if (empty($article_info))
                    jsonResponse('Error: This article does not exist.');

                $update = array();
                if ($article_info['visible'])
                    $update['visible'] = 0;
                else
                    $update['visible'] = 1;

                if ($this->country_articles->update_article($id_article, $update))
                    jsonResponse('The article has been successfully changed.', 'success');
                else
                    jsonResponse('Error: You cannot change this article now. Please try again later.');
                break;
            case 'remove_article':
                $id_article = intVal($_POST['article']);
                $article_info = $this->country_articles->get_article($id_article);
                if (empty($article_info))
                    jsonResponse('This article does not exist.');

                if ($this->country_articles->delete_article($id_article)){
                    if(!empty($article_info)){
                        $this->deleteDirectoryImage(CountryArticlesFilePathGenerator::mainImageFolder($id_article));
                    }
                    jsonResponse('The article has been successfully removed.', 'success');
                } else {
                    jsonResponse('Error: You cannot remove this article now. Please try again later.');
                }
                break;
            case 'edit_article':
                $validator_rules = array(
                    array(
                        'field' => 'country',
                        'label' => 'Country',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'type',
                        'label' => 'Type',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'meta_key',
                        'label' => 'Meta keywords',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'meta_desc',
                        'label' => 'Meta descripions',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'text',
                        'label' => 'Text',
                        'rules' => array('required' => '')
                    )
                );

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                /** @var Country_Articles_Model $countryArticlesModel */
                $countryArticlesModel = model(Country_Articles_Model::class);

                $request = request()->request;
                $articleId = $request->getInt('article');
                $type = $request->getInt('type');
                $countryId = $request->getInt('country');

                if ($countryArticlesModel->exist_article_by_condition(['not_id_article' => $articleId, 'type' => $type, 'country' => $countryId])) {
                    jsonResponse('Error: The article for this country and type already exists. Please edit the existing article.');
                }

                $update = [
                    'id_country'    => $countryId,
                    'meta_desc'     => cleanInput($request->get('meta_desc')),
                    'meta_key'      => cleanInput($request->get('meta_key')),
                    'visible'       => empty($request->get('visible')) ? 0 : 1,
                    'type'          => $type,
                    'text'          => $request->get('text'),
                ];

                if (!$countryArticlesModel->update_article($articleId, $update)) {
                    jsonResponse('You cannot change this article now. Please try again later.');
                }

                if (!empty($image = request()->request->get('image'))) {
                    $this->saveImage($articleId, $image);
                }

                jsonResponse('The article has been successfully changed.', 'success');

                break;
            case "edit_article_i18n":
                $validator_rules = array(
                    array(
                        "field" => "country",
                        "label" => "Country",
                        "rules" => array("required" => "")
                    ),
                    array(
                        "field" => "type",
                        "label" => "Type",
                        "rules" => array("required" => "")
                    ),
                    array(
                        "field" => "meta_key",
                        "label" => "Meta keywords",
                        "rules" => array("required" => "")
                    ),
                    array(
                        "field" => "meta_desc",
                        "label" => "Meta descripions",
                        "rules" => array("required" => "")
                    ),
                    array(
                        "field" => "text",
                        "label" => "Text",
                        "rules" => array("required" => "")
                    ),
                    array(
                        "field" => "lang_article",
                        "label" => "Language",
                        "rules" => array("required" => "")
                    ),
                );

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                /** @var Country_Articles_Model $countryArticlesModel */
                $countryArticlesModel = model(Country_Articles_Model::class);

                if (empty($articleId = uri()->segment(4)) || empty($article = $countryArticlesModel->get_article($articleId))) {
                    jsonResponse("Could not find the article");
                }

                $request = request()->request;
                if (empty($articleLang = $request->get('lang_article')) || empty($tlang = $this->translations->get_language_by_iso2($articleLang))) {
                    jsonResponse("Language does not exist.");
                }

                $translations = json_decode($article["translations_data"], true);
                if (empty($translations[$articleLang])){
                    jsonResponse("Article translation for this language could not be found.");
                }

                if (empty($articleI18n = $countryArticlesModel->get_article_i18n(['id_article' => $articleId, 'lang' => $articleLang]))) {
                    jsonResponse("Article translation for this language could not be found.");
                }

                $update = [
                    "id_article"    => $articleId,
                    "meta_key"      => cleanInput($request->get('meta_key')),
                    "meta_desc"     => cleanInput($request->get('meta_desc')),
                    "text"          => $request->get('text'),
                ];

                if ($countryArticlesModel->update_article_i18n($articleI18n["id_article_i18n"], $update)) {
                    if (!empty($image = request()->request->get('image'))) {
                        $this->saveImageI18n($articleI18n["id_article_i18n"], $image);
                    }
                    jsonResponse("The translation has been successfully edited", "success");
                }

                jsonResponse('Cannot edit translation now. Please try later.');
                break;
            case 'save_article_i18n':
                $validator_rules = array(
                    array(
                        'field' => 'country',
                        'label' => 'Country',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'type',
                        'label' => 'Type',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'meta_key',
                        'label' => 'Meta keywords',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'meta_desc',
                        'label' => 'Meta descripions',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'text',
                        'label' => 'Text',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'image',
                        'label' => 'Image',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'lang_article',
                        'label' => 'Language',
                        'rules' => array('required' => '')
                    ),
                );

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                /** @var Country_Articles_Model $countryArticlesModel */
                $countryArticlesModel = model(Country_Articles_Model::class);

                if (empty($articleId = (int) uri()->segment(4)) || empty($article = $countryArticlesModel->get_article($articleId))) {
                    jsonResponse('Could not find the article.');
                }

                $request = request()->request;
                if (empty($articleLang = $request->get('lang_article')) || empty($tlang = $this->translations->get_language_by_iso2($articleLang))) {
                    jsonResponse('Language does not exist.');
                }

                $translations = json_decode($article['translations_data'], true);
                if (array_key_exists($articleLang, $translations)) {
                    jsonResponse('Article translation for this language already exist.');
                }

                $translations[$articleLang] = [
                    'lang_name' => $tlang['lang_name'],
                    'abbr_iso2' => $tlang['lang_iso2']
                ];

                if (!$translationArticleId = $countryArticlesModel->set_article_i18n([
                    'lang_article'  => $articleLang,
                    'id_article'    => $articleId,
                    'meta_desc'     => cleanInput($request->get('meta_desc')),
                    'meta_key'      => cleanInput($request->get('meta_key')),
                    'text'          => $request->get('text'),
                ])) {
                    jsonResponse('Cannot add translation now. Please try later.');
                }

                if (!empty($image = request()->request->get('image'))) {
                    $this->saveImageI18n($translationArticleId, $image);
                }

                $countryArticlesModel->update_article($articleId, ['translations_data' => json_encode($translations)]);

                jsonResponse('The translation has been successfully added', 'success');

                break;
            case 'save_article':
                $validator_rules = array(
                    array(
                        'field' => 'country',
                        'label' => 'Country',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'type',
                        'label' => 'Type',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'meta_key',
                        'label' => 'Meta keywords',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'meta_desc',
                        'label' => 'Meta descripions',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'text',
                        'label' => 'Text',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'image',
                        'label' => 'Image',
                        'rules' => array('required' => '')
                    ),
                );
                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                /** @var Country_Articles_Model $countryArticlesModel */
                $countryArticlesModel = model(Country_Articles_Model::class);

                $request = request()->request;
                $type = $request->getInt('type');
                $countryId = $request->getInt('country');

                if ($countryArticlesModel->exist_article_by_condition(['type' => $type, 'country' => $countryId])) {
                    jsonResponse('The article for this country and type already exists. Please edit the existing article.');
                }

                if (!$articleId = $countryArticlesModel->set_article([
                    'id_country'    => $countryId,
                    'type'          => $type,
                    'meta_key'      => cleanInput($request->get('meta_key')),
                    'meta_desc'     => cleanInput($request->get('meta_desc')),
                    'text'          => $request->get('text'),
                    'visible'       => empty($request->get('visible')) ? 0 : 1,
                ])) {
                    jsonResponse('You cannot add articles now. Please try again later.');
                }

                if (!empty($image = request()->request->get('image'))) {
                    $this->saveImage($articleId, $image);
                }
                jsonResponse('The article has been successfully changed.', 'success');

                break;
        }
    }

    public function ajax_article_delete_db_photo()
    {
        checkIsAjax();
        checkIsLoggedAjax();

        $countryArticleId = request()->request->getInt('file');
		if (empty($countryArticleId)) {
            jsonResponse('Error: File name is not correct.');
        }
        /** @var Country_Article_Model $articleRepository */
        $articleRepository = model(Country_Article_Model::class);

        if($countryArticle = $articleRepository->findOne($countryArticleId)){
            /** @var FilesystemProviderInterface  $storageProvider */
            $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
            $publicDisk = $storageProvider->storage('public.storage');
            //delete file from database
            $articleRepository->updateOne($countryArticleId, ['photo' => '']);
            //delete file from disk
            try{
                $publicDisk->delete(CountryArticlesFilePathGenerator::mainImagePath($countryArticleId, $countryArticle['photo']));
            } catch (UnableToDeleteFile $e) {
                //silent fail
            }
        }

        jsonResponse("Article image was deleted.", 'success');
    }

    public function ajax_article_delete_db_photo_i18n()
    {
        checkIsAjax();
        checkIsLoggedAjax();

        $countryArticleId = request()->request->getInt('file');
		if (empty($countryArticleId)) {
            jsonResponse('Error: File name is not correct.');
        }
        /** @var Country_Article_Translated_Model $translatedArticleRepository */
        $translatedArticleRepository = model(Country_Article_Translated_Model::class);

        if($countryArticle = $translatedArticleRepository->findOne($countryArticleId)){
            /** @var FilesystemProviderInterface  $storageProvider */
            $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
            $publicDisk = $storageProvider->storage('public.storage');
            //delete file from database
            $translatedArticleRepository->updateOne($countryArticleId, ['photo' => '']);
            //delete file from disk
            try{
                $publicDisk->delete(CountryArticlesI18nFilePathGenerator::mainImagePath($countryArticleId, $countryArticle['photo']));
            } catch (UnableToDeleteFile $e) {
                //silent fail
            }
        }

        jsonResponse("Article image was deleted.", 'success');
    }

    /**
     * Save image on disk and in database
     *
     * @param int $articleId - id of the article
     * @param string $mainImage - path to directory
     */
    private function saveImage(int $articleId, string $mainImage)
    {
        if(empty($articleId) || empty($mainImage)){
            return;
        }

        /** @var FilesystemProviderInterface  $storageProvider */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        //set the disks and prefixers
        $prefixerTemp = $storageProvider->prefixer('temp.storage');
        $prefixerPublic = $storageProvider->prefixer('public.storage');
        $publicDisk = $storageProvider->storage('public.storage');
        //create the folder
        $publicDisk->createDirectory(CountryArticlesFilePathGenerator::mainImageFolder($articleId));
        /** @var TinyMVC_Library_Image_intervention $interventionImageLibrary */
        $interventionImageLibrary = library(TinyMVC_Library_Image_intervention::class);
        $imageConfig = config("img.country_articles.main");
        $images = $interventionImageLibrary->image_processing(
            [
                'tmp_name' => $prefixerTemp->prefixPath($mainImage),
                'name'     => \basename($mainImage),
            ],
            [
                'destination'   => $prefixerPublic->prefixPath(CountryArticlesFilePathGenerator::mainImageFolder($articleId)),
                'rules'         => $imageConfig["rules"],
                'handlers'      => [
                    'resize'        => $imageConfig["resize"],
                ],
            ]
        );

        if (!empty($images['errors'])) {
            jsonResponse($images['errors']);
        }

        /** @var Country_Article_Model $articleRepository */
        $articleRepository = model(Country_Article_Model::class);
        //save the image in the database with the new name
        $articleRepository->updateOne($articleId, [
            'photo' => $images[0]['new_name']
        ]);
    }

    /**
     * Save image on disk and in database
     *
     * @param int $articleId - id of the article
     * @param string $mainImage - path to directory
     */
    private function saveImageI18n(int $articleId, string $mainImage)
    {
        if(empty($articleId) || empty($mainImage)){
            return;
        }

        /** @var FilesystemProviderInterface  $storageProvider */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        //set the disks prefixers
        $prefixerTemp = $storageProvider->prefixer('temp.storage');
        $prefixerPublic = $storageProvider->prefixer('public.storage');
        $publicDisk = $storageProvider->storage('public.storage');
        //create the folder
        $publicDisk->createDirectory(CountryArticlesI18nFilePathGenerator::mainImageFolder($articleId));
        /** @var TinyMVC_Library_Image_intervention $interventionImageLibrary */
        $interventionImageLibrary = library(TinyMVC_Library_Image_intervention::class);
        $imageConfig = config("img.country_articles_i18n.main");
        $images = $interventionImageLibrary->image_processing(
            [
                'tmp_name' => $prefixerTemp->prefixPath($mainImage),
                'name'     => \basename($mainImage),
            ],
            [
                'destination'   => $prefixerPublic->prefixPath(CountryArticlesI18nFilePathGenerator::mainImageFolder($articleId)),
                'rules'         => $imageConfig["rules"],
                'handlers'      => [
                    'resize'        => $imageConfig["resize"],
                ],
            ]
        );

        if (!empty($images['errors'])) {
            jsonResponse($images['errors']);
        }

        /** @var Country_Article_Translated_Model $articleRepository */
        $articleRepository = model(Country_Article_Translated_Model::class);
        //save the image in the database with the new name
        $articleRepository->updateOne($articleId, [
            'photo' => $images[0]['new_name']
        ]);
    }

    /**
     * Delete directory with files in it when deleting the article
     *
     * @param string $pathToDirectory - path to directory
     */
    private function deleteDirectoryImage(string $pathToDirectory): void
    {
        if(empty($pathToDirectory)){
            return;
        }
        /** @var FilesystemProviderInterface  $storageProvider */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $publicDisk = $storageProvider->storage('public.storage');

        try{
            $publicDisk->deleteDirectory($pathToDirectory);
        } catch (UnableToDeleteDirectory $e) {
            //silent fail
        }
    }
}
