<?php

use App\Common\Validation\ValidationException;
use App\Common\Validation\ConstraintViolationInterface;
use App\Common\DependencyInjection\ServiceLocator\LibraryLocator;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Filesystem\CategoryArticlesFilePathGenerator;
use App\Filesystem\CategoryArticlesI18nFilePathGenerator;
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
class Categories_Articles_Controller extends TinyMVC_Controller {

    public function administration() {
        checkPermision('items_categories_articles_administration');

		$this->load->model('Item_Category_Articles_Model', 'category_atr');

        $data['categories'] = $this->category_atr->get_item_categories(array('parent' => 0));

        $data['title'] = 'Categories Articles';
        $this->view->assign($data);
        $this->view->display('admin/header_view');
        $this->view->display('admin/categories_articles/index_view');
        $this->view->display('admin/footer_view');
    }

    function ajax_categories_articles_dt() {
        checkIsAjax();
        checkIsLoggedAjaxDT();
        checkPermisionAjaxDT('items_categories_articles_administration');

        $this->load->model('Category_Model', 'category');
        $this->load->model('Item_Category_Articles_Model', 'category_atr');

        $params = array_merge(
            array(
                'per_p' => intVal($_POST['iDisplayLength']),
                'start' => intVal($_POST['iDisplayStart']),
                'lang' => 'en',
                'order_by' => flat_dt_ordering($_POST, array(
                    'dt_id' => 'id',
                    'dt_category' => 'name_cat'
                ))
            ),
            dtConditions($_POST, array(
                array('as' => 'parent', 'key' => 'parent', 'type' => 'int'),
                array('as' => 'id_cat', 'key' => 'id_category', 'type' => 'int'),
                array('as' => 'visible', 'key' => 'visible', 'type' => 'int'),
            ))
        );

        $categories_atr = $this->category_atr->get_categories_atr($params);
		$count_categories_atr = $this->category_atr->get_count_categories_atr($params);

        $output = array(
            "sEcho" => intval($_POST['sEcho']),
            "iTotalRecords" => $count_categories_atr,
            "iTotalDisplayRecords" => $count_categories_atr,
            "aaData" => array()
        );

		if(empty($categories_atr)) {
			jsonResponse('', 'success', $output);
        }

        foreach ($categories_atr as $key => $category) {
            $cat_str = '<div>
                <a class="txt-bold" href="'.__SITE_URL.'category/'.strForURL($category['name_cat']).'/'.$category['id_cat'].'" title="' . $category['name_cat'] . '">' . $category['name_cat'] . '</a>
                <a href="#" class="dt_filter ep-icon ep-icon_filter txt-green" title="' . $category['name_cat'] . '" data-name="parent" data-title="Category" data-value="' . $category['id_cat'] . '" data-value-text="' . $category['name_cat'] . '"></a>
            </div>';

            $category['breadcrumbs'] = json_decode("[" . $category['breadcrumbs'] . "]", true);

            if(count($category['breadcrumbs']) > 1){
                $cat_str .= '<div class="breadcrumbs-b mt-5">';

                $out = array();
                foreach ($category['breadcrumbs'] as $bread){
                    foreach ($bread as $cat_id => $cat_title){
                        $out[] = '<a href="'.__SITE_URL.'category/'.strForURL($cat_title).'/'.$cat_id.'" title="' . $cat_title . '">' . $cat_title . '</a>
                            <a href="#" class="dt_filter ep-icon ep-icon_filter txt-green" data-name="parent" data-title="Category" data-value="' . $cat_id . '" data-value-text="' . $cat_title . '"></a>';
                    }
                }

                $cat_str .= implode('<span class="crumbs-delimiter fs-16 pr-5 pl-5">&raquo;</span>', $out);
            }

            $cat_str .= '</div>';

            $cats_dots = '';
            if (strlen($category['text']) > 70) {
                $cats_dots = '<p class="tac"><a class="btn-article-more ep-icon ep-icon_arrows-down fs-21" href="#" title="view more"></a></p>';
            }

			$visible_btn = '<a class="ep-icon ep-icon_visible confirm-dialog" data-callback="change_visible_cats_arts" data-cat-art="' . $category['id'] . '" data-message="Are you sure you want to change invisible this article?" href="#" title="Set article inactive"></a>';
			if(!$category['visib']) {
				$visible_btn = '<a class="ep-icon ep-icon_invisible confirm-dialog" data-callback="change_visible_cats_arts" data-cat-art="' . $category['id'] . '" data-message="Are you sure you want to change the visibility status of this article?" href="#" title="Set article active"></a>';
            }

            $langs = array();
            $langs_record = array_filter(json_decode($category['translations_data'], true));
            $langs_record_list = array('English');
            if(!empty($langs_record)){
                foreach ($langs_record as $lang_key => $lang_record) {
                    if($lang_key == 'en'){
                        continue;
                    }

                    $langs[] = '<li>
                                    <div class="flex-display">
                                        <span class="display-ib_i lh-30 pl-5 pr-10 txt-nowrap-simple flex--1">'.$lang_record['lang_name'].'</span>
                                        <a class="display-ib_i lh-30 mb-0 p-0 ep-icon ep-icon_remove txt-red pull-right confirm-dialog" data-callback="delete_item_category_article_i18n" data-item_category_article="' . $category['id'] . '" data-lang="'.$lang_record['abbr_iso2'].'" title="Delete" data-message="Are you sure you want to delete the translation?" href="#" ></a>
                                        <a href="'.__SITE_URL.'categories_articles/popup_forms/edit_item_category_article_i18n/'.$category['id'] . '/' . $lang_record['abbr_iso2'].'" data-title="Edit article translation" title="Edit" class="display-ib_i lh-30 mb-0 p-0 ep-icon ep-icon_pencil fancyboxValidateModalDT fancybox.ajax pull-right"></a>
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
                                    <li><a href="'.__SITE_URL.'categories_articles/popup_forms/add_item_category_article_i18n/'.$category['id'].'" data-title="Add translation" title="Add translation" class="fancyboxValidateModalDT fancybox.ajax">Add translation</a></li>
                                </ul>
                            </div>';

            $photoLink = getDisplayImageLink(
                [
                    '{ARTICLE_ID}'  => $category['id'],
                    '{FILE_NAME}'   => $category['photo']
                ],
                'category_articles.main',
            );
            $output['aaData'][] = array(
                'dt_id' => $category['id'],
                'dt_photo' => "<img class=\"mw-100\" src=\"$photoLink\" alt=\"category\"/>",
                'dt_category' => $cat_str,
                'dt_text' => '<div class="h-50 hidden-b">' . $category['text'] . '</div>' . $cats_dots,
                'dt_actions' =>
					$visible_btn
                	. '<a class="ep-icon ep-icon_pencil fancyboxValidateModalDT fancybox.ajax mr-5" href="categories_articles/popup_forms/edit_category_article/' . $category['id'] . '" title="Edit this category article" data-title="Edit category article"></a>'
					. '<a class="ep-icon ep-icon_remove txt-red confirm-dialog" data-callback="remove_cat_art" data-cat-art="' . $category['id'] . '" title="Remove this article" data-message="Are you sure you want to delete this article?" href="#" ></a>',
                'dt_tlangs' => $langs_dropdown,
                'dt_tlangs_list' => implode(', ', $langs_record_list)
            );
        }

        jsonResponse('', 'success', $output);
    }

    function ajax_categories_articles_operation() {
        checkIsAjax();
        checkIsLoggedAjax();
        checkPermisionAjax('items_categories_articles_administration');

        $this->load->model('Item_Category_Articles_Model', 'category_atr');

        $id_user = $this->session->id;
        $op = $this->uri->segment(3);
        switch ($op) {
			case 'get_filter_subcategories':
                $this->load->model('Item_Category_Articles_Model', 'category_atr');

				$data['cat'] = intval($_POST['cat']);
				$data['level'] = intval($_POST['level']) + 1;
                $data['categories'] = $this->category_atr->get_item_categories(array('parent' => $data['cat']));

				jsonResponse('', 'success', array('content' => $this->view->fetch("admin/categories_articles/category_select_view", $data)));
			break;
            case 'change_visible_category_article':
                $id = intVal($_POST['id_cat_art']);
                $cat_art_info = $this->category_atr->get_details_cat_art($id);

                if (empty($cat_art_info)) {
                    jsonResponse('Error: This article does not exist.');
                }

                $update = array();
                if ($cat_art_info['visible']) {
                    $update['visible'] = 0;
                } else {
                    $update['visible'] = 1;
                }

                if ($this->category_atr->update_cat_art($id, $update)) {
                    jsonResponse('The category article was updated successfully.', 'success');
                } else {
                    jsonResponse('Error: You cannot update category article now. Please try again later.');
                }
			break;
            case 'remove_category_article':
                $id = intVal($_POST['id_cat_art']);
                $cat_art_info = $this->category_atr->get_details_cat_art($id);

                if (empty($cat_art_info)) {
                    jsonResponse('Error: This article does not exist.');
                }

                if ($this->category_atr->delete_cat_art($id)){
                    $this->deleteDirectoryImage(CategoryArticlesFilePathGenerator::mainImageFolder($id));
                    jsonResponse('The category article was removed successfully.', 'success');
				} else{
                    jsonResponse('Error: you cannot remove category article now. Please try again later.');
				}
			break;
            case 'get_subcats':
                $this->load->model('Category_Model', 'category');
                $id = intVal($_POST['id_cat']);

                if ($data = $this->category->get_subcategories($id)) {
                    jsonResponse('', 'success', array('cats' => $data));
                } else {
                    jsonResponse('Info: This category does not have any subcategory', 'info');
                }
			break;
            case 'edit_category_article':
                $validator_rules = array(
                    array(
                        'field' => 'id',
                        'label' => 'Category article info',
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

                $id = intVal($_POST['id']);
                $update = array(
                    'text' => $_POST['text'],
                    'visible' => intVal($_POST['visible'])
                );
                $this->category_atr->update_cat_art($id, $update);
                if (!empty($mainImage = request()->request->get('image'))) {
                    $this->saveImage((int) $id, $mainImage);
                }

				jsonResponse('The article was saved successfully.', 'success');
			break;
            case 'save_category_article':
                $validator_rules = array(
                    array(
                        'field' => 'category',
                        'label' => 'Category',
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

                $id_cat = (int) end($_POST['category']);

                if ($this->category_atr->is_exist_cat_atr($id_cat)) {
                    jsonResponse('The article for this category already exists');
                }

                $insert = array(
                    'id_category' => $id_cat,
                    'text' => $_POST['text'],
                    'visible' => intVal($_POST['visible'])
                );
                $id = $this->category_atr->insert_cat_art($insert);
                if (!empty($mainImage = request()->request->get('image')) && !empty($id)) {
                    $this->saveImage((int) $id, $mainImage);
                }

				jsonResponse('The article was saved successfully.', 'success');
            break;
            case "delete_item_category_article_i18n":
				$id_category_article = intval($_POST['category_article']);
				$lang_category_article = cleanInput($_POST['lang']);
				$category_article_i18n = $this->category_atr->get_details_cat_art_i18n(array('id_article' => $id_category_article, 'lang_article' => $lang_category_article));
				if(empty($category_article_i18n)){
					jsonResponse('Error: The article translation does not exist.');
				}

                $category_article = $this->category_atr->get_details_cat_art($id_category_article);
				$translations_data = json_decode($category_article['translations_data'], true);
				unset($translations_data[$lang_category_article]);
				$this->category_atr->update_cat_art($id_category_article, array('translations_data' => json_encode($translations_data)));
				$this->category_atr->delete_cat_art_i18n($category_article_i18n['id_article_i18n']);

                //delete directory with the image
                if(!empty($category_article_i18n)){
                    $this->deleteDirectoryImage(CategoryArticlesI18nFilePathGenerator::mainImageFolder($category_article_i18n['id_article_i18n']));
                }

				jsonResponse('The article translation has been successfully deleted.', 'success');
            break;
        }
    }

    public function popup_forms() {
        checkIsAjax();
        checkIsLoggedAjaxModal();
        checkPermisionAjaxModal('items_categories_articles_administration');

        $id_user = $this->session->id;
        $op = $this->uri->segment(3);
        switch ($op) {
			case 'edit_item_category_article_i18n':
                /** @var Category_Translated_Article_Model $translatedArticleRepository */
                $translatedArticleRepository = model(Category_Translated_Article_Model::class);
				$articleId = (int) uri()->segment(4);
                $tranlsatedArticle = $translatedArticleRepository->findOneBy([
                    'scopes' => [
                        'idMainArticle' => $articleId,
                        'language'      => uri()->segment(5)
                    ]
                ]);
                if(null == $tranlsatedArticle){
                    messageInModal('Error: The article translation does not exist.');
                }
                /** @var FilesystemProviderInterface $storageProvider */
                $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                $storage = $storageProvider->storage('public.storage');
                $tranlsatedArticle['photoLink'] = CategoryArticlesI18nFilePathGenerator::mainImagePath($tranlsatedArticle['id_article_i18n'], $tranlsatedArticle['photo']);

                /** @var Item_Category_Articles_Model $itemCategoryArticlesModel */
                $itemCategoryArticlesModel = model(Item_Category_Articles_Model::class);
                views(
                    'admin/categories_articles/form_i18n_view',
                    [
                        'fileupload_max_file_size'  => config('fileupload_max_file_size'),
                        'fileupload_total'          => config('img.category_articles.main.limit'),
                        'article_i18n'              => $tranlsatedArticle,
                        'tlanguages'                => $this->translations->get_languages(),
                        'article'                   => $itemCategoryArticlesModel->get_details_cat_art($articleId),
                    ],
                );
            break;
			case 'add_item_category_article_i18n':
                /** @var Item_Category_Articles_Model $itemCategoryArticlesModel */
                $itemCategoryArticlesModel = model(Item_Category_Articles_Model::class);

                views(
                    'admin/categories_articles/form_i18n_view',
                    [
                        'article'                   => $itemCategoryArticlesModel->get_details_cat_art((int) uri()->segment(4)),
                        'tlanguages'                => $this->translations->get_languages(),
                        'fileupload_max_file_size'  => config('fileupload_max_file_size'),
                        'fileupload_total'          => config('img.category_articles.main.limit'),
                    ],
                );
            break;
            case 'add_category_article':
                /** @var Category_Model $categoryModel */
                $categoryModel = model(Category_Model::class);

                views(
                    'admin/categories_articles/categories_articles_form_view',
                    [
                        'fileupload_max_file_size'  => config('fileupload_max_file_size'),
                        'fileupload_total'          => config('img.category_articles.main.limit'),
                        'categories'                => $categoryModel->getCategories([
                            //'item_articles_only'    => true,
                            'columns'               => 'category_id as id, name as cat_name',
                            'parent'                => 0,
                        ])
                    ]
                );
			break;
            case 'edit_category_article':
                /** @var Item_Category_Articles_Model $itemCategoryArticlesModel */
                $itemCategoryArticlesModel = model(Item_Category_Articles_Model::class);

                /** @var Category_Model $categoryModel */
                $categoryModel = model(Category_Model::class);

                $articleId = uri()->segment(4);
                $article = $itemCategoryArticlesModel->get_details_cat_art($articleId);

                $categories = $categoryModel->getCategories(array('cat_list' => $article['id_category'], 'columns' => 'breadcrumbs'));
                $breadcrumbs = json_decode("[" . $categories[0]["breadcrumbs"] . "]", true);

                $articleBreadcrumbs = [];
                foreach ($breadcrumbs as $bread){
                    foreach ($bread as $categoryId => $categoryTitle){
                        $articleBreadcrumbs[] = $categoryTitle;
					}
				}
                //create the link to photo
                /** @var FilesystemProviderInterface $storageProvider */
                $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                $storage = $storageProvider->storage('public.storage');
                $article['photoLink'] = $storage->url(CategoryArticlesFilePathGenerator::mainImagePath($articleId, $article['photo']));

                views(
                    'admin/categories_articles/categories_articles_form_view',
                    [
                        'fileupload_max_file_size'  => config('fileupload_max_file_size'),
                        'fileupload_total'          => config('img.category_articles.main.limit'),
                        'breadcrumbs_str'           => implode("&raquo;", $articleBreadcrumbs),
                        'cat_art'                   => $article,
                    ],
                );
			break;
        }
    }

    function ajax_articles_add_i18n() {
        checkIsAjax();
        checkIsLoggedAjax();
        checkPermisionAjax('items_categories_articles_administration');

        $validator_rules = [
            [
                'field' => 'id_article',
                'label' => 'Id article is required',
                'rules' => ['required' => ''],
            ],
            [
                'field' => 'lang_article',
                'label' => 'Language',
                'rules' => ['required' => ''],
            ],
            [
                'field' => 'text_article',
                'label' => 'Text article',
                'rules' => ['required' => ''],
            ],
        ];

        $this->validator->set_rules($validator_rules);
        if (!$this->validator->validate()) {
            jsonResponse($this->validator->get_array_errors());
        }

        $id_article = intval($_POST['id_article']);

        $this->load->model('Item_Category_Articles_Model', 'category_atr');
        $article = $this->category_atr->get_details_cat_art($id_article);
        if (empty($article)) {
            jsonResponse('Error: The article does not exist.');
        }

        $lang_article = cleanInput($_POST['lang_article']);
        $tlang = $this->translations->get_language_by_iso2($lang_article);
        if (empty($tlang)) {
            jsonResponse('Error: Language does not exist.');
        }

        $translations_data = json_decode($article['translations_data'], true);
        if (array_key_exists($lang_article, $translations_data)) {
            jsonResponse('Error: Article translation for this language already exist.');
        }

        $translations_data[$lang_article] = [
            'lang_name' => $tlang['lang_name'],
            'abbr_iso2' => $tlang['lang_iso2'],
        ];

        $insert = [
            'id_article'   => $id_article,
            'text'         => $_POST['text_article'],
            'lang_article' => $lang_article,
        ];


        if (!$idTranslation = $this->category_atr->set_item_category_article_i18n($insert)) {
            jsonResponse('Cannot add translation now. Please try later.');
        }

        if (!empty($image = request()->request->get('image'))) {
            $this->saveImageI18n($idTranslation, $image);
        }

        $this->category_atr->update_cat_art($id_article, array('translations_data' => json_encode($translations_data)));
        jsonResponse('The translation has been successfully added', 'success');
    }

    function ajax_articles_edit_i18n() {
        checkIsAjax();
        checkIsLoggedAjax();
        checkPermisionAjax('items_categories_articles_administration');

        $validator_rules = array(
            array(
                'field' => 'id_article_i18n',
                'label' => 'Id article is required',
                'rules' => array('required' => '')
            ),
            array(
                'field' => 'id_article',
                'label' => 'Id article is required',
                'rules' => array('required' => '')
            ),
            array(
                'field' => 'lang_article',
                'label' => 'Language',
                'rules' => array('required' => '')
            ),
            array(
                'field' => 'text_article',
                'label' => 'Text article',
                'rules' => array('required' => '')
            )
        );

        $this->validator->set_rules($validator_rules);

        if(!$this->validator->validate()){
            jsonResponse($this->validator->get_array_errors());
        }

        $id_article = intval($_POST['id_article']);
        $id_article_i18n = intval($_POST['id_article_i18n']);

        $lang_article = cleanInput($_POST['lang_article']);
        $tlang = $this->translations->get_language_by_iso2($lang_article);
        if(empty($tlang)){
            jsonResponse('Error: Language does not exist.');
        }

        $this->load->model('Item_Category_Articles_Model', 'category_atr');
        $article_i18n = $this->category_atr->get_details_cat_art_i18n(array("id_article_i18n" => $id_article_i18n));
        if(empty($article_i18n) || $article_i18n['id_article'] != $id_article){
            jsonResponse('Error: The article does not exist.');
        }

        $update = array(
            'id_article' => $id_article,
            'text' => $_POST['text_article'],
        );
        //save the image to the storage
        if (!empty($image = request()->request->get('image'))) {
            $this->saveImageI18n($id_article_i18n, $image);
        }

        if($this->category_atr->update_cat_art_i18n($id_article_i18n, $update)){
            jsonResponse('The translation has been successfully edited', 'success');
        }

        jsonResponse('Error: Cannot edit translation now. Please try later.');
    }

    /**
     * Upload the image to the temporary folder
     */
    public function ajax_upload_temp_photo()
    {
        checkIsLoggedAjax();
        checkPermisionAjax('items_categories_articles_administration');

        $request = request();
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('files')[0];
        if (null === $uploadedFile) {
			jsonResponse(translate('validation_image_required'));
		}
        if (is_array($uploadedFile) || !$uploadedFile->isValid()) {
			jsonResponse(translate('validation_invalid_file_provided'));
		}
        $config = 'img.category_articles.main.rules';
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

    /**
     * Delete photo from the database and disk
     */
    public function ajax_article_delete_db_photo()
    {
        checkIsAjax();
        checkIsLoggedAjax();
        checkPermisionAjax('items_categories_articles_administration');

        $categoryId = request()->request->getInt('file');
		if (empty($categoryId)) {
            jsonResponse('Error: File name is not correct.');
        }
        /** @var Category_Article_Model $articleRepository */
        $articleRepository = model(Category_Article_Model::class);
        //if article exists
        if($categoryArticle = $articleRepository->findOne($categoryId)){
            /** @var FilesystemProviderInterface  $storageProvider */
            $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
            $publicDisk = $storageProvider->storage('public.storage');
            //delete file from database
            $articleRepository->updateOne($categoryId, ['photo' => '']);
            //delete file from disk
            try{
                $publicDisk->delete(CategoryArticlesFilePathGenerator::mainImagePath($categoryId, $categoryArticle['photo']));
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
        checkPermisionAjax('items_categories_articles_administration');

        $categoryId = request()->request->getInt('file');
		if (empty($categoryId)) {
            jsonResponse('Error: File name is not correct.');
        }
        /** @var Category_Translated_Article_Model $translatedArticleRepository */
        $translatedArticleRepository = model(Category_Translated_Article_Model::class);
        //if article exists
        if($categoryArticle = $translatedArticleRepository->findOne($categoryId)){
            /** @var FilesystemProviderInterface  $storageProvider */
            $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
            $publicDisk = $storageProvider->storage('public.storage');
            //delete file from database
            $translatedArticleRepository->updateOne($categoryId, ['photo' => '']);
            //delete file from disk
            try{
                $publicDisk->delete(CategoryArticlesI18nFilePathGenerator::mainImagePath($categoryId, $categoryArticle['photo']));
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
        //set the disks prefixers
        $prefixerTemp = $storageProvider->prefixer('temp.storage');
        $prefixerPublic = $storageProvider->prefixer('public.storage');
        $publicDisk = $storageProvider->storage('public.storage');
        //create the folder
        $publicDisk->createDirectory(CategoryArticlesFilePathGenerator::mainImageFolder($articleId));
        /** @var TinyMVC_Library_Image_intervention $interventionImageLibrary */
        $interventionImageLibrary = library(TinyMVC_Library_Image_intervention::class);
        $configImage = config("img.category_articles.main");
        $images = $interventionImageLibrary->image_processing(
            [
                'tmp_name' => $prefixerTemp->prefixPath($mainImage),
                'name'     => \basename($mainImage),
            ],
            [
                'destination'   => $prefixerPublic->prefixPath(CategoryArticlesFilePathGenerator::mainImageFolder($articleId)),
                'rules'         => $configImage["rules"],
                'handlers'      => [
                    'resize'        => $configImage["resize"],
                ],
            ]
        );
        //return the errors if there are any
        if (!empty($images['errors'])) {
            jsonResponse($images['errors']);
        }
        //else update in the database the new name of the photo
        /** @var Category_Article_Model $articleRepository */
        $articleRepository = model(Category_Article_Model::class);
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
        if (empty($articleId) || empty($mainImage)) {
            return;
        }

        /** @var FilesystemProviderInterface  $storageProvider */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);

        $prefixerTemp = $storageProvider->prefixer('temp.storage');
        $prefixerPublic = $storageProvider->prefixer('public.storage');
        $publicDisk = $storageProvider->storage('public.storage');

        /** @var TinyMVC_Library_Image_intervention $interventionImageLibrary */
        $interventionImageLibrary = library(TinyMVC_Library_Image_intervention::class);
        $configImage = config("img.category_articles_i18n.main");

        /** Create public folder */
        $publicPath = CategoryArticlesI18nFilePathGenerator::mainImageFolder($articleId);
        if (!$publicDisk->fileExists($publicPath)) {
            $publicDisk->createDirectory($publicPath);
        }

        $images = $interventionImageLibrary->image_processing(
            [
                'tmp_name' => $prefixerTemp->prefixPath($mainImage),
                'name'     => \basename($mainImage),
            ],
            [
                'destination'   => $prefixerPublic->prefixPath($publicPath),
                'rules'         => $configImage["rules"],
                'handlers'      => [
                    'resize'        => $configImage["resize"],
                ],
            ]
        );

        if (!empty($images['errors'])) {
            jsonResponse($images['errors']);
        }

        /** @var Category_Translated_Article_Model $articleRepository */
        $articleRepository = model(Category_Translated_Article_Model::class);
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
        //try to delete the directory
        try{
            $publicDisk->deleteDirectory($pathToDirectory);
        } catch (UnableToDeleteDirectory $e) {
            //silent fail
        }
    }
}
