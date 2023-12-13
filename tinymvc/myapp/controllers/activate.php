<?php
/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [15.12.2021]
 * Controller Refactoring
 */
class Activate_Controller extends TinyMVC_Controller {

	private function _load_main(){
		$this->load->model('User_Model', 'users');
		$this->load->model('Category_Model', 'category');
	}

	public function index()
    {
		show_404();
	}

	function cr_account() {
		if(logged_in()){
			$this->session->setMessages(translate("systmess_info_already_logged_in"), 'info');
			headerRedirect();
		}

		$hash = $this->uri->segment(3);
		if(empty($hash)){
			show_404();
		}

		$this->load->model('User_Model', 'users');
		$this->load->model('Notify_Model', 'notify');
		$user = $this->users->check_reset_code($hash);
		if(!empty($user)){
			if($user['status'] != 'new'){
				$this->session->setMessages(translate("systmess_info_user_already_activated"), 'info');
				headerRedirect();
			}

			if($user['gr_type'] != 'CR Affiliate'){
				show_404();
			}

			$notice = array(
				'add_date' => date('Y/m/d H:i:s'),
				'add_by' => 'System',
				'notice' => 'Account has been activated.'
			);

			$update = array(
				'status' => 'active',
				'notice' => json_encode($notice).','.$user['notice'],
				'activation_code' => get_sha1_token($user['email'])
			);

			$this->users->updateUserMain($user['idu'], $update);

			$this->session->setMessages(translate("systmess_success_activate_account_can_log_in"));
			headerRedirect(__SITE_URL . 'login');
		} else{
			$this->session->setMessages(translate("systmess_error_activation_code_not_correct"), 'errors');
			headerRedirect();
		}
	}

	function change_info() {
		$this->_load_main();
		$type = cleanInput($this->uri->segment(3));
		$code = cleanInput($this->uri->segment(4));

		if(empty($code) || empty($type)){
			show_404();
		}

		switch($type){
			case 'notification_email':
				$data_change_email = $this->users->get_user_info_change($code, $type);
				if(empty($data_change_email['email'])){
					show_404();
				}

				$update = array(
					'email'        => $data_change_email['email'],
					'email_status' => $data_change_email['email_status'] ?? null,
				);

				$this->users->updateUserMain($data_change_email['id_user'], $update);

				$this->users->delete_user_info_change($data_change_email['id_user'], $type);
				$this->session->setMessages(translate("systmess_info_notification_email_has_been_changed"));
			break;
			case 'email':
				$data_change_email = model('user')->get_user_info_change($code, $type);
				if(empty($data_change_email['email'])){
					show_404();
				}

				$hash_update = array(
					'token_email' => getEncryptedEmail($data_change_email['email'])
				);

				$user_info = model('user')->getSimpleUser($data_change_email['id_user']);
				model('auth')->change_hash($user_info['id_principal'], $hash_update);
				//endregion hash update

				model('user')->delete_user_info_change($data_change_email['id_user'], $type);
				$this->session->setMessages(translate("systmess_info_email_has_been_changed"));
			break;
			case 'password':
				$data_change_password = $this->users->get_user_info_change($code, $type);
				if(empty($data_change_password['email'])){
					show_404();
				}

				//region hash update
				$hash_update = array(
					'token_password' => $data_change_password['password']
				);
				$user_info = model('user')->getSimpleUser($data_change_password['id_user']);
				model('auth')->change_hash($user_info['id_principal'], $hash_update);
				//endregion hash update

				$this->users->delete_user_info_change($data_change_password['id_user'], $type);
				$this->session->setMessages(translate("systmess_success_password_has_been_changed"), 'success');
			break;
		}

		headerRedirect();
	}
}
