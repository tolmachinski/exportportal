<?php

use App\Common\Buttons\ChatButton;
use App\Filesystem\CompanyLogoFilePathGenerator;
use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [15.12.2021]
 * Controller Refactoring
 */
class Default_Controller extends TinyMVC_Controller {

	private $breadcrumbs = array();

	/* load main models*/
	private function load_main(){
		$this->load->model('Category_Model', 'category');
		$this->load->model('Text_block_model', 'text_block');
	}

    public function index()
    {
        if (__CURRENT_SUB_DOMAIN === getSubDomains()['shippers']) {
            $this->eplHomePage();

            return;
        }

        switch (true) {
            case is_seller():
                $this->sellerHomePage();

                break;
            case is_manufacturer():
                $this->manufacturerHomePage();

                break;
            case is_buyer():
                $this->buyerHomePage();

                break;
            case is_shipper():
                $this->shipperHomePage();

                break;
            default:
                $this->homePageForGuests();

                break;
        }
	}

    private function homePageForGuests()
    {
        /** @var Golden_Categories_Model $goldenCategoriesModel */
        $goldenCategoriesModel = model(Golden_Categories_Model::class);

		views()->display_template([
            'templateViews'             => [
                'headerOutContent'  => 'home/index_header_view',
                'mainOutContent'    => 'home/index_view',
            ],
            'goldenCategories'          => $goldenCategoriesModel->findAll(),
            'webpackData'               => [
                'styleCritical' => 'home',
				'pageConnect' 	=> 'index_page',
            ],
        ]);
    }

    private function eplHomePage()
    {
        views(
            [
                'new/epl/template/index_view'
            ],
            [
                'isHomePage'    => true,
                'templateViews' => [
                    'headerOutContent'  => 'epl/index_header_view',
                    'mainOutContent'    => 'epl/index_view',
                ],
                'webpackData'   => [
                    'styleCritical' => 'epl_critical_styles_home',
                    'pageConnect' 	=> 'epl_index_page',
                ]
            ]
        );
    }

    private function sellerHomePage()
    {
        views()->display_template([
            'templateViews' => [
                'headerOutContent'  => 'home/seller/index_header_view',
                'mainOutContent'    => 'home/seller/index_view',
            ],
            'webpackData'   => [
                'styleCritical' => 'home',
                'pageConnect' 	=> 'logged_index_page',
            ],
        ]);
    }

    private function manufacturerHomePage()
    {
        views()->display_template([
            'templateViews' => [
                'headerOutContent'  => 'home/manufacturer/index_header_view',
                'mainOutContent'    => 'home/manufacturer/index_view',
            ],
            'webpackData'   => [
                'styleCritical' => 'home',
                'pageConnect' 	=> 'logged_index_page',
            ],
        ]);
    }

    private function buyerHomePage()
    {
        /** @var Golden_Categories_Model $goldenCategoriesModel */
        $goldenCategoriesModel = model(Golden_Categories_Model::class);

        views()->display_template([
            'goldenCategories' => $goldenCategoriesModel->findAll(),
            'templateViews'    => [
                'headerOutContent'  => 'home/buyer/index_header_view',
                'mainOutContent'    => 'home/buyer/index_view',
            ],
            'webpackData'      => [
                'styleCritical' => 'home',
                'pageConnect' 	=> 'logged_index_page',
            ],
        ]);
    }

    private function shipperHomePage()
    {
        views()->display_template([
            'templateViews' => [
                'headerOutContent'  => 'home/freight_forwarder/index_header_view',
                'mainOutContent'    => 'home/freight_forwarder/index_view',
            ],
            'webpackData'   => [
                'styleCritical' => 'home',
                'pageConnect' 	=> 'logged_index_page',
            ],
            'magazines' => json_decode(httpGet('https://exportsnews.com/api/magazines?limit=5')->getBody()->getContents(), true),
        ]);
    }

	function _maintenance(){
		$this->view->display('under_construction_view');
	}

	function cookieconsent(){
		$this->load->model('Text_block_model', 'text_block');
		$data['terms_info'] = $this->text_block->get_text_block_by_shortname('cookie_policy');

		if(!isAjaxRequest()){
            $data['header_out_content'] = 'new/terms_and_conditions/header_view';
            $data['main_content'] = 'new/terms_and_conditions/cookieconsent/index_view';
            $data['sidebar_right_content'] = 'new/terms_and_conditions/cookieconsent/sidebar_view';
            $this->view->assign($data);
			$this->view->display('new/index_template_view');
		}else{
            $webpackData = cleanInput($this->uri->segment(3));
            if(!empty($webpackData)){
				$data['webpackData'] = true;
			}
			$data['cookie_policy_modal'] = true;
			$this->view->assign($data);
			$this->view->display('new/terms_and_conditions/cookieconsent/sidebar_view');
			$this->view->display('new/terms_and_conditions/cookieconsent/index_view');
		}
	}

	function buying()
    {
        views()->displayWebpackTemplate([
            'content'           => 'buying/index_view',
            'styleCritical'     => 'buying',
            'headerContent'     => 'new/buying/components/header_view',
        ]);
	}

    public function selling()
    {
        views()->displayWebpackTemplate([
            'content'       => 'selling/index_view',
            'styleCritical' => 'selling',
            'headerContent' => 'new/selling/components/header_view',
        ]);
    }

    public function resources()
    {
        if (config('env.SHIPPER_SUBDOMAIN') !== __CURRENT_SUB_DOMAIN) {
            show_404();
        }

        if (logged_in() && !is_shipper()) {
            headerRedirect();
        }

        /** @var Elasticsearch_Questions_Model $elasticsearchQuestionsModel */
        $elasticsearchQuestionsModel = model(Elasticsearch_Questions_Model::class);

        /** @var Elasticsearch_Faq_Model $elasticsearchFaqModel */
        $elasticsearchFaqModel = model(Elasticsearch_Faq_Model::class);

        /** @var Tags_Faq_Model $tagsFaqModel */
        $tagsFaqModel = model(Tags_Faq_Model::class);
        $tagsIds = [69, 70, 71, 72];

        $faq = $elasticsearchFaqModel->get_faq_list([
            'tagsIds'   => $tagsIds,
            'limit'     => 3,
            'start'     => 0,
        ]);

        /** @var User_Guide_Model $userGuide */
        $userGuide = model(User_Guide_Model::class);
        $documentLangs = $userGuide->getUserGuidesLang();

        views(
            [
                'new/epl/template/index_view',
            ],
            [
                'communityQuestions'  => $elasticsearchQuestionsModel->getQuestions([
                    'categoriesIds' => [51, 68],
                    'order_by'      => 'date_question-desc',
                    'perPage'       => 3,
                ]),
                'faqTags'             => $tagsFaqModel->findAllBy([
                    'conditions' => [
                        'tagsIds' => $tagsIds,
                    ],
                    'order'      => ["`{$tagsFaqModel->getTable()}`.`top_priority`" => 'asc'],
                ]),
                'faqList'             => $faq,
                'faqTagsCounters'     => $elasticsearchFaqModel->aggregates['tags'],
                'documentUploadLangs' => $documentLangs['document_upload'],
                'templateViews'       => [
                    'headerOutContent' => 'epl/resources/index_header_view',
                    'mainOutContent'   => 'epl/resources/index_view',
                ],
                'webpackData'         => [
                    'styleCritical' => 'epl_critical_styles_resources',
                    'pageConnect'   => 'epl_resources_page',
                ],
            ]
        );
    }

	function language(){
		$lang = $this->uri->segment(3);
		//$langdir = "langs/";
		$this->cookies->setCookieParam('lang', $lang);
		header('Location:' . __SITE_URL );
	}

	function export_import(){
		$this->load->model('Category_Model', 'category');
		$this->load->model('Country_Model', 'country');
		$country_articles = $this->country->get_countries();

		$data['country_articles'] = array();
		foreach($country_articles as $blogs){
			$char = substr($blogs['country'], 0, 1);
			$data['country_articles'][$char][] = $blogs;
		}

		$this->breadcrumbs[]= array(
			'link' 	=> __SITE_URL.'export_import',
			'title'	=> translate('breadcrumb_export_import')
		);
		$data['breadcrumbs'] = $this->breadcrumbs;

		$this->view->assign($data);

		$this->view->display('new/header_view');
        $this->view->display('new/export_import_view');
        $this->view->display('new/footer_view');
	}

	function learn_more(){
		$this->breadcrumbs[] = array(
			'link' 	=> __SITE_URL . 'learn_more',
			'title'	=> translate('breadcrumb_learn_more')
		);

        $data = [
            'breadcrumbs'       => $this->breadcrumbs,
            'content'           => 'learn_more/index_view',
            'styleCritical'     => 'learn_more',
            'headerContent'     => 'new/learn_more/components/header_view',
            'customEncoreLinks' => true,
        ];

        views()->displayWebpackTemplate($data);
	}

	function manufacturer_description() {
		$this->view->display('new/header_view');
		$this->view->display('new/users_description/manufacturer_view');
		$this->view->display('new/footer_view');
	}

	function shipper_description() {
		$this->view->display('new/header_view');
		$this->view->display('new/users_description/shipper_view');
		$this->view->display('new/footer_view');
	}

    public function ajax_get_picks_of_month()
    {
        checkIsAjax();

        /** @var FilesystemProviderInterface */
        $filesystemProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $publicStorage = $filesystemProvider->storage('public.storage');
        /** @var Pick_Of_The_Month_Company_Model $pickCompanyModel */
        $pickCompanyModel = model(Pick_Of_The_Month_Company_Model::class);
        /** @var Pick_Of_The_Month_Item_Model $pickItemModel */
        $pickItemModel = model(Pick_Of_The_Month_Item_Model::class);
        /** @var Elasticsearch_Items_Model $elasticsearchItemsModel */
        $elasticsearchItemsModel = model(Elasticsearch_Items_Model::class);

        if (!empty($pickCompany = $pickCompanyModel->findOneBy(['conditions' => ['dateBetween' => new DateTime()]]))) {
            /** @var Elasticsearch_Company_Model $elasticCompany */
            $elasticCompany = model(Elasticsearch_Company_Model::class);
            $elasticCompany->get_companies([
                'list_company_id' => $pickCompany['id_company'],
                'per_p'           => 1,
            ]);

            $elasticCompanies = $elasticCompany->records ?: [];
            $companyPickOfTheMonth = array_pop($elasticCompanies);
            $companyPickOfTheMonth['logoUrl'] = $publicStorage->url(CompanyLogoFilePathGenerator::logoPath(
                $companyPickOfTheMonth['id_company'],
                $companyPickOfTheMonth['logo_company'] ?? 'no-image.jpg'
            ));
        }

        if (!empty($pickItem = $pickItemModel->findOneBy(['conditions' => ['dateBetween' => new DateTime()]]))) {
            $elasticsearchItemsModel->get_items([
                'list_item' => [$pickItem['id_item']],
                'per_p'     => 1,
            ]);

            $elasticItems = $elasticsearchItemsModel->items ?: [];
            $itemPickOfTheMonth = array_pop($elasticItems);

            if (isBackstopEnabled()) {
                $itemPickOfTheMonth['discount'] = '10';
            }
        }

        jsonResponse(
            '',
            'success',
            [
                'picksOfMonth' => views()->fetch('new/home/components/ajax/picks_of_the_month_view', [
                    'itemPickOfTheMonth'    => $itemPickOfTheMonth ?? null,
                    'companyPickOfTheMonth' => $companyPickOfTheMonth ?? null,
                ]),
            ],
        );
    }

    public function ajax_get_customer_reviews()
    {
        checkIsAjax();

        /** @var Ep_Reviews_Model $epReviewsModel */
        $epReviewsModel = model(Ep_Reviews_Model::class);

        $epReviews = $epReviewsModel->findAllBy([
            'conditions' => ['is_published' => 1],
            'with'       => ['user'],
            'order'      => ["{$epReviewsModel->getTable()}.`published_date`" => 'desc'],
            'limit'      => 12,
        ]);

        jsonResponse(
            '',
            'success',
            [
                'reviews' => views()->fetch('new/home/components/ajax/customer_reviews_view', [
                    'epReviews' => $epReviews ?? null,
                ]),
            ],
        );
    }
}
