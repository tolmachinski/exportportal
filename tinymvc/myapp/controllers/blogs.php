<?php

use App\Common\Buttons\ChatButton;
use App\DataProvider\IndexedBlogDataProvider;
use App\Filesystem\BlogsPathGenerator;
use App\Filesystem\FilePathGenerator;
use ExportPortal\Bridge\Filesystem\FilesystemProvider;
use ExportPortal\Contracts\Filesystem\FilesystemOperator;
use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;
use League\Flysystem\PathPrefixer;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Blogs application controller.
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [15.12.2021]
 * Controller Refactoring
 *
 * @property \Blog_Model                $blog
 * @property \Country_Model             $countries
 * @property \TinyMVC_Load              $load
 * @property \TinyMVC_View              $view
 * @property \TinyMVC_Library_URI       $uri
 * @property \TinyMVC_Library_Session   $session
 * @property \TinyMVC_Library_Cookies   $cookies
 * @property \TinyMVC_Library_Upload    $upload
 * @property \TinyMVC_Library_validator $validator
 *
 * @author Bendiucov Oleg
 */
class Blogs_Controller extends TinyMVC_Controller
{
    const IMAGES_AMOUNT_EXCEEDED = 15001;
    const IMAGES_INVALID_DOMAIN = 15002;

    private $breadcrumbs = array();

    private $preview_blog = false;

    private FilesystemOperator $storage;
    private FilesystemOperator $tempStorage;
    private IndexedBlogDataProvider $indexedBlogDataProvider;

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
        $this->indexedBlogDataProvider = $container->get(IndexedBlogDataProvider::class);
    }

    public function index()
    {
        show_404();
    }

    /**
     * @author Alexandr Usinevici
     * @todo Remove [29.06.2022]
     * Reason: not used.
     */
    // public function my()
    // {
    //     checkIsLogged();
    //     checkPermision('manage_blogs');
    //     checkGroupExpire();

    //     /**
    //      * @var Country_Model $countryModel
    //      */
    //     $countryModel = model(Country_Model::class);

    //     /**
    //      * @var Blog_Model $blogModel
    //      */
    //     $blogModel = model(Blog_Model::class);

    //     $uri = uri()->uri_to_assoc();

    //     $data = [
    //         'blog_categories'  => $blogModel->get_blog_categories(),
    //         'blog_countries'    => $countryModel->fetch_port_country(),
    //         'id_blog'           => empty($uri['blog_number']) ? null : (int) $uri['blog_number'],
    //         'counter'           => array_column($blogModel->counter_by_conditions(['status_count' => 1]), null, 'status'),
    //         'title'             => 'My blog',
    //     ];

    //     views(['new/header_view', 'new/blog/my/delete_29_06_2022_index_view', 'new/footer_view'], $data);
    // }

    public function administration()
    {
        checkIsLogged();
        checkPermision('blogs_administration');

        $this->load_main();

        $data['languages'] = $this->translations->get_languages(array('lang_active' => 1, 'lang_url_type' => "'domain'"));
        $data['blog_categories'] = $this->blog->get_blog_categories();
        $data['blog_countries'] = $this->blog->get_cr_countries();
        $data['counter'] = $this->blog->counter_by_conditions(array('status_count' => 1));
        $data['counter'] = arrayByKey($data['counter'], 'status');
        $data['last_blogs_id'] = $this->blog->get_blogs_last_id();

        $this->view->assign($data);
        $this->view->assign('title', 'Blog');
        $this->view->display('admin/header_view');
        $this->view->display('admin/blog/index_view');
        $this->view->display('admin/footer_view');
    }

    public function category_administration()
    {
        checkIsLogged();
        checkPermision('blogs_administration,manage_translations');

        $this->load_main();

        $data['languages'] = $this->translations->get_allowed_languages(array('skip' => array('en')));

        $this->view->assign($data);
        $this->view->assign('title', 'Blogs category');
        $this->view->display('admin/header_view');
        $this->view->display('admin/blog/blog_categories_view');
        $this->view->display('admin/footer_view');
    }

    public function popup_blogs()
    {
        checkIsAjax();
        checkIsLoggedAjaxModal();

        $this->load_main();
        $id_user = $this->session->id;

        $op = $this->uri->segment(3);
        switch ($op) {
            /**
             * @author Alexandr Usinevici
             * @todo Remove [29.06.2022]
             * Reason: not used.
             */
            // case 'edit_user_blog':
            //     checkPermisionAjaxModal('manage_blogs');

            //     $post_id = $this->uri->segment(4);
            //     $blog_info = $this->blog->get_blog($post_id);
            //     if ('en' == $blog_info['lang'] || empty($blog_info['lang'])) {
            //         $blog_categories = $this->blog->get_blog_categories();
            //     } else {
            //         $blog_categories = $this->blog->get_blog_categories_i18n(array('lang_category' => $blog_info['lang']));
            //     }

            //     /** @var FilesystemProviderInterface */
            //     $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
            //     $publicDisk = $storageProvider->storage('public.storage');
            //     $pathToFile = BlogsPathGenerator::gridThumb($post_id, $blog_info['photo']);
            //     if ($publicDisk->fileExists($pathToFile)) {
            //         $blog_info['photo_url'] = $publicDisk->url($pathToFile);
            //     }

            //     $data = array(
            //         'upload_folder'            => encriptedFolderName(),
            //         'tlanguages'               => $this->translations->get_languages(array('lang_active' => 1, 'lang_url_type' => "'domain'")),
            //         'tlanguage'                => $this->translations->get_language_by_iso2($blog_info['lang']),
            //         'blog_info'                => $blog_info,
            //         'blog_tags'                => !empty($blog_info['tags']) ? explode(',', $blog_info['tags']) : array(),
            //         'blog_countries'           => model('country')->fetch_port_country(),
            //         'blog_categories'          => $blog_categories,
            //     );

            //     $this->view->display('new/blog/my/form_view', $data);

            // break;
            /**
             * @author Alexandr Usinevici
             * @todo Remove [29.06.2022]
             * Reason: not used.
             */
            // case 'add_user_blog':
            //     checkPermisionAjaxModal('manage_blogs');

            //     if (__SITE_LANG == 'en') {
            //         $blog_categories = $this->blog->get_blog_categories();
            //     } else {
            //         $blog_categories = $this->blog->get_blog_categories_i18n();
            //     }

            //     $data = array(
            //         'upload_folder'            => encriptedFolderName(),
            //         'tlanguages'               => $this->translations->get_languages(array('lang_active' => 1, 'lang_url_type' => "'domain'")),
            //         'blog_categories'          => $blog_categories,
            //         'blog_countries'           => model('country')->fetch_port_country(),
            //     );

            //     $this->view->display('new/blog/my/form_view', $data);

            // break;
            case 'add_blog':
                checkPermisionAjaxModal('blogs_administration');

                if (__SITE_LANG == 'en') {
                    $blog_categories = $this->blog->get_blog_categories();
                } else {
                    $blog_categories = $this->blog->get_blog_categories_i18n();
                }
                $data = array(
                    'upload_folder'     => encriptedFolderName(),
                    'blog_categories'   => $blog_categories,
                    'tlanguages'        => $this->translations->get_languages(array('lang_active' => 1, 'lang_url_type' => "'domain'")),
                    'blog_countries'    => model('country')->fetch_port_country(),
                );

                $this->view->display('admin/blog/blog_form_view', $data);

            break;
            case 'edit_blog':
                checkPermisionAjaxModal('blogs_administration');

                $id_blog = $this->uri->segment(4);
                $blog_info = $this->blog->get_blog($id_blog);
                $blog_info['publish_on'] = getDateFormat($blog_info['publish_on'], 'Y-m-d', 'm/d/Y');
                $imagePath = BlogsPathGenerator::gridThumb($blog_info['id'], $blog_info['photo']);

                if ('en' == $blog_info['lang']) {
                    $blog_categories = $this->blog->get_blog_categories();
                } else {
                    $blog_categories = $this->blog->get_blog_categories_i18n(array('lang_category' => $blog_info['lang']));
                }

                $data = array(
                    'upload_folder'            => encriptedFolderName(),
                    'blog_categories'          => $blog_categories,
                    'blog_info'                => $blog_info,
                    'tlanguages'               => $this->translations->get_languages(array('lang_active' => 1, 'lang_url_type' => "'domain'")),
                    'blog_countries'           => model('country')->fetch_port_country(),
                    'blogImage'                => $this->storage->url($imagePath),
                );

                $this->view->display('admin/blog/blog_form_view', $data);

            break;
            case 'add_blog_category':
                checkPermisionAjaxModal('blogs_administration');

                $this->view->display('admin/blog/blog_category_form_view');

            break;
            case 'add_category_i18n':
                checkPermisionAjaxModal('blogs_administration,manage_translations');

                $id_category = (int) $this->uri->segment(4);
                $data['category'] = $this->blog->get_blog_category($id_category);
                if (empty($data['category'])) {
                    messageInModal(translate('systmess_error_category_blog_not_exist'));
                }

                $data['tlanguages'] = $this->translations->get_allowed_languages(array('skip' => array('en')));
                if (empty($data['tlanguages'])) {
                    messageInModal(translate('systmess_error_language_not_available'));
                }

                $this->view->display('admin/blog/blog_category_form_i18n_view', $data);

            break;
            case 'edit_blog_category':
                checkPermisionAjaxModal('blogs_administration');

                /** @var Blogs_Categories_Model $blogCategoriesModel */
                $blogCategoriesModel = model(Blogs_Categories_Model::class);

                if (
                    empty($categoryId = (int) uri()->segment(4))
                    || empty($category = $blogCategoriesModel->findOne($categoryId))
                ) {
                    messageInModal(translate('systmess_error_invalid_data'));
                }

                views()->display('admin/blog/blog_category_form_view', ['category' => $category]);
            break;
            case 'edit_category_i18n':
                checkPermisionAjaxModal('blogs_administration,manage_translations');

                /** @var Blogs_Categories_Model $blogsCategoriesModel */
                $blogsCategoriesModel = model(Blogs_Categories_Model::class);

                if (
                    empty($categoryId = (int) uri()->segment(4))
                    || empty($category = $blogsCategoriesModel->findOne($categoryId))
                ) {
                    messageInModal(translate('systmess_error_invalid_data'));
                }

                /** @var Locales_Model $localesModel */
                $localesModel = model(Locales_Model::class);

                if (
                    empty($categoryLang = (string) uri()->segment(5))
                    || empty($languageData = $localesModel->findOneBy([
                        'scopes' => [
                            'iso2'  => $categoryLang,
                        ]
                    ]))
                ) {
                    messageInModal(translate('systmess_error_invalid_data'));
                }

                /** @var Blogs_Categories_I18n_Model $blogsCategoriesI18nModel */
                $blogsCategoriesI18nModel = model(Blogs_Categories_I18n_Model::class);

                if (empty($categoryI18n = $blogsCategoriesI18nModel->findOneBy([
                    'scopes' => [
                        'categoryId'    => $categoryId,
                        'language'      => $categoryLang,
                    ],
                ]))) {
                    messageInModal(translate('systmess_error_invalid_data'));
                }

                if (session()->get('group_lang_restriction') && !in_array($languageData['id_lang'], (array) session()->get('group_lang_restriction_list'))) {
                    messageInModal(translate('systmess_error_edit_category_blog_i18n_restricted_language'));
                }

                views()->display('admin/blog/blog_category_form_i18n_view', [
                    'category'      => $category,
                    'lang_block'    => $languageData,
                    'category_i18n' => $categoryI18n,
                ]);

            break;
            default:
                messageInModal(translate('systmess_error_route_not_found'));

            break;
        }
    }

    // public function ajax_blogs_my()
    // {
    //     checkIsAjax();
    //     checkIsLoggedAjaxDT();
    //     checkPermisionAjaxDT('manage_blogs');
    //     checkGroupExpire('dt');

    //     /**
    //      * @var Blog_Model $blogModel
    //      */
    //     $blogModel = model(Blog_Model::class);

    //     $sortBy = flat_dt_ordering($_POST, array(
    //         'post'            => 'title',
    //         'created_at'      => 'date',
    //     ));

    //     $dtFilters = dtConditions($_POST, [
    //         ['as' => 'start_from',       'key' => 'start_from',      'type' => 'getDateFormat:m/d/Y,Y-m-d'],
    //         ['as' => 'start_to',         'key' => 'start_to',        'type' => 'getDateFormat:m/d/Y,Y-m-d'],
    //         ['as' => 'id_blog',          'key' => 'blog_number',     'type' => 'toId'],
    //         ['as' => 'keywords',         'key' => 'keywords',        'type' => 'cleanInput|cut_str'],
    //         ['as' => 'category',         'key' => 'category',        'type' => 'int'],
    //         ['as' => 'country',          'key' => 'country',         'type' => 'int'],
    //         ['as' => 'status',           'key' => 'status',          'type' => 'cleanInput'],
    //         ['as' => 'visible',          'key' => 'visibility',      'type' => 'int'],
    //     ]);

    //     $params = array_merge(
    //         [
    //             'sort_by'   => empty($sortBy) ? ['date-desc'] : $sortBy,
    //             'per_p'     => (int) $_POST['iDisplayLength'],
    //             'start'     => (int) $_POST['iDisplayStart'],
    //             'user'      => privileged_user_id(),
    //         ],
    //         $dtFilters
    //     );

    //     $blogs_count = $params['count'] = $blogModel->counter_by_conditions($params);
    //     $blogs = $blogModel->get_blogs($params);
    //     $output = array(
    //         'iTotalDisplayRecords' => $blogs_count,
    //         'iTotalRecords'        => $blogs_count,
    //         'aaData'               => [],
    //         'sEcho'                => (int) $_POST['sEcho'],
    //     );

    //     if (empty($blogs)) {
    //         jsonResponse('', 'success', $output);
    //     }

    //     /** @var FilesystemProviderInterface */
    //     $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
    //     $publicDisk = $storageProvider->storage('public.storage');

    //     foreach ($blogs as $blog) {
    //         $post_id = $blog['id'];
    //         $post_title = $blog['title'];
    //         $post_slug = $blog['title_slug'];
    //         $is_visible = filter_var($blog['visible'], FILTER_VALIDATE_BOOLEAN);
    //         $is_new = 'new' === $blog['status'];
    //         $can_delete = $is_new || have_right('blogs_administration');
    //         $can_edit = $is_new || have_right('blogs_administration');

    //         //region Post
    //         $post_image_url = $publicDisk->url(BlogsPathGenerator::gridThumb($post_id, $post_image_name));
    //         $post_status_icon_text = $is_visible ? translate('general_status_published_text') : translate('general_status_unpublished_text');
    //         $post_category_name = $blog['category_name'];
    //         $post_url = __BLOG_URL . (!$is_visible ? "preview_blog/{$post_id}/{$post_slug}" : "detail/{$post_id}/{$post_slug}");
    //         $post = "
    //             <div class=\"flex-card\">
    //                 <div class=\"flex-card__fixed main-data-table__item-img image-card\">
    //                     <span class=\"link\">
    //                         <img class=\"image\" src=\"{$post_image_url}\" alt=\"{$post_title}\"/>
    //                     </span>
    //                 </div>
    //                 <div class=\"flex-card__float\">
    //                     <div class=\"main-data-table__item-ttl\">
    //                         <a href=\"{$post_url}\"
    //                             class=\"display-ib link-black txt-medium\"
    //                             title=\"{$post_title}\"
    //                             target=\"_blank\">
    //                             {$post_title}
    //                         </a>
    //                     </div>
    //                     <div class=\"links-black\">{$post_category_name}</div>
    //                     <div class=\"txt-gray\">{$post_status_icon_text}</div>
    //                 </div>
    //             </div>
    //         ";
    //         //endregion Post

    //         //region Description
    //         $description = '&mdash;';
    //         $description_text = $blog['short_description'];
    //         if (!empty($description_text)) {
    //             $description = "
    //                 <div class=\"grid-text\">
    //                     <div class=\"grid-text__item\">
    //                         <div>
    //                             {$description_text}
    //                         </div>
    //                     </div>
    //                 </div>
    //             ";
    //         }
    //         //endregion Description

    //         //region Country
    //         $country = '&mdash;';
    //         $country_id = (int) $blog['id_country'];
    //         if (null !== $country_id && 0 !== $country_id) {
    //             $country_name = $blog['country'];
    //             $country_url = getCountryFlag($country_name);
    //             $country = "
    //                 <div>
    //                     <img  width=\"24\" height=\"24\" src=\"{$country_url}\" title=\"{$country_name}\" alt=\"{$country_name}\"/>
    //                     {$country_name}
    //                 </div>
    //             ";
    //         }
    //         //endregion Country

    //         //region Actions

    //         //region Delete button
    //         $delete_button = null;
    //         if ($can_delete) {
    //             $delete_button_url = __SITE_URL . 'blogs/ajax_blogs_operation/remove_blog';
    //             $delete_button_title = translate('blog_button_delete_blog_post_text');
    //             $delete_button_text = translate('general_button_delete_text');
    //             $delete_button_message = translate('blog_button_delete_blog_post_message');
    //             $delete_button = "
    //                 <a class=\"dropdown-item confirm-dialog\"
    //                     title=\"{$delete_button_title}\"
    //                     data-message=\"{$delete_button_message}\"
    //                     data-callback=\"deleteBlogPost\"
    //                     data-post=\"{$post_id}\">
    //                     <i class=\"ep-icon ep-icon_trash-stroke\"></i>
    //                     <span>{$delete_button_text}</span>
    //                 </a>
    //             ";
    //         }
    //         //endregion Delete button

    //         //region Edit button
    //         $edit_button = null;
    //         if ($can_edit) {
    //             $edit_button_url = __SITE_URL . "blogs/popup_blogs/edit_user_blog/{$post_id}";
    //             $edit_button_title = translate('blog_button_edit_blog_post_text');
    //             $edit_button_text = translate('general_button_edit_text');
    //             $edit_button = "
    //                 <a rel=\"edit\"
    //                     class=\"dropdown-item fancybox.ajax fancyboxValidateModal\"
    //                     data-fancybox-href=\"{$edit_button_url}\"
    //                     data-title=\"{$edit_button_title}\"
    //                     title=\"{$edit_button_title}\">
    //                     <i class=\"ep-icon ep-icon_pencil\"></i>
    //                     <span>{$edit_button_text}</span>
    //                 </a>
    //             ";
    //         }
    //         //endregion Edit button

    //         //region View button
    //         $view_button_url = __BLOG_URL . (!$is_visible ? "preview_blog/{$post_id}/{$post_slug}" : "detail/{$post_id}/{$post_slug}");
    //         $view_button_title = !$is_visible ? translate('blog_button_preview_blog_post_text') : translate('blog_button_view_blog_post_text');
    //         $view_button_text = !$is_visible ? translate('general_button_preview_text') : translate('general_button_view_text');
    //         $view_button = "
    //             <a href=\"{$view_button_url}\"
    //                 class=\"dropdown-item\"
    //                 data-title=\"{$view_button_title}\"
    //                 title=\"{$view_button_title}\"
    //                 target=\"_blank\">
    //                 <i class=\"ep-icon ep-icon_info-stroke\"></i>
    //                 <span>{$view_button_text}</span>
    //             </a>
    //         ";
    //         //endregion View button

    //         //region Visibility button
    //         $visibility_button_text = $is_visible ? translate('general_button_unpublish_text') : translate('general_button_publish_text');
    //         $visibility_button_title = $is_visible ? translate('blog_button_unpublish_blog_post_text') : translate('blog_button_publish_blog_post_text');
    //         $visibility_button_message = $is_visible ? translate('blog_button_unpublish_blog_post_message') : translate('blog_button_publish_blog_post_message');
    //         $visibility_button_icon = $is_visible ? 'ep-icon_invisible' : 'ep-icon_visible';
    //         $visibility_button = "
    //             <a class=\"dropdown-item confirm-dialog\"
    //                 title=\"{$visibility_button_title}\"
    //                 data-message=\"{$visibility_button_message}\"
    //                 data-callback=\"changeVisibility\"
    //                 data-post=\"{$post_id}\">
    //                 <i class=\"ep-icon {$visibility_button_icon}\"></i>
    //                 <span>{$visibility_button_text}</span>
    //             </a>
    //         ";
    //         //endregion Visibility button

    //         //region All button
    //         $all_button_text = translate('general_dt_info_all_text');
    //         $all_button = "
    //             <a class=\"dropdown-item d-none d-md-block d-lg-block d-xl-none call-function\"
    //                 data-callback=\"dataTableAllInfo\"
    //                 target=\"_blank\">
    //                 <i class=\"ep-icon ep-icon_info-stroke\"></i>
    //                 <span>{$all_button_text}</span>
    //             </a>
    //         ";
    //         //endregion All button

    //         $actions = "
    //             <div class=\"dropdown\">
    //                 <a class=\"dropdown-toggle\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\">
    //                     <i class=\"ep-icon ep-icon_menu-circles\"></i>
    //                 </a>
    //                 <div class=\"dropdown-menu dropdown-menu-right\">
    //                     {$view_button}
    //                     {$edit_button}
    //                     {$visibility_button}
    //                     {$delete_button}
    //                     {$all_button}
    //                 </div>
    //             </div>
    //         ";
    //         //endregion Actions

    //         $output['aaData'][] = [
    //             'post' 	      => $post,
    //             'description' => $description,
    //             'created_at'  => getDateFormatIfNotEmpty($blog['date']),
    //             'country'     => $country,
    //             'actions'     => $actions,
    //         ];
    //     }

    //     jsonResponse('', 'success', $output);
    // }

    public function ajax_blogs_administration()
    {
        checkIsAjax();
        checkIsLoggedAjaxDT();
        checkPermisionAjaxDT('blogs_administration');

        /**
         * @var Blog_Model $blogModel
         */
        $blogModel = model(Blog_Model::class);

        /**
         * @var User_Model $usersModel
         */
        $usersModel = model(User_Model::class);

        $sortBy = flat_dt_ordering($_POST, array(
            'dt_id_blog'      => 'b.id',
            'dt_date_created' => 'b.date',
            'dt_status'       => 'b.status',
            'dt_visible'      => 'b.visible',
            'dt_country'      => 'pc.country',
            'dt_category'     => 'b.id_category',
            'dt_publish_on'   => 'b.publish_on',
            'dt_views'        => 'b.views',
        ));

        $dtFilters = dtConditions($_POST, [
            ['as' => 'start_from',       'key' => 'start_from',      'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'start_to',         'key' => 'start_to',        'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'publish_from',     'key' => 'publish_from',    'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'publish_to',       'key' => 'publish_to',      'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'keywords',         'key' => 'keywords',        'type' => 'cleanInput|cut_str'],
            ['as' => 'user',             'key' => 'user',            'type' => 'int'],
            ['as' => 'lang',             'key' => 'blog_lang',       'type' => 'cleanInput'],
            ['as' => 'category',         'key' => 'category',        'type' => 'int'],
            ['as' => 'country',          'key' => 'country',         'type' => 'int'],
            ['as' => 'status',           'key' => 'status',          'type' => 'cleanInput'],
            ['as' => 'visible',          'key' => 'visibility',      'type' => 'int'],
            ['as' => 'published',        'key' => 'published',       'type' => 'int'],
        ]);

        $params = array_merge(
            [
                'per_p'     => (int) $_POST['iDisplayLength'],
                'start'     => (int) $_POST['iDisplayStart'],
                'sort_by'   => empty($sortBy) ? ['b.id-desc'] : $sortBy
            ],
            $dtFilters
        );

        $blogs = $blogModel->get_blogs($params);
        $blogs_count = $blogModel->counter_by_conditions($params);

        $output = [
            'iTotalDisplayRecords' => $blogs_count,
            'iTotalRecords'        => $blogs_count,
            'aaData'               => [],
            'sEcho'                => (int) $_POST['sEcho'],
        ];

        if (empty($blogs)) {
            jsonResponse('', 'success', $output);
        }

        $users_id = array_column($blogs, 'id_user', 'id_user');

        if (!empty($users_id)) {
            $users_list = $usersModel->getUsers(array('users_list' => implode(',', $users_id)));
            $users_list = array_column($users_list, null, 'idu');
        }

        $status_array = ['new' => 'New', 'moderated' => 'Moderated'];
        $laguages = arrayByKey($this->translations->get_languages(), 'lang_iso2');

        /** @var FilesystemProviderInterface */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $publicDisk = $storageProvider->storage('public.storage');

        foreach ($blogs as $blog) {
            $visible_btn = 'ep-icon_visible';
            $visible_status = '<div class="tal">'
                                . '<a class="ep-icon ep-icon_filter txt-green dt_filter" data-title="Visibility" title="Filter by visibility" data-value-text="Yes" data-value="1" data-name="visibility"></a>'
                            . '</div>Yes';
            if (!$blog['visible']) {
                $visible_btn = 'ep-icon_invisible ';
                $visible_status = '<div class="tal">'
                                    . '<a class="ep-icon ep-icon_filter txt-green dt_filter" data-title="Visibility" title="Filter by visibility" data-value-text="No" data-value="0" data-name="visibility"></a>'
                                . '</div>No';
            }

            if ('moderated' != $blog['status']) {
                $moderated_btn = sprintf(
                    <<<'MODERATED_BUTTON'
                        <a class="ep-icon ep-icon_sheild-nok txt-red confirm-dialog"
                            data-callback="change_moderated_blog"
                            data-blog="%s"
                            data-message="%s"
                            href="#" title="Set blog moderated"
                        ></a>
                    MODERATED_BUTTON,
                    $blog['id'],
                    translate('systmess_confirm_moderate_blog')
                );
            } else {
                $moderated_btn = '<span class="ep-icon ep-icon_sheild-nok txt-green" title="Blog moderated"></span>';
            }

            $user_name = $users_list[$blog['id_user']]['user_name'];

            if (empty($blog['id_country'])) {
                $dt_country = '&mdash;';
            } else {
                $dt_country = '<img width="24" height="24" src="' . getCountryFlag($blog['country']) . '" title="Filter by: ' . $blog['country'] . '" alt="' . $blog['country'] . '"/><br>' . $blog['country'];
            }

            //TODO: admin chat hidden
            $btnChatUser = new ChatButton(['hide' => true, 'recipient' => $blog['id_user'], 'recipientStatus' => 'active'], ['classes' => 'btn-chat-now', 'text' => '']);
            $btnChat = $btnChatUser->button();
            $pathToFile = BlogsPathGenerator::gridThumb($blog['id'], $blog['photo']);

            $imageLink = $publicDisk->url($pathToFile);
            $imageThumbLink = getNoImage(166, 138);
            $imageUrl = ($publicDisk->fileExists($pathToFile)) ? $imageLink : $imageThumbLink;

            $visibleBlogBtn = sprintf(
                <<<VISIBLE_BLOG_BUTTON
                    <a class="ep-icon txt-blue {$visible_btn} confirm-dialog"
                        data-callback="change_visible_blog"
                        data-blog="{$blog['id']}"
                        data-message="%s"
                        href="#"
                        title="%s"
                    ></a>
                VISIBLE_BLOG_BUTTON,
                translate('systmess_confirm_visibility_blog'),
                $blog['visible'] ? 'Set blog inactive' : 'Set blog active'
            );

            $editBlogBtn = sprintf(
                <<<EDIT_BLOG_BUTTON
                    <a href="blogs/popup_blogs/edit_blog/{$blog['id']}"
                        class="ep-icon ep-icon_pencil txt-blue fancyboxValidateModalDT fancybox.ajax"
                        data-title="Edit this blog"
                        title="Edit this blog"
                    ></a>
                EDIT_BLOG_BUTTON
            );

            $blogUrl = __BLOG_URL . 'detail/' . $blog['id'] . '/' . strForURL($blog['title']);
            $previewBlogBtn = sprintf(
                <<<PREVIEW_BLOG_BUTTON
                    <a href="{$blogUrl}"
                        class="ep-icon ep-icon_magnifier txt-blue"
                        title="Preview this blog" target="_blank"
                    ></a>
                    PREVIEW_BLOG_BUTTON
            );

            $removeBlogBtn = sprintf(
                <<<REMOVE_BLOG_BUTTON
                    <a class="ep-icon ep-icon_remove txt-red confirm-dialog"
                        data-callback="remove_blog"
                        data-blog="{$blog['id']}"
                        title="Remove this blog"
                        data-message="%s"
                        href="#"
                    ></a>
                REMOVE_BLOG_BUTTON,
                translate('systmess_confirm_delete_blog')
            );

            $output['aaData'][] = array(
                'dt_id_blog' => $blog['id'],
                'dt_status'  => '<div class="tal">'
                    . '<a class="ep-icon ep-icon_filter txt-green dt_filter" data-title="Status" title="Filter by status" data-value-text="' . $status_array[$blog['status']] . '" data-value="' . $blog['status'] . '" data-name="status"></a>'
                    . '</div>'
                    . $status_array[$blog['status']],
                'dt_visible' => $visible_status,
                'dt_lang'    => $laguages[$blog['lang']]['lang_name'],
                'dt_author'  => '<div class="tal">'
                        . '<a class="ep-icon ep-icon_filter txt-green dt_filter" data-title="Author" title="Filter by ' . $user_name . '" data-value-text="' . $user_name . '" data-value="' . $blog['id_user'] . '" data-name="user"></a>'
                        . '<a class="ep-icon ep-icon_user" title="View personal page of ' . $user_name . '" href="' . __SITE_URL . 'usr/' . strForURL($user_name) . '-' . $blog['id_user'] . '"></a>'
                        . $btnChat
                    . '</div>
					<a href="usr/' . strForURL($user_name) . '-' . $blog['id_user'] . '">' . $user_name . '</a>',
                'dt_blog' => '<div class="pull-left">'
                    . '<div class="clearfix"><strong class="pull-left lh-16 pr-5">Category: </strong>' . $blog['category_name'] . '</div>'
                    . '<div class="clearfix"><strong class="pull-left lh-16 pr-5">Title </strong><a href="' . getBlogUrl($blog) . '">' . $blog['title'] . '</a></div>'
                    . '</div>',
                'dt_photo'             => '<img class="mw-100" src="' . $imageUrl . '" alt="' . $blog['title'] . '"/>',
                'dt_short_description' => '
                    <div class="h-50 hidden-b"><strong>Main:</strong> ' . $blog['description'] . '</div>
                    <div class="h-50 hidden-b"><strong>SEO:</strong> ' . $blog['short_description'] . '</div>
                ',
                'dt_date_created'      => getDateFormat($blog['date']),
                'dt_publish_on'        => getDateFormat($blog['publish_on'], 'Y-m-d', 'm/d/Y'),
                'dt_views'             => $blog['views'],
                'dt_actions'           => $moderated_btn
                    . $visibleBlogBtn
                    . $editBlogBtn
                    . $previewBlogBtn
                    . $removeBlogBtn,
                'dt_country' => $dt_country,
            );
        }

        jsonResponse('', 'success', $output);
    }

    public function ajax_blogs_category_administration()
    {
        checkIsAjax();
        checkIsLoggedAjaxDT();
        checkPermisionAjaxDT('blogs_administration,manage_translations');

        /**
         * @var Blog_Model $blogModel
         */
        $blogModel = model(Blog_Model::class);

        $sortBy = flat_dt_ordering($_POST, array(
            'dt_name'        => 'name',
            'dt_updated_at'  => 'updated_at',
            'dt_id_category' => 'id_category',
        ));

        $dtFilters = dtConditions($_POST, [
            ['as' => 'en_updated_to',       'key' => 'en_updated_to',      'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'en_updated_from',     'key' => 'en_updated_from',    'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'translated_in',       'key' => 'translated_in',      'type' => 'cleanInput'],
            ['as' => 'not_translated_in',   'key' => 'not_translated_in',  'type' => 'cleanInput'],
        ]);

        $params = array_merge(
            [
                'sort_by'   => empty($sortBy) ? ['id_category-asc'] : $sortBy,
                'per_p'     => (int) $_POST['iDisplayLength'],
                'start'     => (int) $_POST['iDisplayStart'],
            ],
            $dtFilters
        );

        $records = $blogModel->get_blog_categories($params);
        $records_total = $blogModel->counter_by_blog_categories($params);

        $output = array(
            'iTotalDisplayRecords' => $records_total,
            'iTotalRecords'        => $records_total,
            'aaData'               => [],
            'sEcho'                => (int) $_POST['sEcho'],
        );

        if (empty($records)) {
            jsonResponse('', 'success', $output);
        }

        $languages = array_column($this->translations->get_allowed_languages(array('skip' => array('en'))), null, 'lang_iso2');
        foreach ($records as $record) {
            $i18n_used = array();
            $i18n_list = array();
            $i18n_meta = array_filter(json_decode($record['translations_data'], true));
            $text_updated_date = getDateFormat($i18n_meta['en']['updated_at'], 'Y-m-d H:i:s');

            foreach ($i18n_meta as $lang_code => $i18n) {
                if (!array_key_exists($lang_code, $languages)) {
                    continue;
                }

                $i18n_used[$lang_code] = $lang_code;
                $i18n_update_date = getDateFormat($i18n['updated_at'], 'Y-m-d H:i:s');
                $i18n_list[] = '<a href="' . __SITE_URL . 'blogs/popup_blogs/edit_category_i18n/' . $record['id_category'] . '/' . $lang_code . '"
									class="btn btn-xs tt-uppercase mnw-30 ' . (($i18n['updated_at'] < $i18n_meta['en']['updated_at']) ? 'btn-danger' : 'btn-primary') . ' mb-5 fancyboxValidateModalDT fancybox.ajax"
									data-title="Edit translation"
									title="Last update: ' . $i18n_update_date . '">
									' . $lang_code . '
								</a>';
            }

            if (empty($i18n_list)) {
                $i18n_list[] = '&mdash;';
            }

            $actions = array();
            if (have_right('manage_translations') && !empty(array_diff_key($languages, $i18n_used))) {
                $actions[] = '<a href="' . __SITE_URL . 'blogs/popup_blogs/add_category_i18n/' . $record['id_category'] . '"
									data-title="Add translation"
									title="Add translation"
									class="fancyboxValidateModalDT fancybox.ajax">
									<i class="ep-icon ep-icon_globe-circle"></i>
								</a>';
            }

            if (have_right('moderate_content')) {
                $actions[] = '<a class="ep-icon ep-icon_pencil fancyboxValidateModalDT fancybox.ajax"
								title="Edit blog category"
								href="' . __SITE_URL . 'blogs/popup_blogs/edit_blog_category/' . $record['id_category'] . '"
								data-title="Edit blog category">
							</a>';
                $actions[] = sprintf(
                    <<<'DELETE_BUTTON'
                        <a class="ep-icon ep-icon_remove txt-red confirm-dialog"
                            data-callback="remove_category_blog"
                            data-message="%s"
                            title="Delete blog category"
                            data-category="%s"
                        ></a>
                    DELETE_BUTTON,
                    translate('systmess_confirm_delete_blog_category'),
                    $record['id_category']
                );
            }

            if (empty($actions)) {
                $actions[] = '&mdash;';
            }

            $output['aaData'][] = array(
                'dt_id_category' => $record['id_category'],
                'dt_name'        => $record['name'],
                'dt_updated_at'  => $text_updated_date,
                'dt_actions'     => implode(' ', $actions),
                'dt_tlangs_list' => implode(' ', $i18n_list),
            );
        }

        jsonResponse('', 'success', $output);
    }

    public function ajax_blogs_operation()
    {
        checkIsAjax();

        $this->load_main();
        $id_user = privileged_user_id();
        $op = $this->uri->segment(3);

        switch ($op) {
            case 'recommended_more':
                $count = (int) $_POST['count'];

                $params_recommended = [
                    'status'  => 'moderated',
                    'visible' => 1,
                    'start'   => $count,
                    'per_p'   => (int) config('blogs_recommended_list_per_page'),
                ];

                $blogsCount = $this->blog->counter_by_conditions($params_recommended);
                $data['lazyLoadDisabled'] = true;
                if ($blogsCount) {
                    $data['blogs'] = $this->blog->get_blogs($params_recommended);

                    $data['blogs'] = array_map(function ($blog) {
                        $blog['photoSrc'] = $this->storage->url(BlogsPathGenerator::gridThumb($blog['id'], $blog['photo']));
                        $blog['title'] = cleanOutput($blog['title']);

                        return $blog;
                    }, $data['blogs']);
                }

                $blogsList = $this->view->fetch('new/blog/blog_recommended_list_view', $data);
                jsonResponse('', 'success', ['list' => $blogsList, 'count'=> $blogsCount]);

            break;
            case 'preview_content':
                if (!logged_in()) {
                    jsonResponse(translate("systmess_error_should_be_logged"));
                }

                $sanitizer = tap(library('Cleanhtml', 'clean'), function ($sanitizer) {
                    $sanitizer->allowIframes();
                    $sanitizer->defaultTextarea([
                        'attribute' => 'data-video,colspan,rowspan,dir',
                        'style' => 'text-align, padding-left, padding-right'
                    ]);
                    $sanitizer->addAdditionalTags('<img><figure><figcaption><h2><h3><h4><h5><h6><p><span><strong><em><b><i><u><a><ol><ul><li><br><table><tbody><tr><td><thead><th>');
                });

                jsonResponse('', 'success', array('content' => $sanitizer->sanitize($_POST['content'])));

            break;
            case 'check_new':
                if (!logged_in()) {
                    jsonResponse(translate("systmess_error_should_be_logged"));
                }

                $lastId = $_POST['lastId'];
                $blogs_count = $this->blog->get_count_new_blogs($lastId);

                if ($blogs_count) {
                    $last_blogs_id = $this->blog->get_blogs_last_id();
                    jsonResponse('', 'success', array('nr_new' => $blogs_count, 'lastId' => $last_blogs_id));
                } else {
                    jsonResponse(translate('systmess_error_dont_exist_blog'));
                }

            break;
            case 'remove_blog':
                checkPermisionAjax('manage_blogs,blogs_administration');

                $post_id = (int) $_POST['blog'];
                if (
                    empty($post_id) ||
                    empty($blog_post = $this->blog->get_blog($post_id))
                ) {
                    jsonResponse(translate('systmess_error_does_not_exist_blog'));
                }

                if ($blog_post['id_user'] != privileged_user_id() && !have_right('blogs_administration')) {
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));
                }

                if (!$this->delete_blog_post($post_id, $blog_post['id_user'])) {
                    jsonResponse(translate('systmess_error_cant_remove blog_now'));
                }

                jsonResponse(translate('systmess_success_blog_delete'), 'success');

            break;
            case 'edit_blog':
                checkPermisionAjax('manage_blogs,blogs_administration');

                //region Validation
                $validator_rules = array(
                    array(
                        'field' => 'country',
                        'label' => translate('blog_dashboard_modal_field_country_label_text'),
                        'rules' => array('required' => '', 'integer' => ''),
                    ),
                    array(
                        'field' => 'category',
                        'label' => translate('blog_dashboard_modal_field_category_label_text'),
                        'rules' => array('required' => '', 'integer' => ''),
                    ),
                    array(
                        'field' => 'title',
                        'label' => translate('blog_dashboard_modal_field_title_label_text'),
                        'rules' => array('required' => '', 'max_len[250]' => ''),
                    ),
                    array(
                        'field' => 'short_description',
                        'label' => translate('blog_dashboard_modal_field_description_label_text'),
                        'rules' => array('required' => '', 'max_len[500]' => ''),
                    ),
                    array(
                        'field' => 'description',
                        'label' => translate('blog_dashboard_modal_field_description_label_text'),
                        'rules' => array('required' => '', 'max_len[200]' => ''),
                    ),
                    array(
                        'field' => 'content',
                        'label' => translate('blog_dashboard_modal_field_content_label_text'),
                        'rules' => array('required' => '', 'html_max_len[20000]' => ''),
                    ),
                    array(
                        'field' => 'upload_folder',
                        'label' => 'Upload folder',
                        'rules' => array('required' => ''),
                    ),
                    array(
                        'field' => 'photo_caption',
                        'label' => "Caption Main photo",
                        'rules' => array('max_len[250]' => ''),
                    ),
                );

                if (have_right('blogs_administration') && isset($_POST['publish_on'])) {
                    $validator_rules[] = [
                        'field' => 'publish_on',
                        'label' => translate('blog_dashboard_modal_field_publish_on_label_text'),
                        'rules' => [
                            'valid_date[m/d/Y]' => '',
                            function (string $attr, $value, callable $fail) {
                                if (empty($value)) {
                                    return;
                                }

                                $currentdate = new DateTimeImmutable();
                                $publishDate = DateTimeImmutable::createFromFormat('m/d/Y', $value);
                                if ($publishDate < $currentdate) {
                                    $fail(translate('systmess_error_incorrect_publish_date'));
                                }
                            },
                        ],
                    ];
                }

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }
                //endregion Validation

                //region Blog post check
                $post_id = (int) $_POST['post'];
                if (
                    empty($post_id) ||
                    empty($post = $this->blog->get_blog($post_id))
                ) {
                    jsonResponse(translate('systmess_error_does_not_exist_blog'));
                }
                //endregion Blog post check

                //region Access check
                if (!have_right('blogs_administration')) {
                    if ((int) $post['id_user'] !== (int) privileged_user_id()) {
                        jsonResponse(translate('systmess_error_does_not_exist_blog'));
                    }
                }
                //endregion Access check

                //region Language check
                // Check language
                $language = null;
                $language_code = cleanInput($_POST['blog_lang']);
                if (!empty($language_code)) {
                    $language = $this->translations->get_language_by_iso2($language_code, array('lang_active' => 1, 'lang_url_type' => "domain"));
                    if (empty($language)) {
                        jsonResponse(translate('systmess_error_language_not_exist'));
                    }
                } else {
                    $language_code = $post['lang'];
                }
                //endregion Language check

                //region Category check
                // Check category
                $category_id = (int) $_POST['category'];
                if (
                    empty($category_id) ||
                    empty($category = $this->blog->get_category($category_id, $language_code))
                ) {
                    jsonResponse(translate('systmess_error_category_blog_not_exist'));
                }
                //endregion Category check

                //region Country check
                // Check if country is valid
                $country_id = empty($_POST['country']) ? 0 : (int) cleanInput($_POST['country']);
                if (0 !== $country_id) {
                    $country = model('country')->get_country($country_id);
                    if (empty($country)) {
                        jsonResponse(translate('systmess_error_country_not_exist'), 'error');
                    }
                } else {
                    $country_id = 0;
                }
                //endregion Country check

                //region Tags cleanup
                // Prepare tags
                $tags = array();
                if (!empty($_POST['tags'])) {
                    if(!is_array($_POST['tags'])){
                        $_POST['tags'] = explode(';', $_POST['tags']);
                    }

                    foreach ($_POST['tags'] as $tag) {
                        $tag = cleanInput($tag);
                        $temp_tag_size = mb_strlen($tag);
                        if ($temp_tag_size >= 3 && $temp_tag_size <= 30 && model('elasticsearch_badwords')->is_clean($tag)) {
                            $tags[] = $tag;
                        }
                    }
                }
                if (empty($tags)) {
                    jsonResponse(sprintf(translate('validation_is_required'), 'Tags'));
                }
                //endregion Tags cleanup

                //region Sanitizer
                // Load sanitize library
                $sanitizer = tap(library('Cleanhtml', 'clean'), function ($sanitizer) {
                    $sanitizer->allowIframes();
                    $sanitizer->defaultTextarea([
                        'attribute' => 'data-video,colspan,rowspan,dir',
                        'style' => 'text-align, padding-left, padding-right'
                    ]);
                    $sanitizer->addAdditionalTags('<img><figure><figcaption><h2><h3><h4><h5><h6><p><span><strong><em><b><i><u><a><ol><ul><li><br><table><tbody><tr><td><thead><th>');
                });
                //endregion Sanitizer

                //region Text & image processing
                $update = array();
                $post_images = array();
                $post_images_raw = array();

                try {
                    $allowed_amount_of_images = (int) config('max_blogs_photos_in_text');
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
                        $post_images_replaced
                    ));

                } catch (\Exception $exception) {
                    switch ($exception->getCode()) {
                        case self::IMAGES_AMOUNT_EXCEEDED:
                            $message = translate('validation_cannot_upload_more_than_photos_messge', ['{{NUMBER}}' => $allowed_amount_of_images]);

                            break;
                        case self::IMAGES_INVALID_DOMAIN:
                            $message = translate('validation_image_cannot_have_external_link');

                            break;
                        default:
                            $message = translate('systmess_error_cant_edit_blog_now');

                            break;
                    }

                    jsonResponse($message);
                }
                //endregion Text & image processing
                $images_to_optimization = array();
                //region Inline images
                // Coppying inline images
                /** @var FilesystemProviderInterface */
                $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                $publicDisk = $storageProvider->storage('public.storage');
                $tempDisk = $storageProvider->storage('temp.storage');
                $publicDiskPrefixer = $storageProvider->prefixer('public.storage');

                if (!empty($post_images)) {

                    foreach ($post_images as $inlineImage) {
                        $inlineImage = ltrim($inlineImage, '/');
                        $inlineImageName = pathinfo($inlineImage, PATHINFO_BASENAME);

                        if (!$tempDisk->fileExists(FilePathGenerator::uploadedFile($inlineImageName))) {
                            continue;
                        }

                        try {
                            $publicDisk->write(
                                BlogsPathGenerator::publicInlineImageBlogsPath($post_id, $inlineImageName),
                                $tempDisk->read(FilePathGenerator::uploadedFile($inlineImageName))
                            );
                        } catch (\Throwable $th) {
                            jsonResponse(translate('validation_images_upload_fail'));
                        }

                        $images_to_optimization[] = [
                            'file_path'	=> $publicDiskPrefixer->prefixPath(BlogsPathGenerator::publicImageBlogsPath($post_id, $inlineImageName)),
                            'type'		=> 'blog_text_photo',
                            'context'	=> array('id_blog' => $post_id),
                        ];
                    }
                }

                // Update image stats
                $post_processed_images = $this->get_images_from_text($post_content);
                $post_inline_images = $this->get_images_stats($post_processed_images);
                $post_inline_images = arrayByKey($post_inline_images, 'name');

                $post_inline_files = $publicDisk->listContents(BlogsPathGenerator::publicInlineBlogsPath($post_id));
                $post_delete_queue = array();
                foreach ($post_inline_files as $file) {
                    if (substr($file->path(), -5) === '.webp') {

                        continue;
                    }

                    $filename = basename($file->path());

                    if (!isset($post_inline_images[$filename])) {
                        $post_delete_queue[] = $file->path();
                    }
                }
                $post_inline_images = json_encode(array_values($post_inline_images), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                //endregion Inline images

                //region Headline image
                // Copy article image
                $photo = null;
                if (!empty($images = request()->request->get('images'))) {
                    if (!$tempDisk->fileExists($images[0])) {
                        jsonResponse(translate('systmess_error_invalid_data'));
                    }

                    $mainImageModule = 'blogs.main';
                    $mainImageThumbs = config("img.{$mainImageModule}.thumbs");
                    $mainImageName = pathinfo($images[0], PATHINFO_BASENAME);

                    try {
                        $publicDisk->write(
                            BlogsPathGenerator::publicImageBlogsPath($post_id, $mainImageName),
                            $tempDisk->read(FilePathGenerator::uploadedFile($mainImageName))
                        );
                    } catch (\Throwable $th) {
                        jsonResponse(translate('validation_images_upload_fail'));
                    }

                    foreach ($mainImageThumbs as $mainImageThumb) {
                        $thumbName = str_replace('{THUMB_NAME}', $mainImageName, $mainImageThumb['name']);

                        try {
                            $publicDisk->write(
                                BlogsPathGenerator::publicImageBlogsPath($post_id, $thumbName),
                                $tempDisk->read(dirname(FilePathGenerator::uploadedFile($mainImageName)). '/' . $thumbName)
                            );
                        } catch (\Throwable $th) {
                            jsonResponse(translate('validation_images_upload_fail'));
                        }
                    }

                    $photo = $mainImageName;

                    $images_to_optimization[] = array(
                        'file_path'	=> $publicDiskPrefixer->prefixPath(BlogsPathGenerator::publicImageBlogsPath($post_id, $mainImageName)),
                        'type'		=> 'blog_main_photo',
                        'context'	=> array('id_blog' => $post_id)
                    );

                    if (!empty($post['photo'])) {
                        try {
                            $publicDisk->delete(BlogsPathGenerator::publicImageBlogsPath($post_id, $post['photo'])) ;
                        } catch (\Throwable $th) {
                            jsonResponse(translate('validation_images_upload_fail'));
                        }

                        if (!empty($mainImageThumbs = config("img.blogs.main.thumbs"))) {

                            foreach ($mainImageThumbs as $mainImageThumb) {
                                $publicDisk->delete(BlogsPathGenerator::publicImageBlogsPath($post_id, str_replace(
                                    '{THUMB_NAME}',
                                    $post['photo'],
                                    $mainImageThumb['name'])
                                ));
                            }
                        }
                    }
                }
                //endregion Headline image

                //region Update
                // Fix status
                $status = 'new';
                if (have_right('blogs_administration') && !empty($_POST['status'])) {
                    $status = 'moderated';
                }

                $update = array(
                    'id_category'       => $category_id,
                    'id_country'        => $country_id,
                    'title'             => $_POST['title'],
                    'title_slug'        => strForUrl($_POST['title']),
                    'short_description' => $_POST['short_description'],
                    'description'       => $_POST['description'],
                    'content'           => $post_content,
                    'status'            => $status,
                    'tags'              => implode(',', $tags),
                    'lang'              => $language_code,
                    'visible'           => (int) filter_var($_POST['visible'], FILTER_VALIDATE_BOOLEAN),
                    'inline_images'     => $post_inline_images,
                );

                if (isset($_POST['photo_caption'])) {
                    $update['photo_caption'] = cleanInput($_POST['photo_caption']);
                }

                if (isset($_POST['publish_on'])) {
                    $update['publish_on'] = getDateFormat(cleanInput($_POST['publish_on']), 'm/d/Y', 'Y-m-d');

                    if ($update['publish_on'] == date('Y-m-d')) {
                        $update['published'] = 1;
                    }
                }

                if (null !== $photo) {
                    $update['photo'] = $photo;
                }

                if (!$this->blog->update_blog($post_id, $update, 'moderated' == $status)) {
                    jsonResponse(translate('systmess_error_blog_was_moderated'));
                }
                //endregion Update
                //region Clean inline images
                $prefixer = $storageProvider->prefixer('public.storage');
                $projectDir = $this->getContainer()->getParameter('kernel.project_dir');
                if (!empty($post_delete_queue)) {
                    foreach ($post_delete_queue as $image) {
                        $prefixPath = $prefixer->stripPrefix($projectDir . '/' . $image);

                        try {
                            $publicDisk->delete($prefixPath);
                        } catch (\Throwable $th) {
                            //do nothing;
                        }
                    }
                }
                //endregion Clean inline images

                if (!empty($images_to_optimization)) {
                    model(Image_optimization_Model::class)->add_records($images_to_optimization);
                }

                jsonResponse(translate('systmess_success_changes_has_been_saved'), 'success');

            break;
            case 'add_blog':
                checkPermisionAjax('manage_blogs,blogs_administration');

                //region Validation
                $validator_rules = array(
                    array(
                        'field' => 'country',
                        'label' => translate('blog_dashboard_modal_field_country_label_text'),
                        'rules' => array('integer' => ''),
                    ),
                    array(
                        'field' => 'blog_lang',
                        'label' => translate('blog_dashboard_modal_field_language_label_text'),
                        'rules' => array('required' => ''),
                    ),
                    array(
                        'field' => 'category',
                        'label' => translate('blog_dashboard_modal_field_category_label_text'),
                        'rules' => array('required' => '', 'integer' => ''),
                    ),
                    array(
                        'field' => 'title',
                        'label' => translate('blog_dashboard_modal_field_title_label_text'),
                        'rules' => array('required' => '', 'max_len[250]' => ''),
                    ),
                    array(
                        'field' => 'short_description',
                        'label' => translate('blog_dashboard_modal_field_description_label_text'),
                        'rules' => array('required' => '', 'max_len[500]' => ''),
                    ),
                    array(
                        'field' => 'description',
                        'label' => translate('blog_dashboard_modal_field_description_label_text'),
                        'rules' => array('required' => '', 'max_len[200]' => ''),
                    ),
                    array(
                        'field' => 'content',
                        'label' => translate('blog_dashboard_modal_field_content_label_text'),
                        'rules' => array('required' => '', 'html_max_len[20000]' => ''),
                    ),
                    array(
                        'field' => 'upload_folder',
                        'label' => 'Upload folder',
                        'rules' => array('required' => ''),
                    ),
                    array(
                        'field' => 'photo_caption',
                        'label' => "Caption Main photo",
                        'rules' => array('max_len[250]' => ''),
                    ),
                    array(
                        'field' => 'images',
                        'label' => 'Main photo',
                        'rules' => array('required' => ''),
                    ),
                );

                if (have_right('blogs_administration')) {
                    $validator_rules[] = [
                        'field' => 'publish_on',
                        'label' => translate('blog_dashboard_modal_field_publish_on_label_text'),
                        'rules' => [
                            'required'          => '',
                            'valid_date[m/d/Y]' => '',
                            function (string $attr, $value, callable $fail, TinyMVC_Library_validator $validator) {
                                if (empty($value)) {
                                    return;
                                }

                                $currentdate = (new DateTimeImmutable())->setTime(0, 0, 0, 0);
                                $publishDate = DateTimeImmutable::createFromFormat('m/d/Y', $value)->setTime(0, 0, 0, 0);
                                if ($publishDate < $currentdate) {
                                    $fail(translate('systmess_error_incorrect_publish_date'));
                                }
                            },
                        ],
                    ];
                }

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }
                //endregion Validation

                //region Language check
                $language_code = cleanInput($_POST['blog_lang']);
                if (
                    empty($language_code) ||
                    empty($language = model('translations')->get_language_by_iso2($language_code, array('lang_active' => 1, 'lang_url_type' => "domain")))
                ) {
                    jsonResponse(translate('systmess_error_language_not_exist'));
                }
                //endregion Language check

                //region Category check
                $category_id = (int) $_POST['category'];
                if (
                    empty($category_id) ||
                    empty($category = $this->blog->get_category($category_id, $language_code))
                ) {
                    jsonResponse(translate('systmess_error_category_blog_not_exist'));
                }
                //endregion Category check

                //region Country check
                // Check if country is valid
                $country_id = empty($_POST['country']) ? 0 : (int) cleanInput($_POST['country']);
                if (0 !== $country_id) {
                    $country = model('country')->get_country($country_id);
                    if (empty($country)) {
                        jsonResponse(translate('systmess_error_country_not_exist'), 'error');
                    }
                } else {
                    $country_id = 0;
                }
                //endregion Country check

                //region Tags cleanup
                // Prepare tags
                $tags = array();
                if (!empty($_POST['tags'])) {
                    if(!is_array($_POST['tags'])){
                        $_POST['tags'] = explode(';', $_POST['tags']);
                    }

                    foreach ($_POST['tags'] as $tag) {
                        $temp_tag_size = mb_strlen($tag);
                        if ($temp_tag_size >= 3 && $temp_tag_size <= 30 && model('elasticsearch_badwords')->is_clean($tag)) {
                            $tags[] = $tag;
                        }
                    }
                }
                if (empty($tags)) {
                    jsonResponse(sprintf(translate('validation_is_required'), 'Tags'));
                }
                //endregion Tags cleanup

                //region Sanitizer
                // Load sanitize library
                $sanitizer = tap(library('Cleanhtml', 'clean'), function ($sanitizer) {
                    $sanitizer->allowIframes();
                    $sanitizer->defaultTextarea([
                        'attribute' => 'data-video,colspan,rowspan,dir',
                        'style' => 'text-align, padding-left, padding-right'
                    ]);
                    $sanitizer->addAdditionalTags('<img><figure><figcaption><h2><h3><h4><h5><h6><p><span><strong><em><b><i><u><a><ol><ul><li><br><table><tbody><tr><td><thead><th>');
                });
                //endregion Sanitizer

                //region Create post
                // Prepare insert data
                $post_title = cleanInput($_POST['title']);
                $insert = array(
                    'id_category'       => $category_id,
                    'id_country'        => $country_id,
                    'id_user'           => $id_user,
                    'title'             => $post_title,
                    'title_slug'        => strForUrl($post_title),
                    'short_description' => cleanInput($_POST['short_description']),
                    'description'       => cleanInput($_POST['description']),
                    'tags'              => implode(',', $tags),
                    'lang'              => $language_code,
                    'visible'           => (int) filter_var($_POST['visible'], FILTER_VALIDATE_BOOLEAN),
                    'publish_on'        => date('Y-m-d'),
                    'published'         => 1,
                );

                if (isset($_POST['photo_caption'])) {
                    $insert['photo_caption'] = cleanInput($_POST['photo_caption']);
                }

                if (isset($_POST['publish_on'])) {
                    $currentdate = (new DateTimeImmutable())->setTime(0, 0, 0, 0);
                    $publishDate = DateTimeImmutable::createFromFormat('m/d/Y', $_POST['publish_on'])->setTime(0, 0, 0, 0);
                    if ($publishDate > $currentdate) {
                        $insert['publish_on'] = $publishDate->format('Y-m-d H:i:s');
                        $insert['published'] = 0;
                    }
                }

                if (have_right('blogs_administration')) {
                    $insert['author_type'] = 'admin';
                    if (!empty($_POST['status'])) {
                        $insert['status'] = 'moderated';
                    }
                }

                $post_id = $this->blog->set_blog($insert);
                if (!$post_id) {
                    jsonResponse(translate('systmess_error_cant_add_blog_now'));
                }
                //endregion Create post

                //region Text & image processing
                $update = array();
                $post_images = array();
                $post_images_raw = array();

                try {
                    $allowed_amount_of_images = (int) config('max_blogs_photos_in_text');
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
                        $post_images_replaced
                    ));
                } catch (\Exception $exception) {
                    switch ($exception->getCode()) {
                        case self::IMAGES_AMOUNT_EXCEEDED:
                            $message = translate('validation_cannot_upload_more_than_photos_messge', ['{{NUMBER}}' => $allowed_amount_of_images]);

                            break;
                        case self::IMAGES_INVALID_DOMAIN:
                            $message = translate('validation_image_cannot_have_external_link');

                            break;
                        default:
                            $message = translate('systmess_error_cant_edit_blog_now');

                            break;
                    }

                    $this->delete_blog_post($post_id);
                    jsonResponse($message);
                }
                //endregion Text & image processing
                $images_to_optimization = array();
                //region Inline images

                // Copying inline images
                /** @var FilesystemProviderInterface */
                $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                $tempDisk = $storageProvider->storage('temp.storage');
                $publicDisk = $storageProvider->storage('public.storage');
                $publicDiskPrefixer = $storageProvider->prefixer('public.storage');

                if (!empty($post_images)) {
                    foreach ($post_images as $inlineImage) {
                        $inlineImage = ltrim($inlineImage, '/');
                        $inlineImageName = pathinfo($inlineImage, PATHINFO_BASENAME);

                        if (!$tempDisk->fileExists(FilePathGenerator::uploadedFile($inlineImageName))) {
                            continue;
                        }

                        try {
                            $publicDisk->write(
                                BlogsPathGenerator::publicInlineImageBlogsPath($post_id, $inlineImageName),
                                $tempDisk->read(FilePathGenerator::uploadedFile($inlineImageName))
                            );
                        } catch (\Throwable $th) {
                            jsonResponse(translate('validation_images_upload_fail'));
                        }

                        $images_to_optimization[] = [
                            'file_path'	=> $publicDiskPrefixer->prefixPath(BlogsPathGenerator::publicInlineImageBlogsPath($post_id, $inlineImageName)),
                            'type'		=> 'blog_text_photo',
                            'context'	=> json_encode(array('id_blog' => $post_id))
                        ];
                    }
                }

                // Update image stats
                $post_processed_images = $this->get_images_from_text($update['content']);
                $post_inline_images = $this->get_images_stats($post_processed_images);
                $update['inline_images'] = json_encode(array_values($post_inline_images), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                //endregion Inline images

                //region Headline image
                // Copy article image
                if (!empty($images = request()->request->get('images'))) {

                    if (!$tempDisk->fileExists($images[0])) {
                        $this->delete_blog_post($post_id);

                        jsonResponse(translate('systmess_error_invalid_data'));
                    }

                    $mainImageModule = 'blogs.main';
                    $mainImageThumbs = config("img.{$mainImageModule}.thumbs");
                    $mainImageName = pathinfo($images[0], PATHINFO_BASENAME);

                    try {
                        $publicDisk->write(
                            BlogsPathGenerator::publicImageBlogsPath($post_id, $mainImageName),
                            $tempDisk->read(FilePathGenerator::uploadedFile($mainImageName))
                        );

                        foreach ($mainImageThumbs as $mainImageThumb) {
                            $thumbName = str_replace('{THUMB_NAME}', $mainImageName, $mainImageThumb['name']);

                            $publicDisk->write(
                                BlogsPathGenerator::publicImageBlogsPath($post_id, $thumbName),
                                $tempDisk->read(dirname(FilePathGenerator::uploadedFile($mainImageName)) . '/' . $thumbName)
                            );
                        }
                    } catch (\Throwable $th) {
                        $this->delete_blog_post($post_id);
                        jsonResponse(translate('validation_images_upload_fail'));
                    }

                    // foreach ($mainImageThumbs as $mainImageThumb) {
                    //     $thumbName = str_replace('{THUMB_NAME}', $mainImageName, $mainImageThumb['name']);

                    //     try {
                    //         $publicDisk->write(
                    //             BlogsPathGenerator::publicImageBlogsPath($post_id, $thumbName),
                    //             $tempDisk->read(dirname(FilePathGenerator::uploadedFile($mainImageName)). '/' . $thumbName)
                    //         );
                    //     } catch (\Throwable $th) {
                    //         jsonResponse(translate('validation_images_upload_fail'));
                    //     }
                    // }

                    $update['photo'] = $mainImageName;

                    $images_to_optimization[] = array(
                        'file_path'	=> $publicDiskPrefixer->prefixPath(BlogsPathGenerator::publicImageBlogsPath($post_id, $mainImageName)),
                        'type'		=> 'blog_main_photo',
                        'context'	=> json_encode(array('id_blog' => $post_id))
                    );
                }
                //endregion Headline image

                //region Update
                if (!$this->blog->update_blog($post_id, $update)) {
                    $this->delete_blog_post($post_id);

                    jsonResponse(translate('systmess_internal_server_error'));
                }

                model('user_statistic')->set_users_statistic(array(
                    $id_user => array(
                        'blogs_wrote' => 1,
                    ),
                ));
                //endregion Update

                if (!empty($images_to_optimization)) {
                    model(Image_optimization_Model::class)->add_records($images_to_optimization);
                }

                if (have_right('blogs_administration')) {
                    if (!empty($_POST['status'])) {
                        jsonResponse(translate('systmess_success_blog_save'), 'success');
                    }
                }

                jsonResponse(translate('systmess_success_save_blog_visible_after_moderation'), 'success');

            break;
            case 'change_moderated_blog':
                checkPermisionAjax('blogs_administration');

                $id_blog = (int) $_POST['blog'];
                $blog_info = $this->blog->get_blog($id_blog);

                if (empty($blog_info)) {
                    jsonResponse(translate('systmess_error_does_not_exist_blog'));
                }

                if ('moderated' == $blog_info['status']) {
                    jsonResponse(translate('systmess_error_blog_was_moderated'));
                }

                if ($this->blog->update_blog($id_blog, array('status' => 'moderated'))) {
                    jsonResponse(translate('systmess_success_changes_has_been_saved'), 'success');
                }

                jsonResponse(translate('systmess_error_cant_edit_blog_now'));

            break;
            case 'change_visible_blog':
                checkPermisionAjax('manage_blogs,blogs_administration');

                $id_blog = (int) $_POST['blog'];
                $blog_info = $this->blog->get_blog($id_blog);
                if (empty($blog_info)) {
                    jsonResponse(translate('systmess_error_does_not_exist_blog'));
                }

                if (($blog_info['id_user'] != privileged_user_id()) && !have_right('blogs_administration')) {
                    jsonResponse(translate('systmess_error_does_not_exist_blog'));
                }

                $update = array();
                if ($blog_info['visible']) {
                    $update['visible'] = 0;
                } else {
                    $update['visible'] = 1;
                }

                if ($this->blog->update_blog($id_blog, $update, 'moderated' == $blog_info['status'])) {
                    jsonResponse(translate('systmess_success_blog_save'), 'success');
                }

                jsonResponse(translate('systmess_error_cant_edit_blog_now'));

            break;
            case 'save_category_blog':
                checkPermisionAjax('blogs_administration');

                $validator_rules = array(
                    [
                        'field' => 'name',
                        'label' => 'Name',
                        'rules' => ['required' => ''],
                    ],
                    [
                        'field' => 'special_link',
                        'lable' => 'Special link',
                        'rules' => ['max_len[50]' => ''],
                    ],
                    [
                        'field' => 'page_header',
                        'lable' => 'H1',
                        'rules' => ['required' => '', 'max_len[255]' => ''],
                    ],
                    [
                        'field' => 'page_subtitle',
                        'lable' => 'Subtitle',
                        'rules' => ['required' => '', 'max_len[255]' => ''],
                    ],
                    [
                        'field' => 'meta_title',
                        'lable' => 'Meta title',
                        'rules' => ['required' => '', 'max_len[400]' => ''],
                    ],
                    [
                        'field' => 'meta_description',
                        'lable' => 'Meta description',
                        'rules' => ['required' => '', 'max_len[500]' => ''],
                    ],
                    [
                        'field' => 'meta_keywords',
                        'lable' => 'Meta keywords',
                        'rules' => ['max_len[500]' => ''],
                    ],
                );

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $request = request()->request;

                /** @var Blogs_Categories_Model $blogsCategoriesModel */
                $blogsCategoriesModel = model(Blogs_Categories_Model::class);
                if (
                    !empty($specialLink = (string) $request->get('special_link'))
                    && !empty($blogsCategoriesModel->countAllBy([
                        'scopes' => [
                            'specialLink' => $specialLink,
                        ]
                    ]))
                ) {
                    jsonResponse(translate('systmess_error_edit_category_blog_special_link_already_exist'));
                }

                $tlang = $this->translations->get_language_by_iso2('en');
                $translations_data = array(
                    'en' => array(
                        'lang_name'  => $tlang['lang_name'],
                        'abbr_iso2'  => $tlang['lang_iso2'],
                        'updated_at' => date('Y-m-d H:i:s'),
                    ),
                );

                $insert = [
                    'h1'                => (string) $request->get('page_header'),
                    'subtitle'          => (string) $request->get('page_subtitle'),
                    'name'              => cleanInput($_POST['name']),
                    'url'               => strForUrl($_POST['name']),
                    'special_link'      => $specialLink ?: null,
                    'translations_data' => json_encode($translations_data),
                    'meta_title'        => $request->get('meta_title'),
                    'meta_description'  => $request->get('meta_description'),
                    'meta_keywords'     => $request->get('meta_keywords'),
                ];

                $id_category_blog = $this->blog->set_category_blog($insert);

                if ($id_category_blog) {
                    jsonResponse(translate('systmess_success_blog_category_save'), 'success');
                }

                jsonResponse(translate('systmess_error_blog_category_cant_save_now'));

            break;
            case 'add_category_i18n':
                checkPermisionAjax('blogs_administration,manage_translations');

                $validator_rules = array(
                    [
                        'field' => 'id_category',
                        'label' => 'Category info',
                        'rules' => ['required' => '', 'integer' => ''],
                    ],
                    [
                        'field' => 'lang_category',
                        'label' => 'Language',
                        'rules' => ['required' => ''],
                    ],
                    [
                        'field' => 'name',
                        'label' => 'Name category',
                        'rules' => ['required' => ''],
                    ],
                    [
                        'field' => 'page_header',
                        'lable' => 'H1',
                        'rules' => ['required' => '', 'max_len[255]' => ''],
                    ],
                    [
                        'field' => 'page_subtitle',
                        'lable' => 'Subtitle',
                        'rules' => ['required' => '', 'max_len[255]' => ''],
                    ],
                    [
                        'field' => 'meta_title',
                        'lable' => 'Meta title',
                        'rules' => ['required' => '', 'max_len[400]' => ''],
                    ],
                    [
                        'field' => 'meta_description',
                        'lable' => 'Meta description',
                        'rules' => ['required' => '', 'max_len[500]' => ''],
                    ],
                    [
                        'field' => 'meta_keywords',
                        'lable' => 'Meta keywords',
                        'rules' => ['max_len[500]' => ''],
                    ],
                );

                $this->validator->set_rules($validator_rules);

                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $request = request()->request;

                $id_category = (int) $_POST['id_category'];
                $category = $this->blog->get_blog_category($id_category);
                if (empty($category)) {
                    jsonResponse(translate('systmess_error_category_blog_not_exist'));
                }

                $lang_category = cleanInput($_POST['lang_category']);
                $tlang = $this->translations->get_language_by_iso2($lang_category);
                if (empty($tlang)) {
                    jsonResponse(translate('systmess_error_language_not_exist'));
                }

                $translations_data = json_decode($category['translations_data'], true);
                if (array_key_exists($lang_category, $translations_data)) {
                    jsonResponse(translate('systmess_error_category_translation_exist'));
                }

                $translations_data[$lang_category] = array(
                    'lang_name'  => $tlang['lang_name'],
                    'abbr_iso2'  => $tlang['lang_iso2'],
                    'updated_at' => date('Y-m-d H:i:s'),
                );

                $insert = [
                    'h1'                => (string) $request->get('page_header'),
                    'subtitle'          => (string) $request->get('page_subtitle'),
                    'id_category'       => $id_category,
                    'name'              => cleanInput($_POST['name']),
                    'url'               => strForUrl($_POST['name']),
                    'lang_category'     => $lang_category,
                    'meta_title'        => $request->get('meta_title'),
                    'meta_description'  => $request->get('meta_description'),
                    'meta_keywords'     => $request->get('meta_keywords'),
                ];

                if ($this->blog->set_category_blog_i18n($insert)) {
                    $this->blog->update_category_blog($id_category, array('translations_data' => json_encode($translations_data)));
                    jsonResponse(translate('systmess_success_translation_added'), 'success');
                }

                jsonResponse(translate('systmess_error_translation_add'));

            break;
            case 'edit_category_blog':
                checkPermisionAjax('blogs_administration');

                $this->validator->set_rules([
                    [
                        'field' => 'name',
                        'label' => 'Name',
                        'rules' => ['required' => ''],
                    ],
                    [
                        'field' => 'category',
                        'lable' => 'category',
                        'rules' => ['required' => '', 'integer' => ''],
                    ],
                    [
                        'field' => 'special_link',
                        'lable' => 'Special link',
                        'rules' => ['max_len[50]' => ''],
                    ],
                    [
                        'field' => 'page_header',
                        'lable' => 'H1',
                        'rules' => ['required' => '', 'max_len[255]' => ''],
                    ],
                    [
                        'field' => 'page_subtitle',
                        'lable' => 'Subtitle',
                        'rules' => ['required' => '', 'max_len[255]' => ''],
                    ],
                    [
                        'field' => 'meta_title',
                        'lable' => 'Meta title',
                        'rules' => ['required' => '', 'max_len[400]' => ''],
                    ],
                    [
                        'field' => 'meta_description',
                        'lable' => 'Meta description',
                        'rules' => ['required' => '', 'max_len[500]' => ''],
                    ],
                    [
                        'field' => 'meta_keywords',
                        'lable' => 'Meta keywords',
                        'rules' => ['max_len[500]' => ''],
                    ],
                ]);

                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $request = request()->request;

                /** @var Blogs_Categories_Model $blogsCategoriesModel */
                $blogsCategoriesModel = model(Blogs_Categories_Model::class);

                if (
                    empty($categoryId = $request->getInt('category'))
                    || empty($category = $blogsCategoriesModel->findOne($categoryId))
                ) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                $specialLink = (string) $request->get('special_link');
                if (
                    !empty($specialLink)
                    && $specialLink !== $category['special_link']
                    && !empty($blogsCategoriesModel->countAllBy([
                        'scopes' => [
                            'specialLink' => $specialLink,
                        ]
                    ]))
                ) {
                    jsonResponse(translate('systmess_error_edit_category_blog_special_link_already_exist'));
                }

                $category['translations_data']['en']['updated_at'] = (new DateTime())->format('Y-m-d H:i:s');

                if (!$blogsCategoriesModel->updateOne($categoryId, [
                    'h1'                => (string) $request->get('page_header'),
                    'subtitle'          => (string) $request->get('page_subtitle'),
                    'name'              => $request->get('name'),
                    'special_link'      => $specialLink ?: null,
                    'url'               => strForUrl($request->get('name')),
                    'translations_data' => $category['translations_data'],
                    'meta_title'        => $request->get('meta_title'),
                    'meta_description'  => $request->get('meta_description'),
                    'meta_keywords'     => $request->get('meta_keywords'),
                ])) {
                    jsonResponse(translate('systmess_internal_server_error'));
                }

                jsonResponse(translate('systmess_success_edit_blog_category'), 'success');

            break;
            case 'edit_category_i18n':
                checkPermisionAjax('blogs_administration,manage_translations');

                $this->validator->set_rules([
                    [
                        'field' => 'id_category_i18n',
                        'label' => 'Category info',
                        'rules' => ['required' => '', 'integer' => ''],
                    ],
                    [
                        'field' => 'name',
                        'label' => 'Name category',
                        'rules' => ['required' => ''],
                    ],
                    [
                        'field' => 'page_header',
                        'lable' => 'H1',
                        'rules' => ['required' => '', 'max_len[255]' => ''],
                    ],
                    [
                        'field' => 'page_subtitle',
                        'lable' => 'Subtitle',
                        'rules' => ['required' => '', 'max_len[255]' => ''],
                    ],
                    [
                        'field' => 'meta_title',
                        'lable' => 'Meta title',
                        'rules' => ['required' => '', 'max_len[400]' => ''],
                    ],
                    [
                        'field' => 'meta_description',
                        'lable' => 'Meta description',
                        'rules' => ['required' => '', 'max_len[500]' => ''],
                    ],
                    [
                        'field' => 'meta_keywords',
                        'lable' => 'Meta keywords',
                        'rules' => ['max_len[500]' => ''],
                    ],
                ]);

                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $request = request()->request;

                /** @var Blogs_Categories_I18n_Model $blogsCategoriesI18nModel */
                $blogsCategoriesI18nModel = model(Blogs_Categories_I18n_Model::class);

                if (
                    empty($categoryI18nId = $request->getInt('id_category_i18n'))
                    || empty($categoryI18n = $blogsCategoriesI18nModel->findOne($categoryI18nId))) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                /** @var Blogs_Categories_Model $blogsCategoriesModel */
                $blogsCategoriesModel = model(Blogs_Categories_Model::class);

                $category = $blogsCategoriesModel->findOne($categoryI18n['id_category']);

                if (!$blogsCategoriesI18nModel->updateOne($categoryI18nId, [
                    'url'               => strForUrl((string) $request->get('name')),
                    'name'              => (string) $request->get('name'),
                    'h1'                => (string) $request->get('page_header'),
                    'subtitle'          => (string) $request->get('page_subtitle'),
                    'meta_title'        => (string) $request->get('meta_title'),
                    'meta_description'  => (string) $request->get('meta_description'),
                    'meta_keywords'     => (string) $request->get('meta_keywords'),
                ])) {
                    jsonResponse(translate('systmess_internal_server_error'));
                }

                $category['translations_data'][$categoryI18n['lang_category']]['updated_at'] = (new DateTime())->format('Y-m-d H:i:s');
                $blogsCategoriesModel->updateOne($categoryI18n['id_category'], ['translations_data' => $category['translations_data']]);

                jsonResponse(translate('systmess_success_edit_blog_category'), 'success');
            break;
            case 'remove_category_blog':
                checkPermisionAjax('blogs_administration');

                $id_category = (int) $_POST['category'];
                $category_info = $this->blog->get_blog_category($id_category);
                if (empty($category_info)) {
                    jsonResponse(translate('systmess_error_category_blog_not_exist'));
                }

                if ($this->blog->delete_category_blog($id_category)) {
                    jsonResponse(translate('systmess_success_category_delete'), 'success');
                }

                jsonResponse(translate('systmess_error_category_cannot_delete'));

            break;
            case 'get_blog_categories':
                if (!logged_in()) {
                    jsonResponse(translate("systmess_error_should_be_logged"));
                }

                $validator_rules = array(
                    array(
                        'field' => 'blog_lang',
                        'label' => 'Blog language',
                        'rules' => array('required' => ''),
                    ),
                );

                $this->validator->set_rules($validator_rules);

                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $lang_category = cleanInput($_POST['blog_lang']);
                if ('en' == $lang_category) {
                    $blog_categories = $this->blog->get_blog_categories();
                } else {
                    $tlang = $this->translations->get_language_by_iso2($lang_category, array('lang_active' => 1, 'lang_url_type' => "domain"));
                    if (empty($tlang)) {
                        jsonResponse(translate('systmess_error_language_not_exist'));
                    }

                    $blog_categories = $this->blog->get_blog_categories_i18n(array('lang_category' => $lang_category));
                }

                jsonResponse('', 'success', array('categories' => $blog_categories));

            break;
            default:
                jsonResponse(translate('systmess_error_route_not_found'));

            break;
        }
    }

    public function ajax_blog_upload_photo()
    {
        checkIsAjax();
        checkIsLoggedAjax();
        checkPermisionAjax('manage_blogs,blogs_administration');

        /** @var null|UploadedFile */
        $uploadedFile = ((array) request()->files->get('files', []))[0] ?? null;
        if (empty($uploadedFile) || !$uploadedFile->isValid()) {
            jsonResponse(translate('systmess_error_select_file_to_upload'));
        }

        /** @var FilesystemProviderInterface */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $tempDisk = $storageProvider->storage('temp.storage');
        $tempDiskPrefixer = $storageProvider->prefixer('temp.storage');
        $imageName = sprintf('%s.%s', \bin2hex(\random_bytes(16)), 'jpg');

        $tempDisk->createDirectory(
            $uploadDirectory = dirname(FilePathGenerator::uploadedFile($imageName))
        );

        /** @var TinyMVC_Library_Image_intervention $interventionImageLibrary */
        $interventionImageLibrary = library(TinyMVC_Library_Image_intervention::class);
        $images = $interventionImageLibrary->image_processing(
            ['tmp_name' => $uploadedFile->getRealPath(), 'name' => pathinfo($imageName, PATHINFO_FILENAME)],
            [
                'use_original_name' => true,
                'destination'       => $tempDiskPrefixer->prefixDirectoryPath($uploadDirectory),
                'quality'           => 100,
                'rules'             => [
                    'size'       => config('fileupload_max_file_size'),
                    'min_width'  => config('blogs_photos_main_min_width'),
                    'min_height' => config('blogs_photos_main_min_height'),
                    'format'     => 'jpg,jpeg,png,bmp',
                ],
                'handlers'          => [
                    'create_thumbs' => config("img.blogs.main.thumbs"),
                    'resize'        => config("img.blogs.main.resize"),
                ],
            ]
        );

        if (!empty($images['errors'])) {
            jsonResponse($images['errors']);
        }

        $result = [];
        foreach ($images as $image) {
            $result['files'][] = [
                'url'  => $tempDisk->url($uploadDirectory . '/' . $images[0]['new_name']),
                'path' => "{$uploadDirectory}/{$image['new_name']}",
                'name' => $image['new_name'],
            ];
        }

        jsonResponse('', 'success', $result);
    }

    /**
     * @deprecated [2022-07-09]
     * <not used>
     */

    // public function ajax_blog_delete_files()
    // {
    //     checkIsAjax();
    //     checkIsLoggedAjax();
    //     checkPermisionAjax('manage_blogs,blogs_administration');

    //     if (empty($_POST['file'])) {
    //         jsonResponse('File name is not correct.');
    //     }

    //     $upload_folder = $this->uri->segment(3);
    //     if (!($upload_folder = checkEncriptedFolder($upload_folder))) {
    //         jsonResponse('File upload path is not correct.');
    //     }

    //     $path = 'temp/blogs/' . id_session() . '/' . $upload_folder . '/main';
    //     if (!is_dir($path)) {
    //         jsonResponse('Upload path is not correct.');
    //     }

    //     @unlink($path . '/' . $_POST['file']);

    //     jsonResponse('', 'success');
    // }

    // public function ajax_blog_delete_db_photo()
    // {
    //     checkIsAjax();
    //     checkIsLoggedAjax();
    //     checkPermisionAjax('manage_blogs,blogs_administration');

    //     $id_blog = (int) $_POST['file'];
    //     $this->load->model('Blog_Model', 'blog');

    //     $blog_info = $this->blog->get_blog($id_blog);
    //     if (empty($blog_info)) {
    //         jsonResponse('The blog does not exist.');
    //     }

    //     if (($blog_info['id_user'] != privileged_user_id()) && !have_right('blogs_administration')) {
    //         jsonResponse('The blog does not exist.');
    //     }

    //     $path_to_folder = getcwd() . DS . getImgPath('blogs.main', ['{ID}' => $blog_info['id']]);
	// 	$image_path = $path_to_folder . $blog_info['photo'];
    //     $image_path_info = pathinfo($image_path);
	// 	$image_path_glob = $path_to_folder . '*' .  $image_path_info['filename'] . '.*';

	// 	removeFileByPatternIfExists($image_path, $image_path_glob);

    //     $this->blog->update_blog($id_blog, array('photo' => ''), 'moderated' == $blog_info['status']);

    //     jsonResponse('Blog image has been deleted.', 'success');
    // }

    /* Used on home page */
    public function ajax_get_blogs() {
        checkIsAjax();

        $blogs = $this->indexedBlogDataProvider->getBlogsForHomePage();

        if (empty($blogs)) {
            jsonResponse('', 'success', ['blogs' => []]);
        }

        $blogs = array_map(
            function($blog) {
                $blog['imagePath'] = $this->storage->url(BlogsPathGenerator::publicImageBlogsPath($blog['id'], $blog['photo']));

                return $blog;
            },
            $blogs
        );

        jsonResponse('', 'success', ['blogs' => views()->fetch('new/home/components/ajax/blogs_view', compact('blogs'))]);
    }


    public function upload_photo()
    {
        checkIsLoggedAjax();
        checkPermisionAjax('manage_blogs,blogs_administration');

        if (empty($_FILES)) {
            jsonResponse(translate('systmess_error_file_name_not_correct'));
        }

        /** @var null|UploadedFile */
        $uploadedFile = (request()->files->get('userfile'));

        if (empty($uploadedFile)) {
            jsonResponse(translate('systmess_error_file_upload_path_not_correct'));
        }

        /** @var FilesystemProviderInterface */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $tempDisk = $storageProvider->storage('temp.storage');
        $tempDiskPrefixer = $storageProvider->prefixer('temp.storage');
        $imageName = sprintf('%s.%s', \bin2hex(\random_bytes(16)), 'jpg');
        $inlineImagesModule = 'blogs.inline';

        $tempDisk->createDirectory(
            $uploadDirectory = dirname(FilePathGenerator::uploadedFile($imageName))
        );

        /** @var TinyMVC_Library_Image_intervention $interventionImageLibrary */
        $interventionImageLibrary = library(TinyMVC_Library_Image_intervention::class);

        $images = $interventionImageLibrary->image_processing(
            ['tmp_name' => $uploadedFile->getRealPath(), 'name' => pathinfo($imageName, PATHINFO_FILENAME)],
            [
                'use_original_name' => true,
                'quality'           => 100,
                'destination'       => $tempDiskPrefixer->prefixDirectoryPath($uploadDirectory),
                'rules'             => config("img.{$inlineImagesModule}.rules"),
                'handlers'          => [
                    'resize' => config("img.{$inlineImagesModule}.resize"),
                ],
            ]
        );

        if (!empty($images['errors'])) {
            jsonResponse($images['errors']);
        }

        jsonResponse('/public/temp/' . $uploadDirectory .'/' . $images[0]['new_name'], 'success');
    }

    public function update_blogs_slug()
    {
        $this->load_main();
        $blogs = $this->blog->get_blogs(array('per_p' => 10000));
        foreach ($blogs as $blog) {
            $update = array(
                'title_slug' => strForUrl($blog['title']),
            );

            $this->blog->update_blog($blog['id'], $update, false);
        }
    }

    public function update_images_stats()
    {
        checkIsLogged();
        checkPermision('blogs_administration');

        $this->load_main();

        foreach ($this->blog->get_all_posts() as $key => $post) {
            $post_id = $post['id'];

            try {
                $processed = $this->process_content_images($post['content'], 10, $post['id']);
                $images = $this->get_images_stats($processed['collected']);

                $this->blog->update_blog($post_id, array('inline_images' => json_encode($images, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)));
            } catch (Exception $exception) {
                switch ($exception->getCode()) {
                    case self::IMAGES_AMOUNT_EXCEEDED:
                        $this->blog->update_blog($post_id, array('images_limit_exceeded' => 1));

                        break;
                    case self::IMAGES_INVALID_DOMAIN:
                        $this->blog->update_blog($post_id, array('has_external_images' => 1));

                        break;
                    default:
                        break;
                }
            }
        }
    }

    private function delete_blog_post($post_id, $author_id = null)
    {
        if (empty($post_id)) {
            return false;
        }

        if (!$this->blog->delete_blog((int) $post_id)) {
            return false;
        }

        /** @var FilesystemProviderInterface */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $publicDisk = $storageProvider->storage('public.storage');
        $path = BlogsPathGenerator::publicBlogsPath($post_id);

            try {
                $publicDisk->deleteDirectory($path);
            } catch (\Throwable $th) {
                jsonResponse(translate('validation_images_delete_fail'));
            }


        if (null !== $author_id) {
            model('user_statistic')->set_users_statistic(array(
                $author_id => array('blogs_wrote' => -1),
            ));
        }

        return true;
    }

    private function load_main()
    {
        $this->load->model('Category_Model', 'category');
        $this->load->model('Blog_Model', 'blog');
        $this->load->model('Items_Model', 'items');
        $this->load->model('User_Model', 'user');
    }

    private function process_content_images($text, $allowed_amount, $post_id = null)
    {
        $matches = array();
        $images_in_text = $this->get_images_from_text($text, $matches);
        if (empty($images_in_text)) {
            return array();
        }

        if (count($images_in_text) > $allowed_amount) {
            throw new Exception(translate('systmess_error_images_allowed_amount_exceed'), self::IMAGES_AMOUNT_EXCEEDED);
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
            throw new Exception(translate('systmess_error_external_image_not_allowed'), self::IMAGES_INVALID_DOMAIN);
        }

        /** @var FilesystemProviderInterface */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $publicDisk = $storageProvider->storage('public.storage');
        $publicBaseUrl = $publicDisk->url(BlogsPathGenerator::publicBlogsPath($post_id));
        $publicUrlPath = ltrim(\parse_url($publicBaseUrl, PHP_URL_PATH), '\\/');
        $pattern = sprintf("/^\/?%s(.+)$/i", preg_quote($publicUrlPath, '/'));

        $path = null;
        if (null !== $post_id) {
            $path = $publicUrlPath;
        }

        $images_paths = array();
        $temporary_images = array_filter($matches[1]);
        foreach ($temporary_images as $key => $image) {
            if (null !== parse_url($image, PHP_URL_HOST)) {
                if (null !== $path && false !== strpos($image, $path)) {
                    continue;
                }

                if (false === strpos($image, __HTTP_HOST_ORIGIN)) {
                    throw new Exception(translate('systmess_error_external_image_not_allowed'), self::IMAGES_INVALID_DOMAIN);

                    continue;
                }

                list(, $path) = explode(__HTTP_HOST_ORIGIN, $image);
                $image = '/' . trim($path, '/');
            } else {
                if (!((bool) preg_match($pattern, $image))) {
                    continue;
                }
                // if (!(startsWith($image, '/public/temp') || startsWith($image, 'public/temp'))) {
                //     continue;
                // }
            }

            $images_paths[] = $image;
        }

        return array(
            'collected' => $temporary_images,
            'paths'     => $images_paths,
        );
    }

    private function change_content_paths($text, $post_id, $source, &$result = array())
    {
        if (empty($source)) {
            return $text;
        }

        /** @var FilesystemProviderInterface */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $publicDisk = $storageProvider->storage('public.storage');
        $tempDisk = $storageProvider->storage('temp.storage');
        $tempBaseUrl = $tempDisk->url('/');
        $tempUrlPath = ltrim(\parse_url($tempBaseUrl, PHP_URL_PATH), '\\/');
        $pattern = sprintf("/^\/?%s(.+)$/i", preg_quote($tempUrlPath, '/'));

        $replacements = array();
        foreach ($source as $index => $image) {
            if (!((bool) preg_match($pattern, $image))) {
                continue;
            }

            $imageUrl = $publicDisk->url(BlogsPathGenerator::publicInlineImageBlogsPath($post_id, basename($image)));
            if (false !== strpos($text, $tempUrl = __IMG_URL . trim($image, '/'))) {
                $result[$index] = $replacements[$tempUrl] = $imageUrl;

                continue;
            }

            $result[$index] = $replacements[$image] = $imageUrl;
        }


        return !empty($replacements) ? strtr($text, $replacements) : $text;
    }

    private function get_images_stats($raw)
    {
        if (null === $raw) {
            return array();
        }

        $images = array();
        foreach ($raw as $key => $url) {
            if (null === ($host = parse_url($url, PHP_URL_HOST))) {
                $path = $url;
            } else {
                list(, $path) = explode($host, $url);
            }

            /** @var FilesystemProviderInterface */
            $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
            $tempDisk = $storageProvider->storage('temp.storage');

            list($realpath) = explode('?', $path);
            $projectDir = $this->getContainer()->getParameter('kernel.project_dir');
            $fullpath = $projectDir . '/' . ltrim($realpath, '/');
            $fileName = pathinfo($realpath, PATHINFO_BASENAME);
            if ($tempDisk->fileExists(FilePathGenerator::uploadedFile($fileName))) {
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

    private function get_images_from_text($text, &$matches = array())
    {
        /** @var FilesystemProviderInterface */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $tempDisk = $storageProvider->storage('temp.storage');
        $urlPath = \parse_url($tempDisk->url('/'), PHP_URL_PATH);
        $escapedPath = preg_quote($urlPath, '/');
        $host = preg_quote(__HTTP_HOST_ORIGIN);
        $pattern = '/<img[^>]*?src=["\'](((\/?' . \ltrim($escapedPath, '\\/') . '[^"\'>]+)|(https?\:\/\/([\w]+\.)?' . $host . $escapedPath . '\/public\/temp[^"\'>]+))|(https?\:\/\/([\w]+\.)?' . $host . '[^"\'>]+)|([^"\'\s>]+))["\'][^>]*?>/m';
        preg_match_all($pattern, $text, $matches, PREG_PATTERN_ORDER);
        $images_in_text = array_filter($matches[1]);
        if (empty($images_in_text)) {
            return array();
        }

        return $images_in_text;
    }
}
