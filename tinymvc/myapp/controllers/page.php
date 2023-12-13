<?php

/**
 *
 * Pages Controller
 *
 * @property \TinyMVC_Load                    $load
 * @property \TinyMVC_View                    $view
 * @property \TinyMVC_Library_URI             $uri
 * @property \TinyMVC_Library_Session         $session
 * @property \Tinymvc_Library_Cleanhtml       $clean
 * @property \TinyMVC_Library_Cookies         $cookies
 * @property \TinyMVC_Library_Upload          $upload
 * @property \TinyMVC_Library_validator       $validator
 * @property \Translations_Model              $translations
 * @property \SystMess_Model                  $systmess
 * @property \Category_Model                  $category
 * @property \Orders_model                    $orders
 * @property \User_model                      $user
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 */
class Page_Controller extends TinyMVC_Controller
{

    private $breadcrumbs = array();

    function index() {
        headerRedirect();
    }

    public function payments() {
		$this->load->model('Category_Model', 'category');
		$this->load->model('Orders_model', 'orders');

		$data['methods'] = $this->orders->get_pay_methods_with_i18n(true);
		$this->breadcrumbs[] = array(
			'link'	=> __SITE_URL.'page/payments',
			'title'	=> 'Payments'
		);

		$data['breadcrumbs'] = $this->breadcrumbs;
        $this->view->assign('title', 'Payments');
        $data['current_page'] = "payments";
        $data['header_out_content'] = 'new/page/payment_view/header_view';
        $data['main_content'] = 'new/page/payment_view/index_view';
        $data['footer_out_content'] = 'new/about/bottom_who_we_are_view';
        $this->view->assign($data);
        $this->view->display('new/index_template_view');
    }

    public function shipperfees() {
		$this->load->model('Category_Model', 'category');

		$this->breadcrumbs[] = array(
			'link'	=> __SITE_URL.'page/shipperfees',
			'title'	=> 'Freight Forwarder fees'
		);

		$data['breadcrumbs'] = $this->breadcrumbs;
        $this->view->assign('title', 'Freight Forwarder fees');
        $data['current_page'] = "shipperfees";
        $data['header_out_content'] = 'new/page/shipperfees_view/header_view';
        $data['main_content'] = 'new/page/shipperfees_view/index_view';
        $data['footer_out_content'] = 'new/about/bottom_who_we_are_view';
        $this->view->assign($data);
        $this->view->display('new/index_template_view');
    }

    public function sellerfees() {
		$this->load->model('Category_Model', 'category');

		$this->breadcrumbs[] = array(
			'link'	=> __SITE_URL.'page/sellerfees',
			'title'	=> 'Seller fees'
		);

		$data['breadcrumbs'] = $this->breadcrumbs;
        $data['current_page'] = "sellerfees";
        $data['header_out_content'] = 'new/page/sellerfees_view/header_view';
        $data['main_content'] = 'new/page/sellerfees_view/index_view';
        $data['footer_out_content'] = 'new/about/bottom_who_we_are_view';
        $this->view->assign($data);
        $this->view->display('new/index_template_view');
    }

    public function feedback()
    {
        $params = ['popup_hash' => 'feedback_page'];

        if (!isBackstopEnabled()) {
            $params['is_active'] = 1;
        }

        $popup = model('popups')->get_one($params);

        if (empty($popup)) {
            show_404();
        }

		$this->view->display('new/header_view');
		$this->view->display('new/page/feedback/feedback_view');
		$this->view->display('new/footer_view');
    }


}
