<?php
/**
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [18.12.2021]
 * Controller Refactoring
 */
class Security_Controller extends TinyMVC_Controller {
	private $breadcrumbs = array();
	/* load main models*/
	private function _load_main(){
		$this->load->model('Category_Model', 'category');
	}


	function index() {
		$this->_load_main();
		$this->load->model('Country_Model', 'country');
		$this->load->model('Questions_Model', 'questions');
		$this->load->model('Offices_Model', 'offices');
		$this->load->model('Video_Model', 'video');

		global $tmvc;
		$data['email_contact_us'] = $tmvc->my_config['email_contact_us'];
		$data['ep_address'] = $tmvc->my_config['ep_address'];
		$data['ep_phone_number'] = $tmvc->my_config['ep_phone_number'];
		$data['ep_phone_number_free'] = $tmvc->my_config['ep_phone_number_free'];
		$data['ep_phone_whatsapp'] = $tmvc->my_config['ep_phone_whatsapp'];

		$this->view->assign('title', translate('breadcrumb_security'));

		$data['countries'] = $this->country->fetch_port_country();
		$data['quest_cats'] = $this->questions->getCategories(array('visible' => 1));
		$data['offices'] = $this->offices->get_office_location(array('order_by' => 'RAND()', 'limit' => 2));

		$this->breadcrumbs[]= array(
			'link' 	=> '',
			'title'	=> translate('breadcrumb_security')
		);
		$data['breadcrumbs'] = $this->breadcrumbs;
		$this->view->assign($data);
        $this->view->display('new/header_view');
        $this->view->display('new/security/index_view');
        $this->view->display('new/footer_view');
	}
}
?>
