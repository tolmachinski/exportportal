<?php
/**
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 */
class Follow_Controller extends TinyMVC_Controller {

    function index() {
        header('location: ' . __SITE_URL);
    }

    public function popup_forms() {
		if(!isAjaxRequest()){
			headerRedirect();
		}
		if(!logged_in()){
			messageInModal(translate("systmess_error_should_be_logged"));
		}
		if(!have_right('follow_this'))
			messageInModal(translate("systmess_error_rights_perform_this_action"));

		$op = $this->uri->segment(3);
		$id = (int)$this->uri->segment(4);

		$id_user = privileged_user_id();

		switch($op){
			case 'follow_b2b_request':
				$data['idRequest'] = $id;
				$this->view->assign($data);

				$this->view->display('new/follow/popup_follow_b2b_view');
			break;
			case 'edit_follow_b2b_request':
				$this->load->model('B2b_Model', 'b2b');

				if(!$this->b2b->isMyFollow($id, $id_user) && !have_right('moderate_content')){
					messageInModal(translate("systmess_error_rights_perform_this_action"));
				}
				$data['follower'] = $this->b2b->getFollower($id);
				if($data['follower']['moderated']){
					messageInModal('Error: This notice was moderated. Please close this window.');
				}
				$this->view->assign($data);

				$this->view->display('new/follow/popup_edit_follow_b2b_view');
			break;
		}
    }

    public function ajax_operation(){
		checkIsAjax();
		checkIsLoggedAjax();
		checkPermisionAjax('follow_this');

		$op = $this->uri->segment(3);

		$id_user = privileged_user_id();

		switch($op){
			case 'follow_b2b':
				$validator_rules = array(
					array(
						'field' => 'message',
						'label' => 'Message',
						'rules' => array('required' => '', 'max_len[1000]' => '')
					),
					array(
						'field' => 'id',
						'label' => 'Request info',
						'rules' => array('required' => '', 'integer' => '')
					),

				);
				$this->validator->set_rules($validator_rules);
				if(!$this->validator->validate()){
					jsonResponse($this->validator->get_array_errors());
				}

				$this->load->model('B2b_Model', 'b2b');

				$data = array(
					'id_request' => (int)$_POST['id'],
					'id_user' => $id_user,
					'notice_follower' => cleanInput($_POST['message'])
				);

				$followerAdded = $this->b2b->setFollower($data);
				if($followerAdded){
					jsonResponse('You are now following this b2b request.', 'success');
				} else {
					jsonResponse('Error: You cannot follow this b2b request now. Please try later.');
				}
			break;
			case 'edit_follow_b2b_request':
				$data = array();
				$validator_rules = array(
					array(
						'field' => 'message',
						'label' => 'Message',
						'rules' => array('required' => '', 'max_len[1000]' => '')
					),
					array(
						'field' => 'follower',
						'label' => 'Follow info',
						'rules' => array('required' => '', 'integer' => '')
					)
				);
				$this->validator->set_rules($validator_rules);
				if(!$this->validator->validate()){
					jsonResponse($this->validator->get_array_errors());
				}

				$this->load->model('B2b_Model', 'b2b');

				$data['id_follower'] = intval($_POST['follower']);

				if(!$this->b2b->isMyFollow($data['id_follower'], $id_user) && !have_right('moderate_content')){
					jsonResponse('Error: This notice is not yours. Please close this window.');
				}

				$follower = $this->b2b->getFollower($data['id_follower']);

				if($follower['moderated']){
					jsonResponse('Error: This notice was moderated. Please close this window.');
				}

				$data['id_user'] = $this->session->id;
				$data['notice_follower'] = cleanInput($_POST['message']);
				$followerAdded = $this->b2b->updateFollower($data);
				$updatedFollower = $this->b2b->getFollower($data['id_follower']);

				if(!empty($updatedFollower)){
					jsonResponse('The message has been successfully changed.', 'success', array('newNotice' => $updatedFollower['notice_follower']));
				} else {
					jsonResponse('Error: The changes have not been saved. Please try again later.');
				}
			break;
			case 'delete_follow_b2b':
				$this->load->model('B2b_Model', 'b2b');

				$id_request = (int)$_POST['id'];
                if(empty($id_request)){
                    jsonResponse(translate('systmess_error_invalid_data'));
                }
				$this->b2b->deleteFollowed($id_request, $id_user);
				jsonResponse('You have successfully unfollowed this b2b request.', 'success');
			break;
		}
	}
}
