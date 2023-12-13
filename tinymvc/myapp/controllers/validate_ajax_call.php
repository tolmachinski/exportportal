<?php
/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [18.12.2021]
 * Controller Refactoring
 */
class Validate_ajax_call_Controller extends TinyMVC_Controller
{
	public function ajax_check_name()
	{
		$validator_rules = array(
			array(
				'field' => 'name',
				'rules' => array('valid_user_name' => '')
			)
		);

		$validator = library('validator');
		$validator->set_rules($validator_rules);
		if (!$validator->validate()) {
			jsonResponse(null, 'error');
		}

		jsonResponse(null, 'success');
	}

    /** @deprecated */
	function ajax_company_link_verify(){
		$resp[0] = 'index_name';

		if(!have_right('add_company')){
			$resp[1] = false;
			exit(json_encode($resp));
		}

		$validator_rules = array(
			array(
				'field' => 'index_name',
				'label' => 'Company url',
				'rules' => array('required' => '', 'company_index_name' => '')
			)
		);

		$this->validator->validate_data = array(
			'index_name' => $_REQUEST['index_name']
		);

		$this->validator->set_rules($validator_rules);
		if (!$this->validator->validate()){
			$resp[1] = false;
		} else{
			$resp[1] = true;
		}

		echo json_encode($resp);
	}

	function ajax_check_password(){
		$resp[0] = 'password';
		$password = $_REQUEST['password'];
		$valid_password = preg_match('/^(?=.*\d)(?=.*[A-Z])(?=.*[a-z])[0-9A-Za-z@#\-_$%^&+=.§!\?]{6,30}$/', $password);
		if(!$valid_password || strlen($password) < 6 || strlen($password) > 30) {
			$resp[1] = false;
		} else{
			$resp[1] = true;
		}

		echo json_encode($resp);
	}

	function ajax_check_email(){
		$resp[0] = 'email';
		$email = cleanInput($_REQUEST['email'], true);
		$this->load->model('Auth_Model', 'auth_hash');
		$user_encrypt_new = getEncryptedEmail($email);

		if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$resp[1] = false;
		} elseif(!$this->auth_hash->exists_hash($user_encrypt_new)){
			$resp[1] = true;
		} else {
			$resp[1] = false;
		}

		echo json_encode($resp);
	}

	function ajax_check_email_new(){
		$email = cleanInput($_REQUEST['email'], true);

		if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			jsonResponse('','warning');
		}

		$this->load->model('Auth_Model', 'auth');
		$encrypted = getEncryptedEmail($email);
		if($this->auth->exists_hash($encrypted)){
			jsonResponse('');
		}

		jsonResponse('','success');
    }
}

?>
