<?php

use App\Common\Buttons\ChatButton;
use App\Common\Contracts\Notifier\SystemChannel;
use App\Email\ConfirmDeleteAccount;
use App\Email\ConfirmUserCancel;
use App\Messenger\Message\Event\Lifecycle\UserWasRemovedEvent;
use ExportPortal\Bridge\Mailer\Mime\RefAddress;
use ExportPortal\Bridge\Notifier\Notification\SystemNotification;
use ExportPortal\Bridge\Notifier\Recipient\Recipient;
use ExportPortal\Bridge\Symfony\Component\Messenger\MessengerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Notifier\NotifierInterface;

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 */
class User_cancel_Controller extends TinyMVC_Controller
{
	private $request_statuses = array(
		'init' => array(
			'icon' => 'ep-icon ep-icon_new-stroke txt-blue',
			'title' => 'New'
		),
		'confirmed' => array(
			'icon' => 'ep-icon ep-icon_ok-circle txt-green',
			'title' => 'Confirmed'
		),
		'canceled' => array(
			'icon' => 'ep-icon ep-icon_remove-circle txt-red',
			'title' => 'Declined'
		),
		'deleted' => array(
			'icon' => 'ep-icon ep-icon_trash txt-gray',
			'title' => 'Deleted'
		)
	);

    /**
     * The notifier instance.
     */
    private NotifierInterface $notifier;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->notifier = $container->get(NotifierInterface::class);
    }

	function index(){
		checkIsLogged();

		checkDomainForGroup();

		if(check_group_type(array('Admin', 'EP Staff', 'Company Staff', 'Shipper Staff'))){
			headerRedirect();
		}

		$data['cancel_request'] = model('user_cancel')->get_close_request(array(
			'user' => id_session(),
			'status_list' => array('init', 'confirmed')
		));

        if (__CURRENT_SUB_DOMAIN === getSubDomains()['shippers']) {
            $this->userCancelEpl($data);
        } else {
            $this->userCancelAll($data);
        }
    }

    private function userCancelEpl($data){
        $data['templateViews'] = [
            'mainOutContent'    => 'user_cancel/index_view',
        ];
        $data['webpackData'] = [
            'dashboardOldPage' => true,
            // 'styleCritical' => 'epl_styles_home',
            // 'pageConnect' 	=> 'epl_index_page',
        ];

        views(["new/epl/template/index_view"], $data);
    }

    private function userCancelAll($data){
        views(["new/header_view", "new/user_cancel/index_view", "new/footer_view"], $data);
    }

    function request_confirmation() {
        $uri = uri()->uri_to_assoc();

        if (!isset($uri['code'])) {
            $this->session->setMessages('The confirmation code doesn\'t found.', 'errors');
			headerRedirect();
        }

        $confirmation_code = cleanInput($uri['code']);
		$cancellation_request = model(User_Cancel_Model::class)->get_close_request(array('token' => $confirmation_code));

		if (empty($cancellation_request)) {
			$this->session->setMessages('The confirmation code is wrong.', 'errors');
			headerRedirect();
		}

		switch ($cancellation_request['status']) {
			case 'confirmed':
				$this->session->setMessages('You have already confirmed the cancellation request. Your request is being processed.', 'info');

				break;
			case 'deleted':
				$this->session->setMessages('Your request has already been processed.', 'info');

				break;
			case 'canceled':
				$this->session->setMessages('Your request has been rejected. Please contact us or create a new cancellation request.', 'info');

				break;
            case 'init':
                if (model(User_Cancel_Model::class)->update_close_request($cancellation_request['idreq'], array('status' => 'confirmed'))) {
                    $this->session->setMessages('You have successfully confirmed the cancellation request. Please wait for it to be processed.', 'success');
                } else {
                    $this->session->setMessages('Some errors occurred while the confirmation of cancellation request. Please contact us to solve this problem.', 'errors');
                }

                break;
        }

        headerRedirect();
    }

	public function administration()
    {
        checkPermision('cancellation_requests_administration');

		$data['last_requests_id'] = model('user_cancel')->get_request_last_id();

        $this->view->assign('title', 'Cancelation');
		$this->view->assign($data);

        $this->view->display('admin/header_view');
        $this->view->display('admin/user_cancel/cancelation_view');
        $this->view->display('admin/footer_view');
    }

	public function ajax_cancelation_administration()
    {
		checkIsAjax();
		checkIsLoggedAjaxDT();
		checkPermisionAjaxDT('cancellation_requests_administration');

        $params = array('per_p' => $_POST['iDisplayLength'], 'start' => $_POST['iDisplayStart']);

        if ($_POST['iSortingCols'] > 0) {
            for ($i = 0; $i < $_POST['iSortingCols']; $i++) {
                switch ($_POST["mDataProp_" . intVal($_POST['iSortCol_' . $i])]) {
                    case 'dt_id_request': $params['sort_by'][] = 'req.idreq-' . $_POST['sSortDir_' . $i]; break;
                    case 'dt_name': $params['sort_by'][] = 'fullname-' . $_POST['sSortDir_' . $i]; break;
                    case 'dt_email': $params['sort_by'][] = 'us.email-' . $_POST['sSortDir_' . $i]; break;
                    case 'dt_close_date': $params['sort_by'][] = 'req.close_date-' . $_POST['sSortDir_' . $i]; break;
                    case 'dt_start_date': $params['sort_by'][] = 'req.start_date-' . $_POST['sSortDir_' . $i]; break;
                    case 'dt_update_date': $params['sort_by'][] = 'req.update_date-' . $_POST['sSortDir_' . $i]; break;
                    case 'dt_status': $params['sort_by'][] = 'req.status-' . $_POST['sSortDir_' . $i]; break;
                }
            }
        }

        if (!empty($_POST['sSearch'])){
            $params['keywords'] = cleanInput($_POST['sSearch']);
		}

        if (isset($_POST['user'])){
            $params['user'] = cleanInput($_POST['user']);
		}

        if (isset($_POST['status'])){
            $params['status'] = cleanInput($_POST['status']);
		}

        if (isset($_POST['start_from'])){
            $params['start_from'] = formatDate(cleanInput($_POST['start_from']), 'Y-m-d');
		}

        if (isset($_POST['start_to'])){
            $params['start_to'] = formatDate(cleanInput($_POST['start_to']), 'Y-m-d');
		}

        $requests = model('user_cancel')->get_close_requests($params);
        $requests_count = model('user_cancel')->counter_close_requests_by_conditions($params);

        $output = array(
            "sEcho" => intval($_POST['sEcho']),
            "iTotalRecords" => $requests_count,
            "iTotalDisplayRecords" => $requests_count,
			'aaData' => array()
        );

		if(empty($requests)){
			jsonResponse('', 'success', $output);
		}

		foreach ($requests as $request) {
			$actions = array(
				'<li>
					<a class="fancyboxValidateModalDT fancybox.ajax" href="'. __SITE_URL .'user_cancel/popup_forms/requests_note/'.$request['idreq'].'" title="EP Manager Notices" data-title="EP Manager Notices">
						<span class="ep-icon ep-icon_notice mb-0 fs-14 lh-16"></span> Notices
					</a>
				</li>'
			);

			if ('init' == $request['status']){
				$actions[] = '<li>
								<a class="txt-orange confirm-dialog" data-callback="decline_request" data-message="Are you sure you want to decline this request?" data-request="' . $request['idreq'] . '" href="#" title="Decline request">
									<span class="ep-icon ep-icon_remove mb-0 fs-14 lh-16"></span> Decline
								</a>
							</li>';

			}

            if ('confirmed' == $request['status'] && have_right('delete_account')) {
                $actions[] = '<li>
								<a class="txt-red confirm-dialog" data-callback="delete_account" data-message="Are you sure you want to delete account?" data-request="' . $request['idreq'] . '" href="#" title="Delete Account">
									<span class="ep-icon ep-icon_trash txt-red mb-0 fs-14 lh-16"></span> Delete Account
								</a>
							</li>';
            }

			$online = '<a class="ep-icon ep-icon_onoff txt-green dt_filter" title="Filter just online" data-value="1" data-name="online"></a>';
			if(!$request['logged']){
				$online = '<a class="ep-icon ep-icon_onoff txt-red dt_filter" title="Filter just offline" data-value="0" data-name="offline"></a>';
            }

            //TODO: admin chat hidden
            $btnChatUser = new ChatButton(['hide' => true, 'recipient' => $request['user'], 'recipientStatus' => $request['user_status']], ['classes' => 'btn-chat-now', 'text' => '']);
            $btnChat = $btnChatUser->button();

			$output['aaData'][] = array(
				'dt_id_request' 	=> $request['idreq'],
				'dt_name'			=> '<div>
											<a class="ep-icon ep-icon_filter txt-green dt_filter" data-title="User" title="Filter by user" data-value-text="'.$request['fullname'].'" data-value="'.$request['user'].'" data-name="user"></a>
											' .$online .'
											<a class="ep-icon ep-icon_user" title="View user" target="_blank" href="' . __SITE_URL . 'usr/' . strForURL($request['fullname']) . '-' . $request['user'] . '"></a>
                                            '.$btnChat.'
										</div>'
                                        . $request['fullname'] .'<br>'. $request['email'],
				'dt_reason'			=> "<div><strong>Reason: </strong>".cleanOutput($request['reason']) . "</div>" . (!empty($request['feedback']) ? "<div class=\"mt-10\"><strong>Is there anything else you'd like us to know?: </strong>" . cleanOutput($request['feedback']) . "</div>" : ""),
				'dt_close_date' 	=> getDateFormat($request['close_date'], 'Y-m-d', 'j M, Y'),
				'dt_start_date' 	=> getDateFormat($request['start_date']),
				'dt_update_date' 	=> getDateFormat($request['update_date']),
				'dt_status' 		=> '<span>
											<i class="'. $this->request_statuses[$request['status']]['icon'] .' fs-30"></i>
											<br>
											'. $this->request_statuses[$request['status']]['title'] .'
										</span>',
				'dt_actions' 		=> '<div class="dropdown">
											<a class="ep-icon ep-icon_menu-circles dropdown-toggle" type="button" data-toggle="dropdown"></a>
											<ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu2">
												'. implode('', $actions) .'
											</ul>
										</div>'
			);
		}

        jsonResponse('', 'success', $output);
    }

	function popup_forms(){
		checkIsAjax();
		checkIsLoggedAjaxModal();

		$op = $this->uri->segment(3);
		switch($op){
			case 'requests_note':
				checkPermisionAjaxModal('cancellation_requests_administration');

				$id_request = (int) $this->uri->segment(4);
				$data['request_info'] = model('user_cancel')->get_close_request(array(
					'idreq' => $id_request
				));

				$this->view->assign($data);
				$this->view->display('admin/user_cancel/popup_cancelation_note_view');
			break;
		}
	}

	function ajax_user_cancel_operation(){
		checkIsAjax();
		checkIsLoggedAjax();

		$op = $this->uri->segment(3);
		switch($op){
			case 'check_new':
				checkPermisionAjax('cancellation_requests_administration');

                $lastId = (int) $_POST['lastId'];
                $requests_count = model('user_cancel')->get_count_new_requests($lastId);

                if ($requests_count) {
                    $last_requests_id = model('user_cancel')->get_request_last_id();
                    jsonResponse('', 'success', array('nr_new' => $requests_count, 'lastId' => $last_requests_id));
				}

				jsonResponse('No new requests', 'info');
            break;
			case 'decline_request':
				checkPermisionAjax('cancellation_requests_administration');

				$id_request = (int) $_POST['request'];
				$request_info = model('user_cancel')->get_close_request(array(
					'idreq' => $id_request,
					'status_list' => array('init')
				));

				if(empty($request_info)){
					jsonResponse(translate('systmess_error_invalid_data'));
				}

				model('user_cancel')->update_close_request($id_request, array(
					'status' => 'canceled'
				));

                $this->notifier->send(
                    (new SystemNotification('user_cancel_declined'))->channels([(string) SystemChannel::STORAGE()]),
                    new Recipient((int) $request_info['user'])
                );

				jsonResponse('The Account cancelation request has been canceled.','success');
			break;
			case 'save_requests_note':
				checkPermisionAjax('cancellation_requests_administration');

				$validator_rules = array(
					array(
						'field' => 'note',
						'label' => 'Note message',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'request',
						'label' => 'Request',
						'rules' => array('required' => '')
					)
				);
				$this->validator->set_rules($validator_rules);

				if(!$this->validator->validate()){
					jsonResponse($this->validator->get_array_errors());
				}

				model('user_cancel')->update_close_request((int) $_POST['request'], array(
					'note' => cleanInput($_POST['note'])
				));

				jsonResponse('The Account cancelation request note has been successfully updated.','success');
			break;
			case 'account_cancel':
				$validator = $this->validator;
				$validator_rules = array(
					array(
						'field' => 'close_date',
						'label' => 'Date of cancellation',
						'rules' => array('required' => '', 'valid_date[m/d/Y]' => '', 'valid_date_future[m/d/Y]' => '')
					),
					array(
						'field' => 'reason',
						'label' => 'Reason for cancellation',
						'rules' => array('required' => '', 'max_len[1000]' => '')
                    ),
					array(
						'field' => 'feedback',
						'label' => 'Is there anything else you\'d like us to know?',
						'rules' => array('max_len[1000]' => '')
					)
				);

				$this->validator->set_rules($validator_rules);
				if(!$validator->validate()){
					jsonResponse($this->validator->get_array_errors());
                }

                $id_user = id_session();
                if (empty($user = model(User_Model::class)->getSimpleUser($id_user, 'users.email, users.fname, users.lname'))) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

				$request = model('user_cancel')->get_close_request(array(
					'user' => $id_user,
					'status_list' => array('init')
				));

				if(!empty($request)){
					jsonResponse('A request to cancel this account already exists.');
                }

                $requestConfirmationCode = get_sha1_token($user['email']);

				model('user_cancel')->set_close_request(array(
					'user' => $id_user,
					'close_date' => getDateFormat($_POST['close_date'], 'm/d/Y', 'Y-m-d'),
					'reason' => $_POST['reason'],
                    'feedback' => $_POST['feedback'],
                    'confirmation_token' => $requestConfirmationCode
                ));

                try {
                    /** @var MailerInterface $mailer */
                    $mailer = $this->getContainer()->get(MailerInterface::class);
                    $mailer->send(
                        (new ConfirmUserCancel("{$user['fname']} {$user['lname']}", $requestConfirmationCode))
                            ->to(new RefAddress((string) $id_user, new Address($user['email'])))
                    );
                } catch (\Throwable $th) {
                    jsonResponse(translate('email_has_not_been_sent'));
                }

				jsonResponse('The request has been successfully sent.','success');
            break;
            case 'delete_account':
                checkPermisionAjax('delete_account');

                $idRequest = (int) $_POST['request'];
                if (empty($idRequest) || empty($request = model(User_Cancel_Model::class)->get_close_request(array('idreq' => $idRequest)))) {
                    jsonResponse('Request ID is wrong.');
                }

                if ('confirmed' != $request['status']) {
                    jsonResponse('Account can be deleted only if request is in status confirmed. Current status is: ' . $request['status']);
                }

                if (!model(Blocking_Model::class)->block_user_content($request['user'])) {
                    jsonResponse('At some point in the blocking of user content, something went wrong.');
                }

                $user = model(User_Model::class)->getSimpleUser($request['user']);

                $userName = $user['fname'] . ' ' . $user['lname'];

                $user_updates = array(
                    'clean_session_token'   => '',
				    'subscription_email'    => 0,
                    'notify_email' 		    => 0,
                    'cookie_salt'           => genRandStr(8),
                    'status'                => 'deleted',
                    'fname'                 => 'User',
                    'lname'                 => $user['idu'],
                    'email'                 => time() . $user['email'],
                    'logged'                => 0,
                );

                if (!model(User_Model::class)->updateUserMain($user['idu'], $user_updates)) {
                    jsonResponse('Failed to update user data.');
                }

                switch ($user['gr_type']) {
                    case 'Buyer':
                        if (!empty($company = model(Company_Buyer_Model::class)->get_company_by_user($user['idu']))) {
                            $company_updates = array(
                                'company_name' => 'company-' . $company['id'],
                                'company_legal_name' => 'company-' . $company['id'],
                            );

                            if (!model(Company_Buyer_Model::class)->update_company($user['idu'], $company_updates)) {
                                jsonResponse('Failed to update buyer\' company data.');
                            }
                        }

                    break;
                    case 'Seller':
                        if (!empty($company = model(Company_Model::class)->get_seller_base_company($user['idu']))) {
                            $company_updates = array(
                                'name_company' => 'company-' . $company['id_company'],
                                'legal_name_company' => 'company-' . $company['id_company'],
                            );

                            if (!model(Company_Model::class)->update_company($company['id_company'], $company_updates)) {
                                jsonResponse('Failed to update seller\' company data.');
                            }
                        }

                    break;
                    case 'Shipper':
                        if (!empty($company = model(Shippers_Model::class)->get_shipper_by_user($user['idu']))) {
                            $company_updates = array(
                                'co_name' => 'company-' . $company['id'],
                                'legal_co_name' => 'company-' . $company['id'],
                            );

                            if (!model(Shippers_Model::class)->update_shipper($company_updates, $company['id'])) {
                                jsonResponse('Failed to update shipper\' company data.');
                            }
                        }

                    break;
                }

                $related_accounts = model(User_Model::class)->get_simple_users_by_id_principal($user['id_principal']);

                if (empty($related_accounts)) {
                    if (!model(Auth_Model::class)->change_hash($user['id_principal'], array(
                        'token_email'       => getEncryptedEmail($user_updates['email']),
                        'is_legacy'         => 0,
                    ))){
                        jsonResponse('Failed to change user credentials.');
                    }
                }
                try {
                    /** @var MailerInterface $mailer */
                    $mailer = $this->getContainer()->get(MailerInterface::class);
                    $mailer->send(
                        (new ConfirmDeleteAccount($userName))
                        ->to(new RefAddress((string) $user['idu'], new Address($user['email'])))
                    );
                } catch (\Throwable $th) {
                    jsonResponse(translate('email_has_not_been_sent'));
                }

                model(User_Cancel_Model::class)->update_close_request($idRequest, ['status' => 'deleted']);

                session()->destroyBySessionId($user['ssid']);

                model(User_Model::class)->set_notice($user['idu'], [
                    'add_date' => date('Y/m/d H:i:s'),
                    'add_by' => 'System',
                    'notice' => 'User account has been deleted. Cancellation request #' . $idRequest
                ]);

                /** @var Ep_Reviews_Model $epReviewsModel */
                $epReviewsModel = model(Ep_Reviews_Model::class);

                $epReviewsModel->updateMany(
                    [
                        'is_published' => 0
                    ],
                    [
                        'conditions' => [
                            'userId'    => (int) $user['idu'],
                        ],
                    ]
                );

                // Send event to the bus about user removal
                $this->getContainer()->get(MessengerInterface::class)->bus('event.bus')->dispatch(new UserWasRemovedEvent((int) $user['idu']));

                jsonResponse('User account has been successfully deleted.', 'success');

            break;
		}
	}
}
