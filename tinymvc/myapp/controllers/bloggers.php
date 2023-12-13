<?php

use App\Common\Contracts\Blogs\BlogPostImageThumb;
use App\Email\BloggersAddArticle;
use App\Email\BloggersContact;
use App\Filesystem\BloggersUploadsPathGenerator;
use App\Filesystem\FilePathGenerator;
use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;


use App\DataProvider\IndexedProductDataProvider;
use App\Filesystem\BlogsPathGenerator;
use ExportPortal\Contracts\Filesystem\FilesystemOperator;
use League\Flysystem\FilesystemOperator as FlysystemFilesystemOperator;
use League\Flysystem\PathPrefixer;
use Symfony\Component\DependencyInjection\ContainerInterface;
/**
 * Bloggers application controller.
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [15.12.2021]
 * Controller Refactoring
 *
 * @property \TinyMVC_Load                  $load
 * @property \TinyMVC_View                  $view
 * @property \TinyMVC_Library_URI           $uri
 * @property \TinyMVC_Library_Session       $session
 * @property \TinyMVC_Library_Cookies       $cookies
 * @property \TinyMVC_Library_Upload        $upload
 * @property \TinyMVC_Library_validator     $validator
 * @property \Tinymvc_Library_Mobile_Detect $mobile_detect
 * @property \Blog_Model                    $blog
 * @property \Bloggers_Model                $bloggers
 * @property \Category_Model                $category
 * @property \Country_Model                 $countries
 * @property \Cleanhtml                     $clean
 * @property \Elasticsearch_Badwords_Model  $bad_words
 * @property \Items_Model                   $items
 * @property \Notify_Model                  $notify
 * @property \User_Model                    $user
 * @property \Translations_Model            $translations
 * @property \Logs_Model                    $logs
 *
 * @author Anton Zencenco
 */
class Bloggers_Controller extends TinyMVC_Controller
{
    const IMAGES_AMOUNT_EXCEEDED = 15001;
    const IMAGES_INVALID_DOMAIN = 15002;

    private IndexedProductDataProvider $indexedProductDataProvider;

    private FilesystemOperator $storage;

    private FilesystemOperator $tempStorage;

    private PathPrefixer  $prefixer;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->indexedProductDataProvider = $container->get(IndexedProductDataProvider::class);

        /** @var FilesystemProviderInterface */
        $storageProvider = $container->get(FilesystemProviderInterface::class);

        $this->storage = $storageProvider->storage('public.storage');
        $this->tempStorage = $storageProvider->storage('temp.storage');
        $this->prefixer = $storageProvider->prefixer('public.storage');
    }

    public function index()
    {
        $this->load->model('Bloggers_Model', 'bloggers');
        $this->view->assign(array(
            'meta_params' => array(),
            'meta_data'   => array(),
            'url'         => array(
                'validate_code' => __BLOGGERS_URL . 'bloggers/ajax_validate_code',
                'video'         => tmvc::instance()->my_config['blogger_video_url'],
            ),
        ));
        $this->view->display('new/bloggers/header_view');
        $this->view->display('new/bloggers/index_view');
        $this->view->display('new/bloggers/footer_view');
    }

    public function preview()
    {
        $this->load->model('Bloggers_Model', 'bloggers');
        $this->load->model('Category_Model', 'category');
        $this->load->model('Items_Model', 'items');
        $this->load->model('User_Model', 'user');

        /** @var Blog_Model $mainBlogModel */
        $mainBlogModel = model(Blog_Model::class);

        // Load html clean library
        $this->load->library('Cleanhtml', 'clean');
        $this->clean->allowIframes();
        $this->clean->defaultTextarea([
            'attribute' => 'data-video,colspan,rowspan,dir',
            'style' => 'text-align, padding-left, padding-right'
        ]);
        $this->clean->addAdditionalTags('<img><figure><figcaption><h2><h3><h4><h5><h6><p><span><strong><em><b><i><u><a><ol><ul><li><br>');

        $article_id = (int) cleanInput($this->uri->segment(3));
        if (!empty($article_id)) {
            if (!have_right('bloggers_articles_administration') || !$this->bloggers->is_article_exists($article_id)) {
                show_404();
            }

            $article = $this->bloggers->find_article($article_id);
            $applicant = $this->bloggers->find_applicant($article['id_applicant']);
        } else {
            $email = !empty($_POST['email']) ? cleanInput($_POST['email']) : null;
            $token = !empty($_POST['token']) ? base64_decode($_POST['token']) : null;
            if (
                null === $token ||
                null === $email ||
                null === ($applicant = $this->bloggers->get_first_applicant(array('active_token' => $token, 'email' => $email)))
            ) {
                show_404();
            }
        }

        // Very... strange tag processing
        $blogs_tags = $mainBlogModel->get_blog_tags();
        $blogs_tags = explode(',', $blogs_tags['all_tags']);
        $data['blogs_tags'] = array_count_values($blogs_tags);
        arsort($data['blogs_tags']);
        array_splice($data['blogs_tags'], 21);

        if (!empty($article)) {
            $date = $article['applicant_article_created_at'];
            $title = $article['applicant_article_title'];
            $content = $article['applicant_article_content'];
            $description = $article['applicant_article_description'];
            $category_id = (int) $article['id_article_category'];
            $lang = $article['applicant_article_lang_code'];
            $raw_photo = $this->storage->url(BloggersUploadsPathGenerator::publicMainUploadPath($article['id_article'], $article['applicant_article_photo']));
            $raw_tags = !empty($article['applicant_article_tags']) ? explode(',', $article['applicant_article_tags']) : array();
        } else {
            $date = date('Y-m-d H:i:s');
            $title = !empty($_POST['title']) ? cleanInput($_POST['title']) : 'Preview';
            $content = !empty($_POST['content']) ? $this->clean->sanitize($_POST['content']) : '';
            $description = !empty($_POST['description']) ? cleanInput($_POST['description']) : '';
            $category_id = (int) cleanInput($_POST['category']);
            $lang = !empty($_POST['lang']) ? cleanInput($_POST['lang']) : 'en';
            $raw_photo = !empty($_POST['images'][0]) ? $_POST['images'][0] : ltrim(self::IMAGE_BASE_PATH, '/') . '/no_image/no-image-512x512.png';
            $raw_tags = !empty($_POST['tags']) ? implode(",", $_POST['tags']) : "";
        }

        // Get photo parameters
        $photo = null;
        if(!empty($raw_photo)) {
            $raw_photo_path = $raw_photo;

            $raw_photo_sizes = getimagesize($raw_photo_path);
            $photo = array(
                'url'    => $raw_photo_path,
                'width'  => $raw_photo_sizes[0],
                'height' => $raw_photo_sizes[1],
                'type'   => $raw_photo_sizes[2],
                'attr'   => $raw_photo_sizes[3],
            );
        }

        // Get category
        if (!empty($category_id)) {
            if ('en' == $lang) {
                $category = $mainBlogModel->get_blog_category($category_id);
            } else {
                $localization = $this->translations->get_language_by_iso2($lang, array('lang_active' => 1, 'lang_url_type' => "domain"));
                if (!empty($localization)) {
                    $category = $mainBlogModel->get_blog_category_i18n(array('id_category' => $category_id, 'lang_category' => $lang));
                }
            }
        }

        // Fill the page data
        $data['blog'] = array(
            'date'          => $date,
            'publish_on'    => $date,
            'title'         => $title,
            'slug'          => !empty($title) ? strForURL($title) : 'preview',
            'content'       => $content,
            'description'   => $description,
            'category_name' => !empty($category) ? $category['name'] : '',
            'user_name'     => "{$applicant['applicant_firstname']} {$applicant['applicant_lastname']}",
            'tags'          => $raw_tags,
            'photo'         => [
                'url'       => $photo['url'],
                'width'     => $photo['width'],
                'height'    => $photo['height'],
            ],
        );


        $data['search_bar_active'] = 'blog';
        $data['blog_uri_components'] = tmvc::instance()->site_urls['blog/all']['replace_uri_components'];
        $data['search_form_link'] = __BLOG_URL;
        $data['blogs_categories'] = $mainBlogModel->get_count_blog_by_category();
        $data['blogs_last'] = $mainBlogModel->get_blogs(array('per_p' => 5, 'status' => 'moderated', 'visible' => 1));
        $data['breadcrumbs'] = array(
            array(
                'link'  => __BLOGGERS_URL,
                'title' => 'Bloggers',
            ),
            array(
                'link'  => !empty($article) ? __CURRENT_URL : __CURRENT_URL . '#',
                'title' => $data['blog']['title'],
            ),
        );
        $data['meta_params'] = array(
            '[BLOG_PREVIEW_DESCRIPTION]' => $data['blog']['description'],
            '[image]'                    => image_exist($data['blog']['photo']['url']) ? $data['blog']['photo']['url'] : 'public/img/og-images/600x315_blog.jpg',
        );

        $data['last_items'] = $this->indexedProductDataProvider->getBloggersItems(3);

        $sellers_list = [];
        foreach ($data['last_items'] as $item) {
            $sellers_list[$item['id_seller']] = $item['id_seller'];
        }

        if (!empty($data['last_items'])) {
            $sellers_list = array_column($data['last_items'], 'id_seller', 'id_seller');

            if (!empty($sellers_list)) {
                $sellers = $this->user->getSellersForList(implode(',', $sellers_list), true);
            }

            $items_country_ids = [];

            foreach ($data['last_items'] as $key => $item) {
                $data['last_items'][$key]['seller'] = search_in_array($sellers, 'idu', $item['id_seller']);

                $items_country_ids[$item['p_country']] = $item['p_country'];
            }

            $data['items_country'] = model('country')->get_simple_countries(implode(",", $items_country_ids));
        }

        //end last items
        $params_recommended = [
            'not_id_blog' => $article_id,
            'status'      => 'moderated',
            'visible'     => 1,
            'published'   => 1,
            'per_p'       => (int) config('blogs_recommended_list_per_page'),
            'lang'        => __SITE_LANG,
        ];

        $data['blogs_count'] = $mainBlogModel->counter_by_conditions($params_recommended);
        $data['blogs'] = $mainBlogModel->get_blogs($params_recommended) ?? [];

        /** @var FilesystemProviderInterface */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $publicDisk = $storageProvider->storage('public.storage');
        $data['blogs'] = array_map(function ($blog) use ($publicDisk) {
            $blog['url'] = getBlogUrl($blog);
            $blog['photoSrc'] = $publicDisk->url(BlogsPathGenerator::thumb($blog['id'], $blog['photo'], BlogPostImageThumb::BIG()));
            $blog['photoMainSrc'] = $publicDisk->url(BlogsPathGenerator::publicImageBlogsPath($blog['id'], $blog['photo']));
            $blog['title'] = cleanOutput($blog['title']);

            return $blog;
        }, $data['blogs']);

        $data['content'] = 'blog/detail_view';
        $data['customEncoreLinks'] = true;
        $data['bloggersPreview'] = 'Search blogs';
        $data['title'] = true;

        views()->displayWebpackTemplate($data);
    }

    public function administration()
    {
        checkPermision('bloggers_articles_administration');

        $this->load->model('Blog_Model', 'blog');
        $this->load->model('Bloggers_Model', 'bloggers');
        $this->load->model('Country_Model', 'countries');

        $data['last_article_id'] = $this->bloggers->get_last_article_id();
        $data['categories'] = $this->blog->get_blog_categories();
        $data['countries'] = $this->countries->get_countries();
        $data['languages'] = $this->translations->get_languages();
        $data['counter'] = $this->bloggers->get_article_status_map();

        $this->view->assign($data);
        $this->view->assign('title', 'Bloggers');
        $this->view->display('admin/header_view');
        $this->view->display('new/bloggers/admin/index_view');
        $this->view->display('admin/footer_view');
    }

    public function popup_forms()
    {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        switch (cleanInput($this->uri->segment(3))) {
            case 'article':
                $this->show_apply_article_form();

                break;
            case 'applicant':
                $this->show_applicant_form();

                break;
            case 'contact':
                $this->show_contact_form();

                break;
            case 'applicant_info':
                $this->show_applicant_info();

                break;
            default:
                show_404();

                break;
        }
    }

    /**
     * Validate bloggers access code.
     */
    public function ajax_validate_code()
    {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        $code = cleanInput($_POST['code']);
        if (
            null === $code ||
            $code !== tmvc::instance()->my_config['blogger_access_key']
        ) {
            $this->logger->error("Access to bloggers article application form is denied due to reason: invalid or expired code. Expected code: {stored}. Provided code: {provided}", array(
                'stored'     => tmvc::instance()->my_config['blogger_access_key'],
                'provided'   => $code,
                'meta'       => array(
                    'package'    => 'blog',
                    'subpackage' => 'bloggers',
                    'step'       => 'validate_code',
                ),
            ));

            return jsonResponse('Invalid or expired code used', 'error');
        }

        return jsonResponse(null, 'success', array(
            'location' => __BLOGGERS_URL . 'bloggers/popup_forms/applicant',
        ));
    }

    public function ajax_process_applicant_credentials()
    {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        $this->load->model('Bloggers_Model', 'bloggers');
        $this->load->model('Country_Model', 'countries');

        $validator_rules = array(
            array(
                'field' => 'email',
                'label' => 'Email',
                'rules' => array('required' => '', 'no_whitespaces' => '', 'valid_email' => '', 'max_len[255]' => ''),
            ),
            array(
                'field' => 'firstname',
                'label' => 'Firstname',
                'rules' => array('required' => '', 'min_len[1]' => '', 'max_len[200]' => ''),
            ),
            array(
                'field' => 'lastname',
                'label' => 'Lastname',
                'rules' => array('required' => '', 'min_len[1]' => '', 'max_len[200]' => ''),
            ),
            array(
                'field' => 'country',
                'label' => 'Country',
                'rules' => array('required' => ''),
            ),
            array(
                'field' => 'about',
                'label' => 'About',
                'rules' => array('required' => '', 'max_len[500]' => ''),
            ),
            array(
                'field' => 'strengths',
                'label' => 'Strengths',
                'rules' => array('required' => '', 'max_len[500]' => ''),
            ),
            array(
                'field' => 'hobbies',
                'label' => 'Hobbies',
                'rules' => array('max_len[500]' => ''),
            ),
            array(
                'field' => 'example',
                'label' => 'Example link',
                'rules' => array('required' => '', 'valid_url' => '', 'max_len[2000]' => ''),
            ),
            array(
                'field' => 'portfolio',
                'label' => 'Portfolio link',
                'rules' => array('valid_url' => '', 'max_len[2000]' => ''),
            ),
            array(
                'field' => 'interview_opportunity',
                'label' => 'Interview opportunity',
                'rules' => array('required' => ''),
            ),
            array(
                'field' => 'interview_experience',
                'label' => 'Interview experience',
                'rules' => array('required' => ''),
            ),
        );
        $social_validator_rules = array(
            array(
                'field' => 'facebook',
                'label' => 'Facebook profile',
                'rules' => array('valid_social_media_link[Facebook]' => ''),
            ),
            array(
                'field' => 'twitter',
                'label' => 'Twitter profile',
                'rules' => array('valid_social_media_link[Twitter]' => ''),
            ),
            array(
                'field' => 'instagram',
                'label' => 'Instagram profile',
                'rules' => array('valid_social_media_link[Instagram]' => ''),
            ),
        );

        $this->validator->set_rules($validator_rules);
        if (!$this->validator->validate()) {
            jsonResponse($this->validator->get_array_errors());
        }

        $allowed_socials = array('facebook', 'instagram', 'twitter');
        $socials = !empty($_POST['socials']) ? array_intersect_key($_POST['socials'], array_flip($allowed_socials)) : array();
        $applicant_socials = new \stdClass();
        if(!empty($socials)) {
            $this->validator->reset_postdata();
            $this->validator->clear_array_errors();
            $this->validator->validate_data = $socials;
            $this->validator->set_rules($social_validator_rules);
            if (!$this->validator->validate()) {
                jsonResponse($this->validator->get_array_errors());
            }

            foreach ($socials as $service => $profile) {
                $applicant_socials->{$service} = array(
                    'url'  => $profile,
                    'name' => $allowed_socials[$service]['name'],
                );
            }
        }

        // Check if country is valid
        $country = (int) cleanInput($_POST['country']);
        $country_data = $this->countries->get_country($country);
        if (empty($country_data)) {
            jsonResponse("country doesn't  exists!", 'error');
        }

        // Fetch applicant data
        $email = cleanInput($_POST['email']);
        $applicant = $this->bloggers->get_first_applicant(
            array(
                'email'        => $email,
                'with_article' => true,
            ),
            array(
                'id_article',
                'id_applicant',
                'applicant_email',
                'applicant_firstname',
                'applicant_lastname',
            )
        );
        if (!empty($applicant['id_article'])) {
            jsonResponse('User with this email already applied an article');
        }

        // Generate access token that will last for 1(?) hour
        list($access_token, $access_token_expiration) = $this->generate_token(
            $email,
            tmvc::instance()->my_config['blogger_access_key']
        );

        // Fill the array with applicant data
        $applicant_input_data = array(
            'id_applicant_country'                => $country,
            'applicant_email'                     => $email,
            'applicant_firstname'                 => cleanInput($_POST['firstname']),
            'applicant_lastname'                  => cleanInput($_POST['lastname']),
            'applicant_about'                     => cleanInput($_POST['about']),
            'applicant_strengths'                 => cleanInput($_POST['strengths']),
            'applicant_hobbies'                   => cleanInput($_POST['hobbies']),
            'applicant_portfolio_link'            => cleanInput($_POST['portfolio']),
            'applicant_work_example_link'         => cleanInput($_POST['example']),
            'applicant_has_interview_opportunity' => (int) $_POST['interview_opportunity'],
            'applicant_has_interview_experience'  => (int) $_POST['interview_experience'],
            'applicant_media_pages'               => $applicant_socials,
            'applicant_access_token'              => $access_token,
            'applicant_access_token_expires_at'   => $access_token_expiration,
        );
        if (null !== $applicant) {
            $applicant = array_merge($applicant, $applicant_input_data);
            if (!$this->bloggers->update_applicant($applicant['id_applicant'], $applicant)) {
                jsonResponse('Failed to update applicant');
            }
        } else {
            $applicant = $applicant_input_data;
            if (!$this->bloggers->create_applicant($applicant)) {
                jsonResponse('Failed to add new applicant');
            }
        }
        unset($applicant['id_article']);

        return jsonResponse(
            null,
            'success',
            array(
                'applicant' => array(
                    'email' => $applicant['applicant_email'],
                    'token' => base64_encode($applicant['applicant_access_token']),
                ),
                'location'  => __BLOGGERS_URL . 'bloggers/popup_forms/article',
            )
        );
    }

    public function ajax_apply_article()
    {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        is_allowed('freq_allowed_bloggers_apply_article');

        $this->load->model('Blog_Model', 'blog');
        $this->load->model('Notify_Model', 'notify');
        $this->load->model('Bloggers_Model', 'bloggers');
        $this->load->model('Country_Model', 'countries');
        $this->load->model('Elasticsearch_Badwords_Model', 'bad_words');

        $validator_rules = array(
            array(
                'field' => 'lang',
                'label' => 'Blog language',
                'rules' => array('required' => ''),
            ),
            // array(
            //     'field' => 'country',
            //     'label' => 'Country',
            //     'rules' => array('required' => '', 'integer' => ''),
            // ),
            array(
                'field' => 'title',
                'label' => 'Title',
                'rules' => array('required' => '', 'max_len[250]' => ''),
            ),
            array(
                'field' => 'description',
                'label' => 'Short description',
                'rules' => array('required' => '', 'max_len[500]' => ''),
            ),
            array(
                'field' => 'content',
                'label' => 'Content',
                'rules' => array('required' => '', 'max_len[60000]' => '', 'html_max_len[60000]' => ''),
            ),
            array(
                'field' => 'upload_folder',
                'label' => 'Upload folder',
                'rules' => array('required' => ''),
            ),
            array(
                'field' => 'terms_cond',
                'label' => 'Terms and Conditions',
                'rules' => array('required' => ''),
            ),
        );
        $this->validator->set_rules($validator_rules);
        if (!$this->validator->validate()) {
            jsonResponse($this->validator->get_array_errors());
        }

        // Check access by email-token pair
        $email = !empty($_POST['email']) ? cleanInput($_POST['email']) : null;
        $token = !empty($_POST['token']) ? base64_decode($_POST['token']) : null;
        if (
            null === $token ||
            null === $email ||
            null === ($applicant = $this->bloggers->get_first_applicant(
                array(
                    'active_token' => $token,
                    'email' => $email,
                    'with_article' => true,
                ),
                array(
                    $this->bloggers->prefix_column('applicant', '*'),
                    'id_article',
                )
            ))
        ) {
            // Here be drago... I mean the message of token expiration
            jsonResponse('Your access has been expired. Please renew your access code and try again');
        }
        if (null !== $applicant['id_article']) {
            jsonResponse('You already applied an article', 'warning');
        }

        // Check language
        $lang = cleanInput($_POST['lang']);
        $lang_data = null;
        if(!empty($lang)) {
            $lang_data = $this->translations->get_language_by_iso2($lang, array('lang_active' => 1, 'lang_url_type' => "domain"));
            if (empty($lang_data)) {
                jsonResponse('The language doesn\'t exist.');
            }
        } else {
            $lang = null;
        }

        // Check if country is valid
        // $country_id = empty($_POST['country']) ? 0 : (int) cleanInput($_POST['country']);
        // if (0 !== $country_id) {
        //     $country_data = $this->countries->get_country($country_id);
        //     if (empty($country_data)) {
        //         jsonResponse("This country doesn't exists!", 'error');
        //     }
        // } else {
        //     $country_id = null;
        // }

        // Check tags
        $tags = array();
        if (!empty($_POST['tags'])) {
            foreach ($_POST['tags'] as $tag) {
                $tag = cleanInput($tag);
                if (!empty($tag)) {
                    $tags[] = $tag;
                }
            }
        }

        // Check folder with files
        $upload_folder = checkEncriptedFolder(cleanInput($_POST['upload_folder']));
        if (false === $upload_folder) {
            jsonResponse('File upload path is not correct.');
        }

        // Load html clean library
        $this->load->library('Cleanhtml', 'clean');
        $this->clean->allowIframes();
        $this->clean->defaultTextarea([
            'attribute' => 'data-video,colspan,rowspan,dir',
            'style' => 'text-align, padding-left, padding-right'
        ]);
        $this->clean->addAdditionalTags('<img><figure><figcaption><h2><h3><h4><h5><h6><p><span><strong><em><b><i><u><a><ol><ul><li><br>');

        try {
            $processed_content_data = $this->process_content_images($_POST['content']);
            $article_content = $processed_content_data['text'];
            $article_raw_images = $processed_content_data['images']['collected'];
            $article_images = array_flip(array_flip($processed_content_data['images']['paths']));
        } catch (\Exception $exception) {
            switch ($exception->getCode()) {
                case self::IMAGES_AMOUNT_EXCEEDED:
                    $allowed_amount_of_images = (int) tmvc::instance()->my_config['max_blogs_photos_in_text'];
                    $message = "You cannot upload more than {$allowed_amount_of_images} photos.";

                    break;
                case self::IMAGES_INVALID_DOMAIN:
                    $message = 'One or more URLs lead to an external domain. Images from such domain are not allowed';

                    break;
                default:
                    $message = 'We failed to properly save content of your article. Please contact administration to resolve this issue';

                    break;
            }

            jsonResponse($message);
        }

        $article_data = array(
            'id_applicant'                  => (int) $applicant['id_applicant'],
            'id_article_category'           => null, // $category_id,
            'id_article_lang'               => null !== $lang_data ? (int) $lang_data['id_lang'] : null,
            'applicant_article_lang_code'   => $lang,
            'applicant_article_title'       => cleanInput($_POST['title']),
            'applicant_article_description' => cleanInput($_POST['description']),
            'applicant_article_content'     => $article_content,
            'applicant_article_tags'        => implode(',', $tags),
            'applicant_article_status'      => 'new',
        );
        $article_id = $this->bloggers->create_article($article_data);
        if (!$article_id) {
            jsonResponse('We failed to properly save content of your article. Please contact administration to resolve this issue');
        }

        //Cleaning current article data
        $article_data = array();

        $article_content = $this->change_content_paths($article_content, $article_id, $article_raw_images);

        $article_data['applicant_article_content'] = $this->clean->sanitize($article_content);

        // Copy article image
        if (!empty($images = $_POST['images'])) {
            $mainImageModule = 'bloggers.main';
            $imagePath = request()->request->get('images');
            $imageName = pathinfo($imagePath[0], PATHINFO_BASENAME);
            $path = FilePathGenerator::uploadedFile($imageName);

            if (!is_array($images)) {
                jsonResponse(translate('systmess_error_invalid_data'));
            }

            $image = array_shift($images);
            if (!$this->tempStorage->fileExists($path)) {
                jsonResponse(translate('systmess_error_invalid_data'));
            }

            $imageName = pathinfo($image, PATHINFO_BASENAME);

            try {
                $this->storage->write(
                    BloggersUploadsPathGenerator::publicMainUploadPath($article_id, $imageName),
                    $this->tempStorage->read($path)
                );
            } catch (\Throwable $th) {
                jsonResponse(translate('systmess_cannot_save_picture'));
            }


            $mainImageThumbs = config("img.{$mainImageModule}.thumbs");
            if (!empty($mainImageThumbs)) {
                foreach ($mainImageThumbs as $mainImageThumb) {
                    $thumbName = str_replace('{THUMB_NAME}', $imageName, $mainImageThumb['name']);

                    $this->storage->write(
                        BloggersUploadsPathGenerator::publicMainUploadPath($article_id, $thumbName),
                        $this->tempStorage->read(dirname($path) . '/' . $thumbName)
                    );
                }
            }

            $article_data['applicant_article_photo'] = $imageName;
        }

        // Coppying inline images
        if (!empty($article_images)) {
            foreach ($article_images as $articleImage) {
                $textImageName =  pathinfo($articleImage, PATHINFO_BASENAME);

                $this->storage->write(
                    BloggersUploadsPathGenerator::publicInlineUploadPath($article_id, $textImageName),
                        $this->tempStorage->read(FilePathGenerator::uploadedFile($textImageName))
                );
            }
        }

        if (!$this->bloggers->update_article($article_id, $article_data)) {
            jsonResponse('We failed to properly save content of your article. Please contact administtration to resolve this issue');
        }

        // MAIL USER
        try {
            /** @var MailerInterface $mailer */
            $mailer = $this->getContainer()->get(MailerInterface::class);
            $mailer->send(
                (new BloggersAddArticle())
                    ->from(config('bloggers_support_email'))
                    ->to(new Address($email))
            );
        } catch (\Throwable $th) {
            jsonResponse(translate('email_has_not_been_sent'));
        }

        jsonResponse('Your article has been successfully saved', 'success');
    }

    public function ajax_upload_images()
    {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        /** @var null|UploadedFile */
        $uploadedFile = ((array) request()->files->get('files', []))[0] ?? null;
        if (empty($uploadedFile) || !$uploadedFile->isValid()) {
            jsonResponse('Please select file to upload.');
        }

        /** @var FilesystemProviderInterface */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $tempDisk = $storageProvider->storage('temp.storage');
        $tempDiskPrefixer = $storageProvider->prefixer('temp.storage');
        $imageName = sprintf('%s.%s', \bin2hex(\random_bytes(16)), 'jpg');

        $is_inline = 'inline' === uri()->segment(4);
        $imageModule = $is_inline ? 'bloggers.inline' : 'bloggers.main';

        $tempDisk->createDirectory(
            $uploadDirectory = dirname(FilePathGenerator::uploadedFile($imageName))
        );
        $path = $tempDiskPrefixer->prefixDirectoryPath($uploadDirectory);

        /** @var TinyMVC_Library_Image_intervention $interventionImageLibrary */
        $interventionImageLibrary = library(TinyMVC_Library_Image_intervention::class);

        $result = $interventionImageLibrary->image_processing(
            ['tmp_name' => $uploadedFile->getRealPath(), 'name' => pathinfo($imageName, PATHINFO_FILENAME)],
            [
                'use_original_name' => true,
                'destination'   => $path,
                'rules'         => config("img.{$imageModule}.rules"),
                'handlers'      => [
                    'create_thumbs' => config("img.{$imageModule}.thumbs") ?: [],
                    'resize'        => config("img.{$imageModule}.resize"),
                ],
            ]
        );

        if (!empty($result['errors'])) {
            jsonResponse($result['errors']);
        }

        $response = [];
        foreach ($result as $resultByImage) {
            $response['files'][] = [
                'path' => 'public/temp/' . $uploadDirectory . '/' . $resultByImage['new_name'],
                'name' => $resultByImage['new_name'],
            ];
        }

        jsonResponse(null, 'success', $response);
    }

    public function ajax_delete_images()
    {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        if (empty($_POST['file'])) {
            jsonResponse('File name is not correct.');
        }

        $upload_folder = checkEncriptedFolder($this->uri->segment(3));
        if (false === $upload_folder) {
            jsonResponse('File upload path is not correct.');
        }

        /** @var FilesystemProviderInterface */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $tempDisk = $storageProvider->storage('temp.storage');
        $imageName = request()->request->get('file');
        $path = FilePathGenerator::uploadedFile($imageName);
        $thumbPath = dirname($path);

        try {
            $tempDisk->delete($path);
        } catch (\Throwable $th) {
            jsonResponse(translate('validation_images_delete_fail'));
        }

        if (!empty($mainImageThumbs = config("img.bloggers.main.thumbs"))) {

            foreach ($mainImageThumbs as $mainImageThumb) {
                $thumbName = str_replace('{THUMB_NAME}', $_POST['file'], $mainImageThumb['name']);

                try {
                    $tempDisk->delete($thumbPath . '/' . $thumbName);
                } catch (\Throwable $th) {
                    jsonResponse(translate('validation_images_delete_fail'));
                }

            }
        }

        jsonResponse(null, 'success');
    }

    public function ajax_get_categories()
    {
        if (!isAjaxRequest()) {
            headerRedirect();
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

        $this->load->model('Blog_Model', 'blog');

        $lang_category = cleanInput($_POST['blog_lang']);
        $base_blog_categories = $blog_categories = $this->blog->get_blog_categories();
        if ('en' !== $lang_category) {
            $tlang = $this->translations->get_language_by_iso2($lang_category, array('lang_active' => 1, 'lang_url_type' => "domain"));
            if (!empty($tlang)) {
                $blog_categories = $this->blog->get_blog_categories_i18n(array('lang_category' => $lang_category));
                if (empty($blog_categories)) {
                    $blog_categories = $base_blog_categories;
                }
            }
        }

        jsonResponse(null, 'success', array('categories' => $blog_categories));
    }

    public function ajax_preview_content()
    {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        $this->load->library('Cleanhtml', 'clean');
        $this->clean->allowIframes();
        $this->clean->defaultTextarea([
            'attribute' => 'data-video,colspan,rowspan,dir',
            'style' => 'text-align, padding-left, padding-right'
        ]);
        $this->clean->addAdditionalTags('<img><figure><figcaption><h2><h3><h4><h5><h6><p><span><strong><em><b><i><u><a><ol><ul><li><br>');

        jsonResponse(null, 'success', array('content' => $this->clean->sanitize($_POST['content'])));
    }

    public function ajax_bloggers_administration()
    {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        checkPermisionAjaxDT('bloggers_articles_administration');

        $this->load->model('Bloggers_Model', 'bloggers');

        $columns = array(
            $this->bloggers->prefix_column('article', '*'),
            $this->bloggers->prefix_column('applicant', 'id_applicant_country'),
            $this->bloggers->prefix_column('applicant', 'applicant_firstname'),
            $this->bloggers->prefix_column('applicant', 'applicant_lastname'),
            $this->bloggers->prefix_column('applicant', 'applicant_email'),
            $this->bloggers->prefix_column('country', 'country_name'),
            "{$this->bloggers->prefix_column('country', 'country')} as `country_base_name`",
            "{$this->bloggers->prefix_column('category', 'name')} as `blog_category_name`",
        );
        $conditions = array();
        $order = array();
        $with = array('applicant' => true, 'country' => true, 'category' => true);
        $limit = !empty($_POST['iDisplayLength']) ? $_POST['iDisplayLength'] : null;
        $skip = !empty($_POST['iDisplayStart']) ? $_POST['iDisplayStart'] : null;

        $order = flat_dt_ordering($_POST, array(
            'dt_id_article' => 'id_article',
            'dt_status' => 'applicant_article_status',
            'dt_category' => 'id_article_category',
            'dt_date_created' => 'applicant_article_created_at',
            'dt_country' => 'id_applicant_country',
        ));

        $conditions = dtConditions($_POST, array(
            array('as' => 'lang',          'key' => 'lang',         'type' => 'cleanInput'),
            array('as' => 'search',        'key' => 'keywords',     'type' => 'cleanInput'),
            array('as' => 'category',      'key' => 'category',     'type' => 'cleanInput'),
            array('as' => 'country',       'key' => 'country',      'type' => 'cleanInput'),
            array('as' => 'status',        'key' => 'status',       'type' => 'cleanInput'),
            array('as' => 'created_from',  'key' => 'created_from', 'type' => 'getDateFormat:m/d/Y,Y-m-d'),
            array('as' => 'created_to',    'key' => 'created_to',   'type' => 'getDateFormat:m/d/Y,Y-m-d'),
        ));

        $params = compact('columns', 'conditions', 'order', 'with', 'limit', 'skip');
        $articles = $this->bloggers->get_articles($params);
        $articles_count = $this->bloggers->count_articles($params);
        $languages = arrayByKey($this->translations->get_languages(), 'id_lang');
        $output = array(
            'sEcho'                => intval($_POST['sEcho']),
            'iTotalRecords'        => $articles_count,
            'iTotalDisplayRecords' => $articles_count,
            'aaData'               => array(),
        );

        if (empty($articles)) {
            jsonResponse(null, 'success', $output);
        }

        /** @var FilesystemProviderInterface */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $publicDisk = $storageProvider->storage('public.storage');

        $status_array = array('new' => 'New', 'approved' => 'Approved', 'declined' => 'Declined');
        $status_icons_array = array('new' => 'ep-icon_new txt-blue', 'approved' => 'ep-icon_thumbup txt-green', 'declined' => 'ep-icon_thumbdown txt-red');
        foreach ($articles as $article) {
            $pathToFile = BloggersUploadsPathGenerator::gridThumb($article['id_article'], $article['applicant_article_photo']);
            $is_imported = filter_var($article['applicant_article_is_imported'], FILTER_VALIDATE_BOOLEAN);
            $change_status_url = __SITE_URL . "bloggers/ajax_change_status";
            $preview_applicant_url = __SITE_URL . "bloggers/popup_forms/applicant_info/{$article['id_applicant']}/" . strForURL(trim("{$article['applicant_firstname']} {$article['applicant_lastname']}"));
            $contact_applicant_url = __SITE_URL . "bloggers/popup_forms/contact/{$article['id_applicant']}";
            $details_url = __BLOGGERS_URL . "preview/{$article['id_article']}/{$article['applicant_article_slug']}";

            $photo_url = $publicDisk->url($pathToFile);

            $user_name = "{$article['applicant_firstname']} {$article['applicant_lastname']} <br> (<a href=\"mailto:{$article['applicant_email']}\">{$article['applicant_email']}</a>)";

            $country_name = '';
            if (!empty($article['id_applicant_country'])) {
                $country_flag_path = getCountryFlag($article['country_base_name']);
                $country_name = "
                    <div class=\"tal\">
                        <a class=\"ep-icon ep-icon_filter txt-green dt_filter\"
                        data-title=\"Country\"
                        title=\"Filter by country\"
                        data-value-text=\"{$article['country_name']}\"
                        data-value=\"{$article['id_applicant_country']}\"
                        data-name=\"country\">
                        </a>
                    </div>
                    <img width=\"24\" height=\"24\" src=\"{$country_flag_path}\" title=\"Filter by: {$article['country_name']}\" alt=\"{$article['country_name']}\"/>
                    <br>{$article['country_name']}
                ";
            }

            $lang_name = '';
            if (!empty($languages[$article['id_article_lang']])) {
                $lang_name = "
                    <div class=\"tal\">
                        <a class=\"ep-icon ep-icon_filter txt-green dt_filter\"
                        data-title=\"Language\"
                        title=\"Filter by language\"
                        data-value-text=\"{$languages[$article['id_article_lang']]['lang_name']}\"
                        data-value=\"{$article['id_article_lang']}\"
                        data-name=\"lang\">
                        </a>
                    </div>
                    {$languages[$article['id_article_lang']]['lang_name']}
                ";
            }

            $approve_button = '';
            $decline_button = '';
            if(!$is_imported && 'new' === $article['applicant_article_status']) {
                $approve_button = "<a href=\"#\"
                        title=\"Approve article\"
                        class=\"ep-icon ep-icon_thumbup txt-green confirm-dialog\"
                        data-url=\"{$change_status_url}\"
                        data-status=\"approved\"
                        data-callback=\"change_article_status\"
                        data-article=\"{$article['id_article']}\"
                        data-message=\"Are you sure you want to approve this article?\">
                    </a>";
                $decline_button = "<a href=\"#\"
                        title=\"Decline article\"
                        class=\"ep-icon ep-icon_thumbdown txt-red confirm-dialog\"
                        data-callback=\"change_article_status\"
                        data-url=\"{$change_status_url}\"
                        data-status=\"declined\"
                        data-article=\"{$article['id_article']}\"
                        data-message=\"Are you sure you want to decline this article?\">
                    </a>";
            }

            $output['aaData'][] = array(
                'dt_id_article' => $article['id_article'],
                'dt_status'     => "
                    <div class=\"tal\">
                        <a class=\"ep-icon ep-icon_filter txt-green dt_filter\"
                        data-title=\"Status\"
                        title=\"Filter by status\"
                        data-value-text=\"{$status_array[$article['applicant_article_status']]}\"
                        data-value=\"{$article['applicant_article_status']}\"
                        data-name=\"status\">
                        </a>
                    </div>
                    <div>
                        <i class=\"fs-30 ep-icon {$status_icons_array[$article['applicant_article_status']]}\"></i>
                        <br>
                        {$status_array[$article['applicant_article_status']]}
                    </div>
                ",
                'dt_lang'    => $lang_name,
                'dt_author'  => "
                    <div class=\"tal\">
                        <a href=\"{$contact_applicant_url}\"
                            class=\"ep-icon ep-icon_envelope fancybox.ajax fancyboxValidateModal\"
                            data-title=\"Contact this user\"
                            title=\"Contact this Author\">
                        </a>
                        <a href=\"{$preview_applicant_url}\"
                            class=\"ep-icon ep-icon_user fancybox.ajax fancyboxValidateModal\"
                            data-title=\"Author information\"
                            title=\"View author information\">
                        </a>
                    </div>
                    {$user_name}
                ",
                'dt_title' => "
                    <div class=\"pull-left\">
                        <div class=\"clearfix\">
                            <strong class=\"pull-left lh-16 pr-5\">Category: </strong>{$article['blog_category_name']}
                        </div>
                        <div class=\"clearfix\">
                            <strong class=\"pull-left lh-16 pr-5\">Title </strong>
                            <a href=\"{$details_url}\"
                                title=\"Preview this article\"
                                target=\"_blank\">
                                {$article['applicant_article_title']}
                            </a>
                        </div>
                    </div>
                ",
                'dt_photo'             => "<img class=\"mw-100\" src=\"{$photo_url}\" alt=\"{$article['title']}\"/>",
                'dt_short_description' => "<div class=\"h-50 hidden-b\">{$article['applicant_article_description']}</div>",
                'dt_date_created'      => formatDate($article['applicant_article_created_at']),
                'dt_actions'           => "
                    {$approve_button}
                    {$decline_button}
                    <a href=\"{$details_url}\"
                        class=\"ep-icon ep-icon_magnifier txt-blue\"
                        title=\"Preview this article\"
                        target=\"_blank\">
                    </a>
                    <a href=\"#\"
                        class=\"ep-icon ep-icon_remove txt-red confirm-dialog\"
                        data-callback=\"remove_article\"
                        data-article=\"{$article['id_article']}\"
                        title=\"Remove this article\"
                        data-message=\"Are you sure you want to delete this article?\">
                    </a>
                ",
                'dt_country' => $country_name,
            );
        }

        jsonResponse('', 'success', $output);
    }

    public function ajax_check_new_articles()
    {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        checkAdminAjax('bloggers_articles_administration');

        $this->load->model('Bloggers_Model', 'bloggers');

        $last_id = $_POST['lastId'];
        $article_count = $this->bloggers->count_new_articles($last_id);
        if ($article_count) {
            $last_blogs_id = $this->bloggers->get_last_article_id();
            jsonResponse(null, 'success', array('nr_new' => $article_count, 'lastId' => $last_blogs_id));
        } else {
            jsonResponse('New articles are not found');
        }
    }

    public function ajax_delete_article()
    {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        checkAdminAjax('bloggers_articles_administration');

        /** @var Bloggers_Model $bloggersModel */
        $bloggersModel = model(Bloggers_Model::class);

        if (empty($articleId = request()->request->getInt('id'))) {
            jsonResponse('Invalid article ID provided');
        }

        if (!$bloggersModel->is_article_exists($articleId)) {
            jsonResponse('Article with provided ID is not found');
        }

        if (!$bloggersModel->remove_application(null, $articleId)) {
            jsonResponse('Failed to remove the article');
        }

        /** @var FilesystemProviderInterface */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $publicDisk = $storageProvider->storage('public.storage');

        try {
            $publicDisk->deleteDirectory(BloggersUploadsPathGenerator::publicIdUploadPath($articleId));
        } catch (\Throwable $th) {
            //NOTHIND TO DO
        }

        jsonResponse('Article is successfully deleted', 'success');
    }

    public function ajax_contact_applicant()
    {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        checkAdminAjax('bloggers_articles_administration');

        $this->load->model('Bloggers_Model', 'bloggers');
        $this->load->model('Notify_Model', 'notify');

        $validator_rules = array(
            array(
                'field' => 'applicant',
                'label' => 'Applicant info',
                'rules' => array('required' => '', 'integer'=>''),
            ),
            array(
                'field' => 'subject',
                'label' => 'Subject',
                'rules' => array('required' => ''),
            ),
            array(
                'field' => 'content',
                'label' => 'Content',
                'rules' => array('required' => ''),
            ),
        );
        $this->validator->set_rules($validator_rules);
        if (!$this->validator->validate()) {
            jsonResponse($this->validator->get_array_errors());
        }

        $applicant_id = (int) cleanInput($_POST['applicant']);
        $applicant = $this->bloggers->find_applicant($applicant_id);
        if (empty($applicant)) {
            jsonResponse("This applicant doesn't exist.");
        }

        // MAIL USER
        try {
            /** @var MailerInterface $mailer */
            $mailer = $this->getContainer()->get(MailerInterface::class);
            $mailer->send(
                (new BloggersContact("{$applicant['applicant_firstname']} {$applicant['applicant_lastname']}", cleanInput(request()->request->get('content'))))
                    ->from(config('bloggers_support_email'))
                    ->to(new Address($applicant['applicant_email']))
                    ->subject(cleanInput(request()->request->get('subject')))
            );
        } catch (\Throwable $th) {
            jsonResponse(translate('email_has_not_been_sent'));
        }

        jsonResponse('Success: The email has been sent.', 'success');
    }

    public function ajax_change_status()
    {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        checkAdminAjax('bloggers_articles_administration');

        $this->load->model('Bloggers_Model', 'bloggers');

        $article_id = !empty($_POST['id']) ? (int) cleanInput($_POST['id']) : null;
        if (null === $article_id) {
            jsonResponse('Invalid article ID provided');
        }
        if (!$this->bloggers->is_article_exists($article_id)) {
            jsonResponse('Article with provided ID is not found');
        }

        $status = !empty($_POST['status']) ? cleanInput($_POST['status']) : null;
        if(null === $status || !in_array($status, ['new', 'approved', 'declined'])) {
            jsonResponse('Invalid article status provided');
        }

        if(!$this->bloggers->update_article($article_id, array('applicant_article_status' => $status))) {
            jsonResponse('Failed to update article status');
        }

        jsonResponse("Status of the article is updated to: \"{$status}\"", 'success');
    }

    public function ajax_clean_draft()
    {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        // Load html clean library
        $this->load->library('Cleanhtml', 'clean');
        $this->clean->allowIframes();
        $this->clean->defaultTextarea([
            'attribute' => 'data-video,colspan,rowspan,dir',
            'style' => 'text-align, padding-left, padding-right'
        ]);
        $this->clean->addAdditionalTags('<img><figure><figcaption><h2><h3><h4><h5><h6><p><span><strong><em><b><i><u><a><ol><ul><li><br>');

        jsonResponse(null, 'success', array(
            'processed' => array(
                'token'         => $_POST['token'],
                'email'         => $_POST['email'],
                'upload_folder' => cleanInput($_POST['upload_folder']),
                'country'       => cleanInput($_POST['country']),
                'lang'          => cleanInput($_POST['lang']),
                'category'      => cleanInput($_POST['category']),
                'title'         => cleanInput($_POST['title']),
                'description'   => cleanInput($_POST['description']),
                'images'        => $_POST['images'],
                'content'       => $this->clean->sanitize($_POST['content']),
                'tags'          => array_filter(array_map(function($tag) {
                    return cleanInput($tag);
                }, !empty($_POST['tags']) ? $_POST['tags'] : array())),
            )
        ));
    }

    private function process_content_images($text)
    {
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
            return array(
                'text'   => $text,
                'images' => array(),
            );
        }

        $allowed_amount_of_images = (int) tmvc::instance()->my_config['max_blogs_photos_in_text'];
        if (count($images_in_text) > $allowed_amount_of_images) {
            throw new Exception('Allowed amount of the images in text exceeded', self::IMAGES_AMOUNT_EXCEEDED);
        }

        $external_paths = array_filter($matches[8]);
        if (!empty($external_paths)) {
            throw new Exception('Images from external domains are not allowed', self::IMAGES_INVALID_DOMAIN);
        }

        $images_paths = array();
        $temporary_images = array_filter($matches[2]);
        foreach ($temporary_images as $key => $image) {
            if(null !== parse_url($image, PHP_URL_HOST)) {
                if (false === strpos($image, __HTTP_HOST_ORIGIN)) {
                    continue;
                }

                list(, $path) = explode(__HTTP_HOST_ORIGIN, $image);
                $image = trim($path, '/');
            }

            $images_paths[] = $image;
        }

        return array(
            'text'   => $text,
            'images' => array(
                'collected' => $temporary_images,
                'paths'     => $images_paths,
            ),
        );
    }

    private function change_content_paths($text, $article_id, $images)
    {
        /** @var FilesystemProviderInterface */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $publicStorage = $storageProvider->storage('public.storage');

        foreach ($images as $image) {
            $imagePath = $publicStorage->url(BloggersUploadsPathGenerator::publicMainUploadPath($article_id, basename($image)));

            if(isset($imagePath)) {
                $text = str_replace(
                    $image,
                    $imagePath,
                    $text
                );
            }
        }

        return $text;
    }

    private function generate_token(...$parts)
    {
        $now = new \DateTimeImmutable();
        $parts[] = openssl_random_pseudo_bytes(32);
        $token_cost = tmvc::instance()->my_config['blogger_access_token_cost'];
        $token = password_hash(
            implode('::', $parts),
            PASSWORD_DEFAULT,
            array('cost' => $token_cost)
        );
        $token_expiration = $now->add(new \DateInterval('P1D'));

        return array(
            $token,
            $token_expiration,
        );
    }

    private function show_applicant_form()
    {
        $this->load->model('Country_Model', 'countries');
        $this->view->display('new/bloggers/applicant_form_view', array(
            'action'    => __BLOGGERS_URL . 'bloggers/ajax_process_applicant_credentials',
            'countries' => $this->countries->get_countries(),
        ));
    }

    private function show_applicant_info()
    {
        checkAdminAjaxModal('bloggers_articles_administration');

        $this->load->model('Bloggers_Model', 'bloggers');
        $this->load->model('Country_Model', 'countries');

        $applicant_id = (int) cleanInput($this->uri->segment(4));
        if (
            empty($applicant_id) ||
            null === ($applicant = $this->bloggers->find_applicant($applicant_id))
        ) {
            messageInModal('Applicant with such ID is not found on this server');
        }

        $country = $this->countries->get_country($applicant['id_applicant_country']);
        $applicant['applicant_media_pages'] = json_decode($applicant['applicant_media_pages'], true);
        $applicant['applicant_media_pages'] = array_filter($applicant['applicant_media_pages'], function($item) { return !empty($item['url']); });
        $applicant['applicant_fullname'] = "{$applicant['applicant_firstname']} {$applicant['applicant_lastname']}";
        $applicant['applicant_country'] = $country['country'];
        $applicant['applicant_photo'] = __IMG_URL . getImage('public/img/no_image/noimage-content-manager-125.jpg');
        $this->view->display('new/bloggers/admin/info_view', array(
            'info' => $applicant
        ));
    }

    private function show_apply_article_form()
    {
        $this->load->model('Blog_Model', 'blog');
        $this->load->model('Bloggers_Model', 'bloggers');
        $this->load->model('Country_Model', 'countries');

        $email = !empty($_POST['email']) ? cleanInput($_POST['email']) : null;
        $token = !empty($_POST['token']) ? base64_decode($_POST['token']) : null;
        if (
            null === $token ||
            null === $email ||
            null === ($applicant = $this->bloggers->get_first_applicant(array('active_token' => $token, 'email' => $email)))
        ) {
            // Here be drago... I mean the message of token expiration
            return $this->view->display('new/bloggers/error_message_view', array(
                'messages' => array(
                    'Your access has been expired. Please renew your access code and try again',
                ),
            ));
        }

        $this->view->display('new/bloggers/article_form_view', array(
            'email'                    => $email,
            'token'                    => base64_encode($token),
            'upload_folder'            => encriptedFolderName(),
            'fileupload_max_file_size' => tmvc::instance()->my_config['fileupload_max_file_size'],
            'blogs_photos_amount'      => tmvc::instance()->my_config['max_blogs_photos_in_text'],
            'required_theme'           => tmvc::instance()->my_config['blogger_required_subject'],
            'tlanguages'               => $this->translations->get_languages(array('lang_active' => 1, 'lang_url_type' => "'domain'")),
            'url'                      => array(
                'save'          => __BLOGGERS_URL . 'bloggers/ajax_apply_article',
                'preview'       => __BLOGGERS_URL . 'bloggers/preview',
                'process_draft' => __BLOGGERS_URL . 'bloggers/ajax_clean_draft',
            ),
        ));
    }

    private function show_contact_form()
    {
        checkAdminAjaxModal('bloggers_articles_administration');

        $this->load->model('Bloggers_Model', 'bloggers');

        $applicant_id = (int) cleanInput($this->uri->segment(4));
        if (
            empty($applicant_id) ||
            null === ($applicant = $this->bloggers->find_applicant($applicant_id))
        ) {
            messageInModal('Applicant with such ID is not found on this server');
        }

        $data['contact']['url'] = __SITE_URL . 'bloggers/ajax_contact_applicant';
        $data['applicant_info'] = $applicant;
        $data['applicant_info']['fullname'] = "{$applicant['applicant_firstname']} {$applicant['applicant_lastname']}";
        $data['applicant_info']['photo'] = __IMG_URL . getImage('public/img/no_image/noimage-content-manager-125.jpg');
        $this->view->assign($data);
        $this->view->display('new/bloggers/admin/email_form_view');
    }
}
