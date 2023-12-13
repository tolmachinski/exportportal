<?php

use App\Session_logs\Messages as SessionLogsMessages;
use App\Session_logs\Types as SessionLogsTypes;
use Symfony\Component\DependencyInjection\ContainerInterface;
use App\Messenger\Message\Command\UpdateUserIdForBuyerIndustryStats;
use ExportPortal\Bridge\Symfony\Component\Messenger\MessengerInterface;

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [01.12.2021]
 * library refactoring: old model used, code style, optimization
 *
 * @link https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/d.-Libraries/d.-Auth
 */
class TinyMVC_Library_Auth
{
    public $email = null;
    public $password = false;
    public $salt = null;
    public $remember = false;
    public $tmvc = null;
    public $cookie_params = null;
    public $logged_from = '';
    public $user = false;
    public $admin_access = false;
    public $exporedByAdmin = false;
    public $auth_email = '';
    public $auth_password = '';
    public $idu = null;
    public $accounts = null;
    public $id_principal = null;
    public $is_legacy = 0;

	public function __construct(ContainerInterface $container)
    {
        $this->tmvc = $container->get(TinyMVC_Controller::class);
	}

    private function _get_user_full_by_id(){
        $this->tmvc->load->model('User_Model', 'users');

        if(!$this->tmvc->users->exist_user($this->idu))
            return array('status' => 'error', 'message' => translate('login_non_existent_user'));
        else {
            $this->user = $this->tmvc->users->getLoginInfoById($this->idu);

            $return = $this->_get_user_status_errors();
            if(isset($return['status'])){
                return $return;
            }
        }

        return array('status' => 'success');
    }
    private function _get_user(){

        $this->tmvc->load->model('Auth_Model', 'auth_hash');
        $this->tmvc->load->model('User_Model', 'users');
        $encrypted = getEncryptedEmail($this->email);

        if(!$this->tmvc->auth_hash->exists_hash($encrypted))
            return array('status' => 'error', 'message' => translate('login_non_existent_email_message'));
        else {
            $this->user = $this->tmvc->users->getLoginInfoByIdPrincipal($this->id_principal);

            if(!$this->user){
                return array('status' => 'error', 'message' => translate('login_not_correct_password_message'));
            }
            $return = $this->_get_user_status_errors();
            if(isset($return['status'])){
                return $return;
            }
        }

        return array('status' => 'success');
    }

    private function _get_user_status_errors(){
        if (!$this->user['email_confirmed']) {
            if (__CURRENT_SUB_DOMAIN === getSubDomains()['shippers']) {
                $info[] = translate('login_not_confirmed_email_message',
                    [
                        '[[LINK_TAG_START]]'  => '
                            <span
                                class="btn-fancybox js-fancybox"
                                data-type="ajax"
                                data-mw="500"
                                data-src="'. __CURRENT_SUB_DOMAIN_URL .'register/popup_forms/resend_confirmation_email?email='.$this->email.'"
                                data-title="',
                        '[[LINK_FIRST_END]]'  => '">',
                        '[[LINK_SECOND_END]]' => '</span>'
                    ]
                );
            } else {
                $info[] = translate('login_not_confirmed_email_message',
                    [
                        '[[LINK_TAG_START]]'  => '
                            <span
                                class="btn-fancybox fancyboxValidateModal fancybox.ajax"
                                data-mw="500"
                                data-fancybox-href="'. __CURRENT_SUB_DOMAIN_URL .'register/popup_forms/resend_confirmation_email?email='.$this->email.'"
                                data-title="',
                        '[[LINK_FIRST_END]]'  => '">',
                        '[[LINK_SECOND_END]]' => '</span>'
                    ]
                );
            }
        } else {
            switch($this->user['status']){
                case 'blocked':
                    if (__CURRENT_SUB_DOMAIN === getSubDomains()['shippers']) {
                        $warnings[] = translate('login_account_blocked_message', [
                                '[[LINK_TAG_START]]'  => '<span
                                        class="btn-fancybox js-fancybox"
                                        type="button"
                                        data-type="ajax"
                                        data-mw="760"
                                        data-src="'. __CURRENT_SUB_DOMAIN_URL .'contact/popup_forms/contact_us"
                                        data-title="',
                                '[[LINK_FIRST_END]]'  => '">',
                                '[[LINK_SECOND_END]]' => '</span>'
                            ]
                        );
                    } else {
                        $warnings[] = translate('login_account_blocked_message', [
                                '[[LINK_TAG_START]]'  => '
                                    <span
                                        class="btn-fancybox fancybox.ajax fancyboxValidateModal"
                                        type="button"
                                        data-mw="760"
                                        data-wrap-class="fancybox-contact-us"
                                        data-fancybox-href="'. __CURRENT_SUB_DOMAIN_URL .'contact/popup_forms/contact_us"
                                        data-title="',
                                '[[LINK_FIRST_END]]'  => '">',
                                '[[LINK_SECOND_END]]' => '</span>'
                            ]
                        );
                    }
                break;
                case 'inactive':
                    if (__CURRENT_SUB_DOMAIN === getSubDomains()['shippers']) {
                        $errors[] = translate('login_account_deactivated_message', [
                                '[[LINK_TAG_START]]'  => '
                                    <span
                                        class="btn-fancybox js-fancybox"
                                        type="button"
                                        data-type="ajax"
                                        data-mw="760"
                                        data-src="'. __CURRENT_SUB_DOMAIN_URL .'contact/popup_forms/contact_us"
                                        data-title="',
                                '[[LINK_FIRST_END]]'  => '">',
                                '[[LINK_SECOND_END]]' => '</span>'
                            ]
                        );
                    } else {
                        $errors[] = translate('login_account_deactivated_message', [
                                '[[LINK_TAG_START]]'  => '
                                    <span
                                        class="btn-fancybox fancyboxValidateModal fancybox.ajax"
                                        type="button"
                                        data-mw="760"
                                        data-wrap-class="fancybox-contact-us"
                                        data-fancybox-href="'. __CURRENT_SUB_DOMAIN_URL .'contact/popup_forms/contact_us"
                                        data-title="',
                                '[[LINK_FIRST_END]]'  => '">',
                                '[[LINK_SECOND_END]]' => '</span>'
                            ]
                        );
                    }
                break;
                case 'deleted':
                    if (__CURRENT_SUB_DOMAIN === getSubDomains()['shippers']) {
                        $errors[] = translate('login_account_deleted_message', [
                                '[[LINK_TAG_START]]'  => '
                                    <span
                                        class="btn-fancybox js-fancybox"
                                        type="button"
                                        data-type="ajax"
                                        data-mw="760"
                                        data-src="'. __CURRENT_SUB_DOMAIN_URL .'contact/popup_forms/contact_us"
                                        data-title="',
                                '[[LINK_FIRST_END]]'  => '">',
                                '[[LINK_SECOND_END]]' => '</span>'
                            ]
                        );
                    } else {
                        $errors[] = translate('login_account_deleted_message', [
                                '[[LINK_TAG_START]]'  => '
                                    <span
                                        class="btn-fancybox fancyboxValidateModal fancybox.ajax"
                                        type="button"
                                        data-mw="760"
                                        data-wrap-class="fancybox-contact-us"
                                        data-fancybox-href="'. __CURRENT_SUB_DOMAIN_URL .'contact/popup_forms/contact_us"
                                        data-title="',
                                '[[LINK_FIRST_END]]'  => '">',
                                '[[LINK_SECOND_END]]' => '</span>'
                            ]
                        );
                    }
                break;
            }
        }

        if(!empty($warnings)){
            return array('status' => 'warning', 'message' => $warnings);
        }

        if(!empty($errors)){
            return array('status' => 'error', 'message' => $errors);
        }

        if(!empty($info)){
            return array('status' => 'info', 'message' => $info);
        }

        if($this->user['logged'] == 1){
            return array('status' => 'info', 'message' => translate('login_already_logged_in_account'), 'code' => 'logged');
        }

        return false;
    }
    private function _get_user_md5()
    {
        $this->tmvc->load->model('User_Model', 'users');
        $this->tmvc->load->model('Auth_Model', 'auth_hash');

        $email = substr($this->cookie_params['ep_r'], 0, 128);

        $this->auth_password = substr($this->cookie_params['ep_r'], 168);

        $user_id = substr($this->cookie_params['ep_r'], 128, 32);
        $cookie_salt = substr($this->cookie_params['ep_r'], 160, 8);

        if(!$this->tmvc->auth_hash->exists_hash($email, $this->auth_password)){
            return false;
        }

        $this->user = $this->tmvc->users->getLoginInfo_md5($user_id);
        if(!$this->user){
            return false;
        }

        if($this->user['cookie_salt'] != $cookie_salt){
            $this->tmvc->cookies->setCookieParam('ep_r', '');
            return false;
        }

        if($this->user['status'] == 'new'){
            return false;
        }

        if(!empty($this->user['id_principal'])){

            $this->accounts = $this->_get_additional_accounts_for_session($this->user['id_principal'], 'principal');
        }

        // DISABLED FOR TESTING PERIOD
        // if($this->user['logged'] == 1){
        //     return false;
        // }

        return true;
    }

    public function set_accounts_in_session($id_user)
    {
        session()->__set('accounts', $this->_get_additional_accounts_for_session($id_user));
    }

    private function _get_additional_accounts_for_session($id = null, $by = 'id_user')
    {
        $this->tmvc->load->model('User_Model', 'users');

        if($by == 'id_user'){
            $users = $this->tmvc->users->get_related_users_by_user_id($id ?? $this->user['idu']);
        }else{
            $users = $this->tmvc->users->get_related_users_by_id_principal($id ?? $this->user['id_principal']);
        }

        foreach($users as $k => $user){
            switch ($user['gr_type']) {
                case 'Buyer':
                    if(!empty($company = model(Company_Buyer_Model::class)->get_company_by_user($user['idu']))){
                        $users[$k]['company_name'] = $company['company_name'];
                    }
                break;
                case 'Seller':
                    if(!empty($company = model(Company_Model::class)->get_seller_base_company($user['idu']))){
                        $users[$k]['company_name'] = $company['name_company'];

                        $companyType = model(Company_Model::class)->get_company_type($company['id_type']);
                        if (!empty($companyType['group_name_suffix'])) {
                            $users[$k]['group_name_suffix'] = $companyType['group_name_suffix'];
                        }
                    }
                break;
            }

            $users[$k]['gr_name'] = $user['is_verified'] ? $user['gr_name'] : trim(str_replace('Verified', '', $user['gr_name']));
        }

        return $users;
    }

    private function _get_user_by_id($id_user = 0){
        $this->tmvc->load->model('User_Model', 'users');
        $this->user = $this->tmvc->users->getLoginInfoById($id_user);

        if(!$this->user){
            return array('status' => 'error', 'message' => translate('login_user_not_exist'));
        }

        return array('status' => 'success');
    }

    function _get_user_company() {

        switch ($this->user['gr_type']) {
            case 'Seller':
            case 'Company Staff':
                $user_companies_rel = model(Company_Model::class)->get_user_companies_rel(array('id_user' => $this->user['idu']));
                if (!empty($user_companies_rel)) {
                    $companies = array();
                    foreach ($user_companies_rel as $ucompany) {
                        if ($ucompany['company_type'] == 'company') {
                            $company = model(Company_Model::class)->get_simple_company((int) $ucompany['id_company']);
                            if (!empty($company)) {
                                $company_type = model(Company_Model::class)->get_company_type($company['id_type']);

                                if (!empty($company_type['group_name_suffix'])) {
                                    session()->group_name_suffix = $company_type['group_name_suffix'];
                                }

                                session()->id_company = $company['id_company'];
                                session()->index_company = $company['index_name'];
                                session()->name_company = $company['name_company'];

                                if ($this->user['user_type'] == 'users_staff') {
                                    session()->my_seller = $company['id_user'];
                                }
                            }
                        }
                        $companies[] = $ucompany['id_company'];
                    }
                    session()->companies = $companies;
                }
            break;
            case 'Shipper':
            case 'Shipper Staff':
                $this->tmvc->load->model('Shippers_Model', 'shippers');
                $company = $this->tmvc->shippers->get_user_shipper_details($this->user['idu']);
                if (!empty($company)) {
                    $this->tmvc->load->model('Shipper_Countries_Model', 'shipper_countries');
                    $this->tmvc->session->select_locations = $this->tmvc->shipper_countries->exist_shipper_countries($this->user['idu']);

                    $this->tmvc->session->shipper_id_company = $company['id'];
                    $this->tmvc->session->shipper_name_company = $company['co_name'];
                    if ('shipper_staff' == $this->user['user_type']) {
                        $shipper_user = $this->users->get_simple_user($company['id_user'], 'u.email');
                        $this->tmvc->session->my_shipper = $company['id_user'];
                        $this->tmvc->session->my_shipper_email = $shipper_user['email'];
                    }
                }
            break;
        }
    }

    private function _get_user_followed(){
        $this->tmvc->load->model('Followers_Model', 'followers');
        $followed = $this->tmvc->followers->get_user_followed($this->user['idu']);
        if(!empty($followed)){
            $this->tmvc->session->followed = explode(',',$followed);
        } else{
            $this->tmvc->session->followed = array();
        }
    }

    private function _get_user_company_saved(){
        $this->tmvc->load->model('Company_Model', 'company');
        $company_saved = $this->tmvc->company->getSavedCompanies($this->user['idu']);
        if(!empty($company_saved)){
            $this->tmvc->session->company_saved = explode(',',$company_saved);
        } else{
            $this->tmvc->session->company_saved = array();
        }
    }

    private function _get_user_shippers_saved(){
        $this->tmvc->load->model("Shippers_Model", 'shippers');
        $shippers_saved = $this->tmvc->shippers->get_saved_shippers($this->user['idu']);
        if(!empty($shippers_saved)){
            $this->tmvc->session->shippers_saved = explode(',',$shippers_saved);
        } else{
            $this->tmvc->session->shippers_saved = array();
        }
    }

    private function _update_last_viewed()
    {
        /** @var Items_Model $itemsModel */
        $itemsModel = model(Items_Model::class);

        $itemsModel->updateIdUserByCookie(
            $this->user['idu'],
            getEpClientIdCookieValue()
        );
    }

    private function setBuyerStatId()
    {
        if(check_group_type('Buyer'))
        {
            /** @var MessengerInterface $messenger */
            $messenger = container()->get(MessengerInterface::class);
            $messenger->bus('command.bus')->dispatch(new UpdateUserIdForBuyerIndustryStats($this->user['idu'], getEpClientIdCookieValue()));
        }
    }

    private function _keep_signed(){

        $this->tmvc->load->model('Auth_Model', 'auth_hash');

        $hashed = $this->tmvc->auth_hash->get_hash_by_id_principal($this->user['id_principal']);

        $this->salt = genRandStr(8);
        if($this->remember){
            $encyrpted_email = $hashed['token_email'];
            $this->tmvc->cookies->setCookieParam('ep_r',
            $encyrpted_email
            . md5($this->idu ?? $this->user['idu'])
            . $this->salt
            . $hashed['token_password'], 3600*24*30);
        } elseif($this->tmvc->cookies->exist_cookie('ep_r')){
            $this->salt = $this->user['cookie_salt'];
        }
    }

    private function _get_user_basket(){
        if(have_right('buy_item')){
            $this->tmvc->load->model('Basket_Model', 'basket');
            $this->tmvc->session->basket = $this->tmvc->basket->count_basket_items($this->user['idu']);
        }
    }

    private function _update_user_main(){
        $this->tmvc->load->model('User_Model', 'users');
        $info = array(
            'user_ip' => getVisitorIP(),
            'logged' => '1',
            'last_active' => date('Y-m-d H:i:s'),
            'ssid' => session_id(),
            'cookie_salt' => $this->salt
        );

        if($this->password === false && $this->logged_from != '')
            $info['logged_from'] = $this->logged_from;

        $this->tmvc->users->updateUserMain($this->user['idu'], $info);
    }

    /**
     * Method created to set user complete profile data in session
     *
     * @param int $userId
     *
     * @return void
     */
    public function setUserCompleteProfile(int $userId): void
    {
        /** @var Users_Complete_Profile_Options_Model $usersCompleteProfileOprionsModel */
        $usersCompleteProfileOprionsModel = model(Users_Complete_Profile_Options_Model::class);

        $completeProfile = [
            'options'           => [],
            'total_completed'   => 100,
        ];

        if (!empty($userProfileOptions = $usersCompleteProfileOprionsModel->getUsersProfileOptions([$userId])[$userId] ?? [])) {
            $profileCompletionPercent = 20;
            $countCompletedOptions = 0;
            foreach ($userProfileOptions as $option) {
                if (1 == $option['option_completed']) {
                    $profileCompletionPercent += (int) $option['option_percent'];
                    $countCompletedOptions++;
                }
            }

            $completeProfile = [
                'options'               => array_column($userProfileOptions, null, 'option_alias'),
                'total_completed'       => $profileCompletionPercent,
                'countOptions'          => count($userProfileOptions),
                'countCompleteOptions'  => $countCompletedOptions,
            ];
        }

        if (id_session() == $userId) {
            session()->__set('completeProfile', $completeProfile);
        } else {
            /** @var TinyMVC_Library_Session $sessionLibrary */
            $sessionLibrary = library(TinyMVC_Library_Session::class);

            $sessionLibrary->updateLoggedUserSession($userId, compact('completeProfile'));
        }
    }

    function _get_user_menu(){
        $custom_menu_array = json_decode($this->user['menu'], true);
        $blocked = false;
        if($this->tmvc->session->group_expired){
            $blocked = true;
            $custom_menu_array = array(
                array(
                    'col' => 1,
                    'cell' => 1,
                    'tab' => 'dashboard',
                    'name' => 'upgrade_extend'
                ),
                array(
                    'col' => 1,
                    'cell' => 2,
                    'tab' => 'dashboard',
                    'name' => 'billing'
                )
            );
        }

		$menu_rights = dashboard_nav_rights($blocked);

		$menu_full_for_session = array();
		foreach($menu_rights as $key_tab => $menu_rights_tab){
			$menu_full_for_session[$key_tab]['params'] = $menu_rights_tab['params'];

			foreach($menu_rights_tab['items'] as $key_item => $menu_rights_item){
				if(!empty($menu_rights_item)){

					if(!empty($menu_rights_item['right']) && !have_right_or($menu_rights_item['right'])){
						continue;
                    }

                    switch ($key_item) {
                        case 'company_page':
                            $menu_rights_item['link'] = getMyCompanyURL();
                        break;
                        case 'company_info':
                            if(in_array(user_group_type(), ['Shipper', 'Shipper Staff'])){
                                $menu_rights_item['link'] = __SITE_URL . "company/edit/".strForURL(my_shipper_company_name()).'-'.my_shipper_company_id();
                            }else{
                                $menu_rights_item['link'] = __SITE_URL . "company/edit/".strForURL(my_company_name()).'-'.my_company_id();
                            }
                        break;
                        case 'ff_company_page':
                            $menu_rights_item['link'] = __SITE_URL . 'shipper/'.strForURL(my_shipper_company_name().' '.my_shipper_company_id());
                        break;
                        case 'my_page':
                        case 'cr_my_page':
                            $menu_rights_item['link'] = getMyProfileLink();
                        break;
                        default:
                            $menu_rights_item['link'] = !empty($menu_rights_item['external_link']) ? $menu_rights_item['external_link'] : getUrlForGroup($menu_rights_item['link']);
                        break;
                    }

					$menu_full_for_session[$key_tab]['items'][$key_item] = array(
						'tab' => $key_tab,
						'name' => $key_item,
						'title' => $menu_rights_item['title'],
						'popup' => arrayGet($menu_rights_item, 'popup'),
						'popup_width' => arrayGet($menu_rights_item, 'popup_width'),
						'link' => $menu_rights_item['link'],
						'icon' => $menu_rights_item['icon'],
                        'icon_color' => $menu_rights_item['icon_color'],
                        'external_link' => $menu_rights_item['external_link'] ?? null,
                        'target' => $menu_rights_item['target'] ?? null,
                        'add_class' => $menu_rights_item['add_class'],
                        'new' => $menu_rights_item['new'] ?? false
					);

					if(isset($menu_rights_item['popup'])){
						$menu_full_for_session[$key_tab]['items'][$key_item]['popup'] = $menu_rights_item['popup'];
                    }

                    if(isset($menu_rights_item['popup_width'])){
						$menu_full_for_session[$key_tab]['items'][$key_item]['popup_width'] = $menu_rights_item['popup_width'];
					}
				}
			}
        }

		if(empty($custom_menu_array)){
        	$this->tmvc->load->model('Usergroup_Model', 'ugroup');
            $custom_menu = $this->tmvc->ugroup->getGroup($this->user['user_group'], 'menu');
            $custom_menu_array = json_decode($custom_menu['menu'], true);
        }

		$menu_for_session = array();

        if(!empty($custom_menu_array)){
            $menu_for_session = array_filter(array_map(function($menu_item) use($menu_full_for_session){
                if(empty($menu_rights_select = $menu_full_for_session[$menu_item['tab']]['items'][$menu_item['name']])){
                    return;
                }

                $menu_rights_select['col'] = $menu_item['col'];
                $menu_rights_select['cell'] = $menu_item['cell'];

                return $menu_rights_select;
            }, $custom_menu_array));
        }

		$this->tmvc->session->menu = json_encode($menu_for_session, JSON_HEX_APOS);
		$this->tmvc->session->menu_full = $menu_full_for_session;
    }

	private function _get_admin_menu(){
		$custom_menu_array = json_decode($this->user['menu'], true);
		$menu_rights = dashboard_admin_nav_rights();
		$menu_full_for_session = array();

		foreach($menu_rights as $key_tab => $menu_rights_tab){
			$menu_full_for_session[$key_tab]['params'] = $menu_rights_tab['params'];

			foreach($menu_rights_tab['items'] as $key_item => $menu_rights_item){
				if(!empty($menu_rights_item)){

					if(!empty($menu_rights_item['right']) && !have_right_or($menu_rights_item['right'])){
						continue;
					}

					$menu_full_for_session[$key_tab]['items'][$key_item] = array(
						'tab' => $key_tab,
						'name' => $key_item,
						'title' => $menu_rights_item['title'],
						'link' => __SITE_URL . $menu_rights_item['link'],
						'icon' => $menu_rights_item['icon'],
						'icon_color' => $menu_rights_item['icon_color'],
					);
				}
			}
		}


		if(!isset($custom_menu_array)){
        	$this->tmvc->load->model('Usergroup_Model', 'ugroup');
            $custom_menu = $this->tmvc->ugroup->getGroup($this->user['user_group'], 'menu');
            $custom_menu_array = json_decode($custom_menu['menu'], true);
        }

        $menu_for_session = array();

        if(!empty($custom_menu_array)){
            $menu_for_session = array_filter(array_map(function($menu_item) use($menu_full_for_session){
                if(empty($menu_rights_select = $menu_full_for_session[$menu_item['tab']]['items'][$menu_item['name']])){
                    return;
                }

                $menu_rights_select['col'] = $menu_item['col'];
                $menu_rights_select['cell'] = $menu_item['cell'];

                return $menu_rights_select;
            }, $custom_menu_array));
        }

		$this->tmvc->session->menu = json_encode($menu_for_session, JSON_HEX_APOS);
		$this->tmvc->session->menu_full = $menu_full_for_session;
    }

    private function _get_rights($rights_params = array()){

        $this->tmvc->load->model('Usergroup_Model', 'ugroup');
        $user_rights = $this->tmvc->ugroup->getUserRights($this->user['user_group'], $rights_params);

        $user_status = $this->user['status'];

        if($user_status == 'active'){
            $aditional_rights = $this->tmvc->ugroup->get_aditional_rights($this->user['idu']);

            if(!empty($aditional_rights)){
                foreach($aditional_rights as $aditional_right){
                    $user_rights[] = $aditional_right['r_alias'];
                }
            }
        }

        return $user_rights;
    }

    public function _get_group_and_rights(){
        $this->tmvc->load->model('Usergroup_Model', 'ugroup');
        $gr_alias = $this->tmvc->ugroup->getGroup($this->user['user_group'], 'gr_alias');
        $this->tmvc->session->group_name = $this->tmvc->session->is_verified ? $this->user['gr_name'] : trim(str_replace('Verified', '', $this->user['gr_name']));
        $this->tmvc->session->group_type = $this->user['gr_type'];
        $this->tmvc->session->group_alias = $gr_alias['gr_alias'];
        $user_status = $this->user['status'];

        $rights_params = array();
        if($user_status == 'pending'){
            $rights_params = array('for_pending_user' => 1);
        }

        switch($this->user['gr_type']){
            case 'Buyer':
            case 'Seller':
            case 'CR Affiliate':
            case 'EP Staff':
            case 'Admin':
                $this->tmvc->session->rights = $this->_get_rights($rights_params);
            break;
            case 'Company Staff':
                $this->tmvc->session->rights = $this->tmvc->ugroup->getCompanyStaffUserRights($this->user['idu']);
            break;
            case 'Shipper':
                $this->tmvc->session->rights = $this->_get_rights();
            break;
            case 'Shipper Staff':
                $this->tmvc->session->rights = $this->tmvc->ugroup->getShipperStaffUserRights($this->user['idu']);
            break;
        }

    }

    private function _set_user_session_data(){

        $accounts = $this->tmvc->session->accounts;
        if($this->admin_access){
            $id_admin = $this->exporedByAdmin ? session()->id_admin : session()->id;
            $admin_name = $this->exporedByAdmin ? session()->admin_name : session()->fname . ' ' . session()->lname;
            $admin_group = $this->exporedByAdmin ? session()->admin_group : session()->group;
            $admin_email = $this->exporedByAdmin ? session()->admin_email : session()->email;
            session_destroy();
            session_start(['use_strict_mode' => 1]);
            $this->tmvc->session->id_admin = $id_admin;
            $this->tmvc->session->admin_name = $admin_name;
            $this->tmvc->session->admin_group = $admin_group;
            $this->tmvc->session->admin_email = $admin_email;
        } else{
            session_destroy();
            session_start(['use_strict_mode' => 1]);
        }
        if(!empty($accounts)){
            $this->tmvc->session->accounts = $accounts;
        }elseif(!empty($this->accounts)){
            $this->tmvc->session->accounts = $this->accounts;
        }
        $this->tmvc->session->loggedIn = true;
        $this->tmvc->session->id = $this->user['idu'];
        $this->tmvc->session->level = $this->user['group'] ?? null;
        $this->tmvc->session->fname = $this->user['fname'];
        $this->tmvc->session->lname = $this->user['lname'];
        $this->tmvc->session->legal_name = $this->user['legal_name'];
        $this->tmvc->session->email = $this->user['email'];
        $this->tmvc->session->group = $this->user['user_group'];
        $this->tmvc->session->user_type = $this->user['user_type'];
        $this->tmvc->session->is_muted = $this->user['is_muted'];
        $this->tmvc->session->notify_email = $this->user['notify_email'];
        $this->tmvc->session->subscription_email = $this->user['subscription_email'];
		$this->tmvc->session->paid = $this->user['paid'];
		$this->tmvc->session->paid_until = $this->user['paid_until'];
		$this->tmvc->session->paid_price = $this->user['paid_price'];
		$this->tmvc->session->id_principal = $this->user['id_principal'];
        $this->tmvc->session->group_expired = 0;
        $this->tmvc->session->is_verified = (int) $this->user['is_verified'];
        $this->tmvc->session->not_auto_logout_date = $this->user['not_auto_logout_date'];
        $this->tmvc->session->status = $this->user['status'];
        $this->tmvc->session->fakeUser = (int) $this->user['fake_user'];
        $this->tmvc->session->isModel = (int) $this->user['is_model'];
        $this->tmvc->session->user_photo_with_badge = $this->user['user_photo_with_badge'];

        if($this->user['paid_until'] != '0000-00-00'){
            //region certification expire soon
            $verifyCertification = verifyCertificationExpireSoon($this->user['paid_until']);
            $this->tmvc->session->paidUntil = $this->user['paid_until'];

            if((bool)$verifyCertification['notify']){
                $this->tmvc->session->certificationExpireSoon = $verifyCertification['days'];
            }
            //endregion certification expire soon

            if(isDateExpired($this->user['paid_until'])){
                $this->tmvc->session->group_expired = 1;

                // $this->tmvc->session->setMessages(translate('login_package_expired_message', array(
                //     '[[LINK_START]]' => '<a href="'.__SITE_URL.'upgrade">',
                //     '[[LINK_END]]' => '</a>'
                //     )),'warning');
            }
        }

		$this->tmvc->session->user_photo = $this->user['user_photo'];
		$this->tmvc->session->country = $this->user['country'];

		if($this->user['user_type'] == 'user' && !$this->user['paid']){
			$this->tmvc->session->setMessages(translate('login_group_bill_message', array(
                '[[LINK_START]]' => '<a href="'.__SITE_URL.'billing/my">',
                '[[LINK_END]]' => '</a>'
                )),'warning');
        }

        // SET GROUP LANG RESTRICTION FLAG & ID LIST
        $this->tmvc->session->group_lang_restriction = false;
        $this->tmvc->session->group_lang_restriction_list = array();
        if(isset($this->user['gr_lang_restriction_enabled'])) {
            $this->tmvc->session->group_lang_restriction = filter_var($this->user['gr_lang_restriction_enabled'], FILTER_VALIDATE_BOOLEAN);
        }
        if($this->tmvc->session->group_lang_restriction) {
            $language_restrictions = $this->tmvc->users->get_user_lang_restriction($this->user['idu'], array('columns' => array('languages')));
            $language_restrictions = json_decode($language_restrictions['languages']);
            $this->tmvc->session->group_lang_restriction_list = null !== $language_restrictions ? $language_restrictions : array();
        }

        // GET SESSION GROUP AND RIGHTS DATA
        $this->_get_group_and_rights();

        // GET COMPANY INFO - FOR SELLERS

        $this->_get_user_company();

		if($this->user['user_type'] == 'ep_staff'){
			$this->_get_admin_menu(); // GET admin MENU
		}else{
			$this->_get_user_menu(); // GET USER MENU
        }

        // GET FOOLOWED USERS
        $this->_get_user_followed();

        // GET SAVED COMPANIES
        $this->_get_user_company_saved();

        // GET SAVED SHIPPERS
        $this->_get_user_shippers_saved();

        // GET LAST VIEWED ITEMS
        $this->_update_last_viewed();

        // GET USER BASKET - ONLY FOR BUYERS
        $this->_get_user_basket();

        // SET COMPLETE PROFILE
        $this->setUserCompleteProfile((int) $this->user['idu']);

        // UPDATE USER STATS BY COOKIE
        $this->setBuyerStatId();

        // GET POPUPS
        $this->_set_user_popups();
        widgetGetPopups();

        if(!$this->admin_access){
            // KEEP USER SIGNED
            $this->_keep_signed();

            // UPDATE USER MAIN
            $this->_update_user_main();
        }
    }

    private function _set_user_popups(){
        if(have_right('manage_personal_items')){
            /** @var User_Popups_Model $userPopups */
            $userPopups = model(User_Popups_Model::class);
            $idUser = (int)$this->user['idu'];

            $popupBulk = $userPopups->findOneBy([
                'conditions' => [
                    'id_user'   => $idUser,
                    'id_popup'  => 24,
                ],
            ]);

            if (empty($popupBulk)) {
                $userPopups->insertOne([
                    'id_user'	    => $idUser,
                    'id_popup'      => 24,
                    'is_viewed'     => 0,
                    'add_date'      => new DateTimeImmutable(date('Y-m-d H:i:s')),
                ]);
            }
        }
    }

    public function exists_user_by_email_hash()
    {
        $this->tmvc->load->model('Auth_Model', 'auth_hash');
        return (bool) $this->tmvc->auth_hash->exists_hash($this->token_email);
    }

    public function exists_user_by_password()
    {
        $this->tmvc->load->model('Auth_Model', 'auth_hash');
        $hash = $this->get_auth_row();

        if($hash['is_legacy']){
            $this->is_legacy = 1;
            return checkPassword($this->password, $hash['token_password'], true, $this->email);
        }
        return checkPassword($this->password, $hash['token_password']);
    }

    public function get_auth_row()
    {
        $this->tmvc->load->model('Auth_Model', 'auth_hash');
        return $this->tmvc->auth_hash->get_hash($this->token_email);
    }

    public function get_accounts_by_tokens()
    {
        $hash = $this->get_auth_row();
        return $this->_get_additional_accounts_for_session($hash['id_principal'], 'principal');
    }

	public function login(){
        // GET USER INFO
        $result = $this->_get_user();
        if($result['status'] != 'success'){
            return $result;
        }

        model('session_logs')->handler_insert(array(
            'id_user' => $this->user['idu'],
            'log_type' => SessionLogsTypes\LOGGED_IN,
            'log_message' => SessionLogsMessages\LOGGED_IN
        ));

        // SET USER SESSION COMPONENTS
        $this->_set_user_session_data();

        return array('status' => 'success', 'message' => '');
    }

	public function login_by_id($idu){
        // GET USER INFO
        $this->idu = $idu;
        $result = $this->_get_user_full_by_id();

        if($result['status'] != 'success'){
            return $result;
        }

        if(logged_in()){
            $this->logout();
        }
        model('session_logs')->handler_insert(array(
            'id_user' => $this->user['idu'],
            'log_type' => SessionLogsTypes\LOGGED_IN,
            'log_message' => SessionLogsMessages\LOGGED_IN
        ));

        if (null != session()->id_admin) {
            $this->admin_access = true;
            $this->exporedByAdmin = true;
        }

        // SET USER SESSION COMPONENTS
        $this->_set_user_session_data();

        return array('status' => 'success', 'message' => '');
    }

    public function logout(){
        model('user')->updateUserMain(id_session(), array(
			'logged' => 0,
			'last_active' => date('Y-m-d H:i:s')
        ));

        model('session_logs')->handler_insert(array(
            'id_user' => id_session(),
            'log_type' => SessionLogsTypes\LOGGED_OUT,
            'log_message' => SessionLogsMessages\LOGGED_OUT
        ));

        /** @todo transfer actualize user in elasticsearch on RabbitMQ */
        /** @var Elasticsearch_Users_Model $elasticsearchUsersModel */
        $elasticsearchUsersModel = model(Elasticsearch_Users_Model::class);
        $elasticsearchUsersModel->sync(id_session());

        session()->destroy();

        cookies()->setCookieParam('ep_r', '');
    }

    public function explore_user($id_user = 0, $exit_access = false){
		is_allowed("freq_allowed_login");

        // GET USER INFO
        $result = $this->_get_user_by_id($id_user);
        if($result['status'] != 'success'){
            return $result;
        }

        if(!$exit_access){
            $this->admin_access = true;

            $info = array(
                'logged' => '0',
                'last_active' => date('Y-m-d H:i:s')
            );

            model(User_Model::class)->updateUserMain(id_session(), $info);

            model('session_logs')->handler_insert(array(
                'id_user' => $id_user,
                'log_type' => SessionLogsTypes\START_EXPLORE_BY_ADMIN,
                'log_message' => str_replace(
                    array('[ADMIN_DATA]'),
                    array(user_name_session() . ' [' . id_session() . ']'),
                    SessionLogsMessages\START_EXPLORE_BY_ADMIN
                ),
            ));
        } else {
            model('session_logs')->handler_insert(array(
                'id_user' => id_session(),
                'log_type' => SessionLogsTypes\END_EXPLORE_BY_ADMIN,
                'log_message' => str_replace(
                    array('[ADMIN_DATA]'),
                    array(admin_logged_as_name() . ' [' . admin_logged_as_id() . ']'),
                    SessionLogsMessages\END_EXPLORE_BY_ADMIN
                ),
            ));
        }

        // SET USER SESSION COMPONENTS
        $this->_set_user_session_data();
        session()->__set('accounts', $this->accounts = $this->_get_additional_accounts_for_session($id_user));

        return array('status' => 'success', 'message' => '');
    }

	public function login_from_cookie(){
        if(!$this->tmvc->cookies->exist_cookie('ep_r')){
            return false;
        }

        $this->cookie_params = array(
            'ep_r' => $this->tmvc->cookies->getCookieParam('ep_r')
        );

        // GET USER INFO
        if(!$this->_get_user_md5()){
            return false;
        }

        // SET USER SESSION COMPONENTS
        $this->_set_user_session_data();

        model('session_logs')->handler_insert(array(
            'id_user' => $this->user['idu'],
            'log_type' => SessionLogsTypes\LOGGED_IN_COOKIE,
            'log_message' => SessionLogsMessages\LOGGED_IN_COOKIE
        ));

        return true;
    }
}
