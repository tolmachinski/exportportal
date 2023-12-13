<?php

use App\Common\File\File;
use App\Email\EplResetPassword;
use App\Email\ResetPasswordEmail;
use ExportPortal\Bridge\Mailer\Mime\RefAddress;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 */
class Authenticate_Controller extends TinyMVC_Controller {

	/* load main models*/
	private function load_main(){
		$this->load->model('User_Model', 'users');
		$this->load->model('Category_Model', 'category');
		$this->load->model('Usergroup_Model', 'ugroup');
	}

    private function showForgotPasswordPage() {
        views()->display('new/header_view');
        views()->display('new/authenticate/forgot_view');
        views()->display('new/footer_view');
    }

    private function showResetPasswordPage($data) {
        views()->assign($data);
        views()->display('new/header_view');
        views()->display('new/authenticate/reset_view');
        views()->display('new/footer_view');
    }

    private function sendEmailEplResetPassword($users, $code) {
        try {
            /** @var MailerInterface $mailer */
            $mailer = $this->getContainer()->get(MailerInterface::class);
            $mailer->send(
                (new EplResetPassword("{$users[0]['fname']} {$users[0]['lname']}", $code))
                    ->to(...array_map(fn (int $id, string $to) => new RefAddress((string) $id, new Address($to)), array_column($users, 'idu', 'idu'), array_column($users, 'email', 'email')))
            );
        } catch (\Throwable $th) {
            jsonResponse(translate('auth_form_forgot_error_cannot_send_email'));
        }
    }

    private function sendEmailResetPassword($users, $code) {
        $userEmails = array_unique(array_column($users, 'email', 'idu'));

        try {
            /** @var MailerInterface $mailer */
            $mailer = $this->getContainer()->get(MailerInterface::class);
            $mailer->send(
                (new ResetPasswordEmail("{$users[0]['fname']} {$users[0]['lname']}", getUrlForGroup("authenticate/reset/{$code}")))
                    ->to(...array_map(
                        fn (int $id, string $to) => new RefAddress((string) $id, new Address($to)),
                        array_keys($userEmails),
                        array_values($userEmails)
                    ))
            );
        } catch (\Throwable $th) {
            jsonResponse(translate('email_has_not_been_sent'));
        }
    }

    private function showEplForgotPasswordPage() {
        $data = [
            'templateViews' => ['headerOutContent'  => 'epl/authenticate/forgot_view'],
            'webpackData'   => [
                'styleCritical' => 'epl_critical_styles_forgot_password',
                'pageConnect' 	=> 'epl_forgot_password_page',
            ]
        ];

        views(["new/epl/template/index_view"], $data);
    }

    private function showEplResetPasswordPage($aditionalData = null) {
        $data = [
            'templateViews' => ['headerOutContent'  => 'epl/authenticate/reset_view'],
            'webpackData'   => [
                'styleCritical' => 'epl_critical_styles_forgot_password',
                'pageConnect' 	=> 'epl_reset_password_page',
            ]
        ];

        if (isset($aditionalData)) {
            $data = array_merge($data, $aditionalData);
        }

        views(["new/epl/template/index_view"], $data);
    }

	function index(){
		headerRedirect( __SITE_URL . 'login');
	}

	function terms_conditions() {
		$this->view->display('authenticate/terms_conditions_view');
	}

	function logout(){
        $parameters = [];
        $reason = request()->query->get('reason');

        if (!empty($reason)) {
            $parameters[] = "reason={$reason}";
        }

        $getParameters = !empty($parameters) ? '?' . implode('&', $parameters) : '';

        checkIsLogged($getParameters);

        library(TinyMVC_Library_Auth::class)->logout();

        $httpReferer = (string) $_SERVER['HTTP_REFERER'];
        $pathComponents = parse_url($httpReferer);

        if (empty($pathComponents)) {
            if (__CURRENT_SUB_DOMAIN === getSubDomains()['shippers']) {
                headerRedirect(__SHIPPER_URL . "login{$getParameters}");
            } else {
                headerRedirect(__SITE_URL . "login{$getParameters}");
            }
        }

        $app = tmvc::instance();

        //region determinate url segments
        $pathInfo = $pathComponents['path'];
        $subDomainRawInfo = explode('.', $pathComponents['host']);
        $currentSubDomain = strtolower(count($subDomainRawInfo) > 2 ? implode('.', array_slice($subDomainRawInfo, 0, -2)) : '');

        if (!empty($app->config['routing']['search']) && !empty($app->config['routing']['replace'])) {
			$pathInfo = preg_replace(
				$app->config['routing']['search'],
				$app->config['routing']['replace'],
				$pathInfo
			);
        }

        $urlSegments = empty($pathInfo) ? array() : array_filter(explode('/', $pathInfo), 'mb_strlen');
        //endregion determinate url segments

        //region determinate controller
        $controllerName = $app->config['root_controller'] ?? null;

        if (null === $controllerName) {
            if ($currentSubDomain === config('env.BLOG_SUBDOMAIN')) {
                $controllerName = $app->config['blog_controller'];
            } elseif (in_array($currentSubDomain, $app->config['cr_available'])){
                $controllerName = $app->config['cr_controller'];
            } else {
                $controllerName = ($urlSegments[1] ?: null) ?? $app->config['default_controller'] ?? null;
            }
        }

        try {
            $directory = new File(TMVC_MYAPPDIR . 'controllers', false);
            $controllerFile = new File(TMVC_MYAPPDIR . "controllers/{$controllerName}.php");

            if ($directory->getRealPath() !== dirname($controllerFile->getRealPath())) {
                throw new FileNotFoundException("The controller file is in wrong directory.");
            }
        } catch (FileNotFoundException $exception) {
            $controllerName = $app->config['company_controller'] ?? null;
        }
        //endregion determinate controller

        //region determinate action
        if (!empty($app->config['root_action'])) {
            $actionName = $app->config['root_action'];
        } else {
            switch ($controllerName) {
                case $app->config['blog_controller']:
					$actionName = (isset($urlSegments[1]) && in_array($urlSegments[1], array('detail','preview_blog'))) ? $urlSegments[1] : $app->config['blog_default_action'];
				break;
				case $app->config['cr_controller']:
					$actionName = empty($urlSegments[1]) ? $app->config['cr_default_action'] : $urlSegments[1];
				break;
				default:
                    $actionName = !empty($urlSegments[2]) ? $urlSegments[2] : (empty($app->config['default_action']) ? 'index' : $app->config['default_action']);
				break;
			}
        }
        //endregion determinate action

        //region check page publicity
        $page = model(Pages_Model::class)->get_first_page(array(
            'controller'    => $controllerName,
            'is_public'     => 1,
            'action'        => $actionName,
        ));
        //endregion check page publicity

        $linkAction = uri()->segment(3);

        if ( empty($page) || (!empty($linkAction) && $linkAction == 'login') ) {
            if (__CURRENT_SUB_DOMAIN === getSubDomains()['shippers']) {
                headerRedirect(__SHIPPER_URL . "login{$getParameters}");
            } else {
                headerRedirect(__SITE_URL . "login{$getParameters}");
            }
        } else {
            headerRedirect($httpReferer.$getParameters);
        }
	}

	public function forgot() {
		if (logged_in()) {
            if (__CURRENT_SUB_DOMAIN === getSubDomains()['shippers']) {
                headerRedirect(__SHIPPER_URL);
            } else {
                headerRedirect();
            }
		}

        if (__CURRENT_SUB_DOMAIN === getSubDomains()['shippers']) {
            $this->showEplForgotPasswordPage();
        } else {
            $this->showForgotPasswordPage();
        }
	}

    function ajax_forgot()
    {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        if(!ajax_validate_google_recaptcha()){
			jsonResponse(translate('systmess_error_you_didnt_pass_bot_check'));
		}

		$validator_rules = array(
			array(
				'field' => 'user_email',
				'label' => 'Email',
				'rules' => array('required' => '','no_whitespaces' => '', 'valid_email' => '')
			)
		);

		$this->validator->set_rules($validator_rules);
		if(!$this->validator->validate()){
			jsonResponse($this->validator->get_array_errors());
		}

		$userEmail = cleanInput($_POST['user_email'], true);

		$this->load->model("Auth_model", 'auth_hash');
		$user_hash = $this->auth_hash->get_hash(getEncryptedEmail($userEmail));

		if(empty($user_hash)){
			jsonResponse(translate('auth_form_forgot_error_email_not_found'));
		}

		$this->load->model('User_Model', 'users');
		$users = $this->users->get_simple_users_by_id_principal($user_hash['id_principal']);

		if($users[0]['status'] == 'new'){
			jsonResponse(translate('auth_form_forgot_error_not_confirmed_email', array('{{HTML_LINK}}' => '<a class="fancyboxValidateModal fancybox.ajax" data-mw="500" href="' . __SITE_URL . 'register/popup_forms/resend_confirmation_email" data-title="' . translate('auth_form_resend_confirmation_link_data_title', null, true) . '">' . translate('general_link_text') . '</a>')));
        }

        // if(session()->__get('resent_reset_password_count') >= config('resend_forgot_password_email_limit')){
        //     jsonResponse(translate('system_message_resend_password_reset_email_limit_exhausted', array('{contact_page_link}' => __SITE_URL.'contact', '{support_email}' => config('noreply_email', 'support@exportportal.com'))), 'info');
        // }
        //Email user When this changes password - confirm password reset
		//default reset code
		$code = get_sha1_token($users[0]['email']);
		$this->users->updateUserResetCodeByPrincipal($users[0]['id_principal'], $code);

        if (__CURRENT_SUB_DOMAIN === getSubDomains()['shippers']) {
            $this->sendEmailEplResetPassword($users, $code);
        } else {
            $this->sendEmailResetPassword($users, $code);
        }

        session()->__set('resent_reset_password_count', ((int) session()->__get('resent_reset_password_count') + 1));

        $notice = array(
            'add_date' => date('Y/m/d H:i:s'),
            'add_by' => 'System',
            'notice' => 'Forgot password init.'
        );

        foreach($users as $user){
            $this->users->set_notice($user['idu'], $notice);
        }

		jsonResponse(translate('auth_form_forgot_success_message'), 'success');
	}

	public function reset() {
		if (logged_in()){
			headerRedirect();
		}

		$data['code'] = $code = $this->uri->segment(3);

		if(empty($code)){
			show_404();
		}

		$user = model('user')->check_reset_code($code);

		if(empty($user)){
            if (__CURRENT_SUB_DOMAIN !== getSubDomains()['shippers']) {
                showOopsSomethingWrong();
            } else{
                showOopsSomethingWrong(false);
                return;
            }
        }

		$data['id_principal'] = $data['idPrincipal'] = (int) $user['id_principal'];

        if (__CURRENT_SUB_DOMAIN === getSubDomains()['shippers']) {
            $this->showEplResetPasswordPage($data);
        } else {
            $this->showResetPasswordPage($data);
        }
	}

	function reset_ajax(){

        // list($token, $captcha_challenge) = $this->__validate_recaptcha();

		$validator_rules = array(
			array(
				'field' => 'pwd',
				'label' => 'Password',
				'rules' => array('required' => '', 'valid_password' => '')
			),
			array(
				'field' => 'pwd_confirm',
				'label' => 'Retype password',
				'rules' => array('required' => '','matches[pwd]' => '')
			),
			array(
				'field' => 'code',
				'label' => 'Code',
				'rules' => array('required' => '')
			),

		);
		$this->validator->set_rules($validator_rules);

		if(!$this->validator->validate()){
			jsonResponse($this->validator->get_array_errors());
		}

        $id_principal = (int) $_POST['id_principal'];
		$code = cleanInput($_POST['code']);
		$user = model(User_Model::class)->check_reset_code($code);

		if(empty($user)){
			jsonResponse(translate('systmess_error_invalid_data'));
		}

		if($user['status'] == 'new'){
			jsonResponse(translate('systmess_error_not_active_account'));
		}

        if((int) $user['id_principal'] !== $id_principal){
			jsonResponse(translate('systmess_error_invalid_data'));
        }

        model(Auth_Model::class)->change_hash($user['id_principal'], array(
            'token_password' => getEncryptedPassword($_POST['pwd']),
            'is_legacy' => 0,
            'reset_password_date' => date('Y-m-d H:i:s')
        ));

        model(User_Model::class)->updateUserByIdPrincipal($user['id_principal'], array(
            'activation_code' => get_sha1_token($user['email']),
            'reset_password_date' => date('Y-m-d H:i:s')
        ));

		model(User_Model::class)->set_notice($user['idu'], array(
			'add_date' => date('Y/m/d H:i:s'),
			'add_by' => 'System',
			'notice' => 'Password has been reset.'
		));

		jsonResponse(translate('auth_reset_password_success_message', array('{{HTML_START_LINK}}' => '<a href="' . __SITE_URL . 'login">', '{{HTML_END_LINK}}' => '</a>')), 'success');
    }

    function reset_legacy_ajax(){
        $id_principal = (int) session()->__get('reset_id_principal');
        if(empty($id_principal)){
            jsonResponse(translate("systmess_internal_server_error"));
        }

		$this->load->model('User_Model', 'users');
        $users = $this->users->get_simple_users_by_id_principal($id_principal);

        if(empty($users)){
            jsonResponse(translate('systmess_error_invalid_data'));
        }

		if($users[0]['status'] == 'new'){
			jsonResponse(translate('auth_form_forgot_error_not_confirmed_email', array('{{HTML_LINK}}' => '<a class="js-validate-modal" data-mw="500" href="' . __SITE_URL . 'register/popup_forms/resend_confirmation_email" data-title="' . translate('auth_form_resend_confirmation_link_data_title', null, true) . '">' . translate('general_link_text') . '</a>')));
        }

        if(session()->__get('resent_reset_password_count') >= config('resend_forgot_password_email_limit')){
            jsonResponse(translate('system_message_resend_password_reset_email_limit_exhausted', array('{contact_page_link}' => __SITE_URL.'contact', '{support_email}' => config('noreply_email', 'support@exportportal.com'))), 'info');
        }

        //Email user When this changes password - confirm password reset
		//default reset code
		$code = get_sha1_token($users[0]['email']);
		$this->users->updateUserResetCodeByPrincipal($users[0]['id_principal'], $code);

        if (__CURRENT_SUB_DOMAIN === getSubDomains()['shippers']) {
            $this->sendEmailEplResetPassword($users, $code);
        } else {
            $this->sendEmailResetPassword($users, $code);
        }

        session()->__set('resent_reset_password_count', ((int) session()->__get('resent_reset_password_count') + 1));

        $notice = array(
            'add_date' => date('Y/m/d H:i:s'),
            'add_by' => 'System',
            'notice' => 'Forgot password init.'
        );

        foreach($users as $user){
            $this->users->set_notice($user['idu'], $notice);
        }

        session()->clear('reset_id_principal');

		jsonResponse(translate('auth_form_forgot_success_message'), 'success');
    }

	function checkSession(){
		if($id_user = id_session()){
			model('user')->updateUserMain($id_user, array(
				'last_active' => date('Y-m-d H:i:s')
			));

            jsonResponse("Session is updated", "success");
		}
    }

    function ajax_operations() {
        if (!isAjaxRequest()) {
			headerRedirect();
		}

        $op = $this->uri->segment(3);

        switch ($op) {
        case 'logout_warning':
            jsonResponse(
                null,
                'success',
                [
                    'isLogged' => (bool) logged_in(),
                    'content'  => views()->fetch('new/auto_logout/warning_logout_view'),
                    'footer'   => views()->fetch('new/auto_logout/status_logout_footer_view')
                ],
            );
        break;
        }
    }

	function popup_forms() {
		if (!isAjaxRequest()) {
			headerRedirect();
		}

		$op = $this->uri->segment(3);

		switch ($op) {
			case 'login':
				$data['popup_login'] = 1;
				$this->view->display('new/authenticate/login_form_view', $data);
            break;
		}
	}

	public function ajax_no_auto_logout()
	{
        checkIsAjax();
        checkIsLoggedAjax();

        model('user')->updateUserMain(session()->__get('id'), array('not_auto_logout_date' => date('Y-m-d')));
        session()->__set('not_auto_logout_date', date('Y-m-d'));
        jsonResponse("", 'success');
	}
}
