<?php

use App\Email\CleanSession;
use App\Email\EplClearSession;
use App\Messenger\Message\Event\Lifecycle\UserWasActiveEvent;
use ExportPortal\Bridge\Mailer\Mime\RefAddress;
use ExportPortal\Bridge\Symfony\Component\Messenger\MessengerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 */
class Login_Controller extends TinyMVC_Controller
{
    private function showLoginPopup($aditionalData = null)
    {
        $data['popup_login'] = 1;

        if (isset($aditionalData)) {
            $data = array_merge($data, $aditionalData);
        }

        views()->display('new/authenticate/login_form_view', $data);
    }

    private function showLoginPage($data = [])
    {

        views()->assign($data);
        views()->display('new/header_view');
        views()->display('new/authenticate/login_view');
        views()->display('new/footer_view');
    }

    private function showEplLoginPopup($aditionalData = null)
    {
        $data['popupLogin'] = 1;

        if (isset($aditionalData)) {
            $data = array_merge($data, $aditionalData);
        }

        views()->display('new/epl/authenticate/login_form_view', $data);
    }

    private function showEplLoginPage($aditionalData = null)
    {

        $data = [
            'showSimpleDelog' => true,
            'templateViews'   => [
                'headerOutContent' => 'epl/authenticate/login_view',
            ],
            'webpackData'     => [
                'styleCritical' => 'epl_styles_login',
                'pageConnect' 	=> 'epl_login_page',
            ],
        ];

        if (isset($aditionalData)) {
            $data = array_merge($data, $aditionalData);
        }

        views(["new/epl/template/index_view"], $data);
    }

    public function index()
    {
        if (isAjaxRequest()) {
            if (logged_in()) {
                messageInModal(translate('login_already_logged_in'));
            }

            if (__CURRENT_SUB_DOMAIN === getSubDomains()['shippers']) {
                $this->showEplLoginPopup();
            } else {
                $this->showLoginPopup();
            }
        } else {
            $featuredItems = request()->query->get('featured_items');

            if (logged_in()) {
                if (!empty($featuredItems)) {
                    $userInfo = model(User_Model::class)->getUser(id_session());

                    if (1 === (int) $userInfo['free_featured_items']) {
                        headerRedirect('authenticate/logout/login');
                    }
                }

                if (__CURRENT_SUB_DOMAIN === getSubDomains()['shippers']) {
                    headerRedirect(__SHIPPER_URL);
                } else {
                    headerRedirect();
                }
            }

            if (__CURRENT_SUB_DOMAIN === getSubDomains()['shippers']) {
                $this->showEplLoginPage();
            } else {
                $data = [
                    'title'              => translate('login_form_ttl'),
                    'main_social'        => true,
                    "showSubscribePopup" => false,
                    'webpackData'        => [
                        'customEncoreLinks' => true,
                        'styleCritical'     => 'login',
                        'pageConnect'       => 'login_page',
                    ],
                ];
                $this->showLoginPage($data);
            }
        }
    }

    public function login_ajax()
    {
        checkIsAjax();
        $validator_rules = [
            [
                'field' => 'email',
                'label' => translate('login_email_label'),
                'rules' => ['required' => '', 'no_whitespaces' => '', 'valid_email' => ''],
            ],
            [
                'field' => 'password',
                'label' => translate('login_password_label'),
                'rules' => ['required' => ''],
            ],
        ];

        $this->validator->set_rules($validator_rules);
        if (!$this->validator->validate()) {
            jsonResponse($this->validator->get_array_errors());
        }

        $email = cleanInput(arrayGet($_POST, 'email'), true);
        $password = arrayGet($_POST, 'password');
        $referer = cleanInput(arrayGet($_POST, 'referer', ''));
        $remember = isset($_POST['remember']) ? true : false;

        $this->load->library('Auth', 'auth');

        $this->auth->email = $email;
        $this->auth->password = $password;
        $this->auth->remember = $remember;

        $this->auth->token_email = getEncryptedEmail($email);

        if (!$this->auth->exists_user_by_email_hash()) {
            jsonResponse(translate('login_no_user_by_email'));
        } else {
            /** @var Users_Model $userModel */
            $userModel = model(Users_Model::class);
            $user = $userModel->findOneBy([
                'with'   => ['group'],
                'scopes' => [
                    'email' => $email,
                ]
            ]);
            $userType = $user['user_type']->value;

            if ($userType === 'shipper' && __CURRENT_SUB_DOMAIN !== getSubDomains()['shippers']) {
                jsonResponse(
                    translate('info_shipper_login_to_ep', ['{{START_LINK}}' => '<a href="' . __SHIPPER_URL . 'login">', '{{END_LINK}}' =>'</a>']),
                    'info',
                    ['userType' => $userType]
                );
            } elseif ($userType !== 'shipper' && $userType !== 'ep_staff' && __CURRENT_SUB_DOMAIN === getSubDomains()['shippers']) {
                jsonResponse(
                    translate('info_not_shipper_login_to_epl',  ['{{START_LINK}}' => '<a href="' . __SITE_URL . 'login">', '{{END_LINK}}' =>'</a>']),
                    'info',
                    ['userType' => $userType]
                );
            }
        }

        $auth_hash = $this->auth->get_auth_row();

        if ($auth_hash['is_legacy']) {
            session()->__set('reset_id_principal', $auth_hash['id_principal']);
            $reset_data = [
                'additional_action'=> 'reset',
            ];
            jsonResponse(translate('login_choose_account_text'), 'success', $reset_data);
        }

        if (!$this->auth->exists_user_by_password()) {
            jsonResponse(translate('login_password_not_correct'));
        }

        session()->clear('accounts');
        $accounts = $this->auth->get_accounts_by_tokens();

        $this->auth->id_principal = $accounts[0]['id_principal'];

        if ($this->auth->is_legacy) {
            model('Auth')->change_hash(
                $this->auth->id_principal,
                [
                    'is_legacy'      => 0,
                    'token_password' => getEncryptedPassword($password), ]
            );
        }

        if (count($accounts) > 1) {
            session()->__set('accounts', $accounts);
            session()->__set('remember', (int) $remember);

            $choose_account_content = $this->view->fetch('new/authenticate/mobile_login_view');

            $choose_data = [
                'additional_action'     => 'choose_account',
                'choose_account_content'=> $choose_account_content,
                'choose_account_url'    => __CURRENT_SUB_DOMAIN_URL . 'login/choose_account_view',
                'remember'              => (int) $remember,
            ];
            jsonResponse(translate('login_choose_account_text'), 'success', $choose_data);
        }

        $result = $this->auth->login();

        $this->sendActivitySignal((int) id_session());
        $this->_return_response_login($result, null, $referer);
    }

    public function choose_account_view()
    {
        checkIsAjax();

        $accounts = $this->session->accounts;
        if (logged_in()) {
            //return all accounts except current for logged in
            $accounts = array_filter($this->session->accounts, function ($account) {
                return $account['idu'] != $this->session->id;
            });
        }

        $this->load->model('User_Model', 'users');
        $data = [
            'accounts' => $accounts,
            'remember' => $this->session->remember,
        ];

        $this->view->assign($data);
        $this->view->display('new/authenticate/choose_account_view');
    }

    public function ajax_login_selected_account()
    {
        checkIsAjax();

        $id_user = intval(arrayGet($_POST, 'id_user'));

        if (!in_array($id_user, array_column($this->session->accounts, 'idu'))) {
            jsonResponse(translate('login_wrong_user_text'));
        }

        $this->load->library('Auth', 'auth');
        $this->auth->remember = intval(arrayGet($_POST, 'remember'));
        $result = $this->auth->login_by_id($id_user);

        /** @todo transfer actualize user in elasticsearch on RabbitMQ */
        /** @var Elasticsearch_Users_Model $elasticsearchUsersModel */
        $elasticsearchUsersModel = model(Elasticsearch_Users_Model::class);
        $elasticsearchUsersModel->sync($id_user);

        $this->sendActivitySignal((int) $id_user);
        $this->_return_response_login($result, $id_user);
    }

    public function view_clean_session()
    {
        checkIsAjax();

        $uri = $this->uri->uri_to_assoc();
        $data = [
            'id_user'                => $uri['id'],
            'show_form'              => true,
            'choose_another_account' => true,
        ];

        $this->view->display('new/authenticate/clean_session_view', $data);
    }

    public function explore_user()
    {
        checkIsAjax();
        checkAdminAjax('login_as_user');

        $id_user = (int) ($_POST['user'] ?? null);
        if (empty($id_user)) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }

        $result = library(TinyMVC_Library_Auth::class)->explore_user($id_user);
        $this->sendActivitySignal((int) $id_user);

        jsonResponse($result['message'], $result['status'], ['redirect' => __SITE_URL]);
    }

    public function exit_explore_user()
    {
        checkIsAjax();

        if (!admin_logged_as_user()) {
            jsonResponse(translate('login_no_permission_text'));
        }

        $id_user = admin_logged_as_id();
        $this->load->library('Auth', 'auth');

        switch (user_group_type()) {
            case 'CR Affiliate':
                $redirect_url = get_dynamic_url('cr_users/administration');

            break;

            default:
                $redirect_url = cookies()->getCookieParam('exitExploreRedirectUrl') ?? get_dynamic_url('users/administration');

                cookies()->removeCookie('exitExploreRedirectUrl');

            break;
        }

        $result = $this->auth->explore_user($id_user, true);

        jsonResponse($result['message'], $result['status'], ['redirect' => $redirect_url]);
    }

    public function clean_session_request()
    {
        checkIsAjax();
        $by_id = (bool) arrayGet($_POST, 'by_id', false);

        if (!empty($delay = session()->delay_on_email_send) && $delay > time()) {
            jsonResponse(translate(
                'system_message_time_between_email_send',
                ['{{TIME_BETWEEN_EMAIL_SEND}}' => config('time_between_email_send')]
            ));
        }

        $this->load->model('User_Model', 'users');

        if (!$by_id) {
            //by email
            $validator = $this->validator;
            $validator_rules = [
                [
                    'field' => 'email',
                    'label' => 'Email',
                    'rules' => ['required' => '', 'no_whitespaces' => '', 'valid_email' => ''],
                ],
            ];
            $this->validator->set_rules($validator_rules);

            if (!$validator->validate()) {
                jsonResponse($this->validator->get_array_errors());
            }

            $email = cleanInput($_POST['email'], true);

            $this->load->model('Auth_Model', 'auth_hash');
            $encrypted_email = getEncryptedEmail($email);
            if (!$this->auth_hash->exists_hash($encrypted_email)) {
                jsonResponse(translate('login_non_existent_email_message'));
            }
            $hashed = $this->auth_hash->get_hash($encrypted_email);
            $user = $this->users->get_one_user_by_id_principal($hashed['id_principal']);

            $token = get_sha1_token($email . 'clean_session_request');
        } else {
            $id = cleanInput(arrayGet($_POST, 'id'), true);

            if (session()->__get('id') == $id) {
                jsonResponse(translate('login_clear_session_current_user_message'));
            }
            if (!$this->users->exist_user($id)) {
                jsonResponse(translate('login_non_existent_user'));
            }

            $user = $this->users->getLoginInfoById($id);

            $token = get_sha1_token($user['email'] . 'clean_session_request');
        }

        // UPDATE USER CLEAR SESSION TOKEN
        $update = [
            'clean_session_token' => $token,
            'clean_session_time'  => date('Y-m-d H:i:s'),
        ];

        $this->users->updateUserMain($user['idu'], $update);

        if (DEBUG_MODE) {
            destroyUserSession($user['idu']);
            jsonResponse(translate('login_session_cleared_message'), 'success');
        }

        /** @var MailerInterface $mailer */
        $mailer = $this->getContainer()->get(MailerInterface::class);

        try {
            if (__CURRENT_SUB_DOMAIN === getSubDomains()['shippers']) {
                $mailer->send(
                    (new EplClearSession("{$user['fname']} {$user['lname']}", $token))
                        ->to(new RefAddress((string) $user['idu'], new Address($user['email'])))
                );
            } else {
                $mailer->send(
                    (new CleanSession("{$user['fname']} {$user['lname']}", $token))
                        ->to(new RefAddress((string) $user['idu'], new Address($user['email'])))
                );
            }
        } catch (\Throwable $th) {
            jsonResponse(translate('email_has_not_been_sent'));
        }

        $notice = [
            'add_date' => date('Y/m/d H:i:s'),
            'add_by'   => 'System',
            'notice'   => 'Clear session',
        ];
        $this->users->set_notice($user['idu'], $notice);

        session()->delay_on_email_send = time() + (60 * config('time_between_email_send'));
        jsonResponse(
            translate('login_email_sent_message', ['[[EMAIL]]' => $user['email']]),
            'success',
            [session()->delay_on_email_send]
        );
    }

    public function clean_session()
    {
        $token = $this->uri->segment(3);
        if ('' == $token) {
            $this->session->setMessages(translate('login_token_not_correct_message'), 'errors');
            headerRedirect('/');
        }

        $this->load->model('User_Model', 'users');
        $exist_token = $this->users->exist_user_by_clean_session_token($token);
        if ($exist_token < 1) {
            $this->session->setMessages(translate('login_token_not_correct_message'), 'errors');
            headerRedirect('/');
        }

        $user = $this->users->getUserByCleanSessionToken($token);
        destroyUserSession($user['idu']);

        $this->session->setMessages(translate('login_session_cleared_message'), 'success');
        headerRedirect('/login');
    }

    private function _return_response_login($result, $id_user = null, $referer = '')
    {
        $return = [];
        if (isset($result['code'])) {
            $return['status'] = $result['code'];
        }
        if (!empty($referer)) {
            $return['referer'] = $referer;
        }

        if ('logged' == $result['code'] && isset($id_user)) {
            $return['clean_session_url'] = __CURRENT_SUB_DOMAIN_URL . 'login/view_clean_session/id/' . $id_user;
        }

        jsonResponse($result['message'], $result['status'], $return);
    }

    /**
     * @deprecated
     *
     * @param mixed $email
     * @param mixed $password
     * @param mixed $logged_from
     */
    private function login_user($email, $password, $logged_from = '')
    {
        $this->load->library('Auth', 'auth');
        $this->auth->email = $email;
        $this->auth->password = $password;
        $this->auth->logged_from = $logged_from;

        return $this->auth->login();
    }

    private function clean_user_session($user = [])
    {
        $accounts = session()->__get('accounts');
        library('session')->destroyBySessionId($user['ssid']);
        session()->__set('accounts', $accounts);

        $this->load->model('User_Model', 'users');
        $this->users->updateUserMain($user['idu'], [
            'clean_session_token' => '',
            'logged'              => 0,
            'ssid'                => session_id(),
            'user_ip'             => getVisitorIP(),
            'last_active'         => date('Y-m-d H:i:s'),
            'cookie_salt'         => genRandStr(8),
        ]);
    }

    /**
     * Send signal about user activity.
     */
    private function sendActivitySignal(int $userId): void
    {
        /** @var MessengerInterface $messenger */
        $messenger = container()->get(MessengerInterface::class);
        $messenger->bus('event.bus')->dispatch(new UserWasActiveEvent($userId));
    }
}
