<?php

use App\Common\Buttons\ChatButton;

use App\Common\Contracts\Email\EmailTemplate;
use App\Email\CrDeleteRequest;
use App\Email\CrSendActivationLink;
use App\Email\GroupEmailTemplates;
use ExportPortal\Bridge\Mailer\Mime\RefAddress;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\BodyRendererInterface;
use function Symfony\Component\String\u;

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [15.12.2021]
 * Controller Refactoring
 *
 * country representative users application controller
 *
 * @property \TinyMVC_Load              $load
 * @property \TinyMVC_View              $view
 * @property \TinyMVC_Library_URI       $uri
 * @property \TinyMVC_Library_Session   $session
 * @property \TinyMVC_Library_Cookies   $cookies
 * @property \TinyMVC_Library_Upload    $upload
 * @property \TinyMVC_Library_validator $validator
 * @property \Translations_Model        $translations
 * @property \User_Model                $user
 * @property \Cr_users_Model            $cr_users
 * @property \Usergroup_Model           $usergroup
 * @property \Notify_Model              $notify
 *
 * @author Cravciuc Andrei
 */

class Cr_users_Controller extends TinyMVC_Controller
{
    private function _load_main(){
        $this->load->model('User_Model', 'user');
        $this->load->model('Cr_users_Model', 'cr_users');
        $this->load->model('Usergroup_Model', 'usergroup');
    }

    public function administration() {
        checkAdmin('manage_cr_users');

        $this->load->model('Usergroup_Model', 'usergroup');
        $this->load->model('Country_Model', 'country');

        $data['groups'] = $this->usergroup->getGroupsByType(array('type' => "'CR Affiliate'"));
        $data['countries'] = $this->country->get_countries();

        $group = $this->uri->segment(4);
        if($group){
            $data['group'] = $group;
        }

        $this->view->assign($data);
        $this->view->assign('title', 'User');
        $this->view->display('admin/header_view');
        $this->view->display('admin/cr/users/index_view');
        $this->view->display('admin/footer_view');
    }

    public function requests() {
        checkAdmin('manage_cr_users');

        $this->load->model('Country_Model', 'country');
        $data['countries'] = $this->country->get_countries();

        $this->view->assign($data);
        $this->view->assign('title', 'User');
        $this->view->display('admin/header_view');
        $this->view->display('admin/cr/users_requests/index_view');
        $this->view->display('admin/footer_view');
    }

    public function ajax_operations(){
        if (!isAjaxRequest()){
            headerRedirect();
        }

        $this->_load_main();

        $action = $this->uri->segment(3);
        switch ($action) {
            case 'add_user':
                checkAdminAjax('manage_cr_users');

                $validator_rules = array(
                    array(
                        'field' => 'domains',
                        'label' => 'Domain(s)',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'fname',
                        'label' => 'First Name',
                        'rules' => array('required' => '', 'valid_user_name' => '')
                    ),
                    array(
                        'field' => 'lname',
                        'label' => 'Last Name',
                        'rules' => array('required' => '', 'valid_user_name' => '')
                    ),
                    array(
                        'field' => 'group',
                        'label' => 'Group',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'email',
                        'label' => 'Email',
                        'rules' => array('required' => '','no_whitespaces' => '', 'valid_email' => '')
                    )
                );

                $this->validator->set_rules($validator_rules);
                if(!$this->validator->validate()){
                    jsonResponse($this->validator->get_array_errors());
                }

                $this->load->model('Cr_domains_Model', 'domains');
                $this->load->model('Auth_model', 'auth_hash');
                $encrypted_email = getEncryptedEmail(cleanInput($_POST['email'], true));

                if($this->auth_hash->exists_hash($encrypted_email)){
                    jsonResponse('The email already exists in the database. Please choose another one!');
                }

                $domains = array_filter($_POST['domains']);
                if(empty($domains)){
                    jsonResponse('Please select at least one domain.');
                }

                $domains_info = $this->domains->get_cr_domains(array('domains_list' => implode(',', $domains)));
                if(empty($domains_info)){
                    jsonResponse('Please select at least one domain.');
                }

                $id_group = (int)$_POST['group'];
                $group_info = $this->usergroup->getGroup($id_group);
                if(empty($group_info)){
                    jsonResponse('The group does not exist.');
                }

                if($group_info['gr_type'] != 'CR Affiliate'){
                    jsonResponse('You can not create account in this group.');
                }

                $group_rights = $this->usergroup->getUserRights($id_group);
                if(!have_right('cr_international', $group_rights) && count($domains_info) > 1){
                    jsonResponse('Account from this group can not be assigned to more than one domain.');
                }

				$email = cleanInput($_POST['email'], true);
                $password = generateRandomPassword();
                $insert = array(
                    'fname' => cleanInput($_POST['fname']),
                    'lname' => cleanInput($_POST['lname']),
                    'email' => $email,
                    'user_group' => $id_group,
                    'user_ip' => getVisitorIP(),
                    'registration_date' => date('Y-m-d H:i:s'),
                    'activation_code' => get_sha1_token(cleanInput($_POST['email'], true)),
                    'accreditation_token' => get_sha1_token($email),
                    'status' => 'new',
                    'country' => $domains_info[0]['id_country'],
                    'user_type' => 'user',
                    'paid' => 1
                );

				$notices[] = json_encode(array(
					'add_date' => date('Y/m/d H:i:s'),
					'add_by' => 'System',
					'notice' => 'The email with activation link has been sent.'
                ));

                $notices[] = json_encode(array(
					'add_date' => date('Y/m/d H:i:s'),
					'add_by' => 'System',
					'notice' => 'Account has been created.'
                ));

                $insert['notice'] = implode(',', $notices);

                //region add_hash
                $hash_insert = array(
                    'token_email' 	 => $encrypted_email,
                    'token_password' => getEncryptedPassword($password)
                );
                $insert['id_principal'] = model('principals')->insert_last_id();
                $this->auth_hash->add_hash($insert['id_principal'], $hash_insert);
                //endregion add_hash

                if (!$id_user = $this->user->setUserMain($insert)) {
                    jsonResponse('Error: You cannot add Country representative. Please try again later.');
                }

                $domains_relation = array();
                foreach ($domains_info as $domain_info) {
                    $domains_relation[] = array(
                        'id_user' => $id_user,
                        'id_domain' => $domain_info['id_domain']
                    );
                }

                if(!empty($domains_relation)){
                    $this->domains->set_user_domains_relation($domains_relation);
                }

                $this->cr_users->cr_set_user_additional(array('id_user' => $id_user));

                try {
                    /** @var MailerInterface $mailer */
                    $mailer = $this->getContainer()->get(MailerInterface::class);
                    $mailer->send(
                        (new CrSendActivationLink("{$insert['fname']} {$insert['lname']}", $group_info['gr_name'], $insert['activation_code'], $password))
                            ->to(new RefAddress((string) $id_user, new Address($insert['email'])))
                    );
                } catch (\Throwable $th) {
                    jsonResponse(translate('email_has_not_been_sent'));
                }

                jsonResponse('Success: Country representative has been added successfully.' , 'success');
            break;
            case 'edit_user':
                checkAdminAjax('manage_cr_users');

                $validator_rules = array(
                    array(
                        'field' => 'id_user',
                        'label' => 'User info',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'domains',
                        'label' => 'Domain(s)',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'fname',
                        'label' => 'First Name',
                        'rules' => array('required' => '', 'valid_user_name' => '')
                    ),
                    array(
                        'field' => 'lname',
                        'label' => 'Last Name',
                        'rules' => array('required' => '', 'valid_user_name' => '')
                    ),
                    array(
                        'field' => 'email',
                        'label' => 'Email',
                        'rules' => array('required' => '','no_whitespaces' => '', 'valid_email' => '')
                    ),
                    array(
                        'field' => 'group',
                        'label' => 'Group',
                        'rules' => array('required' => '')
                    )
                );

                $this->validator->set_rules($validator_rules);
                if(!$this->validator->validate()){
                    jsonResponse($this->validator->get_array_errors());
                }

                $id_user = (int) $_POST['id_user'];
				$user = $this->user->getUser($id_user);

				if(empty($user)){
					jsonResponse(translate("systmess_error_user_does_not_exist"));
                }

                if($user['gr_type'] != 'CR Affiliate'){
                    jsonResponse('Error: You can not assign this type of user to some domain(s).');
                }

                $email = cleanInput($_POST['email'], true);

                $this->load->model('Auth_model', 'auth_hash');
                $encrypted_email = getEncryptedEmail(cleanInput($email, true));
				if($email != $user['email'] && $this->auth_hash->exists_hash($encrypted_email)){
                    jsonResponse('Error: The email already exists in the database. Please choose another one!');
                }

                $email_changed = false;
                if($email != $user['email']){
                    $email_changed = true;
                }

                $this->load->model('Cr_domains_Model', 'domains');

                $domains = array_filter($_POST['domains']);
                if(empty($domains)){
                    jsonResponse('Error: Please select at least one domain.');
                }

                $domains_info = $this->domains->get_cr_domains(array('domains_list' => implode(',', $domains)));
                if(empty($domains_info)){
                    jsonResponse('Error: Please select at least one domain.');
                }

                $id_group = (int)$_POST['group'];
                $group_info = $this->usergroup->getGroup($id_group);
                if(empty($group_info)){
                    jsonResponse('Error: The group does not exist.');
                }

                if($group_info['gr_type'] != 'CR Affiliate'){
                    jsonResponse('Error: You can not create account in this group.');
                }

                $group_rights = $this->usergroup->getUserRights($id_group);
                if(!have_right('cr_international', $group_rights) && count($domains_info) > 1){
                    jsonResponse('Error: Account from this group can not be assigned to more than one domain.');
                }

                $fname = cleanInput($_POST['fname']);
				$lname = cleanInput($_POST['lname']);
				$update = array(
					'fname' => $fname,
					'lname' => $lname,
                    'user_group' => $id_group
                );

                if($email_changed){
                    $update['email'] = $email;
                }

				if($this->user->updateUserMain($id_user, $update)){
					if($email_changed){

                        $hash_update = array(
                            'token_email' 	 => $encrypted_email
                        );

                        $this->auth_hash->change_hash($user['id_principal'], $hash_update);

						$notice = array(
							'add_date' => date('Y/m/d H:i:s'),
							'add_by' => user_name_session(),
							'notice' => "The email has been changed from: {$user['email']} to {$email}. The user have to reset account password using forgot password page."
						);
                        $this->user->set_notice($id_user, $notice);
                    }

                    $domains_relation = array();
                    foreach ($domains_info as $domain_info) {
                        $domains_relation[] = array(
                            'id_user' => $id_user,
                            'id_domain' => $domain_info['id_domain']
                        );
                    }

                    if(!empty($domains_relation)){
                        $this->domains->delete_user_domains_relation($id_user);
                        $this->domains->set_user_domains_relation($domains_relation);
                    }

					jsonResponse('Changes has been saved successfully.' , 'success');
				}else{
					jsonResponse('Error: You cannot change the user info now. Please try again later');
				}
            break;
            case 'delete_user':
                checkAdminAjax('manage_cr_users');

                $validator_rules = array(
                    array(
                        'field' => 'user',
                        'label' => 'User info',
                        'rules' => array('required' => '')
                    )
                );

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()){
                    jsonResponse($this->validator->get_array_errors());
                }

                $id_user = (int) arrayGet($_POST, 'user');
                if(empty($user = $this->user->getUser($id_user))){
                    jsonResponse(translate("systmess_error_user_does_not_exist"));
                }

                if($user['gr_type'] != 'CR Affiliate'){
                    jsonResponse('You can not delete this type of user on this page. Please use users administration page instead.');
                }

                if($user['status'] != 'new'){
                    jsonResponse('This user can not be deleted.');
                }
                $all_related = $this->user->getIdPrincipalByUserId($id_user);
                if(count($all_related) == 1){
                    model('principals')->deleteOne($all_related[0]['id_principal']);
                    model('auth')->delete_one($all_related[0]['id_principal']);
                }
                $this->user->deleteUser($id_user);
                jsonResponse('The user has been deleted.', 'success');
            break;
            case 'list_dt':
                checkAdminAjaxDT('manage_cr_users');

                $this->load->model('User_Photo_Model', 'userphoto');
                $this->load->model("Country_model", 'country');

                $user_params['limit'] = intVal($_POST['iDisplayLength']);
                $user_params['start'] = intVal($_POST['iDisplayStart']);
                $user_params['sort_by'] = flat_dt_ordering($_POST, [
                    'dt_idu'        => 'u.idu',
                    'dt_fullname'   => 'CONCAT(u.fname, u.lname)',
                    'dt_email'      => 'u.email',
                    'dt_registered' => 'u.registration_date',
                    'dt_activity'   => 'u.last_active'
                ]);

                if (isset($_POST['group']))
                    $user_params['group'] = intval($_POST['group']);
                else {
                    $groups = $this->usergroup->getGroupsByType(array('type' => "'CR Affiliate'", 'counter' => false));
                    $lists = arrayToListKeys($groups, array('idgroup'));
                    $user_params['group'] = $lists['idgroup'];
                }

                $user_params = array_merge($user_params,
                    dtConditions($_POST, [
                        ['as' => 'keywords', 'key' => 'search', 'type' => 'cleanInput'],
                        ['as' => 'users_list', 'key' => 'id_user', 'type' => 'int'],
                        ['as' => 'country', 'key' => 'country', 'type' => 'int'],
                        ['as' => 'state', 'key' => 'state', 'type' => 'int'],
                        ['as' => 'status', 'key' => 'status', 'type' => 'cleanInput'],
                        ['as' => 'email_status', 'key' => 'email_status', 'type' => fn($v) => (string) u(cleanInput($v) ?: '')->prepend("'")->append("'")],
                        ['as' => 'city', 'key' => 'city', 'type' => 'int'],
                        ['as' => 'ip', 'key' => 'ip', 'type' => 'cleanInput'],
                        ['as' => 'logged', 'key' => 'online', 'type' => 'cleanInput'],
                        ['as' => 'registration_start_date', 'key' => 'reg_date_from', 'type' => 'getDateFormat:m/d/Y,Y-m-d'],
                        ['as' => 'registration_end_date', 'key' => 'reg_date_to', 'type' => 'getDateFormat:m/d/Y,Y-m-d'],
                        ['as' => 'activity_start_date', 'key' => 'activity_date_from', 'type' => 'getDateFormat:m/d/Y,Y-m-d'],
                        ['as' => 'activity_end_date', 'key' => 'activity_date_to', 'type' => 'getDateFormat:m/d/Y,Y-m-d']
                    ])
                );

                $records = arrayByKey($this->cr_users->cr_get_users($user_params), 'idu');
                $records_count = $this->cr_users->cr_count_users($user_params);
                $user_params['groups_list'] = "'CR Affiliate'";
                $groups_users_count = arrayByKey($this->usergroup->countUsersByGroups($user_params), 'idgroup');
                $output = array(
                    "sEcho" => intval($_POST['sEcho']),
                    "iTotalRecords" => $records_count,
                    "iTotalDisplayRecords" => $records_count,
                    "groups_users_count" => $groups_users_count,
                    "aaData" => array()
                );

                if(empty($records)){
                    jsonResponse('', 'success', $output);
                }

                $cities = array_filter(array_map(function($record){
                    return $record['city'];
                }, $records));

                $locations = array();
                if(!empty($cities)){
                    $locations = $this->country->get_cities_state(implode(',', $cities));
                }

                $users_list = implode(',', array_keys($records));
                $users_photos = $this->userphoto->get_photos(array('users_list' => $users_list));
                foreach ($users_photos as $photo){
                    $records[$photo['id_user']]['photos'][] = $photo;
                }

                $phone_codes = $this->country->get_ccodes();
                $country_codes = array();
                foreach ($phone_codes as $phone_code) {
                    $country_codes[$phone_code['id_country']][] = $phone_code['ccode'];
                }

                $email_status_labels = $this->user->get_emails_status_labels();

                foreach ($records as $records_key => $record) {
                    $country = "&mdash;";
                    $user_detail = array();

                    $photo = "<img class='mw-50 mh-50' src='" . getDisplayImageLink(array('{ID}' => $record['idu'], '{FILE_NAME}' => $record['user_photo']), 'users.main', array( 'thumb_size' => 1, 'no_image_group' => $record['user_group'] )) . "'/>";
                    $personal_page_btn = "<a class='ep-icon ep-icon_user' title='View personal page of " . $record['user_name'] . "' target='_blank' href='" . __SITE_URL . "country_representative/" . strForURL("{$record['user_name']} {$record['idu']}") . "'></a>";

                    $online = ($record['logged']) ? "online" : "offline";

                    if (!empty($record['showed_status'])){
                        $user_detail[] = '<tr>
                                            <td class="w-100">Showed status:</td>
                                            <td>' . $record['showed_status'].'</td>
                                          </tr>';
                    }

                    if (!empty($record['description'])){
                        $user_detail[] = '<tr>
                                            <td class="w-100">Description:</td>
                                            <td>' . $record['description'].'</td>
                                          </tr>';
                    }

                    if (!empty($record['user_country'])) {
                        $country = '<a class="dt_filter" data-value-text="' . $record['user_country'] . '" data-value="' . $record['country'] . '" data-title="Country" data-name="country">
                                        <img width="24" height="24" src="' . getCountryFlag($record['user_country']) . '" title="' . $record['user_country'] . ' ' . implode(', ', $country_codes[$record['country']]) .'" alt="' . $record['user_country'] . '">
                                    </a>';

                        $user_detail[] = '<tr>
                                            <td class="w-100">Country:</td>
                                            <td>' . $country.'</td>
                                          </tr>';
                    }

                    $address = array();
                    if(!empty($record['address'])){
                        $address[] = $record['address'];
                    }

                    if(!empty($locations[$record['city']])){
                        $address[] = $locations[$record['city']];
                    }

                    if(!empty($record['user_country'])){
                        $address[] = $record['user_country'];
                    }

                    if (!empty($address)) {
                        $user_detail[] = '<tr>
                                            <td class="w-100">Address:</td>
                                            <td>' . implode(', ', $address) .'</td>
                                         </tr>';
                    }

                    $email_status_label = '<br>
                                           <span
                                                class="label label-' . $email_status_labels[$record['email_status']]. '"
                                                title="Email status: ' . $record['email_status'] . '"
                                           >' . $record['email_status'] . '</span>';

                    $user_detail[] = '<tr>
                                        <td class="w-100">Email:</td>
                                        <td>' . $record['email'].'</td>
                                      </tr>';

                    if (!empty($record['phone'])){
                        $user_detail[] = '<tr>
                                            <td class="w-100">Phone:</td>
                                            <td>' . $record['phone_code'] .' '. $record['phone'].'</td>
                                          </tr>';
                    }

                    if (!empty($record['fax'])){
                        $user_detail[] = '<tr>
                                            <td class="w-100">Fax:</td>
                                            <td>' . $record['fax_code'] .' '. $record['fax'].'</td>
                                          </tr>';
                    }

                    if (!empty($record['photos'])) {
                        $photos_list = array();
                        foreach ($record['photos'] as $one_p)
                            $photos_list[] = '<div class="img-list-b pull-left mr-5 mb-5 relative-b"><img src="' . getDisplayImageLink(array('{ID}' => $record['idu'], '{FILE_NAME}' => $one_p['name_photo']), 'users.main', array( 'thumb_size' => 0, 'no_image_group' => $record['user_group'] )) . '" alt="img"/><a class="ep-icon ep-icon_remove txt-red absolute-b pos-r0 m-0 bg-white confirm-dialog" data-message="Are you sure you want to delete the user photo?" title="Delete user foto" data-callback="delete_user_image" data-image="' . $one_p['id_photo'] . '" data-user="' . $record['idu'] . '"></a></div>';

                        $photos_list = implode('', $photos_list);
                        $user_detail[] = '<tr>
                                            <td class="w-100">Photos:</td>
                                            <td>' . $photos_list .'</td>
                                          </tr>';
                    }

                    $checkbox = '<input type="checkbox" class="check-user mt-1" data-user="'.$record['idu'].'">';

                    $explore_user_btn = '';
                    if(have_right('login_as_user')){
                        $explore_user_btn = '<a class="ep-icon ep-icon_login confirm-dialog" data-message="Are you sure you want to explore user '. $record['user_name'] .'?" data-callback="explore_user" data-user="'.$record['idu'].'" title="Login as '. $record['user_name'] .'" href="#" data-title="Login as '. $record['user_name'] .'"></a>';
                    }

                    $delete_btn = '';
                    if($record['status'] == 'new'){
                        $delete_btn = '<a class="ep-icon ep-icon_remove txt-red confirm-dialog" data-callback="delete_user" data-user="'.$record['idu'].'" title="Delete user" href="#" data-message="Are you sure you want to delete user: '. $record['user_name'] .'"></a>';
                    }

                    $send_emails_url = __SITE_URL . "cr_users/popup_forms/send_email/{$record['idu']}";
                    $send_emails_button = "
                        <a href=\"{$send_emails_url}\"
                            title=\"Send email to {$record['user_name']}\"
                            class=\"fancyboxValidateModalDT fancybox.ajax\"
                            data-title=\"Send email from template to {$record['user_name']}\">
                            <i class=\"ep-icon ep-icon_envelope-send\"></i>
                        </a>
                    ";

                    //TODO: admin chat hidden
                    $btnChatUser = new ChatButton(['hide' => true, 'recipient' => $record['idu'], 'recipientStatus' => $record['status']], ['classes' => 'btn-chat-now', 'text' => '']);
                    $btnChat = $btnChatUser->button();

                    $output['aaData'][] = array(
                        'dt_idu'	=> $record['idu'].'<br/>'.$checkbox.' <a class="ep-icon ep-icon_plus call-function" data-callback="toggle_detail" title="View details"></a>',
                        "dt_fullname" => '<div class="tal">
                                            <a class="ep-icon ep-icon_onoff ' . (($record['logged']) ? 'txt-green' : 'txt-red') . ' dt_filter" title="Filter just '.$online.'" data-value="'.$record['logged'].'" data-name="online"></a>
                                            '.$personal_page_btn
                                            . $btnChat
                                            .'<a class="ep-icon ep-icon_envelope-send fancyboxValidateModal fancybox.ajax" href="' . __SITE_URL . 'contact/popup_forms/email_user/'.$record['idu'].'" title="Email '. $record['user_name'] .'" data-title="Email '. $record['user_name'] .'"></a>
                                        </div>
                                        <div>'.$record['user_name'].'</div>',
                        "dt_email" => $record['email'] . $email_status_label,
                        "dt_country" => $country,
                        "dt_gr_name" => '<div class="tal">
                                            <a class="ep-icon ep-icon_filter txt-green dt_filter" title="Group '.$record['gr_name'].'" data-value="'.$record['user_group'].'" data-name="group" data-title="Group" data-value-text="'.$record['gr_name'].'"></a>
                                        </div>
                                        '.capitalWord($record['gr_name']),
                        "dt_ip" => '<div class="tal">
                                        <a class="ep-icon ep-icon_filter txt-green dt_filter" title="IP:'.$record['user_ip'].'" data-value="'.$record['user_ip'].'" data-name="ip" data-title="IP" data-value-text="'.$record['user_ip'].'"></a>
                                    </div>
                                    <div>'.$record['user_ip'].'</div>',
                        "dt_registered" => formatDate($record['registration_date']),
                        "dt_activity" => formatDate($record['last_active']),
                        "dt_status" => '<div class="pull-left">
                                            <a class="ep-icon ep-icon_filter txt-green dt_filter" title="Filter just '.capitalWord($record['status']).'" data-value="'.$record['status'].'" data-name="status"></a>
                                        </div>
                                        <div>'.capitalWord($record['status']).'</div>',
                        "dt_records" => '<a class="ep-icon ep-icon_notice fancyboxValidateModal fancybox.ajax" title="Notices" href="'.__SITE_URL.'users/popup_show_notice/' . $record['idu'] . '" data-title="Notice for user '. $record['user_name'] .'"></a>',
                        "dt_actions" => '<a class="ep-icon ep-icon_pencil fancyboxValidateModalDT fancybox.ajax" title="Edit user" href="'.__SITE_URL.'cr_users/popup_forms/edit_user/' . $record['idu'] . '" data-title="Edit user '. $record['user_name'] .'"></a>
                                        '. $send_emails_button
                                        . $explore_user_btn
                                        . $delete_btn,
                        "dt_photo" => $photo,
                        "dt_detail" => implode('', $user_detail)
                    );
                }

                jsonResponse('', 'success', $output);
            break;
            case 'assign_users':
                $validator_rules = array(
                    array(
                        'field' => 'type',
                        'label' => 'Type',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'id_item',
                        'label' => 'Item ID',
                        'rules' => array('required' => '')
                    ),
                );

                $this->validator->set_rules($validator_rules);
                if(!$this->validator->validate()){
                    jsonResponse($this->validator->get_array_errors());
                }

                if (!empty($_POST['id_list'])) {
                    $id_users = $_POST['id_list'];
                } else {
                    $id_users = array();
                }

                switch ($_POST['type']) {
                    case 'training':
                        checkAdminAjax('cr_training_administration');

                        $this->load->model('Cr_users_Model', 'cr_users');
                        $this->load->model('Usergroup_Model', 'usergroup');
                        $this->load->model('Cr_training_Model', 'cr_training');

                        $groups = $this->usergroup->getGroupsByType(array('type' => "'CR Affiliate'", 'counter' => false));
                        $lists = arrayToListKeys($groups, array('idgroup'));

                        $params = array(
                            'group' => $lists['idgroup']
                        );

                        if (!empty($id_users)) {
                            $params['users_list'] = implode(',', $id_users);

                            $users_count = $this->cr_users->cr_count_users($params);
                            if (count($id_users) != $users_count) {
                                jsonResponse('Incorrect user IDs list');
                            }
                        }

                        $id_training = (int)$_POST['id_item'];

                        $training_users = $this->cr_training->get_assigned_users($id_training);
                        $training_users_ids = array();
                        foreach ($training_users as $training_user) {
                            $training_users_ids[] = $training_user['id_user'];
                        }

                        $users_to_assign = array_diff($id_users, $training_users_ids);
                        $users_to_delete = array_diff($training_users_ids, $id_users);

                        // assign users if exists new
                        if (!empty($users_to_assign)) {
                            $this->cr_training->assign_users($users_to_assign, $id_training);
                        }


                        // un-assign users if exists deleted
                        if (!empty($users_to_delete)) {
                            $this->cr_training->un_assign_users($users_to_delete, $id_training);
                        }


                        //update ambassadors count in cr_events table
                        $this->cr_training->update_training($id_training, array(
                            'training_count_ambassadors' => count($id_users)
                        ));

                        jsonResponse('Users assigned successfully' , 'success');
                    break;
                    case 'event':
                        checkAdminAjax('cr_events_administration');

                        $this->load->model('Cr_users_Model', 'cr_users');
                        $this->load->model('Usergroup_Model', 'usergroup');
                        $this->load->model('Cr_events_Model', 'cr_events');

                        $groups = $this->usergroup->getGroupsByType(array('type' => "'CR Affiliate'", 'counter' => false));
                        $lists = arrayToListKeys($groups, array('idgroup'));

                        $params = array(
                            'group' => $lists['idgroup']
                        );

                        if (!empty($id_users)) {
                            $params['users_list'] = implode(',', $id_users);

                            $users_count = $this->cr_users->cr_count_users($params);
                            if (count($id_users) != $users_count) {
                                jsonResponse('Incorrect user IDs list');
                            }
                        }

                        $id_event = (int)$_POST['id_item'];

                        $event_users = $this->cr_events->get_assigned_users($id_event, array('assigned_by_admin' => true));
                        $event_users_ids = array();
                        foreach ($event_users as $event_user) {
                            $event_users_ids[] = $event_user['id_user'];
                        }

                        $users_to_assign = array_diff($id_users, $event_users_ids);
                        $users_to_delete = array_diff($event_users_ids, $id_users);

                        // assign users if exists new
                        if (!empty($users_to_assign)) {
                            $this->cr_events->assign_users($users_to_assign, $id_event, true);
                        }


                        // un-assign users if exists deleted
                        if (!empty($users_to_delete)) {
                            $this->cr_events->un_assign_users($users_to_delete, $id_event);
                        }


                        //update ambassadors count in cr_events table
                        $this->cr_events->update_event($id_event, array(
                            'event_count_ambassadors' => $this->cr_events->count_assigned_users($id_event)
                        ));

                        jsonResponse('Users assigned successfully' , 'success');
                    break;
                    default:
                        jsonResponse('Incorrect type');
                    break;
                }
            break;
            case 'same_country':
                $validator_rules = array(
                    array(
                        'field' => 'user',
                        'label' => 'User info',
                        'rules' => array('required' => '')
                    )
                );

                $id_user = (int) $_POST['user'];
                $user_info = $this->user->getUser($id_user);
                if(empty($user_info)){
                    jsonResponse(translate("systmess_error_user_does_not_exist"));
                }

                if($user_info['gr_type'] != 'CR Affiliate'){
                    jsonResponse(translate("systmess_error_user_does_not_exist"));
                }

                $start = intVal($_POST['start']);
                $domain_users_params = array(
                    'status' => 'active',
                    'group_type' => "'CR Affiliate'",
                    'domains_info' => true,
                    'limit' => "{$start},10",
                    'not_users_list' => $user_info['idu']
                );

                $this->load->model('Cr_domains_Model', 'cr_domains');
                $data['user_domains'] = $this->cr_domains->get_user_domains_relation($id_user);
                $domains_list = array();
                foreach ($data['user_domains'] as $user_domain) {
                    $domains_list[] = $user_domain['id_domain'];
                }

                $domain_users_params['domains'] = implode(',', $domains_list);
                $data['domain_users'] = $this->cr_users->cr_get_users($domain_users_params);
                $domain_users_count = $this->cr_users->cr_count_users($domain_users_params);

                $this->view->assign($data);
                $content = $this->view->fetch('new/cr/user/cr_same_country_popup_item_view', $data);

                $this->view->assign($data);
                $content = $this->view->fetch('new/cr/user/cr_same_country_popup_item_view', $data);

                jsonResponse('','success', array('count' => $domain_users_count, 'html' => $content));
            break;
            case 'list_requests_dt':
                checkAdminAjaxDT('manage_cr_users');

                $this->load->model("Country_model", 'country');

                $user_params['limit'] = intVal($_POST['iDisplayLength']);
                $user_params['start'] = intVal($_POST['iDisplayStart']);
                $user_params['sort_by'] = flat_dt_ordering($_POST, [
                    'dt_request'    => 'ur.id_request',
                    'dt_fullname'   => 'CONCAT(ur.applicant_fname, " ", ur.applicant_lname)',
                    'dt_email'      => 'ur.applicant_email',
                    'dt_registered' => 'ur.applicant_created'
                ]);

                $user_params = array_merge($user_params,
                    dtConditions($_POST, [
                        ['as' => 'keywords', 'key' => 'search', 'type' => 'cleanInput'],
                        ['as' => 'country', 'key' => 'country', 'type' => 'int'],
                        ['as' => 'state', 'key' => 'state', 'type' => 'int'],
                        ['as' => 'status', 'key' => 'status', 'type' => fn($v) => (string) u(cleanInput($v) ?: '')->prepend("'")->append("'")],
                        ['as' => 'city', 'key' => 'city', 'type' => 'int'],
                        ['as' => 'registration_start_date', 'key' => 'reg_date_from', 'type' => 'getDateFormat:m/d/Y,Y-m-d'],
                        ['as' => 'registration_end_date', 'key' => 'reg_date_to', 'type' => 'getDateFormat:m/d/Y,Y-m-d']
                    ])
                );

                $records = $this->cr_users->cr_get_users_requests($user_params);
                $records_count = $this->cr_users->cr_count_users_requests($user_params);
                $output = array(
                    "sEcho" => intval($_POST['sEcho']),
                    "iTotalRecords" => $records_count,
                    "iTotalDisplayRecords" => $records_count,
                    "aaData" => array()
                );

                if(empty($records)){
                    jsonResponse('', 'success', $output);
                }

                $statuses = array(
                    'new' => array(
                        'title' => 'New',
                        'class' => 'ep-icon ep-icon_user-plus txt-green'
                    ),
                    'confirmed' => array(
                        'title' => 'Confirmed',
                        'class' => 'ep-icon ep-icon_user-ok txt-blue'
                    ),
                    'declined' => array(
                        'title' => 'Declined',
                        'class' => 'ep-icon ep-icon_user-minus txt-red'
                    ),
                );

                $phone_codes = $this->country->get_ccodes();
                $country_codes = array();
                foreach ($phone_codes as $phone_code) {
                    $country_codes[$phone_code['id_country']][] = $phone_code['ccode'];
                }

                foreach ($records as $records_key => $record) {
                    $country = "&mdash;";
                    if (!empty($record['applicant_country'])) {
                        $country = '<a class="dt_filter" data-value-text="' . $record['applicant_country'] . '" data-value="' . $record['id_country'] . '" data-title="Country" data-name="id_country">
                                        <img width="24" height="24" src="' . getCountryFlag($record['applicant_country']) . '" title="' . $record['applicant_country'] . ' ' . implode(', ', $country_codes[$record['id_country']]) .'" alt="' . $record['applicant_country'] . '">
                                    </a>';
                    }

                    $edit_url = __SITE_URL . "cr_users/popup_forms/edit_user_request/{$record['id_request']}";
                    $delete_url = __SITE_URL . "cr_users/popup_forms/delete_request/{$record['id_request']}";
                    $output['aaData'][] = array(
                        'dt_request'	=> $record['id_request'],
                        "dt_fullname" => $record['applicant_name'],
                        "dt_email" => $record['applicant_email'],
                        "dt_country" => $country,
                        "dt_registered" => formatDate($record['applicant_created']),
                        'dt_actions' => "
                            <a href=\"{$edit_url}\"
                                title=\"Edit {$record['applicant_name']}\"
                                class=\"fancyboxValidateModalDT fancybox.ajax\"
                                data-title=\"Edit {$record['applicant_name']}\">
                                <i class=\"ep-icon ep-icon_pencil\"></i>
                            </a>
                            <a href=\"{$delete_url}\"
                                title=\"Delete request\"
                                class=\"fancyboxValidateModalDT fancybox.ajax\"
                                data-title=\"Delete request\">
                                <i class=\"ep-icon ep-icon_remove txt-red\"></i>
                            </a>
                        ",
                        "dt_status" => '<span><i class="ep-icon '.$statuses[$record['applicant_status']]['class'].' fs-30"></i><br>'.$statuses[$record['applicant_status']]['title'].'</span>',
                    );
                }

                jsonResponse('', 'success', $output);
            break;
            case 'edit_user_request':
                checkAdminAjax('manage_cr_users');

                $validator_rules = array(
                    array(
                        'field' => 'id_request',
                        'label' => 'Request info',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'domains',
                        'label' => 'Domain(s)',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'fname',
                        'label' => 'First Name',
                        'rules' => array('required' => '','valid_user_name' => '', 'min_len[2]' => '', 'max_len[50]' => '')
                    ),
                    array(
                        'field' => 'lname',
                        'label' => 'Last Name',
                        'rules' => array('required' => '','valid_user_name' => '', 'min_len[2]' => '', 'max_len[50]' => '')
                    ),
                    array(
                        'field' => 'email',
                        'label' => 'Email',
                        'rules' => array('required' => '','no_whitespaces' => '', 'valid_email' => '')
                    ),
                    array(
                        'field' => 'group',
                        'label' => 'Group',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'status',
                        'label' => 'Status',
                        'rules' => array('status' => '')
                    )
                );

                $this->validator->set_rules($validator_rules);
                if(!$this->validator->validate()){
                    jsonResponse($this->validator->get_array_errors());
                }

                $id_request = (int) $_POST['id_request'];
				$request = $this->cr_users->cr_get_user_request($id_request);
				if(empty($request)){
					jsonResponse(translate("systmess_error_request_does_not_exist"));
                }

                $email = cleanInput($_POST['email']);

                $this->load->model('Auth_model', 'auth_hash');
                $encrypted_email = getEncryptedEmail(cleanInput($email, true));
				if($email != $request['applicant_email'] && $this->cr_users->cr_exist_user_request(array('email' => $email)) && $this->auth_hash->exists_hash($encrypted_email)){
                    jsonResponse('The email already exists in the database. Please choose another one!');
                }

                $email_changed = false;
                if($email != $request['applicant_email']){
                    $email_changed = true;
                }

                $this->load->model('Cr_domains_Model', 'domains');
                $this->load->model("Country_model", 'country');

                $domains = array_filter($_POST['domains']);
                if(empty($domains)){
                    jsonResponse('Please select at least one domain.');
                }

                $domains_info = $this->domains->get_cr_domains(array('domains_list' => implode(',', $domains)));
                if(empty($domains_info)){
                    jsonResponse('Please select at least one domain.');
                }

                $id_group = (int)$_POST['group'];
                $group_info = $this->usergroup->getGroup($id_group);
                if(empty($group_info)){
                    jsonResponse('The group does not exist.');
                }

                if($group_info['gr_type'] != 'CR Affiliate'){
                    jsonResponse('You can not create account in this group.');
                }

                $group_rights = $this->usergroup->getUserRights($id_group);
                if(!have_right('cr_international', $group_rights) && count($domains_info) > 1){
                    jsonResponse('Account from this group can not be assigned to more than one domain.');
                }

                $fname = cleanInput($_POST['fname']);
                $lname = cleanInput($_POST['lname']);
                $status = cleanInput($_POST['status']);
                if(!in_array($status, array('new','confirmed','declined'))){
                    jsonResponse('The status is not correct.');
                }

				$update = array(
					'applicant_fname' => $fname,
					'applicant_lname' => $lname,
                    'id_group' => $id_group,
                    'applicant_status' => $status,
                    'applicant_password' => getEncryptedPassword(generateRandomPassword())
                );

                if($email_changed){
                    $update['applicant_email'] = $email;
                }

                $domains_relation = array();
                foreach ($domains_info as $domain_info) {
                    $domains_relation[] = $domain_info['id_domain'];
                }
                $update['applicant_domains'] = implode(',', $domains_relation);
                $this->cr_users->cr_update_user_request($id_request, $update);

                if($_POST['create_user']){
                    $request = $this->cr_users->cr_get_user_request($id_request);
                    if($request['applicant_status'] != 'confirmed'){
                        jsonResponse('To create user please change request status to "Confirmed".');
                    }

                    $password = generateRandomPassword();
                    $insert = array(
                        'fname' => $request['applicant_fname'],
                        'lname' => $request['applicant_lname'],
                        'email' => $request['applicant_email'],
                        'user_group' => $request['id_group'],
                        'user_ip' => $request['applicant_ip'],
                        'registration_date' => date('Y-m-d H:i:s'),
                        'activation_code' => get_sha1_token($request['applicant_email']),
                        'accreditation_token' => get_sha1_token($request['applicant_email']),
                        'status' => 'new',
                        'country' => $request['id_country'],
                        'state' => $request['id_state'],
                        'city' => $request['id_city'],
                        'user_type' => 'user',
                        'paid' => 1
                    );
                    if($email_changed){
                        $hash_insert = array(
                            'token_email' 	 => $encrypted_email,
                            'token_password' => getEncryptedPassword($password)
                        );

                        $insert['id_principal'] = $this->auth_hash->add_hash($hash_insert);
                    }
                    $notices[] = json_encode(array(
                        'add_date' => date('Y/m/d H:i:s'),
                        'add_by' => 'System',
                        'notice' => 'The email with activation link has been sent.'
                    ));

                    $notices[] = json_encode(array(
                        'add_date' => date('Y/m/d H:i:s'),
                        'add_by' => 'System',
                        'notice' => 'Account has been created.'
                    ));

                    $insert['notice'] = implode(',', $notices);

                    $city_info = $this->country->get_city($insert['city']);
                    if(!empty($city_info)){
                        if($city_info['lat_lng_need_complet'] == 0){
                            $this->country->update_city($city_info['id'], array('lat_lng_need_complet' => 1));
                        }

                        if($city_info['lat_lng_need_complet'] == 2){
                            $insert['user_city_lat'] = $city_info['city_lat'];
                            $insert['user_city_lng'] = $city_info['city_lng'];
                        }
                    }

                    if($id_user = $this->user->setUserMain($insert)){
                        $domains_relation = array();
                        foreach ($domains_info as $domain_info) {
                            $domains_relation[] = array(
                                'id_user' => $id_user,
                                'id_domain' => $domain_info['id_domain']
                            );
                        }

                        if(!empty($domains_relation)){
                            $this->domains->set_user_domains_relation($domains_relation);
                        }

                        $this->cr_users->cr_set_user_additional(array('id_user' => $id_user));
                        $this->cr_users->cr_delete_user_request($id_request);

                        try {
                            /** @var MailerInterface $mailer */
                            $mailer = $this->getContainer()->get(MailerInterface::class);
                            $mailer->send(
                                (new CrSendActivationLink("{$insert['fname']} {$insert['lname']}", $group_info['gr_name'], $insert['activation_code'], $password))
                                    ->to(new RefAddress((string) $id_user, new Address($insert['email'])))
                            );
                        } catch (\Throwable $th) {
                            jsonResponse(translate('email_has_not_been_sent'));
                        }

                        jsonResponse('Country representative has been added successfully.' , 'success');
                    }
                }

                jsonResponse('Changes has been saved successfully.' , 'success');
            break;
            case 'delete_request':
                checkAdminAjax('manage_cr_users');

                $this->load->model('Notify_Model', 'notify');

                $validator_rules = array(
                    array(
                        'field' => 'request',
                        'label' => 'Request ID',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'notice',
                        'label' => 'Notice',
                        'rules' => array('required' => '', 'max_len[5000]' => '')
                    ),
                );

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()){
                    jsonResponse($this->validator->get_array_errors());
                }

                $request_id = (int) cleanInput($_POST['request']);
                $notice = cleanInput($_POST['notice']);
                if (
                    empty($request_id) ||
                    empty($request = $this->cr_users->cr_get_user_request($request_id))
                ) {
                    messageInModal(translate("systmess_error_request_does_not_exist"));
                }

                if(!$this->cr_users->cr_delete_user_request($request_id)) {
                    jsonResponse('Failed to delete brand ambassador request.');
                }

                try {
                    /** @var MailerInterface $mailer */
                    $mailer = $this->getContainer()->get(MailerInterface::class);
                    $mailer->send(
                        (new CrDeleteRequest("{$request['applicant_fname']} {$request['applicant_lname']}", $notice))
                            ->to(new Address($request['applicant_email']))
                    );
                } catch (\Throwable $th) {
                    jsonResponse(translate('email_has_not_been_sent'));
                }

                jsonResponse('The request has been deleted.', 'success');
            break;
            case 'send_email':
                // Check type
                $templateKey = cleanInput($_POST['type'], true);

                if(empty($templateKey)) {
                    jsonResponse('Invalid email type provided');
                }

                // Get user
                $userId = (int) cleanInput($_POST['user']);

                if (empty($userId) || empty($user = model(User_Model::class)->getUser($userId))) {
                    jsonResponse(translate("systmess_error_user_does_not_exist"));
                }

                // Get group
                $group = model(Usergroup_Model::class)->getGroup($user['user_group']);

                if(empty($group) || 'CR Affiliate' !== $group['gr_type']) {
                    jsonResponse('Invalid group - wait for user within CR Affiliate group');
                }

                // Get email template
                $templateCall = new GroupEmailTemplates();
                $templateData = $templateCall->getVerificationTemplate($templateKey);

                if (empty($templateData)) {
                    jsonResponse("Invalid email type provided");
                }

                if (!in_array($group['gr_type'], $templateData['restrict_gr_access'])) {
                    jsonResponse('Invalid group - wait for user within CR Affiliate group');
                }

                switch($templateKey) {
                    case 'cr_send_activation_link':
                        $emailDataPassword = generateRandomPassword();

                        $userNotice = json_encode([
                            'add_date' => date('Y/m/d H:i:s'),
                            'add_by' => 'System',
                            'notice' => 'The email with activation link has been sent.'
                        ]);

                        $user_update = [
                            'notice'   => empty($user['notice']) ? $userNotice : $user['notice'] . ',' . $userNotice
                        ];
                    break;
                }

                if(empty($user_update) || !model(User_Model::class)->updateUserMain($user['idu'], $user_update)) {
                    jsonResponse("Failed to add email to queue due to error on user information update");
                }

                $templateCall->sentEmailTemplate($templateData['template_name'], [
                    "userId"        => $userId,
                    "email"         => $user['email'],
                    "userName"      => "{$user['fname']} {$user['lname']}",
                    "groupName"     => $group['gr_name'],
                    "token"         => $user['activation_code'],
                    '[password]'    => $emailDataPassword ?? '',
                ]);

                jsonResponse("Email of selected type is successfully added to queue", 'success');
            break;
        }
    }

    public function popup_forms(){
		if(!isAjaxRequest()){
			headerRedirect();
        }

        $this->_load_main();

		$action = $this->uri->segment(3);
        switch ($action) {
            case 'add_user':
                checkAdminAjaxModal('manage_cr_users');
                $this->load->model('Cr_domains_Model', 'cr_domains');

                $data['domains'] = $this->cr_domains->get_cr_domains();
                $data['groups'] = $this->usergroup->getGroupsByType(array('type' => "'CR Affiliate'"));
                $this->view->display('admin/cr/users/form_view', $data);
            break;
            case 'edit_user':
                checkAdminAjaxModal('manage_cr_users');
                $this->load->model('Cr_domains_Model', 'cr_domains');

                $id_user = (int)$this->uri->segment(4);
                $data['user'] = $this->user->getUser($id_user);
                if(empty($data['user'])){
                    messageInModal(translate("systmess_error_user_does_not_exist"));
                }

                $data['user_domains'] = arrayByKey($this->cr_domains->get_user_domains_relation($id_user), 'id_domain');
                $data['domains'] = $this->cr_domains->get_cr_domains();
                $data['groups'] = $this->usergroup->getGroupsByType(array('type' => "'CR Affiliate'"));
                $this->view->display('admin/cr/users/form_view', $data);
            break;
            case 'edit_user_request':
                checkAdminAjaxModal('manage_cr_users');
                $this->load->model('Cr_domains_Model', 'cr_domains');

                $id_request = (int)$this->uri->segment(4);
                $data['request'] = $this->cr_users->cr_get_user_request($id_request);
                if(empty($data['request'])){
                    messageInModal(translate("systmess_error_request_does_not_exist"));
                }

                $data['domains'] = $this->cr_domains->get_cr_domains();
                $data['groups'] = $this->usergroup->getGroupsByType(array('type' => "'CR Affiliate'"));
                $this->view->display('admin/cr/users_requests/form_view', $data);
            break;
            case 'assign_users':
                if (empty($_GET['type'])) {
                    messageInModal('Please provide the type');
                }

                if (empty($_GET['id_item'])) {
                    messageInModal('Please provide the item ID');
                }

                $id_item = (int)$_GET['id_item'];
                $selected_users = array();
                $domain = null;


                $this->load->model('Cr_users_Model', 'cr_users');
                $this->load->model('Usergroup_Model', 'usergroup');

                $groups = $this->usergroup->getGroupsByType(array('type' => "'CR Affiliate'", 'counter' => false));
                $lists = arrayToListKeys($groups, array('idgroup'));
                $user_params = array(
                    'group' => $lists['idgroup'],
                    'limit' => 'all',
                    'status' => 'active',
                );

                $show_user_country = false;

                switch ($_GET['type']) {
                    case 'training':
                        checkAdminAjaxModal('cr_training_administration');

                        $this->load->model('Cr_training_Model', 'cr_training');
                        $this->load->model('Cr_domains_Model', 'cr_domains');

                        $training = $this->cr_training->get_training($id_item);

                        if (empty($training)) {
                            messageInModal('The training was not found');
                        }

                        $training_users = $this->cr_training->get_assigned_users($id_item);
                        foreach ($training_users as $training_user) {
                            $selected_users[$training_user['id_user']] = $training_user;
                        }

                        $show_user_country = true;
                    break;
                    case 'event':
                        checkAdminAjaxModal('cr_events_administration');

                        $this->load->model('Cr_events_Model', 'cr_events');
                        $this->load->model('Cr_domains_Model', 'cr_domains');

                        $event = $this->cr_events->get_event($id_item);

                        if (empty($event)) {
                            messageInModal('The event was not found');
                        }

                        $domain = $this->cr_domains->get_cr_domain(array(
                            'id_country' => $event['event_id_country']
                        ));

                        if (empty($domain)) {
                            messageInModal('The domain was not found');
                        }

                        $user_params['domains'] = $domain['id_domain'];

                        $event_users = $this->cr_events->get_assigned_users($id_item);
                        foreach ($event_users as $event_user) {
                            $selected_users[$event_user['id_user']] = $event_user;
                        }
                    break;
                    default:
                        messageInModal('Incorrect type');
                    break;
                }


                $records = $this->cr_users->cr_get_users($user_params);

                $groups_users = array();
                foreach ($records as $record) {
                    if (empty($groups_users[$record['gr_name']])) {
                        $groups_users[$record['gr_name']] = array();
                    }

                    $user_name = "{$record['fname']} {$record['lname']}";
                    if($show_user_country) {
                        $user_name .= ", {$record['user_country']}";
                    }

                    $groups_users[$record['gr_name']][] = array(
                        'id_user' => $record['idu'],
                        'name' => $user_name,
                        'group_name' => $record['gr_name'],
                        'country_name' => $record['user_country']
                    );
                }

                $select_data = array();
                foreach ($groups_users as $group => $users) {
                    $group_data = array(
                        'text' => $group,
                        'children' => array()
                    );

                    if($show_user_country) {
                        usort($users, function($a, $b) { return $a['country_name'] > $b['country_name']; });
                    }

                    foreach ($users as $user) {
                        $group_data['children'][] = array(
                            'id' => $user['id_user'],
                            'text' => $user['name'],
                            'selected' => isset($selected_users[$user['id_user']]),
                            'disabled' => isset($selected_users[$user['id_user']]) && isset($selected_users[$user['id_user']]['assigned_by_admin']) && $selected_users[$user['id_user']]['assigned_by_admin'] == 0
                        );
                    }

                    $select_data[] = $group_data;
                }


                $this->view->display('admin/cr/users/assign_users_form_view', array(
                    'select_data' => $select_data,
                    'type' => $_GET['type'],
                    'id_item' => $_GET['id_item'],
                ));
            break;
            case 'same_country':
                $id_user = id_from_link($this->uri->segment(4));
                $user_info = $this->user->getUser($id_user);
                if(empty($user_info)){
                    messageInModal(translate("systmess_error_user_does_not_exist"));
                }

                if($user_info['gr_type'] != 'CR Affiliate'){
                    messageInModal(translate("systmess_error_user_does_not_exist"));
                }

                $data['current_user'] = $id_user;

                $domain_users_params = array(
                    'status' => 'active',
                    'group_type' => "'CR Affiliate'",
                    'domains_info' => true,
                    'limit' => 10,
                    'not_users_list' => $user_info['idu']
                );

                $this->load->model('Cr_domains_Model', 'cr_domains');
                $data['user_domains'] = $this->cr_domains->get_user_domains_relation($id_user);
                $domains_list = array();
                foreach ($data['user_domains'] as $user_domain) {
                    $domains_list[] = $user_domain['id_domain'];
                }

                $domain_users_params['domains'] = implode(',', $domains_list);
                $data['domain_users_count'] = $this->cr_users->cr_count_users($domain_users_params);
                $data['domain_users'] = $this->cr_users->cr_get_users($domain_users_params);

                $this->view->assign($data);
                $this->view->display('new/cr/user/cr_same_country_popup_view');
            break;
            case 'send_email':
                if(!have_right('moderate_content')){
                    messageInModal(translate("systmess_error_rights_perform_this_action"));
                }

                $user_id = (int) $this->uri->segment(4);
                if(empty($user_id) || empty($user = $this->user->getUser($user_id))) {
                    messageInModal(translate("systmess_error_user_does_not_exist"));
                }

                $templateCall = new GroupEmailTemplates();
                $templateData = $templateCall->getVerificationTemplates(['group_type' => 'CR Affiliate']);

                $this->view->assign(array(
                    'user'      => $user,
                    'action'    => __SITE_URL . 'cr_users/ajax_operations/send_email',
                    'templates' => $templateData,
                ));
                $this->view->display('admin/cr/users/send_emails_view');
            break;
            case 'delete_request':
                if(!have_right('moderate_content')){
                    messageInModal(translate("systmess_error_rights_perform_this_action"));
                }

                $request_id = (int) $this->uri->segment(4);
                if(
                    empty($request_id) ||
                    empty($request = $this->cr_users->cr_get_user_request($request_id))
                ) {
                    messageInModal(translate("systmess_error_request_does_not_exist"));
                }

                $this->view->assign(array(
                    'request'   => $request_id,
                    'action'    => __SITE_URL . 'cr_users/ajax_operations/delete_request',
                ));
                $this->view->display("admin/cr/users_requests/delete_request_view");
            break;
        }
    }

    public function preview_email_template()
    {
		if (!logged_in()) {
            // REFACTOR
			echo translate("systmess_error_should_be_logged");
			return;
		}

		if (!have_right('manage_content')) {
            // REFACTOR
			echo translate("systmess_error_rights_perform_this_action");
			return;
        }

        $this->_load_main();

        $templateKey = cleanInput($_GET['type'], true);
        if (empty($templateKey)) {
			echo 'Warning: Select email template.';
			return;
        }

        $user_id = (int) cleanInput($_GET['user'], true);
        if (empty($user_id) || empty($user = $this->user->getUser($user_id))) {
            // REFACTOR
            echo translate("systmess_error_user_does_not_exist");
        }

        $group = $this->usergroup->getGroup($user['user_group']);
        if (empty($group) || 'CR Affiliate' !== $group['gr_type']) {
            // REFACTOR
			echo 'Error: Invalid group';
			return;
        }

        // Get email template
        $templateCall = new GroupEmailTemplates();
        $templateData = $templateCall->getVerificationTemplate($user['email']);

        if (!in_array($group['gr_type'], $templateData['restrict_gr_access'])) {
            $emailContent = model(Emails_Template_Model::class)->getEmailTemplateByAlias($templateKey);

            if (!$emailContent) {
                jsonResponse(translate('email_template_does_not_exist'));
            }

            /** @var BodyRendererInterface $bodyRenderer */
            $bodyRenderer = $this->getContainer()->get(BodyRendererInterface::class);
            $templateCase = EmailTemplate::from($emailContent['alias_template']);
            $className = $templateCase->className();
            $email = new $className(...$templateCase->templateData());
            $email->templateReplacements([]);
            $bodyRenderer->render($email);
            $html = $email->getHtmlBody();
        } else {
            $templateMess = translate('email_template_does_not_exist');
            $html = '';
        }

        views()->display('admin/user/emails/multi_email_template_view', [
            'template'     => $html,
            'templateMess' => $templateMess,
        ]);

        return;
	}
}
